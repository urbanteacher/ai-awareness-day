<?php
/**
 * Find Santa: a riddle a day from 1 to 25 December on the AI Literacy Periodic Table's page.
 *
 * The riddles are in data/literacy-table/santa.json, one per day, each describing a word on the table. On the table's
 * page the word of the week card is joined by the Santa card (aiad_render_literacy_santa(), drawn after it by
 * aiad_render_literacy_week()). assets/js/literacy-santa.js shows the Santa card instead of the word of the week on
 * 1 to 25 December, UK time, and picks the day's riddle there, so a cached page still changes at midnight. Opening a
 * word's card is free; its "Is Santa here?" button (in the panel, data-alpt-santa-slot) uses one of three guesses.
 * Progress and the streak are kept in the browser only.
 *
 * The answers go to the browser lightly encoded, so they don't show in the page's source at a glance.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The year's riddles, or an empty array if the file is missing or broken.
 *
 * @return array{year: int, days: array<int, array{day: int, sym: string, riddle: string}>}|array{}
 */
function aiad_literacy_santa(): array {
	$data = aiad_literacy_data( 'santa' );
	if ( empty( $data['year'] ) || empty( $data['days'] ) || ! is_array( $data['days'] ) ) {
		return array();
	}
	$known = array_flip( array_column( aiad_literacy_raw_elements(), 0 ) );
	// Like the word of the week, never a safeguarding word.
	$known = array_diff_key( $known, array_flip( aiad_literacy_safeguarding_syms() ) );
	$days  = array();
	foreach ( $data['days'] as $d ) {
		$day = (int) ( $d['day'] ?? 0 );
		$sym = (string) ( $d['sym'] ?? '' );
		// A riddle whose word isn't on the table could never be solved, and safeguarding words stay out: leave it out.
		if ( $day >= 1 && $day <= 25 && isset( $known[ $sym ] ) && '' !== trim( (string) ( $d['riddle'] ?? '' ) ) ) {
			$days[ $day ] = array( 'day' => $day, 'sym' => $sym, 'riddle' => (string) $d['riddle'] );
		}
	}
	ksort( $days );
	return $days ? array( 'year' => (int) $data['year'], 'days' => array_values( $days ) ) : array();
}

/**
 * Whether ?santa-day=N may show any day's riddle: for editors, and on a local or staging copy of the site.
 */
function aiad_literacy_santa_preview(): bool {
	return current_user_can( 'edit_pages' ) || in_array( wp_get_environment_type(), array( 'local', 'development', 'staging' ), true );
}

/**
 * Whether the Santa card should be on the page: from the start of November to Boxing Day of the riddles' year (the
 * script narrows it to 1 to 25 December on the reader's own clock), or any time for an editor checking it.
 */
function aiad_literacy_santa_due( array $santa ): bool {
	if ( ! $santa ) {
		return false;
	}
	if ( aiad_literacy_santa_preview() ) {
		return true;
	}
	$now   = new DateTimeImmutable( 'now', new DateTimeZone( 'Europe/London' ) );
	$start = new DateTimeImmutable( $santa['year'] . '-11-01 00:00', new DateTimeZone( 'Europe/London' ) );
	$end   = new DateTimeImmutable( $santa['year'] . '-12-27 00:00', new DateTimeZone( 'Europe/London' ) );
	return $now >= $start && $now < $end;
}

/**
 * The Santa card, hidden until literacy-santa.js shows it, with the riddles for the script.
 */
