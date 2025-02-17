<?php

class funcionesRegistrales_generarAlertas {

    public static function generarAlertaConsulta ($dbx, $expediente = '', $usuariocontrol = '', $emailusuariocontrol = '', $nombreusuariocontrol = '', $celularusuariocontrol = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

        //
        if (defined('GENERAR_ALERTAS_CONSULTAS') && GENERAR_ALERTAS_CONSULTAS == 'NO') {
            return true;
        }

        //
        if ($usuariocontrol != 'USUPUBXX') {
            return true;
        }

        //
        $mysqli = conexionMysqliApi();
        
        //
        $condicion = "matricula='" . $expediente . "' and fecha='" . date("Ymd") . "' and email='" . $emailusuariocontrol . "'";
        if (contarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $condicion) > 0) {
            $mysqli->close();
            return true;
        }

        //
        $arrExp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli,$expediente);
        if ($arrExp === false || empty($arrExp)) {
            $mysqli->close();
            return true;            
        }
        
        //
        if ($arrExp["emailcom"] != '') {
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
            $mensaje = 'Apreciado usuario<br><br>';
            $mensaje .= 'Nombre: ' . $arrExp["nombre"] . '<br>';
            if (trim($arrExp["identificacion"]) != '') {
                $mensaje .= 'Identificacion: ' . $arrExp["identificacion"] . '<br>';
            }
            $mensaje .= 'Matrícula: ' . $arrExp["matricula"] . '<br><br>';
            $mensaje .= 'Señor comerciante, nos permitimos informarle que el día de hoy a las ' . date("H:i:s") . ' su expediente ha sido consultado por ';
            $mensaje .= $nombreusuariocontrol . ' cuyo correo electrónico es ' . $emailusuariocontrol . ' ';
            $mensaje .= 'desde la IP ' . \funcionesGenerales::localizarIP() . '.<br><br>';
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
                }
            }
            if ($okemail) {
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
                    "'" . \funcionesGenerales::localizarIP() . "'",
                    "'" . addslashes($mensaje) . "'",
                    "'2'"
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_alertas_tempranas', $arrCampos, $arrValores);
            }
        }

        //
        $mysqli->close();
        
        //
        return true;
    }

}

?>
