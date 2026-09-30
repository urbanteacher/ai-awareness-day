<?php
/**
 * NEU AI report — canonical chart data (JSON) for localisation and exports.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load NEU report configuration from theme JSON.
 *
 * @return array<string, mixed>
 */
function aiad_neu_ai_report_get_config(): array {
	static $config = null;

	if ( null !== $config ) {
		return $config;
	}

	$path = AIAD_DIR . '/assets/data/neu-ai-report.json';
	if ( ! file_exists( $path ) ) {
		$config = array(
			'sections'  => array(),
			'strings'   => array(),
			'sourceUrl' => 'https://neu.org.uk/latest/press-releases/state-education-2026-ai',
		);
		return $config;
	}

	$raw = file_get_contents( $path );
	$decoded = json_decode( (string) $raw, true );
	if ( ! is_array( $decoded ) ) {
		$config = array(
			'sections'  => array(),
			'strings'   => array(),
			'sourceUrl' => 'https://neu.org.uk/latest/press-releases/state-education-2026-ai',
		);
		return $config;
	}

	$config = $decoded;
	return $config;
}

/**
 * JSON-LD Dataset schema for the NEU figures used in the interactive.
 *
 * @return array<string, mixed>
 */
function aiad_neu_ai_report_dataset_schema(): array {
	$config = aiad_neu_ai_report_get_config();
	$rows   = array();

	foreach ( $config['sections'] as $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}
		$section_id = (string) ( $section['id'] ?? '' );
		$title      = (string) ( $section['title'] ?? $section_id );

		foreach ( array( 'bars', 'split', 'policy', 'stats' ) as $key ) {
			if ( empty( $section[ $key ] ) || ! is_array( $section[ $key ] ) ) {
				continue;
			}
			foreach ( $section[ $key ] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$label = (string) ( $row['l'] ?? '' );
				$pct   = isset( $row['pct'] ) ? (float) $row['pct'] : null;
				if ( '' === $label && null === $pct ) {
					$label = (string) ( $row['n'] ?? '' );
				}
				$rows[] = array(
					'@type'       => 'DataFeedItem',
					'name'        => $title . ' — ' . $label,
					'description' => $label,
					'value'       => null !== $pct ? $pct : (string) ( $row['n'] ?? '' ),
				);
			}
		}
	}

	return array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Dataset',
		'name'        => 'NEU State of Education: AI Report 2026 — key figures',
		'description' => (string) ( $config['surveyNote'] ?? 'NEU teacher survey data visualised by AI Awareness Day.' ),
		'url'         => (string) ( $config['sourceUrl'] ?? '' ),
		'creator'     => array(
			'@type' => 'Organization',
			'name'  => 'National Education Union',
		),
		'distribution' => array(
			array(
				'@type'    => 'DataDownload',
				'encodingFormat' => 'text/csv',
				'contentUrl' => home_url( '/?aiad_neu_report_csv=1' ),
			),
		),
		'variableMeasured' => $rows,
	);
}

/**
 * CSV export for chart data (?aiad_neu_report_csv=1).
 */
function aiad_neu_ai_report_maybe_serve_csv(): void {
	if ( ! isset( $_GET['aiad_neu_report_csv'] ) || '1' !== (string) $_GET['aiad_neu_report_csv'] ) {
		return;
	}

	$config   = aiad_neu_ai_report_get_config();
	$sections = $config['sections'] ?? array();

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="neu-ai-report-2026-data.csv"' );

	$out = fopen( 'php://output', 'w' );
	if ( false === $out ) {
		exit;
	}

	fputcsv( $out, array( 'section_id', 'section_title', 'metric', 'value_percent', 'source', 'notes' ) );

	foreach ( $sections as $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}
		$sid   = (string) ( $section['id'] ?? '' );
		$title = (string) ( $section['title'] ?? $sid );
		$note  = (string) ( $section['note'] ?? '' );

		$groups = array(
			array( 'key' => 'bars', 'source' => 'NEU' ),
			array( 'key' => 'split', 'source' => 'NEU' ),
			array( 'key' => 'policy', 'source' => 'NEU' ),
			array( 'key' => 'stats', 'source' => 'NEU' ),
		);

		foreach ( $groups as $group ) {
			$key            = $group['key'];
			$default_source = $group['source'];
			if ( empty( $section[ $key ] ) || ! is_array( $section[ $key ] ) ) {
				continue;
			}
			foreach ( $section[ $key ] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$metric = (string) ( $row['l'] ?? '' );
				$pct    = isset( $row['pct'] ) ? (string) $row['pct'] : '';
				$source = (string) ( $row['source'] ?? $default_source );
				if ( in_array( $key, array( 'policy', 'stats', 'split' ), true ) ) {
					if ( '' === $metric ) {
						$metric = (string) ( $row['l'] ?? '' );
					}
					if ( '' === $pct && isset( $row['pct'] ) ) {
						$pct = (string) $row['pct'];
					} elseif ( '' === $pct && isset( $row['n'] ) ) {
						$pct = (string) preg_replace( '/[^0-9.]/', '', (string) $row['n'] );
					}
				}
				fputcsv( $out, array( $sid, $title, $metric, $pct, $source, $note ) );
			}
		}
	}

	fclose( $out );
	exit;
}
add_action( 'template_redirect', 'aiad_neu_ai_report_maybe_serve_csv' );
