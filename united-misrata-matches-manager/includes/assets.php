<?php
/**
 * Assets — enqueue frontend CSS and JS.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue frontend styles and scripts only when the shortcode is present.
 */
function ummm_enqueue_frontend_assets() {
	// Only load on pages containing the shortcode.
	global $post;
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'united_matches' ) ) {
		wp_enqueue_style(
			'ummm-frontend',
			UMMM_PLUGIN_URL . 'frontend/css/frontend-style.css',
			array(),
			UMMM_VERSION
		);
		wp_enqueue_script(
			'ummm-tabs',
			UMMM_PLUGIN_URL . 'frontend/js/tabs.js',
			array(),
			UMMM_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ummm_enqueue_frontend_assets' );
