<?php
/**
 * The motion bank: one item for each theme and age pathway.
 *
 * Kept in code for now so it is versioned with the plugin. A schools-facing editor can replace this later.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Motions {

	const THEMES = array(
		'safe'        => 'SAFE',
		'smart'       => 'SMART',
		'creative'    => 'CREATIVE',
		'responsible' => 'RESPONSIBLE',
		'future'      => 'FUTURE',
	);

	const AGES = array(
		'primary'   => 'Primary',
		'secondary' => 'Secondary',
		'post16'    => 'Post-16',
	);

	const FORMATS = array(
		'in_person' => 'In person',
		'online'    => 'Online',
	);

	const JUDGE_TYPES = array(
		'academic'  => 'Academic',
		'industry'  => 'Industry',
		'community' => 'Community',
		'teacher'   => 'Teacher',
	);

	/**
	 * What each age pathway is asked, and the help that goes with it. One item for each theme and age.
	 * The text is the motion the two schools argue; the rest is prompting for the students' preparation.
	 *
	 * Primary: a scenario with clear options, and a sentence starter.
	 * Secondary: an open question, and a "What if...?" challenge card.
	 * Post-16: a formal motion, its central tension and a research prompt.
	 *
	 * These are drafts, for review by debate educators before national use.
	 *
	 * @return array<string,array{theme:string,age:string,text:string,starter?:string,challenge?:string,tension?:string,research?:string}> keyed by motion key
	 */
	public static function all(): array {
		static $bank = null;
		if ( null !== $bank ) {
			return $bank;
		}
		$raw = array(
			'primary'   => array(
				'safe'        => array( 'text' => 'Your AI helper can remember things about you. Should it remember everything, some things or nothing?', 'starter' => 'I think it should remember ___ because ___.' ),
				'smart'       => array( 'text' => "You're stuck on your homework. Would you ask AI for the answer, a clue or a question to help you think?", 'starter' => 'I would ask for ___ because ___.' ),
				'creative'    => array( 'text' => 'AI helps you make a song. Which part would you want to do yourself?', 'starter' => "The part I'd want to do myself is ___ because ___." ),
				'responsible' => array( 'text' => 'Every time you ask AI something, it uses electricity and water. Should the app tell you how much?', 'starter' => "I think it should / shouldn't because ___." ),
				'future'      => array( 'text' => 'If AI could do some of your dream job, what would you still want to learn to do yourself?', 'starter' => "I'd still want to learn ___ because ___." ),
			),
			'secondary' => array(
				'safe'        => array( 'text' => 'An AI assistant remembers your conversations to give you more personal support. How much should it remember, and who should decide?', 'challenge' => 'What if remembering more helped it notice when you were struggling?' ),
				'smart'       => array( 'text' => 'If an AI sounds confident, how would you check whether it is right?', 'challenge' => 'What if checking takes longer than working it out yourself?' ),
				'creative'    => array( 'text' => 'If you loved a song and then discovered it had been generated with AI, would it mean something different to you?', 'challenge' => 'Does it matter if nobody can tell the difference?' ),
				'responsible' => array( 'text' => 'If a tool could imitate your artwork or voice, what permission or recognition would you expect?', 'challenge' => 'What if the copy was just for fun and never sold?' ),
				'future'      => array( 'text' => 'If AI helped you finish work more quickly, what would you do with the time you saved?', 'challenge' => 'What if your school or employer decided how that time was used instead?' ),
			),
			'post16'    => array(
				'safe'        => array( 'text' => 'This house believes users, not companies, should control what AI remembers about them.', 'tension' => 'Personalisation vs privacy', 'research' => "How does the ICO's Children's Code treat data about young people?" ),
				'smart'       => array( 'text' => 'This house would require AI tools in schools to guide students rather than give answers.', 'tension' => 'Efficiency vs learning', 'research' => 'What does the evidence say about AI tutoring and learning outcomes?' ),
				'creative'    => array( 'text' => 'This house believes AI-generated work should not be eligible for creative prizes.', 'tension' => 'Tool vs author', 'research' => 'How is the UK debating copyright and AI training?' ),
				'responsible' => array( 'text' => 'This house would require AI companies to show the environmental cost of every request.', 'tension' => 'Transparency vs practicality', 'research' => "How reliable are current estimates of AI's energy and water use?" ),
				'future'      => array( 'text' => 'This house believes young people should have a formal say in how AI is used in schools.', 'tension' => 'Expertise vs representation', 'research' => 'How do youth councils and student voice bodies influence decisions now?' ),
			),
		);
		$bank = array();
		foreach ( $raw as $age => $themes ) {
			foreach ( $themes as $theme => $item ) {
				$bank[ $age . '-' . $theme . '-1' ] = array_merge( array( 'theme' => $theme, 'age' => $age ), $item );
			}
		}
		return $bank;
	}

	/** The motion for a key, only if it belongs to the chosen theme and age group. */
	public static function find( string $key, string $theme, string $age ): ?array {
		$all = self::all();
		if ( ! isset( $all[ $key ] ) || $all[ $key ]['theme'] !== $theme || $all[ $key ]['age'] !== $age ) {
			return null;
		}
		return $all[ $key ];
	}
}
