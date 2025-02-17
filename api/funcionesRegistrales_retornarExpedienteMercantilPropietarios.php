<?php

class funcionesRegistrales_retornarExpedienteMercantilPropietarios {

    public static function retornarExpedienteMercantilPropietarios($dbx = null, $mat = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');

        set_error_handler('myErrorHandler');

        $nameLog = 'retornarExpedienteMercantilPropietarios_' . $mat;
        $genlog = 'no';

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $mat, 'Inicia lectura expediente');
        }

        // ********************************************************************************** //
        // Instancia la BD si no existe
        // ********************************************************************************** //
        if ($dbx === null) {
            $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
            if (mysqli_connect_error()) {
                $_SESSION["generales"]["mensajerror"] = 'Error coenctando con la BD';
                return false;
            }
        } else {
            $mysqli = $dbx;
        }

        //
        $retorno = array();
        $retorno["propietarios"] = array();
        
        // Propietarios
        $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $mat . "'", "id");
        $iProp = 0;
        if ($props) {
            if (!empty($props)) {
                foreach ($props as $prop) {
                    if ($prop["estado"] == 'V') {
                        $iProp++;
                        if (ltrim($prop["matriculapropietario"], "0") != '') {
                            if (ltrim($prop["codigocamara"], "0") == '') {
                                $prop["codigocamara"] = $_SESSION["generales"]["codigoempresa"];
                            }
                        }

                        $prop["estadomatriculaprop"] = '';
                        $prop["nit"] = '';

                        if ($prop["codigocamara"] == $_SESSION["generales"]["codigoempresa"]) {
                            if (ltrim(trim($prop["matriculapropietario"]), "0") != '') {
                                $xprop = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim(trim($prop["matriculapropietario"]), "0") . "'");
                                if ($xprop === false || empty($xprop)) {
                                    $prop["tipoidentificacion"] = '';
                                    $prop["identificacion"] = '';
                                    $prop["nit"] = '';
                                    $prop["razonsocial"] = '';
                                    $prop["nombre1"] = '';
                                    $prop["nombre2"] = '';
                                    $prop["apellido1"] = '';
                                    $prop["apellido2"] = '';
                                    $prop["dircom"] = '';
                                    $prop["muncom"] = '';
                                    $prop["dirnot"] = '';
                                    $prop["munnot"] = '';
                                    $prop["telcom1"] = '';
                                    $prop["telcom2"] = '';
                                    $prop["telcom3"] = '';
                                    $prop["estadomatriculaprop"] = '';
                                } else {
                                    $prop["tipoidentificacion"] = $xprop["idclase"];
                                    $prop["identificacion"] = $xprop["numid"];
                                    $prop["nit"] = $xprop["nit"];
                                    $prop["razonsocial"] = $xprop["razonsocial"];
                                    $prop["nombre1"] = $xprop["nombre1"];
                                    $prop["nombre2"] = $xprop["nombre2"];
                                    $prop["apellido1"] = $xprop["apellido1"];
                                    $prop["apellido2"] = $xprop["apellido2"];
                                    $prop["dircom"] = $xprop["dircom"];
                                    $prop["muncom"] = $xprop["muncom"];
                                    $prop["dirnot"] = $xprop["dirnot"];
                                    $prop["munnot"] = $xprop["munnot"];
                                    $prop["telcom1"] = $xprop["telcom1"];
                                    $prop["telcom2"] = $xprop["telcom2"];
                                    $prop["telcom3"] = $xprop["telcom3"];
                                    $prop["estadomatriculaprop"] = $xprop["ctrestmatricula"];
                                }
                            }
                        }


                        $retorno["propietarios"][$iProp]["id"] = $prop["id"];
                        $retorno["propietarios"][$iProp]["camarapropietario"] = $prop["codigocamara"];
                        $retorno["propietarios"][$iProp]["matriculapropietario"] = ltrim($prop["matriculapropietario"], '0');
                        $retorno["propietarios"][$iProp]["idtipoidentificacionpropietario"] = trim($prop["tipoidentificacion"]);
                        $retorno["propietarios"][$iProp]["identificacionpropietario"] = ltrim($prop["identificacion"], '0');
                        $retorno["propietarios"][$iProp]["nitpropietario"] = '';
                        if (ltrim(trim($prop["nit"]), '0') == '') {
                            if ($retorno["propietarios"][$iProp]["idtipoidentificacionpropietario"] == '2') {
                                $retorno["propietarios"][$iProp]["nitpropietario"] = $retorno["propietarios"][$iProp]["identificacionpropietario"];
                            }
                        } else {
                            $retorno["propietarios"][$iProp]["nitpropietario"] = ltrim($prop["nit"], '0');
                        }
                        $retorno["propietarios"][$iProp]["nombrepropietario"] = ($prop["razonsocial"]);
                        $retorno["propietarios"][$iProp]["nom1propietario"] = ($prop["nombre1"]);
                        $retorno["propietarios"][$iProp]["nom2propietario"] = ($prop["nombre2"]);
                        $retorno["propietarios"][$iProp]["ape1propietario"] = ($prop["apellido1"]);
                        $retorno["propietarios"][$iProp]["ape2propietario"] = ($prop["apellido2"]);
                        $retorno["propietarios"][$iProp]["tipopropiedad"] = ($prop["tipopropiedad"]);
                        $retorno["propietarios"][$iProp]["direccionpropietario"] = ($prop["dircom"]);
                        $retorno["propietarios"][$iProp]["municipiopropietario"] = ($prop["muncom"]);
                        $retorno["propietarios"][$iProp]["direccionnotpropietario"] = ($prop["dirnot"]);
                        $retorno["propietarios"][$iProp]["municipionotpropietario"] = ($prop["munnot"]);
                        $retorno["propietarios"][$iProp]["telefonopropietario"] = ($prop["telcom1"]);
                        $retorno["propietarios"][$iProp]["telefono2propietario"] = ($prop["telcom2"]);
                        $retorno["propietarios"][$iProp]["celularpropietario"] = ($prop["telcom3"]);

                        if (isset($prop["estadomatriculaprop"])) {
                            $retorno["propietarios"][$iProp]["estadomatriculapropietario"] = ($prop["estadomatriculaprop"]);
                        }
                    }
                }
            }
        }

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo mreg_est_propietarios');
        }


        //
        return $retorno;
    }

}
