<?php
/**
 * Homepage promo for the AI Literacy Periodic Table (the aiad-core plugin's literacy-table module), after the five themes: the table can
 * be coloured by them. Printed after the Principles section (a filter in inc/literacy-table-page.php) once the table's page
 * exists. It uses the benchmark promo's
 * layout and styles (assets/css/components/benchmark-promo.css), with a row of sample tiles.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}
if ( ! function_exists( 'aiad_literacy_table_url' ) || ! function_exists( 'aiad_editable_page_post' ) || ! aiad_editable_page_post( 'ai-literacy-periodic-table' ) ) {
	return;
}

$aiad_table_url = aiad_literacy_table_url();
$aiad_count     = count( aiad_literacy_elements() );
$aiad_samples   = array(
	array( 'Ai', __( 'Artificial Intelligence', 'ai-awareness-day' ), 'foundations' ),
	array( 'Ll', __( 'Large Language Model', 'ai-awareness-day' ), 'generative' ),
	array( 'Dk', __( 'Deepfake', 'ai-awareness-day' ), 'generative' ),
	array( 'Ag', __( 'AI Agent', 'ai-awareness-day' ), 'agents' ),
	array( 'Hi', __( 'Human-in-the-Loop', 'ai-awareness-day' ), 'governance' ),
);
?>
<div id="literacy-table" class="literacy-table-promo fade-up" role="region" aria-labelledby="literacy-table-promo-title">
	<div class="literacy-table-promo__content">
		<p class="benchmark-promo__eyebrow">
			<span class="benchmark-promo__dot" aria-hidden="true"></span>
			<?php esc_html_e( 'New · For teachers and students', 'ai-awareness-day' ); ?>
		</p>
		<h3 class="benchmark-promo__title literacy-table-promo__title" id="literacy-table-promo-title">
			<?php esc_html_e( 'The AI Literacy Periodic Table', 'ai-awareness-day' ); ?>
		</h3>
		<p class="benchmark-promo__desc literacy-table-promo__desc">
			<?php
			/* translators: %d: number of elements */
			echo esc_html( sprintf( __( '%d AI terms in plain English, with the issues they raise, a question to talk about for every one, compounds to discover, ready-made sessions and a poster to print for your display board.', 'ai-awareness-day' ), $aiad_count ) );
			?>
		</p>
		<?php $aiad_week = aiad_literacy_element_of_week(); ?>
		<p class="literacy-table-promo__week">
			<span class="literacy-table-promo__week-label"><?php esc_html_e( 'Word of the week:', 'ai-awareness-day' ); ?></span>
			<a href="<?php echo esc_url( $aiad_table_url . '#element-' . strtolower( $aiad_week['el']['sym'] ) ); ?>"><?php echo esc_html( $aiad_week['el']['name'] ); ?></a>.
			<?php echo esc_html( $aiad_week['talk'] ); ?>
		</p>
		<ul class="literacy-table-promo__tiles" aria-hidden="true">
			<?php foreach ( $aiad_samples as $sample ) : ?>
				<li class="literacy-table-promo__tile literacy-table-promo__tile--<?php echo esc_attr( $sample[2] ); ?>">
					<span class="literacy-table-promo__sym"><?php echo esc_html( $sample[0] ); ?></span>
					<span class="literacy-table-promo__name"><?php echo esc_html( $sample[1] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="literacy-table-promo__actions">
		<a class="literacy-table-promo__btn" href="<?php echo esc_url( $aiad_table_url ); ?>"><?php esc_html_e( 'Explore the table', 'ai-awareness-day' ); ?></a>
		<p class="literacy-table-promo__more">
			<a href="<?php echo esc_url( add_query_arg( 'view', 'themes', $aiad_table_url ) ); ?>"><?php esc_html_e( 'See it in AIAD terms: the five themes', 'ai-awareness-day' ); ?></a>
		</p>
	</div>
</div>
