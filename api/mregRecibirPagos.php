<?php

namespace api;

use api\API;

trait mregRecibirPagos {

    public function mregRecibirPagosGrabarLiquidacion(API $api) {
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
        if (!$api->validarToken('mregRecibirPagosGrabarLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesGrabarSolicitudBloque ';
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
        if (!isset($_SESSION["tramite"]["idliquidacion"]) || $_SESSION["tramite"]["idliquidacion"] == '') {
            $_SESSION["tramite"]["idliquidacion"] = 0;
        }
        if (!isset($_SESSION["tramite"]["numeroliquidacion"]) || $_SESSION["tramite"]["numeroliquidacion"] == '') {
            $_SESSION["tramite"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        }
        $_SESSION["tramite"]["cantidadfolios"] = intval($vars["_cantidadfolios"]);
        $_SESSION["tramite"]["cantidadhojas"] = intval($vars["_cantidadhojas"]);
        $_SESSION["tramite"]["sede"] = $vars["_sede"];

        $_SESSION["tramite"]["idtipoidentificacioncliente"] = strtoupper($vars["_idtipoidentificacioncliente"]);
        $_SESSION["tramite"]["identificacioncliente"] = $vars["_identificacioncliente"];

        $_SESSION["tramite"]["nombrecliente"] = $vars["_nombrecliente"];
        $_SESSION["tramite"]["apellidocliente"] = $vars["_apellidocliente"];
        $_SESSION["tramite"]["apellido1cliente"] = $vars["_apellido1cliente"];
        $_SESSION["tramite"]["apellido2cliente"] = $vars["_apellido2cliente"];
        $_SESSION["tramite"]["nombre1cliente"] = $vars["_nombre1cliente"];
        $_SESSION["tramite"]["nombre2cliente"] = $vars["_nombre2cliente"];

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
        $_SESSION["tramite"]["proyectocaja"] = $vars["_proyectocaja"];
        $_SESSION["tramite"]["enviara"] = $vars["_enviara"];
        $_SESSION["tramite"]["emailenviocertificados"] = $vars["_emailenviocertificados"];

        //
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
            if (trim($_SESSION["tramite"]["apellido2cliente"]) != '') {
                $_SESSION["tramite"]["apellidocliente"] .= $_SESSION["tramite"]["apellido2cliente"];
            }
        }

        $_SESSION["tramite"]["nrocontrolsipref"] = $vars["_nrocontrolsipref"];
        $_SESSION["tramite"]["tipoideradicador"] = $vars["_tipoideradicador"];
        $_SESSION["tramite"]["ideradicador"] = $vars["_ideradicador"];
        $_SESSION["tramite"]["fechaexpradicador"] = $vars["_fechaexpradicador"];
        $_SESSION["tramite"]["nombreradicador"] = $vars["_nombreradicador"];
        $_SESSION["tramite"]["emailradicador"] = $vars["_emailradicador"];
        $_SESSION["tramite"]["telefonoradicador"] = $vars["_telefonoradicador"];
        $_SESSION["tramite"]["celularradicador"] = $vars["_celularradicador"];
        $_SESSION["tramite"]["motivoevidenciafotografica"] = $vars["_motivoevidenciafotografica"];

        $_SESSION["tramite"]["nombre1pagador"] = $_SESSION["tramite"]["nombre1cliente"];
        $_SESSION["tramite"]["nombre2pagador"] = $_SESSION["tramite"]["nombre2cliente"];
        $_SESSION["tramite"]["apellido1pagador"] = $_SESSION["tramite"]["apellido1cliente"];
        $_SESSION["tramite"]["apellido2pagador"] = $_SESSION["tramite"]["apellido2cliente"];

        //WSI
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
            $_SESSION["tramite"]["nombrepagador"] = trim($_SESSION["tramite"]["nombrecliente"]);
            $_SESSION["tramite"]["apellidopagador"] = '';
        } else {
            $_SESSION["tramite"]["nombrepagador"] = trim($_SESSION["tramite"]["nombre1pagador"] . ' ' . $_SESSION["tramite"]["nombre2pagador"]);
            $_SESSION["tramite"]["apellidopagador"] = trim($_SESSION["tramite"]["apellido1pagador"] . ' ' . $_SESSION["tramite"]["apellido2pagador"]);
        }

        //
        $_SESSION["tramite"]["tipoidentificacionpagador"] = $_SESSION["tramite"]["idtipoidentificacioncliente"];
        $_SESSION["tramite"]["identificacionpagador"] = $_SESSION["tramite"]["identificacioncliente"];
        $_SESSION["tramite"]["direccionpagador"] = $_SESSION["tramite"]["direccion"];
        $_SESSION["tramite"]["municipiopagador"] = $_SESSION["tramite"]["idmunicipio"];
        $_SESSION["tramite"]["telefonopagador"] = $_SESSION["tramite"]["telefono"];
        $_SESSION["tramite"]["movilpagador"] = $_SESSION["tramite"]["movil"];
        $_SESSION["tramite"]["emailpagador"] = $_SESSION["tramite"]["email"];
        $_SESSION["tramite"]["origen"] = 'presencial';

        //
        $_SESSION["tramite"]["idcodban"] = $vars["_idcodban"];
        $_SESSION["tramite"]["numerocheque"] = $vars["_numerocheque"];

        //
        $autach = $vars["_autorizacionach"];
        $auttd = $vars["_autorizaciontd"];
        $auttc = $vars["_autorizaciontc"];
        $fraach = $vars["_idfranquiciaach"];
        $fratd = $vars["_idfranquiciatd"];
        $fratc = $vars["_idfranquiciatc"];

        //
        if (trim($vars["_pagoafiliado"]) == '') {
            $_SESSION["tramite"]["pagoafiliado"] = 0;
        } else {
            $_SESSION["tramite"]["pagoafiliado"] = \funcionesGenerales::retornarValorLimpio($vars["_pagoafiliado"]);
        }

        if (trim($vars["_pagoefectivo"]) == '') {
            $_SESSION["tramite"]["pagoefectivo"] = 0;
        } else {
            $_SESSION["tramite"]["pagoefectivo"] = \funcionesGenerales::retornarValorLimpio($vars["_pagoefectivo"]);
        }

        if (trim($vars["_pagocheque"]) == '') {
            $_SESSION["tramite"]["pagocheque"] = 0;
        } else {
            $_SESSION["tramite"]["pagocheque"] = \funcionesGenerales::retornarValorLimpio($vars["_pagocheque"]);
        }

        if (trim($vars["_pagoconsignacion"]) == '') {
            $_SESSION["tramite"]["pagoconsignacion"] = 0;
        } else {
            $_SESSION["tramite"]["pagoconsignacion"] = \funcionesGenerales::retornarValorLimpio($vars["_pagoconsignacion"]);
        }
        
        if (!isset($vars["_pagoqr"]) || trim($vars["_pagoqr"]) == '') {
            $_SESSION["tramite"]["pagoqr"] = 0;
        } else {
            $_SESSION["tramite"]["pagoqr"] = \funcionesGenerales::retornarValorLimpio($vars["_pagoqr"]);
        }

        if (trim($vars["_pagotcredito"]) == '') {
            $_SESSION["tramite"]["pagotcredito"] = 0;
        } else {
            $_SESSION["tramite"]["pagotcredito"] = \funcionesGenerales::retornarValorLimpio($vars["_pagotcredito"]);
        }

        if (trim($vars["_pagotdebito"]) == '') {
            $_SESSION["tramite"]["pagotdebito"] = 0;
        } else {
            $_SESSION["tramite"]["pagotdebito"] = \funcionesGenerales::retornarValorLimpio($vars["_pagotdebito"]);
        }

        if (trim($vars["_pagoach"]) == '') {
            $_SESSION["tramite"]["pagoach"] = 0;
        } else {
            $_SESSION["tramite"]["pagoach"] = \funcionesGenerales::retornarValorLimpio($vars["_pagoach"]);
        }

        if (trim($vars["_vueltas"]) == '') {
            $_SESSION["tramite"]["vueltas"] = 0;
        } else {
            $_SESSION["tramite"]["vueltas"] = \funcionesGenerales::retornarValorLimpio($vars["_vueltas"]);
        }

        if (!isset($vars["_pagoprepago"]) || trim($vars["_pagoprepago"]) == '') {
            $_SESSION["tramite"]["pagoprepago"] = 0;
        } else {
            $_SESSION["tramite"]["pagoprepago"] = \funcionesGenerales::retornarValorLimpio($vars["_pagoprepago"]);
        }


        // *********************************************************************************************************
        // Evalua si se selecciona algun descuento para aplicar
        // *********************************************************************************************************
        $servicioalerta = '';
        $valoralerta = 0;
        $idalerta = 0;
        $linalerta = 0;
        foreach ($vars as $key => $valor) {
            if (substr($key, 0, 12) == 'radioalerta_') {
                list ($r, $l) = str_split("_", $key);
                if ($valor == 'SI') {
                    $linalerta = $l;
                }
            }
        }
        if ($linalerta != 0) {
            foreach ($vars as $key => $valor) {
                if (substr($key, 0, 9) == 'alertaid_') {
                    list ($r, $l) = str_split("_", $key);
                    if ($l == $linalerta) {
                        $idalerta = $valor;
                    }
                }
                if (substr($key, 0, 12) == 'alertavalor_') {
                    list ($r, $l) = str_split("_", $key);
                    if ($l == $linalerta) {
                        $valoralerta = $valor;
                    }
                }
                if (substr($key, 0, 15) == 'alertaservicio_') {
                    list ($r, $l) = str_split("_", $key);
                    if ($l == $linalerta) {
                        $servicioalerta = $valor;
                    }
                }
            }
        }

        //
        $_SESSION["tramite"]["alertaservicio"] = $servicioalerta;
        $_SESSION["tramite"]["alertavalor"] = $valoralerta;
        $_SESSION["tramite"]["alertaid"] = $idalerta;

        //
        $_SESSION["tramite"]["pagovisa"] = 0;
        $_SESSION["tramite"]["pagocredencial"] = 0;
        $_SESSION["tramite"]["pagodiners"] = 0;
        $_SESSION["tramite"]["pagomastercard"] = 0;
        $_SESSION["tramite"]["pagoamerican"] = 0;

        //
        if ($_SESSION["tramite"]["pagoach"] > 0) {
            $_SESSION["tramite"]["idfranquicia"] = '';
            $_SESSION["tramite"]["numeroautorizacion"] = $autach;
            $_SESSION["tramite"]["idcodban"] = $fraach;
            $_SESSION["tramite"]["numerocheque"] = '';
        }
        if ($_SESSION["tramite"]["pagotdebito"] > 0) {
            $_SESSION["tramite"]["idfranquicia"] = $fratd;
            $_SESSION["tramite"]["numeroautorizacion"] = $auttd;
            $_SESSION["tramite"]["idcodban"] = '';
            $_SESSION["tramite"]["numerocheque"] = '';
        }
        if ($_SESSION["tramite"]["pagotcredito"] > 0) {
            $_SESSION["tramite"]["idfranquicia"] = $fratc;
            $_SESSION["tramite"]["numeroautorizacion"] = $auttc;
            $_SESSION["tramite"]["idcodban"] = '';
            $_SESSION["tramite"]["numerocheque"] = '';
            switch ($fratc) {
                case "CR_AM" : $_SESSION["tramite"]["pagoamerican"] = $_SESSION["tramite"]["pagotcredito"];
                    break;
                case "CR_CR" : $_SESSION["tramite"]["pagocredencial"] = $_SESSION["tramite"]["pagotcredito"];
                    break;
                case "CR_DN" : $_SESSION["tramite"]["pagodiners"] = $_SESSION["tramite"]["pagotcredito"];
                    break;
                case "CR_VE" : $_SESSION["tramite"]["pagovisa"] = $_SESSION["tramite"]["pagotcredito"];
                    break;
                case "CR_VS" : $_SESSION["tramite"]["pagovisa"] = $_SESSION["tramite"]["pagotcredito"];
                    break;
                case "M_MSC" : $_SESSION["tramite"]["pagomastercard"] = $_SESSION["tramite"]["pagotcredito"];
                    break;
                case "RM_MC" : $_SESSION["tramite"]["pagomastercard"] = $_SESSION["tramite"]["pagotcredito"];
                    break;
            }
        }

        //
        if (trim($_SESSION["tramite"]["pagoprepago"]) == '') {
            $_SESSION["tramite"]["pagoprepago"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagoafiliado"]) == '') {
            $_SESSION["tramite"]["pagoafiliado"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagoefectivo"]) == '') {
            $_SESSION["tramite"]["pagoefectivo"] = 0;
        }
        if (trim($_SESSION["tramite"]["vueltas"]) == '') {
            $_SESSION["tramite"]["vueltas"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagocheque"]) == '') {
            $_SESSION["tramite"]["pagocheque"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagoconsignacion"]) == '') {
            $_SESSION["tramite"]["pagoconsignacion"] = 0;
        }
        if (!isset($_SESSION["tramite"]["pagoqr"]) || trim($_SESSION["tramite"]["pagoqr"]) == '') {
            $_SESSION["tramite"]["pagoqr"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagoach"]) == '') {
            $_SESSION["tramite"]["pagoach"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagotdebito"]) == '') {
            $_SESSION["tramite"]["pagotdebito"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagovisa"]) == '') {
            $_SESSION["tramite"]["pagovisa"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagomastercard"]) == '') {
            $_SESSION["tramite"]["pagomastercard"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagodiners"]) == '') {
            $_SESSION["tramite"]["pagodiners"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagoamerican"]) == '') {
            $_SESSION["tramite"]["pagoamerican"] = 0;
        }
        if (trim($_SESSION["tramite"]["pagocredencial"]) == '') {
            $_SESSION["tramite"]["pagocredencial"] = 0;
        }
        if (trim($_SESSION["tramite"]["alertavalor"]) == '') {
            $_SESSION["tramite"]["alertavalor"] = 0;
        }

        $_SESSION["tramite"]["pagoefectivo"] = $_SESSION["tramite"]["pagoefectivo"] - $_SESSION["tramite"]["vueltas"];

        // ************************************************************************** //
        // En caso de tramites RUES - Receptora
        // ************************************************************************** //
        if (!isset($_SESSION["tramite"]["tipotramite"])) {
            $_SESSION["tramite"]["tipotramite"] = '';
        }
        if ($_SESSION["tramite"]["tipotramite"] != '') {
            $arrTipoTramite = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");
            if ($arrTipoTramite && !empty($arrTipoTramite)) {
                if ($arrTipoTramite["tiporegistro"] == 'RueRec') {
                    $arrCod = retornarRegistroMysqliApi($mysqli, 'mreg_homologaciones_rue', "id_tabla='02' and cod_cc='" . $_SESSION["tramite"]["tipoidentificacionpagador"] . "'");
                    $_SESSION["tramite"]["rues_claseidentificacion"] = $arrCod["cod_rue"];
                    if ($_SESSION["tramite"]["tipoidentificacionpagador"] == '2') {
                        $idex = \funcionesGenerales::separarDv($_SESSION["tramite"]["identificacionpagador"]);
                        $_SESSION["tramite"]["rues_numeroidentificacion"] = $idex["identificacion"];
                        $_SESSION["tramite"]["rues_dv"] = $idex["dv"];
                    } else {
                        $_SESSION["tramite"]["rues_numeroidentificacion"] = $_SESSION["tramite"]["identificacionpagador"];
                        $_SESSION["tramite"]["rues_dv"] = '';
                    }
                    $_SESSION["tramite"]["rues_nombrepagador"] = trim($_SESSION["tramite"]["apellidopagador"] . ' ' . $_SESSION["tramite"]["nombrepagador"]);
                    $_SESSION["tramite"]["tipogasto"] = '7';
                }
            }
        }

        //
        $_SESSION["tramite"]["asignodatosfacturacion"] = 'si';

        // ************************************************************************** //
        // Graba la liquidación
        // ************************************************************************** //
        if ($_SESSION["tramite"]["idliquidacion"] != 0) {
            \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

            // ************************************************************************** //
            // Graba datos empresas
            // ************************************************************************** //
            if ($_SESSION["tramite"]["identificacioncliente"] != '222222222222') {
                $tipopersona = '';
                if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
                    $tipopersona = '1';
                } else {
                    $tipopersona = '2';
                }
                $arrCampos = array(
                    'tipoidentificacion',
                    'identificacion',
                    'tipopersona',
                    'camara',
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
                    "''",
                    "'" . addslashes(trim($_SESSION["tramite"]["apellidocliente"] . ' ' . $_SESSION["tramite"]["nombrecliente"])) . "'",
                    "''",
                    "'" . addslashes(trim($_SESSION["tramite"]["nombre1cliente"])) . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["nombre2cliente"])) . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["apellido1cliente"])) . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["apellido2cliente"])) . "'",
                    "''",
                    "'" . addslashes(trim($_SESSION["tramite"]["email"])) . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["responsabilidadtributaria"])) . "'",
                    "'" . $_SESSION["tramite"]["codigoregimen"] . "'",
                    "'" . $_SESSION["tramite"]["codigoimpuesto"] . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["nombreimpuesto"])) . "'",
                    "'" . $_SESSION["tramite"]["responsabilidadfiscal"] . "'",
                    "'" . $_SESSION["tramite"]["telefono"] . "'",
                    "'" . $_SESSION["tramite"]["movil"] . "'",
                    "'" . $_SESSION["tramite"]["zonapostal"] . "'",
                    "'" . $_SESSION["tramite"]["pais"] . "'",
                    "'" . $_SESSION["tramite"]["lenguaje"] . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["direccion"])) . "'",
                    "'" . $_SESSION["tramite"]["idmunicipio"] . "'",
                    "'" . $_SESSION["tramite"]["codposcom"] . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["direccionnot"])) . "'",
                    "'" . $_SESSION["tramite"]["idmunicipionot"] . "'",
                    "'" . $_SESSION["tramite"]["codposnot"] . "'"
                );
                $datemp = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'");
                if ($datemp === false || empty($datemp)) {
                    insertarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores);
                } else {
                    if (
                            $datemp["tipoidentificacion"] != $_SESSION["tramite"]["idtipoidentificacioncliente"] ||
                            $datemp["identificacion"] != $_SESSION["tramite"]["identificacioncliente"] ||
                            $datemp["tipopersona"] != $tipopersona ||
                            $datemp["razonsocial"] != trim($_SESSION["tramite"]["apellidocliente"] . ' ' . $_SESSION["tramite"]["nombrecliente"]) ||
                            $datemp["primernombre"] != $_SESSION["tramite"]["nombre1cliente"] ||
                            $datemp["segundonombre"] != $_SESSION["tramite"]["nombre2cliente"] ||
                            $datemp["primerapellido"] != $_SESSION["tramite"]["apellido1cliente"] ||
                            $datemp["segundoapellido"] != $_SESSION["tramite"]["apellido2cliente"] ||
                            $datemp["particula"] != $_SESSION["tramite"]["particulacliente"] ||
                            $datemp["email"] != $_SESSION["tramite"]["email"] ||
                            $datemp["responsabilidadtributaria"] != $_SESSION["tramite"]["responsabilidadtributaria"] ||
                            $datemp["codigoregimen"] != $_SESSION["tramite"]["codigoregimen"] ||
                            $datemp["codigoimpuesto"] != $_SESSION["tramite"]["codigoimpuesto"] ||
                            $datemp["nombreimpuesto"] != $_SESSION["tramite"]["nombreimpuesto"] ||
                            $datemp["responsabilidadfiscal"] != $_SESSION["tramite"]["responsabilidadfiscal"] ||
                            $datemp["telefono1"] != $_SESSION["tramite"]["telefono"] ||
                            $datemp["telefono2"] != $_SESSION["tramite"]["movil"] ||
                            $datemp["zonapostal"] != $_SESSION["tramite"]["zonapostal"] ||
                            $datemp["pais"] != $_SESSION["tramite"]["pais"] ||
                            $datemp["lenguaje"] != $_SESSION["tramite"]["lenguaje"] ||
                            $datemp["dircom"] != $_SESSION["tramite"]["direccion"] ||
                            $datemp["muncom"] != $_SESSION["tramite"]["idmunicipio"] ||
                            $datemp["codposcom"] != $_SESSION["tramite"]["codposcom"] ||
                            $datemp["dirnot"] != $_SESSION["tramite"]["direccionnot"] ||
                            $datemp["munnot"] != $_SESSION["tramite"]["idmunicipionot"] ||
                            $datemp["codposnot"] != $_SESSION["tramite"]["codposnot"]) {
                        regrabarRegistrosMysqliApi($mysqli, 'datos_empresas', $arrCampos, $arrValores, "tipoidentificacion='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "' and identificacion='" . $_SESSION["tramite"]["identificacioncliente"] . "'");
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

    public function mregRecibirPagosValidarSaldoPrepago(API $api) {
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
        $api->validarParametro("identificacion", true);
        $api->validarParametro("totalliquidacion", true);
        $api->validarParametro("clavedigitada", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRecibirPagosValidarSaldoPrepago', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesGrabarSolicitudBloque ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el prepago exista
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        $prep = \funcionesRegistrales::actualizarPrepago($mysqli, 'S', $_SESSION["entrada"]["identificacion"]);
        if ($prep === false || empty($prep)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontro prepago para la identificación ' . $_SESSION["entrada"]["identificacion"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($prep["saldoprepago"] == 0 || $prep["clave"] == '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontro prepago para la identificación ' . $_SESSION["entrada"]["identificacion"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($prep["saldoprepago"] < $_SESSION["entrada"]["totalliquidacion"]) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El saldo del cliente no alcanza para pagar el costo del servicio adquirido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (md5($_SESSION["entrada"]["clavedigitada"]) != $prep['clave'] &&
                sha1($_SESSION["entrada"]["clavedigitada"]) != $prep['clave'] &&
                !password_verify($_SESSION["entrada"]["clavedigitada"], $prep['clave'])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave digitada no corresponde';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregRecibirPagosAsociarImagen(API $api) {
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
        if (!$api->validarToken('mregRecibirPagosAsociarImagen', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRecibirPagosAsociarImagen ';
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

    public function mregRecibirPagosBuscarDescripcionServicio(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["descripcion"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("servicio", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregRecibirPagosBuscarDescripcionServicio', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregRecibirPagosBuscarDescripcionServicio ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . sprintf("%08s", $_SESSION["entrada"]["servicio"]) . "'");
        if ($serv === false || empty($serv)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Servicio [' . sprintf("%08s", $_SESSION["entrada"]["servicio"]) . '] no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $_SESSION["jsonsalida"]["descripcion"] = $serv["nombre"];
        $mysqli->close();
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

}
