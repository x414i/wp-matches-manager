<?php
/**
 * Template: Timeline View
 * Matches are grouped chronologically by date.
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $query->have_posts() ) {
	echo '<div class="ummm-no-matches"><p>' . esc_html__( 'لا توجد مباريات لعرضها.', 'ummm' ) . '</p></div>';
	return;
}

// Group matches by date.
$groups = array();
while ( $query->have_posts() ) {
	$query->the_post();
	$data       = ummm_get_match_data( get_the_ID() );
	$date_key   = $data['match_date'] ?: __( 'بدون تاريخ', 'ummm' );
	$groups[ $date_key ][] = $data;
}
ksort( $groups );
?>
<div class="ummm-wrapper ummm-timeline-view" dir="rtl">
	<div class="ummm-timeline">
		<?php foreach ( $groups as $date => $matches ) :
			// Format the date nicely if it's a valid date string.
			$display_date = $date;
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
				$ts           = strtotime( $date );
				$display_date = date_i18n( 'l، j F Y', $ts );
			}
			?>
			<div class="ummm-timeline__group">
				<div class="ummm-timeline__date-node">
					<div class="ummm-timeline__date-marker"></div>
					<h3 class="ummm-timeline__date-label"><?php echo esc_html( $display_date ); ?></h3>
				</div>

				<div class="ummm-timeline__matches">
					<?php foreach ( $matches as $data ) :
						$status_cls = esc_attr( ummm_status_class( $data['status'] ) );
						$status_lbl = esc_html( ummm_status_label( $data['status'] ) );
						?>
						<div class="ummm-timeline__item">
							<div class="ummm-timeline__item-inner">

								<div class="ummm-timeline__meta">
									<?php if ( $data['match_time'] ) : ?>
										<span class="ummm-timeline__time"><?php echo esc_html( $data['match_time'] ); ?></span>
									<?php endif; ?>
									<span class="ummm-badge <?php echo $status_cls; ?>"><?php echo $status_lbl; ?></span>
								</div>

								<div class="ummm-timeline__matchup">
									<div class="ummm-timeline__team-block">
										<span class="ummm-timeline__team-logo">
											<?php echo ummm_team_logo_html( $data['home_team_image'], $data['home_team'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
										<span class="ummm-timeline__team"><?php echo esc_html( $data['home_team'] ?: '—' ); ?></span>
									</div>

									<span class="ummm-timeline__score">
										<?php echo $data['score_ft'] ? esc_html( $data['score_ft'] ) : '<em>vs</em>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</span>

									<div class="ummm-timeline__team-block ummm-timeline__team-block--away">
										<span class="ummm-timeline__team-logo">
											<?php echo ummm_team_logo_html( $data['away_team_image'], $data['away_team'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
										<span class="ummm-timeline__team"><?php echo esc_html( $data['away_team'] ?: '—' ); ?></span>
									</div>
								</div>

								<?php if ( $data['stadium'] || $data['competition'] ) : ?>
									<div class="ummm-timeline__details">
										<?php if ( $data['competition'] ) : ?>
											<span><?php echo esc_html( $data['competition'] ); ?></span>
										<?php endif; ?>
										<?php if ( $data['stadium'] ) : ?>
											<span><?php echo esc_html__( 'الملعب: ', 'ummm' ) . esc_html( $data['stadium'] ); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>

							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
