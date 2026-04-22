<?php
/**
 * Custom Post Type & Custom Post Statuses registration.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Matches Custom Post Type.
 */
function ummm_register_cpt() {
	$labels = array(
		'name'                  => __( 'المباريات', 'ummm' ),
		'singular_name'         => __( 'مباراة', 'ummm' ),
		'add_new'               => __( 'إضافة مباراة', 'ummm' ),
		'add_new_item'          => __( 'إضافة مباراة جديدة', 'ummm' ),
		'edit_item'             => __( 'تعديل المباراة', 'ummm' ),
		'new_item'              => __( 'مباراة جديدة', 'ummm' ),
		'view_item'             => __( 'عرض المباراة', 'ummm' ),
		'view_items'            => __( 'عرض المباريات', 'ummm' ),
		'search_items'          => __( 'البحث في المباريات', 'ummm' ),
		'not_found'             => __( 'لا توجد مباريات', 'ummm' ),
		'not_found_in_trash'    => __( 'لا توجد مباريات في المهملات', 'ummm' ),
		'all_items'             => __( 'جميع المباريات', 'ummm' ),
		'menu_name'             => __( 'المباريات', 'ummm' ),
		'name_admin_bar'        => __( 'مباراة', 'ummm' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => true,
		'rewrite'             => array( 'slug' => 'matches' ),
		'capability_type'     => 'post',
		'capabilities'        => array(
			'publish_posts'       => 'manage_options',
			'edit_posts'          => 'manage_options',
			'edit_others_posts'   => 'manage_options',
			'delete_posts'        => 'manage_options',
			'delete_others_posts' => 'manage_options',
			'read_private_posts'  => 'manage_options',
			'edit_post'           => 'manage_options',
			'delete_post'         => 'manage_options',
			'read_post'           => 'manage_options',
		),
		'has_archive'         => true,
		'hierarchical'        => false,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-awards',
		'supports'            => array( 'title', 'thumbnail' ),
		'show_in_rest'        => false,
	);

	register_post_type( 'ummm_matches', $args );
}
add_action( 'init', 'ummm_register_cpt' );

/**
 * Register custom post statuses for matches.
 */
function ummm_register_custom_statuses() {
	// Upcoming (قادمة)
	register_post_status( 'upcoming', array(
		'label'                     => _x( 'قادمة', 'post status', 'ummm' ),
		'label_count'               => _n_noop( 'قادمة <span class="count">(%s)</span>', 'قادمة <span class="count">(%s)</span>', 'ummm' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	) );

	// Live (مباشرة)
	register_post_status( 'live', array(
		'label'                     => _x( 'مباشرة', 'post status', 'ummm' ),
		'label_count'               => _n_noop( 'مباشرة <span class="count">(%s)</span>', 'مباشرة <span class="count">(%s)</span>', 'ummm' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	) );

	// Finished (انتهت)
	register_post_status( 'finished', array(
		'label'                     => _x( 'انتهت', 'post status', 'ummm' ),
		'label_count'               => _n_noop( 'انتهت <span class="count">(%s)</span>', 'انتهت <span class="count">(%s)</span>', 'ummm' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	) );

	// Postponed (مؤجلة)
	register_post_status( 'postponed', array(
		'label'                     => _x( 'مؤجلة', 'post status', 'ummm' ),
		'label_count'               => _n_noop( 'مؤجلة <span class="count">(%s)</span>', 'مؤجلة <span class="count">(%s)</span>', 'ummm' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	) );
}
add_action( 'init', 'ummm_register_custom_statuses' );

/**
 * Add custom statuses to the dropdown in the admin editor (classic editor).
 */
function ummm_append_statuses_to_editor() {
	global $post;

	if ( ! $post || 'ummm_matches' !== $post->post_type ) {
		return;
	}

	$statuses = array(
		'upcoming'  => __( 'قادمة', 'ummm' ),
		'live'      => __( 'مباشرة', 'ummm' ),
		'finished'  => __( 'انتهت', 'ummm' ),
		'postponed' => __( 'مؤجلة', 'ummm' ),
	);
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var customStatuses = <?php echo wp_json_encode( $statuses ); ?>;
			var currentStatus  = "<?php echo esc_js( $post->post_status ); ?>";

			// Append options to the status select.
			$.each(customStatuses, function(value, label) {
				if (!$('#post_status option[value="' + value + '"]').length) {
					$('#post_status').append($('<option>', {value: value, text: label}));
				}
			});

			// Set current selection.
			if (customStatuses[currentStatus]) {
				$('#post_status').val(currentStatus);
				$('#post-status-display').text(customStatuses[currentStatus]);
			}
		});
	</script>
	<?php
}
add_action( 'admin_footer-post.php', 'ummm_append_statuses_to_editor' );
add_action( 'admin_footer-post-new.php', 'ummm_append_statuses_to_editor' );

/**
 * Show the current custom status correctly in the admin list.
 *
 * @param array   $states Post states array.
 * @param WP_Post $post   Post object.
 * @return array Modified post states.
 */
function ummm_display_post_states( array $states, $post ) {
	if ( 'ummm_matches' !== $post->post_type ) {
		return $states;
	}

	$custom_statuses = array(
		'upcoming'  => __( 'قادمة', 'ummm' ),
		'live'      => __( 'مباشرة', 'ummm' ),
		'finished'  => __( 'انتهت', 'ummm' ),
		'postponed' => __( 'مؤجلة', 'ummm' ),
	);

	if ( isset( $custom_statuses[ $post->post_status ] ) ) {
		$states[] = esc_html( $custom_statuses[ $post->post_status ] );
	}

	return $states;
}
add_filter( 'display_post_states', 'ummm_display_post_states', 10, 2 );
