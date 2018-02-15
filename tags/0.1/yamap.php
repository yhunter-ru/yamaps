<?php
/**
 * Plugin Name: YaMaps for Wordpress
 * Description: Yandex Map integration
 * Plugin URI:  Ссылка на инфо о плагине
 * Author URI:  www.yhunter.ru
 * Author:      yhunter
 * Version:     0.1
 *
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

$maps_count=0;


function yaplacemark_func($atts) {
	$atts = shortcode_atts( array(
		'coord' => '',
		'name' => '',
		'color' => 'blue',
	), $atts );
	global $yaplacemark_count;
	$yaplacemark_count++;
	$yaplacemark='
		placemark'.$yaplacemark_count.' = new ymaps.Placemark(['.$atts["coord"].'], {
                                hintContent: "'.$atts["name"].'",
                              
                            }, {
                            	preset: "islands#dotIcon", //https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/
                            	iconColor: "'.$atts["color"].'"
                            });  
	';
	

	return $yaplacemark;

}

function yamap_func($atts, $content){
	$atts = shortcode_atts( array(
		'center' => '55.7532,37.6225',
		'zoom' => '16',
		'type' => 'map',
		'height' => '20rem',
		'controls' => ''
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

							'.do_shortcode($content);


							
							for ($i = 1; $i <= $yaplacemark_count; $i++) {
								if ($i>1) $placearr.='.';
								$placearr.='add(placemark'.$i.')';
							}
                            $yamap.='myMap'.$maps_count.'.geoObjects.'.$placearr.';';
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


// Add map button

function yamap_plugin_scripts($plugin_array)
{
    //enqueue TinyMCE plugin script with its ID.
    $plugin_array["yamap_plugin"] =  plugin_dir_url(__FILE__) . "js/btn.js";
    return $plugin_array;
}

// Plugin localisation

add_filter('mce_external_languages', 'yamap_plugin_scripts_add_locale');
add_filter("mce_external_plugins", "yamap_plugin_scripts");

function register_buttons_editor($buttons)
{
    //register buttons with their id.
    array_push($buttons, "yamap");
    return $buttons;
}


add_filter("mce_buttons", "register_buttons_editor");


?>