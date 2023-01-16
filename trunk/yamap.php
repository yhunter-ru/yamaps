<?php
/**
 * Plugin Name: YaMaps for Wordpress
 * Description: Yandex Map integration
 * Plugin URI:  www.yhunter.ru/portfolio/dev/yamaps/
 * Author URI:  www.yhunter.ru
 * Author:      Yuri Baranov
 * Version:     0.6.26
 *
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yamaps
 * Domain Path: /languages/
 *
 */
global $maps_count, $count_content, $yamap_load_api; 

if (!isset($maps_count)) {
	$maps_count=0;
}
if (!isset($count_content)) {
	$count_content=0;
}

// Test for the first time content and single map (WooCommerce and other custom posts)
//$count_content=0;
$yamap_load_api=true;
$apikey='';

$yamaps_defaults_front = array(
	'center_map_option'			=> '55.7473,37.6247',
	'zoom_map_option'			=> '12',
	'type_map_option'			=> 'yandex#map',
	'height_map_option'			=> '22rem',
	'controls_map_option'		=> '',
	'wheelzoom_map_option'		=> 'off',
	'mobiledrag_map_option'		=> 'off',
	'type_icon_option'			=> 'islands#dotIcon',
	'color_icon_option'			=> '#1e98ff',
	'authorlink_map_option'		=> 'off',
	'open_map_option'			=> 'off',
	'apikey_map_option'			=> '',
	'reset_maps_option'			=> 'off',
);	

$yamaps_defaults_front_bak=$yamaps_defaults_front;
$yamaps_defaults=$yamaps_defaults_front;

//Загрузка настроек

$option_name = 'yamaps_options';
if(get_option($option_name)){
    $yamaps_defaults_front=get_option($option_name);
    //исправляем ошибку с дефолтными настройками 0.5.7
    $fixpos = strripos($yamaps_defaults_front['controls_map_option'], '111');
    if (is_int($fixpos)) {
    	$fixpattern=array('111;','111');
    	$yamaps_defaults_front['controls_map_option']=str_replace($fixpattern, '', $yamaps_defaults_front['controls_map_option']);
    	echo esc_html($yamaps_defaults_front['controls_map_option']);
    	update_option($option_name, $yamaps_defaults_front); 
    }
    //конец правки. Будет удалено в следующих версиях.
}

//Проверяем все ли дефолтные параметры есть в настройках плагина
foreach($yamaps_defaults_front_bak as $yamaps_options_key => $yamaps_options_val) {	
	if(!isset($yamaps_defaults_front[$yamaps_options_key])) {
		$yamaps_defaults_front[$yamaps_options_key]=$yamaps_defaults_front_bak[$yamaps_options_key];
	}
}

//Добавляем счетчик полей с контентом (для постов с произвольными полями)
add_filter( 'the_content', 'yamaps_the_content'); 
add_filter('widget_text', 'yamaps_the_content');
add_filter('requirement', 'yamaps_the_content');

function yamaps_the_content( $content ) {
	global $count_content;
	$count_content++;
    return $content;
}

//Новый вызов Yandex Map API. Если передаем true, отдается только адрес API с локалью и API-ключем. Нужно для альтернативного подключения API, при отсутствии wp_footer
function YandexMapAPI_script($noFooter = false) {  
		global $yamaps_defaults_front, $apikey, $post;
		$maplocale = get_locale();
		if (strlen($maplocale)<5) $maplocale = "en_US";
		if (trim($yamaps_defaults_front['apikey_map_option'])<>"") {
			$apikey='&apikey='.esc_html($yamaps_defaults_front['apikey_map_option']);
		}
		else {
			$apikey = '';
		}
		if ($noFooter) {
			return 'https://api-maps.yandex.ru/2.1/?lang='.esc_html($maplocale).esc_html($apikey);
		}
		else {
			if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'yamap') ) {
				// Register the script like this for a plugin:  
				wp_register_script( 'YandexMapAPI', 'https://api-maps.yandex.ru/2.1/?lang='.esc_html($maplocale).esc_html($apikey), [], 2.1, true );  

				// For either a plugin or a theme, you can then enqueue the script:  
			    wp_enqueue_script( 'YandexMapAPI' ); 
			}    
		}
		 
}

