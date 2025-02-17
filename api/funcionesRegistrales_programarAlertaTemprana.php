<?php

class funcionesRegistrales_programarAlertaTemprana {

    public static function programarAlertaTemprana($mysqli, $tiporegistro, $liquidacion, $matricula, $proponente, $tipotramite) {

//
        if ($_SESSION["generales"]["tipousuario"] == '00' || $_SESSION["generales"]["tipousuario"] == '06' || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            if (trim($_SESSION["generales"]["emailusuariocontrol"]) != '') {
                $resEmail = retornarRegistroMysqliApi($mysqli, 'mreg_email_excluidos_alertas_tempranas', "email='" . $_SESSION["generales"]["emailusuariocontrol"] . "'");
                $res = retornarRegistroMysqliApi($mysqli, 'mreg_alertas_tempranas', "idliquidacion=" . $liquidacion);
                if ($res === false || empty($res)) {
                    if ($tiporegistro == 'RegPro') {
                        $arrExp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $proponente . "'");
                    } else {
                        $arrExp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matricula . "'");
                    }
                    if ($arrExp && !empty($arrExp)) {

                        // Envío al email
                        $email = $arrExp["emailnot"];
                        if ($email == '') {
                            $email = $arrExp["emailcom"];
                        }
                        if (trim($email) != '') {
                            $asunto = 'Alerta temprana por acceso al expediente No. ' . trim($matricula . $proponente) . ' en la ' . RAZONSOCIAL;
                            $detalle = 'Señor(es)<br>';
                            $detalle .= $arrExp["razonsocial"] . '<br><br>';
                            $detalle .= 'Nos permitimos informarle que el día ' . date("Y-m-d") . ' a las ' . date("H:i:s") . ' ';
                            $detalle .= 'se solicitó en los sistemas de registro que administra la ' . RAZONSOCIAL . ' el siguiente trámite:<br><br>';
                            $detalle .= '- Expediente : ' . $matricula . $proponente . '<br>';
                            $detalle .= '- Trámite solicitado : ' . $tipotramite . '<br>';
                            $detalle .= '- Email del usuario que solicita el trámite : ' . $_SESSION["generales"]["emailusuariocontrol"] . '<br>';
                            $detalle .= '- Ip del usuario : ' . \funcionesGenerales::localizarIP() . '<br><br>';
                            $detalle .= 'Esta alerta se genera en cumplimiento de lo establecido en la Circular Unica de la Supersociedades, ';
                            $detalle .= 'numeral 1.1.12.5.<br><br>';
                            $detalle .= 'Cordialmente<br><br>';
                            $detalle .= 'Area de Registros Públicos<br>';
                            $detalle .= RAZONSOCIAL;

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
                                $liquidacion,
                                "'" . $matricula . "'",
                                "'" . $proponente . "'",
                                "'" . date("Ymd") . "'",
                                "'" . date("His") . "'",
                                "'" . addslashes($email) . "'",
                                "''", // celular
                                "'" . addslashes($_SESSION["generales"]["emailusuariocontrol"]) . "'",
                                "'" . $tipotramite . "'",
                                "'" . \funcionesGenerales::localizarIP() . "'",
                                "'" . addslashes($detalle) . "'",
                                "'1'" // programada
                            );
                            insertarRegistrosMysqliApi($mysqli, 'mreg_alertas_tempranas', $arrCampos, $arrValores);

                            if (!defined('TIPO_AMBIENTE')) {
                                define('TIPO_AMBIENTE', 'PRUEBAS');
                            }
                            if (TIPO_AMBIENTE == 'PRODUCCION') {
                                if (!$resEmail || empty($resEmail)) {
                                    if ($_SESSION["generales"]["emailusuariocontrol"] != 'prueba@prueba.prueba') {
                                        $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $email, $asunto, $detalle);
                                        if ($res === false) {
                                            sleep(3);
                                            $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $email, $asunto, $detalle);
                                        }
                                    }
                                }
                            } else {
                                if ($_SESSION["generales"]["emailusuariocontrol"] != 'prueba@prueba.prueba') {
                                    if (isset($resEmail) && !empty($resEmail)) {
                                        $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $resEmail["email"], $asunto, $detalle);
                                        if ($res === false) {
                                            $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $resEmail["email"], $asunto, $detalle);
                                        }
                                    }
                                }
                            }
                            if ($res) {
                                $arrCampos = array(
                                    'estado'
                                );
                                $arrValores = array(
                                    "'3'" // Enviado con éxito
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_alertas_tempranas', $arrCampos, $arrValores, "idliquidacion=" . $liquidacion);
                            } else {
                                $arrCampos = array(
                                    'estado'
                                );
                                $arrValores = array(
                                    "'4'" // Envio con error
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_alertas_tempranas', $arrCampos, $arrValores, "idliquidacion=" . $liquidacion);
                            }
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
                            \funcionesRegistrales::actualizarPilaSms($mysqli, '', $cel, '11', '', '', '', '', $expt, $matricula, $proponente, $arrExp["numid"], $arrExp["razonsocial"], $txt, '');
                        }
                    }
                }
            }
        }
    }

}

?>
