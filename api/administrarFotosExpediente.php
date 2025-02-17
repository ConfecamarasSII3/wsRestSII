<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait administrarFotosExpediente {

    public function administrarFotosExpedienteUpload(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');
        
        //cantidad de registros
        $limit = 100;
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["identificadorfoto"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", true);
        $api->validarParametro("fecha", true);
        $api->validarParametro("latitud", false);
        $api->validarParametro("longitud", false);
        $api->validarParametro("imagen", true);
        $api->validarParametro("extension", true);
        $api->validarParametro("origen", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('administrarFotosExpedienteUpload', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim((string)$_SESSION["entrada"]["extension"]) != 'jpg' && trim((string)$_SESSION["entrada"]["extension"]) != 'png') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La imagen debe tener extensión jpg o png';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        if (strlen($_SESSION["entrada"]["imagen"]) > 2000000) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El archivo en base 64 de la imagen que se está subiendo al servidor debe ocupar menos de 2,000,000 de bytes';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'");
        if ($exp === false || empty ($exp)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La matrícula reportada no fue encontrada en el sistema registral';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        
        //
        $numrec = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli,'mreg_est_inscritos_fotos');
        
        //
        $arrCampos = array(
            'matricula',
            'fecha',
            'latitud',
            'longitud',
            'identificadorimagen',
            'extension',
            'origen',
            'fechahoraupload'
        );
        $arrValores = array(
            "'" . $_SESSION["entrada"]["matricula"] . "'",
            "'" . $_SESSION["entrada"]["fecha"] . "'",
            "'" . $_SESSION["entrada"]["latitud"] . "'",
            "'" . $_SESSION["entrada"]["longitud"] . "'",
            "'" . $numrec . "'",
            "'" . $_SESSION["entrada"]["extension"] . "'",
            "'" . addslashes($_SESSION["entrada"]["origen"]) . "'",
            "'" . date ("Ymd") . ' ' . date ("His") . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_fotos', $arrCampos, $arrValores);
        
        //
        if (!is_dir($_SESSION["generales"]["pathabsoluto"]  . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/fotoExpedientes')) {
            $path = $_SESSION["generales"]["pathabsoluto"] . '/' .
                    PATH_RELATIVO_IMAGES . '/' .
                    $_SESSION["generales"]["codigoempresa"] . '/mreg/fotoExpedientes/';

            if (!is_dir($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($_SESSION["generales"]["pathabsoluto"]  . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/fotoExpedientes');
            }
        }
        
        $numrec3 = substr($numrec,0,3);
        if (!is_dir($_SESSION["generales"]["pathabsoluto"]  . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/fotoExpedientes/' . $numrec3)) {
            $path = $_SESSION["generales"]["pathabsoluto"] . '/' .
                    PATH_RELATIVO_IMAGES . '/' .
                    $_SESSION["generales"]["codigoempresa"] . '/mreg/fotoExpedientes/' .
                    $numrec3;
            

            if (!is_dir($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($_SESSION["generales"]["pathabsoluto"]  . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/fotoExpedientes/' . $numrec3);
            }
        }

        $f = fopen($_SESSION["generales"]["pathabsoluto"]  . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/fotoExpedientes/' . $numrec3 . '/' . $numrec . '-' . $_SESSION["entrada"]["matricula"] . '.' . $_SESSION["entrada"]["extension"],"w");
        fwrite ($f,base64_decode($_SESSION["entrada"]["imagen"]));
        fclose ($f);

        //
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["identificadorimagen"] = $numrec;
        
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function administrarFotosExpedienteDelete (API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        //cantidad de registros
        $limit = 100;
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", true);
        $api->validarParametro("identificadorimagen", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('administrarFotosExpedienteDelete', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        //
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos_fotos', "identificadorimagen='" . $_SESSION["entrada"]["identificadorimagen"] . "'");
        if ($res === false || empty ($res)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificador no localizado en la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        if ($res["matricula"] != $_SESSION["entrada"]["matricula"]) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificador de foto no corresponde con la matrícula indicada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        
        //
        borrarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_fotos', "identificadorimagen='" . $_SESSION["entrada"]["identificadorimagen"] . "'");
        
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
