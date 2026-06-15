<?php
/**
 * AIRB_Components
 *
 * Shared HTML component builders for all role result screens.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared HTML component builders for result screens.
 */
class AIRB_Components {

    // -------------------------------------------------------------------------
    // Score hero
    // -------------------------------------------------------------------------

    /**
     * Full-width score hero with band bar.
     *
     * @param array $args {
     *   @type int    $score        0–100
     *   @type string $label        Band label e.g. 'Strong'
     *   @type string $sub_label    Sub-label e.g. 'Strong position'
     *   @type string $consequence  One-sentence plain-English meaning
     *   @type string $color_ramp   red|amber|blue|green|purple|teal
     *   @type array  $bands        Ordered array of band labels for the bar
     *   @type int    $active_band  0-indexed position of active band
     *   @type string $metric_label e.g. 'Overall readiness'
     * }
     */
    public static function score_hero( array $args ): string {
        $score       = (int) ( $args['score'] ?? 0 );
        $label       = esc_html( $args['label'] ?? '' );
        $sub_label   = esc_html( $args['sub_label'] ?? '' );
        $consequence = esc_html( $args['consequence'] ?? '' );
        $ramp        = $args['color_ramp'] ?? 'blue';
        $bands       = $args['bands'] ?? [];
        $active      = (int) ( $args['active_band'] ?? 0 );
        $metric_lbl  = esc_html( $args['metric_label'] ?? 'Overall readiness' );

        $colors = self::ramp_colors( $ramp );

        $html  = '<div class="airb__score-hero airb__score-hero--' . esc_attr( $ramp ) . '">';
        $html .= '<div class="airb__score-hero__top">';
        $html .= '<span class="airb__score-hero__num">' . $score . '</span>';
        $html .= '<div class="airb__score-hero__meta">';
        $html .= '<span class="airb__score-hero__label">' . $label . '</span>';
        if ( $sub_label ) {
            $html .= '<span class="airb__score-hero__sub">' . $sub_label . '</span>';
        }
        $html .= '<span class="airb__score-hero__metric-lbl">' . $metric_lbl . '</span>';
        $html .= '</div>'; // meta
        $html .= '</div>'; // top

        if ( $consequence ) {
            $html .= '<p class="airb__score-hero__consequence">' . $consequence . '</p>';
        }

        // Band bar
        if ( ! empty( $bands ) ) {
            $html .= '<div class="airb__band-bar" role="img" aria-label="' . esc_attr( $label . ' — band ' . ( $active + 1 ) . ' of ' . count( $bands ) ) . '">';
            foreach ( $bands as $i => $band_label ) {
                $is_active = ( $i === $active );
                $html .= '<div class="airb__band-bar__segment' . ( $is_active ? ' airb__band-bar__segment--active' : '' ) . '" aria-hidden="true"></div>';
            }
            $html .= '</div>'; // band-bar
            $html .= '<div class="airb__band-labels" aria-hidden="true">';
            foreach ( $bands as $band_label ) {
                $html .= '<span>' . esc_html( $band_label ) . '</span>';
            }
            $html .= '</div>'; // band-labels
        }

        $html .= '</div>'; // score-hero
        return $html;
    }

    // -------------------------------------------------------------------------
    // Metric cards
    // -------------------------------------------------------------------------

