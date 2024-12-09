<?php
/**
	Clase para manejar objetos para la Cuenta Bancaria
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../backF/rutinas_.php';
class accesos{
	private $idCuentaBancaria;
	private $idUsuario;
	private $usuarioAlta;
	private $fechaAlta;

	// ------------ Constructor -------------------
	public function __construct($aDatos,$usuarioAlta) {
       $this->idCuentaBancaria 	= pg_escape_string($aDatos["idCuentaBancaria"]);
       $this->idUsuario			= pg_escape_string($aDatos["idUsuario"]);
       $this->usuarioAlta		= $usuarioAlta;
	}
	// ------------ GET y SET -------------------
 	public function get_idCuentaBancaria(){
		return $this->idCuentaBancaria;
	}
 	public function set_idCuentaBancaria($idCuentaBancaria){
		$this->idCuentaBancaria = $idCuentaBancaria;
	}
 	public function get_idUsuario(){
		return $this->idUsuario;
	}
 	public function set_idUsuario($idUsuario){
		$this->idUsuario = $idUsuario;
	}
 	public function get_usuarioAlta(){
		return $this->usuarioAlta;
	}
 	public function set_usuarioAlta($usuarioAlta){
		$this->usuarioAlta = $usuarioAlta;
	}
	// ---------- Métodos Auxiliares
	public function ExisteAcceso($conexion,&$respuesta,$operacion){
		$vId1 = pg_escape_string(trim($this->idCuentaBancaria));
		$vId2 = pg_escape_string(trim($this->idUsuario));
		$sql = "Select idcuentabancaria as salida from accesos where idcuentabancaria='$vId1' and idUsuario='$vId2' ";
		$res = getcampo($sql);
		if ($res==""){ // No existe el acceso
			$respuesta["mensaje"] = "No existe el acceso a $operacion de la cuenta $vId1 para el usuario $vId2";
			return false;
		}
		$respuesta["mensaje"] = "Ya existe el acceso solicitado";
		$respuesta["depura"]  = "Acceso Existe";
		return true;
	}
	// ----------- CRUD --------------------
	public function adicionaAcceso($conexion,$operacion,&$respuesta){
		$stmt = null;
		// ----------------
		if($operacion=="Modificar") /*Modifica*/ {
			// No hay campos adicionales a modificar 

		}elseif ($operacion=="Adicionar") {
			$stmt = $conexion->prepare('INSERT INTO accesos '.
					   ' (idcuentabancaria, idusuario, usuarioalta ) ' .
					   ' VALUES(:idCuentaBancaria, :idUsuario, :usuarioAlta)');

			$stmt->bindParam(':usuarioAlta', $this->usuarioAlta , PDO::PARAM_STR);
		}
		
		$stmt->bindParam(':idCuentaBancaria'	, $this->idCuentaBancaria , PDO::PARAM_STR);
		$stmt->bindParam(':idUsuario'			, $this->idUsuario 		  , PDO::PARAM_STR);

		// ------------
		$respuesta["depura"] = $stmt->execute();
		$respuesta["objeto"] = array($this->idCuentaBancaria,$this->idUsuario,$this->usuarioAlta);
	}
	//---------------------------------
	public function eliminaAcceso($conexion,$operacion,&$respuesta){
		$vId1  = pg_escape_string($this->idCuentaBancaria);
		$vId2  = pg_escape_string($this->idUsuario);
		$stmt  = $conexion->prepare("delete from accesos where idcuentabancaria='$vId1' and idUsuario='$vId2' ");
		//
		$respuesta["depura"] = $stmt->execute();
		return $respuesta["depura"];
	}
	//----------------------------------
	public function buscaDatos($conexion,&$respuesta){
		$vId1  = pg_escape_string($this->idCuentaBancaria);
		$vId2  = pg_escape_string($this->idUsuario);
		$sql   =  "select a.idcuentabancaria , b.nombre , a.idusuario , a.usuarioalta, 	a.fechaalta " .
				  " from accesos a , cuentasbancarias b where a.idcuentabancaria=b.idcuentabancaria ";
		if ( $vId1!=""){
			$sql = $sql . " and a.idcuentabancaria='$vId1' ";
		} 
		if ( $vId2!=""){
			$sql = $sql . " and a.idusuario='$vId2' ";
		}
		$res = ejecutaSQL_($sql);
		if ( $res!=null){
			$respuesta["resultados"] = $res;	// Para llenar la tabla HTML
			$respuesta["success"]	 = true;
			$respuesta["mensaje"]	 = ""; 		// En la carga inicial o al refrecar tabla no se debe mandar mensaje
			return true;
		}else{
			$respuesta["mensaje"] = "No hay aún accesos dados de alta en el Sistema";
		}
		return false;
	}
//  ____________________________
//  ____________________________
//  ____________________________
} // fin de class

?>