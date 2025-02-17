<?php

class funcionesRegistrales_determinarTamanoEmpresarialCodigo {

    public static function determinarTamanoEmpresarialCodigo($mysqli, $ciiu, $ingresos, $anodatos, $fechadatos, $anomatricula = 'no') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $salida = '';
        if ($ciiu != '') {
            $arrCiiu = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu like '%" . $ciiu . "'");
            if ($arrCiiu && !empty($arrCiiu)) {
                if (isset($arrCiiu["sector"]) && $arrCiiu["sector"] != '') {
                    $arrTam = retornarRegistroMysqliApi($mysqli, 'bas_tamano_empresarial', "sector='" . $arrCiiu["sector"] . "'");
                    if ($arrTam && !empty($arrTam)) {
                        $uvt = 0;
                        $arrSal = retornarRegistrosMysqliApi($mysqli, 'bas_smlv', "1=1", "fecha");
                        foreach ($arrSal as $s) {
                            /*
                              if ($anomatricula == 'no') {
                              if ($s["fecha"] <= $anodatos . '0000') {
                              $uvt = $s["uvt"];
                              }
                              }
                             */
                            // if ($anomatricula == 'si') {
                            if ($s["fecha"] <= $fechadatos) {
                                $uvt = $s["uvt"];
                            }
                            // }
                        }
                        if ($uvt != 0) {
                            $ingresosuvt = $ingresos / $uvt;
                        } else {
                            $ingresosuvt = 0;
                        }
                        $_SESSION["generales"]["tamuvts"] = $ingresosuvt;
                        if ($ingresosuvt >= $arrTam["micromin"] && $ingresosuvt <= $arrTam["micromax"]) {
                            $salida = '1';
                        }
                        if ($ingresosuvt >= $arrTam["pequemin"] && $ingresosuvt <= $arrTam["pequemax"]) {
                            $salida = '2';
                        }
                        if ($ingresosuvt >= $arrTam["medimin"] && $ingresosuvt <= $arrTam["medimax"]) {
                            $salida = '3';
                        }
                        if ($ingresosuvt >= $arrTam["granmin"]) {
                            $salida = '4';
                        }
                    }
                }
            }
        }
        return $salida;
    }

}

?>
