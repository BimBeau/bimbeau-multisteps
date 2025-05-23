<?php
/**
 * Plugin Name: BimStats - Post View and Time Tracker
 * Description: Plugin pour compter le nombre de vues et le temps moyen passé par post.
 * Version: 2.0
 * Author: BimBeau
 */

if (!defined('ABSPATH')) {
    exit; // Empêche l'accès direct
}

// Activer ou désactiver les logs
define('BIMSTATS_LOG_ENABLED', false); // Mettre à false pour désactiver les logs

// Charger les dépendances
require_once plugin_dir_path(__FILE__) . 'includes/class-bimstats.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-bimstats-views.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-bimstats-time.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-bimstats-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-bimstats-utils.php';

// Initialiser le plugin
function bimstats_init() {
    BimStats::get_instance();
    BimStats_Utils::init_hooks(); // Appel de l'initialisation des hooks ici
}
add_action('plugins_loaded', 'bimstats_init');
