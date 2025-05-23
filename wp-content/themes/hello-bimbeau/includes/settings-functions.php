<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'admin_menu', 'hello_bimbeau_settings_page' );
add_action( 'init', 'hello_bimbeau_tweak_settings', 0 );

/**
 * Register theme settings page.
 */
function hello_bimbeau_settings_page() {

	$menu_hook = '';

	$menu_hook = add_theme_page(
		esc_html__( 'Hello Theme Settings', 'hello-bimbeau' ),
		esc_html__( 'Theme Settings', 'hello-bimbeau' ),
		'manage_options',
		'hello-theme-settings',
		'hello_bimbeau_settings_page_render'
	);

	add_action( 'load-' . $menu_hook, function() {
		add_action( 'admin_enqueue_scripts', 'hello_bimbeau_settings_page_scripts', 10 );
	} );

}

/**
 * Register settings page scripts.
 */
function hello_bimbeau_settings_page_scripts() {

	$dir = get_template_directory() . '/assets/js';
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$handle = 'hello-admin';
	$asset_path = "$dir/hello-admin.asset.php";
	$asset_url = get_template_directory_uri() . '/assets/js';
	if ( ! file_exists( $asset_path ) ) {
		throw new \Error( 'You need to run `npm run build` for the "hello-theme" first.' );
	}
	$script_asset = require( $asset_path );

	wp_enqueue_script(
		$handle,
		"$asset_url/$handle$suffix.js",
		$script_asset['dependencies'],
		$script_asset['version']
	);

	wp_set_script_translations( $handle, 'hello-bimbeau' );

	wp_enqueue_style(
		$handle,
		"$asset_url/$handle$suffix.css",
		[ 'wp-components' ],
		$script_asset['version']
	);

}

/**
 * Render settings page wrapper element.
 */
function hello_bimbeau_settings_page_render() {
	?>
	<div id="hello-bimbeau-settings"></div>
	<?php
}

/**
 * Theme tweaks & settings.
 */
function hello_bimbeau_tweak_settings() {

	$settings_group = 'hello_bimbeau_settings';

	$settings = [
		'DESCRIPTION_META_TAG' => '_description_meta_tag',
		'SKIP_LINK' => '_skip_link',
		'PAGE_TITLE' => '_page_title',
		'HELLO_STYLE' => '_hello_style',
		'HELLO_THEME' => '_hello_theme',
	];

	hello_bimbeau_register_settings( $settings_group, $settings );
	hello_bimbeau_render_tweaks( $settings_group, $settings );
}

/**
 * Register theme settings.
 */
function hello_bimbeau_register_settings( $settings_group, $settings ) {

	foreach ( $settings as $setting_key => $setting_value ) {
		register_setting(
			$settings_group,
			$settings_group . $setting_value,
			[
				'default' => '',
				'show_in_rest' => true,
				'type' => 'string',
			]
		);
	}

}

/**
 * Run a tweek only if the user requested it.
 */
function hello_bimbeau_do_tweak( $setting, $tweak_callback ) {

	$option = get_option( $setting );
	if ( isset( $option ) && ( 'true' === $option ) && is_callable( $tweak_callback ) ) {
		$tweak_callback();
	}

}

/**
 * Render theme tweaks.
 */
function hello_bimbeau_render_tweaks( $settings_group, $settings ) {

	hello_bimbeau_do_tweak( $settings_group . $settings['DESCRIPTION_META_TAG'], function() {
		remove_action( 'wp_head', 'hello_bimbeau_add_description_meta_tag' );
	} );

	hello_bimbeau_do_tweak( $settings_group . $settings['SKIP_LINK'], function() {
		add_filter( 'hello_bimbeau_enable_skip_link', '__return_false' );
	} );

	hello_bimbeau_do_tweak( $settings_group . $settings['PAGE_TITLE'], function() {
		add_filter( 'hello_bimbeau_page_title', '__return_false' );
	} );

	hello_bimbeau_do_tweak( $settings_group . $settings['HELLO_STYLE'], function() {
		add_filter( 'hello_bimbeau_enqueue_style', '__return_false' );
	} );

	hello_bimbeau_do_tweak( $settings_group . $settings['HELLO_THEME'], function() {
		add_filter( 'hello_bimbeau_enqueue_theme_style', '__return_false' );
	} );

}
