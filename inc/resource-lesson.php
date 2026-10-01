<?php
/**
 * Single resource page: the lesson-plan layout (single-resource.php).
 *
 * Helpers that turn the Activity Schema meta into what a teacher reads at the
 * front of a room: a running clock down the steps, notes split into labelled
 * paragraphs and lists, and video references that jump the player.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seconds in a step duration written by an editor: "2 min", "60 seconds",
 * "1 hour", "90s", "1.5 mins". A range ("5-15 min") counts its upper end,
 * since the clock is for planning and a plan should not run over.
 *
 * @param string $text Duration as typed.
 * @return int Seconds, or 0 when the text does not read as a duration.
 */
function aiad_resource_duration_seconds( string $text ): int {
	$text = strtolower( trim( $text ) );
	if ( '' === $text ) {
		return 0;
	}
	if ( ! preg_match( '/(\d+(?:\.\d+)?)(?:\s*[-–to]+\s*(\d+(?:\.\d+)?))?\s*(h|hr|hrs|hour|hours|m|min|mins|minute|minutes|s|sec|secs|second|seconds)\b/u', $text, $m ) ) {
		return 0;
	}
	$amount = (float) ( isset( $m[2] ) && '' !== $m[2] ? $m[2] : $m[1] );
	$unit   = $m[3][0];
	$scale  = 'h' === $unit ? 3600 : ( 's' === $unit ? 1 : 60 );
	return (int) round( $amount * $scale );
}

/**
 * Seconds as a clock reading for the step rail: 0:00, 2:00, 12:30.
 *
 * @param int $seconds Seconds from the start of the lesson.
 * @return string
 */
function aiad_resource_clock( int $seconds ): string {
	return sprintf( '%d:%02d', intdiv( $seconds, 60 ), $seconds % 60 );
}

/**
 * Seconds as a length a person says: "16 min", "1 hr 5 min", "45 sec".
 *
 * @param int $seconds Seconds.
 * @return string
 */
function aiad_resource_length_label( int $seconds ): string {
	if ( $seconds < 60 ) {
		/* translators: %d: number of seconds. */
		return sprintf( __( '%d sec', 'ai-awareness-day' ), $seconds );
	}
	$minutes = (int) round( $seconds / 60 );
	if ( $minutes < 60 ) {
		/* translators: %d: number of minutes. */
		return sprintf( __( '%d min', 'ai-awareness-day' ), $minutes );
	}
	/* translators: 1: hours, 2: minutes. */
	return sprintf( __( '%1$d hr %2$d min', 'ai-awareness-day' ), intdiv( $minutes, 60 ), $minutes % 60 );
}

/**
 * The steps that will be shown, each with its start time on the lesson clock.
 *
 * The clock only runs when every step has a duration that parses; one gap
 * would put every later start time wrong, so then no step gets one and the
 * total is 0. Steps still keep their own duration text either way.
 *
 * @param array $instructions Output of aiad_normalise_instructions().
 * @return array{steps: array<int, array<string, mixed>>, total: int}
 */
function aiad_resource_lesson_steps( array $instructions ): array {
	$steps   = array();
	$elapsed = 0;
	$timed   = true;
	foreach ( $instructions as $step ) {
		$action = is_array( $step ) ? (string) ( $step['action'] ?? '' ) : (string) $step;
		if ( '' === trim( $action ) ) {
			continue;
		}
		$duration = is_array( $step ) ? trim( (string) ( $step['duration'] ?? '' ) ) : '';
		$seconds  = aiad_resource_duration_seconds( $duration );
		if ( 0 === $seconds ) {
			$timed = false;
		}
		$steps[] = array(
			'action'   => $action,
			'duration' => $duration,
			'start'    => $elapsed,
			'ref'      => is_array( $step ) ? trim( (string) ( $step['resource_ref'] ?? '' ) ) : '',
			'students' => is_array( $step ) ? trim( (string) ( $step['student_action'] ?? '' ) ) : '',
			'tip'      => is_array( $step ) ? trim( (string) ( $step['teacher_tip'] ?? '' ) ) : '',
		);
		$elapsed += $seconds;
	}
	if ( ! $timed ) {
		foreach ( $steps as $i => $step ) {
			$steps[ $i ]['start'] = null;
		}
		$elapsed = 0;
	}
	return array(
		'steps' => $steps,
		'total' => $elapsed,
	);
}

