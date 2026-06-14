<?php
/**
 * Front-end shortcode.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [ai_risk_benchmark] shortcode.
 */
class AIRB_Shortcode {

	/**
	 * Register shortcode and assets.
	 */
	public static function register(): void {
		add_shortcode( 'ai_risk_benchmark', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ), 5 );
	}

	/**
	 * Register CSS/JS handles.
	 */
	public static function register_assets(): void {
		wp_register_style(
			'airb-front',
			AIRB_PLUGIN_URL . 'public/css/airb-front.css',
			array(),
			AIRB_VERSION
		);
		wp_register_script(
			'airb-front',
			AIRB_PLUGIN_URL . 'public/js/airb-front.js',
			array(),
			AIRB_VERSION,
			true
		);
		wp_register_script(
			'airb-deck',
			AIRB_PLUGIN_URL . 'public/js/airb-deck.js',
			array(),
			AIRB_VERSION,
			true
		);
	}

	/**
	 * Enqueue when shortcode renders.
	 */
	public static function enqueue_assets(): void {
		wp_enqueue_style( 'airb-front' );
		wp_enqueue_script( 'airb-front' );
		wp_enqueue_script( 'airb-deck' );
		$contact_email = sanitize_email( (string) get_option( 'admin_email' ) );
		if ( function_exists( 'get_theme_mod' ) ) {
			$theme_email = sanitize_email( (string) get_theme_mod( 'aiad_contact_email', '' ) );
			if ( $theme_email ) {
				$contact_email = $theme_email;
			}
		}

		wp_localize_script(
			'airb-front',
			'airbBenchmark',
			array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'airb_benchmark_nonce' ),
				'contactEmail' => $contact_email,
				'config'       => AIRB_Config::public_config(),
				'i18n'         => array(
					'chooseRole'     => __( 'Choose your role', 'ai-risk-benchmark' ),
					'yourRole'       => __( 'Your role', 'ai-risk-benchmark' ),
					'next'           => __( 'Next', 'ai-risk-benchmark' ),
					'back'           => __( 'Back', 'ai-risk-benchmark' ),
					'submit'         => __( 'See results', 'ai-risk-benchmark' ),
					'step'           => __( 'Step', 'ai-risk-benchmark' ),
					'section'        => __( 'Section', 'ai-risk-benchmark' ),
					'of'             => __( 'of', 'ai-risk-benchmark' ),
					'required'       => __( 'Please answer this question before continuing.', 'ai-risk-benchmark' ),
					'consent'        => __( 'Please confirm consent to store your results.', 'ai-risk-benchmark' ),
					'emailInvalid'   => __( 'Please enter a valid email address.', 'ai-risk-benchmark' ),
					'contactTitle'   => __( 'Almost done', 'ai-risk-benchmark' ),
					'contactHint'    => __( 'Optional details help tailor your leadership report and AI Policy Generator.', 'ai-risk-benchmark' ),
					'contactHintTeacher' => __( 'Optional school context and email help tailor your results. We do not need your school name.', 'ai-risk-benchmark' ),
					'contactHintYoung' => __( 'Optionally choose your year group. We do not collect your name, school or email.', 'ai-risk-benchmark' ),
					'contactHintParent' => __( 'Optionally choose your child\'s year group. We do not collect names, schools or email addresses.', 'ai-risk-benchmark' ),
					'yearGroup'      => __( 'Year group (optional)', 'ai-risk-benchmark' ),
					'yearGroupParent'=> __( 'Child\'s year group (optional)', 'ai-risk-benchmark' ),
					'yearGroupChoose'=> __( 'Select year group…', 'ai-risk-benchmark' ),
					'yearGroups'     => array(
						'reception' => __( 'Reception', 'ai-risk-benchmark' ),
						'year_1'    => __( 'Year 1', 'ai-risk-benchmark' ),
						'year_2'    => __( 'Year 2', 'ai-risk-benchmark' ),
						'year_3'    => __( 'Year 3', 'ai-risk-benchmark' ),
						'year_4'    => __( 'Year 4', 'ai-risk-benchmark' ),
						'year_5'    => __( 'Year 5', 'ai-risk-benchmark' ),
						'year_6'    => __( 'Year 6', 'ai-risk-benchmark' ),
						'year_7'    => __( 'Year 7', 'ai-risk-benchmark' ),
						'year_8'    => __( 'Year 8', 'ai-risk-benchmark' ),
						'year_9'    => __( 'Year 9', 'ai-risk-benchmark' ),
						'year_10'   => __( 'Year 10', 'ai-risk-benchmark' ),
						'year_11'   => __( 'Year 11', 'ai-risk-benchmark' ),
						'year_12'   => __( 'Year 12', 'ai-risk-benchmark' ),
						'year_13'   => __( 'Year 13', 'ai-risk-benchmark' ),
					),
					'emailOptional'  => __( 'Email (optional — to receive your report)', 'ai-risk-benchmark' ),
					'requestFullReport' => __( 'Request full leadership report', 'ai-risk-benchmark' ),
					'reportEmailSubject' => __( 'Interest: Full AI Risk Benchmark leadership report', 'ai-risk-benchmark' ),
					'reportEmailIntro' => __( 'Hello, I completed the free AI Risk & Readiness Benchmark and would like the full leadership report.', 'ai-risk-benchmark' ),
					'reportEmailRole' => __( 'Role', 'ai-risk-benchmark' ),
					'reportEmailClosing' => __( 'Please contact me about the premium leadership report and next steps.', 'ai-risk-benchmark' ),
					'shareWithSchool'  => __( 'Share results with your school', 'ai-risk-benchmark' ),
					'shareResultsHint' => __( 'These results are for you. Share them with a teacher or school leader so your school can build the whole-school picture.', 'ai-risk-benchmark' ),
					'shareResultsHintTeacher' => __( 'Share your results with your SLT or colleagues to help build your school\'s whole-school AI picture.', 'ai-risk-benchmark' ),
					'shareResultsHintLeader' => __( 'Share these results with your governing body or leadership team to align on next steps.', 'ai-risk-benchmark' ),
					'shareResultsHintParent' => __( 'These results are for you. Sharing them with your child\'s school can help build a whole-school picture of AI awareness across parents, students, teachers and leaders.', 'ai-risk-benchmark' ),
					'shareEmailSubject' => __( 'AI Risk Benchmark results to share with school', 'ai-risk-benchmark' ),
					'shareEmailIntro'  => __( 'Hello, I completed the free AI Risk & Readiness Benchmark and wanted to share my results with the school.', 'ai-risk-benchmark' ),
					'shareEmailIntroTeacher' => __( 'Hello, I completed the AI Risk & Readiness Benchmark (teacher audit) and wanted to share my results with the school.', 'ai-risk-benchmark' ),
					'shareEmailIntroLeader' => __( 'Hello, I completed the AI Risk & Readiness Benchmark (school leader audit) and wanted to share our school\'s results.', 'ai-risk-benchmark' ),
					'shareEmailIntroStudent' => __( 'Hello, I completed the AI Risk & Readiness Benchmark (student audit) and wanted to share my results with my school.', 'ai-risk-benchmark' ),
					'shareEmailIntroParent' => __( 'Hello, I completed the AI Risk & Readiness Benchmark (parent/carer audit) and wanted to share my results with my child\'s school.', 'ai-risk-benchmark' ),
					'shareEmailClosing' => __( 'Please share this with the relevant teacher or school leader so our school can build the whole-school picture.', 'ai-risk-benchmark' ),
					'shareEmailClosingTeacher' => __( 'Sharing teacher benchmark results helps build a complete picture across staff, students, parents and leaders.', 'ai-risk-benchmark' ),
					'shareEmailClosingLeader' => __( 'Sharing leader benchmark results helps align governance, staff training and whole-school AI planning.', 'ai-risk-benchmark' ),
					'shareEmailClosingStudent' => __( 'Sharing student benchmark results can help your school understand how pupils are using AI.', 'ai-risk-benchmark' ),
					'shareEmailClosingParent' => __( 'Sharing parent/carer benchmark results helps schools understand home AI use and support families.', 'ai-risk-benchmark' ),
					'emailReport'    => __( 'Email me this summary', 'ai-risk-benchmark' ),
					'saved'          => __( 'Results saved.', 'ai-risk-benchmark' ),
					'saving'         => __( 'Preparing your personalised results…', 'ai-risk-benchmark' ),
					'emailed'        => __( 'Report sent to your email.', 'ai-risk-benchmark' ),
					'error'          => __( 'Something went wrong. Please try again.', 'ai-risk-benchmark' ),
					'modifyLabel'    => __( 'Modify before use', 'ai-risk-benchmark' ),
					'resultsTitle'   => __( 'Your benchmark results', 'ai-risk-benchmark' ),
					'resultsProfileTitle' => __( 'Your AI Risk & Readiness profile', 'ai-risk-benchmark' ),
					'resultsRoleResult' => __( '{role} result', 'ai-risk-benchmark' ),
					'statReadiness'  => __( 'Readiness score', 'ai-risk-benchmark' ),
					'statReadinessNote' => __( 'Weighted across every domain in this audit.', 'ai-risk-benchmark' ),
					'statRisk'       => __( 'AI risk score', 'ai-risk-benchmark' ),
					'statRiskNote'   => __( 'Behavioural exposure — the inverse of readiness.', 'ai-risk-benchmark' ),
					'statDepNote'    => __( 'Higher means greater reliance on AI.', 'ai-risk-benchmark' ),
					'statDepNa'      => __( 'Not measured for this audience.', 'ai-risk-benchmark' ),
					'oversightNa'    => __( 'Not measured for this audience.', 'ai-risk-benchmark' ),
					'domainBreakdown'=> __( 'Domain breakdown', 'ai-risk-benchmark' ),
					'parentScores'   => __( 'Your scores', 'ai-risk-benchmark' ),
					'alignment'      => __( 'DfE AI Alignment Score', 'ai-risk-benchmark' ),
					'dfeAlignment'   => __( 'DfE Alignment Score', 'ai-risk-benchmark' ),
					'statDfeNote'    => __( 'Overall alignment with DfE AI guidance for schools.', 'ai-risk-benchmark' ),
					'yourScore'      => __( 'Your score', 'ai-risk-benchmark' ),
					'topQuartile'    => __( 'Top Quartile Schools', 'ai-risk-benchmark' ),
					'peerEstimated'  => __( 'Comparison uses reference benchmarks until enough similar schools have completed the audit.', 'ai-risk-benchmark' ),
					'peerPercentile' => __( 'Your score is ahead of {n}% of similar schools.', 'ai-risk-benchmark' ),
					'priorityAction' => __( 'Priority Action', 'ai-risk-benchmark' ),
					'roleLeader'     => __( 'Leaders', 'ai-risk-benchmark' ),
					'roleTeacher'    => __( 'Teachers', 'ai-risk-benchmark' ),
					'roleStudent'    => __( 'Students', 'ai-risk-benchmark' ),
					'roleParent'     => __( 'Parents', 'ai-risk-benchmark' ),
					'guidedImprovement' => __( 'Learn how to improve this score', 'ai-risk-benchmark' ),
					'dependency'     => __( 'AI Dependency Index', 'ai-risk-benchmark' ),
					'oversight'      => __( 'Human Oversight Ratio', 'ai-risk-benchmark' ),
					'privacy'        => __( 'Privacy Risk Score', 'ai-risk-benchmark' ),
					'safeguarding'   => __( 'Safeguarding Readiness', 'ai-risk-benchmark' ),
					'governance'     => __( 'Governance Maturity', 'ai-risk-benchmark' ),
					'riskLevel'      => __( 'Overall risk level', 'ai-risk-benchmark' ),
					'readinessLevel' => __( 'Readiness level', 'ai-risk-benchmark' ),
					'bandsReadiness' => array(
						'emerging'    => __( 'Emerging', 'ai-risk-benchmark' ),
						'developing'  => __( 'Developing', 'ai-risk-benchmark' ),
						'established' => __( 'Established', 'ai-risk-benchmark' ),
						'strong'      => __( 'Strong', 'ai-risk-benchmark' ),
					),
					'domainScores'   => __( 'Domain scores', 'ai-risk-benchmark' ),
					'recommendations'=> __( 'Tailored recommendations', 'ai-risk-benchmark' ),
					'exposure'       => __( 'Key exposure areas', 'ai-risk-benchmark' ),
					'measures'       => __( 'Measures', 'ai-risk-benchmark' ),
					'outputs'        => __( 'Your outputs', 'ai-risk-benchmark' ),
					'afterPrinciple' => __( 'No hard sales. Only evidence-based recommendations.', 'ai-risk-benchmark' ),
					'stage1'         => __( 'Stage 1 · Free benchmark', 'ai-risk-benchmark' ),
					'stage2'         => __( 'Stage 2 · Automated recommendations', 'ai-risk-benchmark' ),
					'stage3'         => __( 'Stage 3 · Leadership report', 'ai-risk-benchmark' ),
					'stage4'         => __( 'Get support', 'ai-risk-benchmark' ),
					'consultationTitle' => __( 'Get support from the AI Awareness Day team', 'ai-risk-benchmark' ),
					'heatMap'        => __( 'Risk heat map', 'ai-risk-benchmark' ),
					'overallScore'   => __( 'Overall score', 'ai-risk-benchmark' ),
					'highRiskAreas'  => __( 'High risk areas', 'ai-risk-benchmark' ),
					'recommendedActions' => __( 'Recommended actions', 'ai-risk-benchmark' ),
					'policyGen'      => __( 'Policy Generator', 'ai-risk-benchmark' ),
					'schoolPhase'    => __( 'School phase (optional)', 'ai-risk-benchmark' ),
					'schoolPhaseChoose' => __( 'Select phase…', 'ai-risk-benchmark' ),
					'schoolPhasePrimary' => __( 'Primary', 'ai-risk-benchmark' ),
					'schoolPhaseSecondary' => __( 'Secondary', 'ai-risk-benchmark' ),
					'schoolPhaseAllThrough' => __( 'All-through', 'ai-risk-benchmark' ),
					'orgType'        => __( 'Organisation type (optional)', 'ai-risk-benchmark' ),
					'orgTypeChoose'  => __( 'Select type…', 'ai-risk-benchmark' ),
					'orgStandalone'  => __( 'Standalone school', 'ai-risk-benchmark' ),
					'orgMat'         => __( 'MAT', 'ai-risk-benchmark' ),
					'profileHint'    => __( 'Helps tailor your AI Policy Generator and leadership report.', 'ai-risk-benchmark' ),
					'profileHintTeacher' => __( 'Helps tailor recommendations to your school context.', 'ai-risk-benchmark' ),
					'schoolOptional' => __( 'School name (optional)', 'ai-risk-benchmark' ),
					'insightLabel'   => __( 'Your priority focus', 'ai-risk-benchmark' ),
					'domainFocus'    => __( 'What to focus on', 'ai-risk-benchmark' ),
					'roleDone'       => __( 'Done · {n}%', 'ai-risk-benchmark' ),
					'retakeAudit'    => __( 'Retake audit', 'ai-risk-benchmark' ),
					'startAudit'     => __( 'Start audit', 'ai-risk-benchmark' ),
					'nextSteps'      => __( 'Recommended for you', 'ai-risk-benchmark' ),
					'gatewayTitle'   => __( 'Your audit is the starting point', 'ai-risk-benchmark' ),
					'viewSchool'     => __( 'View school-wide dashboard', 'ai-risk-benchmark' ),
					'schoolHint'     => __( 'Enter your school name on the previous step to unlock the whole-school view once all groups have completed audits.', 'ai-risk-benchmark' ),
					'benchmark'      => array(
						'title'          => __( 'How you compare nationally', 'ai-risk-benchmark' ),
						'percentilePre'  => __( 'Your alignment score is ahead of', 'ai-risk-benchmark' ),
						'percentilePost' => __( 'of schools benchmarked for your role.', 'ai-risk-benchmark' ),
						'avgShort'       => __( 'national avg', 'ai-risk-benchmark' ),
						'sampleNote'     => __( 'Based on {n} consented submissions for your role. Updated live as more schools take part.', 'ai-risk-benchmark' ),
					),
				),
			)
		);
	}

	/**
	 * Render shortcode output.
	 *
	 * @param array<string, string>|string $atts Attributes.
	 */
	public static function render( $atts = array() ): string {
		self::enqueue_assets();

		ob_start();
		include AIRB_PLUGIN_DIR . 'templates/form-wrapper.php';
		return (string) ob_get_clean();
	}
}
