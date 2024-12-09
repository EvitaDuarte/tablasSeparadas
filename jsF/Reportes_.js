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
var cPhp      = "Consultas_.php";	// En este php estarán las funciones que se invocaran desde este JS
var cPhp1	  = "Reportes_.php";	// Se separa la parte de los reportes
var dHoy	  = "";					// fecha de Hoy que regresa el servidor
var gTabla	  = "";					// Tabla HTML que se esta visualizando
var gForma	  = "";
var cPag 	  = "-1";				// Inicialización páginado servidor
var funPagina = ""					// Función que tendrá que ejecutar nextpage
var dHoy	  = ""
var pagCta	  = "";					// Guarda la cuenta Bancaria para el paginado
var lTurno	  = false;
var lOkCheque = 0;				// Error en los rangos de cheque
var gLongChe  = 8;

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
				opcion 	: "CargaCuentasBancarias"
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
    //con sole.log(`cOpc=${cOpc}`);
    switch(cOpc) {
    	// _____________________________________________
    	case "CargaCuentasBancarias":
            await rCargaCuentasBancarias(vRes);
            await escuchaFoco(["idCuentabancaria"]);
            await FocoEn("idCuentabancaria");
			llenaFechaHoy("FechaIni",vRes.opcion.hoy,true);
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
			console.log("Consolidado General vRes=["+vRes+"]");
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
function rCargaCuentasBancarias(vRes){
	dHoy = vRes.opcion.hoy;
	//con sole.log(vRes);
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

	if (cCheIni==cCeros){
		FocoEn("idCheIni");
		mandaMensaje("El cheque Inicial no pude ser ceros");
		FocoEn("idCheIni");
		return false;
	}
	if (cCheFin==cCeros){
		FocoEn("idCheFin");
		mandaMensaje("El cheque Final no pude ser ceros");
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
// ________________________________________________________________________________