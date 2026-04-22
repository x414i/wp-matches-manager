<?php
/**
 * Plugin Name:       Ittihad Misrata Lineup Builder
 * Plugin URI:        https://github.com/x414i
 * Description:       إنشاء وإدارة تشكيلات الفرق الرياضية بشكل مرئي تفاعلي مع إمكانية السحب والإفلات
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Mohamed S. Belaiid 
 * Author URI:        https://github.com/x414i
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ittihad-lineup
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'ILB_VERSION',     '1.0.0' );
define( 'ILB_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'ILB_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'ILB_PLUGIN_FILE', __FILE__ );

// Autoload includes
require_once ILB_PLUGIN_DIR . 'includes/post-types.php';
require_once ILB_PLUGIN_DIR . 'includes/admin-ui.php';
require_once ILB_PLUGIN_DIR . 'includes/shortcode.php';

/**
 * Main plugin class
 */
final class Ittihad_Lineup_Builder {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
        add_action( 'init',           [ $this, 'init' ] );
        add_action( 'wp_ajax_ilb_save_lineup',       [ $this, 'ajax_save_lineup' ] );
        add_action( 'wp_ajax_ilb_get_team_players',  [ $this, 'ajax_get_team_players' ] );
        add_action( 'wp_ajax_ilb_get_lineup_data',   [ $this, 'ajax_get_lineup_data' ] );
        add_action( 'wp_ajax_ilb_create_lineup',     [ $this, 'ajax_create_lineup' ] );
        add_action( 'wp_enqueue_scripts',            [ $this, 'enqueue_frontend_scripts' ] );

