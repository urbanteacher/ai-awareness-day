<?php
/**
 * The campaign embed (a video, or a LinkedIn post): the homepage's campaign section and the campaign embed block
 * (blocks/campaign-embed) both print it. Nothing is printed without an address.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$campaign_embed_src = esc_url( (string) ( $args['src'] ?? '' ) );
$campaign_has_embed = ! empty( $campaign_embed_src );
/*
 * The setting predates this and is still named for LinkedIn, but it now holds
 * whatever embed the campaign is running. A video needs a 16:9 frame and an
 * allow attribute rather than the portrait box a LinkedIn post wanted, so the
 * two cases are distinguished here instead of assuming one.
 */
$campaign_embed_is_video = (bool) preg_match('~(youtube\.com|youtube-nocookie\.com|youtu\.be|vimeo\.com)~i', $campaign_embed_src);
$campaign_embed_title    = $campaign_embed_is_video
    ? __('AI Awareness Day 2027 campaign video', 'ai-awareness-day')
    : __('Embedded LinkedIn post', 'ai-awareness-day');
?>
            <?php if ($campaign_has_embed): ?>
                <div class="campaign-embed fade-up">
                    <div class="campaign-embed__wrapper<?php echo $campaign_embed_is_video ? ' campaign-embed__wrapper--video' : ''; ?>">
                        <iframe src="<?php echo esc_url($campaign_embed_src); ?>"
                            <?php if (!$campaign_embed_is_video) : ?>height="399" width="504"<?php endif; ?>
                            <?php if ($campaign_embed_is_video) : ?>allow="autoplay; encrypted-media; picture-in-picture; fullscreen"<?php endif; ?>
                            frameborder="0" allowfullscreen
                            title="<?php echo esc_attr($campaign_embed_title); ?>"
                            loading="lazy"></iframe>
                    </div>
                </div>
            <?php endif; ?>
