jQuery(function($){
	$("#refgen_method").change(function(){
		if($(this).val() === 'php'){
			$('.refgenhide').slideUp('slow');
		} else {
			$('.refgenhide').slideDown('slow');
		}
	});
	if ($("#refgen_method").val() === 'php') {
		$(".refgenhide").hide();
	}	
	$('input[type=checkbox]').click(function(){
		if ($(this).attr('checked') === true) {
			if ($(this).attr('id') !== 'refgen_reset') {
				$('#refgen_reset').prop('checked', false);	
			} else {
				$('input[type=checkbox]').prop('checked', function() {return $(this).is('#refgen_reset')});
			}
		}
	});
});
