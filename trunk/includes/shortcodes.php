<?php

/**
 * Sanitize coordinates string (e.g., "55.7558, 37.6173")
 * Only allows numbers, dots, commas, spaces, and minus signs
 *
 * @param string $coord Coordinate string
 * @return string Sanitized coordinate string
 */
function yamaps_sanitize_coords( string $coord ): string {
    // Remove all characters except numbers, dots, commas, spaces, and minus
    $sanitized = preg_replace( '/[^0-9.,\s\-]/', '', $coord );
    
    // Additional validation: should match pattern like "55.7558, 37.6173"
    if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', trim( $sanitized ) ) ) {
        return trim( $sanitized );
    }
    
    // Return default Moscow coordinates if invalid
    return '55.7558, 37.6173';
}

/**
 * Sanitize zoom level (integer 0-21)
 *
 * @param mixed $zoom Zoom value
 * @return int Sanitized zoom level
 */
function yamaps_sanitize_zoom( $zoom ): int {
    $zoom_int = intval( $zoom );
    
    // Yandex Maps zoom range is 0-21
    if ( $zoom_int < 0 ) {
        return 0;
    }
    if ( $zoom_int > 21 ) {
        return 21;
    }
    
    return $zoom_int;
}

/**
 * Sanitize map type and return full Yandex type string
 *
 * @param string $type Map type
 * @return string Sanitized map type (always with yandex# prefix)
 */
function yamaps_sanitize_map_type( string $type ): string {
    // Map short names to full Yandex type names
    $type_map = array(
        'map'              => 'yandex#map',
        'satellite'        => 'yandex#satellite',
        'hybrid'           => 'yandex#hybrid',
        'yandex#map'       => 'yandex#map',
        'yandex#satellite' => 'yandex#satellite',
        'yandex#hybrid'    => 'yandex#hybrid',
    );
    
    $type = sanitize_text_field( $type );
    
    return isset( $type_map[ $type ] ) ? $type_map[ $type ] : 'yandex#map';
}

/**
 * Sanitize controls string
 *
 * @param string $controls Controls string (semicolon-separated)
 * @return string Sanitized controls for JS array
 */
