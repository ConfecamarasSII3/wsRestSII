<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait recordarContrasenaAfiliado {

    public function recordarContrasenaAfiliado(API $api) {
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
        $_SESSION["jsonsalida"]["identificacionafiliado"] = '';
        $_SESSION["jsonsalida"]["emailafiliado"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacionafiliado", true);
        $api->validarParametro("emailafiliado", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('recordarContrasenaAfiliado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // **************************************************************************** //
        // Recupera el expediente
        // **************************************************************************** //
        $exps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $_SESSION["entrada"]["identificacionafiliado"] . "'", "matricula");
        if ($exps === false || empty($exps)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Número de identificación no asociado a ningún expediente';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // **************************************************************************** //
        // Valida si el expediente es o no afiliado
        // **************************************************************************** //
        $arrMat = false;
        foreach ($exps as $e) {
            if ($e["ctrestmatricula"] == 'MA') {
                if ($e["ctrafiliacion"] == '1') {
                    if ($e["organizacion"] == '01' || ($e["organizacion"] > '02' && $e["categoria"] == '1')) {
                        $arrMat = $e;
                    }
                }
            }
        }
        unset($exps);

        //
        if ($arrMat === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Número de identificación no asociado a ningún expediente de afiliado activo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // **************************************************************************** //
        // Valida email de afiliado
        // **************************************************************************** //
        if ($_SESSION["entrada"]["emailafiliado"] != $arrMat["emailcom"]) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El email reportado no corresponde con el email del afiliado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // **************************************************************************** //
        // genera la clave del afiliado
        // **************************************************************************** //
        $clave = \funcionesGenerales::generarAleatorioNumerico($mysqli);
        // $clavehash = password_hash($clave, PASSWORD_DEFAULT);
        $clavehash = md5($clave);

        // **************************************************************************** //
        // Encuentra cupo del afiliado
        // **************************************************************************** //
        $saldoafiliado = 0;
        $afils = \funcionesRegistrales::consultarSaldoAfiliadoMysqliApi($mysqli, $arrMat["matricula"]);
        if ($afils && !empty($afils)) {
            foreach ($afils as $af) {
                $saldoafiliado = $af["cupo"];
            }
        }

        // **************************************************************************** //
        // Almacena la clave del afiliado
        // **************************************************************************** //
        $arrayCampos = array(
            'matricula',
            'clave',
            'fechaasignacion'
        );
        $arrayValues = array(
            "'" . $arrMat["matricula"] . "'",
            "'" . $clavehash . "'",
            "'" . date("Ymd") . '-' . date("His") . "'",
        );
        if (contarRegistrosMysqliApi($mysqli, 'mreg_claves_afiliados', "matricula='" . $arrMat["matricula"] . "'") == 0) {
            insertarRegistrosMysqliApi($mysqli, 'mreg_claves_afiliados', $arrayCampos, $arrayValues);
        } else {
            regrabarRegistrosMysqliApi($mysqli, 'mreg_claves_afiliados', $arrayCampos, $arrayValues, "matricula='" . $arrMat["matricula"] . "'");
        }

        // **************************************************************************** //
        // Envía email al afiliado
        // **************************************************************************** //
        if (TIPO_AMBIENTE == 'PRUEBAS' || TIPO_AMBIENTE == 'QA') {
            $emx = EMAIL_NOTIFICACION_PRUEBAS;
        } else {
            $emx = $_SESSION["entrada"]["emailafiliado"];
        }

        //
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
            $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, 'Cambio de clave acceso para afiliados, matricula  : ' . $arrMat["matricula"] . ' en ' . RAZONSOCIAL, $txt);
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
