<?php
/**
 * Field Registry System
 * Centralized configuration for resource meta fields
 * Reduces hardcoding by defining fields once, rendering dynamically
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get field registry configuration
 * Defines all resource meta fields in one place
 *
 * @return array Field configuration array
 */
function aiad_get_field_registry(): array {
    return array(
        'resource_details' => array(
            '_aiad_subtitle' => array(
                'type'        => 'text',
                'label'       => __( 'Subtitle', 'ai-awareness-day' ),
                'description' => __( 'Max 120 characters.', 'ai-awareness-day' ),
                'placeholder' => __( 'One sentence: what students will actually do', 'ai-awareness-day' ),
                'maxlength'   => 120,
                'class'       => 'large-text',
                'meta_key'    => '_aiad_subtitle',
            ),
            '_aiad_duration' => array(
                'type'        => 'text',
                'label'       => __( 'Duration', 'ai-awareness-day' ),
                'description' => __( 'Be specific. Not "as specified".', 'ai-awareness-day' ),
                'placeholder' => __( 'e.g. 5 min, 45 min, 2 x 50 min sessions', 'ai-awareness-day' ),
                'class'       => 'regular-text',
                'meta_key'    => '_aiad_duration',
            ),
            '_aiad_level' => array(
                'type'        => 'radio',
                'label'       => __( 'Level', 'ai-awareness-day' ),
                'options'     => array(
                    'beginner'    => __( 'Beginner', 'ai-awareness-day' ),
                    'intermediate' => __( 'Intermediate', 'ai-awareness-day' ),
                    'advanced'    => __( 'Advanced', 'ai-awareness-day' ),
                ),
                'meta_key'    => '_aiad_level',
            ),
            '_aiad_status' => array(
                'type'        => 'radio',
                'label'       => __( 'Status', 'ai-awareness-day' ),
                'options'     => array(
                    'draft'     => __( 'Draft', 'ai-awareness-day' ),
                    'in_review' => __( 'In review', 'ai-awareness-day' ),
                    'published' => __( 'Published', 'ai-awareness-day' ),
                ),
                'meta_key'    => '_aiad_status',
                'default'     => 'draft',
            ),
        ),
        'content_sections' => array(
            '_aiad_preparation' => array(
                'type'        => 'repeatable_text',
                'label'       => __( 'Preparation', 'ai-awareness-day' ),
                'description' => __( 'What the teacher must have ready before the lesson. One concrete action per item.', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_preparation',
                'add_button'  => __( 'Add item', 'ai-awareness-day' ),
            ),
            '_aiad_key_definitions' => array(
                'type'        => 'repeatable_object',
                'label'       => __( 'Key definitions', 'ai-awareness-day' ),
                'description' => __( 'Term, definition; tick if simplified for key stage.', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_key_definitions',
                'fields'      => array(
                    'term'              => array( 'type' => 'text', 'label' => __( 'Term', 'ai-awareness-day' ) ),
                    'definition'        => array( 'type' => 'textarea', 'label' => __( 'Definition', 'ai-awareness-day' ), 'rows' => 2 ),
                    'key_stage_adapted' => array( 'type' => 'checkbox', 'label' => __( 'Key stage adapted', 'ai-awareness-day' ) ),
                ),
                'add_button'  => __( 'Add definition', 'ai-awareness-day' ),
            ),
            '_aiad_learning_objectives' => array(
                'type'        => 'repeatable_object',
                'label'       => __( 'Learning objectives', 'ai-awareness-day' ),
                'description' => __( 'Start with a Bloom\'s verb (understand, recognise, analyse, evaluate, create, apply). Min 2, max 5.', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_learning_objectives',
                'fields'      => array(
                    'objective'  => array( 'type' => 'text', 'label' => __( 'Objective', 'ai-awareness-day' ), 'placeholder' => __( 'e.g. Understand that AI predicts patterns', 'ai-awareness-day' ) ),
                    'assessable' => array( 'type' => 'checkbox', 'label' => __( 'Assessable', 'ai-awareness-day' ) ),
                ),
                'add_button'  => __( 'Add objective', 'ai-awareness-day' ),
                'min'         => 2,
                'max'         => 5,
            ),
            '_aiad_instructions' => array(
                'type'        => 'repeatable_object',
                'label'       => __( 'Instructions', 'ai-awareness-day' ),
                'description' => __( 'Teacher script. At least one step should have a duration. Min 2 steps.', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_instructions',
                'fields'      => array(
                    'step'          => array( 'type' => 'number', 'label' => __( 'Step', 'ai-awareness-day' ), 'min' => 1 ),
                    'action'        => array( 'type' => 'textarea', 'label' => __( 'Action', 'ai-awareness-day' ), 'rows' => 2 ),
                    'duration'      => array( 'type' => 'text', 'label' => __( 'Duration', 'ai-awareness-day' ), 'placeholder' => 'e.g. 60 seconds' ),
                    'resource_ref'  => array( 'type' => 'text', 'label' => __( 'Resource ref', 'ai-awareness-day' ), 'placeholder' => 'e.g. Slide 6' ),
                    'student_action'=> array( 'type' => 'text', 'label' => __( 'Student action', 'ai-awareness-day' ), 'placeholder' => 'e.g. Pair discussion' ),
                    'teacher_tip'   => array( 'type' => 'textarea', 'label' => __( 'Teacher tip', 'ai-awareness-day' ), 'rows' => 1 ),
                ),
                'add_button'  => __( 'Add step', 'ai-awareness-day' ),
                'min'         => 2,
            ),
            '_aiad_discussion_question' => array(
                'type'        => 'text',
                'label'       => __( 'Discussion question', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_discussion_question',
                'class'       => 'large-text',
            ),
            '_aiad_discussion_prompts' => array(
                'type'        => 'repeatable_text',
                'label'       => __( 'Discussion prompts', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_discussion_prompts',
                'add_button'  => __( 'Add prompt', 'ai-awareness-day' ),
            ),
            '_aiad_teacher_notes' => array(
                'type'        => 'textarea',
                'label'       => __( 'Teacher notes (optional)', 'ai-awareness-day' ),
                'description' => __( 'Optional background, misconceptions, and tips for teachers. This does not affect publishing.', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_teacher_notes',
                'rows'        => 5,
                'class'       => 'large-text',
            ),
            '_aiad_differentiation' => array(
                'type'        => 'object',
                'label'       => __( 'Differentiation', 'ai-awareness-day' ),
                'description' => __( 'How to adapt for different learners.', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_differentiation',
                'fields'      => array(
                    'support' => array( 'type' => 'textarea', 'label' => __( 'Support (struggling)', 'ai-awareness-day' ), 'rows' => 2 ),
                    'stretch' => array( 'type' => 'textarea', 'label' => __( 'Stretch (high ability)', 'ai-awareness-day' ), 'rows' => 2 ),
                    'send'    => array( 'type' => 'textarea', 'label' => __( 'SEND (additional needs)', 'ai-awareness-day' ), 'rows' => 2 ),
                ),
            ),
            '_aiad_extensions' => array(
                'type'        => 'repeatable_object',
                'label'       => __( 'Extension activities', 'ai-awareness-day' ),
                'description' => __( 'Specific, actionable task + type.', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_extensions',
                'fields'      => array(
                    'activity' => array( 'type' => 'text', 'label' => __( 'Activity', 'ai-awareness-day' ), 'placeholder' => __( 'Specific task', 'ai-awareness-day' ) ),
                    'type'     => array( 'type' => 'select', 'label' => __( 'Type', 'ai-awareness-day' ), 'options' => array(
                        'homework'        => __( 'Homework', 'ai-awareness-day' ),
                        'next_lesson'     => __( 'Next lesson', 'ai-awareness-day' ),
                        'cross_curricular' => __( 'Cross-curricular', 'ai-awareness-day' ),
                        'independent'     => __( 'Independent', 'ai-awareness-day' ),
                    ) ),
                ),
                'add_button'  => __( 'Add extension', 'ai-awareness-day' ),
            ),
            '_aiad_resources' => array(
                'type'        => 'repeatable_object',
                'label'       => __( 'Resources', 'ai-awareness-day' ),
                'description' => __( 'Slide deck, worksheet, etc. (optional link).', 'ai-awareness-day' ),
                'meta_key'    => '_aiad_resources',
                'fields'      => array(
                    'name' => array( 'type' => 'text', 'label' => __( 'Name', 'ai-awareness-day' ), 'placeholder' => __( 'e.g. Slide deck', 'ai-awareness-day' ) ),
                    'type' => array( 'type' => 'select', 'label' => __( 'Type', 'ai-awareness-day' ), 'options' => array(
                        'slides'   => __( 'Slides', 'ai-awareness-day' ),
                        'worksheet' => __( 'Worksheet', 'ai-awareness-day' ),
                        'handout'  => __( 'Handout', 'ai-awareness-day' ),
                        'video'    => __( 'Video', 'ai-awareness-day' ),
                        'link'     => __( 'Link', 'ai-awareness-day' ),
                        'other'    => __( 'Other', 'ai-awareness-day' ),
                    ) ),
                    'url'  => array( 'type' => 'url', 'label' => __( 'URL', 'ai-awareness-day' ), 'placeholder' => 'URL' ),
                ),
                'add_button'  => __( 'Add resource', 'ai-awareness-day' ),
            ),
        ),
    );
}

/**
 * Get field configuration by meta key
 *
 * @param string $meta_key Meta key to look up
 * @return array|null Field configuration or null if not found
 */
function aiad_get_field_config( string $meta_key ): ?array {
    $registry = aiad_get_field_registry();
    foreach ( $registry as $section => $fields ) {
        if ( isset( $fields[ $meta_key ] ) ) {
            return $fields[ $meta_key ];
        }
    }
    return null;
}

/**
 * Get all fields for a section
 *
 * @param string $section Section name ('resource_details' or 'content_sections')
 * @return array Field configurations
 */
function aiad_get_section_fields( string $section ): array {
    $registry = aiad_get_field_registry();
    return $registry[ $section ] ?? array();
}

/**
 * Render a single field based on its configuration
 *
 * @param array  $config Field configuration from registry
 * @param mixed  $value  Current field value
 * @param string $name   Form field name (without prefix)
 * @return string Rendered HTML
 */
function aiad_render_field( array $config, $value, string $name = '' ): string {
    if ( empty( $name ) ) {
        $name = str_replace( '_aiad_', 'aiad_', $config['meta_key'] );
    }

    $type = $config['type'] ?? 'text';
    $label = $config['label'] ?? '';
    $description = $config['description'] ?? '';
    $class = $config['class'] ?? 'regular-text';
    $placeholder = $config['placeholder'] ?? '';

    $html = '<div class="aiad-rd-section">';
    
    if ( ! empty( $label ) ) {
        $html .= '<strong class="aiad-rd-label">' . esc_html( $label ) . '</strong>';
    }

    switch ( $type ) {
        case 'text':
            $attrs = array(
                'type'        => 'text',
                'name'        => esc_attr( $name ),
                'value'       => esc_attr( $value ),
                'class'       => esc_attr( $class ),
            );
            if ( ! empty( $placeholder ) ) {
                $attrs['placeholder'] = esc_attr( $placeholder );
            }
            if ( isset( $config['maxlength'] ) ) {
                $attrs['maxlength'] = (int) $config['maxlength'];
            }
            $attrs_str = '';
            foreach ( $attrs as $k => $v ) {
                $attrs_str .= ' ' . $k . '="' . $v . '"';
            }
            $html .= '<input' . $attrs_str . ' />';
            break;

        case 'textarea':
            $rows = $config['rows'] ?? 3;
            $html .= '<textarea name="' . esc_attr( $name ) . '" rows="' . esc_attr( $rows ) . '" class="' . esc_attr( $class ) . '" style="width:100%;">' . esc_textarea( $value ) . '</textarea>';
            break;

        case 'radio':
            $html .= '<div class="aiad-rd-radios">';
            $options = $config['options'] ?? array();
            foreach ( $options as $option_value => $option_label ) {
                $checked = checked( $value, $option_value, false );
                $html .= '<label><input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $option_value ) . '" ' . $checked . ' /> ' . esc_html( $option_label ) . '</label> ';
            }
            $html .= '</div>';
            break;

        case 'select':
            $html .= '<select name="' . esc_attr( $name ) . '" class="' . esc_attr( $class ) . '">';
            $options = $config['options'] ?? array();
            foreach ( $options as $option_value => $option_label ) {
                $selected = selected( $value, $option_value, false );
                $html .= '<option value="' . esc_attr( $option_value ) . '" ' . $selected . '>' . esc_html( $option_label ) . '</option>';
            }
            $html .= '</select>';
            break;

        case 'checkbox':
            $checked = checked( ! empty( $value ), true, false );
            $html .= '<label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . $checked . ' /> ' . esc_html( $label ) . '</label>';
            break;

        case 'number':
            $min = $config['min'] ?? '';
            $attrs = 'name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"';
            if ( $min !== '' ) {
                $attrs .= ' min="' . esc_attr( $min ) . '"';
            }
            if ( isset( $config['max'] ) ) {
                $attrs .= ' max="' . esc_attr( $config['max'] ) . '"';
            }
            $html .= '<input type="number" ' . $attrs . ' />';
            break;

        case 'url':
            $html .= '<input type="url" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="' . esc_attr( $class ) . '" placeholder="' . esc_attr( $placeholder ) . '" />';
            break;
    }

    if ( ! empty( $description ) ) {
        $html .= '<p class="description">' . esc_html( $description ) . '</p>';
    }

    $html .= '</div>';
    return $html;
}

/**
 * Render repeatable text field
 *
 * @param array  $config Field configuration
 * @param array  $values Current values array
 * @param string $name   Form field name prefix
 * @return string Rendered HTML
 */
function aiad_render_repeatable_text_field( array $config, array $values, string $name ): string {
    $label = $config['label'] ?? '';
    $description = $config['description'] ?? '';
    $add_button = $config['add_button'] ?? __( 'Add item', 'ai-awareness-day' );

    if ( empty( $values ) ) {
        $values = array( '' );
    }

    $html = '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;">';
    $html .= '<strong class="aiad-rd-label">' . esc_html( $label ) . '</strong>';
    
    if ( ! empty( $description ) ) {
        $html .= '<p class="description">' . esc_html( $description ) . '</p>';
    }

    $html .= '<div class="aiad-repeatable-list" data-name="' . esc_attr( $name ) . '">';
    foreach ( $values as $i => $val ) {
        $val = is_string( $val ) ? $val : '';
        $html .= '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;">';
        $html .= '<input type="text" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $val ) . '" class="large-text" /> ';
        $html .= '<button type="button" class="button button-small aiad-remove-row">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '<button type="button" class="button aiad-add-row" data-name="' . esc_attr( $name ) . '">' . esc_html( $add_button ) . '</button>';
    $html .= '</div>';

    return $html;
}

/**
 * Render repeatable object field (for complex nested structures)
 *
 * @param array  $config Field configuration
 * @param array  $values Current values array
 * @param string $name   Form field name prefix
 * @return string Rendered HTML
 */
function aiad_render_repeatable_object_field( array $config, array $values, string $name ): string {
    $label = $config['label'] ?? '';
    $description = $config['description'] ?? '';
    $add_button = $config['add_button'] ?? __( 'Add item', 'ai-awareness-day' );
    $fields = $config['fields'] ?? array();
    $special_class = '';

    // Special handling for different field types
    if ( $name === 'aiad_key_definitions' ) {
        $special_class = 'aiad-repeatable-definitions';
    } elseif ( $name === 'aiad_instructions' ) {
        $special_class = 'aiad-instruction-steps';
    }

    if ( empty( $values ) ) {
        // Create default empty structure
        $default_item = array();
        foreach ( $fields as $field_key => $field_config ) {
            $default_item[ $field_key ] = '';
        }
        $values = array( $default_item );
    }

    $html = '<div class="aiad-cs-field' . ( $special_class ? ' ' . esc_attr( $special_class ) : '' ) . '" style="margin-bottom: 1.5rem;">';
    $html .= '<strong class="aiad-rd-label">' . esc_html( $label ) . '</strong>';
    
    if ( ! empty( $description ) ) {
        $html .= '<p class="description">' . esc_html( $description ) . '</p>';
    }

    $list_class = 'aiad-repeatable-list';
    if ( $name === 'aiad_key_definitions' ) {
        $list_class = 'aiad-repeatable-rows';
    } elseif ( $name === 'aiad_instructions' ) {
        $list_class = 'aiad-repeatable-list aiad-instruction-steps';
    }

    $html .= '<div class="' . esc_attr( $list_class ) . '"' . ( $name === 'aiad_key_definitions' ? ' data-name-prefix="' . esc_attr( $name ) . '"' : ' data-name="' . esc_attr( $name ) . '"' ) . '>';

    foreach ( $values as $i => $item ) {
        if ( ! is_array( $item ) ) {
            $item = array();
        }

        $row_class = 'aiad-repeatable-row';
        $row_style = 'margin-bottom: 0.5rem;';
        
        if ( $name === 'aiad_key_definitions' ) {
            $row_style = 'margin-bottom: 0.75rem; padding: 0.5rem; background: #f6f7f7; border-radius: 4px;';
        } elseif ( $name === 'aiad_instructions' ) {
            $row_class = 'aiad-repeatable-row aiad-instruction-row';
            $row_style = 'margin-bottom: 1rem; padding: 0.75rem; background: #f6f7f7; border-radius: 4px;';
        } elseif ( $name === 'aiad_learning_objectives' ) {
            $row_style = 'margin-bottom: 0.5rem; padding: 0.35rem 0;';
        }

        $html .= '<div class="' . esc_attr( $row_class ) . '" style="' . esc_attr( $row_style ) . '">';

        foreach ( $fields as $field_key => $field_config ) {
            $field_value = isset( $item[ $field_key ] ) ? $item[ $field_key ] : '';
            $field_name = $name . '[' . (int) $i . '][' . esc_attr( $field_key ) . ']';
            $field_type = $field_config['type'] ?? 'text';
            $field_label = $field_config['label'] ?? ucfirst( $field_key );

            if ( $name === 'aiad_key_definitions' || $name === 'aiad_instructions' ) {
                $html .= '<label style="display:block; margin-bottom: 0.25rem; margin-top: 0.35rem;">' . esc_html( $field_label ) . '</label>';
            }

            switch ( $field_type ) {
                case 'text':
                    $placeholder = $field_config['placeholder'] ?? '';
                    $class = $field_config['class'] ?? ( $name === 'aiad_key_definitions' ? 'regular-text' : 'large-text' );
                    $style = $name === 'aiad_key_definitions' ? 'margin-bottom: 0.5rem;' : '';
                    $html .= '<input type="text" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $field_value ) . '" class="' . esc_attr( $class ) . '" placeholder="' . esc_attr( $placeholder ) . '" style="' . esc_attr( $style ) . '" />';
                    break;

                case 'textarea':
                    $rows = $field_config['rows'] ?? 2;
                    $html .= '<textarea name="' . esc_attr( $field_name ) . '" rows="' . esc_attr( $rows ) . '" class="large-text" style="width:100%;">' . esc_textarea( $field_value ) . '</textarea>';
                    break;

                case 'checkbox':
                    $checked = checked( ! empty( $field_value ), true, false );
                    $html .= '<label style="display:inline-block; margin-left: 0.5rem;"><input type="checkbox" name="' . esc_attr( $field_name ) . '" value="1" ' . $checked . ' /> ' . esc_html( $field_label ) . '</label>';
                    break;

                case 'select':
                    $options = $field_config['options'] ?? array();
                    $html .= '<select name="' . esc_attr( $field_name ) . '">';
                    foreach ( $options as $opt_value => $opt_label ) {
                        $selected = selected( $field_value, $opt_value, false );
                        $html .= '<option value="' . esc_attr( $opt_value ) . '" ' . $selected . '>' . esc_html( $opt_label ) . '</option>';
                    }
                    $html .= '</select>';
                    break;

                case 'number':
                    $min = $field_config['min'] ?? 1;
                    $html .= '<input type="number" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $field_value ) . '" min="' . esc_attr( $min ) . '" style="width:4em;" />';
                    break;

                case 'url':
                    $html .= '<input type="url" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $field_value ) . '" placeholder="' . esc_attr( $field_config['placeholder'] ?? 'URL' ) . '" class="medium-text" />';
                    break;
            }

            // Special handling for instructions step number and duration
            if ( $name === 'aiad_instructions' && $field_key === 'step' ) {
                $html .= ' ';
            } elseif ( $name === 'aiad_instructions' && $field_key === 'duration' ) {
                $html .= '<br style="margin-bottom:0.5rem;" />';
            }
        }

        $remove_text = $name === 'aiad_instructions' ? __( 'Remove step', 'ai-awareness-day' ) : __( 'Remove', 'ai-awareness-day' );
        $html .= ' <button type="button" class="button button-small aiad-remove-row" style="' . ( $name === 'aiad_instructions' ? 'margin-top:0.5rem;' : '' ) . '">' . esc_html( $remove_text ) . '</button>';
        $html .= '</div>';
    }

    $html .= '</div>';

    // Special add button handling (must match JavaScript expectations)
    if ( $name === 'aiad_key_definitions' ) {
        $html .= '<button type="button" class="button aiad-add-definition">' . esc_html( $add_button ) . '</button>';
    } elseif ( $name === 'aiad_instructions' ) {
        $html .= '<button type="button" class="button aiad-add-instruction">' . esc_html( $add_button ) . '</button>';
    } elseif ( $name === 'aiad_extensions' ) {
        $html .= '<button type="button" class="button aiad-add-extension">' . esc_html( $add_button ) . '</button>';
    } elseif ( $name === 'aiad_resources' ) {
        $html .= '<button type="button" class="button aiad-add-resource">' . esc_html( $add_button ) . '</button>';
    } elseif ( $name === 'aiad_learning_objectives' ) {
        // Learning objectives uses aiad-add-row with data-name attribute
        $html .= '<button type="button" class="button aiad-add-row" data-name="' . esc_attr( $name ) . '">' . esc_html( $add_button ) . '</button>';
    } else {
        $html .= '<button type="button" class="button aiad-add-row" data-name="' . esc_attr( $name ) . '">' . esc_html( $add_button ) . '</button>';
    }

    $html .= '</div>';

    return $html;
}

/**
 * Render object field (for differentiation)
 *
 * @param array  $config Field configuration
 * @param array  $value  Current value object
 * @param string $name   Form field name prefix
 * @return string Rendered HTML
 */
function aiad_render_object_field( array $config, array $value, string $name ): string {
    $label = $config['label'] ?? '';
    $description = $config['description'] ?? '';
    $fields = $config['fields'] ?? array();

    $html = '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;">';
    $html .= '<strong class="aiad-rd-label">' . esc_html( $label ) . '</strong>';
    
    if ( ! empty( $description ) ) {
        $html .= '<p class="description">' . esc_html( $description ) . '</p>';
    }

    foreach ( $fields as $field_key => $field_config ) {
        $field_value = isset( $value[ $field_key ] ) ? $value[ $field_key ] : '';
        $field_name = $name . '[' . esc_attr( $field_key ) . ']';
        $field_label = $field_config['label'] ?? ucfirst( $field_key );
        $field_type = $field_config['type'] ?? 'textarea';
        $rows = $field_config['rows'] ?? 2;

        $html .= '<label style="display:block; margin-top:0.5rem;">' . esc_html( $field_label ) . '</label>';
        
        if ( $field_type === 'textarea' ) {
            $html .= '<textarea name="' . esc_attr( $field_name ) . '" rows="' . esc_attr( $rows ) . '" class="large-text" style="width:100%;">' . esc_textarea( $field_value ) . '</textarea>';
        } else {
            $html .= '<input type="' . esc_attr( $field_type ) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $field_value ) . '" class="large-text" />';
        }
    }

    $html .= '</div>';

    return $html;
}
