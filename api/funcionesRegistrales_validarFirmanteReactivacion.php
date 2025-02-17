<?php
 
class funcionesRegistrales_validarFirmanteReactivacion {

    public static function validarFirmanteReactivacion($mysqli, $exp) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';
        $respuesta["tiptra"] = '';
        $respuesta["firmante"] = '';
        $respuesta["factorfirmado"] = '';
        $respuesta["exigeverificado"] = '';
        $respuesta["txtrelacion"] = '';

//
        $respuesta["firmante"] = '01,11';
        $respuesta["factorfirmado"] = 'CLAVE';
        $respuesta["exigeverificado"] = 'si';

        $continuar = 'no';

//
        if ($exp["organizacion"] == '01') {
            if ($_SESSION["generales"]["identificacionusuariocontrol"] === $exp["identificacion"]) {
                $continuar = 'si';
                $respuesta["txtrelacion"] = 'Titular';
            }
        }

//
        if ($exp["organizacion"] === '02') {
            foreach ($exp["propietarios"] as $px) {
                if ($px["identificacionpropietario"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                    $continuar = 'si';
                    $respuesta["txtrelacion"] = 'Propietario';
                }
            }
            if ($continuar == 'no') {
                if (isset($exp["vincuprop"]) && !empty($exp["vincuprop"])) {
                    foreach ($exp["vincuprop"] as $pv) {
                        if ($pv["puedereactivar"] == 'S') {
                            if ($pv["numid"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                $continuar = 'si';
                                $respuesta["txtrelacion"] = 'Vinculado a la sociedad propietaria';
                            }
                        }
                    }
                }
            }
        }

        if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {

            foreach ($exp["vinculos"] as $vx) {
                if ($vx["puedereactivar"] == 'S') {
                    if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                        $continuar = 'si';
                        $respuesta["txtrelacion"] = $vx["cargootros"];
                    }
                }
            }
        }

        if ($exp["organizacion"] > '02' && $exp["categoria"] == '2') {
            foreach ($exp["vinculos"] as $vx) {
                if ($vx["puedereactivar"] == 'S') {
                    if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                        $continuar = 'si';
                        $respuesta["txtrelacion"] = $vx["cargootros"];
                    }
                }
            }
        }

        if ($exp["organizacion"] > '02' && $exp["categoria"] == '3') {
            foreach ($exp["vinculos"] as $vx) {
                if ($vx["puedereactivar"] == 'S') {
                    if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                        $continuar = 'si';
                        $respuesta["txtrelacion"] = $vx["cargootros"];
                    }
                }
            }
        }

//
        if ($continuar == 'no') {
            $respuesta["codigoError"] = '0001';
            $respuesta["msgError"] = '';
            return $respuesta;
        }

//
        return $respuesta;
    }

   
}

?>
