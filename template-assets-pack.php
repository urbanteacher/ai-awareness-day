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

// The downloads (inc/admin-assets-pack.php), shared with the editable page's blocks (patterns/assets-pack.php).
$assets = aiad_assets_pack_downloads();
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
                    <p class="section-desc"><?php esc_html_e( 'Download AiAd27 lockups, strand icons, posters and chamfer shapes. Deep marks on cream; bright marks on ink. Never put bright type on cream.', 'ai-awareness-day' ); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="assets-pack__grid">
            <?php
            $review_pages = aiad_assets_pack_review_pages();
            foreach ( $review_pages as $page ) :
                ?>
            <div class="assets-pack__card assets-pack__card--page fade-up">
                <div class="assets-pack__preview assets-pack__preview--page">
                    <img src="<?php echo esc_url( $page['preview'] ); ?>"
                         alt=""
                         loading="lazy" />
                    <span class="assets-pack__doc-badge"><?php echo esc_html( $page['badge'] ); ?></span>
                </div>
                <div class="assets-pack__info">
                    <h2 class="assets-pack__card-title"><?php echo esc_html( $page['label'] ); ?></h2>
                    <p class="assets-pack__card-desc section-desc"><?php echo esc_html( $page['description'] ); ?></p>
                    <a href="<?php echo esc_url( $page['url'] ); ?>"
                       class="btn assets-pack__download-btn">
                        <?php echo esc_html( $page['btn_label'] ); ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>

            <?php foreach ( $assets as $asset ) :
                $asset_id = absint( $asset['id'] ?? 0 );
                $img_url  = $asset_id ? wp_get_attachment_url( $asset_id ) : ( $asset['url'] ?? '' );
                $img_full = $asset_id ? wp_get_attachment_image_url( $asset_id, 'large' ) : $img_url;
                if ( ! $img_url ) continue;
                $filename = basename( $asset_id ? ( get_attached_file( $asset_id ) ?: $img_url ) : $img_url );
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

            <?php if ( ! array_filter( $assets, static fn( array $asset ): bool => ! empty( $asset['id'] ) || ! empty( $asset['url'] ) ) ) : ?>
            <p class="assets-pack__empty section-desc">
                <?php esc_html_e( 'Assets are being prepared — check back soon.', 'ai-awareness-day' ); ?>
            </p>
            <?php endif; ?>
        </div>

    </div>

    <?php
    /* Local demo only: a quiet way back to the client walkthrough. It shows only when the site's own address is
       localhost and the walkthrough file is in the theme, so it never appears on the live site. */
    $aiad_demo_host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
    if ( in_array( $aiad_demo_host, array( 'localhost', '127.0.0.1' ), true ) && is_readable( get_template_directory() . '/demo-walkthrough.html' ) ) :
        ?>
    <p class="assets-pack__demo"><a href="<?php echo esc_url( AIAD_URI . '/demo-start.php' ); ?>" rel="nofollow">Demo</a></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
