<?php
/**
 * Plugin Name: YaMaps for Wordpress
 * Description: Yandex Map integration
 * Plugin URI:  www.yhunter.ru/portfolio/dev/yamaps/
 * Author URI:  www.yhunter.ru
 * Author:      Yuri Baranov
 * Version:     0.5.3
 *
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yamaps
 * Domain Path: /languages/
 *
 */

$maps_count=0;

// Test for the first time content and single map (WooCommerce and other custom posts)
$count_content=0;

$yamaps_defaults = array(
	'center_map_option'			=> '55.7473,37.6247',
	'zoom_map_option'			=> '12',
	'type_map_option'			=> 'yandex#map',
	'height_map_option'			=> '17rem',
	'controls_map_option'		=> '',
	'wheelzoom_map_option'		=> 'on',
	'type_icon_option'			=> 'islands#dotIcon',
	'color_icon_option'			=> '#1e98ff'
);	

//Страница настроек

$option_name = 'yamaps_options';
if(get_option($option_name)){
    $yamaps_defaults=get_option( $option_name);
}



add_filter( 'the_content', 'tutsplus_the_content' ); 
function tutsplus_the_content( $content ) {
	global $count_content;
	$count_content++;
    return $content;
}


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


	if (strstr($yaicon, "Stretchy")<>FALSE) {
		$yahint="";
		$yacontent=$atts["name"];
		}
	else {
			if (($yaicon==="islands#blueIcon")or($yaicon==="islands#blueCircleIcon")) {
				$yahint=$atts["name"];
				$yacontent=mb_substr($yahint, 0, 1);
			}
			else {
				$yahint=$atts["name"];
				$yacontent="";
			}
	}
	
	

	$yaplacemark='
		placemark'.$yaplacemark_count.' = new ymaps.Placemark(['.$atts["coord"].'], {
                                hintContent: "'.$yahint.'",
                                iconContent: "'.$yacontent.'",


                              
                            }, {
                            	preset: "'.$atts["icon"].'", 
                            	iconColor: "'.$atts["color"].'"
                            });  
	';

	$atts["url"]=trim($atts["url"]);
	if (($atts["url"]<>"")and($atts["url"]<>"0")) {
		$marklink=$atts["url"];
		settype($marklink, "integer");
		if ($marklink<>0) {
			$marklink=get_the_permalink($atts["url"]);
		}
		else {
			$marklink=$atts["url"];
		}


			$yaplacemark.=' 
				placemark'.$yaplacemark_count.'.events.add("click", function () {
	                location.href="'.$marklink.'";
	            });
			';

	}
	

	return $yaplacemark;

}

