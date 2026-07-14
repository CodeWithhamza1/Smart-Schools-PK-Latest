<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Module_lib {

    private $allModules = array();
    protected $modules;
    var $perm_category;

    function __construct() {
        $this->CI = & get_instance();
        $this->modules = array();
        self::loadModule(); //Initiate the userroles
    }

    function loadModule() {
        $this->allModules = $this->CI->Module_model->get();

        if (!empty($this->allModules)) {
            foreach ($this->allModules as $mod_key => $mod_value) {

                if ($mod_value->is_active == 1) {
                    $this->modules[$mod_value->short_code] = true;
                } else {

                    $this->modules[$mod_value->short_code] = false;
                }
            }
        }
    }

    function hasActive($module = null) {

        if (!empty($this->modules[$module])) {
            return true;
        }

        return false;
    }

    function hasModule($module_shortcode) {

        $count = $this->CI->Module_model->hasModule($module_shortcode);

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getExternalMenus($audience = 'admin') {
        if (!isset($this->CI->db)) {
            return array();
        }

        if (!$this->CI->db->table_exists('module_installer_menus') || !$this->CI->db->table_exists('module_installer_modules')) {
            return array();
        }

        $this->CI->db->select('module_installer_menus.*, module_installer_modules.status');
        $this->CI->db->from('module_installer_menus');
        $this->CI->db->join('module_installer_modules', 'module_installer_modules.module_key = module_installer_menus.module_key');
        $this->CI->db->where('module_installer_menus.audience', $audience);
        $this->CI->db->where('module_installer_menus.active', 1);
        $this->CI->db->where('module_installer_modules.status', 'enabled');
        $this->CI->db->order_by('module_installer_menus.parent', 'asc');
        $this->CI->db->order_by('module_installer_menus.sort_order', 'asc');
        $this->CI->db->order_by('module_installer_menus.label', 'asc');

        return $this->CI->db->get()->result_array();
    }

}
