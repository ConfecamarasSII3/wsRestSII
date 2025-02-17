<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait solicitarRegistro {

    public function solicitarRegistro(API $api) {

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
        $api->validarParametro("pais", false);
        $api->validarParametro("municipio", false);
        $api->validarParametro("direccion", false);

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
            if (!(\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechanacimiento"]))) {
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
            if (!(\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechaexpediciondocumento"]))) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha de expedición de la identificación no es válida';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('solicitarRegistro', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el usuario previamente registrado y activado
        // ********************************************************************** // 

        $crearRegistro = 'no';

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
            $clavemd5 = password_hash($clave, PASSWORD_DEFAULT);

            $nombreCompleto = '';
            $nombreCompleto = $_SESSION["entrada"]["apellido1"] . ' ' . $_SESSION["entrada"]["apellido2"] . ' ' . $_SESSION["entrada"]["nombre1"] . ' ' . $_SESSION["entrada"]["nombre2"];

            $arrCampos = array(
                'email',
                'identificacion',
                'tipoidentificacion',
                'apellido1',
                'apellido2',
                'nombre1',
                'nombre2',
                'nombre',
                'celular',
                'direccion',
                'municipio',
                'pais',
                'clave',
                'fecharegistro',
                'fechaactivacion',
                'fechaultimoingreso',
                'intentoslogueo',
                'tipousuario',
                'estado',
                'fechanacimiento',
                'fechaexpedicion'
            );

            $arrValores = array(
                "'" . addslashes($_SESSION["entrada"]["email"]) . "'",
                "'" . $_SESSION["entrada"]["identificacion"] . "'",
                "'" . $_SESSION["entrada"]["tipoidentificacion"] . "'",
                "'" . addslashes($_SESSION["entrada"]["apellido1"]) . "'",
                "'" . addslashes($_SESSION["entrada"]["apellido2"]) . "'",
                "'" . addslashes($_SESSION["entrada"]["nombre1"]) . "'",
                "'" . addslashes($_SESSION["entrada"]["nombre2"]) . "'",
                "'" . addslashes($nombreCompleto) . "'",
                "'" . addslashes($_SESSION["entrada"]["celular"]) . "'",
                "'" . addslashes(trim($_SESSION["entrada"]["direccion"])) . "'",
                "'" . trim($_SESSION["entrada"]["municipio"]) . "'",
                "'" . trim($_SESSION["entrada"]["pais"]) . "'",
                "'" . $clavemd5 . "'",
                "'" . date("Ymd") . "'",
                "''",
                "''",
                0,
                "'P'",
                "'PE'",
                "'" . $_SESSION["entrada"]["fechanacimiento"] . "'",
                "'" . $_SESSION["entrada"]["fechaexpediciondocumento"] . "'"
            );

            insertarRegistrosMysqliApi($mysqli, 'usuarios_registrados', $arrCampos, $arrValores);

            // ********************************************************************** //
            // Envio de mail de solicitud de activación de usuario registrado
            // ********************************************************************** // 
            $txt = \funcionesGenerales::cambiarSustitutoHtml(retornarPantallaPredisenadaMysqliApi($mysqli, 'email.Confirmacion.UsuarioRegistrado'));
            $txt = str_replace('[NOMBRE_USUARIO]', $_SESSION["entrada"]["nombre1"] . ' ' . $_SESSION["entrada"]["nombre2"] . ' ' . $_SESSION["entrada"]["apellido1"] . ' ' . $_SESSION["entrada"]["apellido2"], $txt);
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

            $txConf = '<a href="' . TIPO_HTTP . HTTP_HOST . '/scripts/manejarRegistro.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&accion=confirmarregistro&_parametros=' . $parametros . '">CONFIRMAR LA SOLICITUD DE REGISTRO</a>';
            $txRech = '<a href="' . TIPO_HTTP . HTTP_HOST . '/scripts/manejarRegistro.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&accion=rechazarregistro&_parametros=' . $parametros . '">RECHAZAR LA SOLICITUD DE REGISTRO</a>';
            $txTu = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/scripts/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&accion=mostrarpantallas&pantalla=terminos.uso&mostrarenlaces=no' . '">TERMINOS DE USO DEL PORTAL WEB</a>';
            $txDp = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/scripts/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&accion=mostrarpantallas&pantalla=declaracion.privacidad&mostrarenlaces=no' . '">DECLARACION DE PRIVACIDAD</a>';
            $txTdp = '<a target="_blank" href="' . TIPO_HTTP . HTTP_HOST . '/scripts/mostrarPantallas.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&accion=mostrarpantallas&pantalla=politica.tratamiento.datos.personales&mostrarenlaces=no' . '">POLITICA DE TRAMIENTO DE DATOS PERSONALES</a>';

            $txt = str_replace('[CONFIRMAR]', $txConf, $txt);
            $txt = str_replace('[RECHAZAR]', $txRech, $txt);
            $txt = str_replace('[TERMINOSUSO]', $txTu, $txt);
            $txt = str_replace('[DECLARACIONPRIVACIDAD]', $txDp, $txt);
            $txt = str_replace('[TRATAMIENTODATOSPERSONALES]', $txTdp, $txt);
            $asunto = 'Solicitud de confirmacion de registro en el portal de ' . RAZONSOCIAL . ' desde Sistema Externo';
            $result = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["email"], $asunto, $txt);
            if ($result === false) {
                sleep(5);
                $result = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["email"], $asunto, $txt);
                if ($result === false) {
                    sleep(5);
                    $result = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["email"], $asunto, $txt);
                    if ($result === false) {
                        sleep(5);
                        $result = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["email"], $asunto, $txt);
                    }
                }
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

}
