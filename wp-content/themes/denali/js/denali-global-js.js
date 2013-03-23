
jQuery(document).ready(function() {

  jQuery("#commentform").submit(function() {  
    return denali_submit_contact_form();
  });  
  

  if(jQuery('#global_property_search_home').length != 0 && jQuery('.wpp_slideshow_global_wrapper').length != 0)
    denali_fix_slideshow_dom();

  
});

jQuery(window).ready(function() {
  jQuery(".content_horizontal_widget  .property_widget_block").equalHeights();
 
});

 
/**
   * Fixes height of slider where global slideshow is present to match search form
   *
   */
function denali_fix_slideshow_dom() {

  var widget_height = jQuery("#global_property_search_home").height();
 
  var slideshow_height = jQuery(".wpp_slideshow_global_wrapper").height();
  var difference = (widget_height - slideshow_height);
  
  // If sidebar exists
  
  if(jQuery('#content .sidebar').length != 0) {
    jQuery("#content .sidebar").css('margin-top', (difference - 25) + 'px');  
  } else {
    jQuery("#content").css('margin-top', (difference - 15) + 'px');    
  }  
  
}

/**
   * Cycles through form elements and throws an error if any fields that require validation are empty.
   *
   */
function denali_submit_contact_form() {

  var form_is_good = true;

  // unset any validation failures from last run
  jQuery("#commentform *[aria-required='true']").removeClass('wpp_validation_fail');
  
  // Check if any required fields are not filled out
  
  jQuery("#commentform *[aria-required='true']").each(function(index,element) {
  
    if(jQuery(element).val() == '') {
      jQuery(element).addClass('wpp_validation_fail');
      form_is_good = false;
    }
    
    // Special provision for e-mail
    if(jQuery(element).attr('name') == 'email') {
      if(!denali_email_validate(jQuery(element).val())) {
        jQuery(element).addClass('wpp_validation_fail');
        form_is_good = false;
      }
    }
    
  });

  return form_is_good;

}

 /**
   * Validates e-mail address.
   *
   * Source: http://www.white-hat-web-design.co.uk/articles/js-validation.php
   *
   */
function denali_email_validate(email) {
   var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    if(reg.test(email) == false) {      
      return false;
   }
   
   return true;
}