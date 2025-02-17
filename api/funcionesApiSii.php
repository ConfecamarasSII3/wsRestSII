<?php

class funcionesApiSii {

    /**
     * 
     * @param type $numliq
     * @param type $emailfirmante
     * @param type $identificacionFirmante
     * @param type $nombrefirmante
     * @param type $celularfirmante
     * @param type $clavefirmante
     * @param type $ambiente
     * @param type $controlfirmante
     * @param type $afectar
     * @return bool
     */
    public static function apiFirmarElectronicamenteTramite($numliq = 0, $emailfirmante = '', $identificacionFirmante = '', $nombrefirmante = '', $celularfirmante = '', $clavefirmante = '', $ambiente = 'A', $controlfirmante = 'si', $afectar = 'N') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $nameLog = 'firmarElectronicamenteTramite_' . date("Ymd");

        $parametros = array(
            'codigoempresa' => $_SESSION["generales"]["codigoempresa"],
            'usuariows' => md5(USUARIO_API_DEFECTO),
            'token' => md5(TOKEN_API_DEFECTO),
            'idusuario' => 'USUPUBXX',
            'idliquidacion' => $numliq,
            'identificacioncontrol' => $identificacionFirmante,
            'emailcontrol' => $emailfirmante,
            'celularcontrol' => $celularfirmante,
            'nombrecontrol' => $nombrefirmante,
            'clavefirmado' => $clavefirmante,
            'ambiente' => $ambiente,
            'controlfirmante' => $controlfirmante,
        );
        if ($parametros["ambiente"] == 'P' && TIPO_AMBIENTE == 'PRUEBAS') {
            if ($afectar == 'S') {
                $parametros["actualizarliquidacion"] = 'si';
                $parametros["enviaremail"] = 'si';
            } else {
                $parametros["actualizarliquidacion"] = 'no';
                $parametros["enviaremail"] = 'no';
            }
        } else {
            $parametros["actualizarliquidacion"] = 'si';
            $parametros["enviaremail"] = 'si';
        }
        $request = json_encode($parametros, JSON_PRETTY_PRINT);
        $evaluarJson = 'si';
        $ch = curl_init();

