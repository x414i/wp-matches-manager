<?php
/**
 * Meta Boxes — registration, rendering, and saving.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register meta boxes.
 */
function ummm_register_meta_boxes() {
	// Match details meta box.
	add_meta_box(
		'ummm_match_details',
		__( 'تفاصيل المباراة', 'ummm' ),
		'ummm_render_match_details_meta_box',
		'ummm_matches',
		'normal',
		'high'
	);

	// Match status meta box.
	add_meta_box(
		'ummm_match_status',
		__( 'حالة المباراة', 'ummm' ),
		'ummm_render_match_status_meta_box',
		'ummm_matches',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'ummm_register_meta_boxes' );

/**
 * Render the match details meta box.
 *
 * @param WP_Post $post The current post.
 */
function ummm_render_match_details_meta_box( $post ) {
	wp_nonce_field( 'ummm_save_match_meta', 'ummm_match_meta_nonce' );

	$home_team      = get_post_meta( $post->ID, '_ummm_home_team', true );
	$away_team      = get_post_meta( $post->ID, '_ummm_away_team', true );
	$match_date     = get_post_meta( $post->ID, '_ummm_match_date', true );
	$match_time     = get_post_meta( $post->ID, '_ummm_match_time', true );
	$stadium        = get_post_meta( $post->ID, '_ummm_stadium', true );
	$competition    = get_post_meta( $post->ID, '_ummm_competition', true );
	$score_ft       = get_post_meta( $post->ID, '_ummm_score_ft', true );
	$score_ht       = get_post_meta( $post->ID, '_ummm_score_ht', true );
	$referee        = get_post_meta( $post->ID, '_ummm_referee', true );
	?>
	<style>
		.ummm-meta-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 16px;
			direction: rtl;
		}
		.ummm-meta-grid .ummm-full-width {
			grid-column: 1 / -1;
		}
		.ummm-meta-field label {
			display: block;
			font-weight: 600;
			margin-bottom: 5px;
			color: #1d2327;
		}
		.ummm-meta-field input[type="text"],
		.ummm-meta-field input[type="date"],
		.ummm-meta-field input[type="time"] {
			width: 100%;
			padding: 8px 10px;
			border: 1px solid #8c8f94;
			border-radius: 4px;
			font-size: 14px;
			font-family: inherit;
			direction: rtl;
		}
		.ummm-meta-field input:focus {
			outline: none;
			border-color: #267d34;
			box-shadow: 0 0 0 2px rgba(38,125,52,0.2);
		}
		.ummm-meta-section-title {
			grid-column: 1 / -1;
			border-top: 1px solid #dcdcde;
			padding-top: 16px;
			margin-top: 4px;
			font-size: 13px;
			font-weight: 700;
			color: #646970;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		.ummm-score-row {
			display: flex;
			gap: 8px;
			align-items: center;
		}
		.ummm-score-sep {
			font-size: 20px;
			font-weight: 700;
			color: #267d34;
			padding: 0 4px;
			line-height: 1;
		}
		.ummm-score-row input {
			width: 70px !important;
			text-align: center;
		}
	</style>
	<div class="ummm-meta-grid">

		<p class="ummm-meta-section-title"><?php esc_html_e( 'الفرق', 'ummm' ); ?></p>

		<div class="ummm-meta-field">
			<label for="ummm_home_team"><?php esc_html_e( 'الفريق المستضيف', 'ummm' ); ?></label>
			<input type="text" id="ummm_home_team" name="ummm_home_team"
				value="<?php echo esc_attr( $home_team ); ?>"
				placeholder="<?php esc_attr_e( 'اسم الفريق المستضيف', 'ummm' ); ?>">
		</div>

		<div class="ummm-meta-field">
			<label for="ummm_away_team"><?php esc_html_e( 'الفريق الضيف', 'ummm' ); ?></label>
			<input type="text" id="ummm_away_team" name="ummm_away_team"
				value="<?php echo esc_attr( $away_team ); ?>"
				placeholder="<?php esc_attr_e( 'اسم الفريق الضيف', 'ummm' ); ?>">
		</div>

		<p class="ummm-meta-section-title"><?php esc_html_e( 'الموعد والمكان', 'ummm' ); ?></p>

		<div class="ummm-meta-field">
			<label for="ummm_match_date"><?php esc_html_e( 'تاريخ المباراة', 'ummm' ); ?></label>
			<input type="date" id="ummm_match_date" name="ummm_match_date"
				value="<?php echo esc_attr( $match_date ); ?>">
		</div>

		<div class="ummm-meta-field">
			<label for="ummm_match_time"><?php esc_html_e( 'وقت المباراة', 'ummm' ); ?></label>
			<input type="time" id="ummm_match_time" name="ummm_match_time"
				value="<?php echo esc_attr( $match_time ); ?>">
		</div>

		<div class="ummm-meta-field ummm-full-width">
			<label for="ummm_stadium"><?php esc_html_e( 'الملعب', 'ummm' ); ?></label>
			<input type="text" id="ummm_stadium" name="ummm_stadium"
				value="<?php echo esc_attr( $stadium ); ?>"
				placeholder="<?php esc_attr_e( 'اسم الملعب', 'ummm' ); ?>">
		</div>

		<div class="ummm-meta-field ummm-full-width">
			<label for="ummm_competition"><?php esc_html_e( 'البطولة / الدوري', 'ummm' ); ?></label>
			<input type="text" id="ummm_competition" name="ummm_competition"
				value="<?php echo esc_attr( $competition ); ?>"
				placeholder="<?php esc_attr_e( 'اسم البطولة أو الدوري', 'ummm' ); ?>">
		</div>

		<p class="ummm-meta-section-title"><?php esc_html_e( 'النتائج', 'ummm' ); ?></p>

		<div class="ummm-meta-field">
			<label><?php esc_html_e( 'النتيجة النهائية', 'ummm' ); ?></label>
			<div class="ummm-score-row">
				<input type="text" name="ummm_score_ft_home"
					value="<?php echo esc_attr( isset( explode( '-', $score_ft )[0] ) ? trim( explode( '-', $score_ft )[0] ) : '' ); ?>"
					placeholder="المستضيف" style="text-align:center;">
				<span class="ummm-score-sep">-</span>
				<input type="text" name="ummm_score_ft_away"
					value="<?php echo esc_attr( isset( explode( '-', $score_ft )[1] ) ? trim( explode( '-', $score_ft )[1] ) : '' ); ?>"
					placeholder="الضيف" style="text-align:center;">
			</div>
		</div>

		<div class="ummm-meta-field">
			<label><?php esc_html_e( 'نتيجة الشوط الأول', 'ummm' ); ?></label>
			<div class="ummm-score-row">
				<input type="text" name="ummm_score_ht_home"
					value="<?php echo esc_attr( isset( explode( '-', $score_ht )[0] ) ? trim( explode( '-', $score_ht )[0] ) : '' ); ?>"
					placeholder="المستضيف" style="text-align:center;">
				<span class="ummm-score-sep">-</span>
				<input type="text" name="ummm_score_ht_away"
					value="<?php echo esc_attr( isset( explode( '-', $score_ht )[1] ) ? trim( explode( '-', $score_ht )[1] ) : '' ); ?>"
					placeholder="الضيف" style="text-align:center;">
			</div>
		</div>

		<p class="ummm-meta-section-title"><?php esc_html_e( 'معلومات إضافية', 'ummm' ); ?></p>

		<div class="ummm-meta-field ummm-full-width">
			<label for="ummm_referee"><?php esc_html_e( 'حكم المباراة', 'ummm' ); ?></label>
			<input type="text" id="ummm_referee" name="ummm_referee"
				value="<?php echo esc_attr( $referee ); ?>"
				placeholder="<?php esc_attr_e( 'اسم الحكم (اختياري)', 'ummm' ); ?>">
		</div>

	</div>
	<?php
}