function yamaps_sanitize_controls( string $controls ): string {
    $allowed_controls = array(
        'fullscreenControl',
        'geolocationControl',
        'routeEditor',
        'rulerControl',
        'searchControl',
        'trafficControl',
        'typeSelector',
        'zoomControl',
        'routeButtonControl',
        'routePanelControl',
        'smallMapDefaultSet',
        'mediumMapDefaultSet',
        'largeMapDefaultSet',
        'default',
    );
    
    $controls_array = array_map( 'trim', explode( ';', $controls ) );
    $sanitized = array();
    
    foreach ( $controls_array as $control ) {
        $control = sanitize_text_field( $control );
        if ( in_array( $control, $allowed_controls, true ) ) {
            $sanitized[] = '"' . esc_js( $control ) . '"';
        }
    }
    
    return implode( ', ', $sanitized );
}

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
    
    // Sanitize coordinates to prevent XSS
    $safe_coord = yamaps_sanitize_coords( $atts["coord"] );
    
    $yaplacemark='
        YaMapsWP.myMap'.$maps_count.'.places.placemark'.$yaplacemark_count.' = {icon: "'.esc_js($atts["icon"]).'", name: "'.esc_js($atts["name"]).'", color: "'.esc_js($atts["color"]).'", coord: "'.esc_js($safe_coord).'", url: "'.esc_url($atts["url"]).'",};
        myMap'.$maps_count.'placemark'.$yaplacemark_count.' = new ymaps.Placemark(['.$safe_coord.'], {
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
    
    // Clustering is disabled by default
    // Works only when cluster="1" is explicitly specified in the shortcode
    // The option in options.php only affects the checkbox in the editor when creating a map
    $cluster_grid = isset($yamaps_defaults_front['cluster_grid_option']) ? $yamaps_defaults_front['cluster_grid_option'] : '64';
    
    $atts = shortcode_atts( array(
        'center' => esc_js($yamaps_defaults_front['center_map_option']),
        'zoom' => esc_js($yamaps_defaults_front['zoom_map_option']),
        'type' => 'map',
        'height' => esc_js($yamaps_defaults_front['height_map_option']),
        'controls' => esc_js($yamaps_defaults_front['controls_map_option']),
        'scrollzoom' => '1',
        'mobiledrag' => '1',
        'container' => '',
        'cluster' => '0',           // Disabled by default, works only with cluster="1"
        'clustergrid' => $cluster_grid,
    ), $atts );

    $yaplacemark_count = 0;
    $yacontrol_count = 0;
    $yamap_onpage = true;

    // Sanitize content: only allow [yaplacemark ...] shortcodes, remove everything else
    $safe_content = '';
    if ( ! empty( $content ) ) {
        // Match only [yaplacemark ...] shortcodes (self-closing or with closing tag)
        if ( preg_match_all( '/\[yaplacemark\s+[^\]]*\](?:\[\/yaplacemark\])?/i', $content, $matches ) ) {
            $safe_content = implode( '', $matches[0] );
        }
    }

    // Sanitize all map parameters to prevent XSS
    $safe_center = yamaps_sanitize_coords( $atts["center"] );
    $safe_zoom = yamaps_sanitize_zoom( $atts["zoom"] );
    $safe_type = yamaps_sanitize_map_type( $atts["type"] );
    $safe_controls = yamaps_sanitize_controls( $atts["controls"] );

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
    
    $placemarkscode = $safe_content;

    // Sanitize container ID - only allow alphanumeric, hyphens, underscores
    $atts["container"] = trim( $atts["container"] );
    if ( $atts["container"] !== "" ) {
        $mapcontainter = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $atts["container"] );
        // Ensure ID doesn't start with a number (invalid HTML ID)
        if ( preg_match( '/^[0-9]/', $mapcontainter ) ) {
            $mapcontainter = 'yamap-' . $mapcontainter;
        }
    } else {
        $mapcontainter = 'yamap' . $current_map_index;
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
                    YaMapsWP.myMap'.$current_map_index.' = {center: "'.esc_js($safe_center).'", zoom: '.$safe_zoom.', type: "'.esc_js($safe_type).'", controls: "'.esc_js($atts["controls"]).'", places: {}};

                    var yamapsonclick = function (url) {
                            location.href=url;
                    }                        

                    function init () {
                        myMap'.$current_map_index.' = new ymaps.Map("'.$mapcontainter.'", {
                                center: ['.$safe_center.'],
                                zoom: '.$safe_zoom.',
                                type: "'.$safe_type.'",
                                controls: ['.$safe_controls.'] ,
                                
                            },
                            {
                                suppressMapOpenBlock: '.esc_js($suppressMapOpenBlock).'
                            }); 

                            '.do_shortcode($placemarkscode);                            
                            
                            for ($i = 1; $i <= $yaplacemark_count; $i++) {
                                $placearr.='.add(myMap'.$current_map_index.'placemark'.$i.')';
                            }
                            
                            // If clustering is enabled
                            if ($atts["cluster"]=="1") {
                                $yamap.='
                                var yaClusterer'.$current_map_index.' = new ymaps.Clusterer({
                                    clusterIconLayout: "default#pieChart",
                                    clusterIconPieChartRadius: 25,
                                    clusterIconPieChartCoreRadius: 15,
                                    clusterIconPieChartStrokeWidth: 3,
                                    hasBalloon: false,
                                    gridSize: '.intval($atts["clustergrid"]).'
                                });
                                yaClusterer'.$current_map_index.'.add([';
                                for ($i = 1; $i <= $yaplacemark_count; $i++) {
                                    $yamap.='myMap'.$current_map_index.'placemark'.$i;
                                    if ($i < $yaplacemark_count) $yamap.=',';
                                }
                                $yamap.=']);
                                myMap'.$current_map_index.'.geoObjects.add(yaClusterer'.$current_map_index.');
                                ';
                            } else {
                                $yamap.='myMap'.$current_map_index.'.geoObjects'.$placearr.';';
                            }
                            
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