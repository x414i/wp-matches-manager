<?php
/**
 * Shortcode — [united_matches]
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the [united_matches] shortcode.
 */
function ummm_register_shortcode() {
	add_shortcode( 'united_matches', 'ummm_shortcode_handler' );
}
add_action( 'init', 'ummm_register_shortcode' );

/**
 * Shortcode callback.
 *
 * Usage:
 *   [united_matches view="cards" team="all" sport="all" status="all" number="-1" order="ASC"]
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Inner content (unused).
 * @return string Rendered HTML.
 */
function ummm_shortcode_handler( $atts, $content = '' ) {
	$atts = shortcode_atts(
		array(
			'view'   => 'cards',
			'team'   => 'all',
			'sport'  => 'all',
			'status' => 'all',
			'number' => -1,
			'order'  => 'ASC',
		),
		$atts,
		'united_matches'
	);

	// Sanitize.
	$view   = sanitize_key( $atts['view'] );
	$team   = sanitize_text_field( $atts['team'] );
	$sport  = sanitize_text_field( $atts['sport'] );
	$status = sanitize_key( $atts['status'] );
	$number = intval( $atts['number'] );
	$order  = ( 'DESC' === strtoupper( $atts['order'] ) ) ? 'DESC' : 'ASC';

	// Try transient cache (60 seconds) to reduce duplicate queries per page load.
	$cache_key = 'ummm_sc_' . md5( wp_json_encode( compact( 'view', 'team', 'sport', 'status', 'number', 'order' ) ) );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$query = ummm_get_matches( array(
		'status' => $status,
		'team'   => $team,
		'sport'  => $sport,
		'number' => $number,
		'order'  => $order,
	) );

	$output = ummm_render_matches( $query, $view, compact( 'team', 'sport', 'status', 'number', 'order' ) );
	wp_reset_postdata();

	set_transient( $cache_key, $output, 60 );

	return $output;
}
