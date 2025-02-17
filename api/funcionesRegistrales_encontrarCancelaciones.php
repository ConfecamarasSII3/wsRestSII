<?php

class funcionesRegistrales_encontrarCancelaciones {

    public static function encontrarCancelaciones($mysqli, $control, $tipoenvio = '1') {
        $cancelaciones = array();
        $mats = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "feccancelacion>='" . $_SESSION["entrada"]["fechainicial"] . "' and feccancelacion<='" . $_SESSION["entrada"]["fechafinal"] . "' and muncom='" . $_SESSION["entrada"]["municipio"] . "'", "matricula");
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if ($m["ctrestmatricula"] != 'NA' && $m["ctrestmatricula"] != 'NM') {
                    if ($tipoenvio == '1') {
                        $env = retornarRegistroMysqliApi($mysqli, 'mreg_envio_matriculas_api', "sistemadestino='" . $control["sistemadestino"] . "' and tiporeporte='4' and matricula='" . $m["matricula"] . "'", '*', 'U');
                        if ($env === false || empty($env) || $env["estadoenvio"] == '' || $env["estadoenvio"] == 'PE' || $env["estadoenvio"] == 'ER') {
                            $cancelaciones[$m["matricula"]] = $m["matricula"];
                        }
                    } else {
                        $cancelaciones[$m["matricula"]] = $m["matricula"];
                    }
                }
            }
        }
        return $cancelaciones;
    }

}

?>
