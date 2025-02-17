<?php

class funcionesRegistrales_buscarRangoTarifa {

    public static function buscarRangoTarifa($mysqli, $idservicio = '', $ano = 0, $base = 0, $tipotarifa = 'tarifa', $idclasevalor = '') {
        $nameLog = 'buscarRangoTarifa_' . date ("Ymd");
        $retornar = 0;

        //
        if ($ano < 1993) {
            $ano = 1993;
        }

        //
        if ($idclasevalor == '') {
            $idclasevalor = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $idservicio . "'", "idclasevalor");
        }

        //
        $fliquidar = $ano . date("md");

        //
        $feinicio045 = '';
        $decreto045 = 'no';
        if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
            $feinicio045 = '99999999';
        } else {
            $feinicio045 = FECHA_INICIO_DECRETO_045;
        }
        if ($fliquidar  >= $feinicio045) {
            $decreto045 = 'si';
        }

        //
        if ($fliquidar < $feinicio045) {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_tarifas', "idservicio='" . $idservicio . "' and ano='" . $ano . "'", "idrango");
            if ($result === false || empty($result)) {
                $retornar = false;
            } else {
                foreach ($result as $res) {
                    if (doubleval($res["topeminimo"]) <= doubleval($base) && doubleval($res["topemaximo"]) >= doubleval($base)) {
                        if ($tipotarifa == 'tarifa') {
                            $retornar = doubleval($res["tarifa"]);
                        }
                        if ($tipotarifa == 'tarifapnat') {
                            $retornar = doubleval($res["tarifapnat"]);
                        }
                        if ($tipotarifa == 'tarifapjur') {
                            $retornar = doubleval($res["tarifapjur"]);
                        }
                    }
                }
            }
        }

        if ($fliquidar >= $feinicio045) {
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $idservicio . "'");
            $uvbanterior = \funcionesGenerales::retornarUvbActual($mysqli, $ano - 1);
            $uvb = \funcionesGenerales::retornarUvbActual($mysqli, $ano);
            $smmlv = \funcionesGenerales::retornarSalarioMinimoActual($mysqli, $ano);

            //
            if ($serv["tipotarifa"] == 'RMER-MA') {
                $valbase = $base / $uvb;
                $result = retornarRegistrosMysqliApi($mysqli, 'mreg_tarifas_base_uvb', "tipo='" . $serv["tipotarifa"] . "'", "rango");
                if ($result === false || empty($result)) {
                    $retornar = false;
                } else {
                    foreach ($result as $res) {
                        if (doubleval($res["min"]) < doubleval($valbase) && doubleval($res["max"]) >= doubleval($valbase)) {
                            $divisor = 0;
                            if ($res["factordivisor"] == 'UVB') {
                                $divisor = $res["divisor"] * $uvb;
                            } 
                            if ($res["factordivisor"] == 'UNO') {
                                $divisor = $res["divisor"];
                            } 
                            $retornar = ($res["baseppal"] + $res["factor"] * ($base - $res["restar"] * $uvb) / $divisor) * $uvb;
                            if ($res["limite"] != 0) {
                                if ($retornar > $res["tarifalimite"] * $uvb) {
                                    $retornar = $res["tarifalimite"] * $uvb;
                                }
                            }
                            if ($retornar < $smmlv * 0.03) {
                                $retornar = \funcionesGenerales::redondear100($retornar);
                            } else {
                                $retornar = \funcionesGenerales::redondear1000($retornar);
                            }
                        }
                    }
                }
            }

            //
            if ($serv["tipotarifa"] == 'RMER-RN') {
                $valbase = $base / $uvbanterior;
                $result = retornarRegistrosMysqliApi($mysqli, 'mreg_tarifas_base_uvb', "tipo='" . $serv["tipotarifa"] . "'", "rango");
                if ($result === false || empty($result)) {
                    $retornar = false;
                } else {
                    foreach ($result as $res) {
                        if (doubleval($res["min"]) < doubleval($valbase) && doubleval($res["max"]) >= doubleval($valbase)) {
                            $divisor = 0;
                            if ($res["factordivisor"] == 'UVB') {
                                $divisor = $res["divisor"] * $uvb;
                            } 
                            if ($res["factordivisor"] == 'UNO') {
                                $divisor = $res["divisor"];
                            }
                            $retornar = ($res["baseppal"] + $res["factor"] * ($base - $res["restar"] * $uvb) / $divisor) * $uvb;
                            if ($res["tarifalimite"] != 0) {
                                if ($retornar > $res["tarifalimite"] * $uvb) {
                                    $retornar = $res["tarifalimite"] * $uvb;
                                }
                            }
                            if ($retornar < $smmlv * 0.03) {
                                $retornar = \funcionesGenerales::redondear100($retornar);
                            } else {
                                $retornar = \funcionesGenerales::redondear1000($retornar);
                            }
                        }
                    }
                }
            }


            // Liquida establecicmientos por activos del propietario - locales
            if ($serv["tipotarifa"] == 'RMER-ES1' || $serv["tipotarifa"] == 'RMER-ES2') {
                $valbase = $base / $uvb;
                $result = retornarRegistrosMysqliApi($mysqli, 'mreg_tarifas_base_uvb', "tipo='RMER-ES1'", "rango");
                if ($result === false || empty($result)) {
                    $retornar = false;
                } else {
                    foreach ($result as $res) {
                        if (doubleval($res["min"]) <= doubleval($valbase) && doubleval($res["max"]) >= doubleval($valbase)) {
                            if ($serv["tipotarifa"] == 'RMER-ES1') {
                                $retornar = $res["baselocal"] * $uvb;
                                if ($retornar < $smmlv * 0.03) {
                                    $retornar = \funcionesGenerales::redondear100($retornar);
                                } else {
                                    $retornar = \funcionesGenerales::redondear1000($retornar);
                                }
                            }
                            if ($serv["tipotarifa"] == 'RMER-ES2') {
                                $retornar = $res["baseforaneo"] * $uvb;
                                if ($retornar < $smmlv * 0.03) {
                                    $retornar = \funcionesGenerales::redondear100($retornar);
                                } else {
                                    $retornar = \funcionesGenerales::redondear1000($retornar);
                                }
                            }
                        }
                    }
                }
                $txt = 'Servicio : ' . $idservicio . "\r\n";
                $txt .= 'base : ' . $base . "\r\n";
                $txt .= 'Uvb : ' . $uvb . "\r\n";
                $txt .= 'Valor liquidado : ' . $retornar . "\r\n";
                \logApi::general2($nameLog, $idservicio, $txt);
            }
        }
        return $retornar;
    }

}

?>
