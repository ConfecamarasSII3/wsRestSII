<?php

class funcionesRegistrales_retornarInscripcionesNoNotificadas {

    public static function retornarInscripcionesNoNotificadas($mysqli, $cri, $lib = '', $regi = '', $dup = '', $fecini = '', $tipo = 'I') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        $resultado = array(
            'codigoError' => '0000',
            'msgError' => '',
            'inscripciones' => array()
        );

        if ($cri == 'L') {
            if ($tipo == 'I') {
                $reg1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "fecharegistro >= '" . $fecini . "' and idnotificacionemail = 0 and idnotificacionsms = 0", "libro,registro");
            }
            if ($tipo == 'F') {
                $reg1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "fecharegistro >= '" . $fecini . "' and idnotificacionemail = 0 and idnotificacionsms = 0 and clavefirmado > ''", "libro,registro");
            }

            $i = 0;
            if ($reg1 && !empty($reg1)) {
                foreach ($reg1 as $reg) {
                    $i++;
                    $resultado["inscripciones"][$i]["lib"] = $reg["libro"];
                    $resultado["inscripciones"][$i]["reg"] = $reg["registro"];
                    $resultado["inscripciones"][$i]["dup"] = $reg["dupli"];
                    $resultado["inscripciones"][$i]["nrec"] = $reg["recibo"];
                    $resultado["inscripciones"][$i]["ope"] = $reg["numerooperacion"];
                    $resultado["inscripciones"][$i]["fec"] = $reg["fecharegistro"];
                    $resultado["inscripciones"][$i]["hor"] = $reg["horaregistro"];
                    $resultado["inscripciones"][$i]["ide"] = $reg["identificacion"];
                    $resultado["inscripciones"][$i]["nom"] = $reg["nombre"];
                    $resultado["inscripciones"][$i]["exp"] = '';
                    $resultado["inscripciones"][$i]["mat"] = $reg["matricula"];
                    $resultado["inscripciones"][$i]["pro"] = '';
                    $resultado["inscripciones"][$i]["act"] = $reg["acto"];
                    $resultado["inscripciones"][$i]["emails"] = array();
                    $resultado["inscripciones"][$i]["telefonos"] = array();
                }
            }

            if ($tipo == 'I') {
                $reg1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "fecharegistro >= '" . $fecini . "' and idnotificacionemail = 0 and idnotificacionsms = 0", "libro,registro");
            }
            if ($tipo == 'F') {
                $reg1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "fecharegistro >= '" . $fecini . "' and idnotificacionemail = 0 and idnotificacionsms = 0 and clavefirmado > ''", "libro,registro");
            }

            if ($reg1 && !empty($reg1)) {
                foreach ($reg1 as $reg) {
                    $i++;
                    $resultado["inscripciones"][$i]["lib"] = $reg["libro"];
                    $resultado["inscripciones"][$i]["reg"] = $reg["registro"];
                    $resultado["inscripciones"][$i]["dup"] = '';
                    $resultado["inscripciones"][$i]["nrec"] = $reg["recibo"];
                    $resultado["inscripciones"][$i]["ope"] = $reg["numerooperacion"];
                    $resultado["inscripciones"][$i]["fec"] = $reg["fecharegistro"];
                    $resultado["inscripciones"][$i]["hor"] = $reg["horaregistro"];
                    $resultado["inscripciones"][$i]["ide"] = $reg["identificacion"];
                    $resultado["inscripciones"][$i]["nom"] = $reg["nombre"];
                    $resultado["inscripciones"][$i]["exp"] = '';
                    $resultado["inscripciones"][$i]["mat"] = '';
                    $resultado["inscripciones"][$i]["pro"] = $reg["proponente"];
                    $resultado["inscripciones"][$i]["act"] = $reg["acto"];
                    $resultado["inscripciones"][$i]["emails"] = array();
                    $resultado["inscripciones"][$i]["telefonos"] = array();
                }
            }

            for ($j = 1; $j <= $i; $j++) {
                // Recupera números telefonicos y emails actuales - mercantil
                if ($resultado["inscripciones"][$j]["mat"] != '') {
                    $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $resultado["inscripciones"][$j]["mat"] . "'");
                    if ($exp && empty($exp)) {
                        if (trim($exp["telcom1"]) != '') {
                            if (strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                            }
                        }
                        if (trim($exp["telcom2"])) {
                            if (strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                            }
                        }
                        if (trim($exp["telcom3"])) {
                            if (strlen($exp["telcom3"]) == 10 && substr($exp["telcom3"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telcom3"]] = $exp["telcom3"];
                            }
                        }
                        if (trim($exp["telnot"])) {
                            if (strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telnot"]] = $exp["telnot"];
                            }
                        }
                        if (trim($exp["telnot2"])) {
                            if (strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                            }
                        }
                        if (trim($exp["telnot3"])) {
                            if (strlen($exp["telnot3"]) == 10 && substr($exp["telnot3"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telnot3"]] = $exp["telnot3"];
                            }
                        }
                        if (trim($exp["emailcom"])) {
                            $resultado["inscripciones"][$j]["emails"][$exp["emailcom"]] = $exp["emailcom"];
                        }
                        if (trim($exp["emailcom2"])) {
                            $resultado["inscripciones"][$j]["emails"][$exp["emailcom2"]] = $exp["emailcom2"];
                        }
                        if (trim($exp["emailcom3"])) {
                            $resultado["inscripciones"][$j]["emails"][$exp["emailcom3"]] = $exp["emailcom3"];
                        }
                        if (trim($exp["emailnot"])) {
                            $resultado["inscripciones"][$j]["emails"][$exp["emailnot"]] = $exp["emailnot"];
                        }
                    }
                }

                // recupera números y emails anteriores
                if ($resultado["inscripciones"][$i]["mat"] != '') {
                    $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim($resultado["inscripciones"][$j]["mat"], "0") . "'", "id");
                    if ($exp && empty($exp)) {
                        if (trim($exp["campo='EMAILCOM-ANTERIOR"])) {
                            $resultado["inscripciones"][$j]["emails"][trim($exp["contenido"])] = trim($exp["contenido"]);
                        }
                        if (trim($exp["campo='EMAILNOT-ANTERIOR"])) {
                            $resultado["inscripciones"][$j]["emails"][trim($exp["contenido"])] = trim($exp["contenido"]);
                        }
                        if (trim($exp["campo='CELCOM-ANTERIOR"])) {
                            if (strlen(trim($exp["contenido"])) == 10 && substr(trim($exp["contenido"]), 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][trim($exp["contenido"])] = trim($exp["contenido"]);
                            }
                        }
                        if (trim($exp["campo='CELNOT-ANTERIOR"])) {
                            if (strlen(trim($exp["contenido"])) == 10 && substr(trim($exp["contenido"]), 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][trim($exp["contenido"])] = trim($exp["contenido"]);
                            }
                        }
                    }
                }

                // Recupera números telefonicos y emails actuales - proponentes
                if ($resultado["inscripciones"][$j]["pro"] != '') {
                    $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $resultado["inscripciones"][$j]["pro"] . "'");
                    if ($exp && empty($exp)) {
                        if (trim($exp["telcom1"]) != '') {
                            if (strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                            }
                        }
                        if (trim($exp["telcom2"])) {
                            if (strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                            }
                        }
                        if (trim($exp["celcom"])) {
                            if (strlen($exp["celcom"]) == 10 && substr($exp["celcom"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["celcom"]] = $exp["celcom"];
                            }
                        }
                        if (trim($exp["telnot"])) {
                            if (strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telnot"]] = $exp["telnot"];
                            }
                        }
                        if (trim($exp["telnot2"])) {
                            if (strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                            }
                        }
                        if (trim($exp["celnot"])) {
                            if (strlen($exp["celnot"]) == 10 && substr($exp["celnot"], 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][$exp["celnot"]] = $exp["celnot"];
                            }
                        }
                        if (trim($exp["emailcom"])) {
                            $resultado["inscripciones"][$j]["emails"][$exp["emailcom"]] = $exp["emailcom"];
                        }
                        if (trim($exp["emailnot"])) {
                            $resultado["inscripciones"][$j]["emails"][$exp["emailnot"]] = $exp["emailnot"];
                        }
                    }
                }

                // recupera números y emails anteriores
                if ($resultado["inscripciones"][$j]["pro"] != '') {
                    $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='032' and registro='" . ltrim($resultado["inscripciones"][$j]["pro"], "0") . "'", "id");
                    if ($exp && empty($exp)) {
                        if (trim($exp["campo='EMAILCOM-ANTERIOR"])) {
                            $resultado["inscripciones"][$j]["emails"][trim($exp["contenido"])] = trim($exp["contenido"]);
                        }
                        if (trim($exp["campo='EMAILNOT-ANTERIOR"])) {
                            $resultado["inscripciones"][$j]["emails"][trim($exp["contenido"])] = trim($exp["contenido"]);
                        }
                        if (trim($exp["campo='CELCOM-ANTERIOR"])) {
                            if (strlen(trim($exp["contenido"])) == 10 && substr(trim($exp["contenido"]), 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][trim($exp["contenido"])] = trim($exp["contenido"]);
                            }
                        }
                        if (trim($exp["campo='CELNOT-ANTERIOR"])) {
                            if (strlen(trim($exp["contenido"])) == 10 && substr(trim($exp["contenido"]), 0, 1) == '3') {
                                $resultado["inscripciones"][$j]["telefonos"][trim($exp["contenido"])] = trim($exp["contenido"]);
                            }
                        }
                    }
                }
            }
            unset($reg);
            unset($reg1);
        }

        return $resultado;
    }

}

?>
