(function ($) {
$(document).ready(function() {

$('.pgbar-wrapper').each(function() {
	var wrapper = $(this);
	var current = parseFloat(wrapper.attr('data-pgbar-current'));
	var target  = parseFloat(wrapper.attr('data-pgbar-target'));
	var bars    = $('.pgbar-current', wrapper);

	var percentage = current / target * 100;

	bars.width(0);

	var initial_animation = function() {
		bars.animate({width: percentage + '%'}, percentage);
	}

	window.setTimeout(initial_animation, 2000);
	
});

});
})(jQuery);