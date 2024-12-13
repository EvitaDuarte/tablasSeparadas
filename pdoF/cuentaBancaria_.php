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
	private $tabla;
	private $concilia;
	private $banco;
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
			$this->tabla			= "t_" . trim($aDatos["idCuentaBancaria"]);
			$this->concilia			= $aDatos["concilia"];
			$this->concilia 		= ($this->concilia=="on")?"true":"false";
			$this->banco			= pg_escape_string($aDatos["banco"]);
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
	public function get_tabla(){
		return $this->tabla;
	}
	public function get_concilia(){
		return $this->concilia;
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
 	public function set_tabla($tabla){
		$this->tabla = $tabla;
	}
 	public function set_concilia($concilia){
		$this->concilia = ($concilia=="on")?"true":"false";
	}
//  ____________________________________________________________________________________________________
public function guardar($conexion,$operacion,&$respuesta){
	$stmt = null;
	// ----------------
	if($operacion=="Modificar") /*Modifica*/ {
		$stmt = $conexion->prepare(
					'UPDATE ' . self::TABLA .' SET nombre = :nombre, siglas = :siglas, estatus = :estatus, '.
					' tabla =:tabla, se_concilia=:concilia, idbanco=:banco ' .
				    ' WHERE idcuentabancaria = :idCuentaBancaria');

	}elseif ($operacion=="Adicionar") {
		$stmt = $conexion->prepare('INSERT INTO cuentasbancarias '.
				   ' (idcuentabancaria, nombre, siglas, estatus, consecutivo, usuarioalta, tabla, se_concilia, idbanco ) ' .
				   ' VALUES(:idCuentaBancaria, :nombre, :siglas, :estatus, :consecutivo, :usuarioalta, :tabla, :concilia , :banco)');
		$stmt->bindParam(':consecutivo', $this->consecutivo	, PDO::PARAM_INT);
		$stmt->bindParam(':usuarioalta', $this->usuarioAlta , PDO::PARAM_STR);
	}
	$respuesta["objeto"] = array($this->idCuentaBancaria,$this->nombre,$this->siglas,$this->estatus,$this->consecutivo,
								 $this->usuarioAlta,$this->concilia,$this->banco);
	$stmt->bindParam(':idCuentaBancaria'	, $this->idCuentaBancaria , PDO::PARAM_STR);
	$stmt->bindParam(':nombre'				, $this->nombre 		  , PDO::PARAM_STR);
	$stmt->bindParam(':siglas'				, $this->siglas 		  , PDO::PARAM_STR);
	$stmt->bindParam(':estatus'				, $this->estatus	  	  , PDO::PARAM_INT);
	$stmt->bindParam(':concilia'			, $this->concilia	  	  , PDO::PARAM_INT);
	$stmt->bindParam(':banco'				, $this->banco		  	  , PDO::PARAM_INT);
	$stmt->bindParam(':tabla'				, $this->tabla	  	  	  , PDO::PARAM_STR);
	// ------------
	$respuesta["depura"] = $stmt->execute();

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
	$cTabla = nombreTabla($this->idCuentaBancaria);
	$vId    = pg_escape_string($this->idCuentaBancaria); // Evita cualquier inyección de código
	$sql	= "select idcuentabancaria as salida from $cTabla where idcuentabancaria='$vId'";
	$res	= getcampo($sql);
	return ($res=="");
}
//  ____________________________________________________________________________________________________
public function existeTabla($cCta,&$r){
	$cTabla  = "t_" . trim($cCta);
	$sql	 = " Select  exists (   select 1 from information_schema.tables 
    		 	 where table_schema = 'atablas' and table_name = '$cTabla' ) as existe_tabla;";
	$res	 = ejecutaSQL_($sql);
	$lExiste = $res[0]["existe_tabla"];
	if ( $lExiste==false ){
		$cTabla = "atablas." . trim($cTabla);
		$sql	= "CREATE TABLE $cTabla (LIKE atablas.machote INCLUDING ALL);";
		$res	= ejecutaSQL_($sql);
		$r["ct"]= $res;
		// Solo se deben de copiar las llaves foráneas
		$sql_constraints = "
    			ALTER TABLE $cTabla
    			ADD CONSTRAINT idcuentabancaria_unico CHECK (idcuentabancaria = '$cCta'),

    			ADD CONSTRAINT fk_control_operacion FOREIGN KEY (idcontrol, idoperacion)
		        REFERENCES public.controlesbancarios (idcontrol, idoperacion) MATCH SIMPLE
		        ON UPDATE NO ACTION
		        ON DELETE NO ACTION,
		        
			    ADD CONSTRAINT fk_cuenta FOREIGN KEY (idcuentabancaria)
			        REFERENCES public.cuentasbancarias (idcuentabancaria) MATCH SIMPLE
			        ON UPDATE NO ACTION
			        ON DELETE NO ACTION,
			        
			    ADD CONSTRAINT fk_estatus FOREIGN KEY (estatus)
			        REFERENCES public.movimientoestatus (estatus) MATCH SIMPLE
			        ON UPDATE NO ACTION
			        ON DELETE NO ACTION,
			        
			    ADD CONSTRAINT fk_machote_operaciones FOREIGN KEY (idoperacion)
			        REFERENCES public.operacionesbancarias (idoperacion) MATCH SIMPLE
			        ON UPDATE NO ACTION
			        ON DELETE NO ACTION,
			        
			    ADD CONSTRAINT fk_unidad FOREIGN KEY (idunidad)
			        REFERENCES public.unidades (idunidad) MATCH SIMPLE
			        ON UPDATE NO ACTION
			        ON DELETE NO ACTION;
		";
		$r["st"] = $sql_constraints;
		$res	= ejecutaSQL_($sql_constraints);
		$r["cc"]= $res;
	}
}
//  ____________________________________________________________________________________________________
//  ____________________________________________________________________________________________________
//  ____________________________________________________________________________________________________
//  ____________________________________________________________________________________________________
//  ____________________________________________________________________________________________________
//  ____________________________________________________________________________________________________
}
?>