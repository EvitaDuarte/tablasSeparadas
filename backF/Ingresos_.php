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
define('OchoCeros', '00000000');
define('TitCance' , '** CANCELADO ** ');
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	//
	include_once("con_pg_OpeFinW_.php"); 	// Se incluye conexión a la Base de Datos
	include_once("rutinas_.php");						// Rutinas de uso general
	require_once "../pdoF/metodos_.php";					// Métodos estáticos
	require_once "../pdoF/Movimientos_.php";
	require_once "../pdoF/Saldos_.php";
	require_once "FiltraYPagina_.php";
	/* include_once("Pagina_y_Busca.php"); */

	date_default_timezone_set('America/Mexico_City');
	//
	session_start(); // variables de sesión  
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php");exit; return;
	}
	// _______________________________________
	$idUsuario     	= $_SESSION['OpeFinClave'];
	$esquemaUsuario = $_SESSION['OpeFinEsquema'];
	//
	$respuesta = array(	'success'=>false , 'mensaje'=>""	 , 'resultados'=>array(), 'opcion'=>array() , 'ctas'=>array(),
						'urs'=>array()   , 'opera'=>array()  , 'ctrl'=>array()      , 'datos'=>array()  , 'tipoMov'=>"",
						'_trace'=>"");

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
			$respuesta["tipoMov"] = "I";
			metodos::CargaCatalogosMovimientos($respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'CargaCatalogosEgresos': 			// Se regresara el select de la tabla cuentasbancarias
			$respuesta["tipoMov"] = "E";
			metodos::CargaCatalogosMovimientos($respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'CargaCatalogosCheques': 			// Se regresara el select de la tabla cuentasbancarias
			$respuesta["tipoMov"] = "C";
			metodos::CargaCatalogosMovimientos($respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'SaldoHoy':
			$cCta = $aParametros["cuenta"];
			metodos::SaldoHoy($cCta,$respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'ReciboIngreso':
			metodos::ReciboIngreso($respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'validaReferencia':
			validaReferencia($respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'ConsultaMovimientosBancarios':
			$respuesta["success"] = FiltraYPagina($respuesta["opcion"]); // BuscaYPagina($respuesta["opcion"]);
			$respuesta["mensaje"] = "";
		break;
//	_____________________________________________________________________________________________
		case 'AdicionarMovimiento':
			$lRevisa  = true;
			$vRefBan  = $respuesta["opcion"]["idRefe"];
			$cCtaBan  = $respuesta["opcion"]["idCuentabancaria"];
			$cTipoMov = $respuesta["opcion"]["idTipo"];
		// Revisar si otro usuario no este capturando lo mismo al mismo tiempo
			if ($cTipoMov=="C"){ // Cheques
				if ($vRefBan==OchoCeros){// Estas referencias no se validan
					$lRevisa = false;
				}
			}
			if ($lRevisa){
				if (  metodos::ExisteReferenciaBancaria($vRefBan,$cCtaBan) =="" ){
					AdicionarMovimiento($respuesta);
				}else{
					$respuesta["opcion"]["mensaje"] = "Ya existe la referencia $vRefBan para la cuenta $cCtaBan"; // ??? por que lo puse
					$respuesta["mensaje"] = $respuesta["opcion"]["mensaje"];
				}
			}else{
				AdicionarMovimiento($respuesta);
			}
		break;
//  _____________________________________________________________________________________________
		case 'EliminarMovimiento':
			EliminarMovimiento($respuesta);
		break;		
//	_____________________________________________________________________________________________ 
		case 'ModificaMovimiento':
			ModificaMovimiento($respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'CancelarMovimiento':
			CancelarMovimiento($respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'CancelarLayOut':
			CancelarLayOut($respuesta);
		break;
//	_____________________________________________________________________________________________
		case 'EliminarLayOut':
			EliminarLayOut($respuesta);
		break;
//	_____________________________________________________________________________________________
		default:
			$respuesta["mensaje"] = "No esta definida en Ingresos_.php [" . $vOpc . "]";
		break;
	}
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
return;
// __________________________________________________________________________________
function validaReferencia(&$respuesta){// Es llamada cuando se pierde el foco del html idRefe
	$vRefBan = $respuesta["opcion"]["referencia"];
	$cCtaBan = $respuesta["opcion"]["cuenta"];
	$idOri   = $respuesta["opcion"]["idMov"];
	$idMov	 = metodos::ExisteReferenciaBancaria($vRefBan,$cCtaBan);
	// Se dejara al cliente revisar cuando se intenta duplicar una referencia, 
	// para que regrese el cursor al input text de referencia
	if ($idMov!=""){
		if ($idMov!=$idOri){
			$respuesta["opcion"]["mensaje"] = "Ya existe la referencia $vRefBan para la cuenta $cCtaBan";
		}else{
			$respuesta["opcion"]["mensaje"] = "";
		}
	}else{
		$respuesta["opcion"]["mensaje"] = "";
	}
	$respuesta["success"] = true;
}
// __________________________________________________________________________________
function AdicionarMovimiento(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	creaArregloMovimiento($respuesta,true);
	$oMovimiento = new MovimientoBancario($respuesta["MovBancario"]);
	$lRollBack	 = false; 
	$cCtaBan	 = $respuesta["MovBancario"]["idcuentabancaria"];
	$cFecha 	 = $respuesta["opcion"]["idFecha"];
	$nImpo		 = $respuesta["MovBancario"]["importeoperacion"];
	$cTipoMov	 = $respuesta["opcion"]["idTipo"];
	$cOperacion	 = $cTipoMov=="I"?"Ingreso":($cTipoMov=="E"?"Egreso":"Cheque");
	$lAgrega	 = true;
	try {
		$conn_pdo->beginTransaction();
		$lActualiza = $oMovimiento->ActualizaMovimiento($conn_pdo,$lAgrega);
		if ( $lActualiza ){
			bitacora($conn_pdo, $respuesta["datos"]["idUsuario"],$cCtaBan,$cOperacion,"idOperacion: ".json_encode($respuesta["MovBancario"]),$nImpo);
			// Se actualiza Saldo Bancario
			if ( actualizaSaldos($respuesta,$nImpo,$cFecha,$cTipoMov) ){
				$conn_pdo->commit();
				$respuesta["mensaje"] = "Se adicionó correctamente el $cOperacion. Se actualiza Saldo Bancario";
				$respuesta["success"] = true;
			}else{
				$lRollBack = true;
			}
		}else{
			$lRollBack = true;
			$respuesta["mensaje"] = "No se adicionó " . $respuesta["MovBancario"]["referenciabancaria"] ;
			$respuesta["_trace"] .=  $respuesta["mensaje"] . "\n";
		}
		if ( $lRollBack==true){
			$respuesta["mensaje"]  = "No se adicionó << " . $respuesta["MovBancario"]["referenciabancaria"] . ">>";
			$respuesta["_trace"]  .=  $respuesta["mensaje"] . "\n";
			$conn_pdo->rollBack();
		}
	} catch (Exception $e) {
		$respuesta["mensaje"] = "Excepción en la base de datos " . $e->getMessage() . "\n";
		$conn_pdo->rollBack();
	}
}
// __________________________________________________________________________________
function EliminarMovimiento(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	$lRollBack 	= false;
	$cId 		= $respuesta["opcion"]["idMovimiento"];
	$nImpo 		= ($respuesta["opcion"]["idImpo"])*(-1);
	$cFecha 	= $respuesta["opcion"]["idFecha"];
	$cCtaBan	= $respuesta["opcion"]["idCuentabancaria"];
	$cTipoMov	= $respuesta["opcion"]["idTipo"];
	$cOperacion	= $cTipoMov=="I"?"Ingreso":($cTipoMov=="E"?"Egreso":"Cheque");
	$cOperacion.= "[" . $respuesta["opcion"]["opcion"] . "]";
	try {
		$conn_pdo->beginTransaction();
		$elimina = metodos::EliminaMovimiento($cId,$cCtaBan);
		if ($elimina){
			// Guarda la eliminación en la bitácora
			bitacora($conn_pdo, $respuesta["datos"]["idUsuario"],$cCtaBan,"Eliminar $cOperacion","idOperacion: ".json_encode($respuesta["opcion"]),$nImpo);
			if ( actualizaSaldos($respuesta,$nImpo,$cFecha,$cTipoMov) ){
				$conn_pdo->commit();
				$respuesta["mensaje"] = "Se eliminó correctamente el $cOperacion $cId. Se actualiza Saldo Bancario";
				$respuesta["success"] = true;
			}else{
				$lRollBack = true;
			}
		}else{
			$lRollBack = true;
		}
		if ($lRollBack==true){
			$conn_pdo->rollBack();
			$respuesta["mensaje"] = "No se eliminó correctamente el $cOperacion $cId. No se actualizó Saldo Bancario";
		}
	} catch (Exception $e) {
		$respuesta["mensaje"] = "Excepción en la base de datos " . $e->getMessage() . "\n";
		$conn_pdo->rollBack();
	}
}
// __________________________________________________________________________________
function ModificaMovimiento(&$respuesta){
	global $conn_pdo;							// Variable global que tiene la conexión a la Base de Datos
	creaArregloMovimiento($respuesta,false);	// Deja los datos del movimiento capturado en $respuesta["MovBancario"]
	//
	$lModificaSaldos	= false;
	$lRollBack 			= false;
	$lCambioRecibo		= false;
	$lCambioReferencia  = false;
	$cCtaBan	 		= $respuesta["opcion"]["idCuentabancaria"];
	$nImpo		 		= $respuesta["opcion"]["idImpo"];
	$cFecha				= $respuesta["opcion"]["idFecha"];
	$oMov    			= new MovimientoBancario(null);	// Se crea un objeto Movimiento bancario
	$cId				= $respuesta["opcion"]["idMovimiento"];
	$aMovOri 			= $oMov->traeMovimientoxId( $conn_pdo, $cId )[0];// Regresa un arreglo de un solo elemento
	$respuesta["ctrl"]  = $aMovOri; // para depurar
	$nImpoOri			= $aMovOri["importeoperacion"];
	$cFechaOri			= ddmmyyyy($aMovOri["fechaoperacion"]);
	$cTipoMov			= $respuesta["opcion"]["idTipo"];
	$cOperacion			= $cTipoMov=="I"?"Ingreso":($cTipoMov=="E"?"Egreso":"Cheque");
	$vRefBan			= $respuesta["opcion"]["idRefe"];

	$respuesta["mensaje"] = "En construcción";
	//
	if ( HuboCambios($aMovOri,$respuesta["opcion"],$lModificaSaldos,$lCambioRecibo,$lCambioReferencia)==false ){
		$respuesta["mensaje"] = "No se detectaron cambios ...";
		return false;
	}
	//
	if ( $lCambioReferencia ){
		$lRevisa = true;
		if ($cTipoMov=="C"){// Cheques
			if ($vRefBan==OchoCeros){
				$lRevisa = false;
			}
		}
		if ( $lRevisa){
			if (  metodos::ExisteReferenciaBancaria($vRefBan,$cCtaBan) !=="" ){
				$respuesta["mensaje"] = "Ya existe la referencia $vRefBan para la cuenta $cCtaBan";
				
				return false;
			}
		}
	}
	//
	if ($cTipoMov=="I"){
		if ( $lCambioRecibo){ 							// Aquí todavía no sirve hasta que se incorpore el desglose de In_gresos
			$cFolio = calculaReciboIngreso($respuesta); // Lo vuelve a calcular
			if ($cFolio!==""){
				$respuesta["opcion"]["idRecibo"] = $cFolio;
			}
		}
	}
	//
	creaArregloMovimiento($respuesta,false);
	$oMovimiento = new MovimientoBancario($respuesta["MovBancario"]);
	//  Va a modificar el movimiento y en dado caso recalcular saldos
	try{
		$conn_pdo->beginTransaction();
		$lActualiza = $oMovimiento->ActualizaMovimiento($conn_pdo,false);
		if ( $lActualiza==false){
			$respuesta["mensaje"] = "No se modifico " . $respuesta["MovBancario"]["referenciabancaria"] ;
			$lRollBack = true;
		}else{
			bitacora($conn_pdo, $respuesta["datos"]["idUsuario"],$cCtaBan,"Modifica $cOperacion","idOperacion: ".json_encode($respuesta["MovBancario"]),$nImpo);
			if ( $lModificaSaldos ){
				// Si importe pasa de 900 a 1900 se tiene que actualizar por 1900-900  1000
				// Si importe pasa de 1900 a 900 se tiene que actualizar por 900-1900 -1000
				// Quita el importe anterior del día en que se origino
				if ( actualizaSaldos($respuesta,-$nImpoOri,$cFechaOri,$cTipoMov)==false ){ 
					$lRollBack = true;
				}else{
					// Se agrega el "nuevo" importe en su fecha
					if ( actualizaSaldos($respuesta,$nImpo,$cFecha,$cTipoMov)==false ){ 
						$lRollBack = true;
					}
				}
			}
		}
		if ($lRollBack){
			$conn_pdo->rollBack();
			$respuesta["mensaje"] = "No se actualizó correctamente el $cOperacion $cId. No se actualizó Saldo Bancario";
		}else{
			$conn_pdo->commit();
			$respuesta["mensaje"] = "Se actualizo correctamente el $cOperacion $cId.". ($lModificaSaldos?" Se actualiza Saldo Bancario":"");
			$respuesta["success"] = true;
		}
	}catch (Exception $e) {
		$respuesta["mensaje"] = "Excepción en la base de datos " . $e->getMessage() . "\n";
		$conn_pdo->rollBack();
	}
}
// __________________________________________________________________________________
function CancelarMovimiento(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	$lRollBack 	= false;
	$cId 		= $respuesta["opcion"]["idMovimiento"];
	$cUsuario   = $respuesta["datos"]["idUsuario"];
	$nImpo 		= $respuesta["opcion"]["idImpo"]; // Ya trae signo negativo
	$cFecha 	= $respuesta["opcion"]["idFecCan"];
	$cCtaBan	= $respuesta["opcion"]["idCuentabancaria"];
	$cTipoMov	= $respuesta["opcion"]["idTipo"];
	$cOperacion	= $cTipoMov=="I"?"Ingreso":($cTipoMov=="E"?"Egreso":"Cheque");
	$lAgrega	= true;
	try {
		$conn_pdo->beginTransaction();
		creaArregloMovimiento($respuesta,false);	// Deja los datos del movimiento capturado en $respuesta["MovBancario"]
		$oMovimiento 		= new MovimientoBancario($respuesta["MovBancario"]);
		$respuesta["ctrl"]	= $oMovimiento->cancelaMovimiento($conn_pdo,$cId); // Cancela el original
		bitacora($conn_pdo, $cUsuario,$cCtaBan,"Cancela $cTipoMov","idOperacion: ".$cId,$nImpo);
		$lActualiza = $oMovimiento->ActualizaMovimiento($conn_pdo,$lAgrega);
		if ( $lActualiza ){
			bitacora($conn_pdo, $cUsuario, $cCtaBan, $cTipoMov,"Cancelación: ".json_encode($respuesta["MovBancario"]),$nImpo);
			// Se actualiza Saldo Bancario
			if ( actualizaSaldos($respuesta,$nImpo,$cFecha,$cTipoMov) ){
				$conn_pdo->commit();
				$respuesta["mensaje"] = "Se adicionó el $cOperacion por Cancelación. Se actualiza Saldo Bancario";
				$respuesta["success"] = true;
			}else{
				$lRollBack = true;
			}
		}else{
			$lRollBack = true;
		}

	} catch (Exception $e) {
		$respuesta["_trace"] .= $e->getMessage();
		$lRollBack 			  = true;
	}
	if ($lRollBack){
		$conn_pdo->rollBack();
		$respuesta["mensaje"] = "No se concreto la cancelación. Revíse e intente de nuevo";
	}
}
// __________________________________________________________________________________
function actualizaSaldos(&$respuesta,$nImpo,$cFecha,$cTipoMov){
	global $conn_pdo;
	$cCtaBan = $respuesta["opcion"]["idCuentabancaria"];
	//$nImpo   = $respuesta["opcion"]["idImpo"];
	$nIng    = 0.00; 
	$nEgr 	 = 0.00 ; 
	$nChe 	 = 0.00;
	$cMov    = "";
	if ($cTipoMov=="I"){
		$nIng = $nImpo;
		$cMov = "Ingreso";
	}elseif ($cTipoMov=="E"){
		$nEgr = $nImpo;
		$cMov = "Egreso";
	}elseif ($cTipoMov=="C"){
		$cMov = "Cheque";
		$nChe = $nImpo;
	}
	$aMovs = array(	"idCuentaBancaria"	=>$cCtaBan,
					"fechaSaldo"		=>$cFecha,
					"SaldoInicial"		=>0.00,
					"tipo"				=>$cTipoMov,
					"Ingresos"			=>$nIng,
					"Egresos"			=>$nEgr,
					"Cheques"			=>$nChe,
					"Conexion"			=>$conn_pdo);
	$respuesta["ctrl"] = $aMovs; // solo para depurar
	$oSaldos 		   = new Saldos($aMovs,null); // El segundo parametro es la conexio cuando se genera un objeto vacío
	if ( $oSaldos->AdicionaSaldo($respuesta) ){
		bitacora($conn_pdo, $respuesta["datos"]["idUsuario"],$cCtaBan,$cMov,"Actualiza Saldo: ".json_encode($aMovs),$nImpo);
		return true;
	}else{
		return false;
	}
}
// __________________________________________________________________________________
function creaArregloMovimiento(&$respuesta,$lAltas){
	// 
	$aIng     = $respuesta["opcion"];
	$cTipoMov = $aIng["idTipo"];
	$cFolio   = $aIng["idRecibo"];
	if ($cTipoMov=="I"){
		if ($lAltas){
			$cFolio = calculaReciboIngreso($respuesta);
			$respuesta["_trace"] .= "Folio recalculado [" . $cFolio . "]";
			if ($cFolio==""){
				$cFolio = $aIng["idRecibo"];
			}
		}
	}

	$aDat = $respuesta["datos"];
	$respuesta["MovBancario"] = array(	
		"idcuentabancaria"	=>$aIng["idCuentabancaria"]	,"idoperacion"		=>$aIng["idOpera"]	 	,"idcontrol"		=>$aIng["idCtrl"],
		"referenciabancaria"=>$aIng["idRefe"]			,"fechaoperacion"	=>$aIng["idFecha"]	 	,"importeoperacion"=>$aIng["idImpo"],
		"beneficiario"		=>$aIng["idBenefi"]	   		,"concepto"			=>$aIng["idCpto"]	 	,"anioejercicio"	=>$aIng["idAnio"],
		"folio"				=>$cFolio					,"buzon_captura"	=>"C" 				 	,"usuarioalta"		=>$aDat["idUsuario"],
		"idunidad"			=>$aIng["idUr"]				,"idmovimiento"		=>$aIng["idMovimiento"] ,"estatus"			=>$aIng["estatus"]) ;
}
// __________________________________________________________________________________
function calculaReciboIngreso(&$respuesta){
	$nDigitos = 4; 
	$cFolio   = "";
	$aIng     = $respuesta["opcion"];  
	$cAnio    = $aIng["year"] ; // Año valido definido en la configuracion
	$cSiglas  = $aIng["idSiglas"]; 

	metodos::CalculaSiglas($aIng["idOpera"],$aIng["idCtrl"],$cSiglas,$nDigitos,$aIng["idCuentabancaria"]);
	$respuesta["_trace"] .= "Datos siglas [" . $aIng["idOpera"] . "][" . $aIng["idCtrl"]. "][" . $cSiglas. "][" . $nDigitos. "][" .$aIng["idCuentabancaria"] ."]";
	$cFolio 		= metodos::TraeFolio($aIng["idCuentabancaria"],$cAnio,$cSiglas,$nDigitos,$respuesta);
	$aIng["recibo"] = $cFolio;
	return $cFolio;
}
// __________________________________________________________________________________
function HuboCambios($aOri,$aNue,&$lSaldos,&$lRecibo,&$lRefe){ // Si regresa false es que si hay cambios a guardar
	$lSaldos = false; // No hay cambio en Saldos
	$lRecibo = false; // No cambio el Recibo
	$lRefe	 = false; // No hay cambios en la referencia
	$cFecha  = $aNue["idFecha"];
	$cFecha  = volteFecha($cFecha); 
	//$respuesta["_trace"] .= " Fecha volteada $cFecha ";
	if ( $aOri["folio"]!==$aNue["idRecibo"] ){
		$lRecibo = true; 
		// No se debe poner true para saber si tambien cambio la fecha o el importe y generar saldos
	}
	if ($aOri["referenciabancaria"]!==$aNue["idRefe"]){
		$lRefe = true;
		// No se debe poner true para saber si tambien cambio la fecha o el importe y generar saldos
	}
//  ______________________________________________
//	$cFechaMenor = min($cFecha,$aOri["fechaoperacion"]);	// Creo que no se va a ocupar
//  ______________________________________________
	if ($aOri["fechaoperacion"]!==$cFecha){ // !== verifica el tipo y son de diferente tipo pero igual contenido
		$lSaldos = true;
		return true;
	}
	if ( $aOri["importeoperacion"]!==$aNue["idImpo"]){
		$lSaldos = true;
		return true;
	}
//  ______________________________________________	
	if ($lRecibo || $lRefe){ // Si hubo cambios en la referencai o el recibo
		return true;
	}
	if ( $aOri["idunidad"]!==$aNue["idUr"] || $aOri["idoperacion"]!==$aNue["idOpera"] || $aOri["idcontrol"]!==$aNue["idCtrl"] ){
		return true;
	}
	if ( $aOri["beneficiario"]!==$aNue["idBenefi"] || $aOri["concepto"]!==$aNue["idCpto"] || $aOri["anioejercicio"]!==$aNue["idAnio"] ){
		return true;
	}
	return false; // No hay cambios
}
// __________________________________________________________________________________
function CancelarLayOut(&$respuesta){
	if (movsCanceIncorrectos($respuesta)){
		return false;
	}
	$nMovsCance = cancelaMovsCorrectos($respuesta);

	$respuesta["mensaje"] ="Proceso concluido. Se cancelaron $nMovsCance y se actualizó saldo";


}
// __________________________________________________________________________________
function cancelaMovsCorrectos(&$respuesta){
	global $conn_pdo;
	$aMovsCance = $respuesta["opcion"]["aDatoCance"];
	$opcion 	= $respuesta["opcion"];
	$i			= 0;
	$nMovsCance = 0;
	$oMov		= new MovimientoBancario(null);	// Se crea un objeto Movimiento bancario
	foreach($aMovsCance as $aCance){
		if ($aCance["estatus"]==""){
//
			$cCta									 = trim($opcion["ctBancaria"]);
			$aMovOri								 = $oMov->traeMovimientoxId($conn_pdo,$aCance["idMov"],$cCta)[0];
//
			$respuesta["opcion"]["idCuentabancaria"] = $cCta;
			$respuesta["opcion"]["idOpera"] 		 = $aMovOri["idoperacion"]; // $opcion["opeCan"];
			$respuesta["opcion"]["idCtrl"] 			 = $aMovOri["idcontrol"];
			$respuesta["opcion"]["idRefe"]		 	 = $aCance["referencia"];
			$respuesta["opcion"]["idFecha"] 		 = date("d-m-Y", strtotime($aCance["fechaCance"]));
			$respuesta["opcion"]["idImpo"] 			 = doubleval($aCance["importe"])*(-1);
			$respuesta["opcion"]["idBenefi"] 		 = TitCance . $aMovOri["beneficiario"];
			$respuesta["opcion"]["idCpto"] 			 = TitCance . $aMovOri["concepto"];
			$respuesta["opcion"]["idAnio"]		 	 = $aMovOri["anioejercicio"];
			$respuesta["opcion"]["idRecibo"]		 = $aMovOri["folio"];
			$respuesta["opcion"]["idUr"] 			 = $aMovOri["idunidad"];
			$respuesta["opcion"]["idMovimiento"] 	 = $aCance["idMov"];
			$respuesta["opcion"]["estatus"]		 	 = "C"; // Cancelación			
//			
			$respuesta["opcion"]["idTipo"]		 	 = $opcion["idTipo"];
			$respuesta["opcion"]["idFecCan"]	 	 = date("d-m-Y", strtotime($aCance["fechaCance"]));
			$respuesta["success"]					 = false; 
			CancelarMovimiento($respuesta);
			if ($respuesta["success"]){
				$nMovsCance = $nMovsCance + 1 ;
			}

		}
		$i++;
	}
	return $nMovsCance;
}
// __________________________________________________________________________________
function movsCanceIncorrectos(&$respuesta){ 
	// Se tiene que verificar que exista la referencia Bancaria, que no esten cancelado ya el movimiento
	$cCtaBan = $respuesta["opcion"]["ctBancaria"];
	$cYear	 = date("Y");
	$i		 = 0;
	foreach ($respuesta["opcion"]["aDatoCance"] as $aCance) {
		$cRefBan = $aCance["referencia"];
		$aMov    = metodos::revisaReferenciaBancaria($cRefBan,$cCtaBan);

		if (!empty($aMov)) {
        	if (count($aMov) == 1) {
            	// Si $aMov tiene un solo elemento, agregarlo directamente al arreglo
            	$respuesta["ctas"][] = $aMov[0];
            	$impoBd   = trim($aMov[0]["importeoperacion"]);
            	$impoTxt  = trim($aCance["importe"]);
            	$fechaMov = trim($aMov[0]["fechaoperacion"]);
            	$cAnioMov = substr($fechaMov,0,4);
            	$fechaTxt = trim($aCance["fechaCance"]);
            	// Si los importes son correctos
            	if ($impoBd==$impoTxt){
            		// Fecha menor a la del movimiento o de años anteriores
            		if( ($fechaTxt < $fechaMov) || ( ($cYear-$cAnioMov)>4 ) ){
						$respuesta["opcion"]["aDatoCance"][$i]["estatus"] ="F"; 
            		}else{
            			$respuesta["opcion"]["aDatoCance"][$i]["idMov"] = $aMov[0]["idmovimiento"];
            		}
            	}else{
					$respuesta["opcion"]["aDatoCance"][$i]["estatus"] ="X"; // Importes Diferentes
            	}
        	} else {
            	// Si $aMov tiene dos elementos, agregarlos uno por uno,
            	// Es por que ya esta cancelado
            	$respuesta["ctas"][] = $aMov[0];
            	$respuesta["ctas"][] = $aMov[1];
            	if ($aMov[0]["estatus"]=="C"){
            		$respuesta["opcion"]["aDatoCance"][$i]["estatus"] ="Y"; // Movimiento ya Cancelado
            	}else{
            		$respuesta["opcion"]["aDatoCance"][$i]["estatus"] ="?"; // Por que regreso dos con la misma referencia ???
            	}
        	}
    	}else{
    		$respuesta["opcion"]["aDatoCance"][$i]["estatus"] ="N"; // No se encuentra la referencia
    	}
    	$i++;
	}
	return false;
}
// __________________________________________________________________________________
function EliminarLayOut(&$respuesta){
	$respuesta["pasos"] = "[Entro a EliminarLayOut]";
	movsEliminaCorrectos($respuesta);
	$nMovsTot   	 = count($respuesta["opcion"]["aDatosEli"]);
	$nMovsEliminados = EliminaMovsCorrectos($respuesta);

	$respuesta["mensaje"] ="Proceso concluido. Se eliminaron $nMovsEliminados de $nMovsTot.";
	if( $nMovsEliminados>0){
		$respuesta["mensaje"] .= " Se actualizó saldo";
	}
}
// __________________________________________________________________________________
function EliminaMovsCorrectos(&$respuesta){
	$respuesta["pasos"] .= "[Entro a EliminaMovsCorrectos]";
	$aMovsEli		 = $respuesta["opcion"]["aDatosEli"];
	$nMovsEliminados = 0;

	foreach($aMovsEli as $aEli){
		if ($aEli["estatus"]=="ok"){ // Movimiento correcto para eliminar
				$respuesta["opcion"]["idMovimiento"] = $aEli["idMov"];
				$respuesta["opcion"]["idImpo"]		 = $aEli["importe"];
				$respuesta["opcion"]["idFecha"]		 = date("d-m-Y", strtotime($aEli["fechamov"]));

				EliminarMovimiento($respuesta);
				if ($respuesta["success"]){
					$nMovsEliminados++;
				}
		}
	}
	return $nMovsEliminados;
}
// __________________________________________________________________________________
function movsEliminaCorrectos(&$respuesta){
	$respuesta["pasos"] .= "[Entro a movsEliminaCorrectos]";

	$aMovsEli	= $respuesta["opcion"]["aDatosEli"];
	$cCtaBan	= $respuesta["opcion"]["idCuentabancaria"];
	$cTipoMov	= $respuesta["opcion"]["idTipo"];
	$lAdmin		= $respuesta["opcion"]["esquema"]=="Administrador";
	$i			= 0;
	$cHoy		= date('Y-m-d');
	$cYear		= date("Y");
	

	foreach($aMovsEli as $aEli){
		$lOk	  = false;
		$cIdMov   = "";
		$cRefBan  = $aEli["referencia"];
		// cheques con referencia 00000
		if ( $cTipoMov=="C" && is_numeric($cRefBan) && intval($cRefBan)== 0 ){
			$impoTxt  	= trim($aEli["importe"]);
			$aMov 		= metodos::revisaReferenciaBancariaImporte($cRefBan,$cCtaBan,$impoTxt);
			$respuesta["_trace"] .= " busco importe $impoTxt";
		}else{
			$aMov = metodos::revisaReferenciaBancaria($cRefBan,$cCtaBan);
			$respuesta["_trace"] .= " busco referencia [$cRefBan][$cCtaBan]";
		}
		
		if (!empty($aMov)) {
			$fechaMov = trim($aMov[0]["fechaoperacion"]);
			$cAnioMov = substr($fechaMov,0,4);
			$impoBd   = trim($aMov[0]["importeoperacion"]);
			$impoTxt  = trim($aEli["importe"]);
        	if (count($aMov) == 1) {
        		$lOk	= false;
        		$cIdMov = $aMov[0]["idmovimiento"];
        		$respuesta["_trace"] .= "< ImpBd[$impoBd] ImpTxt[$impoTxt] >";
        		if ( $impoBd!==$impoTxt){ // Importes Diferentes
					$respuesta["opcion"]["aDatosEli"][$i]["estatus"] = "Importes diferentes";
        		}else{
	        		// Días Anteriores 
	        		if ($fechaMov<$cHoy){
	        			if ($lAdmin){
	        				if ( ($cYear-$cAnioMov)<=4 ){
	        					$lOk = true;
	        				}else{ // Años Anteriores
	        					$respuesta["opcion"]["aDatosEli"][$i]["estatus"] = "Fecha de días o años anteriores";
	        				}
	        			}else{
	        				$respuesta["error"] = "No es administrador";
	        			}
	        		}else if($fechaMov==$cHoy){
	        			$lOk = true;
	        		}
	        	}
        	} else { // Estan Cancelados
        		$respuesta["opcion"]["aDatosEli"][$i]["estatus"] = "Movimiento Cancelado";
        	}
        }else{ // No encontró la referencia
        	$respuesta["opcion"]["aDatosEli"][$i]["estatus"] = "No se encuentra la referencia $cRefBan";
        }

        if( $lOk ){
        	$respuesta["opcion"]["aDatosEli"][$i]["estatus"] = "ok";
        	$respuesta["opcion"]["aDatosEli"][$i]["idMov"]   = $cIdMov;
        	$respuesta["opcion"]["aDatosEli"][$i]["fechamov"]= $fechaMov;
        }
        $i++;
	}
}
// __________________________________________________________________________________
// __________________________________________________________________________________
// __________________________________________________________________________________
?>