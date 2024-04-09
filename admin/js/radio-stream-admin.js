( function ( $, MicroModal, window ) {
	'use strict';

	const l10n = window.radio_stream_data.l10n || {};

	function updateResultContent( data ) {
		const contentId = '#result-content';
		if ( ! data || data.length === 0 ) {
			$( contentId ).html( l10n.no_data_available );
			return;
		}

		let html = '<table style="bord">';
		html += `<caption>${ l10n.history_caption }</caption>`;
		html += `<thead><tr><th>${ l10n.played_at }</th><th>${ l10n.artist }</th><th>${ l10n.title }</th><th>${ l10n.artwork }</th></tr></thead>`;
		html += '<tbody>';

		data.forEach( ( item ) => {
			const playedAt = new Date(
				parseInt( item.played_at ) * 1000
			).toLocaleString();
			const artworkImg = item.artwork_url
				? `<img src="${ item.artwork_url }" loading="lazy" style="width: 50px; height: auto;" alt="${ l10n.artwork }" />`
				: l10n.na;

			html += `<tr>
                        <td>${ playedAt }</td>
                        <td>${ item.artist }</td>
                        <td>${ item.title }</td>
                        <td>${ artworkImg }</td>
                    </tr>`;
		} );

		html += '</tbody></table>';

		$( contentId ).html( html );
	}

	function testMetadataUrl() {
		const contentId = '#result-content';
		const { admin_url, action } = window.radio_stream_data || {};

		if ( ! admin_url || ! action ) {
			$( contentId ).html( l10n.something_went_wrong );
			return;
		}

		const streamType = $( '#rht_stream_type' ).val();
		const metadataType = $( '#rht_stream_metadata_type' ).val();
		const metadataEndpoint = $( '#rht_stream_metadata_endpoint' ).val();
		const metadataUsername = $( '#rht_stream_username' ).val();
		const metadataPassword = $( '#rht_stream_password' ).val();
		const nonce = $( '#rht_stream_meta_box_nonce' ).val();

		if ( ! streamType || ! metadataType || ! metadataEndpoint || ! nonce ) {
			$( contentId ).html( l10n.something_went_wrong );
			return;
		}

		$( contentId ).html( l10n.loading );

		$.ajax( {
			url: admin_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: action,
				nonce: nonce,
				stream_type: streamType,
				metadata_type: metadataType,
				metadata_endpoint: metadataEndpoint,
				metadata_username: metadataUsername,
				metadata_password: metadataPassword,
			},
			success: function ( response ) {
				if ( response.success ) {
					updateResultContent( response.data );
				} else {
					$( contentId ).html( l10n.couldnt_process_metadata );
				}
			},
			error: function () {
				$( contentId ).html( l10n.couldnt_process_metadata );
			},
		} );
	}

	$( document ).ready( function () {
		MicroModal.init( {
			onShow: function ( modal ) {
				if ( modal.id === 'rht-test-modal' ) {
					testMetadataUrl();
				}
			},
		} );
	} );
} )( jQuery, MicroModal, window );
