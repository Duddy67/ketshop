
(function($) {

  // Runs a function when the page is fully loaded including graphics.
  $(window).load(function() {

    $('[name="shipping"]').change( function() { $.fn.setTotalAmount(); });
    $('[name="payment_mode"]').change( function() { $.fn.setPaymentId(); });
    $.fn.setTotalAmount();
    $.fn.setPaymentId();
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

    // Updates the shipping id.
    $.fn.setProceedLink('shipping_id', shippingId);
  };


  $.fn.setPaymentId = function() {
    let paymentId = $('input[name="payment_mode"]:checked').val();
    // Updates the payment id.
    $.fn.setProceedLink('payment_id', paymentId);
  };


  $.fn.setProceedLink = function(type, id) {
    let link = $('#proceed').attr('href');
    let regex = new RegExp(type+'=[0-9]+');
    link = link.replace(regex, type+'='+id);
    $('#proceed').attr('href', link);
  };
})(jQuery);
