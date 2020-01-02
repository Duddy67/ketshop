
(function($) {

  // Runs a function when the page is fully loaded including graphics.
  $(window).load(function() {
    // 
    $('[id^="remove-product-"]').click( function(e) { $.fn.warningMessage(e, 'remove_product'); });
    $('#empty-cart').click( function(e) { $.fn.warningMessage(e, 'empty_cart'); });
  });

  $.fn.warningMessage = function(e, type) {
    // Asks the user to confirm deletion.
    if(confirm(Joomla.JText._('COM_KETSHOP_CART_'+type.toUpperCase())) === false) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  };

})(jQuery);

