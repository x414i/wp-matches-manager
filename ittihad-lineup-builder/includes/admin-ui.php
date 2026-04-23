<?php
defined( 'ABSPATH' ) || exit;

/**
 * Handles all admin-facing functionality: menus, meta boxes, assets, and the lineup builder page.
 */
final class ILB_Admin_UI {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function init(): void {
        add_action( 'admin_menu',            [ $this, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'add_meta_boxes',        [ $this, 'register_meta_boxes' ] );
        add_action( 'save_post_ilb_player',  [ $this, 'save_player_meta' ], 10, 2 );
        add_action( 'save_post_ilb_team',    [ $this, 'save_team_meta' ],   10, 2 );
        add_action( 'save_post_ilb_lineup',  [ $this, 'save_lineup_meta' ], 10, 2 );
        add_filter( 'manage_ilb_player_posts_columns',       [ $this, 'player_columns' ] );
        add_action( 'manage_ilb_player_posts_custom_column', [ $this, 'player_column_data' ], 10, 2 );
        add_filter( 'manage_ilb_lineup_posts_columns',       [ $this, 'lineup_columns' ] );
        add_action( 'manage_ilb_lineup_posts_custom_column', [ $this, 'lineup_column_data' ], 10, 2 );
    }

    // ─── Menus ────────────────────────────────────────────────────────────────

    public function register_menus(): void {
        add_menu_page(
            __( 'نادي الاتحاد المصراتي', 'ittihad-lineup' ),
            __( 'الاتحاد المصراتي', 'ittihad-lineup' ),
            'edit_posts',
            'ittihad-main',
        );
        add_submenu_page(
            'ittihad-main',
            __( 'لوحة التحكم', 'ittihad-lineup' ),
            __( 'لوحة التحكم', 'ittihad-lineup' ),
            'edit_posts',
            'ittihad-main',
            [ $this, 'render_dashboard' ]
        );

        add_submenu_page(
            'ittihad-main',
            __( 'منشئ التشكيلة', 'ittihad-lineup' ),
            __( '⚽ منشئ التشكيلة', 'ittihad-lineup' ),
            'edit_posts',
            'ilb_builder',
            [ $this, 'render_builder_page' ]
        );

        // Custom post types submenus are implicitly added by their register_post_type definitions.
    }

    // ─── Assets ───────────────────────────────────────────────────────────────

    public function enqueue_assets( string $hook ): void {
        $post_type = get_current_screen()->post_type ?? '';
        $is_ilb_screen = in_array( $post_type, [ 'ilb_player', 'ilb_team', 'ilb_lineup' ], true )
            || str_contains( $hook, 'ilb_' );

        if ( ! $is_ilb_screen ) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'ilb-admin',
            ILB_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ILB_VERSION
        );

        wp_enqueue_script(
            'ilb-admin',
            ILB_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            ILB_VERSION,
            true
        );

