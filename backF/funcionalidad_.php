<?php
// * * * * * * * * * * * * * * * * * * * * * * * * * 
// * Autor   : Miguel Ángel Bolaños Guillén        *
// * Sistema : Sistema de Operación Bancaria Web   *
// * Fecha   : Septiembre 2023                     *
// * Descripción : Rutinas para ejecutar codigo    * 
// *               SQL para interacturar con las   *
// *               tablas de la BD del Sistema     *
// *               Unadm-Proyecto Terminal         *
// * * * * * * * * * * * * * * * * * * * * * * * * *
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	//
	session_start();
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php");exit; return;
	}
	// _______________________________________
	$usrClave     = pg_escape_string($_SESSION['OpeFinClave']);
	//
	include_once("con_pg_OpeFinW_.php"); 	// Se incluye conexión a la Base de Datos
	include_once("rutinas_.php");						// Rutinas de uso general
	require_once("../pdoF/accesos_.php");					// Clase accesos

	global $conn_pdo;
	// inicializa arreglo que se regresara a JavaScript y que se podra visualizar en el depurador del navegador que se activa con F12
	$respuesta = array('success'=>false, 'mensaje'=>array(), 'resultados'=>array(), 'opcion'=>array() , 
						'datos'=>array() , 'combo'=>array(), 'depura'=>array() , 'combo1'=>array() , 'bita'=>false);
	$aDatos	   = json_decode($_REQUEST['aDatos'],true);		// Recupera el arreglo con los datos que envía JavaScript
	// var_dump($aDatos); con request se tiene un arreglo
	if (isset($aDatos[0])){ //Opción a ejecutar, solo JS manda la opción, cuando se cargan catálogos para llenar tablas HTML o selectbox
		$vOpc =  $aDatos[0];
	}else{					// Además de la opción se envían parámetros
		$vOpc = $aDatos["opcion"]; 
	}
//	Se guardan para efectos de depuración
	$respuesta["opcion"] = $vOpc;
	$respuesta["datos"]  = $aDatos;

	//
	switch ($vOpc) {
		// ------------------
		//case 'EsquemasConsulta': // Se regresara el select de la tabla esquemas
		//	EsquemasConsulta($respuesta);
		//	break;
		//case "EsquemaAgrega":	// Para Ingresar un nuevo esquema
		//	EsquemaAgrega($respuesta);	
		//	break;
		//case "EsquemaModifica":
		//	EsquemaModifica($respuesta);
		//	break;
		//case "EsquemaEliminar":
		//	EsquemaEliminar($respuesta);
		//	break;
		// --------------------------------------------
		case "UsuariosConsulta":
			UsuariosCarga($respuesta);
			if ($respuesta["datos"]["traeEsquemas"]){ // Voy por lo que tenga la tabla de esquemas
				UsuariosEsquemas($respuesta);
			}
			break;
		case "validaLdap":
			validaLdap($respuesta);
			break;
		// ___________________________________________
		case "UsuarioAgrega":
			UsuarioAgrega($respuesta);
			break;
		// ___________________________________________
		case "UsuarioModifica":
			UsuarioModifica($respuesta);
			break;
		// ___________________________________________
		case "UsuarioEliminar":
			UsuarioEliminar($respuesta);
			break;
		// ___________________________________________
		case "ConfiguracionCarga":
			ConfiguracionCarga($respuesta);
		break;
		// ___________________________________________
		case "ConfiguracionActualizar":
			ConfiguracionActualizar($respuesta);
		break;
		// ___________________________________________
		case "AccesosConsulta":
			AccesosConsulta($respuesta);
			AccesosCatalogos($respuesta);
			/*if ($respuesta["datos"]["traeCatalogos"]){ // Voy por lo que tenga la tabla de esquemas
				AccesosCatalogos($respuesta);
			}else{
				AccesosCatalogos($respuesta);
			}*/
		break;
		// ____________________________________________
		case "AccesosAgrega":
			AccesosAgrega($respuesta);
		break;
		// ____________________________________________
		case "AccesosBusca":
			AccesosBusca($respuesta);
		break;
		// ____________________________________________
		case "AccesosElimina":
			AccesosElimina($respuesta);
		break;
		// ____________________________________________
		// ____________________________________________
		default:
			$respuesta["mensaje"] = "No esta construida en funcionalidad.php la opción [" . $vOpc . "]";
			break;
	}
	//

	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
