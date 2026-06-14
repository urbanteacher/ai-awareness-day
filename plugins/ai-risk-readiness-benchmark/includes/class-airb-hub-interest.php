<?php
/**
 * Contextual interest capture on intervention hub pages (FAQ / deep-dive funnel step).
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hub page interest form — page, ref, role and optional benchmark session context.
 */
class AIRB_Hub_Interest {

	/**
	 * Map benchmark domain ref slugs to interest checkbox slugs.
	 *
	 * @return array<string, string>
	 */
	public static function ref_interest_map(): array {
		return array(
			'human_oversight'       => 'whole_school_cpd',
			'privacy'               => 'whole_school_cpd',
			'safeguarding'          => 'parent_sessions',
			'assessment_integrity'  => 'whole_school_cpd',
			'governance'            => 'policy_support',
			'governance_maturity'   => 'governance_review',
			'ai_literacy'           => 'teacher_learn_ai',
			'ai_dependency'         => 'whole_school_cpd',
			'safe_adoption'         => 'whole_school_cpd',
			'parent_awareness'      => 'parent_support_child',
			'home_ai_safety'        => 'parent_resources',
			'homework_oversight'    => 'parent_support_child',
			'parent_ai_dependency'  => 'parent_learn_ai',
			'school_partnership'    => 'parent_share_with_school',
			'child_privacy_risk'    => 'parent_support_child',
			'homework_support_risk' => 'parent_support_child',
			'parent_confidence'     => 'parent_learn_ai',
		);
	}

	/**
	 * Map hub page slugs to default interest prefill.
	 *
	 * @return array<string, string>
	 */
	public static function slug_interest_map(): array {
		return array(
			'teacher-ai-verification-framework'   => 'teacher_learn_ai',
			'teacher-ai-lesson-planning-checklist' => 'teacher_activity_day',
			'teacher-ai-privacy-guide'            => 'whole_school_cpd',
			'teacher-ai-assessment-guide'         => 'whole_school_cpd',
			'student-ai-study-skills'             => 'student_learn_ai',
			'think-first-prompt-second'           => 'student_learn_ai',
			'student-ai-privacy-guide'            => 'student_learn_ai',
			'how-to-check-ai-answers'             => 'student_learn_ai',
			'parent-ai-safety'                    => 'parent_resources',
			'parent-ai-homework-guide'            => 'parent_support_child',
			'parent-deepfake-awareness'           => 'parent_resources',
			'ai-policy-generator'                 => 'policy_support',
			'school-ai-governance'                => 'governance_review',
			'school-ai-maturity'                  => 'governance_review',
			'ai-awareness-day'                    => 'ai_awareness_day',
			'whole-school-ai-benchmark'           => 'whole_school_benchmark',
		);
	}

	/**
	 * Resolve role for hub form.
	 */
	public static function resolve_role( string $page_slug, string $url_role = '' ): string {
		$url_role = sanitize_key( $url_role );
		if ( in_array( $url_role, AIRB_Interest::supported_roles(), true ) ) {
			return $url_role;
		}

		$audience = AIRB_Defaults::hub_audience_for_slug( $page_slug );
		$map      = array(
			'teacher' => 'teacher',
			'student' => 'student',
			'parent'  => 'parent',
			'leader'  => 'leader',
			'all'     => 'leader',
		);

		return $map[ $audience ] ?? 'teacher';
	}

	/**
	 * Suggested interests from hub page + ref.
	 *
	 * @return array<int, string>
	 */
	public static function suggested_for_hub( string $page_slug, string $ref, string $role ): array {
		$suggested = array();
		$ref_map   = self::ref_interest_map();
		$slug_map  = self::slug_interest_map();

		if ( $ref && isset( $ref_map[ $ref ] ) ) {
			$suggested[] = $ref_map[ $ref ];
		}
		if ( isset( $slug_map[ $page_slug ] ) ) {
			$suggested[] = $slug_map[ $page_slug ];
		}

		$allowed = array_column( AIRB_Interest::options_for_role( $role ), 'slug' );
		$suggested = array_values( array_unique( array_intersect( $suggested, $allowed ) ) );

		if ( ! $suggested && in_array( 'further_information', $allowed, true ) ) {
			$suggested[] = 'further_information';
		}

		return $suggested;
	}

	/**
	 * Build results-like array from a submission row for interest helpers.
	 *
	 * @param object $row DB row.
	 * @return array<string, mixed>
	 */
	public static function results_from_submission( object $row ): array {
		$domain_scores = json_decode( (string) ( $row->domain_scores ?? '{}' ), true );
		if ( ! is_array( $domain_scores ) ) {
			$domain_scores = array();
		}

		return array(
			'alignment_score'        => (int) ( $row->alignment_score ?? 0 ),
			'risk_level'             => (string) ( $row->risk_level ?? '' ),
			'domain_scores'          => $domain_scores,
		);
	}

