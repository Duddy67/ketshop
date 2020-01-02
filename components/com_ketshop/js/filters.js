
(function($) {

  // Runs a function when the page is fully loaded including graphics.
  $(window).load(function() {

    // Used mainly with drop down lists with multiple selections.

    $('#filter_manufacturer').change( function() { $.fn.checkIfEmpty(this); });

    $('[id^="filter_attribute_"]').each(function() {
      $(this).change( function() { $.fn.checkIfEmpty(this); });
    });
  });


  $.fn.checkIfEmpty = function(elem) {
    if(elem.selectedOptions.length == 0) {
      // Sets the filter to clear before submiting the form.
      $('#filter-cleared').val(elem.id);
    }

    $('#siteForm').submit();
  };

})(jQuery);
