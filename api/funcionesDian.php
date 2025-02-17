<?php

class funcionesDian {

    public static function retornarExpedienteSolicitudNit($dbx, $arrTem, $prueba = 'no') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //    
        $retorno["codigoError"] = '0000';
        $retorno["msgError"] = '';
        $retorno["datos"] = array();
        $retorno["inconsistencias"] = array();
        $iIcon = 0;
        $iData = 0;

        // $arrTem = retornarRegistro('mreg_est_inscritos', "matricula='" . ltrim($mat, "0") . "'");
        if ($arrTem === false || empty($arrTem)) {
            $retorno["codigoError"] = '9995';
            $retorno["msgError"] = 'Matricula no encontrada en el sistema SII';
            return $retorno;
        }
        if ($arrTem["estadomatricula"] != 'MA' && $arrTem["estadomatricula"] != 'IA') {
            $retorno["codigoError"] = '0004';
            $retorno["msgError"] = 'Matricula no esta activa en el SII';
            return $retorno;
        }
        if ($arrTem["organizacion"] == '02' || $arrTem["categoria"] == '2' || $arrTem["categoria"] == '3') {
            $retorno["codigoError"] = '0005';
            $retorno["msgError"] = 'Matricula no es de persona natural o juridica principal';
            return $retorno;
        }
        if ($arrTem["fechamatricula"] < '20170101') {
            $retorno["codigoError"] = '0003';
            $retorno["msgError"] = 'Matricula es anterior al 2017-01-01';
            return $retorno;
        }
        if (ltrim($arrTem["nit"], "0") != '' && $prueba == 'no') {
            $retorno["codigoError"] = '0006';
            $retorno["msgError"] = 'La matricula ya tiene un nit asignado';
            return $retorno;
        }

        $raiz = CODIGO_EMPRESA . sprintf("%010s", $arrTem["matricula"]);

        // Metodo a consumir
        $iData++;
        $retorno["datos"][$iData] = $raiz . '999' . '00' . '02';

        // Cédula responsable
        $iData++;
        $retorno["datos"][$iData] = $raiz . '984' . '00' . DIAN_USUARIO_CEDULA;

        // nombre responsable
        $iData++;
        $retorno["datos"][$iData] = $raiz . '985' . '00' . DIAN_USUARIO_NOMBRE;

        // Número del prerut
        $iData++;
        $retorno["datos"][$iData] = $raiz . '004' . '00' . sprintf("%013s", $arrTem["prerut"]);

        // Código de la cámara
        $iData++;
        $retorno["datos"][$iData] = $raiz . '170' . '00' . CODIGO_EMPRESA;

        // Inscripción al RUT
        $iData++;
        $retorno["datos"][$iData] = $raiz . '002' . '00' . '01';

        // Nit
        $iData++;
        $retorno["datos"][$iData] = $raiz . '005' . '00' . '';

        // DV
        $iData++;
        $retorno["datos"][$iData] = $raiz . '006' . '00' . '';

