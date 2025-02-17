<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarCertificadoCapas {

    public function consultarCertificadoCapas(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/wsRR18N.class.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idsolicitud"] = '';
        $_SESSION["jsonsalida"]["matricula"] = '';
        $_SESSION["jsonsalida"]["idclase"] = '';
        $_SESSION["jsonsalida"]["identificacion"] = '';
        $_SESSION["jsonsalida"]["servicio"] = '';
        $_SESSION["jsonsalida"]["origen"] = '';
        $_SESSION["jsonsalida"]["recibo"] = '';
        $_SESSION["jsonsalida"]["usuariosolicitante"] = '';
        $_SESSION["jsonsalida"]["codigoverificacion"] = '';
        $_SESSION["jsonsalida"]["linkverificacion"] = '';
        $_SESSION["jsonsalida"]["hashverificacion"] = '';
        $_SESSION["jsonsalida"]["certificado"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", false);
        $api->validarParametro("idclase", false);
        $api->validarParametro("identificacion", false);
        $api->validarParametro("servicio", true);
        $api->validarParametro("origen", true);

        //
        $_SESSION["entrada"]["origen"] = strtolower($_SESSION["entrada"]["origen"]);

        //
        if ($_SESSION["entrada"]["origen"] == 'normal' ||
                $_SESSION["entrada"]["origen"] == 'gasadm' ||
                $_SESSION["entrada"]["origen"] == 'entofi' ||
                $_SESSION["entrada"]["origen"] == 'afiliacion' ||
                $_SESSION["entrada"]["origen"] == 'prepago') {
            $api->validarParametro("recibo", true);
        }

        //
        if ($_SESSION["entrada"]["origen"] == 'convenio') {
            $api->validarParametro("idsolicitud", true);
        }

        //
        if ($_SESSION["entrada"]["origen"] == 'consulta' || $_SESSION["entrada"]["origen"] == 'convenio') {
            $api->validarParametro("usuariosolicitante", true);
            // $api->validarParametro("claveusuariosolicitante", true);
        }

        //
        // if ($_SESSION["entrada"]["origen"] == 'convenio') {
        //    $api->validarParametro("claveusuariosolicitante", true);
        // }
//
        if (!isset($_SESSION["entrada"]["recibo"])) {
            $_SESSION["entrada"]["recibo"] = '';
        }
        if (!isset($_SESSION["entrada"]["origen"])) {
            $_SESSION["entrada"]["origen"] = '';
        }
        if (!isset($_SESSION["entrada"]["idsolicitud"])) {
            $_SESSION["entrada"]["idsolicitud"] = '';
        }
        if (!isset($_SESSION["entrada"]["identificacion"])) {
            $_SESSION["entrada"]["identificacion"] = '';
        } else {
            $_SESSION["entrada"]["identificacion"] = str_replace(array(",", ".", "-", " "), "", (string) $_SESSION["entrada"]["identificacion"]);
        }
        if (!isset($_SESSION["entrada"]["usuariosolicitante"])) {
            $_SESSION["entrada"]["usuariosolicitante"] = '';
        }
        if (!isset($_SESSION["entrada"]["clavesuariosolicitante"])) {
            $_SESSION["entrada"]["claveusuariosolicitante"] = '';
        }

        //
        if ($_SESSION["entrada"]["idsolicitud"] != '') {
            if (substr($_SESSION["entrada"]["idsolicitud"], 0, 4) != date("Y")) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El número de solicitud debe iniciar por ' . date("Y") . ', por favor verifique';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if ($_SESSION["entrada"]["servicio"] != "01010081" &&
                $_SESSION["entrada"]["servicio"] != "01010082" &&
                $_SESSION["entrada"]["servicio"] != "01010083" &&
                $_SESSION["entrada"]["servicio"] != "01010084" &&
                $_SESSION["entrada"]["servicio"] != "01010085" &&
                $_SESSION["entrada"]["servicio"] != "01010086" &&
                $_SESSION["entrada"]["servicio"] != "01010087") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Servicio asociado a la solicitud erróneo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["servicio"] == "01010085" ||
                $_SESSION["entrada"]["servicio"] == "01010086" ||
                $_SESSION["entrada"]["servicio"] == "01010087") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'las peticiciones asociadas a los servicios 01010085, 01010086, 01010087 aún no se encuentran implementados';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim((string) $_SESSION["entrada"]["matricula"]) == '' && trim((string) $_SESSION["entrada"]["idclase"]) == '' && trim((string) $_SESSION["entrada"]["identificacion"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar el número de matrícula del expediente a generar o la clase y número de identificación del mismo.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim((string) $_SESSION["entrada"]["matricula"]) == '') {
            if (trim((string) $_SESSION["entrada"]["idclase"]) == '' || trim((string) $_SESSION["entrada"]["identificacion"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Al no indicar número de matrícula debe informar la clase de identificación y el número de identificación a generar.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarCertificadoCapas', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["idclase"] == '2') {
            if (\funcionesGenerales::validarDv($_SESSION["entrada"]["identificacion"]) === false) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Indicó que el número de identificación es un nit, al parecer el número de identificación no fue incluido o está erróneo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        $_SESSION["jsonsalida"]["idsolicitud"] = $_SESSION["entrada"]["idsolicitud"];
        $_SESSION["jsonsalida"]["idclase"] = $_SESSION["entrada"]["idclase"];
        $_SESSION["jsonsalida"]["identificacion"] = $_SESSION["entrada"]["identificacion"];
        $_SESSION["jsonsalida"]["servicio"] = $_SESSION["entrada"]["servicio"];
        $_SESSION["jsonsalida"]["origen"] = $_SESSION["entrada"]["origen"];
        $_SESSION["jsonsalida"]["recibo"] = $_SESSION["entrada"]["recibo"];
        $_SESSION["jsonsalida"]["usuariosolicitante"] = $_SESSION["entrada"]["usuariosolicitante"];

        //
        // ********************************************************************** //
        // Abre conexión con la BD
        // ********************************************************************** //             
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["entrada"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["entrada"]["codigoempresa"]);
        }

        //
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Si es consulta, valida que el usuario solicitante exista y esté activo
        // ********************************************************************** //
        if ($_SESSION["entrada"]["origen"] == 'consulta') {
            $usu = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["usuariosolicitante"] . "'");
            if ($usu === false || empty($usu)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario solicitante no encontrado en la BD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($usu["idtipousuario"] == '06') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para hacer esta consulta';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($usu["eliminado"] == 'SI') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no fue encontrado en la BD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($usu["fechainactivacion"] != '00000000' || $usu["fechaactivacion"] == '00000000') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no se encuentra habilitado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // Busca el expediente indicado
        // ********************************************************************** // 
        $arrCamposLogCap = array(
            'usuariows',
            'idsolicitud',
            'ip',
            'fecha',
            'hora',
            'idclase',
            'numid',
            'respuesta',
            'codigocamara',
            'matricula',
            'proponente',
            'codigoerror',
            'mensajeerror',
            'servicio',
            'hashverificacion',
            'codigoverificacion',
            'recibo',
            'origen',
            'usuariosolicitante'
        );

        if ($_SESSION["entrada"]["matricula"] == '') {
            $reg = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $_SESSION["entrada"]["identificacion"] . "' and idclase='" . $_SESSION["entrada"]["idclase"] . "' and matricula > ''", "*", 'U');
            if ($reg === false || empty($reg)) {
                $cam = '';
                $mat = '';
                $est = '';
                $ins = \RR18N::singleton(wsRUE_RR18N);
                $camOrigen = CODIGO_EMPRESA;
                $camDestino = CODIGO_EMPRESA;
                if ($_SESSION["entrada"]["idclase"] == '2') {
                    $x = \funcionesGenerales::separarDv($_SESSION["entrada"]["identificacion"]);
                    $idex = $x["identificacion"];
                    $dvx = $x["dv"];
                } else {
                    $idex = $_SESSION["entrada"]["identificacion"];
                    $dvx = '';
                }
                $parametros = array(
                    'numero_interno' => date("Ymd") . date("His") . $camOrigen . $camDestino,
                    'usuario' => CODIGO_EMPRESA,
                    'numero_identificacion' => $idex
                );
                try {
                    $res = $ins->consultarNumeroIdentificacion($parametros);
                } catch (Exception $e) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible conectarse al RUES';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                $t = (array) $res;
                if ($t["codigo_error"] != '0000') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en respuesta RUES : ' . str_replace(array("'", '"'), "", $t["mensaje_error"]);
                }
                if (isset($t["datos_respuesta"])) {
                    if (!isset($t["datos_respuesta"][0])) {
                        if (ltrim($t["datos_respuesta"]["matricula"], "0") != "") {
                            if (($_SESSION["entrada"]["idclase"] == '2' && sprintf("%02s", $t["codigo_clase_identificacion"]) == '02') || sprintf("%02s", $t["codigo_clase_identificacion"]) !== '02') {
                                if (sprintf("%02s", $t["codigo_categoria_matricula"]) == '00' || sprintf("%02s", $t["codigo_categoria_matricula"]) == '01') {
                                    $cam = $t["datos_respuesta"]["codigo_camara"];
                                    $mat = ltrim($t["datos_respuesta"]["matricula"], "0");
                                    $est = '';
                                    if (sprintf("%02s", $t["datos_respuesta"]["codigo_estado_matricula"]) != "01") {
                                        $est = 'MC';
                                    } else {
                                        $est = 'MA';
                                    }
                                }
                            }
                        }
                    } else {
                        foreach ($t["datos_respuesta"] as $r) {
                            if (ltrim($r["matricula"], "0") != "") {
                                if (($_SESSION["entrada"]["idclase"] == '2' && $r["codigo_clase_identificacion"] == '02') || $r["codigo_clase_identificacion"] !== '02') {
                                    if (sprintf("%02s", $r["codigo_categoria_matricula"]) == '00' || sprintf("%02s", $r["codigo_categoria_matricula"]) == '01') {
                                        $cam = $r["codigo_camara"];
                                        $mat = ltrim($r["matricula"], "0");
                                        $est = '';
                                        if (sprintf("%02s", $r["codigo_estado_matricula"]) != "01") {
                                            $est = 'MC';
                                        } else {
                                            $est = 'MA';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($cam == '') {
                    $arrValores = array(
                        "'" . $_SESSION["entrada"]["usuariows"] . "'",
                        "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
                        "'" . \funcionesGenerales::localizarIP() . "'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'" . $_SESSION["entrada"]["idclase"] . "'",
                        "'" . $_SESSION["entrada"]["identificacion"] . "'",
                        "''",
                        "''",
                        "''",
                        "''",
                        "'0001'",
                        "'" . addslashes('No se encontraron registros asociados a la identificación') . "'",
                        "'" . $_SESSION["entrada"]["servicio"] . "'",
                        "''",
                        "''",
                        "'" . $_SESSION["entrada"]["recibo"] . "'",
                        "'" . $_SESSION["entrada"]["origen"] . "'",
                        "'" . $_SESSION["entrada"]["usuariosolicitante"] . "'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarCertificadoCapas_' . date("Y"), $arrCamposLogCap, $arrValores);
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron registros asociados a la identificación';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    $_SESSION["jsonsalida"]["codigocamara"] = $cam;
                    $_SESSION["jsonsalida"]["matricula"] = $mat;
                    if ($est == 'MA') {
                        $arrValores = array(
                            "'" . $_SESSION["entrada"]["usuariows"] . "'",
                            "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
                            "'" . \funcionesGenerales::localizarIP() . "'",
                            "'" . date("Ymd") . "'",
                            "'" . date("His") . "'",
                            "'" . $_SESSION["entrada"]["idclase"] . "'",
                            "'" . $_SESSION["entrada"]["identificacion"] . "'",
                            "''",
                            "'" . $cam . "'",
                            "'" . $mat . "'",
                            "''",
                            "'0002'",
                            "'" . addslashes('La identificacion se encuentra asociada a un comerciante matriculado (activo) en otra cámara de comercio') . "'",
                            "'" . $_SESSION["entrada"]["servicio"] . "'",
                            "''",
                            "''",
                            "'" . $_SESSION["entrada"]["recibo"] . "'",
                            "'" . $_SESSION["entrada"]["origen"] . "'",
                            "'" . $_SESSION["entrada"]["usuariosolicitante"] . "'"
                        );
                        insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarCertificadoCapas_' . date("Y"), $arrCamposLogCap, $arrValores);
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "0002";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'La identificacion se encuentra asociada a un comerciante matriculado (activo) en otra cámara de comercio';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    } else {
                        $arrValores = array(
                            "'" . $_SESSION["entrada"]["usuariows"] . "'",
                            "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
                            "'" . \funcionesGenerales::localizarIP() . "'",
                            "'" . date("Ymd") . "'",
                            "'" . date("His") . "'",
                            "'" . $_SESSION["entrada"]["idclase"] . "'",
                            "'" . $_SESSION["entrada"]["identificacion"] . "'",
                            "''",
                            "'" . $cam . "'",
                            "'" . $mat . "'",
                            "''",
                            "'0003'",
                            "'" . addslashes('La identificacion se encuentra asociada a un comerciante matriculado (cancelado) en otra cámara de comercio') . "'",
                            "'" . $_SESSION["entrada"]["servicio"] . "'",
                            "''",
                            "''",
                            "'" . $_SESSION["entrada"]["recibo"] . "'",
                            "'" . $_SESSION["entrada"]["origen"] . "'",
                            "'" . $_SESSION["entrada"]["usuariosolicitante"] . "'"
                        );
                        insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarCertificadoCapas_' . date("Y"), $arrCamposLogCap, $arrValores);

                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "0003";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'La identificacion se encuentra asociada a un comerciante matriculado (cancelado) en otra cámara de comercio';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    }
                }
            } else {
                $_SESSION["entrada"]["matricula"] = $reg["matricula"];
                $_SESSION["jsonsalida"]["matricula"] = $reg["matricula"];
            }
        } else {
            $_SESSION["jsonsalida"]["matricula"] = $_SESSION["entrada"]["matricula"];
            $reg = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'");
            if ($reg === false || empty($reg)) {
                $arrValores = array(
                    "'" . $_SESSION["entrada"]["usuariows"] . "'",
                    "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
                    "'" . \funcionesGenerales::localizarIP() . "'",
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "'" . $_SESSION["entrada"]["idclase"] . "'",
                    "'" . $_SESSION["entrada"]["identificacion"] . "'",
                    "''",
                    "''",
                    "'" . $_SESSION["entrada"]["matricula"] . "'",
                    "''",
                    "'0001'",
                    "'" . addslashes('No se encontraron registros asociados a la matricula') . "'",
                    "'" . $_SESSION["entrada"]["servicio"] . "'",
                    "''",
                    "''",
                    "'" . $_SESSION["entrada"]["recibo"] . "'",
                    "'" . $_SESSION["entrada"]["origen"] . "'",
                    "'" . $_SESSION["entrada"]["usuariosolicitante"] . "'"
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarCertificadoCapas_' . date("Y"), $arrCamposLogCap, $arrValores);
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron registros asociados a la matricula';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // Si es convenio valida que no se haya procesado previamente
        if ($_SESSION["entrada"]["origen"] == 'convenio') {
            $solprev = retornarRegistroMysqliApi($mysqli, 'mreg_log_consultarCertificadoCapas_' . substr($_SESSION["entrada"]["idsolicitud"], 0, 4), "usuariows='" . $_SESSION["entrada"]["usuariows"] . "' and idsolicitud='" . $_SESSION["entrada"]["idsolicitud"] . "'");
            if ($solprev && !empty($solprev)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Solicitud previamente realizada (' . $solprev["fecha"] . ' - ' . $solprev["hora"] . ')';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // Si es normal, gasadm o entofi  valida que no se haya procesado previamente
        if ($_SESSION["entrada"]["origen"] == 'normal' ||
                $_SESSION["entrada"]["origen"] == 'gasadm' ||
                $_SESSION["entrada"]["origen"] == 'entofi' ||
                $_SESSION["entrada"]["origen"] == 'afiliacion' ||
                $_SESSION["entrada"]["origen"] == 'prepago') {
            $rec = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $_SESSION["entrada"]["recibo"] . "'");
            if ($rec === false) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Recibo reportado no localizado en la BD (' . $_SESSION["entrada"]["recibo"] . ')';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $solprev = retornarRegistroMysqliApi($mysqli, 'mreg_log_consultarCertificadoCapas_' . substr($rec["fecha"], 0, 4), "usuariows='" . $_SESSION["entrada"]["usuariows"] . "' and recibo='" . $_SESSION["entrada"]["recibo"] . "' and matricula='" . $_SESSION["entrada"]["matricula"] . "'");
            if ($solprev && !empty($solprev)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Solicitud previamente realizada (' . $solprev["fecha"] . ' - ' . $solprev["hora"] . ')';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // en caso de convenio o consulta verifica que el usuario reportado como solicitante
        // si esté activado (usuarios, perfil 07)
        if ($_SESSION["entrada"]["origen"] == 'consulta' || $_SESSION["entrada"]["origen"] == 'convenio') {
            $solprev = retornarRegistroMysqliApi($mysqli, 'mreg_log_consultarCertificadoCapas_' . substr($_SESSION["entrada"]["idsolicitud"], 0, 4), "usuariows='" . $_SESSION["entrada"]["usuariows"] . "' and idsolicitud='" . $_SESSION["entrada"]["idsolicitud"] . "'");
            if ($solprev && !empty($solprev)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Solicitud previamente realizada (' . $solprev["fecha"] . ' - ' . $solprev["hora"] . ')';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $reg["matricula"]);

        //
        $tipo = 'capa' . substr($_SESSION["entrada"]["servicio"], 7);
        $json["data"] = $this->generarCertificadoCapas($mysqli, $arrTem, $tipo);

        //
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["codigoverificacion"] = $json["data"]["codigoverificacion"];
        $_SESSION["jsonsalida"]["hashverificacion"] = hash_hmac('ripemd160', json_encode($json["data"]), 'camara' . CODIGO_EMPRESA);
        $_SESSION["jsonsalida"]["certificado"] = base64_encode(json_encode($json["data"]));
        $_SESSION["jsonsalida"]["linkverificacion"] = $json["data"]["linkverificacion"];

        // **************************************************************************** //
        // Almacena log
        // **************************************************************************** //        
        $arrValores = array(
            "'" . $_SESSION["entrada"]["usuariows"] . "'",
            "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
            "'" . \funcionesGenerales::localizarIP() . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $_SESSION["entrada"]["idclase"] . "'",
            "'" . $_SESSION["entrada"]["identificacion"] . "'",
            "'" . base64_encode((string) json_encode($json["data"])) . "'",
            "'" . CODIGO_EMPRESA . "'",
            "'" . $arrTem["matricula"] . "'",
            "''",
            "'0000'",
            "''",
            "'" . $_SESSION["entrada"]["servicio"] . "'",
            "'" . $_SESSION["jsonsalida"]["hashverificacion"] . "'",
            "'" . $_SESSION["jsonsalida"]["codigoverificacion"] . "'",
            "'" . $_SESSION["jsonsalida"]["recibo"] . "'",
            "'" . $_SESSION["jsonsalida"]["origen"] . "'",
            "'" . $_SESSION["jsonsalida"]["usuariosolicitante"] . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarCertificadoCapas_' . date("Y"), $arrCamposLogCap, $arrValores);

        $mysqli->close();

        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $arrTem
     * @return type array
     */
    public function generarCertificadoCapas($mysqli, $arrTem, $tipo) {

        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_codigos_certificas', "1=1", "id");
        $codcerts = array();
        foreach ($temx as $tx) {
            $codcerts[$tx["id"]] = $tx;
        }

        $jsonsal = array();
        $codver = CODIGO_EMPRESA . date("Ymd") . \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);

        // ********************************************************************** //
        // Retornar el expediente recuperado
        // ********************************************************************** // 
        $jsonsal["codigoverificacion"] = $codver;
        $jsonsal["linkverificacion"] = TIPO_HTTP . HTTP_HOST . '/lanzarVirtual.php?_opcion=verificarcertificadoporcapas&_codigoverificacion=' . $codver;
        $jsonsal["codigo_camara"] = CODIGO_EMPRESA;
        $jsonsal["nombre_camara"] = RAZONSOCIAL;
        $jsonsal["matricula_inscripcion"] = ltrim($arrTem["matricula"], "0");
        $jsonsal["nombre_o_razon_social"] = trim($arrTem["nombre"]);
        $jsonsal["pimer_nombre"] = trim($arrTem["nom1"]);
        $jsonsal["segundo_nombre"] = trim($arrTem["nom2"]);
        $jsonsal["primer_apellido"] = trim($arrTem["ape1"]);
        $jsonsal["segundo_apellido"] = trim($arrTem["ape2"]);
        $jsonsal["sigla"] = trim($arrTem["sigla"]);
        $jsonsal["tipo_identificacion"] = $arrTem["tipoidentificacion"];
        $jsonsal["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $arrTem["tipoidentificacion"] . "'", "descripcion");
        $jsonsal["numero_identificacion"] = ltrim($arrTem["identificacion"], "0");
        if (ltrim((string) $arrTem["nit"], "0")) {
            $sepide = \funcionesGenerales::separarDv($arrTem["nit"]);
            $jsonsal["nit"] = $sepide["identificacion"];
            $jsonsal["dv"] = $sepide["dv"];
        }
        if ($tipo > 'capa1') {
            if ($arrTem["organizacion"] == '01') {
                $jsonsal["genero"] = $arrTem["sexo"];
            }
        }
        $jsonsal["organizacion_juridica"] = $arrTem["organizacion"];
        $jsonsal["descripcion_organizacion_juridica"] = retornarRegistroMysqliApi($mysqli, 'bas_organizacionjuridica', "id='" . $arrTem["organizacion"] . "'", "descripcion");
        $jsonsal["categoria"] = $arrTem["categoria"];
        $jsonsal["descripcion_categoria"] = retornarRegistroMysqliApi($mysqli, 'bas_categorias', "id='" . $arrTem["categoria"] . "'", "descripcion");
        $jsonsal["estado_matricula_inscripcion"] = $arrTem["estadomatricula"];
        $jsonsal["descripcion_estado_matricula_inscripcion"] = retornarRegistroMysqliApi($mysqli, 'mreg_estadomatriculas', "id='" . $arrTem["estadomatricula"] . "'", "descripcion");
        $jsonsal["fecha_matricula_inscripcion"] = $arrTem["fechamatricula"];
        $jsonsal["fecha_renovacion"] = $arrTem["fecharenovacion"];
        $jsonsal["ultimo_ano_renovado"] = $arrTem["ultanoren"];
        $jsonsal["fecha_cancelacion"] = $arrTem["fechacancelacion"];
        $jsonsal["estado_de_disolucion"] = $arrTem["estadisuelta"];
        if ($arrTem["norenovado"] == 'si') {
            $jsonsal["estado_de_renovacion"] = 'No renovado';
        } else {
            $jsonsal["estado_de_renovacion"] = 'Al día con la renovación';
        }

        if ($tipo > 'capa2') {
            $jsonsal["fecha_vencimiento"] = $arrTem["fechavencimiento"];
        }
        $jsonsal["tamano_empresarial"] = $arrTem["tamanoempresarial957"];

        //
        $jsonsal["ciiu_principal"] = $arrTem["ciius"][1];
        $jsonsal["descripcion_ciiu_principal"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $arrTem["ciius"][1] . "'", "descripcion");
        if ($arrTem["ciius"][2] != '') {
            $jsonsal["ciiu_secundario"] = $arrTem["ciius"][2];
            $jsonsal["descripcion_ciiu_secundario"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $arrTem["ciius"][2] . "'", "descripcion");
        }
        if ($arrTem["ciius"][3] != '') {
            $jsonsal["ciiu3"] = $arrTem["ciius"][3];
            $jsonsal["descripcion_ciiu3"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $arrTem["ciius"][3] . "'", "descripcion");
        }
        if ($arrTem["ciius"][4] != '') {
            $jsonsal["ciiu4"] = $arrTem["ciius"][4];
            $jsonsal["descripcion_ciiu4"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $arrTem["ciius"][4] . "'", "descripcion");
        }
        $jsonsal["descripcion_actividad"] = $arrTem["desactiv"];

        // Datos de ubicación comercial y de notificación
        if ($tipo > 'capa1') {
            $jsonsal["datos_ubicacion_comercial"] = array();
            $jsonsal["datos_ubicacion_comercial"]["direccion"] = $arrTem["dircom"];
            $jsonsal["datos_ubicacion_comercial"]["municipio"] = $arrTem["muncom"];
            $mun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $arrTem["muncom"] . "'");
            if ($mun && !empty($mun)) {
                $jsonsal["datos_ubicacion_comercial"]["descripcion_municipio"] = $mun["ciudad"] . ' (' . $mun["departamento"] . ")";
            }
            $jsonsal["datos_ubicacion_comercial"]["telefono1"] = $arrTem["telcom1"];
            $jsonsal["datos_ubicacion_comercial"]["telefono2"] = $arrTem["telcom2"];
            $jsonsal["datos_ubicacion_comercial"]["telefono3"] = $arrTem["celcom"];
            $jsonsal["datos_ubicacion_comercial"]["correo_electronico"] = $arrTem["emailcom"];

            if ($arrTem["organizacion"] == '01' ||
                    ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1') ||
                    ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '2')) {
                $jsonsal["datos_ubicacion_notificacion"] = array();
                $jsonsal["datos_ubicacion_notificacion"]["direccion"] = $arrTem["dirnot"];
                $jsonsal["datos_ubicacion_notificacion"]["municipio"] = $arrTem["munnot"];
                $mun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $arrTem["munnot"] . "'");
                if ($mun && !empty($mun)) {
                    $jsonsal["datos_ubicacion_notificacion"]["descripcion_municipio"] = $mun["ciudad"] . ' (' . $mun["departamento"] . ")";
                }

                $jsonsal["datos_ubicacion_notificacion"]["telefono1"] = $arrTem["telnot"];
                $jsonsal["datos_ubicacion_notificacion"]["telefono2"] = $arrTem["telnot2"];
                $jsonsal["datos_ubicacion_notificacion"]["telefono3"] = $arrTem["celnot"];
                $jsonsal["datos_ubicacion_notificacion"]["correo_electronico"] = $arrTem["emailnot"];
            }
        }

        // Establecimientos, sucursales y agencias
        if ($tipo > 'capa1') {
            if (!empty($arrTem["establecimientos"])) {
                $jsonsal["establecimientos_sucursales_agencias"] = array();
                foreach ($arrTem["establecimientos"] as $e) {
                    if ($e["estadodatosestablecimiento"] == 'MA' || $e["estadodatosestablecimiento"] == 'MI') {
                        $arr1 = array();
                        $arr1["nombre_establecimiento"] = $e["nombreestablecimiento"];
                        $arr1["matricula_establecimiento"] = $e["matriculaestablecimiento"];
                        $arr1["fecha_matricula_establecimiento"] = $e["fechamatricula"];
                        $arr1["ultimo_ano_renovado_establecimiento"] = $e["ultanoren"];
                        $arr1["categoria_establecimiento"] = 'Establecimiento de Comercio';
                        $arr1["direccion_establecimiento"] = $e["dircom"];
                        $arr1["municipio_establecimiento"] = $e["muncom"];
                        $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $e["muncom"] . "'");
                        $arr1["descripcion_municipio_establecimiento"] = $tmun["ciudad"] . ' (' . $tmun["departamento"] . ')';
                        $jsonsal["establecimientos_sucursales_agencias"][] = $arr1;
                    }
                }
            }

            //
            if (!empty($arrTem["sucursalesagencias"])) {
                if (!isset($jsonsal["establecimientos_sucursales_agencias"])) {
                    $jsonsal["establecimientos_sucursales_agencias"] = array();
                }
                foreach ($arrTem["sucursalesagencias"] as $e) {
                    if ($e["estado"] == 'MA' || $e["estado"] == 'MI') {
                        $arr1 = array();
                        $arr1["nombre_establecimiento"] = $e["nombresucage"];
                        $arr1["matricula_establecimiento"] = $e["matriculasucage"];
                        $arr1["fecha_matricula_establecimiento"] = $e["fechamatricula"];
                        $arr1["ultimo_ano_renovado_establecimiento"] = $e["ultanoren"];
                        $catx = '';
                        if ($e["categoria"] == '2') {
                            $catx = 'Sucursal';
                        }
                        if ($e["categoria"] == '3') {
                            $catx = 'Agencia';
                        }
                        $arr1["categoria_establecimiento"] = $catx;
                        $arr1["direccion_establecimiento"] = $e["dircom"];
                        $arr1["municipio_establecimiento"] = $e["muncom"];
                        $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $e["muncom"] . "'");
                        $arr1["descripcion_municipio_establecimiento"] = $tmun["ciudad"] . ' (' . $tmun["departamento"] . ')';
                        $jsonsal["establecimientos_sucursales_agencias"][] = $arr1;
                    }
                }
            }
        }

        // Datos de constitución y registro en cámara
        if ($tipo > 'capa2') {
            $jsonsal["datos_constitucion"] = array();
            $jsonsal["datos_constitucion"]["fecha_constitucion"] = '';
            $jsonsal["datos_constitucion"]["tipo_documento_constitucion"] = '';
            $jsonsal["datos_constitucion"]["numero_documento_constitucion"] = '';
            $jsonsal["datos_constitucion"]["origen_documento_constitucion"] = '';
            $jsonsal["datos_constitucion"]["fecha_documento_constitucion"] = '';
            $jsonsal["datos_constitucion"]["municipio_documento_constitucion"] = '';
            $jsonsal["datos_constitucion"]["libro_inscripcion_constitucion"] = '';
            $jsonsal["datos_constitucion"]["numero_inscripcion_constitucion"] = '';
            if ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1') {
                foreach ($arrTem["inscripciones"] as $i) {
                    if ($i["grupoacto"] == '005' || $i["grupoacto"] == '025') {
                        if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                            $i["crev"] = '0';
                        }
                        if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                            $jsonsal["datos_constitucion"]["fecha_constitucion"] = $i["freg"];
                            $jsonsal["datos_constitucion"]["tipo_documento_constitucion"] = $i["tdoc"];
                            $jsonsal["datos_constitucion"]["descripcion_tipo_documento_constitucion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $i["tdoc"] . "'", "descripcion");
                            $jsonsal["datos_constitucion"]["numero_documento_constitucion"] = trim((string) $i["ndoc"]);
                            $jsonsal["datos_constitucion"]["origen_documento_constitucion"] = trim((string) $i["txoridoc"]);
                            $jsonsal["datos_constitucion"]["fecha_documento_constitucion"] = $i["fdoc"];
                            $jsonsal["datos_constitucion"]["municipio_documento_constitucion"] = $i["idmunidoc"];
                            $jsonsal["datos_constitucion"]["libro_inscripcion_constitucion"] = $i["lib"];
                            $jsonsal["datos_constitucion"]["numero_inscripcion_constitucion"] = $i["nreg"];
                        }
                    }
                }
            }
        }

        // Capital
        if ($tipo > 'capa2') {
            $jsonsal["capital"] = array();
            if ($arrTem["organizacion"] == '08' && $arrTem["categoria"] == '1') {
                $jsonsal["capital"]["valor_capital_sucursal_extranjera"] = $arrTem["capsuc"];
                $jsonsal["capital"]["moneda_capital_sucursal_extranjera"] = 'COP';
                if ($arrTem["monedacap"] == '002') {
                    $jsonsal["capital"]["moneda_capital_sucursal_extranjera"] = 'USD';
                }
            }
            if ($arrTem["organizacion"] == '04' || $arrTem["organizacion"] == '07' || $arrTem["organizacion"] == '16' || ($arrTem["organizacion"] == '10' && $arrTem["capaut"] != 0)) {
                if ($arrTem["categoria"] == '1') {
                    $jsonsal["capital"]["valor_capital_autorizado"] = $arrTem["capaut"];
                    $jsonsal["capital"]["valor_capital_suscrito"] = $arrTem["capsus"];
                    $jsonsal["capital"]["valor_capital_pagado"] = $arrTem["cappag"];
                    $jsonsal["capital"]["cuotas_capital_autorizado"] = $arrTem["cuoaut"];
                    $jsonsal["capital"]["cuotas_capital_suscrito"] = $arrTem["cuosus"];
                    $jsonsal["capital"]["cuotas_capital_pagado"] = $arrTem["cuopag"];
                    if ($arrTem["nomaut"] == 0) {
                        if ($arrTem["cuoaut"] != 0) {
                            $jsonsal["capital"]["valor_nominal_cuotas_capital_autorizado"] = number_format($arrTem["capaut"] / $arrTem["cuoaut"], 2, ".", "");
                        }
                    } else {
                        $jsonsal["capital"]["valor_nominal_cuotas_capital_autorizado"] = $arrTem["nomaut"];
                    }
                    if ($arrTem["nomsus"] == 0) {
                        if ($arrTem["cuosus"] != 0) {
                            $jsonsal["capital"]["valor_nominal_cuotas_capital_suscrito"] = number_format($arrTem["capsus"] / $arrTem["cuosus"], 2, ".", "");
                        }
                    } else {
                        $jsonsal["capital"]["valor_nominal_cuotas_capital_suscrito"] = $arrTem["nomsus"];
                    }
                    if ($arrTem["nompag"] == 0) {
                        if ($arrTem["cuopag"] != 0) {
                            $jsonsal["capital"]["valor_nominal_cuotas_capital_pagado"] = number_format($arrTem["cappag"] / $arrTem["cuopag"], 2, ".", "");
                        }
                    } else {
                        $jsonsal["capital"]["valor_nominal_cuotas_capital_pagado"] = $arrTem["nompag"];
                    }
                }
            }
            if ($arrTem["organizacion"] == '06' && $arrTem["capsoc"] != 0) {
                if ($arrTem["categoria"] == '1') {
                    $jsonsal["capital"]["valor_capital_social"] = $arrTem["capsoc"];
                    $jsonsal["capital"]["cuotas_capital_social"] = $arrTem["cuosoc"];
                    $jsonsal["capital"]["valor_nominal_cuotas_capital_social"] = $arrTem["nomsoc"];
                    if ($arrTem["nomsoc"] == 0) {
                        if ($arrTem["cuosoc"] != 0) {
                            $jsonsal["capital"]["valor_nominal_cuotas_capital_social"] = number_format($arrTem["capsoc"] / $arrTem["cuosoc"], 2, ".", "");
                        }
                    }
                }
            }
            if ($arrTem["organizacion"] == '03' || ($arrTem["organizacion"] == '10' && $arrTem["capsoc"] != 0) || ($arrTem["organizacion"] == '17' && $arrTem["capsoc"] != 0)) {
                if ($arrTem["categoria"] == '1') {
                    $jsonsal["capital"]["valor_capital_social"] = $arrTem["capsoc"];
                    $jsonsal["capital"]["cuotas_capital_social"] = $arrTem["cuosoc"];
                    $jsonsal["capital"]["valor_nominal_cuotas_capital_social"] = $arrTem["nomsoc"];
                    if ($arrTem["nomsoc"] == 0) {
                        if ($arrTem["cuosoc"] != 0) {
                            $jsonsal["capital"]["valor_nominal_cuotas_capital_social"] = number_format($arrTem["capsoc"] / $arrTem["cuosoc"], 2, ".", "");
                        }
                    }
                }
            }
            if ($arrTem["organizacion"] == '11' && $arrTem["capsoc"] != 0) {
                if ($arrTem["categoria"] == '1') {
                    $jsonsal["capital"]["valor_capital_social"] = $arrTem["capsoc"];
                    $jsonsal["capital"]["cuotas_capital_social"] = $arrTem["cuosoc"];
                    $jsonsal["capital"]["valor_nominal_cuotas_capital_social"] = $arrTem["nomsoc"];
                    if ($arrTem["nomsoc"] == 0) {
                        if ($arrTem["cuosoc"] != 0) {
                            $jsonsal["capital"]["valor_nominal_cuotas_capital_social"] = number_format($arrTem["capsoc"] / $arrTem["cuosoc"], 2, ".", "");
                        }
                    }
                }
            }
        }

        // Información financiera
        /*
          if ($tipo > 'capa3') {
          if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
          $jsonsal["informacion_financiera"] = array();
          $jsonsal["informacion_financiera"]["ano_datos"] = $arrTem["anodatos"];
          $jsonsal["informacion_financiera"]["fecha_datos"] = $arrTem["fechadatos"];
          $jsonsal["informacion_financiera"]["activo_corriente"] = $arrTem["actcte"];
          $jsonsal["informacion_financiera"]["activo_no_corriente"] = $arrTem["actnocte"];
          $jsonsal["informacion_financiera"]["activo_total"] = $arrTem["acttot"];
          $jsonsal["informacion_financiera"]["pasivo_corriente"] = $arrTem["pascte"];
          $jsonsal["informacion_financiera"]["pasivo_largo_plazo"] = $arrTem["paslar"];
          $jsonsal["informacion_financiera"]["pasivo_total"] = $arrTem["pastot"];
          $jsonsal["informacion_financiera"]["patrimonio_neto"] = $arrTem["pattot"];
          $jsonsal["informacion_financiera"]["pasivo_mas_patrimonio"] = $arrTem["paspat"];
          $jsonsal["informacion_financiera"]["ingresos_actividad_ordinaria"] = $arrTem["ingope"];
          $jsonsal["informacion_financiera"]["otros_ingresos"] = $arrTem["ingnoope"];
          $jsonsal["informacion_financiera"]["gastos_operacionales"] = $arrTem["gtoven"];
          $jsonsal["informacion_financiera"]["gastos_no_operacionales"] = $arrTem["gtoadm"];
          $jsonsal["informacion_financiera"]["costo_ventas"] = $arrTem["cosven"];
          $jsonsal["informacion_financiera"]["utilidad_perdida_operacional"] = $arrTem["utiope"];
          $jsonsal["informacion_financiera"]["resultado_del_ejercicio"] = $arrTem["utinet"];
          $jsonsal["informacion_financiera"]["personal"] = $arrTem["personal"];
          }
          }
         */

        // Información financiera historica
        if ($tipo > 'capa3') {
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                $jsonsal["informacion_financiera"] = array();
                $jsonsal["informacion_financiera_historica"] = array();
                foreach ($arrTem["hf"] as $hf) {
                    $jsonsal["informacion_financiera"] = array();
                    $jsonsal["informacion_financiera"]["ano_datos"] = $hf["anodatos"];
                    $jsonsal["informacion_financiera"]["fecha_datos"] = $hf["fechadatos"];
                    $jsonsal["informacion_financiera"]["activo_corriente"] = $hf["actcte"];
                    $jsonsal["informacion_financiera"]["activo_no_corriente"] = $hf["actnocte"];
                    $jsonsal["informacion_financiera"]["activo_total"] = $hf["acttot"];
                    $jsonsal["informacion_financiera"]["pasivo_corriente"] = $hf["pascte"];
                    $jsonsal["informacion_financiera"]["pasivo_largo_plazo"] = $hf["paslar"];
                    $jsonsal["informacion_financiera"]["pasivo_total"] = $hf["pastot"];
                    $jsonsal["informacion_financiera"]["patrimonio_neto"] = $hf["pattot"];
                    $jsonsal["informacion_financiera"]["pasivo_mas_patrimonio"] = $hf["paspat"];
                    $jsonsal["informacion_financiera"]["ingresos_actividad_ordinaria"] = $hf["ingope"];
                    $jsonsal["informacion_financiera"]["otros_ingresos"] = $hf["ingnoope"];
                    $jsonsal["informacion_financiera"]["gastos_operacionales"] = $hf["gtoven"];
                    $jsonsal["informacion_financiera"]["gastos_no_operacionales"] = $hf["gtoadm"];
                    $jsonsal["informacion_financiera"]["costo_ventas"] = $hf["cosven"];
                    $jsonsal["informacion_financiera"]["utilidad_perdida_operacional"] = $hf["utiope"];
                    $jsonsal["informacion_financiera"]["resultado_del_ejercicio"] = $hf["utinet"];
                    $jsonsal["informacion_financiera"]["personal"] = $hf["personal"];
                    if ($hf["acttot"] != 0) {
                        $jsonsal["informacion_financiera"]["nivel_endeudamiento"] = number_format($hf["pastot"] / $hf["acttot"], 2, ".", "");
                    } else {
                        $jsonsal["informacion_financiera"]["nivel_endeudamiento"] = 'Indeterminado';
                    }
                    if ($hf["pascte"] != 0) {
                        $jsonsal["informacion_financiera"]["indice_liquidez"] = number_format($hf["actcte"] / $hf["pascte"], 2, ".", "");
                    } else {
                        $jsonsal["informacion_financiera"]["indice_liquidez"] = 'Indeterminado';
                    }
                    if ($hf["acttot"] != 0) {
                        $jsonsal["informacion_financiera"]["rentabilidad_activo"] = number_format($hf["utiope"] / $hf["acttot"], 2, ".", "");
                    } else {
                        $jsonsal["informacion_financiera"]["rentabilidad_activo"] = 'Indeterminado';
                    }


                    $arr1 = array();
                    $arr1["ano_datos"] = $hf["anodatos"];
                    $arr1["fecha_datos"] = $hf["fechadatos"];
                    $arr1["activo_corriente"] = $hf["actcte"];
                    $arr1["activo_no_corriente"] = $hf["actnocte"];
                    $arr1["activo_total"] = $hf["acttot"];
                    $arr1["pasivo_corriente"] = $hf["pascte"];
                    $arr1["pasivo_largo_plazo"] = $hf["paslar"];
                    $arr1["pasivo_total"] = $hf["pastot"];
                    $arr1["patrimonio_neto"] = $hf["pattot"];
                    $arr1["pasivo_mas_patrimonio"] = $hf["paspat"];
                    $arr1["ingresos_actividad_ordinaria"] = $hf["ingope"];
                    $arr1["otros_ingresos"] = $hf["ingnoope"];
                    $arr1["gastos_operacionales"] = $hf["gtoven"];
                    $arr1["gastos_no_operacionales"] = $hf["gtoadm"];
                    $arr1["costo_ventas"] = $hf["cosven"];
                    $arr1["utilidad_perdida_operacional"] = $hf["utiope"];
                    $arr1["resultado_del_ejercicio"] = $hf["utinet"];
                    $arr1["personal"] = $hf["personal"];
                    $jsonsal["informacion_financiera_historica"][] = $arr1;
                }
            }
        }

        // Representantes legales
        $jsonsal["representantes_legales"] = array();
        foreach ($arrTem["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'RLP' || $v["tipovinculo"] == 'RLS') {
                $arrl = array();
                $arrl["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                $arrl["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                $arrl["identificacion"] = $v["identificacionotros"];
                $arrl["nombre"] = $v["nombreotros"];
                $arrl["tipo"] = 'P';
                if ($v["tipovinculo"] == 'RLS') {
                    $arrl["tipo"] = 'S';
                }
                $arrl["cargo"] = $v["cargootros"];
                $arrl["libro_registro_nombramiento"] = $v["librootros"];
                $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                $jsonsal["representantes_legales"][] = $arrl;
            }
        }

        // Apoderados
        if ($tipo > 'capa2') {
            $jsonsal["apoderados"] = array();
            foreach ($arrTem["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'APOD') {
                    $arrl = array();
                    $arrl["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arrl["identificacion"] = $v["identificacionotros"];
                    $arrl["nombre"] = $v["nombreotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["apoderados"][] = $arrl;
                }
            }
        }

        // Revisores fiscales
        if ($tipo > 'capa2') {
            $jsonsal["revisores_fiscales"] = array();
            $jsonsal["socios"] = array();
            $jsonsal["situaciones_control_grupos_empresariales"] = array();
            foreach ($arrTem["vinculos"] as $v) {
                if (substr((string) $v["tipovinculo"], 0, 3) == 'RFP' || substr((string) $v["tipovinculo"], 0, 3) == 'RFS') {
                    $arrl = array();
                    $arrl["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arrl["identificacion"] = $v["identificacionotros"];
                    $arrl["nombre"] = $v["nombreotros"];
                    $arrl["tipo"] = 'P';
                    if (substr((string) $v["tipovinculo"], 0, 3) == 'RFS') {
                        $arrl["tipo"] = 'S';
                    }
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["revisores_fiscales"][] = $arrl;
                }
            }
        }

        // socios
        if ($tipo > 'capa2') {
            $jsonsal["socios"] = array();
            foreach ($arrTem["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'SOC') {
                    $arrl = array();
                    $arrl["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arrl["identificacion"] = $v["identificacionotros"];
                    $arrl["nombre"] = $v["nombreotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["socios"][] = $arrl;
                }
            }
        }

        // Situaciones de control
        if ($tipo > 'capa2') {
            $jsonsal["situaciones_control_grupos_empresariales"] = array();
            foreach ($arrTem["vinculos"] as $v) {
                if ($v["vinculootros"] == '6000') { // es controlada por
                    $arr1 = array();
                    $arr1["tipo"] = 'S.C. - CONTROLANTE';
                    $arr1["nombre_razon_social"] = $v["nombreotros"];
                    $arr1["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arr1["identificacion"] = $v["identificacionotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["situaciones_control_grupos_empresariales"][] = $arr1;
                }

                //
                if ($v["vinculootros"] == '6001') { // controla a                   
                    $arr1 = array();
                    $arr1["tipo"] = 'S.C. - CONTROLADA';
                    $arr1["nombre_razon_social"] = $v["nombreotros"];
                    $arr1["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arr1["identificacion"] = $v["identificacionotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["situaciones_control_grupos_empresariales"][] = $arr1;
                }

                //
                if ($v["vinculootros"] == '6002') { // es controlada por
                    $arr1 = array();
                    $arr1["tipo"] = 'G.E. - MATRIZ';
                    $arr1["nombre_razon_social"] = $v["nombreotros"];
                    $arr1["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arr1["identificacion"] = $v["identificacionotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["situaciones_control_grupos_empresariales"][] = $arr1;
                }

                //
                if ($v["vinculootros"] == '6003') { // controla a
                    $arr1 = array();
                    $arr1["tipo"] = 'G.E. - SUBORDINADA';
                    $arr1["nombre_razon_social"] = $v["nombreotros"];
                    $arr1["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arr1["identificacion"] = $v["identificacionotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["situaciones_control_grupos_empresariales"][] = $arr1;
                }

                //
                if ($v["vinculootros"] == '6011') { // es controlada por
                    $arr1 = array();
                    $arr1["tipo"] = 'G.E. - MATRIZ';
                    $arr1["nombre_razon_social"] = $v["nombreotros"];
                    $arr1["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arr1["identificacion"] = $v["identificacionotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["situaciones_control_grupos_empresariales"][] = $arr1;
                }

                //
                if ($v["vinculootros"] == '6012') { // es controlada por
                    $arr1 = array();
                    $arr1["tipo"] = 'G.E. - MATRIZ';
                    $arr1["nombre_razon_social"] = $v["nombreotros"];
                    $arr1["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arr1["identificacion"] = $v["identificacionotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["situaciones_control_grupos_empresariales"][] = $arr1;
                }

                //
                if ($v["vinculootros"] == '6013') { // controla a
                    $arr1 = array();
                    $arr1["tipo"] = 'G.E. - SUBORDINADA';
                    $arr1["nombre_razon_social"] = $v["nombreotros"];
                    $arr1["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arr1["identificacion"] = $v["identificacionotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["situaciones_control_grupos_empresariales"][] = $arr1;
                }

                //
                if ($v["vinculootros"] == '6014') { // controla a
                    $arr1 = array();
                    $arr1["tipo"] = 'G.E. - SUBORDINADA';
                    $arr1["nombre_razon_social"] = $v["nombreotros"];
                    $arr1["tipo_identificacion"] = $v["idtipoidentificacionotros"];
                    $arr1["descripcion_tipo_identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion");
                    $arr1["identificacion"] = $v["identificacionotros"];
                    $arrl["cargo"] = $v["cargootros"];
                    $arrl["libro_registro_nombramiento"] = $v["librootros"];
                    $arrl["numero_registro_nombramiento"] = $v["inscripcionotros"];
                    $arrl["fecha_registro_nombramiento"] = $v["fechaotros"];
                    $jsonsal["situaciones_control_grupos_empresariales"][] = $arr1;
                }
            }
        }

        // Objeto social
        if ($tipo > 'capa2') {
            // Objeto social
            if ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1') {
                if (isset($arrTem["crtsii"]["0740"]) && $arrTem["crtsii"]["0740"] != '') {
                    $s = html_entity_decode($arrTem["crtsii"]["0740"]);
                    $s = strip_tags($s);
                    $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                    $jsonsal["objeto_social"] = $s;
                } else {
                    if (isset($arrTem["crt"]["0740"]) && $arrTem["crt"]["0740"] != '') {
                        $s = html_entity_decode($arrTem["crt"]["0740"]);
                        $s = strip_tags($s);
                        $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                        $jsonsal["objeto_social"] = $s;
                    }
                }
            }
        }

        // Facultades y limitaciones
        if ($tipo > 'capa2') {
            if ($arrTem["organizacion"] == '01') {
                $jsonsal["facultades_y_limitaciones"] = 'Las personas naturales no tiene facultades ni limitaciones';
            } else {
                if (isset($arrTem["crtsii"]["1300"]) && $arrTem["crtsii"]["1300"] != '') {
                    $s = html_entity_decode($arrTem["crtsii"]["1300"]);
                    $s = strip_tags($s);
                    $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                    $jsonsal["facultades_y_limitaciones"] = $s;
                } else {
                    if (isset($arrTem["crt"]["1300"]) && $arrTem["crt"]["1300"] != '') {
                        $s = html_entity_decode($arrTem["crt"]["1300"]);
                        $s = strip_tags($s);
                        $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                        $jsonsal["facultades_y_limitaciones"] = $s;
                    }
                }
            }
        }

        // Reformas especiales
        if ($tipo > 'capa2') {
            if ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1') {
                $jsonsal["reformas_especiales"] = array();
                foreach ($arrTem["inscripciones"] as $dtx) {
                    if (trim((string) $dtx["esreformaespecial"]) == '') {
                        $dtx["esreformaespecial"] = 'N';
                    }
                    if (trim($dtx["esreformaespecial"]) == 'S') {
                        if ($dtx["crev"] != '1' && $dtx["crev"] != '8' && $dtx["crev"] != '9') {
                            $arr1 = array();
                            $arr1["fecha_inscripcion_camara"] = $dtx["freg"];
                            $arr1["libro_inscripcion"] = $dtx["lib"];
                            $arr1["numero_inscripcion"] = $dtx["nreg"];
                            $arr1["tipo_documento_inscrito"] = $dtx["tdoc"];
                            $arr1["descripcion_tipo_documento_inscrito"] = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $dtx["tdoc"] . "'", "descripcion");
                            $arr1["numero_documento_inscrito"] = $dtx["ndoc"];
                            $arr1["fecha_documento_inscrito"] = $dtx["fdoc"];
                            $arr1["origen_documento_inscrito"] = $dtx["txoridoc"];
                            $arr1["municipio_origen_documento_inscrito"] = $dtx["idmunidoc"];
                            $arr1["descripcion_municipio_origen_documento_inscrito"] = '';
                            $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $dtx["idmunidoc"] . "'");
                            if ($tmun && !empty($tmun)) {
                                $arr1["descripcion_municipio_origen_documento_inscrito"] = $tmun["ciudad"] . ' (' . $tmun["departamento"] . ')';
                            }
                            $arr1["acto_inscrito"] = $dtx["acto"];
                            $arr1["noticia_inscripcion"] = $dtx["not"];
                            $arr1["aclaratoria_inscripcion"] = $dtx["aclaratoria"];
                            $jsonsal["reformas_especiales"][] = $arr1;
                        }
                    }
                }
            }
        }

        // Poderes
        if ($tipo > 'capa2') {
            $jsonsal["poderes"] = array();
            foreach ($codcerts as $cx) {
                if ($cx["clase"] == 'CRT-PODER') {
                    if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                        $s = html_entity_decode($arrTem["crtsii"][$cx["id"]]);
                        $s = strip_tags($s);
                        $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                        $jsonsal["poderes"][] = $s;
                    } else {
                        if (isset($arrTem["crt"][$cx["id"]]) && $arrTem["crt"][$cx["id"]] != '') {
                            $s = html_entity_decode($arrTem["crt"][$cx["id"]]);
                            $s = strip_tags($s);
                            $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                            $jsonsal["poderes"][] = $s;
                        }
                    }
                }
            }
        }

        // Recursos de reposicion
        if ($tipo > 'capa2') {
            $jsonsal["recursos_reposicion"] = array();
            if (isset($arrTem["rr"]) && !empty($arrTem["rr"])) {
                foreach ($arrTem["rr"] as $r) {
                    $ins2 = false;
                    foreach ($arrTem["inscripciones"] as $ins1) {
                        if ($ins1["lib"] == $r["libroafectado"] && $ins1["nreg"] == $r["registroafectado"] && $ins1["dupli"] == $r["dupliafectado"]) {
                            $ins2 = $ins1;
                        }
                    }


                    $subsidioapelacion = '';
                    if (isset($r["subsidioapelacion"]) && $r["subsidioapelacion"] == 'S') {
                        $subsidioapelacion = ' y en subsidio de apelación';
                    }

                    $numrecurrentes = 1;
                    $recurrentes = $r["nombrerecurrente"];
                    if (trim($r["nombrerecurrente2"]) != '') {
                        $recurrentes .= ', ' . $r["nombrerecurrente2"];
                        $numrecurrentes++;
                    }
                    if (trim($r["nombrerecurrente3"]) != '') {
                        $recurrentes .= ', ' . $r["nombrerecurrente3"];
                        $numrecurrentes++;
                    }


                    $txt = "El " . \funcionesGenerales::mostrarFechaLetras1($r["fecharadicacion"]) . ", " . $recurrentes;
                    if ($numrecurrentes === 1) {
                        $txt .= " interpuso ";
                    } else {
                        $txt .= " interpusieron ";
                    }
                    if (isset($r["soloapelacion"]) && $r["soloapelacion"] == 'S') {
                        $txt .= "recurso de apelación contra el Acto Administrativo No. " . $r["registroafectado"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado"]);
                    } else {
                        $txt .= "recurso de reposición" . $subsidioapelacion . ' contra el Acto Administrativo No. ' . $r["registroafectado"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado"]);
                    }
                    if (($arrTem["organizacion"] == '12' || $arrTem["organizacion"] == '14') && $arrTem["categoria"] == '1') {
                        $txt .= " del libro " . \funcionesGenerales::retornarLibroFormato2019($r["libroafectado"]) . ", ";
                    } else {
                        $txt .= " del libro " . \funcionesGenerales::retornarLibroFormato2019($r["libroafectado"]) . " del Registro Mercantil, ";
                    }
                    if ($ins2) {
                        $txt .= "correspondiente a la inscripción de ";
                        $txt .= \funcionesGenerales::descripcionesDocumentoFormato2019($mysqli, $arrTem["organizacion"], $ins2["acto"], $ins2["tdoc"], $ins2["ndoc"], $ins2["ndocext"], $ins2["fdoc"], $ins2["idoridoc"], $ins2["txoridoc"], $ins2["idmunidoc"]) . ", ";
                    }
                    $txt .= "la cual se refiere a " . $r["noticiarecurrida"] . ". ";
                    $txt .= "Por lo anterior, la inscripción recurrida se encuentra bajo el efecto suspensivo previsto en el artículo 79 ";
                    $txt .= "del Código de Procedimiento Administrativo y de lo Contencioso Administrativo. \r\n";

                    if ($r["confirmainscripcion"] == 'C' && $r["subsidioapelacion"] == 'S') {
                        $txt .= "\r\n";
                        $txt .= "Mediante resolución No. " . $r["numeroresolucion"] . " de " . \funcionesGenerales::mostrarFechaLetras1($r["fecharesolucion"]) . " ";
                        $txt .= "esta Cámara de Comercio resolvió el anterior recurso, ";
                        $txt .= "confirmó la inscripción y concedió ante la Superintendencia de Sociedades el recurso de apelación interpuesto. ";
                        $txt .= "Por lo anterior, la inscripción recurrida continúa bajo el efecto ";
                        $txt .= "suspensivo previsto en el artículo 79 del Código de Procedimiento Administrativo y de lo Contencioso Administrativo.\r\n";
                    }
                    $jsonsal["recursos_reposicion"][] = $txt;
                }

                foreach ($codcerts as $cx) {
                    if ($cx["clase"] == 'CRT-REQ-REPOSICION') {
                        if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                            $jsonsal["recursos_reposicion"][] = $arrTem["crtsii"][$cx["id"]];
                        }
                    }
                }

                foreach ($codcerts as $cx) {
                    if ($cx["clase"] == 'CRT-REQ-APELACION') {
                        if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                            $jsonsal["recursos_reposicion"][] = $arrTem["crtsii"][$cx["id"]];
                        }
                    }
                }

                foreach ($codcerts as $cx) {
                    if ($cx["clase"] == 'CRT-REQ-QUEJA') {
                        if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                            $jsonsal["recursos_reposicion"][] = $arrTem["crtsii"][$cx["id"]];
                        }
                    }
                }
            } else {

                foreach ($codcerts as $cx) {
                    if ($cx["clase"] == 'CRT-REQ-REPOSICION') {
                        if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                            $jsonsal["recursos_reposicion"][] = $arrTem["crtsii"][$cx["id"]];
                        }
                    }
                }

                foreach ($codcerts as $cx) {
                    if ($cx["clase"] == 'CRT-REQ-APELACION') {
                        if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                            $jsonsal["recursos_reposicion"][] = $arrTem["crtsii"][$cx["id"]];
                        }
                    }
                }

                foreach ($codcerts as $cx) {
                    if ($cx["clase"] == 'CRT-REQ-QUEJA') {
                        if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                            $jsonsal["recursos_reposicion"][] = $arrTem["crtsii"][$cx["id"]];
                        }
                    }
                }
            }
        }

        // Embargos y Medidas cautelares
        if ($tipo > 'capa2') {
            $jsonsal["embargos_medidas_cautelares"] = array();
            foreach ($arrTem["inscripciones"] as $ins) {
                $inc = 'no';
                $not = '';
                if ($ins["grupoacto"] == '018' || $ins["grupoacto"] == '039' || $ins["grupoacto"] == '077' || $ins["grupoacto"] == '088') {
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if ($ins["grupoacto"] == '018') {
                            foreach ($arrTem["ctrembargos"] as $e) {
                                if ($e["libro"] == $ins["lib"] &&
                                        $e["numreg"] == $ins["nreg"] &&
                                        sprintf("%03s", $e["dupli"]) == sprintf("%03s", $ins["dupli"]) &&
                                        $e["acto"] == $ins["acto"]) {
                                    if ($e["estado"] == '1') {
                                        if ($e["esembargo"] == 'S') {
                                            $inc = 'si';
                                            $not = $e["noticia"];
                                        }
                                    }
                                }
                            }
                            if ($inc == 'no') {
                                foreach ($arrTem["ctrembargos"] as $e) {
                                    if ($e["libro"] == $ins["lib"] &&
                                            $e["numreg"] == $ins["nreg"] &&
                                            $e["acto"] == $ins["acto"]) {
                                        if ($e["estado"] == '1') {
                                            if ($e["esembargo"] == 'S') {
                                                $inc = 'si';
                                                $not = $e["noticia"];
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $inc = 'si';
                        }
                    }
                }
                if ($inc == 'si') {
                    $txMun = retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"]);
                    $txt = \funcionesGenerales::descripcionesFormato2019($mysqli, $arrTem["organizacion"], $ins["acto"], $ins["tdoc"], $ins["ndoc"], $ins["ndocext"], $ins["fdoc"], $ins["idoridoc"], $ins["txoridoc"], $ins["idmunidoc"], $ins["lib"], $ins["nreg"], $ins["freg"], $not, array(), '', '', $ins["camant"], $ins["libant"], $ins["regant"], $ins["fecant"], $ins["camant2"], $ins["libant2"], $ins["regant2"], $ins["fecant2"], $ins1["camant3"], $ins["libant3"], $ins["regant3"], $ins["fecant3"], $ins["camant4"], $ins["libant4"], $ins["regant4"], $ins["fecant4"], $ins["camant5"], $ins["libant5"], $ins["regant5"], $ins["fecant5"], $ins["acalaratoria"], $ins["tomo72"], $ins["folio72"], $ins["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $jsonsal["embargos_medidas_cautelares"][] = $txt;
                    if ($ins["grupoacto"] == '018') {
                        $certifica900 = 'si';
                    }
                }
            }
            if ($certifica900 == 'no') {
                foreach ($codcerts as $cx) {
                    if ($cx["clase"] == 'CTR-EMBARGOS') {
                        if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                            $s = html_entity_decode($arrTem["crtsii"][$cx["id"]]);
                            $s = strip_tags($s);
                            $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                            $jsonsal["embargos_medidas_cautelares"][] = $s;
                        }
                    }
                }
            }
            foreach ($codcerts as $cx) {
                if ($cx["clase"] == 'AC-EMBARGOS') {
                    if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                        $s = html_entity_decode($arrTem["crtsii"][$cx["id"]]);
                        $s = strip_tags($s);
                        $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                        $jsonsal["embargos_medidas_cautelares"][] = $s;
                    }
                }
            }
            if ($certifica900 == 'no') {
                foreach ($codcerts as $cx) {
                    if ($cx["clase"] == 'CTR-DEMANDAS') {
                        if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                            $s = html_entity_decode($arrTem["crtsii"][$cx["id"]]);
                            $s = strip_tags($s);
                            $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                            $jsonsal["embargos_medidas_cautelares"][] = $s;
                        }
                    }
                }
            }
            foreach ($codcerts as $cx) {
                if ($cx["clase"] == 'AC-DEMANDAS') {
                    if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                        $s = html_entity_decode($arrTem["crtsii"][$cx["id"]]);
                        $s = strip_tags($s);
                        $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                        $jsonsal["embargos_medidas_cautelares"][] = $s;
                    }
                }
            }
            foreach ($codcerts as $cx) {
                if ($cx["clase"] == 'CTR-MEDCAU') {
                    if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                        $s = html_entity_decode($arrTem["crtsii"][$cx["id"]]);
                        $s = strip_tags($s);
                        $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                        $jsonsal["embargos_medidas_cautelares"][] = $s;
                    }
                }
            }
            foreach ($codcerts as $cx) {
                if ($cx["clase"] == 'AC-MEDCAU') {
                    if (isset($arrTem["crtsii"][$cx["id"]]) && $arrTem["crtsii"][$cx["id"]] != '') {
                        $s = html_entity_decode($arrTem["crtsii"][$cx["id"]]);
                        $s = strip_tags($s);
                        $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                        $jsonsal["embargos_medidas_cautelares"][] = $s;
                    }
                }
            }
        }

        // Habilitaciones especiales
        if ($tipo > 'capa2') {
            $jsonsal["habilitaciones_especiales"] = array();
            if (!empty($arrTem["habilitacionesespeciales"])) {
                foreach ($arrTem["habilitacionesespeciales"] as $he) {
                    $s = html_entity_decode($he);
                    $s = strip_tags($s);
                    $s = str_replace(array(chr(13) . chr(10), chr(13), chr(10)), " ", $s);
                    $jsonsal["habilitaciones_especiales"][] = $s;
                }
            }
        }

        // Observaciones finales - Codigos de barra en proceso
        $jsonsal["observaciones_finales"] = '';
        if (!empty($arrTem["lcodigosbarras"])) {
            $jsonsal["observaciones_finales"] = 'A LA FECHA DE EXPEDICIÓN DE ESTE CERTIFICADO, EXISTEN PETICIONES EN TRÁMITE. LAS CUALES PUEDEN AFECTAR EL CONTENIDO DE LA INFORMACIÓN QUE CONSTA EN EL MISMO';
        }

        //
        return $jsonsal;
    }

}
