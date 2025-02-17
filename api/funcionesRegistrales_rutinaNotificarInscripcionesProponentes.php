<?php

class funcionesRegistrales_rutinaNotificarInscripcionesProponentes {

    public static function rutinaNotificarInscripcionesProponentes($mysqli, $proponente, $libro, $registro) {

        $nameLog = 'rutinaNotificarInscripcionesProponentesAPI_' . date("Ymd");
        \logApi::general2($nameLog, '', 'Ingreso a notificar sipref inscripcion de proponentes : ' . $proponente . ':' . $libro . ':' . $registro);
        $prop = \funcionesRegistrales::retornarExpedienteProponente($mysqli, $proponente, '', '', '', '', '', 'no', 'no');
        if ($prop && !empty($prop)) {
            $arrTels = array();
            $arrEmails = array();

            if ($prop["telcom1"] != '' && strlen($prop["telcom1"]) == 10 && substr($prop["telcom1"], 0, 1) == '3') {
                $arrTels[$prop["telcom1"]] = $prop["telcom1"];
            }
            if ($prop["telcom2"] != '' && strlen($prop["telcom2"]) == 10 && substr($prop["telcom2"], 0, 1) == '3') {
                $arrTels[$prop["telcom2"]] = $prop["telcom2"];
            }
            if ($prop["celcom"] != '' && strlen($prop["celcom"]) == 10 && substr($prop["celcom"], 0, 1) == '3') {
                $arrTels[$prop["celcom"]] = $prop["celcom"];
            }
            if ($prop["telnot"] != '' && strlen($prop["telnot"]) == 10 && substr($prop["telnot"], 0, 1) == '3') {
                $arrTels[$prop["telnot"]] = $prop["telnot"];
            }
            if ($prop["telnot2"] != '' && strlen($prop["telnot2"]) == 10 && substr($prop["telnot2"], 0, 1) == '3') {
                $arrTels[$prop["telnot2"]] = $prop["telnot2"];
            }
            if ($prop["celnot"] != '' && strlen($prop["celnot"]) == 10 && substr($prop["celnot"], 0, 1) == '3') {
                $arrTels[$prop["celnot"]] = $prop["celnot"];
            }
            if (trim($prop["emailcom"]) != '') {
                $prop["emailcom"] = str_replace(".@", "@", $prop["emailcom"]);
                $arrEmails[$prop["emailcom"]] = $prop["emailcom"];
            }
            if (trim($prop["emailnot"]) != '') {
                $prop["emailnot"] = str_replace(".@", "@", $prop["emailnot"]);
                $arrEmails[$prop["emailnot"]] = $prop["emailnot"];
            }
            $t = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "libro='" . $libro . "' and registro='" . $registro . "'");
            if ($t && !empty($t)) {
                if (!empty($arrEmails)) {
                    $totEmails = 0;
                    $totCorrectos = 0;
                    foreach ($arrEmails as $em) {
                        $totEmails++;
                        $t["noticia"] = $t["texto"];
                        $mensaje = \funcionesRegistrales::generarEmailNotificacionInscripcionSipref($mysqli, $t);
                        $emx = $em;
                        if (TIPO_AMBIENTE == 'PRUEBAS') {
                            $emx = 'jnieto@confecamaras.org.co';
                            if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                                $emx = EMAIL_NOTIFICACION_PRUEBAS;
                            }
                        }
                        $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, 'Notificacion de inscripcion No. ' . $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"] . ' en  LA ' . RAZONSOCIAL, $mensaje, array());
                        if ($rEmail) {
                            $totCorrectos++;
                            \logApi::general2($nameLog, '', 'Notificando Inscripcion No. ' . $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"] . ' del codigo de barras: ' . $t["idradicacion"] . ', Email : ' . $em . ' ** OK **');
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '03', $t["idradicacion"], '', $t["numerooperacion"], $t["recibo"], $t["libro"], $t["registro"], $t["dupli"], $t["tipoidentificacion"], $t["identificacion"], '', $proponente, $t["nombre"], $em, $mensaje, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '** OK **', '6.-REGPRO');
                        } else {
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '03', $t["idradicacion"], '', $t["numerooperacion"], $t["recibo"], $t["libro"], $t["registro"], $t["dupli"], $t["tipoidentificacion"], $t["identificacion"], '', $proponente, $t["nombre"], $em, $mensaje, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR: ' . $_SESSION["generales"]["mensajeerror"], '6.-REGPRO');
                            \logApi::general2($nameLog, '', 'Notificando Inscripcion No. ' . $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"] . ' del codigo de barras: ' . $t["idradicacion"] . ', Email : ' . $em . ' ** ERROR : ' . $_SESSION["generales"]["mensajeerror"]);
                        }
                    }
                    if ($totCorrectos > 0) {
                        $valx = 1; // Enviado al menos a un email
                    } else {
                        $valx = 2; // Envios incorrectos
                    }
                } else {
                    $valx = 3; // SIn emails para notificar
                }
                $arrCampos = array(
                    'idnotificacionemail'
                );
                $arrValores = array(
                    $valx
                );
                $condicion = "libro='" . $t["libro"] . "' and registro='" . $t["registro"] . "' and dupli='" . $t["dupli"] . "'";
                $resg = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', $arrCampos, $arrValores, $condicion);
                if ($resg === false) {
                    \logApi::general2($nameLog, '', 'Error regrabando mreg_est_inscripciones_proponentes, campo idnotificacionemail : ' . $_SESSION["generales"]["mensajeerror"]);
                }
                if (!empty($arrTels)) {
                    $txtx = '';
                    $txtx = 'Acto: ' . retornarRegistroMysqliApi($mysqli, 'mreg_actosproponente', "id='" . $t["acto"] . "'", "descripcion");
                    if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                        // $txtSms = 'La ' . RAZONSOCIALSMS . ' le informa que se inscribio un tramite que modifica su registro, ' . $txtx;
                        $txtSms = 'La ' . RAZONSOCIALSMS . ' le informa que se inscribio un tramite que modifica su registro, al correo electrónico se envió información para verificar procedencia.';
                    } else {
                        // $txtSms = 'La ' . RAZONSOCIAL . ' le informa que se inscribio un tramite que modifica su registro, ' . $txtx;
                        $txtSms = 'La ' . RAZONSOCIAL . ' le informa que se inscribio un tramite que modifica su registro, al correo electrónico se envió información para verificar procedencia.';
                    }
                    foreach ($arrTels as $tx) {
                        \funcionesRegistrales::actualizarPilaSms($mysqli, '', $tx, '2', $t["recibo"], $t["idradicacion"], $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"], '', $t["proponente"], '', $t["proponente"], $t["identificacion"], $t["nombre"], $txtSms, '', '6.-REGPRO');
                    }
                    $valx = 1; // Enviado al menos a un celular
                } else {
                    $valx = 3; // Sin celulares para notificar
                }
                $arrCampos = array(
                    'idnotificacionsms'
                );
                $arrValores = array(
                    $valx
                );
                $condicion = "libro='" . $t["libro"] . "' and registro='" . $t["registro"] . "' and dupli='" . $t["dupli"] . "'";
                $resg = regrabarRegistrosMysqli($mysqli, 'mreg_est_inscripciones_proponentes', $arrCampos, $arrValores, $condicion);
                if ($resg === false) {
                    \logApi::general2($nameLog, '', 'Error regrabando mreg_est_inscripciones_proponentes, campo idnotificacionsms : ' . $_SESSION["generales"]["mensajeerror"]);
                }
                return true;
            } else {
                \logApi::general2($nameLog, '', 'No localizo la inscripcion en mreg_est_inscripciones_proponentes : ' . $libro . '-' . $registro);
                return false;
            }
        } else {
            \logApi::general2($nameLog, '', 'No localizo el proponente en mreg_est_inscritos : ' . $proponente);
            return false;
        }
    }
}

?>
