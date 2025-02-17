<?php

class funcionesRegistrales_retornarLiquidacionDatosExpediente {

    public static function retornarLiquidacionDatosExpediente($mysqli, $numliq, $tipotramite, $numexp = '', $grudat = '', $proceso = '', $secuencia = '') {

        if ($numliq == null || $numliq == '' || $numliq == 0) {
            return false;
        }
        $vmat = explode("-", $numexp);
        $cc = '';
        $ma = '';
        if (count($vmat) == 1) {
            $cc = CODIGO_EMPRESA;
            $ma = $vmat[0];
        } else {
            $cc = $vmat[0];
            $ma = $vmat[1];
        }

        if (($tipotramite == 'renovacionmatricula') || ($tipotramite == 'renovacionesadl')) {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq . " and expediente='" . $ma . "' and grupodatos='" . $grudat . "'", "idliquidacion,secuencia");
        }

        if ($tipotramite == 'matriculapnat' ||
                $tipotramite == 'matriculacambidom' ||
                $tipotramite == 'matriculapjur' ||
                $tipotramite == 'matriculaest' ||
                $tipotramite == 'matriculasuc' ||
                $tipotramite == 'matriculaage' ||
                $tipotramite == 'matriculaesadl' ||
                $tipotramite == 'constitucionpjur' ||
                $tipotramite == 'constitucionesadl' ||
                $tipotramite == 'inscripciondocumentos' ||
                $tipotramite == 'inscripcionesregmer' ||
                $tipotramite == 'compraventa' ||
                $tipotramite == 'mercantil') {
            if ($secuencia != '' && $secuencia != '000') {
                $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq . " and secuencia ='" . sprintf("%03s", $secuencia) . "' and expediente='" . $ma . "' and grupodatos='" . $grudat . "'", "idliquidacion,secuencia");
            } else {
                $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq . " and expediente='" . $ma . "' and grupodatos='" . $grudat . "'", "idliquidacion,secuencia");
            }
        }

        if (($tipotramite == 'mutaciondireccion') || ($tipotramite == 'mutacionactividad') || ($tipotramite == 'mutacionnombre') || ($tipotramite == 'mutaciongeneral')) {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq . " and expediente='" . $ma . "' and grupodatos='" . $grudat . "'", "idliquidacion,secuencia");
        }

        if ($tipotramite == 'cancelacionmatricula') {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq . " and expediente='" . $ma . "' and grupodatos='" . $grudat . "'", "idliquidacion,secuencia");
        }

//
        if ($tipotramite == 'inscripcionproponente' || $tipotramite == 'compraventa') {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq, "idliquidacion,secuencia");
        }

//
        if ($tipotramite == 'actualizacionespecial') {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq, "idliquidacion,secuencia");
        }

        if ($tipotramite == 'actualizacionproponente') {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq, "idliquidacion,secuencia");
        }

        if ($tipotramite == 'actualizacionproponente399') {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq, "idliquidacion,secuencia");
        }

        if ($tipotramite == 'renovacionproponente') {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq, "idliquidacion,secuencia");
        }

        if ($tipotramite == 'cambiodomicilioproponente') {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq, "idliquidacion,secuencia");
        }

        if ($tipotramite == 'cancelacionproponente') {
            $result = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq, "idliquidacion,secuencia");
        }

        if ($result === false) {
            $retornar = false;
        } else {
            if (empty($result)) {
                $retornar = 0;
            } else {
                $retornar = 0;
                foreach ($result as $res) {
                    if (($tipotramite == 'renovacionmatricula') || ($tipotramite == 'renovacionesadl')) {
                        $retornar = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, stripslashes($res["xml"]));
                    }

                    if ($tipotramite == 'matriculapnat' ||
                            $tipotramite == 'matriculacambidom' ||
                            $tipotramite == 'matriculaest' ||
                            $tipotramite == 'matriculasuc' ||
                            $tipotramite == 'matriculaage' ||
                            $tipotramite == 'matriculapjur' ||
                            $tipotramite == 'matriculaest' ||
                            $tipotramite == 'constitucionpjur' ||
                            $tipotramite == 'constitucionesadl' ||
                            $tipotramite == 'inscripciondocumentos' ||
                            $tipotramite == 'compraventa' ||
                            $tipotramite == 'mercantil'
                    ) {
                        $retornar = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, stripslashes($res["xml"]));
                    }

                    if ($tipotramite == 'inscripcionproponente') {
                        $retornar = \funcionesGenerales::desserializarExpedienteProponente($mysqli, stripslashes($res["xml"]), '', 'no', $proceso);
                    }

                    if ($tipotramite == 'actualizacionproponente') {
                        $retornar = \funcionesGenerales::desserializarExpedienteProponente($mysqli, stripslashes($res["xml"]), '', 'no', $proceso);
                    }

                    if ($tipotramite == 'actualizacionproponente399') {
                        $retornar = \funcionesGenerales::desserializarExpedienteProponente($mysqli, stripslashes($res["xml"]), '', 'no', $proceso);
                    }

                    if ($tipotramite == 'renovacionproponente') {
                        $retornar = \funcionesGenerales::desserializarExpedienteProponente($mysqli, stripslashes($res["xml"]), '', 'no', $proceso);
                    }

                    if ($tipotramite == 'cambiodomicilioproponente') {
                        $retornar = \funcionesGenerales::desserializarExpedienteProponente($mysqli, stripslashes($res["xml"]), '', 'no', $proceso);
                    }

                    if ($tipotramite == 'actualizacionespecial') {
                        $retornar = \funcionesGenerales::desserializarExpedienteProponente($mysqli, stripslashes($res["xml"]), '', 'no', $proceso);
                    }
                }
            }
            $_SESSION["generales"]["mensajeerror"] = '';
        }

        return $retornar;
    }

}

?>
