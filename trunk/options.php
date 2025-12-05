<?php
$yamaps_page = 'yamaps-options.php'; 

global $yamaps_page, $yamaps_defaults, $yamaps_defaults_front_bak, $lang_array;
$option_name = 'yamaps_options';
if(get_option($option_name)){
	$yamaps_defaults=get_option($option_name);
	if (isset($yamaps_defaults['reset_maps_option'])) {
		if ($yamaps_defaults['reset_maps_option']==="on") {
			update_option( $option_name, $yamaps_defaults_front_bak);
			$yamaps_defaults=$yamaps_defaults_front_bak;
		}
	}
}

/*
 * Function to add a page to the Settings menu
 */
function yamaps_options() {
	global $yamaps_page;
	add_options_page( 'YaMaps', 'YaMaps', 'manage_options', $yamaps_page, 'yamaps_option_page');  
}
add_action('admin_menu', 'yamaps_options');
 
/**
 * Callback function
 */ 
function yamaps_option_page(){
	// Check user permissions
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient permissions to access this page.', 'yamaps'));
	}

	global $yamaps_page, $yamaps_defaults;
	$maplocale = get_locale();
	if (strlen($maplocale)<5) $maplocale = "en_US";
	if (trim($yamaps_defaults['apikey_map_option'])<>"") {
			$apikey='&apikey='.esc_attr($yamaps_defaults['apikey_map_option']);
	}
	else {
		$apikey="";
	}
	?><div class="wrap">
		<h2><?php echo esc_html__('YaMaps default options', 'yamaps'); ?></h2>
		<form method="post" id="YaMapsOptions" enctype="multipart/form-data" action="options.php">
		<?php 
		wp_nonce_field('yamaps_options_verify', 'yamaps_options_nonce');
		echo '<script src="https://api-maps.yandex.ru/2.1/?lang='.esc_attr($maplocale).esc_attr($apikey).'" type="text/javascript"></script>'; ?>
			<script type="text/javascript">
						// Round coordinates to 4 decimal places
						function coordaprox(fullcoord) {
						        if (fullcoord.length!==2) {
						            fullcoord=fullcoord.split(',');
						            if (fullcoord.length!==2) { 
						                fullcoord=[0,0];
						            }
						        }
						        return [parseFloat(fullcoord[0]).toFixed(4), parseFloat(fullcoord[1]).toFixed(4)];
						}

                        ymaps.ready(init); 


                 		// Initialize the map for the settings page
                        function init () {
                        	var testvar=document.getElementById('center_map_option').value;
                        	var apikeyexist = false, apikey=<?php echo '"'.$apikey.'"' ?>;
                        	if (apikey!=="") apikeyexist=true;
                        	var controlsArr=["zoomControl", "typeSelector"];
                            if (apikeyexist) controlsArr.push("searchControl"); // If the API key is defined, add search to the map. Without a key, it won't work anyway and will throw an error.

                            var myMap0 = new ymaps.Map("yamap", {
                                    center: [<?php echo $yamaps_defaults["center_map_option"]; ?>],
                                    zoom: <?php echo $yamaps_defaults["zoom_map_option"]; ?>,
                                    type: '<?php echo $yamaps_defaults["type_map_option"]; ?>',
                                    controls: controlsArr 
                                });   

							// Add a sample placemark
							placemark1 = new ymaps.Placemark([<?php echo $yamaps_defaults["center_map_option"]; ?>], {
                                hintContent: "Placemark",
                                iconContent: "",

                              
                            }, {
                            	<?php 
                            		// Check if the icon field is a URL. If yes, set a custom image as the icon.
								    $iconurl = strripos($yamaps_defaults["type_icon_option"], 'http');
								    if (is_int($iconurl)) {
								    	echo '                        
								                            	iconLayout: "default#image",
								        						iconImageHref: "'.$yamaps_defaults["type_icon_option"].'"
								                              
										';

								    }
								    else {
								    	echo '                        
								                            	preset: "'.$yamaps_defaults["type_icon_option"].'", 
								                            	iconColor: "'.$yamaps_defaults["color_icon_option"].'",
								                             
										';
								    }

                            	?>

                            });  
							myMap0.geoObjects.add(placemark1);

							// Map movement event
							myMap0.events.add('boundschange', function (event) {
										// If the zoom level changed
				                        if (event.get('newZoom') != event.get('oldZoom')) {     
				                            document.getElementById('zoom_map_option').value = event.get('newZoom');				                            
				                        }
				                        // If the center was moved
				                          if (event.get('newCenter') != event.get('oldCenter')) {
				                            document.getElementById('center_map_option').value = coordaprox(event.get('newCenter'));   
				                        }
				                        // Place the marker in the new center
				                        placemark1.geometry.setCoordinates(event.get('newCenter'));			                        
			                });
			                // Icon change event
			                myMap0.events.add('typechange', function (event) {
			                            document.getElementById('type_map_option').value = myMap0.getType();
		                    });
		                    // Search event, hide the result marker
		                    if (apikeyexist) { // Bug, if there is no API key, then there is no search field and it throws an error
	                        	var searchControl = myMap0.controls.get('searchControl');                        
	                        	searchControl.events.add('resultshow', function (e) {
	                            	searchControl.hideResult();
	                        	});
                        	}
                        	// Function to add map control elements in the settings field
                        	var controlElems = document.querySelectorAll('#addcontrol a');
                        	for (var i = 0; i < controlElems.length; i++) {
                        		controlElems[i].style.cursor = "pointer";
                        		controlElems[i].addEventListener('click', function() {                        		
		                        if (document.getElementById('controls_map_option').value.trim()!="") {
		                               document.getElementById('controls_map_option').value = document.getElementById('controls_map_option').value + ';'; 
		                        }
		                        document.getElementById('controls_map_option').value = document.getElementById('controls_map_option').value + this.getAttribute("data-control");
	                        });
								
							}

                        }
                        
            </script>
                    
    		<div id="yamap"  style="position: relative; min-height: 15rem; margin-bottom: 1rem;"></div><br />
    		<div class="thanks" style="border: 4px #ffdb4d solid; background: #fff ; padding: 0 1rem 2rem 1rem; display: flex;">
    			<div style="flex: 1">
    				<?php echo __( '<div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Want other icon types?</h2>Additional types of icons can be found by the link in the <a href="https://tech.yandex.com/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/ " style="white-space: normal">Yandex.Map documentation</a>.</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Do you like YaMaps plugin?</h2>You can support its development by donate (<a href="https://yoomoney.ru/to/41001278340150" style="white-space: normal">Yoomoney</a>) or just leave a positive feedback in the <a href="https://wordpress.org/support/plugin/yamaps/reviews/" style="white-space: normal">plugin repository</a>. It\'s very motivating!</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Any questions?</h2>Ask in the comments <a href="https://www.yhunter.ru/portfolio/dev/yamaps/" style="white-space: normal">on the plug-in\'s page</a>, <a href="https://wordpress.org/support/plugin/yamaps" style="white-space: normal">WP support forum</a> or <a href="https://github.com/yhunter-ru/yamaps/issues" style="white-space: normal">on GitHub</a>.</div>', 'yamaps' ); ?>
    			</div>
    			<!--
    			<div style="flex: 1">					
					<?php //echo __('<div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">Want other plugin features?</h2>Do you like the plugin but lack features for your project? For commercial modifications of the plugin, please contact me.</div><div style="position: relative; display: block; width: 100%; white-space: normal !important;"><h2 style="color: #444;font-size: 18px;font-weight: 600;line-height: 36px;">WordPress website design and development</h2>My name is Yuri and I have been creating websites for over 15 years. I have been familiar with WordPress since 2008. I know and love this CMS for its user friendly interface. This is exactly how I tried to make the interface of my YaMaps plugin, which you are currently using. If you need to create a website, make an interface design or write a plugin for WordPress - I will be happy to help you!<p style="margin-top: .5rem; text-align: center;"><b>Contacts:</b>  <a href="mailto:mail@yhunter.ru">mail@yhunter.ru</a>, <b>telegram:</b> <a href="tg://resolve?domain=yhunter">@yhunter</a>, <b>tel:</b> <a href="tel:+79028358830">+7-902-83-588-30</a></p></div>', 'yamaps') ?>
    			</div>
    			-->
    		</div>
			<?php 
			settings_fields('yamaps_options'); // Plugin settings identifier
			do_settings_sections($yamaps_page);
			?>
			<p class="submit">  
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>

		
	</div><?php
}
 
