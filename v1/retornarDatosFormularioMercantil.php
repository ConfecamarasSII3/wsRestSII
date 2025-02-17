<?php

/*
 * Se recibe json con la siguiente información
 * 
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait retornarDatosFormularioMercantil {


    public function retornarDatosFormularioMercantil(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["formulario"] = array();

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("ip", false);
        $api->validarParametro("sistemaorigen", false);

        //
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("cc", false);
        $api->validarParametro("matricula", true);

        //
        if (!$api->validarToken('retornarDatosFormularioMercantil', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        // ********************************************************************** //
        // Recupera liquidacion
        // ********************************************************************** // 
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera datos del expediente
        // ********************************************************************** //         
        $exps = retornarRegistroMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and expediente='" . $_SESSION["entrada"]["matricula"] . "'");
        if ($exps === false || empty($exps)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Formulario no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["jsonsalida"]["formulario"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $exps["xml"]);

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
