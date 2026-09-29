<?php
/**
 * Schools and the people attached to them.
 *
 * Status flow: pending_email -> pending_slt -> approved   (or rejected)
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Schools {

	public static function get( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'schools' ) . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	public static function get_by_code( string $code ): ?array {
		global $wpdb;
		if ( '' === $code ) {
			return null;
		}
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'schools' ) . ' WHERE code = %s', $code ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/**
	 * Register a school and its first teacher (the lead). Returns the new school id, or a WP_Error.
	 *
	 * @param array<string,string> $data Already sanitised.
	 */
	public static function register( array $data ) {
		global $wpdb;

		$name_key = AIADN_Util::key( $data['name'] );
		$postcode = strtoupper( preg_replace( '/\s+/', '', $data['postcode'] ) );

		// The same school entered twice: point them at the front door instead.
		$existing = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'SELECT id FROM ' . AIADN_Database::table( 'schools' ) . " WHERE name_key = %s AND REPLACE(postcode,' ','') = %s AND status <> 'rejected' AND status <> 'pending_email' LIMIT 1",
			$name_key,
			$postcode
		) );
		if ( $existing ) {
			return new WP_Error( 'exists', 'This school is already registered.' );
		}

		$now = AIADN_Util::now();
		$ok  = $wpdb->insert( // phpcs:ignore WordPress.DB
			AIADN_Database::table( 'schools' ),
			array(
				'name'       => $data['name'],
				'name_key'   => $name_key,
				'postcode'   => $data['postcode'],
				'age_phases' => $data['age_phases'],
				'mat_name'   => $data['mat_name'],
				'partner_ref' => $data['partner_ref'],
				'slt_email'  => $data['slt_email'],
				'status'     => 'pending_email',
				'created_at' => $now,
				'updated_at' => $now,
			)
		);
		if ( ! $ok ) {
			return new WP_Error( 'db', 'Could not save the school.' );
		}
		$school_id = (int) $wpdb->insert_id;

		self::add_member( $school_id, $data['email'], $data['teacher_name'], $data['job_title'], 'lead' );
		return $school_id;
	}

	public static function add_member( int $school_id, string $email, string $name, string $job_title, string $role, bool $verified = false ): int {
		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB
			AIADN_Database::table( 'members' ),
			array(
				'school_id'         => $school_id,
				'email'             => $email,
				'name'              => $name,
				'job_title'         => $job_title,
				'role'              => $role,
				'email_verified_at' => $verified ? AIADN_Util::now() : null,
				'created_at'        => AIADN_Util::now(),
			)
		);
		return (int) $wpdb->insert_id;
	}

	public static function get_member( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'members' ) . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	public static function find_member( int $school_id, string $email ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'members' ) . ' WHERE school_id = %d AND email = %s', $school_id, $email ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> */
	public static function members( int $school_id ): array {
		global $wpdb;
		return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'members' ) . ' WHERE school_id = %d ORDER BY id ASC', $school_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	/** The school's senior leaders: the approver named at registration plus anyone who has signed in as SLT. */
	public static function slt_emails( int $school_id ): array {
		$school = self::get( $school_id );
		$emails = $school && '' !== $school['slt_email'] ? array( strtolower( $school['slt_email'] ) ) : array();
		foreach ( self::members( $school_id ) as $m ) {
			if ( 'slt' === $m['role'] ) {
				$emails[] = strtolower( $m['email'] );
			}
		}
		return array_values( array_unique( $emails ) );
	}

	public static function lead( int $school_id ): ?array {
		foreach ( self::members( $school_id ) as $m ) {
			if ( 'lead' === $m['role'] ) {
				return $m;
			}
		}
		return null;
	}

	public static function mark_member_verified( int $member_id ): void {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . AIADN_Database::table( 'members' ) . ' SET email_verified_at = COALESCE(email_verified_at, %s), last_login_at = %s WHERE id = %d', AIADN_Util::now(), AIADN_Util::now(), $member_id ) ); // phpcs:ignore WordPress.DB
	}

	public static function touch_login( int $member_id ): void {
		global $wpdb;
		$wpdb->update( AIADN_Database::table( 'members' ), array( 'last_login_at' => AIADN_Util::now() ), array( 'id' => $member_id ) ); // phpcs:ignore WordPress.DB
	}

	public static function set_status( int $school_id, string $status, array $extra = array() ): void {
		global $wpdb;
		$wpdb->update( // phpcs:ignore WordPress.DB
			AIADN_Database::table( 'schools' ),
			array_merge( array( 'status' => $status, 'updated_at' => AIADN_Util::now() ), $extra ),
			array( 'id' => $school_id )
		);
	}

	/** Give a school its code (once its lead has verified their email). */
	public static function assign_code( int $school_id ): string {
		global $wpdb;
		$school = self::get( $school_id );
		if ( $school && ! empty( $school['code'] ) ) {
			return (string) $school['code'];
		}
		for ( $i = 0; $i < 20; $i++ ) {
			$code = AIADN_Util::new_school_code();
			// The unique index is the real guard; this just avoids a needless failed write.
			if ( self::get_by_code( $code ) ) {
				continue;
			}
			$done = $wpdb->update( AIADN_Database::table( 'schools' ), array( 'code' => $code, 'updated_at' => AIADN_Util::now() ), array( 'id' => $school_id ) ); // phpcs:ignore WordPress.DB
			if ( false !== $done ) {
				return $code;
			}
		}
		return '';
	}

	/**
	 * Which member (if any) may sign in at the front door with this email and role?
	 * Returns array( member|null, may_join_by_domain )
	 *
	 * - teacher: an existing lead/teacher, or (approved schools only) anyone on the lead's school email domain
	 * - slt: the SLT member created when they approved the school
	 * - judge: someone a teacher has invited to judge a debate involving this school
	 *
	 * @return array{0:?array,1:bool}
	 */
	public static function eligibility( array $school, string $email, string $role ): array {
		$school_id = (int) $school['id'];
		$member    = self::find_member( $school_id, $email );

		if ( 'teacher' === $role ) {
			if ( $member && in_array( $member['role'], array( 'lead', 'teacher' ), true ) ) {
				return array( $member, false );
			}
			if ( 'approved' === $school['status'] && ! $member ) {
				$lead = self::lead( $school_id );
				if ( $lead ) {
					$domain = AIADN_Util::email_domain( $email );
					if ( '' !== $domain && $domain === AIADN_Util::email_domain( $lead['email'] ) && ! AIADN_Util::is_free_mail_domain( $domain ) ) {
						return array( null, true );
					}
				}
			}
			return array( null, false );
		}

		if ( 'judge' === $role ) {
			$judge = AIADN_Debates::judge_for_school_email( $school_id, $email );
			if ( $judge ) {
				return array( $judge, false );
			}
			return array( null, false );
		}

		if ( 'slt' === $role ) {
			if ( $member && 'slt' === $member['role'] && 'approved' === $school['status'] ) {
				return array( $member, false );
			}
		}

		return array( null, false );
	}
}
