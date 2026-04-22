<?php
/**
 * Admin Page — Documentation & Plugin Guide (طريقة استخدام إضافة المباريات)
 *
 * @package UMMM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the admin menu items.
 */
function ummm_register_admin_menu() {
	// Main Documentation Page (sub-menu under المباريات).
	add_submenu_page(
		'edit.php?post_type=ummm_matches',
		__( 'طريقة الاستخدام', 'ummm' ),
		__( 'طريقة الاستخدام', 'ummm' ),
		'manage_options',
		'ummm-docs',
		'ummm_render_docs_page'
	);
}
add_action( 'admin_menu', 'ummm_register_admin_menu' );

/**
 * Enqueue admin styles (scoped to plugin pages).
 *
 * @param string $hook Current admin page hook.
 */
function ummm_admin_styles( $hook ) {
	// Only load on the docs page or match editing screens.
	$allowed_hooks = array(
		'post.php',
		'post-new.php',
		'edit.php',
		'ummm_matches_page_ummm-docs',
	);

	$is_docs     = ( strpos( $hook, 'ummm-docs' ) !== false );
	$is_cpt_page = (
		isset( $_GET['post_type'] ) && 'ummm_matches' === sanitize_key( $_GET['post_type'] )
	) || (
		isset( $_GET['post'] ) && 'ummm_matches' === get_post_type( (int) $_GET['post'] )
	);

	if ( ! $is_docs && ! $is_cpt_page ) {
		return;
	}

	wp_add_inline_style( 'wp-admin', ummm_get_admin_inline_css() );
}
add_action( 'admin_enqueue_scripts', 'ummm_admin_styles' );

/**
 * Returns the inline admin CSS string.
 *
 * @return string CSS.
 */
