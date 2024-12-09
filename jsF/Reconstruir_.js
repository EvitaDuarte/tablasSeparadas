// * * * * * * * * * * * * * * * * * * * * * * * * * 
// * Autor   : Miguel Ángel Bolaños Guillén        *
// * Sistema : Sistema de Operación Bancaria Web   *
// * Fecha   : Abril 2024						   *
// * Descripción : Rutinas para realizar el enlace * 
// *               entre JavaScript y PHP          *
// * * * * * * * * * * * * * * * * * * * * * * * * *
var cPhp    	 = "Reconstruir_.php";	// En este php estarán las funciones que se infocaran desde este JS
var gHoy		 = "";				// Fecha del servidor
var gYear		 = "";				// Año del servidor
var gInicio		 = "";				// 01 de enero del año actual
window.onload = function () {
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);
	// Casos en que se requiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
// 		__________________________________________________________________________
		case "OpeFin01_05Regenerar.php":
			gForma 	 	= "frmRestaura";

			aDatos = {
				opcion 	: "CargaCuentasBancarias"
			};
			quitaSubmit(gForma);
			conectayEjecutaPost(aDatos,"Consultas_.php",null);// Esta función esta en rutinas.js
		break;
// 		__________________________________________________________________________
	}
};
// * * * * * * * * * * * * * * * * * * *
// * Realizar el enlace entre JS y PHP *
// * * * * * * * * * * * * * * * * * * *
// _______________________________________________________________________
// Define una promesa para esta función, despues de ejecutar la opción correspondiente en PHP
// Esto lo ejecuta el cliente despues de invocar algun proceso en la función correspondiente PHP
// Son los regresos despues de que se invoco al servidor
async function procesarRespuesta__(vRes) {		
	cOpc = vRes.opcion.opcion;					// Es como se re cupera en PHP la opción 
	//cons ole.log("Opción de regreso : "+ cOpc);
    switch(cOpc) {
// 		__________________________________________________________________________________
        case "CargaCuentasBancarias":
            await CargaCuentasBancarias(vRes);	
        break;
// 		__________________________________________________________________________________
    	case "ReconstruirSaldos":
    		document.getElementById('loader-container').style.display = 'none';
    		console.log("Terminó");
		break;
// 		__________________________________________________________________________________
    }
}
// _______________________________________________________________________
// _______________________________________________________________________
// _______________________________________________________________________
// _______________________________________________________________________
function ReconstruirSaldos(){
	cSelCta 	= document.getElementById("selTipCta");
	cSelPeriodo = document.getElementById("selPeriodo");
	cMensaje 	= "" , cCtaIni = "" , cCtaFin = "", cFecIni = "";

	if (!validarSeleccion(cSelCta,true)){
		return false
	}
	if (!validarSeleccion(cSelPeriodo,true)){
		return false
	}
	cCtaIni = document.getElementById("idCuentabancariaI");
	cCtaFin = document.getElementById("idCuentabancariaF");
	// Valores de Cuentas
	if ( cSelCta.value==="Rango"){
		if ( !validarSeleccion(cCtaIni,true) ){
			return false;
		}else{
			cCtaIni  = RegresaCtaBancaria("idCuentabancariaI");
			cMensaje = "["+cCtaIni;
		}
		if ( !validarSeleccion(cCtaFin,true) ){
			return false;
		}else{
			cCtaFin  = RegresaCtaBancaria("idCuentabancariaF");
			cMensaje = cMensaje + "-"+cCtaFin+"]";
		}
	}else{
		 $aCtas  = primerUltimoValor(document.getElementById("idCuentabancariaI") );
		 cCtaIni = $aCtas[0];
		 cCtaFin = $aCtas[1];
	}
	// Valor para la Fecha Inicial
	cPeriodo = document.getElementById("idFecha");
	if ( cSelPeriodo.value=="Periodo"){
		if ( !validarSeleccion(cPeriodo,true) ){
			return false;
		}else{
			cFecIni = cPeriodo.value;
			if (cMensaje==""){
				cMensaje = " Periodo [" + cFecIni + "]";
			}else{
				cMensaje = cMensaje + " Periodo [" + cFecIni + "]";
			}
		}
	}else{
		cFecIni = "1900-01-01"
	}
	aDatos = {
		opcion	: "ReconstruirSaldos",
		OpcCta	: cSelCta.value,
		CtaIni	: cCtaIni,
		CtaFin	: cCtaFin,
		OpcPer	: cSelPeriodo.value,
		fecha	: cFecIni
	};
	esperaRespuesta(`Desea iniciar la reconstrucción de Saldos  ${cMensaje} `).then((respuesta) => {
        if (respuesta) {
			document.getElementById('loader-container').style.display = 'flex';
			conectayEjecutaPost(aDatos,cPhp,null);// Esta función esta en rutinas.js
		}
	});
}
// _______________________________________________________________________
function prendeApagaRango(cValor){
	switch(cValor){
//		___________________________________________________________________		
		case "":
		case "Todas":
			document.getElementById("divRanCta1").classList.add("disabled");
			document.getElementById("divRanCta2").classList.add("disabled");
			document.getElementById("idCuentabancariaI").value = "";
			document.getElementById("idCuentabancariaF").value = "";
		break;
//		___________________________________________________________________
		case "Rango":
			document.getElementById("divRanCta1").classList.remove("disabled");
			document.getElementById("divRanCta2").classList.remove("disabled");
			document.getElementById("idCuentabancariaI").value = "";
			document.getElementById("idCuentabancariaF").value = "";
		break;
//		___________________________________________________________________
	}
}
// _______________________________________________________________________
function prendeApagaPeriodo(cValor){
	switch(cValor){
//		___________________________________________________________________		
		case "":
		case "Todo":
			document.getElementById("divPeriodo").classList.add("disabled");
			document.getElementById("idFecha").value = "";
		break;
//		___________________________________________________________________
		case "Periodo":
			document.getElementById("divPeriodo").classList.remove("disabled");
			//document.getElementById("idFecha").value  = gInicio;
		break;
//		___________________________________________________________________
	}
}
// _______________________________________________________________________
function CargaCuentasBancarias(vRes){
	gHoy	 = vRes.opcion.hoy;
	gYear	 = gHoy.slice(0,4);
	gInicio	 = gYear+"-01-01";

	//con sole.log("Hoy ="+gHoy+ " Año="+gYear+ " Inicio="+gInicio);
	llenaCombo( document.getElementById("idCuentabancariaI") , vRes.ctas 	);
	llenaCombo( document.getElementById("idCuentabancariaF") , vRes.ctas 	);

}
// _______________________________________________________________________