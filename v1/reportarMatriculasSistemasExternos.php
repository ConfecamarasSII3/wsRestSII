<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait reportarMatriculasSistemasExternos {

    public function reportarMatriculasSistemasExternos(API $api) {

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
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        set_error_handler('myErrorHandler');

        $nameLog = 'api_reportarMatriculasSistemasExternos_' . date ("Ymd");
        
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["fechainicial"] = '';
        $_SESSION["jsonsalida"]["horainicial"] = '';
        $_SESSION["jsonsalida"]["fechafinal"] = '';
        $_SESSION["jsonsalida"]["horafinal"] = '';
        $_SESSION["jsonsalida"]["tiporeporte"] = '';
        $_SESSION["jsonsalida"]["municipio"] = '';
        $_SESSION["jsonsalida"]["sistemadestino"] = '';
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION["jsonsalida"]["tiempo"] = '';
        $_SESSION["jsonsalida"]["tipoenvio"] = '';
        $_SESSION["jsonsalida"]["expediente"] = array();

        //
        $tini = date("Ymd") . ' ' . date("His");

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("fechainicial", false);
        $api->validarParametro("horainicial", false);
        $api->validarParametro("fechafinal", false);
        $api->validarParametro("horafinal", false);
        $api->validarParametro("tiporeporte", true);
        $api->validarParametro("municipio", false);
        $api->validarParametro("sistemadestino", true);
        $api->validarParametro("tipoenvio", true);
        $api->validarParametro("matricula", false);
        $api->validarParametro("ambiente", false);
        $api->validarParametro("procesar", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('reportarMatriculasSistemasExternos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["tiporeporte"] < '1' || $_SESSION["entrada"]["tiporeporte"] > '5') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en el tipo de reporte';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["tiporeporte"] == '1' || $_SESSION["entrada"]["tiporeporte"] == '2' || $_SESSION["entrada"]["tiporeporte"] == '3') {
            if (trim($_SESSION["entrada"]["fechainicial"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para tipo reporte 1, 2 o 3, la fecha inicial es obligatoria';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (trim($_SESSION["entrada"]["fechafinal"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para tipo reporte 1, 2 o 3, la fecha inicial es obligatoria';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        $_SESSION["entrada"]["horainicial"] = sprintf("%06s", $_SESSION["entrada"]["horainicial"]);
        $_SESSION["entrada"]["horafinal"] = sprintf("%06s", $_SESSION["entrada"]["horafinal"]);
        if ($_SESSION["entrada"]["tiporeporte"] == '1' || $_SESSION["entrada"]["tiporeporte"] == '3') {
            if (trim($_SESSION["entrada"]["horainicial"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para tipo reporte 1 o 3, la hora inicial es obligatoria';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (trim($_SESSION["entrada"]["horafinal"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para tipo reporte 1 o 3, la hora final es obligatoria';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if ($_SESSION["entrada"]["tiporeporte"] == '5' && $_SESSION["entrada"]["matricula"] == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Para tipo reporte 5 debe enviar un número de matrícula';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($_SESSION["entrada"]["tiporeporte"] != '5') {
            $_SESSION["entrada"]["matricula"] = '';
        }

        //
        if ($_SESSION["entrada"]["tipoenvio"] == '') {
            $_SESSION["entrada"]["tipoenvio"] = '0';
        }

        //
        if (isset($_SESSION["entrada"]["procesar"])) {
            $_SESSION["entrada"]["procesar"] = strtoupper((string) $_SESSION["entrada"]["procesar"]);
        }
        if (!isset($_SESSION["entrada"]["procesar"]) || $_SESSION["entrada"]["procesar"] == '') {
            $_SESSION["entrada"]["procesar"] = 'T';
        } else {
            if ($_SESSION["entrada"]["procesar"] != 'T' && $_SESSION["entrada"]["procesar"] != 'D') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Campo procesar debe ser "T"(todo) o "D"(Diario)';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if ($_SESSION["entrada"]["tipoenvio"] != '0' && $_SESSION["entrada"]["tipoenvio"] != '1') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en el tipo de envio (0.- pruebas, 1.- produccion)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["horainicial"] = sprintf("%06s", $_SESSION["entrada"]["horainicial"]);
        $_SESSION["entrada"]["horafinal"] = sprintf("%06s", $_SESSION["entrada"]["horafinal"]);
        if ($_SESSION["entrada"]["horafinal"] == '000000') {
            $_SESSION["entrada"]["horafinal"] = '235999';
        }

        //
        $horas = substr(sprintf("%06s", $_SESSION["entrada"]["horainicial"]), 0, 2);
        if ($horas <= '24' && $horas >= '00') {
            $horaInicial = $_SESSION["entrada"]["horainicial"];
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'horainicial debe ser entre 00 y 24 horas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $horas = substr(sprintf("%06s", $_SESSION["entrada"]["horafinal"]), 0, 2);
        if ($horas <= '24' && $horas >= '00') {
            $horaFinal = $_SESSION["entrada"]["horafinal"];
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'horafinal debe ser entre 00 y 24 horas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
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
        $_SESSION["entrada"]["municipio"] = sprintf("%05s", $_SESSION["entrada"]["municipio"]);

        //
        $mysqli = false;
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }

        //
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }

        //
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }

        //
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        
        if (isset($_SESSION["entrada"]["procesar"]) && $_SESSION["entrada"]["procesar"] != '' && $_SESSION["entrada"]["procesar"] != 'T' && $_SESSION["entrada"]["procesar"] != 'D') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El parámetro procesar debe ser T(Todas) o D(Dia)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        //
        $inicio2 = retornarRegistroMysqliApi($mysqli, 'mreg_control_sistemas_externos', "sistemadestino='" . $_SESSION["entrada"]["sistemadestino"] . "' and tiporeporte='2' and municipio='" . $_SESSION["entrada"]["municipio"] . "'");
        $inicio3 = retornarRegistroMysqliApi($mysqli, 'mreg_control_sistemas_externos', "sistemadestino='" . $_SESSION["entrada"]["sistemadestino"] . "' and tiporeporte='3' and municipio='" . $_SESSION["entrada"]["municipio"] . "'");
        $inicio4 = retornarRegistroMysqliApi($mysqli, 'mreg_control_sistemas_externos', "sistemadestino='" . $_SESSION["entrada"]["sistemadestino"] . "' and tiporeporte='4' and municipio='" . $_SESSION["entrada"]["municipio"] . "'");

        //
        if ($_SESSION["entrada"]["tiporeporte"] == '1') { // Todos
            if ($inicio2 === false || empty($inicio2)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se ha indicado la fecha inicial de reporte de matrículas nuevas (mreg_control_sistemas_externos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($inicio3 === false || empty($inicio3)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se ha indicado la fecha inicial de reporte de novedades (modificaciones) (mreg_control_sistemas_externos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($inicio4 === false || empty($inicio4)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se ha indicado la fecha inicial de reporte de cancelaciones (mreg_control_sistemas_externos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if ($_SESSION["entrada"]["tiporeporte"] == '2') { // Nuevas
            if ($inicio2 === false || empty($inicio2)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se ha indicado la fecha inicial de reporte de matrículas nuevas (mreg_control_sistemas_externos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if ($_SESSION["entrada"]["tiporeporte"] == '3') { // Modificaciones
            if ($inicio3 === false || empty($inicio3)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se ha indicado la fecha inicial de reporte de modificaciones (mreg_control_sistemas_externos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if ($_SESSION["entrada"]["tiporeporte"] == '4') { // Cancelaciones
            if ($inicio4 === false || empty($inicio4)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se ha indicado la fecha inicial de reporte de cancelaciones (mreg_control_sistemas_externos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if ($_SESSION["entrada"]["tiporeporte"] == '1') { // Todos
            $mats = \funcionesRegistrales::encontrarNuevas($mysqli, $inicio2, $_SESSION["entrada"]["tipoenvio"]);
            $mods = \funcionesRegistrales::encontrarModificaciones($mysqli, $inicio3, $_SESSION["entrada"]["tipoenvio"]);
            $cans = \funcionesRegistrales::encontrarCancelaciones($mysqli, $inicio4, $_SESSION["entrada"]["tipoenvio"]);
        }

        if ($_SESSION["entrada"]["tiporeporte"] == '2') { // Matrículas
            $mats = \funcionesRegistrales::encontrarNuevas($mysqli, $inicio2, $_SESSION["entrada"]["tipoenvio"]);
        }

        if ($_SESSION["entrada"]["tiporeporte"] == '3') { // Modificaciones
            $mods = \funcionesRegistrales::encontrarModificaciones($mysqli, $inicio3, $_SESSION["entrada"]["tipoenvio"]);
        }

        if ($_SESSION["entrada"]["tiporeporte"] == '4') { // Cancelaciones
            $cans = \funcionesRegistrales::encontrarCancelaciones($mysqli, $inicio4, $_SESSION["entrada"]["tipoenvio"]);
        }

        //
        $mats1 = array();
        if ($_SESSION["entrada"]["tiporeporte"] == '5') { // Envío unitario
            $mats1[] = $_SESSION["entrada"]["matricula"];
        }

        //
        $matsenviadas = array();

        // 
        if (!empty($mats)) {
            foreach ($mats as $m) {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");
                $incluir = 'si';
                if ($exp["organizacion"] > '02' && ($exp["categoria"] == '' || $exp["categoria"] == '0')) {
                    $incluir = 'no';
                    \logApi::general2($nameLog,$exp["matricula"], 'Matricula sin categoria');
                }
                if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
                    if (trim((string) $exp["nit"]) == '') {
                        $incluir = 'no';
                        \logApi::general2($nameLog,$exp["matricula"], 'Matricula sin nit');
                    }
                }
                if ($exp["organizacion"] == '01' || $exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                    if ($exp["ciiu1"] == '' || $exp["dircom"] == '') {
                        $incluir = 'no';
                        \logApi::general2($nameLog,$exp["matricula"], 'Matricula sin ciiu o sin dirección');
                    }
                }
                if ($incluir == 'si') {
                    $arrJson = \funcionesRegistrales::construirJsonSistemasExternos($mysqli, $exp, '2', $_SESSION["entrada"]["sistemadestino"], $_SESSION["entrada"]["tipoenvio"]);
                    if ($arrJson) {
                        $incluir = 'si';
                        if (($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) && $arrJson["numid"] == '') {
                            $incluir = 'no';
                        }
                        if ($incluir == 'si') {
                            $matsenviadas[] = array(
                                "matricula" => $m,
                                "idenvio" => $arrJson["idenvio"]
                            );
                            $_SESSION["jsonsalida"]["expediente"][] = $arrJson;
                            $_SESSION["jsonsalida"]["cantidad"]++;
                        } else {
                            \logApi::general2($nameLog,$exp["matricula"], 'Matricula sin identificacion');
                        }
                    } else {
                        \logApi::general2($nameLog,$exp["matricula"], $_SESSION["generales"]["mensajeerror"]);
                    }
                }
            }
        }

        //
        if (!empty($mats1)) {
            foreach ($mats1 as $m) {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");
                $incluir = 'si';
                if ($exp["organizacion"] > '02' && ($exp["categoria"] == '' || $exp["categoria"] == '0')) {
                    $incluir = 'no';
                }
                if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
                    if (trim((string) $exp["nit"]) == '') {
                        $incluir = 'no';
                    }
                }
                if ($exp["organizacion"] == '01' || $exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                    if ($exp["ciiu1"] == '' || $exp["dircom"] == '') {
                        $incluir = 'no';
                    }
                }
                if ($incluir == 'si') {
                    $arrJson = \funcionesRegistrales::construirJsonSistemasExternos($mysqli, $exp, '2', $_SESSION["entrada"]["sistemadestino"], $_SESSION["entrada"]["tipoenvio"]);
                    if ($arrJson) {
                        $matsenviadas[] = array(
                            "matricula" => $m,
                            "idenvio" => $arrJson["idenvio"]
                        );
                        $_SESSION["jsonsalida"]["expediente"][] = $arrJson;
                        $_SESSION["jsonsalida"]["cantidad"]++;
                    } else {
                        \logApi::general2($nameLog,$exp["matricula"], $_SESSION["generales"]["mensajeerror"]);
                    }
                }
            }
        }

        //
        if (!empty($mods)) {
            foreach ($mods as $m) {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");
                $arrJson = \funcionesRegistrales::construirJsonSistemasExternos($mysqli, $exp, '3', $_SESSION["entrada"]["sistemadestino"], $_SESSION["entrada"]["tipoenvio"]);
                if ($arrJson) {
                    $matsenviadas[] = array(
                        "matricula" => $m,
                        "idenvio" => $arrJson["idenvio"]
                    );
                    $_SESSION["jsonsalida"]["expediente"][] = $arrJson;
                    $_SESSION["jsonsalida"]["cantidad"]++;
                }
            }
        }

        //
        if (!empty($cans)) {
            foreach ($cans as $m) {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");
                $arrJson = \funcionesRegistrales::construirJsonSistemasExternos($mysqli, $exp, '4', $_SESSION["entrada"]["sistemadestino"], $_SESSION["entrada"]["tipoenvio"]);
                if ($arrJson) {
                    $matsenviadas[] = array(
                        "matricula" => $m,
                        "idenvio" => $arrJson["idenvio"]
                    );
                    $_SESSION["jsonsalida"]["expediente"][] = $arrJson;
                    $_SESSION["jsonsalida"]["cantidad"]++;
                }
            }
        }

        //
        // ********************************************************************** //
        // Actualiza la tabla de envios realizados
        // Siempre y cuando el tipoenvio = 1 (Definitivo)
        // ********************************************************************** // 
        if ($_SESSION["entrada"]["tipoenvio"] == '1') {
            foreach ($matsenviadas as $m) {
                $arrCampos = array(
                    'estadoenvio',
                );
                $arrValores = array(
                    "'PE'"
                );
                regrabarRegistrosMysqliApi($mysqli, 'mreg_envio_matriculas_api', $arrCampos, $arrValores, "idenvio='" . $m["idenvio"] . "'");
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
        $_SESSION["jsonsalida"]["tiporeporte"] = $_SESSION["entrada"]["tiporeporte"];
        $_SESSION["jsonsalida"]["municipio"] = $_SESSION["entrada"]["municipio"];
        $_SESSION["jsonsalida"]["sistemadestino"] = $_SESSION["entrada"]["sistemadestino"];
        $_SESSION["jsonsalida"]["tipoenvio"] = $_SESSION["entrada"]["tipoenvio"];

        $tfin = date("Ymd") . ' ' . date("His");
        $_SESSION["jsonsalida"]["tiempo"] = $tini . ' a ' . $tfin;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
