<?php
/**
	Clase para manejar objetos para la Cuenta Bancaria
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../backF/rutinas_.php';
class operacionBancaria{
	private $idOperacion;
	private $nombre;
	private $tipo;
	private $operador;
	private $idOperCan;
	private $visualizar;
	private $usuarioAlta;
	private $fechaalta;

	public function __construct($aDatos,$usuarioAlta) {
		if ($aDatos==null && $usuarioAlta==null){
			// Solo construye el objeto
		}else{
	       	$this->idOperacion 	= pg_escape_string($aDatos["idOperacion"]);
	       	$this->nombre 		= pg_escape_string($aDatos["nombre"]);
	       	$this->tipo 		= pg_escape_string($aDatos["tipo"]);
	       	$this->operador 	= $aDatos["operador"];
	       	$this->idOperCan	= $aDatos["idOperCan"];
	      	$this->visualizar 	= $aDatos["visualizar"];
	    	$this->usuarioAlta	= $usuarioAlta;
    	}
	}
	// ------------ GET y SET -------------------
 	public function get_idOperacion(){
		return $this->idOperacion;
	}
 	public function set_idOperacion($idOperacion){
		$this->idOperacion = $idOperacion;
	}
 	public function get_nombre(){
		return $this->nombre;
	}
 	public function set_nombre($nombre){
		$this->nombre = $nombre;
	}
 	public function get_tipo(){
		return $this->tipo;
	}
 	public function set_tipo($tipo){
		$this->tipo = $tipo;
	}
 	public function get_operador(){
		return $this->operador;
	}
 	public function set_operador($operador){
		$this->operador = $operador;
	}
 	public function get_idOperCan(){
		return $this->idOperCan;
	}
 	public function set_idOperCan($idOperCan){
		$this->idOperCan = $idOperCan;
	}
 	public function get_visualizar(){
		return $this->visualizar;
	}
 	public function set_visualizar($visualizar){
		$this->visualizar = $visualizar;
	}
 	public function get_usuarioAlta(){
		return $this->usuarioAlta;
	}
 	public function set_usuarioAlta($usuarioAlta){
		$this->usuarioAlta = $usuarioAlta;
	}

//  ____________________________________________________________________________________________________ Métodos Auxiliares
public function ExisteOperacion($conexion,&$respuesta,$operacion){
	$vId1 = pg_escape_string(trim($this->idOperacion));
	$sql = "Select idoperacion as salida from operacionesbancarias where idoperacion='$vId1'  ";
	$res = getcampo($sql);
	if ($res==""){ // No existe el acceso
		$respuesta["mensaje"] = "No existe la operación a $operacion en el catálogo";
		return false;
	}
	$respuesta["mensaje"] = "Ya existe la operación solicitada";
	$respuesta["depura"]  = "Operación Existe";
	return true;
}
//  ____________________________________________________________________________________________________
public function consultaCatalogo($conexion,&$respuesta,$condicion){
	$sql  = "select idoperacion, nombre, tipo, operador, idopercan, visualizar, usuarioalta, fechaalta " .
			" from operacionesbancarias " . $condicion . " order by tipo,idoperacion";
	$stmt = $conexion->prepare($sql);
	//
	$respuesta["depura"] 	 = $stmt->execute();
	if ($respuesta["depura"]){
		$respuesta["resultados"] = $stmt->fetchAll();
		return true;
	}else{
		$respuesta["resultados"] = array();
		return false;
	}

}
//  ____________________________________________________________________________________________________CRUD 
public function adicionaOperacion($conexion,$operacion,&$respuesta){
	$stmt = null;
	// ----------------
	if($operacion=="Modificar") /*Modifica*/ {
		$stmt = $conexion->prepare("UPDATE operacionesbancarias ".
				"set idoperacion=:idoperacion, nombre=:nombre, tipo=:tipo, operador=:operador, " .
				"idopercan=:idopercan, visualizar=:visualizar " .
				"where idoperacion=:idoperacion");
	}elseif ($operacion=="Adicionar") {
		$stmt = $conexion->prepare(
				"INSERT into operacionesbancarias( ".
				"idoperacion, nombre, tipo, operador, idopercan, visualizar, usuarioalta) " .
				"VALUES (:idoperacion, :nombre, :tipo, :operador, :idopercan, :visualizar, :usuarioalta)" );

		$stmt->bindParam(':usuarioalta', $this->usuarioAlta , PDO::PARAM_STR);
	}
	
	$stmt->bindParam(':idoperacion'	, $this->idOperacion 	, PDO::PARAM_STR);
	$stmt->bindParam(':nombre'		, $this->nombre 	  	, PDO::PARAM_STR);
	$stmt->bindParam(':tipo'		, $this->tipo 	  		, PDO::PARAM_STR);
	$stmt->bindParam(':operador'	, $this->operador 		, PDO::PARAM_STR);
	$stmt->bindParam(':idopercan'	, $this->idOperCan 		, PDO::PARAM_STR);
	$stmt->bindParam(':visualizar'	, $this->visualizar 	, PDO::PARAM_INT);

	// ------------
	$respuesta["depura"] = $stmt->execute();
	$respuesta["objeto"] = array($this->idOperacion,$this->nombre,$this->tipo,$this->operador,$this->idOperCan,$this->visualizar,$this->usuarioAlta);
	return $respuesta["depura"];
}
//  ____________________________________________________________________________________________________
public function eliminaOperacion($conexion,$operacion,&$respuesta){
	$vId1  = pg_escape_string($this->idOperacion);
	$stmt  = $conexion->prepare("DELETE from operacionesbancarias where idoperacion='$vId1' ");
	//
	$respuesta["depura"] = $stmt->execute();
	return $respuesta["depura"];
}
//  ____________________________________________________________________________________________________
public function filtraOperaciones($tipo){
	$sql = "select idoperacion, nombre from operacionesbancarias where tipo='$tipo' and visualizar order by nombre ";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$regreso[] 	 = " ,Seleccione"; // Valor nulo
		foreach ($res as $r ){	// llena el combo con la clave y nombre de la operación bancaria
			$regreso[] = $r["idoperacion"] . "," . $r["idoperacion"]. "-" .$r["nombre"];
		}
		return $regreso;
	}
	return null;
}
//  ____________________________________________________________________________________________________
function noTieneControles(){
	$vId = pg_escape_string($this->idOperacion);
	$sql = "Select idoperacion as salida from controlesbancarios where idoperacion='$vId'";
	$res = getcampo($sql);
	if ($res==""){ // No existe la operacion en controles bancarios
		return true;
	}
	return false;
}
//  ____________________________________________________________________________________________________
//  ____________________________________________________________________________________________________
//  ____________________________________________________________________________________________________
}

?>