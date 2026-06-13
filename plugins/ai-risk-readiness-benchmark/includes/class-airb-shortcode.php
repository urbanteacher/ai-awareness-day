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
		wp_localize_script(
			'airb-front',
			'airbBenchmark',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'airb_benchmark_nonce' ),
				'config'  => AIRB_Config::public_config(),
				'i18n'    => array(
					'chooseRole'     => __( 'Choose your role', 'ai-risk-benchmark' ),
					'next'           => __( 'Next', 'ai-risk-benchmark' ),
					'back'           => __( 'Back', 'ai-risk-benchmark' ),
					'submit'         => __( 'See results', 'ai-risk-benchmark' ),
					'step'           => __( 'Step', 'ai-risk-benchmark' ),
					'of'             => __( 'of', 'ai-risk-benchmark' ),
					'required'       => __( 'Please answer this question before continuing.', 'ai-risk-benchmark' ),
					'consent'        => __( 'Please confirm consent to store your results.', 'ai-risk-benchmark' ),
					'emailInvalid'   => __( 'Please enter a valid email address.', 'ai-risk-benchmark' ),
					'contactTitle'   => __( 'Almost done', 'ai-risk-benchmark' ),
					'contactHint'    => __( 'Optional details help tailor your leadership report and AI Policy Generator.', 'ai-risk-benchmark' ),
					'emailOptional'  => __( 'Email (optional — to receive your report)', 'ai-risk-benchmark' ),
					'consentLabel'   => __( 'I consent to my anonymised benchmark results being stored to help improve school AI readiness. No student personal data is collected.', 'ai-risk-benchmark' ),
					'printReport'    => __( 'Download / print report', 'ai-risk-benchmark' ),
					'emailReport'    => __( 'Email me this report', 'ai-risk-benchmark' ),
					'saved'          => __( 'Results saved.', 'ai-risk-benchmark' ),
					'saving'         => __( 'Preparing your personalised results…', 'ai-risk-benchmark' ),
					'emailed'        => __( 'Report sent to your email.', 'ai-risk-benchmark' ),
					'error'          => __( 'Something went wrong. Please try again.', 'ai-risk-benchmark' ),
					'modifyLabel'    => __( 'Modify before use', 'ai-risk-benchmark' ),
					'resultsTitle'   => __( 'Your benchmark results', 'ai-risk-benchmark' ),
					'alignment'      => __( 'DfE AI Alignment Score', 'ai-risk-benchmark' ),
					'dependency'     => __( 'AI Dependency Index', 'ai-risk-benchmark' ),
					'oversight'      => __( 'Human Oversight Ratio', 'ai-risk-benchmark' ),
					'privacy'        => __( 'Privacy Risk Score', 'ai-risk-benchmark' ),
					'safeguarding'   => __( 'Safeguarding Readiness', 'ai-risk-benchmark' ),
					'governance'     => __( 'Governance Maturity', 'ai-risk-benchmark' ),
					'riskLevel'      => __( 'Overall risk level', 'ai-risk-benchmark' ),
					'domainScores'   => __( 'Domain scores', 'ai-risk-benchmark' ),
					'recommendations'=> __( 'Tailored recommendations', 'ai-risk-benchmark' ),
					'exposure'       => __( 'Key exposure areas', 'ai-risk-benchmark' ),
					'measures'       => __( 'Measures', 'ai-risk-benchmark' ),
					'outputs'        => __( 'Your outputs', 'ai-risk-benchmark' ),
					'afterPrinciple' => __( 'No hard sales. Only evidence-based recommendations.', 'ai-risk-benchmark' ),
					'stage1'         => __( 'Stage 1 · Free benchmark', 'ai-risk-benchmark' ),
					'stage2'         => __( 'Stage 2 · Automated recommendations', 'ai-risk-benchmark' ),
					'stage3'         => __( 'Stage 3 · Leadership report', 'ai-risk-benchmark' ),
					'stage4'         => __( 'Stage 4 · Walk through your findings', 'ai-risk-benchmark' ),
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
					'schoolOptional' => __( 'School name (optional)', 'ai-risk-benchmark' ),
					'nextSteps'      => __( 'Recommended for you', 'ai-risk-benchmark' ),
					'gatewayTitle'   => __( 'Your audit is the starting point', 'ai-risk-benchmark' ),
					'viewSchool'     => __( 'View school-wide dashboard', 'ai-risk-benchmark' ),
					'schoolHint'     => __( 'Enter your school name on the previous step (with consent) to unlock the whole-school view once all groups have completed audits.', 'ai-risk-benchmark' ),
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
