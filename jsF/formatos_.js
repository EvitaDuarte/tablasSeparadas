var cPhp    	= "Formatos_.php";	// En este php estarán las funciones que se infocaran desde este JS
var aCopia		= null;


window.onload = function () {		// Función que se ejecuta al cargar la página HTML
	// Se obtiene el nombre del archivo que lo invoca
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);

	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
// 		_____________________________________________________
		case "OpeFin01_06Formato.php":
			aDatos = {
				opcion : "traeCatalogoCuentasBancarias"
			};
			conectayEjecutaPost(aDatos,cPhp,null);
		break;
// 		_____________________________________________________
// 		_____________________________________________________
	}
}
// _____________________________________________________________
async function procesarRespuesta__(vRes) {		
	cOpc = vRes.opcion.opcion;					// Es como se re cupera en PHP la opción 
	//cons ole.log("Opción de regreso : "+ cOpc);
    switch(cOpc) {
// 		_____________________________________________________
    	case "traeCatalogoCuentasBancarias":
    		await traeCatalogoCuentasBancarias(vRes.resultados);
    	break;
// 		_____________________________________________________
		case "traeFormatoCheque":
			await traeFormatoCheque(vRespuesta.resultados);
		break;
// 		_____________________________________________________
		case "modificaFormato":
			await ValidaFormato(document.getElementById("idCuentabancaria").value);
		break; 
// 		_____________________________________________________
    }
}
// _____________________________________________________________
function traeCatalogoCuentasBancarias(aCtas){
	llenaComboCveNombre( document.getElementById("idCuentabancaria") , aCtas	);
}
// _____________________________________________________________
const ValidaFormato = (cCta) =>{
	if (cCta===""){
		mandaMensaje("Seleccione cuenta");
		FocoEn("idCuentabancaria");
		return false;
	}
	aDatos = {
		opcion: "traeFormatoCheque",
		cuenta: cCta
	}
	conectayEjecutaPost(aDatos,cPhp);
}
// _____________________________________________________________
const traeFormatoCheque = (aRen) =>{
	i = 1;
	aCopia = [];
	aRen.forEach(function(item) {
		document.getElementById(`r${i}c2`).value = item.x;
		document.getElementById(`r${i}c3`).value = item.y;
		document.getElementById(`r${i}c4`).value = item.altura;
		document.getElementById(`r${i}c5`).value = item.anchura;
		document.getElementById(`r${i}c6`).value = item.font;
		document.getElementById(`r${i}c7`).value = item.fontsize;
		document.getElementById(`r${i}c8`).value = item.alineacion;
		i= i +1;
		copia  = ["",item.x,item.y,item.altura,item.anchura,item.font,item.fontsize.toString(),item.alineacion]
		aCopia.push(copia) 
	});
}
// _____________________________________________________________
const grabarFormato = () =>{
	cCta = document.getElementById("idCuentabancaria").value;
	if (cCta===""){
		mandaMensaje("Seleccione Cuenta Bancaria");
		FocoEn("idCuentabancaria");
		return false;
	}
	esperaRespuesta(`Desea actualizar el formato de Cheques de la Cuenta ${cCta}`).then((respuesta) => {
        if (respuesta) {
        	aInputs		= valoresInputs();
        	aModificar	= verificaCambios(aInputs)
        	if ( aModificar.length ==0 ){
        		mandaMensaje("No hay cambios a actualizar")
        	}else{
        		aDatos ={
        			opcion	 : "modificaFormato",
        			cuenta	 : cCta,
        			aValores : aModificar
        		}
        		conectayEjecutaPost(aDatos,cPhp);
        	}
        }
    });
}
// _____________________________________________________________
const verificaCambios = (aInputs) =>{
	var renglonesModificar	= [];
	var lCambios 			= false;

// Iterar sobre cada fila
	for (var i = 0; i < 8; i++) {
	    // Variable para almacenar si el renglón debe modificarse
	    var modificarRenglon = false;
	    // Iterar sobre cada columna desde la columna 1 hasta la 8
	    for (var j = 1; j < 8; j++) {
	        // Verificar si el valor en aValores es diferente al valor en aCopia
	        if (aInputs[i][j] !== aCopia[i][j]) {
	            // Si hay diferencia, marcar el renglón como que debe modificarse
	            modificarRenglon = true;
	            // No es necesario seguir verificando el renglón, salir del bucle interno
	            break;
	        }
	    }
	    // Si el renglón debe modificarse, agregarlo al arreglo de renglones a modificar
	    if (modificarRenglon) {
	        renglonesModificar.push(aInputs[i]);
	        lCambios = true;
	    }
	}
	let detente = 0;
	return renglonesModificar;
}
// _____________________________________________________________
const valoresInputs = () =>{
	var valores = [];

	// Obtener todas las filas
	var filas = document.querySelectorAll('.forms-row');

	// Iterar sobre cada fila
	filas.forEach(function(fila) {
    	// Obtener todos los inputs de la fila actual
    	var inputs = fila.querySelectorAll('input');
    	if ( inputs[0].value !=="Elemento"){ // Se salta una líea de inpust que son encabezados
	    	// Definir un arreglo temporal para almacenar los valores de esta fila
	    	var filaValores = [];

		    // Iterar sobre cada input
		    inputs.forEach(function(input) {
                if (input.hasAttribute('pos')) { // Aquí se guarda la llave del registro
                    // Obtener el valor del atributo 'pos'
                    var valor = input.getAttribute('pos');
                } else {
                    // Obtener el valor del input
                    var valor = (input.value);
                }
		        // Agregar el valor al arreglo temporal
		        filaValores.push(valor);
		    });
	    	// Agregar el arreglo de valores de esta fila al arreglo principal
	    	valores.push(filaValores);
	    }
	});
	return valores;
}
// _____________________________________________________________