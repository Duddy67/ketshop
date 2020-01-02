
// Adds extra handlers to the Joomla formvalidator.
// Source: https://docs.joomla.org/Client-side_form_validation

jQuery(document).ready(function(){
   document.formvalidator.setHandler('integer', function(value) {
     let regex = /^[0-9]+$/;
     return regex.test(value);
   });

   document.formvalidator.setHandler('integer-signed', function(value) {
     let regex = /^-?[0-9]+$/;
     return regex.test(value);
   });

   document.formvalidator.setHandler('decimal', function(value) {
     // Allows also integers as they will be converted in decimal by MySQL while saving. 
     let regex = /^[0-9]{1,}(\.[0-9]+)?$/;
     return regex.test(value);
   });

   document.formvalidator.setHandler('decimal-strict', function(value) {
     let regex = /^[0-9]{1,}\.[0-9]+$/;
     return regex.test(value);
   });

   document.formvalidator.setHandler('decimal-signed', function(value) {
     let regex = /^-?[0-9]{1,}(\.[0-9]+)?$/;
     return regex.test(value);
   });
});
