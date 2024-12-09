<?php
/*
* * * * * * * * * * * * * * * * * * * * * * * * * 
* Autor   : Miguel Ángel Bolaños Guillén        *
* Sistema : Sistema de Operación Bancaria Web   *
* Fecha   : Septiembre 2023                     *
* Descripción : Rutinas para ejecutar codigo    * 
*               SQL para interacturar con las   *
*               tablas de la BD del Sistema     *
*               Unadm-Proyecto Terminal         *
* * * * * * * * * * * * * * * * * * * * * * * * *  */
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	//
	session_start(); // variables de sesión
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php");exit; return;
	}
	// _______________________________________
	//
	$idUsuario     	= $_SESSION['OpeFinClave'];
	$esquemaUsuario = $_SESSION['OpeFinEsquema'];
	//
	include_once("con_pg_OpeFinW_.php"); 	// Se incluye conexión a la Base de Datos
	include_once("rutinas_.php");						// Rutinas de uso general
	include_once("Pagina_y_Busca_.php");

	// inicializa arreglo que se regresara a JavaScript y que se podra visualizar en el depurador del navegador (activar con F12)
	$respuesta = array(	'success'=>false, 'mensaje'=>array(), 'resultados'=>array(), 'opcion'=>array() , 
						'datos'=>array() , 'combo'=>array(), 'depura'=>array() , 'sesion'=>array() , "objeto"=>array());
	// Se gauardan variables de sesion
	$respuesta["sesion"] = array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);

	// Recupera los parámetros enviados por JS, 
	$aDatos				 = json_decode( ($_REQUEST['aDatos']),true);	// Debe ser con _REQUEST , con POST no funciona
	$vOpc 				 = $aDatos["opcion"];  // Opción que el JS quiere que se ejecute en este php
	$respuesta["opcion"] = $vOpc;			   // Se guardan para efectos de depuración
	$respuesta["datos"]  = $aDatos;

	switch ($vOpc) {
// 		--------------------------------------------
		case 'CargaCuentasBancarias': 			// Se regresara el select de la tabla cuentasbancarias
			CargaCuentasBancarias($respuesta);
		break;
//		____________________________
		
//		____________________________		
		case "AgregaCuentaBancaria":
			AgregaCuentaBancaria($respuesta);
		break;
//		____________________________		
		case "ModificaCuentaBancaria":
			ModificaCuentaBancaria($respuesta);
		break;
//		____________________________ 		
		case "EliminaCuentaBancaria":
			EliminaCuentaBancaria($respuesta);
		break;
// 		--------------------------------------------
		case "ConsultaOperacionesBancarias":
			ConsultaOperacionesBancarias($respuesta);
		break;
//		--------------------------------------------
		case "AgregaOperacion":
			AgregaOperacion($respuesta);
		break;
//		---------------------------------------------
		case "ModificaOperacion":
			ModificaOperacion($respuesta);
		break;				
//		---------------------------------------------
		case "EliminaOperacion":
			EliminaOperacion($respuesta);
		break;	
//		_____________________________________________
		case "ConsultaControlesBancarios":
			//ConsultaControlesBancarios($respuesta);
			$respuesta["success"] = BuscaYPagina($respuesta["datos"]);
			$respuesta["mensaje"] = "";
			if($respuesta["datos"]["traeOperaciones"]){ // Para llenar el Combo de Operaciones
				ControlesCatalogos($respuesta);
			}
		break;
//		_____________________________________________
		case "AgregaControl":
			AgregaControl($respuesta);
		break;
//		_____________________________________________
		case "ModificaControl":
			ModificaControl($respuesta);
		break;
//		_____________________________________________
		case "EliminaControl":
			EliminaControl($respuesta);
		break;
//		_____________________________________________
		case 'ConsultaUnidadesResponsables':
			ConsultaUnidadesResponsables($respuesta);
		break;
//		_____________________________________________
//		_____________________________________________
		default:
			$respuesta["mensaje"] = "No esta definida en Catalogos_.php [" . $vOpc . "]";
		break;
	}

	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
