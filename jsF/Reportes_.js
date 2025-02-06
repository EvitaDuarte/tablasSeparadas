/*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Autor   		: Miguel Ángel Bolaños Guillén        	*
 * Sistema 		: Sistema de Operación Bancaria Web   	*
 * Fecha   		: Diciembre 2023                       	*
 * Descripción 	: Rutinas para realizar el enlace 		* 
 *                entre JavaScript y PHP           		*
 *                para los Movimientos bancarios		*
 *                Unadm-Proyecto Terminal para INE 		*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*/
var cPhp      	 = "Consultas_.php";	// En este php estarán las funciones que se invocaran desde este JS
var cPhp1	  	 = "Reportes_.php";	// Se separa la parte de los reportes
var dHoy	  	 = "";					// fecha de Hoy que regresa el servidor
var gTabla	  	 = "";					// Tabla HTML que se esta visualizando
var gForma	  	 = "";
var cPag 	  	 = "-1";				// Inicialización páginado servidor
var funPagina 	 = ""					// Función que tendrá que ejecutar nextpage
var dHoy	  	 = ""
var pagCta	  	 = "";					// Guarda la cuenta Bancaria para el paginado
var lTurno	  	 = false;
var lOkCheque 	 = 0;					// Error en los rangos de cheque
var gLongChe  	 = 8;
var gResultLines = [];					// Arreglo global g
var gOfcCtasInt	 = "";					// 

