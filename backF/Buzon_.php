<?php
/*
* * * * * * * * * * * * * * * * * * * * * * * * * 
* Autor   : Miguel Ángel Bolaños Guillén        *
* Sistema : Sistema de Operación Bancaria Web   *
* Fecha   : Septiembre 2023                     *
* Descripción : Rutinas para ejecutar codigo    * 
*               SQL para interacturar con el    *
*               buzón de la BD del Sistema      *
*               Unadm-Proyecto Terminal         *
* * * * * * * * * * * * * * * * * * * * * * * * *  */
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	session_start(); // variables de sesión
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php");exit; return;
	}
	// _______________________________________
	$idUsuario     	= $_SESSION['OpeFinClave'];
	$esquemaUsuario = $_SESSION['OpeFinEsquema'];
	//
	include_once("con_pg_OpeFinW_.php"); 	// Se incluye conexión a la Base de Datos
	include_once("rutinas_.php");						// Rutinas de uso general
	require_once "../pdoF/metodos_.php";					// Métodos estáticos
	require_once "../pdoF/Movimientos_.php";
	require_once "../pdoF/Saldos_.php";

	date_default_timezone_set('America/Mexico_City');

	// inicializa arreglo que se regresara a JavaScript y que se podra visualizar en el depurador del navegador (activar con F12)
	$respuesta = array(	'success'=>false , 'mensaje'=>array(), 'resultados'=>array(), 'opcion'=>array() , 'combo'=>array(),
						'datos'=>array() , 'combo1'=>array() , 'depura'=>array()    , 'sesion'=>array() , 'objeto'=>array(),
						'anioHoy'=>0     , 'hoy'=>array()    , '_trace'=>"");
	// Se gauardan variables de sesion
	$respuesta["sesion"] = array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);

	// Lee el cuerpo de la solicitud HTTP
	$jsonData = file_get_contents('php://input');
	// Decodifica los datos JSON en un array asociativo
	$data = json_decode($jsonData, true);

