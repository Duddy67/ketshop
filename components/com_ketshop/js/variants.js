
(function($) {

  // Runs a function when the page is fully loaded including graphics.
  $(window).load(function() {
    // Binds the variant selecting function to the variant picker.
    $('[id^="variant-picker-"]').click( function() { $.fn.setVariant(this.id); });

    // N.B: Both images and product variants are hidden by default in the css.

    // Shows only the default image and product variant. 
    $('div.default-images').css({'visibility':'visible', 'display':'block'});
    $('div.default-variant').css({'visibility':'visible', 'display':'block'});
    // Activates the very first variant selector.
    $('div.variant-picker div:first-child').addClass('active-variant');
  });

  $.fn.setVariant = function(id) {
    // Extract the id pair (product-variant) from the div id.
    let ids = id.replace(/variant-picker-/, '');
    // Extract the product id from the pair id (product-variant).
    let match = ids.match(/^([0-9]+)/);
    let prodId = match[0];

    // Hides all the images and variants relating to this product.
    $.fn.hideImages(prodId);
    $.fn.hideVariants(prodId);
    $.fn.deactivateSelectors(prodId);

    // Shows the selected variant and its corresponding images.
    $('#product-variant-'+ids).css({'visibility':'visible', 'display':'block'});
    $('#variant-images-'+ids).css({'visibility':'visible', 'display':'block'});
    // Activates the current selector.
    $('#variant-selector-'+ids).addClass('active-variant');
  };

  // Hides all the variants of a given product.
  $.fn.hideVariants = function(prodId) {
    $('[id^="product-variant-'+prodId+'-"]').each(function(index) {
      $(this).css({'visibility':'hidden', 'display':'none'});
    });
  };

  // Hides all the images of a given product.
  $.fn.hideImages = function(prodId) {
    $('[id^="variant-images-'+prodId+'-"]').each(function(index) {
      $(this).css({'visibility':'hidden', 'display':'none'});
    });
  };

  // Deactivate all the variant selectors of a given product.
  $.fn.deactivateSelectors = function(prodId) {
    $('[id^="variant-selector-'+prodId+'-"]').each(function(index) {
      $(this).removeClass('active-variant');
    });
  };

})(jQuery);
