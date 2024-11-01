/**
 * @author giacomo@you-n.com
 * 28/08/13
 */

jQuery.noConflict();
var arrayInfo = new Array;
var zoomMappa = parseInt(zoomConfig);
var tipo;
switch (tipoMappa){
    case 'ROADMAP':
        tipo=google.maps.MapTypeId.ROADMAP;
        break;
    case 'SATELLITE':
        tipo=google.maps.MapTypeId.SATELLITE;
        break;
    case 'HYBRID':
        tipo=google.maps.MapTypeId.HYBRID;
        break;
    case 'TERRAIN':
        tipo = google.maps.MapTypeId.TERRAIN;
        break;
}
jQuery(document).ready(function($){
    
    var lat, lng;
    lat=41.89052;
    lng=12.494249;
    var myOptions = {
        zoom: zoomMappa,
        center: new google.maps.LatLng(lat,lng),
        mapTypeId: tipo
    };
    var map = new google.maps.Map(document.getElementById("YM-mappa"), myOptions);
    
    getMyMapDefault();
    
    
    var dealer;
    function getMyMapDefault(){
        if(visualizzaMarker){ //sono già stati inseriti marker
            bounds = new google.maps.LatLngBounds();
            $.each(arrayMarker,function(i,el){
                addMarker(arrayMarker[i],i);
            });
            map.setCenter(dealer, 13);
            map.setZoom(zoomMappa);
            
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
        bounds.extend(dealer);

        arrayMarker[id] = new google.maps.Marker({
            position: dealer, 
            map: map,
            title:  toolTip,
            icon: my_marker.immagine,
            id: id2
        });
       
        google.maps.event.addListener(arrayMarker[id], 'click', function() {
            closeAllInfoWindow();
            arrayInfo[id].setContent(toolTip);
            arrayInfo[id].open(map,arrayMarker[id]);
        });

    }
    
    function closeAllInfoWindow(){
       
        for(var my_info in arrayInfo){
            arrayInfo[my_info].close();
        }
    }
    
    $(window).resize(function(){
        gestResize()
        });

    function gestResize(){
        map.setCenter(dealer, 13);
    }
});