//Функция добавления метки на карту
function yaplacemark_func($atts) {
	$atts = shortcode_atts( array(
		'coord' => '',
		'name' => '',
		'color' => 'blue',
		'url' => '',
		'icon' => 'islands#dotIcon',
	), $atts );

	global $yaplacemark_count, $maps_count;
	$yaplacemark_count++;
	$yahint="";
	$yacontent="";
	$yaicon=trim(esc_html($atts["icon"]));


	if (strstr($yaicon, "Stretchy")<>FALSE) {
		$yahint="";
		$yacontent=sanitize_text_field($atts["name"]);
		}
	else {
			if (($yaicon==="islands#blueIcon")or($yaicon==="islands#blueCircleIcon")) {
				$yahint=esc_html($atts["name"]);
				$yacontent=esc_html(mb_substr($yahint, 0, 1));
			}
			else {
				$yahint=esc_html($atts["name"]);
				$yacontent="";
			}
	}
	
	
	$yaplacemark='
		YaMapsWP.myMap'.$maps_count.'.places.placemark'.$yaplacemark_count.' = {icon: "'.esc_js($atts["icon"]).'", name: "'.esc_js($atts["name"]).'", color: "'.esc_js($atts["color"]).'", coord: "'.esc_js($atts["coord"]).'", url: "'.esc_url($atts["url"]).'",};
		myMap'.$maps_count.'placemark'.$yaplacemark_count.' = new ymaps.Placemark(['.$atts["coord"].'], {
                                hintContent: "'.esc_js($yahint).'",
                                iconContent: "'.esc_js($yacontent).'",


                              
                            }, {';
    //Проверяем, является ли поле иконки url-адресом. Если да, то ставим в качестве иконки кастомное изображение.
    $iconurl = strripos($atts["icon"], 'http');
    if (is_int($iconurl)) {
    	$yaplacemark.='                        
                            	iconLayout: "default#image",
        						iconImageHref: "'.esc_js($atts["icon"]).'"
                            });  
		';

    }
    else {
    	$yaplacemark.='                        
                            	preset: "'.esc_js($atts["icon"]).'", 
                            	iconColor: "'.esc_js($atts["color"]).'",
                            });  
		';
    }
    
	$atts["url"]=trim(esc_js($atts["url"]));
	if (($atts["url"]<>"")and($atts["url"]<>"0")) {
		$marklink=$atts["url"];
		settype($marklink, "integer");
		if ($marklink<>0) {
			$marklink=get_the_permalink(esc_js($atts["url"]));
			$yaplacemark.='YaMapsWP.myMap'.$maps_count.'.places["placemark'.$yaplacemark_count.'"].url="'.$marklink.'"';
		}
		else {
			$marklink=$atts["url"];
		}
		$yaplacemark.=' 
				YMlisteners.myMap'.$maps_count.'['.$yaplacemark_count.'] = myMap'.$maps_count.'placemark'.$yaplacemark_count.'.events.group().add("click", function(e) {yamapsonclick("'.esc_url($marklink).'")});

		';
	}
	return $yaplacemark;
}

