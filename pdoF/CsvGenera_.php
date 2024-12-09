<?php
/*
* * * * * * * * * * * * * * * * * * * * * * * * * 
* Autor   : Miguel Ángel Bolaños Guillén        *
* Sistema : Sistema de Operación Bancaria Web   *
* Fecha   : Octubre 2023	                    *
* Descripción : Rutinas para ejecutar codigo    * 
*               SQL para traer registros de     *
*               una tabla paginando y buscando  *
* * * * * * * * * * * * * * * * * * * * * * * * *  */

// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once("../backF/con_pg_OpeFinW1_.php"); 	// Se incluye conexión a la Base de Datos
include_once("../backF/rutinas_.php");			// Rutinas de uso general

function EnviaCsv(&$regreso){
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
        $filename 	         = "../csv/" . $datos["csv"];   // "../csv/buscaMovs.csv";
        $regreso["archivo"]  = 'csv/' . $datos["csv"];
        $where               = '';


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

       // Se requiere consultar el número de registros por si hay que traer por pedazos la información
        $sql		    = "SELECT count($id) as numreg1 FROM $cTabla $where " ;
        $res1 		    = ejecutaSQL_conn_pg($conn_pg,$sql);
        $totalFiltro    = (int)$res1[0]["numreg1"];
        $regreso["sql"] = $sql;

        if ($totalFiltro === 0) {
            $regreso["mensaje"] = "No existe información, con los datos solicitados";
            return false; // No hay información
        }
        
        $file	= fopen($filename, 'w');
        // quita el alias de la tabla a.cheque por cheque, a.idcuenta por idcuenta
        for($i=0;$i<count($columns);$i++){
            $columns[$i] = preg_replace('/[a-z]+\./', '', $columns[$i]);
        }
        fputcsv($file, $columns); // Imprime los encabezados del archivo CSV
        // Parámetros para la paginación
        $loteTamano = 100000; // Número de registros por lote, este dato se obtuvo al hacer pruebas a partir de la cual
                              // no aparece el error Allowed memory size of 536870912 bytes exhausted
        $totalLotes = ceil($totalFiltro / $loteTamano);

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

            // Procesa y escribe los datos del lote en el archivo
            foreach ($resultado as $registro) {
                $aMov = []; // Arreglo para almacenar los valores procesados
                for ($i = 0; $i < count($registro); $i++) {
                    $cDato = $registro[$columns[$i]];
                    // Procesa cada dato según su tipo
                    if ($tipos[$i] == "C") {
                        $aMov[] = utf8($cDato);
                    } elseif ($tipos[$i] == "N") {
                        $aMov[] = conComas($cDato);
                    } else {
                        $aMov[] = $cDato;
                    }
                }
                // Escribe la fila procesada en el archivo CSV
                fputcsv($file, $aMov);
            }
            // Liberar memoria después de procesar cada lote
            unset($resultado); // Libera el resultado del lote
            gc_collect_cycles(); // Forzar recolección de basura
        }

        fclose($file);
        $regreso["mensaje"] = ""; // "Se generó el archivo $filename";
/*
        // Se traen ahora si el detalle de los registros 
        $sql 		  = "SELECT $cCampos FROM $cTabla $where $order ";
        $resultado	  = ejecutaSQL_conn_pg($conn_pg,$sql);
        
        $datos["regreso"] = $resultado;
        //var_dump($resultado);
        if ($resultado==false || $resultado==NULL){
            $num_rows = 0;
        }else{
            $num_rows = count($resultado);
        }
        $datos["sql1"]= $sql;


        // Itero sobre cada uno de los registros 
        foreach($resultado as $registro){
            $aMov = []; // Arreglo para almacenar el valor de cada campo
            for ($i=0;$i<count($registro);$i++){
                $cDato = $registro[$columns[$i]];
                // Si $tipo[$i]==="C"  es un caracter
                if ($tipos[$i]=="C"){
                    $aMov[] = utf8($cDato);
                }else if ($tipos[$i]=="N"){
                    $aMov[] = conComas($cDato);
                }else{
                    $aMov[] = $cDato;
                }
            }

            // Imprime la línea con los datos procesados
            fputcsv($file, $aMov);

        }
        fclose($file); */
    }catch(Exception $e){
		$datos["mensaje"] = $e->getMessage();
		return false;
	}
    return true;
}
/*
        // Aquí hay que ver si se tiene que iterar, por si es mucah información y no la puede traer de un solo golpe
        // Ahora con los campos
        $sql 		    = "SELECT $cCampos FROM $cTabla $where $order limit 1";
        $resultado	    = pg_query($conn_pg,$sql); // Se requiere el recordset y no el arreglo que regresa ejecutaSQL_
        $tamanio        = calculaTamanio($resultado);
        $datos["borra"] = pg_fetch_assoc($resultado);
        $datos["Bytes"] = $tamanio;
        $datos["registros"] = $totalFiltro;

        return true;
*/
            /*
            foreach ($registro as $i => $valor) {  // Iteramos con el índice $i para acceder a $tipos
                // Validamos el tipo de dato correspondiente
                if ($tipos[$i] == "C") {
                    $aMov[] = utf8($valor); // Para campos de tipo carácter
                } else if ($tipos[$i] == "N") {
                    $aMov[] = conComas($valor); // Para campos numéricos
                } else {
                    $aMov[] = $valor; // Para otros tipos de campo
                }
            }*/
?>