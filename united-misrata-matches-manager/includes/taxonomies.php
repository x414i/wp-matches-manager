<?php
/**
 * Taxonomies registration and default term seeding.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all taxonomies for ummm_matches.
 */
function ummm_register_taxonomies() {

	// ── 1. Teams (فئات الفريق) ─────────────────────────────────────────────────
	$team_labels = array(
		'name'              => __( 'فئات الفريق', 'ummm' ),
		'singular_name'     => __( 'فئة الفريق', 'ummm' ),
		'search_items'      => __( 'البحث في الفئات', 'ummm' ),
		'all_items'         => __( 'جميع الفئات', 'ummm' ),
		'edit_item'         => __( 'تعديل الفئة', 'ummm' ),
		'update_item'       => __( 'تحديث الفئة', 'ummm' ),
		'add_new_item'      => __( 'إضافة فئة جديدة', 'ummm' ),
		'new_item_name'     => __( 'اسم الفئة الجديدة', 'ummm' ),
		'menu_name'         => __( 'الفرق', 'ummm' ),
		'not_found'         => __( 'لا توجد فئات', 'ummm' ),
	);

	register_taxonomy( 'ummm_team', 'ummm_matches', array(
		'labels'            => $team_labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => false,
		'rewrite'           => array( 'slug' => 'match-team' ),
	) );

	// ── 2. Sports (نوع الرياضة) ──────────────────────────────────────────────
	$sport_labels = array(
		'name'              => __( 'نوع الرياضة', 'ummm' ),
		'singular_name'     => __( 'رياضة', 'ummm' ),
		'search_items'      => __( 'البحث في الرياضات', 'ummm' ),
		'all_items'         => __( 'جميع الرياضات', 'ummm' ),
		'edit_item'         => __( 'تعديل الرياضة', 'ummm' ),
		'update_item'       => __( 'تحديث الرياضة', 'ummm' ),
		'add_new_item'      => __( 'إضافة رياضة جديدة', 'ummm' ),
		'new_item_name'     => __( 'اسم الرياضة الجديدة', 'ummm' ),
		'menu_name'         => __( 'الرياضات', 'ummm' ),
		'not_found'         => __( 'لا توجد رياضات', 'ummm' ),
	);

	register_taxonomy( 'ummm_sport', 'ummm_matches', array(
		'labels'            => $sport_labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => false,
		'rewrite'           => array( 'slug' => 'match-sport' ),
	) );

	// ── 3. Competitions (البطولة) ─────────────────────────────────────────────
	$competition_labels = array(
		'name'              => __( 'البطولات', 'ummm' ),
		'singular_name'     => __( 'بطولة', 'ummm' ),
		'search_items'      => __( 'البحث في البطولات', 'ummm' ),
		'all_items'         => __( 'جميع البطولات', 'ummm' ),
		'edit_item'         => __( 'تعديل البطولة', 'ummm' ),
		'update_item'       => __( 'تحديث البطولة', 'ummm' ),
		'add_new_item'      => __( 'إضافة بطولة جديدة', 'ummm' ),
		'new_item_name'     => __( 'اسم البطولة الجديدة', 'ummm' ),
		'menu_name'         => __( 'البطولات', 'ummm' ),
		'not_found'         => __( 'لا توجد بطولات', 'ummm' ),
	);

	register_taxonomy( 'ummm_competition', 'ummm_matches', array(
		'labels'            => $competition_labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => false,
		'rewrite'           => array( 'slug' => 'match-competition' ),
	) );
}
add_action( 'init', 'ummm_register_taxonomies' );

/**
 * Seed default taxonomy terms on plugin activation.
 */
function ummm_seed_default_terms() {
	// Teams.
	$teams = array(
		__( 'الفريق الأول', 'ummm' ),
		__( 'الأواسط', 'ummm' ),
		__( 'الأشبال', 'ummm' ),
		__( 'البراعم', 'ummm' ),
		__( 'الناشئين', 'ummm' ),
	);
	foreach ( $teams as $team ) {
		if ( ! term_exists( $team, 'ummm_team' ) ) {
			wp_insert_term( $team, 'ummm_team' );
		}
	}

	// Sports.
	$sports = array(
		__( 'كرة القدم', 'ummm' ),
		__( 'كرة الطائرة', 'ummm' ),
		__( 'الدراجات الهوائية', 'ummm' ),
		__( 'القوة البدنية', 'ummm' ),
	);
	foreach ( $sports as $sport ) {
		if ( ! term_exists( $sport, 'ummm_sport' ) ) {
			wp_insert_term( $sport, 'ummm_sport' );
		}
	}

	// Competitions.
	$competitions = array(
		__( 'الدوري المحلي', 'ummm' ),
		__( 'الكأس', 'ummm' ),
		__( 'مباريات ودية', 'ummm' ),
	);
	foreach ( $competitions as $competition ) {
		if ( ! term_exists( $competition, 'ummm_competition' ) ) {
			wp_insert_term( $competition, 'ummm_competition' );
		}
	}
}
