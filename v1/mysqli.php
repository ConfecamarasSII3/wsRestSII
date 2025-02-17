<?php

// *************************************************************************************
// INTERFAZ BASICA DE MYSQLI
// *************************************************************************************
// *************************************************************************************
// RUTINAS COMPLEMENTARIAS ADAPTADAS PARA MYSQLY
// *************************************************************************************
/**
 * 
 * @param mysqli $dbx
 * @param type $idaccion
 * @param type $idusuario
 * @param type $objeto
 * @param type $idtipodoc
 * @param type $idsede
 * @param type $idnumdoc
 * @param type $detalle 
 * @param type $matricula (afectada)
 * @param type $proponente (afectado)
 * @param type $identificacion (afectada)
 * @param int $numeroliq (o número de recuperacion)
 * @param type $codbarras
 * @param type $anexo
 * @param type $ipcliente 
 * @param type $emailusuario (email del usuario que está logueado)
 * @param type $identificacionusuario (identificación del usuario que está logueado)
 * @return type
 */
function conexionMysqli2($fuente = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
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

    //
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

    //
    if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
        $mysqli = new mysqli("p:" . $dbhost, $dbusuario, $dbpassword, $dbname, $dbport);
    } else {
        $mysqli = new mysqli($dbhost, $dbusuario, $dbpassword, $dbname, $dbport);
    }

    //
    if (mysqli_connect_error()) {
        $_SESSION["generales"]["txtemergente"] = "Error en la conexion a la base de datos";
        return false;
    }

    //
    return $mysqli;
}

