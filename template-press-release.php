<?php
/**
 * Template Name: Press Release
 * Template Post Type: page
 *
 * Downloadable press release file (PDF) for AI Awareness Day.
 *
 * @package AI_Awareness_Day
 */

get_header();

$file_id = absint( get_theme_mod( 'aiad_press_release_file', 0 ) );
$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
$mime     = $file_id ? (string) get_post_mime_type( $file_id ) : '';
$is_image = $file_id && $mime && 0 === strpos( $mime, 'image/' );
$preview  = $file_id && $is_image ? wp_get_attachment_image_url( $file_id, 'large' ) : '';
$filename = $file_id ? basename( get_attached_file( $file_id ) ?: $file_url ) : '';

$label       = __( 'Press release', 'ai-awareness-day' );
$description = __( 'Official text for newsletters, websites, and local media. Download and adapt for your school or trust.', 'ai-awareness-day' );
$btn_label   = __( 'Download', 'ai-awareness-day' );
?>

<main id="main" role="main" class="assets-pack-page press-release-page">
	<div class="container">

		<div class="assets-pack__header fade-up">
			<span class="section-label"><?php esc_html_e( 'Media', 'ai-awareness-day' ); ?></span>
			<h1 class="section-title"><?php echo esc_html( get_the_title() ?: __( 'Press Release', 'ai-awareness-day' ) ); ?></h1>
			<?php if ( have_posts() ) : the_post(); ?>
				<?php if ( get_the_content() ) : ?>
					<div class="assets-pack__intro section-desc"><?php the_content(); ?></div>
				<?php else : ?>
					<p class="section-desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="assets-pack__grid">
			<?php if ( $file_id && $file_url ) : ?>
			<div class="assets-pack__card fade-up">
				<div class="assets-pack__preview<?php echo $is_image ? '' : ' assets-pack__preview--doc'; ?>">
					<?php if ( $is_image && $preview ) : ?>
						<img src="<?php echo esc_url( $preview ); ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy" />
					<?php else : ?>
						<span class="assets-pack__doc-badge" aria-hidden="true"><?php echo esc_html( strtoupper( pathinfo( $filename, PATHINFO_EXTENSION ) ?: 'PDF' ) ); ?></span>
						<span class="assets-pack__doc-label"><?php esc_html_e( 'Press release file', 'ai-awareness-day' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="assets-pack__info">
					<h2 class="assets-pack__card-title"><?php echo esc_html( $label ); ?></h2>
					<p class="assets-pack__card-desc section-desc"><?php echo esc_html( $description ); ?></p>
					<a href="<?php echo esc_url( $file_url ); ?>"
					   download="<?php echo esc_attr( $filename ); ?>"
					   class="btn assets-pack__download-btn">
						<?php echo esc_html( $btn_label ); ?>
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
					</a>
				</div>
			</div>
			<?php else : ?>
			<p class="assets-pack__empty section-desc">
				<?php esc_html_e( 'The press release file is being prepared — check back soon.', 'ai-awareness-day' ); ?>
			</p>
			<?php endif; ?>
		</div>

	</div>
</main>

<?php
get_footer();
