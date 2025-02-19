<?php

// *************************************************************************************
// INTERFAZ BASICA DE MYSQLI
// *************************************************************************************
//
function conexionMysqliApi($fuente = '')
{
    if (substr($fuente, 0, 2) != 'P-') {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        if (!defined('LOG_SQL')) {
            define('LOG_SQL', false);
        }
        if (!defined('DEBUG_DB')) {
            define('DEBUG_DB', false);
        }
        if (!defined('DB_HOST_REPLICA')) {
            define('DB_HOST_REPLICA', '');
        }
        $logsql = LOG_SQL;
        $dbms = DBMS;
        $dbhost = DB_HOST;
        $dbport = DB_PORT;
        $dbusuario = DB_USUARIO;
        $dbpassword = DB_PASSWORD;
        $dbname = DB_NAME;
        $debugdb = DEBUG_DB;
        if ($fuente == 'replicabatch' && trim(DB_HOST_REPLICA) != '') {
            $logsql = LOG_SQL_REPLICA;
            $dbms = DBMS_REPLICA;
            $dbhost = DB_HOST_REPLICA;
            $dbport = DB_PORT_REPLICA;
            $dbusuario = DB_USUARIO_REPLICA;
            $dbpassword = DB_PASSWORD_REPLICA;
            $dbname = DB_NAME_REPLICA;
            $debugdb = DEBUG_DB_REPLICA;
        }
    }
    if (substr($fuente, 0, 2) == 'P-') {
        $cam = substr($fuente, 2);
        $logsql = true;
        $dbms = 'mysqli';
        // $dbhost = 'srv-sii-bd-aurora-0-cluster.cluster-ro-cghnnivk3tvt.us-east-1.rds.amazonaws.com';
        //  $dbhost = '172.16.5.164';
        $dbhost = 'srv-sii-bd-aurora-0-cluster.cluster-cghnnivk3tvt.us-east-1.rds.amazonaws.com';
        $dbport = '3306';
        $dbusuario = 'rootsiiaurora';
        $dbpassword = '8nI0d:UtL+Fs';
        $debugdb = false;
        switch ($cam) {
            case "01":
                $dbname = 'sii_armenia';
                $dbusuario = 'aurora_sii_1';
                $dbpassword = '0pa6OMVddH1r..1';
                break;

            case "02":
                $dbname = 'sii_barranca';
                $dbusuario = 'aurora_sii_2';
                $dbpassword = '0pa5G5fmQQOtk22';
                break;

            case "06":
                $dbname = 'sii_buenaventura';
                $dbusuario = 'aurora_sii_6';
                $dbpassword = '0paslg3L4J4ZYY6';
                break;

            case "07":
                $dbname = 'sii_buga';
                $dbusuario = 'aurora_sii_7';
                $dbpassword = '0pa8MelXxwx/9c7';
                break;

            case "10":
                $dbname = 'sii_cartago';
                $dbusuario = 'aurora_sii_10';
                $dbpassword = '1papnE05lebDuA0';
                break;

            case "11":
                $dbname = 'sii_11';
                $dbusuario = 'aurora_sii_11';
                $dbpassword = 'cnCnt4pr11';
                break;

            case "12":
                $dbname = 'sii_chinchina';
                $dbusuario = 'aurora_sii_12';
                $dbpassword = '1pa0keevuSJb062';
                break;

            case "13":
                $dbname = 'sii_duitama';
                $dbusuario = 'aurora_sii_13';
                $dbpassword = '1paEXMBHPwyRLk3';
                break;

            case "14":
                $dbname = 'sii_girardot';
                $dbusuario = 'aurora_sii_14';
                $dbpassword = '1pavJu5bpZXcHU4';
                break;

            case "15":
                $dbname = 'sii_honda';
                $dbusuario = 'aurora_sii_15';
                $dbpassword = '1paQDoqpYnqBks5';
                break;

            case "16":
                $dbname = 'sii_ibague';
                $dbusuario = 'aurora_sii_16';
                $dbpassword = '1paGEzudBfZC5c6';
                break;

            case "17":
                $dbname = 'sii_ipiales';
                $dbusuario = 'aurora_sii_17';
                $dbpassword = '1paxwekIyJeR6A7';
                break;

            case "18":
                $dbname = 'sii_dorada';
                $dbusuario = 'aurora_sii_18';
                $dbpassword = '1patPzlXCw43qc8';
                break;

            case "19":
                $dbname = 'sii_magangue';
                $dbusuario = 'aurora_sii_19';
                $dbpassword = '1pastfhgsfY6UU9';
                break;

            case "20":
                $dbname = 'sii_manizales';
                $dbusuario = 'aurora_sii_20';
                $dbpassword = '2payGq/OCjNnn60';
                break;

            case "22":
                $dbname = 'sii_monteria';
                $dbusuario = 'aurora_sii_22';
                $dbpassword = '2paQNrt3k.51dc2';
                break;

            case "23":
                $dbname = 'sii_23';
                $dbusuario = 'aurora_sii_23';
                $dbpassword = '2paC2b/G2kqEL23';
                break;

            case "24":
                $dbname = 'sii_palmira';
                $dbusuario = 'aurora_sii_24';
                $dbpassword = '2paf257HAjqGkM4';
                break;

            case "25":
                $dbname = 'sii_pamplona';
                $dbusuario = 'aurora_sii_25';
                $dbpassword = '2pastH1m7RIN/25';
                break;

            case "26":
                $dbname = 'sii_pasto';
                $dbusuario = 'aurora_sii_26';
                $dbpassword = '2pa2w4B1FoCHMI6';
                break;

            case "27":
                $dbname = 'sii_pereira';
                $dbusuario = 'aurora_sii_27';
                $dbpassword = '2pa7UISFgw0E/o7';
                break;

            case "28":
                $dbname = 'sii_cauca';
                $dbusuario = 'aurora_sii_28';
                $dbpassword = '2pa.nGeOl8BQzk8';
                break;

            case "30":
                $dbname = 'sii_guajira';
                $dbusuario = 'aurora_sii_30';
                $dbpassword = '3panYYQnzVUOsM0';
                break;

            case "31":
                $dbname = 'sii_sanandres';
                $dbusuario = 'aurora_sii_31';
                $dbpassword = '3pa5Ddhr31FwnM1';
                break;

            case "32":
                $dbname = 'sii_santamarta';
                $dbusuario = 'aurora_sii_32';
                $dbpassword = 'paCdZhfBjShuc32';
                break;

            case "33":
                $dbname = 'sii_santarosa';
                $dbusuario = 'aurora_sii_33';
                $dbpassword = '3pa/FYAoq5vx6c3';
                break;

            case "34":
                $dbname = 'sii_sincelejo';
                $dbusuario = 'aurora_sii_34';
                $dbpassword = '3pah.6QYe2R7zM4';
                break;

            case "35":
                $dbname = 'sii_sogamoso';
                $dbusuario = 'aurora_sii_35';
                $dbpassword = '3pakF8/PXzmMm.5';
                break;

            case "36":
                $dbname = 'sii_tulua';
                $dbusuario = 'aurora_sii_36';
                $dbpassword = '3pa5eT1HoG9bFA6';
                break;

            case "37":
                $dbname = 'sii_tumaco';
                $dbusuario = 'aurora_sii_37';
                $dbpassword = '3padp/6aYO3VeA7';
                break;

            case "38":
                $dbname = 'sii_38';
                $dbusuario = 'aurora_sii_38';
                $dbpassword = 'TnUj4pr38';
                break;

            case "39":
                $dbname = 'sii_valledupar';
                $dbusuario = 'aurora_sii_39';
                $dbpassword = '3palJyfIJtGVr.9';
                break;

            case "40":
                $dbname = 'sii_40';
                $dbusuario = 'aurora_sii_40';
                $dbpassword = '4paAd3ZevpYn760';
                break;

            case "41":
                $dbname = 'sii_florencia';
                $dbusuario = 'aurora_sii_41';
                $dbpassword = '4pafCnSqhUQI/A1';
                break;

            case "42":
                $dbname = 'sii_amazonas';
                $dbusuario = 'aurora_sii_42';
                $dbpassword = '4paIIriA91yI4M2';
                break;

            case "43":
                $dbname = 'sii_sevilla';
                $dbusuario = 'aurora_sii_43';
                $dbpassword = '4paE5rKqceOJe63';
                break;

            case "44":
                $dbname = 'sii_uraba';
                $dbusuario = 'aurora_sii_44';
                $dbpassword = '4pa8JMlcQbnUW64';
                break;

            case "45":
                $dbname = 'sii_espinal';
                $dbusuario = 'aurora_sii_45';
                $dbpassword = '4pactkbgWvUnEA5';
                break;

            case "46":
                $dbname = 'sii_ptoasis';
                $dbusuario = 'aurora_sii_46';
                $dbpassword = '4paQMjx1zLUsjc6';
                break;

            case "47":
                $dbname = 'sii_facatativa';
                $dbusuario = 'aurora_sii_47';
                $dbpassword = '4pa2BQPpu3l.4o7';
                break;

            case "48":
                $dbname = 'sii_arauca';
                $dbusuario = 'aurora_sii_48';
                $dbpassword = '4pabMhYq92BTnU8';
                break;

            case "49":
                $dbname = 'sii_ocana';
                $dbusuario = 'aurora_sii_49';
                $dbpassword = '4paiWzpPdq5iTg9';
                break;

            case "50":
                $dbname = 'sii_casanare';
                $dbusuario = 'aurora_sii_50';
                $dbpassword = '5paWemup2H1qCk0';
                break;

            case "51":
                $dbname = 'sii_orienteantioqueno';
                $dbusuario = 'aurora_sii_51';
                $dbpassword = '5paORYizuDiwMI1';
                break;

            case "52":
                $dbname = 'sii_mmedio';
                $dbusuario = 'aurora_sii_52';
                $dbpassword = '5paCK2un9fS.N.2';
                break;

            case "53":
                $dbname = 'sii_aguachica';
                $dbusuario = 'aurora_sii_53';
                $dbpassword = '5pa4muwMs6ii7w3';
                break;

            case "54":
                $dbname = 'sii_dosquebradas';
                $dbusuario = 'aurora_sii_54';
                $dbpassword = '5paKL6EA3qB14Y4';
                break;

            case "55":
                $dbname = 'sii_aburra';
                $dbusuario = 'aurora_sii_55';
                $dbpassword = '5pamwStQ6Si2';
                break;

            case "56":
                $dbname = 'sii_saravena';
                $dbusuario = 'aurora_sii_56';
                $dbpassword = '5paRSFvg0MPKgc6';
                break;

            case "57":
                $dbname = 'sii_sanjose';
                $dbusuario = 'aurora_sii_57';
                $dbpassword = '5paz88kDzkRNdY7';
                break;
        }
    }

    //
    $mysqli = new mysqli($dbhost, $dbusuario, $dbpassword, $dbname, $dbport);
    if (mysqli_connect_error()) {
        $_SESSION["generales"]["txtemergente"] = "Error en la conexion a la base de datos";
        return false;
    }
    return $mysqli;
}

