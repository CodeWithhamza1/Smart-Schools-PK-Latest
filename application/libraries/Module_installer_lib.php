<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Module_installer_lib {

    private $CI;
    private $base_path;
    private $storage_path;
    private $allowed_targets = array(
        'application/controllers/',
        'application/models/',
        'application/views/',
        'application/helpers/',
        'application/libraries/',
        'application/language/',
        'backend/',
        'uploads/',
    );

    public function __construct() {
        $this->CI = &get_instance();
        $this->base_path = rtrim(str_replace('\\', '/', FCPATH), '/') . '/';
        $this->storage_path = $this->base_path . 'backup/module_installer/';
        $this->CI->load->helper(array('file', 'download', 'string'));
    }

    public function prepareStorage() {
        $dirs = array(
            $this->storage_path,
            $this->storage_path . 'packages/',
            $this->storage_path . 'extract/',
            $this->storage_path . 'database/',
            $this->storage_path . 'files/',
            $this->storage_path . 'exports/',
        );

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $index = $dir . 'index.html';
            if (!file_exists($index)) {
                write_file($index, '<!doctype html><title>403 Forbidden</title>');
            }
        }

        $htaccess = $this->storage_path . '.htaccess';
        if (!file_exists($htaccess)) {
            write_file($htaccess, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        }
    }

    public function storeUploadedPackage($file) {
        $this->prepareStorage();

        if (empty($file['name']) || empty($file['tmp_name'])) {
            throw new Exception('Please select a module ZIP file.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            throw new Exception('Only ZIP module packages are allowed.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Invalid upload request.');
        }

        $filename = 'module_' . date('Ymd_His') . '_' . random_string('alnum', 8) . '.zip';
        $target = $this->storage_path . 'packages/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new Exception('Unable to save uploaded package.');
        }

        return $target;
    }

    public function analyzePackage($package_path) {
        $this->prepareStorage();

        if (!class_exists('ZipArchive')) {
            throw new Exception('PHP ZipArchive extension is required.');
        }

        if (!file_exists($package_path)) {
            throw new Exception('Module package was not found.');
        }

        $zip = new ZipArchive();
        if ($zip->open($package_path) !== true) {
            throw new Exception('Unable to open module ZIP package.');
        }

        $this->validateZipEntries($zip);

        $extract_dir = $this->storage_path . 'extract/' . date('Ymd_His') . '_' . random_string('alnum', 8) . '/';
        mkdir($extract_dir, 0755, true);
        if (!$zip->extractTo($extract_dir)) {
            $zip->close();
            throw new Exception('Unable to extract module package.');
        }
        $zip->close();

        $manifest_path = $extract_dir . 'module.json';
        if (!file_exists($manifest_path)) {
            throw new Exception('module.json is required at the root of the package.');
        }

        $manifest = json_decode(file_get_contents($manifest_path), true);
        if (!is_array($manifest)) {
            throw new Exception('module.json is not valid JSON.');
        }

        $this->validateManifestBasics($manifest);

        $module_key = $manifest['module']['key'];
        $module_version = $manifest['module']['version'];
        $installed = $this->CI->module_installer_model->getModule($module_key);
        $action = 'install';
        $sql_files = $this->resolveSqlFiles($manifest, null, $module_version, $extract_dir, $action);

        if (!empty($installed) && $installed['status'] !== 'uninstalled') {
            $current_version = $installed['version'];
            if (version_compare($module_version, $current_version, '<=')) {
                throw new Exception('Module is already installed at version ' . $current_version . '. Upload a higher version package to upgrade.');
            }

            $action = 'upgrade';
            $sql_files = $this->resolveSqlFiles($manifest, $current_version, $module_version, $extract_dir, $action);
            if (empty($sql_files) && empty($manifest['files'])) {
                throw new Exception('Upgrade package does not declare upgrade SQL or files.');
            }
        }

        $files = $this->validateManifestFiles($manifest, $extract_dir);

        return array(
            'action' => $action,
            'package_path' => $package_path,
            'package_hash' => 'sha256:' . hash_file('sha256', $package_path),
            'extract_dir' => $extract_dir,
            'manifest' => $manifest,
            'module_key' => $module_key,
            'module_version' => $module_version,
            'installed' => $installed,
            'files' => $files,
            'sql_files' => $sql_files,
            'permissions' => isset($manifest['permissions']['groups']) ? $manifest['permissions']['groups'] : array(),
            'menus' => isset($manifest['menus']) && is_array($manifest['menus']) ? $manifest['menus'] : array(),
        );
    }

    public function installFromPreview($preview) {
        $module_key = $preview['module_key'];
        $manifest = $preview['manifest'];
        $installed_files = array();
        $copied_targets = array();
        $db_backup_path = null;

        $this->CI->module_installer_model->addLog($module_key, 'info', 'Starting module ' . $preview['action'] . '.');

        try {
            if (!is_dir($preview['extract_dir'])) {
                $preview = $this->reanalyzePreservingAction($preview);
                $manifest = $preview['manifest'];
            }

            $db_backup_path = $this->createDatabaseBackup($module_key);
            $this->CI->module_installer_model->addLog($module_key, 'info', 'Database backup created.', array('path' => $db_backup_path));

            foreach ($preview['files'] as $file) {
                if ($file['status'] === 'unchanged') {
                    $installed_files[] = $this->fileRecord($module_key, $file, null);
                    continue;
                }

                $backup_path = null;
                if (file_exists($file['target_abs'])) {
                    $backup_path = $this->backupTargetFile($module_key, $file['target']);
                }

                $this->copyFile($file['source_abs'], $file['target_abs']);
                $copied_targets[] = array('target_abs' => $file['target_abs'], 'backup_path' => $backup_path);
                $installed_files[] = $this->fileRecord($module_key, $file, $backup_path);
                $this->CI->module_installer_model->addLog($module_key, 'info', 'Installed file ' . $file['target'] . '.');
            }

            foreach ($preview['sql_files'] as $sql_file) {
                if ($this->CI->module_installer_model->hasMigration($module_key, $sql_file['migration_key'])) {
                    continue;
                }

                $this->executeSqlFile($sql_file['path_abs']);
                $this->CI->module_installer_model->recordMigration($module_key, $sql_file['version'], $sql_file['migration_key'], $sql_file['checksum']);
                $this->CI->module_installer_model->addLog($module_key, 'info', 'Executed SQL migration ' . $sql_file['path'] . '.');
            }

            $this->registerPermissions($manifest);
            $this->registerMenus($module_key, $manifest);

            $module = $manifest['module'];
            $this->CI->module_installer_model->saveModule(array(
                'module_key' => $module_key,
                'name' => $module['name'],
                'version' => $module['version'],
                'description' => isset($module['description']) ? $module['description'] : '',
                'author' => isset($module['author']) ? $module['author'] : '',
                'status' => 'enabled',
                'manifest_json' => json_encode($manifest),
                'package_path' => $preview['package_path'],
                'package_hash' => $preview['package_hash'],
                'db_backup_path' => $db_backup_path,
                'installed_by' => $this->CI->customlib->getStaffID(),
                'last_error' => null,
            ));
            $this->CI->module_installer_model->replaceFiles($module_key, $this->mergeFileRecords($module_key, $installed_files));

            $this->CI->module_installer_model->addLog($module_key, 'success', 'Module installed successfully.');

            return array('status' => true, 'message' => 'Module installed successfully.', 'db_backup_path' => $db_backup_path);
        } catch (Exception $e) {
            $this->restoreCopiedFiles($copied_targets);
            $this->CI->module_installer_model->setModuleStatus($module_key, 'disabled', $e->getMessage());
            $this->CI->module_installer_model->addLog($module_key, 'error', $e->getMessage());
            throw $e;
        }
    }

    public function exportModule($module_key) {
        $module = $this->CI->module_installer_model->getModule($module_key);
        if (!$module || $module['status'] === 'uninstalled') {
            throw new Exception('Module is not installed.');
        }

        $manifest = json_decode($module['manifest_json'], true);
        if (!is_array($manifest)) {
            throw new Exception('Stored module manifest is invalid.');
        }

        $export_dir = $this->storage_path . 'exports/' . $module_key . '_' . date('Ymd_His') . '/';
        mkdir($export_dir, 0755, true);
        mkdir($export_dir . 'files/', 0755, true);
        mkdir($export_dir . 'sql/install/', 0755, true);

        $files = $this->CI->module_installer_model->getFiles($module_key);
        $export_manifest_files = array();
        foreach ($files as $file) {
            $target = $this->normalizeRelativePath($file['target_path']);
            $source_abs = $this->base_path . $target;
            if (!file_exists($source_abs)) {
                continue;
            }

            $dest_rel = 'files/' . $target;
            $dest_abs = $export_dir . $dest_rel;
            $this->copyFile($source_abs, $dest_abs);
            $export_manifest_files[] = array(
                'source' => $dest_rel,
                'target' => $target,
                'checksum' => 'sha256:' . hash_file('sha256', $source_abs),
                'overwrite' => true,
            );
        }

        $manifest['files'] = $export_manifest_files;
        $sql = $this->buildExportSql(isset($manifest['database']['export_tables']) ? $manifest['database']['export_tables'] : array());
        if ($sql !== '') {
            write_file($export_dir . 'sql/install/900_exported_schema_data.sql', $sql);
            $manifest['database']['install'] = array('sql/install/900_exported_schema_data.sql');
        }

        write_file($export_dir . 'module.json', json_encode($manifest, JSON_PRETTY_PRINT));
        write_file($export_dir . 'README.md', "# " . $module['name'] . "\n\nExported from SmartSchools on " . date('Y-m-d H:i:s') . ".\n");

        $zip_path = $this->storage_path . 'exports/' . $module_key . '_' . date('Ymd_His') . '.zip';
        $this->zipDirectory($export_dir, $zip_path);

        return $zip_path;
    }

    public function exportCoreModule($module_key) {
        $group = $this->CI->module_installer_model->getPermissionManifest($module_key);
        if (!$group) {
            throw new Exception('Application module was not found.');
        }

        $export_dir = $this->storage_path . 'exports/core_' . $module_key . '_' . date('Ymd_His') . '/';
        mkdir($export_dir, 0755, true);

        $manifest = array(
            'schema_version' => 1,
            'module' => array(
                'key' => $group['short_code'],
                'name' => $group['name'],
                'version' => '1.0.0',
                'description' => 'Registration-only export generated from an existing application module.',
                'author' => 'SmartSchools',
            ),
            'files' => array(),
            'database' => array(
                'install' => array(),
                'upgrade' => new stdClass(),
                'uninstall' => array(),
                'export_tables' => array(),
            ),
            'permissions' => array(
                'groups' => array($group),
            ),
            'menus' => array(),
        );

        write_file($export_dir . 'module.json', json_encode($manifest, JSON_PRETTY_PRINT));
        write_file($export_dir . 'README.md', "# " . $group['name'] . "\n\nThis is a registration-only export for module key `" . $group['short_code'] . "`.\n\nIt includes module and RBAC permission metadata only. Add PHP files and SQL to the ZIP manually if you need a complete portable module package.\n");

        $zip_path = $this->storage_path . 'exports/core_' . $module_key . '_' . date('Ymd_His') . '.zip';
        $this->zipDirectory($export_dir, $zip_path);

        return $zip_path;
    }

    public function uninstallModule($module_key) {
        $module = $this->CI->module_installer_model->getModule($module_key);
        if (!$module || $module['status'] === 'uninstalled') {
            throw new Exception('Module is not installed.');
        }

        $manifest = json_decode($module['manifest_json'], true);
        if (!is_array($manifest)) {
            throw new Exception('Stored module manifest is invalid.');
        }

        $this->CI->module_installer_model->addLog($module_key, 'info', 'Starting module uninstall.');
        $this->createDatabaseBackup($module_key . '_uninstall');

        $uninstall_sql = isset($manifest['database']['uninstall']) ? $manifest['database']['uninstall'] : array();
        if (!empty($uninstall_sql)) {
            $extract_dir = $this->extractStoredPackage($module);
            foreach ($uninstall_sql as $path) {
                $path = $this->normalizeRelativePath($path);
                $sql_abs = $extract_dir . $path;
                if (file_exists($sql_abs)) {
                    $this->executeSqlFile($sql_abs);
                    $this->CI->module_installer_model->addLog($module_key, 'info', 'Executed uninstall SQL ' . $path . '.');
                }
            }
        }

        $files = $this->CI->module_installer_model->getFiles($module_key);
        foreach ($files as $file) {
            $target = $this->normalizeRelativePath($file['target_path']);
            $target_abs = $this->base_path . $target;
            if (!file_exists($target_abs)) {
                continue;
            }

            $current = 'sha256:' . hash_file('sha256', $target_abs);
            if ($current !== $file['checksum']) {
                $this->CI->module_installer_model->addLog($module_key, 'warning', 'Skipped changed file during uninstall: ' . $target);
                continue;
            }

            $this->backupTargetFile($module_key . '_uninstall', $target);
            unlink($target_abs);
            $this->CI->module_installer_model->addLog($module_key, 'info', 'Removed file ' . $target . '.');
        }

        $this->CI->module_installer_model->replaceMenus($module_key, array());
        if (!empty($manifest['permissions']['groups'])) {
            foreach ($manifest['permissions']['groups'] as $group) {
                if (!empty($group['short_code'])) {
                    $this->CI->module_installer_model->removePermissionGroup($group['short_code']);
                }
            }
        }

        $this->CI->module_installer_model->setModuleStatus($module_key, 'uninstalled');
        $this->CI->module_installer_model->replaceFiles($module_key, array());
        $this->CI->module_installer_model->addLog($module_key, 'success', 'Module uninstalled successfully.');
    }

    private function validateZipEntries($zip) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', $zip->getNameIndex($i));
            if ($name === '' || strpos($name, "\0") !== false || strpos($name, '../') !== false || strpos($name, '..\\') !== false || preg_match('#^([a-zA-Z]:|/)#', $name)) {
                throw new Exception('Unsafe ZIP path found: ' . $name);
            }
        }
    }

    private function validateManifestBasics($manifest) {
        if (!isset($manifest['schema_version']) || (int) $manifest['schema_version'] !== 1) {
            throw new Exception('Unsupported module schema_version.');
        }

        if (empty($manifest['module']['key']) || empty($manifest['module']['name']) || empty($manifest['module']['version'])) {
            throw new Exception('module.key, module.name, and module.version are required.');
        }

        if (!preg_match('/^[a-z0-9_]+$/', $manifest['module']['key'])) {
            throw new Exception('module.key may contain only lowercase letters, numbers, and underscores.');
        }

        if (!empty($manifest['database']['install']) && !is_array($manifest['database']['install'])) {
            throw new Exception('database.install must be an array.');
        }

        if (!empty($manifest['database']['upgrade']) && !is_array($manifest['database']['upgrade'])) {
            throw new Exception('database.upgrade must be an object.');
        }
    }

    private function validateManifestFiles($manifest, $extract_dir) {
        $files = array();
        if (empty($manifest['files'])) {
            return $files;
        }

        foreach ($manifest['files'] as $file) {
            if (empty($file['source']) || empty($file['target']) || empty($file['checksum'])) {
                throw new Exception('Each file entry requires source, target, and checksum.');
            }

            $source = $this->normalizeRelativePath($file['source']);
            $target = $this->normalizeTargetPath($file['target']);
            if (strpos($source, 'files/') !== 0) {
                throw new Exception('File source must be under files/: ' . $source);
            }

            $source_abs = $extract_dir . $source;
            if (!file_exists($source_abs) || is_dir($source_abs)) {
                throw new Exception('Declared source file does not exist: ' . $source);
            }

            $actual_checksum = 'sha256:' . hash_file('sha256', $source_abs);
            if (!hash_equals(strtolower($file['checksum']), strtolower($actual_checksum))) {
                throw new Exception('Checksum mismatch for ' . $source . '.');
            }

            $target_abs = $this->base_path . $target;
            $status = 'new';
            if (file_exists($target_abs)) {
                $target_checksum = 'sha256:' . hash_file('sha256', $target_abs);
                if (strtolower($target_checksum) === strtolower($actual_checksum)) {
                    $status = 'unchanged';
                } elseif (empty($file['overwrite'])) {
                    throw new Exception('Target exists and overwrite is false: ' . $target);
                } else {
                    $status = 'overwrite';
                }
            }

            $files[] = array(
                'source' => $source,
                'target' => $target,
                'source_abs' => $source_abs,
                'target_abs' => $target_abs,
                'checksum' => $actual_checksum,
                'overwrite' => !empty($file['overwrite']),
                'status' => $status,
            );
        }

        return $files;
    }

    private function resolveSqlFiles($manifest, $current_version, $new_version, $extract_dir, &$action) {
        $sql_files = array();

        if (empty($manifest['database'])) {
            return $sql_files;
        }

        if ($current_version === null) {
            $action = 'install';
            foreach (isset($manifest['database']['install']) ? $manifest['database']['install'] : array() as $path) {
                $sql_files[] = $this->sqlFileInfo($path, 'install', $new_version, $extract_dir);
            }
            return $sql_files;
        }

        $action = 'upgrade';
        $upgrades = isset($manifest['database']['upgrade']) ? $manifest['database']['upgrade'] : array();
        ksort($upgrades, SORT_NATURAL);
        foreach ($upgrades as $version => $paths) {
            if (version_compare($version, $current_version, '>') && version_compare($version, $new_version, '<=')) {
                foreach ($paths as $path) {
                    $sql_files[] = $this->sqlFileInfo($path, 'upgrade:' . $version, $version, $extract_dir);
                }
            }
        }

        return $sql_files;
    }

    private function sqlFileInfo($path, $phase, $version, $extract_dir) {
        $path = $this->normalizeRelativePath($path);
        if (strpos($path, 'sql/') !== 0) {
            throw new Exception('SQL files must be under sql/: ' . $path);
        }

        $path_abs = $extract_dir . $path;
        if (!file_exists($path_abs) || is_dir($path_abs)) {
            throw new Exception('Declared SQL file does not exist: ' . $path);
        }

        return array(
            'path' => $path,
            'path_abs' => $path_abs,
            'phase' => $phase,
            'version' => $version,
            'migration_key' => $phase . ':' . $path,
            'checksum' => 'sha256:' . hash_file('sha256', $path_abs),
        );
    }

    private function createDatabaseBackup($module_key) {
        $this->CI->load->dbutil();
        $prefs = array(
            'ignore' => array(),
            'format' => 'txt',
            'filename' => 'module_installer_backup.sql',
            'add_drop' => true,
            'add_insert' => true,
            'newline' => "\n",
        );

        $backup = $this->CI->dbutil->backup($prefs);
        $file = $this->storage_path . 'database/db_' . $module_key . '_' . date('Ymd_His') . '.sql';
        write_file($file, $backup);

        return $file;
    }

    private function backupTargetFile($module_key, $target) {
        $target = $this->normalizeTargetPath($target);
        $source = $this->base_path . $target;
        $backup = $this->storage_path . 'files/' . $module_key . '_' . date('Ymd_His') . '/' . $target;
        $this->copyFile($source, $backup);
        return $backup;
    }

    private function copyFile($source, $target) {
        $dir = dirname($target);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!copy($source, $target)) {
            throw new Exception('Unable to copy file to ' . str_replace($this->base_path, '', str_replace('\\', '/', $target)) . '.');
        }
    }

    private function fileRecord($module_key, $file, $backup_path) {
        return array(
            'module_key' => $module_key,
            'source_path' => $file['source'],
            'target_path' => $file['target'],
            'checksum' => $file['checksum'],
            'backup_path' => $backup_path,
            'installed_at' => date('Y-m-d H:i:s'),
        );
    }

    private function mergeFileRecords($module_key, $new_files) {
        $merged = array();
        $existing = $this->CI->module_installer_model->getFiles($module_key);

        foreach ($existing as $file) {
            $merged[$file['target_path']] = array(
                'module_key' => $module_key,
                'source_path' => $file['source_path'],
                'target_path' => $file['target_path'],
                'checksum' => $file['checksum'],
                'backup_path' => $file['backup_path'],
                'installed_at' => $file['installed_at'],
            );
        }

        foreach ($new_files as $file) {
            $merged[$file['target_path']] = $file;
        }

        return array_values($merged);
    }

    private function executeSqlFile($path) {
        $sql = file_get_contents($path);
        if (stripos($sql, 'DELIMITER') !== false) {
            throw new Exception('DELIMITER SQL scripts are not supported: ' . basename($path));
        }

        $statements = $this->splitSqlStatements($sql);
        foreach ($statements as $statement) {
            $trimmed = trim($statement);
            if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) {
                continue;
            }

            if (!$this->CI->db->query($trimmed)) {
                $error = $this->CI->db->error();
                throw new Exception('SQL failed in ' . basename($path) . ': ' . (isset($error['message']) ? $error['message'] : 'unknown error'));
            }
        }
    }

    private function splitSqlStatements($sql) {
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
        $statements = array();
        $buffer = '';
        $quote = null;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

            if ($quote === null && $char === '-' && $next === '-') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $buffer .= $sql[$i];
                    $i++;
                }
                continue;
            }

            if ($quote === null && $char === '#') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $buffer .= $sql[$i];
                    $i++;
                }
                continue;
            }

            if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
                if ($quote === null) {
                    $quote = $char;
                } elseif ($quote === $char) {
                    $quote = null;
                }
            }

            if ($char === ';' && $quote === null) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    private function registerPermissions($manifest) {
        if (empty($manifest['permissions']['groups'])) {
            return;
        }

        foreach ($manifest['permissions']['groups'] as $group) {
            if (empty($group['short_code']) || empty($group['name'])) {
                throw new Exception('Permission groups require short_code and name.');
            }

            $group_id = $this->CI->module_installer_model->upsertPermissionGroup($group);
            $this->CI->module_installer_model->upsertPermissionStudent($group_id, $group);
            if (!empty($group['categories'])) {
                foreach ($group['categories'] as $category) {
                    $this->CI->module_installer_model->upsertPermissionCategory($group_id, $category);
                }
            }
        }
    }

    private function registerMenus($module_key, $manifest) {
        $menus = array();
        if (!empty($manifest['menus'])) {
            foreach ($manifest['menus'] as $menu) {
                if (empty($menu['label']) || empty($menu['url'])) {
                    throw new Exception('Menus require label and url.');
                }

                $menus[] = array(
                    'module_key' => $module_key,
                    'audience' => isset($menu['audience']) ? $menu['audience'] : 'admin',
                    'parent' => isset($menu['parent']) ? $menu['parent'] : 'External Modules',
                    'label' => $menu['label'],
                    'url' => ltrim($menu['url'], '/'),
                    'icon' => isset($menu['icon']) ? $menu['icon'] : 'fa fa-plug',
                    'permission' => isset($menu['permission']) ? $menu['permission'] : null,
                    'sort_order' => isset($menu['sort_order']) ? (int) $menu['sort_order'] : 0,
                    'active' => 1,
                );
            }
        }

        $this->CI->module_installer_model->replaceMenus($module_key, $menus);
    }

    private function buildExportSql($tables) {
        if (empty($tables)) {
            return '';
        }

        $sql = "-- Exported module data\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        foreach ($tables as $table) {
            if (empty($table['name'])) {
                continue;
            }

            $name = $table['name'];
            if (!preg_match('/^[A-Za-z0-9_]+$/', $name) || !$this->CI->db->table_exists($name)) {
                continue;
            }

            $create = $this->CI->db->query('SHOW CREATE TABLE `' . $name . '`')->row_array();
            $create_sql = isset($create['Create Table']) ? $create['Create Table'] : array_values($create)[1];
            $sql .= "DROP TABLE IF EXISTS `" . $name . "`;\n" . $create_sql . ";\n\n";

            $mode = isset($table['mode']) ? $table['mode'] : 'schema';
            if ($mode === 'schema_data') {
                $rows = $this->CI->db->get($name)->result_array();
                foreach ($rows as $row) {
                    $columns = array();
                    $values = array();
                    foreach ($row as $column => $value) {
                        $columns[] = '`' . str_replace('`', '``', $column) . '`';
                        $values[] = is_null($value) ? 'NULL' : $this->CI->db->escape($value);
                    }
                    $sql .= 'INSERT INTO `' . $name . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        return $sql . "SET FOREIGN_KEY_CHECKS=1;\n";
    }

    private function zipDirectory($source_dir, $zip_path) {
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Unable to create export ZIP.');
        }

        $source_dir = rtrim(str_replace('\\', '/', $source_dir), '/') . '/';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            $local = substr($path, strlen($source_dir));
            if ($file->isDir()) {
                $zip->addEmptyDir($local);
            } else {
                $zip->addFile($path, $local);
            }
        }

        $zip->close();
    }

    private function extractStoredPackage($module) {
        if (empty($module['package_path']) || !file_exists($module['package_path'])) {
            throw new Exception('Original module package is not available for uninstall SQL.');
        }

        $zip = new ZipArchive();
        if ($zip->open($module['package_path']) !== true) {
            throw new Exception('Unable to open original module package.');
        }

        $this->validateZipEntries($zip);
        $extract_dir = $this->storage_path . 'extract/uninstall_' . $module['module_key'] . '_' . date('Ymd_His') . '/';
        mkdir($extract_dir, 0755, true);
        $zip->extractTo($extract_dir);
        $zip->close();

        return $extract_dir;
    }

    private function reanalyzePreservingAction($preview) {
        $next = $this->analyzePackage($preview['package_path']);
        if ($next['module_key'] !== $preview['module_key']) {
            throw new Exception('Package module key changed between preview and install.');
        }

        return $next;
    }

    private function restoreCopiedFiles($files) {
        foreach (array_reverse($files) as $file) {
            if (!empty($file['backup_path']) && file_exists($file['backup_path'])) {
                $this->copyFile($file['backup_path'], $file['target_abs']);
            } elseif (file_exists($file['target_abs'])) {
                unlink($file['target_abs']);
            }
        }
    }

    private function normalizeRelativePath($path) {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '' || strpos($path, "\0") !== false || preg_match('#^([a-zA-Z]:|/)#', $path) || strpos($path, '../') !== false || $path === '..' || strpos($path, './') === 0) {
            throw new Exception('Unsafe relative path: ' . $path);
        }

        return $path;
    }

    private function normalizeTargetPath($path) {
        $path = $this->normalizeRelativePath($path);
        foreach ($this->allowed_targets as $root) {
            if (strpos($path, $root) === 0) {
                return $path;
            }
        }

        throw new Exception('Target path is not allowed: ' . $path);
    }
}
