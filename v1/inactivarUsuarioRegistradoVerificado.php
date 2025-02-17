<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait inactivarUsuarioRegistradoVerificado {

    public function inactivarUsuarioRegistradoVerificado(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["nombreusuario"] = '';

        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacionusuario", true);
        $api->validarParametro("emailusuario", true);
        $api->validarParametro("claveusuario", true);

        if ($_SESSION["entrada"]["identificacionusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la identificación del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["emailusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el correo del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (!filter_var($_SESSION["entrada"]["emailusuario"], FILTER_VALIDATE_EMAIL) === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["claveusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la contraseña del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('inactivarUsuarioRegistradoVerificado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        // ********************************************************************** //
        // Decodifica la clave enviada
        // ********************************************************************** //
        $clavelimpia = \funcionesGenerales::encrypt_decrypt('decrypt', 'c0nf3c4m4r4s', 'c0nf3c4m4r4s', $_SESSION["entrada"]["claveusuario"]);
        $clavemd5 = md5($clavelimpia);
        $clavesha = sha1($clavelimpia);

        $clavemd5limpia = md5($_SESSION["entrada"]["claveusuario"]);
        $claveshalimpia = sha1($_SESSION["entrada"]["claveusuario"]);

        $arrTemD = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', "estado <> 'EL' and identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");
        if (!$arrTemD || empty($arrTemD)) {
            $arrTemD = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', "estado <> 'EL' and identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");
            if (!$arrTemD || empty($arrTemD)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Combinación Email/Identificación no encontrado en la BD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                if (trim($clavemd5) != trim(strtoupper($arrTemD['clave'])) &&
                        trim($clavemd5) != trim($arrTemD['clave']) &&
                        trim($clavemd5limpia) != trim(strtoupper($arrTemD['clave'])) &&
                        trim($clavemd5limpia) != trim($arrTemD['clave']) &&
                        trim($clavesha) != trim(strtoupper($arrTemD['clave'])) &&
                        trim($clavesha) != trim($arrTemD['clave']) &&
                        trim($claveshalimpia) != trim(strtoupper($arrTemD['clave'])) &&
                        trim($claveshalimpia) != trim($arrTemD['clave']) &&
                        !password_verify($clavelimpia, $arrTemD['clave']) &&
                        !password_verify($_SESSION["entrada"]["claveusuario"], $arrTemD['clave'])) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "0003";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave errónea =>' . $_SESSION["entrada"]["claveusuario"] . ' - ' . $arrTemD['clave'];
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    $arrCampos = array(
                        'estado'
                    );
                    $arrValores = array(
                        "'EL'"
                    );
                    regrabarRegistrosMysqliApi($mysqli, 'usuarios_registrados', $arrCampos, $arrValores, "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");

                    $arrCampos = array(
                        'estado',
                        'fecha_hora_inactivacion',
                        'motivoinactivacion'
                    );
                    $arrValores = array(
                        "'EL'",
                        "'" . date("Ymd") . " " . date("His") . "'",
                        "'ELIMINADO A TRAVES DEL API DE INTEGRACION POR EL USUARIO DE API " . $_SESSION["entrada"]["usuariows"] . "'"
                    );
                    regrabarRegistrosMysqliApi($mysqli, 'usuarios_verificados', $arrCampos, $arrValores, "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");

                    $detalle = 'Se marco como eliminado el usuario ' . $_SESSION["entrada"]["identificacionusuario"] . ' - ' . $_SESSION["entrada"]["emailusuario"] . ' - ' . $arrTemD["nombre"] . ' - usuario api : ' . $_SESSION["entrada"]["usuariows"];
                    actualizarLogMysqliApi($mysqli, '004', 'API', 'inactivarUsuarioRegistradoVerificado.php', '', '', '', $detalle, '', '');
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario eliminado';
                    $_SESSION["jsonsalida"]["nombreusuario"] = $arrTemD["nombre"];
                    $mysqli->close();
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        } else {
            if (trim($clavemd5) != trim(strtoupper($arrTemD['claveacceso'])) &&
                    trim($clavemd5) != trim($arrTemD['claveacceso']) &&
                    trim($clavemd5limpia) != trim(strtoupper($arrTemD['claveacceso'])) &&
                    trim($clavemd5limpia) != trim($arrTemD['claveacceso']) &&
                    trim($clavesha) != trim(strtoupper($arrTemD['claveacceso'])) &&
                    trim($clavesha) != trim($arrTemD['claveacceso']) &&
                    trim($claveshalimpia) != trim(strtoupper($arrTemD['claveacceso'])) &&
                    trim($claveshalimpia) != trim($arrTemD['claveacceso']) &&
                    !password_verify($clavelimpia, $arrTemD['claveacceso']) &&
                    !password_verify($_SESSION["entrada"]["claveusuario"], $arrTemD['claveacceso'])) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0004";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave errónea';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $arrCampos = array(
                    'estado',
                    'fecha_hora_inactivacion',
                    'motivoinactivacion'
                );
                $arrValores = array(
                    "'EL'",
                    "'" . date("Ymd") . " " . date("His") . "'",
                    "'ELIMINADO A TRAVES DEL API DE INTEGRACION POR EL USUARIO DE API " . $_SESSION["entrada"]["usuariows"] . "'"
                );
                regrabarRegistrosMysqliApi($mysqli, 'usuarios_verificados', $arrCampos, $arrValores, "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");

                $arrCampos = array(
                    'estado'
                );
                $arrValores = array(
                    "'EL'"
                );
                regrabarRegistrosMysqliApi($mysqli, 'usuarios_registrados', $arrCampos, $arrValores, "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");
                
                $detalle = 'Se marco como eliminado el usuario ' . $_SESSION["entrada"]["identificacionusuario"] . ' - ' . $_SESSION["entrada"]["emailusuario"] . ' - ' . $arrTemD["nombre"] . ' - usuario api : ' . $_SESSION["entrada"]["usuariows"];
                actualizarLogMysqliApi($mysqli, '004', 'API', 'inactivarUsuarioRegistradoVerificado.php', '', '', '', $detalle, '', '');
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario eliminado';
                $_SESSION["jsonsalida"]["nombreusuario"] = $arrTemD["nombre"];
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }
    }

}