/**
 * Where a step reference points in the video, in seconds: "Video 2:01-3:16"
 * starts at 121, "Video 4:39-end" at 279, "Video" alone at 0. Anything that
 * does not name the video ("Slide 6", "Worksheet") is not a jump.
 *
 * @param string $ref Resource reference as typed.
 * @return int|null Seconds, or null when the reference is not to the video.
 */
function aiad_resource_ref_seek( string $ref ): ?int {
	if ( ! preg_match( '/\b(video|clip|film)\b/i', $ref ) ) {
		return null;
	}
	if ( preg_match( '/(?:(\d+):)?(\d{1,2}):(\d{2})/', $ref, $m ) ) {
		return ( (int) $m[1] ) * 3600 + ( (int) $m[2] ) * 60 + (int) $m[3];
	}
	return 0;
}

/**
 * Editor free text as reading HTML. Blank lines start paragraphs, lines
 * beginning "- " become a list, and a short "Label:" opening a paragraph is
 * set in bold, which is how the notes are written ("Sensitivity: ...").
 *
 * @param string $text Plain text from a textarea field.
 * @return string Safe HTML.
 */
function aiad_resource_rich_text( string $text ): string {
	$text = trim( str_replace( array( "\r\n", "\r" ), "\n", $text ) );
	if ( '' === $text ) {
		return '';
	}
	$html = '';
	foreach ( preg_split( "/\n\s*\n/", $text ) as $block ) {
		$lines = array_values( array_filter( array_map( 'trim', explode( "\n", $block ) ), 'strlen' ) );
		$para  = array();
		$items = array();
		$flush = static function () use ( &$para, &$items, &$html ): void {
			if ( $para ) {
				$body = esc_html( implode( ' ', $para ) );
				$body = preg_replace( '/^([A-Z][^:.!?]{1,40}):\s/u', '<strong>$1:</strong> ', $body, 1 );
				$html .= '<p>' . $body . '</p>';
				$para  = array();
			}
			if ( $items ) {
				$html .= '<ul>';
				foreach ( $items as $item ) {
					$html .= '<li>' . esc_html( $item ) . '</li>';
				}
				$html .= '</ul>';
				$items = array();
			}
		};
		foreach ( $lines as $line ) {
			if ( preg_match( '/^[-•*]\s+(.+)$/u', $line, $m ) ) {
				if ( $para ) {
					$flush();
				}
				$items[] = $m[1];
			} else {
				if ( $items ) {
					$flush();
				}
				$para[] = $line;
			}
		}
		$flush();
	}
	// Curly quotes and real dashes: editors type "--" for a dash.
	return wptexturize( $html );
}

/**
 * A YouTube oEmbed iframe that accepts player commands, so a step's video
 * reference can seek it. Other embeds pass through unchanged.
 *
 * @param string $html Embed HTML from aiad_resource_preview_video_html().
 * @return string
 */
function aiad_resource_embed_with_api( string $html ): string {
	if ( false === strpos( $html, 'youtube.com/embed/' ) ) {
		return $html;
	}
	return (string) preg_replace_callback(
		'#src="(https://www\.youtube(?:-nocookie)?\.com/embed/[^"]+)"#',
		static function ( array $m ): string {
			$src = add_query_arg(
				array(
					'enablejsapi' => '1',
					'origin'      => rawurlencode( home_url() ),
				),
				html_entity_decode( $m[1] )
			);
			return 'src="' . esc_url( $src ) . '"';
		},
		$html,
		1
	);
}

/**
 * The debate for a resource, one set-up for each National Conversation age
 * pathway. A pack written for the topic (_aiad_debate_pack) wins; any age it
 * leaves without a motion takes the debate network's motion bank item for
 * the resource's theme, with that item's own prompt, so every lesson has a
 * debate for every age. How students take part and how the day runs come
 * from the National Conversation page, so both pages describe one format.
 *
 * @param int      $post_id    Resource ID.
 * @param string   $theme      Strand slug (safe, smart, creative, responsible, future).
 * @param string[] $key_stages Key stage slugs on the resource.
 * @return array{ages: array<string, array<string, mixed>>, default: string}
 */
