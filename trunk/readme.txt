=== YaMaps for WordPress Plugin ===
Contributors: yhunter
Donate link: https://yoomoney.ru/to/41001278340150
Tags: yandex, яндекс, карты, карта, maps, placemark, elementor
Requires at least: 4.7
Tested up to: 6.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: yamaps

The plugin allows you to add Yandex Maps (Яндекс Карты) to pages of your site using a WordPress visual editor.

== Description ==

YaMaps plugin is the simplest way to insert Yandex maps on your site. The plugin has a user-friendly interface. You can visually put placemarks on your Yandex map, move them with your mouse, change icons and much more.

For use with the new Gutenberg editor, you need add the classic editor block first!

For the map search to work correctly and find routes, you may need to set an API key (JavaScript API и HTTP Geocoder) on the plugin settings page.

= Plugin Highlights: =

* You can add maps to pages without coding.
* Or you can visually edit the shortcodes in the editor.
* You can add any number of maps to one page.
* You can add multiple markers to one card.
* You can add markers with hyperlinks.
* You can select the icon and it's color of the marker in the colorpicker.
* You can select type of map (Map, Satellite, Hybrid), map zoom, map controls in the visual editor.

https://www.youtube.com/watch?v=m7YncsBrL5g

= Shortcodes Structure =

