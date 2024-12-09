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
	//
	include_once("con_pg_OpeFinW_.php"); 		// Se incluye conexión a la Base de Datos
	include_once("rutinas_.php");				// Rutinas de uso general
	require_once "../pdoF/metodos_.php";			// Métodos estáticos
	require_once "../pdoF/movConciliacion_.php";
	include_once("FiltraYPagina_.php");

	date_default_timezone_set('America/Mexico_City');
	//
	session_start(); // variables de sesión  
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php");exit; return;
	}
	// _______________________________________
	set_error_handler('customErrorHandler');
	// _______________________________________
	$idUsuario     	= $_SESSION['OpeFinClave'];
	$esquemaUsuario = $_SESSION['OpeFinEsquema'];
	//
	$respuesta = array(	'success'=>false , 'mensaje'=>""	 , 'resultados'=>array(), 'opcion'=>array() , 'ctas'=>array(),
						'datos'=>array() , 'tipoMov'=>""	 ,	'catope'=>array());

	// Lee el cuerpo de la solicitud HTTP
	$jsonData = file_get_contents('php://input');
	// Decodifica los datos JSON en un array asociativo
	$aParametros 		 = json_decode($jsonData, true);
	$vOpc 				 = $aParametros["opcion"];  					// Opción que el JS quiere que se ejecute en este php
	$respuesta["opcion"] = $aParametros;			   					// Se guardan para efectos de depuración
	$respuesta["datos"]  = array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);
	//
	$respuesta["datos"]["opcion"] = $vOpc;

	switch ($vOpc) {
	//	_____________________________________________________________________________________________
		case 'CargaCatalogos': 			// Se regresara el select de la tabla cuentasbancarias
			metodos::traeCuentasBancariasConciliadas($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case 'CargaCatalogos1': 			// Se regresara el select de la tabla cuentasbancarias
			metodos::traeCuentasBancariasConciliadas($respuesta);
			$respuesta["catope"] = metodos::traeCatalogoOperacionesConciliacion();
		break;
	//	_____________________________________________________________________________________________
		case 'FiltraMovimientosIne':
			$respuesta["success"] = FiltraYPagina($respuesta["opcion"]);
			$respuesta["mensaje"] = "";
		break;
	//	_____________________________________________________________________________________________
		case 'FiltraMovimientosConciliacion':
			$respuesta["success"] = FiltraYPagina($respuesta["opcion"]);
			$respuesta["mensaje"] = "";
		break;
	//  _____________________________________________________________________________________________
		case "operacionesConciliacion":
			$id  = $aParametros["idbanco"];
			$sql = "select opnocon,cheques,ordenes,ingresos,diasig from bancos where idbanco=$id ";
			$respuesta["resultados"] = ejecutaSQL_($sql);
			$respuesta["mensaje"]	 = "";
			$respuesta["success"]	 = true;
		break;
	//	_____________________________________________________________________________________________
		case "realizaConciliacion":
			if ( revisaSaldo($respuesta) ){
				realizaConciliacion($respuesta);
			}
		break;
	//	_____________________________________________________________________________________________
		case "reporteConciliacion":
			$respuesta["success"] = ReporteConciliacion($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "ConciliarMovimiento":
			ConciliarMovimiento($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "validaReferenciaBancos":
			$respuesta["success"] = validaReferenciaBancos($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "AgregaMovBancario":
			$respuesta["success"] = AgregaMovBancario($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "ModificaMovBancario":
			$respuesta["success"] = ModificaMovBancario($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "EliminarMovimiento":
			$respuesta["success"] = EliminarMovimiento($respuesta);
		break;
	//	_____________________________________________________________________________________________
		case "ConciliaMovBanco":
			ConciliaMovBanco($respuesta);
		break;
	//	_____________________________________________________________________________________________
		default:
			$respuesta["mensaje"] = "No esta definida en Concilia_.php [" . $vOpc . "]";
		break;
	}
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);
//	_____________________________________________________________________________________________
function revisaSaldo(&$respuesta){
	global $conn_pdo;
	$cError		= "";
	$cCta		= $respuesta["opcion"]["cuenta"];
	$cFecha		= $respuesta["opcion"]["fecha"];
	$oMovConci	= new movConciliacion( $conn_pdo);
	if ($oMovConci->buscaSaldo($cCta,$cFecha,$cError)===true){
		$respuesta["mensaje"] = "Ya existe saldo de conciliación de la cuenta $cCta para el día $cFecha ";
		return false;
	}else{
		if ($cError===""){ // Se ejecuto correctamente el select de búsqueda y no se encontro saldo
			return true;
		}else{
			$respuesta["mensaje"] = $cError;
		return false;
		}
	}
}
//	_____________________________________________________________________________________________
function realizaConciliacion(&$respuesta){
	global $conn_pdo;
	$cCta		= $respuesta["opcion"]["cuenta"];
	$cError		= "";
//	$cHoy		= date('Y-m-d'); // Formato: YYYY-MM-DD
	$cFechConci = $respuesta["opcion"]["fecha"];
	$cFechConci = DateTime::createFromFormat('Y-m-d', $cFechConci)->format('Y-m-d');
	$cUsu		= $respuesta["datos"]["idUsuario"];
	$oMovConci  = new movConciliacion( $conn_pdo);
	$lRollBack	= false;
	try {
		$conn_pdo->beginTransaction();

		$nSaldo		= $respuesta["opcion"]["saldoFin"];
		$nSaldo 	= str_replace(',', '',$nSaldo);
		$lRes		= $oMovConci->actualizaSaldo($cCta,$cFechConci,$nSaldo,$cUsu,$cError);
		$respuesta["ctas"] = "actualizo saldo";
		if ($lRes===false){
			$conn_pdo->rollBack();
			$respuesta["mensaje"] = $cError;
			return false;
		}
		$j=0; $nReg = count($respuesta["opcion"]["operaciones"]);
		for ($i=0; $i<$nReg;$i++){
			$aDatos   = [];
			$mov	  = $respuesta["opcion"]["operaciones"][$i];
			$cTipo    = $mov["tipo"];
			$cOpe	  = ($cTipo==="C") ? "SCA" : "SAB";
			$lBandera = $mov["seConcilia"];
			$nId 	  = null; // Mov de nlace con los moimientos INE tabla movimientos

			$aDatos["id_concimovimiento"]= 0;					// Es un serial
			$aDatos["idcuentabancaria"]  = $cCta;
			$aDatos["fechaoperacion"]	 = $cFechConci; 
			$aDatos["fechaconciliacion"] = null;				// "";					// $cFechConci;
			$aDatos["idoperacion"]		 = $cOpe;
			$aDatos["importeoperacion"]	 = $mov["importe"];
			$aDatos["id_layout_banco"]	 = $mov["idbanco"];
			$aDatos["conciliado"]		 = "";
			$aDatos["concepto"]			 = $mov["referencia"]; // Puede venir vacía
			$aDatos["fcve_ope_be"]		 = $mov["codigo"];
			$aDatos["idmovimiento"]		 = "";
			$aDatos["usuario_alta"]		 = $cUsu;
			$aDatos["conexion"]			 = $conn_pdo;


			$respuesta["_trace"]		 = $aDatos;

			if ($lBandera==="S"){// Buscar en tabla de movimientos
				$cBusca = $mov["cheque"];
				if ($cBusca===""){
					$cBusca  = $mov["ordPag"];
					$cBusca1 = ltrim($cBusca, '0');
					if ($cBusca==""){
						$cBusca  = $mov["refIng"];
						$cBusca1 = ltrim($cBusca, '0');
					}
				}
				if ($cBusca!==""){
					// Solo puede traer uno ? porque los cancelados estan en negativo y cuando se captura la referencia se valida que sea única 
					// a menos que en la carga del buzon se repita la referencia y el importe
					// Busca con ceros a la izquierda
					$resultado 	= $oMovConci->buscaReferenciaMovimientos($cCta,$cBusca,$mov["importe"]); 
					
					if (!empty($resultado) ){ 
						$nId = $resultado[0]["idmovimiento"];
					}else{
						// Busca sin ceros a la izquierda
						$resultado 	= $oMovConci->buscaReferenciaMovimientos($cCta,$cBusca1,$mov["importe"]);
						if (!empty($resultado) ){ 
							$nId = $resultado[0]["idmovimiento"];
						}
					}
					if ($nId>0){// Encontro el movimiento
						$aDatos["fechaconciliacion"] = $cFechConci;
						$aDatos["conciliado"]		 = "S";
						$aDatos["concepto"]			 = $aDatos["concepto"] . "|" . $nId;
						$aDatos["idmovimiento"]		 = $nId;
						// Concilia en la tabla de MOVIMIENTOS
						if ( $oMovConci->conciliaMovConciliacion($nId,$cFechConci,"S")<1 ){
							$respuesta["mensaje"] = "Excepción con $nId $cFechaConci 'S' en los movimientos";
							$conn_pdo->rollBack();
							return false;
						}else{
							// Cuenta los conciliados
							$j++;
							$respuesta["opcion"]["operaciones"][$i]["seEncontro"]="S";
						}
					}
				}
			}
			// Da de alta en la tabla de conci_movimientos
			$oMovConci->cargaDatos($aDatos);
			if ( $oMovConci->actualizaConciliacion(true)===false){
				$respuesta["mensaje"] = "No se logro actualizar los datos de conciliación ";
				$respuesta["_trace"]  = $aDatos;
				$conn_pdo->rollBack();
				return false;
			}
		}
		$respuesta["success"] = true;
		$respuesta["mensaje"] = "Se conciliarion $j movimientos de $nReg";
		$conn_pdo->commit();
	} catch (Exception $e) {
		$respuesta["mensaje"] = "a)Excepción en la base de datos {" . $e->getMessage() . "}";
		$conn_pdo->rollBack();
	}
}
//	_____________________________________________________________________________________________
function ReporteConciliacion(&$respuesta){
	global $conn_pdo;
	$lRegreso	= false;
	$oMovConci  = new movConciliacion( $conn_pdo);
	$cRegreso 	= "";
	$cCta		= $respuesta["opcion"]["cuenta"];
	$cFecha		= $respuesta["opcion"]["fecha"];

	if ( $oMovConci->traeSaldoBanco($cCta,$cFecha,$cRegreso)){
		$respuesta["datos"]["SaldodelBanco"] = $cRegreso;
		if ( $oMovConci->traeSaldoINE($cCta,$cFecha,$cRegreso) ){
			$respuesta["datos"]["SaldoFinIne"]	= $cRegreso;
			$respuesta["success"]				= true;
			$lRegreso							= true;
			$respuesta["opcion"]["objeto"]		= $oMovConci;
			require_once("repo/ConciliacionReporte_.php");
			ConciliacionReporte($respuesta);
		}else{
			$respuesta["mensaje"] = $cRegreso;
		}
	}else{
		$respuesta["mensaje"] = $cRegreso;
	}

	return $lRegreso;
}
//	_____________________________________________________________________________________________
function ConciliarMovimiento(&$respuesta){
	global $conn_pdo;
	$oMovConci  = new movConciliacion( $conn_pdo);
	$cCta 		= $respuesta["opcion"]["cuenta"];
	$fecha 		= $respuesta["opcion"]["fecha"];
	$id			= $respuesta["opcion"]["id"];
	$status		= $respuesta["opcion"]["status"];
	$cRegreso 	= "";

	if ( $oMovConci->conciliaMovimiento($cCta,$fecha,$id,$status,$cRegreso) ){
		$respuesta["success"] = true;
		$respuesta["mensaje"] = "Se concilio el movimiento";
	}else{
		$respuesta["mensaje"] = $cRegreso;
	}
}
//	_____________________________________________________________________________________________
function validaReferenciaBancos(&$respuesta){
	global $conn_pdo;
	$oMovConci  = new movConciliacion( $conn_pdo);
	$cCta 		= $respuesta["opcion"]["cuenta"];
	$cRefe		= $respuesta["opcion"]["referencia"];
	$cRegreso 	= "";
	if ($oMovConci->validaReferenciaBancos($cCta,$cRefe,$cRegreso)){
		$respuesta["mensaje"] = "";
		// No existe la referencia 
		return true;
	}else{
		$respuesta["mensaje"] = $cRegreso;
		return true; // Para que pueda ejecutar la funcion JS de regreso
	}
}
//	_____________________________________________________________________________________________
function AgregaMovBancario(&$r){
	global $conn_pdo;
	$lAlta		= true;
	$oMovConci  = new movConciliacion( $conn_pdo);
	$aDatos		= [
		"id_concimovimiento"	=>"0",
		"idcuentabancaria"		=>$r["opcion"]["idCuentabancaria"], 
		"fechaoperacion"		=>$r["opcion"]["idFecha"],
		"fechaconciliacion"		=>$r["opcion"]["idFecConci"],
		"idoperacion"			=>$r["opcion"]["idOpera"], 
		"importeoperacion"		=>$r["opcion"]["idImpo"],
		"id_layout_banco"		=>$r["opcion"]["idRefe"], 
		"conciliado"			=>$r["opcion"]["idStaConci"],
		"concepto"				=>$r["opcion"]["idCpto"],
		"fcve_ope_be"			=>"INE", 
		"idmovimiento"			=>null, 
		"usuario_alta"			=>$r["datos"]["idUsuario"] 
	];
	$oMovConci->cargaDatos($aDatos);
	try{
		$conn_pdo->beginTransaction();
		$resultado 	= $oMovConci->actualizaConciliacion($lAlta);
		if ($resultado==true){
			$r["mensaje"] = "Se adicionó el movimiento bancario de conciliación";
			$conn_pdo->commit();
			return true;
		}else{
			$r["mensaje"] = "No se logro actualizar el movimiento bancario de conciliación";
			$conn_pdo->rollBack();
			return false;
		}
	} catch (Exception $e) {
		$r["mensaje"] = "b)Excepción en la base de datos <" . $e->getMessage() . ">" . $e->getLine();
		$conn_pdo->rollBack();
		return false;
	}

}
//	_____________________________________________________________________________________________
function ModificaMovBancario(&$r){
	global $conn_pdo;
	$lAlta		= false; // Modifica
	$oMovConci  = new movConciliacion( $conn_pdo);
	$aDatos		= [
		"idcuentabancaria"		=>$r["opcion"]["idCuentabancaria"],
		"fechaoperacion"		=>$r["opcion"]["idFecha"],
		"idoperacion"			=>$r["opcion"]["idOpera"],
		"importeoperacion"		=>$r["opcion"]["idImpo"],
		"id_layout_banco"		=>$r["opcion"]["idRefe"], 
		"conciliado"			=>$r["opcion"]["idStaConci"],
		"fechaconciliacion"		=>$r["opcion"]["idFecConci"],
		"id_concimovimiento"	=>$r["opcion"]["idMovBanco"],
		"fcve_ope_be"			=>$r["opcion"]["fcve_ope_be"],
		"idmovimiento"			=>$r["opcion"]["idmovimiento"], 
		"concepto"				=>$r["opcion"]["idCpto"],
		"usuario_alta"			=>$r["datos"]["idUsuario"] 
	];
	$oMovConci->cargaDatos($aDatos);
	try{
		$conn_pdo->beginTransaction();
		$resultado 	= $oMovConci->actualizaConciliacion($lAlta);
		if ($resultado==true){
			$r["mensaje"] = "Se modificó el movimiento bancario de conciliación";
			$conn_pdo->commit();
			return true;
		}else{
			$r["mensaje"] = "No se logro modificar el movimiento bancario de conciliación";
			$conn_pdo->rollBack();
			return false;
		}
	} catch (Exception $e) {
		$r["mensaje"] = "c)Excepción en la base de datos (" . $e->getMessage() . ") " . $e->getLine();
		$conn_pdo->rollBack();
		return false;
	}
}
//	_____________________________________________________________________________________________
function EliminarMovimiento(&$r){
	global $conn_pdo;
	$oMovConci  = new movConciliacion( $conn_pdo);
	$cCta 		= $r["opcion"]["cuenta"];
	$cIdMov		= $r["opcion"]["idMov"];
	$cRegreso	= "";

	try{
		$conn_pdo->beginTransaction();
		$resultado 	= $oMovConci->eliminaMovBanco($cIdMov,$cCta,$cRegreso);
		if ($resultado==true){
			$r["mensaje"] = $cRegreso;
			$conn_pdo->commit();
			return true;
		}else{
			$r["mensaje"] = $cRegreso;
			$conn_pdo->rollBack();
			return false;
		}
	} catch (Exception $e) {
		$r["mensaje"] = "d)Excepción en la base de datos (" . $e->getMessage() . ") " . $e->getLine();
		$conn_pdo->rollBack();
		return false;
	}

}	
//	_____________________________________________________________________________________________
function ConciliaMovBanco(&$respuesta){
	global $conn_pdo;
	$oMovConci  = new movConciliacion( $conn_pdo);
	$cCta 		= $respuesta["opcion"]["cuenta"];
	$cId		= $respuesta["opcion"]["id"];
	$cStatus	= $respuesta["opcion"]["status"];
	$cFecConci	= $respuesta["opcion"]["fecha"];
	$cRegreso 	= "";
			
	if ( $oMovConci->ConciliaMovBanco($cCta,$cId,$cStatus,$cFecConci,$cRegreso) ){
		$respuesta["success"] = true;
		$respuesta["mensaje"] = "Se concilio el movimiento";
	}else{
		$respuesta["mensaje"] = $cRegreso;
	}
}
//	_____________________________________________________________________________________________	
//	_____________________________________________________________________________________________	
/*
	try {
		// Parámetros de conexión
		$dsn = 'pgsql:host=localhost;dbname=mi_base_de_datos';
		$usuario = 'mi_usuario';
		$contraseña = 'mi_contraseña';

		// Crear una instancia de PDO
		$pdo = new PDO($dsn, $usuario, $contraseña);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// Número de cuenta bancaria y nombre de la nueva tabla
		$cuentaBancaria = '11272002510';
		$nombreTabla = 'M_' . $cuentaBancaria;

		// Crear la consulta SQL para crear la nueva tabla
		$sql = "CREATE TABLE $nombreTabla (LIKE machote INCLUDING ALL)";

		// Ejecutar la consulta
		$pdo->exec($sql);

		echo "La tabla '$nombreTabla' se ha creado correctamente.";

	} catch (PDOException $e) {
		// Manejo de errores
		echo "Error: " . $e->getMessage();
	}
*/
?>