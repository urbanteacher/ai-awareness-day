<?php
/**
 * The motion bank: two motions for each theme and age pathway.
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

	/** @return array<string,array{theme:string,age:string,text:string}> keyed by motion key */
	public static function all(): array {
		static $bank = null;
		if ( null !== $bank ) {
			return $bank;
		}
		$raw = array(
			'primary'   => array(
				'safe'        => array( 'Children should be allowed to tell an AI their secrets.', 'AI should never be allowed to remember what we say to it.' ),
				'smart'       => array( 'AI should be allowed to help with homework.', 'Learning to spell matters less now that AI can check it for us.' ),
				'creative'    => array( 'AI art should win prizes.', "A story written with AI is still the writer's own story." ),
				'responsible' => array( 'If an AI gets it wrong, the person who used it is to blame.', 'Schools should teach children when NOT to use AI.', 'It is OK for AI to use a lot of electricity and water.' ),
				'future'      => array( 'Robots will do most jobs by the time we grow up.', 'Children should have a say in how AI is used in schools.' ),
			),
			'secondary' => array(
				'safe'        => array( "AI companions are a risk to teenagers' wellbeing.", 'Schools should ban AI tools that collect student data.' ),
				'smart'       => array( 'Using AI for revision makes students worse at thinking for themselves.', 'AI detectors should decide whether coursework is genuine.' ),
				'creative'    => array( 'Work made with AI should never win creative competitions.', 'AI-generated music should be labelled on every platform.' ),
				'responsible' => array( 'Companies, not users, should be responsible for what AI produces.', 'Students who use AI to cheat should face the same penalty as those who copy.', 'The energy and water AI uses are too high a price for the benefits.' ),
				'future'      => array( 'AI will create more jobs for young people than it destroys.', 'Young people should have a vote on how AI is regulated.' ),
			),
			'post16'    => array(
				'safe'        => array( 'Facial recognition should be banned in public spaces.', 'Personal data used to train AI should require opt-in consent.' ),
				'smart'       => array( 'Universities should assess students without access to AI.', 'Critical thinking cannot be taught alongside AI tools.' ),
				'creative'    => array( 'AI cannot be an author or an artist.', 'Creators should be paid when AI learns from their work.' ),
				'responsible' => array( 'Governments should require AI systems to be explainable.', 'AI developers should be legally liable for foreseeable harm.', 'AI companies should have to publish, and cap, the energy and water they use.' ),
				'future'      => array( 'Human workers should have the right to refuse AI management.', 'A universal basic income is the only fair answer to AI automation.' ),
			),
		);
		$bank = array();
		foreach ( $raw as $age => $themes ) {
			foreach ( $themes as $theme => $motions ) {
				foreach ( $motions as $i => $text ) {
					$bank[ $age . '-' . $theme . '-' . ( $i + 1 ) ] = array( 'theme' => $theme, 'age' => $age, 'text' => $text );
				}
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
