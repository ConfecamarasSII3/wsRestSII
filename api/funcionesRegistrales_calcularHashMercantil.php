<?php

class funcionesRegistrales_calcularHashMercantil {

    public static function calcularHashMercantil($dbx, $mat = '', $retorno = array()) {
        if (empty($retorno)) {
            $retorno = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $mat);
        }
        $retorno["fechahoraactualizacion"] = date("Ymd") . '-' . date("His");
        return md5(json_encode($retorno));
    }

}

?>
