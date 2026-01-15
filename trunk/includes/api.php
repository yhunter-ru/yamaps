<?php
/**
 * Sanitize API key - only allow alphanumeric, hyphens, underscores
 *
 * @param string $key API key
 * @return string Sanitized API key
 */
function yamaps_sanitize_apikey( string $key ): string {
    $key = sanitize_text_field( $key );
    // Yandex API keys are typically UUID-like strings
    if ( preg_match( '/^[a-zA-Z0-9\-_]*$/', $key ) ) {
        return $key;
    }
    return '';
}

/**
 * Sanitize locale string
 *
 * @param string $locale Locale string
 * @return string Sanitized locale
 */
function yamaps_sanitize_locale( string $locale ): string {
    // Locale should be like "en_US", "ru_RU", etc.
    if ( preg_match( '/^[a-z]{2}_[A-Z]{2}$/', $locale ) ) {
        return $locale;
    }
    return 'en_US';
}

// New Yandex Map API call
function YandexMapAPI_script($noFooter = false) {  
    global $yamaps_defaults_front, $apikey, $post;
    
    $maplocale = yamaps_sanitize_locale( get_locale() );
    
    $safe_apikey = '';
    if ( ! empty( $yamaps_defaults_front['apikey_map_option'] ) ) {
        $safe_apikey = yamaps_sanitize_apikey( $yamaps_defaults_front['apikey_map_option'] );
    }
    
    $apikey_param = $safe_apikey !== '' ? '&apikey=' . rawurlencode( $safe_apikey ) : '';
    
    if ($noFooter) {
        $AltApiSrc = 'https://api-maps.yandex.ru/2.1/?lang=' . rawurlencode( $maplocale ) . $apikey_param . '&ver=2.1';
        return $AltApiSrc;
    }
    else {
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'yamap') ) {
            $api_url = 'https://api-maps.yandex.ru/2.1/?lang=' . rawurlencode( $maplocale ) . $apikey_param;
            wp_register_script( 'YandexMapAPI', $api_url, [], '2.1', true );  
            wp_enqueue_script( 'YandexMapAPI' ); 
        }    
    }
}

// Hook for API script
if (($yamap_load_api)) {  
    add_action( 'wp_enqueue_scripts', 'YandexMapAPI_script', 5 );
} 