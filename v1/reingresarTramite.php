<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait reingresarTramite {

    public function reingresarTramite(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // 
        $nameLog = "api_reingresarTramite_" . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['codigoestado'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("numerorecuperacion", true);
        $api->validarParametro("ideradicador", true);
        $api->validarParametro("nomradicador", true);
        $api->validarParametro("emailradicador", true);
        $api->validarParametro("celularradicador", true);
        $api->validarParametro("celularradicador", true);
        $api->validarParametro("url", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('reingresarTramite', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);

        //
        if ($liq === false || empty($liq)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada en la BD del Sistema de información';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (strlen($_SESSION["entrada"]["numerorecuperacion"]) == 5) {
            $_SESSION["entrada"]["numerorecuperacion"] = '0' . $_SESSION["entrada"]["numerorecuperacion"];
        }
        
        //
        if (strtoupper($liq["numerorecuperacion"]) != strtoupper($_SESSION["entrada"]["numerorecuperacion"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Número de recuperación reportado no está asociado con la liquidación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($liq["numeroradicacion"] == '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación sin código de barras - radicado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $cb = \funcionesRegistrales::retornarCodigoBarras($mysqli, $liq["numeroradicacion"]);

        //
        if ($cb === false || empty($cb)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Código de barras no localizado en la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($cb["estado"] != '05' && $cb["estado"] != '06' && $cb["estado"] != '07') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Código de barras se encuentra en un estado que no permite su reingreso.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
    
        // ************************************************************************************* //
        // Recupera el documento que se debe cargar como anexo al trámite
        // ************************************************************************************* //
        $file = file_get_contents($_SESSION["entrada"]["url"]);
        if ($file === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible recuperar el pdf con el documento.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);            
        }
        $nameFile = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $liq["numeroradicacion"] . '-' . date ("Ymd") . '-' . date ("His") . '.pdf';
        $f = fopen ($nameFile,"wb");
        fwrite ($f,$file);
        fclose($f);
        
        // ************************************************************************************* //
        // Almacena el dcoumento en el repositorio
        // ************************************************************************************* //
        $id = \funcionesRegistrales::grabarAnexoRadicacion(
                        $mysqli, // Conexion BD
                        $cb["codbarras"], // Código de barras
                        $cb["recibo"], // Número del recibo
                        $cb["operacion"], // Operacion
                        $cb["numid"], // Identificacion
                        $cb["nombre"], // Nombre
                        '', // Acreedor
                        '', // Nombre acreedor
                        $cb["matricula"], // matrícula
                        $cb["proponente"], // proponente
                        '', // Tipo de documento para el sello de mercantil
                        '', // Numero del documento
                        date ("Ymd"),
                        '', // Codigo de origen
                        'EL COMERCIANTE', // origen del documento
                        '', // Clasificacion
                        '', // Numero del contrato
                        '', // Idfuente
                        1, // version
                        '', // Path
                        '1', // Estado
                        date("Ymd"), // fecha de escaneo o generacion
                        'API', // Usuario que genera el registro
                        '', // Caja de archivo
                        '', // Libro de archivo
                        'DOCUMENTO REINGRESADO - SOPORTES', // Observaciones
                        '', // Libro
                        '', // Numero del registro en libros
                        '', // Dupli
                        $cb["bandeja"], // Bandeja de registro
                        'N', // Soporte recibo
                        '', // Identificador
                        '501', // Anexos	
                        '' // Proceso especial
        );

        //
        $dirx = date("Ymd");
        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }
        $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
        copy($nameFile, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
        \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);
        
        
        // ************************************************************************************* //
        // Actualiza estado del código de barras en el SII
        // 2017-06-07 : JINT
        // ************************************************************************************* //
        $arrCampos = array(
            'estadofinal',
            'fechaestadofinal',
            'horaestadofinal',
            'operadorfinal'
        );
        $arrValores = array(
            "'09'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'USUPUBXX'"
        );
        $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', $arrCampos, $arrValores, "codigobarras='" . $liq["numeroradicacion"] . "'");
        if ($res) {
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Actualizo en SII el estado del codigo de barras ' . $liq["numeroradicacion"] . ' a 09.');
        } else {
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Error actualizando en SII el estado del codigo de barras ' . $liq["numeroradicacion"] . ' a 09 : ' . $_SESSION["generales"]["mensajeerror"]);
        }
        $detalle = 'Cambio estado del codigo de barras No. ' . $liq["numeroradicacion"] . ', estado final: 09, Operador: USUPUBXX';
        actualizarLogMysqliApi($mysqli, '069', $_SESSION["generales"]["codigousuario"], 'reingresarTramite.php', '', '', '', $detalle, '', '');

        $arrCampos = array(
            'codigobarras',
            'fecha',
            'hora',
            'estado',
            'operador',
            'impresiones',
            'sucursal'
        );
        $arrValores = array(
            "'" . $liq["numeroradicacion"] . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'09'",
            "'USUPUBXX'",
            "''", // Impresiones
            "''" // Sucursal
        );
        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', $arrCampos, $arrValores);
        if ($res) {
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Actualizo en SII el estado del codigo de barras (mreg_est_codigosbarras_documentos) ' . $liq["numeroradicacion"] . ' a 09.');
        } else {
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Error actualizando en SII el estado del codigo de barras (mreg_est_codigosbarras_documentos) ' . $liq["numeroradicacion"] . ' a 09 : ' . $_SESSION["generales"]["mensajeerror"]);
        }

        // ************************************************************************************* //
        // Actualiza el estado de la liquidacion a reingreado
        // ************************************************************************************* //
        $arrCampos = array('idestado');
        $arrValores = array("'12'");
        $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $liq["idliquidacion"]);
        if ($res) {
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Actualizo mreg_liquidacion a estado 12');
        } else {
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Error actualizando mreg_liquidacion  a estado 12 : ' . $_SESSION["generales"]["mensajeerror"]);
        }

        //
        if ($cb["esembargo"] == 'si') {
            unset($cb["emails"]);
            unset($cb["telefonos"]);
            $cb["emails"][$_SESSION["entrada"]["emailradicador"]] = $_SESSION["entrada"]["emailradicador"];
            $cb["telefonos"][$_SESSION["entrada"]["emailradicador"]] = $_SESSION["entrada"]["emailradicador"];
        }

        // Adiciona a lista de emails y celulares los del firmante
        if (!isset($cb["emails"][$_SESSION["entrada"]["emailradicador"]])) {
            $cb["emails"][$_SESSION["entrada"]["emailradicador"]] = $_SESSION["entrada"]["emailradicador"];
        }
        if (!isset($cb["telefonos"][$_SESSION["entrada"]["celularradicador"]])) {
            $cb["telefonos"][$_SESSION["entrada"]["celularradicador"]] = $_SESSION["entrada"]["celularradicador"];
        }

        // Notificación al EMAIL
        if (empty($cb["emails"])) {
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Reingreso No. ' . $liq["numeroradicacion"] . ' - Mensaje : Sin email para notificar');
        } else {
            $msg = '';
            $msg .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . \funcionesGenerales::mostrarFecha(date("Ymd")) . ' a las ' . \funcionesGenerales::mostrarHora(date("His")) . ' ';
            $msg .= 'fue reingresado en nuestras oficinas una transaccion sujeta a inscripcion en los registros publicos que ';
            $msg .= 'administra y maneja nuestra entidad. Los datos del tramite radicado son los siguientes:<br><br>';
            $msg .= 'Radicado  No. ' . $cb["codbarras"] . '<br>';
            $msg .= 'Recibo  No. ' . $cb["recibo"] . '<br>';
            $msg .= 'Tipo tramite  No. ' . $cb["datliq"]["tipotramite"] . '<br>';
            if (ltrim($cb["datliq"]["idmatriculabase"], "0") != '') {
                $msg .= 'Matricula : ' . $cb["datliq"]["idmatriculabase"] . '<br>';
            }
            if (ltrim($cb["datliq"]["idproponentebase"], "0") != '') {
                $msg .= 'Proponente : ' . $cb["datliq"]["idproponentebase"] . '<br>';
            }
            $msg .= 'Nombre: ' . $_SESSION["entrada"]["nomradicador"] . '<br>';
            $msg .= 'Identificacion: ' . $_SESSION["entrada"]["ideradicador"] . '<br>';


            foreach ($cb["emails"] as $e) {
                $msg .= 'Email ... ' . $e . '<br>';
            }
            $msg .= '<br>';

            if (!defined('NOTIFICAR_TELEFONO')) {
                define('NOTIFICAR_TELEFONO', 'NO');
            }
            if (NOTIFICAR_TELEFONO == 'SI') {
                $msg .= 'Si tiene alguna duda o inquietud con el contenido de esta notificacion, puede comunicarse al ';
                $msg .= 'numero ' . TELEFONO_ATENCION_USUARIOS . ' en la ciudad de ' . retornarNombreMunicipioMysqliApi($mysqli, MUNICIPIO) . ' ';
                $msg .= 'citando el tramite (recibo de caja) No. ' . $cb["recibo"] . '<br><br>';
            }

            //
            $msg .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' ';
            $msg .= 'en cumplimiento a lo contemplado en el Codigo de Procedimiento Administrativo y de lo Contencioso Administrativo.';
            $msg .= '<br><br>';
            $msg .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
            $msg .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
            $msg .= '<br><br>';
            $msg .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';


            //
            $notificados = 0;
            $emails = 0;

            //
            foreach ($cb["emails"] as $e) {

                //
                $emails++;
                \logApi::general2($nameLog, $liq["numeroradicacion"], 'Email No. ' . $emails . ' - ' . $e);

                //
                if (TIPO_AMBIENTE == 'PRUEBAS' || TIPO_AMBIENTE == 'QA') {
                    $emx = EMAIL_NOTIFICACION_PRUEBAS;
                } else {
                    $emx = trim($e);
                }
                $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, 'Notificacion de reingreso del Codigo de Barras No. ' . $cb["codbarras"] . ' en  LA ' . RAZONSOCIAL, $msg, array());

                //
                if ($rEmail) {
                    $notificados++;
                    \logApi::general2($nameLog, $liq["numeroradicacion"], 'Notificando reingreso del codigo de barras: ' . $cb["codbarras"] . ', Email : ' . $e . ' ** OK **');
                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '05', $cb["codbarras"], '', $cb["operacion"], $cb["recibo"], '', '', '', '', $cb["numid"], $cb["matricula"], $cb["proponente"], $cb["nombre"], $e, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '', $cb["bandeja"]);
                } else {
                    \logApi::general2($nameLog, $liq["numeroradicacion"], 'Notificando reingreso del codigo de barras: ' . $cb["codbarras"] . ', Email : ' . $e . ' ** ERRROR : ' . $_SESSION["generales"]["mensajeerror"]);
                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '05', $cb["codbarras"], '', $cb["operacion"], $cb["recibo"], '', '', '', '', $cb["numid"], $cb["matricula"], $cb["proponente"], $cb["nombre"], $e, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', 'ERROR : ' . $_SESSION["generales"]["mensajeerror"], $cb["bandeja"]);
                }
            }
        }

        // Notificacion SMS
        if (empty($cb["telefonos"])) {
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Reingreso No. ' . $db["codbarras"] . ' - Mensaje : Sin telefonos para notificar');
        } else {
            $txtSms = 'La ' . RAZONSOCIALSMS . ' le informa que el ' . \funcionesGenerales::mostrarFecha(date("Ymd")) . ' a las ' . \funcionesGenerales::mostrarHora(date("His")) . ' fue reingresado el tramite No. ' . $cb["codbarras"];
            \logApi::general2($nameLog, $liq["numeroradicacion"], 'Reingreso No. ' . $cb["codbarras"] . ' - Mensaje SMS: ' . $txtSms);
            foreach ($cb["telefonos"] as $t) {
                $exp1 = '';
                if (ltrim($cb["datliq"]["idmatriculabase"], "0") != '') {
                    $exp1 = $cb["datliq"]["idmatriculabase"];
                }
                if (ltrim($cb["datliq"]["idproponentebase"], "0") != '') {
                    $exp1 = $cb["datliq"]["idproponentebase"];
                }
                //
                \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '8', $cb["recibo"], $cb["codbarras"], '', '', '', $cb["matricula"], $cb["proponente"], $cb["numid"], $arrCod["nombre"], $txtSms, '', $cb["bandeja"]);
                \logApi::general2($nameLog, $liq["numeroradicacion"], 'Pila sms : Encoló notificación para ' . $t);
            }
        }

        //
        if (defined('EMAIL_NOTIFICACION_REINGRESOS') && EMAIL_NOTIFICACION_REINGRESOS != '') {
            $msg1 = 'Nos permitimos informar que ha sido reingresado el trámite con código de barras: ' . $liq["numeroradicacion"];
            $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, EMAIL_NOTIFICACION_REINGRESOS, 'Notificacion de reingreso', $msg1, array());
        }

        //
        $mysqli->close();
        
        //
        \logApi::general2($nameLog, $liq["numeroradicacion"], 'Reingreso terminado');

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