return;
// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
// * Funciones relacionadas a OpeFin01_03Configuracion.php"  *
// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
function ConfiguracionCarga(&$respuesta){
	$sql =	"select idconfiguracion,descripcion, valor from configuracion " . 
			"where idconfiguracion='01' or idconfiguracion='02' or idconfiguracion='10'";
	if ( ( $respuesta["resultados"] = ejecutaSQL_($sql) )!=null ){
		$respuesta["success"] = true;
		$respuesta["mensaje"] = "";
	}
	return;
}
// ____________________________________
function ConfiguracionActualizar(&$respuesta){
	global $conn_pdo;
	try {
		$conn_pdo->beginTransaction(); // Empieza la transacción
		ConfiguracionIngresa("01","Año Mínimo Captura Movimientos : ",$respuesta["datos"]["anioMov01"]);
		ConfiguracionIngresa("02","Año Mínimo Captura Reintegros : " ,$respuesta["datos"]["anioRei02"]);
		ConfiguracionIngresa("10",$respuesta["datos"]["deptoRecibo"] ,$respuesta["datos"]["firmaRecibo"]);
    	$conn_pdo->commit();	// Si no hubo problemas guardar la configuración
    	$respuesta["mensaje"] 	= "Se actualizo correctamente la configuración de variables";
    	$respuesta["success"] 	= true;
	} catch (Exception $e) {
		$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
		$conn_pdo->rollBack();
	}
}
// ____________________________________
function ConfiguracionIngresa($vId,$vDescripcion,$vValor){
	global $conn_pdo,$usrClave;
	$vId  		  = pg_escape_string($vId);
	$vDescripcion = pg_escape_string($vDescripcion); 
	$vValor		  = pg_escape_string($vValor);
	$sql 		  = "select idconfiguracion as salida from configuracion where idconfiguracion='$vId'";

	$res = getcampo($sql);
	if ($res!=""){ 	// Existe se hace update
	    $sql  = "UPDATE configuracion SET  descripcion=:descripcion, valor=:valor where idconfiguracion = :idconfiguracion " ;
	    $stmt = $conn_pdo->prepare($sql); // prepara el SQL
	}else{			// No existe se requiere adicionarlo
		$sql  =	"INSERT INTO configuracion(idconfiguracion, descripcion, valor, usuarioalta) " .
				"VALUES (:idconfiguracion, :descripcion, :valor, :usuarioalta)";
		$stmt = $conn_pdo->prepare($sql); // prepara el SQL

		$stmt->bindParam(':usuarioalta'	, $usrClave	, PDO::PARAM_STR);
    
	}
	$stmt->bindParam(':idconfiguracion'	, $vId			, PDO::PARAM_STR);
    $stmt->bindParam(':descripcion'		, $vDescripcion	, PDO::PARAM_STR);
    $stmt->bindParam(':valor'			, $vValor		, PDO::PARAM_STR);
    $respuesta["depura"] = $stmt->execute();
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * *
// * Funciones relacionadas a OpeFin01_04Accesos.php"  *
// * * * * * * * * * * * * * * * * * * * * * * * * * * */
// ____________________________________
function AccesosConsulta(&$respuesta){
	$sql =  "select a.idcuentabancaria , b.nombre , a.idusuario , a.usuarioalta, a.fechaalta " .
			" from accesos a , cuentasbancarias b where a.idcuentabancaria=b.idcuentabancaria ";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$respuesta["resultados"] = $res;	// Para llenar la tabla HTML
		$respuesta["success"]	 = true;
		$respuesta["mensaje"]	 = ""; 		// En la carga inicial o al refrecar tabla no se debe mandar mensaje
		return;
	}else{
		$respuesta["mensaje"] = "No hay aún accesos dados de alta en el Sistema";
	}
	return;
}
// ____________________________________
function AccesosCatalogos(&$respuesta){
	// Se recuperan los esquemas que esten activos
	$sql = "select idcuentabancaria,nombre from cuentasbancarias where estatus order by nombre";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$respuesta["combo"][] = " ,Seleccione";
		foreach ($res as $r ){
			$respuesta["combo"][] = $r["idcuentabancaria"].",".$r["nombre"];
		}
		$sql = "select idusuario from usuarios where estatus order by nombre";
		$res = ejecutaSQL_($sql);
		if ( $res!=null){
			$respuesta["combo1"][] = " ,Seleccione";
			foreach ($res as $r ){
				$respuesta["combo1"][] = $r["idusuario"].",".$r["idusuario"];
			}
			$respuesta["success"]	 = true;
		}
	}
}
// ____________________________________
function AccesosAgrega(&$respuesta){
	global $conn_pdo , $usrClave;	// Variable global que tiene la conexión a la Base de Datos
	$oAcceso = new accesos($respuesta["datos"],$usrClave); // Crea el Objeto 

	if ( !( $oAcceso->ExisteAcceso($conn_pdo,$respuesta,"Adicionar") ) ){
		try {
			$conn_pdo->beginTransaction();
			$oAcceso->adicionaAcceso($conn_pdo,"Adicionar",$respuesta);
			if ($respuesta["depura"]){
				$idCtaBan = $respuesta["datos"]["idCuentaBancaria"]; 
				$bita     = bitacora($conn_pdo, $usrClave,$idCtaBan,"Admin/Acceso/Agregar","Cta-Usu: $idCtaBan-$usrClave",0.00 );
			    $conn_pdo->commit();
		    	AccesosConsulta($respuesta); // Actualiza para refrescar la tabla HTML
		    	$respuesta["mensaje"] 	= "Se dio de alta el acceso solicitado";
		    	$respuesta["success"] 	= true;	
		    }else{
		    	$conn_pdo->rollBack();
		    	$respuesta["mensaje"] 	= "** NO se dio de alta el acceso solicitado **";
		    }
		} catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}
	unset($oCuentaBancaria);// Se destruye el Objeto
}
// ____________________________________
function AccesosBusca(&$respuesta){
	global $conn_pdo, $usrClave;

	$oAcceso = new accesos($respuesta["datos"],$usrClave); // Crea el 
	if ( $oAcceso->buscaDatos($conn_pdo,$respuesta) ){
		$respuesta["success"] = true;
	}else{
		$respuesta["mensaje"] = "No se encontró información de la Cuenta Bancaria o Usuario solicitada";
	}
}
// ____________________________________
function AccesosElimina(&$respuesta){
	global $conn_pdo,$usrClave;	// Variable global que tiene la conexión a la Base de Datos
	$oAcceso = new accesos($respuesta["datos"],$usrClave); // Crea el  // Crea el Objeto 
	if ( ( $oAcceso->ExisteAcceso($conn_pdo,$respuesta,"Eliminar") ) ){
		try {
			$conn_pdo->beginTransaction();
			if ( $oAcceso->eliminaAcceso($conn_pdo,"Eliminar",$respuesta)){
				$idCtaBan = $respuesta["datos"]["idCuentaBancaria"]; 
				$bita     = bitacora($conn_pdo, $usrClave,$idCtaBan,"Admin/Acceso/Eliminar","Cta-Usu: $idCtaBan-$usrClave",0.00 );
		    	$conn_pdo->commit();
	    		AccesosConsulta($respuesta); // Actualiza para refrescar la tabla HTML
	    		$respuesta["mensaje"] 	= "Se eliminó correctamente el acceso solicitado";
	    		$respuesta["success"] 	= true;	
	    		$respuesta["bita"]		= $bita; // True si logró adicionar acción realizada en la bitácora
	    	}else{
	    		$respuesta["mensaje"] 	= "No fue posible eliminar la Cuenta Bancaria. Esta enlazada con accesos de usuario";
	    	}
		} catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}
	unset($oCuentaBancaria);// Se destruye el Objeto
}
// ____________________________________
// ____________________________________
// ____________________________________
?>