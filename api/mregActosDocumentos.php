<?php

namespace api;

use api\API;

trait mregActosDocumentos {

    public function mregActosDocumentosBuscarPorIdentificacion(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["expediente"] = array();

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacion", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRadicarDocumentosBuscarPorIdentificacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRadicarDocumentosBuscarPorIdentificacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        $identificacion = str_replace(array(".",",","-"," "),"",ltrim(trim($_SESSION["entrada"]["identificacion"]),"0"));
        if ($identificacion == '' || strlen($identificacion) < 6 || strlen($identificacion) > 11) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Número de identificación está incorrecto (' . $identificacion. ')';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        
        $_SESSION["jsonsalida"]["expediente"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $identificacion . "' or nit='" . $identificacion . "'","*",'U');
        if ($_SESSION["jsonsalida"]["expediente"] === false || empty ($_SESSION["jsonsalida"]["expediente"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontró en la BD ningún registro con la identificación indicada.  (' . $identificacion. ')';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);    
        }
        
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
