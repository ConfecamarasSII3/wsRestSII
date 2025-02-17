<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait Usuarios {

    public function autenticarUsuario(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        
        /*
          require_once ('myErrorHandler.php');
          $resError = set_error_handler('myErrorHandler');
         */

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['nombreusuario'] = '';
        $_SESSION['jsonsalida']['emailusuario'] = '';
        $_SESSION['jsonsalida']['celularusuario'] = '';
        $_SESSION['jsonsalida']['idtipousuario'] = '';

        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", false);
        $api->validarParametro("emailusuario", false);
        $api->validarParametro("identificacionusuario", false);
        //$api->validarParametro("celularusuario", false);
        $api->validarParametro("claveusuario", false);

        if ($_SESSION["entrada"]["idusuario"] == "" && $_SESSION["entrada"]["emailusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9991";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el idusuario o email';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["emailusuario"] != "") {
            if (!filter_var($_SESSION["entrada"]["emailusuario"], FILTER_VALIDATE_EMAIL) === true) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9991";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        if ($_SESSION["entrada"]["identificacionusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9991";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la identificación del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (strlen($_SESSION["entrada"]["identificacionusuario"]) < 6) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La Identificación parece estar incorrectamente digitada.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["claveusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9991";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la contraseña del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken(__FUNCTION__, $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el usuario de Sistema
        // ********************************************************************** // 
        $claveValidacion = strtoupper((string)$_SESSION["entrada"]["claveusuario"]);

        $detectaUsuario = 'no';

        if ($_SESSION["entrada"]["idusuario"] != "") {
            $queryUsuariosInternos = "fechaactivacion!='00000000' and "
                    . "fechainactivacion='00000000' and "
                    . "password!='' and "
                    . "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and "
                    . "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'";

            $arrUsrSis = retornarRegistroMysqliApi($mysqli, 'usuarios', $queryUsuariosInternos);
            if (!$arrUsrSis || empty($arrUsrSis)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9992";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se detecta el usuario interno';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {

                //
                if (isset($arrUsrSis["fechaexpiracion"]) && ltrim((string) $arrUsrSis["fechaexpiracion"], "0") != "") {
                    if ($arrUsrSis["fechaexpiracion"] < date("Ymd")) {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9992";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario expirado';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                }

                $claveUsrSis = strtoupper((string)$arrUsrSis['password']);

                $txtTipoUsuario = '';
                switch ($arrUsrSis['idtipousuario']) {
                    case '01':
                        $txtTipoUsuario = 'Usuario Administrador';
                        break;
                    case '02':
                        $txtTipoUsuario = 'Usuario Administrativo';
                        break;
                    case '03':
                        $txtTipoUsuario = 'Usuario Producción';
                        break;
                    case '04':
                        $txtTipoUsuario = 'Usuario Ventas';
                        break;
                    case '05':
                        $txtTipoUsuario = 'Usuario Registral';
                        break;
                    case '06':
                        $txtTipoUsuario = 'Usuario Externo';
                        break;
                    default:
                        break;
                }

                if ($claveValidacion == $claveUsrSis) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave Correcta (us)';
                    $_SESSION['jsonsalida']['nombreusuario'] = strtoupper($arrUsrSis['nombreusuario']);
                    $_SESSION['jsonsalida']['emailusuario'] = $arrUsrSis['email'];
                    $_SESSION['jsonsalida']['idtipousuario'] = $arrUsrSis['idtipousuario'];
                    $_SESSION['jsonsalida']['txttipousuario'] = $txtTipoUsuario;
                    $_SESSION['jsonsalida']['celularusuario'] = $arrUsrSis['celular'];
                    $detectaUsuario = 'si';
                } else {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9992";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave digitada para el usuario ' . $_SESSION["entrada"]["idusuario"] . ' no es correcta.';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        }

        if ($_SESSION["entrada"]["emailusuario"] != "" && $detectaUsuario == 'no') {


            // ********************************************************************** //
            // Busca el usuario Verificado
            // ********************************************************************** //
            $queryUsuariosVerificados = "estado='VE' and "
                    . "claveconfirmacion!='' and "
                    . "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and "
                    . "email='" . $_SESSION["entrada"]["emailusuario"] . "'";

            $arrUsrVer = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', $queryUsuariosVerificados);
            if (!$arrUsrVer || empty($arrUsrVer)) {
                $detectaUsuario = 'no';
            } else {
                $claveUsrVer = strtoupper((string)$arrUsrVer['claveacceso']);

                if ($claveValidacion == $claveUsrVer) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave Correcta (uv)';
                    $_SESSION['jsonsalida']['nombreusuario'] = strtoupper($arrUsrVer['nombre']);
                    $_SESSION['jsonsalida']['emailusuario'] = $arrUsrVer['email'];
                    $_SESSION['jsonsalida']['idtipousuario'] = "00";
                    $_SESSION['jsonsalida']['txttipousuario'] = "Usuario Público";
                    $_SESSION['jsonsalida']['celularusuario'] = $arrUsrVer['celular'];
                    $detectaUsuario = 'si';
                } else {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9993";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave digitada para el usuario ' . $_SESSION["entrada"]["emailusuario"] . ' no es correcta.';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            // ********************************************************************** //
            // Busca el usuario Registrado
            // ********************************************************************** // 

            $queryUsuariosRegistrados = "estado='AP' and clave!='' and "
                    . "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and "
                    . "email='" . $_SESSION["entrada"]["emailusuario"] . "'";

            $arrUsrReg = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', $queryUsuariosRegistrados);

            if (!$arrUsrReg || empty($arrUsrReg)) {
                $detectaUsuario = 'no';
            } else {

                $claveUsrReg = strtoupper($arrUsrReg['clave']);

                if ($claveValidacion == $claveUsrReg) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave Correcta (ur)';
                    $_SESSION['jsonsalida']['nombreusuario'] = strtoupper($arrUsrReg['nombre']);
                    $_SESSION['jsonsalida']['emailusuario'] = $arrUsrReg['email'];
                    $_SESSION['jsonsalida']['idtipousuario'] = "00";
                    $_SESSION['jsonsalida']['txttipousuario'] = "Usuario Público";
                    $_SESSION['jsonsalida']['celularusuario'] = $arrUsrReg['celular'];
                    $detectaUsuario = 'si';
                } else {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9994";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave digitada para el usuario ' . $_SESSION["entrada"]["emailusuario"] . ' no es correcta.';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            // ********************************************************************** //
            // Busca el usuario Registrado que no ha realizado la activación
            // ********************************************************************** // 

            $queryUsuariosRegistradosSinActivar = "estado='PE' and clave!='' and "
                    . "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and "
                    . "email='" . $_SESSION["entrada"]["emailusuario"] . "'";

            $arrUsrRegSinAct = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', $queryUsuariosRegistradosSinActivar);

            if ($arrUsrRegSinAct || !empty($arrUsrRegSinAct)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9995";
                $_SESSION["jsonsalida"]["mensajeerror"] = "El usuario " . strtoupper($arrUsrRegSinAct['nombre']) . " se encuentra pendiente de activación, agradecemos realizar la confirmación en el mensaje enviado en la solicitud de registro.";
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            if ($detectaUsuario == 'no') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se detecta el usuario externo, por lo tanto lo invitamos a realizar su registro de usuario en el sistema';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function restaurarClaveUsuario(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        /*
          require_once ('myErrorHandler.php');
          $resError = set_error_handler('myErrorHandler');
         */

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
        $api->validarParametro("idusuario", false);
        $api->validarParametro("emailusuario", false);
        $api->validarParametro("identificacionusuario", true);

        if ($_SESSION["entrada"]["idusuario"] == "" && $_SESSION["entrada"]["emailusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9991";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el idusuario o email';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (strlen($_SESSION["entrada"]["identificacionusuario"]) < 6) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La Identificación parece estar incorrectamente digitada.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!filter_var($_SESSION["entrada"]["emailusuario"], FILTER_VALIDATE_EMAIL) === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken(__FUNCTION__, $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el usuario previamente registrado y/o activado
        // ********************************************************************** // 

        $detectaUsuario = 'no';

        if ($_SESSION["entrada"]["idusuario"] != "") {
            $criterioBusqueda = "fechaactivacion!='00000000' and "
                    . "fechainactivacion='00000000' and "
                    . "password!='' and "
                    . "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and "
                    . "idusuario='" . $_SESSION["entrada"]["idusuario"] . "' and "
                    . "email='" . $_SESSION["entrada"]["emailusuario"] . "'";

            $arrUsrSis = retornarRegistroMysqliApi($mysqli, 'usuarios', $criterioBusqueda);
            if (!$arrUsrSis || empty($arrUsrSis)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9992";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se detecta el usuario interno';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {

                //
                if (isset($arrUsrSis["fechaexpiracion"]) && ltrim((string) $arrUsrSis["fechaexpiracion"], "0") != "") {
                    if ($arrUsrSis["fechaexpiracion"] < date("Ymd")) {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9992";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario expirado';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                }
                
                $detectaUsuario = 'si';
                // ********************************************************************** //
                // Actualizar clave del usuario sistema
                // ********************************************************************** // 

                $clave = rand(0654321, 9999999);
                $clavemd5 = md5($clave);

                $arrCampos = array(
                    'fechacambioclave',
                    'password'
                );

                $arrValores = array(
                    "'" . date("Ymd") . "'",
                    "'" . $clavemd5 . "'"
                );

                regrabarRegistrosMysqliApi($mysqli, 'usuarios', $arrCampos, $arrValores, $criterioBusqueda);
                $txt = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, 'email.CambioClave1'));
                $txt = str_replace('[ID_USUARIO]', $arrUsrSis["idusuario"], $txt);
                $txt = str_replace('[NOMBRE_USUARIO]', $arrUsrSis["nombreusuario"], $txt);
                $txt = str_replace('[CLAVE_USUARIO]', $clave, $txt);
                $txt = str_replace('[NOMBRE_ADMIN_PORTAL]', NOMBRE_ADMIN_PORTAL, $txt);
                $txt = str_replace('[RAZON_SOCIAL]', RAZONSOCIAL, $txt);
                $txt = str_replace('[SITIOWEB]', HTTP_HOST, $txt);
                $asunto = 'Cambio de clave para el usuario ' . ' ' . $arrUsrSis["idusuario"] . ' desde API-SII';
                $result = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["emailusuario"], $asunto, $txt);
                $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Se envia mail con cambio de clave';
            }
        }

        if ($detectaUsuario == 'no') {

            $criterioBusqueda = "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and "
                    . "email='" . $_SESSION["entrada"]["emailusuario"] . "'";

            $arrUsrReg = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', "estado='AP' and " . $criterioBusqueda);

            if (!$arrUsrReg || empty($arrUsrReg)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se detecta el usuario registrado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {

                // ********************************************************************** //
                // Actualizar clave del usuario registrado
                // ********************************************************************** // 

                $clave = rand(0654321, 9999999);
                $clavemd5 = md5($clave);

                $arrCampos = array(
                    'clave'
                );

                $arrValores = array(
                    "'" . $clavemd5 . "'"
                );

                regrabarRegistrosMysqliApi($mysqli, 'usuarios_registrados', $arrCampos, $arrValores, $criterioBusqueda);
                $txt = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, 'email.CambioClave'));
                $txt = str_replace('[ID_USUARIO]', $arrUsrReg["email"], $txt);
                $txt = str_replace('[NOMBRE_USUARIO]', $arrUsrReg["nombre"], $txt);
                $txt = str_replace('[CLAVE_USUARIO]', $clave, $txt);
                $txt = str_replace('[NOMBRE_ADMIN_PORTAL]', NOMBRE_ADMIN_PORTAL, $txt);
                $txt = str_replace('[RAZON_SOCIAL]', RAZONSOCIAL, $txt);
                $txt = str_replace('[SITIOWEB]', HTTP_HOST, $txt);
                $asunto = 'Cambio de clave para el usuario ' . ' ' . $arrUsrReg["nombre"] . ' desde API-SII';
                $result = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["emailusuario"], $asunto, $txt);
                $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Se envia mail con cambio de clave';
            }
        }


        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function confirmarRegistroUsuario(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
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
        $api->validarParametro("emailusuario", true);
        $api->validarParametro("celularusuario", true);
        $api->validarParametro("identificacionusuario", true);
        $api->validarParametro("confirmado", true);

        if (strlen($_SESSION["entrada"]["identificacionusuario"]) < 6) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La Identificación parece estar incorrectamente digitada.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!filter_var($_SESSION["entrada"]["emailusuario"], FILTER_VALIDATE_EMAIL) === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken(__FUNCTION__, $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el usuario previamente registrado 
        // ********************************************************************** // 

        $criterioBusqueda = "identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and "
                . "email='" . $_SESSION["entrada"]["emailusuario"] . "' and "
                . "celular='" . $_SESSION["entrada"]["celularusuario"] . "'";

        $arrUsrReg = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', "estado='PE' and " . $criterioBusqueda);

        if (!$arrUsrReg || empty($arrUsrReg)) {
            if (($_SESSION["entrada"]["confirmado"] == 'PE') || ($_SESSION["entrada"]["confirmado"] == 'EL')) {
                $arrCampos = array(
                    'estado',
                    'fechaactivacion'
                );

                $arrValores = array(
                    "'" . $_SESSION["entrada"]["confirmado"] . "'",
                    "'" . date("Ymd") . "'"
                );

                regrabarRegistrosMysqliApi($mysqli, 'usuarios_registrados', $arrCampos, $arrValores, $criterioBusqueda);
            } else {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se detecta la solicitud de registro del usuario.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {

            // ********************************************************************** //
            // Actualizar clave del usuario registrado
            // ********************************************************************** // 

            if ($_SESSION["entrada"]["confirmado"] == 'S') {
                $arrCampos = array(
                    'estado',
                    'fechaactivacion'
                );

                $arrValores = array(
                    "'AP'",
                    "'" . date("Ymd") . "'"
                );
            }
            if ($_SESSION["entrada"]["confirmado"] == 'N') {

                $arrCampos = array(
                    'estado',
                    'fechaactivacion'
                );

                $arrValores = array(
                    "'EL'",
                    "'00000000'"
                );
            }

            regrabarRegistrosMysqliApi($mysqli, 'usuarios_registrados', $arrCampos, $arrValores, $criterioBusqueda);
        }

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function solicitarRegistroUsuario(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
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
        $api->validarParametro("tipoidentificacion", true);
        $api->validarParametro("identificacion", true);
        $api->validarParametro("apellido1", true);
        $api->validarParametro("apellido2", false);
        $api->validarParametro("nombre1", true);
        $api->validarParametro("nombre2", false);
        $api->validarParametro("email", true);
        $api->validarParametro("celular", true);
        $api->validarParametro("fechanacimiento", true);
        $api->validarParametro("fechaexpediciondocumento", true);
        $api->validarParametro("sistemaorigen", false);
        $api->validarParametro("urlconfirmar", false);
        $api->validarParametro("urlrechazar", false);
        $api->validarParametro("urlterminos", false);
        $api->validarParametro("urldeclaracion", false);
        $api->validarParametro("urltratamientodatos", false);

        if ($_SESSION["entrada"]["sistemaorigen"] == '') {
            $_SESSION["entrada"]["sistemaorigen"] = 'SII';
        }

        if ($_SESSION["entrada"]["tipoidentificacion"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el tipo de identificación del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

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

        if ($_SESSION["entrada"]["apellido1"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el primer apellido del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["nombre1"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el primer nombre del usuario';
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

        if ($_SESSION["entrada"]["celular"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el celular del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if (strlen($_SESSION["entrada"]["celular"]) != 10) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El celular parece estar incorrectamente digitada.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["fechanacimiento"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la fecha de nacimiento del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {

            if (!(\funcionesSii2::validarFecha($_SESSION["entrada"]["fechanacimiento"]))) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha de nacimiento del usuario no es válida';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        if ($_SESSION["entrada"]["fechaexpediciondocumento"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la fecha de expedición de la identificación del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            if (!(\funcionesSii2::validarFecha($_SESSION["entrada"]["fechaexpediciondocumento"]))) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha de expedición de la identificación no es válida';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        if ($_SESSION["entrada"]["sistemaorigen"] == 'SII2') {
            if ($_SESSION["entrada"]["urlconfirmar"] == "" || $_SESSION["entrada"]["urlrechazar"] == "") {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar las URL de confirmación y rechazo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken(__FUNCTION__, $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $crearRegistro = 'no';

        // ********************************************************************** //
        // Validar si correo electrónico corresponde a un usuario del sistema
        // ********************************************************************** // 

        $queryBusquedaEmail = "email='" . trim($_SESSION["entrada"]["email"]) . "'";

        $arrUsrSis = retornarRegistroMysqliApi($mysqli, 'usuarios', $queryBusquedaEmail);
        if ($arrUsrSis || !empty($arrUsrSis)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9996";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La dirección ' . $_SESSION["entrada"]["email"] . ' se encuentra asociada a un usuario interno del sistema, por favor registrarse con un correo electrónico diferente.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el usuario previamente registrado y activado
        // ********************************************************************** // 

        $criterioBusqueda = "identificacion='" . $_SESSION["entrada"]["identificacion"] . "' and email='" . $_SESSION["entrada"]["email"] . "' and celular='" . $_SESSION["entrada"]["celular"] . "'";

        $arrTemAP = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', "estado='AP' and " . $criterioBusqueda);

        if (!$arrTemAP || empty($arrTemAP)) {

            $arrTemPE = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', "estado='PE' and " . $criterioBusqueda);

            if (!$arrTemPE || empty($arrTemPE)) {
                $crearRegistro = 'si';
                borrarRegistrosMysqliApi($mysqli, 'usuarios_registrados', "estado='EL' and " . $criterioBusqueda);
            } else {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario se encuentra pendiente de activación.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario se encuentra previamente registrado.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($crearRegistro == 'si') {
            // ********************************************************************** //
            // Creación del usuario registrado
            // ********************************************************************** // 

            $clave = rand(0654321, 9999999);
            $clavemd5 = md5($clave);

            $nombreCompleto = '';
            $nombreCompleto = $_SESSION["entrada"]["apellido1"] . ' ' . $_SESSION["entrada"]["apellido2"] . ' ' . $_SESSION["entrada"]["nombre1"] . ' ' . $_SESSION["entrada"]["nombre2"];

            $arrCampos = array(
                'email',
                'identificacion',
                'apellido1',
                'apellido2',
                'nombre1',
                'nombre2',
                'nombre',
                'celular',
                'clave',
                'fecharegistro',
                'fechaactivacion',
                'fechaultimoingreso',
                'intentoslogueo',
                'tipousuario',
                'estado'
            );

            $arrValores = array(
                "'" . addslashes($_SESSION["entrada"]["email"]) . "'",
                "'" . $_SESSION["entrada"]["identificacion"] . "'",
                "'" . mb_strtoupper(addslashes($_SESSION["entrada"]["apellido1"]), 'utf-8') . "'",
                "'" . mb_strtoupper(addslashes($_SESSION["entrada"]["apellido2"]), 'utf-8') . "'",
                "'" . mb_strtoupper(addslashes($_SESSION["entrada"]["nombre1"]), 'utf-8') . "'",
                "'" . mb_strtoupper(addslashes($_SESSION["entrada"]["nombre2"]), 'utf-8') . "'",
                "'" . mb_strtoupper(addslashes($nombreCompleto), 'utf-8') . "'",
                "'" . addslashes($_SESSION["entrada"]["celular"]) . "'",
                "'" . $clavemd5 . "'",
                "'" . date("Ymd") . "'",
                "''",
                "''",
                0,
                "'P'",
                "'PE'"
            );

            insertarRegistrosMysqliApi($mysqli, 'usuarios_registrados', $arrCampos, $arrValores);

            // ********************************************************************** //
            // Envio de mail de solicitud de activación de usuario registrado
            // ********************************************************************** // 
            $txt = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, 'email.Confirmacion.UsuarioRegistrado'));
            $txt = str_replace('[NOMBRE_USUARIO]', $nombreCompleto, $txt);
            $txt = str_replace('[IDENTIFICACION_USUARIO]', $_SESSION["entrada"]["identificacion"], $txt);
            $txt = str_replace('[EMAIL_USUARIO]', $_SESSION["entrada"]["email"], $txt);
            $txt = str_replace('[CLAVE_USUARIO]', $clave, $txt);
            $txt = str_replace('[RAZONSOCIAL]', RAZONSOCIAL, $txt);

            $parametros = base64_encode($_SESSION["entrada"]["email"] . '|' . $_SESSION["entrada"]["identificacion"] . '|' . $_SESSION["entrada"]["celular"]);

            $txConf = '';
            $txRech = '';
            $txTu = '';
            $txDp = '';
            $txTdp = '';

            if ($_SESSION["entrada"]["sistemaorigen"] = 'SII') {
                $txConf = '<a href="' . TIPO_HTTP . HTTP_HOST . '/disparador.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&accion=confirmarregistro&_parametros=' . $parametros . '">CONFIRMAR LA SOLICITUD DE REGISTRO</a>';
                $txRech = '<a href="' . TIPO_HTTP . HTTP_HOST . '/disparador.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&accion=rechazarregistro&_parametros=' . $parametros . '">RECHAZAR LA SOLICITUD DE REGISTRO</a>';
                $txTu = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/librerias/presentacion/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&pantalla=terminos.uso&mostrarenlaces=no' . '">TERMINOS DE USO DEL PORTAL WEB</a>';
                $txDp = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/librerias/presentacion/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&pantalla=declaracion.privacidad&mostrarenlaces=no' . '">DECLARACION DE PRIVACIDAD</a>';
                $txTdp = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/librerias/presentacion/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&pantalla=politica.tratamiento.datos.personales&mostrarenlaces=no' . '">POLITICA DE TRAMIENTO DE DATOS PERSONALES</a>';
            }

            if ($_SESSION["entrada"]["sistemaorigen"] = 'SII2') {
                $txConf = '<a href="' . $_SESSION["entrada"]["urlconfirmar"] . '">CONFIRMAR LA SOLICITUD DE REGISTRO</a>';
                $txRech = '<a href="' . $_SESSION["entrada"]["urlrechazar"] . '">RECHAZAR LA SOLICITUD DE REGISTRO</a>';
                $txTu = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/librerias/presentacion/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&pantalla=terminos.uso&mostrarenlaces=no' . '">TERMINOS DE USO DEL PORTAL WEB</a>';
                $txDp = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/librerias/presentacion/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&pantalla=declaracion.privacidad&mostrarenlaces=no' . '">DECLARACION DE PRIVACIDAD</a>';
                $txTdp = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/librerias/presentacion/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&pantalla=politica.tratamiento.datos.personales&mostrarenlaces=no' . '">POLITICA DE TRAMIENTO DE DATOS PERSONALES</a>';
            }


            $txt = str_replace('[CONFIRMAR]', $txConf, $txt);
            $txt = str_replace('[RECHAZAR]', $txRech, $txt);
            $txt = str_replace('[TERMINOSUSO]', $txTu, $txt);
            $txt = str_replace('[DECLARACIONPRIVACIDAD]', $txDp, $txt);
            $txt = str_replace('[TRATAMIENTODATOSPERSONALES]', $txTdp, $txt);
            $asunto = 'Solicitud de confirmacion de registro en el portal de ' . RAZONSOCIAL . ' desde Sistema Externo';
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

    public function retornarMenuUsuario(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');


        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["menu"] = '';

        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);

        //
        if (trim($_SESSION["entrada"]["idusuario"]) == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Código del usuario no debe ser vacío';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken(__FUNCTION__, $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Si el usuaruio es USUPUBXX no lee la tabla de usuarios pues se considera
        // que es un usuario publico
        // ********************************************************************** // 
        $usuariopublico = 'no';
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            $usuariopublico = 'si';
            $tipousuario = '00';
        } else {
            if (substr($_SESSION["entrada"]["idusuario"], 0, 6) == 'ADMGEN') {
                $usuariopublico = 'si';
                $tipousuario = '01';
            } else {

                $arrUsu = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
                if ($arrUsu === false || empty($arrUsu)) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no encontrado en la BD';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                if (ltrim((string)$arrUsu["fechaactivacion"], "0") == '') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no activado';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                if (ltrim((string)$arrUsu["fechainactivacion"], "0") != '') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario inactivado';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                $tipousuario = $arrUsu["idtipousuario"];
            }
        }

        //
        if (!defined('TIPO_EMPRESA')) {
            define('TIPO_EMPRESA', '');
        }
        if (!defined('TIPO_EMPRESA1')) {
            define('TIPO_EMPRESA1', '');
        }
        if (!defined('TIPO_EMPRESA2')) {
            define('TIPO_EMPRESA2', '');
        }

        // Carga la lista de opciones
        $arreglo = array();
        if ($tipousuario == '00') {
            $query = "sii2_mostrar = 'S' and  tipousuariopublico='X' and estado='1'";
        }
        if ($tipousuario == '01') {
            $query = "sii2_mostrar = 'S' and estado='1'";
        }
        if ($tipousuario == '02') {
            $query = "sii2_mostrar = 'S' and  tipousuarioadministrativo='X' and estado='1'";
        }
        if ($tipousuario == '03') {
            $query = "sii2_mostrar = 'S' and  tipousuarioproduccion='X' and estado='1'";
        }
        if ($tipousuario == '04') {
            $query = "sii2_mostrar = 'S' and  tipousuarioventas='X' and estado='1'";
        }
        if ($tipousuario == '05') {
            $query = "sii2_mostrar = 'S' and  tipousuarioregistro='X' and estado='1'";
        }
        if ($tipousuario == '06') {
            $query = "sii2_mostrar = 'S' and  tipousuarioexterno='X' and estado='1'";
        }

        //
        $result = retornarRegistrosMysqliApi($mysqli, "bas_opciones", $query, "idopcion");
        $i = 0;
        if ($result) {
            foreach ($result as $res) {
                if ($res["idtipoopcion"] == 'G') {
                    $i++;
                    $arreglo [$i] ["idopcion"] = $res["idopcion"];
                    $arreglo [$i] ["nombre"] = str_replace("<br>", " ", $res["nombre"]);
                    $arreglo [$i] ["cantsub"] = 0;
                    $arreglo [$i] ["subs"] = array();
                    $j = 0;
                    $k = 0;
                }
                if ($res["idtipoopcion"] == 'S') {
                    $j++;
                    $arreglo [$i] ["subs"][$j] ["idopcion"] = $res["idopcion"];
                    $arreglo [$i] ["subs"][$j] ["nombre"] = str_replace("<br>", " ", $res["nombre"]);
                    $arreglo [$i] ["subs"][$j] ["cantaccs"] = 0;
                    $arreglo [$i] ["subs"][$j] ["accs"] = array();
                    $k = 0;
                }
                if ($res["idtipoopcion"] == 'A') {
                    $k++;
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["idopcion"] = $res["idopcion"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["nombre"] = str_replace("<br>", " ", $res["nombre"]);
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["sii2_controlador"] = $res["sii2_controlador"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["sii2_metodo"] = $res["sii2_metodo"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["sii2_parametros"] = $res["sii2_parametros"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["sii2_ajax"] = $res["sii2_ajax"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["enlace"] = $res["enlace"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["script"] = $res["script"];
                    if (isset($arreglo [$i] ["subs"][$j] ["cantaccs"])) {
                        $arreglo [$i] ["subs"][$j] ["cantaccs"]++;
                        $arreglo [$i] ["cantsub"]++;
                    }
                }
            }
        }

        $json = '[';
        $i1 = 0;
        foreach ($arreglo as $ar) {
            if ($ar["cantsub"] != 0) {
                $i1++;
                if ($i1 > 1) {
                    $json .= ',';
                }
                $json .= '{"parent":"1" ,"titulo":"' . $ar["nombre"] . '","child":"2","opc":[';
                $i2 = 0;
                foreach ($ar["subs"] as $arsub) {
                    if (isset($arsub["cantaccs"]) && $arsub["cantaccs"] != 0) {
                        $i2++;
                        if ($i2 > 1) {
                            $json .= ',';
                        }
                        $json .= '{"parent":"2" ,"titulo":"' . $arsub["nombre"] . '","child":"3","opc":[';
                        $i3 = 0;
                        foreach ($arsub["accs"] as $aracc) {
                            $i3++;
                            if ($i3 > 1) {
                                $json .= ',';
                            }
                            if ($aracc["sii2_controlador"] != '') {
                                $json .= '{"parent":"3" ,"titulo":"' . $aracc["nombre"] . '","x":"' . $aracc["sii2_controlador"] . '","y":"' . $aracc["sii2_metodo"] . '","z":"' . $aracc["sii2_ajax"] . '"}';
                            } else {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
                                $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
                                $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = $aracc["script"];
                                $arr["accion"] = 'seleccion';
                                $arr["parametros"] = array();
                                $jsonx = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerelaes::encriptar($jsonx, $claveEncriptacion));
                                $enlx = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $json .= '{"parent":"3" ,"titulo":"' . $aracc["nombre"] . '","href":"' . $enlx . '"}';
                            }
                        }
                        $json .= ']}';
                    }
                }
                $json .= ']}';
            }
        }
        $json .= ']';

        $_SESSION["jsonsalida"]["menu"] = $json;

        $salida = '{ ';
        $salida .= '"codigoerror":"0000",';
        $salida .= '"mensajeerror":"",';
        $salida .= '"menu":' . $json;
        $salida .= ' }';

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        // $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $salida), 200);
    }

}
