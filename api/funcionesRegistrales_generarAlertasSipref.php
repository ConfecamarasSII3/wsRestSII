<?php

class funcionesRegistrales_generarAlertasSipref {

    /**
     * 
     * @param type $dbx
     * @param type $expediente
     * @param type $usuariocontrol
     * @param type $emailusuariocontrol
     * @param type $nombreusuariocontrol
     * @param type $celularusuariocontrol
     * @param type $ipcliente
     * @return boolean
     */
    public static function generarAlertaSiprefConsulta($dbx = null, $expediente = '', $usuariocontrol = '', $emailusuariocontrol = '', $nombreusuariocontrol = '', $celularusuariocontrol = '', $ipcliente = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

        //
        if (defined('GENERAR_ALERTAS_CONSULTAS') && GENERAR_ALERTAS_CONSULTAS == 'NO') {
            return "false:Cámara no tiene activado envio de alertas de consulta";
        }

        //
        if ($usuariocontrol != 'USUPUBXX') {
            return "false:Usuario no es público (no es USUPUBXX)";
        }

        //
        $condicion = "matricula='" . $expediente . "' and fecha='" . date("Ymd") . "' and email='" . $emailusuariocontrol . "'";
        if (contarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $condicion) > 0) {
            return "false:Alerta enviada previamente para esta matrícula y correo del usuario";
        }

