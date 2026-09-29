<?php
/**
 * Which region a school is in, from the letters at the start of its postcode.
 *
 * This is approximate: a few postcode areas straddle a boundary (Chester, Shrewsbury, Kingston and the
 * outer London towns), and each is placed where most of its addresses sit. It is enough for a programme
 * dashboard and it is never shown to a school.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Regions {

	const UNKNOWN = 'Unknown';

	const AREAS = array(
		'North East'               => array( 'DH', 'DL', 'NE', 'SR', 'TS' ),
		'North West'               => array( 'BB', 'BL', 'CA', 'CH', 'CW', 'FY', 'L', 'LA', 'M', 'OL', 'PR', 'SK', 'WA', 'WN' ),
		'Yorkshire and The Humber' => array( 'BD', 'DN', 'HD', 'HG', 'HU', 'HX', 'LS', 'S', 'WF', 'YO' ),
		'East Midlands'            => array( 'DE', 'LE', 'LN', 'NG', 'NN' ),
		'West Midlands'            => array( 'B', 'CV', 'DY', 'HR', 'ST', 'SY', 'TF', 'WR', 'WS', 'WV' ),
		'East of England'          => array( 'AL', 'CB', 'CM', 'CO', 'IP', 'LU', 'NR', 'PE', 'SG', 'SS', 'WD' ),
		'London'                   => array( 'E', 'EC', 'N', 'NW', 'SE', 'SW', 'W', 'WC', 'BR', 'CR', 'EN', 'HA', 'IG', 'RM', 'SM', 'TW', 'UB' ),
		'South East'               => array( 'BN', 'CT', 'DA', 'GU', 'HP', 'KT', 'ME', 'MK', 'OX', 'PO', 'RG', 'RH', 'SL', 'SO', 'TN' ),
		'South West'               => array( 'BA', 'BH', 'BS', 'DT', 'EX', 'GL', 'PL', 'SN', 'SP', 'TA', 'TQ', 'TR' ),
		'Wales'                    => array( 'CF', 'LD', 'LL', 'NP', 'SA' ),
		'Scotland'                 => array( 'AB', 'DD', 'DG', 'EH', 'FK', 'G', 'HS', 'IV', 'KA', 'KW', 'KY', 'ML', 'PA', 'PH', 'TD', 'ZE' ),
		'Northern Ireland'         => array( 'BT' ),
		'Channel Islands and Isle of Man' => array( 'GY', 'JE', 'IM' ),
	);

	/** The letters at the start of a postcode, e.g. "LS" for "LS6 2AB". Empty if it does not look like one. */
	public static function area( string $postcode ): string {
		$pc = strtoupper( preg_replace( '/\s+/', '', $postcode ) );
		return preg_match( '/^([A-Z]{1,2})\d/', $pc, $m ) ? $m[1] : '';
	}

	public static function for_postcode( string $postcode ): string {
		static $lookup = null;
		if ( null === $lookup ) {
			$lookup = array();
			foreach ( self::AREAS as $region => $areas ) {
				foreach ( $areas as $a ) {
					$lookup[ $a ] = $region;
				}
			}
		}
		$area = self::area( $postcode );
		return ( '' !== $area && isset( $lookup[ $area ] ) ) ? $lookup[ $area ] : self::UNKNOWN;
	}
}
