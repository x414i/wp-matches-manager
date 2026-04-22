<?php
/**
 * Template: Tabs View
 * Matches grouped by status: Upcoming / Live / Finished.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Collect matches grouped by status.
$tabs_data = array(
	'upcoming'  => array( 'label' => __( 'القادمة', 'ummm' ), 'matches' => array() ),
	'live'      => array( 'label' => __( 'المباشرة', 'ummm' ), 'matches' => array() ),
	'finished'  => array( 'label' => __( 'المنتهية', 'ummm' ), 'matches' => array() ),
	'postponed' => array( 'label' => __( 'المؤجلة', 'ummm' ), 'matches' => array() ),
);

while ( $query->have_posts() ) {
	$query->the_post();
	$data   = ummm_get_match_data( get_the_ID() );
	$status = $data['status'];
	if ( isset( $tabs_data[ $status ] ) ) {
		$tabs_data[ $status ]['matches'][] = $data;
	}
}

// Find first non-empty tab (prefer live, then upcoming).
$priority = array( 'live', 'upcoming', 'finished', 'postponed' );
$active_tab = 'upcoming';
foreach ( $priority as $p ) {
	if ( ! empty( $tabs_data[ $p ]['matches'] ) ) {
		$active_tab = $p;
		break;
	}
}

// Unique ID for JS scoping if multiple shortcodes on same page.
$uid = 'ummm-tabs-' . wp_unique_id();
?>
<div class="ummm-wrapper ummm-tabs-view" id="<?php echo esc_attr( $uid ); ?>" dir="rtl">

	<div class="ummm-tabs__nav" role="tablist" aria-label="<?php esc_attr_e( 'تصفية المباريات', 'ummm' ); ?>">
		<?php foreach ( $tabs_data as $key => $tab ) :
			$count = count( $tab['matches'] );
			$is_active = ( $key === $active_tab );
			?>
			<button
				class="ummm-tabs__btn<?php echo $is_active ? ' ummm-tabs__btn--active' : ''; ?>"
				role="tab"
				aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
				aria-controls="<?php echo esc_attr( $uid . '-panel-' . $key ); ?>"
				id="<?php echo esc_attr( $uid . '-tab-' . $key ); ?>"
				data-tab="<?php echo esc_attr( $uid . '-panel-' . $key ); ?>"
			>
				<?php echo esc_html( $tab['label'] ); ?>
				<?php if ( $count > 0 ) : ?>
					<span class="ummm-tabs__count"><?php echo esc_html( $count ); ?></span>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>

	<?php foreach ( $tabs_data as $key => $tab ) :
		$is_active = ( $key === $active_tab );
		?>
		<div
			class="ummm-tabs__panel<?php echo $is_active ? ' ummm-tabs__panel--active' : ''; ?>"
			role="tabpanel"
			id="<?php echo esc_attr( $uid . '-panel-' . $key ); ?>"
			aria-labelledby="<?php echo esc_attr( $uid . '-tab-' . $key ); ?>"
		>
			<?php if ( empty( $tab['matches'] ) ) : ?>
				<div class="ummm-no-matches">
					<p><?php esc_html_e( 'لا توجد مباريات في هذا التصنيف.', 'ummm' ); ?></p>
				</div>
			<?php else : ?>
				<div class="ummm-cards-grid">
					<?php foreach ( $tab['matches'] as $data ) :
						$status_cls = esc_attr( ummm_status_class( $data['status'] ) );
						$status_lbl = esc_html( ummm_status_label( $data['status'] ) );
						$is_live    = ( 'live' === $data['status'] );
						?>
						<article class="ummm-card <?php echo $status_cls; ?><?php echo $is_live ? ' ummm-card--live' : ''; ?>">

							<div class="ummm-card__header">
								<span class="ummm-badge <?php echo $status_cls; ?><?php echo $is_live ? ' ummm-badge--pulse' : ''; ?>">
									<?php if ( $is_live ) : ?>
										<span class="ummm-live-dot"></span>
									<?php endif; ?>
									<?php echo $status_lbl; ?>
								</span>
								<?php if ( $data['competition'] ) : ?>
									<span class="ummm-card__competition"><?php echo esc_html( $data['competition'] ); ?></span>
								<?php endif; ?>
							</div>

							<div class="ummm-card__matchup">
								<div class="ummm-card__team ummm-card__team--home">
									<div class="ummm-card__team-logo"><span class="ummm-logo-placeholder">🏠</span></div>
									<span class="ummm-card__team-name"><?php echo esc_html( $data['home_team'] ?: __( 'المستضيف', 'ummm' ) ); ?></span>
								</div>
								<div class="ummm-card__score-block">
									<?php if ( $data['score_ft'] ) : ?>
										<div class="ummm-card__score"><?php echo esc_html( $data['score_ft'] ); ?></div>
										<?php if ( $data['score_ht'] ) : ?>
											<div class="ummm-card__score-ht"><?php esc_html_e( 'ش1: ', 'ummm' ); ?><?php echo esc_html( $data['score_ht'] ); ?></div>
										<?php endif; ?>
									<?php else : ?>
										<div class="ummm-card__score ummm-card__score--vs">VS</div>
									<?php endif; ?>
								</div>
								<div class="ummm-card__team ummm-card__team--away">
									<div class="ummm-card__team-logo"><span class="ummm-logo-placeholder">✈️</span></div>
									<span class="ummm-card__team-name"><?php echo esc_html( $data['away_team'] ?: __( 'الضيف', 'ummm' ) ); ?></span>
								</div>
							</div>

							<div class="ummm-card__footer">
								<?php if ( $data['match_date'] ) : ?>
									<span class="ummm-card__meta">
										📅 <?php echo esc_html( $data['match_date'] ); ?><?php echo $data['match_time'] ? ' | ' . esc_html( $data['match_time'] ) : ''; ?>
									</span>
								<?php endif; ?>
								<?php if ( $data['stadium'] ) : ?>
									<span class="ummm-card__meta">📍 <?php echo esc_html( $data['stadium'] ); ?></span>
								<?php endif; ?>
							</div>

						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
