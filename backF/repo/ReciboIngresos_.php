<?php
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('Letra', 'Helvetica');
require_once('../assetsF/php/fpdf/mc_table.php');
require_once("rutinas_.php");
// ______________________________________________________________________________
function pdfReciboIngreso(&$res){
	try {
		ob_start();
		// __________________________________________________
	    $pdf = new PDF_MC_Table('P','mm','Letter');
	    $pdf->SetAutoPageBreak(true, 1); // 1 de margen inferior para el footer
	    encabezado($pdf,$res); // Debe ir despues del AddPage
	    // ___________________________________________________
	    cuerpo($pdf,$res);
	    // ___________________________________________________
	    ob_end_clean();
	    $tempFilename = '../pdfs/' . trim($res["datos"]["reporte"]) ;

	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();
	    $res["mensaje"] = "";
	    $res["success"] = true;
	    $res["archivo"] = 'pdfs/' . trim($res["datos"]["reporte"]) ;

	}catch(Exception $e){
		$res["mensaje"] = "No se logro generar la información solicitada";
		$res["success"] = false;
	}
}
// ______________________________________________________________________________
function cuerpo($pdf,$res){
	$pdf->underline = true; // Se requiere que en fpdf esta propiedad este pública
	$cUr	 = "UR : " . $res["datos"]["ur"]; 
	$cRecibo = "FOLIO : " . $res["datos"]["recibo"];
	$cFecha	 = "FECHA : " . strFecha($res["datos"]["fecha"]) ;
	$cImpo	 = "BUENO POR : $" . $res["datos"]["importe"];
	$pdf->SetXY(10, 40); 	$pdf->Cell(0,0,$cUr,0,0,'L');
	$pdf->SetXY(120,40); 	$pdf->Cell(0,0,$cRecibo,0,0,'L');
	$pdf->SetXY(10, 50);	$pdf->Cell(0,0,$cFecha,0,0,'L');
	$pdf->SetXY(120,50); 	$pdf->Cell(0,0,$cImpo,0,0,'L');
	$pdf->underline = false;

	$nX = 25;
	$pdf->SetXY(10, 60); 	$pdf->Cell($nX,0,"CON LETRA:"	,0,0,'R');
	$pdf->SetXY(10, 80);	$pdf->Cell($nX,0,"RECIBIMOS"	,0,0,'R');
	$pdf->SetXY(10, 85);	$pdf->Cell($nX,0,"DE :"			,0,0,'R');
	$pdf->SetXY(10,100);	$pdf->Cell($nX,0,"POR"			,0,0,'R');
	$pdf->SetXY(10,105);	$pdf->Cell($nX,0,"CONCEPTO"		,0,0,'R');
	$pdf->SetXY(10,110);	$pdf->Cell($nX,0,"DE :"			,0,0,'R');

	$nH = 80;
	$pdf->SetFillColor(192,192,192);
	$pdf->Rect(35, 58, $pdf->w-35-10, $nH, 'F');
	$pdf->Rect(35, 58, $pdf->w-35-10, $nH, 'D');

	$nX = 37;
							// El especificar el alto 5 hace que se tenga que ajustar la coordenada y de setxy
	$pdf->SetXY($nX, 58);	$pdf->MultiCell(0, 5, $res["datos"]["importeLetra"]); 
	$pdf->SetXY($nX, 77);	$pdf->MultiCell(0, 5, utf8_decode($res["datos"]["beneficiario"]) ) ;
	$pdf->SetXY($nX, 97);	$pdf->MultiCell(0, 5, utf8_decode($res["datos"]["concepto"]) );

	// Subrayado Importe con letra
	$nX = 38; $nXw = $pdf->w - $nX - 12 ;
	for ($i=2;$i<5;$i++){
		$pdf->Line($nX, 59+(($i-1)*5) , $nX + $nXw , 59+(($i-1)*5) );
	}
	// Subrayado de beneficiario
	$nX = 38; $nXw = $pdf->w - $nX - 12 ;
	for ($i=2;$i<5;$i++){
		$pdf->Line($nX, 77+(($i-1)*5) , $nX + $nXw , 77+(($i-1)*5) );
	}
	// Subrayado de concepto
	$nX = 38; $nXw = $pdf->w - $nX - 12 ;
	for ($i=2;$i<10;$i++){
		$pdf->Line($nX, 97+(($i-1)*5) , $nX + $nXw , 97+(($i-1)*5) );
	}
	$y = 142;
	// Línea de Separación con Desglose
	$pdf->Line(5, $y , $pdf->w - 5 , $y );
	$y = 147;
	$pdf->SetXY(10, $y); 	$pdf->Cell(0,0,"DESGLOSE DEL RECIBO DE INGRESOS : ". $res["datos"]["recibo"]	,0,0,'L');

	// Tabla del Desglose
	$y = 152;
	$w = $pdf->w - ($pdf->w/3);
	$pdf->Rect(10, $y, $w , ( 5 * 3 ), 'D');
	for ($i=2;$i<4;$i++){
		$pdf->Line(10, $y+(($i-1)*5) , $pdf->w - ($pdf->w/3) + 10 , $y+(($i-1)*5) );

		$pdf->Line( 10 + ( ($i-1) * ($w/3) ) , $y,  10 + ( ($i-1) * ($w/3) ) , $y+(15) );
	}

	// Contenido del Desglose
	$pdf->SetXY(10, 152); 			$pdf->Cell($w/3,5,utf8_decode("NÚMERO")		,0,0,'C');
	$pdf->SetXY(10+($w/3), 152); 	$pdf->Cell($w/3,5,utf8_decode("A CARGO DE")	,0,0,'C');
	$pdf->SetXY(10+($w*2/3), 152); 	$pdf->Cell($w/3,5,utf8_decode("IMPORTE")	,0,0,'C');

	$cNomCta = utf8_decode(substr($res["datos"]["nombreCuenta"],0,19));
	$pdf->SetXY(10, 157); 			$pdf->Cell($w/3,5,$res["datos"]["referencia"]		,0,0,'L');
	$pdf->SetXY(10+($w/3)  , 157); 	$pdf->Cell($w/3,5,$cNomCta							,0,0,'L');
	$pdf->SetXY(10+($w*2/3), 157); 	$pdf->Cell($w/3,5,$res["datos"]["importe"]			,0,0,'R');

	$pdf->SetXY(10+($w/3)  , 162); 	$pdf->Cell($w/3,5,"TOTAL"							,0,0,'R');
	$pdf->SetXY(10+($w*2/3), 162); 	$pdf->Cell($w/3,5,$res["datos"]["importe"]			,0,0,'R');

	$pdf->Line( 10,$pdf->GetPageHeight()-20,  80 , $pdf->GetPageHeight()-20 );
	$pdf->SetXY(10,$pdf->GetPageHeight()-18); $pdf->Cell(70,5,$res["datos"]["depto"]	,0,0,'C');
	$pdf->SetXY(10,$pdf->GetPageHeight()-13); $pdf->Cell(70,5,$res["datos"]["empleado"]	,0,0,'C');

}
// ______________________________________________________________________________
function encabezado($pdf,$r){
    // Configurar el encabezado
    $pdf->AddPage();
    $pdf->SetHeights(3); 	// Reducir la altura de línea a n unidades
    $pdf->SetSpaceLine(4);	// Dependiendo este valor hay que actualizar la posición de los rectangluos
    $pdf->SetFont(Letra, 'B', 10, '' , true); // Tambien este valor afecta la posición de los rectangluos

    $pageWidth  = $pdf->w - 10;
    $AltoPagina = $pdf->h - 40;

    // _______________________________
        // Arreglo del Encabezado
    $cW = "CUENTA : ". $r["datos"]["idCuentaBancaria"] . " - " . $r["datos"]["nombreCuenta"];
    $aCabeza = [
    	[utf8_decode("INSTITUTO NACIONAL ELECTORAL")				], 
    	[utf8_decode("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN" )  	],
    	[utf8_decode("DIRECCIÓN DE RECURSOS FINANCIEROS")		    ],
    	[utf8_decode("RECIBO DE INGRESOS")							],
    	[utf8_decode($cW)							 			    ]
    ];
    foreach ($aCabeza as $row) {
    	$pdf->SetAligns(['C']);
        $pdf->RowSinCuadro($row);
	}
	$pdf->SetLineWidth(0.5);
	$pdf->Rect(5, 35, $pageWidth, $pdf->GetPageHeight()-35-27, 'D');// 35 es por que ahí inicia el rectángulo
	$pdf->SetLineWidth(0.2);
}
// ______________________________________________________________________________
?>