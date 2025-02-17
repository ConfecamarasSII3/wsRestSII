<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait recibirCambioEstadoRadicado {

    public function recibirCambioEstadoRadicado(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array ();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        /*
          $dataTexto = file_get_contents("php://input");
          $data = json_decode($dataTexto);

          $_SESSION["entrada"] = array ();
          foreach ($data as $key => $valor) {
          $_SESSION["entrada"][$key] = $valor;
          }
         */
        // Retorna error en caso que alguna variable requerida no sea recibida
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("radicado", true);
        $api->validarParametro("estado", true);
        $api->validarParametro("fecha", true);
        $api->validarParametro("hora", true);
        $api->validarParametro("usuario", true);
        $api->validarParametro("sede", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('recibirCambioEstadoRadicado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el radicado 
        // ********************************************************************** //         
        $arrTem = consumirWsConsultarRuta(ltrim($_SESSION["entrada"]["radicado"], '0'));
        if ($arrTem === false || empty($arrTem)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Radicado no encontrado en la BD del sistema de información';
            \log::general2('recibirCambioEstadoRadicado_' . date("Ymd"), __FUNCTION__, 'Radicado no encontrado en la BD del sistema de información.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $txt = '';
            if ($arrTem == "0001") {
                $txt .= 'Error conectando con el SIREP (' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]) . ')';
            }
            if ($arrTem == "0002") {
                $txt .= 'Código de barras ' . ltrim($_SESSION["entrada"]["radicado"], '0') . ' no encontrado en SIREP';
            }
            if (trim($txt) != '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $txt;
                \log::general2('recibirCambioEstadoRadicado_' . date("Ymd"), __FUNCTION__, $txt);
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ******************************************************************************** //
        // Obtiene los códigos de estado de rutas según trámite
        // ******************************************************************************** //
        if ($arrTem["tramite"] == '09' || $arrTem["tramite"] == '53') {
            $arrEsts = retornarRegistros('mreg_codestados_rutaproponentes', "1=1", "id");
        } else {
            $arrEsts = retornarRegistros('mreg_codestados_rutamercantil', "1=1", "id");
        }

        // ******************************************************************************** //
        // Validar el estado actual - Si corresponde a estado terminal finaliza 
        // ******************************************************************************** //
        $rutas = array ();
        foreach ($arrEsts as $comp) {
            $rutas[$comp["id"]] = $comp["estadoterminal"];
        }

        // 2017-08-15: JINT: Se excluye del control el estado 05
        if ($rutas[$arrTem["estado"]] == 'S' && $arrTem["estado"] != '05' && $arrTem["estado"] != '17') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El radicado se encuentra en estado terminal.';
            \log::general2('api_recibirCambioEstadoRadicado_' . date("Ymd"), __FUNCTION__, 'El radicado se encuentra en estado terminal.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // **************************************************** //
        // Verifica que el estado solicitado sea válido
        // **************************************************** //


        $estadoRecibido = sprintf("%02s", $_SESSION["entrada"]["estado"]);

        if (!isset($rutas[$estadoRecibido])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El estado (' . $estadoRecibido . ') no es válido';
            \log::general2('api_recibirCambioEstadoRadicado_' . date("Ymd"), __FUNCTION__, 'El estado (' . $estadoRecibido . ') no es válido.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        unset($rutas);

        // ********************************************************************** //
        // Verifica que el usuario asignado a la ruta sea un usuario permitido
        // ********************************************************************** //
        if (SISTEMA_REGISTRO == 'SIREP') {
            $arrUsu = retornarRegistro('usuarios', "idcodigosirepcaja='" . $_SESSION["entrada"]["usuario"] . "' or idcodigosirepdigitacion='" . $_SESSION["entrada"]["usuario"] . "' or idcodigosirepregistro='" . $_SESSION["entrada"]["usuario"] . "'");
        } else {
            $arrUsu = retornarRegistro('usuarios', "idusuario='" . $_SESSION["entrada"]["usuario"] . "'");
        }
        if ($arrUsu === false || empty($arrUsu)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario asignado al estado no es un usuario válido';
            \log::general2('recibirCambioEstadoRadicado_' . date("Ymd"), __FUNCTION__, 'El usuario asignado al estado no es un usuario válido.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (ltrim($arrUsu["fechaactivacion"], "0") == '' || ltrim($arrUsu["fechainactivacion"], "0") != '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario asignado al estado no se encuentra activado';
            \log::general2('recibirCambioEstadoRadicado_' . date("Ymd"), __FUNCTION__, 'El usuario asignado al estado no se encuentra activado.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Actualiza el cambio de estado en SIREP
        // ********************************************************************** //

        if (SISTEMA_REGISTRO == 'SIREP') {
            $res = consumirWsActualizaEstadoCodigoBarras(ltrim($_SESSION["entrada"]["radicado"], '0'), $estadoRecibido, $_SESSION["entrada"]["usuario"], 'E', 'N');
            if ($res["codigoError"] != '0000') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible bajar actualización de estado a BD del SIREP.';
                \log::general2('recibirCambioEstadoRadicado_' . date("Ymd"), __FUNCTION__, 'No fue posible bajar actualización de estado a BD del SIREP.');
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // Actualiza el cambio de estado en SII 
        // ********************************************************************** //
        $arrCampos1 = array (
            'estadofinal',
            'operadorfinal',
            'fechaestadofinal',
            'horaestadofinal'
        );
        $arrValores1 = array (
            "'" . $estadoRecibido . "'",
            "'" . $_SESSION["entrada"]["usuario"] . "'",
            "'" . $_SESSION["entrada"]["fecha"] . "'",
            "'" . sprintf("%06s", $_SESSION["entrada"]["hora"]) . "'"
        );
        regrabarRegistros('mreg_est_codigosbarras', $arrCampos1, $arrValores1, "codigobarras='" . $_SESSION["entrada"]["radicado"] . "'");

        $arrCampos2 = array (
            'codigobarras',
            'fecha',
            'hora',
            'estado',
            'impresiones',
            'formareparto',
            'operador',
            'sucursal'
        );
        $arrValores2 = array (
            "'" . ltrim($_SESSION["entrada"]["radicado"], '0') . "'",
            "'" . $_SESSION["entrada"]["fecha"] . "'",
            "'" . sprintf("%06s", $_SESSION["entrada"]["hora"]) . "'",
            "'" .$estadoRecibido . "'",
            "''",
            "''",
            "'" . $_SESSION["entrada"]["usuario"] . "'",
            "'" . $_SESSION["entrada"]["sede"] . "'"
        );
        insertarRegistros('mreg_est_codigosbarras_documentos', $arrCampos2, $arrValores2);


        // ************************************************************************** //
        // Acciones si estado corresponde a enviado a archivo (15) o  archivado (16)
        // ************************************************************************** //

        if ($estadoRecibido== '15' || $estadoRecibido == '16') {

            // ********************************************************************* //
            // Validar si tiene matriculas asociadas y CB en proceso
            // ******************************************************************** //
            $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME);

            if ($mysqli->connect_error) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            // Consultar Matriculas asociadas a al recibo correspondiente al código de barras
            $queryRecMat = "SELECT rg.recibo,rgd.matricula "
                    . "FROM mreg_recibosgenerados rg "
                    . "LEFT JOIN mreg_recibosgenerados_detalle rgd ON rg.recibo=rgd.recibo "
                    . "WHERE rg.codigobarras='" . ltrim($_SESSION["entrada"]["radicado"], '0') . "' and rgd.matricula<>''";


            $mysqli->set_charset("utf8");
            $resQueryRecMat = $mysqli->query($queryRecMat);


            if (!empty($resQueryRecMat)) {
                while ($RecMatTemp = $resQueryRecMat->fetch_array(MYSQL_ASSOC)) {

                    // Consultar si para la matricula asociada se encuentran otros códigos de barras pendientes
                    $queryCBMat = "SELECT cb.matricula,cb.codigobarras,cb.estadofinal,ruta.estadoterminal 
                        FROM mreg_est_codigosbarras cb 
                        LEFT JOIN mreg_codestados_rutamercantil ruta ON ruta.id=cb.estadofinal 
                        WHERE cb.matricula='" . $RecMatTemp["matricula"] . "' and ruta.estadoterminal='N'";

                    $mysqli->set_charset("utf8");
                    $resQueryCBMat = $mysqli->query($queryCBMat);


                    //Si no encuentra resultados se cambia el estado de los datos en inscritos y matriculados
                    if (empty($resQueryCBMat->num_rows)) {
                        $arrCampos = array (
                            'ctrestdatos',
                            'fecactualizacion',
                            'compite360',
                            'rues'
                        );
                        $arrValores = array (
                            "'6'",
                            "'" . date("Ymd") . "'",
                            "'NO'",
                            "'NO'"
                        );
                        regrabarRegistros('mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $RecMatTemp["matricula"] . "'");
                        regrabarRegistros('mreg_est_matriculados', array ("estdatos"), array ("'6'"), "matricula='" . $RecMatTemp["matricula"] . "'");
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
            $regRueRadicacion = retornarRegistro('mreg_rue_radicacion', "codigobarras='" . ltrim($_SESSION["entrada"]["radicado"], '0') . "'");
            if (!empty($regRueRadicacion) && ($regRueRadicacion != false)) {

                $ret = consumirMR03N($regRueRadicacion["numerointernorue"], '11', '');

                if ($ret != false) {

                    $arrCampos3 = array (
                        'estadotransaccion',
                        'fecharespuesta',
                        'horarespuesta'
                    );
                    $arrValores3 = array (
                        "'11'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'"
                    );
                    regrabarRegistros('mreg_rue_radicacion', $arrCampos3, $arrValores3, "codigobarras='" . ltrim($_SESSION["entrada"]["radicado"], '0') . "'");

                    $arrCampos4 = array (
                        'numerointernorue',
                        'fecha',
                        'hora',
                        'usuario',
                        'estado',
                        'origen'
                    );
                    $arrValores4 = array (
                        "'" . $regRueRadicacion["numerointernorue"] . "'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'" . $_SESSION["generales"]["codigousuario"] . "'",
                        "'11'",
                        "'wsRestSII'"
                    );
                    insertarRegistros('mreg_rue_radicacion_estados', $arrCampos4, $arrValores4);

                    if (SISTEMA_REGISTRO == 'SIREP') {
                        consumirWsVRRADRUE('E', $regRueRadicacion["numerointernorue"], $_SESSION["generales"]["codigousuario"], '11');
                    }
                }
            }
        }

        // **************************************************************************** //
        // Crea el log con el resultado
        // **************************************************************************** //
//        \log::general2('recibirCambioEstadoRadicado_' . date("Ymd"), __FUNCTION__, 'Termina proceso de cambio de estado radicado. Resultado: ' . serialize($_SESSION["jsonsalida"]));
        // **************************************************************************** //
        // Actualiza el log de consultas al API
        // **************************************************************************** //
        $json = $api->json($_SESSION["jsonsalida"]);
        actualizarLog('009', $_SESSION["entrada"]["usuariows"], 'wsRestSII/recibirCambioEstadoRadicado', '', '', '', $json, '', '', '', '', $_SESSION["entrada"]["radicado"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
