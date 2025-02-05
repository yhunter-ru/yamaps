<?php
// New Yandex Map API call
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
        $AltApiSrc = 'https://api-maps.yandex.ru/2.1/?lang='.esc_html($maplocale).esc_html($apikey).'&ver=2.1';
        $AltApiSrc = str_replace("&amp;", "&", $AltApiSrc);
        return $AltApiSrc;
    }
    else {
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'yamap') ) {
            wp_register_script( 'YandexMapAPI', 'https://api-maps.yandex.ru/2.1/?lang='.esc_html($maplocale).esc_html($apikey), [], 2.1, true );  
            wp_enqueue_script( 'YandexMapAPI' ); 
        }    
    }
}

// Hook for API script
if (($yamap_load_api)) {  
    add_action( 'wp_enqueue_scripts', 'YandexMapAPI_script', 5 );
} 