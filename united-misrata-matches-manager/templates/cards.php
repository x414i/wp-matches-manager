<?php
/**
 * Template: Cards View
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
?>
<div class="ummm-wrapper ummm-cards-view" dir="rtl">
	<div class="ummm-cards-grid">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			$data        = ummm_get_match_data( get_the_ID() );
			$status_cls  = esc_attr( ummm_status_class( $data['status'] ) );
			$status_lbl  = esc_html( ummm_status_label( $data['status'] ) );
			$is_live     = ( 'live' === $data['status'] );
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
						<div class="ummm-card__team-logo">
							<span class="ummm-logo-placeholder">🏠</span>
						</div>
						<span class="ummm-card__team-name"><?php echo esc_html( $data['home_team'] ?: __( 'الفريق المستضيف', 'ummm' ) ); ?></span>
					</div>

					<div class="ummm-card__score-block">
						<?php if ( $data['score_ft'] ) : ?>
							<div class="ummm-card__score"><?php echo esc_html( $data['score_ft'] ); ?></div>
							<?php if ( $data['score_ht'] ) : ?>
								<div class="ummm-card__score-ht"><?php echo esc_html__( 'ش1: ', 'ummm' ); ?><?php echo esc_html( $data['score_ht'] ); ?></div>
							<?php endif; ?>
						<?php else : ?>
							<div class="ummm-card__score ummm-card__score--vs">VS</div>
						<?php endif; ?>
					</div>

					<div class="ummm-card__team ummm-card__team--away">
						<div class="ummm-card__team-logo">
							<span class="ummm-logo-placeholder">✈️</span>
						</div>
						<span class="ummm-card__team-name"><?php echo esc_html( $data['away_team'] ?: __( 'الفريق الضيف', 'ummm' ) ); ?></span>
					</div>

				</div>

				<div class="ummm-card__footer">
					<?php if ( $data['match_date'] ) : ?>
						<span class="ummm-card__meta">
							<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="1" y="3" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M1 7h14" stroke="currentColor" stroke-width="1.5"/><path d="M5 1v4M11 1v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
							<?php echo esc_html( $data['match_date'] ); ?>
							<?php echo $data['match_time'] ? ' | ' . esc_html( $data['match_time'] ) : ''; ?>
						</span>
					<?php endif; ?>
					<?php if ( $data['stadium'] ) : ?>
						<span class="ummm-card__meta">
							<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 1C5.24 1 3 3.24 3 6c0 3.75 5 9 5 9s5-5.25 5-9c0-2.76-2.24-5-5-5z" stroke="currentColor" stroke-width="1.5"/><circle cx="8" cy="6" r="1.5" stroke="currentColor" stroke-width="1.5"/></svg>
							<?php echo esc_html( $data['stadium'] ); ?>
						</span>
					<?php endif; ?>
				</div>

			</article>
		<?php endwhile; ?>
	</div>
</div>
