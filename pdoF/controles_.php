<?php

/**
	Clase para manejar objetos para la Cuenta Bancaria
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../backF/rutinas_.php';

class controlBancario{
	private $idControl;			// Id Control
	private $idOperacion;		// Id Operación Bancaria ( Mov Bancario )
	private $nombre;			// Nombre del Control
	private $usuarioAlta;
	private $fechaalta;

//	Constructor 
	public function __construct($aDatos,$usuarioAlta) {
		if ($aDatos==null && $usuarioAlta==null){
			// Solo construye el objeto
		}else{
	       	$this->idControl 	= pg_escape_string($aDatos["idControl"]);
			$this->idOperacion	= pg_escape_string($aDatos["idOperacion"]);
	       	$this->nombre 		= pg_escape_string($aDatos["nombre"]);
	    	$this->usuarioAlta	= $usuarioAlta;
    	}
	}

// ------------ GET y SET -------------------
 	public function get_idControl(){
		return $this->idControl;
	}
 	public function set_idControl($idControl){
		$this->idControl = $idControl;
	}
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
 	public function get_usuarioAlta(){
		return $this->usuarioAlta;
	}
 	public function set_usuarioAlta($usuarioAlta){
		$this->usuarioAlta = $usuarioAlta;
	}


//  ____________________________________________________________________________________________________ Métodos Auxiliares
public function ExisteControl($conexion,&$respuesta,$operacion){
	$vId1 = pg_escape_string(trim($this->idControl));
	$vId2 = pg_escape_string(trim($this->idOperacion));
	$sql  = "Select idcontrol as salida from controlesbancarios where idcontrol='$vId1' and idoperacion='$vId2' ";
	$res  = getcampo($sql);
	if ($res==""){ // No existe el acceso
		$respuesta["mensaje"] = "No existe el control-operación a $operacion en el catálogo";
		return false;
	}
	$respuesta["mensaje"] = "Ya existe el control-operación solicitado";
	$respuesta["depura"]  = "Control-Operación Existe";
	return true;
}
//  ____________________________________________________________________________________________________
public function consultaCatalogo($conexion,&$respuesta,$condicion){
	$sql  = "select a.idcontrol, a.idoperacion, a.nombre,  b.tipo, a.usuarioalta, a.fechaalta ".
			"from controlesbancarios a, operacionesbancarias b where a.idoperacion=b.idoperacion " .
			$condicion . " order by b.tipo,idcontrol";

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
//  ____________________________________________________________________________________________________ CRUD
public function adicionaControl($conexion,$operacion,&$respuesta){
	$stmt = null;
	// ----------------
	if($operacion=="Modificar") /*Modifica*/ {
		$stmt = $conexion->prepare("UPDATE controlesbancarios ".
				"set idcontrol=:idcontrol, nombre=:nombre, idoperacion=:idoperacion " .
				"where idcontrol=:idcontrol");
	}elseif ($operacion=="Adicionar") {

		$stmt = $conexion->prepare(
				"INSERT into controlesbancarios( ".
				"idcontrol, nombre, idoperacion, usuarioalta) " .
				"VALUES (:idcontrol, :nombre, :idoperacion, :usuarioalta)" );

		$stmt->bindParam(':usuarioalta', $this->usuarioAlta , PDO::PARAM_STR);
	}
	
	$stmt->bindParam(':idcontrol'	, $this->idControl 		, PDO::PARAM_STR);
	$stmt->bindParam(':idoperacion'	, $this->idOperacion 	, PDO::PARAM_STR);
	$stmt->bindParam(':nombre'		, $this->nombre 	  	, PDO::PARAM_STR);

	// ------------
	$respuesta["depura"] = $stmt->execute();
	$respuesta["objeto"] = array($this->idControl,$this->nombre,$this->idOperacion,$this->usuarioAlta);
	return $respuesta["depura"];
}
//  ____________________________________________________________________________________________________
public function eliminaControl($conexion,$operacion,&$respuesta){
	$vId1  = pg_escape_string($this->idControl);
	$vId2  = pg_escape_string(trim($this->idOperacion));
	$stmt  = $conexion->prepare("DELETE from controlesbancarios where idcontrol='$vId1' and idoperacion='$vId2' ");
	//
	$respuesta["depura"] = $stmt->execute();
	return $respuesta["depura"];
}
//  ____________________________________________________________________________________________________
public function filtraControles($tipo){
	$sql = "select a.idoperacion, a.idcontrol, a.nombre from controlesbancarios a , operacionesbancarias b " .
		   "where b.tipo='$tipo' and a.idoperacion=b.idoperacion order by idoperacion,nombre";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$regreso[] 	 = " ,Seleccione"; // Valor nulo
		foreach ($res as $r ){	// llena el combo con la clave y nombre de la operación bancaria
			$regreso[] = $r["idcontrol"]. "|" . $r["idoperacion"] . "," . $r["idcontrol"] . " - " . $r["nombre"]. " - ".  $r["idoperacion"] ;
		}
		return $regreso;
	}
	return null;
}
//  ____________________________________________________________________________________________________
function noTieneMovimientos(){
	$vId = pg_escape_string($this->idOperacion); $vId1 = pg_escape_string($this->idControl);
	$sql = "Select idoperacion as salida from movimientos where idoperacion='$vId' and idcontrol='$vId1'";
	$res = getcampo($sql);
	if ($res==""){ // No existe la operacion en controles bancarios
		return true;
	}
	return false;
}
//  ____________________________________________________________________________________________________
}

?>