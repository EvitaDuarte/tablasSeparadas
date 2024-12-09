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

var cPhp    	 = "Ingresos_.php";	// En este php estarán las funciones que se infocaran desde este JS
var cPhp1		 = "Reportes_.php";
var cYear		 = "";				// Mínimo año a capturar
var cAnioRei	 = "";				// Mínimo año para los reintegros
var cAnio 		 = "";				// Año que regresa de la fecha actual del Servidor
var dHoy		 = "";				// Fecha del servidor
var cEsquema	 = "";				// Rol del Usuario
var lActiva		 = null;				// Para saber si la cuenta Bancaria esta Activa
var pagCta		 = "";				// Guarda la cuenta Bancaria para el paginado
var nSaldoCta    = 0.00;
var dFecMovOri	 = ""				// Para re cuperar la fechaMov, si se capturo mal
var gForma		 = ""				// Formulario que se este ejecutando
var gTipoMov	 = ""				// I , E o C
var gTabla		 = ""				// Tabla HTML que se esta visualizando
var gOperacion	 = ""				// Ingreso , Egreso , Cheque
var lNoModificar = false;			// Bandera para no modificar o cancelar cheques de años anteriores al año permitido
var nW 			 = 1;	
var lContinua	 = false; 
var gNumChe		 = "";				// Para cuando el cheque pasa de 00000000 a ########
									// Aunque le cambien la fecha a un día actual
var lDiaAnterior = false;

window.onload = function () {		// Función que se ejecuta al cargar la página HTML
	// Se obtiene el nombre del archivo que lo invoca
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);

	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// ______________________________________
		case "OpeFin04_01Ingresos.php":
			gForma 	 	= "formIngreso";
			gTipoMov 	= "I";
			gOperacion	= "Ingreso"
			gTabla	 	= "tablaIngresos";
			aDatos = {
				opcion 	: "CargaCatalogos"
			};
			// 
			quitaSubmit(gForma);
			conectayEjecutaPost(aDatos,cPhp,null);// Esta función esta en rutinas.js
		break;
		// ________________________________________
		case "OpeFin04_02Egresos.php":
			gForma 	 	= "formEgreso";
			gTipoMov 	= "E";
			gOperacion	= "Egreso";
			gTabla	 	= "tablaMovimientos";
			aDatos = {
				opcion 	: "CargaCatalogosEgresos"
			};
			quitaSubmit(gForma);
			conectayEjecutaPost(aDatos,cPhp,null);// Esta función esta en rutinas.js
		break;
		// ________________________________________
		case "OpeFin04_03Cheques.php":
			gForma 	 	= "formCheques";
			gTipoMov 	= "C";
			gOperacion	= "Cheque";
			gTabla	 	= "tablaMovimientos";
			aDatos = {
				opcion 	: "CargaCatalogosCheques"
			};
			quitaSubmit(gForma);
			conectayEjecutaPost(aDatos,cPhp,null);// Esta función esta en rutinas.js
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
        case "CargaCatalogosEgresos":
        case "CargaCatalogosCheques":
            // no hay una tabla HTML a procesar
            //con sole.log("Opcion="+cOpc);
            await CargaCatalogos(vRes);
            await escuchaFoco(["idUr"]);
        break;
// __________________________________________________________________________________
    	case "SaldoHoy":
    		//document.getElementById("FechaHoy").value = vRes.datos.hoy;
    		//document.getElementById("SaldoHoy").value = vRes.datos.saldoHoy;
    		//document.getElementById("idFecha").value  = dHoy;//  obtenerFechaHoy();
    		nSaldoCta = vRes.datos.saldoHoy
    		await NuevoMovimiento('n');
    		await despliegaMovimientos(document.getElementById("idCuentabancaria").value);
		break;
// __________________________________________________________________________________
		case "ReciboIngreso":
			document.getElementById("idRecibo").value = vRes.opcion.recibo;
		break;
// __________________________________________________________________________________
		case "validaReferencia":
			let detente= "";
			// Siempre regresa true, en vRes.opcion.mensaje se ve si se duplico la referencia
			if (vRes.opcion.mensaje!==""){
				FocoEn("idRefe");
				recuperaValorOriginal("idRefe");
				//document.getElementById("idRefe").value = "";
				mandaMensaje(vRes.opcion.mensaje);
			}
		break; 
// __________________________________________________________________________________
		case "ConsultaMovimientosBancarios":
			await ConsultaMovimientosBancarios(vRes);
		break; 
// __________________________________________________________________________________
		case "AdicionarMovimiento":
			//con sole.log("Hola en Adicionar Movimiento");
			await despliegaSaldoHoy(document.getElementById("idCuentabancaria").value);
		break;
// __________________________________________________________________________________
		case "EliminarMovimiento":
			await despliegaSaldoHoy(document.getElementById("idCuentabancaria").value);
		break;
// __________________________________________________________________________________
// __________________________________________________________________________________
		case "ModificaMovimiento": // Es necesario o esta incluida en AdicionarMovimiento
			//con sole.log("Hola en modificación");
			await despliegaSaldoHoy(document.getElementById("idCuentabancaria").value);
		break;
// __________________________________________________________________________________
		case "CancelarMovimiento":
			await despliegaSaldoHoy(document.getElementById("idCuentabancaria").value);
		break;
// __________________________________________________________________________________
		case "CancelarLayOut":
			await despliegaSaldoHoy(document.getElementById("idCuentabancaria").value);
		break;
