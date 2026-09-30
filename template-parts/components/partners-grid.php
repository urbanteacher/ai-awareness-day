<?php
/**
 * The partner cards and the "Show More Partners" button: the homepage's campaign section and the partners block
 * (blocks/partners) both print them. main.js shows and hides the cards; the counts come from aiad_campaign_partners().
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$partners       = $args['partners'] ?? array();
$partners_count = (int) ( $args['count'] ?? 0 );
$initial_show_mobile = (int) ( $args['initial_show_mobile'] ?? 8 );
$initial_show   = $initial_show_mobile;
?>
            <div class="partners-grid">
                <?php
                foreach ($partners as $index => $partner):
                    if (empty($partner['name'])) {
                        continue;
                    }
                    $is_initially_hidden = $index >= $initial_show;
                    $card_classes = array('partner-card', 'fade-up', 'stagger-' . ($index + 1));
                    if (!empty($partner['provides_ai'])) {
                        $card_classes[] = 'partner-card--ai-resources';
                    }
                    if ($is_initially_hidden) {
                        $card_classes[] = 'partner-card--hidden';
                    }
                    $card_class_attr = esc_attr(implode(' ', $card_classes));
                    $has_ai_url      = !empty($partner['provides_ai']) && $partner['card_href'] !== '';
                    $tag_open  = $has_ai_url
                        ? sprintf(
                            '<a class="%s" data-partner-index="%d" data-partner-id="%d" href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s">',
                            $card_class_attr,
                            (int) $index,
                            (int) $partner['id'],
                            esc_url($partner['card_href']),
                            esc_attr(sprintf(__('Visit %s — AI learning resources (opens in new tab)', 'ai-awareness-day'), $partner['name']))
                        )
                        : sprintf('<div class="%s" data-partner-index="%d">', $card_class_attr, (int) $index);
                    $tag_close = $has_ai_url ? '</a>' : '</div>';
                    ?>
                    <?php echo $tag_open; ?>
                        <div class="partner-logo">
                            <?php if ($partner['logo']): ?>
                                <img src="<?php echo esc_url($partner['logo']); ?>"
                                    alt="<?php echo esc_attr($partner['name']); ?>" class="partner-logo__img"
                                    onerror="this.classList.add('is-broken');" />
                            <?php endif; ?>
                        </div>
                        <?php if ( ! empty( $partner['stats'] ) ) : ?>
                            <p class="partner-stats"><?php echo esc_html( $partner['stats'] ); ?></p>
                        <?php else : ?>
                            <p class="partner-stats partner-stats--empty" aria-hidden="true"></p>
                        <?php endif; ?>
                        <?php if (!empty($partner['provides_ai'])): ?>
                            <p class="partner-card__ai-hint"><?php esc_html_e('AI resources ↗', 'ai-awareness-day'); ?></p>
                        <?php endif; ?>
                    <?php echo $tag_close; ?>
                <?php endforeach; ?>

                <?php
                $dummy_index = $partners_count;
                ?>
                <a href="#contact"
                    class="partner-card partner-card--dummy fade-up stagger-<?php echo $dummy_index + 1; ?>"
                    data-partner-index="<?php echo $dummy_index; ?>">
                    <div class="partner-logo" aria-hidden="true">
                        <span class="partner-card__mark">↗</span>
                    </div>
                    <h3><?php esc_html_e('Join the campaign', 'ai-awareness-day'); ?></h3>
                    <p class="partner-stats">
                        <?php esc_html_e('Complete the form to join the movement.', 'ai-awareness-day'); ?>
                    </p>
                </a>
            </div>

            <?php if ($partners_count > $initial_show_mobile) : ?>
                <div class="partners-reveal-wrapper" style="text-align: center; margin-top: 2.5rem;">
                    <button type="button"
                        class="partners-reveal-btn"
                        data-initial-show="<?php echo (int) $initial_show; ?>"
                        data-label-more="<?php echo esc_attr(__('Show More Partners', 'ai-awareness-day')); ?>"
                        data-label-less="<?php echo esc_attr(__('Show Less', 'ai-awareness-day')); ?>"
                        aria-expanded="false">
                        <span class="reveal-text"><?php esc_html_e('Show More Partners', 'ai-awareness-day'); ?></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="partners-reveal-btn__chevron"
                            style="margin-left: 0.5rem; transition: transform 0.3s;"
                            aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
