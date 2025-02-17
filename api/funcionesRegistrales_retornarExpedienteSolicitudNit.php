<?php

class funcionesRegistrales_retornarExpedienteSolicitudNit {

    public static function retornarExpedienteSolicitudNit($dbx, $arrTem) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

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
        if (ltrim($arrTem["nit"], "0") != '') {
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
            switch ($arrTem["tipoidentificacion"]) {
                case "1" : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '13';
                    break; // Cedula
                case "2" : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '31';
                    break; // Nit
                case "3" : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '22';
                    break; // CE
                case "4" : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '12';
                    break; // TI
                case "5" : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '41';
                    break; // Passport
                case "E" : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '42';
                    break; // Dcto extranjero
                case "P" : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '41';
                    break; // Passport
                case "R" : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '11';
                    break; // RegCivil
                default : $iData++;
                    $retorno["datos"][$iData] = $raiz . '025' . '00' . '32';
                    break; // Otros
            }
        }

// Número del documento de identidad
        $iData++;
        $retorno["datos"][$iData] = $raiz . '026' . '00' . $arrTem["identificacion"];

// Fecha de expedición
        $iData++;
        $retorno["datos"][$iData] = $raiz . '027' . '00' . $arrTem["fecexpdoc"];

// Pais de expedición
//WSI - 20170731 - Ajuste para que obtenga el código del pais (numérico) requerido en solicitud de NIT
        if (is_numeric($arrTem["paisexpdoc"])) {
            if (ltrim($arrTem["paisexpdoc"], "0") == '') {
                if ($arrTem["tipoidentificacion"] == '1' || $arrTem["tipoidentificacion"] == '4') {
                    $arrTem["paisexpdoc"] = '0169';
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

            if ($arrTem["tipoidentificacion"] == '1' || $arrTem["tipoidentificacion"] == '4') {
                $arrTem["paisexpdoc"] = '0169'; //Colombia
                $codigoPais = '169';
            }
            $iData++;
            $retorno["datos"][$iData] = $raiz . '028' . '00' . $codigoPais;
        }

// Municipio y departamento de expedición
        if ($arrTem["tipoidentificacion"] == '1' ||
                $arrTem["tipoidentificacion"] == '4' ||
                $arrTem["tipoidentificacion"] == 'R') {
            $iData++;
            $retorno["datos"][$iData] = $raiz . '029' . '00' . substr($arrTem["idmunidoc"], 0, 2);
            $iData++;
            $retorno["datos"][$iData] = $raiz . '030' . '00' . substr($arrTem["idmunidoc"], 2);
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
        if ($arrTem["organizacion"] != '01') {
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
                case "12" : $tsoc = ''; // Otras
                    break;
                case "14" : $tsoc = ''; // Otras
                    break;
                case "16" : $tsoc = '12';
                    break;
                default : $tsoc = '99'; // Otras
                    break;
            }
            $iData++;
            $retorno["datos"][$iData] = $raiz . '063' . '00' . $tsoc;
        }

// Sociedad de derecho publico (064)
// ojo revisar esto
// 
// MOVE SLUCRO.COD-COOPERATIVA TO WCOOPERATIVAS    
// MOVE SLUCRO.COD-OTRA        TO WNO-CLASIFICADAS 
// MOVE SLUCRO.COD-DER-PUB     TO WENT-DERECHO-PUB 
// IF WCOOPERATIVAS = ' ' AND                      
//   WNO-CLASIFICADAS = ' ' AND                  
//   WENT-DERECHO-PUB EQ ' '                     
//   IF CTR-CLASE-ESPECIFICA = '22'                
//    MOVE '99' TO WFONDOS                        
//   END-IF                                          
// END-IF                                          

        $tdp = '';
        if ($nat != '02') {
            if ($arrTem["organizacion"] == '12' || $arrTem["organizacion"] == '14') {
                if (trim($arrTem["ctrderpub"]) != '') {
                    $tdp = $arrTem["ctrderpub"];
                }
            }
        }
        $iData++;
        $retorno["datos"][$iData] = $raiz . '064' . '00' . $tdp;

// Fondos (065)
        $tf = '';
        if ($arrTem["ctrclaseespeesadl"] == '22') {
            $tf = '99';
        }
        $iData++;
        $retorno["datos"][$iData] = $raiz . '065' . '00' . $tf;

// Código cooperarivas (066)
        $tc = '';
        if ($arrTem["ctrcodcoop"] != '') {
            $tc = $arrTem["ctrcodcoop"];
        }
        $iData++;
        $retorno["datos"][$iData] = $raiz . '066' . '00' . $tc;

// Sociedades y organismos extranjeros (067)
        $te = '';
        if ($_SESSION["organizacion"] == '08') {
            $te = '10';
        }
        $iData++;
        $retorno["datos"][$iData] = $raiz . '067' . '00' . $te;

// Sin personería jurídica (068)
        $iData++;
        $retorno["datos"][$iData] = $raiz . '068' . '00' . '';

// Otras no clasificadas (069)
        $tnc = '';
        if ($arrTem["organizacion"] == '09') {
            $tnc = '09';
        } else {
            if ($arrTem["organizacion"] == '12' || $arrTem["organizacion"] == '14') {
                $tnc = $arrTem["ctrcodotras"];
            }
        }
        $iData++;
        $retorno["datos"][$iData] = $raiz . '069' . '00' . $tnc;

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
            }
        }
        if ($nic != '') {
            if ($tide == '2') {
                $sepide = separarDv($nic);
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
            if ($v["tipovinculo"] == 'RLP' || $v["tipovinculo"] == 'RLS1') {
                $iInd++;

// Tipo de representante legal 098.XX
                if ($v["tipovinculo"] == 'RLP') {
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '098' . sprintf("%02s", $iInd) . '18';
                }
                if ($v["tipovinculo"] == 'RLS1') {
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '098' . sprintf("%02s", $iInd) . '19';
                }

// fecha del nombramiento 099.XX                
                $iData++;
                $retorno["datos"][$iData] = $raiz . '099' . sprintf("%02s", $iInd) . $v["fechaotros"];

// ftipo de documento 100.XX                
                $ti = '';
                switch ($v["idtipoidentificacionotros"]) {
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
                    case "N" : $ti = 'XX';
                        break;
                    case "R" : $ti = '11';
                        break;
                }
                $iData++;
                $retorno["datos"][$iData] = $raiz . '100' . sprintf("%02s", $iInd) . $ti;

                $ide = '';
                $dv = '';
                if ($v["idtipoidentificacionotros"] == '2') {
                    $sepide = separarDv($v["identificacionotros"]);
                    $ide = $sepide["identificacion"];
                    $dv = $sepide["dv"];
                } else {
                    $ide = $v["identificacionotros"];
                    $dv = '';
                }

// identificacion 101.XX      
                $iData++;
                $retorno["datos"][$iData] = $raiz . '101' . sprintf("%02s", $iInd) . sprintf("%011s", ltrim($ide, "0"));

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
                        $sepide = separarDv($v["numidemp"]);
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
// Se excluyen los socios en caso de asónimas o asimiladas
        if ($arrTem["organizacion"] == '03' ||
                $arrTem["organizacion"] == '04' ||
                $arrTem["organizacion"] == '05' ||
                $arrTem["organizacion"] == '06' ||
                $arrTem["organizacion"] == '07' ||
                $arrTem["organizacion"] == '08' ||
                $arrTem["organizacion"] == '09' ||
                ($arrTem["organizacion"] == '10' && ($arrTem["naturaleza"] == '2' || $arrTem["naturaleza"] == '4')) ||
                $arrTem["organizacion"] == '11') {
            $iInd = 0;
            $tot = 0;
            foreach ($arrTem["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'SOC') {
                    $tot = $v["valorconst"] + $v["va1"] + $v["va2"] + $v["va3"] + $v["va4"];
                }
            }
            foreach ($arrTem["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'SOC') {
                    $iInd++;

// tipo de documento 111.XX                
                    $ti = '';
                    switch ($v["idtipoidentificacionotros"]) {
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
                        case "N" : $ti = 'XX';
                            break;
                        case "R" : $ti = '11';
                            break;
                        case "V" : $ti = 'XX';
                            break;
                    }
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '111' . sprintf("%02s", $iInd) . $ti;

                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    }

// identificacion 112.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '112' . sprintf("%02s", $iInd) . sprintf("%013s", ltrim($ide, "0"));

// dv 113.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '113' . sprintf("%02s", $iInd) . $dv;

// nacionalidad 114.XX      
                    $nac = '';
                    if ($v["idtipoidentificacionotros"] == '5') {
                        $nac = substr(sprintf("%04s", $v["paisotros"]), 1);
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
                    $val = $v["valorconst"] + $v["va1"] + $v["va2"] + $v["va3"] + $v["va4"];
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '120' . sprintf("%02s", $iInd) . sprintf("%010s", $val);

// participacion 121.XX      
                    $val = $val / $tot;
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
            if ($v["tipovinculo"] == 'JDP') {
                $iInd++;

// tipo de documento 111.XX                
                $ti = '';
                switch ($v["idtipoidentificacionotros"]) {
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
                    case "N" : $ti = 'XX';
                        break;
                    case "R" : $ti = '11';
                        break;
                }
                $iData++;
                $retorno["datos"][$iData] = $raiz . '111' . sprintf("%02s", $iInd) . $ti;

                $ide = '';
                $dv = '';
                if ($v["idtipoidentificacionotros"] == '2') {
                    $sepide = \funcionesGenerales::separarDv($v["identificacionotros"]);
                    $ide = $sepide["identificacion"];
                    $dv = $sepide["dv"];
                }

// identificacion 112.XX      
                $iData++;
                $retorno["datos"][$iData] = $raiz . '112' . sprintf("%02s", $iInd) . sprintf("%013s", ltrim($ide, "0"));

// dv 113.XX      
                $iData++;
                $retorno["datos"][$iData] = $raiz . '113' . sprintf("%02s", $iInd) . $dv;

// nacionalidad 114.XX      
                $nac = '';
                if ($v["idtipoidentificacionotros"] == '5') {
                    $nac = substr(sprintf("%04s", $v["paisotros"]), 1);
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
                    $ti = '';
                    switch ($v["idtipoidentificacionotros"]) {
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
                        case "N" : $ti = 'XX';
                            break;
                        case "R" : $ti = '11';
                            break;
                    }
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '124' . '00' . $ti;

                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    }

// identificacion 125.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '125' . '00' . sprintf("%013s", ltrim($ide, "0"));

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
                        $sepide = separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    }

// nit empresa que representa  132.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '132' . '00' . sprintf("%013s", ltrim($ide, "0"));

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

            if ($v["tipovinculo"] == 'RFS1' ||
                    $v["tipovinculo"] == 'RFDS1') {
                $is++;
                if ($is == 1) {
// tipo de documento 136.XX                
                    $ti = '';
                    switch ($v["idtipoidentificacionotros"]) {
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
                        case "N" : $ti = 'XX';
                            break;
                        case "R" : $ti = '11';
                            break;
                    }
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '136' . '00' . $ti;

                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    }

// identificacion 137.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '137' . '00' . sprintf("%013s", ltrim($ide, "0"));

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
                        $sepide = separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    }

// nit empresa que representa  144.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '144' . '00' . sprintf("%013s", ltrim($ide, "0"));

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
                    $ti = '';
                    switch ($v["idtipoidentificacionotros"]) {
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
                        case "N" : $ti = 'XX';
                            break;
                        case "R" : $ti = '11';
                            break;
                    }
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '148' . '00' . $ti;

                    $ide = '';
                    $dv = '';
                    if ($v["idtipoidentificacionotros"] == '2') {
                        $sepide = separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    }

// identificacion 149.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '149' . '00' . sprintf("%013s", ltrim($ide, "0"));

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
                        $sepide = separarDv($v["identificacionotros"]);
                        $ide = $sepide["identificacion"];
                        $dv = $sepide["dv"];
                    }

// nit empresa que representa  156.XX      
                    $iData++;
                    $retorno["datos"][$iData] = $raiz . '156' . '00' . sprintf("%013s", ltrim($ide, "0"));

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

        //
        return $retorno;
    }



}

?>