function ummm_get_admin_inline_css() {
	return '
	/* United Misrata Matches Manager — Admin Styles */
	:root {
		--ummm-a-green: #267d34;
		--ummm-a-green-light: #2e9e40;
		--ummm-a-bg: #f8f9fa;
		--ummm-a-card: #ffffff;
		--ummm-a-border: #e2e8f0;
		--ummm-a-text: #1d2327;
		--ummm-a-muted: #64748b;
	}
	.ummm-docs-wrap {
		max-width: 960px;
		margin: 24px 20px;
		font-family: "Segoe UI", Tahoma, Arial, sans-serif;
		direction: rtl;
		text-align: right;
		color: var(--ummm-a-text);
	}
	.ummm-docs-hero {
		background: linear-gradient(135deg, #0d1b0f 0%, #1a3a22 100%);
		border-radius: 12px;
		padding: 32px 36px;
		margin-bottom: 28px;
		display: flex;
		align-items: center;
		gap: 20px;
		border: 1px solid #2a5236;
	}
	.ummm-docs-hero__icon {
		font-size: 3rem;
		line-height: 1;
		flex-shrink: 0;
	}
	.ummm-docs-hero__text h1 {
		color: #fff !important;
		font-size: 1.5rem !important;
		margin: 0 0 6px !important;
		padding: 0 !important;
		border: none !important;
	}
	.ummm-docs-hero__text p {
		color: #9dc9a5;
		margin: 0;
		font-size: 0.92rem;
	}
	.ummm-docs-hero__badge {
		margin-right: auto;
		margin-left: 0;
	}
	.ummm-version-badge {
		background: var(--ummm-a-green);
		color: #fff;
		padding: 4px 14px;
		border-radius: 50px;
		font-size: 0.78rem;
		font-weight: 700;
		white-space: nowrap;
	}
	.ummm-docs-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
		margin-bottom: 20px;
	}
	@media (max-width: 768px) {
		.ummm-docs-grid { grid-template-columns: 1fr; }
	}
	.ummm-docs-card {
		background: var(--ummm-a-card);
		border: 1px solid var(--ummm-a-border);
		border-radius: 10px;
		padding: 22px 24px;
		transition: box-shadow 0.2s;
	}
	.ummm-docs-card:hover {
		box-shadow: 0 4px 16px rgba(0,0,0,0.08);
	}
	.ummm-docs-card--full {
		grid-column: 1 / -1;
	}
	.ummm-docs-card__heading {
		display: flex;
		align-items: center;
		gap: 10px;
		margin: 0 0 14px !important;
		padding-bottom: 12px !important;
		border-bottom: 2px solid var(--ummm-a-border) !important;
		color: var(--ummm-a-text) !important;
		font-size: 1rem !important;
	}
	.ummm-docs-card__heading .ummm-icon {
		font-size: 1.3rem;
	}
	.ummm-docs-card p {
		color: var(--ummm-a-muted);
		font-size: 0.88rem;
		line-height: 1.7;
		margin-top: 0;
	}
	.ummm-docs-card ol,
	.ummm-docs-card ul {
		padding-right: 20px;
		padding-left: 0;
		color: var(--ummm-a-muted);
		font-size: 0.88rem;
		line-height: 1.9;
	}
	.ummm-docs-card a {
		color: var(--ummm-a-green-light);
		text-decoration: none;
	}
	.ummm-docs-card a:hover {
		text-decoration: underline;
	}
	.ummm-status-table {
		width: 100%;
		border-collapse: collapse;
		margin-top: 6px;
		font-size: 0.85rem;
	}
	.ummm-status-table th {
		background: var(--ummm-a-bg);
		padding: 9px 14px;
		text-align: right;
		border-bottom: 2px solid var(--ummm-a-border);
		font-weight: 700;
		color: var(--ummm-a-text);
	}
	.ummm-status-table td {
		padding: 9px 14px;
		border-bottom: 1px solid var(--ummm-a-border);
		vertical-align: middle;
	}
	.ummm-status-table tr:last-child td { border-bottom: none; }
	.ummm-adm-badge {
		display: inline-block;
		padding: 3px 12px;
		border-radius: 50px;
		font-size: 0.75rem;
		font-weight: 600;
		color: #fff;
	}
	.ummm-adm-badge.upcoming  { background: #2563eb; }
	.ummm-adm-badge.live      { background: #d63638; }
	.ummm-adm-badge.finished  { background: var(--ummm-a-green); }
	.ummm-adm-badge.postponed { background: #6b7280; }
	.ummm-code-block {
		background: #1e1e3f;
		color: #82aaff;
		padding: 14px 18px;
		border-radius: 8px;
		font-family: "Courier New", monospace;
		font-size: 0.85rem;
		overflow-x: auto;
		margin: 10px 0;
		white-space: pre;
		line-height: 1.7;
	}
	.ummm-tip-box {
		background: #f0fdf4;
		border-right: 4px solid var(--ummm-a-green);
		border-left: none;
		border-radius: 6px;
		padding: 12px 16px;
		margin-top: 12px;
		font-size: 0.85rem;
		color: #166534;
	}
	.ummm-tip-box strong { display: block; margin-bottom: 4px; }
	.ummm-tax-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
	.ummm-tax-chip {
		background: var(--ummm-a-bg);
		border: 1px solid var(--ummm-a-border);
		padding: 4px 12px;
		border-radius: 50px;
		font-size: 0.8rem;
		color: var(--ummm-a-muted);
	}
	.ummm-tax-chip strong { color: var(--ummm-a-text); font-weight: 600; }
	';
}

/**
 * Render the documentation admin page.
 */
function ummm_render_docs_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'ليس لديك صلاحية الوصول إلى هذه الصفحة.', 'ummm' ) );
	}
	?>
	<div class="ummm-docs-wrap">

		<!-- ── Hero ── -->
		<div class="ummm-docs-hero">
			<div class="ummm-docs-hero__icon">⚽</div>
			<div class="ummm-docs-hero__text">
				<h1><?php esc_html_e( 'طريقة استخدام إضافة المباريات', 'ummm' ); ?></h1>
				<p><?php esc_html_e( 'نادي الاتحاد المصراتي — دليل استخدام النظام الاحترافي لإدارة المباريات', 'ummm' ); ?></p>
			</div>
			<div class="ummm-docs-hero__badge">
				<span class="ummm-version-badge"><?php echo esc_html( 'v' . UMMM_VERSION ); ?></span>
			</div>
		</div>

		<div class="ummm-docs-grid">

			<!-- ── 1. How to Add a Match ── -->
			<div class="ummm-docs-card">
				<h2 class="ummm-docs-card__heading">
					<span class="ummm-icon">📋</span>
					<?php esc_html_e( 'كيفية إضافة مباراة', 'ummm' ); ?>
				</h2>
				<ol>
					<li><?php esc_html_e( 'من القائمة الجانبية، انقر على "المباريات"، ثم "إضافة مباراة".', 'ummm' ); ?></li>
					<li><?php esc_html_e( 'أدخل عنوان المباراة (مثال: نادي الاتحاد ضد النصر).', 'ummm' ); ?></li>
					<li><?php esc_html_e( 'في صندوق "تفاصيل المباراة" أدخل: الفريق المستضيف، الفريق الضيف، التاريخ، الوقت، الملعب، والبطولة.', 'ummm' ); ?></li>
					<li><?php esc_html_e( 'في صندوق "حالة المباراة" (الجانب الأيمن) اختر الحالة المناسبة.', 'ummm' ); ?></li>
					<li><?php esc_html_e( 'من أقسام الفئات (الفرق / الرياضات / البطولات) اختر التصنيفات الصحيحة.', 'ummm' ); ?></li>
					<li><?php esc_html_e( 'انقر "نشر" أو "تحديث" لحفظ المباراة.', 'ummm' ); ?></li>
				</ol>
				<div class="ummm-tip-box">
					<strong>💡 نصيحة:</strong>
					<?php esc_html_e( 'بعد انتهاء المباراة، ارجع وأدخل النتيجة النهائية ونتيجة الشوط الأول ثم غيّر الحالة إلى "انتهت".', 'ummm' ); ?>
				</div>
			</div>

			<!-- ── 2. Match Statuses ── -->
			<div class="ummm-docs-card">
				<h2 class="ummm-docs-card__heading">
					<span class="ummm-icon">🚦</span>
					<?php esc_html_e( 'حالات المباراة', 'ummm' ); ?>
				</h2>
				<table class="ummm-status-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'الحالة', 'ummm' ); ?></th>
							<th><?php esc_html_e( 'الشرح', 'ummm' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><span class="ummm-adm-badge upcoming"><?php esc_html_e( 'قادمة', 'ummm' ); ?></span></td>
							<td><?php esc_html_e( 'مباراة مجدولة لم تبدأ بعد.', 'ummm' ); ?></td>
						</tr>
						<tr>
							<td><span class="ummm-adm-badge live"><?php esc_html_e( 'مباشرة', 'ummm' ); ?></span></td>
							<td><?php esc_html_e( 'المباراة جارية الآن. تظهر بمنبه أحمر نابض.', 'ummm' ); ?></td>
						</tr>
						<tr>
							<td><span class="ummm-adm-badge finished"><?php esc_html_e( 'انتهت', 'ummm' ); ?></span></td>
							<td><?php esc_html_e( 'المباراة انتهت وتم إدخال النتيجة.', 'ummm' ); ?></td>
						</tr>
						<tr>
							<td><span class="ummm-adm-badge postponed"><?php esc_html_e( 'مؤجلة', 'ummm' ); ?></span></td>
							<td><?php esc_html_e( 'المباراة أُجّلت ولم يُحدد موعد جديد بعد.', 'ummm' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- ── 3. Taxonomies ── -->
			<div class="ummm-docs-card ummm-docs-card--full">
				<h2 class="ummm-docs-card__heading">
					<span class="ummm-icon">🏷️</span>
					<?php esc_html_e( 'أقسام التصنيف', 'ummm' ); ?>
				</h2>
				<p><?php esc_html_e( 'تتيح الأقسام تصنيف المباريات وتصفيتها بسهولة في الواجهة الأمامية وفي لوحة التحكم.', 'ummm' ); ?></p>

				<p><strong><?php esc_html_e( 'الفرق:', 'ummm' ); ?></strong></p>
				<div class="ummm-tax-chips">
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'الفريق الأول', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'الأواسط', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'الأشبال', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'البراعم', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'الناشئين', 'ummm' ); ?></strong></span>
				</div>

				<p style="margin-top:16px;"><strong><?php esc_html_e( 'الرياضات:', 'ummm' ); ?></strong></p>
				<div class="ummm-tax-chips">
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'كرة القدم', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'كرة الطائرة', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'الدراجات الهوائية', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'القوة البدنية', 'ummm' ); ?></strong></span>
				</div>

				<p style="margin-top:16px;"><strong><?php esc_html_e( 'البطولات:', 'ummm' ); ?></strong></p>
				<div class="ummm-tax-chips">
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'الدوري المحلي', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'الكأس', 'ummm' ); ?></strong></span>
					<span class="ummm-tax-chip"><strong><?php esc_html_e( 'مباريات ودية', 'ummm' ); ?></strong></span>
				</div>
				<div class="ummm-tip-box" style="margin-top:16px;">
					<strong>💡 نصيحة:</strong>
					<?php esc_html_e( 'يمكنك إضافة فئات جديدة من صفحات الفرق، الرياضات، أو البطولات الموجودة في قائمة المباريات.', 'ummm' ); ?>
				</div>
			</div>

			<!-- ── 4. Shortcode Reference ── -->
			<div class="ummm-docs-card ummm-docs-card--full">
				<h2 class="ummm-docs-card__heading">
					<span class="ummm-icon">⚙️</span>
					<?php esc_html_e( 'الشيفرة القصيرة (Shortcode)', 'ummm' ); ?>
				</h2>
				<p><?php esc_html_e( 'استخدم الشيفرة القصيرة التالية في أي صفحة أو منشور لعرض المباريات:', 'ummm' ); ?></p>

				<div class="ummm-code-block">[united_matches view="cards" team="all" sport="all" status="all"]</div>

				<table class="ummm-status-table" style="margin-top:14px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'المعامل', 'ummm' ); ?></th>
							<th><?php esc_html_e( 'القيم المتاحة', 'ummm' ); ?></th>
							<th><?php esc_html_e( 'الافتراضي', 'ummm' ); ?></th>
							<th><?php esc_html_e( 'الوصف', 'ummm' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>view</code></td>
							<td><code>cards | table | timeline | tabs</code></td>
							<td><code>cards</code></td>
							<td><?php esc_html_e( 'طريقة عرض المباريات', 'ummm' ); ?></td>
						</tr>
						<tr>
							<td><code>status</code></td>
							<td><code>upcoming | live | finished | postponed | all</code></td>
							<td><code>all</code></td>
							<td><?php esc_html_e( 'تصفية حسب حالة المباراة', 'ummm' ); ?></td>
						</tr>
						<tr>
							<td><code>team</code></td>
							<td><?php esc_html_e( 'اسم فئة الفريق (slug)', 'ummm' ); ?></td>
							<td><code>all</code></td>
							<td><?php esc_html_e( 'تصفية حسب الفريق', 'ummm' ); ?></td>
						</tr>
						<tr>
							<td><code>sport</code></td>
							<td><?php esc_html_e( 'اسم نوع الرياضة (slug)', 'ummm' ); ?></td>
							<td><code>all</code></td>
							<td><?php esc_html_e( 'تصفية حسب الرياضة', 'ummm' ); ?></td>
						</tr>
						<tr>
							<td><code>number</code></td>
							<td><?php esc_html_e( 'عدد صحيح', 'ummm' ); ?></td>
							<td><code>-1</code></td>
							<td><?php esc_html_e( 'عدد المباريات المعروضة (-1 = الكل)', 'ummm' ); ?></td>
						</tr>
						<tr>
							<td><code>order</code></td>
							<td><code>ASC | DESC</code></td>
							<td><code>ASC</code></td>
							<td><?php esc_html_e( 'ترتيب تصاعدي أو تنازلي حسب التاريخ', 'ummm' ); ?></td>
						</tr>
					</tbody>
				</table>

				<p style="margin-top:16px;"><strong><?php esc_html_e( 'أمثلة:', 'ummm' ); ?></strong></p>
				<div class="ummm-code-block"><?php
				echo esc_html( '[united_matches view="cards" status="upcoming"]' ) . "\n";
				echo esc_html( '[united_matches view="tabs" team="الفريق-الأول"]' ) . "\n";
				echo esc_html( '[united_matches view="table" status="finished" order="DESC" number="10"]' ) . "\n";
				echo esc_html( '[united_matches view="timeline" sport="كرة-القدم"]' );
				?></div>
			</div>

			<!-- ── 5. Divi Integration ── -->
			<div class="ummm-docs-card ummm-docs-card--full">
				<h2 class="ummm-docs-card__heading">
					<span class="ummm-icon">🎨</span>
					<?php esc_html_e( 'الاستخدام مع Divi Builder', 'ummm' ); ?>
				</h2>
				<ol>
					<li><?php esc_html_e( 'افتح الصفحة بمحرر Divi واختر "إضافة قسم" أو استخدم قسم موجود.', 'ummm' ); ?></li>
					<li><?php esc_html_e( 'أضف وحدة "Code" (رمز) أو وحدة "Text" (نص) داخل العمود.', 'ummm' ); ?></li>
					<li><?php esc_html_e( 'الصق الشيفرة القصيرة داخل الوحدة، مثلاً:', 'ummm' ); ?></li>
				</ol>
				<div class="ummm-code-block">[united_matches view="cards" status="upcoming"]</div>
				<ol start="4">
					<li><?php esc_html_e( 'احفظ الصفحة واضغط "عرض" للتحقق من ظهور المباريات.', 'ummm' ); ?></li>
					<li><?php esc_html_e( 'يمكنك تغيير اللون الخلفي للقسم من إعدادات Divi إذا أردت خلفية مختلفة.', 'ummm' ); ?></li>
				</ol>
				<div class="ummm-tip-box">
					<strong>💡 نصيحة التوافق:</strong>
					<?php esc_html_e( 'الإضافة لا تعتمد على أي مكتبة خارجية وتعمل بشكل كامل داخل Divi دون أي تعارض. جميع الأنماط مُعزولة تحت الفئة .ummm-wrapper.', 'ummm' ); ?>
				</div>
			</div>

		</div><!-- .ummm-docs-grid -->

		<p style="color:#aaa; font-size:0.78rem; text-align:center; margin-top: 10px;">
			<?php
			printf(
				/* translators: %s: version number */
				esc_html__( 'United Misrata Matches Manager v%s — نادي الاتحاد المصراتي', 'ummm' ),
				esc_html( UMMM_VERSION )
			);
			?>
		</p>

	</div><!-- .ummm-docs-wrap -->
	<?php
}
