<?php

class funcionesRegistrales_emailsCeluaresAsociadosRadicacion {

    public static function emailsCeluaresAsociadosRadicacion($mysqli, $codbarras) {

        $matriculas = array();
        $servicio = '';
        $proponente = '';
        $salida = array();
        $salida["emails"] = array();
        $salida["telefonos"] = array();
        $reg = false;
        $notificar = 'si';

//
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $codbarras . "'");
        if ($temx && !empty($temx)) {
            if ($temx["recibo"] != '') {
                $query = "recibo='" . $temx["recibo"] . "' and tipogasto IN ('0','4','6','8')";
                $reg = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', $query);
            }
        }

//
        if ($reg && !empty($reg)) {
            $arrTemCB = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $reg["codigobarras"] . "'");
            if ($notificar == 'si') {
//
                $salida["emails"][$reg["email"]] = $reg["email"];
                if ($reg["telefono1"] != '' && strlen($reg["telefono1"]) == 10 && substr($reg["telefono1"], 0, 1) == '3') {
                    $salida["telefonos"][$reg["telefono1"]] = $reg["telefono1"];
                }
                if ($reg["telefono2"] != '' && strlen($reg["telefono2"]) == 10 && substr($reg["telefono2"], 0, 1) == '3') {
                    $salida["telefonos"][$reg["telefono2"]] = $reg["telefono2"];
                }

//
                $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $reg["recibo"] . "'", "secuencia");
                $j = 0;
                if ($arrTem && !empty($arrTem)) {
                    foreach ($arrTem as $tx) {
                        $j++;
                        if ($j == 1) {
                            if ($tx["matricula"] != '' && substr($tx["matricula"], 0, 5) != 'NUEVA') {
                                $matriculas[$tx["matricula"]] = $tx["matricula"];
                            }
                            if ($tx["proponente"] != '') {
                                $proponente = $tx["proponente"];
                            }
                            $servicio = $tx["idservicio"];
                        }
                    }
                }

// ************************************************************************************************** //
// 2017-12-15: JINT: Se notifica sin importar que sea a los emails del recibo y del código de barras
// ************************************************************************************************** //
// $arrTemCB = retornarRegistroMysqli($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $reg["codigobarras"] . "'");
                if ($arrTemCB["emailnot1"] != '') {
                    $salida["emails"][$arrTemCB["emailnot1"]] = $arrTemCB["emailnot1"];
                }
                if ($arrTemCB["emailnot2"] != '') {
                    $salida["emails"][$arrTemCB["emailnot2"]] = $arrTemCB["emailnot2"];
                }
                if ($arrTemCB["emailnot3"] != '') {
                    $salida["emails"][$arrTemCB["emailnot3"]] = $arrTemCB["emailnot3"];
                }
                if ($arrTemCB["celnot1"] != '' && strlen($arrTemCB["celnot1"]) == 10 && substr($arrTemCB["celnot1"], 0, 1) == '3') {
                    $salida["telefonos"][$arrTemCB["celnot1"]] = $arrTemCB["celnot1"];
                }
                if ($arrTemCB["celnot2"] != '' && strlen($arrTemCB["celnot2"]) == 10 && substr($arrTemCB["celnot2"], 0, 1) == '3') {
                    $salida["telefonos"][$arrTemCB["celnot2"]] = $arrTemCB["celnot2"];
                }
                if ($arrTemCB["celnot3"] != '' && strlen($arrTemCB["celnot3"]) == 10 && substr($arrTemCB["celnot3"], 0, 1) == '3') {
                    $salida["telefonos"][$arrTemCB["celnot3"]] = $arrTemCB["celnot3"];
                }

// *********************************************************************************** //
// Siempre y cuando no sea un embargo
// *********************************************************************************** //            
                if (($arrTemCB["actoreparto"] != '07') && ($arrTemCB["actoreparto"] != '29')) {

// *********************************************************************************** //
// Busca cada expediente
// *********************************************************************************** //            
                    if (!empty($matriculas)) {
                        foreach ($matriculas as $m) {
                            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");

// *********************************************************************************** //
// Localiza emails y celulares actuales
// *********************************************************************************** //                                    
                            if ($exp && !empty($exp)) {
                                if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                                    $salida["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                                }
                                if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                                    $salida["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                                }
                                if (trim($exp["telcom3"]) != '' && strlen($exp["telcom3"]) == 10 && substr($exp["telcom3"], 0, 1) == '3') {
                                    $salida["telefonos"][$exp["telcom3"]] = $exp["telcom3"];
                                }
                                if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                                    $salida["telefonos"][$exp["telnot"]] = $exp["telnot"];
                                }
                                if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                                    $salida["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                                }
                                if (trim($exp["telnot3"]) != '' && strlen($exp["telnot3"]) == 10 && substr($exp["telnot3"], 0, 1) == '3') {
                                    $salida["telefonos"][$exp["telnot3"]] = $exp["telnot3"];
                                }
                                if (trim($exp["emailcom"]) != '') {
                                    $salida["emails"][$exp["emailcom"]] = $exp["emailcom"];
                                }
                                if (trim($exp["emailcom2"]) != '') {
                                    $salida["emails"][$exp["emailcom2"]] = $exp["emailcom2"];
                                }
                                if (trim($exp["emailcom3"]) != '') {
                                    $salida["emails"][$exp["emailcom3"]] = $exp["emailcom3"];
                                }
                                if (trim($exp["emailnot"]) != '') {
                                    $salida["emails"][$exp["emailnot"]] = $exp["emailnot"];
                                }
                            }

// *********************************************************************************** //
// Localiza emails y celulares anteriores migrados del SIREP
// *********************************************************************************** //                                                            
                            $exps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim($m, "0") . "'", "id");
                            if ($exps && !empty($exps)) {
                                foreach ($exps as $exps1) {
                                    if (trim($exps1["campo"]) == "EMAILCOM-ANTERIOR") {
                                        $salida["emails"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                    }
                                    if (trim($exps1["campo"]) == "EMAILNOT-ANTERIOR") {
                                        $salida["emails"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                    }
                                    if (trim($exps1["campo="]) == "CELCOM-ANTERIOR") {
                                        if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                            $salida["telefonos"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                        }
                                    }
                                    if (trim($exps1["campo"]) == "CELNOT-ANTERIOR") {
                                        if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                            $salida["telefonos"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                        }
                                    }
                                }
                            }

// *********************************************************************************** //
// Localiza emails y celulares modificados en mreg_campos_historicos_AAAA
// *********************************************************************************** //                                                            
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom1');
                            foreach ($d as $d1) {
                                if (trim($d1) != '' && strlen($d1) == 10 && strlen($d1, 0, 1) == '3') {
                                    $salida["telefonos"][trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom2');
                            foreach ($d as $d1) {
                                if (trim($d1) != '' && strlen($d1) == 10 && strlen($d1, 0, 1) == '3') {
                                    $salida["telefonos"][trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom3');
                            foreach ($d as $d1) {
                                if (trim($d1) != '' && strlen($d1) == 10 && strlen($d1, 0, 1) == '3') {
                                    $salida["telefonos"][trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telnot');
                            foreach ($d as $d1) {
                                if (trim($d1) != '' && strlen($d1) == 10 && strlen($d1, 0, 1) == '3') {
                                    $salida["telefonos"][trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'emailcom');
                            foreach ($d as $d1) {
                                $salida["emails"][trim($d1)] = trim($d1);
                            }
                            $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'emailnot');
                            foreach ($d as $d1) {
                                $salida["emails"][trim($d1)] = trim($d1);
                            }
                        }
                    }
                }

// Recupera números telefonicos y emails actuales - proponentes
                if ($proponente != '') {
                    $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $proponente . "'");
                    if ($exp && !empty($exp)) {
                        if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                            $salida["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                        }
                        if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                            $salida["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                        }
                        if (trim($exp["celcom"]) != '' && strlen($exp["celcom"]) == 10 && substr($exp["celcom"], 0, 1) == '3') {
                            $salida["telefonos"][$exp["celcom"]] = $exp["celcom"];
                        }
                        if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                            $salida["telefonos"][$exp["telnot"]] = $exp["telnot"];
                        }
                        if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                            $salida["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                        }
                        if (trim($exp["celnot"]) != '' && strlen($exp["celnot"]) == 10 && substr($exp["celnot"], 0, 1) == '3') {
                            $salida["telefonos"][$exp["celnot"]] = $exp["celnot"];
                        }
                        if (trim($exp["emailcom"]) != '') {
                            $salida["emails"][$exp["emailcom"]] = $exp["emailcom"];
                        }
                        if (trim($exp["emailnot"]) != '') {
                            $salida["emails"][$exp["emailnot"]] = $exp["emailnot"];
                        }
                    }
                }

// recupera números y emails anteriores (proponentes)
                if ($proponente != '') {
                    $exps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='032' and registro='" . ltrim($proponente, "0") . "'", "id");
                    if ($exps && !empty($exps)) {
                        foreach ($exps as $exps1) {
                            if (trim($exps1["campo"]) == "EMAILCOM-ANTERIOR") {
                                $salida["emails"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                            }
                            if (trim($exps1["campo"]) == "EMAILNOT-ANTERIOR") {
                                $salida["emails"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                            }
                            if (trim($exps1["campo"]) == "CELCOM-ANTERIOR") {
                                if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                    $salida["telefonos"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                }
                            }
                            if (trim($exps1["campo"]) == "CELNOT-ANTERIOR") {
                                if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                    $salida["telefonos"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                }
                            }
                        }
                    }
                }

                unset($reg);
            }
        }
        return $salida;
    }
}

?>
