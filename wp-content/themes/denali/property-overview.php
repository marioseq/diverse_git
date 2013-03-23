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

global $denali_theme_settings, $img_width, $img_height;

if(empty($denali_theme_settings['property_overview_attributes']['stats']))
  $denali_theme_settings['property_overview_attributes']['stats'] = array('display_address', 'price', 'bedrooms', 'bathrooms', 'garage');

if($properties): ?>

    <div class="wpp_row_view all-properties wpp_property_view_result">
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
        
        if(empty($img_width)) {
          $img_width = $image['width'];
        } 

        if(empty($img_height)) {
          $img_height = $image['height'];
        }
      ?>
    
    <div class="property_div <?php echo $property['post_type']; ?> clearfix" style="<?php echo (!empty($img_height) ? 'min-height:' . ($img_height + 40) . 'px' : ''); ?>">

        <div class="wpp_overview_left_column" >

        
        <?php if(!empty($image)): ?>
            <div class="property_image">
                <a href="<?php echo $thumbnail_link; ?>"
                    title="<?php echo $property['post_title'] . ($property['parent_title'] ?  __(' of ', 'wpp') . $property[parent_title] : "");?>"
                    class="property_overview_thumb property_overview_thumb_<?php echo $thumbnail_size; ?> <?php echo $link_class; ?>"
                    rel="properties">
                    <img width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" src="<?php echo $image['link']; ?>" alt="<?php echo $property['post_title'];?>" />
                </a>
            </div>
        <?php endif; ?>

        </div><?php // .wpp_overview_left_column ?>

        <div class="wpp_overview_right_column" style="<?php echo ((!empty($img_width) && !empty($property['images'][$thumbnail_size])) ? 'margin-left:' . ($img_width + 40) . 'px' : ''); ?>">

            <ul class="wpp_overview_data" style="">
                <li class="property_title">
                    <a href="<?php echo $property['permalink']; ?>"><?php echo $property['post_title']; ?></a>
                    <?php if($property['is_child']): ?>
                        of <a href='<?php echo $property['parent_link']; ?>'><?php echo $property['parent_title']; ?></a>
                    <?php endif; ?>
                </li>


            <?php if($denali_theme_settings['property_overview_attributes']['stats']) foreach($denali_theme_settings['property_overview_attributes']['stats'] as $attribute): ?>
            <?php if($property[$attribute]): ?>
                <li class="property_<?php echo $attribute; ?>">

                  <span class="wpp_attribute_label hidden"><?php echo ($wp_properties['property_stats'][$attribute] ? $wp_properties['property_stats'][$attribute] .':'  : UD_F::de_slug($attribute) . ':' );  ?></span>
                  <?php echo $property[$attribute]; ?>
                  <span class="wpp_attribute_icon icon_<?php echo $attribute; ?>"></span>
                </li>
            <?php endif; ?>
            <?php endforeach; ?>
            </ul>

          <?php 
          unset($overview_attributes);
          if($denali_theme_settings['property_overview_attributes']['detail'])
            foreach($denali_theme_settings['property_overview_attributes']['detail'] as $attribute) {
            
            $attribute_title = ($wp_properties['property_stats'][$attribute] ? $wp_properties['property_stats'][$attribute] .':'  : UD_F::de_slug($attribute) . ':' );
            
            if($attribute == 'post_content' || $attribute == 'post_excerpt') 
             $attribute_title = '';
             
            // Replace address attribute with display address attribute
              if($attribute == $wp_properties['configuration']['address_attribute'])
                $property[$attribute] = $property['display_address'];
            
              if($property[$attribute]) {
                
                 if($attribute == 'post_content' || $attribute == 'post_description') {
                   $property[$attribute] = nl2br($property[$attribute]);
                }
                   
                $property[$attribute] = do_shortcode(html_entity_decode($property[$attribute]));
                   
                $overview_attributes[] = "<li class='property_{$attribute}'>" . ($attribute_title ? "<span class='wpp_attribute_icon icon_{$attribute}'></span><span class='attribute'>{$attribute_title}</span>" : "") . " <span class='value'>{$property[$attribute]}</span></li>"; 
               }
             }
          ?>
          
          <?php if($overview_attributes): ?>
            <ul class="wpp_overview_data_detail" style="">
              <?php echo implode('', $overview_attributes); ?>
            </ul>
          <?php endif; ?>

          <?php if($show_children && $property['children']): ?>
             <div class="child_properties">
                <div class="wpd_floorplans_title"><?php echo $child_properties_title; ?></div>
                <table class="wpp_overview_child_properties_table">
                    <?php foreach($property['children'] as $child): ?>
                    <tr class="property_child_row">
                        <th class="property_child_title"><a href="<?php echo $child['permalink']; ?>"><?php echo $child['post_title']; ?></a></th>
                        <td class="property_child_price"><?php echo $child['price']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
           <?php endif; ?>
       

        </div><?php // .wpp_right_column ?>

    </div><?php // .property_div ?>

    <?php endforeach; ?>
    </div><?php // .wpp_row_view ?>
   <?php else: ?>
<div class="wpp_nothing_found">
   <?php echo sprintf(__('Sorry, no properties found - try expanding your search, or <a href="%s">view all</a>.','wpp'), site_url().'/'.$wp_properties['configuration']['base_slug']); ?>
</div>
<?php endif; ?>
<br class="cb" />
