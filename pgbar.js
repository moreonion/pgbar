(function ($) {
$(document).ready(function() {

$('.pgbar-wrapper').each(function() {
  var wrapper = $(this);
  var current = parseFloat(wrapper.attr('data-pgbar-current'));
  var target  = parseFloat(wrapper.attr('data-pgbar-target'));
  var bars    = $('.pgbar-current', wrapper);

  if (wrapper.attr('data-pgbar-inverted') == 'true') {
    var from = 1;
    var to = 1 - current / target;
    var diff = from - to;
  } else {
    var from = 0;
    var to = current / target;
    var diff = to - from;
  }
  if (wrapper.attr('data-pgbar-direction') == 'vertical') {
    bars.height(from*100 + '%');
    var initial_animation = function() {
      bars.animate({height: to*100 + '%'}, 500+1000*diff);
    }
  } else {
    bars.width(from*100 + '%');
    var initial_animation = function() {
      bars.animate({width: to*100 + '%'}, 500+1000*diff);
    }
  }

  window.setTimeout(initial_animation, 2000);
  
});

});
})(jQuery);
