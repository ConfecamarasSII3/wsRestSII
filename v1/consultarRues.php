<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarRues {

    public function consultarRuesRazonSocial(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/wsRR18N.class.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();
        $_SESSION["jsonsalida"]["cantidad"] = 0;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("palabrasbuscar", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarRuesRazonSocial', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Conecta con RUES para consultar el RR18N
        // ********************************************************************** // 
        $ins = \RR18N::singleton(wsRUE_RR18N);
        $camOrigen = CODIGO_EMPRESA;
        $camDestino = CODIGO_EMPRESA;

        // Todos los posibles parametros a enviar
        $parametros = array(
            'numero_interno' => date("Ymd") . date("His") . $camOrigen . $camDestino,
            'usuario' => CODIGO_EMPRESA,
            'razon_social' => mb_strtoupper($_SESSION["entrada"]["palabrasbuscar"]) //consulta por nombre
        );

        try {
            $res = $ins->consultarNombre($parametros);
        } catch (Exception $e) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible conectarse con el RUES para realizar la consulta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $t = (array) $res;
        if ($t["codigo_error"] != '0000') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en respuesta RUES : ' . str_replace(array("'", '"'), "", $t["mensaje_error"]);
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (isset($t["datos_respuesta"])) {

            if (!isset($t["datos_respuesta"][0])) {
                $renglon = array();
                foreach ($t["datos_respuesta"] as $key => $valor) {
                    $renglon[$key] = $valor;
                }
                $_SESSION["jsonsalida"]["renglones"][] = $renglon;
            } else {
                foreach ($t["datos_respuesta"] as $r) {
                    $renglon = array();
                    foreach ($r as $key => $valor) {
                        $renglon[$key] = $valor;
                    }
                    $_SESSION["jsonsalida"]["renglones"][] = $renglon;
                }
            }
        }

        //
        $_SESSION["jsonsalida"]["cantidad"] = count($_SESSION["jsonsalida"]["renglones"]);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function consultarHomonimia(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();
        $_SESSION["jsonsalida"]["cantidad"] = 0;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("razon_social", true);
        $api->validarParametro("usuario_aplicacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarHomonimia', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida usuario
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        $usu = retornarRegistroMysqliApi($mysqli, "usuarios","idusuario='" . $_SESSION["entrada"]["usuario_aplicacion"] . "'");
        $mysqli->close();
        if ($usu === false ||
        empty($usu) ||
        $usu["eliminado"] == 'SI' ||
        $usu["fechaactivacion"] == '00000000' ||
        $usu["fechainactivacion"] != '00000000' ||
        ($usu["fechaexpiracion"] != '' && $usu["fechaexpiracion"] < date ("Ymd"))) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario de aplicacion no permitido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        
        // ********************************************************************** //
        // Conecta con RUES para consultar el RR18N
        // ********************************************************************** // 
        $res = \funcionesRues::consultarHomonimia($_SESSION["entrada"]["razon_social"], $_SESSION["entrada"]["usuario_aplicacion"]);
        if ($res["codigoError"] != '0000') {
            $_SESSION["jsonsalida"]["codigoerror"] = $res["codigoError"];
            $_SESSION["jsonsalida"]["mensajeerror"] = $res["msgError"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!isset($res["response"]["datos_respuesta"]) || empty($res["response"]["datos_respuesta"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron coincidencias';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!($res["response"]["datos_respuesta"][0])) {
            $renglon = array();
            foreach ($res["response"]["datos_respuesta"] as $key => $valor) {
                $renglon[$key] = $valor;
            }
            $_SESSION["jsonsalida"]["renglones"][] = $renglon;
        } else {
            foreach ($res["response"]["datos_respuesta"] as $r) {
                $renglon = array();
                foreach ($r as $key => $valor) {
                    $renglon[$key] = $valor;
                }
                $_SESSION["jsonsalida"]["renglones"][] = $renglon;
            }
        }

        //
        $_SESSION["jsonsalida"]["cantidad"] = count($_SESSION["jsonsalida"]["renglones"]);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
    
    public function consultarNombre(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();
        $_SESSION["jsonsalida"]["cantidad"] = 0;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("nombre", true);
        $api->validarParametro("usuario_aplicacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarNombre', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida usuario
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        $usu = retornarRegistroMysqliApi($mysqli, "usuarios","idusuario='" . $_SESSION["entrada"]["usuario_aplicacion"] . "'");
        $mysqli->close();
        if ($usu === false ||
        empty($usu) ||
        $usu["eliminado"] == 'SI' ||
        $usu["fechaactivacion"] == '00000000' ||
        $usu["fechainactivacion"] != '00000000' ||
        ($usu["fechaexpiracion"] != '' && $usu["fechaexpiracion"] < date ("Ymd"))) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario de aplicacion no permitido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        
        // ********************************************************************** //
        // Conecta con RUES para consultar el RR18N
        // ********************************************************************** // 
        $res = \funcionesRues::consultarNombre($_SESSION["entrada"]["nombre"], $_SESSION["entrada"]["usuario_aplicacion"]);
        if ($res["codigoError"] != '0000') {
            $_SESSION["jsonsalida"]["codigoerror"] = $res["codigoError"];
            $_SESSION["jsonsalida"]["mensajeerror"] = $res["msgError"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!isset($res["response"]["datos_respuesta"]) || empty($res["response"]["datos_respuesta"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron coincidencias';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!($res["response"]["datos_respuesta"][0])) {
            $renglon = array();
            foreach ($res["response"]["datos_respuesta"] as $key => $valor) {
                $renglon[$key] = $valor;
            }
            $_SESSION["jsonsalida"]["renglones"][] = $renglon;
        } else {
            foreach ($res["response"]["datos_respuesta"] as $r) {
                $renglon = array();
                foreach ($r as $key => $valor) {
                    $renglon[$key] = $valor;
                }
                $_SESSION["jsonsalida"]["renglones"][] = $renglon;
            }
        }

        //
        $_SESSION["jsonsalida"]["cantidad"] = count($_SESSION["jsonsalida"]["renglones"]);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
    
    public function consultarPalabrasClave(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();
        $_SESSION["jsonsalida"]["cantidad"] = 0;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("palabras", true);
        $api->validarParametro("usuario_aplicacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarPalabrasClave', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida usuario
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        $usu = retornarRegistroMysqliApi($mysqli, "usuarios","idusuario='" . $_SESSION["entrada"]["usuario_aplicacion"] . "'");
        $mysqli->close();
        if ($usu === false ||
        empty($usu) ||
        $usu["eliminado"] == 'SI' ||
        $usu["fechaactivacion"] == '00000000' ||
        $usu["fechainactivacion"] != '00000000' ||
        ($usu["fechaexpiracion"] != '' && $usu["fechaexpiracion"] < date ("Ymd"))) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario de aplicacion no permitido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        
        // ********************************************************************** //
        // Conecta con RUES para consultar el RR18N
        // ********************************************************************** // 
        $res = \funcionesRues::consultarPalabrasClave($_SESSION["entrada"]["palabras"], $_SESSION["entrada"]["usuario_aplicacion"]);
        if ($res["codigoError"] != '0000') {
            $_SESSION["jsonsalida"]["codigoerror"] = $res["codigoError"];
            $_SESSION["jsonsalida"]["mensajeerror"] = $res["msgError"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!isset($res["response"]["datos_respuesta"]) || empty($res["response"]["datos_respuesta"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron coincidencias';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!($res["response"]["datos_respuesta"][0])) {
            $renglon = array();
            foreach ($res["response"]["datos_respuesta"] as $key => $valor) {
                $renglon[$key] = $valor;
            }
            $_SESSION["jsonsalida"]["renglones"][] = $renglon;
        } else {
            foreach ($res["response"]["datos_respuesta"] as $r) {
                $renglon = array();
                foreach ($r as $key => $valor) {
                    $renglon[$key] = $valor;
                }
                $_SESSION["jsonsalida"]["renglones"][] = $renglon;
            }
        }

        //
        $_SESSION["jsonsalida"]["cantidad"] = count($_SESSION["jsonsalida"]["renglones"]);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
    
    public function consultarNumeroIdentificacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();
        $_SESSION["jsonsalida"]["cantidad"] = 0;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tipo_identificacion", true);
        $api->validarParametro("numero_identificacion", true);
        $api->validarParametro("dv", false);
        $api->validarParametro("usuario_aplicacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarNumeroIdentificacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida usuario
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        $usu = retornarRegistroMysqliApi($mysqli, "usuarios","idusuario='" . $_SESSION["entrada"]["usuario_aplicacion"] . "'");
        $mysqli->close();
        if ($usu === false ||
        empty($usu) ||
        $usu["eliminado"] == 'SI' ||
        $usu["fechaactivacion"] == '00000000' ||
        $usu["fechainactivacion"] != '00000000' ||
        ($usu["fechaexpiracion"] != '' && $usu["fechaexpiracion"] < date ("Ymd"))) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario de aplicacion no permitido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        
        // ********************************************************************** //
        // Conecta con RUES para consultar el RR18N
        // ********************************************************************** // 
        $res = \funcionesRues::consultarNumeroIdentificacion($_SESSION["entrada"]["tipo_identificacion"], $_SESSION["entrada"]["numero_identificacion"], $_SESSION["entrada"]["dv"], $_SESSION["entrada"]["usuario_aplicacion"]);
        if ($res["codigoError"] != '0000') {
            $_SESSION["jsonsalida"]["codigoerror"] = $res["codigoError"];
            $_SESSION["jsonsalida"]["mensajeerror"] = $res["msgError"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!isset($res["response"]["datos_respuesta"]) || empty($res["response"]["datos_respuesta"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron coincidencias';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!($res["response"]["datos_respuesta"][0])) {
            $renglon = array();
            foreach ($res["response"]["datos_respuesta"] as $key => $valor) {
                $renglon[$key] = $valor;
            }
            $_SESSION["jsonsalida"]["renglones"][] = $renglon;
        } else {
            foreach ($res["response"]["datos_respuesta"] as $r) {
                $renglon = array();
                foreach ($r as $key => $valor) {
                    $renglon[$key] = $valor;
                }
                $_SESSION["jsonsalida"]["renglones"][] = $renglon;
            }
        }

        //
        $_SESSION["jsonsalida"]["cantidad"] = count($_SESSION["jsonsalida"]["renglones"]);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
