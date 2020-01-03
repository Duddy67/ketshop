
(function($) {

  // Runs a function when the page is fully loaded including graphics.
  $(window).load(function() {

    $('[name="shipping"]').change( function() { $.fn.setTotalAmount(this.id); });
  });

  $.fn.setTotalAmount = function(id) {
    alert('picker');
  };

})(jQuery);
