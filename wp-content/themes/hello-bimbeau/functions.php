<?php

/**
 * Theme functions and definitions
 *
 * @package HelloBimBeau
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('hello_bimbeau_VERSION', '2.9.0');

if (!isset($content_width)) {
	$content_width = 800; // Pixels.
}

if (!function_exists('hello_bimbeau_setup')) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_bimbeau_setup() {
		if (is_admin()) {
			hello_maybe_update_theme_version_in_db();
		}

		if (apply_filters('hello_bimbeau_register_menus', true)) {
			register_nav_menus(['menu-1' => esc_html__('Header', 'hello-bimbeau')]);
			register_nav_menus(['menu-2' => esc_html__('Footer', 'hello-bimbeau')]);
		}

		if (apply_filters('hello_bimbeau_post_type_support', true)) {
			add_post_type_support('page', 'excerpt');
		}

		if (apply_filters('hello_bimbeau_add_theme_support', true)) {
			add_theme_support('post-thumbnails');
			add_theme_support('automatic-feed-links');
			add_theme_support('title-tag');
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style('classic-editor.css');
			add_editor_style('assets/css/tinymce.css');

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support('align-wide');

			/*
			 * WooCommerce.
			 */
			if (apply_filters('hello_bimbeau_add_woocommerce_support', true)) {
				// WooCommerce in general.
				add_theme_support('woocommerce');
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support('wc-product-gallery-zoom');
				// lightbox.
				add_theme_support('wc-product-gallery-lightbox');
				// swipe.
				add_theme_support('wc-product-gallery-slider');
			}
		}
	}
}
add_action('after_setup_theme', 'hello_bimbeau_setup');

function hello_maybe_update_theme_version_in_db() {
	/*
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option($theme_version_option_name);

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if (!$hello_theme_db_version || version_compare($hello_theme_db_version, hello_bimbeau_VERSION, '<')) {
		update_option($theme_version_option_name, hello_bimbeau_VERSION);
	}
	*/
}

if (!function_exists('hello_bimbeau_scripts_styles')) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_bimbeau_scripts_styles() {
		$min_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		if (apply_filters('hello_bimbeau_enqueue_style', true)) {
			wp_enqueue_style(
				'hello-bimbeau',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				hello_bimbeau_VERSION
			);
		}

		if (apply_filters('hello_bimbeau_enqueue_theme_style', true)) {
			wp_enqueue_style(
				'hello-bimbeau-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				hello_bimbeau_VERSION
			);
		}
	}
}
add_action('wp_enqueue_scripts', 'hello_bimbeau_scripts_styles');

if (!function_exists('hello_bimbeau_register_elementor_locations')) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_bimbeau_register_elementor_locations($elementor_theme_manager) {
		if (apply_filters('hello_bimbeau_register_elementor_locations', true)) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action('elementor/theme/register_locations', 'hello_bimbeau_register_elementor_locations');

if (!function_exists('hello_bimbeau_content_width')) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_bimbeau_content_width() {
		$GLOBALS['content_width'] = apply_filters('hello_bimbeau_content_width', 800);
	}
}
add_action('after_setup_theme', 'hello_bimbeau_content_width', 0);


// Charge les fichiers de traduction du thème
add_action('after_setup_theme', 'bimbeau_load_textdomain');
function bimbeau_load_textdomain() {
    load_theme_textdomain('hello-bimbeau', get_template_directory() . '/languages');
}

// Admin notice
if (is_admin()) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Core functions
require get_template_directory() . '/includes/core-functions.php';

// Allow active/inactive via the Experiments
require get_template_directory() . '/includes/elementor-functions.php';

if (!function_exists('hello_bimbeau_check_hide_title')) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_bimbeau_check_hide_title($val) {
		if (defined('ELEMENTOR_VERSION')) {
			$current_doc = Elementor\Plugin::instance()->documents->get(get_the_ID());
			if ($current_doc && 'yes' === $current_doc->get_settings('hide_title')) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter('hello_bimbeau_page_title', 'hello_bimbeau_check_hide_title');

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_bimbeau_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if (!function_exists('hello_bimbeau_body_open')) {
	function hello_bimbeau_body_open() {
		wp_body_open();
	}
}


/**
 * Intercepte les vérifications de mise à jour des thèmes et injecte les données de mise à jour personnalisées.
 *
 * @param object $transient Données transitoires contenant les informations de mise à jour des thèmes.
 * @return object Données transitoires modifiées avec les informations de mise à jour du thème Hello BimBeau.
 */
function check_for_theme_update($transient) {
	// Si on est en train de chercher des mises à jour, on ne fait rien.
	if (empty($transient->checked)) {
		return $transient;
	}

	// Informations sur la version actuelle du thème.
	$current_version = $transient->checked['hello-bimbeau']; // Assurez-vous que c'est le bon slug du thème.
	$theme_updates = get_transient('hello_bimbeau_theme_updates'); // Informations de mise à jour mises en cache.

	// Si il n'y a pas d'informations mises en cache, interroger le serveur de mise à jour.
	if (false === $theme_updates) {
		// Remplacer l'URL avec l'URL de votre serveur de mise à jour.
		$response = wp_remote_get('https://wordpress.bimbeau.fr/themes/hello-bimbeau/info.json');
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
			return $transient; // En cas d'erreur, on sort.
		}

		$theme_updates = json_decode(wp_remote_retrieve_body($response));
		// Mettre en cache les informations de mise à jour.
		set_transient('hello_bimbeau_theme_updates', $theme_updates, HOUR_IN_SECONDS * 12);
	}

	// Si une nouvelle version est disponible et est supérieure à la version actuelle, préparer la mise à jour.
	if (version_compare($current_version, $theme_updates->new_version, '<')) {
		$transient->response['hello-bimbeau'] = (array) $theme_updates;
	}

	return $transient;
}
add_filter('pre_set_site_transient_update_themes', 'check_for_theme_update');