    /**
     * Single metric card.
     *
     * @param array $args {
     *   @type string $label       Short label e.g. 'AI risk exposure'
     *   @type int    $value       0–100 or raw number
     *   @type string $signal      Band signal text e.g. 'High exposure'
     *   @type string $description One sentence explanation
     *   @type string $color_ramp  red|amber|green|blue|purple
     *   @type bool   $pct         Append % to value. Default true.
     * }
     */
    public static function metric_card( array $args ): string {
        $label  = esc_html( $args['label'] ?? '' );
        $value  = $args['value'] ?? 0;
        $signal = esc_html( $args['signal'] ?? '' );
        $desc   = esc_html( $args['description'] ?? '' );
        $ramp   = $args['color_ramp'] ?? 'blue';
        $pct    = isset( $args['pct'] ) ? (bool) $args['pct'] : true;

        $display = $pct ? ( (int) $value . '%' ) : esc_html( $value );
        $colors  = self::ramp_colors( $ramp );

        $html  = '<div class="airb__metric-card airb__metric-card--' . esc_attr( $ramp ) . '">';
        $html .= '<div class="airb__metric-card__label">' . $label . '</div>';
        $html .= '<div class="airb__metric-card__value">' . $display . '</div>';
        if ( $signal ) {
            $html .= '<div class="airb__metric-card__signal">' . $signal . '</div>';
        }
        if ( $desc ) {
            $html .= '<p class="airb__metric-card__desc">' . $desc . '</p>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Wrap metric cards in a responsive grid.
     *
     * @param string[] $cards  Array of metric_card() HTML strings
     * @param int      $cols   Columns hint (2 or 3). CSS handles responsiveness.
     */
    public static function metric_grid( array $cards, int $cols = 3 ): string {
        $html  = '<div class="airb__metric-grid airb__metric-grid--cols-' . (int) $cols . '">';
        $html .= implode( '', $cards );
        $html .= '</div>';
        return $html;
    }

    // -------------------------------------------------------------------------
    // Domain bar list
    // -------------------------------------------------------------------------

    /**
     * Single domain bar row.
     *
     * @param array $args {
     *   @type string $label    Domain label
     *   @type int    $score    0–100 readiness score
     *   @type string $badge    Optional badge text e.g. 'Critical'
     *   @type string $badge_ramp  red|amber|green|blue
     * }
     */
    public static function domain_bar_row( array $args ): string {
        $label      = esc_html( $args['label'] ?? '' );
        $score      = (int) ( $args['score'] ?? 0 );
        $badge      = esc_html( $args['badge'] ?? '' );
        $badge_ramp = $args['badge_ramp'] ?? '';

        $bar_ramp = self::score_to_ramp( $score );
        $colors   = self::ramp_colors( $bar_ramp );

        $html  = '<div class="airb__domain-row">';
        $html .= '<span class="airb__domain-row__label">' . $label . '</span>';
        $html .= '<div class="airb__domain-row__bar-wrap" role="img" aria-label="' . esc_attr( $label . ': ' . $score . '%' ) . '">';
        $html .= '<div class="airb__domain-row__bar airb__domain-row__bar--' . esc_attr( $bar_ramp ) . '" style="width:' . $score . '%"></div>';
        $html .= '</div>';
        $html .= '<span class="airb__domain-row__val airb__domain-row__val--' . esc_attr( $bar_ramp ) . '">' . $score . '%</span>';
        if ( $badge ) {
            $html .= '<span class="airb__badge airb__badge--' . esc_attr( $badge_ramp ?: $bar_ramp ) . '">' . $badge . '</span>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Full domain bar list card.
     *
     * @param array $domains  Array of domain_bar_row() $args arrays
     */
    public static function domain_bar_list( array $domains ): string {
        $html  = '<div class="airb__card airb__domain-list">';
        foreach ( $domains as $domain ) {
            $html .= self::domain_bar_row( $domain );
        }
        $html .= '</div>';
        return $html;
    }

    // -------------------------------------------------------------------------
    // Focus cards
    // -------------------------------------------------------------------------

    /**
     * Priority focus area card — shared across all roles.
     *
     * @param array $args {
     *   @type string   $title       Domain name
     *   @type int      $score       0–100
     *   @type string   $severity    critical|high|moderate
     *   @type string   $summary     One paragraph description
     *   @type string[] $impact      Bullet points (optional)
     *   @type string   $impact_title  Heading for impact box. Default 'In practice this means'
     *   @type string[] $actions     Numbered action strings
     *   @type string   $badge_text  Override badge text. Defaults to severity + score.
     * }
     */
    public static function focus_card( array $args ): string {
        $title        = esc_html( $args['title'] ?? '' );
        $score        = (int) ( $args['score'] ?? 0 );
        $severity     = $args['severity'] ?? 'moderate';
        $summary      = esc_html( $args['summary'] ?? '' );
        $impact       = $args['impact'] ?? array();
        $impact_title = esc_html( $args['impact_title'] ?? __( 'In practice this means', 'ai-risk-benchmark' ) );
        $actions      = $args['actions'] ?? array();
        $badge_text   = $args['badge_text'] ?? ( ucfirst( (string) $severity ) . ' · ' . $score . '%' );
        $variant      = $args['variant'] ?? 'teacher';

        if ( 'parent' === $variant || 'public' === $variant ) {
            $badge_slug = in_array( $severity, array( 'critical', 'risk' ), true ) ? 'risk' : 'attention';
            $html       = '<div class="airb__parent-topic-card airb__parent-topic-card--' . esc_attr( $severity ) . '">';
            $html      .= '<div class="airb__parent-topic-header">';
            $html      .= '<h4 class="airb__parent-topic-title">' . $title . '</h4>';
            $html      .= '<span class="airb__parent-metric-badge airb__parent-metric-badge--' . esc_attr( $badge_slug ) . '">' . esc_html( $badge_text ) . '</span>';
            $html      .= '</div>';
            if ( $summary ) {
                $html .= '<p class="airb__parent-topic-summary">' . $summary . '</p>';
            }
            if ( ! empty( $impact ) ) {
                $html .= '<div class="airb__parent-topic-challenge airb__parent-topic-challenge--' . esc_attr( $severity ) . '">';
                $html .= '<div class="airb__parent-topic-challenge-title">' . $impact_title . '</div>';
                foreach ( $impact as $item ) {
                    $html .= '<div class="airb__parent-topic-challenge-bullet">' . esc_html( $item ) . '</div>';
                }
                $html .= '</div>';
            }
            if ( ! empty( $actions ) ) {
                foreach ( $actions as $idx => $action ) {
                    $html .= '<div class="airb__parent-action-row">';
                    $html .= '<span class="airb__parent-action-num">' . ( (int) $idx + 1 ) . '</span>';
                    $html .= '<span class="airb__parent-action-text">' . esc_html( $action ) . '</span>';
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
            return $html;
        }

        $badge_slug = ( 'critical' === $severity ) ? 'risk' : ( ( 'high' === $severity ) ? 'attention' : 'moderate' );
        $mod        = ( 'teacher' === $variant ) ? ' airb__teacher-focus-card' : ( ( 'support' === $variant ) ? ' airb__support-focus-card' : '' );

        $html  = '<div class="airb__focus-card' . $mod . ' airb__focus-card--' . esc_attr( $severity ) . '">';
        $html .= '<div class="airb__focus-card-header">';
        $html .= '<h4 class="airb__focus-card-title">' . $title . '</h4>';
        $html .= '<span class="airb__focus-badge airb__focus-badge--' . esc_attr( $badge_slug ) . '">' . esc_html( $badge_text ) . '</span>';
        $html .= '</div>';

        if ( $summary ) {
            $html .= '<p class="airb__focus-card-summary">' . $summary . '</p>';
        }

        if ( ! empty( $impact ) ) {
            $html .= '<div class="airb__focus-practice airb__teacher-focus-practice">';
            $html .= '<div class="airb__focus-practice-title">' . $impact_title . '</div>';
            foreach ( $impact as $item ) {
                $html .= '<div class="airb__teacher-focus-impact">' . esc_html( $item ) . '</div>';
            }
            $html .= '</div>';
        }

        if ( ! empty( $actions ) ) {
            foreach ( $actions as $idx => $action ) {
                $html .= '<div class="airb__teacher-action-row">';
                $html .= '<span class="airb__teacher-action-num">' . ( (int) $idx + 1 ) . '</span>';
                $html .= '<span class="airb__teacher-action-text">' . esc_html( $action ) . '</span>';
                $html .= '</div>';
            }
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Unified bias health payload for teacher, leader and student results.
     *
     * @param array<string, mixed> $results Scored results.
     * @param array<string, mixed> $cfg     Role result config.
     * @param array<string, mixed> $opts    Optional subtitle, callout, role slug.
     * @return array<string, mixed>|null
     */
    public static function bias_health( array $results, array $cfg, array $opts = array() ): ?array {
        if ( ! array_key_exists( 'bias_readiness', $results ) || null === $results['bias_readiness'] ) {
            return null;
        }

		$role      = (string) ( $opts['role'] ?? 'teacher' );
		$score     = (int) $results['bias_readiness'];
		$display   = isset( $opts['score'] ) ? (int) $opts['score'] : $score;
		$threshold = (int) ( $cfg['bias_health_callout_threshold'] ?? 50 );
		$subtitle  = (string) ( $opts['subtitle'] ?? __( 'Fairness · protected characteristics · equality duty', 'ai-risk-benchmark' ) );
		$callout   = (string) ( $opts['callout'] ?? ( $cfg['bias_health_callout'] ?? '' ) );
		$band_lbl  = (string) ( $opts['band_label'] ?? AIRB_Scoring::readiness_band_label( $display ) );

		if ( class_exists( 'AIRB_Copy_Tiers', false ) ) {
			$tier_copy = AIRB_Copy_Tiers::for_role( $role )->bias_readiness( $score );
			if ( ! empty( $tier_copy['consequence'] ) && $score < $threshold ) {
				$callout = (string) $tier_copy['consequence'];
			}
		}

		return array(
			'title'        => (string) ( $cfg['bias_health_title'] ?? AIRB_Scoring::bias_readiness_label() ),
			'subtitle'     => (string) ( $cfg['bias_health_subtitle'] ?? $subtitle ),
			'score'        => $display,
			'band_label'   => $band_lbl,
			'show_callout' => $score < $threshold,
			'callout'      => $callout,
		);
    }

    // -------------------------------------------------------------------------
    // Strength rows
    // -------------------------------------------------------------------------

    /**
     * Single strength row.
     *
     * @param array $args {
     *   @type string $title       Strength headline
     *   @type string $description Supporting sentence
     * }
     */
    public static function strength_row( array $args ): string {
        $title = esc_html( $args['title'] ?? '' );
        $desc  = esc_html( $args['description'] ?? '' );

        $html  = '<div class="airb__strength-row">';
        $html .= '<div class="airb__strength-row__tick" aria-hidden="true"></div>';
        $html .= '<div class="airb__strength-row__copy">';
        $html .= '<p class="airb__strength-row__title">' . $title . '</p>';
        if ( $desc ) {
            $html .= '<p class="airb__strength-row__desc">' . $desc . '</p>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Strength list card.
     *
     * @param array $strengths  Array of strength_row() $args arrays
     */
    public static function strength_list( array $strengths ): string {
        if ( empty( $strengths ) ) return '';
        $html  = '<div class="airb__card airb__strength-list">';
        foreach ( $strengths as $s ) {
            $html .= self::strength_row( $s );
        }
        $html .= '</div>';
        return $html;
    }

    // -------------------------------------------------------------------------
    // Oversight gauge panel
    // -------------------------------------------------------------------------

    /**
     * Oversight gauge panel — wraps the SVG gauge with label and help text.
     * The SVG gauge itself is generated by oversightGaugeSvg() in JS for the
     * live screen; this PHP method generates the equivalent for email/export.
     *
     * @param array $args {
     *   @type int    $value       0–100
     *   @type string $signal      Band label e.g. 'Strong oversight'
     *   @type string $consequence Help text
     *   @type string $role        teacher|student|support|public|leader
     * }
     */
    public static function oversight_gauge_panel( array $args ): string {
        $value       = (int) ( $args['value'] ?? 0 );
        $signal      = esc_html( $args['signal'] ?? '' );
        $consequence = esc_html( $args['consequence'] ?? '' );
        $role        = $args['role'] ?? 'teacher';

        $title = __( 'Human Oversight Ratio', 'ai-risk-benchmark' );
        if ( $role === 'public' ) {
            $title = __( 'Verification habit', 'ai-risk-benchmark' );
        } elseif ( $role === 'student' ) {
            $title = __( 'How often you check AI answers', 'ai-risk-benchmark' );
        }

        $color = self::oversight_zone_color( $value );

        $html  = '<div class="airb__res-panel airb__res-panel--gauge" data-oversight-value="' . $value . '">';
        $html .= '<h3>' . esc_html( $title ) . '</h3>';
        $html .= '<div class="airb__res-gauge-wrap"><!-- SVG injected by JS --></div>';
        if ( $signal ) {
            $html .= '<p class="airb__gauge-band" style="color:' . esc_attr( $color ) . '">' . $signal . '</p>';
        }
        if ( $consequence ) {
            $html .= '<p class="airb__gauge-help">' . $consequence . '</p>';
        }
        $html .= '<div class="airb__gauge-share">';
        $html .= '<button type="button" class="airb__btn airb__btn--ghost airb__btn--sm airb__gauge-share-btn" ';
        $html .= 'data-airb-share-oversight-gauge data-oversight-value="' . $value . '">';
        $html .= esc_html__( 'Share as image', 'ai-risk-benchmark' );
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    // -------------------------------------------------------------------------
    // Peer benchmark
    // -------------------------------------------------------------------------

    /**
     * Compact peer benchmark comparison row.
     *
     * @param array $args {
     *   @type int    $your_score
     *   @type int    $avg_score
     *   @type string $avg_label   e.g. 'Average secondary school'
     *   @type int    $top_score
     *   @type string $top_label   e.g. 'Top quartile schools'
     * }
     */
    public static function peer_benchmark_row( array $args ): string {
        $yours     = (int) ( $args['your_score'] ?? 0 );
        $avg       = (int) ( $args['avg_score'] ?? 0 );
        $avg_lbl   = esc_html( $args['avg_label'] ?? 'Average school' );
        $top       = (int) ( $args['top_score'] ?? 0 );
        $top_lbl   = esc_html( $args['top_label'] ?? 'Top quartile' );

        $gap_avg = $avg - $yours;
        $gap_top = $top - $yours;

        $gap_avg_text = $gap_avg > 0
            ? $gap_avg . ' points below average'
            : ( $gap_avg < 0 ? abs( $gap_avg ) . ' points above average' : 'Equal to average' );

        $gap_top_text = $gap_top > 0
            ? $gap_top . ' points below top quartile'
            : ( $gap_top < 0 ? abs( $gap_top ) . ' points above top quartile' : 'Equal to top quartile' );

        $html  = '<div class="airb__peer-bench">';
        $html .= '<div class="airb__peer-bench__scores">';
        $html .= self::_peer_score( $yours, 'You', 'red' );
        $html .= self::_peer_score( $avg, $avg_lbl, 'gray' );
        $html .= self::_peer_score( $top, $top_lbl, 'green' );
        $html .= '</div>';
        $html .= '<div class="airb__peer-bench__gaps">';
        $html .= '<span>' . esc_html( $gap_avg_text ) . '</span>';
        $html .= '<span>' . esc_html( $gap_top_text ) . '</span>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    private static function _peer_score( int $score, string $label, string $ramp ): string {
        $html  = '<div class="airb__peer-bench__score">';
        $html .= '<span class="airb__peer-bench__score-val airb__peer-bench__score-val--' . esc_attr( $ramp ) . '">' . $score . '%</span>';
        $html .= '<span class="airb__peer-bench__score-lbl">' . esc_html( $label ) . '</span>';
        $html .= '</div>';
        return $html;
    }

    // -------------------------------------------------------------------------
    // Whole-school unlock panel
    // -------------------------------------------------------------------------

    /**
     * Whole-school community unlock panel.
     *
     * @param array $args {
     *   @type array  $slots    Array of {label, count, target} e.g.
     *                          [['label'=>'Teachers','count'=>4,'target'=>20], ...]
     *   @type string $intro    Intro paragraph copy
     *   @type string $cta_text Button label
     * }
     */
    public static function whole_school_unlock( array $args ): string {
        $slots    = $args['slots'] ?? [];
        $intro    = esc_html( $args['intro'] ?? '' );
        $cta_text = esc_html( $args['cta_text'] ?? 'Roll out to your school' );

        $html  = '<div class="airb__card airb__unlock-panel">';
        if ( $intro ) {
            $html .= '<p class="airb__unlock-panel__intro">' . $intro . '</p>';
        }
        if ( ! empty( $slots ) ) {
            $html .= '<div class="airb__unlock-panel__grid">';
            foreach ( $slots as $slot ) {
                $label  = esc_html( $slot['label'] ?? '' );
                $count  = (int) ( $slot['count'] ?? 0 );
                $target = (int) ( $slot['target'] ?? 20 );
                $done   = $count >= $target;
                $html  .= '<div class="airb__unlock-slot' . ( $done ? ' airb__unlock-slot--done' : '' ) . '">';
                $html  .= '<span class="airb__unlock-slot__label">' . $label . '</span>';
                $html  .= '<span class="airb__unlock-slot__count">' . $count . ' of ' . $target . '</span>';
                $html  .= '</div>';
            }
            $html .= '</div>';
        }
        $html .= '<button type="button" class="airb__btn airb__btn--secondary airb__unlock-panel__cta">';
        $html .= $cta_text . ' ↗';
        $html .= '</button>';
        $html .= '</div>';
        return $html;
    }

    // -------------------------------------------------------------------------
    // CTA block
    // -------------------------------------------------------------------------

    /**
     * Recommended next step CTA block.
     *
     * @param array $args {
     *   @type string   $eyebrow     Small label above title
     *   @type string   $title
     *   @type string   $description
     *   @type string[] $includes    Feature bullet list
     *   @type string[] $tags        Small pill tags
     *   @type string   $button      Primary button label
     *   @type string   $color_ramp  Background ramp: green|purple|gray(default)
     * }
     */
    public static function cta_block( array $args ): string {
        $eyebrow = esc_html( $args['eyebrow'] ?? 'Recommended next step' );
        $title   = esc_html( $args['title'] ?? '' );
        $desc    = esc_html( $args['description'] ?? '' );
        $incl    = $args['includes'] ?? [];
        $tags    = $args['tags'] ?? [];
        $button  = esc_html( $args['button'] ?? 'Get started' );
        $ramp    = $args['color_ramp'] ?? 'gray';

        $html  = '<div class="airb__cta-block airb__cta-block--' . esc_attr( $ramp ) . '">';
        $html .= '<span class="airb__cta-block__eyebrow">' . $eyebrow . '</span>';
        $html .= '<h3 class="airb__cta-block__title">' . $title . '</h3>';
        if ( $desc ) {
            $html .= '<p class="airb__cta-block__desc">' . $desc . '</p>';
        }
        if ( ! empty( $incl ) ) {
            $html .= '<ul class="airb__cta-block__includes">';
            foreach ( $incl as $item ) {
                $html .= '<li>' . esc_html( $item ) . '</li>';
            }
            $html .= '</ul>';
        }
        if ( ! empty( $tags ) ) {
            $html .= '<div class="airb__cta-block__tags">';
            foreach ( $tags as $tag ) {
                $html .= '<span class="airb__cta-tag">' . esc_html( $tag ) . '</span>';
            }
            $html .= '</div>';
        }
        $html .= '<button type="button" class="airb__btn airb__btn--primary airb__cta-block__btn">';
        $html .= $button . ' ↗';
        $html .= '</button>';
        $html .= '</div>';
        return $html;
    }

    // -------------------------------------------------------------------------
    // Share panel (public role)
    // -------------------------------------------------------------------------

    /**
     * Social share panel with pre-written share text.
     *
     * @param array $args {
     *   @type int    $score
     *   @type string $band_label   e.g. 'Take care'
     *   @type string $top_gap      e.g. 'Data & privacy'
     *   @type string $share_text   Pre-populated share copy with {score}/{top_gap} replaced
     *   @type string $color_ramp
     * }
     */
    public static function share_panel( array $args ): string {
        $score      = (int) ( $args['score'] ?? 0 );
        $band_label = esc_html( $args['band_label'] ?? '' );
        $share_text = esc_html( $args['share_text'] ?? '' );
        $ramp       = $args['color_ramp'] ?? 'purple';

        $html  = '<div class="airb__share-panel airb__share-panel--' . esc_attr( $ramp ) . '">';
        $html .= '<span class="airb__share-panel__eyebrow">' . esc_html__( 'Share your results', 'ai-risk-benchmark' ) . '</span>';
        $html .= '<h3 class="airb__share-panel__title">' . esc_html__( 'Most people don\'t know how they really use AI', 'ai-risk-benchmark' ) . '</h3>';
        if ( $share_text ) {
            $html .= '<div class="airb__share-panel__preview">';
            $html .= '<p>' . $share_text . '</p>';
            $html .= '</div>';
        }
        $html .= '<div class="airb__share-panel__actions">';
        $html .= '<button type="button" class="airb__btn airb__btn--primary" data-airb-share-social>';
        $html .= esc_html__( 'Share on social', 'ai-risk-benchmark' ) . ' ↗';
        $html .= '</button>';
        $html .= '<button type="button" class="airb__btn airb__btn--ghost" data-airb-retake>';
        $html .= esc_html__( 'Retake the benchmark', 'ai-risk-benchmark' );
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Map a 0-100 score to a color ramp name.
     */
    public static function score_to_ramp( int $score ): string {
        if ( $score <= 34 ) return 'red';
        if ( $score <= 59 ) return 'amber';
        if ( $score <= 74 ) return 'blue';
        return 'green';
    }

    /**
     * Map a ramp name to hex colors [bg, text, bar].
     * These match the CSS variables in airb-front.css.
     */
    public static function ramp_colors( string $ramp ): array {
        $map = [
            'red'    => [ '#FCEBEB', '#A32D2D', '#E24B4A' ],
            'amber'  => [ '#FAEEDA', '#854F0B', '#EF9F27' ],
            'blue'   => [ '#E6F1FB', '#185FA5', '#378ADD' ],
            'green'  => [ '#EAF3DE', '#3B6D11', '#639922' ],
            'purple' => [ '#EEEDFE', '#534AB7', '#7F77DD' ],
            'teal'   => [ '#E1F5EE', '#0F6E56', '#1D9E75' ],
            'gray'   => [ '#F1EFE8', '#5F5E5A', '#888780' ],
        ];
        return $map[ $ramp ] ?? $map['gray'];
    }

    /**
     * Oversight zone color (used for gauge band label).
     */
    public static function oversight_zone_color( int $pct ): string {
        if ( $pct >= 76 ) return '#3B6D11';
        if ( $pct >= 51 ) return '#185FA5';
        if ( $pct >= 26 ) return '#854F0B';
        return '#A32D2D';
    }
}
