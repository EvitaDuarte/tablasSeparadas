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
	require_once("../pdoF/Usuarios_c_.php");					// Clase Usuarios
	require_once("../pdoF/Esquemas_c_.php");					// Clase Usuarios

	global $conn_pdo;
	// inicializa arreglo que se regresara a JavaScript y que se podra visualizar en el depurador del navegador que se activa con F12
	$respuesta = array('success'=>false  , 'mensaje'=>array(), 'resultados'=>array(), 'opcion'=>array() , 
						'datos'=>array() , 'combo'=>array()  , 'depura'=>array() , 'combo1'=>array() , 'bita'=>false);

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

	global $oUsuarios;	// Pra almacenar objeto de la clase usuarios
	$oUsuarios = null;
	if ( isset($aDatos["idUsuario"])==false || isset($aDatos["nombre"])==false ){
		$oUsuarios = new Usuarios(null);
	}else{

		$oUsuarios = new Usuarios( [ "idUsuario"=>$aDatos["idUsuario"], "Nombre" =>$aDatos["nombre"] , "idUnidad"  =>$aDatos["idUnidad"], 
									 "idEsquema"=>$aDatos["idEsquema"], "estatus"=>$aDatos["estatus"], "idusuarioa"=>$_SESSION['OpeFinClave'] ] );
	}

	switch ($vOpc) {
			case "UsuariosConsulta":
				$oEsquemas = new Esquemas(null);
				$oEsquemas->comboEsquema($respuesta);	// Guarda el resultado del SQL en respuesta.comobo
				$oUsuarios->UsuariosCarga($respuesta);	// Guarda el resultado del SQL en respuesta.resultados
				//UsuariosCarga($respuesta);
				//if ($respuesta["datos"]["traeEsquemas"]){ // Voy por lo que tenga la tabla de esquemas
				//	UsuariosEsquemas($respuesta);
				//}
			break;
		case "validaLdap":
			validaLdap($respuesta);
			break;
		case "UsuarioAgrega":
			$oUsuarios->UsuarioAgrega($respuesta);
			break;
		case "UsuarioModifica":
			$oUsuarios->UsuarioModifica($respuesta);
			break;
		case "UsuarioEliminar":
			$oUsuarios->UsuarioEliminar($respuesta);
			break;
		// ____________________________________________
		default:
			$respuesta["mensaje"] = "No esta construida la opcion [" . $vOpc . "] en Usuarios.php";
			break;
	}
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
return;

// ______________________________________________________________________________________________________
function validaLdap(&$respuesta){
	//
	$username 			  = $respuesta["datos"]["idUsuario"];
	$respuesta["mensaje"] = "No se encontraron datos del Usuario [$username] en el LDap"; // Asumo que no se encuentra
	// - SiteGround
	return;
	
	if($connect = @ldap_connect('ldap://autenticacion.ife.org.mx')){					  	// Conectar al servidor Ldap
		if(($bind = @ldap_bind($connect)) == true){											// Autentificar al usuario
			$res_id   = ldap_search( $connect, "ou=people,dc=ife.org.mx", "uid=$username");
			$entry_id = ldap_first_entry($connect, $res_id);
			
			if($entry_id){																	// Se encontraron datos
				$respuesta["resultados"] = array( 											// Se recuperan para JS
					'uid' 	=>ldap_get_values($connect, $entry_id, "uid") ,
					"mail"	=>ldap_get_values($connect, $entry_id, "mail") ,
					"sn" 	=>ldap_get_values($connect, $entry_id, "sn") ,
					"gn"	=>ldap_get_values($connect, $entry_id, "givenname"),
					"ou"	=>ldap_get_values($connect, $entry_id, "ou"),
					"curp"	=>ldap_get_values($connect, $entry_id, "curp"),
					"cn"	=>ldap_get_values($connect, $entry_id, "cn"),
					"pT"	=>ldap_get_values($connect, $entry_id, "personalTitle"),
					"ur"	=>getURAdscripcion(
										ldap_get_values($connect, $entry_id, "idEstado")[0], 
										ldap_get_values($connect, $entry_id, "idDistrito")[0],
										ldap_get_values($connect, $entry_id, "ou")[0])
				);
				$respuesta["mensaje"] = "";//"Se encontraron datos del Usuario en el LDap";
				$respuesta["success"] = true;
			}
		}
	}
	@ldap_close($connect);
}
// ______________________________________________________________________________________________________
