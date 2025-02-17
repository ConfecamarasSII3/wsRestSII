<?php

class funcionesRegistrales_rutinaNotificarInscripciones {

    /**
     * 
     * @param type $mysqli
     * @param type $codbarras
     * @param type $idliquidacion
     * @param string $nameLog
     * @param type $libro
     * @param type $inscripcion
     * @param type $tipo
     * @param type $forzar
     */
    public static function rutinaNotificarInscripciones($mysqli, $codbarras = '', $idliquidacion = 0, $nameLog = '', $libro = '', $inscripcion = '', $tipo = 'todos', $forzar = 'no') {

        ini_set('memory_limit', '1024M');

        if ($nameLog == '') {
            $nameLog = 'rutinaNotificarInscripciones_API_' . date("Ymd");
        }
        \logApi::general2($nameLog, $idliquidacion, 'Ingreso a notificar inscripciones  : ' . $codbarras);

        $arrEmails = array();
        $arrTels = array();
        $arrTem = false;

        //
        if (trim($codbarras) != '') {
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "idradicacion='" . ltrim($codbarras, "0") . "'", "id");
        } else {
            if ($libro != '' && $inscripcion != '') {
                $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $libro . "' and registro='" . $inscripcion . "'", "id");
            }
        }
        if ($arrTem && !empty($arrTem)) {
            foreach ($arrTem as $t) {
                $inc = 'no';
                if ($forzar == 'si') {
                    $inc = 'si';
                } else {
                    if ($t["idnotificacionemail"] == 0 || $t["idnotificacionsms"] == 0) {
                        $inc = 'si';
                    }
                }
                if ($inc == 'si') {
                    $bandejaDigitalizacion = '4.-REGMER';
                    if (substr($t["libro"], 0, 2) == 'RE') {
                        $bandejaDigitalizacion = '5.-REGESADL';
                    }
                    if ($t["matricula"] != '') {
                        $exps = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $t["matricula"] . "'");
                        $exps["emailcom"] = str_replace(".@", "@", $exps["emailcom"]);
                        if ($exps["emailcom"] != '') {
                            $arrEmails[$exps["emailcom"]] = $exps["emailcom"];
                        }
                        $exps["emailnot"] = str_replace(".@", "@", $exps["emailnot"]);
                        if ($exps["emailnot"] != '') {
                            $arrEmails[$exps["emailnot"]] = $exps["emailnot"];
                        }

//
                        $exps["telcom1"] = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $exps["telcom1"])), "0");
                        $exps["telcom2"] = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $exps["telcom2"])), "0");
                        $exps["telcom3"] = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $exps["telcom3"])), "0");
                        $exps["telnot"] = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $exps["telnot"])), "0");
                        $exps["telnot2"] = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $exps["telnot2"])), "0");
                        $exps["telnot3"] = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $exps["telnot3"])), "0");

