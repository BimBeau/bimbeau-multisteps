<?php

// Classe principale du plugin. Elle se charge d'initialiser le plugin, d'enregistrer les actions et les filtres, et de gérer les dépendances.

if (!defined('ABSPATH')) {
    exit;
}

class BimStats {

    private static $instance = null;

    private function __construct() {
        $this->load_dependencies();
        $this->register_actions();
        $this->register_filters();
    }

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'class-bimstats-views.php';
        require_once plugin_dir_path(__FILE__) . 'class-bimstats-time.php';
        require_once plugin_dir_path(__FILE__) . 'class-bimstats-admin.php';
    }

    private function register_actions() {
        BimStats_Views::register_view_actions();
        BimStats_Time::register_time_actions();
        BimStats_Admin::register_admin_actions();
    }

    private function register_filters() {
        BimStats_Admin::register_admin_filters();
    }
}
