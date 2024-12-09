// * * * * * * * * * * * * * * * * * * * * * * * * * 
// * Autor   : Miguel Ángel Bolaños Guillén        *
// * Sistema : Sistema de Operación Bancaria Web   *
// * Fecha   : Septiembre 2023                     *
// * Descripción : Rutinas para realizar el enlace * 
// *               entre JavaScript y PHP          *
// * * * * * * * * * * * * * * * * * * * * * * * * *
var conexion;			// Variable global para la conexión
var una_vez = true;		// Variable global para que algun proceso se ejecute so-lo una vez
var cPhp	= "Usuarios_.php"

// onload se ejecuta cuando se carga el formulario HTML y que tiene incrustado 
// eL script llamando a Usuarios_.js 
window.onload = function () {
	var loc     = window.location;
    var cHtml 	= loc.pathname.substring(loc.pathname.lastIndexOf('/') + 1);
	// Casos en que se rquiere cargar información, antes de la captura del usuario
	// Por eso se pregunta por el nombre del archivo que invoca este JavaScript
	switch(cHtml){
		// -----------------------
		case "OpeFin01_01Usuarios.php":
			// Se requiere ver si hay usuarios para cargarlos en una tabla HTML
			aParametros	= {
				opcion :  "UsuariosConsulta"
			} 	// Va al servidor por los Usuarios 
			// Se realiza la conexión para que consulte Usuarios
			conectayEjecutaPost(aParametros,cPhp,null);
		break;
	}
};
// __________________________________________________________________________________________________________
// __________________________________________________________________________________________________________
async function procesarRespuesta__(vRespuesta) {				// Define una promesa para esta función
	// Solo entra aqui si respuesta.sucess es true, si es false se tendría que modificar en rutinas la respuesta
    switch(vRespuesta.opcion) {
        case "UsuariosConsulta":
            await UsuariosTabla(vRespuesta.resultados,vRespuesta.combo);	// Despliega la nueva información en la tabla HTML
            break;
        case "UsuarioAgrega": 												// Ya debio de agregar el nuevo Usuario
            await UsuariosTabla(vRespuesta.resultados,vRespuesta.combo);	// Actualiza la tabla con el nuevo Usuario
            break;
        case "UsuarioModifica":
            await UsuariosTabla(vRespuesta.resultados,vRespuesta.combo); 	// Despliega la información modificada en la tabla HTML
            break;
        case "UsuarioEliminar": 							// Una vez que haya eliminado el Usuario
        	await UsuariosTabla(vRespuesta.resultados,vRespuesta.combo);
        	break;			// Refresca la tabla HTML con los nuevos datos
    	case "validaLdap":
    		await UsuariosLdap(vRespuesta.resultados);
    	break;
// 		------------------------------------ 
    }
}

