<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait solicitudEnvioAlertasSipref {

    public function solicitudEnvioAlertasSipref(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["identificacionusuario"] = '';
        $_SESSION["jsonsalida"]["emailusuario"] = '';
        $_SESSION["jsonsalida"]["nombreusuario"] = '';
        $_SESSION["jsonsalida"]["expediente"] = '';
        $_SESSION["jsonsalida"]["tipoalerta"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacionusuario", true);
        $api->validarParametro("emailusuario", true);
        $api->validarParametro("nombreusuario", true);
        $api->validarParametro("expediente", true);
        $api->validarParametro("tipoalerta", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('solicitudEnvioAlertasSipref', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // *********************************************************************** //
        // Abre la conexión con la BD
        // *********************************************************************** //        
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // **************************************************************************** //
        // Recupera el expediente
        // **************************************************************************** //
        $exps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["expediente"] . "'");
        if ($exps === false || empty($exps)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Matrícula/Inscripción no encontrtada en la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // **************************************************************************** //
        // Envía email al afiliado
        // **************************************************************************** //
        $txt = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, 'mreg.ClaveAfiliado.Email'));
        $txt = str_replace("NOMBRE_EMPRESA", RAZONSOCIAL, $txt);
        $txt = str_replace("[NIT]", NIT, $txt);
        $txt = str_replace("[DIR_EMPRESA]", DIRECCION1, $txt);
        $txt = str_replace("[PBX]", PBX, $txt);
        $txt = str_replace("[FAX]", FAX, $txt);
        $txt = str_replace("[MATRICULA]", $arrMat["matricula"], $txt);
        $txt = str_replace("[NOMBRE]", $arrMat["razonsocial"], $txt);
        $txt = str_replace("[FECHA]", date("Y-m-d") . ' - ' . date("H:i:s"), $txt);
        $txt = str_replace("[CLAVE]", $clave, $txt);
        $txt = str_replace("Clave para uso del dispensador", 'Clave para consumo del cupo de afiliado', $txt);
        $txt = str_replace("[SALDO]", number_format($saldoafiliado, 0), $txt);
        $txt = str_replace("[EMAIL]", EMAIL_ATENCION_USUARIOS, $txt);
        $txt = str_replace("[TELEFONO_ATENCION_USUARIOS]", TELEFONO_ATENCION_USUARIOS, $txt);

        $okEmail = true;
        $iEmail = 1;
        while ($iEmail <= 5 && $okEmail == true) {
            $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["emailafiliado"], 'Cambio de clave acceso para afiliados, matricula  : ' . $arrMat["matricula"] . ' en ' . RAZONSOCIAL, $txt);
            if ($res === false) {
                sleep(5);
                $iEmail++;
            } else {
                $okEmail = false;
            }
        }
        if ($okEmail === true) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error enviando email con la contraseña';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $_SESSION["jsonsalida"]["mensajeerror"] = 'Email con la contrasena enviado a ' . $_SESSION["entrada"]["emailafiliado"];
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
