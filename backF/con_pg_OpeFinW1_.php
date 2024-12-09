<?php
    global $conn_pg;
     try{
        $host       = '10.0.15.106';
        $port       = '5432';
        $dbname     = '_OpeFinW';
        $user       = 'wwwdea';
        $password   = '3wdeamz56GA$#6'; 
          //$conn_pg = pg_connect('host=berry.db.elephantsql.com port=5432 user=ubxoxaol password=vT_E_GIEdzwdXdtY5dsxrJXcIIDNv4_F dbname=ubxoxaol');
          $conn_pg = pg_connect("host=$host port=5432 user=$user password=$password dbname=$dbname");
     }catch(Exception $e){
         die("Ocurrió un error al tratar de acceder a la base de datos de Operación Bancaria");
     }
?>