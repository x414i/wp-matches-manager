/**
 * United Misrata Matches Manager — Taxonomy Image Uploader
 * Handles the WordPress Media Library integration for team image uploads.
 *
 * @package UMMM
 */

( function ( $ ) {
	'use strict';

	var frame;

	$( document ).on( 'click', '#ummm-team-image-upload', function ( e ) {
		e.preventDefault();

		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media( {
			title: ummmTaxI18n.title || 'Select Image',
			button: { text: ummmTaxI18n.button || 'Use this image' },
			multiple: false,
			library: { type: 'image' }
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var url        = attachment.sizes && attachment.sizes.thumbnail
				? attachment.sizes.thumbnail.url
				: attachment.url;

			$( '#ummm-team-image-id' ).val( attachment.id );
			$( '#ummm-team-image-preview' ).html(
				'<img src="' + url + '" style="max-width:120px;height:auto;border-radius:8px;border:2px solid #dcdcde;">'
			);
			$( '#ummm-team-image-remove' ).show();
			$( '#ummm-team-image-upload' ).text( ummmTaxI18n.change || 'تغيير الصورة' );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '#ummm-team-image-remove', function ( e ) {
		e.preventDefault();
		$( '#ummm-team-image-id' ).val( '' );
		$( '#ummm-team-image-preview' ).html( '' );
		$( this ).hide();
		$( '#ummm-team-image-upload' ).text( ummmTaxI18n.upload || 'رفع صورة الفريق' );
	} );

	// Clear the image field after a new term is added via AJAX (Add New Term form).
	$( document ).ajaxComplete( function ( event, xhr, settings ) {
		if (
			settings.data &&
			typeof settings.data === 'string' &&
			settings.data.indexOf( 'action=add-tag' ) !== -1 &&
			settings.data.indexOf( 'taxonomy=ummm_team' ) !== -1
		) {
			$( '#ummm-team-image-id' ).val( '' );
			$( '#ummm-team-image-preview' ).html( '' );
			$( '#ummm-team-image-remove' ).hide();
			$( '#ummm-team-image-upload' ).text( ummmTaxI18n.upload || 'رفع صورة الفريق' );
		}
	} );

}( jQuery ) );
