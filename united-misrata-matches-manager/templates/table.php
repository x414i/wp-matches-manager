<?php
/**
 * Template: Table View
 *
 * Variables available from renderer.php:
 *   $query  — WP_Query result.
 *   $params — shortcode params.
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
<div class="ummm-wrapper ummm-table-view" dir="rtl">
	<div class="ummm-table-scroll">
		<table class="ummm-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'التاريخ', 'ummm' ); ?></th>
					<th><?php esc_html_e( 'الوقت', 'ummm' ); ?></th>
					<th><?php esc_html_e( 'الفريق المستضيف', 'ummm' ); ?></th>
					<th><?php esc_html_e( 'النتيجة', 'ummm' ); ?></th>
					<th><?php esc_html_e( 'الفريق الضيف', 'ummm' ); ?></th>
					<th><?php esc_html_e( 'الملعب', 'ummm' ); ?></th>
					<th><?php esc_html_e( 'البطولة', 'ummm' ); ?></th>
					<th><?php esc_html_e( 'الحالة', 'ummm' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$data = ummm_get_match_data( get_the_ID() );
					$score_display = $data['score_ft'] ? esc_html( $data['score_ft'] ) : '<span class="ummm-score-placeholder">vs</span>';
					?>
					<tr>
						<td><?php echo esc_html( $data['match_date'] ?: '—' ); ?></td>
						<td><?php echo esc_html( $data['match_time'] ?: '—' ); ?></td>
						<td class="ummm-team-cell">
							<div class="ummm-table-team">
								<span class="ummm-table-team__logo">
									<?php echo ummm_team_logo_html( $data['home_team_image'], $data['home_team'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
								<span><?php echo esc_html( $data['home_team'] ?: '—' ); ?></span>
							</div>
						</td>
						<td class="ummm-score-cell"><?php echo $score_display; // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
						<td class="ummm-team-cell">
							<div class="ummm-table-team">
								<span class="ummm-table-team__logo">
									<?php echo ummm_team_logo_html( $data['away_team_image'], $data['away_team'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
								<span><?php echo esc_html( $data['away_team'] ?: '—' ); ?></span>
							</div>
						</td>
						<td><?php echo esc_html( $data['stadium'] ?: '—' ); ?></td>
						<td><?php echo esc_html( $data['competition'] ?: '—' ); ?></td>
						<td>
							<span class="ummm-badge <?php echo esc_attr( ummm_status_class( $data['status'] ) ); ?>">
								<?php echo esc_html( ummm_status_label( $data['status'] ) ); ?>
							</span>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>
</div>
