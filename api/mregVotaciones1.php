<?php

namespace api;

use api\API;

trait mregVotaciones {

    public function mregVotacionImportarVotantes(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregVotacionImportarVotantes', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregVotacionImportarVotantes ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Pasa de base 64 a texto plano

        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $arregloVinculos = array ();
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_codvinculos', "1=1","id");
        foreach ($temx as $tx) {
            if ($tx["tipovinculo"] == 'RLP' ||
            $tx["tipovinculo"] == 'RLPE' ||
            $tx["tipovinculo"] == 'RLS' ||
            $tx["tipovinculo"] == 'RLS1' ||
            $tx["tipovinculo"] == 'RLS2' ||
            $tx["tipovinculo"] == 'RLS3' ||
            $tx["tipovinculo"] == 'RLS4') {
                $arregloVinculos[] = $tx["id"];
            }
        }
        unset ($temx);
        unset ($tx);
                
        //
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "ctrafiliacion='1'", "matricula");

        //
        foreach ($temx as $tx) {
            if ($tx["ctrestmatricula"] == 'MA' && $tx["ultanoren"] == date("Y")) {
                $arrCampos = array(
                    'ano',
                    'matricula',
                    'razonsocial',
                    'organizacion',
                    'categoria',
                    'idclase',
                    'numid',
                    'nit'
                );
                $arrValores = array(
                    "'" . date("Y") . "'",
                    "'" . $tx["matricula"] . "'",
                    "'" . addslashes($tx["razonsocial"]) . "'",
                    "'" . $tx["organizacion"] . "'",
                    "'" . $tx["categoria"] . "'",
                    "'" . $tx["idclase"] . "'",
                    "'" . $tx["numid"] . "'",
                    "'" . $tx["nit"] . "'"
                );
                $t1 = retornarRegistroMysqliApi($mysqli, 'mreg_votacion_universo_votantes', "ano='" . date("Y") . "' and matricula='" . $tx["matricula"] . "'");
                if ($t1 === false || empty($t1)) {
                    insertarRegistrosMysqliApi($mysqli, 'mreg_votacion_universo_votantes', $arrCampos, $arrValores);
                    actualizarLogMysqliApi($mysqli, '016', $_SESSION["generales"]["codigousuario"], 'mregVotacionVotantes.php', '', '', '', 'Selecciona matricula No. ' . $tx["matricula"] . ' - ' . $tx["razonsocial"], '', '');
                }
            }
        }

