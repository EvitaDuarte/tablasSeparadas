<?php
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('Letra', 'Courier');
require_once('../assetsF/php/fpdf/mc_table.php');
require_once("rutinas_.php");
function pdfImprimeCheque(&$res){
	try {
		ob_start();
		// __________________________________________________
		$cmPt = (72/2.54);
		// __________________________________________________
	    $pdf = new PDF_MC_Table('P','pt','Letter');
	    $pdf->SetAutoPageBreak(true, 1); // 1 de margen inferior para el footer
        $pdf->AddPage();
    	// $pdf->SetHeights(3); 	// Reducir la altura de línea a n unidades
    	// $pdf->SetSpaceLine(4);	// Dependiendo este valor hay que actualizar la posición de los rectangluos
	    //encabezado($pdf,$res); // Debe ir despues del AddPage
	    foreach($res["resultados"] as $row1){
	    	$x 		= number_format($row1["x"],2)*$cmPt ; 
	    	$y 		= number_format($row1["y"],2)*$cmPt;
	    	$ancho  = number_format($row1["anchura"],2)*$cmPt;
	    	$alto	= number_format($row1["altura"],2)*$cmPt;
	    	$alinea = $row1["alineacion"];
	    	$letra	= $row1["font"];
	    	$tamLetr= $row1["fontsize"];


	    	$valor 	= "--";
	    	
	    	$cCampo = $row1["descripcion"];
	    	switch($cCampo){
	    		case "Letra fecha":
	    			$valor = $res["opera"][0]["fecha"];
	    		break;
	    		case "Beneficiario":
	    			$valor = utf8_decode($res["opera"][0]["beneficiario"]);
	    		break;
	    		case "Importe Numérico":
	    			$valor = $res["opcion"]["importe"];
	    		break;
	    		case "Importe letra":
	    		case "Importe Letra":
	    			$valor = $res["opcion"]["importeLetra"];
	    		break;
	    		case "Concepto de pago":
	    			$valor = utf8_decode($res["opera"][0]["concepto"]);
	    		break;
	    		case "Importe Numérico1":
	    			$valor = $res["opcion"]["importe"];
	    		break;
	    		case "Número de Cheque":
	    			$valor = $res["opera"][0]["cheque"];
	    		break;
	    		case "Somire o Documento":
	    			$valor = $res["opera"][0]["somire"];
	    		break;

	    	}
	    	$pdf->SetXY($x,$y );
	    	$pdf->SetFont($letra, 'B', $tamLetr, '' , true);
	    	$pdf->MultiCell($ancho, $alto, $valor, 0 , $alinea); 

	    }
	    // ___________________________________________________
	    //cuerpo($pdf,$res);
	    // ___________________________________________________
	    ob_end_clean();
	    $tempFilename = '../pdfs/' . trim($res["datos"]["reporte"]) ;

	    $pdf->Output( $tempFilename , 'F');
	    $pdf->Close();
	    $res["mensaje"] = "";
	    $res["success"] = true;
	    $res["archivo"] = 'pdfs/' . trim($res["datos"]["reporte"]) ;

	}catch(Exception $e){
		$res["mensaje"] = "Excepción generara " . $e->getMessage();
		$res["success"] = false;
	}
}
?>