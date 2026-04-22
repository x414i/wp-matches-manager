<?php
/**
 * Query — fetches matches based on shortcode parameters.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build and run a WP_Query for matches.
 *
 * @param array $params {
 *     Shortcode parameters.
 *
 *     @type string $status  Post status: upcoming|live|finished|postponed|all. Default 'all'.
 *     @type string $team    Slug of ummm_team taxonomy term. Default 'all'.
 *     @type string $sport   Slug of ummm_sport taxonomy term. Default 'all'.
 *     @type int    $number  Number of posts to retrieve. Default -1 (all).
 *     @type string $orderby Order by field. Default 'meta_value'.
 *     @type string $order   ASC or DESC. Default 'ASC'.
 * }
 * @return WP_Query
 */
function ummm_get_matches( array $params = array() ) {
	$defaults = array(
		'status'  => 'all',
		'team'    => 'all',
		'sport'   => 'all',
		'number'  => -1,
		'orderby' => 'meta_value',
		'order'   => 'ASC',
	);
	$params = wp_parse_args( $params, $defaults );

	// Determine which statuses to query.
	$all_statuses  = array( 'upcoming', 'live', 'finished', 'postponed' );
	$post_status   = ( 'all' === $params['status'] ) ? $all_statuses : array( sanitize_key( $params['status'] ) );

	$query_args = array(
		'post_type'      => 'ummm_matches',
		'post_status'    => $post_status,
		'posts_per_page' => intval( $params['number'] ),
		'orderby'        => 'meta_value',
		'meta_key'       => '_ummm_match_date',
		'order'          => ( 'DESC' === strtoupper( $params['order'] ) ) ? 'DESC' : 'ASC',
		'no_found_rows'  => true,
	);

	// Taxonomy filters.
	$tax_query = array();

	if ( 'all' !== $params['team'] ) {
		$tax_query[] = array(
			'taxonomy' => 'ummm_team',
			'field'    => 'slug',
			'terms'    => sanitize_title( $params['team'] ),
		);
	}

	if ( 'all' !== $params['sport'] ) {
		$tax_query[] = array(
			'taxonomy' => 'ummm_sport',
			'field'    => 'slug',
			'terms'    => sanitize_title( $params['sport'] ),
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( ! empty( $tax_query ) ) {
		$query_args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	return new WP_Query( $query_args );
}

/**
 * Resolve a team field value to a human-readable name.
 *
 * Supports both the new format (term ID as integer) and legacy format (plain text string).
 *
 * @param mixed $value The raw meta value — term ID or team name string.
 * @return string Resolved team name.
 */
function ummm_resolve_team_name( $value ) {
	if ( empty( $value ) ) {
		return '';
	}

	// New format: value is a numeric term ID.
	if ( is_numeric( $value ) ) {
		$term = get_term( absint( $value ), 'ummm_team' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term->name;
		}
		return '';
	}

	// Legacy fallback: value is already a plain text name.
	return (string) $value;
}

/**
 * Resolve a team field value to an image URL (thumbnail size).
 *
 * @param mixed  $value The raw meta value — term ID or plain text.
 * @param string $size  Image size to retrieve. Default 'thumbnail'.
 * @return string Image URL or empty string.
 */
function ummm_resolve_team_image( $value, $size = 'thumbnail' ) {
	if ( empty( $value ) || ! is_numeric( $value ) ) {
		return '';
	}

	$term_id  = absint( $value );
	$image_id = absint( get_term_meta( $term_id, 'ummm_team_image', true ) );

	if ( ! $image_id ) {
		return '';
	}

	$url = wp_get_attachment_image_url( $image_id, $size );
	return $url ? $url : '';
}

/**
 * Get a single match's meta data as an associative array.
 *
 * The home_team and away_team values can be either term IDs (new format)
 * or plain text strings (legacy data). The resolved name and image URL
 * are provided for templates to consume directly.
 *
 * @param int $post_id Post ID.
 * @return array Match meta.
 */
function ummm_get_match_data( $post_id ) {
	$home_raw = get_post_meta( $post_id, '_ummm_home_team', true );
	$away_raw = get_post_meta( $post_id, '_ummm_away_team', true );

	return array(
		'id'              => (int) $post_id,
		'title'           => get_the_title( $post_id ),
		'home_team'       => ummm_resolve_team_name( $home_raw ),
		'away_team'       => ummm_resolve_team_name( $away_raw ),
		'home_team_image' => ummm_resolve_team_image( $home_raw ),
		'away_team_image' => ummm_resolve_team_image( $away_raw ),
		'match_date'      => get_post_meta( $post_id, '_ummm_match_date', true ),
		'match_time'      => get_post_meta( $post_id, '_ummm_match_time', true ),
		'stadium'         => get_post_meta( $post_id, '_ummm_stadium', true ),
		'competition'     => get_post_meta( $post_id, '_ummm_competition', true ),
		'score_ft'        => get_post_meta( $post_id, '_ummm_score_ft', true ),
		'score_ht'        => get_post_meta( $post_id, '_ummm_score_ht', true ),
		'referee'         => get_post_meta( $post_id, '_ummm_referee', true ),
		'status'          => get_post_field( 'post_status', $post_id ),
		'thumbnail'       => get_the_post_thumbnail_url( $post_id, 'medium' ),
	);
}

/**
 * Return a localized, human-readable label for a status slug.
 *
 * @param string $status Post status slug.
 * @return string Translated label.
 */
function ummm_status_label( $status ) {
	$map = array(
		'upcoming'  => __( 'قادمة', 'ummm' ),
		'live'      => __( 'مباشرة', 'ummm' ),
		'finished'  => __( 'انتهت', 'ummm' ),
		'postponed' => __( 'مؤجلة', 'ummm' ),
	);
	return isset( $map[ $status ] ) ? $map[ $status ] : esc_html( $status );
}

/**
 * Return a CSS class name for a given status.
 *
 * @param string $status Post status slug.
 * @return string CSS class.
 */
function ummm_status_class( $status ) {
	$valid = array( 'upcoming', 'live', 'finished', 'postponed' );
	return in_array( $status, $valid, true ) ? 'ummm-status--' . $status : 'ummm-status--unknown';
}

/**
 * Render a team logo image or a fallback placeholder.
 *
 * @param string $image_url Image URL (may be empty).
 * @param string $team_name Team name for alt text.
 * @return string HTML markup.
 */
function ummm_team_logo_html( $image_url, $team_name = '' ) {
	if ( $image_url ) {
		return sprintf(
			'<img src="%s" alt="%s" class="ummm-team-img" loading="lazy">',
			esc_url( $image_url ),
			esc_attr( $team_name )
		);
	}
	return '<span class="ummm-logo-placeholder">⚽</span>';
}
