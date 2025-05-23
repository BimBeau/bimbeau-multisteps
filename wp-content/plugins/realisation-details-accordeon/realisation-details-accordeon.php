<?php

/**
 * Plugin Name: Realisation Details Accordeon
 * Description: Un plugin pour afficher un accordéon des détails de réalisation.
 * Version: 1.0
 * Author: Slaaap
 */

// Ajoute le shortcode
include(plugin_dir_path(__FILE__) . 'shortcode.php');

// Enregistre et charge les fichiers CSS
function realisation_details_accordeon_styles() {
    wp_enqueue_style('realisation-details-accordeon-css', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('wp_enqueue_scripts', 'realisation_details_accordeon_styles');

// Enregistre et charge les fichiers JavaScript
function realisation_details_accordeon_scripts() {
    wp_enqueue_script('realisation-details-accordeon-js', plugin_dir_url(__FILE__) . 'accordeon.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'realisation_details_accordeon_scripts');
