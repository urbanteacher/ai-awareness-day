<?php
/**
 * Template Name: Assets Pack
 * Template Post Type: page
 *
 * Downloadable logo and email banners for schools participating in AI Awareness Day.
 *
 * @package AI_Awareness_Day
 */

get_header();

$logo_id        = absint( get_theme_mod( 'aiad_asset_logo', 0 ) );
$banner_part_id = absint( get_theme_mod( 'aiad_asset_banner_participating', 0 ) );
$banner_done_id = absint( get_theme_mod( 'aiad_asset_banner_participated', 0 ) );

$assets = array(
    array(
        'id'          => $logo_id,
        'label'       => __( 'Logo', 'ai-awareness-day' ),
        'description' => __( 'Use in documents, presentations, and school communications.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Logo', 'ai-awareness-day' ),
    ),
    array(
        'id'          => $banner_part_id,
        'label'       => __( 'Email Banner — Participating', 'ai-awareness-day' ),
        'description' => __( 'Add to your email signature to let others know your school is taking part.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Banner', 'ai-awareness-day' ),
    ),
    array(
        'id'          => $banner_done_id,
        'label'       => __( 'Email Banner — Participated', 'ai-awareness-day' ),
        'description' => __( 'Swap into your email signature after the event to celebrate taking part.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Banner', 'ai-awareness-day' ),
    ),
);
?>

<main id="main" role="main" class="assets-pack-page">
    <div class="container">

        <div class="assets-pack__header fade-up">
            <span class="section-label"><?php esc_html_e( 'Free Downloads', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title"><?php echo esc_html( get_the_title() ?: __( 'Assets Pack', 'ai-awareness-day' ) ); ?></h1>
            <?php if ( have_posts() ) : the_post(); ?>
                <?php if ( get_the_content() ) : ?>
                    <div class="assets-pack__intro section-desc"><?php the_content(); ?></div>
                <?php else : ?>
                    <p class="section-desc"><?php esc_html_e( 'Download the logo and email banners below to show your school\'s involvement in AI Awareness Day.', 'ai-awareness-day' ); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="assets-pack__grid">
            <?php foreach ( $assets as $asset ) :
                $img_url  = $asset['id'] ? wp_get_attachment_url( $asset['id'] ) : '';
                $img_full = $asset['id'] ? wp_get_attachment_image_url( $asset['id'], 'large' ) : '';
                if ( ! $img_url ) continue;
                $filename = basename( get_attached_file( $asset['id'] ) ?: $img_url );
            ?>
            <div class="assets-pack__card fade-up">
                <div class="assets-pack__preview">
                    <img src="<?php echo esc_url( $img_full ?: $img_url ); ?>"
                         alt="<?php echo esc_attr( $asset['label'] ); ?>"
                         loading="lazy" />
                </div>
                <div class="assets-pack__info">
                    <h2 class="assets-pack__card-title"><?php echo esc_html( $asset['label'] ); ?></h2>
                    <p class="assets-pack__card-desc section-desc"><?php echo esc_html( $asset['description'] ); ?></p>
                    <a href="<?php echo esc_url( $img_url ); ?>"
                       download="<?php echo esc_attr( $filename ); ?>"
                       class="btn assets-pack__download-btn">
                        <?php echo esc_html( $asset['btn_label'] ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if ( ! array_filter( array_column( $assets, 'id' ) ) ) : ?>
            <p class="assets-pack__empty section-desc">
                <?php esc_html_e( 'Assets are being prepared — check back soon.', 'ai-awareness-day' ); ?>
            </p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php get_footer(); ?>
