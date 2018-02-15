var script = document.createElement('script');
    script.src = "https://api-maps.yandex.ru/2.1/?lang="+tinymce.util.I18n.getCode();    
    script.setAttribute('type', 'text/javascript');
    document.getElementsByTagName('head')[0].appendChild(script);

var coords, mapzoom, centermap, maptype;                    

(function() {
      var editor = tinymce.activeEditor;
      function markercolor (pcolor) {
                            placemark.options.set('iconColor', pcolor);
                        }
      function createColorPickAction() {
            var colorPickerCallback = editor.settings.color_picker_callback;

            if (colorPickerCallback) {
                return function() {
                    var self = this;

                    colorPickerCallback.call(
                        editor,
                        function(value) {
                            self.value(value).fire('change');
                            markercolor(self.value());

                        },
                        self.value()
                    );
                };
            }
        }

    tinymce.create("tinymce.plugins.yamap_plugin", {

        //url argument holds the absolute url of our plugin directory
        init : function(ed, url) {

            //add new button    
            ed.addButton("yamap", {
                title : "YaMap",
                //cmd : "yamap_command",
                type: 'menubutton',
                plugins: 'colorpicker',
                image : url+ "/img/placeholder.svg",
                menu: [
                {
                    text: 'AddMap',
                    value: 'mapadd',
                    onclick: function() {
                        ed.execCommand("yamap_command");
                    }
                }
                ]

            });

            //button functionality.
            ed.addCommand("yamap_command", function() {
                        
                    
                    

                    $(document).ready(function() {
                        ymaps.ready(init);
                    });    

                    function init () {
                            var myMap = new ymaps.Map("yamap", {
                                    center: [58.0096,56.2455],
                                    zoom: 16,
                                    type: "yandex#map",
                                    controls: ["zoomControl", "searchControl", "typeSelector"] 
                                });   

                            
                        placemark = new ymaps.Placemark([58.0096,56.2455], {
                                hintContent: "name",
                              
                            }, {
                                preset: "islands#blueDotIcon",
                                draggable: true
                            });  

                        myMap.geoObjects.add(placemark);
                        coords = [58.0096,56.2455];
                        savecoordinats();

                        //Отслеживаем событие перемещения метки
                        placemark.events.add("dragend", function (e) {            
                        coords = this.geometry.getCoordinates();
                        savecoordinats();
                        }, placemark);

                        //Отслеживаем событие щелчка по карте
                        myMap.events.add('click', function (e) {        
                        coords = e.get('coordPosition');
                        savecoordinats();
                        }); 


                        //Ослеживаем событие изменения области просмотра карты - масштаб и центр карты
                        myMap.events.add('boundschange', function (event) {
                        if (event.get('newZoom') != event.get('oldZoom')) {     
                            savecoordinats();
                        }
                          if (event.get('newCenter') != event.get('oldCenter')) {       
                            savecoordinats();
                        }
                        
                        });

                        myMap.events.add('typechange', function (event) {
                            maptype = myMap.getType();
                        });

                        
            
                        $( "#addcontrol a" ).click(function() {

                          if ($("#mapcontrols").val().trim()!="") {
                               $("#mapcontrols").val($("#mapcontrols").val()+ ";"); 
                          }
                          $("#mapcontrols").val($("#mapcontrols").val() + $(this).data("control"));
                        });

                        //Функция для передачи полученных значений в форму
                        function savecoordinats (){ 
                        var new_coords = [coords[0].toFixed(4), coords[1].toFixed(4)];  
                        placemark.geometry.getCoordinates();
                        document.getElementById("markercoord").value = new_coords;
                        mapzoom = myMap.getZoom();
                        
                        var center = myMap.getCenter();
                        var new_center = [center[0].toFixed(4), center[1].toFixed(4)];  
                        centermap = new_center;    
                        }


                        }

                    ed.windowManager.open( {
                        
                        title: 'AddMap',
                        width : 700,
                        height : 500,  
                        body: [
                         {
                        type   : 'container',
                        name   : 'container',
                        label  : '',
                        html   : '<div class="mist" id="yamap"  style="position: relative; min-height: 15rem; margin-bottom: 1rem;"></div>'
                        },


                        {
                            type: 'textbox',
                            name: 'name',
                            label: 'MarkerName'
                            
                        },
                        {
                            type: 'textbox',
                            name: 'coord',
                            label: 'MarkerCoord',
                            id: 'markercoord'
                        },
                        {
                            type   : 'colorbox',  // colorpicker plugin MUST be included for this to work
                            name   : 'color',
                            label  : 'MarkerColor',
                            value : '#1e98ff',
                            id: 'colorbox',
                            onaction: createColorPickAction(),
                          },
                        {
                            type: 'textbox',
                            name: 'controls',
                            label: 'MapControls',
                            id: 'mapcontrols'
                        },
                        {
                        type   : 'container',
                        name   : 'addcontrol',                        

                        html   : '<div id="addcontrol" style="text-align: right;"><a data-control="typeSelector">'+tinymce.util.I18n.translate("type")+'</a>, <a data-control="zoomControl">'+tinymce.util.I18n.translate("zoom")+'</a>, <a data-control="searchControl">'+tinymce.util.I18n.translate("search")+'</a>, <a data-control="routeEditor">'+tinymce.util.I18n.translate("route")+'</a>, <a data-control="rulerControl">'+tinymce.util.I18n.translate("ruler")+'</a>, <a data-control="trafficControl">'+tinymce.util.I18n.translate("traffic")+'</a>, <a data-control="fullscreenControl">'+tinymce.util.I18n.translate("fullscreen")+'</a>, <a data-control="geolocationControl">'+tinymce.util.I18n.translate("geolocation")+'</a></div>'
                        }


                        ],
                        onsubmit: function( e ) {

                            ed.insertContent( '&#91;yamap center="' + centermap + '" zoom="' + mapzoom + '" type="' + maptype + '" controls="' + e.data.controls + '"&#93;&#91;yaplacemark coord="' + e.data.coord + '" color="' + e.data.color + '" name="' + e.data.name + '"&#93;&#91;/yamap&#93;');
                        }


                    });
                
            });

        },


        createControl : function(n, cm) {
            return null;
        },

        getInfo : function() {
            return {
                longname : "YaMap Plugin",
                author : "yhunter",
                version : "1"
            };
        }
    });
    
    tinymce.PluginManager.add("yamap_plugin", tinymce.plugins.yamap_plugin);
    
})();