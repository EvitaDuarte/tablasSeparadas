// * * * * * * * * * * * * * * * * * * * * * * * * * 
// * Autor   : Miguel Ángel Bolaños Guillén        *
// * Sistema : Sistema de Operación Bancaria Web   *
// * Fecha   : Septiembre 2023                     *
// * Descripción : Rutinas para realizar el enlace * 
// *               entre JavaScript y PHP          *
// * * * * * * * * * * * * * * * * * * * * * * * * *
var conexion;			// Variable global para la conexión
var una_vez = true;		// Variable global para que algun proceso se ejecute so-lo una vez

// onload se ejecuta cuando se carga el formulario HTML y que tiene incrustado 
// eL script llamando a funcionalidad.js 
window.onload = function () {
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);
	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// __________________________________________
		case "OpeFin01_01Usuarios.php":
			aParametros ={
				opcion 			: "UsuariosConsulta",
				traeEsquemas 	: true
			};
			// Se requiere ver si hay usuarios para cargarlos en una tabla HTML
			// y esquemas para llenar el selectbox
			conectayEjecuta(aParametros);
		break;
		// __________________________________________
		case "OpeFin01_03Configuracion.php":
			aParametros = {
				opcion : "ConfiguracionCarga" // Carga en la pantalla los valores de las variables
			};
			// Hace la conexión y ejecuta el php solicitado
			conectayEjecuta(aParametros);
		break;
		// __________________________________________
		case "OpeFin01_04Accesos.php":
			aParametros = {
				opcion 			: "AccesosConsulta", // Carga en la tabla HTML los registros de la tabla accesos
				traeCatalogos 	: true
			};
			// Hace la conexión y ejecuta el php solicitado
			conectayEjecuta(aParametros);
			//conectayEjecutaPost(aParametros,"funcionalidad_.php",null)
		break;
		// __________________________________________
		// __________________________________________
	}
};
// * * * * * * * * * * * * * * * * * * *
// * Realizar el enlace entre JS y PHP *
// * * * * * * * * * * * * * * * * * * *
// ______________________________________________________________________________________________________________________________________
function conectayEjecuta(aParametros){
	conexion 					= new XMLHttpRequest();					// Prepara conexión http
	conexion.onreadystatechange = respuesta;							// La función JS que se invocara al terminar de ejecutar el php
	aParametros					= JSON.stringify(aParametros);			// Convierte los datos a JSON
	// En el php realizara las operaciones solicitadas (Consultar,Adicionar,Modificar,Borrar)
	// De acuerdo a los parámetros contenidos en aParametros
	// En aParametros.opcion funcionalidad_.php sabra que instrucciones ejecutara
	conexion.open('PUT','backF/funcionalidad_.php?aDatos='+aParametros,true);	// Prepara llamada al archivo PHP
	conexion.send();														// Envía datos al servidor
}
// ______________________________________________________________________________________________________________________________________
// ______________________________________________________________________________________________________________________________________
function respuesta(){  					// Respuesta del servidor, de ConectayEjecuta
	if (conexion.readyState==4){		// Indica si llego respuesta del servidor
		//con sole.log(JSON.parse(conexion.responseText));
		vRespuesta = JSON.parse(conexion.responseText);
		if (vRespuesta.success==true){		// EL Servidor ejecuto exitosamente la operación Solicitada
			cOpcion = vRespuesta.opcion;
			// actualiza información en el cliente
			procesarRespuesta(vRespuesta); 
			setTimeout(function () { 							// Espera que se termine procesarRespuesta
    			if (vRespuesta.mensaje.trim() !== "") {				// Y ya despues lanza el ale rt
        			mandaMensaje("["+vRespuesta.mensaje+"]");
    			}
			}, 0);
		}else{													// Se detectaron inconsistencias
			if (vRespuesta.opcion=="validaLdap"){
				document.getElementById("nombre").value		= "";
				document.getElementById("idUnidad").value	= "";
			}
			mandaMensaje("Inconsistencia ["+vRespuesta.mensaje+"]");
		}
	}else{
		//vRespuesta.innerHTML  =" espere por favor";
	}
}
// ______________________________________________
//async function procesarRespuesta__(vRespuesta) {
async function procesarRespuesta(vRespuesta) {				// Define una promesa para esta función

    switch(vRespuesta.opcion) {
// 		------------------------------------  
		case "ConfiguracionCarga":
			await ConfiguracionCarga(vRespuesta.resultados);
		break;
// 		------------------------------------ 
		case "AccesosConsulta":
			await AccesosTabla(vRespuesta.resultados,vRespuesta.combo,vRespuesta.combo1);
		break;
		case "AccesosAgrega": 								// Despues de agregar el nuevo acceso, refrescar la tabla html
			await AccesosTabla(vRespuesta.resultados,vRespuesta.combo,vRespuesta.combo1);
		break;
		case "AccesosBusca": // Actualiza la Tabla HTML con los filros de Búsqueda
			await AccesosTabla(vRespuesta.resultados,vRespuesta.combo,vRespuesta.combo1);
		break;
		case "AccesosElimina": // Actualiza la Tabla HTML después de haber eliminado un acceso
			await AccesosTabla(vRespuesta.resultados,vRespuesta.combo,vRespuesta.combo1);
		break;
// 		------------------------------------ 
    }
}

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
// * Funciones relacionadas a OpeFin01_03Configuracion.php *
// * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
function ConfiguracionCarga(aConfigura){ // Actualizo los valores HTML con los que esten en la tabla configuración
	aConfigura.forEach(function(elemento) {
		//con sole.log(elemento);
		if (elemento["idconfiguracion"]=="01"){
			document.getElementById("anioMovimiento").value = elemento["valor"];
		}else if (elemento["idconfiguracion"]=="02"){
			document.getElementById("anioReintegro").value = elemento["valor"];
		}else if (elemento["idconfiguracion"]=="10"){
			document.getElementById("deptoRecibo").value = elemento["descripcion"];
			document.getElementById("firmaRecibo").value = elemento["valor"];
		}
	});
}
// ______________________________________________
function ConfiguracionActualizar(){
	var aConfigura = crearDatosConfigura("ConfiguracionActualizar"); // Datos capturados del Usuario 	
	if ( ConfiguraValidaDatos(aConfigura)){							 // Valida los datos capturados
		conectayEjecuta(aConfigura);								 // Manda a ejecutar la Operación en la B.D.
	}
}
// ______________________________________________
function crearDatosConfigura(cOpcion){
	return {
    	opcion 		: cOpcion,
	    anioMov01 	: document.getElementById("anioMovimiento").value.trim(),
	    anioRei02	: document.getElementById("anioReintegro").value.trim(),
	    deptoRecibo : document.getElementById("deptoRecibo").value.trim(),
	    firmaRecibo : document.getElementById("firmaRecibo").value.trim(),
  	};
}
// ______________________________________________
function ConfiguraValidaDatos(aConfigura){
	// 	if ( soloNumeros(aConfigura.anioMov01,"Año Captura","anioMovimiento") ){
	//  if ( soloNumeros(aConfigura.anioRei02,"Año Reintegro","anioReintegro") ){
	if (tieneValor(aConfigura.anioMov01,"Año Captura","anioMovimiento")){
		if (tieneValor(aConfigura.anioRei02,"Año Reintegro","anioReintegro")){
			if (tieneValor(aConfigura.deptoRecibo,"Departamento","deptoRecibo")){
				if (tieneValor(aConfigura.firmaRecibo,"Firma Recibo","firmaRecibo")){
					if (anioValido(aConfigura.anioMov01) && anioValido(aConfigura.anioRei02)){
						return true;
					}else{
						mandaMensaje("Los años no pueden ser mayores al año actual o anteriores a 5 años");
					}
				}
			}
		}
	}
	return false
}
// ______________________________________________
/* * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Funciones relacionadas a OpeFin01_04Accesos.php  *
 * * * * * * * * * * * * * * * * * * * * * * * * * * */