//Функция вывода карты
function yamap_func($atts, $content){
	global $yaplacemark_count, $yamaps_defaults_front, $yamaps_defaults_front_bak, $yacontrol_count, $maps_count, $count_content, $yamap_load_api, $suppressMapOpenBlock, $yamap_onpage;

	$placearr = '';
	$atts = shortcode_atts( array(
		'center' => esc_js($yamaps_defaults_front['center_map_option']),
		'zoom' => esc_js($yamaps_defaults_front['zoom_map_option']),
		'type' => 'map',
		'height' => esc_js($yamaps_defaults_front['height_map_option']),
		'controls' => esc_js($yamaps_defaults_front['controls_map_option']),
		'scrollzoom' => '1',
		'mobiledrag' => '1',
		'container' => '',

	), $atts );
	
	$yaplacemark_count=0;
	$yacontrol_count=0;
	$yamap_onpage=true;

	$yamactrl=str_replace(';', '", "', esc_js($atts["controls"]));

	if (trim($yamactrl)<>"") $yamactrl='"'.$yamactrl.'"';

	if (($yamap_load_api)) { // First time content and single map
		if (trim($yamaps_defaults_front['apikey_map_option'])<>"") {
			$apikey='&apikey='.esc_js($yamaps_defaults_front['apikey_map_option']);
		}
		else {
			$apikey = '';
		}

		$yamap='
		<script>
			if (typeof(YaMapsWP) === "undefined") {
				var YaMapsWP = {}, YMlisteners = {};
				var YaMapsScript = document.createElement("script");	
				var YaMapsScriptCounter = [];					
			}
			var myMap'.$maps_count.';			
		</script>';
	}
	else {
		$yamap='';
	}
	
	$placemarkscode=str_replace("&nbsp;", "", strip_tags($content));

	$atts["container"]=trim($atts["container"]);
	if ($atts["container"]<>"") {
		$mapcontainter=esc_html($atts["container"]);
		$mapcontainter=str_replace("#", "", $mapcontainter);
	}
	else {
		$mapcontainter='yamap'.$maps_count;
	}	
	
	// Проверяем опцию включения кнопки большой карты
	if ($yamaps_defaults_front['open_map_option']<>'on') {
		$suppressMapOpenBlock='true'; 
	}
	else {
		$suppressMapOpenBlock='false';
	}
	//1. Дожидаемся загрузки всей страницы для инициализации карты
	//2. Проверяем, подключился ли API в wp_footer по ID "YandexMapAPI-js" (wp_footer может не оказаться в кастомных шаблонах)
	//3. Если нет, запускаем функцию альтернативного подключения API - AltApiLoad, подключаем скрипт с ID "YandexMapAPI-alt-js"
	//4. Функцию инициализации каждой карты на странице записываем в массив YaMapsScriptCounter и, после загрузки скрипта, поочередно инициализируем
    $yamap.='
						<script type="text/javascript">										
						document.addEventListener("DOMContentLoaded", function() { 
						   if (document.getElementById("YandexMapAPI-js") == null ) {
					   			YaMapsScriptCounter.push(function() {ymaps.ready(init)});
						   		if (document.getElementById("YandexMapAPI-alt-js") == null ) { 
						   			function AltApiLoad(src){

									  YaMapsScript.id = "YandexMapAPI-alt-js";
									  YaMapsScript.src = src;
									  YaMapsScript.async = false;
									  document.head.appendChild(YaMapsScript);

									}

									AltApiLoad("'.YandexMapAPI_script(true).'");

									window.onload = function() {
										YaMapsScriptCounter.forEach(function(entryFunc) {
										    entryFunc();
										});
									}
						   		}

						   		

						   } 
						   else {
						   		ymaps.ready(init); 
						   }
						   
	                 		
							YMlisteners.myMap'.$maps_count.' = {};
							YaMapsWP.myMap'.$maps_count.' = {center: "'.esc_js($atts["center"]).'", zoom: "'.esc_js($atts["zoom"]).'", type: "'.esc_js($atts["type"]).'", controls: "'.esc_js($atts["controls"]).'", places: {}};

	                 		var yamapsonclick = function (url) {
								location.href=url;
	                 		}                        

	                        function init () {
	                            myMap'.$maps_count.' = new ymaps.Map("'.$mapcontainter.'", {
	                                    center: ['.sanitize_text_field($atts["center"]).'],
	                                    zoom: '.sanitize_text_field($atts["zoom"]).',
	                                    type: "'.sanitize_text_field($atts["type"]).'",
	                                    controls: ['.sanitize_text_field($yamactrl).'] ,
	                                    
	                                },
	                                {
	                                	suppressMapOpenBlock: '.esc_js($suppressMapOpenBlock).'
	                                }); 

								'.do_shortcode($placemarkscode);							
								
								for ($i = 1; $i <= $yaplacemark_count; $i++) {
									$placearr.='.add(myMap'.$maps_count.'placemark'.$i.')';
								}
	                            $yamap.='myMap'.$maps_count.'.geoObjects'.$placearr.';';
	                            if ($atts["scrollzoom"]=="0") $yamap.="myMap".$maps_count.".behaviors.disable('scrollZoom');";
	                            //Если у карты mobiledrag=0, отключаем прокрутку карты для следующих платформ
	                            if ($atts["mobiledrag"]=="0") {
	                            	$yamap.="
	                            	if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
	                            		myMap".$maps_count.".behaviors.disable('drag');	
									}";
	                            }
	                            $yamap.='

	                        }
                        }, false);
                    </script>
                    
    ';
    $authorLinkTitle=__( 'YaMaps plugin for Wordpress', 'yamaps' );

    if($yamaps_defaults_front['authorlink_map_option']<>'on'){
    	
    $authorlink='<div style="position: relative; height: 0;  margin-bottom: 1rem !important; overflow: visible; width: 100%; text-align: center; top: -32px;"><a href="https://www.yhunter.ru/portfolio/dev/yamaps/" title="'.esc_attr($authorLinkTitle).'" target="_blank" style="display: inline-block; -webkit-box-align: center; padding: 3.5px 5px; text-decoration: none !important; border-bottom: 0; border-radius: 3px; background-color: #fff; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px 1px rgba(0,0,0,.15),0 2px 5px -3px rgba(0,0,0,.15);"><img src="'.plugins_url( 'js/img/placeholder.svg' , __FILE__ ).'" alt="" style="width: 17px; height: 17px; margin: 0; display: block;" /></a></div>';
    }
    else {
    	$authorlink="";
    }
    if ($atts["container"]=="") $yamap.='<div id="'.esc_attr($mapcontainter).'"  style="position: relative; height: '.esc_attr($atts["height"]).'; margin-bottom: 0 !important;"></div>'.$authorlink;

    if ($count_content>=1) $maps_count++;
    return $yamap; 
}

