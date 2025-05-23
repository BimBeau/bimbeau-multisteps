<?php
/**
 * Plugin Name: Instagram Feed BimBeau
 * Description: Récupère les derniers posts Instagram.
 * Version: 1.2.3
 * Author: BimBeau
 */

// Enregistrement des hooks d'activation et de désactivation
register_activation_hook(__FILE__, 'instagram_feed_bb_activate');
register_deactivation_hook(__FILE__, 'instagram_feed_bb_deactivate');

// Déclaration de la taille d'image spécifique pour Instagram
add_action('after_setup_theme', function() {
    // Ajoute la taille d'image carrée croppée 600x600
    add_image_size('600x600c', 600, 600, true);
});

// Include du CSS
function instagram_feed_bb_enqueue_styles() {
    wp_enqueue_style('instagram-feed-style', plugin_dir_url(__FILE__) . '/assets/css/instagram-feed-bb.css');
}
add_action('wp_enqueue_scripts', 'instagram_feed_bb_enqueue_styles');

// Inclusion des fonctionnalités principales
require_once plugin_dir_path(__FILE__) . 'instagram-feed-bb-settings.php';
require_once plugin_dir_path(__FILE__) . 'instagram-feed-bb-custompost.php';
require_once plugin_dir_path(__FILE__) . 'instagram-feed-bb-render.php';
require_once plugin_dir_path(__FILE__) . 'instagram-feed-bb-cron.php';
?>
