var pagCta		 = "";				// Guarda la cuenta Bancaria para el paginado
var cPhp    	 = "Concilia_.php";	// En este php estarán las funciones que se infocaran desde este JS
var gForma		 = ""				// Formulario que se este ejecutando
var dHoy		 = "";
var gOpeConci	 = [];
var gFiltro		 = false;
var gCampo		 = "";
var gTipo		 = "";
var gOperacion	 = "movimiento de conciliación";

// ______________________________________________________________________________________________________
window.onload = function () {		// Función que se ejecuta al cargar la página HTML
	// Se obtiene el nombre del archivo que lo invoca
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);

	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// ________________________________________________________________________
		case "OpeFin05_02MovsBancos.php":
			// Escuacha para recuperar el valor almacenado del campo date
			escuchaCampoFecha('idFecConci');
			gForma 	 	= "formConcilia";
			gTabla	 	= "tablaConcilia";
			aDatos = {
				opcion 	: "CargaCatalogos1"
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
// ______________________________________________________________________________________________________
// Funciones de regreso cuando se invoca al servidor - PHP 
async function procesarRespuesta__(vRes) {		
	cOpc = vRes.opcion.opcion;	
	cPag = document.getElementById("pagina").value
	//cons ole.log("Opción de regreso : "+ cOpc);
    switch(cOpc) {
	// __________________________________________________________________________________
        case "CargaCatalogos1": // Se llenaron los catalogos iniciales y hay que pasarlos a el HTML
            // no hay una tabla HTML a procesar
            //con sole.log("Opcion="+cOpc);
            await CargaCatalogos1(vRes);
            await escuchaFoco(["idCuentabancaria"]);
        break;
	// __________________________________________________________________________________
		case "FiltraMovimientosConciliacion":
			await FiltraMovimientosConciliacionRegreso(vRes);
		break;
	// __________________________________________________________________________________
		case "validaReferenciaBancos":
			if (vRes.mensaje!==""){
				cId = document.getElementById("id_concimovimiento").value.trim();
				if (cId==""){	// En las altas
					FocoEn("idRefe");
				}
				recuperaValorOriginal("idRefe");
				// mandaMensaje(vRes.opcion.mensaje); en rutinas.js respuesta__ se invoca a un manda mensaje
				//FocoEn("idRefe");
			}
		break;		
	// __________________________________________________________________________________
		case "AgregaMovBancario":
			paginaBancos( cPag );
			NuevoMovimiento("");
		break;
	// __________________________________________________________________________________
		case "ModificaMovBancario":
			paginaBancos( cPag );
		break;
	// __________________________________________________________________________________
		case "EliminarMovimiento":
			paginaBancos( cPag );
			NuevoMovimiento("");
		break;
	// __________________________________________________________________________________
		case "ConciliaMovBanco":
			await paginaBancos(document.getElementById("pagina").value);
		break;
	// __________________________________________________________________________________
		case "reporteConciliacion":
			await abrePdf(vRes.archivo);
		break;
	// __________________________________________________________________________________
		default:
			mandaMensaje("No se encontro código JS de retorno para ["+cOpc+"]");
		break;
	// __________________________________________________________________________________
    }   
}
// __________________________________________________________________________________
async function procesarError__(vRes){ 	// Cuando sucesss es false ??? algo aqui no cuadra 17/08/2024
	cOpc = vRes.opcion.opcion;			// Es como se re cupera en PHP la opción 
	//cons ole.log("Opción de regreso : "+ cOpc);
    switch(cOpc) {
    }
}
// ______________________________________________________________________________________________________
// --------------------- Código para Movimientos del banco ( no del INE )
// __________________________________________________________________________________
function consultaMovsBancos(cCuenta,lHtml=false){
	if (cCuenta===""){
		return false;
	}
	gFiltro = false; 
	// ---------------------------------------
	pagCta = cCuenta.split('|')[0];
	paginaBancos(-1);
}
// __________________________________________________________________________________
function paginaBancos(cPag){
	aCampos = [];
	aTipo   = [];
	cCta    = pagCta; // pagCta se inicializa en el onchange del select de cuentas bancarias
	pagina  = document.getElementById("pagina").value;
	cBuscar = document.getElementById("opcionesFiltro").value.trim();
	cCampos =  "a.id_concimovimiento,a.idcuentabancaria,a.idoperacion,a.conciliado,a.fechaconciliacion,a.importeoperacion,"
	cCampos += "a.id_layout_banco,a.fechaoperacion,a.concepto,a.fcve_ope_be,a.idmovimiento,a.usuario_alta,a.fecha_alta "
	cTipos  =  "N,C,C,C,D,NF,C,D,C,C,N,C,C,"; // C - Caracter , N Número , D - Fecha, NF - Númerico con comas
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
			case "C":	// Concepto
				aCampos = ["a.concepto"];
				aTipo   = ["C"]
			break;
			case "O":	// Operación (Referencia Bancaria) del Banco
				aCampos = ["a.id_layout_banco"];
				aTipo   = ["C"]
			break;
		}
	}
	//
	aDatos = {
		opcion 			: "FiltraMovimientosConciliacion",
		limite			: document.getElementById("num_registros").value,
		busca			: document.getElementById("campo").value,
		pagina 			: pagina,
		tabla			: " conci_movimientos a ",
		tablaPrin		: "conci_movimientos",
		join			: "  idcuentabancaria='"+cCta+"' ",
		campos			: cCampos,
		tipos			: cTipos,
		id 				: "id_concimovimiento",
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
function CargaCatalogos1(vRes){
	if (vRes.ctas!=null){	// Puede venir nula si no se han asignado cuentas al usuario
		llenaCombo( document.getElementById("idCuentabancaria") , vRes.ctas 	);
		document.getElementById("idFecha").value  = vRes.opcion.hoy;
		dHoy									  = vRes.opcion.hoy;
		document.getElementById('idFecha').setAttribute('max', dHoy);
		document.getElementById('idFecConci').setAttribute('max', dHoy);
		llenaCombo( document.getElementById("idOpera") , vRes.catope 	);
	}else{
		mandaMensaje("No se han definido Cuentas Bancarias a Conciliar para el usuario");
	}
}
// __________________________________________________________________________________
function FiltraMovimientosConciliacionRegreso(aRespuesta){ // Es el regreso del PHP
	var cTabla 	 = gTabla;
	var table  	 = document.getElementById(cTabla).getElementsByTagName('tbody')[0];
	lNoModificar = false;
	lDiaAnterior = false;

	limpiaTabla(table);
	// Se llenan ya con los datos formateados con tr y td que se contruyen en el php
	table.innerHTML = aRespuesta.opcion.regreso.data;

	// Asigna escucha click a la tabla, para que refresque la zona de datos
	if (1==1){ // Solo lo haga una vez
		// Apuntador a la tabla HTML 
		const tabla = document.getElementById(cTabla);
		// Obtén todos los renglones de la tabla.
		const renglones = tabla.getElementsByTagName("tr");

		// Agrega un evento "click" a cada  renglón.
		for (let i = 0; i < renglones.length; i++) {
			renglones[i].addEventListener("click", function() {
//				a.id_concimovimiento,a.idcuentabancaria,a.idoperacion,a.conciliado,a.fechaconciliacion,a.importeoperacion,"
//				a.id_layout_banco,a.fechaoperacion,a.concepto,a.fcve_ope_be,a.idmovimiento,a.usuario_alta,a.fecha_alta "
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
				cFechaCap	= this.cells[7].textContent;
		    	cOpe		= this.cells[2].textContent;
		    	cRefe		= this.cells[6].textContent;
		    	cStaConci	= this.cells[3].textContent;
		    	cFecConci	= this.cells[4].textContent;
				cId			= this.cells[0].textContent;
				cCveLay		= this.cells[9].textContent;
				cId1		= this.cells[10].textContent;
				cCpto		= this.cells[8].textContent;

				
				nImpo     = this.cells[5].textContent;
				nImpo	  = parseFloat(nImpo.replace(/,/g, '')); // Se debe pasar a un valor numérico
				nImpo 	  = nImpo.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });


			    document.getElementById("idFecha").value 			= cFechaCap
			    document.getElementById("idOpera").value 			= cOpe;
			    document.getElementById("idImpo").value 			= nImpo;
			    document.getElementById("idRefe").value 			= cRefe
				document.getElementById("idStaConci").value 		= cStaConci
				document.getElementById("idFecConci").value 		= cFecConci
			    document.getElementById("id_concimovimiento").value	= cId;
			    document.getElementById("id_movIne").value			= cId1;
			    document.getElementById("idCveLay").value 			= cCveLay;
			    document.getElementById("idCpto").value 			= cCpto;


				// Input ocultos
				// document.getElementById("conciRespa").value 	= this.cells[1].textContent;  // hidden
				// document.getElementById("fechaRespa").value 	= this.cells[2].textContent;  // hidden
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
    paginaBancos("0");
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
	document.querySelector("#idCuentabancaria").value 	= cCta;
	document.querySelector("#idFecha").value 			= dHoy; // fddmmyyyy(dHoy,'-');
	FocoEn("idFecha");
}
// __________________________________________________________________________________
function GrabarMovimiento(){
	cCta = document.querySelector("#idCuentabancaria").value.trim();
	if (cCta!==""){
		cId		= document.querySelector("#id_concimovimiento").value.trim();
		cOpcion = (cId==""?"AgregaMovBancario":"ModificaMovBancario");
		cW		= (cId==""?"Adicionar":"Modificar");
		cW1		= (cId==""?"":"con Id="+cId);

		aMovs	= crearDatosMovimientos(cOpcion);
		if (validaCaptura(aMovs)){
			// Espera confirmación del Usuario para grabar el movimiento
			esperaRespuesta(`Desea ${cW} el ${gOperacion} ${cW1}`).then((respuesta) => {
	        	if (respuesta) {
					aMovs.id_concimovimiento = cId;
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
function crearDatosMovimientos(cOpcion){
	cImpo  = document.getElementById("idImpo").value.trim().replace(new RegExp(',', 'g'), '');
	cFecha = document.getElementById("idFecha").value.trim(); // yyyy-mm-dd

	return { // no cambiar el orden ya que afecta a la función validaCaptura
		opcion 				: cOpcion,
		idCuentabancaria 	: RegresaCtaBancaria("idCuentabancaria"),
		idFecha				: cFecha,
		idOpera				: document.getElementById("idOpera").value,
		idImpo				: cImpo,
		idRefe				: document.getElementById("idRefe").value.trim(),
		idStaConci			: document.getElementById("idStaConci").value,
		idFecConci			: document.getElementById("idFecConci").value,
		idCpto				: document.getElementById("idCpto").value.trim().toUpperCase(),
		idMovBanco			: document.getElementById("id_concimovimiento").value,
		idmovimiento		: document.getElementById("id_movIne").value,
		fcve_ope_be			: document.getElementById("idCveLay").value

	}
}
// __________________________________________________________________________________
function validaCaptura(aCampos){
	aTitulos = ["","Cuenta Bancaria", "Fecha Operación","Clave Operación","Importe","Referencia Bancaria", 
				"Conciliado","Fecha Conciliación","Concepto"];
	i = 0;
	for (const propiedad in aCampos) {// Recorre los input a validar
		if(i>0 && i<9){
			if (i==6 || i==7){ // Conciliado y fecha de conciliación pueden ir vacíos
				if (i==6){
					cStaConci = aCampos[propiedad].trim();
				}else{
					cFecConci = aCampos[propiedad].trim();
				}
			}else {
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
		  	if ( i==2){
		  		cFecOpe = aCampos[propiedad].trim();
		  	}
	  	}
	  	i++;
	}
	if ( cStaConci=="S" && cFecConci==""){
		mandaMensaje("Se requiere fecha de conciliación ["+cFecConci+"]");
		return false;
	}
	if ( cStaConci=="S" ){
		if (cFecConci<cFecOpe){
			FocoEn("idFecConci");
			mandaMensaje("La fecha de conciliación no puedo ser anterior a la fecha de operación");
			
			return false;
		}
	}
	// Se vuelven a validar algunos campos, 

	if ( soloImportes(aTitulos[4],"idImpo") ){
		if ( soloLetrasNumerosSeparadores(aCampos.idCpto, aTitulos[8],"idCpto") ){
			if ( soloLetrasNumerosGuion(aCampos.idRefe,aTitulos[10],"idRefe") ){
				return true;
			}
		}
	}
	return false;
}
// __________________________________________________________________________________
function validaReferenciaBancos(oRefe){
	cRefe   = oRefe.value.trim();
	cIdMov 	= document.getElementById("id_concimovimiento").value.trim();
	if (cRefe!==""){
		aDatos	= { 
			opcion					: "validaReferenciaBancos", 
			cuenta					: RegresaCtaBancaria("idCuentabancaria") , 
			referencia				: cRefe , 
			id_concimovimiento		: cIdMov
		};
		conectayEjecutaPost(aDatos,cPhp,null);
	}
}
// __________________________________________________________________________________
const EliminarMovimiento = () => {
	cId = document.getElementById("id_concimovimiento").value;
	if (cId===""){
		mandaMensaje("Se requiere seleccionar el movimiento del banco a eliminar");
		return false;
	}
	cClave	  = document.getElementById("idCveLay").value;
	cFecConci = document.getElementById("idFecConci").value;
	cMensaje  = `Desea eliminar el movimiento con Id ${cId} `+(cFecConci==""?"":"que ya esta conciliado")+"?";

	if ( cClave=="SDO" ){
		cFecha   = document.getElementById("idFecha").value
		cMensaje = 	`Desea eliminar el movimiento con Id ${cId} `+(cFecConci==""?"":"que ya esta conciliado")+
					` y que es saldo del día ${cFecha} ?`;
	}
	esperaRespuesta(cMensaje).then((respuesta) => {
    	if (respuesta) {
    		aDatos = {
    			opcion	: "EliminarMovimiento",
    			cuenta	: RegresaCtaBancaria("idCuentabancaria") ,
    			idMov	: cId
    		}
			conectayEjecutaPost(aDatos,cPhp,null);
		}
	});
}
// __________________________________________________________________________________
const ConciliaMovBanco = () => {
	vIdMov		= document.getElementById("id_concimovimiento").value.trim();
	vconRes		= document.getElementById("conciRespa").value.toUpperCase();	// variable hidden
	vFecRes		= document.getElementById("fechaRespa").value;					// variable hidden 
	vCuenta 	= document.getElementById("idCuentabancaria").value;

	if ( vIdMov === "" ){
		mandaMensaje("Se requiere seleccionar el movimiento del banco a conciliar");
		return false;
	}
	vConciliado = document.getElementById("idStaConci").value.toUpperCase().trim();
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
		opcion		: "ConciliaMovBanco",
		cuenta		: vCuenta ,
		fecha		: vFechaConci ,
		id			: vIdMov,
		status		: vConciliado
	}
	conectayEjecutaPost(aDatos,cPhp,null);
}
// __________________________________________________________________________________
function ReporteConciliacion(){
	dFecha = document.getElementById("idFecha").value;
	if (dFecha > dHoy ){
		mandaMensaje("No se pueden procesar fechas futuras");
		document.getElementById("idFecha").value = dHoy;
		FocoEn("idFecha");
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
