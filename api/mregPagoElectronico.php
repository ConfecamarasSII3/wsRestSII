<?php

namespace api;

use api\API;

trait mregPagoElectronico {

    public function mregPagoElectronicoGrabarLiquidacion(API $api) {
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
        if (!$api->validarToken('mregPagoElectronicoGrabarLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregPagoElectronicoGrabarLiquidacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera las variables del arreglo $_SESSION["entrada1"]
        // ********************************************************************** //
        $vars = array();
        foreach ($_SESSION["entrada1"] as $key => $valor) {
            $vars[$key] = base64_decode($valor);
        }

        //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Recupera la liquidación
        // ********************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $vars["_numerorecuperacion"], 'NR');
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizó la liquidación (' . $vars["_numerorecuperacion"] . ')';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida el estado de la liquidación
        // ********************************************************************** //
        if ($_SESSION["tramite"]["idestado"] > '05') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación se encuentra en un estado que no permite ser modificado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Mueve las variables a la liquidación
        // ********************************************************************** //
        $_SESSION["tramite"]["sede"] = $vars["_sede"];
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = strtoupper($vars["_idtipoidentificacioncliente"]);
        $_SESSION["tramite"]["identificacioncliente"] = $vars["_identificacioncliente"];
        $_SESSION["tramite"]["nombrecliente"] = $vars["_nombrecliente"];
        $_SESSION["tramite"]["apellidocliente"] = $vars["_apellidocliente"];
        $_SESSION["tramite"]["apellido1cliente"] = $vars["_apellido1cliente"];
        $_SESSION["tramite"]["apellido2cliente"] = $vars["_apellido2cliente"];
        $_SESSION["tramite"]["nombre1cliente"] = $vars["nombre1cliente"];
        $_SESSION["tramite"]["nombre2cliente"] = $vars["nombre2cliente"];
        $_SESSION["tramite"]["direccion"] = $vars["_direccion"];
        $_SESSION["tramite"]["idmunicipio"] = $vars["_idmunicipio"];
        $_SESSION["tramite"]["codposcom"] = $vars["_codposcom"];
        $_SESSION["tramite"]["direccionnot"] = $vars["_direccionnot"];
        $_SESSION["tramite"]["idmunicipionot"] = $vars["_idmunicipionot"];
        $_SESSION["tramite"]["codposnot"] = $vars["_codposnot"];
        $_SESSION["tramite"]["telefono"] = $vars["_telefono"];
        $_SESSION["tramite"]["movil"] = $vars["_celular"];
        $_SESSION["tramite"]["pais"] = $vars["_pais"];
        $_SESSION["tramite"]["lenguaje"] = $vars["_lenguaje"];
        $_SESSION["tramite"]["email"] = $vars["_email"];
        $_SESSION["tramite"]["zonapostal"] = $vars["_zonapostal"];
        $_SESSION["tramite"]["codigoregimen"] = $vars["_codigoregimen"];
        $_SESSION["tramite"]["responsabilidadtributaria"] = $vars["_responsabilidadtributaria"];
        $_SESSION["tramite"]["responsabilidadfiscal"] = $vars["_responsabilidadfiscal"];
        $_SESSION["tramite"]["codigoimpuesto"] = $vars["_codigoimpuesto"];
        $_SESSION["tramite"]["nombreimpuesto"] = $vars["_nombreimpuesto"];
        $_SESSION["tramite"]["proyectocaja"] = $vars["_proyectocaja"];
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
            $_SESSION["tramite"]["apellidocliente"] = '';
            $_SESSION["tramite"]["nombre1cliente"] = '';
            $_SESSION["tramite"]["nombre2cliente"] = '';
            $_SESSION["tramite"]["apellido1cliente"] = '';
            $_SESSION["tramite"]["apellido2cliente"] = '';
        } else {
            $_SESSION["tramite"]["nombrecliente"] = $_SESSION["tramite"]["nombre1cliente"];
            if (trim($_SESSION["tramite"]["nombre2cliente"]) != '') {
                $_SESSION["tramite"]["nombrecliente"] .= $_SESSION["tramite"]["nombre2cliente"];
            }
            $_SESSION["tramite"]["apellidocliente"] = $_SESSION["tramite"]["apellido1cliente"];
            if (trim($_SESSION["tramite"]["nombre2cliente"]) != '') {
                $_SESSION["tramite"]["apellidocliente"] .= $_SESSION["tramite"]["apellido2cliente"];
            }
        }
        $_SESSION["tramite"]["nombre1pagador"] = $_SESSION["tramite"]["nombre1cliente"];
        $_SESSION["tramite"]["nombre2pagador"] = $_SESSION["tramite"]["nombre2cliente"];
        $_SESSION["tramite"]["apellido1pagador"] = $_SESSION["tramite"]["apellido1cliente"];
        $_SESSION["tramite"]["apellido2pagador"] = $_SESSION["tramite"]["apellido2cliente"];
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
            $_SESSION["tramite"]["nombrepagador"] = trim($_SESSION["tramite"]["nombrecliente"]);
            $_SESSION["tramite"]["apellidopagador"] = '';
        } else {
            $_SESSION["tramite"]["nombrepagador"] = trim($_SESSION["tramite"]["nombre1pagador"] . ' ' . $_SESSION["tramite"]["nombre2pagador"]);
            $_SESSION["tramite"]["apellidopagador"] = trim($_SESSION["tramite"]["apellido1pagador"] . ' ' . $_SESSION["tramite"]["apellido2pagador"]);
        }
        $_SESSION["tramite"]["tipoidentificacionpagador"] = $_SESSION["tramite"]["idtipoidentificacioncliente"];
        $_SESSION["tramite"]["identificacionpagador"] = $_SESSION["tramite"]["identificacioncliente"];
        $_SESSION["tramite"]["direccionpagador"] = $_SESSION["tramite"]["direccion"];
        $_SESSION["tramite"]["municipiopagador"] = $_SESSION["tramite"]["idmunicipio"];
        $_SESSION["tramite"]["telefonopagador"] = $_SESSION["tramite"]["telefono"];
        $_SESSION["tramite"]["movilpagador"] = $_SESSION["tramite"]["movil"];
        $_SESSION["tramite"]["emailpagador"] = $_SESSION["tramite"]["email"];
        $_SESSION["tramite"]["origen"] = 'electronico';

        //
        if ($_SESSION["tramite"]["codposcom"] == '' && $_SESSION["tramite"]["idmunicipio"] != '') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $_SESSION["tramite"]["idmunicipio"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($_SESSION["tramite"]["codposcom"] == '') {
                            $_SESSION["tramite"]["codposcom"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        //
        if ($_SESSION["tramite"]["codposnot"] == '' && $_SESSION["tramite"]["idmunicipionot"] != '') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $_SESSION["tramite"]["idmunicipionot"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($_SESSION["tramite"]["codposnot"] == '') {
                            $_SESSION["tramite"]["codposnot"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        // ************************************************************************** //
        // Graba la liquidación
        // ************************************************************************** //
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregPagoElectronicoBuscarIdentificacion(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No localizo la identificación';

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
        $api->validarParametro("idtipoidentificacioncliente", true);
        $api->validarParametro("identificacioncliente", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregPagoElectronicoBuscarIdentificacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregPagoElectronicoBuscarIdentificacion ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera de datos cliente
        // ********************************************************************** //
        $de = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["entrada"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["entrada"]["identificacioncliente"] . "'");
        if ($de && !empty($de)) {
            if ($_SESSION["entrada"]["idtipoidentificacioncliente"] == '2') {
                $_SESSION["jsonsalida"]["nombrecliente"] = $de["razonsocial"];
                $_SESSION["jsonsalida"]["apellido1cliente"] = '';
                $_SESSION["jsonsalida"]["apellido2cliente"] = '';
                $_SESSION["jsonsalida"]["nombre1cliente"] = '';
                $_SESSION["jsonsalida"]["nombre2cliente"] = '';
            } else {
                $_SESSION["jsonsalida"]["nombrecliente"] = $de["primerapellido"];
                if (trim($de["segundoapellido"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["segundoapellido"];
                }
                if (trim($de["primernombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["primernombre"];
                }
                if (trim($de["segundonombre"]) != '') {
                    $_SESSION["jsonsalida"]["nombrecliente"] .= ' ' . $de["segundonombre"];
                }
                $_SESSION["jsonsalida"]["apellido1cliente"] = $de["primerapellido"];
                $_SESSION["jsonsalida"]["apellido2cliente"] = $de["segundoapellido"];
                $_SESSION["jsonsalida"]["nombre1cliente"] = $de["primernombre"];
                $_SESSION["jsonsalida"]["nombre2cliente"] = $de["segundonombre"];
            }
            $_SESSION["jsonsalida"]["direccion"] = $de["dircom"];
            $_SESSION["jsonsalida"]["direccionnot"] = $de["dirnot"];
            $_SESSION["jsonsalida"]["idmunicipio"] = $de["muncom"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $de["munnot"];
            $_SESSION["jsonsalida"]["zonapostal"] = $de["zonapostal"];
            $_SESSION["jsonsalida"]["codposcom"] = $de["codposcom"];
            $_SESSION["jsonsalida"]["codposnot"] = $de["codposnot"];
            $_SESSION["jsonsalida"]["telefono"] = $de["telefono1"];
            $_SESSION["jsonsalida"]["movil"] = $de["telefono2"];
            $_SESSION["jsonsalida"]["pais"] = $de["pais"];
            $_SESSION["jsonsalida"]["lenguaje"] = strtolower($de["lenguaje"]);
            $_SESSION["jsonsalida"]["codigoregimen"] = $de["codigoregimen"];
            $_SESSION["jsonsalida"]["responsabilidadfiscal"] = $de["responsabilidadfiscal"];
            $_SESSION["jsonsalida"]["codigoimpuesto"] = $de["codigoimpuesto"];
            $_SESSION["jsonsalida"]["nombreimpuesto"] = $de["nombreimpuesto"];
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = $de["responsabilidadtributaria"];
            $_SESSION["jsonsalida"]["email"] = $de["email"];
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde datos_empresas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera de la bd
        // ********************************************************************** //
        $ins = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "idclase='" . $_SESSION["entrada"]["idtipoidentificacioncliente"] . "' and numid='" . $_SESSION["entrada"]["identificacioncliente"] . "'");
        if ($ins && !empty($ins)) {
            $_SESSION["jsonsalida"]["nombrecliente"] = $ins["razonsocial"];
            $_SESSION["jsonsalida"]["apellido1cliente"] = $ins["apellido1"];
            $_SESSION["jsonsalida"]["apellido2cliente"] = $ins["apellido2"];
            $_SESSION["jsonsalida"]["nombre1cliente"] = $ins["nombre1"];
            $_SESSION["jsonsalida"]["nombre2cliente"] = $ins["nombre2"];
            $_SESSION["jsonsalida"]["direccion"] = $ins["dircom"];
            $_SESSION["jsonsalida"]["direccionnot"] = $ins["dirnot"];
            $_SESSION["jsonsalida"]["idmunicipio"] = $ins["muncom"];
            $_SESSION["jsonsalida"]["idmunicipionot"] = $ins["munnot"];
            $_SESSION["jsonsalida"]["zonapostal"] = $ins["codigopostalcom"];
            $_SESSION["jsonsalida"]["codposcom"] = $ins["codigopostalcom"];
            $_SESSION["jsonsalida"]["codposnot"] = $ins["codigopostalnot"];
            $_SESSION["jsonsalida"]["telefono"] = $ins["telcom1"];
            $_SESSION["jsonsalida"]["movil"] = $ins["telcom2"];
            $_SESSION["jsonsalida"]["pais"] = 'CO';
            $_SESSION["jsonsalida"]["lenguaje"] = 'es';
            $_SESSION["jsonsalida"]["email"] = $ins["emailcom"];
            if ($ins["idclase"] == '2') {
                $_SESSION["jsonsalida"]["codigoregimen"] = '48';
                $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PJ';
                $_SESSION["jsonsalida"]["codigoimpuesto"] = '01';
                $_SESSION["jsonsalida"]["nombreimpuesto"] = 'IVA';
            } else {
                $_SESSION["jsonsalida"]["codigoregimen"] = '49';
                $_SESSION["jsonsalida"]["responsabilidadfiscal"] = 'R-99-PN';
                $_SESSION["jsonsalida"]["codigoimpuesto"] = '';
                $_SESSION["jsonsalida"]["nombreimpuesto"] = '';
            }
            $_SESSION["jsonsalida"]["responsabilidadtributaria"] = '';
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Desde mreg_est_inscritos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
    }

    public function mregPagoElectronicoAsignarClientePagador(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No localizo la identificación';

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
        $api->validarParametro("parametros", true);

        //
        $pagobancos = '';
        $linkpagos = '';
        $numliq = '';
        $pars = explode("|", base64_decode($_SESSION["entrada"]["parametros"]));
        foreach ($pars as $p) {
            list($key, $valor) = explode(':', $p);
            if ($key == 'idliquidacion') {
                $numliq = $valor;
            }
        }

        //
        if ($numliq == '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no encontrada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $numliq);

        //
        if ($numliq != $_SESSION["tramite"]["idliquidacion"]) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no corresponde';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $pagocargoprepago = 'NO';
        $claveprepago = '';
        $identificacionprepago = '';
        
        foreach ($pars as $p) {

            list($key, $valor) = explode(':', $p);
            // \logApi::general2('mregPagoElectronico', '', 'Parametros: ' . $key . ' => ' . $valor);

            if ($key == 'tipocliente') {
                $_SESSION["tramite"]["tipocliente"] = $valor;
            }
            if ($key == 'tipoidecliente') {
                $_SESSION["tramite"]["idtipoidentificacioncliente"] = $valor;
            }
            if ($key == 'identificacioncliente') {
                $_SESSION["tramite"]["identificacioncliente"] = $valor;
            }
            if ($key == 'razonsocialcliente') {
                $_SESSION["tramite"]["razonsocialcliente"] = $valor;
            }
            if ($key == 'nombre1cliente') {
                $_SESSION["tramite"]["nombre1cliente"] = $valor;
            }
            if ($key == 'nombre2cliente') {
                $_SESSION["tramite"]["nombre2cliente"] = $valor;
            }
            if ($key == 'apellido1cliente') {
                $_SESSION["tramite"]["apellido1cliente"] = $valor;
            }
            if ($key == 'apellido2cliente') {
                $_SESSION["tramite"]["apellido2cliente"] = $valor;
            }
            if ($key == 'direccioncliente') {
                $_SESSION["tramite"]["direccion"] = $valor;
            }
            if ($key == 'municipiocliente') {
                $_SESSION["tramite"]["idmunicipio"] = $valor;
            }
            if ($key == 'codposcomcliente') {
                $_SESSION["tramite"]["codposcom"] = $valor;
            }

            if ($key == 'direccionnotcliente') {
                $_SESSION["tramite"]["direccionnot"] = $valor;
            }
            if ($key == 'municipionotcliente') {
                $_SESSION["tramite"]["idmunicipionot"] = $valor;
            }
            if ($key == 'codposnotcliente') {
                $_SESSION["tramite"]["codposnot"] = $valor;
            }

            if ($key == 'telefonocliente') {
                $_SESSION["tramite"]["telefono"] = $valor;
            }
            if ($key == 'movilcliente') {
                $_SESSION["tramite"]["movil"] = $valor;
            }
            if ($key == 'emailcliente') {
                $_SESSION["tramite"]["email"] = $valor;
            }
            if ($key == 'paiscliente') {
                $_SESSION["tramite"]["pais"] = $valor;
            }
            if ($key == 'lenguajecliente') {
                $_SESSION["tramite"]["lenguaje"] = $valor;
            }
            if ($key == 'zonapostalcliente') {
                $_SESSION["tramite"]["zonapostal"] = $valor;
            }

            if ($key == 'codigoregimencliente') {
                $_SESSION["tramite"]["codigoregimen"] = $valor;
            }
            if ($key == 'responsabilidadfiscalcliente') {
                $_SESSION["tramite"]["responsabilidadfiscal"] = $valor;
            }
            if ($key == 'codigoimpuestocliente') {
                $_SESSION["tramite"]["codigoimpuesto"] = $valor;
            }
            if ($key == 'nombreimpuestocliente') {
                $_SESSION["tramite"]["nombreimpuesto"] = $valor;
            }
            if ($key == 'responsabilidadtributariacliente') {
                $_SESSION["tramite"]["responsabilidadtributaria"] = $valor;
            }

            if ($key == 'tipopagador') {
                $_SESSION["tramite"]["tipopagador"] = $valor;
            }
            if ($key == 'tipoidepagador') {
                $_SESSION["tramite"]["tipoidentificacionpagador"] = $valor;
            }
            if ($key == 'identificacionpagador') {
                $_SESSION["tramite"]["identificacionpagador"] = $valor;
            }
            if ($key == 'razonsocialpagador') {
                $_SESSION["tramite"]["razonsocialpagador"] = $valor;
            }
            if ($key == 'nombre1pagador') {
                $_SESSION["tramite"]["nombre1pagador"] = $valor;
            }
            if ($key == 'nombre2pagador') {
                $_SESSION["tramite"]["nombre2pagador"] = $valor;
            }
            if ($key == 'apellido1pagador') {
                $_SESSION["tramite"]["apellido1pagador"] = $valor;
            }
            if ($key == 'apellido2pagador') {
                $_SESSION["tramite"]["apellido2pagador"] = $valor;
            }
            if ($key == 'direccionpagador') {
                $_SESSION["tramite"]["direccionpagador"] = $valor;
            }
            if ($key == 'municipiopagador') {
                $_SESSION["tramite"]["municipiopagador"] = $valor;
            }
            if ($key == 'telefonopagador') {
                $_SESSION["tramite"]["telefonopagador"] = $valor;
            }
            if ($key == 'movilpagador') {
                $_SESSION["tramite"]["movilpagador"] = $valor;
            }
            if ($key == 'emailpagador') {
                $_SESSION["tramite"]["emailpagador"] = $valor;
            }
            if ($key == 'pagobancos') {
                $pagobancos = $valor;
            }
            if ($key == 'linkpagos') {
                $linkpagos = $valor;
            }
            if ($key == 'pagocargoprepago') {
                $pagocargoprepago = $valor;
            }
            if ($key == 'claveprepago') {
                $claveprepago = $valor;
            }
            if ($key == 'identificacionprepago') {
                $identificacionprepago = $valor;
            }
            if ($key == 'gateway') {
                $_SESSION["tramite"]["gateway"] = $valor;
            }
        }
        
        //
        if (trim($_SESSION["tramite"]["apellido1cliente"]) == '') {
            $tipopersona = '1';
        } else {
            $tipopersona = '2';
        }
        if (!isset($_SESSION["tramite"]["responsabilidadtributaria"])) {
            $_SESSION["tramite"]["responsabilidadtributaria"] = '';
        }
        if (!isset($_SESSION["tramite"]["responsabilidadfiscal"])) {
            $_SESSION["tramite"]["responsabilidadfiscal"] = '';
        }
        if (!isset($_SESSION["tramite"]["codigoregimen"])) {
            $_SESSION["tramite"]["codigoregimen"] = '';
        }
        if (!isset($_SESSION["tramite"]["codigoimpuesto"])) {
            $_SESSION["tramite"]["codigoimpuesto"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreimpuesto"])) {
            $_SESSION["tramite"]["nombreimpuesto"] = '';
        }
        if (!isset($_SESSION["tramite"]["zonapostal"])) {
            $_SESSION["tramite"]["zonapostal"] = '';
        }
        if (!isset($_SESSION["tramite"]["pais"])) {
            $_SESSION["tramite"]["pais"] = '';
        }
        if (!isset($_SESSION["tramite"]["lenguaje"])) {
            $_SESSION["tramite"]["lenguaje"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionnot"])) {
            $_SESSION["tramite"]["direccionnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["idmunicipionot"])) {
            $_SESSION["tramite"]["idmunicipionot"] = '';
        }
        if (!isset($_SESSION["tramite"]["codposnot"])) {
            $_SESSION["tramite"]["codposnot"] = '';
        }

        if (!isset($_SESSION["tramite"]["emailenviocertificados"])) {
            $_SESSION["tramite"]["emailenviocertificados"] = '';
        }

        //
        if ($_SESSION["tramite"]["codposcom"] == '' && $_SESSION["tramite"]["idmunicipio"] != '') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $_SESSION["tramite"]["idmunicipio"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($_SESSION["tramite"]["codposcom"] == '') {
                            $_SESSION["tramite"]["codposcom"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        //
        if ($_SESSION["tramite"]["codposnot"] == '' && $_SESSION["tramite"]["idmunicipionot"] != '') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $_SESSION["tramite"]["idmunicipionot"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($_SESSION["tramite"]["codposnot"] == '') {
                            $_SESSION["tramite"]["codposnot"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        //
        if ($pagobancos != 'SI') {
            if ($linkpagos != 'SI') {
                $_SESSION["tramite"]["idestado"] = '06';
            }
        }

        // \logApi::general2('mregPagoElectronico', '', 'Estado al pasar a pago: ' . $_SESSION["tramite"]["idestado"]);
        // \logApi::general2('mregPagoElectronico', '', '');
        //
        if ($_SESSION["tramite"]["tipocliente"] == 'PJ') {
            $_SESSION["tramite"]["nombrecliente"] = $_SESSION["tramite"]["razonsocialcliente"];
            $_SESSION["tramite"]["apellidocliente"] = '';
        }

        if ($_SESSION["tramite"]["tipocliente"] == 'PN') {
            $_SESSION["tramite"]["nombrecliente"] = trim($_SESSION["tramite"]["nombre1cliente"] . ' ' . $_SESSION["tramite"]["nombre2cliente"]);
            $_SESSION["tramite"]["apellidocliente"] = trim($_SESSION["tramite"]["apellido1cliente"] . ' ' . $_SESSION["tramite"]["apellido2cliente"]);
        }

        if ($_SESSION["tramite"]["tipopagador"] == 'PJ') {
            $_SESSION["tramite"]["nombrepagador"] = $_SESSION["tramite"]["razonsocialpagador"];
            $_SESSION["tramite"]["apellidopagador"] = '';
        }

        if ($_SESSION["tramite"]["tipopagador"] == 'PN') {
            $_SESSION["tramite"]["nombrepagador"] = trim($_SESSION["tramite"]["nombre1pagador"] . ' ' . $_SESSION["tramite"]["nombre2pagador"]);
            $_SESSION["tramite"]["apellidopagador"] = trim($_SESSION["tramite"]["apellido1pagador"] . ' ' . $_SESSION["tramite"]["apellido2pagador"]);
        }

        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        // Graba datos_empresas
        // Almacen ala tabla datos_empresas
        $arrCampos = array(
            'tipoidentificacion',
            'identificacion',
            'tipopersona',
            'razonsocial',
            'nombreregistrado',
            'primernombre',
            'segundonombre',
            'primerapellido',
            'segundoapellido',
            'particula',
            'email',
            'responsabilidadtributaria',
            'codigoregimen',
            'codigoimpuesto',
            'nombreimpuesto',
            'responsabilidadfiscal',
            'telefono1',
            'telefono2',
            'zonapostal',
            'pais',
            'lenguaje',
            'dircom',
            'muncom',
            'codposcom',
            'dirnot',
            'munnot',
            'codposnot'
        );

        $arrValores = array(
            "'" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'",
            "'" . $_SESSION["tramite"]["identificacioncliente"] . "'",
            "'" . $tipopersona . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"])) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"])) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre1cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre2cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido1cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido2cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["particulacliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["email"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["responsabilidadtributaria"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codigoregimen"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codigoimpuesto"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombreimpuesto"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["responsabilidadfiscal"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["telefono"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["movil"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["zonapostal"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["pais"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["lenguaje"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["direccion"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["idmunicipio"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codposcom"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["direccionnot"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["idmunicipionot"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codposnot"]) . "'"
        );
        if (contarRegistrosMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'") == 0) {
            insertarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores);
        } else {
            regrabarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores, "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'");
        }

        //
        if ($pagocargoprepago == 'SI') {
            if ($claveprepago == '' || $identificacionprepago == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar los datos del prepago para continuar.';
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            $prep = \funcionesRegistrales::actualizarPrepago($mysqli, 'A', $identificacionprepago, $claveprepago);
            if ($prep["codigoError"] != '0000') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No es posible continuar : ' . $prep["msgError"];
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            if ($prep["saldoprepago"] < $_SESSION["tramite"]["valortotal"]) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No tiene cupo suficiente para pagar con cargo al prepago.';
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } 

        //
        $mysqli->close();

        // En caso de tu compra y siempe y cuando la CC tenga habilitado el servicio web de consulta
        if (isset($_SESSION["entrada"]["codigofacturatucompra"]) && trim($_SESSION["entrada"]["codigofacturatucompra"]) != '') {
            if (defined('TUCOMPRA_URL_WS') && TUCOMPRA_URLWS != '') {
                if (defined('TUCOMPRA_USER_WS') && TUCOMPRA_USER_WS != '') {
                    if (defined('TUCOMPRA_PASSWORD_WS') && TUCOMPRA_PASSWORD_WS != '') {
                        // Aqui se incluye el consumo del servicio web de tucompra
                    }
                }
            }
        }

        //
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = 'OK';
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregPagoElectronicoValidarPrepago(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No localizo la identificación';

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
        $api->validarParametro("parametros", true);

        //
        $_SESSION["tramite"]["aceptadoprepago"] = 'NO';

        //
        $pagobancos = '';
        $numliq = '';
        $pars = explode("|", base64_decode($_SESSION["vars"]["parametros"]));
        foreach ($pars as $p) {
            list($key, $valor) = explode(':', $p);
            if ($key == 'idliquidacion') {
                $numliq = $valor;
            }
        }

        //
        if ($numliq == '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no encontrada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $numliq);

        //
        if ($numliq != $_SESSION["tramite"]["idliquidacion"]) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no corresponde';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["tramite"]["aceptadoprepago"] = 'NO';

        //
        foreach ($pars as $p) {

            list($key, $valor) = explode(':', $p);
            \logApi::general2('mregPagoElectronico', '', 'Parametros: ' . $key . ' => ' . $valor);

            if ($key == 'tipocliente') {
                $_SESSION["tramite"]["tipocliente"] = $valor;
            }
            if ($key == 'tipoidecliente') {
                $_SESSION["tramite"]["idtipoidentificacioncliente"] = $valor;
            }
            if ($key == 'identificacioncliente') {
                $_SESSION["tramite"]["identificacioncliente"] = $valor;
            }
            if ($key == 'razonsocialcliente') {
                $_SESSION["tramite"]["razonsocialcliente"] = $valor;
            }
            if ($key == 'nombre1cliente') {
                $_SESSION["tramite"]["nombre1cliente"] = $valor;
            }
            if ($key == 'nombre2cliente') {
                $_SESSION["tramite"]["nombre2cliente"] = $valor;
            }
            if ($key == 'apellido1cliente') {
                $_SESSION["tramite"]["apellido1cliente"] = $valor;
            }
            if ($key == 'apellido2cliente') {
                $_SESSION["tramite"]["apellido2cliente"] = $valor;
            }
            if ($key == 'direccioncliente') {
                $_SESSION["tramite"]["direccion"] = $valor;
            }
            if ($key == 'municipiocliente') {
                $_SESSION["tramite"]["idmunicipio"] = $valor;
            }
            if ($key == 'codposcomcliente') {
                $_SESSION["tramite"]["codposcom"] = $valor;
            }

            if ($key == 'direccionnotcliente') {
                $_SESSION["tramite"]["direccionnot"] = $valor;
            }
            if ($key == 'municipionotcliente') {
                $_SESSION["tramite"]["idmunicipionot"] = $valor;
            }
            if ($key == 'codposnotcliente') {
                $_SESSION["tramite"]["codposnot"] = $valor;
            }

            if ($key == 'telefonocliente') {
                $_SESSION["tramite"]["telefono"] = $valor;
            }
            if ($key == 'movilcliente') {
                $_SESSION["tramite"]["movil"] = $valor;
            }
            if ($key == 'emailcliente') {
                $_SESSION["tramite"]["email"] = $valor;
            }
            if ($key == 'paiscliente') {
                $_SESSION["tramite"]["pais"] = $valor;
            }
            if ($key == 'lenguajelcliente') {
                $_SESSION["tramite"]["lenguaje"] = $valor;
            }
            if ($key == 'zonapostalcliente') {
                $_SESSION["tramite"]["zonapostal"] = $valor;
            }

            if ($key == 'codigoregimencliente') {
                $_SESSION["tramite"]["codigoregimen"] = $valor;
            }
            if ($key == 'responsabilidadfiscalcliente') {
                $_SESSION["tramite"]["responsabilidadfiscal"] = $valor;
            }
            if ($key == 'codigoimpuestocliente') {
                $_SESSION["tramite"]["codigoimpuesto"] = $valor;
            }
            if ($key == 'nombreimpuestocliente') {
                $_SESSION["tramite"]["nombreimpuesto"] = $valor;
            }
            if ($key == 'responsabilidadtributariacliente') {
                $_SESSION["tramite"]["responsabilidadtributaria"] = $valor;
            }

            if ($key == 'tipopagador') {
                $_SESSION["tramite"]["tipopagador"] = $valor;
            }
            if ($key == 'tipoidepagador') {
                $_SESSION["tramite"]["tipoidentificacionpagador"] = $valor;
            }
            if ($key == 'identificacionpagador') {
                $_SESSION["tramite"]["identificacionpagador"] = $valor;
            }
            if ($key == 'razonsocialpagador') {
                $_SESSION["tramite"]["razonsocialpagador"] = $valor;
            }
            if ($key == 'nombre1pagador') {
                $_SESSION["tramite"]["nombre1pagador"] = $valor;
            }
            if ($key == 'nombre2pagador') {
                $_SESSION["tramite"]["nombre2pagador"] = $valor;
            }
            if ($key == 'apellido1pagador') {
                $_SESSION["tramite"]["apellido1pagador"] = $valor;
            }
            if ($key == 'apellido2pagador') {
                $_SESSION["tramite"]["apellido2pagador"] = $valor;
            }
            if ($key == 'direccionpagador') {
                $_SESSION["tramite"]["direccionpagador"] = $valor;
            }
            if ($key == 'municipiopagador') {
                $_SESSION["tramite"]["municipiopagador"] = $valor;
            }
            if ($key == 'telefonopagador') {
                $_SESSION["tramite"]["telefonopagador"] = $valor;
            }
            if ($key == 'movilpagador') {
                $_SESSION["tramite"]["movilpagador"] = $valor;
            }
            if ($key == 'emailpagador') {
                $_SESSION["tramite"]["emailpagador"] = $valor;
            }
            if ($key == 'pagobancos') {
                $pagobancos = $valor;
            }
            if ($key == 'identificacionprepago') {
                $_SESSION["tramite"]["identificacionprepago"] = $valor;
            }
            if ($key == 'claveprepago') {
                $_SESSION["tramite"]["claveprepago"] = $valor;
            }
        }

        //
        if ($pagobancos != 'SI') {
            $_SESSION["tramite"]["idestado"] = '05';
        }

        \logApi::general2('mregPagoElectronico', '', 'Estado al pasar a pago: ' . $_SESSION["tramite"]["idestado"]);
        \logApi::general2('mregPagoElectronico', '', '');

        //
        if ($_SESSION["tramite"]["tipocliente"] == 'PJ') {
            $_SESSION["tramite"]["nombrecliente"] = $_SESSION["tramite"]["razonsocialcliente"];
            $_SESSION["tramite"]["apellidocliente"] = '';
        }

        if ($_SESSION["tramite"]["tipocliente"] == 'PN') {
            $_SESSION["tramite"]["nombrecliente"] = trim($_SESSION["tramite"]["nombre1cliente"] . ' ' . $_SESSION["tramite"]["nombre2cliente"]);
            $_SESSION["tramite"]["apellidocliente"] = trim($_SESSION["tramite"]["apellido1cliente"] . ' ' . $_SESSION["tramite"]["apellido2cliente"]);
        }

        if ($_SESSION["tramite"]["tipopagador"] == 'PJ') {
            $_SESSION["tramite"]["nombrepagador"] = $_SESSION["tramite"]["razonsocialpagador"];
            $_SESSION["tramite"]["apellidopagador"] = '';
        }

        if ($_SESSION["tramite"]["tipopagador"] == 'PN') {
            $_SESSION["tramite"]["nombrepagador"] = trim($_SESSION["tramite"]["nombre1pagador"] . ' ' . $_SESSION["tramite"]["nombre2pagador"]);
            $_SESSION["tramite"]["apellidopagador"] = trim($_SESSION["tramite"]["apellido1pagador"] . ' ' . $_SESSION["tramite"]["apellido2pagador"]);
        }

        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        // Almacen ala tabla datos_empresas
        $arrCampos = array(
            'tipoidentificacion',
            'identificacion',
            'tipopersona',
            'razonsocial',
            'nombreregistrado',
            'primernombre',
            'segundonombre',
            'primerapellido',
            'segundoapellido',
            'particula',
            'email',
            'responsabilidadtributaria',
            'codigoregimen',
            'codigoimpuesto',
            'nombreimpuesto',
            'responsabilidadfiscal',
            'telefono1',
            'telefono2',
            'zonapostal',
            'pais',
            'lenguaje',
            'dircom',
            'muncom',
            'codposcom',
            'dirnot',
            'munnot',
            'codposnot'
        );
        if (trim($_SESSION["tramite"]["apellido1cliente"]) == '') {
            $tipopersona = '1';
        } else {
            $tipopersona = '2';
        }
        if (!isset($_SESSION["tramite"]["responsabilidadtributaria"])) {
            $_SESSION["tramite"]["responsabilidadtributaria"] = '';
        }
        if (!isset($_SESSION["tramite"]["responsabilidadfiscal"])) {
            $_SESSION["tramite"]["responsabilidadfiscal"] = '';
        }
        if (!isset($_SESSION["tramite"]["codigoregimen"])) {
            $_SESSION["tramite"]["codigoregimen"] = '';
        }
        if (!isset($_SESSION["tramite"]["codigoimpuesto"])) {
            $_SESSION["tramite"]["codigoimpuesto"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreimpuesto"])) {
            $_SESSION["tramite"]["nombreimpuesto"] = '';
        }
        if (!isset($_SESSION["tramite"]["zonapostal"])) {
            $_SESSION["tramite"]["zonapostal"] = '';
        }
        if (!isset($_SESSION["tramite"]["pais"])) {
            $_SESSION["tramite"]["pais"] = '';
        }
        if (!isset($_SESSION["tramite"]["lenguaje"])) {
            $_SESSION["tramite"]["lenguaje"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionnot"])) {
            $_SESSION["tramite"]["direccionnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["idmunicipionot"])) {
            $_SESSION["tramite"]["idmunicipionot"] = '';
        }
        if (!isset($_SESSION["tramite"]["codposnot"])) {
            $_SESSION["tramite"]["codposnot"] = '';
        }

        $arrValores = array(
            "'" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'",
            "'" . $_SESSION["tramite"]["identificacioncliente"] . "'",
            "'" . $tipopersona . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"])) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombrecliente"] . ' ' . $_SESSION["tramite"]["apellidocliente"])) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre1cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre2cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido1cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido2cliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["particulacliente"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["email"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["responsabilidadtributaria"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codigoregimen"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codigoimpuesto"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombreimpuesto"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["responsabilidadfiscal"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["telefono"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["movil"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["zonapostal"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["pais"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["lenguaje"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["direccion"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["idmunicipio"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codposcom"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["direccionnot"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["idmunicipionot"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["codposnot"]) . "'"
        );
        if (contarRegistrosMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'") == 0) {
            insertarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores);
        } else {
            regrabarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores, "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'");
        }


        $prep = retornarRegistroMysqliApi($mysqli, 'mreg_prepagos', "identificacion='" . $_SESSION["tramite"]["identificacionprepago"] . "'");
        if ($prep === false || empty($prep)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontro prepago para la identificacion seleccionada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //WSI - 2017-03-26 Ajuste validación prepago luego de revisión de md5 en tabla mreg_prepago
        if ($prep["clave"] != md5($_SESSION["tramite"]["claveprepago"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave asignada al prepago no es la correcta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $saldoPrep = 0;
        $preps = retornarRegistrosMysqliApi($mysqli, 'mreg_prepagos_uso', "identificacion='" . $_SESSION["tramite"]["identificacionprepago"] . "'");
        if ($preps && !empty($preps)) {
            foreach ($preps as $p) {
                if ($p["tipomov"] == 'C') {
                    $saldoPrep = $saldoPrep + $p["valor"];
                } else {
                    $saldoPrep = $saldoPrep - $p["valor"];
                }
            }
        }
        if ($saldoPrep < $_SESSION["tramite"]["valortotal"]) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El saldo del prepago (' . $saldoPrep . ') no es suficiente para cubrir el costo del servicio';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["tramite"]["aceptadoprepago"] = 'SI';
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        //
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = 'OK';
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

    public function mregPagoElectronicoValidarNit(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'No localizo la identificación';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacioncliente", true);

        //
        $ide = base64_decode($_SESSION["entrada"]["identificacioncliente"]);
        if (ltrim(trim($ide), "0") == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificación vacia';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $sepide = \funcionesGenerales::separarDv($ide);
        $dv = \funcionesGenerales::calcularDv($sepide["identificacion"]);
        if ($dv != $sepide["dv"]) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Nit incorrecto, valide el dígito de verificación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
    }

    public function mregPagoElectronicoValidarAni(API $api) {
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
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacioncliente", true);

        //
        $ide = base64_decode($_SESSION["entrada"]["identificacioncliente"]);
        $nom1 = base64_decode($_SESSION["entrada"]["nombre1cliente"]);
        $nom2 = base64_decode($_SESSION["entrada"]["nombre2cliente"]);
        $ape1 = base64_decode($_SESSION["entrada"]["apellido1cliente"]);
        $ape2 = base64_decode($_SESSION["entrada"]["apellido2cliente"]);

        $nomx = trim((string) $ape1);
        if (trim((string) $ape2) != '') {
            $nomx .= ' ' . $ape2;
        }
        if (trim((string) $nom1) != '') {
            $nomx .= ' ' . $nom1;
        }
        if (trim((string) $nom2) != '') {
            $nomx .= ' ' . $nom2;
        }

        if (ltrim(trim($ide), "0") == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificación vacia';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $res = \funcionesRues::consumirANI2(null, '1', $ide);
        if ($res["codigoerror"] == '0000') {
            if ($res["codError"] == '0') {
                $nomreg = trim((string) $res["primerApellido"]);
                if (trim((string) $res["particula"]) != '') {
                    $nomreg .= ' ' . trim((string) $res["particula"]);
                }
                if (trim((string) $res["segundoApellido"]) != '') {
                    $nomreg .= ' ' . trim((string) $res["segundoApellido"]);
                }
                if (trim((string) $res["primerNombre"]) != '') {
                    $nomreg .= ' ' . trim((string) $res["primerNombre"]);
                }
                if (trim((string) $res["segundoNombre"]) != '') {
                    $nomreg .= ' ' . trim((string) $res["segundoNombre"]);
                }
                if ($nomx == $nomreg) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificación verificada';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Revise la identificación y los nombres y apellidos, la información digitada no concuerda contra la informada por la registraduría (' . $nomx . ') (' . $nomreg . ')' . "\r\n";
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    exit();
                }
            } else {
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit();
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificación no encontrada en la registraduría';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }
    }

}
