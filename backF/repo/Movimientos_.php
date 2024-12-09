<?php
define('Letra', 'Helvetica');
 // ___________________________________________________________________________
 // Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');
// ___________________________________________________________________________
require('../assetsF/php/fpdf/mc_table.php');
include_once("con_pg_OpeFinW1_.php"); 	// Se incluye conexión a la Base de Datos
include_once("rutinas_.php");			// Rutinas de uso general
 // ___________________________________________________________________________

function EnviaPdf(&$regreso){
    global $conn_pg;
    try{
        // Datos que se deben pasar 
        $datos               = $regreso["opcion"];
        $cTabla  		     = trim($datos["tabla"]);		// Nombre de la tabla PostgreSQL
        $cCampos 		     = trim($datos["campos"]);		// campos de la tabla a mostrar en el HTML
        $join			     = trim($datos["join"]);         // Si hay relación entre 2 o mas tablas
        $order 			     = trim($datos["order"]);        // Orden en que saldrá la información
        $id 	 		     = trim($datos["id"]);			// campo o campo id para hecer un count
        $campo	 		     = trim($datos["busca"]);		// Texto a Buscar
        $tipos			     = explode(",",$datos["tipos"]); // Tipo de los campos( C-Caracter , D-Fecha )
        $totalFiltro         = 0;
        $where               = '';
        $lCreaPdf            = true;
        //
        $cFecIni             = date("d-m-Y", strtotime($datos["fechaIni"]) );
        $cFecFin             = date("d-m-Y", strtotime($datos["fechaFin"]) );
        $letrero1            = utf8("BÚSQUEDA DE MOVIMIENTOS");
        $letrero2            = "DEL " . $cFecIni . " AL " . $cFecFin;
        $pdf                 = new PDF_MC_Table('L','mm','Letter');
        $cCta1               = $cCta2 = ""; 
        $aAnchos             = array(16, 34, 31, 11, 23, 102 , 22, 32 ); 
        $order               = " order by idcuentabancaria , fechaoperacion desc";

    //
        $campo 	 = pg_escape_string($conn_pg, $campo); 
    //
        if ( isset($datos["aCampos"])){ // Se definieron los campos en $datos["aCampos"]
            $aCampos = $datos["aCampos"];
            $columns = $aCampos; //explode(",", $cCampos);
        }else{                          // Se defieron los campos en $datos["campos"]
            $columns = explode(",", $cCampos);
            $aCampos = $columns;
        }
    //  Si hay texto a buscar empezar a construir el where 
        if ($campo != null && $campo!="") {
            $where = construye_where($campo, $aCampos,$tipos);
        }
        if ($join!=""){ //  SI se especifico un join
            if ($where===""){
                $where = " where ( $join ) ";
            }else{
                $where .= " and ( $join ) ";
            }
        }

         // quita el alias de la tabla a.cheque por cheque, a.idcuenta por idcuenta
        for($i=0;$i<count($columns);$i++){
            $columns[$i] = preg_replace('/[a-z]+\./', '', $columns[$i]);
        }

       // Se requiere consultar el número de registros por si hay que traer por pedazos la información
        $sql		    = "SELECT count($id) as numreg1 FROM $cTabla $where " ;
        $res1 		    = ejecutaSQL_conn_pg($conn_pg,$sql);
        $totalFiltro    = (int)$res1[0]["numreg1"];
        $regreso["sql"] = $sql;

        if ($totalFiltro === 0) {
            $regreso["mensaje"] = "No existe información, con los datos solicitados";
            return false; // No hay información
        }

        // Parámetros para la paginación
        $loteTamano = 100000; // Número de registros por lote, este dato se obtuvo al hacer pruebas a partir de la cual
                              // no aparece el error Allowed memory size of 536870912 bytes exhausted
        $totalLotes = ceil($totalFiltro / $loteTamano);
        $nTotal     = 0.00;
        for ($lote = 0; $lote < $totalLotes; $lote++) {
            $offset = $lote * $loteTamano;

            // Consulta con paginación
            $sql                = "SELECT $cCampos FROM $cTabla $where $order LIMIT $loteTamano OFFSET $offset";
            $resultado          = ejecutaSQL_conn_pg($conn_pg, $sql);
            $regreso["sql1"]    = $sql;

            // Verifica si hay resultados
            if ($resultado === false || $resultado === NULL) {
                continue; // Salta este lote si no hay datos
            }

            if ($lCreaPdf){
                creaPdf($pdf);
                Encabezado1($pdf,$letrero1,"",$letrero2,$aAnchos,"");
                $lCreaPdf = false; // Solo una vez
            }

            // Procesa y escribe los datos del lote en el archivo
            foreach ($resultado as $registro) {
                $aMov = []; // Arreglo para almacenar los valores procesados
                
                for ($i = 0; $i < count($registro); $i++) {
                    $cDato = trim($registro[$columns[$i]]);
                    // Procesa cada dato según su tipo
                    if ($tipos[$i] == "C") {
                        $aMov[] = utf8($cDato);
                    } elseif ($tipos[$i] == "N") {
                        $aMov[] = conComas($cDato);
                        if ($i===3){
                            $nMonto = (float)($cDato); 
                        }
                    } else {
                        $aMov[] = $cDato;
                    }
                }
                // Escribe la fila procesada en el archivo CSV
                $regreso["movs"] = $aMov;
                $cCta1 = $aMov[0];
                if ( $cCta1!==$cCta2){
                    if ($cCta2!==""){ // Imprimir total de la Cta Anterior
                        if ( $pdf->GetY() + 10 > $pdf->h ){
                            encabezado1($pdf,$letrero1,"",$letrero2,$aAnchos);
                            imprimeCta($pdf,$cCta2,$aAnchos);
                        }
                        imprimeTotal($pdf,$nTotal,$cCta2,$aAnchos);
                        $nTotal = 0.00;
                    }
                    $cCta2 = $cCta1;
                    if ( $pdf->GetY() + 10 > $pdf->h ){
                        encabezado1($pdf,$letrero1,"",$letrero2,$aAnchos);
                    }
                    imprimeCta($pdf,$cCta1,$aAnchos);
                }
                if ( $pdf->GetY() + 10 > $pdf->h ){
                    encabezado1($pdf,$letrero1,"",$letrero2,$aAnchos);
                    imprimeCta($pdf,$cCta1,$aAnchos);
                }
                $nTotal += $nMonto;
                imprimeMov($pdf,$aMov,$aAnchos);
                //return true;
            }
            //
            if ( $pdf->GetY() + 10 > $pdf->h ){
                encabezado1($pdf,$letrero1,"",$letrero2,$aAnchos);
                imprimeCta($pdf,$cCta1,$aAnchos);
            }
            imprimeTotal($pdf,$nTotal,$cCta1,$aAnchos);
            // Liberar memoria después de procesar cada lote
            unset($resultado); // Libera el resultado del lote
            gc_collect_cycles(); // Forzar recolección de basura
        }
        cierraPdf($pdf,$regreso);
        $regreso["mensaje"] = ""; // "Se generó el archivo pdf";
    }catch(Exception $e){
        $datos["mensaje"] = $e->getMessage();
        return false;
    }
    return true;
}
// _________________________________________________________________________________
function imprimeMov($pdf,$aMov,$aAnchos){
    $cDes = trim(substr($aMov[5],0,30)) . " - " . trim(substr($aMov[6],0,27));
    $aRen = array($aMov[4],$aMov[2],$aMov[1],$aMov[8],$aMov[9],$cDes,$aMov[7],$aMov[3]);
    $pdf->RowSinCuadro($aRen);
}
// _________________________________________________________________________________
function imprimeCta($pdf,$cCta,$aAnchos){
    $aRen  = array("CUENTA : " . $cCta); // array($cCta,"","","","","","","");
    $pdf->SetWidths(array(array_sum($aAnchos)));
    $pdf->Row($aRen);
    $pdf->SetWidths($aAnchos);
}
// _________________________________________________________________________________
function imprimeTotal($pdf,$nTotal,$cCta,$aAncho){
    $nCol1  = $aAncho[0] + $aAncho[1] + $aAncho[2] + $aAncho[3] + $aAncho[4] + $aAncho[5];
    $aRen   = array(str_pad("TOTAL CUENTA", $nCol1, " ", STR_PAD_LEFT),$cCta,conComas($nTotal));
    $pdf->SetWidths(array($nCol1,$aAncho[6],$aAncho[7]));
    $pdf->SetAligns(['R','L', 'R']);
    $pdf->Row($aRen);
    $pdf->SetWidths($aAncho);
    $pdf->SetAligns( ['L','L','L','C','C','L','R','R']); // Alineación de las columnas
}
// _________________________________________________________________________________
function creaPdf($pdf){
    ob_start();
    
    $pdf->SetAutoPageBreak(true, 1); 	            // 1 de margen inferior para el footer
    $pdf->AliasNbPages('{nb}');	                    // Define el alias para el número total de páginas
    $pdf->SetTopMargin(7);
}
// _________________________________________________________________________________
function cierraPdf($pdf,&$res){
    ob_end_clean();
    $ip 		  = ipRepo();
    $tmpArchivo = '../pdfs/' . trim($ip) ;
    //$nb   =  $pdf->PageNo(); // Obtiene el número total de páginas generadas
    $pdf->Output( $tmpArchivo , 'F');
    $pdf->Close();
    $res["mensaje"] 	= "";
    $res["success"] 	= true;

    $res["archivo"] 	= 'pdfs/' . trim($ip) ;
}
// _________________________________________________________________________________
function encabezado1($pdf,$letrero1,$nombre,$letrero2,$aAnchos){
    $margen = 2;
    $pdf->AddPage();							// Configurar el encabezado
    $pdf->SetWidths($aAnchos); 
    $pdf->SetHeights(3); 						// Reducir la altura de línea a n unidades
    $pdf->SetSpaceLine(5);						// Dependiendo este valor hay que actualizar la posición de los rectangluos
    $pdf->SetFont(Letra, 'B', 11, '' , true);	// Tambien este valor afecta la posición de los rectangluos
    $pdf->SetLeftMargin($margen);
    $x1 = $pdf->GetX();
    // ____________________________________________________________
    logoInstitucional1($pdf);
    // ___________________________________________________________
    $pdf->SetX($x1); // para que salgan bien centrados los siguientes encabezados
    // ___________________________________________________________
    $cPag = utf8("Página "). $pdf->PageNo() . " de ";// . '[ de {total Pages}]');
    $tPag = '{nb}';
    // Arreglo del Encabezado
    $aCabeza = [
    	[" "       ,utf8("DIRECCIÓN EJECUTIVA DE ADMINISTRACIÓN")  ,   "Hora  : " . date("H:i:s")	] ,
    	[" "       ,utf8("SUBDIRECCIÓN DE OPERACIÓN BANCARIA")     , 	"Fecha : " . date("d/m/Y")	],
    	[" "       ,$letrero1                                      ,   $cPag . $tPag				],
    	[$nombre   ,$letrero2	                                    , 	" "							]
    ];
    // ___________________________________________________________
	// Configurar anchos proporcionales para los títulos principales de la página
	$anchoTotal = $pdf->w; // Ancho total de la página
	$anchoPrimeraColumna = $anchoTotal * 0.45; // % del ancho total
	$anchoSegundaColumna = (int)($anchoTotal - $anchoPrimeraColumna)/2; // El resto del ancho

	$pdf->SetWidths(array($anchoSegundaColumna,$anchoPrimeraColumna, $anchoSegundaColumna-10));
    $pdf->SetAligns(['L','C', 'R']);
    foreach ($aCabeza as $row) {
        $pdf->RowSinCuadro($row);
	}
    // ____________________________________________________________
    $aCabeza = [
    	["FECHA",utf8("OPERACIÓN"),"DOCTO","OPE","CTRL","BENEFICIARIO-CONCEPTO","UR","IMPORTE"]
    ];
    $pdf->GuardaXY();
    $pdf->Ln(1);                        // agrega un punto al espaciado
    $pdf->SetWidths($aAnchos);          // Ancho de las columnas
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
 	$pdf->SetAligns( ['L','L','L','C','C','L','R','R']); // Alineación de las columnas
 	$pdf->SetFont(Letra, 'B', 7);
}
?>