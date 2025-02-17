<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait recordarContrasenaVerificado {

    public function recordarContrasenaVerificado(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

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
        $api->validarParametro("identificacion", true);
        $api->validarParametro("email", true);
        $api->validarParametro("celular", false);

        if ($_SESSION["entrada"]["identificacion"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la identificación del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["email"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el correo del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('recordarContrasenaVerificado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
        // Busca el usuario
        // ********************************************************************** // 
        $condicion = "identificacion='" . $_SESSION["entrada"]["identificacion"] . "' and email='" . $_SESSION["entrada"]["email"] . "'";
        $arr = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', "estado='VE' and " . $condicion);

        // ********************************************************************** //
        // Se valida que exista el registro en la tabla usuarios_registrados
        // ********************************************************************** //         
        if (!$arr || empty($arr)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "0001";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Combinación Email/Identificación/celular no existe o no se encuentra activa';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            if ($arr['claveconfirmacion'] == "") {
                $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Combinación Email/Identificación/celular no se encuentra activa';
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        $claveacceso = $clave = rand(123456, 987654);
        $claveaccesomd5 = password_hash($claveacceso, PASSWORD_DEFAULT);

        //
        $mensaje = 'Se&ntilde;or(a)<br>';
        $mensaje .= '[NOMBRE_USUARIO_VERIFICADO]<br>';
        $mensaje .= 'Identificacion : [IDENTIFICACION_USUARIO_VERIFICADO]<br>';
        $mensaje .= 'Email : [EMAIL_USUARIO_VERIFICADO]<br>';
        $mensaje .= 'Celular : [CELULAR_USUARIO_VERIFICADO]<br><br>';

        $mensaje .= 'Hemos generado una contrase&ntilde;a segura para que usted o la empresa que representa, ';
        $mensaje .= 'pueda realizar desde la comodidad de su casa u oficina  tr&aacute;mites en forma 100% ';
        $mensaje .= 'virtual en el portal de servicios virtuales de la [RAZON_SOCIAL], evit&aacute;ndole ';
        $mensaje .= 'el tener que desplazarse a las instalaciones de la Entidad a radicar y pagar dichos ';
        $mensaje .= 'tr&aacute;mites. La contrase&ntilde;a asignada es <br>';

        $mensaje .= '<center><h2>[CLAVE_ACCESO]</h2></center><br>';

        $mensaje .= 'Recuerde que su clave segura es personal e intransferible, por lo tanto le solicitamos la mantenga en un lugar seguro.<br><br>';

        $mensaje .= 'Cordialmente<br><br>';
        $mensaje .= 'DEPARTAMENTO DE REGISTROS PUBLICOS<bR>';
        $mensaje .= '[RAZON_SOCIAL]<br><br>';
        $mensaje .= 'Este correo no se considera spam puesto que se ha enviado como parte integral del proceso de activaci&oacute;n ';
        $mensaje .= 'de su contrase&ntilde;a segura en el portal de la [RAZON_SOCIAL] para la realizaci&oacute;n de tr&aacute;mites ';
        $mensaje .= 'legales relacionados con los Registros P&uacute;blicos que administra nuestra organizaci&oacute;n.<br><br>';

        //
        $mensaje = str_replace("[NOMBRE_USUARIO_VERIFICADO]", $arr["nombres"] . ' ' . $arr["apellido1"] . ' ' . $arr["apellido2"], $mensaje);
        $mensaje = str_replace("[IDENTIFICACION_USUARIO_VERIFICADO]", $arr["identificacion"], $mensaje);
        $mensaje = str_replace("[EMAIL_USUARIO_VERIFICADO]", $arr["email"], $mensaje);
        $mensaje = str_replace("[CELULAR_USUARIO_VERIFICADO]", $arr["celular"], $mensaje);
        $mensaje = str_replace("[CLAVE_ACCESO]", $claveacceso, $mensaje);
        $mensaje = str_replace("[RAZON_SOCIAL]", RAZONSOCIAL, $mensaje);

        //
        $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["email"], 'Generacion clave de acceso', $mensaje);
        if ($res === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9996";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Errores enviando correos electr&oacute;nicos : ' . $_SESSION["generales"]["mensajeerror"];
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        actualizarLogMysqliApi($mysqli,'042', $_SESSION["generales"]["codigousuario"], 'recordarContrasenaVerificado.php', '', '', '', 'Envio de nueva clave segura  para el usuario (' . $arr["email"] . ' - ' . $arr["celular"] . ' - ' . $arr["identificacion"] . ') : ' . $claveaccesomd5);

        //
        $arrCampos = array(
            'claveacceso'
        );
        $arrValores = array(
            "'" . $claveaccesomd5 . "'"
        );
        regrabarRegistrosMysqliApi($mysqli, 'usuarios_verificados', $arrCampos, $arrValores, $condicion);

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