// *************************************************************************************
// RUTINAS COMPLEMENTARIAS ADAPTADAS PARA MYSQLI
// *************************************************************************************
function actualizarLogMysqliApi($dbx, $idaccion, $idusuario, $objeto, $idtipodoc, $idsede, $idnumdoc, $detalle, $matricula = '', $proponente = '', $identificacion = '', $numeroliq = 0, $codbarras = 0, $anexo = 0)
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    //
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = 'localhost';
        }
    }

    if ($numeroliq == '') {
        $numeroliq = 0;
    }

    $arrCampos = array(
        'fecha',
        'hora',
        'idaccion',
        'idusuario',
        'emailusuario',
        'identificacionusuario',
        'objeto',
        'idtipodctal',
        'idsede',
        'numero',
        'numeroliquidacion',
        'ip',
        'matricula',
        'proponente',
        'identificacion',
        'detalle'
    );

    if (!isset($_SESSION["generales"]["emailusuariocontrol"])) {
        $_SESSION["generales"]["emailusuariocontrol"] = '';
    }
    if (!isset($_SESSION["generales"]["identificacionusuariocontrol"])) {
        $_SESSION["generales"]["identificacionusuariocontrol"] = '';
    }


    $arrValores = array(
        "'" . date("Ymd") . "'",
        "'" . date("H:i:s") . "'",
        "'" . $idaccion . "'",
        "'" . $idusuario . "'",
        "'" . addslashes($_SESSION["generales"]["emailusuariocontrol"]) . "'",
        "'" . $_SESSION["generales"]["identificacionusuariocontrol"] . "'",
        "'" . $objeto . "'",
        "'" . $idtipodoc . "'",
        "'" . $idsede . "'",
        "'" . $idnumdoc . "'",
        $numeroliq,
        "'" . $ip . "'",
        "'" . $matricula . "'",
        "'" . $proponente . "'",
        "'" . $identificacion . "'",
        "'" . addslashes(utf8_decode($detalle)) . "'"
    );
    $res = insertarRegistrosMysqliApi($dbx, 'log_' . date("Y"), $arrCampos, $arrValores);
    if ($res === false) {
        return false;
    }
    if ($cerrarMysqli === 'si') {
        $dbx->close();
    }
    return $res;
}

function actualizarLogDocumentosMysqliApi($dbx, $periodo, $idtipodoc, $numdoc, $fecha, $hora, $accion, $usuario, $obs = '')
{
    $arrCampos = array(
        'ano',
        'idtipodoc',
        'numdoc',
        'fecha',
        'hora',
        'control',
        'idusuario',
        'observaciones'
    );
    $arrValores = array(
        "'" . $periodo . "'",
        "'" . $idtipodoc . "'",
        "'" . $numdoc . "'",
        "'" . $fecha . "'",
        "'" . $hora . "'",
        "'" . $accion . "'",
        "'" . $usuario . "'",
        "'" . addslashes($obs) . "'"
    );
    insertarRegistrosMysqliApi($dbx, 'documentos_controles', $arrCampos, $arrValores);
}

// ************************************************************************************************* //
// Borrar registros Sii
// ************************************************************************************************* //
function borrarRegistrosMysqliApi($dbx, $tabla, $condicion)
{
    require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
    $cerrarMysql = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
        $cerrarMysql = 'si';
    }

    $query = "delete from " . $tabla . " where " . $condicion;
    $result = mysqli_query($dbx, $query);
    if ($result === false) {
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx));
        $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
        if ($cerrarMysql == 'si') {
            $dbx->close();
        }
        return false;
    } else {
        $_SESSION["generales"]["mensajeerror"] = '';
        if ($cerrarMysql == 'si') {
            $dbx->close();
        }
        return true;
    }
}

function clonarTablaVaciaMysqliApi($dbx, $base, $tabla)
{
    $res = mysqli_query($dbx, "CREATE TABLE `" . $tabla . "` LIKE `" . $base . "`");
    if ($res === false) {
        return false;
    }
    return true;
}

// ************************************************************************************************* //
// Buscar saldo del afiliado
// ************************************************************************************************* //
function buscarSaldoAfiliadoMysqliApi($dbx, $matricula, $gruposervicios = '', $formacalculo = '')
{
    $cerrarMysql = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqli();
        $cerrarMysql = 'si';
    }

    if ($formacalculo == '') {
        $formaCalculoAfiliacion = retornarClaveValorMysqliApi($dbx, '90.01.60');
    } else {
        $formaCalculoAfiliacion = $formacalculo;
    }

    $salida = array(
        'valorultpagoafi' => 0,
        'fechaultpagoafi' => '',
        'ulanorenafi' => '',
        'pago' => 0,
        'cupo' => 0
    );

    //
    if ($gruposervicios == '') {
        $arrSerAfil = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "grupoventas='02'", '', '*');
        $Servicios = "";
        $ServiciosAfiliacion = array();
        foreach ($arrSerAfil as $ServAfil) {
            if ($Servicios != '') {
                $Servicios .= ",";
            }
            $Servicios .= "'" . $ServAfil["idservicio"] . "'";
            $ServiciosAfiliacion[] = $ServAfil["idservicio"];
        }
    } else {
        $Servicios = $gruposervicios;
    }
    $arrFecValAfi = retornarRegistroMysqliApi($dbx, 'mreg_est_recibos', "matricula='" . $matricula . "' and servicio in (" . $Servicios . ") and ctranulacion = '0' and (substring(numerorecibo,1,1)='R' or substring(numerorecibo,1,1)='S') order by fecoperacion desc limit 1");
    if ($arrFecValAfi && !empty($arrFecValAfi)) {
        $salida["ultanorenafi"] = substr($arrFecValAfi["fecoperacion"], 0, 4);
        $salida["valorultpagoafi"] = $arrFecValAfi["valor"];
        $salida["fechaultpagoafi"] = $arrFecValAfi["fecoperacion"];
    }
    unset($arrFecValAfi);

    $feciniafi = date("Y") . '0101';
    $arrRecs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $matricula . "') and ctranulacion = '0' and left(numerorecibo,1) IN ('H','G','R','S') and fecoperacion >= '" . $feciniafi . "'", "fecoperacion");
    if ($arrRecs && !empty($arrRecs)) {
        foreach ($arrRecs as $rx) {
            if (in_array($rx["servicio"], $ServiciosAfiliacion)) {
                $salida["pago"] = $salida["pago"] + $rx["valor"];
                if ($formaCalculoAfiliacion != '') {
                    if ($formaCalculoAfiliacion == 'RANGO_VAL_AFI') {
                        $arrRan = retornarRegistrosMysqliApi($dbx, 'mreg_rangos_cupo_afiliacion', "ano='" . date("Y") . "'", "orden");
                        foreach ($arrRan as $rx1) {
                            if ($rx1["minimo"] <= $salida["pago"] && $rx1["maximo"] >= $salida["pago"]) {
                                $salida["cupo"] = $rx1["cupo"];
                            }
                        }
                        unset($arrRan);
                        unset($rx1);
                    } else {
                        $salida["cupo"] = round(doubleval($formaCalculoAfiliacion) * $salida["pago"], 0);
                    }
                }
            }
            if ($salida["cupo"] > 0) {
                if (substr($rx["servicio"], 0, 4) == '0101') {
                    if ($rx["tipogasto"] == '1') {
                        if ($salida["cupo"] - $rx["valor"] >= 0) {
                            $salida["cupo"] = $salida["cupo"] - $rx["valor"];
                        } else {
                            $salida["cupo"] = 0;
                        }
                    }
                }
            }
        }
    }

    if ($cerrarMysql == 'si') {
        $dbx->close();
    }

    return $salida;
}