        //
        curl_setopt($ch, CURLOPT_URL, URL_API_SII . 'firmarElectronicamenteTramite');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($response)) {
                        \logApi::general2($nameLog, $numliq, 'La respuesta del servicio web no es un Json (1) - ' . $response);
                        $evaluarJson = 'no';
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $response;
                    \logApi::general2($nameLog, $numliq, 'Response : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        }

        //
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            return json_decode($response, true);
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $idanexo
     * @param type $emailfirmante
     * @param type $identificacionFirmante
     * @param type $celularfirmante
     * @param type $url
     * @param string $nameLog
     * @return bool
     */
    public static function apiRecuperarAnexoLiquidacion($idanexo = 0, $emailfirmante = '', $identificacionFirmante = '', $celularfirmante = '', $url = '', $nameLog = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if ($nameLog == '') {
            $nameLog = 'api_recuperarAnexoLiquidacion_' . date("Ymd");
        }

        $parametros = array(
            'codigoempresa' => $_SESSION["generales"]["codigoempresa"],
            'usuariows' => md5(USUARIO_API_DEFECTO),
            'token' => md5(TOKEN_API_DEFECTO),
            'idusuario' => 'USUPUBXX',
            'tipousuario' => '01',
            'identificacioncontrol' => $identificacionFirmante,
            'emailcontrol' => $emailfirmante,
            'celularcontrol' => $celularfirmante,
            'idanexo' => $idanexo
        );
        $request = json_encode($parametros, JSON_PRETTY_PRINT);
        $evaluarJson = 'si';
        $ch = curl_init();

        //
        \logApi::general2($nameLog, '', 'Url para consultar anexo : ' . $url . 'recuperarAnexoLiquidacion');
        curl_setopt($ch, CURLOPT_URL, $url . 'recuperarAnexoLiquidacion');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($response)) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1) - ' . $response);
                        $evaluarJson = 'no';
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code . ' - ' . $response;
                    \logApi::general2($nameLog, '', 'Response : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        } else {
            $evaluarJson = 'no';
            $msj = $response;
            \logApi::general2($nameLog, '', 'Response : ' . $msj);
        }

        //
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            return json_decode($response, true);
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $data
     * @param type $idusuario
     * @param type $emailcontrol
     * @param type $identificacioncontrol
     * @param type $nombrecontrol
     * @param type $celularcontrol
     * @return bool
     */
    public static function apiLiquidarRenovacionMultiplesAnios($data = array(), $idusuario = '', $emailcontrol = '', $identificacioncontrol = '', $nombrecontrol = '', $celularcontrol = '', $url = '', $nameLog = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if ($nameLog == '') {
            $nameLog = 'api_LiquidarRenovacionMultiplesAnios_' . date("Ymd");
        }

        $arrayMatriculas = array();
        foreach ($data["expedientes"] as $exp) {
            if ($exp["renovaresteano"] == 'si') {
                $mat = array(
                    'matricula' => $exp["matricula"],
                    'activos' => $exp["nuevosactivos"],
                    'anorenovacion' => $exp["ultimoanorenovado"]
                );
                $arrayMatriculas[] = $mat;
            }
        }

        $parametros = array(
            'codigoempresa' => $_SESSION["generales"]["codigoempresa"],
            'usuariows' => md5(USUARIO_API_DEFECTO),
            'token' => md5(TOKEN_API_DEFECTO),
            'idusuario' => $idusuario,
            'identificacioncontrol' => $identificacioncontrol,
            'nombrecontrol' => $nombrecontrol,
            'emailcontrol' => $emailcontrol,
            'celularcontrol' => $celularcontrol,
            'matriculas' => $arrayMatriculas,
            'personal' => $data["numeroempleados"],
            'incluirafiliacion' => $data["incluirafiliacion"],
            'incluircertificado' => $data["incluircertificados"],
            'incluirformulario' => $data["incluirformularios"],
            'cumple1780' => $data["cumplorequisitosbenley1780"],
            'mantiene1780' => $data["mantengorequisitosbenley1780"],
            'renuncia1780' => $data["renunciobeneficiosley1780"],
            'idliquidacion' => $data["idliquidacion"],
            'numerorecuperacion' => $data["numerorecuperacion"]
        );
        $request = json_encode($parametros);
        $evaluarJson = 'si';
        $ch = curl_init();

        //
        \logApi::general2($nameLog, '', 'Url para consultar anexo : ' . $url . 'liquidarRenovacionMultiplesAnios');
        curl_setopt($ch, CURLOPT_URL, $url . 'liquidarRenovacionMultiplesAnios');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $response = curl_exec($ch);
        \logApi::general2($nameLog, '', 'Response: ' . $response);
        /*
        $strpos = strpos($response, '{');
        if ($strpos !== false && $strpos != 0) {
            $response1 = substr($response,$strpos);
        } else {
            $response1 = $response;
        }
        */
        $response1 = $response;
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson(trim($response1))) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1)');
                        $evaluarJson = 'no';
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code;
                    \logApi::general2($nameLog, '', 'Response : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        } else {
            $evaluarJson = 'no';
        }

        //
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            return json_decode($response1, true);
        } else {
            return false;
        }
    }
    
    public static function apiRetornarListaMatriculasRenovar ($idusuario = '', $matriculabase = '', $identificacionbase = '', $procesartodas = '', $url = '', $nameLog = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        // if ($nameLog == '') {
            $nameLog = 'api_apiRetornarListaMatriculasRenovar_' . date("Ymd");
        // }

        $parametros = array(
            'codigoempresa' => $_SESSION["generales"]["codigoempresa"],
            'usuariows' => md5(USUARIO_API_DEFECTO),
            'token' => md5(TOKEN_API_DEFECTO),
            'idusuario' => $idusuario,
            'matriculabase' => $matriculabase,
            'identificacionbase' => $identificacionbase,
            'procesartodas' => $procesartodas
        );
        $request = json_encode($parametros);
        $evaluarJson = 'si';
        $ch = curl_init();

        //
        \logApi::general2($nameLog, '', 'Url para consultar lista de matriculas a renovar : ' . $url . 'retornarListaMatriculasRenovar');
        \logApi::general2($nameLog, '', 'Parametros : ' . $request);
        curl_setopt($ch, CURLOPT_URL, $url . 'retornarListaMatriculasRenovar');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $response1 = curl_exec($ch);
        $pos = strpos($response1,"{");
        if ($pos == false) {
            $response = $response1;
        } else {
            $response = substr($response1,$pos);
        }
        \logApi::general2($nameLog, '', 'Response: ' . $response);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson(trim($response))) {
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1)');
                        $evaluarJson = 'no';
                    } else {
                        $evaluarJson = 'si';
                    }
                    break;
                default:
                    $msj = 'Código HTTP RegMer: ' . $http_code;
                    \logApi::general2($nameLog, '', 'Response : ' . $msj);
                    $evaluarJson = 'no';
                    break;
            }
        } else {
            $evaluarJson = 'no';
        }

        //
        curl_close($ch);

        //
        if ($evaluarJson == 'si') {
            return json_decode($response, true);
        } else {
            return false;
        }
    }

}
