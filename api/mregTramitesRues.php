<?php

namespace api;

use api\API;

trait mregTramitesRues {

    public function ruesBorrarAnexoSolicitudBloque(API $api) {
        require_once ('log.php');

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
        $api->validarParametro("_idanexo", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('ruesBorrarAnexoSolicitudBloque', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesBorrarAnexoSolicitudBloque ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        unlink(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/documentos_sin_costo_rues/' . base64_decode($_SESSION["entrada"]["_idanexo"]));

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function ruesBorrarSolicitudBloque(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');

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
        $api->validarParametro("_id", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('ruesBorrarSolicitudBloque', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Pasa de base 64 a texto plano
        // ********************************************************************** // 
        $_SESSION["entrada"]["_id"] = base64_decode($_SESSION["entrada"]["_id"]);

        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $camarasseleccionadas = 0;
        $camarasenviadas01 = 0;
        $camarasenviadas03 = 0;

        //
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_rues_bloque', "id=" . $_SESSION["entrada"]["_id"]);
        if ($reg === false || empty($reg)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La solicitud no se encontró en la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($reg["estado"] !== 'PE') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La solicitud no se encuentra en un estado que permita su eliminación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $regcam = retornarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque_camaras', "idradicacionbloque=" . $_SESSION["entrada"]["_id"], "camararesponsable");
        if ($regcam === false || empty($regcam)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se recuperaron las cámaras oara validar envíos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        foreach ($regcam as $cx) {
            $camarasseleccionadas++;
            if ($cx["fecharadicacion"] != '') {
                $camarasenviadas01++;
            }
            if ($cx["cargadosobre"] != '') {
                $camarasenviadas03++;
            }
        }
        unset($regcam);

        if ($camarasenviadas01 != 0 || $camarasenviadas03 = 0) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No es posible eliminar, se inició la radicación  en bloque.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $txt = 'Se elimina la solicitud ...' . chr(13) . chr(10);
        foreach ($reg as $key => $valor) {
            $txt .= $key . ' = ' . $valor . chr(13) . chr(10);
        }

        \logApi::general2('api_mregTramitesRues_ruesBorrarSolicitudBloque_' . date("Ymd"), '', $txt);

        //
        borrarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque', "id=" . $_SESSION["entrada"]["_id"]);
        borrarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque_camaras', "idradicacionbloque=" . $_SESSION["entrada"]["_id"]);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function ruesGrabarSolicitudBloque(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');

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
        $api->validarParametro("_numerorecuperacion", true);
        $api->validarParametro("_fecha", true);
        $api->validarParametro("_hora", true);
        $api->validarParametro("_idusuario", true);
        $api->validarParametro("_estado", true);
        $api->validarParametro("_origendocumento", false);
        $api->validarParametro("_entidadorigen", true);
        $api->validarParametro("_nombreremitente", true);
        $api->validarParametro("_idclase", true);
        $api->validarParametro("_numid", true);
        $api->validarParametro("_direccion", true);
        $api->validarParametro("_municipio", true);
        $api->validarParametro("_telefono1", true);
        $api->validarParametro("_telefono2", false);
        $api->validarParametro("_email", true);
        $api->validarParametro("_servicio", true);
        $api->validarParametro("_tipodocumento", true);
        $api->validarParametro("_numerodocumento", true);
        $api->validarParametro("_fechadocumento", true);
        $api->validarParametro("_indicadororigen", true);
        $api->validarParametro("_descripcion", true);
        $api->validarParametro("_nombreafectado", false);
        $api->validarParametro("_idclaseafectado", false);
        $api->validarParametro("_numidafectado", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('ruesGrabarSolicitudBloque', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesGrabarSolicitudBloque ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Pasa de base 65 a texto plano
        // ********************************************************************** // 
        $_SESSION["entrada"]["_numerorecuperacion"] = base64_decode($_SESSION["entrada"]["_numerorecuperacion"]);
        $_SESSION["entrada"]["_idusuario"] = base64_decode($_SESSION["entrada"]["_idusuario"]);
        $_SESSION["entrada"]["_fecha"] = str_replace("-", "", base64_decode($_SESSION["entrada"]["_fecha"]));
        $_SESSION["entrada"]["_hora"] = str_replace(":", "", base64_decode($_SESSION["entrada"]["_hora"]));
        $_SESSION["entrada"]["_servicio"] = base64_decode($_SESSION["entrada"]["_servicio"]);
        $_SESSION["entrada"]["_tipodocumento"] = str_replace("-", "", base64_decode($_SESSION["entrada"]["_tipodocumento"]));
        $_SESSION["entrada"]["_numerodocumento"] = strtoupper(str_replace("-", "", base64_decode($_SESSION["entrada"]["_numerodocumento"])));
        $_SESSION["entrada"]["_fechadocumento"] = str_replace("-", "", base64_decode($_SESSION["entrada"]["_fechadocumento"]));
        $_SESSION["entrada"]["_origendocumento"] = strtoupper(base64_decode($_SESSION["entrada"]["_origendocumento"]));
        $_SESSION["entrada"]["_indicadororigen"] = base64_decode($_SESSION["entrada"]["_indicadororigen"]);
        $_SESSION["entrada"]["_descripcion"] = strtoupper(base64_decode($_SESSION["entrada"]["_descripcion"]));
        $_SESSION["entrada"]["_entidadorigen"] = strtoupper(base64_decode($_SESSION["entrada"]["_entidadorigen"]));
        $_SESSION["entrada"]["_nombreremitente"] = strtoupper(base64_decode($_SESSION["entrada"]["_nombreremitente"]));
        $_SESSION["entrada"]["_idclase"] = base64_decode($_SESSION["entrada"]["_idclase"]);
        $_SESSION["entrada"]["_numid"] = base64_decode($_SESSION["entrada"]["_numid"]);
        $_SESSION["entrada"]["_direccion"] = strtoupper(base64_decode($_SESSION["entrada"]["_direccion"]));
        $_SESSION["entrada"]["_municipio"] = base64_decode($_SESSION["entrada"]["_municipio"]);
        $_SESSION["entrada"]["_telefono1"] = base64_decode($_SESSION["entrada"]["_telefono1"]);
        $_SESSION["entrada"]["_telefono2"] = base64_decode($_SESSION["entrada"]["_telefono2"]);
        $_SESSION["entrada"]["_email"] = base64_decode($_SESSION["entrada"]["_email"]);
        $_SESSION["entrada"]["_estado"] = base64_decode($_SESSION["entrada"]["_estado"]);
        $_SESSION["entrada"]["_nombreafectado"] = strtoupper(base64_decode($_SESSION["entrada"]["_nombreafectado"]));
        $_SESSION["entrada"]["_idclaseafectado"] = base64_decode($_SESSION["entrada"]["_idclaseafectado"]);
        $_SESSION["entrada"]["_numidafectado"] = base64_decode($_SESSION["entrada"]["_numidafectado"]);

        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $camarasseleccionadas = 0;
        $camarasenviadas01 = 0;
        $camarasenviadas03 = 0;
        $camaras = array();

        //
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_rues_bloque', "numerorecuperacion='" . $_SESSION["entrada"]["_numerorecuperacion"] . "'");
        if ($reg && !empty($reg)) {
            $temx = retornarRegistrosMysqliApi($mysqli, "mreg_rues_bloque_camaras", "idradicacionbloque=" . $reg["id"], "camararesponsable");
            if ($temx && !empty($temx)) {
                foreach ($temx as $c) {
                    $camaras[$c["camararesponsable"]] = $c;
                    if ($c["numerointernorue"] != '') {
                        $camarasenviadas01++;
                    }
                    if ($c["cargadosobre"] != '') {
                        $camarasenviadas03++;
                    }
                }
            }
        }

        //
        foreach ($_SESSION["entrada1"] as $key => $valor) {
            if (substr($key, 0, 8) == '_camara_') {
                $cx = substr($key, 8, 2);
                if (base64_decode($valor) == '1') {
                    $camarasseleccionadas++;
                    if (!isset($camaras[$cx])) {
                        $camaras[$cx] = array();
                        $camaras[$cx]["camararesponsable"] = $cx;
                        $camaras[$cx]["numerointernorue"] = '';
                        $camaras[$cx]["numerounicorue"] = '';
                        $camaras[$cx]["fecharadicacion"] = '';
                        $camaras[$cx]["horaradicacion"] = '';
                        $camaras[$cx]["numerorecibo"] = '';
                        $camaras[$cx]["numerooperacion"] = '';
                        $camaras[$cx]["idliquidacion"] = 0;
                        $camaras[$cx]["generadosobre"] = '';
                        $camaras[$cx]["cargadosobre"] = '';
                    }
                } else {
                    if (isset($camaras[$cx])) {
                        if ($camaras[$cx]["numerointernorue"] == '') {
                            unset($camaras[$cx]);
                        }
                    }
                }
            }
        }

        //
        $arrCampos = array(
            'numerorecuperacion',
            'idusuario',
            'fecha',
            'hora',
            'servicio',
            'tipodocumento',
            'numerodocumento',
            'fechadocumento',
            'origendocumento',
            'indicadororigen',
            'descripcion',
            'entidadorigen',
            'nombreremitente',
            'idclase',
            'numid',
            'direccion',
            'municipio',
            'telefono1',
            'telefono2',
            'email',
            'idclaseafectado',
            'numidafectado',
            'nombreafectado',
            'estado',
            'camarasseleccionadas',
            'camarasenviadas01',
            'camarasenviadas03'
        );

        // 
        $arrValores = array(
            "'" . $_SESSION["entrada"]["_numerorecuperacion"] . "'",
            "'" . $_SESSION["entrada"]["_idusuario"] . "'",
            "'" . $_SESSION["entrada"]["_fecha"] . "'",
            "'" . $_SESSION["entrada"]["_hora"] . "'",
            "'" . $_SESSION["entrada"]["_servicio"] . "'",
            "'" . $_SESSION["entrada"]["_tipodocumento"] . "'",
            "'" . $_SESSION["entrada"]["_numerodocumento"] . "'",
            "'" . $_SESSION["entrada"]["_fechadocumento"] . "'",
            "'" . addslashes($_SESSION["entrada"]["_origendocumento"]) . "'",
            "'" . $_SESSION["entrada"]["_indicadororigen"] . "'",
            "'" . addslashes($_SESSION["entrada"]["_descripcion"]) . "'",
            "'" . addslashes($_SESSION["entrada"]["_entidadorigen"]) . "'",
            "'" . addslashes($_SESSION["entrada"]["_nombreremitente"]) . "'",
            "'" . $_SESSION["entrada"]["_idclase"] . "'",
            "'" . $_SESSION["entrada"]["_numid"] . "'",
            "'" . addslashes($_SESSION["entrada"]["_direccion"]) . "'",
            "'" . $_SESSION["entrada"]["_municipio"] . "'",
            "'" . $_SESSION["entrada"]["_telefono1"] . "'",
            "'" . $_SESSION["entrada"]["_telefono2"] . "'",
            "'" . addslashes($_SESSION["entrada"]["_email"]) . "'",
            "'" . $_SESSION["entrada"]["_idclaseafectado"] . "'",
            "'" . $_SESSION["entrada"]["_numidafectado"] . "'",
            "'" . addslashes($_SESSION["entrada"]["_nombreafectado"]) . "'",
            "'" . $_SESSION["entrada"]["_estado"] . "'",
            $camarasseleccionadas,
            $camarasenviadas01,
            $camarasenviadas03
        );

        //
        if ($reg && !empty($reg)) {
            regrabarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque', $arrCampos, $arrValores, "numerorecuperacion='" . $_SESSION["entrada"]["_numerorecuperacion"] . "'");
            $id = $reg["id"];
        } else {
            insertarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque', $arrCampos, $arrValores, 'si');
            $id = $_SESSION["generales"]["lastId"];
        }

        //
        borrarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque_camaras', "idradicacionbloque=" . $id);

        //
        foreach ($camaras as $c) {
            $arrCampos = array(
                'idradicacionbloque',
                'camararesponsable',
                'numerointernorue',
                'numerounicorue',
                'fecharadicacion',
                'horaradicacion',
                'numerorecibo',
                'numerooperacion',
                'idliquidacion',
                'generadosobre',
                'cargadosobre'
            );
            $arrValores = array(
                $id,
                "'" . $c["camararesponsable"] . "'",
                "'" . $c["numerointernorue"] . "'",
                "'" . $c["numerounicorue"] . "'",
                "'" . $c["fecharadicacion"] . "'",
                "'" . $c["horaradicacion"] . "'",
                "'" . $c["numerorecibo"] . "'",
                "'" . $c["numerooperacion"] . "'",
                intval($c["idliquidacion"]),
                "'" . $c["generadosobre"] . "'",
                "'" . $c["cargadosobre"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque_camaras', $arrCampos, $arrValores);
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

    public function ruesRadicarSolicitudBloque(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');
        require_once ('funcionesRues.php');
        require_once ('gestionRecibos.php');
        require_once ('log.php');
        require_once ('PDFA.class.php');
        require_once ('s3_v4.php');
        require_once ('EncodingNew.php');
        require_once ('unirPdfs.php');
        require_once ('../configuracion/common.php');
        require_once ('../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');

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
        $api->validarParametro("_id", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('ruesRadicarSolicitudBloque', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Pasa de base 64 a texto plano
        // ********************************************************************** // 
        $_SESSION["entrada"]["_id"] = base64_decode($_SESSION["entrada"]["_id"]);

        //
        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $camarasseleccionadas = 0;
        $camarasenviadas01 = 0;
        $camarasenviadas03 = 0;

        //
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_rues_bloque', "id=" . $_SESSION["entrada"]["_id"]);
        if ($reg === false || empty($reg)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizó la solicitud en la base de datos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $regcam = retornarRegistrosMysqliApi($mysqli, "mreg_rues_bloque_camaras", "idradicacionbloque=" . $reg["id"], "camararesponsable");
        if ($regcam === false || empty($regcam)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizaron las Cámaras de Comercio donde debe ser radicado el trámite';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        foreach ($regcam as $cx) {
            $camarasseleccionadas++;
            if ($cx["numerorecibo"] != '') {
                $camarasenviadas01++;
            }
            if ($cx["cargadosobre"] != '') {
                $camarasenviadas03++;
            }
        }

        if ($camarasseleccionadas == $camarasenviadas01 && $camarasseleccionadas == $camarasenviadas03) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Solicitud radicada y procesada previamente';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // *********************************************************************** //
        // Arma trámite - Radicar
        // *********************************************************************** //
        foreach ($regcam as $cam) {
            if ($cam["numerorecibo"] == '') {

                // *********************************************************************** //
                // Crea la liquidacion 
                // *********************************************************************** //
                $formulario = array();
                $formulario["codigo"] = $reg["servicio"];
                $formulario["camaraResponsable"] = $cam["camararesponsable"];
                $formulario["origendoc"] = $reg["indicadororigen"];
                $formulario["fechadoc"] = $reg["fechadocumento"];
                $formulario["nombre"] = $reg["entidadorigen"];
                $formulario["tipoide"] = $reg["idclase"];
                $formulario["numide"] = $reg["numid"];
                $formulario["matricula"] = '';
                $razonsocial = $reg["entidadorigen"];
                $tipoIdentificacionCliente = $reg["idclase"];
                $identificacionCliente = $reg["numid"];

                //Inicializa arreglo de la liquidacion
                $_SESSION["tramite"] = array();
                $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');
                $_SESSION["tramite"]["numeroliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
                $_SESSION["tramite"]["idliquidacion"] = $_SESSION["tramite"]["numeroliquidacion"];
                $_SESSION["tramite"]["fecha"] = date("Ymd");
                $_SESSION["tramite"]["hora"] = date("His");
                $_SESSION["tramite"]["fechaultimamodificacion"] = date("His");
                $_SESSION["tramite"]["idusuario"] = $_SESSION["generales"]["codigousuario"];
                $_SESSION["tramite"]["tipotramite"] = 'rues04receptora';
                
                //
                $_SESSION["tramite"]["sistemacreacion"] = 'SIICORE - mregTramitesRuesBloque - ' . $_SESSION["tramite"]["idusuario"];
                
                
                $_SESSION["tramite"]["iptramite"] = \funcionesGenerales::localizarIP();
                $_SESSION["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
                $_SESSION["tramite"]["idestado"] = '02';

                //Datos del cliente
                $_SESSION["tramite"]["idtipoidentificacioncliente"] = $tipoIdentificacionCliente;
                $_SESSION["tramite"]["identificacioncliente"] = $identificacionCliente;
                $_SESSION["tramite"]["nombrecliente"] = $razonsocial;
                $_SESSION["tramite"]["email"] = $reg["email"];
                $_SESSION["tramite"]["direccion"] = $reg["direccion"];
                $_SESSION["tramite"]["idmunicipio"] = $reg["municipio"];
                $_SESSION["tramite"]["telefono"] = $reg["telefono1"];
                $_SESSION["tramite"]["movil"] = $reg["telefono2"];

                //Datos del Pagador
                $_SESSION["tramite"]["nombrepagador"] = $razonsocial;
                $_SESSION["tramite"]["apellidopagador"] = '';
                $_SESSION["tramite"]["tipoidentificacionpagador"] = $tipoIdentificacionCliente;
                $_SESSION["tramite"]["identificacionpagador"] = $identificacionCliente;
                $_SESSION["tramite"]["direccionpagador"] = $reg["direccion"];
                $_SESSION["tramite"]["telefonopagador"] = $reg["telefono1"];
                $_SESSION["tramite"]["municipiopagador"] = $reg["municipio"];
                $_SESSION["tramite"]["emailpagador"] = $reg["email"];

                //Datos basicos del expediente afectado
                $_SESSION["tramite"]["idexpedientebase"] = '';
                $_SESSION["tramite"]["identificacionbase"] = '';
                $_SESSION["tramite"]["tipoidentificacionbase"] = '';
                $_SESSION["tramite"]["nombrebase"] = '';
                $_SESSION["tramite"]["organizacionbase"] = '';
                $_SESSION["tramite"]["categoriabase"] = '';
                $_SESSION["tramite"]["matriculabase"] = '';
                $_SESSION["tramite"]["proponentebase"] = '';
                $_SESSION["tramite"]["actividadesbase"] = '';

                //Valores totales de la liquidacion
                $_SESSION["tramite"]["valorbruto"] = 0;
                $_SESSION["tramite"]["valorbaseiva"] = 0;
                $_SESSION["tramite"]["valoriva"] = 0;
                $_SESSION["tramite"]["valortotal"] = 0;
                $_SESSION["tramite"]["idsolicitudpago"] = 0;

                //Datos del pago
                $_SESSION["tramite"]["pagoefectivo"] = 0;
                $_SESSION["tramite"]["pagoconsignacion"] = 0;
                $_SESSION["tramite"]["pagocheque"] = 0;
                $_SESSION["tramite"]["pagovisa"] = 0;
                $_SESSION["tramite"]["pagoach"] = 0;
                $_SESSION["tramite"]["pagomastercard"] = 0;
                $_SESSION["tramite"]["pagoamerican"] = 0;
                $_SESSION["tramite"]["pagocredencial"] = 0;
                $_SESSION["tramite"]["pagodiners"] = 0;
                $_SESSION["tramite"]["pagotdebito"] = 0;
                $_SESSION["tramite"]["idformapago"] = '';
                $_SESSION["tramite"]["numerorecibo"] = '';
                $_SESSION["tramite"]["numerooperacion"] = '';
                $_SESSION["tramite"]["fecharecibo"] = '';
                $_SESSION["tramite"]["horarecibo"] = '';
                $_SESSION["tramite"]["idfranquicia"] = '';
                $_SESSION["tramite"]["nombrefranquicia"] = '';
                $_SESSION["tramite"]["numeroautorizacion"] = '';
                $_SESSION["tramite"]["idcodban"] = '';
                $_SESSION["tramite"]["nombrebanco"] = '';
                $_SESSION["tramite"]["numerocheque"] = '';

                // Definicion de variable de tramite RUES
                $_SESSION["tramite"]["rues_numerointerno"] = '';
                $_SESSION["tramite"]["rues_numerounico"] = '';
                $_SESSION["tramite"]["rues_camarareceptora"] = $_SESSION["generales"]["codigoempresa"];
                $_SESSION["tramite"]["rues_camararesponsable"] = $formulario["camaraResponsable"];
                $_SESSION["tramite"]["rues_matricula"] = $formulario["matricula"];
                $_SESSION["tramite"]["rues_proponente"] = '';
                $_SESSION["tramite"]["rues_nombreregistrado"] = $razonsocial;
                $_SESSION["tramite"]["rues_claseidentificacion"] = sprintf("%02s", $formulario["tipoide"]);
                if ($formulario["tipoide"] == 2) {
                    $sepIde = \funcionesGenerales::separarDv($formulario["numide"]);
                    $_SESSION["tramite"]["rues_numeroidentificacion"] = $sepIde["identificacion"];
                    $_SESSION["tramite"]["rues_dv"] = $sepIde["dv"];
                } else {
                    $_SESSION["tramite"]["rues_numeroidentificacion"] = $formulario["numide"];
                    $_SESSION["tramite"]["rues_dv"] = '';
                }
                $_SESSION["tramite"]["rues_estado_liquidacion"] = "";
                $_SESSION["tramite"]["rues_estado_transaccion"] = "";
                $_SESSION["tramite"]["rues_nombrepagador"] = $razonsocial;
                $_SESSION["tramite"]["rues_origendocumento"] = $formulario["origendoc"];
                $_SESSION["tramite"]["rues_fechadocumento"] = $formulario["fechadoc"];
                $_SESSION["tramite"]["rues_fechapago"] = "";
                $_SESSION["tramite"]["rues_numerofactura"] = "";
                $_SESSION["tramite"]["rues_referenciaoperacion"] = "";
                $_SESSION["tramite"]["rues_totalpagado"] = 0;
                $_SESSION["tramite"]["rues_formapago"] = "";
                $_SESSION["tramite"]["rues_indicadororigen"] = 'N';
                $_SESSION["tramite"]["rues_empleados"] = 0;
                $_SESSION["tramite"]["rues_indicadorbeneficio"] = 0;
                $_SESSION["tramite"]["rues_fecharespuesta"] = '';
                $_SESSION["tramite"]["rues_horarespuesta"] = '';
                $_SESSION["tramite"]["rues_codigoerror"] = '';
                $_SESSION["tramite"]["rues_mensajeerror"] = '';
                $_SESSION["tramite"]["rues_firmadigital"] = '';

                $_SESSION["tramite"]["rues_servicios"][1]["codigo_servicio"] = $formulario["codigo"];
                $_SESSION["tramite"]["rues_servicios"][1]["descripcion_servicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_homologaciones_rue', "trim(id_tabla) like '01' and trim(cod_rue) like '" . $formulario["codigo"] . "'", "des_rue");
                $_SESSION["tramite"]["rues_servicios"][1]["orden_servicio"] = 1;
                $_SESSION["tramite"]["rues_servicios"][1]["orden_servicio_asociado"] = '';
                $_SESSION["tramite"]["rues_servicios"][1]["nombre_base"] = $reg["nombreafectado"];
                $_SESSION["tramite"]["rues_servicios"][1]["valor_base"] = 0;
                $_SESSION["tramite"]["rues_servicios"][1]["valor_liquidacion"] = 0;
                $_SESSION["tramite"]["rues_servicios"][1]["cantidad_servicio"] = 1;
                $_SESSION["tramite"]["rues_servicios"][1]["indicador_base"] = 'N';
                $_SESSION["tramite"]["rues_servicios"][1]["indicador_renovacion"] = 'N';
                $_SESSION["tramite"]["rues_servicios"][1]["matricula_servicio"] = '0000000000';
                $_SESSION["tramite"]["rues_servicios"][1]["nombre_matriculado"] = $reg["nombreafectado"];
                $_SESSION["tramite"]["rues_servicios"][1]["ano_renovacion"] = '0000';
                $_SESSION["tramite"]["rues_servicios"][1]["valor_activos_sin_ajustes"] = 0;

                $_SESSION["tramite"]["liquidacion"][1]["expediente"] = '';
                $_SESSION["tramite"]["liquidacion"][1]["nombre"] = $reg["nombreafectado"];
                $_SESSION["tramite"]["liquidacion"][1]["ano"] = '0000';
                $_SESSION["tramite"]["liquidacion"][1]["cantidad"] = 1;
                $_SESSION["tramite"]["liquidacion"][1]["valorbase"] = 0;
                $_SESSION["tramite"]["liquidacion"][1]["porcentaje"] = 0;
                $_SESSION["tramite"]["liquidacion"][1]["valorservicio"] = 0;
                $_SESSION["tramite"]["liquidacion"][1]["benart7"] = 'N';
                $_SESSION["tramite"]["liquidacion"][1]["reliquidacion"] = '';
                $_SESSION["tramite"]["liquidacion"][1]["serviciobase"] = 'S';
                $_SESSION["tramite"]["liquidacion"][1]["pagoafiliacion"] = '';
                $_SESSION["tramite"]["liquidacion"][1]["ir"] = '';
                $_SESSION["tramite"]["liquidacion"][1]["iva"] = '';
                $_SESSION["tramite"]["liquidacion"][1]["idalerta"] = '';
                $_SESSION["tramite"]["liquidacion"][1]["idsecuencia"] = 1;
                $_SESSION["tramite"]["liquidacion"][1]["idservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_homologaciones_rue', "trim(id_tabla) like '01' and trim(cod_rue) like '" . $formulario["codigo"] . "'", "cod_cc");

                //
                $txtLog = '-----------------------------------------------------------------------' . chr(13) . chr(10);
                $txtLog .= '*** INICIO LIQUIDACION RUES : ***' . chr(13) . chr(10);
                $txtLog .= var_export($_SESSION["tramite"], true);
                $txtLog .= '*** FINAL LIQUIDACION RUES ***' . chr(13) . chr(10);
                $txtLog .= chr(13) . chr(10);
                \logApi::general2('api_mregTramitesRues_ruesRadicarSolicitudBloque_' . date("Ymd"), __FUNCTION__, $txtLog);

                //Inserta liquidacion en base de datos local
                $res = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
                if ($res === false) {
                    \logApi::general2('api_mregTramitesRues_ruesRadicarSolicitudBloque_' . date("Ymd"), __FUNCTION__, 'Error grabando liquidacion ' . $_SESSION["tramite"]["numerorecuperacion"] . ' : ' . $_SESSION["generales"]["mensajeerror"]);
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Error grabando liquidacion ' . $_SESSION["tramite"]["numerorecuperacion"] . ' : ' . $_SESSION["generales"]["mensajeerror"];
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    \logApi::general2('api_mregTramitesRues_ruesRadicarSolicitudBloque_' . date("Ymd"), __FUNCTION__, 'Armo y grabo liquidacion ' . $_SESSION["tramite"]["numerorecuperacion"]);
                }

                // *********************************************************************** //
                // Serialia la liquidación
                // *********************************************************************** //
                $xmlPago = \funcionesGenerales::serializarLiquidacion($mysqli, 'si', 'localhost', 'localhost1024', '', '', $_SESSION["generales"]["idcodigosirepcaja"]);
                \logApi::general2('api_mregTramitesRues_ruesRadicarSolicitudBloque_' . date("Ymd"), '', 'Serializo pago');

                // *********************************************************************** //
                // Llama al servicio web que registra el pago en cero
                // Crea RUE-RADICACION
                // Consume el MR02N       
                // Genera el recibo de caja         
                // *********************************************************************** //
                $res = \gestionRecibos::asentarRecibos($mysqli, $_SESSION["tramite"]["idliquidacion"], '7', '09', '', '', $_SESSION["generales"]["codigousuario"]);
                // $res = \funcionesGenerales::consumirWsActualizarPago($mysqli, $_SESSION["generales"]["codigoempresa"], $xmlPago, $_SESSION["tramite"]["tipotramite"], $_SESSION["tramite"]["idliquidacion"]);
                if ($res["codigoError"] != '0000') {
                    \logApi::general2('api_mregTramitesRues_ruesRadicarSolicitudBloque_' . date("Ymd"), '', 'Error en consumirWsActualizarPago : ' . $res["codigoError"] . ' ' . $res["msgError"]);
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en consumirWsActualizarPago : ' . $res["codigoError"] . ' ' . $res["msgError"];
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    \logApi::general2('api_mregTramitesRues_ruesRadicarSolicitudBloque_' . date("Ymd"), $res["numeroRecibo"], 'Retorno de consumirWsActualizarPago');

                    // ***************************************************************************** //
                    // Busca la RUE-RADICACION para actualizar el número interno y el número unico
                    // ***************************************************************************** //
                    $nir = '';
                    $nur = '';
                    $rr = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "recibolocal='" . $res["numeroRecibo"] . "'");
                    if ($rr && !empty($rr)) {
                        $nir = $rr["numerointernorue"];
                        $nur = $rr["numerounicoconsulta"];
                        $frad = $rr["fechaoperacion"];
                        $hrad = $rr["horaoperacion"];
                    }

                    // *********************************************************************** //
                    // Actualiza tabla mreg_rues_bloque_camaras con 
                    // - número de liquidación 
                    // - recibo de caja
                    // - número de operación
                    // - numero interno rue
                    // - número unico rue
                    // *********************************************************************** //
                    $arrCampos = array(
                        'numerointernorue',
                        'numerounicorue',
                        'fecharadicacion',
                        'horaradicacion',
                        'numerorecibo',
                        'numerooperacion',
                        'idliquidacion'
                    );
                    $arrValores = array(
                        "'" . $nir . "'",
                        "'" . $nur . "'",
                        "'" . $frad . "'",
                        "'" . $hrad . "'",
                        "'" . $res["numeroRecibo"] . "'",
                        "'" . $res["numeroOperacion"] . "'",
                        $_SESSION["tramite"]["idliquidacion"]
                    );
                    $condicion = "idradicacionbloque=" . $reg["id"] . " and camararesponsable='" . $cam["camararesponsable"] . "'";
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque_camaras', $arrCampos, $arrValores, $condicion);
                }
            }
        }

        // *********************************************************************** //
        // Generar sobre con imagenes
        // *********************************************************************** //
        $regcam = retornarRegistrosMysqliApi($mysqli, "mreg_rues_bloque_camaras", "idradicacionbloque=" . $reg["id"], "camararesponsable");
        if ($regcam === false || empty($regcam)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizaron las Cámaras de Comercio donde debe ser radicado el trámite';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        foreach ($regcam as $cam) {
            if ($cam["numerointernorue"] != '' && $cam["generadosobre"] == '') {

                // ******************************************************************** //
                // mover las imágenes al directorio RUES en el repositorio local
                // ******************************************************************** //
                $targetPath = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"];
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0777);
                    \funcionesGenerales::crearIndex($targetPath);
                }
                $targetPath = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/';
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0777);
                    \funcionesGenerales::crearIndex($targetPath);
                }
                $targetPath = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/';
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0777);
                    \funcionesGenerales::crearIndex($targetPath);
                }
                $targetPath = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/' . $cam["numerointernorue"] . '-03';
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0777);
                    \funcionesGenerales::crearIndex($targetPath);
                }
                $tam = strlen(trim($reg["numerorecuperacion"])) + 1;
                $listaArchivos = array();
                $listaArchivos1 = array();
                if ($dir = opendir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/documentos_sin_costo_rues')) {
                    while (($archivo = readdir($dir)) !== false) {
                        if ($archivo != '.' && $archivo != '..' && $archivo != '.htaccess' && $archivo != 'index.html') {
                            if (substr($archivo, 0, $tam) == $reg["numerorecuperacion"] . '-') {
                                $listaArchivos[] = $archivo;                                
                            }
                        }
                    }
                    closedir($dir);
                }
                if (!empty($listaArchivos)) {
                    foreach ($listaArchivos as $nameFile) {
                        $targetFile = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/' . $cam["numerointernorue"] . '-03/' . $nameFile;
                        copy(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/documentos_sin_costo_rues/' . $nameFile, $targetFile);
                    }
                }

                // ******************************************************************** //
                // Armar el sobre digital
                // Firmar sobre
                // 
                // 2019-03-14: JINT: Se modifica para que no se genere sobre digital
                // De acuerdo con lo acordado en el comité rues operativo, se debe enviar
                // solamente un PDF/A firmado digitalmente
                // Se ajusta la rutina para que :
                // a.- Si se suben varios PDF los una en uno solo
                // b.- Convierta el PDF a PDF/A
                // c.- Firmarlo digitalmente
                // d.- Almacenarlo como un sobre.
                // ******************************************************************** //
                $rad = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "numerointernorue='" . $cam["numerointernorue"] . "'");

                //
                if ((!defined('RUES_S3_awsAccessKey') || RUES_S3_awsAccessKey == '') ||
                        (!defined('RUES_S3_awsSecretKey') || RUES_S3_awsSecretKey == '') ||
                        (!defined('RUES_S3_bucket') || RUES_S3_bucket == '')) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'No se ha configurado el acceso a S3 para el cargue de las imagenes del RUES';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }

                //
                $iP7z = 0;
                $iPdf = 0;
                $listaArchivosPdfs = array();
                $listaArchivosPdfs1 = array();
                $listaArchivosP7z = array();
                if (is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/' . $cam["numerointernorue"] . '-03')) {
                    if ($dir = opendir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/' . $cam["numerointernorue"] . '-03')) {
                        while (($archivo = readdir($dir)) !== false) {
                            if ($archivo != '.' && $archivo != '..' && $archivo != '.htaccess' && $archivo != 'index.html') {
                                /*
                                if (\funcionesGenerales::encontrarExtension($archivo) == 'p7z') {
                                    $iP7z++;
                                    $listaArchivosP7z[$iP7z] = $archivo;
                                }
                                */
                                if (\funcionesGenerales::encontrarExtension($archivo) == 'pdf') {
                                    $iPdf++;
                                    $listaArchivosPdfs[$iPdf] = $archivo;
                                    $listaArchivosPdfs1[$iPdf] = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/' . $cam["numerointernorue"] . '-03/' . $archivo;
                                }
                            }
                        }
                        closedir($dir);
                    }
                }

                //
                if (!empty($listaArchivosPdfs) && !empty($listaArchivosP7z)) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'No deben existir simultaneamente archivos PDFS y TIF junto con P7Z, por favor revisar';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }

                //
                if (empty($listaArchivosPdfs) && empty($listaArchivosP7z)) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Deben cargarse primero los archivos a enviar, sea una combinación de TIFs y PDFs o un archivo P7Z';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }

                //                
                if (!empty($listaArchivosPdfs)) {
                    /*
                    if (count($listaArchivosPdfs) > 1) {
                        unirPdfsApi($listaArchivosPdfs1,'../tmp/' . $cam["numerointernorue"] . 'RUE03.pdf');
                    } else {
                        copy(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/' . $cam["numerointernorue"] . '-03/' . $listaArchivosPdfs[1], '../tmp/' . $cam["numerointernorue"] . 'RUE03.pdf');
                    }
                    */
                    
                    //
                    $nameSalida = $cam["numerointernorue"] . 'RUE03.pdf';
                    $pdfa = new \PDFA();
                    $nombreCompletoFirmante = RAZONSOCIAL;

                    //ARMADO DE TEXTO DE FIRMADO ELECTRÓNICO
                    $textofirmante1 = 'LA ' . RAZONSOCIAL . ' FIRMÓ ELECTRÓNICAMENTE ' .
                            'LOS FORMULARIOS Y ANEXOS DOCUMENTALES DEL TRÁMITE EL ' . \funcionesGenerales::mostrarFecha(date("Ymd")) . ' A LAS ' . \funcionesGenerales::mostrarHora(date("His")) . ' ' .
                            'DANDO FE DEL CONTENIDO DE LOS MISMOS.';

                    //ADICIÓN DE TEXTO DE FIRMA ELECTRÓNICA A ARREGLO DE FIRMANTES
                    $firmantes = array($textofirmante1);

                    //ARREGLO DATOS ENCABEZADO SOBRE ELECTRÓNICO
                    $encabezadoCamara['logo'] = PATH_ABSOLUTO_SITIO . "/images/logocamara" . $_SESSION["generales"]["codigoempresa"] . ".jpg";
                    $encabezadoCamara['nombre'] = RAZONSOCIAL;
                    $encabezadoCamara['direccion'] = DIRECCION1;
                    $encabezadoCamara['telefono'] = PBX;
                    $encabezadoCamara['ciudad'] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . MUNICIPIO . "'", "ciudad");

                    $detalleTramite['idliquidacion'] = $cam["numerointernorue"];
                    $detalleTramite['numerorecuperacion'] = $rad["numerounicoconsulta"];
                    $detalleTramite['fechahora'] = \funcionesGenerales::mostrarFecha(date("Ymd")) . ' ' . \funcionesGenerales::mostrarHora(date("His"));
                    $detalleTramite['tipotramite'] = 'Trámite RUES - Estado 03';

                    $detalleTramite['idecliente'] = $rad["numuidrue"];
                    $detalleTramite['nomcliente'] = $rad["nombreregistrado"];

                    $detalleTramite['idefirmante'] = '';
                    $detalleTramite['nomfirmante'] = $nombreCompletoFirmante;

                    $detalleTramite['numfolios'] = "";
                    $detalleTramite['dependencia'] = "Registros Públicos";
                    $detalleTramite['seriesubserie'] = "Trámites RUES";

                    $detalleTramite['numfolios'] = '';

                    //ARMADO DE ARREGLO DE PATH ADJUNTOS
                    $archivosAdjuntos = array();

                    //CALCULAR EL PESO TOTAL DE SOPORTES A ADJUNTAS EN MB
                    $i = 0;
                    foreach ($listaArchivosPdfs as $t) {
                        $i++;
                        $rutaAbsolutaAdjunto = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/' . $cam["numerointernorue"] . '-03/' . $t;
                        $descripcionAdjunto = $i . '.) ' . $t;
                        $archivosAdjuntos[$i] = $rutaAbsolutaAdjunto . '|' . $descripcionAdjunto . '|';
                    }

                    //RUTA DE SALIDA SOBRE PDF/A
                    $rutaOutPDFA = PATH_ABSOLUTO_SITIO . "/tmp/" . $nameSalida;
                    $nsobre = str_replace(".pdf", "-SF.pdf", $rutaOutPDFA);

                    //GENERACIÓN DE SOBRE ELECTRÓNICO (QUEDA CON EL NOMBRE DADO EN RUTA ADICIONADO -SF)
                    $x = $pdfa->generarSobreFirmado('NA', $firmantes, $encabezadoCamara, $detalleTramite, $archivosAdjuntos, $rutaOutPDFA, 'no');
                    unset($pdfa);
                    if (!$x) {
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Error generando el PDF/A (Firmado digitalmente) con las imágenes que se enviarán al RUES';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                    rename($nsobre, PATH_ABSOLUTO_SITIO . '/tmp/' . $nameSalida);
                }

                /*
                if (!empty($listaArchivosP7z)) {
                    $nameSalida = $_SESSION["generales"]["numerointernorue"] . 'RUE03.p7z';
                    copy(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/RUES/' . $cam["numerointernorue"] . '-03/' . $listaArchivosP7z[1], '../../tmp/' . $nameSalida);
                }
                */
                
                // Cargar las imágenes a s3
                almacenarS3Version4(
                        PATH_ABSOLUTO_SITIO . '/tmp/' . $nameSalida, 'Anexos/Camara' . $_SESSION["generales"]["codigoempresa"] . '/' . $nameSalida, 'RUES'
                );


                // Almacena el nombre del sobre en mreg_rues_bloque_camaras
                // Actualiza la BD
                $arrCampos = array(
                    'generadosobre'
                );
                $arrValores = array(
                    "'" . addslashes($nameSalida) . "'"
                );
                $condicion = "idradicacionbloque=" . $reg["id"] . " and camararesponsable='" . $cam["camararesponsable"] . "'";
                regrabarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque_camaras', $arrCampos, $arrValores, $condicion);
            }
        }

        // *********************************************************************** //
        // Enviar imagenes (sobre)
        // *********************************************************************** //        
        $regcam = retornarRegistrosMysqliApi($mysqli, "mreg_rues_bloque_camaras", "idradicacionbloque=" . $reg["id"], "camararesponsable");
        if ($regcam === false || empty($regcam)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizaron las Cámaras de Comercio donde debe ser radicado el trámite';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        foreach ($regcam as $cam) {
            if ($cam["numerointernorue"] != '' && $cam["generadosobre"] != '' && $cam["cargadosobre"] == '') {

                // Verificar que el NUC tenga estado 02
                $estado = '';
                $res = \funcionesRues::consumirRR07N($cam["numerointernorue"]);
                if ($res) {
                    $ruta = \funcionesRues::ajustarArregloRues($res["respuesta_busqueda"]);
                    foreach ($ruta as $l) {
                        $estado = $l["estado_transaccion"];
                    }
                }
                if ($estado == '02') {
                    // Cargar sobre a s3
                    // Enviar estado 03
                    $res = \funcionesRues::consumirMR03N($cam["numerointernorue"], '03', $cam["generadosobre"]);
                    if ($res) {

                        // actualizar mreg_rues_bloque_camaras
                        $arrCampos = array(
                            'cargadosobre'
                        );
                        $arrValores = array(
                            "'SI'"
                        );
                        $condicion = "idradicacionbloque=" . $reg["id"] . " and camararesponsable='" . $cam["camararesponsable"] . "'";
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque_camaras', $arrCampos, $arrValores, $condicion);

                        // Actualiza mreg_rue_radicacion y mreg_rue_radicacion_estados a estado 3
                        $arrCampos = array(
                            'estadotransaccion',
                            'fecharespuesta',
                            'horarespuesta'
                        );

                        $arrValores = array(
                            "'03'",
                            "'" . date("Ymd") . "'",
                            "'" . date("His") . "'"
                        );
                        $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion', $arrCampos, $arrValores, "numerointernorue='" . $cam["numerointernorue"] . "'");

                        $arrCampos = array(
                            'numerointernorue',
                            'fecha',
                            'hora',
                            'usuario',
                            'estado',
                            'origen'
                        );
                        $arrValores = array(
                            "'" . $cam["numerointernorue"] . "'",
                            "'" . date("Ymd") . "'",
                            "'" . date("His") . "'",
                            "'" . $_SESSION["generales"]["codigousuario"] . "'",
                            "'03'",
                            "'RadSinCosB'"
                        );
                        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion_estados', $arrCampos, $arrValores);
                    }
                }
            }
        }

        //
        $regcam = retornarRegistrosMysqliApi($mysqli, "mreg_rues_bloque_camaras", "idradicacionbloque=" . $reg["id"], "camararesponsable");
        if ($regcam === false || empty($regcam)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizaron las Cámaras de Comercio donde debe ser radicado el trámite';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $camarasseleccionadas = 0;
        $camarasenviadas01 = 0;
        $camarasenviadas03 = 0;
        foreach ($regcam as $cx) {
            $camarasseleccionadas++;
            if ($cx["numerorecibo"] != '') {
                $camarasenviadas01++;
            }
            if ($cx["cargadosobre"] != '') {
                $camarasenviadas03++;
            }
        }
        $estado = 'PE';
        if ($camarasseleccionadas != 0 && $camarasenviadas01 == $camarasseleccionadas && $camarasenviadas03 == $camarasseleccionadas) {
            $estado = 'TE';
        }
        $arrCampos = array(
            'camarasseleccionadas',
            'camarasenviadas01',
            'camarasenviadas03',
            'estado'
        );
        $arrValores = array(
            $camarasseleccionadas,
            $camarasenviadas01,
            $camarasenviadas03,
            "'" . $estado . "'"
        );
        regrabarRegistrosMysqliApi($mysqli, 'mreg_rues_bloque', $arrCampos, $arrValores, "id=" . $_SESSION["entrada"]["_id"]);


        // *********************************************************************** //
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
