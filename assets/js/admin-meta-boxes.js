/**
 * Admin meta boxes (classic editor only; the block editor edits these fields in its own panels).
 * Repeatable fields for resource content sections and partner links. Plain DOM, no jQuery; the words come from
 * wp.i18n, loaded with the script's translations.
 *
 * @package AI_Awareness_Day
 */
( function () {
	'use strict';

	var __ = window.wp.i18n.__;
	var D = 'ai-awareness-day';

	function esc( text ) {
		var span = document.createElement( 'span' );
		span.textContent = text;
		return span.innerHTML.replace( /"/g, '&quot;' );
	}

	function options( list ) {
		return list.map( function ( o ) {
			return '<option value="' + o[ 0 ] + '">' + esc( o[ 1 ] ) + '</option>';
		} ).join( '' );
	}

	function extensionOptions() {
		return options( [
			[ 'homework', __( 'Homework', D ) ],
			[ 'next_lesson', __( 'Next lesson', D ) ],
			[ 'cross_curricular', __( 'Cross-curricular', D ) ],
			[ 'independent', __( 'Independent', D ) ],
		] );
	}

	function resourceOptions() {
		return options( [
			[ 'slides', __( 'Slides', D ) ],
			[ 'worksheet', __( 'Worksheet', D ) ],
			[ 'handout', __( 'Handout', D ) ],
			[ 'video', __( 'Video', D ) ],
			[ 'link', __( 'Link', D ) ],
			[ 'other', __( 'Other', D ) ],
		] );
	}

	function partnerThemeOptions() {
		return options( [
			[ 'safe', __( 'Safe', D ) ],
			[ 'smart', __( 'Smart', D ) ],
			[ 'creative', __( 'Creative', D ) ],
			[ 'responsible', __( 'Responsible', D ) ],
			[ 'future', __( 'Future', D ) ],
		] );
	}

	// Next index is stored on the list/container to avoid duplicates when rows are removed from the middle.
	function getNextIdx( el ) {
		var next = parseInt( el.dataset.aiadNextIdx, 10 );
		if ( isNaN( next ) ) {
			next = el.querySelectorAll( '.aiad-repeatable-row' ).length;
		}
		el.dataset.aiadNextIdx = String( next + 1 );
		return next;
	}

	function removeButton( style ) {
		return '<button type="button" class="button button-small aiad-remove-row"' + ( style ? ' style="' + style + '"' : '' ) + '>' + esc( __( 'Remove', D ) ) + '</button>';
	}

	function append( list, html ) {
		list.insertAdjacentHTML( 'beforeend', html );
	}

	var adders = {
		'.aiad-add-row': function ( btn ) {
			var name = btn.dataset.name;
			var list = btn.previousElementSibling;
			var idx = getNextIdx( list );
			if ( name === 'aiad_preparation' ) {
				append( list, '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_preparation[]" value="" class="large-text" /> ' + removeButton() + '</div>' );
			} else if ( name === 'aiad_learning_objectives' ) {
				append( list, '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem; padding: 0.35rem 0;"><input type="text" name="aiad_learning_objectives[' + idx + '][objective]" value="" class="large-text" /> ' + removeButton() + '</div>' );
			} else {
				append( list, '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="' + esc( name ) + '[]" value="" class="large-text" /> ' + removeButton() + '</div>' );
			}
		},
		'.aiad-add-definition': function ( btn ) {
			var container = btn.previousElementSibling;
			var idx = container.querySelectorAll( '.aiad-repeatable-row' ).length;
			append( container, '<div class="aiad-repeatable-row" style="margin-bottom: 0.75rem; padding: 0.5rem; background: #F6F4ED; border-radius: 4px;">' +
				'<label style="display:block;">' + esc( __( 'Term', D ) ) + '</label><input type="text" name="aiad_key_definitions[' + idx + '][term]" value="" class="regular-text" style="margin-bottom: 0.5rem;" /> ' +
				'<label style="display:block;">' + esc( __( 'Definition', D ) ) + '</label><textarea name="aiad_key_definitions[' + idx + '][definition]" rows="2" class="large-text" style="width:100%;"></textarea> ' +
				'<label style="display:inline-block; margin-left: 0.5rem;"><input type="checkbox" name="aiad_key_definitions[' + idx + '][key_stage_adapted]" value="1" /> ' + esc( __( 'Key stage adapted', D ) ) + '</label> ' +
				removeButton() + '</div>' );
		},
		'.aiad-add-instruction': function ( btn ) {
			var list = btn.previousElementSibling;
			var idx = getNextIdx( list );
			append( list, '<div class="aiad-repeatable-row aiad-instruction-row" style="margin-bottom: 1rem; padding: 0.75rem; background: #F6F4ED; border-radius: 4px;">' +
				'<label>' + esc( __( 'Step', D ) ) + ' <input type="number" name="aiad_instructions[' + idx + '][step]" value="' + ( idx + 1 ) + '" min="1" style="width:4em;" /></label> ' +
				'<label>' + esc( __( 'Duration', D ) ) + ' <input type="text" name="aiad_instructions[' + idx + '][duration]" value="" placeholder="e.g. 60 seconds" style="width:10em;" /></label><br style="margin-bottom:0.5rem;" />' +
				'<label style="display:block; margin-top:0.35rem;">' + esc( __( 'Action', D ) ) + '</label>' +
				'<textarea name="aiad_instructions[' + idx + '][action]" rows="2" class="large-text" style="width:100%;"></textarea>' +
				'<label style="display:block; margin-top:0.35rem;">' + esc( __( 'Resource ref', D ) ) + ' <input type="text" name="aiad_instructions[' + idx + '][resource_ref]" value="" placeholder="e.g. Slide 6" class="regular-text" /></label>' +
				'<label style="display:block; margin-top:0.35rem;">' + esc( __( 'Student action', D ) ) + ' <input type="text" name="aiad_instructions[' + idx + '][student_action]" value="" class="large-text" /></label>' +
				'<label style="display:block; margin-top:0.35rem;">' + esc( __( 'Teacher tip', D ) ) + ' <textarea name="aiad_instructions[' + idx + '][teacher_tip]" rows="1" class="large-text" style="width:100%;"></textarea></label> ' +
				'<button type="button" class="button button-small aiad-remove-row" style="margin-top:0.5rem;">' + esc( __( 'Remove step', D ) ) + '</button></div>' );
		},
		'.aiad-add-extension': function ( btn ) {
			var list = btn.previousElementSibling;
			var idx = getNextIdx( list );
			append( list, '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_extensions[' + idx + '][activity]" value="" class="large-text" /> <select name="aiad_extensions[' + idx + '][type]">' + extensionOptions() + '</select> ' + removeButton() + '</div>' );
		},
		'.aiad-add-resource': function ( btn ) {
			var list = btn.previousElementSibling;
			var idx = getNextIdx( list );
			append( list, '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_resources[' + idx + '][name]" value="" class="regular-text" /> <select name="aiad_resources[' + idx + '][type]">' + resourceOptions() + '</select> <input type="url" name="aiad_resources[' + idx + '][url]" value="" class="medium-text" /> ' + removeButton() + '</div>' );
		},
		'.aiad-add-partner-link': function ( btn ) {
			var list = btn.previousElementSibling;
			var idx = getNextIdx( list );
			var label = 'display:block; margin-bottom: 0.25rem; margin-top: 0.35rem;';
			append( list, '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;">' +
				'<label style="' + label + '">' + esc( __( 'Theme', D ) ) + '</label><select name="partner_links[' + idx + '][theme]">' + partnerThemeOptions() + '</select>' +
				'<label style="' + label + '">' + esc( __( 'Title', D ) ) + '</label><input type="text" name="partner_links[' + idx + '][title]" value="" class="large-text" placeholder="e.g. Introduction to Artificial Intelligence" />' +
				'<label style="' + label + '">' + esc( __( 'Duration', D ) ) + '</label><input type="text" name="partner_links[' + idx + '][duration]" value="" class="regular-text" placeholder="e.g. 30 mins, 1 hour" />' +
				'<label style="' + label + '">' + esc( __( 'Link URL', D ) ) + '</label><input type="url" name="partner_links[' + idx + '][url]" value="" class="medium-text" placeholder="https://…" /> ' +
				removeButton( 'margin-top:0.5rem;' ) + '</div>' );
		},
	};

	document.addEventListener( 'click', function ( event ) {
		var remove = event.target.closest( '.aiad-remove-row' );
		if ( remove ) {
			var row = remove.closest( '.aiad-repeatable-row' );
			var siblings = row.parentNode.querySelectorAll( ':scope > .aiad-repeatable-row' );
			if ( siblings.length > 1 ) {
				row.remove();
			}
			return;
		}
		Object.keys( adders ).forEach( function ( selector ) {
			var btn = event.target.closest( selector );
			if ( btn ) {
				adders[ selector ]( btn );
			}
		} );
	} );
} )();
