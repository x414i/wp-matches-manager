<?php
defined( 'ABSPATH' ) || exit;

/**
 * Handles the [ittihad_lineup] shortcode rendering on the frontend.
 */
final class ILB_Shortcode {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function init(): void {
        add_shortcode( 'ittihad_lineup', [ $this, 'render' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_assets' ] );
    }

    public function maybe_enqueue_assets(): void {
        // Always enqueue — shortcodes can appear anywhere
        wp_enqueue_style(
            'ilb-frontend',
            ILB_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            ILB_VERSION
        );
    }

    /**
     * Render shortcode: [ittihad_lineup id="X"]
     */
    public function render( array $atts ): string {
        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'ittihad_lineup' );
        $lineup_id = absint( $atts['id'] );

        if ( ! $lineup_id ) {
            return $this->error( __( 'خطأ: يرجى تحديد معرف التشكيلة.', 'ittihad-lineup' ) );
        }

        $lineup = get_post( $lineup_id );
        if ( ! $lineup || $lineup->post_type !== 'ilb_lineup' || $lineup->post_status !== 'publish' ) {
            return $this->error( __( 'التشكيلة غير موجودة أو غير منشورة.', 'ittihad-lineup' ) );
        }

        $positions  = get_post_meta( $lineup_id, '_ilb_positions',  true );
        $sport      = get_post_meta( $lineup_id, '_ilb_sport_type', true ) ?: 'football';
        $team_id    = get_post_meta( $lineup_id, '_ilb_team_id',    true );
        $field_type = get_post_meta( $lineup_id, '_ilb_field_type', true ) ?: 'default';
        $field_img  = get_post_meta( $lineup_id, '_ilb_field_image', true );

        $players_data = $positions ? json_decode( $positions, true ) : [];

        ob_start();
        ?>
        <div class="ilb-lineup-wrap" dir="rtl" data-sport="<?php echo esc_attr( $sport ); ?>">

            <!-- Desktop: Field view -->
            <div class="ilb-lineup-field-view">
                <div class="ilb-lineup-header">
                    <?php if ( $team_id ) : ?>
                        <h3 class="ilb-lineup-team"><?php echo esc_html( get_the_title( $team_id ) ); ?></h3>
                    <?php endif; ?>
                    <h4 class="ilb-lineup-name"><?php echo esc_html( $lineup->post_title ); ?></h4>
                </div>

                <div class="ilb-lineup-pitch ilb-sport-<?php echo esc_attr( $sport ); ?>"
                     style="<?php echo $field_type === 'custom' && $field_img ? 'background-image:url(' . esc_url( $field_img ) . ');background-size:cover;background-position:center;' : ''; ?>">

                    <div class="ilb-lineup-pitch__inner">
                        <?php foreach ( $players_data as $player_id => $pos ) :
                            $x    = floatval( $pos['x'] ?? 50 );
                            $y    = floatval( $pos['y'] ?? 50 );
                            $x    = max( 3, min( 97, $x ) );
                            $y    = max( 3, min( 97, $y ) );
                            $name = $pos['name']     ?? '';
                            $num  = $pos['number']   ?? '';
                            $ppos = $pos['position'] ?? '';
                            $photo= $pos['photo']    ?? '';
                        ?>
                            <div class="ilb-lineup-player"
                                 style="left:<?php echo esc_attr( $x ); ?>%;top:<?php echo esc_attr( $y ); ?>%;">
                                <div class="ilb-lineup-player__avatar">
                                    <?php if ( $photo ) : ?>
                                        <img src="<?php echo esc_url( $photo ); ?>"
                                             alt="<?php echo esc_attr( $name ); ?>"
                                             loading="lazy" />
                                    <?php else : ?>
                                        <div class="ilb-lineup-player__avatar-placeholder">
                                            <?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ( $num ) : ?>
                                        <span class="ilb-lineup-player__number"><?php echo esc_html( $num ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="ilb-lineup-player__info">
                                    <span class="ilb-lineup-player__name"><?php echo esc_html( $name ); ?></span>
                                    <?php if ( $ppos ) : ?>
                                        <span class="ilb-lineup-player__pos"><?php echo esc_html( $ppos ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Mobile: List view -->
            <div class="ilb-lineup-list-view">
                <div class="ilb-lineup-list-header">
                    <?php if ( $team_id ) : ?>
                        <h3><?php echo esc_html( get_the_title( $team_id ) ); ?></h3>
                    <?php endif; ?>
                    <h4><?php echo esc_html( $lineup->post_title ); ?></h4>
                </div>
                <div class="ilb-lineup-list">
                    <?php foreach ( $players_data as $player_id => $pos ) :
                        $name  = $pos['name']     ?? '';
                        $num   = $pos['number']   ?? '';
                        $ppos  = $pos['position'] ?? '';
                        $photo = $pos['photo']    ?? '';
                    ?>
                        <div class="ilb-list-player">
                            <div class="ilb-list-player__avatar">
                                <?php if ( $photo ) : ?>
                                    <img src="<?php echo esc_url( $photo ); ?>"
                                         alt="<?php echo esc_attr( $name ); ?>"
                                         loading="lazy" />
                                <?php else : ?>
                                    <div class="ilb-list-player__placeholder">
                                        <?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ilb-list-player__info">
                                <span class="ilb-list-player__name"><?php echo esc_html( $name ); ?></span>
                                <?php if ( $ppos ) : ?>
                                    <span class="ilb-list-player__pos"><?php echo esc_html( $ppos ); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ( $num ) : ?>
                                <span class="ilb-list-player__num"><?php echo esc_html( $num ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if ( empty( $players_data ) ) : ?>
                        <p class="ilb-no-players"><?php esc_html_e( 'لم يتم إضافة لاعبين بعد', 'ittihad-lineup' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    private function error( string $msg ): string {
        return '<div class="ilb-error">' . esc_html( $msg ) . '</div>';
    }
}
