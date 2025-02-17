<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;
use PDFA;

trait firmarDigitalmenteDocumento {

    public function firmarDigitalmenteDocumento(API $api) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/PDFA.class.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');
        
        //
        $nameLog = 'firmarDigitalmenteDocumento_' . date ("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['url'] = '';
        $_SESSION['jsonsalida']['codigofirmapdf'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("url", true);

        if (!$api->validarToken('firmarDigitalmenteDocumento', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], 'Inicia proceso de firma digital a documento. Recibe: ' . json_encode($_SESSION["entrada"]));

        // ********************************************************************** //
        // Pdf firmado
        // ********************************************************************** //         
        $mysqli = conexionMysqliApi();
        $aleatorio = $_SESSION["generales"]["codigoempresa"] . \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
        $mysqli->close();
        
        //
        $in1 = $aleatorio . "-" . date("Hms") . date("His") . ".pdf";
        $rutaInPDF = PATH_ABSOLUTO_SITIO . "/tmp/" . $in1;
        $rutaOutFirmado = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $aleatorio . '_firmado.pdf';
        $rutaOutHtmlFirmado = TIPO_HTTP . HTTP_HOST . '/tmp/' . $aleatorio . '_firmado.pdf';
        $rutaOutHtmlFirmado = TIPO_HTTP . HTTP_HOST . '/tmp/' . $in1;
        
        if (file_exists($rutaOutFirmado)) {
            unlink ($rutaOutFirmado);
        }
        
        //
        \funcionesGenerales::descargaPdf($_SESSION["entrada"]["url"], $rutaInPDF);
        if (!file_exists($rutaInPDF)) {
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], 'No fue posible descargar del sitio origen el documento a firmar');
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], '');
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible descargar del sitio origen el documento a firmar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        if (!isset($_SESSION["entrada"]["metodofirmado"]) || $_SESSION["entrada"]["metodofirmado"] == '') {
            $_SESSION["entrada"]["metodofirmado"] = 'PFX';
        }
        
        if ($_SESSION["entrada"]["metodofirmado"] == 'CERTITOKEN_API') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Metodo de firmado no implementado en esta versión.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        if (!isset($_SESSION["entrada"]["firmante"]) || $_SESSION["entrada"]["firmante"] == '') {
            if ($_SESSION["entrada"]["metodofirmado"] == 'CERTITOKEN' || $_SESSION["entrada"]["metodofirmado"] == 'CERTITOKEN_API') {
                $_SESSION["entrada"]["firmante"] = '0';
            }else {
                $_SESSION["entrada"]["firmante"] = 'PJ';
            }
        }
        
        //
        $usuFirmante = '';        
        if ($_SESSION["entrada"]["metodofirmado"] == 'PFX') {
            $usuFirmante = 'FIRMA_' . $_SESSION["generales"]["codigoempresa"] . '_' . $_SESSION["entrada"]["firmante"];
        } 
        if ($_SESSION["entrada"]["metodofirmado"] == 'CERTITOKEN') {
            $usuFirmante = 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_' . $_SESSION["entrada"]["firmante"];
        } 
        if ($_SESSION["entrada"]["metodofirmado"] == 'CERTITOKEN_API') {
            $usuFirmante = 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_' . $_SESSION["entrada"]["firmante"];
        }

        //
        $pdfa = new PDFA();
        $insVerificar = $pdfa->verificarParametrosFirmado($usuFirmante, date("Ymd"));
        if (!$insVerificar || $insVerificar === false) {
            unset($pdfa);
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], 'El documento no pasó la verificación de parametrización de firma');
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], '');
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El documento no pasó la verificación de parametrización de firma';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], 'Verifico parametros de firmado');
        $salida = $pdfa->generarPDFAfirmado($aleatorio, $rutaInPDF, 'no', 'LETTER', $_SESSION["entrada"]["firmante"], $_SESSION["entrada"]["metodofirmado"]);
        unset ($pdfa);
        
        //
        if ($salida === false) {
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], 'Error, no pudo ser firmado el documento');
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], '');
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El documento no pudo ser firmado digitalmente';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], 'Documento firmado digitalmente'); 
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], 'Salida: '. $rutaOutHtmlFirmado); 
            \logApi::general2($nameLog, $_SESSION["entrada"]["usuariows"], '');
            $_SESSION['jsonsalida']['url'] = $rutaOutHtmlFirmado;
            $_SESSION['jsonsalida']['codigofirmapdf'] = 'API_' . $aleatorio;           
        }

        /*
          $pdfa = new PDFA();
          $firma = 'FIRMA_' . $_SESSION["entrada"]["codigoempresa"] . '_PJ';
          $insVerificar = $pdfa->verificarParametrosFirmado($firma, date("Ymd"));
          if (!$insVerificar || $insVerificar === false) {
          unset($pdfa);
          $_SESSION["jsonsalida"]["codigoerror"] = "9999";
          $_SESSION["jsonsalida"]["mensajeerror"] = 'El documento no pasó la verificación de parametrización de firma';
          } else {
          $pdfa->firmarPDF($_SESSION["entrada"]["usuariows"], $rutaInPDF, $rutaOutPDF, $firma, 0, 0, 0, 0);
          if (file_exists($rutaOutPDF)) {
          $_SESSION['jsonsalida']['url'] = $ruta_publica;
          $_SESSION['jsonsalida']['codigofirmapdf'] = 'API_' . $aleatorio;
          } else {
          $_SESSION["jsonsalida"]["codigoerror"] = "9999";
          $_SESSION["jsonsalida"]["mensajeerror"] = 'El documento no pudo guardarse de forma correcta.';
          }
          }
         */


        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
