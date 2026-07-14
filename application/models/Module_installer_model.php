<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Module_installer_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function ensureTables() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `module_installer_modules` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `module_key` varchar(100) NOT NULL,
            `name` varchar(191) NOT NULL,
            `version` varchar(50) NOT NULL,
            `description` text NULL,
            `author` varchar(191) NULL,
            `status` enum('enabled','disabled','uninstalled') NOT NULL DEFAULT 'enabled',
            `manifest_json` mediumtext NOT NULL,
            `package_path` varchar(500) NULL,
            `package_hash` varchar(100) NULL,
            `db_backup_path` varchar(500) NULL,
            `installed_by` int(11) NULL,
            `installed_at` datetime NULL,
            `updated_at` datetime NULL,
            `last_error` text NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `module_key` (`module_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `module_installer_files` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `module_key` varchar(100) NOT NULL,
            `source_path` varchar(500) NOT NULL,
            `target_path` varchar(500) NOT NULL,
            `checksum` varchar(100) NOT NULL,
            `backup_path` varchar(500) NULL,
            `installed_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `module_key` (`module_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `module_installer_migrations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `module_key` varchar(100) NOT NULL,
            `version` varchar(50) NOT NULL,
            `migration_key` varchar(500) NOT NULL,
            `checksum` varchar(100) NOT NULL,
            `executed_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `module_migration` (`module_key`, `migration_key`(120))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `module_installer_menus` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `module_key` varchar(100) NOT NULL,
            `audience` varchar(50) NOT NULL DEFAULT 'admin',
            `parent` varchar(191) NOT NULL DEFAULT 'External Modules',
            `label` varchar(191) NOT NULL,
            `url` varchar(500) NOT NULL,
            `icon` varchar(100) NULL,
            `permission` varchar(100) NULL,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `active` tinyint(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `module_key` (`module_key`),
            KEY `audience_active` (`audience`, `active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `module_installer_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `module_key` varchar(100) NULL,
            `level` varchar(20) NOT NULL DEFAULT 'info',
            `message` text NOT NULL,
            `context_json` text NULL,
            `created_by` int(11) NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `module_key` (`module_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    public function getModules() {
        if (!$this->db->table_exists('module_installer_modules')) {
            return array();
        }

        return $this->db->order_by('name', 'asc')->get('module_installer_modules')->result_array();
    }

    public function getCoreModules() {
        if (!$this->db->table_exists('permission_group')) {
            return array();
        }

        $this->db->select('permission_group.*');
        $this->db->from('permission_group');
        if ($this->db->field_exists('system', 'permission_group')) {
            $this->db->where('system', 0);
        }
        $this->db->order_by('permission_group.name', 'asc');
        $modules = $this->db->get()->result_array();

        foreach ($modules as $key => $module) {
            $modules[$key]['category_count'] = $this->db->where('perm_group_id', $module['id'])->count_all_results('permission_category');
            $installed = $this->getModule($module['short_code']);
            $modules[$key]['installer_status'] = $installed ? $installed['status'] : null;
        }

        return $modules;
    }

    public function getPermissionManifest($module_key) {
        if (!$this->db->table_exists('permission_group')) {
            return null;
        }

        $group = $this->db->where('short_code', $module_key)->get('permission_group')->row_array();
        if (!$group) {
            return null;
        }

        $student = array();
        if ($this->db->table_exists('permission_student')) {
            $student = $this->db->where('group_id', $group['id'])->get('permission_student')->row_array();
        }

        $categories = $this->db->where('perm_group_id', $group['id'])->order_by('id', 'asc')->get('permission_category')->result_array();
        $manifest_categories = array();
        foreach ($categories as $category) {
            $manifest_categories[] = array(
                'short_code' => $this->valueOrDefault($category, 'short_code', $group['short_code']),
                'name' => $this->valueOrDefault($category, 'name', $group['name']),
                'enable_view' => (int) $this->valueOrDefault($category, 'enable_view', 1),
                'enable_add' => (int) $this->valueOrDefault($category, 'enable_add', 0),
                'enable_edit' => (int) $this->valueOrDefault($category, 'enable_edit', 0),
                'enable_delete' => (int) $this->valueOrDefault($category, 'enable_delete', 0),
            );
        }

        return array(
            'short_code' => $group['short_code'],
            'name' => $group['name'],
            'system' => (int) $this->valueOrDefault($group, 'system', 0),
            'is_active' => (int) $this->valueOrDefault($group, 'is_active', 1),
            'student' => (int) $this->valueOrDefault($student, 'student', 0),
            'parent' => (int) $this->valueOrDefault($student, 'parent', 0),
            'categories' => $manifest_categories,
        );
    }

    public function getModule($module_key) {
        if (!$this->db->table_exists('module_installer_modules')) {
            return null;
        }

        return $this->db->where('module_key', $module_key)->get('module_installer_modules')->row_array();
    }

    public function saveModule($data) {
        $existing = $this->getModule($data['module_key']);
        $now = date('Y-m-d H:i:s');
        $data['updated_at'] = $now;

        if ($existing) {
            $this->db->where('module_key', $data['module_key'])->update('module_installer_modules', $data);
            return $existing['id'];
        }

        $data['installed_at'] = $now;
        $this->db->insert('module_installer_modules', $data);
        return $this->db->insert_id();
    }

    public function setModuleStatus($module_key, $status, $error = null) {
        $data = array(
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        if ($error !== null) {
            $data['last_error'] = $error;
        }

        $this->db->where('module_key', $module_key)->update('module_installer_modules', $data);
        $this->db->where('module_key', $module_key)->update('module_installer_menus', array('active' => ($status === 'enabled') ? 1 : 0));
    }

    public function replaceFiles($module_key, $files) {
        $this->db->where('module_key', $module_key)->delete('module_installer_files');

        if (!empty($files)) {
            $this->db->insert_batch('module_installer_files', $files);
        }
    }

    public function getFiles($module_key) {
        if (!$this->db->table_exists('module_installer_files')) {
            return array();
        }

        return $this->db->where('module_key', $module_key)->order_by('target_path', 'asc')->get('module_installer_files')->result_array();
    }

    public function hasMigration($module_key, $migration_key) {
        if (!$this->db->table_exists('module_installer_migrations')) {
            return false;
        }

        return $this->db->where('module_key', $module_key)
            ->where('migration_key', $migration_key)
            ->count_all_results('module_installer_migrations') > 0;
    }

    public function recordMigration($module_key, $version, $migration_key, $checksum) {
        if ($this->hasMigration($module_key, $migration_key)) {
            return;
        }

        $this->db->insert('module_installer_migrations', array(
            'module_key' => $module_key,
            'version' => $version,
            'migration_key' => $migration_key,
            'checksum' => $checksum,
            'executed_at' => date('Y-m-d H:i:s'),
        ));
    }

    public function replaceMenus($module_key, $menus) {
        $this->db->where('module_key', $module_key)->delete('module_installer_menus');

        if (!empty($menus)) {
            $this->db->insert_batch('module_installer_menus', $menus);
        }
    }

    public function getActiveMenus($audience = 'admin') {
        if (!$this->db->table_exists('module_installer_menus') || !$this->db->table_exists('module_installer_modules')) {
            return array();
        }

        $this->db->select('module_installer_menus.*, module_installer_modules.status');
        $this->db->from('module_installer_menus');
        $this->db->join('module_installer_modules', 'module_installer_modules.module_key = module_installer_menus.module_key');
        $this->db->where('module_installer_menus.audience', $audience);
        $this->db->where('module_installer_menus.active', 1);
        $this->db->where('module_installer_modules.status', 'enabled');
        $this->db->order_by('module_installer_menus.parent', 'asc');
        $this->db->order_by('module_installer_menus.sort_order', 'asc');
        $this->db->order_by('module_installer_menus.label', 'asc');

        return $this->db->get()->result_array();
    }

    public function upsertPermissionGroup($group) {
        $short_code = $group['short_code'];
        $data = $this->filterFields('permission_group', array(
            'name' => $group['name'],
            'short_code' => $short_code,
            'is_active' => isset($group['is_active']) ? (int) $group['is_active'] : 1,
            'system' => isset($group['system']) ? (int) $group['system'] : 0,
        ));

        $existing = $this->db->where('short_code', $short_code)->get('permission_group')->row_array();
        if ($existing) {
            $this->db->where('id', $existing['id'])->update('permission_group', $data);
            return $existing['id'];
        }

        $this->db->insert('permission_group', $data);
        return $this->db->insert_id();
    }

    public function upsertPermissionStudent($group_id, $group) {
        if (!$this->db->table_exists('permission_student')) {
            return;
        }

        $short_code = $group['short_code'];
        $data = $this->filterFields('permission_student', array(
            'group_id' => $group_id,
            'name' => $group['name'],
            'short_code' => $short_code,
            'student' => isset($group['student']) ? (int) $group['student'] : 0,
            'parent' => isset($group['parent']) ? (int) $group['parent'] : 0,
        ));

        $existing = $this->db->where('short_code', $short_code)->get('permission_student')->row_array();
        if ($existing) {
            $this->db->where('id', $existing['id'])->update('permission_student', $data);
            return;
        }

        $this->db->insert('permission_student', $data);
    }

    public function upsertPermissionCategory($group_id, $category) {
        $short_code = $category['short_code'];
        $data = $this->filterFields('permission_category', array(
            'perm_group_id' => $group_id,
            'name' => $category['name'],
            'short_code' => $short_code,
            'enable_view' => isset($category['enable_view']) ? (int) $category['enable_view'] : 1,
            'enable_add' => isset($category['enable_add']) ? (int) $category['enable_add'] : 0,
            'enable_edit' => isset($category['enable_edit']) ? (int) $category['enable_edit'] : 0,
            'enable_delete' => isset($category['enable_delete']) ? (int) $category['enable_delete'] : 0,
        ));

        $existing = $this->db->where('short_code', $short_code)->get('permission_category')->row_array();
        if ($existing) {
            $this->db->where('id', $existing['id'])->update('permission_category', $data);
            return $existing['id'];
        }

        $this->db->insert('permission_category', $data);
        return $this->db->insert_id();
    }

    public function removePermissionGroup($short_code) {
        $group = $this->db->where('short_code', $short_code)->get('permission_group')->row_array();
        if (!$group) {
            return;
        }

        $categories = $this->db->select('id')->where('perm_group_id', $group['id'])->get('permission_category')->result_array();
        if (!empty($categories)) {
            $category_ids = array();
            foreach ($categories as $category) {
                $category_ids[] = $category['id'];
            }
            $this->db->where_in('perm_cat_id', $category_ids)->delete('roles_permissions');
            $this->db->where_in('id', $category_ids)->delete('permission_category');
        }

        if ($this->db->table_exists('permission_student')) {
            $this->db->where('group_id', $group['id'])->delete('permission_student');
        }

        $this->db->where('id', $group['id'])->delete('permission_group');
    }

    public function addLog($module_key, $level, $message, $context = array()) {
        if (!$this->db->table_exists('module_installer_logs')) {
            return;
        }

        $this->db->insert('module_installer_logs', array(
            'module_key' => $module_key,
            'level' => $level,
            'message' => $message,
            'context_json' => empty($context) ? null : json_encode($context),
            'created_by' => $this->customlib->getStaffID(),
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    public function getLogs($module_key = null, $limit = 100) {
        if (!$this->db->table_exists('module_installer_logs')) {
            return array();
        }

        if ($module_key !== null) {
            $this->db->where('module_key', $module_key);
        }

        return $this->db->order_by('id', 'desc')->limit($limit)->get('module_installer_logs')->result_array();
    }

    private function filterFields($table, $data) {
        $filtered = array();

        foreach ($data as $field => $value) {
            if ($this->db->field_exists($field, $table)) {
                $filtered[$field] = $value;
            }
        }

        return $filtered;
    }

    private function valueOrDefault($array, $key, $default) {
        return (is_array($array) && array_key_exists($key, $array)) ? $array[$key] : $default;
    }
}
