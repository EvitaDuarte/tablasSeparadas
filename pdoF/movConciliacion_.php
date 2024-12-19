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
	Clase para manejar objetos para la Tabla de Conciliacion movi-mientos
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

// El php que invoca a Movimi-entos.php debe llamar a rutinas.php y metodos.php
// require_once '../backF/rutinas_.php';
// require_once "metodos.php";
class movConciliacion{
	private $id_concimovimiento;
    private $idcuentabancaria;
    private $fechaoperacion;
    private $fechaconciliacion;
    private $idoperacion; // SCA , SAB , SDO
    private $importeoperacion;
    private $id_layout_banco;
    private $conciliado;
    private $concepto;
    private $fcve_ope_be;
	private $idmovimiento;
	private $usuario_alta;
	private $conexion;

// _______________________________________________________________________________
	public function __construct($conexion) {
		if ($conexion==null){
			// Solo construye el objeto
		}else{
			/*
			$this->id_concimovimiento	= pg_escape_string($aDatos["id_concimovimiento"]);
			$this->idcuentabancaria		= pg_escape_string($aDatos["idcuentabancaria"]); 
			$this->fechaoperacion		= pg_escape_string($aDatos["fechaoperacion"]); 
			$this->fechaconciliacion	= pg_escape_string($aDatos["fechaconciliacion"]); 
			$this->idoperacion			= pg_escape_string($aDatos["idoperacion"]); 
			$this->importeoperacion		= pg_escape_string($aDatos["importeoperacion"]);
			$this->id_layout_banco		= pg_escape_string($aDatos["id_layout_banco"]); 
			$this->conciliado			= pg_escape_string($aDatos["conciliado"]); 
			$this->concepto				= pg_escape_string($aDatos["concepto"]); 
			$this->fcve_ope_be			= pg_escape_string($aDatos["fcve_ope_be"]); 
			$this->usuario_alta			= pg_escape_string($aDatos["usuario_alta"]); */
			$this->conexion				= $conexion; // pg_escape_string($aDatos["conexion"]); 
		}
	}
// _______________________________________________________________________________
	public function cargaDatos($aDatos){
		if ($aDatos!==null){
			$this->id_concimovimiento	= pg_escape_string($aDatos["id_concimovimiento"]);
			$this->idcuentabancaria		= pg_escape_string($aDatos["idcuentabancaria"]); 
			$this->fechaoperacion		= pg_escape_string($aDatos["fechaoperacion"]); 
			$this->fechaconciliacion	= pg_escape_string($aDatos["fechaconciliacion"]); 
			$this->idoperacion			= pg_escape_string($aDatos["idoperacion"]); 
			$this->importeoperacion		= pg_escape_string($aDatos["importeoperacion"]);
			$this->id_layout_banco		= pg_escape_string($aDatos["id_layout_banco"]); 
			$this->conciliado			= pg_escape_string($aDatos["conciliado"]); 
			$this->concepto				= pg_escape_string($aDatos["concepto"]); 
			$this->fcve_ope_be			= pg_escape_string($aDatos["fcve_ope_be"]); 
			$this->idmovimiento			= pg_escape_string($aDatos["idmovimiento"]); 
			$this->usuario_alta			= pg_escape_string($aDatos["usuario_alta"]); 
		}
	}
// _______________________________________________________________________________
	public function iniciaConexion($conexion){
		$this->conexion = $conexion;
	}
// _______________________________________________________________________________	
public function actualizaConciliacion($lAdiciona){
	if ($lAdiciona){
		$sql = 	"INSERT INTO conci_movimientos( " .
				"idcuentabancaria, fechaoperacion, fechaconciliacion, idoperacion, importeoperacion, id_layout_banco, conciliado, concepto, fcve_ope_be, idmovimiento , usuario_alta) " .
				" VALUES ( " . 
				":idcuentabancaria, :fechaoperacion, :fechaconciliacion, :idoperacion, :importeoperacion, :id_layout_banco," . 
				":conciliado, :concepto, :fcve_ope_be, :idmovimiento, :usuario_alta)";
	}else{
		$sql =	"UPDATE conci_movimientos SET ".
				" fechaoperacion=:fechaoperacion, fechaconciliacion=:fechaconciliacion," .
				" idoperacion=:idoperacion, importeoperacion=:importeoperacion, id_layout_banco=:id_layout_banco," .
				" conciliado=:conciliado, concepto=:concepto, fcve_ope_be=:fcve_ope_be, idmovimiento=:idmovimiento " . 
				" where id_concimovimiento=:id_concimovimiento";
	}
	$stmt = $this->conexion->prepare($sql);
	// Se protegen los parametros del SQL
	if ( $lAdiciona){
		$stmt->bindParam(':idcuentabancaria'	, $this->idcuentabancaria 	, PDO::PARAM_STR); // No se debe cmbiar la cuenta bancaria
		$stmt->bindParam(':usuario_alta'		, $this->usuario_alta		, PDO::PARAM_STR);
	}else{
		$stmt->bindParam(':id_concimovimiento'	, $this->id_concimovimiento	, PDO::PARAM_STR);
	}
	//
	$nId = $this->idmovimiento === null ? null : $this->idmovimiento;
	$nId = $this->idmovimiento === ""   ? null : $this->idmovimiento;
	//
	$stmt->bindParam(':fechaoperacion'		, $this->fechaoperacion		, PDO::PARAM_STR);
	$stmt->bindParam(':fechaconciliacion'	, $this->fechaconciliacion	, $this->fechaconciliacion==null?PDO::PARAM_NULL:PDO::PARAM_STR);
	$stmt->bindParam(':idoperacion'			, $this->idoperacion		, PDO::PARAM_STR);
	$stmt->bindParam(':importeoperacion'	, $this->importeoperacion	, PDO::PARAM_STR);
	$stmt->bindParam(':id_layout_banco'		, $this->id_layout_banco	, PDO::PARAM_STR);
	$stmt->bindParam(':conciliado'			, $this->conciliado			, PDO::PARAM_STR);
	$stmt->bindParam(':concepto'			, $this->concepto			, PDO::PARAM_STR);
	$stmt->bindParam(':fcve_ope_be'			, $this->fcve_ope_be		, PDO::PARAM_STR);
	$stmt->bindValue(':idmovimiento'		, $nId						, $nId === null ? PDO::PARAM_NULL:PDO::PARAM_STR);

	//

	return $stmt->execute();
}
// _______________________________________________________________________________	
function traeMovimientoxId($nId){
	$sql =	"select id_concimovimiento,idcuentabancaria, fechaoperacion, fechaconciliacion, idoperacion, importeoperacion, id_layout_banco,". 
			"conciliado, concepto, fcve_ope_be, usuario_alta, fecha_alta from conci_movimientos" .
		   "where id_concimovimiento=$nId";
	return ejecutaSQL_($sql);
}
// _______________________________________________________________________________
function traeMovRefeImpo($cCampo,$cRefe,$cImpo){ // No se ha usado , quitar este comentario cuando se use
	try {
		$cRefe	= pg_escape_string($cRefe);
		$cImpo	= pg_escape_string($cImpo);
		$sql	= "select idmovimiento from conci_movimientos where $cCampo=:referencia and importeoperacion=:importe";
		$stmt	= $this->conexion->prepare($sql);

		// Bind de los parámetros
		$stmt->bindParam(':referencia'	, $cRefe	, PDO::PARAM_STR);
		$stmt->bindParam(':importe'		, $cImpo	, PDO::PARAM_STR);
        // Ejecutar la consulta
        $stmt->execute();
        // Obtener resultados si es necesario
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
		// Regresa Id
        return $resultado['idmovimiento']; // Devolver el ID del movimiento encontrado
		// Si se obtiene un resultado se podria usar la linea de abajo para traer todos los campos
		// return $this->traeMovimientoxId($resultado['idmovimiento']);
		/*
			Como se uso fetch en la función que invoca
			if ($resultado === false) {
				echo "No se encontraron usuarios con el ID especificado.";
			} else {
				echo "Usuario encontrado: " . $resultado['nombre'];
			}
		*/
    } catch (PDOException $e) {
        // Manejar errores de conexión o consulta
        return "a) Error: " . $e->getMessage();
        //return false; // Devolver falso en caso de error
    }
}
// _______________________________________________________________________________	
function buscaSaldo($cCta,$cFecha,&$cError){
	$cError = "";
	$cCta	= pg_escape_string($cCta);  $cId = 'SDO' . $cFecha;		$cFecha = pg_escape_string($cFecha); 
	$sql	= "select id_concimovimiento from conci_movimientos where idcuentabancaria=:idcuentabancaria and idoperacion='SDO' ". 
			  " and fechaoperacion=:fechaoperacion and id_layout_banco=:idlayout and fcve_ope_be='SDO' ";
	$stmt	= $this->conexion->prepare($sql);

	$stmt->bindParam(':idcuentabancaria', $cCta		, PDO::PARAM_STR);
	$stmt->bindParam(':idlayout'		, $cId		, PDO::PARAM_STR);
	$stmt->bindParam(':fechaoperacion'	, $cFecha	, PDO::PARAM_STR);
	

	$resultado = $stmt->execute();

	if ($resultado==true){ // Si encontro ??
		$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
			if ( $resultado==false){
				return false; // No se encontro
			}{
				$cError = $resultado["id_concimovimiento"];
				return true;	// Ya existe el Saldo 
			}
	}else{
		$cError = "Error en la ejecución del SQL";
		return false; // No se encontro 
	}
}
// _______________________________________________________________________________	
function actualizaSaldo($cCta,$cFecha,$nSaldo,$cUsuario,&$cError){
	$cError = "";
	$cCta	= pg_escape_string($cCta);  	$cFecha		= pg_escape_string($cFecha); 	$cId = 'SDO' . $cFecha;
	$nSaldo = pg_escape_string($nSaldo);  	$cUsuario	= pg_escape_string($cUsuario);  $cOpe= 'SDO';

	$sql 	= "INSERT INTO conci_movimientos( " .
			  "idcuentabancaria, fechaoperacion, idoperacion, importeoperacion, id_layout_banco, fcve_ope_be, usuario_alta) " .
			  " VALUES ( " . 
			  ":idcuentabancaria, :fechaoperacion, :idoperacion, :importeoperacion, :id_layout_banco, :fcve_ope_be, :usuario_alta)";
	$stmt	= $this->conexion->prepare($sql);

	$stmt->bindParam(':idcuentabancaria'	, $cCta		, PDO::PARAM_STR);
	$stmt->bindParam(':fechaoperacion'		, $cFecha	, PDO::PARAM_STR);
	$stmt->bindParam(':idoperacion'			, $cOpe		, PDO::PARAM_STR);
	$stmt->bindParam(':importeoperacion'	, $nSaldo	, PDO::PARAM_STR);
	$stmt->bindParam(':id_layout_banco'		, $cId		, PDO::PARAM_STR);
	$stmt->bindParam(':fcve_ope_be'			, $cOpe		, PDO::PARAM_STR);
	$stmt->bindParam(':usuario_alta'		, $cUsuario	, PDO::PARAM_STR);

	$resultado =  $stmt->execute();

	if ($resultado === false) {
        // La ejecución falló; obtener detalles del error
        $errorInfo = $stmt->errorInfo();
        $cError = "Error al ejecutar la consulta: " . $errorInfo[2]; // Mensaje de error detallado
    }

	return $resultado;

}
// _______________________________________________________________________________	
function traeSaldoBanco($cCta,$cFecha,&$cRegreso){
	$cRegreso	= "";
	$cCta		= pg_escape_string($cCta);  
	$cId		= 'SDO' . $cFecha;		
	$cFecha 	= pg_escape_string($cFecha); 
	$sql		= "select importeoperacion from conci_movimientos where idcuentabancaria=:idcuentabancaria and idoperacion='SDO' ". 
			  	  " and fechaoperacion=:fechaoperacion and id_layout_banco=:idlayout and fcve_ope_be='SDO' ";
	$stmt		= $this->conexion->prepare($sql);

	$stmt->bindParam(':idcuentabancaria', $cCta		, PDO::PARAM_STR);
	$stmt->bindParam(':idlayout'		, $cId		, PDO::PARAM_STR);
	$stmt->bindParam(':fechaoperacion'	, $cFecha	, PDO::PARAM_STR);
	

	$resultado = $stmt->execute();

	if ($resultado==true){ // Si encontro ??
		$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
		if ( $resultado==false){
			$cRegreso = "No hay saldo del banco para la cuenta $cCta en el día $cFecha";
			return false; // No se encontro
		}else{
			$cRegreso = $resultado["importeoperacion"];
			return true;	// Ya existe el Saldo 
		}
	}else{
		$cRegreso = "Error en la ejecución del SQL traeSaldo";
		return false; // No se encontro 
	}
}
// _______________________________________________________________________________	
function movimientosBanco($cCta, $cFecha, &$cRegreso){
	$cRegreso = "";

	$sql = 	"select a.idcuentabancaria, a.idoperacion, a.concepto, a.importeoperacion, a.id_layout_banco, a.fechaoperacion," .
			"a.fechaconciliacion, a.conciliado, b.fch_d_h from conci_movimientos a , conci_cata_opera b ".
			"where idcuentabancaria='$cCta' and fechaoperacion<='$cFecha' and not a.idoperacion='SDO' " .
			" and a.idoperacion=b.idoperacion " .
			" and (  (  not conciliado='S'  ) or ( conciliado='S' and fechaconciliacion > '$cFecha' )  ) " .
			" order by fechaoperacion ,fch_d_h"; // desc

	$stmt		= $this->conexion->prepare($sql);
	$resultado	=  $stmt->execute();
	if ($resultado==true){
		$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($resultado)) { // No hay movi-mientos
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
// _______________________________________________________________________________	
function eliminaMovBanco($IdMov,$cCta,&$cRegreso){
	$lRegreso = false;
    try {
        // Conexión a la base de datos (ajusta los parámetros según tu configuración)
        //$pdo = new PDO('mysql:host=localhost;dbname=tu_base_de_datos', 'tu_usuario', 'tu_contraseña');
        //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Preparar la consulta SQL
        $sql = "delete from conci_movimientos WHERE id_concimovimiento = :idMov and idcuentabancaria=:idCta";
        $stmt = $this->conexion->prepare($sql);

        // Vincular el parámetro
        $stmt->bindParam(':idMov', $IdMov, PDO::PARAM_INT);
        $stmt->bindParam(':idCta', $cCta , PDO::PARAM_STR);

        // Ejecutar la consulta
        $stmt->execute();

        // Comprobar si se eliminó algún registro
        if ($stmt->rowCount() > 0) {
            $cRegreso = "Movimiento eliminado correctamente.";
            $lRegreso = true;
        } else {
            $cRegreso = "No se encontró el movimiento con el ID proporcionado.";
        }

    } catch (PDOException $e) {
        // Manejo de errores
        $cRegreso = "Error al eliminar el movimiento: " . $e->getMessage();
    }
    return $lRegreso;
}
// _______________________________________________________________________________	
function validaReferenciaBancos($cCta,$cRefe,&$cRegreso){
	try {
		$sql  =	"select id_concimovimiento from conci_movimientos " .
				"where id_layout_banco=:referencia and idcuentabancaria=:cuenta";
		$stmt = $this->conexion->prepare($sql);

		$stmt->bindParam(':cuenta'		, $cCta		, PDO::PARAM_STR);
		$stmt->bindParam(':referencia'	, $cRefe	, PDO::PARAM_STR);

		$resultado = $stmt->execute();
		if ($resultado==true){ // Si encontro ??
			$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
			if ( $resultado==false){ // no lo encontro
				$cRegreso = "No existe la referencia $cRefe para la cuenta $cCta ";
				return true; // No se encontro
			}else{
				$cRegreso = "Ya existe la referencia $cRefe para la cuenta $cCta "; // $resultado no debe de ir es una array
				return false;	// Ya existe la referencia
			}
		}else{
			$cRegreso = "Error en la ejecución del SQL validaReferenciaBancos";
			return false; // No se encontro 
		}


	}catch (PDOException $e) {
		$cRegreso = "Error : " . $e->getMessage();
		return false;
	}
}
// _______________________________________________________________________________
function ConciliaMovBanco($cCta,$cId,$cStatus,$cFecConci,&$cRegreso){
	try{
		$sql	= "update conci_movimientos set fechaconciliacion=:fechaconciliacion, conciliado=:conciliado " .
				  "where id_concimovimiento=:idMov and idcuentabancaria=:cuenta";
		$stmt	= $this->conexion->prepare($sql);
		//
		$stmt->bindParam(':cuenta'				, $cCta		, PDO::PARAM_STR); // Sera más rápido si se especifica la cuenta
		$stmt->bindParam(':idMov'			    , $cId		, PDO::PARAM_STR); // Suficiente para encontrar el movimiento
		$stmt->bindParam(':conciliado'			, $cStatus	, PDO::PARAM_STR);

		//
		if ( $cStatus==="S"){
			$stmt->bindParam(':fechaconciliacion'	, $cFecConci	, PDO::PARAM_STR);
		}else{
			$cFecConci = null;
			$stmt->bindParam(':fechaconciliacion'	, $cFecConci	, PDO::PARAM_NULL);
		}
		//
		$resultado = $stmt->execute();
		//
		if ($resultado === true) {
			$cRegreso = "";
			return true; // Update exitoso
		}else{
			$cRegreso = "Error en el update ";
			return false; // Error al ejecutar el update
		}
	} catch (PDOException $e) {
		$cRegreso = "<<Excepción detectada " . $e->getMessage() .">>";
		return false;
	}
}
// ---------------------- Tabla Movimientos  ------------------------------------
// _______________________________________________________________________________	
function buscaReferenciaMovimientos($cCta,$cRefe,$nImpo){
	try {
		$cTabla = "atablas.t_" . trim($cCta);
		$sql	= "select idmovimiento from $cTabla where " . 
				  "idcuentabancaria=:cuenta and referenciabancaria=:referencia and  importeoperacion=:importe " . 
				  "order by idmovimiento";
		$stmt	= $this->conexion->prepare($sql);

		// Bind de los parámetros
		$stmt->bindParam(':cuenta'		, $cCta		, PDO::PARAM_STR);
		$stmt->bindParam(':referencia'	, $cRefe	, PDO::PARAM_STR);
		$stmt->bindParam(':importe'		, $nImpo	, PDO::PARAM_STR);

        // Ejecutar la consulta
        $stmt->execute();
		// Obtener resultados si es necesario
		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $resultados;
		/*
			Como se uso fetchAll en la función que invoca
			if (empty($resultados)) {
				echo "No se encontraron información.";
			} else {
				foreach ($resultados as $res) {
					echo "Resultado encontrado: " . $res['idmovimiento'];
				}
			}
		*/
	} catch (PDOException $e) {
		return "b)Error : " . $e->getMessage();
	}
} 
// _______________________________________________________________________________
// Esta se usa para conciliacion por layout, ya que existe una para movs individuales ( conciliaMovimiento )
function conciliaMovConciliacion($idMov,$fecha,$cEstatus,$cCta){ 
	try {
		//$sql	= "update conci_movimientos set fechaconciliacion=:fechaconciliacion, conciliado=:conciliado 
		//where id_concimovimiento=:id_concimovimiento ";
		$cTabla = "atablas.t_" . trim($cCta);
		$sql	= "update $cTabla set fechaconciliacion=:fechaconciliacion, conciliado=:conciliado where idmovimiento=:idMov ";
		$stmt	= $this->conexion->prepare($sql);
		//
		$stmt->bindParam(':idMov'			    , $idMov		, PDO::PARAM_STR);
		$stmt->bindParam(':fechaconciliacion'	, $fecha		, PDO::PARAM_STR);
		$stmt->bindParam(':conciliado'			, $cEstatus		, PDO::PARAM_STR);
		//
		$resultado = $stmt->execute();
		//
		if ($resultado === true) {
			return 1; // Update exitoso
		} else {
			return 0; // Error al ejecutar el update
		}
	} catch (PDOException $e) {
		return -1;
	}
}	
// _______________________________________________________________________________ 
// _______________________________________________________________________________	
function traeSaldoINE($cCta,$cFecha,&$cRegreso){
	$cRegreso 	= "";

	$sql  = "select saldoinicial+ingresos-egresos-cheques as saldofinal from saldos where " .
			"idcuentabancaria=:idcuentabancaria and " .
			"  fechasaldo <=:fechasaldo order by fechasaldo desc limit 1 ";
	$stmt = $this->conexion->prepare($sql); 

	$stmt->bindParam(':idcuentabancaria', $cCta		, PDO::PARAM_STR);
	$stmt->bindParam(':fechasaldo'		, $cFecha	, PDO::PARAM_STR);

	$resultado =  $stmt->execute();
	if ($resultado==true){ // Si encontro ??
		$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
		if ( $resultado==false){
			$cRegreso = "No hay saldo del INE para la cuenta $cCta en el día $cFecha $resultado";
			return false; // No se encontro
		}else{
			$cRegreso = $resultado["saldofinal"];
			return true;	// Ya existe el Saldo 
		}
	}else{
		$cRegreso = "Error en la ejecución del SQL traeSaldoINE";
		return false; // No se encontro 
	}
}

// _______________________________________________________________________________	
function movimientosIne($cCta, $cFecha, &$cRegreso){
	$cRegreso = "";
	$cTabla   = "atablas.t_" . trim($cCta);

	$sql = 	"select a.idcuentabancaria, b.tipo , a.beneficiario, a.concepto, a.importeoperacion, a.referenciabancaria, a.fechaoperacion, " . 
			"a.fechaconciliacion, a.conciliado from $cTabla a , operacionesbancarias b " .
			"where a.idoperacion=b.idoperacion and  idcuentabancaria='$cCta' and fechaoperacion<='$cFecha' and " .
			"(  (  not conciliado='S'  ) or ( conciliado='S' and fechaconciliacion > '$cFecha' )  ) ".
			"order by fechaoperacion, b.tipo desc ";

	$stmt		= $this->conexion->prepare($sql);
	$resultado	=  $stmt->execute();
	if ($resultado==true){
		$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($resultado)) { // No hay movi-mientos
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
// _______________________________________________________________________________
function conciliaMovimiento($cCta,$fecha,$idMov,$cEstatus,&$cRegreso){
	try {
		//$sql	= "update conci_movimientos set fechaconciliacion=:fechaconciliacion, conciliado=:conciliado 
		// where id_concimovimiento=:id_concimovimiento ";
		$cTabla = "atablas.t_" . trim($cCta);
		$sql	= "update $cTabla set fechaconciliacion=:fechaconciliacion, conciliado=:conciliado " .
				  "where idmovimiento=:idMov and idcuentabancaria=:cuenta";
		$stmt	= $this->conexion->prepare($sql);
		//
		$stmt->bindParam(':idMov'			    , $idMov		, PDO::PARAM_STR); // Suficiente para encontrar el movimiento
		$stmt->bindParam(':conciliado'			, $cEstatus		, PDO::PARAM_STR);
		$stmt->bindParam(':cuenta'				, $cCta			, PDO::PARAM_STR); // Sera más rápido si se especifica la cuenta
		//
		if ( $cEstatus==="S"){
			$stmt->bindParam(':fechaconciliacion'	, $fecha		, PDO::PARAM_STR);
			
		}else{
			$fecha = null;
			$stmt->bindParam(':fechaconciliacion'	, $fecha		, PDO::PARAM_NULL);
		}
		//
		$resultado = $stmt->execute();
		//
		if ($resultado === true) {
			$cRegreso = "";
			return true; // Update exitoso
		}else{
			$cRegreso = "Error en el update ";
			return false; // Error al ejecutar el update
		}
	} catch (PDOException $e) {
		$cRegreso = "Excepción detectada " . $e->getMessage();
		return false;
	}
}	
// _______________________________________________________________________________	
}

