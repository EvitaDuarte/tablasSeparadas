<?php
define('Letra', 'Helvetica');
require('../assetsF/php/fpdf/mc_table.php');
function pdfReporteSaldos(&$res){
	// ___________________
	try {
        ob_start();
$res["dummy"]  = "1" ;
		$aDatos  = 	["cuenta"=>$res["datos"]["cuenta"]	,"nombre"=>$res["datos"]["nombre"],
					 "fechaI"=>$res["datos"]["fechaIni"],"fechaF"=>$res["datos"]["fechaFin"]
					];
		// ___________________
$res["dummy"]  = "2" ;					
	    $pdf = new PDF_MC_Table('P','mm','Letter');
	    //$pdf->AliasNbPages($nPagTot);
	    $pdf->SetAutoPageBreak(true, 1); 	// 1 de margen inferior para el footer
		// Define el alias para el número total de páginas
		$pdf->AliasNbPages('{totalPages}');	
$res["dummy"]  = "3" ;		
	    encabezado($pdf,$aDatos); 	// Debe ir despues del AddPage
$res["dummy"]  = "4" ;	    
	 	// _____________________________
	    foreach ($res["resultados"] as $row1) {
	    	if ( $pdf->GetY() + 10 > $pdf->h ){
	    		encabezado($pdf,$aDatos);
	    	}
	    	$ren = array($row1["fechasaldo"],$row1["saldoinicial"],$row1["ingresos"],$row1["egresos"],$row1["cheques"],$row1["saldofinal"]);
	    	$pdf->RowSinCuadro($ren,null);
	    }
	    // ----------------------------
	    ob_end_clean();
	    $tempFilename = '../pdfs/' . trim($res["datos"]["reporte"]) ;

	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();
	    $res["mensaje"] 	= "";
	    $res["success"] 	= true;
	    $res["resultados"]	= $aDatos;
	    $res["archivo"] 	= 'pdfs/' . trim($res["datos"]["reporte"]) ;
	} catch (Exception $e) {
		$res["mensaje"] = "No se logro generar la información solicitada";
		$res["success"] = false;
	}

}

function encabezado($pdf,$aD){
    // Configurar el encabezado
    $pdf->AddPage();
    $pdf->SetHeights(3); 	// Reducir la altura de línea a n unidades
    $pdf->SetSpaceLine(5);	// Dependiendo este valor hay que actualizar la posición de los rectangluos
    $pdf->SetFont(Letra, 'B', 11, '' , true); // Tambien este valor afecta la posición de los rectangluos
    $pdf->SetLeftMargin(2);
    //$pdf->InFooter = true;
    // Cuadro de los encabezados principales
    $pageWidth = $pdf->w - 5;
	$pdf->Rect(2, 8, $pageWidth, 22, 'D');

    // Ruta de la imagen y coordenadas donde se ubicará
    $imagePath = '../assetsF/img/ine_logo_pdf.jpg';
    $x 		= 5;
    $y 		= 11;
    $width 	= 30;
    $height = 0; // 0 para mantener la proporción del tamaño original
    $pdf->Image($imagePath, $x, $y, $width, $height);

    // Arreglo del Encabezado
    $aCabeza = [
    	[" "," ", " "],
    	[" ",utf8("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN"), utf8("PÁGINA : " ). $pdf->PageNo() . ' de {totalPages}' ] ,
    	[" ",uft8("SUBDIRECCIÓN DE OPERACIÓN BANCARIA" 	),   "HORA : " . date("H:i:s")					] ,
    	[" ",utf8("Reporte de Saldos"				 	),  "FECHA : " . date("d/m/Y")					]
    ];
	// Configurar anchos proporcionales
	$anchoTotal = $pdf->w; // Ancho total de la página
	$anchoPrimeraColumna = $anchoTotal * 0.50; // 60% del ancho total
	$anchoSegundaColumna = ($anchoTotal - $anchoPrimeraColumna)/2; // El resto del ancho

	$pdf->SetWidths(array($anchoSegundaColumna,$anchoPrimeraColumna, $anchoSegundaColumna));
    $pdf->SetAligns(['L','C', 'L']);
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
	}

	// Rectángulo para el segundo conjunto de datos rect ( x , y , ancho , alto)
	$pdf->Rect(2, $pdf->GetY(), $pageWidth, 6, 'D'); 
	$nW = $anchoTotal/3;
	$pdf->SetWidths(array($nW,$nW,$nW));
	$pdf->SetAligns(['L','C', 'L']);
	$pdf->SetFont(Letra, 'B', 9, '' , true);
	$aCabeza = [
		["BANCO : " . $aD["nombre"] , " CUENTA: " .  $aD["cuenta"] ,  utf8_decode("Período : ") . $aD["fechaF"] . "->" . $aD["fechaI"]  ]
	]; 		
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
    }
    // -----------------------------
	$aCabeza = [
    	["FECHA","SALDO INICIAL","INGRESOS","EGRESOS","CHEQUES","SALDO FINAL"]
    ];
    //$this->GuardaXY();
    //$y = $this->GetY();//$this->RecuperaY();
    //$this->Ln($y+1);
    $aAnchos = array(20, 38, 38, 38, 38, 39 );
    $pdf->Ln(1); // agrega un punto al espaciado
    $pdf->SetWidths($aAnchos); // Ancho de las columnas
    $pdf->SetFont(Letra, 'B', 9);
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
	$pdf->SetAligns( ['L','R','R','R','R','R']);
	$pdf->SetWidths($aAnchos);
}


?>