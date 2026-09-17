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
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup.svg',
        'label'       => __( 'Campaign lockup (ink)', 'ai-awareness-day' ),
        'description' => __( 'Use on cream or white ground. Includes Keep Humans in the Loop.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download lockup', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-reverse.svg',
        'label'       => __( 'Campaign lockup (reverse)', 'ai-awareness-day' ),
        'description' => __( 'Use on ink or dark grounds only — cream type reverse.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download reverse lockup', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-safe.svg',
        'label'       => __( 'Safe lockup (deep)', 'ai-awareness-day' ),
        'description' => __( 'Deep Safe mark for cream grounds. Never use bright as body text on cream.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Safe lockup', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-safe-bright.svg',
        'label'       => __( 'Safe lockup (bright)', 'ai-awareness-day' ),
        'description' => __( 'Bright Safe mark for ink grounds only.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Safe bright', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-smart.svg',
        'label'       => __( 'Smart lockup (deep)', 'ai-awareness-day' ),
        'description' => __( 'Deep Smart mark for cream grounds.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Smart lockup', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-smart-bright.svg',
        'label'       => __( 'Smart lockup (bright)', 'ai-awareness-day' ),
        'description' => __( 'Bright Smart mark for ink grounds only.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Smart bright', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-creative.svg',
        'label'       => __( 'Creative lockup (deep)', 'ai-awareness-day' ),
        'description' => __( 'Deep Creative mark for cream grounds.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Creative lockup', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-creative-bright.svg',
        'label'       => __( 'Creative lockup (bright)', 'ai-awareness-day' ),
        'description' => __( 'Bright Creative mark for ink grounds only.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Creative bright', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-responsible.svg',
        'label'       => __( 'Responsible lockup (deep)', 'ai-awareness-day' ),
        'description' => __( 'Deep Responsible mark for cream grounds.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Responsible lockup', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-responsible-bright.svg',
        'label'       => __( 'Responsible lockup (bright)', 'ai-awareness-day' ),
        'description' => __( 'Bright Responsible mark for ink grounds only.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Responsible bright', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-future.svg',
        'label'       => __( 'Future lockup (deep)', 'ai-awareness-day' ),
        'description' => __( 'Deep Future mark for cream grounds.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Future lockup', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-future-bright.svg',
        'label'       => __( 'Future lockup (bright)', 'ai-awareness-day' ),
        'description' => __( 'Bright Future mark for ink grounds only.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Future bright', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/icon-safe.svg',
        'label'       => __( 'Safe icon', 'ai-awareness-day' ),
        'description' => __( 'Strand mark for Safe. Pair with the Safe bright ground or deep ink.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Safe icon', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/icon-smart.svg',
        'label'       => __( 'Smart icon', 'ai-awareness-day' ),
        'description' => __( 'Strand mark for Smart.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Smart icon', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/icon-creative.svg',
        'label'       => __( 'Creative icon', 'ai-awareness-day' ),
        'description' => __( 'Strand mark for Creative.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Creative icon', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/icon-responsible.svg',
        'label'       => __( 'Responsible icon', 'ai-awareness-day' ),
        'description' => __( 'Strand mark for Responsible.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Responsible icon', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/icon-future.svg',
        'label'       => __( 'Future icon', 'ai-awareness-day' ),
        'description' => __( 'Strand mark for Future.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Future icon', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/shape-chamfer-panel.svg',
        'label'       => __( 'Chamfer panel shape', 'ai-awareness-day' ),
        'description' => __( 'Campaign chamfer signature for large panels. Use sparingly.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download chamfer panel', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/shape-chamfer-tile.svg',
        'label'       => __( 'Chamfer tile shape', 'ai-awareness-day' ),
        'description' => __( 'Campaign chamfer signature for ballot tiles and small marks.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download chamfer tile', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/poster-safe.svg',
        'label'       => __( 'Safe poster graphic', 'ai-awareness-day' ),
        'description' => __( 'A bright Safe ground and the shield mark for conversations about privacy, trust and sharing.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Safe graphic', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/poster-smart.svg',
        'label'       => __( 'Smart poster graphic', 'ai-awareness-day' ),
        'description' => __( 'The Smart strand graphic for decisions about AI acting on your behalf.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Smart graphic', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/poster-creative.svg',
        'label'       => __( 'Creative poster graphic', 'ai-awareness-day' ),
        'description' => __( 'The Creative strand graphic for attribution, authorship and making.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Creative graphic', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/poster-responsible.svg',
        'label'       => __( 'Responsible poster graphic', 'ai-awareness-day' ),
        'description' => __( 'The Responsible strand graphic for human judgement and consequential decisions.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Responsible graphic', 'ai-awareness-day' ),
    ),
    array(
        'url'         => AIAD_URI . '/assets/brand/aiad27/poster-future.svg',
        'label'       => __( 'Future poster graphic', 'ai-awareness-day' ),
        'description' => __( 'The Future strand graphic for skills worth keeping human.', 'ai-awareness-day' ),
        'btn_label'   => __( 'Download Future graphic', 'ai-awareness-day' ),
    ),
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
                    <p class="section-desc"><?php esc_html_e( 'Download AiAd27 lockups, strand icons, posters and chamfer shapes. Deep marks on cream; bright marks on ink. Never put bright type on cream.', 'ai-awareness-day' ); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="assets-pack__grid">
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

        <p class="assets-pack__review-links">
            <a href="<?php echo esc_url( AIAD_URI . '/assets/aiad27-review/style.html' ); ?>"><?php esc_html_e( 'Style guide', 'ai-awareness-day' ); ?></a>
            <span aria-hidden="true">·</span>
            <a href="<?php echo esc_url( AIAD_URI . '/assets/aiad27-review/preview.html' ); ?>"><?php esc_html_e( 'Student slides', 'ai-awareness-day' ); ?></a>
        </p>

    </div>
</main>

<?php get_footer(); ?>
