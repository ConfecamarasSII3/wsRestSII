<?php

class funcionesRegistrales_validarMatriculasRenovadas {

    public static function validarMatriculasRenovadas($mysqli, $liquidacion) {
        $retornar = false;
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', "idliquidacion=" . $liquidacion);
        if ($temx && !empty($temx)) {
            foreach ($temx as $tx) {
                if ($tx["cc"] == '' || $tx["cc"] == CODIGO_EMPRESA) {
                    if (trim($tx["matricula"]) != '') {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $tx["matricula"] . "'", "ultanoren");
                        if ($exp == date("Y")) {
                            $retornar = true;
                        }
                    }
                }
            }
        }
        return $retornar;
    }
}

?>
