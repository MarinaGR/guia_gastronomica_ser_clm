//Global Variables

var now = new Date().getTime();

//OJO CAMBIAR ESTO Y CONFIG
var extern_siteurl_notif="http://ovnyline.es/SER_CLM_GASTRONOMIA_PRUEBAS/"; 
var extern_siteurl=extern_siteurl_notif+"index.html?app=mobile&app_ios=mobile&flag="+now; 
var extern_siteurl_op=extern_siteurl_notif+"server/functions/api.php";

//Get the screen and viewport size
var viewport_width=$(window).outerWidth();
var viewport_height=$(window).outerHeight();
var screen_width=screen.width;
var screen_height=screen.height; 
var start_session;

var senderID="87250675213";
var id_notificacion=0;
var pushNotification;

var uuid;

$(document).ready(function() {
	$("#contenido").height(parseInt($(window).height())-4+"px");
});

function onBodyLoad()
{	
    document.addEventListener("deviceready", onDeviceReady, false); 
	
	alert(getLocalStorage("fecha"));
	var fecha=getLocalStorage("fecha"); 
	if(typeof fecha == "undefined"  || fecha==null)	
	{	
		setLocalStorage("fecha", now); 
	}
}

function onDeviceReady()
{
	//RECOGER device.uuid para las valoraciones
	uuid=device.uuid;
	setLocalStorage("uuid", uuid);
	
	document.addEventListener("offline", onOffline, false);
	document.addEventListener("online", onOnline, false);
	
	document.addEventListener("backbutton", onBackKeyDown, false);
	document.addEventListener("menubutton", onMenuKeyDown, false);
		
	var start_session=getSessionStorage("start_session"); 
	if(typeof start_session == "undefined" || start_session==null)	
	{	
		var nueva_fecha=parseInt(getLocalStorage("fecha"))+1000*60*3; //60*60*24*5  
		
		alert(now);
		alert(nueva_fecha);
		
		if(now>nueva_fecha) //cada 5 días limpia cache
		{
			window.cache.clear(function(status) {}, function(status) {});
			setLocalStorage("fecha", now);
			alert("limpio cache")
		}
		getSessionStorage("start_session", "inicio");
	}
		
	/* *********************************************************************** */
	/* Comentar desde INICIO TEST NOTIFICACIONES hasta FIN TEST NOTIFICACIONES */
	/* para no realizar el registro del dispositivo	al inicio		 		   */
	/* *********************************************************************** */
	
	// INICIO TEST NOTIFICACIONES	
	var opcion_notif=getLocalStorage("notificacion");	
	var first_exec=getSessionStorage("first_time");
	
	//var myIframe=document.getElementById('contenido');
	//if((myIframe.contentWindow.document.location.href).indexOf("menu.html")!=-1)
	{
		if(typeof opcion_notif == "undefined" || opcion_notif==null || opcion_notif=="si")
		{
			if(typeof first_exec == "undefined" || first_exec==null)
			{
				setSessionStorage("first_time","yes");
				register_notif();
			}
		}
	}
	// FIN TEST NOTIFICACIONES	
	
	//cordova.plugins.notification.local.on("click", function (notification, state) {
	window.plugin.notification.local.onclick = function (notification, state, json) {
	
		 var datos=$.parseJSON(notification.data);
 	 
		 var tipo=(notification.title).split(/\[(.*?)\]/);
		 
		 alert("tipo "+tipo);
		 
		 switch(tipo[1])
		 {
			case "noticia":$("#contenido").attr("src",extern_siteurl_notif+"capitalidad_2016.html?app=mobile&app_ios=mobile&flag="+now);
							break;
							
			case "evento":  
			default:
							$("#contenido").attr("src",extern_siteurl_notif+"calendario.html?app=mobile&app_ios=mobile&flag="+now);
							break;
		 }
		 
	};
	//},this);	
	
	check_internet();			
	
}

