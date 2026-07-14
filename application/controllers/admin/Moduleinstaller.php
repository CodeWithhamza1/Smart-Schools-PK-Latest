<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Moduleinstaller extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }

        $this->load->model('module_installer_model');
        $this->load->library('module_installer_lib');
        $this->load->helper(array('download', 'string'));
        $this->module_installer_model->ensureTables();
        $this->module_installer_lib->prepareStorage();
    }

    public function index() {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/moduleinstaller');

        $data = array(
            'modules' => $this->module_installer_model->getModules(),
            'core_modules' => $this->module_installer_model->getCoreModules(),
            'logs' => $this->module_installer_model->getLogs(null, 30),
        );

        $this->load->view('layout/header');
        $this->load->view('admin/moduleinstaller/index', $data);
        $this->load->view('layout/footer');
    }

    public function upload() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('admin/moduleinstaller');
        }

        try {
            $file = isset($_FILES['module_package']) ? $_FILES['module_package'] : array();
            $package_path = $this->module_installer_lib->storeUploadedPackage($file);
            $preview = $this->module_installer_lib->analyzePackage($package_path);
            $token = random_string('alnum', 24);

            $previews = $this->session->userdata('module_installer_previews');
            if (!is_array($previews)) {
                $previews = array();
            }
            $previews[$token] = $preview;
            $this->session->set_userdata('module_installer_previews', $previews);

            redirect('admin/moduleinstaller/preview/' . $token);
        } catch (Exception $e) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($e->getMessage()) . '</div>');
            redirect('admin/moduleinstaller');
        }
    }

    public function preview($token) {
        $preview = $this->getPreview($token);
        if (!$preview) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Module preview expired. Please upload the package again.</div>');
            redirect('admin/moduleinstaller');
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/moduleinstaller');

        $data = array('token' => $token, 'preview' => $preview);
        $this->load->view('layout/header');
        $this->load->view('admin/moduleinstaller/preview', $data);
        $this->load->view('layout/footer');
    }

    public function install($token) {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('admin/moduleinstaller');
        }

        $preview = $this->getPreview($token);
        if (!$preview) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Module preview expired. Please upload the package again.</div>');
            redirect('admin/moduleinstaller');
        }

        try {
            $result = $this->module_installer_lib->installFromPreview($preview);
            $this->removePreview($token);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . html_escape($result['message']) . '</div>');
        } catch (Exception $e) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($e->getMessage()) . '</div>');
        }

        redirect('admin/moduleinstaller/logs/' . rawurlencode($preview['module_key']));
    }

    public function logs($module_key) {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/moduleinstaller');

        $data = array(
            'module' => $this->module_installer_model->getModule($module_key),
            'logs' => $this->module_installer_model->getLogs($module_key, 200),
        );

        $this->load->view('layout/header');
        $this->load->view('admin/moduleinstaller/logs', $data);
        $this->load->view('layout/footer');
    }

    public function export($module_key) {
        try {
            $zip_path = $this->module_installer_lib->exportModule($module_key);
            force_download(basename($zip_path), file_get_contents($zip_path));
        } catch (Exception $e) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($e->getMessage()) . '</div>');
            redirect('admin/moduleinstaller');
        }
    }

    public function exportcore($module_key) {
        try {
            $zip_path = $this->module_installer_lib->exportCoreModule($module_key);
            force_download(basename($zip_path), file_get_contents($zip_path));
        } catch (Exception $e) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($e->getMessage()) . '</div>');
            redirect('admin/moduleinstaller');
        }
    }

    public function disable($module_key) {
        $this->module_installer_model->setModuleStatus($module_key, 'disabled');
        $this->module_installer_model->addLog($module_key, 'info', 'Module disabled.');
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Module disabled.</div>');
        redirect('admin/moduleinstaller');
    }

    public function enable($module_key) {
        $this->module_installer_model->setModuleStatus($module_key, 'enabled');
        $this->module_installer_model->addLog($module_key, 'info', 'Module enabled.');
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Module enabled.</div>');
        redirect('admin/moduleinstaller');
    }

    public function uninstall($module_key) {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('admin/moduleinstaller');
        }

        try {
            $this->module_installer_lib->uninstallModule($module_key);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Module uninstalled.</div>');
        } catch (Exception $e) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($e->getMessage()) . '</div>');
        }

        redirect('admin/moduleinstaller');
    }

    private function getPreview($token) {
        $previews = $this->session->userdata('module_installer_previews');
        if (!is_array($previews) || !isset($previews[$token])) {
            return null;
        }

        return $previews[$token];
    }

    private function removePreview($token) {
        $previews = $this->session->userdata('module_installer_previews');
        if (is_array($previews) && isset($previews[$token])) {
            unset($previews[$token]);
            $this->session->set_userdata('module_installer_previews', $previews);
        }
    }
}
