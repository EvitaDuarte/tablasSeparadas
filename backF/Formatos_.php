<?php
	// Comentar  para producción
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	//
	session_start(); // variables de sesión
	// _______________________________________
	if ( !isset($_SESSION['OpeFinClave'])){
		header("Location: ../OpeFin00_home.php");exit; return;
	}
	// _______________________________________
	//
	$idUsuario     	= $_SESSION['OpeFinClave'];
	$esquemaUsuario = $_SESSION['OpeFinEsquema'];
	//
	include_once("con_pg_OpeFinW_.php"); 	// Se incluye conexión a la Base de Datos
	include_once("rutinas_.php");


	// inicializa arreglo que se regresara a JavaScript y que se podra visualizar en el depurador del navegador (activar con F12)
	$respuesta = array(	'success'=>false, 'mensaje'=>'', 'resultados'=>array(), 'opcion'=>array() , 
						'datos'=>array() , 'combo'=>array(), 'depura'=>array() , 'sesion'=>array() , "objeto"=>array());
	// Se gauardan variables de sesion
	$respuesta["sesion"] = array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);

	// Recupera los parámetros enviados por JS, 
	// Lee el cuerpo de la solicitud HTTP
	$jsonData = file_get_contents('php://input');
	// Decodifica los datos JSON en un array asociativo
	$aParametros 		 = json_decode($jsonData, true);
	$vOpc 				 = $aParametros["opcion"];  					// Opción que el JS quiere que se ejecute en este php
	$respuesta["opcion"] = $aParametros;			   					// Se guardan para efectos de depuración
	$respuesta["datos"]  = array("idUsuario"=>$idUsuario, "esquemaUsuario"=>$esquemaUsuario);
	//
	$respuesta["datos"]["opcion"] = $vOpc;

	switch ($vOpc) {
//		_________________________________________________		
		case "traeCatalogoCuentasBancarias":
			traeCatalogoCuentasBancarias($respuesta);
		break;
//		_________________________________________________
		case "traeFormatoCheque":
			traeFormatoCheque($respuesta);
		break;
//		_________________________________________________
		case "modificaFormato":
			modificaFormato($respuesta);
		break;
//		_________________________________________________
	}
	// Características de la "página" que se regresa a JS
	header_remove('x-powered-by');							 // remueve el header
	header('Content-type: application/json; charset=utf-8'); // valores en formato JSON caracteres UTF-8
	echo json_encode($respuesta);							 // Se regresa la respuesta a Java Script
return;
// _____________________________________________________________
function traeCatalogoCuentasBancarias(&$respuesta){
	$sql = "select idcuentabancaria as clave, nombre as descripcion from cuentasbancarias where estatus=true order by idcuentabancaria";
	$res = ejecutaSQL_($sql);
	if ( $res!=null){
		$respuesta["resultados"] = $res;
		$respuesta["success"]	 = true;
		$respuesta["mensaje"]	 = "";
	}else{
		$respuesta["mensaje"] = "No hay aún Cuentas Bancarias en el Sistema";
	}
}
// _____________________________________________________________
function traeFormatoCheque(&$respuesta){
	try{
		$cCta 	= $respuesta["opcion"]["cuenta"];
		$sql	= "select posicion, x , y , altura, anchura, font, fontsize, alineacion from frx where idcuentabancaria='$cCta' order by posicion";
		$res	= ejecutaSQL_($sql);
		if ($res!=null){
			$respuesta["resultados"] = $res;
			$respuesta["success"]	 = true;
		}else{
			$aDatos = [
				["01",11.05,  1.98, 0.47,  9.26, 9, "Courier", "R", "Letra fecha"		],
				["02", 2.61,  3.28, 1.32, 12.83, 9, "Courier", "L", "Beneficiario"		],
				["03",16.06,  3.28, 0.47,  4.18, 9, "Courier", "R", "Importe Numérico"	],
				["04", 1.79,  4.84, 1.32, 18.22, 9, "Courier", "L", "Importe Letra"		],
				["05", 1.24,  9.81, 0.79, 13.67, 8, "Courier", "L", "Concepto de pago"	],
				["06",15.02,  9.81, 0.42,  5.29, 8, "Courier", "R", "Importe Numérico1"	],
				["07",17.80, 10.29, 0.42,  2.51, 8, "Courier", "R", "Número de Cheque"	],
				["08", 1.27, 10.66, 0.42,  2.93, 8, "Courier", "L", "Somire o Documento"]
			];
			foreach ($aDatos as $r ){
				$sql1 = "insert into frx ( ".
						"idcuentabancaria, posicion, x, y, altura, anchura, fontsize, font, alineacion, descripcion) values( ".
						"'$cCta','$r[0]',$r[1],$r[2],$r[3],$r[4],$r[5],'$r[6]','$r[7]','$r[8]' )";
				actualizaSql($sql1);
			}
			$res = ejecutaSQL_($sql);
			if ($res!=null){
				$respuesta["resultados"] = $res;
				$respuesta["success"]	 = true;
			}else{
				$respuesta["mensaje"] = "No fue posible adicionar los parámetros del cheque";
			}
		}
	}catch(Exception $e){
		$respuesta["mensaje"] = $e->getMessage();
	}
}
// _____________________________________________________________
function modificaFormato(&$res){
	// $res["mensaje"] = "En construccion ....";
	// Se recorren los renglones que se modificaron
	try{
		$i	  = 0;
		$cCta = $res["opcion"]["cuenta"];
		foreach ($res["opcion"]["aValores"] as $r ){
			list ($posicion,$x,$y,$altura,$anchura,$font,$fontsize,$alineacion) = $r;
			$sql  =	"update frx set x=$x, y=$y, altura=$altura, anchura=$anchura, fontsize=$fontsize, font='$font', alineacion='$alineacion' " .
					"where idcuentabancaria='$cCta' and posicion='$posicion'";
			$nRen = actualizaSql($sql);
			$i	  = $i + $nRen;
			$res["success"] = true;
		}
		$res["mensaje"] = "Se actualizaron $i elementos";
	}catch(Exception $e){
		$res["mensaje"] = "Inconsistencia : " . $e->getMessage();
		$res["success"] = false;
	}

}
// _____________________________________________________________
// _____________________________________________________________
// _____________________________________________________________
// _____________________________________________________________
// _____________________________________________________________
// _____________________________________________________________
// _____________________________________________________________

?>