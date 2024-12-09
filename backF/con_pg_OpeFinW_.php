<?php
    global $conn_pdo;
    // $conn_pg = pg_connect('host=10.0.15.106 port=5432 user=wwwdea password=3wdeamz56GA$#6 dbname=_OpenBanW');
    //$conn_pg = pg_connect('host=10.0.15.106 port=5432 user=wwwdea password=3wdeamz56GA$#6 dbname=_OpeBanW');
//   Favor de no modificar este archivo, se requieren las lineas de abajo
     //try{
     //     $conn_pdo = new PDO('pgsql:host=10.0.15.106;dbname=_OpeBanW', 'wwwdea', '3wdeamz56GA$#6');
     //}catch(Exception $e){
     //    echo "Ocurrió un error al tratar de acceder a la base de datos";
     //}

    global $conn_pdo;
    $host       = '10.0.15.106';
    $port       = '5432';
    $dbname     = '_OpeFinW';
    $user       = 'wwwdea';
    $password   = '3wdeamz56GA$#6';
    try{
        $conn_pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");
        $conn_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){
        die ("An error occurred while trying to access the database [ " . $e->getMessage() . "]" );
    } 

?>