// __________________________________________________________________________________
		case "EliminarLayOut":
			await despliegaSaldoHoy(document.getElementById("idCuentabancaria").value);
		break;
// ___________________________________________________________________________________
		case "genReciboIngreso":
			//con sole.log("["+vRes.archivo+"]");
			await abrePdf(vRes.archivo);
			document.getElementById("seleRecibo").value = ""; // para que tome despues el onchange
		break;
// __________________________________________________________________________________
		case "imprimeCheque":
			await abrePdf(vRes.archivo);
		break;
// __________________________________________________________________________________
		case "revisaCheque": // Actualiza e imprime Número de Cheque
			if (lContinua){
				// Si falla habría que adicionar la condición que  id del cheque ( gId ) . coincide con el de la pantalla (idMovimiento )
				if (gNumChe!=""){ 
					document.getElementById("idRefe").value		= gNumChe;
					document.getElementById("idEstatus").value	= "I";
				}
				await abrePdf(vRes.archivo);
				await despliegaMovimientos(document.getElementById("idCuentabancaria").value);
			}
		break;
// __________________________________________________________________________________
		default:
			mandaMensaje("No se encontró código JS para ["+cOpc+"]")
		break;
// __________________________________________________________________________________
    }   
}

// __________________________________________________________________________________
function EliminarMovimiento(){
	cId = document.getElementById("idMovimiento").value.trim();
	if (verificaIdMov("eliminar",cId,true)===false){
		return false;
	}
	// Espera confirmación del Usuario para eliminar el movimiento
	esperaRespuesta(`Desea eliminar el ${gOperacion} con Id=${cId}`).then((respuesta) => {
        if (respuesta) {
            // El usuario hizo clic en "Sí"
            //mandaMensaje("El usuario seleccionó 'Sí'");
        	aMovs 				= crearDatosMovimientos("EliminarMovimiento",gTipoMov,"X");
        	aMovs.idMovimiento 	= cId;
        	conectayEjecutaPost(aMovs,cPhp,null);
        } else {
            // El usuario hizo clic en "No"
            //mandaMensaje("El usuario seleccionó 'No'");
        }
    });
}
// __________________________________________________________________________________
function CancelarMovimiento(){
	aMovs = null;
	cId   = document.getElementById("idMovimiento").value.trim();
	if (verificaIdMov("cancelar",cId,false)===false){
		return false;
	}
	if (gTipoMov==="C"){ // cheques
		cChe = document.getElementById("idRefe").value
		if (cChe==="00000000"){
			mandaMensaje("Solo se pueden cancelar cheques que tengan número mayor a cero");
			return false;
		}
	}
	lPaso = true;
	document.getElementById("idFechaCan").value =  dHoy; // fddmmyyyy(dHoy,'-'); por el cambio input-text input-date
	capturaFechaModal().then((respuesta) => {
        if (respuesta) {
        	dFechaCan  = document.getElementById("idFechaCan").value.trim();	// En formato dd/mm/yyyy
        	dFechaMov  = document.getElementById("idFecha").value.trim();		// En formato dd/mm/yyyy
    		cFechaCan  = fyyyyddmm(dFechaCan,'/'); // coloca yyyymmdd
			cFechaMov  = fyyyyddmm(dFechaMov,'/'); // coloca yyyymmdd
			if (cFechaCan<cFechaMov){
				mandaMensaje(`La fecha de Cancelación ${dFechaCan} es menor a la del movimiento ${dFechaMov}`)
			}else{
	    	    if (lPaso){
	    	    	dFechaCan = fddmmyyyy(dFechaCan,"-"); // yyyy/mm/dd
					// Espera confirmación del Usuario para cancelar el movimiento
					esperaRespuesta(`Desea cancelar el ${gOperacion} con Id=${cId} con fecha Cancelación ${dFechaCan}`).then((respuesta) => {
			        	if (respuesta) {
				        	aMovs 				= crearDatosMovimientos("CancelarMovimiento",gTipoMov,"C");
				        	aMovs.idMovimiento 	= cId;
				        	aMovs.idImpo		= aMovs.idImpo*(-1);
				        	aMovs.idBenefi  	= "** CANCELADO ** " + aMovs.idBenefi;
				        	aMovs.idCpto		= "** CANCELADO ** " + aMovs.idCpto;
				        	aMovs.idEstatus 	= "C";
				        	aMovs.idFecCan  	= dFechaCan;	// yyyy/mm/dd
				        	aMovs.idFecha   	= dFechaCan;	// yyyy/mm/dd
			        		conectayEjecutaPost(aMovs,cPhp,null);
			        	}
		    		});
				}
			}
    	}
    });

}
// __________________________________________________________________________________
function GrabarMovimiento(){
	cCta = document.querySelector("#idCuentabancaria").value.trim();
	if (cCta!==""){
		cId		= document.querySelector("#idMovimiento").value.trim();
		cOpcion = (cId==""?"AdicionarMovimiento":"ModificaMovimiento");
		cW		= (cId==""?"Adicionar":"Modificar");
		cW1		= (cId==""?"":"con Id="+cId);

		cStatus = " ";
		if (gTipoMov==="C"){ // Cheques
			numChe = parseInt(document.getElementById("idRefe").value.trim() , 10 );
			if (numChe>0 ){
				cStatus = "I"
			}
		}
		aMovs	= crearDatosMovimientos(cOpcion,gTipoMov,cStatus);
		if (validaCaptura(aMovs)){
			// Espera confirmación del Usuario para grabar el movimiento
			esperaRespuesta(`Desea ${cW} el ${gOperacion} ${cW1}`).then((respuesta) => {
	        	if (respuesta) {
					aMovs.idMovimiento = cId;
					conectayEjecutaPost(aMovs,cPhp,null);
				}
			});
		}
	}else{
		FocoEn("idCuentabancaria");
		mandaMensaje("Se requiere seleccionar cuenta Bancaria")
	}
}
// __________________________________________________________________________________
function validaCaptura(aCampos){
	if ( estatusCancelado("modificar")){
		return false;
	}
	aTitulos = ["","Cuenta Bancaria", "Fecha Ingreso","UR","Clave Operación","Clave Control", 
				"Beneficiario","Concepto","Importe","Recibo","Referencia Bancaria"];
	i = 0;
	for (const propiedad in aCampos) {// Recorre los input a validar
		if(i>0 && i<11){
			if (aCampos.hasOwnProperty(propiedad)) {
	    		const cValor = aCampos[propiedad].trim(); // El valor de la propiedad
	    		//con sole.log(`Propiedad: ${propiedad}, Valor: ${cValor}`);
				if (cValor===""){				
					cCampo = `${propiedad}`;
					FocoEn(cCampo);
					mandaMensaje("Se requiere ingresar valor para "+aTitulos[i]);
					return false;
				}
	  		}
	  	}
	  	i++;
	}
	// Se vuelven a validar algunos campos, 
	// el recibo y la referencia bancaria se validaran en PHP
	if ( validaFecha(aCampos.idFecha,"idFecha",false) ){
		if ( soloImportes(aTitulos[8],"idImpo") ){
			if (soloLetrasNumerosSeparadores(aCampos.idBenefi,aTitulos[6],"idBenefi")){
				if ( soloLetrasNumerosSeparadores(aCampos.idCpto, aTitulos[7],"idCpto") ){
					if ( soloImportes(aTitulos[8],"idImpo")){
						if ( soloLetrasNumerosGuion(aCampos.idRefe,aTitulos[10],"idRefe") ){
							return true;
						}
					}
				}
			}
		}
	}
	return false;
}
// __________________________________________________________________________________
function crearDatosMovimientos(cOpcion,cTipo,cEstatus){
	cImpo  = document.getElementById("idImpo").value.trim().replace(new RegExp(',', 'g'), '');
	cFecha = document.getElementById("idFecha").value.trim(); // yyyy-mm-dd
	cYearR = cFecha.substring(0,4);
	cFecha = fddmmyyyy(cFecha,"-"); // La pone en formato dd/mm/yyyy

	return {
		opcion 				: cOpcion,
		idCuentabancaria 	: regresaCuentaBancaria(),
		idFecha				: cFecha,
		idUr				: document.getElementById("idUr").value,
		idOpera				: document.getElementById("idOpera").value,
		idCtrl				: document.getElementById("idCtrl").value.split("|")[0],
		idBenefi			: document.getElementById("idBenefi").value.trim().toUpperCase(),
		idCpto				: document.getElementById("idCpto").value.trim().toUpperCase(),
		idImpo				: cImpo,
		idRecibo			: document.getElementById("idRecibo").value.trim(),
		idRefe				: document.getElementById("idRefe").value.trim(),
		idAnio				: document.getElementById("idAnio").value.trim(),
		year				: cYearR,
		idSiglas			: regresaSiglasBancarias(),
		idTipo				: cTipo,
		estatus				: cEstatus 
	}
}
// __________________________________________________________________________________
function validaReferencia(cRefe){
	cIdRefe = "idRefe";
	if ( cRefe.trim()==="" ){
		//FocoEn(cIdRefe);
		//mandaMensaje("Se requiere valor para la referencia Bancaria");
		//FocoEn(cIdRefe);
		return true;
	}else{
		if (gTipoMov==="C"){// Cheques
			cNumChe = document.getElementById(cIdRefe).value.trim();
			if (soloNumeros(cNumChe,"Número de Cheque",cIdRefe)){
				cNumChe = cNumChe.padStart(8, '0');
				document.getElementById(cIdRefe).value = cNumChe;
				if ( cNumChe != "00000000" ){
					cIdMov 	= document.getElementById("idMovimiento").value.trim();
					aDatos	= { opcion: "validaReferencia", cuenta: regresaCuentaBancaria() , referencia:cNumChe , idMov: cIdMov};
					conectayEjecutaPost(aDatos,cPhp,null);
				}
			}
		}else{
			if ( soloLetrasNumerosGuion(document.getElementById(cIdRefe).value,"Referencia Bancaria",cIdRefe) ){
				cRefe   = cRefe.trim();
				cIdMov 	= document.getElementById("idMovimiento").value.trim();
				aDatos	= { opcion: "validaReferencia", cuenta: regresaCuentaBancaria() , referencia:cRefe , idMov: cIdMov};
				conectayEjecutaPost(aDatos,cPhp,null);
			}
		}
	}
}
// __________________________________________________________________________________
function validaAnio_(cAnio,cIdAnio){ // Hay una función parecida en rutinas.js
	if (cAnio > cYear){
		FocoEn(cIdAnio);
		mandaMensaje("El año no puede ser mayor al año actual "+cYear+"<"+cAnio);
		document.getElementById("idAnio").value = dHoy.split("-")[0];
		return false;
	}else{
		if ( (cYear-cAnio)>4 ){
			FocoEn(cIdAnio);
			mandaMensaje("El año de la fecha no debe ser menor a "+(cYear-4));
			document.getElementById("idAnio").value = dHoy.split("-")[0];
			return false;
		}
	}
	return true;
}
// __________________________________________________________________________________
function calculaRecibo(cRecibo){
	//if (cRecibo===null || cRecibo==="" || cRecibo===undefined){
	cRecibo = cRecibo.trim();
	cId   = document.getElementById("idMovimiento").value.trim();
	if (cId !="" && cRecibo!=""){// En modificaiones no volver a calcular el recibo de ingreso
		return false;
	}
	cOpe  = document.getElementById("idOpera").value
	cCtrl = document.getElementById("idCtrl").value
   
	if (cOpe===""){
		 setTimeout(function() {
			document.querySelector("#idRecibo").blur();
  			document.querySelector("#idOpera").focus();
  			document.querySelector("#idOpera").click();
			mandaMensaje("Se requiere clave de operación");
  			return false;
		}, 1);
	}else{
		if (cCtrl===""){
			setTimeout(function() {
				document.querySelector("#idRecibo").blur();
	  			document.querySelector("#idCtrl").focus();
	  			document.querySelector("#idCtrl").click();
				mandaMensaje("Se requiere clave de control");
	  			return false;
			}, 1);
		}else{
			cCta 	= document.querySelector("#idCuentabancaria").value;
			cSiglas = cCta.split("|")[1].trim(); // Debe ir primero ya que se reasigna valor de cCta
			cCta	= cCta.split("|")[0].trim();
			cYearR	= document.getElementById("idFecha").value.substring(0,4);
			
			//con sole.log(cSiglas);
			if (cSiglas!==""){
				aDatos = {
					opcion 	: "ReciboIngreso",
					cuenta	: cCta,
					siglas	: cSiglas,
					cveOpe	: cOpe,
					cAnio   : cYearR,
					cveCtrl	: cCtrl.split("|")[0]
				}
				conectayEjecutaPost(aDatos,cPhp,null);
			}
		}
	}
}
// __________________________________________________________________________________
function filtraControles(cOperacion){
	filtrarSelect(cOperacion,"idCtrl",true);
	if (gTipoMov=="I"){
		document.getElementById("idRecibo").value = "";
	}
}
// __________________________________________________________________________________
function limpiaRecibo(){
	document.getElementById("idRecibo").value = "";
}
// __________________________________________________________________________________
function despliegaSaldoHoy(cCuenta) {
	document.getElementById("selectLayOut").value = "";
	if (cCuenta===""){
		document.getElementById("divLayOut1").classList.add("disabled");
		document.getElementById(gForma).reset();
		document.querySelector("#FechaHoy").value 			= dHoy;
		document.querySelector("#idFecha").value 			= dHoy; // fddmmyyyy(dHoy,'-');
		document.querySelector("#idAnio").value 			= dHoy.split("-")[0];
		document.querySelector("#SaldoHoy").value 			= 0.00;
		limpiaTabla(document.getElementById(gTabla).getElementsByTagName('tbody')[0]);
		if (gTipoMov==="I"){
			document.getElementById("divRecibo").classList.add("disabled");
		}
		return;
	}
	cCta	= cCuenta.split('|')[0];	// la cuenta y el nombre estan separado por un pipe |
	lActiva = cCuenta.split("|")[2].trim()=="1"?true:false;
	// Deshabilita los botones si la cuenta esta inactiva
	if (lActiva===false){
		document.getElementById("grpBotones").style.display = "none";
	}else{
		document.getElementById("grpBotones").style.display = "flex";
		document.getElementById("divLayOut1").classList.remove("disabled");
	}
	aDatos	= { opcion: "SaldoHoy", cuenta: cCta };
	conectayEjecutaPost(aDatos,cPhp,null);
}
// __________________________________________________________________________________
function despliegaMovimientos(cCuenta){
	pagCta = cCuenta.split('|')[0];
	paginaMovimientos(-1);
}
// __________________________________________________________________________________
function paginaMovimientos(cPag){  
	aCampos	= [];
	aTipo	= [];
	cCta	= pagCta; // pagCta se inicializa en el onchange del select de cuentas bancarias
	pagina	= document.getElementById("pagina").value;
	cBuscar	= document.getElementById("opcionesFiltro").value.trim();
	cCampos	= "a.idcuentabancaria,a.folio,a.referenciabancaria,a.importeoperacion,a.fechaoperacion,a.beneficiario,"
	cCampos +="a.concepto,a.idunidad,a.idoperacion,a.idcontrol,a.anioejercicio,a.idmovimiento,a.estatus,a.usuarioalta,a.fechaalta"
	cTipos	= "C,C,C,NF,D,C,C,C,C,C,C,N,C,C,D"; // C - Caracter , N Número , D - Fecha
	if (pagina===null){
		pagina = 1;
	}
	if (cPag=="-1"){ // Cambia el número de registros x pagina o se introdujo texto de búsqueda
		pagina = 1;
	}
	//
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
			case "R":	// Recibo de Ingreso, Documento
				aCampos = ["a.folio"];
				aTipo   = ["C"]
			break;
		}
	}

	//
	aDatos = {
		opcion 			: "ConsultaMovimientosBancarios",
		limite			: document.getElementById("num_registros").value,
		busca			: document.getElementById("campo").value,
		pagina 			: pagina,
		tabla			: " movimientos a , operacionesbancarias b ",
		tablaPrin		: "movimientos",
		join			: " a.idoperacion=b.idoperacion and b.tipo='"+gTipoMov+"' and idcuentabancaria='"+cCta+"' ",
		campos			: cCampos,
		tipos			: cTipos,
		id 				: "idmovimiento",
		regreso			: "",
		order			: "order by fechaoperacion desc",
		depura			: "",
		traeOperaciones	: true,
		aCampos			: aCampos, /*["a.idcuentabancaria","a.folio","a.referenciabancaria","a.importeoperacion","a.fechaoperacion","a.beneficiario","a.concepto",
						   "a.idunidad","a.idoperacion","a.idcontrol","a.anioejercicio","a.idmovimiento","a.estatus","a.usuarioalta","a.fechaalta"],*/
		aTipos			: aTipo /*
		whereTP			: " idCuentabancaria='"+cCta+"' " // Creo que no se utiliza va en el join  */
	};
	//
	conectayEjecutaPost(aDatos,cPhp,null);
}
// __________________________________________________________________________________
function ConsultaMovimientosBancarios(aRespuesta){ // Es el regreso del PHP
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
		    	cOpe  	  = this.cells[8].textContent;
		    	cCtrl 	  = this.cells[9].textContent;
		    	cFechaCap = this.cells[4].textContent;
		    	cAnio 	  = cFechaCap.substring(0,4);
		    	cEstatus  = this.cells[12].textContent.trim();
		    	cId		  = this.cells[11].textContent;

		    	//quitarFiltroSelect("idCtrl");
			    document.getElementById("idRecibo").value 		= this.cells[1].textContent;
			    document.getElementById("idRefe").value 		= this.cells[2].textContent;
			    document.getElementById("idImpo").value 		= this.cells[3].textContent;
			    document.getElementById("idFecha").value 		= this.cells[4].textContent; // fddmmyyyy(this.cells[4].textContent,"-");
			    document.getElementById("idBenefi").value 		= this.cells[5].textContent;
			    document.getElementById("idCpto").value 		= this.cells[6].textContent;
			    document.getElementById("idUr").value 			= this.cells[7].textContent;
			    document.getElementById("idOpera").value 		= cOpe; 
			    document.getElementById("idCtrl").value 		= cCtrl+"|"+cOpe;
			    document.getElementById("idAnio").value 		= this.cells[10].textContent;
			    document.getElementById("idMovimiento").value	= cId;
			    document.getElementById("idEstatus").value 		= cEstatus;
			    if (cEstatus===""){ // Se dará preferencia al mensaje de que ya esta cancelado el movimiento
				    if ( (cYear - cAnio)> 4 ){
				    	lNoModificar = true;
				    	document.getElementById("idEstatus").value = "NM";
				    }else{
				    	// Si no es administrador no podrá cancelar, modificar, eliminar un movimiento
				    	if ( cFechaCap < dHoy ){
							if (cEsquema!="Administrador"){
								document.getElementById("idEstatus").value = "NM";
								lDiaAnterior = true;
							}
						}
					}
				}
				// En Ingresos, habilita o no la impresión y desglose de ingresos
				if (gTipoMov==="I"){
					if ( cId!==""){
						document.getElementById("divRecibo").classList.remove("disabled");
					}else{
						document.getElementById("divRecibo").classList.add("disabled");
					}
				}
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
function CargaCatalogos(vRes){
	cYear	 = vRes.opcion.anio;
	dHoy	 = vRes.opcion.hoy;
	cAnio	 = dHoy.split("-")[0]; 
	cEsquema = vRes.datos.esquemaUsuario;
	if (vRes.ctas!=null){	// Puede venir nula si no se han asignado cuentas al usuario
		llenaCombo( document.getElementById("idCuentabancaria") , vRes.ctas 	);
	}else{
		mandaMensaje("No se han definido Cuentas Bancarias para el usuario");
	}
	llenaCombo( document.getElementById("idUr") , vRes.urs);
	if (vRes.opera!=null){
		llenaCombo( document.getElementById("idOpera"), vRes.opera);
	}else{
		mandaMensaje("No se han definido las operaciones bancarias");
	}
	if (vRes.ctrl!=null){
		llenaCombo( document.getElementById("idCtrl") , vRes.ctrl);
	}else{
		mandaMensaje("No se han definido los controles bancarios");
	}
	NuevoMovimiento('n');
}
// __________________________________________________________________________________
function NuevoMovimiento(cW){
	//con sole.log("Nuevo movimiento"+nW);nW=nW+1;
	lNoModificar = false;
	lDiaAnterior = false;
	cCta 		 = document.querySelector("#idCuentabancaria").value.trim(); 
	if (cCta==="" && cW===""){
		FocoEn("idCuentabancaria");
		mandaMensaje("Se requiere seleccionar Cuenta Bancaria");
		return false;
	}
	// Se limpian todos los elementos HTML 
	document.getElementById(gForma).reset();
	//
	document.querySelector("#FechaHoy").value 			= dHoy;
	document.querySelector("#idCuentabancaria").value 	= cCta;
	document.querySelector("#idFecha").value 			= dHoy; // fddmmyyyy(dHoy,'-');
	document.querySelector("#idAnio").value 			= dHoy.split("-")[0];
	document.querySelector("#SaldoHoy").value 			= nSaldoCta;
	//if (gTipoMov=="C"){
	//	document.getElementById("idOpera").value	="CHE";filtraControles(this.value)
	//	document.getElementById("idCtrl").value		= "CH";
	//}
	// Solo despliega "Seleccione" en la lista de controles, ya la clave de operación la filtra 
	filtraControles("NoExiste");	
	FocoEn("idUr");
}
// __________________________________________________________________________________
function regresaCuentaBancaria(){
	return document.querySelector("#idCuentabancaria").value.split("|")[0].trim(); // cta | siglas | activa
}
// __________________________________________________________________________________
function regresaNombreCuentaBancaria(){
	// NumCta | siglas | activa | nombreCta , NumCta_NmbreCta
	return document.querySelector("#idCuentabancaria").value.split("|")[3].trim().split(",")[0].trim(); 
}
// __________________________________________________________________________________
//function detectarShiftTab(event,cRefe) { // No funciono para detectar SHIFT-TAB y valide el contenido cuando se pierde el foco
//	if (event.shiftKey && event.key === "Tab") {
//		document.getElementById("idRefe").blur();
//    	validaReferencia(cRefe.trim());
//	}
//}
// __________________________________________________________________________________
function regresaSiglasBancarias(){
	return document.querySelector("#idCuentabancaria").value.split("|")[1].trim(); // cta | siglas | activa
}
// __________________________________________________________________________________
function estatusCancelado(cModifica){
	cEstatus = document.querySelector("#idEstatus").value.trim();
	if (cEstatus==="C"){// Habria que ver si el administrador pueda tener esos privilegios
		mandaMensaje(`No se pueden ${cModifica} ${gOperacion}s ya cancelados`);
		return true;
	}else if(cEstatus==="NM"){
		cW1 = lNoModificar?"años anteriores":"dias anteriores";
		mandaMensaje(`No se pueden ${cModifica} ${gOperacion}s de ${cW1}`);
		return true;
	}
	return false;
}
// __________________________________________________________________________________
function verificaIdMov(cOpcion,cId,lVerifcaAdmin){
	// Se requiere movimiento a eiminar
	if (cId===""){
		mandaMensaje(`Se requiere seleccionar ${gOperacion} Bancario a ${cOpcion}`);
		return false;
	}
	if ( estatusCancelado(cOpcion)){
		return false;
	}

	cFechaCap = fyyyyddmm(document.getElementById("idFecha").value,"/");
	cFechaHoy = dHoy.replace(/-/g, '');
	if (lVerifcaAdmin){
		// Solo el administrador puede eliminar/cancelar movimientos de días anteriores
		if (cFechaCap<cFechaHoy){
			if (cEsquema!="Administrador"){
				mandaMensaje(`No se pueden ${cOpcion} ${gOperacion}s de días anteriores`);
				return false;
			}
		}
	}
	// Verificar que el movimiento no sea menor a el año permitido
	cAnio = cFechaCap.substring(0,4);
	if ( (cYear - cAnio)> 4 ){
		mandaMensaje(`El año del movimiento ${cAnio} es menor al permitido 20${cYear-cAnio}`);
		return false;
	}
	return true
}
// __________________________________________________________________________________
function revisarOperacion(){
	cOpe  = document.getElementById("idOpera").value;
	cCtrl = document.getElementById("idCtrl").value;
	//con sole.log(cOpe+"\n<br>"+cCtrl);
	if (cCtrl===""){
		FocoEn("idCtrl");
		//mandaMensaje("Se requiere seleccionar un Control");
		FocoEn("idCtrl");
		return true;
	}else{
		if (!cCtrl.includes(cOpe)) {
	        //
	        FocoEn("idCtrl");
	        mandaMensaje("El control seleccionado, no corresponde al valor de la operación");
	        // Puedes restablecer el segundo select o tomar otras acciones según tus necesidades
	        document.getElementById("idCtrl").value = "";
	        FocoEn("idCtrl");
	        return false;
	    }
	}
    return true;
}
// __________________________________________________________________________________
function archivoLayOut(cValor,cTipMov){
	cOpc = "";
	if (cValor==""){
		//mandaMensaje("Se requiere seleccionar acción a realizar");
		return;
	}

	if (cValor==="Eliminar"){
		cValor = "eliminación"
		cOpc   = "E";
	    document.getElementById("input_text").textContent = "Seleccione Archivo de Eliminación";
	    document.getElementById("btn_text").textContent   = "Iniciar Eliminación";
	}else if (cValor==="Cancelar"){
		cValor = "cancelación"
		cOpc   = "C";
		document.getElementById("input_text").textContent = "Seleccione Archivo de Cancelación";
		document.getElementById("btn_text").textContent   = "Iniciar Cancelación";
	}
	// Solicita archivo de layOut
	solicitaArchivoLayOut().then((respuesta) => {
		if (respuesta){
			var input1_file = document.getElementById('ArchivoCarga_file');
			var oFile		= input1_file.files[0];
			cFile 			= oFile.name;
			
			esperaRespuesta(`Desea iniciar la búsqueda y ${cValor} de movimientos de ${cFile} `).then((respuesta) => {
				if (respuesta){
					const reader 	= new FileReader();
					reader.onload = function (e) {
						const csvContent = e.target.result;
						if (cOpc==="C"){
							procesarCSVCancelacion(csvContent,cTipMov);
						}else if(cOpc==="E"){
							procesarCSVEliminar(csvContent,cTipMov);
						}
					};
					reader.readAsText(oFile, 'UTF-8');
				}
			});

		}
	});
}
// __________________________________________________________________________________ 
function procesarCSVCancelacion(csvContent,cTipMov){
	let datos = [];
	var lMalFecha = false , cMensaje = "" , lMalNumero=false , lMalCheque=false;
	const filas = csvContent.split('\n');
    // Dividir el contenido CSV en filas y columnas
    const filas1 = csvContent.split('\n').map(fila => fila.split('\t'));
    // Operación de Cancelación, no funcionó por que debe de las operaciones CIN, CEG y CAN 
    // deberían estar relacionadas con cada una de las claves de control
    /*if (cTipMov=="Ing"){
    	cTipMov = "CIN";
    }else if(cTipMov=="Egr"){
		cTipMov = "CEG";
    }else if(cTipMov=="Che"){
		cTipMov = "CAN";
    } */

    // Obtener el número de columnas ( referencia , fecha Cancelación (yyyy-mm-dd) , importe , nombre )
    const numeroDeColumnas = filas1.length > 0 ? filas1[0].length : 0;
    //con sole.log("Fecha de Hoy: "+dHoy)
	filas.forEach(function (fila) {
		if (fila.trim()!=""){
			const [cRefe, cFecha, cImpo] = fila.split("\t");
			// fddmmyyyy puede cambiar de d/m/y  a y/m/d   o   de y/m/d  a d/m/y
			cFecha1 = fgyyyyddmm(cFecha,"/"); cAnio = cFecha1.substring(0,4);
			//con sole.log(`cRefe=${cRefe} cFecha=${cFecha1} Año=${cAnio} cImpo=${cImpo}`);
			if (cFecha1 > dHoy || ( (cYear-cAnio)>4 ) ){
				lMalFecha = true;
			}
			
			cImpo1 = cImpo.replace(/[ \t,"']/g, '').trim(); 
			esNumero = /^-?\d+(\.\d{2})?$/.test(cImpo1); // Aceptar números con dos dígitos decimales opcionales
            if (!esNumero) {
                lMalNumero = true;
            }
            if ( cTipMov==="Che" && parseInt(cRefe)===0 ){
            	lMalCheque = true;
            }

            var filaDatos = {
            	referencia	: cRefe,
            	fechaCance	: cFecha1,
            	importe		: cImpo1,
            	estatus		: "",
            	idMov		: ""
             };
            // Agrega el objeto al arreglo de datos seleccionados
			datos.push(filaDatos);
		}
	});
	if (lMalFecha){
		cMensaje = "Fechas de Cancelación";
	}
	if (lMalNumero){
		if (cMensaje!==""){
			cMensaje = cMensaje + ", ";
		}
		cMensaje = cMensaje + "Importes"
	}
	if (lMalCheque){
		if (cMensaje!==""){
			cMensaje = cMensaje + ", ";
		}
		cMensaje = cMensaje + "Números de Cheque"
	}
	//**
	if (cMensaje!==""){
		cMensaje  = " Inconsistencias en "+ cMensaje + "... revise"
		mandaMensaje(cMensaje);
		return false;
	}
	//mandaMensaje("Todo ok");
	aDatos ={
		opcion		: "CancelarLayOut",
		ctBancaria	: regresaCuentaBancaria(),
		opeCan		: cTipMov,
		idTipo		: gTipoMov,
		aDatoCance	: datos
	}
	// Valida que los datos esten completos
	conectayEjecutaPost(aDatos,cPhp);

}
// __________________________________________________________________________________
function procesarCSVEliminar(csvContent,cTipMov){
	let datos = [] , lMalNumero = false, cMensaje = "";

	const filas = csvContent.split('\n'); // arreglo x el numero de renglones del CSV

	filas.forEach(function (fila) {
		if (fila.trim()!=""){
			const [cRefe, cImpo, cBene] = fila.split("\t");
			cImpo1 = cImpo.replace(/[ \t,"']/g, '').trim(); 
			esNumero = /^-?\d+(\.\d{2})?$/.test(cImpo1); // Aceptar números con dos dígitos decimales opcionales
	        if (!esNumero) {
	            lMalNumero = true;
	        }else{
	        	// En cheques Pasar beneficiario si número de cheque ==="00000000" isNaN ( is Not a Number)
	        	cBene1 =  ( (gTipoMov=="C" && !isNaN(cRefe) && parseInt(cRefe)===0) ? cBene : "" );
	            var filaDatos = {
            		referencia	: cRefe,
            		importe		: cImpo1,
            		estatus		: "",
            		idMov		: "",
            		beneficiario: cBene1
             	};
            	// Agrega el objeto al arreglo de datos seleccionados
				datos.push(filaDatos);
	        }
		}
	});
	if (lMalNumero){
		mandaMensaje("Se requiere revisar los importes del archivo TXT");
		return false;
	}
	if (datos.length>0){
		aDatos ={
			opcion				: "EliminarLayOut",
			idCuentabancaria	: regresaCuentaBancaria(),
			opeEli				: cTipMov,
			idTipo				: gTipoMov,
			esquema				: cEsquema,
			aDatosEli			: datos
		}
		// Valida que los datos esten completos
		conectayEjecutaPost(aDatos,cPhp);
	}else{
		mandaMensaje("No se logró procesar archivo TXT");
	}
}
// __________________________________________________________________________________
function jsReciboIngreso(cOpc){
	//  se requiere un entorno npm o usar un empaquetador como Webpack  o Browserify 
	//  window.convertir = require('numero-a-letras'); 
	cId = document.getElementById("idMovimiento").value.trim();
	if ( cId===""){
		mandaMensaje("Se requiere seleccionar el Ingreso");
	}
	if (cOpc==="ImpRecIng"){
		cImpo = document.getElementById("idImpo").value; 		// Puede o no venir con comas
		cImpo1= cImpo.replace(/,/g, "")							// Se quitan comas
		nImpo = parseFloat(cImpo1).toFixed(2);					// Se convierte a numérico
		cImpo = cImpo1.replace(/\B(?=(\d{3})+(?!\d))/g, ",");	// Se ponen comas
		
		aDatos ={
			opcion				: "genReciboIngreso" ,
			idCuentaBancaria	: regresaCuentaBancaria(),
			referencia			: document.getElementById("idRefe").value.trim(),
			recibo				: document.getElementById("idRecibo").value,
			importe				: cImpo,
			ur					: document.getElementById("idUr").value,
			fecha				: document.getElementById("idFecha").value,
			beneficiario		: document.getElementById("idBenefi").value,
			concepto			: document.getElementById("idCpto").value,
			nombreCuenta		: regresaNombreCuentaBancaria(),
			importeLetra		: numeroletras(nImpo)
		};
		conectayEjecutaPost(aDatos,cPhp1);
	}else{
		mandaMensaje("En construcción");
	}
}
// __________________________________________________________________________________
function DialogoImprimirCheque(){
	vNumChe = document.getElementById("idRefe").value.trim();
	if (vNumChe==""){
		mandaMensaje("Se requiere seleccionar un movimiento con cheque");
		return false;
	}
	// Revisa si no esta cancelado
	vEstatus = document.getElementById("idEstatus").value.trim();
	if (vEstatus==="C"){
		mandaMensaje("No se pueden imprimir cheques Cancelados");
		return false;
	}
	vId = document.getElementById("idMovimiento").value.trim();
	if (vNumChe=="00000000"){
		document.getElementById("idNumChe").value = vNumChe;
		capturaNumeroChequeModal().then((respuesta) => {
        	if (respuesta) {
        		//cNumChe = document.getElementById("idNumChe").value.trim();
        		// imprimeCheque(vId,cNumChe,true);
        	}
        });
	}else{
		imprimeCheque(vId,vNumChe,false);
	}

}
// __________________________________________________________________________________
function imprimeCheque(idCheque,cNumChe,lActualiza){
	cCta	= RegresaCtaBancaria( "idCuentabancaria" );
	cImpo	= document.getElementById("idImpo").value;		// Puedo o no venir con comas
	cImpo1	= cImpo.replace(/,/g, "")						// Quita comas
	nImpo	= parseFloat(cImpo1).toFixed(2);				// Convierte a numérico
	cImpo	= cImpo1.replace(/\B(?=(\d{3})+(?!\d))/g, ",");	// Le pone comas
	gNumChe = "";
	aDatos = {
		opcion			: "imprimeCheque",
		idCuenta		: cCta,
		idCheque 		: idCheque,
		numCheque 		: cNumChe,
		importe			: cImpo,
		importeLetra	: numeroletras(nImpo),
		actualiza		: lActualiza
	}
	conectayEjecutaPost(aDatos,cPhp1);
}
// __________________________________________________________________________________
const validaCheImp = (lMensaje) => {
	oCheque = document.getElementById("idNumChe");
	cCheque = oCheque.value.trim();
	gNumChe = "";
	if (parseInt(cCheque)==0){
		if ( lMensaje ){
			mandaMensaje("El valor del cheque debe ser diferente a 00000000 y numérico");
		}
		lContinua = false;
		return false;
	}else if (cCheque.length>8) {
		mandaMensaje("La longitud del cheque no debe ser mayor a 8 posiciones");
		lContinua = false;
	}else{
		lContinua 	= true; // Se procesa en la respuesta		
		cCheque 	= cCheque.trim().padStart(8, '0'); 	oCheque.value = cCheque;
		gNumChe		= cCheque;
		cImpo 		= document.getElementById("idImpo").value;				// Puedo o no venir con comas
		cImpo1		= cImpo.replace(/,/g, "")								// Se quitan las comas
		nImpo		= parseFloat(cImpo1).toFixed(2);						// Se convierte a numérico
		cImpo		= cImpo1.replace(/\B(?=(\d{3})+(?!\d))/g, ",");			// Se ponen comas
		aDatos 		= {
			opcion 			: "revisaCheque",
			idCuenta		: RegresaCtaBancaria( "idCuentabancaria" ),
			idCheque		: document.getElementById("idMovimiento").value.trim(),
			numCheque		: cCheque,
			importe			: cImpo,
			importeLetra	: numeroletras(nImpo),
			actualiza		: true
		}
		//con sole.log(aDatos.importeLetra);
		conectayEjecutaPost(aDatos,cPhp1);
	}
}
// __________________________________________________________________________________
//const actualizaImprimeCheque = () =>{

//}
// __________________________________________________________________________________
// __________________________________________________________________________________