 var script = document.createElement('script');
    script.src = "https://api-maps.yandex.ru/2.1/?lang="+tinymce.util.I18n.getCode();    
    script.setAttribute('type', 'text/javascript');
    document.getElementsByTagName('head')[0].appendChild(script);

    var mapselector = 'map1', activeplace="";
    var placemark = [];

    //Временные вводные данные. Далее будут удалены.
    var coords = [55.7532,37.6225], mapzoom=12, mapcenter=coords, maptype='yandex#map', markicon, markcount=1, mapcount=1;

    //Изменение поля типа иконки    
    function iconselectchange() {      // change icon type    
        if (activeplace!=='') {
            ym[mapselector].places[activeplace].type=$("#markericon-inp").val();
            iconname();
            ym[mapselector].places[activeplace].type=$("#markericon-inp").val();
        }             
    }

    //Округляем координаты до 4 знаков после запятой
    function coordaprox(fullcoord) {
        //надо сделать проверку на корректный ввод координат
        if (fullcoord.length!==2) {
            fullcoord=fullcoord.split(',');
            if (fullcoord.length!==2) { 
                fullcoord=[0,0];
            }
        }
        return [parseFloat(fullcoord[0]).toFixed(4), parseFloat(fullcoord[1]).toFixed(4)];
    }

    //Изменяем поле с координатами метки
    function markcoordchange() {
        $("#markercoord").val(coordaprox(ym[mapselector].places[activeplace].coord));
    }

    //Активируем выключенное поле
    function enablesinglefield(field) {
    	$(field).attr('disabled',false);
    	$(field).removeClass('mce-disabled');
    	$(field+'-l').removeClass('mce-disabled');
    }

    //Деактивируем поле
    function disablesinglefield(field) {
        $(field).attr('disabled',true);
        $(field).addClass('mce-disabled');
        $(field+'-l').addClass('mce-disabled');        
    }

    //Активируем выключенные поля после создания метки
    function enablefields(fieldact=true) {
        if (fieldact) {
            enablesinglefield('#markercoord'); 
        }
        else {
            disablesinglefield('#markercoord'); 
        }
    	   	
    } 

    //Изменяем данные карты в массиве после изменения полей
    function mapdatechange() {

        ym[mapselector].height=$("#mapheight").val();
        ym[mapselector].ctrl=$("#mapcontrols").val();
        if ($("#scrollzoom").attr('aria-checked')==='false') {
            ym[mapselector].wheelzoom='scrollzoom="0"';

        }
        
        if ($("#mapcontainer").val()!==undefined) {
        	if ($("#mapcontainer").val()!=="") {
	            ym[mapselector].container='container="'+$("#mapcontainer").val()+'"';
	            console.log($("#mapcontainer").val());
        	}
        }
        
        ym[mapselector].zoom=mapzoom;
        ym[mapselector].center=[coordaprox(mapcenter)];
        ym[mapselector].maptype=maptype;       

    }

    //Изменаем данные активной метки в массиве по данным полей ввода
    function markchange() {

        if (activeplace!=='') {
    	
	        ym[mapselector].places[activeplace].name=$("#markername").val();
	        ym[mapselector].places[activeplace].coord=$("#markercoord").val();
	        ym[mapselector].places[activeplace].color=$("#colorbox #colorbox-inp").val();
	        ym[mapselector].places[activeplace].type=$("#markericon #markericon-inp").val();
	        
	        ym[mapselector].places[activeplace].url=$("#markerurl").val();

        }
    }

    //Изменяем данные полей ввода по данным массива    
    function markerfields() {
        $("#markername").val(ym[mapselector].places[activeplace].name);
        $("#markercoord").val(coordaprox(ym[mapselector].places[activeplace].coord));
        $("#markericon #markericon-inp").val(ym[mapselector].places[activeplace].type);
        $("#colorbox #colorbox-inp").val(ym[mapselector].places[activeplace].color);
        $("#colorbox #colorbox-open button i").css('background', ym[mapselector].places[activeplace].color);
        $("#colorbox #colorbox-inp").trigger('change');
        $("#markerurl").val(ym[mapselector].places[activeplace].url);
    }




    //Изменяем имя или хинт иконки в зависимости от ее типа
    function iconname() {       //change icon name
        var markername = $("#markername").val();
        var markicon = $("#markericon-inp").val();

        //Если иконка тянется, выводим название в iconContent
        if (markicon.indexOf("Stretchy")!==-1) { 
            yahint="";
            yacontent=markername;
        }

        //Если круглая пустая иконка, то выводим в iconContent первый символ и название в hintContent
        else {
            if ((markicon==="islands#blueIcon")||(markicon==="islands#blueCircleIcon")) {
                yahint=markername;
                yacontent=yahint[0];
            }
            //Если иконка с точкой, то выводим название в hintContent
            else {
                yahint=markername;
                yacontent="";
            } 
        }

        if (activeplace!=='') {
            placemark[activeplace.replace('placemark', '')].properties.set('hintContent', yahint);
            placemark[activeplace.replace('placemark', '')].properties.set('iconContent', yacontent);
            placemark[activeplace.replace('placemark', '')].options.set('preset', markicon);
        }
    }