function register_notif()
{
	try 
	{ 		
		pushNotification = window.plugins.pushNotification;
		//$("body").append('<br>Registrando ' + device.platform);
		if (device.platform == 'android' || device.platform == 'Android' || device.platform == 'amazon-fireos' ) 
		{
			pushNotification.register(successHandler, errorHandler, {"senderID":senderID, "ecb":"onNotification"});			
		} 
		else
		{	
			pushNotification.register(tokenHandler, errorHandler, 
				{"badge":"true",
				"sound":"true",
				"alert":"true",
				"ecb":"onNotificationAPN"}
			);	
		}
	}
	catch(err) 
	{ 
		//$("body").append("<br>Error registro notif: " + err.message); 
	} 
}

function unregister_notif()
{
	window.plugins.pushNotification.unregister(function() {
			//notificar al usuario con un mensaje
			window.sessionStorage.clear();
			
			/*
			 $.ajax({
				type: "POST",
				url: extern_siteurl_op,
				data: { v: [['id', registrationId], ['uuid', getLocalStorage('uuid')], ['activo', '0']], op: 'pushandroid' },
				dataType: 'json',
				crossDomain: true, 
				success: function() {      
							//$("body").append('<br>Unregister');	    	
							setSessionStorage("regID", registrationId);	
							setLocalStorage("notificacion","no");					
						},
				error: function(jqXHR) {
							if(jqXHR.status == 200) {
								$("body").append('<br>Listo para notificaciones');	

								//notificar al usuario con un mensaje						
								setSessionStorage("regID", registrationId);
								setLocalStorage("notificacion","si");				
							}	
							else if(jqXHR.status == 500) {
								$("body").append('<br>El dispositivo no se pudo registrar para recibir notificaciones.');
							}
							else {
								$("body").append('<br>El dispositivo no se pudo registrar para recibir notificaciones. Err.'+jqXHR.status);
							}						
						}
				
			});
			*/
	});
}

function config_notifications(check) {
	
	switch(check)
	{
		default:
		case "si": 	$("#"+check).val("si");
					if(getLocalStorage("notificacion")!="si")
					{
						setLocalStorage("notificacion","si");
						register_notif();
					}
					break;
					
		case "no":  $("#"+check).val("no");
					if(getLocalStorage("notificacion")!="no")
					{
						setLocalStorage("notificacion","no");
						unregister_notif();
					}
					break;
	}
	 
}

// Notificacion para iOS
function onNotificationAPN(e) {
	if (e.alert) {
		 //$("body").append('<br>Notificaci&oacute;n: ' + e.alert);
		 // Alert (requiere plugin org.apache.cordova.dialogs)
		 navigator.notification.alert(e.alert);
	}
		
	if (e.sound) {
		// Sonido (requiere plugin org.apache.cordova.media)
		var snd = new Media(e.sound);
		snd.play();
	}
	
	if (e.badge) {
		pushNotification.setApplicationIconBadgeNumber(successHandler, e.badge);
	}
}
// GCM notificacion para Android
function onNotification(e) {

	switch( e.event )
	{
		case 'registered':
					if (e.regid.length > 0)
					{
						//$("body").append('<br>Registrado REGID:' + e.regid);
						registerOnServer(e.regid);
					}
					break;
		
		case 'message':
		
					var notif=e.payload;
		
					// Foreground: Notificación en línea, mientras estamos en la aplicación
					if (e.foreground)
					{
  
						// on Android soundname is outside the payload. 
						// On Amazon FireOS all custom attributes are contained within payload
						// var soundfile = e.soundname || e.payload.sound;
						// if the notification contains a soundname, play it.
						// playing a sound also requires the org.apache.cordova.media plugin
						// var my_media = new Media("/android_asset/www/"+ soundfile);
						// my_media.play();
						
						//OPCIÓN: Generamos una notificación en la barra
						
						/*var date_notif=notif.date;
						if(date_notif!="" && date_notif!=null)
							date_notif=new Date();*/
						
						//if(notif.notId!="")
						//	id_notificacion=notif.notId;		
						
						alert("tipo2 "+notif.tipo);
						
						window.plugin.notification.local.add({
							id:      id_notificacion,
							//date:    date_notif, 
							title:   "["+notif.tipo+"] "+notif.title,
							message: notif.message,
							data:	 notif.data,
							ongoing:    true,
							autoCancel: true
						});		

						id_notificacion++;						
											
					}
					else
					{	
						// e.coldstart: Usuario toca notificación en la barra de notificaciones
						// Coldstart y background: Enviamos a la página requerida
						
						alert("tipo3 "+notif.tipo);
						
						switch(notif.tipo)
						{
							case "noticia": $("#contenido").attr("src",extern_siteurl_notif+"capitalidad_2016.html?app=mobile&app_ios=mobile&flag="+now);
											break;
							case "evento":   
							default:
											$("#contenido").attr("src",extern_siteurl_notif+"calendario.html?app=mobile&app_ios=mobile&flag="+now);
											break;
						}
						
					}					
					break;
		
		case 'error':
					//$("body").append('<br>Error:'+ e.msg);
					break;
		
		default:
					//$("body").append('<br>Evento desconocido');
					break;
	}
}