        register_activation_hook( ILB_PLUGIN_FILE,   [ $this, 'activate' ] );
        register_deactivation_hook( ILB_PLUGIN_FILE, [ $this, 'deactivate' ] );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain( 'ittihad-lineup', false, dirname( plugin_basename( ILB_PLUGIN_FILE ) ) . '/languages' );
    }

    public function init(): void {
        ILB_Post_Types::instance()->register();
        ILB_Admin_UI::instance()->init();
        ILB_Shortcode::instance()->init();
    }

    // ─── AJAX: Create new lineup ─────────────────────────────────────────────

    public function ajax_create_lineup(): void {
        check_ajax_referer( 'ilb_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'صلاحيات غير كافية', 'ittihad-lineup' ) ] );
        }

        $name = sanitize_text_field( $_POST['name'] ?? '' );
        if ( ! $name ) {
            wp_send_json_error( [ 'message' => __( 'الاسم مطلوب', 'ittihad-lineup' ) ] );
        }

        $post_id = wp_insert_post( [
            'post_title'  => $name,
            'post_type'   => 'ilb_lineup',
            'post_status' => 'publish',
        ] );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );
        }

        wp_send_json_success( [ 'id' => $post_id, 'name' => $name ] );
    }

    // ─── Frontend Scripts ─────────────────────────────────────────────────────

    public function enqueue_frontend_scripts(): void {
        wp_enqueue_script(
            'ilb-frontend',
            ILB_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            ILB_VERSION,
            true
        );
    }

    // ─── AJAX: Save lineup positions ──────────────────────────────────────────

    public function ajax_save_lineup(): void {
        check_ajax_referer( 'ilb_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'صلاحيات غير كافية', 'ittihad-lineup' ) ] );
        }

        $lineup_id = absint( $_POST['lineup_id'] ?? 0 );
        $positions = wp_unslash( $_POST['positions'] ?? '' );
        $team_id   = absint( $_POST['team_id']   ?? 0 );
        $sport     = sanitize_text_field( $_POST['sport_type'] ?? 'football' );
        $name      = sanitize_text_field( $_POST['lineup_name'] ?? '' );

        if ( ! $lineup_id || ! $positions ) {
            wp_send_json_error( [ 'message' => __( 'بيانات ناقصة', 'ittihad-lineup' ) ] );
        }

        // Validate JSON
        $decoded = json_decode( $positions, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( [ 'message' => __( 'بيانات المواضع غير صحيحة', 'ittihad-lineup' ) ] );
        }

        // Sanitize positions array
        $clean_positions = [];
        foreach ( $decoded as $player_id => $pos ) {
            $clean_positions[ absint( $player_id ) ] = [
                'x'       => floatval( $pos['x'] ?? 50 ),
                'y'       => floatval( $pos['y'] ?? 50 ),
                'name'    => sanitize_text_field( $pos['name']     ?? '' ),
                'number'  => sanitize_text_field( $pos['number']   ?? '' ),
                'position'=> sanitize_text_field( $pos['position'] ?? '' ),
                'photo'   => esc_url_raw( $pos['photo'] ?? '' ),
            ];
        }

        update_post_meta( $lineup_id, '_ilb_positions',  wp_json_encode( $clean_positions ) );
        update_post_meta( $lineup_id, '_ilb_team_id',    $team_id );
        update_post_meta( $lineup_id, '_ilb_sport_type', $sport );

        if ( $name ) {
            wp_update_post( [ 'ID' => $lineup_id, 'post_title' => $name ] );
        }

        wp_send_json_success( [ 'message' => __( 'تم الحفظ بنجاح ✓', 'ittihad-lineup' ) ] );
    }

    // ─── AJAX: Get players by team ────────────────────────────────────────────

    public function ajax_get_team_players(): void {
        check_ajax_referer( 'ilb_nonce', 'nonce' );

        $team_id = absint( $_POST['team_id'] ?? 0 );

        $args = [
            'post_type'      => 'ilb_player',
            'posts_per_page' => 100,
            'post_status'    => 'publish',
        ];

        if ( $team_id ) {
            $args['meta_query'] = [ 
                'relation' => 'OR',
                [ 
                    'key'     => '_ilb_player_team', 
                    'value'   => '"' . $team_id . '"',
                    'compare' => 'LIKE'
                ],
                [ 
                    'key'     => '_ilb_player_team', 
                    'value'   => 'i:' . $team_id . ';',
                    'compare' => 'LIKE'
                ]
            ];
        }

        $players = get_posts( $args );
        $data    = [];

        foreach ( $players as $player ) {
            $thumb = get_the_post_thumbnail_url( $player->ID, 'thumbnail' );
            $data[] = [
                'id'       => $player->ID,
                'name'     => $player->post_title,
                'position' => get_post_meta( $player->ID, '_ilb_position',      true ),
                'number'   => get_post_meta( $player->ID, '_ilb_jersey_number', true ),
                'photo'    => $thumb ?: ILB_PLUGIN_URL . 'assets/images/default-player.svg',
            ];
        }

        wp_send_json_success( $data );
    }

    // ─── AJAX: Get lineup data for editing ───────────────────────────────────

    public function ajax_get_lineup_data(): void {
        check_ajax_referer( 'ilb_nonce', 'nonce' );

        $lineup_id = absint( $_POST['lineup_id'] ?? 0 );
        if ( ! $lineup_id ) {
            wp_send_json_error();
        }

        $positions  = get_post_meta( $lineup_id, '_ilb_positions',      true );
        $team_id    = get_post_meta( $lineup_id, '_ilb_team_id',         true );
        $sport      = get_post_meta( $lineup_id, '_ilb_sport_type',      true );
        $field_img  = get_post_meta( $lineup_id, '_ilb_field_image',     true );
        $field_type = get_post_meta( $lineup_id, '_ilb_field_type',      true );

        wp_send_json_success( [
            'positions'  => $positions ? json_decode( $positions, true ) : [],
            'team_id'    => $team_id,
            'sport_type' => $sport ?: 'football',
            'field_img'  => $field_img,
            'field_type' => $field_type ?: 'football',
        ] );
    }

    // ─── Activation / Deactivation ────────────────────────────────────────────

    public function activate(): void {
        ILB_Post_Types::instance()->register();
        flush_rewrite_rules();
    }

    public function deactivate(): void {
        flush_rewrite_rules();
    }
}

// Bootstrap
Ittihad_Lineup_Builder::instance();
