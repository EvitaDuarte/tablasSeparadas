<?php
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('Letra', 'Courier');
require_once('../assetsF/php/fpdf/mc_table.php');
require_once("rutinas_.php");
function pdfImprimeRangoCheques(&$res){
	try {
		ob_start();
		// __________________________________________________
		$cmPt = (72/2.54);
		// __________________________________________________
	    $pdf = new PDF_MC_Table('P','pt','Letter');
	    $pdf->SetAutoPageBreak(true, 1); // 1 de margen inferior para el footer
	    foreach ($res["opera"] as  $che) {
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
		    			$valor = $che["fecha"];
		    		break;
		    		case "Beneficiario":
		    			$valor = utf8_decode($che["beneficiario"]);
		    		break;
		    		case "Importe Numérico":
		    			$valor = number_format($che["importe"], 2, '.', ',');
		    		break;
		    		case "Importe letra":
		    		case "Importe Letra":
		    			$valor = $che["letraimp"];
		    		break;
		    		case "Concepto de pago":
		    			$valor = utf8_decode($che["concepto"]);
		    		break;
		    		case "Importe Numérico1":
		    			$valor = $che["importe"];
		    		break;
		    		case "Número de Cheque":
		    			$valor = $che["cheque"];
		    		break;
		    		case "Somire o Documento":
		    			$valor = $che["somire"];
		    		break;

		    	}
		    	$pdf->SetXY($x,$y );
		    	$pdf->SetFont($letra, 'B', $tamLetr, '' , true);
		    	$pdf->MultiCell($ancho, $alto, $valor, 0 , $alinea); 
		    }
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