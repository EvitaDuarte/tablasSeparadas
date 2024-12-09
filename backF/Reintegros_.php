<?php
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	//
	include_once("con_pg_OpeFinW_.php"); 		// Se incluye conexión a la Base de Datos
	include_once("rutinas_.php");				// Rutinas de uso general
	require_once "../pdoF/metodos_.php";			// Métodos estáticos
	require_once "../pdoF/ClaseReintegros_.php";

	include_once("FiltraYPagina_.php");

	date_default_timezone_set('America/Mexico_City');
	//
	session_start(); // variables de sesión  
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php"); // Le dice al navegador que abra esta página
		exit; // Detiene la ejecución de este script
	}
	// _______________________________________
	set_error_handler('customErrorHandler');
	// _______________________________________
	$idUsuario     	= $_SESSION['OpeFinClave'];
	$esquemaUsuario = $_SESSION['OpeFinEsquema'];
	//
	$respuesta = array(	'success'=>false , 'mensaje'=>""	 , 'resultados'=>array(), 'opcion'=>array() , 'unidades'=>array(),
						'datos'=>array() , 'tipoMov'=>""	 , 'origenes'=>array()  , 'cuentas'=>array() );

	// Lee el cuerpo de la solicitud HTTP
	$jsonData = file_get_contents('php://input');
	// Decodifica los datos JSON en un array asociativo
	$aParametros 		 		= json_decode($jsonData, true);
	$aParametros["usuarioalta"] = $idUsuario;
	$vOpc 				 		= $aParametros["opcion"];  					// Opción que el JS quiere que se ejecute en este php
	$respuesta["opcion"] 		= $aParametros;			   					// Se guardan para efectos de depuración
	$respuesta["datos"]  		= array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);
	//
	$respuesta["datos"]["opcion"] = $vOpc;

	switch ($vOpc) {
	//	_____________________________________________________________________________________________
		case 'CargaCatalogos': 			// Se regresara el select de la tabla cuentasbancarias
			CargaCatalogos($respuesta);
		break;

	//	_____________________________________________________________________________________________
		case 'FiltraMovimientosReintegros':
			$respuesta["success"] = FiltraYPagina($respuesta["opcion"]);
			$respuesta["mensaje"] = "";
		break;		
	//	_____________________________________________________________________________________________
		case 'AgregaReintegro':
			Agrega_Reintegro($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case 'ModificaReintegro':
			Modifica_Reintegro($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "EliminarReintegro":
			$respuesta["success"] = Eliminar_Reintegro($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "ReporteReintegros":
			$respuesta["success"] = Reporte_Reintegros($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "cargaLayout":
			$respuesta["success"] = Carga_Layout($respuesta);
		break;
	//	_____________________________________________________________________________________________
		default:
			$respuesta["mensaje"] = "No esta definida en Reintegros_.php [" . $vOpc . "]";
		break;
	}
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);
// ______________________________________________________________________________________________________
// ______________________________________________________________________________________________________
// ______________________________________________________________________________________________________
// ______________________________________________________________________________________________________
// ______________________________________________________________________________________________________
// ______________________________________________________________________________________________________
// ______________________________________________________________________________________________________
// ______________________________________________________________________________________________________
// ______________________________________________________________________________________________________
function cargaCatalogos(&$res){
	global $conn_pdo;
	$cRegreso				= null;
	$oRein					= new Reintegros( $conn_pdo);
	$res["opcion"]["hoy"]	= date("Y-m-d");

	if ( $oRein->traeUnidades($cRegreso) ){
		$res["unidades"] = $cRegreso;
		if ( $oRein->traeOrigenes($cRegreso)){
			$res["origenes"]	= $cRegreso;
			if ( $oRein->traeCuentas($cRegreso) ){
				$res["cuentas"]	= $cRegreso;
				$res["success"]	= true;
			}
		}else{
			$res["mensaje"] = $cRegreso;
		}
	}else{
		$res["mensaje"] = $cRegreso;
	}
}
// ______________________________________________________________________________________________________
function Agrega_Reintegro(&$respuesta){
	global $conn_pdo;
	$cRegreso	= null;
	$oRein		= new Reintegros( $conn_pdo);
	$lAlta		= true;
	$oRein->cargaDatos($respuesta["opcion"]);
	if ( $oRein->LlaveDuplicada($cRegreso) ){
		$respuesta["mensaje"] = $cRegreso;
		return false;
	}else{
		
		try{
			$conn_pdo->beginTransaction();
			$resultado 	= $oRein->actualizaReintegro($lAlta);
			if ($resultado==true){
				$respuesta["mensaje"] = "Se adicionó el reintegro ...";
				$respuesta["success"] = true;
				$conn_pdo->commit();
				return true;
			}else{
				$respuesta["mensaje"] = "No se logro actualizar el movimiento de reintegro";
				$conn_pdo->rollBack();
				return false;
			}
		} catch (Exception $e) {
			$respuesta["mensaje"] = "a)Excepción en la base de datos <" . $e->getMessage() . ">" . $e->getLine();
			$conn_pdo->rollBack();
			return false;
		}


	}

}
// ______________________________________________________________________________________________________
function Modifica_Reintegro(&$respuesta){
	global $conn_pdo;
	$cRegreso	= null;
	$oRein		= new Reintegros( $conn_pdo);
	$lAlta		= false;
	$oRein->cargaDatos($respuesta["opcion"]);

	try{
		$conn_pdo->beginTransaction();
		$resultado 	= $oRein->actualizaReintegro($lAlta);
		if ($resultado==true){
			$respuesta["mensaje"] = "Se modificó el reintegro ...";
			$respuesta["success"] = true;
			$conn_pdo->commit();
			return true;
		}else{
			$respuesta["mensaje"] = "No se logro actualizar el movimiento de reintegro";
			$conn_pdo->rollBack();
			return false;
		}
	} catch (Exception $e) {
		$respuesta["mensaje"] = "b)Excepción en la base de datos <" . $e->getMessage() . ">" . $e->getLine();
		$conn_pdo->rollBack();
		return false;
	}

}
// ______________________________________________________________________________________________________
function Eliminar_Reintegro(&$respuesta){
	global $conn_pdo;
	$oMovReint  = new Reintegros($conn_pdo);
	$cIdRei		= $respuesta["opcion"]["idRei"];
	$cRegreso	= "";

	try{
		$conn_pdo->beginTransaction();
		$resultado 	= $oMovReint->eliminaReintegro($cIdRei,$cRegreso);
		if ($resultado==true){
			$respuesta["mensaje"] = $cRegreso;
			$conn_pdo->commit();
			return true;
		}else{
			$respuesta["mensaje"] = $cRegreso;
			$conn_pdo->rollBack();
			return false;
		}
	} catch (Exception $e) {
		$respuesta["mensaje"] = "c)Excepción en la base de datos (" . $e->getMessage() . ") " . $e->getLine();
		$conn_pdo->rollBack();
		return false;
	}

}
// ______________________________________________________________________________________________________
function Reporte_Reintegros(&$respuesta){
	global $conn_pdo;
	$oMovReint  = new Reintegros($conn_pdo);
	$cFecIni	= $respuesta["opcion"]["fecIni"];
	$cFecFin	= $respuesta["opcion"]["fecFin"];
	$cAnioRi	= $respuesta["opcion"]["anioRi"];
	$cAnioRf	= $respuesta["opcion"]["anioRf"];

	$resultado  = $oMovReint->traeNombreUrs($cRegreso);
	if ($resultado==true){
		$respuesta["unidades"] = $cRegreso;
		$resultado  = $oMovReint->ConsultaReintegros($cFecIni,$cFecFin,$cAnioRi,$cAnioRf,$cRegreso);
		if ($resultado==true){
			$respuesta["resultados"] = $cRegreso;
			require_once("repo/ReintegrosReporte_.php");
			ReintegrosReporte($respuesta);
			return true; // Actualizar success
		}else{
			$respuesta["mensaje"] = "No se encontró información para las fechas solicitadas [$cFecIni-$cFecFin]";
			return false;
		}
	}
}
// ______________________________________________________________________________________________________
function Carga_Layout(&$respuesta){
	global $conn_pdo;
	$oMovReint  = new Reintegros($conn_pdo);
	$reintegros = $respuesta["opcion"]["reintegros"];
	$lAlta		= true;
	$nDuplicados= 0;
	$nAltas		= 0;
	$nErroneos	= 0;
	$nTotal		= 0;
	$cUsuario	= $respuesta["datos"]["idUsuario"];
	$cMensaje	= "";
	try{
		$conn_pdo->beginTransaction();
		foreach ($reintegros as $r) {

			$r["usuarioalta"] = $cUsuario;
			$oMovReint->cargaDatos($r);

			if ( $oMovReint->LlaveDuplicada($cRegreso)==false ){
				if ($oMovReint->actualizaReintegro($lAlta)){
					$nAltas +=1;
				}else{
					$nErroneos +=1;
				}
			}else{
				$nDuplicados +=1;
			}
			$nTotal +=1;
		}
		if ( $nAltas > 0){
			$cMensaje = "Se dieron de alta " . $nAltas . " reintegros. ";
		}
		if ( $nErroneos >0 ){
			$cMensaje = $cMensaje . "Hubo " . $nErroneos . " erróneos. ";
		}
		if ( $nDuplicados > 0){
			$cMensaje = $cMensaje . "Se detectaron " . $nDuplicados . " duplicados. ";
		}
		$cMensaje = $cMensaje . "De un total de " . $nTotal . " reintegros";
		$conn_pdo->commit();
		$respuesta["mensaje"] = $cMensaje;
		return true;
	} catch (Exception $e) {
		$respuesta["mensaje"] = "d)Excepción en la base de datos (" . $e->getMessage() . ") " . $e->getLine();
		$conn_pdo->rollBack();
		return false;
	}
	
}	
// ______________________________________________________________________________________________________


?>	