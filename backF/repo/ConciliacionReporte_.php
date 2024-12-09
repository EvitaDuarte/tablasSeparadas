<?php
define('Letra', 'Helvetica');
require('../assetsF/php/fpdf/mc_table.php');
 // ___________________________________________________________________________
function ConciliacionReporte(&$res){

	$cCta 		= $res["opcion"]["cuenta"];
	$cFecha		= $res["opcion"]["fecha"];
	$oMovConci	= $res["opcion"]["objeto"];
	$aAnchos	= array(20, 30, 38, 28, 29, 29 , 29 );
	$cRegreso	= "";
	$aMovBan 	= array();
	$aMovIne	= array();
	if ( $oMovConci->movimientosBanco($cCta,$cFecha,$cRegreso)==true ){
		$res["ctas"] = $cRegreso; 
		$aMovBan	 = $cRegreso;
		
	}
	if ( $oMovConci->movimientosIne($cCta,$cFecha,$cRegreso)==true ){
		$res["Ine"] = $cRegreso;
		$aMovIne	= $cRegreso;
	}
	
	try {
        ob_start();
        // _______________________________________________________________
        $aDatos = [
        			"cuenta"=>$res["opcion"]["cuenta"],
        			"fecha"=>$res["opcion"]["fecha"],
        			"nombre"=>$res["opcion"]["nombre"],
        			"susC"=>0.00,
        			"nuesC"=>0.00,
        			"susA"=>0.00,
        			"nuesA"=>0.00,
        			"SaldoBan"=>$res["datos"]["SaldodelBanco"],
        			"SaldoIne"=>$res["datos"]["SaldoFinIne"]
        		  ];
        // _______________________________________________________________
        $pdf = new PDF_MC_Table('P','mm','Letter');
		$pdf->SetAutoPageBreak(true, 1); 	// 1 de margen inferior para el footer
		$pdf->AliasNbPages('{totalPages}');	// Define el alias para el número total de páginas
		$pdf->SetTopMargin(7);
		encabezado($pdf,$aDatos,$aAnchos); 	// Debe ir despues del AddPage
        // _______________________________________________________________
        MovimientosBanco($pdf,$aDatos,$aAnchos,$aMovBan);
        MovimientosINE($pdf,$aDatos,$aAnchos,$aMovIne);
        // _______________________________________________________________
        totales($aDatos,$pdf);
        // _______________________________________________________________
	    ob_end_clean();
	    $ip 		  = ipRepo();
	    $tempFilename = '../pdfs/' . trim($ip) ;
	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();
	    $res["mensaje"] 	= "";
	    $res["success"] 	= true;
	    $res["resultados"]	= $aDatos;
	    $res["archivo"] 	= 'pdfs/' . trim($ip) ;
        // _______________________________________________________________
    }catch (Exception $e) {
		$res["mensaje"] = "No se logro generar la información solicitada " . $e->getMessage();
		$res["success"] = false;
	}

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
    $imagePath = '../assetsF/img/ine_logo_pdf.jpg';
    $x 			= 5;
    $y 			= 5;
    $width 		= 30;
    $height 	= 0; // 0 para mantener la proporción del tamaño original
    $pdf->Image($imagePath, $x, $y, $width, $height);
    // ___________________________________________________________
    $pdf->SetX($x1); // para que salgan bien centrados los siguientes encabezados
    // ___________________________________________________________

    // Arreglo del Encabezado
    $cW  = "CONCILIACIÓN BANCARIA AL " . fechaLetra($aDatos["fecha"]);
    $cW1 = "BANCO : " . $aDatos["nombre"] . " CUENTA : " . $aDatos["cuenta"] ;
    $aCabeza = [
    	[" ",utf8_decode("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN"), utf8_decode("PÁGINA : " ). $pdf->PageNo() . ' de {totalPages}' ] ,
    	[" ",utf8_decode("SUBDIRECCIÓN DE OPERACIÓN BANCARIA" 	),   "HORA : " . date("H:i:s")					] ,
    	[" ",utf8_decode($cW)									 ,  "FECHA : " . date("d/m/Y")					],
    	[" ",utf8_decode($cW1)									 , ""]
    ];
    // ___________________________________________________________
	// Configurar anchos proporcionales
	$anchoTotal = $pdf->w; // Ancho total de la página
	$anchoPrimeraColumna = $anchoTotal * 0.50; // 60% del ancho total
	$anchoSegundaColumna = ($anchoTotal - $anchoPrimeraColumna)/2; // El resto del ancho

	$pdf->SetWidths(array($anchoSegundaColumna,$anchoPrimeraColumna, $anchoSegundaColumna));
    $pdf->SetAligns(['L','C', 'L']);
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
	}
	// ___________________________________________________________
	$aCabeza = [
    	["FECHA","DOCUMENTO","CONCEPTO","SUS CARGOS","NUESTROS CARGOS","SUS ABONOS","NUESTROS ABONOS"]
    ];
    $pdf->GuardaXY();
    //$y = $this->GetY();//$this->RecuperaY();
    //$this->Ln($y+1);
    // 				 20, 38, 38, 38, 38, 39
    
    $pdf->Ln(1); // agrega un punto al espaciado
    $pdf->SetWidths($aAnchos); // Ancho de las columnas
    $pdf->SetFont(Letra, 'B', 9);
    $pdf->SetAligns( ['C','C','C','C','C','C','C']); // Alineación de las columnas
    foreach ($aCabeza as $row) {
        $pdf->Row($row,null);
    }
	// ___________________________________________________________
    // Posición inicial
    $y 			= $pdf->GetY();
    $totalWidth = array_sum($aAnchos);
    $alto 		= $pdf->h - $y - 4;

	$pdf->Rect($margen, $y  , $totalWidth, $alto, 'D'); // rectangulo
	$x = $margen;
    for ($i=0;$i<6;$i++){									// líneas verticales
    	$x = $x + $aAnchos[$i] ;
   		$pdf->Rect($x,$y,0,$alto , 'D');					// SI utilizo line no salen del mismo tamaño
   	}
 	$pdf->SetAligns( ['L','L','L','R','R','R','R']); // Alineación de las columnas
 	$pdf->SetFont(Letra, 'B', 7);
	// ___________________________________________________________

}
// ___________________________________________________________________________
function MovimientosBanco($pdf,&$aDatos,$aAnchos,$aMovBan){
	//$pdf->SetFont(Letra, 'N', 7); // Si se pone N se genera una excepción 
	foreach($aMovBan as $mov){
		$Cargo  = 0.00; $Abono  = 0.00;
		$bCargo = ""  ; $bAbono = "";
		$nImpo  = $mov["importeoperacion"];
		if ($nImpo!=0.00){
			if ($mov["fch_d_h"]=="-B"){
				$Abono	= $nImpo;
				$bAbono = number_format($Abono, 2, '.', ',');
				$aDatos["susA"] += $nImpo;
			}else{
				$Cargo	= $nImpo;
				$bCargo = number_format($Cargo, 2, '.', ',');
				$aDatos["susC"] += $nImpo;
			}
		}
		$cpto	= substr($mov["concepto"],0,20);// 
		$bCero  = "";
		$aRen  = array($mov["fechaoperacion"],$mov["id_layout_banco"],$cpto,$bCargo,$bCero,$bAbono,$bCero);
    	if ( $pdf->GetY() + 10 > $pdf->h ){
    		encabezado($pdf,$aDatos,$aAnchos);
    	}
    	$pdf->RowSinCuadro($aRen);
	}
}
// ___________________________________________________________________________
function MovimientosINE($pdf,&$aDatos,$aAnchos,$aMovIne){
	foreach($aMovIne as $mov){
		$Cargo  = 0.00; $Abono  = 0.00;  $cpto = "";
		$bCargo = ""  ; $bAbono = ""  ;
		$nImpo  = $mov["importeoperacion"];
		if ($nImpo!=0.00){
			if ($mov["tipo"]=="I"){
				$Cargo	= $nImpo;
				$bCargo = number_format($Cargo, 2, '.', ',');
				$aDatos["nuesC"] += $nImpo;
			}else{
				$Abono	= $nImpo;
				$bAbono = number_format($Abono, 2, '.', ',');
				$aDatos["nuesA"] += $nImpo;
			}
		}
		if ($mov["tipo"]=="I"){
			$cpto = substr($mov["concepto"],0,20);
			if (trim($cpto)==""){
				$cpto = substr($mov["beneficiario"],0,20);
			}
		}else{
			$cpto = substr($mov["beneficiario"],0,20);
			if($cpto=="INSTITUTO NACIONAL E"){ // 20 caracteres
				$cpto = substr($mov["concepto"],0,20);	
			}
		}
		// INSTITUTO NACIONAL ELECTORAL
		$bCero  = "";
		$aRen  = array($mov["fechaoperacion"],$mov["referenciabancaria"],$cpto,$bCero,$bCargo,$bCero,$bAbono);
    	if ( $pdf->GetY() + 10 > $pdf->h ){
    		encabezado($pdf,$aDatos,$aAnchos);
    	}
    	$pdf->RowSinCuadro($aRen);
	}

}
// ___________________________________________________________________________
function totales($aDat,$pdf){


	$pdf->Row( array("","","TOTALES", 
						number_format($aDat["susC"], 2, '.', ','),
						number_format($aDat["nuesC"], 2, '.', ','),
						number_format($aDat["susA"], 2, '.', ','),
						number_format($aDat["nuesA"], 2, '.', ',') 
					) , 
				null );

	$pdf->Row( array("","","SU SALDO", 
								"",
								number_format($aDat["SaldoBan"], 2, '.', ','),
								"",
								"" ) , 
				null );

	$pdf->Row( array("","","NUESTRO SALDO", 
								"",
								"",
								number_format($aDat["SaldoIne"], 2, '.', ','),
								"" ) , 
				null );
	$pdf->Row( array("","","NUESTROS ABONOS", 
								"",
								number_format($aDat["nuesA"], 2, '.', ','),
								"",
								"" ) , 
				null );
	$pdf->Row( array("","","SUS CARGOS", 
								"",
								"",
								number_format($aDat["susC"], 2, '.', ','),
								"" ) , 
				null );
	// Sumas iguales o diferentes  ( ncmenosna + thisform._nSaldoBan )   samenossc + thisform._nSaldoLib
	$nSumaBanco = $aDat["nuesC"] - $aDat["nuesA"] + round(floatval($aDat["SaldoBan"]),2);
	$nSumaIne   = $aDat["susA"]  - $aDat["susC"]  + round(floatval($aDat["SaldoIne"]),2);
	$cSumas     = "SUMAS DIFERENTES";
	if (round($nSumaBanco,2)==round($nSumaIne,2) ){
		$cSumas = "SUMAS IGUALES";
	}

	$pdf->Row( array("","",$cSumas, 
								"",
								number_format($nSumaBanco, 2, '.', ','),
								number_format($nSumaIne  , 2, '.', ','),
								"" ) , 
				null );
}
// ___________________________________________________________________________
// ___________________________________________________________________________
?>