add_shortcode( 'yaplacemark', 'yaplacemark_func' );  
add_shortcode( 'yamap', 'yamap_func' ); 
add_shortcode( 'yacontrol', 'yacontrol_func' ); 

//Функция подключения текстового домена для локализации
function yamaps_plugin_load_plugin_textdomain() {
    load_plugin_textdomain( 'yamaps', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'yamaps_plugin_load_plugin_textdomain' );


// Функция подключения скриптов и массив для локализации
function yamap_plugin_scripts($plugin_array)
{
    
    // Plugin localization

	wp_register_script('yamap_plugin', plugin_dir_url(__FILE__) . 'js/shortcode_parser.js?v=0.2');
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
						 'MobileDrag' => __('Mobile drag', 'yamaps'),
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
						 'ExtraHTML' => __('<div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Want other icon types?</h2>Additional types of icons can be found by the link in the <a href="https://tech.yandex.com/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/ " style="white-space: normal">Yandex.Map documentation</a>.</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Do you like YaMaps plugin?</h2>You can support its development by donate (<a href="https://yoomoney.ru/to/41001278340150" style="white-space: normal">Yoomoney</a>) or just leave a positive feedback in the <a href="https://wordpress.org/support/plugin/yamaps/reviews/" style="white-space: normal">plugin repository</a>. It\'s very motivating!</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Any questions?</h2>Ask in the comments <a href="https://www.yhunter.ru/portfolio/dev/yamaps/" style="white-space: normal">on the plug-in\'s page</a>, <a href="https://wordpress.org/support/plugin/yamaps" style="white-space: normal">WP support forum</a> or <a href="https://github.com/yhunter-ru/yamaps/issues" style="white-space: normal">on GitHub</a>.</div>', 'yamaps'),
							'DeveloperInfoTab' => __('Design & Development', 'yamaps'),
							'DeveloperInfo' => __('<div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Want other plugin features?</h2>Do you like the plugin but lack features for your project? For commercial modifications of the plugin, please contact me.</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">WordPress website design and development</h2>My name is Yuri and I have been creating websites for over 15 years. I have been familiar with WordPress since 2008. I know and love this CMS for its user friendly interface. This is exactly how I tried to make the interface of my YaMaps plugin, which you are currently using. If you need to create a website, make an interface design or write a plugin for WordPress - I will be happy to help you!<p style="margin-top: .5rem; text-align: center;"><b>Contacts:</b>  <a href="mailto:mail@yhunter.ru">mail@yhunter.ru</a>, <b>telegram:</b> <a href="tg://resolve?domain=yhunter">@yhunter</a>, <b>tel:</b> <a href="tel:+79028358830">+7-902-83-588-30</a></p></div>', 'yamaps'),

						);



	
	wp_localize_script('yamap_plugin', 'yamap_object', $lang_array); 

	global $yamaps_defaults_front;
	wp_localize_script('yamap_plugin', 'yamap_defaults', $yamaps_defaults_front); 

	//enqueue TinyMCE plugin script with its ID.

	$plugin_array["yamap_plugin"] =  plugin_dir_url(__FILE__) . "js/btn.js?v=0.34";

    return $plugin_array;

}




