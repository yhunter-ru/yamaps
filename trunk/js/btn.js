;var script = document.createElement('script');
var apikey = '', apikeyexist = false, wp_locale=tinymce.util.I18n.getCode();
if (yamap_defaults['apikey_map_option']!='') {
    apikey="&apikey="+yamap_defaults['apikey_map_option'];
    apikeyexist = true;
}
if (typeof wp_locale !== 'undefined') { //Защита от пустой локали. У кого-то была такая проблема
    if (wp_locale.length<1) {wp_locale="en_US";} 
}
else {
    wp_locale="en_US";
}
script.src = "https://api-maps.yandex.ru/2.1/?lang="+wp_locale+apikey;    
script.setAttribute('type', 'text/javascript');
document.getElementsByTagName('head')[0].appendChild(script);

var mapselector = 'map0', activeplace="", ym={}, editMapAction=false, editorContentData;
var placemark = [];

if (!editMapAction) {//Перенесено для корректной работы в WP 5.6
                            
    ym={map0: {center: coordaprox(yamap_defaults['center_map_option']), controls: yamap_defaults['controls_map_option'], height: yamap_defaults['height_map_option'], zoom: yamap_defaults['zoom_map_option'], maptype: yamap_defaults['type_map_option'], scrollzoom: optionCheck('wheelzoom_map_option'), mobiledrag: optionCheck('mobiledrag_map_option'), container: '', places: {}}};

}  

//Определяем отключен ли скролл колесом на редактируемой карте
function checkParam(param) {
    var checker=true;
    if (ym.map0[param]==="0") { 
        checker=false;
    }
    return checker;
}

//Задаем дефолтные параметры из настроек
function optionCheck(param) {
	var checker="0";
	if (yamap_defaults[param]==='on')	{
		checker="";
	}
	return checker;
}

//Определяем заголовок окна для создания или редактирования
function checkTitle() {
    if (editMapAction) {
        return yamap_object.EditMap;
    }
    else {
        return yamap_object.AddMap;
    }
}

//Дефолтные данные
var coords=[], mapcenter=yamap_defaults['center_map_option'], mapzoom=yamap_defaults['zoom_map_option'], maptype=yamap_defaults['type_map_option'], markicon, markcount=0, mapcount=1;

(function($) {
    parseShortcodes();
})(jQuery);


