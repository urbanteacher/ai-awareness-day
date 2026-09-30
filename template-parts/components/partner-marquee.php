<?php
/**
 * The partner logo strip: the homepage's campaign section and the partner logo strip block (blocks/partner-marquee)
 * both print it.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
        /*
         * Partner logo row. It lives at the top of this section rather than at
         * the top of the page: on the hero's strand ground the mixed-provenance
         * logos needed a plate behind each one to read at all, and on its own
         * band above the hero it sat oddly apart from the campaign it evidences.
         */
        $campaign_marquee = function_exists( 'aiad_get_hero_partner_marquee_entries' )
            ? aiad_get_hero_partner_marquee_entries()
            : array();
        if ( ! empty( $campaign_marquee ) ) :
            // Slower when more logos so the strip does not feel frantic.
            $marquee_secs = (int) min( 90, max( 28, count( $campaign_marquee ) * 5 ) );
            ?>
        <div class="partner-strip">
            <div class="hero-partner-marquee" role="region"
                aria-label="<?php esc_attr_e( 'Partner organisations', 'ai-awareness-day' ); ?>">
                <div class="hero-partner-marquee__viewport">
                    <div class="hero-partner-marquee__track"
                        style="<?php echo esc_attr( '--hero-marquee-duration: ' . $marquee_secs . 's' ); ?>">
                        <?php foreach ( array( 1, 2 ) as $_dup ) : ?>
                        <ul class="hero-partner-marquee__list">
                            <?php foreach ( $campaign_marquee as $row ) : ?>
                            <li class="hero-partner-marquee__item">
                                <a class="hero-partner-marquee__link" href="<?php echo esc_url( $row['href'] ); ?>" data-partner-id="<?php echo esc_attr( (string) $row['id'] ); ?>">
                                    <img class="hero-partner-marquee__img"
                                        src="<?php echo esc_url( $row['img'] ); ?>"
                                        alt="<?php echo esc_attr( $row['title'] ); ?>"
                                        width="240"
                                        height="120"
                                        loading="lazy"
                                        decoding="async" />
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
