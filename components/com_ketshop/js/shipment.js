
(function($) {

  // Runs a function when the page is fully loaded including graphics.
  $(window).load(function() {

    $('[name="shipping"]').change( function() { $.fn.setTotalAmount(); });
    $.fn.setTotalAmount();
  });

  $.fn.setTotalAmount = function() {
    // Collects the needed variables.
    let originalAmount = $('#original-amount').val();
    let shippingId = $('input[name="shipping"]:checked').val();
    let shippingCost = $('#shipping-cost-'+shippingId).val();
    // Computes the total amount.
    let totalAmount = (parseFloat(originalAmount) + parseFloat(shippingCost)).toFixed(2);
    // Displays the total amount into the span tag.
    $('#total-amount').text(totalAmount);
  };

})(jQuery);
