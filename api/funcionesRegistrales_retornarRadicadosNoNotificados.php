<?php

class funcionesRegistrales_retornarRadicadosNoNotificados {

    public static function retornarRadicadosNoNotificados($mysqli, $cri, $recibo = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        $resultado = array(
            'codigoError' => '0000',
            'msgError' => '',
            'recibos' => array()
        );

        if ($cri == 'L') {
            $query = "fecha='" . date("Ymd") . "' and estadoemail='0' and estadosms='0' and tipogasto IN ('0','4',6','8')";
            $reg1 = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $query, "recibo");
            if ($reg1 && !empty($reg1)) {
                $i = 0;
                foreach ($reg1 as $reg) {
                    $i++;
                    $resultado["recibos"][$i]["nrec"] = $reg["recibo"];
                    $resultado["recibos"][$i]["cba"] = $reg["codigobarras"];
                    $resultado["recibos"][$i]["ope"] = $reg["operacion"];
                    $resultado["recibos"][$i]["fec"] = $reg["fecha"];
                    $resultado["recibos"][$i]["hor"] = $reg["hora"];
                    $resultado["recibos"][$i]["ide"] = $reg["identificacion"];
                    $resultado["recibos"][$i]["nom"] = $reg["razonsocial"];
                    $resultado["recibos"][$i]["mat"] = '';
                    $resultado["recibos"][$i]["pro"] = '';
                    $resultado["recibos"][$i]["ser"] = '';
                    $resultado["recibos"][$i]["valor"] = $reg["valorneto"];
                    $resultado["recibos"][$i]["emails"] = array();
                    $resultado["recibos"][$i]["telefonos"] = array();

                    $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $reg["recibo"] . "'", "secuencia");
                    $j = 0;
                    if ($arrTem && !empty($arrTem)) {
                        foreach ($arrTem as $tx) {
                            $j++;
                            if ($j == 1) {
                                $resultado["recibos"][$i]["mat"] = $tx["matricula"];
                                $resultado["recibos"][$i]["pro"] = $tx["proponente"];
                                $resultado["recibos"][$i]["ser"] = $tx["idservicio"];
                            }
                        }
                    }

                    if (trim($reg["email"]) != '') {
                        $resultado["recibos"][$i]["emails"][trim($reg["email"])] = trim($reg["email"]);
                    }

                    if (
                            trim($reg["telefono1"]) != '' &&
                            strlen(trim($reg["telefono1"])) == 10 &&
                            substr(trim($reg["telefono1"]), 0, 1) == '3'
                    ) {
                        $resultado["recibos"][$i]["telefonos"][trim($reg["telefono1"])] = trim($reg["telefono1"]);
                    }
                    if (
                            trim($reg["telefono2"]) != '' &&
                            strlen(trim($reg["telefono2"])) == 10 &&
                            substr(trim($reg["telefono2"]), 0, 1) == '3'
                    ) {
                        $resultado["recibos"][$i]["telefonos"][trim($reg["telefono2"])] = trim($reg["telefono2"]);
                    }

                    // Recupera números telefonicos y emails actuales - mercantil
                    if ($resultado["recibos"][$i]["mat"] != '') {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $resultado["recibos"][$i]["mat"] . "'");
                        if ($exp && empty($exp)) {
                            if (trim($exp["telcom1"]) != '') {
                                if (strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                                }
                            }
                            if (trim($exp["telcom2"])) {
                                if (strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                                }
                            }
                            if (trim($exp["telcom3"])) {
                                if (strlen($exp["telcom3"]) == 10 && substr($exp["telcom3"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telcom3"]] = $exp["telcom3"];
                                }
                            }
                            if (trim($exp["telnot"])) {
                                if (strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telnot"]] = $exp["telnot"];
                                }
                            }
                            if (trim($exp["telnot2"])) {
                                if (strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                                }
                            }
                            if (trim($exp["telnot3"])) {
                                if (strlen($exp["telnot3"]) == 10 && substr($exp["telnot3"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telnot3"]] = $exp["telnot3"];
                                }
                            }
                            if (trim($exp["emailcom"])) {
                                $resultado["recibos"][$i]["emails"][$exp["emailcom"]] = $exp["emailcom"];
                            }
                            if (trim($exp["emailcom2"])) {
                                $resultado["recibos"][$i]["emails"][$exp["emailcom2"]] = $exp["emailcom2"];
                            }
                            if (trim($exp["emailcom3"])) {
                                $resultado["recibos"][$i]["emails"][$exp["emailcom3"]] = $exp["emailcom3"];
                            }
                            if (trim($exp["emailnot"])) {
                                $resultado["recibos"][$i]["emails"][$exp["emailnot"]] = $exp["emailnot"];
                            }
                        }
                    }

                    // recupera números y emails anteriores
                    if ($resultado["recibos"][$i]["mat"] != '') {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim($resultado["recibos"][$i]["mat"], "0") . "'", "id");
                        if ($exp && empty($exp)) {
                            if (trim($exp["campo='EMAILCOM-ANTERIOR"])) {
                                $resultado["recibos"][$i]["emails"][trim($exp["contenido"])] = trim($exp["contenido"]);
                            }
                            if (trim($exp["campo='EMAILNOT-ANTERIOR"])) {
                                $resultado["recibos"][$i]["emails"][trim($exp["contenido"])] = trim($exp["contenido"]);
                            }
                            if (trim($exp["campo='CELCOM-ANTERIOR"])) {
                                if (strlen(trim($exp["contenido"])) == 10 && substr(trim($exp["contenido"]), 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][trim($exp["contenido"])] = trim($exp["contenido"]);
                                }
                            }
                            if (trim($exp["campo='CELNOT-ANTERIOR"])) {
                                if (strlen(trim($exp["contenido"])) == 10 && substr(trim($exp["contenido"]), 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][trim($exp["contenido"])] = trim($exp["contenido"]);
                                }
                            }
                        }
                    }

                    // Recupera números telefonicos y emails actuales - proponentes
                    if ($resultado["recibos"][$i]["pro"] != '') {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $resultado["recibos"][$i]["pro"] . "'");
                        if ($exp && empty($exp)) {
                            if (trim($exp["telcom1"]) != '') {
                                if (strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                                }
                            }
                            if (trim($exp["telcom2"])) {
                                if (strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                                }
                            }
                            if (trim($exp["celcom"])) {
                                if (strlen($exp["celcom"]) == 10 && substr($exp["celcom"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["celcom"]] = $exp["celcom"];
                                }
                            }
                            if (trim($exp["telnot"])) {
                                if (strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telnot"]] = $exp["telnot"];
                                }
                            }
                            if (trim($exp["telnot2"])) {
                                if (strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                                }
                            }
                            if (trim($exp["celnot"])) {
                                if (strlen($exp["celnot"]) == 10 && substr($exp["celnot"], 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][$exp["celnot"]] = $exp["celnot"];
                                }
                            }
                            if (trim($exp["emailcom"])) {
                                $resultado["recibos"][$i]["emails"][$exp["emailcom"]] = $exp["emailcom"];
                            }
                            if (trim($exp["emailnot"])) {
                                $resultado["recibos"][$i]["emails"][$exp["emailnot"]] = $exp["emailnot"];
                            }
                        }
                    }

                    // recupera números y emails anteriores
                    if ($resultado["recibos"][$i]["mat"] != '') {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='032' and registro='" . ltrim($resultado["recibos"][$i]["pro"], "0") . "'", "id");
                        if ($exp && empty($exp)) {
                            if (trim($exp["campo='EMAILCOM-ANTERIOR"])) {
                                $resultado["recibos"][$i]["emails"][trim($exp["contenido"])] = trim($exp["contenido"]);
                            }
                            if (trim($exp["campo='EMAILNOT-ANTERIOR"])) {
                                $resultado["recibos"][$i]["emails"][trim($exp["contenido"])] = trim($exp["contenido"]);
                            }
                            if (trim($exp["campo='CELCOM-ANTERIOR"])) {
                                if (strlen(trim($exp["contenido"])) == 10 && substr(trim($exp["contenido"]), 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][trim($exp["contenido"])] = trim($exp["contenido"]);
                                }
                            }
                            if (trim($exp["campo='CELNOT-ANTERIOR"])) {
                                if (strlen(trim($exp["contenido"])) == 10 && substr(trim($exp["contenido"]), 0, 1) == '3') {
                                    $resultado["recibos"][$i]["telefonos"][trim($exp["contenido"])] = trim($exp["contenido"]);
                                }
                            }
                        }
                    }
                }
                unset($reg);
                unset($reg1);
                unset($dom);
            }
        }

        $_SESSION["sirep"] = array();
        return $resultado;
    }


}

?>
