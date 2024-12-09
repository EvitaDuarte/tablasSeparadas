var pagCta		 = "";					// Guarda la cuenta Bancaria para el paginado
var cPhp    	 = "Reintegros_.php";	// En este php estarán las funciones que se infocaran desde este JS
var gForma		 = ""					// Formulario que se este ejecutando
var dHoy		 = "";
var dAnio		 = "";					// Año actual
var gOpeConci	 = [];
var gFiltro		 = false;
var gCampo		 = "";
var gTipo		 = "";
var gOperacion	 = "movimiento de reintegro";
const UrSet		 = new Set(); 	// Para validar si la UR del archivo de Layout esta en el catálogo
const OriSet	 = new Set();	// Para validar si el origen del archivo de LayOut esta en el catálogo
const CtaSet	 = new Set();


window.onload = function () {		// Función que se ejecuta al cargar la página HTML
	// Se obtiene el nombre del archivo que lo invoca
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);

	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// ______________________________________
		case "OpeFin03_02Reintegros.php":


			gForma 	 	= "formReintegros";
			gTabla	 	= "tablaReintegros";
			aDatos = {
				opcion 	: "CargaCatalogos"
			};
			// 
			quitaSubmit(gForma);
			conectayEjecutaPost(aDatos,cPhp,null);// Esta función esta en rutinas_.js
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
// _______________________________________________________________________________________
// Define una promesa para esta función, despues de ejecutar la opción correspondiente en PHP
// Esto lo ejecuta el cliente despues de invocar algun proceso en la función correspondiente PHP
// Son los regresos despues de que se invoco al servidor
async function procesarRespuesta__(vRes) {		
	cOpc = vRes.opcion.opcion;					// Es como se re cupera en PHP la opción 
	cPag = document.getElementById("pagina").value
	//cons ole.log("Opción de regreso : "+ cOpc);
    switch(cOpc) {
// __________________________________________________________________________________
        case "CargaCatalogos": // Se llenaron los catalogos iniciales y hay que pasarlos a el HTML
            // no hay una tabla HTML a procesar
            //con sole.log("Opcion="+cOpc);
            await CargaCatalogos(vRes);
            await consultaReintegros(); // Hace una llamada a una función en este JS para llenar la tabla
            // await escuchaFoco(["idcuentabancaria"]);
        break;
// __________________________________________________________________________________
		case "FiltraMovimientosReintegros":
			await RegresoConsultaReintegros(vRes);
		break; 		
// __________________________________________________________________________________
		case "AgregaReintegro":
			await paginaReintegros(cPag);
			NuevoReintegro("");
		break; 
// __________________________________________________________________________________
		case "ModificaReintegro":
			await paginaReintegros(cPag);
		break;
// __________________________________________________________________________________
		case "EliminarReintegro":
			await paginaReintegros( cPag );
			NuevoReintegro("");
		break;
// __________________________________________________________________________________
		case "ReporteReintegros":
			await abrePdf(vRes.archivo);
		break;
// __________________________________________________________________________________
		case "cargaLayout":
			await paginaReintegros( cPag );
			document.getElementById("selectLayOut").value 		= "";
			document.getElementById("ArchivoCarga_file").value	= null; // no funciono ni con value = ""; 
		break;
// __________________________________________________________________________________
		default:
			mandaMensaje("No se encontro código JS de retorno para ["+cOpc+"]");
		break;
// __________________________________________________________________________________
    }   
}
// _______________________________________________________________________________________
function CargaCatalogos(vRes){
	if (vRes.unidades!=null){	// Puede venir nula si no se han asignado cuentas al usuario
		llenaComboCveDes( document.getElementById("idunidad")			, vRes.unidades 	);
		llenaComboCveDes( document.getElementById("origen")   			, vRes.origenes 	);
		llenaComboCveDes( document.getElementById("idcuentabancaria")   , vRes.cuentas		);
		dHoy										= vRes.opcion.hoy;
		cAnio										= dHoy.substring(0,4);
		document.getElementById("idFecFin").value	= dHoy;

		document.getElementById("idFecIni").value	= cAnio+"-01-01"; // Enero del presente año
		document.getElementById("fecha_ope").value	= dHoy;
		document.getElementById("anioRi").value		= cAnio;
		document.getElementById("anioRf").value		= cAnio;
		document.getElementById('fecha_ope').setAttribute('max', dHoy);
		document.getElementById("idFecFin").setAttribute("max",dHoy);
		document.getElementById("idFecIni").setAttribute("max",dHoy);
		// 
		 vRes.unidades.forEach((unidad) => {
		 	UrSet.add(unidad["descripcion"].trim()); 

		 });
		 vRes.origenes.forEach((origen) => {
			OriSet.add(origen["clave"].trim());
		 });
		 vRes.cuentas.forEach((cuenta) => {
			CtaSet.add(cuenta["clave"].trim());
		 });
		 let detente = 0;
	}else{
		mandaMensaje("No se han definido centros de costos");
	}
}
// _______________________________________________________________________________________
function cambiaDes(cValor){
	aVal    = cValor.split(",");
	aVal[2] = 
	aVal[2] = aVal[2].replace(/junta local ejecutiva/i, "J.L.E");
	aVal[2] = aVal[2].replace(/junta distrital ejecutiva/i, "J.D.E");
	document.getElementById("nombreunidad").value 	= aVal[2];
	document.getElementById("cta_ur").value			= aVal[1];
}
// _______________________________________________________________________________________
const consultaReintegros = () => {
    gFiltro = false;
	paginaReintegros(-1);
}
// _______________________________________________________________________________________
const paginaReintegros = (cPag) =>{
	aCampos = [];
	aTipo   = [];
	cCta    = pagCta; // pagCta se inicializa en el onchange del select de cuentas bancarias
	pagina  = document.getElementById("pagina").value;
	cBuscar = document.getElementById("opcionesFiltro").value.trim();
	cCampos =  "a.idreintegro,a.idunidad,a.folio,a.oficio,a.monto,a.origen,a.economia,a.pasivo,a.operacion,"
	cCampos += "a.fecha_ope,a.anio,a.folio_interno,a.idcuentabancaria,a.cvectrl,cvemov,a.usuarioalta,a.fechaalta "
	cTipos  =  "N,C,C,C,NF,C,L,L,C,D,N,C,C,C,C,C,D"; // C - Caracter , N Número , D - Fecha, NF - Númerico con comas
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
				aCampos = ["a.monto"];
				aTipo   = ["NF"]; // número con formato
			break;
			case "F":	// Fecha Operacion
				aCampos = ["a.fecha_ope"];
				aTipo   = ["D"]
			break;
			case "B":	// Folio
				aCampos = ["a.folio"];
				aTipo   = ["C"]
			break;
			case "O":	// Operación 
				aCampos = ["a.operacion"];
				aTipo   = ["C"]
			break;
			case "R":	// Folio Interno
				aCampos = ["a.folio_interno"];
				aTipo   = ["C"]
			break;
			case "D":	// Oficio
				aCampos = ["a.oficio"];
				aTipo   = ["C"]
			break;
		}
	}
	//
	aDatos = {
		opcion 			: "FiltraMovimientosReintegros",
		limite			: document.getElementById("num_registros").value,
		busca			: document.getElementById("campo").value,
		pagina 			: pagina,
		tabla			: "jle.reintegros a ",
		tablaPrin		: "jle.reintegros",
		join			: "   ",
		campos			: cCampos,
		tipos			: cTipos,
		id 				: "idreintegro",
		regreso			: "",
		order			: "order by fecha_ope desc",
		depura			: "",
		traeOperaciones	: true,
		aCampos			: aCampos,
		aTipos			: aTipo
	};
	//
	conectayEjecutaPost(aDatos,cPhp,null);
}
// _______________________________________________________________________________________
const RegresoConsultaReintegros = (aRespuesta) =>{
	var cTabla 	 = gTabla;
	var table  	 = document.getElementById(cTabla).getElementsByTagName('tbody')[0];
	var navega	 = document.getElementById("nav-paginacion");
	lNoModificar = false;
	lDiaAnterior = false;

	limpiaTabla(table);
	// Se llenan ya con los datos formateados con tr y td que se contruyen en el php
	table.innerHTML  = aRespuesta.opcion.regreso.data;
	navega.innerHTML = aRespuesta.opcion.regreso.paginacion; // debe estar la función nextpage()
	// ***************************
    // configurar los renglones de la tabla html para que al dar clic se refresque los datos de los inputs
	// Apuntador a la tabla HTML de esquemas
	const tabla = document.getElementById(cTabla);
	// Obtén todos los renglones de la tabla.
	const renglones = tabla.getElementsByTagName("tr");

	// Agrega un evento "click" a cada  renglón.
	for (let i = 0; i < renglones.length; i++) {
		renglones[i].addEventListener("click", function() {
	    	// Acción que deseas realizar cuando se haga clic en el renglón.
	    	// Pasar los datos de la tabla a la zona de captura
			cEco 	= this.cells[6].textContent;
			cPas	= this.cells[7].textContent;
			nImpo	= this.cells[4].textContent;
			nImpo	= parseFloat(nImpo.replace(/,/g, '')); // Se debe pasar a un valor numérico
			nImpo	= nImpo.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
			if ( cEco=="f" && cPas=="f"){
				cTipo = "";
			}else if (cEco =="t" && cPas=="f"){
				cTipo = "E";
			}else if(cEco=="f" && cPas=="t"){
				cTipo = "P";
			}else{
				cTipo = "PE";
			}
	    	obtenUr(this.cells[1].textContent);
			//document.getElementById("idunidad").value 			= this.cells[1].textContent;
			document.getElementById("folio").value 				= this.cells[2].textContent;
			document.getElementById("oficio").value 			= this.cells[3].textContent;
			document.getElementById("monto").value 				= nImpo
		    document.getElementById("origen").value 			= this.cells[5].textContent;
		    document.getElementById("tipo").value 				= cTipo;
		    document.getElementById("operacion").value 			= this.cells[8].textContent;
		    document.getElementById("fecha_ope").value 			= this.cells[9].textContent; // fddmmyyyy(this.cells[4].textContent,"-");
		    document.getElementById("anio").value 				= this.cells[10].textContent;
		    document.getElementById("folio_interno").value 		= this.cells[11].textContent;
		    document.getElementById("idcuentabancaria").value 	= this.cells[12].textContent;
		    document.getElementById("idreintegro").value		= this.cells[0].textContent;

			// En Ingresos, habilita o no la impresión y desglose de ingresos
	  	});
	}
	// ***************************
}
// _______________________________________________________________________________________
function nextPage(pagina){
    document.getElementById('pagina').value = pagina
    paginaReintegros("0");
}
// _______________________________________________________________________________________
const obtenUr = (cUr) =>{
    // Obtén el valor de unidad (suponiendo que 'this' se refiere a una fila de la tabla)
    var unidadValue = cUr
    
    // Obtén la referencia al <select>
    var selectElement = document.getElementById("idunidad");
    
    // Itera sobre las opciones del <select>
    for (var i = 0; i < selectElement.options.length; i++) {
        var option = selectElement.options[i];
        
        // Divide el value de la opción por el delimitador
        var optionValues = option.value.split(',');
        
        // Verifica si el primer valor (unidad) coincide con el valor de unidad
        if (optionValues[0].trim() === unidadValue) {
            // Si coinciden, establece esta opción como seleccionada
            selectElement.selectedIndex						= i;
            document.getElementById("cta_ur").value			= optionValues[1].trim();
            document.getElementById("nombreunidad").value	= optionValues[2].trim();
            break; // Salir del bucle si se encontró una coincidencia
        }
    }
}
// _______________________________________________________________________________________
// _______________________________________________________________________________________
const GrabarReintegro = () =>{
	cUr = document.querySelector("#idunidad").value.trim();
	if (cUr!==""){
		cId		= document.querySelector("#idreintegro").value.trim();
		cOpcion = (cId==""?"AgregaReintegro":"ModificaReintegro");
		cW		= (cId==""?"Adicionar":"Modificar");
		cW1		= (cId==""?"":"con Id="+cId);
		aRei	= crearDatosReintegros(cOpcion);
		if (validaCaptura(aRei)){
			// Espera confirmación del Usuario para grabar el movimiento
			esperaRespuesta(`Desea ${cW} el ${gOperacion} ${cW1}`).then((respuesta) => {
	        	if (respuesta) {
	        		// Nuevo objeto con las mismas propiedades pero con el 2do valor
					const aReiok = Object.keys(aRei).reduce(
						(acc, key) => {
					    acc[key] = aRei[key][1]; // Asignar el segundo valor del arreglo
					    return acc;	
						}, {} // deben ir esta vacías es sintaxis del metodo reduce
					);
					conectayEjecutaPost(aReiok,cPhp,null);
				}
			});
		}
	}else{
		FocoEn("idunidad");
		mandaMensaje("Se requiere seleccionar centro de costo")
	}
}
// _______________________________________________________________________________________
const crearDatosReintegros = (cOpcion) =>{
	cMonto  = document.getElementById("monto").value.trim().replace(new RegExp(',', 'g'), '');
	cTipo	= document.getElementById("tipo").value.trim();
	$cUr	= document.getElementById("idunidad").value.trim().split(",")[0].toUpperCase();
	cEco	= "False"; cPas = "False";
	if (cTipo=="PE"){
		cEco = "True"; cPas = "True";
	}else if (cTipo=="P"){
 		cPas = "True";
	}else if (cTipo=="E"){
		cEco = "True";
	}
	return { // no cambiar el orden ya que afecta a la función validaCaptura
		opcion 				: [""					,cOpcion],
		idunidad 			: ["Centro de Costo"	,$cUr],
		folio				: ["Folio"				,document.getElementById("folio").value.trim().toUpperCase()],
		oficio				: ["Oficio"				,document.getElementById("oficio").value.trim().toUpperCase()],
		monto				: ["Importe"			,cMonto],
		origen				: ["Origen"				,document.getElementById("origen").value.trim().toUpperCase()],
		economia			: [""					,cEco],
		pasivo				: [""					,cPas],
		operacion			: ["Operación"			,document.getElementById("operacion").value.trim().toUpperCase()],
		fecha_ope			: ["Fecha"				,document.getElementById("fecha_ope").value],
		anio				: ["Año"				,document.getElementById("anio").value],
		folio_interno		: [""					,document.getElementById("folio_interno").value.trim().toUpperCase()],
		idcuentabancaria	: ["Cuenta Bancaria"	,document.getElementById("idcuentabancaria").value.trim()],
		cvectrl				: [""					,"RIF"],
		cvemov				: [""					,"OTI"],
		idreintegro			: [""					,document.getElementById("idreintegro").value.trim()]
	}
}
// _______________________________________________________________________________________
const validaCaptura = (oReintegro) =>{ // La propiedad debe coincidir con el id del input html
    for (const propiedad in oReintegro) {
        if (oReintegro.hasOwnProperty(propiedad)) {
            const [titulo, valor] = oReintegro[propiedad];
            
            if (titulo !== "" && valor === "") { // Solo los que tengan titulo
            	cId = `${propiedad}`; // Convierte la propiedad a String,
            	//cId = cId.slice(2).toLowerCase(); // quita los dos primeros caracteres y minúsculas
            	FocoEn(cId)
                mandaMensaje(`Se requiere información para  '${titulo}'`);
                return false; // Salir de la función si la validación falla
            }
        }
    }
    //con sole.log('Validación exitosa para todas las propiedades.');
    return true; // Todas las validaciones fueron exitosas
}
// _______________________________________________________________________________________
function NuevoReintegro(cW){
	// Se limpian todos los elementos HTML 
	document.getElementById(gForma).reset();
	//
	document.querySelector("#fecha_ope").value = dHoy; // fddmmyyyy(dHoy,'-');
	FocoEn("idunidad");
}
// _______________________________________________________________________________________
const EliminarReintegro = () =>{
	cId = document.getElementById("idreintegro").value;

	if (cId===""){
		mandaMensaje("Se requiere seleccionar reintegro a eliminar");
		return false;
	}


	cMensaje  = `Desea eliminar el reintegro con Id ${cId} ?`;

	esperaRespuesta(cMensaje).then((respuesta) => {
    	if (respuesta) {
    		aDatos = {
    			opcion	: "EliminarReintegro",
    			idRei	: cId
    		}
			conectayEjecutaPost(aDatos,cPhp,null);
		}
	});
}
// _______________________________________________________________________________________
const ReporteReintegrosModal = () =>{
	DatosReporteReintegrosModal().then((respuesta) => {
    	if (respuesta) {
    		if ( revisaFechas( document.getElementById("idFecIni"), document.getElementById("idFecFin") )==false ){
    			return false;
    		}
    		cFecIni = document.getElementById("idFecIni").value;
    		cFecFin = document.getElementById("idFecFin").value;
    		cAnioRi = document.getElementById("anioRi").value;
    		cAnioRf = document.getElementById("anioRf").value;
    		if (cAnioRi>cAnioRf){
    			cTemp	= cAnioRf;
    			cAnioRf = cAnioRi;
    			cAnioRi	= cTemp;
    		}
    		cLetrero	=  fechasLetras(cFecIni,cFecFin);
    		cLetrero1	= "Relación de Reintegros : " + cAnioRi + "-" + cAnioRf
    		aDatos = {
    			opcion	 : "ReporteReintegros",
    			fecIni	 : cFecIni,
    			fecFin	 : cFecFin,
    			anioRi	 : cAnioRi,
    			anioRf	 : cAnioRf,
    			letrero  : cLetrero,
    			letrero1 : cLetrero1
    		}
    		conectayEjecutaPost(aDatos,cPhp,null);
    	}
    });
}
// _______________________________________________________________________________________
function DatosReporteReintegrosModal() {
    return new Promise((resolve) => {
        const dialogo    = document.querySelector("#cajaReporteReintegros");
        const btnImprime = document.querySelector('#btnImprime');

       // const dialogRespuesta = document.querySelector('#dialogRespuesta'); // Párrafo de texto
       // dialogRespuesta.textContent = cMensaje; // Cambia el mensaje

        btnImprime.addEventListener('click', (e) => { /* cick asociado al boton impimir */
            e.preventDefault(); // Evitar el envío del formulario ( submit ) y que refresque la pantalla
            dialogo.close();
            resolve(true);		// Regresa a quién lo invoco
        });

        dialogo.showModal();
    });
}
// _______________________________________________________________________________________
const archivoLayOut = (cOpcion) =>{
	if (cOpcion==""){
		mandaMensaje("Seleccione opción");
		return false;
	}
	// Solicita archivo de layOut
	solicitaArchivoLayOut().then((respuesta) => {
		if (respuesta){
			var input1_file = document.getElementById('ArchivoCarga_file');
			if (input1_file.files.length==0){
				document.getElementById("selectLayOut").value = "";
				mandaMensaje("Favor de seleccionar el archivo nuevamente");
				return false;
			}
			var oFile		= input1_file.files[0];
			cFile 			= oFile.name;
			
			esperaRespuesta(`Desea iniciar proceso de carga de ${cFile} `).then((respuesta) => {
				if (respuesta){
					const reader  = new FileReader();
					reader.onload = function (e) {// Se ejecuta una vez que se lea el archivo con reader.readAsText(oFile, 'UTF-8');
						const data = new Uint8Array(e.target.result);
						const workbook = XLSX.read(data, { type: 'array' });
						// Procesa el archivo con la función `procesaCargaReintegros`
						procesaCargaReintegros(workbook);
					};
					reader.readAsArrayBuffer(oFile);
				}
			});

		}
	});
}
// _______________________________________________________________________________________
const procesaCargaReintegros = (workbook) => {
    // Aquí asumimos que deseas procesar la primera hoja del archivo
    const sheetName = workbook.SheetNames[0];
    const sheet = workbook.Sheets[sheetName];
    
    // Convierte la hoja a JSON
    const json = XLSX.utils.sheet_to_json(sheet, { header: 1 });
    
    // Ejemplo: Imprimir los renglones en la consola
    //cons ole.log("Datos de la hoja:", json);
    

    // Validar los datos
    cMensaje = "";
    // UR-Folio-Fecha-Año-Monto-Operación-Oficio-Tipo-Cuenta
    validos  = [];
    json.forEach(row => {
    	if (row[0].trim()!=="UR"){ 	// Saltar encabezado
    		if ( row[0]){ 			// No esta vacío ?
    			lOk = true;
    			// Si un valor es numérico, no lo toma como cadena y el trim no esta disponible
	    		cUr 	= row[0].toString().trim(); cOri = row[7].toString().trim(); cCta = row[8].toString().trim();
	    		cFecha	= row[2].toString().trim(); cAnio= row[3].toString().trim(); cImp = Number(row[4].toString().trim());
	    		nYear 	= parseInt(cFecha.substring(0,4),10);	// El 10 es la base decimal
	    		nMes	= parseInt(cFecha.substring(4,6),10)   ; nDia = parseInt(cFecha.substring(6,8),10); 
	    		fechaV 	= new Date(nYear, nMes - 1, nDia);

		        if ( UrSet.has(cUr)===false ) {
		            // Valor en la primera columna está en los textos del <select>
		             cMensaje += 'UR no encontrada: ' + cUr + "<br>"; lOk = false;
		        } 
		        if ( OriSet.has(cOri)===false ) {
		            // Valor en la primera columna está en los textos del <select>
		             cMensaje += 'Origen no encontrado: ' + cOri + "<br>"; lOk = false;
		            // Aquí puedes hacer algo con los datos de la fila que coincide
		        } 
		        if ( CtaSet.has(cCta)===false ) {
		            // Valor en la primera columna está en los textos del <select>
		             cMensaje += 'Cuenta no encontrada: '+ cCta + "<br>"; lOk = false;
		            // Aquí puedes hacer algo con los datos de la fila que coincide
		        } 
		        if (cFecha.length!=8 || cFecha.slice(0, 2)!="20"){
		        	cMensaje += "Fecha incorrecta " + cFecha + "<br>"; lOk = false;
		        }
		        if ( ! ( fechaV.getFullYear() === nYear && fechaV.getMonth() === nMes - 1 && fechaV.getDate() === nDia ) ){
		        	cMensaje += "Fecha errónea " + cFecha + "<br>"; lOk = false;
		        }
		        if (cAnio.length!=4 || cAnio.slice(0,2)!="20"){
		        	cMensaje += "Año incorrecto " + cAnio + "<br>"; lOk = false;
		        }
				if ( ! ( !isNaN(cImp) && isFinite(cImp) ) ){
					cMensaje += "Importe incorrecto" + cImp + "<br>"; lOk = false;
				}
				if ( lOk){
					aRei = {
						opcion 				: "AgregaReintegro",
						idunidad 			: cUr,
						folio				: row[1].toString().trim(),
						oficio				: row[6].toString().trim(),
						monto				: cImp,
						origen				: cOri,
						economia			: "False",
						pasivo				: "False",
						operacion			: row[5].toString().trim(),
						fecha_ope			: cFecha,
						anio				: cAnio,
						folio_interno		: row[1].toString().trim(),
						idcuentabancaria	: cCta,
						cvectrl				: "RIF",
						cvemov				: "OTI",
						idreintegro			: ""
					}
					validos.push(aRei); // Agregar la fila al arreglo validos
				}
	    	}
	    }
    });
    if ( cMensaje != "" ){
    	mandaMensaje("Revisar información <br>"+cMensaje);
    	return false;
    }
    aDatos = {
    	opcion		: "cargaLayout",
    	reintegros	: validos	
    };
    conectayEjecutaPost(aDatos,cPhp,null);
    //mandaMensaje("Todo Ok "+validos);
}
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________
// _______________________________________________________________________________________