function registerOnServer(registrationId) {

	//var api_key=getLocalStorage("api-key");
	//var mail=getLocalStorage("user_session");

    $.ajax({
        type: "POST",
        url: extern_siteurl_op,
		data: { v: [['id', registrationId], ['uuid', getLocalStorage('uuid')], ['activo', '1']], op: 'pushandroid' },
		/*headers: {
				'Authorization': 'Basic ' + utf8_to_b64(mail+":"+api_key),
				'X-ApiKey':'d2a3771d-f2f3-4fc7-9f9f-8ad7697c81dc'
			},*/
		dataType: 'json',
		crossDomain: true, 
        success: function() {      
					//$("body").append('<br>Listo para notificaciones');	    	
					setSessionStorage("regID", registrationId);	
					setLocalStorage("notificacion","si");					
				},
        error: function(jqXHR) {
					if(jqXHR.status == 200) {
						//$("body").append('<br>Listo para notificaciones');	

						//notificar al usuario con un mensaje						
						setSessionStorage("regID", registrationId);
						setLocalStorage("notificacion","si");				
					}	
					else if(jqXHR.status == 500) {
						$("body").append('<br>El dispositivo no se pudo registrar para recibir notificaciones.');
					}
					else {
						$("body").append('<br>El dispositivo no se pudo registrar para recibir notificaciones. Err.'+jqXHR.status);
					}						
				}
		
    });
}

function registerOnServerIOS(registrationId) {

	//var api_key=getLocalStorage("api-key");
	//var mail=getLocalStorage("user_session");

    $.ajax({
        type: "POST",
        url: extern_siteurl_op,
		data: { v: [['id', registrationId], ['uuid', getLocalStorage('uuid')], ['activo', '1']], op: 'pushandroid' },
		/*headers: {
				'Authorization': 'Basic ' + utf8_to_b64(mail+":"+api_key),
				'X-ApiKey':'d2a3771d-f2f3-4fc7-9f9f-8ad7697c81dc'
			},*/
		dataType: 'json',
		crossDomain: true, 
        success: function() {          	
					setSessionStorage("regID", registrationId);			
					setLocalStorage("notificacion","si");							
				},
        error: function(jqXHR) {
					if(jqXHR.status == 200) {
						//$("body").append('<br>Listo para notificaciones');	

						//notificar al usuario con un mensaje						
						setSessionStorage("regID", registrationId);
						setLocalStorage("notificacion","si");			
					}	
					if(jqXHR.status == 500) {
						//$("body").append('<br>El dispositivo no se pudo registrar para recibir notificaciones.');
					}	
				}
		
    });
}
function tokenHandler (result) {
	//$("body").append('<br>Listo para notificaciones');
	registerOnServerIOS(result);
}

function successHandler (result) {
	//$("body").append('Exito: '+result);
}

function errorHandler (error) {
	//$("body").append('Error: '+error);
} 
//FIN NOTIFICACIONES
    
