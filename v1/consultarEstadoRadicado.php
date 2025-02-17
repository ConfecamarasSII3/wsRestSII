<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarEstadoRadicado {

    public function consultarEstadoRadicado(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');
        
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['codigoestado'] = '';
        $_SESSION['jsonsalida']['procedereingreso'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("radicado", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarEstadoRadicado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        $mysqli = false;
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        
        //
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        $req = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $_SESSION["entrada"]["radicado"] . "'");
        if ($req && !empty($req)) {
            $_SESSION['jsonsalida']['codigoestado'] = trim($req['estadofinal']);
            if ($req["estadofinal"] == '05' || $req["estadofinal"] == '06' || $req["estadofinal"] == '07') {
                $_SESSION['jsonsalida']['procedereingreso'] = 'N';
                $arrTemDev = retornarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_nueva', "idradicacion='" . $_SESSION["entrada"]["radicado"] . "'", "fechadevolucion asc, horadevolucion asc");
                if (!empty($arrTemDev)) {
                    foreach ($arrTemDev as $dev) {
                        if ($dev["estado"] == '1' || $dev["estado"] == '2') {
                            if ($dev["tipodevolucion"] == 'R') {
                                $_SESSION['jsonsalida']['procedereingreso'] = 'S';
                            } else {
                                $_SESSION['jsonsalida']['procedereingreso'] = 'N';
                            }
                        }
                    }
                }
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron resultados para el radicado solicitado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
