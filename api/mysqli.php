<?php

// *************************************************************************************
// INTERFAZ BASICA DE MYSQLI
// *************************************************************************************
//
function conexionMysqliApi($fuente = '', $ehost = '', $eport = '', $edb = '', $euser = '', $epass = '') {
    if ($fuente == 'personalizado') {
        try {
            $mysqli = new mysqli($ehost, $euser, $epass, $edb, $eport);
            return $mysqli;
        } catch (exception $e) {
            return false;
        }
    } else {
        return conexionMysqliApiCamaras($fuente);
    }
}

function conexionMysqliApiCamaras($fuente = '') {
    $listards1 = array('10', '11', '16', '17', '18', '20', '24', '26', '27', '28', '29', '32', '33', '34', '37', '39', '40', '44', '46', '48', '49', '52', '54', '56', '57');
    $listards2 = array('01', '02', '06', '07', '12', '13', '14', '15', '19', '22', '23', '25', '30', '31', '35', '36', '38', '41', '42', '43', '45', '47', '50', '51', '53', '55');
    if (substr($fuente, 0, 2) != 'P-' && substr($fuente, 0, 2) != 'R-' && substr($fuente, 0, 2) != 'D-' && substr($fuente, 0, 2) != 'Q-') {
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
            $_SESSION["generales"]["txtemergente"] = "Cámara de comercio no configurada en este ambiente";
            return false;
        }
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
        /*
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
         */
    }


    if (substr($fuente, 0, 2) == 'P-' || substr($fuente, 0, 2) == 'R-' || substr($fuente, 0, 2) == 'D-' || substr($fuente, 0, 2) == 'Q-') {
        if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/api/mysqlidesarrollo.php')) {
            include ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqlidesarrollo.php');
        } else {
            $_SESSION["generales"]["txtemergente"] = "No es posible la conexión a la BD (***)";
            return false;
        }
    }

    //
    if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
        try {
            $mysqli = new mysqli("p:" . $dbhost, $dbusuario, $dbpassword, $dbname, $dbport);
        } catch (exception $e) {
            $_SESSION["generales"]["txtemergente"] = "Error en la conexion a la base de datos";
            return false;
        }
    } else {
        try {
            $mysqli = new mysqli($dbhost, $dbusuario, $dbpassword, $dbname, $dbport);
        } catch (exception $e) {
            $_SESSION["generales"]["txtemergente"] = "Error en la conexion a la base de datos";
            return false;
        }
    }

    //
    if (mysqli_connect_error()) {
        $_SESSION["generales"]["txtemergente"] = "Error en la conexion a la base de datos";
        return false;
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    // $mysqli->set_charset("utf8");
    $_SESSION["generales"]["bdreferencia"] = $dbname;
    return $mysqli;
}

/**
 * 
 * @param type $dbx
 * @param type $idaccion
 * @param type $idusuario
 * @param type $objeto
 * @param type $idtipodoc
 * @param type $idsede
 * @param type $idnumdoc
 * @param type $detalle
 * @param type $matricula
 * @param type $proponente
 * @param type $identificacion
 * @param int $numeroliq
 * @param type $codbarras
 * @param type $anexo
 * @param type $ipx
 * @return boolean
 */
function actualizarLogMysqliApi($dbx, $idaccion, $idusuario, $objeto, $idtipodoc, $idsede, $idnumdoc, $detalle, $matricula = '', $proponente = '', $identificacion = '', $numeroliq = 0, $codbarras = 0, $anexo = 0, $ipx = '') {
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
    if ($ipx != '') {
        $ip = $ipx;
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
        "'" . addslashes($detalle) . "'"
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

function actualizarLogMigracionMysqliApi($dbx, $idaccion, $idusuario, $objeto, $idtipodoc, $idsede, $idnumdoc, $detalle, $matricula = '', $proponente = '', $identificacion = '', $numeroliq = 0, $codbarras = 0, $anexo = 0, $ipx = '') {
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
    if ($ipx != '') {
        $ip = $ipx;
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
        "'" . addslashes($detalle) . "'"
    );
    $res = insertarRegistrosMysqliApi($dbx, 'log_migracion', $arrCampos, $arrValores);
    if ($res === false) {
        return false;
    }
    if ($cerrarMysqli === 'si') {
        $dbx->close();
    }
    return $res;
}

/**
 * 
 * @param type $dbx
 * @param type $tabla
 * @param type $campo
 * @param type $contenido
 * @param type $condicion
 */
function actualizarCampoMysqliApi($dbx, $tabla, $campo, $contenido, $condicion) {    
    regrabarRegistrosMysqliApi ($dbx, $tabla, array($campo), array($contenido), $condicion);
}

/**
 * 
 * @param type $dbx
 * @param type $proceso
 * @param type $expediente
 * @param type $grupo
 * @param type $mensaje
 */
function actualizarLogCompite360MysqliApi($dbx, $proceso, $expediente, $grupo, $xmljson, $mensaje) {    
    $arrCampos = array(
        'fecha',
        'hora',
        'proceso',
        'expediente',
        'grupo',
        'xml_json',
        'mensaje'
    );
    $arrValores = array(
        "'" . date("Ymd") . "'",
        "'" . date("His") . "'",
        "'" . $proceso . "'",
        "'" . $expediente . "'",
        "'" . $grupo . "'",
        "'" . addslashes($xmljson) . "'",
        "'" . addslashes($mensaje) . "'"
    );
    insertarRegistrosMysqliApi($dbx, 'mreg_log_compite360_' . date("Y"), $arrCampos, $arrValores);
}

function actualizarLogDocumentosMysqliApi($dbx, $periodo, $idtipodoc, $numdoc, $fecha, $hora, $accion, $usuario, $obs = '') {
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
function borrarRegistrosMysqliApi($dbx, $tabla, $condicion = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    $cerrarMysql = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
        $cerrarMysql = 'si';
    }

    if ($condicion == '') {
        $query = "delete from " . $tabla;
    } else {
        $query = "delete from " . $tabla . " where " . $condicion;
    }
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

/**
 * 
 * @param type $dbx
 * @param type $tabla
 * @return bool
 */
function truncarTablaMysqliApi($dbx, $tabla) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    $cerrarMysql = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
        $cerrarMysql = 'si';
    }

    $_SESSION["generales"]["mensajerrror"] = '';
    if (existeTablaMysqliApi($dbx, $tabla)) {
        $query = "truncate table " . $tabla;
        $result = mysqli_query($dbx, $query);
        if ($result === false) {
            \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx));
            $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
            if ($cerrarMysql == 'si') {
                $dbx->close();
            }
            $_SESSION["generales"]["mensajeerror"] = 'Error ejecutando truncate';
            return false;
        } else {
            $_SESSION["generales"]["mensajeerror"] = '';
            if ($cerrarMysql == 'si') {
                $dbx->close();
            }
            return true;
        }
    } else {
        $_SESSION["generales"]["mensajeerror"] = 'Tabla no encontrada';
        return false;
    }
}

function clonarTablaVaciaMysqliApi($dbx, $base, $tabla) {
    $res = mysqli_query($dbx, "CREATE TABLE `" . $tabla . "` LIKE `" . $base . "`");
    if ($res === false) {
        return false;
    }
    return true;
}

//
function busqueInscripcionesMysqliApi($mysqli, $mat, $actos) {
    $ix = 0;
    $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $mat . "'", "fecharegistro");
    if ($regs && !empty($regs)) {
        foreach ($regs as $r) {
            if ($r["libro"] != 'RM07' && $r["libro"] != 'RE52') {
                $ind = $r["libro"] . '-' . $r["acto"];
                if (!isset($actos[$ind])) {
                    if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $r["fecharegistro"]) < 1096) {
                        $ix++;
                    }
                } else {
                    // Excluye embargos y medidas cautelares
                    if ($actos[$ind]["idgrupoacto"] != '018' && $actos[$ind]["idgrupoacto"] != '019' && $actos[$ind]["idgrupoacto"] != '051') {
                        if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $r["fecharegistro"]) < 1096) {
                            $ix++;
                        }
                    }
                }
            }
        }
    }
    unset($regs);
    if ($ix > 0) {
        return true;
    }
    return false;
}

