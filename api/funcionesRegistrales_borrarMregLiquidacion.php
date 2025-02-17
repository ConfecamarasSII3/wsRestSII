<?php

class funcionesRegistrales_borrarMregLiquidacion {

    public static function borrarMregLiquidacion($dbx, $numliq) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $arrLiq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "idliquidacion=" . $numliq);
        if ($arrLiq["idestado"] > '05') {
            return false;
        }

        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacionqr', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_campos', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_textos_rues', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle_rues', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacionexpedientes', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones_generales', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones', "idliquidacion=" . $numliq);
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_asesorias', "idliquidacion=" . $numliq);
        return true;
    }

}

?>
