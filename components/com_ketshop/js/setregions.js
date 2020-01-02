
(function($) {

  //Run a function when the page is fully loaded including graphics.
  //Note: This script is used in address forms on both front and back end. 
  $(window).load(function() {
    var formType = $('#form-type').val();

    $('#'+formType+'_country_code_shipping').change( function() { $.fn.setRegions(this.value, 'shipping'); });
    $('#'+formType+'_country_code_billing').change( function() { $.fn.setRegions(this.value, 'billing'); });

    $.fn.initRegions();
  });


  $.fn.setRegions = function(country_code, type) {
    var regions = ketshop.getRegions();
    var length = regions.length;
    var options = '<option value="">'+Joomla.JText._('COM_KETSHOP_OPTION_SELECT')+'</option>';

    var regex = new RegExp('^'+country_code+'-');
    //Create an option tag for each region.
    for(var i = 0; i < length; i++) {
      //Test the regex to get only regions from the selected country.
      if(regex.test(regions[i].code)) {
	options += '<option value="'+regions[i].code+'">'+regions[i].text+'</option>';
      }
    }

    var formType = $('#form-type').val();
    //Empty the previous options.
    $('#'+formType+'_region_code_'+type).empty();
    //Add the new region options to the select tag.
    $('#'+formType+'_region_code_'+type).append(options);

    //Use Chosen jQuery plugin.
    $('#'+formType+'_region_code_'+type).trigger('liszt:updated');
  };


  $.fn.initRegions = function() {
    var formType = $('#form-type').val();
    //Get the value of the previously selected regions if any.
    var regionCodeSh = $('#hidden-region-code-shipping').val();
    var regionCodeBi = $('#hidden-region-code-billing').val();

    //Empty the options previously set by the regionlist field function.
    $('#'+formType+'_region_code_shipping').empty();
    $('#'+formType+'_region_code_shipping').trigger('liszt:updated');
    $('#'+formType+'_region_code_billing').empty();
    $('#'+formType+'_region_code_billing').trigger('liszt:updated');

    if(regionCodeSh != '') {
      //Build the region option list according to the previously selected country.
      $.fn.setRegions($('#'+formType+'_country_code_shipping').val(), 'shipping');
      //Set the region value previously selected. 
      $('#'+formType+'_region_code_shipping').val(regionCodeSh);
      $('#'+formType+'_region_code_shipping').trigger('liszt:updated');
    }

    if(regionCodeBi != '') {
      //Build the region option list according to the previously selected country.
      $.fn.setRegions($('#'+formType+'_country_code_billing').val(), 'billing');
      //Set the region value previously selected. 
      $('#'+formType+'_region_code_billing').val(regionCodeBi);
      $('#'+formType+'_region_code_billing').trigger('liszt:updated');
    }
  };

})(jQuery);

