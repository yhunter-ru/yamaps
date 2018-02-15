var script = document.createElement('script');
    script.src = "https://api-maps.yandex.ru/2.1/?lang="+tinymce.util.I18n.getCode();    
    script.setAttribute('type', 'text/javascript');
    document.getElementsByTagName('head')[0].appendChild(script);

    function iconselectchange() {      // change icon type                      
        markicon = $("#markericon-inp").val();
        placemark.options.set('preset', markicon);
        iconname();
        //https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/
    }

    function iconname() {       //change icon name
        markername = $("#markername").val();
        markicon = $("#markericon-inp").val();

        if ((markicon==="islands#blueStretchyIcon")||(markicon==="islands#blueIcon")||(markicon==="islands#blueCircleIcon")) {
            yahint="";
            yacontent=markername;
        }
        else {
            yahint=markername;
            yacontent="";
        }

        placemark.properties.set('hintContent', yahint); 
        placemark.properties.set('iconContent', yacontent);
        

    }

var coords, mapzoom, centermap, maptype, markicon;                    

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
                                    center: [55.7532,37.6225],
                                    zoom: 12,
                                    type: "yandex#map",
                                    controls: ["zoomControl", "searchControl", "typeSelector"] 
                                });   

                            
                        placemark = new ymaps.Placemark([55.7532,37.6225], {
                                hintContent: "name",
                              
                            }, {
                                preset: "islands#blueCircleIcon",
                                draggable: true
                            });  

                        myMap.geoObjects.add(placemark);
                        coords = [55.7532,37.6225];
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

                        
                        //Отслеживаем событие поиска и перемещаем метку в центр
                        var searchControl = myMap.controls.get('searchControl');
                        searchControl.events.add("resultselect", function (e) {
                            coords = searchControl.getResultsArray()[0].geometry.getCoordinates();
                            searchControl.hideResult();
                            placemark.geometry.setCoordinates([coords[0],coords[1]]);
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

                        maptype = myMap.getType();

                        myMap.events.add('typechange', function (event) {
                            maptype = myMap.getType();

                        });


                        //отслеживаем изменение типа иконки
                        iconselectchange();
                        $("#markericon-inp").change(function() {
                            iconselectchange();

                        });

                        //отслеживаем изменение имени иконки
                        iconname();
                        $("#markername").change(function() {
                            iconname();

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
                        height : 560,  



                        body: [
                         {
                        type   : 'container',
                        name   : 'container',
                        label  : '',
                        html   : '<div class="mist" id="yamap"  style="position: relative; min-height: 15rem; margin-bottom: 1rem;"></div>'
                        },
                        {
                        layout: 'flow',
                        name: 'tabs',
                        type: 'tabpanel',
                        items: [
                                {
                                    type: 'panel',
                                    title: 'MarkerTab',
                                    items: [
                                        {
                                           type: 'form',
                                           name: 'form1',
                                           minWidth : 598,
                                           items: [
                                                 {
                                                            type: 'textbox',
                                                            name: 'name',
                                                            label: 'MarkerName',
                                                            id: "markername",
                                                            tooltip: "MarkerNameTip",
                                                            
                                                            

                                                    
                                                },
                                                 {
                                                            type: 'textbox',
                                                            name: 'coord',
                                                            label: 'MarkerCoord',
                                                            id: 'markercoord',

                                                },
                                                
                                                
                                               
                                                {
                                                            type   : 'combobox',
                                                            name   : 'markertype',
                                                            label: 'MarkerIcon',
                                                            value: "islands#blueCircleIcon",
                                                            id: "markericon",
                                                            onselect: function() {
                                                                
                                                                iconselectchange();
                                                                
                                                            },
                                                            
                                                            values : [
                                                                { text: 'islands#blueDotIcon', value: 'islands#blueDotIcon' },
                                                                { text: 'islands#blueIcon', value: 'islands#blueIcon' },
                                                                { text: 'islands#blueStretchyIcon ('+tinymce.util.I18n.translate("BlueOnly")+')', value: 'islands#blueStretchyIcon' },
                                                                { text: 'islands#blueCircleDotIcon', value: 'islands#blueCircleDotIcon' },
                                                                { text: 'islands#blueCircleIcon', value: 'islands#blueCircleIcon' }
                                                            ],
                                                            
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
                                                    name: 'url',
                                                    label: 'MarkerUrl',
                                                    id: 'markerurl',
                                                    tooltip: "MarkerUrlTip",

                                                },
                                                {
                                                type   : 'container',
                                                name   : 'empty',   
                                                minWidth : 598,                     

                                                html   : '<div id="addcontrol2" style="text-align: right; width: 500px;">&nbsp;</div>'
                                                }
                                            ]
                                    
                                        }
                                        
                                    ]

                            
                                },
                                {
                                    type: 'panel',
                                    title: 'MapTab',
                                    items: [
                                        {
                                            type: 'form',
                                            name: 'form2',
                                            minWidth : 598,
                                            items: [
                                                {
                                                    type: 'textbox',
                                                    name: 'mapheight',
                                                    label: 'MapHeight',
                                                    id: 'mapheight',
                                                    value: '15rem',                                 
                                                    maxLength: '10',
                                                    tooltip: 'rem, em, px, %'
                                                },

                                                {
                                                type: 'textbox',
                                                name: 'controls',
                                                label: 'MapControls',
                                                id: 'mapcontrols',
                                                tooltip: "MapControlsTip",
                                                },
                                                {
                                                type   : 'container',
                                                name   : 'addcontrol',                        
                                                minWidth : 598,   
                                                html   : '<div id="addcontrol" style="text-align: right;"><a data-control="typeSelector">'+tinymce.util.I18n.translate("type")+'</a>, <a data-control="zoomControl">'+tinymce.util.I18n.translate("zoom")+'</a>, <a data-control="searchControl">'+tinymce.util.I18n.translate("search")+'</a>, <a data-control="routeEditor">'+tinymce.util.I18n.translate("route")+'</a>, <a data-control="rulerControl">'+tinymce.util.I18n.translate("ruler")+'</a>, <a data-control="trafficControl">'+tinymce.util.I18n.translate("traffic")+'</a>, <a data-control="fullscreenControl">'+tinymce.util.I18n.translate("fullscreen")+'</a>, <a data-control="geolocationControl">'+tinymce.util.I18n.translate("geolocation")+'</a></div>'
                                                }
                                                ]
                                        },


                                     


                                    ]

                                },

                            ]
                        },

                        
                        
                        
                        


                        ],
                        onsubmit: function( e ) {

                            ed.insertContent( '&#91;yamap center="' + centermap + '" height="' + e.data.mapheight + '" zoom="' + mapzoom + '" type="' + maptype + '" controls="' + e.data.controls + '"&#93;&#91;yaplacemark coord="' + e.data.coord + '" icon="'+ markicon +'" color="' + e.data.color + '" name="' + e.data.name + '"&#93;&#91;/yamap&#93;');
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