<?php

/*
 * Se recibe json con la siguiente información
 * 
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait recuperarAnexoLiquidacion {

    public function recuperarAnexoLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        //$_SESSION['jsonsalida']['base64'] = '';
        //$_SESSION['jsonsalida']['extension'] = '';
        $_SESSION['jsonsalida']['link'] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("idanexo", true);

        //
        if (!$api->validarToken('recuperarAnexoLiquidacion ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //        
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . $_SESSION ["entrada"]["idanexo"] . " and eliminado='NO'");
        if ($temx === false || empty($temx)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo no localizado en base de datos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $file = str_replace ("//","/",$temx["path"] . '/' . $_SESSION["entrada"]["idanexo"] . '.' . $temx["tipoarchivo"]);
        $path = str_replace ("//","/",'/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $temx["path"] . '/' . $_SESSION["entrada"]["idanexo"] . '.' . $temx["tipoarchivo"]);
        $nametmp = $_SESSION ["generales"]["pathabsoluto"] . $path;
        if (!file_exists($nametmp)) {
            $url = \funcionesS3V4::obtenerUrlRepositorioS3($file);
            if ($url == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo no localizado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $link = $url;
                $_SESSION["jsonsalida"]["link"] = $url;                
            }
        } else {            
            $link = TIPO_HTTP . HTTP_HOST . $path;
            $_SESSION["jsonsalida"]["link"] = $link;
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
