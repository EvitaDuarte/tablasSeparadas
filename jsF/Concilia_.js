/*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Autor   		: Miguel Ángel Bolaños Guillén        	*
 * Sistema 		: Sistema de Operación Bancaria Web   	*
 * Fecha   		: Octubre 2023                        	*
 * Descripción 	: Rutinas para realizar el enlace 		* 
 *                entre JavaScript y PHP           		*
 *                para los Movimientos bancarios		*
 *                Unadm-Proyecto Terminal para INE 		*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*/
var pagCta		 = "";				// Guarda la cuenta Bancaria para el paginado
var cPhp    	 = "Concilia_.php";	// En este php estarán las funciones que se infocaran desde este JS
var gForma		 = ""				// Formulario que se este ejecutando
var dHoy		 = "";
var gOpeConci	 = [];
var gFiltro		 = false;
var gCampo		 = "";
var gTipo		 = "";


window.onload = function () {		// Función que se ejecuta al cargar la página HTML
	// Se obtiene el nombre del archivo que lo invoca
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);

	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// ______________________________________
		case "OpeFin05_01Conciliacion.php":

			escuchaCampoFecha('idFecConci');


			gForma 	 	= "formConcilia";
			gTabla	 	= "tablaConcilia";
			aDatos = {
				opcion 	: "CargaCatalogos"
			};
			// 
			quitaSubmit(gForma);
			conectayEjecutaPost(aDatos,cPhp,null);// Esta función esta en rutinas.js
		break;
		// ________________________________________________________________________
		// ________________________________________________________________________
		// ________________________________________________________________________
		// ________________________________________________________________________
		// ________________________________________________________________________

		default:
			mandaMensaje("No esta definido "+cHtml)
		break;
	}
}
// __________________________________________________________________________________
// Define una promesa para esta función, despues de ejecutar la opción correspondiente en PHP
// Esto lo ejecuta el cliente despues de invocar algun proceso en la función correspondiente PHP
// Son los regresos despues de que se invoco al servidor
async function procesarRespuesta__(vRes) {		
	cOpc = vRes.opcion.opcion;					// Es como se re cupera en PHP la opción 
	//cons ole.log("Opción de regreso : "+ cOpc);
    switch(cOpc) {
// __________________________________________________________________________________
        case "CargaCatalogos": // Se llenaron los catalogos iniciales y hay que pasarlos a el HTML
            // no hay una tabla HTML a procesar
            //con sole.log("Opcion="+cOpc);
            await CargaCatalogos(vRes);
            await escuchaFoco(["idCuentabancaria"]);
        break;
// __________________________________________________________________________________
		case "FiltraMovimientosIne":
			await FiltraMovimientosIneRegreso(vRes);
		break; 
// __________________________________________________________________________________
// __________________________________________________________________________________
		case "operacionesConciliacion":
			await guardaOperacionesConciliacion(vRes.resultados);
		break;
// __________________________________________________________________________________
		case "realizaConciliacion": // Se declara para que no se vaya al default
		break;
// __________________________________________________________________________________
		case "reporteConciliacion":
			await abrePdf(vRes.archivo);
		break;
// __________________________________________________________________________________
		case "ConciliarMovimiento":
			await paginaMovimientos(document.getElementById("pagina").value);
		break;
// __________________________________________________________________________________
		default:
			mandaMensaje("No se encontro código JS de retorno para ["+cOpc+"]");
		break;
// __________________________________________________________________________________
    }   
}
// __________________________________________________________________________________
function CargaCatalogos(vRes){
	if (vRes.ctas!=null){	// Puede venir nula si no se han asignado cuentas al usuario
		llenaCombo( document.getElementById("idCuentabancaria") , vRes.ctas 	);
		document.getElementById("FechaHoy").value = vRes.opcion.hoy;
		dHoy									  = vRes.opcion.hoy;
		document.getElementById('FechaHoy').setAttribute('max', dHoy);
		document.getElementById('idFecConci').setAttribute('max', dHoy);
	}else{
		mandaMensaje("No se han definido Cuentas Bancarias a Conciliar para el usuario");
	}
}
// __________________________________________________________________________________
function guardaOperacionesConciliacion(aRes){
	gOpeConci	 = aRes[0];
	let detente  = 0;
}
// __________________________________________________________________________________
function consultaMovsCta(cCuenta,lHtml=false){ // Llamada desde el HTML
	if (cCuenta===""){
		document.getElementById("divLayOut1").classList.add("disabled");
		return;
	}
	gFiltro = false; 
	// ---------------------------------------
	document.getElementById("divLayOut1").classList.remove("disabled");
	pagCta = cCuenta.split('|')[0];
	paginaMovimientos(-1);
}
// __________________________________________________________________________________
function operacionesConciliacion(cCuenta){
	if (cCuenta===""){
		document.getElementById("divLayOut1").classList.add("disabled");
		return;
	}
	// ---------------------------------------
	document.getElementById("divLayOut1").classList.remove("disabled");
	// Va por las operaciones de Conciliación
	gOpeConci	 = [];
	idBanco		 = cCuenta.split('|')[1];
	aDatos 		 = {
		opcion	: "operacionesConciliacion",
		idbanco	: idBanco
	}
	conectayEjecutaPost(aDatos,cPhp,null); 
}
// __________________________________________________________________________________
function paginaMovimientos(cPag){
	aCampos = [];
	aTipo   = [];
	cCta    = pagCta; // pagCta se inicializa en el onchange del select de cuentas bancarias
	pagina  = document.getElementById("pagina").value;
	cBuscar = document.getElementById("opcionesFiltro").value.trim();
	cCampos =  "a.idcuentabancaria,a.conciliado,a.fechaconciliacion,a.folio,a.referenciabancaria,a.importeoperacion,a.fechaoperacion,"
	cCampos += "a.beneficiario,a.concepto,a.idunidad,a.idoperacion,a.idcontrol,a.anioejercicio,a.idmovimiento,a.estatus,b.tipo,"
	cCampos += "a.fch_opecon"
	cTipos  = "C,C,D,C,C,NF,D,C,C,C,C,C,C,N,C,C,C"; // C - Caracter , N Número , D - Fecha, NF - Númerico con comas
	if (pagina===null){
		pagina = 1;
	}
	if (cPag=="-1"){ // Cambia el número de registros x pagina o se introdujo texto de búsqueda
		pagina = 1;
	}
	// SI se requiere busqueda específica
	if (cBuscar==""){
		gFiltro = false;
		aCampos	= cCampos.split(',').map(campo => campo.trim());
		aTipo   = cTipos.split(',').map(tipo => tipo.trim());
	}else{
		gFiltro = true;
		switch(cBuscar){
			case "I":	// Importe
				aCampos = ["a.importeoperacion"];
				aTipo   = ["NF"]; // número con formato
			break;
			case "F":	// Fecha Operacion
				aCampos = ["a.fechaoperacion"];
				aTipo   = ["D"]
			break;
			case "B":	// Beneficiario
				aCampos = ["a.beneficiario"];
				aTipo   = ["C"]
			break;
			case "C":	// Concepto
				aCampos = ["a.concepto"];
				aTipo   = ["C"]
			break;
			case "O":	// Operación (Referencia Bancaria) del Banco
				aCampos = ["a.referenciabancaria"];
				aTipo   = ["C"]
			break;
			case "R":	// Recibo de Ingreso
				aCampos = ["a.folio"];
				aTipo   = ["C"]
			break;
		}
	}
	cTabla = "atablas.t_" + cCta;
	//
	aDatos = {
		opcion 			: "FiltraMovimientosIne",
		limite			: document.getElementById("num_registros").value,
		busca			: document.getElementById("campo").value,
		pagina 			: pagina,
		tabla			: " " + cTabla + " a , operacionesbancarias b ",
		tablaPrin		: "movimientos",
		join			: " a.idoperacion=b.idoperacion  and idcuentabancaria='"+cCta+"' ",
		campos			: cCampos,
		tipos			: cTipos,
		id 				: "idmovimiento",
		regreso			: "",
		order			: "order by fechaoperacion desc",
		depura			: "",
		traeOperaciones	: true,
		aCampos			: aCampos,
		aTipos			: aTipo
	};
	//
	conectayEjecutaPost(aDatos,cPhp,null);
}
// __________________________________________________________________________________

