/*
 * * * * * * * * * * * * * * * * * * * * * * * * * 
 * Autor   : Miguel Ángel Bolaños Guillén        *
 * Sistema : Sistema de Operación Bancaria Web   *
 * Fecha   : Septiembre 2023                     *
 * Descripción : Rutinas para realizar el enlace * 
 *              entre JavaScript y PHP           *
 *              Unadm-Proyecto Terminal para INE *
 * * * * * * * * * * * * * * * * * * * * * * * * *
*/

var una_vez 	= true;				// Variable global para que algun proceso se ejecute solo una vez
var cPhp    	= "Catalogos_.php";	// En este php estarán las funciones que se infocaran desde JS
var lRequerido 	= true;				// Se requiere valor para campo antes de mandarlo a la B.D.

// onload se ejecuta cuando se carga el formulario HTML y que tiene incrustado 
// eL script llamando a Catalogos.js 
window.onload = function () {
	// Se obtiene el nombre del archivo que lo invoca
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);

	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// ______________________________________
		case "OpeFin02_01CuentasBancarias.php":
			aDatos = {
				opcion 	: "CargaCuentasBancarias"
			};
			conectayEjecuta__(aDatos,cPhp); 
		break;
		// ________________________________________
		case "OpeFin02_02OperacionesBancarias.php":
			aDatos = {
				opcion 	: "ConsultaOperacionesBancarias"
			};
			conectayEjecuta__(aDatos,cPhp); 
		break;
		// ________________________________________
		case "OpeFin02_03ControlesBancarios.php":
			paginaControlBancario("0");
		break;
		// ________________________________________
		case "OpeFin02_04UnidadesResponsables.php":
			aDatos = {
				opcion : "ConsultaUnidadesResponsables"
			};
			conectayEjecuta__(aDatos,cPhp); 
		break;
	}
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * **
*                                                           *
* Funciones relacionadas a OpeFin02_01CuentasBancarias.php  *
*                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
// __________________________________
function AgregaCuentaBancaria(){
	// Llena aDatos con la opción que se ejecutara en PHP y los datos de captura idcuentabancaria, nombre, siglas, estatus	
	var aDatos = crearDatosCuenta("AgregaCuentaBancaria"); 	
	if ( validaDatosCuenta(aDatos)){					// Valida que los datos esten completos
		conectayEjecuta__(aDatos,cPhp);	// Se enlaza con el PHP Catalogos_.php
	}
}
// ______________________________________________
function ModificaCuentaBancaria(){
	// Llena aDatos con la opción que se ejecutara en PHP y los datos de captura idcuentabancaria, nombre, siglas, estatus
	var aDatos = crearDatosCuenta("ModificaCuentaBancaria"); 
	if ( validaDatosCuenta(aDatos)){			// Valida que los datos esten completos
		conectayEjecuta__(aDatos,cPhp);			// Llma a la rutina EsquemaModifica que esta en el php funcionalidad_.php
	}
}
// ______________________________________________
function EliminaCuentaBancaria(){
	var aDatos = crearDatosCuenta("EliminaCuentaBancaria"); 
	if ( validaDatosCuenta(aDatos)){			// Valida que los datos esten completos
		esperaRespuesta(`Desea eliminar la cuenta : ${aDatos.idCuentaBancaria} `).then((respuesta) => {
			if (respuesta) {
				conectayEjecuta__(aDatos,cPhp);			// Llma a la rutina EsquemaModifica que esta en el php funcionalidad_.php
			}
		});
	}
}
// ______________________________________________
function crearDatosCuenta(cOpcion) {
	return {
    	opcion 				: cOpcion,
	    idCuentaBancaria 	: document.getElementById("idCuentaBancaria").value.trim(),
	    nombre 				: document.getElementById("nombre").value.trim().toUpperCase(),
	    siglas 				: document.getElementById("siglas").value.trim().toUpperCase(),
	    estatus 			: document.getElementById("estatus").checked,
	    consecutivo			: 0
  	};
}
// ______________________________________________
function validaDatosCuenta(aDatos){
	var regreso = false;
	if ( soloNumerosGuion(aDatos.idCuentaBancaria,"Cuenta Bancaria","idCuentaBancaria",lRequerido) ){
		if (soloLetrasNumerosGuion(aDatos.nombre,"Nombre Cuenta","nombre",lRequerido)){
			if (soloLetrasNumerosGuion(aDatos.siglas,"Siglas Cuenta","siglas",lRequerido)){
				regreso = true;
			}
		}
	}
	return regreso;
}
// ______________________________________________
async function procesarRespuesta__(vRespuesta) {				// Define una promesa para esta función
    switch(vRespuesta.opcion) {
//		---------------------------------    	
        case "CargaCuentasBancarias":
            await CargaCuentasBancarias(vRespuesta.resultados,vRespuesta.sesion);		// Despliega la nueva información en la tabla HTML
            if (vRespuesta.sesion.esquemaUsuario!="Administrador"){
            	document.getElementById("divBotones").style.display = "none";
            }else{
            	// document.getElementById("divBotones").style.display = "block";		// No se debería de dar, debio a que se debe cerrar sesión 
            }
        break;
        // ______________________________________________________________________
        case "AgregaCuentaBancaria": 													// Despues de insertar
            await CargaCuentasBancarias(vRespuesta.resultados,vRespuesta.sesion);		// Despliega la nueva información en la tabla HTML
        break;
    	case "ModificaCuentaBancaria": 													// Despues de modificar
    		await CargaCuentasBancarias(vRespuesta.resultados,vRespuesta.sesion);		// Desplegara la nueva información en la tabla HTML
		break;
    	case "EliminaCuentaBancaria": 													// Despues de Eliminar 
    		await CargaCuentasBancarias(vRespuesta.resultados,vRespuesta.sesion);		// Desplegara la nueva información en la tabla HTML
		break;
//		--------------------------------- 
		case "ConsultaOperacionesBancarias":
			// Despliega la consulta de la tabla de operacionesbancarias en la tabla HTML 
			await ConsultaOperacionesBancarias(vRespuesta.resultados,vRespuesta.sesion);
            if (vRespuesta.sesion.esquemaUsuario!="Administrador"){
            	document.getElementById("divBotones").style.display = "none";
            }
        break;  
        case "AgregaOperacion":
        	// Despues de agregar se requiere que la tabla HTML se actualice con la nueva operación
        	await  ConsultaOperacionesBancarias(vRespuesta.resultados,vRespuesta.sesion);
    	break;
        case "ModificaOperacion":
        	// Despues de modificar se requiere que la tabla HTML se actualice con los cambios de la operación
        	await  ConsultaOperacionesBancarias(vRespuesta.resultados,vRespuesta.sesion);
    	break;
        case "EliminaOperacion":
        	// Despues de eliminar se requiere que la tabla HTML ya no presente la operación eliminada
        	await  ConsultaOperacionesBancarias(vRespuesta.resultados,vRespuesta.sesion);
    	break;
//		__________________________________________
		case "ConsultaControlesBancarios":
			// Despliega la consulta de la tabla de controlesbancarios en la tabla HTML 
			await  ConsultaControlesBancarios(vRespuesta);
		break;	
		case "AgregaControl":
			// Despues de agregar se requiere que la tabla HTML se actualice con el nuevo control
			//await  ConsultaControlesBancarios(vRespuesta);
			await paginaControlBancario("-1");
		break; 
		case "ModificaControl":
			// Despues de modificar se requiere que la tabla HTML se actualice con los cambios al control
			//await  ConsultaControlesBancarios(vRespuesta);
			await paginaControlBancario("-1");
		break; 
		case "EliminaControl":
			// Despues de eliminar se requiere que la tabla HTML ya no presente el control eliminado
			//await  ConsultaControlesBancarios(vRespuesta);
			//await paginaControlBancario(-1);
			await paginaControlBancario("0");
		break; 
//		__________________________________________
		case "ConsultaUnidadesResponsables":
			await ConsultaUnidadesResponsables(vRespuesta.resultados,vRespuesta.sesion);
		break;
//		__________________________________________

    }   
} 
// __________________________________
function CargaCuentasBancarias(aRen){// aRen contiene todos los elementos que regreso el select a esquemas
	// Se obtiene un apuntador a el cuerpo de la tabla HTML
	var table = document.getElementById("cuentasBancarias").getElementsByTagName('tbody')[0];

	limpiaTabla(table);

	aRen.forEach(function(item) {
  		var row = table.insertRow(-1); // Inserta una nueva fila al final de la tabla.

  		// Y ahora las celdas
  		var cell0 = row.insertCell(0); // celda para Cuenta 
  		var cell1 = row.insertCell(1); // Nombre
  		var cell2 = row.insertCell(2); // Activo
  		var cell3 = row.insertCell(3); // Usuario que da de alta
  		var cell4 = row.insertCell(4); // Fecha de el alta
  		var cell5 = row.insertCell(5); // Fecha de el alta
  		var cell6 = row.insertCell(6); // Fecha de el alta


  		// Asigna los valores de las propiedades a las celdas.
		cell0.innerHTML = item.idcuentabancaria;
		cell1.innerHTML = item.nombre;
		cell2.innerHTML = item.siglas;
		cell3.innerHTML = item.estatus?"SI":"NO";
		cell4.innerHTML = item.consecutivo;
		cell5.innerHTML = item.usuarioalta;
		cell6.innerHTML = item.fechaalta;
	});

	// Asigna escucha click a la tabla
	if (1==1){ // Solo lo haga una vez
		// Apuntador a la tabla HTML de esquemas
		const tabla = document.getElementById("cuentasBancarias");
		// Obtén todos los renglones de la tabla.
		const renglones = tabla.getElementsByTagName("tr");

		// Agrega un evento "click" a cada  renglón.
		for (let i = 0; i < renglones.length; i++) {
			renglones[i].addEventListener("click", function() {
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
			    document.getElementById("idCuentaBancaria").value 	= this.cells[0].textContent;
			    document.getElementById("nombre").value 			= this.cells[1].textContent;
			    document.getElementById("siglas").value 			= this.cells[2].textContent;
			    document.getElementById("estatus").checked			= this.cells[3].textContent==="SI"?true:false;
		  	});
		}

	}
}
// _____________________________________________________________
// _____________________________________________________________
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  *
*                                                               *
* Funciones relacionadas a OpeFin02_02OperacionesBancarias.php  *
*                                                               *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
// __________________________________
function AgregaOperacion(){
	var aDatos = crearDatosOperacion("AgregaOperacion"); 	
	if ( validaDatosOperacion(aDatos)){					// Valida que los datos esten completos
		// EL signo + o - son interpretados al pasar de JS a PHP y no llegan como variables	
		// Ni aunque se protejan con \+ o \-
		if (aDatos.operador==="+"){ 
			aDatos.operador="S";	// Se manda una S en lugar del +
		}else{
			aDatos.operador="R";	// Se manda una R en lugar del -
		}
		conectayEjecuta__(aDatos,cPhp);					// Se enlaza con el PHP Catalogos_.php
	}
}
// __________________________________
function ModificaOperacion(){
	var aDatos = crearDatosOperacion("ModificaOperacion");
	if ( validaDatosOperacion(aDatos)){					// Valida que los datos esten completos
		// EL signo + o - son interpretados al pasar de JS a PHP y no llegan como variables	
		// Ni aunque se protejan con \+ o \-
		if (aDatos.operador==="+"){ 
			aDatos.operador="S";				// Se manda una S en lugar del +
		}else{
			aDatos.operador="R";				// Se manda una R en lugar del -
		}
		conectayEjecuta__(aDatos,cPhp);			// Se enlaza con el PHP Catalogos_.php
	}
}
// __________________________________
function EliminaOperacion(){
	var aDatos = crearDatosOperacion("EliminaOperacion");
	if ( validaDatosOperacion(aDatos)){					// Valida que los datos esten completos
		esperaRespuesta(`Desea eliminar la Operación : ${aDatos.idOperacion} `).then((respuesta) => {
			if (respuesta) {
				conectayEjecuta__(aDatos,cPhp);			// Se enlaza con el PHP Catalogos_.php
			}
		});
	}
}
// __________________________________
// __________________________________
 function crearDatosOperacion(cOpcion) {
	return {
    	opcion 			: cOpcion,
	    idOperacion 	: document.getElementById("idOperacion").value.trim().toUpperCase(),
	    nombre 			: document.getElementById("nombre").value.trim().toUpperCase(),
	    tipo 			: document.getElementById("tipo").value.trim().toUpperCase(),
	    operador		: document.getElementById("operador").value.trim(),
	    idOperCan		: document.getElementById("idOperCan").value.trim(),
	    visualizar		: document.getElementById("visualizar").checked
  	};
}
// __________________________________
function validaDatosOperacion(aDatos){
	var regreso = false; 
	if (aDatos.idOperacion.trim()!==""){
		if ("CIN,CEG,CAN".includes(aDatos.idOperacion) ){
			mandaMensaje("Las operaciones de Cancelación no se pueden modificar, eliminar o adicionar");
			return false;
		}
	}
	if ( sololetras(aDatos.idOperacion,"ID Operación ","idOperacion",lRequerido) ){
		if (sololetras(aDatos.nombre,"Nombre Operación Bancaria","nombre",lRequerido)){
			if (valoresPermitidos(aDatos.tipo,"I,E","Tipo","tipo")){
				if (valoresPermitidos(aDatos.operador,"+,-","Saldo","operador")){
					// Las operaciones de cancelación se adicionaron manualmente, 
					// todas las demas operaciones deben llevar operación de cancelación
					if (tieneValor(aDatos.idOperCan,"Operación Cancelación","idOperCan")){ 
						regreso = true;
					}
				}
			}else{
				mandaMensaje("Se requiere seleccionar valor para Tipo de operación");
			}
		}
	}
	return regreso;
}
// __________________________________
function ConsultaOperacionesBancarias(aRen,cSesion){

	var table = document.getElementById("operacionesBancarias").getElementsByTagName('tbody')[0];

	limpiaTabla(table);

	// Se llena la tabla con los datos que regreso el SQL
	aRen.forEach(function(item) {
		var aCeldas = []; // Crear un arreglo vacío
  		var row 	= table.insertRow(-1); // Inserta una nueva fila al final de la tabla.
  		for(i=0;i<(Object.keys(item).length)/2;i++){ // item tiene propiedades con indice (0....N) y nombre de propiedad (nombre1...nombreN)
  			var celda = row.insertCell(i);
  			aCeldas.push(celda);
  		}

  		for(i=0;i<(Object.keys(item).length)/2;i++){
  			if ( typeof item[i]=== 'boolean' ){
				aCeldas[i].innerHTML = item[i]?"SI":"NO";
  			}else{
				aCeldas[i].innerHTML = item[i]
			}
			//console.log(propiedad + ": " + item[propiedad]);
		}
		detente = 0;
	});

	// Asigna escucha click a la tabla
	if (1==1){ // Solo lo haga una vez
		// Apuntador a la tabla HTML de esquemas
		const tabla = document.getElementById("operacionesBancarias");
		// Obtén todos los renglones de la tabla.
		const renglones = tabla.getElementsByTagName("tr");

		// Agrega un evento "click" a cada  renglón.
		for (let i = 0; i < renglones.length; i++) {
			renglones[i].addEventListener("click", function() {
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
			    document.getElementById("idOperacion").value 	= this.cells[0].textContent;
			    document.getElementById("nombre").value 		= this.cells[1].textContent;
			    document.getElementById("tipo").value 			= this.cells[2].textContent;
			    document.getElementById("operador").value		= this.cells[3].textContent;
			    document.getElementById("idOperCan").value		= this.cells[4].textContent;
			    document.getElementById("visualizar").checked	= this.cells[5].textContent==="SI"?true:false;;
		  	});
		}
	}
	// Se crea una tabla con los botones anterior y siguiente en el div paginador
	var p = new Paginador(
    	document.getElementById('paginador'), 				// div con botones 
    	document.getElementById('operacionesBancarias'), 	// tabla a paginar
    	6 													// Número de renglones por página
	);
	p.Mostrar();
}
// __________________________________
function OperacionSaldoCancelacion(){
	cTipo = document.getElementById("tipo").value;
	switch(cTipo){
		case "I":
			document.getElementById("operador").value = "+";
			document.getElementById("idOperCan").value = "CIN";
		break;
		case "E":
			document.getElementById("operador").value = "-"
			document.getElementById("idOperCan").value = "CEG";
		break;
	}
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  *
*                                                               *
* Funciones relacionadas a OpeFin02_03ContolesBancarios.php     *
*                                                               *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
// __________________________________
function AgregaControl(){
	// Llena aDatos con la opción que se ejecutara en PHP y los datos de captura idcuentabancaria, nombre, siglas, estatus	
	var aDatos = crearDatosControl("AgregaControl"); 	
	if ( validaDatosControl(aDatos)){					// Valida que los datos esten completos
		conectayEjecuta__(aDatos,cPhp);	// Se enlaza con el PHP Catalogos_.php
	}
}
// __________________________________
function ModificaControl(){
	// Llena aDatos con la opción que se ejecutara en PHP y los datos de captura idcuentabancaria, nombre, siglas, estatus	
	var aDatos = crearDatosControl("ModificaControl"); 	
	if ( validaDatosControl(aDatos)){					// Valida que los datos esten completos
		conectayEjecuta__(aDatos,cPhp);					// Se enlaza con el PHP Catalogos_.php
	}
}
// __________________________________
function EliminaControl(){
	// Llena aDatos con la opción que se ejecutara en PHP y los datos de captura idcuentabancaria, nombre, siglas, estatus	
	var aDatos = crearDatosControl("EliminaControl"); 	
	if ( validaDatosControl(aDatos)){					// Valida que los datos esten completos
		esperaRespuesta(`Desea eliminar el Control : ${aDatos.idControl} `).then((respuesta) => {
			if (respuesta) {
				conectayEjecuta__(aDatos,cPhp);	// Se enlaza con el PHP Catalogos_.php
			}
		});
	}
}
// __________________________________
 function crearDatosControl(cOpcion) {
	return {
    	opcion 			: cOpcion,
	    idControl 		: document.getElementById("idControl").value.trim().toUpperCase(),
	    idOperacion		: document.getElementById("idOperacion").value.trim(),
	    nombre 			: document.getElementById("nombre").value.trim().toUpperCase()
  	};
}
// __________________________________
function validaDatosControl(aDatos){
	var regreso = false;
	if ( sololetras(aDatos.idControl,"ID Control ","idControl",lRequerido) ){
		if (tieneValor(aDatos.idOperacion,"Id Operación Bancaria","idOperacion")){
			if (sololetras(aDatos.nombre,"Nombre Control Bancario","nombre",lRequerido)){
			// Las operaciones de cancelación se adicionaron manualmente, 
			// todas las demas operaciones deben llevar operación de cancelación
				regreso = true;
			}
		}
	}
	return regreso;
}
// __________________________________
function paginaControlBancario(cPag){
	pagina = document.getElementById("pagina").value;
	if (pagina===null){
		pagina = 1;
	}
	if (cPag=="-1"){ // Cambia el número de registros x pagina o se introdujo texto de búsqueda
		pagina = 1;
	}
	aDatos = {
		opcion 			: "ConsultaControlesBancarios",
		limite			: document.getElementById("num_registros").value,
		busca			: document.getElementById("campo").value,
		pagina 			: pagina,
		tabla			: " controlesbancarios a , operacionesbancarias b ",
		tablaPrin		: "controlesbancarios",
		join			: " a.idoperacion=b.idoperacion ",
		campos			: " a.idcontrol,a.idoperacion,a.nombre,b.tipo,a.usuarioalta,a.fechaalta ",
		tipos			: "C,C,C,C,C,T",
		order			: "",
		id 				: "idcontrol",
		regreso			: "",
		depura			: "",
		traeOperaciones	: true
	};
	// Hace enlace con el php Catalogos_.php para ejecutar la "opcion"
	conectayEjecuta__(aDatos,cPhp); 
}
// __________________________________
function ConsultaControlesBancarios(aRespuesta){
	var cTabla = "controlesBancarios";
	var table  = document.getElementById(cTabla).getElementsByTagName('tbody')[0];

	limpiaTabla(table);

	if (una_vez){
		// Obtén una referencia al elemento select
		var select = document.getElementById("idOperacion");
		llenaCombo(select,aRespuesta.combo);
		una_vez = false;
	}

	table.innerHTML = aRespuesta.datos.regreso.data;
	// Asigna escucha click a la tabla
	if (1==1){ // Solo lo haga una vez
		// Apuntador a la tabla HTML de esquemas
		const tabla = document.getElementById(cTabla);
		// Obtén todos los renglones de la tabla.
		const renglones = tabla.getElementsByTagName("tr");

		// Agrega un evento "click" a cada  renglón.
		for (let i = 0; i < renglones.length; i++) {
			renglones[i].addEventListener("click", function() {
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
			    document.getElementById("idControl").value 		= this.cells[0].textContent;
			    document.getElementById("idOperacion").value 	= this.cells[1].textContent;
			    document.getElementById("nombre").value 		= this.cells[2].textContent;
		  	});
		}
	}
    //document.getElementById("lbl-total").innerHTML = 'Mostrando ' + aRespuesta.datos.regreso.totalFiltro +
    //                    							 ' de ' + aRespuesta.datos.regreso.totalRegistros + ' registros'
    document.getElementById("nav-paginacion").innerHTML = aRespuesta.datos.regreso.paginacion;
	// Se crea una tabla con los botones anterior y siguiente en el div paginador
	/*
	var p = new Paginador(
    	document.getElementById('paginador'), 	// div con botones 
    	document.getElementById(cTabla), 		// tabla a paginar
    	6 										// Número de renglones por página
	);
	p.Mostrar();*/
	// Se llena el combo-select de claves de operación


}
// _______________________________________________________________
function nextPage(pagina){
    document.getElementById('pagina').value = pagina
    paginaControlBancario("0");
}

// _______________________________________________________________
// _______________________________________________________________
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  *
*                                                               *
* Funciones relacionadas a OpeFin02_04UnidadesResponsables.php  *
*                                                               *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function ConsultaUnidadesResponsables(aRen,vSesion){
	var cTabla = "unidadesResponsables";
	var table  = document.getElementById(cTabla).getElementsByTagName('tbody')[0];

	limpiaTabla(table);

	// Se llena la tabla con los datos que regreso el SQL
	aRen.forEach(function(item) {
		var aCeldas = []; // Crear un arreglo vacío
  		var row 	= table.insertRow(-1); // Inserta una nueva fila al final de la tabla.
  		for(i=0;i<(Object.keys(item).length);i++){ // item tiene propiedades c(nombre1...nombreN)
  			var celda = row.insertCell(i);
  			aCeldas.push(celda);
  		}
  		/*
  		for(i=0;i<(Object.keys(item).length)/2;i++){
  			if ( typeof item[i]=== 'boolean' ){
				aCeldas[i].innerHTML = item[i]?"SI":"NO";
  			}else{
				aCeldas[i].innerHTML = item[i]
			}
			//console.log(propiedad + ": " + item[propiedad]);
		} */
		i=0;
		for (var propiedad in item) {
			// Verificar si la propiedad pertenece al objeto y no a su prototipo
			/*if (miObjeto.hasOwnProperty(propiedad)) {
    			arreglo.push(miObjeto[propiedad]);
  			}*/
  			if ( typeof item[propiedad]=== 'boolean' ){
  				aCeldas[i].innerHTML = item[propiedad]?"SI":"NO";
  			}else{
  				aCeldas[i].innerHTML = item[propiedad];
  			}
  			i++
		}
		detente = 0;
	});

	// Asigna escucha click a la tabla
	if (1==1){ // Solo lo haga una vez
		// Apuntador a la tabla HTML de esquemas
		const tabla = document.getElementById(cTabla);
		// Obtén todos los renglones de la tabla.
		const renglones = tabla.getElementsByTagName("tr");

		// Agrega un evento "click" a cada  renglón.
		for (let i = 1; i < renglones.length; i++) { // 1 para que se salte el encabezado
			renglones[i].addEventListener("click", function() {
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
			    document.getElementById("idUnidad").value 		= this.cells[0].textContent;
			    document.getElementById("nombreunidad").value 	= this.cells[1].textContent;
		    	document.getElementById("estatus").checked 	 	= this.cells[2].textContent==="SI"?true:false;
		  	});
		}
	}
	
	// Se crea una tabla con los botones anterior y siguiente en el div paginador
	var p = new Paginador(
    	document.getElementById('paginador'), 	// div con botones 
    	document.getElementById(cTabla), 		// tabla a paginar
    	10 										// Número de renglones por página
	);
	p.Mostrar();
}