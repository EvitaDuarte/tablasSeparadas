<?php
//	Clase para manejar objetos para la Tabla de Reintegros
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

// El php que invoca a Movimientos_. php debe llamar a rutinas_. php y metodos_. php
class Reintegros{
	private $idreintegro;
    private $idunidad;
    private $folio;
    private $oficio;
    private $monto; 
    private $origen;
    private $economia;
    private $pasivo;
    private $operacion;
    private $fecha_ope;
	private $anio;
	private $folio_interno;
	private $idcuentabancaria;
	private $cvectrl;
	private $cvemov;
	private $usuarioalta;
	private $conexion;

// _______________________________________________________________________________
	public function __construct($conexion) {
		if ($conexion==null){
			// Solo construye el objeto
		}else{
			$this->conexion	= $conexion; 
		}
	}
// _______________________________________________________________________________
	public function cargaDatos($aDatos){
		if ($aDatos!==null){
			$this->idreintegro			= pg_escape_string($aDatos["idreintegro"]);
			$this->idunidad				= pg_escape_string($aDatos["idunidad"]); 
			$this->folio				= pg_escape_string($aDatos["folio"]); 
			$this->oficio				= pg_escape_string($aDatos["oficio"]); 
			$this->monto				= pg_escape_string($aDatos["monto"]); 
			$this->origen				= pg_escape_string($aDatos["origen"]);
			$this->economia				= pg_escape_string($aDatos["economia"]); 
			$this->pasivo				= pg_escape_string($aDatos["pasivo"]); 
			$this->operacion			= pg_escape_string($aDatos["operacion"]); 
			$this->fecha_ope			= pg_escape_string($aDatos["fecha_ope"]); 
			$this->anio					= pg_escape_string($aDatos["anio"]); 
			$this->folio_interno		= pg_escape_string($aDatos["folio_interno"]); 
			$this->idcuentabancaria		= pg_escape_string($aDatos["idcuentabancaria"]); 
			$this->cvectrl				= pg_escape_string($aDatos["cvectrl"]); 
			$this->cvemov				= pg_escape_string($aDatos["cvemov"]); 
			$this->usuarioalta			= pg_escape_string($aDatos["usuarioalta"]); 
		}
	}
// ________________________________________________________________________________________
	public function iniciaConexion($conexion){
		$this->conexion = $conexion;
	}
// ________________________________________________________________________________________	
	public function actualizaReintegro($lAdiciona){
		if ($lAdiciona){
			$sql = 	"INSERT INTO jle.reintegros( " .
					"idunidad, folio, oficio, monto, origen, economia, pasivo, operacion, fecha_ope, anio , folio_interno," .
					" idcuentabancaria, cvectrl, cvemov,usuarioalta ) " .
					" VALUES ( " . 
					":idunidad, :folio, :oficio, :monto, :origen, :economia, :pasivo, :operacion," . 
					":fecha_ope, :anio, :folio_interno, :idcuentabancaria, :cvectrl, :cvemov, :usuarioalta)";
		}else{
			$sql =	"UPDATE jle.reintegros SET ".
					" idunidad=:idunidad, folio=:folio, oficio=:oficio, monto=:monto , origen=:origen, " .
					" economia=:economia, pasivo=:pasivo, operacion=:operacion," .
					" fecha_ope=:fecha_ope, anio=:anio, folio_interno=:folio_interno, idcuentabancaria=:idcuentabancaria, " . 
					" cvectrl=:cvectrl, cvemov=:cvemov " .
					" where idreintegro=:idreintegro";
		}
		$stmt = $this->conexion->prepare($sql);
		// Se protegen los parametros del SQL
		if ( $lAdiciona){
			$stmt->bindParam(':usuarioalta'	, $this->usuarioalta, PDO::PARAM_STR);
		}else{
			$stmt->bindParam(':idreintegro'	, $this->idreintegro, PDO::PARAM_STR);
		}
		//
		// $nId = $this->idmovimiento === null ? null : $this->idmovimiento;
		// $nId = $this->idmovimiento === ""   ? null : $this->idmovimiento;
		//
		$stmt->bindParam(':idunidad'			, $this->idunidad				, PDO::PARAM_STR);
		$stmt->bindParam(':folio'				, $this->folio					, PDO::PARAM_STR);
		$stmt->bindParam(':oficio'				, $this->oficio					, PDO::PARAM_STR);
		$stmt->bindParam(':monto'				, $this->monto					, PDO::PARAM_STR);
		$stmt->bindParam(':origen'				, $this->origen					, PDO::PARAM_STR);
		$stmt->bindParam(':economia'			, $this->economia				, PDO::PARAM_STR);
		$stmt->bindParam(':pasivo'				, $this->pasivo					, PDO::PARAM_STR);
		$stmt->bindParam(':operacion'			, $this->operacion				, PDO::PARAM_STR);
		$stmt->bindParam(':fecha_ope'			, $this->fecha_ope				, PDO::PARAM_STR);
		$stmt->bindValue(':anio'				, $this->anio					, PDO::PARAM_STR);
		$stmt->bindValue(':folio_interno'		, $this->folio_interno			, PDO::PARAM_STR);
		$stmt->bindValue(':idcuentabancaria'	, $this->idcuentabancaria		, PDO::PARAM_STR);
		$stmt->bindValue(':cvectrl'				, $this->cvectrl				, PDO::PARAM_STR);
		$stmt->bindValue(':cvemov'				, $this->cvemov					, PDO::PARAM_STR);

		//

		return $stmt->execute();
	}
// _______________________________________________________________________________
	function eliminaReintegro($nId,&$cRegreso){
		$lRegreso = false;
	    try {
	        // Preparar la consulta SQL
	        $sql = "delete from jle.reintegros WHERE idreintegro = :idRei ";
	        $stmt = $this->conexion->prepare($sql);

	        // Vincular el parámetro
	        $stmt->bindParam(':idRei', $nId, PDO::PARAM_INT);
	        // Ejecutar la consulta
	        $stmt->execute();

	        // Comprobar si se eliminó algún registro
	        if ($stmt->rowCount() > 0) {
	            $cRegreso = "Reintegro eliminado correctamente.";
	            $lRegreso = true;
	        } else {
	            $cRegreso = "No se encontró el reintegro con el ID $nId proporcionado.";
	        }

	    } catch (PDOException $e) {
	        // Manejo de errores
	        $cRegreso = "Error al eliminar el movimiento: " . $e->getMessage();
	    }
	    return $lRegreso;
	}	
// _______________________________________________________________________________	
	function traeMovimientoxId($nId){
		$sql =	"select idunidad,folio, oficio, monto, origen, economia, pasivo, operacion, fecha_ope, anio, " . 
				"folio_interno, idcuentabancaria,cvectrl,cvemov, usuario_alta, fecha_alta from jle.reintegros " .
			    "where idreintegro=$nId";
		return ejecutaSQL_($sql);
	}
// ________________________________________________________________________________________	
	function LlaveDuplicada(&$cRegreso){
		$cRe = "";
		$cUr = $this->idunidad;
		$cOpe= $this->operacion;
		$sql = "select idunidad,operacion from jle.reintegros where idunidad='$cUr' and operacion='$cOpe' ";
		$lRe = $this->consultaSql($sql,$cRe);
		if ( $cRe==array() ){
			$cRegreso = "No duplicada";
			return false; // La llave no esta duplicada
		}else{
			// Llave Duplicada;
			$cRegreso = "La operacion $cOpe ya existe para la U.R. $cUr";
			return true;
		}
	}
// ________________________________________________________________________________________	
// ________________________________________________________________________________________	
// ________________________________________________________________________________________	 Catalogos
	function traeUnidades(&$cRegreso){
		// CONCAT(nombre, '|', apellido) AS nombre_completo
		$sql =	"select concat(idunidad,',',cta_ur,',',nombreunidad) as clave, idunidad as descripcion ".
				" from public.unidades where estatus=true order by idunidad";
		return $this->consultaSql($sql,$cRegreso);
	}
// ________________________________________________________________________________________	
	function traeOrigenes(&$cRegreso){
		$sql = "select origen as clave, descripcion from jle.origenes where activo=true order by origen";
		return $this->consultaSql($sql,$cRegreso);
	}
// ________________________________________________________________________________________
	function traeCuentas(&$cRegreso){
		$sql = 	"select idcuentabancaria as clave , concat(idcuentabancaria,'	',nombre) as descripcion " .
				" from public.cuentasbancarias order by idcuentabancaria";
		return $this->consultaSql($sql,$cRegreso);
	}
// ________________________________________________________________________________________
	function ConsultaReintegros($cFecIni,$cFecFin,$cAnioRi,$cAnioRf,&$cRegreso){
		$sql =	"select a.idunidad, a.anio, a.folio, a.oficio, a.fecha_ope, " .
				"b.descripcion , a.operacion , a.monto " .
				"from jle.reintegros a , jle.origenes b  " .
				"where a.origen=b.origen and " .
				"fecha_ope>='$cFecIni' and fecha_ope<='$cFecFin' and " .
				"anio>='$cAnioRi' and anio<='$cAnioRf' " .
				"order by idunidad,fecha_ope desc ";
		return $this->consultaSql($sql,$cRegreso);
	}
// ________________________________________________________________________________________
	function traeNombreUrs(&$cRegreso){
		$sql = "select idunidad,nombreunidad from unidades order by idunidad";
		return $this->consultaSql($sql,$cRegreso);
	}
// ________________________________________________________________________________________
	function consultaSql($sql,&$cRegreso){
		$stmt		= $this->conexion->prepare($sql);
		$resultado	= $stmt->execute();
		$cRegreso	= array(); // vacío
		if ($resultado==true){
			$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
	        if (empty($resultado)) { // No hay movimientos
	        	$cRegreso = array();
	            return false;
			}else{
				$cRegreso = $resultado; //$sql;
				return true;
			}
		}else{
			$cRegreso = "Error en sql $sql ";
			return false;
		}
	}
}	
// ________________________________________________________________________________________
?>	