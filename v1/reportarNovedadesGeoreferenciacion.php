<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait reportarNovedadesGeoreferenciacion {

    public function reportarNovedadesGeoreferenciacion(API $api) {

        ini_set('memory_limit', '4096M');
        ini_set('display_errors', 1);
        ini_set('default_socket_timeout', 14400);
        ini_set('set_time_limit', 14400);
        ini_set('soap.wsdl_cache_enabled', '0');
        ini_set('soap.wsdl_cache_ttl', '0');

        //
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        // $nameLog = 'api_relacionExpedientesModificados_' . date ("Ymd");
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["fechainicial"] = '';
        $_SESSION["jsonsalida"]["horainicial"] = '';
        $_SESSION["jsonsalida"]["fechafinal"] = '';
        $_SESSION["jsonsalida"]["horafinal"] = '';
        $_SESSION["jsonsalida"]["municipio"] = '';
        $_SESSION["jsonsalida"]["sistemadestino"] = '';
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION["jsonsalida"]["tiempo"] = '';
        $_SESSION["jsonsalida"]["expediente"] = array();

        //
        $tini = date("Ymd") . ' ' . date("His");

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("fechainicial", false);
        $api->validarParametro("horainicial", false);
        $api->validarParametro("fechafinal", false);
        $api->validarParametro("horafinal", false);
        $api->validarParametro("municipio", false);
        $api->validarParametro("sistemadestino", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('reportarNovedadesGeoreferenciacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim($_SESSION["entrada"]["fechainicial"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha inicial es obligatoria';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (trim($_SESSION["entrada"]["fechafinal"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha final es obligatoria';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim($_SESSION["entrada"]["fechainicial"]) == trim($_SESSION["entrada"]["fechafinal"])) {
            if (trim($_SESSION["entrada"]["horainicial"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La hora inicial es obligatoria';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (trim($_SESSION["entrada"]["horafinal"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La hora final es obligatoria';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //Weymer : 2019-10-17 : Rangos de horas
        $_SESSION["entrada"]["horainicial"] = sprintf("%06s", $_SESSION["entrada"]["horainicial"]);
        $_SESSION["entrada"]["horafinal"] = sprintf("%06s", $_SESSION["entrada"]["horafinal"]);
        if ($_SESSION["entrada"]["horafinal"] == '000000') {
            $_SESSION["entrada"]["horafinal"] = '235999';
        }
        $horas = substr(sprintf("%06s", $_SESSION["entrada"]["horainicial"]), 0, 2);
        if ($horas <= '24' && $horas >= '00') {
            $horaInicial = $_SESSION["entrada"]["horainicial"];
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Hora inicial debe ser entre 00 y 24 horas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $horas = substr(sprintf("%06s", $_SESSION["entrada"]["horafinal"]), 0, 2);
        if ($horas <= '24' && $horas >= '00') {
            $horaFinal = $_SESSION["entrada"]["horafinal"];
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Hora final debe ser entre 00 y 24 horas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if (trim($_SESSION["entrada"]["fechainicial"]) != '') {
            if (!\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechainicial"])) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha inicial incorrecta';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if (trim($_SESSION["entrada"]["fechafinal"]) != '') {
            if (!\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechafinal"])) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha final incorrecta';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if (substr($_SESSION["entrada"]["fechainicial"], 0, 6) != substr($_SESSION["entrada"]["fechafinal"], 0, 6)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El rango de fechas debe pertenecer al mismo periodo (mes)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["municipio"] = sprintf("%05s", $_SESSION["entrada"]["municipio"]);

        //
        $mysqli = conexionMysqliApi();

        //
        $mats = $this->encontrarNuevas($mysqli);
        $mats = $this->encontrarModificaciones($mysqli, $mats);
        $mats = $this->encontrarCancelaciones($mysqli, $mats);

        //
        if (!empty($mats)) {
            foreach ($mats as $m) {
                if (trim($m) != '') {
                    $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos',"matricula='" . $m . "'");
                    if ($exp && !empty($exp)) {
                        $arrJson = \funcionesRegistrales::construirJsonGeoreferenciacion($mysqli, $exp);
                        if ($arrJson) {
                            $_SESSION["jsonsalida"]["expediente"][] = $arrJson;
                            $_SESSION["jsonsalida"]["cantidad"]++;
                        }
                    }
                }
            }
        }

        // ********************************************************************** //
        // Retornar registros
        // ********************************************************************** // 
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["fechainicial"] = $_SESSION["entrada"]["fechainicial"];
        $_SESSION["jsonsalida"]["horainicial"] = $_SESSION["entrada"]["horainicial"];
        $_SESSION["jsonsalida"]["fechafinal"] = $_SESSION["entrada"]["fechafinal"];
        $_SESSION["jsonsalida"]["horafinal"] = $_SESSION["entrada"]["horafinal"];
        $_SESSION["jsonsalida"]["municipio"] = $_SESSION["entrada"]["municipio"];
        $_SESSION["jsonsalida"]["sistemadestino"] = $_SESSION["entrada"]["sistemadestino"];

        $tfin = date("Ymd") . ' ' . date("His");
        $_SESSION["jsonsalida"]["tiempo"] = $tini . ' a ' . $tfin;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public static function encontrarNuevas($mysqli) {
        $matriculas = array();
        $mats = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "fecmatricula>='" . $_SESSION["entrada"]["fechainicial"] . "' and fecmatricula <= '" . $_SESSION["entrada"]["fechafinal"] . "' and muncom='" . $_SESSION["entrada"]["municipio"] . "'", "matricula", "matricula,ctrestmatricula");
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if ($m["ctrestmatricula"] != 'NA' && $m["ctrestmatricula"] != 'NM') {
                    $matriculas[] = $m["matricula"];
                }
            }
        }
        unset($mats);
        return $matriculas;
    }

    public static function encontrarModificaciones($mysqli, $matriculas) {
        $query = "SELECT DISTINCT(matricula) FROM mreg_campos_historicos_" . substr($_SESSION["entrada"]["fechainicial"], 0, 4) . " WHERE fecha >= '" . $_SESSION["entrada"]["fechainicial"] . "' AND fecha <= '" . $_SESSION["entrada"]["fechafinal"] . "' GROUP BY matricula";
        $mats = ejecutarQueryMysqliApi($mysqli, $query);
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if (!isset($matriculas[$m["matricula"]])) {
                    $m1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m["matricula"] . "'", "matricula,ctrestmatricula,muncom");
                    if ($m1["ctrestmatricula"] != 'NA' && $m1["ctrestmatricula"] != 'NM') {
                        $matriculas[] = $m1["matricula"];
                    }
                }
            }
        }
        unset($mats);
        return $matriculas;
    }

    public static function encontrarCancelaciones($mysqli, $matriculas) {
        $mats = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "feccancelacion>='" . $_SESSION["entrada"]["fechainicial"] . "' and feccancelacion <= '" . $_SESSION["entrada"]["fechafinal"] . "' and muncom='" . $_SESSION["entrada"]["municipio"] . "'", "matricula", "matricula,ctrestmatricula");
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if ($m["ctrestmatricula"] != 'NA' && $m["ctrestmatricula"] != 'NM') {
                    $matriculas[] = $m["matricula"];
                }
            }
        }
        unset($mats);
        return $matriculas;
    }

}
