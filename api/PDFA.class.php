<?php

/**
 * Clase para la manipulación de PDF/A
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2015/03/27
 *
 */
class PDFA {
    //WSI - 20150824 - Se adiciona parámetro $tamanioPagina = 'LETTER'
    //WSI - 20161004 - Inclusión de nivel de certificación 3 por defecto (Permite modificar formulario, adición de firma y comentarios)
    //WSI - 20161115 - Inclusión de integración con CERTITOKEN
    //WSI - 20161202 - Inclusión de integración con JSIGNPDF (PARA ESTAMPA CRONOLÓGICA)
    //WSI - 20161204 - Separación de métodos de firmado
    //WSI - 20170418 - Ajuste para que automáticamente utilice path absoluto cuando se invoca desde APIRest
    //JINT - 20210925 - Se incliuye parámetro firmante para indicar si se firma con PFX-PJ, CERTITOKEN-PJ, PFX-XXXX, CERTITOKEN-, 

    /**
     * 
     * @param type $aleatorio
     * @param type $rutaPDFRepositorioBase
     * @param type $convertirPDFA
     * @param type $tamanioPagina
     * @param type $firmante
     * @param type $metodofirmado
     * @return boolean
     */
    public function generarPDFAfirmado($aleatorio, $rutaPDFRepositorioBase, $convertirPDFA = 'si', $tamanioPagina = 'LETTER', $firmante = '', $metodofirmado = '') {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $nivelCertificacion = 3;
        $nameLog = 'generarPDFAfirmado_' . date("Ymd");

        /*
         * 0 - NOT_CERTIFIED
         * 1 - CERTIFIED_NO_CHANGES_ALLOWED
         * 2 - CERTIFIED_FORM_FILLING
         * 3 - CERTIFIED_FORM_FILLING_AND_ANNOTATIONS
         */

        //Rutas pdf temporales
        $rutaOutPDFA = str_replace(array("\\", "//", "\/"), "/", $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $aleatorio . '_tmp.pdf');
        $rutaOutFirmado = str_replace(array("\\", "//", "\/"), "/", $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $aleatorio . '_firmado.pdf');
        $rutaPDFRepositorio = str_replace(array("\\", "//", "\/"), "/", $rutaPDFRepositorioBase);

        //
        \logApi::general2($nameLog, $aleatorio, 'Archivo a firmar : ' . $rutaPDFRepositorioBase);
        \logApi::general2($nameLog, $aleatorio, 'Path origen: ' . $rutaPDFRepositorio);
        \logApi::general2($nameLog, $aleatorio, 'Metodo firmado: ' . $metodofirmado);
        \logApi::general2($nameLog, $aleatorio, 'Firmante: ' . $firmante);

        //
        if ($convertirPDFA == 'si') {
            $this->generarPDFA($aleatorio, $rutaPDFRepositorio, $rutaOutPDFA, 'PDF_A_3B', $tamanioPagina);
            if (file_exists($rutaOutPDFA)) {
                $peso = filesize($rutaOutPDFA);
                \logApi::general2($nameLog, $aleatorio, 'Peso Archivo sin firmar:' . \funcionesGenerales::FileSizeConvert($peso));
                $x1 = $this->renombrarArchivoPDF($aleatorio, $rutaOutPDFA, $rutaPDFRepositorio);
                if ($x1) {
                    \logApi::general2($nameLog, $aleatorio, 'Trasladado el PDF/A');
                } else {
                    \logApi::general2($nameLog, $aleatorio, 'No fue posible trasladar el PDF/A');
                }
            } else {
                \logApi::general2($nameLog, $aleatorio, 'No se pudo contruir el PDF/A - Se reutiliza PDF normal sin el estandar PDF/A');
                return false;
            }
        } else {
            \logApi::general2($nameLog, $aleatorio, 'No fue necesario convetir el documento a PDF/A');
        }

        if ($metodofirmado == '' && $firmante == '') {
            if (CERTIFICADOS_FIRMA_DIGITAL == 'CERTITOKEN') {
                \logApi::general2($nameLog, $aleatorio, 'Firmara mediante certitoken, entrada');
                $usuarioFirmante = 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_0';
                $this->firmarCertitoken($aleatorio, $rutaPDFRepositorio, $rutaOutFirmado, $usuarioFirmante, 430.3125, 40.965515, 579.2143, 9.558638, $nivelCertificacion);
                \logApi::general2($nameLog, $aleatorio, 'Firmara mediante certitoken, salida');
            } else {
                if (CERTIFICADOS_FIRMA_DIGITAL == 'CERTITOKEN_API') {
                    \logApi::general2($nameLog, $aleatorio, 'Firmara mediante certitoken_api, entrada');
                    $usuarioFirmante = 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_0';
                    $this->firmarCertitokenApi($aleatorio, $rutaPDFRepositorio, $rutaOutFirmado, $usuarioFirmante, 430.3125, 40.965515, 579.2143, 9.558638, $nivelCertificacion);
                    \logApi::general2($nameLog, $aleatorio, 'Firmara mediante certitoken_api, salida');
                } else {
                    \logApi::general2($nameLog, $aleatorio, 'Firmara mediante p12, entrada');
                    $usuarioFirmante = 'FIRMA_' . $_SESSION["generales"]["codigoempresa"] . '_0';
                    $this->firmarPDF($aleatorio, $rutaPDFRepositorio, $rutaOutFirmado, $usuarioFirmante, 430.3125, 40.965515, 579.2143, 9.558638, $nivelCertificacion);
                    \logApi::general2($nameLog, $aleatorio, 'Firmara mediante p12, salida');
                }
            }
        }

        //
        if ($metodofirmado != '' && $firmante != '') {
            if ($metodofirmado == 'CERTITOKEN') {
                $usuarioFirmante = 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_' . $firmante;
                $this->firmarCertitoken($aleatorio, $rutaPDFRepositorio, $rutaOutFirmado, $usuarioFirmante, 430.3125, 40.965515, 579.2143, 9.558638, $nivelCertificacion);
            }
            if ($metodofirmado == 'CERTITOKEN_API') {
                \logApi::general2($nameLog, $aleatorio, 'Métodop certitoken api no habilitado');
                return false;
                // $usuarioFirmante = 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_' . $firmante;
                // $this->firmarCertitokenApi($aleatorio, $rutaPDFRepositorio, $rutaOutFirmado, $usuarioFirmante, 430.3125, 40.965515, 579.2143, 9.558638, $nivelCertificacion);
            }
            if ($metodofirmado == 'PFX') {
                $usuarioFirmante = 'FIRMA_' . $_SESSION["generales"]["codigoempresa"] . '_' . $firmante;
                $this->firmarPDF($aleatorio, $rutaPDFRepositorio, $rutaOutFirmado, $usuarioFirmante, 430.3125, 40.965515, 579.2143, 9.558638, $nivelCertificacion);
            }
        }

        if (file_exists($rutaOutFirmado)) {
            $peso = filesize($rutaOutFirmado);
            \logApi::general2($nameLog, $aleatorio, 'Peso Archivo Firmado:' . \funcionesGenerales::FileSizeConvert($peso));
            if ($peso > 0) {
                $x2 = self::renombrarArchivoPDF($aleatorio, $rutaOutFirmado, $rutaPDFRepositorio);
                if ($x2) {
                    \logApi::general2($nameLog, $aleatorio, 'Trasladado el  PDF/A firmado a ' . $rutaPDFRepositorio);
                } else {
                    \logApi::general2($nameLog, $aleatorio, 'No fue posible trasladar el PDF/A firmado digitalmente');
                }
            } else {
                \logApi::general2($nameLog, $aleatorio, 'El archivo PDF/A presenta inconsistencias - Se reutiliza PDF/A sin el firmado digital');
                $txtMensaje = '<strong>ADVERTENCIA</strong><p>No se pudo construir el PDF/A con firma digital ' . $usuarioFirmante . ' ';
                $txtMensaje .= 'correspondiente al indicador ' . $aleatorio . '. Informe de este hecho a la Mesa de Ayuda de CONFEC&Aacute;MARAS ';
                $txtMensaje .= 'para realizar la validaci&oacute;n de la configuraci&oacute;n de las firmas digitales del SII y que proceso ';
                $txtMensaje .= 'se afect&oacute; en el incidente.</p>';
                $emailnot = EMAIL_NOTIFICACION_TRANSACCIONES;
                if (trim($emailnot) == '') {
                    $emailnot = EMAIL_ADMIN_PORTAL;
                }
                $notificaCamara = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emailnot, 'Error en Firmado Digital de PDF', $txtMensaje);
                if ($notificaCamara) {
                    \logApi::general2($nameLog, $aleatorio, 'Notificado a ' . $emailnot);
                } else {
                    \logApi::general2($nameLog, $aleatorio, 'Fallo el envio de mail');
                }
                return false;
            }
        } else {
            \logApi::general2($nameLog, $aleatorio, 'No se pudo contruir el PDF/A firmado - Se reutiliza PDF/A sin el firmado digital');
            return false;
        }