        // Tipo contribuyente (1) juridica, (2) persona natural
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '024' . '00' . '2';
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '024' . '00' . '1';
        }

        // Tipo de identificación
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $ti = \funcionesDian::asignarTipoIde($arrTem["tipoidentificacion"]);
            $retorno["datos"][$iData] = $raiz . '025' . '00' . $ti;
        }

        // Número del documento de identidad
        $iData++;
        $retorno["datos"][$iData] = $raiz . '026' . '00' . $arrTem["identificacion"];

        // Fecha de expedición
        $iData++;
        $retorno["datos"][$iData] = $raiz . '027' . '00' . $arrTem["fecexpdoc"];

        // Pais de expedición
        if ($arrTem["organizacion"] == '01') {
            if (is_numeric($arrTem["paisexpdoc"])) {
                if (ltrim($arrTem["paisexpdoc"], "0") == '') {
                    if ($arrTem["tipoidentificacion"] == '1' || $arrTem["tipoidentificacion"] == '4') {
                        $arrTem["paisexpdoc"] = '169';
                    }
                }
                $iData++;
                $retorno["datos"][$iData] = $raiz . '028' . '00' . substr(sprintf("%04s", $arrTem["paisexpdoc"]), 1);
            } else {
                if (!isset($_SESSION["paisescod"])) {
                    $_SESSION["paisescod"] = array();
                    $arrTem = retornarRegistrosMysqliApi($dbx, 'bas_paises', "1=1", "codnumpais");
                    foreach ($arrTem as $t) {
                        $_SESSION["paisescod"][$t["idpais"]] = $t;
                    }
                    unset($arrTem);
                }
                $codigoPais = '';
                if (isset($_SESSION["paisescod"][$arrTem["paisexpdoc"]])) {
                    $codigoPais = $_SESSION["paisescod"][$arrTem["paisexpdoc"]]["codnumpais"];
                }
                if ($codigoPais == '') {
                    if ($arrTem["tipoidentificacion"] == '1' || $arrTem["tipoidentificacion"] == '4') {
                        $arrTem["paisexpdoc"] = '0169'; //Colombia
                        $codigoPais = '0169';
                    }
                }
                $iData++;
                $retorno["datos"][$iData] = $raiz . '028' . '00' . substr(sprintf("%04s", $codigoPais), 1);
            }
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '028' . '00';
        }

        // Municipio y departamento de expedición
        if ($arrTem["tipoidentificacion"] == '1' ||
                $arrTem["tipoidentificacion"] == '4' ||
                $arrTem["tipoidentificacion"] == 'R') {
            if (substr(sprintf("%04s", $codigoPais), 1) == '169') {
                $iData++;
                $retorno["datos"][$iData] = $raiz . '029' . '00' . substr($arrTem["idmunidoc"], 0, 2);
                $iData++;
                $retorno["datos"][$iData] = $raiz . '030' . '00' . substr($arrTem["idmunidoc"], 2);
            } else {
                $iData++;
                $retorno["datos"][$iData] = $raiz . '029' . '00' . '';
                $iData++;
                $retorno["datos"][$iData] = $raiz . '030' . '00' . '';
            }
        }

        // Apellidos, nombres y razón social
        $iData++;
        $retorno["datos"][$iData] = $raiz . '031' . '00' . trim($arrTem["ape1"]);
        $iData++;
        $retorno["datos"][$iData] = $raiz . '032' . '00' . trim($arrTem["ape2"]);
        $iData++;
        $retorno["datos"][$iData] = $raiz . '033' . '00' . trim($arrTem["nom1"]);
        $iData++;
        $retorno["datos"][$iData] = $raiz . '034' . '00' . trim($arrTem["nom2"]);
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '035' . '00' . ''; // Sin razon social
        } else {
            if (trim(substr($arrTem["nombre"], 0, 175)) != '') {
                $iData++;
                $retorno["datos"][$iData] = $raiz . '035' . '01' . trim(substr($arrTem["nombre"], 0, 175)); // 
            }
            if (trim(substr($arrTem["nombre"], 175)) != '') {
                $iData++;
                $retorno["datos"][$iData] = $raiz . '035' . '02' . trim(substr($arrTem["nombre"], 175)); // 
            }
        }

        // vacio
        $iData++;
        $retorno["datos"][$iData] = $raiz . '036' . '00' . '';

        // Sigla        
        $iData++;
        $retorno["datos"][$iData] = $raiz . '037' . '00' . trim($arrTem["sigla"]);

        // Pais de dirección        
        $iData++;
        $retorno["datos"][$iData] = $raiz . '038' . '00' . '169';

        // Departamento de la direccion
        $iData++;
        $retorno["datos"][$iData] = $raiz . '039' . '00' . substr($arrTem["muncom"], 0, 2);

        // Ciudad de la direccion
        $iData++;
        $retorno["datos"][$iData] = $raiz . '040' . '00' . substr($arrTem["muncom"], 2);

        // direccion
        $iData++;
        $retorno["datos"][$iData] = $raiz . '041' . '00' . $arrTem["dircom"];

        // Email
        $iData++;
        $retorno["datos"][$iData] = $raiz . '042' . '00' . $arrTem["emailcom"];

        // AA
        $iData++;
        $retorno["datos"][$iData] = $raiz . '043' . '00' . '';

        // telcom1
        $iData++;
        $retorno["datos"][$iData] = $raiz . '044' . '00' . $arrTem["telcom1"];

        // telcom2
        $iData++;
        $retorno["datos"][$iData] = $raiz . '045' . '00' . $arrTem["telcom2"];

        // Ciiu principal
        $iData++;
        $retorno["datos"][$iData] = $raiz . '046' . '00' . substr($arrTem["ciius"][1], 1);

        // Fecha de inicio del Ciiu principal
        $iData++;
        $retorno["datos"][$iData] = $raiz . '047' . '00' . trim(ltrim($arrTem["feciniact1"], "0"));

        // Ciiu secundario
        if (isset($arrTem["ciius"][2]) && trim($arrTem["ciius"][2] != '')) {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '048' . '00' . substr($arrTem["ciius"][2], 1);

            // Fecha inicio actividad secundaria
            $iData++;
            $retorno["datos"][$iData] = $raiz . '049' . '00' . trim(ltrim($arrTem["feciniact2"], "0"));
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '048' . '00' . '';

            $iData++;
            $retorno["datos"][$iData] = $raiz . '049' . '00' . '';
        }

        // Ciius adicionales
        if (isset($arrTem["ciius"][3]) && trim($arrTem["ciius"][3] != '')) {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '050' . '01' . substr($arrTem["ciius"][3], 1);
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '050' . '01' . '';
        }

        if (isset($arrTem["ciius"][4]) && trim($arrTem["ciius"][4] != '')) {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '050' . '02' . substr($arrTem["ciius"][4], 1);
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '050' . '02' . '';
        }

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '051' . '00' . '';

        // Número de establecimientos
        $iData++;
        $retorno["datos"][$iData] = $raiz . '052' . '00' . sprintf("%03s", count($arrTem["establecimientos"]));

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '053' . '00' . '';

        // Códigos aduaneros 054.XX
        /*
          $cods = explode(",", $arrTem["codaduaneros"]);
          if (!empty($cods)) {
          $iCods = 0;
          foreach ($cods as $c) {
          $iCods++;
          $iData++;
          $retorno["datos"][$iData] = $raiz . '054' . sprintf("%02s", $iCods) . $c;
          }
          }
         */

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '055' . '00' . '';

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '056' . '00' . '';

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '057' . '00' . '';

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '058' . '00' . '';

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '059' . '00' . '';

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '060' . '00' . '';

        // Vacío
        $iData++;
        $retorno["datos"][$iData] = $raiz . '061' . '00' . '';

        // Naturaleza
        if ($arrTem["organizacion"] != '01') {
            if ($arrTem["organizacion"] == '12' || $arrTem["organizacion"] == '14') {
                $nat = '02';
            } else {
                $nat = '01';
                if ($arrTem["cap_porcnalpri"] + $arrTem["cap_porcextpri"] == 100) {
                    $nat = '02';
                }
                if ($arrTem["cap_porcnalpub"] + $arrTem["cap_porcextpub"] == 100) {
                    $nat = '03';
                }
            }
            $iData++;
            $retorno["datos"][$iData] = $raiz . '062' . '00' . $nat; // 
        }

        // Tipo societario
        if ($arrTem["organizacion"] != '01' && $arrTem["organizacion"] != '12' && $arrTem["organizacion"] != '14') {
            $tsoc = '';
            switch ($arrTem["organizacion"]) {
                case "03" : $tsoc = '10'; // Limitadas
                    break;
                case "04" : $tsoc = '03'; // Anonimas
                    break;
                case "05" : $tsoc = '04'; // Colectiva
                    break;
                case "06" : $tsoc = '07'; // Comandita simple
                    break;
                case "07" : $tsoc = '08'; // Comandita por acciones
                    break;
                case "08" : $tsoc = '10'; // Sociedades extranjeras
                    break;
                case "09" : $tsoc = '09';  // asociativas de trabajo
                    break;
                case "10" : $tsoc = '09'; // Civiles
                    break;
                case "11" : $tsoc = '02'; // Unipersonales
                    break;
                // case "12" : $tsoc = '99'; // Otras
                //     break;
                // case "14" : $tsoc = '99'; // Otras
                //    break;
                case "16" : $tsoc = '12'; // SAS
                    break;
                default : $tsoc = '99'; // Otras
                    break;
            }
            $iData++;
            $retorno["datos"][$iData] = $raiz . '063' . '00' . $tsoc;
        }

        //
        $t064 = '';
        $t065 = '';
        $t066 = '';
        $t067 = '';
        $t068 = '';
        $t069 = '';

        //
        if ($arrTem["organizacion"] == '12' || $arrTem["organizacion"] == '14') {
            $claseespe = retornarRegistroMysqliApi($dbx, 'mreg_clase_esadl', "id='" . $arrTem["claseespesadl"] . "'");
            if ($claseespe && !empty($claseespe)) {
                if (trim($claseespe["dian064"]) != '') {
                    $t064 = trim($claseespe["dian064"]);
                }
                if (trim($claseespe["dian065"]) != '') {
                    $t065 = trim($claseespe["dian065"]);
                }
                if (trim($claseespe["dian066"]) != '') {
                    $t066 = trim($claseespe["dian066"]);
                }
                if (trim($claseespe["dian067"]) != '') {
                    $t067 = trim($claseespe["dian067"]);
                }
                if (trim($claseespe["dian068"]) != '') {
                    $t068 = trim($claseespe["dian068"]);
                }
                if (trim($claseespe["dian069"]) != '') {
                    $t069 = trim($claseespe["dian069"]);
                }
            } else {
                if ($nat != '02') {
                    if ($arrTem["organizacion"] == '12' || $arrTem["organizacion"] == '14') {
                        if (trim($arrTem["ctrderpub"]) != '') {
                            $t064 = $arrTem["ctrderpub"];
                        }
                    }
                }
                $tf = '';
                if ($arrTem["ctrclaseespeesadl"] == '22') {
                    $t065 = '99';
                }
                $t066 = '';
                if ($arrTem["ctrcodcoop"] != '') {
                    $t066 = $arrTem["ctrcodcoop"];
                }
                $t067 = '';
                if ($_SESSION["organizacion"] == '08') {
                    $t067 = '10';
                }
                $t069 = '';
                if ($arrTem["organizacion"] == '09') {
                    $t069 = '09';
                } else {
                    if ($arrTem["organizacion"] == '12' || $arrTem["organizacion"] == '14') {
                        $t069 = $arrTem["ctrcodotras"];
                    }
                }
            }
        }

        if ($t064 != '') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '064' . '00' . $t064;
        }

        // Fondos (065)
        if ($t065 != '') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '065' . '00' . $t065;
        }

        // Código cooperarivas (066)
        if ($t066 != '') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '066' . '00' . $t066;
        }

        // Sociedades y organismos extranjeros (067)
        if ($t067 != '') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '067' . '00' . $t067;
        }

        // Sin personería jurídica (068)
        if ($t068 != '') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '068' . '00' . $t068;
        }

        // Otras no clasificadas (069)
        if ($t069 != '') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '069' . '00' . $t069;
        }

        // Otras no clasificadas (070)
        if ($arrTem["organizacion"] != '01') {
            $tb = '01';
            if ($arrTem["organizacion"] == '12' || $arrTem["organizacion"] == '14') {
                $tb = '02';
            }
            $iData++;
            $retorno["datos"][$iData] = $raiz . '070' . '00' . $tb;
        }

        // Clase (071)        
        $td = '09';
        $nd = '';
        $fr = $arrTem["fechamatricula"];
        $fd = $arrTem["fechamatricula"];
        $notx = '';
        $mdoc = $arrTem["muncom"];
        if ($arrTem["organizacion"] != '01') {
            foreach ($arrTem["inscripciones"] as $ins) {
                $encontro = 'no';
                if ($ins["grupoacto"] == '005') {
                    if ($encontro == 'no') {
                        $encontro = 'si';
                        $td = '00';
                        $fr = $ins["freg"];
                        $fd = $ins["fdoc"];
                        $mdoc = $ins["idmunidoc"];
                        if ($ins["tdoc"] == '01') {
                            $td = '01';
                        }
                        if ($ins["tdoc"] == '02') {
                            $td = '05';
                            $notx = ltrim(trim($ins["idoridoc"]), "0");
                            $notx = sprintf("%06s", $notx);
                            $notx = substr($notx, 4, 2);
                        }
                        if ($ins["tdoc"] == '03') {
                            $td = '07';
                        }
                        if ($ins["tdoc"] == '06') {
                            $td = '04';
                        }
                        if ($ins["tdoc"] == '09') {
                            $td = '03';
                        }
                        if ($ins["ndoc"] == '' ||
                                $ins["ndoc"] == 'NA' ||
                                $ins["ndoc"] == 'N/A') {
                            $nd = '1';
                        } else {
                            $nd = substr(sprintf("%07s", $ins["ndoc"]), 3, 5);
                        }
                    }
                }
            }
        }

        // Tipo de documento 071.01
        $iData++;
        $retorno["datos"][$iData] = $raiz . '071' . '01' . $td;

        // Número del documento 072.01
        $iData++;
        $retorno["datos"][$iData] = $raiz . '072' . '01' . $nd;

        // Fecha del documento 073.01
        $iData++;
        $retorno["datos"][$iData] = $raiz . '073' . '01' . $fd;

        // Número de notaría 074.01
        $iData++;
        $retorno["datos"][$iData] = $raiz . '074' . '01' . $notx;

        // Entidad de registro 075.01
        $iData++;
        $retorno["datos"][$iData] = $raiz . '075' . '01' . '03';

        // Fecha de registro en cámara 076.01
        $iData++;
        $retorno["datos"][$iData] = $raiz . '076' . '01' . $fr;

        // Número de matrícula 077.01
        $xmat = sprintf("%010s", $arrTem["matricula"]);
        /*
          if (substr($arrTem["matricula"], 0, 1) == 'S') {
          $xmat = 'S00' . substr($arrTem["matricula"], 1);
          } else {
          $xmat = sprintf("%010s", $arrTem["matricula"]);
          }
         */
        $iData++;
        $retorno["datos"][$iData] = $raiz . '077' . '01' . $xmat;

        // Departamento origen del documento 078.01
        $iData++;
        $retorno["datos"][$iData] = $raiz . '078' . '01' . substr($mdoc, 0, 2);

        // Municipio origen del documento 079.01
        $iData++;
        $retorno["datos"][$iData] = $raiz . '079' . '01' . substr($mdoc, 2);

        if ($arrTem["organizacion"] != '01') {
            // Vigencia desde de la sociedad 080.01
            $iData++;
            $retorno["datos"][$iData] = $raiz . '080' . '01' . $fr;

            // Vigencia hasta de la sociedad 081.01
            if ($arrTem["organizacion"] == '01') {
                $fv = '';
            } else {
                if ($arrTem["fechavencimiento"] == '' ||
                        $arrTem["fechavencimiento"] == '99999999' ||
                        $arrTem["fechavencimiento"] == '99999998' ||
                        $arrTem["fechavencimiento"] == '99999997') {
                    $fv = '99991231';
                } else {
                    $fv = $arrTem["fechavencimiento"];
                }
            }
            $iData++;
            $retorno["datos"][$iData] = $raiz . '081' . '01' . $fv;
        }

        // Porcentaje de capital nacional 082.00
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '082' . '00' . '';
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '082' . '00' . sprintf("%05s", number_format(doubleval($arrTem["cap_porcnaltot"]), 1));
        }

        // Porcentaje de capital nacional publico 083.00
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '083' . '00' . '';
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '083' . '00' . sprintf("%05s", number_format(doubleval($arrTem["cap_porcnalpub"]), 1));
        }

        // Porcentaje de capital nacional privado 084.00
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '084' . '00' . '';
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '084' . '00' . sprintf("%05s", number_format(doubleval($arrTem["cap_porcnalpri"]), 1));
        }

        // Porcentaje de capital extranjero 085.00
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '085' . '00' . '';
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '085' . '00' . sprintf("%05s", number_format(doubleval($arrTem["cap_porcexttot"]), 1));
        }

        // Porcentaje de capital extranjero publico 086.00
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '086' . '00' . '';
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '086' . '00' . sprintf("%05s", number_format(doubleval($arrTem["cap_porcextpub"]), 1));
        }

        // Porcentaje de capital extranjero privado 087.00
        if ($arrTem["organizacion"] == '01') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '087' . '00' . '';
        } else {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '087' . '00' . sprintf("%05s", number_format(doubleval($arrTem["cap_porcextpri"]), 1));
        }

        // Entidad de vigilancia 088.00
        $iData++;
        $retorno["datos"][$iData] = $raiz . '088' . '00' . '';

        // Situación de control
        $ve = '';
        $nic = '';
        $nom = '';
        $ide = '';
        $dv = '';
        $tide = '';
        foreach ($arrTem["vinculos"] as $v) {
            // En caso de tener vínculos de controlantes
            if ($v["tipovinculo"] == 'SCCOI' ||
                    $v["tipovinculo"] == 'SCMAI' ||
                    $v["tipovinculo"] == 'SITC1' ||
                    $v["tipovinculo"] == 'SITC3'
            ) {
                $ve = '2'; // Se reporta que es controlada
                $tide = $v["idtipoidentificacionotros"];
                $nic = $v["identificacionotros"];
                $nom = $v["nombreotros"];
            }

            // En caso de tener vínculos de controladas
            if ($v["tipovinculo"] == 'SICNI' ||
                    $v["tipovinculo"] == 'SISUI' ||
                    $v["tipovinculo"] == 'SITC2' ||
                    $v["tipovinculo"] == 'SITC4'
            ) {
                $ve = '1'; // Se reporta que es controlante
                $tide = $v["idtipoidentificacionotros"];
                $nic = $v["identificacionotros"];
                $nom = $v["nombreotros"];
            }
        }
        if ($nic != '') {
            if ($tide == '2') {
                $sepide = \funcionesGenerales::separarDv($nic);
                $ide = $sepide["identificacion"];
                $dv = $sepide["dv"];
            } else {
                $ide = $nic;
                $dv = '';
            }
        }

        // 2018-07-26: JINT: Se incluye este control para prevenir que se envíe el controlante que no tenga Nit
        if ($tide != '2') {
            $ve = '';
            $ide = '';
            $dv = '';
            $nom = '';
        }

        // Situación de control - tipo  093.00
        $iData++;
        $retorno["datos"][$iData] = $raiz . '093' . '00' . $ve;

        // Situación de control - nombre   094.00
        $iData++;
        $retorno["datos"][$iData] = $raiz . '094' . '00' . '';

        // Situación de control - Identificación  095.00
        $iData++;
        if ($ide != '') {
            $retorno["datos"][$iData] = $raiz . '095' . '00' . sprintf("%013s", ltrim($ide, "0"));
        } else {
            $retorno["datos"][$iData] = $raiz . '095' . '00' . '';
        }

        // Situación de control - Dv 096.00
        $iData++;
        $retorno["datos"][$iData] = $raiz . '096' . '00' . $dv;

        // Situación de control - Nombre 097.00
        $iData++;
        $retorno["datos"][$iData] = $raiz . '097' . '00' . $nom;

        // Representantes legales
        $iInd = 0;
        foreach ($arrTem["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'RLP' || $v["tipovinculo"] == 'RLS' || $v["tipovinculo"] == 'RLS1') {
                $iInd++;

                // Tipo de representante legal 098.XX
                if ($v["tipovinculo"] == 'RLP') {
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '098' . sprintf("%02s", $iInd) . '18';
                }
                if ($v["tipovinculo"] == 'RLS' || $v["tipovinculo"] == 'RLS1') {
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '098' . sprintf("%02s", $iInd) . '19';
                }

                // fecha del nombramiento 099.XX                
                $iData++;
                $retorno["datos"][$iData] = $raiz . '099' . sprintf("%02s", $iInd) . $v["fechaotros"];

                // ftipo de documento 100.XX                
                $ti = \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]);
                $iData++;
                $retorno["datos"][$iData] = $raiz . '100' . sprintf("%02s", $iInd) . $ti;

                $ide = '';
                $dv = '';
                if ($v["idtipoidentificacionotros"] == '2') {
                    $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                    $ide = $sepide["identificacion"];
                    $dv = $sepide["dv"];
                } else {
                    $ide = $v["identificacionotros"];
                    $dv = '';
                }

                // identificacion 101.XX      
                $iData++;
                // $retorno["datos"][$iData] = $raiz . '101' . sprintf("%02s", $iInd) . sprintf("%011s", ltrim($ide, "0"));
                $retorno["datos"][$iData] = $raiz . '101' . sprintf("%02s", $iInd) . $ide;

                // dv 102.XX    
                if (trim($dv) != '') {
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '102' . sprintf("%02s", $iInd) . $dv;
                } else {
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '102' . sprintf("%02s", $iInd) . $dv;
                }

                // tarjeta profesional 103.XX      
                if (ltrim($v["numtarprofotros"], "0") == '') {
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '103' . sprintf("%02s", $iInd) . '';
                } else {
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '103' . sprintf("%02s", $iInd) . sprintf("%08s", $v["numtarprofotros"]);
                }

                // primer apellido 104.XX      
                $iData++;
                $retorno["datos"][$iData] = $raiz . '104' . sprintf("%02s", $iInd) . $v["apellido1otros"];

                // segundo apellido 105.XX      
                $iData++;
                $retorno["datos"][$iData] = $raiz . '105' . sprintf("%02s", $iInd) . $v["apellido2otros"];

                // primer nombre  106.XX      
                $iData++;
                $retorno["datos"][$iData] = $raiz . '106' . sprintf("%02s", $iInd) . $v["nombre1otros"];

                // segundo nombre  107.XX      
                $iData++;
                $retorno["datos"][$iData] = $raiz . '107' . sprintf("%02s", $iInd) . $v["nombre2otros"];

                if (ltrim($v["numidemp"], "0") != '') {
                    $ide = '';
                    $dv = '';
                    if (ltrim($v["numidemp"], "0") != '') {
                        $sepide = \funcionesGenerales::separarDv($v["numidemp"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    }
                    // ide sociedad representada  108.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '108' . sprintf("%02s", $iInd) . sprintf("%013s", ltrim($ide, "0"));

                    // dv sociedad representada  109.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '109' . sprintf("%02s", $iInd) . $dv;

                    // nombre sociedad representada  110.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '110' . sprintf("%02s", $iInd) . $v["nombreemp"];
                }
            }
        }

        // Socios
        // Se excluyen los socios en caso de anónimas o asimiladas
        $iInd = 0;
        if ($arrTem["organizacion"] == '03' ||
                $arrTem["organizacion"] == '04' ||
                $arrTem["organizacion"] == '05' ||
                $arrTem["organizacion"] == '06' ||
                $arrTem["organizacion"] == '07' ||
                $arrTem["organizacion"] == '08' ||
                $arrTem["organizacion"] == '09' ||
                ($arrTem["organizacion"] == '10' && ($arrTem["naturaleza"] == '2' || $arrTem["naturaleza"] == '4')) ||
                $arrTem["organizacion"] == '11') {
            $tot = 0;
            foreach ($arrTem["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'SOC') {
                    $tot = $tot + $v["valorconst"] + $v["va1"] + $v["va2"] + $v["va3"] + $v["va4"];
                }
            }
            foreach ($arrTem["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'SOC') {
                    $iInd++;

                    // tipo de documento 111.XX                
                    $ti = \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]);
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '111' . sprintf("%02s", $iInd) . $ti;

                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    } else {
                        $ide = $v["identificacionotros"];
                        $dv = '';
                    }

                    // identificacion 112.XX      
                    $iData++;
                    if (strlen($ide) < 13) {
                        $retorno["datos"][$iData] = $raiz . '112' . sprintf("%02s", $iInd) . sprintf("%013s", ltrim($ide, "0"));
                    } else {
                        $retorno["datos"][$iData] = $raiz . '112' . sprintf("%02s", $iInd) . $ide;
                    }

                    // dv 113.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '113' . sprintf("%02s", $iInd) . $dv;

                    // nacionalidad 114.XX      
                    $nac = '';
                    if ($v["idtipoidentificacionotros"] == '5' || $v["idtipoidentificacionotros"] == 'P' || $v["idtipoidentificacionotros"] == 'V') {
                        $nac = substr(sprintf("%04s", $v["paisotros"]), 1);
                    } else {
                        $nac = '0169';
                    }
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '114' . sprintf("%02s", $iInd) . $nac;

                    if ($v["idtipoidentificacionotros"] != '2') {

                        // primer apellido 115.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '115' . sprintf("%02s", $iInd) . $v["apellido1otros"];

                        // segundo apellido 116.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '116' . sprintf("%02s", $iInd) . $v["apellido2otros"];

                        // primer nombre  117.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '117' . sprintf("%02s", $iInd) . $v["nombre1otros"];

                        // segundo nombre  118.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '118' . sprintf("%02s", $iInd) . $v["nombre2otros"];

                        // nombre en caso de personas juridicas  119.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '119' . sprintf("%02s", $iInd) . '';
                    } else {

                        // primer apellido 115.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '115' . sprintf("%02s", $iInd) . '';

                        // segundo apellido 116.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '116' . sprintf("%02s", $iInd) . '';

                        // primer nombre  117.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '117' . sprintf("%02s", $iInd) . '';

                        // segundo nombre  118.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '118' . sprintf("%02s", $iInd) . '';

                        // nombre en caso de personas juridicas 119.XX      
                        $iData++;
                        $retorno["datos"][$iData] = $raiz . '119' . sprintf("%02s", $iInd) . $v["nombreotros"];
                    }

                    // valor del capital  120.XX      
                    $val = $v["valorconst"] + $v["va1"] + $v["va2"] + $v["va3"] + $v["va4"];
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '120' . sprintf("%02s", $iInd) . sprintf("%010s", $val);

                    // participacion 121.XX      
                    $val = $val / $tot * 100;
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '121' . sprintf("%02s", $iInd) . sprintf("%05s", number_format($val, 1));

                    // fecha de nombramiento 122.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '122' . sprintf("%02s", $iInd) . $v["fechaotros"];

                    // fecha de retiro 123.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '123' . sprintf("%02s", $iInd) . '';
                }
            }
        }

        // Junta directiva
        foreach ($arrTem["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'JDP' || $v["tipovinculoesadl"] == 'JDP' || $v["tipovinculoesadl"] == 'OAP') {
                $iInd++;

                // tipo de documento 111.XX
                $ti = \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]);
                $iData++;
                $retorno["datos"][$iData] = $raiz . '111' . sprintf("%02s", $iInd) . $ti;

                $ide = '';
                $dv = '';
                if ($v["idtipoidentificacionotros"] == '2') {
                    $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                    $ide = $sepide["identificacion"];
                    $dv = $sepide["dv"];
                } else {
                    $ide = $v["identificacionotros"];
                    $dv = '';
                }

                // identificacion 112.XX
                $iData++;
                if (strlen($ide) < 13) {
                    $retorno["datos"][$iData] = $raiz . '112' . sprintf("%02s", $iInd) . sprintf("%013s", ltrim($ide, "0"));
                } else {
                    $retorno["datos"][$iData] = $raiz . '112' . sprintf("%02s", $iInd) . $ide;
                }

                // dv 113.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '113' . sprintf("%02s", $iInd) . $dv;

                // nacionalidad 114.XX
                $nac = '';
                if ($v["idtipoidentificacionotros"] == '5' || $v["idtipoidentificacionotros"] == 'P' || $v["idtipoidentificacionotros"] == 'V') {
                    $nac = substr(sprintf("%04s", $v["paisotros"]), 1);
                } else {
                    $nac = '0169';
                }
                $iData++;
                $retorno["datos"][$iData] = $raiz . '114' . sprintf("%02s", $iInd) . $nac;

                // primer apellido 115.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '115' . sprintf("%02s", $iInd) . $v["apellido1otros"];

                // segundo apellido 116.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '116' . sprintf("%02s", $iInd) . $v["apellido2otros"];

                // primer nombre  117.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '117' . sprintf("%02s", $iInd) . $v["nombre1otros"];

                // segundo nombre  118.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '118' . sprintf("%02s", $iInd) . $v["nombre2otros"];

                // nombre sociedad representada  119.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '119' . sprintf("%02s", $iInd) . $v["nombreemp"];

                // valor del capital  120.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '120' . sprintf("%02s", $iInd) . '';

                // participacion 121.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '121' . sprintf("%02s", $iInd) . '';

                // fecha de nombramiento 122.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '122' . sprintf("%02s", $iInd) . $v["fechaotros"];

                // fecha de retiro 123.XX
                $iData++;
                $retorno["datos"][$iData] = $raiz . '123' . sprintf("%02s", $iInd) . '';
            }
        }

        // Revisores fiscales
        $ip = 0;
        $is = 0;
        $ic = 0;
        foreach ($arrTem["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'RFP' ||
                    $v["tipovinculo"] == 'RFDP') {
                $ip++;
                if ($ip == 1) {
                    // tipo de documento 124.XX                
                    $ti = \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]);
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '124' . '00' . $ti;

                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    } else {
                        $ide = $v["identificacionotros"];
                        $dv = '';
                    }

                    // identificacion 125.XX      
                    $iData++;
                    // $retorno["datos"][$iData] = $raiz . '125' . '00' . sprintf("%013s", ltrim($ide, "0"));
                    $retorno["datos"][$iData] = $raiz . '125' . '00' . $ide;

                    // dv 126.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '126' . '00' . $dv;

                    // tajeta profesional 127.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '127' . '00' . $v["numtarprofotros"];

                    // primer apellido 128.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '128' . '00' . $v["apellido1otros"];

                    // segundo apellido 129.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '129' . '00' . $v["apellido2otros"];

                    // primer nombre  130.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '130' . '00' . $v["nombre1otros"];

                    // segundo nombre  131.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '131' . '00' . $v["nombre2otros"];

                    $ide = '';
                    $dv = '';
                    if ($v["numidemp"] != '') {
                        $sepide = \funcionesGenerales::separarDv($v["numidemp"]);
                        $ide = sprintf("%013s", $sepide["identificacion"]);
                        $dv = $sepide["dv"];
                    }

                    // nit empresa que representa  132.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '132' . '00' . $ide;

                    // dv que representa  132.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '133' . '00' . $dv;

                    // nombre sociedad representada  134.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '134' . '00' . $v["nombreemp"];

                    // fecha de nombramiento 135.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '135' . '00' . $v["fechaotros"];
                }
            }

            if ($v["tipovinculo"] == 'RFS' ||
                    $v["tipovinculo"] == 'RFS1' ||
                    $v["tipovinculo"] == 'RFDS1') {
                $is++;
                if ($is == 1) {
                    // tipo de documento 136.XX                
                    $ti = \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]);
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '136' . '00' . $ti;

                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    } else {
                        $ide = $v["identificacionotros"];
                        $dv = '';
                    }

                    // identificacion 137.XX      
                    $iData++;
                    // $retorno["datos"][$iData] = $raiz . '137' . '00' . sprintf("%013s", ltrim($ide, "0"));
                    $retorno["datos"][$iData] = $raiz . '137' . '00' . $ide;

                    // dv 138.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '138' . '00' . $dv;

                    // tajeta profesional 139.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '139' . '00' . $v["numtarprofotros"];

                    // primer apellido 140.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '140' . '00' . $v["apellido1otros"];

                    // segundo apellido 141.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '141' . '00' . $v["apellido2otros"];

                    // primer nombre  142.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '142' . '00' . $v["nombre1otros"];

                    // segundo nombre  143.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '143' . '00' . $v["nombre2otros"];

                    $ide = '';
                    $dv = '';
                    if ($v["numidemp"] != '') {
                        $sepide = \funcionesGenerales::separarDv($v["numidemp"]);
                        $ide = sprintf("%013s", $sepide["identificacion"]);
                        $dv = $sepide["dv"];
                    }

                    // nit empresa que representa  144.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '144' . '00' . $ide;

                    // dv que representa  145.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '145' . '00' . $dv;

                    // nombre sociedad representada  146.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '146' . '00' . $v["nombreemp"];

                    // fecha de nombramiento 147.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '147' . '00' . $v["fechaotros"];
                }
            }

            if ($v["tipovinculo"] == 'CON') {
                $ic++;
                if ($ic == 1) {
                    // tipo de documento 148.XX                
                    $ti = \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]);
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '148' . '00' . $ti;

                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    } else {
                        $ide = $v["identificacionotros"];
                        $dv = '';
                    }

                    // identificacion 149.XX      
                    $iData++;
                    // $retorno["datos"][$iData] = $raiz . '149' . '00' . sprintf("%013s", ltrim($ide, "0"));
                    $retorno["datos"][$iData] = $raiz . '149' . '00' . $ide;

                    // dv 150.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '150' . '00' . $dv;

                    // tajeta profesional 151.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '151' . '00' . $v["numtarprofotros"];

                    // primer apellido 152.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '152' . '00' . $v["apellido1otros"];

                    // segundo apellido 153.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '153' . '00' . $v["apellido2otros"];

                    // primer nombre  154.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '154' . '00' . $v["nombre1otros"];

                    // segundo nombre  155.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '155' . '00' . $v["nombre2otros"];

                    $ide = '';
                    $dv = '';
                    if ($v["numidemp"] != '') {
                        $sepide = \funcionesGenerales::separarDv($v["numidemp"]);
                        $ide = sprintf("%013s", $sepide["identificacion"]);
                        $dv = $sepide["dv"];
                    }

                    // nit empresa que representa  156.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '156' . '00' . $ide;

                    // dv que representa  157.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '157' . '00' . $dv;

                    // nombre sociedad representada  158.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '158' . '00' . $v["nombreemp"];

                    // fecha de nombramiento 159.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '159' . '00' . $v["fechaotros"];
                }
            }
        }

        // Establecimientos
        $iInd = 0;
        foreach ($arrTem["establecimientos"] as $e) {
            $iInd++;

            // Tipo 160.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '160' . sprintf("%02s", $iInd) . '02';

            // Ciiu 161.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '161' . sprintf("%02s", $iInd) . substr($e["ciiu1"], 1);

            // nombre 162.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '162' . sprintf("%02s", $iInd) . $e["nombreestablecimiento"];

            // dpto 163.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '163' . sprintf("%02s", $iInd) . substr($e["muncom"], 0, 2);

            // dpto 164.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '164' . sprintf("%02s", $iInd) . substr($e["muncom"], 2);

            // dircom 165.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '165' . sprintf("%02s", $iInd) . $e["dircom"];

            // matricula 166.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '166' . sprintf("%02s", $iInd) . sprintf("%010s", $e["matriculaestablecimiento"]);

            // fechamatricula 167.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '167' . sprintf("%02s", $iInd) . $e["fechamatricula"];

            // telcom1 168.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '168' . sprintf("%02s", $iInd) . $e["telcom1"];

            // telcom1 169.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '169' . sprintf("%02s", $iInd) . '';
        }

        // Sucusales y agencias
        foreach ($arrTem["sucursalesagencias"] as $e) {
            $iInd++;

            $tp = '10';
            if ($e["categoriasucage"] == '3') {
                $tp = '01';
            }

            // Tipo 160.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '160' . sprintf("%02s", $iInd) . $tp;

            // Ciiu 161.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '161' . sprintf("%02s", $iInd) . substr($e["ciiu1"], 1);

            // nombre 162.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '162' . sprintf("%02s", $iInd) . $e["nombresucage"];

            // dpto 163.XX       
            $iData++;
            $retorno["datos"][$iData] = $raiz . '163' . sprintf("%02s", $iInd) . substr($e["muncom"], 0, 2);

            // dpto 164.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '164' . sprintf("%02s", $iInd) . substr($e["muncom"], 2);

            // dircom 165.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '165' . sprintf("%02s", $iInd) . $e["dircom"];

            // matricula 166.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '166' . sprintf("%02s", $iInd) . sprintf("%010s", $e["matriculasucage"]);

            // fechamatricula 167.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '167' . sprintf("%02s", $iInd) . $e["fechamatricula"];

            // telcom1 168.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '168' . sprintf("%02s", $iInd) . $e["telcom1"];

            // telcom1 169.XX      
            $iData++;
            $retorno["datos"][$iData] = $raiz . '169' . sprintf("%02s", $iInd) . '';
        }

        if ($arrTem["organizacion"] == '08' && $arrTem["categoria"] == '1') {
            if (trim($arrTem["idetriextep"]) != '') {
                $iData++;
                $retorno["datos"][$iData] = $raiz . '172' . '00' . trim($arrTem["idetriextep"]);
            }
        }

        //
        return $retorno;
    }

    public static function armarXmlSolicitudNitDian($dbx, $arrMat, $arrDats) {
        require_once ( $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php' );
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php' );
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php' );
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php' );
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $respu = array();
        $respu["codigoError"] = '0000';
        $respu["msgError"] = '';
        $respu["xml"] = '';

        $iLin = 0;
        $campos = array();
        $codigos = array();
        $camposCompletos = "";
        $i = -1;

        //
        if (!defined('DIAN_USUARIO_NOMBRE') || DIAN_USUARIO_NOMBRE == '') {
            $usuarioDian = '';
        } else {
            $usuarioDian = DIAN_USUARIO_NOMBRE;
        }
        if (!defined('DIAN_USUARIO_CARGO') || DIAN_USUARIO_CARGO == '') {
            $nombreDian = '';
        } else {
            $nombreDian = DIAN_USUARIO_CARGO;
        }

        //
        $ok = 'si';
        $okRep = 'no';
        $capjur = 0;
        $txt = '';
        foreach ($arrDats ["datos"] as $d) {
            $iLin++;
            if ($iLin == 1) {
                $campos["camara"] = substr($d, 0, 2);
                $campos["matricula"] = substr($d, 2, 10);
            }
            $codigoCampo = substr($d, 12, 3);
            $subcodigoCampo = substr($d, 15, 2);
            // $contenidoCampo = substr( $d,17,175);
            $contenidoCampo = substr(\funcionesGenerales::restaurarEspeciales($d), 17);
            if ($codigoCampo == '072') {
                if ($contenidoCampo == 'N') {
                    $contenidoCampo = '';
                }
            }

            //
            if ($codigoCampo == '026' && $arrMat ["organizacion"] == '01') {
                if ($contenidoCampo == '') {
                    $ok = 'no';
                }
            } else {
                if ($codigoCampo == '035' && $arrMat ["organizacion"] > '01') {
                    if ($contenidoCampo == '') {
                        $ok = 'no';
                    }
                }
            }

            if ($codigoCampo == '098' && $arrMat ["organizacion"] > '01') {
                if ($contenidoCampo != '') {
                    $okRep = 'si';
                }
            }

            if (($codigoCampo == '082' || $codigoCampo == '085') && $arrMat ["organizacion"] > '01') {
                $capjur = $capjur + doubleval($contenidoCampo);
            }


            //
            $campos["telefonoPreRut"] = trim(ltrim($arrMat["telcom1"], "0"));
            $campos["formularioPreRut"] = trim($arrMat ["prerut"]);

            //
            if ($usuarioDian != '' && $nombreDian != '') {
                if ($codigoCampo == '984') {
                    $contenidoCampo = $usuarioDian;
                }
                if ($codigoCampo == '985') {
                    $contenidoCampo = $nombreDian;
                }
            }

            // Se valida que los campos 984 y 985 (Usuario que realiz&oacute; la matr&iacute;cula) tengan alg&uacute;n contenido
            if ($codigoCampo == '984' || $codigoCampo == '985') {
                if (trim($contenidoCampo) == '') {
                    $txt .= $codigoCampo . ' - No se localiz&oacute; el usuario que realiz&oacute; la matr&iacute;cula, no es posible enviar a la DIAN la solicitud\n';
                }
            }

            // armamos una array con los codigos, subcodigos y contenidos
            $i++;
            $codigos[$i] = array("codigoCampo" => trim($codigoCampo),
                "subcodigoCampo" => trim($subcodigoCampo),
                "contenidoCampo" => trim($contenidoCampo)
            );
            $camposCompletos = $camposCompletos . trim($codigoCampo) . trim($subcodigoCampo) . trim($contenidoCampo);
        }

        //
        if ($ok == 'no') {
            $respu["codigoError"] = '9999';

            $respu["msgError"] = 'Revise datos';
            return $respu;
        }

        //
        if ($arrMat["organizacion"] > '01' && $okRep == 'no') {
            $respu["codigoError"] = '9999';
            $respu["msgError"] = 'Matrícula sin representación legal, revise vínculos';
            return $respu;
        }

        //
        if ($arrMat["organizacion"] > '01' && $capjur != 100) {
            $respu["codigoError"] = '9999';
            $respu["msgError"] = 'Error en composición de capitales, revise los porcentajes de capital público y privado en el formulario';
            return $respu;
        }

        //
        if ($txt != '') {
            $respu["codigoError"] = '9999';
            $respu["msgError"] = $txt;
            return $respu;
        }

        //
        $campos ["campos"] = $codigos;

        $temp = $campos["camara"]
                . $campos["matricula"]
                . $campos ["formularioPreRut"]
                . $campos["telefonoPreRut"]
                . $camposCompletos;

        $campos["md5"] = md5($temp);

        $xml = '<?xml version="1.0" encoding="utf-8"?> ';
        $xml .= "<datosmatricula>";
        $xml .= "<camara>" . $campos["camara"] . "</camara>";
        $xml .= "<matricula>" . $campos ["matricula"] . "</matricula>";
        $xml .= "<formularioPreRut>" . $campos["formularioPreRut"] . "</formularioPreRut>";
        $xml .= "<telefonoPreRut>" . $campos["telefonoPreRut"] . "</telefonoPreRut>";
        $xml .= "<arrayCampos>";

        foreach ($campos["campos"] as $campo) {
            $xml .= "<campo>";
            if ($campo["codigoCampo"] == '062') {
                if (ltrim($campo["contenidoCampo"], "0") == '') {
                    if ($arrMat["organizacion"] != '01') {
                        $campo["contenidoCampo"] = '02';
                    }
                }
                $xml .= "<codigoCampo>" . $campo["codigoCampo"] . "</codigoCampo>";
                $xml .= "<subcodigoCampo>" . $campo["subcodigoCampo"] . "</subcodigoCampo>";
                $xml .= "<contenidoCampo><![CDATA[" . $campo["contenidoCampo"] . "]]></contenidoCampo>";
            }

            if ($campo["codigoCampo"] == '984') {
                $xml .= "<codigoCampo>984</codigoCampo>";
                $xml .= "<subcodigoCampo>00</subcodigoCampo>";
                $xml .= "<contenidoCampo><![CDATA[" . $usuarioDian . "]]></contenidoCampo>";
            }
            if ($campo["codigoCampo"] == '985') {
                $xml .= "<codigoCampo>985</codigoCampo>";
                $xml .= "<subcodigoCampo>00</subcodigoCampo>";
                $xml .= "<contenidoCampo><![CDATA[" . $nombreDian . "]]></contenidoCampo>";
            }
            if (
                    $campo["codigoCampo"] != '062' &&
                    $campo["codigoCampo"] != '984' &&
                    $campo ["codigoCampo"] != '985') {
                $xml .= "<codigoCampo>" . $campo["codigoCampo"] . "</codigoCampo>";
                $xml .= "<subcodigoCampo>" . $campo["subcodigoCampo"] . "</subcodigoCampo>";
                $xml .= "<contenidoCampo><![CDATA[" . $campo["contenidoCampo"] . "]]></contenidoCampo>";
            }
            $xml .= "</campo>";
        }

        $xml .= "</arrayCampos>";
        $xml .= "<md5>" . $campos["md5"] . "</md5>";
        $xml .= "</datosmatricula>";

        $respu["xml"] = $xml;
        return $respu;
    }

    public static function armarXmlSolicitudNitDianSinPreRut($dbx, $arrMat) {
        require_once ( $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php' );
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php' );
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php' );
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php' );
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $respu = array();
        $respu["codigoError"] = '0000';
        $respu["msgError"] = '';
        $respu["errores"] = array();
        $respu["xml"] = '';

        $iLin = 0;
        $campos = array();
        $codigos = array();
        $camposCompletos = "";
        $i = -1;

        // ********************************************************************************* //
        // Preparación del XMOL
        // ********************************************************************************* //
        //
        $respu["xml"] = '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\r\n";
        $respu["xml"] .= '<d_001_4_cc xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="d_ws_d_001_4_cc.xsd">' . "\r\n";

        // ********************************************************************************* //
        // Hoja No. 1
        // ********************************************************************************* //

        $respu["xml"] .= '<h_1>' . "\r\n";
        $respu["xml"] .= '<co_h_1>' . "\r\n";
        $respu["xml"] .= '<cab>' . "\r\n";
        $respu["xml"] .= '<ca_2 v="1"/>' . "\r\n";
        $respu["xml"] .= '<ca_4/>' . "\r\n";
        $respu["xml"] .= '</cab>' . "\r\n";

        // ca_24 - Tipo de persona, natural o jurídica
        if ($arrMat["organizacion"] == '01') {
            $respu["xml"] .= '<ca_24 v="2"/>' . "\r\n";
        } else {
            $respu["xml"] .= '<ca_24 v="1"/>' . "\r\n";
        }

        // ca_25 - Tipo de identificación
        // ca_26 - Identificación
        // ca_27 - Fecha de expedición del dcto de identidad
        if ($arrMat["organizacion"] == '01') {
            $respu["xml"] .= '<ca_25 v="' . \funcionesDian::asignarTipoIde($arrMat["tipoidentificacion"]) . '"/>' . "\r\n";
            $respu["xml"] .= '<ca_26 v="' . sprintf("%011s", $arrMat["identificacion"]) . '"/>' . "\r\n";
            $respu["xml"] .= '<ca_27 v="' . \funcionesGenerales::mostrarFecha($arrMat["fecexpdoc"]) . '"/>' . "\r\n";
            if (trim($arrMat["fecexpdoc"]) == '') {
                $respu["errores"][] = 'Fecha de expedición del documento de identidad no debe ser vacía';
            }
        } else {
            $respu["xml"] .= '<ca_25 v=""/>' . "\r\n";
            $respu["xml"] .= '<ca_26 v=""/>' . "\r\n";
            $respu["xml"] .= '<ca_27 v=""/>' . "\r\n";
        }

        // ca_28 - pais de expedición del documento de identidad
        if ($arrMat["organizacion"] != '01') {
            $respu["xml"] .= '<ca_28 v=""/>' . "\r\n";
        } else {
            if ($arrMat["paisexpdoc"] == '') {
                $arrMat["paisexpdoc"] = '169';
            }
            $codigoPais = $arrMat["paisexpdoc"];
            if (ltrim($arrMat["paisexpdoc"], "0") == '') {
                if ($arrMat["tipoidentificacion"] == '1' || $arrMat["tipoidentificacion"] == '4') {
                    $arrMat["paisexpdoc"] = '169';
                    $codigoPais = '0169';
                }
            }
            $respu["xml"] .= '<ca_28 v="' . substr(sprintf("%04s", $arrMat["paisexpdoc"]), 1) . '"/>' . "\r\n";
            if (trim($arrMat["paisexpdoc"]) == '') {
                $respu["errores"][] = 'País de expedición del documento de identidad no debe ser vacío';
            }
        }

        // ca_29 - Municipio de expedición
        // ca_30 - país de expedición
        if ($arrMat["tipoidentificacion"] == '1' ||
                $arrMat["tipoidentificacion"] == '4' ||
                $arrMat["tipoidentificacion"] == 'V' ||
                $arrMat["tipoidentificacion"] == 'P' ||
                $arrMat["tipoidentificacion"] == 'R') {
            if (substr(sprintf("%04s", $codigoPais), 1) == '169') {
                $respu["xml"] .= '<ca_29 v="' . substr($arrMat["idmunidoc"], 0, 2) . '"/>' . "\r\n";
                $respu["xml"] .= '<ca_30 v="' . substr($arrMat["idmunidoc"], 2) . '"/>' . "\r\n";
                if (trim($arrMat["idmunidoc"]) == '') {
                    $respu["errores"][] = 'Municipio de expedición del documento de identidad no debe ser vacío';
                }
            } else {
                $respu["xml"] .= '<ca_29 v=""/>' . "\r\n";
                $respu["xml"] .= '<ca_30 v=""/>' . "\r\n";
            }
        }

        // ca 31 a 34 - Nombres y razón social
        $respu["xml"] .= '<ca_31 v="' . \funcionesDian::reemplazarCaracteres($arrMat["ape1"]) . '"/>' . "\r\n";
        $respu["xml"] .= '<ca_32 v="' . \funcionesDian::reemplazarCaracteres($arrMat["ape2"]) . '"/>' . "\r\n";
        $respu["xml"] .= '<ca_33 v="' . \funcionesDian::reemplazarCaracteres($arrMat["nom1"]) . '"/>' . "\r\n";
        $respu["xml"] .= '<ca_34 v="' . \funcionesDian::reemplazarCaracteres($arrMat["nom2"]) . '"/>' . "\r\n";

        // ca 35 Razón social
        if ($arrMat["organizacion"] == '01') {
            $respu["xml"] .= '<ca_35 v=""/>' . "\r\n";
        }
        if ($arrMat["organizacion"] > '01') {
            $respu["xml"] .= '<ca_35 v="' . \funcionesDian::reemplazarCaracteres($arrMat["nombre"]) . '"/>' . "\r\n";
        }

        // ca-36
        $respu["xml"] .= '<ca_36 v=""/>' . "\r\n";

        // ca-37 sigla
        $respu["xml"] .= '<ca_37 v="' . \funcionesDian::reemplazarCaracteres($arrMat["sigla"]) . '"/>' . "\r\n";

        // ca-38 pais de la dirección
        $respu["xml"] .= '<ca_38 v="169"/>' . "\r\n";

        // ca-39 Departamento comercial
        $respu["xml"] .= '<ca_39 v="' . substr($arrMat["muncom"], 0, 2) . '"/>' . "\r\n";
        if (trim($arrMat["muncom"]) == '') {
            $respu["errores"][] = 'Municipio comercial no debe ser vacío';
        }

        // ca-40 municipio comercial
        $respu["xml"] .= '<ca_40 v="' . substr($arrMat["muncom"], 2) . '"/>' . "\r\n";

        // ca-41 dirección comercial
        $respu["xml"] .= '<ca_41 v="' . $arrMat["dircom"] . '"/>' . "\r\n";
        if (trim($arrMat["dircom"]) == '') {
            $respu["errores"][] = 'Dirección comercial no debe ser vacía';
        }

        // ca-42 email comercial
        $respu["xml"] .= '<ca_42 v="' . $arrMat["emailcom"] . '"/>' . "\r\n";
        if (trim($arrMat["emailcom"]) == '') {
            $respu["errores"][] = 'Email comercial no debe ser vacío';
        }

        // ca-43 aa comercial
        $respu["xml"] .= '<ca_43 v=""/>' . "\r\n";

        // ca-44 telcom1 comercial
        $respu["xml"] .= '<ca_44 v="' . $arrMat["telcom1"] . '"/>' . "\r\n";
        if (trim($arrMat["telcom1"]) == '') {
            $respu["errores"][] = 'Telefono 1 comercial no debe ser vacío';
        }

        // ca-45 telcom2 comercial
        $respu["xml"] .= '<ca_45 v="' . $arrMat["telcom2"] . '"/>' . "\r\n";

        // ca-46 telcom2 ciiu principal
        $respu["xml"] .= '<ca_46 v="' . substr($arrMat["ciius"][1], 1) . '"/>' . "\r\n";
        if (trim($arrMat["ciius"][1]) == '') {
            $respu["errores"][] = 'Ciiu principal no debe ser vacío';
        }

        // ca-47 fecha inicio ciiu principal
        $respu["xml"] .= '<ca_47 v="' . \funcionesGenerales::mostrarFecha(trim(ltrim($arrMat["feciniact1"], "0"))) . '"/>' . "\r\n";
        if (trim($arrMat["feciniact1"]) == '') {
            $respu["errores"][] = 'Fecha de inicio de la actividad principal no debe ser vacía';
        }

        // ciiu secundario (ca-48 y ca-49)
        if (isset($arrMat["ciius"][2]) && trim($arrMat["ciius"][2]) != '') {
            $respu["xml"] .= '<ca_48 v="' . substr($arrMat["ciius"][2], 1) . '"/>' . "\r\n";
            $respu["xml"] .= '<ca_49 v="' . \funcionesGenerales::mostrarFecha(trim(ltrim($arrMat["feciniact2"], "0"))) . '"/>' . "\r\n";
        } else {
            $respu["xml"] .= '<ca_48 v=""/>' . "\r\n";
            $respu["xml"] .= '<ca_49 v=""/>' . "\r\n";
        }

        // ca-50 - ciiu alternativo
        if ($arrMat["ciius"][3] != '' || $arrMat["ciius"][4] != '') {
            $ixx = 0;
            $respu["xml"] .= '<ca_50>' . "\r\n";
            if ($arrMat["ciius"][3] != '') {
                $ixx++;
                $respu["xml"] .= '<itE id="' . $ixx . '" v="' . substr($arrMat["ciius"][3], 1) . '"/>' . "\r\n";
            }
            if ($arrMat["ciius"][4] != '') {
                $ixx++;
                $respu["xml"] .= '<itE id="' . $ixx . '" v="' . substr($arrMat["ciius"][4], 1) . '"/>' . "\r\n";
            }
            $respu["xml"] .= '</ca_50>' . "\r\n";
        }

        // ca-51 - ocupación
        $respu["xml"] .= '<ca_51 v=""/>' . "\r\n";

        // ca-52 - cantidad de establecimientos
        $cante = count($arrMat["establecimientos"]) + count($arrMat["sucursalesagencias"]);
        $respu["xml"] .= '<ca_52 v="' . sprintf("%03s", $cante) . '"/>' . "\r\n";

        // ca-53 - responsabilidades tributarias
        $iind = 0;
        if (!empty($arrMat["codrespotri"])) {
            $respu["xml"] .= '<ca_53>' . "\r\n";
            foreach ($arrMat["codrespotri"] as $rt) {
                if (trim($rt) != '') {
                    $iind++;
                    $respu["xml"] .= '<itE id="' . $iind . '" v="' . $rt . '"/>' . "\r\n";
                    if ($rt == '10') {
                        $respu["errores"][] = 'Selecciono responsabilidad 10 (usuario aduanero), no es posible realizar la solicitud del Nit';
                    }
                    if ($rt == '49' && $arrMat["organizacion"] > '01') {
                        $respu["errores"][] = 'Selecciono responsabilidad 49 (no responsable de IVA), esta responsabilidad no se debe seleccionar para personas jurídicas';
                    }
                }
            }
            $respu["xml"] .= '</ca_53>' . "\r\n";
        } else {
            $respu["errores"][] = 'Expediente sin responsabilidades tributarias';
        }

        // ca-54 - código aduaneero 
        /*
          if (trim(ltrim($arrMat["impexp"], "0")) != '') {
          $respu["xml"] .= '<ca_54>' . "\r\n";
          $ix = 0;
          if ($arrMat["impexp"] == '2' || $arrMat["impexp"] == '3') {
          $ix++;
          $respu["xml"] .= '<itE id="' . $ix . '" v="22"/>' . "\r\n";
          }
          if ($arrMat["impexp"] == '1' || $arrMat["impexp"] == '3') {
          $ix++;
          $respu["xml"] .= '<itE id="' . $ix . '" v="23"/>' . "\r\n";
          }
          $respu["xml"] .= '</ca_54>' . "\r\n";
          }
         */

        // ca-55  - como exporta
        /*
          if ($arrMat["impexp"] == '2' || $arrMat["impexp"] == '3') {
          $respu["xml"] .= '<ca_55 v="3"/>' . "\r\n";
          }

          // ca-56 - que exporta
          if ($arrMat["impexp"] == '2' || $arrMat["impexp"] == '3') {
          $respu["xml"] .= '<ca_56 v="3"/>' . "\r\n";
          }

          // ca-57 - modo de exportación
          if ($arrMat["impexp"] == '2' || $arrMat["impexp"] == '3') {
          $respu["xml"] .= '<ca_57 v=""/>' . "\r\n";
          }

          // ca-58 - CPC
          if ($arrMat["impexp"] == '2' || $arrMat["impexp"] == '3') {
          $respu["xml"] .= '<ca_58 v=""/>' . "\r\n";
          }
         */

        // ca-59 - anexos
        // $respu["xml"] .= '<ca_59 v=""/>' . "\r\n";
        // ca-60 - nro de folios
        // $respu["xml"] .= '<ca_60 v=""/>' . "\r\n";
        // ca-61 
        $respu["xml"] .= '<ca_61 v=""/>' . "\r\n";

        // Pie página 1 
        if ($arrMat["organizacion"] == '01') {
            $respu["xml"] .= '<pie>' . "\r\n";
            $respu["xml"] .= '<ca_984 v="' . trim($arrMat["ape1"]) . ' ' . trim($arrMat["ape2"]) . ' ' . trim($arrMat["nom1"]) . ' ' . trim($arrMat["nom2"]) . '"/>' . "\r\n";
            $respu["xml"] .= '<ca_985 v="CONTRIBUYENTE"/>' . "\r\n";
            $respu["xml"] .= '</pie>' . "\r\n";
        } else {
            $nomvin = '';
            foreach ($arrMat["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'RLP' || $v["tipovinculoesadl"] == 'RLP') {
                    if ($nomvin == '') {
                        $nomvin = trim($v["apellido1otros"] . ' ' . $v["apellido2otros"] . ' ' . $v["nombre1otros"] . ' ' . $v["nombre2otros"]);
                    }
                }
            }
            $respu["xml"] .= '<pie>' . "\r\n";
            $respu["xml"] .= '<ca_984 v="' . $nomvin . '"/>' . "\r\n";
            $respu["xml"] .= '<ca_985 v="' . 'REPRESENTANTE LEGAL' . '"/>' . "\r\n";
            $respu["xml"] .= '</pie>' . "\r\n";
        }
        $respu["xml"] .= '</co_h_1>' . "\r\n";
        $respu["xml"] .= '</h_1>' . "\r\n";

        // ********************************************************************************* //
        // Hoja No. 2
        // ********************************************************************************* //
        $respu["xml"] .= '<h_2>' . "\r\n";
        $respu["xml"] .= '<co_h_2 id="1">' . "\r\n";

        // ca-62 Naturaleza
        if ($arrMat["organizacion"] != '01') {
            if ($arrMat["organizacion"] == '12' || $arrMat["organizacion"] == '14') {
                $nat = '02';
            } else {
                $nat = '01';
                if ($arrMat["cap_porcnalpri"] + $arrMat["cap_porcextpri"] == 100) {
                    $nat = '02';
                }
                if ($arrMat["cap_porcnalpub"] + $arrMat["cap_porcextpub"] == 100) {
                    $nat = '03';
                }
            }
            $respu["xml"] .= '<ca_62 v="' . $nat . '"/>' . "\r\n";
            if ($arrMat["cap_porcnalpri"] + $arrMat["cap_porcextpri"] + $arrMat["cap_porcnalpub"] + $arrMat["cap_porcextpub"] != 100) {
                $respu["errores"][] = 'Composición del capital erróneo';
            }
        }


        // ca-63 Tipo societario
        if ($arrMat["organizacion"] != '01' && $arrMat["organizacion"] != '12' && $arrMat["organizacion"] != '14') {
            // if ($arrMat["organizacion"] != '01') {
            $tsoc = '';
            switch ($arrMat["organizacion"]) {
                case "03" : $tsoc = '10'; // Limitadas
                    break;
                case "04" : $tsoc = '03'; // Anonimas
                    break;
                case "05" : $tsoc = '04'; // Colectiva
                    break;
                case "06" : $tsoc = '07'; // Comandita simple
                    break;
                case "07" : $tsoc = '08'; // Comandita por acciones
                    break;
                case "08" : $tsoc = '10'; // Sociedades extranjeras
                    break;
                case "09" : $tsoc = '09';  // asociativas de trabajo
                    break;
                case "10" : $tsoc = '09'; // Civiles
                    break;
                case "11" : $tsoc = '02'; // Unipersonales
                    break;
                // case "12" : $tsoc = '99'; // Otras
                //     break;
                // case "14" : $tsoc = '99'; // Otras
                //    break;
                case "16" : $tsoc = '12'; // SAS
                    break;
                default : $tsoc = '99'; // Otras
                    break;
            }
            if ($tsoc != '') {
                $respu["xml"] .= '<ca_63 v="' . $tsoc . '"/>' . "\r\n";
            }
        }

        // ca-64 a ca-70
        $t064 = '';
        $t065 = '';
        $t066 = '';
        $t067 = '';
        $t068 = '';
        $t069 = '';

        //
        if ($arrMat["organizacion"] == '12' || $arrMat["organizacion"] == '14') {
            $claseespe = retornarRegistroMysqliApi($dbx, 'mreg_clase_esadl', "id='" . $arrMat["claseespesadl"] . "'");
            if ($claseespe && !empty($claseespe)) {
                if (trim($claseespe["dian064"]) != '') {
                    $t064 = trim($claseespe["dian064"]);
                }
                if (trim($claseespe["dian065"]) != '') {
                    $t065 = trim($claseespe["dian065"]);
                }
                if (trim($claseespe["dian066"]) != '') {
                    $t066 = trim($claseespe["dian066"]);
                }
                if (trim($claseespe["dian067"]) != '') {
                    $t067 = trim($claseespe["dian067"]);
                }
                if (trim($claseespe["dian068"]) != '') {
                    $t068 = trim($claseespe["dian068"]);
                }
                if (trim($claseespe["dian069"]) != '') {
                    $t069 = trim($claseespe["dian069"]);
                }
            } else {
                if ($nat != '02') {
                    if ($arrMat["organizacion"] == '12' || $arrMat["organizacion"] == '14') {
                        if (trim($arrMat["ctrderpub"]) != '') {
                            $t064 = $arrMat["ctrderpub"];
                        }
                    }
                }
                $tf = '';
                if ($arrMat["ctrclaseespeesadl"] == '22') {
                    $t065 = '99';
                }
                $t066 = '';
                if ($arrMat["ctrcodcoop"] != '') {
                    $t066 = $arrMat["ctrcodcoop"];
                }
                $t067 = '';
                if ($_SESSION["organizacion"] == '08') {
                    $t067 = '10';
                }
                $t069 = '';
                if ($arrMat["organizacion"] == '09') {
                    $t069 = '09';
                } else {
                    if ($arrMat["organizacion"] == '12' || $arrMat["organizacion"] == '14') {
                        $t069 = $arrMat["ctrcodotras"];
                    }
                }
            }
        }

        if ($t064 != '') {
            $respu["xml"] .= '<ca_64 v="' . $t064 . '"/>' . "\r\n";
        }

        // Fondos (065)
        if ($t065 != '') {
            $respu["xml"] .= '<ca_65 v="' . $t065 . '"/>' . "\r\n";
        }

        // Código cooperarivas (066)
        if ($t066 != '') {
            $respu["xml"] .= '<ca_66 v="' . $t066 . '"/>' . "\r\n";
        }

        // Sociedades y organismos extranjeros (067)
        if ($t067 != '') {
            $respu["xml"] .= '<ca_67 v="' . $t067 . '"/>' . "\r\n";
        }

        // Sin personería jurídica (068)
        if ($t068 != '') {
            $respu["xml"] .= '<ca_68 v="' . $t068 . '"/>' . "\r\n";
        }

        // Otras no clasificadas (069)
        if ($t069 != '') {
            $respu["xml"] .= '<ca_69 v="' . $t069 . '"/>' . "\r\n";
        }

        // Otras no clasificadas (070)
        if ($arrMat["organizacion"] != '01') {
            $tb = '01';
            if ($arrMat["organizacion"] == '12' || $arrMat["organizacion"] == '14') {
                $tb = '02';
            }
            $respu["xml"] .= '<ca_70 v="' . $tb . '"/>' . "\r\n";
        }

        // ca-71 tipo de documento de constitución
        // Clase (071)        
        if ($arrMat["organizacion"] == '01') {
            $td = '09';
            $nd = '';
            $fr = $arrMat["fechamatricula"];
            $fd = $arrMat["fechamatricula"];
            $notx = '';
            $mdoc = $arrMat["muncom"];
        } else {
            $td = '99';
            $nd = '';
            $fr = $arrMat["fechamatricula"];
            $fd = $arrMat["fechamatricula"];
            $notx = '';
            $mdoc = $arrMat["muncom"];
            if ($arrMat["organizacion"] != '01') {
                foreach ($arrMat["inscripciones"] as $ins) {
                    $encontro = 'no';
                    if ($ins["grupoacto"] == '005') {
                        if ($encontro == 'no') {
                            $encontro = 'si';
                            $td = '99';
                            $fr = $ins["freg"];
                            $fd = $ins["fdoc"];
                            $mdoc = $ins["idmunidoc"];
                            if ($ins["tdoc"] == '01') {
                                $td = '01';
                            }
                            if ($ins["tdoc"] == '02') {
                                $td = '05';
                                $notx = ltrim(trim($ins["idoridoc"]), "0");
                                $notx = sprintf("%06s", $notx);
                                $notx = substr($notx, 4, 2);
                            }
                            if ($ins["tdoc"] == '03') {
                                $td = '07';
                            }
                            if ($ins["tdoc"] == '06') {
                                $td = '04';
                            }
                            if ($ins["tdoc"] == '09') {
                                $td = '03';
                            }
                            if ($ins["ndoc"] == '' ||
                                    $ins["ndoc"] == 'NA' ||
                                    $ins["ndoc"] == 'N/A') {
                                $nd = '1';
                            } else {
                                $nd = substr(sprintf("%07s", $ins["ndoc"]), 3, 5);
                            }
                        }
                    }
                }
            }
        }
        $respu["xml"] .= '<ca_71>' . "\r\n";
        $respu["xml"] .= '<itE id="1" v="' . $td . '"/>' . "\r\n";
        $respu["xml"] .= '</ca_71>' . "\r\n";

        // ca-72 - Número del documento
        $respu["xml"] .= '<ca_72>' . "\r\n";
        $respu["xml"] .= '<itE id="1" v="' . $nd . '"/>' . "\r\n";
        $respu["xml"] .= '</ca_72>' . "\r\n";

        // ca-73 - fecha del documento
        $respu["xml"] .= '<ca_73>' . "\r\n";
        $respu["xml"] .= '<itF id="1" v="' . \funcionesGenerales::mostrarFecha($fd) . '"/>' . "\r\n";
        $respu["xml"] .= '</ca_73>' . "\r\n";

        // ca-74 - Notaria
        $respu["xml"] .= '<ca_74>' . "\r\n";
        $respu["xml"] .= '<itE id="1" v="' . $notx . '"/>' . "\r\n";
        $respu["xml"] .= '</ca_74>' . "\r\n";

        // ca-75 - Entidad de registro
        $respu["xml"] .= '<ca_75>' . "\r\n";
        $respu["xml"] .= '<itE id="1" v="03"/>' . "\r\n";
        $respu["xml"] .= '</ca_75>' . "\r\n";

        // ca-76 - fecha de registro en cámara
        $respu["xml"] .= '<ca_76>' . "\r\n";
        $respu["xml"] .= '<itF id="1" v="' . \funcionesGenerales::mostrarFecha($fr) . '"/>' . "\r\n";
        $respu["xml"] .= '</ca_76>' . "\r\n";

        // ca-77 Número de matrícula 077.01
        $xmat = sprintf("%010s", $arrMat["matricula"]);
        $respu["xml"] .= '<ca_77>' . "\r\n";
        $respu["xml"] .= '<itC id="1" v="' . $xmat . '"/>' . "\r\n";
        $respu["xml"] .= '</ca_77>' . "\r\n";

        // ca-78 Dpto del documento
        $respu["xml"] .= '<ca_78>' . "\r\n";
        $respu["xml"] .= '<itE id="1" v="' . substr($mdoc, 0, 2) . '"/>' . "\r\n";
        $respu["xml"] .= '</ca_78>' . "\r\n";

        // ca-79 municipio del documento
        $respu["xml"] .= '<ca_79>' . "\r\n";
        $respu["xml"] .= '<itE id="1" v="' . substr($mdoc, 2) . '"/>' . "\r\n";
        $respu["xml"] .= '</ca_79>' . "\r\n";

        // ca-80 fecha de constitucion
        // ca-81 fecha de vigencia
        if ($arrMat["organizacion"] == '01') {
            /*
              $respu["xml"] .= '<ca_80>' . "\r\n";
              $respu["xml"] .= '<itF id="1" v=""/>' . "\r\n";
              $respu["xml"] .= '</ca_80>' . "\r\n";
              $respu["xml"] .= '<ca_81>' . "\r\n";
              $respu["xml"] .= '<itF id="1" v=""/>' . "\r\n";
              $respu["xml"] .= '</ca_81>' . "\r\n";
             */
        } else {
            $respu["xml"] .= '<ca_80>' . "\r\n";
            $respu["xml"] .= '<itF id="1" v="' . \funcionesGenerales::mostrarFecha($fr) . '"/>' . "\r\n";
            $respu["xml"] .= '</ca_80>' . "\r\n";
            if ($arrMat["fechavencimiento"] == '' ||
                    $arrMat["fechavencimiento"] == '99999999' ||
                    $arrMat["fechavencimiento"] == '99999998' ||
                    $arrMat["fechavencimiento"] == '99999997') {
                $fv = '99991231';
            } else {
                $fv = $arrMat["fechavencimiento"];
            }
            $respu["xml"] .= '<ca_81>' . "\r\n";
            $respu["xml"] .= '<itF id="1" v="' . \funcionesGenerales::mostrarFecha($fv) . '"/>' . "\r\n";
            $respu["xml"] .= '</ca_81>' . "\r\n";
        }

        // Porcentaje de capital nacional ca-82 al ca-87
        if ($arrMat["organizacion"] > '01') {
            $pornalpub = 0;
            $pornalpri = 0;
            $porextpub = 0;
            $porextpri = 0;
            if ($arrMat["cap_porcnaltot"] > 0) {
                $pornalpub = $arrMat["cap_porcnalpub"] / $arrMat["cap_porcnaltot"] * 100;
                $pornalpri = 100 - $pornalpub;
            }
            if ($arrMat["cap_porcexttot"] > 0) {
                $porextpub = $arrMat["cap_porcextpub"] / $arrMat["cap_porcexttot"] * 100;
                $porextpri = 100 - $porextpub;
            }
        }
        
        if ($arrMat["organizacion"] == '01') {
            // if ($arrMat["tipoidentificacion"] == '1' || $arrMat["tipoidentificacion"] == '4') {
            //    $respu["xml"] .= '<ca_82 v="100.0"/>' . "\r\n";
            // } else {
                $respu["xml"] .= '<ca_82 v=""/>' . "\r\n";
            // }
        } else {
            $respu["xml"] .= '<ca_82 v="' . sprintf("%05s", number_format(doubleval($arrMat["cap_porcnaltot"]), 1)) . '"/>' . "\r\n";
        }


        if ($arrMat["organizacion"] == '01') {
            $respu["xml"] .= '<ca_83 v=""/>' . "\r\n";
        } else {
            $respu["xml"] .= '<ca_83 v="' . sprintf("%05s", number_format(doubleval($pornalpub), 1)) . '"/>' . "\r\n";
        }

        if ($arrMat["organizacion"] == '01') {
            // if ($arrMat["tipoidentificacion"] == '1' || $arrMat["tipoidentificacion"] == '4') {
            //    $respu["xml"] .= '<ca_84 v="100.0"/>' . "\r\n";
            // } else {
                $respu["xml"] .= '<ca_84 v=""/>' . "\r\n";
            // }
        } else {
            $respu["xml"] .= '<ca_84 v="' . sprintf("%05s", number_format(doubleval($pornalpri), 1)) . '"/>' . "\r\n";
        }

        if ($arrMat["organizacion"] == '01') {
            $respu["xml"] .= '<ca_85 v=""/>' . "\r\n";
        } else {
            $respu["xml"] .= '<ca_85 v="' . sprintf("%05s", number_format(doubleval($arrMat["cap_porcexttot"]), 1)) . '"/>' . "\r\n";
        }

        if ($arrMat["organizacion"] == '01') {
            $respu["xml"] .= '<ca_86 v=""/>' . "\r\n";
        } else {
            $respu["xml"] .= '<ca_86 v="' . sprintf("%05s", number_format(doubleval($porextpub), 1)) . '"/>' . "\r\n";
        }

        if ($arrMat["organizacion"] == '01') {
            $respu["xml"] .= '<ca_87 v=""/>' . "\r\n";
        } else {
            $respu["xml"] .= '<ca_87 v="' . sprintf("%05s", number_format(doubleval($porextpri), 1)) . '"/>' . "\r\n";
        }

        // ca-88 entidad de vigilancia
        // $respu["xml"] .= '<ca_88 v=""/>' . "\r\n";
        // Situación de control (ca-93 al ca-97)
        $ve = '';
        $nic = '';
        $nom = '';
        $ide = '';
        $dv = '';
        $tide = '';
        foreach ($arrMat["vinculos"] as $v) {
            // En caso de tener vínculos de controlantes
            if ($v["tipovinculo"] == 'SCCOI' ||
                    $v["tipovinculo"] == 'SCMAI' ||
                    $v["tipovinculo"] == 'SITC1' ||
                    $v["tipovinculo"] == 'SITC3'
            ) {
                $ve = '2'; // Se reporta que es controlada
                $tide = $v["idtipoidentificacionotros"];
                $nic = $v["identificacionotros"];
                $nom = $v["nombreotros"];
            }

            // En caso de tener vínculos de controladas
            if ($v["tipovinculo"] == 'SICNI' ||
                    $v["tipovinculo"] == 'SISUI' ||
                    $v["tipovinculo"] == 'SITC2' ||
                    $v["tipovinculo"] == 'SITC4'
            ) {
                $ve = '1'; // Se reporta que es controlante
            }
        }
        if ($nic != '') {
            if ($tide == '2') {
                $sepide = \funcionesGenerales::separarDv($nic);
                $ide = $sepide["identificacion"];
                $dv = $sepide["dv"];
            } else {
                $ide = $nic;
                $dv = '';
            }
        }
        if ($tide != '2') {
            $ve = '';
            $ide = '';
            $dv = '';
            $nom = '';
        }
        if (trim($ve) != '') {
            $respu["xml"] .= '<ca_93 v="' . $ve . '"/>' . "\r\n";
            $respu["xml"] .= '<ca_94 v=""/>' . "\r\n";
            if ($ide != '') {
                $respu["xml"] .= '<ca_95 v="' . ltrim($ide, "0") . '"/>' . "\r\n";
            } else {
                $respu["xml"] .= '<ca_95 v=""/>' . "\r\n";
            }
            $respu["xml"] .= '<ca_96 v="' . $dv . '"/>' . "\r\n";
            $respu["xml"] .= '<ca_97 v="' . \funcionesDian::reemplazarCaracteres($nom) . '"/>' . "\r\n";
        }
        $respu["xml"] .= '</co_h_2>' . "\r\n";
        $respu["xml"] .= '</h_2>' . "\r\n";

        // ********************************************************************************* //
        // Hoja No. 3
        // ********************************************************************************* //
        if ($arrMat["organizacion"] > '01') {
            $respu["xml"] .= '<h_3>' . "\r\n";
            $respu["xml"] .= '<co_h_3 id="1">' . "\r\n";

            // Representantes legales
            $xmlp098 = '';
            $xmlp099 = '';
            $xmlp100 = '';
            $xmlp101 = '';
            $xmlp102 = '';
            $xmlp103 = '';
            $xmlp104 = '';
            $xmlp105 = '';
            $xmlp106 = '';
            $xmlp107 = '';
            $xmlp108 = '';
            $xmlp109 = '';
            $xmlp110 = '';
            $iInd = 0;
            foreach ($arrMat["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'RLP' || $v["tipovinculo"] == 'RLS' || $v["tipovinculo"] == 'RLS1') {
                    $iInd++;

                    // ca-98 Tipo de representante
                    if ($v["tipovinculo"] == 'RLP') {
                        if ($xmlp098 == '') {
                            $xmlp098 = '<ca_98>' . "\r\n";
                        }
                        $xmlp098 .= '<itE id="' . $iInd . '" v="' . '18' . '"/>' . "\r\n";
                    }
                    if ($v["tipovinculo"] == 'RLS' || $v["tipovinculo"] == 'RLS1') {
                        if ($xmlp098 == '') {
                            $xmlp098 = '<ca_98>' . "\r\n";
                        }
                        $xmlp098 .= '<itE id="' . $iInd . '" v="' . '19' . '"/>' . "\r\n";
                    }

                    // ca-99 - fecha del nombramiento 099.XX    
                    if ($xmlp099 == '') {
                        $xmlp099 = '<ca_99>' . "\r\n";
                    }
                    $xmlp099 .= '<itF id="' . $iInd . '" v="' . \funcionesGenerales::mostrarFecha($v["fechaotros"]) . '"/>' . "\r\n";

                    // ca-100 tipo de documento
                    if ($xmlp100 == '') {
                        $xmlp100 = '<ca_100>' . "\r\n";
                    }
                    $xmlp100 .= '<itE id="' . $iInd . '" v="' . \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]) . '"/>' . "\r\n";

                    //
                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    } else {
                        $ide = $v["identificacionotros"];
                        $dv = '';
                    }

                    // ca-101 identificacion
                    if ($xmlp101 == '') {
                        $xmlp101 = '<ca_101>' . "\r\n";
                    }
                    $xmlp101 .= '<itC id="' . $iInd . '" v="' . $ide . '"/>' . "\r\n";

                    // ca-102 dv    
                    if ($xmlp102 == '') {
                        $xmlp102 = '<ca_102>' . "\r\n";
                    }
                    $xmlp102 .= '<itE id="' . $iInd . '" v="' . $dv . '"/>' . "\r\n";

                    // ca-103 tarjeta profesional
                    // if (ltrim($v["numtarprofotros"], "0") != '') {
                    if ($xmlp103 == '') {
                        $xmlp103 = '<ca_103>' . "\r\n";
                    }
                    $xmlp103 .= '<itC id="' . $iInd . '" v="' . trim($v["numtarprofotros"]) . '"/>' . "\r\n";
                    // }
                    // ca-104 primer apellido
                    if ($xmlp104 == '') {
                        $xmlp104 = '<ca_104>' . "\r\n";
                    }
                    $xmlp104 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["apellido1otros"]) . '"/>' . "\r\n";

                    // ca-105 segundo apellido
                    if ($xmlp105 == '') {
                        $xmlp105 = '<ca_105>' . "\r\n";
                    }
                    $xmlp105 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["apellido2otros"]) . '"/>' . "\r\n";

                    // ca-106 primer nombre
                    if ($xmlp106 == '') {
                        $xmlp106 = '<ca_106>' . "\r\n";
                    }
                    $xmlp106 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombre1otros"]) . '"/>' . "\r\n";

                    // ca-107 segundo nombre
                    if ($xmlp107 == '') {
                        $xmlp107 = '<ca_107>' . "\r\n";
                    }
                    $xmlp107 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombre2otros"]) . '"/>' . "\r\n";

                    if (ltrim($v["numidemp"], "0") != '') {
                        $ide = '';
                        $dv = '';
                        if (ltrim(trim($v["numidemp"]), "0") != '') {
                            $sepide = \funcionesGenerales::separarDv($v["numidemp"]);
                            $ide = $sepide["identificacion"];
                            $dv = $sepide["dv"];

                            // ca-108 sociedad representada
                            if ($xmlp108 == '') {
                                $xmlp108 = '<ca_108>' . "\r\n";
                            }
                            $xmlp108 .= '<itE id="' . $iInd . '" v="' . trim($ide) . '"/>' . "\r\n";

                            // ca-109 dv sociedad representada
                            if ($xmlp109 == '') {
                                $xmlp109 = '<ca_109>' . "\r\n";
                            }
                            $xmlp109 .= '<itE id="' . $iInd . '" v="' . $dv . '"/>' . "\r\n";

                            // ca-110 dv nombre sociedad representada
                            if ($xmlp110 == '') {
                                $xmlp110 = '<ca_110>' . "\r\n";
                            }
                            $xmlp110 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombreemp"]) . '"/>' . "\r\n";
                        }
                    }
                }
            }
            if (trim($xmlp098) != '') {
                $xmlp098 .= '</ca_98>' . "\r\n";
            }
            if (trim($xmlp099) != '') {
                $xmlp099 .= '</ca_99>' . "\r\n";
            }
            if (trim($xmlp100) != '') {
                $xmlp100 .= '</ca_100>' . "\r\n";
            }
            if (trim($xmlp101) != '') {
                $xmlp101 .= '</ca_101>' . "\r\n";
            }
            if (trim($xmlp102) != '') {
                $xmlp102 .= '</ca_102>' . "\r\n";
            }
            if (trim($xmlp103) != '') {
                $xmlp103 .= '</ca_103>' . "\r\n";
            }
            if (trim($xmlp104) != '') {
                $xmlp104 .= '</ca_104>' . "\r\n";
            }
            if (trim($xmlp105) != '') {
                $xmlp105 .= '</ca_105>' . "\r\n";
            }
            if (trim($xmlp106) != '') {
                $xmlp106 .= '</ca_106>' . "\r\n";
            }
            if (trim($xmlp107) != '') {
                $xmlp107 .= '</ca_107>' . "\r\n";
            }
            if (trim($xmlp108) != '') {
                $xmlp108 .= '</ca_108>' . "\r\n";
            }
            if (trim($xmlp109) != '') {
                $xmlp109 .= '</ca_109>' . "\r\n";
            }
            if (trim($xmlp110) != '') {
                $xmlp110 .= '</ca_110>' . "\r\n";
            }
            $respu["xml"] .= $xmlp098;
            $respu["xml"] .= $xmlp099;
            $respu["xml"] .= $xmlp100;
            $respu["xml"] .= $xmlp101;
            $respu["xml"] .= $xmlp102;
            $respu["xml"] .= $xmlp103;
            $respu["xml"] .= $xmlp104;
            $respu["xml"] .= $xmlp105;
            $respu["xml"] .= $xmlp106;
            $respu["xml"] .= $xmlp107;
            $respu["xml"] .= $xmlp108;
            $respu["xml"] .= $xmlp109;
            $respu["xml"] .= $xmlp110;
            $respu["xml"] .= '</co_h_3>' . "\r\n";
            $respu["xml"] .= '</h_3>' . "\r\n";
        }

        // ********************************************************************************* //
        // Hoja No. 4
        // ********************************************************************************* //
        // Socios
        // Se excluyen los socios en caso de anónimas o asimiladas
        $icant = 0;
        $ipag4 = 0;
        $iInd = 0;
        $xmlh4 = '';
        $xmlp111 = '';
        $xmlp112 = '';
        $xmlp113 = '';
        $xmlp114 = '';
        $xmlp115 = '';
        $xmlp116 = '';
        $xmlp117 = '';
        $xmlp118 = '';
        $xmlp119 = '';
        $xmlp120 = '';
        $xmlp121 = '';
        $xmlp122 = '';
        $xmlp123 = '';
        if ($arrMat["organizacion"] > '01') {
            $tot = 0;
            if ($arrMat["organizacion"] == '03' ||
                    // $arrMat["organizacion"] == '04' ||
                    $arrMat["organizacion"] == '05' ||
                    $arrMat["organizacion"] == '06' ||
                    // $arrMat["organizacion"] == '07' ||
                    $arrMat["organizacion"] == '08' ||
                    $arrMat["organizacion"] == '09' ||
                    ($arrMat["organizacion"] == '10' && ($arrMat["naturaleza"] == '2' || $arrMat["naturaleza"] == '4')) ||
                    $arrMat["organizacion"] == '11') {
                foreach ($arrMat["vinculos"] as $v) {
                    if ($v["tipovinculo"] == 'SOC') {
                        $tot = $tot + $v["valorconst"] + $v["va1"] + $v["va2"] + $v["va3"] + $v["va4"];
                    }
                }
                foreach ($arrMat["vinculos"] as $v) {
                    if ($v["tipovinculo"] == 'SOC') {
                        $icant++;
                        $iInd++;
                        if ($iInd == 6) {
                            if (trim($xmlp111) != '') {
                                $xmlp111 .= '</ca_111>' . "\r\n";
                            }
                            if (trim($xmlp112) != '') {
                                $xmlp112 .= '</ca_112>' . "\r\n";
                            }
                            if (trim($xmlp113) != '') {
                                $xmlp113 .= '</ca_113>' . "\r\n";
                            }
                            if (trim($xmlp114) != '') {
                                $xmlp114 .= '</ca_114>' . "\r\n";
                            }
                            if (trim($xmlp115) != '') {
                                $xmlp115 .= '</ca_115>' . "\r\n";
                            }
                            if (trim($xmlp116) != '') {
                                $xmlp116 .= '</ca_116>' . "\r\n";
                            }
                            if (trim($xmlp117) != '') {
                                $xmlp117 .= '</ca_117>' . "\r\n";
                            }
                            if (trim($xmlp118) != '') {
                                $xmlp118 .= '</ca_118>' . "\r\n";
                            }
                            if (trim($xmlp119) != '') {
                                $xmlp119 .= '</ca_119>' . "\r\n";
                            }
                            if (trim($xmlp120) != '') {
                                $xmlp120 .= '</ca_120>' . "\r\n";
                            }
                            if (trim($xmlp121) != '') {
                                $xmlp121 .= '</ca_121>' . "\r\n";
                            }
                            if (trim($xmlp122) != '') {
                                $xmlp122 .= '</ca_122>' . "\r\n";
                            }
                            if (trim($xmlp123) != '') {
                                $xmlp123 .= '</ca_123>' . "\r\n";
                            }
                            $xmlh4 .= $xmlp111;
                            $xmlh4 .= $xmlp112;
                            $xmlh4 .= $xmlp113;
                            $xmlh4 .= $xmlp114;
                            $xmlh4 .= $xmlp115;
                            $xmlh4 .= $xmlp116;
                            $xmlh4 .= $xmlp117;
                            $xmlh4 .= $xmlp118;
                            $xmlh4 .= $xmlp119;
                            $xmlh4 .= $xmlp120;
                            $xmlh4 .= $xmlp121;
                            $xmlh4 .= $xmlp122;
                            $xmlh4 .= $xmlp123;
                            $xmlh4 .= '</co_h_4>' . "\r\n";
                            $iInd = 1;
                        }
                        if ($iInd == 1) {
                            $ipag4++;
                            if ($ipag4 == 1) {
                                $xmlh4 = '<h_4>' . "\r\n";
                            }
                            $xmlh4 .= '<co_h_4 id="' . $ipag4 . '">' . "\r\n";
                            $xmlp111 = '';
                            $xmlp112 = '';
                            $xmlp113 = '';
                            $xmlp114 = '';
                            $xmlp115 = '';
                            $xmlp116 = '';
                            $xmlp117 = '';
                            $xmlp118 = '';
                            $xmlp119 = '';
                            $xmlp120 = '';
                            $xmlp121 = '';
                            $xmlp122 = '';
                            $xmlp123 = '';
                        }

                        // ca-111 tipo de documento
                        if ($xmlp111 == '') {
                            $xmlp111 = '<ca_111>' . "\r\n";
                        }
                        $xmlp111 .= '<itE id="' . $iInd . '" v="' . \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]) . '"/>' . "\r\n";

                        //
                        $ide = '';
                        $dv = '';
                        if ($v["idtipoidentificacionotros"] == '2') {
                            $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                            $ide = $sepide["identificacion"];
                            $dv = $sepide["dv"];
                        } else {
                            $ide = $v["identificacionotros"];
                            $dv = '';
                        }

                        // ca-112 identificacion
                        if ($xmlp112 == '') {
                            $xmlp112 = '<ca_112>' . "\r\n";
                        }
                        $xmlp112 .= '<itC id="' . $iInd . '" v="' . trim($ide) . '"/>' . "\r\n";

                        // ca-113 dv
                        if (trim($dv) != '') {
                            if ($xmlp113 == '') {
                                $xmlp113 = '<ca_113>' . "\r\n";
                            }
                            $xmlp113 .= '<itE id="' . $iInd . '" v="' . $dv . '"/>' . "\r\n";
                        }

                        // ca-114 nacionalidad
                        $nac = '';
                        if ($v["idtipoidentificacionotros"] == '5' || $v["idtipoidentificacionotros"] == 'P' || $v["idtipoidentificacionotros"] == 'V') {
                            $nac = sprintf("%04s", $v["paisotros"]);
                        } else {
                            $nac = '0169';
                        }
                        if ($xmlp114 == '') {
                            $xmlp114 = '<ca_114>' . "\r\n";
                        }
                        $xmlp114 .= '<itE id="' . $iInd . '" v="' . $nac . '"/>' . "\r\n";

                        // ca-115 primer apellido
                        if ($xmlp115 == '') {
                            $xmlp115 = '<ca_115>' . "\r\n";
                        }
                        $xmlp115 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["apellido1otros"]) . '"/>' . "\r\n";

                        // ca-116 segundo apellido
                        if ($xmlp116 == '') {
                            $xmlp116 = '<ca_116>' . "\r\n";
                        }
                        $xmlp116 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["apellido2otros"]) . '"/>' . "\r\n";

                        // ca-117 primer nombre
                        if ($xmlp117 == '') {
                            $xmlp117 = '<ca_117>' . "\r\n";
                        }
                        $xmlp117 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombre1otros"]) . '"/>' . "\r\n";

                        // ca-118 segundo nombre
                        if ($xmlp118 == '') {
                            $xmlp118 = '<ca_118>' . "\r\n";
                        }
                        $xmlp118 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombre2otros"]) . '"/>' . "\r\n";

                        // ca-119 nombre en caso de personas juridicas
                        if (trim($v["nombreotros"]) != '') {
                            if ($xmlp119 == '') {
                                $xmlp119 = '<ca_119>' . "\r\n";
                            }
                            $xmlp119 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombreotros"]) . '"/>' . "\r\n";
                        }

                        // ca-120 valor del capital
                        $val = $v["valorconst"] + $v["va1"] + $v["va2"] + $v["va3"] + $v["va4"];
                        if ($xmlp120 == '') {
                            $xmlp120 = '<ca_120>' . "\r\n";
                        }
                        $xmlp120 .= '<itE id="' . $iInd . '" v="' . sprintf("%010s", $val) . '"/>' . "\r\n";

                        // ca-121 participacion
                        $val = $val / $tot * 100;
                        if ($xmlp121 == '') {
                            $xmlp121 = '<ca_121>' . "\r\n";
                        }
                        $xmlp121 .= '<itD id="' . $iInd . '" v="' . sprintf("%05s", number_format($val, 1)) . '"/>' . "\r\n";

                        // ca-122 fecha de nombramiento
                        if ($xmlp122 == '') {
                            $xmlp122 = '<ca_122>' . "\r\n";
                        }
                        $xmlp122 .= '<itF id="' . $iInd . '" v="' . \funcionesGenerales::mostrarFecha($v["fechaotros"]) . '"/>' . "\r\n";

                        // ca-123 fecha de retiro    
                        // if ($xmlp123 == '') {
                        //     $xmlp123 = '<ca_123>' . "\r\n";
                        // }
                        // $xmlp123 .= '<itF id="' . $iInd . '" v="' . '' . '"/>' . "\r\n";
                    }
                }
            }


            // Junta directiva
            // if ($arrMat["organizacion"] != '01' && $arrMat["organizacion"] != '12' && $arrMat["organizacion"] != '14') {
            if ($arrMat["organizacion"] != '01') {
                foreach ($arrMat["vinculos"] as $v) {
                    if ($v["tipovinculo"] == 'JDP' || $v["tipovinculoesadl"] == 'JDP' || $v["tipovinculoesadl"] == 'OAP') {
                        $icant++;
                        $iInd++;
                        if ($iInd == 6) {
                            if (trim($xmlp111) != '') {
                                $xmlp111 .= '</ca_111>' . "\r\n";
                            }
                            if (trim($xmlp112) != '') {
                                $xmlp112 .= '</ca_112>' . "\r\n";
                            }
                            if (trim($xmlp113) != '') {
                                $xmlp113 .= '</ca_113>' . "\r\n";
                            }
                            if (trim($xmlp114) != '') {
                                $xmlp114 .= '</ca_114>' . "\r\n";
                            }
                            if (trim($xmlp115) != '') {
                                $xmlp115 .= '</ca_115>' . "\r\n";
                            }
                            if (trim($xmlp116) != '') {
                                $xmlp116 .= '</ca_116>' . "\r\n";
                            }
                            if (trim($xmlp117) != '') {
                                $xmlp117 .= '</ca_117>' . "\r\n";
                            }
                            if (trim($xmlp118) != '') {
                                $xmlp118 .= '</ca_118>' . "\r\n";
                            }
                            if (trim($xmlp119) != '') {
                                $xmlp119 .= '</ca_119>' . "\r\n";
                            }
                            if (trim($xmlp120) != '') {
                                $xmlp120 .= '</ca_120>' . "\r\n";
                            }
                            if (trim($xmlp121) != '') {
                                $xmlp121 .= '</ca_121>' . "\r\n";
                            }
                            if (trim($xmlp122) != '') {
                                $xmlp122 .= '</ca_122>' . "\r\n";
                            }
                            if (trim($xmlp123) != '') {
                                $xmlp123 .= '</ca_123>' . "\r\n";
                            }
                            $xmlh4 .= $xmlp111;
                            $xmlh4 .= $xmlp112;
                            $xmlh4 .= $xmlp113;
                            $xmlh4 .= $xmlp114;
                            $xmlh4 .= $xmlp115;
                            $xmlh4 .= $xmlp116;
                            $xmlh4 .= $xmlp117;
                            $xmlh4 .= $xmlp118;
                            $xmlh4 .= $xmlp119;
                            $xmlh4 .= $xmlp120;
                            $xmlh4 .= $xmlp121;
                            $xmlh4 .= $xmlp122;
                            $xmlh4 .= $xmlp123;
                            $xmlh4 .= '</co_h_4>' . "\r\n";
                            $iInd = 1;
                        }
                        if ($iInd == 1) {
                            $ipag4++;
                            if ($ipag4 == 1) {
                                $xmlh4 = '<h_4>' . "\r\n";
                            }
                            $xmlh4 .= '<co_h_4 id="' . $ipag4 . '">' . "\r\n";
                            $xmlp111 = '';
                            $xmlp112 = '';
                            $xmlp113 = '';
                            $xmlp114 = '';
                            $xmlp115 = '';
                            $xmlp116 = '';
                            $xmlp117 = '';
                            $xmlp118 = '';
                            $xmlp119 = '';
                            $xmlp120 = '';
                            $xmlp121 = '';
                            $xmlp122 = '';
                            $xmlp123 = '';
                        }

                        // ca-111 tipo de documento
                        if ($xmlp111 == '') {
                            $xmlp111 = '<ca_111>' . "\r\n";
                        }
                        $xmlp111 .= '<itE id="' . $iInd . '" v="' . \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]) . '"/>' . "\r\n";

                        // ca-112 identificación
                        $ide = '';
                        $dv = '';
                        if ($v["idtipoidentificacionotros"] == '2') {
                            $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                            $ide = $sepide["identificacion"];
                            $dv = $sepide["dv"];
                        } else {
                            $ide = $v["identificacionotros"];
                            $dv = '';
                        }


                        if ($xmlp112 == '') {
                            $xmlp112 = '<ca_112>' . "\r\n";
                        }
                        $xmlp112 .= '<itC id="' . $iInd . '" v="' . trim($ide) . '"/>' . "\r\n";

                        // ca-113 dv
                        if ($dv != '') {
                            if ($xmlp113 == '') {
                                $xmlp113 = '<ca_113>' . "\r\n";
                            }
                            $xmlp113 .= '<itE id="' . $iInd . '" v="' . $dv . '"/>' . "\r\n";
                        }

                        // ca-114 nacionalidad
                        $nac = '';
                        if ($v["idtipoidentificacionotros"] == '5' || $v["idtipoidentificacionotros"] == 'P' || $v["idtipoidentificacionotros"] == 'V') {
                            // $nac = substr(sprintf("%04s", $v["paisotros"]), 1);
                            $nac = sprintf("%04s", $v["paisotros"]);
                        } else {
                            $nac = '0169';
                        }
                        if ($xmlp114 == '') {
                            $xmlp114 = '<ca_114>' . "\r\n";
                        }
                        $xmlp114 .= '<itE id="' . $iInd . '" v="' . $nac . '"/>' . "\r\n";

                        // ca-115 primer apellido
                        if ($xmlp115 == '') {
                            $xmlp115 = '<ca_115>' . "\r\n";
                        }
                        $xmlp115 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["apellido1otros"]) . '"/>' . "\r\n";

                        // ca-116 segundo apellido
                        if ($xmlp116 == '') {
                            $xmlp116 = '<ca_116>' . "\r\n";
                        }
                        $xmlp116 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["apellido2otros"]) . '"/>' . "\r\n";

                        // ca-117 primer nombre
                        if ($xmlp117 == '') {
                            $xmlp117 = '<ca_117>' . "\r\n";
                        }
                        $xmlp117 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombre1otros"]) . '"/>' . "\r\n";

                        // ca-118 segundo nombre
                        if ($xmlp118 == '') {
                            $xmlp118 = '<ca_118>' . "\r\n";
                        }
                        $xmlp118 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombre2otros"]) . '"/>' . "\r\n";

                        // ca-119 Nombre empresa
                        if (trim($v["nombreemp"]) != '') {
                            if ($xmlp119 == '') {
                                $xmlp119 = '<ca_119>' . "\r\n";
                            }
                            $xmlp119 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($v["nombreemp"]) . '"/>' . "\r\n";
                        }

                        // ca-120 valor del capital 
                        // if ($xmlp120 == '') {
                        //     $xmlp120 = '<ca_120>' . "\r\n";
                        // }
                        // $xmlp120 .= '<itE id="' . $iInd . '" v=""/>' . "\r\n";
                        // ca-121 participacion 
                        // if ($xmlp121 == '') {
                        //     $xmlp121 = '<ca_121>' . "\r\n";
                        // }
                        // $xmlp121 .= '<itD id="' . $iInd . '" v=""/>' . "\r\n";
                        // ca-122 fecha de nombramiento
                        if ($xmlp122 == '') {
                            $xmlp122 = '<ca_122>' . "\r\n";
                        }
                        $xmlp122 .= '<itF id="' . $iInd . '" v="' . \funcionesGenerales::mostrarFecha($v["fechaotros"]) . '"/>' . "\r\n";

                        // ca-123 fecha de retiro
                        // if ($xmlp123 == '') {
                        //     $xmlp123 = '<ca_123>' . "\r\n";
                        // }
                        // $xmlp123 .= '<itF id="' . $iInd . '" v=""/>' . "\r\n";
                    }
                }
            }

            if (trim($xmlp111) != '') {
                $xmlp111 .= '</ca_111>' . "\r\n";
            }
            if (trim($xmlp112) != '') {
                $xmlp112 .= '</ca_112>' . "\r\n";
            }
            if (trim($xmlp113) != '') {
                $xmlp113 .= '</ca_113>' . "\r\n";
            }
            if (trim($xmlp114) != '') {
                $xmlp114 .= '</ca_114>' . "\r\n";
            }
            if (trim($xmlp115) != '') {
                $xmlp115 .= '</ca_115>' . "\r\n";
            }
            if (trim($xmlp116) != '') {
                $xmlp116 .= '</ca_116>' . "\r\n";
            }
            if (trim($xmlp117) != '') {
                $xmlp117 .= '</ca_117>' . "\r\n";
            }
            if (trim($xmlp118) != '') {
                $xmlp118 .= '</ca_118>' . "\r\n";
            }
            if (trim($xmlp119) != '') {
                $xmlp119 .= '</ca_119>' . "\r\n";
            }
            if (trim($xmlp120) != '') {
                $xmlp120 .= '</ca_120>' . "\r\n";
            }
            if (trim($xmlp121) != '') {
                $xmlp121 .= '</ca_121>' . "\r\n";
            }
            if (trim($xmlp122) != '') {
                $xmlp122 .= '</ca_122>' . "\r\n";
            }
            if (trim($xmlp123) != '') {
                $xmlp123 .= '</ca_123>' . "\r\n";
            }

            if ($icant > 0) {
                $xmlh4 .= $xmlp111;
                $xmlh4 .= $xmlp112;
                $xmlh4 .= $xmlp113;
                $xmlh4 .= $xmlp114;
                $xmlh4 .= $xmlp115;
                $xmlh4 .= $xmlp116;
                $xmlh4 .= $xmlp117;
                $xmlh4 .= $xmlp118;
                $xmlh4 .= $xmlp119;
                $xmlh4 .= $xmlp120;
                $xmlh4 .= $xmlp121;
                $xmlh4 .= $xmlp122;
                $xmlh4 .= $xmlp123;
                $xmlh4 .= '</co_h_4>' . "\r\n";
                $xmlh4 .= '</h_4>' . "\r\n";
                $respu["xml"] .= $xmlh4;
            }
        }

        // ********************************************************************************* //
        // Hoja No. 5 ca-124 al ca-159
        // Revisores fiscales
        // ********************************************************************************* //
        if ($arrMat["organizacion"] > '01') {
            $th5 = '';
            $th5 .= '<h_5>' . "\r\n";
            $th5 .= '<co_h_5 id="1">' . "\r\n";
            $icant = 0;
            $ip = 0;
            $is = 0;
            $ic = 0;
            foreach ($arrMat["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'RFP' || $v["tipovinculo"] == 'RFDP' || $v["tipovinculoesadl"] == 'RFP' || $v["tipovinculoesadl"] == 'RFDP') {
                    $ip++;
                    if ($ip == 1) {
                        $icant++;
                        // ca-124  tipo de identifcación revisor fiscal principal
                        $th5 .= '<ca_124 v="' . \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]) . '"/>' . "\r\n";

                        // ca-125  identifcación revisor fiscal principal
                        $ide = '';
                        $dv = '';
                        if ($v["idtipoidentificacionotros"] == '2') {
                            $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                            $ide = ltrim($sepide["identificacion"], "0");
                            $dv = $sepide["dv"];
                        } else {
                            $ide = $v["identificacionotros"];
                            $dv = \funcionesGenerales::calcularDv($v["identificacionotros"]);
                        }
                        $th5 .= '<ca_125 v="' . $ide . '"/>' . "\r\n";

                        // ca-126  dv revisor fiscal principal
                        $th5 .= '<ca_126 v="' . $dv . '"/>' . "\r\n";

                        // ca-127 tajeta profesional revisor fiscal principal
                        $th5 .= '<ca_127 v="' . $v["numtarprofotros"] . '"/>' . "\r\n";

                        // ca-128 pimer apellido revisor fiscal principal
                        $th5 .= '<ca_128 v="' . \funcionesGenerales::utf8_decode($v["apellido1otros"]) . '"/>' . "\r\n";

                        // ca-129 segundo apellido revisor fiscal principal
                        $th5 .= '<ca_129 v="' . \funcionesGenerales::utf8_decode($v["apellido2otros"]) . '"/>' . "\r\n";

                        // ca-130 pimer nombre revisor fiscal principal
                        $th5 .= '<ca_130 v="' . \funcionesGenerales::utf8_decode($v["nombre1otros"]) . '"/>' . "\r\n";

                        // ca-131 segundo nombre revisor fiscal principal
                        $th5 .= '<ca_131 v="' . \funcionesGenerales::utf8_decode($v["nombre2otros"]) . '"/>' . "\r\n";

                        // ca-132 identificación empresa representa revisor fiscal principal
                        $ide = '';
                        $dv = '';
                        if ($v["numidemp"] != '') {
                            $sepide = \funcionesGenerales::separarDv($v["numidemp"]);
                            $ide = ltrim($sepide["identificacion"], "0");
                            $dv = $sepide["dv"];
                        }
                        if (trim($ide) != '') {
                            $th5 .= '<ca_132 v="' . $ide . '"/>' . "\r\n";

                            // ca-133 dv empresa representa revisor fiscal principal
                            $th5 .= '<ca_133 v="' . $dv . '"/>' . "\r\n";

                            // ca-134 razonsocial revisor fiscal principal
                            $th5 .= '<ca_134 v="' . \funcionesDian::reemplazarCaracteres($v["nombreemp"]) . '"/>' . "\r\n";
                        }

                        // ca-135 fecha nombramiento revisor fiscal principal
                        $th5 .= '<ca_135 v="' . \funcionesGenerales::mostrarFecha($v["fechaotros"]) . '"/>' . "\r\n";
                    }
                }

                if ($v["tipovinculo"] == 'RFS' ||
                        $v["tipovinculo"] == 'RFS1' ||
                        $v["tipovinculo"] == 'RFDS1' ||
                        $v["tipovinculoesadl"] == 'RFS' ||
                        $v["tipovinculoesadl"] == 'RFS1' ||
                        $v["tipovinculoesadl"] == 'RFDS1'
                ) {
                    $is++;
                    if ($is == 1) {
                        $icant++;
                        // ca-136  tipo de identifcación revisor fiscal suplente
                        $th5 .= '<ca_136 v="' . \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]) . '"/>' . "\r\n";

                        // ca-137  identifcación revisor fiscal suplente
                        $ide = '';
                        $dv = '';
                        if ($v["idtipoidentificacionotros"] == '2') {
                            $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                            $ide = ltrim($sepide["identificacion"], "0");
                            $dv = $sepide["dv"];
                        } else {
                            $ide = $v["identificacionotros"];
                            $dv = '';
                        }
                        $th5 .= '<ca_137 v="' . $ide . '"/>' . "\r\n";

                        // ca-138  dv revisor fiscal suplente
                        if (trim($dv) != '') {
                            $th5 .= '<ca_138 v="' . $dv . '"/>' . "\r\n";
                        }

                        // ca-139 tajeta profesional revisor fiscal suplente
                        $th5 .= '<ca_139 v="' . $v["numtarprofotros"] . '"/>' . "\r\n";

                        // ca-140 pimer apellido revisor fiscal suplente
                        $th5 .= '<ca_140 v="' . \funcionesDian::reemplazarCaracteres($v["apellido1otros"]) . '"/>' . "\r\n";

                        // ca-141 segundo apellido revisor fiscal supolente
                        $th5 .= '<ca_141 v="' . \funcionesDian::reemplazarCaracteres($v["apellido2otros"]) . '"/>' . "\r\n";

                        // ca-142 pimer nombre revisor fiscal suplente
                        $th5 .= '<ca_142 v="' . \funcionesDian::reemplazarCaracteres($v["nombre1otros"]) . '"/>' . "\r\n";

                        // ca-143 segundo nombre revisor fiscal suplente
                        $th5 .= '<ca_143 v="' . \funcionesDian::reemplazarCaracteres($v["nombre2otros"]) . '"/>' . "\r\n";

                        // ca-144 identificación empresa representa revisor fiscal suplente
                        $ide = '';
                        $dv = '';
                        if ($v["numidemp"] != '') {
                            $sepide = \funcionesGenerales::separarDv($v["numidemp"]);
                            $ide = ltrim($sepide["identificacion"], "0");
                            $dv = $sepide["dv"];
                            $th5 .= '<ca_144 v="' . $ide . '"/>' . "\r\n";

                            // ca-145 dv empresa representa revisor fiscal suplente
                            $th5 .= '<ca_145 v="' . $dv . '"/>' . "\r\n";
                        }

                        // ca-146 razonsocial revisor fiscal suplente
                        if (trim($v["nombreemp"]) != '') {
                            $th5 .= '<ca_146 v="' . \funcionesDian::reemplazarCaracteres($v["nombreemp"]) . '"/>' . "\r\n";
                        }

                        // ca-147 fecha nombramiento revisor fiscal suplente
                        $th5 .= '<ca_147 v="' . \funcionesGenerales::mostrarFecha($v["fechaotros"]) . '"/>' . "\r\n";
                    }
                }

                if ($v["tipovinculo"] == 'CON' || $v["tipovinculoesadl"] == 'CON') {
                    $ic++;
                    if ($ic == 1) {
                        $icant++;
                        // ca-148  tipo de identifcación contador
                        $th5 .= '<ca_148 v="' . \funcionesDian::asignarTipoIde($v["idtipoidentificacionotros"]) . '"/>' . "\r\n";

                        // ca-149  identifcación contador
                        $ide = '';
                        $dv = '';
                        if ($v["idtipoidentificacionotros"] == '2') {
                            $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                            $ide = ltrim($sepide["identificacion"], "0");
                            $dv = $sepide["dv"];
                        } else {
                            $ide = $v["identificacionotros"];
                            $dv = '';
                        }
                        $th5 .= '<ca_149 v="' . $ide . '"/>' . "\r\n";

                        // ca-150  dv revisor conbtador
                        $th5 .= '<ca_150 v="' . $dv . '"/>' . "\r\n";

                        // ca-151 tajeta profesional contador
                        $th5 .= '<ca_151 v="' . $v["numtarprofotros"] . '"/>' . "\r\n";

                        // ca-152 pimer apellido contador
                        $th5 .= '<ca_152 v="' . \funcionesDian::reemplazarCaracteres($v["apellido1otros"]) . '"/>' . "\r\n";

                        // ca-153 segundo apellido contador
                        $th5 .= '<ca_153 v="' . \funcionesDian::reemplazarCaracteres($v["apellido2otros"]) . '"/>' . "\r\n";

                        // ca-154 pimer nombre contador
                        $th5 .= '<ca_154 v="' . \funcionesDian::reemplazarCaracteres($v["nombre1otros"]) . '"/>' . "\r\n";

                        // ca-155 segundo nombre contador
                        $th5 .= '<ca_155 v="' . \funcionesDian::reemplazarCaracteres($v["nombre2otros"]) . '"/>' . "\r\n";

                        // ca-156 identificación empresa representa contador
                        $ide = '';
                        $dv = '';
                        if ($v["numidemp"] != '') {
                            $sepide = \funcionesGenerales::separarDv($v["numidemp"]);
                            $ide = ltrim($sepide["identificacion"], "0");
                            $dv = $sepide["dv"];
                        }
                        $th5 .= '<ca_156 v="' . $ide . '"/>' . "\r\n";

                        // ca-157 dv empresa representa contador
                        $th5 .= '<ca_157 v="' . $dv . '"/>' . "\r\n";

                        // ca-158 razonsocial contador
                        $th5 .= '<ca_158 v="' . \funcionesDian::reemplazarCaracteres($v["nombreemp"]) . '"/>' . "\r\n";

                        // ca-159 fecha nombramiento contador
                        $th5 .= '<ca_159 v="' . \funcionesGenerales::mostrarFecha($v["fechaotros"]) . '"/>' . "\r\n";
                    }
                }
            }
            $th5 .= '</co_h_5>' . "\r\n";
            $th5 .= '</h_5>' . "\r\n";
            if ($icant > 0) {
                $respu["xml"] .= $th5;
            }
        }

        // ********************************************************************************* //
        // Hoja No. 6 Establecimientos
        // ********************************************************************************* //
        if (!empty($arrMat["establecimientos"]) || !empty($arrMat["sucursalesagencias"])) {
            $iInd = 0;
            $ipag6 = 0;
            $xmlh6 = '';
            $xmlp160 = '';
            $xmlp161 = '';
            $xmlp162 = '';
            $xmlp163 = '';
            $xmlp164 = '';
            $xmlp165 = '';
            $xmlp166 = '';
            $xmlp167 = '';
            $xmlp168 = '';
            $xmlp169 = '';
            // $respu["xml"] .= '<h_6>' . "\r\n";
            // $respu["xml"] .= '<co_h_6 id="1">' . "\r\n";
            foreach ($arrMat["establecimientos"] as $e) {
                $iInd++;
                if ($iInd == 4) {
                    if (trim($xmlp160) != '') {
                        $xmlp160 .= '</ca_160>' . "\r\n";
                    }
                    if (trim($xmlp161) != '') {
                        $xmlp161 .= '</ca_161>' . "\r\n";
                    }
                    if (trim($xmlp162) != '') {
                        $xmlp162 .= '</ca_162>' . "\r\n";
                    }
                    if (trim($xmlp163) != '') {
                        $xmlp163 .= '</ca_163>' . "\r\n";
                    }
                    if (trim($xmlp164) != '') {
                        $xmlp164 .= '</ca_164>' . "\r\n";
                    }
                    if (trim($xmlp165) != '') {
                        $xmlp165 .= '</ca_165>' . "\r\n";
                    }
                    if (trim($xmlp166) != '') {
                        $xmlp166 .= '</ca_166>' . "\r\n";
                    }
                    if (trim($xmlp167) != '') {
                        $xmlp167 .= '</ca_167>' . "\r\n";
                    }
                    if (trim($xmlp168) != '') {
                        $xmlp168 .= '</ca_168>' . "\r\n";
                    }
                    if (trim($xmlp169) != '') {
                        $xmlp169 .= '</ca_169>' . "\r\n";
                    }
                    $xmlh6 .= $xmlp160;
                    $xmlh6 .= $xmlp161;
                    $xmlh6 .= $xmlp162;
                    $xmlh6 .= $xmlp163;
                    $xmlh6 .= $xmlp164;
                    $xmlh6 .= $xmlp165;
                    $xmlh6 .= $xmlp166;
                    $xmlh6 .= $xmlp167;
                    $xmlh6 .= $xmlp168;
                    $xmlh6 .= $xmlp169;
                    $xmlh6 .= '</co_h_6>' . "\r\n";
                    $iInd = 1;
                }
                if ($iInd == 1) {
                    $ipag6++;
                    if ($ipag6 == 1) {
                        $xmlh6 = '<h_6>' . "\r\n";
                    }
                    $xmlh6 .= '<co_h_6 id="' . $ipag6 . '">' . "\r\n";
                    $xmlp160 = '';
                    $xmlp161 = '';
                    $xmlp162 = '';
                    $xmlp163 = '';
                    $xmlp164 = '';
                    $xmlp165 = '';
                    $xmlp166 = '';
                    $xmlp167 = '';
                    $xmlp168 = '';
                    $xmlp169 = '';
                }

                // Tipo 160.XX      
                if ($xmlp160 == '') {
                    $xmlp160 .= '<ca_160>' . "\r\n";
                }
                $xmlp160 .= '<itE id="' . $iInd . '" v="02"/>' . "\r\n";

                // Ciiu 161.XX      
                if ($xmlp161 == '') {
                    $xmlp161 .= '<ca_161>' . "\r\n";
                }
                $xmlp161 .= '<itE id="' . $iInd . '" v="' . substr($e["ciiu1"], 1) . '"/>' . "\r\n";

                // nombre 162.XX      
                if ($xmlp162 == '') {
                    $xmlp162 .= '<ca_162>' . "\r\n";
                }
                $xmlp162 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($e["nombreestablecimiento"]) . '"/>' . "\r\n";

                // dpto 163.XX      
                if ($xmlp163 == '') {
                    $xmlp163 .= '<ca_163>' . "\r\n";
                }
                $xmlp163 .= '<itE id="' . $iInd . '" v="' . substr($e["muncom"], 0, 2) . '"/>' . "\r\n";

                // dpto 164.XX      
                if ($xmlp164 == '') {
                    $xmlp164 .= '<ca_164>' . "\r\n";
                }
                $xmlp164 .= '<itE id="' . $iInd . '" v="' . substr($e["muncom"], 2) . '"/>' . "\r\n";

                // dircom 165.XX     
                if ($xmlp165 == '') {
                    $xmlp165 .= '<ca_165>' . "\r\n";
                }
                $xmlp165 .= '<itC id="' . $iInd . '" v="' . $e["dircom"] . '"/>' . "\r\n";

                // matricula 166.XX      
                if ($xmlp166 == '') {
                    $xmlp166 .= '<ca_166>' . "\r\n";
                }
                $xmlp166 .= '<itC id="' . $iInd . '" v="' . sprintf("%010s", $e["matriculaestablecimiento"]) . '"/>' . "\r\n";

                // fechamatricula 167.XX      
                if ($xmlp167 == '') {
                    $xmlp167 .= '<ca_167>' . "\r\n";
                }
                $xmlp167 .= '<itF id="' . $iInd . '" v="' . \funcionesGenerales::mostrarFecha($e["fechamatricula"]) . '"/>' . "\r\n";

                // telcom1 168.XX 
                if ($xmlp168 == '') {
                    $xmlp168 .= '<ca_168>' . "\r\n";
                }
                $xmlp168 .= '<itE id="' . $iInd . '" v="' . $e["telcom1"] . '"/>' . "\r\n";

                // 169.XX      
                if ($xmlp169 == '') {
                    $xmlp169 .= '<ca_169>' . "\r\n";
                }
                $xmlp169 .= '<itF id="' . $iInd . '" v=""/>' . "\r\n";
            }

            foreach ($arrMat["sucursalesagencias"] as $e) {
                $iInd++;
                if ($iInd == 4) {
                    if (trim($xmlp160) != '') {
                        $xmlp160 .= '</ca_160>' . "\r\n";
                    }
                    if (trim($xmlp161) != '') {
                        $xmlp161 .= '</ca_161>' . "\r\n";
                    }
                    if (trim($xmlp162) != '') {
                        $xmlp162 .= '</ca_162>' . "\r\n";
                    }
                    if (trim($xmlp163) != '') {
                        $xmlp163 .= '</ca_163>' . "\r\n";
                    }
                    if (trim($xmlp164) != '') {
                        $xmlp164 .= '</ca_164>' . "\r\n";
                    }
                    if (trim($xmlp165) != '') {
                        $xmlp165 .= '</ca_165>' . "\r\n";
                    }
                    if (trim($xmlp166) != '') {
                        $xmlp166 .= '</ca_166>' . "\r\n";
                    }
                    if (trim($xmlp167) != '') {
                        $xmlp167 .= '</ca_167>' . "\r\n";
                    }
                    if (trim($xmlp168) != '') {
                        $xmlp168 .= '</ca_168>' . "\r\n";
                    }
                    if (trim($xmlp169) != '') {
                        $xmlp169 .= '</ca_169>' . "\r\n";
                    }
                    $xmlh6 .= $xmlp160;
                    $xmlh6 .= $xmlp161;
                    $xmlh6 .= $xmlp162;
                    $xmlh6 .= $xmlp163;
                    $xmlh6 .= $xmlp164;
                    $xmlh6 .= $xmlp165;
                    $xmlh6 .= $xmlp166;
                    $xmlh6 .= $xmlp167;
                    $xmlh6 .= $xmlp168;
                    $xmlh6 .= $xmlp169;
                    $xmlh6 .= '</co_h_6>' . "\r\n";
                    $iInd = 1;
                }
                if ($iInd == 1) {
                    $ipag6++;
                    if ($ipag6 == 1) {
                        $xmlh6 = '<h_6>' . "\r\n";
                    }
                    $xmlh6 .= '<co_h_6 id="' . $ipag6 . '">' . "\r\n";
                    $xmlp160 = '';
                    $xmlp161 = '';
                    $xmlp162 = '';
                    $xmlp163 = '';
                    $xmlp164 = '';
                    $xmlp165 = '';
                    $xmlp166 = '';
                    $xmlp167 = '';
                    $xmlp168 = '';
                    $xmlp169 = '';
                }

                // Tipo 160.XX      
                $tp = '10';
                if ($e["categoriasucage"] == '3') {
                    $tp = '01';
                }
                if ($xmlp160 == '') {
                    $xmlp160 .= '<ca_160>' . "\r\n";
                }
                $xmlp160 .= '<itE id="' . $iInd . '" v="' . $tp . '"/>' . "\r\n";

                // Ciiu 161.XX      
                if ($xmlp161 == '') {
                    $xmlp161 .= '<ca_161>' . "\r\n";
                }
                $xmlp161 .= '<itE id="' . $iInd . '" v="' . substr($e["ciiu1"], 1) . '"/>' . "\r\n";

                // nombre 162.XX  
                if ($xmlp162 == '') {
                    $xmlp162 .= '<ca_162>' . "\r\n";
                }
                $xmlp162 .= '<itC id="' . $iInd . '" v="' . \funcionesDian::reemplazarCaracteres($e["nombresucage"]) . '"/>' . "\r\n";

                // dpto 163.XX       
                if ($xmlp163 == '') {
                    $xmlp163 .= '<ca_163>' . "\r\n";
                }
                $xmlp163 .= '<itE id="' . $iInd . '" v="' . substr($e["muncom"], 0, 2) . '"/>' . "\r\n";

                // dpto 164.XX      
                if ($xmlp164 == '') {
                    $xmlp164 .= '<ca_164>' . "\r\n";
                }
                $xmlp164 .= '<itE id="' . $iInd . '" v="' . substr($e["muncom"], 2) . '"/>' . "\r\n";

                // dircom 165.XX     
                if ($xmlp165 == '') {
                    $xmlp165 .= '<ca_165>' . "\r\n";
                }
                $xmlp165 .= '<itC id="' . $iInd . '" v="' . e["dircom"] . '"/>' . "\r\n";

                // matricula 166.XX    
                if ($xmlp166 == '') {
                    $xmlp166 .= '<ca_166>' . "\r\n";
                }
                $xmlp166 .= '<itC id="' . $iInd . '" v="' . sprintf("%010s", $e["matriculasucage"]) . '"/>' . "\r\n";

                // fechamatricula 167.XX      
                if ($xmlp167 == '') {
                    $xmlp167 .= '<ca_167>' . "\r\n";
                }
                $xmlp167 .= '<itF id="' . $iInd . '" v="' . \funcionesGenerales::mostrarFecha($e["fechamatricula"]) . '"/>' . "\r\n";

                // telcom1 168.XX      
                if ($xmlp168 == '') {
                    $xmlp168 .= '<ca_168>' . "\r\n";
                }
                $xmlp168 .= '<itE id="' . $iInd . '" v="' . $e["telcom1"] . '"/>' . "\r\n";

                //  169.XX    
                if ($xmlp169 == '') {
                    $xmlp169 .= '<ca_169>' . "\r\n";
                }
                $xmlp169 .= '<itF id="' . $iInd . '" v=""/>' . "\r\n";
            }

            if (trim($xmlp160) != '') {
                $xmlp160 .= '</ca_160>' . "\r\n";
            }
            if (trim($xmlp161) != '') {
                $xmlp161 .= '</ca_161>' . "\r\n";
            }
            if (trim($xmlp162) != '') {
                $xmlp162 .= '</ca_162>' . "\r\n";
            }
            if (trim($xmlp163) != '') {
                $xmlp163 .= '</ca_163>' . "\r\n";
            }
            if (trim($xmlp164) != '') {
                $xmlp164 .= '</ca_164>' . "\r\n";
            }
            if (trim($xmlp165) != '') {
                $xmlp165 .= '</ca_165>' . "\r\n";
            }
            if (trim($xmlp166) != '') {
                $xmlp166 .= '</ca_166>' . "\r\n";
            }
            if (trim($xmlp167) != '') {
                $xmlp167 .= '</ca_167>' . "\r\n";
            }
            if (trim($xmlp168) != '') {
                $xmlp168 .= '</ca_168>' . "\r\n";
            }
            if (trim($xmlp169) != '') {
                $xmlp169 .= '</ca_169>' . "\r\n";
            }
            $xmlh6 .= $xmlp160;
            $xmlh6 .= $xmlp161;
            $xmlh6 .= $xmlp162;
            $xmlh6 .= $xmlp163;
            $xmlh6 .= $xmlp164;
            $xmlh6 .= $xmlp165;
            $xmlh6 .= $xmlp166;
            $xmlh6 .= $xmlp167;
            $xmlh6 .= $xmlp168;
            $xmlh6 .= $xmlp169;
            $xmlh6 .= '</co_h_6>' . "\r\n";
            $xmlh6 .= '</h_6>' . "\r\n";
            $respu["xml"] .= $xmlh6 . "\r\n";
        }

        // ********************************************************************************* //
        // Hoja No. 7
        // ********************************************************************************* //
        $essimple = '';
        foreach ($arrMat["codrespotri"] as $tx) {
            if ($tx == '47') {
                $essimple = 'si';
            }
        }

        // ca-89 Estado de la empresa
        /*
          $i89 = 0;
          $i90 = 0;
          $t89 = '';
          $t90 = '';
          if ($essimple == 'si') {
          $i89++;
          if ($i89 == 1) {
          $t89 .= '<ca_89>' . "\r\n";
          }
          $t89 .= '<itE id="' . $i89 . '" v="100"/>' . "\r\n";
          $i90++;
          if ($i90 == 1) {
          $t90 .= '<ca_90>' . "\r\n";
          }
          $t90 .= '<itF id="' . $i90 . '" v="' . \funcionesGenerales::mostrarFecha($arrMat["fechamatricula"]) . '"/>' . "\r\n";
          }
          if ($i89 != '') {
          $t89 .= '</ca_89>' . "\r\n";
          }
          if ($i90 != '') {
          $t90 .= '</ca_90>' . "\r\n";
          }

          if ($i89 > 0 || $i90 > 0) {
          $respu["xml"] .= '<h_7>' . "\r\n";
          $respu["xml"] .= '<co_h_7 id="1">' . "\r\n";
          $respu["xml"] .= $t89;
          $respu["xml"] .= $t90;
          $respu["xml"] .= '</co_h_7>' . "\r\n";
          $respu["xml"] .= '</h_7>' . "\r\n";
          }
         */

        //
        $respu["xml"] .= '</d_001_4_cc>' . "\r\n";

        //
        if (!empty($respu["errores"])) {
            $respu["codigoError"] = '9999';
            $respu["msgError"] = 'Se detectaron los siguientes errores:';
        }
        return $respu;
    }

    public static function asignarTipoIde($tie) {
        $ti = 'XX';
        switch ($tie) {
            case "1" : $ti = '13';
                break;
            case "2" : $ti = '31';
                break;
            case "3" : $ti = '22';
                break;
            case "4" : $ti = '12';
                break;
            case "5" : $ti = '41';
                break;
            case "E" : $ti = '42';
                break;
            case "N" : $ti = '32';
                break;
            case "R" : $ti = '11';
                break;
            case "V" : $ti = '47';
                break;
            case "P" :
                // $ti = '48' 2022-05-27 por recomenación de DIAN
                $ti = '47';
                break;
        }
        return $ti;
    }
        
    public static function reemplazarCaracteres($entrada) {
        // $salida = \funcionesGenerales::utf8_decode($entrada);
        // $salida = str_replace (' & ', ' &amp; ',$entrada);
        $salida = str_replace ('&', '&amp;',$entrada);
        $salida = str_replace ('<', '&lt;',$salida);
        $salida = str_replace ('>', '&gt;',$salida);
        $salida = str_replace ('"', '&quot;',$salida);
        $salida = str_replace ("'", '&apos;',$salida);
        $salida = str_replace ("Á", '&Aacute;',$salida);
        $salida = str_replace ("É", '&Eacute;',$salida);
        $salida = str_replace ("Í", '&Iacute;',$salida);
        $salida = str_replace ("Ó", '&Oacute;',$salida);
        $salida = str_replace ("Ú", '&Uacute;',$salida);
        $salida = str_replace ("Ñ", '&Ntilde;',$salida);
        return $salida;
    }

}
