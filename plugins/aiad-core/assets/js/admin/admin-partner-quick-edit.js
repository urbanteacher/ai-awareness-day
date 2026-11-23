/**
 * Partner list: fill the Quick Edit Position field from the row's value.
 */
( function () {
	if ( typeof inlineEditPost === 'undefined' ) {
		return;
	}
	var original = inlineEditPost.edit;
	inlineEditPost.edit = function ( id ) {
		original.apply( this, arguments );
		var postId = typeof id === 'object' ? parseInt( this.getId( id ), 10 ) : id;
		var row = document.getElementById( 'post-' + postId );
		var edit = document.getElementById( 'edit-' + postId );
		var order = row && row.querySelector( '.aiad-partner-order' );
		var field = edit && edit.querySelector( '.aiad-partner-menu-order' );
		if ( order && field ) {
			field.value = order.getAttribute( 'data-order' );
		}
	};
} )();
