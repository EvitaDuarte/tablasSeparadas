<?php
/**
    Clase para manejar objetos para la Cuenta Bancaria
 */
// Comentar  para producción
error_reporting(E_ALL);
ini_set('display_errors', '1');

class metodos{
//  _______________________________________________________________________________________________
public static function ExisteReferenciaBancaria($cRefBan,$cCtaBan){
    $cTabla  = nombreTabla($cCtaBan);
    $cRefBan = pg_escape_string($cRefBan);
    $cCtaBan = pg_escape_string($cCtaBan);
    $sql     = "select idmovimiento as salida from $cTabla as movimientos where " .
               "referenciabancaria='$cRefBan' and idcuentabancaria='$cCtaBan' ";
    return getCampo($sql);     
}
//  _______________________________________________________________________________________________
public static function OperaciónIngresoEgreso($cOperacion){
    $sql = "select tipo,operador from operacionesbancarias where idoperacion='$cOperacion' ";
    $res = ejecutaSQL_($sql);
    return $res;
}  
//  _______________________________________________________________________________________________
public static function CalculaSiglas($cMovOpe,$cCtrl,&$cSiglas,&$nDigitos,$cCtaBan){
    try{
        $nDigitos = 4;
        if ($cCtrl=="INV"){ // Hasta ahora el control INV solo esta asociado a la operacion TRI por eso puse su else
            $cSiglas = "INV" . substr($cSiglas, -3); // tres últimos dígitos
        }else{
            if ($cMovOpe=="AR"){
                $cSiglas = $cSiglas . "AR" . "-";
                $nDigitos = 5;
            }else{
                if ($cMovOpe=="XTR"){
                    $cSiglas = "INE" . substr(trim($cCtaBan),-4) . "-" . "XTR";
                    $nDigitos = 5;
                }else{
                    $cSiglas = $cSiglas;
                }
            }
        }
    }catch(Exception $e){
        $conError = true;
    }
}
//  _______________________________________________________________________________________________  
public static function TraeFolio($cCtaBan,$cAnio,$cSiglas,$nDigitos,&$respuesta){
    try{
        $cConse = $cSiglas . $cAnio ; // Parte fija del folio
        $cTabla = nombreTabla($cCtaBan);
        // Busco el último folio parecido para la cuentaBancaria
        $sql    = "select folio from $cTabla where idcuentabancaria='$cCtaBan' and folio like '$cConse%' order by folio desc limit 1 ";
        $respuesta["depura"] =  $sql;
        $res    = ejecutaSQL_($sql);
        $respuesta["objeto"] =  $res;
        //$respuesta["_trace"] .= json_encode($res);
        if ($res!=null){ // Si lo Encontro
            $folio = $res[0]["folio"];
            $nLen  = strlen($cConse); // Se obtiene la parte del consecutivo para incrementarlo
            //$respuesta["_trace"] .= "$folio $nLen";
            $nFol  = intval( substr($folio,$nLen,$nDigitos) ) + 1 ;
            return ( $cConse . str_pad($nFol , $nDigitos, '0', STR_PAD_LEFT) );
        }else{
            return ( $cConse . str_pad(1     , $nDigitos, '0', STR_PAD_LEFT) );
        }
    }catch(Exception $e){

        $respuesta["depura"] = $respuesta["depura"]  . "trae Folio excepción: " . $e->getMessage();
        return null;
    }
}
//  _______________________________________________________________________________________________
public static function CargaCatalogosMovimientos(&$respuesta){
    require_once "../pdoF/cuentaBancaria_.php";
    require_once "../pdoF/operaciones_.php";
    require_once "../pdoF/controles_.php";
    $oCatalogo                   = new cuentaBancaria(null,null);
    $respuesta["ctas"]           = $oCatalogo->filtraCtasBancarias($respuesta["datos"]["idUsuario"],$respuesta["datos"]["esquemaUsuario"]);
    $oCatalogo                   = new operacionBancaria(null,null);
    $respuesta["opera"]          = $oCatalogo->filtraOperaciones($respuesta["tipoMov"]);
    $oCatalogo                   = new controlBancario(null,null);
    $respuesta["ctrl"]           = $oCatalogo->filtraControles($respuesta["tipoMov"]);
    $respuesta["urs"]            = self::traeUrs();
    $respuesta["success"]        = true;
    $respuesta["opcion"]["anio"] = self::traeAnio("01"); // 01 es el Id para año mínimo captura de movs
    $respuesta["opcion"]["hoy"]  = date("Y-m-d");
}
//  _______________________________________________________________________________________________
public static function traeUrs(){
    $sql = "select idunidad, nombreunidad from unidades order by idunidad";
    $res = ejecutaSQL_($sql);
    if ( $res!==null){
        $regreso[]   = " ,Seleccione"; // Valor nulo
        foreach ($res as $r ){  // llena el combo con la clave y nombre de la operación bancaria
            $regreso[] = $r["idunidad"] . "," . $r["idunidad"];
        }
        return $regreso;
    }
    return null;
}
//  _______________________________________________________________________________________________
public static function traeAnio($cVar){
    $cAnio = trim( getcampo("select valor as salida from configuracion where idconfiguracion='$cVar'") );
    if ($cAnio=="" || $cAnio==null){
        $cAnio = date("Y");
    }
    return $cAnio;
}
//  _______________________________________________________________________________________________
public static function SaldoHoy($ctaBan,&$respuesta){
    global $conn_pdo;
    require_once "../pdoF/Saldos_.php";
    $cHoy   = date("Y-m-d");        // Fecha de Hoy del Servidor
    $oSaldo = new Saldos(null,$conn_pdo);
    $nSaldo = $oSaldo->traeSaldoAnterior($ctaBan,$cHoy,true,$respuesta);
    $respuesta["datos"]["hoy"]      = $cHoy;
    $respuesta["datos"]["saldoHoy"] = number_format($nSaldo, 2, '.', ',');
    $respuesta["success"]           = true;
}
//  _______________________________________________________________________________________________
public static function SaldoAnterior(&$res){
    global $conn_pdo;
    require_once "../pdoF/Saldos_.php";
    $oSaldo = new Saldos(null,$conn_pdo);
    $nSaldo = $oSaldo->traeSaldoAnterior($res["datos"]["cCta"],$res["datos"]["cFecha"],false,$res); // el false es para que busque del día anterior a cFecha
    $res["datos"]["saldoAnterior"] = $nSaldo;
}
//  _______________________________________________________________________________________________
public static function ReciboIngreso(&$respuesta){
    $cSiglas    = trim($respuesta["opcion"]["siglas"]);
    $cCtaBan    = trim($respuesta["opcion"]["cuenta"]);
    $cCtrl      = trim($respuesta["opcion"]["cveCtrl"]);
    $cMovOpe    = trim($respuesta["opcion"]["cveOpe"]);
    $cAnio      = trim($respuesta["opcion"]["cAnio"]);
    $nLenR      = 4;    // Posiciones para el consecutivo

    self::CalculaSiglas($cMovOpe,$cCtrl,$cSiglas,$nLenR,$cCtaBan);
    $respuesta["opcion"]["recibo"] = self::TraeFolio($cCtaBan,$cAnio,$cSiglas,$nLenR,$respuesta);
    $respuesta["success"]          = true;


}
//  _______________________________________________________________________________________________
public static function EliminaMovimiento($cId){
    //$cId  = "8888888";
    $sql  = "delete from movimientos where idmovimiento=$cId";
    $res  = actualizaSql($sql);
    return ($res>0); // true si se elimino 
}
//  _______________________________________________________________________________________________
public static function traeCuentasBancarias(&$respuesta){
    require_once "../pdoF/cuentaBancaria_.php";
    $oCatalogo                   = new cuentaBancaria(null,null);
    $respuesta["ctas"]           = $oCatalogo->filtraCtasBancarias($respuesta["datos"]["idUsuario"],$respuesta["datos"]["esquemaUsuario"]);
    $respuesta["opcion"]["hoy"]  = date("Y-m-d");
    
    if ( $respuesta["ctas"]!=null ){
        $respuesta["success"] = true;
    }else{
        $respuesta["mensaje"] = "No se han definido Cuentas Bancarias para el usuario";
    }
}
//  _______________________________________________________________________________________________
public static function traeCuentasBancariasConciliadas(&$respuesta){
    require_once "../pdoF/cuentaBancaria_.php";
    $oCatalogo                   = new cuentaBancaria(null,null);
    $respuesta["ctas"]           = $oCatalogo->filtraCtasBancariasConciliadas($respuesta["datos"]["idUsuario"],$respuesta["datos"]["esquemaUsuario"]);
    $respuesta["opcion"]["hoy"]  = date("Y-m-d");
    
    if ( $respuesta["ctas"]!=null ){
        $respuesta["success"] = true;
    }else{
        $respuesta["mensaje"] = "No se han definido Cuentas Bancarias para el usuario";
    }
}
//  _______________________________________________________________________________________________
public static function revisaReferenciaBancaria($cRefe,$cBan){
    $cTabla= nombreTabla($cBan);
    $cRefe = pg_escape_string($cRefe); $cBan= pg_escape_string($cBan);
    $sql   = "select idmovimiento, idoperacion, referenciabancaria, fechaoperacion, importeoperacion, estatus " .
             " from $cTabla as movimientos where idcuentabancaria='$cBan' and referenciabancaria='$cRefe' ";
    $aDat  = ejecutaSQL_($sql);
    return $aDat;
}
//  _______________________________________________________________________________________________
public static function revisaReferenciaBancariaImporte($cRefe,$cBan,$cImpo){
    $cTabla= nombreTabla($cBan);
    $cRefe = pg_escape_string($cRefe); $cBan= pg_escape_string($cBan); $cImpo = pg_escape_string($cImpo);
    $sql   = "select idmovimiento, idoperacion, referenciabancaria, fechaoperacion, importeoperacion, estatus " .
             " from $cTabla as movimientos where idcuentabancaria='$cBan' and referenciabancaria='$cRefe' and importeoperacion=$cImpo ";
    $aDat  = ejecutaSQL_($sql);
    return $aDat;
}
//  _______________________________________________________________________________________________
public static function traeCatalogoOperacionesConciliacion(){
    $sql  = "select idoperacion,nombre,fch_d_h from conci_cata_opera order by orden";
    $aDat = ejecutaSQL_($sql);
    if ( $aDat!==null){
        $result = array_map(
                    function($item) {
                        $w = $item['fch_d_h'] . " | " . $item['nombre'];
                        return $item['idoperacion'] . ',' . $w ;
                    }, $aDat);
        array_unshift($result, ',Seleccione');
    }else{
        $result = null;
    }
    return $result;
}
//  _______________________________________________________________________________________________
//
    /*
    "😀 😃 😄 😁 😆 😞"
    ALGORTIMO PARA CALCULAR EL CONSECUTIVO DE RECIBOS DE IN_GRESOS
nLenR = 4 posiciones para el consecutivo
Tomar siglas de la tabla cuentas bancarias
- Si el control es INV siglas debe cambiar
    siglas = INV + tres últimos dígitos siglas
- Si la operación es AR
    siglas = siglas + "AR" + "-"
    nLenR = 5 posiciones para el consecutivo
- Si la operación es XTR
    siglas = "INE"+ Right(Alltrim(cBan),4) + "-" + XTR
    nLenR = 5 posiciones para el consecutivo

Se busca en folio cSiglas+cAnio+"99999" si fueron 5 posiciones para el consecutivo
ó
Se busca en folio cSiglas+cAnio+"9999" si fueron 4 posiciones para el consecutivo
Se busca el "último folio " que coincida con cSiglas + cAnio
Si no lo encuentra
    cValor = cSiglas + cAnio + Padl("1",nLenR,"0")
Si si lo encuentra
    nLen = Len(cSiglas) + Len(cAnio)
    cNoRec = Alltrim(Substr(che_mto.fno_folio,nLen+1,nLenR))
    If Val(cNoRec) > 0
        cValor = cSiglas + cAnio + Padl( Int(Val(cNoRec))+1 , nLenR , "0" )
    else
        cValor = cSiglas + cAnio + Padl("1",nLenR,"0")

    */
/*
lparameters cBan , cSiglas, cAnio , cCtrl , cOpe
local nLen , cNoRec , nR , cOrd , cValor , nLenR
*
        nR      = Recno(this._ta_mto)
        cOrd    = Order(this._ta_mto)
        cOpe    = Iif(pcount()<=4,"",Alltrim(cOpe))
        cValor  = ''                    && Regresa el Siguiente recibo
        cSiglas = Iif(Alltrim(cCtrl)=="INV",cCtrl+Right(cSiglas,3),cSiglas)
        cSiglas = Iif( cOpe==this._v_ar , cSiglas+cOpe+"-" , cSiglas )
        If (cOpe==this._v_xtr)
            cSiglas = "INE"+ Right(Alltrim(cBan),4) + "-" + this._v_xtr
            nLenR   = 5
        Else
            nLenR   = Iif( cOpe==this._v_ar , 5 , 4 ) && Posiciones para el consecutivo AR 5 ( 2018 )   
        Endif
        
        
*
        If Empty(cSiglas) or Empty(cAnio)
            this._m_Mensaje("Faltan parámetros para asignar siguiente Recibio de In_greso"+chr(13)+chr(13)+;
                            "Banco  :"+cBan     +Chr(13)+;
                            "Siglas :"+cSiglas  +Chr(13)+;
                            "   Año :"+cAnio )
        Else
**          set order to conciliar in che_mto   && el indice conciliar no tiene filtro for es general
            Set Order to in_gresos in che_mto
*
            nLen = Len(cSiglas)+ Len(cAnio) && debe de venir de che_ban.fsiglas
*
            Set Near On
*           Busco el ultimo
*           thisform._f_._m_Mensaje( cban+padr(cSiglas+cAnio,20,"9") )
            =Seek( cBan+cSiglas+cAnio+Replicate("9",nLenR), this._ta_mto , "IN_GRESOS" )
            If Eof(this._ta_mto)        && no hay registros
                Skip -1 In che_mto      && Me regreso 1
            Endif
*
            If che_mto.fcve_banco==cBan                                     && Si encontro el banco
                If Left(che_mto.fno_folio,nLen)==cSiglas+cAnio              && Si son las mismas siglas
*   *               Messagebox(cNoRec)
                    cNoRec = Alltrim(Substr(che_mto.fno_folio,nLen+1,nLenR))
*                   this._m_pc_mabg("["+cNoRec+"]")
                    If Val(cNoRec) > 0
                        cValor = cSiglas + cAnio + Padl( Int(Val(cNoRec))+1 , nLenR , "0" )
*                       this._m_Mensaje("["+cValor+"]"+chr(13)+cSiglas+cAnio+cNoRec)
                    Else
                        cValor = cSiglas + cAnio + Padl("1",nLenR,"0")
                    Endif
                Else
                    cValor = cSiglas + cAnio + Padl("1",nLenR,"0")
                Endif 
            Else
                cValor = cSiglas + cAnio + Padl("1",nLenR,"0")
            Endif
            *
            If File("c:\tmp\debug.txt")
                MessageBox("["+cOpe+"]["+cSiglas+"]["+cValor+"]",4096)
            EndIf 
            Set Near Off
            Set Order to (cOrd) in che_mto
            If nR>0 and nR<=Reccount(this._ta_mto)
                Go nR In che_mto
            Endif
        Endif
*
Return cValor
*/
/*
    if ($cCtrl=="INV"){
        $cSiglas = "INV" . substr($cSiglas,-3);
    }
    if ($cOpe=="AR"){
        $cSiglas = $cSiglas . "AR-";
        $nLenR = 5; 
    }else{
        if ($cOpe=="XTR"){
            $cSiglas = "INE" . substr($cConse,-4) . "-XTR";
            $nLenR = 5;
        }
    }
    $sql = "select ";
    $respuesta["opcion"]["recibo"]  = $cSiglas . $cAnio;
    $respuesta["success"]           = true; */
//  _______________________________________________________________________________________________
//  _______________________________________________________________________________________________
//  _______________________________________________________________________________________________
//  _______________________________________________________________________________________________
//  _______________________________________________________________________________________________    
}
?>