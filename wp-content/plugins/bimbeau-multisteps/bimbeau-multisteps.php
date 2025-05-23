<?php
/*
Plugin Name: BimBeau MultiSteps
Description: Convertit le formulaire d'estimation multi-étapes en plugin administrable.
Version: 1.0.0
Author: BimBeau
*/

if (!defined('ABSPATH')) {
    exit;
}

// Démarrage de la session si nécessaire
add_action('init', function() {
    if (!session_id()) {
        session_start();
    }
});

// Définition des constantes du plugin
define('BIMBEAU_MS_DIR', plugin_dir_path(__FILE__));
define('BIMBEAU_MS_URL', plugin_dir_url(__FILE__));

if (is_admin()) require_once BIMBEAU_MS_DIR . 'includes/admin/admin-settings.php';
// Enqueue front-end assets
function bimbeau_ms_enqueue_assets() {
    wp_enqueue_style(
        'bimbeau-ms-style',
        BIMBEAU_MS_URL . 'assets/css/estimation-form.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'bimbeau-ms-form-interactions',
        BIMBEAU_MS_URL . 'assets/js/form-interactions.js',
        ['jquery'],
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'bimbeau_ms_enqueue_assets');

// Chargement du coeur du formulaire après l'initialisation de WordPress
function bimbeau_ms_load_core() {
    require_once BIMBEAU_MS_DIR . 'includes/forms/estimation-form.php';
}
add_action('init', 'bimbeau_ms_load_core');


// Définition des options par défaut à l'activation
register_activation_hook(__FILE__, function() {
    add_option('bimbeau_ms_mode', 'PROD');
    add_option('bimbeau_ms_payment_link', 'https://buy.stripe.com/14k5mzfDf86f7U4cO6');
    add_option('bimbeau_ms_payment_link_test', 'https://buy.stripe.com/test_bIY2bbckteyjgbm4gg');
    add_option('bimbeau_ms_secret_key', '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');
    add_option('bimbeau_ms_secret_key_test', 'sk_test_51JUCdyHKX5FyumXs1WF9dsIgDPgJu2a05VtBgspxxA86CDwrkGy3cPadlSXx9LyZhP5iDitOcQ8m62dvEgsWESoT007cVCjJiA');
    add_option('bimbeau_ms_admin_email', 'hello@secretdeco.fr');
    add_option('bimbeau_ms_menu_label', 'BimBeau MultiSteps');
    add_option('bimbeau_ms_menu_icon', 'dashicons-admin-generic');
});