return;
/*
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                           *
* Funciones relacionadas a OpeFin02_01CuentasBancarias_.php *
*                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
// _________________________________________________
function CargaCuentasBancarias(&$respuesta){

	$sql = "select idcuentabancaria, nombre, siglas, estatus, consecutivo, usuarioalta, fechaalta ".
		   " from cuentasbancarias order by idcuentabancaria";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$respuesta["resultados"] = $res;
		$respuesta["success"]	 = true;
		$respuesta["mensaje"]	 = "";
	}else{
		$respuesta["mensaje"] = "No hay aún Cuentas Bancarias en el Sistema";
	}
}
// _________________________________________________

// _________________________________________________
function AgregaCuentaBancaria(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/cuentaBancaria_.php");
	$oCuentaBancaria = new cuentaBancaria($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 

	if ( !( $oCuentaBancaria->ExisteCuentaBancaria($conn_pdo,$respuesta,"Adicionar") ) ){
		try {
			$conn_pdo->beginTransaction();
			$oCuentaBancaria->guardar($conn_pdo,"Adicionar",$respuesta);
		    $conn_pdo->commit();
	    	CargaCuentasBancarias($respuesta); // Actualiza para refrescar la tabla HTML
	    	$respuesta["mensaje"] 	= "Se dio de alta la Cuenta Bancaria solicitada";
	    	$respuesta["success"] 	= true;	
		} catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}

	unset($oCuentaBancaria);// Se destruye el Objeto

}
// _________________________________________________
function ModificaCuentaBancaria(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/cuentaBancaria_.php");
	$oCuentaBancaria = new cuentaBancaria($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 

	if ( ( $oCuentaBancaria->ExisteCuentaBancaria($conn_pdo,$respuesta,"Modificar") ) ){
		try {
			$conn_pdo->beginTransaction();
			$oCuentaBancaria->guardar($conn_pdo,"Modificar",$respuesta);
		    $conn_pdo->commit();
	    	CargaCuentasBancarias($respuesta); // Actualiza para refrescar la tabla HTML
	    	$respuesta["mensaje"] 	= "Se actualizó correctamente la Cuenta Bancaria solicitada";
	    	$respuesta["success"] 	= true;	
		} catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}
	unset($oCuentaBancaria);// Se destruye el Objeto
}
// _________________________________________________
function EliminaCuentaBancaria(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/cuentaBancaria_.php");

	$oCuentaBancaria = new cuentaBancaria($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 
	if ( ( $oCuentaBancaria->ExisteCuentaBancaria($conn_pdo,$respuesta,"Eliminar") ) ){
		if ( $oCuentaBancaria->notieneAccesos($respuesta)){
			if ($oCuentaBancaria->noTieneMovimientosBancarios()){
				try {
					$conn_pdo->beginTransaction();
					if ( $oCuentaBancaria->EliminarCuentaBancaria($conn_pdo,"Eliminar",$respuesta)){
						$conn_pdo->commit();
						CargaCuentasBancarias($respuesta); // Actualiza para refrescar la tabla HTML
						$respuesta["mensaje"] 	= "Se eliminó correctamente la Cuenta Bancaria solicitada";
						$respuesta["success"] 	= true;	
					}else{
						$respuesta["mensaje"] 	= "No fue posible eliminar la Cuenta Bancaria.";
						$conn_pdo->rollBack();
					}
				} catch (Exception $e) {
					$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
					$conn_pdo->rollBack();
				}
			}else{
				$respuesta["mensaje"] = "No procede, la cuenta bancaria tiene movimientos";
				$respuesta["success"] 	= false;
			}
		}else{
			$respuesta["mensaje"] = "No procede, la cuenta bancaria tiene accesos asignados";
			$respuesta["success"] 	= false;
		}
	}else{
		// El mensaje lo coloca ExisteCuentabancaria
	}
	unset($oCuentaBancaria);// Se destruye el Objeto
}
// _________________________________________________
/*
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                               *
* Funciones relacionadas a OpeFin02_02OperacionesBancarias_.php *
*                                                               *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  * */
// _________________________________________________
function ConsultaOperacionesBancarias(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/operaciones_.php");
	$oOperacion = new operacionBancaria(null,null);
	$condicion  = "";
	if ( $oOperacion->consultaCatalogo($conn_pdo,$respuesta,$condicion) ){
		$respuesta["success"]	 = true;
		$respuesta["mensaje"]	 = "";
	}else{
		$respuesta["mensaje"] = "No hay Operaciones Bancarias en el Catálogo de Operaciones";
	}
}
// _________________________________________________
function AgregaOperacion(&$respuesta){

	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/operaciones_.php");
	// Al no poser pasar desde JS el caracater + o - Se codifico a S y R
	$respuesta["datos"]["operador"] = decodifica($respuesta["datos"]["operador"]);

	$oOperacionBancaria = new operacionBancaria($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 
	if ( !( $oOperacionBancaria->ExisteOperacion($conn_pdo,$respuesta,"Adicionar") ) ){
		try {
			$conn_pdo->beginTransaction();
			$oOperacionBancaria->adicionaOperacion($conn_pdo,"Adicionar",$respuesta);
			if ($respuesta["depura"]){
				$bita     = bitacora($conn_pdo, $respuesta["sesion"]["idUsuario"],"","Cata/Operacion/Adicionar","idOperacion: ".
									 json_encode($respuesta['datos']),0.00 );
			    $conn_pdo->commit();
		    	ConsultaOperacionesBancarias($respuesta); // Actualiza para refrescar la tabla HTML
		    	$respuesta["mensaje"] 	= "Se dio de alta la Operación Bancaria solicitada";
		    	$respuesta["success"] 	= true;	
		    }else{
		    	$conn_pdo->rollBack();
		    	$respuesta["mensaje"] 	= "La base de datos rechazo agregar la operación";
		    }
		} catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}

	unset($oOperacionBancaria);// Se destruye el Objeto

}
// _________________________________________________
function ModificaOperacion(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/operaciones_.php");
	// Al no poser pasar desde JS el caracater + o - Se codifico a S y R
	$respuesta["datos"]["operador"] = decodifica($respuesta["datos"]["operador"]);

	$oOperacionBancaria = new operacionBancaria($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 
	if ( ( $oOperacionBancaria->ExisteOperacion($conn_pdo,$respuesta,"Modificar") ) ){
		try {
			$conn_pdo->beginTransaction();
			$oOperacionBancaria->adicionaOperacion($conn_pdo,"Modificar",$respuesta);
			if ($respuesta["depura"]){
				$bita     = bitacora($conn_pdo, $respuesta["sesion"]["idUsuario"],"","Cata/Operacion/Modifica","idOperacion: ".
									 json_encode($respuesta['datos']),0.00 );
			    $conn_pdo->commit();
		    	ConsultaOperacionesBancarias($respuesta); // Actualiza para refrescar la tabla HTML
		    	$respuesta["mensaje"] 	= "Se actualizó correctamente la Operación Bancaria solicitada";
		    	$respuesta["success"] 	= true;	
		    }else{
		    	$conn_pdo->rollBack();
		    	$respuesta["mensaje"] 	= "Los datos para modificar la operación son incorrectos";
		    }
		} catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}

	unset($oOperacionBancaria);// Se destruye el Objeto
}
// _________________________________________________
function EliminaOperacion(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/operaciones_.php");
	try{
		$oOperacionBancaria = new operacionBancaria($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 
		if ( ( $oOperacionBancaria->ExisteOperacion($conn_pdo,$respuesta,"Eliminar") ) ){
			if ($oOperacionBancaria->noTieneControles() ){
				$conn_pdo->beginTransaction();
				if ( $oOperacionBancaria->eliminaOperacion($conn_pdo,"Eliminar",$respuesta)){
					bitacora($conn_pdo, $respuesta["sesion"]["idUsuario"],"","Cata/Operacion/Eliminar","idOperacion: ".
										 json_encode($respuesta['datos']),0.00 );
			    	$conn_pdo->commit();
		    		ConsultaOperacionesBancarias($respuesta); // Actualiza para refrescar la tabla HTML
		    		$respuesta["mensaje"] 	= "Se eliminó correctamente la Operación Bancaria solicitada";
		    		$respuesta["success"] 	= true;	
		    	}else{
		    		$respuesta["mensaje"] 	= "No fue posible eliminar la Operación Bancaria. Esta enlazada con otra información";
		    	}
		    }else{
		    	$respuesta["mensaje"] = "No procede, la operación tiene asignado controles bancarios";
		    }
		}
		unset($oOperacionBancaria);// Se destruye el Objeto
	} catch (Exception $e) {
		$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "] en EliminaOperacion";
		$conn_pdo->rollBack();
	}
}
// _________________________________________________
function decodifica($cOperador){
	switch($cOperador){
		case "S":
			return "+";
		break;
		case "R":
			return "-";
		break;
		default:
			return $cOperador;
		break;
	}
}
/*
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                               *
* Funciones relacionadas a OpeFin02_03ControlesBancarios_.php   *
*                                                               *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
// _________________________________________________
function ConsultaControlesBancarios(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/controles_.php");
	$oOperacion = new controlBancario(null,null);
	$condicion  = "";
	if ( $oOperacion->consultaCatalogo($conn_pdo,$respuesta,$condicion) ){
		$respuesta["success"]	 = true;
		$respuesta["mensaje"]	 = "";
	}else{
		$respuesta["mensaje"] = "No hay aún Controles Bancarios en el Catálogo de Controles";
	}
}
// _________________________________________________
function ControlesCatalogos(&$respuesta){
	// Se requiere llenar un combo con el catálogo de Operaciones Bancarias, en la pantalla de Controles Bancarios
	$sql = "select idoperacion,nombre from operacionesbancarias where idopercan is not null order by tipo desc,idoperacion";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$respuesta["combo"][] = " ,Seleccione"; // Valor nulo
		foreach ($res as $r ){	// llena el combo con la clave y nombre de la operación bancaria
			$respuesta["combo"][] = $r["idoperacion"].",".$r["idoperacion"]." - ".$r["nombre"];
		}
	}
}
// _________________________________________________
function AgregaControl(&$respuesta){

	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/controles_.php");
	try {
		$oControlBancario = new controlBancario($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 
		if ( !( $oControlBancario->ExisteControl($conn_pdo,$respuesta,"Adicionar") ) ){
			$conn_pdo->beginTransaction();
			$oControlBancario->adicionaControl($conn_pdo,"Adicionar",$respuesta);
			if ($respuesta["depura"]){
				$bita     = bitacora($conn_pdo, $respuesta["sesion"]["idUsuario"],"","Cata/Control-Operación/Adicionar","Ctrl-Ope: ".
									 json_encode($respuesta['datos']),0.00 );
			    $conn_pdo->commit();
		    	ConsultaControlesBancarios($respuesta); // Actualiza para refrescar la tabla HTML
		    	$respuesta["mensaje"] 	= "Se dio de alta el Control-Operación Solicitado";
		    	$respuesta["success"] 	= true;	
		    }else{
		    	$conn_pdo->rollBack();
		    	$respuesta["mensaje"] 	= "La base de datos rechazo agregar el control-operación";
		    }
		}
	} catch (Exception $e) {
		$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
		$conn_pdo->rollBack();
	}
	unset($oControlBancario);// Se destruye el Objeto

}
// _________________________________________________
function ModificaControl(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/controles_.php");

	try {

		$oControlBancario = new controlBancario($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 
		if ( ( $oControlBancario->ExisteControl($conn_pdo,$respuesta,"Modificar") ) ){

			$conn_pdo->beginTransaction();
			$oControlBancario->adicionaControl($conn_pdo,"Modificar",$respuesta);
			if ($respuesta["depura"]){
				$bita     = bitacora($conn_pdo, $respuesta["sesion"]["idUsuario"],"","Cata/Control-Operación/Modifica","Ctrl-Ope: ".
									 json_encode($respuesta['datos']),0.00 );
			    $conn_pdo->commit();
		    	ConsultaControlesBancarios($respuesta); // Actualiza para refrescar la tabla HTML
		    	$respuesta["mensaje"] 	= "Se actualizó correctamente el Control-Operación Solicitado";
		    	$respuesta["success"] 	= true;	
		    }else{
		    	$conn_pdo->rollBack();
		    	$respuesta["mensaje"] 	= "Los datos para modificar el Contol-Operación son incorrectos";
		    }
		}
	} catch (Exception $e) {
		$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
		$conn_pdo->rollBack();
	}
	unset($oControlBancario);// Se destruye el Objeto
}
// _________________________________________________
function EliminaControl(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	require_once("../pdoF/controles_.php");
	try{
		$oControlBancario = new controlBancario($respuesta["datos"],$respuesta["sesion"]["idUsuario"]); // Crea el Objeto 
		if ( ( $oControlBancario->ExisteControl($conn_pdo,$respuesta,"Eliminar") ) ){
			if ( $oControlBancario->noTieneMovimientos()){
				$conn_pdo->beginTransaction();
				if ( $oControlBancario->eliminaControl($conn_pdo,"Eliminar",$respuesta)){
					bitacora($conn_pdo, $respuesta["sesion"]["idUsuario"],"","Cata/Control-Operación/Eliminar","Ctrl-Ope: ".
										 json_encode($respuesta['datos']),0.00 );
			    	$conn_pdo->commit();
		    		ConsultaControlesBancarios($respuesta); // Actualiza para refrescar la tabla HTML
		    		$respuesta["mensaje"] 	= "Se eliminó correctamente el Control-Operación solicitado";
		    		$respuesta["success"] 	= true;	
		    	}else{
		    		$respuesta["mensaje"] 	= "No fue posible eliminar el Control-Operación. Esta enlazada con otra información";
		    	}
	    	}else{
	    		$respuesta["mensaje"] = "No procede, la combinación operación-control tiene movimientos asignados";
	    	}
		}
		unset($oControlBancario);// Se destruye el Objeto
	} catch (Exception $e) {
		$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "] en EliminaControl";
		$conn_pdo->rollBack();
	}
}
/*
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                               *
* Funciones relacionadas a OpeFin02_03ControlesBancarios_.php   *
*                                                               *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
// _________________________________________________
function ConsultaUnidadesResponsables(&$respuesta){
	$sql = "select idunidad, nombreunidad, estatus, usuarioalta, fechaalta from unidades order by idunidad";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$respuesta["resultados"] = $res;
		$respuesta["success"]	 = true;
		$respuesta["mensaje"]	 = "";
	}else{
		$respuesta["mensaje"] = "No hay aún Unidades Responsables en el Sistema";
	}
}
// _______________________________________________________________________
// _______________________________________________________________________
// _______________________________________________________________________