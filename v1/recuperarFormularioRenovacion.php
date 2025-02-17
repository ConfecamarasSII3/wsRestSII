<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait recuperarFormularioRenovacion {

    public function recuperarFormularioRenovacion(API $api) {
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
        $_SESSION["jsonsalida"]['idliquidacion'] = '';
        $_SESSION["jsonsalida"]['numerorecuperacion'] = '';
        $_SESSION["jsonsalida"]['expediente'] = '';
        $_SESSION["jsonsalida"]['datos'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("numerorecuperacion", true);
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("expediente", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('recuperarFormularioRenovacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["numerorecuperacion"] == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el número de recuperación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["idliquidacion"] == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el número de la liquidación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["expediente"] == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el número del expediente';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Consulta la liquidacion
        // ********************************************************************** //
        $liq = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["numerorecuperacion"], 'NR');
        if ($liq === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Número de recuperación no encontrado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]['idliquidacion'] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION["jsonsalida"]['numerorecuperacion'] = $_SESSION["entrada"]["numerorecuperacion"];
        $_SESSION["jsonsalida"]['expediente'] = $_SESSION["entrada"]["expediente"];

        // ********************************************************************** //
        // Recupera la data del expediente
        // ********************************************************************** //
        $xml = retornarRegistroMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $liq["idliquidacion"] . " and expediente='" . $_SESSION["entrada"]["expediente"] . "'", "xml", "U");
        if ($xml !== '') {
            $data = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $xml);
            unset($data->crt);
            unset($data->crtsii);
            unset($data->informacionadicional);
            unset($data->clasevinculo);
            unset($data->inscripciones);
            unset($data->inscripcioneslibros);
            unset($data->nomant);
            unset($data->codigoscae);
            unset($data->periodicoafiliados);
            unset($data->imagenes);
            $mysqli->close();
            $_SESSION["jsonsalida"]["datos"] = $data;
            $_SESSION["jsonsalida"]["datos"]["estadoformulario"] = 'recuperado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $datos = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["expediente"], '', '', '', 'N', 'T');
            if ($datos === false) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            
            //
            $mysqli->close();
            
            //
            unset($datos["crt"]);
            unset($datos["crtsii"]);
            unset($datos["informacionadicional"]);
            unset($datos["clasevinculo"]);
            unset($datos["inscripciones"]);
            unset($datos["inscripcioneslibros"]);
            unset($datos["nomant"]);
            unset($datos["codigoscae"]);
            unset($datos["periodicoafiliados"]);
            unset($datos["imagenes"]);
            $_SESSION["jsonsalida"]["datos"] = $datos;
            $_SESSION["jsonsalida"]["datos"]["estadoformulario"] = 'nuevo';
            $json = $api->json($_SESSION["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }
    }

}
