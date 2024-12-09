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
var cPhp1	  = "Reportes_.php";
var dHoy	  = "";					// fecha de Hoy que regresa el servidor
var gTabla	  = "tablaSaldos";		// Tabla HTML que se esta visualizando
var cPag 	  = "-1";
var funPagina	 = ""				// Función que tendrá que ejecutar nextpage
window.onload = function () {		// Función que se ejecuta al cargar la página HTML que invoca a Consultas.js
	// Se obtiene el nombre del archivo que lo invoca
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);
    //console.log(`cHtml[${cHtml}]`);
	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// ______________________________________
		case "OpeFin07_01ConsultasSaldos.php":
			funPagina = "Saldos";
			// Hace enlace con el php Consultas_.php para ejecutar la "opcion"
			aDatos = {
				opcion 	: "CargaCuentasBancarias"
			};
			// Esta función esta en rutinas.js e invoca a la función procesarRespuesta__ que esta en este archivo
			//console.log(`Va a ejecutar ${cHtml} ${cPhp} ${aDatos}`)
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
		// __________________________________________________________________________________
		case "OpeFin07_02ConsultasMovimientos.php":
			//
			funPagina = "Movimientos";
			NumerosComasDecimales("importeIni");//,"-")
			NumerosComasDecimales("importeFin");//,"+")

			aDatos = {
				opcion 	: "CargaCuentasBancarias1"
			};
			// Esta función esta en rutinas.js e invoca a la función procesarRespuesta__ que esta en este archivo
			//console.log(`Va a ejecutar ${cHtml} ${cPhp} ${aDatos}`)
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
		// __________________________________________________________________________________
		// __________________________________________________________________________________


	}
	// __________________________________________

	// __________________________________________
}
// ********************************************************************************
// ________________________________________________________________________________
async function procesarRespuesta__(vRes) {		
	cOpc = vRes.opcion.opcion;					// Es como se recupera en PHP la opción 
    //console.log(`cOpc=${cOpc}`);
    switch(cOpc) {
    	// _____________________________________________
    	case "CargaCuentasBancarias":
            await CargaCuentasBancarias(vRes);
            await escuchaFoco(["idCuentabancaria"]);
            await FocoEn("idCuentabancaria");
		break;
    	// _____________________________________________
    	case "CargaCuentasBancarias1":
            await CargaCuentasBancarias1(vRes);
            await escuchaFoco(["idCtaIni"]);
            await FocoEn("idCtaIni");
		break;
		// _____________________________________________
		case "ConsultaSaldosBancarios":
			await ConsultaSaldosBancarios(vRes);
		break; 
		// _____________________________________________
		case "BuscaMovimientosBancarios":
			if (vRes.opcion.salida=="Pantalla"){
				await BuscaMovimientosBancarios(vRes);
			}else{
				await abrePdf(vRes.archivo);
				//mandaMensaje("Generó el arhivo???");
			}
		break;
		// _____________________________________________
		case "ReporteSaldos":
			//await mandaMensaje("Regrese de ReporteSaldos");
			await abrePdf(vRes.archivo);
		break;
		// _____________________________________________
    }
}
// ________________________________________________________________________________
function CargaCuentasBancarias(vRes){
	dHoy = vRes.opcion.hoy;
	dIni = dHoy.substring(0,8)+"01" // Inicio del mes
	//console.log(vRes);
	llenaCombo( document.getElementById("idCuentabancaria") , vRes.ctas 	);

	document.querySelector("#FechaFin").value = dHoy; // fddmmyyyy(dHoy,"-");
	document.querySelector("#FechaIni").value = dIni; // fddmmyyyy(dIni,"-");
}
// ________________________________________________________________________________
function CargaCuentasBancarias1(vRes){
	dHoy = vRes.opcion.hoy;
	dIni = dHoy.substring(0,8)+"01" // Inicio del mes
	oCtaIni = document.getElementById("idCtaIni");
	oCtaFin = document.getElementById("idCtaFin");
	//console.log(vRes);
	llenaCombo( oCtaIni , vRes.ctas 	);
	llenaCombo( oCtaFin , vRes.ctas 	);
	oCtaIni.selectedIndex = 1;
	oCtaFin.selectedIndex = oCtaFin.options.length - 1;

	document.querySelector("#FechaFin").value = dHoy; // fddmmyyyy(dHoy,"-");
	document.querySelector("#FechaIni").value = dIni; // fddmmyyyy(dIni,"-");
}
// ________________________________________________________________________________
// ________________________________________________________________________________
// ________________________________________________________________________________
// ________________________________________________________________________________
// ********************************************************************************
// ________________________________________________________________________________
function GenerarSaldos(cOpcion='Consulta'){
	oCtaBan  = {id:"idCuentabancaria",nombre:"Cuenta Bancaria"	,valor:""};
	oFecIni  = document.getElementById("FechaIni");
	oFecFin  = document.getElementById("FechaFin");
	if (seCapturo(oCtaBan)===false){
		FocoEn(oCtaBan.id);
		return false;
	}
	/*
	if ( revisaFecha(oFecIni)==false ){
		return false;
	}
	if ( revisaFecha(oFecFin)==false ){
		return false;
	} */
	if ( oFecIni > oFecFin){
		mandaMensaje("La fecha inicial no puede ser mayor a la fecha final");
		return false;
	}
	//mandaMensaje("Todo ok");
	if (cOpcion=="Consulta"){
		paginaSaldos(-1);
	}else{ // Reporte de Saldos
		cCta	 = oCtaBan.valor.split('|')[0];
		FecIni	 = oFecIni.value; // oFecIni  = fgyyyyddmm(oFecIni.value,"-");
		FecFin	 = oFecFin.value; // oFecFin  = fgyyyyddmm(oFecFin.value,"-");
		aDatos 	 = {
			opcion		: "ReporteSaldos",
			cuenta 		: cCta,
			nombre 		: oCtaBan.valor.split('|')[3],
			fechaIni	: FecIni,
			fechaFin	: FecFin
		}
		conectayEjecutaPost(aDatos,cPhp1,null);
	}
}
// ________________________________________________________________________________
function ConsultaSaldosBancarios(aRespuesta){// Regreso del PHP
	var cTabla 	 = gTabla;
	var table  	 = document.getElementById(cTabla).getElementsByTagName('tbody')[0];

	limpiaTabla(table);

	table.innerHTML = aRespuesta.opcion.regreso.data;	

    //document.getElementById("lbl-total").innerHTML 		= 'Mostrando ' + aRespuesta.opcion.regreso.totalFiltro +
    //                    							 	  ' de ' + aRespuesta.opcion.regreso.totalRegistros + ' registros'
    document.getElementById("nav-paginacion").innerHTML = aRespuesta.opcion.regreso.paginacion;
}
// ________________________________________________________________________________
function BuscaMovimientosBancarios(aRespuesta){// Regreso del PHP
	var cTabla 	 = gTabla;
	var table  	 = document.getElementById(cTabla).getElementsByTagName('tbody')[0];

	limpiaTabla(table);

	table.innerHTML = aRespuesta.opcion.regreso.data;	

    //document.getElementById("lbl-total").innerHTML 		= 'Mostrando ' + aRespuesta.opcion.regreso.totalFiltro +
    //                    							 	  ' de ' + aRespuesta.opcion.regreso.totalRegistros + ' registros'
    document.getElementById("nav-paginacion").innerHTML = aRespuesta.opcion.regreso.paginacion;

}
// ________________________________________________________________________________
function BuscarMovimientos(cPag){
	//
	pagina = document.getElementById("pagina").value;
	if (pagina===null){
		pagina = 1;
	}
	if (cPag=="-1"){ // Cambia el número de registros x pagina o se introdujo texto de búsqueda
		pagina = 1;
	}
	//
	cDato  = ""; 
	cBusca = "";
	cW     = "";
	aCampos = [
		{id:"idCtaIni"  ,titulo:"Cuenta Inicial"  ,valor:""},
		{id:"idCtaFin"  ,titulo:"Cuenta Final"    ,valor:""},
		{id:"FechaIni"  ,titulo:"Fecha Inicial"   ,valor:""},
		{id:"FechaFin"  ,titulo:"Fecha Final"     ,valor:""},
		{id:"importeIni",titulo:"Importe Inicial" ,valor:""},
		{id:"importeFin",titulo:"Importe Final"   ,valor:""},
	];
	if (faltanDatos(aCampos)){
		return false;
	}
	sBusca = ""
	cBusca = document.getElementById("idBusca").value
	if ( cBusca!==""){
		cDato = document.getElementById("idValor").value
		if (cDato===""){
			FocoEn("idValor");
			mandaMensaje(`Se requiere proporcionar información de ${cBusca}`);
			return false
		}else{
			cW = " and " + cBusca + " like '%" + cDato + "%' ";
			sBusca = cBusca + "[" + cDato + "]";
		}
	}
	cSalida  = document.getElementById("idSalida").value
	cCtaIni  = aCampos[0].valor.split('|')[0];
	cCtaFin  = aCampos[1].valor.split('|')[0];
	cFecIni  = aCampos[2].valor; //fgyyyyddmm(aCampos[2].valor,"/");
	cFecFin  = aCampos[3].valor; // fgyyyyddmm(aCampos[3].valor,"/");
	cImpIni	 = aCampos[4].valor.replace(/,/g, '');
	cImpFin	 = aCampos[5].valor.replace(/,/g, '');
	cCampos  = "a.idcuentabancaria,a.folio,a.referenciabancaria,a.importeoperacion,a.fechaoperacion,a.beneficiario,"
	cCampos += "a.concepto,a.idunidad,a.idoperacion,a.idcontrol,a.anioejercicio,a.idmovimiento,a.estatus,a.usuarioalta,a.fechaalta"
	cTipos   = "C,C,C,N,D,C,C,C,C,C,C,N,C,C,D"; // C - Caracter , N Número , D - Fecha

	where    =  "a.idcuentabancaria>='"+cCtaIni+ "' and a.idcuentabancaria<='"+cCtaFin+ "' and " +
				"a.importeoperacion>=" +cImpIni+ "  and a.importeoperacion<=" +cImpFin+ "  and "  +
				"a.fechaoperacion>='"  +cFecIni+ "' and a.fechaoperacion<='"  +cFecFin+ "' ";

	if (cW!==""){
		where += cW;
	}
	aDatos = {
		opcion 			: "BuscaMovimientosBancarios",
		limite			: document.getElementById("num_registros").value,
		busca			: document.getElementById("campo").value,
		pagina 			: pagina,
		tabla			: "movimientos a ",
		tablaPrin		: "movimientos",
		join			: where,
		campos			: cCampos,
		tipos			: cTipos,
		id 				: "idcontrol",
		regreso			: "",
		order			: "order by fechaoperacion desc, idcuentabancaria",
		depura			: "",
		salida			: cSalida,
		traeOperaciones	: true,
		aCampos			: ["a.idcuentabancaria","a.folio","a.referenciabancaria","a.importeoperacion","a.fechaoperacion","a.beneficiario","a.concepto",
						   "a.idunidad","a.idoperacion","a.idcontrol","a.anioejercicio","a.idmovimiento","a.estatus","a.usuarioalta","a.fechaalta"],
		fechaIni		: cFecIni,
		fechaFin		: cFecFin,
		busqueda		: sBusca
	};
	//
	conectayEjecutaPost(aDatos,cPhp,null);
}
// ________________________________________________________________________________
function faltanDatos(aCampos){
	for (i=0;i<aCampos.length;i++){
		if( seCapturo(aCampos[i])===false){
			FocoEn(aCampos[i].id);
			mandaMensaje("Falta información de "+aCampos[i].titulo)
			return true;
		}
	}
	return false;
}
// ________________________________________________________________________________
function nextPage(pagina){
    document.getElementById('pagina').value = pagina
    if (funPagina==="Saldos"){
    	paginaSaldos("0");
    }else if(funPagina==="Movimientos"){
		BuscarMovimientos("0");
    }
}
// ________________________________________________________________________________
function paginaSaldos(cPag){
	oCtaBan  = {id:"idCuentabancaria",nombre:"Cuenta Bancaria"	,valor:""};
	oFecIni  = document.getElementById("FechaIni");
	oFecFin  = document.getElementById("FechaFin");
	cCta	 = document.getElementById("idCuentabancaria").value.split('|')[0];
	oFecIni	 = oFecIni.value; // oFecIni  = fgyyyyddmm(oFecIni.value,"-");
	oFecFin  = oFecFin.value; // oFecFin  = fgyyyyddmm(oFecFin.value,"-");


	pagina = document.getElementById("pagina").value;
	if (pagina===null){
		pagina = 1;
	}
	if (cPag=="-1"){ // Cambia el número de registros x pagina o se introdujo texto de búsqueda
		pagina = 1;
	}
	/*
	TO_CHAR(saldoinicial, '9,999,999,999') AS saldoinicial, no funciono por que en pagina y busca se hace un explode con comas
    TO_CHAR(ingresos, '9,999,999,999') AS ingresos,
    TO_CHAR(egresos, '9,999,999,999') AS egresos,
    TO_CHAR(cheques, '9,999,999,999') AS cheques,
    TO_CHAR(saldoinicial + ingresos - egresos - cheques, '9,999,999,999') AS saldofinal
	*/
	cCampos  =  "a.idcuentabancaria, a.fechasaldo, to_char(a.saldoinicial,'9,999,999,990.99') as saldoinicial, " ;
	cCampos  += "to_char(a.ingresos,'9,999,999,990.99') as ingresos, to_char(a.egresos,'9,999,999,990.99') as egresos, ";
	cCampos  += "to_char(a.cheques,'9,999,999,990.99') as cheques ,";
	cCampos  += "to_char((a.saldoinicial + a.ingresos - a.egresos - a.cheques),'9,999,999,990.99')  as saldofinal ";
	//cCampos += "a.concepto,a.idunidad,a.idoperacion,a.idcontrol,a.anioejercicio,a.idmovimiento,a.estatus,a.usuarioalta,a.fechaalta"
	cTipos   = "C,D,N,N,N,N,T"; // C - Caracter , N Número , D - Date , Cualquier otra letra no buscara , debe por lo menos ir un C o un N
	//
	aDatos = {
		opcion 			: "ConsultaSaldosBancarios",
		limite			: document.getElementById("num_registros").value,
		busca			: document.getElementById("campo").value,
		pagina 			: pagina,
		tabla			: " saldos a ",
		tablaPrin		: "saldos",
		join			: " idcuentabancaria='"+cCta+"' and fechasaldo>='"+oFecIni+"' and fechasaldo<='"+oFecFin+"'",
		campos			: cCampos,
		tipos			: cTipos,
		id 				: "idCuentabancaria",
		regreso			: "",
		order			: "order by fechasaldo desc",
		depura			: "",
		traeOperaciones	: true,
		aCampos			: ["a.idcuentabancaria","a.fechasaldo","a.saldoinicial","a.ingresos","a.egresos","a.cheques","saldofinal"]
	};
	//
	conectayEjecutaPost(aDatos,cPhp,null);

}
// ________________________________________________________________________________
const ReporteSaldos = () =>{
	GenerarSaldos("Reporte");
}
// ________________________________________________________________________________