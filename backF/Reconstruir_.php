<?php
/*
* * * * * * * * * * * * * * * * * * * * * * * * * 
* Autor   : Miguel Ángel Bolaños Guillén        *
* Sistema : Sistema de Operación Bancaria Web   *
* Fecha   : Abril  2024                         *
* Descripción : Rutinas para ejecutar codigo    * 
*               SQL para interacturar con los   *
*               Saldos y movimientos de la BD   *
*               del Sistema.                    *
*               Unadm-Proyecto Terminal         *
* * * * * * * * * * * * * * * * * * * * * * * * *  */

	global $conn_pdo;
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	//
	require_once "rutinas_.php";					// Métodos estáticos
    require_once "../pdoF/Saldos_.php";

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
	// _________________________________________
	$respuesta = array(	'success'=>false , 'mensaje'=>""	 , 'resultados'=>array(), 'opcion'=>array() , 'ctas'=>array(),
						'urs'=>array()   , 'opera'=>array()  , 'ctrl'=>array()      , 'datos'=>array()  , 'tipoMov'=>"",
						'_trace'=>""	 , 'depura'=>array() );
	// Lee el cuerpo de la solicitud HTTP
	$jsonData = file_get_contents('php://input');
	// Decodifica los datos JSON en un array asociativo
	$aParametros 		 = json_decode($jsonData, true);
	$vOpc 				 = $aParametros["opcion"]; 
	$respuesta["opcion"] = $aParametros; // Debe de ir para que se identifique en el regreso del PHP al JS
	$respuesta["datos"]  = array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);
//	_____________________________________________________________________________________________
	$respuesta["datos"]["opcion"] = $vOpc;

	switch ($vOpc) {
		// ___________________________
		case "ReconstruirSaldos":
			
			ReconstruirSaldos($respuesta);
		break;
		// ___________________________
	}	

	/*{
  "opcion": "ReconstruirSaldos",
  "OpcCta": "Rango",
  "CtaIni": "7726413-4",
  "CtaFin": "7726413-4",
  "OpcPer": "Periodo",
  "fecha": "2023-01-01"
}*/

	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
	return;

// ________________________________________________________
function ReconstruirSaldos(&$respuesta){
	global $conn_pdo;
	$lRoll       = false;
	$cCtaIni	 = $respuesta["opcion"]["CtaIni"];
	$cCtaFin	 = $respuesta["opcion"]["CtaFin"];
	$cFechaIni	 = $respuesta["opcion"]["fecha"];

	$aCtas = arregloCtasBancarias($cCtaIni,$cCtaFin);

	try{
		$conn_pdo->beginTransaction();

		foreach($aCtas as $cta){
			$cCta = $cta["idcuentabancaria"];
			if ( limpiaImportes($cCta,$cFechaIni,$respuesta) ){
				if ( !(acumulaImportes($cCta,$cFechaIni,$respuesta)) ) {
					$respuesta["mensaje"]  .= "No se reconstruyeron los saldos. Intente de nuevo";
					$conn_pdo->rollBack();
					return false;
				}
			}else{
				$conn_pdo->rollBack();
				return false;
			}
		}
		$conn_pdo->commit();
		$respuesta["mensaje"] = "El proceso de reconstrucción, terminó de manera exitosa";
		$respuesta["success"] = true;
		return true;
	}catch(Exception $e){
		$respuesta["mensaje"] .= "Ocurrió una excepción " . $e->getMessage();
		$conn_pdo->rollBack();
		return false;
	}

}
// ________________________________________________________

