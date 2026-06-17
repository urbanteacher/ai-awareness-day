<?php
/**
 * Config storage and retrieval.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Benchmark configuration.
 */
class AIRB_Config {

	/**
	 * Seed defaults on first run.
	 */
	public static function maybe_seed_defaults(): void {
		if ( get_option( AIRB_OPTION_CONFIG ) ) {
			self::maybe_upgrade_config();
			return;
		}
		update_option( AIRB_OPTION_CONFIG, AIRB_Defaults::config(), false );
	}

	/**
	 * Merge new default keys into existing saved config (non-destructive).
	 */
	public static function maybe_upgrade_config(): void {
		$config  = get_option( AIRB_OPTION_CONFIG );
		$defaults = AIRB_Defaults::config();
		if ( ! is_array( $config ) ) {
			return;
		}

		$changed = false;
		$top_keys = array( 'framework', 'domain_sources', 'positioning', 'domain_descriptions', 'guidance_refs', 'gateway', 'pathway_offers', 'role_benchmarks', 'signature_metrics', 'after_audit', 'services', 'aad_2027' );
		foreach ( $top_keys as $key ) {
			if ( empty( $config[ $key ] ) && ! empty( $defaults[ $key ] ) ) {
				$config[ $key ] = $defaults[ $key ];
				$changed        = true;
			}
		}

		if ( is_array( $config['positioning'] ?? null ) && is_array( $defaults['positioning'] ?? null ) ) {
			foreach ( $defaults['positioning'] as $sub_key => $sub_val ) {
				if ( empty( $config['positioning'][ $sub_key ] ) && ! empty( $sub_val ) ) {
					$config['positioning'][ $sub_key ] = $sub_val;
					$changed = true;
				}
			}

			$stored_headline = (string) ( $config['positioning']['headline'] ?? '' );
			$inaccurate_headlines = array(
				"The UK's First DfE-Aligned AI Risk & Readiness Benchmark for Schools",
				'The UK\'s First DfE-Aligned AI Risk & Readiness Benchmark for Schools',
				'AI Risk & Readiness Benchmark for Schools',
			);
			if ( in_array( $stored_headline, $inaccurate_headlines, true ) ) {
				$config['positioning']['headline'] = (string) ( $defaults['positioning']['headline'] ?? '' );
				$changed                           = true;
			}
		}

		if ( is_array( $config['guidance_refs'] ?? null ) ) {
			$withdrawn_ofsted_url = 'https://www.gov.uk/government/publications/ofsteds-approach-to-artificial-intelligence-ai-in-education';
			$current_ofsted_url   = 'https://www.gov.uk/government/publications/ofsteds-approach-to-ai';
			foreach ( $config['guidance_refs'] as $index => $ref ) {
				if ( ! is_array( $ref ) ) {
					continue;
				}
				if ( (string) ( $ref['url'] ?? '' ) === $withdrawn_ofsted_url ) {
					$config['guidance_refs'][ $index ]['url'] = $current_ofsted_url;
					$changed                                  = true;
				}
			}
		}

		$legacy_disclaimers = array(
			'This tool is an educational self-assessment aligned to DfE, ICO, KCSIE, JCQ, Ofqual and Ofsted guidance for schools in England. It is not legal advice and does not replace safeguarding, data protection or legal counsel. It does not imply official endorsement.',
			'Educational self-assessment only — not legal advice and not official endorsement. No student names or personal data are collected.',
		);
		if ( in_array( (string) ( $config['disclaimer'] ?? '' ), $legacy_disclaimers, true ) ) {
			$config['disclaimer'] = '';
			$changed              = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 4 ) {
			$config['questions'] = AIRB_Questions::all();
			$config['version']   = 4;
			$changed             = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 5 ) {
			if ( ! empty( $config['questions'] ) && is_array( $config['questions'] ) ) {
				$policy_labels = array(
					'published' => __( 'Published & reviewed', 'ai-risk-benchmark' ),
					'draft'     => __( 'In draft', 'ai-risk-benchmark' ),
					'informal'  => __( 'Informal only', 'ai-risk-benchmark' ),
				);
				foreach ( $config['questions'] as &$question ) {
					if ( (string) ( $question['id'] ?? '' ) !== 'l_policy' || empty( $question['options'] ) ) {
						continue;
					}
					foreach ( $question['options'] as &$option ) {
						$value = (string) ( $option['value'] ?? '' );
						if ( isset( $policy_labels[ $value ] ) ) {
							$option['label'] = $policy_labels[ $value ];
						}
					}
					unset( $option );
				}
				unset( $question );
			}
			$config['version'] = 5;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 6 ) {
			if ( ! empty( $config['questions'] ) && is_array( $config['questions'] ) ) {
				$without_ai_labels = array(
					'yes_easily' => __( 'Yes, easily', 'ai-risk-benchmark' ),
					'yes_some'   => __( 'Yes, with effort', 'ai-risk-benchmark' ),
					'difficult'  => __( 'Difficult', 'ai-risk-benchmark' ),
					'no'         => __( 'Not realistically', 'ai-risk-benchmark' ),
				);
				foreach ( $config['questions'] as &$question ) {
					if ( (string) ( $question['id'] ?? '' ) !== 't_without_ai' || empty( $question['options'] ) ) {
						continue;
					}
					foreach ( $question['options'] as &$option ) {
						$value = (string) ( $option['value'] ?? '' );
						if ( isset( $without_ai_labels[ $value ] ) ) {
							$option['label'] = $without_ai_labels[ $value ];
						}
					}
					unset( $option );
				}
				unset( $question );
			}
			$config['version'] = 6;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 7 ) {
			self::patch_dfe_cta_urls( $config );
			$config['version'] = 7;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 8 ) {
			if ( is_array( $config['consultation_cta'] ?? null ) ) {
				$config['consultation_cta']['title'] = __( 'Contact the AI Awareness Day team', 'ai-risk-benchmark' );
				$config['consultation_cta']['text']  = __( 'Get support with your results', 'ai-risk-benchmark' );
				$changed                             = true;
			}
			$config['version'] = 8;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 13 ) {
			$config['questions'] = AIRB_Questions::all();
			$config['version']   = 13;
			$changed             = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 14 ) {
			$default_roles = (array) ( $defaults['role_benchmarks'] ?? array() );
			if ( ! isset( $config['role_benchmarks'] ) || ! is_array( $config['role_benchmarks'] ) ) {
				$config['role_benchmarks'] = $default_roles;
			} else {
				foreach ( $default_roles as $role => $role_defaults ) {
					if ( ! is_array( $role_defaults ) ) {
						continue;
					}
					if ( ! isset( $config['role_benchmarks'][ $role ] ) || ! is_array( $config['role_benchmarks'][ $role ] ) ) {
						$config['role_benchmarks'][ $role ] = $role_defaults;
						$changed                            = true;
						continue;
					}
					if ( ! empty( $role_defaults['tagline'] ) ) {
						$config['role_benchmarks'][ $role ]['tagline'] = $role_defaults['tagline'];
						$changed                                       = true;
					}
				}
			}
			$config['version'] = 14;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 15 ) {
			$default_roles = (array) ( $defaults['role_benchmarks'] ?? array() );
			if ( ! isset( $config['role_benchmarks'] ) || ! is_array( $config['role_benchmarks'] ) ) {
				$config['role_benchmarks'] = $default_roles;
			} else {
				foreach ( $default_roles as $role => $role_defaults ) {
					if ( ! is_array( $role_defaults ) || empty( $role_defaults['tagline'] ) ) {
						continue;
					}
					if ( ! isset( $config['role_benchmarks'][ $role ] ) || ! is_array( $config['role_benchmarks'][ $role ] ) ) {
						$config['role_benchmarks'][ $role ] = $role_defaults;
					} else {
						$config['role_benchmarks'][ $role ]['tagline'] = $role_defaults['tagline'];
					}
					$changed = true;
				}
			}
			$config['version'] = 15;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 16 ) {
			$default_leader = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
				$config['leader_result'] = $default_leader;
			} else {
				foreach ( array( 'metric_labels', 'metric_signals', 'risk_score_note' ) as $key ) {
					if ( ! empty( $default_leader[ $key ] ) ) {
						$config['leader_result'][ $key ] = $default_leader[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 16;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 17 ) {
			$default_leader = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
				$config['leader_result'] = $default_leader;
			} else {
				foreach ( array(
					'metric_signals',
					'urgent_action_heading',
					'peer_comparison_label',
					'peer_you_label',
					'peer_gap_below_average',
					'peer_gap_above_average',
					'peer_gap_at_average',
					'peer_gap_below_top',
					'peer_gap_above_top',
					'default_priority_rationale',
					'priority_rationales',
				) as $key ) {
					if ( ! empty( $default_leader[ $key ] ) ) {
						$config['leader_result'][ $key ] = $default_leader[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 17;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 18 ) {
			$default_leader = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
				$config['leader_result'] = $default_leader;
			} else {
				foreach ( array(
					'focus_copy',
					'focus_section_heading',
					'focus_practice_heading',
					'heatmap_section_heading',
					'heatmap_card_title',
					'heatmap_card_help',
					'rollout_section_heading',
					'rollout_intro',
					'rollout_rollout_cta',
					'rollout_locked_items',
					'help_support_heading',
					'next_step_blocks',
					'hero_next_step_heading',
				) as $key ) {
					if ( ! empty( $default_leader[ $key ] ) ) {
						$config['leader_result'][ $key ] = $default_leader[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 18;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 19 ) {
			$default_leader = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
				$config['leader_result'] = $default_leader;
			} else {
				foreach ( array(
					'peer_comparison_label_short',
					'peer_phase_short',
					'peer_gap_below_top_short',
					'urgent_action_heading_short',
					'focus_section_heading_short',
					'focus_practice_heading_short',
					'heatmap_card_title_short',
					'rollout_section_heading_short',
					'rollout_intro_short',
				) as $key ) {
					if ( ! empty( $default_leader[ $key ] ) ) {
						$config['leader_result'][ $key ] = $default_leader[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 19;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 20 ) {
			$default_leader = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
				$config['leader_result'] = $default_leader;
			} else {
				foreach ( array(
					'copy_tiers',
					'focus_tiers',
					'priority_scenarios',
					'rollout_tiers',
					'readiness_cta_tiers',
				) as $key ) {
					if ( ! empty( $default_leader[ $key ] ) ) {
						$config['leader_result'][ $key ] = $default_leader[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 20;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 21 ) {
			$default_leader = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
				$config['leader_result'] = $default_leader;
			} else {
				foreach ( array(
					'help_support_heading',
					'help_support_heading_short',
				) as $key ) {
					if ( ! empty( $default_leader[ $key ] ) ) {
						$config['leader_result'][ $key ] = $default_leader[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 21;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 22 ) {
			$config['questions'] = AIRB_Questions::all();
			$default_leader    = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
				$config['leader_result'] = $default_leader;
			} else {
				foreach ( array(
					'bias_health_title',
					'bias_health_subtitle',
					'bias_health_callout_threshold',
					'bias_health_callout',
				) as $key ) {
					if ( ! empty( $default_leader[ $key ] ) ) {
						$config['leader_result'][ $key ] = $default_leader[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 22;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 23 ) {
			$default_teacher = (array) ( $defaults['teacher_result'] ?? array() );
			if ( ! isset( $config['teacher_result'] ) || ! is_array( $config['teacher_result'] ) ) {
				$config['teacher_result'] = $default_teacher;
			} else {
				foreach ( array(
					'copy_tiers',
					'domain_descriptions',
					'strength_tiers',
					'focus_tiers',
					'focus_actions_heading',
					'rollout_tiers',
					'readiness_cta_tiers',
					'headlines',
					'rollout_locked_items',
					'oversight_metric_note',
					'oversight_card_suffix',
					'domains_section_heading',
					'domains_section_heading_short',
					'strengths_heading_short',
				) as $key ) {
					if ( ! empty( $default_teacher[ $key ] ) ) {
						$config['teacher_result'][ $key ] = $default_teacher[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 23;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 24 ) {
			$default_student = (array) ( $defaults['student_result'] ?? array() );
			if ( ! isset( $config['student_result'] ) || ! is_array( $config['student_result'] ) ) {
				$config['student_result'] = $default_student;
			} else {
				foreach ( array(
					'copy_tiers',
					'strength_tiers',
					'focus_tiers',
					'focus_label_map',
					'hero_metric_label',
					'skills_section_heading',
					'skills_section_heading_short',
					'focus_section_heading',
					'focus_section_heading_short',
					'resources_section_heading',
					'resources_section_heading_short',
					'resources_section_intro',
					'share_section_kicker',
					'share_section_title',
					'share_section_body',
					'share_count_label',
					'share_unlock_label',
					'share_unlock_value',
					'share_cta_primary',
					'share_cta_secondary',
					'strengths_heading_short',
					'profile_title',
					'student_resources',
				) as $key ) {
					if ( ! empty( $default_student[ $key ] ) ) {
						$config['student_result'][ $key ] = $default_student[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 24;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 25 ) {
			$default_parent = (array) ( $defaults['parent_result'] ?? array() );
			if ( ! isset( $config['parent_result'] ) || ! is_array( $config['parent_result'] ) ) {
				$config['parent_result'] = $default_parent;
			} else {
				foreach ( array(
					'copy_tiers',
					'home_metrics',
					'focus_tiers',
					'focus_slug_map',
					'conversation_starters',
					'profile_title',
					'hero_metric_label',
					'metrics_section_heading',
					'metrics_section_heading_short',
					'focus_section_heading',
					'focus_section_heading_short',
					'conversation_section_heading',
					'conversation_section_heading_short',
					'conversation_section_intro',
					'share_section_kicker',
					'share_section_title',
					'share_section_body',
					'share_cta_primary',
					'share_cta_secondary',
					'display_domains',
				) as $key ) {
					if ( ! empty( $default_parent[ $key ] ) ) {
						$config['parent_result'][ $key ] = $default_parent[ $key ];
						$changed                        = true;
					}
				}
			}
			$config['version'] = 25;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 26 ) {
			$default_support = (array) ( $defaults['support_result'] ?? array() );
			if ( ! isset( $config['support_result'] ) || ! is_array( $config['support_result'] ) ) {
				$config['support_result'] = $default_support;
			} else {
				foreach ( array(
					'copy_tiers',
					'metric_signals',
					'strength_tiers',
					'focus_tiers',
					'domain_rows',
					'cta_hero',
					'profile_title',
					'hero_metric_label',
					'domains_section_heading',
					'domains_section_heading_short',
					'focus_section_heading',
					'focus_section_heading_short',
					'rollout_section_heading',
					'rollout_section_heading_short',
					'rollout_intro',
					'rollout_intro_short',
					'rollout_rollout_cta',
					'rollout_locked_items',
					'strengths_heading_short',
					'help_support_heading',
					'help_support_heading_short',
					'opportunity_copy',
				) as $key ) {
					if ( ! empty( $default_support[ $key ] ) ) {
						$config['support_result'][ $key ] = $default_support[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 26;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 27 ) {
			$default_leader = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
				$config['leader_result'] = $default_leader;
			} else {
				foreach ( array(
					'copy_tiers',
					'focus_tiers',
				) as $key ) {
					if ( ! empty( $default_leader[ $key ] ) ) {
						$config['leader_result'][ $key ] = $default_leader[ $key ];
						$changed                         = true;
					}
				}
				if ( ! empty( $default_leader['metric_labels']['bias'] ) ) {
					if ( ! isset( $config['leader_result']['metric_labels'] ) || ! is_array( $config['leader_result']['metric_labels'] ) ) {
						$config['leader_result']['metric_labels'] = array();
					}
					$config['leader_result']['metric_labels']['bias'] = $default_leader['metric_labels']['bias'];
					$changed                                          = true;
				}
			}
			$config['version'] = 27;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 28 ) {
			$config['questions'] = AIRB_Questions::all();
			$default_student     = (array) ( $defaults['student_result'] ?? array() );
			if ( ! isset( $config['student_result'] ) || ! is_array( $config['student_result'] ) ) {
				$config['student_result'] = $default_student;
			} else {
				foreach ( array(
					'copy_tiers',
					'focus_tiers',
					'metric_labels',
					'bias_health_title',
					'bias_health_subtitle',
					'bias_health_callout_threshold',
					'bias_health_callout',
					'bias_section_heading',
					'bias_section_heading_short',
					'skills_section_heading',
					'skills_section_heading_short',
					'strength_tiers',
					'focus_label_map',
					'opportunity_topics',
					'journey_focus_map',
				) as $key ) {
					if ( ! empty( $default_student[ $key ] ) ) {
						$config['student_result'][ $key ] = $default_student[ $key ];
						$changed                          = true;
					}
				}
			}
			$config['version'] = 28;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 29 ) {
			$default_student = (array) ( $defaults['student_result'] ?? array() );
			if ( ! isset( $config['student_result'] ) || ! is_array( $config['student_result'] ) ) {
				$config['student_result'] = $default_student;
			} elseif ( ! empty( $default_student['journey_levels'] ) ) {
				$config['student_result']['journey_levels'] = $default_student['journey_levels'];
				$changed                                    = true;
			}
			$config['version'] = 29;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 30 ) {
			$default_student = (array) ( $defaults['student_result'] ?? array() );
			if ( ! isset( $config['student_result'] ) || ! is_array( $config['student_result'] ) ) {
				$config['student_result'] = $default_student;
			} else {
				foreach ( array( 'help_support_heading', 'help_support_heading_short' ) as $key ) {
					if ( ! empty( $default_student[ $key ] ) ) {
						$config['student_result'][ $key ] = $default_student[ $key ];
						$changed                          = true;
					}
				}
			}
			$config['version'] = 30;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 31 ) {
			if ( ! empty( $config['student_result']['journey_levels'] ) && is_array( $config['student_result']['journey_levels'] ) ) {
				foreach ( $config['student_result']['journey_levels'] as $idx => $level ) {
					if ( ! is_array( $level ) || 'developing' !== ( $level['slug'] ?? '' ) ) {
						continue;
					}
					$config['student_result']['journey_levels'][ $idx ]['label'] = __( 'Building', 'ai-risk-benchmark' );
					$changed                                                     = true;
				}
			}
			$config['version'] = 31;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 32 ) {
			$default_student = (array) ( $defaults['student_result'] ?? array() );
			if ( ! isset( $config['student_result'] ) || ! is_array( $config['student_result'] ) ) {
				$config['student_result'] = $default_student;
			} else {
				foreach ( array( 'skills_section_heading', 'skills_section_heading_short' ) as $key ) {
					if ( ! empty( $default_student[ $key ] ) ) {
						$config['student_result'][ $key ] = $default_student[ $key ];
						$changed                          = true;
					}
				}
			}
			$config['version'] = 32;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 33 ) {
			$default_student = (array) ( $defaults['student_result'] ?? array() );
			if ( ! isset( $config['student_result'] ) || ! is_array( $config['student_result'] ) ) {
				$config['student_result'] = $default_student;
			} else {
				foreach ( array(
					'retake_at_risk_threshold',
					'retake_at_risk_heading',
					'retake_at_risk_body',
					'retake_body_default',
				) as $key ) {
					if ( array_key_exists( $key, $default_student ) ) {
						$config['student_result'][ $key ] = $default_student[ $key ];
						$changed                          = true;
					}
				}
			}
			$config['version'] = 33;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 34 ) {
			$config['questions'] = AIRB_Questions::all();
			$default_teacher = (array) ( $defaults['teacher_result'] ?? array() );
			if ( ! isset( $config['teacher_result'] ) || ! is_array( $config['teacher_result'] ) ) {
				$config['teacher_result'] = $default_teacher;
			} else {
				foreach ( array(
					'copy_tiers',
					'focus_tiers',
					'metric_labels',
					'bias_health_title',
					'bias_health_subtitle',
					'bias_health_callout_threshold',
					'bias_health_callout',
				) as $key ) {
					if ( ! empty( $default_teacher[ $key ] ) ) {
						$config['teacher_result'][ $key ] = $default_teacher[ $key ];
						$changed                          = true;
					}
				}
			}
			$config['version'] = 34;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 35 ) {
			$role_band_keys = array(
				'teacher_result' => array( 'hero_bands' ),
				'leader_result'  => array( 'hero_bands' ),
				'support_result' => array( 'hero_bands' ),
				'student_result' => array( 'journey_levels' ),
				'parent_result'  => array( 'awareness_levels' ),
			);
			foreach ( $role_band_keys as $role_key => $keys ) {
				$default_role = (array) ( $defaults[ $role_key ] ?? array() );
				if ( ! isset( $config[ $role_key ] ) || ! is_array( $config[ $role_key ] ) ) {
					$config[ $role_key ] = $default_role;
					$changed             = true;
					continue;
				}
				foreach ( $keys as $key ) {
					if ( ! empty( $default_role[ $key ] ) ) {
						$config[ $role_key ][ $key ] = $default_role[ $key ];
						$changed                     = true;
					}
				}
			}
			$config['version'] = 35;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 36 ) {
			$role_copy_keys = array(
				'student_result' => array( 'copy_tiers', 'retake_at_risk_heading' ),
				'parent_result'  => array( 'copy_tiers' ),
			);
			foreach ( $role_copy_keys as $role_key => $keys ) {
				$default_role = (array) ( $defaults[ $role_key ] ?? array() );
				if ( ! isset( $config[ $role_key ] ) || ! is_array( $config[ $role_key ] ) ) {
					$config[ $role_key ] = $default_role;
					$changed             = true;
					continue;
				}
				foreach ( $keys as $key ) {
					if ( ! empty( $default_role[ $key ] ) ) {
						$config[ $role_key ][ $key ] = $default_role[ $key ];
						$changed                     = true;
					}
				}
			}
			$config['version'] = 36;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 37 ) {
			$config['questions'] = AIRB_Questions::all();
			$default_public      = AIRB_Defaults::public_result_config();
			if ( ! isset( $config['public_result'] ) || ! is_array( $config['public_result'] ) ) {
				$config['public_result'] = $default_public;
			} else {
				foreach ( array( 'display_domains', 'section_metrics', 'domain_weights', 'hero_bands', 'copy_tiers', 'focus_topics' ) as $key ) {
					if ( ! empty( $default_public[ $key ] ) ) {
						$config['public_result'][ $key ] = $default_public[ $key ];
						$changed                         = true;
					}
				}
			}
			$config['version'] = 37;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 38 ) {
			$config['questions'] = AIRB_Questions::all();
			$config['version']   = 38;
			$changed             = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 39 ) {
			$default_public = AIRB_Defaults::public_result_config();
			if ( ! isset( $config['public_result'] ) || ! is_array( $config['public_result'] ) ) {
				$config['public_result'] = $default_public;
			} else {
				foreach ( array_keys( $default_public ) as $key ) {
					if ( ! empty( $default_public[ $key ] ) ) {
						$config['public_result'][ $key ] = $default_public[ $key ];
					}
				}
			}
			$config['version'] = 39;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 40 ) {
			$role_keys = array( 'teacher_result', 'leader_result', 'student_result', 'parent_result', 'support_result', 'public_result' );
			foreach ( $role_keys as $role_key ) {
				$default_role = (array) ( $defaults[ $role_key ] ?? array() );
				if ( empty( $default_role ) ) {
					continue;
				}
				if ( ! isset( $config[ $role_key ] ) || ! is_array( $config[ $role_key ] ) ) {
					$config[ $role_key ] = $default_role;
					$changed             = true;
					continue;
				}
				foreach ( array( 'copy_tiers', 'focus_tiers', 'focus_topics' ) as $key ) {
					if ( ! empty( $default_role[ $key ] ) ) {
						$config[ $role_key ][ $key ] = $default_role[ $key ];
						$changed                   = true;
					}
				}
			}
			$config['version'] = 40;
			$changed           = true;
			AIRB_Copy_Tiers::export_missing_json_files();
		}

		if ( (int) ( $config['version'] ?? 0 ) < 41 ) {
			AIRB_Copy_Tiers::export_missing_json_files();
			$config['version'] = 41;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 42 ) {
			$default_leader = (array) ( $defaults['leader_result'] ?? array() );
			if ( ! empty( $default_leader ) ) {
				if ( ! isset( $config['leader_result'] ) || ! is_array( $config['leader_result'] ) ) {
					$config['leader_result'] = $default_leader;
				} else {
					foreach ( array( 'copy_tiers', 'focus_tiers', 'focus_topics' ) as $key ) {
						if ( ! empty( $default_leader[ $key ] ) ) {
							$config['leader_result'][ $key ] = $default_leader[ $key ];
						}
					}
				}
			}
			$config['version'] = 42;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 43 ) {
			$config['questions'] = AIRB_Questions::all();
			foreach ( array( 'domain_sources', 'domain_descriptions' ) as $key ) {
				$default_map = (array) ( $defaults[ $key ] ?? array() );
				if ( ! isset( $config[ $key ] ) || ! is_array( $config[ $key ] ) ) {
					$config[ $key ] = $default_map;
					continue;
				}
				foreach ( $default_map as $slug => $value ) {
					if ( ! isset( $config[ $key ][ $slug ] ) ) {
						$config[ $key ][ $slug ] = $value;
					}
				}
			}
			$config['version'] = 43;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 44 ) {
			$default_teacher = (array) ( $defaults['teacher_result'] ?? array() );
			if ( ! empty( $default_teacher['dashboard'] ) ) {
				if ( ! isset( $config['teacher_result'] ) || ! is_array( $config['teacher_result'] ) ) {
					$config['teacher_result'] = $default_teacher;
				} else {
					$config['teacher_result']['dashboard'] = $default_teacher['dashboard'];
				}
			}
			$config['version'] = 44;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < 45 ) {
			$role_keys = array( 'teacher_result', 'leader_result', 'student_result', 'parent_result', 'support_result', 'public_result' );
			foreach ( $role_keys as $role_key ) {
				$default_role = (array) ( $defaults[ $role_key ] ?? array() );
				if ( empty( $default_role['dashboard'] ) ) {
					continue;
				}
				if ( ! isset( $config[ $role_key ] ) || ! is_array( $config[ $role_key ] ) ) {
					$config[ $role_key ] = $default_role;
				} else {
					$config[ $role_key ]['dashboard'] = $default_role['dashboard'];
				}
			}
			$config['version'] = 45;
			$changed           = true;
		}

		if ( (int) ( $config['version'] ?? 0 ) < (int) ( $defaults['version'] ?? 0 ) ) {
			$config['version'] = (int) $defaults['version'];
			$changed           = true;
		}

		if ( $changed ) {
			update_option( AIRB_OPTION_CONFIG, $config, false );
		}
	}

	/**
	 * Point policy and data-protection CTAs at official DfE publications.
	 *
	 * @param array<string, mixed> $config Config (by reference).
	 */
	private static function patch_dfe_cta_urls( array &$config ): void {
		$policy_url    = AIRB_Defaults::dfe_url_using_ai();
		$checklist_url = AIRB_Defaults::dfe_url_generative_ai();

		$patch_offer = static function ( array &$item ) use ( $policy_url, $checklist_url ): void {
			$title = (string) ( $item['title'] ?? '' );
			$id    = (string) ( $item['id'] ?? '' );

			if ( 'leader_no_policy' === $id
				|| false !== stripos( $title, 'AI policy' ) ) {
				$item['cta_url'] = $policy_url;
			}

			if ( false !== stripos( $title, 'Data Protection Checklist' ) ) {
				$item['cta_url'] = $checklist_url;
			}
		};

		foreach ( array( 'recommendations', 'pathway_offers' ) as $list_key ) {
			if ( empty( $config[ $list_key ] ) || ! is_array( $config[ $list_key ] ) ) {
				continue;
			}
			foreach ( $config[ $list_key ] as &$item ) {
				if ( is_array( $item ) ) {
					$patch_offer( $item );
				}
			}
			unset( $item );
		}

		if ( ! empty( $config['services']['items'] ) && is_array( $config['services']['items'] ) ) {
			foreach ( $config['services']['items'] as &$service ) {
				if ( ! is_array( $service ) ) {
					continue;
				}
				if ( false !== stripos( (string) ( $service['label'] ?? '' ), 'AI Policy' ) ) {
					$service['url'] = $policy_url;
				}
			}
			unset( $service );
		}
	}

	/**
	 * Get full config.
	 *
	 * @return array<string, mixed>
	 */
	public static function get(): array {
		self::maybe_upgrade_config();
		$config = get_option( AIRB_OPTION_CONFIG );
		if ( ! is_array( $config ) || empty( $config['questions'] ) ) {
			$config = AIRB_Defaults::config();
			update_option( AIRB_OPTION_CONFIG, $config, false );
		}
		return $config;
	}

	/**
	 * Save config (admin).
	 *
	 * @param array<string, mixed> $config Config array.
	 */
	public static function save( array $config ): void {
		update_option( AIRB_OPTION_CONFIG, $config, false );
	}

	/**
	 * Questions for a role.
	 *
	 * @param string $role Role slug.
	 * @return array<int, array<string, mixed>>
	 */
	public static function questions_for_role( string $role ): array {
		$config = self::get();
		$out    = array();
		foreach ( (array) ( $config['questions'] ?? array() ) as $q ) {
			if ( isset( $q['role'] ) && $q['role'] === $role ) {
				$out[] = $q;
			}
		}
		return $out;
	}

	/**
	 * Public config for front-end (strip internal fields if needed).
	 *
	 * @return array<string, mixed>
	 */
	public static function public_config(): array {
		$config = self::get();

		$cta = (array) ( $config['consultation_cta'] ?? array() );
		$cta['url'] = AIRB_Defaults::interest_form_url( 'governance_review' );

		return array(
			'disclaimer'          => (string) ( $config['disclaimer'] ?? '' ),
			'intro'               => (string) ( $config['intro'] ?? '' ),
			'positioning'         => $config['positioning'] ?? array(),
			'framework'           => $config['framework'] ?? array(),
			'domain_sources'      => $config['domain_sources'] ?? array(),
			'domain_descriptions' => $config['domain_descriptions'] ?? array(),
			'roles'                 => AIRB_Defaults::roles(),
			'domains'               => AIRB_Defaults::domains(),
			'domain_colors'         => AIRB_Defaults::domain_colors(),
			'domain_recommendations'=> AIRB_Defaults::domain_recommendations(),
			'parent_result'         => AIRB_Defaults::parent_result_config(),
			'teacher_result'        => AIRB_Defaults::teacher_result_config(),
			'student_result'        => AIRB_Defaults::student_result_config(),
			'public_result'         => AIRB_Defaults::public_result_config(),
			'leader_result'         => AIRB_Defaults::leader_result_config(),
			'support_result'        => AIRB_Defaults::support_result_config(),
			'improvement_hub'       => AIRB_Defaults::improvement_hub_config(),
			'role_meta'             => AIRB_Defaults::role_meta(),
			'copy_tiers'            => AIRB_Copy_Tiers::registry_for_js(),
			'questions'           => $config['questions'] ?? array(),
			'recommendations'     => $config['recommendations'] ?? array(),
			'guidance_refs'       => $config['guidance_refs'] ?? array(),
			'consultation_cta'    => $cta,
			'role_benchmarks'     => $config['role_benchmarks'] ?? array(),
			'signature_metrics'   => $config['signature_metrics'] ?? array(),
			'after_audit'         => $config['after_audit'] ?? array(),
		);
	}
}