function actualizarLogMysqli2($dbx, $idaccion, $idusuario, $objeto, $idtipodoc, $idsede, $idnumdoc, $detalle, $matricula = '', $proponente = '', $identificacion = '', $numeroliq = 0, $codbarras = '', $anexo = 0, $ipcliente = '', $emailusuario = '', $identificacionusuario = '') {

    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    if ($ipcliente != '') {
        $ip = $ipcliente;
    } else {
        if (!empty($_SERVER ['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER ['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
        } else {
            if (isset($_SERVER ['REMOTE_ADDR'])) {
                $ip = $_SERVER ['REMOTE_ADDR'];
            } else {
                $ip = 'localhost';
            }
        }
    }

    if ($numeroliq == '') {
        $numeroliq = 0;
    }

    if ($emailusuario == '') {
        $emailusuario = $_SESSION["generales"]["emailusuariocontrol"];
    }

    if ($identificacionusuario == '') {
        $identificacionusuario = $_SESSION["generales"]["identificacionusuariocontrol"];
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
    $arrValores = array(
        "'" . date("Ymd") . "'",
        "'" . date("H:i:s") . "'",
        "'" . $idaccion . "'",
        "'" . $idusuario . "'",
        "'" . addslashes($emailusuario) . "'",
        "'" . $identificacionusuario . "'",
        "'" . $objeto . "'",
        "'" . $idtipodoc . "'",
        "'" . $idsede . "'",
        "'" . $idnumdoc . "'",
        $numeroliq,
        "'" . $ip . "'",
        "'" . $matricula . "'",
        "'" . $proponente . "'",
        "'" . $identificacion . "'",
        "'" . addslashes($detalle) . "'"
    );
    $res = insertarRegistrosMysqli2($dbx, 'log_' . date("Y"), $arrCampos, $arrValores);

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $res;
}

// ************************************************************************************************* //
// Borrar registros Sii
// ************************************************************************************************* //
function borrarRegistrosMysqli2($dbx, $tabla, $condicion) {

    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
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

// ************************************************************************************************* //
// Buscar saldo del afiliado
// ************************************************************************************************* //
function buscarSaldoAfiliadoMysqli2($dbx, $matricula, $gruposervicios = '', $formacalculo = '') {
    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    if ($formacalculo == '') {
        $formaCalculoAfiliacion = retornarClaveValorSii2($dbx, '90.01.60');
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
        $arrSerAfil = retornarRegistrosMysqli2($dbx, "mreg_servicios", "grupoventas='02'", '', '*');
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
    $arrFecValAfi = retornarRegistroMysqli2($dbx, 'mreg_est_recibos', "matricula='" . $matricula . "' and servicio in (" . $Servicios . ") and ctranulacion = '0' and (substring(numerorecibo,1,1)='R' or substring(numerorecibo,1,1)='S') order by fecoperacion desc limit 1");
    if ($arrFecValAfi && !empty($arrFecValAfi)) {
        $salida["ultanorenafi"] = substr($arrFecValAfi["fecoperacion"], 0, 4);
        $salida["valorultpagoafi"] = $arrFecValAfi["valor"];
        $salida["fechaultpagoafi"] = $arrFecValAfi["fecoperacion"];
    }
    unset($arrFecValAfi);

    $feciniafi = date("Y") . '0101';
    $arrRecs = retornarRegistrosMysqli2($dbx, 'mreg_est_recibos', "(matricula='" . $matricula . "') and ctranulacion = '0' and left(numerorecibo,1) IN ('H','G','R','S') and fecoperacion >= '" . $feciniafi . "'", "fecoperacion");
    if ($arrRecs && !empty($arrRecs)) {
        foreach ($arrRecs as $rx) {
            if (in_array($rx["servicio"], $ServiciosAfiliacion)) {
                $salida["pago"] = $salida["pago"] + $rx["valor"];
                if ($formaCalculoAfiliacion != '') {
                    if ($formaCalculoAfiliacion == 'RANGO_VAL_AFI') {
                        $arrRan = retornarRegistrosMysqli2($dbx, 'mreg_rangos_cupo_afiliacion', "ano='" . date("Y") . "'", "orden");
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

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $salida;
}

// ************************************************************************************************* //
// Contar registros Sii 
// ************************************************************************************************* //
function contarRegistrosMysqli2($dbx, $tabla, $condicion) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $result = mysqli_query($dbx, "select count(*) as contador from " . $tabla . " where " . $condicion);
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
        return false;
    }
    $row = mysqli_fetch_assoc($result);

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $row["contador"];
}

// ************************************************************************************************* //
// Construir noticia parta sur occidentew
// ************************************************************************************************* //
function construirNoticiaSurOccidenteSii2($dbx, $ins) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $salida = '';

    //
    if ($ins["actosistemaanterior"] == '') {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return $salida;
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
        $salida = 'CAMBIO DE DOMICILIO DESDE LA CIUDAD DE ' . retornarRegistroMysqli2($dbx, 'rp_datos_actos', "cod_tipo_registro='" . $treg . "' and cod_libro='" . $nlib . "' and num_inscripcion='" . $nins . "' and cod_tipo_dato='74'", "descripcion");
    }

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $salida;
}

function ejecutarQueryMysqli2($dbx, $query) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
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
function existeTablaSii2($tabla) {
    if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
        $mysqli = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
    } else {
        $mysqli = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
    }
    $result = mysqli_query($mysqli, "select count(*) as count from information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND table_name = '" . $tabla . "'");
    $obj = $result->fetch_object();
    $cant = $obj->count;
    mysqli_free_result($result);
    mysqli_close($mysqli);
    if ($cant == 0) {
        return false;
    } else {
        return true;
    }
}

function encontrarHistoricoPagosMysqliSii($dbx = null, $mat = '', $serviciosRenovacion = array(), $serviciosAfiliacion = array(), $serviciosMatricula = array()) {

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
    if ($dbx == null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    if (!isset($serviciosRenovacion) || empty($serviciosRenovacion)) {
        $serviciosRenovacion = array();
        $serviciosMatricula = array();
        $temx1 = retornarRegistrosMysqli2($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
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
        $temx1 = retornarRegistrosMysqli2($dbx, "mreg_servicios", "1=1", "idservicio");
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
    if (!isset($serviciosMatricula) || empty($serviciosMatricula)) {
        $serviciosMatricula = array();
        $temx1 = retornarRegistrosMysqli2($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
        foreach ($temx1 as $x1) {
            if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
            }
        }
    }

    //
    // $estadosCbNoAsentadoLocal = array('00', '01', '02', '03', '04', '05', '06', '07', '17', '19','39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
    // $estadosCbNoAsentadoRues = array('00', '01', '02', '03', '04', '05', '06', '07', '17', '19', '23', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
    $estadosCbNoAsentadoLocal = array('00', '05', '06', '07', '17', '19', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
    $estadosCbNoAsentadoRues = array('01', '05', '06', '07', '17', '19', '23', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
    $estadosCbRadicadosLocal = array('01', '02', '03', '04', '09', '10');
    $recs = retornarRegistrosMysqli2($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosRenovacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,anorenovacion");
    if ($recs && !empty($recs)) {
        $anosren = array();
        foreach ($recs as $r) {
            if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R') {
                $cba = retornarRegistroMysqli2($dbx, 'mreg_est_codigosbarras', "recibo='" . $r["numerorecibo"] . "'");
                $inc = 'no';
                $ai = 'no';
                if ($cba === false || empty($cba)) {
                    $inc = 'si';
                } else {
                    if ($r["tipogasto"] == '0' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoLocal)) {
                        if (in_array($cba["estadofinal"], $estadosCbRadicadosLocal)) {
                            $liq = retornarRegistroMysqli2($dbx, 'mreg_liquidacion', "numerorecibo='" . $r["numerorecibo"] . "'");
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
                        } else {
                            if ($inc == 'no') {
                                if ($cba["verificacionsoportes"] != 'SI') {
                                    $inc = 'si';
                                }
                            }
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
                    } else {
                        $salida["actultren"] = $r["activos"];
                        $salida["pagultren"] = $r["valor"];
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
                    $salida["renovacionanos"][$ind]["ai"] = $ai;
                }
            }
        }
    }
    unset($recs);
    unset($anosren);

    //
    $recs = retornarRegistrosMysqli2($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosAfiliacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,horaoperacion,anorenovacion");
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
        $exp = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'", "matricula,fecmatricula,fecrenovacion,fecrenant,camant,ultanorenant");
        if ($exp["fecmatricula"] >= $salida["fecultren"]) {
            if ($exp["camant"] != '') {
                if ($salida["fecultren"] < $exp["fecrenant"]) {
                    $salida["fecultren"] = $exp["fecrenant"];
                    $salida["ultanoren"] = $exp["ultanorenant"];
                }
            } else {
                $salida["fecultren"] = $exp["fecmatricula"];
                $salida["ultanoren"] = substr($exp["fecmatricula"], 0, 4);
            }
        }
    }

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $salida;
}

// ************************************************************************************************* //
// Rutina maestra para insercion de registros
// ************************************************************************************************* //
function insertarRegistrosMysqli2($dbx, $tabla, $arrCampos, $arrValores) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
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
    $query .= ") VALUES (";
    $i = 0;
    foreach ($arrValores as $v) {
        $i++;
        if ($i != 1) {
            $query .= ",";
        }
        $query .= $v;
    }
    $query .= ")";
    $result = mysqli_query($dbx, $query);
    if ($result === false) {
        \logSii2::general2('api_sentencias_error_' . date("Ymd"), '', $query);
        $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } else {
        $_SESSION ["generales"] ["lastId"] = 0;
        $_SESSION ["generales"] ["lastId"] = mysqli_insert_id($dbx);

        $_SESSION["generales"]["mensajeerror"] = '';
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return true;
    }
}

// *****************************************************************
// Localiza el dato anterior existente en un campo
// Valida en los últimos 5 años
// *****************************************************************
function localizarCampoAnteriorSii2($dbx, $mat, $campo) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $salida = '';
    $ano = date("Y");
    $anoinicial = date("Y");
    while ($ano > $anoinicial - 5) {
        if (existeTablaSii2('mreg_campos_historicos_' . $ano)) {
            if ($salida == '') {
                $temx = retornarRegistrosMysqli2($dbx, 'mreg_campos_historicos_' . $ano, "matricula='" . $mat . "' and campo='" . $campo . "'", "fecha desc, hora desc");
                if ($temx && !empty($temx)) {
                    foreach ($temx as $tx) {
                        if (!isset($tx["inactivadosipref"]) || strtolower($tx["inactivadosipref"]) != 'si') {
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
        }
        if ($ano != 0) {
            $ano = $ano - 1;
        }
    }

    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $salida;
}

function localizarCampoAnteriorTodosSii2($dbx, $mat, $campo) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $salida = array();
    $ano = date("Y");
    $anoinicial = date("Y");
    while ($ano > $anoinicial - 5) {
        if (existeTablaSii2('mreg_campos_historicos_' . $ano)) {
            $temx = retornarRegistrosMysqli2($dbx, 'mreg_campos_historicos_' . $ano, "matricula='" . $mat . "' and campo='" . $campo . "'", "fecha desc, hora desc");
            if ($temx && !empty($temx)) {
                foreach ($temx as $tx) {
                    if (!isset($tx["inactivadosipref"]) || $tx["inactivadosipref"] != 'si') {
                        if (trim($tx["datoanterior"]) != '') {
                            $salida[] = $tx["datoanterior"];
                        }
                    }
                }
            }
        }
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

function localizarSmmlvMysqli2($dbx = null, $fecha) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    $temx = retornarRegistrosMysqli2($dbx, 'bas_smlv', "fecha");
    foreach ($temx as $res) {
        if ($res["fecha"] < $fecha) {
            $resultado = $res["salario"];
        }
    }

    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }
    return $resultado;
}

// *****************************************************************
// Retorna una clave valor
// *****************************************************************
function retornarClaveValorSii2($dbx, $clave) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    if (!isset($_SESSION["generales"]["clavevalor"][$clave])) {
        $_SESSION["generales"]["clavevalor"][$clave] = '';
        $query = "select * from bas_claves_valor, claves_valor ";
        $query .= "where bas_claves_valor.idorden='" . $clave . "' and ";
        $query .= "claves_valor.id=bas_claves_valor.id";
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
            while ($row = $result->fetch_assoc()) {
                $_SESSION["generales"]["clavevalor"][$clave] = $row["valor"];
            }
        }
        $result->free();
    }

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $_SESSION["generales"]["clavevalor"][$clave];
}

// ************************************************************************************************* //
// Rutina maestra para insercion de registros
// ************************************************************************************************* //
function insertarRegistrosBloqueMysqli2($dbx, $tabla, $arrCampos, $arrValores) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    foreach ($arrValores as $val) {
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
        foreach ($val as $v) {
            $i++;
            if ($i != 1) {
                $query .= ",";
            }
            $query .= $v;
        }
        $query .= ")";

        $result = mysqli_query($dbx, $query);
        if ($result === false) {
            \logSii2::general2('api_sentencias_error_' . date("Ymd"), '', $query);
            $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }
    }

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return true;
}

// ************************************************************************************************* //
// Rutina maestra para regrabación de registros
// ************************************************************************************************* //
function regrabarRegistrosMysqli2($dbx, $tabla, $arrCampos, $arrValores, $condicion) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $i = 0;
    $query = "update " . $tabla . " set ";
    foreach ($arrCampos as $c) {
        $i++;
        if ($i != 1) {
            $query .= ",";
        }
        $query .= $c . '=' . $arrValores [$i - 1];
    }
    $query .= " where " . $condicion;

    $result = mysqli_query($dbx, $query);
    if ($result === false) {
        \logSii2::general2('api_sentencias_error_' . date("Ymd"), '', $query);
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
 * @return boolean|string
 */
function retornarRegistrosMysqli2($dbx, $tabla, $condicion, $orden = '', $campos = '*', $offset = 0, $limit = 0) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
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
    if ($limit != 0 && $offset == 0) {
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
            $res[$i] = $row;
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
 * @param type $tabla
 * @param type $condicion
 * @param type $campos
 * @param type $tip
 * @return boolean|string
 */
function retornarRegistroMysqli2($dbx = null, $tabla = null, $condicion = null, $campos = '*', $tip = 'P') {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $uncampo = 'no';
    if ($campos != '*') {
        if (strpos($campos, ',') === false) {
            $uncampo = 'si';
        }
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
                    $res = $row;
                }
            } else {
                $res = $row;
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

function retornarNombreCamaraMysqli2($dbx = null, $id = null) {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //    
    $retorno = false;
    $result = retornarRegistroMysqli2($dbx, 'bas_camaras', "id='" . $id . "'");
    if ($result && !empty($result)) {
        $retorno = $result["nombre"];
    }
    unset($result);
    unset($res);

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $retorno;
}

function retornarNombreBarrioMysqli2($dbx = null, $mun = '', $id = '') {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //    
    $retorno = '';
    $result = retornarRegistroMysqli2($dbx, 'mreg_barriosmuni', "idmunicipio='" . $mun . "' and idbarrio='" . $id . "'");
    if ($result && !empty($result)) {
        $retorno = $result["nombre"];
    }
    unset($result);
    unset($res);

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    return $retorno;
}

function retornarNombreMunicipioMysqli2($dbx, $id) {
    return retornarRegistroMysqli2($dbx, "bas_municipios", "codigomunicipio='" . $id . "'", "ciudad");
}

function retornarNombreDptoMysqli2($dbx, $id) {
    return retornarRegistroMysqli2($dbx, "bas_municipios", "codigomunicipio='" . $id . "'", "departamento");
}

function retornarNombrePaisMysqli2($dbx, $id) {
    return retornarRegistroMysqli2($dbx, "bas_paises", "idpais='" . $id . "'", "nombrepais");
}

function retornarDescripcionCiiuMysqli2($dbx, $id) {
    return retornarRegistroMysqli2($dbx, "bas_ciius", "idciiu='" . $id . "'", "descripcion");
}

function retornarNombreTablaBasicaMysqli2($dbx, $tabla, $id = '') {
    return retornarRegistroMysqli2($dbx, $tabla, "id='" . $id . "'", "descripcion");
}

function retornarNombreTablasSirepMysqli2($dbx, $idtabla, $idcodigo = '') {
    return retornarRegistroMysqli2($dbx, 'mreg_tablassirep', "idtabla='" . $idtabla . "' and idcodigo='" . $idcodigo . "'", "descripcion");
}

function retornarNombreUsuarioMysqli2($dbx, $usua) {
    return retornarRegistroMysqli2($dbx, 'usuarios', "idusuario='" . $usua . "' or idcodigosirepcaja='" . $usua . "' or idcodigosirepdigitacion='" . $usua . "' or idcodigosirepregistro='" . $usua . "'", "nombreusuario");
}

function retornarNombreUsuarioSirepMysqli2($dbx, $usua = '') {
    return retornarRegistroMysqli2($dbx, 'usuarios', "idcodigosirepcaja='" . $usua . "' or idcodigosirepdigitacion='" . $usua . "' or idcodigosirepregistro='" . $usua . "'", "nombreusuario");
}

function retornarPantallaPredisenadaMysqli2($dbx, $pantalla = '') {
    $pant = retornarRegistroMysqli2($dbx, 'pantallas_propias', "idpantalla='" . $pantalla . "'");
    if ($pant === false || empty($pant)) {
        $pant = retornarRegistroMysqli2($dbx, 'bas_pantallas', "idpantalla='" . $pantalla . "'");
        if ($pant === false || empty($pant)) {
            return "";
        } else {
            return $pant["txtasociado"];
        }
    } else {
        return $pant["txtasociado"];
    }
}

function retornarNombreActosRegistroMysqli2($dbx, $idlibro, $idacto = '') {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $dbx->set_charset("utf8");
    $result = $dbx->query("select * from mreg_actos where idlibro='" . $idlibro . "' and idacto='" . $idacto . "'");
    if ($result === false) {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
    if ($result->num_rows > 0) {
        $nombre = '';
        while ($row = $result->fetch_assoc()) {
            $nombre = isset($row["nombre"]) ? $row["nombre"] : '';
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

function retornarNombreActosProponentesMysqli2($dbx, $idacto = '') {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $dbx->set_charset("utf8");
    $result = $dbx->query("select * from mreg_actosproponente where id='" . $idacto . "'");
    if ($result === false) {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return "";
    }
    if ($result->num_rows > 0) {
        $nombre = '';
        while ($row = $result->fetch_assoc()) {
            $nombre = isset($row["descripcion"]) ? $row["descripcion"] : '';
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

function retornarSecuenciaMysqli2($dbx, $sec = '') {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysqli = 'si';
    }

    //
    $res = retornarRegistroMysqli2($dbx, 'secuencias', "tipo='" . $sec . "'");
    if ($res === false) {
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    }

    if (empty($res)) {
        $retornar = 1900000000;
    } else {
        $retornar = $res["consecutivo"];
    }

    //
    $retornar++;

    //
    if ($sec == 'CON-PONAL-MULTAS') {
        $ok = 'no';
        while ($ok == 'no') {
            if (contarRegistrosMysqli2($dbx, 'mreg_multas_ponal', "idliquidacion=" . $retornar) > 0) {
                $retornar++;
            } else {
                $ok = 'si';
            }
        }
    }

    //
    if ($sec == 'LIQUIDACION-REGISTROS') {
        $ok = 'no';
        while ($ok == 'no') {
            if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "idliquidacion=" . $retornar) > 0) {
                $retornar++;
            } else {
                $arrCampos = array(
                    'idliquidacion',
                    'fecha',
                    'hora',
                    'idestado'
                );
                $arrValores = array(
                    $retornar,
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "'01'"
                );
                insertarRegistrosMysqli2($dbx, 'mreg_liquidacion', $arrCampos, $arrValores);
                $ok = 'si';
            }
        }
    }

    if ($sec == 'DEVOLUCION-REGISTROS') {
        $ok = 'no';
        while ($ok == 'no') {
            if (contarRegistrosMysqli2($dbx, 'mreg_devoluciones_nueva', "iddevolucion=" . $retornar) > 0) {
                $retornar++;
            } else {
                $ok = 'si';
            }
        }
    }

    if ($sec == 'RADICACION-REPORTES-EE') {
        $ok = 'no';
        while ($ok == 'no') {
            if (contarRegistrosMysqli2($dbx, 'mreg_reportesradicados', "idradicacion='" . ltrim($retornar, "0") . "'") > 0) {
                $retornar++;
            } else {
                $ok = 'si';
            }
        }
    }

    if (empty($res)) {
        $arrCampos = array(
            'tipo',
            'consecutivo'
        );
        $arrValores = array(
            "'" . $sec . "'",
            $retornar
        );
        insertarRegistrosMysqli2($dbx, 'secuencias', $arrCampos, $arrValores);
    } else {
        $arrCampos = array(
            'consecutivo'
        );
        $arrValores = array(
            $retornar
        );
        regrabarRegistrosMysqli2($dbx, 'secuencias', $arrCampos, $arrValores, "tipo='" . $sec . "'");
    }

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $retornar;
}

function asignarNumeroRecuperacionMysqli2($dbx, $tipo) {
    require_once ('funcionesSii2.php');

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    //
    $OK = 'NO';
    while ($OK == 'NO') {

        //
        if ($tipo == 'mreg') {
            $num = strtoupper(trim(\funcionesSii2::generarAleatorioAlfanumerico()));
            if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . trim($num) . "'") == 0) {
                $OK = "SI";
            }
        } else {

            //
            if ($tipo == 'news') {
                $num = strtoupper(trim(\funcionesSii2::generarAleatorioAlfanumerico10()));
                if (contarRegistrosMysqli2($dbx, 'news', "numerorecuperacion='" . trim($num) . "'") == 0) {
                    $OK = "SI";
                }

                //    
            } else {
                $num = strtoupper(trim(\funcionesSii2::generarAleatorioAlfanumerico()));
            }
        }
    }

    //
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }

    //
    return $num;
}

?>