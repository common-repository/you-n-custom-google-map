$=jQuery;
$('#TitleFoto').attr('disabled','disabled')
$('#AltFoto').attr('disabled','disabled')
$('#YS-update-foto').attr('disabled','disabled').addClass('YS-button-disabled');
$('#YS-delete-foto').attr('disabled','disabled').addClass('YS-button-disabled');
$('#YS-delete-foto').fadeOut('slow');
var uploader = new qq.FileUploader({
    element: document.getElementById('file-upload'),
    action: urlo+'?action=uploadYMMarker',
    allowedExtensions: ["png"],
    sizeLimit: 1024*1024*2.5,
    multiple: false,
    showMessage: function(message){
        $('#risposta').html('');
        $('#risposta').html(message.path);
    },
    onComplete: function(id, fileName, responseJSON){
        var r = responseJSON;
        var message ='<span class="save-order-ok">Marker caricato correttamente</span>';
            
        if(r.success){
            $('#TitleFoto').removeAttr('disabled');
            $('#AltFoto').removeAttr('disabled');
            $('#YS-update-foto').removeAttr('disabled').removeClass('YS-button-disabled');
            $('#YS-delete-foto').removeAttr('disabled').removeClass('YS-button-disabled').fadeIn('slow');
            $('.qq-upload-button input').attr('disabled','disabled');
            $('.qq-upload-button').addClass('qq-upload-button-disabled');
            
            $('#fileup').val(r.file);

            $('#result').html('');

            $('.qq-upload-list').html('');

            $('#risposta').html('');
            $('#risposta').html(message);
            $('#srcFoto').attr('value',r.newImg);
            $('#NomeFoto').attr('value',r.file);
            $('<img/>').attr('src',r.newImg).css('display','none').appendTo($('#result2')).fadeIn();
        }

        else {

            $('#risposta').html('');
            var mess='<span class="errore-save-order">Upload del file fallita, ti preghiamo di riprovare</span>';
            $('#risposta').html(mess);

        }
    }

});
    
function getBaseURL() {
    var url = location.href;  // entire url including querystring - also: window.location.href;
    var baseURL = url.substring(0, url.indexOf('/', 14));


    if (baseURL.indexOf(location.href) != -1) {
        // Base Url for localhost
        var url = location.href;  // window.location.href;
 
        var pathname = location.pathname;  // window.location.pathname;
        var index1 = url.indexOf(pathname);
        var index2 = url.indexOf("/", index1 + 1);
        var baseLocalUrl = url.substr(0, index2);

        return baseLocalUrl + "/";
    }
    else {
        // Root Url for domain name
        return baseURL + "/";
    }

}