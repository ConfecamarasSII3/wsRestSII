<?php

class funcionesPagoElectronico {

    /**
     * 
     * @param type $referencia
     * @param type $arrLiq
     * @return type
     */
    public static function solicitarRedirectPlaceToPay($referencia = '', $arrLiq = array()) {

        require_once PATH_ABSOLUTO_SITIO . '/api/log.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/mysqli.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php';

        $nombreLog = __FUNCTION__ . '_' . date("Ymd");

        //Valida parámetros requeridos para consumo Rest
        if (!defined('URL_PLACETOPAY_REST')) {
            define('URL_PLACETOPAY_REST', '');
            define('LOGIN_PLACETOPAY', '');
            define('SECRETKEY_PLACETOPAY', '');
        }

        //Construye parámetros adicionales en el consumo Rest (seed,nonce,nonceBase64,tranceKey)
        $seed = date('c');
        $fechaTmp = strtotime('+24 hours', strtotime($seed));
        /*
         * Expiración de esta solicitud, el cliente debe terminar el proceso antes de esta fecha. i.e. 2016-07-22T15:43:25-05:00
         */
        $expiracion = date('c', $fechaTmp);
        $nonce = '';
        $tranKey = '';
        if (function_exists('random_bytes')) {
            $nonce = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $nonce = mt_rand();
        }

        $nonceBase64 = base64_encode($nonce);
        $tranKey = base64_encode(sha1($nonce . $seed . SECRETKEY_PLACETOPAY, true));

        //Define arreglo de autenticación
        $arregloAutenticacion = array(
            "login" => LOGIN_PLACETOPAY,
            "seed" => $seed,
            "nonce" => $nonceBase64,
            "tranKey" => $tranKey,
        );

        //Define arreglo de Info de Pago
        $arregloPago = array(
            "reference" => $referencia,
            "description" => $arrLiq["tipotramite"],
            "amount" => array(
                "currency" => "COP",
                "total" => $arrLiq["valortotal"],
            ),
        );

        // Homologa los tipos de identificación (CC, CE, TI, SSN,NIT, PPN)
        $tipoIdePlacetopay = '';
        switch (trim($arrLiq["tipoidentificacionpagador"])) {
            case "1":
                $tipoIdePlacetopay = "CC";
                break;
            case "3":
            case "E":
            case "5":
                $tipoIdePlacetopay = "CE";
                break;
            case "4":
                $tipoIdePlacetopay = "TI";
                break;
            case "2":
                $tipoIdePlacetopay = "NIT";
                break;
            default:
                break;
        }

        //Define arreglo de Pagador
        $arregloPagador = array(
            "documentType" => $tipoIdePlacetopay,
            "document" => $arrLiq["identificacionpagador"],
            "name" => $arrLiq["nombrepagador"],
            "surname" => $arrLiq["apellidopagador"],
            "email" => $arrLiq["emailpagador"],
            "address" => array("street" => $arrLiq["direccionpagador"], "city" => retornarNombreMunicipioMysqliApi(null, $arrLiq["municipiopagador"])),
            "mobile" => $arrLiq["movilpagador"],
        );

        //Construye Arreglo de Petición de redireccionamiento
        $data = array(
            "auth" => $arregloAutenticacion,
            "payment" => $arregloPago,
            "buyer" => $arregloPagador,
            "expiration" => $expiracion,
            "returnUrl" => TIPO_HTTP . HTTP_HOST . "/retornoPlacetoPay.php?accion=return&referencia=" . $referencia,
            "cancelUrl" => TIPO_HTTP . HTTP_HOST . "/retornoPlacetoPay.php?accion=cancel&referencia=" . $referencia,
            "ipAddress" => \funcionesGenerales::localizarIP(),
            "userAgent" => "PlacetoPay Sandbox",
        );

        //Codifica en formato JSON
        $json_data = json_encode($data);

        //Consume via CURL en servicio Rest de PlacetoPay
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, URL_PLACETOPAY_REST . "/redirection/api/session");
        curl_setopt($ch, CURLOPT_URL, URL_PLACETOPAY_REST . "/api/session"); // Cambio solicitado por place to pay pendiente de activar
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
        $arrResult = json_decode($result, true);
        \logApi::general2($nombreLog, $referencia, 'Solicitud de redirect: ' . var_export($arrResult, true));
        return $arrResult;
    }

    /**
     * 
     * @param type $referencia
     * @param type $ticketid
     * @return type
     */
    public static function consultarPlaceToPay($referencia = '', $ticketid = '') {

        require_once PATH_ABSOLUTO_SITIO . '/api/log.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/mysqli.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php';

        $nombreLog = __FUNCTION__ . '_' . date("Ymd");

        //Valida parámetros requeridos para consumo Rest
        if (!defined('URL_PLACETOPAY_REST')) {
            define('URL_PLACETOPAY_REST', '');
            define('LOGIN_PLACETOPAY', '');
            define('SECRETKEY_PLACETOPAY', '');
        }

        /*
         * Construye parámetros adicionales en el consumo Rest CURL
         */
        $seed = date('c');
        //$fechaTmp = strtotime('+1 hours', strtotime($seed));
        //$expiracion = date('c', $fechaTmp);

        $nonce = '';
        $tranKey = '';

        if (function_exists('random_bytes')) {
            $nonce = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $nonce = mt_rand();
        }

        $nonceBase64 = base64_encode($nonce);
        $tranKey = base64_encode(sha1($nonce . $seed . SECRETKEY_PLACETOPAY, true));

        /*
         * Define arreglo de autenticación
         */
        $arregloAutenticacion = array(
            "login" => LOGIN_PLACETOPAY,
            "seed" => $seed,
            "nonce" => $nonceBase64,
            "tranKey" => $tranKey);

        $dataConsulta = array(
            "auth" => $arregloAutenticacion,
        );
        $json_dataConsulta = json_encode($dataConsulta);

        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, URL_PLACETOPAY_REST . "/redirection/api/session/" . $ticketid);
        curl_setopt($ch, CURLOPT_URL, URL_PLACETOPAY_REST . "/api/session/" . $ticketid); // Cambio solicitado por place to pay pendiente de activar
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_dataConsulta);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);

        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: # OK
                    $arrResult = json_decode($result, true);
                    \logApi::general2($nombreLog, $referencia, 'Consulta de ticketid ' . $ticketid . ': ' . var_export($arrResult, true));
                    break;
                default:
                    $msj = 'Código HTTP inesperado: ' . $http_code;
                    \logApi::general2($nombreLog, $referencia, 'Consulta de ticketid ' . $ticketid . ': ' . $msj);
                    $arrResult = null;
                    break;
            }
        }

        curl_close($ch);
        return $arrResult;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $idliquidacion
     * @param type $arrResult
     * @return bool
     */
    public static function asentarPlaceToPay($mysqli = null, $idliquidacion = '', $arrResult = array ()) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/gestionRecibos.php');
        $nombreLog = 'asentarPlaceToPay_' . date("Ymd");
        $estado = '';
        $numeroautorizacion = '';
        $franquicia = '';
        $valortotal = '';
        if (isset($arrResult["status"]["status"])) {
            $estado = $arrResult["status"]["status"];
        }

        if ($estado == 'REJECTED') {
            if (isset($arrResult["payment"])) {
                foreach ($arrResult["payment"] as $key => $value) {
                    $estado = $value["status"]["status"];
                }
            }
        }

        if ($estado != "APPROVED") {
            return false;
        } else {
            $numeroautorizacion = '';
            $franquicia = '';
            $nomban = '';
            $codban = '';
            $valortotal = 0;
            if (isset($arrResult["payment"])) {
                foreach ($arrResult["payment"] as $key => $value) {
                    $numeroautorizacion = $value["authorization"];
                    if (isset($value["franchise"])) {
                        $franquicia = $value["franchise"];
                    }
                    if (isset($value["bankName"])) {
                        $nomban = $value["bankName"];
                    }
                    $valortotal = $value["amount"]["from"]["total"];
                }
            }
            $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $idliquidacion);

            //
            if ($valortotal == 0) {
                $valortotal = $_SESSION["tramite"]["valortotal"];
            }

            // $_SESSION["tramite"]["idestado"] = '07';
            $_SESSION["tramite"]["idformapago"] = '05';
            $_SESSION["tramite"]["proyectocaja"] = '001';
            $_SESSION["tramite"]["origen"] = 'electronico';
            $_SESSION["tramite"]["idfranquicia"] = $franquicia;
            $_SESSION["tramite"]["nombrefranquicia"] = $franquicia;
            $_SESSION["tramite"]["idcodban"] = $codban;
            $_SESSION["tramite"]["nombrebanco"] = $nomban;
            $encuentraITA = strpos($_SESSION["tramite"]["nombrebanco"], 'ITA');
            if ($encuentraITA !== false) {
                $_SESSION["tramite"]["nombrebanco"] = 'ITAU';
            }
            $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;

            switch ($franquicia) {
                case "CR_VS": // visa
                    $_SESSION["tramite"]["pagovisa"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'VISA';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;
                case "RM_MC": // mastercard
                    $_SESSION["tramite"]["pagomastercard"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'MasterCard';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;

                case "_PSE_": // Cuenta de ahorros o corriente PSE
                    $_SESSION["tramite"]["pagoach"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'PSE / ACH';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '09';
                    break;
                case "CR_DN": // Diners
                    $_SESSION["tramite"]["pagodiners"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'Diners';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;
                case "CR_AM": // American
                    $_SESSION["tramite"]["pagoamerican"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'American';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;
                case "CR_CR": // Credencial
                    $_SESSION["tramite"]["pagocredencial"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'Credencial';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;
                case "EFCTY": // Pagado en Efecty
                    $_SESSION["tramite"]["pagoefectivo"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'Sistema efecty';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '10';
                    break;
                default: // Otras formas de pago
                    $_SESSION["tramite"]["pagoach"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = '_OTR_';
                    $_SESSION["tramite"]["nombrefranquicia"] = $franquicia;
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '09';
                    break;
            }

            // ********************************************************************* //
            // Actualiza la liquidación antes de enviarla al SII
            // ********************************************************************* //
            $resultado = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
            if ($resultado) {
                \logApi::general2($nombreLog, $idliquidacion, 'Actualiza la liquidación mreg_liquidacion con la confirmación de Place To Pay');
            } else {
                \logApi::general2($nombreLog, $idliquidacion, '*** ERROR *** de Actualización de la liquidación mreg_liquidacion con la confirmación de Place To Pay');
            }

            // ********************************************************************* //
            // Asentar Pago Modo Función
            // ********************************************************************* //
            $res = \gestionRecibos::asentarRecibos($mysqli, $idliquidacion, '0', '09', date("Ymd"), date("Ymd"), $_SESSION["generales"]["codigousuario"]);
            if ($res["codigoError"] != '0000') {
                \logApi::general2($nombreLog, $idliquidacion, 'Error asentando pago en SII : ' . $res["msgError"]);
                return false;
            } else {
                \logApi::general2($nombreLog, $idliquidacion, 'Asentando pago en SII : ' . $res["numeroRecibo"] . ', ' . $res["numeroOperacion"] . ', ' . $res["fechaRecibo"] . ', ' . $res["horaRecibo"]);
            }
        }

        // ********************************************************************* //
        // Generar log de transacción en BD
        // ********************************************************************* //

        $arrCampos = array(
            'identificacion',
            'fecha',
            'hora',
            'idliquidacion',
            'tipotramite',
            'estado',
            'cus',
            'valortotal',
        );
        $arrValores = array(
            "'" . $_SESSION["tramite"]["identificacionpagador"] . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $idliquidacion . "'",
            "'" . $_SESSION["tramite"]["tipotramite"] . "'",
            "'" . $estado . "'",
            "'" . $numeroautorizacion . "'",
            $_SESSION["tramite"]["valortotal"],
        );

        $resInsertar = insertarRegistrosMysqliApi($mysqli, 'mreg_log_transacciones', $arrCampos, $arrValores);
        if ($resInsertar) {
            \logApi::general2($nombreLog, $idliquidacion, 'Grabó mreg_log_transacciones');
        }
        return true;
    }

    /**
     * 
     * @param type $referencia
     * @param type $arrLiq
     * @return type
     */
    public static function solicitarRedirectGou($referencia = '', $arrLiq = array()) {

        require_once PATH_ABSOLUTO_SITIO . '/api/log.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/mysqli.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php';

        $nombreLog = __FUNCTION__ . '_' . date("Ymd");

        //Valida parámetros requeridos para consumo Rest
        if (!defined('URL_GOU_REST')) {
            define('URL_GOU_REST', '');
            define('LOGIN_GOU', '');
            define('SECRETKEY_GOU', '');
        }

        //Construye parámetros adicionales en el consumo Rest (seed,nonce,nonceBase64,tranceKey)
        $seed = date('c');
        $fechaTmp = strtotime('+30 minutes', strtotime($seed));
        /*
         * Expiración de esta solicitud, el cliente debe terminar el proceso antes de esta fecha. i.e. 2016-07-22T15:43:25-05:00
         */
        $expiracion = date('c', $fechaTmp);
        $nonce = '';
        $tranKey = '';
        if (function_exists('random_bytes')) {
            $nonce = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $nonce = mt_rand();
        }

        $nonceBase64 = base64_encode($nonce);
        $tranKey = base64_encode(sha1($nonce . $seed . SECRETKEY_GOU, true));

        //Define arreglo de autenticación
        $arregloAutenticacion = array(
            "login" => LOGIN_GOU,
            "seed" => $seed,
            "nonce" => $nonceBase64,
            "tranKey" => $tranKey,
        );

        //Define arreglo de Info de Pago
        $arregloPago = array(
            "reference" => $referencia,
            "description" => $arrLiq["tipotramite"],
            "amount" => array(
                "currency" => "COP",
                "total" => $arrLiq["valortotal"],
            ),
        );

        // Homologa los tipos de identificación (CC, CE, TI, SSN,NIT, PPN)
        $tipoIdePlacetopay = '';
        switch (trim($arrLiq["tipoidentificacionpagador"])) {
            case "1":
                $tipoIdePlacetopay = "CC";
                break;
            case "3":
            case "E":
            case "5":
                $tipoIdePlacetopay = "CE";
                break;
            case "4":
                $tipoIdePlacetopay = "TI";
                break;
            case "2":
                $tipoIdePlacetopay = "NIT";
                break;
            default:
                break;
        }

        //Define arreglo de Pagador
        $arregloPagador = array(
            "documentType" => $tipoIdePlacetopay,
            "document" => $arrLiq["identificacionpagador"],
            "name" => $arrLiq["nombrepagador"],
            "surname" => $arrLiq["apellidopagador"],
            "email" => $arrLiq["emailpagador"],
            "address" => array("street" => $arrLiq["direccionpagador"], "city" => retornarNombreMunicipioMysqliApi(null, $arrLiq["municipiopagador"])),
            "mobile" => $arrLiq["movilpagador"],
        );

        //Construye Arreglo de Petición de redireccionamiento
        $data = array(
            "auth" => $arregloAutenticacion,
            "payment" => $arregloPago,
            "buyer" => $arregloPagador,
            "expiration" => $expiracion,
            "returnUrl" => TIPO_HTTP . HTTP_HOST . "/retornoGou.php?accion=return&referencia=" . $referencia,
            "cancelUrl" => TIPO_HTTP . HTTP_HOST . "/retornoGou.php?accion=cancel&referencia=" . $referencia,
            "ipAddress" => \funcionesGenerales::localizarIP(),
            "userAgent" => "PlacetoPay Sandbox",
        );

        //Codifica en formato JSON
        $json_data = json_encode($data);

        //Consume via CURL en servicio Rest de PlacetoPay
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, URL_PLACETOPAY_REST . "/redirection/api/session");
        curl_setopt($ch, CURLOPT_URL, URL_GOU_REST . "/api/session"); // Cambio solicitado por 
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
        $arrResult = json_decode($result, true);
        \logApi::general2($nombreLog, $referencia, 'Solicitud de redirect: ' . var_export($arrResult, true));
        return $arrResult;
    }

    /**
     * 
     * @param type $referencia
     * @param type $ticketid
     * @return type
     */
    public static function consultarGou($referencia = '', $ticketid = '') {

        require_once PATH_ABSOLUTO_SITIO . '/api/log.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/mysqli.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php';

        $nombreLog = __FUNCTION__ . '_' . date("Ymd");

        //Valida parámetros requeridos para consumo Rest
        if (!defined('URL_GOU_REST')) {
            define('URL_GOU_REST', '');
            define('LOGIN_GOU', '');
            define('SECRETKEY_GOU', '');
        }

        /*
         * Construye parámetros adicionales en el consumo Rest CURL
         */
        $seed = date('c');
        //$fechaTmp = strtotime('+1 hours', strtotime($seed));
        //$expiracion = date('c', $fechaTmp);

        $nonce = '';
        $tranKey = '';

        if (function_exists('random_bytes')) {
            $nonce = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $nonce = mt_rand();
        }

        $nonceBase64 = base64_encode($nonce);
        $tranKey = base64_encode(sha1($nonce . $seed . SECRETKEY_GOU, true));

        /*
         * Define arreglo de autenticación
         */
        $arregloAutenticacion = array(
            "login" => LOGIN_GOU,
            "seed" => $seed,
            "nonce" => $nonceBase64,
            "tranKey" => $tranKey);

        $dataConsulta = array(
            "auth" => $arregloAutenticacion,
        );
        $json_dataConsulta = json_encode($dataConsulta);

        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, URL_PLACETOPAY_REST . "/redirection/api/session/" . $ticketid);
        curl_setopt($ch, CURLOPT_URL, URL_GOU_REST . "/api/session/" . $ticketid);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_dataConsulta);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);

        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: # OK
                    $arrResult = json_decode($result, true);
                    \logApi::general2($nombreLog, $referencia, 'Consulta de ticketid ' . $ticketid . ': ' . var_export($arrResult, true));
                    break;
                default:
                    $msj = 'Código HTTP inesperado: ' . $http_code;
                    \logApi::general2($nombreLog, $referencia, 'Consulta de ticketid ' . $ticketid . ': ' . $msj);
                    $arrResult = null;
                    break;
            }
        }

        curl_close($ch);
        return $arrResult;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $idliquidacion
     * @param type $arrResult
     * @return bool
     */
    public static function asentarGou($mysqli = null, $idliquidacion = '', $arrResult = array ()) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/gestionRecibos.php');

        $nombreLog = 'asentarGou_' . date("Ymd");
        $estado = '';
        $numeroautorizacion = '';
        $franquicia = '';
        $valortotal = '';
        if (isset($arrResult["status"]["status"])) {
            $estado = $arrResult["status"]["status"];
        }

        if ($estado == 'REJECTED') {
            if (isset($arrResult["payment"])) {
                foreach ($arrResult["payment"] as $key => $value) {
                    $estado = $value["status"]["status"];
                }
            }
        }

        if ($estado != "APPROVED") {
            return false;
        } else {
            $numeroautorizacion = '';
            $franquicia = '';
            $nomban = '';
            $codban = '';
            $valortotal = 0;
            if (isset($arrResult["payment"])) {
                foreach ($arrResult["payment"] as $key => $value) {
                    $numeroautorizacion = $value["authorization"];
                    if (isset($value["franchise"])) {
                        $franquicia = $value["franchise"];
                    }
                    if (isset($value["bankName"])) {
                        $nomban = $value["bankName"];
                    }
                    $valortotal = $value["amount"]["from"]["total"];
                }
            }
            $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $idliquidacion);

            //
            if ($valortotal == 0) {
                $valortotal = $_SESSION["tramite"]["valortotal"];
            }

            // $_SESSION["tramite"]["idestado"] = '07';
            $_SESSION["tramite"]["idformapago"] = '05';
            $_SESSION["tramite"]["proyectocaja"] = '001';
            $_SESSION["tramite"]["origen"] = 'electronico';
            $_SESSION["tramite"]["idfranquicia"] = $franquicia;
            $_SESSION["tramite"]["nombrefranquicia"] = $franquicia;
            $_SESSION["tramite"]["idcodban"] = $codban;
            $_SESSION["tramite"]["nombrebanco"] = $nomban;
            $encuentraITA = strpos($_SESSION["tramite"]["nombrebanco"], 'ITA');
            if ($encuentraITA !== false) {
                $_SESSION["tramite"]["nombrebanco"] = 'ITAU';
            }
            $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;

            switch ($franquicia) {
                case "CR_VS": // visa
                    $_SESSION["tramite"]["pagovisa"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'VISA';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;
                case "RM_MC": // mastercard
                    $_SESSION["tramite"]["pagomastercard"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'MasterCard';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;

                case "_PSE_": // Cuenta de ahorros o corriente PSE
                    $_SESSION["tramite"]["pagoach"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'PSE / ACH';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '09';
                    break;
                case "CR_DN": // Diners
                    $_SESSION["tramite"]["pagodiners"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'Diners';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;
                case "CR_AM": // American
                    $_SESSION["tramite"]["pagoamerican"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'American';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;
                case "CR_CR": // Credencial
                    $_SESSION["tramite"]["pagocredencial"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'Credencial';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '05';
                    break;
                case "EFCTY": // Pagado en Efecty
                    $_SESSION["tramite"]["pagoefectivo"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = $franquicia;
                    $_SESSION["tramite"]["nombrefranquicia"] = 'Sistema efecty';
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '10';
                    break;
                default: // Otras formas de pago
                    $_SESSION["tramite"]["pagoach"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = '_OTR_';
                    $_SESSION["tramite"]["nombrefranquicia"] = $franquicia;
                    $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                    $_SESSION["tramite"]["idformapago"] = '09';
                    break;
            }

            // ********************************************************************* //
            // Actualiza la liquidación antes de enviarla al SII
            // ********************************************************************* //
            $resultado = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
            if ($resultado) {
                \logApi::general2($nombreLog, $idliquidacion, 'Actualiza la liquidación mreg_liquidacion con la confirmación de GOU');
            } else {
                \logApi::general2($nombreLog, $idliquidacion, '*** ERROR *** de Actualización de la liquidación mreg_liquidacion con la confirmación de GOU');
            }

            // ********************************************************************* //
            // Asentar Pago Modo Función
            // ********************************************************************* //
            $res = \gestionRecibos::asentarRecibos($mysqli, $idliquidacion, '0', '07', date("Ymd"), date("Ymd"), $_SESSION["generales"]["codigousuario"]);
            if ($res["codigoError"] != '0000') {
                \logApi::general2($nombreLog, $idliquidacion, 'Error sentando pago en SII : ' . $res["msgError"]);
                return false;
            } else {
                \logApi::general2($nombreLog, $idliquidacion, 'Sentando pago en SII : ' . $res["numeroRecibo"] . ', ' . $res["numeroOperacion"] . ', ' . $res["fechaRecibo"] . ', ' . $res["horaRecibo"]);
            }
        }

        // ********************************************************************* //
        // Generar log de transacción en BD
        // ********************************************************************* //

        $arrCampos = array(
            'identificacion',
            'fecha',
            'hora',
            'idliquidacion',
            'tipotramite',
            'estado',
            'cus',
            'valortotal',
        );
        $arrValores = array(
            "'" . $_SESSION["tramite"]["identificacionpagador"] . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $idliquidacion . "'",
            "'" . $_SESSION["tramite"]["tipotramite"] . "'",
            "'" . $estado . "'",
            "'" . $numeroautorizacion . "'",
            $_SESSION["tramite"]["valortotal"],
        );

        $resInsertar = insertarRegistrosMysqliApi($mysqli, 'mreg_log_transacciones', $arrCampos, $arrValores);
        if ($resInsertar) {
            \logApi::general2($nombreLog, $idliquidacion, 'Grabó mreg_log_transacciones');
        }
        return true;
    }

    /**
     * 
     * @param type $referencia
     * @param type $arrLiq
     * @return type
     */
    public static function solicitarRedirectZonaVirtual($referencia = '', $arrLiq = array()) {

        require_once PATH_ABSOLUTO_SITIO . '/api/log.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/mysqli.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php';

        $nombreLog = __FUNCTION__ . '_' . date("Ymd");

        //Valida parámetros requeridos para consumo Rest
        if (!defined('URL_ZONAVIRTUAL_REST')) {
            define('URL_ZONAVIRTUAL_REST', '');
            define('ID_ZONAVIRTUAL_REST', '');
            define('RUTA_ZONAVIRTUAL_REST', '');
            define('USUARIO_ZONAVIRTUAL_REST', '');
            define('CLAVE_ZONAVIRTUAL_REST', '');
            define('PSE_ZONAVIRTUAL_REST', '');
        }

        /* Homologa los tipos de identificación :

          En Zona Virtual :

          0 No se usa o Tipo no Identificado
          1 CC Cedula de Ciudadanía  (1)
          2 CE Cedula de Extranjería (3)
          3 NIT Nit Empresa (2)
          4 NUIP Número Único de Identificación
          5 TI Tarjeta de Identidad (4)
          6 PP Pasaporte (5)
          7 IDC Identificador Único del Cliente
          8 CEL En caso de que el identificador sea un número móvil o celular
          9 RC Registro Civil de Nacimiento (R)
          10 DE Documento de Identificación Extranjero (E)
          11 Otro no tipificado

          En SII :

          1    Cédula de ciudadanía
          2    NIT
          3    Cédula de extranjería
          4    Tarjeta de identidad
          5    Pasaporte
          6    Personería jurídica
          E    Documento extranjero
          R    Registro Civil

         */
        $tipoIdeZonaVirtual = '';
        switch (trim($arrLiq["tipoidentificacionpagador"])) {
            case "1":
                $tipoIdeZonaVirtual = "1";
                break;
            case "2":
                $tipoIdeZonaVirtual = "3";
                break;
            case "3":
                $tipoIdeZonaVirtual = "2";
                break;
            case "4":
                $tipoIdeZonaVirtual = "5";
                break;
            case "5":
                $tipoIdeZonaVirtual = "6";
                break;
            case "R":
                $tipoIdeZonaVirtual = "9";
                break;
            case "E":
                $tipoIdeZonaVirtual = "10";
                break;
            default:
                $tipoIdeZonaVirtual = "11";
                break;
        }

        //Construye parámetros en el consumo Rest
        $data = array(
            'InformacionPago' => array(
                'flt_total_con_iva' => $arrLiq["valortotal"],
                'flt_valor_iva' => 0,
                'str_id_pago' => $referencia,
                'str_descripcion_pago' => $arrLiq["tipotramite"],
                'str_email' => $arrLiq["emailpagador"],
                'str_id_cliente' => $arrLiq["identificacionpagador"],
                'str_tipo_id' => $tipoIdeZonaVirtual,
                'str_nombre_cliente' => $arrLiq["nombrepagador"],
                'str_apellido_cliente' => trim($arrLiq["apellidopagador"]) != '' ? $arrLiq["apellidopagador"] : ' - ',
                'str_telefono_cliente' => $arrLiq["movilpagador"],
                'str_opcional1' => '',
                'str_opcional2' => '',
                'str_opcional3' => '',
                'str_opcional4' => '',
                'str_opcional5' => '',
            ),
            'InformacionSeguridad' => array(
                'int_id_comercio' => ID_ZONAVIRTUAL_REST,
                'str_usuario' => USUARIO_ZONAVIRTUAL_REST,
                'str_clave' => CLAVE_ZONAVIRTUAL_REST,
                'int_modalidad' => 1, //Definir códigos y modalidades a enviar
            ),
            'AdicionalesPago' => array(
                0 => array(
                    'int_codigo' => 111, //Oculta campo nombre en tarjeta de crédito (1=>SI 0=>NO)
                    'str_valor' => '0',
                ),
                1 => array(
                    'int_codigo' => 112, //Valor mixto a pagar por defecto
                    'str_valor' => '0',
                ),
            ),
            'AdicionalesConfiguracion' => array(
                0 => array(
                    'int_codigo' => 50, //Código de servicio Principal PSE
                    'str_valor' => PSE_ZONAVIRTUAL_REST,
                ),
                1 => array(
                    'int_codigo' => 100, //Dividir en Varios Medios de Pago (1=>SI 2=>NO)
                    'str_valor' => '2',
                ),
                2 => array(
                    'int_codigo' => 101, //Dividir en varias Tarjetas de Crédito (1=>SI 0=>NO)
                    'str_valor' => '0',
                ),
                3 => array(
                    'int_codigo' => 102, //Dividir en varios PSE (1=>SI 0=>NO)
                    'str_valor' => '0',
                ),
                4 => array(
                    'int_codigo' => 103, //Desactivar PSE (1=>SI 0=>NO)
                    'str_valor' => '0',
                ),
                5 => array(
                    'int_codigo' => 104, //URL Retorno Cliente
                    'str_valor' => TIPO_HTTP . HTTP_HOST . "/retornoZonaVirtual.php?referencia=" . $referencia,
                ),
                6 => array(
                    'int_codigo' => 105, //Valor mínimo de Fracción
                    'str_valor' => '0',
                ),
                7 => array(
                    'int_codigo' => 106, //Número de Fracciones Máximas
                    'str_valor' => '0',
                ),
                8 => array(
                    'int_codigo' => 107, //Desactivar TC (1=>SI 0=>NO)
                    'str_valor' => '0',
                ),
                9 => array(
                    'int_codigo' => 108, //Activa los términos y condiciones (1=>SI 0=>NO)
                    'str_valor' => '1',
                ),
                10 => array(
                    'int_codigo' => 109, //Habilita los pagos mixtos (1=>SI 0=>NO)
                    'str_valor' => '0',
                ),
                11 => array(
                    'int_codigo' => 110, //Pago total del saldo si se realiza por PSE (1=>SI 0=>NO)
                    'str_valor' => '0',
                ),
            ),
        );

        //Codifica en formato JSON
        $json_data = json_encode($data);

        //Consume via CURL en servicio Rest de PlacetoPay
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, URL_ZONAVIRTUAL_REST . "/InicioPago");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
        $arrResult = json_decode($result, true);
        \logApi::general2($nombreLog, $referencia, 'Request: ' . var_export($json_data, true));
        \logApi::general2($nombreLog, $referencia, 'Solicitud de redirect: ' . var_export($arrResult, true));
        return $arrResult;
    }

    /**
     * 
     * @param type $referencia
     * @return type
     */
    public static function consultarZonaVirtual($referencia = '') {

        require_once PATH_ABSOLUTO_SITIO . '/api/log.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/mysqli.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php';

        $nombreLog = __FUNCTION__ . '_' . date("Ymd");

        //Valida parámetros requeridos para consumo Rest
        if (!defined('URL_ZONAVIRTUAL_REST')) {
            define('URL_ZONAVIRTUAL_REST', '');
            define('ID_ZONAVIRTUAL_REST', '');
            define('RUTA_ZONAVIRTUAL_REST', '');
            define('USUARIO_ZONAVIRTUAL_REST', '');
            define('CLAVE_ZONAVIRTUAL_REST', '');
        }

        $data = array(
            "int_id_comercio" => ID_ZONAVIRTUAL_REST,
            "str_usr_comercio" => USUARIO_ZONAVIRTUAL_REST,
            "str_pwd_Comercio" => CLAVE_ZONAVIRTUAL_REST,
            "str_id_pago" => $referencia,
            "int_no_pago" => -1,
        );

        //Codifica en formato JSON
        $json_data = json_encode($data);

        //Consume via CURL en servicio Rest de PlacetoPay
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, URL_ZONAVIRTUAL_REST . "/VerificacionPago");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);

        $arrResult = json_decode($result, true);
        \logApi::general2($nombreLog, $referencia, 'Consulta de referencia : ' . var_export($arrResult, true));

        if ($arrResult["int_cantidad_pagos"] > 1) {

            //Separar transacciones por ;
            $arrDatosPago = explode(";", $arrResult["str_res_pago"]);

            //Eliminar posiciones vacías
            $arrDatosPagoFiltrado = array_filter($arrDatosPago, "trim");

            //Utiliza la última transacción
            $strPago = end($arrDatosPagoFiltrado);

            //Asigna a resultado
            $arrResult["str_res_pago"] = $strPago;
        }

        \logApi::general2($nombreLog, $referencia, 'Consulta de referencia2 : ' . var_export($arrResult, true));

        return $arrResult;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $idliquidacion
     * @param type $arrResult
     * @return bool
     */
    public static function asentarZonaVirtual($mysqli = null, $idliquidacion = '', $arrResult = array ()) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/gestionRecibos.php');

        $nombreLog = 'asentarZonaVirtual_' . date("Ymd");
        \logApi::general2($nombreLog, $idliquidacion, 'Estado OK - Se procede a asentar el pago');

        $status = isset($arrResult["int_estado"]) ? trim($arrResult["int_estado"]) : '';
        if ($status == 1) {
            $cantidadPagos = isset($arrResult["int_cantidad_pagos"]) ? trim($arrResult["int_cantidad_pagos"]) : '';
            $cadenaPago = isset($arrResult["str_res_pago"]) ? trim($arrResult["str_res_pago"]) : '';
            if ($cantidadPagos >= 1) {
                if (trim($cadenaPago) != "") {
                    $arrDatosPago = explode("|", $cadenaPago);
                    $numeroautorizacion = isset($arrDatosPago[1]) ? trim($arrDatosPago[1]) : "";
                    $pagoTerminado = isset($arrDatosPago[3]) ? trim($arrDatosPago[3]) : "";
                    $estadoPago = isset($arrDatosPago[4]) ? trim($arrDatosPago[4]) : "";
                    $valortotal = isset($arrDatosPago[5]) ? trim($arrDatosPago[5]) : "";
                    $medioPago = isset($arrDatosPago[20]) ? trim($arrDatosPago[20]) : "";
                    $nombreBanco = isset($arrDatosPago[24]) ? trim($arrDatosPago[24]) : "";
                    $cus = isset($arrDatosPago[25]) ? trim($arrDatosPago[25]) : "";
                    $codFranquicia = isset($arrDatosPago[29]) ? trim($arrDatosPago[29]) : "";
                }
            }
        } else {
            return false;
        }

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $idliquidacion);

        //
        if ($estadoPago == '1') {
            if (isset($codFranquicia)) {
                $_SESSION["tramite"]["idformapago"] = '05';
                $_SESSION["tramite"]["proyectocaja"] = '001';
                $_SESSION["tramite"]["origen"] = 'electronico';
                $_SESSION["tramite"]["idfranquicia"] = "";
                $_SESSION["tramite"]["nombrefranquicia"] = "";
                $_SESSION["tramite"]["nombrebanco"] = \funcionesGenerales::utf8_decode($nombreBanco);
                $encuentraITA = strpos($_SESSION["tramite"]["nombrebanco"], 'ITA');
                if ($encuentraITA !== false) {
                    $_SESSION["tramite"]["nombrebanco"] = 'ITAU';
                }
                $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;

                //Pagado mediante Tarjeta Crédito
                if ($medioPago == 32) {
                    switch ($codFranquicia) {
                        case "Visa": // visa
                            $_SESSION["tramite"]["pagovisa"] = $valortotal;
                            $_SESSION["tramite"]["idfranquicia"] = 'CR_VS';
                            $_SESSION["tramite"]["nombrefranquicia"] = 'VISA';
                            $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                            $_SESSION["tramite"]["idformapago"] = '05';
                            break;
                        case "Master Card": // mastercard
                            $_SESSION["tramite"]["pagomastercard"] = $valortotal;
                            $_SESSION["tramite"]["idfranquicia"] = 'RM_MC';
                            $_SESSION["tramite"]["nombrefranquicia"] = 'MasterCard';
                            $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                            $_SESSION["tramite"]["idformapago"] = '05';
                            break;
                        case "Diners Club": // Diners
                            $_SESSION["tramite"]["pagodiners"] = $valortotal;
                            $_SESSION["tramite"]["idfranquicia"] = 'CR_DN';
                            $_SESSION["tramite"]["nombrefranquicia"] = 'Diners';
                            $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                            $_SESSION["tramite"]["idformapago"] = '05';
                            break;
                        case "American Express": // American
                            $_SESSION["tramite"]["pagoamerican"] = $valortotal;
                            $_SESSION["tramite"]["idfranquicia"] = 'CR_AM';
                            $_SESSION["tramite"]["nombrefranquicia"] = 'American';
                            $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                            $_SESSION["tramite"]["idformapago"] = '05';
                            break;
                        default: // Otras formas de pago
                            $_SESSION["tramite"]["pagoach"] = $valortotal;
                            $_SESSION["tramite"]["idfranquicia"] = '_OTR_';
                            $_SESSION["tramite"]["nombrefranquicia"] = 'OTRAS';
                            $_SESSION["tramite"]["numeroautorizacion"] = $numeroautorizacion;
                            $_SESSION["tramite"]["idformapago"] = '09';
                            break;
                    }
                }
                //Pagado mediante PSE
                if ($medioPago == 29) {
                    $_SESSION["tramite"]["pagoach"] = $valortotal;
                    $_SESSION["tramite"]["idfranquicia"] = '_PSE_';
                    $_SESSION["tramite"]["nombrefranquicia"] = 'PSE / ACH';
                    $_SESSION["tramite"]["numeroautorizacion"] = $cus;
                    $_SESSION["tramite"]["idformapago"] = '09';
                    $numeroautorizacion = $cus;
                }

                // ********************************************************************* //
                // Actualiza la liquidación antes de enviarla al SII
                // ********************************************************************* //
                $resultado = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
                if ($resultado) {
                    \logApi::general2($nombreLog, $idliquidacion, 'Actualiza la liquidación mreg_liquidacion con la confirmación de Zona Virtual');
                } else {
                    \logApi::general2($nombreLog, $idliquidacion, '*** ERROR *** de Actualización de la liquidación mreg_liquidacion con la confirmación de Zona Virtual');
                }

                // ********************************************************************* //
                // Asentar Pago Modo Función
                // ********************************************************************* //
                // $res = asentarReciboRegistro($mysqli, $reg["idliquidacion"], '0', '09', date("Ymd"), date("Ymd"), $_SESSION["generales"]["codigousuario"]);
                $res = \gestionRecibos::asentarRecibos($mysqli, $idliquidacion, '0', '07', date("Ymd"), date("Ymd"), $_SESSION["generales"]["codigousuario"]);
                if ($res["codigoError"] != '0000') {
                    \logApi::general2($nombreLog, $idliquidacion, 'Error sentando pago en SII : ' . $res["msgError"]);
                    return false;
                } else {
                    \logApi::general2($nombreLog, $idliquidacion, 'Sentando pago en SII : ' . $res["numeroRecibo"] . ', ' . $res["numeroOperacion"] . ', ' . $res["fechaRecibo"] . ', ' . $res["horaRecibo"]);
                    return true;
                }
            } else {
                \logApi::general2($nombreLog, $idliquidacion, 'Error sentando pago : No hay datos completos por parte de Zona Virtual para sentar el pago');
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $idliquidacion
     * @param type $arrResult
     * @return bool
     */
    public static function asentarAvisorRest($mysqli = null, $idliquidacion = '', $arrResult = array ()) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/gestionRecibos.php');

        $nombreLog = 'asentarAvisorRest_' . date("Ymd");
        \logApi::general2($nombreLog, $idliquidacion, 'Estado OK - Se procede a asentar el pago');
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $idliquidacion);
        $_SESSION["tramite"]["pagoefectivo"] = 0;
        $_SESSION["tramite"]["pagocheque"] = 0;
        $_SESSION["tramite"]["pagoach"] = 0;
        $_SESSION["tramite"]["pagovisa"] = 0;
        $_SESSION["tramite"]["pagomastercard"] = 0;
        $_SESSION["tramite"]["pagoamerican"] = 0;
        $_SESSION["tramite"]["pagodiners"] = 0;
        $_SESSION["tramite"]["pagocredencial"] = 0;
        $_SESSION["tramite"]["pagoach"] = 0;
        $_SESSION["tramite"]["pagotdebito"] = 0;
        $_SESSION["tramite"]["pagoprepago"] = 0;
        $_SESSION["tramite"]["pagoafiliado"] = 0;
        $_SESSION["tramite"]["idformapago"] = '05';
        $_SESSION["tramite"]["numeroautorizacion"] = $arrResult["TrazabilityCode"];
        $_SESSION["tramite"]["cajero"] = 'USUPUBXX';
        $_SESSION["tramite"]["idoperador"] = 'USUPUBXX';
        $_SESSION["tramite"]["idusuario"] = 'USUPUBXX';
        $_SESSION["tramite"]["idestado"] = '06'; // Estado de la liquidacion - pendiente de pago            
        $finame = $arrResult["FiName"];
        $transValue = doubleval($arrResult["TransValue"]);

        // Evalua forma de pago para asignar valores, franquicias y nombres de bancos
        $asignofranquicia = 'no';
        if ($finame == 'VISA') {
            $_SESSION["tramite"]["idfranquicia"] = 'CR_VS';
            $_SESSION["tramite"]["nombrefranquicia"] = 'VISA';
            $_SESSION["tramite"]["idcodban"] = '00';
            $_SESSION["tramite"]["nombrebanco"] = ''; // Nombre del banco
            $_SESSION["tramite"]["pagovisa"] = $transValue;
            $asignofranquicia = 'si';
        }
        if ($finame == 'MASTERCARD') {
            $_SESSION["tramite"]["idfranquicia"] = 'RM_MC';
            $_SESSION["tramite"]["nombrefranquicia"] = 'MASTERCARD';
            $_SESSION["tramite"]["idcodban"] = '00';
            $_SESSION["tramite"]["nombrebanco"] = ''; // Nombre del banco
            $_SESSION["tramite"]["pagomastercard"] = $transValue;
            $asignofranquicia = 'si';
        }
        if ($finame == 'CREDENCIAL') {
            $_SESSION["tramite"]["idfranquicia"] = 'CR_CR';
            $_SESSION["tramite"]["nombrefranquicia"] = 'CREDENCIAL';
            $_SESSION["tramite"]["idcodban"] = '00';
            $_SESSION["tramite"]["nombrebanco"] = ''; // Nombre del banco
            $_SESSION["tramite"]["pagocredencial"] = $transValue;
            $asignofranquicia = 'si';
        }
        if ($finame == 'AMEX' || $finame == 'AMERICAN') {
            $_SESSION["tramite"]["idfranquicia"] = 'CR_AM';
            $_SESSION["tramite"]["nombrefranquicia"] = 'AMERICAN EXPRESS';
            $_SESSION["tramite"]["idcodban"] = '00';
            $_SESSION["tramite"]["nombrebanco"] = ''; // Nombre del banco
            $_SESSION["tramite"]["pagoamerican"] = $transValue;
            $asignofranquicia = 'si';
        }
        if ($finame == 'DINERS') {
            $_SESSION["tramite"]["idfranquicia"] = 'CR_DN';
            $_SESSION["tramite"]["nombrefranquicia"] = 'DINERS CLUB';
            $_SESSION["tramite"]["idcodban"] = '00';
            $_SESSION["tramite"]["nombrebanco"] = ''; // Nombre del banco
            $_SESSION["tramite"]["pagodiners"] = $transValue;
            $asignofranquicia = 'si';
        }

        if ($asignofranquicia == 'no') {
            $_SESSION["tramite"]["idfranquicia"] = '_PSE_';
            $_SESSION["tramite"]["nombrefranquicia"] = 'PSE / ACH';
            $_SESSION["tramite"]["idcodban"] = '00';
            $_SESSION["tramite"]["nombrebanco"] = ''; // Nombre del banco
            $_SESSION["tramite"]["pagoach"] = $transValue;
            $asignofranquicia = 'si';
        }

        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        //
        $res = \gestionRecibos::asentarRecibos($mysqli, $_SESSION["tramite"]["idliquidacion"], '0', '07', date("Ymd"), date("Ymd"), $_SESSION["tramite"]["idusuario"]);
        if ($res["codigoError"] != '0000') {
            \logApi::general2($nombreLog, $_SESSION["tramite"]["idliquidacion"], 'Error asentando el pago en el SII : ' . $res["msgError"]);
            return false;
        } else {
            \logApi::general2($nombreLog, $_SESSION["tramite"]["idliquidacion"], 'Pago asentado en el SII. - Recibo ' . $res["numeroRecibo"]);
            return true;
        }
    }

    /**
     * 
     * @param type $referencia
     * @param type $ticketid
     * @return bool
     */
    public static function consultarAvisorRest($referencia = '', $ticketid = '') {

        require_once PATH_ABSOLUTO_SITIO . '/api/log.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/mysqli.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesRegistrales.php';

        $nombreLog = 'consultarAvisorRest_' . date("Ymd");

        $tokenavisor = '';
        $url = URL_API_AVISOR_REST . '/getSessionToken';
        $params = array();
        $params["EntityCode"] = COMERCE_CODE_AVISOR_REST;
        $params["ApiKey"] = API_KEY_AVISOR_REST;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $result = curl_exec($ch);
        \logApi::general2($nombreLog, '', 'Respuesta token avisor rest. ' . $result);
        if (\funcionesGenerales::isJson($result)) {
            $token = json_decode($result, true);
            if ($token["ReturnCode"] == 'SUCCESS') {
                \logApi::general2($nombreLog, '', 'Token avisor rest. ' . $token["SessionToken"]);
                $tokenavisor = $token["SessionToken"];
            }
        }

        if ($tokenavisor === '') {
            \logApi::general2($nombreLog, '', 'No fue posible retornar el token de avisor rest');
            return false;
        }

        // ************************************************************************************************* //
        // get Transaction
        // ************************************************************************************************ //
        $tranState = '';
        $transValue = 0;
        $trazabilityCode = '';
        $url = URL_API_AVISOR_REST_GET_TRANSACTION . '/getTransactionInformation';
        $params = array();
        $params["EntityCode"] = COMERCE_CODE_AVISOR_REST;
        $params["SessionToken"] = $tokenavisor;
        $params["TicketId"] = $ticketid;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $result = curl_exec($ch);
        \logApi::general2($nombreLog, '', 'Respuesta getTransactionInformation avisor rest. ' . $result);

//
        if (\funcionesGenerales::isJson($result)) {
            $create = json_decode($result, true);
            return $create;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $referencia
     * @return type
     */
    public static function consultarEpayco($referencia = '') {
        require_once PATH_ABSOLUTO_SITIO . '/api/log.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/mysqli.php';
        require_once PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php';
        $nombreLog = 'consultarEpayco_' . date("Ymd");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "https://secure.epayco.co/validation/v1/reference/" . $referencia);
        $data1 = curl_exec($ch);
        curl_close($ch);
        \logApi::general2($nombreLog, '', "Consulta estado liquidacion en epayco: " . $data1);
        $data = json_decode($data1, true);
        return $data;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $tipotramite
     * @param type $numrec
     * @return string
     */
    public static function validarTipoTramiteRetorno($mysqli = null, $tipotramite = '', $numrec = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');

        $enlace = TIPO_HTTP . HTTP_HOST . "/scripts/mregPagoElectronico.php?accion=validarseleccion&_numrec=" . $numrec . '&session_parameters=' . \funcionesGenerales::armarVariablesPantalla();
        return $enlace;

    }

}
