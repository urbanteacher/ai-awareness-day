/**
 * Admin meta boxes JavaScript
 * Handles repeatable fields for resource content sections.
 *
 * @package AI_Awareness_Day
 */

(function($) {
    'use strict';

    // Remove row handler
    $(document).on('click', '.aiad-remove-row', function() {
        var row = $(this).closest('.aiad-repeatable-row');
        if (row.siblings('.aiad-repeatable-row').length >= 1) {
            row.remove();
        }
    });

    // Add row handler for preparation, learning objectives, etc.
    $(document).on('click', '.aiad-add-row', function() {
        var name = $(this).data('name');
        var list = $(this).prev('.aiad-repeatable-list');
        var idx = list.find('.aiad-repeatable-row').length;
        var html;

        if (name === 'aiad_preparation') {
            html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_preparation[]" value="" class="large-text" /> <button type="button" class="button button-small aiad-remove-row">' + aiadAdminMeta.removeText + '</button></div>';
        } else if (name === 'aiad_learning_objectives') {
            html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem; padding: 0.35rem 0;"><input type="text" name="aiad_learning_objectives[' + idx + '][objective]" value="" class="large-text" /> <label><input type="checkbox" name="aiad_learning_objectives[' + idx + '][assessable]" value="1" /> ' + aiadAdminMeta.assessableText + '</label> <button type="button" class="button button-small aiad-remove-row">' + aiadAdminMeta.removeText + '</button></div>';
        } else {
            html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="' + name + '[]" value="" class="large-text" /> <button type="button" class="button button-small aiad-remove-row">' + aiadAdminMeta.removeText + '</button></div>';
        }
        list.append(html);
    });

    // Add definition handler
    $(document).on('click', '.aiad-add-definition', function() {
        var container = $(this).prev('.aiad-repeatable-rows');
        var idx = container.find('.aiad-repeatable-row').length;
        var html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.75rem; padding: 0.5rem; background: #f6f7f7; border-radius: 4px;">' +
            '<label style="display:block;">' + aiadAdminMeta.termText + '</label><input type="text" name="aiad_key_definitions[' + idx + '][term]" value="" class="regular-text" style="margin-bottom: 0.5rem;" /> ' +
            '<label style="display:block;">' + aiadAdminMeta.definitionText + '</label><textarea name="aiad_key_definitions[' + idx + '][definition]" rows="2" class="large-text" style="width:100%;"></textarea> ' +
            '<label style="display:inline-block; margin-left: 0.5rem;"><input type="checkbox" name="aiad_key_definitions[' + idx + '][key_stage_adapted]" value="1" /> ' + aiadAdminMeta.keyStageAdaptedText + '</label> ' +
            '<button type="button" class="button button-small aiad-remove-row">' + aiadAdminMeta.removeText + '</button></div>';
        container.append(html);
    });

    // Add instruction handler
    $(document).on('click', '.aiad-add-instruction', function() {
        var list = $(this).prev('.aiad-repeatable-list');
        var idx = list.find('.aiad-repeatable-row').length;
        var stepNum = idx + 1;
        var html = '<div class="aiad-repeatable-row aiad-instruction-row" style="margin-bottom: 1rem; padding: 0.75rem; background: #f6f7f7; border-radius: 4px;">' +
            '<label>Step <input type="number" name="aiad_instructions[' + idx + '][step]" value="' + stepNum + '" min="1" style="width:4em;" /></label> ' +
            '<label>' + aiadAdminMeta.durationText + ' <input type="text" name="aiad_instructions[' + idx + '][duration]" value="" placeholder="e.g. 60 seconds" style="width:10em;" /></label><br style="margin-bottom:0.5rem;" />' +
            '<label style="display:block; margin-top:0.35rem;">' + aiadAdminMeta.actionText + '</label>' +
            '<textarea name="aiad_instructions[' + idx + '][action]" rows="2" class="large-text" style="width:100%;"></textarea>' +
            '<label style="display:block; margin-top:0.35rem;">' + aiadAdminMeta.resourceRefText + ' <input type="text" name="aiad_instructions[' + idx + '][resource_ref]" value="" placeholder="e.g. Slide 6" class="regular-text" /></label>' +
            '<label style="display:block; margin-top:0.35rem;">' + aiadAdminMeta.studentActionText + ' <input type="text" name="aiad_instructions[' + idx + '][student_action]" value="" class="large-text" /></label>' +
            '<label style="display:block; margin-top:0.35rem;">' + aiadAdminMeta.teacherTipText + ' <textarea name="aiad_instructions[' + idx + '][teacher_tip]" rows="1" class="large-text" style="width:100%;"></textarea></label>' +
            ' <button type="button" class="button button-small aiad-remove-row" style="margin-top:0.5rem;">' + aiadAdminMeta.removeStepText + '</button></div>';
        list.append(html);
    });

    // Add extension handler
    $(document).on('click', '.aiad-add-extension', function() {
        var list = $(this).prev('.aiad-repeatable-list');
        var idx = list.find('.aiad-repeatable-row').length;
        var opts = aiadAdminMeta.extensionOptions;
        var html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_extensions[' + idx + '][activity]" value="" class="large-text" /> <select name="aiad_extensions[' + idx + '][type]">' + opts + '</select> <button type="button" class="button button-small aiad-remove-row">' + aiadAdminMeta.removeText + '</button></div>';
        list.append(html);
    });

    // Add resource handler
    $(document).on('click', '.aiad-add-resource', function() {
        var list = $(this).prev('.aiad-repeatable-list');
        var idx = list.find('.aiad-repeatable-row').length;
        var opts = aiadAdminMeta.resourceOptions;
        var html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_resources[' + idx + '][name]" value="" class="regular-text" /> <select name="aiad_resources[' + idx + '][type]">' + opts + '</select> <input type="url" name="aiad_resources[' + idx + '][url]" value="" class="medium-text" /> <button type="button" class="button button-small aiad-remove-row">' + aiadAdminMeta.removeText + '</button></div>';
        list.append(html);
    });

})(jQuery);