	/**
	 * Hub-specific form labels.
	 *
	 * @return array<string, string>
	 */
	public static function form_labels( string $role, string $page_title ): array {
		unset( $page_title );
		$labels = AIRB_Interest::form_labels( $role );
		$labels['heading']         = __( 'Contact AI Awareness Day', 'ai-risk-benchmark' );
		$labels['intro']           = __( 'Optional — tell us if you need further guidance, sessions or whole-school support.', 'ai-risk-benchmark' );
		$labels['context_label']   = __( 'Topic you were reading', 'ai-risk-benchmark' );
		$labels['benchmark_label'] = __( 'From your benchmark', 'ai-risk-benchmark' );
		$labels['submit']          = __( 'Send message', 'ai-risk-benchmark' );
		return $labels;
	}

	/**
	 * Whether current request is an intervention hub page.
	 */
	public static function is_hub_page( ?WP_Post $post = null ): bool {
		if ( function_exists( 'aiad_is_hub_resource_page' ) ) {
			return aiad_is_hub_resource_page( $post );
		}

		if ( null === $post ) {
			if ( ! function_exists( 'is_page' ) || ! is_page() ) {
				return false;
			}
			$post = get_queried_object();
		}

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		return in_array( $post->post_name, AIRB_Hub_Content::intervention_slugs(), true );
	}

	/**
	 * Build client config for hub interest JS.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function client_config( ?WP_Post $post = null ): ?array {
		if ( ! self::is_hub_page( $post ) ) {
			return null;
		}

		if ( null === $post ) {
			$post = get_queried_object();
		}
		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		$slug     = $post->post_name;
		$url_role = isset( $_GET['airb_role'] ) ? sanitize_key( wp_unslash( (string) $_GET['airb_role'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ref      = isset( $_GET['airb_ref'] ) ? sanitize_key( wp_unslash( (string) $_GET['airb_ref'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$role     = self::resolve_role( $slug, $url_role );

		return array(
			'pageSlug'  => $slug,
			'pageTitle' => get_the_title( $post ),
			'ref'       => $ref,
			'role'      => $role,
			'suggested' => self::suggested_for_hub( $slug, $ref, $role ),
			'labels'    => self::form_labels( $role, get_the_title( $post ) ),
			'fields'    => AIRB_Interest::fields_for_role( $role ),
			'options'   => AIRB_Interest::options_for_role( $role ),
			'benchmarkUrl' => AIRB_Defaults::benchmark_page_url(),
			'journey'   => AIRB_Hub_Journey::static_config( $slug, $role, get_the_title( $post ) ),
		);
	}

	/**
	 * Append hub context lines to interest notification email.
	 *
	 * @param array<string, mixed> $data Submission payload.
	 */
	public static function append_email_context( string $body, array $data ): string {
		$source = sanitize_key( (string) ( $data['source'] ?? '' ) );
		if ( 'hub' !== $source ) {
			return $body;
		}

		$body .= "\nSource: Hub resource page\n";
		if ( ! empty( $data['hub_title'] ) ) {
			$body .= 'Topic: ' . sanitize_text_field( (string) $data['hub_title'] ) . "\n";
		}
		if ( ! empty( $data['hub_page'] ) ) {
			$body .= 'Page slug: ' . sanitize_key( (string) $data['hub_page'] ) . "\n";
		}
		if ( ! empty( $data['hub_ref'] ) ) {
			$body .= 'Benchmark ref: ' . sanitize_key( (string) $data['hub_ref'] ) . "\n";
		}
		if ( ! empty( $data['hub_url'] ) ) {
			$body .= 'Page URL: ' . esc_url_raw( (string) $data['hub_url'] ) . "\n";
		}
		$done  = (int) ( $data['checklist_done'] ?? 0 );
		$total = (int) ( $data['checklist_total'] ?? 0 );
		if ( $total > 0 ) {
			$body .= "Checklist progress: {$done}/{$total}\n";
		}

		return $body;
	}

	/**
	 * Render mount point for hub interest form (filled by JS).
	 */
	public static function render_form_shell(): void {
		if ( ! self::is_hub_page() ) {
			return;
		}
		echo '<section class="airb-hub-journey-wrap" id="airb-hub-interest" aria-label="' . esc_attr__( 'Your improvement journey', 'ai-risk-benchmark' ) . '">';
		echo '<div class="airb-hub-journey-mount" data-airb-hub-journey-mount></div>';
		echo '</section>';
	}

	/**
	 * Register hooks.
	 */
	public static function register(): void {
		add_action( 'airb_after_hub_resource_content', array( __CLASS__, 'render_form_shell' ) );
	}
}
