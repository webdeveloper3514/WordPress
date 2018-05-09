function add_new(){
    $('.add_new_pnp').click(function(e) {
		e.preventDefault();
		$('#myModal').modal('hide');
		$('.div-add-new').find('.add_new_pnp').remove();
		$('.div-add-new').find('.add_new').appendTo($('#frm_add_pnp'));
	});
}