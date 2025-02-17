<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;
use PDFA;

trait verificarParametrosFirmado {

    public function verificarParametrosFirmado(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/PDFA.class.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
        exit ();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("usuariofirmante", true);

        if (!$api->validarToken('verificarParametrosFirmado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $usuarioFirmante = $_SESSION["entrada"]["usuariofirmante"];

        if (!defined('PATH_COMMON_BASE')) {
            define('PATH_COMMON_BASE', '/opt');
        }

        if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9991";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizo el archivo de configuración';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        require_once (PATH_COMMON_BASE . '/commonBase.php');

        $fechaValidacion = date("Ymd");

        $diaV = substr($fechaValidacion, 6, 2);
        $mesV = substr($fechaValidacion, 4, 2);
        $anoV = substr($fechaValidacion, 0, 4);

        $ctrlFechaV = checkdate($mesV, $diaV, $anoV);
        if (!$ctrlFechaV) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9992";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha de validación no es correcta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if (defined($usuarioFirmante)) {

            $regFirma = explode("|", constant($usuarioFirmante));

            $rutaIDFirma = trim($regFirma[1]);

            if ($usuarioFirmante == 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_0') {
                $fechaCaducidad = trim($regFirma[4]);

                if (empty($rutaIDFirma)) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9993";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizo el ID de firma digital';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            } else {
                $fechaCaducidad = trim($regFirma[3]);

                if (!file_exists($rutaIDFirma)) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9994";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizo el archivo de firma digital';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            if (trim($fechaCaducidad) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9995";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha de caducidad no esta definida';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {

                $diaC = substr($fechaCaducidad, 6, 2);
                $mesC = substr($fechaCaducidad, 4, 2);
                $anoC = substr($fechaCaducidad, 0, 4);

                $ctrlFechaC = checkdate($mesC, $diaC, $anoC);
                if (!$ctrlFechaC) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha de caducidad no es válida';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }

            $fechaValidacionUnix = strtotime($fechaValidacion);
            $fechaCaducidadUnix = strtotime($fechaCaducidad);

            if ($fechaValidacionUnix > $fechaCaducidadUnix) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La fecha de validación [' . $fechaValidacion . '] supera o es igual a [' . $fechaCaducidad . ']';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9998";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El nombre de la firma esta mal formado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logSii2::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
