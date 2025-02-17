<?php

class funcionesSii2_liquidaciones {

    public static function rutinaLiquidacionTransacciones($dbx, $transaccion = '', $tipoliquidacion = '') {

        if (!defined('RENOVACION_SERV_FORMULARIOS_ESADL')) {
            define('RENOVACION_SERV_FORMULARIOS_ESADL', '');
        }

        //
        $txtFinal = '';

        // echo "Transaccion : " . $transaccion . '<br>';
        // *********************************************************************** //
        // Arma Tabla de servicios
        // *********************************************************************** //
        $txtServAfiliacion = '';
        $arrServs = array();
        $arrTem = retornarRegistrosMysqli2($dbx, 'mreg_servicios', "1=1", "idservicio");
        foreach ($arrTem as $t) {
            $arrServs[$t["idservicio"]] = $t;
            if ($t["grupoventas"] == '02') {
                if ($txtServAfiliacion != '') {
                    $txtServAfiliacion .= ",";
                }
                $txtServAfiliacion .= "'" . $t["idservicio"] . "'";
            }
        }

        // *********************************************************************** //
        // Arma tabla de transacciones asociadas a la liquidacion
        // *********************************************************************** //
        $_SESSION["tramite"]["txtemergente"] = '';
        $arrTrans = array();
        if ($transaccion != '') {
            $arrTem = retornarRegistroMysqli2($dbx, 'mreg_transacciones', "idcampo='" . $transaccion . "'");
            if ($arrTem === false || empty($arrTem)) {
                return false;
            } else {
                $arrTrans[1]["maetrans"] = $arrTem;
                $arrTrans[1]["dattrans"] = array();
                $arrTrans[1]["dattrans"]["idsecuencia"] = 1;
                $arrTrans[1]["dattrans"]["idtransaccion"] = $transaccion;
                $arrTrans[1]["dattrans"]["matriculaafectada"] = $_SESSION["tramite"]["idmatriculabase"];
                if (trim($arrTrans[1]["dattrans"]["matriculaafectada"]) == '') {
                    $arrTrans[1]["dattrans"]["matriculaafectada"] = $_SESSION["tramite"]["idexpedientebase"];
                }
                $arrTrans[1]["dattrans"]["tipodoc"] = '06'; // Documento privado
                $arrTrans[1]["dattrans"]["numdoc"] = 'N/A';
                $arrTrans[1]["dattrans"]["origendoc"] = 'EL COMERCIANTE';
                $arrTrans[1]["dattrans"]["mundoc"] = '';
                $arrTrans[1]["dattrans"]["camaravendedor"] = '';
                $arrTrans[1]["dattrans"]["matriculavendedor"] = '';
                $arrTrans[1]["dattrans"]["tipoidentificacionvendedor"] = '';
                $arrTrans[1]["dattrans"]["identificacionvendedor"] = '';
                $arrTrans[1]["dattrans"]["nombrevendedor"] = '';
                $arrTrans[1]["dattrans"]["nombre1vendedor"] = '';
                $arrTrans[1]["dattrans"]["nombre2vendedor"] = '';
                $arrTrans[1]["dattrans"]["apellido1vendedor"] = '';
                $arrTrans[1]["dattrans"]["apellido2vendedor"] = '';
                $arrTrans[1]["dattrans"]["emailvendedor"] = '';
                $arrTrans[1]["dattrans"]["celularvendedor"] = '';
                $arrTrans[1]["dattrans"]["cancelarvendedor"] = '';
                $arrTrans[1]["dattrans"]["camaracomprador"] = '';
                $arrTrans[1]["dattrans"]["matriculacomprador"] = '';
                $arrTrans[1]["dattrans"]["organizacioncomprador"] = '';
                $arrTrans[1]["dattrans"]["municipiocomprador"] = '';
                $arrTrans[1]["dattrans"]["tipoidentificacioncomprador"] = '';
                $arrTrans[1]["dattrans"]["identificacioncomprador"] = '';
                $arrTrans[1]["dattrans"]["nombrecomprador"] = '';
                $arrTrans[1]["dattrans"]["nombre1comprador"] = '';
                $arrTrans[1]["dattrans"]["nombre2comprador"] = '';
                $arrTrans[1]["dattrans"]["apellido1comprador"] = '';
                $arrTrans[1]["dattrans"]["apellido2comprador"] = '';
                $arrTrans[1]["dattrans"]["emailcomprador"] = '';
                $arrTrans[1]["dattrans"]["celularcomprador"] = '';
                $arrTrans[1]["dattrans"]["activoscomprador"] = 0;
                $arrTrans[1]["dattrans"]["personalcomprador"] = 0;
                $arrTrans[1]["dattrans"]["municipioanterior"] = '';
                $arrTrans[1]["dattrans"]["matriculaanterior"] = '';
                $arrTrans[1]["dattrans"]["camaraanterior"] = '';
                $arrTrans[1]["dattrans"]["fechamatriculaanterior"] = '';
                $arrTrans[1]["dattrans"]["fecharenovacionanterior"] = '';
                $arrTrans[1]["dattrans"]["ultimoanorenovadoanterior"] = '';
                $arrTrans[1]["dattrans"]["benart7anterior"] = '';
                $arrTrans[1]["dattrans"]["camaradestino"] = '';
                $arrTrans[1]["dattrans"]["organizacion"] = $_SESSION["tramite"]["organizacionbase"];
                $arrTrans[1]["dattrans"]["categoria"] = $_SESSION["tramite"]["categoriabase"];
                $arrTrans[1]["dattrans"]["razonsocial"] = $_SESSION["tramite"]["nombrebase"];
                $arrTrans[1]["dattrans"]["sigla"] = '';
                $arrTrans[1]["dattrans"]["tipoidentificacion"] = '';
                $arrTrans[1]["dattrans"]["identificacion"] = '';
                $arrTrans[1]["dattrans"]["nit"] = '';
                $arrTrans[1]["dattrans"]["prerut"] = '';
                $arrTrans[1]["dattrans"]["ape1"] = '';
                $arrTrans[1]["dattrans"]["ape2"] = '';
                $arrTrans[1]["dattrans"]["nom1"] = '';
                $arrTrans[1]["dattrans"]["nom2"] = '';
                $arrTrans[1]["dattrans"]["cargo"] = '';
                $arrTrans[1]["dattrans"]["idvinculo"] = '';
                $arrTrans[1]["dattrans"]["idrenglon"] = '';
                $arrTrans[1]["dattrans"]["aceptacion"] = '';
                $arrTrans[1]["dattrans"]["identificacionrepresentada"] = '';
                $arrTrans[1]["dattrans"]["razonsocialrepresentada"] = '';
                $arrTrans[1]["dattrans"]["pornaltot"] = 0;
                $arrTrans[1]["dattrans"]["pornalpub"] = 0;
                $arrTrans[1]["dattrans"]["pornalpri"] = 0;
                $arrTrans[1]["dattrans"]["porexttot"] = 0;
                $arrTrans[1]["dattrans"]["porextpub"] = 0;
                $arrTrans[1]["dattrans"]["porextpri"] = 0;
                $arrTrans[1]["dattrans"]["personal"] = 0;
                $arrTrans[1]["dattrans"]["activos"] = 0;
                $arrTrans[1]["dattrans"]["costotransaccion"] = 0;
                $arrTrans[1]["dattrans"]["patrimonio"] = 0;
                $arrTrans[1]["dattrans"]["benart7"] = '';
                $arrTrans[1]["dattrans"]["benley1780"] = '';
                $arrTrans[1]["dattrans"]["fechanacimientopnat"] = '';
                $arrTrans[1]["dattrans"]["capitalsocial"] = 0;
                $arrTrans[1]["dattrans"]["capitalautorizado"] = 0;
                $arrTrans[1]["dattrans"]["capitalsuscrito"] = 0;
                $arrTrans[1]["dattrans"]["capitalpagado"] = 0;
                $arrTrans[1]["dattrans"]["aporteactivos"] = 0;
                $arrTrans[1]["dattrans"]["aportedinero"] = 0;
                $arrTrans[1]["dattrans"]["aportelaboral"] = 0;
                $arrTrans[1]["dattrans"]["aportelaboraladicional"] = 0;
                $arrTrans[1]["dattrans"]["capitalasignado"] = 0;
                $arrTrans[1]["dattrans"]["acreditapagoir"] = 'N';
                $arrTrans[1]["dattrans"]["nroreciboacreditapagoir"] = '';
                $arrTrans[1]["dattrans"]["fechareciboacreditapagoir"] = '';
                $arrTrans[1]["dattrans"]["gobernacionacreditapagoir"] = '';
                $arrTrans[1]["dattrans"]["dircom"] = '';
                $arrTrans[1]["dattrans"]["municipio"] = '';
                $arrTrans[1]["dattrans"]["fechaduracion"] = '';
                $arrTrans[1]["dattrans"]["tipodisolucion"] = '';
                $arrTrans[1]["dattrans"]["tipoliquidacion"] = '';
                $arrTrans[1]["dattrans"]["motivodisolucion"] = '';
                $arrTrans[1]["dattrans"]["motivoliquidacion"] = '';
                $arrTrans[1]["dattrans"]["ciiu1"] = '';
                $arrTrans[1]["dattrans"]["ciiu2"] = '';
                $arrTrans[1]["dattrans"]["ciiu3"] = '';
                $arrTrans[1]["dattrans"]["ciiu4"] = '';
                $arrTrans[1]["dattrans"]["entidadvigilancia"] = '';
                $arrTrans[1]["dattrans"]["objetosocial"] = '';
                $arrTrans[1]["dattrans"]["facultades"] = '';
                $arrTrans[1]["dattrans"]["limitaciones"] = '';
                $arrTrans[1]["dattrans"]["poderespecial"] = '';
                $arrTrans[1]["dattrans"]["texto"] = '';
                $arrTrans[1]["dattrans"]["cantidad"] = 1;
                $arrTrans[1]["dattrans"]["cantidadadicional"] = 0;
            }
        } else {
            $i = 0;
            foreach ($_SESSION["tramite"]["transacciones"] as $t) {
                $i++;
                $arrTem = retornarRegistroMysqli2($dbx, 'mreg_transacciones', "idcampo='" . $t["idtransaccion"] . "'");
                if ($arrTem === false || empty($arrTem)) {
                    $_SESSION["tramite"]["txtemergente"] .= 'Transacción ' . $t["idtransaccion"] . ' no encontrada en el maestro de transacciones\r\n';
                } else {
                    $arrTrans[$i]["maetrans"] = $arrTem;
                    $arrTrans[$i]["dattrans"] = $t;
                }
            }
        }
        if (trim($_SESSION["tramite"]["txtemergente"]) != '') {
            return false;
        }

        // ********************************************************************************** //
        // Verifica si la camara liquida o no impuesto de registro
        // Encuentra servicios, porcentajes y valores
        // ********************************************************************************** //	
        $liquidarIR = retornarClaveValorSii2($dbx, '90.01.35');

        // Servicios para el cálculo del impuesto de registro
        $sir_regmer_cuantia = retornarClaveValorSii2($dbx, '90.27.53');
        $sir_regmer_sincuantia = retornarClaveValorSii2($dbx, '90.27.51');
        $sir_regesadl_cuantia = retornarClaveValorSii2($dbx, '90.27.63');
        $sir_regesadl_sincuantia = retornarClaveValorSii2($dbx, '90.27.61');

        // Servicios para el cálculo de la mora del impuesto de registro
        $smir_regmer_cuantia = retornarClaveValorSii2($dbx, '90.27.57');
        $smir_regmer_sincuantia = retornarClaveValorSii2($dbx, '90.27.55');
        $smir_regesadl_cuantia = retornarClaveValorSii2($dbx, '90.27.67');
        $smir_regesadl_sincuantia = retornarClaveValorSii2($dbx, '90.27.65');

        // *********************************************************************** //
        // Inicializa datos de la liquidación
        // *********************************************************************** //

        $_SESSION["tramite"]["liquidacion"] = array();
        $_SESSION["tramite"]["valorbruto"] = 0;
        $_SESSION["tramite"]["valoriva"] = 0;
        $_SESSION["tramite"]["valortotal"] = 0;
        $_SESSION["tramite"]["valorbaseiva"] = 0;
        $_SESSION["tramite"]["valorimpregistro"] = 0;


        // ************************************************************ //
        // Cuando el trámite sea renovación de matrícula
        // ************************************************************ //
        $ley1780 = array();
        $iley1780 = 0;
        $liquidarLey1780 = 'NO';

        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            //
            if (!defined('TIPO_AFILIACION')) {
                define('TIPO_AFILIACION', 'TARIFAGENERAL');
            }

            //
            $actprin = 0;
            $actest = 0;
            if (!defined('RENOVACION_ACTIVO_MINIMO')) {
                $actmin = 0;
            } else {
                if (trim(RENOVACION_ACTIVO_MINIMO) == '') {
                    $actmin = 0;
                } else {
                    $actmin = RENOVACION_ACTIVO_MINIMO;
                }
            }

            if (!defined('RENOVACION_ACTIVO_MINIMO_ESADL')) {
                $actminesadl = 0;
            } else {
                if (trim(RENOVACION_ACTIVO_MINIMO_ESADL) == '') {
                    $actminesadl = 0;
                } else {
                    $actminesadl = RENOVACION_ACTIVO_MINIMO_ESADL;
                }
            }

            if (!defined('RENOVACION_PORCENTAJE_DISMINUCION')) {
                $porcentajedisminucion = 0;
            } else {
                if (trim(RENOVACION_PORCENTAJE_DISMINUCION) == '') {
                    $porcentajedisminucion = 0;
                } else {
                    $porcentajedisminucion = intval(RENOVACION_PORCENTAJE_DISMINUCION);
                }
            }

            $_SESSION ["generales"] ["txtemergente"] = '';
            $cantAnos = 0;
            $cantRenovar = 0;
            $arrForms = array();
            $iRenglon = 0;

            foreach ($_SESSION ["tramite"] ["expedientes"] as $matri) {
                $iRenglon++;
                $cantAnos++;

                //
                if (!isset($arrForms [$matri ["matricula"]])) {
                    $arrForms [$matri ["matricula"]] = 0;
                }

                //
                if ($matri ["renovaresteano"] != 'si') {
                    if ($matri ["renovaresteano"] == 'no') {
                        $arrForms [$matri ["matricula"]] = 1;
                    }
                    if ($matri ["nuevosactivos"] != 0) {
                        if ($tipoliquidacion != 'consulta') {
                            $_SESSION ["generales"] ["txtemergente"] .= 'Se indicó que el año ' . $matri ["ultimoanorenovado"] . ' para la matrícula ' . $matri ["matricula"] . ' no será renovado, los activos deberán ser ceros\n';
                        } else {
                            $txtFinal .= 'Se indicó que el año ' . $matri ["ultimoanorenovado"] . ' para la matrícula ' . $matri ["matricula"] . ' no será renovado, los activos deberán ser ceros<br>';
                        }
                    }
                }

                if ($tipoliquidacion != 'consulta') {
                    if ($txtFinal != '') {
                        return false;
                    }
                }

                //
                if ($matri ["renovaresteano"] == 'si') {
                    if ($arrForms [$matri ["matricula"]] == 1) {
                        if ($tipoliquidacion != 'consulta') {
                            $_SESSION ["generales"] ["txtemergente"] .= 'Los años a renovar de la matrícula ' . $matri ["matricula"] . ' deben ser consecutivos (No dejar huecos - años sin renovar)\n';
                        } else {
                            $txtFinal .= 'Los años a renovar de la matrícula ' . $matri ["matricula"] . ' deben ser consecutivos (No dejar huecos - años sin renovar)<br>';
                        }
                    }
                    $cantRenovar ++;
                    if ($matri ["ultimoanorenovado"] == date("Y")) {
                        if (($matri ["organizacion"] == '01') || (($matri ["organizacion"] > '02') && (($matri ["categoria"] == '0') || ($matri ["categoria"] == '1')))) {
                            $actprin = $actprin + $matri ["nuevosactivos"];
                        } else {
                            $actest = $actest + $matri ["nuevosactivos"];
                        }
                    }
                    if (!is_numeric($matri ["nuevosactivos"])) {
                        if ($tipoliquidacion != 'consulta') {
                            $_SESSION ["generales"] ["txtemergente"] .= 'Activos erróneos para la matrícula ' . $matri ["matricula"] . ' en el año ' . $matri ["ultimoanorenovado"] . '\n';
                        } else {
                            $txtFinal .= 'Los activos para la matrícula ' . $matri ["matricula"] . ' en el año ' . $matri ["ultimoanorenovado"] . ' no están correctamente digitados<br>';
                        }
                    }

                    //
//
                    $aceptavalor = RENOVACION_ACEPTAR_VALOR;
                    if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
                        if (defined('RENOVACION_ACEPTAR_VALOR_USUPUBXX') &&
                                RENOVACION_ACEPTAR_VALOR_USUPUBXX != '') {
                            $aceptavalor = RENOVACION_ACEPTAR_VALOR_USUPUBXX;
                        }
                    }
                    if ($aceptavalor == "igual") {
                        if ($matri ["nuevosactivos"] < $matri ["ultimosactivos"]) {
                            if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                if ($tipoliquidacion != 'consulta') {
                                    $_SESSION ["generales"] ["txtemergente"] .= 'Activos nuevos deben ser IGUALES O SUPERIORES a los últimos reportados para la matrícula ' . $matri ["matricula"] . ' en el año ' . $matri ["ultimoanorenovado"] . '\n';
                                } else {
                                    $txtFinal .= 'Los activos con los que se va a renovar la matrícula ' . $matri ["matricula"] . ' en el año ' . $matri ["ultimoanorenovado"] . ' son menores a los de la última renovación, por favor tenga esto en cuenta pues ';
                                    $txtFinal .= 'puede tener inconvenientes al momento de presentar su trámite. La Cámara podrá solicitarle la documentación necesaria para justificar la disminución.<br>';
                                }
                            }
                        }
                    }
                    if ($aceptavalor == "mayor") {
                        if ($matri ["nuevosactivos"] <= $matri ["ultimosactivos"]) {
                            if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                if ($tipoliquidacion != 'consulta') {
                                    $_SESSION ["generales"] ["txtemergente"] .= 'Activos nuevos deben ser SUPERIORES a los últimos reportados para la matrícula ' . $matri ["matricula"] . ' en el año ' . $matri ["ultimoanorenovado"] . '\n';
                                } else {
                                    $txtFinal .= 'Los activos con los que se va a renovar la matrícula ' . $matri ["matricula"] . ' en el año ' . $matri ["ultimoanorenovado"] . ' son menores a los de la última renovación, por favor tenga esto en cuenta pues ';
                                    $txtFinal .= 'puede tener inconvenientes al momento de presentar su trámite o la Cámara puede solicitarle la documentación necesaria para justificar la disminución.<br>';
                                }
                            }
                        }
                    }
                    if ($aceptavalor == "menor") {
                        if ($porcentajedisminucion != 0) {

                            $variacion = $matri ["ultimosactivos"] * $porcentajedisminucion / 100;
                            if ($matri ["nuevosactivos"] < ($matri ["ultimosactivos"] - $variacion)) {
                                if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                    if ($tipoliquidacion != 'consulta') {
                                        $_SESSION ["generales"] ["txtemergente"] .= 'Matrícula No. ' . $matri ["matricula"] . ', año ' . $matri ["ultimoanorenovado"] . '. Señor usuario  Usted está renovando  con activos inferiores a los reportados en su anterior renovación, por tanto, debe  anexar  el balance  con corte a Diciembre 31 del año inmediatamente anterior, debidamente suscrito  por Contador Público,  para efectos de validar la información, (Artículo 36 Código de Comercio)\n';
                                    } else {
                                        $txtFinal .= 'Los activos con los que se va a renovar la matrícula ' . $matri ["matricula"] . ' en el año ' . $matri ["ultimoanorenovado"] . ' son menores a los de la última renovación, por favor tenga esto en cuenta pues ';
                                        $txtFinal .= 'puede tener inconvenientes al momento de presentar su trámite o la Cámara puede solicitarle la documentación necesaria para justificar la disminución.<br>';
                                    }
                                }
                            }
                        }
                    }



                    if (trim($_SESSION ["generales"] ["txtemergente"]) == '') {
                        if ($matri ["ultimoanorenovado"] == date("Y")) {
                            if (($matri ["organizacion"] != '12') && ($matri ["organizacion"] != '14')) {
                                if ($matri ["nuevosactivos"] < $actmin) {
                                    if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                        if ($tipoliquidacion != 'consulta') {
                                            $_SESSION ["generales"] ["txtemergente"] .= 'Matrícula No. ' . $matri ["matricula"] . ', año ' . $matri ["ultimoanorenovado"] . '. Señor Usuario: Teniendo en cuenta el valor del activo ingresado para la renovación, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir información   y proceder a  efectuar  el trámite  respectivo.\n';
                                        } else {
                                            $txtFinal .= 'Los activos con los que se está liquindado la renovación de la matrícula No. ' . $matri ["matricula"] . ', ';
                                            $txtFinal .= 'año ' . $matri ["ultimoanorenovado"] . ', son cero o inferiores a los reportados en la última renovación, ';
                                            $txtFinal .= 'esto puede traerle inconvenientes al momento de realizar su trámite. Es posible que la Cámara ';
                                            $txtFinal .= 'le solicite información adicional para justificar este hecho.<br>';
                                        }
                                    }
                                }
                            } else {
                                if (($matri ["categoria"] == '2') || ($matri ["categoria"] == '3')) {
                                    if ($matri ["nuevosactivos"] < $actmin) {
                                        if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                            if ($tipoliquidacion != 'consulta') {
                                                $_SESSION ["generales"] ["txtemergente"] .= 'Matrícula No. ' . $matri ["matricula"] . ', año ' . $matri ["ultimoanorenovado"] . '. Señor Usuario: Teniendo en cuenta el valor del activo ingresado para la renovación, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir información   y proceder a  efectuar  el trámite  respectivo.\n';
                                            } else {
                                                $txtFinal .= 'Matrícula No. ' . $matri ["matricula"] . ', año ' . $matri ["ultimoanorenovado"] . '. Señor Usuario: Teniendo en cuenta el valor del activo ingresado para la renovación, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir información   y proceder a  efectuar  el trámite  respectivo.<br>';
                                            }
                                        }
                                    }
                                } else {
                                    if ($matri ["nuevosactivos"] < $actminesadl) {
                                        if ($_SESSION ["generales"] ["escajero"] != 'SI') {
                                            if ($tipoliquidacion != 'consulta') {
                                                $_SESSION ["generales"] ["txtemergente"] .= 'Matrícula No. ' . $matri ["matricula"] . ', año ' . $matri ["ultimoanorenovado"] . '. Señor Usuario: Teniendo en cuenta el valor del activo ingresado para la renovación, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir información   y proceder a  efectuar  el trámite  respectivo.\n';
                                            } else {
                                                $txtFinal .= 'Matrícula No. ' . $matri ["matricula"] . ', año ' . $matri ["ultimoanorenovado"] . '. Señor Usuario: Teniendo en cuenta el valor del activo ingresado para la renovación, le sugerimos presentarse  a cualquiera de nuestras sedes para recibir información   y proceder a  efectuar  el trámite  respectivo.<br>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }


            if ($tipoliquidacion != 'consulta') {
                if ($_SESSION ["generales"] ["txtemergente"] != '') {
                    return false;
                }
            }

            if ($actprin > 0) {
                if ($actprin < $actest) {
                    $_SESSION ["generales"] ["txtemergente"] = 'El activo de la persona natural o sociedad principal NO DEBE ser inferior a la sumatoria de los activos de sus establecimientos de comercio\n';
                    return false;
                }
            }

            $_SESSION ["generales"] ["txtemergente"] = '';
            // $txt1 = '';
            // Liquida el costo de la renovacion por cada matricula
            $i = 0;
            $arregloDesc = array();
            $idesc = 0;
            $tarianteriorafiliacion = 0;
            $baseanteriorafiliacion = 0;

            foreach ($_SESSION ["tramite"] ["expedientes"] as $matri) {

                if ($matri ["renovaresteano"] == 'si') {

                    $i ++;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cc"] = $matri ["cc"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $matri ["matricula"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $matri ["razonsocial"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $matri ["organizacion"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $matri ["categoria"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["afiliado"] = $matri ["afiliado"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ultimoanoafiliado"] = $matri ["ultimoanoafiliado"];

                    if ($matri ["organizacion"] == '01') {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_PNAT;
                    }
                    if (($matri ["organizacion"] == '02') && ($matri ["propietariojurisdiccion"] == 'S')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_EST_JUR;
                    }
                    if (($matri ["organizacion"] == '02') && ($matri ["propietariojurisdiccion"] == 'N')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_EST_NOJUR;
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '0')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_PJUR;
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '1')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_PJUR;
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '2') && ($matri ["propietariojurisdiccion"] == 'S')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_SUC_JUR;
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '2') && ($matri ["propietariojurisdiccion"] == 'N')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_SUC_NOJUR;
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '3') && ($matri ["propietariojurisdiccion"] == 'S')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_AGE_JUR;
                    }
                    if (($matri ["organizacion"] > '02') && ($matri ["categoria"] == '3') && ($matri ["propietariojurisdiccion"] == 'N')) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_AGE_NOJUR;
                    }

                    // En caso de entidades sin Animo de Lucro
                    if (($matri ["organizacion"] == '12') || ($matri ["organizacion"] == '14')) {
                        if (($matri ["categoria"] == '0') || ($matri ["categoria"] == '1')) {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = retornarClaveValorSii2($dbx, '90.25.15');
                        }
                    }
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = $matri ["ultimoanorenovado"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = $matri ["nuevosactivos"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["reliquidacion"] = $matri ["reliquidacion"];

                    // base para el calculo de afiliaciones anteriormente liquidadas (en reliquidaciones)
                    if ($matri ["reliquidacion"] == 'si') {
                        if ($matri ["organizacion"] == '01' || $matri ["organizacion"] > '02') {
                            if ($matri ["categoria"] == '' || $matri ["categoria"] == '0' || $matri ["categoria"] == '1' || $matri ["categoria"] == '2') {
                                $baseanteriorafiliacion = $matri ["ultimosactivos"];
                            }
                        }
                    }

                    //
                    $tari = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $matri ["ultimoanorenovado"], 1, $matri ["nuevosactivos"]);
                    $tarianterior = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $matri ["ultimoanorenovado"], 1, $matri ["ultimosactivos"]);
                    $okbeneficio = $matri ["benart7"];
                    $okbeneficio1780 = $matri ["benley1780"];
                    if ($okbeneficio1780 == 'S') {
                        if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'N' ||
                                $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'N') {
                            $okbeneficio1780 = 'P';
                        } else {
                            if ($_SESSION["tramite"]["renunciobeneficiosley1780"] == 'S') {
                                $okbeneficio1780 = 'R';
                            }
                        }
                    }

                    // En caso de esadl que sean veedurias personas naturales y esadl extranjeras
                    // tarifa de renovacion en cero
                    if (($matri ["organizacion"] == '12')) {
                        if (($matri ["categoria"] == '0') || ($matri ["categoria"] == '1')) {
                            if (($matri ["claseespesadl"] == '61') || ($matri ["claseespesadl"] == '62')) {
                                $tari = 0;
                                $tarianterior = 0;
                            }
                        }
                    }

                    // En caso de economia solidaria que sean cooperativas y precooperativas de trabajo asociado (49) o
                    // Ongs (extranjeras (61), cooperativas de educacion (63)
                    // tarifa de renovacion en cero
                    if (($matri ["organizacion"] == '14')) {
                        if (($matri ["categoria"] == '0') || ($matri ["categoria"] == '1')) {
                            if ($matri ["claseespesadl"] == '49' || $matri ["claseespesadl"] == '61'
                            // || $matri ["claseespesadl"] == '63'
                            ) {
                                $tari = 0;
                                $tarianterior = 0;
                            }
                        }
                    }

                    //
                    $fecmatcontrol = $matri ["fechamatricula"];
                    if (ltrim($matri ["fecmatant"], "0") != '') {
                        $fecmatcontrol = $matri ["fecmatant"];
                    }

                    // **************************************************************************** //
                    // ******** AJUSTES LEY 1780 APLICABLES PARA EL AÑO 2019 EN ADELANTE ********** //
                    // **************************************************************************** //
                    // 2018-12-21   
                    // Si el comerciante se matriculo en el 2018, y renueva a tiempo o está reliquidando
                    // Se debe verificar si es beneficiario de la ley 1780 para aplicar el descuento sobre la reliquidación
                    $tanoren = date("Y");
                    $tanorenant = $tanoren - 1;
                    $aplico1780 = 'no';
                    if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'S' && $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'S' && $_SESSION["tramite"]["renunciobeneficiosley1780"] == 'N') {
                        if ($fecmatcontrol >= $tanorenant . '0101') {
                            if ($matri ["benley1780"] == 'S') {
                                if ($_SESSION ["tramite"] ["numeroempleados"] <= 50 && $matri ["nuevosactivos"] <= $_SESSION ["generales"] ["smmlv"] * 5000) {
                                    $liquidarLey1780 = 'SI';
                                    $tari1 = $tari;
                                    $tarianterior1 = $tarianterior;
                                    if ($matri ["reliquidacion"] == 'si') {
                                        $tari1 = $tari1 - $tarianterior1;
                                    }
                                    $iley1780 ++;
                                    $ley1780 [$iley1780] = array();
                                    $ley1780 [$iley1780]["idsec"] = '000';
                                    $ley1780 [$iley1780]["porcentaje"] = 100;
                                    $ley1780 [$iley1780]["activos"] = $matri ["nuevosactivos"];
                                    $ley1780 [$iley1780] ["cc"] = $matri ["cc"];
                                    $ley1780 [$iley1780] ["expediente"] = $matri ["matricula"];
                                    $ley1780 [$iley1780]["nombre"] = $matri ["razonsocial"];
                                    $ley1780 [$iley1780]["organizacion"] = $matri ["organizacion"];
                                    $ley1780 [$iley1780]["categoria"] = $matri ["categoria"];
                                    $ley1780 [$iley1780]["valorservicio"] = $tari1;
                                    $ley1780 [$iley1780]["servicio"] = retornarClaveValorSii2($dbx, '90.27.41');
                                    $ley1780 [$iley1780] ["afiliado"] = $matri ["afiliado"];
                                    $ley1780 [$iley1780] ["ultimoanoafiliado"] = $matri ["ultimoanoafiliado"];
                                    $ley1780 [$iley1780] ["ano"] = $matri ["ultimoanorenovado"];
                                    $ley1780 [$iley1780] ["cantidad"] = 1;
                                    $ley1780 [$iley1780] ["valorbase"] = $matri ["nuevosactivos"];
                                    $ley1780 [$iley1780] ["reliquidacion"] = $matri ["reliquidacion"];
                                    if ($ley1780 [$iley1780] ["valorservicio"] < 0) {
                                        $ley1780 [$iley1780] ["valorservicio"] = 0;
                                    }
                                    $aplico1780 = 'si';
                                } else {
                                    $okbeneficio1780 = 'P';
                                }
                            }
                        }
                    } else {
                        if (isset($_SESSION["tramite"]["expedientes"][1]["benley1780"])) {
                            if ($_SESSION["tramite"]["expedientes"][1]["benley1780"] == 'S') {
                                if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] == 'N' ||
                                        $_SESSION["tramite"]["mantengorequisitosbenley1780"] == 'N') {
                                    $okbeneficio1780 = 'P';
                                } else {
                                    if ($_SESSION["tramite"]["renunciobeneficiosley1780"] == 'S') {
                                        $okbeneficio1780 = 'R';
                                    }
                                }
                            } else {
                                $okbeneficio1780 = 'N';
                            }
                        } else {
                            $okbeneficio1780 = 'N';
                        }
                    }


                    //
                    if ($matri ["reliquidacion"] == 'si') {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $tari - $tarianterior;
                    } else {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $tari;
                    }
                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'S';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["benart7"] = $okbeneficio;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["benaley1780"] = $okbeneficio1780;
                    if ($_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] < 0) {
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = 0;
                    }
                }
            }

            // ***********************************************************************//
            // Aplica descuentos de Ley 1429 solamente si se van a renovar todos los años 
            // (al dia)
            // No aplica para renovaciones parciales
            // ***********************************************************************//
            if ($idesc > 0) {
                if ($cantAnos == $cantRenovar) {
                    foreach ($arregloDesc as $dsc) {
                        $i ++;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] = $dsc;
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                    }
                }
            }
        }


        // ***********************************************************************//
        // Evalua transacciones del trámite
        // ***********************************************************************//

        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        $_SESSION["generales"]["txtemergente"] = '';
        $txtErrores = '';

        //
        foreach ($arrTrans as $t) {

            // ********************************************************************************** //
            // Valida que la transacción tenga correctamente parametrizados los servicios
            // ********************************************************************************** //
            if (trim($t["maetrans"]["idservicio1"]) == '' &&
                    trim($t["maetrans"]["idservicio2"]) == '' &&
                    trim($t["maetrans"]["idservicio3"]) == '' &&
                    trim($t["maetrans"]["idservicio4"]) == '' &&
                    trim($t["maetrans"]["idservicio5"]) == '' &&
                    trim($t["maetrans"]["idservicio6"]) == '' &&
                    trim($t["maetrans"]["idservicio7"]) == '' &&
                    trim($t["maetrans"]["idservicio8"])) {
                $txtErrores .= "Error en la transacción No. " . $t["idtransaccion"] . ", no tiene parametrizados los servicios asociados.\r\n";
            }

            //
            if (trim($txtErrores) != '') {
                $_SESSION["generales"]["txtemergente"] = $txtErrores . "\r\n" . "\r\n";
                $_SESSION["generales"]["txtemergente"] .= "Por favor informe esta situación al administrador del sistema.";
                return false;
            }
        }

        // ***********************************************************************//
        // Inicializa variables relacionadas con Decreto Ley 1820
        // ***********************************************************************//

        $liquidarDecreto1820 = 'NO';
        $liquidarDecreto658 = 'NO';
        $decreto1820 = array();
        $idecreto1820 = 0;
        $decreto658 = array();
        $idecreto658 = 0;

        // ******************************************************************************************   //
        // Liquida transaccion por transaccion, los servicios base, el impuesto de registro y la mora   //
        // Al igual que los beneficios de Ley 1429, Decreto 1820 y Ley 1780                             //
        // ******************************************************************************************   //
        foreach ($arrTrans as $t) {

            // 2017-05-31: Se carga el expedienmte afectado desde mreg_est_inscritos para tener disponibles esn la variable $expMat el mismo en caso de
            // alguna necesidad.

            $expMat = false;
            if (ltrim(trim($t["dattrans"]["matriculaafectada"]), "0") != '') {
                if ($t["dattrans"]["matriculaafectada"] != 'NUEVANAT' &&
                        $t["dattrans"]["matriculaafectada"] != 'NUEVAEST' &&
                        $t["dattrans"]["matriculaafectada"] != 'NUEVAJUR' &&
                        $t["dattrans"]["matriculaafectada"] != 'NUEVAESA' &&
                        $t["dattrans"]["matriculaafectada"] != 'NUEVASUC' &&
                        $t["dattrans"]["matriculaafectada"] != 'NUEVAAGE' &&
                        $t["dattrans"]["matriculaafectada"] != 'NUEVACIV'
                ) {
                    $expMat = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . $t["dattrans"]["matriculaafectada"] . "'");
                }
            }

            $liquidarDecreto1820 = 'NO';
            $liquidarLey1780 = 'NO';
            $liquidarDecreto658 = 'NO';

            //
            $_SESSION["tramite"]["valorimpregistro"] = 0;

            // 2016-05-14 : JINT
            // Revisa si alguna de las transacciones implica beneficio de la Ley 1780 de 2016
            // Sobre la base del concepto de matrícula mercantil
            $okbeneficio1780 = '';
            if ($t["maetrans"]["solicitar_datos1780"] == 'S') {
                if ($t["dattrans"]["benley1780"] == 'S') {
                    $okbeneficio1780 = 'S';
                    $liquidarLey1780 = 'SI';
                    $iley1780++;
                    $ley1780[$iley1780] = array();
                    $ley1780[$iley1780]["tipotramite"] = $t["maetrans"]["tipotramite"];
                    $ley1780[$iley1780]["idsec"] = $t["dattrans"]["idsecuencia"];
                    $ley1780[$iley1780]["porcentaje"] = 100;
                    if (isset($t["dattrans"]["activoscomprador"]) && $t["dattrans"]["activoscomprador"] != 0) {
                        $ley1780[$iley1780]["activos"] = $t["dattrans"]["activoscomprador"];
                        // $ley1780[$iley1780]["expediente"] = 'NUEVANAT';
                    } else {
                        $ley1780[$iley1780]["activos"] = $t["dattrans"]["activos"];
                    }
                    $ley1780[$iley1780]["nombre"] = $t["dattrans"]["razonsocial"];
                    $ley1780[$iley1780]["organizacion"] = $t["dattrans"]["organizacion"];
                    $ley1780[$iley1780]["categoria"] = $t["dattrans"]["categoria"];
                    $ley1780[$iley1780]["servicio"] = '';
                    $ley1780[$iley1780]["valorservicio"] = 0;
                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                        $ley1780[$iley1780]["servicio"] = retornarClaveValorSii2($dbx, '90.27.41');
                        $servX = '01020101';
                    } else {
                        $ley1780[$iley1780]["servicio"] = retornarClaveValorSii2($dbx, '90.27.40');
                        $servX = '01020101';
                    }
                    $ley1780[$iley1780]["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $servX, date("Y"), 1, $ley1780[$iley1780]["activos"]);
                }
            } else {
                if ($t["maetrans"]["nombrecorto"] == 'CONTRACV') {
                    if ($t["dattrans"]["benley1780"] == 'S') {
                        $okbeneficio1780 = 'S';
                        $liquidarLey1780 = 'SI';
                        $iley1780++;
                        $ley1780[$iley1780] = array();
                        $ley1780[$iley1780]["tipotramite"] = $t["maetrans"]["tipotramite"];
                        $ley1780[$iley1780]["idsec"] = $t["dattrans"]["idsecuencia"];
                        $ley1780[$iley1780]["porcentaje"] = 100;
                        $ley1780[$iley1780]["activos"] = $t["dattrans"]["activoscomprador"];
                        $ley1780[$iley1780]["nombre"] = $t["dattrans"]["razonsocial"];
                        $ley1780[$iley1780]["organizacion"] = $t["dattrans"]["organizacion"];
                        $ley1780[$iley1780]["categoria"] = $t["dattrans"]["categoria"];
                        $ley1780[$iley1780]["servicio"] = '';
                        $ley1780[$iley1780]["valorservicio"] = 0;
                        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                            $ley1780[$iley1780]["servicio"] = retornarClaveValorSii2($dbx, '90.27.41');
                            $servX = '01020101';
                        } else {
                            $ley1780[$iley1780]["servicio"] = retornarClaveValorSii2($dbx, '90.27.40');
                            $servX = '01020101';
                        }
                        $ley1780[$iley1780]["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $servX, date("Y"), 1, $ley1780[$iley1780]["activos"]);
                    }
                }
            }

            // 2015-12-17 : JINT
            // Revisa si alguna de las transacciones implica beneficio del decreto 1820 de 2015
            // Sobre la base del concepto de matrícula mercantil
            if ($liquidarLey1780 == 'NO') {
                if ($t["maetrans"]["solicitar_datos1820"] == 'S') {
                    $xMun = '';
                    if ($t["dattrans"]["municipio"] != '') {
                        $xMun = $t["dattrans"]["municipio"];
                    } else {
                        if ($t["dattrans"]["mundoc"] != '') {
                            $xMun = $t["dattrans"]["mundoc"];
                        }
                    }
                    if ($xMun != '') {
                        $arrTemY = retornarRegistroMysqli2($dbx, 'mreg_municipiosjurisdiccion', "idcodigo='" . $xMun . "'");
                        if ($arrTemY && $arrTemY["excencionmatricula"] != 0) {
                            $liquidarDecreto1820 = 'SI';
                            $idecreto1820++;
                            $decreto1820[$idecreto1820] = array();
                            $decreto1820[$idecreto1820]["idsec"] = $t["dattrans"]["idsecuencia"];
                            $decreto1820[$idecreto1820]["porcentaje"] = $arrTemY["excencionmatricula"];
                            $decreto1820[$idecreto1820]["activos"] = $t["dattrans"]["activos"];
                            $decreto1820[$idecreto1820]["nombre"] = $t["dattrans"]["razonsocial"];
                            $decreto1820[$idecreto1820]["organizacion"] = $t["dattrans"]["organizacion"];
                            $decreto1820[$idecreto1820]["categoria"] = $t["dattrans"]["categoria"];
                            $decreto1820[$idecreto1820]["servicio"] = '';
                            $decreto1820[$idecreto1820]["valorservicio"] = 0;
                            if ($t["dattrans"]["organizacion"] == '01') {
                                $decreto1820[$idecreto1820]["servicio"] = retornarClaveValorSii2($dbx, '90.27.30');
                                $servX = '01020101';
                            }
                            if ($t["dattrans"]["organizacion"] == '02') {
                                $decreto1820[$idecreto1820]["servicio"] = retornarClaveValorSii2($dbx, '90.27.32');
                                $servX = '01020102';
                            }
                            if ($t["dattrans"]["organizacion"] > '02' && ($t["dattrans"]["categoria"] == '2' || $t["dattrans"]["categoria"] == '3')) {
                                $decreto1820[$idecreto1820]["servicio"] = retornarClaveValorSii2($dbx, '90.27.32');
                                $regx = retornarRegistroMysqli2('mreg_municipiosjurisdiccion', "idcodigo='" . $t["dattrans"]["mundoc"] . "'");
                                if ($regx && !empty($regx)) {
                                    $servX = '01020102';
                                } else {
                                    $servX = '01020105';
                                }
                            }

                            if ($t["dattrans"]["organizacion"] > '02' && ($t["dattrans"]["categoria"] == '0' || $t["dattrans"]["categoria"] == '1')) {
                                $decreto1820[$idecreto1820]["servicio"] = retornarClaveValorSii2($dbx, '90.27.31');
                                $servX = '01020101';
                            }
                            $decreto1820[$idecreto1820]["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $servX, date("Y"), 1, $decreto1820[$idecreto1820]["activos"]);
                        }
                    }
                }
            }

            // Encuentra valores y porcentajes del impuesto de registro
            $v_regmer_sincuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.81')); // Valor registro IR - Regmer - Sin cuantía
            //
            //
            if ($t["maetrans"]["solicitar_datosconstitucion"] == 'S') {
                $v_regmer_cuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.79')); // % registro IR - Regmer - Con cuantía - Constituciones            
                if ($v_regmer_cuantia == 0) {
                    $v_regmer_cuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.80')); // % registro IR - Regmer - Con cuantía - Otros
                }
            } else {
                $v_regmer_cuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.80')); // % registro IR - Regmer - Con cuantía - Otros
            }
            $v_regmer_cuantiaD = doubleval(retornarClaveValorSii2($dbx, '90.27.78')); // % registro IR - Regmer - Con cuantía - Tarifa especial similar a constituciones    
            //
            $v_regesadl_sincuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.84')); // Valor registro IR - regEsadl - Sin cuantía

            if ($t["maetrans"]["solicitar_datosconstitucion"] == 'S') {
                $v_regesadl_cuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.82')); // % registro IR - RegEsadl - Con cuantía - Constituciones                        
                if ($v_regesadl_cuantia == 0) {
                    $v_regesadl_cuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.83')); // % registro IR - RegEsadl - Con cuantía
                }
            } else {
                $v_regesadl_cuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.83')); // % registro IR - RegEsadl - Con cuantía
            }
            $v_regesadl_cuantiaD = doubleval(retornarClaveValorSii2($dbx, '90.27.85')); // % registro IR - RegEsadl - Con cuantía - Tarifa especial similar a constituciones                        
            //
            //
            $vm_regmer_sincuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.87')); // % mora IR - RegMer - sin cuantía
            $vm_regmer_cuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.86')); // % mora IR - RegMer - con cuantía
            $vm_regesadl_sincuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.89')); // % mora IR - RegEsadl - sin cuantía
            $vm_regesadl_cuantia = doubleval(retornarClaveValorSii2($dbx, '90.27.88')); // % mora IR - RegEsadl - con cuantía
            //
            // Verifique si existe parametrización de I.R para el municipio
            $arrIR = retornarRegistroMysqli2($dbx, 'mreg_impregistro', "codigomunicipio='" . $t["dattrans"]["mundoc"] . "'");

            if ($arrIR && !empty($arrIR)) {
                $v_regmer_sincuantia = $arrIR["valorimpregsincuarmer"]; // Valor registro IR - Regmer - Sin cuantía
                if ($t["maetrans"]["solicitar_datosconstitucion"] == 'S') {
                    $v_regmer_cuantia = $arrIR["porcimpregconcuarmer"]; // % registro IR - Regmer - Con cuantía - Tartifa constitucion
                } else {
                    $v_regmer_cuantia = $arrIR["porcimpregconcuarmerotros"]; // % registro IR - Regmer - Con cuantía  - Tarifa estandard
                }
                $v_regmer_cuantiaD = $arrIR["porcimpregconcuarmerdiferencial"]; // % registro IR - Regmer - Con cuantía - Especial
                //
                $v_regesadl_sincuantia = $arrIR["valorimpregsincuaesadl"]; // Valor registro IR - regEsadl - Sin cuantía
                if ($t["maetrans"]["solicitar_datosconstitucion"] == 'S') {
                    $v_regesadl_cuantia = $arrIR["porcimpregconcuaesadl"]; // % registro IR - RegEsadl - Con cuantía - Tarifa constitucion
                } else {
                    $v_regesadl_cuantia = $arrIR["porcimpregconcuaesadlotros"]; // % registro IR - RegEsadl - Con cuantía - Tarifa estandard
                }
                $v_regesadl_cuantiaD = $arrIR["porcimpregconcuaesadldiferencial"]; // % registro IR - RegEsadl - Con cuantía - Especial
                //
                $vm_regmer_sincuantia = $arrIR["porcmoraimpregrmer"]; // % mora IR - RegMer - sin cuantía
                $vm_regmer_cuantia = $arrIR["porcmoraimpregrmer"]; // % mora IR - RegMer - con cuantía
                $vm_regesadl_sincuantia = $arrIR["porcmoraimpregesadl"]; // % mora IR - RegEsadl - sin cuantía
                $vm_regesadl_cuantia = $arrIR["porcmoraimpregesadl"]; // % mora IR - RegEsadl - con cuantía
            }

            //
            $controlliquidacion = 'si';
            if ($t["maetrans"]["idservicio1"] == '') {
                $controlliquidacion = 'no';
            }
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["condicionservicio1"] != '') {
                    eval($t["maetrans"]["condicionservicio1"]);
                }
            }

            //
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["idservicio1"] != '') {
                    $serv = $t["maetrans"]["idservicio1"];
                    $temServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $serv . "'");
                    if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' ||
                            ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                        $i++;
                        $j = $i;
                        $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicio($dbx, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, 1);
                    }
                    if ($t["dattrans"]["acreditapagoir"] == 'N') {
                        if ($liquidarIR == 'SI' && ($t["maetrans"]["ir1"] == 'S' || $t["maetrans"]["ir1"] == 'C' || $t["maetrans"]["ir1"] == 'D')) {
                            if ($t["maetrans"]["ir1"] == 'S') {
                                $i++;
                                $j1 = $i;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                    $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                    $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            if ($t["maetrans"]["ir1"] == 'C' || $t["maetrans"]["ir1"] == 'D') {
                                if ($t["dattrans"]["pornaltot"] + $t["dattrans"]["porexttot"] == 0) {
                                    $t["dattrans"]["pornalpri"] = 100;
                                }
                                if ($t["maetrans"]["ir1"] == 'C') {
                                    $vimp = $v_regmer_cuantia;
                                    $vimpEsadl = $v_regesadl_cuantia;
                                }
                                if ($t["maetrans"]["ir1"] == 'D') {
                                    $vimp = $v_regmer_cuantiaD;
                                    $vimpEsadl = $v_regesadl_cuantiaD;
                                }
                                $i++;
                                $j1 = $i;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100, -2);
                                    $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $vimp, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100, -2);
                                    $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            $dias = \funcionesSii2::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);
                            if ($dias > 60) {
                                $diascalcular = $dias - 60;
                                if ($t["maetrans"]["ir1"] == 'S') {
                                    $i++;
                                    if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                        $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * $diascalcular / 30 / 100, -2);
                                        $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                    } else {
                                        $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * $diascalcular / 30 / 100, -2);
                                        $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                    }
                                }
                                if ($t["maetrans"]["ir1"] == 'C' || $t["maetrans"]["ir1"] == 'D') {
                                    $i++;
                                    if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                        $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia / 30 / 100 * $diascalcular, -2);
                                        $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                    } else {
                                        $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia / 30 / 100 * $diascalcular, -2);
                                        $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //
            $controlliquidacion = 'si';
            if ($t["maetrans"]["idservicio2"] == '') {
                $controlliquidacion = 'no';
            }
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["condicionservicio2"] != '') {
                    eval($t["maetrans"]["condicionservicio2"]);
                }
            }
            if ($controlliquidacion == 'si') {
                $serv = $t["maetrans"]["idservicio2"];
                $temServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $serv . "'");
                if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' ||
                        ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                    $i++;
                    $j = $i;
                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicio($dbx, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, 2);
                }
                if ($t["dattrans"]["acreditapagoir"] == 'N') {
                    if ($liquidarIR == 'SI' && ($t["maetrans"]["ir2"] == 'S' || $t["maetrans"]["ir2"] == 'C' || $t["maetrans"]["ir2"] == 'D')) {
                        if ($t["maetrans"]["ir2"] == 'S') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        if ($t["maetrans"]["ir2"] == 'C' || $t["maetrans"]["ir2"] == 'D') {
                            if ($t["dattrans"]["pornaltot"] + $t["dattrans"]["porexttot"] == 0) {
                                $t["dattrans"]["pornalpri"] = 100;
                            }
                            if ($t["maetrans"]["ir2"] == 'C') {
                                $vimp = $v_regmer_cuantia;
                                $vimpEsadl = $v_regesadl_cuantia;
                            }
                            if ($t["maetrans"]["ir2"] == 'D') {
                                $vimp = $v_regmer_cuantiaD;
                                $vimpEsadl = $v_regesadl_cuantiaD;
                            }
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        $dias = \funcionesSii2::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);
                        if ($dias > 60) {
                            $diascalcular = $dias - 60;
                            if ($t["maetrans"]["ir2"] == 'S') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            if ($t["maetrans"]["ir2"] == 'C' || $t["maetrans"]["ir2"] == 'D') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                        }
                    }
                }
            }

            //
            $controlliquidacion = 'si';
            if ($t["maetrans"]["idservicio3"] == '') {
                $controlliquidacion = 'no';
            }
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["condicionservicio3"] != '') {
                    eval($t["maetrans"]["condicionservicio3"]);
                }
            }
            if ($controlliquidacion == 'si') {
                $serv = $t["maetrans"]["idservicio3"];
                $temServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $serv . "'");
                if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' ||
                        ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                    $i++;
                    $j = $i;
                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicio($dbx, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, 3);
                }
                if ($t["dattrans"]["acreditapagoir"] == 'N') {
                    if ($liquidarIR == 'SI' && ($t["maetrans"]["ir3"] == 'S' || $t["maetrans"]["ir3"] == 'C' || $t["maetrans"]["ir3"] == 'D')) {
                        if ($t["maetrans"]["ir3"] == 'S') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        if ($t["maetrans"]["ir3"] == 'C' || $t["maetrans"]["ir3"] == 'D') {
                            if ($t["dattrans"]["pornaltot"] + $t["dattrans"]["porexttot"] == 0) {
                                $t["dattrans"]["pornalpri"] = 100;
                            }
                            if ($t["maetrans"]["ir3"] == 'C') {
                                $vimp = $v_regmer_cuantia;
                                $vimpEsadl = $v_regesadl_cuantia;
                            }
                            if ($t["maetrans"]["ir3"] == 'D') {
                                $vimp = $v_regmer_cuantiaD;
                                $vimpEsadl = $v_regesadl_cuantiaD;
                            }

                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        $dias = \funcionesSii2::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);
                        if ($dias > 60) {
                            $diascalcular = $dias - 60;
                            if ($t["maetrans"]["ir3"] == 'S') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            if ($t["maetrans"]["ir3"] == 'C' || $t["maetrans"]["ir3"] == 'D') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                        }
                    }
                }
            }

            //
            $controlliquidacion = 'si';
            if ($t["maetrans"]["idservicio4"] == '') {
                $controlliquidacion = 'no';
            }
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["condicionservicio4"] != '') {
                    eval($t["maetrans"]["condicionservicio4"]);
                }
            }
            if ($controlliquidacion == 'si') {
                $serv = $t["maetrans"]["idservicio4"];
                $temServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $serv . "'");
                if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' ||
                        ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                    $i++;
                    $j = $i;
                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicio($dbx, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, 4);
                }
                if ($t["dattrans"]["acreditapagoir"] == 'N') {
                    if ($liquidarIR == 'SI' && ($t["maetrans"]["ir4"] == 'S' || $t["maetrans"]["ir4"] == 'C' || $t["maetrans"]["ir4"] == 'D')) {
                        if ($t["maetrans"]["ir4"] == 'S') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        if ($t["maetrans"]["ir4"] == 'C' || $t["maetrans"]["ir4"] == 'D') {
                            if ($t["dattrans"]["pornaltot"] + $t["dattrans"]["porexttot"] == 0) {
                                $t["dattrans"]["pornalpri"] = 100;
                            }
                            if ($t["maetrans"]["ir4"] == 'C') {
                                $vimp = $v_regmer_cuantia;
                                $vimpEsadl = $v_regesadl_cuantia;
                            }
                            if ($t["maetrans"]["ir4"] == 'D') {
                                $vimp = $v_regmer_cuantiaD;
                                $vimpEsadl = $v_regesadl_cuantiaD;
                            }

                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        $dias = \funcionesSii2::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);
                        if ($dias > 60) {
                            $diascalcular = $dias - 60;
                            if ($t["maetrans"]["ir4"] == 'S') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            if ($t["maetrans"]["ir4"] == 'C' || $t["maetrans"]["ir4"] == 'D') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                        }
                    }
                }
            }

            //
            $controlliquidacion = 'si';
            if ($t["maetrans"]["idservicio5"] == '') {
                $controlliquidacion = 'no';
            }
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["condicionservicio5"] != '') {
                    eval($t["maetrans"]["condicionservicio5"]);
                }
            }
            if ($controlliquidacion == 'si') {
                $serv = $t["maetrans"]["idservicio5"];
                $temServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $serv . "'");
                if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' ||
                        ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                    $i++;
                    $j = $i;
                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicio($dbx, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, 5);
                }
                if ($t["dattrans"]["acreditapagoir"] == 'N') {
                    if ($liquidarIR == 'SI' && ($t["maetrans"]["ir5"] == 'S' || $t["maetrans"]["ir5"] == 'C' || $t["maetrans"]["ir5"] == 'D')) {
                        if ($t["maetrans"]["ir5"] == 'S') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        if ($t["maetrans"]["ir5"] == 'C' || $t["maetrans"]["ir5"] == 'D') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["pornaltot"] == 0 && $t["dattrans"]["porexttot"] == 0) {
                                $t["dattrans"]["pornalpri"] = 100;
                            }
                            if ($t["maetrans"]["ir5"] == 'C') {
                                $vimp = $v_regmer_cuantia;
                                $vimpEsadl = $v_regesadl_cuantia;
                            }
                            if ($t["maetrans"]["ir5"] == 'D') {
                                $vimp = $v_regmer_cuantiaD;
                                $vimpEsadl = $v_regesadl_cuantiaD;
                            }

                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        $dias = \funcionesSii2::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);
                        if ($dias > 60) {
                            $diascalcular = $dias - 60;
                            if ($t["maetrans"]["ir5"] == 'S') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            if ($t["maetrans"]["ir5"] == 'C' || $t["maetrans"]["ir5"] == 'D') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                        }
                    }
                }
            }

            //
            $controlliquidacion = 'si';
            if ($t["maetrans"]["idservicio6"] == '') {
                $controlliquidacion = 'no';
            }
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["condicionservicio6"] != '') {
                    eval($t["maetrans"]["condicionservicio6"]);
                }
            }
            if ($controlliquidacion == 'si') {
                $serv = $t["maetrans"]["idservicio6"];
                $temServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $serv . "'");
                if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' ||
                        ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                    $i++;
                    $j = $i;
                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicio($dbx, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, 6);
                }
                if ($t["dattrans"]["acreditapagoir"] == 'N') {
                    if ($liquidarIR == 'SI' && ($t["maetrans"]["ir6"] == 'S' || $t["maetrans"]["ir6"] == 'C' || $t["maetrans"]["ir6"] == 'D')) {
                        if ($t["maetrans"]["ir6"] == 'S') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        if ($t["maetrans"]["ir6"] == 'C' || $t["maetrans"]["ir6"] == 'D') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["pornaltot"] == 0 && $t["dattrans"]["porexttot"] == 0) {
                                $t["dattrans"]["pornalpri"] = 100;
                            }
                            if ($t["maetrans"]["ir6"] == 'C') {
                                $vimp = $v_regmer_cuantia;
                                $vimpEsadl = $v_regesadl_cuantia;
                            }
                            if ($t["maetrans"]["ir6"] == 'D') {
                                $vimp = $v_regmer_cuantiaD;
                                $vimpEsadl = $v_regesadl_cuantiaD;
                            }

                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        $dias = \funcionesSii2::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);
                        if ($dias > 60) {
                            $diascalcular = $dias - 60;
                            if ($t["maetrans"]["ir6"] == 'S') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            if ($t["maetrans"]["ir6"] == 'C' || $t["maetrans"]["ir6"] == 'D') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                        }
                    }
                }
            }

            //
            $controlliquidacion = 'si';
            if ($t["maetrans"]["idservicio7"] == '') {
                $controlliquidacion = 'no';
            }
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["condicionservicio7"] != '') {
                    eval($t["maetrans"]["condicionservicio7"]);
                }
            }
            if ($controlliquidacion == 'si') {
                $serv = $t["maetrans"]["idservicio7"];
                $temServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $serv . "'");
                if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' ||
                        ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                    $i++;
                    $j = $i;
                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicio($dbx, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, 7);
                }
                if ($t["dattrans"]["acreditapagoir"] == 'N') {
                    if ($liquidarIR == 'SI' && ($t["maetrans"]["ir7"] == 'S' || $t["maetrans"]["ir7"] == 'C' || $t["maetrans"]["ir7"] == 'D')) {
                        if ($t["maetrans"]["ir7"] == 'S') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        if ($t["maetrans"]["ir7"] == 'C' || $t["maetrans"]["ir7"] == 'D') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["pornaltot"] == 0 && $t["dattrans"]["porexttot"] == 0) {
                                $t["dattrans"]["pornalpri"] = 100;
                            }
                            if ($t["maetrans"]["ir7"] == 'C') {
                                $vimp = $v_regmer_cuantia;
                                $vimpEsadl = $v_regesadl_cuantia;
                            }
                            if ($t["maetrans"]["ir7"] == 'D') {
                                $vimp = $v_regmer_cuantiaD;
                                $vimpEsadl = $v_regesadl_cuantiaD;
                            }

                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        $dias = \funcionesSii2::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);
                        if ($dias > 60) {
                            $diascalcular = $dias - 60;
                            if ($t["maetrans"]["ir7"] == 'S') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            if ($t["maetrans"]["ir7"] == 'C' || $t["maetrans"]["ir7"] == 'D') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                        }
                    }
                }
            }

            //
            $controlliquidacion = 'si';
            if ($t["maetrans"]["idservicio8"] == '') {
                $controlliquidacion = 'no';
            }
            if ($controlliquidacion == 'si') {
                if ($t["maetrans"]["condicionservicio8"] != '') {
                    eval($t["maetrans"]["condicionservicio8"]);
                }
            }
            if ($controlliquidacion == 'si') {
                $serv = $t["maetrans"]["idservicio8"];
                $temServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $serv . "'");
                if ($t["dattrans"]["acreditapagoir"] == 'N' || $t["dattrans"]["acreditapagoir"] == '' ||
                        ($t["dattrans"]["acreditapagoir"] == 'S' && ltrim($temServ["conceptodepartamental"], "0") == '')) {
                    $i++;
                    $j = $i;
                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicio($dbx, $t["dattrans"]["idsecuencia"], $serv, $t, $arrServs, $temServ, 8);
                }
                if ($t["dattrans"]["acreditapagoir"] == 'N') {
                    if ($liquidarIR == 'SI' && ($t["maetrans"]["ir8"] == 'S' || $t["maetrans"]["ir8"] == 'C' || $t["maetrans"]["ir8"] == 'D')) {
                        if ($t["maetrans"]["ir8"] == 'S') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regmer_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["cantidad"] * $v_regesadl_sincuantia, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], 0, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        if ($t["maetrans"]["ir8"] == 'C' || $t["maetrans"]["ir8"] == 'D') {
                            $i++;
                            $j1 = $i;
                            if ($t["dattrans"]["pornaltot"] == 0 && $t["dattrans"]["porexttot"] == 0) {
                                $t["dattrans"]["pornalpri"] = 100;
                            }
                            if ($t["maetrans"]["ir8"] == 'C') {
                                $vimp = $v_regmer_cuantia;
                                $vimpEsadl = $v_regesadl_cuantia;
                            }
                            if ($t["maetrans"]["ir8"] == 'D') {
                                $vimp = $v_regmer_cuantiaD;
                                $vimpEsadl = $v_regesadl_cuantiaD;
                            }
                            if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimp / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            } else {
                                $valor = round($_SESSION["tramite"]["liquidacion"][$j]["valorbase"] * ($t["dattrans"]["pornalpri"] + $t["dattrans"]["porextpri"]) / 100 * $vimpEsadl / 100, -2);
                                $_SESSION["tramite"]["valorimpregistro"] = $_SESSION["tramite"]["valorimpregistro"] + $valor;
                                $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $sir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j]["valorbase"], $v_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                            }
                        }
                        $dias = \funcionesSii2::diferenciaEntreFechasCalendario(date("Ymd"), $t["dattrans"]["fechadoc"]);
                        if ($dias > 60) {
                            $diascalcular = $dias - 60;
                            if ($t["maetrans"]["ir8"] == 'S') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_sincuantia * $diascalcular / 30 / 100, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_sincuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_sincuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                            if ($t["maetrans"]["ir8"] == 'C' || $t["maetrans"]["ir8"] == 'D') {
                                $i++;
                                if ($t["dattrans"]["organizacion"] != '12' && $t["dattrans"]["organizacion"] != '14') {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regmer_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regmer_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regmer_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                } else {
                                    $valor = round($_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"] * $vm_regesadl_cuantia / 30 / 100 * $diascalcular, -2);
                                    $_SESSION["tramite"]["liquidacion"][$i] = \funcionesSii2_liquidaciones::sumarServicioGeneral($t["dattrans"]["idsecuencia"], $smir_regesadl_cuantia, $_SESSION["tramite"]["liquidacion"][$j]["expediente"], $_SESSION["tramite"]["liquidacion"][$j]["nombre"], date("Y"), $_SESSION["tramite"]["liquidacion"][$j]["cantidad"], $_SESSION["tramite"]["liquidacion"][$j1]["valorservicio"], $vm_regesadl_cuantia, $valor, 'N', 'N', 'N', 'N', 'S', 'N');
                                }
                            }
                        }
                    }
                }
            }
        }

        // ********************************************************************************** //
        // Verifica Aplicacion del decreto 658
        // ********************************************************************************** //
        if ($liquidarDecreto658 == 'SI') {
            foreach ($decreto658 as $d) {
                if ($d["porcentaje"] != 0) {
                    if ($d["valorservicio"] != 0) {
                        $arrLiq = $_SESSION["tramite"]["liquidacion"];
                        $i = count($_SESSION["tramite"]["liquidacion"]);
                        $i++;
                        $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = sprintf("%03s", $d["idsec"]);
                        $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $d["servicio"];
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = '';
                        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $d["nombre"];
                        $_SESSION["tramite"]["liquidacion"][$i]["ano"] = date("Y");
                        $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = -1;
                        $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $d["activos"];
                        $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = $d["porcentaje"];
                        $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $d["valorservicio"] * -1;
                        $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                        $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
                    }
                }
            }
        }

        // ********************************************************************************** //
        // Verifica Aplicacion Ley 1780
        // ********************************************************************************** //
        foreach ($ley1780 as $d) {
            if ($d["valorservicio"] != 0) {
                $arrLiq = $_SESSION["tramite"]["liquidacion"];
                $i = count($_SESSION["tramite"]["liquidacion"]);
                $i++;
                $_SESSION["tramite"]["liquidacion"][$i]["idsec"] = sprintf("%03s", $d["idsec"]);
                $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $d["servicio"];
                if (isset($d["cc"])) {
                    $_SESSION["tramite"]["liquidacion"][$i]["cc"] = $d["cc"];
                } else {
                    $_SESSION["tramite"]["liquidacion"][$i]["cc"] = CODIGO_EMPRESA;
                }
                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $d["expediente"];
                if ($d["expediente"] == '') {
                    if ($d["tipotramite"] == 'constitucionpjur') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAJUR';
                    }
                    if ($d["tipotramite"] == 'constitucionesadl') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAESA';
                    }
                    if ($d["tipotramite"] == 'matriculapnat') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVANAT';
                    }
                    if ($d["tipotramite"] == 'matriculaest') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAEST';
                    }
                    if ($d["tipotramite"] == 'matriculasuc') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVASUC';
                    }
                    if ($d["tipotramite"] == 'matriculaage') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = 'NUEVAAGE';
                    }
                }
                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $d["nombre"];
                $_SESSION["tramite"]["liquidacion"][$i]["ano"] = date("Y");
                $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = -1;
                $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $d["activos"];
                $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = $d["porcentaje"];
                $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $d["valorservicio"] * -1;
                $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["benley1780"] = 'S';
                $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["ir"] = 'N';
                $_SESSION["tramite"]["liquidacion"][$i]["iva"] = 'N';
            }
        }



        // ************************************************************ //
        // Liquida los servicios dependientes (relacionados)
        // ************************************************************ //
        if ($_SESSION["tramite"]["tipotramite"] != 'inscripciondocumentos') {
            $i = count($_SESSION ["tramite"] ["liquidacion"]);
            $_SESSION ["generales"] ["txtemergente"] = '';
            $siAfiliado = 'NO';
            $tarifaanteriorafiliacion = 0;
            foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                if (trim($serv["idservicio"]) != '') {
                    if ($serv ["serviciobase"] == 'S') {
                        $arrServicio = retornarRegistroMysqli2($dbx, "mreg_servicios", "idservicio='" . $serv ["idservicio"] . "'");
                        if (($arrServicio === false) || (count($arrServicio) == 0)) {
                            $_SESSION ["generales"] ["txtemergente"] .= 'El servicio [' . $serv ["idservicio"] . '] no esta definido en la tabla de servicios del SII\n';
                        } else {
                            for ($x = 1; $x <= 7; $x ++) {
                                switch ($x) {
                                    case 1 :
                                        $tempserv = $arrServicio ["iddependiente1"];
                                        break;
                                    case 2 :
                                        $tempserv = $arrServicio ["iddependiente2"];
                                        break;
                                    case 3 :
                                        $tempserv = $arrServicio ["iddependiente3"];
                                        break;
                                    case 4 :
                                        $tempserv = $arrServicio ["iddependiente4"];
                                        break;
                                    case 5 :
                                        $tempserv = $arrServicio ["iddependiente5"];
                                        break;
                                    case 6 :
                                        $tempserv = $arrServicio ["iddependiente6"];
                                        break;
                                    case 7 :
                                        $tempserv = $arrServicio ["iddependiente7"];
                                        break;
                                }
                                if (trim($tempserv) != '') {
                                    $sumar = 'si';
                                    if ($tempserv == RENOVACION_SERV_AFILIACION) {
                                        if ($serv ["organizacion"] != '02') {
                                            if ($serv ["categoria"] != '3') {
                                                if ($_SESSION ["tramite"] ["incluirafiliacion"] == 'S') {
                                                    if ($serv ["afiliado"] != 'S') {
                                                        $sumar = 'no';
                                                    } else {
                                                        if (RENOVACION_LIQUIDAR_AFILIACION_RELACIONADO != 'S') {
                                                            $sumar = 'no';
                                                        } else {
                                                            if ($serv ["reliquidacion"] != 'si') {
                                                                if ($serv ["ultimoanoafiliado"] == intval(date("Y"))) {
                                                                    $sumar = 'no';
                                                                }
                                                            }
                                                            if ($serv ["reliquidacion"] == 'si') {
                                                                if ($serv ["ultimoanoafiliado"] != intval(date("Y"))) {
                                                                    $sumar = 'no';
                                                                } else {
                                                                    $tarifaanteriorafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, $serv ["ultimoanoafiliado"], 1, $baseanteriorafiliacion);
                                                                    if (TIPO_AFILIACION == 'TARIFAPORTIPO') {
                                                                        if ($serv ["organizacion"] == '01') {
                                                                            $tarifaanteriorafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, $serv ["ultimoanoafiliado"], 1, $baseanteriorafiliacion, 'tarifapnat');
                                                                        }
                                                                        if ($serv ["organizacion"] != '01') {
                                                                            $tarifaanteriorafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, $serv ["ultimoanoafiliado"], 1, $baseanteriorafiliacion, 'tarifapjur');
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $sumar = 'no';
                                                }
                                            } else {
                                                $sumar = 'no';
                                            }
                                        } else {
                                            $sumar = 'no';
                                        }
                                    }

                                    //
                                    if ($sumar == 'si') {
                                        if ($serv ["ano"] == date("Y")) {
                                            $sumar = 'si';
                                        } else {
                                            $sumar = 'no';
                                            // En el caso de la Camara de Comercio de Cucuta y cuando se trate del servicio
                                            // 04040172 (estampilla), se liquida siempre y cuando el año sea igual o superior al 2003 
                                            if ($_SESSION ["generales"] ["codigoempresa"] == '11') {
                                                if ($tempserv == '04040172') {
                                                    if ($serv ["ano"] >= 2003) {
                                                        $sumar = 'si';
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if ($sumar == 'si') {
                                        $i ++;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = $serv ["idsec"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $tempserv;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = $serv ["ano"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = $serv ["valorbase"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                        if ($_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] == RENOVACION_SERV_AFILIACION) {
                                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"], 1, $serv ["valorbase"]);
                                            if (TIPO_AFILIACION == 'TARIFAPORTIPO') {
                                                if ($serv ["organizacion"] == '01') {
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"], 1, $serv ["valorbase"], 'tarifapnat');
                                                }
                                                if ($serv ["organizacion"] != '01') {
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"], 1, $serv ["valorbase"], 'tarifapjur');
                                                }
                                            }
                                        } else {
                                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"], 1, $serv ["valorbase"]);
                                        }
                                        if ($_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] == RENOVACION_SERV_AFILIACION) {
                                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] - $tarifaanteriorafiliacion;
                                        }
                                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["reliquidacion"] = $serv ["reliquidacion"];
                                        if ($tempserv == RENOVACION_SERV_AFILIACION) {
                                            $siAfiliado = 'SI';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //
            if ($_SESSION ["generales"] ["txtemergente"] != '') {
                $_SESSION ["generales"] ["txtemergente"] = 'Hay errores en la parametrización de los servicios de renovación, por favor informe al administrador del portal los errores que a continuación se describen:\n\n' . $_SESSION ["generales"] ["txtemergente"];
                return false;
            }
        }


        // ************************************************************ //
        // Liquida Certificados
        // Solo en caso de renovaciones
        // Solamente si se renuevan todos los años
        // ************************************************************ //
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            $siCertificados = 'NO';
            if (RENOVACION_LIQUIDAR_CERTIFICADOS == 'S') {
                if ($cantAnos == $cantRenovar) {
                    if ($_SESSION ["tramite"] ["incluircertificados"] == 'S') {
                        $serv = $_SESSION ["tramite"] ["liquidacion"] [1];
                        if ($serv ["organizacion"] == '01') {
                            $tempserv = RENOVACION_SERV_CERTIMAT;
                        }
                        if ($serv ["organizacion"] == '02') {
                            $tempserv = RENOVACION_SERV_CERTIMAT;
                        }
                        if (($serv ["organizacion"] > '02') && ($serv ["categoria"] == '1')) {
                            $tempserv = RENOVACION_SERV_CERTIEXI;
                        }
                        if (($serv ["organizacion"] > '02') && ($serv ["categoria"] == '2')) {
                            $tempserv = RENOVACION_SERV_CERTIEXI;
                        }
                        if (($serv ["organizacion"] > '02') && ($serv ["categoria"] == '3')) {
                            $tempserv = RENOVACION_SERV_CERTIMAT;
                        }
                        $i ++;

                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $tempserv;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = RENOVACION_CANT_CERTIFICADOS;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                        if ($siAfiliado == 'SI') {
                            if (!defined('RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS')) {
                                define('RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS', 'S');
                            }
                            if (RENOVACION_COBRAR_CERTIFICADOS_AFILIADOS == 'S') {
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, RENOVACION_CANT_CERTIFICADOS, 0);
                            } else {
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = 0;
                            }
                        } else {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, RENOVACION_CANT_CERTIFICADOS, 0);
                        }
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                        $siCertificados = 'SI';
                    }
                }
            }
        }

        // ************************************************************ //
        // Liquida Diplomas (para las personas naturales y sociedades principales)
        // Solo en caso de renovaciones
        // Solamente si se renuevan todos los años
        // ************************************************************ //
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            if (RENOVACION_LIQUIDAR_DIPLOMAS == 'S') {
                if ($_SESSION ["tramite"] ["incluirdiploma"] == 'S') {
                    if ($cantAnos == $cantRenovar) {
                        foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                            if ($serv ["serviciobase"] == 'S') {
                                if (($serv ["organizacion"] == '01') || (($serv ["organizacion"] > '02') && ($serv ["categoria"] == '1'))) {
                                    if ($serv ["ano"] == date("Y")) {
                                        $i ++;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = $serv ["000"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_DIPLOMA;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii::buscaTarifaSii($_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, 1, $serv ["valorservicio"]);
                                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // ************************************************************ //
        // Liquida Cartulinas (para los establecimientos de comercio)
        // Solo en caso de renovaciones
        // Solamente si se renuevan todos los años
        // ************************************************************ //
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            if (RENOVACION_LIQUIDAR_CARTULINAS == 'S') {
                if ($_SESSION ["tramite"] ["incluircartulina"] == 'S') {
                    if ($cantAnos == $cantRenovar) {
                        foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                            if ($serv ["serviciobase"] == 'S') {
                                if (($serv ["organizacion"] == '02') || ($serv ["categoria"] == '2') || ($serv ["categoria"] == '3')) {
                                    if ($serv ["ano"] == date("Y")) {
                                        $i ++;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = $serv ["000"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_CARTULINAS;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, 1, $serv ["valorservicio"]);
                                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        // ************************************************************ //
        // Liquida formularios - Uno por cada matricula
        // Solo en caso de renovación
        // ************************************************************ //

        if (!defined('RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX')) {
            define('RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX', 'S');
        }
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            $arrForms = array();
            $cant = 0;
            $expeini = '';
            if (
                    (RENOVACION_LIQUIDAR_FORMULARIOS == 'S' && $_SESSION["generales"]["escajero"] == 'SI') ||
                    ((RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX == 'S' || RENOVACION_LIQUIDAR_FORMULARIOS_USUPUBXX == '') && $_SESSION["generales"]["escajero"] != 'SI')
            ) {
                if (substr(strtoupper($_SESSION ["tramite"] ["incluirformularios"]), 0, 1) == 'S') {
                    $cant = 0;
                    foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                        if ($serv ["serviciobase"] == 'S') {
                            if (!isset($arrForms [$serv ["expediente"]])) {
                                $arrForms [$serv ["expediente"]] = 0;
                            }
                            $arrForms [$serv ["expediente"]] ++;
                            if ($expeini == '') {
                                $expeini = $serv ["expediente"];
                            }
                        }
                    }

                    //
                    // Resta un formulario al total, salvo cuando el conteo de uno (1)
                    // Por efectos del primer establecimiento de comercio
                    foreach ($arrForms as $f) {
                        $cant ++;
                    }
                    if ($cant > 1) {
                        $cant = $cant - 1;
                    }

                    if ($cant > 0) {
                        $servform = RENOVACION_SERV_FORMULARIOS;
                        if ($_SESSION["tarmite"]["tipotramite"] == 'renovacionesadl') {
                            if (trim(RENOVACION_SERV_FORMULARIOS_ESADL) != '') {
                                $servform = RENOVACION_SERV_FORMULARIOS_ESADL;
                            }
                        }
                        $i ++;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $expeini;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $servform;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = $cant;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                        if ($siAfiliado == 'SI' || $siAfiliado == '1') {
                            if (!defined('RENOVACION_COBRAR_FORMULARIOS_AFILIADOS')) {
                                define('RENOVACION_COBRAR_FORMULARIOS_AFILIADOS', 'S');
                            }
                            if (RENOVACION_COBRAR_FORMULARIOS_AFILIADOS == 'S') {
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, $cant, $serv ["valorservicio"]);
                            } else {
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = 0;
                            }
                        } else {
                            $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, $cant, $serv ["valorservicio"]);
                        }
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                    }
                }
            }
        }

        // ************************************************************ //
        // Liquida afiliacion
        // Solo en caso de renovaciones
        // Debe activarse cuando la afiliacion no se maneje como un servicio 
        // relacionado
        // RENOVACION_PUBLICOS_COBROS_ADICIONALES
        // ************************************************************ //
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            if ($_SESSION ["tramite"] ["incluirafiliacion"] == 'S') {
                if (RENOVACION_LIQUIDAR_AFILIACION_APARTE == 'S' && $siAfiliado == 'NO') {
                    $siAfiliado = 'NO';
                    $tarifaanteriorafiliacion = 0;
                    foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
                        if ($serv ["serviciobase"] == 'S') {
                            if ($serv ["afiliado"] == 'S' || $serv ["afiliado"] == '1') {
                                if ($serv ["ultimoanoafiliado"] < intval(date("Y")) || $serv ["reliquidacion"] == 'si') {
                                    if ($serv ["ano"] == date("Y")) {
                                        if ($serv ["organizacion"] != '02') {
                                            if ($serv ["categoria"] != '3') {

                                                /*
                                                  $i++;
                                                  if ($serv ["reliquidacion"] == 'si') {
                                                  $tarifaanteriorafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, date("Y"), 1, $baseanteriorafiliacion);
                                                  if (TIPO_AFILIACION == 'TARIFAPORTIPO') {
                                                  if ($serv ["organizacion"] == '01') {
                                                  $tarifaanteriorafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, date("Y"), 1, $baseanteriorafiliacion, 'tarifapnat');
                                                  }
                                                  if ($serv ["organizacion"] != '01') {
                                                  $tarifaanteriorafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, date("Y"), 1, $baseanteriorafiliacion, 'tarifapjur');
                                                  }
                                                  }
                                                  }
                                                 */

                                                // 2020-03-04: JINT - Busca los pagos de afiliación primera vez o renovación que haya tenido
                                                $tarifaanteriorafiliacion = 0;
                                                // if ($serv ["reliquidacion"] != 'si') {
                                                    $pagosAfil = retornarRegitrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $serv ["expediente"] . "') and (fecoperacion > '" . date("Y") . "0100') and (servicio in (" . $txtServAfiliacion . ")) and (tipogasto IN ('0','8')) ", "numerorecibo");
                                                    if ($pagosAfil && !empty($pagosAfil)) {
                                                        foreach ($pagosAfil as $afil) {
                                                            $tarifaanteriorafiliacion = $tarifaanteriorafiliacion + $afil["valor"];
                                                        }
                                                    }
                                                // }

                                                //
                                                $deltaafiliacion = 0;
                                                $nuevatarifaafiliacion = 0;
                                                if (TIPO_AFILIACION == 'TARIFAPORTIPO') {
                                                    if ($serv ["organizacion"] == '01') {
                                                        $nuevatarifaafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, date("Y"), 1, $serv ["valorbase"], 'tarifapnat');
                                                        $deltaafiliacion = $nuevatarifaafiliacion - $tarifaanteriorafiliacion;
                                                    }
                                                    if ($serv ["organizacion"] != '01') {
                                                        $nuevatarifaafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, date("Y"), 1, $serv ["valorbase"], 'tarifapjur');
                                                        $deltaafiliacion = $nuevatarifaafiliacion - $tarifaanteriorafiliacion;
                                                    }
                                                } else {
                                                    $nuevatarifaafiliacion = \funcionesSii2::buscaTarifaSii($dbx, RENOVACION_SERV_AFILIACION, date("Y"), 1, $serv ["valorbase"]);
                                                    $deltaafiliacion = $nuevatarifaafiliacion - $tarifaanteriorafiliacion;
                                                }

                                                //
                                                if ($deltaafiliacion > 0) {
                                                     $i++;
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_AFILIACION;
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = date("Y");
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = $serv ["valorbase"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                                    $siAfiliado = 'SI';

                                                    $cupoanteriorcertificados = 0;
                                                    $cuponuevocertificados = 0;
                                                    $incrementocupocertificados = 0;
                                                    $formaCalculoAfiliacion = retornarClaveValorSii2($dbx, '90.01.60');
                                                    if ($formaCalculoAfiliacion != '') {
                                                        if ($formaCalculoAfiliacion == 'RANGO_VAL_AFI') {
                                                            $arrServicio = retornarRegistrosMysqli2($dbx, "mreg_rangos_cupo_afiliacion", "ano='" . date("Y") . "'", "orden");
                                                            foreach ($arrRan as $rx) {
                                                                if ($rx["minimo"] <= $tarifaanteriorafiliacion && $rx["maximo"] >= $tarifaanteriorafiliacion) {
                                                                    $cupoanteriorcertificados = $rx["cupo"];
                                                                }
                                                                if ($rx["minimo"] <= $nuevatarifaafiliacion && $rx["maximo"] >= $nuevatarifaafiliacion) {
                                                                    $cuponuevocertificados = $rx["cupo"];
                                                                }
                                                            }
                                                            unset($arrRan);
                                                            unset($rx);
                                                        } else {
                                                            $cupoanteriorcertificados = round(doubleval($formaCalculoAfiliacion) * $tarifaanteriorafiliacion, 0);
                                                            $cuponuevocertificados = round(doubleval($formaCalculoAfiliacion) * $nuevatarifaafiliacion, 0);
                                                        }
                                                    }
                                                    $incrementocupocertificados = $cuponuevocertificados - $cupoanteriorcertificados;
                                                    $_SESSION ["tramite"] ["incrementocupocertificados"] = $incrementocupocertificados;

                                                    if ($_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] == 0) {
                                                        unset($_SESSION["tramite"]["liquidacion"][$i]);
                                                        $i = $i - 1;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // ***********************************************************************//
        // Liquida Fletes
        // Solo en caso de renovaciones
        // Obviamente si se han incluido certificados
        // Solamente si se renuevan todos los años
        // ***********************************************************************//
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {

            if (!defined('RENOVACION_VALOR_FLETE')) {
                define('RENOVACION_VALOR_FLETE', '0');
            }
            if (!defined('RENOVACION_SERV_FLETE')) {
                define('RENOVACION_SERV_FLETE', '');
            }

            if (RENOVACION_SERV_FLETE != '') {
                if ($_SESSION ["tramite"] ["incluirfletes"] == 'S') {
                    if ($cantAnos == $cantRenovar) {
                        $i ++;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $_SESSION ["tramite"] ["expedientes"] [1] ["matricula"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $_SESSION ["tramite"] ["expedientes"] [1] ["razonsocial"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $_SESSION ["tramite"] ["expedientes"] [1] ["organizacion"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $_SESSION ["tramite"] ["expedientes"] [1] ["categoria"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = RENOVACION_SERV_FLETE;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = RENOVACION_VALOR_FLETE;
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                    }
                }
            }
        }

        // ************************************************************ //
        // Liquida servicios Gravados
        // Servicios que están asociados con otros servicios
        // ************************************************************ //
        $_SESSION ["generales"] ["txtemergente"] = '';
        $i = count($_SESSION ["tramite"] ["liquidacion"]);
        foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
            if (trim($serv ["idservicio"]) != '') {
                $arrServicio = retornarRegistroMysqli2($dbx, "mreg_servicios", "idservicio='" . $serv ["idservicio"] . "'");
                if (($arrServicio === false) || (count($arrServicio) == 0)) {
                    $_SESSION ["generales"] ["txtemergente"] .= 'El servicio [[' . $serv ["idservicio"] . ']] no está definido en la tabla de servicios del SII.\n';
                } else {
                    for ($x = 1; $x <= 7; $x ++) {
                        switch ($x) {
                            case 1 :
                                $tempserv = $arrServicio ["idgravado1"];
                                break;
                            case 2 :
                                $tempserv = $arrServicio ["idgravado2"];
                                break;
                            case 3 :
                                $tempserv = $arrServicio ["idgravado3"];
                                break;
                            case 4 :
                                $tempserv = $arrServicio ["idgravado4"];
                                break;
                            case 5 :
                                $tempserv = $arrServicio ["idgravado5"];
                                break;
                            case 6 :
                                $tempserv = $arrServicio ["idgravado6"];
                                break;
                            case 7 :
                                $tempserv = $arrServicio ["idgravado7"];
                                break;
                        }
                        if (trim($tempserv) != '') {
                            $arrServicio = retornarRegistroMysqli2($dbx, "mreg_servicios", "idservicio='" . $tempserv . "'");
                            $ok1 = 'si';
                            if (trim($serv ["ano"]) != '') {
                                if (trim($arrServicio ["fechainicial"]) == '') {
                                    $ok1 = 'si';
                                } else {
                                    if (substr($arrServicio ["fechainicial"], 0, 4) <= $serv ["ano"]) {
                                        $ok1 = 'si';
                                    } else {
                                        $ok1 = 'no';
                                    }
                                }
                            } else {
                                $ok1 = 'si';
                            }
                            if ($ok1 == 'si') {
                                $i ++;
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = $serv ["idsec"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $serv ["expediente"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $serv ["nombre"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $serv ["organizacion"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $serv ["categoria"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $tempserv;
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = $serv ["ano"];
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 0;
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = $serv ["valorservicio"];

                                //
                                if ($arrServicio ["idesiva"] == 'S') {
                                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = $arrServicio ["valorservicio"];
                                } else {
                                    if (($arrServicio ["idtipovalor"] == '4') || ($arrServicio ["idtipovalor"] == '5')) {
                                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                                    }
                                }

                                //
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"], 0, 1, $serv ["valorservicio"]);
                                $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                                if ($arrServicio ["idesiva"] == 'S') {
                                    $_SESSION ["tramite"] ["valorbaseiva"] = $_SESSION ["tramite"] ["valorbaseiva"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"];
                                    $_SESSION ["tramite"] ["valoriva"] = $_SESSION ["tramite"] ["valoriva"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                } else {
                                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                                }
                            }
                        }
                    }
                }
            } else {
                $_SESSION ["generales"] ["txtemergente"] .= 'El servicio se encuentra vacio, cuidado, no puede continuar.\n';
            }
        }

        //
        if ($_SESSION ["generales"] ["txtemergente"] != '') {
            $_SESSION ["generales"] ["txtemergente"] = 'Hay errores en la parametrización de los servicios de renovación, por favor informe al administrador del portal los errores que a continuación se describen:\n\n' . $_SESSION ["generales"] ["txtemergente"];
            return false;
        }

        // 2017-11-28: JINT: En caso de reliquidacion y que a su vez se maneje como una mutación
        if (RENOVACION_RELIQUIDACION_COMO_MUTACION == 'S') {
            foreach ($_SESSION["tramite"]["expedientes"] as $e) {
                if ($e["reliquidacion"] == 'si' && $e["renovaresteano"] == 'si') {
                    $servx = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_15;
                    $ex = retornarRegistrosMysqli2($dbx, 'mrg_est_inscritos', "matricula='" . $e["matricula"] . "'");
                    if ($ex["ciiu1"] == 'R9200' ||
                            $ex["ciiu2"] == 'R9200' ||
                            $ex["ciiu3"] == 'R9200' ||
                            $ex["ciiu4"] == 'R9200') {
                        $servx = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_22;
                    }
                    if ($ex["organizacion"] == '12' && $ex["categoria"] == '1') {
                        $servx = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_51;
                    }
                    if ($ex["organizacion"] == '14' && $ex["categoria"] == '1') {
                        $servx = RENOVACION_SERV_RELIQUIDACION_COMO_MUTACION_53;
                    }

                    $i ++;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $e["matricula"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $e["razonsocial"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $e["organizacion"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $e["categoria"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $servx;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $servx, 0, $cant, 0);
                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                }
            }
        }

        // ************************************************************ //
        // Aplica Alertas que estén asociados con la matrícula o con
        // la identificación
        // ************************************************************ //
        $aleTem = array();
        if (ltrim($_SESSION ["tramite"] ["matriculabase"], "0") != '') {
            $aleTem = retornarRegistrosMysqli2($dbx, 'mreg_alertas', "matricula='" . trim($_SESSION ["tramite"] ["matriculabase"]) . "' and (idestado<>'AP' and idestado<>'IN') and (tipoalerta='1' or tipoalerta='4')", "fecha");
            if (empty($aleTem)) {
                if (trim($_SESSION ["tramite"] ["identificacionbase"]) != '') {
                    $aleTem = retornarRegistrosMysqli2($dbx, 'mreg_alertas', "identificacion='" . $_SESSION ["tramite"] ["identificacionbase"] . "' and (idestado<>'AP' and idestado<>'IN') and (tipoalerta='1' or tipoalerta='4')", "fecha");
                }
            }
        }

        //
        if ($aleTem && !empty($aleTem)) {
            $i = count($_SESSION ["tramite"] ["liquidacion"]);
            foreach ($aleTem as $t) {
                $valalerta = 0;
                $i ++;
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idsec"] = '000';
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["expediente"] = $_SESSION ["tramite"] ["liquidacion"] [1] ["expediente"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["nombre"] = $_SESSION ["tramite"] ["liquidacion"] [1] ["nombre"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["organizacion"] = $_SESSION ["tramite"] ["liquidacion"] [1] ["organizacion"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["categoria"] = $_SESSION ["tramite"] ["liquidacion"] [1] ["categoria"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idservicio"] = $t ["idservicio"];
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["ano"] = '';
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["cantidad"] = 1;
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorbase"] = 0;
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["porcentaje"] = 0;
                if ($t ["tipoalerta"] == '1') {
                    if ($_SESSION ["tramite"] ["valorbruto"] - $t ["valoralerta"] >= 0) {
                        $valalerta = $t ["valoralerta"];
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] - $valalerta;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $valalerta * - 1;
                    } else {
                        $valalerta = $t ["valoralerta"] - $_SESSION ["tramite"] ["valorbruto"];
                        $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] - $valalerta;
                        $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $valalerta * - 1;
                    }
                }
                if ($t ["tipoalerta"] == '4') {
                    $valalerta = $t ["valoralerta"];
                    $_SESSION ["tramite"] ["liquidacion"] [$i] ["valorservicio"] = $valalerta;
                    $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $valalerta;
                }
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["serviciobase"] = 'N';
                $_SESSION ["tramite"] ["liquidacion"] [$i] ["idalerta"] = $t ["id"];
            }
        }

        // ************************************************************ //
        // Recalcula los valores generales de la liquidacion
        // para prevenir errores
        // ************************************************************ //
        $_SESSION ["tramite"] ["valortotal"] = 0;
        $_SESSION ["tramite"] ["valoriva"] = 0;
        $_SESSION ["tramite"] ["valorbruto"] = 0;
        $_SESSION ["tramite"] ["valorbaseiva"] = 0;
        foreach ($_SESSION ["tramite"] ["liquidacion"] as $serv) {
            $_SESSION ["tramite"] ["valortotal"] = $_SESSION ["tramite"] ["valortotal"] + $serv ["valorservicio"];
            $tmpServ = retornarRegistroMysqli2($dbx, "mreg_servicios", "idservicio='" . $serv ["idservicio"] . "'");
            if ($tmpServ ["idesiva"] == 'S') {
                $_SESSION ["tramite"] ["valorbaseiva"] = $_SESSION ["tramite"] ["valorbaseiva"] + $serv ["valorbase"];
                $_SESSION ["tramite"] ["valoriva"] = $_SESSION ["tramite"] ["valoriva"] + $serv ["valorservicio"];
            } else {
                $_SESSION ["tramite"] ["valorbruto"] = $_SESSION ["tramite"] ["valorbruto"] + $serv ["valorservicio"];
            }
        }

        // ************************************************************ //
        if ($txtFinal != '') {
            $_SESSION["generales"]["mensajeerror"] = $txtFinal;
        } else {
            $_SESSION["generales"]["mensajeerror"] = '';
        }
        return true;
    }

    public static function sumarServicio($dbx, $key, $serv, $tran, $servs, $temServ, $indice, $cant = '') {

        $ok = 'no';

        // **************************************************************************** //
        // Asigna el expediente dependiendo del tipo de servicio que se esté liquidando //
        // Por defecto, asigna la matrícula afectada en la transacción
        // **************************************************************************** //
        $matricula = $tran["dattrans"]["matriculaafectada"];
        $nombre = $tran["dattrans"]["razonsocial"];
        $organizacion = $tran["dattrans"]["organizacion"];
        $categoria = $tran["dattrans"]["categoria"];

        if ($tran["maetrans"]["tipotramite"] == 'constitucionpjur') {
            $matricula = 'NUEVAJUR';
        }
        if ($tran["maetrans"]["tipotramite"] == 'constitucionesadl') {
            $matricula = 'NUEVAESA';
        }
        if ($tran["maetrans"]["tipotramite"] == 'matriculapnat') {
            $matricula = 'NUEVANAT';
        }
        if ($tran["maetrans"]["tipotramite"] == 'matriculaest') {
            $matricula = 'NUEVAEST';
        }
        if ($tran["maetrans"]["tipotramite"] == 'matriculasuc') {
            $matricula = 'NUEVASUC';
        }
        if ($tran["maetrans"]["tipotramite"] == 'matriculaage') {
            $matricula = 'NUEVAAGE';
        }

        // Si el tipo de transacción es una compraventa
        if ($tran["maetrans"]["idtipotransaccion"] == '022') {
            if ($serv == '01020101') { //
                $matricula = 'NUEVANAT';
                $nombre = $tran["dattrans"]["nombrecomprador"];
                $organizacion = $tran["dattrans"]["organizacioncomprador"];
                $categoria = '0';
            }
            if ($serv == '01031501' || $serv == '01031509') { //
                $matricula = $tran["dattrans"]["matriculavendedor"];
                $nombre = $tran["dattrans"]["nombrevendedor"];
                $organizacion = '01';
                $categoria = '0';
            }
        }

        //
        if (ltrim($cant, "0") == '') {
            if (ltrim($tran["dattrans"]["cantidad"], "0") != '') {
                $cant = $tran["dattrans"]["cantidad"];
            }
        }
        if (ltrim($cant, "0") == '') {
            $cant = 1;
        }

        //
        $arrSal = array(
            'idsec' => sprintf("%03s", $key),
            'idservicio' => $serv,
            'expediente' => $matricula,
            'nombre' => $nombre,
            'organizacion' => $organizacion,
            'categoria' => $categoria,
            'ano' => date("Y"),
            'cantidad' => $cant,
            'valorbase' => 0,
            'porcentaje' => 0,
            'valorservicio' => 0,
            'benart7' => 'N',
            'reliquidacion' => 'N',
            'serviciobase' => 'S',
            'pagoafiliacion' => 'N',
            'ir' => 'N',
            'iva' => 'N',
        );

        //
        $tipobase = '';
        switch ($indice) {
            case 1 : $tipobase = $tran["maetrans"]["valorbase1"];
                break;
            case 2 : $tipobase = $tran["maetrans"]["valorbase2"];
                break;
            case 3 : $tipobase = $tran["maetrans"]["valorbase3"];
                break;
            case 4 : $tipobase = $tran["maetrans"]["valorbase4"];
                break;
            case 5 : $tipobase = $tran["maetrans"]["valorbase5"];
                break;
            case 6 : $tipobase = $tran["maetrans"]["valorbase6"];
                break;
            case 7 : $tipobase = $tran["maetrans"]["valorbase7"];
                break;
            case 8 : $tipobase = $tran["maetrans"]["valorbase8"];
                break;
        }

        if ($tipobase == 'sinbase') {
            $arrSal["valorbase"] = 0;
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, 0);
            $ok = 'si';
        }

        if ($tipobase == 'activos') {
            $bas = $tran["dattrans"]["activos"];
            $arrSal["valorbase"] = $tran["dattrans"]["activos"];
            if ($tran["maetrans"]["idtipotransaccion"] == '022') { // Si se trata de una compraventa
                $arrSal["valorbase"] = $tran["dattrans"]["activoscomprador"];
                $bas = $tran["dattrans"]["activoscomprador"];
            }
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $bas);
            $ok = 'si';
        }

        //
        if ($tipobase == 'valorcontrato') {
            $arrSal["valorbase"] = $tran["dattrans"]["costotransaccion"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $tran["dattrans"]["costotransaccion"]);
            $ok = 'si';
        }

        //
        if ($tipobase == 'valorcompraventa') {
            $arrSal["valorbase"] = $tran["dattrans"]["costotransaccion"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $tran["dattrans"]["costotransaccion"]);
            $ok = 'si';
        }

        //
        if ($tipobase == 'patrimonio') {
            $arrSal["valorbase"] = $tran["dattrans"]["patrimonio"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $tran["dattrans"]["patrimonio"]);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalsocial') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalsocial"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $tran["dattrans"]["capitalsocial"]);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalautorizado') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalautorizado"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $tran["dattrans"]["capitalautorizado"]);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalsuscrito') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalsuscrito"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $tran["dattrans"]["capitalsuscrito"]);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalpagado') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalpagado"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $tran["dattrans"]["capitalpagado"]);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalasociativas') {
            $arrSal["valorbase"] = $tran["dattrans"]["aporteactivos"] + $tran["dattrans"]["aportedinero"] + $tran["dattrans"]["aportelaboral"] + $tran["dattrans"]["aportelaboraladicional"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $arrSal["valorbase"]);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalasignado') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalasignado"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $tran["dattrans"]["capitalasignado"]);
            $ok = 'si';
        }

        if ($tipobase == 'impreg') {
            $arrSal["valorbase"] = $_SESSION["tramite"]["valorimpregistro"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $arrSal["valorbase"]);
            $ok = 'si';
        }


        //
        if ($ok == 'no') {
            $arrSal["valorbase"] = $tran["dattrans"]["costotransaccion"];
            $arrSal["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $serv, date("Y"), $cant, $arrSal["valorbase"]);
            $ok = 'si';
        }

        //
        if (!isset($_SESSION["tramite"]["cobrarmutacion"])) {
            $_SESSION["tramite"]["cobrarmutacion"] = '';
        }
        if ($_SESSION["tramite"]["cobrarmutacion"] == 'N') {
            $arrSal["valorservicio"] = 0;
        }

        return $arrSal;
    }

    public static function sumarServicioGeneral($idsec = '', $serv = '', $expediente = '', $nombre = '', $ano = '', $cantidad = 0, $valorbase = 0, $porcentaje = 0, $valorservicio = 0, $benart7 = 'N', $reliquidacion = 'N', $serviciobase = 'N', $pagoafiliacion = 'N', $ir = 'N', $iva = 'N') {
        $arrSal = array(
            'idsec' => sprintf("%03s", $idsec),
            'idservicio' => $serv,
            'expediente' => $expediente,
            'nombre' => $nombre,
            'ano' => $ano,
            'cantidad' => $cantidad,
            'valorbase' => $valorbase,
            'porcentaje' => $porcentaje,
            'valorservicio' => $valorservicio,
            'benart7' => $benart7,
            'reliquidacion' => $reliquidacion,
            'serviciobase' => $serviciobase,
            'pagoafiliacion' => $pagoafiliacion,
            'ir' => $ir,
            'iva' => $iva
        );
        return $arrSal;
    }

    /*
     * Localiza que servicio gravado afecta un servicio base
     */

    public static function rutinaLiquidacionCertificados($dbx, $servicio, $cantidad, $expediente) {

        //
        $arrServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $servicio . "'");

        //
        if ($arrServ === false || empty($arrServ)) {
            $_SESSION["generales"]["mensajeerror"] = 'SERVICIO (' . $servicio . ') NO ENCONTRADO EN LA TABLA DE SERVICIOS DEL SII';
            return false;
        }

        //
        if (trim($arrServ["tipocertificado"]) == '') {
            $_SESSION["generales"]["mensajeerror"] = 'SERVICIO (' . $servicio . ') NO ESTA ASOCIADO CON UN TIPO DE CERTIFICADO A EXPEDIR';
            return false;
        }

        //
        if (trim($arrServ["tipocertificado"]) == 'CerMat' ||
                trim($arrServ["tipocertificado"]) == 'CerExi' ||
                trim($arrServ["tipocertificado"]) == 'CerLibRegMer' ||
                trim($arrServ["tipocertificado"]) == 'CerEsadl') {
            $arrExp = \funcionesSii2::retornarExpedienteMercantilSii($dbx, $expediente);
            if ($arrExp === false || $arrExp == 0) {
                $_SESSION["generales"]["mensajeerror"] = 'EXPEDIENTE (' . $expediente . ') NO LOCALIZADO EN EL SISTEMA DE REGISTRO';
                return false;
            }
        }

        //
        if (trim($arrServ["tipocertificado"]) == 'CerPro') {
            // 2017-12-28: pendiente de crear en funcionesSii2
            $arrExp = retornarExpedienteProponente($expediente);
            if ($arrExp === false || $arrExp == 0) {
                $_SESSION["generales"]["mensajeerror"] = 'EXPEDIENTE (' . $expediente . ') NO LOCALIZADO EN EL SISTEMA DE REGISTRO';
                return false;
            }
            $arrExp["categoria"] = ''; // Para proponentes este campo no se maneja
        }

        if ($arrExp === false || $arrExp == 0 || empty($arrExp)) {
            return false;
        }

        // Liquidar certificados
        $arrRet = array();
        $i = 0;

        //

        $i++;
        $arrRet[$i]["expediente"] = $expediente;
        $arrRet[$i]["nombre"] = $arrExp["nombre"];
        $arrRet[$i]["organizacion"] = $arrExp["organizacion"];
        $arrRet[$i]["categoria"] = $arrExp["categoria"];
        $arrRet[$i]["afiliado"] = '';
        $arrRet[$i]["ultimoanoafiliado"] = '';
        $arrRet[$i]["idservicio"] = $servicio;
        $arrRet[$i]["ano"] = '';
        $arrRet[$i]["cantidad"] = $cantidad;
        $arrRet[$i]["valorbase"] = 0;
        $arrRet[$i]["porcentaje"] = 0;
        $arrRet[$i]["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $servicio, 0, 1, 0) * $cantidad;
        $arrRet[$i]["serviciobase"] = 'S';
        $arrRet[$i]["benart7"] = '';
        $arrRet[$i]["reliquidacion"] = 'N';
        $arrRet[$i]["pagoafiliacion"] = 'N';
        $arrRet[$i]["ir"] = 'N';
        $arrRet[$i]["iva"] = 'N';
        $arrRet[$i]["idalerta"] = 0;


        // Liquida los servicios dependientes
        for ($x = 1; $x <= 7; $x++) {
            switch ($x) {
                case 1: $tempserv = $arrServ["iddependiente1"];
                    break;
                case 2: $tempserv = $arrServ["iddependiente2"];
                    break;
                case 3: $tempserv = $arrServ["iddependiente3"];
                    break;
                case 4: $tempserv = $arrServ["iddependiente4"];
                    break;
                case 5: $tempserv = $arrServ["iddependiente5"];
                    break;
                case 6: $tempserv = $arrServ["iddependiente6"];
                    break;
                case 7: $tempserv = $arrServ["iddependiente7"];
                    break;
            }
            if (trim($tempserv) != '') {
                $arrTemServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $tempserv . "'");
                $i++;

                $arrRet[$i]["expediente"] = $expediente;
                $arrRet[$i]["nombre"] = $arrExp["nombre"];
                $arrRet[$i]["organizacion"] = $arrExp["organizacion"];
                $arrRet[$i]["categoria"] = $arrExp["categoria"];
                $arrRet[$i]["idservicio"] = $tempserv;
                $arrRet[$i]["ano"] = '';
                $arrRet[$i]["cantidad"] = $cantidad;
                $arrRet[$i]["valorbase"] = \funcionesSii2::buscaTarifaSii($dbx, $servicio, 0, 1, 0) * $cantidad;
                if ($arrTemServ["idesiva"] == 'S') {
                    $arrRet[$i]["porcentaje"] = $arrTemServ["valorservicio"];
                } else {
                    if (($arrTemServ["idtipovalor"] == '4') || ($arrTemServ["idtipovalor"] == '5')) {
                        $arrRet[$i]["porcentaje"] = 0;
                    }
                }
                $arrRet[$i]["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $tempserv, 0, 1, $arrRet[$i]["valorbase"]) * $cantidad;
                $arrRet[$i]["serviciobase"] = 'N';
                $arrRet[$i]["benart7"] = '';
                $arrRet[$i]["reliquidacion"] = 'N';
                $arrRet[$i]["pagoafiliacion"] = 'N';
                $arrRet[$i]["ir"] = 'N';
                $arrRet[$i]["iva"] = 'N';
                if (trim($arrTemServ["conceptodepartamental"]) != '') {
                    $arrRet[$i]["ir"] = 'S';
                }
                if (trim($arrTemServ["idesiva"]) == 'S') {
                    $arrRet[$i]["iva"] = 'S';
                }
                $arrRet[$i]["idalerta"] = 0;
            }
        }


        // Liquida servicios Gravados
        for ($x = 1; $x <= 7; $x++) {
            switch ($x) {
                case 1: $tempserv = $arrServ["idgravado1"];
                    break;
                case 2: $tempserv = $arrServ["idgravado2"];
                    break;
                case 3: $tempserv = $arrServ["idgravado3"];
                    break;
                case 4: $tempserv = $arrServ["idgravado4"];
                    break;
                case 5: $tempserv = $arrServ["idgravado5"];
                    break;
                case 6: $tempserv = $arrServ["idgravado6"];
                    break;
                case 7: $tempserv = $arrServ["idgravado7"];
                    break;
            }
            if (trim($tempserv) != '') {
                $arrTemServ = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $tempserv . "'");

                $i++;
                $arrRet[$i]["expediente"] = $expediente;
                $arrRet[$i]["nombre"] = $arrExp["nombre"];
                $arrRet[$i]["organizacion"] = $arrExp["organizacion"];
                $arrRet[$i]["categoria"] = $arrExp["categoria"];
                $arrRet[$i]["idservicio"] = $tempserv;
                $arrRet[$i]["ano"] = '';
                $arrRet[$i]["cantidad"] = $cantidad;
                $arrRet[$i]["valorbase"] = \funcionesSii2::buscaTarifaSii($dbx, $servicio, 0, 1, 0) * $cantidad;
                if ($arrTemServ["idesiva"] == 'S') {
                    $arrRet[$i]["porcentaje"] = $arrTemServ["valorservicio"];
                } else {
                    if (($arrTemServ["idtipovalor"] == '4') || ($arrTemServ["idtipovalor"] == '5')) {
                        $arrRet[$i]["porcentaje"] = 0;
                    }
                }
                $arrRet[$i]["valorservicio"] = \funcionesSii2::buscaTarifaSii($dbx, $tempserv, 0, 1, $arrRet[$i]["valorbase"]) * $cantidad;
                $arrRet[$i]["serviciobase"] = 'N';
                $arrRet[$i]["benart7"] = '';
                $arrRet[$i]["reliquidacion"] = 'N';
                $arrRet[$i]["pagoafiliacion"] = 'N';
                $arrRet[$i]["ir"] = 'N';
                $arrRet[$i]["iva"] = 'N';
                if (trim($arrTemServ["conceptodepartamental"]) != '') {
                    $arrRet[$i]["ir"] = 'S';
                }
                if (trim($arrTemServ["idesiva"]) == 'S') {
                    $arrRet[$i]["iva"] = 'S';
                }
                $arrRet[$i]["idalerta"] = 0;
            }
        }

        return $arrRet;
    }

    //put your code here

    public static function rutinaRetornarMregLiquidacion($dbx, $numliq, $tipo = 'L') {

        // Inicializa las variables del tr&aacute;mite
        $respuesta = array();
        $respuesta["idliquidacion"] = 0;
        $respuesta["numeroliquidacion"] = 0;
        $respuesta["fecha"] = '';
        $respuesta["hora"] = '';
        $respuesta["idusuario"] = '';
        $respuesta["sede"] = '';
        $respuesta["tipotramite"] = '';
        $respuesta["subtipotramite"] = '';
        $respuesta["origen"] = '';
        $respuesta["iptramite"] = '';
        $respuesta["idestado"] = '';
        $respuesta["idexpedientebase"] = '';
        $respuesta["idmatriculabase"] = '';
        $respuesta["idproponentebase"] = '';
        $respuesta["tipoproponente"] = '';
        $respuesta["tipoidentificacionbase"] = '';
        $respuesta["identificacionbase"] = '';
        $respuesta["nombrebase"] = '';
        $respuesta["nom1base"] = '';
        $respuesta["nom2base"] = '';
        $respuesta["ape1base"] = '';
        $respuesta["ape2base"] = '';
        $respuesta["organizacionbase"] = '';
        $respuesta["categoriabase"] = '';
        $respuesta["afiliadobase"] = '';
        $respuesta["matriculabase"] = '';
        $respuesta["proponentebase"] = '';

        $respuesta["numeromatriculapnat"] = '';
        $respuesta["camarapnat"] = '';
        $respuesta["orgpnat"] = '';
        $respuesta["tipoidepnat"] = '';
        $respuesta["idepnat"] = '';
        $respuesta["nombrepnat"] = '';

        //
        $respuesta["nombreest"] = '';
        $respuesta["nombrepjur"] = '';
        $respuesta["nombresuc"] = '';
        $respuesta["nombreage"] = '';

        $respuesta["orgpjur"] = '';
        $respuesta["orgsuc"] = '';
        $respuesta["orgage"] = '';

        $respuesta["actpnat"] = '';
        $respuesta["actpjur"] = '';
        $respuesta["actest"] = '';
        $respuesta["actsuc"] = '';
        $respuesta["actage"] = '';

        $respuesta["perpnat"] = '';
        $respuesta["perpjur"] = '';

        $respuesta["munpnat"] = '';
        $respuesta["munest"] = '';
        $respuesta["munpjur"] = '';
        $respuesta["munsuc"] = '';
        $respuesta["munage"] = '';

        $respuesta["ultanoren"] = '';
        $respuesta["domicilioorigen"] = '';
        $respuesta["domiciliodestino"] = '';


        $respuesta["tipocliente"] = '';
        $respuesta["idtipoidentificacioncliente"] = '';
        $respuesta["identificacioncliente"] = '';
        $respuesta["nombrecliente"] = '';
        $respuesta["apellidocliente"] = '';

        $respuesta["razonsocialcliente"] = '';
        $respuesta["nombre1cliente"] = '';
        $respuesta["nombre2cliente"] = '';
        $respuesta["apellido1cliente"] = '';
        $respuesta["apellido2cliente"] = '';

        $respuesta["email"] = '';
        $respuesta["direccion"] = '';
        $respuesta["idmunicipio"] = '';
        $respuesta["telefono"] = '';
        $respuesta["movil"] = '';

        $respuesta["tipopagador"] = '';
        $respuesta["nombrepagador"] = '';
        $respuesta["apellidopagador"] = '';

        $respuesta["razonsocialpagador"] = '';
        $respuesta["nombre1pagador"] = '';
        $respuesta["nombre2pagador"] = '';
        $respuesta["apellido1pagador"] = '';
        $respuesta["apellido2pagador"] = '';

        $respuesta["tipoidentificacionpagador"] = '';
        $respuesta["identificacionpagador"] = '';
        $respuesta["direccionpagador"] = '';
        $respuesta["telefonopagador"] = '';
        $respuesta["movilpagador"] = '';
        $respuesta["municipiopagador"] = '';
        $respuesta["emailpagador"] = '';

        $respuesta["valorbruto"] = 0;
        $respuesta["valorbaseiva"] = 0;
        $respuesta["valoriva"] = 0;
        $respuesta["valortotal"] = 0;
        $respuesta["idsolicitudpago"] = 0;
        $respuesta["pagoefectivo"] = 0;
        $respuesta["pagocheque"] = 0;
        $respuesta["pagoconsignacion"] = 0;
        $respuesta["pagovisa"] = 0;
        $respuesta["pagoach"] = 0;
        $respuesta["pagomastercard"] = 0;
        $respuesta["pagoamerican"] = 0;
        $respuesta["pagocredencial"] = 0;
        $respuesta["pagodiners"] = 0;
        $respuesta["pagotdebito"] = 0;
        $respuesta["pagoprepago"] = 0;
        $respuesta["pagoafiliado"] = 0;
        $respuesta["idformapago"] = '';
        $respuesta["numerorecibo"] = '';
        $respuesta["numerooperacion"] = '';
        $respuesta["fecharecibo"] = '';
        $respuesta["horarecibo"] = '';
        $respuesta["idfranquicia"] = '';
        $respuesta["nombrefranquicia"] = '';
        $respuesta["numeroautorizacion"] = '';
        $respuesta["idcodban"] = '';
        $respuesta["nombrebanco"] = '';
        $respuesta["numerocheque"] = '';
        $respuesta["numerorecuperacion"] = '';
        $respuesta["numeroradicacion"] = '';
        $respuesta["alertaid"] = 0;
        $respuesta["alertaservicio"] = '';
        $respuesta["alertavalor"] = 0;
        $respuesta["ctrcancelacion"] = '';
        $respuesta["idasesor"] = '';
        $respuesta["numeroempleados"] = 0;
        $respuesta["pagoafiliacion"] = '';
        $respuesta["numerofactura"] = '';
        $respuesta["vueltas"] = 0;
        $respuesta["gateway"] = "";

        $respuesta["incluirformularios"] = '';
        $respuesta["incluircertificados"] = '';
        $respuesta["incluirdiploma"] = '';
        $respuesta["incluircartulina"] = '';
        $respuesta["matricularpnat"] = '';
        $respuesta["matricularest"] = '';
        $respuesta["regimentributario"] = '';
        $respuesta["tipomatricula"] = '';
        $respuesta["camaracambidom"] = '';
        $respuesta["matriculacambidom"] = '';
        $respuesta["municipiocambidom"] = '';
        $respuesta["fecmatcambidom"] = '';
        $respuesta["fecrencambidom"] = '';
        $respuesta["benart7"] = 'N';
        $respuesta["benley1780"] = 'N';
        $respuesta["controlfirma"] = 'N';
        $respuesta["actualizacionciiuversion4"] = '';
        $respuesta["reliquidacion"] = '';
        $respuesta["cumplorequisitosbenley1780"] = '';
        $respuesta["mantengorequisitosbenley1780"] = '';
        $respuesta["renunciobeneficiosley1780"] = '';
        $respuesta["multadoponal"] = '';
        $respuesta["controlaactividadaltoimpacto"] = '';

        $respuesta["capital"] = 0;
        $respuesta["tipodoc"] = '';
        $respuesta["numdoc"] = '';
        $respuesta["fechadoc"] = '';
        $respuesta["origendoc"] = '';
        $respuesta["mundoc"] = '';
        $respuesta["organizacion"] = '';
        $respuesta["categoria"] = '';

        $respuesta["tipoiderepleg"] = '';
        $respuesta["iderepleg"] = '';
        $respuesta["nombre1repleg"] = '';
        $respuesta["nombre2repleg"] = '';
        $respuesta["apellido1repleg"] = '';
        $respuesta["apellido2repleg"] = '';
        $respuesta["cargorepleg"] = ''; //
        $respuesta["emailrepleg"] = ''; //
        $respuesta["firmorepleg"] = ''; //
        $respuesta["celularrepleg"] = ''; //

        $respuesta["tipoideradicador"] = '';
        $respuesta["ideradicador"] = '';
        $respuesta["nombreradicador"] = '';
        $respuesta["fechaexpradicador"] = '';
        $respuesta["emailradicador"] = '';
        $respuesta["telefonoradicador"] = '';
        $respuesta["celularradicador"] = '';

        $respuesta["tipolibro"] = ''; //
        $respuesta["codigolibro"] = ''; //
        $respuesta["primeravez"] = ''; //
        $respuesta["confirmadigital"] = ''; //

        $respuesta["iderevfis"] = ''; //
        $respuesta["nombre1revfis"] = ''; //
        $respuesta["nombre2revfis"] = ''; //
        $respuesta["apellido1revfis"] = ''; //
        $respuesta["apellido2revfis"] = ''; //
        $respuesta["cargorevfis"] = ''; //
        $respuesta["emailrevfis"] = ''; //
        $respuesta["firmorevfis"] = ''; //
        $respuesta["celularrevfis"] = ''; //

        $respuesta["idepreasa"] = ''; //
        $respuesta["nombre1preasa"] = ''; //
        $respuesta["nombre2preasa"] = ''; //
        $respuesta["apellido1preasa"] = ''; //
        $respuesta["apellido2preasa"] = ''; //
        $respuesta["cargopreasa"] = ''; //
        $respuesta["emailpreasa"] = ''; //
        $respuesta["firmopreasa"] = ''; //
        $respuesta["celularpreasa"] = ''; //

        $respuesta["idesecasa"] = ''; //
        $respuesta["nombre1secasa"] = ''; //
        $respuesta["nombre2secasa"] = ''; //    
        $respuesta["apellido1secasa"] = ''; //
        $respuesta["apellido2secasa"] = ''; //
        $respuesta["cargosecasa"] = ''; //
        $respuesta["emailsecasa"] = ''; //
        $respuesta["firmosecasa"] = ''; //
        $respuesta["celularsecasa"] = ''; //

        $respuesta["tipoidentificacionaceptante"] = '';
        $respuesta["identificacionaceptante"] = '';
        $respuesta["nombre1aceptante"] = '';
        $respuesta["nombre2aceptante"] = '';
        $respuesta["apellido1aceptante"] = '';
        $respuesta["apellido2aceptante"] = '';
        $respuesta["direccionaceptante"] = '';
        $respuesta["municipioaceptante"] = '';
        $respuesta["emailaceptante"] = '';
        $respuesta["telefonoaceptante"] = '';
        $respuesta["celularaceptante"] = '';
        $respuesta["cargoaceptante"] = '';
        $respuesta["fechadocideaceptante"] = '';

        $respuesta["motivocorreccion"] = '';
        $respuesta["tipoerror1"] = '';
        $respuesta["tipoerror2"] = '';
        $respuesta["tipoerror3"] = '';
        $respuesta["tipoidentificacioncor"] = '';
        $respuesta["nombre1cor"] = '';
        $respuesta["nombre2cor"] = '';
        $respuesta["apellido1cor"] = '';
        $respuesta["apellido2cor"] = '';
        $respuesta["direccioncor"] = '';
        $respuesta["municipiocor"] = '';
        $respuesta["emailcor"] = '';
        $respuesta["telefonocor"] = '';
        $respuesta["celularcor"] = '';


        $respuesta["descripcionembargo"] = '';
        $respuesta["descripciondesembargo"] = '';
        $respuesta["tipoidentificaciondemandante"] = '';
        $respuesta["identificaciondemandante"] = '';
        $respuesta["nombredemandante"] = '';
        $respuesta["libro"] = '';
        $respuesta["numreg"] = '';

        $respuesta["descripcionpqr"] = '';
        $respuesta["tipoidentificacionpqr"] = '';
        $respuesta["nombre1pqr"] = '';
        $respuesta["nombre2pqr"] = '';
        $respuesta["apellido1pqr"] = '';
        $respuesta["apellido2pqr"] = '';
        $respuesta["direccionpqr"] = '';
        $respuesta["municipiopqr"] = '';
        $respuesta["emailpqr"] = '';
        $respuesta["telefonopqr"] = '';
        $respuesta["celularpqr"] = '';

        $respuesta["descripcionrr"] = '';
        $respuesta["tipoidentificacionrr"] = '';
        $respuesta["nombre1rr"] = '';
        $respuesta["nombre2rr"] = '';
        $respuesta["apellido1rr"] = '';
        $respuesta["apellido2rr"] = '';
        $respuesta["direccionrr"] = '';
        $respuesta["municipiorr"] = '';
        $respuesta["emailrr"] = '';
        $respuesta["telefonorr"] = '';
        $respuesta["celularrr"] = '';

        $respuesta["tipocertificado"] = '';
        $respuesta["explicacion"] = '';
        $respuesta["textolibre"] = '';

        $respuesta["proyectocaja"] = '001';
        $respuesta["cargoafiliacion"] = 'NO';
        $respuesta["cargogastoadministrativo"] = 'NO';
        $respuesta["cargoentidadoficial"] = 'NO';
        $respuesta["cargoconsulta"] = 'NO';

        $respuesta["opcionafiliado"] = '';
        $respuesta["saldoafiliado"] = 0;
        $respuesta["matriculaafiliado"] = '';
        $respuesta["ultanorenafi"] = '';

        // Mutaciones
        $respuesta["modcom"] = '';
        $respuesta["modnot"] = '';
        $respuesta["modciiu"] = '';
        $respuesta["modnombre"] = '';

        $respuesta["nombreanterior"] = '';
        $respuesta["nombrenuevo"] = '';

        $respuesta["ant_versionciiu"] = '';
        $respuesta["ant_ciiu11"] = '';
        $respuesta["ant_ciiu12"] = '';
        $respuesta["ant_ciiu13"] = '';
        $respuesta["ant_ciiu14"] = '';
        $respuesta["ant_ciiu21"] = '';
        $respuesta["ant_ciiu22"] = '';
        $respuesta["ant_ciiu23"] = '';
        $respuesta["ant_ciiu24"] = '';
        $respuesta["ant_dircom"] = '';
        $respuesta["ant_telcom1"] = '';
        $respuesta["ant_telcom2"] = '';
        $respuesta["ant_faxcom"] = '';
        $respuesta["ant_celcom"] = '';
        $respuesta["ant_muncom"] = '';
        $respuesta["ant_barriocom"] = '';
        $respuesta["ant_numpredial"] = '';
        $respuesta["ant_emailcom"] = '';
        $respuesta["ant_emailcom2"] = '';
        $respuesta["ant_emailcom3"] = '';
        $respuesta["ant_dirnot"] = '';
        $respuesta["ant_telnot1"] = '';
        $respuesta["ant_telnot2"] = '';
        $respuesta["ant_faxnot"] = '';
        $respuesta["ant_celnot"] = '';
        $respuesta["ant_munnot"] = '';
        $respuesta["ant_barrionot"] = '';
        $respuesta["ant_emailnot"] = '';

        $respuesta["versionciiu"] = '';
        $respuesta["ciiu11"] = '';
        $respuesta["ciiu12"] = '';
        $respuesta["ciiu13"] = '';
        $respuesta["ciiu14"] = '';
        $respuesta["ciiu21"] = '';
        $respuesta["ciiu22"] = '';
        $respuesta["ciiu23"] = '';
        $respuesta["ciiu24"] = '';
        $respuesta["dircom"] = '';
        $respuesta["telcom1"] = '';
        $respuesta["telcom2"] = '';
        $respuesta["faxcom"] = '';
        $respuesta["celcom"] = '';
        $respuesta["muncom"] = '';
        $respuesta["barriocom"] = '';
        $respuesta["numpredial"] = '';
        $respuesta["emailcom"] = '';
        $respuesta["emailcom2"] = '';
        $respuesta["emailcom3"] = '';
        $respuesta["dirnot"] = '';
        $respuesta["telnot1"] = '';
        $respuesta["telnot2"] = '';
        $respuesta["faxnot"] = '';
        $respuesta["celnot"] = '';
        $respuesta["munnot"] = '';
        $respuesta["barrionot"] = '';
        $respuesta["emailnot"] = '';

        // En caso de tr&aacute;mites rues
        $respuesta["rues_numerointerno"] = "";
        $respuesta["rues_numerounico"] = "";
        $respuesta["rues_camarareceptora"] = "";
        $respuesta["rues_camararesponsable"] = "";
        $respuesta["rues_matricula"] = "";
        $respuesta["rues_proponente"] = "";
        $respuesta["rues_nombreregistrado"] = "";
        $respuesta["rues_claseidentificacion"] = "";
        $respuesta["rues_numeroidentificacion"] = "";
        $respuesta["rues_dv"] = "";
        $respuesta["rues_estado_liquidacion"] = "";
        $respuesta["rues_estado_transaccion"] = "";
        $respuesta["rues_nombrepagador"] = "";
        $respuesta["rues_origendocumento"] = "";
        $respuesta["rues_fechadocumento"] = "";
        $respuesta["rues_fechapago"] = "";
        $respuesta["rues_numerofactura"] = "";
        $respuesta["rues_referenciaoperacion"] = "";
        $respuesta["rues_totalpagado"] = 0;
        $respuesta["rues_formapago"] = "";
        $respuesta["rues_indicadororigen"] = "";
        $respuesta["rues_empleados"] = "";
        $respuesta["rues_indicadorbeneficio"] = "";
        $respuesta["rues_fecharespuesta"] = "";
        $respuesta["rues_horarespuesta"] = "";
        $respuesta["rues_codigoerror"] = "";
        $respuesta["rues_mensajeerror"] = "";
        $respuesta["rues_firmadigital"] = "";
        $respuesta["rues_firmadigital"] = "";
        $respuesta["rues_caracteres_por_linea"] = "";


        $respuesta["expedientes"] = array();
        $respuesta["liquidacion"] = array();
        $respuesta["rues_servicios"] = array();
        $respuesta["rues_textos"] = array();
        $respuesta["transacciones"] = array();

        //
        $respuesta["nrocontrolsipref"] = '';
        $respuesta["foto"] = '../../images/sii/people.png';
        $respuesta["fotoabsoluta"] = 'images/sii/people.png';
        $respuesta["cedula1"] = '../../images/sii/people.png';
        $respuesta["cedula1absoluta"] = 'images/sii/people.png';
        $respuesta["cedula2"] = '../../images/sii/people.png';
        $respuesta["cedula2absoluta"] = 'images/sii/people.png';

        //
        $respuesta["firmadoelectronicamente"] = '';
        $respuesta["tipoidefirmante"] = '';
        $respuesta["identificacionfirmante"] = '';
        $respuesta["fechaexpfirmante"] = '';
        $respuesta["apellido1firmante"] = '';
        $respuesta["apellido2firmante"] = '';
        $respuesta["nombre1firmante"] = '';
        $respuesta["nombre2firmante"] = '';
        $respuesta["emailfirmante"] = '';
        $respuesta["emailfirmanteseguimiento"] = '';
        $respuesta["celularfirmante"] = '';
        $respuesta["direccionfirmante"] = '';
        $respuesta["municipiofirmante"] = '';


        //
        $respuesta["idclasefirmamanuscrita"] = '';
        $respuesta["numidfirmamanuscrita"] = '';
        $respuesta["nombrefirmamanuscrita"] = '';
        $respuesta["emailfirmamanuscrita"] = '';
        $respuesta["firmamanuscritabase64"] = '';
        $respuesta["fechafirmamanuscrita"] = '';
        $respuesta["horafirmamanuscrita"] = '';
        $respuesta["ipfirmamanuscrita"] = '';

        //
        $respuesta["emailcontactoasesoria"] = '';
        $respuesta["comentariosasesoria"] = '';

        //
        $respuesta["pedirbalance"] = '';
        $respuesta["incrementocupocertificados"] = 0;
        $respuesta["cobrarmutacion"] = '';

        $respuesta["propcamaraorigen"] = '';
        $respuesta["propproponenteorigen"] = '';
        $respuesta["propfechaultimainscripcion"] = '';
        $respuesta["propfechaultimarenovacion"] = '';
        $respuesta["propdircom"] = '';
        $respuesta["propmuncom"] = '';
        $respuesta["proptelcom1"] = '';
        $respuesta["proptelcom2"] = '';
        $respuesta["proptelcom3"] = '';
        $respuesta["propemailcom"] = '';
        $respuesta["propdirnot"] = '';
        $respuesta["propmunnot"] = '';
        $respuesta["proptelnot1"] = '';
        $respuesta["proptelnot2"] = '';
        $respuesta["proptelnot3"] = '';
        $respuesta["propemailnot"] = '';

        $respuesta["totalmatriculasrenovar"] = 0;
        $respuesta["totalmatriculasrenovadas"] = 0;

        $respuesta["cantidadfolios"] = 0;
        $respuesta["cantidadhojas"] = 0;
        $respuesta["enviara"] = '';
        $respuesta["emailcontrol"] = '';

        // 2018-05-21: JINT
        $respuesta["deposito_ano"] = '';
        $tx = retornarRegistrosMysqli2($dbx, 'mreg_identificadores', "pubbal > ''", "pubbal");
        if ($tx && !empty($tx)) {
            foreach ($tx as $tx1) {
                $respuesta["deposito_" . $tx1["id"]]["fechainforme"] = '';
                $respuesta["deposito_" . $tx1["id"]]["folios"] = '';
                if ($respuesta["idliquidacion"] != 0) {
                    $tdp = retornarRegistroMysqli2($dbx, 'mreg_publicacion_balances', "idliquidacion=" . $respuesta["idliquidacion"] . " and identificador='" . $tx1["id"] . "'");
                    if ($tdp && !empty($tdp)) {
                        $respuesta["deposito_" . $tx1["id"]]["fechainforme"] = $tdp["fechainforme"];
                        $respuesta["deposito_" . $tx1["id"]]["folios"] = $tdp["folios"];
                    }
                }
            }
        }
        unset($tx);



        // 2016-07-31 : JINT
        // Asocia la sede al trámite
        $respuesta["tramitepresencial"] = '';
        $respuesta["sede"] = '01';
        if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $respuesta["sede"] = '99'; // Sede virtual
            $respuesta["tramitepresencial"] = '1'; // Tramite virtual
        } else {
            $respuesta["tramitepresencial"] = '4'; // Trámite presencial
            if (!isset($_SESSION["generales"]["sedeusuario"])) {
                $_SESSION["generales"]["sedeusuario"] = '01';
            }
            $respuesta["sede"] = $_SESSION["generales"]["sedeusuario"];
        }

        //
        if ($tipo == 'VC') {
            if ($cerrarmysql = 'si') {
                $mysqli->close();
            }
            return $respuesta;
        }

        //
        $arrLiq = array();

        //
        if ($tipo == 'L') {
            $arrLiq = retornarRegistroMysqli2($dbx, 'mreg_liquidacion', "idliquidacion=" . $numliq);
        }

        //
        if ($tipo == 'NR') {
            $arrLiq = retornarRegistroMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $numliq . "'");
            if (($arrLiq) && (!empty($arrLiq))) {
                $numliq = $arrLiq["idliquidacion"];
            }
        }

        if ($tipo == 'CB') {
            $arrLiq = false;
            if (ltrim(trim($numliq), "0") != '') {
                $arrLiq = retornarRegistroMysqli2($dbx, 'mreg_liquidacion', "numeroradicacion='" . $numliq . "'");
            }
            if (($arrLiq) && (!empty($arrLiq))) {
                $numliq = $arrLiq["idliquidacion"];
            }
        }

        if (empty($arrLiq)) {
            if ($cerrarmysql == 'si') {
                $mysqli->close();
            }
            return false;
        }

        // 2016-07-31 : JINT
        // Asocia la sede al trámite
        if (!isset($arrLiq["sede"]) || $arrLiq["sede"] == '') {
            $arrLiq["sede"] = '99';
            if ($arrLiq["idusuario"] != 'USUPUBXX') {
                if ($arrLiq["idusuario"] == 'RUE') {
                    $arrLiq["sede"] = '90';
                } else {
                    $arrusu = retornarRegistroMysqli2($dbx, 'usuarios', "idusuario='" . $arrLiq["idusuario"] . "'");
                    if ($arrusu === false || $arrusu["idsede"] == '') {
                        $arrLiq["sede"] = '01';
                    } else {
                        $arrLiq["sede"] = $arrusu["idsede"];
                    }
                }
            }
            if ($arrLiq["sede"] == '') {
                $arrLiq["sede"] = '01';
            }
        }

        if (!isset($arrLiq["pagoconsignacion"])) {
            $arrLiq["pagoconsignacion"] = 0;
        }
        if (!isset($arrLiq["proyectocaja"])) {
            $arrLiq["proyectocaja"] = '001';
        }
        if (!isset($arrLiq["cargoafiliacion"])) {
            $arrLiq["cargoafiliacion"] = 'NO';
        }
        if (!isset($arrLiq["cargogastoadministrativo"])) {
            $arrLiq["cargogastoadministrativo"] = 'NO';
        }
        if (!isset($arrLiq["cargoentidadoficial"])) {
            $arrLiq["cargoentidadoficial"] = 'NO';
        }
        if (!isset($arrLiq["cargoconsulta"])) {
            $arrLiq["cargoconsulta"] = 'NO';
        }
        if (!isset($arrLiq["domicilioorigen"])) {
            $arrLiq["domicilioorigen"] = '';
        }
        if (!isset($arrLiq["domiciliodestino"])) {
            $arrLiq["domiciliodestino"] = '';
        }
        if (!isset($arrLiq["benart7"])) {
            $arrLiq["benart7"] = 'N';
        }
        if (!isset($arrLiq["controlfirma"])) {
            $arrLiq["controlfirma"] = 'N';
        }
        if (!isset($arrLiq["ultanoren"])) {
            $arrLiq["ultanoren"] = '';
        }
        if (!isset($arrLiq["idmatriculabase"])) {
            $arrLiq["idmatriculabase"] = '';
        }
        if (!isset($arrLiq["idproponentebase"])) {
            $arrLiq["idproponentebase"] = '';
        }

        // $respuesta = array();
        $respuesta["idliquidacion"] = ltrim($numliq, "0");
        $respuesta["numeroliquidacion"] = ltrim($numliq, "0");
        $respuesta["fecha"] = $arrLiq["fecha"];
        $respuesta["hora"] = $arrLiq["hora"];
        $respuesta["fechaultimamodificacion"] = $arrLiq["fechaultimamodificacion"];
        $respuesta["idusuario"] = $arrLiq["idusuario"];
        $respuesta["tipotramite"] = $arrLiq["tipotramite"];
        $respuesta["iptramite"] = $arrLiq["iptramite"];
        $respuesta["idestado"] = $arrLiq["idestado"];

        $respuesta["idexpedientebase"] = $arrLiq["idexpedientebase"];
        $respuesta["idmatriculabase"] = $arrLiq["idmatriculabase"];
        $respuesta["idproponentebase"] = $arrLiq["idproponentebase"];

        $respuesta["identificacionbase"] = $arrLiq["identificacionbase"];
        $respuesta["tipoidentificacionbase"] = $arrLiq["tipoidentificacionbase"];
        $respuesta["nombrebase"] = $arrLiq["nombrebase"];
        $respuesta["organizacionbase"] = $arrLiq["organizacionbase"];
        $respuesta["categoriabase"] = $arrLiq["categoriabase"];

        $respuesta["tipoidepnat"] = $arrLiq["tipoidepnat"];
        $respuesta["idepnat"] = $arrLiq["idepnat"];

        $respuesta["nombrepnat"] = $arrLiq["nombrepnat"];
        $respuesta["nombreest"] = $arrLiq["nombreest"];

        $respuesta["actpnat"] = $arrLiq["actpnat"];
        $respuesta["actest"] = $arrLiq["actest"];

        $respuesta["perpnat"] = $arrLiq["perpnat"];


        $respuesta["numeromatriculapnat"] = $arrLiq["numeromatriculapnat"];
        $respuesta["camarapnat"] = $arrLiq["camarapnat"];

        $respuesta["ultanoren"] = $arrLiq["ultanoren"];
        $respuesta["domicilioorigen"] = $arrLiq["domicilioorigen"];
        $respuesta["domiciliodestino"] = $arrLiq["domiciliodestino"];

        $respuesta["idtipoidentificacioncliente"] = $arrLiq["idtipoidentificacioncliente"];
        $respuesta["identificacioncliente"] = $arrLiq["identificacioncliente"];
        $respuesta["nombrecliente"] = $arrLiq["nombrecliente"];
        $respuesta["apellidocliente"] = $arrLiq["apellidocliente"];
        $respuesta["email"] = $arrLiq["email"];
        $respuesta["direccion"] = $arrLiq["direccion"];
        $respuesta["idmunicipio"] = $arrLiq["idmunicipio"];
        $respuesta["telefono"] = $arrLiq["telefono"];
        $respuesta["movil"] = $arrLiq["movil"];

        $respuesta["nombrepagador"] = $arrLiq["nombrepagador"];
        $respuesta["apellidopagador"] = $arrLiq["apellidopagador"];
        $respuesta["tipoidentificacionpagador"] = $arrLiq["tipoidentificacionpagador"];
        $respuesta["identificacionpagador"] = $arrLiq["identificacionpagador"];
        $respuesta["direccionpagador"] = $arrLiq["direccionpagador"];
        $respuesta["telefonopagador"] = $arrLiq["telefonopagador"];
        $respuesta["movilpagador"] = $arrLiq["movilpagador"];
        $respuesta["municipiopagador"] = $arrLiq["municipiopagador"];
        $respuesta["emailpagador"] = $arrLiq["emailpagador"];

        $respuesta["valorbruto"] = $arrLiq["valorbruto"];
        $respuesta["valorbaseiva"] = $arrLiq["valorbaseiva"];
        $respuesta["valoriva"] = $arrLiq["valoriva"];
        $respuesta["valortotal"] = $arrLiq["valortotal"];
        $respuesta["idsolicitudpago"] = $arrLiq["idsolicitudpago"];

        $respuesta["pagoefectivo"] = $arrLiq["pagoefectivo"];
        $respuesta["pagocheque"] = $arrLiq["pagocheque"];
        $respuesta["pagoconsignacion"] = $arrLiq["pagoconsignacion"];
        $respuesta["pagovisa"] = $arrLiq["pagovisa"];
        $respuesta["pagoach"] = $arrLiq["pagoach"];
        $respuesta["pagomastercard"] = $arrLiq["pagomastercard"];
        $respuesta["pagoamerican"] = $arrLiq["pagoamerican"];
        $respuesta["pagocredencial"] = $arrLiq["pagocredencial"];
        $respuesta["pagodiners"] = $arrLiq["pagodiners"];
        $respuesta["pagotdebito"] = $arrLiq["pagotdebito"];
        $respuesta["pagoprepago"] = $arrLiq["pagoprepago"];
        $respuesta["pagoafiliado"] = $arrLiq["pagoafiliado"];
        $respuesta["gateway"] = $arrLiq["gateway"];

        $respuesta["idformapago"] = $arrLiq["idformapago"];
        $respuesta["numerorecibo"] = $arrLiq["numerorecibo"];
        $respuesta["numerooperacion"] = $arrLiq["numerooperacion"];
        $respuesta["fecharecibo"] = $arrLiq["fecharecibo"];
        $respuesta["horarecibo"] = $arrLiq["horarecibo"];
        $respuesta["idfranquicia"] = $arrLiq["idfranquicia"];
        $respuesta["nombrefranquicia"] = $arrLiq["nombrefranquicia"];
        $respuesta["numeroautorizacion"] = $arrLiq["numeroautorizacion"];
        $respuesta["idcodban"] = $arrLiq["idcodban"];
        $respuesta["nombrebanco"] = $arrLiq["nombrebanco"];
        $respuesta["numerocheque"] = $arrLiq["numerocheque"];
        $respuesta["numerorecuperacion"] = $arrLiq["numerorecuperacion"];
        $respuesta["numeroradicacion"] = $arrLiq["numeroradicacion"];
        $respuesta["alertaid"] = $arrLiq["alertaid"];
        $respuesta["alertaservicio"] = $arrLiq["alertaservicio"];
        $respuesta["alertavalor"] = $arrLiq["alertavalor"];
        $respuesta["ctrcancelacion"] = $arrLiq["ctrcancelacion"];
        $respuesta["idasesor"] = $arrLiq["idasesor"];
        $respuesta["numeroempleados"] = $arrLiq["numeroempleados"];
        $respuesta["pagoafiliacion"] = $arrLiq["pagoafiliacion"];
        $respuesta["numerofactura"] = $arrLiq["numerofactura"];
        $respuesta["ticketid"] = $arrLiq["ticketid"];


        $respuesta["incluirformularios"] = $arrLiq["incluirformularios"];
        $respuesta["incluircertificados"] = $arrLiq["incluircertificados"];
        $respuesta["incluirdiploma"] = $arrLiq["incluirdiploma"];
        $respuesta["incluircartulina"] = $arrLiq["incluircartulina"];
        $respuesta["matricularpnat"] = $arrLiq["matricularpnat"];
        $respuesta["matricularest"] = $arrLiq["matricularest"];
        $respuesta["regimentributario"] = $arrLiq["regimentributario"];
        $respuesta["tipomatricula"] = $arrLiq["tipomatricula"];
        $respuesta["camaracambidom"] = $arrLiq["camaracambidom"];
        $respuesta["matriculacambidom"] = $arrLiq["matriculacambidom"];
        $respuesta["municipiocambidom"] = $arrLiq["municipiocambidom"];
        $respuesta["fecmatcambidom"] = $arrLiq["fecmatcambidom"];
        $respuesta["benart7"] = $arrLiq["benart7"];
        $respuesta["controlfirma"] = $arrLiq["controlfirma"];
        $respuesta["actualizacionciiuversion4"] = $arrLiq["actualizacionciiuversion4"];
        $respuesta["reliquidacion"] = $arrLiq["reliquidacion"];

        $respuesta["capital"] = $arrLiq["capital"];
        $respuesta["tipodoc"] = $arrLiq["tipodoc"];
        $respuesta["numdoc"] = $arrLiq["numdoc"];
        $respuesta["fechadoc"] = $arrLiq["fechadoc"];
        $respuesta["origendoc"] = $arrLiq["origendoc"];
        $respuesta["mundoc"] = $arrLiq["mundoc"];
        $respuesta["organizacion"] = $arrLiq["organizacion"];
        $respuesta["categoria"] = $arrLiq["categoria"];

        $respuesta["tipoiderepleg"] = $arrLiq["tipoiderepleg"];
        $respuesta["iderepleg"] = $arrLiq["iderepleg"];
        $respuesta["nombrerepleg"] = $arrLiq["nombrerepleg"];

        $respuesta["tipoideradicador"] = $arrLiq["tipoideradicador"];
        $respuesta["ideradicador"] = $arrLiq["ideradicador"];
        $respuesta["nombreradicador"] = $arrLiq["nombreradicador"];
        $respuesta["fechaexpradicador"] = $arrLiq["fechaexpradicador"];
        $respuesta["emailradicador"] = $arrLiq["emailradicador"];
        $respuesta["telefonoradicador"] = $arrLiq["telefonoradicador"];
        $respuesta["celularradicador"] = $arrLiq["celularradicador"];

        $respuesta["proyectocaja"] = $arrLiq["proyectocaja"];
        $respuesta["cargoafiliacion"] = $arrLiq["cargoafiliacion"];
        $respuesta["cargogastoadministrativo"] = $arrLiq["cargogastoadministrativo"];
        $respuesta["cargoentidadoficial"] = $arrLiq["cargoentidadoficial"];
        $respuesta["cargoconsulta"] = $arrLiq["cargoconsulta"];
        $respuesta["emailcontrol"] = $arrLiq["emailcontrol"];

        // 2016-04-08: JINT
        $respuesta["tramitepresencial"] = $arrLiq["tramitepresencial"];
        if ($respuesta["tramitepresencial"] == '') {
            if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
                $respuesta["tramitepresencial"] = '1'; // Tramite virtual
            } else {
                $respuesta["tramitepresencial"] = '4'; // Trámite presencial
            }
        }

        // 2016-09-07: JINT
        $respuesta["firmadoelectronicamente"] = $arrLiq["firmadoelectronicamente"];

        $respuesta["cumplorequisitosbenley1780"] = $arrLiq["cumplorequisitosbenley1780"];
        $respuesta["mantengorequisitosbenley1780"] = $arrLiq["mantengorequisitosbenley1780"];
        $respuesta["renunciobeneficiosley1780"] = $arrLiq["renunciobeneficiosley1780"];
        $respuesta["controlactividadaltoimpacto"] = $arrLiq["controlactividadaltoimpacto"];
        $respuesta["multadoponal"] = $arrLiq["multadoponal"];

        //
        $respuesta["rues_claseidentificacion "] = '';


        $temCampos = retornarRegistrosMysqli2($dbx, 'mreg_liquidacion_campos', "idliquidacion=" . $numliq, "campo");
        foreach ($temCampos as $c) {
            if ($c["campo"] == 'incrementocupocertificados') {
                $respuesta[$c["campo"]] = intval(trim($c["contenido"]));
            } else {
                if ($c["campo"] != 'firmadoelectronicamente') {
                    $respuesta[$c["campo"]] = trim($c["contenido"]);
                }
            }
        }
        unset($temCampos);


        //
        // En caso de tr&aacute;mites rues	
        $temx = retornarRegistrosMysqli2($dbx, 'mreg_liquidacion_textos_rues', "idliquidacion='" . $numliq . "'", "id");
        $ix = 0;
        foreach ($temx as $x) {
            $ix++;
            $respuesta["rues_textos"][$ix] = stripslashes($x);
        }
        unset($temx);

        //	
        //
        $respuesta["matriculabase"] = '';
        $respuesta["proponentebase"] = '';
        $respuesta["nrocontrolsipref"] = $arrLiq["nrocontrolsipref"];

        //
        $arrTem1 = retornarRegistroMysqli2($dbx, 'bas_tipotramites', "id='" . $respuesta["tipotramite"] . "'");
        if ($arrTem1 && !empty($arrTem1)) {
            if ($arrTem1["tiporegistro"] == 'RegMer' || $arrTem1["tiporegistro"] == 'RegEsadl') {
                $respuesta["matriculabase"] = $respuesta["idexpedientebase"];
            }
            if ($arrTem1["tiporegistro"] == 'RegPro') {
                $respuesta["proponentebase"] = $respuesta["idexpedientebase"];
            }
        }

        //
        unset($arrLiq);

        //
        $respuesta["expedientes"] = array();
        $respuesta["transacciones"] = array();
        $respuesta["liquidacion"] = array();


        // Arma arreglo de la liquidacion
        $arrDet = retornarRegistrosMysqli2($dbx, 'mreg_liquidaciondetalle', "idliquidacion=" . $respuesta["numeroliquidacion"], "idsec");
        $i = 0;
        foreach ($arrDet as $lin) {
            $i++;
            $respuesta["liquidacion"][$i]["idsec"] = sprintf("%03s", $lin["idsec"]);
            $respuesta["liquidacion"][$i]["idservicio"] = $lin["idservicio"];
            if (!isset($lin["cc"])) {
                $lin["cc"] = '';
            }
            $respuesta["liquidacion"][$i]["cc"] = $lin["cc"];
            $respuesta["liquidacion"][$i]["expediente"] = $lin["expediente"];
            $respuesta["liquidacion"][$i]["nombre"] = $lin["nombre"];
            $respuesta["liquidacion"][$i]["ano"] = $lin["ano"];
            $respuesta["liquidacion"][$i]["cantidad"] = $lin["cantidad"];
            $respuesta["liquidacion"][$i]["valorbase"] = $lin["valorbase"];
            $respuesta["liquidacion"][$i]["porcentaje"] = $lin["porcentaje"];
            $respuesta["liquidacion"][$i]["valorservicio"] = $lin["valorservicio"];
            $respuesta["liquidacion"][$i]["benart7"] = $lin["benart7"];
            $respuesta["liquidacion"][$i]["benley1780"] = $lin["benley1780"];
            $respuesta["liquidacion"][$i]["reliquidacion"] = $lin["reliquidacion"];
            $respuesta["liquidacion"][$i]["serviciobase"] = $lin["serviciobase"];
            $respuesta["liquidacion"][$i]["pagoafiliacion"] = $lin["pagoafiliacion"];
            $respuesta["liquidacion"][$i]["ir"] = $lin["ir"];
            $respuesta["liquidacion"][$i]["iva"] = $lin["iva"];
            $respuesta["liquidacion"][$i]["idalerta"] = $lin["idalerta"];
        }
        unset($arrDet);

        // Arma arreglo de la liquidaci&oacute;n RUES
        $arrDet = retornarRegistrosMysqli2($dbx, 'mreg_liquidaciondetalle_rues', "idliquidacion=" . $respuesta["numeroliquidacion"], "secuencia");
        $i = 0;
        foreach ($arrDet as $lin) {
            $i++;
            $respuesta["rues_servicios"][$i]["codigo_servicio"] = $lin["codigo_servicio"];
            $respuesta["rues_servicios"][$i]["descripcion_servicio"] = $lin["descripcion_servicio"];
            $respuesta["rues_servicios"][$i]["orden_servicio"] = $lin["orden_servicio"];
            $respuesta["rues_servicios"][$i]["orden_servicio_asociado"] = $lin["orden_servicio_asociado"];
            $respuesta["rues_servicios"][$i]["nombre_base"] = $lin["nombre_base"];
            $respuesta["rues_servicios"][$i]["valor_base"] = $lin["valor_base"];
            $respuesta["rues_servicios"][$i]["valor_liquidacion"] = $lin["valor_liquidacion"];
            $respuesta["rues_servicios"][$i]["cantidad_servicio"] = $lin["cantidad_servicio"];
            $respuesta["rues_servicios"][$i]["indicador_base"] = $lin["indicador_base"];
            $respuesta["rues_servicios"][$i]["indicador_renovacion"] = $lin["indicador_renovacion"];
            $respuesta["rues_servicios"][$i]["matricula_servicio"] = $lin["matricula_servicio"];
            $respuesta["rues_servicios"][$i]["nombre_matriculado"] = $lin["nombre_matriculado"];
            $respuesta["rues_servicios"][$i]["ano_renovacion"] = $lin["ano_renovacion"];
            $respuesta["rues_servicios"][$i]["valor_activos_sin_ajustes"] = $lin["valor_activos_sin_ajustes"];
        }
        unset($arrDet);

        // Arma arreglo de expedientes
        $arrExp = retornarRegistrosMysqli2($dbx, 'mreg_liquidacionexpedientes', "idliquidacion=" . $respuesta["numeroliquidacion"], "secuencia");
        if ($arrExp && !empty($arrExp)) {
            $i = 0;
            foreach ($arrExp as $lin) {
                if ($lin["registrobase"] == 'S') {
                    if (trim($lin["primeranorenovado"]) == '') {
                        $lin["primeranorenovado"] = $lin["ultimoanorenovado"];
                    }
                }
                $i++;
                if (!isset($lin["cc"])) {
                    $lin["cc"] = '';
                }
                $respuesta["expedientes"][$i]["cc"] = $lin["cc"];
                $respuesta["expedientes"][$i]["matricula"] = $lin["matricula"];
                $respuesta["expedientes"][$i]["proponente"] = $lin["proponente"];
                $respuesta["expedientes"][$i]["numrue"] = $lin["numrue"];
                $respuesta["expedientes"][$i]["idtipoidentificacion"] = $lin["idtipoidentificacion"];
                $respuesta["expedientes"][$i]["identificacion"] = $lin["identificacion"];
                $respuesta["expedientes"][$i]["razonsocial"] = $lin["razonsocial"];
                $respuesta["expedientes"][$i]["ape1"] = $lin["ape1"];
                $respuesta["expedientes"][$i]["ape2"] = $lin["ape2"];
                $respuesta["expedientes"][$i]["nom1"] = $lin["nom1"];
                $respuesta["expedientes"][$i]["nom2"] = $lin["nom2"];
                $respuesta["expedientes"][$i]["organizacion"] = $lin["organizacion"];
                $respuesta["expedientes"][$i]["categoria"] = $lin["categoria"];
                $respuesta["expedientes"][$i]["afiliado"] = $lin["afiliado"];
                $respuesta["expedientes"][$i]["propietariojurisdiccion"] = $lin["propietariojurisdiccion"];
                $respuesta["expedientes"][$i]["ultimoanoafiliado"] = $lin["ultimoanoafiliado"];
                $respuesta["expedientes"][$i]["primeranorenovado"] = $lin["primeranorenovado"];
                $respuesta["expedientes"][$i]["ultimoanorenovado"] = $lin["ultimoanorenovado"];
                $respuesta["expedientes"][$i]["ultimosactivos"] = $lin["ultimosactivos"];
                $respuesta["expedientes"][$i]["nuevosactivos"] = $lin["nuevosactivos"];
                $respuesta["expedientes"][$i]["actividad"] = $lin["actividad"];
                $respuesta["expedientes"][$i]["registrobase"] = $lin["registrobase"];
                $respuesta["expedientes"][$i]["benart7"] = $lin["benart7"];
                $respuesta["expedientes"][$i]["benley1780"] = $lin["benley1780"];
                $respuesta["expedientes"][$i]["fechanacimiento"] = $lin["fechanacimiento"];
                $respuesta["expedientes"][$i]["renovaresteano"] = $lin["renovaresteano"];
                $respuesta["expedientes"][$i]["fechamatricula"] = $lin["fechamatricula"];
                $respuesta["expedientes"][$i]["fecmatant"] = $lin["fecmatant"];
                $respuesta["expedientes"][$i]["reliquidacion"] = $lin["reliquidacion"];
                $respuesta["expedientes"][$i]["controlpot"] = $lin["controlpot"];
                $respuesta["expedientes"][$i]["dircom"] = $lin["dircom"];
                $respuesta["expedientes"][$i]["muncom"] = $lin["muncom"];
            }
        }
        unset($arrExp);

        // echo "expedientes leidos = " . $i. '<br>';
        // Arma arreglo de transacciones
        $arrTra = retornarRegistrosMysqli2($dbx, 'mreg_liquidacion_transacciones', "idliquidacion=" . $respuesta["numeroliquidacion"], "idsecuencia");
        $i = 0;
        if ($arrTra && !empty($arrTra)) {
            foreach ($arrTra as $tra) {
                $i++;
                $respuesta["transacciones"][$i] = $tra;
            }
            unset($arrTra);
        }

        //
        $respuesta["iLin"] = $i;

        //
        if ($respuesta["numerorecuperacion"] != '') {
            if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Foto.jpg')) {
                $respuesta["foto"] = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Foto.jpg';
                $respuesta["fotoabsoluta"] = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Foto.jpg';
            } else {
                $respuesta["fotoabsoluta"] = 'images/sii/people.png';
                $respuesta["foto"] = '../../images/sii/people.png';
            }

            if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula1.jpg')) {
                $respuesta["cedula1"] = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula1.jpg';
                $respuesta["cedula1absoluta"] = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula1.jpg';
            } else {
                $respuesta["cedula1absoluta"] = 'images/sii/people.png';
                $respuesta["cedula1"] = '../../images/sii/people.png';
            }

            if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula2.jpg')) {
                $respuesta["cedula2"] = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula2.jpg';
                $respuesta["cedula2absoluta"] = PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/fotoEvidencias/' . substr($respuesta["numerorecuperacion"], 0, 3) . '/' . $respuesta["numerorecuperacion"] . '-Cedula2.jpg';
            } else {
                $respuesta["cedula2absoluta"] = 'images/sii/people.png';
                $respuesta["cedula2"] = '../../images/sii/people.png';
            }
        }

        //
        $temx = retornarRegistroMysqli2($dbx, 'mreg_liquidacionfirmas', "idliquidacion=" . $respuesta["numeroliquidacion"]);
        if ($temx && !empty($temx)) {
            $respuesta["idclasefirmamanuscrita"] = $temx["idclase"];
            $respuesta["numidfirmamanuscrita"] = $temx["numid"];
            $respuesta["emailfirmamanuscrita"] = $temx["email"];
            $respuesta["nombrefirmamanuscrita"] = $temx["nombre"];
            $respuesta["firmamanuscritabase64"] = $temx["firma"];
            $respuesta["fechafirmamanuscrita"] = $temx["fecha"];
            $respuesta["horafirmamanuscrita"] = $temx["hora"];
            $respuesta["ipfirmamanuscrita"] = $temx["ip"];
        }


        return $respuesta;
    }

}