// Ahora puedes acceder a los datos como $data['aParametros']
	//print_r($data);
	//var_dump("Datos enviados" .$_REQUEST['aParametros']);
	// Recupera los parámetros enviados por JS, 
	$aParametros		 = $data; 	
	$vOpc 				 = $aParametros["opcion"];  					// Opción que el JS quiere que se ejecute en este php
	$respuesta["opcion"] = $vOpc;			   							// Se guardan para efectos de depuración
	$respuesta["datos"]  = $aParametros;

	switch ($vOpc) {
// 		--------------------------------------------
		case 'CargaCatalogos': 			// Se regresara el select de la tabla cuentasbancarias
			CargaCatalogos($respuesta);
		break;
//		____________________________________________
		case "ValidaBuzon":
			ValidaBuzon($respuesta);
		break;
//		_____________________________________________		
		default:
			$respuesta["mensaje"] = "No esta definida en Buzon_.php [" . $vOpc . "]";
		break;
	}
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
return;
// __________________________________________________________
function ValidaBuzon(&$respuesta){
	// Falta validar que el año de integración no sea de años anteriores( o de fechas anteriores ???  
	// o que en enero si se permita el año anterior o año posterior , dependiendo del valor configura.valor para configura.id="01")
	if ( validaReferencias($respuesta)==false ){
		return;
	}
	$cMov = $respuesta["datos"]["cMovOpe"];
	$res  = metodos::OperaciónIngresoEgreso($cMov);
	if ($res==null){
		$respuesta["mensaje"] = "No se encontró la operación $cMov";
		return;
	}else{
		$respuesta["datos"]["cTipo"] 	 = $res[0]["tipo"];
		$respuesta["datos"]["cOperador"] = $res[0]["operador"];
	}
	actualizaMovimientos($respuesta);
}
// __________________________________________________________
function actualizaMovimientos(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	$lRollBack = false;
	try {
		$conn_pdo->beginTransaction();
		$fecha_movs			= date('d/m/Y',convierteFecha($respuesta["datos"]["fechaMov"]) ); //var_dump($fecha_movs);
		$respuesta["hoy"]	= date('d/m/Y');
		$cCtaBan 			= $respuesta["datos"]["ctBancaria"];
		$cMovOpe			= trim($respuesta["datos"]["cMovOpe"]);
		$cCtrl				= trim($respuesta["datos"]["cCtrl"]);
		$cSiglas			= trim($respuesta["datos"]["cSiglas"]);
		$cFecOpe			= $fecha_movs;
		//$cFecOpe			= volteFecha($cFecOpe);
		$cAnio 				= substr($respuesta["datos"]["fechaMov"],-4); // date("Y",strtotime($cFecOpe));
		$cUsuarioAlta		= $respuesta["sesion"]["idUsuario"];
		$ImpoSaldo			= 0.00;
		$lAgrega			= true;
		$cTipo				= $respuesta["datos"]["cTipo"];
		$nDigitos			= 4; // 4 posiciones numéricas para el consecutivo de ingresos
		$conError			= false;
		$respuesta["depura"]=$fecha_movs . "->" . $cFecOpe;// Son solo asignaciones
		if ( $cTipo=="I" ){ // para los Ingresos se tiene que determinar la Siglas del Recibo de Ingresos
			metodos::CalculaSiglas($cMovOpe,$cCtrl,$cSiglas,$nDigitos,$cCtaBan);
			//if($conError==true){ // No se paso esta variable 
			//	$respuesta["mensaje"] = "Se encontró una inconsistencia en el calculo de Siglas para Ingresos";
			//	return false;
			//}
		}
		
		// Se agregan los movimientos del Buzón
		foreach ($respuesta["datos"]["aBuzon"] as $rB) {
			// Si es un Ingreso se requiere un número de folio automático.
			if ( $cTipo=="I" ){
				$cFolio = metodos::TraeFolio($cCtaBan,$cAnio,$cSiglas,$nDigitos,$respuesta);
				if ($cFolio==null && $cTipo=="I" ){
					$lRollBack = true;
					$respuesta["_trace"] .= "Inconsistencia en TraeFolio [$cCtaBan][$cAnio][$cSiglas][$nDigitos][$cTipo]\n";
					break;
				}
			}else{
				$cFolio = $rB["docto"];
			}
			$cStatus= "";
			if ($cMovOpe=="CHE" and $rB["referencia"]!="00000000"){
				$cStatus = "I";
			}
			$nImpo  = (float)$rB["importe"];
			$aMovs  = array("idcuentabancaria"=>$cCtaBan		   ,"idoperacion"=>$cMovOpe			,"idcontrol"=>$cCtrl,
						   	"referenciabancaria"=>$rB["referencia"],"fechaoperacion"=>$cFecOpe		,"importeoperacion"=>$rB["importe"],
						   	"beneficiario"=>$rB["beneficiario"]	   ,"concepto"=>$rB["concepto"]		,"anioejercicio"=>$cAnio,
							"folio"=>$cFolio					   ,"buzon_captura"=>"B" 			,"usuarioalta"=>$cUsuarioAlta,
							"idunidad"=>$rB["ur"]				   ,"idmovimiento"=>0				,"estatus"=>$cStatus);
			$oMovimiento = new MovimientoBancario($aMovs); 
			if ( $oMovimiento->ActualizaMovimiento($conn_pdo,$lAgrega) ){
				bitacora($conn_pdo, $respuesta["sesion"]["idUsuario"],$cCtaBan,"Buzón","idOperacion: ".
									 json_encode($aMovs),$nImpo);
				$respuesta["objeto"][] = true; 
			}else{
				$lRollBack = true;
				$respuesta["_trace"] = $respuesta["_trace"] . "No se adiciono " . $rB["referencia"] . "\n";
			}
			$respuesta["combo"]	 = $aMovs;
			$ImpoSaldo	 		 = $ImpoSaldo + $nImpo;
		}
		// Requiero actualizar Saldos desde la fecha de los movimientos
		if ($lRollBack==false){
			$nIng = 0.00 ; $nEgr = 0.00 ; $nChe =0.00;
			if ($cMovOpe=="CHE"){
				$nChe = $ImpoSaldo;
			}else{
				if ($cTipo=="E"){
					$nEgr = $ImpoSaldo;
				}elseif ($cTipo=="I"){
					$nIng = $ImpoSaldo;
				}
			}
			$aMovs = array(	"idCuentaBancaria"=>$cCtaBan,"fechaSaldo"=>$cFecOpe,"SaldoInicial"=>0.00,"tipo"=>$cTipo,
							"Ingresos"=>$nIng,"Egresos"=>$nEgr,"Cheques"=>$nChe,"Conexion"=>$conn_pdo);
			$respuesta["combo1"] = $aMovs;
			$oSaldos 			 = new Saldos($aMovs,null); // El segundo parametro es la conexio cuando se genera un objeto vacío
			if ( $oSaldos->AdicionaSaldo($respuesta) ){
				bitacora($conn_pdo, $respuesta["sesion"]["idUsuario"],$cCtaBan,"Buzón","Actualiza Saldo: ".
									 json_encode($aMovs),$ImpoSaldo);
				$conn_pdo->commit();
				$respuesta["mensaje"] = "Se integró correctamente la información del buzón y se actualizó el Saldo Bancario";
				$respuesta["success"] = true;
			}else{
				$lRollBack = true;
			}
		}else{ //   if ($lRollBack==true){
			$conn_pdo->rollBack();
			$respuesta["mensaje"] = "No fue posible registrar los movimientos y Saldos \n";
		}
	} catch (Exception $e) {
		$respuesta["mensaje"] = "Excepción en la base de datos " . $e->getMessage() . "\n";
		$conn_pdo->rollBack();
	}
}
// __________________________________________________________
// __________________________________________________________
// __________________________________________________________
// __________________________________________________________
// __________________________________________________________
// __________________________________________________________
function validaReferencias(&$respuesta){
	$regreso = true;
	$cCtaBan = $respuesta["datos"]["ctBancaria"];
	foreach ($respuesta["datos"]["aBuzon"] as $rBuzon) {
		$cRefBan = $rBuzon["referencia"];
		$idMov   = metodos::ExisteReferenciaBancaria($cRefBan,$cCtaBan);
		if ( $idMov!="" ){ // Ya existe la referencia
			$respuesta["mensaje"] = "Ya existe la referencia bancaria $cRefBan con Id:$idMov \n" ;
			$regreso 			  = false;
		}
	}
	return $regreso;
}
// __________________________________________________________
function CargaCatalogos(&$respuesta){
	try{
		// Traigo Operación-Control
		$sql = 	"select a.idoperacion, b.idcontrol , b.nombre from operacionesbancarias a , controlesbancarios b " .
				" where a.idoperacion=b.idoperacion order by nombre ";
		$res = ejecutaSQL_($sql);
		if ( $res!=null){
			$respuesta["combo"][] 	 = " ,Seleccione"; // Valor nulo
			foreach ($res as $r ){	// llena el combo con la clave y nombre de la operación bancaria
				$respuesta["combo"][] = $r["idoperacion"]."-".$r["idcontrol"] . "," . $r["nombre"] ." - [". $r["idoperacion"]."-".$r["idcontrol"]."]";
			}
			// Busco las cuentas Bancarias , se filtran si el usuario no es el administrador
			if ($respuesta["sesion"]["esquemaUsuario"]=="Administrador"){
				$sql = "select idcuentabancaria, nombre, siglas from cuentasbancarias where estatus=true order by idcuentabancaria";
			}else{
				$cIdUsu = $respuesta["sesion"]["idUsuario"];
				$sql = 	"select a.idcuentabancaria, b.nombre, b.siglas from accesos a , cuentasbancarias b where ".
						" a.idcuentabancaria=b.idcuentabancaria and a.idusuario='$cIdUsu' ";
				$respuesta["depura"] = $sql;
			}
			$res = ejecutaSQL_($sql);
			if ( $res!=null){
				$respuesta["combo1"][] 	 = " ,Seleccione"; // Valor nulo
				foreach ($res as $r ){	// llena el combo con la clave y nombre de la operación bancaria
					$respuesta["combo1"][] = $r["idcuentabancaria"] . "|" .$r["siglas"] . "," . $r["idcuentabancaria"] . "-" . $r["nombre"];
				}
				$sql = "select valor from configuracion where idconfiguracion='01' ";
				$res = ejecutaSQL_($sql);
				if ( $res!=null){
					date_default_timezone_set('America/Mexico_City');
					$respuesta["_trace"] = $res[0]["valor"]; // Menor año permitido
					$respuesta["success"]	 = true;
					$respuesta["mensaje"]	 = "";
					$respuesta["anioHoy"]	 = date('Y');		 // Máximo año permitido
					$respuesta["hoy"]		 = date('d/m/Y');
				}else{
					$respuesta["mensaje"] = "No se ha definido el año permitido de captura de movimientos";
				}
			}else{
				$respuesta["mensaje"] = "No se han definido Cuentas Bancarias para el usuario";
			}
		}else{
			$respuesta["mensaje"] = "No se han definido Operaciones-Controles Bancarios en el Sistema";
		}
	}catch(Exception $e){
		$respuesta["mensaje"] ="Error de programación? [" . $e->getMessage() . "]";
	}
}