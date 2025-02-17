<?php

/*
 * Se recibe json con la siguiente información
 * 
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait retornarSoportesLiquidacion {


    public function retornarSoportesLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['titulo'] = '';
        $_SESSION['jsonsalida']['expedientes'] = array();

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        //
        $api->validarParametro("idliquidacion", true, true);
        $api->validarParametro("tiposoporte", false);

        //
        if ($_SESSION["entrada"]["tiposoporte"] != 'ponal-pot' &&
                $_SESSION["entrada"]["tiposoporte"] != 'ponal-multas' &&
                $_SESSION["entrada"]["tiposoporte"] != 'ley1780' &&
                $_SESSION["entrada"]["tiposoporte"] != 'balance') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Tipo de soporte solicitado incorrecto';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (!$api->validarToken('retornarSoportesLiquidacion ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["tiposoporte"] === 'ponal-pot') {
            if ($_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No corresponde a un comerciante que requiera soportes de uso de suelos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["titulo"] = 'SOPORTES CODIGO DE POLICIA - ACTIVIDADES DE ALTO IMPACTO';
            foreach ($_SESSION["tramite"]["expedientes"] as $ex) {
                if ($ex["registrobase"] == 'S') {
                    if ($ex["controlpot"] == 'S') {
                        $expediente = array();
                        $expediente["expediente"] = $ex["matricula"];
                        $expediente["nombre"] = mb_strtoupper($ex["razonsocial"], 'utf-8');
                        $expediente["soportes"] = array();
                        $soporte = array();
                        $soporte["identificador"] = 'regmer-esadl-ponal-pot';
                        $soporte["descripcion"] = 'Certificación uso de suelos (POT autorizado)';
                        $soporte["observaciones"] = retornarRegistroMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "identificador='regmer-esadl-ponal-pot'", "observaciones");
                        $soporte["idtipodoc"] = retornarRegistroMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "tipotramitegenerico='soporteponalaltoimpacto' and identificador='regmer-esadl-ponal-pot'", "idtipodoc");
                        $soporte["documentos"] = array();
                        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and identificador='regmer-esadl-ponal-pot' and expediente='" . $ex["matricula"] . "' and eliminado<>'SI'", "idanexo");
                        if ($temx && !empty($temx)) {
                            foreach ($temx as $tx) {
                                $dcto = array();
                                $dcto["idanexo"] = $tx["idanexo"];
                                $dcto["observaciones"] = $tx["observaciones"];
                                $dcto["idtipodoc"] = $tx["idtipodoc"];
                                $pathAnexo = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["entrada"]["codigoempresa"] . '/' . $tx["path"] . $tx["idanexo"] . '.' . $tx["tipoarchivo"];
                                $dcto["link"] = $pathAnexo;
                                $soporte["documentos"][] = $dcto;
                            }
                        }
                        $expediente["soportes"][] = $soporte;
                        $_SESSION["jsonsalida"]["expedientes"][] = $expediente;
                    }
                }
            }
        }

        if ($_SESSION["entrada"]["tiposoporte"] === 'ponal-multas') {
            if ($_SESSION["tramite"]["multadoponal"] != 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No corresponde a un comerciante que requiera soportes de pago de multas';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["titulo"] = 'SOPORTES CODIGO DE POLICIA - MULTAS VENCIDAS';
            foreach ($_SESSION["tramite"]["expedientes"] as $ex) {
                if ($ex["organizacion"] == '01') {
                    if ($ex["registrobase"] == 'S') {
                        $expediente = array();
                        $expediente["expediente"] = $ex["matricula"];
                        $expediente["nombre"] = mb_strtoupper($ex["razonsocial"], 'utf-8');
                        $expediente["soportes"] = array();
                        $soporte = array();
                        $soporte["identificador"] = 'regmer-esadl-ponal-pagomultas';
                        $soporte["descripcion"] = 'Soporte del pago de multas por incumplimiento del código de policía';
                        $soporte["observaciones"] = retornarRegistroMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "identificador='regmer-esadl-ponal-pagomultas'", "observaciones");
                        $soporte["idtipodoc"] = retornarRegistroMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "tipotramitegenerico='soporteponalmultas' and identificador='regmer-esadl-ponal-pagomultas'", "idtipodoc");
                        $soporte["documentos"] = array();
                        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and identificador='regmer-esadl-ponal-pagomultas' and expediente='" . $ex["matricula"] . "'  and eliminado<>'SI'", "idanexo");
                        if ($temx && !empty($temx)) {
                            foreach ($temx as $tx) {
                                $dcto = array();
                                $dcto["idanexo"] = $tx["idanexo"];
                                $dcto["observaciones"] = $tx["observaciones"];
                                $dcto["idtipodoc"] = $tx["idtipodoc"];
                                $pathAnexo = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["entrada"]["codigoempresa"] . '/' . $tx["path"] . $tx["idanexo"] . '.' . $tx["tipoarchivo"];
                                $dcto["link"] = $pathAnexo;
                                $soporte["documentos"][] = $dcto;
                            }
                        }
                        $expediente["soportes"][] = $soporte;
                        $_SESSION["jsonsalida"]["expedientes"][] = $expediente;
                    }
                }
            }
        }

        if ($_SESSION["entrada"]["tiposoporte"] === 'ley1780') {
            if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["renunciobeneficiosley1780"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'no accedera a los beneficios de Ley 1780, no requiere soportes';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["titulo"] = 'SOPORTES PARA ACCEDER A LOS BENEFICIOS DE LA LEY 1780';
            foreach ($_SESSION["tramite"]["expedientes"] as $ex) {
                if ($ex["registrobase"] == 'S') {
                    if ($ex["organizacion"] != '02' && $ex["categoria"] != '2' && $ex["categoria"] != '3') {
                        $expediente = array();
                        $expediente["expediente"] = $ex["matricula"];
                        $expediente["nombre"] = mb_strtoupper($ex["razonsocial"], 'utf-8');
                        $expediente["soportes"] = array();
                        $tems = retornarRegistrosMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "tipotramitegenerico='renovacionmatricula'", "orden,identificador");
                        if ($tems && !empty($tems)) {
                            foreach ($tems as $ts) {
                                $soporte = array();
                                $soporte["identificador"] = $ts["identificador"];
                                $soporte["descripcion"] = $ts["descripcion"];
                                $soporte["observaciones"] = $ts["observaciones"];
                                $soporte["idtipodoc"] = $ts["idtipodoc"];
                                $soporte["documentos"] = array();
                                $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and identificador='" . $ts["identificador"] . "'  and eliminado<>'SI'", "idanexo");


                                if ($temx && !empty($temx)) {
                                    foreach ($temx as $tx) {
                                        $dcto = array();
                                        $dcto["idanexo"] = $tx["idanexo"];
                                        $dcto["observaciones"] = $tx["observaciones"];
                                        $dcto["idtipodoc"] = $tx["idtipodoc"];
                                        $pathAnexo = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["entrada"]["codigoempresa"] . '/' . $tx["path"] . $tx["idanexo"] . '.' . $tx["tipoarchivo"];
                                        $dcto["link"] = $pathAnexo;
                                        $soporte["documentos"][] = $dcto;
                                    }
                                }

                                $expediente["soportes"][] = $soporte;
                            }
                        }
                        $_SESSION["jsonsalida"]["expedientes"][] = $expediente;
                    }
                }
            }
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    
}
