 
<?php
$denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
$longitude = $denali_theme_settings['longitude'];
$latitude = $denali_theme_settings['latitude'];
  ?>
 
<div class="bottom"></div>
</div>
<div class="footer ">
   
</div>
</div>
<?php wp_footer(); ?>
<?php ob_start(); ?><script type='text/javascript'>
    //Check browser version. Set 'true' if the current browser is IE7
    var ie7 = (document.all && !window.opera && window.XMLHttpRequest)?true:false;
    if(typeof document.documentMode != 'undefined') {
        ie7 = (document.documentMode == 7)?true:false;
    }
    
    jQuery(document).ready(function(jQuery){
    
    //IE7 Hack for properties sorter's and pagination's elements positions
    if(ie7) {
        var pp = jQuery('#properties_pagination');
        if(pp.length > 0) {
            var ppChilds = pp.children();
            var ppWidth = 0;
            for(i=0; i<ppChilds.length; i++) {
                var el = jQuery(ppChilds[i]);
                ppWidth += el.width() + parseInt(el.css('marginLeft')) + parseInt(el.css('marginRight')) + 1;
            }
            pp.width(ppWidth);
        }
    }
    
    jQuery("#denali_contact_form").submit(function(event) {
         event.preventDefault();

         var data = {
            action: 'denali_contact_form_submit',
            nonce: '<?php echo wp_create_nonce('denali_contact_form'); ?>',
            name: jQuery("#denali_contact_form #contact_name").val(),
            email: jQuery("#denali_contact_form #contact_email").val(),
            phone: jQuery("#denali_contact_form #contact_phone").val(),
            subject: jQuery("#denali_contact_form #contact_subject").val(),
            message: jQuery("#denali_contact_form #contact_message").val()
         }
            
 
        jQuery.post(
            '<?php echo admin_url('admin-ajax.php'); ?>',
            data,
            function(result) {
                 if(result.success == 'true') {                 
                    jQuery('.header_contact_div li.form').html("<p class='denali_contact_form_success'><?php _e('Thank you for your message.', 'wpp'); ?></div>");
                 
                 } else {                 
                    var error_message = '';
                    if(typeof result.errors == 'object') {
                        error_message += '<ul class="errors">';
                        for(var i in result.errors) {
                            if(typeof result.errors != 'function') {
                                error_message += '<li>' + result.errors[i] + '</li>';
                            }
                        }
                        error_message += '</ul>';
                    } else {
                        error_message = "Sorry, something wrong. Please, try again later.";                    
                    }
                    
                    jQuery("#denali_contact_form *").removeClass('denali_contact_form_validation_error');

                    for(var i in result.errors) {
                        var value = result.errors[i];                        
                        jQuery("#denali_contact_form #contact_" + i).addClass('denali_contact_form_validation_error');
                    }
                    jQuery('.header_contact_div li.form .ajax_error').show();
                    jQuery('.header_contact_div li.form .ajax_error').html(error_message);
                 }
            },
            "json");
    
    
    
        return false;
    });
    var all = jQuery('div.disbl div').length;
    jQuery('ul.log_menu li a').click(function(e){
        //e.preventDefault();
        id = jQuery(this).parent().attr('id');
        if(id){
            for(var index=1;index<=all;index=index+1){
                if(jQuery('div.disbl div#div-'+index).css("display")=='block' && index != id){
                    jQuery('div.disbl div#div-'+index).css("position","relative");
                    jQuery('div.disbl div#div-'+index).slideUp("slow");
                    jQuery('ul.log_menu li#'+index+' a').removeClass('act');
                }
            }
            
            var find_top = false;
            if(jQuery(this).parent().hasClass('find_top')) {
                find_top = jQuery(this).parent();
            }
            
            if(jQuery('ul.log_menu li#'+id+' a').hasClass('act') ){
                if(find_top) {
                    find_top.removeClass('act');
                }
                
                jQuery('div.disbl').css('paddingBottom', '0');
                jQuery('#div-'+id).css("position","relative");
                jQuery('#div-'+id).slideUp("slow", function(){
                    //Hack for IE7
                    if(ie7) {
                        var disblHeight = jQuery('.disbl').height();
                        if(jQuery('span.logo').length > 0) jQuery('span.logo').css('marginTop', (disblHeight + 'px'));
                        if(jQuery('span.custom_logo').length > 0) jQuery('span.custom_logo').css('marginTop', (disblHeight + 'px'));
                    }
                });
                jQuery('ul.log_menu li#'+id+' a').removeClass('act');
            }else{
                if(find_top) {
                    find_top.addClass('act');
                } else {
                    if(jQuery('ul.log_menu .find_top').length > 0) {
                        jQuery('ul.log_menu .find_top').removeClass('act');
                    }
                }
                jQuery('div.disbl').css('paddingBottom', '7px');
                jQuery('#div-'+id).css("position","static");
                
                
                var disblChilds = jQuery('div.disbl').children();
                
                for(i=0;i<disblChilds.length;i++) {
                    var el = jQuery(disblChilds[i]);
                    el.hide();
                }
                
                jQuery('.disbl').slideDown("slow");
                jQuery('#div-'+id).slideDown("slow", function(){
                    //Hack for IE7
                    if(ie7) {
                        var disblHeight = jQuery('.disbl').height();
                        if(jQuery('span.logo').length > 0) jQuery('span.logo').css('marginTop', (disblHeight + 'px'));
                        if(jQuery('span.custom_logo').length > 0) jQuery('span.custom_logo').css('marginTop', (disblHeight + 'px'));
                    }
                    
                });
                jQuery('ul.log_menu li#'+id+' a').addClass('act');

                loadMap();
                
            }
        }
    });
    //Add class in li element in fields with select
    jQuery('select.wpp_search_select_field').parent().addClass('select_class');
    
});

function loadMap(){
    <?php if ($latitude): ?>
            var myLatlng1 = new google.maps.LatLng(<?php echo $latitude; ?>,<?php echo $longitude; ?>);
                  var myOptions = {
                  zoom: 8,
                  center: myLatlng1,
                  mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                var map1 = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
            
                var marker1 = new google.maps.Marker({
                    position: myLatlng1, 
                    map1: map1,
                    title:""
                }); 
               // map1.setCenter(myLatlng1);
<?php endif; ?>
} 
            
</script>
<?php $header_js = ob_get_contents(); ob_end_clean(); ?>

<?php 
  if(class_exists('WPP_F'))
    echo WPP_F::minify_js($header_js); 
  else
    echo $header_js; 
?>
</body>
</html>