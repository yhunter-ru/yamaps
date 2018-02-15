=== YaMaps for WordPress Plugin ===
Contributors: yhunter
Donate link: https://www.paypal.me/yhunter
Tags: coordinates, maps, geolocation, location, placemark, yandex
Requires at least: 4.2
Tested up to: 4.9.3
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 

The plugin allows you to add Yandex Maps to pages of your site using a WordPress visual editor.

== Description ==

YaMaps plugin is the simplest way to insert Yandex maps on your site. 

= Plugin Highlights: =

* You can add maps to pages without coding.
* Or you can manually edit the shortcodes in the editor.
* You can add any number of maps to one page.
* You can add multiple markers to one card.
* You can select the color of the marker in the colorpicker.
* You can select type of map (Map, Satellite, Hybrid), map zoom, map controls in the visual editor.

= Shortcodes Structure =

* yamap center - Map center coordinates
* yamap zoom - Map zoom (0 to 19)
* yamap type - Map type (yandex#map, yandex#satellite, yandex#hybrid)
* yamap controls - Map controls separated by a semicolon (typeSelector;zoomControl;searchControl;routeEditor;trafficControl;fullscreenControl;geolocationControl)

* yaplacemark coord - Placemark coordinates
* yaplacemark color - Marker color
* yaplacemark name - Placemark hint

* You can insert multiple placemark codes inside the maps's shortcode.

= Shortcode Example =

[yamap center="58.0096,56.2455" zoom="12" type="yandex#hybrid" controls="zoomControl"]
	[yaplacemark coord="58.0096,56.2455" color="#ff1f96" name="Placemark Hint"]
[/yamap]

== Installation ==

1. Upload `yamaps` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Do I need a Yandex Map API key for using YaMaps? =

No, I don't need it.

= Russian description =
https://www.yhunter.ru/portfolio/dev/yamaps/

 == Screenshots == 

1. Shortcode in TinyMCE editor.
2. Map on the blog page.
3. Shortcode button.
4. Visual shortcode dialog window.
5. Visual selecting the marker color.

== Changelog ==

= 0.1 =
* Initial release
