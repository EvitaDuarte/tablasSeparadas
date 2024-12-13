<?php
/**
	Clase para manejar objetos para la Cuenta Bancaria
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../backF/rutinas_.php');
require_once("../backF/con_pg_OpeFinW1_.php");

class Usuarios{
	private $idUsuario;
	private $Nombre;
	private $idUnidad;
	private $Estatus;
	private $idEsquema;
	private $idusuarioa;
	private $fechaAlta;

		// ------------ Constructor -------------------
	public function __construct($aDatos) {
		if ($aDatos==null){
			// Solo construye el objeto
		}else{
			// Despues del this no lleva $
			$this->idUsuario	= pg_escape_string($aDatos["idUsuario"]);
			$this->Nombre 		= pg_escape_string($aDatos["Nombre"]);
			$this->idUnidad		= pg_escape_string($aDatos["idUnidad"]);
			$this->idEsquema	= $aDatos["idEsquema"];
			$this->Estatus		= $aDatos["estatus"];
			$this->idusuarioa  	= $aDatos["idusuarioa"];
		}
	}
//	------------ Get y Set ------------
 	public function get_idUsuario(){
		return $this->idUsuario;
	}
 	public function set_idUsuario($idUsuario){
		$this->idUsuario = $idUsuario;
	}
 	public function get_Nombre(){
		return $this->Nombre;
	}
 	public function set_($Nombre){
		$this->Nombre = $Nombre;
	}
 	public function get_idUnidad(){
		return $this->idUnidad;
	}
 	public function set_idUnidad($idUnidad){
		$this->idUnidad = $idUnidad;
	}
 	public function get_Estatus(){
		return $this->Estatus;
	}
 	public function set_Estatus($Estatus){
		$this->Estatus = $Estatus;
	}
 	public function get_idEsquema(){
		return $this->idEsquema;
	}
 	public function set_idEsquema($idEsquema){
		$this->idEsquema = $idEsquema;
	}
 	public function get_usuarioAlta(){
		return $this->usuarioAlta;
	}
 	public function set_usuarioAlta($usuarioAlta){
		$this->usuarioAlta = $usuarioAlta;
	}
// ________________________________________________________________
// ---------- Métodos Auxiliares
public function UsuarioExiste(&$respuesta,$operacion){
	// Se revisa si existe el usuario a modificar
	$vId = pg_escape_string($this->idUsuario);
	$sql = "select idusuario as salida from usuarios where idusuario='$vId'";
	$res = getcampo($sql);
	if ($res==""){ // No existe el usuario
		$respuesta["mensaje"] = "No existe el usuario a $operacion con ID[$vId]";
		return false;
	}
	$respuesta["depura"] = "Usuario Existe";
	return true;
}
// ________________________________________________________________
public function UsuariosCarga(&$respuesta){
	$sql = "select a.idusuario, a.nombre, a.idunidad, a.estatus, b.descripcion, a.idusuarioa, a.fechaalta, a.idesquema ".
		   " from usuarios a , esquemas b where a.idesquema=b.idesquema order by idusuario";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$respuesta["resultados"] = $res;	// Para llenar la tabla HTML
		$respuesta["success"]	 = true;
		$respuesta["mensaje"]	 = ""; 		// En la carga inicial o al refrecar tabla no se debe mandar mensaje
		return;
	}else{
		$respuesta["mensaje"] = "No hay usuarios dados de alta en el Sistema";
	}
	return;
}
// ________________________________________________________________
// --------------------- CRUD ---------------------
// ________________________________________________________________	
public function UsuarioAgrega(&$respuesta){
	global $conn_pdo,$usrClave;	// Variable global que tiene la conexión a la Base de Datos

	if ( $this->UsuarioExiste($respuesta,"agregar") ){
		$respuesta["mensaje"] = "Ya existe el usuario solicitado";
	}else{
		try {
			$conn_pdo->beginTransaction();
			// SQL para la inserción
		    $sql = "INSERT INTO usuarios (idusuario, nombre, estatus, idesquema, idusuarioa, idunidad)  " . 
		    	   "VALUES (:idusuario, :nombre, :estatus, :idesquema, :idusuarioa, :idunidad)";

		    // Preparar la consulta
		    $stmt = $conn_pdo->prepare($sql);

		    // Asignar valores a los parámetros
    		// Se recupera la descripción, se protegue de inyección de código
			// Se obtiene el valor del Id del Esquema, e protege de inyección de código
			$vIdUsuario  = pg_escape_string($this->idUsuario);
			$vNombre	 = pg_escape_string($this->Nombre);
			$vIdUnidad	 = pg_escape_string($this->idUnidad);
			$vIdEsquema	 = pg_escape_string($this->idEsquema);
			$vEstatus	 = ($this->Estatus=="on")?"true":"false";
			$vUsrClave   = pg_escape_string($this->idusuarioa);

		    $stmt->bindParam(':idusuario'	, $vIdUsuario	, PDO::PARAM_STR);
		    $stmt->bindParam(':nombre'		, $vNombre		, PDO::PARAM_STR);
		    $stmt->bindParam(':estatus'		, $vEstatus		, PDO::PARAM_INT);
		    $stmt->bindParam(':idesquema'	, $vIdEsquema	, PDO::PARAM_STR);
		    $stmt->bindParam(':idusuarioa'	, $vUsrClave	, PDO::PARAM_STR);
		    $stmt->bindParam(':idunidad'	, $vIdUnidad	, PDO::PARAM_STR);
		    
		    // Ejecuta el SQL
		    $stmt->execute();
		    // Confirmar la transacción (guardar los cambios en la base de datos)
		    $conn_pdo->commit();
	    	$this->UsuariosCarga($respuesta); // Actualiza para refrescar la tabla HTML
	    	$respuesta["mensaje"] 	= "Se dio de alta el Usuario solicitado";
	    	$respuesta["success"] 	= true;
		}catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}
}
// ________________________________________________________________	
public function UsuarioModifica(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	$conn_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if ( $this->UsuarioExiste($respuesta,"modificar") ){
		// Se realizará el Update usando transacciones
		try {
			$conn_pdo->beginTransaction();
			// SQL para la  mofificacion 
		    $sql = "UPDATE usuarios SET  nombre=:nombre, estatus=:estatus, idesquema=:idesquema, " .
		    	   "idusuarioa=:idusuarioa, idunidad=:idunidad where idusuario = :idusuario " ;
		    // Prepara la operacion sql 
		    $stmt = $conn_pdo->prepare($sql);

		    // Asignar valores a los parámetros
    		// Se recupera la descripción, se protegue de inyección de código
			// Se obtiene el valor del Id del Esquema, e protege de inyección de código
			$vIdUsuario  = pg_escape_string($this->idUsuario);
			$vNombre	 = pg_escape_string($this->Nombre);
			$vIdUnidad	 = pg_escape_string($this->idUnidad);
			$vIdEsquema	 = pg_escape_string($this->idEsquema);
			$vEstatus	 = ($this->Estatus=="on")?"true":"false";
			$vUsrClave   = pg_escape_string($this->idusuarioa);

		    $stmt->bindParam(':idusuario'	, $vIdUsuario	, PDO::PARAM_STR);
		    $stmt->bindParam(':nombre'		, $vNombre		, PDO::PARAM_STR);
		    $stmt->bindParam(':idunidad'	, $vIdUnidad	, PDO::PARAM_STR);
		    $stmt->bindParam(':idesquema'	, $vIdEsquema	, PDO::PARAM_STR);
		    $stmt->bindParam(':estatus'		, $vEstatus		, PDO::PARAM_INT);
		    $stmt->bindParam(':idusuarioa'	, $vUsrClave		, PDO::PARAM_STR);
		    
		    // Ejecuta el SQL
		    $respuesta["depura"] = $stmt->execute();
		    // Confirmar la transacción (guardar los cambios en la base de datos)
		    $conn_pdo->commit();
	    	$this->UsuariosCarga($respuesta); // Actualiza para refrescar la tabla HTML

	    	$respuesta["mensaje"] 	= "Se actualizo correctamente el Usuario solicitado";
	    	$respuesta["success"] 	= true;
		} catch (Exception $e) {
			$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
			$conn_pdo->rollBack();
		}
	}
}
// ________________________________________________________________	
public function UsuarioEliminar(&$respuesta){
	global $conn_pdo;	// Variable global que tiene la conexión a la Base de Datos
	if ( $this->UsuarioExiste($respuesta,"eliminar") ){
		if ( $this->UsuarioNotieneMovimientos($respuesta) ){
			if ( $this->UsuarioNoTieneAsignadoCuentas() ){
				// Se realizará el Delete usando transaccones
				try{
					$conn_pdo->beginTransaction(); // Empieza la transacción
					// Se obtiene el valor del Id del Esquema, e protege de inyección de código
					$vIdUsuario  = pg_escape_string($this->idUsuario);
					// Se prepara el delete
					$stmt = $conn_pdo->prepare("DELETE from usuarios WHERE idusuario = :idusuario ");
					$stmt->bindParam(':idusuario'	, $vIdUsuario	, PDO::PARAM_STR);
					//
				    // Ejecutar el delete
			    	$respuesta["depura"] = $stmt->execute();
			    	$conn_pdo->commit();	// Si no hubo problemas eliminar el esquema
			    	$this->UsuariosCarga($respuesta); // Actualiza para refrescar la tabla HTML
			    	$respuesta["mensaje"] 	= "Se elimina correctamente el usuario [$vIdUsuario]";
			    	$respuesta["success"] 	= true;
			    	return;
				}catch (Exception $e) {
					$respuesta["mensaje"] = "Ocurrió una excepción [" . $e->getMessage() . "]";
					$conn_pdo->rollBack();
				}
			}else{
				$respuesta["mensaje"] = "El usuario con ID[$this->idUsuario] tiene cuentas bancarias asignados, no procede su eliminación.";
			}
		}else{
			$respuesta["mensaje"] = "El usuario con ID[$this->idUsuario] tiene movimientos asignados, no procede su eliminación.";
			return;
		}
	}else{
		return;
	}
}
// ________________________________________________________________	
public function UsuarioNotieneMovimientos(&$r){
	// return true; 
	// No se puede eliminar un usuario que tenga movimientos o cuentas bancarias asignadas
	$vIdUsuario  = pg_escape_string($this->idUsuario);
	$lTieneMovs	 = tieneMovimientos("usuarioalta",$vIdUsuario,$r);

	if ($lTieneMovs){
		return false; // no se puede eliminar
	}

	return true;

	/*
	$sql = "select usuarioalta as salida from movimientos where usuarioalta='$vIdUsuario'";
	$res = getcampo($sql);
	if ($res!=""){ // Existen movimientos asignados a usuario
		return false;
	}
	return true;*/
}
// ________________________________________________________________	
public function UsuarioNoTieneAsignadoCuentas(){
	$vIdUsuario  = pg_escape_string($this->idUsuario);
	$sql = "select idusuario as salida from accesos where idusuario='$vIdUsuario'";
	$res = getcampo($sql);
	if ($res!=""){ // Existen accesos a cuentas asignados a usuario
		return false;
	}
	return true;
}
// ________________________________________________________________	
// ________________________________________________________________	
}

?>