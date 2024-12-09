<?php
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('Letra', 'Helvetica');
define('Letra1', 'Courier');
require('../assetsF/php/fpdf/mc_table.php');
// ______________________________________________________________________________
function pdfConsolidadoGeneral(&$res){
	try {
		ob_start();
		// ___________________
	    $pdf = new PDF_MC_Table('P','mm','Letter');
	    $pdf->SetAutoPageBreak(true, 1); // 1 de margen inferior para el footer
	    encabezado($pdf,$res); // Debe ir despues del AddPage
	    // -----------------------------
	    $anchoTotal = $pdf->w; // Ancho total de la página
		$ancho1 = $anchoTotal * 0.40; // 60% del ancho total
		$ancho2 = ($anchoTotal-$ancho1) * 0.25;
		$anchos = array($ancho1,$ancho2,$ancho2,$ancho2);

		$pdf->SetFont(Letra, '', 9, '' , true); // Tambien este valor afecta la posición de los rectangluos
		$pdf->SetAligns(['R', 'R','R','R']);
		$pdf->SetWidths($anchos);
	    foreach($res["aPdf"] as $row){
			$pdf->RowSinCuadro($row);
	    }
	    // _____________________________
	    $anchoTotal = $anchoTotal - 15;
	    $ancho2 = intval($anchoTotal*.10);
	    $ancho1 = intval(($anchoTotal-$ancho2)/4);
	    $anchos = array($ancho2,$ancho1+6,$ancho1-2,$ancho1-2,$ancho1-2);
	    $lVer   = true;
	    
		$pdf->SetWidths($anchos);
		$pdf->SetFont(Letra1, 'B', 8, '' , true); 
	    foreach($res["aCtas1"] as $row){
	    	if($lVer){
	    		$lVer = false;
	    		$pdf->SetAligns(['C', 'C','C','C','C']);
	    	}else{
	    		$pdf->SetAligns(['L', 'L','R','R','R']);
	    	}
			$pdf->RowSinCuadro($row);
	    }
	    // _____________________________
	    ob_end_clean();
	    $tempFilename = '../pdfs/' . trim($res["datos"]["reporte"]) ;

	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();
	    $res["mensaje"] = "";
	    $res["success"] = true;
	    $res["archivo"] = 'pdfs/' . trim($res["datos"]["reporte"]) ;
	    // var_dump($res);
	    // _____________________________
	} catch (Exception $e) {
		$res["mensaje"] = "No se logro generar la información solicitada";
		$res["success"] = false;
	}
}
// ______________________________________________________________________________
function encabezado($pdf,$r){
    // Configurar el encabezado
    $pdf->AddPage();
    $pdf->SetHeights(3); 	// Reducir la altura de línea a n unidades
    $pdf->SetSpaceLine(4);	// Dependiendo este valor hay que actualizar la posición de los rectangluos
    

    $pageWidth = $pdf->w - 10;
    //Cuadro de toda la página
    $y = $pdf->GetY();
    $pdf->Rect(5, $y-2, $pageWidth, $pdf->h - $y - 5, 'D');
    $pdf->SetFont(Letra, 'B', 6, '' , true); // Tambien este valor afecta la posición de los rectangluos
    // Imprimir cadena debajo del rectángulo
	$texto = utf8_decode("(*) LOS SALDOS QUE SE REPORTAN EN CUENTAS DE INVERSION, CORRESPONDEN AL DÍA ANTERIOR");
	$pdf->SetY($pdf->h - 3);  // Establecer la posición Y al final de la página
	$pdf->Cell(0, 0, $texto, 0, 0, 'L');  // Imprimir la cadena centrada

	$pdf->SetY($y);
	$pdf->SetFont(Letra, 'B', 10, '' , true); // Tambien este valor afecta la posición de los rectangluos
    // Cuadro de los encabezados principales
	$pdf->Rect(5, $y-2, $pageWidth, 22, 'D');

    // Ruta de la imagen y coordenadas donde se ubicará
    $imagePath = '../assetsF/img/ine_logo_pdf.jpg';
    $x 		= 12;
    $y 		= 10;
    $width 	= 30;
    $height = 0; // 0 para mantener la proporción del tamaño original
    $pdf->Image($imagePath, $x, $y, $width, $height);

    // Arreglo del Encabezado
    $aCabeza = [
    	[utf8_decode("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN"), utf8_decode("PÁGINA : " ). $pdf->PageNo() . ' de ' . 1	] ,
    	[utf8_decode("SUBDIRECCIÓN DE OPERACIÓN BANCARIA" )  ,   "HORA : " . date("H:i:s")								] ,
    	[utf8_decode("C O N S O L I D A D O")				 ,  "FECHA : " . date("d/m/Y")								],
    	[""												 	 , ""														],
    	[$r["datos"]["cFecha1"]								 , "" 														]
    ];
	// Configurar anchos proporcionales
	$anchoTotal = $pdf->w; // Ancho total de la página
	$anchoPrimeraColumna = $anchoTotal*.75 ; // 60% del ancho total
	$anchoSegundaColumna = $anchoTotal - ($anchoPrimeraColumna); // El resto del ancho

	$pdf->SetWidths(array($anchoPrimeraColumna, $anchoSegundaColumna));
	$pdf->SetAligns(['C', 'L']);
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
	}
}
// ______________________________________________________________________________
// ______________________________________________________________________________
?>