function onBackKeyDown()
{
	var myIframe=document.getElementById('contenido');
	if((myIframe.contentWindow.document.location.href).indexOf("menu.html")!=-1 || ($("#contenido").attr("src")).indexOf("offline.html")!=-1)
	{		
		navigator.app.exitApp();
		return false;
	}
	window.history.back();
}
function onMenuKeyDown()
{
	//window.location.href='index.html';
}
function onOnline()
{
	setTimeout(function(){
		$("#contenido").attr("src",extern_siteurl+"&devid="+getLocalStorage("uuid"));
	},250);
	
	/*var networkState = navigator.connection.type;

    var states = {};
    states[Connection.UNKNOWN]  = 'Unknown connection';
    states[Connection.ETHERNET] = 'Ethernet connection';
    states[Connection.WIFI]     = 'WiFi connection';
    states[Connection.CELL_2G]  = 'Cell 2G connection';
    states[Connection.CELL_3G]  = 'Cell 3G connection';
    states[Connection.CELL_4G]  = 'Cell 4G connection';
    states[Connection.CELL]     = 'Cell generic connection';
    states[Connection.NONE]     = 'No network connection';

    alert('Conexión: ' + states[networkState]);*/

}
function onOffline()
{
	setTimeout(function(){
		$("#contenido").attr("src","offline.html");
	},250);

}

function check_internet(){

	var isOffline = 'onLine' in navigator && !navigator.onLine;

	if(isOffline) 
	{		
		setTimeout(function(){
			$("#contenido").attr("src","offline.html");				
		},250);
	}
	else 
	{
		if(typeof $("#contenido").attr("src") == "undefined")
		{			
			setTimeout(function(){
				$("#contenido").attr("src",extern_siteurl+"&devid="+getLocalStorage("uuid"));
			},250);
			
			/*NOTIFICACIONES
			
			var values="date="+getLocalStorage("fecha");
			ajax_operation_cross(values,"ov_get_notifications");
			
			//CADA 6 HORAS
			setInterval(function(){
				var values2="date="+getLocalStorage("fecha");
				ajax_operation_cross(values2,"ov_get_notifications");
			},6*60*60*1000);  //cada minuto: 1min*60seg*1000; cada 24 horas: 24*60*60*1000
			
			*/
		}		
	}

}

function get_var_url(variable){

	var tipo=typeof variable;
	var direccion=location.href;
	var posicion=direccion.indexOf("?");
	
	posicion=direccion.indexOf(variable,posicion) + variable.length; 
	
	if (direccion.charAt(posicion)== "=")
	{ 
        var fin=direccion.indexOf("&",posicion); 
        if(fin==-1)
        	fin=direccion.length;
        	
        return direccion.substring(posicion+1, fin); 
    } 
	else
		return false;
	
}

function setLocalStorage(keyinput,valinput) 
{
	if(typeof(window.localStorage) != 'undefined') { 
		window.localStorage.setItem(keyinput,valinput); 
	} 
	else { 
		alert("localStorage no definido"); 
	}
}
function getLocalStorage(keyoutput)
{
	if(typeof(window.localStorage) != 'undefined') { 
		return window.localStorage.getItem(keyoutput); 
	} 
	else { 
		alert("localStorage no definido"); 
	}
}
function setSessionStorage(keyinput,valinput)
{
	if(typeof(window.sessionStorage) != 'undefined') { 
		window.sessionStorage.setItem(keyinput,valinput); 
	} 
	else { 
		alert("sessionStorage no definido"); 
	}
}
function getSessionStorage(keyoutput)
{
	if(typeof(window.sessionStorage) != 'undefined') { 
		return window.sessionStorage.getItem(keyoutput); 
	} 
	else { 
		alert("sessionStorage no definido"); 
	}
}

