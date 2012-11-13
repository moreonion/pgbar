(function ($) {
$(document).ready(function() {

$('.pgbar-wrapper').each(function() {
  var wrapper = $(this);
  var current = parseFloat(wrapper.attr('data-pgbar-current'));
  var target  = parseFloat(wrapper.attr('data-pgbar-target'));
  var bars    = $('.pgbar-current', wrapper);

  var percentage = current / target * 100;

  if (wrapper.attr('data-pgbar-mode') == 'vertical') {
    bars.height(0);
    var initial_animation = function() {
      bars.animate({height: percentage + '%'}, 500+10*percentage);
    }
  } else {
    bars.width(0);
    var initial_animation = function() {
      bars.animate({width: percentage + '%'}, 500+10*percentage);
    }
  }

  window.setTimeout(initial_animation, 2000);
  
});

});
})(jQuery);
