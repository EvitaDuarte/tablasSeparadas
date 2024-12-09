<?php
define('Letra', 'Helvetica');
require('../assetsF/php/fpdf/mc_table.php');
 // ___________________________________________________________________________

function ReintegrosReporte(&$res){
	//
	$aDatos = [
		"fechaIni"	=> $res["opcion"]["fecIni"],
		"fechaFin"	=> $res["opcion"]["fecFin"],
		"letrero"	=> $res["opcion"]["letrero"],
		"letrero1"	=> $res["opcion"]["letrero1"],
		"totalUr"	=> 0.00,
		"totalJLE"	=> 0.00,
		"totalJLD"	=> 0.00,
		"totalGral"	=> 0.00
	];
	//
	$aReinte = $res["resultados"];
	//
	$aAnchos = array(10, 20, 20, 46, 29, 29 , 29 , 20 ); // 18
	try {
        ob_start();
        // _______________________________________________________________
        $pdf = new PDF_MC_Table('P','mm','Letter');
		$pdf->SetAutoPageBreak(true, 1); 	// 1 de margen inferior para el footer
		$pdf->AliasNbPages('{totalPages}');	// Define el alias para el número total de páginas
		$pdf->SetTopMargin(7);
		encabezado($pdf,$aDatos,$aAnchos); 	// Debe ir despues del AddPage
		// _______________________________________________________________
		$res["unidades"] = generaArregloUnidades($res["unidades"]);
		$totales 		 = array();
        // _______________________________________________________________
        reintegros($pdf,$aDatos,$aAnchos,$res["resultados"],$res["unidades"],$totales);
        // _______________________________________________________________
        resumen($pdf,$totales,$aDatos["letrero"]);
        // _______________________________________________________________
 		ob_end_clean();
	    $ip 		  = ipRepo();
	    $tempFilename = '../pdfs/' . trim($ip) ;
	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();
	    $res["mensaje"] 	= "";
	    $res["success"] 	= true;
	    $res["origenes"]	= $aDatos;
	    $res["totales"]		= $totales;
	    $res["archivo"] 	= 'pdfs/' . trim($ip) ;
        // _______________________________________________________________

    }catch (Exception $e) {
		$res["mensaje"] = "No se logro generar el reporte solicitado " . $e->getMessage();
		$res["success"] = false;
	}
}
// ___________________________________________________________________________
function reintegros($pdf,$aDatos,$aAnchos,$reintegros,$unidades,&$totales){
	$x =3; $y=37; $cUr1= "";
	$nTotUr = 0.00;$nTotJle = 0.00 ; $nTotDto = 0.00; $nTotGral = 0.00;
	$nJle = 0 ; $nDto =0;


	$pdf->SetXY($x, $y); // x , y  
	$pdf->SetWidths($aAnchos);
	foreach ($reintegros as $r ) {
		$cUr	= trim($r["idunidad"]);
		$nMonto	= $r["monto"];
		$cMonto = number_format($nMonto, 2, '.', ',');
		// Hay cambio de UR
		if ( $cUr!==$cUr1){
    		// Total de la UR
    		if ($cUr1!==""){
    			if ( $pdf->GetY() + 10 > $pdf->h ){
    				encabezado($pdf,$aDatos,$aAnchos);
    			}
    			$cMonto = number_format($nTotUr, 2, '.', ',');
				$aRen	= array("","","","","","","TOTAL ".$cUr1,$cMonto);
				$pdf->RowSinCuadro($aRen);
				if (  substr($cUr1,-2)=="00"){
					$nJle += 1;
				}else{
					$nDto += 1;
				}
    		}
    		// Imprime la UR y su nombre
	    	if ( $pdf->GetY() + 10 > $pdf->h ){
    			encabezado($pdf,$aDatos,$aAnchos);
    			$pdf->SetXY($x, $y); // x , y	  
    		}
    		$pdf->SetFont(Letra, 'B', 9);
			$pdf->Cell(0, 10, $cUr . " " . $unidades[$cUr]); // El 10 indica la altura del texto
			$pdf->Ln(); // Añade una nueva línea 
			$pdf->SetFont(Letra, 'B', 7);
			//
			$cUr1	= $cUr;
			$nTotUr = 0.00;
		}
    	if ( $pdf->GetY() + 10 > $pdf->h ){
			encabezado($pdf,$aDatos,$aAnchos);
		}
		$cDes  = utf8_decode($r["descripcion"]);
		$aRen  = array("",$r["anio"],$r["folio"],$r["oficio"],$r["fecha_ope"],$cDes,$r["operacion"],$cMonto);
		$pdf->RowSinCuadro($aRen);
		// Totales
		$nTotUr		+= $nMonto;
		$nTotGral	+= $nMonto; 
		if (  substr($cUr,-2)=="00"){
			$nTotJle += $nMonto;
		}else{
			$nTotDto += $nMonto;
		}
	}
	$totales = [
	    $nJle . " JUNTAS LOCALES"		=> number_format($nTotJle, 2, '.', ','),
	    $nDto . " JUNTAS DISTRITALES"	=> number_format($nTotDto, 2, '.', ','),
	    "TOTAL GENERAL : "				=> number_format($nTotGral, 2, '.', ',')
	];

}
// ___________________________________________________________________________
function resumen($pdf,$totales,$letrero){
    $pdf->AddPage();
    logoInstitucional($pdf);
	$pdf->SetXY(2, 100); // x , y  
	CentraCadena($pdf, "INSTITUTO NACIONAL ELECTORAL"					,9);
	CentraCadena($pdf, utf8_decode("SUBDIRECCIÓN DE OPERACIÓN BANCARIA"),9);
	CentraCadena($pdf, "RECURSOS NO EJERCIDOS :"						,9);
	CentraCadena($pdf, $letrero											,9);
	$pdf->Ln(20);


	// Definir el rectángulo
	$x = 50; // Coordenada X
	$y = 140; // Coordenada Y
	$w = 120; // Ancho del rectángulo
	$h = 70; // Altura del rectángulo

	// Dibuja el rectángulo
	$pdf->RoundedRect($x, $y, $w, $h, 5); // x , y , ancho , altura , radio esquinas


	// Altura de cada fila
	$height		= 10;
	$halfWidth	= $w / 2 ; 	// Ancho de la mitad del rectángulo
	$marDer		= 10; 		// Margen Derecho 

	// Posicionar el cursor para empezar a imprimir
	$pdf->SetXY($x, $y+20);

	// Iterar sobre el arreglo y imprimir en las dos mitades
	$i = 0;
	foreach ($totales as $index => $value) {
	    // Imprimir el índice en la primera mitad del rectángulo
	    $pdf->SetX($x); // Posición X para la primera mitad
	    $pdf->Cell($halfWidth, $height, $index, 0, 0, 'R'); // Imprime el índice
	    $i++;
	    // Imprimir el valor en la segunda mitad del rectángulo
	    $pdf->SetX($x + $halfWidth); // Posición X para la segunda mitad
	    if ($i == 3) {
        	$pdf->SetFillColor(211, 211, 211); // Gris claro (RGB: 211, 211, 211)
        	 $pdf->Cell($halfWidth-$marDer, $height, $value, 0, 1, 'R', true); // Con background
    	}else{
			$pdf->Cell($halfWidth-$marDer, $height, $value, 0, 1, 'R'); // Imprime el valor -10 es par
    	}
	    
	}
	$pdf->SetFillColor(255, 255, 255); // Blanco (para las otras celdas)
}
// ___________________________________________________________________________
function generaArregloUnidades($unidades){ // Para que el índice del arreglo sea la UR y se pued obtener directamente el nombre

	$arregloNuevo = [];

	foreach ($unidades as $unidad) {
    	$arregloNuevo[$unidad["idunidad"]] = utf8_decode($unidad["nombreunidad"]);
	}

	return $arregloNuevo;
}
// ___________________________________________________________________________
function encabezado($pdf,$aDatos,$aAnchos){
    $margen = 2;
    $x1		= $pdf->GetX();
    $pdf->AddPage();							// Configurar el encabezado
    $pdf->SetHeights(3); 						// Reducir la altura de línea a n unidades
    $pdf->SetSpaceLine(5);						// Dependiendo este valor hay que actualizar la posición de los rectangluos
    $pdf->SetFont(Letra, 'B', 11, '' , true);	// Tambien este valor afecta la posición de los rectangluos
    $pdf->SetLeftMargin($margen);
    // ____________________________________________________________
    logoInstitucional($pdf);

    // ___________________________________________________________
    $pdf->SetX($x1); // para que salgan bien centrados los siguientes encabezados
    // ___________________________________________________________

    // Arreglo del Encabezado
    $cW  = $aDatos["letrero1"]; // . fechaLetra($aDatos["fecha"]);
    $cW1 = $aDatos["letrero"]; 
    $aCabeza = [
    	[" ",utf8_decode("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN"),   utf8_decode("Página : " ). $pdf->PageNo() . ' de {totalPages}'	] ,
    	[" ",utf8_decode("SUBDIRECCIÓN DE OPERACIÓN BANCARIA" 	), 	"Hora  : " . date("H:i:s")										] ,
    	[" ",utf8_decode($cW)									 ,  "Fecha : " . date("d/m/Y")										],
    	[" ",utf8_decode($cW1)									 , 	""																]
    ];
    // ___________________________________________________________
	// Configurar anchos proporcionales
	$anchoTotal = $pdf->w; // Ancho total de la página
	$anchoPrimeraColumna = $anchoTotal * 0.45; // % del ancho total
	$anchoSegundaColumna = ($anchoTotal - $anchoPrimeraColumna)/2; // El resto del ancho

	$pdf->SetWidths(array($anchoSegundaColumna,$anchoPrimeraColumna, $anchoSegundaColumna));
    $pdf->SetAligns(['L','C', 'L']);
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
	}
	// ___________________________________________________________
	$aCabeza = [
    	["U.R",utf8_decode("Año Reintegro"),"Folio","Oficio","Fecha","Tipo",utf8_decode("Operación"),"Importe"]
    ];
    $pdf->GuardaXY();
    //$y = $this->GetY();//$this->RecuperaY();
    //$this->Ln($y+1);
    // 				 20, 38, 38, 38, 38, 39
    
    $pdf->Ln(1); // agrega un punto al espaciado
    $pdf->SetWidths($aAnchos); // Ancho de las columnas
    $pdf->SetFont(Letra, 'B', 9);
    $pdf->SetAligns( ['C','C','C','C','C','C','C','C']); // Alineación de las columnas
    foreach ($aCabeza as $row) {
        $pdf->Row($row,null);
    }
	// ___________________________________________________________
    // Posición inicial
    $y 			= $pdf->GetY();
    $totalWidth = array_sum($aAnchos);
    $alto 		= $pdf->h - $y - 4;

	$pdf->Rect($margen, $y  , $totalWidth, $alto, 'D'); // rectangulo
	/*
	$x = $margen;
    for ($i=0;$i<6;$i++){									// líneas verticales
    	$x = $x + $aAnchos[$i] ;
   		$pdf->Rect($x,$y,0,$alto , 'D');					// SI utilizo line no salen del mismo tamaño
   	}
   	*/
 	$pdf->SetAligns( ['L','L','L','L','L','L','L','R']); // Alineación de las columnas
 	$pdf->SetFont(Letra, 'B', 7);
	// ___________________________________________________________

}
// ___________________________________________________________________________
function logoInstitucional($pdf){
    $imagePath = '../assetsF/img/ine_logo_pdf.jpg';
    $x 			= 5;
    $y 			= 5;
    $width 		= 30;
    $height 	= 0; // 0 para mantener la proporción del tamaño original
    $pdf->Image($imagePath, $x, $y, $width, $height);
}
// ___________________________________________________________________________
function CentraCadena($pdf, $text, $height = 10, $fontFamily = Letra, $fontStyle = 'B', $fontSize = 12) {
    // Establecer la fuente
    $pdf->SetFont($fontFamily, $fontStyle, $fontSize);

    // Obtener el ancho de la página y el ancho del texto
    $pageWidth = $pdf->GetPageWidth();
    $textWidth = $pdf->GetStringWidth($text);

    // Calcular la posición X para centrar el texto
    $xPos = ($pageWidth - $textWidth) / 2;

    // Establecer la posición X
    $pdf->SetX($xPos);

    // Imprimir el texto centrado
    $pdf->Cell($textWidth, $height, $text, 0, 1, 'C');
    //$pdf->Ln();
}
// ___________________________________________________________________________
?>