function aiad_render_literacy_santa(): string {
	$santa = aiad_literacy_santa();
	if ( ! aiad_literacy_santa_due( $santa ) ) {
		return '';
	}
	wp_enqueue_script( 'aiad-literacy-santa' );
	$days = array();
	foreach ( $santa['days'] as $d ) {
		// The answer reversed and base64-encoded: not secret, just not readable in the source at a glance.
		$days[] = array( $d['day'], $d['riddle'], base64_encode( strrev( $d['sym'] ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}
	$config = array(
		'year'    => $santa['year'],
		'days'    => $days,
		'share'   => home_url( '/santa/' ),
		// Editors (and local or staging copies) can try any day with ?santa-day=7 on the page.
		'preview' => aiad_literacy_santa_preview(),
	);
	$hat = '<svg class="alpt-santa__hat" viewBox="0 0 60 44" aria-hidden="true" focusable="false"><path d="M6 34C10 14 26 2 44 6c6 2 9 8 9 14-8-5-16-3-22 3z" fill="#E4002B"/><rect x="2" y="30" width="44" height="11" fill="#fff"/><circle cx="53" cy="22" r="6" fill="#fff"/></svg>';
	$holly = '<svg class="alpt-santa__holly" viewBox="0 0 90 60" aria-hidden="true" focusable="false"><path d="M45 34C34 20 18 22 6 14c4 10 0 16 6 22-6 2-8 6-6 10 12-6 26 0 39-12z" fill="#2F7D4F"/><path d="M45 34C56 20 72 22 84 14c-4 10 0 16-6 22 6 2 8 6 6 10-12-6-26 0-39-12z" fill="#1F6A40"/><path d="M45 34C36 26 22 30 12 28M45 34C54 26 68 30 78 28" stroke="#163F2A" stroke-width="1.4" fill="none"/><circle cx="40" cy="38" r="6" fill="#D7263D"/><circle cx="51" cy="37" r="6" fill="#E4314A"/><circle cx="45.5" cy="46" r="6" fill="#C21D33"/><circle cx="38" cy="36" r="1.6" fill="#fff" opacity=".7"/><circle cx="49" cy="35" r="1.6" fill="#fff" opacity=".7"/></svg>';
	ob_start();
	?>
<div class="alpt-santa" data-alpt-santa hidden aria-labelledby="alpt-santa-title">
	<div class="alpt-santa__deco" aria-hidden="true">
		<svg class="alpt-santa__lights" data-alpt-santa-lights preserveAspectRatio="none" focusable="false"></svg>
		<div class="alpt-santa__snow" data-alpt-santa-snow></div>
		<?php echo $holly; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a fixed drawing. ?>
	</div>
	<span class="alpt-santa__sticker" aria-hidden="true"><?php esc_html_e( 'New riddle every day', 'aiad-core' ); ?></span>
	<div class="alpt-santa__tile" aria-hidden="true"><span class="alpt-santa__q">?</span><?php echo $hat; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a fixed drawing. ?></div>
	<div class="alpt-santa__head">
		<h2 class="alpt-santa__name" id="alpt-santa-title"><?php esc_html_e( 'Where is Santa hiding today?', 'aiad-core' ); ?></h2>
		<p class="alpt-santa__label"><span data-alpt-santa-day></span><span data-alpt-santa-streak></span></p>
	</div>
	<div class="alpt-santa__riddle" data-alpt-santa-riddle></div>
	<div class="alpt-santa__status" aria-live="polite">
		<span class="alpt-santa__guesses"><span class="alpt-santa__meta"><?php esc_html_e( 'Guesses', 'aiad-core' ); ?></span><span class="alpt-santa__marks" data-alpt-santa-marks></span></span>
		<span class="alpt-santa__hint" data-alpt-santa-hint></span>
	</div>
	<div class="alpt-santa__fam" id="alpt-santa-fam" data-alpt-santa-fam hidden></div>
	<p class="alpt-santa__how" id="alpt-santa-how" hidden><?php esc_html_e( 'Santa is hiding in one word’s card. Read the riddle, open any card to read it, and press “Is Santa here?” when you think you’ve found him. You have three guesses; a wrong one tells you if you’re warm (right family) or cold. Opening cards to read them never uses a guess. A new riddle every day from 1 to 25 December.', 'aiad-core' ); ?></p>
	<div class="alpt-santa__actions">
		<button type="button" class="alpt-btn alpt-btn--santa" data-alpt-santa-board><?php esc_html_e( 'Show to class', 'aiad-core' ); ?></button>
		<button type="button" class="alpt-btn" data-alpt-santa-hint-btn aria-expanded="false" aria-controls="alpt-santa-fam"><?php esc_html_e( 'Hint', 'aiad-core' ); ?></button>
		<button type="button" class="alpt-btn" data-alpt-santa-how aria-expanded="false" aria-controls="alpt-santa-how"><?php esc_html_e( 'How to play', 'aiad-core' ); ?></button>
	</div>
	<script type="application/json" data-alpt-santa-data><?php echo wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * /santa/, the address in a shared result, opens the table's page.
 */
function aiad_literacy_santa_redirect(): void {
	if ( ! is_404() ) {
		return;
	}
	$path = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( 'santa' === strtolower( $path ) ) {
		wp_safe_redirect( aiad_literacy_table_url(), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'aiad_literacy_santa_redirect', 1 );
