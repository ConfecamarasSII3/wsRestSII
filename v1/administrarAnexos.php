<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait administrarAnexos {

    public function administrarAnexosUpload(API $api) {
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
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("identificador", true);
        $api->validarParametro("expediente", false);
        $api->validarParametro("tipoanexo", true);
        $api->validarParametro("identificacion", false);
        $api->validarParametro("nombre", false);
        $api->validarParametro("idtipodoc", true);
        $api->validarParametro("numdoc", true);
        $api->validarParametro("fechadoc", true);
        $api->validarParametro("txtorigendoc", true);
        $api->validarParametro("extension", true);
        $api->validarParametro("imagen", true);
        

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('administrarAnexosUpload', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
        if (trim((string)$_SESSION["entrada"]["extension"]) != 'pdf') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La imagen debe tener extensión pdf';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        if (strlen($_SESSION["entrada"]["imagen"]) > 4000000) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El archivo en base 64 de la imagen que se está subiendo al servidor debe ocupar menos de 4,000,000 de bytes';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }       
        
        //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
        if ($liq === false || empty ($liq)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no localizada';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        if ($liq["idestado"] > '05') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion en un estado no disponible para ser modificada';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        $tt = retornarRegistroMysqliApi($mysqli,'bas_tipotramites',"id='" . $liq["tipotramite"] . "'");
        if ($tt === false || empty ($tt)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Tipo tramite de la liquidación no encontrado en tabla maestra de tipos de trámites';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);    
        }
        $_SESSION["entrada"]["bandeja"] = $tt["bandeja"];
        
        //
        $dir = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date ("Ymd");
        $pathrel = 'liquidacionmreg/' . date ("Ymd") . '/';
                
        //
        $arrCampos = array (
            'idliquidacion',
            'identificador',
            'expediente',
            'tipoanexo',
            'identificacion',
            'nombre',
            'idtipodoc',
            'numdoc',
            'fechadoc',
            'txtorigendoc',
            'path',
            'tipoarchivo',
            'bandeja',
            'eliminado'
        );
        $arrValores = array (
            $_SESSION["entrada"]["idliquidacion"],
            "'" . $_SESSION["entrada"]["identificador"] . "'",
            "'" . $_SESSION["entrada"]["expediente"] . "'",
            "'" . $_SESSION["entrada"]["tipoanexo"] . "'",
            "'" . $_SESSION["entrada"]["identificacion"] . "'",
            "'" . $_SESSION["entrada"]["nombre"] . "'",
            "'" . $_SESSION["entrada"]["idtipodoc"] . "'",
            "'" . $_SESSION["entrada"]["numdoc"] . "'",
            "'" . $_SESSION["entrada"]["fechadoc"] . "'",
            "'" . $_SESSION["entrada"]["txtorigendoc"] . "'",
            "'" . $pathrel . "'",
            "'" . $_SESSION["entrada"]["extension"] . "'",
            "'" . $_SESSION["entrada"]["bandeja"] . "'",
            "'NO'"            
        );
        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores);
        if ($res === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando indice del anexo en la tabla mreg_anexos_liquidaciones';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $idanexo = $_SESSION ["generales"] ["lastId"];
        }
                
        //
        if (!is_dir($dir)) {
                mkdir($dir, 0777);
                \funcionesGenerales::crearIndex($dir);
        }
        
        $archivo = $dir . '/' . $idanexo . '.pdf';
        $f = fopen($archivo,"wb");
        fwrite ($f,base64_decode($_SESSION["entrada"]["imagen"]));
        fclose ($f);

        //
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["identificadorimagen"] = $idanexo;
        
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function administrarAnexosDelete (API $api) {
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
        $api->validarParametro("identificadorimagen", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('administrarAnexosDelete', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . $_SESSION["entrada"]["identificadorimagen"]);
        if ($res === false || empty ($res)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo no localizado en la BD';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($res["idliquidacion"] == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo sin liquidación no borrable';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            
        }
        
        //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $res["idliquidacion"]);
        if ($liq === false || empty ($liq)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion del anexo no localizada';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        if ($liq["idestado"] > '05') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion en un estado no disponible para ser modificada (anexo no borrable)';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        
        //
        borrarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . $_SESSION["entrada"]["identificadorimagen"]);
        
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
