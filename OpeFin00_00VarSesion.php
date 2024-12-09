<?php
    header_remove('x-powered-by');
    session_start();
    //echo "** " .$_SESSION['OpeBanClave'] . " **";

    
    $vinactivo = 900; // 120; // 900;

    if(isset($_SESSION['tiempo'])){
        $vida_session = time() - $_SESSION['tiempo'];
        if($vida_session > $vinactivo){
            // header("Location OpeBan00_salir.php"); //exit;
            header("Location: OpeFin00_home.php");exit;
        }else{
            $_SESSION['tiempo'] = time();
        }
    }else{
        //header("Location OpeBan00_salir.php");//exit;
        header("Location: OpeFin00_home.php");exit;
    }

    

    if(!isset($_SESSION['OpeFinClave'])){
        header("Location OpeFin00_salir.php"); //exit;
        // header("Location: OpeBan00_home.php");exit;
    }else{
        // Se recuperan variables de sesion
        $usrClave     = $_SESSION['OpeFinClave'];
        $usrApellidos = $_SESSION['OpeFinApellidos'];
        $usrNombres   = $_SESSION['OpeFinNombres'];
        $usrCurp      = $_SESSION['OpeFinCurp'];
        $usrNombreC   = $_SESSION['OpeFinNC'];
        $usrPuesto    = $_SESSION["OpeFinPuesto"];
        $usrEsquema   = $_SESSION['OpeFinEsquema'];
        $v_TituloS    = $_SESSION['OpeFinTituloS'];
        $v_Error      = $_SESSION['OpeFinError'];
    }
?>
