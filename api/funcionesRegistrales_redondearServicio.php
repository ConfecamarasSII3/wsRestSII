<?php

class funcionesRegistrales_redondearServicio {

    public static function redondearServicio($mysqli, $servicio, $valor) {
        $valorsalida = $valor;
        $arrServicio = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $servicio . "'");
        if (!$arrServicio) {
            return $valorsalida;
        }
        if (empty($arrServicio)) {
            return $valorsalida;
        }

        // 2015-12-29: JINT : Redondeos
        if (!isset($arrServicio["redondeo"])) {
            return $valorsalida;
        }

        if (trim($arrServicio["redondeo"]) == '') {
            return $valorsalida;
        }

        switch ($arrServicio["redondeo"]) {

            case "10":
                $ent = intval($valorsalida / 10) * 10;
                $res = $valorsalida - $ent;
                if ($res < 5) {
                    $valorsalida = $ent;
                } else {
                    $valorsalida = $ent + 10;
                }
                break;

            case "50":
                $ent = intval($valorsalida / 50) * 50;
                $res = $valorsalida - $ent;
                if ($res < 25) {
                    $valorsalida = $ent;
                } else {
                    $valorsalida = $ent + 50;
                }
                break;

            case "100":
                $ent = intval($valorsalida / 100) * 100;
                $res = $valorsalida - $ent;
                if ($res < 50) {
                    $valorsalida = $ent;
                } else {
                    $valorsalida = $ent + 100;
                }
                break;

            case "500":
                $ent = intval($valorsalida / 500) * 500;
                $res = $valorsalida - $ent;
                if ($res < 250) {
                    $valorsalida = $ent;
                } else {
                    $valorsalida = $ent + 500;
                }
                break;

            case "1000":
                $ent = intval($valorsalida / 1000) * 1000;
                $res = $valorsalida - $ent;
                if ($res < 500) {
                    $valorsalida = $ent;
                } else {
                    $valorsalida = $ent + 1000;
                }
                break;

            case "10+":
                if (intval($valorsalida) < 10) {
                    $valorsalida = 10;
                }
                break;

            case "50+":
                if (intval($valorsalida) < 50) {
                    $tarifa = 50;
                }
                break;

            case "100+":
                if (intval($valorsalida) < 100) {
                    $valorsalida = 100;
                }
                break;

            case "500+":
                if (intval($valorsalida) < 500) {
                    $valorsalida = 500;
                }
                break;

            case "1000+":
                if (intval($valorsalida) < 1000) {
                    $valorsalida = 1000;
                }

            case "100+C":
                if (intval($valorsalida) < 100) {
                    $valorsalida = 100;
                } else {
                    $ent = intval($valorsalida / 100) * 100;
                    $res = $valorsalida - $ent;
                    if ($res < 50) {
                        $valorsalida = $ent;
                    } else {
                        $valorsalida = $ent + 100;
                    }
                }
                break;

            case "1000+C":
                if (intval($valorsalida) < 1000) {
                    $valorsalida = 1000;
                } else {
                    $ent = intval($valorsalida / 1000) * 1000;
                    $res = $valorsalida - $ent;
                    if ($res < 500) {
                        $valorsalida = $ent;
                    } else {
                        $valorsalida = $ent + 1000;
                    }
                }
                break;
                
            case "100++":
                if (intval($valorsalida) < 100) {
                    $valorsalida = 100;
                } else {
                    $ent = intval($valorsalida / 100) * 100;
                    $res = $valorsalida - $ent;
                    if ($res != 0) {
                        $valorsalida = $ent + 100;
                    }
                }
                break;

            case "1000++":
                if (intval($valorsalida) < 1000) {
                    $valorsalida = 1000;
                } else {
                    $ent = intval($valorsalida / 1000) * 1000;
                    $res = $valorsalida - $ent;
                    if ($res != 0) {
                        $valorsalida = $ent + 1000;
                    }
                }
                break;
        }

        //
        return $valorsalida;
    }
}

?>
