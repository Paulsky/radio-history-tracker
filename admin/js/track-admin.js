( function ( $ ) {
	'use strict';
	$( document ).ready( function () {
		$( '#add-timestamp' ).click( function ( e ) {
			e.preventDefault();
			const wrapper = $( '.timestamps-wrapper' );
			const firstTimestamp = $( '.timestamp-wrapper' ).first();
			const newField = firstTimestamp.clone();
			newField.find( 'input' ).val( '' );
			newField.find( '.remove-timestamp' ).show();
			wrapper.append( newField );
		} );

		$( '.timestamp-wrapper' ).first().find( '.remove-timestamp' ).hide();

		$( document ).on( 'click', '.remove-timestamp', function ( e ) {
			e.preventDefault();
			if ( $( '.timestamp-wrapper' ).length > 1 ) {
				$( this ).closest( '.timestamp-wrapper' ).remove();
				$( '.timestamp-wrapper' )
					.first()
					.find( '.remove-timestamp' )
					.hide();
			} else {
				$( this ).siblings( 'input' ).val( '' );
			}
		} );
	} );
} )( jQuery );
