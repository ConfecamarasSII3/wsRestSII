<?php

class funcionesRegistrales_validarFirmante {

    public static function validarFirmante($mysqli, $tram = array(), $expentrada = false) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $matentradaprop = '';
        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';
        $respuesta["tiptra"] = '';
        $respuesta["firmante"] = '';
        $respuesta["factorfirmado"] = '';
        $respuesta["exigeverificado"] = '';

        // Ubica el tipo de trámite
        if (trim($tram["subtipotramite"]) != '') {
            $tiptra = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $tram["subtipotramite"] . "'");
            $respuesta["tiptra"] = $tram["subtipotramite"];
        } else {
            $tiptra = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $tram["tipotramite"] . "'");
            $respuesta["tiptra"] = $tram["tipotramite"];
        }

        // En caso de no existir el tipo de trámite
        if ($tiptra && !empty($tiptra)) {
            $respuesta["firmante"] = $tiptra["firmante"];
            $respuesta["factorfirmado"] = $tiptra["factorfirmado"];
            $respuesta["exigeverificado"] = $tiptra["exigeverificado"];
            if (trim($respuesta["factorfirmado"]) == '') {
                $respuesta["factorfirmado"] = 'CLAVE';
            }
        }

//
        if ($respuesta["firmante"] == '' || $respuesta["factorfirmado"] == '' || $respuesta["exigeverificado"] == '') {
            if ($respuesta["firmante"] == '') {
                $respuesta["firmante"] = '99';
            }
            if ($respuesta["factorfirmado"] == '') {
                $respuesta["factorfirmado"] = 'CLAVE';
            }
            if ($respuesta["exigeverificado"] == '') {
                $respuesta["exigeverificado"] = 'no';
            }
        }

// 
        $continuar = 'no';

//
        if ($respuesta["firmante"] == '99' && $respuesta["exigeverificado"] == 'no') {
            $continuar = 'si';
        } else {
            $firms = explode(",", $respuesta["firmante"]);
            $txtError = '';
            $txtErrorBase = '';
            foreach ($firms as $fx) {
                if ($fx == '99') {
                    $continuar = 'si';
                    $txtError = '';
                } else {
                    if ($txtErrorBase != '') {
                        $txtErrorBase .= ', ';
                    }
                    switch ($fx) {
                        case "01":
                            $txtErrorBase .= 'Debe ser el propietario o la persona actuando a nombre propio';
                            break;
                        case "11":
                            $txtErrorBase .= 'Debe ser representante legal';
                            break;
                        case "21":
                            $txtErrorBase .= 'Debe ser un socio o asociado';
                            break;
                        case "31":
                            $txtErrorBase .= 'Debe ser un miembro de Junta Directiva';
                            break;
                        case "41":
                            $txtErrorBase .= 'Debe ser un un Representante Legal';
                            break;
                        case "91":
                            $txtErrorBase .= 'Debe ser el proponente';
                            break;
                    }
                }
            }
        }

//
        if ($continuar == 'no') {
            if ($tram["tipotramite"] == 'matriculaest') {
                if ($tram["orgpnat"] == '99') {
                    if ($tram["idepnat"] != '') {
                        $expproptmp = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "(idclase='" . $tram["tipoidepnat"] . "' and numid='" . $tram["idepnat"] . "') or nit='" . $tram["idepnat"] . "'", "matricula");
                        if ($expproptmp === false || count($expproptmp) == 0) {
                            $continuar = 'si';
                        } else {
                            foreach ($expproptmp as $tmp) {
                                if ($tmp["matricula"] != '') {
                                    if ($tmp["ctrestmatricula"] != 'NM' &&
                                            $tmp["ctrestmatricula"] != 'MF' &&
                                            $tmp["ctrestmatricula"] != 'MC' &&
                                            $tmp["ctrestmatricula"] != 'IF' &&
                                            $tmp["ctrestmatricula"] != 'IC'
                                    ) {
                                        $matentradaprop = $tmp["matricula"];
                                    }
                                }
                            }
                            if ($matentradaprop === '') {
                                $continuar = 'si';
                            }
                        }
                    }
                }
            }
        }