/*************************************************************/
// SIN USO ACTUALMENTE 
function show_close_app()
{
	if (device.platform == 'android' || device.platform == 'Android' || device.platform == 'amazon-fireos' ) 
	{
		setTimeout(function(){	
			var myIframe=document.getElementById('contenido'); 
			
			//alert(myIframe.contentWindow.document.location.href);
			
			if((myIframe.contentWindow.document.location.href).indexOf("menu.html")!=-1 || ($("#contenido").attr("src")).indexOf("offline.html")!=-1)
			{
				$('#boton_cierre').html("<div style='width:100%;margin:auto;text-align:right;color:#f6f6f6;background:#01448A;' onclick='navigator.app.exitApp();'><i class='fa fa-times fa-2' style='padding:5px 25px;margin:auto'> </i></div>");
			}
			
		},500);	
	}
}

function show_notification(msg)
{
	/*window.plugin.notification.local.add({
		id:         String,  // A unique id of the notifiction
		date:       Date,    // This expects a date object
		message:    String,  // The message that is displayed
		title:      String,  // The title of the message
		repeat:     String,  // Either 'secondly', 'minutely', 'hourly', 'daily', 'weekly', 'monthly' or 'yearly'
		badge:      Number,  // Displays number badge to notification
		sound:      String,  // A sound to be played
		json:       String,  // Data to be passed through the notification
		autoCancel: Boolean, // Setting this flag and the notification is automatically canceled when the user clicks it
		ongoing:    Boolean, // Prevent clearing of notification (Android only)
	});*/
	
	var f_last_update=new Date(parseInt(getLocalStorage("fecha")));
	var mensaje='';//+f_last_update.toString();
	
	now=new Date().getTime();
	var _10_seconds_from_now = new Date(now + 10*1000);
	setLocalStorage("fecha", now);
	
	if(msg[0]["ov_events"]>0)
	{
		mensaje+=''+msg[0]["ov_events"]+' EVENTOS\r\n';
		
		window.plugin.notification.local.add({
			id:      id_notificacion,
			date:    _10_seconds_from_now, 
			title:   'NOVEDADES GUIA GASTRONÓMICA SER CLM',
			message: mensaje,
			autoCancel: true
		});
		
	}
	
}

function ajax_operation(values,operation)
{
	var retorno=false;		
	$.ajax({
	  type: 'POST',
	  url: extern_siteurl_op,
	  data: { v: values, o: operation },
	  success: h_proccess,
	  error:h_error,
	  dataType: "json",
	  async:false
	});			
	function h_proccess(data){
		
		if(data.error=="0")
		{			
			if(data.warning!="0")
			{
				alert(data.warning);
			}
			retorno=data.result;
		}
		else
		{
			//alert(data.error+" - "+data.error_message); // uncomment to trace errors
			retorno=false;
		}				
	}
	function h_error(jqXHR, textStatus, errorThrown)
	{
		console.log(errorThrown);
		retorno=false;		
	}					
	return retorno;
}
function ajax_operation_cross(values,operation)
{
	var retorno=false;		
	$.ajax({
	  type: 'POST',
	  url: extern_siteurl_op,
	  data: { v: values, o: operation },
	  beforeSend: function( xhr ) {
	    xhr.overrideMimeType("text/javascript");
	  },
	  success: h_proccess_p,
	  error:function(jqXHR, textStatus, errorThrown){
            console.log(jqXHR.responseText);
            console.log(errorThrown);
            //alert(jqXHR.responseText+" - "+errorThrown);
            retorno=false;
         },
	  dataType: "jsonp",
      jsonp: 'callback',
      jsonpCallback: 'jsonpCallback',
	  async:false
	});		
	function jsonpCallback(data){
        console.log(data);
        retorno=data.result;
    }	
	function h_proccess_p(data){

		//console.log(data);

		if(data.error=="0")
		{			
			if(data.warning!="0")
			{
				alert(data.warning);
			}
			retorno=data.result;
			
			id_notificacion++;
			show_notification(retorno);
		}
		else
		{
			//alert(data.error+" - "+data.error_message); // uncomment to trace errors
			retorno=false;
		}			
	}	
	return retorno;					
}

/*************************************************************/