/** 
 *
 *@author giacomo@you-n.com
 *22/07/13
 */
jQuery.noConflict();
var arrayInfo = new Array;
jQuery(document).ready(function($){
    gestHelper();
    gestOpt();
    gestThumb();
    gestMenuNav();
    $('#YM-color-picker').iris({
        hide: false, 
        palettes: true,
        change: function(event, ui) {
            updateColor();
        }
    });
    // MODULO drag/drop per spessore!!
    updateColor();
    $('#YM-anteprima-spessore').css('border',spessore+'px solid');
    $('#YM-pixel-scelto').html('').append(spessore+'px');
    var leftLoading=0;
    switch(spessore){
        case 4:
            leftLoading=200;
        break;
        case 3:
            leftLoading=150;
        break;
        case 2:
            leftLoading=100;
        break;
        case 1:
            leftLoading=50;
        break;
    }
    $('#YM-spessore-posizione').animate({
        'left':leftLoading
    }, 700);
    function updateColor(){
        var colore= $('#YM-color-picker').attr('value');
        $('#YM-anteprima-spessore').css('border-color',colore);
        
    }    
    
    var old=0;
    $('#YM-spessore-posizione').draggable({
        containment: "parent",
        drag: function() {
            updateWidthExample($(this).position().left,old);
        },
        stop: function(){
            updateWidthExample($(this).position().left,old);
            updateInDB(old);
        }
    });
    
    
    function updateWidthExample(left, old1){
        var scelta=old1;
        if(left>=0 && left<=50){
            scelta=1;
        }else
        if(left>=51 && left<=100 ){
            scelta=2;
        }else
        if(left >=101 && left<=150){
            scelta=3;
        }else
        if(left>= 151 && left <=200){
            scelta=4;
        }else{
            scelta=old1;
        }
        old=scelta;
        //        console.log(scelta)
        $('#YM-anteprima-spessore').css('border',scelta+'px solid');
        
        $('#YM-pixel-scelto').html('').append(scelta+'px');
        updateColor();
           
               
    }
    
    function updateInDB(scelta){
        $.ajax({
            url: urlo+'?action=updateInDBSceltaBordo',
            type:'post',
            data: 'spessore='+scelta,
            success: function(data){
                if(typeof(data)=== 'string') data=JSON.parse(data);
                $('#YM-messaggio-anteprima-spessore').find('.messaggioSpessore').remove();
                $('#YM-messaggio-anteprima-spessore').append('<p class="messaggioSpessore">'+data.message+'</p>');
            }
        });
    }
    
    //end drag/drop
    var lat, lng;
    lt=41.89052;
    lng=12.494249;
    var myOptions = {  
        zoom: 6,
        center: new google.maps.LatLng(lt,lng),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map"), myOptions);
     
    getMyMapDefault();
    $('#getCoords').click(function(e){
        e.preventDefault();
        getMyMapCustom();
    });
    function getMyMapCustom(markers){
        var markers = markers || false;
        var indirizzo= $('#indirizzo').val() + ', '+$('#citta').val()+ ' '+$('#nazione').val();
      
        geo_map = new google.maps.Geocoder();  
        geo_map.geocode( {
            'address': indirizzo
        }, function(results, status) {
            if(results[0].partial_match){
                DialogHelper('Attention, partial result: the marker is positioned at the center of the city');
            }
            if (status == google.maps.GeocoderStatus.OK) {
                var imgMarker='';
                if($('#result2 img').attr('src')!=undefined){
                    imgMarker=$('#result2 img').attr('src');
                }else{
                    imgMarker=$('#srcFoto').attr('src');
                }
                $('#lat').val(results[0]['geometry']['location'].lat()); 
                $('#lgt').val(results[0]['geometry']['location'].lng());   
                dealer = new google.maps.LatLng(results[0]['geometry']['location'].lat(), results[0]['geometry']['location'].lng());
                var my_marker=  new google.maps.Marker({
                    position: dealer, 
                    map: map,
                    icon:imgMarker,
                    draggable: true
                });
                
             
                
                //CONTROLLO!!
                var tooltipContent = $('#tooltip').val();
                if(tooltipContent.length>0){
                    //                    function addMarker(my_marker){
                    //                        arrayInfo[my_marker.id_dealer] =  new google.maps.InfoWindow({
                    //                            content: my_marker.info
                    //                            });		
                    //		
                    //                        dealer = new google.maps.LatLng(my_marker.lat, my_marker.lng);		
                    //                        bounds.extend(dealer);
                    //
                    //                        arrayMarker[my_marker.id_dealer] = new google.maps.Marker({
                    //                            position: dealer, 
                    //                            map: map,
                    //                            title: my_marker.info.replace('<br>',"\n"),
                    //                            icon: markerimage
                    //                        });
                    //		
                    //                        google.maps.event.addListener(arrayMarker[my_marker.id_dealer], 'click', function() {
                    //                            closeAllInfoWindow();																			  
                    //                            arrayInfo[my_marker.id_dealer].open(map,arrayMarker[my_marker.id_dealer]);
                    //                        });
                    //		
                    //		
                    //                    }
                    var coordInfoWindow = new google.maps.InfoWindow({
                        map:map,
                        content: tooltipContent
                    });

                    google.maps.event.addListener(my_marker, 'click', function() {
                        coordInfoWindow.setContent(tooltipContent);
                       
                        coordInfoWindow.open(map, my_marker);
                    });
               
                    function createInfoWindowContent(){
                        
                        return tooltipContent;
                    }
           
                }
                
                //END
                // alert(dealer);
                map.setCenter(dealer, 13);
                map.setZoom(14);
                return 1;
            } else {
                DialogHelper( status+ ' -> Ripeti la ricerca');
                return 0;
            }
        //$('loading').hide();
        });
        
    }
    var marker = new Array();
    var infoWindow = new Array();
    var dealer;
    function getMyMapDefault(){
        if(visualizzaMarker){ //sono già stati inseriti marker
            bounds = new google.maps.LatLngBounds();
            $.each(arrayMarker,function(i,el){
                addMarker(arrayMarker[i],i);
            // console.log(arrayMarker[i]);
            //marker[i]['longitudine']= arrayMarker[i].longitudine;
            //marker[i]['immagine']=arrayMarker[i].immagine;
            // marker[i]['toolTip']=arrayMarker[i].toolTip;
            });
            map.setCenter(dealer, 13);
            map.setZoom(14);
            
        }
        return;
    }
    function addMarker(my_marker,id2){
        var id= parseInt(my_marker.id_marker);
        var latitudine = (my_marker.latitudine);
        var longitudine =(my_marker.longitudine);
        var toolTip = my_marker.toolTip;
        
        arrayInfo[id] =  new google.maps.InfoWindow(toolTip);		
		
        dealer = new google.maps.LatLng(latitudine,longitudine);
        // console.log(dealer);
        bounds.extend(dealer);

        arrayMarker[id] = new google.maps.Marker({
            position: dealer, 
            map: map,
            title:  toolTip,
            icon: my_marker.immagine,
            draggable: true,
            id: id2
        });
        /*
        infoWindow[id] = new 	google.maps.InfoWindow({
            //map:map,
            content:toolTip
        });
        */
        google.maps.event.addListener(arrayMarker[id], 'click', function() {
            closeAllInfoWindow();
            arrayInfo[id].setContent(toolTip);
            arrayInfo[id].open(map,arrayMarker[id]);
        });
        google.maps.event.addListener(arrayMarker[id], 'drag', function() {
//            updateMarkerStatus('Dragging...');
            updateMarkerPosition(arrayMarker[id].getPosition());
        });
 
        google.maps.event.addListener(arrayMarker[id], 'dragend', function() {
//            updateMarkerStatus('Drag ended');
            geocodePosition(arrayMarker[id].getPosition(),id2);
        });

		
    //    console.log(arrayInfo[id])
    }
    function closeAllInfoWindow(){
       
        for(var my_info in arrayInfo){
            arrayInfo[my_info].close();
        }
    }
    
    var validationWidth=false;
    var validationHeight=false;
    function gestOpt(){
        $('#wrapper-is-responsive').fadeOut('slow');
        if($('#YM-same-marker').attr('name')!=undefined)
            $('#YM-wrapper-file-upload').fadeOut('slow');
        $('#YM-opt-'+attivoDisattivo).attr('selected', 'selected');
        $('#YM-opt-'+responsive).attr('selected','selected');
        $('#YM-opt-zoom_'+zoomConfig).attr('selected','selected');
        var tipo=0;
        switch (tipoMappa){
            case 'ROADMAP':
                tipo=1;
                break;
            case 'SATELLITE':
                tipo=2;
                break;
            case 'HYBRID':
                tipo=3;
                break;
            case 'TERRAIN':
                tipo = 4;
                break;
        }
        $('#YM-opt-tipoMappa_'+tipo).attr('selected','selected');
        if($('#YM-opt-responsive-select').attr('value')=='custom')
            $('#wrapper-is-responsive').fadeIn('slow');
        $('#YM-opt-responsive-select').on('change',function(){
            if($(this).attr('value')=='responsive'){
                $('#wrapper-is-responsive').fadeOut('slow');
            }else{
                $('#wrapper-is-responsive').fadeIn('slow');
            } 
        });
        var zoom=0;
        $('#YM-zoom').on('change',function(){
            zoom=parseInt($(this).attr('value'));
            map.setZoom(zoom);
        });
        
        if($('#YM-same-marker').attr('name')!=undefined){
            $('#YM-riuso-marker .YM-wrapper-thumb').click(function(evt){
                evt.preventDefault();
                $('#YM-riuso-marker .YM-wrapper-thumb').removeClass('selected-marker');
                $(this).addClass('selected-marker');
                var srcFotro= $(this).find('img').attr('src');
                var idDaCopiare= $(this).find('img').attr('id');
                idDaCopiare = idDaCopiare.split('_');
                idDaCopiare= idDaCopiare[1];
               
                $.ajax({
                    url: urlo+'?action=createMarkerCopy',
                    type:'post',
                    data: 'nome_marker='+srcFotro+'&id_da_copiare='+idDaCopiare,
                    success: function(data){
                        if(typeof(data)=== 'string') data=JSON.parse(data);
                     
                        $('#srcFoto').attr('value',data.marker);
                    }
                });
            });
            
        }
        
        $('#YM-same-marker').on('change',function(){
            if($(this).attr('value')=='yes'){
                $('#YM-wrapper-file-upload').fadeOut('slow');
                $('#YM-riuso-marker').fadeIn('slow');
            }
            if($(this).attr('value')=='no'){
                $('#YM-wrapper-file-upload').fadeIn('slow');
                $('#YM-riuso-marker').fadeOut('slow');
            }
        });
        
        
        $('#width, #height').focusin(function(){
       
            if(validationWidth){
                $('#width').css({
                    "border":"1px solid red"
                });
                validationWidth=false;
            }
            if(validationHeight){
           
            }
        })
        $('#width, #height').focusout(function(e){ //prevent sul % e altri caratteri NON numerici
            var id=$(this).attr('id');
            if(isNaN($('#width').attr('value'))){
                DialogHelper('E\' possibile inserire solamente caratteri numerici nella configurazione della larghezza!');
                validationWidth= true;
                $(this).css({
                    "border":"1px solid red"
                });
            
            }
            if(isNaN($('#height').attr('value'))){
                DialogHelper('E\' possibile inserire solamente caratteri numerici nella configurazione dell\'altezza!');
                validationHeight= true;
                $(this).css({
                    "border":"1px solid red"
                });
            }
        
            if (e.keyCode == 53) {
                DialogHelper('Non è possibile inserire il carattere %');
            }
        
        });
    }
    function gestThumb(){
       
        $('.YM-wrapper-thumb').find('div').stop().fadeOut('slow');

        $('.YM-wrapper-thumb').hover(
            function(){
                //console.log($(this).find('div').stop().animate({"display":"block"},500))
          
                $(this).find('div').stop().fadeIn('slow');
            },
            function(){
                // console.log('OUT')
                $(this).find('div').stop().fadeOut('slow');
            });
        $('.YM-wrapper-thumb div').hover(
            function(){
                var div = $(this).attr('class');
                var messaggio='';
                var classe='tooltip-up';
                switch (div){
                    case 'area-modifica':
                        messaggio="Clicca per modificare le informazioni del marker";
                        break;
                    case 'area-cancellami':
                        messaggio ="Clicca per eliminare questo marker";
                        break;
                    case 'area-wrap':
                        messaggio="Utilizza i comandi per eliminare o modificare le informazioni";
                        classe='tooltip-down';
                        break;
                }
                $(this).parent().append('<div class="tooltip '+classe+'" style="width:280px; color:#FFF; z-index:5; padding:10px; background:url('+urloModal+'/images/bg_tooltip.png); position:relative; border-radius:10px;">'+messaggio+'<div style="width:15px; height:15px; background:url('+urloModal+'/images/arrow_tooltip.png); top:-15px; left:22px; position:absolute; display:block;"></div></div>');
            },function(){
                $(this).parent().find('.tooltip').remove();
            }
            );
        $('.area-modifica').click(function(evt){
            evt.preventDefault();
            var id_foto=$(this).parent().find('img').attr('id');
            id_foto= id_foto.split('_');
            $.ajax({
                url: urlo+'?action=getInfoMarker',
                type:'post',
                data: 'id_marker='+id_foto[1],
                success: function(data){
                    if(typeof(data)=== 'string') data=JSON.parse(data);
                    if(data.ok==true){
                        mess='<div class="wrapper-modifica">';
                        mess+='<label for="lat">Latitudine:</label><input type="text" name="lat" id="latitudine" value="'+data.latitudine+'" />';
                        mess+='<div class="clear"></div><label for="lgt">Longitudine:</label><input type="text" id="longitudine" name="lgt" value="'+data.longitudine+'" />';
                        mess+='<div class="clear"></div><label for="toolTip">Tool tip:</label><textarea id="toolTip">'+data.toolTip+'</textarea><div class="clear"></div>';
                        mess+='<input id="id_marker" name="id_marker" type="hidden" value="'+id_foto[1]+'" /><input class="YM-button" type="submit" id="YM_update-campi" value="Save changes" />';
                        mess+='</div>';
                        DialogHelper(mess);
                    }
                }
            });
        });
        $('.area-cancellami').click(function(evt){
            evt.preventDefault();
            var id_foto=$(this).parent().find('img').attr('id');
            id_foto= id_foto.split('_');
            $.ajax({
                url: urlo+'?action=getInfoMarker',
                type:'post',
                data: 'id_marker='+id_foto[1]+'&for_delete=1',
                success: function(data){
                    if(typeof(data)=== 'string') data=JSON.parse(data);
                    if(data.ok==true){
                        var markerImg;
                        if(data.marker.length>2){
                            markerImg = data.marker
                        }else{
                            markerImg = urloModal+'/images/default-marker.png' ;
                        }
                        var ltlng=new google.maps.LatLng(data.latitudine,data.longitudine);
                     
                        convertLtLang(ltlng);
                        
                        mess='<div class="wrapper-elimina">';
                        mess+='<div class="clear"></div><span>Delete this item?</span><div class="clear"></div><div id="indirizzoModal"></div><label for="toolTip">Tool tip:</label><textarea id="toolTip" disabled="disabled">'+data.toolTip+'</textarea><div class="clear"></div>';
                        mess+='<div class="clear"></div><img src="'+markerImg+'" title="delete this item?" /><div class="clear"></div>';
                        mess+='<input id="id_marker" name="id_marker" type="hidden" value="'+id_foto[1]+'" /><input class="YM-button" type="submit" id="YM_delete-my-marker" value="Delete Elemnt" />';
                        mess+='</div>';
                        DialogHelper(mess);
                    }
                }
            }); 
        });
    }
    function gestHelper(){
        $('.YM-helper-dialog p').css({
            "display":"none"
        });
        $('.YM-helper-dialog').append('<div class="help-button"></div>');
        $('.help-button').click(function(evt){
            evt.preventDefault();
            var mess= $(this).parent().find('p').html();
            DialogHelper(mess);
        })
    }

    function DialogHelper(mess){
        var str='<div class="wrapper-contenitore-info" style="position:relative!important; padding:20px; border-radius:10px!important;"><a href="#" id="chiudi-modal" style="width:40px; height:40px; display:block; position:absolute!important; right:-20px; top:-20px; background:url('+urloModal+'/images/close-modal.png) center no-repeat; text-indent:-5000px;">X</a><div class="contenuto-modal"><div style="border-radius:10px;">'+mess+'</div></div>';
        $.blockUI({
            css:{
                "border":"none",
                "cursor":"default",
                "border-radius":"10px"
            },
            message: str,
            onBlock:function(){
                $('#chiudi-modal').click(function(evt){
                    evt.preventDefault();
                    $.unblockUI();
                });
                if($('#YM_delete-my-marker')!= undefined){
                    $('#YM_delete-my-marker').click(function(evt){
                       
                        evt.preventDefault();
                        $.ajax({
                            url: urlo+'?action=deleteMyMarker',
                            type:'post',
                            data: 'id_marker='+$('#id_marker').attr('value'),
                            success: function(data){
                                if(typeof(data)=== 'string') data=JSON.parse(data);
                                //svuotare modal, aggiornare mappa
                                $('.wrapper-elimina').append('<div class="update-ok">'+data.message+'</div>');
                                if(data.ok==true){
                                    //aggiornare la mappa!!
                                    getMyNewMap(data.markers);
                                    if(getNewThumbnailConfiguration()){
                                        $.unblockUI();
                                    }else{
                                        $('.wrapper-elimina').find('.update-ok').html('').append('<div class="update-ok">Error in thumb creation, but marker successful deleted. Please refresh the page</div>');
                                    }
                                }
                            }
                        });
                        
                    })
                }
                if($('#YM_update-campi')!=undefined){
                    $('#YM_update-campi').click(function(evt){
                        evt.preventDefault();
                        $.ajax({
                            url: urlo+'?action=updateInfoMarker',
                            type:'post',
                            data: 'id_marker='+$('#id_marker').attr('value')+'&latitudine='+$('#latitudine').attr('value')+'&longitudine='+$('#longitudine').attr('value')+'&toolTip='+$('#toolTip').attr('value'),
                            success: function(data){
                                if(typeof(data)=== 'string') data=JSON.parse(data);
                                //svuotare modal, aggiornare mappa
                                $('.wrapper-modifica').append('<div class="update-ok">'+data.message+'</div>');
                                if(data.ok==true){
                                    //aggiornare la mappa!!
                                    getMyNewMap(data.markers);
                                    
                                }
                            }
                        });
                    });
                }
                if($('#YM-update-Marker-location')!=undefined){
                    $('#YM-update-Marker-location').click(function(evt){
                        evt.preventDefault();
                        $.ajax({
                            url: urlo+'?action=updateMarkerPosition',
                            type:'post',
                            data: 'id_marker='+$('#NMP').attr('value')+'&latitudine='+$('#newLatitude').attr('value')+'&longitudine='+$('#newLongitude').attr('value'),
                            success: function(data){
                                if(typeof(data)=== 'string') data=JSON.parse(data);
                                //svuotare modal, aggiornare mappa
                                $('.contenuto-modal').append('<div class="update-ok">'+data.message+'</div>');
                                if(data.ok==true){
                                    //aggiornare la mappa!!
                                    getMyNewMap(data.markers);
                                    
                                }
                            }
                        });
                    });
                }
                
            }
        });
    }
    
    function getNewThumbnailConfiguration(){
        $('#YM-paginatore-thumb').html('');
        $('#YM-riuso-marker').html('');
        $.ajax({
            url: urlo+'?action=getThumbConfiguration',
            type:'post',
            success: function(data){
                if(typeof(data)=== 'string') data=JSON.parse(data);
            
                if(data.thumbnail=='' & data.riuso==''){
                    $('#YM-paginatore-thumb').html('No marker Found!');
                    $('#YM-riuso-marker').html('');
                    $('#YM-wrapper-same-marker').fadeOut('slow');
                    $('#YM-wrapper-file-upload').fadeIn('slow')
                    $('#YM-same-marker').attr('value','no');
                }
                $('#YM-paginatore-thumb').html(data.thumbnail);
                $('#YM-riuso-marker').html(data.riuso);
                gestThumb();
            }
        });
        return true
    }
    
    function getMyNewMap(markers){
        $.each(markers,function(i,el){
            arrayMarker[i].setMap(null);
            addMarker2(markers[i],i);
        //console.log(arrayMarker[i]);
        })
        
    }
    function addMarker2(my_marker,id){
        //var id= parseInt(my_marker.id_marker);
        var latitudine = (my_marker.lat);
        var longitudine =(my_marker.lgt);
        var toolTip = my_marker.toolTip;
          
        arrayInfo[id] =  new google.maps.InfoWindow(toolTip);		
		
        dealer = new google.maps.LatLng(latitudine,longitudine);
        // console.log(dealer);
        bounds.extend(dealer);

        arrayMarker[id] = new google.maps.Marker({
            position: dealer, 
            map: map,
            title:  toolTip,
            icon: my_marker.marker,
            draggable: true,
            id:id
        });
        /*
        infoWindow[id] = new 	google.maps.InfoWindow({
            //map:map,
            content:toolTip
        });
        */
        google.maps.event.addListener(arrayMarker[id], 'click', function() {
            closeAllInfoWindow();
            arrayInfo[id].setContent(toolTip);
            arrayInfo[id].open(map,arrayMarker[id]);
        });
        google.maps.event.addListener(arrayMarker[id], 'drag', function() {
//            updateMarkerStatus('Dragging...');
            updateMarkerPosition(arrayMarker[id].getPosition());
        });
 
        google.maps.event.addListener(arrayMarker[id], 'dragend', function() {
//            updateMarkerStatus('Drag ended');
            geocodePosition(arrayMarker[id].getPosition(),id);
        });
		
    //    console.log(arrayInfo[id])
    }

    $('#YM-paginatore-thumb .YM-wrapper-thumb .area-wrap').click(function(evt){
        evt.preventDefault();
        var id=$(this).parent().find('img').attr('id');
       
        id= id.split('_');
        $.ajax({
            url: urlo+'?action=getLatLongMarker',
            type:'post',
            data: 'id_marker='+id[1],
            success: function(data){
                if(typeof(data)=== 'string') data=JSON.parse(data);
                var newLatLng = new google.maps.LatLng(parseFloat(data.latitudine),parseFloat(data.longitudine))
                map.setCenter(newLatLng);
                map.setZoom(14);
            }
        });
    });




    function updateMarkerPosition(latLng) {
        document.getElementById('info').innerHTML = [
        latLng.lat(),
        latLng.lng()
        ].join(', ');
    }
    function updateMarkerStatus(str) {
        document.getElementById('markerStatus').innerHTML = str;
    }

    function geocodePosition(pos, idMarker) {
        geo_map = new google.maps.Geocoder();  
        geo_map.geocode({
            latLng: pos
        }, function(responses) {
            if (responses && responses.length > 0) {
                updateMarkerAddress(responses[0].formatted_address,responses[0]['geometry']['location'].lat(),responses[0]['geometry']['location'].lng(),idMarker);
            } else {
                updateMarkerAddress('Cannot determine address at this location.');
            }
        });
    }
 

    function updateMarkerAddress(str, latitudine, longitudine, idMarker) {
        DialogHelper('Spostare questo marker su questo indirizzo: '+str+'?<div class="clear"></div><input type="submit" value="Update position" class="YM-button" id="YM-update-Marker-location" /> <input type="hidden" id="NMP" value="'+idMarker+'" /><input type="hidden" id="newLatitude" value="'+latitudine+'" /><input type="hidden" id="newLongitude" value="'+longitudine+'"/>');
       
    }
    
    var msg;
    function convertLtLang(pos){
        geo_map = new google.maps.Geocoder();  
        geo_map.geocode({
            latLng: pos
        }, function(responses) {
            if (responses && responses.length > 0) {
                ritornaInd(responses[0].formatted_address)
            } else {
                ritornaInd('Cannot determine address at this location.')
            }
        });
        
        function ritornaInd(m){
            $('#indirizzoModal').html(m);
        }
       
        
    }
    
    function gestMenuNav(){
        $('.YS-indice-config li a').click(function(evt){
            evt.preventDefault();
            var cliccato = $(this).attr('id');
            var id = cliccato.substring(4,cliccato.length);
            var offset = $('.goto-'+id).offset().top;
            $("body, html").animate({
                scrollTop : offset+380
            }, 800,function(){
                $('body, html').animate({
                    scrollTop: offset-45
                },400)
            })
        
        })
    }
    
});