<?php
	global $conn_pdo;
	require_once("con_pg_OpeFinW_.php");

// _____________________________________________________________________________
function getPermisos($clave){
    global $conn_pdo;
    $salida = false;
    $sql 	= "select idusuario from usuarios where idusuario = '$clave' and estatus = true ";
	$stmt 	= $conn_pdo->prepare($sql); 
	$stmt->execute() or die ("1 No se pudo ejecutar la consulta, $sql");
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
	foreach ($resultados as $fila) { // Si encontro información
		$salida = true;
	}
    $resultados = null;
    return $salida;
}	
// _____________________________________________________________________________
function getCampo($sql){ // El sql debe ser "select campo as salida from tabla where ....""
	global $conn_pdo;
	$salida = "";
	$stmt 	= $conn_pdo->prepare($sql);
	$stmt->execute() or die ("1 No se pudo ejecutar la consulta, $sql");
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
 	foreach ($resultados as $fila) { // Si encontro información
		$salida = $fila['salida'];
	}
    $resultados = null;
    return $salida;
}
// _____________________________________________________________________________
function ejecutaSQL_($sql){	// Regresa un arreglo
	global $conn_pdo;// Si no se lo pongo me manda error en el $sql 
	$regreso = null;
	$stmt 	 = $conn_pdo->prepare($sql);
	$stmt->execute() or die ("1 No se pudo ejecutar la consulta, $sql");
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
 	foreach ($resultados as $fila) { // Si encontro información
		$regreso[] = $fila;
	}
    $resultados = null;
    return $regreso;
}
// _____________________________________________________________________________
function ejecutaSQL_C($sql,$conexion){	// Regresa un arreglo
	$regreso = null;
	$stmt 	 = $conexion->prepare(trim($sql));
	$stmt->execute();
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
 	foreach ($resultados as $fila) { // Si encontro información
		$regreso[] = $fila;
	}
    $resultados = null;
    return $regreso;
}
// _____________________________________________________________________________
/*function ejecutaSQL_fetch($sql){	// regresa recorset
	global $conn_pdo;// Si no se lo pongo me manda error en el $sql 
	$regreso = null;
	$stmt 	= $conn_pdo->prepare($sql);
	$stmt->execute() or die ("1 No se pudo ejecutar la consulta, $sql");
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultados;
}*/
// _____________________________________________________________________________
function  actualizaSql($sql){ // Update, Insert, Delete
	global $conn_pdo;// Si no se lo pongo me manda error en el $sql 
	$regreso	= null;
	$stmt 		= $conn_pdo->prepare($sql); // Prepara el SQL
	$stmt->execute() or die ("1 No se pudo ejecutar la consulta, $sql");
	$regreso	= $stmt->rowCount();// Número de renglones afectados
	return $regreso;
}
// _____________________________________________________________________________
function contar($sql){ // Usar para contar con select 
	global $conn_pdo;// Si no se lo pongo me manda error en el $sql 
	$regreso	= null;
	$stmt 		= $conn_pdo->prepare($sql); // Prepara el SQL
	$stmt->execute() or die ("1 No se pudo ejecutar la consulta, $sql");
	$regreso	= $stmt->fetchColumn();;// Número de renglones afectados
	return $regreso;
}
// _____________________________________________________________________________
// Se utiliza para obtener la UR a partir de los datos que arroja el LDap
function getURAdscripcion($idEstado, $idDistrito, $unidadResponsable){ 
	$urAdscripcion = "";
	$idDistritoPadded = str_pad($idDistrito, 2, "0", STR_PAD_LEFT);
	$arrEstados = array(
		"0" => "OC",		"1" => "AG",		"2" => "BC",
		"3" => "BS",		"4" => "CC",		"5" => "CL",
		"6" => "CM",		"7" => "CS",		"8" => "CH",
		"9" => "MX",		"10" => "DG",		"11" => "GT",
		"12" => "GR",		"13" => "HG",		"14" => "JC",
		"15" => "MC",		"16" => "MN",		"17" => "MS",
		"18" => "NT",		"19" => "NL",		"20" => "OC",
		"21" => "PL",		"22" => "QT",		"23" => "QR",
		"24" => "SP",		"25" => "SL",		"26" => "SR",
		"27" => "TC",		"28" => "TS",		"29" => "TL",
		"30" => "VZ",		"31" => "YN",		"32" => "ZS"
	);

	$arrOficinas = array(
		"PRESIDENCIA DEL CONSEJO DEL INSTITUTO FEDERAL ELECTORAL" => "01",
		"CONSEJEROS ELECTORALES" => "02",
		"SECRETARIA EJECUTIVA" => "03",
		"COORDINACION NACIONAL DE COMUNICACION SOCIAL" => "04",
		"COORDINACION DE ASUNTOS INTERNACIONALES" => "05",
		"DIRECCION DEL SECRETARIADO" => "06",
		"CONTRALORIA GENERAL" => "07",
		"DIRECCION JURIDICA" => "08",
		"UNIDAD DE SERVICIOS DE INFORMATICA" => "09",
		"DIRECCION EJECUTIVA DEL REGISTRO FEDERAL DE ELECTORES" => "11",
		"DIRECCION EJECUTIVA DE PRERROGATIVAS Y PARTIDOS POLITICOS" => "12",
		"DIRECCION EJECUTIVA DE ORGANIZACION ELECTORAL" => "13",
		"DIRECCION EJECUTIVA DEL SERVICIO PROFESIONAL ELECTORAL" => "14",
		"DIRECCION EJECUTIVA DE CAPACITACION ELECTORAL Y EDUCACION CIVICA" => "15",
		"DIRECCION EJECUTIVA DE ADMINISTRACION" => "16",
		"UNIDAD TECNICA DE TRANSPARENCIA Y PROTECCION DE DATOS PERSONALES" => "18",
		"UNIDAD TECNICA DE FISCALIZACION" => "20",
		"UNIDAD TECNICA DE PLANEACION" => "21",
		"UNIDAD TECNICA DE IGUALDAD DE GENERO Y NO DISCRIMINACION" => "22",
		"UNIDAD TECNICA DE VINCULACION CON LOS ORGANISMOS PUBLICOS LOCALES" => "23"
	);

    if($idEstado != "0"){
			$urAdscripcion = $arrEstados[$idEstado] . $idDistritoPadded;
    } else {
		$urAdscripcion = "OF".$arrOficinas[$unidadResponsable];
    }
    
    return $urAdscripcion;
}
// _____________________________________________________________________________
function bitacora($conexion,$vUsr,$vCtaBan,$vPanta,$vOpera,$vImpo){
	$sql = 	"INSERT INTO bitacora( " .
			"idusuario, idcuentabancaria, pantalla, operacion, importe) ".
			"VALUES (:idusuario, :idcuentabancaria, :pantalla, :operacion, :importe)";
	$stmt = $conexion->prepare($sql);
	$stmt->bindParam(':idusuario'			, $vUsr 	, PDO::PARAM_STR);
	$stmt->bindParam(':idcuentabancaria'	, $vCtaBan	, PDO::PARAM_STR);
	$stmt->bindParam(':pantalla'			, $vPanta	, PDO::PARAM_STR);
	$stmt->bindParam(':operacion'			, $vOpera	, PDO::PARAM_STR);
	$stmt->bindParam(':importe'				, $vImpo	);
	//
	return $stmt->execute();
}
// _____________________________________________________________________________
function convierteFecha($cFecha){ // cFecha en frmato dd/mm/yyyy
	return strtotime(str_replace('/', '-', $cFecha));
}
// _____________________________________________________________________________
function volteFecha($cFecha){ // de dd/mm/yyyy a yyyy-mm-dd
	return substr($cFecha,-4) . "-" . substr($cFecha, 3,2) . "-" . substr($cFecha,0,2);
}
// _____________________________________________________________________________
function ddmmyyyy($cFecha){ // de yyyy-mm-dd a dd/mm/yyyy
	$cFecha = trim($cFecha);
	$cFecha = substr($cFecha,-2) . "/" . substr($cFecha,5,2) . "/" . substr($cFecha,0,4);
	return $cFecha;
}
// _____________________________________________________________________________
function ejecutaSQL_conn_pg($conn_pg,$sql){
	//echo $sql;
	$regreso = null;
	$rst1 = pg_query($conn_pg, $sql) or die ("Error en " . "\n" . $sql);;
	if ($rst1){
		if (pg_num_rows($rst1)>0 ){
			$regreso = array();
			while ($row = pg_fetch_row($rst1, NULL, PGSQL_ASSOC)) {
				$regreso[] = $row;
			}
			//var_dump($regreso);
		}
	}
	return $regreso;
}
// _____________________________________________________________________________
function conComas($cNumero,$nDeimal=2){
	return number_format($cNumero, $nDeimal, '.', ',');
}
// _____________________________________________________________________________
function abreArchivoPdf($cArch){
	$cArch = "../" . $cArch;
	// Verificar si el archivo existe
	if (file_exists($cArch)) {
	    // Configurar las cabeceras para indicar que se trata de un archivo PDF
	    header('Content-Type: application/pdf');
	    header('Content-Disposition: inline; filename="' . basename($cArch) . '"');
	    header('Content-Transfer-Encoding: binary');
	    header('Content-Length: ' . filesize($cArch));

	    // Enviar el contenido del archivo al navegador
	    readfile($cArch);
	} else {
	    // El archivo no existe
	    echo "El archivo no existe. [" . $cArch . "]";
	}
}
// _____________________________________________________________________________
function arregloCtasBancarias($cCtaIni,$cCtaFin){
    $sql  = "select idcuentabancaria from cuentasbancarias where " .
            "idcuentabancaria>='$cCtaIni' and idcuentabancaria<='$cCtaFin' order by idcuentabancaria"; 
    $aDat = ejecutaSQL_($sql);
    return $aDat;
}
// _____________________________________________________________________________
function strFecha($cFecha){
	return strftime( "%d de " . mesesEspanol(date("m", strtotime($cFecha))) . " del %Y", strtotime($cFecha));
}
// _____________________________________________________________________________
function mesesEspanol($mes) {
  $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
  return $meses[$mes - 1];
}	
// _____________________________________________________________________________
function fechaLetra($cFecha){
	list($vAnio, $vMes, $vDia) = explode('-', $cFecha);
	return $vDia . " DE " . strtoupper(mesesEspanol($vMes)) . " DEL " . $vAnio;
}
// _____________________________________________________________________________
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Solo manejar errores de tipo NOTICE y WARNING
    if ($errno == E_NOTICE || $errno == E_WARNING) {
        // Lanzar una excepción con la información del error
        throw new Exception("Error: [$errno] $errstr en $errfile:$errline");
    }
    // Deja que el manejador de errores predeterminado maneje otros errores
    return false;
}
// _____________________________________________________________________________
function ipRepo(){
	$ip = $_SERVER['REMOTE_ADDR'];
	return "R_" . str_replace(".", "", $ip) . ".pdf";
}
// _____________________________________________________________________________
// ___________________________________________________________________________
function logoInstitucional1($pdf){ // Para impresión en pdf
    $imagePath = '../assetsF/img/ine_logo_pdf.jpg';
    $x 			= 5;
    $y 			= 5;
    $width 		= 30;
    $height 	= 0; // 0 para mantener la proporción del tamaño original
    $pdf->Image($imagePath, $x, $y, $width, $height);
}
// _____________________________________________________________________________
// _____________________________________________________________________________
function utf8($cadena,$codOrigen='ISO-8859-1'){
	return mb_convert_encoding($cadena,$codOrigen,'UTF-8');
}
// _____________________________________________________________________________
function calculaTamanio($result){
	$total_size = ""; // 0;
	$i = pg_num_fields($result);
	for ($j = 0; $j < $i; $j++) {
		//$total_size += pg_field_size($result, $j);
		//$size = pg_field_size($result, $j);
		//$total_size .= "Campo " . pg_field_name($result, $j) . " es: $size bytes\n";

		$field_value = pg_fetch_result($result, 0, $j); // Primer registro (índice 0) y campo $j
        $size = strlen($field_value);  // Calcular el tamaño real en bytes
        //echo "El tamaño real del campo " . pg_field_name($result, $j) . " es: $size bytes\n";
        $total_size += $size;

	}      
    return $total_size;
}
// _____________________________________________________________________________
function nombreTabla($cCtaBan){
	return "atablas.t_" . trim($cCtaBan);
}
// _____________________________________________________________________________
function tieneMovimientos($cCampo,$cValor,&$r){ // Revisa si existe información en todas las tablas del esquema atablas
    $sql	= "select table_name from information_schema.tables where table_name like 't_%' and table_schema = 'atablas'";
	$tablas = ejecutaSQL_($sql);

    foreach ($tablas as $tabla) {
        $tableName = $tabla['table_name'];
        
        // Construir la consulta dinámica para verificar el valor en el campo
        $sql = "Select COUNT(*) as cantidad from atablas.$tableName where $cCampo = '$cValor'";
        $reg = ejecutaSQL_($sql);
        $r["tm"] = $sql;
        $r["tm1"] = $reg;

        if ( $reg[0]["cantidad"] > 0 ){
        	return true; // Existe información
        }
        
    }
    return false;
    
}
// _____________________________________________________________________________

?>