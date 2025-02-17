<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarEstadisticasDiarias {

    public function consultarEstadisticasDiarias(API $api) {
        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
        $_SESSION["jsonsalida"]["mensajeerror"] = 'Funcionalidad no habilitada';
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

}
