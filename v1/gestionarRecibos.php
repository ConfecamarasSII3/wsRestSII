<?php

class gestionarRecibos
{

    public static function asentarReciboRegistroSii($mysqli = null, $idSolicitudPago = 0, $tipogasto = '0', $estadofinalliquidacion = '09', $fechareciboagenerar = '', $fecharenovacionagenerar = '', $cajero = '') {
        $_SESSION["generales"]["pathabsoluto"] = PATH_ABSOLUTO_SITIO;
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/gestionRecibos.php');
        $resultado = \gestionRecibos::asentarRecibos($mysqli, $idSolicitudPago, $tipogasto = '0', $estadofinalliquidacion = '09', $fechareciboagenerar = '', $fecharenovacionagenerar = '', $cajero = '');
        return $resultado;
    }
}
