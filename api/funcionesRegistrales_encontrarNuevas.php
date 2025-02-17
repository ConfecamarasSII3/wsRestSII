<?php

class funcionesRegistrales_encontrarNuevas {

    public static function encontrarNuevas($mysqli, $control, $tipoenvio = '1') {
        $matriculas = array();
        if (isset($_SESSION["entrada"]["matricula"]) && trim($_SESSION["entrada"]["matricula"]) != '') {
            $mats = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'", "matricula", "matricula,ctrestmatricula");
        } else {
            if (isset($_SESSION["entrada"]["procesar"]) && (trim($_SESSION["entrada"]["procesar"]) == 'T' || trim($_SESSION["entrada"]["procesar"]) == '')) {
                $mats = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "fecmatricula>='" . $_SESSION["entrada"]["fechainicial"] . "' and fecmatricula <= '" . $_SESSION["entrada"]["fechafinal"] . "' and muncom='" . $_SESSION["entrada"]["municipio"] . "'", "matricula", "matricula,ctrestmatricula");
            } else {
                $mats = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "fecmatricula='" . date("Ymd") . "' and muncom='" . $_SESSION["entrada"]["municipio"] . "'", "matricula", "matricula,ctrestmatricula");
            }
        }
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if ($m["ctrestmatricula"] != 'NA' && $m["ctrestmatricula"] != 'NM') {
                    if ($tipoenvio == '1') {
                        $env = retornarRegistroMysqliApi($mysqli, 'mreg_envio_matriculas_api', "sistemadestino='" . $control["sistemadestino"] . "' and tiporeporte='2' and matricula='" . $m["matricula"] . "'","*","U");
                        if ($env === false || empty($env) || $env["estadoenvio"] == '' || $env["estadoenvio"] == 'ER') {
                            $matriculas[] = $m["matricula"];
                        }
                    } else {
                        $matriculas[] = $m["matricula"];
                    }
                }
            }
        }
        unset($mats);
        return $matriculas;
    }

}
 
?>