        return true;
    }

    function renombrarArchivoPDF($aleatorio = '', $rutaInPDF = '', $rutaOutPDF = '') {
        if (substr(PHP_VERSION, 0, 1) == '7' || substr(PHP_VERSION, 0, 1) == '8' || substr(PHP_VERSION, 0, 1) == '9') {
            return $this->renombrarArchivoPDFPhp7($aleatorio, $rutaInPDF, $rutaOutPDF);
        }

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if (file_exists($rutaInPDF)) {

            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                \logApi::general2(__FUNCTION__, $aleatorio, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION["generales"]["pathabsoluto"] . '/components/phpseclib');
            require_once ('Net/SSH2.php');
            ini_set('display_errors', '1');

            $ssh = new Net_SSH2('localhost');
            if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                \logApi::general2(__FUNCTION__, $aleatorio, 'Fallo la autenticación SSH');
                return false;
            }
            $comando = $ssh->exec("mv " . $rutaInPDF . " " . $rutaOutPDF . "");

            if (!empty($comando)) {

                \logApi::general2(__FUNCTION__, $aleatorio, 'ERROR:' . $comando);
                $txt = '';
                foreach ($ssh->message_log as $log) {
                    $txt .= $log;
                }
                return false;
            } else {
                \logApi::general2(__FUNCTION__, $aleatorio, $rutaInPDF);
                $ssh->exec('chmod 777 ' . $rutaOutPDF);
                return true;
            }

            unset($ssh);
            return true;
        } else {
            \logApi::general2(__FUNCTION__, $aleatorio, 'No se localizo el archivo pdf');
            return false;
        }
    }

    function renombrarArchivoPDFPhp7($aleatorio = '', $rutaInPDF = '', $rutaOutPDF = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if (file_exists($rutaInPDF)) {

            $session = ssh2_connect('localhost', 22);
            if (!$session) {
                \logApi::general2(__FUNCTION__, $aleatorio, 'Fallo conexion SSH');
                return false;
            }
            if (!ssh2_auth_password($session, SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                unset($session);
                \logApi::general2(__FUNCTION__, $aleatorio, 'Fallo la autenticación SSH');
                return false;
            }

            $ntmp = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-' . date("Ymd") . '-' . date("His") . '.mv';
            ssh2_exec($session, "mv " . $rutaInPDF . " " . $rutaOutPDF . " > " . $ntmp);
            $resultado = file_get_contents($ntmp);
            ssh2_exec($session, "chmod 777 " . $rutaOutPDF);
            unset($session);
            return true;
        } else {
            \logApi::general2(__FUNCTION__, $aleatorio, 'No se localizo el archivo pdf');
            return false;
        }
    }

    public function verificarParametrosFirmado($usuarioFirmante, $fechaValidacion) {


        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'verificarParametrosFirmado_' . date("Ymd");
        $usuarioFirmante1 = $usuarioFirmante;

        if (!defined('PATH_COMMON_BASE')) {
            define('PATH_COMMON_BASE', '/opt');
        }

        if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
            \logApi::general2($nameLog, $usuarioFirmante, 'No se localizo el archivo de configuración');
            return false;
        }

        require_once (PATH_COMMON_BASE . '/commonBase.php');

        ini_set('display_errors', '1');

        if (trim($fechaValidacion) == '') {
            $_SESSION["codigoerror"] = 'La fecha de validación no esta definida';
            \logApi::general2($nameLog, $usuarioFirmante, $_SESSION["codigoerror"]);
            return false;
        } else {

            $diaV = substr($fechaValidacion, 6, 2);
            $mesV = substr($fechaValidacion, 4, 2);
            $anoV = substr($fechaValidacion, 0, 4);

            $ctrlFechaV = checkdate($mesV, $diaV, $anoV);
            if (!$ctrlFechaV) {
                $_SESSION["codigoerror"] = 'La fecha de validación no es correcta';
                \logApi::general2($nameLog, $usuarioFirmante, $_SESSION["codigoerror"]);
                return false;
            }
        }

        if (defined($usuarioFirmante1)) {

            $regFirma = explode("|", constant($usuarioFirmante1));

            $rutaIDFirma = trim($regFirma[1]);

            if ($usuarioFirmante == 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_0') {
                $fechaCaducidad = trim($regFirma[4]);

                if (empty($rutaIDFirma)) {
                    $_SESSION["codigoerror"] = 'No se localizo el ID de firma digital';
                    \logApi::general2($nameLog, $usuarioFirmante, $_SESSION["codigoerror"]);
                    return false;
                }
            } else {
                $fechaCaducidad = trim($regFirma[3]);

                if (!file_exists($rutaIDFirma)) {
                    $_SESSION["codigoerror"] = 'No se localizo el archivo de firma digital';
                    \logApi::general2($nameLog, $usuarioFirmante, $_SESSION["codigoerror"]);
                    return false;
                }
            }

            if (trim($fechaCaducidad) == '') {
                $_SESSION["codigoerror"] = 'La fecha de caducidad no esta definida';
                \logApi::general2($nameLog, $usuarioFirmante, $_SESSION["codigoerror"]);
                return false;
            } else {

                $diaC = substr($fechaCaducidad, 6, 2);
                $mesC = substr($fechaCaducidad, 4, 2);
                $anoC = substr($fechaCaducidad, 0, 4);

                $ctrlFechaC = checkdate($mesC, $diaC, $anoC);
                if (!$ctrlFechaC) {

                    $_SESSION["codigoerror"] = 'La fecha de caducidad no válida';
                    \logApi::general2($nameLog, $usuarioFirmante, $_SESSION["codigoerror"]);
                    return false;
                }
            }

            $fechaValidacionUnix = strtotime($fechaValidacion);
            $fechaCaducidadUnix = strtotime($fechaCaducidad);

            if ($fechaValidacionUnix > $fechaCaducidadUnix) {
                $_SESSION["codigoerror"] = 'La fecha de validación [' . $fechaValidacion . '] supera o es igual a [' . $fechaCaducidad . ']';
                \logApi::general2($nameLog, $usuarioFirmante, $_SESSION["codigoerror"]);
                return false;
            }
        } else {

            $_SESSION["codigoerror"] = 'El nombre de la firma esta mal formado';
            \logApi::general2($nameLog, $usuarioFirmante, $_SESSION["codigoerror"]);
            return false;
        }
        return true;
    }

    function firmarPDF($aleatorio = '', $rutaInPDF = '', $rutaOutPdfFirmado = '', $usuarioFirmante = '', $lx = '', $ly = '', $rx = '', $ry = '', $nivelCertificacion = 3) {
        if (substr(PHP_VERSION, 0, 1) == '7' || substr(PHP_VERSION, 0, 1) == '8' || substr(PHP_VERSION, 0, 1) == '9') {
            return $this->firmarPDFPhp7($aleatorio, $rutaInPDF, $rutaOutPdfFirmado, $usuarioFirmante, $lx, $ly, $rx, $ry, $nivelCertificacion);
        }

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if ($aleatorio == '') {
            $aleatorio = $_SESSION["generales"]["codigousuario"];
        }


        switch ($nivelCertificacion) {
            case '0':
                $nivelCertificacionLbl = 'NOT_CERTIFIED';
                break;
            case '1':
                $nivelCertificacionLbl = 'CERTIFIED_NO_CHANGES_ALLOWED';
                break;
            case '2':
                $nivelCertificacionLbl = 'CERTIFIED_FORM_FILLING';
                break;
            case '3':
                $nivelCertificacionLbl = 'CERTIFIED_FORM_FILLING_AND_ANNOTATIONS';
                break;
        }


        if (file_exists($rutaInPDF)) {

            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!defined('PATH_JAVA')) {
                define('PATH_JAVA', '');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                \logApi::general2('generarPDFAfirmado', $aleatorio, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION["generales"]["pathabsoluto"] . '/includes/phpseclib');
            require_once ('Net/SSH2.php');
            ini_set('display_errors', '1');

            $ssh = new Net_SSH2('localhost');

            if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                unset($ssh);
                \logApi::general2('generarPDFAfirmado', $aleatorio, 'Fallo la autenticación SSH');
                return false;
            }

            if (defined($usuarioFirmante)) {
                $regFirma = explode("|", constant($usuarioFirmante));
                $rutaFirma = trim($regFirma[1]);
                $pwdFirma = trim($regFirma[2]);
                $pathJava = str_replace(".", "", PATH_JAVA);
                \logApi::general2('generarPDFAfirmado', $aleatorio, 'Solicita firmado con p12');
                $comandoJava = $pathJava . "java -Xms128M -Xmx2048M -XX:PermSize=128M -XX:MaxPermSize=2048M -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/firmadoDigital.jar " .
                        $rutaFirma . " " .
                        "'" . $pwdFirma . "' " .
                        $rutaInPDF . " " .
                        $rutaOutPdfFirmado . " " .
                        $lx . " " .
                        $ly . " " .
                        $rx . " " .
                        $ry . " " .
                        $nivelCertificacion . " ";
                \logApi::general2('generarPDFAfirmado', $aleatorio, 'Comando a ejecutar :' . $comandoJava);
                $ssh->setTimeout(0);
                $resultado = $ssh->exec($comandoJava);
                if (!$resultado) {
                    \logApi::general2('generarPDFAfirmado', $aleatorio, 'ERROR:' . $resultado);
                    $txt = '';
                    foreach ($ssh->message_log as $log) {
                        $txt .= $log;
                    }
                    unset($ssh);
                    return false;
                } else {
                    $ssh->exec('chmod 777 ' . $rutaOutPdfFirmado);
                    \logApi::general2('generarPDFAfirmado', $aleatorio, 'Firmado digital del pdf con p12');
                    unset($ssh);
                    return true;
                }
            } else {
                unset($ssh);
                return false;
            }
            unset($ssh);
        } else {
            \logApi::general2('generarPDFAfirmado', $aleatorio, 'No se localizo el archivo pdf');
        }
    }

    function firmarPDFPhp7($aleatorio = '', $rutaInPDF = '', $rutaOutPdfFirmado = '', $usuarioFirmante = '', $lx = '', $ly = '', $rx = '', $ry = '', $nivelCertificacion = 3) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

        $nameLog = 'firmarPDFPhp7_' . date("Ymd");
        if ($aleatorio == '') {
            $aleatorio = $_SESSION["generales"]["codigousuario"];
        }


        switch ($nivelCertificacion) {
            case '0':
                $nivelCertificacionLbl = 'NOT_CERTIFIED';
                break;
            case '1':
                $nivelCertificacionLbl = 'CERTIFIED_NO_CHANGES_ALLOWED';
                break;
            case '2':
                $nivelCertificacionLbl = 'CERTIFIED_FORM_FILLING';
                break;
            case '3':
                $nivelCertificacionLbl = 'CERTIFIED_FORM_FILLING_AND_ANNOTATIONS';
                break;
        }


        if (file_exists($rutaInPDF)) {

            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!defined('PATH_JAVA')) {
                define('PATH_JAVA', '');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                \logApi::general2($nameLog, $aleatorio, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            ini_set('display_errors', '1');

            try {
                $session = ssh2_connect('localhost', 22);
                if (!$session) {
                    \logApi::general2($nameLog, $aleatorio, 'Fallo conexion SSH');
                    return false;
                }
                if (!ssh2_auth_password($session, SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                    unset($session);
                    \logApi::general2($nameLog, $aleatorio, 'Fallo la autenticación SSH');
                    return false;
                }
            } catch (Exception $e) {
                unset($session);
                \logApi::general2($nameLog, $aleatorio, 'No fue posible crear conxion ssh : ' . $e->message());
                return false;
            }

            if (defined($usuarioFirmante)) {
                $regFirma = explode("|", constant($usuarioFirmante));
                $rutaFirma = trim($regFirma[1]);
                $pwdFirma = trim($regFirma[2]);
                $pathJava = str_replace(".", "", PATH_JAVA);
                \logApi::general2($nameLog, $aleatorio, 'Solicita firmado con p12');
                // $comandoJava = $pathJava . "java -Xms128M -Xmx2048M -XX:PermSize=128M -XX:MaxPermSize=2048M -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/firmadoDigital.jar " .
                $comandoJava = $pathJava . "java -Xms128M -Xmx2048M -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/firmadoDigital.jar " .
                        $rutaFirma . " " .
                        "'" . $pwdFirma . "' " .
                        $rutaInPDF . " " .
                        $rutaOutPdfFirmado . " " .
                        $lx . " " .
                        $ly . " " .
                        $rx . " " .
                        $ry . " " .
                        $nivelCertificacion . " ";
                \logApi::general2($nameLog, $aleatorio, 'Comando a ejecutar :' . $comandoJava);
                $ntmp = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $aleatorio . '-' . date("Ymd") . '-' . date("His") . '.fir';
                ssh2_exec($session, $comandoJava . ' > ' . $ntmp);
                $resultado = file_get_contents($ntmp);
                \logApi::general2($nameLog, $aleatorio, $resultado);
                if (file_exists($ntmp) && \funcionesGenerales::tamanoArchivo($ntmp) != 0) {
                    unset($session);
                    \logApi::general2($nameLog, $aleatorio, 'ERROR Firmando');
                    return false;
                } else {
                    \logApi::general2($nameLog, $aleatorio, 'Firmado digital del pdf con p12');
                    return true;
                }
                ssh2_exec($session, 'chmod 777 ' . $rutaOutPdfFirmado);
                unset($session);
            } else {
                logApi::general2($nameLog, $aleatorio, 'No se definio usuario firmante');
                unset($session);
                return false;
            }
        } else {
            \logApi::general2($nameLog, $aleatorio, 'No se localizo el archivo pdf');
            return false;
        }
    }

    function firmarJSignPdf($aleatorio = '', $rutaInPDF = '', $usuarioFirmante = '', $nivelCertificacion = 3, $estampar = 'no') {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if ($aleatorio == '') {
            $aleatorio = $_SESSION["generales"]["codigousuario"];
        }

        switch ($nivelCertificacion) {
            case '0':
                $nivelCertificacionLbl = 'NOT_CERTIFIED';
                break;
            case '1':
                $nivelCertificacionLbl = 'CERTIFIED_NO_CHANGES_ALLOWED';
                break;
            case '2':
                $nivelCertificacionLbl = 'CERTIFIED_FORM_FILLING';
                break;
            case '3':
                $nivelCertificacionLbl = 'CERTIFIED_FORM_FILLING_AND_ANNOTATIONS';
                break;
        }

        $pathEstampado = '';
        if ($estampar == 'si') {
            /*
              $pathEstampado = "-ta PASSWORD " .
              "--tsa-server-url http://190.131.205.170:9233 " .
              "--tsa-policy-oid 1.3.6.1.4.1.23267.60.1 " .
              "--proxy-type DIRECT " .
              "-tsh SHA1 " .
              "-tsu AB20160912321 " .
              "-tsp Certi123*";
             */

            $pathEstampado = "-ta PASSWORD " .
                    "--tsa-server-url " . TSA_URL . " " .
                    "--tsa-policy-oid " . TSA_OID . " " .
                    "--proxy-type DIRECT " .
                    "-tsh SHA1 " .
                    "-tsu " . TSA_USER . " " .
                    "-tsp " . TSA_PASSWORD;
        }


        if (file_exists($rutaInPDF)) {

            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!defined('PATH_JAVA')) {
                define('PATH_JAVA', '');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                log::general2('generarPDFAfirmado', $aleatorio, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION["generales"]["pathabsoluto"] . '/includes/phpseclib');
            require_once ('Net/SSH2.php');
            ini_set('display_errors', '1');

            $ssh = new Net_SSH2('localhost');

            if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                \logApi::general2('generarPDFAfirmado', $aleatorio, 'Fallo la autenticación SSH');
                return false;
            }

            if (defined($usuarioFirmante)) {

                $regFirma = explode("|", constant($usuarioFirmante));
                //$usuarioSII = trim($regFirma[0]);
                $rutaFirma = trim($regFirma[1]);
                $pwdFirma = trim($regFirma[2]);

                $pathJava = str_replace(".", "", PATH_JAVA);

                \logApi::general2('generarPDFAfirmado', $aleatorio, 'Solicita firmado con p12 (JSignPdf) - Estampar=' . $estampar);

                try {

                    $comandoJava = $pathJava . "java -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/JSignPdf/JSignPdf.jar " .
                            $rutaInPDF . " " .
                            "--out-directory " . $_SESSION["generales"]["pathabsoluto"] . "/tmp " .
                            "-os _firmado " .
                            "-kst PKCS12 " .
                            "-ksf " . $rutaFirma . " " .
                            "-ksp " . $pwdFirma . " " .
                            "-r SII-JSignPdf " .
                            "-l CO " .
                            "--certification-level " . $nivelCertificacionLbl . " " .
                            " -ha SHA512 " . $pathEstampado;

                    //log::general2('generarPDFAfirmado', $aleatorio, 'Comando Java:' . $comandoJava);

                    $ssh->setTimeout(0);
                    $resultado = $ssh->exec($comandoJava);
                } catch (Exception $exc) {
                    \logApi::general2('generarPDFAfirmado', $aleatorio, 'Exception :' . $exc->getTraceAsString());
                    return false;
                }

                $encuentraFinal = strpos($resultado, 'Finished: Signature succesfully created.');
                if ($encuentraFinal !== false) {
                    \logApi::general2('generarPDFAfirmado', $aleatorio, 'Firmado digital del pdf con p12 (JSignpdf)');
                } else {
                    \logApi::general2('generarPDFAfirmado', $aleatorio, 'ERROR:' . $resultado);
                    $txt = '';
                    foreach ($ssh->message_log as $log) {
                        $txt .= $log;
                    }
                    return false;
                }
                //  $ssh->exec('chmod 777 ' . $rutaOutPdfFirmado);
            } else {
                return false;
            }
            unset($ssh);
        } else {
            \logApi::general2('generarPDFAfirmado', $aleatorio, 'No se localizo el archivo pdf');
            return false;
        }
    }

    function firmarCertitoken($aleatorio = '', $rutaInPDF = '', $rutaOutPdfFirmado = '', $usuarioFirmante = '', $lx = '', $ly = '', $rx = '', $ry = '', $nivelCertificacion = 3) {

        $nameLog = 'firmarCertitokenNew_' . date("Ymd");

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        
        if (!defined('SUB_CERTICAMARA') || SUB_CERTICAMARA == '') {
            $subcerticamara = "AC SUB CERTICAMARA";            
        } else {
            switch (SUB_CERTICAMARA) {
                case '2048' : $subcerticamara = "AC SUB CERTICAMARA"; break;
                case '4096' : $subcerticamara = "AC SUB 4096 CERTICAMARA"; break;
            }
        }

        if ($aleatorio == '') {
            $aleatorio = $_SESSION["generales"]["codigousuario"];
        }

        switch ($nivelCertificacion) {
            case '0':
                $nivelCertificacionLbl = 'NOT_CERTIFIED';
                break;
            case '1':
                $nivelCertificacionLbl = 'CERTIFIED_NO_CHANGES_ALLOWED';
                break;
            case '2':
                $nivelCertificacionLbl = 'CERTIFIED_FORM_FILLING';
                break;
            case '3':
                $nivelCertificacionLbl = 'CERTIFIED_FORM_FILLING_AND_ANNOTATIONS';
                break;
        }


        if (file_exists($rutaInPDF)) {

            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!defined('PATH_JAVA')) {
                define('PATH_JAVA', '');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                \logApi::general2($nameLog, $aleatorio, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            try {
                $session = ssh2_connect('localhost', 22);
                if (!$session) {
                    \logApi::general2($nameLog, $aleatorio, 'Fallo conexion SSH');
                    return false;
                }
                if (!ssh2_auth_password($session, SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                    unset($session);
                    \logApi::general2($nameLog, $aleatorio, 'Fallo la autenticación SSH');
                    return false;
                }
            } catch (Exception $e) {
                unset($session);
                \logApi::general2($nameLog, $aleatorio, 'No fue posible crear conxion ssh : ' . $e->message());
                return false;
            }

            if (defined($usuarioFirmante)) {

                if ($usuarioFirmante == 'CERTITOKEN_' . $_SESSION["generales"]["codigoempresa"] . '_0') {

                    \logApi::general2($nameLog, $aleatorio, 'Solicita firmado con token virtual');

                    try {

                        $regFirmaToken = explode("|", constant($usuarioFirmante));
                        $serialCert = trim($regFirmaToken[1]);
                        $usrToken = trim($regFirmaToken[2]);
                        $pwdToken = trim($regFirmaToken[3]);

                        $apiPath = $_SESSION["generales"]["pathabsoluto"] . "/components/certitoken/api/wrapperSign4J/WrapperSign4J.jar";
                        $signType = "PADES";
                        $xmlConfigPath = $_SESSION["generales"]["pathabsoluto"] . "/components/certitoken/config/properties.xml";
                        $signReason = "SII-TokenVirtual";
                        $signLocation = "CO";
                        $isCertiToken = "true";
                        $issuerCert = "CN=" . $subcerticamara . ", O=CERTICAMARA S.A, OU=NIT 830084433-7, C=CO, ST=DISTRITO CAPITAL, L=BOGOTA, STREET=www.certicamara.com";
                        $stamp = "false";
                        $stampType = "";
                        $stampP12Path = "";
                        $stampP12Password = "";
                        $ltv = "false";
                        $pdfSignTypeConstants = "CERTIFIED_FORM_FILLING_AND_ANNOTATIONS";
                        $signImageAttrs = "";
                        $policyXmlEpes = "null";

                        $pathJava = str_replace(".", "", PATH_JAVA);

                        /**
                         * 2017-09-15 WSIERRA ::: Se retiran los parámetros definidos de memoria para ejecución de JVM los cuales 
                         * garantizaron eficiencia en servidores con una sola cámara de comercio con certitoken 
                         */
                        //$comandoJava = $pathJava . "\"java\" -Xms128M -Xmx2048M -XX:PermSize=128M -XX:MaxPermSize=2048M -jar "
                        $comandoJava = $pathJava . "\"java\" -jar "
                                . "\"$apiPath\" "
                                . "\"$signType\" "
                                . "\"$xmlConfigPath\" "
                                . "\"$rutaInPDF\" "
                                . "\"$rutaOutPdfFirmado\" "
                                . "\"$usrToken\" "
                                . "\"$pwdToken\" "
                                . "\"$stamp\" "
                                . "\"$stampP12Path\" "
                                . "\"$stampP12Password\" "
                                . "\"$signReason\" "
                                . "\"$signLocation\" "
                                . "\"$signImageAttrs\" "
                                . "\"$ltv\" "
                                . "\"$policyXmlEpes\" "
                                . "\"$stampType\" "
                                . "\"$isCertiToken\" "
                                . "\"$serialCert\" "
                                . "\"$issuerCert\" "
                                . "\"$pdfSignTypeConstants\"";

                        \logApi::general2($nameLog, $aleatorio, $comandoJava);
                        $ntmp = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $aleatorio . '-' . date("Ymd") . '-' . date("His") . '.ctk';
                        ssh2_exec($session, $comandoJava . ' > ' . $ntmp);
                        $resultado = file_get_contents($ntmp);
                        \logApi::general2($nameLog, $aleatorio, 'Resultado del firmado : ' . $resultado);
                        if (!file_exists($rutaOutPdfFirmado)) {
                            unset($session);
                            \logApi::general2($nameLog, $aleatorio, 'ERROR Firmando');
                            return false;
                        } else {
                            ssh2_exec($session, 'chmod 777 ' . $rutaOutPdfFirmado);
                            \logApi::general2($nameLog, $aleatorio, 'Firmado digital del pdf con certitoken');
                            unset($session);
                            return true;
                        }
                        unset($session);
                    } catch (Exception $exc) {
                        \logApi::general2($nameLog, $aleatorio, 'Exception :' . $exc->getTraceAsString());
                        return false;
                    }
                }
            } else {
                return false;
            }
        } else {
            \logApi::general2($nameLog, $aleatorio, 'No se localizo el archivo pdf');
            return false;
        }
    }

    function generarPDFA($aleatorio = '', $rutaInPDF = '', $rutaOutPDFA = '', $tipoPDFA = 'PDF_A_1B', $tamanioPagina = 'LETTER') {
        if (substr(PHP_VERSION, 0, 1) == '7' || substr(PHP_VERSION, 0, 1) == '8' || substr(PHP_VERSION, 0, 1) == '9') {
            return $this->generarPDFAPhp7($aleatorio, $rutaInPDF, $rutaOutPDFA, $tipoPDFA, $tamanioPagina);
        }
        $nameLog = 'generarPDFA_' . date("Ymd");

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if (file_exists($rutaInPDF)) {

            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!defined('PATH_JAVA')) {
                define('PATH_JAVA', '');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                \logApi::general2($nameLog, $aleatorio, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION["generales"]["pathabsoluto"] . '/components/phpseclib');
            require_once ('Net/SSH2.php');
            ini_set('display_errors', '1');

            $ssh = new Net_SSH2('localhost');
            if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                \logApi::general2($nameLog, $aleatorio, 'Fallo la autenticación SSH');
                return false;
            }

            $pathJava = str_replace(".", "", PATH_JAVA);

            $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/conversorPDFA.jar " .
                    $rutaInPDF . " " .
                    $rutaOutPDFA . " " .
                    $tipoPDFA . " " .
                    $tamanioPagina;

            $ssh->setTimeout(0);
            $resultado = $ssh->exec($comandoJava);

            if (!$resultado) {
                $txt = '';
                foreach ($ssh->message_log as $log) {
                    $txt .= $log;
                }
                return false;
            }
            $ssh->exec('chmod 777 ' . $rutaOutPDFA);
            unset($ssh);
            return true;
        } else {
            \logApi::general2($nameLog, $aleatorio, 'No se localizo el archivo pdf');
            return false;
        }
    }

    function generarPDFAPhp7($aleatorio = '', $rutaInPDF = '', $rutaOutPDFA = '', $tipoPDFA = 'PDF_A_1B', $tamanioPagina = 'LETTER') {
        $nameLog = 'generarPDFAPhp7_' . date("Ymd");
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        if (file_exists($rutaInPDF)) {

            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!defined('PATH_JAVA')) {
                define('PATH_JAVA', '');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                \logApi::general2($nameLog, $aleatorio, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            ini_set('display_errors', '1');

            $session = ssh2_connect('localhost', 22);
            if (!$session) {
                \logApi::general2($nameLog, $aleatorio, 'Fallo conexion SSH');
                return false;
            }
            if (!ssh2_auth_password($session, SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                unset($session);
                \logApi::general2($nameLog, $aleatorio, 'Fallo la autenticación SSH');
                return false;
            }

            $pathJava = str_replace(".", "", PATH_JAVA);

            $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/conversorPDFA.jar " .
                    $rutaInPDF . " " .
                    $rutaOutPDFA . " " .
                    $tipoPDFA . " " .
                    $tamanioPagina;

            \logApi::general2($nameLog, $aleatorio, $comandoJava);

            $ntmp = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-' . date("Ymd") . '-' . date("His") . '.fir';
            ssh2_exec($session, $comandoJava . ' > ' . $ntmp);
            $resultado = file_get_contents($ntmp);
            \logApi::general2($nameLog, $aleatorio, $resultado);

            //
            $encuentraBin = strpos($resultado, 'Finaliza el firmado del Sobre');

            if ($encuentraBin !== false) {
                ssh2_exec($session, 'chmod 777 ' . $rutaOutPDFA);
                unset($session);
                return true;
            } else {
                unset($session);
                return false;
            }
        } else {
            \logApi::general2($nameLog, $aleatorio, 'No se localizo el archivo pdf');
        }
    }

    function generarSobreFirmado($hashFirmado, $firmantes = array(), $encabezadoCamara = array(), $detalleTramite = array(), $archivosAdjuntos = array(), $rutaOutPDFA = '', $sobreLink = 'no') {

        if (substr(PHP_VERSION, 0, 1) == '7' || substr(PHP_VERSION, 0, 1) == '8' || substr(PHP_VERSION, 0, 1) == '9') {
            return $this->generarSobreFirmadoPhp7($hashFirmado, $firmantes, $encabezadoCamara, $detalleTramite, $archivosAdjuntos, $rutaOutPDFA, $sobreLink);
        }

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        // require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/EncodingNew.php');

        $firmante = \funcionesGenerales::utf8_decode($detalleTramite['nomcliente'] . ' - ' . $detalleTramite['nomfirmante']);

        if (!defined('PATH_COMMON_BASE')) {
            define('PATH_COMMON_BASE', '/opt');
        }

        if (!defined('PATH_JAVA')) {
            define('PATH_JAVA', '');
        }

        if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
            \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'No se localizo el archivo de configuración');
            return false;
        }

        require_once (PATH_COMMON_BASE . '/commonBase.php');
        set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION["generales"]["pathabsoluto"] . '/includes/phpseclib');
        require_once ('Net/SSH2.php');
        ini_set('display_errors', '1');

        $ssh = new Net_SSH2('localhost');

        if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
            \logApi::general2('generarPDFAfirmado', '', 'Fallo la autenticación SSH');
            return false;
        }

        //UTILIZA FIRMA DE PERSONA JURIDICA
        $usuarioFirmante = 'FIRMA_' . $_SESSION["generales"]["codigoempresa"] . '_PJ';

        if (defined($usuarioFirmante)) {

            $regFirma = explode("|", constant($usuarioFirmante));

            //$usuarioSII = trim($regFirma[0]);
            $rutaFirma = trim($regFirma[1]);
            $pwdFirma = trim($regFirma[2]);

            $parametros = "";
            $parametros .= "'" . $encabezadoCamara['logo'] . "' ";
            $parametros .= "'" . $encabezadoCamara['nombre'] . "' ";
            $parametros .= "'" . $encabezadoCamara['direccion'] . "' ";
            $parametros .= "'" . $encabezadoCamara['telefono'] . "' ";
            $parametros .= "'" . $encabezadoCamara['ciudad'] . "' ";
            $parametros .= "'" . $detalleTramite['idliquidacion'] . " / " . $detalleTramite['numerorecuperacion'] . "' "; //Aplica como CODIGO_BARRAS EN JAR
            $parametros .= "'" . $detalleTramite['fechahora'] . "' ";
            $parametros .= "'" . $detalleTramite['idecliente'] . " - " . str_replace("'", "", $detalleTramite['nomcliente']) . "' ";
            $parametros .= "'" . $detalleTramite['idefirmante'] . " - " . str_replace("'", "", $detalleTramite['nomfirmante']) . "' ";
            $parametros .= "'" . $detalleTramite['tipotramite'] . "' ";
            $parametros .= "'" . $detalleTramite['numfolios'] . "' ";
            $parametros .= "'" . $detalleTramite['dependencia'] . "' ";
            $parametros .= "'" . $detalleTramite['seriesubserie'] . "' ";

            //anexar un log
            //INICIA TEXTO FIRMANTES
            $parametros .= "'";
            foreach ($firmantes as $txtFirmante) {
                $parametros .= $txtFirmante . "|";
            }
            $parametros .= "' ";

            //PARAMETRO HASH (VIENE NA SI NO SE DEFINE)
            $parametros .= "'" . $hashFirmado . "' ";

            //PARAMETRO RUTA PDF/A
            $parametros .= "'" . $rutaOutPDFA . "' ";

            //PARAMETROS DE CERTIFICADO DIGITAL
            $parametros .= "'" . $rutaFirma . "' ";
            $parametros .= "'" . $pwdFirma . "' ";

            $pathJava = str_replace(".", "", PATH_JAVA);

            //PARAMETROS DE ARCHIVOS ADJUNTOS
            if ($sobreLink == 'no') {

                foreach ($archivosAdjuntos as $ruta) {
                    $parametros .= "'" . $ruta . "' ";
                }
                $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/armarSobre.jar " . $parametros;
                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], $comandoJava);
                $ssh->setTimeout(0);
                $resultado = $ssh->exec($comandoJava);
            } else {

                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'Conjunto de soportes sobrepasa 300MB');

                //LLENAR TXT CON PARAMETROS DE SOPORTES
                $rutaTxt = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-SoportesSobreDigital-' . $detalleTramite['idliquidacion'] . '-' . date('YmdHis') . '.txt';
                $f1 = fopen($rutaTxt, "a");

                foreach ($archivosAdjuntos as $ruta) {

                    $ruta = str_replace("Ñ", "N", $ruta);
                    fwrite($f1, $ruta . "*");
                }
                fclose($f1);

                //ASIGNAR RUTA A PARAMETROS DEL JAR
                $parametros .= "'" . $rutaTxt . "' ";
                $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/armarSobreV2.jar " . $parametros;
                $ssh->setTimeout(0);
                $resultado = $ssh->exec($comandoJava);
                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], $comandoJava);
            }

            $encuentraBin = strpos($resultado, 'bin');
            if ($encuentraBin !== false) {
                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], $resultado);
            }

            //
            $encuentraError = strpos($resultado, 'Exception');
            if ($encuentraError !== false) {
                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'RETORNO JAR:' . strstr($resultado, 'Exception'));
            }

            //
            if (!$resultado) {
                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'ERROR:' . $resultado);

                $txt = '';
                foreach ($ssh->message_log as $log) {
                    $txt .= $log;
                }
                return false;
            } else {
                $encuentraError = strpos($resultado, 'Exception');
                if ($encuentraError === false) {
                    \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], $firmante);
                    return $resultado;
                } else {
                    \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'EXCEPCION: ', $resultado);
                    return false;
                }
            }
            $resultado = $ssh->exec('chmod 777 ' . $rutaOutPDFA);
        } else {
            return false;
        }
        unset($ssh);
    }

    function generarSobreFirmadoPhp7($hashFirmado, $firmantes = array(), $encabezadoCamara = array(), $detalleTramite = array(), $archivosAdjuntos = array(), $rutaOutPDFA = '', $sobreLink = 'no') {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $firmante = \funcionesGenerales::utf8_decode($detalleTramite['nomcliente'] . ' - ' . $detalleTramite['nomfirmante']);

        if (!defined('PATH_COMMON_BASE')) {
            define('PATH_COMMON_BASE', '/opt');
        }

        if (!defined('PATH_JAVA')) {
            define('PATH_JAVA', '');
        }

        if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
            \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'No se localizo el archivo de configuración');
            return false;
        }

        require_once (PATH_COMMON_BASE . '/commonBase.php');

        ini_set('display_errors', '1');

        $session = ssh2_connect('localhost', 22);
        if (!$session) {
            \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'Fallo conexion SSH');
            return false;
        }
        if (!ssh2_auth_password($session, SII_USER_ROOT, SII_PASSWORD_ROOT)) {
            \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'Fallo la autenticación SSH');
            return false;
        }

        //UTILIZA FIRMA DE PERSONA JURIDICA
        $usuarioFirmante = 'FIRMA_' . $_SESSION["generales"]["codigoempresa"] . '_PJ';

        if (defined($usuarioFirmante)) {

            $regFirma = explode("|", constant($usuarioFirmante));

            //$usuarioSII = trim($regFirma[0]);
            $rutaFirma = trim($regFirma[1]);
            $pwdFirma = trim($regFirma[2]);

            $parametros = "";
            $parametros .= "'" . $encabezadoCamara['logo'] . "' ";
            $parametros .= "'" . $encabezadoCamara['nombre'] . "' ";
            $parametros .= "'" . $encabezadoCamara['direccion'] . "' ";
            $parametros .= "'" . $encabezadoCamara['telefono'] . "' ";
            $parametros .= "'" . $encabezadoCamara['ciudad'] . "' ";
            $parametros .= "'" . $detalleTramite['idliquidacion'] . " / " . $detalleTramite['numerorecuperacion'] . "' "; //Aplica como CODIGO_BARRAS EN JAR
            $parametros .= "'" . $detalleTramite['fechahora'] . "' ";
            $parametros .= "'" . $detalleTramite['idecliente'] . " - " . str_replace("'", "", $detalleTramite['nomcliente']) . "' ";
            $parametros .= "'" . $detalleTramite['idefirmante'] . " - " . str_replace("'", "", $detalleTramite['nomfirmante']) . "' ";
            $parametros .= "'" . $detalleTramite['tipotramite'] . "' ";
            $parametros .= "'" . $detalleTramite['numfolios'] . "' ";
            $parametros .= "'" . $detalleTramite['dependencia'] . "' ";
            $parametros .= "'" . $detalleTramite['seriesubserie'] . "' ";

            //anexar un log
            //INICIA TEXTO FIRMANTES
            $parametros .= "'";
            foreach ($firmantes as $txtFirmante) {
                $parametros .= $txtFirmante . "|";
            }
            $parametros .= "' ";

            //PARAMETRO HASH (VIENE NA SI NO SE DEFINE)
            $parametros .= "'" . $hashFirmado . "' ";

            //PARAMETRO RUTA PDF/A
            $parametros .= "'" . $rutaOutPDFA . "' ";

            //PARAMETROS DE CERTIFICADO DIGITAL
            $parametros .= "'" . $rutaFirma . "' ";
            $parametros .= "'" . $pwdFirma . "' ";

            $pathJava = str_replace(".", "", PATH_JAVA);

            //PARAMETROS DE ARCHIVOS ADJUNTOS
            if ($sobreLink == 'no') {
                foreach ($archivosAdjuntos as $ruta) {
                    $parametros .= "'" . $ruta . "' ";
                }
                
                $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/armarSobre.jar " . $parametros;
                $aleatjava = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . \funcionesGenerales::generarAleatorioAlfanumerico10() . '.sh';
                $f = fopen ($aleatjava,"w");
                fwrite ($f,$comandoJava);
                fclose($f);
                chmod($aleatjava, 777); 
                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], '$sobreLink=no - comando java : ' . $comandoJava);
                // ssh2_exec($session, $comandoJava . ' > ' . PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-' . $detalleTramite['idliquidacion'] . '.fir');
                ssh2_exec($session, $aleatjava . ' > ' . PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-' . $detalleTramite['idliquidacion'] . '.fir');                
            } else {
                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], 'Conjunto de soportes sobrepasa 300MB');
                $rutaTxt = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-SoportesSobreDigital-' . $detalleTramite['idliquidacion'] . '-' . date('YmdHis') . '.txt';
                $f1 = fopen($rutaTxt, "a");
                foreach ($archivosAdjuntos as $ruta) {
                    $ruta = str_replace("Ñ", "N", $ruta);
                    fwrite($f1, $ruta . "*");
                }
                fclose($f1);
                $parametros .= "'" . $rutaTxt . "' ";
                $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/armarSobreV2.jar " . $parametros;
                \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], '$sobreLink=si - comando java : ' . $comandoJava);
                ssh2_exec($session, $comandoJava . ' > ' . PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-' . $detalleTramite['idliquidacion'] . '.fir');
            }

            //
            $resultado = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-' . $detalleTramite['idliquidacion'] . '.fir');
            \logApi::general2(__FUNCTION__, $detalleTramite['idliquidacion'], $resultado);

            //
            $encuentraBin = strpos($resultado, 'Finaliza el firmado del Sobre');
            if ($encuentraBin !== false) {
                ssh2_exec($session, 'chmod 777 ' . $rutaOutPDFA);
                unset($session);
                return true;
            } else {
                unset($session);
                return false;
            }
        } else {
            unset($session);
            return false;
        }
    }

    function generarSobreSinFirma($encabezadoCamara = array(), $detalleTramite = '', $archivosAdjuntos = array(), $rutaOutPDFA = '') {

        if (substr(PHP_VERSION, 0, 1) == '7' || substr(PHP_VERSION, 0, 1) == '8' || substr(PHP_VERSION, 0, 1) == '9') {
            return $this->generarSobreSinFirmaPhp7($encabezadoCamara, $detalleTramite, $archivosAdjuntos, $rutaOutPDFA);
        }
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/EncodingNew.php');

        if (!defined('PATH_COMMON_BASE')) {
            define('PATH_COMMON_BASE', '/opt');
        }

        if (!defined('PATH_JAVA')) {
            define('PATH_JAVA', '');
        }

        if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
            \logApi::general2(__FUNCTION__, $rutaOutPDFA, 'No se localizo el archivo de configuración');
            return false;
        }

        require_once (PATH_COMMON_BASE . '/commonBase.php');

        set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION["generales"]["pathabsoluto"] . '/includes/phpseclib');
        require_once ('Net/SSH2.php');
        ini_set('display_errors', '1');

        $ssh = new Net_SSH2('localhost');
        if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
            \logApi::general2(__FUNCTION__, $rutaOutPDFA, 'Fallo la autenticación SSH');
            return false;
        }

        $parametros = "";
        $parametros .= "'" . $encabezadoCamara['logo'] . "' ";
        $parametros .= "'" . $encabezadoCamara['nombre'] . "' ";
        $parametros .= "'" . $encabezadoCamara['direccion'] . "' ";
        $parametros .= "'" . $encabezadoCamara['telefono'] . "' ";
        $parametros .= "'" . $encabezadoCamara['ciudad'] . "' ";

        //PARAMETRO DETALLE DEL SOBRE
        $detalleSobre = \funcionesGenerales::utf8_decode($detalleTramite);
        $parametros .= "'" . $detalleSobre . "' ";

        //PARAMETRO RUTA PDF/A
        $parametros .= "'" . $rutaOutPDFA . "' ";

        $pathJava = str_replace(".", "", PATH_JAVA);

        //PARAMETROS DE ARCHIVOS ADJUNTOS
        foreach ($archivosAdjuntos as $ruta) {
            $parametros .= "'" . $ruta . "' ";
        }
        $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/armarSobreV3.jar " . $parametros;
        $ssh->setTimeout(0);
        $resultado = $ssh->exec($comandoJava);

        \logApi::general2(__FUNCTION__, $rutaOutPDFA, $comandoJava);

        $encuentraBin = strpos($resultado, 'bin');
        if ($encuentraBin !== false) {
            \logApi::general2(__FUNCTION__, $rutaOutPDFA, $resultado);
        }

        $encuentraError = strpos($resultado, 'Exception');
        if ($encuentraError !== false) {
            \logApi::general2(__FUNCTION__, $rutaOutPDFA, 'RETORNO JAR:' . strstr($resultado, 'Exception'));
        }

        if (!$resultado) {
            \logApi::general2(__FUNCTION__, $rutaOutPDFA, 'ERROR:' . $resultado);

            $txt = '';
            foreach ($ssh->message_log as $log) {
                $txt .= $log;
            }
            return false;
        } else {
            $encuentraError = strpos($resultado, 'Exception');
            if ($encuentraError === false) {
                \logApi::general2(__FUNCTION__, $rutaOutPDFA, 'OK');
                return $resultado;
            } else {
                \logApi::general2(__FUNCTION__, $rutaOutPDFA, 'EXCEPCION: ', $resultado);
                return false;
            }
        }

        $ssh->exec('chmod 777 ' . $rutaOutPDFA);

        unset($ssh);
    }

    function generarSobreSinFirmaPhp7($encabezadoCamara = array(), $detalleTramite = '', $archivosAdjuntos = array(), $rutaOutPDFA = '') {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $nameLog = 'generarSobreSinFirmaPhp7_' . date("Ymd");

        if (!defined('PATH_COMMON_BASE')) {
            define('PATH_COMMON_BASE', '/opt');
        }

        if (!defined('PATH_JAVA')) {
            define('PATH_JAVA', '');
        }

        if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
            \logApi::general2(__FUNCTION__, $rutaOutPDFA, 'No se localizo el archivo de configuración');
            return false;
        }

        require_once (PATH_COMMON_BASE . '/commonBase.php');

        $session = ssh2_connect('localhost', 22);
        if (!$session) {
            \logApi::general2($nameLog, '', 'Fallo conexion SSH');
            return false;
        }
        if (!ssh2_auth_password($session, SII_USER_ROOT, SII_PASSWORD_ROOT)) {
            unset($session);
            \logApi::general2($nameLog, '', 'Fallo la autenticación SSH');
            return false;
        }

        ini_set('display_errors', '1');

        $parametros = "";
        $parametros .= "'" . $encabezadoCamara['logo'] . "' ";
        $parametros .= "'" . $encabezadoCamara['nombre'] . "' ";
        $parametros .= "'" . $encabezadoCamara['direccion'] . "' ";
        $parametros .= "'" . $encabezadoCamara['telefono'] . "' ";
        $parametros .= "'" . $encabezadoCamara['ciudad'] . "' ";

        //PARAMETRO DETALLE DEL SOBRE
        $detalleSobre = \funcionesGenerales::utf8_decode($detalleTramite);
        $parametros .= "'" . $detalleSobre . "' ";

        //PARAMETRO RUTA PDF/A
        $parametros .= "'" . $rutaOutPDFA . "' ";

        $pathJava = str_replace(".", "", PATH_JAVA);

        //PARAMETROS DE ARCHIVOS ADJUNTOS
        foreach ($archivosAdjuntos as $ruta) {
            $parametros .= "'" . $ruta . "' ";
        }
        $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/armarSobreV3.jar " . $parametros;

        \logApi::general2($nameLog, '', $comandoJava);

        $ntmp = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION ["generales"] ["codigoempresa"] . '-' . date("Ymd") . '-' . date("His") . '.fir';
        ssh2_exec($session, $comandoJava . ' > ' . $ntmp);
        $resultado = file_get_contents($ntmp);
        \logApi::general2($nameLog, '', 'Resultado del java : ' . $resultado);
        sleep(5);
        if (file_exists($rutaOutPDFA)){
            \logApi::general2($nameLog, '', 'Encontro sobre generado en ' . $rutaOutPDFA);
            ssh2_exec($session, 'chmod 777 ' . $rutaOutPDFA);
            unset($session);
            return true;
        } else {
            \logApi::general2($nameLog, '', 'No encontro sobre generado en ' . $rutaOutPDFA);
            unset($session);
            return false;
        }
        
        /*
        $encuentraBin = strpos($resultado, 'Finaliza el firmado del Sobre');
        if ($encuentraBin !== false) {
            ssh2_exec($session, 'chmod 777 ' . $rutaOutPDFA);
            unset($session);
            return true;
        } else {
            unset($session);
            return false;
        }
        */
        
    }

    function extraerListadoAdjuntos($rutaInPDFA = '') {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/EncodingNew.php');

        //
        if (file_exists($rutaInPDFA)) {


            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!defined('PATH_JAVA')) {
                define('PATH_JAVA', '');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                \logApi::general2(__FUNCTION__, $rutaInPDFA, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION["generales"]["pathabsoluto"] . '/includes/phpseclib');
            require_once ('Net/SSH2.php');
            ini_set('display_errors', '1');

            $ssh = new Net_SSH2('localhost');
            if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                \logApi::general2(__FUNCTION__, $rutaInPDFA, 'Fallo la autenticación SSH');
                return false;
            }

            $parametros = "";
            $parametros .= "'" . $rutaInPDFA . "'";

            $pathJava = str_replace(".", "", PATH_JAVA);

            $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/extraerAdjuntos.jar -l " . $parametros;
            $resultado = $ssh->exec($comandoJava);

            unset($parametros);

            if (!$resultado) {

                \logApi::general2(__FUNCTION__, $rutaInPDFA, 'ERROR:' . $resultado);

                $txt = '';
                foreach ($ssh->message_log as $log) {
                    $txt .= $log;
                }
                return false;
            } else {
                $encuentraError = strpos($resultado, 'Exception');
                if ($encuentraError === false) {
                    \logApi::general2(__FUNCTION__, $rutaInPDFA, 'OK');
                    return $resultado;
                } else {
                    \logApi::general2(__FUNCTION__, $rutaInPDFA, 'EXCEPCION: ', $resultado);
                    return false;
                }
            }
            unset($ssh);
        } else {
            \logApi::general2(__FUNCTION__, $rutaInPDFA, 'No se localizo el archivo pdf');
        }
    }

    function extraerArchivoAdjunto($rutaInPDFA = '', $directorioOut = '', $nombreAdjunto = '') {


        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/EncodingNew.php');

        //
        if (file_exists($rutaInPDFA)) {

            if (!defined('PATH_COMMON_BASE')) {
                define('PATH_COMMON_BASE', '/opt');
            }

            if (!defined('PATH_JAVA')) {
                define('PATH_JAVA', '');
            }

            if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
                \logApi::general2(__FUNCTION__, $rutaInPDFA, 'No se localizo el archivo de configuración');
                return false;
            }

            require_once (PATH_COMMON_BASE . '/commonBase.php');

            set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION["generales"]["pathabsoluto"] . '/components/phpseclib');
            require_once ('Net/SSH2.php');
            ini_set('display_errors', '1');

            $ssh = new Net_SSH2('localhost');
            if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
                \logApi::general2(__FUNCTION__, $rutaInPDFA, 'Fallo la autenticación SSH');
                return false;
            }

            $parametros = "";
            $parametros .= "'" . $rutaInPDFA . "' ";
            $parametros .= "'" . $directorioOut . "' ";
            $parametros .= "'" . $nombreAdjunto . "' ";

            $pathJava = str_replace(".", "", PATH_JAVA);

            $comandoJava = $pathJava . "java -Xms128m -Xmx2048m -jar " . $_SESSION["generales"]["pathabsoluto"] . "/components/pdfa_dig/extraerAdjuntos.jar -e " . $parametros;
            $resultado = $ssh->exec($comandoJava);

            unset($parametros);

            if (!$resultado) {

                \logApi::general2(__FUNCTION__, $rutaInPDFA, 'ERROR:' . $resultado);

                $txt = '';
                foreach ($ssh->message_log as $log) {
                    $txt .= $log;
                }
                return false;
            } else {
                $encuentraError = strpos($resultado, 'Exception');
                if ($encuentraError === false) {
                    \logApi::general2(__FUNCTION__, $rutaInPDFA, 'OK');
                    return $resultado;
                } else {
                    \logApi::general2(__FUNCTION__, $rutaInPDFA, 'EXCEPCION: ', $resultado);
                    return false;
                }
            }


            unset($ssh);
        } else {
            \logApi::general2(__FUNCTION__, $rutaInPDFA, 'No se localizo el archivo pdf');
        }
    }

}

?>