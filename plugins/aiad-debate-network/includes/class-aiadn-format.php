<?php
/**
 * The shape of a debate: 30 minutes on the day, and the preparation in the weeks before.
 *
 * Ten minutes of start-up (welcome, opening vote, survey findings) and twenty of debate. The minutes
 * change with the age pathway; the total never does. Kept in code so it is versioned with the plugin.
 * The stages, timings and wording are a draft for review by debate educators before national use.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Format {

	/** Every age pathway adds up to this. */
	const TOTAL_MINUTES = 30;

	/** Minutes at the start that are set-up, not debate. */
	const START_UP_MINUTES = 10;

	/**
	 * Minutes are per side where 'each' is true. 'alt' replaces the wording for one age pathway.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private static function definitions(): array {
		return array(
			array(
				'key'   => 'welcome',
				'label' => 'Welcome and roles',
				'mins'  => array( 'primary' => 3, 'secondary' => 2, 'post16' => 2 ),
				'each'  => false,
				'what'  => 'The chair introduces the motion and the rules, and confirms who is speaking, taking notes and asking questions.',
			),
			array(
				'key'   => 'open_vote',
				'label' => 'Opening vote',
				'mins'  => array( 'primary' => 3, 'secondary' => 2, 'post16' => 2 ),
				'each'  => false,
				'what'  => 'The room votes for, against or undecided, and the result is recorded.',
				'alt'   => array( 'primary' => 'The room votes for, against or undecided by moving to a corner of the room. The result is recorded.' ),
			),
			array(
				'key'   => 'survey',
				'label' => 'Survey findings',
				'mins'  => array( 'primary' => 2, 'secondary' => 3, 'post16' => 3 ),
				'each'  => true,
				'what'  => 'Each school shares what its own students said: a simple chart and one key finding.',
			),
			array(
				'key'   => 'opening',
				'label' => 'Opening cases',
				'mins'  => array( 'primary' => 2, 'secondary' => 3, 'post16' => 3.5 ),
				'each'  => true,
				'what'  => 'Proposition, then Opposition. The other side takes notes.',
				'alt'   => array( 'post16' => 'Proposition, then Opposition. The other side takes notes and may offer points of information in the middle of each speech.' ),
			),
			array(
				'key'   => 'prep',
				'label' => 'Rebuttal preparation',
				'mins'  => array( 'primary' => 3, 'secondary' => 2, 'post16' => 2 ),
				'each'  => false,
				'what'  => 'Each team huddles with its note-takers, while the audience talks in pairs.',
			),
			array(
				'key'   => 'rebuttal',
				'label' => 'Rebuttal',
				'mins'  => array( 'primary' => 1, 'secondary' => 2, 'post16' => 2 ),
				'each'  => true,
				'what'  => 'Opposition goes first, then Proposition.',
			),
			array(
				'key'   => 'floor',
				'label' => 'Floor questions',
				'mins'  => array( 'primary' => 4, 'secondary' => 3, 'post16' => 3 ),
				'each'  => false,
				'what'  => 'The chair takes questions from the audience, to either side.',
			),
			array(
				'key'   => 'closing',
				'label' => 'Closing',
				'mins'  => array( 'primary' => 1, 'secondary' => 1, 'post16' => 1.5 ),
				'each'  => true,
				'what'  => 'Opposition first, Proposition last.',
			),
			array(
				'key'   => 'buffer',
				'label' => 'Changeover buffer',
				'mins'  => array( 'primary' => 2, 'secondary' => 1, 'post16' => 0 ),
				'each'  => false,
				'what'  => 'Covers any overrun.',
			),
			array(
				'key'   => 'final_vote',
				'label' => 'Final vote and result',
				'mins'  => array( 'primary' => 3, 'secondary' => 2, 'post16' => 1 ),
				'each'  => false,
				'what'  => 'The room votes again, and the chair announces the swing: who changed their minds.',
				'alt'   => array( 'post16' => 'A show of hands or a digital vote. The chair announces the swing: who changed their minds.' ),
			),
		);
	}

	/** An unknown or missing age pathway is treated as Secondary. */
	public static function age( string $age ): string {
		return isset( AIADN_Motions::AGES[ $age ] ) ? $age : 'secondary';
	}

	/**
	 * The running order for an age pathway, with each stage's start and end in seconds from the first word.
	 *
	 * @return array<int,array{key:string,label:string,what:string,minutes:float,each:bool,total:float,start:int,end:int,part:string}>
	 */
	public static function run( string $age ): array {
		$age    = self::age( $age );
		$offset = 0;
		$out    = array();
		foreach ( self::definitions() as $def ) {
			$minutes = (float) $def['mins'][ $age ];
			if ( $minutes <= 0 ) {
				continue;
			}
			$total = $def['each'] ? $minutes * 2 : $minutes;
			$out[] = array(
				'key'     => $def['key'],
				'label'   => $def['label'],
				'what'    => $def['alt'][ $age ] ?? $def['what'],
				'minutes' => $minutes,
				'each'    => (bool) $def['each'],
				'total'   => $total,
				'start'   => $offset,
				'end'     => $offset + (int) round( $total * 60 ),
				'part'    => $offset < self::START_UP_MINUTES * 60 ? 'start' : 'debate',
			);
			$offset += (int) round( $total * 60 );
		}
		return $out;
	}

	/** '3 min', '3½ min', '2 min each'. */
	public static function minutes_label( float $minutes, bool $each ): string {
		$whole = (int) floor( $minutes );
		$half  = ( $minutes - $whole ) >= 0.5;
		$text  = ( $whole > 0 ? $whole : '' ) . ( $half ? '&frac12;' : '' );
		return $text . ' min' . ( $each ? ' each' : '' );
	}

	/** Seconds from the start as m:ss. */
	public static function clock( int $seconds ): string {
		return sprintf( '%d:%02d', intdiv( $seconds, 60 ), $seconds % 60 );
	}

	/** The wall-clock time a stage starts, from the debate's start (a UTC time) and an offset. */
	public static function clock_time( string $starts_at_utc, int $seconds ): string {
		return AIADN_Util::show( gmdate( 'Y-m-d H:i:s', strtotime( $starts_at_utc . ' UTC' ) + $seconds ), 'H:i' );
	}

	/**
	 * How each school gathers its own students' views and researches the motion, in the weeks before.
	 *
	 * @return array{survey:string,research:string,sources:string}
	 */
	public static function before( string $age ): array {
		$age = self::age( $age );
		$all = array(
			'primary'   => array(
				'survey'   => 'A class vote or tally chart, with the teacher helping. Ask the same few questions in every class that takes part.',
				'research' => 'Work from short fact cards, one fact to a card.',
				'sources'  => 'Fact cards',
			),
			'secondary' => array(
				'survey'   => 'A short online form, sent to a year group.',
				'research' => 'Use two or three sources you are given, plus one more that students find for themselves.',
				'sources'  => 'Given sources plus one of their own',
			),
			'post16'    => array(
				'survey'   => 'Students write their own questions, starting from the ones below, and note the limits of their survey: sample size and leading wording.',
				'research' => 'Independent research, with a note of how reliable each source is.',
				'sources'  => 'Independent',
			),
		);
		return $all[ $age ];
	}

	/** The three research groups every team splits into. */
	const RESEARCH_GROUPS = array(
		'The facts'                    => 'What is actually true, and how do we know?',
		'The people affected'          => 'Who gains, who loses, and who is not in the room?',
		'Rules and responsibilities'   => 'Who decides, who is accountable, and what should the rules be?',
	);

	/**
	 * The questions both schools ask their own students, so the results can be compared on the day.
	 * The motion itself, the Student Voice statement for the theme, and the reason.
	 *
	 * @return array<int,array{text:string,answers:string}>
	 */
	public static function survey_questions( array $debate ): array {
		$scale = implode( ' / ', AIADN_Voice::ANSWERS );
		$out   = array(
			array( 'text' => 'Do you agree with this? "' . $debate['motion_text'] . '"', 'answers' => $scale ),
		);
		foreach ( AIADN_Voice::QUESTIONS as $q ) {
			if ( $q['theme'] === $debate['theme'] ) {
				$out[] = array( 'text' => $q['text'], 'answers' => $scale );
			}
		}
		$out[] = array( 'text' => 'What is the strongest reason for your answer?', 'answers' => 'One sentence' );
		return $out;
	}

	/** The named roles each school fields. Everyone else has one of the last two jobs. */
	const ROLES = array(
		'Survey presenters (2)' => 'Share the school\'s survey findings at the start.',
		'Opening speaker (1)'   => 'Makes the school\'s case.',
		'Rebuttal speaker (1)'  => 'Answers what the other side said, using the notes.',
		'Closing speaker (1)'   => 'Sums up in the school\'s last minute.',
		'Lead note-taker (1)'   => 'Listens to the other side and briefs the rebuttal speaker.',
		'Note-takers'           => 'Listen to the other side\'s opening case, and feed the rebuttal in the huddle.',
		'Questioners'           => 'Ask the floor questions to either side.',
	);

	/**
	 * A school's own to-do list for the debate. Ticks are kept with the debate, for that school only.
	 * 'when' groups the list; 'in_person' items are left out of an online debate.
	 */
	const CHECKLIST = array(
		'prep_sides'        => array( 'when' => 'before', 'text' => 'Our students know which side we argue.' ),
		'prep_roles'        => array( 'when' => 'before', 'text' => 'We have named our roles: two survey presenters, an opening speaker, a rebuttal speaker, a closing speaker and a lead note-taker.' ),
		'prep_survey_how'   => array( 'when' => 'before', 'text' => 'We have chosen how our students complete the survey: a class vote, an online form, or Student Voice on the whiteboard.' ),
		'prep_survey_done'  => array( 'when' => 'before', 'text' => 'Our students have completed the survey, and we have a simple chart and one key finding to share.' ),
		'prep_groups'       => array( 'when' => 'before', 'text' => 'Our research groups are set: the facts, the people affected, and rules and responsibilities.' ),
		'prep_sources'      => array( 'when' => 'before', 'text' => 'Every fact we plan to use has more than one source, and none rests only on AI-generated content.' ),
		'prep_order'        => array( 'when' => 'day', 'text' => 'We have decided which running order to use, or how we will adapt it.' ),
		'prep_chair'        => array( 'when' => 'day', 'text' => 'We have agreed who the chair is, and have a timer everyone can see.' ),
		'prep_paper'        => array( 'when' => 'day', 'text' => 'A printed scorecard is ready for the judge, if they would rather score on paper.', 'in_person' => true ),
	);

	/** @return array<string,array{when:string,text:string}> the items that apply to this format */
	public static function checklist_for( string $format ): array {
		return array_filter( self::CHECKLIST, static fn( $item ) => empty( $item['in_person'] ) || 'in_person' === $format );
	}

	/** The compact one-paragraph version for the printed scorecard. */
	public static function one_line( string $age, string $starts_at_utc ): string {
		$parts = array();
		foreach ( self::run( $age ) as $s ) {
			$parts[] = $s['label'] . ' ' . self::clock_time( $starts_at_utc, $s['start'] );
		}
		return implode( ' · ', $parts );
	}
}