window.onload = function () {		// Función que se ejecuta al cargar la página HTML que invoca a Consultas.js
	// Se obtiene el nombre del archivo que lo invoca
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);
    //con sole.log(`cHtml[${cHtml}]`);
	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// ______________________________________
		case "OpeFin08_01EdoPosFinDia.php":
			aDatos = {
				opcion 	: "CargaCuentasBancarias0"
			};
			// Esta función esta en rutinas_.js e invoca a la función procesarRespuesta__ que esta en este archivo
			//con sole.log(`Va a ejecutar ${cHtml} ${cPhp} ${aDatos}`)
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
		// __________________________________________________________________________________
		case "OpeFin08_02EdoPosMensual.php":
			aDatos = {
				opcion 	: "CargaCuentasBancarias1"
			};
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
		// __________________________________________________________________________________
		// __________________________________________________________________________________
		case "OpeFin08_03Consolidado.php":
			gForma = "ConsoGral";
			quitaSubmit(gForma);
			aDatos = {
				opcion 	: "FechaHoy"
			};
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
		// __________________________________________________________________________________
		case "OpeFin08_04ImpCheques.php":
			gTabla = "tablaMovimientos";
			gForma = "RanCheImp";
			quitaSubmit(gForma);
			aDatos = {
				opcion 	: "CargaCuentasBancarias2"
			};
			// Esta función esta en rutinas.js e invoca a la función procesarRespuesta__ que esta en este archivo
			//con sole.log(`Va a ejecutar ${cHtml} ${cPhp} ${aDatos}`)
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
		// __________________________________________________________________________________
		case "OpeFin08_05Reportes.php":
			gTabla = "tablaMovimientos";
			gForma = "Reportes";
			aDatos = {
				opcion 	: "CargaCuentasBancarias"
			};
			// Esta función esta en rutinas.js e invoca a la función procesarRespuesta__ que esta en este archivo
			//con sole.log(`Va a ejecutar ${cHtml} ${cPhp} ${aDatos}`)
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
		// __________________________________________________________________________________
		case "OpeFin08_06Exportar.php":
			// Nada por el momento
			aDatos = {
				opcion : "CtasOF16"
			};
			conectayEjecutaPost(aDatos,cPhp1,null);
		break;
		// __________________________________________________________________________________
		default:
			mandaMensaje("No esta codificado el init de "+cHtml);
		break;

	}
	// __________________________________________

	// __________________________________________
}
// ********************************************************************************
// __________________________REGRESOS DE PHP _____________________________________
async function procesarRespuesta__(vRes) {		
	cOpc = vRes.opcion.opcion;					// Es como se recupera en PHP la opción 
    //con sole.log(`cOpc=[${cOpc}]`);
    switch(cOpc) {
    	// _____________________________________________
    	case "CargaCuentasBancarias0":
            await rCargaCuentasBancarias0(vRes);
            await escuchaFoco(["idCuentabancaria"]);
            await FocoEn("idCuentabancaria");
			llenaFechaHoy("FechaIni",vRes.opcion.hoy);
		break;
    	// _____________________________________________
    	case "CargaCuentasBancarias":
            await rCargaCuentasBancarias(vRes);
            await escuchaFoco(["idCuentabancaria"]);
            await FocoEn("idCuentabancaria");
			llenaFechaHoy("FechaIni",vRes.opcion.hoy,true); // Pone el primer día del mes corriente
			llenaFechaHoy("FechaFin",vRes.opcion.hoy);
		break;
    	// _____________________________________________
    	case "CargaCuentasBancarias1":
            await rCargaCuentasBancarias1(vRes);
        //    await escuchaFoco(["idCtaIni"]);
        //    await FocoEn("idCtaIni");
		break;
		// _____________________________________________
    	case "CargaCuentasBancarias2":
            await rCargaCuentasBancarias2(vRes);
            await escuchaFoco(["idCuentabancaria"]);
            await FocoEn("idCuentabancaria");
		break;
    	// _____________________________________________
    	//case "CargaCuentasBancarias1": No me acuerdo por que lo puse aqui
		case "FechaHoy":
			dHoy = vRes.resultados.Hoy;
			document.getElementById("FechaIni").value = vRes.resultados.Hoy; //  vRes.resultados.fRep;
		break; 
		// _____________________________________________
		case "EdoPosFinDia":
			await abrePdf(vRes.archivo);
		break;
		// _____________________________________________
		case "EdoPosFinMensual":
			await abrePdf(vRes.archivo);
		break;
		// _____________________________________________
		case "ConsolidadoGeneral":
			//con sole.log("Consolidado General vRes=["+vRes+"]");
			await abrePdf(vRes.archivo);
		break;
		// _____________________________________________
		case "ConsultaMovimientosBancarios":
			await ConsultaMovimientosBancarios(vRes);
		break; 
		// _____________________________________________
		case "existeCheque":
			lOkCheque = lOkCheque + 1;
			// nothing to do
		break;
		// _____________________________________________
		case "ImpresionRangoCheques":
			await abrePdf(vRes.archivo);
		break;
		// _____________________________________________
		case "EdoCta":
			if (vRes.datos.salida=="Pdf"){
				await abrePdf(vRes.archivo);
			}else{
				await abrePdf(vRes.archivo);
			}
		break;
		// _____________________________________________
		case "CtasOF16":
			gOfcCtasInt = vRes.resultados[0].ctas_intereses;
			// mandaMensaje(gOfcCtasInt);
		break;
		// _____________________________________________
		case "InteresesPdf":
			await abrePdf(vRes.archivo);
		break;
		// _____________________________________________
		default:
			mandaMensaje("No esta codificado el regreso JS de [" + cOpc +"]" );
		break;
		// _____________________________________________
    }
}
// __________________________REGRESOS DE PHP ______________________________________
async function procesarError__(vRes) {		
	cOpc = vRes.opcion.opcion;					// Es como se recupera en PHP la opción 
    //con sole.log(`cOpc=${cOpc}`);
    switch(cOpc) {
		case "existeCheque":
			document.getElementById(vRes.datos.idCheque).value = cerosIzquierda("",gLongChe);
			FocoEn(document.getElementById(vRes.datos.idCheque));
		break;
    }
}
// __________________________REGRESOS DE PHP ______________________________________
function rCargaCuentasBancarias0(vRes){
	dHoy = vRes.opcion.hoy;
	// con sole.log("Hoy: "+dHoy);
	llenaCombo( document.getElementById("idCuentabancaria") , vRes.ctas );
	document.querySelector("#FechaIni").value = dHoy; // fddmmyyyy(dHoy,"-");
}
// ________________________________________________________________________________
function rCargaCuentasBancarias(vRes){
	dHoy = vRes.opcion.hoy;
	// con sole.log("Hoy: "+dHoy);
	llenaCombo( document.getElementById("idCuentabancaria") , vRes.ctas );
	document.querySelector("#FechaIni").value = dHoy; // fddmmyyyy(dHoy,"-");
}
// ________________________________________________________________________________
function rCargaCuentasBancarias1(vRes){
	llenaCombo( document.getElementById("idCuentabancaria") , vRes.ctas );
	document.getElementById("idAnio").value = new Date().getFullYear();
	document.getElementById("idMes").value  = formatoMes(new Date().getMonth()+1);
}
// ________________________________________________________________________________
function rCargaCuentasBancarias2(vRes){
	llenaCombo( document.getElementById("idCuentabancaria") , vRes.ctas );
	document.getElementById("idCheIni").value = cerosIzquierda("",gLongChe);
	document.getElementById("idCheFin").value = cerosIzquierda("",gLongChe);
}
// ________________________________________________________________________________
// ********************************************************************************
function GeneraPosFinDia(){
	cCta = valorDeObjeto("idCuentabancaria");
	if (cCta!=null){
		aCta	= cCta.split("|");
		cFecha	= valorDeObjeto("FechaIni");
		if (cFecha!=null){
			cSalida	= valorDeObjeto("idSalida");
			aDatos = {
				opcion 	: "EdoPosFinDia",
				cCta 	: aCta[0],
				cNombre : aCta[3],
				cFecha	: cFecha , // fgyyyyddmm(cFecha,"/"),
				cFecha1 : cFecha ,
				salida	: cSalida
			}
			conectayEjecutaPost(aDatos,cPhp1,null);
		}
	}
}
// ________________________________________________________________________________
function GeneraPosFinMes(){
	cCta = valorDeObjeto("idCuentabancaria");
	if (cCta!=null){
		aCta  = cCta.split("|");
		cAnio = valorDeObjeto("idAnio");
		if (cAnio!=null){
			cMes = valorDeObjeto("idMes");
			if ( cMes!= null ){
				cSalida	= valorDeObjeto("idSalida");
				// --------------------------
				aDatos = {
					opcion 	: "EdoPosFinMensual",
					cCta 	: aCta[0],
					cNombre : aCta[3],
					cAnio	: cAnio,
					cMes    : cMes,
					sMes	: textoSelect("idMes"),
					cDias	: obtenerDiasEnMes(cAnio, cMes),
					salida	: cSalida
				}
				conectayEjecutaPost(aDatos,cPhp1,null);
				// --------------------------
			}
		}
	}
}
// ________________________________________________________________________________
function ReporteConsolidadoGeneral(){
	cFecha = valorDeObjeto("FechaIni");
	cTipo  = valorRadio("filCta","Filtro Cuenta");
	if (cFecha!=null && cTipo !=null){
		aDatos = {
			opcion  : "ConsolidadoGeneral",
			cFecha  : cFecha, // fgyyyyddmm(cFecha,"/"),
			cFecha1 : formatearFecha(cFecha),
			cTipo   : cTipo,
			dummy	: "nada"
		}
		conectayEjecutaPost(aDatos,cPhp1,null);
	}
}
// ________________________________________________________________________________
const refrescaMovimientos = () =>{
	cCta = document.getElementById("idCuentabancaria").value.trim();
	if (cCta===""){
		FocoEn("idCuentabancaria");
		mandaMensaje("Seleccione Cuenta Bancaria");
		FocoEn("idCuentabancaria");
		return false;
	}
	pagCta = cCta.split('|')[0];
	paginaMovimientos(-1);
}
// ________________________________________________________________________________
const paginaMovimientos = (cPag)=>{
	cCta   = pagCta; // pagCta se inicializa en el onchange del select de cuentas bancarias
	pagina = document.getElementById("pagina").value;
	if (pagina===null){
		pagina = 1;
	}
	if (cPag=="-1"){ // Cambia el número de registros x pagina o se introdujo texto de búsqueda
		pagina = 1;
	}
	cCampos  = "a.idcuentabancaria,a.folio,a.importeoperacion,a.referenciabancaria,a.beneficiario,a.fechaoperacion ";
	cTipos   = "C,C,N,C,C,D"; // C - Caracter , N Número , D - Fecha
	//
	aDatos = {
		opcion 			: "ConsultaMovimientosBancarios",
		limite			: document.getElementById("num_registros").value,
		busca			: document.getElementById("campo").value,
		pagina 			: pagina,
		tabla			: " movimientos a ",
		tablaPrin		: "movimientos",
		join			: " idCuentabancaria='"+cCta+"' and idoperacion='CHE' and estatus!='C' ",
		campos			: cCampos,
		tipos			: cTipos,
		id 				: "idmovimiento",
		regreso			: "",
		order			: "order by fechaoperacion desc , referenciabancaria desc",
		depura			: "",
		traeOperaciones	: true,
		aCampos			: ["a.idcuentabancaria","a.folio","a.importeoperacion","a.referenciabancaria","a.beneficiario","a.fechaoperacion"],
		where			: " idCuentabancaria='"+cCta+"' and idoperacion='CHE' and estatus=' '" // Hay una condición para ver si toma o no el where 
	};
	//
	conectayEjecutaPost(aDatos,cPhp1,null);
}
// ________________________________________________________________________________
function ConsultaMovimientosBancarios(aRespuesta){ // Es el regreso del PHP
	var cTabla 	 = gTabla;
	var table  	 = document.getElementById(cTabla).getElementsByTagName('tbody')[0];

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
				rangoCheque(this.cells[3].textContent);

		    	//cChe = this.cells[3].textContent;

		  	});
		}
	}
    //document.getElementById("lbl-total").innerHTML = 'Mostrando ' + aRespuesta.opcion.regreso.totalFiltro +
    //                    							 ' de ' + aRespuesta.opcion.regreso.totalRegistros + ' registros'
    document.getElementById("nav-paginacion").innerHTML = aRespuesta.opcion.regreso.paginacion;
}
// ________________________________________________________________________________
const rangoCheque= (cCheque) =>{
	cCeros  = "00000000";
	cCheIni = document.getElementById("idCheIni").value;
	cCheFin = document.getElementById("idCheFin").value;
	if (cCheIni==cCeros){
		if (cCheFin==cCeros){
			document.getElementById("idCheIni").value = cCheque;
		}else{
			if ( cCheque > cCheFin ){
				document.getElementById("idCheIni").value = cCheFin
				document.getElementById("idCheFin").value = cCheque

			}else{
				document.getElementById("idCheIni").value = cCheque;
			}
		}
	}else{ // cCheIni != ceros
	 	if (cCheFin==cCeros){
			if ( cCheque<cCheIni){
				document.getElementById("idCheIni").value = cCheque;
				document.getElementById("idCheFin").value = cCheIni;
			}else{
				document.getElementById("idCheFin").value = cCheque;
			}
		}else{ // cCheFin != ceros cCheque debe sustituir al mayor o al menor ??
			if ( lTurno ){
				document.getElementById("idCheIni").value = cCheque;
			}else{
				document.getElementById("idCheFin").value = cCheque;
			}
			if (document.getElementById("idCheIni").value > document.getElementById("idCheFin").value ){
				cCheque = document.getElementById("idCheIni").value
				document.getElementById("idCheIni").value = document.getElementById("idCheFin").value;
				document.getElementById("idCheFin").value = cCheque;
			}
			lTurno = !lTurno;
		}
	}
}
// ________________________________________________________________________________
const existeCheque = (oCheque) => {
	if (pagCta===""){
		mandaMensaje("Se requiere seleccionar cuenta");
		oCheque.value = cerosIzquierda("",gLongChe);
		FocoEnObjeto(oCheque);
		return false;
	}
	oCheque.value = cerosIzquierda(oCheque.value.trim(),gLongChe);
	aDatos = {
		opcion		: "existeCheque",
		idCuenta	: pagCta,
		idCheque	: oCheque.id,
		numCheque	: oCheque.value
	}
	conectayEjecutaPost(aDatos,cPhp1,null);
}
// ________________________________________________________________________________
const ImpresionRangoCheques = () =>{
	cCheIni = document.getElementById("idCheIni").value;
	cCheFin = document.getElementById("idCheFin").value;
	cCeros	= cerosIzquierda("",gLongChe);
	cCta	= valorDeObjeto("idCuentabancaria");
	if (cCta!==null){
		if (cCheIni==cCeros){
			FocoEn("idCheIni");
			mandaMensaje("El cheque Inicial no puede ser ceros");
			FocoEn("idCheIni");
			return false;
		}
		if (cCheFin==cCeros){
			FocoEn("idCheFin");
			mandaMensaje("El cheque Final no puede ser ceros");
			FocoEn("idCheFin");
			return false;
		}
		aDatos={
			opcion		: "ImpresionRangoCheques",
			idCuenta	: pagCta,
			cheIni		: cCheIni,
			cheFin		: cCheFin
		}
		conectayEjecutaPost(aDatos,cPhp1,null);
	}
}
// ________________________________________________________________________________
const refrescaPantalla = () =>{

}
// ________________________________________________________________________________
const reporteSolicitado = () =>{
	cReporte = valorDeObjeto("idReporte");
	if (cReporte!=null){
		cCta = valorDeObjeto("idCuentabancaria");
		if (cCta!=null){
			cSalida = valorDeObjeto("idSalida");
			aDatos ={
				opcion : cReporte,
				cCta   : separaCtaBancaria(cCta,0), // Se requiere que la propiedad sea cCta para traer saldoAnterior
				cFecha : valorDeObjeto("FechaIni"), // Para traer el Saldo Anterior a la Fecha Inicial
				nombre : separaCtaBancaria(cCta,3),
				fechaI : valorDeObjeto("FechaIni"),
				fechaF : valorDeObjeto("FechaFin"),
				salida : cSalida
			}
			conectayEjecutaPost(aDatos,cPhp1,null);
		}
	}
}
// ________________________________________________________________________________
const expoArch = () =>{
	cOpc = document.getElementById("idExportar").value
	switch(cOpc){
		// _____________________________
		case "":
		break;
		// _____________________________
		case "Intereses":
			document.getElementById('ArchivoCarga_file').value = "";
			document.getElementById("input_text").textContent = "Seleccione Archivo de Intereses";
			archivoLayOut(cOpc,"intereses");
		break;
		// _____________________________
		case "Respuesta":
			document.getElementById('ArchivoCarga_file').value = "";
			document.getElementById("input_text").textContent = "Seleccione Archivo de Respuesta Intereses";
			archivoLayOut(cOpc,"respuesta de intereses");
		break;
		// _____________________________
	}
}
// ________________________________________________________________________________
const archivoLayOut = (cOpc,cTit) =>{	// Solicita archivo de layOut
	solicitaArchivoLayOut().then((respuesta) => {
		if (respuesta){ // Segun yo siempre regresa true
			var input1_file = document.getElementById('ArchivoCarga_file');
			if (input1_file.files.length >0){
				var oFile		= input1_file.files[0];
				cFile 			= oFile.name;
				
				esperaRespuesta(`Desea iniciar carga de ${cTit} de ${cFile} `).then((respuesta) => {
					if (respuesta){
						const reader  = new FileReader();
						reader.onload = function (e) {
							const txtContent = e.target.result;
							if (cOpc==="Intereses"){
								procesarTxtIntereses(txtContent);
							}else if(cOpc==="Respuesta"){
								procesarTxtRespuesta(txtContent);
							}
						};
						if (cOpc=="Intereses" || cOpc=="Respuesta"){ // Usar para txt o csv
							reader.readAsText(oFile, 'UTF-8');
						}else if (cOpc=="EX" || cOpc=="CX"){ // Usar para un XLS
							reader.readAsArrayBuffer(oFile);
						}
					}
				});
			}else{
				mandaMensaje("No se ha seleccionado archivo");
				document.getElementById("idExportar").value = "";
			}
		}
	});
}
// ________________________________________________________________________________
const procesarTxtIntereses = (txtContent) =>{
	// Paso 1: Dividir el contenido por líneas
	let lines = txtContent.split('\n');

	let instituteLine	= null;
	gResultLines		= [];
	let listaUrs		= [];
	oFecha				= obtenerFechaYHora();
	//cFecha			= oFecha.fechaFormateada;
	//cHora				= oFecha.horaFormateada;


	// Paso 2: Iterar sobre las líneas y generar las combinaciones
	cSucOri = ""; cCta = ""; cCta1 = "";
	lines.forEach(line => {
		let columns = line.split('|');

		// Condición 1: Buscar líneas con "INSTITUTO NACIONAL ELECTORAL" en la cuarta columna
		if (columns.length >= 4 && columns[3].trim() === 'INSTITUTO NACIONAL ELECTORAL') {
			cSucOri = columns[7].trim().padStart(4,"0");
			cCta1	= columns[8].trim();
			cCta	= cCta1.padStart(20,"0");

			cUr		= columns[9].substring(0,4);
			cHora	= columns[1].replace(":","");
		}

		// Condición 2: Buscar líneas con "A" en la tercera columna y "73" en la cuarta columna
		if (columns.length >= 4 && columns[2].trim() === 'A' && columns[3].trim() === '73') {
			if (gOfcCtasInt.includes(cCta1)){
				// Excluir oficinas Centrales
			}else{
		    	cImpo = columns[8];
		    	cImpo = cImpo.replace(/,/g, '').replace('.', '').padStart(14, '0');
		    	cFecha= columns[1].split("/");
		    	cFecha= cFecha[2]+cFecha[1];

		    	linea={
		    		a01_tiptra: "01",
		    		a02_ctaOri: "01", 
		    		a03_sucOri: cSucOri,
		    		a04_cuenta: cCta,
		    		a05_ctaDes: "01",
		    		a06_sucOfc: "7001",
		    		a07_ctaIne: "00000000000003854775",
		    		a08_Impo  : cImpo,
		    		a09_Moneda: "001",
		    		a10_Ur	  : cUr,
		    		a11_Fecha : cFecha,
		    		a12_Hora  : cHora
		    	};
		    	gResultLines.push(linea); // Agregamos la nueva línea combinada al resultado
		    }
	    	cSucOri = ""; cCtaOri = ""; cCta1 = "" ; cCta= "";
		}
	});	
	// Ordenar arreglo
	gResultLines.sort((a, b) => {
		if (a.a10_Ur < b.a10_Ur) {
			return -1;  // Si a es menor que b, se coloca antes
		}
		if (a.a10_Ur > b.a10_Ur) {
			return 1;   // Si a es mayor que b, se coloca después
		}
		return 0;  // Si son iguales, no se cambian de lugar
	});	
	//
	//console.log(gResultLines);

	// Paso 3: Poblar la tabla en HTML
	const cuerpoTabla = document.getElementById('cuerpo'); // Obtener el cuerpo de la tabla

	// Limpiar el cuerpo de la tabla antes de agregar nuevos datos
	cuerpoTabla.innerHTML = '';

	// Iterar sobre cada línea de gResultLines y agregar una fila (tr) con celdas (td)
	gResultLines.forEach(linea => {
		let fila = document.createElement('tr');

		// Crear las celdas de la fila según los valores de la línea
		Object.values(linea).forEach(valor => {
			let celda = document.createElement('td');
			celda.textContent = valor; // Asignamos el valor de la celda
			fila.appendChild(celda); // Añadimos la celda a la fila
		});

		// Añadir la fila al cuerpo de la tabla
		cuerpoTabla.appendChild(fila);
	});

	// Eliminar la columna 'a10_Ur' de cada línea en gResultLines
	//gResultLines.forEach(linea => {
	//    delete linea.a10_Ur;
	//});

	// Llamada a la función para generar y descargar el archivo
	generarArchivoTXT(gResultLines);
	expoPdf();
	document.getElementById("idExportar").value = "";

/*
	// Paso 2: Filtrar las líneas que cumplan con las condiciones
	let aLineas = [];
	let filteredLines = lines.filter(line => {
		// Dividir cada línea en columnas usando "|" como separador
		let columns = line.split('|');

		// Verificar si la línea tiene al menos 4 columnas (para evitar errores)
		if (columns.length >= 4) {
	    	// Condición 1: Comprobar si la cuarta columna es "INSTITUTO NACIONAL ELECTORAL"
	    	if (columns[3].trim() === 'INSTITUTO NACIONAL ELECTORAL') {
	    		aLineas.push(columns);
	      		return true;
	    	}

	    	// Condición 2: Comprobar si la tercera columna es "A" y la cuarta columna es "73"
	    	if (columns[2].trim() === 'A' && columns[3].trim() === '73') {
	    		aLineas.push(columns);
	      		return true;
	    	}
		}

		// Si no se cumple ninguna de las condiciones, se descarta la línea
		return false;
	});
	console.log(aLineas);
	let instituteLine 	= null;
	let gResultLines 	= [];	

	aLineas.forEach((linea)=>{

	});

			 vRes.unidades.forEach((unidad) => {
		 	UrSet.add(unidad["descripcion"].trim()); 

		 });
	let processedLines = filteredLines.map(line => {
		let columns = line.split('|');
		var cSucOri;
  		if (columns[03].trim()=="INSTITUTO NACIONAL ELECTORAL"){
  			cSucOri = columns[7];
  		}else{ // A 73

  		}
		return {
			tipTra: '01',
			ctaOri: '01',
			sucOri: cSucOri
			codigo: columns[2],
			entidad: columns[3],
			otroCampo: columns[4],
		// Aquí puedes agregar los demás campos que necesites
		};
	});

	console.log(processedLines); */
}
// ________________________________________________________________________________
const generarArchivoTXT = (resultLines) => {
	let contenidoArchivo = '';

	// Paso 1: Iterar sobre resultLines y generar el contenido del archivo
	resultLines.forEach(linea => {
		let lineaFormateada = '';

		// Concatenar cada valor de la línea en una sola cadena sin separadores ni espacios
		lineaFormateada += linea.a01_tiptra;
		lineaFormateada += linea.a02_ctaOri;
		lineaFormateada += linea.a03_sucOri;
		lineaFormateada += linea.a04_cuenta;
		lineaFormateada += linea.a05_ctaDes;
		lineaFormateada += linea.a06_sucOfc;
		lineaFormateada += linea.a07_ctaIne;
		lineaFormateada += linea.a08_Impo;
		lineaFormateada += linea.a09_Moneda;
		lineaFormateada += linea.a11_Fecha;
		lineaFormateada += linea.a12_Hora;

		// Añadir la línea al contenido del archivo, seguida de un salto de línea
		contenidoArchivo += lineaFormateada + '\n';
	});

	// Paso 2: Crear un blob con el contenido y permitir la descarga del archivo
	const blob = new Blob([contenidoArchivo], { type: 'text/plain' });
	const url = URL.createObjectURL(blob);

	// Crear un enlace para la descarga
	const a = document.createElement('a');
	a.href = url;
	a.download = `in${cFecha}${cHora}.txt`; // Generar el nombre del archivo basado en la fecha y hora
	document.body.appendChild(a); // Añadir el enlace al DOM (para que funcione en algunos navegadores)
	a.click(); // Hacer clic en el enlace para descargar el archivo
	document.body.removeChild(a); // Eliminar el enlace después de la descarga
	URL.revokeObjectURL(url); // Revocar la URL después de usarla
};
// ________________________________________________________________________________
const obtenerFechaYHora = () => {
	const fecha = new Date();

	// Obtener el año (últimos dos dígitos)
	const year = fecha.getFullYear().toString().slice(-2); // '25' para 2025

	// Obtener el mes y el día (asegurarse de que tengan 2 dígitos)
	const mes = (fecha.getMonth() + 1).toString().padStart(2, '0'); // '01' para enero
	const dia = fecha.getDate().toString().padStart(2, '0'); // '24' para el 24 de enero

	// Obtener la hora y los minutos (asegurarse de que tengan 2 dígitos)
	const hora = fecha.getHours().toString().padStart(2, '0'); // '13' para 1:00 PM
	const minutos = fecha.getMinutes().toString().padStart(2, '0'); // '30' para 30 minutos

	// Concatenar todo en el formato requerido
	const fechaFormateada = `${year}${mes}${dia}`;
	const horaFormateada = `${hora}${minutos}`;

	return { fechaFormateada, horaFormateada };
};
// ________________________________________________________________________________
const expoPdf = () =>{
	cOpcion = document.getElementById("idExportar")
	if (gResultLines.length==0){
		mandaMensaje("No se ha cargado el archivo TXT");
		return false;
	}
	aDatos={
		opcion		: "InteresesPdf",
		aRes		: gResultLines
	}
	conectayEjecutaPost(aDatos,cPhp1,null);

}
// ________________________________________________________________________________
const procesarTxtRespuesta = (txtContent) =>{
	// Paso 1: Dividir el contenido por líneas
	let lines = txtContent.split('\n');
	mandaMensaje(lines);
}
// ________________________________________________________________________________
// ________________________________________________________________________________