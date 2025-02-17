<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait relacionMovimientos {

    /**
     * 
     * @param API $api
     * Permite seleccionar movimientos desde el archivo de caja entre dos fechas determinadas
     * Parámetros de entrada
     * - fechainicial
     * - fechafinal
     * - tipo: Puede ser
     *                  MAT.- Matrículados
     *                  REN.- Renovados
     *                  MUT.- Mutaciones
     *                  DOC.- Documentos
     *                  CAN.- Solicitudes de cancelación
     *                  CER.- Certificados
     *                  OTR.- Otras transacciones
     *                  RUEREC.- Transacciones RUES como receptoras
     *                  RUERES.- Transacciones RUES como responsables
     */
    public function relacionMovimientos(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["fechainicial"] = '';
        $_SESSION["jsonsalida"]["fechafinal"] = '';
        $_SESSION["jsonsalida"]["tipo"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("fechainicial", true);
        $api->validarParametro("fechafinal", true);
        $api->validarParametro("tipo", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('relacionMovimientos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // Validar fechas
        if ($_SESSION["entrada"]["fechainicial"] > $_SESSION["entrada"]["fechafinal"]) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Rango de fechas incorrecto';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechainicial"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha inicial incorrecta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (!\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechafinal"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha final incorrecta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $servs = '';

        //
        if ($_SESSION["entrada"]["tipo"] == 'MAT') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1");
            foreach ($temx as $tx) {
                if ($tx["tipoingreso"] == '02' || $tx["tipoingreso"] == '12') {
                    if ($servs != '') {
                        $servs .= ',';
                    }
                    $servs .= "'" . $tx["idservicio"] . "'";
                }
            }
            unset($temx);
        }

        //
        if ($_SESSION["entrada"]["tipo"] == 'REN') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1");
            foreach ($temx as $tx) {
                if ($tx["tipoingreso"] == '03' || $tx["tipoingreso"] == '13') {
                    if ($servs != '') {
                        $servs .= ',';
                    }
                    $servs .= "'" . $tx["idservicio"] . "'";
                }
            }
            unset($temx);
        }

        //
        if ($_SESSION["entrada"]["tipo"] == 'MUT') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1");
            foreach ($temx as $tx) {
                if ($tx["tipoingreso"] == '07' || $tx["tipoingreso"] == '17') {
                    if ($servs != '') {
                        $servs .= ',';
                    }
                    $servs .= "'" . $tx["idservicio"] . "'";
                }
            }
            unset($temx);
        }

        //
        if ($_SESSION["entrada"]["tipo"] == 'DOC') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1");
            foreach ($temx as $tx) {
                if ($tx["tipoingreso"] == '05' || $tx["tipoingreso"] == '15') {
                    if ($servs != '') {
                        $servs .= ',';
                    }
                    $servs .= "'" . $tx["idservicio"] . "'";
                }
            }
            unset($temx);
        }

        //
        if ($_SESSION["entrada"]["tipo"] == 'CAN') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1");
            foreach ($temx as $tx) {
                if ($tx["tipoingreso"] == '04' || $tx["tipoingreso"] == '14') {
                    if ($servs != '') {
                        $servs .= ',';
                    }
                    $servs .= "'" . $tx["idservicio"] . "'";
                }
            }
            unset($temx);
        }

        //
        if ($_SESSION["entrada"]["tipo"] == 'CER' || $_SESSION["entrada"]["tipo"] == 'CERAFI' || $_SESSION["entrada"]["tipo"] == 'CERADM') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1");
            foreach ($temx as $tx) {
                if ($tx["tipoingreso"] == '06' || $tx["tipoingreso"] == '16') {
                    if ($servs != '') {
                        $servs .= ',';
                    }
                    $servs .= "'" . $tx["idservicio"] . "'";
                }
            }
            unset($temx);
        }

        //
        if ($_SESSION["entrada"]["tipo"] == 'OTR') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1");
            foreach ($temx as $tx) {
                if ($tx["tipoingreso"] == '01' || $tx["tipoingreso"] == '10' || $tx["tipoingreso"] == '11' || $tx["tipoingreso"] == '20') {
                    if ($servs != '') {
                        $servs .= ',';
                    }
                    $servs .= "'" . $tx["idservicio"] . "'";
                }
            }
            unset($temx);
        }

        //
        if ($servs != '') {
            $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "(fecoperacion between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "') and servicio IN (" . $servs . ")", "matricula,fecoperacion");
        } else {
            $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "(fecoperacion between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "')", "matricula,fecoperacion");
        }
        if ($regs && !empty($regs)) {
            foreach ($regs as $r) {
                $cont = 'no';
                if ($_SESSION["entrada"]["tipo"] == 'CERAFI') {
                    if ($r["tipogasto"] == '1') {
                        $cont = 'si';
                    }
                } else {
                    if ($_SESSION["entrada"]["tipo"] == 'CERADM') {
                        if ($r["tipogasto"] == '2') {
                            $cont = 'si';
                        }
                    } else {
                        if (($servs != '' && $r["ctranulacion"] != '1' && $r["ctranulacion"] != '2' && $r["tipogasto"] != '1' && $r["tipogasto"] != '2' && $r["tipogasto"] != '3' && $r["tipogasto"] != '7' && (substr($r["numerorecibo"], 0, 1) == 'R' || substr($r["numerorecibo"], 0, 1) == 'S')) ||
                                ($_SESSION["entrada"]["tipo"] == 'RUEREC' && $r["ctranulacion"] != '1' && $r["ctranulacion"] != '2' && $r["tipogasto"] == '7' && (substr($r["numerorecibo"], 0, 1) == 'R' || substr($r["numerorecibo"], 0, 1) == 'S')) ||
                                ($_SESSION["entrada"]["tipo"] == 'RUERES' && $r["ctranulacion"] != '1' && $r["ctranulacion"] != '2' && $r["tipogasto"] == '8' && (substr($r["numerorecibo"], 0, 1) == 'R' || substr($r["numerorecibo"], 0, 1) == 'S'))) {
                            $cont = 'si';
                        }
                    }
                }
                if ($cont == 'si') {
                    if ($r["tipogasto"] != '7') {
                        $ins = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $r["matricula"] . "'");
                    }
                    $lin = array();
                    $lin["camara"] = '';
                    $lin["matricula"] = '';
                    $lin["razonsocial"] = '';
                    $lin["organizacion"] = '';
                    $lin["categoria"] = '';
                    $lin["muncom"] = '';
                    $lin["ciiu1"] = '';
                    $lin["ciiu2"] = '';
                    $lin["ciiu3"] = '';
                    $lin["ciiu4"] = '';
                    if ($r["tipogasto"] != '7') {
                        $lin["camara"] = CODIGO_EMPRESA;
                        $lin["matricula"] = $r["matricula"];
                        $lin["razonsocial"] = $ins["razonsocial"];
                        $lin["organizacion"] = $ins["organizacion"];
                        $lin["categoria"] = $ins["categoria"];
                        $lin["muncom"] = $ins["muncom"];
                        $lin["ciiu1"] = $ins["ciiu1"];
                        $lin["ciiu2"] = $ins["ciiu2"];
                        $lin["ciiu3"] = $ins["ciiu3"];
                        $lin["ciiu4"] = $ins["ciiu4"];
                    } else {
                        $lin["camara"] = $r["codigocontable"];
                        $lin["matricula"] = $r["matricula"];
                        $lin["razonsocial"] = $r["nombre"];
                    }
                    $lin["fecha"] = $r["fecoperacion"];
                    $lin["recibo"] = $r["numerorecibo"];
                    $lin["servicio"] = $r["servicio"];
                    $lin["cantidad"] = $r["cantidad"];
                    $lin["ano"] = $r["anorenovacion"];
                    $lin["activos"] = $r["activos"];
                    $lin["valor"] = $r["valor"];
                    $_SESSION["jsonsalida"]["renglones"][] = $lin;
                }
            }
        }

        // ********************************************************************** //
        // Retornar registros
        // ********************************************************************** // 
        $_SESSION["jsonsalida"]["fechainicial"] = $_SESSION["entrada"]["fechainicial"];
        $_SESSION["jsonsalida"]["fechafinal"] = $_SESSION["entrada"]["fechafinal"];
        $_SESSION["jsonsalida"]["tipo"] = $_SESSION["entrada"]["tipo"];

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
