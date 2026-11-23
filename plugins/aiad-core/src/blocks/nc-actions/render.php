<?php
/**
 * National Conversation buttons: what the page offers depends on whether the portal is live yet
 * (aiad_portal_is_live()) and whether the readiness check is installed, so it is worked out when the page renders.
 * The markup is page-national-conversation.php's.
 *
 * hero:     the buttons under the dates, and the nominate and sign-in links.
 * cta:      the closing section's text, button and links.
 * nominate: the "Nominate it" link in the section for organisations (nothing until the portal is live).
 *
 * @package AI_Awareness_Day
 *
 * @var array $attributes Block attributes.
 */

// This block prints the AI Awareness Day theme's markup with the theme's own functions; under another theme it
// prints nothing rather than stopping the page with a fatal error.
foreach ( array( 'aiad_portal_is_live', 'aiad_conversation_url', 'aiad_national_conversation_dates' ) as $aiad_theme_fn ) {
	if ( ! function_exists( $aiad_theme_fn ) ) {
		return;
	}
}

$aiad_portal_live  = aiad_portal_is_live();
$aiad_register_url = $aiad_portal_live ? ( aiad_conversation_url( 'register' ) ?: home_url( '/#contact' ) ) : '';
$aiad_sign_in_url  = $aiad_portal_live ? aiad_conversation_url( 'join' ) : '';
$aiad_nominate_url = $aiad_portal_live ? aiad_conversation_url( 'nominate' ) : '';
$aiad_readiness    = function_exists( 'aiad_get_benchmark_start_url' ) ? aiad_get_benchmark_start_url() : '';
$aiad_opens_long   = wp_date( 'j F Y', aiad_national_conversation_dates()['opens']->getTimestamp() );
$aiad_variant      = $attributes['variant'] ?? 'hero';

$aiad_more = static function () use ( $aiad_nominate_url, $aiad_sign_in_url ): void {
	if ( ! $aiad_nominate_url && ! $aiad_sign_in_url ) {
		return;
	}
	echo '<p class="ncp-more">';
	if ( $aiad_nominate_url ) {
		echo '<a href="' . esc_url( $aiad_nominate_url ) . '">' . esc_html__( 'Nominate a school you work with', 'aiad-core' ) . '</a>';
	}
	if ( $aiad_sign_in_url ) {
		echo '<a href="' . esc_url( $aiad_sign_in_url ) . '">' . esc_html__( 'Already registered? Sign in', 'aiad-core' ) . '</a>';
	}
	echo '</p>';
};

if ( 'nominate' === $aiad_variant ) {
	if ( $aiad_nominate_url ) {
		echo '<p><a class="ncp-link" href="' . esc_url( $aiad_nominate_url ) . '">' . esc_html__( 'Work with a school? Nominate it', 'aiad-core' ) . '</a></p>';
	}
	return;
}

if ( 'cta' === $aiad_variant ) {
	if ( $aiad_register_url ) {
		echo '<p>' . esc_html__( 'It takes a few minutes, and your headteacher approves with one click.', 'aiad-core' ) . '</p>';
		echo '<p class="ncp-actions"><a class="ncp-btn ncp-btn--primary" href="' . esc_url( $aiad_register_url ) . '">' . esc_html__( 'Register your school', 'aiad-core' ) . '</a></p>';
		$aiad_more();
		return;
	}
	/* translators: %s: opening date */
	echo '<p>' . sprintf( esc_html__( 'Schools can register from %s. Until then, see how ready your school is for AI.', 'aiad-core' ), esc_html( $aiad_opens_long ) ) . '</p>';
	if ( $aiad_readiness ) {
		echo '<p class="ncp-actions"><a class="ncp-btn ncp-btn--primary" href="' . esc_url( $aiad_readiness ) . '">' . esc_html__( 'Check your AI readiness', 'aiad-core' ) . '</a></p>';
	}
	return;
}

echo '<p class="ncp-actions">';
if ( $aiad_register_url ) {
	echo '<a class="ncp-btn ncp-btn--primary" href="' . esc_url( $aiad_register_url ) . '">' . esc_html__( 'Register your school', 'aiad-core' ) . '</a>';
} else {
	/* translators: %s: opening date */
	echo '<span class="ncp-btn ncp-btn--soon">' . sprintf( esc_html__( 'Schools can register from %s', 'aiad-core' ), esc_html( $aiad_opens_long ) ) . '</span>';
}
if ( $aiad_readiness ) {
	echo '<a class="ncp-btn ncp-btn--ghost" href="' . esc_url( $aiad_readiness ) . '">' . esc_html__( 'Check your AI readiness', 'aiad-core' ) . '</a>';
}
echo '</p>';
$aiad_more();
