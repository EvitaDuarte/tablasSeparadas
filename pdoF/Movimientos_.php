<?php
/**
* * * * * * * * * * * * * * * * * * * * * * * * * 
* Autor   : Miguel Ángel Bolaños Guillén        *
* Sistema : Sistema de Operación Bancaria Web   *
* Fecha   : Octubre 2023                        *
* Descripción : Rutinas para ejecutar codigo    * 
*               SQL para interacturar con el    *
*               buzón de la BD del Sistema      *
*               Unadm-Proyecto Terminal         *
* * * * * * * * * * * * * * * * * * * * * * * * *  
	Clase para manejar objetos para la Tabla de Movimientos
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

// El php que invoca a Movimientos.php debe llamar a rutinas.php y metodos.php
// require_once '../backF/rutinas.php';
// require_once "metodos.php";
class MovimientoBancario{
private $idmovimiento;
private $idcuentabancaria; 
private $idoperacion; 
private $idcontrol; 
private $idunidad; 
private $referenciabancaria; 
private $fechaoperacion; 
private $importeoperacion; 
private $beneficiario; 
private $concepto; 
private $anioejercicio; 
private $folio; 
private $buzon_captura; 
private $usuarioalta; 
private $estatus;
//	private $fechaalta;
//	__________________________________________________________________________________________
public function __construct($aDatos) {
	if ($aDatos==null){
		// Solo construye el objeto
	}else{
		$this->idcuentabancaria		= pg_escape_string($aDatos["idcuentabancaria"]); 
		$this->idoperacion			= pg_escape_string($aDatos["idoperacion"]); 
		$this->idcontrol			= pg_escape_string($aDatos["idcontrol"]); 
		$this->idunidad				= pg_escape_string($aDatos["idunidad"]); 
		$this->referenciabancaria	= pg_escape_string($aDatos["referenciabancaria"]); 
		$this->fechaoperacion		= pg_escape_string($aDatos["fechaoperacion"]); 
		$this->importeoperacion		= pg_escape_string($aDatos["importeoperacion"]); 
		$this->beneficiario			= pg_escape_string($aDatos["beneficiario"]); 
		$this->concepto				= pg_escape_string($aDatos["concepto"]); 
		$this->anioejercicio		= pg_escape_string($aDatos["anioejercicio"]); 
		$this->folio				= pg_escape_string($aDatos["folio"]); 
		$this->buzon_captura		= pg_escape_string($aDatos["buzon_captura"]); 
		$this->usuarioalta			= pg_escape_string($aDatos["usuarioalta"]); 
		$this->idmovimiento			= pg_escape_string($aDatos["idmovimiento"]);
		$this->estatus				= pg_escape_string($aDatos["estatus"]);
		//$this->fechaalta			= pg_escape_string($aDatos[""]);
	}
}
//	__________________________________________________________________________________________
function ActualizaMovimiento($conexion,$lAdiciona){
	//var_dump($this->fechaoperacion); // Se espera que la fecha llegue en formato d/m/Y para pasarla a Y-m-d
	if (strpos($this->fechaoperacion, '-') !== false) {
    	//echo "La fecha tiene como separador el guion '-'";
    	$fechaFormateada = DateTime::createFromFormat('d-m-Y', $this->fechaoperacion)->format('Y-m-d'); // El servidor español maneja otro formato
	}else{
		$fechaFormateada = DateTime::createFromFormat('d/m/Y', $this->fechaoperacion)->format('Y-m-d'); // El servidor español maneja otro formato
	}

	$cTabla = "atablas.t_" . trim($this->idcuentabancaria);
	
	if ( $lAdiciona){
		$sql = 	"INSERT INTO $cTabla (".
			"idcuentabancaria, idoperacion, idcontrol, idunidad, referenciabancaria, ". 
			"fechaoperacion, importeoperacion, beneficiario, concepto, anioejercicio, folio, ".
			"buzon_captura, usuarioalta, estatus) VALUES (".
 			":idcuentabancaria, :idoperacion, :idcontrol, :idunidad, :referenciabancaria,". 
			":fechaoperacion, :importeoperacion, :beneficiario, :concepto, :anioejercicio, :folio,".
			":buzon_captura, :usuarioalta, :estatus) ";
	}else{
		$sql = "UPDATE $cTabla SET ".
			   " idoperacion=:idoperacion		, idcontrol=:idcontrol				, idunidad=:idunidad			, referenciabancaria=:referenciabancaria, " .
			   " fechaoperacion=:fechaoperacion , importeoperacion=:importeoperacion, beneficiario=:beneficiario	, concepto=:concepto, " .
			   " anioejercicio=:anioejercicio	, folio=:folio 						, buzon_captura=:buzon_captura " .
			   " where idmovimiento=:idmovimiento "; 

	}
	$stmt = $conexion->prepare($sql);

	// Se protegen los parametros del SQL
	if ( $lAdiciona){
		$stmt->bindParam(':idcuentabancaria'	, $this->idcuentabancaria 	, PDO::PARAM_STR);
		$stmt->bindParam(':usuarioalta'			, $this->usuarioalta		, PDO::PARAM_STR);
		$stmt->bindParam(':estatus'				, $this->estatus			, PDO::PARAM_STR);
	}else{
		$stmt->bindParam(':idmovimiento'		, $this->idmovimiento		, PDO::PARAM_STR);
	}
	$stmt->bindParam(':idoperacion'			, $this->idoperacion		, PDO::PARAM_STR);
	$stmt->bindParam(':idcontrol'			, $this->idcontrol			, PDO::PARAM_STR);
	$stmt->bindParam(':idunidad'			, $this->idunidad			, PDO::PARAM_STR);
	$stmt->bindParam(':referenciabancaria'	, $this->referenciabancaria , PDO::PARAM_STR);
	//$stmt->bindParam(':fechaoperacion'		, $this->fechaoperacion 	, PDO::PARAM_STR);
	$stmt->bindParam(':fechaoperacion'		, $fechaFormateada			, PDO::PARAM_STR);
	$stmt->bindParam(':importeoperacion'	, $this->importeoperacion 	, PDO::PARAM_STR);
	$stmt->bindParam(':beneficiario'		, $this->beneficiario 		, PDO::PARAM_STR);
	$stmt->bindParam(':concepto'			, $this->concepto			, PDO::PARAM_STR);
	$stmt->bindParam(':anioejercicio'		, $this->anioejercicio		, PDO::PARAM_STR);
	$stmt->bindParam(':folio'				, $this->folio				, PDO::PARAM_STR);
	$stmt->bindParam(':buzon_captura'		, $this->buzon_captura		, PDO::PARAM_STR);
	
	return $stmt->execute();
}
//	__________________________________________________________________________________________
function traeMovimientoxId($conexion,$nId){
	$sql = "select idcuentabancaria, idoperacion, idcontrol, idunidad, referenciabancaria, fechaoperacion," .
		   "importeoperacion, beneficiario, concepto, anioejercicio, folio from movimientos " .
		   "where idmovimiento=$nId";
	return ejecutaSQL_($sql);
}
//  __________________________________________________________________________________________
function cancelaMovimiento($conexion,$cId){
	$sql = "update movimientos set estatus='C', beneficiario = CONCAT('** CANCELADO ** ', beneficiario), " .
		   "concepto =  CONCAT('** CANCELADO ** ', concepto) " .
		   "where idmovimiento=$cId ";
	return ejecutaSQL_($sql);
}
//  __________________________________________________________________________________________
function traeMovRefeImpo($cRefe,$cImpo){ // No se ha usado , quitar este comentario cuando se use
	$cRefe = pg_escape_string($cRefe);
	$cImpo = pg_escape_string($cImpo);
	$sql   = "select idmovimiento from movimientos where referenciabancaria='$cRefe' and importeoperacion=$cImpo";

	return ejecutaSQL_($sql);
}
//  __________________________________________________________________________________________
//  __________________________________________________________________________________________
//  __________________________________________________________________________________________	
}

?>