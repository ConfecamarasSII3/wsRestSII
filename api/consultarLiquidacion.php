<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarLiquidacion {

    public function consultarLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]['idusuario'] = '';
        $_SESSION["jsonsalida"]['identificacioncontrol'] = '';
        $_SESSION["jsonsalida"]['nombrecontrol'] = '';
        $_SESSION["jsonsalida"]['emailcontrol'] = '';
        $_SESSION["jsonsalida"]['celularcontrol'] = '';
        $_SESSION["jsonsalida"]['idliquidacion'] = '';
        $_SESSION["jsonsalida"]['numerorecuperacion'] = '';
        $_SESSION["jsonsalida"]['fecha'] = '';
        $_SESSION["jsonsalida"]['hora'] = '';
        $_SESSION["jsonsalida"]['tipotramite'] = '';
        $_SESSION["jsonsalida"]['subtipotramite'] = '';
        $_SESSION["jsonsalida"]['idestado'] = '';
        $_SESSION["jsonsalida"]['matricula'] = '';
        $_SESSION["jsonsalida"]['proponente'] = '';
        $_SESSION["jsonsalida"]['tipoidentificacion'] = '';
        $_SESSION["jsonsalida"]['identificacion'] = '';
        $_SESSION["jsonsalida"]['nombre'] = '';
        $_SESSION["jsonsalida"]['email'] = '';
        $_SESSION["jsonsalida"]['celular'] = '';
        $_SESSION["jsonsalida"]['valor'] = '';
        $_SESSION["jsonsalida"]['recibo'] = '';
        $_SESSION["jsonsalida"]['fecharecibo'] = '';
        $_SESSION["jsonsalida"]['horarecibo'] = '';
        $_SESSION["jsonsalida"]['recibogob'] = '';
        $_SESSION["jsonsalida"]['fecharecibogob'] = '';
        $_SESSION["jsonsalida"]['horarecibogob'] = '';
        
        $_SESSION["jsonsalida"]['radicacion'] = '';
        $_SESSION["jsonsalida"]['estadoradicacion'] = '';
        $_SESSION["jsonsalida"]['estadoradicacionvue'] = '';
        $_SESSION["jsonsalida"]['operacion'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);

        $numliq = '';
        $numrec = '';
        if (isset($_SESSION["entrada"]["idliquidacion"]) && trim($_SESSION["entrada"]["idliquidacion"]) != '') {
            $numliq = $_SESSION["entrada"]["idliquidacion"];
        } else {
            if (isset($_SESSION["entrada"]["numerorecuperacion"]) && trim($_SESSION["entrada"]["numerorecuperacion"]) != '') {
                $numrec = $_SESSION["entrada"]["numerorecuperacion"];
            } else {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó ni número de liquidación ni número de recuperación';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Conexion con la BD
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        //
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            if (!isset($_SESSION["entrada"]["identificacioncontrol"]) || $_SESSION["entrada"]["identificacioncontrol"] == '' |
                    !isset($_SESSION["entrada"]["nombrecontrol"]) || $_SESSION["entrada"]["nombrecontrol"] == '' ||
                    !isset($_SESSION["entrada"]["emailcontrol"]) || $_SESSION["entrada"]["emailcontrol"] == '' ||
                    !isset($_SESSION["entrada"]["celularcontrol"]) || $_SESSION["entrada"]["celularcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportaron los datos de control del usuario público';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
            $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
            $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
            $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
            $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        } else {
            $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
            if ($usux === false || empty($usux) ||
                    $usux["eliminado"] == 'SI' ||
                    ltrim(trim((string)$usux["fechaactivacion"]), "0") == '' ||
                    ltrim(trim((string)$usux["fechainactivacion"]), "0") != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario reportado no está habilitado o no existe en la BD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
            $_SESSION["jsonsalida"]["identificacioncontrol"] = $usux["identificacion"];
            $_SESSION["jsonsalida"]["nombrecontrol"] = $usux["nombreusuario"];
            $_SESSION["jsonsalida"]["emailcontrol"] = $usux["email"];
            $_SESSION["jsonsalida"]["celularcontrol"] = $usux["celular"];
        }

        // ********************************************************************** //
        // Consulta la liquidacion
        // ********************************************************************** //
        $liq = false;
        if ($numliq != '') {
            $liq = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $numliq, 'L');
        } else {
            $liq = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $numrec, 'NR');
        }

        //
        if ($liq === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no encontrada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $_SESSION["jsonsalida"]['idliquidacion'] = trim((string)$liq['idliquidacion']);
        $_SESSION["jsonsalida"]['numerorecuperacion'] = trim((string)$liq['numerorecuperacion']);
        $_SESSION["jsonsalida"]['tipotramite'] = trim((string)$liq['tipotramite']);
        $_SESSION["jsonsalida"]['subtipotramite'] = trim((string)$liq['subtipotramite']);
        $_SESSION["jsonsalida"]['fecha'] = trim((string)$liq['fecha']);
        $_SESSION["jsonsalida"]['hora'] = trim((string)$liq['hora']);
        $_SESSION["jsonsalida"]['idestado'] = trim((string)$liq['idestado']);
        $_SESSION["jsonsalida"]['matricula'] = trim((string)$liq['idmatriculabase']);
        $_SESSION["jsonsalida"]['proponente'] = trim((string)$liq['idproponentebase']);
        if (trim($_SESSION["jsonsalida"]['matricula']) == '') {
            $_SESSION["jsonsalida"]['matricula'] = trim((string)$liq['idexpedientebase']);
        }
        $_SESSION["jsonsalida"]['valor'] = trim((string)$liq['valortotal']);
        
        $_SESSION["jsonsalida"]['recibo'] = trim((string)$liq['numerorecibo']);
        $_SESSION["jsonsalida"]['fecharecibo'] = trim((string)$liq['fecharecibo']);
        $_SESSION["jsonsalida"]['horarecibo'] = trim((string)$liq['horarecibo']);
        
        $_SESSION["jsonsalida"]['recibogob'] = trim((string)$liq['numerorecibogob']);
        $_SESSION["jsonsalida"]['fecharecibogob'] = trim((string)$liq['fecharecibogob']);
        $_SESSION["jsonsalida"]['horarecibogob'] = trim((string)$liq['horarecibogob']);
        
        $_SESSION["jsonsalida"]['radicacion'] = trim((string)$liq['numeroradicacion']);               
        $_SESSION["jsonsalida"]['operacion'] = trim((string)$liq['numerooperacion']);

        $_SESSION["jsonsalida"]['tipoidentificacion'] = trim((string)$liq['idtipoidentificacioncliente']);
        $_SESSION["jsonsalida"]['identificacion'] = trim((string)$liq['identificacioncliente']);
        $_SESSION["jsonsalida"]['nombre'] = trim((string)$liq['nombre1cliente'] . ' ' . $liq['nombre2cliente'] . ' ' . $liq['apellido1cliente'] . ' ' . $liq['apellido2cliente']);
        if ($_SESSION["jsonsalida"]['nombre'] == '') {
            $_SESSION["jsonsalida"]['nombre'] = trim((string)$liq['nombrecliente']) . ' ' . trim((string)$liq['apellidocliente']);
        }
        $_SESSION["jsonsalida"]['email'] = trim((string)$liq['email']);
        $_SESSION["jsonsalida"]['celular'] = trim((string)$liq['movil']);

        $detallado = array ();
        $expedientes = array ();
        foreach ($liq['liquidacion'] as $liqx) {
            $detallado[] = $liqx;
        }
        foreach ($liq['expedientes'] as $expx) {
            $expedientes[] = $expx;
        }
        
        //
        $_SESSION["jsonsalida"]['liquidaciondetalle'] = $detallado;
        $_SESSION["jsonsalida"]['expedientesdetalle'] = $expedientes;

        //
        if (trim((string)$_SESSION["jsonsalida"]["radicacion"]) != '') {
            $cb = retornarRegistroMysqliApi($mysqli,'mreg_est_codigosbarras',"codigobarras='" . $_SESSION["jsonsalida"]['radicacion'] . "'");
            if ($cb && !empty ($cb)) {
                $_SESSION["jsonsalida"]["estadoradicacion"] = $cb["estadofinal"];
                $_SESSION["jsonsalida"]["estadoradicacionvue"] = retornarRegistroMysqliApi($mysqli,'mreg_codestados_rutamercantil',"id='" . $cb["estadofinal"] . "'", "codigorutavue");
            }
        }
        
        // Adiciona links
        $tt = false;
        $urlPago = '';
        $urlVolante = '';

        //
        if ($liq["idestado"] <= '05' || $liq["idestado"] == '19' || $liq["idestado"] == '33') {
            if ($liq["valortotal"] != 0) {
                if (trim($liq['subtipotramite']) != '') {
                    $tt = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $liq['subtipotramite'] . "'");
                } else {
                    if (trim($liq['tipotramite']) != '') {
                        $tt = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $liq['tipotramite'] . "'");
                    }
                }
                if ($tt && !empty($tt)) {
                    if ($tt["exigefirmadoelectronico"] == 'si') {
                        if ($liq["idestado"] == '19' || $liq["idestado"] == '33') {
                            $urlPago = TIPO_HTTP . HTTP_HOST . '/lanzarVirtual.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&_opcion=pagoelectronico&_numrec=' . trim($liq['numerorecuperacion']);
                            $aleatVolante = \funcionesGenerales::generarAleatorioAlfanumerico20($mysqli);
                            $arr = $liq;
                            $arrLiquidacion = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', "idliquidacion=" . $_SESSION["jsonsalida"]['idliquidacion'], "idsec");
                            $textoBancos = retornarRegistroMysqliApi($mysqli, 'textos_propios', "idtexto='bancos_y_corresponsales'", "texto");
                            $namevol = str_replace($_SESSION["generales"]["pathabsoluto"] . "/tmp/", "", armarPdfVolantePagoBancosCucutaNuevo($mysqli, $arr, $arrLiquidacion, $arr["valortotal"], 'justificado', 'bancos', $textoBancos, '', $aleatVolante));
                            $urlVolante = TIPO_HTTP . HTTP_HOST . '/tmp/' . $namevol;
                        }
                    } else {
                        $urlPago = TIPO_HTTP . HTTP_HOST . '/lanzarVirtual.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&_opcion=pagoelectronico&_numrec=' . trim($liq['numerorecuperacion']);
                        $aleatVolante = \funcionesGenerales::generarAleatorioAlfanumerico20($mysqli);
                        $arr = $liq;
                        $arrLiquidacion = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', "idliquidacion=" . $_SESSION["jsonsalida"]['idliquidacion'], "idsec");
                        $textoBancos = retornarRegistroMysqliApi($mysqli, 'textos_propios', "idtexto='bancos_y_corresponsales'", "texto");
                        $namevol = str_replace($_SESSION["generales"]["pathabsoluto"] . "/tmp/", "", armarPdfVolantePagoBancosCucutaNuevo($mysqli, $arr, $arrLiquidacion, $arr["valortotal"], 'justificado', 'bancos', $textoBancos, '', $aleatVolante));
                        $urlVolante = TIPO_HTTP . HTTP_HOST . '/tmp/' . $namevol;
                    }
                }
            }
        }

        //
        $_SESSION["jsonsalida"]["urlPago"] = $urlPago;
        $_SESSION["jsonsalida"]["urlVolante"] = $urlVolante;
        $_SESSION["jsonsalida"]["sistemaorigen"] = $liq["sistemacreacion"];

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
