<?php

namespace api;

use api\API;

trait mregValidaciones {

    public function mregValidacionesAsignarVariablesEscaneo(API $api) {

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
        $api->validarParametro("idanexo", true);
        $mysqli = conexionMysqliApi();
        $anxf = retornarRegistroMysqliApi($mysqli, 'mreg_radicacionesanexos', "idanexo=" . $_SESSION["entrada"]["idanexo"]);

        // Usado por el escaneador Nuevo
        $escaneo = "escaneo" . $_SESSION["vars"]["idanexo"];
        $_SESSION[$escaneo]["tipoarchivo"] = 'mreg';
        $_SESSION[$escaneo]["idanexo"] = $_SESSION["vars"]["idanexo"];
        $_SESSION[$escaneo]["idradicado"] = $anxf["idradicacion"];
        $_SESSION[$escaneo]["fecha"] = $anxf["fechaescaneo"];
        $_SESSION[$escaneo]["hora"] = '';
        $_SESSION[$escaneo]["destinatario"] = '';
        $_SESSION[$escaneo]["origen"] = $anxf["txtorigen"];
        $_SESSION[$escaneo]["tramite"] = '';
        $_SESSION[$escaneo]["organizacion"] = RAZONSOCIAL;
        $_SESSION[$escaneo]["matricula"] = $anxf["matricula"];
        $_SESSION[$escaneo]["proponente"] = $anxf["proponente"];
        $_SESSION[$escaneo]["identificacion"] = $anxf["identificacion"];
        $_SESSION[$escaneo]["recibo"] = $anxf["numerorecibo"];
        $_SESSION[$escaneo]["operacion"] = $anxf["numerooperacion"];
        $_SESSION[$escaneo]["expediente"] = '';
        $_SESSION[$escaneo]["nombre"] = $anxf["nombre"];
        $_SESSION[$escaneo]["archivooriginal"] = $anxf["path"];
        $_SESSION[$escaneo]["sistemaorigen"] = $anxf["sistemaorigen"];
        $_SESSION[$escaneo]["version"] = $anxf["version"];
        $escaneo = "escaneo";
        $_SESSION[$escaneo]["tipoarchivo"] = 'mreg';
        $_SESSION[$escaneo]["idanexo"] = $_SESSION["vars"]["idanexo"];
        $_SESSION[$escaneo]["idradicado"] = $anxf["idradicacion"];
        $_SESSION[$escaneo]["fecha"] = $anxf["fechaescaneo"];
        $_SESSION[$escaneo]["hora"] = '';
        $_SESSION[$escaneo]["destinatario"] = '';
        $_SESSION[$escaneo]["origen"] = $anxf["txtorigen"];
        $_SESSION[$escaneo]["tramite"] = '';
        $_SESSION[$escaneo]["organizacion"] = RAZONSOCIAL;
        $_SESSION[$escaneo]["matricula"] = $anxf["matricula"];
        $_SESSION[$escaneo]["proponente"] = $anxf["proponente"];
        $_SESSION[$escaneo]["identificacion"] = $anxf["identificacion"];
        $_SESSION[$escaneo]["recibo"] = $anxf["numerorecibo"];
        $_SESSION[$escaneo]["operacion"] = $anxf["numerooperacion"];
        $_SESSION[$escaneo]["expediente"] = '';
        $_SESSION[$escaneo]["nombre"] = $anxf["nombre"];
        $_SESSION[$escaneo]["archivooriginal"] = $anxf["path"];
        $_SESSION[$escaneo]["sistemaorigen"] = $anxf["sistemaorigen"];
        $_SESSION[$escaneo]["version"] = $anxf["version"];
        $mysqli->close();
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'VARIABLES ASIGNADAS';
        \logApi::peticionRest('api_' . __FUNCTION__);
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesConsultarMatricula(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $_SESSION["jsonsalida"]["matricula"] = $_SESSION["entrada"]["matricula"];
            $_SESSION["jsonsalida"]["identificacion"] = '';
            $_SESSION["jsonsalida"]["nombre"] = '';

            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("matricula", true);
        $mysqli = conexionMysqliApi();
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'");
        if ($res === false || empty($res)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Matrícula no localizada';
            $_SESSION["jsonsalida"]["matricula"] = $_SESSION["entrada"]["matricula"];
            $_SESSION["jsonsalida"]["identificacion"] = '';
            $_SESSION["jsonsalida"]["nombre"] = '';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $mysqli->close();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["matricula"] = $res["matricula"];
        $_SESSION["jsonsalida"]["identificacion"] = $res["numid"];
        $_SESSION["jsonsalida"]["nombre"] = $res["razonsocial"];
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesConsultarRecibo(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $_SESSION["jsonsalida"]["recibo"] = $_SESSION["entrada"]["recibo"];
            $_SESSION["jsonsalida"]["identificacion"] = '';
            $_SESSION["jsonsalida"]["nombre"] = '';

            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("recibo", true);
        $mysqli = conexionMysqliApi();
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $_SESSION["entrada"]["recibo"] . "'");
        if ($res === false || empty($res)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Recibo no localizado';
            $_SESSION["jsonsalida"]["recibo"] = $_SESSION["entrada"]["recibo"];
            $_SESSION["jsonsalida"]["identificacion"] = '';
            $_SESSION["jsonsalida"]["nombre"] = '';
            $_SESSION["jsonsalida"]["matricula"] = '';
            $_SESSION["jsonsalida"]["proponente"] = '';
            $_SESSION["jsonsalida"]["codigobarras"] = '';
            $_SESSION["jsonsalida"]["bandeja"] = '';
            $_SESSION["jsonsalida"]["fecha"] = '';
            $_SESSION["jsonsalida"]["operacion"] = '';
            $_SESSION["jsonsalida"]["origen"] = '';
            $_SESSION["jsonsalida"]["tipdoc"] = '';
            $_SESSION["jsonsalida"]["tipdocgen"] = '90.01.033';
            $_SESSION["jsonsalida"]["numdoc"] = '';
            $_SESSION["jsonsalida"]["anx"] = '501';
            $_SESSION["jsonsalida"]["libro"] = '';
            $_SESSION["jsonsalida"]["registro"] = '';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $det = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $_SESSION["entrada"]["recibo"] . "'", "*", "P");
        if ($res["codigobarras"] != '') {
            $cb = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $res["codigobarras"] . "'");
        } else {
            $cb = false;
        }
        if ($det["matricula"] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $det["matricula"] . "'", "numid,razonsocial");
        } else {
            $exp = false;
        }
        $mysqli->close();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = 'Recibo localizado';
        $_SESSION["jsonsalida"]["recibo"] = $_SESSION["entrada"]["recibo"];
        $_SESSION["jsonsalida"]["identificacion"] = $res["identificacion"];
        $_SESSION["jsonsalida"]["nombre"] = $res["razonsocial"];
        $_SESSION["jsonsalida"]["matricula"] = $det["matricula"];
        $_SESSION["jsonsalida"]["proponente"] = $det["proponente"];
        $_SESSION["jsonsalida"]["codigobarras"] = $res["codigobarras"];
        $_SESSION["jsonsalida"]["bandeja"] = '';
        if ($det["matricula"] != '' && substr($det["matricula"], 0, 1) == 'S') {
            $_SESSION["jsonsalida"]["bandeja"] = '5.-REGESADL';
        }
        if ($det["matricula"] != '' && substr($det["matricula"], 0, 1) != 'S') {
            $_SESSION["jsonsalida"]["bandeja"] = '4.-REGMER';
        }
        if ($det["proponente"] != '') {
            $_SESSION["jsonsalida"]["bandeja"] = '6.-REGPRO';
        }
        $_SESSION["jsonsalida"]["fecha"] = $res["fecha"];
        $_SESSION["jsonsalida"]["operacion"] = $res["operacion"];
        $_SESSION["jsonsalida"]["origen"] = '';
        if ($cb) {
            $_SESSION["jsonsalida"]["origen"] = $cb["oridoc"];
        }
        $_SESSION["jsonsalida"]["tipdocgen"] = '90.01.033';
        $_SESSION["jsonsalida"]["numdoc"] = $cb["numdoc"];
        $_SESSION["jsonsalida"]["anx"] = '501';
        $_SESSION["jsonsalida"]["libro"] = '';
        $_SESSION["jsonsalida"]["registro"] = '';
        if ($exp && !empty($exp)) {
            $_SESSION["jsonsalida"]["identificacion"] = $exp["numid"];
            $_SESSION["jsonsalida"]["nombre"] = $exp["razonsocial"];
        }
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesConsultarCodigoBarras(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $_SESSION["jsonsalida"]["recibo"] = '';
            $_SESSION["jsonsalida"]["identificacion"] = '';
            $_SESSION["jsonsalida"]["nombre"] = '';
            $_SESSION["jsonsalida"]["matricula"] = '';
            $_SESSION["jsonsalida"]["proponente"] = '';
            $_SESSION["jsonsalida"]["codigobarras"] = $_SESSION["entrada"]["codfigobarras"];
            $_SESSION["jsonsalida"]["bandeja"] = '';
            $_SESSION["jsonsalida"]["fecha"] = '';
            $_SESSION["jsonsalida"]["operacion"] = '';
            $_SESSION["jsonsalida"]["origen"] = '';
            $_SESSION["jsonsalida"]["tipdoc"] = '';
            $_SESSION["jsonsalida"]["numdoc"] = '';
            $_SESSION["jsonsalida"]["fecdoc"] = '';
            $_SESSION["jsonsalida"]["anx"] = '';
            $_SESSION["jsonsalida"]["libro"] = '';
            $_SESSION["jsonsalida"]["registro"] = '';

            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("codigobarras", true);
        $mysqli = conexionMysqliApi();
        $cb = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $_SESSION["entrada"]["codigobarras"] . "'");
        if ($cb === false || empty($cb)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Código de barras no localizado';
            $_SESSION["jsonsalida"]["recibo"] = $_SESSION["entrada"]["recibo"];
            $_SESSION["jsonsalida"]["identificacion"] = '';
            $_SESSION["jsonsalida"]["nombre"] = '';
            $_SESSION["jsonsalida"]["matricula"] = '';
            $_SESSION["jsonsalida"]["proponente"] = '';
            $_SESSION["jsonsalida"]["codigobarras"] = '';
            $_SESSION["jsonsalida"]["bandeja"] = '';
            $_SESSION["jsonsalida"]["fecha"] = '';
            $_SESSION["jsonsalida"]["operacion"] = '';
            $_SESSION["jsonsalida"]["origen"] = '';
            $_SESSION["jsonsalida"]["tipdoc"] = '';
            $_SESSION["jsonsalida"]["numdoc"] = '';
            $_SESSION["jsonsalida"]["fecdoc"] = '';
            $_SESSION["jsonsalida"]["anx"] = '';
            $_SESSION["jsonsalida"]["libro"] = '';
            $_SESSION["jsonsalida"]["registro"] = '';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $rec = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "codigobarras='" . $_SESSION["entrada"]["codigobarras"] . "'");
        $det = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $rec["recibo"] . "'", "*", "P");
        if ($det["matricula"] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $det["matricula"] . "'", "numid,razonsocial");
        } else {
            $exp = false;
        }
        $_SESSION["jsonsalida"]["registro"] = '';
        $mysqli->close();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = 'Recibo localizado';
        $_SESSION["jsonsalida"]["recibo"] = $rec["recibo"];
        $_SESSION["jsonsalida"]["identificacion"] = $rec["identificacion"];
        $_SESSION["jsonsalida"]["nombre"] = $rec["razonsocial"];
        $_SESSION["jsonsalida"]["matricula"] = $det["matricula"];
        $_SESSION["jsonsalida"]["proponente"] = $det["proponente"];
        $_SESSION["jsonsalida"]["codigobarras"] = $_SESSION["entrada"]["codigobarras"];
        $_SESSION["jsonsalida"]["bandeja"] = '';
        if ($det["matricula"] != '' && substr($det["matricula"], 0, 1) == 'S') {
            $_SESSION["jsonsalida"]["bandeja"] = '5.-REGESADL';
        }
        if ($det["matricula"] != '' && substr($det["matricula"], 0, 1) != 'S') {
            $_SESSION["jsonsalida"]["bandeja"] = '4.-REGMER';
        }
        if ($det["proponente"] != '') {
            $_SESSION["jsonsalida"]["bandeja"] = '6.-REGPRO';
        }
        $_SESSION["jsonsalida"]["fecha"] = $rec["fecha"];
        $_SESSION["jsonsalida"]["operacion"] = $rec["operacion"];
        $_SESSION["jsonsalida"]["origen"] = $cb["oridoc"];
        $_SESSION["jsonsalida"]["tipdoc"] = $cb["tipdoc"];
        $_SESSION["jsonsalida"]["tipdocgen"] = '90.01.033';
        $_SESSION["jsonsalida"]["numdoc"] = $cb["numdoc"];
        $_SESSION["jsonsalida"]["fecdoc"] = $cb["fecdoc"];
        $_SESSION["jsonsalida"]["anx"] = '';
        $_SESSION["jsonsalida"]["libro"] = '';
        $_SESSION["jsonsalida"]["registro"] = '';
        if ($exp && !empty($exp)) {
            $_SESSION["jsonsalida"]["identificacion"] = $exp["numid"];
            $_SESSION["jsonsalida"]["nombre"] = $exp["razonsocial"];
        }
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesGrabarSolicitudVerificado(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

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
        $api->validarParametro("_tipodoc", true);
        $api->validarParametro("_identificacion", true);
        $api->validarParametro("_primerapellido", true);
        $api->validarParametro("_segundoapellido", false);
        $api->validarParametro("_primernombre", true);
        $api->validarParametro("_otrosnombres", false);
        $api->validarParametro("_direccion", true);
        $api->validarParametro("_email", true);
        $api->validarParametro("_celular", true);
        $api->validarParametro("_fechaexpedicion", true);
        $api->validarParametro("_origen", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesGrabarSolicitudVerificado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesGrabarSolicitudVerificado ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $condicion = "email='" . $_SESSION["entrada"]["_email"] . "' and celular='" . $_SESSION["entrada"]["_celular"] . "' and identificacion='" . $_SESSION["entrada"]["_identificacion"] . "' and estado <> 'AN' and estado <> 'EL'";
        $arr = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', $condicion);
        if ($arr && !empty($arr)) {
            $estado = 'PENDIENTE';
            if ($arr["estado"] == 'VE') {
                $estado = 'VIGENTE';
            }
            if ($arr["estado"] == 'PE') {
                $estado = 'PENDIENTE';
            }
            if ($arr["estado"] == 'RZ') {
                $estado = 'RECHAZADA';
            }
            if ($arr["estado"] == 'IN') {
                $estado = 'INACTIVADA';
            }

            //
            if ($arr["estado"] == 'SF') {
                $estado = 'SIN_HISTORIAL_FINANCIERO';
            }

            $_SESSION["jsonsalida"] = array();
            $_SESSION["jsonsalida"]["codigoerror"] = '0002';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'SOLICITUD GENERADA PREVIAMENTE';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $nombre = $_SESSION["entrada"]["_primerapellido"];
        if (trim($_SESSION["entrada"]["_segundoapellido"]) != '') {
            $nombre .= ' ' . $_SESSION["entrada"]["_segundoapellido"];
        }
        if (trim($_SESSION["entrada"]["_primernombre"]) != '') {
            $nombre .= ' ' . $_SESSION["entrada"]["_primernombre"];
        }
        if (trim($_SESSION["entrada"]["_otrosnombres"]) != '') {
            $nombre .= ' ' . $_SESSION["entrada"]["_otrosnombres"];
        }

        //
        $arrCampos = array(
            'email',
            'celular',
            'tipoidentificacion',
            'identificacion',
            'nombre',
            'nombres',
            'apellido1',
            'apellido2',
            'direccion',
            'municipio',
            'nitempresa',
            'nombreempresa',
            'estado',
            'fecha_hora_creacion',
            'fecha_hora_verificacion',
            'fecha_hora_rechazo',
            'fecha_hora_inactivacion',
            'motivoinactivacion',
            'claveactivacion',
            'claveconfirmacion',
            'claveacceso'
        );
        $arrValores = array(
            "'" . $_SESSION["entrada"]["_email"] . "'",
            "'" . $_SESSION["entrada"]["_celular"] . "'",
            "'" . $_SESSION["entrada"]["_tipodoc"] . "'",
            "'" . $_SESSION["entrada"]["_identificacion"] . "'",
            "'" . $nombre . "'",
            "'" . trim($_SESSION["entrada"]["_primernombre"] . ' ' . $_SESSION["entrada"]["_otrosnombres"]) . "'",
            "'" . $_SESSION["entrada"]["_primerapellido"] . "'",
            "'" . $_SESSION["entrada"]["_segundoapellido"] . "'",
            "'" . $_SESSION["entrada"]["_direccion"] . "'",
            "'" . $_SESSION["entrada"]["_municipio"] . "'",
            "''",
            "'" . $_SESSION["entrada"]["_origen"] . "'",
            "'PE'",
            "'" . date("Ymd") . ' ' . date("His") . "'",
            "''",
            "''",
            "''",
            "''",
            "''",
            "''",
            "''"
        );
        $res = insertarRegistrosMysqliApi($mysqli, 'usuarios_verificados', $arrCampos, $arrValores);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"] = array();
            $_SESSION["jsonsalida"]["codigoerror"] = '0001';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'IMPOSIBLE CONTINUAR, NO SE PUDO GRABAR EL REGISTRO EN LAS TABLAS DEL SISTEMA, POR FAVOR INFORME ESTE HECHO AL ADMINISTRADOR DEL SISTEMA';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli->close();
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'SOLICITUD GRABADA';
        \logApi::peticionRest('api_' . __FUNCTION__);
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesValidarReactivador(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

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
        $mysqli = conexionMysqliApi();

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", true);
        $api->validarParametro("tipoidentificacion", true);
        $api->validarParametro("identificacion", true);
        // $api->validarParametro("fechaexpedicion", true);
        $api->validarParametro("nombre", true);
        $api->validarParametro("apellido", true);
        $api->validarParametro("cargo", true);
        $api->validarParametro("email", true);
        $api->validarParametro("celular", true);

        $_SESSION["entrada"]["matricula"] = base64_decode($_SESSION["entrada"]["matricula"]);
        $_SESSION["entrada"]["tipoidentificacion"] = base64_decode($_SESSION["entrada"]["tipoidentificacion"]);
        $_SESSION["entrada"]["identificacion"] = base64_decode($_SESSION["entrada"]["identificacion"]);
        // $_SESSION["entrada"]["fechaexpedicion"] = base64_decode($_SESSION["entrada"]["fechaexpedicion"]);
        $_SESSION["entrada"]["nombre"] = base64_decode($_SESSION["entrada"]["nombre"]);
        $_SESSION["entrada"]["apellido"] = base64_decode($_SESSION["entrada"]["apellido"]);
        $_SESSION["entrada"]["cargo"] = base64_decode($_SESSION["entrada"]["cargo"]);
        $_SESSION["entrada"]["email"] = base64_decode($_SESSION["entrada"]["email"]);
        $_SESSION["entrada"]["celular"] = base64_decode($_SESSION["entrada"]["celular"]);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesValidarReactivador', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesValidarReactivador ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $valido = 'no';

        //
        $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);

        if ($exp["existenvinculos"] == 'si' || $exp["organizacion"] == '01' || $exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
            if ($exp["organizacion"] == '01') {
                if ($exp["tipoidentificacion"] == $_SESSION["entrada"]["tipoidentificacion"] && $exp["identificacion"] == ltrim($_SESSION["entrada"]["identificacion"], "0")) {
                    $valido = 'si';
                }
            } else {
                foreach ($exp["vinculos"] as $v) {
                    if ($v["idtipoidentificacionotros"] == $_SESSION["entrada"]["tipoidentificacion"] && $v["identificacionotros"] == ltrim($_SESSION["entrada"]["identificacion"], "0")) {
                        if ($v["puedereactivar"] == 'S') {
                            $valido = 'si';
                        }
                    }
                }
                if ($valido == 'no') {
                    if (isset($exp["propietarios"])) {
                        foreach ($exp["propietarios"] as $v) {
                            if ($v["idtipoidentificacionpropietario"] == $_SESSION["entrada"]["tipoidentificacion"] && $v["identificacionpropietario"] == ltrim($_SESSION["entrada"]["identificacion"], "0")) {
                                $valido = 'si';
                            }
                        }
                    }
                }
                if ($valido == 'no') {
                    if (isset($exp["vincuprop"])) {
                        foreach ($exp["vincuprop"] as $v) {
                            if ($v["idclase"] == $_SESSION["entrada"]["tipoidentificacion"] && $v["numid"] == ltrim($_SESSION["entrada"]["identificacion"], "0")) {
                                $valido = 'si';
                            }
                        }
                    }
                }
            }
        }

        //
        if ($valido == 'no') {
            $usu = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["generales"]["codigousuario"] . "'");
            if ($usu["abogadocoordinador"] == 'SI') {
                $valido = 'si';
            }
        }

        if ($valido == 'si') {
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "0001";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El tercero no esta relacionado con el expediente o no tiene potestad para reactivar';
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregValidacionesValidarANI(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');
        require_once ('funcionesRues.php');

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
        $mysqli = conexionMysqliApi();

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacion", true);
        $api->validarParametro("apellido1", false);
        $api->validarParametro("apellido2", false);
        $api->validarParametro("nombre1", false);
        $api->validarParametro("nombre2", false);
        $api->validarParametro("tipoconsulta", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesValidarANI', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesValidarANI ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (isset($_SESSION["entrada"]["tipoconsulta"]) && $_SESSION["entrada"]["tipoconsulta"] == 'publicos') {
            $resAni = \funcionesRues::consumirANI2($mysqli, '1', $_SESSION["entrada"]["identificacion"]);
            if ($resAni["codigoerror"] == '0000') {
                if ($resAni["codError"] == '1') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificación no encontrada en la Registraduría';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                $namernec = trim($resAni["primerApellido"]);
                if (trim($resAni["particula"]) != '') {
                    $namernec .= ' ' . trim($resAni["particula"]);
                }
                if (trim($resAni["segundoApellido"]) != '') {
                    $namernec .= ' ' . trim($resAni["segundoApellido"]);
                }
                if (trim($resAni["primerNombre"]) != '') {
                    $namernec .= ' ' . trim($resAni["primerNombre"]);
                }
                if (trim($resAni["segundoNombre"]) != '') {
                    $namernec .= ' ' . trim($resAni["segundoNombre"]);
                }
                $nametra = trim($_SESSION["entrada"]["apellido1"]);
                if (trim($_SESSION["entrada"]["apellido2"])) {
                    $nametra .= ' ' . $_SESSION["entrada"]["apellido2"];
                }
                if (trim($_SESSION["entrada"]["nombre1"])) {
                    $nametra .= ' ' . $_SESSION["entrada"]["nombre1"];
                }
                if (trim($_SESSION["entrada"]["nombre2"])) {
                    $nametra .= ' ' . $_SESSION["entrada"]["nombre2"];
                }
                if ($namernec != $nametra) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Nombre digitdo no corresponde con el reportado por la Registraduría para la identificación.';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        } else {
            if (!defined('CFE_VALIDAR_ANI_CAJA') || trim(CFE_VALIDAR_ANI_CAJA) == '' || CFE_VALIDAR_ANI_CAJA == 'SI') {
                $resAni = \funcionesRues::consumirANI2($mysqli, '1', $_SESSION["entrada"]["identificacion"]);
                if ($resAni["codigoerror"] == '0000') {
                    if ($resAni["codError"] == '1') {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificación no encontrada en la Registraduría';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                    $namernec = trim($resAni["primerApellido"]);
                    if (trim($resAni["particula"]) != '') {
                        $namernec .= ' ' . trim($resAni["particula"]);
                    }
                    if (trim($resAni["segundoApellido"]) != '') {
                        $namernec .= ' ' . trim($resAni["segundoApellido"]);
                    }
                    if (trim($resAni["primerNombre"]) != '') {
                        $namernec .= ' ' . trim($resAni["primerNombre"]);
                    }
                    if (trim($resAni["segundoNombre"]) != '') {
                        $namernec .= ' ' . trim($resAni["segundoNombre"]);
                    }
                    $nametra = trim($_SESSION["entrada"]["apellido1"]);
                    if (trim($_SESSION["entrada"]["apellido2"])) {
                        $nametra .= ' ' . $_SESSION["entrada"]["apellido2"];
                    }
                    if (trim($_SESSION["entrada"]["nombre1"])) {
                        $nametra .= ' ' . $_SESSION["entrada"]["nombre1"];
                    }
                    if (trim($_SESSION["entrada"]["nombre2"])) {
                        $nametra .= ' ' . $_SESSION["entrada"]["nombre2"];
                    }
                    if ($namernec != $nametra) {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Nombre digitdo no corresponde con el reportado por la Registraduría para la identificación.';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                }
            }
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregValidacionesAsociarImagen(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

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

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesAsociarImagen', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesAsociarImagen ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera las variables del arreglo $_SESSION["entrada1"]
        // ********************************************************************** //
        $vars = array();
        foreach ($_SESSION["entrada1"] as $key => $valor) {
            $vars[$key] = base64_decode($valor);
        }

        // ********************************************************************** //
        // Recupera las variables del arreglo $_SESSION["entrada1"]
        // ********************************************************************** //
        if (!is_dir(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"])) {
            mkdir(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"], 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"]);
        }
        if (!is_dir(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/fotoEvidencias/")) {
            mkdir(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/fotoEvidencias/", 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/fotoEvidencias/");
        }
        if (!is_dir(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/fotoEvidencias/" . substr($vars["_nrocontrolsipref"], 0, 3) . "/")) {
            mkdir(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/fotoEvidencias/" . substr($vars["_nrocontrolsipref"], 0, 3) . "/", 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/fotoEvidencias/" . substr($vars["_nrocontrolsipref"], 0, 3) . "/");
        }

        $nf = PATH_ABSOLUTO_SITIO . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/fotoEvidencias/" . substr($vars["_nrocontrolsipref"], 0, 3) . "/" . $vars["_nrocontrolsipref"] . '-' . $vars["_tipoimagen"] . '.jpg';
        $f = fopen($nf, "w");
        fwrite($f, base64_decode(str_replace("data:image/png;base64,", "", $vars["_data"])));
        fclose($f);
    }

    public function mregValidacionesvalidarNroControl(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

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
        $api->validarParametro("nrocontrolsipref", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesvalidarNroControl', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesvalidarNroControl ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        $nrocontrolsipref = base64_decode($_SESSION["entrada"]["nrocontrolsipref"]);
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_sipref_controlevidencias', "nrocontrolsipref='" . $nrocontrolsipref . "'");
        if ($res === false || empty($res)) {
            $res = retornarRegistroMysqliApi($mysqli, 'mreg_sipref_controlevidencias_biometricas', "nrocontrolsipref='" . $nrocontrolsipref . "'");
            if ($res === false || empty($res)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Nro. de control SIPREF no existe';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            if (trim($res["tipotramite"]) != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0002";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Nro. de control SIPREF previamente procesado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        $mysqli->close();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesValidarMatricula(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

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
        $api->validarParametro("matricula", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesValidarMatricula', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesValidarMatricula ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . base64_decode($_SESSION["entrada"]["matricula"]) . "'");
        if ($res === false || empty($res)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Matrícula no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli->close();
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["matricula"] = $res["matricula"];
        $_SESSION["jsonsalida"]["proponente"] = $res["proponente"];
        $_SESSION["jsonsalida"]["idclase"] = $res["idclase"];
        $_SESSION["jsonsalida"]["numid"] = $res["numid"];
        $_SESSION["jsonsalida"]["razonsocial"] = $res["razonsocial"];
        $_SESSION["jsonsalida"]["sigla"] = $res["sigla"];
        $_SESSION["jsonsalida"]["organizacion"] = $res["organizacion"];
        $_SESSION["jsonsalida"]["categoria"] = $res["categoria"];
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesSearchIdentificacion(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');
        require_once ('funcionesRues.php');

        // array de respuesta
        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["nombre"] = '';
        $_SESSION["jsonsalida"]["ape1"] = '';
        $_SESSION["jsonsalida"]["ape2"] = '';
        $_SESSION["jsonsalida"]["nom1"] = '';
        $_SESSION["jsonsalida"]["nom2"] = '';
        $_SESSION["jsonsalida"]["fnac"] = '';
        $_SESSION["jsonsalida"]["fexp"] = '';
        $_SESSION["jsonsalida"]["vani"] = '';
        $_SESSION["jsonsalida"]["fvani"] = '';
        $_SESSION["jsonsalida"]["nvani"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idclase", true);
        $api->validarParametro("numid", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesSearchIdentificacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesSearchIdentificacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        //
        $mysqli = conexionMysqliApi();

        //
        if ($_SESSION["entrada"]["idclase"] == '1') {
            $res = \funcionesRues::consumirANI2($mysqli, $_SESSION["entrada"]["idclase"], $_SESSION["entrada"]["numid"]);
            if ($res && !empty($res)) {
                $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                $_SESSION["jsonsalida"]["mensajeerror"] = '';
                $_SESSION["jsonsalida"]["nombre"] = $res["primerApellido"];
                if (trim((string) $res["segundoApellido"]) != '') {
                    if (trim((string) $res["particula"]) != '') {
                        $_SESSION["jsonsalida"]["nombre"] .= ' '  . $res["particula"] . ' ' . $res["segundoApellido"];
                    } else {
                        $_SESSION["jsonsalida"]["nombre"] .= ' ' . $res["segundoApellido"];
                    }
                }
                if (trim((string) $res["primerNombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombre"] .= ' ' . $res["primerNombre"];
                }
                if (trim((string) $res["segundoNombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombre"] .= ' ' . $res["segundoNombre"];
                }
                $_SESSION["jsonsalida"]["ape1"] = $res["primerApellido"];
                $_SESSION["jsonsalida"]["ape2"] = '';
                if (trim((string) $res["segundoApellido"]) != '') {
                    if (trim((string) $res["particula"]) != '') {
                        $_SESSION["jsonsalida"]["ape2"] = $res["particula"] . ' '  . $res["segundoApellido"];
                    } else {
                        $_SESSION["jsonsalida"]["ape2"] = $res["segundoApellido"];
                    }
                }
                $_SESSION["jsonsalida"]["nom1"] = $res["primerNombre"];
                $_SESSION["jsonsalida"]["nom2"] = $res["segundoNombre"];
                $_SESSION["jsonsalida"]["fnac"] = $res["fechaNacimiento"];
                $_SESSION["jsonsalida"]["fexp"] = $res["fechaExpedicion"];
                $_SESSION["jsonsalida"]["vani"] = 'SI';
                $_SESSION["jsonsalida"]["fvani"] = date("Ymd");
                $_SESSION["jsonsalida"]["nvani"] = $res["numeroControl"];
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit();
            }
        }

        //
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "idclase='" . $_SESSION["entrada"]["idclase"] . "' and numid='" . $_SESSION["entrada"]["numid"] . "'");
        if ($res && !empty($res)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $_SESSION["jsonsalida"]["nombre"] = $res["razonsocial"];
            $_SESSION["jsonsalida"]["ape1"] = $res["apellido1"];
            $_SESSION["jsonsalida"]["ape2"] = $res["apellido2"];
            $_SESSION["jsonsalida"]["nom1"] = $res["nombre1"];
            $_SESSION["jsonsalida"]["nom2"] = $res["nombre2"];
            $_SESSION["jsonsalida"]["fnac"] = $res["fechanacimiento"];
            $_SESSION["jsonsalida"]["fexp"] = $res["fechaexpedicion"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        //
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_est_vinculos', "idclase='" . $_SESSION["entrada"]["idclase"] . "' and numid='" . $_SESSION["entrada"]["numid"] . "'");
        if ($res && !empty($res)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $_SESSION["jsonsalida"]["nombre"] = $res["nombre"];
            $_SESSION["jsonsalida"]["ape1"] = $res["ape1"];
            $_SESSION["jsonsalida"]["ape2"] = $res["ape2"];
            $_SESSION["jsonsalida"]["nom1"] = $res["nom1"];
            $_SESSION["jsonsalida"]["nom2"] = $res["nom2"];
            $_SESSION["jsonsalida"]["fnac"] = $res["fechanacimiento"];
            $_SESSION["jsonsalida"]["fexp"] = $res["fechaexpdoc"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        //
        $mysqli->close();

        //
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesRecuperarPoderKardex(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["textopoderkardex"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("inscripcion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesRecuperarPoderKardex', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesSearchIdentificacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        list ($libro, $registro, $dupli, $fecha) = explode("-", $_SESSION["entrada"]["inscripcion"]);

        //
        $mysqli = conexionMysqliApi();
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $libro . "' and registro='" . $registro . "' and dupli='" . $dupli . "'");
        if ($res && !empty($res)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $_SESSION["jsonsalida"]["textopoderkardex"] = base64_encode((string) $res["txtpoder"]);
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
    }

    public function mregValidacionesRecuperarCodigoBarras(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("codigobarras", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesRecuperarCodigoBarras', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesRecuperarCodigoBarras ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        $cb = \funcionesRegistrales::retornarCodigoBarras($mysqli, $_SESSION["entrada"]["codigobarras"]);
        $mysqli->close();

        //
        if ($cb === false || empty($cb)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Código de barras no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        foreach ($cb as $key => $valor) {
            if ($key != 'datliq' && $key != 'telefonos' && $key != 'emails' && $key != 'servicios' && $key != 'sellos' && $key != 'pasos' && $key != 'arrpasos' && $key != 'matriculasasociadas') {
                $_SESSION["jsonsalida"][$key] = $valor;
            }
        }
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesSearchPropietario(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');
        require_once ('funcionesRues.php');

        // array de respuesta
        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idclasepropietario"] = '';
        $_SESSION["jsonsalida"]["numidpropietario"] = '';
        $_SESSION["jsonsalida"]["nitpropietario"] = '';
        $_SESSION["jsonsalida"]["camarapropietario"] = '';
        $_SESSION["jsonsalida"]["matriculapropietario"] = '';
        $_SESSION["jsonsalida"]["organizacionpropietario"] = '';
        $_SESSION["jsonsalida"]["nombrepropietario"] = '';
        $_SESSION["jsonsalida"]["dircompropietario"] = '';
        $_SESSION["jsonsalida"]["muncompropietario"] = '';
        $_SESSION["jsonsalida"]["dirnotpropietario"] = '';
        $_SESSION["jsonsalida"]["munnotpropietario"] = '';
        $_SESSION["jsonsalida"]["telefono1propietario"] = '';
        $_SESSION["jsonsalida"]["telefono2propietario"] = '';
        $_SESSION["jsonsalida"]["telefono3propietario"] = '';
        $_SESSION["jsonsalida"]["nombrereplegal"] = '';
        $_SESSION["jsonsalida"]["idclasereplegal"] = '';
        $_SESSION["jsonsalida"]["numidreplegal"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idclase", true);
        $api->validarParametro("numid", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregValidacionesSearchPropietario', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregValidacionesSearchPropietario ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $encontro = '';

        //
        $local = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "idclase='" . $_SESSION["entrada"]["idclase"] . "' and numid='" . $_SESSION["entrada"]["numid"] . "'", "fechamatricula");
        if ($local && !empty($local)) {
            foreach ($local as $l) {
                if ($l["ctrestmatricula"] == 'MA' || $l["ctrestmatricula"] == 'MI') {
                    if ($l["organizacion"] == '01' || ($l["organizacion"] > '02' && $l["categoria"] == '1')) {
                        $encontro = 'si';
                        $_SESSION["jsonsalida"]["idclasepropietario"] = $l["idclase"];
                        $_SESSION["jsonsalida"]["numidpropietario"] = $l["numid"];
                        $_SESSION["jsonsalida"]["nitpropietario"] = $l["nit"];
                        $_SESSION["jsonsalida"]["camarapropietario"] = CODIGO_EMPRESA;
                        $_SESSION["jsonsalida"]["matriculapropietario"] = $l["matricula"];
                        $_SESSION["jsonsalida"]["organizacionpropietario"] = $l["organizacion"];
                        $_SESSION["jsonsalida"]["nombrepropietario"] = $l["razsonsocial"];
                        $_SESSION["jsonsalida"]["dircompropietario"] = $l["dircom"];
                        $_SESSION["jsonsalida"]["muncompropietario"] = $l["muncom"];
                        $_SESSION["jsonsalida"]["dirnotpropietario"] = $l["dirnot"];
                        $_SESSION["jsonsalida"]["munnotpropietario"] = $l["munnot"];
                        $_SESSION["jsonsalida"]["telefono1propietario"] = $l["telcom1"];
                        $_SESSION["jsonsalida"]["telefono2propietario"] = $l["telcom2"];
                        $_SESSION["jsonsalida"]["telefono3propietario"] = $l["telcom3"];
                        $_SESSION["jsonsalida"]["nombrereplegal"] = '';
                        $_SESSION["jsonsalida"]["idclasereplegal"] = '';
                        $_SESSION["jsonsalida"]["numidreplegal"] = '';
                        if ($l["organizacion"] > '02') {
                            $vins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $l["matricula"] . "'", "id");
                            if ($vins && !empty($vins)) {
                                foreach ($vins as $v) {
                                    if ($v["estado"] == 'V') {
                                        if ($v["vinculo"] == '2170' || $v["vinculo"] == '4170') {
                                            if ($_SESSION["jsonsalida"]["nombrereplegal"] == '') {
                                                $_SESSION["jsonsalida"]["nombrereplegal"] = $v["nombre"];
                                                $_SESSION["jsonsalida"]["idclasereplegal"] = $v["idclase"];
                                                $_SESSION["jsonsalida"]["numidreplegal"] = $v["numid"];
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
        if ($encontro == 'si') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $rues = \funcionesRues::consultarRegMerIdentificacion($_SESSION["generales"]["codigousuario"], $_SESSION["entrada"]["idclase"], $_SESSION["entrada"]["numid"]);
        if ($rues) {
            if (is_array($rues)) {
                if (isset($rues["registros"])) {
                    if (!empty($rues["registros"])) {
                        foreach ($rues["registros"] as $r) {
                            if ($r["codigo_estado_matricula"] == '01') {
                                if ($encontro == 'no') {
                                    $encontro = 'si';
                                    $_SESSION["jsonsalida"]["idclasepropietario"] = $_SESSION["entrada"]["idclase"];
                                    $_SESSION["jsonsalida"]["numidpropietario"] = $_SESSION["entrada"]["numid"];
                                    if ($_SESSION["entrada"]["idclase"] == '2') {
                                        $_SESSION["jsonsalida"]["nitpropietario"] = $_SESSION["entrada"]["numid"];
                                    } else {
                                        if ($r["digito_verificacion"] != '') {
                                            $_SESSION["jsonsalida"]["nitpropietario"] = trim($r["numero_identificacion"] . $r["digito_verificacion"]);
                                        }
                                    }
                                    $_SESSION["jsonsalida"]["camarapropietario"] = $r["codigo_camara"];
                                    $_SESSION["jsonsalida"]["matriculapropietario"] = $r["matricula"];
                                    $_SESSION["jsonsalida"]["organizacionpropietario"] = \funcionesGenerales::homologacionCodigoSII($mysqli, '03', $r["codigo_organizacion_juridica"]);
                                    $_SESSION["jsonsalida"]["nombrepropietario"] = $r["razon_social"];
                                    $_SESSION["jsonsalida"]["dircompropietario"] = $r["direccion_comercial"];
                                    $_SESSION["jsonsalida"]["muncompropietario"] = $r["codigo_municipio_comercial"];
                                    $_SESSION["jsonsalida"]["dirnotpropietario"] = $r["direccion_fiscal"];
                                    $_SESSION["jsonsalida"]["munnotpropietario"] = $r["codigo_municipio_fiscal"];
                                    $_SESSION["jsonsalida"]["telefono1propietario"] = $r["telefono_comercial_1"];
                                    $_SESSION["jsonsalida"]["telefono2propietario"] = '';
                                    $_SESSION["jsonsalida"]["telefono3propietario"] = '';
                                    $_SESSION["jsonsalida"]["nombrereplegal"] = '';
                                    $_SESSION["jsonsalida"]["idclasereplegal"] = '';
                                    $_SESSION["jsonsalida"]["numidreplegal"] = '';
                                    if (isset($r["vinculos"]) && !empty($r["vinculos"])) {
                                        foreach ($r["vinculos"] as $v) {
                                            if ($v["codigo_tipo_vinculo"] == '01') {
                                                if ($_SESSION["jsonsalida"]["nombrereplegal"] == '') {
                                                    $_SESSION["jsonsalida"]["idclasereplegal"] = \funcionesGenerales::homologacionCodigoSII($mysqli, '02', $r["codigo_clase_identificacion"]);
                                                    $_SESSION["jsonsalida"]["numidreplegal"] = ltrim($v["numero_identificacion"], "0");
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
        if ($encontro == 'si') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["idclase"] == '1') {
            $res = \funcionesRues::consumirANI2($mysqli, $_SESSION["entrada"]["idclase"], $_SESSION["entrada"]["numid"]);
            if ($res && !empty($res)) {
                $encontro = 'si';
                $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                $_SESSION["jsonsalida"]["mensajeerror"] = '';
                $_SESSION["jsonsalida"]["idclasepropietario"] = $_SESSION["entrada"]["idclase"];
                $_SESSION["jsonsalida"]["numidpropietario"] = $_SESSION["entrada"]["numid"];
                $_SESSION["jsonsalida"]["organizacionpropietario"] = '01';
                $_SESSION["jsonsalida"]["nombrepropietario"] = $res["primerApellido"];
                if (trim((string) $res["segundoApellido"]) != '') {
                    $_SESSION["jsonsalida"]["nombrepropietario"] .= ' ' . $res["segundoApellido"];
                }
                if (trim((string) $res["primerNombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrepropietario"] .= ' ' . $res["primerNombre"];
                }
                if (trim((string) $res["segundoNombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrepropietario"] .= ' ' . $res["segundoNombre"];
                }
            }
        }

        //
        if ($encontro == 'si') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli->close();
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesValidarCapturaEvidenciaSipref() {
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("nrocontrolsipref", true);
        $nrocontrolsipref = $_SESSION["entrada"]["nrocontrolsipref"];

        $existe = 'no';
        $f1 = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($nrocontrolsipref, 0, 3) . '/' . $nrocontrolsipref . '-Foto.jpg';
        $c1 = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($nrocontrolsipref, 0, 3) . '/' . $nrocontrolsipref . '-Cedula1.jpg';
        $c2 = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($nrocontrolsipref, 0, 3) . '/' . $nrocontrolsipref . '-Cedula2.jpg';
        $r1 = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($nrocontrolsipref, 0, 3) . '/' . $nrocontrolsipref . '-Rnec.pdf';

        if (file_exists($f1)) {
            $existe = 'si';
        }
        if (file_exists($c1)) {
            $existe = 'si';
        }
        if (file_exists($c2)) {
            $existe = 'si';
        }
        if (file_exists($r1)) {
            $existe = 'si';
        }

        $mysqli = conexionMysqliApi();
        $resBio = retornarRegistroMysqliApi($mysqli, 'mreg_sipref_controlevidencias_biometricas', "nrocontrolsipref='" . $nrocontrolsipref . "'");
        $mysqli->close();
        if ($resBio) {
            $existe = 'si';
        }

        //
        if ($existe === 'no') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No existe captura de evidencia SIPREF';
        }
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregValidacionesValidarNroControlSipref() {

        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("nrocontrolsipref", true);
        $nrocontrolsipref = $_SESSION["entrada"]["nrocontrolsipref"];
        $mysqli = conexionMysqliApi();
        $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_sipref_controlevidencias', "nrocontrolsipref='" . $nrocontrolsipref . "'");
        $mysqli->close();
        if ($arrTem === false || empty($arrTem)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "0001";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Nro de control SIPREF no encontrado';
        } else {
            if ($arrTem["tipotramite"] != '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "0002";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Nro de control SIPREF asignado a otro trámite';
            }
        }
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

}