/*
 * Register settings
 * My settings will be stored in the database under the name yamaps_options (this is also visible in the previous function)
 */
function yamaps_option_settings() {
	global $yamaps_page;
	// Assign validation function ( yamaps_validate_settings() ). You will find it below
	register_setting( 'yamaps_options', 'yamaps_options', 'yamaps_validate_settings' ); // yamaps_options
 
	// Map settings area
	add_settings_section( 'map_section', __( 'Map options', 'yamaps' ), '', $yamaps_page );
 
	// Map center field
	$yamaps_field_params = array(
		'type'      => 'text', // type
		'id'        => 'center_map_option',
		'desc'      => __( 'Drag the map to set its default coordinates', 'yamaps' ), 
		'label_for' => 'center_map_option' 
	);
	add_settings_field( 'center_map_option', __( 'Map center', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Map zoom field
	$yamaps_field_params = array(
		'type'      => 'text', // type
		'id'        => 'zoom_map_option',
		'desc'      => __( 'Zoom the map to set its default scale', 'yamaps' ), 
		'label_for' => 'zoom_map_option' 
	);
	add_settings_field( 'zoom_map_option', __( 'Map zoom', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Map type field
	$yamaps_field_params = array(
		'type'      => 'text', // type
		'id'        => 'type_map_option',
		'desc'      => __( 'Choose default map type: yandex#map, yandex#satellite, yandex#hybrid', 'yamaps' ), 
		'label_for' => 'type_map_option' 
	);
	add_settings_field( 'type_map_option', __( 'Map type', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Map height field
	$yamaps_field_params = array(
		'type'      => 'text', // type
		'id'        => 'height_map_option',
		'desc'      => 'rem, em, px, %', 
		'label_for' => 'height_map_option'
	);
	add_settings_field( 'height_map_option', __( 'Map height', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

 
	// Map controls field
	$yamaps_field_params = array(
		'type'      => 'textarea',
		'id'        => 'controls_map_option',
		'desc'      => '<div id="addcontrol" style="text-align: left;"><a data-control="typeSelector">'.__( 'Map type', 'yamaps' ).'</a>, <a data-control="zoomControl">'.__( 'Zoom', 'yamaps' ).'</a>, <a data-control="searchControl">'.__( 'Search', 'yamaps' ).'</a>, <a data-control="routeButtonControl">'.__( 'Route', 'yamaps' ).'</a>, <a data-control="rulerControl">'.__( 'Ruler', 'yamaps' ).'</a>, <a data-control="trafficControl">'.__( 'Traffic', 'yamaps' ).'</a>, <a data-control="fullscreenControl">'.__( 'Full screen', 'yamaps' ).'</a>, <a data-control="geolocationControl">'.__( 'Geolocation', 'yamaps' ).'</a></div>'
	);
	add_settings_field( 'controls_map_option', __( 'Map controls', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Checkbox for wheel zoom
	$yamaps_field_params = array(
		'type'      => 'checkbox',
		'id'        => 'wheelzoom_map_option',
		'desc'      => __( 'The map can be scaled with mouse scroll', 'yamaps' )
	);
	add_settings_field( 'wheelzoom_map_option', __( 'Wheel zoom', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Checkbox for mobile dragging
	$yamaps_field_params = array(
		'type'      => 'checkbox',
		'id'        => 'mobiledrag_map_option',
		'desc'      => __( 'The map can be dragged on mobile', 'yamaps' )
	);
	add_settings_field( 'mobiledrag_map_option', __( 'Mobile drag', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Checkbox for clustering
	$yamaps_field_params = array(
		'type'      => 'checkbox',
		'id'        => 'cluster_map_option',
		'desc'      => __( 'Enable marker clustering by default', 'yamaps' )
	);
	add_settings_field( 'cluster_map_option', __( 'Clustering', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Cluster grid size field
	$yamaps_field_params = array(
		'type'      => 'text',
		'id'        => 'cluster_grid_option',
		'desc'      => __( 'Cluster grid size in pixels (2, 4, 8 ... 64, 128, 256)', 'yamaps' ),
		'label_for' => 'cluster_grid_option'
	);
	add_settings_field( 'cluster_grid_option', __( 'Cluster grid', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Checkbox for opening a big map
	$yamaps_field_params = array(
		'type'      => 'checkbox',
		'id'        => 'open_map_option',
		'desc'      => __( 'Open big map/how to get button', 'yamaps' )
	);
	add_settings_field( 'open_map_option', __( 'Big map', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );

	// Checkbox for author link
	$yamaps_field_params = array(
		'type'      => 'checkbox',
		'id'        => 'authorlink_map_option',
		'desc'      => __( 'Disable link to plugin page', 'yamaps' )
	);
	add_settings_field( 'authorlink_map_option', __( 'Author link', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'map_section', $yamaps_field_params );
 
	// Marker settings area
 
	add_settings_section( 'icon_section', __( 'Marker options', 'yamaps' ), '', $yamaps_page );
 
	// Marker type field
	$yamaps_field_params = array(
		'type'      => 'text',
		'id'        => 'type_icon_option',
		'desc'      => '<a href="https://tech.yandex.com/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/" target="_blank">'.__( 'Other icon types', 'yamaps' ).'</a>'
	);
	add_settings_field( 'type_icon_option', __( 'Icon', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'icon_section', $yamaps_field_params );

	// Marker color field
	$yamaps_field_params = array(
		'type'      => 'text',
		'id'        => 'color_icon_option',
		'desc'      => __( 'For example:', 'yamaps' ).' #ff3333'
	);
	add_settings_field( 'color_icon_option', __( 'Marker color', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'icon_section', $yamaps_field_params );

	// API key area

	add_settings_section( 'apikey_section', __( 'Yandex.Maps API key', 'yamaps' ), '', $yamaps_page );

	// API key field
	$yamaps_field_params = array(
		'type'      => 'text', // type
		'id'        => 'apikey_map_option',
		'desc'      => __( '<a href="https://developer.tech.yandex.com/services/">Get a key</a> (JavaScript API & HTTP Geocoder) if it necessary', 'yamaps' ), 
		'label_for' => 'apikey_map_option'
	);
	add_settings_field( 'apikey_map_option', __( 'API key', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'apikey_section', $yamaps_field_params );

	// Reset settings area
 
	add_settings_section( 'reset_section', __( 'Reset options', 'yamaps' ), '', $yamaps_page );

	// Reset settings checkbox
	$yamaps_field_params = array(
		'type'      => 'checkbox',
		'id'        => 'reset_maps_option',
		'desc'      => __( 'Restore defaults', 'yamaps' )
	);
	add_settings_field( 'reset_maps_option', __( 'Reset options', 'yamaps' ), 'yamaps_option_display_settings', $yamaps_page, 'reset_section', $yamaps_field_params );
 
}
add_action( 'admin_init', 'yamaps_option_settings' );
 
/*
 * Function to display input fields
 * Here is the HTML and PHP that outputs the fields
 */
function yamaps_option_display_settings($args) {
	global $yamaps_defaults, $yamaps_defaults_front_bak;
	extract( $args );
 
	$option_name = 'yamaps_options';
	//delete_option($option_name); // delete settings for testing
 	
	// If settings are not found in the database, save the default settings of the plugin there
	if(!get_option( $option_name)){
    	update_option( $option_name, $yamaps_defaults_front_bak);
	}

	// Need to iterate through the settings and set the default for the missing ones.

	$o = get_option( $option_name );
	
	// Ensure $o is an array
	if ( ! is_array( $o ) ) {
		$o = array();
	}

	foreach ($yamaps_defaults_front_bak as $key => $value) {
		if (!isset($o[$key])) {
			// Set default value for missing keys
			$o[$key] = $yamaps_defaults_front_bak[$key];
		}
	}

	// Ensure the current field exists in options
	if ( ! isset( $o[$id] ) ) {
		$o[$id] = isset( $yamaps_defaults_front_bak[$id] ) ? $yamaps_defaults_front_bak[$id] : '';
	}

	switch ( $type ) {  
		case 'text':  
			$o[$id] = esc_attr( stripslashes( (string) $o[$id] ) );
			echo "<input class='regular-text' type='text' id='" . esc_attr($id) . "' name='" . esc_attr($option_name) . "[" . esc_attr($id) . "]' value='" . esc_attr($o[$id]) . "' />";  
			echo ($desc != '') ? "<br /><span class='description'>" . wp_kses_post($desc) . "</span>" : "";  
		break;
		case 'textarea':  
			$o[$id] = esc_attr( stripslashes( (string) $o[$id] ) );
			echo "<textarea class='code large-text' cols='50' rows='2' type='text' id='" . esc_attr($id) . "' name='" . esc_attr($option_name) . "[" . esc_attr($id) . "]'>" . esc_textarea($o[$id]) . "</textarea>";  
			echo ($desc != '') ? "<br /><span class='description'>" . wp_kses_post($desc) . "</span>" : "";  
		break;
		case 'checkbox':
			$checked = ( isset($o[$id]) && $o[$id] == 'on' ) ? " checked='checked'" :  '';  
			echo "<label><input type='checkbox' id='" . esc_attr($id) . "' name='" . esc_attr($option_name) . "[" . esc_attr($id) . "]' $checked /> ";  
			echo ($desc != '') ? wp_kses_post($desc) : "";
			echo "</label>";  
		break;
		case 'select':
			echo "<select id='" . esc_attr($id) . "' name='" . esc_attr($option_name) . "[" . esc_attr($id) . "]'>";
			foreach($vals as $v=>$l){
				$selected = ( isset($o[$id]) && $o[$id] == $v ) ? "selected='selected'" : '';  
				echo "<option value='" . esc_attr($v) . "' $selected>" . esc_html($l) . "</option>";
			}
			echo ($desc != '') ? wp_kses_post($desc) : "";
			echo "</select>";  
		break;
		case 'radio':
			echo "<fieldset>";
			foreach($vals as $v=>$l){
				$checked = ( isset($o[$id]) && $o[$id] == $v ) ? "checked='checked'" : '';  
				echo "<label><input type='radio' name='" . esc_attr($option_name) . "[" . esc_attr($id) . "]' value='" . esc_attr($v) . "' $checked />" . esc_html($l) . "</label><br />";
			}
			echo "</fieldset>";  
		break; 
	}
}

/*
 * Data validation function
 */
function yamaps_validate_settings($input) {
    // Check nonce
    if (!isset($_POST['yamaps_options_nonce']) || !wp_verify_nonce($_POST['yamaps_options_nonce'], 'yamaps_options_verify')) {
        add_settings_error('yamaps_options', 'invalid_nonce', __('Security check failed', 'yamaps'));
        return get_option('yamaps_options');
    }

    return $input;
}

?>