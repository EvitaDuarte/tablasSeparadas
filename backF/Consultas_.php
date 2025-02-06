<?php
/*
* * * * * * * * * * * * * * * * * * * * * * * * * 
* Autor   : Miguel Ángel Bolaños Guillén        *
* Sistema : Sistema de Operación Bancaria Web   *
* Fecha   : Diciembre  2023                     *
* Descripción : Rutinas para ejecutar codigo    * 
*               SQL para interacturar con los   *
*               Saldos y movimientos de la BD   *
*               del Sistema.                    *
*               Unadm-Proyecto Terminal         *
* * * * * * * * * * * * * * * * * * * * * * * * *  */
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	// Desactivar la visualización de errores
	//ini_set('display_errors', '0');
	// _________________________________________
	require_once "../pdoF/cuentaBancaria_.php";
	require_once "../pdoF/Saldos_.php";
	require_once "../pdoF/metodos_.php";	
	require_once "../pdoF/CsvGenera_.php";
	include_once("Pagina_y_Busca_.php");
	include_once("./repo/Movimientos_.php");
	// _________________________________________
	date_default_timezone_set('America/Mexico_City');
	session_start(); // variables de sesión  
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php");exit; return;
	}
	// _______________________________________
	$idUsuario     	= $_SESSION['OpeFinClave'];
	$esquemaUsuario = $_SESSION['OpeFinEsquema'];
	$ip 			= $_SERVER['REMOTE_ADDR'];
	$v_Alias        = $_SESSION['alias'];
	// _________________________________________
	$respuesta = array(	'success'=>false , 'mensaje'=>""	 , 'resultados'=>array(), 'opcion'=>array() , 'ctas'=>array(),
						'urs'=>array()   , 'opera'=>array()  , 'ctrl'=>array()      , 'datos'=>array()  , 'tipoMov'=>"",
						'_trace'=>""	 , 'alias'=>$v_Alias);
	// Lee el cuerpo de la solicitud HTTP
	$jsonData = file_get_contents('php://input');
	// Decodifica los datos JSON en un array asociativo
	$aParametros 		 	= json_decode($jsonData, true);
	$vOpc 				 	= $aParametros["opcion"]; 
	$aParametros["pdf"]		= "R_" . str_replace(".", "", $ip) . ".pdf";
	$aParametros["csv"]		= "R_" . str_replace(".", "", $ip) . ".csv";
	$respuesta["opcion"] 	= $aParametros; // Debe de ir para que se identifique en el regreso del PHP al JS
	$respuesta["datos"]  	= array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);
	// ___________________________________________

	// ___________________________________________
	switch ($vOpc) {
//	_____________________________________________________________________________________________
		case 'CargaCuentasBancarias': 			// Se regresara el select de la tabla cuentasbancarias
		case 'CargaCuentasBancarias0':
		case 'CargaCuentasBancarias1':
		case 'CargaCuentasBancarias2':
			CargaCuentasBancarias($respuesta);
		break;
		// _____________________________________________________________
		case 'ConsultaSaldosBancarios':
			$respuesta["ctrl"]    = $respuesta["opcion"]["aCampos"];
			$respuesta["success"] = BuscaYPagina($respuesta["opcion"]);
			$respuesta["mensaje"] = "";
		break;
		// _____________________________________________________________
		case 'BuscaMovimientosBancarios':
			$respuesta["ctrl"]    = $respuesta["opcion"]["aCampos"];
			if ($respuesta["opcion"]["salida"]==="Pantalla"){
				procesaCtas($respuesta);
				$respuesta["success"] = BuscaYPagina($respuesta["opcion"]);
				$respuesta["mensaje"] = ""; 
			}elseif ($respuesta["opcion"]["salida"]==="Csv"){
				$respuesta["success"] = EnviaCsv($respuesta);
			}elseif($respuesta["opcion"]["salida"]==="Pdf"){
				$respuesta["success"] = EnviaPdf($respuesta);
			}
	
		break;
		// _____________________________________________________________
		case "FechaHoy":
			$respuesta["success"] 		= true;
			$respuesta["resultados"]	= array("fRep"=>date("d/m/Y"),"Hoy"=>date("Y-m-d"));
		break;
		// _____________________________________________________________
		default:
			$respuesta["mensaje"] = "No esta definida en Consultas_.php [" . $vOpc . "]";
		break;
	}
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
return;
// ****************************************************************************************
function CargaCuentasBancarias(&$respuesta){
	metodos::traeCuentasBancarias($respuesta);
}	
// ________________________________________________________________________________________
function procesaCtas(&$r){
	$cCtaI = $r["opcion"]["cuentaI"]; $cCtaF = $r["opcion"]["cuentaF"];
	$aCtas = arregloCtasBancarias($cCtaI,$cCtaF);

	try {
		creaTablaTemporal($r);
		foreach($aCtas as $cta){
			$cCta = $cta["idcuentabancaria"];
			buscaMovsCta($cCta,$r);
		}
		$r["opcion"]["tabla"]		= "temp.t_" . $r["alias"] . " a ";
		$r["opcion"]["tablaPrin"]	= "temp.t_" . $r["alias"] . " "  ; // Al parecer esta no se utiliza en Pagina_y_Busca

		
	} catch (Exception $e) {
		$r["mensaje"] = $e->getMessage();
		return false;
	}
}
// ________________________________________________________________________________________
// ________________________________________________________________________________________
function creaTablaTemporal($r){
	$alias	= "temp.t_" . $r["alias"];
	$sql	= "DROP TABLE IF EXISTS $alias"; 
	$res	= ejecutaSQL_($sql);
	$sql	= "CREATE TABLE $alias (LIKE atablas.machote INCLUDING ALL)";
	$res	= ejecutaSQL_($sql);
}
// ________________________________________________________________________________________
function buscaMovsCta($cCta,&$r){
	// Al realizar el copiado desde el servidor, no es necesario particionar el select por el limite del buffer de PHP sehun chatGPT
	$alias	= "temp.t_" . $r["alias"];
	$cTabla	= "atablas.t_" . $cCta;
	$sql 	= "insert into $alias " .
			  "select * from $cTabla a where " . $r["opcion"]["join"];
	$res	= ejecutaSQL_($sql);
	$r["_sql"] = $sql;

}
// ________________________________________________________________________________________
// ________________________________________________________________________________________
// ________________________________________________________________________________________
?>