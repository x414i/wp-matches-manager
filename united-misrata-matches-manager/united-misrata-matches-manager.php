<?php
/**
 * Plugin Name:       United Misrata Matches Manager
 * Plugin URI:        https://github.com/x414i/wp-matches-manager
 * Description:       إضافة احترافية لإدارة مباريات نادي الاتحاد المصراتي لكرة القدم والرياضات الأخرى.
 * Version:           1.0.0
 * Author:            Mohamed  S. Belaid
 * Author URI:        https://github.com/x414i
 * Text Domain:       ummm
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL v2 or later
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'UMMM_VERSION', '1.0.0' );
define( 'UMMM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UMMM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UMMM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The main plugin class.
 */
final class United_Misrata_Matches_Manager {

	/**
	 * Single instance of the class.
	 *
	 * @var United_Misrata_Matches_Manager
	 */
	private static $instance = null;

	/**
	 * Get or create the singleton instance.
	 *
	 * @return United_Misrata_Matches_Manager
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — load all modules.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load all required files.
	 */
	private function load_dependencies() {
		require_once UMMM_PLUGIN_DIR . 'includes/cpt.php';
		require_once UMMM_PLUGIN_DIR . 'includes/taxonomies.php';
		require_once UMMM_PLUGIN_DIR . 'includes/meta-boxes.php';
		require_once UMMM_PLUGIN_DIR . 'includes/query.php';
		require_once UMMM_PLUGIN_DIR . 'includes/renderer.php';
		require_once UMMM_PLUGIN_DIR . 'includes/shortcode.php';
		require_once UMMM_PLUGIN_DIR . 'includes/assets.php';
		require_once UMMM_PLUGIN_DIR . 'admin/admin-page.php';
	}

	/**
	 * Register top-level hooks.
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		register_activation_hook( __FILE__, array( $this, 'on_activation' ) );
	}

	/**
	 * Load plugin text domain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ummm', false, dirname( UMMM_PLUGIN_BASENAME ) . '/languages' );
	}

	/**
	 * Plugin activation callback — flush rewrite rules and seed default terms.
	 */
	public function on_activation() {
		ummm_register_cpt();
		ummm_register_custom_statuses();
		ummm_register_taxonomies();
		ummm_seed_default_terms();
		flush_rewrite_rules();
	}
}

// Bootstrap the plugin.
United_Misrata_Matches_Manager::instance();
