<?php
defined( 'ABSPATH' ) || exit;

/**
 * Registers all Custom Post Types and Taxonomies for the plugin.
 */
final class ILB_Post_Types {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function register(): void {
        $this->register_player_cpt();
        $this->register_team_cpt();
        $this->register_lineup_cpt();
        $this->register_sport_taxonomy();
    }

    // ─── Players CPT ─────────────────────────────────────────────────────────

    private function register_player_cpt(): void {
        $labels = [
            'name'               => __( 'اللاعبون', 'ittihad-lineup' ),
            'singular_name'      => __( 'لاعب', 'ittihad-lineup' ),
            'add_new'            => __( 'إضافة لاعب', 'ittihad-lineup' ),
            'add_new_item'       => __( 'إضافة لاعب جديد', 'ittihad-lineup' ),
            'edit_item'          => __( 'تعديل اللاعب', 'ittihad-lineup' ),
            'new_item'           => __( 'لاعب جديد', 'ittihad-lineup' ),
            'view_item'          => __( 'عرض اللاعب', 'ittihad-lineup' ),
            'search_items'       => __( 'بحث عن لاعب', 'ittihad-lineup' ),
            'not_found'          => __( 'لا يوجد لاعبون', 'ittihad-lineup' ),
            'not_found_in_trash' => __( 'لا يوجد لاعبون في المهملات', 'ittihad-lineup' ),
            'menu_name'          => __( 'اللاعبون', 'ittihad-lineup' ),
        ];

        register_post_type( 'ilb_player', [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'ittihad-main',
            'show_in_rest'       => true,
            'supports'           => [ 'title', 'thumbnail' ],
            'has_archive'        => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'menu_icon'          => 'dashicons-groups',
        ] );
    }

    // ─── Teams CPT ───────────────────────────────────────────────────────────

    private function register_team_cpt(): void {
        $labels = [
            'name'               => __( 'الفرق', 'ittihad-lineup' ),
            'singular_name'      => __( 'فريق', 'ittihad-lineup' ),
            'add_new'            => __( 'إضافة فريق', 'ittihad-lineup' ),
            'add_new_item'       => __( 'إضافة فريق جديد', 'ittihad-lineup' ),
            'edit_item'          => __( 'تعديل الفريق', 'ittihad-lineup' ),
            'new_item'           => __( 'فريق جديد', 'ittihad-lineup' ),
            'not_found'          => __( 'لا توجد فرق', 'ittihad-lineup' ),
            'menu_name'          => __( 'الفرق', 'ittihad-lineup' ),
        ];

        register_post_type( 'ilb_team', [
            'labels'          => $labels,
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => 'ittihad-main',
            'show_in_rest'    => true,
            'supports'        => [ 'title' ],
            'has_archive'     => false,
            'rewrite'         => false,
            'capability_type' => 'post',
        ] );
    }

    // ─── Lineups CPT ─────────────────────────────────────────────────────────

    private function register_lineup_cpt(): void {
        $labels = [
            'name'               => __( 'التشكيلات', 'ittihad-lineup' ),
            'singular_name'      => __( 'تشكيلة', 'ittihad-lineup' ),
            'add_new'            => __( 'إنشاء تشكيلة', 'ittihad-lineup' ),
            'add_new_item'       => __( 'إنشاء تشكيلة جديدة', 'ittihad-lineup' ),
            'edit_item'          => __( 'تعديل التشكيلة', 'ittihad-lineup' ),
            'new_item'           => __( 'تشكيلة جديدة', 'ittihad-lineup' ),
            'not_found'          => __( 'لا توجد تشكيلات', 'ittihad-lineup' ),
            'menu_name'          => __( 'التشكيلات', 'ittihad-lineup' ),
        ];

        register_post_type( 'ilb_lineup', [
            'labels'          => $labels,
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => 'ittihad-main',
            'show_in_rest'    => false,
            'supports'        => [ 'title' ],
            'has_archive'     => false,
            'rewrite'         => false,
            'capability_type' => 'post',
        ] );
    }

    // ─── Sport Type Taxonomy ─────────────────────────────────────────────────

    private function register_sport_taxonomy(): void {
        $labels = [
            'name'          => __( 'أنواع الرياضات', 'ittihad-lineup' ),
            'singular_name' => __( 'نوع رياضة', 'ittihad-lineup' ),
            'add_new_item'  => __( 'إضافة رياضة جديدة', 'ittihad-lineup' ),
            'edit_item'     => __( 'تعديل الرياضة', 'ittihad-lineup' ),
            'not_found'     => __( 'لا توجد رياضات', 'ittihad-lineup' ),
        ];

        register_taxonomy( 'ilb_sport', [ 'ilb_team', 'ilb_player' ], [
            'labels'            => $labels,
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_admin_column' => true,
            'hierarchical'      => false,
            'rewrite'           => false,
        ] );
    }
}
