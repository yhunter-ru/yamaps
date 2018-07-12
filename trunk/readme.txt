=== YaMaps for WordPress Plugin ===
Contributors: yhunter
Donate link: https://www.paypal.me/yhunter
Tags: yandex, яндекс, карты, coordinates, maps, placemark
Requires at least: 4.2
Tested up to: 4.9.7
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: yamaps

The plugin allows you to add Yandex Maps (Яндекс Карты) to pages of your site using a WordPress visual editor.

== Description ==

YaMaps plugin is the simplest way to insert Yandex maps on your site. 

= Plugin Highlights: =

* You can add maps to pages without coding.
* Or you can manually edit the shortcodes in the editor.
* You can add any number of maps to one page.
* You can add multiple markers to one card.
* You can add markers with hyperlinks.
* You can select the icon and it's color of the marker in the colorpicker.
* You can select type of map (Map, Satellite, Hybrid), map zoom, map controls in the visual editor.

= Shortcodes Structure =

* yamap center - Map center coordinates
* yamap height - Map height
* yamap zoom - Map zoom (0 to 19)
* yamap scrollzoom - Scrollwheel zoom lock (scrollzoom="0" for lock)
* yamap type - Map type (yandex#map, yandex#satellite, yandex#hybrid)
* yamap controls - Map controls separated by a semicolon (typeSelector;zoomControl;searchControl;routeEditor;trafficControl;fullscreenControl;geolocationControl)

* yaplacemark coord - Placemark coordinates
* yaplacemark icon - Placemark icon
* yaplacemark color - Marker color
* yaplacemark name - Placemark hint or content

* You can insert multiple placemark codes inside the maps's shortcode.

= Shortcode Example =

[yamap center="55.7532,37.6225" height="15rem" zoom="12" type="yandex#map" controls="typeSelector;zoomControl"][yaplacemark coord="55.7532,37.6225" icon="islands#blueRailwayIcon" color="#ff751f" name="Placemark"][/yamap]

== Installation ==

1. Upload `yamaps` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Do I need a Yandex Map API key for using YaMaps? =

No, You don't need it.

= How to choose the type and zoom of the map? =

Just set type and zoom of the map in the visual editor window. On the site it will be displayed identically.

= How to insert a map into my template as PHP code? =

Use the tag "echo do_shortcode('')" with your map shortcode insde.

= How to set an icon that is not in the drop-down list? =

You can chose icon at https://tech.yandex.com/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/ and set it manually to the "Icon" field. For example "islands#blueRailwayIcon".

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