// ************************************************************************************************* //
// Contar registros Sii 
// ************************************************************************************************* //
function contarRegistrosMysqliApi($dbx, $tabla, $condicion)
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }
    $result = mysqli_query($dbx, "select count(*) as contador from " . $tabla . " where " . $condicion);
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
        return false;
    }
    $row = mysqli_fetch_assoc($result);
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }
    return $row["contador"];
}

// ************************************************************************************************* //
// Construir noticia parta sur occidentew
// ************************************************************************************************* //
function construirNoticiaSurOccidenteMysqliApi($mysqli, $ins)
{
    $salida = '';

    //
    if ($ins["actosistemaanterior"] == '') {
        return $salida;
    }

    $cerrarMysqli = 'no';
    if ($mysqli === null) {
        $mysqli = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    // Ciudad del cambio de domicilio 
    // tipo de dato 74
    if (trim($ins["actosistemaanterior"]) == '15|47|891') {
        $nlib = '';
        $treg = '';
        if (substr($ins["libro"], 0, 2) == 'RM') {
            $nlib = substr($ins["libro"], 2, 2);
            $treg = '1';
        }
        if (substr($ins["libro"], 0, 2) == 'RE') {
            $nlib = substr($ins["libro"], 3, 1);
            $treg = '2';
        }

        $nins = $ins["registro"];
        $salida = 'CAMBIO DE DOMICILIO DESDE LA CIUDAD DE ' . retornarRegistroMysqliApi($mysqli, 'rp_datos_actos', "cod_tipo_registro='" . $treg . "' and cod_libro='" . $nlib . "' and num_inscripcion='" . $nins . "' and cod_tipo_dato='74'", "descripcion");
    }

    //
    if ($cerrarMysqli == 'si') {
        $mysqli->close();
    }

    //
    return $salida;
}

function encontrarHistoricoPagosMysqliApi($dbx = null, $mat = '', $serviciosRenovacion = array(), $serviciosAfiliacion = array(), $serviciosMatricula = array())
{
    require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';

    $nameLog = 'encontrarHistoricoRenovacion_' . date("Ymd");
    \logApi::general2($nameLog, '', '');

    $salida = array();
    $salida["fecultren"] = '';
    $salida["ultanoren"] = '';
    $salida["actultren"] = '';
    $salida["pagultren"] = '';
    $salida["anorenaflia"] = '';
    $salida["fecrenaflia"] = '';
    $salida["valpagaflia"] = 0;

    $salida["renovacionanos"] = array();
    $salida["renovacionsinaplicaranos"] = array();
    $salida["afiliacionanos"] = array();

    //
    if ($mat == '') {
        return $salida;
    }

    //
    $cerrarMysqli = 'no';
    if ($dbx == null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    //
    $fcorte = retornarRegistroMysqliApi($dbx, "mreg_cortes_renovacion", "ano='" . date("Y") . "'", "corte");

    //
    $exp = retornarRegistroMysqliApi($dbx, "mreg_est_inscritos", "matricula='" . $mat . "'");
    if ($exp && !empty($exp)) {
        if ($exp["fecrenant"] != '') {
            $salida["fecultren"] = $exp["fecrenant"];
            $salida["ultanoren"] = $exp["ultanorenant"];
        } else {
            $salida["fecultren"] = $exp["fecrenovacion"];
            $salida["ultanoren"] = $exp["ultanoren"];
        }
    }

    // 
    if (!isset($serviciosRenovacion) || empty($serviciosRenovacion) || !isset($serviciosMatricula) || empty($serviciosMatricula)) {
        $serviciosRenovacion = array();
        $serviciosMatricula = array();
        $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
        foreach ($temx1 as $x1) {
            if ($x1["tipoingreso"] == '03' || $x1["tipoingreso"] == '13') {
                $serviciosRenovacion[$x1["idservicio"]] = $x1["idservicio"];
            }
            if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
            }
        }
    }

    //
    $tServiciosRenovacion = '';
    foreach ($serviciosRenovacion as $s) {
        if ($tServiciosRenovacion != '') {
            $tServiciosRenovacion .= ",";
        }
        $tServiciosRenovacion .= "'" . $s . "'";
    }

    //
    if (!isset($serviciosAfiliacion) || empty($serviciosAfiliacion)) {
        $serviciosAfiliacion = array();
        $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "1=1", "idservicio");
        foreach ($temx1 as $x1) {
            if ($x1["grupoventas"] == '02') {
                $serviciosAfiliacion[$x1["idservicio"]] = $x1["idservicio"];
            }
        }
    }

    //
    $tServiciosAfiliacion = '';
    foreach ($serviciosAfiliacion as $s) {
        if ($tServiciosAfiliacion != '') {
            $tServiciosAfiliacion .= ",";
        }
        $tServiciosAfiliacion .= "'" . $s . "'";
    }

    // ********************************************************************************************************************** //
    // Encuentra ultimo año renovado y fecha de renovación
    // ********************************************************************************************************************** //
    // $estadosCbNoAsentadoLocal = array('00', '01', '02', '03', '04', '05', '06', '07', '17', '19','39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
    // $estadosCbNoAsentadoRues = array('00', '01', '02', '03', '04', '05', '06', '07', '17', '19', '23', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
    $estadosCbNoAsentadoLocal = array("00", "05", "06", "09", "10", "07", "17", "19", "39", "40", "41", "42", "43", "50", "51", "52", "53", "99");
    $estadosCbNoAsentadoRues = array("01", "05", "06", "07", "09", "10", "17", "19", "23", "39", "40", "41", "42", "43", "50", "51", "52", "53", "99");
    $estadosCbRadicadosLocal = array("01", "02", "03", "04", "09", "10");
    $recs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosRenovacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,horaoperacion,anorenovacion");
    if ($recs && !empty($recs)) {
        $anosren = array();
        foreach ($recs as $r) {
            \logApi::general2($nameLog, $mat, 'Recibo No.: ' . $r["numerorecibo"] . ' - ' . $r["fecoperacion"] . ' - ' . $r["horaoperacion"] . ' - ' . $r["ctranulacion"] . ' - ' . $r["servicio"] . ' - ' . $r["anorenovacion"]);
            if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R' || substr($r["numerorecibo"], 0, 1) == 'Z') {
                $cba = retornarRegistroMysqliApi($dbx, 'mreg_est_codigosbarras', "recibo='" . $r["numerorecibo"] . "'");
                $inc = 'no';
                $ai = 'no';
                if ($cba === false || empty($cba)) {
                    \logApi::general2($nameLog, $mat, 'Recibo No.: ' . $r["numerorecibo"] . ' - No localizo codigo de barras');
                    $inc = 'si';
                } else {
                    \logApi::general2($nameLog, $mat, 'Recibo No.: ' . $r["numerorecibo"] . ' - ' . $cba["codigobarras"] . ' - Estado final ' . $cba["estadofinal"]);
                    if ($r["tipogasto"] == '0' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoLocal)) {
                        if (in_array($cba["estadofinal"], $estadosCbRadicadosLocal)) {
                            $liq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "numerorecibo='" . $r["numerorecibo"] . "'");
                            if ($liq && !empty($liq) && $liq["controlactividadaltoimpacto"] == 'S') {
                                $inc = 'si';
                                $ai = 'si';
                            }
                        } else {
                            $inc = 'si';
                        }
                    } else {
                        if ($r["tipogasto"] == '8' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoRues)) {
                            $inc = 'si';
                        }
                    }
                    \logApi::general2($nameLog, $mat, 'Recibo No.: ' . $r["numerorecibo"] . ' - ' . $cba["codigobarras"] . ' - Estado final ' . $cba["estadofinal"] . ' - ' . $inc);
                }
                if ($inc == 'si') {
                    if (!isset($anosren[$r["anorenovacion"]]) || $anosren[$r["anorenovacion"]] == 1) {
                        if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                            $salida["fecultren"] = $r["fecharenovacionaplicable"];
                        } else {
                            $salida["fecultren"] = $r["fecoperacion"];
                        }
                        $salida["ultanoren"] = $r["anorenovacion"];
                        $salida["actultren"] = $r["activos"];
                        $salida["pagultren"] = $r["valor"];
                        if (isset($serviciosMatricula[$r["servicio"]])) {
                            $anosren[$r["anorenovacion"]] = 1;
                        } else {
                            $anosren[$r["anorenovacion"]] = 2;
                        }
                    } else {
                        $salida["actultren"] = $r["activos"];
                        $salida["pagultren"] = $r["valor"];
                    }
                    $ind = $r["anorenovacion"] . '-' . $r["fecoperacion"] . '-' . $r["horaoperacion"];
                    $salida["renovacionanos"][$ind] = array();
                    $salida["renovacionanos"][$ind]["recibo"] = $r["numerorecibo"];
                    $salida["renovacionanos"][$ind]["fecharecibo"] = $r["fecoperacion"];
                    $salida["renovacionanos"][$ind]["ano"] = $r["anorenovacion"];
                    if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                        $salida["renovacionanos"][$ind]["fecrenovacion"] = $r["fecharenovacionaplicable"];
                    } else {
                        $salida["renovacionanos"][$ind]["fecrenovacion"] = $r["fecoperacion"];
                    }
                    $salida["renovacionanos"][$ind]["activos"] = $r["activos"];
                    $salida["renovacionanos"][$ind]["valor"] = $r["valor"];
                    $salida["renovacionanos"][$ind]["ai"] = $ai;
                } else {
                    $ind = $r["anorenovacion"] . '-' . $r["fecoperacion"] . '-' . $r["horaoperacion"];
                    $salida["renovacionsinaplicaranos"][$ind] = array();
                    $salida["renovacionsinaplicaranos"][$ind]["recibo"] = $r["numerorecibo"];
                    $salida["renovacionsinaplicaranos"][$ind]["fecharecibo"] = $r["fecoperacion"];
                    $salida["renovacionsinaplicaranos"][$ind]["ano"] = $r["anorenovacion"];
                    if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                        $salida["renovacionsinaplicaranos"][$ind]["fecrenovacion"] = $r["fecharenovacionaplicable"];
                    } else {
                        $salida["renovacionsinaplicaranos"][$ind]["fecrenovacion"] = $r["fecoperacion"];
                    }
                    $salida["renovacionsinaplicaranos"][$ind]["activos"] = $r["activos"];
                    $salida["renovacionsinaplicaranos"][$ind]["valor"] = $r["valor"];
                }
            }
        }
    }
    unset($recs);
    unset($anosren);

    // 2020-01-23: JINT
    if ($salida["fecultren"] == '') {
        $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'", "matricula,fecmatricula,fecrenovacion,ultanoren,fecrenant,camant,ultanorenant");
        if ($exp["camant"] != '') {
            $salida["fecultren"] = $exp["fecrenant"];
            $salida["ultanoren"] = $exp["ultanorenant"];
        } else {
            if ($exp["fecrenovacion"] == '') {
                $salida["fecultren"] = $exp["fecrenovacion"];
                $salida["ultanoren"] = $exp["ultanoren"];
            } else {
                $salida["fecultren"] = $exp["fecmatricula"];
                $salida["ultanoren"] = substr($exp["fecmatricula"], 0, 4);
            }
        }
    }

    //
    if ($salida["fecultren"] != '') {
        if ($exp["fecmatricula"] >= $salida["fecultren"]) {
            if ($exp["camant"] != '') {
                if ($salida["fecultren"] <= $exp["fecrenant"]) {
                    $salida["fecultren"] = $exp["fecrenant"];
                    $salida["ultanoren"] = $exp["ultanorenant"];
                }
            } else {
                $salida["fecultren"] = $exp["fecmatricula"];
                $salida["ultanoren"] = substr($exp["fecmatricula"], 0, 4);
            }
        }
    }

    // ********************************************************************************************************************** //
    // Encuentra ultimo pago de afiliación y la fecha del mismo
    // ********************************************************************************************************************** //
    $recsafi = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosAfiliacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,horaoperacion,anorenovacion");
    if ($recsafi && !empty($recsafi)) {
        foreach ($recsafi as $r) {
            if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R') {
                $ind = $r["anorenovacion"] . '-' . $r["fecoperacion"];
                $salida["afiliacionanos"][$ind] = array();
                $salida["afiliacionanos"][$ind]["ano"] = $r["anorenovacion"];
                if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                    $salida["afiliacionanos"][$ind]["fecpago"] = $r["fecharenovacionaplicable"];
                } else {
                    $salida["afiliacionanos"][$ind]["fecpago"] = $r["fecoperacion"];
                }
                $salida["afiliacionanos"][$ind]["activos"] = $r["activos"];
                $salida["afiliacionanos"][$ind]["valor"] = $r["valor"];
                $salida["afiliacionanos"][$ind]["recibo"] = $r["numerorecibo"];
                $salida["afiliacionanos"][$ind]["tipo"] = $r["servicio"];

                if (substr($salida["afiliacionanos"][$ind]["fecpago"], 0, 4) != $salida["anorenaflia"]) {
                    $salida["anorenaflia"] = substr($salida["afiliacionanos"][$ind]["fecpago"], 0, 4);
                    $salida["fecrenaflia"] = $salida["afiliacionanos"][$ind]["fecpago"];
                    $salida["valpagaflia"] = $r["valor"];
                } else {
                    $salida["valpagaflia"] = $salida["valpagaflia"] + $r["valor"];
                }
            }
        }
    }
    unset($recsafi);

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $salida;
}

