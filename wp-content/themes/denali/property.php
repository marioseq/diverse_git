<?php
/**
 * Property Default Template for Single Property View
 *
 * Overwrite by creating your own in the theme directory called either:
 * property.php
 * or add the property type to the end to customize further, example:
 * property-building.php or property-floorplan.php, etc.
 *
 * By default the system will look for file with property type suffix first,
 * if none found, will default to: property.php
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 *
 * @version 1.4
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package WP-Property
*/

global $wp_properties,$post;
$map_image_type = $wp_properties['configuration']['single_property_view']['map_image_type'];

$slideshow_dimensions = WPP_F::get_image_dimensions($wp_properties['configuration']['feature_settings']['slideshow']['property']['image_size']);

$slideshow_height = (!empty($slideshow_dimensions[1]) ? $slideshow_dimensions[1] : 180);

$denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
?>

<?php get_header(); ?>
    <div id="content" role="main" class="property_content">
    
    <?php 
    if($denali_theme_settings['never_show_property_slideshow'] != 'true' ) { 
      $slideshow = property_slideshow(false, true); 
    }    
     //** Show header area if current theme support it, and if there's either a slideshow or show_this_header_area() return true  */
    if(current_theme_supports('inner_page_slideshow_area') && (denali_theme::show_this_header_area() || $slideshow)): ?>

            <div class="sld-flexible">
                <div class="sld-top"></div>
                <?php preg_match('/\[\s*property_slideshow\s*\]/', $post->post_content, $slideshow_matches); ?>
                <?php if ($slideshow && empty($slideshow_matches)): ?>
                <div class="property_slideshow"><?php echo $slideshow; ?></div>
                <?php else: ?>    
                <div class="image_wrapper"><?php denali_header_image(); ?></div>
                <?php endif; ?>
                <div class="sld-bottom"></div>
            </div>
    <?php endif; /* inner_page_slideshow_area */ ?>
 
    <?php the_post(); ?>
  <div id="post-<?php the_ID(); ?>" <?php post_class('main'); ?>>
    <div id="container" class="<?php echo (!empty($post->property_type) ? $post->property_type . "_container" : "");?>">
      <div class="building_title_wrapper">
        <h1 class="property-title entry-title"><?php the_title(); ?></h1>
        <h3 class="entry-subtitle"><?php the_tagline(); ?></h3>
      </div>
    <div class="entry-content">
      <?php @the_content(); ?>

        <dl id="property_stats" class="overview_stats">
          <?php if(!empty($post->display_address)):  ?>
          <dt class="wpp_stat_dt_location"><?php echo $wp_properties['property_stats'][$wp_properties['configuration']['address_attribute']]; ?></dt>
          <dd class="wpp_stat_dd_location alt"><?php echo $post->display_address; ?>&nbsp;</dd>
          <?php endif; ?>
          <?php draw_stats("make_link=true&return_blank=false&exclude={$wp_properties['configuration']['address_attribute']}"); ?>
        </dl>

       <?php if(!empty($wp_properties['taxonomies'])) foreach($wp_properties['taxonomies'] as $tax_slug => $tax_data): ?>
        <?php if(get_features("type=$tax_slug&format=count")):  ?>
        <div class="<?php echo $tax_slug; ?>_list features_list">
        <h2><?php echo $tax_data['label']; ?></h2>
        <ul class="wp_<?php echo $tax_slug; ?>s  wpp_feature_list clearfix">
        <?php get_features("type=$tax_slug&format=list&links=false"); ?>
        </ul>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>    

        <br class="cboth" />

      <?php if(is_array($wp_properties['property_meta'])): ?>
      <?php foreach($wp_properties['property_meta'] as $meta_slug => $meta_title):
          if(empty($post->$meta_slug) || $meta_slug == 'tagline')
              continue;
      ?>
          <h2><?php echo $meta_title; ?></h2>
          <p><?php echo do_shortcode(html_entity_decode($post->$meta_slug)); ?></p>
      <?php endforeach; ?>
      <?php endif; ?>
  </div><!-- .entry-content -->

  <?php if(WPP_F::get_coordinates()): ?>
  <div class="property_map_wrapper">
            <div id="property_map" style="width:100%; height:450px"></div>
  </div>
        <?php endif; ?>

    <?php if(class_exists('WPP_Inquiry')): ?>
        <h2><?php _e('Interested?','wpp') ?></h2>
        <?php WPP_Inquiry::contact_form(); ?>
    <?php else: ?>

      <?php if(comments_open()): ?>
        <?php
          if($denali_theme_settings[show_property_comments] == 'true')
            $title_reply = __('Comment About', 'wpp') . ' ' . $post->post_title;
          else
            $title_reply = __('Inquire About', 'wpp') .' '. $post->post_title;
          
          if (function_exists('wpp_inquiry_form')) {
            wpp_inquiry_form("title_reply=$title_reply&comment_notes_after=&comment_notes_before=");
          } else {
            comment_form("title_reply=$title_reply&comment_notes_after=&comment_notes_before=");
          }
        ?>
      <?php endif; ?>

       <?php if($denali_theme_settings['show_property_comments'] == 'true'): ?>
        <ol class="commentlist">
          <?php wp_list_comments( array( 'callback' => 'denali_comment' ), get_comments( array('post_id' => $post->ID, 'status' => 'approve', 'order' => 'ASC') ));?>
        </ol>
      <?php endif; ?>
      <?php endif; ?>
 
    </div><!-- #container -->

    <?php if ( is_active_sidebar( "denali_property_footer") ) : ?>
  <div class="content_horizontal_widget widget_area clearfix">
      <?php dynamic_sidebar( "denali_property_footer"); ?>
   </div>
  <?php endif; ?>
