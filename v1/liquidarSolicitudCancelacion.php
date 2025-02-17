<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait liquidarSolicitudCancelacion {

    public function liquidarSolicitudCancelacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesEspeciales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $resError = set_error_handler('myErrorHandler');

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
        $api->validarParametro("tipoliquidacion", true);
        $_SESSION["entrada"]["tipoliquidacion"] = strtoupper($_SESSION["entrada"]["tipoliquidacion"]);
        
        //
        if ($_SESSION["entrada"]["tipoliquidacion"] != 'L' && $_SESSION["entrada"]["tipoliquidacion"] != 'P') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Tipo de liquidación debe ser P o L';
        }
        
        //
        if (strtoupper((string) $_SESSION["entrada"]["tipoliquidacion"]) == 'L') {
            $api->validarParametro("emailcontrol", true);
            $api->validarParametro("identificacioncontrol", true);
            $api->validarParametro("nombrecontrol", true);
            $api->validarParametro("celularcontrol", true);
            $api->validarParametro("motivo", true);
            $api->validarParametro("detallemotivo", false);
        }

        //
        if (!$api->validarToken('liquidarSolicitudCancelacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //
        $fecorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'");

        // ************************************************************************ //
        // Arma variables de session
        // ************************************************************************ //        
        $res = \funcionesGenerales::asignarVariablesSession($mysqli, $_SESSION["entrada"]);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de autenticacion, usuario no puede consumir el API - no localizado como usuario registrado, verificado local, verificado nacional';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($_SESSION["generales"]["tipousuariocontrol"] != 'usuarioverificado') {
            if ($_SESSION["entrada"]["tipoliquidacion"] != 'P') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de autenticacion, los datos del usuario no corresponden con un usuario verificado.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ************************************************************************ //
        // Valida exitencia del arreglo de matrículas
        // ************************************************************************ //   
        if (!isset($_SESSION["entrada1"]["matriculas"]) || !is_array($_SESSION["entrada1"]["matriculas"]) || empty($_SESSION["entrada1"]["matriculas"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportaron matrículas a cancelar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ************************************************************************ //
        // Verifica que las matrículas sean cancelables
        // ************************************************************************ //   
        $matp = '';
        $mate = array();
        $matbase = '';
        $txt = '';
        $ican = 0;
        $imats = 0;
        foreach ($_SESSION["entrada1"]["matriculas"] as $m) {
            $err = 'no';
            $e = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");
            if ($e === false || empty($e)) {
                if ($txt != '') {
                    $txt .= '<br>';
                }
                $txt .= 'Matrícula No. ' . $m . ' no localizada en la BD';
                $err = 'si';
            } else {
                if ($e["ctrestmatricula"] != 'MA') {
                    if ($txt != '') {
                        $txt .= '<br>';
                    }
                    $txt .= 'Matrícula No. ' . $m . ' no se encuentra activa';
                    $err = 'si';
                } else {
                    if ($e["organizacion"] != '01' && $e["organizacion"] != '02') {
                        if ($e["organizacion"] > '02' && $e["organizacion"] == '1') {
                            if ($txt != '') {
                                $txt .= '<br>';
                            }
                            $txt .= 'Matrícula No. ' . $m . ' corresponde a una persona jurídica principal';
                            $err = 'si';
                        }
                    }
                }
            }
            if ($err == 'no') {
                if (date("Ymd") <= $fecorte) {
                    if ($e["ultanoren"] < date("Y") - 1) {
                        if ($txt != '') {
                            $txt .= '<br>';
                        }
                        $txt .= 'Matrícula No. ' . $m . ' no se encuentra renovada para poder cancelarla.';
                        $err = 'si';
                    }
                }
                if (date("Ymd") > $fecorte) {
                    if ($e["ultanoren"] < date("Y")) {
                        if ($txt != '') {
                            $txt .= '<br>';
                        }
                        $txt .= 'Matrícula No. ' . $m . ' no se encuentra renovada para poder cancelarla.';
                        $err = 'si';
                    }
                }
            }
            if ($err == 'no') {
                $ican++;
                if ($ican == 1) {
                    if ($e["organizacion"] == '01') {
                        $matp = $m;
                        $matbase = $m;
                        $imats++;
                    } else {
                        $mate[] = $m;
                        $matbase = $m;
                        $imats++;
                    }
                } else {
                    if ($e["organizacion"] == '01') {
                        if ($txt != '') {
                            $txt .= '<br>';
                        }
                        $txt .= 'Se encontró más de una matrícula para persona natural, no es posible solicitar la cancelación.';
                        $err = 'si';
                    } else {
                        $mate[] = $m;
                        $imats++;
                    }
                }
            }
        }

        //
        if ($txt != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $txt;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (isset($_SESSION["entrada"]["urlretorno"]) && $_SESSION["entrada"]["urlretorno"] != '') {
            $_SESSION["tramite"]["urlretorno"] = $_SESSION["entrada"]["urlretorno"];
        }
        
        //
        // ********************************************************************** //
        // Inicializa la liquidación
        // ********************************************************************** //        
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');

        //
        $_SESSION["tramite"]["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
        $_SESSION["tramite"]["numeroliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
        $_SESSION["tramite"]["fecha"] = date("Ymd");
        $_SESSION["tramite"]["hora"] = date("His");
        $_SESSION["tramite"]["idusuario"] = 'USUPUBXX';
        $_SESSION["tramite"]["sede"] = '99';
        $_SESSION["tramite"]["tramitepresencial"] = '1';

        $_SESSION["tramite"]["tipotramite"] = 'inscripciondocumentos';
        $_SESSION["tramite"]["subtipotramite"] = 'solicitudcancelacionvue';
        $_SESSION["tramite"]["tipogasto"] = '0';
        $_SESSION["tramite"]["origen"] = '';
        $_SESSION["tramite"]["iptramite"] = \funcionesGenerales::localizarIP();
        $_SESSION["tramite"]["idestado"] = '01';
        $_SESSION["tramite"]["txtestado"] = 'PENDIENTE';
        $_SESSION["tramite"]["idexpedientebase"] = '';
        $_SESSION["tramite"]["idmatriculabase"] = '';
        if ($imats == 1) {
            $_SESSION["tramite"]["idexpedientebase"] = $matbase;
            $_SESSION["tramite"]["idmatriculabase"] = $matbase;
        }

        if ($matp != '') {
            $expe = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $matp);
        } else {
            $expe = false;
        }
        if ($expe === false) {
            $_SESSION["tramite"]["tipoidentificacionbase"] = '';
            $_SESSION["tramite"]["identificacionbase"] = $_SESSION["entrada"]["identificacioncontrol"];
            $_SESSION["tramite"]["nombrebase"] = strtoupper($_SESSION["entrada"]["nombrecontrol"]);
            $_SESSION["tramite"]["nom1base"] = '';
            $_SESSION["tramite"]["nom2base"] = '';
            $_SESSION["tramite"]["ape1base"] = '';
            $_SESSION["tramite"]["ape2base"] = '';
            $_SESSION["tramite"]["organizacionbase"] = '';
            $_SESSION["tramite"]["categoriabase"] = '';
        } else {
            $_SESSION["tramite"]["tipoidentificacionbase"] = $expe["tipoidentificacion"];
            $_SESSION["tramite"]["identificacionbase"] = $expe["identificacion"];
            $_SESSION["tramite"]["nombrebase"] = $expe["nombre"];
            $_SESSION["tramite"]["nom1base"] = $expe["nom1"];
            $_SESSION["tramite"]["nom2base"] = $expe["nom2"];
            $_SESSION["tramite"]["ape1base"] = $expe["ape1"];
            $_SESSION["tramite"]["ape2base"] = $expe["ape2"];
            $_SESSION["tramite"]["organizacionbase"] = $expe["organizacion"];
            $_SESSION["tramite"]["categoriabase"] = $expe["categoria"];
        }
        $_SESSION["tramite"]["afiliadobase"] = '';
        $_SESSION["tramite"]["matriculabase"] = $matbase;

        $_SESSION["tramite"]["numeromatriculapnat"] = '';
        $_SESSION["tramite"]["camarapnat"] = '';
        $_SESSION["tramite"]["orgpnat"] = '';
        $_SESSION["tramite"]["tipoidepnat"] = '';
        $_SESSION["tramite"]["idepnat"] = '';
        $_SESSION["tramite"]["nombrepnat"] = '';

//
        $_SESSION["tramite"]["nombreest"] = '';
        $_SESSION["tramite"]["nombrepjur"] = '';
        $_SESSION["tramite"]["nombresuc"] = '';
        $_SESSION["tramite"]["nombreage"] = '';

        $_SESSION["tramite"]["orgpjur"] = '';
        $_SESSION["tramite"]["orgsuc"] = '';
        $_SESSION["tramite"]["orgage"] = '';

        $_SESSION["tramite"]["actpnat"] = '';
        $_SESSION["tramite"]["actpjur"] = '';
        $_SESSION["tramite"]["actest"] = '';
        $_SESSION["tramite"]["actsuc"] = '';
        $_SESSION["tramite"]["actage"] = '';

        $_SESSION["tramite"]["perpnat"] = '';
        $_SESSION["tramite"]["perpjur"] = '';

        $_SESSION["tramite"]["munpnat"] = '';
        $_SESSION["tramite"]["munest"] = '';
        $_SESSION["tramite"]["munpjur"] = '';
        $_SESSION["tramite"]["munsuc"] = '';
        $_SESSION["tramite"]["munage"] = '';

        $_SESSION["tramite"]["ultanoren"] = '';
        $_SESSION["tramite"]["domicilioorigen"] = '';
        $_SESSION["tramite"]["domiciliodestino"] = '';

        $_SESSION["tramite"]["incluirformularios"] = '';
        $_SESSION["tramite"]["incluircertificados"] = '';
        $_SESSION["tramite"]["incluirdiploma"] = '';
        $_SESSION["tramite"]["incluircartulina"] = '';
        $_SESSION["tramite"]["matricularpnat"] = '';
        $_SESSION["tramite"]["matricularest"] = '';
        $_SESSION["tramite"]["regimentributario"] = '';
        $_SESSION["tramite"]["tipomatricula"] = '';
        $_SESSION["tramite"]["camaracambidom"] = '';
        $_SESSION["tramite"]["matriculacambidom"] = '';
        $_SESSION["tramite"]["municipiocambidom"] = '';
        $_SESSION["tramite"]["fecmatcambidom"] = '';
        $_SESSION["tramite"]["fecrencambidom"] = '';
        $_SESSION["tramite"]["benart7"] = 'N';
        $_SESSION["tramite"]["benley1780"] = 'N';
        $_SESSION["tramite"]["controlfirma"] = 'N';
        $_SESSION["tramite"]["cumplorequisitosbenley1780"] = '';
        $_SESSION["tramite"]["mantengorequisitosbenley1780"] = '';
        $_SESSION["tramite"]["renunciobeneficiosley1780"] = '';
        $_SESSION["tramite"]["multadoponal"] = '';
        $_SESSION["tramite"]["controlaactividadaltoimpacto"] = '';
        $_SESSION["tramite"]["sistemacreacion"] = 'API - liquidarSolicitudCancelacion - ' . $_SESSION["entrada"]["usuariows"];

        $_SESSION["tramite"]["capital"] = 0;
        $_SESSION["tramite"]["tipodoc"] = '';
        $_SESSION["tramite"]["numdoc"] = '';
        $_SESSION["tramite"]["fechadoc"] = '';
        $_SESSION["tramite"]["origendoc"] = '';
        $_SESSION["tramite"]["mundoc"] = '';
        $_SESSION["tramite"]["organizacion"] = '';
        $_SESSION["tramite"]["categoria"] = '';

        $_SESSION["tramite"]["tipolibro"] = ''; 
        $_SESSION["tramite"]["codigolibro"] = ''; 
        $_SESSION["tramite"]["primeravez"] = ''; 
        $_SESSION["tramite"]["confirmadigital"] = ''; 
        
        // Adiciona servicio para matricula del propietario
        $i = 0;
        if ($matp != '') {
            $i++;
            $_SESSION["tramite"]["liquidacion"][] = $this->adicionarRegistro($mysqli, $matp, $i);
        }
        if (!empty($mate)) {
            foreach ($mate as $m) {
                $i++;
                $_SESSION["tramite"]["liquidacion"][] = $this->adicionarRegistro($mysqli, $m, $i);
            }
        }

        //
        foreach ($_SESSION["tramite"]["liquidacion"] as $lq) {
            $_SESSION["tramite"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"] + $lq["valorservicio"];
            $_SESSION["tramite"]["valortotal"] = $_SESSION["tramite"]["valortotal"] + $lq["valorservicio"];
        }

        //
        $_SESSION["tramite"]["idmotivocancelacion"] = $_SESSION["entrada"]["motivo"];
        $_SESSION["tramite"]["motivocancelacion"] = $_SESSION["entrada"]["detallemotivo"];

        // Adiciona servicio por cada matrícula de establecimientos, sucursales o agencias
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["tramite"]["idliquidacion"]);

        //
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
        $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
        $_SESSION["jsonsalida"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"];
        $_SESSION["jsonsalida"]["valorbaseiva"] = $_SESSION["tramite"]["valorbaseiva"];
        $_SESSION["jsonsalida"]["valoriva"] = $_SESSION["tramite"]["valoriva"];
        $_SESSION["jsonsalida"]["valortotal"] = $_SESSION["tramite"]["valortotal"];
        $_SESSION["jsonsalida"]["liquidacion"] = array();
        foreach ($_SESSION["tramite"]["liquidacion"] as $xliq) {
            $dliq = array();
            $dliq["servicio"] = $xliq["idservicio"];
            $dliq["nservicio"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $xliq["idservicio"] . "'", "nombre");
            $dliq["matricula"] = $xliq["expediente"];
            $dliq["nombre"] = $xliq["nombre"];
            $dliq["cantidad"] = $xliq["cantidad"];
            $dliq["baseliquidacion"] = $xliq["valorbase"];
            $dliq["porcentaje"] = $xliq["porcentaje"];
            $dliq["valor"] = $xliq["valorservicio"];
            $_SESSION["jsonsalida"]["liquidacion"][] = $dliq;
        }

        //
        if ($_SESSION["entrada"]["tipoliquidacion"]  == 'P') {
            \funcionesRegistrales::borrarMregLiquidacion($mysqli, $_SESSION["tramite"]["idliquidacion"]);
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

    public function adicionarRegistro($mysqli, $mat, $i) {
        $e = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat . "'");
        $serv = '';
        if ($e["organizacion"] == '01') {
            $serv = '01031501';
        }
        if ($e["organizacion"] == '02') {
            $serv = '01031502';
        }
        if ($e["organizacion"] > '02' && $e["categoria"] == '2') {
            $serv = '01031509';
        }
        if ($e["organizacion"] > '02' && $e["categoria"] == '3') {
            $serv = '01031509';
        }

        $s = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $serv . "'");
        $renglon = array();
        $renglon["secuencia"] = $i;
        $renglon["idsec"] = '000';
        $renglon["idservicio"] = $serv;
        $renglon["txtservicio"] = $s["nombre"];
        $renglon["cc"] = CODIGO_EMPRESA;
        $renglon["expediente"] = $mat;
        $renglon["nombre"] = $e["razonsocial"];
        $renglon["ano"] = '0000';
        $renglon["cantidad"] = 1;
        $renglon["valorbase"] = 0;
        $renglon["porcentaje"] = 0;
        $renglon["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date ("Y"), 1);
        $renglon["benart7"] = '';
        $renglon["benley1780"] = '';
        $renglon["reliquidacion"] = 'no';
        $renglon["serviciobase"] = '';
        $renglon["pagoafiliacion"] = '';
        $renglon["ir"] = '';
        $renglon["iva"] = '';
        $renglon["idalerta"] = 0;
        $renglon["expedienteafiliado"] = '';

        $renglon["porcentajeiva"] = 0;
        $renglon["valoriva"] = 0;
        $renglon["servicioiva"] = '';
        $renglon["porcentajedescuento"] = 0;
        $renglon["valordescuento"] = 0;
        $renglon["serviciodescuento"] = '';
        $renglon["clavecontrol"] = '';
        return $renglon;
    }

}
