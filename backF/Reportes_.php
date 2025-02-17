<?php
/*
* * * * * * * * * * * * * * * * * * * * * * * * * 
* Autor   : Miguel Ángel Bolaños Guillén        *
* Sistema : Sistema de Operación Bancaria Web   *
* Fecha   : Enero 2024							*
* Descripción : Rutinas para ejecutar codigo    * 
*               SQL para interacturar con los   *
*               Saldos y movimientos de la BD   *
*               del Sistema.                    *
*               Unadm-Proyecto Terminal         *
* * * * * * * * * * * * * * * * * * * * * * * * *  */
try{
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
// ________________________________________________________________________________________
	require_once("con_pg_OpeFinW1_.php");
	require_once "../backF/rutinas_.php";	
	require_once "../pdoF/metodos_.php";
	require_once("Pagina_y_Busca_.php");   
// ________________________________________________________________________________________	
	date_default_timezone_set('America/Mexico_City');
	session_start(); // variables de sesión  
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php");exit; return;
	}
	// _______________________________________
	$idUsuario     	= $_SESSION['OpeFinClave'];
	$esquemaUsuario = $_SESSION['OpeFinEsquema'];
	$ip 			= $_SERVER['REMOTE_ADDR'];
	// _________________________________________
	$respuesta = array(	'success'=>false , 'mensaje'=>""	 , 'resultados'=>array(), 'opcion'=>array() , 'ctas'=>array(),
						'sql'=>array()   , 'opera'=>array()  , 'ctrl'=>array()      , 'datos'=>array()  , 'tipoMov'=>"",
						'_trace'=>""	 , 'dummy'=>"");
	// Lee el cuerpo de la solicitud HTTP
	$jsonData = file_get_contents('php://input');
	// Decodifica los datos JSON en un array asociativo
	$aParametros 		 			= json_decode($jsonData, true);
	$vOpc 				 			= $aParametros["opcion"]; 
	$aParametros["idUsuario"] 	 	= $idUsuario;
	$aParametros["esquemaUsuario"]	= $esquemaUsuario;
	$aParametros["reporte"]			= "R_" . str_replace(".", "", $ip) . ".pdf";
	$aParametros["csv"]				= "R_" . str_replace(".", "", $ip) . ".csv";
	$respuesta["datos"]  			= $aParametros; // Debe de ir para que se identifique en el regreso del PHP al JS
	$respuesta["opcion"]  			= $aParametros; // Debe de ir para que se identifique en el regreso del PHP al JS
	//$respuesta["datos"]  = array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);
	// ___________________________________________
	switch ($vOpc) {
		// _______________________________________
		case "EdoPosFinDia":
			rEdoPosFinDia($respuesta);
		break;
		// _______________________________________
		case "EdoPosFinMensual":
			rEdoPosFinMensual($respuesta);
		break;
		// _______________________________________
		case "ConsolidadoGeneral":
			// No quitar solo así funciono, para el consolidado de algún
			// modo en el echo json_encode($respuesta); la respuesta llegaba vacía a JS 
			$respuesta1 = $respuesta;
			rConsolidadoGeneral($respuesta1); 	// Mando la copia de $respuesto a procrsar el reporte
			$respuesta["success"] 	= true;		// Recupero algunos valores necesarios o para depurar
			//$respuesta["mensaje"]	= "ok";
			$respuesta["datos"]		= $respuesta1["datos"];
			$respuesta["resultados"]= $respuesta1["resultados"];
			$respuesta["archivo"]	= $respuesta1["archivo"];
			//$respuesta["aCtas1"]	= $respuesta1["aCtas1"]; si se activa no se genera el pdf
			$respuesta["aCtas"]		= $respuesta1["aCtas"];

		break;
		// _______________________________________
		case "genReciboIngreso":
			genReciboIngreso($respuesta);
		break;
		// _______________________________________
		case "imprimeCheque":
			imprimeCheque($respuesta);
		break;
		// _______________________________________
		case "revisaCheque":
			revisaCheque($respuesta);
		break;
		// _______________________________________
		case 'ConsultaMovimientosBancarios':
			$respuesta["success"] = BuscaYPagina($respuesta["opcion"]);
			$respuesta["mensaje"] = "";
		break;
		// _______________________________________
		case "existeCheque":
			existeCheque($respuesta);
		break;
		// _______________________________________
		case "ImpresionRangoCheques":
			ImpresionRangoCheques($respuesta);
		break;
		// _______________________________________
		case "ReporteSaldos":
			ReporteSaldos($respuesta);
		break;
		// _______________________________________
		case "EdoCta":
			EstadoCuenta($respuesta);
		break;
		// _______________________________________
		case "InteresesPdf":
			InteresesPdf($respuesta);
		break;
		// _______________________________________
		case "CtasOF16":
			CtasOF16($respuesta);
		break;
		// _______________________________________
		case "RespuestaPdf":
			RespuestaPdf($respuesta);
		break;
		// _______________________________________
		default:
			$respuesta["mensaje"] = "No esta definida en Reportes_.php [" . $vOpc . "]";
		break;
	}
	// ___________________________________________
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);
return;
}catch(Exception $e){
	var_dump($e->getMessage());
}
//	_______________________________________________________________________________________
function rEdoPosFinDia(&$respuesta){
	require_once "repo/EdoPosFinDia_.php";
	global $conn_pg;
	$cCta   = $respuesta["datos"]["cCta"];
	$cFecha = $respuesta["datos"]["cFecha"];
	$cSalida= $respuesta["datos"]["salida"];
	$cTabla = "atablas.t_" . trim($cCta);
	$sql 	= "select a.referenciabancaria, a.folio, a.beneficiario, a.concepto,a.importeoperacion, b.tipo, ".
			  " a.idunidad, a.idoperacion, a.idcontrol, a.anioejercicio, a.fechaoperacion " .
			  "from $cTabla a , operacionesbancarias b " .
			  "where a.idcuentabancaria='$cCta' and a.fechaoperacion='$cFecha' and a.idoperacion=b.idoperacion ".
			  "order by b.tipo desc, a.fechaalta, a.folio, a.referenciabancaria ";
	// 
    metodos::SaldoAnterior($respuesta); // lo guarda en $respuesta["datos"]["saldoAnterior"]
	//
    $respuesta["sql"] = $sql;
    $resultado 		  = ejecutaSQL_conn_pg($conn_pg,$sql);

    //
    $respuesta["resultados"] = $resultado; // quitar lo que se requiere es generar el pdf 
    if ($resultado==null){
    	$respuesta["mensaje"] = "No se encuentra información del día y cuenta solicitada";
    	return;
    }
	if ($cSalida=="Pdf"){
    	pdfEdoPosFinDia($respuesta);
	}else{
		xlsEdoPosFinDia($respuesta,false);
	}
}
// ________________________________________________________________________________________
function rEdoPosFinMensual(&$res){
	try{
		require_once "repo/EdoPosFinMensual_.php";
		global $conn_pg;
		$cCta    = $res["datos"]["cCta"];
		$cFecIni = $res["datos"]["cAnio"] . "-" . $res["datos"]["cMes"] . "-01";
		$cFecFin = $res["datos"]["cAnio"] . "-" . $res["datos"]["cMes"] . "-" . $res["datos"]["cDias"];
		$cSalida = $res["datos"]["salida"];
		$cTabla	 = "atablas.t_" . trim($cCta);

		$res["datos"]["cFecha"] = $cFecIni;
	    metodos::SaldoAnterior($res); // lo guarda en $res["datos"]["saldoAnterior"]

		$sql 	= "select a.referenciabancaria, a.folio, a.beneficiario, a.concepto,a.importeoperacion, b.tipo, ".
				  " a.idunidad, a.idoperacion, a.idcontrol, a.anioejercicio,a.fechaoperacion " .
				  "from $cTabla a , operacionesbancarias b " .
				  "where a.idcuentabancaria='$cCta' and a.fechaoperacion>='$cFecIni' and a.fechaoperacion<='$cFecFin' and a.idoperacion=b.idoperacion ".
				  "order by a.fechaoperacion, b.tipo desc, a.folio, a.referenciabancaria ";

	    $res["sql"] = $sql;
	    $resultado	= ejecutaSQL_conn_pg($conn_pg,$sql);

	    $res["resultados"] = $resultado; // quitar lo que se requiere es generar el pdf 
	    if ($resultado==null){
	    	$res["mensaje"] = "No se encuentra información del año-mes y cuenta solicitada";
	    	return;
	    }
		if ($cSalida=="Pdf"){
			pdfEdoPosFinMensual($res);
		}else{
			xlsEdoPosFinMensual($res);
		}
	}catch(Exception $e){
		$res["mensaje"] = $e->getMessage();
		return false;
	}
}
// ________________________________________________________________________________________
function rConsolidadoGeneral(&$res){
	require_once "repo/ConsolidadoGeneral_.php";
	traeSaldosAnteriores($res);			
	traeOperacionesHoy($res);
	pdfConsolidadoGeneral($res);
	//var_dump($res);	
	$res["mensaje"] = "Se generó la información";
	return true;
}
// ________________________________________________________________________________________
// ________________________________________________________________________________________
function traeSaldosAnteriores(&$resp){
	global $conn_pg;
	$sql  = "select idcuentabancaria, nombre from cuentasbancarias ";
	$cFil = $resp["datos"]["cTipo"];
	$cDia = $resp["datos"]["cFecha"];
	if ($cFil=="A"){
		$sql .= " where estatus=true ";
	}elseif ($cFil=="I"){
		$sql .= " where estatus=false ";
	}
	// Se obtiene un arreglo con el numero de cta y nombre
	$resp["sql"] 	 = $sql;
	$aCtas 			 = ejecutaSQL_conn_pg($conn_pg,$sql);
	$nSaldo1		 = 0.00;
	// Se agrega saldos Anteriores a la fecha específica
	for($i=0;$i<count($aCtas);$i++){
		$resp["datos"]["cCta"] = $aCtas[$i]["idcuentabancaria"];
		metodos::SaldoAnterior($resp); // lo guarda en $respuesta["datos"]["saldoAnterior"]
		$nSaldo = floatval($resp["datos"]["saldoAnterior"]);
		$aCtas[$i]["saldoAnterior"] = $nSaldo;
		$nSaldo1 = $nSaldo + $nSaldo1;
	}
	$resp["aCtas"]					= $aCtas;
	$resp["datos"]["saldosCuentas"] = $nSaldo1;
}
// ________________________________________________________________________________________
function traeOperacionesHoy(&$resp){
	global $conn_pg;
	$cDia  = $resp["datos"]["cFecha"];
	$cFil  = $resp["datos"]["cTipo"];
//
	$where1= "";	 // Todas las cuentas
	if ($cFil=="A"){ // Solo cuentas activas
		$where1 = " and c.estatus=true ";
	}elseif ($cFil=="I"){ // Solo cuentas inactivas
		$where1 = " and c.estatus=false ";
	}
	// Itera sobre todas las tablas de atablas
	$aSCtas = [];  // Inicializar como un arreglo vacío para almacenar todos los resultados
	foreach ($resp["aCtas"] as $iCta) {
		$cCta	= trim($iCta["idcuentabancaria"]);
		$cTabla = "atablas.t_" .$cCta;
		$sql	= "select c.idcuentabancaria, b.nombre, b.tipo, sum(a.importeoperacion) ".
					"from operacionesbancarias b, $cTabla a , cuentasbancarias c " .
					"where b.idoperacion = a.idoperacion and c.idcuentabancaria=a.idcuentabancaria and a.fechaoperacion='$cDia' ".
					$where1 . 
					"group by c.idcuentabancaria, b.nombre, b.tipo ".
					"order by c.idcuentabancaria, b.tipo desc ";
		$aSCtas1= ejecutaSQL_conn_pg($conn_pg,$sql);
	    // Verificar si la consulta devuelve resultados
    	if ($aSCtas1) {
	        // Agregar cada fila de los resultados de la consulta a $aSctas1
	        foreach ($aSCtas1 as $row) {
	            $aSCtas[] = $row;  // Agrega cada fila al arreglo $aSctas1
	        }
	    }
	}
	//


	$nSaldo = 0.00; $nIngresos = 0.00 ; $nEgresos = 0.00;
	// Acumular importes del día a los saldos anteriores de cada cuenta
	$cCtAnt = $aSCtas[0]["idcuentabancaria"];
	for($i=0;$i<count($aSCtas);$i++){
		//
		//
		$cCtaN = $aSCtas[$i]["idcuentabancaria"];
		$nImpo = floatval($aSCtas[$i]["sum"]);
		$cTipo = $aSCtas[$i]["tipo"];
		if ($cCtAnt!=$cCtaN){// Vacía el saldo en la cuenta anterior correspondiente
			for ($j=0 ; $j<count($resp["aCtas"]);$j++){
				if ($cCtAnt==$resp["aCtas"][$j]["idcuentabancaria"]){
					$nSalAnt = floatval($resp["aCtas"][$j]["saldoAnterior"]);
					$resp["aCtas"][$j]["SaldoFinal"] = $nSalAnt + $nSaldo;
				}
			}			
			$nSaldo = 0.00;
			$cCtAnt = $cCtaN;
		}
		if ($cTipo=="I"){
			$nIngresos	+= $nImpo;
			$nSaldo 	+= $nImpo;
		}else{
			$nSaldo		-= $nImpo;
			$nEgresos	+= $nImpo;
		}
	}
	for ($j=0 ; $j<count($resp["aCtas"]);$j++){
		if ($cCtAnt==$resp["aCtas"][$j]["idcuentabancaria"]){
			$nSalAnt = floatval($resp["aCtas"][$j]["saldoAnterior"]);
			$resp["aCtas"][$j]["SaldoFinal"] = $nSalAnt + $nSaldo;
		}
	}
//
	$acumuladoPorNombre = [];

	// Recorrer el array original y acumular por nombre de operación
	foreach ($aSCtas as $cuenta) {
	    $nombre  = $cuenta['nombre'];
	    $importe = floatval($cuenta['sum']); // Convertir el importe a número
	    $tipo    = $cuenta['tipo'];

	    // Verificar si el nombre ya está en el array acumulado
	    $encontrado = false;
	    foreach ($acumuladoPorNombre as &$acumulado) { // el & es para que pueda modificar 
	        if ($acumulado['nombre'] === $nombre && $acumulado['tipo'] === $tipo) {
	            // Acumular el importe
	            $acumulado['importe'] += $importe;
	            $encontrado = true;
	            break;
	        }
	    }

	    if (!$encontrado) {
	        // Agregar una nueva entrada al array acumulado
	        $acumuladoPorNombre[] = [
	            'nombre'  => $nombre,
	            'importe' => $importe,
	            'tipo'    => $tipo,
	        ];
	    }
	}
	$nSalAntCtas = $resp["datos"]["saldosCuentas"];
	//$resp["acum"] = $acumuladoPorNombre;
	// Arreglo final para el PDF  number_format($nSaldo, 2, '.', ',');
	$aPdf = [
		["a01",utf8("SALDOS EN BANCOS DEL DÍA ANTERIOR"),"", conComas($nSalAntCtas) ],
		["a02","","",""],
		["a03","","",""],
		["b00",utf8("MÁS DEPÓSITOS POR CONCEPTO DE :")		,""		,conComas($nIngresos)],
		["d00",utf8("MENOS EROGACIONES POR CONCEPTO DE :")	,""		,conComas($nEgresos)]
	];
	$nI=1;$nE=1;//Contadores
	foreach($acumuladoPorNombre as $acum){
		if ($acum["tipo"]=="I"){
			$cI   	= sprintf("b%02d", $nI);
			$aPdf[] = [$cI, utf8($acum["nombre"]), conComas($acum["importe"]), ""];
			$nI   	+= 1;
		}else{
			$cE	  	= sprintf("d%02d", $nE);
			$aPdf[] = [$cE, utf8($acum["nombre"]), conComas($acum["importe"]), ""];
			$nE   	+= 1;		
		}
	}
	$aPdf[] = ["c98","","_._",""];
	$aPdf[] = ["c99","","",""];
	$aPdf[] = ["d97","","_._",""];
	$aPdf[] = ["d98","","","_._"];
	$aPdf[] = ["d99","","",""];
	$aPdf[] = ["e01",utf8("SALDO DE BANCOS DEL DÍA QUE SE REPORTA :"),"",conComas($nSalAntCtas+$nIngresos-$nEgresos)];
	$aPdf[] = ["e97","","","_2.2_"];
	$aPdf[] = ["e98","","",""];

	// Ordenar el arreglo usando la función de comparación
	usort($aPdf, 'compararPorPrimeraColumna');

	$arrayModificado = [];

	foreach ($aPdf as $subarray) {
    	// Eliminar el primer elemento
    	array_shift($subarray);
    
    	// Agregar el subarray modificado al nuevo array
    	$arrayModificado[] = $subarray;
	}
	$resp["aPdf"] = $arrayModificado;
//	Verificar si hay Saldo final en el arreglo
	for ($j=0 ; $j<count($resp["aCtas"]);$j++){
		if ( !isset($resp["aCtas"][$j]["SaldoFinal"]) ){
			$resp["aCtas"][$j]["SaldoFinal"] = $resp["aCtas"][$j]["saldoAnterior"];
		}
	}
//	
	$arrayModificado 	= []; $nTotal=0.00;
	$arrayModificado[]	= ["","RESUMEN DE SALDOS POR BANCO"	,"CHEQUES", utf8("*INVERSIÓN"), "TOTAL"	];
	$arrayModificado[]	= ["",""							,""		  , ""						 , ""		];
	//print_r($resp["aCtas"]);
	foreach($resp["aCtas"] as $w){
		$nTotal				+= $w["SaldoFinal"];
		$arrayModificado[]  = [$w["idcuentabancaria"], utf8(substr($w["nombre"],0,22)) , conComas($w["SaldoFinal"]) , "" , conComas($w["SaldoFinal"]) ];
	}
	$arrayModificado[] = [ "" , "" 			, "_._" , "_._" , "_._"];
	$arrayModificado[] = [ "" , "T O T A L" , conComas($nTotal) , "" , conComas($nTotal)];
	$arrayModificado[] = [ "" , "" 			, "_2.2_" , "_2.2_" , "_2.2_"];
	$resp["aCtas1"]    = $arrayModificado;

}
// ________________________________________________________________________________________
function traeOperacionesHoyBorrarDespues(&$resp){
	global $conn_pg;
	$cDia  = $resp["datos"]["cFecha"];
	$cFil  = $resp["datos"]["cTipo"];
//
	$where1= "";
	if ($cFil=="A"){ // Solo cuentas activas
		$where1 = " and c.estatus=true ";
	}elseif ($cFil=="I"){ // Solo cuentas inactivas
		$where1 = " and c.estatus=false ";
	}
	$sql =  "select c.idcuentabancaria, b.nombre, b.tipo, sum(a.importeoperacion) ".
			"from operacionesbancarias b, movimientos a , cuentasbancarias c " .
			"where b.idoperacion = a.idoperacion and c.idcuentabancaria=a.idcuentabancaria and a.fechaoperacion='$cDia' ".
			$where1 . 
			"group by c.idcuentabancaria, b.nombre, b.tipo ".
			"order by c.idcuentabancaria, b.tipo desc ";
	//$r["sql"] .= "\n<br> " . $sql;
	$aSCtas 	= ejecutaSQL_conn_pg($conn_pg,$sql);
	//$r["sCtas"] = $aSCtas;
//
	$nSaldo = 0.00; $nIngresos = 0.00 ; $nEgresos = 0.00;
	// Acumular importes del día a los saldos anteriores de cada cuenta
	$cCtAnt = $aSCtas[0]["idcuentabancaria"];
	for($i=0;$i<count($aSCtas);$i++){
		$cCtaN = $aSCtas[$i]["idcuentabancaria"];
		$nImpo = floatval($aSCtas[$i]["sum"]);
		$cTipo = $aSCtas[$i]["tipo"];
		if ($cCtAnt!=$cCtaN){// Vacía el saldo en la cuenta anterior correspondiente
			for ($j=0 ; $j<count($resp["aCtas"]);$j++){
				if ($cCtAnt==$resp["aCtas"][$j]["idcuentabancaria"]){
					$nSalAnt = floatval($resp["aCtas"][$j]["saldoAnterior"]);
					$resp["aCtas"][$j]["SaldoFinal"] = $nSalAnt + $nSaldo;
				}
			}			
			$nSaldo = 0.00;
			$cCtAnt = $cCtaN;
		}
		if ($cTipo=="I"){
			$nIngresos	+= $nImpo;
			$nSaldo 	+= $nImpo;
		}else{
			$nSaldo		-= $nImpo;
			$nEgresos	+= $nImpo;
		}
	}
	for ($j=0 ; $j<count($resp["aCtas"]);$j++){
		if ($cCtAnt==$resp["aCtas"][$j]["idcuentabancaria"]){
			$nSalAnt = floatval($resp["aCtas"][$j]["saldoAnterior"]);
			$resp["aCtas"][$j]["SaldoFinal"] = $nSalAnt + $nSaldo;
		}
	}
//
	$acumuladoPorNombre = [];

	// Recorrer el array original y acumular por nombre de operación
	foreach ($aSCtas as $cuenta) {
	    $nombre  = $cuenta['nombre'];
	    $importe = floatval($cuenta['sum']); // Convertir el importe a número
	    $tipo    = $cuenta['tipo'];

	    // Verificar si el nombre ya está en el array acumulado
	    $encontrado = false;
	    foreach ($acumuladoPorNombre as &$acumulado) { // el & es para que pueda modificar 
	        if ($acumulado['nombre'] === $nombre && $acumulado['tipo'] === $tipo) {
	            // Acumular el importe
	            $acumulado['importe'] += $importe;
	            $encontrado = true;
	            break;
	        }
	    }

	    if (!$encontrado) {
	        // Agregar una nueva entrada al array acumulado
	        $acumuladoPorNombre[] = [
	            'nombre'  => $nombre,
	            'importe' => $importe,
	            'tipo'    => $tipo,
	        ];
	    }
	}
	$nSalAntCtas = $resp["datos"]["saldosCuentas"];
	//$resp["acum"] = $acumuladoPorNombre;
	// Arreglo final para el PDF  number_format($nSaldo, 2, '.', ',');
	$aPdf = [
		["a01",utf8("SALDOS EN BANCOS DEL DÍA ANTERIOR"),"", conComas($nSalAntCtas) ],
		["a02","","",""],
		["a03","","",""],
		["b00",utf8("MÁS DEPÓSITOS POR CONCEPTO DE :")		,""		,conComas($nIngresos)],
		["d00",utf8("MENOS EROGACIONES POR CONCEPTO DE :")	,""		,conComas($nEgresos)]
	];
	$nI=1;$nE=1;//Contadores
	foreach($acumuladoPorNombre as $acum){
		if ($acum["tipo"]=="I"){
			$cI   	= sprintf("b%02d", $nI);
			$aPdf[] = [$cI, utf8($acum["nombre"]), conComas($acum["importe"]), ""];
			$nI   	+= 1;
		}else{
			$cE	  	= sprintf("d%02d", $nE);
			$aPdf[] = [$cE, utf8($acum["nombre"]), conComas($acum["importe"]), ""];
			$nE   	+= 1;		
		}
	}
	$aPdf[] = ["c98","","_._",""];
	$aPdf[] = ["c99","","",""];
	$aPdf[] = ["d97","","_._",""];
	$aPdf[] = ["d98","","","_._"];
	$aPdf[] = ["d99","","",""];
	$aPdf[] = ["e01",utf8("SALDO DE BANCOS DEL DÍA QUE SE REPORTA :"),"",conComas($nSalAntCtas+$nIngresos-$nEgresos)];
	$aPdf[] = ["e97","","","_2.2_"];
	$aPdf[] = ["e98","","",""];

	// Ordenar el arreglo usando la función de comparación
	usort($aPdf, 'compararPorPrimeraColumna');

	$arrayModificado = [];

	foreach ($aPdf as $subarray) {
    	// Eliminar el primer elemento
    	array_shift($subarray);
    
    	// Agregar el subarray modificado al nuevo array
    	$arrayModificado[] = $subarray;
	}
	$resp["aPdf"] = $arrayModificado;
//	Verificar si hay Saldo final en el arreglo
	for ($j=0 ; $j<count($resp["aCtas"]);$j++){
		if ( !isset($resp["aCtas"][$j]["SaldoFinal"]) ){
			$resp["aCtas"][$j]["SaldoFinal"] = $resp["aCtas"][$j]["saldoAnterior"];
		}
	}
//	
	$arrayModificado 	= []; $nTotal=0.00;
	$arrayModificado[]	= ["","RESUMEN DE SALDOS POR BANCO"	,"CHEQUES", utf8("*INVERSIÓN"), "TOTAL"	];
	$arrayModificado[]	= ["",""							,""		  , ""						 , ""		];
	//print_r($resp["aCtas"]);
	foreach($resp["aCtas"] as $w){
		$nTotal				+= $w["SaldoFinal"];
		$arrayModificado[]  = [$w["idcuentabancaria"], utf8(substr($w["nombre"],0,22)) , conComas($w["SaldoFinal"]) , "" , conComas($w["SaldoFinal"]) ];
	}
	$arrayModificado[] = [ "" , "" 			, "_._" , "_._" , "_._"];
	$arrayModificado[] = [ "" , "T O T A L" , conComas($nTotal) , "" , conComas($nTotal)];
	$arrayModificado[] = [ "" , "" 			, "_2.2_" , "_2.2_" , "_2.2_"];
	$resp["aCtas1"]    = $arrayModificado;

}
// ________________________________________________________________________________________
function compararPorPrimeraColumna($a, $b) {
    return strcmp($a[0], $b[0]);
}
// ________________________________________________________________________________________
function genReciboIngreso(&$respuesta){
	require_once "repo/ReciboIngresos_.php";
	$sql = "select descripcion, valor from configuracion where idconfiguracion='10'";
	$aVal= ejecutaSQL_($sql);
	if ($aVal!==null){
		$respuesta["datos"]["depto"]  	= utf8($aVal[0]["descripcion"]);
		$respuesta["datos"]["empleado"] = utf8($aVal[0]["valor"]);
	}else{
		$respuesta["datos"]["depto"] 	= utf8("DEPARTAMENTO DE TESORERÍA");
		$respuesta["datos"]["empleado"] = "Falta definir firma";
	}
	pdfReciboIngreso($respuesta);
	$respuesta["mensaje"] = "Se generó la información";
	return true;
}
// ________________________________________________________________________________________
function imprimeCheque(&$respuesta){
	require_once "repo/ImprimeCheque_.php";
	$cCta 	= $respuesta["datos"]["idCuenta"]; 
	$sql 	= "select * from frx where idcuentabancaria='$cCta' order by posicion";
	$aVal	= ejecutaSQL_($sql);
	$cTabla = "atablas.t_" . trim($cCta);
	if ($aVal!=null){

		$respuesta["resultados"] = $aVal;

		$cId  = $respuesta['datos']['idCheque']; 
		$sql  = "select fechaoperacion as fecha, beneficiario, importeoperacion as importe, concepto, " .
			    "referenciabancaria as cheque, folio as somire from $cTabla where idmovimiento=$cId ";
		$aVal = ejecutaSQL_($sql);
		$aVal[0]["fecha"]   = fechaLetra($aVal[0]["fecha"]);
		$respuesta["opera"] = $aVal;
		pdfImprimeCheque($respuesta);
	}else{
		$respuesta["mensaje"] = "Falta definir formato de cheque para la cuenta $cCta ";
	}
	return true;

}
// ________________________________________________________________________________________
function ImpresionRangoCheques(&$res){
	require_once("../pdo/NumeroALetras_.php");
	require_once("repo/ImprimeRangoCheques_.php");
	try{
		$cCta	= $res["datos"]["idCuenta"];
		$sql 	= "select * from frx where idcuentabancaria='$cCta' order by posicion";
		$aVal	= ejecutaSQL_($sql);
		$cTabla = "atablas.t_" . trim($cCta);
		if ($aVal!==null){
			$conv	 			= new NumeroALetras();
			$conv->apocope 		= true;
			$res["resultados"]	= $aVal;

			$cCheIni = $res["datos"]["cheIni"];
			$cCheFin = $res["datos"]["cheFin"];
			$sql	 = "select fechaoperacion as fecha, beneficiario, importeoperacion as importe, concepto, " .
				       "referenciabancaria as cheque, folio as somire, 'letra' as letraImp from $cTabla where  " .
				       "referenciabancaria>='$cCheIni' and referenciabancaria<='$cCheFin' and idcuentabancaria='$cCta' ".
				       "order by referenciabancaria";
			$aVal 	 = ejecutaSQL_($sql);
			if ($aVal!==null){
				foreach ($aVal as &$mov) {
					$mov["fecha"]		= fechaLetra($mov["fecha"]);
					$mov["letraimp"]	= $conv->toInvoice($mov["importe"], 2, 'Pesos');
					// code...
				}
				$res["opera"]= $aVal;
				pdfImprimeRangoCheques($res);
			}else{
				$res["mensaje"] = "No se encontró información [$cCheIni - $cCheFin] ";
			}
		}else{
			$res["mensaje"] = "Falta definir formato de cheque para la cuenta $cCta ";
		}
	}catch(Exception $e){
		$res["mensaje"] = $e->getMessage();
		return false;
	}
	
}
// ________________________________________________________________________________________
function revisaCheque(&$respuesta){
	try{
		$cCheque = $respuesta["datos"]["numCheque"];
		$cCheque = pg_escape_string($cCheque);
		$cCta	 = $respuesta["datos"]["idCuenta"];
		$cId  	 = $respuesta['datos']['idCheque']; 
		$cTabla	 = "atablas.t_" . trim($cCta);
		$sql 	 = "select referenciabancaria as cheque from $cTabla where idcuentabancaria='$cCta' and referenciabancaria='$cCheque'";
		$aVal	 = ejecutaSQL_($sql);

		if ($aVal!==null){  // Ya existe el cheque
			$respuesta["mensaje"] ="Ya existe el cheque $cCheque ... revise";
			return false;
		}
		$sql = "update $cTabla set referenciabancaria='$cCheque', estatus='I' where idcuentabancaria='$cCta' and idmovimiento=$cId";
		if ( actualizaSql($sql) > 0 ){
			imprimeCheque($respuesta);
		}else{
			$respuesta["mensaje"] ="No se logro actualizar número de cheque con Id $cId";
			return false;
		}
	}catch(Exception $e){
		$respuesta["mensaje"] = "Iconsistencia " . $e->getMessage();
		return false;
	}
	

}
// ________________________________________________________________________________________
function existeCheque(&$respuesta){
	$cCheque = $respuesta["datos"]["numCheque"];
	$cCta	 = $respuesta["datos"]["idCuenta"];
	$cTabla  = "atablas.t_" . trim($cCta);
	$sql 	 = "select referenciabancaria as cheque from $cTabla where idcuentabancaria='$cCta' and referenciabancaria='$cCheque'";
	$aVal	 = ejecutaSQL_($sql);

	if ($aVal==null){  // No existe el cheque
		$respuesta["mensaje"] = "No existe el cheque $cCheque ... revise";
		return false;
	}else{
		$respuesta["success"] = true;
		return true;
	}
}
// ________________________________________________________________________________________
function ReporteSaldos(&$res){
	require_once("repo/SaldosReporte_.php");
	$cCta	 = $res["datos"]["cuenta"];
	$cFecIni = $res["datos"]["fechaIni"];
	$cFecFin = $res["datos"]["fechaFin"];
	$sql =  "select idcuentabancaria, fechasaldo, to_char(saldoinicial,'9,999,999,990.99') as saldoinicial, "		.
			"to_char(ingresos,'9,999,999,990.99') as ingresos, to_char(egresos,'9,999,999,990.99') as egresos, " 	.
			"to_char(cheques,'9,999,999,990.99') as cheques , " 													.
			"to_char((saldoinicial + ingresos - egresos - cheques),'9,999,999,990.99')  as saldofinal "				.
			" from saldos where idcuentabancaria='$cCta' and fechasaldo>='$cFecIni' and fechasaldo<='$cFecFin' "	.
			" order by fechasaldo desc ";
	$res["_trace"] = $sql;

	$aVal	 = ejecutaSQL_($sql);

	if ($aVal==null){  // No existe el cheque
		$res["mensaje"] = "No existe información ...";
		return false;
	}else{
		$res["success"] 	= true;
		$res["resultados"] 	= $aVal;
		pdfReporteSaldos($res);
		return true;
	}

}
// ________________________________________________________________________________________
function EstadoCuenta(&$res){
	try {
		metodos::SaldoAnterior($res); // lo guarda en $res["datos"]["saldoAnterior"], se requiere porpiedades cCta y cFecha
		// ini_set('memory_limit', '1G'); // Aumenta a 1 GB
		$cCta	= $res["datos"]["cCta"];
		$fecI	= $res["datos"]["fechaI"];
		$fecF	= $res["datos"]["fechaF"];
		$cSal	= $res["datos"]["salida"];
		$cTabla = "atablas.t_" . trim($cCta);
		$sql	=	"select a.fechaoperacion, a.referenciabancaria, a.folio, a.idoperacion, a.idcontrol, " . 
					" a.beneficiario, a.concepto, a.importeoperacion, b.tipo, a.idunidad " . 
					" from $cTabla a, operacionesbancarias b ". 
					" where a.idoperacion=b.idoperacion and a.idcuentabancaria='$cCta' and " .
					" fechaoperacion>='$fecI' and fechaoperacion<='$fecF' " .
					" order by a.fechaoperacion, b.tipo desc";
		$res["_trace"] = $sql;
	
		$aVal = ejecutaSQL_($sql);
	
		if ($aVal==null){  
			$res["mensaje"] = "No existe información ...";
			return false;
		}else{
			$res["resultados"] 	= $aVal;
			if ($cSal==="Pdf"){
				$res["success"] 	= true;
				require_once("repo/EstadoCuentaReporte_.php");
				pdfEstadoCuenta($res);
				return true;
			}else{ // Salida pdf
				xlsEstadoCuenta($res);
			}
		}
	} catch(Exception $e){
		$res["mensaje"] = $e->getMessage();
		return false;
	}
}
// ________________________________________________________________________________________
function xlsEstadoCuenta(&$res){
	$lRegreso = true;
	try{
		$filename 	= "../csv/" . $res["datos"]["csv"];
		$file 		= fopen($filename, 'w');
		$nI 		= 0.00;
		$nE			= 0.00;
		$c2			= "";
		$c3 		= "";

		// Datos del Reporte
		$cW = "CUENTA : ". $res["datos"]["cCta"]."-". $res["datos"]["nombre"];
		fputcsv($file, ["", "","","","",$cW,"","",""]);
		$cW = "ESTADO DE CUENTA DEL " . date("d-m-Y", strtotime($res["datos"]["fechaI"]) ) . 
			  " AL " . date("d-m-Y", strtotime($res["datos"]["fechaF"]) );
		fputcsv($file, ["", "","","","",$cW,"","",""]);
		fputcsv($file, [""]);
		$nSI = $res["datos"]["saldoAnterior"];
		$cW  = "SALDO INICIAL : " . conComas($nSI);
		// Encabezados
		fputcsv($file, ['FECHA', utf8("OPERACIÓN"),"DOCTO","OPE","CTRL","BENEFICIARIO","CONCEPTO","INGRESOS","EGRESOS","UR"]);
		fputcsv($file, ['', "","","","","",$cW,"","",""]);
		// Datos
		//fputcsv($file, [$aDatos["DEA"], $aDatos["SOB"], date("H:i:s"), date("d/m/Y"), $aDatos["letPag"] . '1 de {totalPages}']);

		foreach ($res["resultados"] as $m ) {
			$c2 = $c3 = "";
			if ($m["tipo"]=="I"){
				$c2  = conComas($m["importeoperacion"]);
				$nI += $m["importeoperacion"];
			}else{
				$c3  = conComas($m["importeoperacion"]);
				$nE += $m["importeoperacion"];
			}
			$cBene  = utf8($m["beneficiario"]);
			$cCpto	= utf8($m["concepto"]);
			fputcsv($file,[$m["fechaoperacion"],$m["referenciabancaria"],$m["folio"],$m["idoperacion"],$m["idcontrol"],
						  $cBene,$cCpto,$c2,$c3,$m["idunidad"]]);
		}
		fputcsv($file, [""]);
		fputcsv($file, ['', "","","","","","SALDO INICIAL","INGRESOS","EGRESOS","SALDO FINAL"]);
		fputcsv($file, ['', "","","","","",conComas($nSI),conComas($nI),conComas($nE),conComas($nSI+$nI-$nE)]);

		$res["success"] = true;
		$res["archivo"] = 'csv/' . $res["datos"]["csv"];
	} catch(Exception $e){
		$res["mensaje"] = $e->getMessage();
		$lRegreso		= false;
	}
	// Cerrar el archivo
	fclose($file);
	return $lRegreso;
}
// ________________________________________________________________________________________
function xlsEdoPosFinDia(&$res,$lMensual){
	$lRegreso	= true;
	$filename 	= "../csv/" . $res["datos"]["csv"];

	try{
		$file	= fopen($filename, 'w');
		$nI		= $res["datos"]["saldoAnterior"];
		$cCta   = $res["datos"]["cCta"];
		$nE		= 0.00; $nTI = $nTE = 0.00;
		$nSf	= $nI;
		$cFecha = date("d-m-Y", strtotime($res["datos"]["cFecha"]));
		$cW 	= "CUENTA : ". $cCta ."-". $res["datos"]["cNombre"];
		//
		// Datos del Reporte
		fputcsv($file, ["", "",utf8("ESTADO DE POSICIÓN FINANCIERA ") . ($lMensual?"MENSUAL":"DIARIO"),"","",""]);
		if ($lMensual){
			fputcsv($file, ["", "",$cW,"","",utf8("AÑO : "). $res["datos"]["cAnio"] . " MES : ". $res["datos"]["sMes"]]);
		}else{
			fputcsv($file, ["", "",$cW,"","","FECHA : ". $cFecha]);
		}
		fputcsv($file, [""]);
		fputcsv($file, ["DOCUMENTO", "BENEFICIARIO","CONCEPTO","INGRESOS","EGRESOS","SALDO FINAL", "", "UR","OPE","CTRL",utf8("AÑO"),($lMensual?"FECHA":"")]);
		fputcsv($file, ["", "","\tSALDO INICIAL : ", conComas($nI),"",conComas($nSf)]);
		//
		foreach ($res["resultados"] as $m ) {
			$tipo = $m["tipo"];
			
			if ($tipo=="I"){
				$docto	= trim($m["folio"]);
				$nI		= $m["importeoperacion"];
				$nTI	+= $nI;
				$cI		= conComas($nI);
				$cE		= "";
				$nSf	+= $nI;
				if ($docto==""){
					$docto = $m["referenciabancaria"];
				}
			}else{
				$docto = $m["referenciabancaria"];
				$nE		= $m["importeoperacion"];
				$nTE	+= $nE;
				$cI		= "";
				$cE		= conComas($nE);
				$nSf	-= $nE;
			}
			fputcsv($file, [$docto, utf8($m["beneficiario"]),utf8($m["concepto"]),$cI,$cE,conComas($nSf),"",
								   $m["idunidad"],$m["idoperacion"],$m["idcontrol"],$m["anioejercicio"],
								($lMensual?$m["fechaoperacion"]:"")  ]);
			$res["success"] = true;
			$res["archivo"] = 'csv/' . $res["datos"]["csv"];
		}
		fputcsv($file, [str_repeat("_", 10), str_repeat("_", 25),str_repeat("_", 25),str_repeat("_", 15),str_repeat("_", 15),str_repeat("_", 15)]);
		fputcsv($file, [ "", "","TOTAL CUENTA : " . $cCta,conComas($nTI),conComas($nTE) , conComas($nSf) ]);
	} catch(Exception $e){
		$res["mensaje"] = $e->getMessage();
		return false;
	}
	fclose($file);
	return $lRegreso;
}
// ________________________________________________________________________________________
function xlsEdoPosFinMensual(&$res){
	xlsEdoPosFinDia($res,true);
}
// ________________________________________________________________________________________
function InteresesPdf(&$res){
	require_once("repo/InteresesPdf_.php");
	//					and estatus=true se le quito por que todavía hay cuentas de la re-distritación
	$sql 		 = "select idunidad, nombreunidad from public.unidades where  " .
				   "  ctas_intereses is not null and idunidad!='OF16' order by idunidad ";
	$aVal		 = ejecutaSQL_($sql);
	$res["ctas"] = $aVal;
	$mensaje	 = "";

	$res["success"] = pdfReporteIntereses($res);
}
// ________________________________________________________________________________________
function CtasOF16(&$res){
	metodos::CtasOF16($res); // Se guarda en $res["resultados"], las cuentas pertenecientes a OF16
	metodos::CtasUrs($res);
}
// ________________________________________________________________________________________
function RespuestaPdf(&$res){
	require_once("repo/InteresRespuestaPdf_.php");
	//					and estatus=true se le quito por que todavía hay cuentas de la re-distritación and idunidad!='OF16'
	$sql 		 = "select idunidad, nombreunidad from public.unidades where  " .
				   "  ctas_intereses is not null  order by idunidad ";
	$aVal		 = ejecutaSQL_($sql);
	$res["ctas"] = $aVal;
	$mensaje	 = "";

	$res["success"] = pdfInteresesRespuesta($res);
}
// ________________________________________________________________________________________
// ________________________________________________________________________________________
// ________________________________________________________________________________________
// ________________________________________________________________________________________
// ________________________________________________________________________________________
//
?> 