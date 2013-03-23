<?php
/**
 * WP-Property Overview Template
 *
 * To customize this file, copy it into your theme directory, and the plugin will
 * automatically load your version.
 *
 * You can also customize it based on property type.  For example, to create a custom
 * overview page for 'building' property type, create a file called property-overview-building.php
 * into your theme directory.
 *
 *
 * Settings passed via shortcode:
 * $properties: either array of properties or false
 * $show_children: default true
 * $thumbnail_size: slug of thumbnail to use for overview page
 * $thumbnail_sizes: array of image dimensions for the thumbnail_size type
 * $fancybox_preview: default loaded from configuration
 * $child_properties_title: default "Floor plans at location:"
 *
 *
 *
 * @version 1.4
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package WP-Property
*/

global $denali_theme_settings;

$thumbnail_size = 'sidebar_gallery';

 if(empty($denali_theme_settings['grid_property_overview_attributes']['stats']))
  $denali_theme_settings['grid_property_overview_attributes']['stats'] = array('display_address', 'price', 'bedrooms', 'bathrooms', 'garage');

if($properties): ?>
<script type="text/javascript"><?php do_action('wpp_js_on_property_overview_display', 'grid'); ?> </script>
<div class="wpp_grid_view all-properties wpp_property_view_result clearfix">
<?php
unset($properties['total']); // VERY IMPORTANT!!!
foreach($properties as $property_id):
    // Get property array/object and run it through prepare_property_for_display(), which runs all filters
    $property = prepare_property_for_display(get_property($property_id, "get_property['children']={$show_property['children']}"));

    // Configure variables
    if($fancybox_preview) {
        $thumbnail_link = $property['featured_image_url'];
        $link_class = 'fancybox_image';
    } else {
        $thumbnail_link = $property['permalink'];
    }
    
    $image = wpp_get_image_link($property['featured_image'], $thumbnail_size, array('return'=>'array'));

  ?>
<div class="property_div <?php echo $property['post_type']; ?> <?php echo $property['property_type']; ?> clearfix">
 
    <?php if($property['images'][$thumbnail_size]): ?>
        <div class="property_image">
            <a href="<?php echo $thumbnail_link; ?>"
                title="<?php echo $property['post_title'] . ($property['parent_title'] ?  __(' of ', 'wpp') . $property[parent_title] : "");?>"
                class="property_overview_thumb property_overview_thumb_<?php echo $thumbnail_size; ?> <?php echo $link_class; ?>"
                rel="properties">
                <img width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" src="<?php echo $image['link']; ?>" alt="<?php echo $property['post_title'];?>" />
            </a>
        </div>
    <?php endif; ?>


      <ul class="wpp_overview_data" style="">
          <li class="property_title">
              <a href="<?php echo $property['permalink']; ?>"><?php echo $property['post_title']; ?></a>
              <?php if($property['is_child']): ?>
                  of <a href='<?php echo $property['parent_link']; ?>'><?php echo $property['parent_title']; ?></a>
              <?php endif; ?>
          </li>
 
      <?php if($denali_theme_settings['grid_property_overview_attributes']['stats']) foreach($denali_theme_settings['grid_property_overview_attributes']['stats'] as $attribute): ?>
      <?php 
      $attribute_title = ($wp_properties['property_stats'][$attribute] ? $wp_properties['property_stats'][$attribute]  : UD_F::de_slug($attribute)); 
                  
      // Replace address attribute with display address attribute
      if($attribute == $wp_properties['configuration']['address_attribute'])
        $property[$attribute] = $property['display_address'];
        
      if($property[$attribute]): ?>
          <?php echo "<li class='property_{$attribute}'><span class='wpp_attribute_icon icon_{$attribute}'></span><span class='attribute'>{$attribute_title}:</span> <span class='value'>{$property[$attribute]}</span></li>"; ?>
      <?php endif; ?>
      <?php endforeach; ?>
      </ul>
 </div><?php // .property_div ?>

<?php endforeach; ?>
</div><?php // .wpp_grid_view ?>
   <?php else: ?>
<div class="wpp_nothing_found">
   <?php echo sprintf(__('Sorry, no properties found - try expanding your search, or <a href="%s">view all</a>.','wpp'), site_url().'/'.$wp_properties['configuration']['base_slug']); ?>
</div>
<?php endif; ?>