// * * * * * * * * * * * * * * * * * * * * * * * * * * *
// * Funciones relacionadas a OpeFin01_01Usuarios.php  *
// * * * * * * * * * * * * * * * * * * * * * * * * * * *
// __________________________________________________________________________________________________________
function UsuarioAgrega(){
// Llena aUsuario con la opción que se ejecutara en PHP y los datos de captura 
	var aUsuario = crearDatosUsuario("UsuarioAgrega"); 	
	if ( validaUsuario(aUsuario)){					// Valida que los datos esten completos
		conectayEjecutaPost(aUsuario,cPhp,null);	// Se enlaza con el PHP Usuario_. php
	}
}
// __________________________________________________________________________________________________________
function UsuarioModifica(){
	// Llena aUsuario con la opción que se ejecutara en PHP y los datos de captura 
	var aUsuario = crearDatosUsuario("UsuarioModifica"); 
	//console.log("Estatus["+aUsuario.estatus+"]");
	if ( validaUsuario(aUsuario)){				// Valida que los datos esten completos
		conectayEjecutaPost(aUsuario,cPhp,null);
	}
}
// __________________________________________________________________________________________________________
function UsuarioEliminar(){
	var aUsuario = crearDatosUsuario("UsuarioEliminar");
	if ( validaUsuario(aUsuario)){				// Valida que los datos esten completos
		esperaRespuesta(`Desea eliminar el usuario : ${aUsuario.idUsuario} `).then((respuesta) => {
			if (respuesta) {
				//conectayEjecuta(aDatos);				
				conectayEjecutaPost(aUsuario,cPhp,null); // Llma a la rutina UsuarioEliminar que debe de estar en el php Usuario_. php
			}
		});
	}
}
// __________________________________________________________________________________________________________
function validaUsuario(aUsuario){
	var regreso = false;
	if ( tieneValor(aUsuario.idUsuario,"Id Usuario","idUsuario") ){
		if ( soloDominio(aUsuario.idUsuario,"Id Usuario","idUsuario") ){						// Verifica que idUsuario con formato nombre.apellido
			if ( tieneValor(aUsuario.nombre,"Nombre","nombre") ){								// Verifica que el Nombre no este vacía
				if (sololetras(aUsuario.nombre,"Nombre","nombre")){								// Nombre so-lo puede tener letras y espacios
					if (tieneValor(aUsuario.idUnidad,"Ur Empleado","idUnidad")){				// Ur no vacia
						if (tieneValor(aUsuario.idEsquema,"Esquema Usuario","idEsquema")){	// Esquema no vacía
							regreso = true;
						}
					}
				}
			}
		}
	}
	return regreso;
}
// __________________________________________________________________________________________________________
function crearDatosUsuario(cOpcion) {
	return {
    	opcion 		: cOpcion,
	    idUsuario 	: document.getElementById("idUsuario").value.trim(),
	    nombre		: document.getElementById("nombre").value.trim().toUpperCase(),
	    idUnidad	: document.getElementById("idUnidad").value.trim(),
	    idEsquema   : document.getElementById("idEsquema").value.trim(), // esquema idEsquema
	    estatus 	: document.getElementById("estatus").checked,
  	};
}
// __________________________________________________________________________________________________________
function UsuariosTabla(aRen,aCombo){// aRen contiene todos los elementos que regreso el select a Usuarios
	// Se obtiene un apuntador a el cuerpo de la tabla HTML
	var table = document.getElementById("usuarios").getElementsByTagName('tbody')[0];

	limpiaTabla(table)

	// Se llena la tabla con los datos que regreso el SQL
	aRen.forEach(function(item) {
		var aCeldas = []; // Crear un arreglo vacío
  		var row 	= table.insertRow(-1); // Inserta una nueva fila al final de la tabla.
  		for(i=0;i<(Object.keys(item).length);i++){ // item tiene propiedades con indice (0....N) y nombre de propiedad (nombre1...nombreN)
  			var celda = row.insertCell(i);
  			aCeldas.push(celda);
  		}
		aObjeto = Object.values(item); 
  		//for(i=0;i<(Object.keys(item).length);i++){ // Esto es cuando se usa stm y fetchall con pdo
		for(i=0;i<aObjeto.length;i++){

  			if ( typeof aObjeto[i]=== 'boolean' ){
				aCeldas[i].innerHTML = aObjeto[i]?"SI":"NO";
  			}else{
				aCeldas[i].innerHTML = aObjeto[i]
			}

		}
		detente = 0;
	});

	// Asigna escucha click a la tabla
	if (1==1){ // So-lo lo haga una vez
		// Apuntador a la tabla HTML de esquemas
		const tabla = document.getElementById("usuarios");
		// Obtén todos los renglones de la tabla.
		const renglones = tabla.getElementsByTagName("tr");

		// Agrega un evento "click" a cada  renglón.
		for (let i = 0; i < renglones.length; i++) {
			renglones[i].addEventListener("click", function() {
		    	// Acción que deseas realizar cuando se haga clic en el renglón.
		    	// Pasar los datos de la tabla a la zona de captura
			    document.getElementById("idUsuario").value 	 = this.cells[0].textContent;
			    document.getElementById("nombre").value 	 = this.cells[1].textContent;
			    document.getElementById("idUnidad").value 	 = this.cells[2].textContent;
			    document.getElementById("estatus").checked 	 = this.cells[3].textContent==="SI"?true:false;
			    document.getElementById("idEsquema").value   = this.cells[7].textContent; // aqui va el idesquema por eso es 7
			    //document.getElementById("idEsquema").value = this.cells[3].textContent;
			    //console.log("Click ["+this.cells[2].textContent+"]");
		  	});
		}
		//una_vez = false;
	}
	if (una_vez){
		// Obtén una referencia al elemento select
		var select = document.getElementById("idEsquema");
		llenaCombo(select,aCombo);
		una_vez = false;
	}

}
// ______________________________________________
function validaLdap(){
	cUsu 		= document.getElementById("idUsuario").value.trim();
	//if ( cUsu!="" ){
		if ( soloDominio(cUsu,"Usuario:","idUsuario") ){
			/* SiteGround
			aParametros = {
				opcion 		: "validaLdap",
			    idUsuario 	: cUsu,
			}
			//conectayEjecuta(aParametros);
			conectayEjecutaPost(aParametros,cPhp,null); */
		}
	//}
}
// ______________________________________________
function UsuariosLdap(vLdap){
	document.getElementById("nombre").value		= vLdap["cn"]["0"]; // Nombre Completo del Usuario
	document.getElementById("idUnidad").value	= vLdap["ur"];		// Unidad donde labora el empleado
}
// __________________________________________________________________________________________________________
// __________________________________________________________________________________________________________