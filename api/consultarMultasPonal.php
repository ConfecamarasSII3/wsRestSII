<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarMultasPonal {

    public function consultarMultasPonal(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]['tipoidentificacion'] = '';
        $_SESSION["jsonsalida"]['identificacion'] = '';
        $_SESSION["jsonsalida"]['multadovencido'] = '';
        $_SESSION["jsonsalida"]['texto'] = '';
        $_SESSION["jsonsalida"]['multas'] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tipoidentificacion", true);
        $api->validarParametro("identificacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 

        if (!$api->validarToken('consultarMultasPonal', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Consume servicio web PONAL
        // ********************************************************************** //
        $temliq = retornarSecuenciaMysqliApi($mysqli, 'CON-PONAL-MULTAS');
        $res = \funcionesRegistrales::consultarMultasPolicia($mysqli, $_SESSION["entrada"]["tipoidentificacion"], $_SESSION["entrada"]["identificacion"], $temliq);

        if ($res === false) {
            $_SESSION["jsonsalida"]["multadovencido"] = 'ERROR';
            $_SESSION["jsonsalida"]["texto"] = 'No fue posible consultar multas en el RNMC (false)';
        }

        if ($res === 'ER') {
            $_SESSION["jsonsalida"]["multadovencido"] = 'ERROR';
            $_SESSION["jsonsalida"]["texto"] = 'No fue posible consultar multas en el RNMC (ER)';
        }

        if ($res === 'NO') {
            $_SESSION["jsonsalida"]["multadovencido"] = 'NO';
            $_SESSION["jsonsalida"]["texto"] = 'No existen multas vencidas en el RNMC para la identificacion ' . $_SESSION["pantide"]["identificacion"];
        }

        if ($res === 'SI') {
            $_SESSION["jsonsalida"]["multadovencido"] = 'SI';
            $_SESSION["jsonsalida"]["texto"] = 'Si tiene multas vencidas.';
            $muls = retornarRegistrosMysqliApi($mysqli, 'mreg_multas_ponal', "idliquidacion=" . $temliq, "fecha,hora");
            foreach ($muls as $m) {
                $amul = array();
                $amul["fechahoraconsulta"] = $m["fecha"] . ' - ' . $m["hora"];
                $amul["identificacion"] = $m["identificacion"];
                $amul["nombres"] = $m["nombres"];
                $amul["apellidos"] = $m["apellidos"];
                $amul["nit"] = $m["nit"];
                $amul["razonsocial"] = $m["razonsocial"];
                $amul["estado"] = $m["estado"];
                $amul["fechaimposicion"] = $m["fechaimposicion"];
                $amul["multavencida"] = $m["multavencida"];
                $amul["direccionhechos"] = $m["direccionhechos"];
                $amul["nombremunicipio"] = $m["nombremunicipio"];
                $amul["nombredpto"] = $m["nombredpto"];
                $amul["nombrebarrio"] = $m["nombrebarrio"];
                $amul["articuloinfringido"] = $m["articuloinfringido"];
                $amul["numeralinfringido"] = $m["numeralinfringido"];
                $_SESSION["jsonsalida"]['multas'][] = $amul;
            }
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
