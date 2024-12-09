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
	$usrClave     = pg_escape_string($_SESSION['OpeFinClave']); // Usuario que se logea
	//
	include_once("con_pg_OpeFinW_.php"); 		// Se incluye conexión a la Base de Datos
	include_once("rutinas_.php");
	require_once("../pdoF/Esquemas_c_.php");					// Clase esquemas

	global $conn_pdo;
	// inicializa arreglo que se regresara a JavaScript y que se podra visualizar en el depurador del navegador que se activa con F12
	$respuesta = array('success'=>false, 'mensaje'=>array(), 'resultados'=>array(), 'opcion'=>array() , 
						'datos'=>array() , 'combo'=>array(), 'depura'=>array() , 'combo1'=>array() , 'bita'=>false);

	// Lee el cuerpo de la solicitud HTTP
	$jsonData = file_get_contents('php://input');
	// Decodifica los datos JSON en un array asociativo
	$data = json_decode($jsonData, true);


	$aDatos	   = $data; //json_decode($_POST['aParametros'],true);		// Recupera el arreglo con los datos que envía JavaScript
	// var_dump($aDatos); con request se tiene un arreglo
	if (isset($aDatos[0])){ //Opción a ejecutar, solo JS manda la opción, cuando se cargan catálogos para llenar tablas HTML o selectbox
		$vOpc =  $aDatos[0];
	}else{					// Además de la opción se envían parámetros
		$vOpc = $aDatos["opcion"]; 
	}

	//	Se guardan para efectos de depuración
	$respuesta["opcion"] = $vOpc;
	$respuesta["datos"]  = $aDatos;

	global $oEsquemas;
	$oEsquemas = null;
	if ( isset($aDatos["idEsquema"])==false ){
		$oEsquemas = new Esquemas(null);
	}else{
		$oEsquemas = new Esquemas([ "idEsquema"=>$aDatos["idEsquema"], "descripcion"=>$aDatos["descripcion"],  
									"estatus"=>$aDatos["estatus"] 	 , "usuarioAlta"=>$_SESSION['OpeFinClave']   ]);
	}

	//
	switch ($vOpc) {
		// ------------------
		case 'EsquemasConsulta': // Se regresara el select de la tabla esquemas
			$oEsquemas->EsquemasConsulta($respuesta);
			//EsquemasConsulta($respuesta);
			break;
		case "EsquemaAgrega":	// Para Ingresar un nuevo esquema
			$oEsquemas->EsquemaAgrega($respuesta);	
			break;
		case "EsquemaModifica":
			$oEsquemas->EsquemaModifica($respuesta);
			break;
		case "EsquemaEliminar":
			$oEsquemas->EsquemaEliminar($respuesta);
			break;
		default:
			$respuesta["mensaje"] = "No esta construida la funcionalidad de [" . $vOpc . "]";
			break;
	}
	//
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
return;

?>