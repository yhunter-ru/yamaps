<?php
/**
 * Plugin Name: YaMaps for Wordpress
 * Description: Yandex Map integration
 * Plugin URI:  www.yhunter.ru/portfolio/dev/yamaps/
 * Author URI:  www.yhunter.ru
 * Author:      yhunter
 * Version:     0.3.0
 *
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yamaps
 * Domain Path: /languages/
 *
 */

$maps_count=0;


function yaplacemark_func($atts) {
	$atts = shortcode_atts( array(
		'coord' => '',
		'name' => '',
		'color' => 'blue',
		'url' => '',
		'icon' => 'islands#dotIcon',
	), $atts );
	global $yaplacemark_count;
	$yaplacemark_count++;
	$yahint="";
	$yacontent="";
	$yaicon=trim($atts["icon"]);


	if (($yaicon==="islands#blueStretchyIcon")or($yaicon==="islands#blueIcon")or($yaicon==="islands#blueCircleIcon")) {
		$yahint="";
		$yacontent=$atts["name"];
	}
	else {
		$yahint=$atts["name"];
		$yacontent="";
	}

	$yaplacemark='
		placemark'.$yaplacemark_count.' = new ymaps.Placemark(['.$atts["coord"].'], {
                                hintContent: "'.$yahint.'",
                                iconContent: "'.$yacontent.'",


                              
                            }, {
                            	preset: "'.$atts["icon"].'", //https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/
                            	iconColor: "'.$atts["color"].'"
                            });  
	';

	if (trim($atts["url"])<>"") {
		$yaplacemark.=' 
			placemark'.$yaplacemark_count.'.events.add("click", function () {
                location.href="'.$atts["url"].'";
            });
		';
	}
	

	return $yaplacemark;

}

function yamap_func($atts, $content){
	$atts = shortcode_atts( array(
		'center' => '55.7532,37.6225',
		'zoom' => '12',
		'type' => 'map',
		'height' => '20rem',
		'controls' => '',
		'scrollzoom' => '1',

	), $atts );
	global $yaplacemark_count;
	global $yacontrol_count;
	global $maps_count;
	$yaplacemark_count=0;
	$yacontrol_count=0;

	$yamactrl=str_replace(';', '", "', $atts["controls"]);

	if (trim($yamactrl)<>"") $yamactrl='"'.$yamactrl.'"';

	if ($maps_count==0) {
		$yamap='<script src="https://api-maps.yandex.ru/2.1/?lang='.get_locale().'" type="text/javascript"></script>
                    ';
	}
	
	$placemarkscode=str_replace("&nbsp;", "", strip_tags($content));

    $yamap.='

						<script type="text/javascript">
                        ymaps.ready(init);
                 
                        function init () {
                            var myMap'.$maps_count.' = new ymaps.Map("yamap'.$maps_count.'", {
                                    center: ['.$atts["center"].'],
                                    zoom: '.$atts["zoom"].',
                                    type: "'.$atts["type"].'",
                                    controls: ['.$yamactrl.'] 
                                });   

							'.do_shortcode($placemarkscode);


							
							for ($i = 1; $i <= $yaplacemark_count; $i++) {
								if ($i>1) $placearr.='.';
								$placearr.='add(placemark'.$i.')';
							}
                            $yamap.='myMap'.$maps_count.'.geoObjects.'.$placearr.';';
                            if ($atts["scrollzoom"]=="0") $yamap.="myMap".$maps_count.".behaviors.disable('scrollZoom');";
                            $yamap.='

                        }
                    </script>
                    <div class="mist" id="yamap'.$maps_count.'"  style="position: relative; min-height: '.$atts["height"].'; margin-bottom: 1rem;"></div>
    ';

    $maps_count++;
    return $yamap; 
}

add_shortcode( 'yaplacemark', 'yaplacemark_func' );  
add_shortcode( 'yamap', 'yamap_func' ); 
add_shortcode( 'yacontrol', 'yacontrol_func' ); 


function yamaps_plugin_load_plugin_textdomain() {
    load_plugin_textdomain( 'yamaps', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'yamaps_plugin_load_plugin_textdomain' );


// Add map button

function yamap_plugin_scripts($plugin_array)
{
    
    // Plugin localization

	wp_register_script('yamap_plugin', plugin_dir_url(__FILE__) . 'js/localization.js');
	wp_enqueue_script('yamap_plugin');
	
	$lang_array	 = array('YaMap' => __('Map', 'yamaps'),
							'AddMap' => __('Add map', 'yamaps'),
							'MarkerTab' => __('Placemark', 'yamaps'),
							'MapTab' => __('Map', 'yamaps'),
							'MarkerIcon' => __('Icon', 'yamaps'),
							'BlueOnly' => __('Blue only', 'yamaps'),
							'MarkerUrl' => __('Link', 'yamaps'),
							'MarkerUrlTip' => __('Placemark hyperlink url', 'yamaps'),
							'MapHeight' => __('Map height', 'yamaps'),
							'MarkerName' => __('Placemark name', 'yamaps'),
							'MarkerNameTip' => __('Text for hint or icon content', 'yamaps'),
							'MapControlsTip' => __('Use the links below', 'yamaps'),		
							'MarkerCoord' => __('Сoordinates', 'yamaps'),
							'MapControls' => __('Map controls', 'yamaps'),
							'type' => __('Map type', 'yamaps'),
							'zoom' => __('Zoom', 'yamaps'),
							'ScrollZoom' => __('Wheel zoom', 'yamaps'),
							'search' => __('Search', 'yamaps'),
							'route' => __('Route', 'yamaps'),
							'ruler' => __('Ruler', 'yamaps'),
							'traffic' => __('Traffic', 'yamaps'),
							'fullscreen' => __('Full screen', 'yamaps'),
							'geolocation' => __('Geolocation', 'yamaps'),
							'MarkerColor' => __('Marker color', 'yamaps'));



	
	wp_localize_script('yamap_plugin', 'yamap_object', $lang_array); 
	

	//enqueue TinyMCE plugin script with its ID.

	$plugin_array["yamap_plugin"] =  plugin_dir_url(__FILE__) . "js/btn.js";

    return $plugin_array;

}





add_filter("mce_external_plugins", "yamap_plugin_scripts");

function register_buttons_editor($buttons)
{
    //register buttons with their id.
    array_push($buttons, "yamap");
    return $buttons;
}


add_filter("mce_buttons", "register_buttons_editor");


?>