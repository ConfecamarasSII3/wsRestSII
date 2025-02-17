<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarServicios {

    public function consultarServicios(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["servicio"] = '';
        $_SESSION["jsonsalida"]["data"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("servicio", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarServicios', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]["servicio"] = $_SESSION["entrada"]["servicio"];

        //
        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busqueda de servicio
        // ********************************************************************** //
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $_SESSION["entrada"]["servicio"] . "'");
        if ($res === false || empty($res)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Servicio no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        foreach ($res as $key => $valor) {
            if (!is_numeric($key)) {
                $_SESSION["jsonsalida"]["data"][$key] = $valor;
            }
        }

        //
        $_SESSION["jsonsalida"]["data"]["tarifas"] = array();
        $tars = retornarRegistrosMysqliApi($mysqli, 'mreg_tarifas', "idservicio='" . $_SESSION["entrada"]["servicio"] . "'", "ano,idrango");
        if ($tars && !empty($tars)) {
            foreach ($tars as $t) {
                if ($t["ano"] >= date("Y") - 5) {
                    $_SESSION["jsonsalida"]["data"]["tarifas"][] = array(
                        'ano' => $t["ano"],
                        'rango' => $t["idrango"],
                        'topeminimo' => number_format($t["topeminimo"],2,".",""),
                        'topemaximo' => number_format($t["topemaximo"],2,".",""),
                        'tarifa' => number_format($t["tarifa"],2,".","")
                    );
                }
            }
        }

        //
        $_SESSION["jsonsalida"]["data"]["descuentos"] = array();
        $tars = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios_descuentos', "idservicio='" . $_SESSION["entrada"]["servicio"] . "'", "iddescuento");
        if ($tars && !empty($tars)) {
            foreach ($tars as $t) {
                $_SESSION["jsonsalida"]["data"]["descuentos"][] = array(
                    'iddescuento' => $t["iddescuento"],
                    'nombredescuento' => retornarRegistroMysqliApi($mysqli, 'mreg_descuentos', "id='" . $t["iddescuento"] . "'", "nombre"),
                    'valor' => number_format($t["valor"],2,".",""),
                    'porcentaje' => number_format($t["porcentaje"],2,".","")
                );
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