        //
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_votacion_universo_votantes', "ano='" . date ("Y") . "'","matricula");
        if ($temx && !empty($temx)) {
            foreach ($temx as $tx) {
                $t1x = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $tx["matricula"] . "'","id");
                foreach ($t1x as $t1) {
                    if ($t1["estado"] == 'V') {
                        if (in_array($t1["vinculo"],$arregloVinculos)) {
                            $arrCampos = array (
                                'ano',
                                'matricula',
                                'identificacion',
                                'nombre',
                                'vinculo',
                                'cargo'
                            );
                            $arrValores = array (
                                "'" . date ("Y") . "'",
                                "'" . $tx["matricula"] . "'",
                                "'" . $t1["numid"] . "'",
                                "'" . addslashes($t1["nombre"]) . "'",
                                "'" . $t1["vinculo"] . "'",
                                "'" . addslashes($t1["descargo"]) . "'"
                            );
                            $condicion = "ano='" . date ("Y") . "' and identificacion='" . $t1["numid"] . "'";
                            $t2x = retornarRegistroMysqliApi($mysqli, 'mreg_votacion_universo_identificaciones', $condicion);
                            if ($t2x === false || empty ($t2x)) {
                                insertarRegistrosMysqliApi($mysqli, 'mreg_votacion_universo_identificaciones', $arrCampos, $arrValores);
                                actualizarLogMysqliApi($mysqli, '016', $_SESSION["generales"]["codigousuario"], 'mregVotacionVotantes.php', '', '', '', 'Selecciona vínculo votante : matricula No. ' . $tx["matricula"] . ' - ' . $tx["razonsocial"] . ', Identificacion: ' . $t1["numid"] . ' - ' . $t1["nombre"], '', '');
                            }
                        }                        
                    }
                }
            }
        }
        
        //
        unset ($temx);
        unset ($t1x);
        unset ($tx);
        unset ($t1);
        unset ($t2x);
        
        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregVotacionImportarIdentificaciones (API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregVotacionImportarIdentificaciones', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregVotacionImportarIdentificaciones ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Pasa de base 64 a texto plano

        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // Elimina las identificaciones asociadas con los votantes
        borrarRegistrosMysqliApi($mysqli, 'mreg_votacion_universo_identificaciones', "ano='" . date ("Y") . "'");
        actualizarLogMysqliApi($mysqli, '009', $_SESSION["generales"]["codigousuario"], 'mregVotacionVotantes.php', '', '', '', 'Inicializa tabla de identificación de votantes', '', '');
        
        //
        $arregloVinculos = array ();
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_codvinculos', "1=1","id");
        foreach ($temx as $tx) {
            if ($tx["tipovinculo"] == 'RLP' ||
            $tx["tipovinculo"] == 'RLPE' ||
            $tx["tipovinculo"] == 'RLS' ||
            $tx["tipovinculo"] == 'RLS1' ||
            $tx["tipovinculo"] == 'RLS2' ||
            $tx["tipovinculo"] == 'RLS3' ||
            $tx["tipovinculo"] == 'RLS4') {
                $arregloVinculos[] = $tx["id"];
            }
        }
        unset ($temx);
        unset ($tx);
                
        //
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_votacion_universo_votantes', "ano='" . date ("Y") . "'","matricula");
        if ($temx && !empty($temx)) {
            foreach ($temx as $tx) {
                $t1x = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $tx["matricula"] . "'","id");
                foreach ($t1x as $t1) {
                    if ($t1["estado"] == 'V') {
                        if (in_array($t1["vinculo"],$arregloVinculos)) {
                            $arrCampos = array (
                                'ano',
                                'matricula',
                                'identificacion',
                                'nombre',
                                'vinculo',
                                'cargo'
                            );
                            $arrValores = array (
                                "'" . date ("Y") . "'",
                                "'" . $tx["matricula"] . "'",
                                "'" . $t1["numid"] . "'",
                                "'" . addslashes($t1["nombre"]) . "'",
                                "'" . $t1["vinculo"] . "'",
                                "'" . addslashes($t1["descargo"]) . "'"
                            );
                            $condicion = "ano='" . date ("Y") . "' and identificacion='" . $t1["numid"] . "'";
                            $t2x = retornarRegistroMysqliApi($mysqli, 'mreg_votacion_universo_identificaciones', $condicion);
                            if ($t2x === false || empty ($t2x)) {
                                insertarRegistrosMysqliApi($mysqli, 'mreg_votacion_universo_identificaciones', $arrCampos, $arrValores);
                                actualizarLogMysqliApi($mysqli, '016', $_SESSION["generales"]["codigousuario"], 'mregVotacionVotantes.php', '', '', '', 'Selecciona vínculo votante : matricula No. ' . $tx["matricula"] . ' - ' . $tx["razonsocial"] . ', Identificacion: ' . $t1["numid"] . ' - ' . $t1["nombre"], '', '');
                            }
                        }                        
                    }
                }
            }
        }
        
        //
        unset ($temx);
        unset ($t1x);
        unset ($tx);
        unset ($t1);
        unset ($t2x);
        
        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
    
    public function mregVotacionLimpiarTablas(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregVotacionLimpiarTablas', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregVotacionLimpiarTablas ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Pasa de base 64 a texto plano

        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $arrCampos = array (
            'fechahoravotacion',
            'mesavotacion',
            'identificacionvotante',
            'fechahoravotacionrf',
            'mesavotacionrf',
            'identificacionvotanterf'            
        );
        $arrValores = array (
            "''",
            "''",
            "''",
            "''",
            "''",
            "''"
        );
        regrabarRegistrosMysqliApi($mysqli, 'mreg_votacion_universo_votantes', $arrCampos, $arrValores, "ano='" . date ("Y") . "'");

        //
        borrarRegistrosMysqliApi($mysqli, 'mreg_votacion_resultados', "ano='" . date ("Y") . "'");
        
        //
        actualizarLogMysqliApi(null, '009', $_SESSION["generales"]["codigousuario"], 'mregVotacionVotantes.php', '', '', '', 'Limpia tablas de votacion, elimina simulacros', '', '');
        
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
