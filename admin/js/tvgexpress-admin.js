/**
 * Plugin Name: EIMSKIP
 * Plugin URI: https://eimskip.is/
 * Description: Connection to EIMSKIP in Iceland
 *
 * @package Tvgexpress
 */

(function( $ ) {
	'use strict';

	$(
		function() {
			$( '.tvg_enable_box' ).parents( 'tr' ).css( 'border-top','1px solid #ccc' );
		}
	);

	$( document ).ready(
		function(){

			$( ".print-tvg-shipment" ).on(
				'click',
				function(e){
					e.preventDefault();
					$( this ).prop( 'disabled', true );
					$.post(
						tvgexpress_printnode_action.ajax_url,
						{
							'action':'tvgexpress_printnode_action',
							'nonce':tvgexpress_printnode_action.tvgexpress_printnode_action_nonce,
							'printer':$( ".order-printer-tvg-select" ).val(),
							'shipment_id':$( this ).data( 'shipmentid' )
						},
						function(data){
							var response = jQuery.parseJSON( data );
							if (response.success === 1) {
								$( '.printer-response' ).html( 'Miði #' + response.shipmentNumber + ' sendur í prentara ' + $( ".order-printer-tvg-select option:selected" ).text() );
							} else {
								$( '.printer-response' ).html( 'Villa kom upp í prentun á miða' );
							}

							$( this ).prop( 'disabled', false );
						}
					);

					return false;
				}
			);

			$( '.tvg_shipping_method' ).on(
				'change',
				function(){
					if ($( this ).val() === "DROPP") {
						$( '.tvg_shipping_box' ).show();
					} else {
						$( '.tvg_shipping_box' ).hide();
					}
				}
			);
			$( '.tvg_shipping_method' ).trigger( 'change' );

			$( '.update_tvg_shipment' ).on(
				'click',
				function(e){
					e.preventDefault();

					$.post(
						tvg_ajax_change_shipment.ajax_url,
						{
							'action':'tvg_ajax_change_shipment',
							'nonce':tvg_ajax_change_shipment.tvg_ajax_change_shipment_nonce,
							'order_id':$( this ).data( 'order' ),
							'tvg_method':$( '.tvg_shipping_method' ).val(),
							'tvg_box':$( '.tvg_shipping_box' ).val()
						},
						function(data){
							var response = jQuery.parseJSON( data );
							console.log( response.html );
							$( '#order_shipment .inside' ).html( response.html );
						}
					);
				}
			);
		}
	);

})( jQuery );
