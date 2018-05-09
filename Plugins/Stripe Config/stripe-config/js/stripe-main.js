$(document).ready(function(){
    //page (join-kc and oto-gqkc and oto-ygkc) remove page reload to button click and open Stripe payment window
    $('.'+bttnCls).click(function(e){
        e.preventDefault();
        $("a.nectar-button").removeAttr("href");
        $('.stripe_f .stripe-button-el').click(); // open Stripe pay window
        $('#stripe_upsell').click();
       // console.log('click form');
    })
});


