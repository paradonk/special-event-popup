/**
 * Special Event Popup - Admin behavior.
 *
 * Wires up the WordPress Media Uploader for selecting the popup image.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var frame;
		var $wrap = $( '.sep-admin-wrap' );
		var $preview = $wrap.find( '.sep-image-preview' );
		var $previewImg = $preview.find( 'img' );
		var $idField = $wrap.find( '.sep-image-id' );
		var $selectBtn = $wrap.find( '.sep-select-image' );
		var $removeBtn = $wrap.find( '.sep-remove-image' );
		var $typeField = $wrap.find( '#sep_popup_type' );
		var $memorialRow = $wrap.find( '.sep-mode-memorial' );
		var $celebrationRow = $wrap.find( '.sep-mode-celebration' );

		function toggleModeRows() {
			var type = $typeField.val();

			$memorialRow.toggle( 'memorial' === type );
			$celebrationRow.toggle( 'celebration' === type );
		}

		toggleModeRows();
		$typeField.on( 'change', toggleModeRows );

		$selectBtn.on( 'click', function ( event ) {
			event.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: ( window.sepAdmin && sepAdmin.mediaTitle ) || 'Select image',
				button: {
					text: ( window.sepAdmin && sepAdmin.mediaButton ) || 'Use this image',
				},
				library: { type: 'image' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var url = ( attachment.sizes && attachment.sizes.medium ) ? attachment.sizes.medium.url : attachment.url;

				$idField.val( attachment.id );
				$previewImg.attr( 'src', url );
				$preview.show();
				$removeBtn.show();
			} );

			frame.open();
		} );

		$removeBtn.on( 'click', function ( event ) {
			event.preventDefault();

			$idField.val( '' );
			$previewImg.attr( 'src', '' );
			$preview.hide();
			$removeBtn.hide();
		} );
	} );
} )( jQuery );
