// * * * * * * * * * * * * * * * * * * * * * * * * * 
// * Autor   : Miguel Ángel Bolaños Guillén        *
// * Sistema : Sistema de Operación Bancaria Web   *
// * Fecha   : Septiembre 2023                     *
// * Descripción : Rutinas para realizar el enlace * 
// *               entre JavaScript y PHP          *
// * * * * * * * * * * * * * * * * * * * * * * * * *
var conexion;					// Variable global para la conexión
var una_vez = true;				// Variable global para que algun proceso se ejecute so-lo una vez
var cPhp    = "Esquemas_.php";	// En este php estarán las funciones que se invocaran desde este JS


// onload se ejecuta cuando se carga el formulario HTML y que tiene incrustado 
// eL script llamando a esquemas.js 
window.onload = function () {
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);
	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// -----------------------
		case "OpeFin01_02Esquemas.php":
			// Se requiere ver si hay esquemas para cargarlos en una tabla HTML
			aParametros	= {
				opcion :  "EsquemasConsulta"
			} 	// Va al servidor por los esquemas 
			// Se realiza la conexión para que consulte Esquemas
			conectayEjecutaPost(aParametros,cPhp,null);
		break;
	}
};
// __________________________________________________________________________________________________________
// __________________________________________________________________________________________________________
async function procesarRespuesta__(vRespuesta) {				// Define una promesa para esta función
    switch(vRespuesta.opcion) {
        case "EsquemasConsulta":
            await EsquemasTabla(vRespuesta.resultados);		// Despliega la nueva información en la tabla HTML
            break;
        case "EsquemaAgrega": 								// Ya debio de agregar el nuevo Esquema
            await EsquemasTabla(vRespuesta.resultados);		// Actualiza la tabla con el nuevo esquema
            break;
        case "EsquemaModifica":
            await EsquemasTabla(vRespuesta.resultados); 	// Despliega la información modificada en la tabla HTML
            break;
        case "EsquemaEliminar": 							// Una vez que haya eliminado el esquema
        	await EsquemasTabla(vRespuesta.resultados);
        	break;			// Refresca la tabla HTML con los nuevos datos
// 		------------------------------------ 
    }
}

// * * * * * * * * * * * * * * * * * * * * * * * * * * *
// * Funciones relacionadas a OpeFin01_02Esquemas.php  *
// * * * * * * * * * * * * * * * * * * * * * * * * * * *
// __________________________________________________________________________________________________________
function EsquemaAgrega(){
// Llena aDatos con la opción que se ejecutara en PHP y los datos de captura idesquema, descripción y estatus	
	var aDatos = crearDatosEsquema("EsquemaAgrega"); 	
	if ( validaEsquema(aDatos)){				// Valida que los datos esten completos
		conectayEjecutaPost(aDatos,cPhp,null);
	}
}
// __________________________________________________________________________________________________________
function EsquemaModifica(){
	// Llena aDatos con la opción que se ejecutara en PHP y los datos de captura idesquema, descripción y estatus
	var aDatos = crearDatosEsquema("EsquemaModifica"); 
	//console.log("Estatus["+aDatos.estatus+"]");
	if ( validaEsquema(aDatos)){				// Valida que los datos esten completos
		conectayEjecutaPost(aDatos,cPhp,null);
	}
}
// __________________________________________________________________________________________________________
function EsquemaEliminar(){
	var aDatos = crearDatosEsquema("EsquemaEliminar"); 
	if ( validaEsquema(aDatos)){				// Valida que los datos esten completos
		esperaRespuesta(`Desea eliminar el esquema con id : ${aDatos.idEsquema} `).then((respuesta) => {
			if (respuesta) {
				conectayEjecutaPost(aDatos,cPhp,null);
			}
		});
	}
}
// __________________________________________________________________________________________________________
function validaEsquema(aDatos){
	var regreso = false;
	if ( tieneValor(aDatos.idEsquema,"Id Esquema","idEsquema") ){
		if ( soloNumeros(aDatos.idEsquema,"Id Esquema","idEsquema")){							// Verifica que idEsquema sea numérico
			if ( tieneValor(aDatos.descripcion,"Descripción Esquema","descripcion") ){			// Verifica que la descripción no este vacía
				if (sololetras(aDatos.descripcion,"Descripción","descripcion")){				// Descrpción so-lo puede tener letras y espacios
					regreso = true;
				}
			}
		}
	}
	return regreso;
}
// __________________________________________________________________________________________________________
function crearDatosEsquema(cOpcion) {
	return {
    	opcion 		: cOpcion,
	    idEsquema 	: document.getElementById("idEsquema").value,
	    descripcion : document.getElementById("descripcion").value.trim(),
	    estatus 	: document.getElementById("estatus").checked,
  	};
}
// __________________________________________________________________________________________________________
function EsquemasTabla(aRen){// aRen contiene todos los elementos que regreso el select a esquemas
	// Se obtiene un apuntador a el cuerpo de la tabla HTML
	var table = document.getElementById("esquemas").getElementsByTagName('tbody')[0];

	limpiaTabla(table)

	aRen.forEach(function(item) {
  		var row = table.insertRow(-1); // Inserta una nueva fila al final de la tabla.

  		// Supongamos que item es un objeto con propiedades correspondientes a cada columna.
  		var cell1 = row.insertCell(0); // Tantos insert como id se requieran 
  		var cell2 = row.insertCell(1); // descripción
  		var cell3 = row.insertCell(2); // Activo
  		var cell4 = row.insertCell(3); // Usuario que da de alta
  		var cell5 = row.insertCell(4); // Fecha de el alta


  		// Asigna los valores de las propiedades a las celdas.
  			cell1.innerHTML = item.idesquema;
  			cell2.innerHTML = item.descripcion;
  			cell3.innerHTML = item.estatus?"SI":"NO";
  			cell4.innerHTML = item.idusuario;
  			cell5.innerHTML = item.fechaalta;

  		// Puedes agregar más celdas según sea necesario para más columnas.
	});

	// Asigna escucha click a la tabla
	if (1==1){ // So-lo lo haga una vez
		// Apuntador a la tabla HTML de esquemas
		const tabla = document.getElementById("esquemas");
		// Obtén todos los renglones de la tabla.
		const renglones = tabla.getElementsByTagName("tr");

		// Agrega un evento "click" a cada  renglón.
		for (let i = 0; i < renglones.length; i++) {
			renglones[i].addEventListener("click", function() {
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
			    document.getElementById("idEsquema").value 	 = this.cells[0].textContent;
			    document.getElementById("descripcion").value = this.cells[1].textContent;
			    document.getElementById("estatus").checked 	 = this.cells[2].textContent==="SI"?true:false;
			    //console.log("Click ["+this.cells[2].textContent+"]");
		  	});
		}
	}
}
// __________________________________________________________________________________________________________
// __________________________________________________________________________________________________________