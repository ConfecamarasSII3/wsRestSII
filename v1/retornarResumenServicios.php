<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait retornarResumenServicios {

    public function retornarResumenServicios(API $api) {
        ini_set('memory_limit','2048M');
        
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["servicios"] = array();
        
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("fechainicial", false);
        $api->validarParametro("fechafinal", false);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        // if (!$api->validarToken('retornarResumenServicios', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
        //     $api->response($api->json($_SESSION["jsonsalida"]), 200);
        // }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $serviciosC = array();

        //
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1", "idservicio");
        foreach ($temx as $x) {
            if ($x["tipoingreso"] >= '01' && $x["tipoingreso"] <= '30') {
                if (!isset($serviciosC[$x["idservicio"]])) {
                    $serviciosC[$x["idservicio"]] = array();
                    $serviciosC[$x["idservicio"]]["servicio"] = $x["idservicio"];
                    $serviciosC[$x["idservicio"]]["nombre"] = trim($x["nombre"]);
                    $serviciosC[$x["idservicio"]]["cantidad"] = 0;
                    $serviciosC[$x["idservicio"]]["valor"] = 0;
                }
            }
        }
        unset($temx);

        //
        $condicion = "(fecoperacion between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "')";
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', $condicion, "servicio", "servicio,cantidad,valor,numerorecibo,ctranulacion,tipogasto");
        foreach ($temx as $x) {
            if (substr($x["numerorecibo"], 0, 1) == 'S' || substr($x["numerorecibo"], 0, 1) == 'R') {
                if ($x["ctranulacion"] != '1' && $x["ctranulacion"] != '2') {
                    if ($x["tipogasto"] == '' || $x["tipogasto"] == '0' || $x["tipogasto"] == '8') {
                        // echo "camara ... " . $x["servicio"] . "\r\n";
                        if ($x["cantidad"] == 0) {
                            $x["cantidad"] = 1;
                        }
                        if (isset($serviciosC[$x["servicio"]])) {
                            $serviciosC[$x["servicio"]]["cantidad"] += $x["cantidad"];
                            $serviciosC[$x["servicio"]]["valor"] += $x["valor"];
                        }
                    }
                }
            }
        }
        unset($temx);
        $mysqli->close();

        //
        foreach ($serviciosC as $cx) {
            if ($cx["cantidad"] != 0) {
                $serv = array(
                    'servicio' => $cx["servicio"],
                    'nombre' => $cx["nombre"],
                    'cantidad' => $cx["cantidad"],
                    'valor' => $cx["valor"]
                );
                $_SESSION["jsonsalida"]["servicios"][] = $serv;
            }
        }

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
