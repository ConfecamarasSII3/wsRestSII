<?php

class funcionesRegistrales_localizarMotivoCancelacion {

    public static function localizarMotivoCancelacion($mysqli, $mat, $org, $cat, $libs, $motivox) {
        $motivo = array();
        $motivo["generico"] = '';
        $motivo["texto"] = '';

        //
        if (ltrim(trim($motivox), "0") != '') {
            $motivo["generico"] = retornarRegistroMysqliApi($mysqli, 'mreg_motivos_cancelacion', "id='" . $motivox . "'", "descripcion");
            $motivo["texto"] = '';
            return $motivo;
        }

        //
        if ($libs && !empty($libs)) {
            $tieneliquidacion = '';
            $tieneincorporacion = '';
            $tienecambiodomicilio = '';
            $tieneabsorcion = '';
            $tienefusion = '';
            $tiene530 = '';
            $tiene540 = '';
            $cb530 = '';
            $cb540 = '';
            foreach ($libs as $l) {

                //
                if ($l["acto"] == '0498') {
                    $tienecambiodomicilio = 'si';
                }

                //
                if ($l["acto"] == '0520') {
                    $tieneliquidacion = 'si';
                }

                //
                if ($l["acto"] == '0523') {
                    $tieneincorporacion = 'si';
                }

                //
                if ($l["acto"] == '0733') {
                    $tieneabsorcion = 'si';
                }

                //
                if ($l["acto"] == '0531') {
                    $motivo["generico"] = 'Fallecimiento';
                }

                //
                if ($l["acto"] == '0532') {
                    $motivo["generico"] = 'Cambio de domicilio';
                }

                //
                if ($l["acto"] == '0534') {
                    $motivo["generico"] = 'Fusión';
                }

                //
                if ($l["acto"] == '0536') {
                    $motivo["generico"] = 'Absorción';
                }

                //
                if ($l["acto"] == '0538') {
                    $motivo["generico"] = 'Escisión';
                }

                //
                if ($l["acto"] == '0539') {
                    $motivo["generico"] = 'Reconstitución';
                }

                //
                if ($l["acto"] == '0530') {
                    $tiene530 = 'si';
                    $cb530 = $l["idradicacion"];
                }

                //
                if ($l["acto"] == '0540') {
                    $tiene540 = 'si';
                    $cb540 = $l["idradicacion"];
                }

                //
                if ($l["acto"] == '0530' || $l["acto"] == '0540') {
                    $pos = strpos($l["noticia"], 'DEPURACIÓN');
                    $pos1 = strpos($l["noticia"], 'DEPURACION');
                    $pos2 = strpos($l["noticia"], '1727');
                    $pos3 = strpos($l["noticia"], '1429');
                    if ($pos !== false ||
                            $pos1 !== false ||
                            $pos2 !== false ||
                            $pos3 !== false) {
                        $motivo["generico"] = 'Depuración';
                    }
                }
            }

            if ($motivo["generico"] == '') {
                if ($tieneabsorcion == 'si') {
                    $motivo["generico"] = 'Absorción';
                }
            }

            if ($motivo["generico"] == '') {
                if ($tienefusion == 'si') {
                    $motivo["generico"] = 'Fusión';
                }
            }

            if ($motivo["generico"] == '') {
                if ($tienecambiodomicilio == 'si') {
                    $motivo["generico"] = 'Cambio de domicilio';
                }
            }

            if ($motivo["generico"] == '') {
                if ($tieneincorporacion == 'si') {
                    $motivo["generico"] = 'Incorporación';
                }
            }

            if ($motivo["generico"] == '') {
                if ($tieneliquidacion == 'si') {
                    $motivo["generico"] = 'Liquidación';
                }
            }

            if ($motivo["generico"] == '' && $tiene530 == 'si') {
                if (ltrim(trim($cb530), "0") != '') {
                    $cb = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $cb530 . "'");
                    if ($cb && $cb["actoreparto"] == '20') {
                        $motivo["generico"] = 'Voluntario/InscripcionDocumentos';
                    }
                    if ($cb && $cb["actoreparto"] == '25') {
                        $motivo["generico"] = 'Compraventa';
                    }
                    if ($motivo["generico"] == '') {
                        if ($cb && substr($cb["operacion"], 0, 6) == '90-RUE') {
                            $motivo["generico"] = 'RUE/InscripcionDocumentos';
                        }
                    }
                } else {
                    $motivo["generico"] = 'Sin razón declarada';
                }
            }

            if ($motivo["generico"] == '' && $tiene540 == 'si') {
                if (ltrim(trim($cb540), "0") != '') {
                    $cb = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $cb540 . "'");
                    if ($cb && $cb["actoreparto"] == '20') {
                        $motivo["generico"] = 'Voluntario/InscripcionDocumentos';
                    }
                    if ($motivo["generico"] == '') {
                        if ($cb && substr($cb["operacion"], 0, 6) == '90-RUE') {
                            $motivo["generico"] = 'RUE/InscripcionDocumentos';
                        }
                    }
                } else {
                    $motivo["generico"] = 'Sin razón declarada';
                }
            }
        }
        return $motivo;
    }

}

?>