add_filter("mce_external_plugins", "yamap_plugin_scripts", 999 );

//Функция регистрации кнопок в редакторе
function register_buttons_editor($buttons)
{
    //register buttons with their id.
    array_push($buttons, "yamap");
    return $buttons;
}


add_filter("mce_buttons", "register_buttons_editor", 999 );

add_action('admin_head', 'yamaps_custom_fonts', 999 );

//Исправляем проблему со съехавшим шрифтом в Stretchy метке на карте в редакторе
function yamaps_custom_fonts() {			
	  echo '<style>
	    .mce-container ymaps {	    	
	    	font-family: "Source Sans Pro",HelveticaNeue-Light,"Helvetica Neue Light","Helvetica Neue",Helvetica,Arial,"Lucida Grande",sans-serif !important;
	    	font-size: 11px !important;
	    }
	  </style>';
}


//Подключаем шаблон шорткода
function yamaps_shortcode_tmpl() {
	//Подключаем шаблон правки шорткода
	include_once dirname(__FILE__).'/templates/tmpl-editor-yamap.html';
}

add_action('admin_head', 'yamaps_shortcode_tmpl');

//Подключаем внешние стили
function yamap_mce_css( $mce_css ) {
  if ( !empty( $mce_css ) )
    $mce_css .= ',';
    $mce_css .= plugins_url( 'style.content.css', __FILE__ );
    return $mce_css;
  }
add_filter( 'mce_css', 'yamap_mce_css' );

//Подключаем стили для нового редактора Gutenberg
function yamaps_gutenberg_styles() {
	// Load the theme styles within Gutenberg.
	 wp_enqueue_style( 'yamaps-gutenberg', plugins_url( 'style.content.css', __FILE__ ));
}
add_action( 'enqueue_block_editor_assets', 'yamaps_gutenberg_styles' );


if (($yamap_load_api)) {  
	add_action( 'wp_enqueue_scripts', 'YandexMapAPI_script', 5 );
} 

include( plugin_dir_path( __FILE__ ) . 'options.php'); 
