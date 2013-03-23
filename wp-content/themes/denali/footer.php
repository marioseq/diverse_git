</div>

<?php
$denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
$longitude = $denali_theme_settings['longitude'];
$latitude = $denali_theme_settings['latitude'];

if($footer_menu = wp_nav_menu(array(
  'theme_location'  => 'footer-menu',
  'menu_class'      => 'footer-nav',
  'before'          => '<span class="menu"><span class="left_top"></span><span class="right_top"></span>',
  'after'           => '</span>',
  'echo'           => false,
  'fallback_cb'     => 'denali_list_pages'
))) echo ($footer_menu ? "<div class='menu-footer-container'>{$footer_menu}</div>" : ''); ?>
 
<div class="bottom"></div>
</div>
<div class="footer ">
    <div class='inner_footer'>
    <?php if ( is_active_sidebar( "latest_listings" ) ) : ?>
      <div class="foot_last_list">
          <?php dynamic_sidebar( 'latest_listings' ); ?>
      </div>
    <?php endif; ?>    
    <?php if(current_theme_supports('footer_explore_block') && $denali_theme_settings['footer_explore_block_hide'] != 'true'): ?>
      <div class="foot_explore <?php if (!is_active_sidebar( "latest_listings" ) ) : ?>big<?php endif; ?>">
          
      <?php if($denali_theme_settings['footer_explore_title_hide'] != 'true'): ?>
      <h5><?php _e("Explore", 'wpp'); ?></h5>
      <?php endif; ?>
        
  <?php 
    $explore = $denali_theme_settings['options_explore'];
   
   if ($explore == 'custom_html'){

      echo do_shortcode(nl2br($denali_theme_settings['explore']['custom_html_content']));
       
   }
       
   if ($explore == 'pages'){       
       ?> <ul> <?php
       wp_list_pages('title_li=&number=8&offset=0&depth=1');
       ?></ul><ul> <?php
       wp_list_pages('title_li=&number=8&offset=8&depth=1');
       ?> </ul> <?php
       $pages = get_posts();
           $cats = explode("<br />",wp_list_categories('title_li=&echo=0&depth=1&style=none'));
    }
   
    if ($explore == 'cats'){
        $cats = explode("<br />",wp_list_categories('title_li=&echo=0&depth=1&style=none'));
        $cat_n = count($cats) - 1;
        for ($i=0;$i< $cat_n;$i++){
            if ($i<$cat_n/2){
                $cat_left = $cat_left.'<li>'.$cats[$i].'</li>';
            }elseif ($i>=$cat_n/2){
                $cat_right = $cat_right.'<li>'.$cats[$i].'</li>';
            }
        }
        ?>
        <ul class="left"><?php echo $cat_left;?></ul>
        <ul class="right"><?php echo $cat_right;?></ul>
   <?php } ?>
           
        </div>
    <?php endif; /*footer_explore_block */ ?>
        
        
        <p class="foot_cont">
            <?php echo (!empty($denali_theme_settings[phone]) ? "<span>{$denali_theme_settings[phone]}</span><br />": "");?>
            <?php echo get_bloginfo('description');?>
        </p>
        <div class="bot_right">
          <div class="bottom_right_icons">
            <?php denali_footer_follow($denali_theme_settings); ?>
            <?php if($denali_theme_settings['show_equal_housing_icon'] == 'true'): ?>
              <span class="equal_housing_icon">&nbsp;</span>
            <?php endif; ?>
          </div>
          <span class="copy"> Copyright &copy; <?php the_time('Y '); bloginfo(' name'); ?></span>
        </div>
        <div class="cboth"></div>
    </div>
</div>


<?php if($bottom_of_page_menu= wp_nav_menu(array(
  'theme_location'  => 'bottom_of_page_menu',
  'menu_class'      => 'bottom_of_page_menu',
  'before'          => '<span class="menu"><span class="left_top"></span><span class="right_top"></span>',
  'after'           => '</span>',
  'echo'           => false,
  'fallback_cb'     => 'denali_list_pages'
))) echo ($bottom_of_page_menu ? "<div class='bottom_of_page_menu'>{$bottom_of_page_menu}</div>" : ''); ?>
 
</div><?php //** .wrapper */ ?> 
 
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