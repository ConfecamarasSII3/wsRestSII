<?php

class funcionesRegistrales_buscarTarifaValor {

    /**
     * 
     * @param type $mysqli
     * @param type $idservicio
     * @return type
     */
    public static function buscarTarifaValor($mysqli, $idservicio = '') {
        $retornar = 0;

        //
        $fliquidar = date("Ymd");
        $fcorteuvb = '';
        if (!defined('FECHA_INICIO_APLICACION_UVB') || FECHA_INICIO_APLICACION_UVB == '') {
            $fcorteuvb = '20250101';
        } else {
            $fcorteuvb = FECHA_INICIO_APLICACION_UVB;
        }

        //
        if ($fliquidar < $fcorteuvb) {
            $retornar = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $idservicio . "'", "valorservicio");
        }

        if ($fliquidar >= $fcorteuvb) {
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $idservicio . "'");
            if ($serv["tipotarifa"] == 'TAR-VALOR' || $serv["tipotarifa"] == '') {
                $retornar = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $idservicio . "'", "valorservicio");
            } else {
                $uvb = \funcionesGenerales::retornarUvbActual($mysqli, date("Y"));
                $result = retornarRegistroMysqliApi($mysqli, 'mreg_tarifas_base_uvb', "tipo='" . $serv["tipotarifa"] . "'");
                if ($result && !empty($result)) {
                    $retornar = $result["tar"] * $uvb;
                } else {
                    $retornar = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $idservicio . "'", "valorservicio");
                }
            }
        }
        return $retornar;
    }

}

?>