// ************************************************************************************************* //
// Buscar saldo del afiliado
// ************************************************************************************************* //
function buscarSaldoAfiliadoMysqliApi($dbx, $matricula, $gruposervicios = '', $formacalculo = '') {
    $cerrarMysql = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
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
function contarRegistrosMysqliApi($dbx, $tabla, $condicion) {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }
    try {
        $result = mysqli_query($dbx, "select count(*) as contador from " . $tabla . " where " . $condicion);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
            return false;
        }
        $row = mysqli_fetch_assoc($result);
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Excepcion';
        return false;
    }
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }
    return $row["contador"];
}

// ************************************************************************************************* //
// Construir noticia parta sur occidentew
// ************************************************************************************************* //
function construirNoticiaSurOccidenteMysqliApi($mysqli, $ins) {
    $salida = '';

    //
    if ($ins["actosistemaanterior"] == '') {
        return $salida;
    }

    $cerrarMysqli = 'no';
    if ($mysqli === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $mysqli = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $mysqli = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
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

/**
 * 
 * @param type $dbx
 * @param type $mat
 * @param type $serviciosRenovacion
 * @param type $serviciosAfiliacion
 * @param type $serviciosMatricula
 * @param type $fuente (mreg_est_inscritos, mreg_est_inscritos_AAAA)
 * @param type $fechacorte Fecha de corte para búsquedas
 * @return array
 */
function encontrarHistoricoPagosMysqliApi($dbx = null, $mat = '', $serviciosRenovacion = array(), $serviciosAfiliacion = array(), $serviciosMatricula = array(), $fuente = 'mreg_est_inscritos', $fechacorte = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    if ($fechacorte == '') {
        $fechacorte = date("Ymd");
    }


    //
    $ultmov = '';
    $tieneren = '';

    //
    $nameLog = 'encontrarHistoricoRenovacion_' . date("Ymd");

    //
    $salida = array();
    $salida["fecultren"] = '';
    $salida["ultanoren"] = '';
    $salida["fecultren1"] = '';
    $salida["ultanoren1"] = '';
    $salida["fecultren2"] = '';
    $salida["ultanoren2"] = '';

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
        $dbx = conexionMysqliApi();
        $cerrarMysqli = 'si';
    }

    //
    $fcorte = retornarRegistroMysqliApi($dbx, "mreg_cortes_renovacion", "ano='" . substr($fechacorte, 0, 4) . "'", "corte");

    //
    $exp = retornarRegistroMysqliApi($dbx, $fuente, "matricula='" . $mat . "'");
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
    $recs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosRenovacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0') and fecoperacion <='" . $fechacorte . "'", "fecoperacion,horaoperacion,anorenovacion");
    if ($recs && !empty($recs)) {
        $anosren = array();
        foreach ($recs as $r) {

            if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R' || substr($r["numerorecibo"], 0, 1) == 'Z') {
                $devueltodesistido = 'no';
                $devueltonoreingresado = 'no';
                $numerocb = '';
                $fechareingresocba = '';
                $cba = retornarRegistroMysqliApi($dbx, 'mreg_est_codigosbarras', "recibo='" . $r["numerorecibo"] . "'");
                if ($cba && !empty($cba)) {
                    $cbest = retornarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras_documentos', "codigobarras='" . $cba["codigobarras"] . "'", "fecha,hora");
                    if ($cbest && !empty($cbest)) {
                        foreach ($cbest as $cbx) {
                            $numerocb = $cbx["codigobarras"];
                            if ($cbx["estado"] == '09') {
                                $fechareingresocba = $cbx["fecha"];
                                $devueltodesistido = 'no';
                                $devueltonoreingresado = 'no';
                            }
                            if ($cbx["estado"] == '05' ||
                                    $cbx["estado"] == '06' ||
                                    $cbx["estado"] == '07' ||
                                    $cbx["estado"] == '17' ||
                                    $cbx["estado"] == '39'
                            ) {
                                $devueltodesistido = 'si';
                            }
                            if ($cbx["estado"] == '05' ||
                                    $cbx["estado"] == '06'
                            ) {
                                $devueltonoreingresado = 'si';
                            }
                        }
                    }
                }

                /*
                if ($devueltonoreingresado == 'si') {
                    $cbest = retornarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras_documentos', "codigobarras='" . $cba["codigobarras"] . "'", "fecha,hora");
                    if ($cbest && !empty($cbest)) {
                        foreach ($cbest as $cbx) {
                            $numerocb = $cbx["codigobarras"];
                            if ($cbx["estado"] == '04') {
                                $fechareingresocba = $cbx["fecha"];
                                $devueltodesistido = 'no';
                                $devueltonoreingresado = 'no';
                            }
                            if ($cbx["estado"] == '05' ||
                                    $cbx["estado"] == '06' ||
                                    $cbx["estado"] == '07' ||
                                    $cbx["estado"] == '17' ||
                                    $cbx["estado"] == '39'
                            ) {
                                $devueltodesistido = 'si';
                            }
                            if ($cbx["estado"] == '05' ||
                                    $cbx["estado"] == '06'
                            ) {
                                $devueltonoreingresado = 'si';
                            }
                        }
                    }
                }
                */
                
                //
                $inc = 'no';
                $ai = 'no';

                //
                if ($devueltodesistido == 'no') {
                    if ($cba === false || empty($cba)) {
                        $inc = 'si';
                    } else {
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
                    }
                }

                //
                if ($inc == 'si') {
                    if (isset($serviciosMatricula[$r["servicio"]])) {
                        $ultmov = 'matricula';
                    } else {
                        if (isset($serviciosRenovacion[$r["servicio"]])) {
                            $ultmov = '';
                            $tieneren = 'si';
                        }
                    }
                    if (!isset($anosren[$r["anorenovacion"]]) || $anosren[$r["anorenovacion"]] == 1) {
                        if ($fechareingresocba != '') {
                            $salida["fecultren"] = $fechareingresocba;
                        } else {
                            if (ltrim(trim((string) $r["fecharenovacionaplicable"]), "0") != '' && $r["fecharenovacionaplicable"] != $r["fecoperacion"]) {
                                $salida["fecultren"] = (string) $r["fecharenovacionaplicable"];
                            } else {
                                $salida["fecultren"] = (string) $r["fecoperacion"];
                            }
                        }
                        $salida["ultanoren"] = (string) $r["anorenovacion"];
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
                    $salida["renovacionanos"][$ind]["recibo"] = (string) $r["numerorecibo"];
                    $salida["renovacionanos"][$ind]["codigobarras"] = $numerocb;
                    $salida["renovacionanos"][$ind]["devueltodesistido"] = $devueltodesistido;
                    $salida["renovacionanos"][$ind]["fecharecibo"] = (string) $r["fecoperacion"];
                    $salida["renovacionanos"][$ind]["ano"] = (string) $r["anorenovacion"];
                    // $salida["renovacionanos"][$ind]["fecrenovacion"] = $salida["fecultren"];
                    if ($fechareingresocba != '') {
                        $salida["renovacionanos"][$ind]["fecrenovacion"] = $fechareingresocba;
                    } else {
                        if (trim((string) $r["fecharenovacionaplicable"]) != '') {
                            $salida["renovacionanos"][$ind]["fecrenovacion"] = $r["fecharenovacionaplicable"];
                        } else {
                            $salida["renovacionanos"][$ind]["fecrenovacion"] = $r["fecoperacion"];
                        }
                    }
                    $salida["renovacionanos"][$ind]["activos"] = $r["activos"];
                    $salida["renovacionanos"][$ind]["valor"] = $r["valor"];
                    $salida["renovacionanos"][$ind]["ai"] = (string) $ai;
                } else {
                    $ind = $r["anorenovacion"] . '-' . $r["fecoperacion"] . '-' . $r["horaoperacion"];
                    $salida["renovacionsinaplicaranos"][$ind] = array();
                    $salida["renovacionsinaplicaranos"][$ind]["recibo"] = (string) $r["numerorecibo"];
                    $salida["renovacionsinaplicaranos"][$ind]["codigobarras"] = $numerocb;
                    $salida["renovacionsinaplicaranos"][$ind]["devueltodesistido"] = $devueltodesistido;
                    $salida["renovacionsinaplicaranos"][$ind]["fecharecibo"] = (string) $r["fecoperacion"];
                    $salida["renovacionsinaplicaranos"][$ind]["ano"] = (string) $r["anorenovacion"];
                    if ($fechareingresocba != '') {
                        $salida["renovacionsinaplicaranos"][$ind]["fecrenovacion"] = $fechareingresocba;
                    } else {
                        if (trim((string) $r["fecharenovacionaplicable"]) != '') {
                            $salida["renovacionsinaplicaranos"][$ind]["fecrenovacion"] = $r["fecharenovacionaplicable"];
                        } else {
                            $salida["renovacionsinaplicaranos"][$ind]["fecrenovacion"] = $r["fecoperacion"];
                        }
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
        $exp = retornarRegistroMysqliApi($dbx, $fuente, "matricula='" . $mat . "'", "matricula,fecmatricula,fecrenovacion,ultanoren,fecrenant,camant,ultanorenant");
        if ($exp["camant"] != '') {
            $salida["fecultren"] = $exp["fecrenant"];
            $salida["ultanoren"] = $exp["ultanorenant"];
        } else {
            if ($exp["fecrenovacion"] != '') {
                $salida["fecultren"] = $exp["fecrenovacion"];
                $salida["ultanoren"] = $exp["ultanoren"];
            } else {
                $salida["fecultren"] = $exp["fecmatricula"];
                $salida["ultanoren"] = substr($exp["fecmatricula"], 0, 4);
            }
        }
    }

    //
    // 2022-08-02: JINT
    // Se cambia la rutina
    // Ejemplo conversiones de civil a comerciales
    // Cambios de domicilio

    if ($salida["fecultren"] != '') {
        if ($exp["fecmatricula"] >= $salida["fecultren"]) {
            if ($exp["camant"] != '') {
                if ($salida["fecultren"] <= $exp["fecrenant"]) {
                    $salida["fecultren"] = $exp["fecrenant"];
                    $salida["ultanoren"] = $exp["ultanorenant"];
                }
            } else {
                if ($tieneren != 'si') {
                    $salida["fecultren"] = $exp["fecmatricula"];
                    $salida["ultanoren"] = substr($exp["fecmatricula"], 0, 4);
                }
            }
        }
    }

    // ********************************************************************************************************************** //
    // Encuentra ultimo pago de afiliación y la fecha del mismo
    // ********************************************************************************************************************** //
    $recsafi = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosAfiliacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0') and fecoperacion <='" . $fechacorte . "'", "fecoperacion,horaoperacion,anorenovacion");
    if ($recsafi && !empty($recsafi)) {
        foreach ($recsafi as $r) {
            if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R') {
                $ind = $r["anorenovacion"] . '-' . $r["fecoperacion"];
                $salida["afiliacionanos"][$ind] = array();
                $salida["afiliacionanos"][$ind]["ano"] = (string) $r["anorenovacion"];
                if (trim((string) $r["fecharenovacionaplicable"]) != '') {
                    $salida["afiliacionanos"][$ind]["fecpago"] = (string) $r["fecharenovacionaplicable"];
                } else {
                    $salida["afiliacionanos"][$ind]["fecpago"] = (string) $r["fecoperacion"];
                }
                $salida["afiliacionanos"][$ind]["activos"] = $r["activos"];
                $salida["afiliacionanos"][$ind]["valor"] = $r["valor"];
                $salida["afiliacionanos"][$ind]["recibo"] = (string) $r["numerorecibo"];
                $salida["afiliacionanos"][$ind]["tipo"] = (string) $r["servicio"];

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
    $salida["fecultren1"] = '';
    $salida["ultanoren1"] = '';
    $salida["fecultren2"] = '';
    $salida["ultanoren2"] = '';
    $salida["fecultren3"] = '';
    $salida["ultanoren3"] = '';
    $salida["fecultren4"] = '';
    $salida["ultanoren4"] = '';

    $recs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosRenovacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0') and fecoperacion <= '" . $fechacorte . "'", "fecoperacion,horaoperacion,anorenovacion");
    foreach ($recs as $r) {
        if ((int) $salida["ultanoren"] == 0) {
            $salida["ultanoren"] = substr($salida["fecultren"], 0, 4);
        }
        $a1 = (int) $salida["ultanoren"] - 1;
        $a2 = (int) $salida["ultanoren"] - 2;
        $a3 = (int) $salida["ultanoren"] - 3;
        $a4 = (int) $salida["ultanoren"] - 4;
        if ($r["anorenovacion"] == $a1) {
            if ($salida["fecultren1"] == '') {
                $salida["fecultren1"] = (string) $r["fecharenovacionaplicable"];

                if (trim((string) $salida["fecultren1"]) == '') {
                    $salida["fecultren1"] = (string) $r["fecoperacion"];
                }
                $salida["ultanoren1"] = $r["anorenovacion"];
            }
        }
        if ($r["anorenovacion"] == $a2) {
            if ($salida["fecultren2"] == '') {
                $salida["fecultren2"] = (string) $r["fecharenovacionaplicable"];
                if (trim((string) $salida["fecultren2"]) == '') {
                    $salida["fecultren2"] = (string) $r["fecoperacion"];
                }
                $salida["ultanoren2"] = (string) $r["anorenovacion"];
            }
        }
        if ($r["anorenovacion"] == $a3) {
            if ($salida["fecultren3"] == '') {
                $salida["fecultren3"] = (string) $r["fecharenovacionaplicable"];
                if (trim((string) $salida["fecultren3"]) == '') {
                    $salida["fecultren3"] = (string) $r["fecoperacion"];
                }
                $salida["ultanoren3"] = (string) $r["anorenovacion"];
            }
        }
        if ($r["anorenovacion"] == $a4) {
            if ($salida["fecultren4"] == '') {
                $salida["fecultren4"] = (string) $r["fecharenovacionaplicable"];
                if (trim((string) $salida["fecultren4"]) == '') {
                    $salida["fecultren4"] = (string) $r["fecoperacion"];
                }
                $salida["ultanoren4"] = (string) $r["anorenovacion"];
            }
        }
    }

    // 2022-08-02: JINT
    // Regraba fecha de renovación  y último año renovado
    // de acuerdo con el calculado
    // siempre y cuando sea diferente a la que está en iscritos
    if ($salida["fecultren"] != '' && $fechacorte == date("Ymd")) {
        if ($exp && !empty($exp)) {
            if ($salida["fecultren"] != $exp["fecrenovacion"]) {
                if ((string) $salida["fecultren"] != '' && (string) $salida["ultanoren"] != '') {
                    $arrCampos = array(
                        'fecrenovacion',
                        'ultanoren'
                    );
                    $arrValores = array(
                        "'" . $salida["fecultren"] . "'",
                        "'" . $salida["ultanoren"] . "'"
                    );
                    $res = regrabarRegistrosMysqliApi($dbx, $fuente, $arrCampos, $arrValores, "matricula='" . $exp["matricula"] . "'");
                    if ($res === false) {
                        $dbx1 = conexionMysqliApi();
                        $res = regrabarRegistrosMysqliApi($dbx1, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $exp["matricula"] . "'");
                        $dbx1->close();
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

/**
 * 
 * @param type $dbx
 * @param type $mat
 * @param type $serviciosAfiliacion
 * @param type $fuente
 * @param type $fechacorte
 * @return int
 */
function encontrarHistoricoPagosAfiliacionMysqliApi($dbx = null, $mat = '', $serviciosAfiliacion = array(), $fuente = 'mreg_est_inscritos', $fechacorte = '') {
    if ($fechacorte == '') {
        $fechacorte = date("Ymd");
    }
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    $nameLog = 'encontrarHistoricoRenovacionAfiliacion_' . date("Ymd");
    \logApi::general2($nameLog, '', '');
    $salida = array();
    $salida["fecrenaflia"] = '';
    $salida["anorenaflia"] = '';
    $salida["valorpagadoultimaafiliacion"] = 0;
    if ($mat == '') {
        return $salida;
    }
    $cerrarMysqli = 'no';
    if ($dbx == null) {
        $dbx = conexionMysqliApi();
        $cerrarMysqli = 'si';
    }
    $fcorte = retornarRegistroMysqliApi($dbx, "mreg_cortes_renovacion", "ano='" . substr($fechacorte, 0, 4) . "'", "corte");
    $exp = retornarRegistroMysqliApi($dbx, $fuente, "matricula='" . $mat . "'");
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
    $recs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosAfiliacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0') and fecoperacion <='" . $fechacorte . "'", "fecoperacion,horaoperacion,anorenovacion");
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
                    if (ltrim(trim((string) $r["fecharenovacionaplicable"]), "0") != '') {
                        $salida["fecrenaflia"] = $r["fecharenovacionaplicable"];
                    } else {
                        $salida["fecrenaflia"] = $r["fecoperacion"];
                    }
                    $salida["anorenaflia"] = substr($salida["fecrenaflia"], 0, 4);
                    $salida["valorpagadoultimaafiliacion"] = $r["valor"];
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

function encontrarPagosRenovacionMysqliApi($dbx = null, $mat = '', $serviciosMatricula = array(), $serviciosRenovacion = array()) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

    //
    $nameLog = 'encontrarPagosRenovacion_' . date("Ymd");

    //
    $salida = array();

    //
    if ($mat == '') {
        return $salida;
    }

    //
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
    foreach ($serviciosMatricula as $s) {
        if ($tServiciosRenovacion != '') {
            $tServiciosRenovacion .= ",";
        }
        $tServiciosRenovacion .= "'" . $s . "'";
    }
    foreach ($serviciosRenovacion as $s) {
        if ($tServiciosRenovacion != '') {
            $tServiciosRenovacion .= ",";
        }
        $tServiciosRenovacion .= "'" . $s . "'";
    }


    // ********************************************************************************************************************** //
    // Encuentra ultimo año renovado y fecha de renovación
    // ********************************************************************************************************************** //
    $estadosCbNoAsentadoLocal = array("00", "05", "06", "09", "10", "07", "17", "19", "39", "40", "41", "42", "43", "50", "51", "52", "53", "99");
    $estadosCbNoAsentadoRues = array("01", "05", "06", "07", "09", "10", "17", "19", "23", "39", "40", "41", "42", "43", "50", "51", "52", "53", "99");
    $estadosCbRadicadosLocal = array("01", "02", "03", "04", "09", "10");
    $recs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosRenovacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,horaoperacion,anorenovacion");
    if ($recs && !empty($recs)) {
        foreach ($recs as $r) {
            if (!isset($salida[$r["anorenovacion"]])) {
                if (ltrim(trim((string) $r["fecharenovacionaplicable"]), "0") != '') {
                    $salida[$r["anorenovacion"]] = (string) $r["fecharenovacionaplicable"];
                } else {
                    $salida[$r["anorenovacion"]] = (string) $r["fecoperacion"];
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

function actualizarDatosRenovacionMysqliApi($dbx = null, $mat = '', $serviciosRenovacion = array(), $serviciosAfiliacion = array(), $serviciosMatricula = array()) {
    $datren = encontrarHistoricoPagosMysqliApi($dbx, $mat, $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
    $datrenafil = encontrarHistoricoPagosAfiliacionMysqliApi($dbx, $mat, $serviciosAfiliacion);
    if ($datren && $datren["fecultren"] != '') {
        $arrCampos = array(
            'fecrenovacion',
            'ultanoren'
        );
        $arrValores = array(
            "'" . $datren["fecultren"] . "'",
            "'" . $datren["ultanoren"] . "'"
        );
        regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $mat . "'");
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
        regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $mat . "'");
    }
    return true;
}

function ejecutarQueryMysqliApi($dbx, $query, $replica = '') {

    //
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if ($replica == '') {
            $dbx = conexionMysqliApi();
        } else {
            $dbx = conexionMysqliApi($replica);
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
function existeTablaMysqliApi($mysqli = null, $tabla = '') {
    if ($mysqli == null) {
        $mysqli = conexionMysqliApi();
        $cerrar = 'si';
    } else {
        $cerrar = 'no';
    }
    //$mysqli = new mysqli("p:".DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
    $result = mysqli_query($mysqli, "select count(*) as count from information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND table_name = '" . $tabla . "'");
    $obj = $result->fetch_object();
    $cant = $obj->count;
    if ($cerrar == 'si') {
        $mysqli->close();
    }
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
function insertarRegistrosMysqliApi($dbx = null, $tabla = '', $arrCampos = array(), $arrValores = array()) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
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

    try {
        $result = mysqli_query($dbx, $query);
        if ($result === false) {
            \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx));
            $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        } else {
            $_SESSION ["generales"] ["lastId"] = mysqli_insert_id($dbx);
            $_SESSION["generales"]["mensajeerror"] = '';
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return true;
        }
    } catch (MySQLDuplicateKeyException $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } catch (MySQLException $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } catch (Exception $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    }

    /*
      $result = mysqli_query($dbx, $query);
      if ($result === false) {
      \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx));
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
     */
}

function insertarRegistrosWithLockMysqliApi($dbx, $tabla, $arrCampos, $arrValores) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
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

    //
    $_SESSION ["generales"] ["lastId"] = 0;
    $query1 = 'LOCK TABLES ' . $tabla . '  WRITE;';
    $result1 = mysqli_query($dbx, $query1);
    if ($result1 === false) {
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query1 . ' - ' . mysqli_error($dbx));
    }

    //
    try {
        $result = mysqli_query($dbx, $query);
        if ($result === false) {
            \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx));
            $_SESSION["generales"]["mensajeerror"] = mysqli_error($dbx);
            $query1 = 'UNLOCK TABLES;';
            $result1 = mysqli_query($dbx, $query1);
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        } else {
            $_SESSION ["generales"] ["lastId"] = mysqli_insert_id($dbx);
            $query1 = 'UNLOCK TABLES;';
            $result1 = mysqli_query($dbx, $query1);
            $_SESSION["generales"]["mensajeerror"] = '';
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return true;
        }
    } catch (MySQLDuplicateKeyException $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        $query1 = 'UNLOCK TABLES;';
        $result1 = mysqli_query($dbx, $query1);
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } catch (MySQLException $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        $query1 = 'UNLOCK TABLES;';
        $result1 = mysqli_query($dbx, $query1);
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } catch (Exception $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        $query1 = 'UNLOCK TABLES;';
        $result1 = mysqli_query($dbx, $query1);
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    }
}

function inactivarSiprefMatriculasMysqliApi($mysqli = null, $mat = '', $fmat = '', $fren = '') {

    // carga tabla actos
    if (!isset($_SESSION["actos"])) {
        $_SESSION["actos"] = array();
        $rets = retornarRegistrosMysqliApi($mysqli, 'mreg_actos', "1=1", "idlibro,idacto");
        foreach ($rets as $r) {
            $ind = $r["idlibro"] . '-' . $r["idacto"];
            $_SESSION["actos"][$ind] = $r;
        }
    }
    unset($rets);

    //
    $return = false;

    //
    if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $fmat) > 1095) {
        if ($fmat != $fren) {
            if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $fren) > 1095) {
                if (busqueInscripcionesMysqliApi($mysqli, $mat, $_SESSION["actos"])) {
                    $return = false;
                } else {
                    $return = true;
                }
            }
        } else {
            if (busqueInscripcionesMysqliApi($mysqli, $mat, $_SESSION["actos"])) {
                $return = false;
            } else {
                $return = true;
            }
        }
    }

    //
    if ($return == true) {
        $reacts = retornarRegistrosMysqliApi($mysqli, "mreg_sipref_controlevidencias", "matricula='" . $mat . "' and tipotramite='reactivacionmatricula'", "matricula,fecha");
        if ($reacts && !empty($reacts)) {
            foreach ($reacts as $rx) {
                if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $rx["fecha"]) < 1096) {
                    $return = false;
                }
            }
        }
    }

    //
    return $return;
}

function insertarCamposHistoricosMysqliApi($dbx, $user, $mat, $cmp, $ori, $nue, $tt, $rec, $cb, $ip) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
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
        "'" . addslashes((string) $ori) . "'",
        "'" . addslashes((string) $nue) . "'",
        "'" . $user . "'",
        "'" . $ip . "'",
        "'" . $tt . "'",
        "'" . $rec . "'"
    );
    insertarRegistrosMysqliApi($dbx, 'mreg_campos_historicos_' . date("Y"), $arrCampos, $arrValores);
}

// *****************************************************************
// Localiza el dato anterior existente en un campo
// Valida en los últimos 5 años
// *****************************************************************
function localizarCampoAnteriorMysqliApi($dbx, $mat, $campo) {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
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
function localizarCampoAnteriorTodosMysqliApi($dbx, $mat, $campo) {

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

/**
 * 
 * @param type $dbx
 * @param type $mat
 * @param type $acto
 * @param type $tipofecha
 * @return type
 */
function localizarFechaActoMysqliApi($dbx, $mat, $acto, $tipofecha = 'I') {
    $salida = '';
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }
    $res = retornarRegistrosMysqliApi($dbx, 'mreg_est_inscripciones', "matricula='" . $mat . "' and acto='" . $acto . "'", "fecharegistro");
    if ($res && !empty($res)) {
        foreach ($res as $r) {
            if ($tipofecha == 'I') {
                $salida = $r["fecharegistro"];
            } else {
                $salida = $r["fecharadicacion"];
            }
        }
    }
    if ($cerrarMysqli == 'si') {
        $dbx->close();
    }
    return $salida;
}

function retornarCargoUsuarioMysqliApi($dbx, $usua = '') {
    $idcargo = retornarRegistroMysqliApi($dbx, 'usuarios', "idusuario='" . $usua . "'", "idcargo");
    if ($idcargo == '') {
        return "";
    }
    return retornarRegistroMysqliApi($dbx, 'cargos', "idcargo='" . $idcargo . "'", "nombre");
}

// *****************************************************************
// Retorna una clave valor
// *****************************************************************
function retornarClaveValorMysqliApi($dbx, $clave) {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
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

/**
 * 
 * @param type $mysqli
 * @param type $periodo
 * @param type $tipodoc
 * @param type $estado
 * @param type $fecha
 * @param type $crear
 * @param type $modulo
 * @param type $identificacion
 * @param type $nombre
 * @return boolean|string
 */
function retornarConsecutivoTipoDocMysqliApi($mysqli, $periodo, $tipodoc = '', $estado = '', $fecha = '', $crear = 'N', $modulo = '', $identificacion = '', $nombre = '') {
    $arrTipoDoc = retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "idtipodoc='" . $tipodoc . "'");
    if ($arrTipoDoc === false) {
        $_SESSION["generales"]["mensajeerror"] .= ' (bas_tipodoc - false - ' . $tipodoc . ')';
        return false;
    }
    if (empty($arrTipoDoc)) {
        $_SESSION["generales"]["mensajeerror"] .= ' (bas_tipodoc - empty - ' . $tipodoc . ')';
        return false;
    }

    //
    if ($arrTipoDoc["numeracion"] == null) {
        $arrTipoDoc["numeracion"] = '1';
    }
    if ($arrTipoDoc["numeracion"] == '') {
        $arrTipoDoc["numeracion"] = '1';
    }

    // asigna la raíz a buscar en secuencias dependiendo si el documento maneja consecutivo &uacute;nico, anual o mensual
    switch ($arrTipoDoc["numeracion"]) {
        case "1":
            $busq = (string) $tipodoc;
            $raiz = '';
            $tam = 0;
            break;
        case "2":
            $busq = $tipodoc . '-' . $periodo;
            $raiz = $periodo;
            $tam = 4;
            break;
        case "3":
            if (trim((string) $fecha) == '') {
                return false;
            }
            $busq = $tipodoc . '-' . $periodo . '-' . (string) substr($fecha, 4, 2);
            $raiz = $periodo . substr($fecha, 4, 2);
            $tam = 6;
            break;
    }
    if (trim($busq) == '') {
        $_SESSION["generales"]["mensajeerror"] = 'No se identificó el consecutivo a buscae';
        return false;
    }

    // Buscar la secuencia que sigue en la tabla de documentos, de acuerdo con el tipo de documento y su numeracion
    // CONVERT(value USING charset_of_table)
    $arrCon = retornarRegistroMysqliApi($mysqli, 'secuencias', "tipo='" . (string) $busq . "'", '*', 'P');
    if ($arrCon === false) {
        $_SESSION["generales"]["mensajeerror"] .= ' (bas_tipodoc)';
        return false;
    }

    //
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
                case 0: $num = $sec;
                    break;
                case 4: $num = $raiz . sprintf("%05s", $sec);
                    break;
                case 6: $num = $raiz . sprintf("%03s", $sec);
                    break;
            }
            if (contarRegistrosMysqliApi($mysqli, 'documentos', "ano='" . $periodo . "' and idtipodoc='" . $tipodoc . "' and numdoc='" . $num . "'") == 0) {
                $grabado = 'si';
            }
        }
    } else {
        $sec++;
        switch ($tam) {
            case 0: $num = $sec;
                break;
            case 4: $num = $raiz . sprintf("%05s", $sec);
                break;
            case 6: $num = $raiz . sprintf("%03s", $sec);
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
    if ($crear == 'S') {
        actualizarLogDocumentosMysqliApi($mysqli, $periodo, $tipodoc, $num, date("Ymd"), date("His"), 'creacion', $_SESSION["generales"]["codigousuario"]);
    }

    // Actualiza el log
    if ($crear == 'S') {
        actualizarLogMysqliApi($mysqli, '002', $_SESSION["generales"]["codigousuario"], '', $tipodoc, '', $num, 'Documento creado');
    }
    return $num;
}

// ************************************************************************************************* //
// Rutina maestra para insercion de registros
// ************************************************************************************************* //
function insertarRegistrosBloqueMysqliApi($dbx, $tabla, $arrCampos, $arrValores) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
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
function regrabarRegistrosMysqliApi($dbx, $tabla, $arrCampos, $arrValores, $condicion) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        if (!defined('DB_PERSISTENCY') || DB_PERSISTENCY == '' || DB_PERSISTENCY == 'SI') {
            $dbx = new mysqli("p:" . DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        } else {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        }
        $cerrarMysqli = 'si';
    }

    $i = 0;
    $query = "update " . $tabla . " set ";
    foreach ($arrCampos as $c) {
        $i++;
        if ($i != 1) {
            $query .= ",";
        }
        $query .= $c . '=' . ($arrValores [$i - 1]);
    }
    $query .= " where " . $condicion;
    $dbx->set_charset("utf8");

    //
    try {
        $result = mysqli_query($dbx, $query);
        if ($result === false) {
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
    } catch (MySQLDuplicateKeyException $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } catch (MySQLException $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
    } catch (Exception $e) {
        $e->getMessage();
        \logApi::general2('api_sentencias_error_' . date("Ymd"), '', $query . ' - ' . mysqli_error($dbx) . ' - ' . $e->getMessage());
        $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return false;
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
function retornarRegistrosMysqliApi($dbx, $tabla, $condicion, $orden = '', $campos = '*', $offset = 0, $limit = 0) {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
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
 * @param type $tabla
 * @param type $condicion
 * @param type $campos
 * @param type $tip
 * @return boolean|string
 */
function retornarRegistroMysqliApi($dbx = null, $tabla = '', $condicion = '', $campos = '*', $tip = 'P', $charset = 'utf8') {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
        $cerrarMysqli = 'si';
    }

    $uncampo = 'no';
    if ($campos != '*') {
        if (strpos($campos, ',') === false) {
            $uncampo = 'si';
        }
    }
    $dbx->set_charset($charset);
    if ($campos == '*') {
        $query = "SELECT * from " . $tabla . " where " . $condicion;
        try {
            $result = $dbx->query($query);
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }
    } else {
        $query = "SELECT " . $campos . " from " . $tabla . " where " . $condicion;
        try {
            $result = $dbx->query($query);
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = $e->getMessage();
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }
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

//
function retornarSelectTablaSirepMysqliApi($dbx, $tab, $id, $orden = 'idcodigo') {
    $reg = retornarRegistroMysqliApi($dbx, 'mreg_tablassirep', "idtabla='" . $tab . "'", $orden);
    $retornar = '';
    if (trim($id == '')) {
        $retornar .= '<option value="" selected>Seleccione ...</option>';
    } else {
        $retornar .= '<option value="">Seleccione ...</option>';
    }
    if ($reg === false || empty($reg)) {
        $retornar = false;
    } else {
        foreach ($reg as $res) {
            if ($res["idcodigo"] == $id) {
                $retornar .= '<option value=' . $res["idcodigo"] . ' selected>' . substr($res["descripcion"], 0, 30) . ' (' . $res["idcodigo"] . ')</option>';
            } else {
                $retornar .= '<option value=' . $res["idcodigo"] . '>' . substr($res["descripcion"], 0, 30) . ' (' . $res["idcodigo"] . ')</option>';
            }
        }
        $_SESSION["generales"]["mensajeerror"] = '';
    }
    unset($reg);
    unset($res);
    return $retornar;
}

function retornarSelectTablaBasicaMysqliApi($dbx, $tab, $id, $orden = 'id', $txtValor = "id", $txtTexto = "descripcion", $condicion = '1=1') {

    $result = retornarRegistrosMysqliApi($dbx, $tab, $condicion, $orden);

    $retornar = '';
    if (trim($id) == '') {
        $retornar .= "<option value='' selected>Seleccione ...</option>";
    } else {
        if ($id == 0) {
            $retornar .= "<option value=0 selected>Seleccione ...</option>";
        } else {
            $retornar .= "<option value=''>Seleccione ...</option>";
        }
    }

    foreach ($result as $res) {
        if ($res[$txtValor] == $id) {
            $retornar .= "<option value='" . $res[$txtValor] . "' selected>" . $res[$txtValor] . ' - ' . substr($res[$txtTexto], 0, 45) . "</option>";
        } else {
            $retornar .= "<option value='" . $res[$txtValor] . "'>" . $res[$txtValor] . ' - ' . substr($res[$txtTexto], 0, 45) . "</option>";
        }
    }

    return $retornar;
}

//
function retornarTablaBasicaPorCodigoMysqliApi($dbx, $tab, $id) {
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

function retornarSelectBarriosMysqliApi($dbx, $idmun, $idbar) {
    $retornar = '';
    if (trim($idbar) == '') {
        $retornar .= "<option value='' selected>Seleccione un barrio</option>";
    } else {
        $retornar .= "<option value=''>Seleccione un barrio</option>";
    }
    $query = "select * from mreg_barriosmuni order by idmunicipio, nombre";
    $result = ejecutarQueryMysqliApi($dbx, $query);
    foreach ($result as $res) {
        $mun = retornarNombreMunicipioMysqliApi($dbx, $res["idmunicipio"]);
        if (($res["idmunicipio"] == $idmun) && ($res["idbarrio"] == $idbar)) {
            $retornar .= '<option value=' . $res["idbarrio"] . ' selected>' . substr($mun . ' - ' . $res["nombre"], 0, 40) . '</option>';
        } else {
            $retornar .= '<option value=' . $res["idbarrio"] . '>' . substr($mun . ' - ' . $res["nombre"], 0, 40) . '</option>';
        }
    }
    return $retornar;
}

function retornarSelectBarriosMunicipioMysqliApi($dbx, $idmun, $idbar) {
    require_once ('../../librerias/funciones/persistencia.php');
    //
    if (trim($idbar) == '') {
        $retornar = "<option value='' selected>Seleccione un barrio</option>";
    } else {
        $retornar = "<option value=''>Seleccione un barrio</option>";
    }
    if (trim($idmun) != '') {
        $reg = retornarRegistrosMysqliApi($dbx, 'mreg_barriosmuni', "idmunicipio='" . $idmun . "'", "nombre");
    } else {
        $reg = retornarRegistrosMysqliApi($dbx, 'mreg_barriosmuni', "1=1", "nombre");
    }
    if ($reg) {
        foreach ($reg as $res) {
            if ($res["idbarrio"] == $idbar) {
                $retornar .= '<option value=' . $res["idbarrio"] . ' selected>' . substr($res["nombre"], 0, 70) . '</option>';
            } else {
                $retornar .= '<option value=' . $res["idbarrio"] . '>' . substr($res["nombre"], 0, 70) . '</option>';
            }
        }
        $_SESSION["generales"]["mensajeerror"] = '';
    }

    //
    unset($result);
    unset($res);
    return $retornar;
}

function retornarSelectCargosMysqliApi($dbx, $id) {
    $retornar = '';
    if (trim($id) == 0) {
        $retornar .= "<option value=0 selected>Seleccione un cargo</option>";
    } else {
        $retornar .= "<option value=0>Seleccione un cargo</option>";
    }
    $query = "select * from cargos order by idcargo";
    $result = ejecutarQueryMysqliApi($dbx, $query);
    foreach ($result as $res) {
        if ($res["id"] == $id) {
            $retornar .= '<option value=' . $res["id"] . ' selected>' . $res["idcargo"] . ' - ' . \funcionesGenerales::utf8_decode($res["nombre"]) . '</option>';
        } else {
            $retornar .= '<option value=' . $res["id"] . '>' . $res["idcargo"] . ' - ' . \funcionesGenerales::utf8_decode($res["nombre"]) . '</option>';
        }
    }
    return $retornar;
}

function retornarSelectCcosMysqliApi($dbx, $id) {
    if (trim($id) == '') {
        $retornar = "<option value='' selected>Seleccione un centro de costos</option>";
    } else {
        $retornar = "<option value=''>Seleccione un centro de costos</option>";
    }
    $query = "select * from ccos where ano='" . $_SESSION["generales"]["periodo"] . "' and eliminado<>'SI' order by idccos";
    $result = ejecutarQueryMysqliApi($dbx, $query);
    if ($result === false) {
        $retornar = false;
    } else {
        foreach ($result as $res) {
            if ($res["idccos"] == $id) {
                $retornar .= '<option value=' . $res["idccos"] . ' selected>' . $res["idccos"] . ' - ' . \funcionesGenerales::utf8_decode($res["nombre"]) . '</option>';
            } else {
                $retornar .= '<option value=' . $res["idccos"] . '>' . $res["idccos"] . ' - ' . \funcionesGenerales::utf8_decode($res["nombre"]) . '</option>';
            }
        }
        $_SESSION ["generales"] ["mensajeerror"] = '';
    }
    unset($result);
    unset($res);
    return $retornar;
}

function retornarSelectSedesMysqliApi($dbx, $id) {
    $retorno = '';
    $query = "select * from mreg_sedes where 1=1 order by id";
    if (trim($id) == '') {
        $retorno .= "<option value='' selected>Seleccione ...</option>" . chr(13);
    } else {
        $retorno .= "<option value=''>Seleccione ...</option>" . chr(13);
    }
    $result = ejecutarQueryMysqliApi($dbx, $query);
    if ($result === false) {
        $retorno = "<option value=''>Error recuperando lista de sedes</option>" . chr(13);
    }
    foreach ($result as $res) {
        if ($res["id"] == $id) {
            $retorno .= "<option value='" . $res["id"] . "' selected>" . \funcionesGenerales::utf8_decode($res["descripcion"]) . "</option>" . chr(13);
        } else {
            $retorno .= "<option value='" . $res["id"] . "'>" . \funcionesGenerales::utf8_decode($res["descripcion"]) . "</option>" . chr(13);
        }
    }

    return $retorno;
}

function retornarSelectMunicipiosMysqliApi($dbx, $id) {
    $query = "select * from bas_municipios order by ciudad";
    $retorno = '';
    if (trim($id) == '') {
        $retorno .= "<option value='' selected>Seleccione ...</option>" . chr(13);
    } else {
        $retorno .= "<option value=''>Seleccione ...</option>" . chr(13);
    }
    $result = ejecutarQueryMysqliApi($dbx, $query);
    if ($result === false) {
        $retorno = "<option value=''>Error recuperando municipios ...</option>" . chr(13);
    }
    foreach ($result as $res) {
        if ($res["codigomunicipio"] == $id) {
            $retorno .= "<option value='" . $res["codigomunicipio"] . "' selected>" . utf8decode_sii($res["ciudad"]) . ' (' . substr($res["departamento"], 0, 3) . ')' . "</option>" . chr(13);
        } else {
            $retorno .= "<option value='" . $res["codigomunicipio"] . "'>" . utf8decode_sii($res["ciudad"]) . ' (' . substr($res["departamento"], 0, 3) . ')' . "</option>" . chr(13);
        }
    }
    return $retorno;
}

function retornarSelectMunicipiosJurisdiccionMysqliApi($dbx, $id) {
    if (trim($id) == '') {
        $retornar = "<option value='' selected>Seleccione un municipio</option>";
    } else {
        $retornar = "<option value=''>Seleccione un municipio</option>";
    }
    //
    $query = "SELECT mreg_municipiosjurisdiccion.idcodigo AS muni_codigo, ";
    $query .= "bas_municipios.ciudad AS muni_nombre ";
    $query .= "FROM mreg_municipiosjurisdiccion, bas_municipios ";
    $query .= "WHERE mreg_municipiosjurisdiccion.idcodigo = bas_municipios.codigomunicipio ";
    $query .= "ORDER BY muni_nombre ";
    $result = ejecutarQueryMysqliApi($dbx, $query);
    //
    if ($result === false) {
        $retornar = false;
    } else {
        foreach ($result as $res) {
            if ($res["muni_codigo"] == $id) {
                $retornar .= '<option value=' . $res["muni_codigo"] . ' selected>' . $res["muni_nombre"] . '</option>';
            } else {
                $retornar .= '<option value=' . $res["muni_codigo"] . '>' . $res["muni_nombre"] . '</option>';
            }
        }
        $_SESSION["generales"]["mensajeerror"] = '';
    }
    //
    unset($result);
    unset($res);
    return $retornar;
    exit();
}

function retornarSelectPaisesMysqliApi($dbx, $id) {
    $query = "select * from bas_paises where codnumpais > '' order by nombrepais";
    $retorno = '';
    if (trim($id) == '') {
        $retorno .= "<option value='' selected>Seleccione ...</option>" . chr(13);
    } else {
        $retorno .= "<option value=''>Seleccione ...</option>" . chr(13);
    }
    $result = ejecutarQueryMysqliApi($dbx, $query);
    if ($result === false) {
        $retorno = "<option value=''>Error recuperando paises ...</option>" . chr(13);
    }
    foreach ($result as $res) {
        if ($res["codnumpais"] == '169') {
            if ($res["idpais"] == $id || $res["codnumpais"] == $id) {
                $retorno .= "<option value='" . $res["idpais"] . "' selected>" . utf8decode_sii($res["nombrepais"]) . "</option>" . chr(13);
            } else {
                $retorno .= "<option value='" . $res["idpais"] . "'>" . utf8decode_sii($res["nombrepais"]) . "</option>" . chr(13);
            }
        }
    }
    foreach ($result as $res) {
        if ($res["codnumpais"] != '169') {
            if ($res["idpais"] == $id || $res["codnumpais"] == $id) {
                $retorno .= "<option value='" . $res["idpais"] . "' selected>" . utf8decode_sii($res["nombrepais"]) . "</option>" . chr(13);
            } else {
                $retorno .= "<option value='" . $res["idpais"] . "'>" . utf8decode_sii($res["nombrepais"]) . "</option>" . chr(13);
            }
        }
    }

    return $retorno;
}

function retornarSelectPaisesCodigoNumericoMysqliApi($dbx, $id) {
    $query = "select * from bas_paises where codnumpais > '' order by nombrepais";
    $retorno = '';
    if (trim($id) == '') {
        $retorno .= "<option value='' selected>Seleccione ...</option>" . chr(13);
    } else {
        $retorno .= "<option value=''>Seleccione ...</option>" . chr(13);
    }
    $result = ejecutarQueryMysqliApi($dbx, $query);
    if ($result === false) {
        $retorno = "<option value=''>Error recuperando paises ...</option>" . chr(13);
    }
    foreach ($result as $res) {
        if ($res["idpais"] == $id || $res["codnumpais"] == $id) {
            $retorno .= "<option value='" . $res["codnumpais"] . "' selected>" . utf8decode_sii($res["nombrepais"]) . "</option>" . chr(13);
        } else {
            $retorno .= "<option value='" . $res["codnumpais"] . "'>" . utf8decode_sii($res["nombrepais"]) . "</option>" . chr(13);
        }
    }
    return $retorno;
}

function retornarTipoRegistroMysqliApi($dbx, $tra) {
    return retornarRegistroMysqliApi($dbx, 'bas_tipotramites', "id='" . $tra . "'", "tiporegistro");
}

function retornarDescripcionCiiuMysqliApi($dbx, $id, $tipociiu = '') {
    if ($tipociiu == '3.1') {
        return retornarRegistroMysqliApi($dbx, "bas_ciius_3_1", "idciiu='" . $id . "'", "descripcion");
    } else {
        return retornarRegistroMysqliApi($dbx, "bas_ciius", "idciiu='" . $id . "'", "descripcion");
    }
}

function retornarListaOpcionesMysqliApi($dbx) {
    $excluir = '';
    if ($_SESSION["generales"]["tipomenu"] != 'MOVIL') {
        $excluir = " and substring(idopcion,1,5) <> '00.00' ";
    }

    $arreglo = array();
    $query = '';
    if ((trim($_SESSION ["generales"] ["codigousuario"]) == '') || $_SESSION ["generales"] ["codigousuario"] == 'USUPUBXX' || $_SESSION ["generales"] ["tipousuario"] == '00') {
        $query = "tipousuariopublico='X' and estado='1' and mostrarmenuaplicacion = 'S' " . $excluir;
    } else {
        if (substr($_SESSION ["generales"] ["tipousuario"], 0, 2) == '01') {
            $query = "'1=1'  " . $excluir;
        } else {
            if (substr($_SESSION ["generales"] ["tipousuario"], 0, 2) == '02') {
                $query = "tipousuarioadministrativo='X' and estado='1' and mostrarmenuaplicacion = 'S'  " . $excluir;
            } else {
                if (substr($_SESSION ["generales"] ["tipousuario"], 0, 2) == '03') {
                    $query = "tipousuarioproduccion='X' and estado='1'  and mostrarmenuaplicacion = 'S'  " . $excluir;
                } else {
                    if (substr($_SESSION ["generales"] ["tipousuario"], 0, 2) == '04') {
                        $query = "tipousuarioventas='X' and estado='1' and mostrarmenuaplicacion = 'S'  " . $excluir;
                    } else {
                        if (substr($_SESSION ["generales"] ["tipousuario"], 0, 2) == '05') {
                            $query = "tipousuarioregistro='X' and estado='1' and mostrarmenuaplicacion = 'S'  " . $excluir;
                        } else {
                            if (substr($_SESSION ["generales"] ["tipousuario"], 0, 2) == '06') {
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
        $i = - 1;
        if ($result) {
            foreach ($result as $res) {
                if ($res["estado"] == '1') {
                    $i++;
                    $arreglo [$i] ["idopcion"] = $res["idopcion"];
                    $arreglo [$i] ["nombre"] = str_replace("<br>", " ", $res["nombre"]);
                    $arreglo [$i] ["tipo"] = $res["idtipoopcion"];
                    $arreglo [$i] ["icono"] = $res["icono"];
                    $arreglo [$i] ["tooltip"] = $res["tooltip"];
                    $arreglo [$i] ["script"] = $res["script"];
                    $arreglo [$i] ["mostrarbootstrap"] = '';
                    $arreglo [$i] ["clasephp"] = '';
                    $arreglo [$i] ["metodophp"] = '';
                    $arreglo [$i] ["parametrosphp"] = '';
                    if (isset($res["mostrarbootstrap"])) {
                        $arreglo [$i] ["mostrarbootstrap"] = $res["mostrarbootstrap"];
                    }
                    if (isset($res["clasephp"])) {
                        $arreglo [$i] ["clasephp"] = $res["clasephp"];
                    }
                    if (isset($res["metodophp"])) {
                        $arreglo [$i] ["metodophp"] = $res["metodophp"];
                    }
                    if (isset($res["parametrosphp"])) {
                        $arreglo [$i] ["parametrosphp"] = $res["parametrosphp"];
                    }
                    $arreglo [$i] ["enlace"] = $res["enlace"];
                    $arreglo [$i] ["destino"] = $res["destino"];
                    $arreglo [$i] ["estado"] = $res["estado"];
                    $arreglo [$i] ["tipoempresa"] = $res["tipoempresa"];
                    $arreglo [$i] ["cantidad"] = 0;
                    $arreglo [$i] ["ejecutar"] = 0;
                }
            }
        } else {
            $arreglo = false;
        }
    }
    return $arreglo;
}

function retornarNombreDptoMysqliApi($dbx, $id) {
    return retornarRegistroMysqliApi($dbx, "bas_municipios", "codigomunicipio='" . $id . "'", "departamento");
}

function retornarNombreIdentificacionMysqliApi($dbx, $ide) {
    if (ltrim($ide, "0") == '') {
        return "";
    }
    return retornarRegistroMysqliApi($dbx, "identificaciones", "identificacion='" . $ide . "'", "razonsocial");
}

// 2020 01 22 - JINT - Se descomentarea la línea del utf8
function retornarNombreMunicipioMysqliApi($dbx = null, $id = '', $forma = 'M') {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
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

function retornarNombrePaisMysqliApi($dbx, $id) {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
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

function retornarNombreCamaraMysqliApi($dbx = null, $id = null) {
    $retorno = false;
    $result = retornarRegistroMysqliApi($dbx, 'bas_camaras', "id='" . $id . "'");
    if ($result && !empty($result)) {
        $retorno = $result["nombre"];
    }
    unset($result);
    return $retorno;
}

function retornarNombreBarrioMysqliApi($dbx, $mun = '', $id = '') {
    $retorno = '';
    $result = retornarRegistroMysqliApi($dbx, 'mreg_barriosmuni', "idmunicipio='" . $mun . "' and idbarrio='" . $id . "'");
    if ($result && !empty($result)) {
        $retorno = $result["nombre"];
    }
    unset($result);
    return $retorno;
}

function retornarNombreTablaBasicaMysqliApi($dbx, $tabla, $id) {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
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

function retornarNombreTablasSirepMysqliApi($dbx, $idtabla, $idcodigo = '') {
    return retornarRegistroMysqliApi($dbx, 'mreg_tablassirep', "idtabla='" . $idtabla . "' and idcodigo='" . $idcodigo . "'", "descripcion");
}

function retornarNombreTipoDocumentalMysqliApi($dbx, $id) {
    $txt = retornarRegistroMysqliApi($dbx, 'bas_tipodoc', "idtipodoc='" . $id . "'", "nombre");
    if ($txt == '') {
        return false;
    }
    $retornar = str_replace("--- ", "", $txt);
    $retornar = str_replace("---", "", $retornar);
    return $retornar;
}

function retornarNombreUsuarioMysqliApi($dbx, $usua) {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
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

function retornarNombreUsuarioSirepMysqliApi($dbx, $usua = '') {
    $cerrarMysqli = 'no';
    if ($dbx === null) {
        $dbx = conexionMysqliApi();
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

function retornarNombreActosRegistroMysqliApi($dbx, $idlibro, $idacto = '') {
    return retornarRegistroMysqliApi($dbx, 'mreg_actos', "idlibro='" . $idlibro . "' and idacto='" . $idacto . "'", "nombre");
}

function retornarNombreActosProponentesMysqliApi($dbx, $idacto = '') {
    return retornarRegistroMysqliApi($dbx, 'mreg_actosproponente', "id='" . $idacto . "'", "descripcion");
}

function retornarPantallaPredisenadaMysqliApi($dbx, $pantalla = '', $fuente = '') {
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

function retornarSecuenciaMysqliApi($dbx, $sec = '') {

    $res = retornarRegistroMysqliApi($dbx, 'secuencias', "tipo='" . $sec . "'");
    if ($res === false) {
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
            if (contarRegistrosMysqliApi($dbx, 'mreg_multas_ponal', "idliquidacion=" . $retornar) > 0) {
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
            if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "idliquidacion=" . $retornar) > 0) {
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
                insertarRegistrosMysqliApi($dbx, 'mreg_liquidacion', $arrCampos, $arrValores);
                $ok = 'si';
            }
        }
    }

    if ($sec == 'DEVOLUCION-REGISTROS') {
        $ok = 'no';
        while ($ok == 'no') {
            if (contarRegistrosMysqliApi($dbx, 'mreg_devoluciones_nueva', "iddevolucion=" . $retornar) > 0) {
                $retornar++;
            } else {
                $ok = 'si';
            }
        }
    }

    if ($sec == 'RADICACION-REPORTES-EE') {
        $ok = 'no';
        while ($ok == 'no') {
            if (contarRegistrosMysqliApi($dbx, 'mreg_reportesradicados', "idradicacion='" . ltrim($retornar, "0") . "'") > 0) {
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
        insertarRegistrosMysqliApi($dbx, 'secuencias', $arrCampos, $arrValores);
    } else {
        $arrCampos = array(
            'consecutivo'
        );
        $arrValores = array(
            $retornar
        );
        regrabarRegistrosMysqliApi($dbx, 'secuencias', $arrCampos, $arrValores, "tipo='" . $sec . "'");
    }

    //
    return $retornar;
}

function retornarSalarioMinimoActualMysqliApi($dbx, $ano = '') {
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

?>