<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait recibirCambioEstadoRadicado {

    public function recibirCambioEstadoRadicado(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesEspeciales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // Retorna error en caso que alguna variable requerida no sea recibida
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("radicado", true);
        $api->validarParametro("estado", true);
        $api->validarParametro("fecha", true);
        $api->validarParametro("hora", true);
        $api->validarParametro("usuario", true);
        $api->validarParametro("sede", false);

        if (!$api->validarToken('recibirCambioEstadoRadicado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el radicado y su reporte de ruta
        // ********************************************************************** //         

        $arrTem = \funcionesRegistrales::retornarCodigoBarras($mysqli, ltrim($_SESSION["entrada"]["radicado"], '0'));
        if ($arrTem === false || empty($arrTem)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Radicado no encontrado en la BD del sistema de información';
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], ltrim($_SESSION["entrada"]["radicado"], '0') . ': El radicado no fue encontrado en la BD del SII.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $txt = '';
            if ($arrTem == "0001") {
                $txt .= 'Error conectando con el SII (' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]) . ')';
            }
            if ($arrTem == "0002") {
                $txt .= 'El Radicado no fue encontrado en SII';
            }
            if (trim($txt) != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $txt;
                \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], $txt);
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ******************************************************************************** //
        // JINT: 2021-03-12
        // Filtra estados para no permitir que trámites archivados se reactiven
        // ******************************************************************************** //
        if ($arrTem["estado"] == '16') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Estado recibido no puede ser procesado, el codigo de barras se encuentra archivado');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ******************************************************************************** //
        // JINT: 2021-03-12
        // Filtra estados para no permitir que trámites archivados se reactiven
        // ******************************************************************************** //
        if ($arrTem["estado"] == '15' && $_SESSION["entrada"]["estado"] != '16') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Estado recibido no puede ser procesado, el codigo de barras se encuentra enviado a archivo');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ******************************************************************************** //
        // Obtiene los códigos de estado de rutas según trámite
        // ******************************************************************************** //
        if ($arrTem["tramite"] == '09' || $arrTem["tramite"] == '53') {
            $arrEsts = retornarRegistrosMysqliApi($mysqli, 'mreg_codestados_rutaproponentes', "1=1", "id");
        } else {
            $arrEsts = retornarRegistrosMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "1=1", "id");
        }

        // ******************************************************************************** //
        // Validar el estado actual - Si corresponde a estado terminal finaliza 
        // ******************************************************************************** //
        $rutas = array();
        foreach ($arrEsts as $comp) {
            $rutas[$comp["id"]] = $comp["estadoterminal"];
        }

        // **************************************************** //
        // Verifica que el estado solicitado sea válido
        // **************************************************** //

        $estadoActual = $arrTem["estado"];
        $estadoRecibido = sprintf("%02s", $_SESSION["entrada"]["estado"]);
        \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], ltrim($_SESSION["entrada"]["radicado"], '0') . ': El radicado se encuentra en estado [' . $estadoActual . '] y se desea actualizar a [' . $estadoRecibido . ']');
        if (!isset($rutas[$estadoRecibido])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El estado (' . $estadoRecibido . ') no es válido';
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], ltrim($_SESSION["entrada"]["radicado"], '0') . ': El estado [' . $estadoRecibido . '] no es válido.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        unset($rutas);

        // ********************************************************************** //
        // Verifica que el usuario asignado a la ruta sea un usuario permitido
        // ********************************************************************** //
        //2017-09-01 - SOLAMENTE CONFRONTA CON BD DE USUARIOS DEL SII
        $arrUsu = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["usuario"] . "'");

        if ($arrUsu === false || empty($arrUsu)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario asignado al estado no es un usuario válido';
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], ltrim($_SESSION["entrada"]["radicado"], '0') . ':El usuario [' . $_SESSION["entrada"]["usuario"] . '] asignado al estado no es válido.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //2017-09-08 - OBTIENE EL OPERADOR SIREP
        $ope = '';
        $ope = $_SESSION["entrada"]["usuario"];

        if (ltrim((string)$arrUsu["fechaactivacion"], "0") == '' || ltrim((string)$arrUsu["fechainactivacion"], "0") != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario asignado al estado no se encuentra activado';
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], ltrim($_SESSION["entrada"]["radicado"], '0') . ':El usuario [' . $_SESSION["entrada"]["usuario"] . '] asignado al estado no se encuentra activado.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Actualiza el cambio de estado en SII 
        // ********************************************************************** //
        $arrCampos1 = array(
            'estadofinal',
            'operadorfinal',
            'fechaestadofinal',
            'horaestadofinal'
        );
        $arrValores1 = array(
            "'" . $estadoRecibido . "'",
            "'" . $ope . "'",
            "'" . $_SESSION["entrada"]["fecha"] . "'",
            "'" . sprintf("%06s", $_SESSION["entrada"]["hora"]) . "'"
        );
        regrabarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', $arrCampos1, $arrValores1, "codigobarras='" . $_SESSION["entrada"]["radicado"] . "'");
        $detalle = 'Cambio estado del codigo de barras No. ' . $_SESSION["entrada"]["radicado"] . ', estado final: ' . $estadoRecibido . ', Operador: ' . $ope;
        actualizarLogMysqliApi($mysqli, '069', $_SESSION["generales"]["codigousuario"], 'recibirCambioEstadoRadicado.php', '', '', '', $detalle, '', '');

        $arrCampos2 = array(
            'codigobarras',
            'fecha',
            'hora',
            'estado',
            'impresiones',
            'formareparto',
            'operador',
            'sucursal'
        );
        $arrValores2 = array(
            "'" . ltrim((string)$_SESSION["entrada"]["radicado"], '0') . "'",
            "'" . $_SESSION["entrada"]["fecha"] . "'",
            "'" . sprintf("%06s", $_SESSION["entrada"]["hora"]) . "'",
            "'" . $estadoRecibido . "'",
            "''",
            "''",
            "'" . $ope . "'",
            "'" . $_SESSION["entrada"]["sede"] . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', $arrCampos2, $arrValores2);

        // ************************************************************************** //
        // Acciones si estado corresponde a enviado a archivo (15) o  archivado (16)
        // ************************************************************************** //

        if ($estadoRecibido == '15' || $estadoRecibido == '16') {
            // ********************************************************************* //
            // Validar si tiene matriculas asociadas y CB en proceso
            // ******************************************************************** //
            // Consultar Matriculas asociadas a al recibo correspondiente al código de barras
            $queryRecMat = "SELECT rg.recibo,rgd.matricula "
                    . "FROM mreg_recibosgenerados rg "
                    . "LEFT JOIN mreg_recibosgenerados_detalle rgd ON rg.recibo=rgd.recibo "
                    . "WHERE rg.codigobarras='" . ltrim((string)$_SESSION["entrada"]["radicado"], '0') . "' and rgd.matricula<>''";

            $mysqli->set_charset("utf8");
            $resQueryRecMat = $mysqli->query($queryRecMat);

            if (!empty($resQueryRecMat)) {
                while ($RecMatTemp = $resQueryRecMat->fetch_array(MYSQLI_ASSOC)) {
                    // ********************************************************************* //
                    // Consultar si se encuentran otros códigos de barras pendientes
                    // ******************************************************************** //
                    $queryCBMat = "SELECT cb.matricula,cb.codigobarras,cb.estadofinal,ruta.estadoterminal 
                        FROM mreg_est_codigosbarras cb 
                        LEFT JOIN mreg_codestados_rutamercantil ruta ON ruta.id=cb.estadofinal 
                        WHERE cb.matricula='" . $RecMatTemp["matricula"] . "' and ruta.estadoterminal='N'";

                    $mysqli->set_charset("utf8");
                    $resQueryCBMat = $mysqli->query($queryCBMat);

                    // ********************************************************************* //
                    // Cambia el estado de los datos
                    // ******************************************************************** //
                    if (empty($resQueryCBMat->num_rows)) {
                        $arrCampos = array(
                            'ctrestdatos',
                            'fecactualizacion',
                            'compite360',
                            'rues',
                            'ivc'
                        );
                        $arrValores = array(
                            "'6'",
                            "'" . date("Ymd") . "'",
                            "'NO'",
                            "'NO'",
                            "'NO'"
                        );
                        unset($_SESSION["expedienteactual"]);
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $RecMatTemp["matricula"] . "'");
                    }
                    $resQueryCBMat->free();
                    unset($resQueryCBMat);
                }
                $resQueryRecMat->free();
            } else {
                //No encontro 
            }
            $mysqli->close();
            unset($resQueryRecMat);

            // ********************************************************************* //
            // Validar si corresponde a trámite  RUES
            // ******************************************************************** //
            $regRueRadicacion = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "codigobarras='" . ltrim($_SESSION["entrada"]["radicado"], '0') . "'");
            if (!empty($regRueRadicacion) && ($regRueRadicacion != false)) {

                
                $ret = \funcionesRues::consumirMR03N($regRueRadicacion["numerointernorue"], '11', '');

                if ($ret != false) {

                    $arrCampos3 = array(
                        'estadotransaccion',
                        'fecharespuesta',
                        'horarespuesta'
                    );
                    $arrValores3 = array(
                        "'11'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'"
                    );
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion', $arrCampos3, $arrValores3, "codigobarras='" . ltrim($_SESSION["entrada"]["radicado"], '0') . "'");

                    $arrCampos4 = array(
                        'numerointernorue',
                        'fecha',
                        'hora',
                        'usuario',
                        'estado',
                        'origen'
                    );
                    $arrValores4 = array(
                        "'" . $regRueRadicacion["numerointernorue"] . "'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'" . $_SESSION["entrada"]["usuario"] . "'",
                        "'11'",
                        "'wsRestSII'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion_estados', $arrCampos4, $arrValores4);
                }
            }
        }

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