// __________________________________________________________________________________
function FiltraMovimientosIneRegreso(aRespuesta){ // Es el regreso del PHP
	var cTabla 	 = gTabla;
	var table  	 = document.getElementById(cTabla).getElementsByTagName('tbody')[0];
	lNoModificar = false;
	lDiaAnterior = false;

	limpiaTabla(table);

	table.innerHTML = aRespuesta.opcion.regreso.data;

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
				cFechaCap = this.cells[6].textContent;
		    	cOpe  	  = this.cells[10].textContent;
		    	cCtrl 	  = this.cells[11].textContent;
				cId		  = this.cells[13].textContent;
				cEstatus  = this.cells[14].textContent.trim();
		    	cAnio 	  = cFechaCap.substring(0,4);
				
				nImpo     = this.cells[5].textContent;
				nImpo	  = parseFloat(nImpo.replace(/,/g, '')); // Se debe pasar a un valor numérico
				nImpo 	  = nImpo.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
		    	
				document.getElementById("idStaConci").value 	= this.cells[1].textContent;
				document.getElementById("conciRespa").value 	= this.cells[1].textContent;  // hidden
				document.getElementById("idFecConci").value 	= this.cells[2].textContent;
				document.getElementById("fechaRespa").value 	= this.cells[2].textContent;  // hidden
			    document.getElementById("idRecibo").value 		= this.cells[3].textContent;
			    document.getElementById("idRefe").value 		= this.cells[4].textContent;
			    document.getElementById("idImpo").value 		= nImpo;
			    document.getElementById("idFecha").value 		= this.cells[6].textContent; // fddmmyyyy(this.cells[4].textContent,"-");
			    document.getElementById("idBenefi").value 		= this.cells[7].textContent;
			    document.getElementById("idCpto").value 		= this.cells[8].textContent;
			    document.getElementById("idUr").value 			= this.cells[9].textContent;
			    document.getElementById("idOpera").value 		= cOpe; 
			    document.getElementById("idCtrl").value 		= cCtrl+"|"+cOpe;
			    document.getElementById("idAnio").value 		= this.cells[12].textContent;
			    document.getElementById("idMovimiento").value	= cId;
			    document.getElementById("idEstatus").value 		= cEstatus;
			    document.getElementById("idTipo").value 		= this.cells[15].textContent;
			    document.getElementById("idOpeConci").value 	= this.cells[16].textContent;
				// En Ingresos, habilita o no la impresión y desglose de ingresos
		  	});
		}
	}
    //document.getElementById("lbl-total").innerHTML = 'Mostrando ' + aRespuesta.opcion.regreso.totalFiltro +
    //                    							 ' de ' + aRespuesta.opcion.regreso.totalRegistros + ' registros'
    document.getElementById("nav-paginacion").innerHTML = aRespuesta.opcion.regreso.paginacion;
}
// __________________________________________________________________________________
function nextPage(pagina){
    document.getElementById('pagina').value = pagina
    paginaMovimientos("0");
}
// __________________________________________________________________________________
function archivoLayOut(cValor){
	cOpc = "";
	if (cValor==""){
		mandaMensaje("Se requiere archivo de conciliación");
		return;
	}
	// Solicita archivo de layOut
	solicitaArchivoLayOut().then((respuesta) => {
		if (respuesta){
			var input1_file = document.getElementById('ArchivoCarga_file');
			var oFile		= input1_file.files[0];
			cFile 			= oFile.name;
			
			esperaRespuesta(`Desea iniciar proceso de conciliación de ${cFile} `).then((respuesta) => {
				if (respuesta){
					const reader  = new FileReader();
					reader.onload = function (e) {// Se ejecuta una vez que se lea el archivo con reader.readAsText(oFile, 'UTF-8');
						const csvContent = e.target.result;
						procesarTxtConciliar(csvContent);
					};
					reader.readAsText(oFile, 'UTF-8');
				}
			});

		}
	});
}
// __________________________________________________________________________________
function procesarTxtConciliar(csvContent){
	// console.log(csvContent);
	let datos   = []; 
	let saldos  = [];
	let cCta1   = RegresaCtaBancaria("idCuentabancaria");
	let cFecha  = "";
	let nSalFin = 0.00;
	const filas = csvContent.split('\n'); // Se obtiene renglones del archivo


	// Filtrar las líneas a partir de la línea 6 y luego iterar sobre ellas
	lPrimera = true;

	// No usar foreach ya que no acepta return para salir de la función
	for (let i = 5; i < filas.length; i++) {
		let fila = filas[i].trim(); 

		cChe = cOpa = cIng = ""; filaDatos = null;
		if (fila !== '') {
			let columna = fila.split('|');
			if (i == 5) {
				cCta	= columna[8];
				cFecha  = "20" +fgyyyyddmm(columna[6],"/"); // Cambiar cuando llegue a 2099 
				if (cCta!=cCta1){
					mandaMensaje("El layout es de la cuenta "+cCta+" y se selecciono la cuenta "+cCta1);
					return false;
				}
				nSalIni = columna[11];		nSalFin = columna[13];
				nNumCar = columna[14];		nNumAbo = columna[15];
				nSumCar = columna[16];		nSumAbo = columna[17];
				saldos.push(
					{
						SaldoIni : nSalIni,
						SaldoFin : nSalFin,
						NumCar 	 : nNumCar,
						NumAbo   : nNumAbo,
						SumCar   : nSumCar,
						SumAbo   : nSumAbo 
					}
				)
			} else {
				idArch 	= columna[0];		cTipo 	 = columna[2];
				cImpo	= columna[8];		cCodigo	 = columna[3]+columna[4];
				cNoRef	= columna[6];		cRefAl	 = columna[7];
				cConci	= "S";				cIdBanco = columna[9];
				if (gOpeConci.cheques.includes(cCodigo)){ 			// 0200,0100,0300
					cChe = columna[6].padStart(8, '0');
				}else if ( gOpeConci.opnocon.includes(cCodigo)){	// 1100,0500,1103,1300,1700,7300,5203,5302,7105,0554,6356
					cConci = "N"; // No se concilian
				}else if ( gOpeConci.ordenes.includes(cCodigo) ){	// 5100,5101,5102,6100,5105,5108,5100,5101,6100,5105,5108,5102,0506,
					// Aquí había una línea que si es layout de Sinope se debe tomar la columna
					cOpa = columna[9];
				}else if ( gOpeConci.ingresos.includes(cCodigo)){	// 7100,7101,1700
					cIng = columna[9];
				}

				var filaDatos = {
					numren		: idArch,
					tipo		: cTipo,
					codigo		: cCodigo,
					cheque		: cChe,
					ordPag		: cOpa,
					refIng		: cIng,
					idbanco		: cIdBanco,
					importe		: cImpo.replace(/,/g, ''),
					referencia  : cRefAl,
					seConcilia	: cConci,
					seEncontro  : ""
				 };
				 datos.push(filaDatos);
			}
		}
	}

	//console.log(datos);
	//console.log(saldos);

	aDatos = {
		opcion		: "realizaConciliacion",
		cuenta 		: cCta1,
		fecha		: cFecha,
		saldoFin	: nSalFin,
		operaciones : datos
	}
	conectayEjecutaPost(aDatos,cPhp,null);
}
// __________________________________________________________________________________
function ReporteConciliacion(){
	dFecha = document.getElementById("FechaHoy").value;
	if (dFecha > dHoy ){
		mandaMensaje("No se pueden procesar fechas futuras");
		document.getElementById("FechaHoy").value = dHoy;
		return false;
	}
	cCuenta = document.getElementById("idCuentabancaria").value;
	if (cCuenta==""){
		mandaMensaje("Se requiere seleccionar cuenta bancaria");
		FocoEn("idCuentabancaria");
		return false;
	}
	cNombre	= separaNombreCuenta(cCuenta); // debe ir antes
	cCuenta = separaCtaBancaria(cCuenta);

	aDatos ={
		opcion		: "reporteConciliacion",
		cuenta		: cCuenta,
		fecha		: dFecha,
		nombre		: cNombre
	}
	conectayEjecutaPost(aDatos,cPhp,null);
}
// __________________________________________________________________________________
function ConciliarMovimiento(){
	vIdMov		= document.getElementById("idMovimiento").value.trim();
	vconRes		= document.getElementById("conciRespa").value.toUpperCase();	// variable hidden
	vFecRes		= document.getElementById("fechaRespa").value;					// variable hidden 
	vCuenta 	= document.getElementById("idCuentabancaria").value;

	if ( vIdMov === "" ){
		mandaMensaje("Se requiere seleccionar el movimiento a conciliar");
		return false;
	}
	vConciliado = document.getElementById("idStaConci").value.toUpperCase();
	vFechaConci = document.getElementById("idFecConci").value;
	vFechaOpe	= document.getElementById("idFecha").value;	
	vCuenta 	= separaCtaBancaria(vCuenta);

	if (vConciliado==="S" && vFechaConci===""){
		mandaMensaje("Se requiere fecha de conciliación");
		return false;
	}
	if (vconRes==vConciliado && vFecRes==vFechaConci){
		mandaMensaje("No se han detectado cambios");
		return false;
	}

	if (vConciliado==="S"){
		if (vFechaConci<vFechaOpe){
			mandaMensaje("La fecha de conciliación no puedo ser anterior a la fecha de operación");
			return false;
		}
	}
	// Se asume que quiere conciliar
	if (vConciliado==="" && vFechaConci!==""){
		vConciliado ="S";
	}
	// Faltaría verificar que si esta conciliado y limpio el estatus al grabar quite la fecha de conciliación o que lo de por hecho
	aDatos = {
		opcion		: "ConciliarMovimiento",
		cuenta		: vCuenta ,
		status		: vConciliado,
		fecha		: vFechaConci ,
		id			: vIdMov
	}
	conectayEjecutaPost(aDatos,cPhp,null);

	// console.log(`Conciliado ${vConciliado} FechaConci ${vFechaConci}`);
}
// __________________________________________________________________________________
/* function CambiaLetrero(){ esta en rutinas.js
    const seleccion			= document.getElementById('opcionesFiltro');
    const etiqueta			= document.getElementById('labelFiltro');
    const inputBusqueda		= document.getElementById("campo")
    const opcionElegida		= seleccion.options[seleccion.selectedIndex].text;
    etiqueta.textContent	= opcionElegida;

    if (opcionElegida=="Fecha"){
    	inputBusqueda.type = "date";
    	inputBusqueda.value = "";
    }else{
		inputBusqueda.type  = "text";
		inputBusqueda.value = "";
    }
    inputBusqueda.focus();
} */
// __________________________________________________________________________________


// __________________________________________________________________________________
// __________________________________________________________________________________
// __________________________________________________________________________________
