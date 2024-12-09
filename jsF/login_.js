// ________________________________________________________
function ValidaUsuario(){
	cUsuario = document.getElementById("user_login").value;
	cContra	 = document.getElementById("password_login").value;

	//console.log(cUsuario + "-" + cContra );
	llamarPhp("backF/login_.php","vOpc="+"validaLdap"+"&cUsuario="+cUsuario+"&cContra="+cContra);
	/*
	$.ajax({
		type     : "POST",
		url      : "back/login.php",
		dataType : 'JSON',
		data     : { vOpc : "revisaCredenciales" , vUsuario : cUsuario , vContra : cContra },
		cache    : false, 
		async    : false,	// Evita la asincronía
		dataType : 'json',
		success  : function(response) {
		}
	});*/
}
// ________________________________________________________
// Invoca a un archivo PHP, cParametros debe ser cValor1=""
function llamarPhp(cPhp,cParametros){
	// Crear una instancia de XMLHttpRequest
	var xhr = new XMLHttpRequest();
	// Revisa si hay parametros a pasar al PHP
	//if ( !(cParametros===undefined) ){
	//	cPhp = cPhp + "?" + cParametros
	//}
	//console.log(cPhp);
	// Configurar una función de respuesta para manejar la respuesta del servidor
	xhr.onreadystatechange = function () {
	    // Verificar si la solicitud se completó con éxito
	    if (xhr.readyState === 4 && xhr.status === 200) {
	        // Parsear la respuesta JSON en un objeto JavaScript
	        var respuesta = JSON.parse(xhr.responseText);

	        // Acceder a los datos devueltos desde PHP
	        //console.log("respuesta: " + respuesta);
	        if (respuesta.success){// Paso el login 
	        	//llamarPhp("OpeFin00_00.php","");
	        }else{
	    		//alert(respuesta.mensaje);
	    		alert("Credenciales Incorrectas");
	    	}
	    }
	};
	//
	// Configurar la solicitud: método HTTP y URL del archivo o servicio
	xhr.open("POST", cPhp, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	// Enviar la solicitud al servidor
	xhr.send(cParametros);
}