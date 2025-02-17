<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait relacionExpedientesModificados {

    public function relacionExpedientesModificados(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');
        
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["fechainicial"] = '';
        $_SESSION["jsonsalida"]["fechafinal"] = '';
        $_SESSION["jsonsalida"]["horainicial"] = '';
        $_SESSION["jsonsalida"]["horafinal"] = '';
        $_SESSION["jsonsalida"]["control"] = '';
        $_SESSION["jsonsalida"]["archivomodificaciones"] = '';
        $_SESSION["jsonsalida"]["matriculas"] = array();

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
        $api->validarParametro("horainicial", false);
        $api->validarParametro("horafinal", false);
        $api->validarParametro("control", true);

        //
        $_SESSION["entrada"]["horainicial"] = sprintf("%06s", $_SESSION["entrada"]["horainicial"]);
        $_SESSION["entrada"]["horafinal"] = sprintf("%06s", $_SESSION["entrada"]["horafinal"]);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('relacionExpedientesModificados', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        // validar horas
        if ($_SESSION["entrada"]["fechainicial"] == $_SESSION["entrada"]["fechafinal"]) {
            if ($_SESSION["entrada"]["horainicial"] != '' || $_SESSION["entrada"]["horafinal"] != '') {
                if ($_SESSION["entrada"]["horainicial"] == '' || $_SESSION["entrada"]["horafinal"] == '') {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar una hora innicial y una final';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                if ($_SESSION["entrada"]["horainicial"] > $_SESSION["entrada"]["horafinal"]) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Rango de horas/minutos incorrecto';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        }

        $mats = array();
        $condicion = "(fecha between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "') ";
        if ($_SESSION["entrada"]["horainicial"] != '') {
            $condicion .= "and (hora between '" . $_SESSION["entrada"]["horainicial"] . "' and '" . $_SESSION["entrada"]["horafinal"] . "') ";
        }
        if ($_SESSION["entrada"]["control"] == 'M') {
            $condicion .= "and (campo IN ('razonsocial','dircom','dirnot','emailcom','emailnot','telcom1','telcom2','telcom3','telnot','telnot2','telnot3','ctrestmatricula','organizacion','categoria','ciiu1','ciiu2','ciiu3','ciiu4','vinculo-creado','vinculo-modificado','vinculo-borrado','vinculo-finalizado'))";
        }

        // \logSii2::general2($nameLog, '', $condicion);
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //        
        $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . substr($_SESSION["entrada"]["fechainicial"], 0, 4), $condicion, "matricula");

        $name = $_SESSION["generales"]["codigoempresa"] . '-modificaciones-' . date("Ymd") . '-' . date("His") . '.txt';
        $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "w");
        $fl = "\r\n";
        if ($regs && !empty($regs)) {
            foreach ($regs as $r) {
                $inc = 'si';
                if (trim($r["matricula"]) == '') {
                    $inc = 'no';
                } else {
                    if (!is_numeric($r["matricula"])) {
                        $inc = 'no';
                        if (substr($r["matricula"],0,1) == 'S' || substr($r["matricula"],0,1) == 'N') {
                            if (is_numeric(substr($r["matricula"],1))) {
                                $inc = 'si';
                            }
                        }
                    }
                }
                if ($inc == 'si') {
                    $arr = array();
                    $arr["matricula"] = $r["matricula"];
                    $arr["campo"] = $r["campo"];
                    $arr["fecha"] = $r["fecha"];
                    $arr["hora"] = $r["hora"];
                    $arr["codigobarras"] = $r["codigobarras"];
                    $arr["datoanterior"] = $r["datoanterior"];
                    $arr["datonuevo"] = $r["datonuevo"];
                    $arr["usuario"] = $r["usuario"];
                    $arr["tipotramite"] = $r["tipotramite"];
                    $arr["recibo"] = $r["recibo"];
                    $linea = json_encode($arr);
                    fwrite($f, $linea . $fl);
                    if (!isset($mats[$r["matricula"]])) {
                        $mats[$r["matricula"]] = $r["matricula"];
                    }
                }
            }
        }
        fclose($f);

        // ********************************************************************** //
        // Retornar registros
        // ********************************************************************** // 
        $_SESSION["jsonsalida"]["fechainicial"] = $_SESSION["entrada"]["fechainicial"];
        $_SESSION["jsonsalida"]["fechafinal"] = $_SESSION["entrada"]["fechafinal"];
        $_SESSION["jsonsalida"]["horainicial"] = $_SESSION["entrada"]["horainicial"];
        $_SESSION["jsonsalida"]["horafinal"] = $_SESSION["entrada"]["horafinal"];
        $_SESSION["jsonsalida"]["control"] = $_SESSION["entrada"]["control"];
        foreach ($mats as $m) {
            $_SESSION["jsonsalida"]["matriculas"][] = $m;
        }
        $_SESSION["jsonsalida"]["archivomodificaciones"] = TIPO_HTTP . HTTP_HOST . '/tmp/' . $name;

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