//
        /*
          echo 'tipotramite: ' . $tram["tipotramite"] . '<br>';
          echo 'orgpnat: ' . $tram["orgpnat"] . '<br>';
          echo 'tipoidepnat: ' . $tram["tipoidepnat"] . '<br>';
          echo 'idepnat: ' . $tram["idepnat"] . '<br>';
          echo 'matricula entrada propietario: ' . $matentradaprop . '<br>';
          echo 'continuar: ' . $continuar . '<br>';
          echo 'count $expproptmp ' . count($expproptmp) . '<br>';
         */

//
        if ($continuar == 'no') {
            foreach ($firms as $fx) {
                if ($fx == '01') { // Firma el propietario del trámite
                    $txtError = $txtErrorBase;
                    if ($_SESSION["generales"]["identificacionusuariocontrol"] == $tram["idepnat"]) {
                        $continuar = 'si';
                        $txtError = '';
                    } else {
                        if ($expentrada && !empty($expentrada)) {
                            if ($_SESSION["generales"]["identificacionusuariocontrol"] == ltrim($expentrada["identificacion"], "0")) {
                                $continuar = 'si';
                                $txtError = '';
                            } else {
                                foreach ($expentrada["propietarios"] as $px) {
                                    if ($px["identificacionpropietario"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                        $continuar = 'si';
                                        $txtError = '';
                                    }
                                }
                            }
                        } else {
                            $matx = '';
                            if (ltrim($tram["idmatriculabase"], "0") != '') {
                                $matx = $tram["idmatriculabase"];
                            } else {
                                if (ltrim($tram["idexpedientebase"], "0") != '') {
                                    $matx = $tram["idexpedientebase"];
                                }
                            }
                            if ($matx == '') {
                                if (!empty($tram["expedientes"])) {
                                    foreach ($tram["expedientes"] as $exp) {
                                        if (trim($exp["matricula"]) != '') {
                                            if ($exp["cc"] == '' || $exp["cc"] = CODIGO_EMPRESA) {
                                                if (substr($exp["matricula"], 0, 5) != 'NUEVA') {
                                                    $matx = $exp["matricula"];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ($matx != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                                if ($_SESSION["generales"]["identificacionusuariocontrol"] == ltrim($exp["identificacion"], "0")) {
                                    $continuar = 'si';
                                    $txtError = '';
                                } else {
                                    foreach ($exp["propietarios"] as $px) {
                                        if ($px["identificacionpropietario"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                            $continuar = 'si';
                                            $txtError = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($continuar == 'no') {
                    if ($fx == '11') { // Representantes legales                
                        $txtError = $txtErrorBase;
                        if ($_SESSION["generales"]["identificacionusuariocontrol"] == $tram["iderepleg"]) {
                            $continuar = 'si';
                            $txtError = '';
                        } else {
                            $matx = '';
                            if ($matentradaprop != '') {
                                $matx = $matentradaprop;
                            }
                            if (ltrim($tram["idmatriculabase"], "0") != '') {
                                $matx = $tram["idmatriculabase"];
                            } else {
                                if (ltrim($tram["idexpedientebase"], "0") != '') {
                                    $matx = $tram["idexpedientebase"];
                                } else {
                                    if (ltrim($tram["numeromatriculapnat"], "0") != '' && $tram["camarapnat"] == CODIGO_EMPRESA) {
                                        $matx = $tram["numeromatriculapnat"];
                                    }
                                }
                            }
                            if ($matx == '') {
                                if (!empty($tram["expedientes"])) {
                                    foreach ($tram["expedientes"] as $exp) {
                                        if (trim($exp["matricula"]) != '') {
                                            if ($exp["cc"] == '' || $exp["cc"] = CODIGO_EMPRESA) {
                                                if (substr($exp["matricula"], 0, 5) != 'NUEVA') {
                                                    $matx = $exp["matricula"];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ($matx != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                                foreach ($exp["vinculos"] as $vx) {
                                    if (
                                            $vx["tipovinculo"] == 'ADMP' ||
                                            $vx["tipovinculo"] == 'RLP' ||
                                            $vx["tipovinculo"] == 'RLS' ||
                                            $vx["tipovinculo"] == 'RLS1' ||
                                            $vx["tipovinculo"] == 'RLS2'
                                    ) {
                                        if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                            $continuar = 'si';
                                            $txtError = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($continuar == 'no') {
                    if ($fx == '21') { // Socios
                        $txtError = $txtErrorBase;
                        $matx = '';
                        if ($matentradaprop != '') {
                            $matx = $matentradaprop;
                        }
                        if (ltrim($tram["idmatriculabase"], "0") != '') {
                            $matx = $tram["idmatriculabase"];
                        } else {
                            if (ltrim($tram["idexpedientebase"], "0") != '') {
                                $matx = $tram["idexpedientebase"];
                            }
                        }
                        if ($matx == '') {
                            if (!empty($tram["expedientes"])) {
                                foreach ($tram["expedientes"] as $exp) {
                                    if (trim($exp["matricula"]) != '') {
                                        if ($exp["cc"] == '' || $exp["cc"] = CODIGO_EMPRESA) {
                                            if (substr($exp["matricula"], 0, 5) != 'NUEVA') {
                                                $matx = $exp["matricula"];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($matx != '') {
                            $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                            foreach ($exp["vinculos"] as $vx) {
                                if ($vx["tipovinculo"] == 'SOC') {
                                    if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                        $continuar = 'si';
                                        $txtError = '';
                                    }
                                }
                            }
                        }
                    }
                }

                if ($continuar == 'no') {
                    if ($fx == '31') { // Junta directiva
                        $txtError = $txtErrorBase;
                        $matx = '';
                        if ($matentradaprop != '') {
                            $matx = $matentradaprop;
                        }
                        if (ltrim($tram["idmatriculabase"], "0") != '') {
                            $matx = $tram["idmatriculabase"];
                        } else {
                            if (ltrim($tram["idexpedientebase"], "0") != '') {
                                $matx = $tram["idexpedientebase"];
                            }
                        }
                        if ($matx == '') {
                            if (!empty($tram["expedientes"])) {
                                foreach ($tram["expedientes"] as $exp) {
                                    if (trim($exp["matricula"]) != '') {
                                        if ($exp["cc"] == '' || $exp["cc"] = CODIGO_EMPRESA) {
                                            if (substr($exp["matricula"], 0, 5) != 'NUEVA') {
                                                $matx = $exp["matricula"];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($matx != '') {
                            $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                            foreach ($exp["vinculos"] as $vx) {
                                if (
                                        $vx["tipovinculo"] == 'JDP' ||
                                        $vx["tipovinculo"] == 'JDS'
                                ) {
                                    if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                        $continuar = 'si';
                                        $txtError = '';
                                    }
                                }
                            }
                        }
                    }
                }

                if ($continuar == 'no') {
                    if ($fx == '41') { // Revisores fiscales
                        $txtError = $txtErrorBase;
                        $matx = '';
                        if ($matentradaprop != '') {
                            $matx = $matentradaprop;
                        }
                        if (ltrim($tram["idmatriculabase"], "0") != '') {
                            $matx = $tram["idmatriculabase"];
                        } else {
                            if (ltrim($tram["idexpedientebase"], "0") != '') {
                                $matx = $tram["idexpedientebase"];
                            }
                        }
                        if ($matx == '') {
                            if (!empty($tram["expedientes"])) {
                                foreach ($tram["expedientes"] as $exp) {
                                    if (trim($exp["matricula"]) != '') {
                                        if ($exp["cc"] == '' || $exp["cc"] = CODIGO_EMPRESA) {
                                            if (substr($exp["matricula"], 0, 5) != 'NUEVA') {
                                                $matx = $exp["matricula"];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($matx != '') {
                            $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                            foreach ($exp["vinculos"] as $vx) {
                                if (
                                        $vx["tipovinculo"] == 'RFP' ||
                                        $vx["tipovinculo"] == 'RFS' ||
                                        $vx["tipovinculo"] == 'RFS1'
                                ) {
                                    if ($vx["identificacionotros"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                        $continuar = 'si';
                                        $txtError = '';
                                    }
                                }
                            }
                        }
                    }
                }

                if ($continuar == 'no') {
                    if ($fx == '91') { // El proponente                            
                        $txtError = $txtErrorBase;
                        $matx = '';
                        if (ltrim($tram["idproponentebase"], "0") != '') {
                            $prpx = $tram["idproponentebase"];
                        } else {
                            if (ltrim($tram["idexpedientebase"], "0") != '') {
                                $prpx = $tram["idexpedientebase"];
                            }
                        }
                        if ($prpx == '') {
                            if (!empty($tram["expedientes"])) {
                                foreach ($tram["expedientes"] as $exp) {
                                    if (trim($exp["proponente"]) != '') {
                                        $prpx = $exp["proponente"];
                                    }
                                }
                            }
                        }
                        if ($prpx != '') {
                            $exp = \funcionesRegistrales::retornarExpedienteProponente($mysqli, $prpx);
                        } else {
                            $exp = false;
                            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "secuencia");
                            if ($temx && !empty($temx)) {
                                foreach ($temx as $tx) {
                                    $exp = \funcionesGenerales::desserializarExpedienteProponente($mysqli, $tx["xml"]);
                                }
                            }
                        }
                        if ($exp === false || $exp == 0) {
                            $txtError = 'No se localizo el expediente para encontrar el firmante.';
                        } else {
                            $txtError .= ' ' . $exp["tipoidentificacion"] . ' ' . $exp["identificacion"] . ' - ' . $_SESSION["tramite"]["tipoidefirmante"] . ' ' . $_SESSION["tramite"]["identificacionfirmante"];
                            if ($exp["organizacion"] == '01') {
                                if ($exp["identificacion"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                    $continuar = 'si';
                                    $txtError = '';
                                }
                            } else {
                                foreach ($exp["representanteslegales"] as $vx) {
                                    if ($vx["identificacionrepleg"] == $_SESSION["generales"]["identificacionusuariocontrol"]) {
                                        $continuar = 'si';
                                        $txtError = '';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($continuar == 'no') {
            $respuesta["codigoError"] = '0001';
            $respuesta["msgError"] = $txtError;
            return $respuesta;
        }

        // Si se requiere usuario verificado confirma que este grabado y activado
        if ($respuesta["exigeverificado"] == 'si') {

            //
            $nal = \funcionesGenerales::validarSuscripcionNacional($_SESSION["generales"]["emailusuariocontrol"], $_SESSION["generales"]["identificacionusuariocontrol"]);
            if ($nal["codigoerror"] == '0000') {
                return $respuesta;
            }

            //
            /*
            if ($nal["codigoerror"] == '9994') {
                $respuesta["codigoError"] = '0006'; // verificado sin activar
                return $respuesta;
            }
            */
            
            //
            $usu = false;
            $usus = retornarRegistrosMysqliApi($mysqli, 'usuarios_verificados', "email='" . $_SESSION["generales"]["emailusuariocontrol"] . "' and identificacion='" . $_SESSION["generales"]["identificacionusuariocontrol"] . "'", "id");
            if ($usus && !empty($usus)) {
                foreach ($usus as $usx) {
                    if ($usx["estado"] != 'EL') {
                        $usu = $usx;
                    }
                }
            }
            unset($usus);
            
            //
            if ($usu === false || empty($usu) || $usu["estado"] == 'EL') {
                if ($nal["codigoerror"] == '9994') {
                    $respuesta["codigoError"] = '0006'; // verificado sin activar
                    return $respuesta;
                }
            }
            
            //
            if ($usu === false || empty($usu) || $usu["estado"] == 'EL') {
                $respuesta["codigoError"] = '0002'; // usuario no existe  o está eliminado
            } else {
                if ($usu["estado"] == 'PE') {
                    $respuesta["codigoError"] = '0003'; // usuario pendiente
                }
                if ($usu["estado"] == 'RZ') {
                    $respuesta["codigoError"] = '0004'; // usuario rechazado
                }
                if ($usu["estado"] == 'SF') {
                    $respuesta["codigoError"] = '0005'; // usuario sin informacion financiera
                }
                if ($usu["estado"] == 'VE' && $usu["claveconfirmacion"] == '') {
                    $respuesta["codigoError"] = '0006'; // usuario no activado
                }
            }
        }

//
        return $respuesta;
    }

}

?>
