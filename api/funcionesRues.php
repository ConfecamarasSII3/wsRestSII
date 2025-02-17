<?php

class funcionesRues {

    /**
     * 
     * @param type $forzar
     * @return type
     */
    public static function obtenerUrlRues($forzar = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        if ($forzar == '') {
            $forzar = TIPO_AMBIENTE;
        }
        switch ($forzar) {
            case "PRODUCCION":
                $urlrues = URL_API_P;
                break;
            case "PRUEBAS":
                $urlrues = URL_API_D;
                break;
            case "QA":
                $urlrues = URL_API_QA;
                break;
        }
        return $urlrues;
    }

    /**
     * 
     * @param type $forzar
     * @return bool
     */
    public static function solicitarToken($forzar = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        if ($forzar == '') {
            $forzar = TIPO_AMBIENTE;
        }
        if ($forzar == 'PRODUCCION') {
            $urlrues = URL_API_P;
            $userrues = USER_API_P;
            $passrues = PASSWORD_API_P;
        } else {
            if ($forzar == 'PRUEBAS') {
                $urlrues = URL_API_D;
                $userrues = USER_API_D;
                $passrues = PASSWORD_API_D;
            } else {
                $urlrues = URL_API_QA;
                $userrues = USER_API_QA;
                $passrues = PASSWORD_API_QA;
            }
        }
        $nameLog = 'solicitarTokenRues_' . date("Ymd");
        $access_token = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . '/Token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=" . $userrues . "&password=" . $passrues . "&grant_type=password");
        $result = curl_exec($ch);
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200:
                $resultado = json_decode($result, true);
                if (is_array($resultado)) {
                    $access_token = $resultado['access_token'];
                }
                break;
            default:
                $msj = 'Código HTTP Token : ' . $http_code;
                \logApi::general2($nameLog, '', 'ResponseToken : ' . $msj);
                break;
        }
        curl_close($ch);
        if ($access_token == '') {
            return false;
        }
        return $access_token;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $mat
     * @param type $cb
     * @param type $rec
     * @param type $cp
     * @param type $nl
     * @param type $nr
     * @param type $dp
     * @param type $ctce
     * @return string
     */
    public static function actualizarDesdeVue($mysqli, $mat = '', $cb = '', $rec = '', $cp = '', $nl = '', $nr = '', $dp = '', $ctce = array()) {
        $res = array(
            'codigoerror' => '0000',
            'mensajeerror' => '',
            'actualizacionExpediente' => '',
            'actualizacionFinanciera' => '',
            'actualizacionTextos' => ''
        );

        //
        \funcionesRegistrales::actualizarPasosDigitacion($mysqli, $cb, $nl, $nr, $cp);
        $nameLog = 'formCTVCE2-Inscritos_' . date("Ymd");
        \logApi::general2($nameLog, $mat . '-formCTVCE2-Inscritos', var_export($ctce["expediente"], true));
        $resActualizaInscrito = \funcionesRegistrales::actualizarMregEstInscritos($mysqli, $ctce["expediente"], $cb, 'formCTVCEV2-Inscritos', $rec);
        $resActualizaFinanciera = \funcionesRegistrales::actualizarMregEstInformacionFinanciera($mysqli, $ctce["dataf"], $cb, 'formCTVCEV2-Financiera', $rec);
        $resActualizaTextos = \funcionesRegistrales::actualizarMregEstTextos($mysqli, $mat, $ctce["certificas"], $cb, 'formCTVCEV2-Textos', $rec);

        //
        if ($resActualizaInscrito) {
            $res["actualizacionExpediente"] = 'OK';
        } else {
            $res["actualizacionExpediente"] = 'ERROR';
        }
        if ($resActualizaFinanciera) {
            $res["actualizacionFinanciera"] = 'OK';
        } else {
            $res["actualizacionFinanciera"] = 'ERROR';
        }
        if ($resActualizaTextos) {
            $res["actualizacionTextos"] = 'OK';
        } else {
            $res["actualizacionTextos"] = 'ERROR';
        }
        return $res;
    }

    /**
     * 
     * @param type $parametros
     */
    public static function importarFormularioCTVCEAutomatico($parametros) {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        $nameLog = $parametros["log"];
        $resCTVCE = \funcionesRues::consumirApiCTVCEV2($parametros["numerorecuperacion"]);
        if ($resCTVCE["codigoerror"] == '0000') {
            $mysqli = conexionMysqliApi();
            $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $parametros["matricula"]);
            $ctce = \funcionesRues::construirExpedienteVUEV2($mysqli, $parametros["matricula"], $parametros["organizacion"], $parametros["categoria"], '', $exp, $resCTVCE, $parametros["matriculapropietario"]);
            $res = \funcionesRues::actualizarDesdeVue($mysqli, $parametros["matricula"], $parametros["codigobarras"], $parametros["recibo"], $parametros["codigopaso"], $parametros["libro"], $parametros["registro"], $parametros["dupli"], $ctce);
            if ($res["codigoerror"] != '0000') {
                \logApi::general2($nameLog, $parametros["codigobarras"], 'Error importando matrícula No. ' . $parametros["matricula"] . ' : ' . $res["mensajeerror"]);
            }
            if ($res["actualizacionExpediente"] == 'OK' && $res["actualizacionFinanciera"] == 'OK' && $res["actualizacionTextos"] == 'OK') {
                \logApi::general2($nameLog, $parametros["codigobarras"], 'Exito importando matrícula No. ' . $parametros["matricula"] . ' desde VUE/CTCE');
            } else {
                \logApi::general2($nameLog, $parametros["codigobarras"], 'Errores importando matrícula No. ' . $parametros["matricula"] . ' desde VUE/CTCE');
            }
            $mysqli->close();
        } else {
            \logApi::general2($nameLog, $parametros["codigobarras"], 'Error importando matrícula No. ' . $parametros["matricula"] . ' : ' . $resCTVCE["mensajeerror"]);
        }
    }

    /**
     * 
     * @param type $data
     * @param type $version
     * @return string
     */
    public static function actualizarMercantilRues($data = array(), $version = '1') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //
        if (!defined('RUES_PRUEBAS_ACTIVADO')) {
            define('RUES_PRUEBAS_ACTIVADO', 'S');
        }
        if (TIPO_AMBIENTE == 'PRUEBAS' && RUES_PRUEBAS_ACTIVADO != 'S') {
            $respuesta["codigoError"] = '9980';
            $respuesta["msgError"] = 'Rues en pruebas no activado';
            $respuesta["hashControl"] = '';
            $respuesta["version"] = '';
            return $respuesta;
        }

        //
        if ($version == '1') {
            if (!empty($data)) {
                $r = \funcionesRues::consumirRegMer(null, $data["matricula"], 'forzar', $data, TIPO_AMBIENTE);
            } else {
                $r = \funcionesRues::consumirRegMer();
            }
        }

        //
        if ($version == '2') {
            if (!empty($data)) {
                $r = \funcionesRues::consumirRegMerV2(null, $data["matricula"], 'forzar', $data, TIPO_AMBIENTE);
            } else {
                $r = \funcionesRues::consumirRegMerV2();
            }
        }
        return $r;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $data
     * @param type $version
     * @return string
     */
    public static function apiActualizarProponenteRues($data = array(), $version = '1') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $nameLog = 'apiActualizarProponenteRues_' . date("Ymd");

        //
        if (!defined('RUES_PRUEBAS_ACTIVADO')) {
            define('RUES_PRUEBAS_ACTIVADO', 'S');
        }
        if (TIPO_AMBIENTE == 'PRUEBAS' && RUES_PRUEBAS_ACTIVADO != 'S') {
            $respuesta["codigoError"] = '9980';
            $respuesta["msgError"] = 'Rues en pruebas no activado';
            return $respuesta;
        }

        //
        \logApi::general2($nameLog, '', 'Ingreso a sincronizar proponente al rues');
        $r = \funcionesRues::consumirRegPro($data);
        \logApi::general2($nameLog, '', 'Respuesta a sincronizar proponente al rues - ' . $r["codigoError"] . ' - ' . $r["msgError"]);
        return $r;
    }

    public static function ajustarArregloRues($arrRues) {
        if (!isset($arrRues[0])) {
            $tmp = $arrRues;
            unset($arrRues);
            $arrRues[0] = $tmp;
        }
        return $arrRues;
    }

    public static function apiConsultarNumeroIdentificacion($parametros) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        $respuesta = array();
        $respuesta["codigoError"] = "0000";
        $respuesta["msgError"] = '';
        $respuesta["datos_respuesta"] = array();

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible generar el token o no está definida la URL del servicio API/REST en RUES';
            $respuesta["renglones"] = array();
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $nameLog = 'apiConsultarNumeroIdentificacion_' . date("Ymd");

        //
        $json = array(
            "numero_interno" => $parametros["numero_interno"],
            "usuario" => $parametros["usuario"],
            "codigo_clase_identificacion" => $parametros["codigo_clase_identificacion"],
            "numero_identificacion" => sprintf("%014s", $parametros["numero_identificacion"]),
            "digito_verificacion" => $parametros["digito_verificacion"]
        );
        $jsonencode = json_encode($json);
        \logApi::general2($nameLog, '', 'Request : ' . $jsonencode);

        //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . 'api/ConsultaEntidades/consultarNumeroIdentificacion');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonencode);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response : ' . $responseRues);
        curl_close($ch);

        //
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        $evaluarJson = 'no';
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, '', 'ResponseRegMer : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }

        //
        if ($evaluarJson == 'si') {
            $resp = json_decode($responseRues, true);
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    return $respuesta;
                } else {
                    if (!isset($resp["datos_respuesta"]) || empty($resp["datos_respuesta"])) {
                        $respuesta["codigoError"] = '9999';
                        $respuesta["msgError"] = 'No se encontro la identificación en el RUES';
                        return $respuesta;
                    } else {
                        $respuesta["codigoError"] = '0000';
                        $respuesta["msgError"] = 'Matricula Actualizada en RUES';
                        $respuesta["datos_respuesta"] = $resp["datos_respuesta"];
                        return $respuesta;
                    }
                }
            } else {
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                return $respuesta;
            }
        } else {
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Respuesta del RUES incorrecta';
            return $respuesta;
        }
    }

    public static function apiCambioEstado($numerointerno, $estado, $anexo) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        $nameLog = 'apiCambioEstado_' . date("Ymd");
        $respuesta = array();
        $respuesta["codigoError"] = "0000";
        $respuesta["msgError"] = '';
        $respuesta["datos_respuesta"] = array();

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            \logApi::general2($nameLog, '', 'Error solicitando token en apiCambioEstado');
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible solicitar el token';
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $json = array(
            "numero_interno" => $numerointerno,
            "usuario" => $_SESSION["generales"]["codigousuario"],
            "estado_transaccion" => $estado,
            "anexos" => trim((string) $anexo),
            "estado" => '1',
            "firma_digital" => $_SESSION["generales"]["codigoempresa"]
        );
        $jsonencode = json_encode($json);
        \logApi::general2($nameLog, '', 'Request : ' . $jsonencode);

        //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . SOLICITUD_CAMBIO_ESTADO);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonencode);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response : ' . $responseRues);

        //
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        $evaluarJson = 'no';
                        $msj = 'La respuesta no es un json valido : ' . $responseRues;
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, '', 'ResponseRegMer : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        //
        if ($evaluarJson == 'no') {
            $respuesta["codigoError"] = "9998";
            $respuesta["msgError"] = $msj;
            return $respuesta;
        }

        $ret = json_decode($responseRues, true);
        if ($ret["respuesta"]["codigo_error"] != '0000') {
            $respuesta["codigoError"] = $ret["respuesta"]["codigo_error"];
            $respuesta["msgError"] = $ret["respuesta"]["mensaje_error"];
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }

        //
        $respuesta = $ret["respuesta"];
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';
        return $respuesta;
    }

    /**
     * 
     * @param type $numerointerno
     * @return bool
     */
    public static function apiConsultarRutaNacional($numerointerno) {
        $nameLog = 'apiConsultarRutaNacional_' . date("Ymd");
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible solicitar el token (apiConsultarRutaNacional)';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();
        $arr = array();
        $arr['numero_unico_consulta'] = $numerointerno;
        $arr['usuario'] = $_SESSION["generales"]["codigousuario"];
        $jsonencode = json_encode($arr);
        \logApi::general2($nameLog, '', 'Request : ' . $jsonencode);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . CONSULTAR_RUTA_NACIONAL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonencode);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response : ' . $responseRues);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        $msj = 'La respuesta del servicio web no es un Json - ' . $responseRues;
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json - ' . $responseRues);
                        $evaluarJson = 'no';
                    } else {
                        \logApi::general2($nameLog, '', 'ResponseRegMer : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code;
                    \logApi::general2($nameLog, '', 'ResponseRegMer : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        if ($evaluarJson == 'no') {
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = $msj;
            return $respuesta;
        }

        $respuestax = json_decode($responseRues, true);
        if ($respuestax["registros"]["codigo_error"] != '0000') {
            $respuesta["codigoError"] = $respuestax["registros"]["codigo_error"];
            $respuesta["msgError"] = $respuestax["registros"]["mensaje_error"];
            return $respuesta;
        }
        $respuesta = $respuestax["registros"];
        $respuesta["codigoError"] = "0000";
        $respuesta["msgError"] = '';
        return $respuesta;
    }

    public static function apiSolicitudCertificado($codusu, $tramite) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        $nameLog = 'apiSolicitudCertificado_' . date("Ymd");

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            \logApi::general2($nameLog, '', 'Error solicitando token en apiSolicitudCertificado');
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible solicitar el token (apiSolicitudCertificado)';
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        $camaraReceptora = $_SESSION["generales"]["codigoempresa"];
        $camaraDestino = $tramite["rues_camararesponsable"];

        //Mejora para solicitudes de certificados con estampilla
        $servicios = array();
        $i = 0;
        $codservradicar = '';
        foreach ($tramite["rues_servicios"] as $valor) {
            if ($i == 0) {
                $codservradicar = $valor["codigo_servicio"];
            }
            $nombreMatriculado = str_replace("º", "", $valor["nombre_matriculado"]);
            $nombreBase = str_replace("º", "", $valor["nombre_base"]);
            $servicios[$i]['codigo_servicio'] = $valor["codigo_servicio"];
            $servicios[$i]['descripcion_servicio'] = $valor["descripcion_servicio"];
            $servicios[$i]['orden_servicio'] = (int) $i + 1;
            $servicios[$i]['orden_servicio_asociado'] = $valor["orden_servicio_asociado"];
            $servicios[$i]['nombre_base'] = $nombreBase;
            $servicios[$i]['valor_base'] = $valor["valor_base"];
            $servicios[$i]['valor_liquidacion'] = $valor["valor_liquidacion"];
            $servicios[$i]['cantidad_servicio'] = $valor["cantidad_servicio"];
            $servicios[$i]['indicador_base'] = $valor["indicador_base"];
            $servicios[$i]['indicador_renovacion'] = $valor["indicador_renovacion"]; // S o N
            $servicios[$i]['matricula_servicio'] = $valor["matricula_servicio"];
            $servicios[$i]['nombre_matriculado'] = $nombreMatriculado;
            $servicios[$i]['ano_renovacion'] = $valor["ano_renovacion"];
            $servicios[$i]['valor_activos_sin_ajustes'] = $valor["valor_activos_sin_ajustes"];
            $i++;
        }

        switch ($_SESSION["tramite"]["idtipoidentificacioncliente"]) {
            case "1":
                $tramite["rues_claseidentificacion"] = '01';
                $tramite["rues_numeroidentificacion"] = $_SESSION["tramite"]["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "2":
                $tramite["rues_claseidentificacion"] = '02';
                $idex = \funcionesGenerales::separarDv($tramite["identificacioncliente"]);
                $tramite["rues_numeroidentificacion"] = $idex["identificacion"];
                $tramite["rues_dv"] = $idex["dv"];
                break;
            case "3":
                $tramite["rues_claseidentificacion"] = '03';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "4":
                $tramite["rues_claseidentificacion"] = '04';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "5":
                $tramite["rues_claseidentificacion"] = '05';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            default:
                break;
        }
        $pagador = trim($tramite["nombrecliente"] . ' ' . $tramite["apellidocliente"]);

        $parametros = array(
            'numero_interno' => date('Ymd') . date('His') . '000' . $camaraReceptora . $camaraDestino . $tramite["rues_servicios"][1]["codigo_servicio"], // Oblig.
            'usuario' => $codusu,
            'codigo_servicio_radicar' => $codservradicar,
            'camara_receptora' => $camaraReceptora,
            'camara_destino' => $camaraDestino,
            'matricula' => $tramite["rues_matricula"],
            'inscripcion' => $tramite["rues_proponente"],
            'clase_identificacion' => $tramite["rues_claseidentificacion"],
            'numero_identificacion' => $tramite["rues_numeroidentificacion"],
            'digito_verificacion' => $tramite["rues_dv"],
            'estado_transaccion' => '01',
            'fecha_pago' => date('Ymd'),
            'numero_factura' => $tramite["numeroliquidacion"],
            'forma_pago' => '01',
            'servicios' => $servicios,
            'empleados' => null, // Oblig.
            'indicador_beneficio' => null,
            'total_pagado' => $tramite["rues_totalpagado"],
            'nombre_registrado' => $tramite["rues_nombreregistrado"],
            'nombre_pagador' => $pagador,
            'origen_documento' => '1',
            'fecha_documento' => date('Ymd'),
            'referencia_operacion' => '',
            'estado' => '1',
            'firma_digital' => ' '
        );

        $jsonencode = json_encode($parametros);
        \logApi::general2($nameLog, '', 'Request : ' . $jsonencode);

        // ******************************************************** //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . SOLICITUD_CERTIFICADOS);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parametros, JSON_PRETTY_PRINT));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response : ' . $responseRues);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json - ' . $responseRues);
                        $msj = 'La respuesta del servicio web no es un Json - ' . $responseRues;
                        $evaluarJson = 'no';
                    } else {
                        \logApi::general2($nameLog, '', 'Response : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code;
                    \logApi::general2($nameLog, '', 'Response : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        if ($evaluarJson == 'no') {
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = $msj;
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }

        $arrSal = json_decode($responseRues, true);
        return $arrSal["respuesta"];
    }

    public static function apiSolicitudLiquidacion($parametros) {
        $nameLog = 'apiSolicitudLiquidacion_' . date("Ymd");

        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            \logApi::general2($nameLog, '', 'Error solicitando token en apiSolicitudLiquidacion');
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible solicitar el token (apiSolicitudLiquidacion)';
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        $jsonencode = json_encode($parametros);
        \logApi::general2($nameLog, '', 'Request : ' . $jsonencode);

        // ******************************************************** //
        // Consumir servicio MR01D Rest
        // ******************************************************** //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . SOLICITUD_LIQUIDACION);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parametros, JSON_PRETTY_PRINT));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response : ' . $responseRues);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json - ' . $responseRues);
                        $msj = 'La respuesta del servicio web no es un Json - ' . $responseRues;
                        $evaluarJson = 'no';
                    } else {
                        \logApi::general2($nameLog, '', 'ResponseRegMer : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code;
                    \logApi::general2($nameLog, '', 'ResponseRegMer : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        if ($evaluarJson == 'no') {
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = $msj;
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }

        return json_decode($responseRues);
    }

    public static function apiPublicarNoticiaProponentes($numinterno, $usuario, $camara, $proponente, $identificacion, $dv, $nombre, $libro, $reg_libro, $acto_rup, $txt_noticia, $est_noticia, $fec_registro, $hora_registro) {
        $nameLog = 'api_PublicarNoticiaProponentes_' . date("Ymd");

        // 
        $actohomologado = retornarRegistroMysqliApi(null, 'mreg_actosproponente', "id='" . $acto_rup . "'", "rues");
        if ($actohomologado == '') {
            $respuesta = array();
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'El acto ' . $acto_rup . ' no tiene homologacion en RUES';
            \logApi::general2($nameLog, '', 'El acto ' . $acto_rup . ' no tiene homologacion en RUES');
            return $respuesta;
        }

        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            \logApi::general2($nameLog, '', 'Error solicitando token en apiSolicitudLiquidacion');
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible solicitar el token (apiSolicitudLiquidacion)';
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $parametros = array(
            "numero_interno" => $numinterno,
            'usuario' => $usuario,
            'camara_comercio_proponente' => sprintf("%02s", $camara),
            'inscripcion_proponente' => sprintf("%012s", $proponente),
            'numero_identificacion' => sprintf("%014s", $identificacion),
            'digito_verificacion' => $dv,
            'razon_social' => $nombre,
            'codigo_libro' => $libro,
            'numero_inscripcion_libro' => sprintf("%010s", $reg_libro),
            'codigo_acto_rup' => sprintf("%02s", $actohomologado),
            'noticia' => $txt_noticia,
            'codigo_estado_noticia' => $est_noticia,
            'fecha_inscripcion_camara' => $fec_registro,
            'hora_inscripcion_camara' => sprintf("%06s", $hora_registro),
            'numero_publicacion_noticia' => '',
            'fecha_publicacion' => '',
            'hora_publicacion' => '',
            'codigo_error' => '0000',
            'mensaje_error' => '',
            'firma_digital' => ''
        );

        //
        $jsonencode = json_encode($parametros);
        \logApi::general2($nameLog, '', 'Request : ' . $jsonencode);

        // ******************************************************** //
        // Consumir servicio MR01D Rest
        // ******************************************************** //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . PUBLICACION_NOTICIA_PROPONENTES);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parametros, JSON_PRETTY_PRINT));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response : ' . $responseRues);
        if (!\funcionesGenerales::isJson($responseRues)) {
            $msj = 'La respuesta del servicio web no es un Json) - ' . $responseRues;
            \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1) - ' . $responseRues);
            $evaluarJson = 'no';
        } else {
            $evaluarJson = 'si';
        }
        curl_close($ch);

        if ($evaluarJson == 'no') {
            $respuesta = array();
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = $msj;
            \logApi::general2($nameLog, '', 'Error en respuesta : ' . $msj);
            return $respuesta;
        }


        //
        $t = json_decode($responseRues, true);
        $respuesta["codigoError"] = $t["codigo_error"];
        $respuesta["msgError"] = $t["mensaje_error"];
        $respuesta["numpub"] = $t["numero_publicacion_noticia"];
        $respuesta["fecpub"] = $t["fecha_publicacion"];
        $respuesta["horpub"] = $t["hora_publicacion"];
        return $respuesta;
    }

    public static function apiSolicitudRadicacion($codusu, $tramite) {
        $nameLog = 'apiSolicitudRadicacion_' . date("Ymd");

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            \logApi::general2($nameLog, '', 'Error solicitando token en apiSolicitudLiquidacion');
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible solicitar el token (apiSolicitudLiquidacion)';
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $camaraReceptora = $_SESSION["generales"]["codigoempresa"];
        $camaraDestino = $tramite["rues_camararesponsable"];
        //$codserviciobase = '';
        $servicios = array();
        $i = 0;

        foreach ($tramite["rues_servicios"] as $valor) {
            $servicios[$i]['codigo_servicio'] = $valor["codigo_servicio"];
            $servicios[$i]['descripcion_servicio'] = $valor["descripcion_servicio"];
            $servicios[$i]['orden_servicio'] = (int) $i + 1;
            $servicios[$i]['orden_servicio_asociado'] = $valor["orden_servicio_asociado"];
            $servicios[$i]['nombre_base'] = (str_replace(array("&", "&iamp;", "&amp;", "iamp;", "amp;"), " ", $valor["nombre_base"]));
            $servicios[$i]['valor_base'] = $valor["valor_base"];
            $servicios[$i]['valor_liquidacion'] = $valor["valor_liquidacion"];
            $servicios[$i]['cantidad_servicio'] = $valor["cantidad_servicio"];
            $servicios[$i]['indicador_base'] = $valor["indicador_base"];
            $servicios[$i]['indicador_renovacion'] = $valor["indicador_renovacion"]; // S o N
            $servicios[$i]['matricula_servicio'] = $valor["matricula_servicio"];
            $servicios[$i]['nombre_matriculado'] = (str_replace(array("&", "&iamp;", "&amp;", "iamp;", "amp;"), " ", $valor["nombre_matriculado"]));
            $servicios[$i]['ano_renovacion'] = $valor["ano_renovacion"];
            $servicios[$i]['valor_activos_sin_ajustes'] = $valor["valor_activos_sin_ajustes"];
            $i++;
        }

        //
        switch ($_SESSION["tramite"]["idtipoidentificacioncliente"]) {
            case "1":
                $tramite["rues_claseidentificacion"] = '01';
                $tramite["rues_numeroidentificacion"] = $_SESSION["tramite"]["identificacioncliente"];
                $tramite["rues_dv"] = \funcionesGenerales::calcularDv($tramite["rues_numeroidentificacion"]);
                break;
            case "2":
                $tramite["rues_claseidentificacion"] = '02';
                $idex = \funcionesGenerales::separarDv($tramite["identificacioncliente"]);
                $tramite["rues_numeroidentificacion"] = $idex["identificacion"];
                $tramite["rues_dv"] = $idex["dv"];
                break;
            case "3":
                $tramite["rues_claseidentificacion"] = '03';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "4":
                $tramite["rues_claseidentificacion"] = '04';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "5":
                $tramite["rues_claseidentificacion"] = '05';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            default:
                break;
        }

        $pagador = trim($tramite["nombrecliente"] . ' ' . $tramite["apellidocliente"]);

        $parametros = array(
            'numero_interno' => date('Ymd') . date('His') . '000' . $camaraReceptora . $camaraDestino . $tramite["rues_servicios"][1]["codigo_servicio"], // Oblig.
            'usuario' => $codusu,
            'codigo_servicio_radicar' => $tramite["rues_servicios"][1]["codigo_servicio"],
            'camara_receptora' => $camaraReceptora,
            'camara_destino' => $camaraDestino,
            'matricula' => trim($tramite["rues_matricula"]),
            'inscripcion' => trim($tramite["rues_proponente"]),
            'nombre_registrado' => (str_replace(array("&", "&iamp;", "&amp;", "iamp;", "amp;"), " ", $tramite["rues_nombreregistrado"])),
            'clase_identificacion' => trim($tramite["rues_claseidentificacion"]),
            'numero_identificacion' => trim($tramite["rues_numeroidentificacion"]),
            'digito_verificacion' => $tramite["rues_dv"],
            'estado_transaccion' => '01',
            'nombre_pagador' => (str_replace(array("&", "&iamp;", "&amp;", "iamp;", "amp;"), " ", trim($tramite["nombrecliente"] . ' ' . $tramite["apellidocliente"]))),
            'origen_documento' => $tramite["rues_origendocumento"],
            'fecha_documento' => $tramite["rues_fechadocumento"],
            'fecha_pago' => date('Ymd'),
            'numero_factura' => $tramite["numeroliquidacion"],
            'referencia_operacion' => '',
            'total_pagado' => $tramite["rues_totalpagado"],
            'forma_pago' => '01', //Revisar
            'servicios' => $servicios,
            'numero_unico_consulta' => $tramite["rues_numerounico"],
            'estado' => '1',
            'empleados' => $tramite["numeroempleados"],
            'indicador_beneficio' => $tramite["rues_indicadorbeneficio"],
            'firma_digital' => ' '
        );

        //
        $jsonencode = json_encode($parametros);
        \logApi::general2($nameLog, '', 'Request : ' . $jsonencode);

        // ******************************************************** //
        // Consumir servicio MR01D Rest
        // ******************************************************** //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . SOLICITUD_LIQUIDACION);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parametros, JSON_PRETTY_PRINT));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response : ' . $responseRues);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json - ' . $responseRues);
                        $msj = 'La respuesta del servicio web no es un Json - ' . $responseRues;
                        $evaluarJson = 'no';
                    } else {
                        \logApi::general2($nameLog, '', 'ResponseRegMer : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code;
                    \logApi::general2($nameLog, '', 'ResponseRegMer : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        if ($evaluarJson == 'no') {
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = $msj;
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }

        $arrSal = json_decode($responseRues, true);
        return $arrSal["respuesta"];
        return $arrSal;
    }

    public static function consumirMR01D($parametros) {
        /*
          require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
          if (defined('SOLICITUD_LIQUIDACION') && SOLICITUD_LIQUIDACION != '') {
          return \funcionesRues::apiSolicitudLiquidacion($parametros);
          }
         */

        //
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/wsMR01D.class.php');

        if (!defined('wsRUE_MR01D')) {
            define('wsRUE_MR01D', '');
        }
        if (wsRUE_MR01D == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB wsRUE_MR01D EN RUES NO ESTA DEFINIDO EN EL COMMONXX';
            return false;
        }

        $txtLog = '-----------------------------------------------------------------------' . chr(13) . chr(10);
        $txtLog .= '*** INICIO PARAMETROS RUES : ***' . chr(13) . chr(10);
        $txtLog .= var_export($parametros, true);
        $txtLog .= '*** FINAL PARAMETROS RUES ***' . chr(13) . chr(10);
        $txtLog .= chr(13) . chr(10);
        \logApi::general2('consumirMR01D_' . date("Ymd"), __FUNCTION__, $txtLog);
        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");

        /* @var $ins MR01D */
        $ins = MR01D::singleton(wsRUE_MR01D);
        $ret = $ins->solicitarLiquidacion($parametros);
        unset($ins);
        if ($ret["codigo_error"] != '0000') {
            if (!isset($ret["mensaje_error"])) {
                $ret["mensaje_error"] = '';
            }
            $_SESSION["generales"]["mensajeerror"] = $ret["codigo_error"] . ' - ' . $ret["mensaje_error"];
            return $ret;
        }
        return $ret;
    }

    public static function consumirMR02N($codusu, $tramite) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        if (defined('SOLICITUD_RADICACION') && SOLICITUD_RADICACION != '') {
            return \funcionesRues::apiSolicitudRadicacion($codusu, $tramite);
        }

        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/wsMR02N.class.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/EncodingNew.php');

        //

        if (wsRUE_MR02N == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB MR02N EN RUES NO ESTA DEFINIDO EN EL COMMONXX';
            return false;
        }

        //
        $camaraReceptora = $_SESSION["generales"]["codigoempresa"];
        $camaraDestino = $tramite["rues_camararesponsable"];
        //$codserviciobase = '';
        $servicios = array();
        $i = 0;

        foreach ($tramite["rues_servicios"] as $valor) {
            $servicios[$i]['codigo_servicio'] = $valor["codigo_servicio"];
            $servicios[$i]['descripcion_servicio'] = $valor["descripcion_servicio"];
            $servicios[$i]['orden_servicio'] = (int) $i + 1;
            $servicios[$i]['orden_servicio_asociado'] = $valor["orden_servicio_asociado"];
            $servicios[$i]['nombre_base'] = (str_replace(array("&", "&iamp;", "&amp;", "iamp;", "amp;"), " ", $valor["nombre_base"]));
            $servicios[$i]['valor_base'] = $valor["valor_base"];
            $servicios[$i]['valor_liquidacion'] = $valor["valor_liquidacion"];
            $servicios[$i]['cantidad_servicio'] = $valor["cantidad_servicio"];
            $servicios[$i]['indicador_base'] = $valor["indicador_base"];
            $servicios[$i]['indicador_renovacion'] = $valor["indicador_renovacion"]; // S o N
            $servicios[$i]['matricula_servicio'] = $valor["matricula_servicio"];
            $servicios[$i]['nombre_matriculado'] = (str_replace(array("&", "&iamp;", "&amp;", "iamp;", "amp;"), " ", $valor["nombre_matriculado"]));
            $servicios[$i]['ano_renovacion'] = $valor["ano_renovacion"];
            $servicios[$i]['valor_activos_sin_ajustes'] = $valor["valor_activos_sin_ajustes"];
            $i++;
        }

        //
        switch ($_SESSION["tramite"]["idtipoidentificacioncliente"]) {
            case "1":
                $tramite["rues_claseidentificacion"] = '01';
                $tramite["rues_numeroidentificacion"] = $_SESSION["tramite"]["identificacioncliente"];
                $tramite["rues_dv"] = \funcionesGenerales::calcularDv($tramite["rues_numeroidentificacion"]);
                break;
            case "2":
                $tramite["rues_claseidentificacion"] = '02';
                $idex = \funcionesGenerales::separarDv($tramite["identificacioncliente"]);
                $tramite["rues_numeroidentificacion"] = $idex["identificacion"];
                $tramite["rues_dv"] = $idex["dv"];
                break;
            case "3":
                $tramite["rues_claseidentificacion"] = '03';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "4":
                $tramite["rues_claseidentificacion"] = '04';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "5":
                $tramite["rues_claseidentificacion"] = '05';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            default:
                break;
        }

        $pagador = trim($tramite["nombrecliente"] . ' ' . $tramite["apellidocliente"]);

        // Todos los posibles parametros a enviar
        $parametros = array(
            'numero_interno' => date('Ymd') . date('His') . '000' . $camaraReceptora . $camaraDestino . $tramite["rues_servicios"][1]["codigo_servicio"], // Oblig.
            'usuario' => $codusu,
            'codigo_servicio_radicar' => $tramite["rues_servicios"][1]["codigo_servicio"],
            'camara_receptora' => $camaraReceptora,
            'camara_destino' => $camaraDestino,
            'matricula' => trim($tramite["rues_matricula"]),
            'inscripcion' => trim($tramite["rues_proponente"]),
            'nombre_registrado' => (str_replace(array("&", "&iamp;", "&amp;", "iamp;", "amp;"), " ", $tramite["rues_nombreregistrado"])),
            'clase_identificacion' => trim($tramite["rues_claseidentificacion"]),
            'numero_identificacion' => trim($tramite["rues_numeroidentificacion"]),
            'digito_verificacion' => $tramite["rues_dv"],
            'estado_transaccion' => '01',
            'nombre_pagador' => (str_replace(array("&", "&iamp;", "&amp;", "iamp;", "amp;"), " ", trim($tramite["nombrecliente"] . ' ' . $tramite["apellidocliente"]))),
            'origen_documento' => $tramite["rues_origendocumento"],
            'fecha_documento' => $tramite["rues_fechadocumento"],
            'fecha_pago' => date('Ymd'),
            'numero_factura' => $tramite["numeroliquidacion"],
            'referencia_operacion' => '',
            'total_pagado' => $tramite["rues_totalpagado"],
            'forma_pago' => '01', //Revisar
            'servicios' => $servicios,
            'numero_unico_consulta' => $tramite["rues_numerounico"],
            'estado' => '1',
            'empleados' => $tramite["numeroempleados"],
            'indicador_beneficio' => $tramite["rues_indicadorbeneficio"],
            'firma_digital' => ' '
        );

        // Log temporal antes del llamado al consumo
        $txtLog = '';
        foreach ($parametros as $key => $valor) {
            if (!is_array($valor)) {
                $txtLog .= $key . ' => ' . $valor . chr(13) . chr(10);
            } else {
                foreach ($valor as $key1 => $valor1) {
                    if (!is_array($valor1)) {
                        $txtLog .= ' ... ' . $key1 . ' => ' . $valor1 . chr(13) . chr(10);
                    } else {
                        foreach ($valor1 as $key2 => $valor2) {
                            $txtLog .= ' ...... ' . $key2 . ' => ' . $valor2 . chr(13) . chr(10);
                        }
                    }
                }
            }
        }
        \logApi::general2('consumirMR02N_' . date('Ymd'), '', wsRUE_MR02N);
        \logApi::general2('consumirMR02N_' . date('Ymd'), '', 'Parametros : ' . $txtLog);
        \logApi::general2('consumirMR02N_' . date('Ymd'), '', '');

        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");
        $ins = MR02N::singleton(wsRUE_MR02N);
        $ret = $ins->solicitudRadicacion($parametros);
        unset($ins);

        // En caso de error
        if ($ret["codigo_error"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = $ret["codigo_error"] . ' - ' . $ret["mensaje_error"];
            \logApi::general2('consumirMR02N_' . date('Ymd'), __FUNCTION__, $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        //

        return $ret;
    }

    public static function consumirMR03N($numerointerno, $estado, $anexos) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        if (defined('SOLICITUD_CAMBIO_ESTADO') && SOLICITUD_CAMBIO_ESTADO != '') {
            return \funcionesRues::apiCambioEstado($numerointerno, $estado, $anexos);
        }

        //
        require_once('log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once('wsMR03N.class.php');

        if (wsRUE_MR03N == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB MR03N EN RUES NO ESTA DEFINIDO EN EL COMMONXX';
            return false;
        }

        $RUE_actualizacionEstado = array(
            'numero_interno' => '',
            'usuario' => '',
            'estado_transaccion' => '',
            'anexos' => '',
            'estado' => '',
            'fecha_respuesta' => '',
            'hora_respuesta' => '',
            'codigo_error' => '',
            'mensaje_error' => '',
            'firma_digital' => ''
        );

        // Todos los posibles parametros a enviar
        $RUE_actualizacionEstado["numero_interno"] = trim($numerointerno); //Oblig
        $RUE_actualizacionEstado["usuario"] = $_SESSION["generales"]["codigousuario"]; // Oblig.
        $RUE_actualizacionEstado["estado_transaccion"] = trim($estado); //Oblig.
        $RUE_actualizacionEstado["anexos"] = trim($anexos);
        $RUE_actualizacionEstado["estado"] = '1';
        $RUE_actualizacionEstado["firma_digital"] = $_SESSION["generales"]["codigoempresa"];

        $txtLog = '-----------------------------------------------------------------------' . chr(13) . chr(10);
        $txtLog .= '*** INICIO CAMBIO DE ESTADO : ***' . chr(13) . chr(10);
        $txtLog .= var_export($RUE_actualizacionEstado, true);
        $txtLog .= '*** FINAL CAMBIO ESTADO ***' . chr(13) . chr(10);
        $txtLog .= chr(13) . chr(10);
        \logApi::general2('consumirMR03N_' . date('Ymd'), __FUNCTION__, $txtLog);

        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");
        $ins = MR03N::singleton(wsRUE_MR03N);
        $ret = $ins->solicitudActualizacionEstado($RUE_actualizacionEstado);
        unset($ins);

        // En caso de error
        if ($ret["codigo_error"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = 'FALLO';
            \logApi::general2('consumirMR03N_' . date('Ymd'), __FUNCTION__, $_SESSION["generales"]["mensajeerror"]);
            return false;
        }
        return $ret;
    }

    public static function consumirRR04N($codusu, $tramite) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        if (defined('SOLICITUD_CERTIFICADOS') && SOLICITUD_CERTIFICADOS != '') {
            return \funcionesRues::apiSolicitudCertificado($codusu, $tramite);
        }
        require_once('log.php');
        require_once('wsRR04N.class.php');

        if (wsRUE_RR04N == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB RR04N EN RUES NO ESTA DEFINIDO EN EL COMMONXX';
            return false;
        }

        $camaraReceptora = $_SESSION["generales"]["codigoempresa"];
        $camaraDestino = $tramite["rues_camararesponsable"];

        //Mejora para solicitudes de certificados con estampilla
        $servicios = array();
        $i = 0;
        $codservradicar = '';
        foreach ($tramite["rues_servicios"] as $valor) {
            if ($i == 0) {
                $codservradicar = $valor["codigo_servicio"];
            }
            $nombreMatriculado = str_replace("º", "", $valor["nombre_matriculado"]);
            $nombreBase = str_replace("º", "", $valor["nombre_base"]);
            $servicios[$i]['codigo_servicio'] = $valor["codigo_servicio"];
            $servicios[$i]['descripcion_servicio'] = $valor["descripcion_servicio"];
            $servicios[$i]['orden_servicio'] = (int) $i + 1;
            $servicios[$i]['orden_servicio_asociado'] = $valor["orden_servicio_asociado"];
            $servicios[$i]['nombre_base'] = $nombreBase;
            $servicios[$i]['valor_base'] = $valor["valor_base"];
            $servicios[$i]['valor_liquidacion'] = $valor["valor_liquidacion"];
            $servicios[$i]['cantidad_servicio'] = $valor["cantidad_servicio"];
            $servicios[$i]['indicador_base'] = $valor["indicador_base"];
            $servicios[$i]['indicador_renovacion'] = $valor["indicador_renovacion"]; // S o N
            $servicios[$i]['matricula_servicio'] = $valor["matricula_servicio"];
            $servicios[$i]['nombre_matriculado'] = $nombreMatriculado;
            $servicios[$i]['ano_renovacion'] = $valor["ano_renovacion"];
            $servicios[$i]['valor_activos_sin_ajustes'] = $valor["valor_activos_sin_ajustes"];
            $i++;
        }

        switch ($_SESSION["tramite"]["idtipoidentificacioncliente"]) {
            case "1":
                $tramite["rues_claseidentificacion"] = '01';
                $tramite["rues_numeroidentificacion"] = $_SESSION["tramite"]["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "2":
                $tramite["rues_claseidentificacion"] = '02';
                $idex = \funcionesGenerales::separarDv($tramite["identificacioncliente"]);
                $tramite["rues_numeroidentificacion"] = $idex["identificacion"];
                $tramite["rues_dv"] = $idex["dv"];
                break;
            case "3":
                $tramite["rues_claseidentificacion"] = '03';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "4":
                $tramite["rues_claseidentificacion"] = '04';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            case "5":
                $tramite["rues_claseidentificacion"] = '05';
                $tramite["rues_numeroidentificacion"] = $tramite["identificacioncliente"];
                $tramite["rues_dv"] = '';
                break;
            default:
                break;
        }
        $pagador = trim($tramite["nombrecliente"] . ' ' . $tramite["apellidocliente"]);

        // Todos los posibles parametros a enviar
        $parametros = array(
            'numero_interno' => date('Ymd') . date('His') . '000' . $camaraReceptora . $camaraDestino . $tramite["rues_servicios"][1]["codigo_servicio"], // Oblig.
            'usuario' => $codusu,
            'codigo_servicio_radicar' => $codservradicar,
            'camara_receptora' => $camaraReceptora,
            'camara_destino' => $camaraDestino,
            'matricula' => $tramite["rues_matricula"],
            'inscripcion' => $tramite["rues_proponente"],
            'clase_identificacion' => $tramite["rues_claseidentificacion"],
            'numero_identificacion' => $tramite["rues_numeroidentificacion"],
            'digito_verificacion' => $tramite["rues_dv"],
            'estado_transaccion' => '01',
            'fecha_pago' => date('Ymd'),
            'numero_factura' => $tramite["numeroliquidacion"],
            'forma_pago' => '01',
            'servicios' => $servicios,
            'empleados' => null, // Oblig.
            'indicador_beneficio' => null,
            'total_pagado' => $tramite["rues_totalpagado"],
            'nombre_registrado' => $tramite["rues_nombreregistrado"],
            'nombre_pagador' => $pagador,
            'origen_documento' => '1',
            'fecha_documento' => date('Ymd'),
            'referencia_operacion' => '',
            'estado' => '1',
            'firma_digital' => ' '
        );

        // Log temporal antes del llamado al consumo
        $txtLog = '';
        foreach ($parametros as $key => $valor) {
            if (!is_array($valor)) {
                $txtLog .= $key . ' => ' . $valor . chr(13) . chr(10);
            } else {
                foreach ($valor as $key1 => $valor1) {
                    if (!is_array($valor1)) {
                        $txtLog .= ' ... ' . $key1 . ' => ' . $valor1 . chr(13) . chr(10);
                    } else {
                        foreach ($valor1 as $key2 => $valor2) {
                            $txtLog .= ' ...... ' . $key2 . ' => ' . $valor2 . chr(13) . chr(10);
                        }
                    }
                }
            }
        }
        \logApi::general2('consumirRR04N_' . date('Ymd'), '', wsRUE_RR04N);
        \logApi::general2('consumirRR04N_' . date('Ymd'), '', 'Parametros : ' . $txtLog);
        \logApi::general2('consumirRR04N_' . date('Ymd'), '', '');

        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");
        $ins = RR04N::singleton(wsRUE_RR04N);
        $ret = $ins->solicitarCertificado($parametros);
        unset($ins);

        // En caso de error
        if ($ret["codigo_error"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = $ret["codigo_error"] . ' - ' . $ret["mensaje_error"];
            return false;
        }

        //

        return $ret;
    }

    public static function consumirRR07N($numeroInterno = '', $numeroUnico = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/wsRR07N.class.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if (defined('CONSULTAR_RUTA_NACIONAL') && CONSULTAR_RUTA_NACIONAL != '') {
            return \funcionesRues::apiConsultarRutaNacional($numeroUnico);
        }

        if (wsRUE_RR07N == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB RR07N EN RUES NO ESTA DEFINIDO EN EL COMMONXX';
            return false;
        }

        $RUE_RutaNacional_BC = array(
            'numero_interno' => '',
            'usuario' => ''
        );

        $RUE_RutaNacional_BC["numero_interno"] = $numeroInterno;
        $RUE_RutaNacional_BC["usuario"] = $_SESSION["generales"]["codigousuario"];

        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");
        $ins = RR07N::singleton(wsRUE_RR07N);
        $ret = $ins->consultaRutaNacional($RUE_RutaNacional_BC);
        unset($ins);

        // En caso de error
        if ($ret["codigo_error"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = $ret["codigo_error"] . ' - ' . $ret["mensaje_error"];
            return false;
        }
        return $ret;
    }

    public static function consumirRR19N($camaraResponsable, $matricula) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once('log.php');
        require_once('wsRR19N.class.php');

        if (!defined('wsRUE_RR19N'))
            define('wsRUE_RR19N', '');
        if (wsRUE_RR19N == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB wsRUE_RR19N EN RUES NO ESTA DEFINIDO EN EL COMMONXX';
            return false;
        }

        $numeroInterno = date("Ymd") . date("His") . '000' . $_SESSION["generales"]["codigoempresa"] . $camaraResponsable . '00000000';
        $paramws['numero_interno'] = $numeroInterno;
        $paramws['usuario'] = $_SESSION["generales"]["codigousuario"];
        $paramws['codigo_camara'] = $camaraResponsable;
        $paramws['matricula'] = $matricula;

        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");

        /* @var $ins RR19N */
        $ins = RR19N::singleton(wsRUE_RR19N);
        $ret = $ins->consultarMatricula($paramws);
        unset($ins);

        if ($ret["codigo_error"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = $ret["codigo_error"] . ' - ' . $ret["mensaje_error"];
            return false;
        }

        if (isset($ret["datos_respuesta"][0])) {
            foreach ($ret["datos_respuesta"][0] as $key => $reg) {
                $arrRet[$key] = $reg;
            }
        } else {
            foreach ($ret["datos_respuesta"] as $key => $reg) {
                $arrRet[$key] = $reg;
            }
        }

        return $arrRet;
    }

    public static function consumirRR30N($data = array()) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/wsRR30N.class.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //
        if (defined('ACTUALIZAR_PROPONENTE') && ACTUALIZAR_PROPONENTE != '') {
            return \funcionesRues::apiActualizarProponenteRues($data);
        }

        ini_set('display_errors', '1');

        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';

        //
        if (!defined('RUES_PRUEBAS_ACTIVADO')) {
            define('RUES_PRUEBAS_ACTIVADO', 'S');
        }
        if (TIPO_AMBIENTE == 'PRUEBAS' && RUES_PRUEBAS_ACTIVADO != 'S') {
            $respuesta["codigoError"] = '9980';
            $respuesta["msgError"] = 'RUES DE PRUEBAS NO ACTIVADO';
            return $respuesta;
        }

        //
        if (!empty($data)) {
            $datos = $data;
        } else {
            $datos = $_SESSION['formulario'];
        }

        $param['numero_interno'] = date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"];
        $param['camara_comercio_proponente'] = $_SESSION["generales"]["codigoempresa"];
        $param['usuario'] = $_SESSION["generales"]["codigousuario"];
        $param['codigo_camara'] = $_SESSION["generales"]["codigoempresa"];
        $param['inscripcion_proponente'] = $datos["proponente"];

        $param['matricula'] = trim(ltrim($datos['matricula'], '0'));

        $param['razon_social'] = $datos["nombre"];
        $param['sigla'] = $datos["sigla"];

        if (trim($datos["nit"]) == '') {

            if (!empty($datos['idtipoidentificacion'])) {
                $param['codigo_clase_identificacion'] = $datos["idtipoidentificacion"];
            } else {
                $param['codigo_clase_identificacion'] = '06';
            }

            $param['numero_identificacion'] = $datos["identificacion"];
            $param['digito_verificacion'] = \funcionesGenerales::calcularDv($datos["identificacion"]);
        } else {
            $param['codigo_clase_identificacion'] = '02';
            $param['numero_identificacion'] = substr($datos["nit"], 0, -1);
            $param['digito_verificacion'] = substr($datos["nit"], -1, 1);
        }

        if (trim($datos["enviarint"]) == '') {
            $param['autorizacion_datos'] = 'N';
        } else {
            $param['autorizacion_datos'] = $datos["enviarint"];
        }


        if (trim($datos["muncom"]) == '') {
            $param['municipio_comercial'] = '99999';
        } else {
            $param['municipio_comercial'] = $datos["muncom"];
        }

        $param['direccion_comercial'] = $datos["dircom"];
        $param['telefono_comercial'] = $datos["telcom1"];
        $param['fax_comercial'] = $datos["faxcom"];

        //Establecer regla si estado es cancelado y tiene matricula diferente de vacio (es comerciante)
        if ((($datos["idestadoproponente"] == '01') || ($datos["idestadoproponente"] == '03')) && (trim($datos["matricula"]) != '')) {
            if (trim($datos["munnot"] != '')) {
                $param['municipio_fiscal'] = $datos["munnot"];
            } else {
                $munnotComerciante = retornarRegistroMysqliApi(null, 'mreg_est_matriculados', "trim(matricula) like '" . trim($datos["matricula"]) . "'", "munnot");
                $param['municipio_fiscal'] = empty($munnotComerciante) ? '99999' : $munnotComerciante;
            }
        } else {
            if (trim($datos["munnot"] != '')) {
                $param['municipio_fiscal'] = $datos["munnot"];
            } else {
                $param['municipio_fiscal'] = '99999';
            }
        }

        //
        $param['direccion_fiscal'] = $datos["dirnot"];
        $param['telefono_fiscal'] = $datos["telnot"];
        $param['fax_fiscal'] = $datos["faxnot"];
        $param['correo_electronico'] = $datos["emailcom"];
        $param['codigo_estado_proponente'] = $datos["idestadoproponente"];
        $param['multas'] = (count($datos["multas"]) != 0) ? 'S' : 'N';
        $param['sanciones'] = (count($datos["sanciones"]) != 0) ? 'S' : 'N';
        $param['fecha_inscripcion'] = !empty($datos["fechaultimainscripcion"]) ? $datos["fechaultimainscripcion"] : '00000000';
        $param['fecha_renovacion'] = !empty($datos["fechaultimarenovacion"]) ? $datos["fechaultimarenovacion"] : '00000000';

        if (($datos["idestadoproponente"] == '01') || ($datos["idestadoproponente"] == '03')) {
            $param['fecha_cancelacion'] = !empty($datos["fechacancelacion"]) ? $datos["fechacancelacion"] : '00000000';
        } else {
            $param['fecha_cancelacion'] = '00000000';
        }

        $param['fecha_corte_informacion_financiera'] = !empty($datos["inffin1510_fechacorte"]) ? $datos["inffin1510_fechacorte"] : '00000000';
        $param['activo_corriente'] = $datos["inffin1510_actcte"];
        $param['fijo_neto'] = $datos["inffin1510_fijnet"];
        $param['otros_activos'] = $datos["inffin1510_actotr"];
        $param['valorizaciones'] = $datos["inffin1510_actval"];
        $param['activo_total'] = $datos["inffin1510_acttot"];
        $param['pasivo_corriente'] = $datos["inffin1510_pascte"];
        $param['largo_plazo'] = $datos["inffin1510_paslar"];
        $param['pasivo_total'] = $datos["inffin1510_pastot"];
        $param['patrimonio'] = $datos["inffin1510_patnet"];
        $param['ingresos_operacionales'] = $datos["inffin1510_ingope"];
        $param['ingresos_no_operacionales'] = $datos["inffin1510_ingnoope"];
        $param['gastos_operacionales'] = $datos["inffin1510_gasope"];
        $param['gastos_no_operacionales'] = $datos["inffin1510_gasnoope"];
        $param['costo_ventas'] = $datos["inffin1510_cosven"];
        $param['gastos_intereses'] = $datos["inffin1510_gasint"];
        $param['utilidad_perdida_operacional'] = $datos["inffin1510_utiope"];
        $param['utilidad_perdida_neta'] = $datos["inffin1510_utinet"];

        if (round($datos["inffin1510_pascte"]) != 0) {
            $param['indice_liquidez'] = \funcionesGenerales::truncateFloat(($datos["inffin1510_actcte"] / $datos["inffin1510_pascte"]), 2);
        } else {
            $param['indice_liquidez'] = 0;
        }

        if (round($datos["inffin1510_acttot"]) != 0) {
            $param['indice_endeudamiento'] = \funcionesGenerales::truncateFloat(($datos["inffin1510_pastot"] / $datos["inffin1510_acttot"]), 2);
        } else {
            $param['indice_endeudamiento'] = 0;
        }

        if (round($datos["inffin1510_gasint"]) != 0) {
            $param['razon_cobertura_intereses'] = \funcionesGenerales::truncateFloat(($datos["inffin1510_utiope"] / $datos["inffin1510_gasint"]), 2);
        } else {
            $param['razon_cobertura_intereses'] = 0;
        }

        $param['rentabilidad_patrimonio'] = \funcionesGenerales::truncateFloat($datos["inffin1510_renpat"], 2);
        $param['rentabilidad_activo'] = \funcionesGenerales::truncateFloat($datos["inffin1510_renact"], 2);

        if (count($datos["clasi1510"]) > 0) {

            $arrUnspsc = array_unique($datos["clasi1510"]);

            foreach ($arrUnspsc as $key => $value) {
                $param['clasificacion_unspsc'][]['codigo_unspsc'] = $value;
            }
        } else {
            $param['clasificacion_unspsc'][]['codigo_unspsc'] = '00000000';
        }

        if (count($datos["sitcontrol"]) > 0) {

            $arrGrupos = $datos["sitcontrol"];

            foreach ($arrGrupos as $key => $value) {

                $keys = $key - 1;
                $param['grupo_empresarial_situaciones_control'][$keys]['id_grupo'] = $key;
                $param['grupo_empresarial_situaciones_control'][$keys]['nit'] = $value['identificacion'];
                $param['grupo_empresarial_situaciones_control'][$keys]['nombre'] = $value['nombre'];
                $param['grupo_empresarial_situaciones_control'][$keys]['domicilio'] = $value['domicilio'];

                switch ($value["tipo"]) {
                    case "0":
                        // MATRIZ
                        $ge_matriz = '0';
                        $sc_controlante = '';
                        break;
                    case "1":
                        // SUBORDINADA
                        $ge_matriz = '1';
                        $sc_controlante = '';
                        break;
                    case "2":
                        // CONTROLANTE
                        $ge_matriz = '';
                        $sc_controlante = '0';
                        break;
                    case "3":
                        // CONTROLADA
                        $ge_matriz = '';
                        $sc_controlante = '1';
                        break;
                    case "4":
                        // MATRIZ Y SUBORDINADA
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "5":
                        // MATRIZ Y CONTROLANTE
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "6":
                        // MATRIZ Y CONTROLADA
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "7":
                        // SUBORDINADA Y CONTROLANTE
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "8":
                        // SUBORDINADA Y CONTROLADA
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "9":
                        // CONTROLANTE Y CONTROLADA
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                }


                if (trim($ge_matriz) != '') {
                    $param['grupo_empresarial_situaciones_control'][$keys]['ge_matriz'] = $ge_matriz;
                }

                if (trim($sc_controlante) != '') {
                    $param['grupo_empresarial_situaciones_control'][$keys]['sc_controlante'] = $sc_controlante;
                }
            }
        }

        //
        $param['k_contratacion_constructor'] = 0;
        $param['k_contratacion_consultor'] = 0;
        $param['k_contratacion_proveedor'] = 0;

        $param['codigo_tamano_empresa'] = $datos["tamanoempresa"];
        $param['codigo_error'] = '0000';
        $param['mensaje_error'] = '';

        try {

            $ins = RR30N::singleton(wsRUE_RR30N);

            $resp = $ins->radicarRegistroProponente($param);

            unset($param);

            if ($resp['codigo_error'] != '0000') {
                $respuesta["codigoError"] = $resp['codigo_error'];
                $respuesta["retornoWS"] = empty($resp['mensaje_error']) ? 'NO SE OBTIENE MENSAJE DE ERROR DEL RUES' : $resp['mensaje_error'];
                $respuesta["msgError"] = 'NO FUE POSIBLE ACTUALIZAR EL PROPONENTE EN EL RUES ';
                return $respuesta;
            } else {
                $respuesta["codigoError"] = '0000';
                $respuesta["retornoWS"] = 'PROPONENTE ACTUALIZADO (RR30N)';
                $respuesta["msgError"] = 'PROPONENTE ACTUALIZADO (RR30N)';
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'ERROR DE EXCEPCION INSTANCIANDO EL SERVICIO WEB ';
            return $respuesta;
        }
    }

    public static function consumirRR41N($matricula = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/wsRR41N.class.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //
        $wsRUE_41N = wsRUE_RR41N;

        //
        if ($wsRUE_41N == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB RR41N EN RUES NO ESTA DEFINIDO EN EL COMMONXX';
            return false;
        }

        if ($matricula == '') {
            if (isset($_SESSION['formulario'])) {
                $datos = $_SESSION['formulario'];
            }
        } else {
            $mysqli = conexionMysqliApi();
            $datos = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $matricula, '', '', '', 'si', 'N', 'SII');
            $mysqli->close();
        }

        /* Llamado a consumo del web service RR41N */
        try {
            /* @var $ins RR41N */
            $ins = RR41N::singleton($wsRUE_41N);
            $resp = $ins->radicarRegistroMercantil($datos);

            unset($datos);
            unset($ins);

            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["hashControl"] = '';
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = 'Matrícula Actualizada en RUES';
                    $respuesta["hashControl"] = '';
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoError"] = '0001';
                $respuesta["msgError"] = 'Error en respuesta del RUES';
                $respuesta["hashControl"] = '';
                unset($resp);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Exception - ' . $e->getMessage();
            $respuesta["hashControl"] = '';
            return $respuesta;
        }
    }

    /**
     * 
     * @param type $dbx
     * @param type $tide
     * @param type $ide
     * @param type $useapi
     * @param type $passapi
     * @return type
     */
    public static function consultarEstablecimientosNacionales($dbx = null, $tide = '', $ide = '') {
        require_once('funcionesGenerales.php');
        require_once('log.php');
        $nameLog = 'api_consultarEstablecimientosNacionales_' . date("Ymd");

        //
        if (!defined('RUES_PRUEBAS_ACTIVADO')) {
            define('RUES_PRUEBAS_ACTIVADO', 'S');
        }
        if (TIPO_AMBIENTE == 'PRUEBAS' && RUES_PRUEBAS_ACTIVADO != 'S') {
            return array();
        }

        //
        $token = \funcionesRues::solicitarToken('PRODUCCION');
        if ($token === false) {
            \logApi::general2($nameLog, '', 'Error solicitando token en apiSolicitudLiquidacion');
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible solicitar el token (apiSolicitudLiquidacion)';
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues('PRODUCCION');

        //
        if ($tide != '2') {
            $ide1 = $ide;
            $ide2 = '';
        } else {
            $sep = \funcionesGenerales::separarDv($ide);
            $ide1 = $sep["identificacion"];
            $ide2 = $sep["dv"];
        }
        $url = $urlrues . '/api/establecimientos?usuario=admgen&nit=' . $ide1 . '&dv=' . $ide2;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);

        \logApi::general2($nameLog, $tide . '-' . $ide, '****** PETICION : ' . $url . ' *** RESPUESTA : ' . $result);
        \logApi::general2($nameLog, $tide . '-' . $ide, '');
        //
        if (!\funcionesGenerales::isJson($result)) {
            return array();
        }

        //
        $resultado = json_decode($result, true);
        if ($resultado["error"] != null) {
            return array();
        }

        //
        if ($resultado["establecimientos"] == null) {
            return array();
        }

        //
        $xcon = array();
        $salida = array();
        $ix = 0;
        foreach ($resultado["establecimientos"] as $est) {

            $ind = $est["codigo_camara"] . '-' . $est["matricula"];
            if (!isset($xcon[$ind])) {
                $xcon[$ind] = 1;

                $ix++;
                $salida[$ix] = $est;
                $salida[$ix]["ind"] = $ind;
                $salida[$ix]["nombre_municipio_comercial"] = retornarRegistroMysqliApi($dbx, "bas_municipios", "codigomunicipio='" . $est["municipio_comercial"] . "'", "ciudad");

                // Homologa organizacion juridica
                $xorg = '';
                switch ($est["codigo_organizacion_juridica"]) {
                    case "01":
                        $xorg = '01';
                        break;
                    case "02":
                        $xorg = '02';
                        break;
                    case "03":
                        $xorg = '03';
                        break;
                    case "04":
                        $xorg = '04';
                        break;
                    case "05":
                        $xorg = '05';
                        break;
                    case "06":
                        $xorg = '06';
                        break;
                    case "07":
                        $xorg = '07';
                        break;
                    case "08":
                        $xorg = '08';
                        break;
                    case "09":
                        $xorg = '09';
                        break;

                    case "10":
                        $xorg = '11';
                        break;
                    case "11":
                        $xorg = '17';
                        break;
                    case "12":
                        $xorg = '99';
                        break;
                    case "13":
                        $xorg = '15';
                        break;
                    default:
                        $xorg = '12';
                        break;
                }
                $salida[$ix]["codigo_organizacion_juridica"] = $xorg;

                // Homologo categoria
                $xcat = '';
                switch ($est["codigo_categoria_matricula"]) {
                    case "00":
                        $xcat = '';
                        break;
                    case "01":
                        $xcat = '1';
                        break;
                    case "02":
                        $xcat = '2';
                        break;
                    case "03":
                        $xcat = '3';
                        break;
                    case "04":
                        $xcat = '';
                        break;
                    default:
                        break;
                }
                $salida[$ix]["codigo_categoria_matricula"] = $xcat;

                // Ajusta fecha de renovacion
                if ($salida[$ix]["fecha_renovacion"] == '') {
                    if (isset($salida[$ix]["fecha_matricula"]) && $salida[$ix]["fecha_matricula"] != '') {
                        $salida[$ix]["fecha_renovacion"] = $salida[$ix]["fecha_matricula"];
                    }
                }
            }
        }
        $salida1 = \funcionesGenerales::ordenarMatriz($salida, "ind");

        //
        unset($resultado);

        //
        return $salida1;
    }

    public static function consultarReportes($numinterno, $usuario, $numid, $dv) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';
        $respuesta["contratos"] = array();
        $respuesta["multas"] = array();
        $respuesta["sanciones"] = array();
        if (defined('INHABILITAR_RR17N') && INHABILITAR_RR17N == 'S') {
            return $respuesta;
        }

        $RUE_ConsultaHistoriaProponente_BC = array(
            "numero_interno" => $numinterno,
            'usuario' => $usuario,
            'nit_proponente' => $numid,
            'dv_proponente' => $dv
        );

        $wsdl = URL_RUE_WS . "_DL/RR17N.asmx?WSDL";

        // Instancia el servicio web
        try {
            $client = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true, 'encoding' => 'ISO-8859-1'));
            if (is_soap_fault($client)) {
                $respuesta["codigoError"] = '0001';
                $respuesta["msgError"] = 'No fue posible crear la conexi&oacute;n con el servicio web  de consulta historia de proponentes ';
                \logApi::general2('consultarReportesRue', 'Error Soap ', 'Error consultando el proponente ' . $numid . ' - ' . $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n instanciando el servico web :  ' . $e->getMessage();
            \logApi::general2('consultarReportesRue', 'Error de excepci&oacute;n instanciando el servico web ', 'Error consultando el proponente ' . $numid . ' - ' . $e->getMessage());
            return $respuesta;
        } catch (SoapFault $fault) {
            $respuesta["codigoError"] = '0003';
            \logApi::general2('consultarReportesRue', 'Error Soap Fault ', 'Error consultando el proponente ' . $numid . ' - ' . $e->getMessage());
            return $respuesta;
        }

        try {
            $result = $client->consultaHistoriaProponente(array("consultaHistorialProponente" => $RUE_ConsultaHistoriaProponente_BC));
            if ($result === false) {
                $respuesta["codigoError"] = '0004';
                $respuesta["msgError"] = 'Error en la respuesta del servico web ' . $result;
                \logApi::general2('consultarReportesRue', 'Client Error ', 'Error consultando el proponente ' . $numid . ' - ' . $result);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            \logApi::general2('consultarReportesRue', 'Client Exception Error', 'Error consultando el proponente ' . $numid . ' - ' . $e->getMessage());
            return $respuesta;
        }

        $t = (array) $result;
        \logApi::general2('consultarReportesRue', 'Respuesta correcta', 'Proponente ' . $numid . ' - ' . print_r($t, true));
        if (ltrim($t["RUE_ConsultaHistoriaProponente_BC"]->codigo_error, "0") != '') {
            $respuesta["codigoError"] = $t["RUE_ConsultaHistoriaProponente_BC"]->codigo_error;
            $respuesta["msgError"] = $t["RUE_ConsultaHistoriaProponente_BC"]->mensaje_error;
            \logApi::general2('consultarReportesRue', 'Error en Respuesta', 'Error consultando el proponente ' . $numid . ' - ' . $respuesta["codigoError"] . ' - ' . $respuesta["msgError"]);
        } else {
            $iCnt = 0;
            $respuesta["contratos"] = array();
            $respuesta["multas"] = array();
            $respuesta["sanciones"] = array();

            if (isset($t["RUE_ConsultaHistoriaProponente_BC"]->contratos)) {
                if (is_array($t["RUE_ConsultaHistoriaProponente_BC"]->contratos)) {
                    foreach ($t["RUE_ConsultaHistoriaProponente_BC"]->contratos as $cnt) {
                        $iCnt++;
                        $respuesta["contratos"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_indicador_envio);
                        $respuesta["contratos"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_proponente);
                        $respuesta["contratos"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_proponente);
                        $respuesta["contratos"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_entidad);
                        $respuesta["contratos"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_entidad);
                        $respuesta["contratos"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->municipio_entidad);
                        $respuesta["contratos"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_contrato);
                        $respuesta["contratos"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_entidad);
                        $respuesta["contratos"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_proponente);
                        $respuesta["contratos"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->seccional_entidad);
                        $respuesta["contratos"][$iCnt]["fecha_adjudicacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_adjudicacion);
                        $respuesta["contratos"][$iCnt]["fecha_perfeccionamiento"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_perfeccionamiento);
                        $respuesta["contratos"][$iCnt]["fecha_inicio"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_inicio);
                        $respuesta["contratos"][$iCnt]["fecha_terminacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_terminacion);
                        $respuesta["contratos"][$iCnt]["fecha_liquidacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_liquidacion);
                        $respuesta["contratos"][$iCnt]["valor_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->valor_contrato);
                        $respuesta["contratos"][$iCnt]["valor_pagado"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->valor_pagado);
                        $respuesta["contratos"][$iCnt]["cod_estado_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_estado_contrato);
                        $respuesta["contratos"][$iCnt]["cod_tipo_contratista"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_tipo_contratista);
                        $respuesta["contratos"][$iCnt]["motivo_terminacion_anticipada"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->motivo_terminacion_anticipada);
                        $respuesta["contratos"][$iCnt]["fecha_terminacion_anticipada"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_terminacion_anticipada);
                        $respuesta["contratos"][$iCnt]["cod_actividad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_actividad);
                        $respuesta["contratos"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->observaciones);
                        $respuesta["contratos"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_camara);
                        $respuesta["contratos"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_libro_registro);
                        $respuesta["contratos"][$iCnt]["numero_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_inscripcion_camara);
                        $respuesta["contratos"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_inscripcion_camara);
                        $respuesta["contratos"][$iCnt]["clasificasiones1464"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->clasificasiones1464);
                        $respuesta["contratos"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_radicacion_rue);
                        $respuesta["contratos"][$iCnt]["nueva_fechareporte"] = '';
                        $respuesta["contratos"][$iCnt]["nueva_libro"] = '';
                        $respuesta["contratos"][$iCnt]["nueva_inscripcion"] = '';
                        $respuesta["contratos"][$iCnt]["nueva_fechainscripcion"] = '';
                    }
                } else {
                    $iCnt++;
                    $respuesta["contratos"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->cod_indicador_envio);
                    $respuesta["contratos"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->nit_proponente);
                    $respuesta["contratos"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->dv_proponente);
                    $respuesta["contratos"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->nit_entidad);
                    $respuesta["contratos"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->dv_entidad);
                    $respuesta["contratos"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->municipio_entidad);
                    $respuesta["contratos"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->numero_contrato);
                    $respuesta["contratos"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->nombre_entidad);
                    $respuesta["contratos"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->nombre_proponente);
                    $respuesta["contratos"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->seccional_entidad);
                    $respuesta["contratos"][$iCnt]["fecha_adjudicacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_adjudicacion);
                    $respuesta["contratos"][$iCnt]["fecha_perfeccionamiento"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_perfeccionamiento);
                    $respuesta["contratos"][$iCnt]["fecha_inicio"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_inicio);
                    $respuesta["contratos"][$iCnt]["fecha_terminacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_terminacion);
                    $respuesta["contratos"][$iCnt]["fecha_liquidacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_liquidacion);
                    $respuesta["contratos"][$iCnt]["valor_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->valor_contrato);
                    $respuesta["contratos"][$iCnt]["valor_pagado"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->valor_pagado);
                    $respuesta["contratos"][$iCnt]["cod_estado_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->cod_estado_contrato);
                    $respuesta["contratos"][$iCnt]["cod_tipo_contratista"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->cod_tipo_contratista);
                    $respuesta["contratos"][$iCnt]["motivo_terminacion_anticipada"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->motivo_terminacion_anticipada);
                    $respuesta["contratos"][$iCnt]["fecha_terminacion_anticipada"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_terminacion_anticipada);
                    $respuesta["contratos"][$iCnt]["cod_actividad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->cod_actividad);
                    $respuesta["contratos"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->observaciones);
                    $respuesta["contratos"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->codigo_camara);
                    $respuesta["contratos"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->codigo_libro_registro);
                    $respuesta["contratos"][$iCnt]["numero_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->numero_inscripcion_camara);
                    $respuesta["contratos"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_inscripcion_camara);
                    $respuesta["contratos"][$iCnt]["clasificasiones1464"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->clasificasiones1464);
                    $respuesta["contratos"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->numero_radicacion_rue);
                    $respuesta["contratos"][$iCnt]["nueva_fechareporte"] = '';
                    $respuesta["contratos"][$iCnt]["nueva_libro"] = '';
                    $respuesta["contratos"][$iCnt]["nueva_inscripcion"] = '';
                    $respuesta["contratos"][$iCnt]["nueva_fechainscripcion"] = '';
                }
            }
            $iCnt = 0;
            if (isset($t["RUE_ConsultaHistoriaProponente_BC"]->multas)) {
                if (is_array($t["RUE_ConsultaHistoriaProponente_BC"]->multas)) {
                    foreach ($t["RUE_ConsultaHistoriaProponente_BC"]->multas as $cnt) {
                        $iCnt++;
                        $respuesta["multas"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_indicador_envio);
                        $respuesta["multas"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_proponente);
                        $respuesta["multas"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_proponente);
                        $respuesta["multas"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_entidad);
                        $respuesta["multas"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_entidad);
                        $respuesta["multas"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->municipio_entidad);
                        $respuesta["multas"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_contrato);
                        $respuesta["multas"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_entidad);
                        $respuesta["multas"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_proponente);
                        $respuesta["multas"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->seccional_entidad);
                        $respuesta["multas"][$iCnt]["numero_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_administrativo);
                        $respuesta["multas"][$iCnt]["fecha_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_administrativo);
                        $respuesta["multas"][$iCnt]["fecha_ejecutoria"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_ejecutoria);
                        $respuesta["multas"][$iCnt]["valor_multa"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->valor_multa);
                        $respuesta["multas"][$iCnt]["valor_pagado_multa"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->valor_pagado_multa);
                        $respuesta["multas"][$iCnt]["cod_estado"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_estado);
                        $respuesta["multas"][$iCnt]["numero_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_suspension);
                        $respuesta["multas"][$iCnt]["fecha_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_suspension);
                        $respuesta["multas"][$iCnt]["numero_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_confirmacion);
                        $respuesta["multas"][$iCnt]["fecha_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_confirmacion);
                        $respuesta["multas"][$iCnt]["numero_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_revocacion);
                        $respuesta["multas"][$iCnt]["fecha_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_revocacion);
                        $respuesta["multas"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->observaciones);
                        $respuesta["multas"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_camara);
                        $respuesta["multas"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_libro_registro);
                        $respuesta["multas"][$iCnt]["numero_inscripcion_libro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_inscripcion_libro);
                        $respuesta["multas"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_inscripcion_camara);
                        $respuesta["multas"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_radicacion_rue);
                        $respuesta["multas"][$iCnt]["nueva_fechareporte"] = '';
                        $respuesta["multas"][$iCnt]["nueva_libro"] = '';
                        $respuesta["multas"][$iCnt]["nueva_inscripcion"] = '';
                        $respuesta["multas"][$iCnt]["nueva_fechainscripcion"] = '';
                    }
                } else {
                    $respuesta["multas"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->cod_indicador_envio);
                    $respuesta["multas"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->nit_proponente);
                    $respuesta["multas"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->dv_proponente);
                    $respuesta["multas"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->nit_entidad);
                    $respuesta["multas"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->dv_entidad);
                    $respuesta["multas"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->municipio_entidad);
                    $respuesta["multas"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_contrato);
                    $respuesta["multas"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->nombre_entidad);
                    $respuesta["multas"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->nombre_proponente);
                    $respuesta["multas"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->seccional_entidad);
                    $respuesta["multas"][$iCnt]["numero_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_acto_administrativo);
                    $respuesta["multas"][$iCnt]["fecha_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_acto_administrativo);
                    $respuesta["multas"][$iCnt]["fecha_ejecutoria"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_ejecutoria);
                    $respuesta["multas"][$iCnt]["valor_multa"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->valor_multa);
                    $respuesta["multas"][$iCnt]["valor_pagado_multa"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->valor_pagado_multa);
                    $respuesta["multas"][$iCnt]["cod_estado"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->cod_estado);
                    $respuesta["multas"][$iCnt]["numero_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_acto_suspension);
                    $respuesta["multas"][$iCnt]["fecha_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_acto_suspension);
                    $respuesta["multas"][$iCnt]["numero_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_acto_confirmacion);
                    $respuesta["multas"][$iCnt]["fecha_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_acto_confirmacion);
                    $respuesta["multas"][$iCnt]["numero_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_acto_revocacion);
                    $respuesta["multas"][$iCnt]["fecha_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_acto_revocacion);
                    $respuesta["multas"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->observaciones);
                    $respuesta["multas"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->codigo_camara);
                    $respuesta["multas"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->codigo_libro_registro);
                    $respuesta["multas"][$iCnt]["numero_inscripcion_libro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_inscripcion_libro);
                    $respuesta["multas"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_inscripcion_camara);
                    $respuesta["multas"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_radicacion_rue);
                    $respuesta["multas"][$iCnt]["nueva_fechareporte"] = '';
                    $respuesta["multas"][$iCnt]["nueva_libro"] = '';
                    $respuesta["multas"][$iCnt]["nueva_inscripcion"] = '';
                    $respuesta["multas"][$iCnt]["nueva_fechainscripcion"] = '';
                }
            }

            $iCnt = 0;
            if (isset($t["RUE_ConsultaHistoriaProponente_BC"]->sanciones)) {
                if (is_array($t["RUE_ConsultaHistoriaProponente_BC"]->sanciones)) {
                    foreach ($t["RUE_ConsultaHistoriaProponente_BC"]->sanciones as $cnt) {
                        $iCnt++;
                        $respuesta["sanciones"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_indicador_envio);
                        $respuesta["sanciones"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_proponente);
                        $respuesta["sanciones"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_proponente);
                        $respuesta["sanciones"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_entidad);
                        $respuesta["sanciones"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_entidad);
                        $respuesta["sanciones"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->municipio_entidad);
                        $respuesta["sanciones"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_contrato);
                        $respuesta["sanciones"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_entidad);
                        $respuesta["sanciones"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_proponente);
                        $respuesta["sanciones"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->seccional_entidad);
                        $respuesta["sanciones"][$iCnt]["numero_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_administrativo);
                        $respuesta["sanciones"][$iCnt]["fecha_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_administrativo);
                        $respuesta["sanciones"][$iCnt]["fecha_ejecutoria"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_ejecutoria);
                        $respuesta["sanciones"][$iCnt]["descripcion_sancion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->descripcion_sancion);
                        $respuesta["sanciones"][$iCnt]["condicion_incumplimiento"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->condicion_incumplimiento);
                        $respuesta["sanciones"][$iCnt]["cod_estado"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_estado);
                        $respuesta["sanciones"][$iCnt]["numero_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_suspension);
                        $respuesta["sanciones"][$iCnt]["fecha_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_suspension);
                        $respuesta["sanciones"][$iCnt]["numero_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_confirmacion);
                        $respuesta["sanciones"][$iCnt]["fecha_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_confirmacion);
                        $respuesta["sanciones"][$iCnt]["numero_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_revocacion);
                        $respuesta["sanciones"][$iCnt]["fecha_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_revocacion);
                        $respuesta["sanciones"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->observaciones);
                        $respuesta["sanciones"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_camara);
                        $respuesta["sanciones"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_libro_registro);
                        $respuesta["sanciones"][$iCnt]["numero_inscripcion_libro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_inscripcion_libro);
                        $respuesta["sanciones"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_inscripcion_camara);
                        $respuesta["sanciones"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_radicacion_rue);
                        $respuesta["sanciones"][$iCnt]["nueva_fechareporte"] = '';
                        $respuesta["sanciones"][$iCnt]["nueva_libro"] = '';
                        $respuesta["sanciones"][$iCnt]["nueva_inscripcion"] = '';
                        $respuesta["sanciones"][$iCnt]["nueva_fechainscripcion"] = '';
                    }
                } else {
                    $respuesta["sanciones"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->cod_indicador_envio);
                    $respuesta["sanciones"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->nit_proponente);
                    $respuesta["sanciones"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->dv_proponente);
                    $respuesta["sanciones"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->nit_entidad);
                    $respuesta["sanciones"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->dv_entidad);
                    $respuesta["sanciones"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->municipio_entidad);
                    $respuesta["sanciones"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_contrato);
                    $respuesta["sanciones"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->nombre_entidad);
                    $respuesta["sanciones"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->nombre_proponente);
                    $respuesta["sanciones"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->seccional_entidad);
                    $respuesta["sanciones"][$iCnt]["numero_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_acto_administrativo);
                    $respuesta["sanciones"][$iCnt]["fecha_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_acto_administrativo);
                    $respuesta["sanciones"][$iCnt]["fecha_ejecutoria"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_ejecutoria);
                    $respuesta["sanciones"][$iCnt]["descripcion_sancion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->descripcion_sancion);
                    $respuesta["sanciones"][$iCnt]["condicion_incumplimiento"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->condicion_incumplimiento);
                    $respuesta["sanciones"][$iCnt]["cod_estado"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->cod_estado);
                    $respuesta["sanciones"][$iCnt]["numero_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_acto_suspension);
                    $respuesta["sanciones"][$iCnt]["fecha_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_acto_suspension);
                    $respuesta["sanciones"][$iCnt]["numero_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_acto_confirmacion);
                    $respuesta["sanciones"][$iCnt]["fecha_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_acto_confirmacion);
                    $respuesta["sanciones"][$iCnt]["numero_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_acto_revocacion);
                    $respuesta["sanciones"][$iCnt]["fecha_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_acto_revocacion);
                    $respuesta["sanciones"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->observaciones);
                    $respuesta["sanciones"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->codigo_camara);
                    $respuesta["sanciones"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->codigo_libro_registro);
                    $respuesta["sanciones"][$iCnt]["numero_inscripcion_libro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_inscripcion_libro);
                    $respuesta["sanciones"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_inscripcion_camara);
                    $respuesta["sanciones"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_radicacion_rue);
                    $respuesta["sanciones"][$iCnt]["nueva_fechareporte"] = '';
                    $respuesta["sanciones"][$iCnt]["nueva_libro"] = '';
                    $respuesta["sanciones"][$iCnt]["nueva_inscripcion"] = '';
                    $respuesta["sanciones"][$iCnt]["nueva_fechainscripcion"] = '';
                }
            }
        }
        return $respuesta;
    }

    /**
     * 
     * @param type $dbx
     * @param type $tide
     * @param type $ide
     * @param type $coduser
     * @return string
     */
    public static function consumirANI2($dbx, $tide, $ide, $coduser = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        $nameLog = 'validacionANI2_' . date("Ymd");

        //
        $token = \funcionesRues::solicitarToken('PRODUCCION');
        if ($token === false) {
            \logApi::general2($nameLog, '', 'Error solicitando token en apiSolicitudLiquidacion');
            $respuesta["codigoError"] = "9999";
            $respuesta["msgError"] = 'No fue posible solicitar el token (apiSolicitudLiquidacion)';
            $respuesta["datos_respuesta"] = array();
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues('PRODUCCION');

        //    
        if ($tide != '2') {
            $ide1 = $ide;
            $ide2 = '';
        } else {
            $sep = \funcionesGenerales::separarDv($ide);
            $ide1 = $sep["identificacion"];
            $ide2 = $sep["dv"];
        }

        if ($coduser == '') {
            if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == '') {
                $coduser = 'DESCONOCIDO';
            } else {
                $coduser = $_SESSION["generales"]["codigousuario"];
            }
        }
        $json = '{
            "codigoCamara":"' . CODIGO_EMPRESA . '",
            "usuarioCamara":"' . CODIGO_EMPRESA . '-' . $coduser . '",
            "cedulas":["' . $ide1 . '"]        
            }';

        $url = $urlrues . 'api/ConsultaANI/ConsultarCedula';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        // curl_setopt($ch, CURLOPT_NOSIGNAL, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            \logApi::general2($nameLog, $tide . '-' . $ide, 'Respuesta ConsultarCedula ani curl_errno: ' . curl_errno($ch));
        }
        curl_close($ch);

        //
        \logApi::general2($nameLog, $tide . '-' . $ide, '****** PETICION : ' . $url . ' *** RESPUESTA : ' . $result);
        \logApi::general2($nameLog, '', '');

        //
        $estadoConsulta = '0';
        $error = 'no';
        $xml = '';

        //
        if (!\funcionesGenerales::isJson($result)) {
            \logApi::general2($nameLog, $tide . '-' . $ide, 'La respuesta del servicio web no es un Json (1) - ' . $result);
            $estadoConsulta = '1';
            $xml = 'No se obtuvo respuesta del servicio web de ANI2 (1)';
            $_SESSION["generales"]["mensajeerror"] = 'No se obtuvo respuesta del servicio web de ANI2 (1)';
            $error = 'si';
        }

        if ($error == 'no') {
            $resultado = json_decode($result, true);
        }

        if ($error == 'no') {
            if (!isset($resultado["return"])) {
                \logApi::general2($nameLog, $tide . '-' . $ide, 'Respuesta erronea de ANI2 (2) - ' . $result);
                $estadoConsulta = '1';
                $xml = 'Respuesta erronea de ANI2 (2) - ' . $result;
                $_SESSION["generales"]["mensajeerror"] = 'Respuesta erronea de ANI2 (2) - ' . $result;
                $error = 'si';
            }
        }

        //
        if ($error == 'no') {
            if (!isset($resultado["return"]["estadoConsulta"])) {
                \logApi::general2($nameLog, $tide . '-' . $ide, 'Respuesta erronea de ANI2 (3) - ' . $result);
                $estadoConsulta = '1';
                $xml = 'Respuesta erronea de ANI2 (3) - ' . $result;
                $_SESSION["generales"]["mensajeerror"] = 'Respuesta erronea de ANI2 (3) - ' . $result;
                $error = 'si';
            }
        }

        //
        if ($error == 'no') {
            if (!isset($resultado["return"]["estadoConsulta"]["codError"])) {
                \logApi::general2($nameLog, $tide . '-' . $ide, 'Respuesta erronea de ANI2 (4) - ' . $result);
                $estadoConsulta = '1';
                $xml = 'Respuesta erronea de ANI2 (4) - ' . $result;
                $_SESSION["generales"]["mensajeerror"] = 'Respuesta erronea de ANI2 (4) - ' . $result;
                $error = 'si';
            }
        }

        //
        if ($error == 'no') {
            if ($resultado["return"]["estadoConsulta"]["codError"] != '0') {
                \logApi::general2($nameLog, $tide . '-' . $ide, 'Error en la consulta ANI (5) - ' . $resultado["return"]["estadoConsulta"]["descripcionError"] . ' - ' . $result);
                $estadoConsulta = '1';
                if ($resultado["return"]["estadoConsulta"]["codError"] == '1') {
                    $estadoConsulta = '2';
                }
                $xml = 'Error en la repsuesta del servicio ANI2 (5) - ' . $resultado["return"]["estadoConsulta"]["descripcionError"] . ' - ' . $result;
                $_SESSION["generales"]["mensajeerror"] = 'Error en la respuesta del servicio ANI2 (5) - ' . $resultado["return"]["estadoConsulta"]["descripcionError"] . ' - ' . $result;
                $error = 'si';
            }
        }


        //
        if ($error == 'si') {
            $xml = $result;
            $salida1 = array();
            $salida1["codigoerror"] = '9999';
            $salida1["msgerror"] = 'No fue posible consultar ANI';
            $salida1["numeroControl"] = '';
            $salida1["nuip"] = '';
            $salida1["codError"] = '';
            $salida1["primerApellido"] = '';
            $salida1["particula"] = '';
            $salida1["segundoApellido"] = '';
            $salida1["primerNombre"] = '';
            $salida1["segundoNombre"] = '';
            $salida1["municipioExpedicion"] = '';
            $salida1["departamentoExpedicion"] = '';
            $salida1["fechaExpedicion"] = '';
            $salida1["estadoCedula"] = '';
            $salida1["numResolucion"] = '';
            $salida1["anoResolucion"] = '';
            $salida1["genero"] = '';
            $salida1["fechaNacimiento"] = '';
            $salida1["lugarNacimiento"] = '';
            $salida1["informante"] = '';
            $salida1["serial"] = '';
            $salida1["fechaDefuncion"] = '';
            $salida1["lugarNovedad"] = '';
            $salida1["lugarPreparacion"] = '';
            $salida1["grupoSanguineo"] = '';
            $salida1["estatura"] = '';
        }

        //
        if ($error == 'no') {
            $xml = $result;
            foreach ($resultado["return"]["datosCedulas"] as $ced) {
                $salida1["codigoerror"] = '0000';
                $salida1["msgerror"] = $_SESSION["generales"]["mensajeerror"];
                $salida1["codError"] = $ced["codError"];
                $salida1["numeroControl"] = $resultado["return"]["estadoConsulta"]["numeroControl"];
                $salida1["nuip"] = $ced["nuip"];
                if (trim((string) $ced["primerApellido"]) != '' && trim((string) $ced["primerApellido"]) != 'null') {
                    $salida1["primerApellido"] = trim((string) $ced["primerApellido"]);
                } else {
                    $salida1["primerApellido"] = '';
                }
                if (trim((string) $ced["particula"]) != '' && trim((string) $ced["particula"]) != 'null') {
                    $salida1["particula"] = trim((string) $ced["particula"]);
                } else {
                    $salida1["particula"] = '';
                }
                if (trim((string) $ced["segundoApellido"]) != '' && trim((string) $ced["segundoApellido"]) != 'null') {
                    $salida1["segundoApellido"] = trim((string) $ced["segundoApellido"]);
                } else {
                    $salida1["segundoApellido"] = '';
                }
                if (trim((string) $ced["primerNombre"]) != '' && trim((string) $ced["primerNombre"]) != 'null') {
                    $salida1["primerNombre"] = trim((string) $ced["primerNombre"]);
                } else {
                    $salida1["primerNombre"] = '';
                }
                if (trim((string) $ced["segundoNombre"]) != '' && trim((string) $ced["segundoNombre"]) != 'null') {
                    $salida1["segundoNombre"] = trim((string) $ced["segundoNombre"]);
                } else {
                    $salida1["segundoNombre"] = '';
                }
                $salida1["municipioExpedicion"] = $ced["municipioExpedicion"];
                $salida1["departamentoExpedicion"] = $ced["departamentoExpedicion"];
                if (trim((string) $ced["fechaExpedicion"]) != '' && $ced["fechaExpedicion"] != null) {
                    $salida1["fechaExpedicion"] = substr($ced["fechaExpedicion"], 6, 4) . substr($ced["fechaExpedicion"], 3, 2) . substr($ced["fechaExpedicion"], 0, 2);
                }
                if (!is_numeric($salida1["fechaExpedicion"])) {
                    $salida1["fechaExpedicion"] = null;
                }
                $salida1["estadoCedula"] = $ced["estadoCedula"];
                $salida1["numResolucion"] = $ced["numResolucion"];
                $salida1["anoResolucion"] = $ced["anoResolucion"];

                if ($ced["genero"] != null && trim($ced["genero"]) != '') {
                    $salida1["genero"] = $ced["genero"];
                }
                if ($ced["fechaNacimiento"] != null && trim($ced["fechaNacimiento"]) != '') {
                    $salida1["fechaNacimiento"] = substr($ced["fechaNacimiento"], 6, 4) . substr($ced["fechaNacimiento"], 3, 2) . substr($ced["fechaNacimiento"], 0, 2);
                }
                if (!is_numeric($salida1["fechaNacimiento"])) {
                    $salida1["fechaNacimiento"] = null;
                }
                if ($ced["lugarNacimiento"] != null && trim($ced["lugarNacimiento"]) != '') {
                    $salida1["lugarNacimiento"] = $ced["lugarNacimiento"];
                }
                if ($ced["informante"] != null && trim($ced["informante"]) != '') {
                    $salida1["informante"] = $ced["informante"];
                }
                if ($ced["serial"] != null && trim($ced["serial"]) != '') {
                    $salida1["serial"] = $ced["serial"];
                }
                if ($ced["fechaDefuncion"] != null && trim($ced["fechaDefuncion"]) != '') {
                    $salida1["fechaDefuncion"] = substr($ced["fechaDefuncion"], 6, 4) . substr($ced["fechaDefuncion"], 3, 2) . substr($ced["fechaDefuncion"], 0, 2);
                }
                if ($ced["lugarNovedad"] != null && trim($ced["lugarNovedad"]) != '') {
                    $salida1["lugarNovedad"] = $ced["lugarNovedad"];
                }
                if ($ced["lugarPreparacion"] != null && trim($ced["lugarPreparacion"]) != '') {
                    $salida1["lugarPreparacion"] = $ced["lugarPreparacion"];
                }
                if ($ced["grupoSanguineo"] != null && trim($ced["grupoSanguineo"]) != '') {
                    $salida1["grupoSanguineo"] = $ced["grupoSanguineo"];
                }
                if ($ced["estatura"] != null && trim($ced["estatura"]) != '') {
                    $salida1["estatura"] = $ced["estatura"];
                }
            }
        }

        //
        if (!isset($estadoConsulta)) {
            $estadoConsulta = '';
        }

        //
        $existeNumeroControl = 'no';
        $columnName = 'numerocontrol';
        $result = ejecutarQueryMysqliApi($dbx, "SHOW COLUMNS FROM mreg_ani_log WHERE Field = '$columnName'");
        if ($result && !empty($result)) {
            $existeNumeroControl = 'si';
        }

        //
        $cerrarMysql = 'no';
        if ($dbx === null) {
            $dbx = conexionMysqliApi();
            $cerrarMysql = 'si';
        }

        //
        $arrCampos = array(
            'fecha',
            'hora',
            'identificacion',
            'estadoconsulta',
            'xml'
        );

        if ($existeNumeroControl == 'si') {
            $arrCampos[] = 'numerocontrol';
        }
        //

        $arrValores = array(
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $tide . '-' . $ide . "'",
            "'" . $estadoConsulta . "'",
            "'" . addslashes($xml) . "'"
        );

        if ($existeNumeroControl == 'si') {
            $arrValores[] = "'" . $salida1["numeroControl"] . "'";
        }

        //
        insertarRegistrosMysqliApi($dbx, 'mreg_ani_log', $arrCampos, $arrValores);

        //
        if ($cerrarMysql == 'si') {
            $dbx->close();
        }

        //
        if ($error == 'si') {
            $salida1 = array();
            $salida1["codigoerror"] = '9998';
            $salida1["msgerror"] = $_SESSION["generales"]["mensajeerror"];
        }

        return $salida1;
    }

    /**
     * 
     * @param type $numerorecuperacion
     * @return string
     */
    public static function consumirApiCTVCE($numerorecuperacion) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'consumirApiCTVCE_' . date("Ymd");

        $response["Recuperacion"] = '';
        $response["Codigo"] = '0000';
        $response["Mensaje"] = '';
        $response["Data"] = '';

        if (!defined('URL_API_CTVCE')) {
            define('URL_API_CTVCE', '');
        }

        if (!defined('CODE_API_CTVCE')) {
            define('CODE_API_CTVCE', '');
        }

        $response = '';

        if ((defined('URL_API_CTVCE') && URL_API_CTVCE != '')) {
            $urlApiFormularios = URL_API_CTVCE . "/GetDataFormularios?CodeAPI=" . CODE_API_CTVCE . "&SII_Recuperacion=" . $numerorecuperacion;
            \logApi::general2($nameLog, $numerorecuperacion, 'Url de consumo: ' . $urlApiFormularios);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlApiFormularios);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $response = curl_exec($ch);
            curl_close($ch);
            \logApi::general2($nameLog, $numerorecuperacion, $response);
            if (!$response) {
                $response["Recuperacion"] = '';
                $response["Codigo"] = '9999';
                $response["Mensaje"] = 'No se obtuvo respuesta del servicio api del CTCE';
                $response["Data"] = '';
            } else {
                $response = json_decode($response, true);
                $response["Codigo"] = sprintf("%04s", $response["Codigo"]);
                return $response;
            }
        } else {
            $response["Recuperacion"] = '';
            $response["Codigo"] = '9999';
            $response["Mensaje"] = 'No hay URL configurada';
            $response["Data"] = '';
            return $response;
        }
    }

    /**
     * 
     * @param type $numerorecuperacion
     * @return string
     */
    public static function consumirApiCTVCEV2($numerorecuperacion) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'consumirApiCTVCEV2_' . date("Ymd");

        //
        $response["codigoerror"] = '0000';
        $response["mensajeerror"] = '';
        $response["data"] = '';

        //
        if (!defined('URL_API_CTVCE_V2')) {
            define('URL_API_CTVCE_V2', '');
        }

        if (!defined('USER_API_CTVCE_V2')) {
            define('USER_API_CTVCE_V2', '');
        }

        if (!defined('KEY_API_CTVCE_V2')) {
            define('KEY_API_CTVCE_V2', '');
        }

        //
        if ((!defined('URL_API_CTVCE_V2') || URL_API_CTVCE_V2 == '')) {
            $response["codigoerror"] = '9999';
            $response["mensajeerror"] = 'No está configurado la url de api v2 de CTCE';
            return $response;
        }

        //
        $json = array(
            "API_Usuario" => USER_API_CTVCE_V2,
            "API_Clave" => KEY_API_CTVCE_V2
        );
        $jsont = json_encode($json);

        //
        $access_token = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, URL_API_CTVCE_V2 . '/generarToken');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsont);
        $result = curl_exec($ch);
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200: //OK
                $resultado = json_decode($result, true);
                if (is_array($resultado)) {
                    if ($resultado["Codigo"] == '00') {
                        $access_token = $resultado['Token'];
                    } else {
                        $response["codigoerror"] = '9999';
                        $response["mensajeerror"] = $resultado["Mensaje"];
                    }
                } else {
                    $response["codigoerror"] = '9999';
                    $response["mensajeerror"] = 'El resultado de la solicitud del token no es un json válido';
                }
                break;
            default:
                $response["codigoerror"] = '9999';
                $response["mensajeerror"] = 'Error HHTP consumiendo el servicio de Token(' . $http_code . ')';
                break;
        }
        curl_close($ch);

        //
        if ($response["codigoerror"] != '0000') {
            return $response;
        }

        //
        $response["codigoerror"] = '0000';
        $response["mensajeerror"] = '';
        $response["data"] = array();

        //
        $json = array(
            "SII_Recuperacion" => $numerorecuperacion
        );
        $jsont = json_encode($json);

        //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, URL_API_CTVCE_V2 . '/ConsultaFormularios');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . $access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsont);
        $result = curl_exec($ch);
        \logApi::general2($nameLog, $numerorecuperacion, $result);
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200: //OK
                $resultado = json_decode($result, true);
                if (is_array($resultado)) {
                    if ($resultado["Codigo"] == '00') {
                        $response["data"] = $resultado['Data'];
                    } else {
                        $response["codigoerror"] = '9999';
                        $response["mensajeerror"] = $resultado["Mensaje"];
                    }
                } else {
                    $response["codigoerror"] = '9999';
                    $response["mensajeerror"] = 'El resultado del método ConsultarFormularios no es un json válido';
                }
                break;
            default:
                $response["codigoerror"] = '9999';
                $response["mensajeerror"] = 'Error HHTP consumiendo el servicio de Token(' . $http_code . ')';
                break;
        }
        curl_close($ch);

        //
        return $response;
    }

    public static function consumirFR01N($fecha) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/wsFR01N.class.php');

        if (!defined('wsRUE_FR01N')) {
            define('wsRUE_FR01N', '');
        }
        if (wsRUE_FR01N == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB FR01N EN RUES NO ESTA DEFINIDO EN EL COMMONXX';
            return false;
        }

        $RUE_Recaudo_BC = array(
            'numero_interno' => '',
            'usuario' => '',
            'camara_comercio' => '',
            'fecha_consulta' => '',
            'Datos_respuesta' => '',
            'fecha_respuesta' => '',
            'hora_respuesta' => '',
            'codigo_error' => '',
            'mensaje_error' => '',
            'firma_digital' => ''
        );

        // Todos los posibles parametros a enviar    
        $numeroInterno = str_pad(date('Ymd') . date('His'), 29, "0");
        $numeroInterno = date("Ymd") . date("His") . rand(0, 999) . $_SESSION["generales"]["codigoempresa"] . '00' . '00000000';
        $RUE_Recaudo_BC["numero_interno"] = $numeroInterno;
        $RUE_Recaudo_BC["usuario"] = $_SESSION["generales"]["codigousuario"]; // Oblig.
        $RUE_Recaudo_BC["camara_comercio"] = $_SESSION["generales"]["codigoempresa"];
        $RUE_Recaudo_BC["fecha_consulta"] = $fecha;

        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");
        $ins = FR01N::singleton(wsRUE_FR01N);
        $ret = $ins->consultaTransaccionesRecaudoFecha($RUE_Recaudo_BC);
        unset($ins);

        // En caso de error
        if ($ret["codigo_error"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = $ret["codigo_error"] . ' - ' . $ret["mensaje_error"];
            return false;
        }
        return $ret;
    }

    /**
     * Funcion que se conecta con muisca a través del RUES para consultar un nit
     * @param type $nit
     * @return boolean
     */
    public static function consumirNitDian($nit) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/wsMuiscaConsultar.class.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        ini_set('display_errors', '1');

        if (!defined('URL_CONSULTANITDIAN_WS')) {
            define('URL_CONSULTANITDIAN_WS', '');
        }

        if (URL_CONSULTANITDIAN_WS == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB MUISCA_CONSULTAR NO ESTA DEFINIDO EN EL COMMON';
            return false;
        }

        if (\funcionesGenerales::validarNit($nit)) {
            $sepIde = \funcionesGenerales::separarDv($nit);
            $nit = $sepIde["identificacion"];
            $dv = $sepIde["dv"];
        } else {
            $dv = \funcionesGenerales::calcularDv($nit);
        }

        $parametros = array('NIT' => $nit, 'dv' => $dv);

        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");

        $ins = MUISCAConsultar::singleton(URL_CONSULTANITDIAN_WS);
        $ret = $ins->ConsultarNIT($parametros);
        unset($ins);

        // En caso de error
        if ($ret["cod_error"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = $ret["cod_error"] . ' - ' . $ret["mensaje_error"];
            return false;
        } else {
            return $ret;
        }
    }

    /**
     * 
     * Funcion que valida si un proponente se encuentra inhabilitado por incumplimiento reiterado
     * Se tienen en cuenta los siguientes criterios para el calculo del incumplimiento
     * 
     * 1.- Solo se toman multas y sanciones cuya fecha de ejecutoria o acto administrativo sea igual o superior al 20110712
     * que es la fecha en que entr&oacute; en vigencia le ley 1474 de 2011
     * 
     * 2.- Se revisa cuantas multas y sanciones se tienen para cada año (de acuerdo con la fecha anterior)
     * 
     * 3.- Se revisa si la fecha del &uacute;ltimo reporte para el año en cuestion comparada con la fecha de hoy es inferior a 3 a&ntilde;os
     * 
     * 4.- Si se cumple que tiene 5 multas en el año, o 2 multas y 1 sancion con incumplimiento, o 2 sanciones con incumplimiento
     * se determina que ha inhabilidad por incumplimiento reiterado y se arma el texto correspondiente
     * 
     * 5.- Si no hay incumplimiento reiterado se revisa que sanciones no tienen indicador de incumplimiento y si por alguna razon con dichas
     * sanciones se constituye un incumplimiento, se arma el texto indicado que existe duda al respecto y que no puede indicarse que est&eacute;
     * inhabilitado pues la entidad estatal no ha reportado la condici&oacute;n de incumplimiento de ciertas sanciones.
     * 
     * 
     * @param 		$nit					Nit al cual se le verifica la inhabilidad
     * @result 		$array					Arreglo que contiene
     * @result 			inhabilidad			no, si, duda (Duda es cuando se encuentran sanciones sin indicador de incumplimiento)
     * @result 			ano				Año del incumplimiento
     * @result 			multas				Arreglo con la informacion de las multas del año en cuesti&oacute;n
     * @result 			sanciones			Arreglo con la informacion de las sanciones del año en cuesti&oacute;n
     * @result 			sinindicador                    Arreglo con la informacion de las sanciones que no tienen indicador de incumplimiento
     * @result 			texto	 			Areglo que contiene en lineas de 70 posiciones el texto que se debe ceertificar
     */
    public static function validarInhabilidad($nit, $proponente = '', $nombre = '', $observaciones = '') {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once('mysqli.php');
        require_once('funcionesGenerales.php');

        //
        $resultado = array();
        $resultado["inhabilidad"] = 'no';
        $resultado["tipoinhabilidad"] = '';
        $resultado["ano"] = '';
        $resultado["multas"] = array();
        $resultado["sanciones"] = array();
        $resultado["sinindicador"] = array();
        $resultado["texto"] = array();
        $resultado["textosii"] = array();

        //
        if (!defined('RUES_PRUEBAS_ACTIVADO')) {
            define('RUES_PRUEBAS_ACTIVADO', 'S');
        }
        if (TIPO_AMBIENTE == 'PRUEBAS' && RUES_PRUEBAS_ACTIVADO != 'S') {
            return $resultado;
        }


        $fechabase = '20110712'; // Fecha de la entrada en vigencia de la Ley 1474
        $sepIde = \funcionesGenerales::separarDv($nit);
        $identificacion = $sepIde["identificacion"];
        $dv = $sepIde["dv"];

        //
        $numinterno = date("Ymd") . date("His") . '001' . CODIGO_EMPRESA . '00' . '00000000';
        if (isset($_SESSION["generales"]["codigousuario"]) && $_SESSION["generales"]["codigousuario"] != '') {
            $usuario = $_SESSION["generales"]["codigousuario"];
        } else {
            $usuario = 'SII-API';
        }
        $res = \funcionesRues::consultarReportes($numinterno, $usuario, $identificacion, $dv);

        // En caso de error 
        if ($res["codigoError"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = 'No es posible determinar la inhabilidad - ' . $res["msgError"];
            \funcionesRues::validarInhabilidadGrabarLog($nit, $proponente, $nombre, '', '', $observaciones, $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        $arrAnos = array();

        // 2016-04-06: JINT: Verifica que cada multa sea contada solo una vez
        // Toma el último registro reportado por el RUES para cada caso.
        if (!empty($res["multas"])) {
            $multas = array();
            foreach ($res["multas"] as $m) {
                $ind = ltrim($m["nit_proponente"], "0") . '-' . ltrim($m["nit_entidad"], "0") . '-' . $m["municipio_entidad"] . '-' . ltrim($m["numero_contrato"] . '-' . $m["numero_acto_administrativo"], "0");
                $multas[$ind] = $m;
            }
            $res["multas"] = $multas;
        }

        // 2016-04-06: JINT: Verifica que cada sanción sea contada solo una vez
        // Toma el último registro reportado por el RUES para cada caso.
        if (!empty($res["sanciones"])) {
            $sanciones = array();
            foreach ($res["sanciones"] as $m) {
                $ind = ltrim($m["nit_proponente"], "0") . '-' . ltrim($m["nit_entidad"], "0") . '-' . $m["municipio_entidad"] . '-' . ltrim($m["numero_contrato"] . '-' . $m["numero_acto_administrativo"], "0");
                $sanciones[$ind] = $m;
            }
            $res["sanciones"] = $sanciones;
        }

        //
        if (!empty($res["multas"])) {
            foreach ($res["multas"] as $mul) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($mul["fecha_ejecutoria"], "0") != '') {
                    $fechacontrol = $mul["fecha_ejecutoria"];
                    $anocontrol = substr($mul["fecha_ejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $mul["fecha_acto_administrativo"];
                    $anocontrol = substr($mul["fecha_acto_administrativo"], 0, 4);
                }
                if (($mul["cod_estado"] == '0') || ($mul["cod_estado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (!isset($arrAnos[$anocontrol])) {
                            $arrAnos[$anocontrol]["anocontrol"] = $anocontrol;
                            $arrAnos[$anocontrol]["multas"] = 0;
                            $arrAnos[$anocontrol]["sanciones"] = 0;
                            $arrAnos[$anocontrol]["sinindicador"] = 0;
                            $arrAnos[$anocontrol]["fechaultimainscripcion"] = '';
                        }
                        $arrAnos[$anocontrol]["multas"] = $arrAnos[$anocontrol]["multas"] + 1;
                        if ($mul["fecha_inscripcion_camara"] > $arrAnos[$anocontrol]["fechaultimainscripcion"]) {
                            $arrAnos[$anocontrol]["fechaultimainscripcion"] = $mul["fecha_inscripcion_camara"];
                        }
                    }
                }
            }
        }

        //
        if (!empty($res["sanciones"])) {
            foreach ($res["sanciones"] as $san) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($san["fecha_ejecutoria"], "0") != '') {
                    $fechacontrol = $san["fecha_ejecutoria"];
                    $anocontrol = substr($san["fecha_ejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $san["fecha_acto_administrativo"];
                    $anocontrol = substr($san["fecha_acto_administrativo"], 0, 4);
                }
                if (($san["cod_estado"] == '0') || ($san["cod_estado"] == '2')) {
                    if (!isset($arrAnos[$anocontrol])) {
                        $arrAnos[$anocontrol]["anocontrol"] = $anocontrol;
                        $arrAnos[$anocontrol]["multas"] = 0;
                        $arrAnos[$anocontrol]["sanciones"] = 0;
                        $arrAnos[$anocontrol]["sinindicador"] = 0;
                        $arrAnos[$anocontrol]["fechaultimainscripcion"] = '';
                    }
                    if ($fechacontrol >= $fechabase) {
                        if (($san["condicion_incumplimiento"] == 'S') || ($san["condicion_incumplimiento"] == 's')) {
                            $arrAnos[$anocontrol]["sanciones"] = $arrAnos[$anocontrol]["sanciones"] + 1;
                            if ($san["fecha_inscripcion_camara"] > $arrAnos[$anocontrol]["fechaultimainscripcion"]) {
                                $arrAnos[$anocontrol]["fechaultimainscripcion"] = $san["fecha_inscripcion_camara"];
                            }
                        }
                        if ((trim($san["condicion_incumplimiento"]) == '') || ($san["condicion_incumplimiento"] == 'I') || ($san["condicion_incumplimiento"] == 'i')) {
                            $arrAnos[$anocontrol]["sinindicador"] = $arrAnos[$anocontrol]["sinindicador"] + 1;
                            if ($san["fecha_inscripcion_camara"] > $arrAnos[$anocontrol]["fechaultimainscripcion"]) {
                                $arrAnos[$anocontrol]["fechaultimainscripcion"] = $san["fecha_inscripcion_camara"];
                            }
                        }
                    }
                }
            }
        }

        if (empty($arrAnos)) {
            return $resultado;
        }

        //
        $inhabilidad = 'no';
        $anoinhabilidad = '';
        foreach ($arrAnos as $an) {
            $hoy = date("Ymd");
            $anoini = intval(substr($hoy, 0, 4)) - 3;
            $fecini = sprintf("%04s", $anoini) . substr($hoy, 4, 4);
            if ($an["fechaultimainscripcion"] >= $fecini) {
                if ($an["multas"] >= 5) {
                    $inhabilidad = 'si1';
                    $anoinhabilidad = $an["anocontrol"];
                } else {
                    if (($an["multas"] >= 0) && ($an["sanciones"] >= 2)) {
                        $inhabilidad = 'si2';
                        $anoinhabilidad = $an["anocontrol"];
                    } else {
                        if (($an["multas"] >= 2) && ($an["sanciones"] >= 1)) {
                            $inhabilidad = 'si3';
                            $anoinhabilidad = $an["anocontrol"];
                        }
                    }
                }
                if ($inhabilidad == 'no') {
                    if (($an["multas"] >= 0) && (($an["sanciones"] + $an["sinindicador"]) >= 2)) {
                        $inhabilidad = 'duda2';
                        $anoinhabilidad = $an["anocontrol"];
                    }
                    if (($an["multas"] >= 2) && (($an["sanciones"] + $an["sinindicador"]) >= 1)) {
                        $inhabilidad = 'duda3';
                        $anoinhabilidad = $an["anocontrol"];
                    }
                }
            }
        }

        if (($inhabilidad == 'si1') || ($inhabilidad == 'si2') || ($inhabilidad == 'si3')) {
            $resultado["inhabilidad"] = 'si';
            $resultado["ano"] = $anoinhabilidad;
            foreach ($res["multas"] as $mul) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($mul["fecha_ejecutoria"], "0") != '') {
                    $fechacontrol = $mul["fecha_ejecutoria"];
                    $anocontrol = substr($mul["fecha_ejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $mul["fecha_acto_administrativo"];
                    $anocontrol = substr($mul["fecha_acto_administrativo"], 0, 4);
                }
                if (($mul["cod_estado"] == '0') || ($mul["cod_estado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (substr($fechacontrol, 0, 4) == $anoinhabilidad) {
                            $resultado["multas"][] = $mul;
                        }
                    }
                }
            }
            foreach ($res["sanciones"] as $san) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($san["fecha_ejecutoria"], "0") != '') {
                    $fechacontrol = $san["fecha_ejecutoria"];
                    $anocontrol = substr($san["fecha_ejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $san["fecha_acto_administrativo"];
                    $anocontrol = substr($san["fecha_acto_administrativo"], 0, 4);
                }
                if (($san["cod_estado"] == '0') || ($san["cod_estado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (substr($fechacontrol, 0, 4) == $anoinhabilidad) {
                            if (($san["condicion_incumplimiento"] == 'S') || ($san["condicion_incumplimiento"] == 's')) {
                                $resultado["sanciones"][] = $san;
                            }
                        }
                        if ((trim($san["condicion_incumplimiento"]) == '') || ($san["condicion_incumplimiento"] == 'I') || ($san["condicion_incumplimiento"] == 'i')) {
                            $resultado["sinindicador"][] = $san;
                        }
                    }
                }
            }
        }

        if (($inhabilidad == 'duda2') || ($inhabilidad == 'duda3')) {
            $resultado["inhabilidad"] = 'duda';
            $resultado["ano"] = $anoinhabilidad;
            foreach ($res["multas"] as $mul) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($mul["fechaejecutoria"], "0") != '') {
                    $fechacontrol = $mul["fechaejecutoria"];
                    $anocontrol = substr($mul["fechaejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $mul["fecha_acto_administrativo"];
                    $anocontrol = substr($mul["fecha_acto_administrativo"], 0, 4);
                }
                if (($mul["codestado"] == '0') || ($mul["codestado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (substr($fechacontrol, 0, 4) == $anoinhabilidad) {
                            $resultado["multas"][] = $mul;
                        }
                    }
                }
            }
            foreach ($res["sanciones"] as $san) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($san["fechaejecutoria"], "0") != '') {
                    $fechacontrol = $san["fechaejecutoria"];
                    $anocontrol = substr($san["fechaejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $san["fecha_acto_administrativo"];
                    $anocontrol = substr($san["fecha_acto_administrativo"], 0, 4);
                }
                if (($san["codestado"] == '0') || ($san["codestado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (substr($fechacontrol, 0, 4) == $anoinhabilidad) {
                            if (($san["condicion_incumplimiento"] == 'S') || ($san["condicion_incumplimiento"] == 's')) {
                                $resultado["sanciones"][] = $san;
                            }
                        }
                        if ((trim($san["condicion_incumplimiento"]) == '') || ($san["condicion_incumplimiento"] == 'I') || ($san["condicion_incumplimiento"] == 'i')) {
                            $resultado["sinindicador"][] = $san;
                        }
                    }
                }
            }
        }

        if ($resultado["inhabilidad"] == 'si') {
            $resultado["tipoinhabilidad"] = $inhabilidad;
            // 			 12345678901234567890123456789012345678901234567890123456789012345
            $resultado["texto"][] = '        CERTIFICACION DE INCUMPLIMIENTO REITERADO';
            $resultado["texto"][] = '';
            $resultado["texto"][] = 'QUE DE ACUERDO CON LO DISPUESTO EN EL ARTICULO 90 DE LA  LEY 1474';
            $resultado["texto"][] = 'DE JULIO 12 DE 2011 Y CON LO DISPUESTO EN LOS PARAGRAFOS 2-4  DEL';
            $resultado["texto"][] = 'ARTICULO 6.1.3.5 DEL DECRETO 734 DE 2012:';
            $resultado["texto"][] = '';
            $resultado["texto"][] = 'EL INSCRITO REGISTRA MULTAS E INCUMPLIMIENTOS, REPORTADOS POR LAS';
            $resultado["texto"][] = 'ENTIDADES ESTATALES, DURANTE LA VIGENCIA FISCAL DEL A&ntilde;O ' . $resultado["ano"] . ' Y EN';
            $resultado["texto"][] = 'CONSECUENCIA SE ENCUENTRA INCURSO EN INHABILIDAD POR INCUMPLIMIEN';
            $resultado["texto"][] = 'TO REITERADO, POR EL TERMINO DE TRES (3) A&ntilde;OS CONTADOS  A  PARTIR';
            $resultado["texto"][] = 'DE LA INSCRIPCION DEL ULTIMO ACTO QUE IMPONE LA MULTA  O  DECLARA';
            $resultado["texto"][] = 'TORIA DE INCUMPLIMIENTO.';
            $resultado["texto"][] = '';
            $resultado["texto"][] = 'LA   INHABILIDAD   POR  INCUMPLIMIENTO  REITERADO  SE  CONSTITUYO';
            $resultado["texto"][] = 'TENIENDO EN CUENTA LAS SIGUIENTES MULTAS E INCUMPLIMIENTOS:';
            $resultado["texto"][] = '';

            // 			 12345678901234567890123456789012345678901234567890123456789012345
            $resultado["textosii"][0] = 'CERTIFICACION DE INCUMPLIMIENTO REITERADO';

            //
            $resultado["textosii"][1] = 'QUE DE ACUERDO CON LO DISPUESTO EN EL ARTICULO 90 DE LA  LEY 1474 ';
            $resultado["textosii"][1] .= 'DE JULIO 12 DE 2011 Y CON LO DISPUESTO EN LOS PARAGRAFOS 2-4 DEL ';
            $resultado["textosii"][1] .= 'ARTICULO 6.1.3.5 DEL DECRETO 734 DE 2012:<br><br>';
            $resultado["textosii"][1] .= 'EL INSCRITO REGISTRA MULTAS E INCUMPLIMIENTOS, REPORTADOS POR LAS ';
            $resultado["textosii"][1] .= 'ENTIDADES ESTATALES, DURANTE LA VIGENCIA FISCAL DEL A&ntilde;O ' . $resultado["ano"] . ' Y EN ';
            $resultado["textosii"][1] .= 'CONSECUENCIA SE ENCUENTRA INCURSO EN INHABILIDAD POR INCUMPLIMIENTO ';
            $resultado["textosii"][1] .= 'REITERADO, POR EL TERMINO DE TRES (3) A&ntilde;OS CONTADOS  A  PARTIR ';
            $resultado["textosii"][1] .= 'DE LA INSCRIPCION DEL ULTIMO ACTO QUE IMPONE LA MULTA  O  DECLARATORIA ';
            $resultado["textosii"][1] .= 'DE INCUMPLIMIENTO.<br><br>';
            $resultado["textosii"][1] .= 'LA INHABILIDAD POR INCUMPLIMIENTO REITERADO SE CONSTITUYO ';
            $resultado["textosii"][1] .= 'TENIENDO EN CUENTA LAS SIGUIENTES MULTAS E INCUMPLIMIENTOS:<br><br>';
        }

        //if (($resultado["inhabilidad"]=='si')  || ($resultado["inhabilidad"]=='duda')) {
        if ($resultado["inhabilidad"] == 'si') {
            if (!empty($resultado["multas"])) {
                $resultado["textosii"][2] = '';
            }
            foreach ($resultado["multas"] as $mul) {
                $resultado["texto"][] = '*** MULTA:';
                $resultado["texto"][] = 'NIT ENTIDAD : ' . $mul["nit_entidad"] . '-' . $mul["dv_entidad"];
                $resultado["texto"][] = 'NOMBRE : ' . substr($mul["nombre_entidad"], 0, 57);
                $resultado["texto"][] = 'MUNICIPIO : ' . retornarNombreMunicipioMysqliApi(null, $mul["municipio_entidad"]);
                $resultado["texto"][] = 'SECCIONAL : ' . $mul["seccional_entidad"];
                $resultado["texto"][] = 'NUMERO CONTRATO : ' . $mul["numero_contrato"];
                $resultado["texto"][] = 'ACTO ADMINISTRATIVO : ' . $mul["numero_acto_administrativo"];
                $resultado["texto"][] = 'FECHA ACTO ADMINISTRATIVO : ' . $mul["fecha_acto_administrativo"];
                $resultado["texto"][] = 'FECHA EJECUTORIA : ' . $mul["fecha_ejecutoria"];
                $resultado["texto"][] = 'VALOR MULTA : ' . $mul["valor_multa"];
                $resultado["texto"][] = 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $mul["codigo_camara"];
                $resultado["texto"][] = 'EN FECHA : ' . $mul["fecha_inscripcion_camara"];
                $resultado["texto"][] = 'NUMERO DE INSCRIPCION : ' . $mul["numero_inscripcion_libro"];
                $resultado["texto"][] = '';

                //
                $resultado["textosii"][2] .= '<strong>*** MULTA:</strong><br>';
                $resultado["textosii"][2] .= 'NIT ENTIDAD : ' . $mul["nit_entidad"] . '-' . $mul["dv_entidad"] . '<br>';
                $resultado["textosii"][2] .= 'NOMBRE : ' . substr($mul["nombre_entidad"], 0, 57) . '<br>';
                $resultado["textosii"][2] .= 'MUNICIPIO : ' . retornarNombreMunicipioMysqliApi(null, $mul["municipio_entidad"]) . '<br>';
                $resultado["textosii"][2] .= 'SECCIONAL : ' . $mul["seccional_entidad"] . '<br>';
                $resultado["textosii"][2] .= 'NUMERO CONTRATO : ' . $mul["numero_contrato"] . '<br>';
                $resultado["textosii"][2] .= 'ACTO ADMINISTRATIVO : ' . $mul["numero_acto_administrativo"] . '<br>';
                $resultado["textosii"][2] .= 'FECHA ACTO ADMINISTRATIVO : ' . $mul["fecha_acto_administrativo"] . '<br>';
                $resultado["textosii"][2] .= 'FECHA EJECUTORIA : ' . $mul["fecha_ejecutoria"] . '<br>';
                $resultado["textosii"][2] .= 'VALOR MULTA : ' . $mul["valor_multa"] . '<br>';
                $resultado["textosii"][2] .= 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $mul["codigo_camara"] . '<br>';
                $resultado["textosii"][2] .= 'EN FECHA : ' . $mul["fecha_inscripcion_camara"] . '<br>';
                $resultado["textosii"][2] .= 'NUMERO DE INSCRIPCION : ' . $mul["numero_inscripcion_libro"] . '<br><br>';
            }

            if (!empty($resultado["sanciones"])) {
                $resultado["textosii"][3] = '';
            }
            foreach ($resultado["sanciones"] as $san) {

                //
                $san["descripcion"] = sprintf("%-600s", $san["descripcion"]);
                $resultado["texto"][] = '*** SANCION :';
                $resultado["texto"][] = 'NIT ENTIDAD : ' . $san["nit_entidad"] . '-' . $san["dv_entidad"];
                $resultado["texto"][] = 'NOMBRE : ' . substr($san["nombre_entidad"], 0, 57);
                $resultado["texto"][] = 'MUNICIPIO : ' . retornarNombreMunicipioMysqliApi(null, $san["municipio_entidad"]);
                $resultado["texto"][] = 'SECCIONAL : ' . $san["seccional_entidad"];
                $resultado["texto"][] = 'NUMERO CONTRATO : ' . $san["numero_contrato"];
                $resultado["texto"][] = 'ACTO ADMINISTRATIVO : ' . $san["numero_acto_administrativo"];
                $resultado["texto"][] = 'FECHA ACTO ADMINISTRATIVO : ' . $san["fecha_acto_administrativo"];
                $resultado["texto"][] = 'FECHA EJECUTORIA : ' . $san["fecha_ejecutoria"];
                $resultado["texto"][] = 'DESCRIPCION SANCION : ' . substr($san["descripcion_sancion"], 0, 44);
                if (trim(substr($san["descripcion"], 44, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 44, 65);
                }
                if (trim(substr($san["descripcion"], 109, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 109, 65);
                }
                if (trim(substr($san["descripcion"], 174, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 174, 65);
                }
                if (trim(substr($san["descripcion"], 239, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 239, 65);
                }
                if (trim(substr($san["descripcion"], 304, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 304, 65);
                }
                if (trim(substr($san["descripcion"], 369, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 369, 65);
                }
                $resultado["texto"][] = 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $san["codigo_camara"];
                $resultado["texto"][] = 'EN FECHA : ' . $san["fecha_inscripcion_camara"];
                $resultado["texto"][] = 'NUMERO DE INSCRIPCION : ' . $san["numero_inscripcion_libro"];
                $resultado["texto"][] = '';

                //
                $resultado["textosii"][3] .= '<strong>*** SANCION :</strong><br>';
                $resultado["textosii"][3] .= 'NIT ENTIDAD : ' . $san["nit_entidad"] . '-' . $san["dv_entidad"] . '<br>';
                $resultado["textosii"][3] .= 'NOMBRE : ' . substr($san["nombre_entidad"], 0, 57) . '<br>';
                $resultado["textosii"][3] .= 'MUNICIPIO : ' . retornarNombreMunicipioMysqliApi(null, $san["municipio_entidad"]) . '<br>';
                $resultado["textosii"][3] .= 'SECCIONAL : ' . $san["seccional_entidad"] . '<br>';
                $resultado["textosii"][3] .= 'NUMERO CONTRATO : ' . $san["numero_contrato"] . '<br>';
                $resultado["textosii"][3] .= 'ACTO ADMINISTRATIVO : ' . $san["numero_acto_administrativo"] . '<br>';
                $resultado["textosii"][3] .= 'FECHA ACTO ADMINISTRATIVO : ' . $san["fecha_acto_administrativo"] . '<br>';
                $resultado["textosii"][3] .= 'FECHA EJECUTORIA : ' . $san["fecha_ejecutoria"] . '<br>';
                $resultado["textosii"][3] .= 'DESCRIPCION SANCION : ' . trim($san["descripcion_sancion"]) . '<br>';
                $resultado["textosii"][3] .= 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $san["codigo_camara"] . '<br>';
                $resultado["textosii"][3] .= 'EN FECHA : ' . $san["fecha_inscripcion_camara"] . '<br>';
                $resultado["textosii"][3] .= 'NUMERO DE INSCRIPCION : ' . $san["numero_inscripcion_libro"] . '<br><br>';
            }

            if (!empty($resultado["sinindicador"])) {
                $resultado["textosii"][4] = '';
            }
            foreach ($resultado["sinindicador"] as $san) {
                $san["descripcion"] = sprintf("%-600s", $san["descripcion"]);
                $resultado["texto"][] = '*** SANCION SIN INDICADOR DE INCUMPLIMIENTO :';
                $resultado["texto"][] = 'NIT ENTIDAD : ' . $san["nit_entidad"] . '-' . $san["dv_entidad"];
                $resultado["texto"][] = 'NOMBRE : ' . substr($san["nombre_entidad"], 0, 57);
                $resultado["texto"][] = 'MUNICIPIO : ' . retornarNombreMunicipioMysqliApi(null, $san["municipio_entidad"]);
                $resultado["texto"][] = 'NUMERO CONTRATO : ' . $san["numero_contrato"];
                $resultado["texto"][] = 'ACTO ADMINISTRATIVO : ' . $san["numero_acto_administrativo"];
                $resultado["texto"][] = 'SECCIONAL : ' . $san["seccional_entidad"];
                $resultado["texto"][] = 'FECHA ACTO ADMINISTRATIVO : ' . $san["fecha_acto_administrativo"];
                $resultado["texto"][] = 'FECHA EJECUTORIA : ' . $san["fecha_ejecutoria"];
                $resultado["texto"][] = 'DESCRIPCION SANCION: ' . substr($san["descripcion"], 0, 44);
                if (trim(substr($san["descripcion"], 44, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 44, 65);
                }
                if (trim(substr($san["descripcion"], 109, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 109, 65);
                }
                if (trim(substr($san["descripcion"], 174, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 174, 65);
                }
                if (trim(substr($san["descripcion"], 239, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 239, 65);
                }
                if (trim(substr($san["descripcion"], 304, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 304, 65);
                }
                if (trim(substr($san["descripcion"], 369, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 369, 65);
                }
                $resultado["texto"][] = 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $san["codigo_camara"];
                $resultado["texto"][] = 'EN FECHA : ' . $san["fecha_inscripcion_camara"];
                $resultado["texto"][] = 'NUMERO DE INSCRIPCION : ' . $san["numero_inscripcion_libro"];
                $resultado["texto"][] = '';

                //
                $resultado["textosii"][4] .= '<strong>*** SANCION SIN INDICADOR DE INCUMPLIMIENTO :</strong><br>';
                $resultado["textosii"][4] .= 'NIT ENTIDAD : ' . $san["nit_entidad"] . '-' . $san["dv_entidad"] . '<br>';
                $resultado["textosii"][4] .= 'NOMBRE : ' . substr($san["nombre_entidad"], 0, 57) . '<br>';
                $resultado["textosii"][4] .= 'MUNICIPIO : ' . retornarNombreMunicipioMysqliApi(null, $san["municipio_entidad"]) . '<br>';
                $resultado["textosii"][4] .= 'ACTO ADMINISTRATIVO : ' . $san["numero_acto_administrativo"] . '<br>';
                $resultado["textosii"][4] .= 'SECCIONAL : ' . $san["seccional_entidad"] . '<br>';
                $resultado["textosii"][4] .= 'NUMERO CONTRATO : ' . $san["numero_contrato"] . '<br>';
                $resultado["textosii"][4] .= 'FECHA ACTO ADMINISTRATIVO : ' . $san["fecha_acto_administrativo"] . '<br>';
                $resultado["textosii"][4] .= 'FECHA EJECUTORIA : ' . $san["fecha_ejecutoria"] . '<br>';
                $resultado["textosii"][4] .= 'DESCRIPCION SANCION: ' . trim($san["descripcion"]) . '<br>';
                $resultado["textosii"][4] .= 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $san["codigo_camara"] . '<br>';
                $resultado["textosii"][4] .= 'EN FECHA : ' . $san["fecha_inscripcion_camara"] . '<br>';
                $resultado["textosii"][4] .= 'NUMERO DE INSCRIPCION : ' . $san["numero_inscripcion_libro"] . '<br><br>';
            }
        }

        // 2018-02-05: JINT: Almacena el log del cálculo de la inhabilidad del proponente
        $txt = '';
        foreach ($resultado["textosii"] as $tx) {
            $txt .= $tx . '<br><br>';
        }
        \funcionesRues::validarInhabilidadGrabarLog($nit, $proponente, $nombre, $resultado["inhabilidad"], $txt, $observaciones, 'OK');

        //
        return $resultado;
    }

    // 2018-02-05: JINT: Graba log con el resultado del cálculo de la inhabilidad
    public static function validarInhabilidadGrabarLog($nit, $proponente, $nombre, $inh, $txt, $observaciones, $complemento) {
        require_once('mysqli.php');
        $arrCampos = array(
            'fecha',
            'hora',
            'nit',
            'proponente',
            'nombre',
            'inhabilidad',
            'texto',
            'estadosirep'
        );
        $arrValores = array(
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $nit . "'",
            "'" . $proponente . "'",
            "'" . addslashes($nombre) . "'",
            "'" . $inh . "'",
            "'" . addslashes($txt) . "'",
            "'" . addslashes($observaciones . ' : ' . $complemento) . "'"
        );
        insertarRegistrosMysqliApi(null, 'mreg_log_control_inhabilidades_proponentes', $arrCampos, $arrValores);
    }

    /**
     *  Weymer : 2020-05-26 : Función sustituta de RR41N integrando RUES en modelo REST
     */
    public static function consumirRegMer($mysqli = null, $matricula = '', $accion = '', $data = array(), $ambiente = '') {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $nameLog = 'consumirRegMer_' . date("Ymd");

        //
        $datos = array();
        if ($matricula == '') {
            if (isset($data) && !empty($data)) {
                $datos = $data;
            } else {
                if (isset($_SESSION['formulario']) && !empty($_SESSION['formulario'])) {
                    $datos = $_SESSION['formulario'];
                }
            }
        } else {
            if (empty($data)) {
                if ($mysqli == null) {
                    $mysqli = conexionMysqliApi();
                    $cerrarMysqli = 'si';
                } else {
                    $cerrarMysqli = 'no';
                }
                $datos = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $matricula, '', '', '', 'si', 'N', 'SII');
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                    $mysqli = null;
                }
            } else {
                $datos = $data;
            }
        }
        if ($accion == 'forzar') {
            $renovarhash = true;
        } else {
            $renovarhash = false;
        }
        $requestRues = \funcionesRues::construirParametrosRegMer($mysqli, $datos, $renovarhash);
        $sal = json_encode($requestRues, JSON_PRETTY_PRINT);
        // \logApi::general2($nameLog, $matricula, 'Información a enviar al RUES : ' . chr(10) . chr(13) . $sal);
        if ($requestRues["codigo_error"] !== "") {
            \logApi::general2($nameLog, $matricula, 'Información a enviar al RUES : ' . chr(10) . chr(13) . $sal);
            $respuesta["codigoHTTP"] = '';
            $respuesta["codigoError"] = '9970';
            $respuesta["msgError"] = 'actualizacionRegMerV1 - Error en datos armando json - ' . $requestRues["mensaje_error"];
            $respuesta["hashControl"] = '';
            $respuesta["version"] = "1";
            return $respuesta;
        }

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            \logApi::general2($nameLog, '', 'Error solicitando token en actualizacionRegMerV1');
            $respuesta["codigoHTTP"] = '';
            $respuesta["codigoError"] = '9969';
            $respuesta["msgError"] = 'actualizacionRegMerV1 - Error solicitando token';
            $respuesta["hashControl"] = '';
            $respuesta["version"] = "1";
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . 'api/RegMer/');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sal);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, $matricula, 'La respuesta del servicio web no es un Json (1) - ' . $responseRues);
                        $evaluarJson = 'no';
                    } else {
                        \logApi::general2($nameLog, $matricula, 'ResponseRegMer : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, $matricula, 'ResponseRegMer : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }

        curl_close($ch);

        if ($evaluarJson == 'si') {
            //Retornar respuesta como array
            $resp = json_decode($responseRues, true);

            //Controles de retornos para interfaz SII
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                    $respuesta["version"] = "1";
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = 'Matricula Actualizada en RUES';
                    $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                    $respuesta["version"] = "1";
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                $respuesta["version"] = "1";
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigoError"] = '0000';
            } else {
                $respuesta["codigoError"] = '0004';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code . ' - ' . $responseRues);
            $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
            $respuesta["version"] = "1";
            return $respuesta;
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $datos
     * @param type $renovarhash
     * @return type
     */
    public static function construirParametrosRegMer($mysqli = null, $datos = array(), $renovarhash = false) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/retornarHomologaciones.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysqli = 'si';
        } else {
            $cerrarMysqli = 'no';
        }

        //
        $arrDatosInfoFinanciera = array();
        $arrDatosInfoCapitales = array();
        $arrDatosInfoAdicional = array();
        $arrDatosPropietarios = array();
        $arrDatosVinculos = array();
        $arrDatosHistoricoPagos = array();
        $Nit = '';
        $dv = '';

        //
        if (!isset($datos["nit"])) {
            $datos["nit"] = '';
        }
        $datos["nit"] = str_replace(array(",", "-", " "), "", (string) $datos["nit"]);

        // En caso de sucursales y agencias        
        // Se borran las identificaciones
        if (($datos["organizacion"] != '01') && ($datos["organizacion"] != '02') && ($datos["categoria"] == '2' || $datos["categoria"] == '3')) {
            $datos["tipoidentificacion"] = '';
            $datos["identificacion"] = '';
            $datos["nit"] = '';
        }

        // En caso de personas jurídicas principales
        // Solo puede tener nit o vacío
        if (($datos["organizacion"] != '01') && ($datos["organizacion"] != '02') && ($datos["categoria"] == '1')) {
            if (trim($datos["tipoidentificacion"]) != '2') {
                if (ltrim(trim($datos["nit"]), "0") !== '') {
                    $datos["tipoidentificacion"] = '2';
                    $datos["identificacion"] = ltrim(trim($datos["nit"]), "0");
                } else {
                    $datos["tipoidentificacion"] = '';
                    $datos["identificacion"] = '';
                }
            }
        }


        //
        if (trim((string) $datos['tipoidentificacion']) == '2') {
            if (($datos["organizacion"] != '01') && ($datos["organizacion"] != '02') && ($datos["categoria"] == '1')) {
                $longitudNit = strlen(trim($datos["nit"]));
                switch ($longitudNit) {
                    case 10:
                        $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                        $numIdentificacion = substr(trim($datos["nit"]), 0, -1);
                        $Nit = $numIdentificacion;
                        $dv = substr($datos["nit"], -1, 1);
                        break;
                    case 9:
                        $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                        $numIdentificacion = trim($datos["nit"]);
                        $Nit = $numIdentificacion;
                        $dv = \funcionesGenerales::calcularDv($datos["nit"]);
                        break;
                    default:
                        $tipoIdentificacion = '';
                        $numIdentificacion = '00000000000000';
                        $Nit = '';
                        $dv = '';
                        break;
                }
            } else {
                if ($datos["nit"] != '') {
                    $nit1 = sprintf("%020s", $datos["nit"]);
                    $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                    $numIdentificacion = ltrim(substr($nit1, 0, 19), "0");
                    $Nit = $numIdentificacion;
                    $dv = substr($nit1, 19, 1);
                } else {
                    $tipoIdentificacion = '';
                    $numIdentificacion = '00000000000000';
                    $Nit = '';
                    $dv = '';
                }
            }
        } else {
            if (trim((string) $datos['tipoidentificacion']) != 'V') {
                if (trim($datos["nit"]) != '') {
                    $nit1 = sprintf("%020s", $datos["nit"]);
                    $Nit = ltrim(substr((string) $nit1, 0, 19), "0");
                    $dv = substr((string) $nit1, 19, 1);
                }
                $tipoIdentificacion = trim((string) $datos['tipoidentificacion']);
                $numIdentificacion = trim((string) $datos["identificacion"]);
            } else {
                $tipoIdentificacion = '09';
                $numIdentificacion = $datos["identificacion"];
            }
            if (trim($datos["nit"]) != '') {
                $nit1 = sprintf("%020s", $datos["nit"]);
                $Nit = ltrim(substr((string) $nit1, 0, 19), "0");
                $dv = substr((string) $nit1, 19, 1);
            }
        }

        /*
         * Forzar en el caso de establecimientos, sucursales o agencias que se envie vacio los datos de identificación sin importar la data que tenga la cámara.
         */

        //Sucursales y agencias
        $valorEstSucAg = '';
        if (($datos["categoria"] == '2') || ($datos["categoria"] == '3')) {
            $tipoIdentificacion = '';
            $numIdentificacion = '00000000000000';
            $Nit = '';
            $dv = '';
            $valorEstSucAg = $datos["actvin"];
        }
        //Establecimientos 
        if ($datos["organizacion"] == '02') {
            $tipoIdentificacion = '';
            $numIdentificacion = '00000000000000';
            $Nit = '';
            $dv = '';
            $valorEstSucAg = $datos["valest"];
        }


        $fechaMatricula = $datos["fechamatricula"];
        $fechaRenovacion = $datos["fecharenovacion"];
        $ultAnoRenovado = $datos["ultanoren"];
        $anoMatricula = substr($fechaMatricula, 0, 4);
        $anoRenovacion = substr($fechaRenovacion, 0, 4);

        //2020-11-26 : No tener en cuenta lo siguiente a partir de solicitudes de camnbio de domicilio.
        /*
          if ($anoMatricula == $anoRenovacion) {
          $fechaRenovacion = '';
          $ultAnoRenovado = 0;
          }

          if ($anoMatricula == $ultAnoRenovado) {
          $fechaRenovacion = '';
          $ultAnoRenovado = 0;
          }
         */
        switch ($datos["estadomatricula"]) {
            case 'MC':
            case 'MF':
            case 'MG':
            case 'IC':
            case 'IF':
            case 'IG':

                if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
                    if (trim($datos["fechaliquidacion"]) != '') {
                        $fechaCancelacion = $datos["fechaliquidacion"];
                    } else {
                        $fechaCancelacion = $datos["fechacancelacion"];
                    }
                } else {
                    if (trim($datos["fechacancelacion"]) != '') {
                        $fechaCancelacion = $datos["fechacancelacion"];
                    } else {
                        $fechaCancelacion = '';
                    }
                }

// JINT - 20241030 - 
                if ($fechaCancelacion == '') {
                    $fechaCancelacion = $datos["fecharenovacion"];
                }
                if ($fechaCancelacion == '') {
                    $fechaCancelacion = $datos["fechamatricula"];
                }

                $indicadorMotivoCancelacion = '0';
                if (trim($datos["motivocancelacion"]) != '') {
                    $indicadorMotivoCancelacion = $datos["motivocancelacion"];
                }

                break;
            default:
                $fechaCancelacion = '';
                $indicadorMotivoCancelacion = '';
                break;
        }

        if (trim($datos["fechavencimiento"]) == '99999999') {
            $fechaVigencia = '99991231';
        } else {
            if (trim($datos["fechavencimiento"]) != '') {
                $fechaVigencia = $datos["fechavencimiento"];
            } else {
                $fechaVigencia = '99991231';
            }
        }

        if (trim($datos["fechaconstitucion"]) != '') {
            $fechaConstitucion = $datos["fechaconstitucion"];
        } else {
            $fechaConstitucion = '';
        }

        /*
         * Validaciones Campo clasificación Importador Exportador
         */
        if (($datos["impexp"] == '1') || ($datos["impexp"] == '3')) {
            $indicadorImpExp = '1';
        }
        if (($datos["impexp"] == '2') || ($datos["impexp"] == '3')) {
            $indicadorImpExp = '2';
        } else {
            $indicadorImpExp = '0';
        }

        /*
         * Validaciones Campo Afiliado
         */
        switch ($datos["afiliado"]) {
            case '':
            case '0':
            case '2':
                $indicadorAfiliado = 'N';
                break;
            case '1':
            case '3':
            case '5':
                $indicadorAfiliado = 'S';
                break;
            default:
                break;
        }

        $CantEstablecimientos = '';
        if (isset($datos["cantest"])) {
            $CantEstablecimientos = trim($datos["cantest"]);
        }

        /*
         * Parámetros Información Financiera 
         */
        if (isset($datos["hf"])) {
            if (count($datos["hf"]) > 0) {
                $arrHistoriaFinanciera = (array) $datos["hf"];
                //Obtiene año inicial de datos (últimos cinco años de información financiera)                
                $anoInicial = $datos["anodatos"] - 5;
                $anosReportados = array();
                $sec = 0;

                foreach ($arrHistoriaFinanciera as $value) {
                    if ($anoInicial < $value["anodatos"]) {

                        //Verifica que el año recorrido no fue reportado previamente y crear datos de control
                        if (!isset($anosReportados[$value["anodatos"]])) {
                            $anosReportados[$value["anodatos"]] = $sec;
                        }

                        //Si el año fue reportado previamente reutiliza la secuencia de años y actualiza valores
                        if (isset($anosReportados[$value["anodatos"]])) {
                            $secBase = $sec;
                            $sec = $anosReportados[$value["anodatos"]];
                        }

                        if ($value["actnocte"] === null) {
                            $value["actnocte"] = 0;
                        }
                        $arrDatosInfoFinanciera[$sec]['ano_informacion_financiera'] = $value["anodatos"];
                        $arrDatosInfoFinanciera[$sec]['activo_corriente'] = $value["actcte"];
                        $arrDatosInfoFinanciera[$sec]['activo_no_corriente'] = $value["actnocte"];
                        $arrDatosInfoFinanciera[$sec]['activo_total'] = $value["acttot"];
                        $arrDatosInfoFinanciera[$sec]['pasivo_corriente'] = $value["pascte"];
                        $arrDatosInfoFinanciera[$sec]['pasivo_no_corriente'] = $value["paslar"];
                        $arrDatosInfoFinanciera[$sec]['pasivo_total'] = $value["pastot"];
                        $arrDatosInfoFinanciera[$sec]['patrimonio_neto'] = $value["pattot"];
                        $arrDatosInfoFinanciera[$sec]['pasivo_mas_patrimonio'] = $value["paspat"];
                        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
                            if ($datos["categoria"] == '1') {
                                $arrDatosInfoFinanciera[$sec]['balance_social'] = $value["balsoc"];
                            }
                        } else {
                            $arrDatosInfoFinanciera[$sec]['balance_social'] = '';
                        }
                        $arrDatosInfoFinanciera[$sec]['ingresos_actividad_ordinaria'] = $value["ingope"];
                        $arrDatosInfoFinanciera[$sec]['otros_ingresos'] = $value["ingnoope"];
                        $arrDatosInfoFinanciera[$sec]['costo_ventas'] = $value["cosven"];
                        $arrDatosInfoFinanciera[$sec]['gastos_operacionales'] = $value["gtoven"];
                        $arrDatosInfoFinanciera[$sec]['otros_gastos'] = $value["gtoadm"];
                        $arrDatosInfoFinanciera[$sec]['gastos_impuestos'] = $value["gasimp"];
                        $arrDatosInfoFinanciera[$sec]['utilidad_perdida_operacional'] = $value["utiope"];
                        $arrDatosInfoFinanciera[$sec]['resultado_del_periodo'] = $value["utinet"];
                        $arrDatosInfoFinanciera[$sec]['valor_est_suc_ag'] = $valorEstSucAg;

                        //Si el año de datos pertenece a una secuencia previa reasigna el valor de la secuencia base
                        if (isset($anosReportados[$value["anodatos"]])) {
                            $sec = $secBase;
                        }
                        //Incrementa la secuencia
                        $sec++;
                    }
                }
                //Obtiene los valores recogidos en el arreglo e indexa el resultado 
                $arrDatosInfoFinanciera = array_values($arrDatosInfoFinanciera);
            }
        }


        /*
         * Parámetros Información Capitales 
         */
        if ($datos["organizacion"] != '01' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
            if ($datos["categoria"] == '1') {
                if (isset($datos["capitales"])) {
                    if (count($datos["capitales"]) > 0) {
                        if ($datos["anodatos"] != 0 && is_numeric($datos["anodatos"])) {
                            $arrHistoriaCapitales = (array) $datos["capitales"];

                            //Obtiene año inicial de datos (últimos cinco años de información Capitales)                
                            $anoInicial = $datos["anodatos"] - 10;
                            $anosReportados = array();
                            $sec = 0;
                            $secfin = null;
                            foreach ($arrHistoriaCapitales as $value) {
                                //Verifica que el año recorrido no fue reportado previamente y crear datos de control
                                if (!isset($anosReportados[$value["anodatos"]])) {
                                    $anosReportados[$value["anodatos"]] = $sec;
                                }

                                //Si el año fue reportado previamente reutiliza la secuencia de años y actualiza valores
                                if (isset($anosReportados[$value["anodatos"]])) {
                                    $secBase = $sec;
                                    $sec = $anosReportados[$value["anodatos"]];
                                }
                                $secfin = $sec;
                                $arrDatosInfoCapitales[$sec] = array();
                                $arrDatosInfoCapitales[$sec]['fecha_modificacion_capital'] = $value["fechadatos"];

                                //
                                if ($datos["organizacion"] != '09') {
                                    $arrDatosInfoCapitales[$sec]['capital_social'] = $value["social"];
                                    $arrDatosInfoCapitales[$sec]['capital_autorizado'] = $value["autorizado"];
                                    $arrDatosInfoCapitales[$sec]['capital_suscrito'] = $value["suscrito"];
                                    $arrDatosInfoCapitales[$sec]['capital_pagado'] = $value["pagado"];
                                    $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_mujeres'] = 0;
                                    $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_etnia'] = 0;
                                }

                                //
                                if ($datos["organizacion"] == '09') {
                                    $arrDatosInfoCapitales[$sec]['eat_aportes_laborales'] = $value["apolab"];
                                    $arrDatosInfoCapitales[$sec]['eat_aportes_activos'] = $value["apoact"];
                                    $arrDatosInfoCapitales[$sec]['eat_aportes_laborales_adicionales'] = $value["apolabadi"];
                                    $arrDatosInfoCapitales[$sec]['eat_aportes_en_dinero'] = $value["apodin"];
                                    $arrDatosInfoCapitales[$sec]['eat_total_aportes'] = $value["apolab"] + $value["apoact"] + $value["apolabadi"] + $value["apodin"];
                                    $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_mujeres'] = 0;
                                    $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_etnia'] = 0;
                                }

                                //Si el año de datos pertenece a una secuencia previa reasigna el valor de la secuencia base
                                if (isset($anosReportados[$value["anodatos"]])) {
                                    $sec = $secBase;
                                }
                                //Incrementa la secuencia
                                $sec++;
                                // }
                            }

                            // Asocia el porcentaje de participacion mujeres con el último capital que se reporte
                            if ($secfin !== null) {
                                if ($datos["participacionmujeres"] != '') {
                                    $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_mujeres'] = $datos["participacionmujeres"];
                                }
                                if ($datos["participacionetnia"] != '') {
                                    $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_etnia'] = $datos["participacionetnia"];
                                }
                            }

                            //Obtiene los valores recogidos en el arreglo e indexa el resultado 
                            $arrDatosInfoCapitales = array_values($arrDatosInfoCapitales);
                        }
                    }
                }
            }
        }


        /*
         * Parámetros Información Capitales - ESADL
         */
        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
            if ($data["categoria"] == '1') {
                if (isset($datos["patrimoniosesadl"])) {
                    if (count($datos["patrimoniosesadl"]) > 0) {
                        $arrHistoriaCapitales = (array) $datos["patrimoniosesadl"];

                        //Obtiene año inicial de datos (últimos cinco años de información Capitales)                
                        $anoInicial = $datos["anodatos"] - 5;
                        $anosReportados = array();
                        $sec = 0;
                        $secfin = null;

                        foreach ($arrHistoriaCapitales as $value) {
                            // if ($anoInicial < $value["anodatos"]) {
                            //Verifica que el año recorrido no fue reportado previamente y crear datos de control
                            if (!isset($anosReportados[$value["anodatos"]])) {
                                $anosReportados[$value["anodatos"]] = $sec;
                            }

                            //Si el año fue reportado previamente reutiliza la secuencia de años y actualiza valores
                            if (isset($anosReportados[$value["anodatos"]])) {
                                $secBase = $sec;
                                $sec = $anosReportados[$value["anodatos"]];
                                $secfin = $sec;
                            }

                            //
                            $arrDatosInfoCapitales[$sec]['fecha_modificacion_capital'] = $value["fechadatos"];
                            $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_mujeres'] = 0;
                            $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_etnia'] = 0;
                            $arrDatosInfoCapitales[$sec]['patrimonio_esal'] = $value["patrimonio"];
                            $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_mujeres'] = 0;
                            $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_etnia'] = 0;

                            //Si el año de datos pertenece a una secuencia previa reasigna el valor de la secuencia base
                            if (isset($anosReportados[$value["anodatos"]])) {
                                $sec = $secBase;
                            }
                            //Incrementa la secuencia
                            $sec++;
                            // }
                        }

                        //
                        if ($secfin !== null) {
                            if ($datos["participacionmujeres"] != '' && doubleval($datos["participacionmujeres"]) != 0) {
                                $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_mujeres'] = $datos["participacionmujeres"];
                            }
                            if ($datos["participacionetnia"] != '' && doubleval($datos["participacionetnia"]) != 0) {
                                $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_etnia'] = $datos["participacionetnia"];
                            }
                        }

                        //Obtiene los valores recogidos en el arreglo e indexa el resultado 
                        $arrDatosInfoCapitales = array_values($arrDatosInfoCapitales);
                    }
                }
            }
        }

        /*
         * Parámetros Propietarios de Establecimientos
         */
        if ($datos['organizacion'] == '02') {
            if (isset($datos["propietarios"])) {
                if (count($datos["propietarios"]) > 0) {
                    $arrPropietarios = (array) $datos["propietarios"];
                    foreach ($arrPropietarios as $key => $value) {

                        // Si es persona natural comprobada
                        $calprop = 'no';
                        if (isset($value['organizacionpropietario']) && $value['organizacionpropietario'] == '01') {
                            $tipoIdePropietario = trim($value['idtipoidentificacionpropietario']);
                            $numIdePropietario = trim($value['identificacionpropietario']);
                            if (ltrim(trim($value['nitpropietario']), "0") != '') {
                                $sepíde = \funcionesGenerales::separarDv($value['nitpropietario']);
                                $NitPropietario = $sepíde["identificacion"];
                                $dvPropietario = $sepíde["dv"];
                            } else {
                                $NitPropietario = trim($value['identificacionpropietario']);
                                $dvPropietario = \funcionesGenerales::calcularDv($NitPropietario);
                            }
                            $calprop = 'si';
                        }

                        // Si es persona juridica comprobada
                        if (isset($value['organizacionpropietario']) && $value['organizacionpropietario'] > '02') {
                            $sepide = \funcionesGenerales::separarDv($value['nitpropietario']);
                            $calcdv = \funcionesGenerales::calcularDv($sepide["identificacion"]);
                            if ($calcdv == $sepide["dv"]) {
                                $tipoIdePropietario = '2';
                                $numIdePropietario = $sepide["identificacion"];
                                $NitPropietario = $sepide["identificacion"];
                                $dvPropietario = $sepide["dv"];
                            } else {
                                $tipoIdePropietario = '2';
                                $numIdePropietario = $value['nitpropietario'];
                                $NitPropietario = $value['nitpropietario'];
                                $dvPropietario = \funcionesGenerales::calcularDv($value['nitpropietario']);
                            }
                            $calprop = 'si';
                        }

                        // Si no sabemos que es
                        if ($calprop == 'no') {
                            if (ltrim(trim($value['nitpropietario']), "0") != '') {
                                $sepide = \funcionesGenerales::separarDv($value['nitpropietario']);
                                $calcdv = \funcionesGenerales::calcularDv($sepide["identificacion"]);
                                if ($calcdv == $sepide["dv"]) {
                                    $tipoIdePropietario = trim($value['idtipoidentificacionpropietario']);
                                    $numIdePropietario = trim($value['identificacionpropietario']);
                                    $NitPropietario = $sepide["identificacion"];
                                    $dvPropietario = $sepide["dv"];
                                } else {
                                    $tipoIdePropietario = trim($value['idtipoidentificacionpropietario']);
                                    $numIdePropietario = trim($value['identificacionpropietario']);
                                    $NitPropietario = $value['nitpropietario'];
                                    $dvPropietario = \funcionesGenerales::calcularDv($value['nitpropietario']);
                                }
                            } else {
                                if ($value['idtipoidentificacionpropietario'] == '2') {
                                    $sepide = \funcionesGenerales::separarDv($value['identificacionpropietario']);
                                    $calcdv = \funcionesGenerales::calcularDv($sepide["identificacion"]);
                                    if ($calcdv == $sepide["dv"]) {
                                        $tipoIdePropietario = '2';
                                        $numIdePropietario = $sepide["identificacion"];
                                        $NitPropietario = $sepide["identificacion"];
                                        $dvPropietario = $sepide["dv"];
                                    } else {
                                        $tipoIdePropietario = '2';
                                        $numIdePropietario = $value['identificacionpropietario'];
                                        $NitPropietario = $value['identificacionpropietario'];
                                        $dvPropietario = \funcionesGenerales::calcularDv($value['identificacionpropietario']);
                                    }
                                } else {
                                    $tipoIdePropietario = trim($value['idtipoidentificacionpropietario']);
                                    $numIdePropietario = trim($value['identificacionpropietario']);
                                    $NitPropietario = '';
                                    $dvPropietario = '';
                                }
                            }
                        }
                        $arrDatosPropietarios[$key - 1]['codigo_clase_identificacion_propietario'] = homologacionTipoIdentificacionRUESApi($tipoIdePropietario);
                        if ($tipoIdePropietario == 'V') {
                            $idepropenviar = $numIdePropietario;
                        } else {
                            $idepropenviar = str_pad(trim($numIdePropietario), 14, "0", STR_PAD_LEFT);
                        }
                        $arrDatosPropietarios[$key - 1]['numero_identificacion_propietario'] = $idepropenviar;
                        if (trim($NitPropietario) != '') {
                            $arrDatosPropietarios[$key - 1]['nit_propietario'] = $NitPropietario;
                            $arrDatosPropietarios[$key - 1]['digito_verificacion_propietario'] = $dvPropietario;
                        }
                        $arrDatosPropietarios[$key - 1]['codigo_camara_propietario'] = trim($value['camarapropietario']);
                        $arrDatosPropietarios[$key - 1]['matricula_propietario'] = homologacionMatriculaRUESApi($value['matriculapropietario']);
                        if (trim($arrDatosPropietarios[$key - 1]['matricula_propietario']) == '') {
                            $arrDatosPropietarios[$key - 1]['razon_social_propietario'] = trim($value['nombrepropietario']);
                        }
                    }
                }
            }
        }

        /*
         * Parámetros Propietarios de Sucursales y agencias  
         */
        if ($datos['categoria'] == '2' || $datos['categoria'] == '3') {
            $sepide = \funcionesGenerales::separarDv($datos["cpnumnit"]);
            if ($sepide["dv"] == substr($datos["cpnumnit"], -1, 1)) {
                $tipoIdePropietario = '2';
                $numIdePropietario = $sepide["identificacion"];
                $NitPropietario = $sepide["identificacion"];
                $dvPropietario = $sepide["dv"];
            } else {
                $tipoIdePropietario = '2';
                $numIdePropietario = $datos["cpnumnit"];
                $NitPropietario = $datos["cpnumnit"];
                $dvPropietario = \funcionesGenerales::calcularDv($datos["cpnumnit"]);
            }

            $arrDatosPropietarios[0]['codigo_clase_identificacion_propietario'] = homologacionTipoIdentificacionRUESApi('2');
            $arrDatosPropietarios[0]['numero_identificacion_propietario'] = str_pad(trim($numIdePropietario), 14, "0", STR_PAD_LEFT);
            if (trim($NitPropietario) != '') {
                $arrDatosPropietarios[0]['nit_propietario'] = $NitPropietario;
                $arrDatosPropietarios[0]['digito_verificacion_propietario'] = $dvPropietario;
            }
            $arrDatosPropietarios[0]['codigo_camara_propietario'] = trim($datos['cpcodcam']);
            $arrDatosPropietarios[0]['matricula_propietario'] = homologacionMatriculaRUESApi($datos['cpnummat']);
            if (trim($arrDatosPropietarios[0]['matricula_propietario']) == '') {
                $arrDatosPropietarios[0]['razon_social_propietario'] = trim($value['nombrepropietario']);
            }
        }


        /*
         * Parámetros Vinculos 
         */
        if (isset($datos["vinculos"])) {
            if (count($datos["vinculos"]) > 0) {
                $arrVinculos = (array) $datos["vinculos"];
                foreach ($arrVinculos as $key => $value) {
                    $arrDatosVinculos[$key - 1]['tipo_identificacion'] = homologacionTipoIdentificacionRUESApi($value['idtipoidentificacionotros']);
                    if ($value['idtipoidentificacionotros'] == 'V') {
                        $idevinenviar = $value['identificacionotros'];
                    } else {
                        $idevinenviar = str_pad(trim($value['identificacionotros']), 14, "0", STR_PAD_LEFT);
                    }
                    $arrDatosVinculos[$key - 1]['numero_identificacion'] = $idevinenviar;
                    $arrDatosVinculos[$key - 1]['nombre'] = $value["nombreotros"];
                    $arrDatosVinculos[$key - 1]['nombre1'] = $value["nombre1otros"];
                    $arrDatosVinculos[$key - 1]['nombre2'] = $value["nombre2otros"];
                    $arrDatosVinculos[$key - 1]['apellido1'] = $value["apellido1otros"];
                    $arrDatosVinculos[$key - 1]['apellido2'] = $value["apellido2otros"];
                    $arrDatosVinculos[$key - 1]['detalle_vinculos'][0] = array('codigo_tipo_vinculo' => homologacionCodigoVinculoRUESApi($datos["organizacion"], $value['vinculootros']));
                }
            }
        }

        /*
         * Parámetros Histórico de Pagos (últimos 5 años)
         */


        $servsmat = array();
        $servsren = array();
        $servsafi = array();
        $servsben = array();
        $consulta = "";
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "tipoingreso IN ('02','03','13','31','85')", "idservicio");
        foreach ($temx as $tx) {
            if ($tx["tipoingreso"] == '02') {
                $servsmat[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '03' || $tx["tipoingreso"] == '13') {
                $servsren[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '31') {
                $servsafi[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '85') {
                $servsben[$tx["idservicio"]] = $tx["idservicio"];
            }

            if ($consulta != '') {
                $consulta .= ",";
            }
            $consulta .= "'" . $tx["idservicio"] . "'";
        }

        $res = retornarRegistrosMysqliApi($mysqli, "mreg_est_recibos", "matricula='" . $datos["matricula"] . "' and servicio IN (" . $consulta . ")", "fecoperacion desc");
        $valorPagadoMatricula = '';
        $valorPagadoRenovacion = '';

        if ($res && !empty($res)) {
            $sech = 0;

            foreach ($res as $rs) {
                $incluir = 'no';
                if ($rs["ctranulacion"] != '1' && $rs["ctranulacion"] != '2') {
                    if (substr($rs["numerorecibo"], 0, 1) == 'R' || substr($rs["numerorecibo"], 0, 1) == 'S') {
                        $incluir = 'si';
                    }
                }

                //
                if ($incluir == 'si') {
                    if ($rs["valor"] == 0) {
                        $incluir = 'no';
                    }
                }

                if ($incluir == 'si') {
                    $ctp = '';
                    $arp = '';
                    if (isset($servsmat[$rs["servicio"]])) {
                        $ctp = '01';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                        if ($valorPagadoMatricula == '') {
                            $valorPagadoMatricula = $rs["valor"];
                        }
                    }
                    if (isset($servsren[$rs["servicio"]])) {
                        if ($rs["fecharenovacionaplicable"] != '') {
                            $fren = $rs["fecharenovacionaplicable"];
                        } else {
                            $fren = $rs["fecoperacion"];
                        }
                        $ctp = '02';
                        $arp = $rs["anorenovacion"];
                        if ($rs["servicio"] == "00000510") {
                            $arp = substr($fren, 0, 4);
                        }
                        if ($rs["servicio"] == "00000710") {
                            $arp = '';
                        }
                        if ($valorPagadoRenovacion == '') {
                            $valorPagadoRenovacion = $rs["valor"];
                        }
                    }
                    if (isset($servsafi[$rs["servicio"]])) {
                        $ctp = '03';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                    }
                    if (isset($servsben[$rs["servicio"]])) {
                        $ctp = '04';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                    }

                    //
                    if ($ctp != '') {
                        if ($rs["fecharenovacionaplicable"] != '') {
                            $fren = $rs["fecharenovacionaplicable"];
                        } else {
                            $fren = $rs["fecoperacion"];
                        }
                        $arrDatosHistoricoPagos[$sech]['codigo_tipo_pago'] = $ctp;
                        $arrDatosHistoricoPagos[$sech]['ano'] = $arp;
                        $arrDatosHistoricoPagos[$sech]['fecha'] = $fren;
                        $arrDatosHistoricoPagos[$sech]['valor_base'] = $rs["base"];
                        $arrDatosHistoricoPagos[$sech]['valor_pagado'] = $rs["valor"];
                        $sech++;
                    }
                }
            }
        }



        /*
         * Validaciones Grupo NIIF
         */
        $indicadorGrupoNiif = '';
        if (trim($datos["gruponiif"]) == '') {
            $indicadorGrupoNiif = '0';
        } else {
            $indicadorGrupoNiif = \funcionesGenerales::retornarGrupoNiifFormulario($mysqli, $datos["gruponiif"]);
        }


        /*
         * Validaciones Campo Objeto Social
         */
        if (isset($datos["crtsii"]["0740"])) {
            $objetoSocial = strip_tags($datos["crtsii"]["0740"]);
        } else {
            if (isset($datos["crt"]["0740"])) {
                $objetoSocial = strip_tags($datos["crt"]["0740"]);
            } else {
                $objetoSocial = '';
            }
        }

        /*
         * Validaciones Campo Juegos Suerte Azar (CIIU=R9200) 
         */
        $indicadorJuegosSuerteAzar = 'N';
        if (isset($datos["ciius"])) {
            if (in_array("R9200", $datos["ciius"])) {
                $indicadorJuegosSuerteAzar = 'S';
            }
        }

        /*
         * Validaciones Campo Transporte Carga
         */
        $indicadorTransporteCarga = 'N';
        if (isset($datos["inscripciones"])) {
            foreach ($datos["inscripciones"] as $key => $value) {
                if ($value['acto'] == '0800' || $value['acto'] == '0801') {
                    $indicadorTransporteCarga = 'S';
                }
            }
        }

        /*
         * Validaciones Campo Facultades 
         * Obtiene de tabla mreg_certificas_sii los certificas 1300 y 1500
         */
        $facultades = '';
        if (isset($datos["crtsii"]["1300"])) {
            $facultades = $datos["crtsii"]["1300"];
            if (isset($datos["crtsii"]["1500"])) {
                $facultades .= " | " . $datos["crtsii"]["1500"];
            }
        } else {
            if (isset($datos["crt"]["1300"])) {
                $facultades = $datos["crt"]["1300"];
                if (isset($datos["crt"]["1500"])) {
                    $facultades .= " | " . $datos["crt"]["1500"];
                }
            }
        }


        /*
         * Validaciones Zona Notificación Comercial
         */
        $indicadorZonaNotificacionComercial = '';
        if ($datos["codigozonacom"] == 'U') {
            $indicadorZonaNotificacionComercial = 1; //URBANA
        }
        if ($datos["codigozonacom"] == 'R') {
            $indicadorZonaNotificacionComercial = 2; //RURAL
        }

        /*
         * Validaciones Zona Notificación Judicial
         */
        $indicadorZonaNotificacionJudicial = '';
        if ($datos["codigozonanot"] == 'U') {
            $indicadorZonaNotificacionJudicial = 1; //URBANA
        }
        if ($datos["codigozonanot"] == 'R') {
            $indicadorZonaNotificacionJudicial = 2; //RURAL
        }

        /*
         * Validaciones Notificación Email
         */
        $valorCtrmennot = substr($datos["ctrmennot"], 0, 1);
        $indicadorNotificacionEmail = \funcionesGenerales::homologarBoleeano($valorCtrmennot);

        /*
         * Validaciones Etnia
         */
        $cualEtnia = '';
        $indicadorEtnia = \funcionesGenerales::homologarBoleeano($datos["ctresaetnia"]);
        if ($indicadorEtnia == 'S') {
            $cualEtnia = $datos["ctresacualetnia"];
        }

        /*
         * Validaciones Reinsertado
         */

        $cualReinsertado = '';
        $indicadorReinsertado = \funcionesGenerales::homologarBoleeano($datos["ctresadespvictreins"]);
        if ($indicadorReinsertado == 'S') {
            $cualReinsertado = $datos["ctresacualdespvictreins"];
        }

        /*
         * Validaciones Tipo Propiedad - Establecimientos
         */
        $indicadorTipoPropiedad = '';
        if ($datos['organizacion'] == '02') {
            if ($datos["tipolocal"] == '1') {
                $indicadorTipoPropiedad = 1; //PROPIO
            }
            if ($datos["tipolocal"] == '0') {
                $indicadorTipoPropiedad = 2; //AJENO
            }
        }


        /*
         * Parámetros Finales consumo 
         */

        $parametros = array();
        $parametros['codigo_error'] = '';
        $parametros['mensaje_error'] = '';
        $parametros['numero_interno'] = '0';
        $parametros['usuario'] = $_SESSION["generales"]["codigousuario"];
        $parametros['codigo_camara'] = $_SESSION["generales"]["codigoempresa"];
        $parametros['matricula'] = homologacionMatriculaRUESApi($datos["matricula"]);
        $parametros['inscripcion_proponente'] = str_pad($datos["proponente"], 12, "0", STR_PAD_LEFT);
        $parametros['razon_social'] = $datos["nombrerues"];
        if ($datos["organizacion"] == '01') {
            $parametros['primer_apellido'] = $datos["ape1"];
            $parametros['segundo_apellido'] = $datos["ape2"];
            $parametros['primer_nombre'] = $datos["nom1"];
            $parametros['segundo_nombre'] = $datos["nom2"];
        }
        $parametros['sigla'] = $datos["sigla"];
        if ($datos["organizacion"] == '01') {
            if ($datos["sexo"] != '') {
                $parametros['genero'] = $datos["sexo"];
            }
            if ($datos["etnia"] != '') {
                $parametros['etnia'] = $datos["etnia"];
            }
        }
        $parametros['codigo_clase_identificacion'] = homologacionTipoIdentificacionRUESApi($tipoIdentificacion);
        if ($tipoIdentificacion == '09') {
            if (strlen($numIdentificacion) > 14) {
                $ideenviar = $numIdentificacion;
            } else {
                $ideenviar = str_pad($numIdentificacion, 14, "0", STR_PAD_LEFT);
            }
        } else {
            $ideenviar = str_pad($numIdentificacion, 14, "0", STR_PAD_LEFT);
        }
        $parametros['numero_identificacion'] = $ideenviar;
        $parametros['nit'] = $Nit;
        $parametros['digito_verificacion'] = $dv;
        $parametros['fecha_expedicion'] = "";
        if ($datos["organizacion"] == '01') {
            $parametros['fecha_expedicion'] = $datos["fecexpdoc"];
            $parametros['lugar_expedicion'] = $datos["idmunidoc"];
            $parametros['pais_expedicion'] = $datos["paisexpdoc"];
        }
        if (ltrim($tipoIdentificacion, "0") == '3' || ltrim($tipoIdentificacion, "0") == '5') {
            $parametros['num_id_trib_ep'] = $datos["idetripaiori"];
            $parametros['pais_origen'] = $datos["paiori"];
            $parametros['num_id_trib_ep'] = $datos["idetriextep"];
        }

        $parametros['direccion_comercial'] = $datos["dircom"];
        $parametros['codigo_ubicacion_empresa'] = $datos["ctrubi"];
        $parametros['codigo_zona_comercial'] = $indicadorZonaNotificacionComercial; //1 o 2
        $parametros['codigo_postal_comercial'] = $datos["codigopostalcom"];
        $parametros['municipio_comercial'] = empty($datos["muncom"]) ? '99999' : $datos["muncom"];
        //$parametros['barrio_comercial'] = retornarNombreBarrioMysqliApi($mysqli, $datos["muncom"], $datos["barriocom"]);
        $parametros['telefono_comercial_1'] = $datos["telcom1"];
        $parametros['telefono_comercial_2'] = $datos["telcom2"];
        $parametros['telefono_comercial_3'] = $datos["celcom"];
        $parametros['correo_electronico_comercial'] = $datos["emailcom"];
        $parametros['direccion_fiscal'] = $datos["dirnot"];
        $parametros['codigo_zona_fiscal'] = $indicadorZonaNotificacionJudicial; //1 o 2
        $parametros['codigo_postal_fiscal'] = $datos["codigopostalnot"];
        $parametros['municipio_fiscal'] = empty($datos["munnot"]) ? '99999' : $datos["munnot"];
        //$parametros['barrio_fiscal'] = retornarNombreBarrioMysqliApi($mysqli, $datos["munnot"], $datos["barrionot"]);
        $parametros['telefono_fiscal_1'] = $datos["telnot"];
        $parametros['telefono_fiscal_2'] = $datos["telnot2"];
        $parametros['telefono_fiscal_3'] = $datos["celnot"];
        $parametros['correo_electronico_fiscal'] = $datos["emailnot"];
        if ($datos["tiposedeadm"] == '0') {
            $datos["tiposedeadm"] = '';
        }
        $parametros['codigo_sede_administrativa'] = $datos["tiposedeadm"];
        $parametros['autorizacion_envio_correo_electronico'] = $indicadorNotificacionEmail; //S o N
        $parametros['objeto_social'] = base64_encode($objetoSocial);
        $parametros['cod_ciiu_act_econ_pri'] = empty($datos["ciius"][1]) ? '9999' : substr($datos["ciius"][1], 1);
        $parametros['cod_ciiu_act_econ_sec'] = empty($datos["ciius"][2]) ? '' : substr($datos["ciius"][2], 1);
        $parametros['fecha_inicio_act_econ_pri'] = $datos["feciniact1"]; //YYYYMMDD
        $parametros['fecha_inicio_act_econ_sec'] = $datos["feciniact2"]; //YYYYMMDD
        $parametros['ciiu3'] = empty($datos["ciius"][3]) ? '' : substr($datos["ciius"][3], 1);
        $parametros['ciiu4'] = empty($datos["ciius"][4]) ? '' : substr($datos["ciius"][4], 1);
        $parametros['ciiu_mayores_ingresos'] = substr($datos["ciiutamanoempresarial"], 1);
        $parametros['clasificacion_imp_exp'] = $indicadorImpExp;
        $parametros['empresa_familiar'] = \funcionesGenerales::homologarBoleeano($datos["empresafamiliar"]);
        $parametros['proceso_innovacion'] = \funcionesGenerales::homologarBoleeano($datos["procesosinnovacion"]);
        $parametros['fecha_matricula'] = $fechaMatricula; //YYYMMDD
        $parametros['fecha_constitucion'] = $fechaConstitucion; //YYYMMDD
        $parametros['fecha_renovacion'] = $fechaRenovacion; //YYYMMDD
        $parametros['ultimo_ano_renovado'] = $ultAnoRenovado; //YYYMMDD
        $parametros['valor_pagado_renovacion'] = $valorPagadoRenovacion;
        $parametros['valor_pagado_matricula'] = $valorPagadoMatricula;
        $parametros['fecha_vigencia'] = $fechaVigencia; //YYYMMDD
        $parametros['fecha_cancelacion'] = $fechaCancelacion; //YYYMMDD
        $parametros['codigo_motivo_cancelacion'] = homologacionMotivoCancelacionRUESApi($indicadorMotivoCancelacion); //HOMOLOGACION BAS_MOTIVOS_CANCELACION
        $parametros['codigo_tipo_sociedad'] = homologacionSociedadRUESApi($datos['organizacion'], $datos['claseespesadl'], $datos["ciius"]); //HOMOLOGACION BAS_TIPO_SOCIEDAD
        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
            $parametros['codigo_organizacion_juridica'] = homologacionOrganizacionEsadlRUESApi($mysqli, $datos['claseespesadl']);
        } else {
            $parametros['codigo_organizacion_juridica'] = homologacionOrganizacionRUESApi($datos['organizacion'], $datos['clasegenesadl'], $datos['claseespesadl'], $datos['claseeconsoli']);
        }
        $parametros['codigo_categoria_matricula'] = homologacionCategoriaRUESApi($datos['organizacion'], $datos['categoria']); //HOMOLOGACION BAS_CATEGORIA_MATRICULA
        $parametros['indicador_vendedor_juegos_suerte_azar'] = $indicadorJuegosSuerteAzar;
        $parametros['indicador_transporte_de_carga'] = $indicadorTransporteCarga;
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
            if (($datos["ctrbic"] != '') && ($datos["ctrbic"] == 'S')) {
                $parametros['indicador_empresa_bic'] = "1";
            }
        }

        //2023-01-12 JINT: reporte emprendimiento social 
        $parametros['indicador_emprendimiento_social'] = $datos["emprendimientosocial"]; // S o N o vacio
        //
        $parametros['afiliado'] = $indicadorAfiliado;
        $parametros['url'] = filter_var($datos["urlcom"], FILTER_VALIDATE_URL) ? $datos["urlcom"] : '';
        $parametros['codigo_estado_matricula'] = homologacionEstadoMatriculaRUESApi($datos["estadomatricula"]); //HOMOLOGACION BAS_ESTADO_MATRICULA
        $parametros['codigo_estado_persona_juridica'] = $datos["estadocapturado"];
        $parametros['empleados'] = empty($datos["personal"]) ? '0' : $datos["personal"];
        $parametros['porcentaje_empleados_temporales'] = $datos["personaltemp"];
        $parametros['porcentaje_participacion_mujeres_capital'] = $datos["participacionmujeres"];

        //
        if (
                $datos["organizacion"] == '01' ||
                ($datos["organizacion"] > '02' && $datos["categoria"] == '1')
        ) {
            if ($datos["cantidadmujeres"] != '') {
                $parametros['cantidad_mujeres_empleadas'] = $datos["cantidadmujeres"];
            }
        }

        //
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            if ($datos["cantidadmujerescargosdirectivos"] != '') {
                $parametros['cantidad_mujeres_cargos_directivos'] = $datos["cantidadmujerescargosdirectivos"];
            }
        }

        //
        $parametros['codigo_tamano_empresa'] = str_pad($datos["tamanoempresarial957codigo"], 2, "0", STR_PAD_LEFT); //HOMOLOGACION BAS_TAMANO_EMPRESA 
        $parametros['codigo_estado_liquidacion'] = homologacionEstadoLiquidacionRUESApi($datos["estadotipoliquidacion"]); //REVISAR HOMOLOGACION BAS_CODIGOS_LIQUIDACION
        $parametros['latitud'] = $datos['latitud'];
        $parametros['longitud'] = $datos['longitud'];
        $parametros['informacion_financiera'] = $arrDatosInfoFinanciera;
        $parametros['informacion_capitales'] = $arrDatosInfoCapitales;
        $parametros['grupo_niif'] = $indicadorGrupoNiif; // 0 - 7
        $parametros['codigo_partidas_conciliatorias'] = $datos["niifconciliacion"];
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            if ($datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
                $parametros['capital_social_nacional_publico'] = $datos["cap_porcnalpub"];
                $parametros['capital_social_nacional_privado'] = $datos["cap_porcnalpri"];
                $parametros['capital_social_extranjero_publico'] = $datos["cap_porcextpub"];
                $parametros['capital_social_extranjero_privado'] = $datos["cap_porcextpri"];
            }
        }
        $parametros['indicador_beneficio_ley_1429'] = \funcionesGenerales::homologarBoleeano($datos["art7"]);
        $parametros['indicador_beneficio_ley_1780'] = \funcionesGenerales::homologarBoleeano($datos["benley1780"]);
        $parametros['indicador_beneficio_ley1780'] = \funcionesGenerales::homologarBoleeano($datos["benley1780"]);
        $parametros['indicador_aportante_seguridad_social'] = $datos["aportantesegsocial"];
        if ($datos["aportantesegsocial"] == 'S') {
            $parametros['tipo_aportante_seguridad_social'] = $datos["tipoaportantesegsocial"]; //REVISAR HOMOLOGACION BAS_TIPO_APORTANTE
        }
        $parametros['informacion_adicional'] = $arrDatosInfoAdicional;
        $parametros['cantidad_establecimientos'] = $CantEstablecimientos;
        if ($datos["organizacion"] == '02') {
            $parametros['tipo_propietario'] = homologacionTipoPropietarioRUESApi($datos["tipopropiedad"]);
            $parametros['codigo_tipo_local'] = $indicadorTipoPropiedad;
        }
        $parametros['grupo_empresarial_tipo'] = $datos["tipogruemp"];
        $parametros['grupo_empresarial_nombre'] = $datos["nombregruemp"];
        $parametros['datos_propietarios'] = $arrDatosPropietarios;
        $parametros['facultades'] = base64_encode($facultades);
        $parametros['vinculos'] = $arrDatosVinculos;
        //Solo ESADL
        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
            $parametros['esal_numero_asociados'] = $datos["ctresacntasociados"];
            $parametros['esal_numero_mujeres'] = $datos["ctresacntmujeres"];
            $parametros['esal_numero_hombres'] = $datos["ctresacnthombres"];
            $parametros['esal_indicador_pertenencia_gremio'] = \funcionesGenerales::homologarBoleeano($datos["ctresapertgremio"]);
            $parametros['esal_nombre_gremio'] = $datos["ctresagremio"];
            $parametros['esal_entidad_acreditada'] = $datos["ctresaacredita"];
            $parametros['esal_entidad_ivc'] = $datos["ctresaivc"];
            $parametros['esal_ha_remitido_info_ivc'] = \funcionesGenerales::homologarBoleeano($datos["ctresainfoivc"]);
            $parametros['esal_autorizacion_registro'] = \funcionesGenerales::homologarBoleeano($datos["ctresaautregistro"]);
            $parametros['esal_entidad_autoriza'] = $datos["ctresaentautoriza"];
            $parametros['esal_codigo_naturaleza'] = homologacionNaturalezaEsadlRUESApi($datos['organizacion'], $datos["ctresacodnat"]);
            $parametros['esal_codigo_tipo_entidad'] = homologacionOrganizacionRUESApi($datos['organizacion'], $datos['clasegenesadl'], $datos['claseespesadl'], $datos['claseeconsoli']);
            $parametros['esal_ed_discapacidad'] = $datos["ctresadiscap"];
            $parametros['esal_ed_etnia'] = $indicadorEtnia;
            $parametros['esal_ed_etnia_cual'] = $cualEtnia;
            $parametros['esal_ed_lgbti'] = $datos["ctresalgbti"];
            $parametros['esal_ed_desp_vict_reins'] = $indicadorReinsertado;
            $parametros['esal_ed_desp_vict_reins_cual'] = $cualReinsertado;
            $parametros['esal_indicador_gestion'] = \funcionesGenerales::homologarBoleeano($datos["ctresaindgest"]);
        }
        $parametros['historico_pagos'] = $arrDatosHistoricoPagos;

        /* Eliminar campos vacíos de los parámetros */


        foreach ($parametros as $claveRaiz => $valorRaiz) {

            if (!is_array($valorRaiz)) {
                if (trim((string) $valorRaiz) == '') {
                    if ($claveRaiz != 'codigo_error' && $claveRaiz != 'mensaje_error') {
                        unset($parametros[$claveRaiz]);
                    }
                }
            } else {
                foreach ($valorRaiz as $clave1 => $valor1) {
                    foreach ($valor1 as $clave2 => $valor2) {
                        if (!is_array($valor2)) {
                            if (trim((string) $valor2) == '') {
                                unset($parametros[$claveRaiz][$clave1][$clave2]);
                            }
                        }
                    }
                }
            }
        }


        unset($arrDatosInfoFinanciera);
        unset($arrDatosInfoCapitales);
        unset($arrDatosInfoAdicional);
        unset($arrDatosPropietarios);
        unset($arrDatosVinculos);
        unset($arrDatosHistoricoPagos);

        /*
          $retorno["hashcontrolnuevo"] = date("Ymd") . '|' . md5(json_encode($retorno));
          $retorno["hashcontrol"] = $reg["hashcontrol"];
         */

        if (isset($datos["hashcontrolnuevo"])) {
            $sep = explode("|", $datos["hashcontrolnuevo"]);
        } else {
            $sep[1] = '';
        }

        if ($renovarhash) {
            $sep[1] = 'WS' . date('His');
        }


        $parametros['hash_control'] = empty($sep[1]) ? '' : $sep[1];
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"], 29, "0", STR_PAD_RIGHT);

        if ($cerrarMysqli == 'si') {
            $mysqli->close();
        }

        //
        // $sal = json_encode($parametros, JSON_PRETTY_PRINT);
        // \logApi::general2('construirParametrosRegMer_' . date("Ymd"), $datos["matricula"], $sal);
        return $parametros;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $matricula
     * @param type $organizacion
     * @param type $categoria
     * @param type $razonsocial
     * @param type $exp
     * @param type $resAPI
     * @param type $matP
     * @return type
     */
    public static function construirExpedienteVUEV2($mysqli = null, $matricula = '', $organizacion = '', $categoria = '', $razonsocial = '', $exp = array(), $resAPI = array(), $matP = '') {

        //
        $cerrarMysqli = 'no';
        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysqli = 'si';
        }

        //
        $ctce = array();
        $ctce["tramite"] = array();
        $ctce["anexo5"] = array();
        $ctce["matriculapn"] = array();
        $ctce["ubicacion"] = array();
        $ctce["organizacion"] = array();
        $ctce["actividades"] = array();
        $ctce["financieros"] = array();
        $ctce["establecimientos"] = array();
        $ctce["documentoConstitucion"] = array();
        $ctce["responsabilidades"] = array();
        $ctce["representantes"] = array();
        $ctce["socios"] = array();
        $ctce["textos"] = array();
        $ctce["certificas"] = array();
        $ctce["expediente"] = array();
        $ctce["dataf"] = array();
        $ctce["tresponseactual"] = '';
        $ctce["tresponsaimportar"] = '';

        //
        if (isset($resAPI["data"]['Tramite']))
            $ctce["tramite"] = $resAPI["data"]["Tramite"];
        if (isset($resAPI["data"]["Organizacion"]))
            $ctce["organizacion"] = $resAPI["data"]["Organizacion"];
        if (isset($resAPI["data"]["MatriculaPN"]))
            $ctce["matriculapn"] = $resAPI["data"]["MatriculaPN"];
        if (isset($resAPI["data"]["Anexo5"]))
            $ctce["anexo5"] = $resAPI["data"]["Anexo5"];
        if (isset($resAPI["data"]["Ubicacion"]))
            $ctce["ubicacion"] = $resAPI["data"]["Ubicacion"];
        if (isset($resAPI["data"]["Actividades"]))
            $ctce["actividades"] = $resAPI["data"]["Actividades"];
        if (isset($resAPI["data"]["Financieros"]))
            $ctce["financieros"] = $resAPI["data"]["Financieros"];
        if (isset($resAPI["data"]["Establecimientos"]))
            $ctce["establecimientos"] = $resAPI["data"]["Establecimientos"];
        if (isset($resAPI["data"]["DocumentoConstitucion"]))
            $ctce["documentoConstitucion"] = $resAPI["data"]["DocumentoConstitucion"];
        if (isset($resAPI["data"]["ResponsabilidadesTributarias"]))
            $ctce["responsabilidades"] = $resAPI["data"]["ResponsabilidadesTributarias"];
        if (isset($resAPI["data"]["Representantes"]))
            $ctce["representantes"] = $resAPI["data"]["Representantes"];
        if (isset($resAPI["data"]["Socios"]))
            $ctce["socios"] = $resAPI["data"]["Socios"];
        if (isset($resAPI["data"]["DocumentoConstitucion"]["oArticulos"]))
            $ctce["textos"] = $resAPI["data"]["DocumentoConstitucion"]["oArticulos"];

        //
        if (empty($exp)) {
            $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $matricula);
            $expnue = $exp;
        } else {
            $expnue = $exp;
        }

        // En caso de personas jurídicas principales o personas naturales
        if ($organizacion == '01' || ($organizacion > '01' && $categoria == '1')) {
            // Homologa organizaciones y categorias dependiendo del tipo y subtipo de trámite
            switch ($ctce["tramite"]["Tipo"]) {
                case "1" :
                    $expnue["organizacion"] = '01';
                    $expnue["categoria"] = '0';
                    $expnue["estadomatricula"] = 'MA';
                    break;
                case "2" :
                    $expnue["organizacion"] = '16';
                    $expnue["categoria"] = '1';
                    $expnue["estadomatricula"] = 'MA';
                    break;
                case "3" :
                case "4" :
                    $expnue["organizacion"] = '02';
                    $expnue["categoria"] = '0';
                    $expnue["estadomatricula"] = 'MA';
                    break;
                case "5" :
                    $expnue["organizacion"] = '03';
                    $expnue["categoria"] = '1';
                    $expnue["estadomatricula"] = 'MA';
                    break;
                case "6" :
                    $expnue["organizacion"] = '06';
                    $expnue["categoria"] = '1';
                    $expnue["estadomatricula"] = 'MA';
                    break;
                case "7" :
                    $expnue["organizacion"] = '07';
                    $expnue["categoria"] = '1';
                    $expnue["estadomatricula"] = 'MA';
                    break;
                case "8" :
                    $expnue["organizacion"] = '04';
                    $expnue["categoria"] = '1';
                    $expnue["estadomatricula"] = 'MA';
                    break;
                case "9" :
                    $expnue["estadomatricula"] = 'IA';
                    switch ($ctce["tramite"]["SubTipo"]) {
                        case "1" : // ESADL
                            $expnue["organizacion"] = '12';
                            $expnue["categoria"] = '1';
                            $expnue["clasegenesadl"] = '';
                            $expnue["claseespesadl"] = '';
                            break;
                        case "2" : // EconSoili
                            $expnue["organizacion"] = '14';
                            $expnue["categoria"] = '1';
                            $expnue["clasegenesadl"] = '';
                            $ctce["ClaseEspeEsadl"] = '';
                            break;
                        case "3" : // Veedurías
                            $expnue["organizacion"] = '12';
                            $expnue["categoria"] = '1';
                            $expnue["clasegenesadl"] = '8';
                            $expnue["claseespesadl"] = '72';
                            break;
                        case "4" : // ONGs extranjeras
                            $expnue["organizacion"] = '12';
                            $expnue["categoria"] = '1';
                            $expnue["clasegenesadl"] = '7';
                            $expnue["claseespesadl"] = '61';
                            break;
                    }
            }

            //
            if ($ctce["ubicacion"]["AutorizaEnvioEmail"] == '0') {
                $expnue["ctrmennot"] = 'N';
            } else {
                if ($ctce["ubicacion"]["AutorizaEnvioEmail"] == '1') {
                    $expnue["ctrmennot"] = 'S';
                }
            }

            //
            $expnue["nombre"] = $ctce["tramite"]["Nombre"];
            if ($expnue["organizacion"] > '02' && $expnue["categoria"] == '1') {
                if (isset($ctce["tramite"]["Sigla"]))
                    $expnue["sigla"] = $ctce["tramite"]["Sigla"];
            }

            //
            if ($expnue["organizacion"] == '01') {
                $expnue["ape1"] = $ctce["matriculapn"]["Apellido1"];
                $expnue["ape2"] = $ctce["matriculapn"]["Apellido2"];
                $expnue["nom1"] = $ctce["matriculapn"]["Nombre1"];
                $expnue["nom2"] = $ctce["matriculapn"]["Nombre2"];
                $expnue["fechanacimiento"] = substr($ctce["matriculapn"]["FechaNacimiento"], 6, 4) . substr($ctce["matriculapn"]["FechaNacimiento"], 3, 2) . substr($ctce["matriculapn"]["FechaNacimiento"], 0, 2);
                $expnue["fecexpdoc"] = substr($ctce["matriculapn"]["FechaExpedicion"], 6, 4) . substr($ctce["matriculapn"]["FechaExpedicion"], 3, 2) . substr($ctce["matriculapn"]["FechaExpedicion"], 0, 2);
                if ($ctce["matriculapn"]["IdMunicipioExpedicionDocumento"] == '11000') {
                    $ctce["matriculapn"]["IdMunicipioExpedicionDocumento"] = '11001';
                }
                $expnue["idmunidoc"] = $ctce["matriculapn"]["IdMunicipioExpedicionDocumento"];
                if ($ctce["matriculapn"]["PaisDocumento"] == 'CO') {
                    $expnue["paisexpdoc"] = '169';
                    $expnue["nacionalidad"] = 'COLOMBIANO/A';
                } else {
                    $expnue["paisexpdoc"] = $ctce["matriculapn"]["PaisDocumento"];
                }
                $expnue["tipoidentificacion"] = $ctce["matriculapn"]["IdTipoDocumento"];
                $expnue["identificacion"] = $ctce["matriculapn"]["NumeroIdentificacion"];
                $expnue["nit"] = trim((string) $ctce["matriculapn"]["Nit"] . $ctce["matriculapn"]["Dv"]);
            }

            //
            $expnue["ctrubi"] = $ctce["ubicacion"]["IdTipoUbicacionEmpresa"];
            $expnue["dircom"] = $ctce["ubicacion"]["DireccionPrincipal"];
            if ($ctce["ubicacion"]["CodigoSIIPrincipal"] != '') {
                $expnue["barriocom"] = sprintf("%05s", $ctce["ubicacion"]["CodigoSIIPrincipal"]);
            }
            if ($ctce["ubicacion"]["CodigoSIIPrincipal"] == '0') {
                $ctce["ubicacion"]["CodigoSIIPrincipal"] = '';
            }
            $expnue["barriocom"] = $ctce["ubicacion"]["CodigoSIIPrincipal"];
            $expnue["barriocomnombre"] = $ctce["ubicacion"]["BarrioPrincipal"];
            $expnue["codigozonacom"] = '';
            if ($ctce["ubicacion"]["IdTipoZonaPrincipal"] == '2') {
                $expnue["codigozonacom"] = 'U';
            }
            if ($ctce["ubicacion"]["IdTipoZonaPrincipal"] == '3') {
                $expnue["codigozonacom"] = 'R';
            }
            $expnue["muncom"] = $ctce["ubicacion"]["IdMunicipioPrincipal"];
            $expnue["paicom"] = '169';
            $expnue["emailcom"] = $ctce["ubicacion"]["EmailPrincipal"];
            $expnue["codigopostalcom"] = $ctce["ubicacion"]["ZonaPostalPrincipal"];

            $expnue["dirnot"] = $ctce["ubicacion"]["DireccionNotificacion"];
            $expnue["munnot"] = $ctce["ubicacion"]["IdMunicipioNotificacion"];
            $expnue["emailnot"] = $ctce["ubicacion"]["EmailNotificacion"];
            if ($ctce["ubicacion"]["CodigoSIINotificacion"] == '0') {
                $ctce["ubicacion"]["CodigoSIINotificacion"] = '';
            }
            
            $expnue["barrionot"] = $ctce["ubicacion"]["CodigoSIINotificacion"];
            $expnue["barrionotnombre"] = $ctce["ubicacion"]["BarrioNotificacion"];
            $expnue["telcom1"] = $ctce["ubicacion"]["TelefonoPrincipal1"];
            $expnue["telcom2"] = $ctce["ubicacion"]["TelefonoPrincipal2"];
            $expnue["celcom"] = $ctce["ubicacion"]["TelefonoPrincipal3"];
            $expnue["telnot"] = $ctce["ubicacion"]["TelefonoNotificacion1"];
            $expnue["telnot2"] = $ctce["ubicacion"]["TelefonoNotificacion2"];
            $expnue["celnot"] = $ctce["ubicacion"]["TelefonoNotificacion3"];
            $expnue["tiposedeadm"] = $ctce["ubicacion"]["IdTipoSedeAdministrativa"];
            // $expnue["codigozonapostal"] = $ctce["ubicacion"]["ZonaPostalNotificacion"];
            $expnue["painot"] = '169';
            if ($ctce["ubicacion"]["IdTipoZonaNotificacion"] == '2') {
                $expnue["codigozonanot"] = 'U';
            }
            if ($ctce["ubicacion"]["IdTipoZonaNotificacion"] == '3') {
                $expnue["codigozonanot"] = 'R';
            }
            $expnue["codigopostalnot"] = $ctce["ubicacion"]["ZonaPostalNotificacion"];

            //
            if ($expnue["organizacion"] > '02' && $expnue["categoria"] == '1') {
                $expnue["fechaconstitucion"] = substr($ctce["organizacion"]["sFechaConstitucionDesde"], 6, 4) . substr($ctce["organizacion"]["sFechaConstitucionDesde"], 3, 2) . substr($ctce["organizacion"]["sFechaConstitucionDesde"], 0, 2);
                $expnue["fechavencimiento"] = substr($ctce["organizacion"]["sFechaConstitucionHasta"], 6, 4) . substr($ctce["organizacion"]["sFechaConstitucionHasta"], 3, 2) . substr($ctce["organizacion"]["sFechaConstitucionHasta"], 0, 2);
                if ($expnue["fechavencimiento"] < $expnue["fechaconstitucion"]) {
                    $expnue["fechavencimiento"] = '99999999';
                }
                $expnue["participacionmujeres"] = doubleval($ctce["organizacion"]["PorcentajeJovenesCapitalSocial"]);
                $expnue["cap_porcnaltot"] = $ctce["organizacion"]["CapitalNacionalTotal"];
                $expnue["cap_porcnalpri"] = $ctce["organizacion"]["CapitalNacionalPrivado"];
                $expnue["cap_porcnalpub"] = $ctce["organizacion"]["CapitalNacionalPublico"];
                $expnue["cap_porcexttot"] = $ctce["organizacion"]["CapitalExtranjeroTotal"];
                $expnue["cap_porcextpri"] = $ctce["organizacion"]["CapitalExtranjeroPrivado"];
                $expnue["cap_porcextpub"] = $ctce["organizacion"]["CapitalExtranjeroPublico"];
            }

            //
            $expnue["personal"] = intval($ctce["organizacion"]["PersonalOcupadoNivelNacional"]);
            $expnue["personaltemp"] = intval($ctce["organizacion"]["PorcentajeTrabajadoresTemporales"]);
            $expnue["impexp"] = '0';
            if ($ctce["organizacion"]["SwImportador"] == '1') {
                $expnue["impexp"] = '1';
                if ($ctce["organizacion"]["SwExportador"] == '1') {
                    $expnue["impexp"] = '3';
                }
            } else {
                if ($ctce["organizacion"]["SwExportador"] == '1') {
                    $expnue["impexp"] = '2';
                }
            }
            $expnue["gruponiif"] = $ctce["organizacion"]["GrupoNIIF"];
            if ($ctce["organizacion"]["SwProcesoInnovacion"] == '1') {
                $expnue["procesosinnovacion"] = 'S';
            } else {
                $expnue["procesosinnovacion"] = 'N';
            }
            if ($ctce["organizacion"]["SwEmpresaFamiliar"] == '1') {
                $expnue["empresafamiliar"] = 'S';
            } else {
                $expnue["empresafamiliar"] = 'N';
            }
            if ($ctce["organizacion"]["SwAportante"] == '1') {
                $expnue["aportantesegsocial"] = 'S';
            } else {
                $expnue["aportantesegsocial"] = 'N';
            }
            $expnue["tipoaportantesegsocial"] = $ctce["organizacion"]["IdTipoAportante"];
            $expnue["cantidadmujeres"] = intval($ctce["organizacion"]["NumeroMujeres"]);
            $expnue["cantidadmujerescargosdirectivos"] = intval($ctce["organizacion"]["NumeroMujeresCargosDirectivos"]);
            $expnue["sexo"] = $ctce["organizacion"]["TipoGenero"];
            if ($ctce["organizacion"]["SwCumpleBeneficioLey1780"] == '1') {
                $expnue["benley1780"] = 'S';
                $expnue["cumplerequisitos1780"] = 'S';
            }
            if ($ctce["organizacion"]["SwEmprendimientoSocial"] == '1') {
                $expnue["emprendimientosocial"] = 'S';
            } else {
                if ($expnue["organizacion"] == '01' || ($expnue["organizacion"] > '02' && $expnue["categoria"] == '1')) {
                    $expnue["emprendimientosocial"] = 'N';
                }
            }

            if ($ctce["actividades"]["CodigoA1"] != '') {
                $expnue["ciius"][1] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . $ctce["actividades"]["CodigoA1"] . "'", "idciiu");
                if ($expnue["organizacion"] == '01' || ($expnue["organizacion"] > '02' && $expnue["categoria"] == '1')) {
                    $expnue["feciniact1"] = substr($ctce["actividades"]["FechaCIIU1"], 6, 4) . substr($ctce["actividades"]["FechaCIIU1"], 3, 2) . substr($ctce["actividades"]["FechaCIIU1"], 0, 2);
                }
            }
            if ($ctce["actividades"]["CodigoA2"] != '') {
                $expnue["ciius"][2] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . $ctce["actividades"]["CodigoA2"] . "'", "idciiu");
                if ($expnue["organizacion"] == '01' || ($expnue["organizacion"] > '02' && $expnue["categoria"] == '1')) {
                    $expnue["feciniact2"] = substr($ctce["actividades"]["FechaCIIU2"], 6, 4) . substr($ctce["actividades"]["FechaCIIU2"], 3, 2) . substr($ctce["actividades"]["FechaCIIU2"], 0, 2);
                }
            }
            if ($ctce["actividades"]["CodigoA3"] != '') {
                $expnue["ciius"][3] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . $ctce["actividades"]["CodigoA3"] . "'", "idciiu");
            }
            if ($ctce["actividades"]["CodigoA4"] != '') {
                $expnue["ciius"][4] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . $ctce["actividades"]["CodigoA4"] . "'", "idciiu");
            }
            if (isset($ctce["actividades"]["DescripcionActividadEconomica"]))
                $expnue["desactiv"] = $ctce["actividades"]["DescripcionActividadEconomica"];
            if ($ctce["actividades"]["CIIUMasIngresos"] != '') {
                $expnue["ciiutamanoempresarial"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiunum='" . $ctce["actividades"]["CIIUMasIngresos"] . "'", "idciiu");
            }

            //
            if ($expnue["organizacion"] == '01' || ($expnue["organizacion"] > '02' && $expnue["categoria"] == '1')) {
                $expnue["anodatos"] = date("Y");
                $expnue["fechadatos"] = date("Ymd");
                $expnue["actcte"] = doubleval($ctce["financieros"]["ActivoCorriente"]);
                $expnue["actnocte"] = doubleval($ctce["financieros"]["ActivoNoCorriente"]);
                $expnue["acttot"] = doubleval($ctce["financieros"]["ActivoTotal"]);
                $expnue["pascte"] = doubleval($ctce["financieros"]["PasivoCorriente"]);
                $expnue["paslar"] = doubleval($ctce["financieros"]["PasivoNoCorriente"]);
                $expnue["pastot"] = doubleval($ctce["financieros"]["PasivoTotal"]);
                $expnue["pattot"] = doubleval($ctce["financieros"]["PatrimonioNeto"]);
                $expnue["paspat"] = doubleval($ctce["financieros"]["PasivoPatrimonio"]);
                $expnue["ingope"] = doubleval($ctce["financieros"]["IngresosActividadOrdinaria"]);
                $expnue["ingnoope"] = doubleval($ctce["financieros"]["OtrosIngresos"]);
                $expnue["cosven"] = doubleval($ctce["financieros"]["CostosVentas"]);
                $expnue["gtoven"] = doubleval($ctce["financieros"]["GastosOperacionales"]);
                $expnue["gtoadm"] = doubleval($ctce["financieros"]["OtrosGastos"]);
                $expnue["gasnoope"] = doubleval($ctce["financieros"]["OtrosGastos"]);
                $expnue["gasimp"] = doubleval($ctce["financieros"]["GastosImpuestos"]);
                $expnue["utiope"] = doubleval($ctce["financieros"]["UtilidadOperacional"]);
                $expnue["utinet"] = doubleval($ctce["financieros"]["UtilidadNeta"]);
                $expnue["ingresostamanoempresarial"] = $expnue["ingope"];
                $expnue["anodatostamanoempresarial"] = $expnue["anodatos"];
                $expnue["fechadatostamanoempresarial"] = $expnue["fechadatos"];
                $calte = \funcionesRegistrales::determinarTamanoEmpresarialUvts($mysqli, $expnue["ciiutamanoempresarial"], $expnue["ingresostamanoempresarial"], $expnue["anodatostamanoempresarial"], $expnue["fechadatostamanoempresarial"], $expnue["anodatos"]);
                $expnue["tamanoempresarial957"] = $calte["tamanotexto"];
                $expnue["tamanoempresarial957uvts"] = '';
                $expnue["tamanoempresarial957codigo"] = '';
                if ($expnue["tamanoempresarial957"] == 'MICRO EMPRESA') {
                    $expnue["tamanoempresarial957codigo"] = '1';
                }
                if ($expnue["tamanoempresarial957"] == 'PEQUEÑA EMPRESA') {
                    $expnue["tamanoempresarial957codigo"] = '2';
                }
                if ($expnue["tamanoempresarial957"] == 'MEDIANA EMPRESA') {
                    $expnue["tamanoempresarial957codigo"] = '3';
                }
                if ($expnue["tamanoempresarial957"] == 'GRAN EMPRESA') {
                    $expnue["tamanoempresarial957codigo"] = '4';
                }
                $expnue["codrespotri"] = array();
                if (isset($ctce["responsabilidades"]) && !empty($ctce["responsabilidades"])) {
                    foreach ($ctce["responsabilidades"] as $respon) {
                        $expnue["codrespotri"][] = $respon["Codigo"];
                    }
                }
            }

            if (isset($exp["codrespotri"]) && !empty($exp["codrespotri"])) {
                foreach ($exp["codrespotri"] as $r1) {
                    if ($ctce["tresponseactual"] != '') {
                        $ctce["tresponseactual"] .= ', ';
                    }
                    $ctce["tresponseactual"] .= $r1;
                }
            }
            if (isset($expnue["codrespotri"]) && !empty($expnue["codrespotri"])) {
                foreach ($expnue["codrespotri"] as $r1) {
                    if ($ctce["tresponsaimportar"] != '') {
                        $ctce["tresponsaimportar"] .= ', ';
                    }
                    $ctce["tresponsaimportar"] .= $r1;
                }
            }

            //
            if ($expnue["organizacion"] == '12' || $expnue["organizacion"] == '14') {
                if ($expnue["categoria"] == '1') {
                    if (!empty($ctce["anexo5"])) {
                        $expnue["ctresacntasociados"] = intval($ctce["anexo5"]["NumeroAsociados"]);
                        $expnue["ctresacntmujeres"] = intval($ctce["anexo5"]["NumeroMujeres"]);
                        $expnue["ctresacnthombres"] = intval($ctce["anexo5"]["NumeroHombres"]);
                        if ($ctce["anexo5"]["SwPerteneceGremio"] == '1') {
                            $expnue["ctresapertgremio"] = 'S';
                        }
                        $expnue["ctresagremio"] = $ctce["anexo5"]["CualGremio"];
                        $expnue["ctresaivc"] = $ctce["anexo5"]["EntidadIVC"];
                        $expnue["vigcontrol"] = $ctce["anexo5"]["EntidadIVC"];
                        if ($ctce["anexo5"]["SwDocumentacionIVC"] == '1') {
                            $expnue["ctresainfoivc"] = 'S';
                        }
                        $expnue["ctresaacredita"] = $ctce["anexo5"]["EntidadAcredita"];
                        if ($ctce["anexo5"]["SwRequiereAutorizacion"] == '1') {
                            $expnue["ctresaautregistro"] = 'S';
                        }
                        $expnue["ctresaentautoriza"] = $ctce["anexo5"]["EntidadAutoriza"];
                        if ($ctce["anexo5"]["SwPersonaDiscapacidad"] == '1') {
                            $expnue["ctresadiscap"] = 'S';
                        }
                        if ($ctce["anexo5"]["SwGrupoLGTBI"] == '1') {
                            $expnue["ctresalgbti"] = 'S';
                        }
                        if ($ctce["anexo5"]["SwEtnia"] == '1') {
                            $expnue["ctresaetnia"] = 'S';
                        }
                        $expnue["ctresacualetnia"] = $ctce["anexo5"]["CualETNIA"];
                        if ($ctce["anexo5"]["SwIndicadoresGestion"] == '1') {
                            $expnue["ctresaindgest"] = 'S';
                        }
                        if ($ctce["anexo5"]["SwCondicion"] == '1') {
                            $expnue["ctresadespvictreins"] = 'S';
                        }
                        $expnue["ctresacualdespvictreins"] = $ctce["anexo5"]["CualCondicion"];
                    }
                }
            }
        }

        // En caso de establecimientos de comercio, agencias o sucursales
        if ($organizacion == '02' || $categoria == '2' || $categoria == '3') {
            foreach ($ctce["establecimientos"] as $est) {
                $expnue["dircom"] = $est["Ubicacion"]["DireccionPrincipal"];
                $expnue["muncom"] = $est["Ubicacion"]["IdMunicipioPrincipal"];      
                $expnue["paicom"] = '169';       
                if ($est["Ubicacion"]["CodigoSIIPrincipal"] != '') {
                    $expnue["barriocom"] = sprintf("%05s", $est["Ubicacion"]["CodigoSIIPrincipal"]);
                }
                $expnue["barriocomnombre"] = $est["Ubicacion"]["BarrioPrincipal"];
                $expnue["telcom1"] = $est["Ubicacion"]["TelefonoPrincipal1"];
                $expnue["telcom2"] = $est["Ubicacion"]["TelefonoPrincipal2"];
                $expnue["celcom"] = $est["Ubicacion"]["TelefonoPrincipal3"];
                $expnue["emailcom"] = $est["Ubicacion"]["EmailPrincipal"];
                $expnue["ctrubi"] = $est["Ubicacion"]["IdTipoUbicacion"];
                $expnue["codigopostalcom"] = $est["Ubicacion"]["ZonaPostalPrincipal"];
                $expnue["ctrubi"] = $est["Ubicacion"]["IdTipoUbicacion"];
                $expnue["ciius"][1] = $est["Actividades"]["CodigoA1"];
                $expnue["ciius"][2] = $est["Actividades"]["CodigoA2"];
                $expnue["ciius"][3] = $est["Actividades"]["CodigoA3"];
                $expnue["ciius"][4] = $est["Actividades"]["CodigoA4"];
                $expnue["desactiv"] = $est["Actividades"]["DescripcionActividadEconomica"];
                if ($est["Actividades"]["IdTipoLocal"] == 'P') {
                    $expnue["tipolocal"] = '1';
                }
                if ($est["Actividades"]["IdTipoLocal"] == 'A') {
                    $expnue["tipolocal"] = '0';
                }

                if (isset($est["Actividades"]["Activos"])) {
                    $expnue["actvin"] = $est["Actividades"]["Activos"];
                }
                if (isset($est["Actividades"]["Personal"])) {
                    $expnue["personal"] = $est["Actividades"]["Personal"];
                }
                if (isset($est["Actividades"]["Activos"])) {
                    $expnue["actvin"] = doubleval($est["Actividades"]["Activos"]);
                    $expnue["anodatos"] = date("Y");
                    $expnue["fechadatos"] = date("Ymd");
                }
                $expnue["propietarioacrear"] = array();
                if ($matP != '') {
                    $expp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matP . "'");
                    $expnue["propietarioacrear"] = array();
                    $expnue["propietarioacrear"]['codigocamara'] = CODIGO_EMPRESA;
                    $expnue["propietarioacrear"]['matriculapropietario'] = $matP;
                    $expnue["propietarioacrear"]['tipopropiedad'] = '0';
                    $expnue["propietarioacrear"]['tipoidentificacion'] = $expp["idclase"];
                    $expnue["propietarioacrear"]['identificacion'] = $expp["numid"];
                    $expnue["propietarioacrear"]['nit'] = $expp["nit"];
                    $expnue["propietarioacrear"]['razonsocial'] = $expp["razonsocial"];
                    $expnue["propietarioacrear"]['apellido1'] = $expp["apellido1"];
                    $expnue["propietarioacrear"]['apellido2'] = $expp["apellido2"];
                    $expnue["propietarioacrear"]['nombre1'] = $expp["nombre1"];
                    $expnue["propietarioacrear"]['nombre2'] = $expp["nombre2"];
                    $expnue["propietarioacrear"]['dircom'] = $expp["dircom"];
                    $expnue["propietarioacrear"]['muncom'] = $expp["muncom"];
                    $expnue["propietarioacrear"]['telcom1'] = $expp["telcom1"];
                    $expnue["propietarioacrear"]['telcom2'] = $expp["telcom2"];
                    $expnue["propietarioacrear"]['telcom3'] = $expp["telcom3"];
                    $expnue["propietarioacrear"]['emailcom'] = $expp["emailcom"];

                    $expnue["propietarioacrear"]['dirnot'] = $expp["dirnot"];
                    $expnue["propietarioacrear"]['munnot'] = $expp["munnot"];
                    $expnue["propietarioacrear"]['telnot1'] = $expp["telnot"];
                    $expnue["propietarioacrear"]['telnot2'] = $expp["telnot2"];
                    $expnue["propietarioacrear"]['telnot3'] = $expp["telnot3"];
                    $expnue["propietarioacrear"]['emailnot'] = $expp["emailnot"];
                    
                    $expnue["propietarioacrear"]['tipoidentificacionreplegal'] = '';
                    $expnue["propietarioacrear"]['identificacionreplegal'] = '';
                }
            }
        }

        //
        $ctce["expediente"] = $expnue;

        //
        if (!isset($ctce["expediente"]["gasnoope"])) {
            $ctce["expediente"]["gasnoope"] = 0;
        }
        $ctce["dataf"]["matricula"] = $ctce["expediente"]["matricula"];
        $ctce["dataf"]['anodatos'] = $ctce["expediente"]["anodatos"];
        $ctce["dataf"]["fechadatos"] = $ctce["expediente"]["fechadatos"];
        $ctce["dataf"]["personal"] = $ctce["expediente"]["personal"];
        $ctce["dataf"]["personaltemp"] = $ctce["expediente"]["personaltemp"];
        $ctce["dataf"]["actcte"] = $ctce["expediente"]["actcte"];
        $ctce["dataf"]["actnocte"] = $ctce["expediente"]["actnocte"];
        $ctce["dataf"]["actfij"] = 0;
        $ctce["dataf"]["fijnet"] = 0;
        $ctce["dataf"]["actval"] = 0;
        $ctce["dataf"]["actotr"] = 0;
        $ctce["dataf"]["actsinaju"] = 0;
        $ctce["dataf"]["invent"] = 0;
        $ctce["dataf"]["acttot"] = $ctce["expediente"]["acttot"];
        $ctce["dataf"]["pascte"] = $ctce["expediente"]["pascte"];
        $ctce["dataf"]["paslar"] = $ctce["expediente"]["paslar"];
        $ctce["dataf"]["pastot"] = $ctce["expediente"]["pastot"];
        $ctce["dataf"]["pattot"] = $ctce["expediente"]["pattot"];
        $ctce["dataf"]["paspat"] = $ctce["expediente"]["paspat"];
        $ctce["dataf"]["balsoc"] = 0;
        $ctce["dataf"]["ingope"] = $ctce["expediente"]["ingope"];
        $ctce["dataf"]["ingnoope"] = $ctce["expediente"]["ingnoope"];
        $ctce["dataf"]["cosven"] = $ctce["expediente"]["cosven"];
        $ctce["dataf"]["gtoven"] = $ctce["expediente"]["gtoven"];
        $ctce["dataf"]["gasope"] = 0;
        $ctce["dataf"]["depamo"] = 0;
        $ctce["dataf"]["gasint"] = 0;
        $ctce["dataf"]["gasnoope"] = $ctce["expediente"]["gasnoope"];
        $ctce["dataf"]["gtoadm"] = $ctce["expediente"]["gtoadm"];
        $ctce["dataf"]["gasimp"] = $ctce["expediente"]["gasimp"];
        $ctce["dataf"]["utiope"] = $ctce["expediente"]["utiope"];
        $ctce["dataf"]["utinet"] = $ctce["expediente"]["utinet"];
        $ctce["dataf"]["actvin"] = $ctce["expediente"]["actvin"];

        if ($expnue["organizacion"] == '01' || ($expnue["organizacion"] > '02' && $expnue["categoria"] == '1')) {
            if (isset($ctce["textos"]) && !empty($ctce["textos"])) {
                foreach ($ctce["textos"] as $tx) {
                    if ($tx["CodigoSII"] != '') {
                        $ctce["certificas"][$tx["CodigoSII"]] = $tx["Texto"];
                    }
                }
            }
        }

        //
        if ($cerrarMysqli == 'si') {
            $mysqli->close();
        }

        //
        return $ctce;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $matricula
     * @param type $accion
     * @param type $data
     * @param type $ambiente
     * @return string
     */
    public static function consumirRegMerV2($mysqli = null, $matricula = '', $accion = '', $data = array(), $ambiente = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $nameLog = 'consumirRegMerV2_' . date("Ymd");

        //
        $datos = array();
        if ($matricula == '') {
            if (isset($data) && !empty($data)) {
                $datos = $data;
            } else {
                if (isset($_SESSION['formulario']) && !empty($_SESSION['formulario'])) {
                    $datos = $_SESSION['formulario'];
                }
            }
        } else {
            if (empty($data)) {
                if ($mysqli == null) {
                    $mysqli = conexionMysqliApi();
                    $cerrarMysqli = 'si';
                } else {
                    $cerrarMysqli = 'no';
                }
                $datos = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $matricula, '', '', '', 'si', 'N', 'SII');
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                    $mysqli = null;
                }
            } else {
                $datos = $data;
            }
        }
        if ($accion == 'forzar') {
            $renovarhash = true;
        } else {
            $renovarhash = false;
        }
        $requestRues = \funcionesRues::construirParametrosRegMerV2($mysqli, $datos, $renovarhash);
        $sal = json_encode($requestRues, JSON_PRETTY_PRINT);
        // \logApi::general2($nameLog, $datos["matricula"], 'Información a enviar al RUES : ' . chr(10) . chr(13) . $sal);        
        if ($requestRues["codigo_error"] !== "") {
            \logApi::general2($nameLog, $datos["matricula"], 'Información a enviar al RUES : ' . chr(10) . chr(13) . $sal);
            $respuesta["codigoHTTP"] = '';
            $respuesta["codigoError"] = '9970';
            $respuesta["msgError"] = 'actualizaciónRegmerV2 - Error en datos - armando json - ' . $requestRues["mensaje_error"];
            $respuesta["hashControl"] = '';
            $respuesta["request"] = '';
            $respuesta["version"] = '2';
            return $respuesta;
        }

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token, no es posible continuar';
            \logApi::general2($nameLog, $datos["matricula"], 'Error solicitando token en actualizaciónRegmerV2');
            $respuesta["codigoHTTP"] = '';
            $respuesta["codigoError"] = '9969';
            $respuesta["msgError"] = 'actualizaciónRegmerV2 - Error solicitando token';
            $respuesta["hashControl"] = '';
            $respuesta["request"] = '';
            $respuesta["version"] = '2';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . 'api/RegMerV2/');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sal);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, $datos["matricula"], 'La respuesta del servicio web no es un Json (1) - ' . $responseRues);
                        $evaluarJson = 'no';
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, $datos["matricula"], 'ResponseRegMer : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }

        //
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            $resp1 = json_decode($responseRues, true);
            if (isset($resp1["respuesta"])) {
                $resp = $resp1["respuesta"];
            } else {
                $resp = $resp1;
            }
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                    $respuesta["request"] = $sal;
                    $respuesta["version"] = '2';
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = 'Matricula Actualizada en RUES';
                    $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                    $respuesta["request"] = $sal;
                    $respuesta["version"] = '2';
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                $respuesta["request"] = $sal;
                $respuesta["version"] = '2';
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigoError"] = '0000';
            } else {
                $respuesta["codigoError"] = '0004';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code . ' - ' . $responseRues);
            $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
            $respuesta["request"] = $sal;
            $respuesta["version"] = '2';
            return $respuesta;
        }
    }

    /**
     * 
     * @param type $razon_social
     * @param type $usuario
     * @return string
     */
    public static function consultarHomonimia($razon_social = '', $usuario = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $respuesta = array();
        $respuesta["codigoHTTP"] = '';
        $respuesta["codigoError"] = '';
        $respuesta["msgError"] = '';
        $respuesta["hashControl"] = '';
        $respuesta["response"] = '';

        $nameLog = 'consultarHomonimia_' . date("Ymd");
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en apiconsultarHomonimia';
            \logApi::general2($nameLog, '', 'Error solicitando token en apiconsultarHomonimia');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en apiconsultarHomonimia';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $parametros = array();
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"], 29, "0", STR_PAD_RIGHT);
        $parametros['usuario'] = $usuario;
        $parametros['razon_social'] = $razon_social;
        $sal = json_encode($parametros, JSON_PRETTY_PRINT);
        \logApi::general2($nameLog, '', 'Request ConsultarHomonimia : ' . chr(10) . chr(13) . $sal);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . '/' . CONSULTAR_HOMONIMIA);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sal);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1) - ' . $responseRues);
                        $evaluarJson = 'no';
                    } else {
                        // \logApi::general2($nameLog, '', 'ResponseConsultarHomonimia : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, '', 'ResponseConsultarHomonimia : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            $resp1 = json_decode($responseRues, true);
            if (isset($resp1["respuesta"])) {
                $resp = $resp1["respuesta"];
            } else {
                $resp = $resp1;
            }
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["response"] = '';
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = '';
                    $respuesta["hashControl"] = '';
                    $respuesta["response"] = $resp;
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                $respuesta["hashControl"] = '';
                $respuesta["response"] = '';
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigoError"] = '0000';
            } else {
                $respuesta["codigoError"] = '0004';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code . ' - ' . $responseRues);
            $respuesta["hashControl"] = '';
            $respuesta["response"] = '';
            return $respuesta;
        }
    }

    /**
     * 
     * @param type $nombre
     * @param type $usuario
     * @return string
     */
    public static function consultarNombre($nombre = '', $usuario = '', $ambiente = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $respuesta = array();
        $respuesta["codigoHTTP"] = '';
        $respuesta["codigoError"] = '';
        $respuesta["msgError"] = '';
        $respuesta["hashControl"] = '';
        $respuesta["response"] = '';

        $nameLog = 'consultarNombre_' . date("Ymd");
        $token = \funcionesRues::solicitarToken($ambiente);
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en apiConsultarNombre';
            \logApi::general2($nameLog, '', 'Error solicitando token en apiConsultarNombre');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en apiConsultarNombre';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues($ambiente);

        //
        $parametros = array();
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"], 29, "0", STR_PAD_RIGHT);
        $parametros['usuario'] = $usuario;
        $parametros['razon_social'] = $nombre;
        $sal = json_encode($parametros, JSON_PRETTY_PRINT);
        \logApi::general2($nameLog, '', 'Request ConsultarNombre : ' . chr(10) . chr(13) . $sal);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . '/' . CONSULTAR_NOMBRE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sal);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1) - ' . $responseRues);
                        $evaluarJson = 'no';
                    } else {
                        // \logApi::general2($nameLog, '', 'ResponseConsultarNombre : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, '', 'ResponseConsultarNombre : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            $resp1 = json_decode($responseRues, true);
            if (isset($resp1["respuesta"])) {
                $resp = $resp1["respuesta"];
            } else {
                $resp = $resp1;
            }
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["response"] = '';
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = '';
                    $respuesta["hashControl"] = '';
                    $respuesta["response"] = $resp;
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                $respuesta["hashControl"] = '';
                $respuesta["response"] = '';
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigoError"] = '0000';
            } else {
                $respuesta["codigoError"] = '0004';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code . ' - ' . $responseRues);
            $respuesta["hashControl"] = '';
            $respuesta["response"] = '';
            return $respuesta;
        }
    }

    /**
     * 
     * @param type $palabras
     * @param type $usuario
     * @return string
     */
    public static function consultarPalabrasClave($palabras = '', $usuario = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $respuesta = array();
        $respuesta["codigoHTTP"] = '';
        $respuesta["codigoError"] = '';
        $respuesta["msgError"] = '';
        $respuesta["hashControl"] = '';
        $respuesta["response"] = '';

        $nameLog = 'consultarNombre_' . date("Ymd");
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en apiconsultarPalabrasClave';
            \logApi::general2($nameLog, '', 'Error solicitando token en apiconsultarPalabrasClave');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en apiconsultarPalabrasClave';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $parametros = array();
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"], 29, "0", STR_PAD_RIGHT);
        $parametros['usuario'] = $usuario;
        $parametros['razon_social'] = $palabras;
        $sal = json_encode($parametros, JSON_PRETTY_PRINT);
        \logApi::general2($nameLog, '', 'Request ConsultarNombre : ' . chr(10) . chr(13) . $sal);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . '/' . CONSULTAR_PALABRA_CLAVE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sal);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1) - ' . $responseRues);
                        $evaluarJson = 'no';
                    } else {
                        // \logApi::general2($nameLog, '', 'ResponseConsultarPalabrasClave : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, '', 'ResponseConsultarPalabrasClave : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            $resp1 = json_decode($responseRues, true);
            if (isset($resp1["respuesta"])) {
                $resp = $resp1["respuesta"];
            } else {
                $resp = $resp1;
            }
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["response"] = '';
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = '';
                    $respuesta["hashControl"] = '';
                    $respuesta["response"] = $resp;
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                $respuesta["hashControl"] = '';
                $respuesta["response"] = '';
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigoError"] = '0000';
            } else {
                $respuesta["codigoError"] = '0004';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code . ' - ' . $responseRues);
            $respuesta["hashControl"] = '';
            $respuesta["response"] = '';
            return $respuesta;
        }
    }

    
    /**
     * 
     * @param type $tipo_identificacion
     * @param type $numero_identificacion
     * @param type $dv
     * @param string $usuario
     * @param type $ambiente
     * @return string
     */
    public static function consultarNumeroIdentificacion($tipo_identificacion = '', $numero_identificacion = '', $dv = '', $usuario = '', $ambiente = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $respuesta = array();
        $respuesta["codigoHTTP"] = '';
        $respuesta["codigoError"] = '';
        $respuesta["msgError"] = '';
        $respuesta["hashControl"] = '';
        $respuesta["response"] = '';

        $nameLog = 'ruesConsultarNumeroIdentificacion_' . date("Ymd");
        $token = \funcionesRues::solicitarToken($ambiente);
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en apiconsultarNumeroIdentificacion';
            \logApi::general2($nameLog, '', 'Error solicitando token en apiconsultarNumeroIdentificacion');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en apiconsultarNumeroIdentificacion';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues($ambiente);

        //
        if ($usuario == '') {
            $usuario = 'USERSII';
        }
        $parametros = array();
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"], 29, "0", STR_PAD_RIGHT);
        $parametros['usuario'] = $usuario;
        $parametros['codigo_clase_identificacion'] = \funcionesRues::homologarTipoIdentificacion(null, $tipo_identificacion);
        $parametros['numero_identificacion'] = sprintf("%014s", $numero_identificacion);
        $parametros['digito_verificacion'] = $dv;
        $sal = json_encode($parametros, JSON_PRETTY_PRINT);
        \logApi::general2($nameLog, '', 'Request ConsultarNombre : ' . chr(10) . chr(13) . $sal);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . '/' . CONSULTAR_NUMERO_IDENTIFICACION);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sal);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response - ' . $responseRues);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json');
                        $evaluarJson = 'no';
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, '', 'ResponseConsultarPalabrasClave : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            $resp1 = json_decode($responseRues, true);
            if (isset($resp1["respuesta"])) {
                $resp = $resp1["respuesta"];
            } else {
                $resp = $resp1;
            }
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["response"] = '';
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = '';
                    $respuesta["hashControl"] = '';
                    $respuesta["response"] = $resp;
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                $respuesta["hashControl"] = '';
                $respuesta["response"] = '';
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigoError"] = '0000';
            } else {
                $respuesta["codigoError"] = '0004';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code . ' - ' . $responseRues);
            $respuesta["hashControl"] = '';
            $respuesta["response"] = '';
            return $respuesta;
        }
    }

    public static function consultarNitDian($numero_identificacion = '', $dv = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $respuesta = array();
        $respuesta["codigoHTTP"] = '';
        $respuesta["codigoError"] = '';
        $respuesta["msgError"] = '';
        $respuesta["hashControl"] = '';
        $respuesta["response"] = '';

        $nameLog = 'ruesConsultarNumeroIdentificacion_' . date("Ymd");
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en apiconsultarNumeroIdentificacion';
            \logApi::general2($nameLog, '', 'Error solicitando token en apiconsultarNumeroIdentificacion');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en apiconsultarNumeroIdentificacion';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        if ($usuario == '') {
            $usuario = 'USERSII';
        }
        $parametros = array();
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"], 29, "0", STR_PAD_RIGHT);
        $parametros['usuario'] = $usuario;
        $parametros['codigo_clase_identificacion'] = \funcionesRues::homologarTipoIdentificacion(null, $tipo_identificacion);
        $parametros['numero_identificacion'] = sprintf("%014s", $numero_identificacion);
        $parametros['digito_verificacion'] = $dv;
        $sal = json_encode($parametros, JSON_PRETTY_PRINT);
        \logApi::general2($nameLog, '', 'Request ConsultarNombre : ' . chr(10) . chr(13) . $sal);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . '/' . CONSULTAR_NUMERO_IDENTIFICACION);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sal);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response - ' . $responseRues);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json');
                        $evaluarJson = 'no';
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, '', 'ResponseConsultarPalabrasClave : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            $resp1 = json_decode($responseRues, true);
            if (isset($resp1["respuesta"])) {
                $resp = $resp1["respuesta"];
            } else {
                $resp = $resp1;
            }
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["response"] = '';
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = '';
                    $respuesta["hashControl"] = '';
                    $respuesta["response"] = $resp;
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                $respuesta["hashControl"] = '';
                $respuesta["response"] = '';
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigoError"] = '0000';
            } else {
                $respuesta["codigoError"] = '0004';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code . ' - ' . $responseRues);
            $respuesta["hashControl"] = '';
            $respuesta["response"] = '';
            return $respuesta;
        }
    }
    
    /**
     * 
     * @param type $mysqli
     * @param type $proponente
     * @param type $data
     * @return bool
     */
    public static function consumirRegPro($data = array()) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $respuesta = array();
        $respuesta["codigoHTTP"] = '';
        $respuesta["codigoError"] = '';
        $respuesta["msgError"] = '';
        $respuesta["hashControl"] = '';
        $respuesta["request"] = '';

        $nameLog = 'consumirRegPro_' . date("Ymd");

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en apiActualizarProponente';
            \logApi::general2($nameLog, '', 'Error solicitando token en apiActualizarProponente');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en apiActualizarProponente';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $datos = $data;

        //
        $mysqli = conexionMysqliApi();
        $requestRues = \funcionesRues::construirParametrosRegPro($mysqli, $datos);
        $mysqli->close();
        \logApi::general2($nameLog, $datos["proponente"], 'RequestRegpro : ' . chr(10) . chr(13) . $requestRues);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . ACTUALIZAR_PROPONENTE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestRues);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $responseRues = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, $datos["proponente"], 'La respuesta del servicio web no es un Json (1) - ' . $responseRues);
                        $evaluarJson = 'no';
                    } else {
                        // \logApi::general2($nameLog, $datos["proponente"], 'ResponseRegPro : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $responseRues;
                    \logApi::general2($nameLog, $datos["proponente"], 'ResponseRegPro : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }

        curl_close($ch);

        if ($evaluarJson == 'si') {
            $resp1 = json_decode($responseRues, true);
            if (isset($resp1["respuesta"])) {
                $resp = $resp1["respuesta"];
            } else {
                $resp = $resp1;
            }
            if (isset($resp['codigo_error'])) {
                if ($resp['codigo_error'] != '0000') {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = $resp['codigo_error'];
                    $respuesta["msgError"] = empty($resp['mensaje_error']) ? 'No se obtiene mensaje de error del RUES' : $resp['mensaje_error'];
                    $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                    $respuesta["request"] = $requestRues;
                    unset($resp);
                    return $respuesta;
                } else {
                    $respuesta["codigoHTTP"] = $http_code;
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = 'Proponente Actualizado en RUES';
                    $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                    $respuesta["request"] = $requestRues;
                    unset($resp);
                    return $respuesta;
                }
            } else {
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["codigoError"] = '0003';
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
                $respuesta["request"] = $requestRues;
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigoError"] = '0000';
            } else {
                $respuesta["codigoError"] = '0004';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code . ' - ' . $responseRues);
            $respuesta["hashControl"] = $datos['hashcontrolnuevo'];
            $respuesta["request"] = $requestRues;
            return $respuesta;
        }
    }

    public static function construirParametrosRegMerV2($mysqli = null, $datos = array(), $renovarhash = false) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/retornarHomologaciones.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

        //
        $_SESSION["generales"]["errorjson"] = '';

        //
        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysqli = 'si';
        } else {
            $cerrarMysqli = 'no';
        }

        //
        $arrDatosInfoFinanciera = array();
        $arrDatosInfoCapitales = array();
        $arrDatosInfoAdicional = array();
        $arrDatosPropietarios = array();
        $arrDatosVinculos = array();
        $arrDatosHistoricoPagos = array();
        $Nit = '';
        $dv = '';

        //
        $datos["nit"] = str_replace(array(",", "-", " "), "", $datos["nit"]);
        $datos["identificacion"] = str_replace(array(",", "-", " "), "", $datos["identificacion"]);

        // En caso de sucursales y agencias        
        // Se borran las identificaciones
        if (($datos["organizacion"] != '01') && ($datos["organizacion"] != '02') && ($datos["categoria"] == '2' || $datos["categoria"] == '3')) {
            $datos["tipoidentificacion"] = '';
            $datos["identificacion"] = '';
            $datos["nit"] = '';
        }

        // En caso de personas jurídicas principales
        // Solo puede tener nit o vacío
        if (($datos["organizacion"] != '01') && ($datos["organizacion"] != '02') && ($datos["categoria"] == '1')) {
            if (trim($datos["tipoidentificacion"]) != '2') {
                if (ltrim(trim($datos["nit"]), "0") !== '') {
                    $datos["tipoidentificacion"] = '2';
                    $datos["identificacion"] = ltrim(trim($datos["nit"]), "0");
                } else {
                    $datos["tipoidentificacion"] = '';
                    $datos["identificacion"] = '';
                }
            }
        }


        //
        if (trim($datos['tipoidentificacion']) == '2') {
            if (($datos["organizacion"] != '01') && ($datos["organizacion"] != '02') && ($datos["categoria"] == '1')) {
                $longitudNit = strlen(trim($datos["nit"]));
                switch ($longitudNit) {
                    case 10:
                        $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                        $numIdentificacion = substr(trim($datos["nit"]), 0, -1);
                        $Nit = $numIdentificacion;
                        $dv = substr($datos["nit"], -1, 1);
                        break;
                    case 9:
                        $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                        $numIdentificacion = trim($datos["nit"]);
                        $Nit = $numIdentificacion;
                        $dv = \funcionesGenerales::calcularDv($datos["nit"]);
                        break;
                    default:
                        $tipoIdentificacion = '';
                        $numIdentificacion = '00000000000000';
                        $Nit = '';
                        $dv = '';
                        break;
                }
            } else {
                if ($datos["nit"] != '') {
                    $nit1 = sprintf("%020s", $datos["nit"]);
                    $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                    $numIdentificacion = ltrim(substr($nit1, 0, 19), "0");
                    $Nit = $numIdentificacion;
                    $dv = substr($nit1, 19, 1);
                } else {
                    $tipoIdentificacion = '';
                    $numIdentificacion = '00000000000000';
                    $Nit = '';
                    $dv = '';
                }
            }
        } else {
            if (trim($datos['tipoidentificacion']) != 'V') {
                if (trim($datos["nit"]) != '') {
                    $nit1 = sprintf("%020s", $datos["nit"]);
                    $Nit = ltrim(substr($nit1, 0, 19), "0");
                    $dv = substr($nit1, 19, 1);
                }
                $tipoIdentificacion = trim($datos['tipoidentificacion']);
                $numIdentificacion = trim($datos["identificacion"]);
            } else {
                $tipoIdentificacion = '09';
                $numIdentificacion = $datos["identificacion"];
            }
            if (trim($datos["nit"]) != '') {
                $nit1 = sprintf("%020s", $datos["nit"]);
                $Nit = ltrim(substr($nit1, 0, 19), "0");
                $dv = substr($nit1, 19, 1);
            }
        }

        /*
         * Forzar en el caso de establecimientos, sucursales o agencias que se envie vacio los datos de identificación sin importar la data que tenga la cámara.
         */

        //Sucursales y agencias
        $valorEstSucAg = '';
        if (($datos["categoria"] == '2') || ($datos["categoria"] == '3')) {
            $tipoIdentificacion = '';
            $numIdentificacion = '';
            $Nit = '';
            $dv = '';
            $valorEstSucAg = $datos["actvin"];
        }
        //Establecimientos 
        if ($datos["organizacion"] == '02') {
            $tipoIdentificacion = '';
            $numIdentificacion = '';
            $Nit = '';
            $dv = '';
            $valorEstSucAg = $datos["valest"];
        }


        $fechaMatricula = $datos["fechamatricula"];
        $fechaRenovacion = $datos["fecharenovacion"];
        $ultAnoRenovado = $datos["ultanoren"];
        $fechaVigencia = '';
        $fechaCancelacion = '';
        $fechaConstitucion = '';
        $anoMatricula = substr($fechaMatricula, 0, 4);
        $anoRenovacion = substr($fechaRenovacion, 0, 4);

        //2020-11-26 : No tener en cuenta lo siguiente a partir de solicitudes de camnbio de domicilio.
        switch ($datos["estadomatricula"]) {
            case 'MC':
            case 'MF':
            case 'MG':
            case 'IC':
            case 'IG':

                if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
                    if (trim($datos["fechaliquidacion"]) != '') {
                        $fechaCancelacion = $datos["fechaliquidacion"];
                    } else {
                        $fechaCancelacion = $datos["fechacancelacion"];
                    }
                } else {
                    if (trim($datos["fechacancelacion"]) != '') {
                        $fechaCancelacion = $datos["fechacancelacion"];
                    } else {
                        $fechaCancelacion = '';
                    }
                }

                //


                $indicadorMotivoCancelacion = '0';
                if (trim($datos["motivocancelacion"]) != '') {
                    $indicadorMotivoCancelacion = $datos["motivocancelacion"];
                }

                // JINT - 20241030 - 
                if ($fechaCancelacion == '') {
                    $fechaCancelacion = $datos["fecharenovacion"];
                }
                if ($fechaCancelacion == '') {
                    $fechaCancelacion = $datos["fechamatricula"];
                }
                break;
            default:
                $fechaCancelacion = '';
                $indicadorMotivoCancelacion = '';
                break;
        }

        if (trim($datos["fechavencimiento"]) == '99999999') {
            $fechaVigencia = '99991231';
        } else {
            if (trim($datos["fechavencimiento"]) != '') {
                $fechaVigencia = $datos["fechavencimiento"];
            } else {
                $fechaVigencia = '99991231';
            }
        }

        if (trim($datos["fechaconstitucion"]) != '') {
            $fechaConstitucion = $datos["fechaconstitucion"];
        } else {
            $fechaConstitucion = '';
        }



        /*
         * Validaciones Campo clasificación Importador Exportador
         */
        if (($datos["impexp"] == '1') || ($datos["impexp"] == '3')) {
            $indicadorImpExp = '1';
        }
        if (($datos["impexp"] == '2') || ($datos["impexp"] == '3')) {
            $indicadorImpExp = '2';
        } else {
            $indicadorImpExp = '0';
        }

        /*
         * Validaciones Campo Afiliado
         */
        switch ($datos["afiliado"]) {
            case '':
            case '0':
            case '2':
                $indicadorAfiliado = 'N';
                break;
            case '1':
            case '3':
            case '5':
                $indicadorAfiliado = 'S';
                break;
            default:
                break;
        }

        $CantEstablecimientos = '';
        if (isset($datos["cantest"])) {
            $CantEstablecimientos = trim($datos["cantest"]);
        }

        $indicadorGrupoNiif = '';
        if (trim($datos["gruponiif"]) == '') {
            $indicadorGrupoNiif = '0';
        } else {
            $indicadorGrupoNiif = \funcionesGenerales::retornarGrupoNiifFormulario($mysqli, $datos["gruponiif"]);
        }

        /*
         * Parámetros Información Financiera 
         */
        if (isset($datos["hf"])) {
            if (count($datos["hf"]) > 0) {
                $arrHistoriaFinanciera = (array) $datos["hf"];
                //Obtiene año inicial de datos (últimos cinco años de información financiera)                
                $anoInicial = $datos["anodatos"] - 5;
                $anosReportados = array();
                $sec = 0;
                foreach ($arrHistoriaFinanciera as $value) {
                    if ($anoInicial < $value["anodatos"]) {
                        if (!isset($anosReportados[$value["anodatos"]])) {
                            $anosReportados[$value["anodatos"]] = $sec;
                        }
                        if (isset($anosReportados[$value["anodatos"]])) {
                            $secBase = $sec;
                            $sec = $anosReportados[$value["anodatos"]];
                        }
                        $arrDatosInfoFinanciera[$sec]['ano_informacion_financiera'] = $value["anodatos"];
                        $arrDatosInfoFinanciera[$sec]['fecha_datos'] = $value["fechadatos"];
                        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
                            if ($value["actnocte"] === null) {
                                $value["actnocte"] = 0;
                            }
                            if ($value["gtoven"] === null) {
                                $value["gtoven"] = 0;
                            }
                            if ($value["gtoadm"] === null) {
                                $value["gtoadm"] = 0;
                            }
                            if ($value["gasimp"] === null) {
                                $value["gasimp"] = 0;
                            }
                            $arrDatosInfoFinanciera[$sec]['activo_corriente'] = $value["actcte"];
                            $arrDatosInfoFinanciera[$sec]['activo_no_corriente'] = $value["actnocte"];
                            $arrDatosInfoFinanciera[$sec]['activo_total'] = $value["acttot"];
                            $arrDatosInfoFinanciera[$sec]['pasivo_corriente'] = $value["pascte"];
                            $arrDatosInfoFinanciera[$sec]['pasivo_no_corriente'] = $value["paslar"];
                            $arrDatosInfoFinanciera[$sec]['pasivo_total'] = $value["pastot"];
                            $arrDatosInfoFinanciera[$sec]['patrimonio_neto'] = $value["pattot"];
                            $arrDatosInfoFinanciera[$sec]['pasivo_mas_patrimonio'] = $value["paspat"];
                            $arrDatosInfoFinanciera[$sec]['balance_social'] = '0';
                            if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
                                if ($datos["categoria"] == '1') {
                                    $arrDatosInfoFinanciera[$sec]['balance_social'] = $value["balsoc"];
                                }
                            }
                            $arrDatosInfoFinanciera[$sec]['ingresos_actividad_ordinaria'] = $value["ingope"];
                            $arrDatosInfoFinanciera[$sec]['otros_ingresos'] = $value["ingnoope"];
                            $arrDatosInfoFinanciera[$sec]['costo_ventas'] = $value["cosven"];
                            $arrDatosInfoFinanciera[$sec]['gastos_operacionales'] = $value["gtoven"];
                            $arrDatosInfoFinanciera[$sec]['otros_gastos'] = $value["gtoadm"];
                            $arrDatosInfoFinanciera[$sec]['gastos_impuestos'] = $value["gasimp"];
                            $arrDatosInfoFinanciera[$sec]['utilidad_perdida_operacional'] = $value["utiope"];
                            $arrDatosInfoFinanciera[$sec]['resultado_del_periodo'] = $value["utinet"];
                            if ($indicadorGrupoNiif == '0') {
                                $indicadorGrupoNiif = '';
                            }
                            $arrDatosInfoFinanciera[$sec]['grupo_niif'] = $indicadorGrupoNiif;
                        }
                        $arrDatosInfoFinanciera[$sec]['valor_est_suc_ag'] = $valorEstSucAg;
                        if (isset($anosReportados[$value["anodatos"]])) {
                            $sec = $secBase;
                        }
                        $sec++;
                    }
                }
                $arrDatosInfoFinanciera = array_values($arrDatosInfoFinanciera);
            }
        }


        /*
         * Parámetros Información Capitales 
         */
        if ($datos["organizacion"] != '01' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
            if ($datos["categoria"] == '1') {
                if (isset($datos["capitales"])) {
                    if (count($datos["capitales"]) > 0) {
                        if ($datos["anodatos"] != 0 && is_numeric($datos["anodatos"])) {
                            $arrHistoriaCapitales = (array) $datos["capitales"];
                            $anoInicial = $datos["anodatos"] - 10;
                            $anosReportados = array();
                            $sec = 0;
                            $secfin = null;
                            foreach ($arrHistoriaCapitales as $value) {
                                if (!isset($anosReportados[$value["anodatos"]])) {
                                    $anosReportados[$value["anodatos"]] = $sec;
                                }
                                if (isset($anosReportados[$value["anodatos"]])) {
                                    $secBase = $sec;
                                    $sec = $anosReportados[$value["anodatos"]];
                                }
                                $secfin = $sec;
                                $arrDatosInfoCapitales[$sec] = array();
                                $arrDatosInfoCapitales[$sec]['fecha_modificacion_capital'] = $value["fechadatos"];
                                if ($datos["organizacion"] != '09') {
                                    $arrDatosInfoCapitales[$sec]['capital_social'] = $value["social"];
                                    $arrDatosInfoCapitales[$sec]['capital_autorizado'] = $value["autorizado"];
                                    $arrDatosInfoCapitales[$sec]['capital_suscrito'] = $value["suscrito"];
                                    $arrDatosInfoCapitales[$sec]['capital_pagado'] = $value["pagado"];
                                    $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_mujeres'] = 0;
                                    $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_etnia'] = 0;
                                }
                                if ($datos["organizacion"] == '09') {
                                    $arrDatosInfoCapitales[$sec]['eat_aportes_laborales'] = $value["apolab"];
                                    $arrDatosInfoCapitales[$sec]['eat_aportes_activos'] = $value["apoact"];
                                    $arrDatosInfoCapitales[$sec]['eat_aportes_laborales_adicionales'] = $value["apolabadi"];
                                    $arrDatosInfoCapitales[$sec]['eat_aportes_en_dinero'] = $value["apodin"];
                                    $arrDatosInfoCapitales[$sec]['eat_total_aportes'] = $value["apolab"] + $value["apoact"] + $value["apolabadi"] + $value["apodin"];
                                    $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_mujeres'] = 0;
                                    $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_etnia'] = 0;
                                }
                                if (isset($anosReportados[$value["anodatos"]])) {
                                    $sec = $secBase;
                                }
                                $sec++;
                            }
                            if ($secfin !== null) {
                                if ($datos["participacionmujeres"] != '') {
                                    if ($datos["participacionmujeres"] == 0) {
                                        $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_mujeres'] = 0.00;
                                    } else {
                                        if ($datos["participacionmujeres"] == 100) {
                                            $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_mujeres'] = $datos["participacionmujeres"];
                                        } else {
                                            $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_mujeres'] = number_format($datos["participacionmujeres"], 2);
                                        }
                                    }
                                }
                                if ($datos["participacionetnia"] != '') {
                                    $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_etnia'] = $datos["participacionetnia"];
                                }
                            }
                            $arrDatosInfoCapitales = array_values($arrDatosInfoCapitales);
                        }
                    }
                }
            }
        }


        /*
         * Parámetros Información Capitales - ESADL
         */
        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
            if ($datos["categoria"] == '1') {
                if (isset($datos["patrimoniosesadl"])) {
                    if (count($datos["patrimoniosesadl"]) > 0) {
                        $arrHistoriaCapitales = (array) $datos["patrimoniosesadl"];
                        $anoInicial = $datos["anodatos"] - 5;
                        $anosReportados = array();
                        $sec = 0;
                        $secfin = null;

                        foreach ($arrHistoriaCapitales as $value) {
                            if (!isset($anosReportados[$value["anodatos"]])) {
                                $anosReportados[$value["anodatos"]] = $sec;
                            }
                            if (isset($anosReportados[$value["anodatos"]])) {
                                $secBase = $sec;
                                $sec = $anosReportados[$value["anodatos"]];
                                $secfin = $sec;
                            }
                            $arrDatosInfoCapitales[$sec]['fecha_modificacion_capital'] = $value["fechadatos"];
                            $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_mujeres'] = 0;
                            $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_etnia'] = 0;
                            $arrDatosInfoCapitales[$sec]['patrimonio_esal'] = $value["patrimonio"];
                            $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_mujeres'] = 0;
                            $arrDatosInfoCapitales[$sec]['porcentaje_participacion_capital_etnia'] = 0;
                            if (isset($anosReportados[$value["anodatos"]])) {
                                $sec = $secBase;
                            }
                            $sec++;
                        }

                        //
                        if ($secfin !== null) {
                            if ($datos["participacionmujeres"] != '' && doubleval($datos["participacionmujeres"]) != 0) {
                                $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_mujeres'] = $datos["participacionmujeres"];
                            }
                            if ($datos["participacionetnia"] != '' && doubleval($datos["participacionetnia"]) != 0) {
                                $arrDatosInfoCapitales[$secfin]['porcentaje_participacion_capital_etnia'] = $datos["participacionetnia"];
                            }
                        }

                        //Obtiene los valores recogidos en el arreglo e indexa el resultado 
                        $arrDatosInfoCapitales = array_values($arrDatosInfoCapitales);
                    }
                }
            }
        }

        /*
         * Parámetros Propietarios de Establecimientos
         */
        if ($datos['organizacion'] == '02') {
            if (isset($datos["propietarios"])) {
                if (count($datos["propietarios"]) > 0) {
                    $arrPropietarios = (array) $datos["propietarios"];
                    foreach ($arrPropietarios as $key => $value) {
                        $tipoIdePropietario = '';
                        $numIdePropietario = '';
                        $NitPropietario = '';
                        $dvPropietario = '';
                        if ($value['idtipoidentificacionpropietario'] == '1' ||
                                $value['idtipoidentificacionpropietario'] == '3' ||
                                $value['idtipoidentificacionpropietario'] == '4' ||
                                $value['idtipoidentificacionpropietario'] == '5' ||
                                $value['idtipoidentificacionpropietario'] == 'E' ||
                                $value['idtipoidentificacionpropietario'] == 'P' ||
                                $value['idtipoidentificacionpropietario'] == 'R' ||
                                $value['idtipoidentificacionpropietario'] == 'V') {
                            $tipoIdePropietario = trim($value['idtipoidentificacionpropietario']);
                            $numIdePropietario = trim($value['identificacionpropietario']);
                            if (ltrim(trim($value['nitpropietario']), "0") != '') {
                                $sepide = \funcionesGenerales::separarDv($value['nitpropietario']);
                                $NitPropietario = $sepide["identificacion"];
                                $dvPropietario = $sepide["dv"];
                            }
                        }
                        if ($value['idtipoidentificacionpropietario'] == '2') {
                            $tipoIdePropietario = trim($value['idtipoidentificacionpropietario']);
                            if (ltrim(trim($value['nitpropietario']), "0") != '') {
                                $sepide = \funcionesGenerales::separarDv($value['nitpropietario']);
                                $NitPropietario = $sepide["identificacion"];
                                $dvPropietario = $sepide["dv"];
                                $numIdePropietario = $NitPropietario;
                            } else {
                                if (ltrim(trim($value['identificacionpropietario']), "0") != '') {
                                    $sepide = \funcionesGenerales::separarDv($value['identificacionpropietario']);
                                    $NitPropietario = $sepide["identificacion"];
                                    $dvPropietario = $sepide["dv"];
                                    $numIdePropietario = $NitPropietario;
                                }
                            }
                        }
                        $arrDatosPropietarios[$key - 1]['codigo_clase_identificacion_propietario'] = homologacionTipoIdentificacionRUESApi($tipoIdePropietario);
                        if ($tipoIdePropietario == 'V') {
                            $idepropenviar = $numIdePropietario;
                        } else {
                            $idepropenviar = str_pad(trim($numIdePropietario), 14, "0", STR_PAD_LEFT);
                        }
                        $arrDatosPropietarios[$key - 1]['numero_identificacion_propietario'] = $idepropenviar;
                        if (trim($NitPropietario) != '') {
                            $arrDatosPropietarios[$key - 1]['nit_propietario'] = $NitPropietario;
                            $arrDatosPropietarios[$key - 1]['digito_verificacion_propietario'] = $dvPropietario;
                        }
                        $arrDatosPropietarios[$key - 1]['codigo_camara_propietario'] = trim($value['camarapropietario']);
                        $arrDatosPropietarios[$key - 1]['matricula_propietario'] = homologacionMatriculaRUESApi($value['matriculapropietario']);
                        $arrDatosPropietarios[$key - 1]['razon_social_propietario'] = trim($value['nombrepropietario']);
                        $arrDatosPropietarios[$key - 1]['porcentaje_participacion'] = number_format($value['participacionpropietario'], 2);
                    }
                }
            }

            // En caso que no tenga propietarios vigentes
            if (empty($arrDatosPropietarios)) {
                if (isset($datos["propietariosh"])) {
                    if (count($datos["propietariosh"]) > 0) {
                        $arrPropietarios = (array) $datos["propietariosh"];
                        foreach ($arrPropietarios as $key => $value) {
                            $tipoIdePropietario = '';
                            $numIdePropietario = '';
                            $NitPropietario = '';
                            $dvPropietario = '';
                            if ($value['idtipoidentificacionpropietario'] == '1' ||
                                    $value['idtipoidentificacionpropietario'] == '3' ||
                                    $value['idtipoidentificacionpropietario'] == '4' ||
                                    $value['idtipoidentificacionpropietario'] == '5' ||
                                    $value['idtipoidentificacionpropietario'] == 'E' ||
                                    $value['idtipoidentificacionpropietario'] == 'P' ||
                                    $value['idtipoidentificacionpropietario'] == 'R' ||
                                    $value['idtipoidentificacionpropietario'] == 'V') {
                                $tipoIdePropietario = trim($value['idtipoidentificacionpropietario']);
                                $numIdePropietario = trim($value['identificacionpropietario']);
                                if (ltrim(trim($value['nitpropietario']), "0") != '') {
                                    $sepide = \funcionesGenerales::separarDv($value['nitpropietario']);
                                    $NitPropietario = $sepide["identificacion"];
                                    $dvPropietario = $sepide["dv"];
                                }
                            }
                            if ($value['idtipoidentificacionpropietario'] == '2') {
                                $tipoIdePropietario = trim($value['idtipoidentificacionpropietario']);
                                if (ltrim(trim($value['nitpropietario']), "0") != '') {
                                    $sepide = \funcionesGenerales::separarDv($value['nitpropietario']);
                                    $NitPropietario = $sepide["identificacion"];
                                    $dvPropietario = $sepide["dv"];
                                    $numIdePropietario = $NitPropietario;
                                } else {
                                    if (ltrim(trim($value['identificacionpropietario']), "0") != '') {
                                        $sepide = \funcionesGenerales::separarDv($value['identificacionpropietario']);
                                        $NitPropietario = $sepide["identificacion"];
                                        $dvPropietario = $sepide["dv"];
                                        $numIdePropietario = $NitPropietario;
                                    }
                                }
                            }
                            $arrDatosPropietarios[0]['codigo_clase_identificacion_propietario'] = homologacionTipoIdentificacionRUESApi($tipoIdePropietario);
                            if ($tipoIdePropietario == 'V') {
                                $idepropenviar = $numIdePropietario;
                            } else {
                                $idepropenviar = str_pad(trim($numIdePropietario), 14, "0", STR_PAD_LEFT);
                            }
                            $arrDatosPropietarios[0]['numero_identificacion_propietario'] = $idepropenviar;
                            if (trim($NitPropietario) != '') {
                                $arrDatosPropietarios[0]['nit_propietario'] = $NitPropietario;
                                $arrDatosPropietarios[0]['digito_verificacion_propietario'] = $dvPropietario;
                            }
                            $arrDatosPropietarios[0]['codigo_camara_propietario'] = trim($value['camarapropietario']);
                            $arrDatosPropietarios[0]['matricula_propietario'] = homologacionMatriculaRUESApi($value['matriculapropietario']);
                            $arrDatosPropietarios[0]['razon_social_propietario'] = trim($value['nombrepropietario']);
                            $arrDatosPropietarios[0]['porcentaje_participacion'] = number_format($value['participacionpropietario'], 2);
                        }
                    }
                }
            }
        }

        /*
         * Parámetros Propietarios de Sucursales y agencias  
         */
        if (($datos['categoria'] == '2') || ($datos['categoria'] == '3')) {
            $sepide = \funcionesGenerales::separarDv($datos["cpnumnit"]);
            if ($sepide["dv"] == substr($datos["cpnumnit"], -1, 1)) {
                $tipoIdePropietario = '2';
                $numIdePropietario = $sepide["identificacion"];
                $NitPropietario = $sepide["identificacion"];
                $dvPropietario = $sepide["dv"];
            } else {
                $tipoIdePropietario = '2';
                $numIdePropietario = $datos["cpnumnit"];
                $NitPropietario = $datos["cpnumnit"];
                $dvPropietario = \funcionesGenerales::calcularDv($datos["cpnumnit"]);
            }

            $arrDatosPropietarios[0]['codigo_clase_identificacion_propietario'] = homologacionTipoIdentificacionRUESApi('2');
            $arrDatosPropietarios[0]['numero_identificacion_propietario'] = str_pad(trim($numIdePropietario), 14, "0", STR_PAD_LEFT);
            if (trim($NitPropietario) != '') {
                $arrDatosPropietarios[0]['nit_propietario'] = $NitPropietario;
                $arrDatosPropietarios[0]['digito_verificacion_propietario'] = $dvPropietario;
            }
            $arrDatosPropietarios[0]['codigo_camara_propietario'] = trim($datos['cpcodcam']);
            $arrDatosPropietarios[0]['matricula_propietario'] = homologacionMatriculaRUESApi($datos['cpnummat']);
            $arrDatosPropietarios[0]['razon_social_propietario'] = trim($datos['cprazsoc']);
            $arrDatosPropietarios[0]['porcentaje_participacion'] = 0;
        }



        /*
         * Parámetros Vinculos 
         */
        if (isset($datos["vinculos"])) {
            if (count($datos["vinculos"]) > 0) {
                $arrVinculos = (array) $datos["vinculos"];
                foreach ($arrVinculos as $key => $value) {
                    $arrDatosVinculos[$key - 1]['tipo_identificacion'] = homologacionTipoIdentificacionRUESApi($value['idtipoidentificacionotros']);
                    if ($value['idtipoidentificacionotros'] == 'V') {
                        $idevinenviar = $value['identificacionotros'];
                    } else {
                        $idevinenviar = str_pad(trim($value['identificacionotros']), 14, "0", STR_PAD_LEFT);
                    }
                    $arrDatosVinculos[$key - 1]['numero_identificacion'] = $idevinenviar;
                    $arrDatosVinculos[$key - 1]['nombre'] = $value["nombreotros"];
                    $arrDatosVinculos[$key - 1]['nombre1'] = $value["nombre1otros"];
                    $arrDatosVinculos[$key - 1]['nombre2'] = $value["nombre2otros"];
                    $arrDatosVinculos[$key - 1]['apellido1'] = $value["apellido1otros"];
                    $arrDatosVinculos[$key - 1]['apellido2'] = $value["apellido2otros"];
                    $arrDatosVinculos[$key - 1]['detalle_vinculos'][0] = array('codigo_tipo_vinculo' => homologacionCodigoVinculoRUESApi($datos["organizacion"], $value['vinculootros']));
                }
            }
        }

        /*
         * Parámetros Histórico de Pagos (últimos 5 años)
         */


        $servsmat = array();
        $servsren = array();
        $servsafi = array();
        $servsben = array();
        $consulta = "";
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "tipoingreso IN ('02','03','13','31','85')", "idservicio");
        foreach ($temx as $tx) {
            if ($tx["tipoingreso"] == '02') {
                $servsmat[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '03' || $tx["tipoingreso"] == '13') {
                $servsren[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '31') {
                $servsafi[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '85') {
                $servsben[$tx["idservicio"]] = $tx["idservicio"];
            }

            if ($consulta != '') {
                $consulta .= ",";
            }
            $consulta .= "'" . $tx["idservicio"] . "'";
        }

        $res = retornarRegistrosMysqliApi($mysqli, "mreg_est_recibos", "matricula='" . $datos["matricula"] . "' and servicio IN (" . $consulta . ")", "fecoperacion desc");
        $valorPagadoMatricula = 0;
        $valorPagadoRenovacion = 0;

        if ($res && !empty($res)) {
            $sech = 0;

            foreach ($res as $rs) {
                $incluir = 'no';
                if ($rs["ctranulacion"] != '1' && $rs["ctranulacion"] != '2') {
                    if (substr($rs["numerorecibo"], 0, 1) == 'R' || substr($rs["numerorecibo"], 0, 1) == 'S') {
                        $incluir = 'si';
                    }
                }

                //
                if ($incluir == 'si') {
                    if ($rs["valor"] == 0) {
                        $incluir = 'no';
                    }
                }

                if ($incluir == 'si') {
                    $ctp = '';
                    $arp = '';
                    if (isset($servsmat[$rs["servicio"]])) {
                        $ctp = '01';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                        if ($valorPagadoMatricula == '') {
                            $valorPagadoMatricula = $rs["valor"];
                        }
                    }
                    if (isset($servsren[$rs["servicio"]])) {
                        if ($rs["fecharenovacionaplicable"] != '') {
                            $fren = $rs["fecharenovacionaplicable"];
                        } else {
                            $fren = $rs["fecoperacion"];
                        }
                        $ctp = '02';
                        $arp = $rs["anorenovacion"];
                        if ($rs["servicio"] == "00000510") {
                            $arp = substr($fren, 0, 4);
                        }
                        if ($rs["servicio"] == "00000710") {
                            $arp = '';
                        }
                        if ($valorPagadoRenovacion == '') {
                            $valorPagadoRenovacion = $rs["valor"];
                        }
                    }
                    if (isset($servsafi[$rs["servicio"]])) {
                        $ctp = '03';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                    }
                    if (isset($servsben[$rs["servicio"]])) {
                        $ctp = '04';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                    }

                    //
                    if ($ctp != '') {
                        if ($rs["fecharenovacionaplicable"] != '') {
                            $fren = $rs["fecharenovacionaplicable"];
                        } else {
                            $fren = $rs["fecoperacion"];
                        }
                        $arrDatosHistoricoPagos[$sech]['codigo_tipo_pago'] = $ctp;
                        $arrDatosHistoricoPagos[$sech]['ano'] = $arp;
                        $arrDatosHistoricoPagos[$sech]['fecha'] = $fren;
                        $arrDatosHistoricoPagos[$sech]['valor_base'] = $rs["base"];
                        $arrDatosHistoricoPagos[$sech]['valor_pagado'] = $rs["valor"];
                        $sech++;
                    }
                }
            }
        }




        /*
         * Validaciones Campo Objeto Social
         */
        if (isset($datos["crtsii"]["0740"])) {
            $objetoSocial = strip_tags($datos["crtsii"]["0740"]);
        } else {
            if (isset($datos["crt"]["0740"])) {
                $objetoSocial = strip_tags($datos["crt"]["0740"]);
            } else {
                $objetoSocial = '';
            }
        }

        /*
         * Validaciones Campo Juegos Suerte Azar (CIIU=R9200) 
         */
        $indicadorJuegosSuerteAzar = 'N';
        if (isset($datos["ciius"])) {
            if (in_array("R9200", $datos["ciius"])) {
                $indicadorJuegosSuerteAzar = 'S';
            }
        }

        /*
         * Validaciones Campo Transporte Carga
         */
        $indicadorTransporteCarga = 'N';
        if (isset($datos["inscripciones"])) {
            foreach ($datos["inscripciones"] as $key => $value) {
                if ($value['acto'] == '0800' || $value['acto'] == '0801') {
                    $indicadorTransporteCarga = 'S';
                }
            }
        }

        /*
         * Validaciones Campo Facultades 
         * Obtiene de tabla mreg_certificas_sii los certificas 1300 y 1500
         */
        $facultades = '';
        if (isset($datos["crtsii"]["1300"])) {
            $facultades = $datos["crtsii"]["1300"];
            if (isset($datos["crtsii"]["1500"])) {
                $facultades .= " | " . $datos["crtsii"]["1500"];
            }
        } else {
            if (isset($datos["crt"]["1300"])) {
                $facultades = $datos["crt"]["1300"];
                if (isset($datos["crt"]["1500"])) {
                    $facultades .= " | " . $datos["crt"]["1500"];
                }
            }
        }


        /*
         * Validaciones Zona Notificación Comercial
         */
        $indicadorZonaNotificacionComercial = 1;
        if ($datos["codigozonacom"] == 'U') {
            $indicadorZonaNotificacionComercial = 1; //URBANA
        }
        if ($datos["codigozonacom"] == 'R') {
            $indicadorZonaNotificacionComercial = 2; //RURAL
        }

        /*
         * Validaciones Zona Notificación Judicial
         */
        $indicadorZonaNotificacionJudicial = '';
        if ($datos["codigozonanot"] == 'U') {
            $indicadorZonaNotificacionJudicial = 1; //URBANA
        }
        if ($datos["codigozonanot"] == 'R') {
            $indicadorZonaNotificacionJudicial = 2; //RURAL
        }

        /*
         * Validaciones Notificación Email
         */
        $valorCtrmennot = substr($datos["ctrmennot"], 0, 1);
        $indicadorNotificacionEmail = \funcionesGenerales::homologarBoleeano($valorCtrmennot);

        /*
         * Validaciones Etnia
         */
        $cualEtnia = '';
        $indicadorEtnia = \funcionesGenerales::homologarBoleeano($datos["ctresaetnia"]);
        if ($indicadorEtnia == 'S') {
            $cualEtnia = $datos["ctresacualetnia"];
        }

        /*
         * Validaciones Reinsertado
         */

        $cualReinsertado = '';
        $indicadorReinsertado = \funcionesGenerales::homologarBoleeano($datos["ctresadespvictreins"]);
        if ($indicadorReinsertado == 'S') {
            $cualReinsertado = $datos["ctresacualdespvictreins"];
        }

        /*
         * Validaciones Tipo Propiedad - Establecimientos
         */
        $indicadorTipoPropiedad = '';
        if ($datos['organizacion'] == '02') {
            if ($datos["tipolocal"] == '1') {
                $indicadorTipoPropiedad = 1; //PROPIO
            }
            if ($datos["tipolocal"] == '0') {
                $indicadorTipoPropiedad = 2; //AJENO
            }
        }


        /*
         * Parámetros Finales consumo 
         */

        // Inicialización del arreglo
        $parametros = array();
        $parametros['codigo_error'] = "";
        $parametros['mensaje_error'] = "";
        $parametros['numero_interno'] = "";
        $parametros['usuario'] = "";
        $parametros['codigo_camara'] = "";
        $parametros['matricula'] = "";
        $parametros['inscripcion_proponente'] = "";
        $parametros['razon_social'] = "";
        $parametros['sigla'] = "";
        $parametros['primer_apellido'] = "";
        $parametros['segundo_apellido'] = "";
        $parametros['primer_nombre'] = "";
        $parametros['segundo_nombre'] = "";
        $parametros['genero'] = "";
        $parametros['codigo_clase_identificacion'] = '';
        $parametros['numero_identificacion'] = '00000000000000';
        $parametros['nit'] = '';
        $parametros['digito_verificacion'] = '';
        $parametros['fecha_expedicion'] = "";
        $parametros['lugar_expedicion'] = "";
        $parametros['pais_expedicion'] = "";
        $parametros['num_id_trib_ep'] = "";
        $parametros['pais_origen'] = "";
        $parametros['num_id_trib_ep'] = "";
        $parametros['direccion_comercial'] = "";
        $parametros['codigo_ubicacion_empresa'] = "";
        $parametros['codigo_zona_comercial'] = "";
        $parametros['codigo_postal_comercial'] = "";
        $parametros['municipio_comercial'] = "";
        $parametros['barrio_comercial'] = "";
        $parametros['telefono_comercial_1'] = "";
        $parametros['telefono_comercial_2'] = "";
        $parametros['telefono_comercial_3'] = "";
        $parametros['correo_electronico_comercial'] = "";
        $parametros['direccion_fiscal'] = "";
        $parametros['codigo_zona_fiscal'] = "";
        $parametros['codigo_postal_fiscal'] = "";
        $parametros['municipio_fiscal'] = "";
        $parametros['barrio_fiscal'] = "";
        $parametros['telefono_fiscal_1'] = "";
        $parametros['telefono_fiscal_2'] = "";
        $parametros['telefono_fiscal_3'] = "";
        $parametros['correo_electronico_fiscal'] = "";
        $parametros['codigo_sede_administrativa'] = "";
        $parametros['autorizacion_envio_correo_electronico'] = "";
        $parametros['objeto_social'] = "";
        $parametros['cod_ciiu_act_econ_pri'] = "";
        $parametros['fecha_inicio_act_econ_pri'] = "";
        $parametros['cod_ciiu_act_econ_sec'] = "";
        $parametros['fecha_inicio_act_econ_sec'] = "";
        $parametros['ciiu3'] = "";
        $parametros['ciiu4'] = "";
        $parametros['ciiu_mayores_ingresos'] = "";
        $parametros['clasificacion_imp_exp'] = "";
        $parametros['empresa_familiar'] = "";
        $parametros['proceso_innovacion'] = "";
        $parametros['url'] = "";
        $parametros['empleados'] = "";
        $parametros['porcentaje_empleados_temporales'] = "0.00";
        $parametros['cantidad_cargos_directivos'] = "";
        $parametros['cantidad_mujeres_empleadas'] = "";
        $parametros['cantidad_mujeres_cargos_directivos'] = 0;
        $parametros['porcentaje_participacion_capital_mujeres'] = "0.00";
        $parametros['codigo_tamano_empresa'] = "00";
        $parametros['codigo_estado_matricula'] = "";
        $parametros['fecha_matricula'] = "";
        $parametros['fecha_constitucion'] = "";
        $parametros['fecha_renovacion'] = "";
        $parametros['ultimo_ano_renovado'] = "";
        $parametros['valor_pagado_renovacion'] = "";
        $parametros['valor_pagado_matricula'] = "";
        $parametros['fecha_vigencia'] = "";
        $parametros['fecha_cancelacion'] = "";
        $parametros['codigo_motivo_cancelacion'] = "";
        $parametros['codigo_tipo_sociedad'] = "";
        $parametros['codigo_organizacion_juridica'] = "";
        $parametros['codigo_categoria_matricula'] = "";
        $parametros['indicador_vendedor_juegos_suerte_azar'] = "";
        $parametros['indicador_empresa_bic'] = "";
        $parametros['indicador_transporte_de_carga'] = "";
        $parametros['indicador_emprendimiento_social'] = "";
        $parametros['emprendimiento_social_categorias'] = "";
        $parametros['emprendimiento_social_beneficiarios'] = "";
        $parametros['afiliado'] = "";
        $parametros['codigo_estado_persona_juridica'] = "";
        $parametros['control_inactivacion_sipref'] = "";
        $parametros['codigo_estado_liquidacion'] = "";
        $parametros['fecha_decreta_disolucion'] = "";
        $parametros['motivo_disolucion'] = "";
        $parametros['indicador_beneficio_ley_1429'] = "";
        $parametros['indicador_beneficio_ley_1780'] = "";
        $parametros['indicador_aportante_seguridad_social'] = "";
        $parametros['tipo_aportante_seguridad_social'] = "0";
        $parametros['tipo_propietario'] = "";
        $parametros['codigo_tipo_local'] = "";
        $parametros['grupo_empresarial_tipo'] = "";
        $parametros['grupo_empresarial_nombre'] = "";
        $parametros['facultades'] = "";
        $parametros['informacion_financiera'] = array();
        $parametros['informacion_capitales'] = array();
        $parametros['capital_social_nacional_publico'] = "";
        $parametros['capital_social_nacional_privado'] = "";
        $parametros['capital_social_extranjero_publico'] = "";
        $parametros['capital_social_extranjero_privado'] = "";
        $parametros['datos_propietarios'] = array();
        $parametros['vinculo'] = array();
        $parametros['datos_esadl'] = array();
        $parametros['historico_pagos'] = array();
        $parametros["historico_razon_social"] = array();
        $parametros["informacion_adicional"] = array();
        $parametros['esal_numero_asociados'] = "";
        $parametros['esal_numero_mujeres'] = "";
        $parametros['esal_numero_hombres'] = "";
        $parametros['esal_indicador_pertenencia_gremio'] = "";
        $parametros['esal_nombre_gremio'] = "";
        $parametros['esal_entidad_acreditada'] = "";
        $parametros['esal_entidad_ivc'] = "";
        $parametros['esal_ha_remitido_info_ivc'] = "";
        $parametros['esal_autorizacion_registro'] = "";
        $parametros['esal_entidad_autoriza'] = "";
        $parametros['esal_codigo_naturaleza'] = "";
        $parametros['esal_codigo_tipo_entidad'] = "";
        $parametros['esal_ed_discapacidad'] = "";
        $parametros['esal_ed_etnia'] = "";
        $parametros['esal_ed_etnia_cual'] = "";
        $parametros['esal_ed_lgbti'] = "";
        $parametros['esal_ed_desp_vict_reins'] = "";
        $parametros['esal_ed_desp_vict_reins_cual'] = "";
        $parametros['esal_indicador_gestion'] = "";
        $parametros["indicadores"] = array();
        $indicadores = array();

        // Llenado del arreglo
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"], 29, "0", STR_PAD_RIGHT);
        $parametros['usuario'] = $_SESSION["generales"]["codigousuario"];
        $parametros['codigo_camara'] = $_SESSION["generales"]["codigoempresa"];
        $parametros['matricula'] = homologacionMatriculaRUESApi($datos["matricula"]);
        $parametros['inscripcion_proponente'] = str_pad($datos["proponente"], 12, "0", STR_PAD_LEFT);
        $parametros['razon_social'] = $datos["nombrerues"];
        $parametros['sigla'] = "";
        $parametros['primer_apellido'] = "";
        $parametros['segundo_apellido'] = "";
        $parametros['primer_nombre'] = "";
        $parametros['segundo_nombre'] = "";
        $parametros['genero'] = "";
        if ($datos["organizacion"] == '01') {
            $parametros['primer_apellido'] = $datos["ape1"];
            $parametros['segundo_apellido'] = $datos["ape2"];
            $parametros['primer_nombre'] = $datos["nom1"];
            $parametros['segundo_nombre'] = $datos["nom2"];
        }
        $parametros['sigla'] = $datos["sigla"];
        if ($datos["organizacion"] == '01') {
            if ($datos["sexo"] != '') {
                $parametros['genero'] = $datos["sexo"];
            }
        }

        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            $parametros['codigo_clase_identificacion'] = homologacionTipoIdentificacionRUESApi($tipoIdentificacion);
            if ($tipoIdentificacion == '09') {
                if (strlen($numIdentificacion) > 14) {
                    $ideenviar = $numIdentificacion;
                } else {
                    $ideenviar = str_pad($numIdentificacion, 14, "0", STR_PAD_LEFT);
                }
            } else {
                $ideenviar = str_pad($numIdentificacion, 14, "0", STR_PAD_LEFT);
            }
            $parametros['numero_identificacion'] = $ideenviar;
            $parametros['nit'] = $Nit;
            $parametros['digito_verificacion'] = $dv;
        }
        if ($datos["organizacion"] == '01') {
            $parametros['fecha_expedicion'] = $datos["fecexpdoc"];
            $parametros['lugar_expedicion'] = $datos["idmunidoc"];
            $parametros['pais_expedicion'] = $datos["paisexpdoc"];
        }
        if (ltrim($tipoIdentificacion, "0") == '3' || ltrim($tipoIdentificacion, "0") == '5') {
            $parametros['num_id_trib_ep'] = $datos["idetripaiori"];
            $parametros['pais_origen'] = $datos["paiori"];
            $parametros['num_id_trib_ep'] = $datos["idetriextep"];
        }

        $parametros['direccion_comercial'] = $datos["dircom"];
        $parametros['codigo_ubicacion_empresa'] = $datos["ctrubi"];
        $parametros['codigo_zona_comercial'] = $indicadorZonaNotificacionComercial; //1 o 2
        $parametros['codigo_postal_comercial'] = $datos["codigopostalcom"];
        $parametros['municipio_comercial'] = empty($datos["muncom"]) ? '99999' : $datos["muncom"];
        $parametros['barrio_comercial'] = "";
        $parametros['telefono_comercial_1'] = $datos["telcom1"];
        $parametros['telefono_comercial_2'] = $datos["telcom2"];
        $parametros['telefono_comercial_3'] = $datos["celcom"];
        $parametros['correo_electronico_comercial'] = $datos["emailcom"];
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            $parametros['direccion_fiscal'] = $datos["dirnot"];
            $parametros['codigo_zona_fiscal'] = $indicadorZonaNotificacionJudicial; //1 o 2
            $parametros['codigo_postal_fiscal'] = $datos["codigopostalnot"];
            $parametros['municipio_fiscal'] = empty($datos["munnot"]) ? '99999' : $datos["munnot"];
            $parametros['barrio_fiscal'] = "";
            $parametros['telefono_fiscal_1'] = $datos["telnot"];
            $parametros['telefono_fiscal_2'] = $datos["telnot2"];
            $parametros['telefono_fiscal_3'] = $datos["celnot"];
            $parametros['correo_electronico_fiscal'] = $datos["emailnot"];
        }
        if (!isset($datos["tiposedeadm"]) && $datos["tiposedeadm"] != '' && $datos["tiposedeadm"] != null && $datos["tiposedeadm"] != '0') {
            $parametros['codigo_sede_administrativa'] = $datos["tiposedeadm"];
        }
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            $parametros['autorizacion_envio_correo_electronico'] = $indicadorNotificacionEmail; //S o N
        }
        $parametros['objeto_social'] = base64_encode($objetoSocial);
        $parametros['cod_ciiu_act_econ_pri'] = empty($datos["ciius"][1]) ? '9999' : substr($datos["ciius"][1], 1);
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            $parametros['fecha_inicio_act_econ_pri'] = $datos["feciniact1"]; //YYYMMDD
        } else {
            $parametros['fecha_inicio_act_econ_pri'] = '00000000'; //YYYMMDD
        }
        $parametros['cod_ciiu_act_econ_sec'] = empty($datos["ciius"][2]) ? '' : substr($datos["ciius"][2], 1);
        if ($datos["ciius"][2] != '') {
            if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
                $parametros['fecha_inicio_act_econ_sec'] = $datos["feciniact2"]; //YYYMMDD
            } else {
                $parametros['fecha_inicio_act_econ_sec'] = '00000000'; //YYYYMMDD
            }
        } else {
            $parametros['fecha_inicio_act_econ_sec'] = ''; //YYYYMMDD
        }
        $parametros['ciiu3'] = empty($datos["ciius"][3]) ? '' : substr($datos["ciius"][3], 1);
        $parametros['ciiu4'] = empty($datos["ciius"][4]) ? '' : substr($datos["ciius"][4], 1);
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            if (isset($datos["ciiutamanoempresarial"]) || $datos["ciiutamanoempresarial"] != '') {
                $parametros['ciiu_mayores_ingresos'] = substr($datos["ciiutamanoempresarial"], 1);
            }
        }
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            $parametros['clasificacion_imp_exp'] = $indicadorImpExp;
        }
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            if ($datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
                $parametros['empresa_familiar'] = \funcionesGenerales::homologarBoleeano($datos["empresafamiliar"]);
            }
            $parametros['proceso_innovacion'] = \funcionesGenerales::homologarBoleeano($datos["procesosinnovacion"]);
        }
        $parametros['url'] = filter_var($datos["urlcom"], FILTER_VALIDATE_URL) ? $datos["urlcom"] : '';
        $parametros['empleados'] = empty($datos["personal"]) ? '0' : $datos["personal"];
        if (!isset($datos["cargosdirectivos"])) {
            $datos["cargosdirectivos"] = '';
        }
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            if ($datos["cargosdirectivos"] != '') {
                $parametros['cantidad_cargos_directivos'] = $datos["cargosdirectivos"];
            }
        }
        if (
                $datos["organizacion"] == '01' ||
                ($datos["organizacion"] > '02' && $datos["categoria"] == '1')
        ) {
            if ($datos["cantidadmujeres"] != '') {
                $parametros['cantidad_mujeres_empleadas'] = $datos["cantidadmujeres"];
            }
        }
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            if ($datos["cantidadmujerescargosdirectivos"] != '') {
                $parametros['cantidad_mujeres_cargos_directivos'] = $datos["cantidadmujerescargosdirectivos"];
            }
        }
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            $parametros['porcentaje_participacion_capital_mujeres'] = number_format($datos["participacionmujeres"], 2);
        }
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            $parametros['codigo_tamano_empresa'] = str_pad($datos["tamanoempresarial957codigo"], 2, "0", STR_PAD_LEFT); //HOMOLOGACION BAS_TAMANO_EMPRESA 
        }
        $parametros['codigo_estado_matricula'] = homologacionEstadoMatriculaRUESApi($datos["estadomatricula"]); //HOMOLOGACION BAS_ESTADO_MATRICULA
        if ($parametros['codigo_estado_matricula'] == '01') {
            foreach ($datos["inscripciones"] as $inscx) {
                if ($inscx["acto"] == '0042') {
                    $parametros['codigo_estado_matricula'] == '07';
                }
                if ($inscx["acto"] == '9990') {
                    $parametros['codigo_estado_matricula'] == '07';
                }
            }
        }
        $parametros['fecha_matricula'] = $fechaMatricula;
        $parametros['fecha_constitucion'] = $fechaConstitucion;
        $parametros['fecha_renovacion'] = $fechaRenovacion;
        $parametros['ultimo_ano_renovado'] = $ultAnoRenovado;
        $parametros['valor_pagado_renovacion'] = $valorPagadoRenovacion;
        $parametros['valor_pagado_matricula'] = $valorPagadoMatricula;
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            $parametros['fecha_vigencia'] = $fechaVigencia;
        }
        if ($datos["estadomatricula"] == 'MC' || $datos["estadomatricula"] == 'MF' || $datos["estadomatricula"] == 'IC' || $datos["estadomatricula"] == 'IF') {
            $parametros['fecha_cancelacion'] = $fechaCancelacion;
            $parametros['codigo_motivo_cancelacion'] = homologacionMotivoCancelacionRUESApi($indicadorMotivoCancelacion); //HOMOLOGACION BAS_MOTIVOS_CANCELACION        
        }
        $parametros['codigo_tipo_sociedad'] = homologacionSociedadRUESApi($datos['organizacion'], $datos['claseespesadl'], $datos["ciius"]); //HOMOLOGACION 
        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
            $parametros['codigo_organizacion_juridica'] = homologacionOrganizacionEsadlRUESApi($mysqli, $datos['claseespesadl']);
        } else {
            $parametros['codigo_organizacion_juridica'] = homologacionOrganizacionRUESApi($datos['organizacion'], $datos['clasegenesadl'], $datos['claseespesadl'], $datos['claseeconsoli']);
        }
        $parametros['codigo_categoria_matricula'] = homologacionCategoriaRUESApi($datos['organizacion'], $datos['categoria']); //HOMOLOGACION BAS_CATEGORIA_MATRICULA
        $parametros['indicador_vendedor_juegos_suerte_azar'] = $indicadorJuegosSuerteAzar;
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
            if (($datos["ctrbic"] != '') && ($datos["ctrbic"] == 'S')) {
                $parametros['indicador_empresa_bic'] = "S";
            } else {
                $parametros['indicador_empresa_bic'] = "N";
            }
        }
        $parametros['indicador_transporte_de_carga'] = $indicadorTransporteCarga;
        if ($datos["emprendimientosocial"] == '') {
            $datos["emprendimientosocial"] = 'N';
        }
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            $parametros['indicador_emprendimiento_social'] = $datos["emprendimientosocial"]; // S o N o vacio
            $parametros['emprendimiento_social_categorias'] = $datos["empsoccategorias"];
            $parametros['emprendimiento_social_beneficiarios'] = $datos["empsocbeneficiarios"];
        }
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && ($datos["categoria"] == '1' || $datos["categoria"] == '2'))) {
            $parametros['afiliado'] = $indicadorAfiliado;
        }
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            $parametros['codigo_estado_persona_juridica'] = '01';
        }
        $parametros['control_inactivacion_sipref'] = 'N';
        // if ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            $parametros['codigo_estado_liquidacion'] = sprintf("%02s", homologacionEstadoLiquidacionRUESApi($datos["estadotipoliquidacion"]));
        }
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            if ($datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
                $parametros['indicador_beneficio_ley_1429'] = \funcionesGenerales::homologarBoleeano($datos["art7"]);
                if ($parametros['indicador_beneficio_ley_1429'] == '') {
                    $parametros['indicador_beneficio_ley_1429'] = 'N';
                }
            }
            if ($datos["benley1780"] == '') {
                $datos["benley1780"] = 'N';
            }
            if ($datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
                $parametros['indicador_beneficio_ley_1780'] = \funcionesGenerales::homologarBoleeano($datos["benley1780"]);
            }
            $parametros['indicador_aportante_seguridad_social'] = $datos["aportantesegsocial"];
            $parametros['tipo_aportante_seguridad_social'] = $datos["tipoaportantesegsocial"]; //REVISAR HOMOLOGACION BAS_TIPO_APORTANTE
        }
        if ($datos["organizacion"] == '02') {
            if (count($arrDatosPropietarios) == 1) {
                $parametros['tipo_propietario'] = '1';
            } else {
                if ($datos["tipopropiedad"] == '' || $datos["tipopropiedad"] == '0') {
                    $parametros['tipo_propietario'] = '1';
                } else {
                    $parametros['tipo_propietario'] = homologacionTipoPropietarioRUESApi($datos["tipopropiedad"]);
                }
            }
            $parametros['codigo_tipo_local'] = $indicadorTipoPropiedad;
        }
        if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1')) {
            $parametros['grupo_empresarial_tipo'] = $datos["tipogruemp"];
            $parametros['grupo_empresarial_nombre'] = $datos["nombregruemp"];
        }
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            $parametros['facultades'] = base64_encode($facultades);
        }
        $parametros['informacion_financiera'] = $arrDatosInfoFinanciera;
        $parametros['informacion_capitales'] = $arrDatosInfoCapitales;
        if ($datos["organizacion"] > '02' && $datos["categoria"] == '1') {
            $parametros['capital_social_nacional_publico'] = $datos["cap_porcnalpub"];
            $parametros['capital_social_nacional_privado'] = $datos["cap_porcnalpri"];
            $parametros['capital_social_extranjero_publico'] = $datos["cap_porcextpub"];
            $parametros['capital_social_extranjero_privado'] = $datos["cap_porcextpri"];
        }
        // $parametros['grupo_niif'] = $indicadorGrupoNiif; // 0 - 7
        // $parametros['codigo_partidas_conciliatorias'] = $datos["niifconciliacion"];
        if ($datos["organizacion"] == '02' || $datos["categoria"] == '2' || $datos["categoria"] == '3') {
            $parametros['datos_propietarios'] = $arrDatosPropietarios;
        }

        $parametros['vinculo'] = $arrDatosVinculos;
        // $parametros['cantidad_establecimientos'] = $CantEstablecimientos;
        //Solo ESADL
        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
            if ($datos["categoria"] == '1') {

                if (!isset($datos["ctresacntasociados"]) || $datos["ctresacntasociados"] === null) {
                    $datos["ctresacntasociados"] = '0';
                }
                if (!isset($datos["ctresacntmujeres"]) || $datos["ctresacntmujeres"] === null) {
                    $datos["ctresacntmujeres"] = '0';
                }
                if (!isset($datos["ctresacnthombres"]) || $datos["ctresacnthombres"] === null) {
                    $datos["ctresacnthombres"] = '0';
                }

                if (!isset($datos["ctresacntasociados"]) || $datos["ctresacntasociados"] === null) {
                    $datos["ctresacntasociados"] = '0';
                }
                if (!isset($datos["ctresacntmujeres"]) || $datos["ctresacntmujeres"] === null) {
                    $datos["ctresacntmujeres"] = '0';
                }
                if (!isset($datos["ctresacnthombres"]) || $datos["ctresacnthombres"] === null) {
                    $datos["ctresacnthombres"] = '0';
                }
                if (!isset($datos["ctresapertgremio"]) || $datos["ctresapertgremio"] === null) {
                    $datos["ctresapertgremio"] = '';
                }
                if (!isset($datos["ctresagremio"]) || $datos["ctresagremio"] === null) {
                    $datos["ctresagremio"] = '';
                }
                if (!isset($datos["ctresaacredita"]) || $datos["ctresaacredita"] === null) {
                    $datos["ctresaacredita"] = '';
                }
                if (!isset($datos["ctresaivc"]) || $datos["ctresaivc"] === null) {
                    $datos["ctresaivc"] = '';
                }
                if (!isset($datos["ctresainfoivc"]) || $datos["ctresainfoivc"] === null) {
                    $datos["ctresainfoivc"] = '';
                }
                if (!isset($datos["ctresaautregistro"]) || $datos["ctresaautregistro"] === null) {
                    $datos["ctresaautregistro"] = '';
                }
                if (!isset($datos["ctresadiscap"]) || $datos["ctresadiscap"] === null) {
                    $datos["ctresadiscap"] = '';
                }
                if (!isset($datos["ctresadiscap"]) || $datos["ctresadiscap"] === null) {
                    $datos["ctresadiscap"] = '';
                }
                if (!isset($datos["ctresalgbti"]) || $datos["ctresalgbti"] === null) {
                    $datos["ctresalgbti"] = '';
                }
                if (!isset($datos["ctresaindgest"]) || $datos["ctresaindgest"] === null) {
                    $datos["ctresaindgest"] = '';
                }

                $indicadores[] = array(
                    'codigo_indicador' => '6000',
                    'valor_indicador' => $datos["ctresacntasociados"]
                );
                $indicadores[] = array(
                    'codigo_indicador' => '6010',
                    'valor_indicador' => $datos["ctresacntmujeres"]
                );
                $indicadores[] = array(
                    'codigo_indicador' => '6020',
                    'valor_indicador' => $datos["ctresacnthombres"]
                );

                $parametros['esal_numero_asociados'] = $datos["ctresacntasociados"];
                $parametros['esal_numero_mujeres'] = $datos["ctresacntmujeres"];
                $parametros['esal_numero_hombres'] = $datos["ctresacnthombres"];
                $parametros['esal_indicador_pertenencia_gremio'] = \funcionesGenerales::homologarBoleeano($datos["ctresapertgremio"]);
                $parametros['esal_nombre_gremio'] = $datos["ctresagremio"];
                $parametros['esal_entidad_acreditada'] = $datos["ctresaacredita"];
                $parametros['esal_entidad_ivc'] = $datos["ctresaivc"];
                $parametros['esal_ha_remitido_info_ivc'] = \funcionesGenerales::homologarBoleeano($datos["ctresainfoivc"]);
                $parametros['esal_autorizacion_registro'] = \funcionesGenerales::homologarBoleeano($datos["ctresaautregistro"]);
                $parametros['esal_entidad_autoriza'] = $datos["ctresaentautoriza"];
                $parametros['esal_codigo_naturaleza'] = homologacionNaturalezaEsadlRUESApi($datos['organizacion'], $datos["ctresacodnat"]);
                $parametros['esal_codigo_tipo_entidad'] = homologacionOrganizacionRUESApi($datos['organizacion'], $datos['clasegenesadl'], $datos['claseespesadl'], $datos['claseeconsoli']);
                $parametros['esal_ed_discapacidad'] = $datos["ctresadiscap"];
                $parametros['esal_ed_etnia'] = $indicadorEtnia;
                $parametros['esal_ed_etnia_cual'] = $cualEtnia;
                $parametros['esal_ed_lgbti'] = $datos["ctresalgbti"];
                $parametros['esal_ed_desp_vict_reins'] = $indicadorReinsertado;
                $parametros['esal_ed_desp_vict_reins_cual'] = $cualReinsertado;
                $parametros['esal_indicador_gestion'] = \funcionesGenerales::homologarBoleeano($datos["ctresaindgest"]);
            }
        }

        // extincion de dominio
        $indicadores[] = array(
            'codigo_indicador' => '1000',
            'valor_indicador' => $datos["extinciondominio"]
        );

        //  Indicador de control de propietario


        $parametros['historico_pagos'] = $arrDatosHistoricoPagos;
        $parametros["indicadores"] = $indicadores;

        /*
          $retorno["hashcontrolnuevo"] = date("Ymd") . '|' . md5(json_encode($retorno));
          $retorno["hashcontrol"] = $reg["hashcontrol"];
         */

        if (isset($datos["hashcontrolnuevo"])) {
            $sep = explode("|", $datos["hashcontrolnuevo"]);
        } else {
            $sep[1] = '';
        }

        if ($renovarhash) {
            $sep[1] = 'WS' . date('His');
        }

        //
        $parametros['hash_control'] = empty($sep[1]) ? '' : $sep[1];

        //
        if ($cerrarMysqli == 'si') {
            $mysqli->close();
        }

        // Validación de parámetros desde el SII
        if ($datos["organizacion"] == '02' || $datos["categoria"] == '2' || $datos["categoria"] == '3') {
            if (empty($arrDatosPropietarios)) {
                if ($datos["estadomatricula"] == 'MA' || $datos["estadomatricula"] == 'MI' || $datos["estadomatricula"] == 'IA' || $datos["estadomatricula"] == 'II' || $datos["estadomatricula"] == 'MR' || $datos["estadomatricula"] == 'IR') {
                    $parametros["codigo_error"] = '9999';
                    $parametros["mensaje_error"] = 'Establecimiento, sucursal o agencia sin propietarios';
                } else {
                    if ($datos["estadomatricula"] == 'MC' || $datos["estadomatricula"] == 'IC' || $datos["estadomatricula"] == 'MF' || $datos["estadomatricula"] == 'IF') {
                        if ($datos["ultanoren"] > '2016') {
                            $parametros["codigo_error"] = '9999';
                            $parametros["mensaje_error"] = 'Establecimiento, sucursal o agencia sin propietarios';
                        }
                    }
                }
            }
        }

        //
        unset($arrDatosInfoFinanciera);
        unset($arrDatosInfoCapitales);
        unset($arrDatosInfoAdicional);
        unset($arrDatosPropietarios);
        unset($arrDatosVinculos);
        unset($arrDatosHistoricoPagos);

        //
        // $sal = json_encode($parametros, JSON_PRETTY_PRINT);
        // \logApi::general2('construirParametrosRegMerV2_' . date("Ymd"), $datos["matricula"], $sal);
        return $parametros;
    }

    public static function construirParametrosRegPro($mysqli = null, $datos = array()) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/retornarHomologaciones.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysqli = 'si';
        } else {
            $cerrarMysqli = 'no';
        }


        if ($datos["dircom"] == '' || $datos["dirnot"] == '') {
            if ($datos["matricula"] !== '') {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $datos["matricula"] . "'");
                if ($exp && !empty($exp)) {
                    $datos["dircom"] = $exp["dircom"];
                    $datos["muncom"] = $exp["muncom"];
                    $datos["telcom"] = $exp["telcom"];
                    $datos["emailcom"] = $exp["emailcom"];
                    $datos["dirnot"] = $exp["dirnot"];
                    $datos["munnot"] = $exp["munnot"];
                    $datos["telnot"] = $exp["telnot"];
                    $datos["emailnot"] = $exp["emailnot"];
                    if ($datos["enviarint"] == '') {
                        $datos["enviarint"] = $exp["ctrnotsms"];
                    }
                }
            }
        } else {
            if ($datos["enviarint"] == '') {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $datos["matricula"] . "'");
                if ($exp && !empty($exp)) {
                    $datos["enviarint"] = $exp["ctrnotsms"];
                }
            }
        }

        // Inicialización del arreglo
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION["generales"]["codigoempresa"] . $_SESSION["generales"]["codigoempresa"], 29, "0", STR_PAD_RIGHT);
        $parametros['usuario'] = $_SESSION["generales"]["codigousuario"];

        $parametros['codigo_camara_proponente'] = $_SESSION["generales"]["codigoempresa"];
        $parametros['inscripcion_proponente'] = str_pad($datos["proponente"], 12, "0", STR_PAD_LEFT);

        $parametros['codigo_camara_matricula'] = "";
        $parametros['matricula'] = "";
        if ($datos["matricula"] != '') {
            $parametros['codigo_camara_matricula'] = $_SESSION["generales"]["codigoempresa"];
            $parametros['matricula'] = homologacionMatriculaRUESApi($datos["matricula"]);
        }

        $parametros['razon_social'] = $datos["nombre"];
        $parametros['primer_nombre'] = $datos["nom1"];
        $parametros['segundo_nombre'] = $datos["nom2"];
        $parametros['primer_apellido'] = $datos["ape1"];
        $parametros['segundo_apellido'] = $datos["ape2"];
        $parametros['sigla'] = $datos["sigla"];

        $parametros['codigo_clase_identificacion'] = \funcionesRues::homologarTipoIdentificacion($mysqli, $datos["tipoidentificacion"]);
        $parametros['numero_identificacion'] = "";
        $parametros['nit'] = "";
        $parametros['digito_verificacion'] = "";
        if ($datos["tipoidentificacion"] == '2') {
            $ide = \funcionesGenerales::separarDv($datos["identificacion"]);
            $parametros['numero_identificacion'] = sprintf("%014s", $ide["identificacion"]);
            $parametros['nit'] = $ide["identificacion"];
            $parametros['digito_verificacion'] = $ide["dv"];
        } else {
            $ide = \funcionesGenerales::separarDv($datos["nit"]);
            $parametros['numero_identificacion'] = sprintf("%014s", $datos["identificacion"]);
            $parametros['nit'] = $ide["identificacion"];
            $parametros['digito_verificacion'] = $ide["dv"];
        }
        $parametros['nacionalidad'] = "";
        $parametros['autorizacion_datos'] = $datos["enviarint"];
        $parametros['codigo_organizacion_juridica'] = $datos["organizacion"];
        $parametros['codigo_tamano_empresa'] = sprintf("%02s", $datos["tamanoempresa"]);
        $parametros['codigo_estado_proponente'] = $datos["idestadoproponente"];
        $parametros['fecha_inscripcion'] = !empty($datos["fechaultimainscripcion"]) ? $datos["fechaultimainscripcion"] : '00000000';
        $parametros['fecha_renovacion'] = !empty($datos["fechaultimarenovacion"]) ? $datos["fechaultimarenovacion"] : '00000000';
        if (($datos["idestadoproponente"] == '01') || ($datos["idestadoproponente"] == '03')) {
            $parametros['fecha_cancelacion'] = !empty($datos["fechacancelacion"]) ? $datos["fechacancelacion"] : '00000000';
        } else {
            $parametros['fecha_cancelacion'] = '00000000';
        }
        $parametros['tipo_documento_personeria_juridica'] = '';
        $parametros['numero_documento_personeria_juridica'] = '';
        // $parametros['numdocperjur'] = '';
        $parametros['fecha_documento_personeria_juridica'] = '';
        $parametros['origen_documento_personeria_juridica'] = '';
        $parametros['fecha_constitucion'] = '';
        $parametros['fecha_vencimiento'] = '';

        $parametros['direccion_comercial'] = $datos["dircom"];
        $parametros['ubicacion_comercial'] = '';
        $parametros['barrio_comercial'] = '';
        $parametros['municipio_comercial'] = $datos["muncom"];
        $parametros['telefono_comercial'] = $datos["telcom1"];
        $parametros['telefono_comercial2'] = $datos["telcom2"];
        $parametros['telefono_comercial3'] = $datos["celcom"];
        $parametros['correo_electronico_comercial'] = $datos["emailcom"];
        $parametros['zona_comercial'] = '';
        $parametros['apartado_aereo_comercial'] = '';

        $parametros['direccion_notificacion'] = $datos["dirnot"];
        $parametros['direccion_fiscal'] = $datos["dirnot"];
        $parametros['barrio_notificacion'] = '';
        $parametros['municipio_notificacion'] = $datos["munnot"];
        $parametros['municipio_fiscal'] = $datos["munnot"];
        $parametros['telefono_fiscal'] = $datos["telnot"];
        $parametros['telefono_fiscal2'] = $datos["telno2"];
        $parametros['telefono_fiscal3'] = $datos["celnot"];
        $parametros['correo_electronico'] = $datos["emailnot"];
        $parametros['zona_notificacion'] = '';
        $parametros['apartado_aereo_fiscal'] = '';

        $parametros['tipo_sede'] = '';

        $parametros['facultades'] = '';

        $parametros['representacion_legal'] = array();
        if (isset($datos["enfirme"]["representanteslegales"]) && !empty($datos["enfirme"]["representanteslegales"])) {
            foreach ($datos["enfirme"]["representanteslegales"] as $rlp) {
                $ren = array();
                $ren["codigo_clase_identificacion"] = \funcionesRues::homologarTipoIdentificacion($mysqli, $rlp["idtipoidentificacionrepleg"]);
                $ren["numero_identificacion"] = sprintf("%014s", $rlp["identificacionrepleg"]);
                $ren["razon_social"] = $rlp["nombrerepleg"];
                $ren["primer_nombre"] = $rlp["nom1"];
                $ren["segundo_nombre"] = $rlp["nom2"];
                $ren["primer_apellido"] = $rlp["ape1"];
                $ren["segundo_apellido"] = $rlp["ape2"];
                $ren["tipo_representacion"] = $rlp["cargorepleg"];
                $parametros['representacion_legal'][] = $ren;
            }
        }

        $parametros['situaciones_control'] = array();
        if (isset($datos["enfirme"]["sitcontrol"]) && count($datos["enfirme"]["sitcontrol"]) > 0) {
            $arrGrupos = $datos["enfirme"]["sitcontrol"];
            foreach ($arrGrupos as $value) {
                $ren = array();
                $ren['id_grupo'] = $key;
                $ren['nit'] = $value['identificacion'];
                $ren['nombre'] = $value['nombre'];
                $ren['domicilio'] = $value['domicilio'];
                switch ($value["tipo"]) {
                    case "0":
                        // MATRIZ
                        $ge_matriz = '0';
                        $sc_controlante = '';
                        break;
                    case "1":
                        // SUBORDINADA
                        $ge_matriz = '1';
                        $sc_controlante = '';
                        break;
                    case "2":
                        // CONTROLANTE
                        $ge_matriz = '';
                        $sc_controlante = '0';
                        break;
                    case "3":
                        // CONTROLADA
                        $ge_matriz = '';
                        $sc_controlante = '1';
                        break;
                    case "4":
                        // MATRIZ Y SUBORDINADA
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "5":
                        // MATRIZ Y CONTROLANTE
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "6":
                        // MATRIZ Y CONTROLADA
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "7":
                        // SUBORDINADA Y CONTROLANTE
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "8":
                        // SUBORDINADA Y CONTROLADA
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                    case "9":
                        // CONTROLANTE Y CONTROLADA
                        $ge_matriz = '';
                        $sc_controlante = '';
                        break;
                }
                $ren['tipo'] = $ge_matriz;
                $parametros['situaciones_control'][] = $ren;
            }
        }

        $parametros["informacion_financiera"] = array();
        $parametros["informacion_financiera"]['fecha_corte_informacion_financiera'] = !empty($datos["enfirme"]["inffin1510_fechacorte"]) ? $datos["enfirme"]["inffin1510_fechacorte"] : '00000000';
        $parametros["informacion_financiera"]['activo_corriente'] = $datos["enfirme"]["inffin1510_actcte"];
        $parametros["informacion_financiera"]['activo_no_corriente'] = $datos["enfirme"]["inffin1510_actnocte"];
        $parametros["informacion_financiera"]['activo_total'] = $datos["enfirme"]["inffin1510_acttot"];
        $parametros["informacion_financiera"]['pasivo_corriente'] = $datos["enfirme"]["inffin1510_pascte"];
        $parametros["informacion_financiera"]['largo_plazo'] = $datos["enfirme"]["inffin1510_paslar"];
        $parametros["informacion_financiera"]['pasivo_total'] = $datos["enfirme"]["inffin1510_pastot"];
        $parametros["informacion_financiera"]['patrimonio'] = $datos["enfirme"]["inffin1510_patnet"];
        $parametros["informacion_financiera"]['patrimonio_neto'] = $datos["enfirme"]["inffin1510_patnet"];
        $parametros["informacion_financiera"]['pasivo_patrimonio'] = $datos["enfirme"]["inffin1510_paspat"];
        $parametros["informacion_financiera"]['balance_social'] = $datos["enfirme"]["inffin1510_balsoc"];
        $parametros["informacion_financiera"]['ingresos_operacionales'] = $datos["enfirme"]["inffin1510_ingope"];
        $parametros["informacion_financiera"]['ingresos_no_operacionales'] = $datos["enfirme"]["inffin1510_ingnoope"];
        $parametros["informacion_financiera"]['gastos_operacionales'] = $datos["enfirme"]["inffin1510_gasope"];
        $parametros["informacion_financiera"]['gastos_no_operacionales'] = $datos["enfirme"]["inffin1510_gasnoope"];
        $parametros["informacion_financiera"]['costo_ventas'] = $datos["enfirme"]["inffin1510_cosven"];
        $parametros["informacion_financiera"]['gastos_impuestos'] = $datos["enfirme"]["inffin1510_gasimp"];
        $parametros["informacion_financiera"]['gastos_financieros'] = $datos["enfirme"]["inffin1510_gasint"];
        $parametros["informacion_financiera"]['gastos_intereses'] = $datos["enfirme"]["inffin1510_gasint"];
        $parametros["informacion_financiera"]['utilidad_perdida_operacional'] = $datos["enfirme"]["inffin1510_utiope"];
        $parametros["informacion_financiera"]['utilidad_perdida_neta'] = $datos["enfirme"]["inffin1510_utinet"];
        $parametros["informacion_financiera"]['indice_liquidez'] = \funcionesGenerales::truncateFloat($datos["enfirme"]["inffin1510_indliq"], 2);
        $parametros["informacion_financiera"]['indice_endeudamiento'] = \funcionesGenerales::truncateFloat($datos["enfirme"]["inffin1510_nivend"], 2);
        $parametros["informacion_financiera"]['razon_cobertura_intereses'] = \funcionesGenerales::truncateFloat($datos["enfirme"]["inffin1510_razcob"], 2);
        $parametros["informacion_financiera"]['rentabilidad_patrimonio'] = \funcionesGenerales::truncateFloat($datos["enfirme"]["inffin1510_renpat"], 2);
        $parametros["informacion_financiera"]['rentabilidad_activo'] = \funcionesGenerales::truncateFloat($datos["enfirme"]["inffin1510_renact"], 2);
        $parametros['codigo_tamano_empresa'] = sprintf("%02s", $datos["enfirme"]["tamanoempresa"]);

        //
        $parametros['clasificacion_unspsc'] = array();
        if (isset($datos["enfirme"]["clasi1510"]) && count($datos["enfirme"]["clasi1510"]) > 0) {
            $arrUnspsc = array_unique($datos["enfirme"]["clasi1510"]);
            foreach ($arrUnspsc as $key => $value) {
                // $parametros['clasificacion_unspsc'][]['codigo_unspsc'] = $value;
                $parametros['clasificacion_unspsc'][] = $value . '00';
            }
        }

        //
        $parametros['experiencia'] = array();
        if (isset($datos["enfirme"]["exp1510"]) && !empty($datos["enfirme"]["exp1510"])) {
            foreach ($datos["enfirme"]["exp1510"] as $expe) {
                $ren = array();
                $ren["secuencia_contrato"] = $expe["secuencia"];
                $ren["celebrado_por"] = $expe["celebradopor"];
                $ren["nombre_contratista"] = $expe["nombrecontratista"];
                $ren["nombre_contratante"] = $expe["nombrecontratante"];
                $ren["fecha_ejecucion"] = $expe["fecejecucion"];
                $ren["valor_smmlv"] = $expe["valor"];
                $ren["porcentaje_participacion"] = $expe["porcentaje"];
                $clax = '';
                foreach ($expe["clasif"] as $cl1) {
                    if ($clax != '') {
                        $clax .= ',';
                    }
                    $clax .= $cl1 . '00';
                }
                $ren["codigos_unspsc"] = $clax;
                $parametros['experiencia'][] = $ren;
            }
        }


        // $parametros['informacion_adicional'] = array();
        //
        $parametros['codigo_error'] = '0000';
        $parametros['mensaje_error'] = '';

        //
        $sal = json_encode($parametros, JSON_PRETTY_PRINT);
        // \logApi::general2('construirParametrosRegPro' . date("Ymd"), $datos["proponente"], $sal);
        return $sal;
    }

    public static function consultarRegMer($camara = '', $matricula = '') {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //
        $nameLog = 'funcionesRues_consultarRegMer_' . date("Ymd");

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en consultarRegMer';
            \logApi::general2($nameLog, '', 'Error solicitando token en consultarRegMer');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en consultarRegMer';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        $matconsultar = $matricula;
        if (substr($matricula, 0, 1) == 'S') {
            $matconsultar = '900' . substr($matricula, 1);
        }
        if (substr($matricula, 0, 1) == 'N') {
            $matconsultar = '800' . substr($matricula, 1);
        }

        //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . 'api/RegMer/' . sprintf("%02s", $camara) . sprintf("%010s", $matconsultar));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $responseRues = curl_exec($ch);
        logApi::general2($nameLog, $matricula, 'Respuesta rues - ' . $responseRues);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($responseRues)) {
                        \logApi::general2($nameLog, $matricula, 'La respuesta del servicio web no es un Json (1) - ' . $responseRues);
                        $evaluarJson = 'no';
                    } else {
                        // \logApi::general2($nameLog, $matricula, 'ResponseRegMer : ' . chr(10) . chr(13) . $responseRues);
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code;
                    \logApi::general2($nameLog, $matricula, 'ResponseRegMer : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }
        curl_close($ch);
        if ($evaluarJson == 'si') {
            $resp = json_decode($responseRues, true);
            if (isset($resp['codigo_error'])) {
                return $resp;
            } else {
                $respuesta["codigo_error"] = '0003';
                $respuesta["codigoHTTP"] = $http_code;
                $respuesta["msgError"] = 'Error en respuesta del RUES : ' . retornaMensajeHttp($http_code);
                unset($resp);
                return $respuesta;
            }
        } else {
            if ($http_code == '304') {
                $respuesta["codigo_error"] = '0004';
            } else {
                $respuesta["codigo_error"] = '0005';
            }
            $respuesta["codigoHTTP"] = $http_code;
            $respuesta["msgError"] = retornaMensajeHttp($http_code);
            return $respuesta;
        }
    }

    public static function consultarRegMerIdentificacion($usu = '', $tipoId = '', $numid = '') {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //
        $nameLog = 'funcionesRues_consultarRegMerIdentificacion_' . date("Ymd");

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en consultarRegMerIdentificacion';
            \logApi::general2($nameLog, '', 'Error solicitando token en consultarRegMerIdentificacion');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en consultarRegMerIdentificacion';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        if ($tipoId == '2') {
            $sepIde = \funcionesGenerales::separarDv($numid);
            $tipoidrues = '02';
        } else {
            $sepIde = array();
            $sepIde["identificacion"] = $numid;
            $sepIde["dv"] = "";
            switch ($tipoId) {
                case "1" : $tipoidrues = '01';
                    break;
                case "3" : $tipoidrues = '03';
                    break;
                case "4" : $tipoidrues = '04';
                    break;
                case "5" : $tipoidrues = '05';
                    break;
                case "R" : $tipoidrues = '07';
                    break;
                case "E" : $tipoidrues = '08';
                    break;
                case "V" : $tipoidrues = '09';
                    break;
                case "P" : $tipoidrues = '10';
                    break;
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . 'api/consultasRUES/ConsultaNIT?usuario=' . $usu . '&nit=' . $sepIde["identificacion"] . '&tipoId=' . $tipoidrues . '&dv=' . $sepIde["dv"]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $responseRues = curl_exec($ch);
        \logApi::general2($nameLog, '', $responseRues);
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        } else {
            curl_close($ch);
            if (!\funcionesGenerales::isJson($responseRues)) {
                return false;
            } else {
                return json_decode($responseRues, true);
            }
        }
    }

    public static function consultarRegMerIdentificacionActivos($usu = '', $tipoId = '', $numid = '') {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //
        $nameLog = 'funcionesRues_consultarRegMerIdentificacionActivos_' . date("Ymd");

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en consultarRegMerIdentificacion';
            \logApi::general2($nameLog, '', 'Error solicitando token en consultarRegMerIdentificacion');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en consultarRegMerIdentificacion';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        //
        if ($tipoId == '2') {
            $sepIde = \funcionesGenerales::separarDv($numid);
            $tipoidrues = '02';
        } else {
            $sepIde = array();
            $sepIde["identificacion"] = $numid;
            $sepIde["dv"] = "";
            switch ($tipoId) {
                case "1" : $tipoidrues = '01';
                    break;
                case "3" : $tipoidrues = '03';
                    break;
                case "4" : $tipoidrues = '04';
                    break;
                case "5" : $tipoidrues = '05';
                    break;
                case "R" : $tipoidrues = '07';
                    break;
                case "E" : $tipoidrues = '08';
                    break;
                case "V" : $tipoidrues = '09';
                    break;
                case "P" : $tipoidrues = '10';
                    break;
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlrues . 'api/consultasRUES/ConsultaNIT?usuario=' . $usu . '&nit=' . $sepIde["identificacion"] . '&tipoId=' . $tipoidrues . '&dv=' . $sepIde["dv"]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $responseRues1 = curl_exec($ch);
        \logApi::general2($nameLog, '', $responseRues1);
        $strpos = strpos($responseRues1, '{');
        if ($strpos !== false && $strpos != 0) {
            $responseRues = substr($responseRues1, $strpos);
        } else {
            $responseRues = $responseRues1;
        }
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        } else {
            curl_close($ch);
            if (!\funcionesGenerales::isJson($responseRues)) {
                return false;
            } else {
                $ruetid = '';
                $rueid = '';
                $ruedv = '';
                $ruematricula = '';
                $ruecamara = '';
                $ruers = '';
                $establecimientoslocales = 0;
                $establecimientosforaneos = 0;
                $rueacttot = 0;
                $rueultanoren = '';
                $respuesta = false;
                $prop = json_decode($responseRues, true);
                if (isset($prop["registros"]) && !empty($prop["registros"])) {
                    foreach ($prop["registros"] as $r) {
                        if ($r["codigo_estado_matricula"] == '01') {
                            if ($r["codigo_categoria_matricula"] == '00' || $r["codigo_categoria_matricula"] == '01') {
                                $ruetid = $r["codigo_tipo_identificacion"];
                                $rueid = $r["numero_identificacion"];
                                $ruedv = $r["digito_verificacion"];
                                $ruematricula = $r["matricula"];
                                $ruecamara = $r["codigo_camara"];
                                $ruers = $r["razon_social"];
                                $ruefecmatricula = $r["fecha_matricula"];
                                $ruefecrenovacion = $r["fecha_renovacion"];
                                $rueultanoren = $r["ultimo_ano_renovado"];
                                $ruesdireccion = $r["direccion_comercial"];
                                $ruesmunicipio = $r["codigo_municipio_comercial"];
                                $ruestelefono = $r["telefono_comercial_1"];
                                $ruesorganizacion = $r["codigo_organizacion_juridica"];
                                $ruescategoria = $r["codigo_categoria_matricula"];
                                $respuesta = true;
                                if (isset($r["informacionFinanciera"]) && !empty($r["informacionFinanciera"])) {
                                    foreach ($r["informacionFinanciera"] as $f)
                                        if ($f["ano_informacion_financiera"] == $r["ultimo_ano_renovado"]) {
                                            $rueacttot = $f["activo_total"];
                                        }
                                }
                                if (isset($r["establecimientos"]) && !empty($r["establecimientos"])) {
                                    foreach ($r["establecimientos"] as $e) {
                                        if ($e["codigo_estado_matricula"] == '01') {
                                            if ($e["codigo_camara"] == $r["codigo_camara"]) {
                                                $establecimientoslocales++;
                                            } else {
                                                $establecimientosforaneos++;
                                            }
                                        }
                                    }
                                }
                                $respuesta = true;
                            }
                        }
                    }
                }
                if ($respuesta === false) {
                    return false;
                } else {
                    $resp = array(
                        'tipoidentificacion' => $ruetid,
                        'identificacion' => $rueid,
                        'dv' => $ruedv,
                        'camara' => $ruecamara,
                        'matricula' => $ruematricula,
                        'nombre' => $ruers,
                        'direccion' => $ruesdireccion,
                        'municipio' => $ruesmunicipio,
                        'telefono' => $ruestelefono,
                        'organizacion' => $ruesorganizacion,
                        'categoria' => $ruescategoria,
                        'ultimo_ano_renovado' => $rueultanoren,
                        'fecha_matricula' => $ruefecmatricula,
                        'fecha_renovacion' => $ruefecrenovacion,
                        'activos_totales' => $rueacttot,
                        'establecimientos_locales' => $establecimientoslocales,
                        'establecimientos_foraneos' => $establecimientosforaneos
                    );
                    \logApi::general2($nameLog, '', 'Respuesta : ' . json_encode($resp));
                    return $resp;
                }
            }
        }
    }

    /**
     * 
     * @param type $numinterno
     * @param type $usuario
     * @param type $camara
     * @param type $proponente
     * @param type $identificacion
     * @param type $dv
     * @param type $nombre
     * @param type $libro
     * @param type $reg_libro
     * @param type $acto_rup
     * @param type $txt_noticia
     * @param type $est_noticia
     * @param type $fec_registro
     * @param type $hora_registro
     * @return string
     */
    public static function publicarNoticiaProponentes($numinterno, $usuario, $camara, $proponente, $identificacion, $dv, $nombre, $libro, $reg_libro, $acto_rup, $txt_noticia, $est_noticia, $fec_registro, $hora_registro) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRuesApi.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        if (defined('PUBLICACION_NOTICIA_PROPONENTES') && PUBLICACION_NOTICIA_PROPONENTES != '') {
            return \funcionesRues::apiPublicarNoticiaProponentes($numinterno, $usuario, $camara, $proponente, $identificacion, $dv, $nombre, $libro, $reg_libro, $acto_rup, $txt_noticia, $est_noticia, $fec_registro, $hora_registro);
        }

        //
        $nameLog = 'publicarNoticiaProponentes_' . date("Ymd");

        //
        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';

        //
        if (!defined('RUES_PRUEBAS_ACTIVADO')) {
            define('RUES_PRUEBAS_ACTIVADO', 'S');
        }

        //
        if (TIPO_AMBIENTE == 'PRUEBAS' && RUES_PRUEBAS_ACTIVADO != 'S') {
            $respuesta["codigoError"] = '9980';
            $respuesta["numpub"] = '999999';
            $respuesta["fecpub"] = date("Ymd");
            $respuesta["horpub"] = date("His");
            $respuesta["msgError"] = 'RUES de PRUEBAS NO ACTIVADO';
            return $respuesta;
        }

        //
        $RUE_RegistroProponente_BC = array(
            "numero_interno" => $numinterno,
            'usuario' => $usuario,
            'camara_comercio_proponente' => sprintf("%02s", $camara),
            'inscripcion_proponente' => sprintf("%012s", $proponente),
            'numero_identificacion' => sprintf("%014s", $identificacion),
            'digito_verificacion' => $dv,
            'razon_social' => $nombre,
            'codigo_libro' => $libro,
            'numero_inscripcion_libro' => sprintf("%010s", $reg_libro),
            'codigo_acto_rup' => sprintf("%02s", $acto_rup),
            'noticia' => $txt_noticia,
            'codigo_estado_noticia' => $est_noticia,
            'fecha_inscripcion_camara' => $fec_registro,
            'hora_inscripcion_camara' => sprintf("%06s", $hora_registro),
            'numero_publicacion_noticia' => '',
            'fecha_publicacion' => '',
            'hora_publicacion' => '',
            'codigo_error' => '0000',
            'mensaje_error' => '',
            'firma_digital' => ''
        );

        //
        $txt = '';
        foreach ($RUE_RegistroProponente_BC as $key => $valor) {
            $txt .= $key . ' : ' . $valor . "\r\n";
        }

        //
        \logApi::general2($nameLog, 'Request RR09N->radicarNoticiaProponente', $txt);
        $wsdl = wsRUE_RR09N;

        // Instancia el servicio web
        try {
            $client = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true, 'encoding' => 'ISO-8859-1'));
            if (is_soap_fault($client)) {
                $respuesta["codigoError"] = '0001';
                $respuesta["msgError"] = 'No fue posible crear la conexi&oacute;n con el servicio web  de publicaci&oacute;n de noticia ';
                \logApi::general2($nameLog, 'Error Soap: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n instanciando el servico web :  ' . $e->getMessage();
            \logApi::general2($nameLog, 'Error Exception: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        } catch (SoapFault $fault) {
            $respuesta["codigoError"] = '0003';
            $respuesta["msgError"] = 'Error de soap fault : ' . $fault->getMessage();
            \logApi::general2($nameLog, 'Error SoapFault: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        }

        //
        try {
            if (substr(PHP_VERSION, 0, 1) == '5' || substr(PHP_VERSION, 0, 1) == '6' || substr(PHP_VERSION, 0, 1) == '7') {
                $result = $client->radicarNoticiaProponente($RUE_RegistroProponente_BC);
            } else {
                $result = $client->radicarNoticiaProponente(array('noticiaProponente' => $RUE_RegistroProponente_BC));
            }
            if ($result === false) {
                $respuesta["codigoError"] = '0004';
                $respuesta["msgError"] = 'Error en la respuesta del servico web ' . $result;
                \logApi::general2($nameLog, 'Error Client: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n ' . $e->getMessage();
            \logApi::general2($nameLog, 'Error Client Exception : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        }

        //
        $t = (array) $result;
        $respuesta["codigoError"] = $t["RUE_NoticiaProponente_BC"]->codigo_error;
        $respuesta["msgError"] = $t["RUE_NoticiaProponente_BC"]->mensaje_error;
        $respuesta["numpub"] = $t["RUE_NoticiaProponente_BC"]->numero_publicacion_noticia;
        $respuesta["fecpub"] = $t["RUE_NoticiaProponente_BC"]->fecha_publicacion;
        $respuesta["horpub"] = $t["RUE_NoticiaProponente_BC"]->hora_publicacion;
        \logApi::general2($nameLog, 'Ok : Libro: ' . $libro . ' - Registro: ' . $reg_libro, 'NumPubRue: ' . $respuesta["numpub"] . ' - FecPubRue : ' . $respuesta["fecpub"] . ' - HorPubRue :' . $respuesta["horpub"] . ' - MsgError: ' . $respuesta["msgError"]);
        return $respuesta;
    }

    /**
     *  Weymer : 2020-05-26 : Función sustituta de RR41N integrando RUES en modelo REST
     */
    public static function generarJsonMercantil($mysqli, $matricula) {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //
        $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $matricula);

        //
        $matriculax = '';
        if (substr($matriculax, 0, 1) == 'S') {
            $matriculax = '900' . substr($matricula, 1);
        }
        if (substr($matriculax, 0, 1) == 'N') {
            $matriculax = '800' . substr($matricula, 1);
        }

        //
        $identificacionx = '';
        $dvx = '';
        if ($exp["idtipoidentificacion"] == '2') {
            $sep = \funcionesGenerales::separarDv($exp["identificacion"]);
            $identificacionx = $sep["identificacion"];
            $dvx = $sep["dv"];
        } else {
            $identificacionx = $exp["idtipoidentificacion"];
        }

        //
        $nitx = '';
        $dvx = '';
        if (ltrim(trim($exp["nit"]), "0") != '') {
            $sep = \funcionesGenerales::separarDv($exp["nit"]);
            $nitx = $sep["identificacion"];
            $dvx = $sep["dv"];
        } else {
            $identificacionx = $exp["idtipoidentificacion"];
        }

        //
        $ctrbic = '';
        if ($exp["ctrbic"] == 'S') {
            $ctrbic = '1';
        }

        //
        $motivocancelacionx = '';
        if (ltrim(trim($exp["fechacancelacion"]), "0") != '') {
            $motivocancelacionx = '00';
        }

        //
        $expe = array();
        $expe["camara"] = CODIGO_EMPRESA;
        $expe["matricula"] = $matriculax;
        $expe["proponente"] = sprintf("%010s", $exp["proponente"]);
        if ($exp["organizacion"] != '01') {
            $expe["nombre"] = $exp["nombre"];
        } else {
            if ($expe["ape1"] == '') {
                $expe["nombre"] = $exp["nombre"];
            } else {
                $expe["nombre"] = trim((string) $expe["ape1"]);
                if (trim((string) $expe["ape2"]) != '') {
                    $expe["nombre"] .= ' ' . trim((string) $expe["ape2"]);
                }
                if (trim((string) $expe["nom1"]) != '') {
                    $expe["nombre"] .= ' ' . trim((string) $expe["nom1"]);
                }
                if (trim((string) $expe["nom2"]) != '') {
                    $expe["nombre"] .= ' ' . trim((string) $expe["nom2"]);
                }
            }
        }
        $expe["ape1"] = $exp["ape1"];
        $expe["ape2"] = $exp["ape2"];
        $expe["nom1"] = $exp["nom1"];
        $expe["nom2"] = $exp["nom2"];
        $expe["sigla"] = $exp["sigla"];
        $expe["nombrecomercial"] = $exp["nombrecomercial"];
        $expe["idtipoidentificacion"] = \funcionesRues::homologarTipoIdentificacion($mysqli, $expe["tipoidentificacion"]);
        $expe["identificacion"] = $identificacionx;
        $expe["idmunidoc"] = $exp["idmunidoc"];
        $expe["fechaexpdoc"] = $exp["fecexpdoc"];
        $expe["paisexpdoc"] = $exp["paisexpdoc"];
        $expe["nit"] = $nitx;
        $expe["dv"] = $dvx;
        $expe["nacionalidad"] = $exp["nacionalidad"];
        $expe["genero"] = $exp["sexo"];
        $expe["indicadorempresabic"] = $ctrbic;
        $expe["numidetribpaisorigen"] = $exp["idetripaiori"];
        $expe["numidetribextranjeroep"] = $exp["idetriextep"];
        $expe["fechamatricula"] = $exp["fechamatricula"];
        $expe["fecharenovacion"] = $exp["fecharenovacion"];
        $expe["fechaconstitucion"] = $exp["fechaconstitucion"];
        $expe["fechavigencia"] = $exp["fechavencimiento"];
        $expe["fechacancelacion"] = ltrim(trim($exp["fechacancelacion"]), "0");
        $expe["motivocancelacion"] = $motivocancelacionx;
        $expe["fechadisolucion"] = $exp["fechadisolucion"];
        $expe["fechaliquidacion"] = $exp["fechaliquidacion"];
        $expe["ultanoren"] = $exp["ultanoren"];
        $expe["estadomatricula"] = \funcionesRues::homologarEstadoMatricula($mysqli, $exp["estadomatricula"]);
        $expe["organizacion"] = \funcionesRues::homologarOrganizacionMatricula($mysqli, $exp["organizacion"], $exp["categoria"], $exp["claseespesadl"], $exp["claseeconsoli"]);
        $expe["categoria"] = \funcionesRues::homologarCategoriaMatricula($mysqli, $exp["organizacion"], $exp["categoria"]);
        $expe["cantidadmujerescargosdirectivos"] = intval($exp["cantidadmujerescargosdirectivos"]);
        $expe["cantidadmujeresempleadas"] = intval($exp["cantidadmujeres"]);
        $expe["ctrbeneficioarticulo4"] = $exp["art4"];
        $expe["ctrbeneficioarticulo7"] = $exp["art7"];
        $expe["ctrbeneficioarticulo50"] = $exp["art50"];
        $expe["ctrcancelacionley1429depuracion"] = $exp["ctrcancelacion1429"];
        $expe["ctrbeneficioLey1780"] = $exp["benley1780"];
        $expe["ctrcumplerequisitosley1780"] = $exp["cumplerequisitos1780"];
        $expe["ctrrenunciabeneficiosley1780"] = $exp["renunciabeneficios1780"];
        $expe["ctrcumplerequisitosley1780primrenovacion"] = $exp["cumplerequisitos1780primren"];
        $expe["ctrdepuracion1727"] = $exp["ctrdepuracion1727"]; // Campos nuevos sugeridos
        $expe["ctrfechadepuracion1727"] = $exp["ctrfechadepuracion1727"]; // Campos nuevos sugeridos

        $expe["ctraportante"] = $exp["aportantesegsocial"];
        $expe["ctrtipoaportante"] = $exp["tipoaportantesegsocial"];

        $expe["tamanoempresa"] = $exp["tamanoempresarial957codigo"];
        $expe["emprendedor28"] = '';
        $expe["pemprendedor28"] = 0;
        $expe["impexp"] = $exp["impexp"];
        $expe["tipopropiedad"] = $exp["tipopropiedad"];
        $expe["tipolocal"] = $exp["tipolocal"];
        $expe["empresafamiliar"] = $exp["empresafamiliar"];
        $expe["procesosinnovacion"] = $exp["procesosinnovacion"];
        $expe["ctrubicacion"] = $exp["ctrubi"];
        $expe["ctrfuncionamiento"] = $exp["ctrfun"];
        $expe["tiposedeadm"] = $exp["tiposedeadm"];
        $expe["estadoactualpjur"] = '';

        $expe["fechaperj"] = $exp["fecperj"];
        $expe["txtorigenperj"] = $exp["origendocconst"];
        $expe["numperj"] = $exp["numperj"];
        $expe["vigcontrol"] = $exp["vigcontrol"];
        $expe["vigifechaini"] = '';
        $expe["vigifechafin"] = '';
        $expe["claseeconsoli"] = '';
        $expe["cntasociados"] = $exp["ctresacntasociados"];
        $expe["cntmujeres"] = $exp["ctresacntmujeres"];
        $expe["cnthombres"] = $exp["ctresacnthombres"];
        $expe["pertgremio"] = $exp["ctresapertgremio"];
        $expe["nomgremio"] = $exp["ctresagremio"];
        $expe["entidadacreditadacursoeconsol"] = $exp["ctresaacredita"];
        $expe["ivcnombre"] = $exp["ctresaivc"];
        $expe["remisioninfoivc"] = $exp["ctresainfoivc"];
        $expe["ctrautorizaregistro"] = $exp["ctresaautregistro"];
        $expe["entautorizaregistro"] = $exp["ctresaentautoriza"];
        $expe["codnaturaleza"] = $exp["ctresacodnat"];
        $expe["ctrdiscapacitados"] = $exp["ctresadiscap"];
        $expe["ctretnia"] = $exp["ctresaetnia"];
        $expe["cualetnia"] = $exp["ctresacualetnia"];
        $expe["ctrdesvicreins"] = $exp["ctresadespvictreins"];
        $expe["ctrdesvicreinscual"] = $exp["ctresacualdespvictreins"];
        $expe["ctrindgestion"] = $exp["ctresaindgest"];
        $expe["ctrlgbti"] = $exp["ctresalgbti"];

        $expe["ctrafiliacion"] = $exp["afiliado"];

        $expe["dircom"] = $exp["dircom"];
        $expe["zonacom"] = $exp["codigozonacom"];
        $expe["codposcom"] = $exp["codigopostalcom"];
        $expe["ubicacioncom"] = '';
        $expe["barriocom"] = '';
        $expe["muncom"] = $exp["muncom"];
        $expe["telcom1"] = $exp["telcom1"];
        $expe["telcom2"] = $exp["telcom2"];
        $expe["telcom3"] = $exp["celcom"];
        $expe["emailcom"] = $exp["emailcom"];

        $expe["dirnot"] = $exp["dirnot"];
        $expe["zonanot"] = $exp["codigozonanot"];
        $expe["codposnot"] = $exp["codigopostalnot"];
        $expe["ubicacionnot"] = '';
        $expe["barrionot"] = '';
        $expe["munnot"] = $exp["munnot"];
        $expe["painot"] = $exp["painot"];
        $expe["telnot1"] = $exp["telnot"];
        $expe["telnot2"] = $exp["telno2"];
        $expe["telnot3"] = $exp["celnot"];
        $expe["emailnot"] = $exp["emailnot"];
        $expe["autorizonotifemail"] = $exp["ctrmennot"];

        $expe["ciiu1"] = $exp["ciius"][1];
        $expe["ciiu2"] = $exp["ciius"][2];
        $expe["ciiu3"] = $exp["ciius"][3];
        $expe["ciiu4"] = $exp["ciius"][4];
        $expe["fechainiciociiu1"] = $exp["feciniact1"];
        $expe["fechainiciociiu2"] = $exp["feciniact2"];
        $expe["descripcionactividad"] = base64_encode($exp["desactiv"]);
        $expe["ciiumayoresingresos"] = $exp["ciiutamanoempresarial"];

        //
        $expe["infoFinanciera"] = array();
        foreach ($exp["hf"] as $regf) {
            $infoFinanciera = array();
            $infoFinanciera["anodatos"] = $regf["anodatos"];
            $infoFinanciera["fechadatos"] = $regf["fechadatos"];
            $infoFinanciera["personal"] = $regf["personal"];
            $infoFinanciera["personaltemp"] = $regf["pcttemp"];
            $infoFinanciera["actvin"] = $regf["actvin"];
            $infoFinanciera["actcte"] = $regf["actcte"];
            $infoFinanciera["actnocte"] = $regf["actnocte"];
            $infoFinanciera["acttot"] = $regf["acttot"];
            $infoFinanciera["pascte"] = $regf["pascte"];
            $infoFinanciera["pasnocte"] = $regf["paslar"];
            $infoFinanciera["pastot"] = $regf["pastot"];
            $infoFinanciera["patnet"] = $regf["patnet"];
            $infoFinanciera["paspat"] = $regf["paspat"];
            $infoFinanciera["balsoc"] = $regf["balsoc"];
            $infoFinanciera["ingope"] = $regf["ingope"];
            $infoFinanciera["ingnoope"] = $regf["ingnoope"];
            $infoFinanciera["gasope"] = $regf["gasope"];
            $infoFinanciera["gasnoope"] = $regf["gasadm"];
            $infoFinanciera["cosven"] = $regf["cosven"];
            $infoFinanciera["gasimp"] = $regf["gasimp"];
            $infoFinanciera["utiope"] = $regf["utiope"];
            $infoFinanciera["utinet"] = $regf["utinet"];
            $expe["infoFinanciera"][] = $infoFinanciera;
        }

        //
        $expe["gruponiif"] = $exp["gruponiif"];
        $expe["pornalpub"] = $exp["cap_porcnalpub"];
        $expe["pornalpri"] = $exp["cap_porcnalpri"];
        $expe["porextpub"] = $exp["cap_porcextpub"];
        $expe["porextpri"] = $exp["cap_porcextpri"];

        $expe["vinculo"] = array();
        foreach ($exp["vinculos"] as $vin) {
            $vinculo = array();
            $vinculo["tipovinculo"] = \funcionesRues::homologarTipoVinculosMatricula($mysqli, $vin["vinculootros"]);
            $vinculo["idtipoidentificacion"] = \funcionesRues::homologarTipoIdentificacion($mysqli, $vin["idtipoidentificacionotros"]);
            $vinculo["identificacion"] = $vin["identificacionotros"];
            $vinculo["nombre_razon_social"] = $vin["nombreotros"];
            $vinculo["ape1"] = $vin["apellido1otros"];
            $vinculo["ape2"] = $vin["apellido2otros"];
            $vinculo["nom1"] = $vin["nombre1otros"];
            $vinculo["nom2"] = $vin["nombre2otros"];
            $vinculo["fechanacimiento"] = $vin["fechanacimientootros"];
            $vinculo["fecahexpdoc"] = '';
            $vinculo["cargo"] = $vin["cargootros"];
            $vinculo["renglon"] = '';
            $vinculo["nitrepresenta"] = $vin["numidemp"];
            $vinculo["libroregistro"] = $vin["librootros"];
            $vinculo["numeroregistro"] = $vin["inscripcionotros"];
            $vinculo["dupliregistro"] = $vin["dupliotros"];
            $vinculo["fecharegistro"] = $vin["fechaotros"];
            $vinculo["numerocuotas"] = $vin["cuotasconst"];
            $vinculo["valorcuotas"] = $vin["valorconst"];
            if ($vin["cuotasref"] != 0) {
                $vinculo["numerocuotas"] = $vin["cuotasref"];
                $vinculo["valorcuotas"] = $vin["valorref"];
            }
            $vinculo["apolab"] = $vin["va1"];
            $vinculo["apolabadi"] = $vin["va2"];
            $vinculo["apoact"] = $vin["va4"];
            $vinculo["apodin"] = $vin["va3"];
            if ($vin["va5"] != 0 || $vin["va6"] != 0 || $vin["va7"] != 0 || $vin["va8"] != 0) {
                $vinculo["apolab"] = $vin["va5"];
                $vinculo["apolabadi"] = $vin["va6"];
                $vinculo["apoact"] = $vin["va8"];
                $vinculo["apodin"] = $vin["va7"];
            }
            $expe["vinculo"][] = $vinculo;
        }

        $expe["capital"] = array();
        foreach ($exp["capitales"] as $hcap) {
            $capital = array();
            $capital["libroregistro"] = $hcap["libro"];
            $capital["numeroregistro"] = $hcap["registro"];
            $capital["fechadatos"] = $hcap["fechadatos"];
            $capital["anodatos"] = $hcap["anodatos"];
            $capital["cuotascapsocial"] = $hcap["cuosocial"];
            $capital["valorcapsocial"] = $hcap["capsoc"];
            $capital["cuotascapaut"] = $hcap["cuoautorizado"];
            $capital["valorcapaut"] = $hcap["capaut"];
            $capital["cuotascapsus"] = $hcap["cuosuscrito"];
            $capital["valorcapsus"] = $hcap["capsus"];
            $capital["cuotascappag"] = $hcap["cuopagado"];
            $capital["valorcappag"] = $hcap["cappag"];
            $capital["apolab"] = $hcap["apolab"];
            $capital["apolabadi"] = $hcap["apolabadi"];
            $capital["apoact"] = $hcap["apoact"];
            $capital["apodin"] = $hcap["apodin"];
            $capital["patrimonio"] = 0;
            $capital["participacionmujeres"] = 0;
            $capital["participacionetnia"] = 0;

            $expe["capital"][] = $capital;
        }

        $expe["propietario"] = array();
        foreach ($expe["propietarios"] as $prop) {
            $propietario = array();
            $propietario["camara"] = $prop["camarapropietario"];
            $propietario["matricula"] = $prop["matriculapropietario"];
            $propietario["idtipoidentificacion"] = \funcionesRues::homologarTipoIdentificacion($mysqli, $prop["idtipoidentificacionpropietario"]);
            $propietario["identificacion"] = $prop["identificacionpropietario"];
            $propietario["nombre"] = $prop["nombrepropietario"];
            $propietario["nom1"] = $prop["nom1propietario"];
            $propietario["nom2"] = $prop["nom2propietario"];
            $propietario["ape1"] = $prop["ape1propietario"];
            $propietario["ape2"] = $prop["ape2propietario"];
            $propietario["dircom"] = $prop["direccionpropietario"];
            $propietario["muncom"] = $prop["municipiopropietario"];
            $propietario["telcom1"] = $prop["telefonopropietario"];
            $propietario["telcom2"] = $prop["telefono2propietario"];
            $propietario["emailcom"] = '';
            $expe["propietario"][] = $propietario;
        }

        //
        $expe["casasPrincipales"] = array();
        $casaPrincipal = array();
        $casaPrincipal["cpcamara"] = $exp["cpcodcam"];
        $casaPrincipal["cpmatricula"] = $exp["cpnummat"];
        $casaPrincipal["cpnit"] = $exp["cpnumnit"];
        $casaPrincipal["cprazonsocial"] = $exp["cprazsoc"];
        $casaPrincipal["cpdircom"] = $exp["cpdircom"];
        $casaPrincipal["cpmuncom"] = $exp["cpcodmun"];
        $casaPrincipal["cptelcom1"] = $exp["cpnumtel"];
        $casaPrincipal["cptelcom2"] = $exp["cpnumtel2"];
        $casaPrincipal["cptelcom3"] = $exp["cpnumtel3"];
        $casaPrincipal["cpemailcom"] = '';
        $expe["casaPrincipal"][] = $casaPrincipal;

        //
        $expe["certificas"] = array();
        foreach ($expe["ctrsii"] as $key => $crt) {
            $crt = array();
            $crt["codigo"] = \funcionesRues::homologarCertificasMatricula($mysqli, $key);
            $crt["texto"] = base64_encode(strip_tags($crt));
            $expe["certificas"][] = $crt;
        }

        //
        $expe["libroscomercio"] = array();
        foreach ($exp["inscripcioneslibros"] as $lib) {
            $libro = array();
            $libro["libroregistro"] = $lib["lib"];
            $libro["numeroregistro"] = $lib["nreg"];
            $libro["dupli"] = $lib["dupli"];
            $libro["fecharegistro"] = $lib["freg"];
            $libro["codigolibro"] = '0000';
            $libro["descripcionlibro"] = $lib["deslib"];
            $libro["paginainicial"] = $lib["paginainicial"];
            $libro["totalpaginas"] = $lib["numhojas"];
            $expe["libroscomercio"][] = $libro;
        }

        //
        $expe["embargo"] = array();
        foreach ($exp["ctrembargos"] as $emb) {
            $embargo = array();
            $embargo["libroregistro"] = $emb["libro"];
            $embargo["numeroregistro"] = $emb["numreg"];
            $embargo["fecharegistro"] = $emb["fecinscripcion"];
            $embargo["tipo"] = '01';
            $embargo["tipo"] = $expe["matricula"];
            $embargo["idtipoidentificaciondemandante"] = '';
            $embargo["identificaciondemandante"] = '';
            $embargo["nombredemandante"] = '';
            $embargo["noticia"] = base64_encode($exp["noticia"]);
            $expe["embargo"][] = $embargo;
        }

        //
        $expe["Kardex"] = array();
        foreach ($exp["inscripciones"] as $ins) {
            $kardex = array();
            $kardex["libroregistro"] = $ins["lib"];
            $kardex["numeroregistro"] = $ins["nreg"];
            $kardex["Dupli"] = $ins["dupli"];
            $kardex["fecharegistro"] = $ins["freg"];
            $kardex["actogenerico"] = \funcionesRues::homologarActosGenericosMatricula($mysqli, $ins["acto"]);
            $kardex["descripcionacto"] = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $ins["lib"] . "' idacto='" . $ins["acto"] . "'", "nombre");
            $kardex["Idtipodoc"] = \funcionesRues::homologarTiposDocumentales($mysqli, $ins["tdoc"]);
            $kardex["Fechadoc"] = $ins["fdoc"];
            $kardex["txtorigendoc"] = $ins["txoridoc"];
            $kardex["Numdoc"] = $ins["ndoc"];
            $kardex["Noticia"] = base64_encode($ins["not"]);
            if ($ins["crev"] == '1') {
                $kardex["ctrrevocacion"] = '1';
            } else {
                $kardex["ctrrevocacion"] = '0';
            }
            $kardex["numeroregistrorevocacion"] = $ins["regrev"];
            $kardex["fecharegistrorevocacion"] = '';
            $kardex["camaraanterior"] = $ins["camant"];
            $kardex["librocamaraanterior"] = $ins["libant"];
            $kardex["numeroregistrocamaraanterior"] = $ins["regant"];
            $kardex["fecharegistrocamaraanterior"] = $ins["fecharegistroanterior"];
            $expe["Kardex"][] = $kardex;
        }

        return json_encode($expe);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $codcba - código de barras a reportar
     * @param type $estados - U.-Ultimo estado T.- Todos los estados
     * @return boolean
     */
    public static function reportarEstadoTramiteVUE($mysqli, $codcba, $numliq, $codest, $fec, $hor) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/wsReportarEstadoTramiteVue.class.php');

        if (ACTIVADA_EN_VUE != 'SI') {
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'No activada en VUE');
            return false;
        }

        //
        if (wsReportarEstadoTramiteVUE == '') {
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'No encontro wsdl parametrizado');
            return false;
        }

        //
        if (ltrim(trim($codcba), "0") == '') {
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'No reporto codigo de barras');
            return false;
        }

        //
        $cba = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $codcba . "'");
        if ($cba === false || empty($cba)) {
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'Codigo de barras no localizado');
            return false;
        }

        //
        $liqcam = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $numliq . " and campo='subtipotramite'");
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $numliq);
        if ($liq === false || empty($liq)) {
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'Liquidacion ' . $numliq . ' no localizada');
            return false;
        }

        //
        if ($liq["tramitepresencial"] != '3') {
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'No es un tramite virtual');
            return false;
        }

        //
        if ($liqcam["contenido"] != 'matriculapnatcae' &&
                $liqcam["contenido"] != 'matriculapjurcae' &&
                $liqcam["contenido"] != 'matriculapnat' &&
                $liqcam["contenido"] != 'matriculapjur' &&
                $liqcam["contenido"] != 'matriculaest' &&
                $liqcam["contenido"] != 'matriculasuc' &&
                $liqcam["contenido"] != 'matriculaage' &&
                $liq["tipotramite"] != 'matriculapnatcae' &&
                $liq["tipotramite"] != 'matriculapjurcae' &&
                $liq["tipotramite"] != 'matriculapnat' &&
                $liq["tipotramite"] != 'matriculapjur' &&
                $liq["tipotramite"] != 'matriculaest' &&
                $liq["tipotramite"] != 'matriculasuc' &&
                $liq["tipotramite"] != 'matriculaage') {
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'No es matricula o constitucion');
            return false;
        }

        //
        $vue = "false";
        if ($liq["tipotramite"] == 'matriculapnatcae' || $liq["tipotramite"] == 'matriculapjurcae' || $liqcam["contenido"] == 'matriculapnatcae' || $liqcam["contenido"] == 'matriculapjurcae') {
            $vue = "true";
        }

        // Homologar estados
        $codesthomologado = '';
        switch ($codest) {
            case "01" :
            case "04" :
            case "09" :
            case "11" :
            case "23" :
            case "34" :
            case "38" :
                $codesthomologado = '02';
                break;

            case "15" :
            case "16" :
                $codesthomologado = '03';
                break;

            case "05" :
            case "06" :
            case "07" :
                $codesthomologado = '04';
                break;

            case "39" :
                $codesthomologado = '08';
                break;

            case "99" :
                $codesthomologado = '09';
                break;
        }

        //
        if ($codesthomologado == '') {
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'No es un estado reportable (' . $codest . ')');
            return false;
        }

        //
        $mat = '';
        $org = '';
        if ($cba["matricula"] != '' && substr($cba["matricula"], 0, 5) != 'NUEVA') {
            $mat = $cba["matricula"];
            $org = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat . "'", "organizacion");
        } else {
            if ($liqcam["contenido"] == 'matriculapnatcae' ||
                    $liqcam["contenido"] == 'matriculapnat' ||
                    $liq["tipotramite"] == 'matriculapnatcae' ||
                    $liq["tipotramite"] == 'matriculapnat') {
                $org = '01';
            } else {
                if ($liqcam["contenido"] == 'matriculaest' ||
                        $liq["tipotramite"] == 'matriculaest') {
                    $org = '02';
                } else {
                    $org = '16';
                }
            }
        }

        // Todos los posibles parametros a enviar
        $parametros = array(
            'CODIGO_CAMARA_COMERCIO' => CODIGO_EMPRESA,
            'CODIGO_ESTADO' => $codesthomologado,
            'CODIGO_ORGANIZACION_JURIDICA' => sprintf("%02s", $org),
            'FECHA' => $fec,
            'HORA' => substr(str_replace(":", "", $hor), 0, 4),
            'MATRICULA' => sprintf("%010s", $mat),
            'NUMERO_ADICIONAL' => array($numliq),
            'NUMERO_RADICADO' => $codcba,
            'USUARIO' => $_SESSION["generales"]["codigousuario"],
            'VUE' => $vue
        );

        // Log temporal antes del llamado al consumo
        $txtLog = '';
        foreach ($parametros as $key => $valor) {
            if (!is_array($valor)) {
                $txtLog .= $key . ' => ' . $valor . chr(13) . chr(10);
            } else {
                foreach ($valor as $key1 => $valor1) {
                    if (!is_array($valor1)) {
                        $txtLog .= ' ... ' . $key1 . ' => ' . $valor1 . chr(13) . chr(10);
                    } else {
                        foreach ($valor1 as $key2 => $valor2) {
                            $txtLog .= ' ...... ' . $key2 . ' => ' . $valor2 . chr(13) . chr(10);
                        }
                    }
                }
            }
        }
        \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, wsReportarEstadoTramiteVUE);
        \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, 'Parametros : ' . $txtLog);
        \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), $codcba, '');

        $array = array();
        $array["TRAMITE"] = $parametros;

        set_time_limit(90);
        ini_set("soap.wsdl_cache_enabled", "0");
        $ins = wsReportarEstadoTramiteVue::singleton(wsReportarEstadoTramiteVUE);
        $ret = $ins->registroEstadoTramite($array);
        unset($ins);

        // En caso de error
        if ($ret["codigo_error"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = $ret["codigo_error"] . ' - ' . $ret["mensaje_error"];
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), __FUNCTION__, $_SESSION["generales"]["mensajeerror"]);
            return false;
        } else {
            $_SESSION["generales"]["mensajeerror"] = $ret["codigo_error"] . ' - ' . $ret["mensaje_error"];
            \logApi::general2('reportarEstadoTramiteVUE_' . date('Ymd'), __FUNCTION__, $_SESSION["generales"]["mensajeerror"]);
        }

        //
        return $ret;
    }

    /**
     * 
     * @param type $numinterno
     * @param type $usuario
     * @param type $codindicadorenvio
     * @param type $nitproponente
     * @param type $dvproponente
     * @param type $nitentidad
     * @param type $dventidad
     * @param type $munentidad
     * @param type $numcontrato
     * @param type $nomentidad
     * @param type $nomproponente
     * @param type $secentidad
     * @param string $fecadjudicacion
     * @param string $fecperfeccionamiento
     * @param string $fecinicio
     * @param string $fecterminacion
     * @param string $fecliquidacion
     * @param int $valcontrato
     * @param int $valpagado
     * @param type $estcontrato
     * @param type $tipocontratista
     * @param type $motterminacionanticipada
     * @param string $fecterminacionanticipada
     * @param int $codactividad
     * @param type $ciiu1
     * @param type $ciiu2
     * @param type $ciiu3
     * @param type $ciiu4
     * @param type $observaciones
     * @param type $camara
     * @param type $libro
     * @param type $reg_libro
     * @param type $fec_registro
     * @param type $clasif1464
     * @param type $objeto
     * @param type $motcesion
     * @param string $feccesion
     * @param type $unspsc
     * @return string
     */
    public static function reportarContratosArt90RR31N($numinterno, $usuario, $codindicadorenvio, $nitproponente, $dvproponente, $nitentidad, $dventidad, $munentidad, $numcontrato, $nomentidad, $nomproponente, $secentidad, $fecadjudicacion, $fecperfeccionamiento, $fecinicio, $fecterminacion, $fecliquidacion, $valcontrato, $valpagado, $estcontrato, $tipocontratista, $motterminacionanticipada, $fecterminacionanticipada, $codactividad, $ciiu1, $ciiu2, $ciiu3, $ciiu4, $observaciones, $camara, $libro, $reg_libro, $fec_registro, $clasif1464, $objeto = '', $motcesion = '', $feccesion = '', $unspsc = '') {

        //
        $nameLog = 'reportarContratosArt90RR31N_' . date("Ymd");

        //
        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';

        if (ltrim($fecadjudicacion, "0") == '')
            $fecadjudicacion = '00000000';
        if (ltrim($fecperfeccionamiento, "0") == '')
            $fecperfeccionamiento = '00000000';
        if (ltrim($fecinicio, "0") == '')
            $fecinicio = '00000000';
        if (ltrim($fecterminacion, "0") == '')
            $fecterminacion = '00000000';
        if (ltrim($fecliquidacion, "0") == '')
            $fecliquidacion = '00000000';
        if (ltrim($valcontrato, "0") == '')
            $valcontrato = 0;
        if (ltrim($valpagado, "0") == '')
            $valpagado = 0;
        if (ltrim($codactividad, "0") == '')
            $codactividad = 0;
        if (ltrim($fecterminacionanticipada, "0") == '')
            $fecterminacionanticipada = '00000000';
        if (ltrim($feccesion, "0") == '')
            $feccesion = '00000000';

        $ciius = array();

        if (trim($ciiu1) != '') {
            $ciius[]["codigo_ciiu"] = $ciiu1;
        }
        if (trim($ciiu2) != '') {
            $ciius[]["codigo_ciiu"] = $ciiu2;
        }
        if (trim($ciiu3) != '') {
            $ciius[]["codigo_ciiu"] = $ciiu3;
        }
        if (trim($ciiu4) != '') {
            $ciius[]["codigo_ciiu"] = $ciiu4;
        }

        //
        $arrayUnspsc = array();
        if (trim($unspsc) != '') {
            $arrayUnspsc = explode(",", $unspsc);
        }

        //
        $RUE_ReporteContratoRR31N_BC = array(
            "numero_interno" => $numinterno,
            "usuario" => $usuario,
            "cod_indicador_envio" => $codindicadorenvio, // (indicador de envio del contrato)
            "nit_proponente" => sprintf("%014s", $nitproponente),
            "dv_proponente" => $dvproponente,
            "nit_entidad" => sprintf("%014s", $nitentidad),
            "dv_entidad" => $dventidad,
            "municipio_entidad" => $munentidad,
            "numero_contrato" => $numcontrato,
            "nombre_entidad" => $nomentidad,
            "nombre_proponente" => $nomproponente,
            "seccional_entidad" => $secentidad,
            "fecha_adjudicacion" => $fecadjudicacion, // Enviar ceros en caso de vacios)
            "fecha_perfeccionamiento" => $fecperfeccionamiento,
            "fecha_inicio" => $fecinicio,
            "fecha_terminacion" => $fecterminacion,
            "fecha_terminacion_ejecucion" => $fecterminacion,
            "fecha_liquidacion" => $fecliquidacion,
            "valor_contrato" => $valcontrato,
            "valor_pagado" => $valpagado,
            "cod_estado_contrato" => $estcontrato,
            "objeto_contrato" => substr($objeto, 0, 512), // Nuevo
            "cod_tipo_contratista" => $tipocontratista,
            "motivo_terminacion_anticipada" => ($motterminacionanticipada),
            "fecha_terminacion_anticipada" => $fecterminacionanticipada,
            "motivo_cesion" => $motcesion, // Nuevo
            "fecha_cesion" => $feccesion, // Nuevo        
            "cod_actividad" => $codactividad,
            'actividad_ciiu' => $ciius,
            'clasificacionunspsc' => $arrayUnspsc, // Nuevo 
            "observaciones" => $observaciones,
            "codigo_camara" => $camara,
            "codigo_libro_registro" => $libro,
            "numero_inscripcion_camara" => $reg_libro,
            "numero_inscripcion_libro" => $reg_libro,
            "fecha_inscripcion_camara" => $fec_registro,
            "clasificaciones1464" => $clasif1464,
            "numero_radicacion_rue" => 0,
            "fecha_radicacion_rue" => '00000000',
            "hora_radicacion_rue" => '',
            "codigo_error" => '',
            "mensaje_error" => ''
        );

        \logApi::general2($nameLog, 'Enviando', print_r($RUE_ReporteContratoRR31N_BC, true));

        // Consumir v&iacute;a Document Literal
        $wsdl = URL_RUE_WS . "_DL/RR31N.asmx?WSDL";

        // Instancia el servicio web
        try {
            $client = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true, 'encoding' => 'ISO-8859-1'));
            if (is_soap_fault($client)) {
                $respuesta["codigoError"] = '0001';
                $respuesta["msgError"] = 'No fue posible crear la conexi&oacute;n con el servicio web  de publicaci&oacute;n de noticia ';
                \logApi::general2($nameLog, 'Error Soap: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n instanciando el servico web :  ' . $e->getMessage();
            \logApi::general2($nameLog, 'Error Exception : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        } catch (SoapFault $fault) {
            $respuesta["codigoError"] = '0003';
            $respuesta["msgError"] = 'Error de soap fault : ' . $fault->getMessage();
            \logApi::general2($nameLog, 'Error Soap Fault Exception: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        }

        try {
            $result = $client->reporteContratos(array('reporteContratos' => $RUE_ReporteContratoRR31N_BC));
            if ($result === false) {
                $respuesta["codigoError"] = '0004';
                $respuesta["msgError"] = 'Error en la respuesta del servico web ' . $result;
                \logApi::general2($nameLog, 'Error Client : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepcion ' . $e->getMessage();
            \logApi::general2($nameLog, 'Error excepcion Soap : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        }

        $t = (array) $result;
        $respuesta["codigoError"] = $t["RUE_ReporteContratoRR31N_BC"]->codigo_error;
        $respuesta["msgError"] = $t["RUE_ReporteContratoRR31N_BC"]->mensaje_error;
        $respuesta["numradrue"] = ltrim($t["RUE_ReporteContratoRR31N_BC"]->numero_radicacion_rue, "0");
        $respuesta["fecradrue"] = $t["RUE_ReporteContratoRR31N_BC"]->fecha_radicacion_rue;
        $respuesta["horradrue"] = $t["RUE_ReporteContratoRR31N_BC"]->hora_radicacion_rue;
        \logApi::general2($nameLog, 'Ok : Libro: ' . $libro . ' - Registro: ' . $reg_libro, 'NumRadRue: ' . $respuesta["numradrue"] . ' - FecRadRue : ' . $respuesta["fecradrue"] . ' - HorRadRue :' . $respuesta["horradrue"] . ' - CodigoError: ' . $respuesta["codigoError"] . ' - MsgError: ' . $respuesta["msgError"]);
        return $respuesta;
    }

    /**
     * 
     * @param type $numinterno
     * @param type $usuario
     * @param type $codindicadorenvio
     * @param type $nitproponente
     * @param type $dvproponente
     * @param type $nitentidad
     * @param type $dventidad
     * @param type $munentidad
     * @param type $numcontrato
     * @param type $nomentidad
     * @param type $nomproponente
     * @param type $secentidad
     * @param type $numactoadministrativo
     * @param string $fecactoadministrativo
     * @param string $fecejecutoria
     * @param int $valmulta
     * @param int $valpagado
     * @param type $estmulta
     * @param string $numactosuspension
     * @param string $fecactosuspension
     * @param string $numactoconfirmacion
     * @param string $fecactoconfirmacion
     * @param string $numactorevocacion
     * @param string $fecactorevocacion
     * @param type $observaciones
     * @param type $camara
     * @param type $libro
     * @param type $reg_libro
     * @param type $fec_registro
     * @param type $numactoejecutoria
     * @param type $numsecop
     * @return string
     */
    public static function reportarMultasArt90RR31N($numinterno, $usuario, $codindicadorenvio, $nitproponente, $dvproponente, $nitentidad, $dventidad, $munentidad, $numcontrato, $nomentidad, $nomproponente, $secentidad, $numactoadministrativo, $fecactoadministrativo, $fecejecutoria, $valmulta, $valpagado, $estmulta, $numactosuspension, $fecactosuspension, $numactoconfirmacion, $fecactoconfirmacion, $numactorevocacion, $fecactorevocacion, $observaciones, $camara, $libro, $reg_libro, $fec_registro, $numactoejecutoria = '', $numsecop = '') {

        $nameLog = 'reportarMultasArt90RR31N_' . date("Ymd");

        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';

        if (ltrim($fecactoadministrativo, "0") == '')
            $fecactoadministrativo = '00000000';
        if (ltrim($fecejecutoria, "0") == '')
            $fecejecutoria = '00000000';
        if (ltrim($fecactosuspension, "0") == '')
            $fecactosuspension = '00000000';
        if (ltrim($fecactoconfirmacion, "0") == '')
            $fecactoconfirmacion = '00000000';
        if (ltrim($fecactorevocacion, "0") == '')
            $fecactorevocacion = '00000000';
        if (ltrim($numactorevocacion, "0") == '')
            $numactorevocacion = '0';
        if (ltrim($numactoconfirmacion, "0") == '')
            $numactoconfirmacion = '0';
        if (ltrim($numactosuspension, "0") == '')
            $numactosuspension = '0';
        if (ltrim($valmulta, "0") == '')
            $valmulta = 0;
        if (ltrim($valpagado, "0") == '')
            $valpagado = 0;


        $RUE_ReporteMultaRR31N_BC = array(
            "numero_interno" => $numinterno,
            "usuario" => $usuario,
            "cod_indicador_envio" => $codindicadorenvio,
            "nit_proponente" => sprintf("%014s", $nitproponente),
            "dv_proponente" => $dvproponente,
            "nit_entidad" => sprintf("%014s", $nitentidad),
            "dv_entidad" => $dventidad,
            "municipio_entidad" => $munentidad,
            "numero_contrato" => $numcontrato,
            "nombre_entidad" => ($nomentidad),
            "nombre_proponente" => ($nomproponente),
            "seccional_entidad" => ($secentidad),
            "numero_acto_administrativo" => $numactoadministrativo,
            "fecha_acto_administrativo" => $fecactoadministrativo,
            "numero_acto_ejecutoria" => $numactoejecutoria,
            "fecha_ejecutoria" => $fecejecutoria,
            "valor_multa" => $valmulta,
            "valor_pagado_multa" => $valpagado,
            "cod_estado" => $estmulta,
            "numero_acto_suspension" => $numactosuspension,
            "fecha_acto_suspension" => $fecactosuspension,
            "numero_acto_confirmacion" => $numactoconfirmacion,
            "fecha_acto_confirmacion" => $fecactoconfirmacion,
            "numero_acto_revocacion" => $numactorevocacion,
            "fecha_acto_revocacion" => $fecactorevocacion,
            "observaciones" => $observaciones,
            "codigo_camara" => $camara,
            "codigo_libro_registro" => $libro,
            "numero_inscripcion_libro" => $reg_libro,
            "fecha_inscripcion_camara" => $fec_registro,
            "numero_contrato_secop" => $numsecop,
            "numero_radicacion_rue" => 0,
            "fecha_radicacion_rue" => '00000000',
            "hora_radicacion_rue" => '0000',
            "codigo_error" => '',
            "mensaje_error" => '',
            "fecha_registro_inicial_rue" => \funcionesGenerales::mostrarfecha($fec_registro)
        );

        $wsdl = URL_RUE_WS . "_DL/RR31N.asmx?WSDL";

        $tx = '';
        foreach ($RUE_ReporteMultaRR31N_BC as $key => $valor) {
            $tx .= $key . ' => ' . $valor . chr(13) . chr(10);
        }
        \logApi::general2($nameLog, 'Libro: ' . $libro . ' - Registro: ' . $reg_libro, $tx);

        // Instancia el servicio web
        try {
            $client = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true, 'encoding' => 'ISO-8859-1'));
            if (is_soap_fault($client)) {
                $respuesta["codigoError"] = '0001';
                $respuesta["msgError"] = 'No fue posible crear la conexi&oacute;n con el servicio web  de publicaci&oacute;n de noticia ';
                \logApi::general2($nameLog, 'Error Soap: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n instanciando el servico web :  ' . $e->getMessage();
            \logApi::general2($nameLog, 'Error Exception : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        } catch (SoapFault $fault) {
            $respuesta["codigoError"] = '0003';
            $respuesta["msgError"] = 'Error de soap fault : ' . $fault->getMessage();
            \logApi::general2($nameLog, 'Error Soap Fault Exception: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        }

        try {
            $result = $client->reporteMultas(array('reporteMultas' => $RUE_ReporteMultaRR31N_BC));
            if ($result === false) {
                $respuesta["codigoError"] = '0004';
                $respuesta["msgError"] = 'Error en la respuesta del servico web ' . $result;
                \logApi::general2($nameLog, 'Error Client : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n ' . $e->getMessage();
            \logApi::general2($nameLog, 'Error Client Soap : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        }

        $t = (array) $result;
        $respuesta["codigoError"] = $t["RUE_ReporteMultaRR31N_BC"]->codigo_error;
        $respuesta["msgError"] = $t["RUE_ReporteMultaRR31N_BC"]->mensaje_error;
        $respuesta["numradrue"] = ltrim($t["RUE_ReporteMultaRR31N_BC"]->numero_radicacion_rue, "0");
        $respuesta["fecradrue"] = $t["RUE_ReporteMultaRR31N_BC"]->fecha_radicacion_rue;
        $respuesta["horradrue"] = $t["RUE_ReporteMultaRR31N_BC"]->hora_radicacion_rue;
        \logApi::general2($nameLog, 'Ok : Libro: ' . $libro . ' - Registro: ' . $reg_libro, 'NumRadRue: ' . $respuesta["numradrue"] . ' - FecRadRue : ' . $respuesta["fecradrue"] . ' - HorRadRue :' . $respuesta["horradrue"] . ' - MsgError: ' . $respuesta["msgError"]);
        return $respuesta;
    }

    /**
     * 
     * @param type $numinterno
     * @param type $usuario
     * @param type $codindicadorenvio
     * @param type $nitproponente
     * @param type $dvproponente
     * @param type $nitentidad
     * @param type $dventidad
     * @param type $munentidad
     * @param type $numcontrato
     * @param type $nomentidad
     * @param type $nomproponente
     * @param type $secentidad
     * @param type $numactoadministrativo
     * @param string $fecactoadministrativo
     * @param string $fecejecutoria
     * @param type $dessancion
     * @param string $condincumplimiento
     * @param type $estsancion
     * @param string $numactosuspension
     * @param string $fecactosuspension
     * @param string $numactoconfirmacion
     * @param string $fecactoconfirmacion
     * @param string $numactorevocacion
     * @param string $fecactorevocacion
     * @param type $observaciones
     * @param type $camara
     * @param type $libro
     * @param type $reg_libro
     * @param type $fec_registro
     * @param type $numactoejecutoria
     * @param string $vigsancion
     * @param type $fundlegal
     * @param type $numsecop
     * @return string
     */
    public static function reportarSancionesArt90RR31N($numinterno, $usuario, $codindicadorenvio, $nitproponente, $dvproponente, $nitentidad, $dventidad, $munentidad, $numcontrato, $nomentidad, $nomproponente, $secentidad, $numactoadministrativo, $fecactoadministrativo, $fecejecutoria, $dessancion, $condincumplimiento, $estsancion, $numactosuspension, $fecactosuspension, $numactoconfirmacion, $fecactoconfirmacion, $numactorevocacion, $fecactorevocacion, $observaciones, $camara, $libro, $reg_libro, $fec_registro, $numactoejecutoria = '', $vigsancion = '', $fundlegal = '', $numsecop = '') {

        $nameLog = 'reportarSancionesArt90RR31N_' . date("Ymd");

        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';

        if (ltrim($fecactoadministrativo, "0") == '')
            $fecactoadministrativo = '00000000';
        if (ltrim($fecejecutoria, "0") == '')
            $fecejecutoria = '00000000';
        if (ltrim($vigsancion, "0") == '')
            $vigsancion = '00000000';
        if (ltrim($fecactosuspension, "0") == '')
            $fecactosuspension = '00000000';
        if (ltrim($fecactoconfirmacion, "0") == '')
            $fecactoconfirmacion = '00000000';
        if (ltrim($fecactorevocacion, "0") == '')
            $fecactorevocacion = '00000000';
        if (ltrim($numactoconfirmacion, "0") == '')
            $numactoconfirmacion = '0';
        if (ltrim($numactorevocacion, "0") == '')
            $numactorevocacion = '0';
        if (ltrim($numactosuspension, "0") == '')
            $numactosuspension = '0';
        if (trim($condincumplimiento) == '')
            $condincumplimiento = 'I';


        $RUE_ReporteSancionRR31N_BE = array(
            "numero_interno" => $numinterno,
            "usuario" => $usuario,
            "cod_indicador_envio" => $codindicadorenvio,
            "nit_proponente" => sprintf("%014s", $nitproponente),
            "dv_proponente" => $dvproponente,
            "nit_entidad" => sprintf("%014s", $nitentidad),
            "dv_entidad" => $dventidad,
            "municipio_entidad" => $munentidad,
            "numero_contrato" => $numcontrato,
            "nombre_entidad" => $nomentidad,
            "nombre_proponente" => $nomproponente,
            "seccional_entidad" => $secentidad,
            "numero_acto_administrativo" => $numactoadministrativo,
            "fecha_acto_administrativo" => $fecactoadministrativo,
            "numero_acto_ejecutoria" => $numactoejecutoria, // Nuevo
            "fecha_ejecutoria" => $fecejecutoria,
            "descripcion_sancion" => $dessancion,
            "vigencia_sancion" => $vigsancion, // Nuevo
            "fundamento_legal" => $fundlegal, // Nuevo
            "condicion_incumplimiento" => $condincumplimiento,
            "cod_estado" => $estsancion,
            "numero_acto_suspension" => $numactosuspension,
            "fecha_acto_suspension" => $fecactosuspension,
            "numero_acto_confirmacion" => $numactoconfirmacion,
            "fecha_acto_confirmacion" => $fecactoconfirmacion,
            "numero_acto_revocacion" => $numactorevocacion,
            "fecha_acto_revocacion" => $fecactorevocacion,
            "observaciones" => $observaciones,
            "codigo_camara" => $camara,
            "codigo_libro_registro" => $libro,
            "numero_inscripcion_libro" => $reg_libro,
            "fecha_inscripcion_camara" => $fec_registro,
            "numero_contrato_secop" => $numsecop, // Nuevo
            "numero_radicacion_rue" => 0,
            "fecha_radicacion_rue" => '00000000',
            "hora_radicacion_rue" => '0000',
            "codigo_error" => '',
            "mensaje_error" => '',
            "fecha_registro_inicial_rue" => \funcionesGenerales::mostrarFecha($fec_registro)
        );

        $tx = '';
        foreach ($RUE_ReporteSancionRR31N_BE as $key => $valor) {
            $tx .= $key . ' => ' . $valor . chr(13) . chr(10);
        }
        \logApi::general2($nameLog, 'Libro: ' . $libro . ' - Registro: ' . $reg_libro, $tx);

        $wsdl = URL_RUE_WS . "_DL/RR31N.asmx?WSDL";

        // Instancia el servicio web
        try {
            $client = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true, 'encoding' => 'ISO-8859-1'));
            if (is_soap_fault($client)) {
                $respuesta["codigoError"] = '0001';
                $respuesta["msgError"] = 'No fue posible crear la conexi&oacute;n con el servicio web  de publicaci&oacute;n de noticia ';
                \logApi::general2($nameLog, 'Error Soap: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n instanciando el servico web :  ' . $e->getMessage();
            \logApi::general2($nameLog, 'Error Exception : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        } catch (SoapFault $fault) {
            $respuesta["codigoError"] = '0003';
            $respuesta["msgError"] = 'Error de soap fault : ' . $fault->getMessage();
            \logApi::general2($nameLog, 'Error Soap Fault Exception: Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        }

        try {
            $result = $client->reporteSanciones(array('reporteSanciones' => $RUE_ReporteSancionRR31N_BE));
            if ($result === false) {
                $respuesta["codigoError"] = '0004';
                $respuesta["msgError"] = 'Error en la respuesta del servico web ' . $result;
                \logApi::general2($nameLog, 'Error Client : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n ' . $e->getMessage();
            \logApi::general2($nameLog, 'Error Client Soap : Libro: ' . $libro . ' - Registro: ' . $reg_libro, $respuesta["msgError"]);
            return $respuesta;
        }

        $t = (array) $result;
        $respuesta["codigoError"] = $t["RUE_ReporteSancionRR31N_BE"]->codigo_error;
        $respuesta["msgError"] = $t["RUE_ReporteSancionRR31N_BE"]->mensaje_error;
        $respuesta["numradrue"] = ltrim($t["RUE_ReporteSancionRR31N_BE"]->numero_radicacion_rue, "0");
        $respuesta["fecradrue"] = $t["RUE_ReporteSancionRR31N_BE"]->fecha_radicacion_rue;
        $respuesta["horradrue"] = $t["RUE_ReporteSancionRR31N_BE"]->hora_radicacion_rue;

        \logApi::general2($nameLog, 'Ok : Libro: ' . $libro . ' - Registro: ' . $reg_libro, 'NumRadRue: ' . $respuesta["numradrue"] . ' - FecRadRue : ' . $respuesta["fecradrue"] . ' - HorRadRue :' . $respuesta["horradrue"] . ' - MsgError: ' . $respuesta["msgError"]);
        return $respuesta;
    }

    /**
     * 
     * @param type $dbx
     * @param type $tide
     * @param type $ide
     * @param type $userapi
     * @param type $passapi
     * @return type
     */
    public static function recuperarEstablecimientosNacionales($dbx = null, $tide = '', $ide = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $nameLog = 'validacionEstablecimientosRues_' . date("Ymd");

        //
        $token = \funcionesRues::solicitarToken();
        if ($token === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error solicitando token en consultarRegMerIdentificacion';
            \logApi::general2($nameLog, '', 'Error solicitando token en consultarRegMerIdentificacion');
            $respuesta["codigoError"] = '9999';
            $respuesta["msgError"] = 'Error solicitando token en consultarRegMerIdentificacion';
            return $respuesta;
        }
        $urlrues = \funcionesRues::obtenerUrlRues();

        if ($tide != '2') {
            $ide1 = $ide;
            $ide2 = '';
        } else {
            $sep = \funcionesgenerales::separarDv($ide);
            $ide1 = $sep["identificacion"];
            $ide2 = $sep["dv"];
        }
        $url = $urlrues . 'api/establecimientos?usuario=' . CODIGO_EMPRESA . '-' . $_SESSION["generales"]["codigousuario"] . '&nit=' . $ide1 . '&dv=' . $ide2 . '&codCamExc=' . CODIGO_EMPRESA;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);

        //
        \logApi::general2($nameLog, $tide . '-' . $ide, '****** PETICION : ' . $url . ' *** RESPUESTA : ' . $result);
        \logApi::general2($nameLog, '', '');

        //
        if (!\funcionesGenerales::isJson($result)) {
            return array();
        }

        //
        $resultado = json_decode($result, true);

        if (isset($resultado["error"])) {
            return array();
        }

        //
        if (!isset($resultado["establecimientos"])) {
            return array();
        }
        //
        $xcon = array();
        $salida = array();
        $ix = 0;
        foreach ($resultado["establecimientos"] as $est) {

            $ind = $est["codigo_camara"] . '-' . $est["matricula"];
            if (!isset($xcon[$ind])) {
                $xcon[$ind] = 1;

                $ix++;
                $salida[$ix] = $est;
                $salida[$ix]["ind"] = $ind;
                $salida[$ix]["nombre_municipio_comercial"] = retornarRegistroMysqliApi($dbx, "bas_municipios", "codigomunicipio='" . $est["municipio_comercial"] . "'", "ciudad");

                // Homologa organizacion juridica
                $xorg = '';
                switch ($est["codigo_organizacion_juridica"]) {
                    case "01":
                        $xorg = '01';
                        break;
                    case "02":
                        $xorg = '02';
                        break;
                    case "03":
                        $xorg = '03';
                        break;
                    case "04":
                        $xorg = '04';
                        break;
                    case "05":
                        $xorg = '05';
                        break;
                    case "06":
                        $xorg = '06';
                        break;
                    case "07":
                        $xorg = '07';
                        break;
                    case "08":
                        $xorg = '08';
                        break;
                    case "09":
                        $xorg = '09';
                        break;

                    case "10":
                        $xorg = '11';
                        break;
                    case "11":
                        $xorg = '17';
                        break;
                    case "12":
                        $xorg = '99';
                        break;
                    case "13":
                        $xorg = '15';
                        break;
                    default:
                        $xorg = '12';
                        break;
                }
                $salida[$ix]["codigo_organizacion_juridica"] = $xorg;

                // Homologo categoria
                $xcat = '';
                switch ($est["codigo_categoria_matricula"]) {
                    case "00":
                        $xcat = '';
                        break;
                    case "01":
                        $xcat = '1';
                        break;
                    case "02":
                        $xcat = '2';
                        break;
                    case "03":
                        $xcat = '3';
                        break;
                    case "04":
                        $xcat = '';
                        break;
                }
                $salida[$ix]["codigo_categoria_matricula"] = $xcat;

                // Ajusta fecha de renovacion
                if ($salida[$ix]["fecha_renovacion"] == '') {
                    if (isset($salida[$ix]["fecha_matricula"]) && $salida[$ix]["fecha_matricula"] != '') {
                        $salida[$ix]["fecha_renovacion"] = $salida[$ix]["fecha_matricula"];
                    }
                }

                // historico renovaciones
                // ultimo_ano_renovado variable de referencia
                // - ano_renovado
                // - fecha_renovacion
                // - afiliado
                $anoren1 = $salida[$ix]["ultimo_ano_renovado"];
                $anoren2 = $anoren1 - 1;
                $anoren3 = $anoren1 - 2;
                $salida[$ix]["anorenultimo"] = $salida[$ix]["ultimo_ano_renovado"];
                $salida[$ix]["anorenanterior"] = '';
                $salida[$ix]["anorenantepenultimo"] = '';
                $salida[$ix]["historicorenovaciones"] = array();
                if (isset($est["historicoRenovaciones"]) && !empty($est["historicoRenovaciones"])) {
                    foreach ($est["historicoRenovaciones"] as $rx) {
                        $salida[$ix]["historicorenovaciones"][] = array(
                            'ano' => $rx["ano_renovado"],
                            'fecha' => $rx["fecha_renovacion"],
                            'afiliado' => $rx["afiliado"]
                        );
                    }
                }
            }
        }
        $salida1 = \funcionesGenerales::ordenarMatriz($salida, "ind");
        unset($resultado);
        return $salida1;
    }

    //
    public static function homologarTipoIdentificacion($mysqli, $codigo) {
        $ret = '06';
        switch ($codigo) {
            case "1" : $ret = '01';
                break;
            case "2" : $ret = '02';
                break;
            case "3" : $ret = '03';
                break;
            case "4" : $ret = '04';
                break;
            case "5" : $ret = '05';
                break;
            case "R" : $ret = '07';
                break;
            case "E" : $ret = '08';
                break;
            case "V" : $ret = '09';
                break;
            case "P" : $ret = '10';
                break;
        }
        return $ret;
    }

    //
    public static function homologarImpExpMatricula($mysqli, $codigo) {
        $ret = '0';
        switch ($codigo) {
            case "1" : $ret = '1';
                break;
            case "2" : $ret = '2';
                break;
            case "3" : $ret = '3';
                break;
        }
        return $ret;
    }

    //
    public static function homologarEstadoMatricula($mysqli, $codigo) {
        $ret = '05';
        switch ($codigo) {
            case "MA" :
            case "MR" :
            case "MI" :
                $ret = '01';
                break;
            case "MC" : $ret = '03';
                break;
            case "MF" : $ret = '04';
                break;

            case "IA" :
            case "IR" :
            case "II" :
                $ret = '01';
                break;
            case "IC" : $ret = '03';
                break;
            case "IF" : $ret = '04';
                break;
        }
        return $ret;
    }

    //
    public static function homologarCategoriaMatricula($mysqli, $org, $cat) {
        $ret = '00';
        if ($org == '01') {
            $ret = '00';
        }
        if ($org == '02') {
            $ret = '04';
        }
        if ($org > '02' && $cat == '1') {
            $ret = '01';
        }
        if ($org > '02' && $cat == '2') {
            $ret = '02';
        }
        if ($org > '02' && $cat == '3') {
            $ret = '03';
        }
        return $ret;
    }

    //
    public static function homologarOrganizacionMatricula($mysqli, $org, $cat, $clasegen = '', $claseespe = '', $claseconsoli = '') {
        $ret = null;
        switch ($org) {
            case "01" : $ret = '01';
                break;
            case "02" : $ret = '02';
                break;
            case "03" : $ret = '03';
                break;
            case "04" : $ret = '04';
                break;
            case "05" : $ret = '05';
                break;
            case "06" : $ret = '06';
                break;
            case "07" : $ret = '07';
                break;
            case "08" : $ret = '08';
                break;
            case "09" : $ret = '09';
                break;
            case "11" : $ret = '10';
                break;
            case "17" : $ret = '11';
                break;
            case "15" : $ret = '13';
                break;
            case "16" : $ret = '16';
                break;
            case "99" : $ret = '12';
                break;
            case "12" :
                if (trim($clasegen) != '') {
                    switch ($clasegen) {
                        case '1':
                            $ret = '32';
                            break;
                        case '3':
                            $ret = '31';
                            break;
                        case '0':
                            $ret = '34';
                            break;
                        default:
                            $ret = null;
                            break;
                    }
                } else {
                    if (trim($claseespe) != '') {
                        switch ($claseespe) {
                            case '20':
                                $ret = '21';
                                break;
                            case '25':
                                $ret = '26';
                                break;
                            case '26':
                                $ret = '27';
                                break;
                            case '29':
                                $ret = '29';
                                break;
                            case '41':
                                $ret = '30';
                                break;
                            case '60':
                                $ret = '34';
                                break;
                            case '62':
                                $ret = '34';
                                break;
                            default:
                                $ret = '33';
                                break;
                        }
                    } else {
                        $ret = '33';
                    }
                }
                break;
            case "14" :
                if (trim($claseconsoli) != '') {
                    switch ($claseconsoli) {
                        case '03':
                            $ret = '25';
                            break;
                        case '05':
                            $ret = '23';
                            break;
                        case '07':
                            $ret = '24';
                            break;
                        default:
                            $ret = '22';
                            break;
                    }
                } else {
                    $ret = '22';
                }
                break;
        }
        return $ret;
    }

    //
    public static function homologarCertificasMatricula($mysqli, $codigo) {
        $ret = 'XXXX';
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_codigos_certificas', "id='" . $codigo . "'");
        if ($temx === false || empty($temx)) {
            return $ret;
        }
        if (isset($temx["rues"]) && trim($temx["rues"]) != '') {
            return $temx["rues"];
        } else {
            return $ret;
        }
    }

    //
    public static function homologarActosGenericosMatricula($mysqli, $grupoacto) {
        $ret = '999';
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_gruposactos', "id='" . $grupoacto . "'");
        if ($temx === false || empty($temx)) {
            return $ret;
        }
        if (isset($temx["rues"]) && trim($temx["rues"]) != '') {
            return $temx["rues"];
        } else {
            return $ret;
        }
    }

    //
    public static function homologarTipoVinculosMatricula($mysqli, $codigo) {
        $ret = null;
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $codigo . "'");
        if ($temx === false || empty($temx)) {
            return $ret;
        }
        if (isset($temx["rues"]) && trim($temx["rues"]) != '') {
            return $temx["rues"];
        } else {
            return $ret;
        }
    }

    //
    public static function homologarActosRUP($mysqli, $codigo) {
        $ret = null;
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_actosproponente', "id='" . $codigo . "'");
        if ($temx === false || empty($temx)) {
            return $ret;
        }
        if (isset($temx["rues"]) && trim($temx["rues"]) != '') {
            return $temx["rues"];
        } else {
            return $ret;
        }
    }

    //
    public static function homologarTiposDocumentales($mysqli, $codigo) {
        $ret = $codigo;
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $codigo . "'");
        if ($temx === false || empty($temx)) {
            return $ret;
        }
        if (isset($temx["rues"]) && trim($temx["rues"]) != '') {
            return $temx["rues"];
        } else {
            return $ret;
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $codigo
     * @return type
     */
    public static function homologarTiposDocumentalesImagenes($mysqli, $codigo) {
        $ret = $codigo;
        $temx = retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "idtipodoc='" . $codigo . "'");
        if ($temx === false || empty($temx)) {
            return $ret;
        }
        if (isset($temx["homologarues"]) && trim($temx["homologarues"]) != '') {
            return $temx["homologarues"];
        } else {
            return $ret;
        }
    }

}

/**
 * 
 * @param type $codigo
 * @return string
 */
function retornaMensajeHttp($codigo) {
    switch ($codigo) {
        case 200:
            $msj = 'OK';
            break;
        case 401:
            $msj = 'No autorizada la petición.';
            break;
        case 403:
            $msj = 'El token no es válido o no hay permisos de consumo.';
            break;
        case 404:
            $msj = 'No existe un registro previo.';
            break;
        case 304:
            $msj = 'Ya existe un registro previo.';
            break;
        case 500:
            $msj = 'Error Interno.';
            break;
        case 504:
            $msj = 'Tiempo de espera agotado';
            break;
        default:
            $msj = 'Código desconocido. (' . $codigo . ')';
            break;
    }
    return $msj;
}
