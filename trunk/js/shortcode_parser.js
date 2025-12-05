// Parse placemark shortcodes inside the map
function findPlaceMarks(found) {
    if (typeof found !== 'string') return;
    
    foundplace = found.match(/\[yaplacemark(.*?)\]/g);
    if (foundplace !== null) {
        for (var j = 0; j < foundplace.length; j++) {       
            foundplacemark = foundplace[j].match(/([a-zA-Z]+)="([^"]+)+"/gi);     
            ym['map0'].places['placemark'+j] = {};
            for (var k = 0; k < foundplacemark.length; k++) {
                // Bugfix: Gutenberg changes ampersands to HTML tags. Change back.
                foundplacemark[k] = foundplacemark[k].split("&amp;").join("&");

                placeparams = foundplacemark[k].split("=");
                // Bugfix: If the string in the shortcode contains an equals sign, don't lose its continuation when splitting into key/value
                if (placeparams.length > 2) {
                    placeparams[1] = foundplacemark[k].replace(placeparams[0]+"=", "");
                }
                placeparams[1] = placeparams[1].replace(/\"|\'/g, '');

                // Safe handling of URL and coordinates
                if (placeparams[0] === 'coord') {
                    // Coordinate validation
                    var coords = placeparams[1].split(',');
                    if (coords.length === 2) {
                        var lat = parseFloat(coords[0]);
                        var lng = parseFloat(coords[1]);
                        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                            ym['map0'].places['placemark'+j][placeparams[0]] = lat + ',' + lng;
                        } else {
                            ym['map0'].places['placemark'+j][placeparams[0]] = '55.7473,37.6247'; // Default coordinates
                        }
                    } else {
                        ym['map0'].places['placemark'+j][placeparams[0]] = '55.7473,37.6247'; // Default coordinates
                    }
                }
                else if (placeparams[0] === 'url') {
                    // Safe URL handling
                    try {
                        var url = decodeURI(placeparams[1]);
                        // Check if value is a number (post ID)
                        if (!isNaN(url)) {
                            ym['map0'].places['placemark'+j][placeparams[0]] = placeparams[1];
                        } else {
                            // If it's a URL, encode it
                            ym['map0'].places['placemark'+j][placeparams[0]] = encodeURI(url);
                        }
                    } catch(e) {
                        ym['map0'].places['placemark'+j][placeparams[0]] = '';
                    }
                }
                else {
                    ym['map0'].places['placemark'+j][placeparams[0]] = placeparams[1];
                }
            }
        }
    }
}


/* Shortcode edit based on https://github.com/dtbaker/wordpress-mce-view-and-shortcode-editor  */
function parseShortcodes(){

    var media = wp.media, shortcode_string = 'yamap'; markcount=0;
    wp.mce = wp.mce || {};
    wp.mce.yamap = {
        shortcode_data: {},
        template: media.template( 'editor-yamap' ),
        getContent: function() { 
            var options = this.shortcode.attrs.named;
            options.text = this.text;
            options.plugin = yamap_object.PluginTitle;
            options.innercontent = this.shortcode.content;
            // Code inside function blocks shortcode insertion in Elementor. Need to investigate.
            if (typeof ElementorConfig === 'undefined') {
                return this.template(options);
            }
        },
        View: { // before WP 4.2:
            template: media.template( 'editor-yamap' ), 
            postID: jQuery('#post_ID').val(),
            initialize: function( options ) {
                this.shortcode = options.shortcode;
                wp.mce.yamap.shortcode_data = this.shortcode;
            },
            getHtml: function() {
                var options = this.shortcode.attrs.named;
                options.innercontent = this.shortcode.content;
                return this.template(options);
            }
        },
        edit: function( data ) {
            var shortcode_data = wp.shortcode.next(shortcode_string, data);
            var values = shortcode_data.shortcode.attrs.named;
            values.innercontent = shortcode_data.shortcode.content;
            wp.mce.yamap.popupwindow(tinyMCE.activeEditor, values);
        },
        // this is called from our tinymce plugin, also can call from our "edit" function above
        // wp.mce.yamap.popupwindow(tinyMCE.activeEditor, "bird");
        popupwindow: function(editor, values, onsubmit_callback){
            
            values = values || [];
            editMapAction=true;
            ym['map0']={};
            ym['map0'].places={};
            for(var key in values) {
                ym['map0'][key]=values[key];                
                delete ym['map0'].innercontent;
                findPlaceMarks(values.innercontent);                            
                                
            }
            mapcenter=ym.map0.center;
            mapzoom=ym.map0.zoom;
            tinymce.activeEditor.execCommand("yamap_command");
            
        }
    };
    wp.mce.views.register( shortcode_string, wp.mce.yamap );
}