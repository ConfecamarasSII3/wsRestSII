<?php

class funcionesRegistrales_localizarSaldoPrepago {

    public static function localizarSaldoPrepago($mysqli, $ide) {
        $saldoPrep = 0;
        $prep = retornarRegistroMysqliApi($mysqli, 'mreg_prepagos', "identificacion='" . $ide . "'");
        if ($prep && !empty($prep)) {
            $preps = retornarRegistrosMysqliApi($mysqli, 'mreg_prepagos_uso', "identificacion='" . $ide . "'");
            if ($preps && !empty($preps)) {
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $saldoPrep = $saldoPrep + $p["valor"];
                    } else {
                        $saldoPrep = $saldoPrep - $p["valor"];
                    }
                }
            }
        }
        return $saldoPrep;
    }
}

?>
