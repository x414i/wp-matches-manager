<?php
/**
 * Taxonomy Meta — Team Image field using WordPress Media Uploader.
 *
 * Adds an image upload field to the ummm_team taxonomy (Add / Edit screens).
 * Images are stored as attachment IDs via the term meta API.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Admin scripts for the media uploader ──────────────────────────────────────

/**
 * Enqueue the WP media uploader scripts on taxonomy term screens.
 *
 * @param string $hook Current admin page hook suffix.
 */
function ummm_enqueue_taxonomy_scripts( $hook ) {
	if ( 'edit-tags.php' !== $hook && 'term.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'ummm_team' !== $screen->taxonomy ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'ummm-taxonomy-image',
		UMMM_PLUGIN_URL . 'admin/js/taxonomy-image.js',
		array( 'jquery' ),
		UMMM_VERSION,
		true
	);
	wp_localize_script( 'ummm-taxonomy-image', 'ummmTaxI18n', array(
		'title'  => __( 'اختيار صورة الفريق', 'ummm' ),
		'button' => __( 'استخدام هذه الصورة', 'ummm' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'ummm_enqueue_taxonomy_scripts' );

// ── Add Term form (new term) ──────────────────────────────────────────────────

/**
 * Render the image field in the "Add New Term" form.
 *
 * @param string $taxonomy The taxonomy slug.
 */
function ummm_team_add_image_field( $taxonomy ) {
	?>
	<div class="form-field">
		<label><?php esc_html_e( 'صورة الفريق', 'ummm' ); ?></label>
		<div id="ummm-team-image-wrap">
			<div id="ummm-team-image-preview" style="margin-bottom:10px;"></div>
			<input type="hidden" id="ummm-team-image-id" name="ummm_team_image" value="">
			<button type="button" class="button" id="ummm-team-image-upload"><?php esc_html_e( 'رفع صورة الفريق', 'ummm' ); ?></button>
			<button type="button" class="button" id="ummm-team-image-remove" style="display:none;color:#b32d2e;"><?php esc_html_e( 'إزالة الصورة', 'ummm' ); ?></button>
		</div>
		<p class="description"><?php esc_html_e( 'صورة أو شعار الفريق — تظهر في واجهة عرض المباريات.', 'ummm' ); ?></p>
	</div>
	<?php
}
add_action( 'ummm_team_add_form_fields', 'ummm_team_add_image_field' );

// ── Edit Term form (existing term) ───────────────────────────────────────────

/**
 * Render the image field in the "Edit Term" screen.
 *
 * @param WP_Term $term The term object being edited.
 */
function ummm_team_edit_image_field( $term ) {
	$image_id  = absint( get_term_meta( $term->term_id, 'ummm_team_image', true ) );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	?>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'صورة الفريق', 'ummm' ); ?></label></th>
		<td>
			<div id="ummm-team-image-wrap">
				<div id="ummm-team-image-preview" style="margin-bottom:10px;">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" style="max-width:120px;height:auto;border-radius:8px;border:2px solid #dcdcde;">
					<?php endif; ?>
				</div>
				<input type="hidden" id="ummm-team-image-id" name="ummm_team_image" value="<?php echo esc_attr( $image_id ); ?>">
				<button type="button" class="button" id="ummm-team-image-upload">
					<?php echo $image_url ? esc_html__( 'تغيير الصورة', 'ummm' ) : esc_html__( 'رفع صورة الفريق', 'ummm' ); ?>
				</button>
				<button type="button" class="button" id="ummm-team-image-remove" style="<?php echo $image_url ? '' : 'display:none;'; ?>color:#b32d2e;">
					<?php esc_html_e( 'إزالة الصورة', 'ummm' ); ?>
				</button>
			</div>
			<p class="description"><?php esc_html_e( 'صورة أو شعار الفريق — تظهر في واجهة عرض المباريات.', 'ummm' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'ummm_team_edit_form_fields', 'ummm_team_edit_image_field' );

// ── Save / Create callbacks ──────────────────────────────────────────────────

/**
 * Save the team image on term creation.
 *
 * @param int $term_id New term ID.
 */
function ummm_save_team_image_on_create( $term_id ) {
	if ( isset( $_POST['ummm_team_image'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta( $term_id, 'ummm_team_image', absint( $_POST['ummm_team_image'] ) );
	}
}
add_action( 'created_ummm_team', 'ummm_save_team_image_on_create' );

/**
 * Save the team image on term update.
 *
 * @param int $term_id Updated term ID.
 */
function ummm_save_team_image_on_edit( $term_id ) {
	if ( isset( $_POST['ummm_team_image'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$val = absint( $_POST['ummm_team_image'] );
		if ( $val ) {
			update_term_meta( $term_id, 'ummm_team_image', $val );
		} else {
			delete_term_meta( $term_id, 'ummm_team_image' );
		}
	}
}
add_action( 'edited_ummm_team', 'ummm_save_team_image_on_edit' );

// ── Admin list table image column ────────────────────────────────────────────

/**
 * Add an image column to the Teams taxonomy list table.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function ummm_team_add_image_column( $columns ) {
	$new = array();
	foreach ( $columns as $key => $val ) {
		if ( 'name' === $key ) {
			$new['ummm_image'] = __( 'الشعار', 'ummm' );
		}
		$new[ $key ] = $val;
	}
	return $new;
}
add_filter( 'manage_edit-ummm_team_columns', 'ummm_team_add_image_column' );

/**
 * Populate the image column content.
 *
 * @param string $content     Existing column content.
 * @param string $column_name Column slug.
 * @param int    $term_id     Term ID.
 * @return string Modified content.
 */
function ummm_team_image_column_content( $content, $column_name, $term_id ) {
	if ( 'ummm_image' !== $column_name ) {
		return $content;
	}

	$image_id  = absint( get_term_meta( $term_id, 'ummm_team_image', true ) );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';

	if ( $image_url ) {
		return sprintf(
			'<img src="%s" alt="" style="width:36px;height:36px;border-radius:50%%;object-fit:cover;border:2px solid #dcdcde;">',
			esc_url( $image_url )
		);
	}

	return '<span style="display:inline-block;width:36px;height:36px;border-radius:50%;background:#f0f0f0;text-align:center;line-height:36px;font-size:16px;">⚽</span>';
}
add_filter( 'manage_ummm_team_custom_column', 'ummm_team_image_column_content', 10, 3 );
