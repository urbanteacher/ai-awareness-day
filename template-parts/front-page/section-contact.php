<?php
/**
 * Front page section: Contact (Get Involved form)
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<section class="section section--alt <?php echo esc_attr( $text_alignment_class ); ?>" id="contact">
    <div class="container">
                <div class="contact-wrapper">

                    <div class="contact-info fade-up">
                        <span class="section-label"><?php esc_html_e('Contact Us', 'ai-awareness-day'); ?></span>
                        <?php
                        $defaults = aiad_get_customizer_defaults();
                        ?>
                        <h2 class="section-title">
                            <?php echo esc_html(get_theme_mod('aiad_contact_title', $defaults['aiad_contact_title'])); ?>
                        </h2>
                        <p class="section-desc">
                            <?php echo wp_kses_post(get_theme_mod('aiad_contact_desc', $defaults['aiad_contact_desc'])); ?>
                        </p>
                    </div>

                    <div class="contact-form fade-up stagger-2">
                        <form id="aiad-contact-form" novalidate>
                            <div class="form-group">
                                <label
                                    for="involved_as"><?php esc_html_e('I\'m getting involved as *', 'ai-awareness-day'); ?></label>
                                <select id="involved_as" name="involved_as" required aria-describedby="involved_as-desc">
                                    <option value=""><?php esc_html_e('Select...', 'ai-awareness-day'); ?></option>
                                    <option value="teacher"><?php esc_html_e('Teacher', 'ai-awareness-day'); ?></option>
                                    <option value="parent"><?php esc_html_e('Parent', 'ai-awareness-day'); ?></option>
                                    <option value="school_leader">
                                        <?php esc_html_e('School leader', 'ai-awareness-day'); ?>
                                    </option>
                                    <option value="organisation"><?php esc_html_e('Organisation', 'ai-awareness-day'); ?>
                                    </option>
                                </select>
                                <span id="involved_as-desc"
                                    class="screen-reader-text"><?php esc_html_e('Choose the option that best describes you.', 'ai-awareness-day'); ?></span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name"><?php esc_html_e('First Name *', 'ai-awareness-day'); ?></label>
                                    <input type="text" id="first_name" name="first_name" required
                                        placeholder="<?php esc_attr_e('Your first name', 'ai-awareness-day'); ?>"
                                        autocomplete="given-name">
                                </div>
                                <div class="form-group">
                                    <label for="last_name"><?php esc_html_e('Last Name *', 'ai-awareness-day'); ?></label>
                                    <input type="text" id="last_name" name="last_name" required
                                        placeholder="<?php esc_attr_e('Your last name', 'ai-awareness-day'); ?>"
                                        autocomplete="family-name">
                                </div>
                            </div>

                            <div class="form-group form-group-role" data-role="teacher school_leader"
                                style="display: none;">
                                <label for="school_name"><?php esc_html_e('School name *', 'ai-awareness-day'); ?></label>
                                <input type="text" id="school_name" name="school_name" required
                                    placeholder="<?php esc_attr_e('Your school', 'ai-awareness-day'); ?>"
                                    autocomplete="organization">
                            </div>
                            <div class="form-group form-group-role" data-role="teacher" style="display: none;">
                                <label for="subject"><?php esc_html_e('Subject / area *', 'ai-awareness-day'); ?></label>
                                <input type="text" id="subject" name="subject" required
                                    placeholder="<?php esc_attr_e('e.g. Computing, Maths', 'ai-awareness-day'); ?>">
                            </div>

                            <div class="form-group form-group-role" data-role="parent" style="display: none;">
                                <label
                                    for="child_school"><?php esc_html_e('Child\'s school *', 'ai-awareness-day'); ?></label>
                                <input type="text" id="child_school" name="child_school" required
                                    placeholder="<?php esc_attr_e('School name', 'ai-awareness-day'); ?>"
                                    autocomplete="organization">
                            </div>

                            <div class="form-group form-group-role" data-role="school_leader" style="display: none;">
                                <label for="role_title"><?php esc_html_e('Your role *', 'ai-awareness-day'); ?></label>
                                <input type="text" id="role_title" name="role_title" required
                                    placeholder="<?php esc_attr_e('e.g. Head teacher, Deputy', 'ai-awareness-day'); ?>"
                                    autocomplete="organization-title">
                            </div>

                            <div class="form-group form-group-role" data-role="organisation" style="display: none;">
                                <label
                                    for="organisation"><?php esc_html_e('Organisation name *', 'ai-awareness-day'); ?></label>
                                <input type="text" id="organisation" name="organisation" required
                                    placeholder="<?php esc_attr_e('Company or organisation', 'ai-awareness-day'); ?>"
                                    autocomplete="organization">
                            </div>
                            <div class="form-group form-group-role" data-role="organisation" style="display: none;">
                                <label for="org_type"><?php esc_html_e('Type *', 'ai-awareness-day'); ?></label>
                                <select id="org_type" name="org_type" required>
                                    <option value=""><?php esc_html_e('Select...', 'ai-awareness-day'); ?></option>
                                    <?php foreach (aiad_get_organisation_type_options() as $value => $label): ?>
                                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php /* Optional checklist – shown per role */ ?>
                            <div class="form-group form-group-role contact-checklist" data-role="teacher"
                                style="display: none;">
                                <p class="form-label-optional">
                                    <?php esc_html_e('Optional – what are you interested in?', 'ai-awareness-day'); ?>
                                </p>
                                <div class="checklist-options">
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="teacher_display_board"><span
                                            class="checklist-option__text"><?php esc_html_e('Interested in creating a display board', 'ai-awareness-day'); ?></span></label>
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="teacher_activity_day"><span
                                            class="checklist-option__text"><?php esc_html_e('I\'d like to do an activity for the day', 'ai-awareness-day'); ?></span></label>
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="teacher_learn_ai"><span
                                            class="checklist-option__text"><?php esc_html_e('I\'d like to learn more about AI', 'ai-awareness-day'); ?></span></label>
                                </div>
                            </div>
                            <div class="form-group form-group-role contact-checklist" data-role="parent"
                                style="display: none;">
                                <p class="form-label-optional">
                                    <?php esc_html_e('Optional – what are you interested in?', 'ai-awareness-day'); ?>
                                </p>
                                <div class="checklist-options">
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="parent_support_child"><span
                                            class="checklist-option__text"><?php esc_html_e('I\'d like to support my child with AI', 'ai-awareness-day'); ?></span></label>
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="parent_learn_ai"><span
                                            class="checklist-option__text"><?php esc_html_e('I\'d like to learn more about AI', 'ai-awareness-day'); ?></span></label>
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="parent_school_take_part"><span
                                            class="checklist-option__text"><?php esc_html_e('I\'d like my child\'s school to take part', 'ai-awareness-day'); ?></span></label>
                                </div>
                            </div>
                            <div class="form-group form-group-role contact-checklist" data-role="school_leader"
                                style="display: none;">
                                <p class="form-label-optional">
                                    <?php esc_html_e('Optional – what are you interested in?', 'ai-awareness-day'); ?>
                                </p>
                                <div class="checklist-options">
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="school_leader_staff_activity"><span
                                            class="checklist-option__text"><?php esc_html_e('I\'d like my staff to do an activity', 'ai-awareness-day'); ?></span></label>
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="school_leader_logo_supporter"><span
                                            class="checklist-option__text"><?php esc_html_e('I\'d like our logo displayed as a supporter', 'ai-awareness-day'); ?></span></label>
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="school_leader_school_promote"><span
                                            class="checklist-option__text"><?php esc_html_e('I\'d like our school to promote AI Awareness Day', 'ai-awareness-day'); ?></span></label>
                                </div>
                            </div>
                            <div class="form-group form-group-role contact-checklist" data-role="organisation"
                                style="display: none;">
                                <p class="form-label-optional">
                                    <?php esc_html_e('Optional – what are you interested in?', 'ai-awareness-day'); ?>
                                </p>
                                <div class="checklist-options">
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="org_brand_sponsor"><span
                                            class="checklist-option__text"><?php esc_html_e('Brand Sponsor', 'ai-awareness-day'); ?></span></label>
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="org_theme_sponsor"><span
                                            class="checklist-option__text"><?php esc_html_e('Theme Sponsor', 'ai-awareness-day'); ?></span></label>
                                    <label class="checklist-option"><input type="checkbox" name="aiad_checklist[]"
                                            value="org_campaign_sponsor"><span
                                            class="checklist-option__text"><?php esc_html_e('Campaign Sponsor', 'ai-awareness-day'); ?></span></label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email"><?php esc_html_e('Email *', 'ai-awareness-day'); ?></label>
                                <input type="email" id="email" name="email" required
                                    placeholder="<?php esc_attr_e('your@email.com', 'ai-awareness-day'); ?>"
                                    autocomplete="email" inputmode="email">
                            </div>

                            <div class="form-group">
                                <label for="message"><?php esc_html_e('Message *', 'ai-awareness-day'); ?></label>
                                <textarea id="message" name="message" required
                                    placeholder="<?php esc_attr_e('Tell us how you\'d like to get involved...', 'ai-awareness-day'); ?>"></textarea>
                            </div>

                            <?php /* Honeypot: leave empty; bots that fill all fields will be rejected */ ?>
                            <div class="aiad-honeypot" aria-hidden="true">
                                <label for="aiad_website"><?php esc_html_e('Website', 'ai-awareness-day'); ?></label>
                                <input type="text" id="aiad_website" name="aiad_website" value="" tabindex="-1"
                                    autocomplete="off">
                            </div>

                            <button type="submit" class="btn-submit">
                                <?php esc_html_e('Send Message', 'ai-awareness-day'); ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </button>

                            <div id="form-status" aria-live="polite" aria-atomic="true"
                                style="margin-top:1rem; text-align:center; font-size:0.95rem;"></div>
                        </form>
                    </div>

                </div>
    </div>
</section>
