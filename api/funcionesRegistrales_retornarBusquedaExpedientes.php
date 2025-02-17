<?php

class funcionesRegistrales_retornarBusquedaExpedientes {

    public static function retornarBusquedaExpedientes($mysqli, $criterio = '', $matbase = '', $propbase = '', $nombase = '', $palbase = '', $idebase = '', $tipoide = '', $semilla = '0', $cantidadregistros = 15, $mostrarnomatriculados = 'S', $soloestablecimientos = 'N', $mostrarcancelados = 'S', $relacionestablecimientos = 'N', $registroexacto = 'N') {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/definicionArrays.php');

        if ($criterio != '') {
            $_SESSION["consulta"]["criterio"] = $criterio;
            $_SESSION["consulta"]["matricula"] = ltrim($matbase, "0");
            $_SESSION["consulta"]["proponente"] = ltrim($propbase, "0");
            $_SESSION["consulta"]["nombre"] = trim($nombase);
            $_SESSION["consulta"]["palabras"] = trim($palbase);
            $_SESSION["consulta"]["identificacion"] = ltrim(trim($idebase), "0");
            $_SESSION["consulta"]["semilla"] = $semilla;
            $_SESSION["consulta"]["retornar"] = $cantidadregistros;
            $_SESSION["consulta"]["offset"] = $semilla;
            $_SESSION["consulta"]["registroexacto"] = $registroexacto;
        } else {
            $_SESSION["consulta"]["retornar"] = $cantidadregistros;
            if (!isset($_SESSION["consulta"]["registroexacto"])) {
                $_SESSION["consulta"]["registroexacto"] = 'N';
            }
        }

//
        $soloestq = '';
        $canq = '';
        if ($soloestablecimientos == 'S') {
            $soloestq = " and organizacion='02'";
        }
        if ($mostrarcancelados != 'S') {
            $canq = " and (ctrestmatricula IN ('MA','MI','IA','II'))";
        }

        if ($semilla != '0') {
            $_SESSION["consulta"]["offset"] = $semilla * $cantidadregistros;
        }

        switch ($_SESSION["consulta"]["criterio"]) {
            case "1":
                if ($_SESSION["consulta"]["registroexacto"] == 'S') {
                    $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($_SESSION["consulta"]["matricula"], "0") . "' " . $soloestq . $canq, "matricula", '*', $_SESSION["consulta"]["offset"], $_SESSION["consulta"]["retornar"]);
                } else {
                    $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "matricula>='" . ltrim($_SESSION["consulta"]["matricula"], "0") . "' " . $soloestq . $canq, "matricula", '*', $_SESSION["consulta"]["offset"], $_SESSION["consulta"]["retornar"]);
                }
                break;
            case "2":
                if ($_SESSION["consulta"]["registroexacto"] == 'S') {
                    $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . ltrim($_SESSION["consulta"]["proponente"], "0") . "'", "proponente", '*', $_SESSION["consulta"]["offset"], $_SESSION["consulta"]["retornar"]);
                } else {
                    $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "proponente>='" . ltrim($_SESSION["consulta"]["proponente"], "0") . "'", "proponente", '*', $_SESSION["consulta"]["offset"], $_SESSION["consulta"]["retornar"]);
                }
                break;
            case "3":
                $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "razonsocial like '" . $_SESSION["consulta"]["nombre"] . "%' " . $soloestq . $canq, "razonsocial", '*', $_SESSION["consulta"]["offset"], $_SESSION["consulta"]["retornar"]);
                break;

            case "4":
                $busqueda = "";
                $cantidad_palabras = 0;
                $palabras_busqueda = explode(" ", $_SESSION["consulta"]["palabras"]);
                foreach ($palabras_busqueda as $palabra) {
                    $cantidad_palabras++;
                    if ($cantidad_palabras == 1) {
                        $busqueda .= "(razonsocial like '%" . $palabra . "%' or sigla like '%" . $palabra . "%')";
                    } else {
                        $busqueda .= " and (razonsocial like '%" . $palabra . "%'  or sigla like '%" . $palabra . "%')";
                    }
                }
                $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $busqueda . $soloestq . $canq, "razonsocial", '*', $_SESSION["consulta"]["offset"], $_SESSION["consulta"]["retornar"]);
                break;
            case "5":
            case "6":
            case "7":
                if ($_SESSION["consulta"]["registroexacto"] == 'S') {
                    $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid = '" . $_SESSION["consulta"]["identificacion"] . "' or nit = '" . $_SESSION["consulta"]["identificacion"] . "'" . $soloestq . $canq, "numid", '*', $_SESSION["consulta"]["offset"], $_SESSION["consulta"]["retornar"]);
                } else {
                    $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid like '" . $_SESSION["consulta"]["identificacion"] . "%' or nit like '" . $_SESSION["consulta"]["identificacion"] . "%'" . $soloestq . $canq, "numid", '*', $_SESSION["consulta"]["offset"], $_SESSION["consulta"]["retornar"]);
                }
                break;
        }

        if ($reg === false) {
            $_SESSION["generales"]["txtemergente"] = 'No encontr&oacute; registros que cumplan con el criterio indicado (error)';
            return array();
        }
        if (empty($reg)) {
            $_SESSION["generales"]["txtemergente"] = 'No encontr&oacute; registros que cumplan con el criterio indicado (vac&iacute;o)';
            return array();
        }

        $retorno = array();
        $i = -1;
        foreach ($reg as $rg) {
            $i++;
            $retorno[$i]["matricula"] = $rg["matricula"];
            $retorno[$i]["proponente"] = ltrim(trim($rg["proponente"]), "0");
            $retorno[$i]["tipoidentificacion"] = $rg["idclase"];
            $retorno[$i]["identificacion"] = $rg["numid"];
            $retorno[$i]["numrue"] = $rg["nit"];
            $retorno[$i]["nombre"] = $rg["razonsocial"];
            $retorno[$i]["razonsocial"] = $rg["razonsocial"];
            $retorno[$i]["ape1"] = $rg["apellido1"];
            $retorno[$i]["ape2"] = $rg["apellido2"];
            $retorno[$i]["nom1"] = $rg["nombre1"];
            $retorno[$i]["nom2"] = $rg["nombre2"];
            $retorno[$i]["organizacion"] = $rg["organizacion"];
            $retorno[$i]["categoria"] = $rg["categoria"];
            $retorno[$i]["fecmat"] = $rg["fecmatricula"];
            $retorno[$i]["fecren"] = $rg["fecrenovacion"];
            $retorno[$i]["ultanoren"] = $rg["ultanoren"];
            $histopagos = encontrarHistoricoPagosMysqliApi($mysqli, $rg["matricula"], array(), array());
            if ($histopagos["fecultren"] != '') {
                $retorno[$i]["fecren"] = $histopagos["fecultren"];
                $retorno[$i]["ultanoren"] = $histopagos["ultanoren"];
            }
            $retorno[$i]["afiliacion"] = $rg["ctrafiliacion"];
            $retorno[$i]["estadomatricula"] = $rg["ctrestmatricula"];
            $retorno[$i]["estadoproponente"] = $rg["ctrestproponente"];
            $retorno[$i]["estabs"] = array();
            $retorno[$i]["embargos"] = '';
            $retorno[$i]["estadodatosmatricula"] = $rg["ctrestdatos"];
            $retorno[$i]["fecinsprop"] = '';
            $retorno[$i]["fecrenprop"] = '';
            $retorno[$i]["feccanprop"] = '';
            $retorno[$i]["saldoafiliado"] = $rg["saldoaflia"];
            $retorno[$i]["dircom"] = $rg["dircom"];
            $retorno[$i]["muncom"] = $rg["muncom"];

//añadido para wsRESTSII
            $retorno[$i]["sigla"] = $rg["sigla"];
            $retorno[$i]["nit"] = $rg["nit"];
//añadido para wsRESTSII fin
            if (ltrim(trim($rg["proponente"]), "0") != '') {
                $arr1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . ltrim($rg["proponente"], "0") . "'");
                if ($arr1 && !empty($arr1)) {
                    $retorno[$i]["fecinsprop"] = $arr1["fechaultimainscripcion"];
                    $retorno[$i]["fecrenprop"] = $arr1["fechaultimarenovacion"];
                    $retorno[$i]["feccanprop"] = $arr1["fechacancelacion"];
                }
            }

            if ($relacionestablecimientos == 'S') {
                if ($rg["categoria"] != '2' && $rg["categoria"] != '3' && $rg["organizacion"] != '02') {
                    if ($rg["matricula"] != '') {
                        $arr1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario='" . $rg["matricula"] . "'", "matricula");
                        if ($arr1 && !empty($arr1)) {
                            $j = 0;
                            foreach ($arr1 as $ar) {
                                if ($ar["codigocamara"] == '' || $ar["codigocamara"] == CODIGO_EMPRESA) {
                                    $arr2 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $ar["matricula"] . "'");
                                    if ($arr2 && !empty($arr2)) {
                                        if ($arr2["ctrestmatricula"] == 'MA' || $arr2["ctrestmatricula"] == 'MI' || $arr2["ctrestmatricula"] == 'IA' || $arr2["ctrestmatricula"] == 'II') {
                                            $j++;
                                            $retorno[$i]["estabs"][$j]["mat"] = $ar["matricula"];
                                            $retorno[$i]["estabs"][$j]["nom"] = $arr2["razonsocial"];
                                            $retorno[$i]["estabs"][$j]["est"] = $arr2["ctrestmatricula"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (ltrim($rg["matricula"], "0") != 0) {
                $retorno[$i]["embargos"] = contarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . ltrim($rg["matricula"], "0") . "' and acto IN ('0900','0940','1000','1040') and ctrestadoembargo = '1'");
            }

            $retorno[$i]["tipopropietario"] = '';
            if (ltrim($rg["matricula"], "0") != 0) {
                if ($rg["organizacion"] == '02' && $rg["ctrestmatricula"] != 'MC') {
                    $prps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $rg["matricula"] . "'", "matriculapropietario");
                    if ($prps && !empty($prps)) {
                        foreach ($prps as $prp) {
                            if ($prp["estado"] == 'V') {
                                if ($prp["matricula"] != '' && ($prp["codigocamara"] == '' || $prp["codigocamara"] == CODIGO_EMPRESA)) {
                                    $retorno[$i]["tipopropietario"] = 'Propietario local en la jurisdicción';
                                }
                            }
                        }
                    }
                    if ($retorno[$i]["tipopropietario"] == '') {
                        $retorno[$i]["tipopropietario"] = 'Propietario foraneo o no matriculado';
                    }
                }
            }
        }

        // 
        return $retorno;
    }
}

?>