* yamap center - Map center coordinates
* yamap height - Map height
* yamap zoom - Map zoom (0 to 19)
* yamap scrollzoom - Scrollwheel zoom lock (scrollzoom="0" for lock)
* yamap mobiledrag - Map dragging can be disabled for mobile devices (mobiledrag="0" for lock)
* yamap type - Map type (yandex#map, yandex#satellite, yandex#hybrid)
* yamap controls - Map controls separated by a semicolon (typeSelector;zoomControl;searchControl;routeEditor;trafficControl;fullscreenControl;geolocationControl)
* yamap container - ID of the existing block in the WP template. The map will be placed in the block with this ID. The new block in the content will not be created.

* yaplacemark coord - Placemark coordinates
* yaplacemark icon - Placemark icon (Yandex.Map icon type or url of your own image)
* yaplacemark color - Marker color
* yaplacemark name - Placemark hint or content
* yaplacemark url - Linked URL or post with ID will be opened by click on the placemark

* You can insert multiple placemark codes inside the maps's shortcode.

= Shortcode Example =

[yamap center="55.7532,37.6225" height="15rem" zoom="12" type="yandex#map" controls="typeSelector;zoomControl"][yaplacemark coord="55.7532,37.6225" icon="islands#blueRailwayIcon" color="#ff751f" name="Placemark"][/yamap]

== Installation ==

1. Upload `yamaps` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Do I need a Yandex Map API key for using YaMaps? =

At the moment, the key is needed only for the search on the map. The rest of the plugin functionality will work without a key. You can get the key (https://developer.tech.yandex.ru/services/) and enter it in the plugin settings page.

= How to choose the type and zoom of the map? =

Just set type and zoom of the map in the visual editor window. On the site it will be displayed identically.

= How to use the plugin with new Gutenberg editor?  =

You can add a block with a classic editor and add a map with it. Later, native support can be added if most users start to use Gutenberg.

= How to insert a map into my template as PHP code? =

Use the tag "echo do_shortcode('')" with your map shortcode insde.

= How to set an icon that is not in the drop-down list? =

You can chose icon at https://tech.yandex.com/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/ and set it manually to the "Icon" field. For example "islands#blueRailwayIcon". Also you can insert the URL of your file in the field. For example, PNG-image with transparency.

= Why a can't change color of StretchyIcon? =

This is the limitation of Yandex.Map API. You can select a stretchy icon of the desired color at https://tech.yandex.com/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/ and set it manually.

= Russian description =
https://www.yhunter.ru/portfolio/dev/yamaps/

= GitHub Project =
https://github.com/yhunter-ru/yamaps

 == Screenshots == 

1. Visual shortcode dialog window.
2. Shortcode in TinyMCE editor.
3. Map on the blog page.
4. Shortcode button.
5. Visual selecting the marker color.

== Changelog ==

= 0.6.26 =
* Fixed: Bugfix.

= 0.6.25 =
* Fixed: WP 6.0 support

= 0.6.24 =
* Fixed: Bugfix.

= 0.6.23 =
* New: Yandex Map Api is called only for pages with a map.

= 0.6.22 =
* Fixed: Bugfix.

= 0.6.21 =

* Fixed: Adding WP 5.6 support

= 0.6.20 =

* Fixed: HTML validation fix
* Fixed: Bugfix.

= 0.6.19 =

* Fixed: Adding controls in the editor does not work if the API key is not entered in the plugin settings

= 0.6.18 =

* Edit: If no wp_footer() function is called in the template footer, the map API will be called in an alternative way.

= 0.6.17 =

* Edit: If the Yandex.Maps API key is not set in the plugin settings page, the search field on the map is not displayed.

= 0.6.16 =

* New: Yandex Map Api request and map moved to the footer to speed up content loading.
* Fixed: Bugfix.

= 0.6.15 =

* Fixed: Bugfix.

= 0.6.14 =

* Fixed: Bugfix.

= 0.6.13 =

* Fixed: Bugfix.

= 0.6.12 =

* Fixed: Bugfix.

= 0.6.11 =

* Fixed: Bugfix for WP 5.3.

= 0.6.10 =

* Fixed: Bugfix.

= 0.6.9 =

* Fixed: Bugfix.

= 0.6.8 =

* New: In the settings page, you can set the Yandex Maps API key if there are problems with limits.
* Fixed: Bugfix. Display maps in widgets and content same time could cause an error.

= 0.6.7 =

* The button for opening a large yandex map can be switched in the plugin settings.

= 0.6.6 =

* The button for opening a large yandex map was removed.

= 0.6.5 =

* Fixed: Bugfix. Improved compatibility with other plugins.

= 0.6.4 =

* Fixed: Bugfix. In rare cases, the problem of loading the API with custom fields.

= 0.6.3 =

* Fixed: Bugfix. Fixed conflict with Yandex.Metrica
* New: The plugin works in the Elementor editor.

= 0.6.2 =

* Fixed: Bugfix.

= 0.6.1 =

* Fixed: Bugfix.

= 0.6 =

* New: Interaction possibility with the map from other plugins and themes. The possibility of expanding the functionality of the plugin. Contact the author if you need additional features on your website.
* Code refactoring.
* Fixed: Bugfix.

= 0.5.11 =
* New: Map dragging can be disabled for mobile devices. 
* Fixed: Bugfix.

= 0.5.10 =
* Fixed: Bugfix.

= 0.5.9 =
* New: You can use your own icons. Place the link to the file in the icon field (url must contain "http").
* Fixed: Bugfix.

= 0.5.8 =
* New: Restore defaults button on the settings page.
* Fixed: Bugfix.

= 0.5.7 =
* Fixed: Incorrect "111" control in the settings blocked the rendering of elements on new maps.

= 0.5.6 =
* Fixed: Bugfix.

= 0.5.5 =
* Fixed: Bugfix.

= 0.5.4 =
* New: Links-helper for adding controls on the settings page.
* New: Link to plugin page (It can be disabled in the plugin settings).
* Fixed: Bugfix.

= 0.5.3 =
* New: Plugin default options page.
* Fixed: Bugfix.

= 0.5.2 =
* Fixed: Bugfix. 

= 0.5.1 =
* New: Adding styles for classic editor block in Gutenberg editor.

= 0.5.0 =
* New: Visual editing of existing maps.
* New: Nice looking shortcodes template in the TinyMCE editor with edit button.
* Code refactoring.
* Fixed: Bugfix. 

= 0.4.1 =
* New: "Extra" Panel tab added
* New: Instead of the URL, you can put a post ID.
* New: You can place the map in an existing block in the WordPress template. If filled, the map block in the content will not be created.

= 0.4.0 =
* New: Adding multiple placemarks in the visual editor
* Change: "routeEditor" map control replaced with "routeButtonControl" for a better experience. 

= 0.3.4 =
* New: Added map support in WooСommerce product description.

= 0.3.3 =
* Fixed: Bugfix. 

= 0.3.2 =
* Fixed: Bugfix.

= 0.3.1 =
* Fixed: Bugfix.

= 0.3.0 =
* Fixed: The way of localization of the plugin is changed. According to the WordPress documentation.

= 0.2.4 =
* New: Added scrollwheel zoom lock

= 0.2.3 =
* Fixed: Adding url tag from a visual editor

= 0.2.2 =
* Fixed: Strip html tags inside shortcode

= 0.2.1 =
* Fixed: Plugin info

= 0.2 =
* New: Added icon selection
* New: Added hyperlinks feature for the icons
* New: Added "Height" parameter for map
* Fixed: Automatically move the marker to the coordinates of the search result

= 0.1 =
* Initial release
