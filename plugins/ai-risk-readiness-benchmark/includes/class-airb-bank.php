<?php
/**
 * Reader for the shared AiAd question bank format (`aiad.questions/1`).
 *
 * The PHP counterpart of the app's src/data/bank.ts. Both read the same bank
 * files, so question text, scoring and branching live in the bank rather than
 * in either codebase.
 *
 * The two implementations MUST agree. Two readers that score the same bank
 * differently are worse than one reader, so tools/bank-parity.php checks them
 * against each other.
 *
 * @see includes/data/schema/aiad-questions.v1.md
 * @package AI_Risk_Readiness_Benchmark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIRB_Bank {

	private const SCHEMA = 'aiad.questions/1';

	/** @var array<string, mixed> */
	private array $data;

	/**
	 * @param array<string, mixed> $data Decoded bank.
	 * @throws InvalidArgumentException When the bank is unusable.
	 */
	public function __construct( array $data ) {
		$schema = (string) ( $data['schema'] ?? '' );
		if ( self::SCHEMA !== $schema ) {
			throw new InvalidArgumentException( 'Unsupported bank schema: ' . ( '' === $schema ? 'missing' : $schema ) );
		}
		$questions = $data['questions'] ?? null;
		if ( ! is_array( $questions ) || array() === $questions ) {
			throw new InvalidArgumentException( 'Bank "' . (string) ( $data['bank'] ?? '?' ) . '" has no questions' );
		}

		$ids = array();
		foreach ( $questions as $question ) {
			$id = (string) ( $question['id'] ?? '' );
			if ( '' === $id || isset( $ids[ $id ] ) ) {
				throw new InvalidArgumentException( 'Duplicate or missing question id: ' . $id );
			}
			$ids[ $id ] = true;
		}
		foreach ( (array) ( $data['scenarios'] ?? array() ) as $scenario ) {
			foreach ( (array) ( $scenario['questionIds'] ?? array() ) as $ref ) {
				if ( ! isset( $ids[ (string) $ref ] ) ) {
					throw new InvalidArgumentException(
						'Scenario ' . (string) ( $scenario['id'] ?? '?' ) . ' references unknown question ' . (string) $ref
					);
				}
			}
		}

		$this->data = $data;
	}

	/**
	 * Load a bank by id from includes/data/banks.
	 *
	 * @throws RuntimeException When the file is missing or not valid JSON.
	 */
	public static function load( string $bank_id ): self {
		$path = AIRB_PLUGIN_DIR . 'includes/data/banks/' . sanitize_file_name( $bank_id ) . '.json';
		if ( ! file_exists( $path ) ) {
			throw new RuntimeException( 'Bank file not found: ' . $path );
		}
		$decoded = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_array( $decoded ) ) {
			throw new RuntimeException( 'Bank file is not valid JSON: ' . $path );
		}
		return new self( $decoded );
	}

	public function id(): string {
		return (string) ( $this->data['bank'] ?? '' );
	}

	public function pass_mark(): int {
		return (int) ( $this->data['passMark'] ?? 70 );
	}

	/** @return array<int, array<string, mixed>> */
	public function questions(): array {
		return (array) ( $this->data['questions'] ?? array() );
	}

	/** @return array<int, array<string, mixed>> */
	public function scenarios(): array {
		return (array) ( $this->data['scenarios'] ?? array() );
	}

	/**
	 * Evaluate a visibility condition.
	 *
	 * Deliberately a condition object rather than an expression string: an
	 * expression language would need a parser here and in TypeScript, kept in
	 * step. This needs neither, and cannot execute anything.
	 *
	 * @param array<string, mixed>|null    $condition Condition, or null for always visible.
	 * @param array<string, string>        $answers   question id => chosen value.
	 * @param array<string, string>        $profile   profile field => value.
	 */
	public static function is_visible( ?array $condition, array $answers, array $profile = array() ): bool {
		if ( empty( $condition ) ) {
			return true;
		}
		if ( isset( $condition['allOf'] ) && is_array( $condition['allOf'] ) ) {
			foreach ( $condition['allOf'] as $child ) {
				if ( ! self::is_visible( (array) $child, $answers, $profile ) ) {
					return false;
				}
			}
			return true;
		}
		if ( isset( $condition['anyOf'] ) && is_array( $condition['anyOf'] ) ) {
			foreach ( $condition['anyOf'] as $child ) {
				if ( self::is_visible( (array) $child, $answers, $profile ) ) {
					return true;
				}
			}
			return false;
		}
		if ( isset( $condition['not'] ) && is_array( $condition['not'] ) ) {
			return ! self::is_visible( (array) $condition['not'], $answers, $profile );
		}
		if ( isset( $condition['question'] ) ) {
			return self::leaf_passes( $answers[ (string) $condition['question'] ] ?? '', $condition );
		}
		if ( isset( $condition['profile'] ) ) {
			return self::leaf_passes( $profile[ (string) $condition['profile'] ] ?? '', $condition );
		}
		return true;
	}

	/** @param array<string, mixed> $test */
	private static function leaf_passes( string $value, array $test ): bool {
		if ( array_key_exists( 'answered', $test ) ) {
			return $test['answered'] ? '' !== $value : '' === $value;
		}
		/* An unanswered dependency passes: a question stays visible until its
		   dependency actually rules it out. */
		if ( '' === $value ) {
			return true;
		}
		if ( isset( $test['in'] ) && is_array( $test['in'] ) ) {
			return in_array( $value, array_map( 'strval', $test['in'] ), true );
		}
		if ( isset( $test['notIn'] ) && is_array( $test['notIn'] ) ) {
			return ! in_array( $value, array_map( 'strval', $test['notIn'] ), true );
		}
		return true;
	}

	/**
	 * @param array<string, string> $answers
	 * @param array<string, string> $profile
	 * @return array<int, array<string, mixed>>
	 */
	public function visible_questions( array $answers, array $profile = array() ): array {
		return array_values(
			array_filter(
				$this->questions(),
				static function ( $question ) use ( $answers, $profile ) {
					$condition = isset( $question['visible'] ) ? (array) $question['visible'] : null;
					return self::is_visible( $condition, $answers, $profile );
				}
			)
		);
	}

	/**
	 * Questions grouped under their vignette, in bank order.
	 *
	 * @param array<string, string> $answers
	 * @param array<string, string> $profile
	 * @return array<int, array{scenario: array<string, mixed>|null, questions: array<int, array<string, mixed>>}>
	 */
	public function clusters( array $answers = array(), array $profile = array() ): array {
		$out   = array();
		$index = array();

		foreach ( $this->visible_questions( $answers, $profile ) as $question ) {
			$scenario_id = (string) ( $question['scenario'] ?? '' );
			if ( '' === $scenario_id ) {
				$out[] = array(
					'scenario'  => null,
					'questions' => array( $question ),
				);
				continue;
			}
			if ( ! isset( $index[ $scenario_id ] ) ) {
				$scenario = null;
				foreach ( $this->scenarios() as $candidate ) {
					if ( (string) ( $candidate['id'] ?? '' ) === $scenario_id ) {
						$scenario = $candidate;
						break;
					}
				}
				$out[]                     = array(
					'scenario'  => $scenario,
					'questions' => array(),
				);
				$index[ $scenario_id ]     = count( $out ) - 1;
			}
			$out[ $index[ $scenario_id ] ]['questions'][] = $question;
		}

		return $out;
	}

	/**
	 * 0-100 readiness.
	 *
	 * `score` on an option is a risk weight where 0 is the strongest answer,
	 * so the result is inverted. Unanswered counts as the worst option, which
	 * is what makes a partial run score low rather than undefined.
	 *
	 * @param array<string, string> $answers
	 * @return array{score: int, answered: int, total: int}
	 */
	public function score( array $answers ): array {
		$visible  = $this->visible_questions( $answers );
		$earned   = 0;
		$worst    = 0;
		$answered = 0;

		foreach ( $visible as $question ) {
			$options = (array) ( $question['options'] ?? array() );
			$max     = 0;
			foreach ( $options as $option ) {
				$max = max( $max, (int) ( $option['score'] ?? 0 ) );
			}
			$worst += $max;

			$chosen = (string) ( $answers[ (string) $question['id'] ] ?? '' );
			$picked = null;
			foreach ( $options as $option ) {
				if ( (string) ( $option['value'] ?? '' ) === $chosen ) {
					$picked = $option;
					break;
				}
			}
			if ( null !== $picked ) {
				++$answered;
				$earned += (int) ( $picked['score'] ?? 0 );
			} else {
				$earned += $max;
			}
		}

		return array(
			'score'    => 0 === $worst ? 0 : (int) round( ( ( $worst - $earned ) / $worst ) * 100 ),
			'answered' => $answered,
			'total'    => count( $visible ),
		);
	}
}