function encontrarHistoricoPagosAfiliacionMysqliApi($dbx = null, $mat = '', $serviciosAfiliacion = array())
{
    require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
    $nameLog = 'encontrarHistoricoRenovacionAfiliacion_' . date("Ymd");
    \logApi::general2($nameLog, '', '');
    $salida = array();
    $salida["fecrenaflia"] = '';
    $salida["anorenaflia"] = '';
    if ($mat == '') {
        return $salida;
    }
    $cerrarMysqli = 'no';
    if ($dbx == null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }
    $fcorte = retornarRegistroMysqliApi($dbx, "mreg_cortes_renovacion", "ano='" . date("Y") . "'", "corte");
    $exp = retornarRegistroMysqliApi($dbx, "mreg_est_inscritos", "matricula='" . $mat . "'");
    if (!isset($serviciosAfiliacion) || empty($serviciosAfiliacion)) {
        $serviciosAfiliacion = array();
        $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "1=1", "idservicio");
        foreach ($temx1 as $x1) {
            if ($x1["grupoventas"] == '02') {
                $serviciosAfiliacion[$x1["idservicio"]] = $x1["idservicio"];
            }
        }
    }
    $tServiciosAfiliacion = '';
    foreach ($serviciosAfiliacion as $s) {
        if ($tServiciosAfiliacion != '') {
            $tServiciosAfiliacion .= ",";
        }
        $tServiciosAfiliacion .= "'" . $s . "'";
    }

    // ********************************************************************************************************************** //
    // Encuentra ultimo año renovado y fecha de renovación
    // ********************************************************************************************************************** //
    $estadosCbNoAsentadoLocal = array("00", "05", "06", "09", "10", "07", "17", "19", "39", "40", "41", "42", "43", "50", "51", "52", "53", "99");
    $estadosCbNoAsentadoRues = array("01", "05", "06", "07", "09", "10", "17", "19", "23", "39", "40", "41", "42", "43", "50", "51", "52", "53", "99");
    $estadosCbRadicadosLocal = array("01", "02", "03", "04", "09", "10");
    $recs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosAfiliacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,horaoperacion,anorenovacion");
    if ($recs && !empty($recs)) {
        $anosren = array();
        foreach ($recs as $r) {
            \logApi::general2($nameLog, $mat, 'Recibo No.: ' . $r["numerorecibo"] . ' - ' . $r["fecoperacion"] . ' - ' . $r["horaoperacion"] . ' - ' . $r["ctranulacion"] . ' - ' . $r["servicio"] . ' - ' . $r["anorenovacion"]);
            if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R' || substr($r["numerorecibo"], 0, 1) == 'Z') {
                $cba = retornarRegistroMysqliApi($dbx, 'mreg_est_codigosbarras', "recibo='" . $r["numerorecibo"] . "'");
                $inc = 'no';
                $ai = 'no';
                if ($cba === false || empty($cba)) {
                    \logApi::general2($nameLog, $mat, 'Recibo No.: ' . $r["numerorecibo"] . ' - No localizo codigo de barras');
                    $inc = 'si';
                } else {
                    \logApi::general2($nameLog, $mat, 'Recibo No.: ' . $r["numerorecibo"] . ' - ' . $cba["codigobarras"] . ' - Estado final ' . $cba["estadofinal"]);
                    if ($r["tipogasto"] == '0' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoLocal)) {
                        if (in_array($cba["estadofinal"], $estadosCbRadicadosLocal)) {
                            $liq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "numerorecibo='" . $r["numerorecibo"] . "'");
                            if ($liq && !empty($liq) && $liq["controlactividadaltoimpacto"] == 'S') {
                                $inc = 'si';
                                $ai = 'si';
                            }
                        } else {
                            $inc = 'si';
                        }
                    } else {
                        if ($r["tipogasto"] == '8' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoRues)) {
                            $inc = 'si';
                        }
                    }
                    \logApi::general2($nameLog, $mat, 'Recibo No.: ' . $r["numerorecibo"] . ' - ' . $cba["codigobarras"] . ' - Estado final ' . $cba["estadofinal"] . ' - ' . $inc);
                }
                if ($inc === 'si') {
                    if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                        $salida["fecrenaflia"] = $r["fecharenovacionaplicable"];
                    } else {
                        $salida["fecrenaflia"] = $r["fecoperacion"];
                    }
                    $salida["anorenaflia"] = substr($salida["fecrenaflia"], 0, 4);
                }
            }
        }
    }
    unset($recs);
    unset($anosren);
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }
    return $salida;
}

