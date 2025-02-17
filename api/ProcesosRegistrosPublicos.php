<?php

/*
 * Se recibe json con la siguiente información
 *
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait ProcesosRegistrosPublicos {

    public function marcarPagoCaja(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfEstablecimientoslNuevo1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfEstablecimientoslNuevoAnosAnteriores1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfPrincipalNuevo2023.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfPrincipalNuevoAnosAnteriores1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/uniPdfs.php');
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
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("ip", false);
        $api->validarParametro("sistemaorigen", false);
        //
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("incluirformularios", true);
        //
        if (!$api->validarToken('marcarPagoCaja', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Cambia el estado de la  liquidacion
        // ********************************************************************** //

        $arrCampos = array('idestado');
        $arrValores = array("'05'");
        $result = regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);

        if ($result === false || empty($result)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada (1)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Recupera liquidacion
        // ********************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada (2)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["incluirformularios"] == 'SI') {

            // ********************************************************************** //
            // Arma data del formulario
            // ********************************************************************** //
            $_SESSION["formulario"]["tipomatricula"] = '';
            $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];
            $_SESSION["formulario"]["reliquidacion"] = $_SESSION["tramite"]["reliquidacion"];
            $_SESSION["formulario"]["liquidacion"] = $_SESSION["entrada"]["idliquidacion"];
            //$_SESSION["formulario"]["matricula"] = $_SESSION["entrada"]["matricula"];
            $_SESSION["formulario"]["organizacion"] = '';
            $_SESSION["formulario"]["categoria"] = '';


            // ********************************************************************** //
            // Recupera datos del expediente
            // ********************************************************************** //
            $arrForms = retornarRegistrosMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);



            if ($arrForms === false || empty($arrForms)) {
                if ($_SESSION["entrada"]["matricula"] == '' ||
                        substr($_SESSION["entrada"]["matricula"], 0, 5) == 'NUEVA') {
                    $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, '');
                } else {
                    $_SESSION["formulario"]["datos"] = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
                    $_SESSION["formulario"]["organizacion"] = $_SESSION["formulario"]["datos"]["organizacion"];
                    $_SESSION["formulario"]["categoria"] = $_SESSION["formulario"]["datos"]["categoria"];
                    \funcionesRegistrales::almacenarDatosImportantesRenovacion($mysqli, $_SESSION["entrada"]["idliquidacion"], $_SESSION["formulario"]["datos"], 'I');
                }
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada (2)';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {

                /*
                  $_SESSION["formulario"]["datos"] = \funcionesSii2_desserializaciones::desserializarExpedienteMatricula($mysqli, $arrForms["xml"]);
                  $_SESSION["formulario"]["organizacion"] = $_SESSION["formulario"]["datos"]["organizacion"];
                  $_SESSION["formulario"]["categoria"] = $_SESSION["formulario"]["datos"]["categoria"];
                 */
                $lista = array();
                $iLista = 0;


                /*
                  $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                  $_SESSION["jsonsalida"]["mensajeerror"] = 'test2';
                  $_SESSION["jsonsalida"]["data"] = $arrForms;
                  $api->response($api->json($_SESSION["jsonsalida"]), 200);
                 */
                $pathUnionPdf = PATH_ABSOLUTO_SITIO . '/tmp/';


                foreach ($arrForms as $ind => $form) {
                    $dat = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $form["xml"]);
                    //  if ($dat["matricula"] == $_SESSION["formulario"]["matricula"]) {
                    $_SESSION["formulario"]["datos"] = $dat;

                    if (($_SESSION["formulario"]["datos"]["organizacion"] == '02') ||
                            ($_SESSION["formulario"]["datos"]["categoria"] == '2') ||
                            ($_SESSION["formulario"]["datos"]["categoria"] == '3')) {
                        $name = armarPdfEstablecimientoNuevo1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');


                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                            $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                            unirPdfsApiV2(array(
                                $pathUnionPdf . $name,
                                $pathUnionPdf . $name1
                                    ), $pathUnionPdf . $name);
                        }
                        $iLista++;
                        $lista[$iLista] = $pathUnionPdf . $name;

                        $ok = 'si-1';
                    } else {
                        $name = armarPdfPrincipalNuevo2023Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                            $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                            unirPdfsApiV2(array(
                                $pathUnionPdf . $name,
                                $pathUnionPdf . $name1
                                    ), $pathUnionPdf . $name);
                        }
                        $iLista++;
                        $lista[$iLista] = $pathUnionPdf . $name;

                        $ok = 'si-2';
                    }
                    //  }
                }
            }

            unset($arrForms);

            //

            $nameSalida = $_SESSION["generales"]["codigoempresa"] . '-' . date("Ymd") . '-' . date("His") . '-' . session_id() . '.pdf';

            if ($iLista == 1) {
                $nameSalida = str_replace($pathUnionPdf, "", $lista[1]);
            } else {
                unirPdfsApiV2($lista, $pathUnionPdf . $nameSalida);
            }
            //
            $_SESSION['jsonsalida']['idliquidacion'] = $_SESSION["entrada"]["idliquidacion"];
            $_SESSION['jsonsalida']['idestado'] = $_SESSION["tramite"]["idestado"];

            $linktmp = "/tmp/" . $nameSalida;
            $linktmp = str_replace("//", "/", $linktmp);
            $_SESSION["jsonsalida"]["link"] = TIPO_HTTP . HTTP_HOST . $linktmp;

            //
        } else {
            $_SESSION['jsonsalida']['idliquidacion'] = $_SESSION["entrada"]["idliquidacion"];
            $_SESSION['jsonsalida']['idestado'] = $_SESSION["tramite"]["idestado"];
            $_SESSION["jsonsalida"]["link"] = "";
        }

        if ($_SESSION['jsonsalida']['idestado'] == '05') {
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación queda marcada para pago en la caja de la Cámara a de comercio.';
        }

        $mysqli->close();


        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function construirVolanteBancos(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfEstablecimientoslNuevo1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfEstablecimientoslNuevoAnosAnteriores1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfPrincipalNuevo2023.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfPrincipalNuevoAnosAnteriores1082.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil_armarPdfVolantePagoBancos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/uniPdfs.php');
        
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
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("ip", false);
        $api->validarParametro("sistemaorigen", false);
        //
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("incluirformularios", true);

        //
        if (!$api->validarToken('construirVolanteBancos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Cambia el estado de la  liquidacion
        // ********************************************************************** //

        $arrCampos = array('idestado');
        $arrValores = array("'05'");
        $result = regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);

        if ($result === false || empty($result)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada (1)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Recupera liquidacion
        // ********************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada (2)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Arma data del formulario
        // ********************************************************************** //
        $_SESSION["formulario"]["tipomatricula"] = '';
        $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];
        $_SESSION["formulario"]["reliquidacion"] = $_SESSION["tramite"]["reliquidacion"];
        $_SESSION["formulario"]["liquidacion"] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION["formulario"]["organizacion"] = '';
        $_SESSION["formulario"]["categoria"] = '';


        // ********************************************************************** //
        // Recupera datos del expediente
        // ********************************************************************** //
        $arrForms = retornarRegistrosMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);

        if ($arrForms === false || empty($arrForms)) {
            if ($_SESSION["entrada"]["matricula"] == '' ||
                    substr($_SESSION["entrada"]["matricula"], 0, 5) == 'NUEVA') {
                $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, '');
            } else {
                $_SESSION["formulario"]["datos"] = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
                $_SESSION["formulario"]["organizacion"] = $_SESSION["formulario"]["datos"]["organizacion"];
                $_SESSION["formulario"]["categoria"] = $_SESSION["formulario"]["datos"]["categoria"];
                \funcionesRegistrales::almacenarDatosImportantesRenovacion($mysqli, $_SESSION["entrada"]["idliquidacion"], $_SESSION["formulario"]["datos"], 'I');
            }
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada (2)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {

            /*
              $_SESSION["formulario"]["datos"] = \funcionesSii2_desserializaciones::desserializarExpedienteMatricula($mysqli, $arrForms["xml"]);
              $_SESSION["formulario"]["organizacion"] = $_SESSION["formulario"]["datos"]["organizacion"];
              $_SESSION["formulario"]["categoria"] = $_SESSION["formulario"]["datos"]["categoria"];
             */

            // ************************************************************************** //
            // genera el volante para pago en bancos
            // ************************************************************************** //
            //
            $textoBancos = '';

            //
            $tb = retornarRegistroMysqliApi($mysqli, 'textos_propios', "idtexto='bancos_y_corresponsales'", "texto");
            if (trim($tb) != '') {
                $textoBancos = $tb;
            } else {
                $textoBancos .= 'Señor Usuario Realice sus pagos en: ';
                $textoBancos .= (ACTIVAR_PAGO_BANCOS_OCCIDENTE == 'S') ? 'Banco de Occidente, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_BANCOS_BOGOTA == 'S') ? 'Banco de Bogotá, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_BANCOS_DAVIVIENDA == 'S') ? 'Banco Davivienda, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_BANCOS_BANCOLOMBIA == 'S') ? 'Bancolombia, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_BANCOS_CAJASOCIAL == 'S') ? 'Banco Caja Social, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_BANCOS_POPULAR == 'S') ? 'Banco Popular, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_BANCOS_COLPATRIA == 'S') ? 'Banco Colpatria, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_BANCOS_BBVA == 'S') ? 'Banco BBVA, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_BALOTO == 'S') ? 'Puntos Baloto, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_EXITO == 'S') ? 'Almacenes Éxito, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_POMONA == 'S') ? 'Almacenes Pomona, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_SUPERINTER == 'S') ? 'Almacenes Superinter, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_SURTIMAX == 'S') ? 'Almacenes Surtimax, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_CARULLA == 'S') ? 'Almacenes Carulla, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_SURED == 'S') ? 'Puntos Susuerte, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_COOGUASIMALES == 'S') ? 'Puntos Cooguasimales, ' : '';
                $textoBancos .= (ACTIVAR_PAGO_APOSTAR == 'S') ? 'Puntos Apostar, ' : '';
                $textoBancos .= 'o en cualquiera de nuestras Sedes.';
            }

            //
            $textoSoportes = '';
            if ($_SESSION["tramite"]["idestado"] != '19' && $_SESSION["tramite"]["idestado"] != '44') {
                $textoSoportes = 'Señor Cajero, EXIJA que el cliente presente los formularios debidamente firmados.';
            }

            //

            $lista = array();
            $iLista = 0;

            $iLista++;
            $lista[$iLista] = armarPdfVolantePagoBancos($mysqli, $_SESSION["tramite"], $_SESSION["tramite"]["liquidacion"], $_SESSION["tramite"] ["valortotal"], 'justificado', 'bancos', $textoBancos, $textoSoportes);


            $pathUnionPdf = PATH_ABSOLUTO_SITIO . '/tmp/';

            if ($_SESSION["entrada"]["incluirformularios"] == 'SI') {

                foreach ($arrForms as $ind => $form) {
                    $dat = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $form["xml"]);
                    //  if ($dat["matricula"] == $_SESSION["formulario"]["matricula"]) {
                    $_SESSION["formulario"]["datos"] = $dat;

                    if (($_SESSION["formulario"]["datos"]["organizacion"] == '02') ||
                            ($_SESSION["formulario"]["datos"]["categoria"] == '2') ||
                            ($_SESSION["formulario"]["datos"]["categoria"] == '3')) {
                        $name = armarPdfEstablecimientoNuevo1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');


                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                            $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                            unirPdfsApiV2(array(
                                $pathUnionPdf . $name,
                                $pathUnionPdf . $name1
                                    ), $pathUnionPdf . $name);
                        }
                        $iLista++;
                        $lista[$iLista] = $pathUnionPdf . $name;

                        $ok = 'si-1';
                    } else {
                        $name = armarPdfPrincipalNuevo2023Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                            $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Api($mysqli, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                            unirPdfsApiV2(array(
                                $pathUnionPdf . $name,
                                $pathUnionPdf . $name1
                                    ), $pathUnionPdf . $name);
                        }
                        $iLista++;
                        $lista[$iLista] = $pathUnionPdf . $name;

                        $ok = 'si-2';
                    }
                    //  }
                }
            }

            unset($arrForms);

            //
        }

        $nameSalida = $_SESSION["generales"]["codigoempresa"] . '-' . date("Ymd") . '-' . date("His") . '-' . session_id() . '.pdf';

        if ($iLista == 1) {
            $nameSalida = str_replace($pathUnionPdf, "", $lista[1]);
        } else {
            unirPdfsApiV2($lista, $pathUnionPdf . $nameSalida);
        }

        //
        $_SESSION['jsonsalida']['idliquidacion'] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION['jsonsalida']['idestado'] = $_SESSION["tramite"]["idestado"];


        $_SESSION["jsonsalida"]["link"] = TIPO_HTTP . HTTP_HOST . "/tmp/" . $nameSalida;
        $_SESSION["jsonsalida"]["link"] = str_replace("tmp//", "tmp/", $_SESSION["jsonsalida"]["link"]);

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
