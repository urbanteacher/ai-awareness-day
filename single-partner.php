<?php
/**
 * Single template for a Partner.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="single-partner">
    <section class="section section--top">
        <div class="container">
            <?php while ( have_posts() ) : the_post();
                $url = get_post_meta( get_the_ID(), '_partner_url', true );
                $intro = get_post_meta( get_the_ID(), '_partner_profile_intro', true );
                $links = get_post_meta( get_the_ID(), '_partner_links', true );
                $links = is_array( $links ) ? $links : array();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'partner-bio' ); ?>>
                    <h1 class="screen-reader-text"><?php echo esc_html( get_the_title() ); ?></h1>

                    <div class="partner-bio__card fade-up">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="partner-bio__logo">
                                <?php if ( $url ) : ?>
                                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php the_post_thumbnail( 'medium', array( 'class' => 'partner-bio__logo-img' ) ); ?>
                                    </a>
                                <?php else : ?>
                                    <?php the_post_thumbnail( 'medium', array( 'class' => 'partner-bio__logo-img' ) ); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $intro ) : ?>
                            <p class="partner-bio__intro"><?php echo esc_html( (string) $intro ); ?></p>
                        <?php elseif ( get_the_content() ) : ?>
                            <div class="partner-bio__content entry-content">
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        $theme_labels = array(
                            'safe'        => __( 'Safe', 'ai-awareness-day' ),
                            'smart'       => __( 'Smart', 'ai-awareness-day' ),
                            'creative'    => __( 'Creative', 'ai-awareness-day' ),
                            'responsible' => __( 'Responsible', 'ai-awareness-day' ),
                            'future'      => __( 'Future', 'ai-awareness-day' ),
                        );
                        $grouped = array();
                        foreach ( $links as $item ) {
                            if ( ! is_array( $item ) ) {
                                continue;
                            }
                            $t = isset( $item['theme'] ) ? (string) $item['theme'] : '';
                            $u = isset( $item['url'] ) ? (string) $item['url'] : '';
                            $title = isset( $item['title'] ) ? (string) $item['title'] : '';
                            if ( $u === '' || $title === '' ) {
                                continue;
                            }
                            if ( ! isset( $theme_labels[ $t ] ) ) {
                                $t = 'smart';
                            }
                            $grouped[ $t ][] = array(
                                'title'    => $title,
                                'duration' => isset( $item['duration'] ) ? (string) $item['duration'] : '',
                                'url'      => $u,
                            );
                        }
                        ?>
                        <?php if ( ! empty( $grouped ) ) : ?>
                            <div class="partner-bio__resources" aria-label="<?php esc_attr_e( 'Partner resources', 'ai-awareness-day' ); ?>">
                                <h2 class="partner-bio__resources-title"><?php esc_html_e( 'Partner resources', 'ai-awareness-day' ); ?></h2>
                                <div class="partner-bio__resources-grid">
                                    <?php foreach ( $theme_labels as $slug => $label ) : ?>
                                        <?php if ( empty( $grouped[ $slug ] ) ) : ?>
                                            <?php continue; ?>
                                        <?php endif; ?>
                                        <section class="partner-bio__theme">
                                            <h3 class="partner-bio__theme-title">
                                                <span class="partner-bio__theme-pill partner-bio__theme-pill--<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></span>
                                            </h3>
                                            <ul class="partner-bio__link-list">
                                                <?php foreach ( $grouped[ $slug ] as $li ) : ?>
                                                    <li class="partner-bio__link-item">
                                                        <a class="partner-bio__link" href="<?php echo esc_url( $li['url'] ); ?>" target="_blank" rel="noopener noreferrer">
                                                            <span class="partner-bio__link-title"><?php echo esc_html( $li['title'] ); ?></span>
                                                            <?php if ( $li['duration'] ) : ?>
                                                                <span class="partner-bio__link-duration"><?php echo esc_html( $li['duration'] ); ?></span>
                                                            <?php endif; ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </section>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="partner-bio__actions">
                            <?php if ( $url ) : ?>
                                <a href="<?php echo esc_url( $url ); ?>" class="btn-submit partner-bio__cta" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e( 'Visit website', 'ai-awareness-day' ); ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'partner' ) ); ?>" class="partner-bio__back">
                                <?php esc_html_e( 'Back to Partners', 'ai-awareness-day' ); ?>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