        wp_localize_script( 'ilb-admin', 'ILB', [
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'ilb_nonce' ),
            'pluginUrl'  => ILB_PLUGIN_URL,
            'strings'    => [
                'saving'       => __( 'جارٍ الحفظ...', 'ittihad-lineup' ),
                'saved'        => __( 'تم الحفظ بنجاح ✓', 'ittihad-lineup' ),
                'error'        => __( 'حدث خطأ، يرجى المحاولة مجدداً', 'ittihad-lineup' ),
                'selectTeam'   => __( 'اختر الفريق أولاً', 'ittihad-lineup' ),
                'noPlayers'    => __( 'لا يوجد لاعبون في هذا الفريق', 'ittihad-lineup' ),
                'dragHint'     => __( 'اسحب اللاعبين إلى الملعب', 'ittihad-lineup' ),
                'removePlayer' => __( 'إزالة اللاعب', 'ittihad-lineup' ),
                'confirmRemove'=> __( 'هل تريد إزالة هذا اللاعب من التشكيلة؟', 'ittihad-lineup' ),
            ],
            'teams'      => $this->get_all_teams(),
            'lineups'    => $this->get_all_lineups(),
        ] );
    }

    private function get_all_teams(): array {
        $teams = get_posts( [ 'post_type' => 'ilb_team', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        return array_map( fn( $t ) => [
            'id'         => $t->ID,
            'name'       => $t->post_title,
            'sport_type' => get_post_meta( $t->ID, '_ilb_sport_type', true ) ?: 'football',
        ], $teams );
    }

    private function get_all_lineups(): array {
        $lineups = get_posts( [ 'post_type' => 'ilb_lineup', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        return array_map( fn( $l ) => [
            'id'   => $l->ID,
            'name' => $l->post_title,
        ], $lineups );
    }

    // ─── Meta Boxes ──────────────────────────────────────────────────────────

    public function register_meta_boxes(): void {
        // Player meta box
        add_meta_box( 'ilb_player_details', __( 'تفاصيل اللاعب', 'ittihad-lineup' ),
            [ $this, 'render_player_meta_box' ], 'ilb_player', 'normal', 'high' );

        // Team meta box
        add_meta_box( 'ilb_team_details', __( 'تفاصيل الفريق', 'ittihad-lineup' ),
            [ $this, 'render_team_meta_box' ], 'ilb_team', 'normal', 'high' );

        // Lineup meta box
        add_meta_box( 'ilb_lineup_details', __( 'إعدادات التشكيلة', 'ittihad-lineup' ),
            [ $this, 'render_lineup_meta_box' ], 'ilb_lineup', 'normal', 'high' );

        // Shortcode helper
        add_meta_box( 'ilb_shortcode_helper', __( 'الشورت كود', 'ittihad-lineup' ),
            [ $this, 'render_shortcode_meta_box' ], 'ilb_lineup', 'side', 'default' );
    }

    // ─── Render Player Meta Box ──────────────────────────────────────────────

    public function render_player_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'ilb_player_meta', 'ilb_player_nonce' );

        $position      = get_post_meta( $post->ID, '_ilb_position',      true );
        $jersey_number = get_post_meta( $post->ID, '_ilb_jersey_number', true );
        $team_ids      = get_post_meta( $post->ID, '_ilb_player_team',   true );
        if ( ! is_array( $team_ids ) ) {
            $team_ids = $team_ids ? (array) $team_ids : [];
        }

        $teams = get_posts( [ 'post_type' => 'ilb_team', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        ?>
        <div class="ilb-meta-grid">
            <div class="ilb-field">
                <label for="ilb_position"><?php esc_html_e( 'مركز اللاعب', 'ittihad-lineup' ); ?></label>
                <input type="text" id="ilb_position" name="ilb_position"
                       value="<?php echo esc_attr( $position ); ?>"
                       placeholder="<?php esc_attr_e( 'مثال: حارس مرمى، مهاجم...', 'ittihad-lineup' ); ?>" />
            </div>
            <div class="ilb-field">
                <label for="ilb_jersey_number"><?php esc_html_e( 'رقم القميص', 'ittihad-lineup' ); ?></label>
                <input type="number" id="ilb_jersey_number" name="ilb_jersey_number"
                       value="<?php echo esc_attr( $jersey_number ); ?>" min="1" max="99" />
            </div>
            <div class="ilb-field ilb-field--full">
                <label for="ilb_player_team"><?php esc_html_e( 'الفريق', 'ittihad-lineup' ); ?></label>
                <select id="ilb_player_team" name="ilb_player_team[]" multiple="multiple" style="height:100px;">
                    <?php foreach ( $teams as $team ) : ?>
                        <option value="<?php echo esc_attr( $team->ID ); ?>"
                            <?php selected( in_array( $team->ID, $team_ids ) ); ?>>
                            <?php echo esc_html( $team->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( 'اضغط مطولاً على الزر Ctrl (Windows) أو Command (Mac) لاختيار عدة فرق', 'ittihad-lineup' ); ?></p>
            </div>
        </div>
        <?php
    }

    // ─── Render Team Meta Box ────────────────────────────────────────────────

    public function render_team_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'ilb_team_meta', 'ilb_team_nonce' );

        $sport_type = get_post_meta( $post->ID, '_ilb_sport_type', true ) ?: 'football';
        ?>
        <div class="ilb-meta-grid">
            <div class="ilb-field ilb-field--full">
                <label for="ilb_sport_type"><?php esc_html_e( 'نوع الرياضة', 'ittihad-lineup' ); ?></label>
                <select id="ilb_sport_type" name="ilb_sport_type">
                    <option value="football"   <?php selected( $sport_type, 'football' ); ?>><?php esc_html_e( 'كرة القدم', 'ittihad-lineup' ); ?></option>
                    <option value="volleyball" <?php selected( $sport_type, 'volleyball' ); ?>><?php esc_html_e( 'الكرة الطائرة', 'ittihad-lineup' ); ?></option>
                    <option value="basketball" <?php selected( $sport_type, 'basketball' ); ?>><?php esc_html_e( 'كرة السلة', 'ittihad-lineup' ); ?></option>
                    <option value="handball"   <?php selected( $sport_type, 'handball' ); ?>><?php esc_html_e( 'كرة اليد', 'ittihad-lineup' ); ?></option>
                    <option value="futsal"     <?php selected( $sport_type, 'futsal' ); ?>><?php esc_html_e( 'كرة القدم الصالات', 'ittihad-lineup' ); ?></option>
                    <option value="custom"     <?php selected( $sport_type, 'custom' ); ?>><?php esc_html_e( 'ملعب مخصص', 'ittihad-lineup' ); ?></option>
                </select>
            </div>
        </div>
        <?php
    }

    // ─── Render Lineup Meta Box ──────────────────────────────────────────────

    public function render_lineup_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'ilb_lineup_meta', 'ilb_lineup_nonce' );

        $team_id    = get_post_meta( $post->ID, '_ilb_team_id',    true );
        $sport      = get_post_meta( $post->ID, '_ilb_sport_type', true ) ?: 'football';
        $field_type = get_post_meta( $post->ID, '_ilb_field_type', true ) ?: 'default';
        $field_img  = get_post_meta( $post->ID, '_ilb_field_image', true );

        $teams = get_posts( [ 'post_type' => 'ilb_team', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        ?>
        <div class="ilb-meta-grid">
            <div class="ilb-field">
                <label for="ilb_lineup_team"><?php esc_html_e( 'الفريق', 'ittihad-lineup' ); ?></label>
                <select id="ilb_lineup_team" name="ilb_lineup_team">
                    <option value=""><?php esc_html_e( 'اختر الفريق', 'ittihad-lineup' ); ?></option>
                    <?php foreach ( $teams as $team ) : ?>
                        <option value="<?php echo esc_attr( $team->ID ); ?>"
                                data-sport="<?php echo esc_attr( get_post_meta( $team->ID, '_ilb_sport_type', true ) ?: 'football' ); ?>"
                            <?php selected( $team_id, $team->ID ); ?>>
                            <?php echo esc_html( $team->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ilb-field">
                <label for="ilb_lineup_sport"><?php esc_html_e( 'نوع الرياضة', 'ittihad-lineup' ); ?></label>
                <select id="ilb_lineup_sport" name="ilb_lineup_sport">
                    <option value="football"   <?php selected( $sport, 'football' ); ?>><?php esc_html_e( 'كرة القدم', 'ittihad-lineup' ); ?></option>
                    <option value="volleyball" <?php selected( $sport, 'volleyball' ); ?>><?php esc_html_e( 'الكرة الطائرة', 'ittihad-lineup' ); ?></option>
                    <option value="basketball" <?php selected( $sport, 'basketball' ); ?>><?php esc_html_e( 'كرة السلة', 'ittihad-lineup' ); ?></option>
                    <option value="handball"   <?php selected( $sport, 'handball' ); ?>><?php esc_html_e( 'كرة اليد', 'ittihad-lineup' ); ?></option>
                    <option value="futsal"     <?php selected( $sport, 'futsal' ); ?>><?php esc_html_e( 'كرة القدم الصالات', 'ittihad-lineup' ); ?></option>
                </select>
            </div>
            <div class="ilb-field ilb-field--full">
                <label for="ilb_field_type"><?php esc_html_e( 'نوع الملعب', 'ittihad-lineup' ); ?></label>
                <select id="ilb_field_type" name="ilb_field_type">
                    <option value="default" <?php selected( $field_type, 'default' ); ?>><?php esc_html_e( 'افتراضي (CSS)', 'ittihad-lineup' ); ?></option>
                    <option value="custom"  <?php selected( $field_type, 'custom' ); ?>><?php esc_html_e( 'صورة مخصصة', 'ittihad-lineup' ); ?></option>
                </select>
            </div>
            <div class="ilb-field ilb-field--full" id="ilb_custom_field_wrap" style="<?php echo $field_type === 'custom' ? '' : 'display:none'; ?>">
                <label><?php esc_html_e( 'صورة الملعب المخصصة', 'ittihad-lineup' ); ?></label>
                <div class="ilb-media-uploader">
                    <input type="hidden" id="ilb_field_image" name="ilb_field_image" value="<?php echo esc_url( $field_img ); ?>" />
                    <button type="button" class="button" id="ilb_upload_field_btn">
                        <?php esc_html_e( 'اختر صورة', 'ittihad-lineup' ); ?>
                    </button>
                    <?php if ( $field_img ) : ?>
                        <img src="<?php echo esc_url( $field_img ); ?>" id="ilb_field_preview" class="ilb-preview-img" />
                    <?php else : ?>
                        <img src="" id="ilb_field_preview" class="ilb-preview-img" style="display:none;" />
                    <?php endif; ?>
                    <button type="button" class="button-link-delete" id="ilb_remove_field_btn" <?php echo $field_img ? '' : 'style="display:none;"'; ?>>
                        <?php esc_html_e( 'إزالة', 'ittihad-lineup' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <?php if ( $post->ID && $post->post_status === 'publish' ) : ?>
            <div class="ilb-builder-link">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ilb_builder&lineup_id=' . $post->ID ) ); ?>"
                   class="button button-primary button-hero">
                    ⚽ <?php esc_html_e( 'فتح منشئ التشكيلة', 'ittihad-lineup' ); ?>
                </a>
            </div>
        <?php elseif ( 'auto-draft' === $post->post_status || ! $post->ID ) : ?>
            <p class="ilb-info"><?php esc_html_e( 'احفظ التشكيلة أولاً لتتمكن من فتح المنشئ', 'ittihad-lineup' ); ?></p>
        <?php endif; ?>
        <?php
    }

    // ─── Render Shortcode Meta Box ────────────────────────────────────────────

    public function render_shortcode_meta_box( WP_Post $post ): void {
        if ( ! $post->ID ) return;
        ?>
        <div class="ilb-shortcode-box">
            <p><?php esc_html_e( 'انسخ هذا الشورت كود وضعه في أي صفحة:', 'ittihad-lineup' ); ?></p>
            <code class="ilb-shortcode-code" onclick="this.select()">[ittihad_lineup id="<?php echo esc_attr( $post->ID ); ?>"]</code>
            <button type="button" class="button ilb-copy-btn" data-clipboard="[ittihad_lineup id=&quot;<?php echo esc_attr( $post->ID ); ?>&quot;]">
                <?php esc_html_e( 'نسخ', 'ittihad-lineup' ); ?>
            </button>
        </div>
        <?php
    }

    // ─── Save Metas ──────────────────────────────────────────────────────────

    public function save_player_meta( int $post_id, WP_Post $post ): void {
        if ( ! isset( $_POST['ilb_player_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['ilb_player_nonce'], 'ilb_player_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        update_post_meta( $post_id, '_ilb_position',      sanitize_text_field( $_POST['ilb_position']      ?? '' ) );
        update_post_meta( $post_id, '_ilb_jersey_number', sanitize_text_field( $_POST['ilb_jersey_number'] ?? '' ) );
        $teams = isset( $_POST['ilb_player_team'] ) && is_array( $_POST['ilb_player_team'] ) 
            ? array_map( 'strval', array_map( 'absint', $_POST['ilb_player_team'] ) ) 
            : [];
        update_post_meta( $post_id, '_ilb_player_team',   $teams );
    }

    public function save_team_meta( int $post_id, WP_Post $post ): void {
        if ( ! isset( $_POST['ilb_team_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['ilb_team_nonce'], 'ilb_team_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        update_post_meta( $post_id, '_ilb_sport_type', sanitize_text_field( $_POST['ilb_sport_type'] ?? 'football' ) );
    }

    public function save_lineup_meta( int $post_id, WP_Post $post ): void {
        if ( ! isset( $_POST['ilb_lineup_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['ilb_lineup_nonce'], 'ilb_lineup_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        update_post_meta( $post_id, '_ilb_team_id',    absint( $_POST['ilb_lineup_team']  ?? 0 ) );
        update_post_meta( $post_id, '_ilb_sport_type', sanitize_text_field( $_POST['ilb_lineup_sport'] ?? 'football' ) );
        update_post_meta( $post_id, '_ilb_field_type', sanitize_text_field( $_POST['ilb_field_type']   ?? 'default' ) );
        update_post_meta( $post_id, '_ilb_field_image', esc_url_raw( $_POST['ilb_field_image'] ?? '' ) );
    }

    // ─── Custom Columns ──────────────────────────────────────────────────────

    public function player_columns( array $columns ): array {
        $new = [];
        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;
            if ( $key === 'title' ) {
                $new['ilb_photo']    = __( 'الصورة', 'ittihad-lineup' );
                $new['ilb_position'] = __( 'المركز', 'ittihad-lineup' );
                $new['ilb_number']   = __( 'الرقم', 'ittihad-lineup' );
                $new['ilb_team']     = __( 'الفريق', 'ittihad-lineup' );
            }
        }
        return $new;
    }

    public function player_column_data( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'ilb_photo':
                $thumb = get_the_post_thumbnail( $post_id, [ 50, 50 ] );
                echo $thumb ?: '—';
                break;
            case 'ilb_position':
                echo esc_html( get_post_meta( $post_id, '_ilb_position', true ) ?: '—' );
                break;
            case 'ilb_number':
                echo esc_html( get_post_meta( $post_id, '_ilb_jersey_number', true ) ?: '—' );
                break;
            case 'ilb_team':
                $team_ids = get_post_meta( $post_id, '_ilb_player_team', true );
                if ( ! is_array( $team_ids ) ) $team_ids = $team_ids ? (array) $team_ids : [];
                $titles = array_filter( array_map( 'get_the_title', $team_ids ) );
                echo $titles ? esc_html( implode( '، ', $titles ) ) : '—';
                break;
        }
    }

    public function lineup_columns( array $columns ): array {
        $new = [];
        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;
            if ( $key === 'title' ) {
                $new['ilb_team']      = __( 'الفريق', 'ittihad-lineup' );
                $new['ilb_sport']     = __( 'الرياضة', 'ittihad-lineup' );
                $new['ilb_shortcode'] = __( 'الشورت كود', 'ittihad-lineup' );
                $new['ilb_builder']   = __( 'المنشئ', 'ittihad-lineup' );
            }
        }
        return $new;
    }

    public function lineup_column_data( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'ilb_team':
                $team_id = get_post_meta( $post_id, '_ilb_team_id', true );
                echo $team_id ? esc_html( get_the_title( $team_id ) ) : '—';
                break;
            case 'ilb_sport':
                $sport_map = [
                    'football'   => 'كرة القدم',
                    'volleyball' => 'الكرة الطائرة',
                    'basketball' => 'كرة السلة',
                    'handball'   => 'كرة اليد',
                    'futsal'     => 'كرة القدم الصالات',
                ];
                $sport = get_post_meta( $post_id, '_ilb_sport_type', true );
                echo esc_html( $sport_map[ $sport ] ?? $sport ?: '—' );
                break;
            case 'ilb_shortcode':
                echo '<code>[ittihad_lineup id="' . esc_attr( $post_id ) . '"]</code>';
                break;
            case 'ilb_builder':
                $url = admin_url( 'admin.php?page=ilb_builder&lineup_id=' . $post_id );
                echo '<a href="' . esc_url( $url ) . '" class="button button-small">' . esc_html__( 'فتح المنشئ', 'ittihad-lineup' ) . '</a>';
                break;
        }
    }

    // ─── Dashboard Page ───────────────────────────────────────────────────────

    public function render_dashboard(): void {
        $player_count = wp_count_posts( 'ilb_player' )->publish ?? 0;
        $team_count   = wp_count_posts( 'ilb_team' )->publish   ?? 0;
        $lineup_count = wp_count_posts( 'ilb_lineup' )->publish ?? 0;
        ?>
        <div class="wrap ilb-wrap" dir="rtl">
            <div class="ilb-container">
                <div class="ilb-dashboard">
                <div class="ilb-dashboard__header">
                    <div class="ilb-dashboard__logo">🏆</div>
                    <div>
                        <h1><?php esc_html_e( 'نادي الاتحاد المصراتي', 'ittihad-lineup' ); ?></h1>
                        <p><?php esc_html_e( 'إدارة التشكيلات الرياضية', 'ittihad-lineup' ); ?></p>
                    </div>
                </div>

                <div class="ilb-stats-grid">
                    <div class="ilb-stat-card">
                        <span class="ilb-stat-icon">👥</span>
                        <span class="ilb-stat-number"><?php echo esc_html( $player_count ); ?></span>
                        <span class="ilb-stat-label"><?php esc_html_e( 'لاعب', 'ittihad-lineup' ); ?></span>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ilb_player' ) ); ?>" class="ilb-stat-link"><?php esc_html_e( 'عرض الكل', 'ittihad-lineup' ); ?></a>
                    </div>
                    <div class="ilb-stat-card">
                        <span class="ilb-stat-icon">🏟️</span>
                        <span class="ilb-stat-number"><?php echo esc_html( $team_count ); ?></span>
                        <span class="ilb-stat-label"><?php esc_html_e( 'فريق', 'ittihad-lineup' ); ?></span>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ilb_team' ) ); ?>" class="ilb-stat-link"><?php esc_html_e( 'عرض الكل', 'ittihad-lineup' ); ?></a>
                    </div>
                    <div class="ilb-stat-card">
                        <span class="ilb-stat-icon">⚽</span>
                        <span class="ilb-stat-number"><?php echo esc_html( $lineup_count ); ?></span>
                        <span class="ilb-stat-label"><?php esc_html_e( 'تشكيلة', 'ittihad-lineup' ); ?></span>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ilb_lineup' ) ); ?>" class="ilb-stat-link"><?php esc_html_e( 'عرض الكل', 'ittihad-lineup' ); ?></a>
                    </div>
                </div>

                <div class="ilb-quick-actions">
                    <h2><?php esc_html_e( 'إجراءات سريعة', 'ittihad-lineup' ); ?></h2>
                    <div class="ilb-actions-grid">
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ilb_player' ) ); ?>" class="ilb-action-btn">
                            <span>➕</span> <?php esc_html_e( 'إضافة لاعب', 'ittihad-lineup' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ilb_team' ) ); ?>" class="ilb-action-btn">
                            <span>➕</span> <?php esc_html_e( 'إضافة فريق', 'ittihad-lineup' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ilb_lineup' ) ); ?>" class="ilb-action-btn">
                            <span>➕</span> <?php esc_html_e( 'إنشاء تشكيلة', 'ittihad-lineup' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=ilb_builder' ) ); ?>" class="ilb-action-btn ilb-action-btn--primary">
                            <span>⚽</span> <?php esc_html_e( 'منشئ التشكيلة', 'ittihad-lineup' ); ?>
                        </a>
                    </div>
                </div>

                <div class="ilb-guide">
                    <h2><?php esc_html_e( 'دليل الاستخدام السريع', 'ittihad-lineup' ); ?></h2>
                    <ol class="ilb-guide-steps">
                        <li><?php esc_html_e( 'أضف لاعبين من قسم "اللاعبون" مع صورة ورقم ومركز لكل لاعب', 'ittihad-lineup' ); ?></li>
                        <li><?php esc_html_e( 'أنشئ فريقاً من قسم "الفرق" وحدد نوع الرياضة', 'ittihad-lineup' ); ?></li>
                        <li><?php esc_html_e( 'أنشئ تشكيلة جديدة وارتبطها بالفريق', 'ittihad-lineup' ); ?></li>
                        <li><?php esc_html_e( 'افتح "منشئ التشكيلة" واسحب اللاعبين إلى مواضعهم', 'ittihad-lineup' ); ?></li>
                        <li><?php esc_html_e( 'انسخ الشورت كود وضعه في أي صفحة: [ittihad_lineup id="X"]', 'ittihad-lineup' ); ?></li>
                    </ol>
                </div>

                <div class="ilb-credits">
                    <p><?php esc_html_e( 'طُوِّر بواسطة', 'ittihad-lineup' ); ?>: <strong>محمد بلعيد</strong> — <a href="https://github.com/x414i" target="_blank">github.com/x414i</a></p>
                </div>
            </div>
        </div>
        <?php
    }

    // ─── Builder Page ─────────────────────────────────────────────────────────

    public function render_builder_page(): void {
        $lineup_id   = absint( $_GET['lineup_id'] ?? 0 );
        $lineup_name = $lineup_id ? get_the_title( $lineup_id ) : '';
        $team_id     = $lineup_id ? get_post_meta( $lineup_id, '_ilb_team_id',    true ) : 0;
        $sport       = $lineup_id ? get_post_meta( $lineup_id, '_ilb_sport_type', true ) : 'football';
        $field_type  = $lineup_id ? get_post_meta( $lineup_id, '_ilb_field_type', true ) : 'default';
        $field_img   = $lineup_id ? get_post_meta( $lineup_id, '_ilb_field_image', true ) : '';

        $all_lineups = get_posts( [ 'post_type' => 'ilb_lineup', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        $all_teams   = get_posts( [ 'post_type' => 'ilb_team',   'posts_per_page' => -1, 'post_status' => 'publish' ] );
        ?>
        <div class="wrap ilb-wrap ilb-builder-wrap" dir="rtl">
            <div class="ilb-container pd-0 h-100">
                <div class="ilb-builder" id="ilb-builder"
                 data-lineup-id="<?php echo esc_attr( $lineup_id ); ?>"
                 data-sport-type="<?php echo esc_attr( $sport ); ?>"
                 data-field-type="<?php echo esc_attr( $field_type ); ?>"
                 data-field-img="<?php echo esc_url( $field_img ); ?>">

                <!-- Header -->
                <div class="ilb-builder__header">
                    <div class="ilb-builder__title">
                        <span class="ilb-builder__icon">⚽</span>
                        <div>
                            <h1><?php esc_html_e( 'منشئ التشكيلة', 'ittihad-lineup' ); ?></h1>
                            <p class="ilb-builder__subtitle"><?php esc_html_e( 'نادي الاتحاد المصراتي', 'ittihad-lineup' ); ?></p>
                        </div>
                    </div>

                    <div class="ilb-builder__controls">
                        <!-- Lineup selector -->
                        <div class="ilb-ctrl-group">
                            <label><?php esc_html_e( 'التشكيلة:', 'ittihad-lineup' ); ?></label>
                            <select id="ilb-lineup-select">
                                <option value=""><?php esc_html_e( 'اختر تشكيلة...', 'ittihad-lineup' ); ?></option>
                                <?php foreach ( $all_lineups as $l ) : ?>
                                    <option value="<?php echo esc_attr( $l->ID ); ?>" <?php selected( $lineup_id, $l->ID ); ?>>
                                        <?php echo esc_html( $l->post_title ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- New lineup -->
                        <button type="button" id="ilb-new-lineup-btn" class="ilb-btn ilb-btn--outline">
                            ➕ <?php esc_html_e( 'تشكيلة جديدة', 'ittihad-lineup' ); ?>
                        </button>

                        <!-- Save -->
                        <button type="button" id="ilb-save-btn" class="ilb-btn ilb-btn--primary" <?php echo ! $lineup_id ? 'disabled' : ''; ?>>
                            💾 <?php esc_html_e( 'حفظ التشكيلة', 'ittihad-lineup' ); ?>
                        </button>
                    </div>
                </div>

                <!-- Builder Body -->
                <div class="ilb-builder__body">

                    <!-- Sidebar: Settings + Players -->
                    <div class="ilb-sidebar">

                        <!-- Settings panel -->
                        <div class="ilb-panel ilb-panel--settings" id="ilb-settings-panel">
                            <div class="ilb-panel__head">
                                <span>⚙️</span> <?php esc_html_e( 'الإعدادات', 'ittihad-lineup' ); ?>
                                <button class="ilb-panel__toggle" data-target="ilb-settings-body">▲</button>
                            </div>
                            <div class="ilb-panel__body" id="ilb-settings-body">
                                <div class="ilb-field">
                                    <label><?php esc_html_e( 'اسم التشكيلة', 'ittihad-lineup' ); ?></label>
                                    <input type="text" id="ilb-lineup-name" value="<?php echo esc_attr( $lineup_name ); ?>"
                                           placeholder="<?php esc_attr_e( 'ادخل اسم التشكيلة', 'ittihad-lineup' ); ?>" />
                                </div>
                                <div class="ilb-field">
                                    <label><?php esc_html_e( 'الفريق', 'ittihad-lineup' ); ?></label>
                                    <select id="ilb-team-select">
                                        <option value=""><?php esc_html_e( 'اختر الفريق', 'ittihad-lineup' ); ?></option>
                                        <?php foreach ( $all_teams as $t ) : ?>
                                            <option value="<?php echo esc_attr( $t->ID ); ?>"
                                                    data-sport="<?php echo esc_attr( get_post_meta( $t->ID, '_ilb_sport_type', true ) ?: 'football' ); ?>"
                                                <?php selected( $team_id, $t->ID ); ?>>
                                                <?php echo esc_html( $t->post_title ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="ilb-field">
                                    <label><?php esc_html_e( 'نوع الرياضة', 'ittihad-lineup' ); ?></label>
                                    <select id="ilb-sport-select">
                                        <option value="football"   <?php selected( $sport, 'football' ); ?>><?php esc_html_e( 'كرة القدم', 'ittihad-lineup' ); ?></option>
                                        <option value="volleyball" <?php selected( $sport, 'volleyball' ); ?>><?php esc_html_e( 'الكرة الطائرة', 'ittihad-lineup' ); ?></option>
                                        <option value="basketball" <?php selected( $sport, 'basketball' ); ?>><?php esc_html_e( 'كرة السلة', 'ittihad-lineup' ); ?></option>
                                        <option value="handball"   <?php selected( $sport, 'handball' ); ?>><?php esc_html_e( 'كرة اليد', 'ittihad-lineup' ); ?></option>
                                        <option value="futsal"     <?php selected( $sport, 'futsal' ); ?>><?php esc_html_e( 'كرة القدم الصالات', 'ittihad-lineup' ); ?></option>
                                    </select>
                                </div>
                                <div class="ilb-field">
                                    <label><?php esc_html_e( 'خلفية الملعب', 'ittihad-lineup' ); ?></label>
                                    <select id="ilb-field-type-select">
                                        <option value="default" <?php selected( $field_type, 'default' ); ?>><?php esc_html_e( 'افتراضي', 'ittihad-lineup' ); ?></option>
                                        <option value="custom"  <?php selected( $field_type, 'custom' ); ?>><?php esc_html_e( 'صورة مخصصة', 'ittihad-lineup' ); ?></option>
                                    </select>
                                </div>
                                <div id="ilb-custom-field-upload" style="<?php echo $field_type === 'custom' ? '' : 'display:none;'; ?>">
                                    <button type="button" class="ilb-btn ilb-btn--sm" id="ilb-upload-field">
                                        🖼️ <?php esc_html_e( 'رفع صورة الملعب', 'ittihad-lineup' ); ?>
                                    </button>
                                    <input type="hidden" id="ilb-field-img-url" value="<?php echo esc_url( $field_img ); ?>" />
                                </div>
                            </div>
                        </div>

                        <!-- Players panel -->
                        <div class="ilb-panel ilb-panel--players">
                            <div class="ilb-panel__head">
                                <span>👥</span> <?php esc_html_e( 'اللاعبون', 'ittihad-lineup' ); ?>
                                <button class="ilb-panel__toggle" data-target="ilb-players-body">▲</button>
                            </div>
                            <div class="ilb-panel__body" id="ilb-players-body">
                                <div class="ilb-players-search">
                                    <input type="text" id="ilb-player-search" placeholder="<?php esc_attr_e( 'بحث عن لاعب...', 'ittihad-lineup' ); ?>" />
                                </div>
                                <div id="ilb-players-list" class="ilb-players-list">
                                    <p class="ilb-empty-msg"><?php esc_html_e( 'اختر الفريق لعرض اللاعبين', 'ittihad-lineup' ); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Field players panel -->
                        <div class="ilb-panel ilb-panel--field-players">
                            <div class="ilb-panel__head">
                                <span>📋</span> <?php esc_html_e( 'التشكيلة الحالية', 'ittihad-lineup' ); ?>
                                <span id="ilb-player-count" class="ilb-badge">0</span>
                                <button class="ilb-panel__toggle" data-target="ilb-active-body">▲</button>
                            </div>
                            <div class="ilb-panel__body" id="ilb-active-body">
                                <div id="ilb-active-players" class="ilb-active-players"></div>
                                <button type="button" id="ilb-clear-btn" class="ilb-btn ilb-btn--danger ilb-btn--sm">
                                    🗑️ <?php esc_html_e( 'مسح الكل', 'ittihad-lineup' ); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Shortcode -->
                        <?php if ( $lineup_id ) : ?>
                        <div class="ilb-panel ilb-panel--shortcode">
                            <div class="ilb-panel__head">
                                <span>🔗</span> <?php esc_html_e( 'الشورت كود', 'ittihad-lineup' ); ?>
                            </div>
                            <div class="ilb-panel__body">
                                <code id="ilb-shortcode-display" onclick="this.select()">[ittihad_lineup id="<?php echo esc_attr( $lineup_id ); ?>"]</code>
                                <button type="button" class="ilb-btn ilb-btn--sm ilb-copy-btn"
                                        data-clipboard="[ittihad_lineup id=&quot;<?php echo esc_attr( $lineup_id ); ?>&quot;]">
                                    📋 <?php esc_html_e( 'نسخ', 'ittihad-lineup' ); ?>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Field -->
                    <div class="ilb-field-area">
                        <div class="ilb-field-container" id="ilb-field-container">
                            <div class="ilb-pitch" id="ilb-pitch"
                                 data-sport="<?php echo esc_attr( $sport ); ?>"
                                 style="<?php echo $field_type === 'custom' && $field_img ? 'background-image:url(' . esc_url( $field_img ) . ');background-size:cover;' : ''; ?>">
                                <div class="ilb-pitch__overlay"></div>
                                <!-- Players dropped here dynamically -->
                                <div class="ilb-pitch__drop-hint" id="ilb-drop-hint">
                                    <span>👆</span>
                                    <p><?php esc_html_e( 'اسحب اللاعبين إلى هنا', 'ittihad-lineup' ); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Status bar -->
                        <div class="ilb-status-bar">
                            <span id="ilb-status-msg"></span>
                            <span class="ilb-credits-bar">by محمد بلعيد</span>
                        </div>
                    </div>
                </div>

                <!-- New Lineup Modal -->
                <div class="ilb-modal" id="ilb-new-lineup-modal" style="display:none;">
                    <div class="ilb-modal__backdrop"></div>
                    <div class="ilb-modal__dialog">
                        <h3><?php esc_html_e( 'إنشاء تشكيلة جديدة', 'ittihad-lineup' ); ?></h3>
                        <div class="ilb-field">
                            <label><?php esc_html_e( 'اسم التشكيلة', 'ittihad-lineup' ); ?></label>
                            <input type="text" id="ilb-new-lineup-name"
                                   placeholder="<?php esc_attr_e( 'اسم التشكيلة الجديدة', 'ittihad-lineup' ); ?>" />
                        </div>
                        <div class="ilb-modal__actions">
                            <button type="button" id="ilb-create-lineup-btn" class="ilb-btn ilb-btn--primary">
                                <?php esc_html_e( 'إنشاء', 'ittihad-lineup' ); ?>
                            </button>
                            <button type="button" id="ilb-cancel-modal-btn" class="ilb-btn ilb-btn--outline">
                                <?php esc_html_e( 'إلغاء', 'ittihad-lineup' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        </div>
        <?php
    }
}