function actualizarDatosRenovacionMysqliApi($dbx = null, $mat = '', $serviciosRenovacion = array(), $serviciosAfiliacion = array(), $serviciosMatricula = array())
{
    $datren = encontrarHistoricoPagosMysqliApi($dbx, $mat, $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
    $datrenafil = encontrarHistoricoPagosAfiliacionMysqliApi($dbx, $mat, $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
    if ($datren && $datren["fecultren"] != '') {
        $arrCampos = array(
            'fecharenovacion',
            'ultanoren'
        );
        $arrValores = array(
            "'" . $datren["fecultren"] . "'",
            "'" . $datren["ultanoren"] . "'"
        );
        regrabarRegistrosMysqliApi($dbx, 'mreg_inscritos', $arrCampos, $arrValores, "matricula='" . $mat . "'");
    }
    if ($datrenafil && $datrenafil["fecrenaflia"] != '') {
        $arrCampos = array(
            'fecrenaflia',
            'anorenaflia'
        );
        $arrValores = array(
            "'" . $datrenafil["fecrenaflia"] . "'",
            "'" . $datrenafil["anorenaflia"] . "'"
        );
        regrabarRegistrosMysqliApi($dbx, 'mreg_inscritos', $arrCampos, $arrValores, "matricula='" . $mat . "'");
    }
    return true;
}

function encontrarHistoricoPagosMysqliApiErroneo($mysqli = null, $mat = '', $serviciosRenovacion = array(), $serviciosAfiliacion = array())
{

    $salida = array();
    $salida["fecultren"] = '';
    $salida["ultanoren"] = '';
    $salida["actultren"] = '';
    $salida["pagultren"] = '';
    $salida["renovacionanos"] = array();
    $salida["afiliacionanos"] = array();

    //
    if ($mat == '') {
        return $salida;
    }

    $cerrarMysqli = 'no';
    if ($mysqli == null) {
        $mysqli = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    //
    if (!isset($serviciosRenovacion) || empty($serviciosRenovacion)) {
        $serviciosRenovacion = array();
        $serviciosMatricula = array();
        $temx1 = retornarRegistrosMysqliApi($mysqli, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
        foreach ($temx1 as $x1) {
            $serviciosRenovacion[] = $x1["idservicio"];
            if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
            }
        }
    }

    //
    $tServiciosRenovacion = '';
    foreach ($serviciosRenovacion as $s) {
        if ($tServiciosRenovacion != '') {
            $tServiciosRenovacion .= ",";
        }
        $tServiciosRenovacion .= "'" . $s . "'";
    }

    //
    if (!isset($serviciosAfiliacion) || empty($serviciosAfiliacion)) {
        $serviciosAfiliacion = array();
        $temx1 = retornarRegistrosMysqliApi($mysqli, "mreg_servicios", "1=1", "idservicio");
        foreach ($temx1 as $x1) {
            if ($x1["grupoventas"] == '02') {
                $serviciosAfiliacion[] = $x1["idservicio"];
            }
        }
    }

    //
    $tServiciosAfiliacion = '';
    foreach ($serviciosAfiliacion as $s) {
        if ($tServiciosAfiliacion != '') {
            $tServiciosAfiliacion .= ",";
        }
        $tServiciosAfiliacion .= "'" . $s . "'";
    }

    //
    $estadosCbNoAsentadoLocal = array('00', '01', '02', '03', '05', '06', '07', '17', '19', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
    $estadosCbNoAsentadoRues = array('00', '01', '02', '03', '04', '05', '06', '07', '17', '19', '23', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
    $recs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosRenovacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,anorenovacion");
    if ($recs && !empty($recs)) {
        $anosren = array();
        foreach ($recs as $r) {
            if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R') {
                $cba = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "recibo='" . $r["numerorecibo"] . "'");
                $inc = 'no';
                if ($cba === false || empty($cba)) {
                    $inc = 'si';
                } else {
                    if ($cba["verificacionsoportes"] != 'SI') {
                        $inc = 'si';
                    } else {
                        if (($r["tipogasto"] == '0' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoLocal)) ||
                            ($r["tipogasto"] == '8' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoRues))
                        ) {
                            $inc = 'si';
                        }
                    }
                }
                if ($inc == 'si') {
                    if (!isset($anosren[$r["anorenovacion"]]) || $anosren[$r["anorenovacion"]] == 1) {
                        if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                            $salida["fecultren"] = $r["fecharenovacionaplicable"];
                        } else {
                            $salida["fecultren"] = $r["fecoperacion"];
                        }
                        $salida["ultanoren"] = $r["anorenovacion"];
                        $salida["actultren"] = $r["activos"];
                        $salida["pagultren"] = $r["valor"];
                        if (isset($serviciosMatricula[$r["servicio"]])) {
                            $anosren[$r["anorenovacion"]] = 1;
                        } else {
                            $anosren[$r["anorenovacion"]] = 2;
                        }
                    }
                    $ind = $r["anorenovacion"] . '-' . $r["fecoperacion"];
                    $salida["renovacionanos"][$ind] = array();
                    $salida["renovacionanos"][$ind]["ano"] = $r["anorenovacion"];
                    if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                        $salida["renovacionanos"][$ind]["fecrenovacion"] = $r["fecharenovacionaplicable"];
                    } else {
                        $salida["renovacionanos"][$ind]["fecrenovacion"] = $r["fecoperacion"];
                    }
                    $salida["renovacionanos"][$ind]["activos"] = $r["activos"];
                    $salida["renovacionanos"][$ind]["valor"] = $r["valor"];
                }
            }
        }
    }
    unset($recs);
    unset($anosren);

    //
    $recs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosAfiliacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,horaoperacion,anorenovacion");
    if ($recs && !empty($recs)) {
        foreach ($recs as $r) {
            if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R') {
                $ind = $r["anorenovacion"] . '-' . $r["fecoperacion"];
                $salida["afiliacionanos"][$ind] = array();
                $salida["afiliacionanos"][$ind]["ano"] = $r["anorenovacion"];
                if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                    $salida["afiliacionanos"][$ind]["fecpago"] = $r["fecharenovacionaplicable"];
                } else {
                    $salida["afiliacionanos"][$ind]["fecpago"] = $r["fecoperacion"];
                }
                $salida["afiliacionanos"][$ind]["activos"] = $r["activos"];
                $salida["afiliacionanos"][$ind]["valor"] = $r["valor"];
                $salida["afiliacionanos"][$ind]["recibo"] = $r["numerorecibo"];
                $salida["afiliacionanos"][$ind]["tipo"] = $r["servicio"];
            }
        }
    }
    unset($recs);

    // 2019-08-26: JINT
    if ($salida["fecultren"] != '') {
        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat . "'", "matricula,fecmatricula,fecrenovacion,fecrenant,camant,ultanorenant");
        if ($exp["fecmatricula"] > $salida["fecultren"]) {
            if ($exp["camant"] != '') {
                $salida["fecultren"] = $exp["fecrenant"];
                $salida["ultanoren"] = $exp["ultanorenant"];
            } else {
                $salida["fecultren"] = $exp["fecmatricula"];
                $salida["ultanoren"] = substr($exp["fecmatricula"], 0, 4);
            }
        }
    }

    //
    if ($cerrarMysqli == 'si') {
        $mysqli->close();
    }

    //
    return $salida;
}

function ejecutarQueryMysqliApi($dbx, $query)
{

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    //
    $dbx->set_charset("utf8");
    $result = $dbx->query($query);
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = $dbx->error;
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    }
    $_SESSION["generales"]["mensajeerror"] = '';
    if ($result->num_rows > 0) {
        $res = array();
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $i++;
            $res[$i] = $row;
        }
        $result->free();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return $res;
    } else {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return array();
    }
}

// *************************************************************************
// Valida que una tabla exista en la BD
// *************************************************************************
function existeTablaMysqliApi($mysqli, $tabla)
{
    // $mysqli = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
    $result = mysqli_query($mysqli, "select count(*) as count from information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND table_name = '" . $tabla . "'");
    $obj = $result->fetch_object();
    $cant = $obj->count;
    // mysqli_free_result($result);
    // mysqli_close($mysqli);
    if ($cant == 0) {
        return false;
    } else {
        return true;
    }
}

