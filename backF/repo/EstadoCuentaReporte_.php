<?php
define('Letra', 'Helvetica');
require('../assetsF/php/fpdf/mc_table.php');
 // ___________________________________________________________________________

function pdfEstadoCuenta(&$res){
	//
    try {
        $cFecIni = date("d-m-Y", strtotime($res["datos"]["fechaI"]) );
        $cFecFin = date("d-m-Y", strtotime($res["datos"]["fechaF"]) );
    	$aDatos = [
            "cuenta"    => $res["datos"]["cCta"],
            "nombre"    => $res["datos"]["nombre"],
    		"fechaIni"	=> $res["datos"]["fechaI"],
    		"fechaFin"	=> $res["opcion"]["fechaF"],
    		"saldoIni"	=> $res["datos"]["saldoAnterior"],
            "DEA"       => utf8("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN"),
            "SOB"       => utf8("SUBDIRECCIÓN DE OPERACIÓN BANCARIA"),
            "letPag"    => utf8("PÁGINA : "),
            "letRep"    => "ESTADO DE CUENTA DEL " . $cFecIni . " AL " . $cFecFin ,
            "letNombre" => "BANCO : " . $res["datos"]["nombre"],
            "letCta"    => "CUENTA : " . $res["datos"]["cCta"],
            "letOpe"    => utf8("OPERACIÓN"),
    		"totalIng"	=> 0.00,
    		"totalEgr"	=> 0.00,
            "SalFin"    => 0.00
    	];
        $aMovs              = $res["resultados"];
        $res["origenes"]    = $aDatos; // Quitar en producción
        // _______________________________________________
        $aAnchos = array(20, 27, 32, 13, 13, 102 , 32 , 32 ); // Si se cambia se tiene que modificar $anchos1 mas abajo

        ob_start();
        // _______________________________________________________________
        $pdf = new PDF_MC_Table('L','mm','Letter');
		$pdf->SetAutoPageBreak(true, 1); 	// 1 de margen inferior para el footer
		$pdf->AliasNbPages('{nb}');	// Define el alias para el número total de páginas
		$pdf->SetTopMargin(7);
		encabezado($pdf,$aDatos,$aAnchos); 	// Debe ir despues del AddPage
		// _______________________________________________________________
        movimientos($pdf,$aDatos,$aAnchos,$aMovs);
        // _______________________________________________________________
 		ob_end_clean();
	    $ip 		  = ipRepo();
	    $tempFilename = '../pdfs/' . trim($ip) ;
        //$nb   =  $pdf->PageNo(); // Obtiene el número total de páginas generadas
	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();
	    $res["mensaje"] 	= "";
	    $res["success"] 	= true;

	    $res["archivo"] 	= 'pdfs/' . trim($ip) ;
        // _______________________________________________________________

    }catch (Exception $e) {
		$res["mensaje"] = "No se logro generar el reporte solicitado " . $e->getMessage();
		$res["success"] = false;
	}
    // _______________________________________________
    //
}
// ________________________________________________________________________________________________
function movimientos($pdf,$aDatos,$aAnchos,$aMovs){
    $x=2        ; $y=34;
    $nI = 0.00  ; $nE = 0.00;
    $nSi=$aDatos["saldoIni"];

    $pdf->SetWidths($aAnchos);
    $pdf->SetXY($x, $y); // x , y  
    $aRen  = array("","","","","","SALDO INICIAL",conComas($nSi),"");
    $pdf->RowSinCuadro($aRen);
    foreach ($aMovs as $m ) {
        if ( $pdf->GetY() + 10 > $pdf->h ){
			encabezado($pdf,$aDatos,$aAnchos);
		}
        $c1    = $c2 = $c3 = "";
        if ($m["tipo"]=="I"){
            $c2  = conComas($m["importeoperacion"]);
            $nI += $m["importeoperacion"];
        }else{
            $c3  = conComas($m["importeoperacion"]);
            $nE += $m["importeoperacion"];
        }
        $cDes  = substr(utf8($m["beneficiario"]."-".$m["concepto"]),0,65);
        $aRen  = array($m["fechaoperacion"],$m["referenciabancaria"],$m["folio"],$m["idoperacion"],$m["idcontrol"],$cDes,$c2,$c3);
		$pdf->RowSinCuadro($aRen);
        
    }
    
    if ( $pdf->GetY() + 20 > $pdf->h ){ //  En teoría 20 son dos renglones ???
        encabezado($pdf,$aDatos,$aAnchos);
    }
    /*
    $aAnchos1 = array(105, 102 , 32 , 32 );
    $pdf->SetWidths($aAnchos1);
    $pdf->SetAligns( ['R','R','R','R']);
    $aRen  = array("","TOTALES",number_format($nI, 2, '.', ','),number_format($nE, 2, '.', ','));
    $pdf->Row($aRen);
    $aAnchos1 = array(73,32, 102 , 32 , 32 );  $pdf->SetWidths($aAnchos1);$pdf->SetAligns( ['R','R','R','R']);
    $aRen  = array("SALDO INICIAL",number_format($nSi, 2, '.', ','),"SALDO FINAL",number_format($nSi+$nI-$nE, 2, '.', ','),"");
    $pdf->Row($aRen);*/
    // A repartir 268 (271)
    
    $aAnchos1 = array(70, 67 , 67 , 67 ); $pdf->SetWidths($aAnchos1); $pdf->SetAligns( ['C','C','C','C']);
    $aRen  = array("SALDO INICIAL","INGRESOS","EGRESOS","SALDO FINAL");                 $pdf->Row($aRen);
    $aRen  = array(conComas($nSi),conComas($nI),conComas($nE),conComas($nSi+$nI-$nE));  $pdf->Row($aRen);
    
}
// ________________________________________________________________________________________________
function encabezado($pdf,$aDatos,$aAnchos){
    // mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    $margen = 2;
    $x1		= $pdf->GetX();
    $pdf->AddPage();							// Configurar el encabezado
    $pdf->SetHeights(3); 						// Reducir la altura de línea a n unidades
    $pdf->SetSpaceLine(5);						// Dependiendo este valor hay que actualizar la posición de los rectangluos
    $pdf->SetFont(Letra, 'B', 11, '' , true);	// Tambien este valor afecta la posición de los rectangluos
    $pdf->SetLeftMargin($margen);
    // ____________________________________________________________
    logoInstitucional1($pdf);

    // ___________________________________________________________
    $pdf->SetX($x1); // para que salgan bien centrados los siguientes encabezados
    // ___________________________________________________________
    $cPag = $aDatos["letPag"]. $pdf->PageNo() . " de ";// . '[ de {total Pages}]');
    $tPag = '{nb}';
    // Arreglo del Encabezado
    $aCabeza = [
    	[" "                    ,$aDatos["DEA"]     ,   "Hora  : " . date("H:i:s")	] ,
    	[" "                    ,$aDatos["SOB"]     , 	"Fecha : " . date("d/m/Y")	],
    	[" "                    ,$aDatos["letRep"]  ,   $cPag . $tPag				],
    	[$aDatos["letNombre"]   ,$aDatos["letCta"]	, 	" "							]
    ];
    // ___________________________________________________________
	// Configurar anchos proporcionales para los títulos principales de la página
	$anchoTotal = $pdf->w; // Ancho total de la página
	$anchoPrimeraColumna = $anchoTotal * 0.45; // % del ancho total
	$anchoSegundaColumna = ($anchoTotal - $anchoPrimeraColumna)/2; // El resto del ancho

	$pdf->SetWidths(array($anchoSegundaColumna,$anchoPrimeraColumna, $anchoSegundaColumna-10));
    $pdf->SetAligns(['L','C', 'R']);
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
	}
	// ___________________________________________________________
	$aCabeza = [
    	["FECHA",$aDatos["letOpe"],"DOCTO","OPE","CTRL","BENEFICIARIO-CONCEPTO","INGRESOS","EGRESOS"]
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
 	$pdf->SetAligns( ['L','L','L','C','C','L','R','R']); // Alineación de las columnas
 	$pdf->SetFont(Letra, 'B', 7);
}
// ________________________________________________________________________________________________
// ________________________________________________________________________________________________