//
                        if ($exps["telcom1"] != '' && strlen($exps["telcom1"]) == 10 && substr($exps["telcom1"], 0, 1) == '3') {
                            $arrTels[$exps["telcom1"]] = $exps["telcom1"];
                        }
                        if ($exps["telcom2"] != '' && strlen($exps["telcom2"]) == 10 && substr($exps["telcom2"], 0, 1) == '3') {
                            $arrTels[$exps["telcom2"]] = $exps["telcom2"];
                        }
                        if ($exps["telcom3"] != '' && strlen($exps["telcom3"]) == 10 && substr($exps["telcom3"], 0, 1) == '3') {
                            $arrTels[$exps["telcom3"]] = $exps["telcom3"];
                        }
                        if ($exps["telnot"] != '' && strlen($exps["telnot"]) == 10 && substr($exps["telnot"], 0, 1) == '3') {
                            $arrTels[$exps["telnot"]] = $exps["telnot"];
                        }
                        if ($exps["telnot2"] != '' && strlen($exps["telnot2"]) == 10 && substr($exps["telnot2"], 0, 1) == '3') {
                            $arrTels[$exps["telnot2"]] = $exps["telnot2"];
                        }
                        if ($exps["telnot3"] != '' && strlen($exps["telnot3"]) == 10 && substr($exps["telnot3"], 0, 1) == '3') {
                            $arrTels[$exps["telnot3"]] = $exps["telnot3"];
                        }

                        // *********************************************************************************** //
                        // Localiza emails y celulares anteriores migrados del SII
                        // Todos, busca los anteriores
                        // *********************************************************************************** //  
                        if ($tipo == 'todos') {
                            $exps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim($t["matricula"], "0") . "'", "id");
                            if ($exps && !empty($exps)) {
                                foreach ($exps as $exps1) {
                                    if (trim($exps1["campo"]) == "EMAILCOM-ANTERIOR") {
                                        $exps1["contenido"] = str_replace(".@", "@", $exps1["contenido"]);
                                        $arrEmails[trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                    }
                                    if (trim($exps1["campo"]) == "EMAILNOT-ANTERIOR") {
                                        $exps1["contenido"] = str_replace(".@", "@", $exps1["contenido"]);
                                        $arrEmails[trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                    }
                                    if (trim($exps1["campo"]) == "CELCOM-ANTERIOR") {
                                        $exps1["contenido"] = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $exps1["contenido"])), "0");
                                        if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                            $arrTels[trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                        }
                                    }
                                    if (trim($exps1["campo"]) == "CELNOT-ANTERIOR") {
                                        $exps1["contenido"] = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $exps1["contenido"])), "0");
                                        if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                            $arrTels[trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                        }
                                    }
                                }
                            }

                            // *********************************************************************************** //
                            // Localiza emails y celulares modificados en mreg_campos_historicos_AAAA
                            // *********************************************************************************** //                                                            
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $t["matricula"], 'telcom1');
                            foreach ($d as $d1) {
                                $d1 = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $d1)), "0");
                                if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                                    $arrTels[trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $t["matricula"], 'telcom2');
                            foreach ($d as $d1) {
                                $d1 = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $d1)), "0");
                                if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                                    $arrTels[trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $t["matricula"], 'telcom3');
                            foreach ($d as $d1) {
                                $d1 = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $d1)), "0");
                                if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                                    $arrTels[trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $t["matricula"], 'telnot');
                            foreach ($d as $d1) {
                                $d1 = ltrim(trim(str_replace(array("+", " ", ",", ".", "-"), "", $d1)), "0");
                                if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                                    $arrTels[trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $t["matricula"], 'emailcom');
                            foreach ($d as $d1) {
                                $d1 = str_replace(".@", "@", $d1);
                                $arrEmails[trim($d1)] = trim($d1);
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $t["matricula"], 'emailnot');
                            foreach ($d as $d1) {
                                $d1 = str_replace(".@", "@", $d1);
                                $arrEmails[trim($d1)] = trim($d1);
                            }
                        }

                        //
                        $arrCampos = array(
                            'recibo',
                            'codigobarras',
                            'idliquidacion',
                            'tipo',
                            'contenido'
                        );
                        $arrValores = array();
                        if (!empty($arrEmails)) {
                            $arrValores = array();
                            foreach ($arrEmails as $e) {
                                $arrValores[] = array(
                                    "''",
                                    "'" . $codbarras . "'",
                                    "'" . $idliquidacion . "'",
                                    "'email-inscripcion'",
                                    "'" . $e . "'"
                                );
                            }
                        }
                        if (!empty($arrTels)) {
                            $arrValores = array();
                            foreach ($arrTels as $e) {
                                $arrValores[] = array(
                                    "''",
                                    "'" . $codbarras . "'",
                                    "'" . $idliquidacion . "'",
                                    "'telefono-inscripcion'",
                                    "'" . $e . "'"
                                );
                            }
                        }

                        // *********************************************************************************** //
                        // Almacena tabla donde guarda que emails y celulares será notificados por recibo
                        // *********************************************************************************** //
                        if (!empty($arrValores)) {
                            insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_recibos_sipref_destinos', $arrCampos, $arrValores);
                        }

                        //
                        if (!empty($arrEmails)) {
                            $totEmails = 0;
                            $totCorrectos = 0;
                            foreach ($arrEmails as $em) {
                                $totEmails++;
                                $mensaje = \funcionesRegistrales::generarEmailNotificacionInscripcionSipref($mysqli, $t);
                                $emx = $em;
                                if (TIPO_AMBIENTE == 'PRUEBAS') {
                                    $emx = 'jint@confecamaras.org.co';
                                    if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                                        $emx = EMAIL_NOTIFICACION_PRUEBAS;
                                    }
                                }
                                $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, 'Notificacion de inscripcion No. ' . $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"] . ' en  LA ' . RAZONSOCIAL, $mensaje, array());
                                if ($rEmail) {
                                    $totCorrectos++;
                                    if ($codbarras != '') {
                                        \logApi::general2($nameLog, $idliquidacion, 'Notificando Inscripcion No. ' . $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"] . ' del codigo de barras: ' . $t["idradicacion"] . ', Email : ' . $em . ' ** OK **');
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '03', $t["idradicacion"], '', $t["numerooperacion"], $t["recibo"], $t["libro"], $t["registro"], $t["dupli"], $t["tipoidentificacion"], $t["identificacion"], $t["matricula"], '', $t["nombre"], $em, $mensaje, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '** OK **', $bandejaDigitalizacion);
                                    } else {
                                        \logApi::general2($nameLog, $idliquidacion, 'Notificando Inscripcion No. ' . $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"] . ', Email : ' . $em . ' ** OK **');
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '03', '', '', $t["numerooperacion"], $t["recibo"], $t["libro"], $t["registro"], $t["dupli"], $t["tipoidentificacion"], $t["identificacion"], $t["matricula"], '', $t["nombre"], $em, $mensaje, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '** OK **', $bandejaDigitalizacion);
                                    }
                                } else {
                                    if ($codbarras != '') {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '03', $t["idradicacion"], '', $t["numerooperacion"], $t["recibo"], $t["libro"], $t["registro"], $t["dupli"], $t["tipoidentificacion"], $t["identificacion"], $t["matricula"], '', $t["nombre"], $em, $mensaje, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR: ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $idliquidacion, 'Notificando Inscripcion No. ' . $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"] . ' del codigo de barras: ' . $t["idradicacion"] . ', Email : ' . $em . ' ** ERROR : ' . $_SESSION["generales"]["mensajeerror"]);
                                    } else {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '03', '', '', $t["numerooperacion"], $t["recibo"], $t["libro"], $t["registro"], $t["dupli"], $t["tipoidentificacion"], $t["identificacion"], $t["matricula"], '', $t["nombre"], $em, $mensaje, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR: ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $idliquidacion, 'Notificando Inscripcion No. ' . $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"] . ', Email : ' . $em . ' ** ERROR : ' . $_SESSION["generales"]["mensajeerror"]);
                                    }
                                }
                            }
                            if ($totCorrectos > 0) {
                                $valx = 1; // Enviado al menos a un email
                            } else {
                                $valx = 2; // Envios incorrectos
                            }
                        } else {
                            $valx = 3; // Sin emails para notificar
                        }
                        $arrCampos = array(
                            'idnotificacionemail'
                        );
                        $arrValores = array(
                            $valx
                        );
                        $condicion = "libro='" . $t["libro"] . "' and registro='" . $t["registro"] . "' and dupli='" . $t["dupli"] . "'";
                        $resg = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', $arrCampos, $arrValores, $condicion);
                        if ($resg === false) {
                            \logApi::general2($nameLog, $idliquidacion, 'Error regrabando mreg_est_inscripciones, campo idnotificacionemail : ' . $_SESSION["generales"]["mensajeerror"]);
                        }
                        if ($codbarras != '') {
                            \logApi::general2($nameLog, $idliquidacion, 'Salio de enviar emails de notificación sipref de inscripciones, radicado ' . $codbarras);
                        }