// ************************************************************************************************* //
// Rutina maestra para insercion de registros
// ************************************************************************************************* //
function insertarRegistrosMysqliApi($dbx, $tabla, $arrCampos, $arrValores)
{
    require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    $i = 0;
    $query = "insert into " . $tabla . " (";
    foreach ($arrCampos as $c) {
        $i++;
        if ($i != 1) {
            $query .= ",";
        }
        $query .= $c;
    }
    $query .= ") VALUES (";
    $i = 0;
    foreach ($arrValores as $v) {
        $i++;
        if ($i != 1) {
            $query .= ",";
        }
        $query .= ($v);
    }
    $query .= ")";
    $dbx->set_charset("utf8");
    $result = mysqli_query($dbx, $query);
    if ($result === false) {
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx));
        $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } else {
        $_SESSION["generales"]["lastId"] = 0;
        $_SESSION["generales"]["lastId"] = mysqli_insert_id($dbx);
        $_SESSION["generales"]["mensajeerror"] = '';
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return true;
    }
}

function insertarCamposHistoricosMysqliApi($dbx, $user, $mat, $cmp, $ori, $nue, $tt, $rec, $cb, $ip)
{
    require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
    $arrCampos = array(
        'matricula',
        'campo',
        'fecha',
        'hora',
        'codigobarras',
        'datoanterior',
        'datonuevo',
        'usuario',
        'ip',
        'tipotramite',
        'recibo'
    );
    $arrValores = array(
        "'" . $mat . "'",
        "'" . $cmp . "'",
        "'" . date("Ymd") . "'",
        "'" . date("His") . "'",
        "'" . $cb . "'",
        "'" . addslashes($ori) . "'",
        "'" . addslashes($nue) . "'",
        "'" . $user . "'",
        "'" . $ip . "'",
        "'" . $tt . "'",
        "'" . $rec . "'"
    );
    insertarRegistrosMysqli($dbx, 'mreg_campos_historicos_' . date("Y"), $arrCampos, $arrValores);
}

// *****************************************************************
// Localiza el dato anterior existente en un campo
// Valida en los últimos 5 años
// *****************************************************************
function localizarCampoAnteriorMysqliApi($dbx, $mat, $campo)
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    $salida = '';
    $ano = date("Y");
    $anoinicial = date("Y");
    while ($ano > $anoinicial - 5) {
        // if (existeTablaMysqliApi('mreg_campos_historicos_' . $ano)) {
        if ($salida == '') {
            $temx = retornarRegistrosMysqliApi($dbx, 'mreg_campos_historicos_' . $ano, "matricula='" . $mat . "' and campo='" . $campo . "'", "fecha desc, hora desc");
            if ($temx && !empty($temx)) {
                foreach ($temx as $tx) {
                    if (!isset($tx["inactivarsipref"]) || strtolower($tx["inactivarsipref"]) != 'si') {
                        if (trim($tx["datoanterior"]) != '') {
                            $salida = $tx["datoanterior"];
                        }
                    }
                }
            }
        }
        if ($salida != '') {
            $ano = 0;
        }
        // }
        if ($ano != 0) {
            $ano = $ano - 1;
        }
    }
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }
    return $salida;
}

/**
 * 
 * @param mysqli $dbx
 * @param type $mat
 * @param type $campo
 * @return type
 */
function localizarCampoAnteriorTodosMysqliApi($dbx, $mat, $campo)
{

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    //
    $salida = array();
    $ano = date("Y");
    $anoinicial = date("Y");
    while ($ano > $anoinicial - 5) {
        // if (existeTablaMysqliApi('mreg_campos_historicos_' . $ano)) {
        $temx = retornarRegistrosMysqliApi($dbx, 'mreg_campos_historicos_' . $ano, "matricula='" . $mat . "' and campo='" . $campo . "'", "fecha desc, hora desc");
        if ($temx && !empty($temx)) {
            foreach ($temx as $tx) {
                if (!isset($tx["inactivadosipref"]) || strtolower($tx["inactivadosipref"]) != 'si') {
                    if (trim($tx["datoanterior"]) != '') {
                        $salida[] = $tx["datoanterior"];
                    }
                }
            }
        }
        // }
        if ($ano != 0) {
            $ano = $ano - 1;
        }
    }

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $salida;
}

function retornarCargoUsuarioMysqliApi($dbx, $usua = '')
{
    $idcargo = retornarRegistroMysqliApi($dbx, 'usuarios', "idusuario='" . $usua . "'", "idcargo");
    if ($idcargo == '') {
        return "";
    }
    return retornarRegistroMysqliApi($dbx, 'cargos', "idcargo='" . $idcargo . "'", "nombre");
}

// *****************************************************************
// Retorna una clave valor
// *****************************************************************
function retornarClaveValorMysqliApi($dbx, $clave)
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    // if (!isset($_SESSION["generales"]["clavevalor"][$clave])) {
    $_SESSION["generales"]["clavevalor"][$clave] = '';
    $query = "select * from bas_claves_valor, claves_valor ";
    $query .= "where bas_claves_valor.idorden='" . $clave . "' and ";
    $query .= "claves_valor.id=bas_claves_valor.id";
    $result = $dbx->query($query);
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = $dbx->error;
        return false;
    }
    $_SESSION["generales"]["mensajeerror"] = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $_SESSION["generales"]["clavevalor"][$clave] = $row["valor"];
        }
    }
    $result->free();
    // }
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }
    return $_SESSION["generales"]["clavevalor"][$clave];
}

function retornarConsecutivoTipoDocMysqliApi($mysqli, $periodo, $tipodoc, $estado = '', $fecha = '', $crear = 'N', $modulo = '', $identificacion = '', $nombre = '')
{
    $arrTipoDoc = retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "idtipodoc='" . $tipodoc . "'");
    if (($arrTipoDoc === false) || (empty($arrTipoDoc))) {
        return false;
    }

    if ($arrTipoDoc["numeracion"] == null) {
        $arrTipoDoc["numeracion"] = '1';
    }
    if ($arrTipoDoc["numeracion"] == '') {
        $arrTipoDoc["numeracion"] = '1';
    }

    // asigna la raíz a buscar en secuencias dependiendo si el documento maneja consecutivo &uacute;nico, anual o mensual
    switch ($arrTipoDoc["numeracion"]) {
        case "1":
            $busq = $tipodoc;
            $raiz = '';
            $tam = 0;
            break;
        case "2":
            $busq = $tipodoc . '-' . $periodo;
            $raiz = $periodo;
            $tam = 4;
            break;
        case "3":
            if (trim($fecha) == '') {
                return false;
            }
            $busq = $tipodoc . '-' . $periodo . '-' . substr($fecha, 4, 2);
            $raiz = $periodo . substr($fecha, 4, 2);
            $tam = 6;
            break;
    }
    if (trim($busq) == '') {
        return false;
    }

    // Buscar la secuencia que sigue en la tabla de documentos, de acuerdo con el tipo de documento y su numeracion
    $arrCon = retornarRegistroMysqliApi($mysqli, 'secuencias', "tipo='" . $busq . "'");
    if ($arrCon === false) {
        return false;
    }
    if (empty($arrCon)) {
        $arrCampos = array(
            'tipo',
            'consecutivo'
        );
        $arrValues = array(
            "'" . $busq . "'",
            0
        );
        insertarRegistrosMysqliApi($mysqli, 'secuencias', $arrCampos, $arrValues);
        $sec = 0;
    } else {
        if ($arrCon === false) {
            $_SESSION["generales"]["txtemergente"] = 'No fue posible recuperar el consecutivo del tipo de documento seleccionado';
            return false;
        } else {
            $sec = $arrCon["consecutivo"];
        }
    }

    // Si se desea adicionar el documento a la tabla de documentos contables.
    if ($crear == 'S') {
        $grabado = 'no';
        while ($grabado == 'no') {
            $sec++;
            switch ($tam) {
                case 0:
                    $num = $sec;
                    break;
                case 4:
                    $num = $raiz . sprintf("%05s", $sec);
                    break;
                case 6:
                    $num = $raiz . sprintf("%03s", $sec);
                    break;
            }
            if (contarRegistrosMysqliApi($mysqli, 'documentos', "ano='" . $periodo . "' and idtipodoc='" . $tipodoc . "' and numdoc='" . $num . "'") == 0) {
                $grabado = 'si';
            }
        }
    } else {
        $sec++;
        switch ($tam) {
            case 0:
                $num = $sec;
                break;
            case 4:
                $num = $raiz . sprintf("%05s", $sec);
                break;
            case 6:
                $num = $raiz . sprintf("%03s", $sec);
                break;
        }
    }

    // Actualizar secuencias
    $arrCampos = array(
        'consecutivo'
    );
    $arrValues = array(
        $sec
    );
    regrabarRegistrosMysqliApi($mysqli, 'secuencias', $arrCampos, $arrValues, "tipo='" . $busq . "'");

    //
    if ($crear == 'S') {
        $arrCampos = array(
            'ano',
            'idtipodoc',
            'numdoc',
            'fechadoc',
            'idestado',
            'idmodulo',
            'identificacion',
            'nombre'
        );
        $arrValues = array(
            "'" . $periodo . "'",
            "'" . $tipodoc . "'",
            "'" . $num . "'",
            "'" . $fecha . "'",
            "'" . $estado . "'",
            "'" . $modulo . "'",
            "'" . $identificacion . "'",
            "'" . addslashes($nombre) . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'documentos', $arrCampos, $arrValues);
    }

    // Actualizar tabla controles de documentos (log de documentos)
    actualizarLogDocumentosMysqliApi($mysqli, $periodo, $tipodoc, $num, date("Ymd"), date("His"), 'creacion', $_SESSION["generales"]["codigousuario"]);

    // Actualiza el log
    actualizarLogMysqliApi($mysqli, '002', $_SESSION["generales"]["codigousuario"], '', $tipodoc, '', $num, 'Documento creado');
    return $num;
}