//Работаем с редактором tinyMCE в модальном окне
(function() { 
    var editor = tinymce.activeEditor;

    //Изменяем цвет иконки на карте, записываем в массив  
    function markercolor (pcolor) {
        //Если метки нет, цвета не прописываем
        if (activeplace!=='') {
	        placemark[activeplace.replace('placemark', '')].options.set('iconColor', pcolor);
	        ym[mapselector].places[activeplace].color=$("#colorbox #colorbox-inp").val();
        }                            
    }

    //Плагин colorPicker для tinyMCE
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

    //Добавляем кнопку и меню плагина в редактор
    tinymce.create("tinymce.plugins.yamap_plugin", {

        //url argument holds the absolute url of our plugin directory
        init : function(ed, url) {



            //add new button    
            ed.addButton("yamap", {
                title : yamap_object.YaMap,
                //cmd : "yamap_command",
                type: 'menubutton',
                plugins: 'colorpicker',

                image : url+ "/img/placeholder.svg",
                menu: [
                {
                    text: yamap_object.AddMap,
                    value: 'mapadd',
                    onclick: function() {
                        ed.execCommand("yamap_command");
                    }
                }
                ]

            });



            //Функционал кнопки в редакторе
            ed.addCommand("yamap_command", function() {
            activeplace="";            
                    
                    
                    //Инициализируем карту
                    $(document).ready(function() {
                        ymaps.ready(init);
                        $('.mce-container').css('line-height', '.75rem');
                        ym={map1: {center: [55.7532,37.6225], height: '15rem', zoom: '16', maptype: 'yandex#map', ctrl: '', wheelzoom: '', container: '', places: {}}};

                    });
                    
                    //Удаляем метку с карты
                    function removeplacemark(map, place) {
                        map.geoObjects.remove(place);
                    }

                    //Функция создания метки
                    function createplacemark(map, defcoord=[55.7532,37.6225]) {

                        enablefields(); //Активируем выключенные поля
                        ym.map1['places']['placemark'+markcount] = {name: '', coord: defcoord, type: 'islands#blueDotIcon', color: $("#colorbox #colorbox-inp").val(), url: ''}; //: {name: 'placemark1', coord: coords, type: 'islands#blueDotIcon', color: '#ff0000', url: 'url1'};
                        if (activeplace==='') { //Если создается первая метка, берем значения из полей формы
                        	activeplace = 'placemark'+markcount; 
                        	markchange();
                        	ym.map1['places']['placemark'+markcount].coord = defcoord;

                        }
                        
                        activeplace = 'placemark'+markcount; 
                        markerfields();

                        //Создание метки на карте
                        placemark[markcount] = new ymaps.Placemark(defcoord, {
                                hintContent: "name",
                                id: 'placemark'+markcount,

                              
                            }, {
                                preset: $("#markericon #markericon-inp").val(), //берем тип создаваемой иконки из поле ввода
                                draggable: true,
                                iconColor: $("#colorbox #colorbox-inp").val(), //берем цвет создаваемой иконки из поле ввода
                                zIndex: 1,
                        });

                        
                        map.geoObjects.add(placemark[markcount]);
                        iconname();
                        coords = defcoord;

                        //Отслеживаем событие перемещения метки
                        placemark[markcount].events.add("dragend", function (e) {   
                            var trg = e.get('target');         
                        	coords = this.geometry.getCoordinates();
                            ym.map1['places'][trg.properties.get('id')].coord = coords;
                        }, placemark[markcount]);

                        //Отслеживаем событие начала перемещения метки
                        placemark[markcount].events.add("dragstart", function (e) {            
                            var trg = e.get('target'); 
                            activeplace=trg.properties.get('id');
                        	map.geoObjects.each(function (geoObject) {
							    if (geoObject.properties.get('id') == 'closesvg') {
							    	map.geoObjects.remove(geoObject);									
							    }

							});

                        }, placemark[markcount]);

                        
                        //Добавляем событие клика по метке
                        placemark[markcount].events.add('click', function (e) {

                            var trg = e.get('target');
                            var plcoord=e.get('target').geometry.getCoordinates();
                            activeplace = trg.properties.get('id');
                            markerfields();
                            
                            //Удаляем метку-закрытия для всех меток
                            map.geoObjects.each(function (geoObject) {
							    if (geoObject.properties.get('id') == 'closesvg') {
							    	map.geoObjects.remove(geoObject);									
							    }
                                activeplace='';
							});

                            //Добавляем метку-кнопку закрытия родительской метки
                            var closePlacemark = new ymaps.Placemark(
                                  plcoord,
                                  {
                                    hintContent: yamap_object.MarkerDelete,
                                    id: 'closesvg',
                                  }, {
                                    
                                    iconLayout: 'default#image',
                                    iconImageHref: url+ "/img/close.svg",
                                    iconImageSize: [16, 16],
                                    // Описываем фигуру активной области
                                    // "Прямоугольник".
                                    iconOffset: [-2, -30],
                                    iconImageOffset: [5, 10],
                                    zIndex: 999,

                                  }
                                );
                            activeplace=trg.properties.get('id');
                            map.geoObjects.add(closePlacemark);
                            closePlacemark.events.add('click', function (e) {
                                removeplacemark(map, closePlacemark); 
                                removeplacemark(map, trg);
                                activeplace='';                                  
                                delete ym.map1['places'][trg.properties.get('id')]; // удаляем все свойства точки из массива
                                if (Object.keys(ym.map1.places).length===0) {
                                    //Выключаем поле с координатами
                                    $('#markercoord').val(yamap_object.NoCoord);
                                    enablefields(false);
                                }
                            });

                            //Событие клика для закрытия метки
                            map.events.add('click', function (e) { 
                                removeplacemark(map, closePlacemark);
                            });

                    });

                        map.geoObjects.add(placemark[markcount]);
                        markcount++;
                    }   

                    //Функция инициализации карты
                    function init () {
                            var myMap=[];
                            myMap[mapcount] = new ymaps.Map("yamap", {
                                    center: [55.7532,37.6225],
                                    zoom: 12,
                                    type: "yandex#map",
                                    controls: ["zoomControl", "searchControl", "typeSelector"] 
                                }); 

                        //Отслеживаем событие щелчка по карте
                        myMap[mapcount].events.add('click', function (e) { 
                            createplacemark(myMap[mapcount], e.get('coords'));
                        }); 


                        //Отслеживаем событие поиска и ставим метку в центр
                        var searchControl = myMap[mapcount].controls.get('searchControl');                        
                        searchControl.events.add("resultshow", function (e) {
                            coords = searchControl.getResultsArray()[0].geometry.getCoordinates();
                            searchControl.hideResult();
                            createplacemark(myMap[mapcount], coords); 
                        });


                        //Ослеживаем событие изменения области просмотра карты - масштаб и центр карты
                        myMap[mapcount].events.add('boundschange', function (event) {
                        if (event.get('newZoom') != event.get('oldZoom')) {     
                            mapzoom=event.get('newZoom');
                            mapdatechange();
                            
                        }
                          if (event.get('newCenter') != event.get('oldCenter')) {
                          	mapcenter = event.get('newCenter');
                            mapdatechange();      
                        }
                        
                        });

                        maptype = myMap[mapcount].getType();                        

                        myMap[mapcount].events.add('typechange', function (event) {
                            maptype = myMap[mapcount].getType();
                            mapdatechange();
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

                        $("#mapheight, #mapcontrols").change(function() {
                            mapdatechange();
                        });
                        $("#scrollzoom, #addcontrol a").click(function() {
                            mapdatechange();
                        });

                        $("#markername, #markercoord, #markericon, #markerurl").change(function() {
                            markchange();
                        });                        
                        
            
                        $( "#addcontrol a" ).click(function() {

                          if ($("#mapcontrols").val().trim()!="") {
                               $("#mapcontrols").val($("#mapcontrols").val()+ ";"); 
                          }
                          $("#mapcontrols").val($("#mapcontrols").val() + $(this).data("control"));
                          mapdatechange();
                        });

                    }

                    //Параметры модального окна редактора
                    var win = ed.windowManager.open( {
                        
                        title: yamap_object.AddMap,
                        width : 700,
                        height : 560, 

                        body: [
                         {
                        type   : 'container',
                        name   : 'container',
                        id : 'yamapcontainer',
                        label  : '',
                        
                        html   : '<div id="yamap"  style="position: relative; min-height: 15rem; margin-bottom: 1rem; "></div>'
                        },
                        {
                        layout: 'flow',
                        name: 'tabs',
                        type: 'tabpanel',
                        items: [
                                {
                                    type: 'panel',
                                    title: yamap_object.MarkerTab,
                                    items: [
                                        {
                                           type: 'form',
                                           name: 'form1',
                                           minWidth : 598,
                                           items: [
                                                 {
                                                            type: 'textbox',
                                                            name: 'name',
                                                            label: yamap_object.MarkerName,
                                                            id: "markername",
                                                            tooltip: yamap_object.MarkerNameTip,
                                                            value: "",                           
    
                                                },
                                                {
                                                            type: 'textbox',
                                                            name: 'coord',
                                                            label: yamap_object.MarkerCoord,
                                                            id: 'markercoord',
                                                            disabled: true,
                                                            value: yamap_object.NoCoord,                                                            

                                                },
                                                {
                                                            type   : 'combobox',
                                                            name   : 'markertype',
                                                            label: yamap_object.MarkerIcon,
                                                            value: "islands#blueDotIcon",
                                                            id: "markericon",
                                                            
                                                            onselect: function() {
                                                                
                                                                iconselectchange();
                                                                
                                                            },
                                                            
                                                            values : [
                                                                { text: 'islands#blueDotIcon', value: 'islands#blueDotIcon' },
                                                                { text: 'islands#blueIcon', value: 'islands#blueIcon' },
                                                                { text: 'islands#blueStretchyIcon ('+yamap_object.BlueOnly+')', value: 'islands#blueStretchyIcon' },
                                                                { text: 'islands#blueCircleDotIcon', value: 'islands#blueCircleDotIcon' },
                                                                { text: 'islands#blueCircleIcon', value: 'islands#blueCircleIcon' }
                                                            ],
                                                            
                                                        },
                                                        {
                                                            type   : 'colorbox',  // colorpicker plugin MUST be included for this to work
                                                            name   : 'color',
                                                            label  : yamap_object.MarkerColor,
                                                            value : '#1e98ff',
                                                            id: 'colorbox',
                                                            onaction: createColorPickAction(),
                                                            
                                                },
                                                {
                                                    type: 'textbox',
                                                    name: 'url',
                                                    label: yamap_object.MarkerUrl,
                                                    id: 'markerurl',
                                                    tooltip: yamap_object.MarkerUrlTip,
                                                    

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
                                    title: yamap_object.MapTab,
                                    items: [
                                        {
                                            type: 'form',
                                            name: 'form2',
                                            minWidth : 598,
                                            items: [
                                                {
                                                    type: 'textbox',
                                                    name: 'mapheight',
                                                    label: yamap_object.MapHeight,
                                                    id: 'mapheight',
                                                    value: '15rem',                                 
                                                    maxLength: '10',
                                                    tooltip: 'rem, em, px, %',
                                                    onaction: mapdatechange(),
                                                },

                                                {
                                                    type: 'textbox',
                                                    name: 'controls',
                                                    label: yamap_object.MapControls,
                                                    id: 'mapcontrols',
                                                    tooltip: yamap_object.MapControlsTip,
                                                    onaction: mapdatechange(),
                                                },
                                                {
                                                type   : 'container',
                                                name   : 'addcontrol',
                                                id     : 'ctrlhelper',                        
                                                minWidth : 598,   
                                                html   : '<div id="addcontrol" style="text-align: right;"><a data-control="typeSelector">'+yamap_object.type+'</a>, <a data-control="zoomControl">'+yamap_object.zoom+'</a>, <a data-control="searchControl">'+yamap_object.search+'</a>, <a data-control="routeButtonControl">'+yamap_object.route+'</a>, <a data-control="rulerControl">'+yamap_object.ruler+'</a>, <a data-control="trafficControl">'+yamap_object.traffic+'</a>, <a data-control="fullscreenControl">'+yamap_object.fullscreen+'</a>, <a data-control="geolocationControl">'+yamap_object.geolocation+'</a></div>'
                                                },                                                
                                                {
                                                    type: 'checkbox',
                                                    checked: true,
                                                    name: 'scrollZoom',
                                                    label: yamap_object.ScrollZoom,
                                                    id: 'scrollzoom',
                                                    onaction: mapdatechange(),

                                                },
                                                {
                                                    type: 'textbox',
                                                    name: 'mapcontainer',
                                                    label: yamap_object.MapContainerID,
                                                    id: 'mapcontainer',
                                                    value: '', 
                                                    tooltip: yamap_object.MapContainerIDTip,
                                                    onaction: mapdatechange(),
                                                },
                                                ]
                                        },


                                     


                                    ]

                                },
                                {
                                    type: 'panel',
                                    title: yamap_object.Extra,
                                    items: [
                                        {
                                            type: 'form',
                                            name: 'form2',
                                            minWidth : 598,

                                            items: [
                                                {
                                                type   : 'container',
                                                name   : 'addcontrol',
                                                                       
                                                minWidth : 598,   
                                                html   : yamap_object.ExtraHTML,
                                            },
                                                
                                                ]
                                        },


                                     


                                    ]

                                }

                            ]
                        },                    
                        
                        ],
                        onsubmit: function( e ) {
                            mapdatechange();
                            markchange();
                            var dialogArguments = ed.windowManager.getParams();

                             

                            contentplacemarks = ''; 
                            yamapnumber='map1'; 
                            for(var key in ym.map1['places']) {
                                contentplacemarks = contentplacemarks + '&#91;yaplacemark coord="' + ym[yamapnumber].places[key].coord + '" icon="'+ ym[yamapnumber].places[key].type +'" color="' + ym[yamapnumber].places[key].color + '" url="' + ym[yamapnumber].places[key].url + '" name="' + ym[yamapnumber].places[key].name + '"' + '&#93;';

                            } 
                            
                            ed.insertContent( '&#91;yamap center="' + ym[yamapnumber].center + '" height="' + ym[yamapnumber].height + '" zoom="' + ym[yamapnumber].zoom + '" ' + ym[yamapnumber].wheelzoom + ym[yamapnumber].container + ' type="' + ym[yamapnumber].maptype + '" controls="' + ym[yamapnumber].ctrl + '"&#93; '+contentplacemarks+' &#91;/yamap&#93;');
                        }
                    });
                mapdatechange();
                
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