// ______________________________________________
function AccesosAgrega(){
//  Llena oAccesos con la opción que se ejecutara en PHP y los datos de captura 	
	var oAccesos = crearDatosAccesos("AccesosAgrega"); 	// Datos capturados del Acceso 	
	if ( AccesosValidaDatos(oAccesos)){					// Verifica que esten completos
		conectayEjecuta(oAccesos);						// Ejecuta la operación de Agregar Acceso
	}
}
// ______________________________________________
function AccesosBusca(){
	var oAccesos = crearDatosAccesos("AccesosBusca"); 	// Datos capturados del Acceso 
	//if ( oAccesos.idCuentaBancaria ==="" && oAccesos.idUsuario==="" ){
	//	ale rt("Se requiere un parámetro de Búsqueda Cuenta Bancaria o Usuario");
	//}else{
		conectayEjecuta(oAccesos);
	//}
}
// ______________________________________________
function AccesosElimina(){
//  Llena oAccesos con la opción que se ejecutara en PHP y los datos de captura 	
	var oAccesos = crearDatosAccesos("AccesosElimina"); 	// Datos capturados del Acceso 	
	if ( AccesosValidaDatos(oAccesos)){					// Verifica que esten completos
		esperaRespuesta(`Desea eliminar el acceso : ${oAccesos.idCuentaBancaria}-${oAccesos.idUsuario} `).then((respuesta) => {
			if (respuesta) {
				conectayEjecuta(oAccesos);						// Ejecuta la operación de Agregar Acceso
			}
		});
	}
}
// ______________________________________________
function crearDatosAccesos(cOpcion){
	return {
    	opcion 				: cOpcion,
	    idCuentaBancaria 	: document.getElementById("idCuentaBancaria").value.trim(),
	    idUsuario			: document.getElementById("idUsuario").value.trim()
  	};
}
// ______________________________________________
function AccesosValidaDatos(oAccesos){
	// Como vienen de Catálogo, so-lo es necesario validar que no esten vacíos
	var regreso = false;
	if ( oAccesos.idCuentaBancaria !="" ){
		if (oAccesos.idUsuario!=""){
			regreso = true;
		}else{
			mandaMensaje("Se requiere seleccionar Usuario");
		}
	}else{
		mandaMensaje("Se requiere seleccionar Cuenta Bancaria");
	}
	return regreso;
}
// ______________________________________________
// ______________________________________________
// ______________________________________________
// ______________________________________________
// ______________________________________________
function AccesosTabla(aRen,aCombo,aCombo1){// aRen contiene todos los elementos que regreso el select a esquemas
	// Se obtiene un apuntador a el cuerpo de la tabla HTML
	var table = document.getElementById("accesos").getElementsByTagName('tbody')[0];

	limpiaTabla(table)

	aRen.forEach(function(item) {
  		var row = table.insertRow(-1); // Inserta una nueva fila al final de la tabla.

  		// Supongamos que item es un objeto con propiedades correspondientes a cada columna.
  		var cell0 = row.insertCell(0); // idcuentabancaria 
  		var cell1 = row.insertCell(1); // nombre
  		var cell2 = row.insertCell(2); // idusuario
  		var cell3 = row.insertCell(3); // usuario que capturo información
  		var cell4 = row.insertCell(4); // Fecha de el alta

  		// Asigna los valores de las propiedades a las celdas.
  			cell0.innerHTML = item.idcuentabancaria;
  			cell1.innerHTML = item.nombre;
  			cell2.innerHTML = item.idusuario;
  			cell3.innerHTML = item.usuarioalta;
  			cell4.innerHTML = item.fechaalta;
	});

	// Asigna escucha click a la tabla
	if (1==1){ // So-lo lo haga una vez
		// Apuntador a la tabla HTML de esquemas
		const tabla = document.getElementById("accesos");
		// Obtén todos los renglones de la tabla.
		const renglones = tabla.getElementsByTagName("tr");

		// Agrega un evento "click" a cada  renglón.
		for (let i = 0; i < renglones.length; i++) {
			renglones[i].addEventListener("click", function() {
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
			    document.getElementById("idCuentaBancaria").value 	= this.cells[0].textContent;
			    document.getElementById("idUsuario").value 	 		= this.cells[2].textContent;
		  	});
		}
		//una_vez = false;
	}
	if (una_vez){
		// Obtén una referencia al elemento select
		var select = document.getElementById("idCuentaBancaria");
		llenaCombo(select,aCombo);
		select = document.getElementById("idUsuario");
		llenaCombo(select,aCombo1);
		una_vez = false;
	}

}
// ______________________________________________
// ______________________________________________
// ______________________________________________