<?php

  //** Bail out if page is being loaded directly - stripslashes_deep() is a WP function. */
  if(!function_exists('stripslashes_deep')) {
    die();    
  }
  
  global $wp_query, $denali_theme_settings;
  
?>
<?php @get_header() ?>

<?php if(current_theme_supports('home_page_attention_grabber_area') && $denali_theme_settings['home_page_attention_grabber_area_hide'] != 'true'): ?>
<div class="slide sld-flexible">
<div class="sld-top"></div>
<?php if(function_exists('global_slideshow') && $global_slideshow = global_slideshow(true)): ?>
    <?php echo $global_slideshow; ?>    
  <?php else: ?>
  <div class="wpp_slideshow_global_wrapper">
    <?php denali_header_image(); ?>
  </div>
  <?php endif; ?>

  <?php if($denali_theme_settings['hide_slideshow_search'] != 'true'): ?>
    <div id="global_property_search_home">
        <?php if(is_active_sidebar('global_property_search')) { dynamic_sidebar( 'global_property_search' );  }?>
    </div>
  <?php endif; ?>
  <div class="sld-bottom"></div>
</div>
  <?php endif; /*home_page_attention_grabber_area*/  ?>
<div id="content">
  

    <div class="home <?php if(is_active_sidebar('home_sidebar')): ?>main<?php else: ?>main wide-home<?php endif;?>">

    <?php get_template_part( 'loop', 'home' ); ?>

    <div class="content_horizontal_widget widget_area clearfix">
    <?php dynamic_sidebar( 'home_bottom_sidebar' ); ?>
    </div>

        <div class="cboth"></div>
    </div>
  <?php if(is_active_sidebar('home_sidebar')): ?>
    <div class="sidebar"><?php dynamic_sidebar( 'home_sidebar' ); ?></div>
  <?php endif; ?>
    <div class="cboth"></div>
</div>
<?php get_footer() ?>
