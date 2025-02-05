<?php
global $lang_array;
// Function for scripts and localization array
function yamap_plugin_scripts($plugin_array) {
    // Plugin localization
    wp_register_script('yamap_plugin', plugin_dir_url(__FILE__) . '../js/shortcode_parser.js?v=0.2');
    wp_enqueue_script('yamap_plugin');
    
    $lang_array = array(
        'YaMap' => __('Map', 'yamaps'),
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
        'DeveloperInfoTab' => __('Design & Development', 'yamaps'),
        'ExtraHTML' => __( '<div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Want other icon types?</h2>Additional types of icons can be found by the link in the <a href="https://tech.yandex.com/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/ " style="white-space: normal">Yandex.Map documentation</a>.</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Do you like YaMaps plugin?</h2>You can support its development by donate (<a href="https://yoomoney.ru/to/41001278340150" style="white-space: normal">Yoomoney</a>) or just leave a positive feedback in the <a href="https://wordpress.org/support/plugin/yamaps/reviews/" style="white-space: normal">plugin repository</a>. It\'s very motivating!</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Any questions?</h2>Ask in the comments <a href="https://www.yhunter.ru/portfolio/dev/yamaps/" style="white-space: normal">on the plug-in\'s page</a>, <a href="https://wordpress.org/support/plugin/yamaps" style="white-space: normal">WP support forum</a> or <a href="https://github.com/yhunter-ru/yamaps/issues" style="white-space: normal">on GitHub</a>.</div>', 'yamaps' )				


    );

    wp_localize_script('yamap_plugin', 'yamap_object', $lang_array); 

    global $yamaps_defaults_front;
    wp_localize_script('yamap_plugin', 'yamap_defaults', $yamaps_defaults_front); 

    //enqueue TinyMCE plugin script with its ID.
    $plugin_array["yamap_plugin"] =  plugin_dir_url(__FILE__) . "../js/btn.js?v=0.40";

    return $plugin_array;
}

// Function for registering buttons in editor
function register_buttons_editor($buttons) {
    array_push($buttons, "yamap");
    return $buttons;
}

// Fix font problem in Stretchy marker on map in editor
function yamaps_custom_fonts() {            
    echo '<style>
        .mce-container ymaps {            
            font-family: "Source Sans Pro",HelveticaNeue-Light,"Helvetica Neue Light","Helvetica Neue",Helvetica,Arial,"Lucida Grande",sans-serif !important;
            font-size: 11px !important;
        }
    </style>';
}

// Include shortcode template
function yamaps_shortcode_tmpl() {
    include_once dirname(__FILE__).'/../templates/tmpl-editor-yamap.html';
}

// Add hooks
add_filter("mce_external_plugins", "yamap_plugin_scripts", 999);
add_filter("mce_buttons", "register_buttons_editor", 999);
add_action('admin_head', 'yamaps_custom_fonts', 999);
add_action('admin_head', 'yamaps_shortcode_tmpl'); 