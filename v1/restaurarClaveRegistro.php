<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait restaurarClaveRegistro {

    public function restaurarClaveRegistro(API $api) {

        //  require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/email.php');

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');        
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("email", true);


        if ($_SESSION["entrada"]["identificacion"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la identificación del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (strlen($_SESSION["entrada"]["identificacion"]) < 6) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La Identificación parece estar incorrectamente digitada.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["email"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el correo del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (!filter_var($_SESSION["entrada"]["email"], FILTER_VALIDATE_EMAIL) === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('restaurarClaveRegistro', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (defined('ACTIVAR_USUARIOS_NACIONALES') && substr(ACTIVAR_USUARIOS_NACIONALES, 0, 2) == 'SI') {
            $ok = \funcionesGenerales::recordarContrasenaNacional($_SESSION["entrada"]["email"], $_SESSION["entrada"]["identificacion"],'A');
            if ($ok["codigoerror"] == '0000') {
                $_SESSION["jsonsalida"]["codigoerror"] = '0000';
                $_SESSION["jsonsalida"]["mensajeerror"] = '';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }
        
        
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Busca el usuario previamente registrado y activado
        // ********************************************************************** // 
        $arrTemAP = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', "identificacion='" . $_SESSION["entrada"]["identificacion"] . "' and email='" . $_SESSION["entrada"]["email"] . "' and estado='VE' and claveconfirmacion !=''");
        if (!$arrTemAP || empty($arrTemAP)) {
            $arrTemAP = retornarRegistroMysqliapi($mysqli, 'usuarios_registrados', "identificacion='" . $_SESSION["entrada"]["identificacion"] . "' and email='" . $_SESSION["entrada"]["email"] . "' and estado='AP'");
            if (!$arrTemAP || empty($arrTemAP)) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Combinación Identificación/Email no existe como usuario registrado ni como usuario verificado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $clave = rand(0654321, 9999999);
                // $clavemd5 = md5($clave);
                $clavemd5 = password_hash($clave,PASSWORD_DEFAULT);
                $arrCampos = array(
                    'clave'
                );
                $arrValores = array(
                    "'" . $clavemd5 . "'"
                );
                $criterioBusqueda = "identificacion='" . $_SESSION["entrada"]["identificacion"] . "' and email='" . $_SESSION["entrada"]["email"] . "'";
                regrabarRegistrosMysqliApi($mysqli, 'usuarios_registrados', $arrCampos, $arrValores, $criterioBusqueda);
                $txt = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli,'email.CambioClave'));
                $txt = str_replace('[ID_USUARIO]', $arrTemAP["identificacion"], $txt);
                $txt = str_replace('[NOMBRE_USUARIO]', $arrTemAP["nombre"], $txt);
                $txt = str_replace('[CLAVE_USUARIO]', $clave, $txt);
                $txt = str_replace('[NOMBRE_ADMIN_PORTAL]', NOMBRE_ADMIN_PORTAL, $txt);
                $txt = str_replace('[RAZON_SOCIAL]', RAZONSOCIAL, $txt);
                $txt = str_replace('[SITIOWEB]', HTTP_HOST, $txt);
                $asunto = 'Cambio de clave para el usuario registrado ' . ' ' . $arrTemAP["nombre"] . ' desde Sistema Externo';
                $result = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["email"], $asunto, $txt);
            }
        } else {
            $clave = rand(0654321, 9999999);
            // $clavemd5 = md5($clave);
            $clavemd5 = password_hash($clave,PASSWORD_DEFAULT);
            $arrCampos = array(
                'claveacceso'
            );
            $arrValores = array(
                "'" . $clavemd5 . "'"
            );
            $criterioBusqueda = "identificacion='" . $_SESSION["entrada"]["identificacion"] . "' and email='" . $_SESSION["entrada"]["email"] . "'";
            regrabarRegistrosMysqliApi($mysqli, 'usuarios_verificados', $arrCampos, $arrValores, $criterioBusqueda);
            $txt = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli,'email.CambioClave'));
            $txt = str_replace('[ID_USUARIO]', $arrTemAP["identificacion"], $txt);
            $txt = str_replace('[NOMBRE_USUARIO]', $arrTemAP["nombre"], $txt);
            $txt = str_replace('[CLAVE_USUARIO]', $clave, $txt);
            $txt = str_replace('[NOMBRE_ADMIN_PORTAL]', NOMBRE_ADMIN_PORTAL, $txt);
            $txt = str_replace('[RAZON_SOCIAL]', RAZONSOCIAL, $txt);
            $txt = str_replace('[SITIOWEB]', HTTP_HOST, $txt);
            $asunto = 'Cambio de clave para el usuario verificado ' . ' ' . $arrTemAP["nombre"] . ' desde Sistema Externo';
            $result = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["email"], $asunto, $txt);
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
