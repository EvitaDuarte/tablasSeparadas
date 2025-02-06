<?php
define('Letra', 'Helvetica');
require('../assetsF/php/fpdf/mc_table.php');
//require('rutinas.php'); ya se invoco en reportes_.php

function pdfReporteSaldos(&$res){
	global $aAnchos, $nTotJle;
	$aAnchos =  array(15, 95 , 50 , 40 );
	// ___________________
	$datos = $res["opcion"]["aRes"];$ctas = $res["ctas"]; 	
	// ___________________
	$nSalto  = 45;
	$nPag	 = 1;

	// ___________________
	try {
        ob_start();
        $cMes = substr( $datos[0]["a11_Fecha"] , 2,2);
        $cMes = (int)$cMes;
        $cMes = "INTERESES " . strtoupper(mesesEspanol($cMes));
		// ___________________
	    $pdf = new PDF_MC_Table('P','mm','Letter');
	    $pdf->SetTopMargin(7);
	    $pdf->SetAutoPageBreak(true, 1); 	// 1 de margen inferior para el footer y que detecte salto de página $pdf->GetY() + 10 > $pdf->h
	    imprimeJuntas($pdf,$datos,$ctas,$cMes);
	    imprimeDtos($pdf,$datos,$ctas,$cMes,$res);
	    // ___________________
 		ob_end_clean();
	    $ip 		  = ipRepo();
	    $tempFilename = '../pdfs/' . trim($ip) ;

	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();

	    $res["mensaje"] 	= "";
	    $res["success"] 	= true;

	    $res["archivo"] 	= 'pdfs/' . trim($ip) ;
	    // ___________________
	    return true;
	    // ___________________
	} catch (Exception $e) {
		$mensaje = "No se logro generar la información solicitada";
		return false;
	}	
}
// ______________________________________________________________________________________________________
function imprimeJuntas($pdf,$datos,$ctas,$cMes){
	global $nTotJle;
	$nPag = 1; $nTot = 0.00;
	encabezado($pdf,$nPag,$cMes);
	foreach ($datos as $jle ) {
		$cUr = $jle["a10_Ur"];
		if ( substr($cUr, 2,2)=="00"){
			$cNombre = buscaUr($cUr,$ctas);
			$noCta 	 = $jle["a04_cuenta"];
			$nImpo	 = valorFloat($jle["a08_Impo"]);
	        if ( $pdf->GetY() + 10 > $pdf->h ){
	        	$nPag++;
				encabezado($pdf,$nPag,$cMes);
			}
	        $aRen  = array($cUr,$cNombre,$noCta,conComas($nImpo));
			$pdf->RowSinCuadro($aRen);
			$nTot += $nImpo;
		}
	}
    if ( $pdf->GetY() + 10 > $pdf->h ){
    	$nPag++;
		encabezado($pdf,$nPag,$cMes);
	}
	$pdf->SetFont(Letra, 'B', 8);
	$aRen  = array("","","TOTAL JUNTAS LOCALES",conComas($nTot));
	$pdf->RowSinCuadro($aRen);
	$pdf->SetFont(Letra, '', 7);
	$nTotJle = $nTot;
}
// ______________________________________________________________________________________________________
function imprimeDtos($pdf,$datos,$ctas,$cMes){
	global $nTotJle;
	$nPag = 1; $nTot = 0.00;$cUr1="";$nTUr=0.00;
	encabezado($pdf,$nPag,$cMes);
	$x=2        ; $y=36;
	$pdf->SetXY($x, $y); // x , y
	foreach ($datos as $jle ) {
		$cUr = $jle["a10_Ur"];
		if ( substr($cUr, 2,2)!="00"){
			$cUr2    = substr($cUr,0,2);

			// Ve si hay cambio de UR
			if ($cUr1==""){
				$cUr1 = $cUr2;
			}else{
				if ($cUr1!==$cUr2){
			        if ( $pdf->GetY() + 10 > $pdf->h ){
			        	$nPag++;
						encabezado($pdf,$nPag,$cMes);
					}

					$pdf->SetFont(Letra, 'B', 8);
					$aRen  = array("","","TOTAL UR " . $cUr1 ,conComas($nTUr));
					$pdf->RowSinCuadro($aRen);
					$pdf->SetFont(Letra, '', 7);

					$cUr1 = ""; $nTUr = 0.00;
				}
			}

			$cNombre = buscaUr($cUr,$ctas);
			$noCta 	 = $jle["a04_cuenta"];
			$nImpo	 = valorFloat($jle["a08_Impo"]);
	        if ( $pdf->GetY() + 10 > $pdf->h ){
	        	$nPag++;
				encabezado($pdf,$nPag,$cMes);
			}
	        $aRen  = array($cUr,$cNombre,$noCta,conComas($nImpo));
			$pdf->RowSinCuadro($aRen);
			$nTot += $nImpo; $nTUr+= $nImpo;

		}
	}
    if ( $pdf->GetY() + 30 > $pdf->h ){
    	$nPag++;
		encabezado($pdf,$nPag,$cMes);
	}
	$pdf->SetFont(Letra, 'B', 8);
	$aRen  = array("","","TOTAL UR " . $cUr1 ,conComas($nTUr));
	$pdf->RowSinCuadro($aRen);	
	$aRen  = array("","","TOTAL JUNTAS DISTRITALES",conComas($nTot));
	$pdf->RowSinCuadro($aRen);
	$aRen  = array("","","TOTAL GENERAL",conComas($nTot+$nTotJle));
	$pdf->RowSinCuadro($aRen);
}
// ______________________________________________________________________________________________________
function buscaUr($cUr,$ctas){
	$cNombre = ""; 
	foreach ($ctas as $cta) {
	    if ($cta['idunidad'] === $cUr) {
	        $cNombre = strtoupper(utf8_decode($cta["nombreunidad"]));
	         break;  // Salir del bucle una vez encontrado el valor
	    }
	}
	return $cNombre;
}
// ______________________________________________________________________________________________________
function valorFloat($cadena){
	$entero		= substr($cadena, 0, strlen($cadena) - 2);  // Los primeros (longitud - 2) caracteres
	$decimales	= substr($cadena, -2);  // Los últimos 2 caracteres

	// Concatenar la parte entera y los decimales con un punto entre ellos
	$numero_float = (float)($entero . '.' . $decimales);
	return $numero_float;
}
// _____________________________ _________________________________________________________________________
function encabezado($pdf,&$nPag,$cMes){
	// Globales
	global $aAnchos;
    // Configurar el encabezado
    $pdf->AddPage();
    $pdf->SetHeights(3); 	// Reducir la altura de línea a n unidades
    $pdf->SetSpaceLine(5);	// Dependiendo este valor hay que actualizar la posición de los rectangluos
    $pdf->SetFont(Letra, 'B', 11, '' , true); // Tambien este valor afecta la posición de los rectangluos
    $pdf->SetLeftMargin(2);
    //$pdf->InFooter = true;
    // Cuadro de los encabezados principales
    $pageWidth = $pdf->w - 5;
	//$pdf->Rect(2, 8, $pageWidth, 22, 'D');

	logoInstitucional1($pdf);

    // Arreglo del Encabezado
    $aCabeza = [
    	[" ","INSTITUTO NACIONAL ELECTORAL", " "],
    	[" ",utf8_decode("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN")	, utf8_decode("PÁGINA : " ) . $nPag  ] ,
    	[" ",utf8_decode("SUBDIRECCIÓN DE OPERACIÓN BANCARIA" )  	,   "HORA : " . date("H:i:s")		 ] ,
    	[" ",utf8_decode("RELACIÓN DE CUENTAS BANCARIA BANAMEX")	,  "FECHA : " . date("d/m/Y")		 ]
    ];
	// Configurar anchos proporcionales
	$anchoTotal = $pdf->w; // Ancho total de la página
	$anchoPrimeraColumna = $anchoTotal * 0.50; // % del ancho total
	$anchoSegundaColumna = ($anchoTotal - $anchoPrimeraColumna - 50); // El resto del ancho

	$pdf->SetWidths(array(40,$anchoPrimeraColumna, $anchoSegundaColumna));
    $pdf->SetAligns(['L','C', 'R']);
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
	}
	$pdf->SetFont(Letra, 'B', 10, '' , true);

    // -----------------------------
    //Cuadro de toda la página
    $y = $pdf->GetY();
    $pdf->Rect(2, $y, $pageWidth, $pdf->h - $y - 2, 'D');
    // Líneas verticales de separación
    $x = 0;
    for ($i=0;$i<count($aAnchos)-1;$i++){
    	$x = $x + $aAnchos[$i] ;
   		$pdf->Rect(2+$x,$y,0,$pdf->h - $y - 2, 'D');
   	}
    // -----------------------------
	$aCabeza = [
    	["UR","ENTIDAD FEDERATIVA","NO. CUENTA",$cMes  ]
    ];
    $pdf->GuardaXY();
    //$y = $this->GetY();//$this->RecuperaY();
    //$this->Ln($y+1);
    
    // Encabezados columnas
    $pdf->Ln(1); // agrega un punto al espaciado
    $pdf->SetWidths($aAnchos); // Ancho de las columnas
    $pdf->SetFont(Letra, 'B', 9);
    $pdf->SetAligns( ['C','C','C','C']); // Alineación de las columnas
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row,null);
    }
	$pdf->Rect(2,$y+5,$pdf->w-5 ,0, 'D');

	// Detalle Páginaletra anchos
	$pdf->SetFont(Letra, '', 7);
	$pdf->SetAligns( ['L','L','L','R']);
	$pdf->SetWidths($aAnchos);
}
// ______________________________________________________________________________________________________
?>