function aiad_resource_debate( int $post_id, string $theme, array $key_stages ): array {
	$pack = get_post_meta( $post_id, '_aiad_debate_pack', true );
	$pack = is_array( $pack ) ? $pack : array();

	$bank = class_exists( 'AIADN_Motions' ) ? AIADN_Motions::all() : array();

	$nc_ages = array();
	if ( function_exists( 'aiad_national_conversation_content' ) ) {
		$content = aiad_national_conversation_content( '', '', '' );
		$nc_ages = isset( $content['ages'] ) ? array_values( $content['ages'] ) : array();
	}

	$lines = static function ( string $text ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $text ) ), 'strlen' ) );
	};

	$labels = array(
		'primary'   => array( __( 'Primary', 'ai-awareness-day' ), __( 'Years 5 and 6', 'ai-awareness-day' ) ),
		'secondary' => array( __( 'Secondary', 'ai-awareness-day' ), __( 'Years 7 to 11', 'ai-awareness-day' ) ),
		'post16'    => array( __( 'Post-16', 'ai-awareness-day' ), __( 'Sixth form and college', 'ai-awareness-day' ) ),
	);

	$ages = array();
	$i    = 0;
	foreach ( $labels as $age => $label ) {
		$motion = trim( (string) ( $pack[ $age . '_motion' ] ?? '' ) );
		$prompt = trim( (string) ( $pack[ $age . '_prompt' ] ?? '' ) );
		$item   = $bank[ $age . '-' . $theme . '-1' ] ?? null;

		// The bank's prompt belongs to the bank's motion, so it only comes
		// with it; a pack motion carries its own prompt or none.
		if ( '' === $motion && $item ) {
			$motion = (string) $item['text'];
			if ( isset( $item['starter'] ) ) {
				$prompt = __( 'Sentence starter:', 'ai-awareness-day' ) . ' ' . $item['starter'];
			} elseif ( isset( $item['challenge'] ) ) {
				$prompt = __( 'Challenge card:', 'ai-awareness-day' ) . ' ' . $item['challenge'];
			} elseif ( isset( $item['tension'] ) ) {
				$prompt = sprintf(
					/* translators: 1: the central tension, 2: a research question. */
					__( 'The tension: %1$s. Research: %2$s', 'ai-awareness-day' ),
					$item['tension'],
					$item['research'] ?? ''
				);
			}
		}
		if ( '' === $motion ) {
			$i++;
			continue;
		}

		$nc        = $nc_ages[ $i ] ?? array();
		$ages[ $age ] = array(
			'name'    => $label[0],
			'years'   => $label[1],
			'motion'  => $motion,
			'prompt'  => $prompt,
			'for'     => $lines( (string) ( $pack[ $age . '_for' ] ?? '' ) ),
			'against' => $lines( (string) ( $pack[ $age . '_against' ] ?? '' ) ),
			'take'    => (string) ( $nc['take'] ?? '' ),
			'day'     => (string) ( $nc['day'] ?? '' ),
		);
		$i++;
	}

	// Open on the pathway the resource is written for.
	$default = 'secondary';
	foreach ( $key_stages as $ks ) {
		if ( in_array( $ks, array( 'eyfs', 'ks1', 'ks2' ), true ) ) {
			$default = 'primary';
			break;
		}
		if ( in_array( $ks, array( 'ks3', 'ks4' ), true ) ) {
			$default = 'secondary';
			break;
		}
		if ( 'ks5' === $ks ) {
			$default = 'post16';
			break;
		}
	}
	if ( $ages && ! isset( $ages[ $default ] ) ) {
		$default = (string) array_key_first( $ages );
	}

	return array(
		'ages'    => $ages,
		'default' => $default,
	);
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( is_admin() || ! is_singular( 'resource' ) ) {
			return;
		}
		// Priority 15 lands this after the modular theme sheets and the
		// AiAd27 layer, so the page's own rules win without doubled selectors.
		$css = AIAD_DIR . '/assets/css/pages/resource-lesson.css';
		if ( is_readable( $css ) ) {
			wp_enqueue_style( 'aiad-resource-lesson', AIAD_URI . '/assets/css/pages/resource-lesson.css', array( 'aiad-style' ), (string) filemtime( $css ) );
		}
		$js = AIAD_DIR . '/assets/js/resource-lesson.js';
		if ( is_readable( $js ) ) {
			wp_enqueue_script( 'aiad-resource-lesson', AIAD_URI . '/assets/js/resource-lesson.js', array(), (string) filemtime( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}
	},
	15
);
