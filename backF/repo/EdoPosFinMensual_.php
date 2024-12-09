<?php
define('Letra', 'Helvetica');
require('../assetsF/php/fpdf/mc_table.php');
function pdfEdoPosFinMensual(&$res){
	// ___________________
	try {
        ob_start();
		
		global $cCta , $cNombre , $cTit;
		$nPag 	 = 1;
		$nCar 	 = 31; $nCar1 = 31;
		$nSalto  = 45;
		$cCta 	 = $res["datos"]["cCta"];
		$cNombre = $res["datos"]["cNombre"];
		$cTit    = $res["datos"]["sMes"] . " DEL AÑO " . $res["datos"]["cAnio"];
		$nSal	 = $res["datos"]["saldoAnterior"];
		$nPagTot = ceil( (count($res["resultados"])+1)/$nSalto); // El +1 es por el total general
		// ___________________
	    $pdf = new PDF_MC_Table('P','mm','Letter');
	    //$pdf->AliasNbPages($nPagTot);
	    $pdf->SetAutoPageBreak(true, 1); // 1 de margen inferior para el footer
		$pdf->AliasNbPages('{totalPages}');	
	    encabezado($pdf,$nPag,$nPagTot); // Debe ir despues del AddPage
	    // -----------------------------
	    // Convertir la cadena a un número de punto flotante (float)
	    $nEgresos	= 0.00;
		$nSaldo 	= floatval($nSal);
		$nIngresos	= $nSaldo;
		$cSaldo		= number_format($nSaldo, 2, '.', ',');
		$ren		= array("","","SALDO INICIAL",$cSaldo,"",$cSaldo);
		$pdf->Row($ren,null);
	 	// _____________________________
		$nRen = 1;
	    foreach ($res["resultados"] as $row1) {
	    	$nImporte = $row1["importeoperacion"];
	    	$cImporte = number_format($nImporte, 2, '.', ',');
	    	if ($row1["tipo"]=="I"){
	    		$nIngresos += $nImporte;
	    		$nSaldo 	= $nSaldo + $nImporte;
	    		$cSaldo 	= number_format($nSaldo, 2, '.', ',');
	    		$ren 		= array($row1["folio"],utf8(substr($row1["beneficiario"],0,$nCar)),utf8(substr($row1["concepto"],0,$nCar1)),$cImporte,"",$cSaldo);
	    	}else{
	    		$nEgresos  += $nImporte;
	    		$nSaldo 	= $nSaldo - $nImporte;
	    		$cSaldo 	= number_format($nSaldo, 2, '.', ',');
				$ren 		= array($row1["referenciabancaria"],utf8(substr($row1["beneficiario"],0,$nCar)),utf8(substr($row1["concepto"],0,$nCar1)),"",$cImporte,$cSaldo);
	    	}
	    	$pdf->RowSinCuadro($ren);
	    	$nRen++;
	    	if ($nRen>$nSalto){
	    		$nRen = 1;
	    		encabezado($pdf,$nPag,$nPagTot);
	    	}
	    }
	    // ----------------------------
    	if ($nRen>$nSalto){
    		$nRen = 1;
    		encabezado($pdf,$nPag,$nPagTot);
    	}
		$ren = array("","","TOTAL CUENTA : ". $cCta,number_format($nIngresos, 2, '.', ','),number_format($nEgresos, 2, '.', ','),$cSaldo);
		$pdf->Row($ren,null);
	    // -----------------------------

	    ob_end_clean();
	    $tempFilename = '../pdfs/' . trim($res["datos"]["reporte"]) ;

	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();
	    $res["mensaje"] = "";
	    $res["success"] = true;
	    $res["archivo"] = 'pdfs/' . trim($res["datos"]["reporte"]) ;
	    // Send the PDF file as response
	    //$cHead = 'Content-Disposition: attachment; filename="' .  $tempFilename . '"';
	    //header_remove('x-powered-by');
	    //header('Content-Type: application/pdf');
	    //header($cHead);
	    //readfile($tempFilename);
	    //exit;
	} catch (Exception $e) {
		$res["mensaje"] = "No se logro generar la información solicitada";
		$res["success"] = false;
	}

}
// _______________________________________________________________
function encabezado($pdf,&$nPag,$nPagTot){
    // Configurar el encabezado
    $pdf->AddPage();
    $pdf->SetHeights(3); 	// Reducir la altura de línea a n unidades
    $pdf->SetSpaceLine(5);	// Dependiendo este valor hay que actualizar la posición de los rectangulos
    $pdf->SetFont(Letra, 'B', 11, '' , true); // Tambien este valor afecta la posición de los rectangulos
    $pdf->SetLeftMargin(2);
    $pdf->InFooter = true;
    // Cuadro de los encabezados principales
    $pageWidth = $pdf->w - 5;
	$pdf->Rect(2, 8, $pageWidth, 22, 'D');

    // Ruta de la imagen y coordenadas donde se ubicará
    $imagePath = '../assetsF/img/ine_logo_pdf.jpg';
    $x 		= 7;
    $y 		= 13;
    $width 	= 25;
    $height = 0; // 0 para mantener la proporción del tamaño original
    $pdf->Image($imagePath, $x, $y, $width, $height);

    // Arreglo del Encabezado
    // X
    $aCabeza = [
    	["","",""],
    	[" ",utf8("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN"), utf8("PÁGINA : " ) . $pdf->PageNo() . ' de ' . ' de {totalPages}'	] ,
    	[" ",utf8("SUBDIRECCIÓN DE OPERACIÓN BANCARIA" )  ,   "HORA : " . date("H:i:s")					] ,
    	[" ",utf8("ESTADO DE POSICIÓN FINANCIERA"		),  "FECHA : " . date("d/m/Y")					]
    ];
	// Configurar anchos proporcionales
	$anchoTotal = $pdf->w; // Ancho total de la página
	$anchoPrimeraColumna = $anchoTotal * 0.50; // 60% del ancho total
	$anchoSegundaColumna = ($anchoTotal - $anchoPrimeraColumna)/2; // El resto del ancho

	$pdf->SetWidths(array($anchoSegundaColumna-20,$anchoPrimeraColumna+10, $anchoSegundaColumna+10));
    $pdf->SetAligns(['L','C', 'L']);
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
	}
	$pdf->SetFont(Letra, 'B', 9, '' , true);
	global 	$cCta , $cNombre , $cTit;

	// Rectángulo para el segundo conjunto de datos rect ( x , y , ancho , alto)
	$pdf->Rect(2, $pdf->GetY(), $pageWidth, 6, 'D'); 
	$aCabeza = [
		[" ","BANCO : " . $cNombre . " CUENTA: " .  $cCta , utf8($cTit) ]
	]; 		
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
    }
	$aCabeza = [
    	["DOCUMENTO","BENEFICIARIO","CONCEPTO","INGRESOS","EGRESOS","SALDO FINAL"]
    ];
    //$this->GuardaXY();
    //$y = $this->GetY();//$this->RecuperaY();
    //$this->Ln($y+1);
    $aAnchos = array(30, 55,50.5,25,25,25.5);
    $pdf->Ln(1); // agrega un punto al espaciado
    $pdf->SetWidths($aAnchos); // Ancho de las columnas
    $pdf->SetAligns( ['C','C','C','C','C','C']); // Alineación de las columnas
    foreach ($aCabeza as $row) {
        $pdf->Row($row,null);
    }
    //Cuadro de toda la página
    $y = $pdf->GetY();
    $pdf->Rect(2, $y, $pageWidth, $pdf->h - $y - 2, 'D');
    // Líneas verticales de separación
    $x = 0;
    for ($i=0;$i<5;$i++){
    	$x = $x + $aAnchos[$i] ;
   		$pdf->Rect(2+$x,$y,0,$pdf->h - $y - 2, 'D');
   	}
	$pdf->SetFont(Letra, '', 7);
	$pdf->SetAligns( ['L','L','L','R','R','R']);
	$pdf->SetWidths($aAnchos);
}
?>