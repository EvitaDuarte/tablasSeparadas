<?php
/**
	Clase para manejar objetos para la Cuenta Bancaria
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../backF/rutinas_.php';
require_once("../backF/con_pg_OpeFinW1_.php");
class cuentaBancaria{
	private $idCuentaBancaria;
	private $nombre;
	private $siglas;
	private $estatus;
	private $consecutivo;
	private $usuarioAlta;
	private $fechaAlta;
	//
	const TABLA = 'cuentasbancarias';

	public function __construct($aDatos,$usuarioAlta) {
		if ( $aDatos==null && $usuarioAlta==null){
		}else{
			$this->idCuentaBancaria = pg_escape_string($aDatos["idCuentaBancaria"]);
			$this->nombre 			= pg_escape_string($aDatos["nombre"]);
			$this->siglas 			= pg_escape_string($aDatos["siglas"]);
			$this->estatus 			= $aDatos["estatus"];
			$this->consecutivo		= $aDatos["consecutivo"];
			$this->usuarioAlta		= $usuarioAlta;
			$this->estatus 			= ($this->estatus=="on")?"true":"false";
		}
	}
	// -------------------------------
 	public function get_idCuentaBancaria(){
		return $this->idCuentaBancaria;
	}
 	public function get_nombre(){
		return $this->nombre;
	}
 	public function get_siglas(){
		return $this->siglas;
	}
 	public function get_estatus(){
		return $this->estatus;
	}
 	public function get_consecutivo(){
		return $this->consecutivo;
	}
 	public function get_usuarioAlta(){
		return $this->usuarioAlta;
	}
	// ---------------------------------
 	public function set_idCuentaBancaria($idCuentaBancaria){
		$this->idCuentaBancaria = $idCuentaBancaria;
	}
 	public function set_nombre($nombre){
		$this->nombre = $nombre;
	}
 	public function set_siglas($siglas){
		$this->siglas = $siglas;
	}
 	public function set_estatus($estatus){
		$this->estatus = ($estatus=="on")?"true":"false";
	}
 	public function set_consecutivo($consecutivo){
		$this->consecutivo = $consecutivo;
	}
 	public function set_usuarioAlta($usuarioAlta){
		$this->usuarioAlta = $usuarioAlta;
	}
//  ____________________________________________________________________________________________________
public function guardar($conexion,$operacion,&$respuesta){
	$stmt = null;
	// ----------------
	if($operacion=="Modificar") /*Modifica*/ {
		$stmt = $conexion->prepare(
					'UPDATE ' . self::TABLA .' SET nombre = :nombre, siglas = :siglas, estatus = :estatus '.
				   ' WHERE idcuentabancaria = :idCuentaBancaria');

	}elseif ($operacion=="Adicionar") {
		$stmt = $conexion->prepare('INSERT INTO cuentasbancarias '.
				   ' (idcuentabancaria, nombre, siglas, estatus, consecutivo, usuarioalta ) ' .
				   ' VALUES(:idCuentaBancaria, :nombre, :siglas, :estatus, :consecutivo, :usuarioalta)');
		$stmt->bindParam(':consecutivo', $this->consecutivo	, PDO::PARAM_INT);
		$stmt->bindParam(':usuarioalta', $this->usuarioAlta , PDO::PARAM_STR);
	}
	
	$stmt->bindParam(':idCuentaBancaria'	, $this->idCuentaBancaria , PDO::PARAM_STR);
	$stmt->bindParam(':nombre'				, $this->nombre 		  , PDO::PARAM_STR);
	$stmt->bindParam(':siglas'				, $this->siglas 		  , PDO::PARAM_STR);
	$stmt->bindParam(':estatus'				, $this->estatus	  	  , PDO::PARAM_INT);
	// ------------
	$respuesta["depura"] = $stmt->execute();
	$respuesta["objeto"] = array($this->idCuentaBancaria,$this->nombre,$this->siglas,$this->estatus,$this->consecutivo,$this->usuarioAlta);
}
//  ____________________________________________________________________________________________________
public function ExisteCuentaBancaria($conexion,&$respuesta,$operacion){
	$vId = pg_escape_string($this->idCuentaBancaria);
	$sql = "Select idcuentabancaria as salida from cuentasbancarias where idcuentabancaria='$vId'";
	$res = getcampo($sql);
	if ($res==""){ // No existe el usuario
		$respuesta["mensaje"] = "No existe la Cuenta Bancaria a $operacion con ID[$vId]";
		return false;
	}
	$respuesta["mensaje"] = "Ya existe la cuenta Bancaria solicitada";
	$respuesta["depura"]  = "Cuenta Bancaria Existe";
	return true;
}
//  ____________________________________________________________________________________________________
public function EliminarCuentaBancaria($conexion,$operacion,&$respuesta){
	$vId  = pg_escape_string($this->idCuentaBancaria);
	$stmt = $conexion->prepare("delete from cuentasbancarias where idcuentabancaria='$vId'");
	//
	$respuesta["depura"] = $stmt->execute();
	return $respuesta["depura"];
}
//  ____________________________________________________________________________________________________
public function filtraCtasBancarias($idUsuario,$cRol){
	$idUsuario	= pg_escape_string($idUsuario); // Protege inyección de código
	$regreso	= null;
	if ($cRol=="Administrador"){
		$sql = "select a.idcuentabancaria,a.nombre,a.siglas,a.estatus from cuentasbancarias a order by a.idcuentabancaria";
	}else{
		$sql =	"select a.idcuentabancaria,a.nombre,a.siglas,a.estatus from cuentasbancarias a, accesos b " .
		   		"where a.idcuentabancaria=b.idcuentabancaria and b.idUsuario='$idUsuario' order by a.idcuentabancaria";
   	}
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$regreso[] 	 = " ,Seleccione"; // Valor nulo
		foreach ($res as $r ){	// llena el combo con la clave y nombre de la operación bancaria
			$regreso[] = $r["idcuentabancaria"]. "|" . $r["siglas"] . "|" . $r["estatus"] . "|" . $r["nombre"] . 
			"," . $r["idcuentabancaria"]."_".$r["nombre"];
		}
	}
	return $regreso;
}
//  ____________________________________________________________________________________________________
public function filtraCtasBancariasConciliadas($idUsuario,$cRol){
	$idUsuario	= pg_escape_string($idUsuario); // Protege inyección de código
	$regreso	= null;
	if ($cRol=="Administrador"){
		$sql = "select a.idcuentabancaria,a.nombre,a.idbanco from cuentasbancarias a where a.se_concilia=true order by a.idcuentabancaria";
	}else{
		$sql =	"select a.idcuentabancaria,a.nombre,a.idbanco from cuentasbancarias a, accesos b " .
		   		"where a.idcuentabancaria=b.idcuentabancaria and b.idUsuario='$idUsuario' and a.se_concilia=true order by a.idcuentabancaria";
   	}
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$regreso[] 	 = " ,Seleccione"; // Valor nulo
		foreach ($res as $r ){	// llena el combo con la clave y nombre de la operación bancaria
			$regreso[] = $r["idcuentabancaria"]. "|" . $r["idbanco"] . "|" . $r["nombre"] . 
			"," . $r["idcuentabancaria"]."_".$r["nombre"];
		}
	}
	return $regreso;
}
//  ____________________________________________________________________________________________________
public function notieneAccesos(&$respuesta){
	$vId = pg_escape_string($this->idCuentaBancaria);
	$sql = "Select idcuentabancaria as salida from accesos where idcuentabancaria='$vId'";
	$res = getcampo($sql);
	if ($res==""){ // No existe la cuenta en accesos
		return true;
	}
	$respuesta["mensaje"] = "La cuenta Bancaria tiene accesos";
	$respuesta["depura"]  = "Cuenta Bancaria Existe";
	return false;
}
//  ____________________________________________________________________________________________________
public function noTieneMovimientosBancarios(){
	$vId = pg_escape_string($this->idCuentaBancaria); // Evita cualquier inyección de código
	$sql = "select idcuentabancaria as salida from movimientos where idcuentabancaria='$vId'";
	$res = getcampo($sql);
	return ($res=="");
}
//  ____________________________________________________________________________________________________
}
?>