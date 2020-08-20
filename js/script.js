$(function(){

	var flash = $('.js-flash');

	flash.slideDown('slow');
	setTimeout(function() {
		flash.slideUp();
	} ,5000);


});