function yamap_func($atts, $content){
	$placearr = '';
	$atts = shortcode_atts( array(
		'center' => '55.7532,37.6225',
		'zoom' => '12',
		'type' => 'map',
		'height' => '20rem',
		'controls' => '',
		'scrollzoom' => '1',
		'container' => '',

	), $atts );
	global $yaplacemark_count;
	global $yacontrol_count;
	global $maps_count;
	global $count_content;
	$yaplacemark_count=0;
	$yacontrol_count=0;

	$yamactrl=str_replace(';', '", "', $atts["controls"]);

	if (trim($yamactrl)<>"") $yamactrl='"'.$yamactrl.'"';

	if ($maps_count==0) { // Test for first time content and single map
		$yamap='<script src="https://api-maps.yandex.ru/2.1/?lang='.get_locale().'" type="text/javascript"></script>
                    ';
	}
	else {
		$yamap='';
	}
	
	$placemarkscode=str_replace("&nbsp;", "", strip_tags($content));

	$atts["container"]=trim($atts["container"]);
	if ($atts["container"]<>"") {
		$mapcontainter=$atts["container"];
		$mapcontainter=str_replace("#", "", $mapcontainter);
	}
	else {
		$mapcontainter='yamap'.$maps_count;
	}
	

    $yamap.='

						<script type="text/javascript">
                        ymaps.ready(init); 
                 
                        function init () {
                            var myMap'.$maps_count.' = new ymaps.Map("'.$mapcontainter.'", {
                                    center: ['.$atts["center"].'],
                                    zoom: '.$atts["zoom"].',
                                    type: "'.$atts["type"].'",
                                    controls: ['.$yamactrl.'] 
                                });   

							'.do_shortcode($placemarkscode);


							
							for ($i = 1; $i <= $yaplacemark_count; $i++) {
								$placearr.='.add(placemark'.$i.')';
							}
                            $yamap.='myMap'.$maps_count.'.geoObjects'.$placearr.';';
                            if ($atts["scrollzoom"]=="0") $yamap.="myMap".$maps_count.".behaviors.disable('scrollZoom');";
                            $yamap.='

                        }
                    </script>
                    
    ';
    if ($atts["container"]=="") $yamap.='<div id="'.$mapcontainter.'"  style="position: relative; min-height: '.$atts["height"].'; margin-bottom: 1rem;"></div>';

    if ($count_content>=1) $maps_count++;
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

	wp_register_script('yamap_plugin', plugin_dir_url(__FILE__) . 'js/shortcode_parser.js');
	wp_enqueue_script('yamap_plugin');
	
	$lang_array	 = array('YaMap' => __('Map', 'yamaps'),
							'AddMap' => __('Add map', 'yamaps'),
							'EditMap' => __('Edit map', 'yamaps'),
							'PluginTitle' => __('YaMaps plugin: Yandex.Map', 'yamaps'),
							'MarkerTab' => __('Placemark', 'yamaps'),
							'MapTab' => __('Map', 'yamaps'),
							'MarkerIcon' => __('Icon', 'yamaps'),
							'BlueOnly' => __('Blue only', 'yamaps'),
							'MarkerUrl' => __('Link', 'yamaps'),
							'MarkerUrlTip' => __('Placemark hyperlink url or post ID', 'yamaps'),
							'MapHeight' => __('Map height', 'yamaps'),
							'MarkerName' => __('Placemark name', 'yamaps'),
							'MarkerNameTip' => __('Text for hint or icon content', 'yamaps'),
							'MapControlsTip' => __('Use the links below', 'yamaps'),		
							'MarkerCoord' => __('Сoordinates', 'yamaps'),
							'NoCoord' => __('Click on the map to choose or create the mark', 'yamaps'),
							'MapControls' => __('Map controls', 'yamaps'),
							'MarkerDelete' => __('Delete', 'yamaps'),
							'type' => __('Map type', 'yamaps'),
							'zoom' => __('Zoom', 'yamaps'),
							'ScrollZoom' => __('Wheel zoom', 'yamaps'),
							'search' => __('Search', 'yamaps'),
							'route' => __('Route', 'yamaps'),
							'ruler' => __('Ruler', 'yamaps'),
							'traffic' => __('Traffic', 'yamaps'),
							'fullscreen' => __('Full screen', 'yamaps'),
							'geolocation' => __('Geolocation', 'yamaps'),
							'MarkerColor' => __('Marker color', 'yamaps'),
							'MapContainerID' => __('Put in ID', 'yamaps'),
							'MapContainerIDTip' => __('Do not create a block in the content. Use the existing block of the WP theme with the specified ID', 'yamaps'),
							'Extra' => __('Extra', 'yamaps'),
							'ExtraHTML' => __('<div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Want other icon types?</h2>Additional types of icons can be found by the link in the <a href="https://tech.yandex.com/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/ " style="white-space: normal">Yandex.Map documentation</a>.</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Do you like YaMaps plugin?</h2>You can support its development by donate (<a href="https://money.yandex.ru/to/41001278340150" style="white-space: normal">Yandex</a>, <a href="https://www.paypal.me/yhunter" style="white-space: normal">PayPal</a>) or just leave a positive feedback in the <a href="https://wordpress.org/support/plugin/yamaps/reviews/" style="white-space: normal">plugin repository</a>. It\'s very motivating!</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Any questions?</h2>Ask in the comments <a href="https://www.yhunter.ru/portfolio/dev/yamaps/" style="white-space: normal">on the plug-in\'s page</a>, <a href="https://wordpress.org/support/plugin/yamaps" style="white-space: normal">WP support forum</a> or <a href="https://github.com/yhunter-ru/yamaps/issues" style="white-space: normal">on GitHub</a>.</div>', 'yamaps')

						);



	
	wp_localize_script('yamap_plugin', 'yamap_object', $lang_array); 

	global $yamaps_defaults;
	wp_localize_script('yamap_plugin', 'yamap_defaults', $yamaps_defaults); 

	//Подключаем шаблон правки шорткода
	include_once dirname(__FILE__).'/templates/tmpl-editor-yamap.html';

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

add_action('admin_head', 'yamaps_custom_fonts');

function yamaps_custom_fonts() {
	//Исправляем проблему со съехавшим шрифтом в Stretchy метке на карте в редакторе		
	  echo '<style>
	    .mce-container ymaps {
	    	
	    	font-family: "Source Sans Pro",HelveticaNeue-Light,"Helvetica Neue Light","Helvetica Neue",Helvetica,Arial,"Lucida Grande",sans-serif !important;
	    	font-size: 11px !important;
	    }
	  </style>';
}

//Подключаем внешние стили

function yamap_mce_css( $mce_css ) {
  if ( !empty( $mce_css ) )
    $mce_css .= ',';
    $mce_css .= plugins_url( 'style.content.css', __FILE__ );
    return $mce_css;
  }
add_filter( 'mce_css', 'yamap_mce_css' );

//Поддключаем стили для нового редактора Gutenberg
function yamaps_gutenberg_styles() {
	// Load the theme styles within Gutenberg.
	 wp_enqueue_style( 'yamaps-gutenberg', plugins_url( 'style.content.css', __FILE__ ));
}
add_action( 'enqueue_block_editor_assets', 'yamaps_gutenberg_styles' );


include( plugin_dir_path( __FILE__ ) . 'options.php'); 



