<?php
/**
 * Template for /walkthrough/: a public tour of the National AI Conversation platform (see inc/walkthrough.php). Once
 * the editable page has been created (Pages → Theme pages), the address shows that instead, built from the same
 * screens (patterns/walkthrough.php).
 *
 * Signed-in screens are screenshots of the local demo, so the tour works for anyone, on any device, without an
 * account; the two public pages link to the live site. Every screen shows made-up demo data.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aiad_demo_url = function_exists( 'aiad_walkthrough_demo_url' ) ? aiad_walkthrough_demo_url() : '';
$aiad_nc_url   = function_exists( 'aiad_national_conversation_page_url' ) ? aiad_national_conversation_page_url() : home_url( '/national-conversation/' );

$aiad_parts = aiad_walkthrough_parts( $aiad_nc_url ); // inc/walkthrough.php, shared with the editable page's blocks.

get_header();
?>

<main id="main" role="main" class="wt">

	<section class="wt-hero" aria-labelledby="wt-title">
		<div class="container">
			<p class="wt-eyebrow"><?php esc_html_e( 'National AI Conversation 2027', 'ai-awareness-day' ); ?></p>
			<h1 class="wt-title" id="wt-title"><?php esc_html_e( 'Platform walkthrough', 'ai-awareness-day' ); ?></h1>
			<p class="wt-lead"><?php esc_html_e( 'A tour of the platform schools will use from 1 January 2027, from signing up to the programme team\'s dashboard.', 'ai-awareness-day' ); ?></p>
			<p class="wt-demo-note"><?php esc_html_e( 'Every screen shows made-up demo data: Willowbrook Primary School and the schools it debates.', 'ai-awareness-day' ); ?></p>
			<p class="wt-actions">
				<a class="wt-btn wt-btn--primary" href="<?php echo esc_url( $aiad_nc_url ); ?>"><?php esc_html_e( 'What is the National AI Conversation?', 'ai-awareness-day' ); ?></a>
				<?php if ( $aiad_demo_url ) : ?>
					<a class="wt-btn wt-btn--ghost" href="<?php echo esc_url( $aiad_demo_url ); ?>"><?php esc_html_e( 'Open the interactive demo', 'ai-awareness-day' ); ?></a>
				<?php endif; ?>
			</p>
			<nav class="wt-parts" aria-label="<?php esc_attr_e( 'Parts of the walkthrough', 'ai-awareness-day' ); ?>">
				<ol>
					<?php foreach ( $aiad_parts as $aiad_i => $aiad_part ) : ?>
						<li><a href="#wt-<?php echo esc_attr( $aiad_part['id'] ); ?>"><?php echo esc_html( ( $aiad_i + 1 ) . '. ' . $aiad_part['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ol>
			</nav>
		</div>
	</section>

	<?php
	$aiad_n = 0;
	foreach ( $aiad_parts as $aiad_i => $aiad_part ) :
		?>
	<section class="wt-part<?php echo 1 === $aiad_i % 2 ? ' wt-part--card' : ''; ?>" id="wt-<?php echo esc_attr( $aiad_part['id'] ); ?>" aria-labelledby="wt-<?php echo esc_attr( $aiad_part['id'] ); ?>-title">
		<div class="container">
			<p class="wt-part__number"><?php echo esc_html( sprintf( /* translators: %d: part number */ __( 'Part %d', 'ai-awareness-day' ), $aiad_i + 1 ) ); ?></p>
			<h2 id="wt-<?php echo esc_attr( $aiad_part['id'] ); ?>-title"><?php echo esc_html( $aiad_part['title'] ); ?></h2>
			<p class="wt-part__intro"><?php echo esc_html( $aiad_part['intro'] ); ?></p>
			<ol class="wt-shots">
				<?php
				foreach ( $aiad_part['shots'] as $aiad_shot ) :
					++$aiad_n;
					list( $aiad_file, $aiad_w, $aiad_h, $aiad_title, $aiad_desc, $aiad_live ) = $aiad_shot;
					$aiad_src   = AIAD_URI . '/assets/images/walkthrough/' . $aiad_file . '.jpg';
					$aiad_phone = $aiad_w < $aiad_h * 0.5;
					?>
					<li class="wt-shot<?php echo $aiad_phone ? ' wt-shot--phone' : ''; ?>">
						<a class="wt-shot__frame" href="<?php echo esc_url( $aiad_src ); ?>" target="_blank" rel="noopener">
							<img src="<?php echo esc_url( $aiad_src ); ?>" width="<?php echo (int) $aiad_w; ?>" height="<?php echo (int) $aiad_h; ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s: screen name */ __( 'Screenshot: %s', 'ai-awareness-day' ), $aiad_title ) ); ?>" loading="lazy" decoding="async">
							<span class="wt-shot__zoom"><?php esc_html_e( 'View full screen', 'ai-awareness-day' ); ?></span>
						</a>
						<div class="wt-shot__body">
							<p class="wt-shot__step"><?php echo esc_html( sprintf( /* translators: %d: screen number */ __( 'Screen %d', 'ai-awareness-day' ), $aiad_n ) ); ?></p>
							<h3><?php echo esc_html( $aiad_title ); ?></h3>
							<p><?php echo esc_html( $aiad_desc ); ?></p>
							<?php if ( $aiad_live ) : ?>
								<p class="wt-shot__live"><a href="<?php echo esc_url( $aiad_live ); ?>"><?php esc_html_e( 'Open the live page', 'ai-awareness-day' ); ?></a></p>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
	<?php endforeach; ?>

	<section class="wt-cta" aria-labelledby="wt-cta">
		<div class="container">
			<h2 id="wt-cta"><?php esc_html_e( 'Schools can register from 1 January 2027', 'ai-awareness-day' ); ?></h2>
			<p><?php esc_html_e( 'Find out more about the National AI Conversation, or get in touch.', 'ai-awareness-day' ); ?></p>
			<p class="wt-actions">
				<a class="wt-btn wt-btn--primary" href="<?php echo esc_url( $aiad_nc_url ); ?>"><?php esc_html_e( 'The National AI Conversation', 'ai-awareness-day' ); ?></a>
				<a class="wt-btn wt-btn--ghost" href="<?php echo esc_url( home_url( '/#contact' ) ); ?>"><?php esc_html_e( 'Get in touch', 'ai-awareness-day' ); ?></a>
			</p>
		</div>
	</section>

</main>

<?php get_footer(); ?>