// ************************************************************************************************* //
// Rutina maestra para insercion de registros
// ************************************************************************************************* //
function insertarRegistrosBloqueMysqliApi($dbx, $tabla, $arrCampos, $arrValores)
{
    require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    //
    $i = 0;
    $query = "insert into " . $tabla . " (";
    foreach ($arrCampos as $c) {
        $i++;
        if ($i != 1) {
            $query .= ",";
        }
        $query .= $c;
    }

    //
    $query .= ") VALUES ";

    //
    $i = 0;
    foreach ($arrValores as $val) {
        $i++;
        if ($i != 1) {
            $query .= ", ";
        }
        $i1 = 0;
        $query .= "(";
        foreach ($val as $v) {
            $i1++;
            if ($i1 != 1) {
                $query .= ",";
            }
            $query .= ($v);
        }
        $query .= ")";
    }
    $query .= ";";

    //
    $dbx->set_charset("utf8");
    $result = mysqli_query($dbx, $query);
    if ($result === false) {
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query);
        $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    }

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }
    return true;
}

// ************************************************************************************************* //
// Rutina maestra para regrabación de registros
// ************************************************************************************************* //
function regrabarRegistrosMysqliApi($dbx, $tabla, $arrCampos, $arrValores, $condicion)
{
    require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    $i = 0;
    $query = "update " . $tabla . " set ";
    foreach ($arrCampos as $c) {
        $i++;
        if ($i != 1) {
            $query .= ",";
        }
        $query .= $c . '=' . ($arrValores[$i - 1]);
    }
    $query .= " where " . $condicion;
    $dbx->set_charset("utf8");
    $result = mysqli_query($dbx, $query);
    if ($result === false) {
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query);
        $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } else {
        $_SESSION["generales"]["mensajeerror"] = '';
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return true;
    }
}

/**
 *
 * @param type $dbx
 * @param type $tabla
 * @param type $condicion
 * @param type $orden
 * @param type $campos
 * @param type $offset
 * @param type $limit
 * @return mixed
 */
function retornarRegistrosMysqliApi($dbx, $tabla, $condicion, $orden = '', $campos = '*', $offset = 0, $limit = 0)
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    $uncampo = 'no';
    if ($campos != '*') {
        if (strpos($campos, ',') === false) {
            $uncampo = 'si';
        }
    }

    $ordenx = '';
    if ($orden != '') {
        $ordenx = " order by " . $orden;
    }
    $limitx = '';
    if ($limit != 0 && $offset != 0) {
        $limitx = " limit " . $offset . "," . $limit;
    }
    if ($limit != '' && $limit != 0 && ($offset == '' || $offset == 0)) {
        $limitx = " limit " . $limit;
    }


    $dbx->set_charset("utf8");
    if ($campos == '*') {
        $query = "SELECT * from " . $tabla . " where " . $condicion . $ordenx . $limitx;
        $result = $dbx->query($query);
    } else {
        $query = "SELECT " . $campos . " from " . $tabla . " where " . $condicion . $ordenx . $limitx;
        $result = $dbx->query($query);
    }
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = $dbx->error;
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    }
    $_SESSION["generales"]["mensajeerror"] = '';
    if ($result->num_rows > 0) {
        $res = array();
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $i++;
            $res[$i] = array();
            foreach ($row as $key => $valor) {
                if (!is_numeric($key)) {
                    $res[$i][$key] = ($valor);
                }
            }
        }
        $result->free();
        if ($uncampo == 'no') {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return $res;
        } else {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return $res[$campos];
        }
    } else {
        if ($uncampo == 'no') {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return array();
        } else {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return "";
        }
    }
}

// ************************************************************************************************* //
// Retornar Registros
// ************************************************************************************************* //
/**
 *
 * @param type $dbx
 * @param string $tabla
 * @param string $condicion
 * @param string $campos
 * @param string $tip
 * @return boolean|string
 */
function retornarRegistroMysqliApi($dbx, $tabla, $condicion, $campos = '*', $tip = 'P')
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    $uncampo = 'no';
    if ($campos != '*' && strpos($campos, ',') === false) {
        $uncampo = 'si';
    }
    $dbx->set_charset("utf8");
    if ($campos == '*') {
        $query = "SELECT * from " . $tabla . " where " . $condicion;
        $result = $dbx->query($query);
    } else {
        $query = "SELECT " . $campos . " from " . $tabla . " where " . $condicion;
        $result = $dbx->query($query);
    }
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = $dbx->error;
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    }
    $_SESSION["generales"]["mensajeerror"] = '';
    if ($result->num_rows > 0) {
        $res = array();
        $icon = 0;
        while ($row = $result->fetch_assoc()) {
            $icon++;
            if ($tip == 'P') {
                if ($icon == 1) {
                    $res = array();
                    foreach ($row as $key => $valor) {
                        if (!is_numeric($key)) {
                            $res[$key] = ($valor);
                        }
                    }
                }
            } else {
                $res = array();
                foreach ($row as $key => $valor) {
                    if (!is_numeric($key)) {
                        $res[$key] = ($valor);
                    }
                }
            }
        }
        $result->free();
        if ($uncampo == 'no') {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return $res;
        } else {
            if (!isset($res[$campos])) {
                if ($cerrarMysqli == 'si') {
                    $dbx->close();
                }
                return "";
            } else {
                if ($cerrarMysqli == 'si') {
                    $dbx->close();
                }
                return $res[$campos];
            }
        }
    } else {
        if ($uncampo == 'no') {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return array();
        } else {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return "";
        }
    }
}

function retornarTablaBasicaPorCodigoMysqliApi($dbx, $tab, $id)
{
    $reg = retornarRegistroMysqliApi($dbx, $tab, "id='" . $id . "'");
    if ($reg === false || empty($reg)) {
        return "";
    }
    if (isset($reg["descripcion"])) {
        return $reg["descripcion"];
    }
    if (isset($reg["nombre"])) {
        return $reg["nombre"];
    }
}

function retornarTipoRegistroMysqliApi($dbx, $tra)
{
    return retornarRegistroMysqliApi($dbx, 'bas_tipotramites', "where id='" . $tra . "'", "tiporegistro");
}

function retornarDescripcionCiiuMysqliApi($dbx, $id, $tipociiu = '')
{
    if ($tipociiu == '3.1') {
        return retornarRegistroMysqliApi($dbx, "bas_ciius_3_1", "idciiu='" . $id . "'", "descripcion");
    } else {
        return retornarRegistroMysqliApi($dbx, "bas_ciius", "idciiu='" . $id . "'", "descripcion");
    }
}

