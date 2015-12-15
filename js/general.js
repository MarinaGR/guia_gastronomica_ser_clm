//Global Variables

var now = new Date().getTime();
var id_notificacion=0;

var extern_siteurl="http://ovnyline.es/SER_CLM_GASTRONOMIA/index.html?app=mobile&app_ios=mobile"; 
var extern_siteurl_op="http://ovnyline.es/SER_CLM_GASTRONOMIA/server/functions/api.php";

//Get the screen and viewport size
var viewport_width=$(window).outerWidth();
var viewport_height=$(window).outerHeight();
var screen_width=screen.width;
var screen_height=screen.height; 

$(document).ready(function() {
	$("#contenido").height(parseInt($(window).height())-4+"px");
});

function onBodyLoad()
{	
    document.addEventListener("deviceready", onDeviceReady, false); 
	
	var fecha=getLocalStorage("fecha"); 
	if(typeof fecha == "undefined"  || fecha==null)	
	{	
		var nueva_fecha=now; //new Date(2016,0,1).getTime(); 
		setLocalStorage("fecha", nueva_fecha);
	}
	
	check_internet();			
	
}
function onDeviceReady()
{
	document.addEventListener("offline", onOffline, false);
	document.addEventListener("online", onOnline, false);

	//cordova.plugins.backgroundMode.enable(); 	
	
	document.addEventListener("backbutton", onBackKeyDown, false);
	document.addEventListener("menubutton", onMenuKeyDown, false);
	
}    
function onBackKeyDown()
{
	if($("#contenido").attr("src")=="offline.html") 
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
		$("#contenido").attr("src",extern_siteurl);
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
			/*NOTIFICACIONES
			
			var values="date="+getLocalStorage("fecha");
			ajax_operation_cross(values,"ov_get_notifications");
	
			setTimeout(function(){
				$("#contenido").attr("src",extern_siteurl);	
			},250);
			
			//CADA 6 HORAS
			setInterval(function(){
				var values2="date="+getLocalStorage("fecha");
				ajax_operation_cross(values2,"ov_get_notifications");
			},6*60*60*1000);  //cada minuto: 1min*60seg*1000; cada 24 horas: 24*60*60*1000
			
			*/
		}		
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

/*************************************************************/
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
/*************************************************************/
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