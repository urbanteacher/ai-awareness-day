<?php
/**
 * /conversation/snapshot/  the School AI Snapshot: what a school's students think about AI, and what the
 * school has done with it, on one printable page. Section 12 of the brief: the return a school gets for
 * asking its students.
 *
 * It is a page to print or save as PDF from the browser, in the same way as the certificate and the
 * paper scorecard, so it needs no PDF library on the server.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Snapshot_Front {

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	public static function view_snapshot(): string {
		AIADN_Front::set_title( 'School AI Snapshot' );
		$session = AIADN_Auth::current();
		if ( ! $session || ! in_array( $session['role'], array( 'lead', 'teacher', 'slt' ), true ) || 'approved' !== $session['school']['status'] ) {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}
		$school = $session['school'];
		$sid    = (int) $school['id'];

		$count   = AIADN_Voice::count( $sid );
		$results = AIADN_Voice::results( $sid );
		$sum     = AIADN_Results::summary( $sid );
		$cert    = AIADN_Certificates::get_for_school( $sid );
		$prog    = AIADN_Certificates::progress( $sid );

		$themes = array();
		foreach ( $sum['rows'] as $r ) {
			$themes[ $r['debate']['theme'] ] = true;
		}

		$h  = '<h1>School AI Snapshot</h1>';
		$h .= '<p class="aiadn__small aiadn__noprint"><a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">&larr; Your school</a></p>';
		$h .= '<div class="aiadn__snapshot">';
		$h .= '<div class="aiadn__paper-logo">' . AIADN_Front::logo_html() . '</div>';
		$h .= '<p class="aiadn__eyebrow aiadn__printonly">School AI Snapshot</p><h2>' . self::esc( $school['name'] ) . '</h2>';
		$h .= '<p class="aiadn__small">School code ' . self::esc( $school['code'] ) . ' &middot; Prepared ' . self::esc( wp_date( 'j F Y' ) ) . '</p>';

		// The headline numbers.
		$h .= '<div class="aiadn__stats">';
		foreach ( array( 'Student answers' => $count, 'Debates completed' => $sum['count'], 'Students in debates' => $sum['students'], 'Schools debated' => $sum['schools'], 'Themes explored' => count( $themes ) ) as $label => $n ) {
			$h .= '<div class="aiadn__stat"><strong>' . (int) $n . '</strong><span>' . esc_html( $label ) . '</span></div>';
		}
		$h .= '</div>';

		// What students think.
		$h .= '<h3>What your students think</h3>';
		if ( ! $results ) {
			$h .= '<p>' . (int) $count . ' of ' . (int) AIADN_Voice::MIN_RESPONSES . ' students have answered so far. Results stay hidden until there are enough that no individual can be picked out. Start a class PIN from your school page to collect more.</p>';
		} else {
			$h .= '<p class="aiadn__small">Based on ' . (int) $count . ' anonymous answers from your students. Agree, not sure and disagree, as a share of those who answered each question.</p>';
			foreach ( AIADN_Voice::QUESTIONS as $key => $q ) {
				$r = $results[ $key ] ?? null;
				if ( ! $r ) {
					continue;
				}
				$h .= '<div class="aiadn__vq aiadn__q--' . esc_attr( $q['theme'] ) . '">' . AIADN_Voice_Front::tile( $q['theme'] ) . '<div><p><strong>' . esc_html( $q['label'] ) . '</strong> &ldquo;' . esc_html( $q['text'] ) . '&rdquo;</p>' . AIADN_Voice_Front::stack( $r ) . '<p class="aiadn__small">Agree ' . (int) $r['agree'] . '% &middot; Not sure ' . (int) $r['unsure'] . '% &middot; Disagree ' . (int) $r['disagree'] . '%</p></div></div>';
			}
			$split = AIADN_Voice::biggest_split( $results );
			if ( $split ) {
				$h .= '<p><strong>Biggest split:</strong> ' . esc_html( AIADN_Voice::QUESTIONS[ $split ]['label'] ) . '. Students disagree with each other most here, which makes it a good topic for a debate or a form-time discussion.</p>';
			}
		}

		// Before and after a debate.
		$movement = AIADN_Voice::movement( $sid );
		if ( $movement ) {
			$h .= '<h3>Before and after a debate</h3><ul class="aiadn__list">';
			foreach ( $movement as $m ) {
				foreach ( AIADN_Voice::QUESTIONS as $key => $q ) {
					if ( empty( $m['before'][ $key ] ) || empty( $m['after'][ $key ] ) ) {
						continue;
					}
					$change = $m['after'][ $key ]['agree'] - $m['before'][ $key ]['agree'];
					$h     .= '<li>' . esc_html( $q['label'] ) . ' (' . self::esc( $m['debate']['code'] ) . '): agree ' . (int) $m['before'][ $key ]['agree'] . '% &rarr; ' . (int) $m['after'][ $key ]['agree'] . '% (' . ( $change >= 0 ? '+' : '' ) . (int) $change . ')</li>';
				}
			}
			$h .= '</ul>';
		}

		// What the school has done.
		$h .= '<h3>What your school has done</h3>';
		if ( $sum['count'] ) {
			$names = array();
			foreach ( array_keys( $themes ) as $t ) {
				$names[] = AIADN_Motions::THEMES[ $t ] ?? '';
			}
			$h .= '<p>' . (int) $sum['count'] . ' debate' . ( 1 === $sum['count'] ? '' : 's' ) . ' judged, against ' . (int) $sum['schools'] . ' different school' . ( 1 === $sum['schools'] ? '' : 's' ) . ', on ' . esc_html( implode( ', ', $names ) ) . '.</p>';
		} else {
			$h .= '<p>No debate has been judged yet.</p>';
		}
		$h .= '<p>' . ( $cert && 'issued' === $cert['status']
			? 'National AI Debate School certificate <strong>earned</strong> (reference ' . self::esc( $cert['reference'] ) . ').'
			: 'Certificate progress: ' . (int) $prog['have'] . ' of ' . (int) $prog['need'] . ' debates against different schools.' ) . '</p>';

		// What to do with it.
		$h .= '<h3>Ways to use this</h3><ul class="aiadn__list"><li>Curriculum: take the biggest split into PSHE, computing or citizenship.</li><li>AI strategy and staff development: show what students actually say about AI, not what adults assume.</li><li>Governors: report participation and what students told you.</li><li>Student voice: share it back, and act on one thing.</li></ul>';

		$h .= '<p class="aiadn__small">These are your students&rsquo; views at your school, from the students who answered. They are not a national sample. The statements are drafts and will be reviewed before national use.</p>';
		$h .= '</div>';
		$h .= '<p class="aiadn__noprint"><button class="aiadn__button" type="button" onclick="window.print()">Print or save as PDF</button></p>';
		return $h;
	}
}