// ________________________________________________________
function limpiaImportes($cCta,$cFechaIni,&$respuesta){
	// No se puede usar update ya que al acumular importes puede habaer días que no haya ingresos,
	// ni egresos ni cheques y no traspasaría el saldo correctamente 
	// $sql  =	"UPDATE saldos set ingresos = 0.00 , egresos = 0.00, cheques=0.00, saldoinicial=0.00 ".
	//		    " where idcuentabancaria='$cCta'  and fechasaldo>='$cFechaIni' ";
	$sql  = "delete from saldos where idcuentabancaria='$cCta'  and fechasaldo>='$cFechaIni' ";
			//var_dump($cCta);var_dump($cFechaIni);
	try{ 
		$nRen = actualizaSql($sql);
		//$sql  = "select count(ingresos) from saldos where idcuentabancaria='$cCta'  and fechasaldo>='$cFechaIni' ";
		//$nRen = contar($sql);
		$respuesta["opera"][$cCta] 		= $nRen;
		$respuesta["opera"]["limpia"] 	= $sql;
	}catch(Exception $e){
		$respuesta["mensaje"] = "Ocurrió una excepción en limpiaImportes" . $e->getMessage();
		return false;
	}
	return true;
}
// ________________________________________________________
function acumulaImportes($cCta,$cFechaIni,&$respuesta){
	global $conn_pdo;
	ini_set('memory_limit', '2048M');
	// Se debe traer los datos por bloques para que no marque error de memoria
	$limite = 2000; 	$offset = 0;
	$lUna	= true; 	$lEntro = false;
	$cTabla = "atablas.t_" . trim($cCta);
	try{
		$i=0;
		while(true){
			// Acumula Ingreos, Egresos, Cheques desde tabla de movimientos pod cuenta y fecha y tipo (IEC)
			$sql =	"select a.idcuentabancaria , a.fechaoperacion  ,b.tipo ,  sum(a.importeoperacion) as suma " .
					" from $cTabla a , operacionesbancarias b " .
					" where a.idoperacion=b.idoperacion " .
					" and a.idcuentabancaria='$cCta' " .
					" and a.fechaoperacion >='$cFechaIni' " .
					" group by a.idcuentabancaria, a.fechaoperacion, b.tipo " .
					" order by a.idcuentabancaria,a.fechaoperacion,b.tipo " .
					" LIMIT $limite OFFSET $offset"; // debe de llevar todo a. , b. por que si no no acumula bien
			$aMovs = ejecutaSQL_($sql);
			$respuesta["ctrl"][$cCta] = $sql;

			if (empty($aMovs) ){ // || $i==10
				break;
			}

			foreach ($aMovs as $mov){
				$nIng = 0.00; $nEgr = 0.00 ; $nChe = 0.00;
				$fechaAct = ddmmyyyy($mov["fechaoperacion"]);
				$tipoMov  = $mov["tipo"];
				switch ($tipoMov) {
					case 'I':
						$nIng = $mov["suma"];
					break;
					case 'E':
						$nEgr = $mov["suma"];
					break;
					case 'C':
						$nChe = $mov["suma"];
					break;
				}
				$aDatos = [ "idCuentaBancaria"=>$cCta,"SaldoInicial"=>0.00,"fechaSaldo"=>$fechaAct,"tipo"=>$tipoMov,
							"Ingresos"=>$nIng,"Egresos"=>$nEgr,"Cheques"=>$nChe,"Conexion"=>$conn_pdo];

				$oSaldo = new Saldos($aDatos,$conn_pdo);
/*				if ( $cCta=='3854775' && $mov["fechaoperacion"]=='2017-02-15' ){ No estaba acumulando bu¿ien en esta fecha para esta cuenta
					$respuesta["depura"][] = $mov; 								 Se soluciono colocando a. b. en todo el sql
				} */
				if ( !($oSaldo->AdicionaSaldo($respuesta) ) ){
					return false;
				}
			}
            // Incrementar el offset para obtener el siguiente lote de registros
            $offset += $limite;
            $i++;
		}
	}catch(Exception $e){
		$respuesta["mensaje"] = "Ocurrió una excepción en acumImp" . $e->getMessage();
		return false;
	}
	return true;
}
// ______________________________________________________________
// _______________________________________________________________

?>