        //
        $arrExp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $expediente);
        if ($arrExp === false || empty($arrExp)) {
            return "false:Expediente no localizado en la BD";
        }

        if ($ipcliente == '') {
            $ipcliente = \funcionesGenerales::localizarIP();
        }

        //
        if ($arrExp["emailcom"] == '') {
            return "false:Expediente sin correo electrónico comercial para alertar";
        }

        //
        $emx = $arrExp["emailcom"];
        if (TIPO_AMBIENTE == 'PRUEBAS') {
            if (defined('EMAIL_NOTIFICACION_PRUEBAS') && trim(EMAIL_NOTIFICACION_PRUEBAS) != '') {
                $emx = EMAIL_NOTIFICACION_PRUEBAS;
            } else {
                $emx = 'jint@confecamaras.org.co';
            }
        } else {
            if ($emailusuariocontrol == 'prueba@prueba.prueba') {
                if (defined('EMAIL_NOTIFICACION_PRUEBAS') && trim(EMAIL_NOTIFICACION_PRUEBAS) != '') {
                    $emx = EMAIL_NOTIFICACION_PRUEBAS;
                } else {
                    $emx = 'jint@confecamaras.org.co';
                }
            }
        }

        //
        $mensaje = 'Apreciado usuario<br><br>';
        $mensaje .= 'Nombre: ' . $arrExp["nombre"] . '<br>';
        if (trim($arrExp["identificacion"]) != '') {
            $mensaje .= 'Identificacion: ' . $arrExp["identificacion"] . '<br>';
        }
        $mensaje .= 'Matrícula: ' . $arrExp["matricula"] . '<br><br>';
        $mensaje .= 'Señor comerciante, nos permitimos informarle que el día de hoy a las ' . date("H:i:s") . ' su expediente ha sido consultado por ';
        $mensaje .= $nombreusuariocontrol . ' cuyo correo electrónico es ' . $emailusuariocontrol . ' ';
        $mensaje .= 'desde la IP ' . $ipcliente . '.<br><br>';
        $mensaje .= 'Este email se envía con el objeto de mantenerlo informado.<br><br>';
        $mensaje .= 'Cordialmente,<br>';
        $mensaje .= 'Area de Registros Públicos<br>';
        $mensaje .= RAZONSOCIAL;
        $okemail = false;
        $iemail = 1;
        while ($okemail == false && $iemail <= 5) {
            $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, 'Reporte de consulta a los Registros de la ' . RAZONSOCIAL, $mensaje);
            if ($res) {
                $okemail = true;
            } else {
                $iemail++;
                sleep(3);
            }
        }
        if ($okemail === false) {
            return "false:Error enviando la alerta de consulta";
        }

        //
        $arrCampos = array(
            'idliquidacion',
            'matricula',
            'proponente',
            'fecha',
            'hora',
            'email',
            'celular',
            'usuario',
            'tipotramite',
            'ip',
            'textoalerta',
            'estado'
        );
        $arrValores = array(
            0,
            "'" . $expediente . "'",
            "''",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . addslashes($emailusuariocontrol) . "'",
            "'" . $celularusuariocontrol . "'",
            "'" . $usuariocontrol . "'",
            "'Consulta'",
            "'" . $ipcliente . "'",
            "'" . addslashes($mensaje) . "'",
            "'2'"
        );
        insertarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores);

        //
        return true;
    }

    /**
     * 
     * @param type $dbx
     * @param type $idliquidacion
     * @param type $expediente
     * @param type $tiporegistro
     * @param type $tipotramite
     * @param type $usuariocontrol
     * @param type $emailusuariocontrol
     * @param type $nombreusuariocontrol
     * @param type $celularusuariocontrol
     * @param type $ipcliente
     */
    public static function generarAlertaSiprefTemprana($dbx = null, $idliquidacion = 0, $expediente = '', $tiporegistro = '', $tipotramite = '', $usuariocontrol = '', $emailusuariocontrol = '', $nombreusuariocontrol = '', $celularusuariocontrol = '', $ipcliente = '') {

        if ($ipcliente == '') {
            $ipcliente = \funcionesGenerales::localizarIP();
        }

        //
        if ($usuariocontrol != 'USUPUBXX') {
            return "false:No es usuario público (USUPUBXX)";
        }

        //
        $matricula = '';
        $proponente = '';

        //
        $resEmail = retornarRegistroMysqliApi($dbx, 'mreg_email_excluidos_alertas_tempranas', "email='" . $emailusuariocontrol . "'");
        if ($resEmail && !empty($resEmail)) {
            return "false:Email excluido";
        }

        //
        $res = retornarRegistroMysqliApi($dbx, 'mreg_alertas_tempranas', "idliquidacion=" . $idliquidacion);
        if ($res && !empty($res)) {
            return "false:Alerta ya enviada para esta liquidación";
        }

        //
        if ($tiporegistro == 'RegPro') {
            $proponente = $expediente;
            $arrExp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "proponente='" . $expediente . "'");
        } else {
            $matricula = $expediente;
            $arrExp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $expediente . "'");
        }

        //
        if ($arrExp === false || empty($arrExp)) {
            return "false:Expediente no localizado en la BD";
        }
        
        //
        $email = $arrExp["emailnot"];
        if ($email == '') {
            $email = $arrExp["emailcom"];
        }
        if (trim($email) == '') {
            return "false:Expediente sin emails para informar";
        }

        //
        $asunto = 'Alerta temprana por acceso al expediente No. ' . trim($matricula . $proponente) . ' en la ' . RAZONSOCIAL;
        $detalle = 'Señor(es)<br>';
        $detalle .= $arrExp["razonsocial"] . '<br><br>';
        $detalle .= 'Nos permitimos informarle que el día ' . date("Y-m-d") . ' a las ' . date("H:i:s") . ' ';
        $detalle .= 'se solicitó en los sistemas de registro que administra la ' . RAZONSOCIAL . ' el siguiente trámite:<br><br>';
        $detalle .= '- Expediente : ' . $expediente . '<br>';
        $detalle .= '- Trámite solicitado : ' . $tipotramite . '<br>';
        $detalle .= '- Email del usuario que solicita el trámite : ' . $emailusuariocontrol . '<br>';
        $detalle .= '- Ip del usuario : ' . $ipcliente . '<br><br>';
        $detalle .= 'Esta alerta se genera en cumplimiento de lo establecido en la Circular Unica de la Supersociedades, ';
        $detalle .= 'numeral 1.1.12.5.<br><br>';
        $detalle .= 'Cordialmente<br><br>';
        $detalle .= 'Area de Registros Públicos<br>';
        $detalle .= RAZONSOCIAL;

        //
        $arrCampos = array(
            'idliquidacion',
            'matricula',
            'proponente',
            'fecha',
            'hora',
            'email',
            'celular',
            'usuario',
            'tipotramite',
            'ip',
            'textoalerta',
            'estado'
        );
        $arrValores = array(
            $idliquidacion,
            "'" . $matricula . "'",
            "'" . $proponente . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . addslashes($email) . "'",
            "''", // celular
            "'" . addslashes($emailusuariocontrol) . "'",
            "'" . $tipotramite . "'",
            "'" . $ipcliente . "'",
            "'" . addslashes($detalle) . "'",
            "'1'" // programada
        );
        insertarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores);

        //
        if (!defined('TIPO_AMBIENTE')) {
            define('TIPO_AMBIENTE', 'PRUEBAS');
        }

        //
        $emx = $email;
        if (TIPO_AMBIENTE == 'PRUEBAS') {
            if (defined('EMAIL_NOTIFICACION_PRUEBAS') && trim(EMAIL_NOTIFICACION_PRUEBAS) != '') {
                $emx = EMAIL_NOTIFICACION_PRUEBAS;
            } else {
                $emx = 'jint@confecamaras.org.co';
            }
        }

        //
        if ($emailusuariocontrol != 'prueba@prueba.prueba') {
            $okEmail = true;
            $iEmail = 1;
            while ($okEmail === true && $iEmail <= 5) {
                $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, $asunto, $detalle);
                if ($res === false) {
                    sleep(3);
                    $iEmail++;
                } else {
                    $okEmail = false;
                }
            }

            //
            if ($res) {
                $arrCampos = array(
                    'estado'
                );
                $arrValores = array(
                    "'3'" // Enviado con éxito
                );
                regrabarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores, "idliquidacion=" . $idliquidacion);
            } else {
                $arrCampos = array(
                    'estado'
                );
                $arrValores = array(
                    "'4'" // Envio con error
                );
                regrabarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores, "idliquidacion=" . $idliquidacion);
            }

            // 2022-06-15 Envío al sms
            $cel = '';
            if (trim($arrExp["telnot"]) != '' && strlen($arrExp["telnot"]) == 10 && substr($arrExp["telnot"], 0, 1) == '3') {
                $cel = $arrExp["telnot"];
            }
            if ($cel == '') {
                if (trim($arrExp["telnot2"]) != '' && strlen($arrExp["telnot2"]) == 10 && substr($arrExp["telnot2"], 0, 1) == '3') {
                    $cel = $arrExp["telnot2"];
                }
            }
            if ($cel == '') {
                if (trim($arrExp["telnot3"]) != '' && strlen($arrExp["telnot3"]) == 10 && substr($arrExp["telnot3"], 0, 1) == '3') {
                    $cel = $arrExp["telnot3"];
                }
            }
            if ($cel == '') {
                if (trim($arrExp["telcom1"]) != '' && strlen($arrExp["telcom1"]) == 10 && substr($arrExp["telcom1"], 0, 1) == '3') {
                    $cel = $arrExp["telcom1"];
                }
            }
            if ($cel == '') {
                if (trim($arrExp["telcom2"]) != '' && strlen($arrExp["telcom2"]) == 10 && substr($arrExp["telcom2"], 0, 1) == '3') {
                    $cel = $arrExp["telcom2"];
                }
            }
            if ($cel == '') {
                if (trim($arrExp["telcom3"]) != '' && strlen($arrExp["telcom3"]) == 10 && substr($arrExp["telcom3"], 0, 1) == '3') {
                    $cel = $arrExp["telcom3"];
                }
            }
            if (trim($cel) != '') {
                $expt = '';
                if (trim($matricula) != '') {
                    $expt = $matricula;
                }
                if (trim($proponente) != '') {
                    $expt = $proponente;
                }
                $txt = 'Le informamos que se inició un trámite de ' . $tipotramite . ' sobre el expediente No. ' . $expt;
                \funcionesRegistrales::actualizarPilaSms($dbx, '', $cel, '11', '', '', '', '', $expt, $matricula, $proponente, $arrExp["numid"], $arrExp["reazonsocial"], $txt, $obs);
            }
        }

        return true;
    }

}

?>
