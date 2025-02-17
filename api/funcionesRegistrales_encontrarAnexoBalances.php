<?php

class funcionesRegistrales_encontrarAnexoBalances {

    public static function encontrarAnexoBalances($dbx, $idliq) {
        $pantallabalance = '';
        $aliq = \funcionesRegistrales::retornarMregLiquidacion($dbx, $idliq);
        if ($aliq["pedirbalance"] == "si" && ($aliq["tipotramite"] == 'renovacionmatricula' || $aliq["tipotramite"] == 'renovacionesadl')) {
            $pantallabalance = 'si';
            $anxliq = retornarRegistrosMysqliApi($dbx, 'mreg_anexos_liquidaciones', "idliquidacion=" . $aliq["numeroliquidacion"], "idanexo");
            if ($anxliq && !empty($anxliq)) {
                foreach ($anxliq as $anx) {
                    if ($anx["eliminado"] != 'SI') {
                        if ($anx["identificador"] == 'regmer-esadl-estados-financieros') {
                            $pantallabalance = 'no';
                        }
                    }
                }
            }
        }
        return $pantallabalance;
    }

}

?>