</div>

        <?php if ( is_active_sidebar( "wpp_sidebar_" . $post->property_type ) ) : ?>
        <div class="sidebar <?php echo "wpp_sidebar_" . $post->property_type; ?>">
      <ul>
        <?php dynamic_sidebar( "wpp_sidebar_" . $post->property_type ); ?>
      </ul>
         </div>
        <?php endif; ?>

 <div class="cboth"></div>

    </div>
    <?php ob_start(); ?><script type='text/javascript'>
        jQuery(document).ready(function() {
            jQuery("a.fancybox_image, .gallery-item a").fancybox({
                'transitionIn'  :   'elastic',
                'transitionOut' :   'elastic',
                'speedIn'       :   600,
                'speedOut'      :   200,
                'overlayShow'   :   false
            });
            <?php if($coords = WPP_F::get_coordinates()): ?>
            initializeMap();
            <?php endif; ?>
        });
        
        <?php if($coords = WPP_F::get_coordinates()): ?>
        function initializeMap() {
            if(typeof google.maps != 'undefined') {
                var myLatlng = new google.maps.LatLng(<?php echo $coords[latitude]; ?>,<?php echo $coords[longitude]; ?>);
                var myOptions = {
                  zoom: <?php echo (!empty($wp_properties[configuration][gm_zoom_level]) ? $wp_properties[configuration][gm_zoom_level] : 13); ?>,
                  center: myLatlng,
                  mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                
                var map = new google.maps.Map(document.getElementById("property_map"), myOptions);
                
                var marker = new google.maps.Marker({
                    position: myLatlng,
                    map: map,
                    title: '<?php echo addslashes($post->post_title); ?>'
                });
                
                var infowindow = new google.maps.InfoWindow({
                    content: '<?php echo WPP_F::google_maps_infobox($post); ?>'
                });
                
                // Hack. Sets timout 3 sec, because map doesn't catch to loads all functionality,
                // so infobox can be cutted off.
                setTimeout(function(){
                    infowindow.open(map,marker);
                    
                    google.maps.event.addListener(infowindow, 'domready', function() {
                        document.getElementById('infowindow').parentNode.style.overflow='';
                        document.getElementById('infowindow').parentNode.parentNode.style.overflow='';
                    });
                    
                    google.maps.event.addListener(marker, 'click', function() {
                        infowindow.open(map,marker);
                    });
                }, 3000);
            }
        }
        <?php endif; ?>
    </script>
<?php $header_js = ob_get_contents(); ob_end_clean(); ?>
<?php if(class_exists('WPP_F')) echo WPP_F::minify_js($header_js); else echo $header_js; ?>
<?php get_footer(); ?>