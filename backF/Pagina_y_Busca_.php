<?php 
/*
* * * * * * * * * * * * * * * * * * * * * * * * * 
* Autor   : Miguel Ángel Bolaños Guillén        *
* Sistema : Sistema de Operación Bancaria Web   *
* Fecha   : Octubre 2023	                    *
* Descripción : Rutinas para ejecutar codigo    * 
*               SQL para traer registros de     *
*               una tabla paginando y buscando  *
*               Unadm-Proyecto Terminal         *
* * * * * * * * * * * * * * * * * * * * * * * * *  */

// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once("con_pg_OpeFinW1_.php"); 	// Se incluye conexión a la Base de Datos
include_once("rutinas_.php");						// Rutinas de uso general
function BuscaYPagina(&$datos){
	global $conn_pg;
	//
	// Se recupera lo que envía JS medante Ajax
	//$datos = $_POST['datos'];
	// Datos que se deben pasar 
	$cTabla  		= trim($datos["tabla"]);		// Nombre de la tabla PostgreSQL
	$cCampos 		= trim($datos["campos"]);		// campos de la tabla a mostrar en el HTML
	$join			= trim($datos["join"]);
	$id 	 		= trim($datos["id"]);			// campo o campo id para hecer un count
	$campo	 		= trim($datos["busca"]);		// Texto a Buscar
	$cTabPrin		= trim($datos["tablaPrin"]);
	$registros		= $datos["limite"];				// Registros por página
	$pagina  		= $datos["pagina"];				// Número de página
	$tipos			= explode(",",$datos["tipos"]);
	$order 			= trim($datos["order"]);
	//
	$totalRegistros = 0;
	//$totalFiltro	= 0;
	//$whereTP	= "";							// Where aplicado a la tabla principal, Para 
	//if (isset($datos["whereTP"])){
	//	$whereTP	= "where " . trim($datos["whereTP"]);		// Condición  la Base de Datos
	//}else{
	//	$whereTP = "";
	//}
	//$aCampos	= $datos["aCampos"];
	// Se protege lo que teclea el usuario para evitar inyecciones de código
	$campo 	 = pg_escape_string($conn_pg, $campo); 
	if ( isset($datos["aCampos"])){
		$aCampos = $datos["aCampos"];
		$columns = $aCampos; //explode(",", $cCampos);
	}else{
		$columns = explode(",", $cCampos);
		$aCampos = $columns;
	}

	/* Filtrado    Debe ir por lo menos un C de búsqueda para que si lo que busca no esta , marque Sin Resultados
				   Ya que el N y el D estan condicionados, el D marca error si solo se quiere buscar 2023- por eso esta condicionado
	*/
	$where = '';

	if ($campo != null && $campo!="") {
		$where = construye_where($campo, $aCampos,$tipos);
		if ($join!=""){
			if ($where==""){
				$where .= " where ( $join ) ";
			}else{
				$where .= " and ( $join ) ";
			}
		}
	}else{
		if ($join!=""){
			$where = "where ( $join )";
		}
	}

	/* Limit */
	$limit  = isset($registros) ? pg_escape_string($conn_pg, $registros) : 15;
	$pagina = isset($pagina)    ? pg_escape_string($conn_pg, $pagina)    : 0;
	if (!$pagina) {
	    $inicio = 0;
	    $pagina = 1;
	} else {
		// _____________________________________________
		if (isset($totalFiltro)){
			if ( ($pagina -1) * $limit > $totalFiltro ){ // Se quedo en la página 20 y aplico filtro
		    	$inicio = 0;
		    	$pagina = 1;
			}
		}
		// _____________________________________________
	    $inicio = ($pagina - 1) * $limit;
	}

	$sLimit = " OFFSET $inicio  LIMIT $limit ";
//	_____________________________________________________	
	/* Consulta */

	// Se requiere consultar el número de registros sin el limit

	$sql		  = "SELECT count($id) as numreg1 FROM $cTabla $where " ;
	// $datos["depura"] = $sql;
	$res 		  = ejecutaSQL_conn_pg($conn_pg,$sql);
	$totalFiltro  = $res[0]["numreg1"];
	$datos["sql"] = $sql;
	// Ahora con el limit
	$sql 		  = "SELECT $cCampos FROM $cTabla $where $order $sLimit ";
	$resultado	  = ejecutaSQL_conn_pg($conn_pg,$sql);
	//var_dump($resultado);
	if ($resultado==false || $resultado==NULL){
		$num_rows = 0;
	}else{
		$num_rows = count($resultado);
	}
	$datos["sql1"]= $sql;

	// Total de registros de la tabla principal objetivo no la de los join
	//$sql 			= "SELECT count($id) as numreg1 FROM $cTabPrin $whereTP ";
	//$res 			= ejecutaSQL_conn_pg($conn_pg,$sql);
	//$totalRegistros = $res[0]["numreg1"];
	//$datos["sql2"]  = $sql;
	$datos["where"] = $where;

/* Mostrado resultados */
	$output 					= [];
	$output['totalRegistros'] 	= $totalRegistros;
	$output['totalFiltro'] 		= $totalFiltro;
	$output['data'] 			= '';
	$output['paginacion'] 		= '';
	$columns					= $aCampos;
	if ($num_rows > 0) {
		// quita el alias de la tabla a.cheque por cheque, a.idcuenta por idcuenta
		for($i=0;$i<count($columns);$i++){
			$columns[$i] = preg_replace('/[a-z]+\./', '', $columns[$i]);
		}
		foreach ($resultado as $row) {
			$datos["depura"] = $row;
   			$output['data'] .= '<tr>';
			$j1 = 0;
   			foreach($columns as $col){
				if ($tipos[$j1]==="NF"){// Regresar numéricos con comas
					// Primero, eliminamos cualquier carácter no numérico, si es necesario
    				// $numberStr = preg_replace('/[^\d.]/', '', $numberStr);

    				// Convertimos la cadena a un número flotante
    				$number = floatval($row[$col]);
    				// Formateamos el número con comas como separadores de miles
    	 			$number = number_format($number, 2, '.', ',');
					 $output['data'] .= '<td>' .$number . '</td>';
				}else{
   					$output['data'] .= '<td>' . $row[$col] . '</td>';
				}
				$j1++;
   			}
   			$output['data'] .= '</tr>';
		}
	} else {
	    $output['data'] .= '<tr>';
	    $output['data'] .= '<td colspan="'. count($columns) .'">Sin resultados</td>';
	    $output['data'] .= '</tr>';
	}

	if ($output['totalFiltro'] > 0) { // totalRegistros
	    $totalPaginas = ceil($output['totalFiltro'] / $limit); // totalRegistros

	    $output['paginacion'] .= '<nav>';
	    $output['paginacion'] .= '<ul class="mipagination">';

	    $numeroInicio = 1;

	    if(($pagina - 4) > 1){
	        $numeroInicio = $pagina - 4;
	    }

	    $numeroFin = $numeroInicio + 9;

	    if($numeroFin > $totalPaginas){
	        $numeroFin = $totalPaginas;
	    }

	    for ($i = $numeroInicio; $i <= $numeroFin; $i++) {
	        if ($pagina == $i) {
	            $output['paginacion'] .= '<li class="mipage-item active"><a class="mipage-link" href="#">' . $i . '</a></li>';
	        } else {
	            $output['paginacion'] .= '<li class="mipage-item"><a class="mipage-link" href="#" onclick="nextPage(' . $i . ')">' . $i . '</a></li>';
	        }
	    }

	    $output['paginacion'] .= '</ul>';
	    $output['paginacion'] .= '</nav>';
	}
	$datos["regreso"] = $output; // json_encode($output, JSON_UNESCAPED_UNICODE); // $output;
	return true;
}
// _____________________________________________
// _____________________________________________
function construye_where($campo, $aCampos,$tipos){
	$where = "";
    $cont = count($aCampos); 
    for ($i = 0; $i < $cont; $i++) {
    	if($tipos[$i]=="C"){ // Caracter o String
    		$where = $where==""?"WHERE (":$where;
        	$where .= $aCampos[$i] . " LIKE '%" . $campo . "%' OR ";
        }elseif($tipos[$i]=="N" || $tipos[$i]=="NF" ){ // Busca Números
        	if ( is_numeric($campo) ){
        		$where = $where==""?"WHERE (":$where;
        		$where .= $aCampos[$i] . "::text ILIKE '%" . $campo . "%' OR "; 
        	}
        }elseif($tipos[$i]=="D"){ // Busca Fecha
        	if (strtotime($campo) !== false){
        		if(strlen($campo)==10){ // hasta que complete la fecha
        			$where = $where==""?"WHERE (":$where;
        			$where .= $aCampos[$i] . " = '" . $campo . "' OR ";
        		}
        	}
        }
    }
    if ($where!=""){
	    $where = substr_replace($where, "", -3); // quita ultimo OR
    	$where .= ")";
    }
    return $where;
}
// _____________________________________________	

?>