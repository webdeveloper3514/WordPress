
jQuery( function ( $ ) {
    jQuery( '#enable_for_all' ).change( function ( $ ) {

        if (jQuery( '#enable_for_all' ).is( ':checked' ) == true ) {            
            jQuery( '#select_mode' ).prop( 'disabled', true );
            jQuery( ".hidediv" ).hide();    
        } else {            
            jQuery( '#select_mode' ).prop( 'disabled', false );
            jQuery( ".hidediv" ).show();
        }
        
    });
})

$( document ).on( 'change', '.div-toggle', function () {
    var target = $( this ).data( 'target' );
    var show = $( "option:selected", this ).data( 'show' );
    $( target ).children().addClass( 'hide' );
    $( show ).removeClass( 'hide' );
});
$( document ).ready( function () {
    $( '.div-toggle').trigger( 'change' );
});

$( document ).ready( function(){
    $( '#checkout_page_type' ).on( 'change', function() {
      if ( this.value == 'custom' )
      {
        $( ".custom-checkout-page" ).show();
      }
      else
      {
        $( ".custom-checkout-page" ).hide();
      }
    });
});

$( document ).ready( function(){
    $( '#checkout_page_type' ).on( 'change', function() {
      if ( this.value == 'handsomecheckout' )
      {
        $( ".handsome-checkout-page" ).show();
      }
      else
      {
        $( ".handsome-checkout-page" ).hide();
      }
    });
});


jQuery(function ( $ ) {
    jQuery( '#select_mode' ).change(function ( $ ) {
    //$("#target_category option[value == 'xyz']").remove();
    jQuery( '#target_category' ).children( 'option' ).remove();
    jQuery( '#target_category' ).children( 'option' ).remove();
    
    });
})