//Изменение поля типа иконки    
function iconselectchange() {      // change icon type    
        if (activeplace!=='') {
            ym[mapselector].places[activeplace].icon=jQuery("#markericon-inp").val();
            iconname();
            ym[mapselector].places[activeplace].icon=jQuery("#markericon-inp").val();
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
        jQuery("#markercoord").val(coordaprox(ym[mapselector].places[activeplace].coord));
}

//Активируем выключенное поле
function enablesinglefield(field) {
        jQuery(field).attr('disabled',false);
        jQuery(field).removeClass('mce-disabled');
        jQuery(field+'-l').removeClass('mce-disabled');
}

//Деактивируем поле
function disablesinglefield(field) {
        jQuery(field).attr('disabled',true);
        jQuery(field).addClass('mce-disabled');
        jQuery(field+'-l').addClass('mce-disabled');        
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

//Проверяем значение чекбокса
function checkcheckbox(param) {
    if (jQuery("#"+param).attr('aria-checked')!="undefined") {
        if (jQuery("#"+param).attr('aria-checked')=='true') {
                    ym[mapselector][param]='1';

                }
                else ym[mapselector][param]='0';
    }           
}

//Изменяем данные карты в массиве после изменения полей
function mapdatechange() {
        
        if(document.getElementById('mapcontrols')) {
            if (jQuery("#mapcontrols").val().trim().substr(-1)===';') jQuery("#mapcontrols").val(jQuery("#mapcontrols").val().trim().slice(0, -1));          
            ym[mapselector].controls=jQuery("#mapcontrols").val();
        } 

        if(document.getElementById('mapheight')) {
            ym[mapselector].height=jQuery("#mapheight").val();
        } 

        setTimeout(checkcheckbox, 200, 'scrollzoom');
        setTimeout(checkcheckbox, 200, 'mobiledrag');
          
        if(document.getElementById('mapcontainer')) {
            if (jQuery("#mapcontainer").val()!="undefined") {

                    ym.map0.container=jQuery("#mapcontainer").val();

            }
        }
        
}

//Изменяем данные карты в массиве после изменения карты в редакторе
function mapSave() {
        ym[mapselector].zoom=mapzoom;
        ym[mapselector].center=[coordaprox(mapcenter)];
        ym[mapselector].maptype=maptype;  
}

//Изменаем данные активной метки в массиве по данным полей ввода
function markchange() {

        if (activeplace!=='') {
        
            ym[mapselector].places[activeplace].name=jQuery("#markername").val().replace(/["]/g, '&quot;');
            ym[mapselector].places[activeplace].coord=jQuery("#markercoord").val();
            ym[mapselector].places[activeplace].color=jQuery("#colorbox #colorbox-inp").val();
            ym[mapselector].places[activeplace].icon=jQuery("#markericon #markericon-inp").val();
            
            ym[mapselector].places[activeplace].url=jQuery("#markerurl").val();

        }
}

//Изменяем данные полей ввода по данным массива    
function markerfields() {
		if (typeof ym[mapselector].places[activeplace].name !== 'undefined') {
        	jQuery("#markername").val(ym[mapselector].places[activeplace].name.replace(/(&quot;)/g, '"'));
        }
        jQuery("#markercoord").val(coordaprox(ym[mapselector].places[activeplace].coord));
        jQuery("#markericon #markericon-inp").val(ym[mapselector].places[activeplace].icon);
        jQuery("#colorbox #colorbox-inp").val(ym[mapselector].places[activeplace].color);
        jQuery("#colorbox #colorbox-open button i").css('background', ym[mapselector].places[activeplace].color);
        jQuery("#colorbox #colorbox-inp").trigger('change');
        jQuery("#markerurl").val(ym[mapselector].places[activeplace].url);
}

//Выключаем поле координат, когда не выбрана метка
function inactive() {
            jQuery("#markercoord").val(yamap_object.NoCoord);
            enablefields(false);
}


//Изменяем имя или хинт иконки в зависимости от ее типа
function iconname(place) {       //change icon name
		yacontent="";
        if (activeplace!=='') { 
            place=activeplace;
        }
        var markername = ym[mapselector].places[place].name;
        var markicon = ym[mapselector].places[place].icon;
        var yahint = "";

        //Если иконка тянется, выводим название в iconContent
        if (markicon.indexOf("Stretchy")!==-1) { 
            yahint="";
            yacontent=markername;
        }

        //Если круглая пустая иконка, то выводим в iconContent первый символ и название в hintContent
        else {
            if ((markicon==="islands#blueIcon")||(markicon==="islands#blueCircleIcon")) {
                yahint=markername;

                if ((yahint!="")&&(yahint!=undefined)) {
                    yacontent=yahint[0];
                }
                
            }
            //Если иконка с точкой, то выводим название в hintContent
            else {
                yahint=markername;
                yacontent="";
            } 
        }

        if (place!=='') {
            placemark[place.replace('placemark', '')].properties.set('hintContent', yahint);
            placemark[place.replace('placemark', '')].properties.set('iconContent', yacontent);
            //Проверяем, является ли поле иконки url-адресом. Если да, то ставим в качестве иконки кастомное изображение.
            if (markicon.indexOf("http")===-1) {
                placemark[place.replace('placemark', '')].options.unset('iconLayout', 'default#image');
                placemark[place.replace('placemark', '')].options.unset('iconImageHref', markicon);
                placemark[place.replace('placemark', '')].options.set('preset', markicon);
            }
            else {            
                placemark[place.replace('placemark', '')].options.unset('preset', markicon);
                placemark[place.replace('placemark', '')].options.set('iconLayout', 'default#image');
                placemark[place.replace('placemark', '')].options.set('iconImageHref', markicon);
            }
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
            ym[mapselector].places[activeplace].color=jQuery("#colorbox #colorbox-inp").val();
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
                title : yamap_object.AddMap,
                type: 'button',
                plugins: 'colorpicker',

                image : url+ "/img/placeholder.svg",
                onclick: function() {
                        editMapAction=false;
                        ed.execCommand("yamap_command");
                }


            });





            //Функционал кнопки в редакторе
            ed.addCommand("yamap_command", function() {
            activeplace="";   
            markcount=0;         
                    
                    
                    //Инициализируем карту
                    jQuery(document).ready(function() {                       
                        

                        if (!editMapAction) { //Перенесено в начало кода из-за проблем в WP 5.6
                            delete  ym.map0.places;
                            ym.map0.places = {};

                        //    ym={map0: {center: coordaprox(yamap_defaults['center_map_option']), controls: yamap_defaults['controls_map_option'], height: yamap_defaults['height_map_option'], zoom: yamap_defaults['zoom_map_option'], maptype: yamap_defaults['type_map_option'], scrollzoom: optionCheck('wheelzoom_map_option'), mobiledrag: optionCheck('mobiledrag_map_option'), container: '', places: {}}};

                        }  
                        ymaps.ready(init);                      

                    });
                    
                    //Удаляем метку с карты
                    function removeplacemark(map, place) {
                        map.geoObjects.remove(place);
                    }

                    //Функция создания метки
                    function createplacemark(map, defcoord=[55.7532,37.6225]) {
                        var newmark=false;
                        enablefields(); //Активируем выключенные поля

                        if (!ym.map0.places.hasOwnProperty('placemark'+markcount))  {  
                            newmark=true;
                            ym.map0['places']['placemark'+markcount] = {name: '', coord: defcoord, icon: yamap_defaults['type_icon_option'], color: jQuery("#colorbox #colorbox-inp").val(), url: ''}; //: {name: 'placemark1', coord: coords, type: 'islands#blueDotIcon', color: '#ff0000', url: 'url1'};
                            if (activeplace==='') { //Если создается первая метка, берем значения из полей формы
                                activeplace = 'placemark'+markcount; 
                                markchange();
                                ym.map0['places']['placemark'+markcount].coord = defcoord;

                            }
                            activeplace = 'placemark'+markcount; 
                            markerfields();
                        }

                        


                        //Создание метки на карте
                        placemark[markcount] = new ymaps.Placemark(defcoord, {
                                hintContent: "name",
                                id: 'placemark'+markcount,

                              
                            }, {                                
                                draggable: true,
                                preset: ym.map0['places']['placemark'+markcount].icon, 
                                iconColor: ym.map0['places']['placemark'+markcount].color, 
                                zIndex: 1,
                        });

                        
                        activeplace = 'placemark'+markcount; 
                        iconname('placemark'+markcount);
                        activeplace = '';
                        coords = defcoord;



                        //Отслеживаем событие перемещения метки
                        placemark[markcount].events.add("dragend", function (e) {   
                            var trg = e.get('target');         
                            coords = this.geometry.getCoordinates();
                            ym.map0['places'][trg.properties.get('id')].coord = coords;
                            activeplace='';
                            inactive(); 
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
                            enablefields();
                            
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
                                inactive();                                
                                delete ym.map0['places'][trg.properties.get('id')]; // удаляем все свойства точки из массива
                                if (Object.keys(ym.map0.places).length===0) {
                                    //Выключаем поле с координатами
                                    jQuery('#markercoord').val(yamap_object.NoCoord);
                                    enablefields(false);
                                }
                            });

                            //Событие клика для закрытия метки
                            map.events.add('click', function (e) { 
                                removeplacemark(map, closePlacemark);
                            });

                    });

                        map.geoObjects.add(placemark[markcount]);
                        if (newmark) placemark[markcount].events.fire('click');
                        markcount++;

                    }   



                    //Функция инициализации карты
                    function init () {
                            var myMap=[];
                            var controlsArr=["zoomControl", "typeSelector"];
                            if (apikeyexist) controlsArr.push("searchControl"); //Если определен API key, добавляем поиск на карту. Без ключа он все равно не будет работать и выдавать ошибку.

                            mapcenter=ym.map0.center[0];
                            coords=ym.map0.center[0];
                            myMap[mapcount] = new ymaps.Map("yamap", {
                                    center: ym.map0.center[0],
                                    zoom: ym.map0.zoom,
                                    type: ym.map0.maptype,
                                    controls: controlsArr 
                            },
                            {
                                suppressMapOpenBlock: true
                            }); 

                        //Заполняем данные формы из массива при редактировании
                        function loadMap() {
                            if (ym.map0.height!=="undefined") jQuery('#mapheight').val(ym.map0.height);
                            if (ym.map0.controls!=="undefined") jQuery('#mapcontrols').val(ym.map0.controls);

                        }



                        //Ставим метки редактируемой карты
                        function loadPlacemarks(map) {

                            for(var key in ym.map0['places']) {
                                activeplace=key;
                                createplacemark(myMap[mapcount],coordaprox(ym[map].places[key].coord));                               
                                
                            }
                            activeplace="";
                            enablefields(false);
                        }

                        if (editMapAction) {
                            loadPlacemarks('map0');
                            loadMap();

                        } 

                        //Отслеживаем событие щелчка по карте
                        myMap[mapcount].events.add('click', function (e) { 
                            createplacemark(myMap[mapcount], e.get('coords'));
                        }); 


                        //Отслеживаем событие поиска и ставим метку в центр
                        if (apikeyexist) {
	                        var searchControl = myMap[mapcount].controls.get('searchControl');                        
	                        searchControl.events.add("resultshow", function (e) {
	                            coords = searchControl.getResultsArray()[0].geometry.getCoordinates();
	                            searchControl.hideResult();
	                            createplacemark(myMap[mapcount], coords);
	                            mapSave(); 
	                        });
	                    }


                        //Ослеживаем событие изменения области просмотра карты - масштаб и центр карты
                        myMap[mapcount].events.add('boundschange', function (event) {
                        if (event.get('newZoom') != event.get('oldZoom')) {     
                            mapzoom=event.get('newZoom');
                            mapSave();
                            
                        }
                          if (event.get('newCenter') != event.get('oldCenter')) {
                            mapcenter = event.get('newCenter');
                            mapSave();      
                        }
                        
                        });

                        maptype = myMap[mapcount].getType();                        

                        //Отслеживаем изменение типа карты
                        myMap[mapcount].events.add('typechange', function (event) {
                            maptype = myMap[mapcount].getType();
                            mapSave();
                        });
                        
                        iconselectchange();


                        jQuery("#mapheight, #mapcontrols").change(function() {
                            mapdatechange();
                        });
                        jQuery("#scrollzoom, #mobiledrag, #addcontrol a").click(function() {
                            mapdatechange();
                        });

                        //отслеживаем изменение полей иконки  
                        jQuery("#markername, #markercoord, #markericon-inp, #markerurl").change(function() {
                            markchange();
                        });   


                        //отслеживаем изменение имени иконки  
                        jQuery("#markername").change(function() {
                            if (activeplace!=="") {
                                iconname();
                            }                            
                        });  


                        //отслеживаем изменение типа иконки
                        jQuery("#markericon-inp").change(function() {
                            iconselectchange();
                        });                  
                        
            
                        jQuery( "#addcontrol a" ).click(function() {
                          if (jQuery("#mapcontrols").val().trim()!="") {
                               jQuery("#mapcontrols").val(jQuery("#mapcontrols").val()+ ";"); 
                          }
                          jQuery("#mapcontrols").val(jQuery("#mapcontrols").val() + jQuery(this).data("control"));
                          mapdatechange();
                        });

                    }

                    //Параметры модального окна редактора
                    function checkApiKeyForSearchField() {
                    	if (apikeyexist) {
                    		return '<a data-control="searchControl">'+yamap_object.search+'</a>, ';
                    	}
                    	else return '';
                    }
                    var win = ed.windowManager.open( {
                        
                        title: checkTitle(),
                        width : 700,
                        height : 560, 
                        buttons: [
                        {
                          text: 'OK',
                          classes: 'btn primary',
                          onclick: 'submit',
                          id: 'insertButton'
                      }, {
                          text: 'Close',
                          onclick: 'close',
                          window : win,
   
                      }],

                        body: [
                         {
                        type   : 'container',
                        name   : 'container',
                        id : 'yamapcontainer',
                        label  : '',
                        
                        html   : '<div id="yamap"  style="position: relative; min-height: 15rem; margin-bottom: 1rem; overflow: hidden"></div>'
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
                                                            value: yamap_defaults['type_icon_option'],
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
                                                            value : yamap_defaults['color_icon_option'],
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
                                                    value: ym[mapselector].height, //yamap_defaults['height_map_option'],                                 
                                                    maxLength: '10',
                                                    tooltip: 'rem, em, px, %',
                                                    onaction: mapdatechange(),
                                                },

                                                {
                                                    type: 'textbox',
                                                    name: 'controls',
                                                    label: yamap_object.MapControls,
                                                    id: 'mapcontrols',
                                                    value: ym[mapselector].controls, //yamap_defaults['controls_map_option'],   
                                                    tooltip: yamap_object.MapControlsTip,
                                                    onaction: mapdatechange(),
                                                },
                                                {
                                                type   : 'container',
                                                name   : 'addcontrol',
                                                id     : 'ctrlhelper',                        
                                                minWidth : 598,   
                                                html   : '<div id="addcontrol" style="text-align: right;"><a data-control="typeSelector">'+yamap_object.type+'</a>, <a data-control="zoomControl">'+yamap_object.zoom+'</a>, '+checkApiKeyForSearchField()+'<a data-control="routeButtonControl">'+yamap_object.route+'</a>, <a data-control="rulerControl">'+yamap_object.ruler+'</a>, <a data-control="trafficControl">'+yamap_object.traffic+'</a>, <a data-control="fullscreenControl">'+yamap_object.fullscreen+'</a>, <a data-control="geolocationControl">'+yamap_object.geolocation+'</a></div>'
                                                },                                                
                                                {
                                                    type: 'checkbox',
                                                    checked: checkParam('scrollzoom'),
                                                    name: 'scrollZoom',
                                                    label: yamap_object.ScrollZoom,
                                                    id: 'scrollzoom',
                                                    onaction: mapdatechange(),

                                                },                                                
                                                {
                                                    type: 'checkbox',
                                                    checked: checkParam('mobiledrag'),
                                                    name: 'mobileDrag',
                                                    label: yamap_object.MobileDrag,
                                                    id: 'mobiledrag',
                                                    onaction: mapdatechange(),

                                                },
                                                {
                                                    type: 'textbox',
                                                    name: 'mapcontainer',
                                                    label: yamap_object.MapContainerID,
                                                    id: 'mapcontainer',
                                                    value: ym.map0.container, 
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

                                },
                                {
                                    type: 'panel',
                                    title: '🦉\u00A0' + yamap_object.DeveloperInfoTab,
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
                                                html   : yamap_object.DeveloperInfo,
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
                            //mapSave();
                            if (activeplace!=="") {
                                markchange();
                                iconname();
                            }
                            
                            contentplacemarks = ''; 
                                yamapnumber='map0'; 
                                for(var key in ym.map0['places']) {
                                    contentplacemarks = contentplacemarks + '[yaplacemark ';
                                    for (var keyplace in ym.map0['places'][key]) {
                                        if (ym.map0['places'][key][keyplace]!=='') {
                                            contentplacemarks = contentplacemarks + ' ' + keyplace + '="' + ym.map0['places'][key][keyplace] + '"';  
                                        }     
                                    }
                                    contentplacemarks = contentplacemarks + ']';                                    

                                } 
                            

                            var mapArgs = {
                            tag     : 'yamap',
                            attrs : {
                                center: ym[yamapnumber].center,
                                height: ym[yamapnumber].height,
                                controls: ym[yamapnumber].controls, 
                                zoom: ym[yamapnumber].zoom,
                                type: ym[yamapnumber].maptype,
                              
                            },
                            

                            content : contentplacemarks, 

                            };
                            if (ym[mapselector].container!=="") mapArgs.attrs.container=ym[mapselector].container;
                            if (ym[mapselector].scrollzoom==="0") mapArgs.attrs.scrollzoom=ym[mapselector].scrollzoom;
                            if (ym[mapselector].mobiledrag==="0") mapArgs.attrs.mobiledrag=ym[mapselector].mobiledrag;
                            if (ym[mapselector].controls!=="") mapArgs.attrs.controls=ym[yamapnumber].controls;
                            ed.insertContent( wp.shortcode.string( mapArgs ) );

                             

                        }
                    });
                mapdatechange();
                mapSave();


                

                
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

