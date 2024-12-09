<?php

/**
	Clase para manejar objetos para la Cuenta Bancaria
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_errors', 'On');

require_once '../backF/rutinas_.php';

class Saldos{
	private $idCuentaBancaria;
	private $fechaSaldo;
	private $SaldoInicial;
	private $tipo;
	private $Ingresos;
	private $Egresos;
	private $Cheques;
	private $conexion;
// ______________________________________________________________________________________________________
	// Constructor
	public function __construct($aDatos,$conexion) {
		if ($aDatos==null ){
			// Solo construye el objeto
			$this->conexion = $conexion;
		}else{
			$this->idCuentaBancaria = pg_escape_string($aDatos["idCuentaBancaria"]);
	       	$this->SaldoInicial 	= pg_escape_string($aDatos["SaldoInicial"]);
			$this->fechaSaldo		= pg_escape_string($aDatos["fechaSaldo"]);
			$this->tipo 			= pg_escape_string($aDatos["tipo"]);
	       	$this->Ingresos 		= pg_escape_string($aDatos["Ingresos"]);
	    	$this->Egresos			= pg_escape_string($aDatos["Egresos"]);
	    	$this->Cheques			= pg_escape_string($aDatos["Cheques"]);
	    	$this->conexion			= $aDatos["Conexion"];
    	}
	}
// ______________________________________________________________________________________________________
	// ---- Set y Get ----
 	public function get_idCuentaBancaria(){
		return $this->idCuentaBancaria;
	}
 	public function set_idCuentaBancaria($idCuentaBancaria){
		$this->idCuentaBancaria = $idCuentaBancaria;
	}
 	public function get_SaldoInicial(){
		return $this->SaldoInicial;
	}
 	public function set_SaldoInicial($SaldoInicial){
		$this->SaldoInicial = $SaldoInicial;
	}
	 public function get_fechaSaldo(){
		return $this->fechaSaldo;
	}
 	public function set_fechaSaldo($fechaSaldo){
		$this->fechaSaldo = $fechaSaldo;
	}
 	public function get_tipo(){
		return $this->tipo;
	}
 	public function set_tipo($tipo){
		$this->tipo = $tipo;
	}
 	public function get_Ingresos(){
		return $this->Ingresos;
	}
 	public function set_Ingresos($Ingresos){
		$this->Ingresos = $Ingresos;
	}
 	public function get_Egresos(){
		return $this->Egresos;
	}
 	public function set_Egresos($Egresos){
		$this->Egresos = $Egresos;
	}
 	public function get_Cheques(){
		return $this->Cheques;
	}
 	public function set_Cheques($Cheques){
		$this->Cheques = $Cheques;
	}
 	public function get_conexion(){
		return $this->conexion;
	}
 	public function set_conexion($conexion){
		$this->conexion = $conexion;
	}
// ______________________________________________________________________________________________________
	// ----------- CRUD --------------------
	public function adicionaSaldo(&$respuesta){
		$stmt 			= null;
		$saldoAnterior  = 0.00;
		$cCtaBan		= $this->idCuentaBancaria; $cFecSaldo = $this->fechaSaldo;
		// _______________
		//var_dump($cFecSaldo);
		if (strpos($cFecSaldo, '-') !== false) {
	    	//echo "La fecha tiene como separador el guion '-'";
	    	$cFecSaldo = DateTime::createFromFormat('d-m-Y', $cFecSaldo)->format('Y-m-d'); // El servidor español maneja otro formato
		}else{
			$cFecSaldo = DateTime::createFromFormat('d/m/Y', $cFecSaldo)->format('Y-m-d'); // El servidor español maneja otro formato
		}

		// $cFecSaldo = DateTime::createFromFormat('d/m/Y', $cFecSaldo)->format('Y-m-d');
		// --------------- MODIFICAR
		if ($this->ExisteSaldo($cCtaBan,$cFecSaldo,$respuesta )){ // Modifica
			$stmt = $this->conexion->prepare(
					"UPDATE saldos set  ingresos = ingresos + :ingresos, " .
					"egresos = egresos + :egresos, cheques = cheques + :cheques " .
					" where idcuentabancaria=:idcuentabancaria and fechasaldo=:fechasaldo" );
		}else{ // --------------- ADICIONAR
			$saldoAnterior = $this->traeSaldoAnterior($cCtaBan,$cFecSaldo,false,$respuesta);
			$stmt 		   = $this->conexion->prepare("INSERT into saldos( ".
							 "idcuentabancaria ,  fechasaldo,  saldoinicial,  ingresos,  egresos,  cheques ) values (".
							 ":idcuentabancaria, :fechasaldo, :saldoinicial, :ingresos, :egresos, :cheques )");
			// No se debe poner fuera por que en el UP-Date no se esta definiendo
			$stmt->bindParam(':saldoinicial'		, $saldoAnterior 	  		, PDO::PARAM_STR);
		}
		$stmt->bindParam(':idcuentabancaria'	, $cCtaBan 			, PDO::PARAM_STR);
		$stmt->bindParam(':fechasaldo'			, $cFecSaldo 		, PDO::PARAM_STR);
		$stmt->bindParam(':ingresos'			, $this->Ingresos 	, PDO::PARAM_STR);
		$stmt->bindParam(':egresos'				, $this->Egresos 	, PDO::PARAM_STR);
		$stmt->bindParam(':cheques'				, $this->Cheques 	, PDO::PARAM_STR);

		// ------------
		if ( $stmt->execute() ){
			// para efectos de depuración
			$respuesta["objeto"] = array($cCtaBan,$this->SaldoInicial,$this->Ingresos,$this->Egresos,$this->Cheques,date('Y-m-d'),$cFecSaldo);
			//
			$nMonto 			 = $this->verificaImportes();
			if ( $cFecSaldo < date('Y-m-d') ){ // Si es de fechas anteriores
				$respuesta["_trace"] = " Llama actualizar saldos desde ";
				$this->actualizaSaldoDesde($cFecSaldo,$nMonto,$respuesta);
			}
			return true;
		}else{
			$respuesta["_trace"] =  "No logró ejecurar insert/update del saldo $cCtaBan - $cFecSaldo - $saldoAnterior - $this->Ingresos \n";
		}
		return false;
	}
// ______________________________________________________________________________________________________
	public function actualizaSaldoDesde($vFecha,$nMonto,&$respuesta){
		$vCta 	= pg_escape_string($this->idCuentaBancaria);
		$vFecha = pg_escape_string($vFecha);
		$sql 	= "select idcuentabancaria,fechasaldo,saldoinicial from saldos " . 
				  "where idcuentabancaria='$vCta' and fechasaldo > '$vFecha' " .
				  "order by fechasaldo asc";

		//$respuesta["update"] = "{" . $sql . "}<br>";
		$Saldos	= ejecutaSQL_C($sql,$this->conexion);
		if ($Saldos !=null ){
			foreach($Saldos as $s){
				$fecSal = $s["fechasaldo"];
				$nSaldo = $s["saldoinicial"] + $nMonto;
				$sql    = "update saldos set saldoinicial=$nSaldo where idcuentabancaria='$vCta' and fechasaldo='$fecSal'";
				$res    = ejecutaSQL_C($sql,$this->conexion);
				//$respuesta["update"] = $respuesta["update"] . "[" . $sql . "]<br>";
			}
		}
	}	
// ______________________________________________________________________________________________________	
	public function ExisteSaldo($CtaBancaria,$fechaSaldo,&$respuesta){
		$stmt = null;
		$stmt = $this->conexion->prepare( "select saldoinicial from saldos " .
										  " where idcuentabancaria=:idcuentabancaria and " .
										  " fechasaldo=:fechasaldo" );
		// $fechaSaldo Formateada = DateTime::create FromFormat('d/m/Y', $this->fechaSaldo)->format('Y-m-d');
		$stmt->bindParam(':idcuentabancaria'	, $CtaBancaria	, PDO::PARAM_STR);
		$stmt->bindParam(':fechasaldo'			, $fechaSaldo	, PDO::PARAM_STR);
		if ( $stmt->execute() ) {// Solo verifica si ejecuto el select
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
	    	// Verificar si se encontraron resultados
			$lEncontro = count($res) > 0 ;
			if ($lEncontro){
				$respuesta["_trace"] =  "Existe Saldo para $CtaBancaria - $fechaSaldo  ";	
			}else{
				$respuesta["_trace"] =  "count Ejecuto select pero no existe Saldo para $CtaBancaria - $fechaSaldo \n";
			}
			return ( $lEncontro );
		}else{
			$respuesta["_trace"] = "execute No existe Saldo para $CtaBancaria - $fechaSaldo  \n";
			return false;
		}
	}
// ______________________________________________________________________________________________________
	public function traeSaldoAnterior($ctaBan,$fecha,$lIgual, &$respuesta){
		$lEjecuto = false;
		$nSaldo   = "0.00";
		try {
			$sql  = "select saldoinicial+ingresos-egresos-cheques as saldofinal from saldos where " .
					"idcuentabancaria=:idcuentabancaria and " .
					"  fechasaldo <" . ( ($lIgual)?"=":"" ) . ":fechasaldo order by fechasaldo desc limit 1 ";
					// Busco el saldo de la fecha anterior
			$stmt = $this->conexion->prepare($sql);
			$stmt->bindParam(':idcuentabancaria', $ctaBan	, PDO::PARAM_STR);
			$stmt->bindParam(':fechasaldo'		, $fecha	, PDO::PARAM_STR);
			$lEjecuto =  $stmt->execute();
			if ( $lEjecuto){
				// Se obtiene el resultado del SQL
				$resultado = $stmt->fetch(PDO::FETCH_ASSOC); 
				if ($resultado !== false ){
					if (count($resultado)>0){
						$respuesta["_trace"]	   = "{" . json_encode($resultado) . "}";
						if (isset($resultado["saldofinal"])){
							$nSaldo = $resultado["saldofinal"]; // Esta como un String
						}else{// Este caso no se puede dar según pruebas de caja blanca
							$nSaldo = "0.00";
							$resultado = [];
							$respuesta["resultados"][]	= "*** NO debe darse El saldo Anterior es [" . $nSaldo ."]";
							$respuesta["_trace"]	    = "*** No debe darse Si encontro Saldo Anterior [". $nSaldo ."]Resultado[". count($resultado) ."] \n";
						}
					}
				}else{
					$respuesta["resultados"][]	= "No se encontrón saldo anterior $fecha [".strval($resultado)."] [Saldo = $nSaldo]\n";
					$nSaldo = "0.00";
					$resultado = [];
				}
			}else{
				$respuesta["_trace"]	=  "execute No encontro Saldo Anterior [".strval($lEjecuto)."][ Saldo = $nSaldo] \n";
			}
		} catch (Exception $e) {
			$respuesta["mensaje"] = "Excepción en la base de datos " . $e->getMessage() . "\n";
		}

		return $nSaldo;
	}
// ______________________________________________________________________________________________________
	public function verificaImportes(){
		$nImpo = 0.00;
		if ($this->tipo=="I"){
			$nImpo = $this->Ingresos;					// Incrementa Saldo
		}else{
			$nImpo = -($this->Egresos+$this->Cheques);	// Resta al Saldo
		}
		return $nImpo;
	}	
// ______________________________________________________________________________________________________
}