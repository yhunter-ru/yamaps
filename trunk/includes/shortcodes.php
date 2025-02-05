<?php
// Placemark shortcode function
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
    //Check if icon field is URL. If yes, set custom image as icon
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

// Map shortcode function
function yamap_func($atts, $content) {
    global $yaplacemark_count, $yamaps_defaults_front, $yamaps_defaults_front_bak, $yacontrol_count, $yamap_load_api, $suppressMapOpenBlock, $yamap_onpage, $maps_count;

    $current_map_index = $maps_count;
    
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

    $yaplacemark_count = 0;
    $yacontrol_count = 0;
    $yamap_onpage = true;

    $yamactrl = str_replace(';', '", "', esc_js($atts["controls"]));
    if (trim($yamactrl) != "") $yamactrl = '"'.$yamactrl.'"';

    if (($yamap_load_api)) {
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
            var myMap'.$current_map_index.';            
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
        $mapcontainter='yamap'.$current_map_index;
    }    
    
    // Check if "Open big map" button is enabled
    if ($yamaps_defaults_front['open_map_option']<>'on') {
        $suppressMapOpenBlock='true'; 
    }
    else {
        $suppressMapOpenBlock='false';
    }

    // 1. Wait for the entire page to load before initializing the map
    // 2. Check if the API is connected in wp_footer by ID "YandexMapAPI-js" (wp_footer may not be present in custom templates)
    // 3. If not, run the alternative API connection function - AltApiLoad, connect the script with ID "YandexMapAPI-alt-js"
    // 4. Write the initialization function of each map on the page to the YaMapsScriptCounter array and initialize them sequentially after loading the script
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
                   
                    YMlisteners.myMap'.$current_map_index.' = {};
                    YaMapsWP.myMap'.$current_map_index.' = {center: "'.esc_js($atts["center"]).'", zoom: "'.esc_js($atts["zoom"]).'", type: "'.esc_js($atts["type"]).'", controls: "'.esc_js($atts["controls"]).'", places: {}};

                    var yamapsonclick = function (url) {
                            location.href=url;
                    }                        

                    function init () {
                        myMap'.$current_map_index.' = new ymaps.Map("'.$mapcontainter.'", {
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
                                $placearr.='.add(myMap'.$current_map_index.'placemark'.$i.')';
                            }
                            $yamap.='myMap'.$current_map_index.'.geoObjects'.$placearr.';';
                            if ($atts["scrollzoom"]=="0") $yamap.="myMap".$current_map_index.".behaviors.disable('scrollZoom');";
                            // If map has mobiledrag=0, disable map dragging for the following platforms
                            if ($atts["mobiledrag"]=="0") {
                                $yamap.="
                                if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
                                    myMap".$current_map_index.".behaviors.disable('drag');    
                                }";
                            }
                            $yamap.='
                    }
            }, false);
        </script>
    ';
    
    $authorLinkTitle=__( 'YaMaps plugin for Wordpress', 'yamaps' );
    if($yamaps_defaults_front['authorlink_map_option']<>'on'){
        $authorlink='<div style="position: relative; height: 0;  margin-bottom: 0rem !important; margin-top:0 !important; overflow: visible; width: 100%; text-align: center; top: -32px;"><a href="https://www.yhunter.ru/portfolio/dev/yamaps/" title="'.esc_attr($authorLinkTitle).'" target="_blank" style="display: inline-block; -webkit-box-align: center; padding: 3.5px 5px; text-decoration: none !important; border-bottom: 0; border-radius: 3px; background-color: #fff; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px 1px rgba(0,0,0,.15),0 2px 5px -3px rgba(0,0,0,.15);"><img src="'.plugins_url( '../js/img/placeholder.svg' , __FILE__ ).'" alt="" style="width: 17px; height: 17px; margin: 0; display: block;" /></a></div>';
    }
    else {
        $authorlink="";
    }
    
    if ($atts["container"]=="") {
        $yamap .= '<div id="'.esc_attr($mapcontainter).'"  style="position: relative; height: '.esc_attr($atts["height"]).'; margin-bottom: 0 !important;"></div>'.$authorlink;
    }
    $maps_count++;

    return $yamap;
}

// Register shortcodes
add_shortcode('yaplacemark', 'yaplacemark_func');
add_shortcode('yamap', 'yamap_func');