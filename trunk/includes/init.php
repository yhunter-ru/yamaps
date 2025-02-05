<?php
// Global variables
global $maps_count, $yamap_load_api;

if (!isset($maps_count)) {
    $maps_count = 0;
}

// Test for the first time content and single map (WooCommerce and other custom posts)
$yamap_load_api = true;
$apikey = '';

// Get API key from options
$yamaps_options = get_option('yamaps_options');
if (!empty($yamaps_options['apikey_map_option'])) {
    $apikey = '&apikey=' . esc_attr($yamaps_options['apikey_map_option']);
} else {
    $apikey = '';
}

// Default settings
$yamaps_defaults_front = array(
    'center_map_option'          => '55.7473,37.6247',
    'zoom_map_option'            => '12',
    'type_map_option'            => 'yandex#map',
    'height_map_option'          => '22rem',
    'controls_map_option'        => '',
    'wheelzoom_map_option'       => 'off',
    'mobiledrag_map_option'      => 'off',
    'type_icon_option'           => 'islands#dotIcon',
    'color_icon_option'          => '#1e98ff',
    'authorlink_map_option'      => 'off',
    'open_map_option'            => 'off',
    'apikey_map_option'          => '',
    'reset_maps_option'          => 'off',
);

$yamaps_defaults_front_bak = $yamaps_defaults_front;
$yamaps_defaults = $yamaps_defaults_front;

// Load settings
$option_name = 'yamaps_options';
if(get_option($option_name)){
    $yamaps_defaults_front = get_option($option_name);
    //Fix default settings error 0.5.7
    $fixpos = strripos($yamaps_defaults_front['controls_map_option'], '111');
    if (is_int($fixpos)) {
        $fixpattern = array('111;','111');
        $yamaps_defaults_front['controls_map_option'] = str_replace($fixpattern, '', $yamaps_defaults_front['controls_map_option']);
        echo esc_html($yamaps_defaults_front['controls_map_option']);
        update_option($option_name, $yamaps_defaults_front); 
    }
}

// Check if all default parameters exist in plugin settings
foreach($yamaps_defaults_front_bak as $yamaps_options_key => $yamaps_options_val) {    
    if(!isset($yamaps_defaults_front[$yamaps_options_key])) {
        $yamaps_defaults_front[$yamaps_options_key] = $yamaps_defaults_front_bak[$yamaps_options_key];
    }
} 