//
                        if (!empty($arrTels)) {
                            $txtactonombre = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $t["libro"] . "' and idacto='" . $t["acto"] . "'", "nombre");
                            $txtactonombre = str_replace (array ('Á','É','Í','Ó','Ú'), array ('A','E','I','O','U'), $txtactonombre);
                            $txtactonombre = str_replace (array ('á','é','í','ó','ú'), array ('a','e','i','o','u'), $txtactonombre);
                            $txtx = 'Expediente ' . $t["matricula"] . ', Acto: ' . $txtactonombre;
                            if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                                $txtSms = 'La ' . RAZONSOCIALSMS . ' informa inscripcion, ' . $txtx . ', al correo electronico se envio informacion para verificar procedencia.';
                            } else {
                                $txtSms = 'La ' . RAZONSOCIAL . ' informa inscripcion, ' . $txtx . ', al correo electronico se envio informacion para verificar procedencia.';
                            }
                            foreach ($arrTels as $tx) {
                                if ($codbarras != '') {
                                    \funcionesRegistrales::actualizarPilaSms($mysqli, '', $tx, '2', $t["recibo"], $t["idradicacion"], $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"], '', $t["matricula"], $t["matricula"], '', $t["identificacion"], $t["nombre"], $txtSms, '', $bandejaDigitalizacion);
                                } else {
                                    \funcionesRegistrales::actualizarPilaSms($mysqli, '', $tx, '2', $t["recibo"], '', $t["libro"] . '-' . $t["registro"] . '-' . $t["dupli"], '', $t["matricula"], $t["matricula"], '', $t["identificacion"], $t["nombre"], $txtSms, '', $bandejaDigitalizacion);
                                }
                            }
                            \logApi::general2($nameLog, $idliquidacion, 'Salio de crear pila_sms');
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
                        $resg = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', $arrCampos, $arrValores, $condicion);
                        if ($resg === false) {
                            \logApi::general2($nameLog, $idliquidacion, 'Error regrabando mreg_est_inscripciones, campo idnotificacionsms : ' . $_SESSION["generales"]["mensajeerror"]);
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $idliquidacion, 'Salio de la generacion de notificaciones sipref de inscripcion');
    }

}

?>