/**
 * Render the match status sidebar meta box.
 *
 * @param WP_Post $post The current post.
 */
function ummm_render_match_status_meta_box( $post ) {
	$statuses = array(
		'upcoming'  => __( 'قادمة', 'ummm' ),
		'live'      => __( 'مباشرة 🔴', 'ummm' ),
		'finished'  => __( 'انتهت', 'ummm' ),
		'postponed' => __( 'مؤجلة', 'ummm' ),
	);
	$current = $post->post_status;
	if ( ! array_key_exists( $current, $statuses ) ) {
		$current = 'upcoming';
	}
	?>
	<style>
		.ummm-status-box { direction: rtl; }
		.ummm-status-box label { display: flex; align-items: center; gap: 8px; padding: 8px 0; cursor: pointer; font-size: 14px; }
		.ummm-status-box input[type="radio"] { margin: 0; width: 16px; height: 16px; cursor: pointer; accent-color: #267d34; }
		.ummm-status-badge {
			display: inline-block;
			padding: 2px 10px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 600;
			color: #fff;
		}
		.ummm-status-badge.upcoming  { background: #0073aa; }
		.ummm-status-badge.live      { background: #d63638; }
		.ummm-status-badge.finished  { background: #267d34; }
		.ummm-status-badge.postponed { background: #8c8f94; }
	</style>
	<div class="ummm-status-box">
		<?php foreach ( $statuses as $key => $label ) : ?>
		<label>
			<input type="radio" name="ummm_match_status" value="<?php echo esc_attr( $key ); ?>"
				<?php checked( $current, $key ); ?>>
			<span class="ummm-status-badge <?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></span>
		</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Save match meta data.
 *
 * @param int $post_id The post ID being saved.
 */
function ummm_save_match_meta( $post_id ) {
	// Security checks.
	if ( ! isset( $_POST['ummm_match_meta_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['ummm_match_meta_nonce'] ), 'ummm_save_match_meta' )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'manage_options', $post_id ) ) {
		return;
	}

	// Define text fields.
	$text_fields = array(
		'ummm_home_team'   => '_ummm_home_team',
		'ummm_away_team'   => '_ummm_away_team',
		'ummm_match_date'  => '_ummm_match_date',
		'ummm_match_time'  => '_ummm_match_time',
		'ummm_stadium'     => '_ummm_stadium',
		'ummm_competition' => '_ummm_competition',
		'ummm_referee'     => '_ummm_referee',
	);

	foreach ( $text_fields as $field => $meta_key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	// Composite score fields — Full Time.
	$ft_home = isset( $_POST['ummm_score_ft_home'] ) ? sanitize_text_field( wp_unslash( $_POST['ummm_score_ft_home'] ) ) : '';
	$ft_away = isset( $_POST['ummm_score_ft_away'] ) ? sanitize_text_field( wp_unslash( $_POST['ummm_score_ft_away'] ) ) : '';
	if ( '' !== $ft_home || '' !== $ft_away ) {
		update_post_meta( $post_id, '_ummm_score_ft', $ft_home . ' - ' . $ft_away );
	} else {
		delete_post_meta( $post_id, '_ummm_score_ft' );
	}

	// Half Time.
	$ht_home = isset( $_POST['ummm_score_ht_home'] ) ? sanitize_text_field( wp_unslash( $_POST['ummm_score_ht_home'] ) ) : '';
	$ht_away = isset( $_POST['ummm_score_ht_away'] ) ? sanitize_text_field( wp_unslash( $_POST['ummm_score_ht_away'] ) ) : '';
	if ( '' !== $ht_home || '' !== $ht_away ) {
		update_post_meta( $post_id, '_ummm_score_ht', $ht_home . ' - ' . $ht_away );
	} else {
		delete_post_meta( $post_id, '_ummm_score_ht' );
	}

	// Save the match status as post_status.
	$allowed_statuses = array( 'upcoming', 'live', 'finished', 'postponed' );
	if ( isset( $_POST['ummm_match_status'] )
		&& in_array( sanitize_key( $_POST['ummm_match_status'] ), $allowed_statuses, true )
	) {
		// Unhook to prevent infinite loop.
		remove_action( 'save_post_ummm_matches', 'ummm_save_match_meta' );
		wp_update_post( array(
			'ID'          => $post_id,
			'post_status' => sanitize_key( $_POST['ummm_match_status'] ),
		) );
		add_action( 'save_post_ummm_matches', 'ummm_save_match_meta' );
	}
}
add_action( 'save_post_ummm_matches', 'ummm_save_match_meta' );

/**
 * Add custom status columns to the admin list table.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function ummm_match_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb']          = $columns['cb'];
	$new_columns['title']       = __( 'المباراة', 'ummm' );
	$new_columns['match_date']  = __( 'التاريخ', 'ummm' );
	$new_columns['match_teams'] = __( 'الفرق', 'ummm' );
	$new_columns['match_status'] = __( 'الحالة', 'ummm' );
	$new_columns['ummm_team']   = $columns['ummm_team'] ?? __( 'الفئة', 'ummm' );
	$new_columns['date']        = $columns['date'];
	return $new_columns;
}
add_filter( 'manage_ummm_matches_posts_columns', 'ummm_match_columns' );

/**
 * Populate custom columns in admin list table.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function ummm_match_column_data( $column, $post_id ) {
	switch ( $column ) {
		case 'match_date':
			$date = get_post_meta( $post_id, '_ummm_match_date', true );
			$time = get_post_meta( $post_id, '_ummm_match_time', true );
			echo esc_html( $date ? $date . ( $time ? ' | ' . $time : '' ) : '—' );
			break;

		case 'match_teams':
			$home = get_post_meta( $post_id, '_ummm_home_team', true );
			$away = get_post_meta( $post_id, '_ummm_away_team', true );
			echo esc_html( $home ? $home . ' ضد ' . $away : '—' );
			break;

		case 'match_status':
			$status_map = array(
				'upcoming'  => array( 'label' => 'قادمة', 'color' => '#0073aa' ),
				'live'      => array( 'label' => 'مباشرة', 'color' => '#d63638' ),
				'finished'  => array( 'label' => 'انتهت', 'color' => '#267d34' ),
				'postponed' => array( 'label' => 'مؤجلة', 'color' => '#8c8f94' ),
			);
			$post   = get_post( $post_id );
			$status = $post->post_status;
			if ( isset( $status_map[ $status ] ) ) {
				printf(
					'<span style="background:%s;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">%s</span>',
					esc_attr( $status_map[ $status ]['color'] ),
					esc_html( $status_map[ $status ]['label'] )
				);
			}
			break;
	}
}
add_action( 'manage_ummm_matches_posts_custom_column', 'ummm_match_column_data', 10, 2 );

/**
 * Make custom columns sortable.
 *
 * @param array $columns Sortable columns.
 * @return array Modified sortable columns.
 */
function ummm_sortable_columns( $columns ) {
	$columns['match_date'] = 'match_date';
	return $columns;
}
add_filter( 'manage_edit-ummm_matches_sortable_columns', 'ummm_sortable_columns' );

/**
 * Add status filter links to the admin list view.
 *
 * @param array  $views  Current view links.
 * @return array Modified views.
 */
function ummm_add_status_views( $views ) {
	global $typenow, $wpdb;

	if ( 'ummm_matches' !== $typenow ) {
		return $views;
	}

	$custom_statuses = array(
		'upcoming'  => __( 'قادمة', 'ummm' ),
		'live'      => __( 'مباشرة', 'ummm' ),
		'finished'  => __( 'انتهت', 'ummm' ),
		'postponed' => __( 'مؤجلة', 'ummm' ),
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $custom_statuses as $slug => $label ) {
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
			'ummm_matches',
			$slug
		) );

		$current_status = isset( $_GET['post_status'] ) ? sanitize_key( $_GET['post_status'] ) : '';
		$class          = ( $current_status === $slug ) ? ' class="current"' : '';
		$url            = add_query_arg( array( 'post_type' => 'ummm_matches', 'post_status' => $slug ), admin_url( 'edit.php' ) );

		$views[ $slug ] = sprintf(
			'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
			esc_url( $url ),
			$class,
			esc_html( $label ),
			$count
		);
	}

	return $views;
}
add_filter( 'views_edit-ummm_matches', 'ummm_add_status_views' );
