<?php
/**
 * Renderer — dispatches output to the correct template.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render matches using the selected view template.
 *
 * @param WP_Query $query  Query result containing matches.
 * @param string   $view   One of: table | cards | timeline | tabs.
 * @param array    $params Original shortcode params (passed to template).
 * @return string Rendered HTML.
 */
function ummm_render_matches( $query, $view, $params ) {
	$allowed_views = array( 'table', 'cards', 'timeline', 'tabs' );
	$view = in_array( $view, $allowed_views, true ) ? $view : 'cards';

	$template_file = UMMM_PLUGIN_DIR . 'templates/' . $view . '.php';

	if ( ! file_exists( $template_file ) ) {
		return '<p class="ummm-error">' . esc_html__( 'قالب العرض غير موجود.', 'ummm' ) . '</p>';
	}

	ob_start();
	include $template_file;
	return ob_get_clean();
}
