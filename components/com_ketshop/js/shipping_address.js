
(function($) {

  // Runs a function when the page is fully loaded including graphics.
  $(window).load(function() {
    $('input[name="jform[shipping_address]"]').click( function() { $.fn.toggleAddress(); });
    $.fn.toggleAddress();

    $('#new-billing-address').click( function() { $.fn.resetAddress('billing'); });
    $('#new-shipping-address').click( function() { $.fn.resetAddress('shipping'); });
  });

  $.fn.toggleAddress = function() {
    // The fields to modify.
    let fields = ['street_shipping', 'postcode_shipping', 'country_code_shipping', 'region_code_shipping', 'city_shipping'];

    if($('input[name="jform[shipping_address]"]:checked').val() == 1) {
      // Show fields.
      $('#shipping_div').css({'visibility':'visible', 'display':'block'});
      // Makes the fields mandatory.
      for(var i = 0; i < fields.length; i++) {
	$('#jform_'+fields[i]).addClass('required');
	$('#jform_'+fields[i]).attr('required', 'required');
      }
    }
    else {
      // Hides fields.
      $('#shipping_div').css({'visibility':'hidden', 'display':'none'});
      // Makes the fields non-binding.
      for(var i = 0; i < fields.length; i++) {
	$('#jform_'+fields[i]).removeClass('required invalid');
	$('#jform_'+fields[i]).removeAttr('required');
      }
    }
  };

  $.fn.resetAddress = function(type) {
    $('input[id$=_'+type+']').each(function() {
      // Resets text fields.
      $(this).val('');
    });

    // Resets drop down lists.
    $('#jform_region_code_'+type+' option[selected="selected"]').prop('selected', false);
    $('#jform_region_code_'+type).trigger('liszt:updated');
    $('#jform_country_code_'+type+' option[selected="selected"]').prop('selected', false);
    $('#jform_country_code_'+type).trigger('liszt:updated');

    $('#jform_new_'+type+'_address').val(1);
  };

})(jQuery);

