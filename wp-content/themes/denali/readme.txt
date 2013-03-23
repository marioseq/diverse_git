Denali WordPress Theme By TwinCitiesTech.com - http://twincitiestech.com
Theme Homepage -  http://twincitiestech.com/plugins/wp-property/wp-property-premium-theme-the-denali/
-------------------------------------------------------------------------------------------------

== Changelog ==

= 2.1 =
* New "Setup Assistant" feature for quick setup, example: http://vimeo.com/26637788
* New navigation menu available at the very bottom of the screen.
* Denali Child theme is now packaged into the main theme and can be installed automatically using the Settings page.
* New widget area (sidebar) now available and is included on all pages that include the [property_overview] shortcode.
* Added the wpp_inquiry_form() function to replace the default comment forum function. 
* Upgraded theme to use the new wpp_get_image_link() function for on-the-fly image resizing. 
* Added new feature where skins can have thumbnails associated with them. 

= 2.0.4 =
* Fixed a property overview bug that occured when no default address attribute was set but address attribute was set to display on results. 

= 2.0.3 =
* Added JavaScript code to adjust margins on home and post page for situations when slideshow is not as tall as property search widget that overlays it.
* Fixed positioning of slideshow scrolling arrows on home page slideshow.

= 2.0.2 =
* Added shortcode execution to property meta fields. 
* Fixed footer to prevent overlap of EHO icon when Explore block is hidden.
* Added option to make custom inquiry fields required.
* Added JavaScript validation of forms (inquiry and comment)
* Fixed static width issue or horizontal widget area when using no-column templates.
* Added fixed title width to sidebar property widgets to resolve problem mentioned here: http://forums.twincitiestech.com/topic/featured-properties-title-not-wrapping-properly


= 1.9  =
* Fixes to dark color scheme.
* Removed text from sprite image, and changes style.css file to not hide text labels on buttons.
* Fixed property_overview width issues for home page.
* Added 	current_theme_supports() elements: inner_page_slideshow_area, post_page_attention_grabber_area, footer_explore_block
* Hid thumbnail from property overview shortcode if no thumbnail exists.
* Added numerous localization strings which can be inherited from WP-Property.
* Added 'denali_header_menu' filter to navigation menu.
* Added option to hide header image completely if there is no big enough image for the given page.
* Added option to hide slideshow areas on home and post pages.
* Added option to hide explorer block. 

 