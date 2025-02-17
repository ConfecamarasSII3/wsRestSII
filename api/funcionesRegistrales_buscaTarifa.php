<?php

class funcionesRegistrales_buscaTarifa {

    /**
     * 
     * @param type $mysqli
     * @param type $idservicio
     * @param int $ano
     * @param type $cantidad
     * @param type $base
     * @param type $tipotarifa
     * @param type $actprop
     * @param type $canesttot
     * @param type $matricula
     * @param type $descuentoaplicable
     * @return bool
     */
    public static function buscaTarifa($mysqli, $idservicio = '', $ano = 0, $cantidad = 0, $base = 0, $tipotarifa = 'tarifa', $actprop = 0, $canesttot = 0, $matricula = '', $descuentoaplicable = '') {

        $tarifa = 0;

        //
        if ($ano < 1993) {
            $ano = 1993;
        }

        $arrServicio = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $idservicio . "'");
        if (!$arrServicio) {
            return false;
        }
        if (empty($arrServicio)) {
            return false;
        }

        //
        $feinicio045 = '';
        $decreto045 = 'no';
        if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
            $feinicio045 = '99999999';
        } else {
            $feinicio045 = FECHA_INICIO_DECRETO_045;
        }
        if ($ano . date("md") >= $feinicio045) {
            $decreto045 = 'si';
        }

        // Liquida por valor unico
        if ($arrServicio["idclasevalor"] == '1') {
            $tarifa = \funcionesRegistrales::buscarTarifaValor($mysqli, $idservicio);
        }
        // Liquida por rango de tarifas
        if ($arrServicio["idclasevalor"] == '2') {
            $tarifa = \funcionesRegistrales::buscarRangoTarifa($mysqli, $idservicio, $ano, $base, $tipotarifa, $arrServicio["idclasevalor"]);
        }

        // monto limite
        if ($arrServicio["idclasevalor"] == '3') {
            $tarifa = $base;
        }

        // Liquida por porcentaje
        if ($arrServicio["idclasevalor"] == '4') {
            $tarifa = $arrServicio["valorservicio"] * $base / 100;
        }

        // Liquida por cálculo
        if ($arrServicio["idclasevalor"] == '5') {
            $tarifa = $arrServicio["valorservicio"] * $base / 100;
        }

        $tarifa = $tarifa * $cantidad;

        // Evalua descuentos 
        if ($descuentoaplicable != '' && $descuentoaplicable != '00') {
            $desc = retornarRegistroMysqliApi($mysqli, 'mreg_servicios_descuentos', "idservicio='" . $idservicio . "' and iddescuento='" . sprintf("%02s", $descuentoaplicable) . "'");
            if ($desc && !empty($desc)) {
                if ($desc["porcentaje"] != 0) {
                    $tarifa = $tarifa - ($tarifa * $desc["porcentaje"]) / 100;
                } else {
                    $tarifa = $tarifa - $desc["valor"];
                    if ($tarifa < 0) {
                        $tarifa = 0;
                    }
                }
            }
        } else {
            if ($matricula != '') {                
                $iddescuento = '';
                if (CODIGO_EMPRESA == '55') {
                    $iddescuento = '04';
                }
                if (CODIGO_EMPRESA == '23') {
                    $iddescuento = '01';
                }
                if ($iddescuento != '') {
                    $desc = retornarRegistroMysqliApi($mysqli, 'mreg_servicios_descuentos', "idservicio='" . $idservicio . "' and iddescuento='" . $iddescuento . "'");
                    if ($desc && !empty($desc)) {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matricula . "'", "matricula,ctrestmatricula,ctrafiliacion");
                        if ($exp && !empty($exp) && $exp["ctrafiliacion"] == '1' && $exp["ctrestmatricula"] == 'MA') {
                            $tarifa = $tarifa - ($tarifa * $desc["porcentaje"]) / 100;
                        }
                    }
                }
            }
        }

        //
        switch ($arrServicio["redondeo"]) {

            case "1":
                $ent = intval($tarifa);
                $res = $tarifa - $ent;
                if ($res == 0) {                    
                    $tarifa = $ent;
                } else {
                    if ($res < 0.50) {
                        $tarifa = $ent;
                    } else {
                        $tarifa = $ent + 1;
                    }
                }
                break;

            case "10":
                $ent = intval($tarifa / 10) * 10;
                $res = $tarifa - $ent;
                if ($res < 5) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 10;
                }
                break;

            case "50":
                $ent = intval($tarifa / 50) * 50;
                $res = $tarifa - $ent;
                if ($res < 25) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 50;
                }
                break;

            case "100":
                $ent = intval($tarifa / 100) * 100;
                $res = $tarifa - $ent;
                if ($res < 50) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 100;
                }
                break;

            case "500":
                $ent = intval($tarifa / 500) * 500;
                $res = $tarifa - $ent;
                if ($res < 250) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 500;
                }
                break;

            case "1000":
                $ent = intval($tarifa / 1000) * 1000;
                $res = $tarifa - $ent;
                if ($res < 500) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 1000;
                }
                break;

            case "10+":
                if (intval($tarifa) < 10) {
                    $tarifa = 10;
                }
                break;

            case "50+":
                if (intval($tarifa) < 50) {
                    $tarifa = 50;
                }
                break;

            case "100+":
                if (intval($tarifa) < 100) {
                    $tarifa = 100;
                }
                break;

            case "500+":
                if (intval($tarifa) < 500) {
                    $tarifa = 500;
                }
                break;

            case "1000+":
                if (intval($tarifa) < 1000) {
                    $tarifa = 1000;
                }
                break;

            case "100+C":
                if (intval($tarifa) < 100) {
                    $tarifa = 100;
                } else {
                    $ent = intval($tarifa / 100) * 100;
                    $res = $tarifa - $ent;
                    if ($res < 50) {
                        $tarifa = $ent;
                    } else {
                        $tarifa = $ent + 100;
                    }
                }
                break;

            case "1000+C":
                if (intval($tarifa) < 1000) {
                    $tarifa = 1000;
                } else {
                    $ent = intval($tarifa / 1000) * 1000;
                    $res = $tarifa - $ent;
                    if ($res < 500) {
                        $tarifa = $ent;
                    } else {
                        $tarifa = $ent + 1000;
                    }
                }
                break;

            default:
                $tarifa = \funcionesGenerales::enteroval($tarifa);
                break;
        }
        return $tarifa;
    }

}

?>
