<?php

/**
 * Plugin Name: SC Progress Circle
 * Description: Un plugin pour créer un cercle de progression avec shortcode.
 * Version: 1.0
 * Author: Slaaap
 */


// Shortcode pour Cercle de Progression [sc_progress_circle current="x" total="y"]
function sc_progress_circle_shortcode($atts) {
    // Attributs par défaut du shortcode
    $atts = shortcode_atts(array(
        'current' => '1', // Étape actuelle
        'total' => '10',  // Nombre total d'étapes
    ), $atts, 'sc_progress_circle');

    // Circonférence du cercle (2 * π * r) avec r = 45
    $circumference = 283;

    // Calcul du stroke-dashoffset pour l'étape actuelle et l'étape précédente
    $progress_current = (intval($atts['current']) / intval($atts['total'])) * $circumference;
    $progress_previous = (intval($atts['current']) - 1) / intval($atts['total']) * $circumference;
    $stroke_dashoffset_current = $circumference - $progress_current;
    $stroke_dashoffset_previous = $circumference - $progress_previous;

    // Construction du HTML du shortcode
    $output = '
    <div class="sc-progress-circle" data-progress-current="' . $stroke_dashoffset_current . '" data-progress-previous="' . $stroke_dashoffset_previous . '">
        <svg class="sc-circle-svg" viewBox="0 0 100 100">
            <circle class="sc-circle-bg" cx="50" cy="50" r="45"></circle>
            <circle class="sc-circle" cx="50" cy="50" r="45" style="stroke-dashoffset: ' . $stroke_dashoffset_previous . ';"></circle>
        </svg>
        <span class="sc-progress-text">' . esc_html($atts['current']) . '<span class="separator">/</span>' . esc_html($atts['total']) . '</span>
    </div>';

    return $output;
}

function sc_progress_circle_enqueue_styles() {
    wp_enqueue_style('sc-progress-circle-style', plugin_dir_url(__FILE__) . 'sc-progress-circle.css');
    wp_enqueue_script('sc-progress-circle-script', plugin_dir_url(__FILE__) . 'sc-progress-circle.js', array('jquery'), null, true);
}

add_shortcode('sc_progress_circle', 'sc_progress_circle_shortcode');
add_action('wp_enqueue_scripts', 'sc_progress_circle_enqueue_styles');
