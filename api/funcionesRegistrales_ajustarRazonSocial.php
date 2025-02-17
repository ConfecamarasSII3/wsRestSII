<?php

class funcionesRegistrales_ajustarRazonSocial {

    public static function ajustarRazonSocial($mysqli, $r, $inscs, $arrActos) {
//
        $enliquidacion = 'no';
        $enreestructuracion = 'no';
        $enreorganizacion = 'no';
        $enliquidacionjudicial = 'no';
        $enliquidacionforsoza = 'no';
        $enrecuperacion = 'no';

//
        if (isset($r["complementorazonsocial"])) {
            $r["complementorazonsocial"] = stripslashes(\funcionesGenerales::restaurarEspeciales($r["complementorazonsocial"]));
        } else {
            $r["complementorazonsocial"] = '';
        }
        $r["razonsocial"] = str_replace("¥", "Ñ", stripslashes(\funcionesGenerales::restaurarEspeciales($r["razonsocial"])));

//
        $salida = array();
        $salida ["razonsocial"] = \funcionesGenerales::borrarpalabrasAutomaticas($r["razonsocial"], $r["complementorazonsocial"]);
        $salida ["fecdisolucion"] = '';
        $salida ["fecliquidacion"] = '';

//
        if ($r["ctrestmatricula"] != 'MC' && $r["ctrestmatricula"] != 'IC' && $r["ctrestmatricula"] != 'MF' && $r["ctrestmatricula"] != 'IF' && $r["ctrestmatricula"] != 'NA' && $r["ctrestmatricula"] != 'NM') {
            if ($r["organizacion"] == '01' || ($r["organizacion"] > '02' && $r["categoria"] == '1')) {
                if ($inscs && !empty($inscs)) {
                    foreach ($inscs as $insc) {
                        $ind = $insc["libro"] . '-' . $insc["acto"];
                        if (isset($arrActos[$ind])) {
                            if ($arrActos[$ind]["idgrupoacto"] == '010') {
                                $salida ["fecliquidacion"] = $insc["fecharegistro"];
                            }
                            if ($arrActos[$ind]["textoenliquidacion"] == 'S') {
                                $salida ["fecdisolucion"] = $insc["fecharegistro"];
                                $enliquidacion = 'si';
                                $enreestructuracion = 'no';
                                $enreorganizacion = 'no';
                                $enliquidacionjudicial = 'no';
                                $enliquidacionforsoza = 'no';
                                $enrecuperacion = 'no';
                            }
                            if ($arrActos[$ind]["textoenliquidacion"] == 'L') {
                                $salida ["fecdisolucion"] = $insc["fecharegistro"];
                                $enliquidacionjudicial = 'si';
                                $enliquidacion = 'no';
                                $enreorganizacion = 'no';
                                $enreestructuracion = 'no';
                                $enliquidacionforsoza = 'no';
                                $enrecuperacion = 'no';
                            }
                            if ($arrActos[$ind]["textoenliquidacion"] == 'F') {
                                $salida ["fecdisolucion"] = $insc["fecharegistro"];
                                $enliquidacionforsoza = 'si';
                                $enliquidacion = 'no';
                                $enreorganizacion = 'no';
                                $enliquidacionjudicial = 'no';
                                $enreestructuracion = 'no';
                                $enrecuperacion = 'no';
                            }

                            if ($arrActos[$ind]["textoenliquidacion"] == 'Q') {
                                $salida ["fecdisolucion"] = '';
                                $enliquidacion = 'no';
                                $enliquidacionjudicial = 'no';
                                $enreestructuracion = 'no';
                                $enreorganizacion = 'no';
                                $enliquidacionforsoza = 'no';
                                $enrecuperacion = 'no';
                            }

                            if ($arrActos[$ind]["textoenreestructuracion"] == 'S') {
                                $salida ["fecdisolucion"] = '';
                                $enliquidacion = 'no';
                                $enliquidacionjudicial = 'no';
                                $enreestructuracion = 'si';
                                $enreorganizacion = 'no';
                                $enrecuperacion = 'no';
                            }
                            if ($arrActos[$ind]["textoenreestructuracion"] == 'L') {
                                $salida ["fecdisolucion"] = '';
                                $enliquidacion = 'no';
                                $enliquidacionjudicial = 'si';
                                $enreestructuracion = 'no';
                                $enreorganizacion = 'no';
                                $enrecuperacion = 'no';
                            }
                            if ($arrActos[$ind]["textoenreestructuracion"] == 'R') {
                                $salida ["fecdisolucion"] = '';
                                $enliquidacion = 'no';
                                $enliquidacionjudicial = 'no';
                                $enreestructuracion = 'no';
                                $enreorganizacion = 'si';
                                $enrecuperacion = 'no';
                            }
                            if ($arrActos[$ind]["textoenreestructuracion"] == 'E') {
                                $salida ["fecdisolucion"] = '';
                                $enliquidacion = 'no';
                                $enliquidacionjudicial = 'no';
                                $enreestructuracion = 'no';
                                $enreorganizacion = 'no';
                                $enrecuperacion = 'si';
                            }
                            if ($arrActos[$ind]["textoenreestructuracion"] == 'Q') {
                                $salida ["fecdisolucion"] = '';
                                $enliquidacion = 'no';
                                $enliquidacionjudicial = 'no';
                                $enreestructuracion = 'no';
                                $enreorganizacion = 'no';
                                $enrecuperacion = 'no';
                            }
                        }
                    }
                }
            }

//
            $ya = 'no';
            if ($enliquidacion == 'si' ||
                    $enreestructuracion == 'si' ||
                    $enreorganizacion == 'si' ||
                    $enliquidacionjudicial == 'si' ||
                    $enliquidacionforsoza == 'si' ||
                    $enrecuperacion == 'si'
            ) {
                if ($r["complementorazonsocial"] != '') {
                    $salida["razonsocial"] .= ' ' . $r["complementorazonsocial"];
                    $ya = 'si';
                }
            }
            if ($ya == 'no') {
                if ($enliquidacion == 'si') {
                    $salida["razonsocial"] .= ' EN LIQUIDACION';
                    $ya = 'si';
                }
                if ($enreestructuracion == 'si') {
                    $salida["razonsocial"] .= ' EN REESTRUCTURACION';
                    $ya = 'si';
                }
                if ($enreorganizacion == 'si') {
                    $salida["razonsocial"] .= ' EN REORGANIZACION';
                    $ya = 'si';
                }
                if ($enliquidacionjudicial == 'si') {
                    $salida["razonsocial"] .= ' EN LIQUIDACION JUDICIAL';
                    $ya = 'si';
                }
                if ($enliquidacionforsoza == 'si') {
                    $salida["razonsocial"] .= ' EN LIQUIDACION FORZOSA';
                    $ya = 'si';
                }
                if ($enrecuperacion == 'si') {
                    $salida["razonsocial"] .= ' EN RECUPERACION EMPRESARIAL';
                    $ya = 'si';
                }
                
            }
            if ($ya == 'no') {
                if (trim($r["fecvigencia"]) != '' && $r["fecvigencia"] != '99999999') {
                    if (trim($r["fecvigencia"]) < date("Ymd")) {
                        $salida ["fecdisolucion"] = $r["fecvigencia"];
                        $salida["razonsocial"] .= ' EN LIQUIDACION';
                        $ya = 'si';
                    }
                }
            }
        }

        return $salida;
    }
}

?>
