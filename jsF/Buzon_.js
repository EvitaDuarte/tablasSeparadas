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

var una_vez 	= true;			// Variable global para que algun proceso se ejecute solo una vez
var cPhp    	= "Buzon_.php";	// En este php estarán las funciones que se infocaran desde este JS
var name_file	= "";			// Nombre del archivo CSV 
var datosTabla  = [];			// Para almacenar los datos de la tabla y pasarlos a PHP
var anioMov		= ""; 			// Año de fecha de captura
var anioAct		= ""; 			// Año actual del Servidor
var hoy			= ""; 			// Dia de hoy del Servidor
var marcar		= false;
var lValidar	= false;
var dHoy		= "";
var cEsquema	= "";				// Rol del Usuario
var cYear		= "";
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
		case "OpeFin03_01Buzon.php":
			aDatos = {
				opcion 	: "CargaCatalogos"
			};
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
		// ________________________________________
	}
}
// _____________________________________________________
// ______________________________________________
async function procesarRespuesta__(vRes) {				// Define una promesa para esta función
    switch(vRes.opcion) {
//		__________________________________________
        case "CargaCatalogos":
            // no hay una tabla HTML a procesar
            cEsquema = vRes.sesion.esquemaUsuario;
            cYear	 = vRes.anioHoy;
            CargaCatalogos(vRes.combo,vRes.combo1,vRes.resultados,vRes.anioHoy,vRes.hoy);
        break;
//		__________________________________________
    	case "ValidaBuzon":
    		//Quizas limpiar la información para la siguiente carga del buzon
    	break;
    }   
} 
// _______________________________________________
function CargaCatalogos(aOpeCtrl,aCtaBan,cAnio,cAnioHoy,cHoy){
	// Se llena el combo-select de claves de operación-ctrl y de Cuentas bancarias
	if (una_vez){
		// Obtén una referencia al elemento select
		var select = document.getElementById("idOpeCtrl");
		llenaCombo(select,aOpeCtrl);
		var select = document.getElementById("idCuentabancaria");
		llenaCombo(select,aCtaBan);	
		anioMov = cAnio;	
		una_vez = false;
		anioAct = cAnioHoy;
		hoy		= cHoy;
		dHoy	= fgyyyyddmm(cHoy,"/");
		// input date necesita valor en yyyy-mm-dd
		document.querySelector("#idFechaMovs").value = dHoy; // fgyyyyddmm devuelve [2]-[1]-[0]
		// con sole.log(cAnio);
	}

}
// _______________________________________________
function cargaArchivoCsv(){
	const fileInput = document.getElementById('ArchivoCarga_file'); // Asegúrate de tener un elemento de entrada de archivo en tu HTML
	const file 		= fileInput.files[0];
	const reader 	= new FileReader();
	reader.onload = function (e) {
		const csvContent = e.target.result;
		// Aquí puedes procesar el contenido del archivo CSV
		// Llamar a una función para cargarlo en la tabla HTML
		procesarCSV(csvContent);
	};
	reader.readAsText(file, 'UTF-8');
}
// _______________________________________________
function procesarCSV(csvContent) {
	const tableBody 	= document.getElementById('cuerpo'); // Asegúrate de tener un tbody en tu tabla HTML
	tableBody.innerHTML = ''; // Limpia el contenido actual de la tabla

	const filas = csvContent.split('\n');
	//
    // Dividir el contenido CSV en filas y columnas
    const filas1 = csvContent.split('\n').map(fila => fila.split('\t'));

    // Obtener el número de columnas
    const numeroDeColumnas = filas1.length > 0 ? filas1[0].length : 0;

    // Validar que el número de columnas sea exactamente 6
    //if (numeroDeColumnas !== 6) {
    //    mandaMensaje('El archivo CSV debe tener exactamente 6 columnas.');
    //    return;
    //}
    cMensaje = "";
    if ( SegunColumnaNumerica(filas1)==false ){
    	cMensaje = "Error en el número de columnas o en el campo numérico";
    	lValidar = false;
    }else{
    	lValidar = true;
    }
    //
    cMensaje1 = ""
    filas.forEach(function (fila) {
    	if (fila.trim()!=""){ // Revisa las longitudes de los campos
    		const [cBenefi, cImpo, cCpto,cRefe,cDocto,cUr] = fila.split("\t");
    		if (cBenefi.trim().length>150 ){
    			if ( !cMensaje1.includes("Beneficiario") ){
    				cMensaje1 = cMensaje1 + "Beneficiario(150), ";
    			}
    		}
    		if (cCpto.trim().length>150){
    			if ( !cMensaje1.includes("Concepto") ){
    				cMensaje1 = cMensaje1 + "Concepto(150), ";
    			}
    		}
    		if (cRefe.trim().length>20 ){
    			if ( !cMensaje1.includes("Referencia") ){
    				cMensaje1 = cMensaje1 + "Referencia(20), ";
    			}
    		}
    		if ( cDocto.trim().length>20 ){
    			if ( !cMensaje1.includes("Documento") ){
    				cMensaje1 = cMensaje1 + "Documento(20), ";
    			}  
    		}
    		if ( cUr.trim().length> 4){
    			if ( !cMensaje1.includes("UR") ){
    				cMensaje1 = cMensaje1 + "UR(4) [" + cUr + "]"; 
    			}
    		}
    	}
    });
    if (cMensaje!="" || cMensaje1 !=""){
    	lValidar = false;  
    	if (cMensaje1!=""){
    		cMensaje1 = "Los valores de "+cMensaje1+" exceden los límites"
    	}
    	mandaMensaje(cMensaje+" "+cMensaje1);
    }
	//
	filas.forEach(function (fila) {				// Recorrer cada renglón del archivo CSV
		if (fila.trim()!=""){
			const celdas 	= fila.split('\t'); 	// Delimitado por tabulaciones
			const filaHTML	= document.createElement('tr');

			celdas.forEach(function (celda) {
				// Quita comillas que puedan venir en el archivo CSV
				celda 					= celda.replace(/"/g,'');
				const celdaHTML 		= document.createElement('td');
				celdaHTML.textContent 	= celda.trim(); 
				filaHTML.appendChild(celdaHTML);
			});

	    	// Agrega un checkbox como celda
	    	const celdaHTML			= document.createElement('td');
		    const checkbox			= document.createElement('input');
		    checkbox.type			= 'checkbox';
	        checkbox.name 			= 'seleccion';
	    	checkbox.checked 		= true
	    	checkbox.style.display	= 'block';
	    	checkbox.classList.add('mi-checkbox');
		    celdaHTML.appendChild(checkbox);
			filaHTML.appendChild(celdaHTML);

			filaHTML.addEventListener("click", function() {
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
			    document.getElementById("idDocto").value 		= filaHTML.cells[4].textContent;
			    document.getElementById("idRefe").value 		= filaHTML.cells[3].textContent;
			    document.getElementById("idImpo").value 		= filaHTML.cells[1].textContent;
			    document.getElementById("idUr").value			= filaHTML.cells[5].textContent;
			    document.getElementById("txtBeneficia").value	= filaHTML.cells[0].textContent;
			    document.getElementById("txtCpto").value		= filaHTML.cells[2].textContent;
		  	});

	    	// Agrega un botón como celda
	    	/*
	    	const celdaHTML1	= document.createElement('td');
		    const boton			= document.createElement('input');
		    boton.type			= 'button';
		    boton.value 		= 'Elimina';
		    boton.classList.add('mi-boton');
		    celdaHTML1.appendChild(boton);
			filaHTML.appendChild(celdaHTML1);
			*/

			tableBody.appendChild(filaHTML);
		}
	});
	/* Se hace bolas con la búsqueda
	var p = new Paginador(
    	document.getElementById('paginador'), 				// div con botones 
    	document.getElementById('buzon'), 					// tabla a paginar
    	12 													// Número de renglones por página
	);
	p.Mostrar();
	*/
}
// _______________________________________________
function SegunColumnaNumerica(filas) {
	lBien = true;
	i = 1;
    //filas.every(fila => {
	filas.forEach(fila => {
    //for (let i = 0; i < filas.length; i++) {
       // const fila = filas[i];
		if (fila.length === 0 || fila.every(columna => columna.trim() === '')) {
		}else{
	        if (fila.length == 6) {
	            valorSegundaColumna = fila[1].replace(/[ \t,"']/g, '').trim(); // Eliminar espacios, tabuladores, comas y comillas dobles
	            esNumero = /^-?\d+(\.\d{2})?$/.test(valorSegundaColumna); // Aceptar números con dos dígitos decimales opcionales
	            if (!esNumero) {
	            	//con sole.log ("No numérico "+valorSegundaColumna)
	                lBien = false;
	            }
	        }else{
	        	//con sole.log("Renglon "+ (i) +" columnas "+fila.length+ "["+fila+"]");
	        	lBien = false;
	        }
	    }
	    1 +1;
    });
    return lBien
}
// _______________________________________________
function BuzonValidar(){
	// Validaciones en el cliente
	if ( name_file==="" ){
		mandaMensaje("No se ha integrado el archivo CSV delimitado por tabuladores");
		return;
	}
	if ( !lValidar){
		mandaMensaje("No se tiene el número de columnas o información correctos");
		return;
	}
	oCtaBan  = {id:"idCuentabancaria",nombre:"Cuenta Bancaria"	,valor:""};
	oOpeCtrl = {id:"idOpeCtrl"		 ,nombre:"Operación Control",valor:""};
	oFechMov = {id:"idFechaMovs"	 ,nombre:"Fecha Integración",valor:""};
	// Valida que esten los datos completos
	if (seCapturo(oCtaBan)===false){
		return;
	}
	if (seCapturo(oOpeCtrl)===false){
		return;
	}
	if (seCapturo(oFechMov)===false){
		return;
	}
	oFechMov.valor = fddmmyyyy(oFechMov.valor.trim(),"-"); // por el cambio de input text a input date

	datosTabla = renglonesTablaHtml();
	//con sole.log(oFechMov.valor);
	if ( CadenaToFecha(oFechMov.valor)==null ){
		return;
	}
	cAnio = oFechMov.valor.substr(-4);
	// Valída el año
	if ( cAnio<anioMov){
		mandaMensaje("El año solicitado ["+cAnio+"] es menor al año permitido ["+anioMov+"]");
		return;
	}
	if ( cAnio > anioAct ){
		mandaMensaje("El año solicitado ["+cAnio+"] es mayor al año actual ["+anioAct+"]");
		return;
	}
	// Valida la fecha de integración
	if ( fyyyyddmm(oFechMov.valor,'/') > fyyyyddmm(hoy,'/') ){
		mandaMensaje( "La fecha de los movimientos ["+oFechMov.valor+"] es mayor a la fecha actual ["+hoy+"]" );
		return;
	}
	if ( fyyyyddmm(oFechMov.valor,'/')!==fyyyyddmm(hoy,'/') ){
		vRol = document.getElementById("nomEsquema").value.toUpperCase();
		if (vRol!=="ADMINISTRADOR"){
			mandaMensaje("Solo el Administrador puede integrar movimientos de días anteriores ["+oFechMov.valor);
			return;
		}
	}
	// Valida en el servidor
	aOpeCtrl = oOpeCtrl.valor.split("-"); // Divide la Operación y el Control
	aCtaBan  = oCtaBan.valor.split("|");  // Divide la cuenta Bancaria y la sigña
	aDatos ={
		opcion		: "ValidaBuzon",
		ctBancaria	: aCtaBan[0],
		cSiglas		: aCtaBan[1],
		cMovOpe		: aOpeCtrl[0],
		cCtrl		: aOpeCtrl[1],
		fechaMov	: oFechMov.valor,
		cTipo		: "",	// Para guardar si es un movimiento de I o E
		cOperador	: "",	// para saber si se Suma (+) o Resta (-) al Saldo
		aBuzon		: datosTabla
	}
	// Valida que los datos esten completos
	conectayEjecutaPost(aDatos,cPhp);			// Llma a la rutina ValidaBuzon que esta en el php Buzon_.php
}
// _______________________________________________
function renglonesTablaHtml(){
	let datos = [];
	// Se obtiene tabla a iterar
	var tabla = document.getElementById('buzon');	

	// Se recorren todos los datos de la tabla HTML
	for (var i = 1; i < tabla.rows.length; i++) {
		// Obtiene la celda del checkbox en la última columna
		var checkboxCell = tabla.rows[i].cells[tabla.rows[i].cells.length - 1];

		// Obtiene el checkbox dentro de la celda
		var checkbox = checkboxCell.querySelector('input[type="checkbox"]');
		// Verifica si el checkbox está marcado
		if (checkbox.checked) {
			// Crea un objeto para almacenar los datos de esta fila
			var filaDatos = {
				beneficiario	: tabla.rows[i].cells[0].textContent,
				importe			: tabla.rows[i].cells[1].textContent.replace(/,/g, ''),
				concepto		: tabla.rows[i].cells[2].textContent,
				referencia		: tabla.rows[i].cells[3].textContent,
				docto			: tabla.rows[i].cells[4].textContent,
				ur				: tabla.rows[i].cells[5].textContent,
				validar			: 1
			};
			// Agrega el objeto al arreglo de datos seleccionados
			datos.push(filaDatos);
		}
	}
	return datos;
}
// _______________________________________________
function BuzonMarcar() {
	var tabla = document.getElementById("buzon");
	var filas = tabla.getElementsByTagName('tr');
	var columna = 6;
//
	for (var i = 0; i < filas.length; i++) {
		var celda = filas[i].getElementsByTagName('td')[columna];
		if (celda) {
			var checkbox = celda.querySelector('input[type="checkbox"]');
			if (checkbox) {
		    	checkbox.checked = !checkbox.checked; // Alternar el estado
		  	}
		}
	}	
}
// _______________________________________________