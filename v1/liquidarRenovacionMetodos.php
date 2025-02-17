<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait liquidarRenovacionMetodos {

    public function liquidarRenovacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $resError = set_error_handler('myErrorHandler');

        $feinicio045 = '';
        if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
            $feinicio045 = '99999999';
        } else {
            $feinicio045 = FECHA_INICIO_DECRETO_045;
        }

        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        //
        $api->validarParametro("idliquidacion", true, true);

        //
        if (!$api->validarToken('liquidarRenovacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera los renglones con la información de las matrículas
        // ********************************************************************** //
        if (!isset($_SESSION["entrada1"]["registros"]) || count($_SESSION["entrada1"]["registros"]) == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicaron los matrículas y años a renovar';
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


        //WSIERRA 20180222 validar
        // ************************************************************************ //
        // Arma variables de session
        // ************************************************************************ //        
        $res = \funcionesGenerales::asignarVariablesSession($mysqli, $_SESSION["entrada"]);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de autenticacion, usuario no puede consumir el API - problemas de sesion';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Recupera la liquidación
        // ********************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Si estado de liquidación diferente de 01 o 02
        // ********************************************************************** //
        if ($_SESSION["tramite"]["idestado"] != '01' && $_SESSION["tramite"]["idestado"] != '02') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación está en un estado que no permite su modificación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recibe variables generales del formulario
        // ********************************************************************** //
        if (isset($_SESSION["entrada1"] ["fechanacimiento"])) {
            $_SESSION ["tramite"]["expedientes"][0]["fechanacimiento"] = str_replace(array("-", "/"), "", $_SESSION["entrada1"] ["fechanacimiento"]);
        }
        if (isset($_SESSION["entrada1"] ["numeroempleados"])) {
            $_SESSION ["tramite"] ["numeroempleados"] = intval($_SESSION["entrada1"] ["numeroempleados"]);
        }
        if (isset($_SESSION["entrada1"] ["cumplorequisitosbenley1780"])) {
            $_SESSION ["tramite"] ["cumplorequisitosbenley1780"] = strtoupper($_SESSION["entrada1"] ["cumplorequisitosbenley1780"]);
        }
        if (isset($_SESSION["entrada1"] ["mantengorequisitosbenley1780"])) {
            $_SESSION ["tramite"] ["mantengorequisitosbenley1780"] = strtoupper($_SESSION["entrada1"] ["mantengorequisitosbenley1780"]);
        }
        if (isset($_SESSION["entrada1"] ["renunciobeneficiosley1780"])) {
            $_SESSION ["tramite"] ["renunciobeneficiosley1780"] = strtoupper($_SESSION["entrada1"] ["renunciobeneficiosley1780"]);
        }
        if (isset($_SESSION["entrada1"] ["incluirafiliacion"])) {
            $_SESSION ["tramite"] ["incluirafiliacion"] = strtoupper($_SESSION["entrada1"] ["incluirafiliacion"]);
        }
        if (isset($_SESSION["entrada1"] ["incluirformulario"])) {
            $_SESSION ["tramite"] ["incluirformularios"] = strtoupper($_SESSION["entrada1"] ["incluirformulario"]);
        }
        if (isset($_SESSION["entrada1"] ["incluirdiploma"])) {
            $_SESSION ["tramite"] ["incluirdiploma"] = strtoupper($_SESSION["entrada1"] ["incluirdiploma"]);
        }
        if (isset($_SESSION["entrada1"] ["incluircartulina"])) {
            $_SESSION ["tramite"] ["incluircartulina"] = strtoupper($_SESSION["entrada1"] ["incluircartulina"]);
        }
        if (isset($_SESSION["entrada1"] ["incluirfletes"])) {
            $_SESSION ["tramite"] ["incluirfletes"] = strtoupper($_SESSION["entrada1"] ["incluirfletes"]);
        }
        if (isset($_SESSION["entrada1"] ["incluircertificado"])) {
            $_SESSION ["tramite"] ["incluircertificados"] = intval($_SESSION["entrada1"] ["incluircertificado"]);
        }

        $antesde = $_SESSION ["tramite"] ["incluirformularios"];

        // ********************************************************************** //
        // Encuentra salario mínimo
        // ********************************************************************** //
        $_SESSION ["generales"] ["smmlv"] = \funcionesGenerales::retornarSalarioMinimoActual($mysqli, date("Y"));

        //
        $nameLog = 'api_' . __FUNCTION__ . '_Pasos';

        foreach ($_SESSION["entrada1"]["registros"] as $r) {
            $msg = 'Solicita liquidar => Matrícula=' . $r["matricula"] . ' - AñoRenovar=' . $r["ano"] . ' - NuevosActivos=' . $r["activos"] . ' - DeseaRenovarAño=' . $r["renovaresteano"];
            \logApi::general2($nameLog, '', $msg);

            foreach ($_SESSION["tramite"]["expedientes"] as $key => $l) {
                $txt = 'Detecta => Matrícula=' . $l["matricula"] . ' - ÚltimoAñoRenovado=' . $l["ultimoanorenovado"];
                if ($r["matricula"] == $l["matricula"] && $r["ano"] == $l["ultimoanorenovado"]) {
                    $txt .= ' => OK';
                    if ($r["activos"] == '') {
                        $r["activos"] = 0;
                    }
                    $_SESSION ["tramite"] ["expedientes"] [$key] ["nuevosactivos"] = doubleval(str_replace(array(",", "-", "$", " "), "", $r["activos"]));
                    $_SESSION ["tramite"] ["expedientes"] [$key] ["renovaresteano"] = 'no';
                    if ($r["renovaresteano"] == 'si') {
                        $_SESSION ["tramite"] ["expedientes"] [$key] ["renovaresteano"] = 'si';
                    }
                    if ($r["renovaresteano"] == 'in') {
                        $_SESSION ["tramite"] ["expedientes"] [$key] ["renovaresteano"] = 'in';
                    }
                }
                \logApi::general2($nameLog, '', $txt);
            }
        }

        // ********************************************************************** //
        // Valida controles de la renovacion
        // ********************************************************************** //

        $numExpedientes = count($_SESSION ["tramite"] ["expedientes"]);

        $itemNoRenovar = 0;

        foreach ($_SESSION ["tramite"] ["expedientes"] as $ind => $data) {
            if (!isset($data ["renovaresteano"])) {
                $_SESSION ["tramite"] ["expedientes"] [$ind] ["renovaresteano"] = 'no';
            }

            // 2016-03-17 : Se asume que no se reciben activos en cero. y si viene en cero, siempre y cuando
            // el parámetro RENOVACION_ACTIVOS_CERO = 'N', lo asume como un año a no renovar.
            if (!defined('RENOVACION_ACTIVOS_CERO')) {
                define('RENOVACION_ACTIVOS_CERO', 'N');
            }
            if (RENOVACION_ACTIVOS_CERO == 'N' || trim(RENOVACION_ACTIVOS_CERO) == '') {
                if ($data["renovaresteano"] == 'si') {
                    if (doubleval($data["nuevosactivos"]) == 0) {
                        $_SESSION ["tramite"] ["expedientes"] [$ind] ["renovaresteano"] = 'no';
                        $itemNoRenovar++;
                    }
                }
            }
        }

        if (RENOVACION_ACTIVOS_CERO == 'N' || trim(RENOVACION_ACTIVOS_CERO) == '') {
            if ($numExpedientes <= $itemNoRenovar) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para los expedientes seleccionados no se informa los nuevos activos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // 2017-11-26:JINT: Se incluye para validar campos de Ley 1780
        // ********************************************************************** //
        if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != '' &&
                $_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' &&
                $_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'N') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El indicador de cumplimiento de requisitos para acceder a los beneficios de Ley 1780 debe ser S o N';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["tramite"]["mantengorequisitosbenley1780"] != '' &&
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' &&
                $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'N') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El indicador de mantenimiento de requisitos para acceder a los beneficios de Ley 1780 debe ser S o N';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'S' && $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'S') {
            if ($_SESSION["tramite"]["expedientes"][0]["organizacion"] == '01') {
                if ($_SESSION["tramite"]["expedientes"][0]["fechanacimiento"] == '') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de nacimiento de la persona natural no debe ser vacía';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                if (\funcionesGenerales::validarFecha($_SESSION["tramite"]["expedientes"][0]["fechanacimiento"]) === false) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de nacimiento de la persona natural parece estar incorrecta';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $_SESSION["tramite"]["expedientes"][0]["fechanacimiento"], 'ANOS', false) > 38) {
                    if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'S' && $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'S') {
                        $_SESSION["tramite"]["cumplorequisitosbenley1780"] = 'N';
                        $_SESSION["tramite"]["mantengorequisitosbenley1780"] = 'N';
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'De acuerdo con su edad, no puede acceder a los beneficios de la Ley 1780, por favor confirme';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    } else {
                        $_SESSION["tramite"]["cumplorequisitosbenley1780"] = 'N';
                        $_SESSION["tramite"]["mantengorequisitosbenley1780"] = 'N';
                    }
                }
            }
        } else {
            $_SESSION["tramite"]["cumplorequisitosbenley1780"] = 'N';
            $_SESSION["tramite"]["mantengorequisitosbenley1780"] = 'N';
        }


        // *********************************************************************** //
        // 2015-12-23 : JINT : Liquida a través de la rutina general de liquidación
        // *********************************************************************** //
        $resLiq = \funcionesRegistrales::rutinaLiquidacionTransacciones($mysqli);
        if ($resLiq === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["txtemergente"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'Liquidó renovación');

        // *********************************************************************** //
        // Pone en anulada la liquidacion si hay matriculas inactivas
        // Con el objeto que no pueda ser retomada (recuperada) posteriormente
        // *********************************************************************** //

        if (isset($_SESSION ["tramite"] ["caninactivas"])) {
            if ($_SESSION ["tramite"] ["caninactivas"] > 0) {
                $_SESSION ["tramite"] ["idestado"] = '99';
            }
        }



        // *********************************************************************** //
        // Graba la liquidación
        // *********************************************************************** //
        //Revisar aqui WSI
        //
        if (isset($_SESSION["entrada"]["sistemacreacion"]) && $_SESSION["entrada"]["sistemacreacion"] != '') {
            $_SESSION["tramite"]["sistemacreacion"] = $_SESSION["entrada"]["sistemacreacion"];
        } else {
            $_SESSION["tramite"]["sistemacreacion"] = 'API - liquidarRenovacionMultiplesAnios - ' . $_SESSION["entrada"]["usuariows"];
        }

        //
        $result = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        if ($result === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'Grabó liquidación');

        //
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function liquidarRenovacionPrediligenciados(API $api) {

        if (date("Ymd") > ' 20240100') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Metodo no habilitado a partir del 2024';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idusuario"] = '';
        $_SESSION["jsonsalida"]["emailcontrol"] = '';
        $_SESSION["jsonsalida"]["nombrecontrol"] = '';
        $_SESSION["jsonsalida"]["celularcontrol"] = '';
        $_SESSION["jsonsalida"]["identificacioncontrol"] = '';
        $_SESSION["jsonsalida"]["idliquidacion"] = '';
        $_SESSION["jsonsalida"]["numerorecuperacion"] = '';
        $_SESSION["jsonsalida"]["valorbruto"] = 0;
        $_SESSION["jsonsalida"]["valorbaseiva"] = 0;
        $_SESSION["jsonsalida"]["valoriva"] = 0;
        $_SESSION["jsonsalida"]["valortotal"] = 0;
        $_SESSION["jsonsalida"]["matricula"] = '';
        $_SESSION["jsonsalida"]["identificacion"] = '';
        $_SESSION["jsonsalida"]["activos"] = '';
        $_SESSION["jsonsalida"]["personal"] = '';
        $_SESSION["jsonsalida"]["incluirafiliacion"] = '';
        $_SESSION["jsonsalida"]["incluircertificado"] = '';
        $_SESSION["jsonsalida"]["incluirformulario"] = '';
        $_SESSION["jsonsalida"]["liquidacion"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("matricula", false);
        $api->validarParametro("identificacion", false);
        $api->validarParametro("activos", true);
        $api->validarParametro("personal", true);
        $api->validarParametro("incluirafiliacion", true);
        $api->validarParametro("incluircertificado", true);
        $api->validarParametro("incluirformulario", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('liquidarRenovacionPrediligenciados', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["jsonsalida"]["matricula"] = $_SESSION["entrada"]["matricula"];
        $_SESSION["jsonsalida"]["identificacion"] = $_SESSION["entrada"]["identificacion"];
        $_SESSION["jsonsalida"]["activos"] = $_SESSION["entrada"]["activos"];
        $_SESSION["jsonsalida"]["personal"] = $_SESSION["entrada"]["personal"];
        $_SESSION["jsonsalida"]["incluirafiliacion"] = $_SESSION["entrada"]["incluirafiliacion"];
        $_SESSION["jsonsalida"]["incluircertificado"] = $_SESSION["entrada"]["incluircertificado"];
        $_SESSION["jsonsalida"]["incluirformulario"] = $_SESSION["entrada"]["incluirformulario"];

        // ************************************************************************** //
        // Valida el idusuario reportado
        // En caso de reportar USUPUBXX deben indicarse los datos del cliente logueado
        // ************************************************************************** //
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            $sedeusuario = '99';
            if (!isset($_SESSION["entrada"]["emailcontrol"]) || $_SESSION["entrada"]["emailcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el email del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["nombrecontrol"]) || $_SESSION["entrada"]["nombrecontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el nombre del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["celularcontrol"]) || $_SESSION["entrada"]["celularcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el celular del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["identificacioncontrol"]) || $_SESSION["entrada"]["identificacioncontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó la identificación del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
            if ($usux === false || empty($usux)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no localizado en la BD del sistema';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechainactivacion"]), "0") != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario reportado está inactivo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechaactivacion"]), "0") == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario reportado no está activo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $sedeusuario = $usux["idsede"];
            $_SESSION["entrada"]["emailcontrol"] = $usux["email"];
            $_SESSION["entrada"]["nombrecontrol"] = $usux["nombreusuario"];
            $_SESSION["entrada"]["celularcontrol"] = $usux["celular"];
            $_SESSION["entrada"]["identificacioncontrol"] = $usux["identificacion"];
        }
        $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
        $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];

        // ****************************************************************************** //
        // Localiza el expediente
        // ****************************************************************************** //        
        $exp = false;
        if (trim($_SESSION["entrada"]["matricula"]) == '' && trim($_SESSION["entrada"]["identificacion"]) != '') {
            $tmx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $_SESSION["entrada"]["identificacion"] . "' or nit='" . $_SESSION["entrada"]["identificacion"] . "'", "matricula");
            if ($tmx && !empty($tmx)) {
                foreach ($tmx as $tm) {
                    if ($tm["ctrestmatricula"] == 'MA' || $tm["ctrestmatricula"] == 'IA') {
                        if ($tm["organizacion"] == '01' || ($tm["organizacion"] > '02' && $tm["categoria"] == '1')) {
                            $exp = $tm;
                        }
                    }
                }
            }
        } else {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'");
        }


        // ****************************************************************************** //
        // En caso que el expediente no exista
        // ****************************************************************************** //
        if ($exp === false || empty($exp)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no encontrado en la base de datos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ****************************************************************************** //
        // Si matrícula no está activa
        // ****************************************************************************** //
        if ($exp["ctrestmatricula"] != 'MA' && $exp["ctrestmatricula"] != 'IA') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no se encuentra activo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ****************************************************************************** //
        // Si ya está renovado al año actual
        // ****************************************************************************** //
        if ($exp["ultanoren"] == date("Y")) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ya renovó este año';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ****************************************************************************** //
        // Si debe más de un año
        // ****************************************************************************** //
        if ($exp["ultanoren"] < date("Y") - 1) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente debe más de un año, no es candidato a renovar por prediligenciado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ****************************************************************************** //
        // Si es proponente y está activo
        // ****************************************************************************** //        
        if ($exp["proponente"] != '' && ($exp["ctrestproponente"] == '00' || $exp["ctrestproponente"] == '02')) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente es a su vez proponente, no es candidato a renovar por prediligenciado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ****************************************************************************** //
        // Verifica que no sea beneficiario de la Ley 1780
        // ****************************************************************************** //
        if (substr($exp["fecmatricula"], 0, 4) == date("Y") - 1) {
            if ($exp["ctrbenley1780"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente es beneficiario de la Ley 1780, no es candidato a renovar por prediligenciado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ****************************************************************************** //
        // Se excluyen cooperativas y precooperativas
        // ****************************************************************************** //
        if (substr($exp["matricula"], 0, 1) == 'S') {
            if ($exp["ctrclasegenesadl"] == '4' || $exp["ctrclasegenesadl"] == '6') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente es una cooperativa o precoperativa, no es candidato a renovar por prediligenciado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ****************************************************************************** //
        // Se excluyen ongs extranjeras y veedurías
        // ****************************************************************************** //
        if (substr($exp["matricula"], 0, 1) == 'S') {
            if ($exp["ctrclaseespeesadl"] == '61' || $exp["ctrclaseespeesadl"] == '62') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente es una ONG extranjera o veeduría, no es candidato a renovar por prediligenciado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ****************************************************************************** //
        // Se excluyen cooperativas y precooperativas
        // ****************************************************************************** //
        if (substr($exp["matricula"], 0, 1) == 'S') {
            if ($exp["ctrclaseeconsoli"] == '01' || $exp["ctrclaseeconsoli"] == '02') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente es una cooperativa o precoperativa, no es candidato a renovar por prediligenciado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ****************************************************************************** //
        // Se excluyen establecimientos
        // ****************************************************************************** //
        if ($exp["organizacion"] == '02') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente es un establecimiento, no se liquida por prediligenciados';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ****************************************************************************** //
        // Se excluyen disueltas
        // ****************************************************************************** //
        if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
            if (strpos($exp["razonsocial"], 'EN LIQUIDACION') ||
                    strpos($exp["razonsocial"], 'EN REORGANIZACION') ||
                    strpos($exp["razonsocial"], 'EN REESTRUCTURACION')) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente se encuentra disuelto';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ****************************************************************************** //
        // Se excluyen disueltas por terminos
        // ****************************************************************************** //
        if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
            if (ltrim(trim($exp["fecvigencia"]), "0") != '' && ltrim(trim($exp["fecvigencia"]), "0") != '99999999' && ltrim(trim($exp["fecvigencia"]), "0") != '9999999') {
                if ($exp["fecvigencia"] < date("Ymd")) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente se encuentra disuelto por vencimiento de términos';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        }

        // ****************************************************************************** //
        // pnat fallecidos
        // ****************************************************************************** //
        if ($exp["organizacion"] == '01') {
            if (strpos($exp["razonsocial"], 'FALLECIDO') || strpos($exp["razonsocial"], 'FALLECIDA')) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente pertenece a una persona natural fallecida';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // **************************************************************************************** //
        // Si el valor de los activos es inferior al del añok anterior
        // **************************************************************************************** //
        if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
            if ($exp["actvin"] > $_SESSION["entrada"]["activos"]) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Los activos indicados son inferiores a los del año anterior';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }
        if ($exp["organizacion"] == '01' || $exp["categoria"] == '1') {
            if ($exp["acttot"] > $_SESSION["entrada"]["activos"]) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Los activos indicados son inferiores a los del año anterior';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // **************************************************************************************** //
        // Si es sucursal o agencia verifica que si la principal esta en la misma jurisdiccion o no
        // **************************************************************************************** //
        $propjurisdiccion = 'no';
        if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
            if ($exp["cpcodcam"] == '' || $exp["cpcodcam"] == CODIGO_EMPRESA) {
                $propjurisdiccion = 'si';
            }
        }

        // **************************************************************************************** //
        // Valida cantidad de establecimientos
        // ****************************************************************************** //
        $matest = '';
        $ests = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario='" . $exp["matricula"] . "'", "id");
        if ($ests && !empty($ests)) {
            $iests = 0;
            $excluir = 'no';
            foreach ($ests as $es) {
                $ins = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $es["matricula"] . "'");
                if ($ins && !empty($ins)) {
                    if ($ins["ctrestmatricula"] == 'MA' && $ins["ultanoren"] == $exp["ultanoren"]) {
                        $iests++;
                        if ($ins["actvin"] != $exp["acttot"]) {
                            $excluir = 'si';
                        } else {
                            $matest = $es["matricula"];
                        }
                    }
                }
            }
            if ($iests > 1) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente tiene más de un establecimiento de comercio';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($excluir == 'si') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Los activos del comerciante son diferentes a los de sus establecimientos de comercio';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************** //
        // Encuentra el valor de la liquidación de renovación del comerciante
        // ************************************************************************** //
        $liq = array();
        $liq["servicio"] = '';
        $liq["nservicio"] = '';
        $liq["matricula"] = $exp["matricula"];
        $liq["nmatricula"] = $exp["razonsocial"];
        $liq["anorenovar"] = date("Y");
        $liq["cantidad"] = 1;
        $liq["activos"] = $_SESSION["entrada"]["activos"];
        $liq["valor"] = 0;
        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
            if (substr($exp["matricula"], 0, 1) == 'S') {
                $liq["servicio"] = '01020208';
            } else {
                $liq["servicio"] = '01020201';
            }
        }
        if ($exp["organizacion"] == '02') {
            if ($propjurisdiccion == 'si') {
                $liq["servicio"] = '01020202';
            } else {
                $liq["servicio"] = '01020203';
            }
        }
        if ($exp["categoria"] == '2') {
            if ($propjurisdiccion == 'si') {
                $liq["servicio"] = '01020204';
            } else {
                $liq["servicio"] = '01020205';
            }
        }
        if ($exp["categoria"] == '3') {
            if ($propjurisdiccion == 'si') {
                $liq["servicio"] = '01020206';
            } else {
                $liq["servicio"] = '01020207';
            }
        }
        $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
        $tars = retornarRegistrosMysqliApi($mysqli, 'mreg_tarifas', "ano='" . date("Y") . "' and idservicio='" . $liq["servicio"] . "'", "idrango");
        foreach ($tars as $tar) {
            if ($_SESSION["entrada"]["activos"] >= $tar["topeminimo"] && $_SESSION["entrada"]["activos"] <= $tar["topemaximo"]) {
                $liq["valor"] = $tar["tarifa"];
            }
        }
        $_SESSION["jsonsalida"]["liquidacion"][] = $liq;

        // ************************************************************************** //
        // Encuentra el valor de la liquidación de renovación del establecimiento
        // ************************************************************************** //
        if ($matest != '') {
            $liq = array();
            $liq["servicio"] = '01020202';
            $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
            $liq["matricula"] = $matest;
            $liq["nmatricula"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matest . "'", "razonsocial");
            $liq["anorenovar"] = date("Y");
            $liq["cantidad"] = 1;
            $liq["activos"] = $_SESSION["entrada"]["activos"];
            $liq["valor"] = 0;
            $tars = retornarRegistrosMysqliApi($mysqli, 'mreg_tarifas', "ano='" . date("Y") . "' and idservicio='" . $liq["servicio"] . "'", "idrango");
            foreach ($tars as $tar) {
                if ($_SESSION["entrada"]["activos"] >= $tar["topeminimo"] &&
                        $_SESSION["entrada"]["activos"] <= $tar["topemaximo"]) {
                    $liq["valor"] = $tar["tarifa"];
                }
            }
            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
        }

        // ****************************************************************************** //
        // Busca el valor de afiliacion
        // ****************************************************************************** //}
        if ($exp["ctrafiliacion"] == '1') {
            if ($_SESSION["entrada"]["incluirafiliacion"] == 'S') {

                $liq = array();
                $liq["servicio"] = '06010002';
                $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                $liq["matricula"] = $exp["matricula"];
                $liq["nmatricula"] = $exp["razonsocial"];
                $liq["anorenovar"] = date("Y");
                $liq["cantidad"] = 1;
                $liq["activos"] = $_SESSION["entrada"]["activos"];
                $liq["valor"] = 0;
                $tars = retornarRegistroMysqliApi($mysqli, 'mreg_tarifas', "ano='" . date("Y") . "' and idservicio='" . $liq["servicio"] . "'", "idrango");
                foreach ($tars as $tar) {
                    if ($_SESSION["entrada"]["activos"] >= $tar["topeminimo"] &&
                            $_SESSION["entrada"]["activos"] <= $tar["topemaximo"]) {
                        $liq["valor"] = $tar["tarifa"];
                    }
                }
                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
            }
        }

        // ****************************************************************************** //
        // Recupera el expediente
        // ****************************************************************************** //
        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'");

        // ****************************************************************************** //
        // Busca el valor del certificado a incluir
        // ****************************************************************************** //        
        if ($_SESSION["entrada"]["incluircertificado"] == 'S') {
            $liq = array();
            if ($exp["organizacion"] == '01' || $exp["categoria"] == '3') {
                $liq["servicio"] = '01010101';
            } else {
                if (substr($exp["matricula"], 0, 1) == 'S') {
                    $liq["servicio"] = '01010301';
                } else {
                    $liq["servicio"] = '01010102';
                }
            }
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
            $liq["nservicio"] = $serv["nombre"];
            $liq["matricula"] = $exp["matricula"];
            $liq["nmatricula"] = $exp["razonsocial"];
            $liq["anorenovar"] = '';
            $liq["cantidad"] = 1;
            $liq["activos"] = 0;
            $liq["valor"] = $serv["valorservicio"];
            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
        }

        // ****************************************************************************** //
        // Busca el valor del formulario a incluir
        // ****************************************************************************** //
        if ($_SESSION["entrada"]["incluirformulario"] == 'S') {
            $coBfor = 'si';
            if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX' && RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX != 'S') {
                $coBfor = 'no';
            }
            if ($_SESSION["entrada"]["idusuario"] != 'USUPUBXX' && RENOVACION_LIQUIDAR_FORMULARIOS != 'S') {
                $coBfor = 'no';
            }
            if ($coBfor == 'si') {
                $liq = array();
                $liq["servicio"] = RENOVACION_SERV_FORMULARIOS;
                if (substr($exp["matricula"], 0, 1) == 'S') {
                    if (defined('RENOVACION_SERV_FORMULARIOS_ESADL') && RENOVACION_SERV_FORMULARIOS_ESADL != '') {
                        $liq["servicio"] = RENOVACION_SERV_FORMULARIOS_ESADL;
                    }
                }
                $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
                $liq["nservicio"] = $serv["nombre"];
                $liq["matricula"] = $exp["matricula"];
                $liq["nmatricula"] = $exp["razonsocial"];
                $liq["anorenovar"] = '';
                $liq["cantidad"] = 1;
                $liq["activos"] = 0;
                $liq["valor"] = $serv["valorservicio"];
                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
            }
        }


        // ***************************************************************************** //
        // Crea la liquidacion en mreg_liquidacion
        // ***************************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando liquidacion (VC): ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // Datos básicos de la liquidación
        $_SESSION["tramite"]["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
        $_SESSION["tramite"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
        $_SESSION["tramite"]["fecha"] = date("Ymd");
        $_SESSION["tramite"]["hora"] = date("His");
        $_SESSION["tramite"]["idestado"] = '01';
        $_SESSION["tramite"]["iptramite"] = \funcionesGenerales::localizarIP();
        $_SESSION["tramite"]["tipotramite"] = 'renovacionmatricula';
        $_SESSION["tramite"]["tiporenovacion"] = 'prediligenciados';
        if (substr($exp["matricula"], 0, 1) == 'S') {
            $_SESSION["tramite"]["tipotramite"] = 'renovacionesadl';
        }
        $_SESSION["tramite"]["activosbase"] = $_SESSION["entrada"]["activos"];
        $_SESSION["tramite"]["personalbase"] = $_SESSION["entrada"]["personal"];
        $_SESSION["tramite"]["sede"] = $sedeusuario;
        $_SESSION["tramite"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["tramite"]["matriculabase"] = $exp["matricula"];
        $_SESSION["tramite"]["idmatriculabase"] = $exp["matricula"];
        $_SESSION["tramite"]["idexpedientebase"] = $exp["matricula"];
        $_SESSION["tramite"]["nombrebase"] = $exp["razonsocial"];
        $_SESSION["tramite"]["nom1base"] = $exp["nombre1"];
        $_SESSION["tramite"]["nom2base"] = $exp["nombre2"];
        $_SESSION["tramite"]["ape1base"] = $exp["apellido1"];
        $_SESSION["tramite"]["ape2base"] = $exp["apellido2"];
        $_SESSION["tramite"]["tipoidentificacionbase"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacionbase"] = $exp["numid"];
        $_SESSION["tramite"]["organizacionbase"] = $exp["organizacion"];
        $_SESSION["tramite"]["categoriabase"] = $exp["categoria"];

        //
        if (isset($_SESSION["entrada"]["sistemacreacion"]) && $_SESSION["entrada"]["sistemacreacion"] != '') {
            $_SESSION["tramite"]["sistemacreacion"] = $_SESSION["entrada"]["sistemacreacion"];
        } else {
            $_SESSION["tramite"]["sistemacreacion"] = 'API - liquidarRenovacionMultiplesAnios - ' . $_SESSION["entrada"]["usuariows"];
        }

        // Datos del cliente
        $_SESSION["tramite"]["tipocliente"] = '';
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacioncliente"] = $exp["numid"];
        $_SESSION["tramite"]["razonsocialcliente"] = $exp["razonsocial"];
        $_SESSION["tramite"]["nombrecliente"] = $exp["razonsocial"];
        $_SESSION["tramite"]["apellidocliente"] = trim($exp["apellido1"] . ' ' . $exp["apellido2"]);
        $_SESSION["tramite"]["apellido1cliente"] = $exp["apellido1"];
        $_SESSION["tramite"]["apellido2cliente"] = $exp["apellido2"];
        $_SESSION["tramite"]["nombre1cliente"] = $exp["nombre1"];
        $_SESSION["tramite"]["nombre2cliente"] = $exp["nombre2"];
        $_SESSION["tramite"]["email"] = $exp["emailcom"];
        $_SESSION["tramite"]["direccion"] = $exp["dircom"];
        $_SESSION["tramite"]["telefono"] = $exp["telcom1"];
        $_SESSION["tramite"]["movil"] = $exp["telcom2"];
        $_SESSION["tramite"]["idmunicipio"] = $exp["muncom"];
        $_SESSION["tramite"]["procesartodas"] = 'si';

        // Datos del pagador
        $_SESSION["tramite"]["tipopagador"] = '';
        $_SESSION["tramite"]["tipoidentificacionpagador"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacionpagador"] = $exp["numid"];
        $_SESSION["tramite"]["razonsocialpagador"] = $exp["razonsocial"];
        $_SESSION["tramite"]["apellido1pagador"] = $exp["apellido1"];
        $_SESSION["tramite"]["apellido2pagador"] = $exp["apellido2"];
        $_SESSION["tramite"]["nombre1pagador"] = $exp["nombre1"];
        $_SESSION["tramite"]["nombre2pagador"] = $exp["nombre2"];
        $_SESSION["tramite"]["emailpagador"] = $exp["emailcom"];
        $_SESSION["tramite"]["direccionpagador"] = $exp["dircom"];
        $_SESSION["tramite"]["telefonopagador"] = $exp["telcom1"];
        $_SESSION["tramite"]["movilpagador"] = $exp["telcom2"];
        $_SESSION["tramite"]["municipiopagador"] = $exp["muncom"];
        $_SESSION["tramite"]["valorbruto"] = 0;
        $_SESSION["tramite"]["valorbaseiva"] = 0;
        $_SESSION["tramite"]["valoriva"] = 0;
        $_SESSION["tramite"]["valortotal"] = 0;
        $_SESSION["tramite"]["liquidacion"] = array();

        //
        $i = 0;
        foreach ($_SESSION["jsonsalida"]["liquidacion"] as $lliq) {
            if ($lliq["servicio"] != '') {
                $i++;
                $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = '000';
                $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $lliq["servicio"];
                $_SESSION["tramite"]["liquidacion"][$i]["cc"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $lliq["matricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $lliq["nmatricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["ano"] = $lliq["anorenovar"];
                $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = 1;
                $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $lliq["activos"];
                $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $lliq["valor"];
                $_SESSION["tramite"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"] + $lliq["valor"];
                $_SESSION["tramite"]["valortotal"] = $_SESSION["tramite"]["valortotal"] + $lliq["valor"];
            }
        }

        // expedientes
        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'");
        $i = 1;
        $_SESSION["tramite"]["expedientes"][$i] = array();
        $_SESSION["tramite"]["expedientes"][$i]["cc"] = CODIGO_EMPRESA;
        $_SESSION["tramite"]["expedientes"][$i]["matricula"] = $_SESSION["entrada"]["matricula"];
        $_SESSION["tramite"]["expedientes"][$i]["proponente"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["numrue"] = $exp["nit"];
        $_SESSION["tramite"]["expedientes"][$i]["idtipoidentificacion"] = $exp["idclase"];
        $_SESSION["tramite"]["expedientes"][$i]["identificacion"] = $exp["numid"];
        $_SESSION["tramite"]["expedientes"][$i]["razonsocial"] = $exp["razonsocial"];
        $_SESSION["tramite"]["expedientes"][$i]["ape1"] = $exp["apellido1"];
        $_SESSION["tramite"]["expedientes"][$i]["ape2"] = $exp["apellido2"];
        $_SESSION["tramite"]["expedientes"][$i]["nom1"] = $exp["nombre1"];
        $_SESSION["tramite"]["expedientes"][$i]["nom2"] = $exp["nombre2"];
        $_SESSION["tramite"]["expedientes"][$i]["organizacion"] = $exp["organizacion"];
        $_SESSION["tramite"]["expedientes"][$i]["categoria"] = $exp["categoria"];
        $_SESSION["tramite"]["expedientes"][$i]["afiliado"] = $exp["ctrafiliacion"];
        $_SESSION["tramite"]["expedientes"][$i]["propietariojurisdiccion"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["primeranorenovado"] = date("Y");
        $_SESSION["tramite"]["expedientes"][$i]["ultimoanoafiliado"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["ultimoanorenovado"] = date("Y");
        $_SESSION["tramite"]["expedientes"][$i]["ultimosactivos"] = $exp["acttot"];
        $_SESSION["tramite"]["expedientes"][$i]["nuevosactivos"] = $_SESSION["entrada"]["activos"];
        $_SESSION["tramite"]["expedientes"][$i]["actividad"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["registrobase"] = 'S';
        $_SESSION["tramite"]["expedientes"][$i]["benart7"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["benley1780"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["renovaresteano"] = 'si';
        $_SESSION["tramite"]["expedientes"][$i]["fechanacimiento"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["fechamatricula"] = $exp["fecmatricula"];
        $_SESSION["tramite"]["expedientes"][$i]["fecmatant"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["reliquidacion"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["controlpot"] = '';
        $_SESSION["tramite"]["expedientes"][$i]["dircom"] = $exp["dircom"];
        $_SESSION["tramite"]["expedientes"][$i]["muncom"] = $exp["muncom"];

        // establecimiento
        if ($matest != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matest . "'");
            $i++;
            $_SESSION["tramite"]["expedientes"][$i] = array();
            $_SESSION["tramite"]["expedientes"][$i]["cc"] = CODIGO_EMPRESA;
            $_SESSION["tramite"]["expedientes"][$i]["matricula"] = $_SESSION["entrada"]["matricula"];
            $_SESSION["tramite"]["expedientes"][$i]["proponente"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["numrue"] = $exp["nit"];
            $_SESSION["tramite"]["expedientes"][$i]["idtipoidentificacion"] = $exp["idclase"];
            $_SESSION["tramite"]["expedientes"][$i]["identificacion"] = $exp["numid"];
            $_SESSION["tramite"]["expedientes"][$i]["razonsocial"] = $exp["razonsocial"];
            $_SESSION["tramite"]["expedientes"][$i]["ape1"] = $exp["apellido1"];
            $_SESSION["tramite"]["expedientes"][$i]["ape2"] = $exp["apellido2"];
            $_SESSION["tramite"]["expedientes"][$i]["nom1"] = $exp["nombre1"];
            $_SESSION["tramite"]["expedientes"][$i]["nom2"] = $exp["nombre2"];
            $_SESSION["tramite"]["expedientes"][$i]["organizacion"] = $exp["organizacion"];
            $_SESSION["tramite"]["expedientes"][$i]["categoria"] = $exp["categoria"];
            $_SESSION["tramite"]["expedientes"][$i]["afiliado"] = $exp["ctrafiliacion"];
            $_SESSION["tramite"]["expedientes"][$i]["propietariojurisdiccion"] = 'S';
            $_SESSION["tramite"]["expedientes"][$i]["primeranorenovado"] = date("Y");
            $_SESSION["tramite"]["expedientes"][$i]["ultimoanoafiliado"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["ultimoanorenovado"] = date("Y");
            $_SESSION["tramite"]["expedientes"][$i]["ultimosactivos"] = $exp["actvin"];
            $_SESSION["tramite"]["expedientes"][$i]["nuevosactivos"] = $_SESSION["entrada"]["activos"];
            $_SESSION["tramite"]["expedientes"][$i]["actividad"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["registrobase"] = 'S';
            $_SESSION["tramite"]["expedientes"][$i]["benart7"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["benley1780"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["renovaresteano"] = 'si';
            $_SESSION["tramite"]["expedientes"][$i]["fechanacimiento"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["fechamatricula"] = $exp["fecmatricula"];
            $_SESSION["tramite"]["expedientes"][$i]["fecmatant"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["reliquidacion"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["controlpot"] = '';
            $_SESSION["tramite"]["expedientes"][$i]["dircom"] = $exp["dircom"];
            $_SESSION["tramite"]["expedientes"][$i]["muncom"] = $exp["muncom"];
        }


        //
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["jsonsalida"]["emailcontrol"];
        $_SESSION["tramite"]["tramitepresencial"] = '7'; // Prediligenciado
        //
        //
        $res = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando liquidacion : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
        $_SESSION["jsonsalida"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"];
        $_SESSION["jsonsalida"]["valorbaseiva"] = $_SESSION["tramite"]["valorbaseiva"];
        $_SESSION["jsonsalida"]["valoriva"] = $_SESSION["tramite"]["valoriva"];
        $_SESSION["jsonsalida"]["valortotal"] = $_SESSION["tramite"]["valortotal"];

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function liquidarRenovacionNormal(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $resError = set_error_handler('myErrorHandler');

        $feinicio045 = '';
        if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
            $feinicio045 = '99999999';
        } else {
            $feinicio045 = FECHA_INICIO_DECRETO_045;
        }

        $nameLog = 'api_liquidarRenovacionNormal_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idusuario"] = '';
        $_SESSION["jsonsalida"]["emailcontrol"] = '';
        $_SESSION["jsonsalida"]["celularcontrol"] = '';
        $_SESSION["jsonsalida"]["nombrecontrol"] = '';
        $_SESSION["jsonsalida"]["identificacioncontrol"] = '';
        $_SESSION["jsonsalida"]["matriculas"] = array();
        $_SESSION["jsonsalida"]["incluirafiliacion"] = '';
        $_SESSION["jsonsalida"]["incluircertificado"] = '';
        $_SESSION["jsonsalida"]["incluirformulario"] = '';
        $_SESSION["jsonsalida"]["cumple1780"] = '';
        $_SESSION["jsonsalida"]["mantiene1780"] = '';
        $_SESSION["jsonsalida"]["renuncia1780"] = '';
        $_SESSION["jsonsalida"]["idliquidacion"] = '';
        $_SESSION["jsonsalida"]["numerorecuperacion"] = '';
        $_SESSION["jsonsalida"]["liquidacion"] = array();
        $_SESSION["jsonsalida"]["expedientes"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("incluirafiliacion", true);
        $api->validarParametro("incluircertificado", true);
        $api->validarParametro("incluirformulario", true);
        $api->validarParametro("cumple1780", true);
        $api->validarParametro("mantiene1780", true);
        $api->validarParametro("renuncia1780", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('liquidarRenovacionNormal', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // *********************************************************************** //
        // Mueve parámetros al arreglo de salida
        // *********************************************************************** // 
        $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["jsonsalida"]["incluirafiliacion"] = $_SESSION["entrada"]["incluirafiliacion"];
        $_SESSION["jsonsalida"]["incluircertificado"] = $_SESSION["entrada"]["incluircertificado"];
        $_SESSION["jsonsalida"]["incluirformulario"] = $_SESSION["entrada"]["incluirformulario"];
        $_SESSION["jsonsalida"]["cumple1780"] = $_SESSION["entrada"]["cumple1780"];
        $_SESSION["jsonsalida"]["mantiene1780"] = $_SESSION["entrada"]["mantiene1780"];
        $_SESSION["jsonsalida"]["renuncia1780"] = $_SESSION["entrada"]["renuncia1780"];

        // *********************************************************************** //
        // Recupera lista de matrículas, activos y personal
        // *********************************************************************** //
        $prim = '';
        $cantfor = 0;
        foreach ($_SESSION["entrada"]["matriculas"] as $m) {
            $_SESSION["jsonsalida"]["matriculas"][] = $m;
            if ($prim == '') {
                $prim = $m["matricula"];
            }
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

        //
        $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["fcortemesdia"] = substr($_SESSION["generales"]["fcorte"], 4, 4);

        // ************************************************************************** //
        // Valida el idusuario reportado
        // En caso de reportar USUPUBXX deben indicarse los datos del cliente logueado
        // ************************************************************************** //
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            $sedeusuario = '99';
            if (!isset($_SESSION["entrada"]["emailcontrol"]) || $_SESSION["entrada"]["emailcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el email del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["nombrecontrol"]) || $_SESSION["entrada"]["nombrecontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el nombre del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["celularcontrol"]) || $_SESSION["entrada"]["celularcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el celular del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["identificacioncontrol"]) || $_SESSION["entrada"]["identificacioncontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó la identificación del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
            if ($usux === false || empty($usux)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no localizado en la BD del sistema';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechainactivacion"]), "0") != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario reportado está inactivo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechaactivacion"]), "0") == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario reportado no está activo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $sedeusuario = $usux["idsede"];
            $_SESSION["entrada"]["emailcontrol"] = $usux["email"];
            $_SESSION["entrada"]["nombrecontrol"] = $usux["nombreusuario"];
            $_SESSION["entrada"]["celularcontrol"] = $usux["celular"];
            $_SESSION["entrada"]["identificacioncontrol"] = $usux["identificacion"];
        }
        $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
        $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];

        // ****************************************************************************** //
        // Localiza el expediente
        // ****************************************************************************** //
        $liqtideprop = '';
        $liqideprop = '';
        $liqmatprop = '';
        $liqcamprop = '';
        $liqactprop = 0;
        $liqcantesttotnal = 0;
        $cantesttot = 0;

        // Encuentra datos del propietario en la renovación
        $mats = array();
        foreach ($_SESSION["jsonsalida"]["matriculas"] as $matri) {
            if (!isset($mats[$matri["matricula"]])) {
                $mats[$matri["matricula"]] = array(
                    'matricula' => $matri["matricula"],
                    'activos' => $matri["activos"],
                    'anorenovacion' => $matri["anorenovacion"]
                );
            } else {
                $mats[$matri["matricula"]]["activos"] = $matri["activos"];
                $mats[$matri["matricula"]]["anorenovacion"] = $matri["anorenovacion"];
            }
        }
        foreach ($mats as $matri) {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matri["matricula"] . "'", "matricula,organizacion,categoria,idclase,numid");
            if ($exp && !empty($exp)) {
                if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                    $liqtideprop = $exp["idclase"];
                    $liqideprop = $exp["numid"];
                    $liqactprop = $matri["activos"];
                }
            }
        }


        // Encuentra cantidad de establecimientos a renovar
        $unamatest = '';
        $unaorgest = '';
        $unacatest = '';
        foreach ($mats as $matri) {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matri["matricula"] . "'", "matricula,organizacion,categoria");
            if ($exp && !empty($exp)) {
                if ($exp["organizacion"] == '02' || ($exp["organizacion"] > '02' && ($exp["categoria"] == '2' || $exp["categoria"] == '3'))) {
                    $unamatest = $exp["matricula"];
                    $unaorgest = $exp["organizacion"];
                    $unacatest = $exp["categoria"];
                    $cantesttot++;
                }
            }
        }

        // Si el propietario no renueva en el trámite, lo busca.
        if ($liqtideprop == '') {
            if ($unamatest != '') {
                if ($unaorgest == '02') {
                    $prop = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $unamatest . "'", "id");
                    if ($prop && !empty($prop)) {
                        foreach ($prop as $p) {
                            if ($p["estado"] == 'V') {
                                if ($p["codigocamara"] == CODIGO_EMPRESA) {
                                    $prop1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $p["matriculapropietario"] . "'");
                                    if ($prop1 && !empty($prop1)) {
                                        $liqtideprop = $prop1["idclase"];
                                        $liqideprop = $prop1["numid"];
                                        $liqactprop = $prop1["acttot"];
                                    }
                                } else {
                                    $liqtideprop = $p["tipoidentificacion"];
                                    $liqideprop = $prop1["identificacion"];
                                    $liqactprop = 0;
                                }
                            }
                        }
                    }
                } else {
                    if ($unacatest == '2' || $unacatest == '3') {
                        $est1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $unamatest . "'");
                        if ($est1 && !empty($est1)) {
                            $liqtideprop = '2';
                            $liqideprop = $est1["cpnumnit"];
                        }
                    }
                }
            }
        }

        // Encuentra el valor de los activos del propietario a nivel nacional y
        // La cantidad de establecimientos a nivel nacional
        if ($feusouvb <= date("Ymd")) {
            if ($cantesttot > 0) {
                if ($liqtideprop != '') {
                    $prop = \funcionesRues::consultarRegMerIdentificacionActivos($_SESSION["entrada"]["idusuario"], $liqtideprop, $liqideprop);
                    if ($prop) {
                        if ($liqactprop == 0) {
                            $liqactprop = $prop["activos_totales"];
                        }
                        $liqcantesttotnal = $prop["establecimientos_locales"] + $prop["establecimientos_foraneos"];
                    }
                }
            }
        } else {
            $liqactprop = 0;
            $liqcantesttotnal = 0;
        }
        $msg = "(Metodo liquidar renovacion normal) Datos del propietario a nivel nacional \r\n";
        $msg .= "Identificación el propietario : " . $liqtideprop . "-" . $liqideprop . "\r\n";
        $msg .= "Cantidad de establecimientos a nivel nacional : " . $liqcantesttotnal . "\r\n";
        $msg .= "Establecimientos/años a renovar : " . $cantesttot . "\r\n";
        $msg .= "Activos del propietario : " . $liqactprop . "\r\n";
        \logApi::general2($nameLog, '', $msg);

        //
        foreach ($_SESSION["jsonsalida"]["matriculas"] as $m) {

            $cantfor++;
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m["matricula"] . "'");

            // ****************************************************************************** //
            // En caso que el expediente no exista
            // ****************************************************************************** //
            if ($exp === false || empty($exp)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m["matricula"] . ' no encontrado en la base de datos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }




            // ****************************************************************************** //
            // Si matrícula no está activa
            // ****************************************************************************** //
            if ($exp["ctrestmatricula"] != 'MA' && $exp["ctrestmatricula"] != 'IA') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m["matricula"] . ' no se encuentra activo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            // ****************************************************************************** //
            // Si ya está renovado al año actual
            // ****************************************************************************** //
            if ($exp["ultanoren"] == date("Y")) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m["matricula"] . ' ya renovó este año';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            // ****************************************************************************** //
            // Se excluyen disueltas
            // ****************************************************************************** //
            /*
              if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
              if (strpos($exp["razonsocial"], 'EN LIQUIDACION') ||
              strpos($exp["razonsocial"], 'EN REORGANIZACION') ||
              strpos($exp["razonsocial"], 'EN REESTRUCTURACION')) {
              $mysqli->close();
              $_SESSION["jsonsalida"]["codigoerror"] = "9999";
              $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m["matricula"] . ' se encuentra disuelto';
              $api->response($api->json($_SESSION["jsonsalida"]), 200);
              }
              }
             */

            // ****************************************************************************** //
            // Se excluyen disueltas por terminos
            // ****************************************************************************** //
            /*
              if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
              if (ltrim(trim($exp["fecvigencia"]), "0") != '' && ltrim(trim($exp["fecvigencia"]), "0") != '99999999' && ltrim(trim($exp["fecvigencia"]), "0") != '9999999') {
              if ($exp["fecvigencia"] < date("Ymd")) {
              $mysqli->close();
              $_SESSION["jsonsalida"]["codigoerror"] = "9999";
              $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m["matricula"] . ' se encuentra disuelta por vencimiento de términos';
              $api->response($api->json($_SESSION["jsonsalida"]), 200);
              }
              }
              }
             */

            // ****************************************************************************** //
            // pnat fallecidos
            // ****************************************************************************** //
            if ($exp["organizacion"] == '01') {
                if (strpos($exp["razonsocial"], 'FALLECIDO') || strpos($exp["razonsocial"], 'FALLECIDA')) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m["matricula"] . ' pertenece a una persona natural fallecida';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            // **************************************************************************************** //
            // Si el valor de los activos es inferior al del año anterior
            // **************************************************************************************** //
            if ($exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                if ($exp["actvin"] > $m["activos"]) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Los activos de la matrícula ' . $m["matricula"] . ' indicados son inferiores a los del año anterior';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
            if ($exp["organizacion"] == '01' || $exp["categoria"] == '1') {
                if ($exp["acttot"] > $m["activos"]) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Los activos de la matrícula ' . $m["matricula"] . ' indicados son inferiores a los del año anterior';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            // **************************************************************************************** //
            // Si es sucursal o agencia verifica que si la principal esta en la misma jurisdiccion o no
            // **************************************************************************************** //
            $propjurisdiccion = 'no';
            if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                if ($exp["cpcodcam"] == '' || $exp["cpcodcam"] == CODIGO_EMPRESA) {
                    $propjurisdiccion = 'si';
                }
            }
            if ($exp["organizacion"] == '02') {
                if ($prim == $exp["matricula"]) {
                    $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $exp["matricula"] . "'", "id");
                    foreach ($props as $px) {
                        if ($px["estado"] == 'V') {
                            if ($px["matriculapropietario"] != '') {
                                if ($px["codigocamara"] == '' || $px["codigocamara"] == CODIGO_EMPRESA) {
                                    $propjurisdiccion = 'si';
                                }
                            }
                        }
                    }
                } else {
                    $propjurisdiccion = 'si';
                }
            }

            // ************************************************************************** //
            // Encuentra el valor de la liquidación de renovación del comerciante
            // ************************************************************************** //

            $anosrenovados = 0;
            $anoini = $exp["ultanoren"] + 1;
            for ($ix = $anoini; $ix <= date("Y"); $ix++) {
                $anosrenovados++;
                $liq = array();
                $liq["servicio"] = '';
                $liq["cc"] = CODIGO_EMPRESA;
                $liq["matricula"] = $exp["matricula"];
                $liq["nmatricula"] = $exp["razonsocial"];
                $liq["anorenovar"] = $ix;
                $liq["cantidad"] = 1;
                $liq["activos"] = $m["activos"];
                $liq["valor"] = 0;
                if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                    if (substr($exp["matricula"], 0, 1) == 'S') {
                        $liq["servicio"] = '01020208';
                    } else {
                        $liq["servicio"] = '01020201';
                    }
                }
                if ($exp["organizacion"] == '02') {
                    if ($propjurisdiccion == 'si') {
                        $liq["servicio"] = '01020202';
                    } else {
                        $liq["servicio"] = '01020203';
                    }
                }
                if ($exp["categoria"] == '2') {
                    if ($propjurisdiccion == 'si') {
                        $liq["servicio"] = '01020204';
                    } else {
                        $liq["servicio"] = '01020205';
                    }
                }
                if ($exp["categoria"] == '3') {
                    if ($propjurisdiccion == 'si') {
                        $liq["servicio"] = '01020206';
                    } else {
                        $liq["servicio"] = '01020207';
                    }
                }
                $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $ix, 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal);
                $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
            }

            // ************************************************************************** //
            // Si renueva más de un año no tendría beneficios
            // ************************************************************************** //
            if ($exp["ctrbenley1780"] == 'S') {
                if ($anosrenovados != 1) {
                    $exp["ctrbenley1780"] = 'P';
                } else {
                    if (date("md") > $_SESSION["generales"]["fcortemesdia"]) {
                        $exp["ctrbenley1780"] = 'P';
                    }
                }
            }

            // ************************************************************************** //
            // Suma el expediente a la lista de expedientes
            // ************************************************************************** //
            $lin = array();
            $lin["cc"] = CODIGO_EMPRESA;
            $lin["matricula"] = $exp["matricula"];
            $lin["proponente"] = '';
            $lin["numrue"] = $exp["nit"];
            $lin["idtipoidentificacion"] = $exp["idclase"];
            $lin["identificacion"] = $exp["numid"];
            $lin["razonsocial"] = $exp["razonsocial"];
            $lin["ape1"] = $exp["apellido1"];
            $lin["ape2"] = $exp["apellido2"];
            $lin["nom1"] = $exp["nombre1"];
            $lin["nom2"] = $exp["nombre2"];
            $lin["organizacion"] = $exp["organizacion"];
            $lin["categoria"] = $exp["categoria"];
            $lin["afiliado"] = $exp["ctrafiliacion"];
            $lin["propietariojurisdiccion"] = '';
            $lin["primeranorenovado"] = $exp["ultanoren"] + 1;
            $lin["ultimoanoafiliado"] = '';
            $lin["ultimoanorenovado"] = date("Y");
            if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                $lin["ultimosactivos"] = $exp["acttot"];
            } else {
                $lin["ultimosactivos"] = $exp["actvin"];
            }
            $lin["nuevosactivos"] = $m["activos"];
            $lin["actividad"] = '';
            $lin["registrobase"] = 'S';
            $lin["benart7"] = $exp["ctrbenart7"];
            $lin["benley1780"] = $exp["ctrbenley1780"];
            $lin["renovaresteano"] = 'si';
            $lin["fechanacimiento"] = '';
            $lin["fechamatricula"] = $exp["fecmatricula"];
            $lin["fecmatant"] = '';
            $lin["reliquidacion"] = '';
            $lin["controlpot"] = '';
            $lin["dircom"] = $exp["dircom"];
            $lin["muncom"] = $exp["muncom"];
            $lin["valor"] = $liq["valor"];
            $_SESSION["jsonsalida"]["expedientes"][] = $lin;

            // ******************************************************************************** //
            // Verifica que efetcivamente se puedan liquidar beneficios y no haya renunciado
            // ******************************************************************************** //
            if ($exp["ctrbenley1780"] == 'S') {
                if ($_SESSION["entrada"]["cumple1780"] == 'S' && $_SESSION["jsonsalida"]["mantiene1780"] == 'S' && $_SESSION["jsonsalida"]["renuncia1780"] == 'N') {
                    $exp["ctrbenley1780"] = 'S';
                } else {
                    if ($_SESSION["entrada"]["cumple1780"] == 'S' && $_SESSION["jsonsalida"]["mantiene1780"] == 'S' && $_SESSION["jsonsalida"]["renuncia1780"] == 'S') {
                        $exp["ctrbenley1780"] = 'R';
                    } else {
                        $exp["ctrbenley1780"] = 'P';
                    }
                }
            }

            // ************************************************************************** //
            // Liquida beneficio de la Ley 1780
            // ************************************************************************** //
            if ($anosrenovados == 1) {
                if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                    if ($exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                        if (substr($exp["fecmatricula"], 0, 4) == date("Y") - 1) {
                            if ($exp["ctrbenley1780"] == 'S') {
                                $liq = array();
                                $liq["servicio"] = '01090111';
                                $liq["cc"] = CODIGO_EMPRESA;
                                $liq["matricula"] = $exp["matricula"];
                                $liq["nmatricula"] = $exp["razonsocial"];
                                $liq["anorenovar"] = date("Y");
                                $liq["cantidad"] = 1;
                                $liq["activos"] = $m["activos"];
                                $liq["valor"] = 0;
                                $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01020201', $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal) * -1;
                                $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
                            }
                        }
                    }
                }
            }

            // ****************************************************************************** //
            // Busca el valor de afiliacion
            // ****************************************************************************** //}
            if ($exp["ctrafiliacion"] == '1') {
                if ($_SESSION["entrada"]["incluirafiliacion"] == 'S') {
                    $liq = array();
                    $liq["servicio"] = '06010002';
                    $liq["cc"] = CODIGO_EMPRESA;
                    $liq["matricula"] = $exp["matricula"];
                    $liq["nmatricula"] = $exp["razonsocial"];
                    $liq["anorenovar"] = date("Y");
                    $liq["cantidad"] = 1;
                    $liq["activos"] = $m["activos"];
                    $liq["valor"] = 0;
                    $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal);
                    $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                    $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
                }
            }
        }

        // ****************************************************************************** //
        // Recupera el primer expediente
        // ****************************************************************************** //        
        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $prim . "'");

        // ****************************************************************************** //
        // Busca el valor del certificado a incluir
        // ****************************************************************************** //        
        if ($_SESSION["entrada"]["incluircertificado"] == 'S') {
            $liq = array();
            if ($exp["organizacion"] == '01' || $exp["categoria"] == '3') {
                $liq["servicio"] = '01010101';
            } else {
                if (substr($exp["matricula"], 0, 1) == 'S') {
                    $liq["servicio"] = '01010301';
                } else {
                    $liq["servicio"] = '01010102';
                }
            }
            $liq["cc"] = CODIGO_EMPRESA;
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
            $liq["matricula"] = $exp["matricula"];
            $liq["nmatricula"] = $exp["razonsocial"];
            $liq["anorenovar"] = '';
            $liq["cantidad"] = 1;
            $liq["activos"] = 0;
            $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal);
            $liq["nservicio"] = $serv["nombre"];
            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
        }

        // ****************************************************************************** //
        // Busca el valor del formulario a incluir
        // ****************************************************************************** //
        if ($_SESSION["entrada"]["incluirformulario"] == 'S') {
            if ($cantfor == 0 || $cantfor == 1 || $cantfor == 2) {
                $cantfor = 1;
            } else {
                $cantfor = $cantfor - 1;
            }

            $liq = array();
            $liq["servicio"] = RENOVACION_SERV_FORMULARIOS;
            if (substr($exp["matricula"], 0, 1) == 'S') {
                if (defined('RENOVACION_SERV_FORMULARIOS_ESADL') && RENOVACION_SERV_FORMULARIOS_ESADL != '') {
                    $liq["servicio"] = RENOVACION_SERV_FORMULARIOS_ESADL;
                }
            }
            $liq["cc"] = CODIGO_EMPRESA;
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
            $liq["matricula"] = $exp["matricula"];
            $liq["nmatricula"] = $exp["razonsocial"];
            $liq["anorenovar"] = '';
            $liq["cantidad"] = $cantfor;
            $liq["activos"] = 0;
            $liq["valor"] = $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal) * $cantfor;
            $liq["nservicio"] = $serv["nombre"];
            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
        }

        // ***************************************************************************** //
        // Crea la liquidacion en mreg_liquidacion
        // ***************************************************************************** //
        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $prim . "'");
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando liquidacion (VC): ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // Datos básicos de la liquidación
        $_SESSION["tramite"]["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
        $_SESSION["tramite"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
        $_SESSION["tramite"]["fecha"] = date("Ymd");
        $_SESSION["tramite"]["hora"] = date("His");
        $_SESSION["tramite"]["idestado"] = '01';
        $_SESSION["tramite"]["iptramite"] = '';
        $_SESSION["tramite"]["tipotramite"] = 'renovacionmatricula';
        if (substr($exp["matricula"], 0, 1) == 'S') {
            $_SESSION["tramite"]["tipotramite"] = 'renovacionesadl';
        }
        $_SESSION["tramite"]["sede"] = $sedeusuario;
        $_SESSION["tramite"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["tramite"]["matriculabase"] = $exp["matricula"];
        $_SESSION["tramite"]["idmatriculabase"] = $exp["matricula"];
        $_SESSION["tramite"]["idexpedientebase"] = $exp["matricula"];
        $_SESSION["tramite"]["nombrebase"] = $exp["razonsocial"];
        $_SESSION["tramite"]["nom1base"] = $exp["nombre1"];
        $_SESSION["tramite"]["nom2base"] = $exp["nombre2"];
        $_SESSION["tramite"]["ape1base"] = $exp["apellido1"];
        $_SESSION["tramite"]["ape2base"] = $exp["apellido2"];
        $_SESSION["tramite"]["tipoidentificacionbase"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacionbase"] = $exp["numid"];
        $_SESSION["tramite"]["organizacionbase"] = $exp["organizacion"];
        $_SESSION["tramite"]["categoriabase"] = $exp["categoria"];

        //
        if (isset($_SESSION["entrada"]["sistemacreacion"]) && $_SESSION["entrada"]["sistemacreacion"] != '') {
            $_SESSION["tramite"]["sistemacreacion"] = $_SESSION["entrada"]["sistemacreacion"];
        } else {
            $_SESSION["tramite"]["sistemacreacion"] = 'API - liquidarRenovacionNormal - ' . $_SESSION["entrada"]["usuariows"];
        }

        // Datos del cliente
        $_SESSION["tramite"]["tipocliente"] = '';
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacioncliente"] = $exp["numid"];
        $_SESSION["tramite"]["razonsocialcliente"] = $exp["razonsocial"];
        $_SESSION["tramite"]["nombrecliente"] = $exp["razonsocial"];
        $_SESSION["tramite"]["apellidocliente"] = trim($exp["apellido1"] . ' ' . $exp["apellido2"]);
        $_SESSION["tramite"]["apellido1cliente"] = $exp["apellido1"];
        $_SESSION["tramite"]["apellido2cliente"] = $exp["apellido2"];
        $_SESSION["tramite"]["nombre1cliente"] = $exp["nombre1"];
        $_SESSION["tramite"]["nombre2cliente"] = $exp["nombre2"];
        $_SESSION["tramite"]["email"] = $exp["emailcom"];
        $_SESSION["tramite"]["direccion"] = $exp["dircom"];
        $_SESSION["tramite"]["telefono"] = $exp["telcom1"];
        $_SESSION["tramite"]["movil"] = $exp["telcom2"];
        $_SESSION["tramite"]["idmunicipio"] = $exp["muncom"];

        // Datos del pagador
        $_SESSION["tramite"]["tipopagador"] = '';
        $_SESSION["tramite"]["tipoidentificacionpagador"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacionpagador"] = $exp["numid"];
        $_SESSION["tramite"]["razonsocialpagador"] = $exp["razonsocial"];
        $_SESSION["tramite"]["apellido1pagador"] = $exp["apellido1"];
        $_SESSION["tramite"]["apellido2pagador"] = $exp["apellido2"];
        $_SESSION["tramite"]["nombre1pagador"] = $exp["nombre1"];
        $_SESSION["tramite"]["nombre2pagador"] = $exp["nombre2"];
        $_SESSION["tramite"]["emailpagador"] = $exp["emailcom"];
        $_SESSION["tramite"]["direccionpagador"] = $exp["dircom"];
        $_SESSION["tramite"]["telefonopagador"] = $exp["telcom1"];
        $_SESSION["tramite"]["movilpagador"] = $exp["telcom2"];
        $_SESSION["tramite"]["municipiopagador"] = $exp["muncom"];
        $_SESSION["tramite"]["valorbruto"] = 0;
        $_SESSION["tramite"]["valorbaseiva"] = 0;
        $_SESSION["tramite"]["valoriva"] = 0;
        $_SESSION["tramite"]["valortotal"] = 0;
        $_SESSION["tramite"]["liquidacion"] = array();

        // Arreglo de liquidacion
        $i = 0;
        foreach ($_SESSION["jsonsalida"]["liquidacion"] as $lliq) {
            if (isset($lliq["servicio"]) && $lliq["servicio"] != '') {
                $i++;
                $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = '000';
                $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $lliq["servicio"];
                $_SESSION["tramite"]["liquidacion"][$i]["cc"] = $lliq["cc"];
                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $lliq["matricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $lliq["nmatricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["ano"] = $lliq["anorenovar"];
                $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = 1;
                $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $lliq["activos"];
                $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $lliq["valor"];
                $_SESSION["tramite"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"] + $lliq["valor"];
                $_SESSION["tramite"]["valortotal"] = $_SESSION["tramite"]["valortotal"] + $lliq["valor"];
            }
        }

        // Arreglo de expedientes
        $i = 0;
        foreach ($_SESSION["jsonsalida"]["expedientes"] as $lexp) {
            $i++;
            $_SESSION["tramite"]["expedientes"][$i] = $lexp;
        }
        unset($_SESSION["jsonsalida"]["expedientes"]);

        //
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["jsonsalida"]["emailcontrol"];
        $_SESSION["tramite"]["tramitepresencial"] = '1'; // Inicio virtual
        //
        $res = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando liquidacion : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
        $_SESSION["jsonsalida"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"];
        $_SESSION["jsonsalida"]["valorbaseiva"] = $_SESSION["tramite"]["valorbaseiva"];
        $_SESSION["jsonsalida"]["valoriva"] = $_SESSION["tramite"]["valoriva"];
        $_SESSION["jsonsalida"]["valortotal"] = $_SESSION["tramite"]["valortotal"];

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function liquidarRenovacionMultiplesAniosAnterior(API $api) {
        // return $this->liquidarRenovacionMultiplesAniosNueva($api);

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $resError = set_error_handler('myErrorHandler');
        $nameLog = 'api_liquidarRenovacionMultiplesAnios_' . date("Ymd");

        $feinicio045 = '';
        if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
            $feinicio045 = '99999999';
        } else {
            $feinicio045 = FECHA_INICIO_DECRETO_045;
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idusuario"] = '';
        $_SESSION["jsonsalida"]["emailcontrol"] = '';
        $_SESSION["jsonsalida"]["celularcontrol"] = '';
        $_SESSION["jsonsalida"]["nombrecontrol"] = '';
        $_SESSION["jsonsalida"]["identificacioncontrol"] = '';
        $_SESSION["jsonsalida"]["matriculas"] = array();
        $_SESSION["jsonsalida"]["incluirafiliacion"] = '';
        $_SESSION["jsonsalida"]["incluircertificado"] = '';
        $_SESSION["jsonsalida"]["incluirformulario"] = '';
        $_SESSION["jsonsalida"]["cumple1780"] = '';
        $_SESSION["jsonsalida"]["mantiene1780"] = '';
        $_SESSION["jsonsalida"]["renuncia1780"] = '';
        $_SESSION["jsonsalida"]["idliquidacion"] = '';
        $_SESSION["jsonsalida"]["numerorecuperacion"] = '';
        $_SESSION["jsonsalida"]["liquidacion"] = array();
        $_SESSION["jsonsalida"]["expedientes"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("incluirafiliacion", false);
        $api->validarParametro("incluircertificado", false);
        $api->validarParametro("incluirformulario", false);
        $api->validarParametro("cumple1780", false);
        $api->validarParametro("mantiene1780", false);
        $api->validarParametro("renuncia1780", false);
        $api->validarParametro("ambiente", false);

        //
        if (!isset($_SESSION["entrada"]["incluirafiliacion"])) {
            $_SESSION["entrada"]["incluirafiliacion"] = '';
        }
        if (!isset($_SESSION["entrada"]["incluircertificado"])) {
            $_SESSION["entrada"]["incluircertificado"] = '';
        }
        if (!isset($_SESSION["entrada"]["incluirformulario"])) {
            $_SESSION["entrada"]["incluirformulario"] = '';
        }
        if (!isset($_SESSION["entrada"]["cumple1780"])) {
            $_SESSION["entrada"]["cumple1780"] = '';
        }
        if (!isset($_SESSION["entrada"]["mantiene1780"])) {
            $_SESSION["entrada"]["mantiene1780"] = '';
        }
        if (!isset($_SESSION["entrada"]["renuncia1780"])) {
            $_SESSION["entrada"]["renuncia1780"] = '';
        }

        // $api->validarParametro("eliminarliquidacion", false); // S       
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('liquidarRenovacionMultiplesAnios', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // *********************************************************************** //
        // Mueve parámetros al arreglo de salida
        // *********************************************************************** // 
        $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["jsonsalida"]["incluirafiliacion"] = $_SESSION["entrada"]["incluirafiliacion"];
        $_SESSION["jsonsalida"]["incluircertificado"] = $_SESSION["entrada"]["incluircertificado"];
        $_SESSION["jsonsalida"]["incluirformulario"] = $_SESSION["entrada"]["incluirformulario"];
        $_SESSION["jsonsalida"]["cumple1780"] = $_SESSION["entrada"]["cumple1780"];
        $_SESSION["jsonsalida"]["mantiene1780"] = $_SESSION["entrada"]["mantiene1780"];
        $_SESSION["jsonsalida"]["renuncia1780"] = $_SESSION["entrada"]["renuncia1780"];

        // *********************************************************************** //
        // Recupera lista de matrículas, activos y personal
        // *********************************************************************** //
        $prim = '';
        $cantfor = 0;
        foreach ($_SESSION["entrada"]["matriculas"] as $m) {
            $_SESSION["jsonsalida"]["matriculas"][] = $m;
            if ($prim == '') {
                $prim = $m["matricula"];
            }
        }

        // *********************************************************************** //
        // Abre la conexión con la BD
        // *********************************************************************** //        
        $mysqli = false;
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9904";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["fcortemesdia"] = substr($_SESSION["generales"]["fcorte"], 4, 4);

        // ************************************************************************** //
        // Valida el idusuario reportado
        // En caso de reportar USUPUBXX deben indicarse los datos del cliente logueado
        // ************************************************************************** //
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            $sedeusuario = '99';
            if (!isset($_SESSION["entrada"]["emailcontrol"]) || $_SESSION["entrada"]["emailcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el email del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["nombrecontrol"]) || $_SESSION["entrada"]["nombrecontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el nombre del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["celularcontrol"]) || $_SESSION["entrada"]["celularcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el celular del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["identificacioncontrol"]) || $_SESSION["entrada"]["identificacioncontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó la identificación del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
            if ($usux === false || empty($usux)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no localizado en la BD del sistema';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechainactivacion"]), "0") != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario reportado está inactivo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechaactivacion"]), "0") == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario reportado no está activo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $sedeusuario = $usux["idsede"];
            $_SESSION["entrada"]["emailcontrol"] = $usux["email"];
            $_SESSION["entrada"]["nombrecontrol"] = $usux["nombreusuario"];
            $_SESSION["entrada"]["celularcontrol"] = $usux["celular"];
            $_SESSION["entrada"]["identificacioncontrol"] = $usux["identificacion"];
        }
        $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
        $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];

        if (!isset($_SESSION["tramite"]["sistemacreacion"])) {
            $_SESSION["tramite"]["sistemacreacion"] = '';
        }

        // ****************************************************************************** //
        // Localiza el expediente
        // ****************************************************************************** //    
        $liqtideprop = '';
        $liqideprop = '';
        $liqmatprop = '';
        $liqcamprop = '';
        $liqactprop = 0;
        $liqcantesttotnal = 0;
        $cantesttot = 0;

        // Encuentra datos del propietario en la renovación
        $mats = array();
        foreach ($_SESSION["jsonsalida"]["matriculas"] as $matri) {
            if (!isset($mats[$matri["matricula"]])) {
                $mats[$matri["matricula"]] = array(
                    'matricula' => $matri["matricula"],
                    'activos' => $matri["activos"],
                    'anorenovacion' => $matri["anorenovacion"]
                );
            } else {
                $mats[$matri["matricula"]]["activos"] = $matri["activos"];
                $mats[$matri["matricula"]]["anorenovacion"] = $matri["anorenovacion"];
            }
        }
        foreach ($mats as $matri) {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matri["matricula"] . "'", "matricula,organizacion,categoria,idclase,numid");
            if ($exp && !empty($exp)) {
                if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                    $liqtideprop = $exp["idclase"];
                    $liqideprop = $exp["numid"];
                    $liqactprop = $matri["activos"];
                }
            }
        }


        // Encuentra cantidad de establecimientos a renovar
        $unamatest = '';
        $unaorgest = '';
        $unacatest = '';
        foreach ($mats as $matri) {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matri["matricula"] . "'", "matricula,organizacion,categoria");
            if ($exp && !empty($exp)) {
                if ($exp["organizacion"] == '02' || ($exp["organizacion"] > '02' && ($exp["categoria"] == '2' || $exp["categoria"] == '3'))) {
                    $unamatest = $exp["matricula"];
                    $unaorgest = $exp["organizacion"];
                    $unacatest = $exp["categoria"];
                    $cantesttot++;
                }
            }
        }

        // Si el propietario no renueva en el trámite, lo busca.
        if ($liqtideprop == '') {
            if ($unamatest != '') {
                if ($unaorgest == '02') {
                    $prop = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $unamatest . "'", "id");
                    if ($prop && !empty($prop)) {
                        foreach ($prop as $p) {
                            if ($p["estado"] == 'V') {
                                if ($p["codigocamara"] == CODIGO_EMPRESA) {
                                    $prop1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $p["matriculapropietario"] . "'");
                                    if ($prop1 && !empty($prop1)) {
                                        $liqtideprop = $prop1["idclase"];
                                        $liqideprop = $prop1["numid"];
                                        $liqactprop = $prop1["acttot"];
                                    }
                                } else {
                                    $liqtideprop = $p["tipoidentificacion"];
                                    $liqideprop = $prop1["identificacion"];
                                    $liqactprop = 0;
                                }
                            }
                        }
                    }
                } else {
                    if ($unacatest == '2' || $unacatest == '3') {
                        $est1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $unamatest . "'");
                        if ($est1 && !empty($est1)) {
                            $liqtideprop = '2';
                            $liqideprop = $est1["cpnumnit"];
                        }
                    }
                }
            }
        }

        // Encuentra el valor de los activos del propietario a nivel nacional y
        // La cantidad de establecimientos a nivel nacional
        if ($feusouvb <= date("Ymd")) {
            if ($cantesttot > 0) {
                if ($liqtideprop != '') {
                    $prop = \funcionesRues::consultarRegMerIdentificacionActivos($_SESSION["entrada"]["idusuario"], $liqtideprop, $liqideprop);
                    if ($prop) {
                        if ($liqactprop == 0) {
                            $liqactprop = $prop["activos_totales"];
                        }
                        $liqcantesttotnal = $prop["establecimientos_locales"] + $prop["establecimientos_foraneos"];
                    }
                }
            }
        } else {
            $liqactprop = 0;
            $liqcantesttotnal = 0;
        }
        $msg = "(Metodo liquidar renovacion multiples anios) Datos del propietario a nivel nacional \r\n";
        $msg .= "Identificación el propietario : " . $liqtideprop . "-" . $liqideprop . "\r\n";
        $msg .= "Cantidad de establecimientos a nivel nacional : " . $liqcantesttotnal . "\r\n";
        $msg .= "Establecimientos/años a renovar : " . $cantesttot . "\r\n";
        $msg .= "Activos del propietario : " . $liqactprop . "\r\n";
        \logApi::general2($nameLog, '', $msg);

        $matriculas = array();
        $matControl = array();
        foreach ($_SESSION["jsonsalida"]["matriculas"] as $m) {
            if (!isset($matControl[$m["matricula"]])) {
                $matControl[$m["matricula"]] = $m["matricula"];
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m["matricula"] . "'");
                if ($exp["ultanoren"] < date("Y")) {
                    for ($i = $exp["ultanoren"] + 1; $i <= date("Y"); $i++) {
                        $matriculas[] = array(
                            'matricula' => $m["matricula"],
                            'anorenovar' => $i,
                            'reliquidacion' => 'no'
                        );
                    }
                } else {
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        $matriculas[] = array(
                            'matricula' => $m["matricula"],
                            'anorenovar' => date("Y"),
                            'reliquidacion' => 'si',
                            'activosiniciales' => $exp["acttot"]
                        );
                    } else {
                        $matriculas[] = array(
                            'matricula' => $m["matricula"],
                            'anorenovar' => date("Y"),
                            'reliquidacion' => 'si',
                            'activosiniciales' => $exp["actvin"]
                        );
                    }
                }
            }
        }

        //
        $totalmatriculas = 0;
        $reliquidacion = 0;
        foreach ($matControl as $m) {

            $cantfor++;
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");

            // ****************************************************************************** //
            // En caso que el expediente no exista
            // ****************************************************************************** //
            if ($exp === false || empty($exp)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m . ' no encontrado en la base de datos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            // ****************************************************************************** //
            // Si matrícula no está activa
            // ****************************************************************************** //
            if (isset($_SESSION["entrada"]["sistemacreacion"]) && $_SESSION["entrada"]["sistemacreacion"] == 'SII3') {
                if ($exp["ctrestmatricula"] != 'MA' && $exp["ctrestmatricula"] != 'IA' && $exp["ctrestmatricula"] != 'MI' && $exp["ctrestmatricula"] != 'II') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m . ' no se encuentra activo';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            } else {
                if ($exp["ctrestmatricula"] != 'MA' && $exp["ctrestmatricula"] != 'IA') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m . ' no se encuentra activo';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            // ****************************************************************************** //
            // Se excluyen disueltas
            // ****************************************************************************** //
            /*
              if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
              if (strpos($exp["razonsocial"], 'EN LIQUIDACION') ||
              strpos($exp["razonsocial"], 'EN REORGANIZACION') ||
              strpos($exp["razonsocial"], 'EN REESTRUCTURACION')) {
              $mysqli->close();
              $_SESSION["jsonsalida"]["codigoerror"] = "9999";
              $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m . ' se encuentra disuelto';
              $api->response($api->json($_SESSION["jsonsalida"]), 200);
              }
              }
             */

            // ****************************************************************************** //
            // Se excluyen disueltas por terminos
            // ****************************************************************************** //
            /*
              if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
              if (ltrim(trim($exp["fecvigencia"]), "0") != '' && ltrim(trim($exp["fecvigencia"]), "0") != '99999999' && ltrim(trim($exp["fecvigencia"]), "0") != '9999999') {
              if ($exp["fecvigencia"] < date("Ymd")) {
              $mysqli->close();
              $_SESSION["jsonsalida"]["codigoerror"] = "9999";
              $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m . ' se encuentra disuelta por vencimiento de términos';
              $api->response($api->json($_SESSION["jsonsalida"]), 200);
              }
              }
              }
             */

            // ****************************************************************************** //
            // pnat fallecidos
            // ****************************************************************************** //
            if ($exp["organizacion"] == '01') {
                if (strpos($exp["razonsocial"], 'FALLECIDO') || strpos($exp["razonsocial"], 'FALLECIDA')) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente ' . $m . ' pertenece a una persona natural fallecida';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            // ****************************************************************************** //
            // Si ya está renovado al año actual
            // ****************************************************************************** //
            if ($exp["ultanoren"] == date("Y")) {
                $reliquidacion++;
            }
            $totalmatriculas++;

            // **************************************************************************************** //
            // Si es sucursal o agencia verifica que si la principal esta en la misma jurisdiccion o no
            // **************************************************************************************** //
            $propjurisdiccion = 'no';
            if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                if ($exp["cpcodcam"] == '' || $exp["cpcodcam"] == CODIGO_EMPRESA) {
                    $propjurisdiccion = 'si';
                }
            }
            if ($exp["organizacion"] == '02') {
                if ($prim == $exp["matricula"]) {
                    $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $exp["matricula"] . "'", "id");
                    foreach ($props as $px) {
                        if ($px["estado"] == 'V') {
                            if ($px["matriculapropietario"] != '') {
                                if ($px["codigocamara"] == '' || $px["codigocamara"] == CODIGO_EMPRESA) {
                                    $propjurisdiccion = 'si';
                                }
                            }
                        }
                    }
                } else {
                    $propjurisdiccion = 'si';
                }
            }

            // ************************************************************************** //
            // Encuentra el valor de la liquidación de renovación del comerciante
            // ************************************************************************** //


            $anosrenovados = 0;
            $ultanoren = '';
            $ultactivos = 0;
            foreach ($matriculas as $mx1) {
                if ($mx1["matricula"] == $m) {
                    foreach ($_SESSION["jsonsalida"]["matriculas"] as $mx) {
                        if ($mx["matricula"] == $mx1["matricula"] && $mx["anorenovacion"] == $mx1["anorenovar"]) {
                            $ultanoren = $mx["anorenovacion"];
                            $ultactivos = $mx["activos"];
                            $anosrenovados++;
                            $liq = array();
                            $liq["servicio"] = '';
                            $liq["cc"] = CODIGO_EMPRESA;
                            $liq["matricula"] = $exp["matricula"];
                            $liq["nmatricula"] = $exp["razonsocial"];
                            $liq["anorenovar"] = $mx["anorenovacion"];
                            $liq["cantidad"] = 1;
                            $liq["activos"] = $mx["activos"];
                            $liq["valor"] = 0;
                            if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                                if (substr($exp["matricula"], 0, 1) == 'S') {
                                    $liq["servicio"] = '01020208';
                                } else {
                                    $liq["servicio"] = '01020201';
                                }
                            }
                            if ($exp["organizacion"] == '02') {
                                if ($propjurisdiccion == 'si') {
                                    $liq["servicio"] = '01020202';
                                } else {
                                    $liq["servicio"] = '01020203';
                                }
                            }
                            if ($exp["categoria"] == '2') {
                                if ($propjurisdiccion == 'si') {
                                    $liq["servicio"] = '01020204';
                                } else {
                                    $liq["servicio"] = '01020205';
                                }
                            }
                            if ($exp["categoria"] == '3') {
                                if ($propjurisdiccion == 'si') {
                                    $liq["servicio"] = '01020206';
                                } else {
                                    $liq["servicio"] = '01020207';
                                }
                            }
                            $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal);
                            if ($mx1["reliquidacion"] == 'si') {
                                $val1 = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $mx1["activosiniciales"], 'tarifa', $liqactprop, $liqcantesttotnal);
                                $liq["valor"] = $liq["valor"] - $val1;
                            }

                            if ($exp["organizacion"] == '12' && $exp["categoria"] == '1') {
                                if ($exp ["ctrclaseespeesadl"] == '61' || $exp ["ctrclaseespeesadl"] == '62') {
                                    $liq["valor"] = 0;
                                }
                            }
                            if ($exp["organizacion"] == '14' && $exp["categoria"] == '1') {
                                if ($exp ["ctrclaseespeesadl"] == '49' || $exp ["ctrclaseespeesadl"] == '61') {
                                    $liq["valor"] = 0;
                                }
                            }

                            $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;

                            //
                            $lin = array();
                            $lin["cc"] = CODIGO_EMPRESA;
                            $lin["matricula"] = $exp["matricula"];
                            $lin["proponente"] = '';
                            $lin["numrue"] = $exp["nit"];
                            $lin["idtipoidentificacion"] = $exp["idclase"];
                            $lin["identificacion"] = $exp["numid"];
                            $lin["razonsocial"] = $exp["razonsocial"];
                            $lin["ape1"] = $exp["apellido1"];
                            $lin["ape2"] = $exp["apellido2"];
                            $lin["nom1"] = $exp["nombre1"];
                            $lin["nom2"] = $exp["nombre2"];
                            $lin["organizacion"] = $exp["organizacion"];
                            $lin["categoria"] = $exp["categoria"];
                            $lin["afiliado"] = $exp["ctrafiliacion"];
                            $lin["propietariojurisdiccion"] = '';
                            $lin["primeranorenovado"] = $exp["ultanoren"] + 1;
                            $lin["ultimoanoafiliado"] = '';
                            $lin["ultimoanorenovado"] = $mx["anorenovacion"];
                            if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                                $lin["ultimosactivos"] = $exp["acttot"];
                            } else {
                                $lin["ultimosactivos"] = $exp["actvin"];
                            }
                            $lin["nuevosactivos"] = $mx["activos"];
                            $lin["actividad"] = '';
                            $lin["registrobase"] = 'S';
                            $lin["benart7"] = $exp["ctrbenart7"];
                            $lin["benley1780"] = $exp["ctrbenley1780"];
                            $lin["renovaresteano"] = 'si';
                            $lin["fechanacimiento"] = '';
                            $lin["fechamatricula"] = $exp["fecmatricula"];
                            $lin["fecmatant"] = '';
                            $lin["reliquidacion"] = '';
                            $lin["controlpot"] = '';
                            $lin["dircom"] = $exp["dircom"];
                            $lin["muncom"] = $exp["muncom"];
                            $lin["valor"] = $liq["valor"];
                            $_SESSION["jsonsalida"]["expedientes"][] = $lin;
                        }
                    }

                    // ************************************************************************** //
                    // Si renueva más de un año no tendría beneficios
                    // ************************************************************************** //
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        if ($exp["ctrbenley1780"] == 'S') {
                            if ($anosrenovados > 1) {
                                $exp["ctrbenley1780"] = 'P';
                            } else {
                                if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                                    $exp["ctrbenley1780"] = 'P';
                                }
                            }
                        }
                    } else {
                        $exp["ctrbenley1780"] = '';
                    }

                    // ************************************************************************** //
                    // Suma el expediente a la lista de expedientes
                    // ************************************************************************** //
                    /*
                      $lin = array();
                      $lin["cc"] = CODIGO_EMPRESA;
                      $lin["matricula"] = $exp["matricula"];
                      $lin["proponente"] = '';
                      $lin["numrue"] = $exp["nit"];
                      $lin["idtipoidentificacion"] = $exp["idclase"];
                      $lin["identificacion"] = $exp["numid"];
                      $lin["razonsocial"] = $exp["razonsocial"];
                      $lin["ape1"] = $exp["apellido1"];
                      $lin["ape2"] = $exp["apellido2"];
                      $lin["nom1"] = $exp["nombre1"];
                      $lin["nom2"] = $exp["nombre2"];
                      $lin["organizacion"] = $exp["organizacion"];
                      $lin["categoria"] = $exp["categoria"];
                      $lin["afiliado"] = $exp["ctrafiliacion"];
                      $lin["propietariojurisdiccion"] = '';
                      $lin["primeranorenovado"] = $exp["ultanoren"] + 1;
                      $lin["ultimoanoafiliado"] = '';
                      $lin["ultimoanorenovado"] = $ultanoren;
                      if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                      $lin["ultimosactivos"] = $exp["acttot"];
                      } else {
                      $lin["ultimosactivos"] = $exp["actvin"];
                      }
                      $lin["nuevosactivos"] = $ultactivos;
                      $lin["actividad"] = '';
                      $lin["registrobase"] = 'S';
                      $lin["benart7"] = $exp["ctrbenart7"];
                      $lin["benley1780"] = $exp["ctrbenley1780"];
                      $lin["renovaresteano"] = 'si';
                      $lin["fechanacimiento"] = '';
                      $lin["fechamatricula"] = $exp["fecmatricula"];
                      $lin["fecmatant"] = '';
                      $lin["reliquidacion"] = '';
                      $lin["controlpot"] = '';
                      $lin["dircom"] = $exp["dircom"];
                      $lin["muncom"] = $exp["muncom"];
                      $lin["valor"] = $liq["valor"];
                      $_SESSION["jsonsalida"]["expedientes"][] = $lin;
                     */

                    // ******************************************************************************** //
                    // Verifica que efetcivamente se puedan liquidar beneficios y no haya renunciado
                    // ******************************************************************************** //
                    if ($exp["ctrbenley1780"] == 'S') {
                        if ($_SESSION["entrada"]["cumple1780"] == 'S' && $_SESSION["jsonsalida"]["mantiene1780"] == 'S' && $_SESSION["jsonsalida"]["renuncia1780"] == 'N') {
                            $exp["ctrbenley1780"] = 'S';
                        } else {
                            if ($_SESSION["entrada"]["cumple1780"] == 'S' && $_SESSION["jsonsalida"]["mantiene1780"] == 'S' && $_SESSION["jsonsalida"]["renuncia1780"] == 'S') {
                                $exp["ctrbenley1780"] = 'R';
                            } else {
                                $exp["ctrbenley1780"] = 'P';
                            }
                        }
                    }

                    // ************************************************************************** //
                    // Liquida beneficio de la Ley 1780
                    // ************************************************************************** //
                    if ($anosrenovados == 1) {
                        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                            if ($exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                                if (substr($exp["fecmatricula"], 0, 4) == date("Y") - 1) {
                                    if ($exp["ctrbenley1780"] == 'S') {
                                        $liq = array();
                                        $liq["servicio"] = '01090111';
                                        $liq["cc"] = CODIGO_EMPRESA;
                                        $liq["matricula"] = $exp["matricula"];
                                        $liq["nmatricula"] = $exp["razonsocial"];
                                        $liq["anorenovar"] = $ultanoren;
                                        $liq["cantidad"] = 1;
                                        $liq["activos"] = $ultactivos;
                                        $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01020201', $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal) * -1;
                                        $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                                        $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
                                    }
                                }
                            }
                        }
                    }

                    // ****************************************************************************** //
                    // Busca el valor de afiliacion
                    // ****************************************************************************** //}
                    if ($exp["ctrafiliacion"] == '1') {
                        if ($_SESSION["entrada"]["incluirafiliacion"] == 'S') {
                            $incafil = 'si';
                            if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                                if (defined('RENOVACION_BLOQUEAR_AFILIACION_ABRIL') && RENOVACION_BLOQUEAR_AFILIACION_ABRIL == 'S') {
                                    $incafil = 'no';
                                }
                            }
                            if ($incafil == 'si') {
                                $liq = array();
                                $liq["servicio"] = '06010002';
                                $liq["cc"] = CODIGO_EMPRESA;
                                $liq["matricula"] = $exp["matricula"];
                                $liq["nmatricula"] = $exp["razonsocial"];
                                $liq["anorenovar"] = date("Y");
                                $liq["cantidad"] = 1;
                                $liq["activos"] = $ultactivos;
                                $liq["valor"] = 0;
                                $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal);
                                $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
                            }
                        }
                    }
                }
            }
        }

        if ($reliquidacion > 0 && $reliquidacion != $totalmatriculas) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Se detectó que al menos una de las matrículas está al día en la renovación, no es posible continuar con el proceso por este medio';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($reliquidacion > 0) {
            $esreliquidacion = 'si';
        } else {
            $esreliquidacion = 'no';
        }

        // ****************************************************************************** //
        // Recupera el primer expediente
        // ****************************************************************************** //        
        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $prim . "'");

        // ****************************************************************************** //
        // Forzar el cobro de certificados para usuarios públicos.
        // ****************************************************************************** //        
        if ($_SESSION["entrada"]["incluircertificado"] == 'N') {
            if (RENOVACION_LIQUIDAR_CERTIFICADOS == 'S') {
                if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
                    $_SESSION["entrada"]["incluircertificado"] = 'S';
                }
            }
        }

        // ****************************************************************************** //
        // Busca el valor del certificado a incluir
        // ****************************************************************************** //                
        if ($_SESSION["entrada"]["incluircertificado"] == 'S') {
            $liq = array();
            if ($exp["organizacion"] == '01' || $exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                $liq["servicio"] = '01010101';
            } else {
                if (substr($exp["matricula"], 0, 1) == 'S') {
                    $liq["servicio"] = '01010301';
                } else {
                    $liq["servicio"] = '01010102';
                }
            }
            $liq["cc"] = CODIGO_EMPRESA;
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
            $liq["matricula"] = $exp["matricula"];
            $liq["nmatricula"] = $exp["razonsocial"];
            $liq["anorenovar"] = '';
            $liq["cantidad"] = 1;
            $liq["activos"] = 0;
            if ($exp["ctrafiliacion"] == '1') {
                if (!defined('RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS')) {
                    define('RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS', 'S');
                }
                if (RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS == 'S') {
                    $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal);
                } else {
                    $liq["valor"] = 0;
                }
            } else {
                $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal);
            }
            $liq["nservicio"] = $serv["nombre"];
            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
        }

        // ****************************************************************************** //
        // Busca el valor del formulario a incluir
        // ****************************************************************************** //
        if ($_SESSION["entrada"]["incluirformulario"] == 'S') {
            if ($cantfor == 0 || $cantfor == 1 || $cantfor == 2) {
                $cantfor = 1;
            } else {
                $cantfor = $cantfor - 1;
            }

            $liq = array();
            $liq["servicio"] = RENOVACION_SERV_FORMULARIOS;
            if (substr($exp["matricula"], 0, 1) == 'S') {
                if (defined('RENOVACION_SERV_FORMULARIOS_ESADL') && RENOVACION_SERV_FORMULARIOS_ESADL != '') {
                    $liq["servicio"] = RENOVACION_SERV_FORMULARIOS_ESADL;
                }
            }
            $liq["cc"] = CODIGO_EMPRESA;
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
            $liq["matricula"] = $exp["matricula"];
            $liq["nmatricula"] = $exp["razonsocial"];
            $liq["anorenovar"] = '';
            $liq["cantidad"] = $cantfor;
            $liq["activos"] = 0;
            if ($exp["ctrafiliacion"] == '1') {
                if (!defined('RENOVACION_COBRAR_FORMULARIOS_AFILIADOS')) {
                    define('RENOVACION_COBRAR_FORMULARIOS_AFILIADOS', 'S');
                }
                if (RENOVACION_COBRAR_FORMULARIOS_AFILIADOS == 'S') {
                    $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal) * $cantfor;
                } else {
                    $liq["valor"] = 0;
                }
            } else {
                $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal) * $cantfor;
            }
            $liq["nservicio"] = $serv["nombre"];
            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
        }

        if ($esreliquidacion == 'si') {
            if (RENOVACION_RELIQUIDACION_COMO_MUTACION == 'S') {
                $liq = array();
                $liq["servicio"] = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_15;
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $prim . "'");
                if ($exp["ciiu1"] == 'R9200' || $exp["ciiu2"] == 'R9200' || $exp["ciiu3"] == 'R9200' || $exp["ciiu4"] == 'R9200') {
                    $liq["servicio"] = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_22;
                }
                if ($exp["organizacion"] == '12' && $exp["categoria"] == '1') {
                    $liq["servicio"] = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_51;
                }
                if ($exp["organizacion"] == '14' && $exp["categoria"] == '1') {
                    $liq["servicio"] = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_53;
                }
                $liq["cc"] = CODIGO_EMPRESA;
                $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
                $liq["matricula"] = $prim;
                $liq["nmatricula"] = $exp["razonsocial"];
                $liq["anorenovar"] = '';
                $liq["cantidad"] = 1;
                $liq["activos"] = 0;
                $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $liqactprop, $liqcantesttotnal) * $cantfor;
                $liq["nservicio"] = $serv["nombre"];
                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
            }
        }

        $alertaid = 0;
        $alertaservicio = '';
        $alertavalor = 0;

        // ***************************************************************************** //
        // Crea la liquidacion en mreg_liquidacion
        // ***************************************************************************** //
        // Datos básicos de la liquidación
        $nuevaliq = 'no';
        if (!isset($_SESSION["entrada"]["idliquidacion"])) {
            $nuevaliq = 'si';
        } else {
            if ($_SESSION["entrada"]["idliquidacion"] == "0" || $_SESSION["entrada"]["idliquidacion"] == "") {
                $nuevaliq = 'si';
            } else {
                $aliq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
                if ($aliq === false || empty($aliq)) {
                    $nuevaliq = 'si';
                } else {
                    if ($aliq["idestado"] != '01') {
                        $nuevaliq = 'si';
                    }
                }
            }
        }

        if ($nuevaliq == 'si') {
            $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');
            if ($_SESSION["tramite"] === false) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando liquidacion (VC): ' . $_SESSION["generales"]["mensajeerror"];
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["tramite"]["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
            $_SESSION["tramite"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
            $_SESSION["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
            $_SESSION["tramite"]["fecha"] = date("Ymd");
            $_SESSION["tramite"]["hora"] = date("H:i:s");
            $_SESSION["tramite"]["idestado"] = '01';
            $_SESSION["tramite"]["iptramite"] = '';
            $_SESSION["tramite"]["tipotramite"] = 'renovacionmatricula';
            if (substr($exp["matricula"], 0, 1) == 'S') {
                $_SESSION["tramite"]["tipotramite"] = 'renovacionesadl';
            }
        } else {
            $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        }
        // $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $prim . "'");
        $_SESSION["tramite"]["sede"] = $sedeusuario;
        $_SESSION["tramite"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["tramite"]["matriculabase"] = $exp["matricula"];
        $_SESSION["tramite"]["idmatriculabase"] = $exp["matricula"];
        $_SESSION["tramite"]["idexpedientebase"] = $exp["matricula"];
        $_SESSION["tramite"]["nombrebase"] = $exp["razonsocial"];
        $_SESSION["tramite"]["nom1base"] = $exp["nombre1"];
        $_SESSION["tramite"]["nom2base"] = $exp["nombre2"];
        $_SESSION["tramite"]["ape1base"] = $exp["apellido1"];
        $_SESSION["tramite"]["ape2base"] = $exp["apellido2"];
        $_SESSION["tramite"]["tipoidentificacionbase"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacionbase"] = $exp["numid"];
        $_SESSION["tramite"]["organizacionbase"] = $exp["organizacion"];
        $_SESSION["tramite"]["categoriabase"] = $exp["categoria"];
        $_SESSION["tramite"]["incluirformularios"] = '';
        $_SESSION["tramite"]["incluircertificados"] = '';

        if (isset($_SESSION["entrada"]["incluirformulario"])) {
            $_SESSION["tramite"]["incluirformularios"] = $_SESSION["entrada"]["incluirformulario"];
        }
        if (isset($_SESSION["entrada"]["incluircertificado"])) {
            $_SESSION["tramite"]["incluircertificados"] = $_SESSION["entrada"]["incluircertificado"];
        }

        //
        if (isset($_SESSION["entrada"]["sistemacreacion"]) && $_SESSION["entrada"]["sistemacreacion"] != '') {
            $_SESSION["tramite"]["sistemacreacion"] = $_SESSION["entrada"]["sistemacreacion"];
        } else {
            $_SESSION["tramite"]["sistemacreacion"] = 'API - liquidarRenovacionMultiplesAnios - ' . $_SESSION["entrada"]["usuariows"];
        }

        // Datos del cliente
        $_SESSION["tramite"]["tipocliente"] = '';
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacioncliente"] = $exp["numid"];
        $_SESSION["tramite"]["razonsocialcliente"] = $exp["razonsocial"];
        $_SESSION["tramite"]["nombrecliente"] = $exp["razonsocial"];
        $_SESSION["tramite"]["apellidocliente"] = trim($exp["apellido1"] . ' ' . $exp["apellido2"]);
        $_SESSION["tramite"]["apellido1cliente"] = $exp["apellido1"];
        $_SESSION["tramite"]["apellido2cliente"] = $exp["apellido2"];
        $_SESSION["tramite"]["nombre1cliente"] = $exp["nombre1"];
        $_SESSION["tramite"]["nombre2cliente"] = $exp["nombre2"];
        $_SESSION["tramite"]["email"] = $exp["emailcom"];
        $_SESSION["tramite"]["direccion"] = $exp["dircom"];
        $_SESSION["tramite"]["telefono"] = $exp["telcom1"];
        $_SESSION["tramite"]["movil"] = $exp["telcom2"];
        $_SESSION["tramite"]["idmunicipio"] = $exp["muncom"];

        // Datos del pagador
        $_SESSION["tramite"]["tipopagador"] = '';
        $_SESSION["tramite"]["tipoidentificacionpagador"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacionpagador"] = $exp["numid"];
        $_SESSION["tramite"]["razonsocialpagador"] = $exp["razonsocial"];
        $_SESSION["tramite"]["apellido1pagador"] = $exp["apellido1"];
        $_SESSION["tramite"]["apellido2pagador"] = $exp["apellido2"];
        $_SESSION["tramite"]["nombre1pagador"] = $exp["nombre1"];
        $_SESSION["tramite"]["nombre2pagador"] = $exp["nombre2"];
        $_SESSION["tramite"]["emailpagador"] = $exp["emailcom"];
        $_SESSION["tramite"]["direccionpagador"] = $exp["dircom"];
        $_SESSION["tramite"]["telefonopagador"] = $exp["telcom1"];
        $_SESSION["tramite"]["movilpagador"] = $exp["telcom2"];
        $_SESSION["tramite"]["municipiopagador"] = $exp["muncom"];
        $_SESSION["tramite"]["valorbruto"] = 0;
        $_SESSION["tramite"]["valorbaseiva"] = 0;
        $_SESSION["tramite"]["valoriva"] = 0;
        $_SESSION["tramite"]["valortotal"] = 0;
        $_SESSION["tramite"]["reliquidacion"] = $esreliquidacion;
        $_SESSION["tramite"]["liquidacion"] = array();
        $_SESSION["tramite"]["alertaid"] = $alertaid;
        $_SESSION["tramite"]["alertaservicio"] = $alertaservicio;
        $_SESSION["tramite"]["alertavalor"] = $alertavalor;

        $_SESSION["tramite"]["cumplorequisitosbenley1780"] = $_SESSION["entrada"]["cumple1780"];
        $_SESSION["tramite"]["mantengorequisitosbenley1780"] = $_SESSION["entrada"]["mantiene1780"];
        $_SESSION["tramite"]["renunciobeneficiosley1780"] = $_SESSION["entrada"]["renuncia1780"];

        // Arreglo de liquidacion
        $i = 0;
        foreach ($_SESSION["jsonsalida"]["liquidacion"] as $lliq) {
            if (isset($lliq["servicio"]) && $lliq["servicio"] != '') {
                $i++;
                $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = '000';
                $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $lliq["servicio"];
                $_SESSION["tramite"]["liquidacion"][$i]["cc"] = $lliq["cc"];
                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $lliq["matricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $lliq["nmatricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["ano"] = $lliq["anorenovar"];
                $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = 1;
                $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $lliq["activos"];
                $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $lliq["valor"];
                $_SESSION["tramite"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"] + $lliq["valor"];
                $_SESSION["tramite"]["valortotal"] = $_SESSION["tramite"]["valortotal"] + $lliq["valor"];
            }
        }

        // Arreglo de expedientes
        $i = 0;
        foreach ($_SESSION["jsonsalida"]["expedientes"] as $lexp) {
            $i++;
            $_SESSION["tramite"]["expedientes"][$i] = $lexp;
        }
        unset($_SESSION["jsonsalida"]["expedientes"]);

        //
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["jsonsalida"]["emailcontrol"];
        $_SESSION["tramite"]["tramitepresencial"] = '1'; // Inicio virtual
        //
        $res = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando liquidacion : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
        $_SESSION["jsonsalida"]["reliquidacion"] = $esreliquidacion;
        $_SESSION["jsonsalida"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"];
        $_SESSION["jsonsalida"]["valorbaseiva"] = $_SESSION["tramite"]["valorbaseiva"];
        $_SESSION["jsonsalida"]["valoriva"] = $_SESSION["tramite"]["valoriva"];
        $_SESSION["jsonsalida"]["valortotal"] = $_SESSION["tramite"]["valortotal"];
        $_SESSION["jsonsalida"]["alertaid"] = $alertaid;
        $_SESSION["jsonsalida"]["alertaservicio"] = $alertaservicio;
        $_SESSION["jsonsalida"]["alertavalor"] = $alertavalor;

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        \logApi::general2($nameLog, '', 'Request: ' . json_encode($_SESSION["entrada"]));
        \logApi::general2($nameLog, '', 'Response: ' . json_encode($_SESSION["jsonsalida"]));
        \logApi::general2($nameLog, '', '');

        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function liquidarRenovacionMultiplesAnios(API $api) {
        $dateinicioliq = date("Ymd") . ' ' . date("His");
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $resError = set_error_handler('myErrorHandler');
        $nameLog = 'api_LiquidarRenovacionMultiplesAnios_' . date("Ymd");

        $feinicio045 = '';
        $decreto045 = 'no';
        $decreto045blqlocalnoren = 'no';
        $decreto045blqforaneonoren = 'no';
        $decreto045solactivosforaneos = 'no';
        $decreto045solactivosnomat = 'no';
        $matblq = array();

        if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
            $feinicio045 = '99999999';
            $feinicio045general = '99999999';
        } else {
            $feinicio045 = FECHA_INICIO_DECRETO_045;
            $feinicio045general = FECHA_INICIO_DECRETO_045;
        }
        if (date("Ymd") >= $feinicio045) {
            $decreto045 = 'si';
        }
        if ($decreto045 == 'si') {
            if (defined('BLOQUEAR_PROPIETARIOS_NO_RENOVADOS_LOCALES') && BLOQUEAR_PROPIETARIOS_NO_RENOVADOS_LOCALES == 'SI') {
                $decreto045blqlocalnoren = 'si';
            }
            if (defined('BLOQUEAR_PROPIETARIOS_NO_RENOVADOS_FORANEOS') && BLOQUEAR_PROPIETARIOS_NO_RENOVADOS_FORANEOS == 'SI') {
                $decreto045blqforaneonoren = 'si';
            }
            if (defined('PEDIR_ACTIVOS_PROPIETARIOS_NO_MATRICULADOS') && PEDIR_ACTIVOS_PROPIETARIOS_NO_MATRICULADOS == 'SI') {
                $decreto045solactivosnomat = 'si';
            }
            if (defined('PEDIR_ACTIVOS_PROPIETARIOS_FORANAEOS') && PEDIR_ACTIVOS_PROPIETARIOS_FORANAEOS == 'SI') {
                $decreto045solactivosforaneos = 'si';
            }
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idusuario"] = '';
        $_SESSION["jsonsalida"]["emailcontrol"] = '';
        $_SESSION["jsonsalida"]["celularcontrol"] = '';
        $_SESSION["jsonsalida"]["nombrecontrol"] = '';
        $_SESSION["jsonsalida"]["identificacioncontrol"] = '';
        $_SESSION["jsonsalida"]["matriculas"] = array();
        $_SESSION["jsonsalida"]["incluirafiliacion"] = '';
        $_SESSION["jsonsalida"]["incluircertificado"] = '';
        $_SESSION["jsonsalida"]["incluirformulario"] = '';
        $_SESSION["jsonsalida"]["cumple1780"] = '';
        $_SESSION["jsonsalida"]["mantiene1780"] = '';
        $_SESSION["jsonsalida"]["renuncia1780"] = '';
        $_SESSION["jsonsalida"]["idliquidacion"] = '';
        $_SESSION["jsonsalida"]["numerorecuperacion"] = '';
        $_SESSION["jsonsalida"]["pedirbalance"] = 'no';
        $_SESSION["jsonsalida"]["personal"] = '';
        $_SESSION["jsonsalida"]["liquidacion"] = array();
        $_SESSION["jsonsalida"]["expedientes"] = array();
        $_SESSION["jsonsalida"]["desbloquear045"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("incluirafiliacion", false);
        $api->validarParametro("incluircertificado", false);
        $api->validarParametro("incluirformulario", false);
        $api->validarParametro("cumple1780", false);
        $api->validarParametro("mantiene1780", false);
        $api->validarParametro("renuncia1780", false);
        $api->validarParametro("ambiente", false);
        $api->validarParametro("permitirsaltoanos", false);

        //
        if (!isset($_SESSION["entrada"]["incluirafiliacion"])) {
            $_SESSION["entrada"]["incluirafiliacion"] = '';
        }
        if (!isset($_SESSION["entrada"]["incluircertificado"])) {
            $_SESSION["entrada"]["incluircertificado"] = '';
        }
        if (!isset($_SESSION["entrada"]["incluirformulario"])) {
            $_SESSION["entrada"]["incluirformulario"] = '';
        }
        if (!isset($_SESSION["entrada"]["cumple1780"])) {
            $_SESSION["entrada"]["cumple1780"] = '';
        }
        if (!isset($_SESSION["entrada"]["mantiene1780"])) {
            $_SESSION["entrada"]["mantiene1780"] = '';
        }
        if (!isset($_SESSION["entrada"]["renuncia1780"])) {
            $_SESSION["entrada"]["renuncia1780"] = '';
        }
        if (!isset($_SESSION["entrada"]["permitirsaltoanos"])) {
            $_SESSION["entrada"]["permitirsaltoanos"] = '';
        }
        if (!isset($_SESSION["entrada"]["personal"])) {
            $_SESSION["entrada"]["personal"] = 0;
        }


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('liquidarRenovacionMultiplesAnios', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        \logApi::general2($nameLog, '', 'Request: ' . json_encode($_SESSION["entrada"]));

        // *********************************************************************** //
        // Mueve parámetros al arreglo de salida
        // *********************************************************************** // 
        $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["jsonsalida"]["incluirafiliacion"] = $_SESSION["entrada"]["incluirafiliacion"];
        $_SESSION["jsonsalida"]["incluircertificado"] = $_SESSION["entrada"]["incluircertificado"];
        $_SESSION["jsonsalida"]["incluirformulario"] = $_SESSION["entrada"]["incluirformulario"];
        $_SESSION["jsonsalida"]["personal"] = $_SESSION["entrada"]["personal"];

        // *********************************************************************** //
        // Abre la conexión con la BD
        // *********************************************************************** //                
        $mysqli = false;
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'Q') {
            $mysqli = conexionMysqliApi('Q-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9904";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'Conecto a la BD');

        // ************************************************************************** //
        // Valida el idusuario reportado
        // En caso de reportar USUPUBXX deben indicarse los datos del cliente logueado
        // ************************************************************************** //
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            $sedeusuario = '99';
            $menerror = '';
            if (!isset($_SESSION["entrada"]["emailcontrol"]) || $_SESSION["entrada"]["emailcontrol"] == '') {
                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.control.sin.emailcontrol');
                // if ($menerror == '') {
                $menerror = 'No se reportó email  del usuario que hace la liquidación';
                // }
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el email del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["nombrecontrol"]) || $_SESSION["entrada"]["nombrecontrol"] == '') {
                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.control.sin.nombrecontrol');
                // if ($menerror == '') {
                $menerror = 'No se reportó el nombre del usuario que hace la liquidación';
                // }
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["celularcontrol"]) || $_SESSION["entrada"]["celularcontrol"] == '') {
                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.control.sin.celularcontrol');
                // if ($menerror == '') {
                $menerror = 'No se reportó el celular del usuario que hace la liquidación';
                // }
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["identificacioncontrol"]) || $_SESSION["entrada"]["identificacioncontrol"] == '') {
                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.control.sin.numidcontrol');
                // if ($menerror == '') {
                $menerror = 'No se reportó la identificación del usuario que hace la liquidación';
                // }
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
            if ($usux === false || empty($usux)) {
                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.control.user.inexistente');
                $menerror = 'Usuario que solicita la liquidación no fue encomntrado en la BD';
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechainactivacion"]), "0") != '') {
                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.control.user.inactivo');
                $menerror = 'Usuario que solicita la liquidación se encuentra inactivo en la BD';
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechaactivacion"]), "0") == '') {
                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.control.user.noactivado');
                $menerror = 'Usuario que solicita la liquidación no se encuentra activado en la BD';
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $sedeusuario = $usux["idsede"];
            $_SESSION["entrada"]["emailcontrol"] = $usux["email"];
            $_SESSION["entrada"]["nombrecontrol"] = $usux["nombreusuario"];
            $_SESSION["entrada"]["celularcontrol"] = $usux["celular"];
            $_SESSION["entrada"]["identificacioncontrol"] = $usux["identificacion"];
        }
        $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
        $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];

        if (!isset($_SESSION["tramite"]["sistemacreacion"])) {
            $_SESSION["tramite"]["sistemacreacion"] = '';
        }
        \logApi::general2($nameLog, '', 'Valido datos de usuario');

        // *********************************************************************** //
        // Recupera lista de matrículas, activos y personal
        // Identifica matrícula del propietario, ultimo año a renovar y activos
        // *********************************************************************** //
        //
        $matriculabase = '';
        $tipoidentificacionbase = '';
        $identificacionbase = '';
        $nombrebase = '';
        $nombre1base = '';
        $nombre2base = '';
        $apellido1base = '';
        $apellido2base = '';
        $organizacionbase = '';
        $categoriabase = '';

        $tipoidentificacioncliente = '';
        $identificacioncliente = '';
        $razonsocialcliente = '';
        $nombre1cliente = '';
        $nombre2cliente = '';
        $apellido1cliente = '';
        $apellido2cliente = '';
        $emailcliente = '';
        $dircomcliente = '';
        $telcom1cliente = '';
        $telcom2cliente = '';
        $muncomcliente = '';

        $prim = '';
        $mprop = '';
        $cesactprop = '';
        $mest = '';
        $cest = '';
        $actprop = '';
        $aactprop = array();
        $actproprues = '';
        $uarenprop = '';
        $uarenproprues = '';
        $matsvacias = 0;
        $tideprop = '';
        $ideprop = '';
        $proplocal = '';
        $cantfor = 0;

        //
        $matriculasIncluidas = array();
        $mats = array();
        $mats1 = array();
        $matest1 = array();
        $matprop1 = array();
        $anosquedeberenovar = array();

        $txterroresiniciales = '';
        $txtceros = '';
        $txtactivosmenores = '';
        $txtaceptarvalor = '';
        $txtactivosminimos = '';
        $txtcesacionactividad = '';
        $cafeini = '';
        $cafefin = '';
        $menerror = '';
        $mensajepropietarionorenovado = '';
        $propietariodisueltoultimoano = '';

        \logApi::general2($nameLog, '', 'Inicializo variables');

        // ******************************************************************************************* //
        // Recorre las matículas y arma arreglo de las matrículas a renovar
        // Evalua si hay matrículas vacías, es decir, registros sin número de matrícula
        // ******************************************************************************************* //
        if (isset($_SESSION["entrada"]["matriculas"]) && is_array($_SESSION["entrada"]["matriculas"]) && !empty($_SESSION["entrada"]["matriculas"])) {
            foreach ($_SESSION["entrada"]["matriculas"] as $m) {
                if ($prim == '') {
                    $prim = $m["matricula"];
                }
                if (trim((string) $m["matricula"]) == '') {
                    $matsvacias++;
                } else {
                    $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m["matricula"] . "'", "matricula,organizacion,categoria,idclase,numid,nit");
                    if ($exp1["organizacion"] == '02' || $exp1["categoria"] == '2' || $exp1["categoria"] == '3') {
                        if (!isset($matriculasIncluidas[$m["matricula"]])) {
                            $matriculasIncluidas[$m["matricula"]] = array();
                            $matriculasIncluidas[$m["matricula"]]["cantidadpropietarios"] = 1;
                            if ($exp1["organizacion"] == '02') {
                                $matriculasIncluidas[$m["matricula"]]["cantidadpropietarios"] = 0;
                                $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $m["matricula"] . "'", "id");
                                if ($props && !empty($props)) {
                                    foreach ($props as $px) {
                                        if ($px["estado"] == 'V') {
                                            $matriculasIncluidas[$m["matricula"]]["cantidadpropietarios"]++;
                                        }
                                    }
                                }
                            }
                        }
                        $matriculasIncluidas[$m["matricula"]]["matricula"] = $m["matricula"];
                        $matriculasIncluidas[$m["matricula"]]["incluida"] = 'si';
                        $matriculasIncluidas[$m["matricula"]]["organizacion"] = $exp1["organizacion"];
                        $matriculasIncluidas[$m["matricula"]]["categoria"] = $exp1["categoria"];
                        $matriculasIncluidas[$m["matricula"]]["anoren"] = $m["anorenovacion"];
                        $matriculasIncluidas[$m["matricula"]]["desbloquear045"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . $m["matricula"] . "' and campo='desbloquear045'", "contenido");
                        if (!isset($matblq[$m["anorenovacion"]])) {
                            $matblq[$m["anorenovacion"]] = array();
                            $matblq[$m["anorenovacion"]]["prop"] = 0;
                            $matblq[$m["anorenovacion"]]["est"] = 0;
                        }
                        $matblq[$m["anorenovacion"]]["est"]++;
                    } else {
                        $mprop = $m["matricula"];
                        $actprop = $m["activos"];
                        $aactprop[$m["anorenovacion"]] = $m["activos"];
                        $uarenprop = $m["anorenovacion"];
                        $tideprop = $exp1["idclase"];
                        $ideprop = $exp1["numid"];
                        $proplocal = 'si-incluido';
                        if (!isset($matblq[$m["anorenovacion"]])) {
                            $matblq[$m["anorenovacion"]] = array();
                            $matblq[$m["anorenovacion"]]["prop"] = 0;
                            $matblq[$m["anorenovacion"]]["est"] = 0;
                        }
                        $matblq[$m["anorenovacion"]]["prop"]++;
                    }
                    if ($matriculabase == '') {
                        $matriculabase = $m["matricula"];
                    }
                    if (!isset($m["personal"])) {
                        $m["personal"] = $_SESSION["entrada"]["personal"];
                    }
                    if ($exp1["organizacion"] == '02' || $exp1["categoria"] == '2' || $exp1["categoria"] == '3') {
                        if ($matriculasIncluidas[$m["matricula"]]["cantidadpropietarios"] == 1) {
                            if ($mprop != '') {
                                $feinicio045a = '';
                                $decreto045a = 'no';
                                if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                                    $feinicio045a = '99999999';
                                } else {
                                    $feinicio045a = FECHA_INICIO_DECRETO_045;
                                }
                                if ($m["anorenovacion"] . '0101' >= $feinicio045a) {
                                    $decreto045a = 'si';
                                }
                                if ($decreto045a == 'si') {
                                    if (isset($aactprop[$m["anorenovacion"]])) {
                                        if ($aactprop[$m["anorenovacion"]] != $m["activos"]) {
                                            if ($txterroresiniciales != '') {
                                                $txterroresiniciales .= '<br>';
                                            }
                                            $txterroresiniciales .= '** ' . retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m["matricula"] . "'", "razonsocial") . ' (' . $m["matricula"] . ') año ' . $m["anorenovacion"] . ' los activos deben ser iguales a los activos del propietario. ($' . number_format($aactprop[$m["anorenovacion"]]) . ')';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $_SESSION["jsonsalida"]["matriculas"][] = $m;
                }
            }
        }
        \logApi::general2($nameLog, '', 'Evalua integridad de la lista de matrículas');

        // Valida si hay registros con matrícula vacía
        if ($matsvacias > 0) {
            $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.exp.vacio');
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($txterroresiniciales != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $txterroresiniciales;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ******************************************************************************************* //
        // Localiza la totalidad de los establecimientos locales del propietario
        // Incluye sucursales y agencias
        // ******************************************************************************************* //
        if ($mprop != '') {

            $estabs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario='" . $mprop . "'", "matricula");
            foreach ($estabs as $ests) {
                if ($ests["estado"] == 'V') {
                    $expest = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $ests["matricula"] . "'");
                    if ($expest && !empty($expest)) {
                        if ($expest["ctrestmatricula"] == 'MA' || $expest["ctrestmatricula"] == 'MI' || $expest["ctrestmatricula"] == 'MR') {
                            if (!isset($matriculasIncluidas[$expest["matricula"]])) {
                                $matriculasIncluidas[$expest["matricula"]] = array();
                                $matriculasIncluidas[$expest["matricula"]]["matricula"] = $expest["matricula"];
                                $matriculasIncluidas[$expest["matricula"]]["incluida"] = 'no';
                                $matriculasIncluidas[$expest["matricula"]]["anoren"] = $expest["ultanoren"];
                                $matriculasIncluidas[$expest["matricula"]]["organizacion"] = $expest["organizacion"];
                                $matriculasIncluidas[$expest["matricula"]]["categoria"] = $expest["categoria"];
                            }
                        }
                    }
                }
            }

            //
            $sucages = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "cpcodcam='" . $_SESSION["generales"]["codigoempresa"] . "' and cpnummat='" . $mprop . "'", "matricula");
            if ($sucages && !empty($sucages)) {
                foreach ($sucages as $sucage) {
                    if ($sucage["ctrestmatricula"] == 'MA' || $sucage["ctrestmatricula"] == 'MI' || $sucage["ctrestmatricula"] == 'MR') {
                        if (!isset($matriculasIncluidas[$sucage["matricula"]])) {
                            $matriculasIncluidas[$sucage["matricula"]] = array();
                            $matriculasIncluidas[$sucage["matricula"]]["matricula"] = $sucage["matricula"];
                            $matriculasIncluidas[$sucage["matricula"]]["incluida"] = 'no';
                            $matriculasIncluidas[$sucage["matricula"]]["organizacion"] = $sucage["organizacion"];
                            $matriculasIncluidas[$sucage["matricula"]]["categoria"] = $sucage["categoria"];
                            $matriculasIncluidas[$sucage["matricula"]]["anoren"] = $sucage["ultanoren"];
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, '', 'Localiza la totalidad de los establecimientos');

        //
        $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["fcortemesdia"] = substr($_SESSION["generales"]["fcorte"], 4, 4);

        // ******************************************************************* //
        // Valida si se renovaran todos los años o hasta donde renovara
        // ******************************************************************* //        
        foreach ($_SESSION["jsonsalida"]["matriculas"] as $matri) {

            $feinicio045a = '';
            $decreto045a = 'no';
            if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                $feinicio045a = '99999999';
            } else {
                $feinicio045a = FECHA_INICIO_DECRETO_045;
            }
            if ($matri["anorenovacion"] . '0101' >= $feinicio045a) {
                $decreto045a = 'si';
            }

            $simensaje = false;
            $cafeini = '';
            $cafefin = '';
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matri["matricula"] . "'", "matricula,organizacion,categoria,idclase,numid,acttot,actvin,ultanoren");
            $desbloquear045 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . $matri["matricula"] . "' and campo='desbloquear045'", "contenido");
            $_SESSION["jsonsalida"]["desbloquear045"] = $desbloquear045;
            \logApi::general2($nameLog, '', 'Localiza expediente ' . $matri["matricula"] . ' para validar si debe o no renovar');
            if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                $ind = $matri["anorenovacion"] . '-' . $matri["matricula"];
                $matprop1[$ind] = $matri["activos"];
                $indx = $exp["matricula"] . '-' . $matri["anorenovacion"];
                $anosquedeberenovar[$indx] = 'si';
                $ca = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $matri["matricula"] . "' and acto IN ('0580','0581')", "fecharegistro,horaregistro");
                if ($ca && !empty($ca)) {
                    foreach ($ca as $ca1) {
                        if (substr($ca1["fecharegistro"], 0, 4) <= $matri["anorenovacion"]) {
                            if ($ca1["acto"] == '0580') {
                                $cafeini = $ca1["fecharegistro"];
                            }
                            if ($ca1["acto"] == '0581') {
                                if (substr($ca1["fecharegistro"], 0, 4) < $matri["anorenovacion"]) {
                                    $cafeini = '';
                                    $cafefin = '';
                                } else {
                                    $cafefin = $ca1["fecharegistro"];
                                }
                            }
                        }
                    }
                    if ($desbloquear045 != 'S') {
                        if ($cafeini != '') {
                            if ($cafefin == '') {
                                if ($matri["anorenovacion"] > substr($cafeini, 0, 4)) {
                                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.ces.act', $matri["anorenovacion"], $matri["matricula"]);
                                    // if ($menerror === '') {
                                    $menerror = 'El expediente ' . $matri["matricula"] . ' tiene cesada su actividad, de acuerdo con la Ley vigente, no debe renovar el año ' . $matri["anorenovacion"];
                                    // }
                                    $txtcesacionactividad .= $menerror . '<br>';
                                    $simensaje = true;
                                }
                                if ($matri["anorenovacion"] == substr($cafeini, 0, 4)) {
                                    $fecorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . substr($cafeini, 0, 4) . "'");
                                    if ($cafeini <= $fecorte["corte"]) {
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.ces.act', $matri["anorenovacion"], $matri["matricula"]);
                                        // if ($menerror === '') {
                                        $menerror = 'El expediente ' . $matri["matricula"] . ' tiene cesada su actividad, de acuerdo con la Ley vigente, no debe renovar el año ' . $matri["anorenovacion"];
                                        // }
                                        $txtcesacionactividad .= $menerror . '<br>';
                                        $simensaje = true;
                                    }
                                }
                            } else {
                                if ($matri["anorenovacion"] > substr($cafeini, 0, 4)) {
                                    if ($matri["anorenovacion"] < substr($cafefin, 0, 4)) {
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.ces.act', $matri["anorenovacion"], $matri["matricula"]);
                                        // if ($menerror === '') {
                                        $menerror = 'El expediente ' . $matri["matricula"] . ' tiene cesada su actividad, de acuerdo con la Ley vigente, no debe renovar el año ' . $matri["anorenovacion"];
                                        // }
                                        $txtcesacionactividad .= $menerror . '<br>';
                                        $simensaje = true;
                                    }
                                    if ($matri["anorenovacion"] >= substr($cafefin, 0, 4)) {
                                        $cafeini = '';
                                        $cafefin = '';
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.ces.act', $matri["anorenovacion"], $matri["matricula"]);
                                        // $txtcesacionactividad .= $menerror . '<br>';
                                        // $simensaje = true;
                                    }
                                }
                                if ($matri["anorenovacion"] == substr($cafeini, 0, 4)) {
                                    $fecorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . substr($cafeini, 0, 4) . "'");
                                    if ($cafeini <= $fecorte["corte"]) {
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.ces.act', $matri["anorenovacion"], $matri["matricula"]);
                                        // if ($menerror === '') {
                                        $menerror = 'El expediente ' . $matri["matricula"] . ' tiene cesada su actividad, de acuerdo con la Ley vigente, no debe renovar el año ' . $matri["anorenovacion"];
                                        // }
                                        $txtcesacionactividad .= $menerror . '<br>';
                                        $simensaje = true;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $mest = $matri["matricula"];
                $corg = $exp["organizacion"];
                $cest = $exp["categoria"];
                $ind = $matri["anorenovacion"] . '-' . $matri["matricula"];
                $matest1[$ind] = $matri["activos"];
                $ca = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $matri["matricula"] . "' and acto IN ('0580','0581')", "fecharegistro,horaregistro");
                if ($ca && !empty($ca)) {
                    foreach ($ca as $ca1) {
                        if (substr($ca1["fecharegistro"], 0, 4) <= $matri["anorenovacion"]) {
                            if ($ca1["acto"] == '0580') {
                                $cafeini = $ca1["fecharegistro"];
                            }
                            if ($ca1["acto"] == '0581') {
                                if (substr($ca1["fecharegistro"], 0, 4) < $matri["anorenovacion"]) {
                                    $cafeini = '';
                                    $cafefin = '';
                                } else {
                                    $cafefin = $ca1["fecharegistro"];
                                }
                            }
                        }
                    }
                    if ($desbloquear045 != 'S') {
                        if ($cafeini != '') {
                            if ($cafefin == '') {
                                if ($matri["anorenovacion"] > substr($cafeini, 0, 4)) {
                                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.cie.est', $matri["anorenovacion"], $matri["matricula"]);
                                    // if ($menerror === '') {
                                    $menerror = 'El expediente ' . $matri["matricula"] . ' ha sido cerrado, no debe renovar el año ' . $matri["anorenovacion"];
                                    // }
                                    $txtcesacionactividad .= $menerror . '<br>';
                                    $simensaje = true;
                                }
                                if ($matri["anorenovacion"] == substr($cafeini, 0, 4)) {
                                    $fecorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . substr($cafeini, 0, 4) . "'");
                                    if ($cafeini <= $fecorte["corte"]) {
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.cie.est', $matri["anorenovacion"], $matri["matricula"]);
                                        // if ($menerror === '') {
                                        $menerror = 'El expediente ' . $matri["matricula"] . ' ha sido cerrado, no debe renovar el año ' . $matri["anorenovacion"];
                                        // }
                                        $txtcesacionactividad .= $menerror . '<br>';
                                        $simensaje = true;
                                    }
                                }
                            } else {
                                if ($matri["anorenovacion"] > substr($cafeini, 0, 4)) {
                                    if ($matri["anorenovacion"] <= substr($cafefin, 0, 4)) {
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.cie.est', $matri["anorenovacion"], $matri["matricula"]);
                                        // if ($menerror === '') {
                                        $menerror = 'El expediente ' . $matri["matricula"] . ' ha sido cerrado, no debe renovar el año ' . $matri["anorenovacion"];
                                        // }
                                        $txtcesacionactividad .= $menerror . '<br>';
                                        $simensaje = true;
                                    }
                                }
                                if ($matri["anorenovacion"] == substr($cafeini, 0, 4)) {
                                    $fecorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . substr($cafeini, 0, 4) . "'");
                                    if ($cafeini <= $fecorte["corte"]) {
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.cie.est', $matri["anorenovacion"], $matri["matricula"]);
                                        // if ($menerror === '') {
                                        $menerror = 'El expediente ' . $matri["matricula"] . ' ha sido cerrado, no debe renovar el año ' . $matri["anorenovacion"];
                                        // }
                                        $txtcesacionactividad .= $menerror . '<br>';
                                        $simensaje = true;
                                    }
                                }
                            }
                            \logApi::general2($nameLog, '', 'Valido cesacion de actividad');
                        }
                    }
                }
            }

            //
            if (!isset($mats[$matri["matricula"]])) {
                $mats[$matri["matricula"]] = array(
                    'matricula' => $matri["matricula"],
                    'activos' => $matri["activos"],
                    'personal' => $matri["personal"],
                    'anorenovacion' => $matri["anorenovacion"],
                    'organizacion' => $exp["organizacion"],
                    'categoria' => $exp["categoria"]
                );
            } else {
                $mats[$matri["matricula"]]["activos"] = $matri["activos"];
                $mats[$matri["matricula"]]["anorenovacion"] = $matri["anorenovacion"];
            }

            //
            $mats1[] = array(
                'matricula' => $matri["matricula"],
                'activos' => $matri["activos"],
                'personal' => $matri["personal"],
                'anorenovacion' => $matri["anorenovacion"],
                'organizacion' => $exp["organizacion"],
                'categoria' => $exp["categoria"]
            );

            //
            if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '01' && $exp["categoria"] == '1')) {
                if (defined('RENOVACION_PORCENTAJE_DISMINUCION') && RENOVACION_PORCENTAJE_DISMINUCION != '' && RENOVACION_PORCENTAJE_DISMINUCION != 0) {
                    $actdis = $exp["acttot"] - ($exp["acttot"] * RENOVACION_PORCENTAJE_DISMINUCION / 100);
                } else {
                    $actdis = $exp["acttot"];
                }
            }

            if ($exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                if (defined('RENOVACION_PORCENTAJE_DISMINUCION') && RENOVACION_PORCENTAJE_DISMINUCION != '' && RENOVACION_PORCENTAJE_DISMINUCION != 0) {
                    $actdis = $exp["actvin"] - ($exp["actvin"] * RENOVACION_PORCENTAJE_DISMINUCION / 100);
                } else {
                    $actdis = $exp["actvin"];
                }
            }

            // Bloquea activos en ceros
            if (!$simensaje) {
                if (defined('RENOVACION_ACTIVOS_CERO') && RENOVACION_ACTIVOS_CERO == 'N') {
                    if ($matri["activos"] == 0) {
                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.ceros', $matri["anorenovacion"], $matri["matricula"], '', '');
                        // if ($menerror === '') {
                        $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser 0.';
                        // }
                        $txtceros .= $menerror . '<br>';
                        $simensaje = true;
                    }
                }
            }

            // Activos menores bloqueados
            if (!$simensaje) {
                if (defined('RENOVACION_BLOQUEAR_ACTIVOS_MENORES') && RENOVACION_BLOQUEAR_ACTIVOS_MENORES == 'S') {
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        if (doubleval($matri["activos"]) < doubleval($exp["acttot"])) {
                            // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                            // if ($menerror === '') {
                            // $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                            // JINT : 20250206 : RF-199934-1-45932
                            $menerror = 'Nos permitimos informar que el valor de los activos reportados ha disminuido en comparación con los reportados el año anterior. ';
                            $menerror .= 'En caso de detectar algún error, podrá realizar los ajustes necesarios y volver a calcular su pre liquidación. ';
                            $menerror .= 'Si el dato suministrado es correcto, deberá continuar con la renovación de su matrícula dirigiéndose a la oficina más cercana de cualquier ';
                            $menerror .= 'cámara de comercio del país y presentar la documentación que lo acredite (estado de situación financiera y/o certificado de contador o ';
                            $menerror .= 'revisor fiscal). La información sobre los activos debe reflejar fielmente el estado de situación financiera. Presentar datos falsos en el ';
                            $menerror .= 'registro conlleva sanciones conforme al artículo 38 del Código de Comercio, y la respectiva cámara de comercio estará obligada a formular denuncia ante el juez competente.';
                            // }
                            $txtactivosmenores .= $menerror . '<br>';
                            $simensaje = true;
                        }
                    } else {
                        if ($decreto045a == 'no') {
                            if (doubleval($matri["activos"]) < doubleval($exp["actvin"])) {
                                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                // if ($menerror === '') {
                                $menerror = 'Los activos vinculados para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                // }
                                $txtactivosmenores .= $menerror . '<br>';
                                $simensaje = true;
                            }
                        }
                    }
                } else {
                    if ($exp["organizacion"] == '01') {
                        if (doubleval($matri["activos"]) < $actdis) {
                            if (!defined('RENOVACION_PEDIR_BALANCE_PNAT') || (defined('RENOVACION_PEDIR_BALANCE_PNAT') && RENOVACION_PEDIR_BALANCE_PNAT != 'N')) {
                                $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                            }
                        }
                    }
                    if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
                        if ($exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                            if (doubleval($matri["activos"]) < $actdis) {
                                if (!defined('RENOVACION_PEDIR_BALANCE_PJUR') || (defined('RENOVACION_PEDIR_BALANCE_PJUR') && RENOVACION_PEDIR_BALANCE_PJUR != 'N')) {
                                    $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                                }
                            }
                        }
                        if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                            if (doubleval($matri["activos"]) < $actdis) {
                                if (!defined('RENOVACION_PEDIR_BALANCE_ESADL') || (defined('RENOVACION_PEDIR_BALANCE_ESADL') && RENOVACION_PEDIR_BALANCE_ESADL != 'N')) {
                                    $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                                }
                            }
                        }
                    }
                }
            }

            //
            if (!$simensaje) {
                if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
                    if (defined('RENOVACION_ACEPTAR_VALOR_USUPUBXX') && RENOVACION_ACEPTAR_VALOR_USUPUBXX == 'igual') {
                        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                            if (doubleval($matri["activos"]) < doubleval($exp["acttot"])) {
                                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                // if ($menerror === '') {
                                // $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                // JINT : 20250206 : RF-199934-1-45932
                                $menerror = 'Nos permitimos informar que el valor de los activos reportados no ha aumentado en comparación con los reportados el año anterior. ';
                                $menerror .= 'En caso de detectar algún error, podrá realizar los ajustes necesarios y volver a calcular su pre liquidación. ';
                                $menerror .= 'Si el dato suministrado es correcto, deberá continuar con la renovación de su matrícula dirigiéndose a la oficina más cercana de cualquier ';
                                $menerror .= 'cámara de comercio del país y presentar la documentación que lo acredite (estado de situación financiera y/o certificado de contador o ';
                                $menerror .= 'revisor fiscal). La información sobre los activos debe reflejar fielmente el estado de situación financiera. Presentar datos falsos en el ';
                                $menerror .= 'registro conlleva sanciones conforme al artículo 38 del Código de Comercio, y la respectiva cámara de comercio estará obligada a formular denuncia ante el juez competente.';
                                $txtactivosmenores .= $menerror . '<br>';
                                $simensaje = true;
                            }
                        } else {
                            if ($decreto045a == 'no') {
                                if (doubleval($matri["activos"]) < doubleval($exp["actvin"])) {
                                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                    // if ($menerror === '') {
                                    $menerror = 'Los activos vinculados para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                    // }
                                    $simensaje = true;
                                }
                            }
                        }
                    }

                    if (defined('RENOVACION_ACEPTAR_VALOR_USUPUBXX') && RENOVACION_ACEPTAR_VALOR_USUPUBXX == 'mayor') {
                        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                            if (doubleval($matri["activos"]) <= doubleval($exp["acttot"])) {
                                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                // if ($menerror === '') {
                                // $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                // JINT : 20250206 : RF-199934-1-45932
                                $menerror = 'Nos permitimos informar que el valor de los activos reportados ha disminuido en comparación con los reportados el año anterior. ';
                                $menerror .= 'En caso de detectar algún error, podrá realizar los ajustes necesarios y volver a calcular su pre liquidación. ';
                                $menerror .= 'Si el dato suministrado es correcto, deberá continuar con la renovación de su matrícula dirigiéndose a la oficina más cercana de cualquier ';
                                $menerror .= 'cámara de comercio del país y presentar la documentación que lo acredite (estado de situación financiera y/o certificado de contador o ';
                                $menerror .= 'revisor fiscal). La información sobre los activos debe reflejar fielmente el estado de situación financiera. Presentar datos falsos en el ';
                                $menerror .= 'registro conlleva sanciones conforme al artículo 38 del Código de Comercio, y la respectiva cámara de comercio estará obligada a formular denuncia ante el juez competente.';
                                // }
                                $txtaceptarvalor .= $menerror . '<br>';
                                $simensaje = true;
                            }
                        } else {
                            if ($decreto045a == 'no') {
                                if (doubleval($matri["activos"]) <= doubleval($exp["actvin"])) {
                                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                    // if ($menerror === '') {
                                    $menerror = 'Los activos vinculados para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                    // }
                                    $txtaceptarvalor .= $menerror . '<br>';
                                    $simensaje = true;
                                }
                            }
                        }
                    }
                } else {
                    if (defined('RENOVACION_ACEPTAR_VALOR') && RENOVACION_ACEPTAR_VALOR == 'igual') {
                        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                            if (doubleval($matri["activos"]) < doubleval($exp["acttot"])) {
                                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                // if ($menerror === '') {
                                $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                // }
                                $txtactivosmenores .= $menerror . '<br>';
                                $simensaje = true;
                            }
                        } else {
                            if ($decreto045a == 'no') {
                                if (doubleval($matri["activos"]) < doubleval($exp["actvin"])) {
                                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                    // if ($menerror === '') {
                                    $menerror = 'Los activos vinculados para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                    // }
                                    $txtactivosmenores .= $menerror . '<br>';
                                    $simensaje = true;
                                }
                            }
                        }
                    }

                    if (defined('RENOVACION_ACEPTAR_VALOR') && RENOVACION_ACEPTAR_VALOR == 'mayor') {
                        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                            if (doubleval($matri["activos"]) <= doubleval($exp["acttot"])) {
                                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                // if ($menerror === '') {
                                $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                // }
                                $txtaceptarvalor .= $menerror . '<br>';
                                $simensaje = true;
                            }
                        } else {
                            if ($decreto045a == 'no') {
                                if (doubleval($matri["activos"]) <= doubleval($exp["actvin"])) {
                                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.menores', $matri["anorenovacion"], $matri["matricula"]);
                                    // if ($menerror === '') {
                                    $menerror = 'Los activos vinculados para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a los del año anterior.';
                                    // }
                                    $txtaceptarvalor .= $menerror . '<br>';
                                    $simensaje = true;
                                }
                            }
                        }
                    }
                }
            }

            if (!$simensaje) {
                if (defined('RENOVACION_ACTIVO_MINIMO') && RENOVACION_ACTIVO_MINIMO !== '' && RENOVACION_ACTIVO_MINIMO !== '0') {
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        if ($exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                            if (doubleval($matri["activos"]) < doubleval(RENOVACION_ACTIVO_MINIMO)) {
                                if (!defined('RENOVACION_ACTIVO_MINIMO_PEDIR_BALANCE') || RENOVACION_ACTIVO_MINIMO_PEDIR_BALANCE === '' || RENOVACION_ACTIVO_MINIMO_PEDIR_BALANCE === 'N') {
                                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.minimo', $matri["anorenovacion"], $matri["matricula"], RENOVACION_ACTIVO_MINIMO);
                                    // if ($menerror === '') {
                                    $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a $' . number_format(RENOVACION_ACTIVO_MINIMO);
                                    // }
                                    $txtactivosminimos .= $menerror . '<br>';
                                    $simensaje = true;
                                } else {
                                    if ($_SESSION["entrada"]["usuariows"] == 'AppConfe' || $_SESSION["entrada"]["usuariows"] == 'EXUS') {
                                        $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.minimo', $matri["anorenovacion"], $matri["matricula"], RENOVACION_ACTIVO_MINIMO);
                                        $menerror = 'Está tratando de renovar con activos inferiores al valor mínimo establecido, le invitamos a realizar la renovación desde el sitio web de la cámara de comercio';
                                        $txtactivosminimos .= $menerror . '<br>';
                                        $simensaje = true;
                                    } else {
                                        $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                                    }
                                }
                            }
                        }
                    } else {
                        if ($decreto045a == 'no') {
                            if (doubleval($matri["activos"]) < doubleval(RENOVACION_ACTIVO_MINIMO)) {
                                if (!defined('RENOVACION_ACTIVO_MINIMO_PEDIR_BALANCE') || RENOVACION_ACTIVO_MINIMO_PEDIR_BALANCE === '' || RENOVACION_ACTIVO_MINIMO_PEDIR_BALANCE === 'N') {
                                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.minimo', $matri["anorenovacion"], $matri["matricula"], RENOVACION_ACTIVO_MINIMO);
                                    // if ($menerror === '') {
                                    $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' y matrícula No. ' . $matri["matricula"] . ' no deben ser menores a $' . number_format(RENOVACION_ACTIVO_MINIMO);
                                    // }
                                    $txtactivosminimos .= $menerror . '<br>';
                                    $simensaje = true;
                                }
                            }
                        }
                    }
                }
            }

            if (!$simensaje) {
                if (defined('RENOVACION_ACTIVO_MINIMO_ESADL') && RENOVACION_ACTIVO_MINIMO_ESADL !== '' && RENOVACION_ACTIVO_MINIMO_ESADL !== '0') {
                    if (($exp["organizacion"] == '12' || $exp["organizacion"] == '14') && $exp["categoria"] == '1') {
                        if (doubleval($matri["activos"]) < doubleval(RENOVACION_ACTIVO_MINIMO_ESADL)) {
                            if (!defined('RENOVACION_ACTIVO_MINIMO_ESADL_PEDIR_BALANCE') || RENOVACION_ACTIVO_MINIMO_ESADL_PEDIR_BALANCE === '' || RENOVACION_ACTIVO_MINIMO_ESADL_PEDIR_BALANCE === 'N') {
                                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.act.minimo', $matri["anorenovacion"], $matri["matricula"], RENOVACION_ACTIVO_MINIMO_ESADL);
                                // if ($menerror === '') {
                                $menerror = 'Los activos para el año ' . $matri["anorenovacion"] . ' e inscripción No. ' . $matri["matricula"] . ' no deben ser menores a $' . number_format(RENOVACION_ACTIVO_MINIMO);
                                // }
                                $txtactivosminimos .= $menerror . '<br>';
                                $simensaje = true;
                            } else {
                                $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, '', 'Valido si se renovaran todos los años o hasta donde renovara');

        // Control de cesación de actividad
        if ($txtcesacionactividad != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $txtcesacionactividad;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'evaluo $txtcesacionactividad');

        // Control de activos en cero
        if ($txtceros != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $txtceros;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'evaluo $txtceros');

        // Control de activos menores
        if ($txtactivosmenores != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $txtactivosmenores;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'evaluo $txtactivosmenores');

        // Control de activos aceptar valor
        if ($txtaceptarvalor != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $txtaceptarvalor;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'evaluo $txtaceptarvalor');

        // Control de activos minimos
        if ($txtactivosminimos != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $txtactivosminimos;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'evaluo $txtactivosminimos');

        //
        if ($mprop != '') {
            $expx1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mprop . "'");
            if ($expx1) {
                if ($expx1["ctrbenley1780"] == 'S') {
                    if (substr($expx1["fecmatricula"], 0, 4) < date("Y") - 1) {
                        $expx1["cumplerequisitos1780"] = 'N';
                        $expx1["cumplerequisitos1780primren"] = 'N';
                        $expx1["ctrbenley1780"] = 'P';
                        \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $mprop, 'ctrbenley1780', $expx1["ctrbenley1780"], '', '', 'liquidacionRenovacionMultiplesAnios');
                        \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $mprop, 'cumplerequisitos1780', $expx1["cumplerequisitos1780"], '', '', 'liquidacionRenovacionMultiplesAnios');
                        \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $mprop, 'cumplerequisitos1780primren', $expx1["cumplerequisitos1780primren"], '', '', 'liquidacionRenovacionMultiplesAnios');
                    }
                }
            }
        }

        if ($_SESSION["jsonsalida"]["pedirbalance"] != 'si') {
            if ($_SESSION["jsonsalida"]["idusuario"] == 'USUPUBXX') {
                if (defined('RENOVACION_SOLICITAR_BALANCE_TODAS_USUPUBXX') && RENOVACION_SOLICITAR_BALANCE_TODAS_USUPUBXX == 'S') {
                    $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                }
            } else {
                if (defined('RENOVACION_SOLICITAR_BALANCE_TODAS_CAJEROS') && RENOVACION_SOLICITAR_BALANCE_TODAS_CAJEROS == 'S') {
                    $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                }
            }
        }


        // controla la sumatoria total de activos vinculados vs el activo el propietario
        // solo si no se ha activado el decreto045
        // Si se renueva el propietario a la par de sus establecimientos
        if (substr((string) $proplocal, 0, 2) == 'si') {
            if ($decreto045 == 'no') {
                $erroractivos = 'no';
                foreach ($matprop1 as $ind => $p1) {
                    $anop = substr($ind, 0, 4);
                    $totest = 0;
                    foreach ($matest1 as $ind => $e1) {
                        $anoe = substr($ind, 0, 4);
                        if ($anoe == $anop) {
                            $totest = $totest + $e1;
                        }
                    }
                    if ($totest > 0) {
                        if ($totest > $p1) {
                            $erroractivos = 'si';
                        }
                    }
                }
                if ($erroractivos == 'si') {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.sum.est');
                    // if ($menerror === '') {
                    $menerror = 'La sumatoría de los activos de los establecimientos de comercio son superiores al valor del activo del propietario';
                    // }
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        }
        \logApi::general2($nameLog, '', 'Valido sumatoria de activos de establecimientos');

        // ******************************************************************************************* //
        // Si el propietario no renueva en el trámite, lo busca.
        // Sea localmente o nacionalmente (si el decreto045 está activo)
        // ******************************************************************************************* //
        if ($mprop == '') {
            \logApi::general2($nameLog, '', 'Buscara el propietario del establecimiento ' . $mest . ' pues no se renueva en esta liquidación');
            if ($mest != '') {
                if ($corg == '02') {
                    $cantprop = 0;
                    $prop = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $mest . "'", "id");
                    if ($prop && !empty($prop)) {
                        foreach ($prop as $p) {
                            if ($p["estado"] == 'V') {
                                $cantprop++;
                            }
                        }
                    }
                    if ($cantprop > 1) {
                        $tideprop = '';
                        $ideprop = '';
                        $proplocal = 'multiple';
                    } else {
                        foreach ($prop as $p) {
                            if ($p["estado"] == 'V') {
                                if ($p["codigocamara"] == CODIGO_EMPRESA) {
                                    $prop1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $p["matriculapropietario"] . "'", "matricula,idclase,numid,acttot,ultanoren");
                                    if ($prop1 && !empty($prop1)) {
                                        $cafeini1 = '';
                                        $cafefin1 = '';
                                        $mprop = $prop1["matricula"];
                                        $tideprop = $prop1["idclase"];
                                        $ideprop = $prop1["numid"];
                                        $actprop = $prop1["acttot"];
                                        $aactprop[$prop1["ultanoren"]] = $prop1["acttot"];
                                        $uarenprop = $prop1["ultanoren"];
                                        $proplocal = 'si-noincluido';
                                        $ca = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $mprop . "' and acto IN ('0580','0581')", "fecharegistro,horaregistro");
                                        if ($ca && !empty($ca)) {
                                            foreach ($ca as $ca1) {
                                                if (substr($ca1["fecharegistro"], 0, 4) <= $matri["anorenovacion"]) {
                                                    if ($ca1["acto"] == '0580') {
                                                        $cafeini1 = $ca1["fecharegistro"];
                                                    }
                                                    if ($ca1["acto"] == '0581') {
                                                        if (substr($ca1["fecharegistro"], 0, 4) < $matri["anorenovacion"]) {
                                                            $cafeini1 = '';
                                                            $cafefin1 = '';
                                                        } else {
                                                            $cafefin1 = $ca1["fecharegistro"];
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        if ($cafeini1 != '') {
                                            $cesactprop = 'si';
                                        }
                                    }
                                } else {
                                    $tideprop = $p["tipoidentificacion"];
                                    $ideprop = $p["identificacion"];
                                    $proplocal = 'no';
                                }
                            }
                        }
                    }
                } else {
                    if ($cest == '2' || $cest == '3') {
                        $est1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mest . "'");
                        if ($est1 && !empty($est1)) {
                            if ($est1["cpcodcam"] == CODIGO_EMPRESA && $est1["cpnummat"] != '') {
                                $prop1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $est1["cpnummat"] . "'");
                                if ($prop1 && !empty($prop1)) {
                                    $mprop = $prop1["matricula"];
                                    $tideprop = $prop1["idclase"];
                                    $ideprop = $prop1["numid"];
                                    $actprop = $prop1["acttot"];
                                    $aactprop[$prop1["ultanoren"]] = $prop1["acttot"];
                                    $uarenprop = $prop1["ultanoren"];
                                    $proplocal = 'si-noincluido';
                                } else {
                                    $tideprop = '2';
                                    $ideprop = $est1["cpnumnit"];
                                    $proplocal = 'no';
                                }
                            } else {
                                $tideprop = '2';
                                $ideprop = $est1["cpnumnit"];
                                $proplocal = 'no';
                            }
                        }
                    }
                }
            }
        }

        // }
        // Encuentra el valor de los activos del propietario a nivel nacional y
        // Solo si el decreto045 esta activado
        // solo si el propietario no es local
        // if ($decreto045 == 'si') {

        if ($proplocal == 'no') {
            $prop = \funcionesRues::consultarRegMerIdentificacionActivos($_SESSION["entrada"]["idusuario"], $tideprop, $ideprop);
            if ($prop) {
                $actproprues = $prop["activos_totales"];
                $uarenproprues = $prop["ultimo_ano_renovado"];
                $uarenprop = $prop["ultimo_ano_renovado"];
                $actprop = $prop["activos_totales"];
                $proplocal = 'no-matriculado';
            } else {
                $proplocal = 'no-nomatriculado';
            }
        }

        //
        ob_end_clean();

        // }
        //
        $msg = "Condición del propietario\r\n";
        $msg .= "matricula del propietario : " . $mprop . "\r\n";
        $msg .= "Identificación el propietario : " . $tideprop . "-" . $ideprop . "\r\n";
        $msg .= "Ultimos Activos del propietario : " . $actprop . "\r\n";
        $msg .= "Ultimo año renovado del propietario : " . $uarenprop . "\r\n";
        $msg .= "Tipo propietario : " . $proplocal . "\r\n";
        if ($proplocal == 'no-matriculado') {
            $msg .= "Activos del propietario (rues): " . $actproprues . "\r\n";
            $msg .= "Ultimo año renovado del propietario (rues): " . $uarenproprues . "\r\n";
        }
        \logApi::general2($nameLog, '', $msg);

        //
        $siinhabilidad = 0;
        if ($ideprop != '') {
            $inhabs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='RM02' and tipoidentificacion='" . $tideprop . "' and identificacion='" . $ideprop . "'", "fecharegistro");
            if ($inhabs && !empty($inhabs)) {
                foreach ($inhabs as $inh) {
                    if ($inh["acto"] == '3010' || $inh["acto"] == '3020' || $inh["acto"] == '3030') {
                        $siinhabilidad++;
                    }
                    if ($inh["acto"] == '3080' || $inh["acto"] == '3081' || $inh["acto"] == '3089') {
                        $siinhabilidad--;
                    }
                }
            }
        }

        // 
        if ($siinhabilidad > 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $menerror = 'El propietario/comerciante se encuentra con inhabilidad, no es posible continuar con el proceso.';
            $mysqli->close();
            $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // En caso de controlar que el propietario local o foraneo tenga que estar renovado al año actual.
        if ($decreto045 == 'si') {
            if ($proplocal == 'no-matriculado') {
                if ($decreto045blqforaneonoren == 'si') {
                    if ($_SESSION["jsonsalida"]["desbloquear045"] != 'S') {
                        if ($uarenprop < date("Y")) {
                            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                            $menerror = 'El propietario no ha renovando al periodo actual, no es posible continuar con el proceso.';
                            $mysqli->close();
                            $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                            $api->response($api->json($_SESSION["jsonsalida"]), 200);
                        }
                    }
                }
            }
        }

        //
        if ($decreto045 == 'si') {
            if (substr($proplocal, 0, 2) == 'si') {
                if (isset($matriculasIncluidas[$matri["matricula"]]["cantidadpropietarios"]) && $matriculasIncluidas[$matri["matricula"]]["cantidadpropietarios"] == 1) {
                    if ($decreto045blqlocalnoren == 'si') {
                        if ($matri["anorenovacion"] . '0101' >= $feinicio045general) {
                            if ($uarenprop < date("Y")) {
                                if ($cesactprop != 'si') {
                                    $bloquear = 'no';
                                    $blqanotxt = '';
                                    foreach ($matblq as $blqano => $blqdat) {
                                        if ($blqdat["prop"] == 0 && $blqdat["est"] > 0) {
                                            $bloquear = 'si';
                                            $blqanotxt = $blqano;
                                        }
                                    }
                                }
                                if ($bloquear == 'si') {
                                    if (!isset($matriculasIncluidas[$matri["matricula"]]["desbloquear045"]) || $matriculasIncluidas[$matri["matricula"]]["desbloquear045"] != 'S') {
                                        if ($mprop != '') {
                                            $exp1 = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $mprop);
                                            if ($exp1["estadomatricula"] == 'MA' || $exp1["estadomatricula"] == 'MI' || $exp1["estadomatricula"] == 'IA' || $exp1["estadomatricula"] == 'II') {
                                                $at = array();
                                                $at["anorenovar"] = $blqanotxt;
                                                if (!estaDisueltaLocal($mysqli, $exp1, $at)) {
                                                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                                                    $menerror = 'El propietario (' . $mprop . ') no está renovando al año ' . $blqanotxt . ', no es posible continuar con el proceso de renovación.';
                                                    $mysqli->close();
                                                    $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                                                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //
        $matriculas = array();
        $matControl = array();
        $icon = 0;
        foreach ($_SESSION["jsonsalida"]["matriculas"] as $m) {
            if (!isset($matControl[$m["matricula"]])) {
                $matControl[$m["matricula"]] = $m["matricula"];
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m["matricula"] . "'");
                if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                    if ($exp["categoria"] == '1') {
                        if ($exp["ultanoren"] < '2012') {
                            $exp["ultanoren"] = '2012';
                        }
                    }
                }
                if ($exp["ultanoren"] < date("Y")) {
                    for ($i = $exp["ultanoren"] + 1; $i <= date("Y"); $i++) {
                        $enc = 'no';
                        foreach ($mats1 as $mx1) {
                            if ($mx1["matricula"] == $m["matricula"] && $mx1["anorenovacion"] == $i) {
                                $enc = 'si';
                            }
                        }
                        $icon++;
                        $matriculas[$icon] = array(
                            'matricula' => $m["matricula"],
                            'personal' => $m["personal"],
                            'anorenovar' => $i,
                            'reliquidacion' => 'no',
                            'activosiniciales' => $exp["acttot"],
                            'activoactual' => $m["activos"],
                            'correcto' => $enc,
                            'organizacion' => $exp["organizacion"],
                            'categoria' => $exp["categoria"],
                            'control' => ''
                        );
                    }
                } else {
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        $icon++;
                        $matriculas[$icon] = array(
                            'matricula' => $m["matricula"],
                            'personal' => $m["personal"],
                            'anorenovar' => date("Y"),
                            'reliquidacion' => 'si',
                            'activosiniciales' => $exp["acttot"],
                            'activoactual' => $m["activos"],
                            'correcto' => 'si',
                            'organizacion' => $exp["organizacion"],
                            'categoria' => $exp["categoria"],
                            'control' => ''
                        );
                    } else {
                        $icon++;
                        $matriculas[$icon] = array(
                            'matricula' => $m["matricula"],
                            'personal' => $m["personal"],
                            'anorenovar' => date("Y"),
                            'reliquidacion' => 'si',
                            'activosiniciales' => $exp["actvin"],
                            'activoactual' => $m["activos"],
                            'correcto' => 'si',
                            'organizacion' => $exp["organizacion"],
                            'categoria' => $exp["categoria"],
                            'control' => ''
                        );
                    }
                }
            }
        }

        // 
        if ($_SESSION["entrada"]["permitirsaltoanos"] != 'S') {
            $txt = array();
            $mx3 = '';
            foreach ($matriculas as $icon => $mx1) {
                if ($mx3 != $mx1["matricula"]) {
                    $controlerrores = 0;
                    $anoscontrol = 0;
                    $mx3 = $mx1["matricula"];
                    $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $mx1["matricula"]);
                }
                $anoscontrol++;

                //
                if ($exp["desbloquear045"] != 'S') {
                    if ($mx1["correcto"] == 'no') {
                        $validar = '';
                        if ($anoscontrol == 1) {
                            $validar = 'si';
                        } else {
                            $validar = 'no';
                            foreach ($matriculas as $icon2 => $mx2) {
                                if ($mx2["matricula"] == $mx1["matricula"]) {
                                    if ($icon2 > $icon) {
                                        if ($mx2["correcto"] == 'si') {
                                            $validar = 'si';
                                        }
                                    }
                                }
                            }
                        }

                        //
                        if ($validar == 'si') {
                            if ($exp) {
                                if (!estaDisueltaLocal($mysqli, $exp, $mx1, $nameLog)) {
                                    if (!estaCesadaActividadLocal($mysqli, $exp, $mx1, $nameLog)) {
                                        // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.ano.debe.renovar', $mx1["anorenovar"], $mx1["matricula"]);
                                        $menerror = 'El expediente ' . $mx1["matricula"] . ' no debe renovar el periodo ' . $mx1["anorenovar"] . ' dada que su actividad ha cesado';
                                        $txt[] = $menerror;
                                        $matriculas[$icon]["control"] = 'error';
                                    }
                                }
                            } else {
                                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.ano.debe.renovar', $mx1["anorenovar"], $mx1["matricula"]);
                                $menerror = 'El expediente ' . $mx1["matricula"] . ' debe renovar el periodo ' . $mx1["anorenovar"];
                                $txt[] = $menerror;
                                $matriculas[$icon]["control"] = 'error';
                            }
                        }
                    } else {
                        if (!estaDisueltaLocal($mysqli, $exp, $mx1, $nameLog)) {
                            if (estaCesadaActividadLocal($mysqli, $exp, $mx1, $nameLog)) {
                                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.ces.act', $mx1["anorenovar"], $mx1["matricula"]);
                                $menerror = 'El expediente ' . $mx1["matricula"] . ' no debe renovar el periodo ' . $mx1["anorenovar"] . ' dado que su actividad ha cesado';
                                $txt[] = $menerror;
                                $matriculas[$icon]["control"] = 'error';
                            }
                        } else {
                            // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.dis.no.debe.renovar', $mx1["anorenovar"], $mx1["matricula"]);
                            $menerror = 'El expediente ' . $mx1["matricula"] . ' no debe renovar el periodo ' . $mx1["anorenovar"] . ' puesto que se encuentra disuelto. ';
                            $menerror .= 'De acuerdo con la normatividad que rige el registro, las personas jurídicas no deben renovar su matrícula o inscripción ';
                            $menerror .= 'a partir del momento en que se disuelven y entran en estado de liquidación.';
                            $txt[] = $menerror;
                            $matriculas[$icon]["control"] = 'error';
                        }
                    }
                }
            }
            if (!empty($txt)) {
                $txt1 = '';
                foreach ($txt as $tx0) {
                    if ($txt1 != '') {
                        $txt1 .= '<br>';
                    }
                    // $txt1 .= \funcionesGenerales::utf8_decode($tx0);
                    $txt1 .= $tx0;
                }
                unset($txt);
                \logApi::general2($nameLog, '', 'Errores en liquidacion: ' . $txt1);
                $mysqli->close();
                // unset($_SESSION["jsonsalida"]);
                // $_SESSION["jsonsalida"] = array();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $txt1;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        \logApi::general2($nameLog, '', 'Salio de validar saltos de años y evaluación de disoluciones');

        //
        $totalmatriculas = 0;
        $reliquidacion = 0;
        $matexp = array();
        foreach ($matControl as $m) {

            $cantfor++;
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");

            // ****************************************************************************** //
            // En caso que el expediente no exista
            // ****************************************************************************** //
            if ($exp === false || empty($exp)) {
                // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.exp.noexiste');
                // $menerror = str_replace("[MATRICULA]", $m, $menerror);
                $menerror = 'El expediente ' . $m . ' no fue localizado en la BD';
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            // ****************************************************************************** //
            // Si matrícula no está activa
            // ****************************************************************************** //
            if (isset($_SESSION["entrada"]["sistemacreacion"]) && $_SESSION["entrada"]["sistemacreacion"] == 'SII3') {
                if ($exp["ctrestmatricula"] != 'MA' && $exp["ctrestmatricula"] != 'IA' && $exp["ctrestmatricula"] != 'MI' && $exp["ctrestmatricula"] != 'II') {
                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.exp.inactivo');
                    // $menerror = str_replace("[MATRICULA]", $m, $menerror);
                    $menerror = 'El expediente ' . $m . ' se encuentra inactivo (SIPREF)';
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            } else {
                if ($exp["ctrestmatricula"] != 'MA' && $exp["ctrestmatricula"] != 'IA') {
                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.exp.inactivo', '', $m);
                    $menerror = 'El expediente ' . $m . ' se encuentra inactivo (SIPREF)';
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            // ****************************************************************************** //
            // pnat fallecidos
            // ****************************************************************************** //
            if ($exp["organizacion"] == '01') {
                if (strpos($exp["razonsocial"], 'FALLECIDO') || strpos($exp["razonsocial"], 'FALLECIDA')) {
                    // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.exp.fallecido', '', $m);
                    $menerror = 'El expediente ' . $m . ' corresponde a una persona fallecida';
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            // ****************************************************************************** //
            // Si ya está renovado al año actual
            // ****************************************************************************** //
            if ($exp["ultanoren"] == date("Y")) {
                $reliquidacion++;
            }
            $totalmatriculas++;

            // **************************************************************************************** //
            // Si es sucursal o agencia verifica que si la principal esta en la misma jurisdiccion o no
            // **************************************************************************************** //
            $propjurisdiccion = 'no';
            if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                if ($exp["cpcodcam"] == '' || $exp["cpcodcam"] == CODIGO_EMPRESA) {
                    $propjurisdiccion = 'si';
                }
            }
            if ($exp["organizacion"] == '02') {
                if ($prim == $exp["matricula"]) {
                    $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $exp["matricula"] . "'", "id");
                    foreach ($props as $px) {
                        if ($px["estado"] == 'V') {
                            if ($px["matriculapropietario"] != '') {
                                if ($px["codigocamara"] == '' || $px["codigocamara"] == CODIGO_EMPRESA) {
                                    $propjurisdiccion = 'si';
                                }
                            }
                        }
                    }
                } else {
                    $propjurisdiccion = 'si';
                }
            }

            // ************************************************************************** //
            // Encuentra el valor de la liquidación de renovación del comerciante
            // ************************************************************************** //
            $anosrenovados = 0;
            $ultanoren = '';
            $ultactivos = 0;
            $reliquidarmatricula = '';

            foreach ($matriculas as $mx1) {
                if ($mx1["matricula"] == $m) {
                    foreach ($_SESSION["jsonsalida"]["matriculas"] as $mx) {
                        if ($mx["matricula"] == $mx1["matricula"] && $mx["anorenovacion"] == $mx1["anorenovar"]) {
                            \logApi::general2($nameLog, '', $msg);
                            if (!isset($mx["protegeractivos"])) {
                                $mx["protegeractivos"] = '';
                            }
                            $matexp[$mx["matricula"] . '-' . $mx["anorenovacion"]] = $mx["activos"] . '|' . $mx["personal"] . '|' . $mx["protegeractivos"];
                            $ultanoren = $mx["anorenovacion"];
                            $ultactivos = $mx["activos"];
                            $anosrenovados++;
                            $liq = array();
                            $liq["servicio"] = '';
                            $liq["cc"] = CODIGO_EMPRESA;
                            $liq["matricula"] = $exp["matricula"];
                            $liq["nmatricula"] = $exp["razonsocial"];
                            $liq["anorenovar"] = $mx["anorenovacion"];
                            $liq["cantidad"] = 1;
                            $liq["activos"] = $mx["activos"];
                            $liq["personal"] = $mx["personal"];
                            $liq["valor"] = 0;
                            $liq["organizacion"] = $exp["organizacion"];
                            $liq["categoria"] = $exp["categoria"];
                            if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                                if (substr($exp["fecmatricula"], 0, 4) == date("Y") && $exp["fecrenovacion"] >= $exp["fecmatricula"]) {
                                    if (substr($exp["matricula"], 0, 1) == 'S') {
                                        $liq["servicio"] = '01020208';
                                        $servtarifa = '01020208';
                                    } else {
                                        $liq["servicio"] = '01020201';
                                        $servtarifa = '01020101';
                                    }
                                } else {
                                    if (substr($exp["matricula"], 0, 1) == 'S') {
                                        $liq["servicio"] = '01020208';
                                        $servtarifa = '01020208';
                                    } else {
                                        $liq["servicio"] = '01020201';
                                        $servtarifa = '01020201';
                                    }
                                }
                            }
                            if ($exp["organizacion"] == '02') {
                                if (substr($exp["fecmatricula"], 0, 4) == date("Y") && $exp["fecrenovacion"] >= $exp["fecmatricula"]) {
                                    if ($propjurisdiccion == 'si') {
                                        $liq["servicio"] = '01020202';
                                        $servtarifa = '01020102';
                                    } else {
                                        $liq["servicio"] = '01020203';
                                        $servtarifa = '01020103';
                                    }
                                } else {
                                    if ($propjurisdiccion == 'si') {
                                        $liq["servicio"] = '01020202';
                                        $servtarifa = '01020202';
                                    } else {
                                        $liq["servicio"] = '01020203';
                                        $servtarifa = '01020203';
                                    }
                                }
                            }
                            if ($exp["categoria"] == '2') {
                                if ($propjurisdiccion == 'si') {
                                    $liq["servicio"] = '01020204';
                                    $servtarifa = '01020204';
                                } else {
                                    $liq["servicio"] = '01020205';
                                    $servtarifa = '01020205';
                                }
                            }
                            if ($exp["categoria"] == '3') {
                                if ($propjurisdiccion == 'si') {
                                    $liq["servicio"] = '01020206';
                                    $servtarifa = '01020206';
                                } else {
                                    $liq["servicio"] = '01020207';
                                    $servtarifa = '01020207';
                                }
                            }

                            // Valida si debe liquidar con el valor de activos del establecimiento o con el valor de activos del propietario
                            /*
                              if ($liq["organizacion"] == '02' || $liq["categoria"] == '2' || $liq["categoria"] == '3') {
                              $feinicio045a = '';
                              $decreto045a = 'no';
                              if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                              $feinicio045a = '99999999';
                              } else {
                              $feinicio045a = FECHA_INICIO_DECRETO_045;
                              }
                              if ($liq["anorenovar"] . '0101' >= $feinicio045a) {
                              $decreto045a = 'si';
                              }
                              if ($decreto045a == 'si') {
                              if ($mprop != '') {
                              $liq["activos"] = $actprop;
                              }
                              }
                              }
                              $paseactivos = $actprop;
                              if (isset($aactprop[$liq["anorenovar"]])) {
                              $paseactivos = $aactprop[$liq["anorenovar"]];
                              }
                             */
                            $msg = 'buscaTarifa: matricula -> ' . $liq["matricula"] . ', Ano renovar -> ' . $liq["anorenovar"] . ', Activos : ' . $liq["activos"];
                            \logApi::general2($nameLog, '', $msg);

                            //
                            $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $servtarifa, $liq["anorenovar"], 1, $liq["activos"], 'tarifa', null);
                            if ($mx1["reliquidacion"] == 'si') {
                                $msg = 'buscaTarifa Reliquidacion: matricula -> ' . $liq["matricula"] . ', Ano renovar -> ' . $liq["anorenovar"] . ', Activos : ' . $mx1["activosiniciales"];
                                \logApi::general2($nameLog, '', $msg);
                                $val1 = \funcionesRegistrales::buscaTarifa($mysqli, $servtarifa, $liq["anorenovar"], 1, $mx1["activosiniciales"], 'tarifa', null);
                                $liq["valor"] = $liq["valor"] - $val1;
                                if ($liq["valor"] < 0) {
                                    if (!defined('RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA') || RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA != 'S') {
                                        $liq["valor"] = 0;
                                    }
                                }
                                $reliquidarmatricula = 'si';
                            }

                            if ($exp["organizacion"] == '12' && $exp["categoria"] == '1') {
                                if ($exp ["ctrclaseespeesadl"] == '61') {
                                    $liq["valor"] = 0;
                                }
                            }
                            if ($exp["organizacion"] == '14' && $exp["categoria"] == '1') {
                                if ($exp ["ctrclaseespeesadl"] == '49' || $exp ["ctrclaseespeesadl"] == '61') {
                                    $liq["valor"] = 0;
                                }
                            }

                            $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
                        }
                    }

                    // ************************************************************************** //
                    // Si renueva más de un año no tendría beneficios
                    // ************************************************************************** //
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        if (substr($exp["fecmatricula"], 0, 4) < date("Y") - 1) {
                            $exp["ctrbenley1780"] = '';
                            $_SESSION["entrada"]["cumple1780"] = '';
                            $_SESSION["entrada"]["mantiene1780"] = '';
                        } else {
                            if ($exp["ctrbenley1780"] == 'S') {
                                if ($anosrenovados > 1) {
                                    $exp["ctrbenley1780"] = 'P';
                                    $_SESSION["entrada"]["cumple1780"] = 'N';
                                    $_SESSION["entrada"]["mantiene1780"] = 'N';
                                } else {
                                    if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                                        $exp["ctrbenley1780"] = 'P';
                                        $_SESSION["entrada"]["mantiene1780"] = 'N';
                                        $_SESSION["entrada"]["cumple1780"] = 'N';
                                    }
                                }
                            }
                        }
                    } else {
                        $exp["ctrbenley1780"] = '';
                        \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $exp["matricula"], 'ctrbenley1780', '', 'varchar', '', 'liquidacionRenovacionMultiplesAnios');
                    }

                    // ******************************************************************************** //
                    // Verifica que efectivamente se puedan liquidar beneficios y no haya renunciado
                    // ******************************************************************************** //
                    if ($exp["ctrbenley1780"] == 'S') {
                        if ($exp["cumplerequisitos1780"] == '') {
                            $exp["cumplerequisitos1780"] = $exp["ctrbenley1780"];
                        }
                        $_SESSION["entrada"]["cumple1780"] = $exp["cumplerequisitos1780"];
                        if ($_SESSION["entrada"]["cumple1780"] == 'S' && $_SESSION["entrada"]["mantiene1780"] == 'S' && $_SESSION["entrada"]["renuncia1780"] == 'N') {
                            $exp["ctrbenley1780"] = 'S';
                        } else {
                            if ($_SESSION["entrada"]["cumple1780"] == 'S' && $_SESSION["entrada"]["mantiene1780"] == 'S' && $_SESSION["entrada"]["renuncia1780"] == 'S') {
                                $exp["ctrbenley1780"] = 'R';
                            } else {
                                $exp["ctrbenley1780"] = 'P';
                                $_SESSION["entrada"]["cumple1780"] = 'N';
                                $_SESSION["entrada"]["mantiene1780"] = 'N';
                            }
                        }
                    }
                    if ($exp["ctrbenley1780"] == 'S') {
                        if ($exp["organizacion"] == '01') {
                            if ($exp["fechanacimiento"] == '') {
                                if (isset($_SESSION["entrada"]["fchanacimiento"]) && $_SESSION["entrada"]["fchanacimiento"] != '') {
                                    $exp["fechanacimiento"] = $_SESSION["entrada"]["fchanacimiento"];
                                } else {
                                    if ($exp["idclase"] == '1') {
                                        $ani = \funcionesRues::consumirANI2($mysqli, $exp["idclase"], $exp["numid"]);
                                        $exp["fechanacimiento"] = $ani["fechaNacimiento"];
                                    }
                                }
                            }
                            if ($exp["fechanacimiento"] != '') {
                                $edad = \funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $exp["fechanacimiento"], 'ANOS');
                                if ($edad > 35) {
                                    $exp["ctrbenley1780"] = 'P';
                                    $_SESSION["entrada"]["cumple1780"] = 'N';
                                    $_SESSION["entrada"]["mantiene1780"] = 'N';
                                }
                            }
                        }
                    }

                    // ************************************************************************** //
                    // Liquida beneficio de la Ley 1780
                    // ************************************************************************** //
                    if ($anosrenovados == 1) {

                        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                            if ($exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                                if (substr($exp["fecmatricula"], 0, 4) == date("Y") - 1) {
                                    if ($exp["ctrbenley1780"] == 'S') {
                                        $liq = array();
                                        $liq["servicio"] = '01090111';
                                        $liq["cc"] = CODIGO_EMPRESA;
                                        $liq["matricula"] = $exp["matricula"];
                                        $liq["nmatricula"] = $exp["razonsocial"];
                                        $liq["anorenovar"] = $ultanoren;
                                        $liq["cantidad"] = 1;
                                        $liq["activos"] = $ultactivos;
                                        $paseactivos = $actprop;
                                        if (isset($aactprop[$liq["anorenovar"]])) {
                                            $paseactivos = $aactprop[$liq["anorenovar"]];
                                        }
                                        $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, '01020201', $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $paseactivos) * -1;
                                        if ($mx1["reliquidacion"] == 'si') {
                                            if (substr($exp["fecmatricula"], 0, 4) == date("Y") && $exp["fecrenovacion"] >= $exp["fecmatricula"]) {
                                                $val1 = \funcionesRegistrales::buscaTarifa($mysqli, '01020101', $liq["anorenovar"], 1, $mx1["activosiniciales"], 'tarifa', $paseactivos) * -1;
                                            } else {
                                                $val1 = \funcionesRegistrales::buscaTarifa($mysqli, '01020201', $liq["anorenovar"], 1, $mx1["activosiniciales"], 'tarifa', $paseactivos) * -1;
                                            }
                                            $liq["valor"] = $liq["valor"] - $val1;
                                            if ($liq["valor"] > 0) {
                                                if (!defined('RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA') || RENOVACION_DEVOLUCION_DINEROS_RELIQUIDACION_CAJA != 'S') {
                                                    $liq["valor"] = 0;
                                                }
                                            }
                                        }
                                        $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                                        $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // ****************************************************************************** //
            // Busca el valor de afiliacion
            // ****************************************************************************** //}
            if ($exp["ctrafiliacion"] == '1') {
                if ($_SESSION["entrada"]["incluirafiliacion"] == 'S' || $_SESSION["entrada"]["incluirafiliacion"] == '1') {
                    $incafil = 'si';
                    if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                        if (defined('RENOVACION_BLOQUEAR_AFILIACION_ABRIL') && RENOVACION_BLOQUEAR_AFILIACION_ABRIL == 'S') {
                            $incafil = 'no';
                        }
                    }
                    if ($incafil == 'si') {
                        $valorprevio = 0;
                        if ($mx1["reliquidacion"] != 'si') {
                            $pagosafil = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $exp["matricula"] . "' and fecoperacion>='" . date("Y") . "0101' and servicio IN ('06010001','06010002')", "fecoperacion");
                            if ($pagosafil && !empty($pagosafil)) {
                                foreach ($pagosafil as $p) {
                                    $valorprevio = $valorprevio + $p["valor"];
                                }
                            }
                        }
                        $liq = array();
                        $liq["servicio"] = '06010002';
                        $liq["cc"] = CODIGO_EMPRESA;
                        $liq["matricula"] = $exp["matricula"];
                        $liq["nmatricula"] = $exp["razonsocial"];
                        $liq["anorenovar"] = date("Y");
                        $liq["cantidad"] = 1;
                        $liq["activos"] = $ultactivos;
                        $liq["valor"] = 0;
                        $paseactivos = $actprop;
                        if (isset($aactprop[$liq["anorenovar"]])) {
                            $paseactivos = $aactprop[$liq["anorenovar"]];
                        }
                        $tipotarifaafil = 'tarifa';
                        if (TIPO_AFILIACION == 'TARIFAPORTIPO') {
                            if ($exp["organizacion"] == '01') {
                                $tipotarifaafil = 'tarifapnat';
                            } else {
                                $tipotarifaafil = 'tarifapjur';
                            }
                        }
                        $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], $tipotarifaafil, $paseactivos);
                        if ($mx1["reliquidacion"] == 'si') {
                            $val1 = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $mx1["activosiniciales"], $tipotarifaafil, $paseactivos);
                            $liq["valor"] = $liq["valor"] - $val1;
                        } else {
                            $liq["valor"] = $liq["valor"] - $valorprevio;
                        }
                        if ($liq["valor"] < 0) {
                            $liq["valor"] = 0;
                        }
                        if ($liq["valor"] != 0) {
                            $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
                        } else {
                            if ($mx1["reliquidacion"] != 'si') {
                                $liq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'", "nombre");
                                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
                            }
                        }
                    }
                }
            }
        }

        if ($reliquidacion > 0 && $reliquidacion != $totalmatriculas) {
            // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.exp.aldia');
            $menerror = 'Los expedientes se encuentran al día, no procede la renovación.';
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($reliquidacion > 0) {
            $esreliquidacion = 'si';
        } else {
            $esreliquidacion = 'no';
        }

        // ****************************************************************************** //
        // Recupera el primer expediente
        // ****************************************************************************** //        
        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $prim . "'");

        // ********************************************************************************************************** //
        // jint: 2024-06-06 - Evaluación del parámetro para validar si pide o no balcnes en reliquidaciones
        // ********************************************************************************************************** //
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            if (defined('RENOVACIONES_ASIENTO_AUTOMATICO_RELIQUIDACIONES_USUPUBXX') && RENOVACIONES_ASIENTO_AUTOMATICO_RELIQUIDACIONES_USUPUBXX == 'N') {
                if ($esreliquidacion == 'si') {
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                    }
                }
            }
        } else {
            if (defined('RENOVACIONES_ASIENTO_AUTOMATICO_RELIQUIDACIONES_CAJERO') && RENOVACIONES_ASIENTO_AUTOMATICO_RELIQUIDACIONES_CAJERO == 'N') {
                if ($esreliquidacion == 'si') {
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        $_SESSION["jsonsalida"]["pedirbalance"] = 'si';
                    }
                }
            }
        }



        // ****************************************************************************** //
        // Forzar el cobro de certificados para usuarios públicos.
        // ****************************************************************************** //     
        if (RENOVACION_LIQUIDAR_CERTIFICADOS == 'S' || RENOVACION_LIQUIDAR_CERTIFICADOS == 'S-OBLIGATORIO') {
            if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
                $_SESSION["entrada"]["incluircertificado"] = 'S';
            }
        }

        // ****************************************************************************** //
        // Busca el valor del certificado a incluir
        // ****************************************************************************** //                
        if (!defined('RENOVACION_CANT_CERTIFICADOS') || RENOVACION_CANT_CERTIFICADOS == 0) {
            $cantser = 1;
        } else {
            $cantser = RENOVACION_CANT_CERTIFICADOS;
        }
        if ($_SESSION["entrada"]["incluircertificado"] == 'S' | $_SESSION["entrada"]["incluircertificado"] == '1') {
            $liq = array();
            if ($exp["organizacion"] == '01' || $exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                $liq["servicio"] = '01010101';
            } else {
                if (substr($exp["matricula"], 0, 1) == 'S') {
                    $liq["servicio"] = '01010301';
                } else {
                    $liq["servicio"] = '01010102';
                }
            }
            $liq["cc"] = CODIGO_EMPRESA;
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
            $liq["matricula"] = $exp["matricula"];
            $liq["nmatricula"] = $exp["razonsocial"];
            $liq["anorenovar"] = '';
            $liq["cantidad"] = $cantser;
            $liq["activos"] = 0;
            $paseactivos = $actprop;
            if (isset($aactprop[$liq["anorenovar"]])) {
                $paseactivos = $aactprop[$liq["anorenovar"]];
            }
            if ($exp["ctrafiliacion"] == '1') {
                if (!defined('RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS')) {
                    define('RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS', 'S');
                }
                if (RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS == 'S') {
                    $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], $cantser, $liq["activos"], 'tarifa', $paseactivos);
                } else {
                    $liq["valor"] = 0;
                }
            } else {
                $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], $cantser, $liq["activos"], 'tarifa', $paseactivos);
            }
            $liq["nservicio"] = $serv["nombre"];
            $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
        }

        // ****************************************************************************** //
        // Busca el valor del formulario a incluir
        // ****************************************************************************** //
        $coBfor = 'no';
        if ($_SESSION["entrada"]["incluirformulario"] == 'S' || $_SESSION["entrada"]["incluirformulario"] == '1' || ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX' && RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX == 'S')) {
            $coBfor = 'si';
            if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX' && RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX != 'S') {
                $coBfor = 'no';
            }
            if ($_SESSION["entrada"]["idusuario"] != 'USUPUBXX' && RENOVACION_LIQUIDAR_FORMULARIOS != 'S') {
                $coBfor = 'no';
            }
            if ($coBfor == 'si') {
                if ($cantfor == 0 || $cantfor == 1 || $cantfor == 2) {
                    $cantfor = 1;
                } else {
                    $cantfor = $cantfor - 1;
                }

                $liq = array();
                $liq["servicio"] = RENOVACION_SERV_FORMULARIOS;
                if (substr($exp["matricula"], 0, 1) == 'S') {
                    if (defined('RENOVACION_SERV_FORMULARIOS_ESADL') && RENOVACION_SERV_FORMULARIOS_ESADL != '') {
                        $liq["servicio"] = RENOVACION_SERV_FORMULARIOS_ESADL;
                    }
                }
                $liq["cc"] = CODIGO_EMPRESA;
                $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
                $liq["matricula"] = $exp["matricula"];
                $liq["nmatricula"] = $exp["razonsocial"];
                $liq["anorenovar"] = '';
                $liq["cantidad"] = $cantfor;
                $liq["activos"] = 0;
                $paseactivos = $actprop;
                if (isset($aactprop[$liq["anorenovar"]])) {
                    $paseactivos = $aactprop[$liq["anorenovar"]];
                }
                if ($exp["ctrafiliacion"] == '1') {
                    if (!defined('RENOVACION_COBRAR_FORMULARIOS_AFILIADOS')) {
                        define('RENOVACION_COBRAR_FORMULARIOS_AFILIADOS', 'S');
                    }
                    if (RENOVACION_COBRAR_FORMULARIOS_AFILIADOS == 'S') {
                        $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $paseactivos) * $cantfor;
                    } else {
                        $liq["valor"] = 0;
                    }
                } else {
                    $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $paseactivos) * $cantfor;
                }
                $liq["nservicio"] = $serv["nombre"];
                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
            }
        }

        if ($esreliquidacion == 'si') {
            if (RENOVACION_RELIQUIDACION_COMO_MUTACION == 'S') {
                $liq = array();
                $liq["servicio"] = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_15;
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $prim . "'");
                if ($exp["ciiu1"] == 'R9200' || $exp["ciiu2"] == 'R9200' || $exp["ciiu3"] == 'R9200' || $exp["ciiu4"] == 'R9200') {
                    $liq["servicio"] = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_22;
                }
                if ($exp["organizacion"] == '12' && $exp["categoria"] == '1') {
                    $liq["servicio"] = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_51;
                }
                if ($exp["organizacion"] == '14' && $exp["categoria"] == '1') {
                    $liq["servicio"] = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_53;
                }
                $liq["cc"] = CODIGO_EMPRESA;
                $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $liq["servicio"] . "'");
                $liq["matricula"] = $prim;
                $liq["nmatricula"] = $exp["razonsocial"];
                $liq["anorenovar"] = '';
                $liq["cantidad"] = 1;
                $liq["activos"] = 0;
                $paseactivos = $actprop;
                if (isset($aactprop[$liq["anorenovar"]])) {
                    $paseactivos = $aactprop[$liq["anorenovar"]];
                }
                $liq["valor"] = \funcionesRegistrales::buscaTarifa($mysqli, $liq["servicio"], $liq["anorenovar"], 1, $liq["activos"], 'tarifa', $paseactivos) * $cantfor;
                $liq["nservicio"] = $serv["nombre"];
                $_SESSION["jsonsalida"]["liquidacion"][] = $liq;
            }
        }

        $alertaid = 0;
        $alertaservicio = '';
        $alertavalor = 0;

        // ***************************************************************************** //
        // Crea la liquidacion en mreg_liquidacion
        // ***************************************************************************** //
        // Datos básicos de la liquidación
        $nuevaliq = 'no';
        if (!isset($_SESSION["entrada"]["idliquidacion"])) {
            $nuevaliq = 'si';
        } else {
            if ($_SESSION["entrada"]["idliquidacion"] == "0" || $_SESSION["entrada"]["idliquidacion"] == "") {
                $nuevaliq = 'si';
            } else {
                $aliq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
                if ($aliq === false || empty($aliq)) {
                    $nuevaliq = 'si';
                } else {
                    if ($aliq["idestado"] != '01') {
                        $nuevaliq = 'si';
                    }
                }
            }
        }

        // 
        $xtmats = renovandoTodasLocal($matriculasIncluidas, $esreliquidacion);
        if ($xtmats !== 'si') {
            // $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.ren.todas', '', '', '', '', $xtmats);
            $menerror = 'Debe renovar todas las matrículas';
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $menerror;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($nuevaliq == 'si') {
            $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');
            if ($_SESSION["tramite"] === false) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando liquidacion (VC): ' . $_SESSION["generales"]["mensajeerror"];
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["tramite"]["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
            $_SESSION["tramite"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
            $_SESSION["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
            $_SESSION["tramite"]["fecha"] = date("Ymd");
            $_SESSION["tramite"]["hora"] = date("H:i:s");
            $_SESSION["tramite"]["idestado"] = '01';
            $_SESSION["tramite"]["iptramite"] = '';
            $_SESSION["tramite"]["tipotramite"] = 'renovacionmatricula';
            if (substr($exp["matricula"], 0, 1) == 'S') {
                $_SESSION["tramite"]["tipotramite"] = 'renovacionesadl';
            }
        } else {
            $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        }

        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matriculabase . "'");
        if ($exp && !empty($exp)) {
            if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                $tipoidentificacionbase = $exp["idclase"];
                $identificacionbase = $exp["numid"];
                $nombrebase = $exp["razonsocial"];
                $nombre1base = $exp["nombre1"];
                $nombre2base = $exp["nombre2"];
                $apellido1base = $exp["apellido1"];
                $apellido2base = $exp["apellido2"];
                $organizacionbase = $exp["organizacion"];
                $categoriabase = $exp["categoria"];
                $tipoidentificacioncliente = $exp["idclase"];
                $identificacioncliente = $exp["numid"];
                $razonsocialcliente = $exp["razonsocial"];
                $nombre1cliente = $exp["nombre1"];
                $nombre2cliente = $exp["nombre2"];
                $apellido1cliente = $exp["apellido1"];
                $apellido2cliente = $exp["apellido2"];
                $emailcliente = $exp["emailcom"];
                $dircomcliente = $exp["dircom"];
                $telcom1cliente = $exp["telcom1"];
                $telcom2cliente = $exp["telcom2"];
                $muncomcliente = $exp["muncom"];
            }
            if ($exp["organizacion"] == '02') {
                $tipoidentificacionbase = '';
                $identificacionbase = '';
                $nombrebase = $exp["razonsocial"];
                $nombre1base = '';
                $nombre2base = '';
                $apellido1base = '';
                $apellido2base = '';
                $organizacionbase = $exp["organizacion"];
                $categoriabase = '0';
                $prop = retornarRegistroMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $matriculabase . "' and estado = 'V'");
                if ($prop && !empty($prop)) {
                    if ($prop["matriculapropietario"] != '' && ($prop["codigocamara"] == '' || $prop["codigocamara"] == $_SESSION["generales"]["codigoempresa"])) {
                        $prop1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $prop["matriculapropietario"] . "'");
                        if ($prop1 && !empty($prop1)) {
                            $tipoidentificacioncliente = $prop1["idclase"];
                            $identificacioncliente = $prop1["numid"];
                            $razonsocialcliente = $prop1["razonsocial"];
                            $nombre1cliente = $prop1["nombre1"];
                            $nombre2cliente = $prop1["nombre2"];
                            $apellido1cliente = $prop1["apellido1"];
                            $apellido2cliente = $prop1["apellido2"];
                            $emailcliente = $prop1["emailcom"];
                            $dircomcliente = $prop1["dircom"];
                            $telcom1cliente = $prop1["telcom1"];
                            $telcom2cliente = $prop1["telcom2"];
                            $muncomcliente = $prop1["muncom"];
                        }
                    } else {
                        $tipoidentificacioncliente = $prop["tipoidentificacion"];
                        $identificacioncliente = $prop["identificacion"];
                        $razonsocialcliente = $prop["razonsocial"];
                        $nombre1cliente = $prop["nombre1"];
                        $nombre2cliente = $prop["nombre2"];
                        $apellido1cliente = $prop["apellido1"];
                        $apellido2cliente = $prop["apellido2"];
                        $emailcliente = $prop["emailcom"];
                        $dircomcliente = $prop["dircom"];
                        $telcom1cliente = $prop["telcom1"];
                        $telcom2cliente = $prop["telcom2"];
                        $muncomcliente = $prop["muncom"];
                    }
                }
            }
            if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                $tipoidentificacionbase = '';
                $identificacionbase = '';
                $nombrebase = $exp["razonsocial"];
                $nombre1base = '';
                $nombre2base = '';
                $apellido1base = '';
                $apellido2base = '';
                $organizacionbase = $exp["organizacion"];
                $categoriabase = $exp["categoria"];
                if ($exp["cpcodcam"] == $_SESSION["generales"]["codigoempresa"] && $exp["cpnummat"] != '') {
                    $prop = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $exp["cpnummat"] . "'");
                    if ($prop && !empty($prop)) {
                        $tipoidentificacioncliente = $prop["idclase"];
                        $identificacioncliente = $prop["numid"];
                        $razonsocialcliente = $prop["razonsocial"];
                        $nombre1cliente = $prop["nombre1"];
                        $nombre2cliente = $prop["nombre2"];
                        $apellido1cliente = $prop["apellido1"];
                        $apellido2cliente = $prop["apellido2"];
                        $emailcliente = $prop1["emailcom"];
                        $dircomcliente = $prop1["dircom"];
                        $telcom1cliente = $prop1["telcom1"];
                        $telcom2cliente = $prop1["telcom2"];
                        $muncomcliente = $prop1["muncom"];
                    }
                } else {
                    $tipoidentificacioncliente = '2';
                    $identificacioncliente = $exp["cpnumnit"];
                    $razonsocialcliente = $exp["cprazsoc"];
                    $nombre1cliente = '';
                    $nombre2cliente = '';
                    $apellido1cliente = '';
                    $apellido2cliente = '';
                    $emailcliente = '';
                    $dircomcliente = '';
                    $telcom1cliente = '';
                    $telcom2cliente = '';
                    $muncomcliente = '';
                }
            }
        }
        $_SESSION["tramite"]["sede"] = $sedeusuario;
        $_SESSION["tramite"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["tramite"]["matriculabase"] = $matriculabase;
        $_SESSION["tramite"]["idmatriculabase"] = $matriculabase;
        $_SESSION["tramite"]["idexpedientebase"] = $matriculabase;
        $_SESSION["tramite"]["nombrebase"] = $nombrebase;
        $_SESSION["tramite"]["nom1base"] = $nombre1base;
        $_SESSION["tramite"]["nom2base"] = $nombre2base;
        $_SESSION["tramite"]["ape1base"] = $apellido1base;
        $_SESSION["tramite"]["ape2base"] = $apellido2base;
        $_SESSION["tramite"]["tipoidentificacionbase"] = $tipoidentificacionbase;
        $_SESSION["tramite"]["identificacionbase"] = $identificacionbase;
        $_SESSION["tramite"]["organizacionbase"] = $organizacionbase;
        $_SESSION["tramite"]["categoriabase"] = $categoriabase;
        $_SESSION["tramite"]["incluirformularios"] = '';
        $_SESSION["tramite"]["incluircertificados"] = '';
        $_SESSION["tramite"]["pedirbalance"] = $_SESSION["jsonsalida"]["pedirbalance"];

        if (isset($_SESSION["entrada"]["incluirformulario"])) {
            $_SESSION["tramite"]["incluirformularios"] = $_SESSION["entrada"]["incluirformulario"];
        }
        if (isset($_SESSION["entrada"]["incluircertificado"])) {
            $_SESSION["tramite"]["incluircertificados"] = $_SESSION["entrada"]["incluircertificado"];
        }

        //
        if (isset($_SESSION["entrada"]["sistemacreacion"]) && $_SESSION["entrada"]["sistemacreacion"] != '') {
            $_SESSION["tramite"]["sistemacreacion"] = $_SESSION["entrada"]["sistemacreacion"];
        } else {
            $_SESSION["tramite"]["sistemacreacion"] = 'API - liquidarRenovacionMultiplesAnios - ' . $_SESSION["entrada"]["usuariows"];
        }

        // Datos del cliente
        $_SESSION["tramite"]["tipocliente"] = '';
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $tipoidentificacioncliente;
        $_SESSION["tramite"]["identificacioncliente"] = $identificacioncliente;
        $_SESSION["tramite"]["razonsocialcliente"] = $razonsocialcliente;
        $_SESSION["tramite"]["nombrecliente"] = $razonsocialcliente;
        $_SESSION["tramite"]["apellidocliente"] = trim($apellido1cliente . ' ' . $apellido2cliente);
        $_SESSION["tramite"]["apellido1cliente"] = $apellido1cliente;
        $_SESSION["tramite"]["apellido2cliente"] = $apellido2cliente;
        $_SESSION["tramite"]["nombre1cliente"] = $nombre1cliente;
        $_SESSION["tramite"]["nombre2cliente"] = $nombre2cliente;
        $_SESSION["tramite"]["email"] = $emailcliente;
        $_SESSION["tramite"]["direccion"] = $dircomcliente;
        $_SESSION["tramite"]["telefono"] = $telcom1cliente;
        $_SESSION["tramite"]["movil"] = $telcom2cliente;
        $_SESSION["tramite"]["idmunicipio"] = $muncomcliente;

        // Datos del pagador
        $_SESSION["tramite"]["tipopagador"] = '';
        $_SESSION["tramite"]["tipoidentificacionpagador"] = $exp["idclase"];
        $_SESSION["tramite"]["identificacionpagador"] = $exp["numid"];
        $_SESSION["tramite"]["razonsocialpagador"] = $exp["razonsocial"];
        $_SESSION["tramite"]["apellido1pagador"] = $exp["apellido1"];
        $_SESSION["tramite"]["apellido2pagador"] = $exp["apellido2"];
        $_SESSION["tramite"]["nombre1pagador"] = $exp["nombre1"];
        $_SESSION["tramite"]["nombre2pagador"] = $exp["nombre2"];
        $_SESSION["tramite"]["emailpagador"] = $exp["emailcom"];
        $_SESSION["tramite"]["direccionpagador"] = $exp["dircom"];
        $_SESSION["tramite"]["telefonopagador"] = $exp["telcom1"];
        $_SESSION["tramite"]["movilpagador"] = $exp["telcom2"];
        $_SESSION["tramite"]["municipiopagador"] = $exp["muncom"];
        $_SESSION["tramite"]["valorbruto"] = 0;
        $_SESSION["tramite"]["valorbaseiva"] = 0;
        $_SESSION["tramite"]["valoriva"] = 0;
        $_SESSION["tramite"]["valortotal"] = 0;
        $_SESSION["tramite"]["reliquidacion"] = $esreliquidacion;
        $_SESSION["tramite"]["liquidacion"] = array();
        $_SESSION["tramite"]["expedientes"] = array();
        $_SESSION["tramite"]["alertaid"] = $alertaid;
        $_SESSION["tramite"]["alertaservicio"] = $alertaservicio;
        $_SESSION["tramite"]["alertavalor"] = $alertavalor;

        $_SESSION["tramite"]["cumplorequisitosbenley1780"] = $_SESSION["entrada"]["cumple1780"];
        $_SESSION["tramite"]["mantengorequisitosbenley1780"] = $_SESSION["entrada"]["mantiene1780"];
        $_SESSION["tramite"]["renunciobeneficiosley1780"] = $_SESSION["entrada"]["renuncia1780"];

        // Arreglo de liquidacion
        $i = 0;
        foreach ($_SESSION["jsonsalida"]["liquidacion"] as $lliq) {
            if (isset($lliq["servicio"]) && $lliq["servicio"] != '') {
                $i++;
                $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = '000';
                $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $lliq["servicio"];
                $_SESSION["tramite"]["liquidacion"][$i]["cc"] = $lliq["cc"];
                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $lliq["matricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $lliq["nmatricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["ano"] = $lliq["anorenovar"];
                $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $lliq["cantidad"];
                $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $lliq["activos"];
                $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = 0;
                $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $lliq["valor"];
                $_SESSION["tramite"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"] + $lliq["valor"];
                $_SESSION["tramite"]["valortotal"] = $_SESSION["tramite"]["valortotal"] + $lliq["valor"];
            }
        }

        // Arreglo de expedientes
        $mcont = '';
        $lin = array();
        $mats2 = array();
        foreach ($matexp as $m => $a) {
            list ($mat1, $ano1) = explode("-", $m);
            list ($act1, $per1, $prot1) = explode("|", $a);
            if (!isset($mats2[$mat1])) {
                $mats2[$mat1] = array();
                $mats2[$mat1]["matricula"] = $mat1;
                $mats2[$mat1]["inicial"] = '';
                $mats2[$mat1]["final"] = '';
            }
            if ($mats2[$mat1]["inicial"] == '') {
                $mats2[$mat1]["inicial"] = $ano1;
            }
            $mats2[$mat1]["final"] = $ano1;
        }

        foreach ($mats2 as $mx) {
            foreach ($matexp as $m => $a) {
                list ($mat1, $ano1) = explode("-", $m);
                list ($act1, $per1, $prot1) = explode("|", $a);
                if ($mat1 == $mx["matricula"]) {
                    $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat1 . "'");
                    $lin["cc"] = CODIGO_EMPRESA;
                    $lin["matricula"] = $exp["matricula"];
                    $lin["proponente"] = '';
                    $lin["numrue"] = $exp["nit"];
                    $lin["idtipoidentificacion"] = $exp["idclase"];
                    $lin["identificacion"] = $exp["numid"];
                    $lin["razonsocial"] = $exp["razonsocial"];
                    $lin["ape1"] = $exp["apellido1"];
                    $lin["ape2"] = $exp["apellido2"];
                    $lin["nom1"] = $exp["nombre1"];
                    $lin["nom2"] = $exp["nombre2"];
                    $lin["organizacion"] = $exp["organizacion"];
                    $lin["categoria"] = $exp["categoria"];
                    $lin["afiliado"] = $exp["ctrafiliacion"];
                    $lin["propietariojurisdiccion"] = '';
                    if ($ano1 == $mx["final"]) {
                        $lin["primeranorenovado"] = $mx["inicial"];
                        $lin["registrobase"] = 'S';
                    } else {
                        $lin["primeranorenovado"] = $ano1;
                        $lin["registrobase"] = 'N';
                    }
                    $lin["ultimoanorenovado"] = $ano1;
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        $lin["ultimosactivos"] = $exp["acttot"];
                    } else {
                        $lin["ultimosactivos"] = $exp["actvin"];
                    }
                    $lin["nuevosactivos"] = $act1;
                    $lin["personal"] = $per1;
                    $lin["actividad"] = '';
                    $lin["benart7"] = $exp["ctrbenart7"];
                    if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                        $lin["benley1780"] = $exp["ctrbenley1780"];
                    } else {
                        $lin["benley1780"] = '';
                    }
                    $lin["renovaresteano"] = 'si';
                    $lin["fechanacimiento"] = $exp["fechanacimiento"];
                    $lin["fechamatricula"] = $exp["fecmatricula"];
                    $lin["fecmatant"] = '';
                    $lin["reliquidacion"] = '';
                    $lin["controlpot"] = '';
                    $lin["dircom"] = $exp["dircom"];
                    $lin["muncom"] = $exp["muncom"];
                    $lin["valor"] = 0;
                    $lin["protegeractivos"] = $prot1;
                    $_SESSION["jsonsalida"]["expedientes"][] = $lin;
                }
            }
        }

        //
        $i = 0;
        foreach ($_SESSION["jsonsalida"]["expedientes"] as $lexp) {
            $i++;
            $_SESSION["tramite"]["expedientes"][$i] = $lexp;
        }
        unset($_SESSION["jsonsalida"]["expedientes"]);

        //
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["jsonsalida"]["emailcontrol"];
        $_SESSION["tramite"]["tramitepresencial"] = '1'; // Inicio virtual
        // 
        $datefinalliq = date("Ymd") . ' ' . date("His");
        $_SESSION["tramite"]["horainicioliquidacion"] = $dateinicioliq;
        $_SESSION["tramite"]["horafinalliquidacion"] = $datefinalliq;

        //
        $res = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error creando liquidacion : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
        $_SESSION["jsonsalida"]["reliquidacion"] = $esreliquidacion;
        $_SESSION["jsonsalida"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"];
        $_SESSION["jsonsalida"]["valorbaseiva"] = $_SESSION["tramite"]["valorbaseiva"];
        $_SESSION["jsonsalida"]["valoriva"] = $_SESSION["tramite"]["valoriva"];
        $_SESSION["jsonsalida"]["valortotal"] = $_SESSION["tramite"]["valortotal"];
        $_SESSION["jsonsalida"]["alertaid"] = $alertaid;
        $_SESSION["jsonsalida"]["alertaservicio"] = $alertaservicio;
        $_SESSION["jsonsalida"]["alertavalor"] = $alertavalor;

        //
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["jsonsalida"]["incluirafiliacion"] = $_SESSION["entrada"]["incluirafiliacion"];
        $_SESSION["jsonsalida"]["incluircertificado"] = $_SESSION["entrada"]["incluircertificado"];
        $_SESSION["jsonsalida"]["incluirformulario"] = $_SESSION["entrada"]["incluirformulario"];
        $_SESSION["jsonsalida"]["cumple1780"] = $_SESSION["entrada"]["cumple1780"];
        $_SESSION["jsonsalida"]["mantiene1780"] = $_SESSION["entrada"]["mantiene1780"];
        $_SESSION["jsonsalida"]["renuncia1780"] = $_SESSION["entrada"]["renuncia1780"];

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::general2($nameLog, '', 'Response: ' . json_encode($_SESSION["jsonsalida"]));
        \logApi::general2($nameLog, '', '');

        //       
        ob_clean();
        $json1 = json_encode($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json1), 200);
    }

    public function retornarListaMatriculasRenovar(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $resError = set_error_handler('myErrorHandler');
        $nameLog = 'retornarListaMatriculasRenovar_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("matriculabase", false);
        $api->validarParametro("identificacionbase", false);
        $api->validarParametro("procesartodas", false);

        // *************************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // Utiliza los mismos permisos que el método liquidarRenovacionMultiplesAnios
        // *************************************************************************** // 
        if (!$api->validarToken('liquidarRenovacionMultiplesAnios', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // Valida matricula e identificacion
        if (!isset($_SESSION["entrada"]["matriculabase"])) {
            $_SESSION["entrada"]["matriculabase"] = '';
        }
        if (!isset($_SESSION["entrada"]["identificacionbase"])) {
            $_SESSION["entrada"]["identificacionbase"] = '';
        }
        if ($_SESSION["entrada"]["matriculabase"] == '' && $_SESSION["entrada"]["identificacionbase"] == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar al menos una matrícula o una identificación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // valida el parámetro procesartodas
        // SP.- solo el propietario
        // N.- Solo la matrícula seleccionada
        // L.- El propietario y sus matrículas en la jurisdicción
        // E.- Solo los establecimientos locales
        // S.- El propietario y sus establecimientos a nivel nacional (locales y foraneos)
        // X.- Establecimientos nacionales.
        if (!isset($_SESSION["entrada"]["procesartodas"]) || $_SESSION["entrada"]["procesartodas"] == '') {
            $_SESSION["entrada"]["procesartodas"] = 'L';
        }
        $_SESSION["entrada"]["procesartodas"] = strtoupper($_SESSION["entrada"]["procesartodas"]);
        if ($_SESSION["entrada"]["procesartodas"] != 'SP' &&
                $_SESSION["entrada"]["procesartodas"] != 'N' &&
                $_SESSION["entrada"]["procesartodas"] != 'L' &&
                $_SESSION["entrada"]["procesartodas"] != 'E' &&
                $_SESSION["entrada"]["procesartodas"] != 'S' &&
                $_SESSION["entrada"]["procesartodas"] != 'X'
        ) {
            ob_clean();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El parámetro procesartodas enviado en forma incorrecta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        $arrMatriculas = \funcionesRegistrales::retornarListaMatriculasRenovarNuevo($mysqli, $_SESSION ["entrada"] ["matriculabase"], $_SESSION ["entrada"] ["identificacionbase"], $_SESSION ["entrada"] ["procesartodas"], $_SESSION ["entrada"] ["idusuario"]);
        $mysqli->close();
        if ($arrMatriculas["codigoerror"] !== '0000') {
            ob_clean();
            $_SESSION["jsonsalida"]["codigoerror"] = $arrMatriculas["codigoerror"];
            $_SESSION["jsonsalida"]["mensajeerror"] = $arrMatriculas["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        foreach ($arrMatriculas as $key => $valor) {
            $_SESSION["jsonsalida"][$key] = $valor;
        }

        //
        ob_clean();
        $json1 = json_encode($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json1), 200);
    }

    public function validarActivosLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $resError = set_error_handler('myErrorHandler');
        $nameLog = 'validarActivosLiquidacion_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = "";
        $_SESSION["jsonsalida"]["matriculapropietario"] = '';
        $_SESSION["jsonsalida"]["matriculasestablecimientos"] = '';
        $_SESSION["jsonsalida"]["activospropietario"] = 0;
        $_SESSION["jsonsalida"]["sumatoriaactivosestablecimientos"] = 0;

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

        // *************************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // Utiliza los mismos permisos que el método validarActivosLiquidacion
        // *************************************************************************** // 
        if (!$api->validarToken('validarActivosLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        $forms = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"], "secuencia");
        if ($forms && !empty($forms)) {
            foreach ($forms as $f) {
                $dat = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $f["xml"]);
                if ($dat["organizacion"] == '01' || ($dat["organizacion"] > '02' && $dat["categoria"] == '1')) {
                    $_SESSION["jsonsalida"]["activospropietario"] = $_SESSION["jsonsalida"]["activospropietario"] + $dat["acttot"];
                    $_SESSION["jsonsalida"]["matriculapropietario"] = $dat["matricula"];
                } else {
                    $_SESSION["jsonsalida"]["sumatoriaactivosestablecimientos"] = $_SESSION["jsonsalida"]["sumatoriaactivosestablecimientos"] + $dat["actvin"];
                    if ($_SESSION["jsonsalida"]["matriculasestablecimientos"] != '') {
                        $_SESSION["jsonsalida"]["matriculasestablecimientos"] .= ',';
                    }
                    $_SESSION["jsonsalida"]["matriculasestablecimientos"] .= $dat["matricula"];
                }
            }
        }
        if ($_SESSION["jsonsalida"]["matriculasestablecimientos"] != '') {
            if ($_SESSION["jsonsalida"]["matriculapropietario"] != '') {
                if ($_SESSION["jsonsalida"]["sumatoriaactivosestablecimientos"] > $_SESSION["jsonsalida"]["activospropietario"]) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Se ha detectado que la sumatoria de los activos vinculados a (los) establecimiento(s) es superior al valor de los activos del propietario, ';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'esto se considera una inconsistencia en la data que está reportando al registro. Por favor revise y corrija para poder continuar.';
                }
            }
            if ($_SESSION["jsonsalida"]["matriculapropietario"] == '') {
                $matprop = '';
                $canprop = 0;
                $ests = explode(",", $_SESSION["jsonsalida"]["matriculasestablecimientos"]);
                if (isset($ests[0])) {
                    $pests = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $ests[0] . "'", "id");
                    if ($pests && !empty($pests)) {
                        foreach ($pests as $p) {
                            if ($p["estado"] == 'V') {
                                $canprop++;
                                if ($p["codigocamara"] == CODIGO_EMPRESA && $p["matriculapropietario"] != '') {
                                    if ($matprop == '') {
                                        $matprop = $p["matriculapropietario"];
                                    }
                                }
                            }
                        }
                    }
                }
                if ($canprop == 1 && $matprop != '') {
                    $_SESSION["jsonsalida"]["activospropietario"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matprop . "'", "acttot");
                    if ($_SESSION["jsonsalida"]["sumatoriaactivosestablecimientos"] > $_SESSION["jsonsalida"]["activospropietario"]) {
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Se ha detectado que la SUMA de los activos vinculados a (los) establecimiento(s) y que se digitó en los formularios, es superior al valor de los activos del propietario, ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'esto se considera una inconsistencia en la data que está reportando al registro. Por favor revise los formularios de los establecimientos de comercio, sucursales y agencias y corrija para poder continuar.';
                    }
                }
            }
        }

        //
        $mysqli->close();

        ob_clean();
        $json1 = json_encode($_SESSION["jsonsalida"]);
        \logApi::general2($nameLog, '', $json1);
        $api->response(str_replace("\\/", "/", $json1), 200);
    }

    public function ubicacionCartulina(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $resError = set_error_handler('myErrorHandler');
        $nameLog = 'ubicacionCartulina_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = "";
        $_SESSION["jsonsalida"]["coordenadasid"] = '';
        $_SESSION["jsonsalida"]["coordenadasmatricula"] = '';
        $_SESSION["jsonsalida"]["coordenadasnombre"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);

        // *************************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // Utiliza los mismos permisos que el método validarActivosLiquidacion
        // *************************************************************************** // 
        if (!$api->validarToken('ubicacionCartulina', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["jsonsalida"]["coordenadasid"] = '39,195';
        $_SESSION["jsonsalida"]["coordenadasmatricula"] = '100,45';
        $_SESSION["jsonsalida"]["coordenadasnombre"] = '113,45';

        if (defined('CARTULINA_LIN_COL_NUMREC') && CARTULINA_LIN_COL_NUMREC != '') {
            $_SESSION["jsonsalida"]["coordenadasid"] = CARTULINA_LIN_COL_NUMREC;
        }
        if (defined('CARTULINA_LIN_COL_MATRICULA') && CARTULINA_LIN_COL_MATRICULA != '') {
            $_SESSION["jsonsalida"]["coordenadasmatricula"] = CARTULINA_LIN_COL_MATRICULA;
        }
        if (defined('CARTULINA_LIN_COL_NOMBRE') && CARTULINA_LIN_COL_NOMBRE != '') {
            $_SESSION["jsonsalida"]["coordenadasnombre"] = CARTULINA_LIN_COL_NOMBRE;
        }

        ob_clean();
        $json1 = json_encode($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json1), 200);
    }

    public function generarCartulina(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsCartulinas.php');
        $resError = set_error_handler('myErrorHandler');
        $nameLog = 'ubicacionCartulina_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = "";
        $_SESSION["jsonsalida"]["link"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", true);

        // *************************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // Utiliza los mismos permisos que el método validarActivosLiquidacion
        // *************************************************************************** // 
        if (!$api->validarToken('generacionCartulina', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!file_exists(PATH_ABSOLUTO_SITIO . '/images/cartulina' . $_SESSION["generales"]["codigoempresa"] . '-' . date("Y") . '.jpg')) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No existe cartulina habilitada para esta Cámara / año';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
        if ($exp === false || empty($exp)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
            if ($exp["ultanoren"] != date("Y")) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no se encuentra al día con su renovación';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $name = armarCartulinaValledupar($mysqli, $_SESSION["entrada"]["matricula"]);
                $_SESSION["jsonsalida"]["link"] = TIPO_HTTP . HTTP_HOST . '/tmp/' . $name;
            }
        } else {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no es un establecimiento sucursal o agencia';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        ob_clean();
        $json1 = json_encode($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json1), 200);
    }
}

function estaDisueltaLocal($mysqli, $exp, $mx1, $nameLog = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

    $retornar = true;
    if ($exp["estadisuelta"] == 'si') {
        $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $mx1["anorenovar"] . "'", "corte");
        if (substr($exp["fechadisolucion"], 0, 4) > $mx1["anorenovar"]) {
            $retornar = false;
        }
        if (substr($exp["fechadisolucion"], 0, 4) == $mx1["anorenovar"]) {
            if ($exp["fechadisolucion"] > $fcorte1) {
                $retornar = false;
            }
        }
    } else {
        $finidis = '';
        $ffindis = '';
        foreach ($exp["inscripciones"] as $insc1) {
            if ($insc1["acto"] == '0510') {
                $finidis = $insc1["freg"];
                $ffindis = '';
            }
            if ($insc1["acto"] == '0511') {
                $ffindis = $insc1["freg"];
            }
        }
        if ($finidis == '' && $ffindis == '') {
            $retornar = false;
        } else {
            $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $mx1["anorenovar"] . "'", "corte");
            if ($finidis == '' && $ffindis != '') {
                $finidis = $exp["fechavencimiento1"];
            }
            if ($finidis == '' && $ffindis != '') {
                if ($ffindis < $fcorte1) {
                    $retornar = false;
                }
            }
            if ($finidis != '' && $ffindis == '') {
                if ($mx1["anorenovar"] < substr($finidis, 0, 4) || (substr($finidis, 0, 4) == $mx1["anorenovar"] && $finidis > $fcorte1)) {
                    $retornar = false;
                }
            }
            if ($finidis != '' && $ffindis != '') {
                if ($mx1["anorenovar"] < substr($finidis, 0, 4) || (substr($finidis, 0, 4) == $mx1["anorenovar"] && $finidis > $fcorte1)) {
                    $retornar = false;
                }
                if ($mx1["anorenovar"] > substr($ffindis, 0, 4) || (substr($ffindis, 0, 4) == $mx1["anorenovar"])) {
                    $retornar = false;
                }
            }
        }
    }
    $xretornar = '';
    if ($retornar === true) {
        $xretornar = 'si';
    }
    if ($retornar === false) {
        $xretornar = 'no';
    }
    if ($nameLog != '') {
        \logApi::general2($nameLog, '', 'Esta disuelta - ' . $mx1["matricula"] . ' - ' . $mx1["anorenovar"] . ' - ' . $xretornar);
    }
    return $retornar;
}

function estaCesadaActividadLocal($mysqli, $exp, $mx1, $nameLog = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    $retornar = false;
    $ca = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $mx1["matricula"] . "' and acto IN ('0580','0581')", "fecharegistro,horaregistro");
    if ($ca && !empty($ca)) {
        foreach ($ca as $ca1) {
            if (substr($ca1["fecharegistro"], 0, 4) <= $mx1["anorenovar"]) {
                if ($ca1["acto"] == '0580') {
                    $cafeini = $ca1["fecharegistro"];
                }
                if ($ca1["acto"] == '0581') {
                    if (substr($ca1["fecharegistro"], 0, 4) < $mx1["anorenovar"]) {
                        $cafeini = '';
                        $cafefin = '';
                    } else {
                        $cafefin = $ca1["fecharegistro"];
                    }
                }
            }
        }
        if ($cafeini != '') {
            if ($cafefin == '') {
                if ($mx1["anorenovar"] > substr($cafeini, 0, 4)) {
                    $retornar = true;
                }
                if ($mx1["anorenovar"] == substr($cafeini, 0, 4)) {
                    $fecorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . substr($cafeini, 0, 4) . "'");
                    if ($cafeini <= $fecorte["corte"]) {
                        $retornar = true;
                    }
                }
            } else {
                if ($mx1["anorenovar"] > substr($cafeini, 0, 4)) {
                    if ($mx1["anorenovar"] < substr($cafefin, 0, 4)) {
                        $retornar = true;
                    }
                    if ($mx1["anorenovar"] >= substr($cafefin, 0, 4)) {
                        $cafeini = '';
                        $cafefin = '';
                    }
                }
                if ($mx1["anorenovar"] == substr($cafeini, 0, 4)) {
                    $fecorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . substr($cafeini, 0, 4) . "'");
                    if ($cafeini <= $fecorte["corte"]) {
                        $retornar = true;
                    }
                }
            }
        }
    }
    if ($nameLog != '') {
        \logApi::general2($nameLog, '', 'Cesacion Activdad : ' . $mx1["matricula"] . ' - ' . $mx1["anorenovar"] . ' - ' . $retornar);
    }
    return $retornar;
}

function renovandoTodasLocal($matriculasIncluidas = array(), $esreliquidacion = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    $xtmats = '';
    if (defined('RENOVACION_OBLIGAR_TODAS_USUPUBXX') && RENOVACION_OBLIGAR_TODAS_USUPUBXX == 'S') {
        if ($esreliquidacion != 'si') {
            if ($_SESSION["entrada"]["idusuario"] === 'USUPUBXX') {
                $xtmats = '';
                foreach ($matriculasIncluidas as $matx) {
                    if ($matx["incluida"] == 'no') {
                        if ($xtmats != '') {
                            $xtmats .= ', ';
                        }
                        $xtmats .= $matx["matricula"];
                    } else {
                        if ($matx["anoren"] != date("Y")) {
                            if ($xtmats != '') {
                                $xtmats .= ', ';
                            }
                            $xtmats .= $matx["matricula"];
                        }
                    }
                }
            }
        }
    }
    if ($xtmats == '') {
        return "si";
    } else {
        return $xtmats;
    }
}
