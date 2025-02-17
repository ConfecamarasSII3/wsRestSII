<?php

namespace api;

use api\API;

trait digitar {

    public function digitarSaveCaseFile(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }
        // \logApi::general2('digitarSaveCaseFile_' . date ("Ymd"), '', 'Abrio conexion con BD');
        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        \logApi::peticionRest('api_' . __FUNCTION__);

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tramite", false);
        $api->validarParametro("matricula", true);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // \logApi::general2('digitarSaveCaseFile_' . date ("Ymd"), '', 'Leyo parametros');
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarSaveCaseFile', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarSaveCaseFile', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        if (!isset($_SESSION["entrada"]["tramite"])) {
            $_SESSION["entrada"]["tramite"] = '';
        } else {
            $_SESSION["entrada"]["tramite"] = $_SESSION["entrada"]["tramite"];
        }
        $_SESSION["entrada"]["matricula"] = base64_decode($_SESSION["entrada"]["matricula"]);

        // Lee el expediente
        $expe = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
        if ($expe === false || empty($expe)) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-enrollment-not-localized-in-register') . '(' . $_SESSION["entrada"]["matricula"] . ')';
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }
        \logApi::general2('digitarSaveCaseFile_' . date("Ymd"), '', 'Leyo expediente ' . $_SESSION["entrada"]["matricula"]);

        //
        $tLog = "Expediente recuperado para la matricula " . $_SESSION["entrada"]["matricula"] . " : \r\n";
        foreach ($expe as $key => $valor) {
            if (!is_array($valor)) {
                $tLog .= $key . " => " . $valor . "\r\n";
            }
        }
        $tLog .= "\r\n";
        \logApi::general2('digitarSaveCaseFile_' . date("Ymd"), '', $tLog);

        // lee los parámetros de entrada1 para actualizar los datos de expe
        $tLog = "Parametros recibos para la matricula " . $_SESSION["entrada"]["matricula"] . " : \r\n";
        foreach ($_SESSION["entrada"] as $par => $value) {
            if ($par != 'session_parameters' &&
                    $par != 'matricula' &&
                    $par != 'usuariows' &&
                    $par != 'token' &&
                    $par != 'acceso' &&
                    $par != 'codigoempresa') {
                if ($par == 'fecmatricula' ||
                        $par == 'fecconstitucion' ||
                        $par == 'fecrenovacion' ||
                        $par == 'fecdisolucion' ||
                        $par == 'fecliquidacion' ||
                        $par == 'feccancelacion' ||
                        $par == 'fecvigencia' ||
                        $par == 'fecdocconstitucion' ||
                        $par == 'fecmatriculacamara' ||
                        $par == 'fecrenovacioncamara' ||
                        $par == 'feccancelacioncamara') {
                    $value = str_replace("/", "", base64_decode($value));
                } else {
                    $value = base64_decode(str_replace(" ", "+", $value));
                }
                $tLog .= $par . " => " . $value . "\r\n";
                $expe[$par] = trim($value);
            }
        }
        \logApi::general2('digitarSaveCaseFile_' . date("Ymd"), '', $tLog);

        //
        $codmun = '';
        $muns = retornarRegistrosTablasMysqliApi($mysqli, 'municipios');
        foreach ($muns as $mun) {
            $tmun = $mun["descripcion"] . ' (' . $mun["campo1"] . ')';
            if ($expe["mundocconstitucion"] == $tmun) {
                $codmun = $mun["idcodigo"];
            }
        }
        $expe["mundocconstitucion"] = $codmun;
        unset($muns);

        //
        $codori = '';
        $oris = retornarRegistrosTablasMysqliApi($mysqli, 'origenes');
        foreach ($oris as $ori) {
            if ($expe["oridocconstitucion"] == $ori["descripcion"]) {
                $codori = $ori["idcodigo"];
            }
        }
        $expe["oridocconstitucion"] = $codori;
        unset($oris);

        //
        $tLog = "Expediente a almacenar para la matricula " . $_SESSION["entrada"]["matricula"] . " : \r\n";
        foreach ($expe as $key => $valor) {
            if (!is_array($valor)) {
                $tLog .= $key . " => " . $valor . "\r\n";
            }
        }
        $tLog .= "\r\n";
        \logApi::general2('digitarSaveCaseFile_' . date("Ymd"), '', $tLog);

        // Actualiza el expediente
        $res = \funcionesRegistrales::actualizarExpedienteMercantil($mysqli, $expe, $_SESSION["entrada"]["tramite"]);
        if ($res === false) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }
        \logApi::general2('digitarSaveCaseFile_' . date("Ymd"), '', 'Retorno de actualizarExpedienteMercantil');

        // ********************************************************************************* //
        // revisa si el kardes de constitución existe, de no existir, procede a crearlo
        // Siempre y cuando se hubiere digitado la información de constitución
        // ********************************************************************************* //
        if ($expe["libroconstitucion"] != '') {
            $condicion = "libroinscripciones=" . $expe["libroconstitucion"] . "' and ";
            $condicion .= "tomoinscripciones=" . $expe["tomoconstitucion"] . "' and ";
            $condicion .= "registroinscripciones=" . $expe["registroconstitucion"] . "'";
            $lib = retornarRegistroMysqliApi($mysqli, 'inscripciones', $condicion);
            if ($lib === false || empty($lib)) {
                $arrCampos = array(
                    'libroinscripciones',
                    'tomoinscripciones',
                    'registroinscripciones',
                    'dupliinscripciones',
                    'fecharegistroinscripciones',
                    'horaregistroinscripciones',
                    'matriculainscripciones',
                    'organizacioninscripciones',
                    'categoriainscripciones',
                    'naturalezainscripciones',
                    'idclaseinscripciones',
                    'numidinscripciones',
                    'nombreinscripciones',
                    'nombrecomercialinscripciones',
                    'fecdocinscripciones',
                    'idorigendocinscripciones',
                    'origendocinscripciones',
                    'mundocinscripciones',
                    'paisdocinscripciones',
                    'numdocinscripciones',
                    'idtipodocinscripciones',
                    'actoinscripciones',
                    'noticiainscripciones',
                    'usuarioinscripciones',
                    'reciboinscripciones',
                    'numerooperacioninscripciones',
                    'radicadoinscripciones',
                    'fecharadicadoinscripciones',
                    'usuarioinscribeinscripciones',
                    'ctrnotificacioninscripciones',
                    'ctrrevocainscripciones',
                    'idanexoselloinscripciones',
                    'idnotificacionemailinscripciones',
                    'estadoinscripciones',
                    'esreformainscripciones',
                    'espoderinscripciones',
                    'esaprobacionbalancesinscripciones',
                    'esnombramientosinscripciones',
                    'esmedidapreventivainscripciones',
                    'esprendainscripciones'
                );

                $noticia = '';
                if ($expe["organizacion"] == '01') {
                    $noticia = 'CONSTITUCION DE COMERCIANTE INDIVIDUAL DENOMINADO ' . $expe["razonsocial"];
                }
                if ($expe["organizacion"] == '02') {
                    $noticia = 'APERTURA DE ESTABLECIMIENTO DE COMERCIO DENOMINADO ' . $expe["razonsocial"];
                }
                if ($expe["organizacion"] > '02') {
                    $noticia = 'CONSTITUCION DE COMERCIANTE SOCIETARIO DENOMINADO ' . $expe["razonsocial"];
                }

                //
                $arrValores = array(
                    "'" . $expe["libroconstitucion"] . "'",
                    "'" . $expe["tomoconstitucion"] . "'",
                    "'" . $expe["registroconstitucion"] . "'",
                    "'01'",
                    "'" . $expe["fecmatricula"] . "'",
                    "'000000'",
                    "'" . $expe["matricula"] . "'",
                    "'" . $expe["organizacion"] . "'", // organizacion
                    "'" . $expe["categoria"] . "'", // Categoria
                    "''", // naturaleza
                    "'" . $expe["idclase"] . "'", // idclase
                    "'" . $expe["numid"] . "'", // numid
                    "'" . $expe["razonsocial"] . "'",
                    "'" . $expe["nombrecomercial"] . "'",
                    "'" . $expe["fecdocconstitucion"] . "'",
                    "'" . $expe["oridocconstitucion"] . "'",
                    "''", // origendoc textual
                    "'" . $expe["mundocconstitucion"] . "'",
                    "''", // paisdoc
                    "'" . $expe["numdocconstitucion"] . "'",
                    "'" . $expe["tipodocconstitucion"] . "'",
                    "'0040'",
                    "'" . $noticia . "'",
                    "'" . $_SESSION["generales"]["codigousuario"] . "'",
                    "''", // Recibo inscripciones
                    "''", // Numero operacion
                    "''",
                    "''", // fecha radicado inscripciones
                    "'" . $_SESSION["generales"]["codigousuario"] . "'",
                    "'0'", // Notificado
                    "'0'", // Revocado
                    0, // id anexo sello
                    0, // id notificación email
                    "'V'",
                    "''",
                    "''",
                    "''",
                    "''",
                    "''",
                    "''"
                );
                insertarRegistrosMysqliApi($mysqli, 'inscripciones', $arrCampos, $arrValores);
            }
        }
        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarCapitalFormSave(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        require_once ('myErrorHandler.php');
        set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        // ********************************************************************** //
        // Conexion con mysql
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que la peticion sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida parámetros recibidos
        // ********************************************************************** //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("id", false);
        $api->validarParametro("tramite", false);
        $api->validarParametro("matricula", true);
        $api->validarParametro("registrocap", true);
        $api->validarParametro("anocap", true);
        $api->validarParametro("fechacap", true);
        $api->validarParametro("nomanifestadocap", true);
        $api->validarParametro("valorcap", false);
        $api->validarParametro("valorminimocap", false);
        $api->validarParametro("valormaximocap", false);
        $api->validarParametro("cuotascap", false);
        $api->validarParametro("nominalcap", false);
        $api->validarParametro("accionpreferencialcap", false);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarCapitalFormSave', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarCapitalFormSave', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["id"] = base64_decode($_SESSION["entrada"]["id"]);
        if (ltrim(trim($_SESSION["entrada"]["id"]), "0") == '') {
            $_SESSION["entrada"]["id"] = 0;
        }
        $_SESSION["entrada"]["tramite"] = base64_decode($_SESSION["entrada"]["tramite"]);
        $_SESSION["entrada"]["matricula"] = base64_decode($_SESSION["entrada"]["matricula"]);
        $_SESSION["entrada"]["registrocap"] = base64_decode($_SESSION["entrada"]["registrocap"]);
        $_SESSION["entrada"]["anocap"] = base64_decode($_SESSION["entrada"]["anocap"]);
        $_SESSION["entrada"]["fechacap"] = base64_decode(str_replace(array("-", "/"), "", $_SESSION["entrada"]["fechacap"]));
        $_SESSION["entrada"]["nomanifestadocap"] = base64_decode($_SESSION["entrada"]["nomanifestadocap"]);
        $_SESSION["entrada"]["valorcap"] = doubleval(base64_decode($_SESSION["entrada"]["valorcap"]));
        $_SESSION["entrada"]["valorminimocap"] = doubleval(base64_decode($_SESSION["entrada"]["valorminimocap"]));
        $_SESSION["entrada"]["valormaximocap"] = doubleval(base64_decode($_SESSION["entrada"]["valormaximocap"]));
        $_SESSION["entrada"]["cuotascap"] = doubleval(base64_decode($_SESSION["entrada"]["cuotascap"]));
        $_SESSION["entrada"]["nominalcap"] = doubleval(base64_decode($_SESSION["entrada"]["nominalcap"]));
        $_SESSION["entrada"]["accionpreferencialcap"] = base64_decode($_SESSION["entrada"]["accionpreferencialcap"]);


        //
        list ($libx, $tomx, $regx, $dupx) = \explode("-", $_SESSION["entrada"]["registrocap"]);
        $rins = retornarRegistroMysqliApi($mysqli, 'inscripciones', "matriculainscripciones='" . $_SESSION["entrada"]["matricula"] . "' and libroinscripciones='" . $libx . "' and tomoinscripciones='" . $tomx . "' and registroinscripciones='" . $regx . "' and dupliinscripciones='" . $dupx . "'");
        if ($rins === false) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["fechacap"] = $rins["fecharegistroinscripciones"];
        $_SESSION["entrada"]["anocap"] = substr($rins["fecharegistroinscripciones"], 0, 4);

        //
        $arrCampos = array(
            'matriculacap',
            'anodatoscap',
            'fechadatoscap',
            'registrocap',
            'nomanifestadocap',
            'valorsocialcap',
            'valorsocialminimocap',
            'valorsocialmaximocap',
            'cuotascap',
            'valornominalcap',
            'accionpreferencialcap',
            'fecsincronizacioncap',
            'horsincronizacioncap'
        );

        //
        $arrValores = array(
            "'" . $_SESSION["entrada"]["matricula"] . "'",
            "'" . $_SESSION["entrada"]["anocap"] . "'",
            "'" . $_SESSION["entrada"]["fechacap"] . "'",
            "'" . $_SESSION["entrada"]["registrocap"] . "'",
            "'" . $_SESSION["entrada"]["nomanifestadocap"] . "'",
            $_SESSION["entrada"]["valorcap"],
            $_SESSION["entrada"]["valorminimocap"],
            $_SESSION["entrada"]["valormaximocap"],
            $_SESSION["entrada"]["cuotascap"],
            $_SESSION["entrada"]["nominalcap"],
            "'" . $_SESSION["entrada"]["accionpreferencialcap"] . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'"
        );

        //
        if ($_SESSION["entrada"]["id"] == 0) {
            $res = insertarRegistrosMysqliApi($mysqli, 'capitales', $arrCampos, $arrValores);
            $cap = retornarRegistroMysqliApi($mysqli, 'capitales', "id=" . $_SESSION["generales"]["lastId"]);
            $detalle = 'Creó capital' . "\r\n";
            $detalle .= 'Matrícula = ' . $cap["matriculacap"] . "\r\n";
            $detalle .= 'Anodatos = ' . $cap["anodatoscap"] . "\r\n";
            $detalle .= 'Fechadatos = ' . $cap["fechadatoscap"] . "\r\n";
            $detalle .= 'Registro = ' . $cap["registrocap"] . "\r\n";
            $detalle .= 'NoManifestado = ' . $cap["nomanifestadocap"] . "\r\n";
            $detalle .= 'ValorSocial = ' . $cap["valosocialcap"] . "\r\n";
            $detalle .= 'ValorSocialMinimo = ' . $cap["valosocialminimocap"] . "\r\n";
            $detalle .= 'ValorSocialMaximo = ' . $cap["valosocialmaximocap"] . "\r\n";
            $detalle .= 'Cuotas = ' . $cap["cuotascap"] . "\r\n";
            $detalle .= 'ValorNominal = ' . $cap["valornominalcal"] . "\r\n";
            $detalle .= 'AccionPreferencial = ' . $cap["accionpreferencialcap"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '002', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        } else {
            $cap = retornarRegistroMysqliApi($mysqli, 'capitales', "id=" . $_SESSION["entrada"]["id"]);
            $res = regrabarRegistrosMysqliApi($mysqli, 'capitales', $arrCampos, $arrValores, "id=" . $_SESSION["entrada"]["id"]);
            $cap1 = retornarRegistroMysqliApi($mysqli, 'capitales', "id=" . $_SESSION["entrada"]["id"]);
            $detalle = 'Actualizó capital' . "\r\n";
            $detalle .= 'Matrícula = ' . $cap["matriculacap"] . "\r\n";
            $detalle .= 'Anodatos = ' . $cap["anodatoscap"] . "\r\n";
            $detalle .= 'Fechadatos = ' . $cap["fechadatoscap"] . "\r\n";
            $detalle .= 'Registro = ' . $cap["registrocap"] . "\r\n";
            $detalle .= 'NoManifestado = ' . $cap["nomanifestadocap"] . "\r\n";
            $detalle .= 'ValorSocial = ' . $cap["valosocialcap"] . "\r\n";
            $detalle .= 'ValorSocialMinimo = ' . $cap["valosocialminimocap"] . "\r\n";
            $detalle .= 'ValorSocialMaximo = ' . $cap["valosocialmaximocap"] . "\r\n";
            $detalle .= 'Cuotas = ' . $cap["cuotascap"] . "\r\n";
            $detalle .= 'ValorNominal = ' . $cap["valornominalcal"] . "\r\n";
            $detalle .= 'AccionPreferencial = ' . $cap["accionpreferencialcap"] . "\r\n";
            $detalle .= 'Por ' . "\r\n";
            $detalle .= 'Matrícula = ' . $cap1["matriculacap"] . "\r\n";
            $detalle .= 'Anodatos = ' . $cap1["anodatoscap"] . "\r\n";
            $detalle .= 'Fechadatos = ' . $cap1["fechadatoscap"] . "\r\n";
            $detalle .= 'Registro = ' . $cap1["registrocap"] . "\r\n";
            $detalle .= 'NoManifestado = ' . $cap1["nomanifestadocap"] . "\r\n";
            $detalle .= 'ValorSocial = ' . $cap1["valosocialcap"] . "\r\n";
            $detalle .= 'ValorSocialMinimo = ' . $cap1["valosocialminimocap"] . "\r\n";
            $detalle .= 'ValorSocialMaximo = ' . $cap1["valosocialmaximocap"] . "\r\n";
            $detalle .= 'Cuotas = ' . $cap1["cuotascap"] . "\r\n";
            $detalle .= 'ValorNominal = ' . $cap1["valornominalcal"] . "\r\n";
            $detalle .= 'ValorNominal = ' . $cap1["accionpreferencialcap"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '003', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        if ($res === false) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarCapitalDelete(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tramite", false);
        $api->validarParametro("id", true);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarCapitalDelete', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarCapitalDelete', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["id"] = base64_decode($_SESSION["entrada"]["id"]);
        if (ltrim(trim($_SESSION["entrada"]["id"]), "0") == '') {
            $_SESSION["entrada"]["id"] = 0;
        }

        if ($_SESSION["entrada"]["id"] != 0) {
            $cap = retornarRegistroMysqliApi($mysqli, 'capitales', "id=" . $_SESSION["entrada"]["id"]);
            $detalle = 'Borró capital' . "\r\n";
            $detalle .= 'Matrícula = ' . $cap["matriculacap"] . "\r\n";
            $detalle .= 'Anodatos = ' . $cap["anodatoscap"] . "\r\n";
            $detalle .= 'Fechadatos = ' . $cap["fechadatoscap"] . "\r\n";
            $detalle .= 'Registro = ' . $cap["registrocap"] . "\r\n";
            $detalle .= 'NoManifestado = ' . $cap["nomanifestadocap"] . "\r\n";
            $detalle .= 'ValorSocial = ' . $cap["valorsocialcap"] . "\r\n";
            $detalle .= 'ValorSocialMinimo = ' . $cap["valorsocialminimocap"] . "\r\n";
            $detalle .= 'ValorSocialMaximo = ' . $cap["valorsocialmaximocap"] . "\r\n";
            $detalle .= 'Cuotas = ' . $cap["cuotascap"] . "\r\n";
            $detalle .= 'ValorNominal = ' . $cap["valornominalcap"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '004', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $cap["matriculacap"]);
            borrarRegistrosMysqliApi($mysqli, 'capitales', "id=" . $_SESSION["entrada"]["id"]);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarVinculoFormSave(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        require_once ('myErrorHandler.php');
        set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        // ********************************************************************** //
        // Conexion con mysql
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que la peticion sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida parámetros recibidos
        // ********************************************************************** //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tramite", false);
        $api->validarParametro("id", false);
        $api->validarParametro("matricula", true);
        $api->validarParametro("registrovinc", true);
        $api->validarParametro("idclase", false);
        $api->validarParametro("numid", false);
        $api->validarParametro("razonsocialvinc", false);
        $api->validarParametro("vinculo", false);
        $api->validarParametro("descargo", false);
        $api->validarParametro("cuotasconst", false);
        $api->validarParametro("cuotasref", false);
        $api->validarParametro("valorconst", false);
        $api->validarParametro("porcconst", false);
        $api->validarParametro("porcref", false);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters 
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarVinculoFormSave', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarVinculoFormSave', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["id"] = base64_decode($_SESSION["entrada"]["id"]);
        if (ltrim(trim($_SESSION["entrada"]["id"]), "0") == '') {
            $_SESSION["entrada"]["id"] = 0;
        }
        $_SESSION["entrada"]["tramite"] = base64_decode($_SESSION["entrada"]["tramite"]);
        $_SESSION["entrada"]["matricula"] = base64_decode($_SESSION["entrada"]["matricula"]);
        $_SESSION["entrada"]["registrovinc"] = base64_decode($_SESSION["entrada"]["registrovinc"]);
        $_SESSION["entrada"]["idclase"] = base64_decode($_SESSION["entrada"]["idclase"]);
        $_SESSION["entrada"]["numid"] = base64_decode($_SESSION["entrada"]["numid"]);
        $_SESSION["entrada"]["razonsocialvinc"] = base64_decode($_SESSION["entrada"]["razonsocialvinc"]);
        $_SESSION["entrada"]["vinculo"] = base64_decode($_SESSION["entrada"]["vinculo"]);
        $_SESSION["entrada"]["descargo"] = base64_decode($_SESSION["entrada"]["descargo"]);
        $_SESSION["entrada"]["cuotasconst"] = doubleval(base64_decode($_SESSION["entrada"]["cuotasconst"]));
        $_SESSION["entrada"]["valorconst"] = doubleval(base64_decode($_SESSION["entrada"]["valorconst"]));
        $_SESSION["entrada"]["porcconst"] = doubleval(base64_decode($_SESSION["entrada"]["porcconst"]));
        $_SESSION["entrada"]["cuotasref"] = doubleval(base64_decode($_SESSION["entrada"]["cuotasref"]));
        $_SESSION["entrada"]["valorref"] = doubleval(base64_decode($_SESSION["entrada"]["valorref"]));
        $_SESSION["entrada"]["porcref"] = doubleval(base64_decode($_SESSION["entrada"]["porcref"]));

        //
        list ($libx, $tomx, $regx, $dupx) = \explode("-", $_SESSION["entrada"]["registrovinc"]);
        $rins = retornarRegistroMysqliApi($mysqli, 'inscripciones', "matriculainscripciones='" . $_SESSION["entrada"]["matricula"] . "' and libroinscripciones='" . $libx . "' and tomoinscripciones='" . $tomx . "' and registroinscripciones='" . $regx . "' and dupliinscripciones='" . $dupx . "'");
        if ($rins === false) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Error : ' . $_SESSION["generales"]["mensajeerror"];
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }
        if (empty($rins)) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-register-not-found') . ' (' . $_SESSION["entrada"]["registrovinc"] . ')';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["fecha"] = $rins["fecharegistroinscripciones"];

        //
        $arrCampos = array(
            'matriculavinc',
            'idclasevinc',
            'numidvinc',
            'razonsocialvinc',
            'vinculovinc',
            'descargovinc',
            'registrovinc',
            'fecharegistrovinc',
            'cuotasconstvinc',
            'valorconstvinc',
            'porcconstvinc',
            'cuotasrefvinc',
            'valorrefvinc',
            'porcrefvinc',
            'estadovinc',
            'fechahistoricovinc',
            'usuariohistoricovinc',
            'fecsincronizacionvinc',
            'horsincronizacionvinc'
        );

        //
        $arrValores = array(
            "'" . $_SESSION["entrada"]["matricula"] . "'",
            "'" . $_SESSION["entrada"]["idclase"] . "'",
            "'" . $_SESSION["entrada"]["numid"] . "'",
            "'" . addslashes($_SESSION["entrada"]["razonsocialvinc"]) . "'",
            "'" . $_SESSION["entrada"]["vinculo"] . "'",
            "'" . addslashes($_SESSION["entrada"]["descargo"]) . "'",
            "'" . $_SESSION["entrada"]["registrovinc"] . "'",
            "'" . $_SESSION["entrada"]["fecha"] . "'",
            doubleval($_SESSION["entrada"]["cuotasconst"]),
            doubleval($_SESSION["entrada"]["valorconst"]),
            doubleval($_SESSION["entrada"]["porcconst"]),
            doubleval($_SESSION["entrada"]["cuotasref"]),
            doubleval($_SESSION["entrada"]["valorref"]),
            doubleval($_SESSION["entrada"]["porcref"]),
            "'V'", // Estado
            "''", // Fecha historico
            "''", // Usuario historico
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'"
        );

        //
        if ($_SESSION["entrada"]["id"] == 0) {
            $res = insertarRegistrosMysqliApi($mysqli, 'vinculos', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $vinc = retornarRegistroMysqliApi($mysqli, 'vinculos', "id=" . $_SESSION["generales"]["lastId"]);
            $detalle = 'Creó vínculo' . "\r\n";
            $detalle .= 'Matrícula = ' . $vinc["matriculavinc"] . "\r\n";
            $detalle .= 'Vínculo = ' . $vinc["vinculovinc"] . "\r\n";
            $detalle .= 'Idclase = ' . $vinc["idclasevinc"] . "\r\n";
            $detalle .= 'Numid = ' . $vinc["numidvinc"] . "\r\n";
            $detalle .= 'Nombre = ' . $vinc["razonsocialvinc"] . "\r\n";
            $detalle .= 'Cargo = ' . $vinc["idcargovinc"] . "\r\n";
            $detalle .= 'DesCargo = ' . $vinc["descargovinc"] . "\r\n";
            $detalle .= 'Registro = ' . $vinc["registrovinc"] . "\r\n";
            $detalle .= 'CuotasConst = ' . $vinc["cuotasconstvinc"] . "\r\n";
            $detalle .= 'CuotasRef = ' . $vinc["cuotasrefvinc"] . "\r\n";
            $detalle .= 'CuotasRef = ' . $vinc["cuotasrefvinc"] . "\r\n";
            $detalle .= 'ValorConst = ' . $vinc["valorconstvinc"] . "\r\n";
            $detalle .= 'ValorRef = ' . $vinc["valorrefvinc"] . "\r\n";
            $detalle .= 'PorcConst = ' . $vinc["porcconstvinc"] . "\r\n";
            $detalle .= 'PorcRef = ' . $vinc["porcrefvinc"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '002', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        } else {
            $vinc = retornarRegistroMysqliApi($mysqli, 'vinculos', "id=" . $_SESSION["entrada"]["id"]);
            $res = regrabarRegistrosMysqliApi($mysqli, 'vinculos', $arrCampos, $arrValores, "id=" . $_SESSION["entrada"]["id"]);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizo vínculo' . "\r\n";
            $detalle .= 'Matrícula = ' . $vinc["matriculavinc"] . "\r\n";
            $detalle .= 'Vínculo = ' . $vinc["vinculovinc"] . "\r\n";
            $detalle .= 'Idclase = ' . $vinc["idclasevinc"] . "\r\n";
            $detalle .= 'Numid = ' . $vinc["numidvinc"] . "\r\n";
            $detalle .= 'Nombre = ' . $vinc["razonsocialvinc"] . "\r\n";
            $detalle .= 'Cargo = ' . $vinc["idcargovinc"] . "\r\n";
            $detalle .= 'DesCargo = ' . $vinc["descargovinc"] . "\r\n";
            $detalle .= 'Registro = ' . $vinc["registrovinc"] . "\r\n";
            $detalle .= 'CuotasConst = ' . $vinc["cuotasconstvinc"] . "\r\n";
            $detalle .= 'CuotasRef = ' . $vinc["cuotasrefvinc"] . "\r\n";
            $detalle .= 'ValorConst = ' . $vinc["valorconstvinc"] . "\r\n";
            $detalle .= 'ValorRef = ' . $vinc["valorrefvinc"] . "\r\n";
            $detalle .= 'PorcConst = ' . $vinc["porcconstvinc"] . "\r\n";
            $detalle .= 'PorcRef = ' . $vinc["porcrefvinc"] . "\r\n";
            $detalle .= 'Por :' . "\r\n";
            $vinc1 = retornarRegistroMysqliApi($mysqli, 'vinculos', "id=" . $_SESSION["entrada"]["id"]);
            $detalle .= 'Matrícula = ' . $vinc1["matriculavinc"] . "\r\n";
            $detalle .= 'Vínculo = ' . $vinc1["vinculovinc"] . "\r\n";
            $detalle .= 'Idclase = ' . $vinc1["idclasevinc"] . "\r\n";
            $detalle .= 'Numid = ' . $vinc1["numidvinc"] . "\r\n";
            $detalle .= 'Nombre = ' . $vinc1["razonsocialvinc"] . "\r\n";
            $detalle .= 'Cargo = ' . $vinc1["idcargovinc"] . "\r\n";
            $detalle .= 'DesCargo = ' . $vinc1["descargovinc"] . "\r\n";
            $detalle .= 'Registro = ' . $vinc1["registrovinc"] . "\r\n";
            $detalle .= 'CuotasConst = ' . $vinc1["cuotasconstvinc"] . "\r\n";
            $detalle .= 'CuotasRef = ' . $vinc1["cuotasrefvinc"] . "\r\n";
            $detalle .= 'ValorConst = ' . $vinc1["valorconstvinc"] . "\r\n";
            $detalle .= 'ValorRef = ' . $vinc1["valorrefvinc"] . "\r\n";
            $detalle .= 'PorcConst = ' . $vinc1["porcconstvinc"] . "\r\n";
            $detalle .= 'PorcRef = ' . $vinc1["porcrefvinc"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '003', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarVinculoDelete(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tramite", false);
        $api->validarParametro("matricula", false);
        $api->validarParametro("id", false);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarVinculoDelete', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarVinculoDelete', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["id"] = base64_decode($_SESSION["entrada"]["id"]);
        if (ltrim(trim($_SESSION["entrada"]["id"]), "0") == '') {
            $_SESSION["entrada"]["id"] = 0;
        }
        if ($_SESSION["entrada"]["id"] != 0) {
            $vinc = retornarRegistroMysqliApi($mysqli, 'vinculos', "id=" . $_SESSION["entrada"]["id"]);
            borrarRegistrosMysqliApi($mysqli, 'vinculos', "id=" . $_SESSION["entrada"]["id"]);

            //
            $detalle = 'Borró vínculo' . "\r\n";
            $detalle .= 'Matrícula = ' . $vinc["matriculavinc"] . "\r\n";
            $detalle .= 'Vínculo = ' . $vinc["vinculovinc"] . "\r\n";
            $detalle .= 'Idclase = ' . $vinc["idclasevinc"] . "\r\n";
            $detalle .= 'Numid = ' . $vinc["numidvinc"] . "\r\n";
            $detalle .= 'Nombre = ' . $vinc["razonsocialvinc"] . "\r\n";
            $detalle .= 'Cargo = ' . $vinc["idcargovinc"] . "\r\n";
            $detalle .= 'DesCargo = ' . $vinc["descargovinc"] . "\r\n";
            $detalle .= 'Registro = ' . $vinc["registrovinc"] . "\r\n";
            $detalle .= 'CuotasConst = ' . $vinc["cuotasconstvinc"] . "\r\n";
            $detalle .= 'CuotasRef = ' . $vinc["cuotasrefvinc"] . "\r\n";
            $detalle .= 'ValorConst = ' . $vinc["valorconstvinc"] . "\r\n";
            $detalle .= 'ValorRef = ' . $vinc["valorrefvinc"] . "\r\n";
            $detalle .= 'PorcConst = ' . $vinc["porcconstvinc"] . "\r\n";
            $detalle .= 'PorcRef = ' . $vinc["porcrefvinc"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '004', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarVinculoToHistory(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tramite", false);
        $api->validarParametro("matricula", false);
        $api->validarParametro("id", false);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarVinculoToHistory', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarVinculoToHistory', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["id"] = base64_decode($_SESSION["entrada"]["id"]);
        if (ltrim(trim($_SESSION["entrada"]["id"]), "0") == '') {
            $_SESSION["entrada"]["id"] = 0;
        }
        if ($_SESSION["entrada"]["id"] != 0) {
            $vinc = retornarRegistroMysqliApi($mysqli, 'vinculos', "id=" . $_SESSION["entrada"]["id"]);
            $arrCampos = array(
                'estadovinc',
                'fechahistoricovinc',
                'usuariohistoricovinc',
                'fecsincronizacionvinc',
                'horsincronizacionvinc'
            );
            $arrValores = array(
                "'H'",
                "'" . date("Ymd") . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            regrabarRegistrosMysqliApi($mysqli, 'vinculos', $arrCampos, $arrValores, "id=" . $_SESSION["entrada"]["id"]);

            //
            $detalle = 'Pasó vínculo a históricos' . "\r\n";
            $detalle .= 'Matrícula = ' . $vinc["matriculavinc"] . "\r\n";
            $detalle .= 'Vínculo = ' . $vinc["vinculovinc"] . "\r\n";
            $detalle .= 'Idclase = ' . $vinc["idclasevinc"] . "\r\n";
            $detalle .= 'Numid = ' . $vinc["numidvinc"] . "\r\n";
            $detalle .= 'Nombre = ' . $vinc["razonsocialvinc"] . "\r\n";
            $detalle .= 'Cargo = ' . $vinc["idcargovinc"] . "\r\n";
            $detalle .= 'DesCargo = ' . $vinc["descargovinc"] . "\r\n";
            $detalle .= 'Registro = ' . $vinc["registrovinc"] . "\r\n";
            $detalle .= 'CuotasConst = ' . $vinc["cuotasconstvinc"] . "\r\n";
            $detalle .= 'CuotasRef = ' . $vinc["cuotasrefvinc"] . "\r\n";
            $detalle .= 'ValorConst = ' . $vinc["valorconstvinc"] . "\r\n";
            $detalle .= 'ValorRef = ' . $vinc["valorrefvinc"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '067', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarVinculoFromHistory(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tramite", false);
        $api->validarParametro("matricula", false);
        $api->validarParametro("id", false);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarVinculoFromHistory', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarVinculoFromHistory', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["matricula"] = base64_decode($_SESSION["entrada"]["matricula"]);
        $_SESSION["entrada"]["id"] = base64_decode($_SESSION["entrada"]["id"]);
        if (ltrim(trim($_SESSION["entrada"]["id"]), "0") == '') {
            $_SESSION["entrada"]["id"] = 0;
        }
        if ($_SESSION["entrada"]["id"] != 0) {
            $vinc = retornarRegistroMysqliApi($mysqli, 'vinculos', "id=" . $_SESSION["entrada"]["id"]);
            $arrCampos = array(
                'estadovinc',
                'fechahistoricovinc',
                'usuariohistoricovinc',
                'fecsincronizacionvinc',
                'horsincronizacionvinc'
            );
            $arrValores = array(
                "'V'",
                "''",
                "''",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            regrabarRegistrosMysqliApi($mysqli, 'vinculos', $arrCampos, $arrValores, "id=" . $_SESSION["entrada"]["id"]);

            //
            $detalle = 'Recuperación de vínculo de históricos a vigentes' . "\r\n";
            $detalle .= 'Matrícula = ' . $vinc["matriculavinc"] . "\r\n";
            $detalle .= 'Vínculo = ' . $vinc["vinculovinc"] . "\r\n";
            $detalle .= 'Idclase = ' . $vinc["idclasevinc"] . "\r\n";
            $detalle .= 'Numid = ' . $vinc["numidvinc"] . "\r\n";
            $detalle .= 'Nombre = ' . $vinc["razonsocialvinc"] . "\r\n";
            $detalle .= 'Cargo = ' . $vinc["idcargovinc"] . "\r\n";
            $detalle .= 'DesCargo = ' . $vinc["descargovinc"] . "\r\n";
            $detalle .= 'Registro = ' . $vinc["registrovinc"] . "\r\n";
            $detalle .= 'CuotasConst = ' . $vinc["cuotasconstvinc"] . "\r\n";
            $detalle .= 'CuotasRef = ' . $vinc["cuotasrefvinc"] . "\r\n";
            $detalle .= 'ValorConst = ' . $vinc["valorconstvinc"] . "\r\n";
            $detalle .= 'ValorRef = ' . $vinc["valorrefvinc"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '068', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarCertificaFormSave(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        require_once ('myErrorHandler.php');
        set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        // ********************************************************************** //
        // Conexion con mysql
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que la peticion sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida parámetros recibidos
        // ********************************************************************** //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tramite", false);
        $api->validarParametro("matricula", true);
        $api->validarParametro("certifica", true);
        $api->validarParametro("textocert", false);
        $api->validarParametro("textocertembargos", false);
        $api->validarParametro("textocertdesembargos", false);
        $api->validarParametro("textocertmedidas", false);
        $api->validarParametro("textocertlevantamientos", false);
        $api->validarParametro("textocertprendas", false);
        $api->validarParametro("textocertcancelaciones", false);
        $api->validarParametro("textocertpoderes", false);
        $api->validarParametro("textocertrevocatorias", false);
        $api->validarParametro("textocertfideicomisos", false);
        $api->validarParametro("textocertcancelacionfideicomisos", false);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarCertificaFormSave', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarCertificaFormSave', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["tramite"] = base64_decode($_SESSION["entrada"]["tramite"]);
        $_SESSION["entrada"]["matricula"] = base64_decode($_SESSION["entrada"]["matricula"]);
        $_SESSION["entrada"]["certifica"] = base64_decode($_SESSION["entrada"]["certifica"]);

        $_SESSION["entrada"]["textocert"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocert"]));
        $_SESSION["entrada"]["textocert"] = strip_tags($_SESSION["entrada"]["textocert"], "<p>");

        $_SESSION["entrada"]["textocertembargos"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertembargos"]));
        $_SESSION["entrada"]["textocertembargos"] = strip_tags($_SESSION["entrada"]["textocertembargos"], "<p>");

        $_SESSION["entrada"]["textocertdesembargos"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertdesembargos"]));
        $_SESSION["entrada"]["textocertdesembargos"] = strip_tags($_SESSION["entrada"]["textocertdesembargos"], "<p>");

        $_SESSION["entrada"]["textocertmedidas"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertmedidas"]));
        $_SESSION["entrada"]["textocertmedidas"] = strip_tags($_SESSION["entrada"]["textocertmedidas"], "<p>");

        $_SESSION["entrada"]["textocertlevantamientos"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertlevantamientos"]));
        $_SESSION["entrada"]["textocertlevantamientos"] = strip_tags($_SESSION["entrada"]["textocertlevantamientos"], "<p>");

        $_SESSION["entrada"]["textocertprendas"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertprendas"]));
        $_SESSION["entrada"]["textocertprendas"] = strip_tags($_SESSION["entrada"]["textocertprendas"], "<p>");

        $_SESSION["entrada"]["textocertcancelaciones"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertcancelaciones"]));
        $_SESSION["entrada"]["textocertcancelaciones"] = strip_tags($_SESSION["entrada"]["textocertcancelaciones"], "<p>");

        $_SESSION["entrada"]["textocertpoderes"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertpoderes"]));
        $_SESSION["entrada"]["textocertpoderes"] = strip_tags($_SESSION["entrada"]["textocertpoderes"], "<p>");

        $_SESSION["entrada"]["textocertrevocatorias"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertrevocatorias"]));
        $_SESSION["entrada"]["textocertrevocatorias"] = strip_tags($_SESSION["entrada"]["textocertrevocatorias"], "<p>");

        $_SESSION["entrada"]["textocertfideicomisos"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertfideicomisos"]));
        $_SESSION["entrada"]["textocertfideicomisos"] = strip_tags($_SESSION["entrada"]["textocertfideicomisos"], "<p>");

        $_SESSION["entrada"]["textocertcancelacionfideicomisos"] = base64_decode(str_replace(" ", "+", $_SESSION["entrada"]["textocertcancelacionfideicomisos"]));
        $_SESSION["entrada"]["textocertcancelacionfideicomisos"] = strip_tags($_SESSION["entrada"]["textocertcancelacionfideicomisos"], "<p>");

        //
        //
        $arrCampos = array(
            'matriculacert',
            'idcertificacert',
            'textocert',
            'fecsincronizacioncert',
            'horsincronizacioncert'
        );

        //
        if ($_SESSION["entrada"]["certifica"] != '0900' && $_SESSION["entrada"]["certifica"] != '1010' && $_SESSION["entrada"]["certifica"] != '1500' && $_SESSION["entrada"]["certifica"] != '6010') {
            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'" . $_SESSION["entrada"]["certifica"] . "'",
                "'" . $_SESSION["entrada"]["textocert"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='" . $_SESSION["entrada"]["certifica"] . "'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= 'Certifica = ' . $_SESSION["entrada"]["certifica"] . "\r\n";
            $detalle .= 'Texto = ' . $_SESSION["entrada"]["textocert"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        if ($_SESSION["entrada"]["certifica"] == '0900') {
            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'0900'",
                "'" . $_SESSION["entrada"]["textocertembargos"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='0900'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 0900\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertembargos"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);

            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'0901'",
                "'" . $_SESSION["entrada"]["textocertdesembargos"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='0901'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 0901\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertdesembargos"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);

            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'0902'",
                "'" . $_SESSION["entrada"]["textocertmedidas"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='0902'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 0902\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertmedidas"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);

            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'0903'",
                "'" . $_SESSION["entrada"]["textocertlevantamientos"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='0903'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 0903\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocerlevantamientos"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        if ($_SESSION["entrada"]["certifica"] == '1010') {
            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'1010'",
                "'" . $_SESSION["entrada"]["textocertprendas"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='1010'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 1010\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertprendas"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);

            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'1011'",
                "'" . $_SESSION["entrada"]["textocertcancelaciones"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='1011'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 1011\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertcancelaciones"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        if ($_SESSION["entrada"]["certifica"] == '1500') {
            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'1500'",
                "'" . $_SESSION["entrada"]["textocertpoderes"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='1500'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 1500\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertpoderes"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);

            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'1501'",
                "'" . $_SESSION["entrada"]["textocertrevocatorias"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='1501'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 1501\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertrevocatorias"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        if ($_SESSION["entrada"]["certifica"] == '6010') {
            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'6010'",
                "'" . $_SESSION["entrada"]["textocertfideicomisos"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='6010'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 6010\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertfideicomisos"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);

            $arrValores = array(
                "'" . $_SESSION["entrada"]["matricula"] . "'",
                "'6011'",
                "'" . $_SESSION["entrada"]["textocertcancelacionfideicomisos"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );
            borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='6011'");
            $res = insertarRegistrosMysqliApi($mysqli, 'certificas', $arrCampos, $arrValores);
            if ($res === false) {
                $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"] ["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
                $mysqli->close();
                $api->response($api->json($_SESSION ["jsonsalida"]), 200);
            }
            $detalle = 'Actualizó certifica ' . "\r\n";
            $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
            $detalle .= "Certifica = 6011\r\n";
            $detalle .= "Texto = " . $_SESSION["entrada"]["textocertcancelacionfideicomisos"] . "\r\n";
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarCertificaDelete(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        require_once ('myErrorHandler.php');
        set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        // ********************************************************************** //
        // Conexion con mysql
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que la peticion sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida parámetros recibidos
        // ********************************************************************** //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tramite", false);
        $api->validarParametro("matricula", true);
        $api->validarParametro("certifica", true);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarCertificaDelete', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarCertificaDelete', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["tramite"] = base64_decode($_SESSION["entrada"]["tramite"]);
        $_SESSION["entrada"]["matricula"] = base64_decode($_SESSION["entrada"]["matricula"]);
        $_SESSION["entrada"]["certifica"] = base64_decode($_SESSION["entrada"]["certifica"]);

        //
        $cert = retornarRegistroMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='" . $_SESSION["entrada"]["certifica"] . "'");

        //
        borrarRegistrosMysqliApi($mysqli, 'certificas', "matriculacert='" . $_SESSION["entrada"]["matricula"] . "' and idcertificacert='" . $_SESSION["entrada"]["certifica"] . "'");

        //
        $detalle = 'Borró certifica ' . "\r\n";
        $detalle .= 'Matrícula = ' . $_SESSION["entrada"]["matricula"] . "\r\n";
        $detalle .= 'Certifica = ' . $_SESSION["entrada"]["certifica"] . "\r\n";
        $detalle .= 'Texto = ' . $cert["textocert"] . "\r\n";
        actualizarLogMysqliApi($mysqli, '004', $_SESSION["generales"]["codigousuario"], 'digitar.php', '', '', '', $detalle, $_SESSION["entrada"]["matricula"]);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarSaveKardex(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }
        // \logApi::general2('digitarSaveCaseFile_' . date ("Ymd"), '', 'Abrio conexion con BD');
        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        \logApi::peticionRest('api_' . __FUNCTION__);

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }


        // \logApi::general2('digitarSaveCaseFile_' . date ("Ymd"), '', 'Leyo parametros');
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarSaveKardex', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarSaveKardex', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION ["entrada"] ["libroinscripciones"] = base64_decode($_SESSION ["entrada"] ["libroinscripciones"]);
        $_SESSION ["entrada"] ["tomoinscripciones"] = base64_decode($_SESSION ["entrada"] ["tomoinscripciones"]);
        $_SESSION ["entrada"] ["registroinscripciones"] = base64_decode($_SESSION ["entrada"] ["registroinscripciones"]);
        $_SESSION ["entrada"] ["dupliinscripciones"] = base64_decode($_SESSION ["entrada"] ["dupliinscripciones"]);

        //
        $codmun = '';
        $temx = retornarRegistrosTablasMysqliApi($mysqli, 'municipios');
        foreach ($temx as $tx) {
            $tmun = $tx["descripcion"] . ' (' . $tx["campo1"] . ')';
            if ($tmun == base64_decode($_SESSION["entrada"]["mundocinscripciones"])) {
                $codmun = $tx["idcodigo"];
            }
        }

        //
        $codori = '';
        $temx = retornarRegistrosTablasMysqliApi($mysqli, 'origenes');
        foreach ($temx as $tx) {
            if ($tx["descripcion"] == base64_decode($_SESSION["entrada"]["idorigendocinscripciones"])) {
                $codori = $tx["idcodigo"];
            }
        }

        // Lee el registro del kardex
        $reg = \funcionesRegistrales::retornarRegistroKardex($mysqli, $_SESSION["entrada"]["libroinscripciones"], $_SESSION["entrada"]["tomoinscripciones"], $_SESSION["entrada"]["registroinscripciones"], $_SESSION["entrada"]["dupliinscripciones"]);

        $reg["radicadoinscripciones"] = base64_decode($_SESSION["entrada"]["radicadoinscripciones"]);
        $reg["reciboinscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["reciboinscripciones"]));
        $reg["fecharadicadoinscripciones"] = str_replace(array("-", "/"), "", base64_decode($_SESSION["entrada"]["fecharadicadoinscripciones"]));

        $reg["actoinscripciones"] = base64_decode($_SESSION["entrada"]["actoinscripciones"]);
        $reg["noticiainscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["noticiainscripciones"]));
        $reg["imprimirnoticiainscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["imprimirnoticiainscripciones"]));

        $reg["esreformainscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["esreformainscripciones"]));
        $reg["espoderinscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["espoderinscripciones"]));
        $reg["esaprobacionbalancesinscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["esaprobacionbalancesinscripciones"]));
        $reg["esnombramientosinscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["esnombramientosinscripciones"]));
        $reg["esmedidapreventivainscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["esmedidapreventivainscripciones"]));
        $reg["esprendainscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["esprendainscripciones"]));

        $reg["fecharegistroinscripciones"] = str_replace(array("-", "/"), "", base64_decode($_SESSION["entrada"]["fecharegistroinscripciones"]));
        $reg["horaregistroinscripciones"] = sprintf("%06s", base64_decode($_SESSION["entrada"]["horaregistroinscripciones"]));

        $reg["matriculainscripciones"] = ltrim(base64_decode($_SESSION["entrada"]["matriculainscripciones"]), "0");
        $reg["organizacioninscripciones"] = base64_decode($_SESSION["entrada"]["organizacioninscripciones"]);
        $reg["categoriainscripciones"] = base64_decode($_SESSION["entrada"]["categoriainscripciones"]);
        $reg["idclaseinscripciones"] = base64_decode($_SESSION["entrada"]["idclaseinscripciones"]);
        $reg["numidinscripciones"] = base64_decode($_SESSION["entrada"]["numidinscripciones"]);
        $reg["nombreinscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["nombreinscripciones"]));
        $reg["nombrecomercialinscripciones"] = strtoupper(base64_decode($_SESSION["entrada"]["nombrecomercialinscripciones"]));
        $reg["idtipodocinscripciones"] = base64_decode($_SESSION["entrada"]["idtipodocinscripciones"]);
        $reg["numdocinscripciones"] = base64_decode($_SESSION["entrada"]["numdocinscripciones"]);
        if (trim($reg["numdocinscripciones"]) == '') {
            $reg["numdocinscripciones"] = 'N/A';
        }
        $reg["fecdocinscripciones"] = str_replace(array("-", "/"), "", base64_decode($_SESSION["entrada"]["fecdocinscripciones"]));
        $reg["idorigendocinscripciones"] = $codori;
        $reg["mundocinscripciones"] = $codmun;
        $reg["paisdocinscripciones"] = base64_decode($_SESSION["entrada"]["paisdocinscripciones"]);

        $reg = \funcionesRegistrales::actualizarRegistroKardex($mysqli, $reg);

        if ($reg === false) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Error almacenando el registro en el kardex';
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarGenerateCertificate(API $api) {
        require_once ('genPdfCertificados.php');
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('../components/phpqrcode/qrlib.php');

        // ********************************************************************** //
        // Conexion a BD
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Inicializa arreglo salda
        // ********************************************************************** //        
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["link"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-post-request');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        \logApi::peticionRest('api_' . __FUNCTION__);

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("session_parameters", true);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // \logApi::general2('digitarSaveCaseFile_' . date ("Ymd"), '', 'Leyo parametros');
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarGenerateCertificate', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = str_replace("[NAME-METHOD]", 'digitarGenerateCertificate', \funcionesGenerales::retornarLabel($mysqli, 'error-no-permits-for-execute-method'));
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION ["entrada"] ["matricula"] = base64_decode($_SESSION ["entrada"] ["matricula"]);


        // ************************************************************** //
        // Localiza expediente
        // ************************************************************** //
        $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
        if ($exp === false || empty($exp)) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = \funcionesGenerales::retornarLabel($mysqli, 'error-no-possible-locate-enrollment');
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ************************************************************** //
        // Genera el certificado
        // ************************************************************** //
        $npdf = armarPdfExistenceAndLegalRepresentation($mysqli, 'consulta', array(), $exp, '', '', '');
        $_SESSION ["jsonsalida"]["link"] = base64_encode($npdf);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarBuscarMatriculas(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idliquidacion", false);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('consultasBuscarExpedientes', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Sin permisos para ejecutar el método consultasBuscarExpedientes';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // Arma pantalla de presentación
        $string = '';

        $pres = new \presentacionBootstrap ();
        $string .= $pres->abrirPanel();
        $txt = \funcionesGenerales::retornarLabel($mysqli, 'text-search-registers-2');
        $string .= $pres->armarLineaTextoInformativa($txt, 'center');
        $string .= '<center>';
        $string .= $pres->armarCampoTextoMd('', 'no', '_textobuscar', 8, '', '');
        $string .= '</center>';
        $string .= '<hr>';
        $arrBtnTipo = array();
        $arrBtnImagen = array();
        $arrBtnEnlace = array();
        $arrBtnTipo [] = 'javascript';
        $arrBtnImagen [] = \funcionesGenerales::retornarLabel($mysqli, 'btn-consult');
        $arrBtnEnlace [] = 'buscarMatriculasContinuar();';
        $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
        $string .= '<hr>';
        $string .= '<div id="resultadoBusqueda" style="height: 250px; overflow-y: scroll;"></div>';
        $string .= $pres->cerrarPanel();
        unset($pres);

        //
        $mysqli->close();

        //
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarBuscarMatriculasContinuar(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("textobuscar", false);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarBuscarMatriculasContinuar', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Sin permisos para ejecutar el método digitarBuscarMatriculasContinuar';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // Arma pantalla de presentación
        $string = '';
        $pres = new \presentacionBootstrap ();

        //
        if (is_numeric($_SESSION["entrada"]["textobuscar"])) {
            $res1 = retornarRegistrosMysqliApi($mysqli, 'inscritos', "matricula='" . $_SESSION["entrada"]["textobuscar"] . "' or numid='" . $_SESSION["entrada"]["textobuscar"] . "'", "matricula");
            $res = array();
            if ($res1 && !empty($res1)) {
                foreach ($res1 as $r) {
                    if ($r["razonsocial"] != 'NO ASIGNADA') {
                        $res[] = $r;
                    }
                }
            }
        } else {
            $palabras = explode(" ", $_SESSION["entrada"]["textobuscar"]);
            if (count($palabras) == 1) {
                $query = "(razonsocial like '%" . $_SESSION["entrada"]["textobuscar"] . "%' or ";
                $query .= "sigla like '%" . $_SESSION["entrada"]["textobuscar"] . "%' or ";
                $query .= "nombrecomercial like '%" . $_SESSION["entrada"]["textobuscar"] . "%')";
            } else {
                $query = "(razonsocial like '" . $_SESSION["entrada"]["textobuscar"] . "%' or ";
                $query .= "sigla like '" . $_SESSION["entrada"]["textobuscar"] . "%' or ";
                $query .= "nombrecomercial like '" . $_SESSION["entrada"]["textobuscar"] . "%')";
            }
            if (count($palabras) > 1) {
                $query .= " or (";
                $cantidad_palabras = 0;
                $query .= "(";
                foreach ($palabras as $palabra) {
                    $cantidad_palabras++;
                    if ($cantidad_palabras == 1) {
                        $query .= "(razonsocial like '%" . $palabra . "%')";
                    } else {
                        $query .= " and (razonsocial like '%" . $palabra . "%')";
                    }
                }
                $query .= ") or ";

                $cantidad_palabras = 0;
                $query .= "(";
                foreach ($palabras as $palabra) {
                    $cantidad_palabras++;
                    if ($cantidad_palabras == 1) {
                        $query .= "(sigla like '%" . $palabra . "%')";
                    } else {
                        $query .= " and (sigla like '%" . $palabra . "%')";
                    }
                }
                $query .= ") or ";

                $cantidad_palabras = 0;
                $query .= "(";
                foreach ($palabras as $palabra) {
                    $cantidad_palabras++;
                    if ($cantidad_palabras == 1) {
                        $query .= "(nombrecomercial like '%" . $palabra . "%')";
                    } else {
                        $query .= " and (nombrecomercial like '%" . $palabra . "%')";
                    }
                }
                $query .= ")";
                $query .= ")";
            }
            $res1 = retornarRegistrosMysqliApi($mysqli, 'inscritos', $query, "razonsocial");
            $res = array();
            if ($res1 && !empty($res1)) {
                foreach ($res1 as $r) {
                    if ($r["razonsocial"] != 'NO ASIGNADA') {
                        $res[] = $r;
                    }
                }
            }
        }

        //
        foreach ($res as $tx) {
            $data = base64_encode($tx["matricula"] . '|' . $tx["idclase"] . '|' . $tx["numid"] . '|' . $tx["razonsocial"] . '|' . $tx["nombrecomercial"] . '|' . $tx["organizacion"] . '|' . $tx["categoria"]);
            $txt = '<a href="javascript:seleccionarMatricula(\'' . $data . '\');">' . $tx["matricula"] . '</a> ' . ' - ' . $tx["razonsocial"];
            if (trim($tx["nombrecomercial"]) != '') {
                $txt .= ' - ' . $tx["nombrecomercial"];
            }
            if (trim($tx["sigla"]) != '') {
                $txt .= ' - ' . $tx["sigla"];
            }
            $string .= $pres->armarLineaTextoInformativa($txt, 'left', 'small', 'text-dark');
            $string .= '<hr>';
        }

        //
        unset($pres);

        //
        $mysqli->close();

        //
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarBuscarMatriculasNumero(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["idclase"] = '';
        $_SESSION ["jsonsalida"] ["numid"] = '';
        $_SESSION ["jsonsalida"] ["razonsocial"] = '';
        $_SESSION ["jsonsalida"] ["nombrecomercial"] = '';
        $_SESSION ["jsonsalida"] ["organizacion"] = '';
        $_SESSION ["jsonsalida"] ["categoria"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", true);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarBuscarMatriculasNumero', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Sin permisos para ejecutar el método digitarBuscarMatriculasNumero';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $exp = retornarRegistroMysqliApi($mysqli, 'inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'");
        if ($exp === false || empty($exp)) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Matrícula no localizada';
            $mysqli->close();
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $_SESSION ["jsonsalida"] ["idclase"] = $exp["idclase"];
        $_SESSION ["jsonsalida"] ["numid"] = $exp["numid"];
        $_SESSION ["jsonsalida"] ["razonsocial"] = $exp["razonsocial"];
        $_SESSION ["jsonsalida"] ["nombrecomercial"] = $exp["nombrecomercial"];
        $_SESSION ["jsonsalida"] ["organizacion"] = $exp["organizacion"];
        $_SESSION ["jsonsalida"] ["categoria"] = $exp["categoria"];
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarAssignDateCapital(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["date"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idliquidacion", false);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarAssignDateCapital', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Sin permisos para ejecutar el método digitarAssignDateCapital';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        list ($lib, $tom, $reg, $dup) = explode("-", $_SESSION["entrada"]["registro"]);
        $condicion = "libroinscripciones='" . $lib . "' and tomoinscripciones='" . $tom . "' and registroinscripciones='" . $reg . "' and dupliinscripciones='" . $dup . "'";
        $ins = retornarRegistroMysqliApi($mysqli, 'inscripciones', $condicion);
        $mysqli->close();

        //
        $_SESSION ["jsonsalida"] ["date1"] = $ins["fecharegistroinscripciones"];

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function digitarRecuperarImagenAnotacion(API $api) {
        require_once ('log.php');
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["date"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("inscripcionseleccionada", false);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('digitarRecargarImagenAnotacion', $_SESSION ["entrada"] ["token"], $_SESSION ["entrada"] ["usuariows"])) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'Sin permisos para ejecutar el método digitarRecargarImagenAnotacion';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Armar el formulario
        // ********************************************************************** //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        $filex = '';
        list ($lib, $tom, $reg, $dup) = explode("-", $_SESSION["entrada"]["inscripcionseleccionada"]);
        $condicion = "libroanx='" . $lib . "' and tomoanx='" . $tom . "' and registroanx='" . $reg . "'";
        $temx = retornarRegistrosMysqliApi($mysqli, 'radicacionesanexos', $condicion, "idanexo");
        if ($temx && !empty($temx)) {
            $ianx = '';
            foreach ($temx as $tx) {
                if ($tx["tipoanexoanx"] == '501') {
                    if ($ianx == '') {
                        if ($tx["sistemaorigenanx"] == 'ISSMARTPRS') {
                            if (file_exists(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $tx["pathanx"])) {
                                $filex = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $tx["pathanx"];
                            }
                            if (file_exists(str_replace(".pdf", "-signed.pdf", PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $tx["pathanx"]))) {
                                $filex = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace(".pdf", "-signed.pdf", $tx["pathanx"]);
                            }
                        }
                        if ($tx["sistemaorigenanx"] == 'ROYAL') {
                            $filex = 'tmp/' . \funcionesGenerales::convertirImagenRoyalPdf($tx["idanexo"], $tx["pathanx"]);
                        }

                        $ianx = $tx["idanexo"];
                    }
                }
            }
        }
        if ($filex == '') {
            $condicion = "libroinscripciones='" . $lib . "' and tomoinscripciones='" . $tom . "' and registroinscripciones='" . $reg . "' and dupliinscripciones='" . $dup . "'";
            $temx = retornarRegistroMysqliApi($mysqli, 'inscripciones', $condicion);
            if ($temx && !empty($temx)) {
                if ($temx["radicadoinscripciones"] != '') {
                    $temx1 = retornarRegistrosMysqliApi($mysqli, 'radicacionesanexos', "idliquidacionanx=" . $temx["radicadoinscripciones"], "idanexo");
                    if ($temx1 && !empty($temx1)) {
                        $ianx = '';
                        foreach ($temx1 as $tx) {
                            if ($tx["tipoanexoanx"] == '501' && $tx["eliminadoanx"] != 'SI') {
                                if ($ianx == '') {
                                    if ($tx["sistemaorigenanx"] == 'ISSMARTPRS') {
                                        if (file_exists(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $tx["pathanx"])) {
                                            $filex = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $tx["pathanx"];
                                        }
                                        if (file_exists(str_replace(".pdf", "-signed.pdf", PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $tx["pathanx"]))) {
                                            $filex = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace(".pdf", "-signed.pdf", $tx["pathanx"]);
                                        }
                                    }
                                    if ($tx["sistemaorigenanx"] == 'ROYAL') {
                                        $filex = 'tmp/' . \funcionesGenerales::convertirImagenRoyalPdf($tx["idanexo"], $tx["pathanx"]);
                                    }

                                    $ianx = $tx["idanexo"];
                                }
                            }
                        }
                    }
                }
            }
        }
        $mysqli->close();

        //
        $_SESSION ["jsonsalida"] ["filex"] = $filex;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
