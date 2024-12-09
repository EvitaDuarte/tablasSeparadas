<?php

/**
	Clase para manejar objetos del tipo esquemas
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../backF/rutinas_.php');
require_once("../backF/con_pg_OpeFinW1_.php");

class Esquemas{
	private $idEsquema;			// Id Esquema
	private $descripcion;		// Descripcion del esquema
	private $estatus;			// Estatus del esquema (true-activo)
	private $usuarioAlta;
	private $fechaAlta;
//  _________________________________________________________________________________
	public function __construct($aDatos) {
		if ($aDatos==null){
			// Solo construye el objeto
		}else{
	       	$this->idEsquema 	= pg_escape_string($aDatos["idEsquema"]);
			$this->descripcion	= pg_escape_string($aDatos["descripcion"]);
	       	$this->estatus 		= pg_escape_string($aDatos["estatus"]);
	    	$this->usuarioAlta	= pg_escape_string($aDatos["usuarioAlta"]);
    	}
	}
//	_________________________________________________________________________________
 	public function get_idEsquema(){
		return $this->idEsquema;
	}
 	public function set_idEsquema($idEsquema){
		$this->idEsquema = $idEsquema;
	}
 	public function get_descripcion(){
		return $this->descripcion;
	}
 	public function set_descripcion($descripcion){
		$this->descripcion = $descripcion;
	}
 	public function get_estatus(){
		return $this->estatus;
	}
 	public function set_estatus($estatus){
		$this->estatus = $estatus;
	}
 	public function get_usuarioAlta(){
		return $this->usuarioAlta;
	}
 	public function set_usuarioAlta($usuarioAlta){
		$this->usuarioAlta = $usuarioAlta;
	}
//	_________________________________________________________________________________
function EsquemasConsulta(&$respuesta){
	$res = $this->regresaEsquemas();
	if ( $res!=null){
		$respuesta["resultados"] = $res;
		$respuesta["success"]	 = true;
		$respuesta["mensaje"]	 = "";
	}else{
		$respuesta["mensaje"] = "No hay esquemas dados de alta en el Sistema";
	}
} 
//	_________________________________________________________________________________
function EsquemaExiste(&$respuesta,$operacion){
	// Se revisa si existe el esquema a modificar
	$vId = pg_escape_string($this->idEsquema);
	$sql = "select idesquema as salida from esquemas where idesquema=$vId";
	$res = getcampo($sql);
	if ($res==""){ // No existe el esquema
		$respuesta["mensaje"] = "No existe el esquema a $operacion con ID[$vId]";
		return false;
	}
	return true;
}
//	_________________________________________________________________________________
function EsquemaAgrega(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	if ( $this->EsquemaExiste($respuesta,"agregar") ){
		$respuesta["mensaje"] = "Ya existe el esquema solicitado";
	}else{
		try {
			$conn_pdo->beginTransaction();
			// SQL para la inserción
		    $sql = "INSERT INTO esquemas (idesquema, descripcion, estatus) VALUES (:id, :descripcion, :estatus)";

		    // Preparar la consulta
		    $stmt = $conn_pdo->prepare($sql);
	        if (!$stmt) {
        		throw new Exception("Error al preparar el alta: " . $conn_pdo->errorInfo()[2]);
    		}

		    // Asignar valores a los parámetros
    		// Se recupera la descripción, se protegue de inyección de código
			// Se obtiene el valor del Id del Esquema, e protege de inyección de código
			$vId  = pg_escape_string($this->idEsquema);
			$vDes = pg_escape_string($this->descripcion);
			$vEst = ($this->estatus=="on" || $this->estatus=="1")?"true":"false";
		    $stmt->bindParam(':id'			, $vId, PDO::PARAM_INT);
		    $stmt->bindParam(':descripcion'	, $vDes, PDO::PARAM_STR);
		    $stmt->bindParam(':estatus'		, $vEst, PDO::PARAM_INT);
		    // Ejecuta el SQL
		    $stmt->execute();
		    // Confirmar la transacción (guardar los cambios en la base de datos)
		    $conn_pdo->commit();
	    	$respuesta["mensaje"] 	= "Se dio de alta el esquema solicitado";
	    	$respuesta["success"] 	= true;
	    	$respuesta["resultados"]= $this->regresaEsquemas(); // Actualiza para refrescar la tabla HTML
		}catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}
}
//	_________________________________________________________________________________
function EsquemaModifica(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	if ( $this->EsquemaExiste($respuesta,"modificar") ){
		// Se realizará el Update usando transacciones
		try {
			$conn_pdo->beginTransaction();
			$stmt = $conn_pdo->prepare("UPDATE esquemas SET descripcion = :_descripcion , estatus= :_estatus WHERE idesquema = :_idesquema ");
	        if (!$stmt) {
        		throw new Exception("Error al preparar la modificación: " . $conn_pdo->errorInfo()[2]);
    		}
		    // Ejecutar la consulta preparada con los valores
		    $vEst = ($this->estatus=="on" || $this->estatus=="1")?"true":"false";
		    $vId  = pg_escape_string($this->idEsquema);
		    $vDes = pg_escape_string($this->descripcion);
	    	$stmt->bindParam(':_descripcion', $vDes , PDO::PARAM_STR);
	    	$stmt->bindParam(':_estatus'	, $vEst , PDO::PARAM_STR);
	    	$stmt->bindParam(':_idesquema'	, $vId	, PDO::PARAM_STR);
		    // Ejecutar el update
	    	$stmt->execute();
	    	$conn_pdo->commit();
	    	$respuesta["mensaje"] 	= "Se actualizo correctamente el esquema [$vId]";
	    	$respuesta["success"] 	= true;
	    	$respuesta["resultados"]= $this->regresaEsquemas(); // Actualiza para refrescar la tabla HTML
		}catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}else{
		return;
	}
}
//	_________________________________________________________________________________
function EsquemaEliminar(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	if (  $this->EsquemaExiste($respuesta,"eliminar") ){
		if ( $this->EsquemaNoTieneUsuarios($respuesta) ){
		// Se realizará el Delete usando transaccones
			try{
				$conn_pdo->beginTransaction(); // Empieza la transacción
				// Se obtiene el valor del Id del Esquema, e protege de inyección de código
				$vId  = pg_escape_string($respuesta["datos"]["idEsquema"]);
				// Se prepara el delete
				$stmt = $conn_pdo->prepare("DELETE from esquemas WHERE idesquema = :_idesquema ");
		        if (!$stmt) {
        			throw new Exception("Error al preparar la eliminación: " . $conn_pdo->errorInfo()[2]);
    			}
				$stmt->bindParam(':_idesquema'	, $vId	, PDO::PARAM_STR);
				//
			    // Ejecutar el delete
		    	$stmt->execute();
		    	$conn_pdo->commit();	// Si no hubo problemas eliminar el esquema
		    	$respuesta["mensaje"] 	= "Se elimina correctamente el esquema [$vId]";
		    	$respuesta["success"] 	= true;
		    	$respuesta["resultados"]= $this->regresaEsquemas(); // Actualiza para refrescar la tabla HTML, no se pued usar Esquemas Consulta
		    	//
			}catch (Exception $e) {
				$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
				$conn_pdo->rollBack();
			}
		}else{
			return;
		}
	}else{
		return;
	}
}
//	_________________________________________________________________________________
function EsquemaNoTieneUsuarios(&$respuesta){
	// No se puede eliminar un esquema que tenga usuarios
	$vId = pg_escape_string($this->idEsquema);
	$sql = "select idusuario as salida from usuarios where idesquema=$vId";
	$res = getcampo($sql);
	if ($res!=""){ // Existen usuarios asignados a esquema
		$respuesta["mensaje"] = "El esquema con ID[$vId] tiene usuarios asignados, no procede su eliminación";
		return false;
	}
	return true;
}
//	_________________________________________________________________________________
function regresaEsquemas(){
	$sql = "select idesquema, descripcion, estatus, idusuario, fechaalta from esquemas order by idesquema";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		return $res;
	}else{
		return [];
	}
}
//	_________________________________________________________________________________
function comboEsquema(&$respuesta){
	$res = $this->regresaEsquemas();
	if ( $res!=null){
		$respuesta["combo"][] = " ,Seleccione";
		foreach ($res as $r ){
			$respuesta["combo"][] = $r["idesquema"].",".$r["descripcion"];
		}
	}
}
//	_________________________________________________________________________________
}

?>