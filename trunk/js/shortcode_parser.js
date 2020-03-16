//Парсим шорткод меток внутри карты
function findPlaceMarks(found) {
    foundplace = found.match(/\[yaplacemark(.*?)\]/g);
        if (foundplace!==null) {
                for (var j = 0; j < foundplace.length; j++) {       
                    foundplacemark=foundplace[j].match(/([a-zA-Z]+)="([^"]+)+"/gi);     
                    ym['map0'].places['placemark'+j]={};
                    for (var k = 0; k < foundplacemark.length; k++) {

			            foundplacemark[k]=foundplacemark[k].split("&amp;").join("&"); //Bugfix: Гутенберг меняет амперсанды на html тэги. Меняем обратно.

                        placeparams=foundplacemark[k].split("=");
                        if (placeparams.length>2) { //Bugfix: Если строка в шорткоде содержит знак равества, не теряем ее продолжение при делении на ключ/значение
                        	placeparams[1]=foundplacemark[k].replace(placeparams[0]+"=", "");
                        }
                        placeparams[1]=placeparams[1].replace(/\"|\'/g, '');
                        if (placeparams[0]==='coord') {
                            ym['map0'].places['placemark'+j][placeparams[0]]=placeparams[1];
                        }
                        else {
                            ym['map0'].places['placemark'+j][placeparams[0]]=[placeparams[1]].join('');                            
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
            if (typeof ElementorConfig === 'undefined') { //код внутри функции блокирует вставку шорткода в Elementor. Нужно разобраться.
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