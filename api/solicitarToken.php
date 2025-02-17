<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

trait solicitarToken {

    public function solicitarToken(API $api) {

        require_once ('myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["token"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("clavews", true);

        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME);

        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $usuWs = retornarRegistroMysqliApi($mysqli, 'mreg_api_sii_usuarios', "usuariows='" . $_SESSION["entrada"]["usuariows"] . "'");

        if (!$usuWs || empty($usuWs)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'usuariows no concuerda con la información almacenada en la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($usuWs['estado'] != "A") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario no está activo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (md5($_SESSION["entrada"]["clavews"]) != $usuWs['clavews']) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'clavews no es la correcta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $classObj = new funcionesAPI();
        $token = $classObj->generarJWT($_SESSION["generales"]["codigoempresa"], $_SESSION["entrada"]["usuariows"]);

        $_SESSION["jsonsalida"]["token"] = $token;

        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
