<?php

class funcionesRegistrales_validarReglasEspecialesRenovacion {

    public static function validarReglasEspecialesRenovacion($mysqli, $codcam = '', $servicio = '', $ano = '') {
        $sumar = 'no';
        // En el caso de la C&aacute;mara de Comercio de C&uacute;cuta y cuando se trate del servicio
        // 04040172 (estampilla), se liquida siempre y cuando el a&ntilde;o sea igual o superior al 2003 
        if ($codcam == '11') {
            if ($servicio == '04040172') {
                if ($ano >= 2003) {
                    $sumar = 'si';
                }
            }
        }
//

        return $sumar;
    }
}

?>