function retornarListaOpcionesMysqliApi($dbx)
{
    $excluir = '';
    if ($_SESSION["generales"]["tipomenu"] != 'MOVIL') {
        $excluir = " and substring(idopcion,1,5) <> '00.00' ";
    }

    $arreglo = array();
    $query = '';
    if ((trim($_SESSION["generales"]["codigousuario"]) == '') || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX' || $_SESSION["generales"]["tipousuario"] == '00') {
        $query = "tipousuariopublico='X' and estado='1' and mostrarmenuaplicacion = 'S' " . $excluir;
    } else {
        if (substr($_SESSION["generales"]["tipousuario"], 0, 2) == '01') {
            $query = "'1=1'  " . $excluir;
        } else {
            if (substr($_SESSION["generales"]["tipousuario"], 0, 2) == '02') {
                $query = "tipousuarioadministrativo='X' and estado='1' and mostrarmenuaplicacion = 'S'  " . $excluir;
            } else {
                if (substr($_SESSION["generales"]["tipousuario"], 0, 2) == '03') {
                    $query = "tipousuarioproduccion='X' and estado='1'  and mostrarmenuaplicacion = 'S'  " . $excluir;
                } else {
                    if (substr($_SESSION["generales"]["tipousuario"], 0, 2) == '04') {
                        $query = "tipousuarioventas='X' and estado='1' and mostrarmenuaplicacion = 'S'  " . $excluir;
                    } else {
                        if (substr($_SESSION["generales"]["tipousuario"], 0, 2) == '05') {
                            $query = "tipousuarioregistro='X' and estado='1' and mostrarmenuaplicacion = 'S'  " . $excluir;
                        } else {
                            if (substr($_SESSION["generales"]["tipousuario"], 0, 2) == '06') {
                                $query = "tipousuarioexterno='X' and estado='1' and mostrarmenuaplicacion = 'S'  " . $excluir;
                            }
                        }
                    }
                }
            }
        }
    }

    if ($query == '') {
        $arreglo = false;
    } else {

        $result = retornarRegistrosMysqliApi($dbx, 'bas_opciones', $query, 'idopcion');
        $i = -1;
        if ($result) {
            foreach ($result as $res) {
                if ($res["estado"] == '1') {
                    $i++;
                    $arreglo[$i]["idopcion"] = $res["idopcion"];
                    $arreglo[$i]["nombre"] = str_replace("<br>", " ", $res["nombre"]);
                    $arreglo[$i]["tipo"] = $res["idtipoopcion"];
                    $arreglo[$i]["icono"] = $res["icono"];
                    $arreglo[$i]["tooltip"] = $res["tooltip"];
                    $arreglo[$i]["script"] = $res["script"];
                    $arreglo[$i]["mostrarbootstrap"] = '';
                    $arreglo[$i]["clasephp"] = '';
                    $arreglo[$i]["metodophp"] = '';
                    $arreglo[$i]["parametrosphp"] = '';
                    if (isset($res["mostrarbootstrap"])) {
                        $arreglo[$i]["mostrarbootstrap"] = $res["mostrarbootstrap"];
                    }
                    if (isset($res["clasephp"])) {
                        $arreglo[$i]["clasephp"] = $res["clasephp"];
                    }
                    if (isset($res["metodophp"])) {
                        $arreglo[$i]["metodophp"] = $res["metodophp"];
                    }
                    if (isset($res["parametrosphp"])) {
                        $arreglo[$i]["parametrosphp"] = $res["parametrosphp"];
                    }
                    $arreglo[$i]["enlace"] = $res["enlace"];
                    $arreglo[$i]["destino"] = $res["destino"];
                    $arreglo[$i]["estado"] = $res["estado"];
                    $arreglo[$i]["tipoempresa"] = $res["tipoempresa"];
                    $arreglo[$i]["cantidad"] = 0;
                    $arreglo[$i]["ejecutar"] = 0;
                }
            }
        } else {
            $arreglo = false;
        }
    }
    return $arreglo;
}

function retornarNombreDptoMysqliApi($dbx, $id)
{
    return retornarRegistroMysqliApi($dbx, "bas_municipios", "codigomunicipio='" . $id . "'", "departamento");
}

function retornarNombreIdentificacionMysqliApi($dbx, $ide)
{
    if (ltrim($ide, "0") == '') {
        return "";
    }
    return retornarRegistroMysqliApi($dbx, "identificaciones", "identificacion='" . $ide . "'", "razonsocial");
}

// 2020 01 22 - JINT - Se descomentarea la línea del utf8
function retornarNombreMunicipioMysqliApi($dbx = null, $id = '', $forma = 'M')
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    $dbx->set_charset("utf8");
    $result = $dbx->query("SELECT * from bas_municipios where codigomunicipio='" . $id . "'");
    if ($result === false) {
        return "";
    }
    if ($result->num_rows > 0) {
        $nombre = '';
        while ($row = $result->fetch_assoc()) {
            if ($forma == 'M') {
                $nombre = $row["ciudad"];
            }
            if ($forma == 'm') {
                $nombre = $row["ciudadminusculas"];
            }
        }
        $result->free();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return $nombre;
    } else {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
}

function retornarNombrePaisMysqliApi($dbx, $id)
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    // $dbx->set_charset("utf8");
    $result = $dbx->query("SELECT * from bas_paises where idpais='" . $id . "'");
    if ($result === false) {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
    if ($result->num_rows > 0) {
        $nombre = '';
        while ($row = $result->fetch_assoc()) {
            $nombre = $row["nombrepais"];
        }
        $result->free();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return $nombre;
    } else {
        $result = $dbx->query("SELECT * from bas_paises where codnumpais='" . sprintf("%03s", $id) . "'");
        if ($result === false) {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return "";
        }
        if ($result->num_rows > 0) {
            $nombre = '';
            while ($row = $result->fetch_assoc()) {
                $nombre = $row["nombrepais"];
            }
            $result->free();
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return $nombre;
        } else {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return "";
        }
    }
}

function retornarNombreCamaraMysqliApi($dbx = null, $id = null)
{
    $retorno = false;
    $result = retornarRegistroMysqliApi($dbx, 'bas_camaras', "id='" . $id . "'");
    if ($result && !empty($result)) {
        $retorno = $result["nombre"];
    }
    unset($result);
    return $retorno;
}

function retornarNombreBarrioMysqliApi($dbx, $mun = '', $id = '')
{
    $retorno = '';
    $result = retornarRegistroMysqliApi($dbx, 'mreg_barriosmuni', "idmunicipio='" . $mun . "' and idbarrio='" . $id . "'");
    if ($result && !empty($result)) {
        $retorno = $result["nombre"];
    }
    unset($result);
    return $retorno;
}

function retornarNombreTablaBasicaMysqliApi($dbx, $tabla, $id)
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    // $dbx->set_charset("utf8");
    $result = $dbx->query("SELECT * from " . $tabla . " where id='" . $id . "'");
    if ($result === false) {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
    if ($result->num_rows > 0) {
        $nombre = '';
        while ($row = $result->fetch_assoc()) {
            $nombre = isset($row["nombre"]) ? $row["nombre"] : $row["descripcion"];
        }
        $result->free();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return $nombre;
    } else {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
}

function retornarNombreTablasSirepMysqliApi($dbx, $idtabla, $idcodigo = '')
{
    return retornarRegistroMysqliApi($dbx, 'mreg_tablassirep', "idtabla='" . $idtabla . "' and idcodigo='" . $idcodigo . "'", "descripcion");
}

function retornarNombreTipoDocumentalMysqliApi($dbx, $id)
{
    $txt = retornarRegistroMysqliApi($dbx, 'bas_tipodoc', "idtipodoc='" . $id . "'", "nombre");
    if ($txt == '') {
        return false;
    }
    $retornar = str_replace("--- ", "", $txt);
    $retornar = str_replace("---", "", $retornar);
    return $retornar;
}

function retornarNombreUsuarioMysqliApi($dbx, $usua)
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    // $dbx->set_charset("utf8");
    $result = $dbx->query("select nombreusuario from usuarios where idusuario='" . $usua . "' or idcodigosirepcaja='" . $usua . "' or idcodigosirepdigitacion='" . $usua . "' or idcodigosirepregistro='" . $usua . "'");
    if ($result === false) {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
    if ($result->num_rows > 0) {
        $nombre = '';
        while ($row = $result->fetch_assoc()) {
            $nombre = isset($row["nombreusuario"]) ? $row["nombreusuario"] : '';
        }
        $result->free();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return $nombre;
    } else {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
}

function retornarNombreUsuarioSirepMysqliApi($dbx, $usua = '')
{
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    // $dbx->set_charset("utf8");
    $result = $dbx->query("select nombreusuario from usuarios where idcodigosirepcaja='" . $usua . "' or idcodigosirepdigitacion='" . $usua . "' or idcodigosirepregistro='" . $usua . "'");
    if ($result === false) {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
    if ($result->num_rows > 0) {
        $nombre = '';
        while ($row = $result->fetch_assoc()) {
            $nombre = isset($row["nombreusuario"]) ? $row["nombreusuario"] : '';
        }
        $result->free();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return $nombre;
    } else {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
    //
}

function retornarNombreActosRegistroMysqliApi($dbx, $idlibro, $idacto = '')
{
    return retornarRegistroMysqliApi($dbx, 'mreg_actos', "idlibro='" . $idlibro . "' and idacto='" . $idacto . "'", "nombre");
}

function retornarNombreActosProponentesMysqliApi($dbx, $idacto = '')
{
    return retornarRegistroMysqliApi($dbx, 'mreg_actosproponente', "id='" . $idacto . "'", "descripcion");
}

function retornarPantallaPredisenadaMysqliApi($dbx, $pantalla = '', $fuente = '')
{
    $pant = retornarRegistroMysqliApi($dbx, 'pantallas_propias', "idpantalla='" . $pantalla . "'");
    if ($pant === false || empty($pant)) {
        $pant = retornarRegistroMysqliApi($dbx, 'bas_pantallas', "idpantalla='" . $pantalla . "'");
        if ($pant === false || empty($pant)) {
            return "";
        } else {
            return $pant["txtasociado"];
        }
    } else {
        return $pant["txtasociado"];
    }
}

function retornarSalarioMinimoActualMysqliApi($dbx, $ano = '')
{
    $smlvs = retornarRegistrosMysqliApi($dbx, 'bas_smlv', "1=1", "fecha asc");
    $minimo = 0;
    foreach ($smlvs as $sm) {
        if ($ano != '') {
            if (substr($sm["fecha"], 0, 4) == $ano) {
                $minimo = $sm["salario"];
            }
        } else {
            if (($sm["fecha"] <= date("Ymd"))) {
                $minimo = $sm["salario"];
            }
        }
    }
    unset($smlvs);
    unset($sm);
    return $minimo;
}
