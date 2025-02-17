<?php

namespace api;

use api\API;

trait apiValidaciones {

    public function apiValidacionesGetLabel(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        // ************************************************************************************ //
        // Conexion a la BD
        // ************************************************************************************ //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);

        //
        $fecharec = date("Ymd");
        $horarec = date("His");

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Método utilizado no es POST';
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("label", true);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('apiValidacionesGetLabel', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Usuario API no tiene acceso al método apiValidacionesGetLabel';
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Retorna el label
        // ********************************************************************** //
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, base64_decode($_SESSION["entrada"]["label"]));

        //
        $mysqli->close();

        //
        $api->response($api->json($_SESSION ["jsonsalida"]), 200);
    }

    public function apiValidacionesGetInfo(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // ************************************************************************************ //
        // Conexion a la BD
        // ************************************************************************************ //
        $mysqli = conexionMysqliApi();

        //
        $fecharec = date("Ymd");
        $horarec = date("His");

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("info", true);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('apiValidacionesGetInfo', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'apiValidacionesGetInfo', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Retorna la info
        // ********************************************************************** //
        $regtxt = retornarRegistroMysqliApi($mysqli, 'pantallas_propias', "idpantalla='" . base64_decode($_SESSION ["entrada"] ["info"]) . "'");
        if ($regtxt === false || empty($regtxt)) {
            $regtxt = retornarRegistroMysqliApi($mysqli, 'pantallas', "idpantalla='" . base64_decode($_SESSION ["entrada"] ["info"]) . "'");
            if ($regtxt === false || empty($regtxt)) {
                $regtxt = retornarRegistroMysqliApi($mysqli, 'bas_help', "campo='" . base64_decode($_SESSION ["entrada"] ["info"]) . "'");
                if ($regtxt === false || empty($regtxt)) {
                    $regtxt = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='help' and idcodigo='" . base64_decode($_SESSION ["entrada"] ["info"]) . "'");
                    if ($regtxt === false || empty($regtxt)) {
                        $txt = 'Ayuda no encontrada';
                    } else{
                        $txt = str_replace(array(chr(13).chr(10),chr(13),chr(10)),'<br>',$regtxt["campo1"]);
                    }
                } else {
                    $txt = $regtxt["contenido"];
                }
            } else{
                $txt = $regtxt["txtasociado"];
            }
        } else {
            $txt = $regtxt["txtasociado"];
        }
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = $txt;

        //
        $mysqli->close();

        //
        $api->response($api->json($_SESSION ["jsonsalida"]), 200);
    }

}
