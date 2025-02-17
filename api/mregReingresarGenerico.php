<?php

namespace api;

use api\API;

trait mregReingresarGenerico {

    public function mregReingresarGenericoDeleteFile(API $api) {
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("codigobarras", true);
        $api->validarParametro("filex", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregReingresarGenericoDeleteFile', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregReingresarGenericoDeleteFile ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $file = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/radicacion_generica/' . $_SESSION["entrada"]["codigobarras"] . '/' . base64_decode($_SESSION["entrada"]["filex"]);
        if (file_exists($file)) {
            unlink($file);
        }

        //
        $lisAnx = false;
        if ($dir = opendir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/radicacion_generica/' . $_SESSION["entrada"]["codigobarras"])) {
            while (($archivo = readdir($dir)) !== false) {
                if ($archivo != '.' && $archivo != '..' && $archivo != '.htaccess' && $archivo != 'index.html') {
                    $lisAnx[] = $archivo;
                }
            }
            closedir($dir);
        }
        $txt = '';
        $pres = new \presentacionBootstrap();
        if ($lisAnx) {
            foreach ($lisAnx as $anx) {
                $txt .= $anx . '&nbsp;&nbsp;';
                $txt .= '<a href=javascript:download(\'' . base64_encode($anx) . '\');" data-toggle="tooltip" data-placement="left" title="Descargar archivo"><i class="fas fa-download fa-sm"></i></a>&nbsp;&nbsp;';
                $txt .= '<a href=javascript:delete(\'' . base64_encode($anx) . '\');" data-toggle="tooltip" data-placement="left" title="Borrar anexo"><i class="far fa-trash-alt fa-sm"></i></a><br>';
            }
            $html = $pres->armarLineaTextoInformativa($txt, 'left', '', 'text-dark');
        } else {
            $html = $pres->armarLineaTextoInformativa('Aún no se han cargado soportes', 'center');
        }
        unset($pres);
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = $html;
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }
    
    public function mregReingresarGenericoRefreshDivSoportes (API $api) {
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("codigobarras", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregReingresarGenericoRefreshDivSoportes', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregReingresarGenericoRefreshDivSoportes ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $file = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/radicacion_generica/' . $_SESSION["entrada"]["codigobarras"] . '/' . base64_decode($_SESSION["entrada"]["filex"]);
        if (file_exists($file)) {
            unlink($file);
        }

        //
        $lisAnx = false;
        if ($dir = opendir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/radicacion_generica/' . $_SESSION["entrada"]["codigobarras"])) {
            while (($archivo = readdir($dir)) !== false) {
                if ($archivo != '.' && $archivo != '..' && $archivo != '.htaccess' && $archivo != 'index.html') {
                    $lisAnx[] = $archivo;
                }
            }
            closedir($dir);
        }
        $txt = '';
        $pres = new \presentacionBootstrap();
        if ($lisAnx) {
            foreach ($lisAnx as $anx) {
                $txt .= $anx . '&nbsp;&nbsp;';
                $txt .= '<a href=javascript:download(\'' . base64_encode($anx) . '\');" data-toggle="tooltip" data-placement="left" title="Descargar archivo"><i class="fas fa-download fa-sm"></i></a>&nbsp;&nbsp;';
                $txt .= '<a href=javascript:delete(\'' . base64_encode($anx) . '\');" data-toggle="tooltip" data-placement="left" title="Borrar anexo"><i class="far fa-trash-alt fa-sm"></i></a><br>';
            }
            $html = $pres->armarLineaTextoInformativa($txt, 'left', '', 'text-dark');
        } else {
            $html = $pres->armarLineaTextoInformativa('Aún no se han cargado soportes', 'center');
        }
        unset($pres);
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["html"] = $html;
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregReingresarGenericoValidarReingreso(API $api) {
        require_once ('presentacion.class.php');

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
        $api->validarParametro("codigobarras", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregReingresarGenericoValidarReingreso', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregReingresarGenericoValidarReingreso ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $lisAnx = false;
        if ($dir = opendir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/radicacion_generica/' . $_SESSION["entrada"]["codigobarras"])) {
            while (($archivo = readdir($dir)) !== false) {
                if ($archivo != '.' && $archivo != '..' && $archivo != '.htaccess' && $archivo != 'index.html') {
                    $lisAnx[] = $archivo;
                }
            }
            closedir($dir);
        }
        if ($lisAnx === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se han cargado archivos en pdf, no es posible firmar el trámite';
        }
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

}
