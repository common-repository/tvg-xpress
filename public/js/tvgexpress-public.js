(function( $ ) {
	'use strict';

	$(
		function() {
			$( document ).on(
				'updated_checkout',
				function () {
					let el = $( "#shipping_method_0_dropp" );
					if (el.attr( 'type' ) === 'radio' && el.is( ':checked' ) === false) {
						$( '.tvg_postbox_select' ).hide();
					}
				}
			);

			$( '#billing_address_1, #billing_postcode, #shipping_address_1, #shipping_postcode' ).on(
				'change input blur',
				() => {
                $( 'body' ).trigger(
						'update_checkout',
						{
							update_shipping_method: true
						}
					);
				}
			);
		}
	);
})( jQuery );
