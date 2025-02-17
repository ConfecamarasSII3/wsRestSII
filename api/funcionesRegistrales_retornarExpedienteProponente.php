<?php

class funcionesRegistrales_retornarExpedienteProponente {

    public static function retornarExpedienteProponente($dbx = null, $prop = '', $mat = '', $tipotramite = '', $proceso = 'Sin identificar la rutina', $origen = '', $retornarInhabilidad = 'si', $retornarRee = 'si', $incluir = 'todos') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        // $tipodata = '';

        $nameLog = 'retornarExpedienteProponente_' . date("Ymd");

        // Instancia la BD si no existe
        // ********************************************************************************** //
        if ($dbx === null) {
            $mysqli = conexionMysqliApi();
            if ($mysqli === false) {
                $_SESSION["generales"]["mensajerror"] = 'Error conectando con la BD';
                return false;
            }
        } else {
            $mysqli = $dbx;
        }


        $fecultinsc = '';
        $fecultinsccambidom = '';
        $estadoultimainscripcion = '';
        $estadoultimoacto = '';
        $cantidadnofirme = 0;
        $cantidadnopublicadasrues = 0;
        $fecultren = '';
        $fecultact = '';
        $fecultcanc = '';

        // \logApi::general2($nameLog, $prop, 'Inicia cargue del proponente');
//
        if (ltrim($prop, "0") != '') {
            $arrTemIns = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . ltrim($prop, "0") . "'");
            if ($arrTemIns === false || empty($arrTemIns)) {
                $_SESSION["generales"]["mensajeerror"] = '(1) Proponente no encontrado en mreg_est_inscritos';
                return false;
            } else {
                $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . ltrim($prop, "0") . "'");
                if ($arrTem === false || empty($arrTem)) {
                    $_SESSION["generales"]["mensajeerror"] = '(2) Proponente no encontrado en mreg_est_proponentes';
                    return false;
                }
                // \logApi::general2($nameLog, $prop, 'Leyo mreg_est_inscritos y mreg_est_proponentes');
            }
        }

        if (ltrim($prop, "0") != '') {
            if (ltrim($arrTemIns["matricula"], "0") != '') {
                if ($arrTemIns["ctrestmatricula"] != 'MA' && $arrTemIns["ctrestmatricula"] != 'MI' && $arrTemIns["ctrestmatricula"] != 'IA' && $arrTemIns["ctrestmatricula"] != 'II') {
                    $arrTemMer = false;
                } else {
                    $arrTemMer = $arrTemIns;
                }
            } else {
                $arrTemMer = false;
            }
        } else {
            $arrTem = false;
            $arrTemIns = false;
            if (ltrim($mat, "0") != '') {
                $arrTemMer = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($mat, "0") . "'");
                if ($arrTemMer["ctrestmatricula"] != 'MA' && $arrTemMer["ctrestmatricula"] != 'MI' && $arrTemMer["ctrestmatricula"] != 'IA' && $arrTemMer["ctrestmatricula"] != 'II') {
                    $arrTemMer = false;
                }
                // \logApi::general2($nameLog, $mat, 'Leyo mreg_est_inscritos por matricula');
            }
        }

//
        $retorno["tipotramite"] = $tipotramite;
        $retorno["numerorecuperacion"] = '';
        $retorno["numeroliquidacion"] = '';

        // Inicializa arreglos generales
        $retorno["enfirme"] = array();
        $retorno["representanteslegales"] = array();

        $retorno["inffin1510_fechacorte"] = '';
        $retorno["inffin1510_actcte"] = 0;
        $retorno["inffin1510_actnocte"] = 0;
        $retorno["inffin1510_fijnet"] = 0;
        $retorno["inffin1510_actotr"] = 0;
        $retorno["inffin1510_actval"] = 0;
        $retorno["inffin1510_acttot"] = 0;
        $retorno["inffin1510_pascte"] = 0;
        $retorno["inffin1510_paslar"] = 0;
        $retorno["inffin1510_pastot"] = 0;
        $retorno["inffin1510_patnet"] = 0;
        $retorno["inffin1510_paspat"] = 0;
        $retorno["inffin1510_balsoc"] = 0;
        $retorno["inffin1510_ingope"] = 0;
        $retorno["inffin1510_ingnoope"] = 0;
        $retorno["inffin1510_gasope"] = 0;
        $retorno["inffin1510_gasnoope"] = 0;
        $retorno["inffin1510_cosven"] = 0;
        $retorno["inffin1510_utinet"] = 0;
        $retorno["inffin1510_utiope"] = 0;
        $retorno["inffin1510_gasint"] = 0;
        $retorno["inffin1510_gasimp"] = 0;
        $retorno["inffin1510_indliq"] = 0;
        $retorno["inffin1510_nivend"] = 0;
        $retorno["inffin1510_razcob"] = 0;
        $retorno["inffin1510_renpat"] = 0;
        $retorno["inffin1510_renact"] = 0;
        $retorno["inffin1510_gruponiif"] = '';

        $retorno["inffin399a_fechacorte"] = '';
        $retorno["inffin399a_pregrabado"] = '';
        $retorno["inffin399a_actcte"] = 0;
        $retorno["inffin399a_actnocte"] = 0;
        $retorno["inffin399a_acttot"] = 0;
        $retorno["inffin399a_pascte"] = 0;
        $retorno["inffin399a_paslar"] = 0;
        $retorno["inffin399a_pastot"] = 0;
        $retorno["inffin399a_patnet"] = 0;
        $retorno["inffin399a_paspat"] = 0;
        $retorno["inffin399a_balsoc"] = 0;
        $retorno["inffin399a_ingope"] = 0;
        $retorno["inffin399a_ingnoope"] = 0;
        $retorno["inffin399a_gasope"] = 0;
        $retorno["inffin399a_gasnoope"] = 0;
        $retorno["inffin399a_cosven"] = 0;
        $retorno["inffin399a_utinet"] = 0;
        $retorno["inffin399a_utiope"] = 0;
        $retorno["inffin399a_gasint"] = 0;
        $retorno["inffin399a_gasimp"] = 0;
        $retorno["inffin399a_indliq"] = 0;
        $retorno["inffin399a_nivend"] = 0;
        $retorno["inffin399a_razcob"] = 0;
        $retorno["inffin399a_renpat"] = 0;
        $retorno["inffin399a_renact"] = 0;
        $retorno["inffin399a_gruponiif"] = '';

        $retorno["inffin399b_fechacorte"] = '';
        $retorno["inffin399b_pregrabado"] = '';
        $retorno["inffin399b_actcte"] = 0;
        $retorno["inffin399b_actnocte"] = 0;
        $retorno["inffin399b_acttot"] = 0;
        $retorno["inffin399b_pascte"] = 0;
        $retorno["inffin399b_paslar"] = 0;
        $retorno["inffin399b_pastot"] = 0;
        $retorno["inffin399b_patnet"] = 0;
        $retorno["inffin399b_paspat"] = 0;
        $retorno["inffin399b_balsoc"] = 0;
        $retorno["inffin399b_ingope"] = 0;
        $retorno["inffin399b_ingnoope"] = 0;
        $retorno["inffin399b_gasope"] = 0;
        $retorno["inffin399b_gasnoope"] = 0;
        $retorno["inffin399b_cosven"] = 0;
        $retorno["inffin399b_utinet"] = 0;
        $retorno["inffin399b_utiope"] = 0;
        $retorno["inffin399b_gasint"] = 0;
        $retorno["inffin399b_gasimp"] = 0;
        $retorno["inffin399b_indliq"] = 0;
        $retorno["inffin399b_nivend"] = 0;
        $retorno["inffin399b_razcob"] = 0;
        $retorno["inffin399b_renpat"] = 0;
        $retorno["inffin399b_renact"] = 0;
        $retorno["inffin399b_gruponiif"] = '';

        $retorno["sitcontrol"] = array();
        $retorno["exp1510"] = array();
        $retorno["clasi1510"] = array();
        $retorno["bienes"] = array();
        $retorno["contratos"] = array();
        $retorno["multas"] = array();
        $retorno["sanciones"] = array();
        $retorno["sandis"] = array();
        $retorno["inscripciones"] = array();
        $retorno["observaciones"] = '';
        $retorno["lcodigosbarras"] = array();
        $retorno["codigosbarraspendientes"] = '';
        $retorno["codigosbarras"] = 0;
        $retorno["recursostramite"] = '';
        $retorno["reeentramite"] = '';
        $retorno["inhabilidad"] = array();
        $retorno["crtsii"] = array();
        $retorno["financierahistorica"] = array();

        // ***************************************************************************** //
        // Relación de códigos de barras pendientes
        // ***************************************************************************** //
        if (ltrim($prop, "0") != '') {
            $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "proponente='" . $prop . "'", "codigobarras");
            $ix = 0;
            if ($arrX && !empty($arrX)) {
                foreach ($arrX as $x) {
                    if ($x["actoreparto"] == '09' || // Trámites de proponentes locales
                            $x["actoreparto"] == '27' || // Recursos de reposición
                            $x["actoreparto"] == '53' // Trámites RUES de proponentes
                    ) {
                        if ($x["estadofinal"] == '01' || // Radicado
                                $x["estadofinal"] == '04' || // En estudio
                                $x["estadofinal"] == '09' || // Reingresado
                                $x["estadofinal"] == '10' || // Devuelto a reparto
                                $x["estadofinal"] == '11' || // Inscrito
                                $x["estadofinal"] == '13' || // Asignado a estudio
                                $x["estadofinal"] == '22' || // Registrado - proponentes
                                $x["estadofinal"] == '23' || // En digitacion
                                $x["estadofinal"] == '24' || // Digitado
                                $x["estadofinal"] == '34' || // Para firma
                                $x["estadofinal"] == '35' || // Firmado
                                $x["estadofinal"] == '38' // Control de calidad
                        ) {
                            $ix++;
                            $retorno["lcodigosbarras"][$ix]["cbar"] = $x["codigobarras"];
                            $retorno["lcodigosbarras"][$ix]["frad"] = $x["fecharadicacion"];
                            $retorno["lcodigosbarras"][$ix]["ttra"] = $x["actoreparto"];
                            $retorno["lcodigosbarras"][$ix]["esta"] = $x["estadofinal"];
                            $retorno["lcodigosbarras"][$ix]["nesta"] = '';
                            $retorno["lcodigosbarras"][$ix]["ntra"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='01' and idcodigo='" . $retorno["lcodigosbarras"][$ix]["ttra"] . "'", "descripcion");
                            $retorno["lcodigosbarras"][$ix]["sist"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='01' and idcodigo='" . $retorno["lcodigosbarras"][$ix]["ttra"] . "'", "tipo");
                            $retorno["lcodigosbarras"][$ix]["nesta"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='82' and idcodigo='" . $retorno["lcodigosbarras"][$ix]["esta"] . "'", "descripcion");
                            $retorno["codigosbarras"]++;
                        }
                    }

                    // Si la ruta es un recurso en trámite
                    if ($x["actoreparto"] == '19' || $x["actoreparto"] == '23' || $x["actoreparto"] == '54') {
                        $retorno["recursostramite"] = 'S';
                    }
                }
            }
            // \logApi::general2($nameLog, $prop, 'Leyo codigos de barras');
        }

        // ****************************************************************************** //
        // Busca si existen reportes que no hayan sido inscritos
        // mreg_reportescontratos
        // mreg_reportesmultas
        // mreg_reportessanciones
        // mreg_reportessancionesdisciplinarias
        // ****************************************************************************** //
        $where = '';
        if ($arrTemIns && trim($arrTemIns["numid"], "0") != '') {
            $where = "nitproponente='" . $arrTemIns["numid"] . "'";
        }
        if ($arrTemIns && trim($arrTemIns["nit"], "0") != '') {
            if ($where != '') {
                $where .= " or ";
            }
            $where .= "nitproponente='" . $arrTemIns["numid"] . "'";
        }
        if (ltrim(trim($prop), "0") != '') {
            if ($where != '') {
                $where .= " or ";
            }
            $where .= "numeroproponente='" . trim(trim($prop), "0") . "'";
        }
        if ($where != '') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_reportescontratos', $where, "id");
            // \logApi::general2($nameLog, $prop, 'Leyo mreg_reportescontratos');
        } else {
            $temx = false;
        }
        if ($temx && !empty($temx)) {
            foreach ($temx as $tx) {
                if ($tx["estado"] != '03' && $tx["estado"] != '04') {
                    $retorno["reeentramite"] = 'si';
                }
            }
        }
        if ($retorno["reeentramite"] != 'si') {
            if ($where != '') {
                $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_reportesmultas', $where, "id");
                // \logApi::general2($nameLog, $prop, 'Leyo mreg_reportesmultas');
            } else {
                $temx = false;
            }
            if ($temx && !empty($temx)) {
                foreach ($temx as $tx) {
                    if ($tx["estado"] != '03' && $tx["estado"] != '04') {
                        $retorno["reeentramite"] = 'si';
                    }
                }
            }
        }
        if ($retorno["reeentramite"] != 'si') {
            if ($where != '') {
                $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_reportessanciones', $where, "id");
                // \logApi::general2($nameLog, $prop, 'Leyo mreg_reportessanciones');
            } else {
                $temx = false;
            }
            if ($temx && !empty($temx)) {
                foreach ($temx as $tx) {
                    if ($tx["estado"] != '03' && $tx["estado"] != '04') {
                        $retorno["reeentramite"] = 'si';
                    }
                }
            }
        }
        if ($retorno["reeentramite"] != 'si') {
            if ($where != '') {
                $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_reportessancionesdisciplinarias', $where, "id");
                // \logApi::general2($nameLog, $prop, 'Leyo mreg_reportessancionesdisciplinarias');
            } else {
                $temx = false;
            }
            if ($temx && !empty($temx)) {
                foreach ($temx as $tx) {
                    if ($tx["estado"] != '03' && $tx["estado"] != '04') {
                        $retorno["reeentramite"] = 'si';
                    }
                }
            }
        }

        // ***************************************************************************** //
        // ARMA LISTA COMPLETA DE INSCRIPCIONES
        // Encuentra ultimas fechas de cada acto de control
        // Encuentra el estado de la ultima inscripcion
        // ***************************************************************************** //
        $iInsc = 0;
        if (ltrim($prop, "0") != '') {
            $arrTemInsc = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "proponente='" . ltrim($prop, "0") . "'", "fecharegistro,horaregistro");
            // \logApi::general2($nameLog, $prop, 'Leyo mreg_est_inscripciones_proponentes');
            if ($arrTemInsc && !empty($arrTemInsc)) {
                foreach ($arrTemInsc as $insc) {
                    $iInsc++;
                    $retorno["inscripciones"][$iInsc] = array(
                        'libro' => $insc["libro"],
                        'registro' => $insc["registro"],
                        'fecharegistro' => $insc["fecharegistro"],
                        'horaregistro' => $insc["horaregistro"],
                        'fechadocumento' => $insc["fechadocumento"],
                        'tipodocumento' => $insc["tipodocumento"],
                        'acto' => $insc["acto"],
                        'fecpublicacionrue' => $insc["fecpublicacionrue"],
                        'idpublicacionrue' => $insc["idpublicacionrue"],
                        'estado' => $insc["estado"],
                        'texto' => $insc["texto"],
                        'ctrrevoca' => $insc["ctrrevoca"],
                        'registrorevocacion' => $insc["registrorevocacion"],
                        'fecharegistrorevocacion' => '' // Ojo calcular - 2017 08 22
                    );
                    if (trim($insc["acto"]) == '01') { // Ultima inscripcion
                        $cantidadnopublicadasrues = 0;
                        $cantidadnofirme = 0;
                        $fecultinsc = $insc["fecharegistro"];
                        $estadoultimainscripcion = $insc["estado"];
                        $estadoultimoacto = $insc["estado"];
                        $fecultren = '';
                        $fecultact = '';
                        $fecultcanc = '';
                        if ($insc["estado"] == '00') {
                            $cantidadnopublicadasrues++;
                        }
                        if ($insc["estado"] != '04') {
                            $cantidadnofirme++;
                        }
                    }
                    if (trim($insc["acto"]) == '16') { // Ultima inscripcion por cambio de domicilio
                        $cantidadnopublicadasrues = 0;
                        $cantidadnofirme = 0;
                        $fecultinsc = $insc["fecharegistro"];
                        $fecultinsccambidom = $insc["fecharegistro"];
                        $estadoultimainscripcion = '04';
                        $estadoultimoacto = '04'; // Automaticamente queda en firme
                        $fecultren = '';
                        $fecultact = '';
                        $fecultcanc = '';
                        if ($insc["estado"] == '00') {
                            $cantidadnopublicadasrues++;
                        }
                    }
                    if (trim($insc["acto"]) == '02') { // Ultima renovacion
                        $fecultren = $insc["fecharegistro"];
                        $estadoultimoacto = $insc["estado"];
                        if ($insc["estado"] == '00') {
                            $cantidadnopublicadasrues++;
                        }
                        if ($insc["estado"] != '04') {
                            $cantidadnofirme++;
                        }
                    }
                    if (trim($insc["acto"]) == '03' || trim($insc["acto"]) == '36') { // Ultima actualización
                        $fecultact = $insc["fecharegistro"];
                        $estadoultimoacto = $insc["estado"];
                        if ($insc["estado"] == '00') {
                            $cantidadnopublicadasrues++;
                        }
                        if ($insc["estado"] != '04') {
                            $cantidadnofirme++;
                        }
                    }
                    if (trim($insc["acto"]) == '04' || trim($insc["acto"]) == '05') { // Ultima cancelacion - cesacion
                        $fecultcanc = $insc["fecharegistro"];
                    }
                }
            }

            //
            $retorno["observaciones"] = '';
            if ($cantidadnopublicadasrues > 0) {
                $retorno["observaciones"] .= '!!! CUIDADO !!! Existen inscripciones en libros que no han sido publicadas al RUES';
            }
            if ($cantidadnofirme > 0) {
                if ($retorno["observaciones"] != '') {
                    $retorno["observaciones"] .= '<br><br>';
                }
                $retorno["observaciones"] .= '!!! CUIDADO !!! Existen inscripciones en libros que aún no han adquirido firmeza';
            }
        }

        // ***************************************************************************** //
        // ARMA DATOS ACTUALIZADOS DEL EXPEDIENTE
        // ES LA INFORMACIÓN ACTUAL, INCLUSIVE SI NO ESTÁ EN FIRME
        // ***************************************************************************** //
        // Datos de Identificacion del expediente
        if ($arrTemIns) {
            $retorno["proponente"] = ltrim(trim($arrTemIns["proponente"]), "0");
            $retorno["matricula"] = ltrim(trim($arrTemIns["matricula"]), "0");
            $retorno["ctrestmatricula"] = trim($arrTemIns["ctrestmatricula"]);
            $retorno["fecmatricula"] = trim($arrTemIns["fecmatricula"]);
            $retorno["nombre"] = trim($arrTemIns["razonsocial"]);
            $retorno["ape1"] = trim($arrTemIns["apellido1"]);
            $retorno["ape2"] = trim($arrTemIns["apellido2"]);
            $retorno["nom1"] = trim($arrTemIns["nombre1"]);
            $retorno["nom2"] = trim($arrTemIns["nombre2"]);
            $retorno["sigla"] = trim($arrTemIns["sigla"]);
            $retorno["idtipoidentificacion"] = $arrTemIns["idclase"];
            $retorno["categoria"] = $arrTemIns["categoria"];
            $retorno["identificacion"] = ltrim(trim($arrTemIns["numid"]), "0");

            $retorno["idpaisidentificacion"] = ''; // 20170607 - Nuevo campo por circular única
            $retorno["idmunidoc"] = ''; // 20170607 - Nuevo campo por circular única
            $retorno["fecexpdoc"] = ''; // 20170607 - Nuevo campo por circular única

            $retorno["nit"] = ltrim(trim($arrTemIns["nit"]), "0");
            $retorno["nacionalidad"] = trim($arrTemIns["nacionalidad"]);
            $retorno["organizacion"] = trim($arrTemIns["organizacion"]);
            if ($arrTem) {
                $retorno["tamanoempresa"] = trim($arrTem["tamanoempresa"]);
                $retorno["emprendedor28"] = trim($arrTem["emprendedor28"]);
                $retorno["pemprendedor28"] = trim($arrTem["pemprendedor28"]);
                $retorno["vigcontrol"] = trim($arrTem["vigcontrol"]);
                $retorno["certificardesde"] = trim($arrTem["certificardesde"]);
            }

            // Datos de cambio de domicilio
            $retorno["propcamaraorigen"] = '';
            if ($arrTem) {
                $retorno["cambidom_idmunicipioorigen"] = trim($arrTem["cambidom_idmunicipioorigen"]);
                $retorno["cambidom_idmunicipiodestino"] = trim($arrTem["cambidom_idmunicipiodestino"]);
                $retorno["cambidom_fechaultimainscripcion"] = trim($arrTem["cambidom_fechaultimainscripcion"]);
                $retorno["cambidom_fechaultimarenovacion"] = trim($arrTem["cambidom_fechaultimarenovacion"]);
            }

            // Datos de catalogacion del registro
            $retorno["idestadoproponente"] = trim($arrTemIns["ctrestproponente"]);
            $retorno["fechaultimainscripcion"] = $fecultinsc;
            if ($fecultinsc == $fecultinsccambidom) {
                if ($retorno["cambidom_fechaultimainscripcion"] != '') {
                    $retorno["fechaultimainscripcion"] = $retorno["cambidom_fechaultimainscripcion"];
                }
            }
            $retorno["fechaultimarenovacion"] = $fecultren;
            $retorno["fechaultimaactualizacion"] = $fecultact;
            $retorno["fechacancelacion"] = $fecultcanc;

            //LLENA CON INFORMACION DE mreg_est_proponentes
            if ($arrTem) {
                $retorno["idtipodocperjur"] = trim($arrTem["idtipodocperjur"]);
                $retorno["numdocperjur"] = trim($arrTem["numdocperjur"]);
                $retorno["fecdocperjur"] = trim($arrTem["fechaconstitucion"]);
                $retorno["origendocperjur"] = trim($arrTem["origendocperjur"]);
                $retorno["fechaconstitucion"] = trim($arrTem["fechaconstitucion"]);
                $retorno["fechavencimiento"] = trim($arrTem["fechavencimiento"]);
            }

            // informacion de ubicacion comercial en el registro mercantil
            if ($arrTem) {
                $retorno["dircom"] = trim($arrTem["dircom"]);
                $retorno["dircom_tipovia"] = '';
                $retorno["dircom_numvia"] = '';
                $retorno["dircom_apevia"] = '';
                $retorno["dircom_orivia"] = '';
                $retorno["dircom_numcruce"] = '';
                $retorno["dircom_apecruce"] = '';
                $retorno["dircom_oricruce"] = '';
                $retorno["dircom_numplaca"] = '';
                $retorno["dircom_complemento"] = '';
                $retorno["muncom"] = trim($arrTem["muncom"]);
                $retorno["paicom"] = '';  // Nuevo campo circular 002
                $retorno["telcom1"] = trim($arrTem["telcom1"]);
                $retorno["telcom2"] = trim($arrTem["telcom2"]);
                $retorno["faxcom"] = trim($arrTem["faxcom"]);
                $retorno["celcom"] = trim($arrTem["celcom"]);
                $retorno["emailcom"] = trim($arrTem["emailcom"]);
                $retorno["enviarint"] = trim($arrTem["enviarint"]);
                $retorno["codigopostalcom"] = ''; // Nuevo campo circular 002
                $retorno["codigozonacom"] = ''; // Nuevo campo circular 002 R/U
                // informacion de ubicacion de notificacion
                $retorno["dirnot"] = trim($arrTem["dirnot"]);
                $retorno["dirnot_tipovia"] = '';
                $retorno["dirnot_numvia"] = '';
                $retorno["dirnot_apevia"] = '';
                $retorno["dirnot_orivia"] = '';
                $retorno["dirnot_numcruce"] = '';
                $retorno["dirnot_apecruce"] = '';
                $retorno["dirnot_oricruce"] = '';
                $retorno["dirnot_numplaca"] = '';
                $retorno["dirnot_complemento"] = '';
                $retorno["munnot"] = trim($arrTem["munnot"]);
                $retorno["painot"] = '';  // Nuevo campo circular 002
                $retorno["telnot"] = trim($arrTem["telnot"]);
                $retorno["telnot2"] = trim($arrTem["telnot2"]);
                $retorno["faxnot"] = trim($arrTem["faxnot"]);
                $retorno["celnot"] = trim($arrTem["celnot"]);
                $retorno["emailnot"] = trim($arrTem["emailnot"]);
                $retorno["enviarnot"] = trim($arrTem["enviarnot"]);
                $retorno["codigopostalnot"] = ''; // Nuevo campo circular 002
                $retorno["codigozonanot"] = ''; // Nuevo campo circular 002 R/U
                $retorno["facultades"] = trim($arrTem["facultades"]);
            }

            // Informacion financiera decreto 1510
            if ($arrTem) {
                $retorno["gruponiif"] = '';
                $retorno["gruponiiftextual"] = '';
                $retorno["marcotecnicoanterior"] = '';
                $retorno["inffin1510_fechacorte"] = trim($arrTem["inffin1510_fechacorte"]);
                $retorno["inffin1510_actcte"] = doubleval($arrTem["inffin1510_actcte"]);
                $retorno["inffin1510_actnocte"] = doubleval($arrTem["inffin1510_actnocte"]);
                $retorno["inffin1510_fijnet"] = doubleval($arrTem["inffin1510_fijnet"]);
                $retorno["inffin1510_actotr"] = doubleval($arrTem["inffin1510_actotr"]);
                $retorno["inffin1510_actval"] = doubleval($arrTem["inffin1510_actval"]);
                $retorno["inffin1510_acttot"] = doubleval($arrTem["inffin1510_acttot"]);
                $retorno["inffin1510_pascte"] = doubleval($arrTem["inffin1510_pascte"]);
                $retorno["inffin1510_paslar"] = doubleval($arrTem["inffin1510_paslar"]);
                $retorno["inffin1510_pastot"] = doubleval($arrTem["inffin1510_pastot"]);
                $retorno["inffin1510_patnet"] = doubleval($arrTem["inffin1510_patnet"]);
                $retorno["inffin1510_paspat"] = doubleval($arrTem["inffin1510_paspat"]);
                $retorno["inffin1510_balsoc"] = doubleval($arrTem["inffin1510_balsoc"]);
                $retorno["inffin1510_ingope"] = doubleval($arrTem["inffin1510_ingope"]);
                $retorno["inffin1510_ingnoope"] = doubleval($arrTem["inffin1510_ingnoope"]);
                $retorno["inffin1510_gasope"] = doubleval($arrTem["inffin1510_gasope"]);
                $retorno["inffin1510_gasnoope"] = doubleval($arrTem["inffin1510_gasnoope"]);
                $retorno["inffin1510_cosven"] = doubleval($arrTem["inffin1510_utinet"]);
                $retorno["inffin1510_utiope"] = doubleval($arrTem["inffin1510_utiope"]);
                $retorno["inffin1510_gasint"] = doubleval($arrTem["inffin1510_gasint"]);
                $retorno["inffin1510_gasimp"] = doubleval($arrTem["inffin1510_gasimp"]);
                $retorno["inffin1510_indliq"] = doubleval($arrTem["inffin1510_indliq"]);
                $retorno["inffin1510_nivend"] = doubleval($arrTem["inffin1510_nivend"]);
                $retorno["inffin1510_razcob"] = doubleval($arrTem["inffin1510_razcob"]);
                $retorno["inffin1510_renpat"] = doubleval($arrTem["inffin1510_renpat"]);
                $retorno["inffin1510_renact"] = doubleval($arrTem["inffin1510_renact"]);

                //
                // if (doubleval($retorno["inffin1510_nivend"]) == 0) {
                if (doubleval($retorno["inffin1510_pastot"]) == 0) {
                    $retorno["inffin1510_nivend"] = 0;
                } else {
                    if (doubleval($retorno["inffin1510_acttot"]) == 0) {
                        $retorno["inffin1510_nivend"] = 999;
                    } else {
                        $retorno["inffin1510_nivend"] = doubleval($retorno["inffin1510_pastot"]) / doubleval($retorno["inffin1510_acttot"]);
                        if ($retorno["inffin1510_nivend"] < 0.01) {
                            $retorno["inffin1510_nivend"] = 0;
                        }
                    }
                }

                // }
                //
                /*
                if (doubleval($retorno["inffin1510_patnet"]) != 0) {
                    $retorno["inffin1510_renpat"] = $retorno["inffin1510_utiope"] / $retorno["inffin1510_patnet"];
                    if (abs($retorno["inffin1510_renpat"]) < 0.01) {
                        $retorno["inffin1510_renpat"] = 0;
                    }
                }
                */
                
                /*
                if (doubleval($retorno["inffin1510_acttot"]) != 0) {
                    $retorno["inffin1510_renact"] = $retorno["inffin1510_utiope"] / $retorno["inffin1510_acttot"];
                    if (abs($retorno["inffin1510_renact"]) < 0.01) {
                        $retorno["inffin1510_renact"] = 0;
                    }
                }
                */
                $retorno["inffin1510_gruponiif"] = trim((string)$arrTem["gruponiif"]);
            }

            // 2021-04-18 
            // Arma información financiera histórica
            $iVig = 0;
            $fin = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_financiera', "proponente='" . $prop . "'", "fechacorte asc");
            if ($fin && !empty($fin)) {
                foreach ($fin as $fin1) {
                    $insx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "libro='" . $fin1["libro"] . "' and registro='" . $fin1["registro"] . "' and proponente='" . $fin1["proponente"] . "'");
                    if ($insx && !empty($insx)) {
                        if ($insx["acto"] == '01' || $insx["acto"] == '02' || $insx["acto"] == '16' || $insx["acto"] == '36') {
                            $fin1["firmeza"] = '';
                            $fin1["acto"] = $insx["acto"];
                            if ($insx["acto"] == '16' || $insx["estado"] == '04') {
                                $fin1["firmeza"] = 'S';
                            }

                            //
                            if (doubleval($fin1["pastot"]) == 0) {
                                $fin1["nivend"] = 0;
                            } else {
                                if (doubleval($fin1["acttot"]) == 0) {
                                    $fin1["nivend"] = 999;
                                } else {
                                    $fin1["nivend"] = $fin1["pastot"] / $fin1["acttot"];
                                    if ($fin1["nivend"] < 0.01) {
                                        $fin1["nivend"] = 0;
                                    }
                                }
                            }

                            //
                            if (doubleval($fin1["patnet"]) != 0) {
                                $fin1["renpat"] = $fin1["utiope"] / $fin1["patnet"];
                                if (abs($fin1["renpat"]) < 0.01) {
                                    $fin1["renpat"] = 0;
                                }
                            }

                            if (doubleval($fin1["acttot"]) != 0) {
                                $fin1["renact"] = $fin1["utiope"] / $fin1["acttot"];
                                if (abs($fin1["renact"]) < 0.01) {
                                    $fin1["renact"] = 0;
                                }
                            }
                            $retorno["financierahistorica"][] = $fin1;

                            //
                            if (date("Y") == '2021') {
                                if ($fin1["fechacorte"] > '20200101') {
                                    if ($fin1["firmeza"] == 'S') {
                                        $retorno["inffin1510_fechacorte"] = trim($fin1["fechacorte"]);
                                        $retorno["inffin1510_actcte"] = trim($fin1["actcte"]);
                                        $retorno["inffin1510_actnocte"] = trim($fin1["actnocte"]);
                                        $retorno["inffin1510_fijnet"] = 0;
                                        $retorno["inffin1510_actotr"] = 0;
                                        $retorno["inffin1510_actval"] = 0;
                                        $retorno["inffin1510_acttot"] = trim($fin1["acttot"]);
                                        $retorno["inffin1510_pascte"] = trim($fin1["pascte"]);
                                        $retorno["inffin1510_paslar"] = trim($fin1["paslar"]);
                                        $retorno["inffin1510_pastot"] = trim($fin1["pastot"]);
                                        $retorno["inffin1510_patnet"] = trim($fin1["patnet"]);
                                        $retorno["inffin1510_paspat"] = trim($fin1["paspat"]);
                                        $retorno["inffin1510_balsoc"] = trim($fin1["balsoc"]);
                                        $retorno["inffin1510_ingope"] = trim($fin1["ingope"]);
                                        $retorno["inffin1510_ingnoope"] = trim($fin1["ingnoope"]);
                                        $retorno["inffin1510_gasope"] = trim($fin1["gasope"]);
                                        $retorno["inffin1510_gasnoope"] = trim($fin1["gasnoope"]);
                                        $retorno["inffin1510_cosven"] = trim($fin1["cosven"]);
                                        $retorno["inffin1510_utinet"] = trim($fin1["utinet"]);
                                        $retorno["inffin1510_utiope"] = trim($fin1["utiope"]);
                                        $retorno["inffin1510_gasint"] = trim($fin1["gasint"]);
                                        $retorno["inffin1510_gasimp"] = trim($fin1["gasimp"]);
                                        $retorno["inffin1510_indliq"] = trim($fin1["indliq"]);
                                        $retorno["inffin1510_nivend"] = trim($fin1["nivend"]);
                                        $retorno["inffin1510_razcob"] = trim($fin1["razcob"]);
                                        $retorno["inffin1510_renpat"] = trim($fin1["renpat"]);
                                        $retorno["inffin1510_renact"] = trim($fin1["renact"]);
                                        $retorno["inffin1510_gruponiif"] = trim($fin1["gruponiif"]);
                                    }
                                }
                                if ($fin1["fechacorte"] >= '20190101' && $fin1["fechacorte"] <= '20191231') {
                                    $retorno["inffin399a_fechacorte"] = trim($fin1["fechacorte"]);
                                    $retorno["inffin399a_pregrabado"] = 'si';
                                    $retorno["inffin399a_actcte"] = trim($fin1["actcte"]);
                                    $retorno["inffin399a_actnocte"] = trim($fin1["actnocte"]);
                                    $retorno["inffin399a_acttot"] = trim($fin1["acttot"]);
                                    $retorno["inffin399a_pascte"] = trim($fin1["pascte"]);
                                    $retorno["inffin399a_paslar"] = trim($fin1["paslar"]);
                                    $retorno["inffin399a_pastot"] = trim($fin1["pastot"]);
                                    $retorno["inffin399a_patnet"] = trim($fin1["patnet"]);
                                    $retorno["inffin399a_paspat"] = trim($fin1["paspat"]);
                                    $retorno["inffin399a_balsoc"] = trim($fin1["balsoc"]);
                                    $retorno["inffin399a_ingope"] = trim($fin1["ingope"]);
                                    $retorno["inffin399a_ingnoope"] = trim($fin1["ingnoope"]);
                                    $retorno["inffin399a_gasope"] = trim($fin1["gasope"]);
                                    $retorno["inffin399a_gasnoope"] = trim($fin1["gasnoope"]);
                                    $retorno["inffin399a_cosven"] = trim($fin1["cosven"]);
                                    $retorno["inffin399a_utinet"] = trim($fin1["utinet"]);
                                    $retorno["inffin399a_utiope"] = trim($fin1["utiope"]);
                                    $retorno["inffin399a_gasint"] = trim($fin1["gasint"]);
                                    $retorno["inffin399a_gasimp"] = trim($fin1["gasimp"]);
                                    $retorno["inffin399a_indliq"] = trim($fin1["indliq"]);
                                    $retorno["inffin399a_nivend"] = trim($fin1["nivend"]);
                                    $retorno["inffin399a_razcob"] = trim($fin1["razcob"]);
                                    $retorno["inffin399a_renpat"] = trim($fin1["renpat"]);
                                    $retorno["inffin399a_renact"] = trim($fin1["renact"]);
                                    $retorno["inffin399a_gruponiif"] = trim($fin1["gruponiif"]);
                                }
                                if ($fin1["fechacorte"] >= '20180101' && $fin1["fechacorte"] <= '20181231') {
                                    $retorno["inffin399b_fechacorte"] = trim($fin1["fechacorte"]);
                                    $retorno["inffin399b_pregrabado"] = 'si';
                                    $retorno["inffin399b_actcte"] = trim($fin1["actcte"]);
                                    $retorno["inffin399b_actnocte"] = trim($fin1["actnocte"]);
                                    $retorno["inffin399b_acttot"] = trim($fin1["acttot"]);
                                    $retorno["inffin399b_pascte"] = trim($fin1["pascte"]);
                                    $retorno["inffin399b_paslar"] = trim($fin1["paslar"]);
                                    $retorno["inffin399b_pastot"] = trim($fin1["pastot"]);
                                    $retorno["inffin399b_patnet"] = trim($fin1["patnet"]);
                                    $retorno["inffin399b_paspat"] = trim($fin1["paspat"]);
                                    $retorno["inffin399b_balsoc"] = trim($fin1["balsoc"]);
                                    $retorno["inffin399b_ingope"] = trim($fin1["ingope"]);
                                    $retorno["inffin399b_ingnoope"] = trim($fin1["ingnoope"]);
                                    $retorno["inffin399b_gasope"] = trim($fin1["gasope"]);
                                    $retorno["inffin399b_gasnoope"] = trim($fin1["gasnoope"]);
                                    $retorno["inffin399b_cosven"] = trim($fin1["cosven"]);
                                    $retorno["inffin399b_utinet"] = trim($fin1["utinet"]);
                                    $retorno["inffin399b_utiope"] = trim($fin1["utiope"]);
                                    $retorno["inffin399b_gasint"] = trim($fin1["gasint"]);
                                    $retorno["inffin399b_gasimp"] = trim($fin1["gasimp"]);
                                    $retorno["inffin399b_indliq"] = trim($fin1["indliq"]);
                                    $retorno["inffin399b_nivend"] = trim($fin1["nivend"]);
                                    $retorno["inffin399b_razcob"] = trim($fin1["razcob"]);
                                    $retorno["inffin399b_renpat"] = trim($fin1["renpat"]);
                                    $retorno["inffin399b_renact"] = trim($fin1["renact"]);
                                    $retorno["inffin399b_gruponiif"] = trim($fin1["gruponiif"]);
                                }
                            }

                            //
                            if (date("Y") == '2022') {
                                if ($fin1["fechacorte"] > '20210101') {
                                    if ($fin1["firmeza"] == 'S') {
                                        $retorno["inffin1510_fechacorte"] = trim($fin1["fechacorte"]);
                                        $retorno["inffin1510_actcte"] = trim($fin1["actcte"]);
                                        $retorno["inffin1510_actnocte"] = trim($fin1["actnocte"]);
                                        $retorno["inffin1510_fijnet"] = 0;
                                        $retorno["inffin1510_actotr"] = 0;
                                        $retorno["inffin1510_actval"] = 0;
                                        $retorno["inffin1510_acttot"] = trim($fin1["acttot"]);
                                        $retorno["inffin1510_pascte"] = trim($fin1["pascte"]);
                                        $retorno["inffin1510_paslar"] = trim($fin1["paslar"]);
                                        $retorno["inffin1510_pastot"] = trim($fin1["pastot"]);
                                        $retorno["inffin1510_patnet"] = trim($fin1["patnet"]);
                                        $retorno["inffin1510_paspat"] = trim($fin1["paspat"]);
                                        $retorno["inffin1510_balsoc"] = trim($fin1["balsoc"]);
                                        $retorno["inffin1510_ingope"] = trim($fin1["ingope"]);
                                        $retorno["inffin1510_ingnoope"] = trim($fin1["ingnoope"]);
                                        $retorno["inffin1510_gasope"] = trim($fin1["gasope"]);
                                        $retorno["inffin1510_gasnoope"] = trim($fin1["gasnoope"]);
                                        $retorno["inffin1510_cosven"] = trim($fin1["cosven"]);
                                        $retorno["inffin1510_utinet"] = trim($fin1["utinet"]);
                                        $retorno["inffin1510_utiope"] = trim($fin1["utiope"]);
                                        $retorno["inffin1510_gasint"] = trim($fin1["gasint"]);
                                        $retorno["inffin1510_gasimp"] = trim($fin1["gasimp"]);
                                        $retorno["inffin1510_indliq"] = trim($fin1["indliq"]);
                                        $retorno["inffin1510_nivend"] = trim($fin1["nivend"]);
                                        $retorno["inffin1510_razcob"] = trim($fin1["razcob"]);
                                        $retorno["inffin1510_renpat"] = trim($fin1["renpat"]);
                                        $retorno["inffin1510_renact"] = trim($fin1["renact"]);
                                        $retorno["inffin1510_gruponiif"] = trim($fin1["gruponiif"]);
                                    }
                                }
                                if ($fin1["fechacorte"] >= '20200101' && $fin1["fechacorte"] <= '20201231') {
                                    $retorno["inffin399a_fechacorte"] = trim($fin1["fechacorte"]);
                                    $retorno["inffin399a_pregrabado"] = 'si';
                                    $retorno["inffin399a_actcte"] = trim($fin1["actcte"]);
                                    $retorno["inffin399a_actnocte"] = trim($fin1["actnocte"]);
                                    $retorno["inffin399a_acttot"] = trim($fin1["acttot"]);
                                    $retorno["inffin399a_pascte"] = trim($fin1["pascte"]);
                                    $retorno["inffin399a_paslar"] = trim($fin1["paslar"]);
                                    $retorno["inffin399a_pastot"] = trim($fin1["pastot"]);
                                    $retorno["inffin399a_patnet"] = trim($fin1["patnet"]);
                                    $retorno["inffin399a_paspat"] = trim($fin1["paspat"]);
                                    $retorno["inffin399a_balsoc"] = trim($fin1["balsoc"]);
                                    $retorno["inffin399a_ingope"] = trim($fin1["ingope"]);
                                    $retorno["inffin399a_ingnoope"] = trim($fin1["ingnoope"]);
                                    $retorno["inffin399a_gasope"] = trim($fin1["gasope"]);
                                    $retorno["inffin399a_gasnoope"] = trim($fin1["gasnoope"]);
                                    $retorno["inffin399a_cosven"] = trim($fin1["cosven"]);
                                    $retorno["inffin399a_utinet"] = trim($fin1["utinet"]);
                                    $retorno["inffin399a_utiope"] = trim($fin1["utiope"]);
                                    $retorno["inffin399a_gasint"] = trim($fin1["gasint"]);
                                    $retorno["inffin399a_gasimp"] = trim($fin1["gasimp"]);
                                    $retorno["inffin399a_indliq"] = trim($fin1["indliq"]);
                                    $retorno["inffin399a_nivend"] = trim($fin1["nivend"]);
                                    $retorno["inffin399a_razcob"] = trim($fin1["razcob"]);
                                    $retorno["inffin399a_renpat"] = trim($fin1["renpat"]);
                                    $retorno["inffin399a_renact"] = trim($fin1["renact"]);
                                    $retorno["inffin399a_gruponiif"] = trim($fin1["gruponiif"]);
                                }
                                if ($fin1["fechacorte"] >= '20190101' && $fin1["fechacorte"] <= '20191231') {
                                    $retorno["inffin399b_fechacorte"] = trim($fin1["fechacorte"]);
                                    $retorno["inffin399b_pregrabado"] = 'si';
                                    $retorno["inffin399b_actcte"] = trim($fin1["actcte"]);
                                    $retorno["inffin399b_actnocte"] = trim($fin1["actnocte"]);
                                    $retorno["inffin399b_acttot"] = trim($fin1["acttot"]);
                                    $retorno["inffin399b_pascte"] = trim($fin1["pascte"]);
                                    $retorno["inffin399b_paslar"] = trim($fin1["paslar"]);
                                    $retorno["inffin399b_pastot"] = trim($fin1["pastot"]);
                                    $retorno["inffin399b_patnet"] = trim($fin1["patnet"]);
                                    $retorno["inffin399b_paspat"] = trim($fin1["paspat"]);
                                    $retorno["inffin399b_balsoc"] = trim($fin1["balsoc"]);
                                    $retorno["inffin399b_ingope"] = trim($fin1["ingope"]);
                                    $retorno["inffin399b_ingnoope"] = trim($fin1["ingnoope"]);
                                    $retorno["inffin399b_gasope"] = trim($fin1["gasope"]);
                                    $retorno["inffin399b_gasnoope"] = trim($fin1["gasnoope"]);
                                    $retorno["inffin399b_cosven"] = trim($fin1["cosven"]);
                                    $retorno["inffin399b_utinet"] = trim($fin1["utinet"]);
                                    $retorno["inffin399b_utiope"] = trim($fin1["utiope"]);
                                    $retorno["inffin399b_gasint"] = trim($fin1["gasint"]);
                                    $retorno["inffin399b_gasimp"] = trim($fin1["gasimp"]);
                                    $retorno["inffin399b_indliq"] = trim($fin1["indliq"]);
                                    $retorno["inffin399b_nivend"] = trim($fin1["nivend"]);
                                    $retorno["inffin399b_razcob"] = trim($fin1["razcob"]);
                                    $retorno["inffin399b_renpat"] = trim($fin1["renpat"]);
                                    $retorno["inffin399b_renact"] = trim($fin1["renact"]);
                                    $retorno["inffin399b_gruponiif"] = trim($fin1["gruponiif"]);
                                }
                            }
                        }
                    }
                }
            }
            // \logApi::general2($nameLog, $prop, 'Leyo mreg_est_proponentes_financiera');
        }

        //
        if (isset($arrTemMer) && $arrTemMer) {
            $arrTemx = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $arrTemMer["matricula"], '', '', '', 'N');
            // \logApi::general2($nameLog, $prop, 'Ejecuto  retornarExpedienteMercantil');
            $retorno["nombre"] = trim($arrTemMer["razonsocial"]);
            $retorno["ape1"] = trim($arrTemMer["apellido1"]);
            $retorno["ape2"] = trim($arrTemMer["apellido2"]);
            $retorno["nom1"] = trim($arrTemMer["nombre1"]);
            $retorno["nom2"] = trim($arrTemMer["nombre2"]);
            $retorno["sigla"] = trim($arrTemMer["sigla"]);

            $retorno["tipoidentificacion"] = $arrTemMer["idclase"];
            $retorno["categoria"] = $arrTemMer["categoria"];

            $retorno["identificacion"] = ltrim(trim($arrTemMer["numid"]), "0");
            $retorno["idpaisidentificacion"] = '';
            $retorno["nit"] = ltrim(trim($arrTemMer["nit"]), "0");
            $retorno["nacionalidad"] = trim($arrTemMer["nacionalidad"]);
            $retorno["organizacion"] = trim($arrTemMer["organizacion"]);
            // $retorno["tamanoempresa"] = trim($arrTemMer["tamanoempresa"]);
            $retorno["emprendedor28"] = trim($arrTemMer["emprendedor28"]);
            $retorno["pemprendedor28"] = trim($arrTemMer["pemprendedor28"]);
            $retorno["vigcontrol"] = trim($arrTemMer["vigcontrol"]);

            //LLENA CON INFORMACION DE mreg_est_proponentes
            $retorno["idtipodocperjur"] = '';
            $retorno["numdocperjur"] = '';
            $retorno["fecdocperjur"] = '';
            $retorno["origendocperjur"] = '';
            if ($retorno["fechaconstitucion"] == '') {
                $retorno["fechaconstitucion"] = trim($arrTemx["fechaconstitucion"]);
            }
            $retorno["fechavencimiento"] = trim($arrTemx["fechavencimiento"]);
            if (trim($retorno["fechavencimiento"]) == '99999999') {
                $retorno["fechavencimiento"] = '';
            }

//
            if ($retorno["organizacion"] > '02') {
                foreach ($arrTemx["inscripciones"] as $ix) {
                    if ($ix["grupoacto"] == '005') {
                        $retorno["idtipodocperjur"] = $ix["tdoc"];
                        $retorno["numdocperjur"] = $ix["ndoc"];
                        $retorno["fecdocperjur"] = $ix["fdoc"];
                        $retorno["origendocperjur"] = $ix["txoridoc"];
                    }
                }
                if ($retorno["idtipodocperjur"] == '') {
                    $retorno["numdocperjur"] = $arrTemx["numperj"];
                    $retorno["fecdocperjur"] = $arrTemx["fecperj"];
                }
            }


            //
            $retorno["dircom"] = trim($arrTemMer["dircom"]);
            $retorno["dircom_tipovia"] = '';
            $retorno["dircom_numvia"] = '';
            $retorno["dircom_apevia"] = '';
            $retorno["dircom_orivia"] = '';
            $retorno["dircom_numcruce"] = '';
            $retorno["dircom_apecruce"] = '';
            $retorno["dircom_oricruce"] = '';
            $retorno["dircom_numplaca"] = '';
            $retorno["dircom_complemento"] = '';
            $retorno["muncom"] = trim($arrTemMer["muncom"]);
            $retorno["paicom"] = trim($arrTemMer["paicom"]);
            $retorno["telcom1"] = trim($arrTemMer["telcom1"]);
            $retorno["telcom2"] = trim($arrTemMer["telcom2"]);
            $retorno["faxcom"] = trim($arrTemMer["faxcom"]);
            $retorno["celcom"] = trim($arrTemMer["telcom3"]);
            $retorno["emailcom"] = trim($arrTemMer["emailcom"]);
            $retorno["enviarint"] = trim($arrTemMer["ctrnotemail"]);
            $retorno["codigopostalcom"] = trim($arrTemMer["codigopostalcom"]);
            $retorno["codigozonacom"] = trim($arrTemMer["codigozonacom"]);

            // informacion de ubicacion de notificacion
            $retorno["dirnot"] = trim($arrTemMer["dirnot"]);
            $retorno["dirnot_tipovia"] = '';
            $retorno["dirnot_numvia"] = '';
            $retorno["dirnot_apevia"] = '';
            $retorno["dirnot_orivia"] = '';
            $retorno["dirnot_numcruce"] = '';
            $retorno["dirnot_apecruce"] = '';
            $retorno["dirnot_oricruce"] = '';
            $retorno["dirnot_numplaca"] = '';
            $retorno["dirnot_complemento"] = '';
            $retorno["munnot"] = trim($arrTemMer["munnot"]);
            $retorno["painot"] = trim($arrTemMer["painot"]);
            $retorno["telnot"] = trim($arrTemMer["telnot"]);
            $retorno["telnot2"] = trim($arrTemMer["telnot2"]);
            $retorno["faxnot"] = trim($arrTemMer["faxnot"]);
            $retorno["celnot"] = trim($arrTemMer["telnot3"]);
            $retorno["emailnot"] = trim($arrTemMer["emailnot"]);
            $retorno["enviarnot"] = trim($arrTemMer["ctrnotsms"]);
            $retorno["codigopostalnot"] = trim($arrTemMer["codigopostalnot"]);
            $retorno["codigozonanot"] = ''; // Adicionado con circular 002

            if (isset($arrTemx["crtsii"]["0041"]) && trim($arrTemx["crtsii"]["0041"]) != '') {
                $retorno["crt0041"] = $arrTemx["crtsii"]["0041"]; // Adicionado con circular 002
            } else {
                if (isset($arrTemx["crt"]["0041"]) && trim($arrTemx["crt"]["0041"]) != '') {
                    $retorno["crt0041"] = $arrTemx["crt"]["0041"]; // Adicionado con circular 
                }
            }

            if (isset($arrTemx["crtsii"]["1121"]) && trim($arrTemx["crtsii"]["1121"]) != '') {
                $retorno["crt1121"] = $arrTemx["crtsii"]["1121"]; // Adicionado con circular 002
            } else {
                if (isset($arrTemx["crt"]["1121"]) && trim($arrTemx["crt"]["1121"]) != '') {
                    $retorno["crt1121"] = $arrTemx["crt"]["1121"]; // Adicionado con circular 
                }
            }

            // Localiza vínculos de representación legal
            $i = 0;
            if (!empty($arrTemx["vinculos"])) {
                foreach ($arrTemx["vinculos"] as $v) {
                    if ($v["tipovinculo"] == 'RLP' ||
                            $v["tipovinculo"] == 'RLS' ||
                            $v["tipovinculo"] == 'RLS1' ||
                            $v["tipovinculo"] == 'RLS2' ||
                            $v["tipovinculo"] == 'RLS3' ||
                            $v["tipovinculo"] == 'RLS4') {
                        $i++;
                        $retorno["representanteslegales"][$i]["idtipoidentificacionrepleg"] = $v["idtipoidentificacionotros"];
                        $retorno["representanteslegales"][$i]["identificacionrepleg"] = $v["identificacionotros"];
                        $retorno["representanteslegales"][$i]["paisrepleg"] = $v["paisotros"];
                        $retorno["representanteslegales"][$i]["nombrerepleg"] = $v["nombreotros"];
                        $retorno["representanteslegales"][$i]["cargorepleg"] = $v["cargootros"];
                        $retorno["representanteslegales"][$i]["nom1"] = $v["nombre1otros"];
                        $retorno["representanteslegales"][$i]["nom2"] = $v["nombre2otros"];
                        $retorno["representanteslegales"][$i]["ape1"] = $v["apellido1otros"];
                        $retorno["representanteslegales"][$i]["ape2"] = $v["apellido2otros"];
                    }
                }
            }

            // Localiza facultades de los comerciantes
            $retorno["facultades"] = '';
            $certs = retornarRegistrosMysqliApi($mysqli, 'mreg_codigos_certificas', "clase IN ('CRT-FACULTADES','CRT-ORGADM')", "id");
            // \logApi::general2($nameLog, $prop, 'Leyo  mreg_codigos_certificas');
            if ($certs && !empty($certs)) {
                foreach ($certs as $cr) {
                    if (isset($arrTemx["crtsii"][$cr["id"]]) && trim($arrTemx["crtsii"][$cr["id"]]) != '') {
                        if ($retorno["facultades"] != '') {
                            $retorno["facultades"] .= "\r\n";
                        }
                        $retorno["facultades"] .= \funcionesGenerales::reemplazarAcutes($arrTemx["crtsii"][$cr["id"]]);
                    }
                }
                if (trim($retorno["facultades"]) == '') {
                    foreach ($certs as $cr) {
                        if (isset($arrTemx["crt"][$cr["id"]]) && trim($arrTemx["crt"][$cr["id"]]) != '') {
                            if ($retorno["facultades"] != '') {
                                $retorno["facultades"] .= "\r\n";
                            }
                            $retorno["facultades"] .= str_replace(array("||", "|"), array(chr(13), chr(10), " "), $arrTemx["crt"][$cr["id"]]);
                        }
                    }
                }
            }


            //
            unset($arrTemx);
        }


        // Busca Representantes Legales - Cuando no es comerciante
        $i = 0;
        if (isset($arrTemMer) && $arrTemMer === false && $arrTem) {
            $arrTemVin = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_representacion', "proponente='" . $arrTem["proponente"] . "'", "id");
            foreach ($arrTemVin as $v) {
                $i++;
                $retorno["representanteslegales"][$i]["idtipoidentificacionrepleg"] = $v["tipoidentificacion"];
                $retorno["representanteslegales"][$i]["identificacionrepleg"] = $v["identificacion"];
                $retorno["representanteslegales"][$i]["paisrepleg"] = ''; // nace con circular 002
                $retorno["representanteslegales"][$i]["nombrerepleg"] = $v["nombre"];
                $retorno["representanteslegales"][$i]["cargorepleg"] = $v["cargo"];
                $retorno["representanteslegales"][$i]["nom1"] = '';
                $retorno["representanteslegales"][$i]["nom2"] = '';
                $retorno["representanteslegales"][$i]["ape1"] = '';
                $retorno["representanteslegales"][$i]["ape2"] = '';
            }
            // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_proponentes_representacion');
        }

        // Busca Situaciones de control
        if (ltrim($prop, "0") != '') {
            $i = 0;
            $arrTemCer = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_sitcontrol', "proponente='" . ltrim($prop, "0") . "'", "secuencia");
            foreach ($arrTemCer as $c) {
                $i++;
                $retorno["sitcontrol"][$i]["nombre"] = $c["nombre"];
                $retorno["sitcontrol"][$i]["identificacion"] = $c["identificacion"];
                $retorno["sitcontrol"][$i]["domicilio"] = $c["domicilio"];
                $retorno["sitcontrol"][$i]["tipo"] = $c["tipo"];
            }
            // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_proponentes_sitcontrol');
        }

        // Busca clasificaciones
        if (ltrim($prop, "0") != '') {
            $i = 0;
            $retorno["clasi1510"] = array();
            $arrTemCer = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_unspsc', "proponente='" . ltrim($prop, "0") . "'", "unspsc");
            foreach ($arrTemCer as $c) {
                $i++;
                $retorno["clasi1510"][$i] = $c["unspsc"];
            }
            asort($retorno["clasi1510"]);
            // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_proponentes_unspsc');
        }

        // Busca experiencia
        if (ltrim($prop, "0") != '') {
            $i = 0;
            $retorno["exp1510"] = array();
            $arrTemCer = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_experiencia', "proponente='" . ltrim($prop, "0") . "'", "secuencia");
            foreach ($arrTemCer as $c) {
                $i++;
                $retorno["exp1510"][$i]["secuencia"] = $c["secuencia"];
                $retorno["exp1510"][$i]["clavecontrato"] = '';
                $retorno["exp1510"][$i]["celebradopor"] = $c["celebradopor"];
                $retorno["exp1510"][$i]["nombrecontratista"] = $c["nombrecontratista"];
                $retorno["exp1510"][$i]["nombrecontratante"] = $c["nombrecontratante"];
                $retorno["exp1510"][$i]["fecejecucion"] = $c["fecejecucion"];
                $retorno["exp1510"][$i]["valor"] = $c["valor"];
                $retorno["exp1510"][$i]["porcentaje"] = $c["porcentaje"];
                $retorno["exp1510"][$i]["clasif"] = array();
                $arrx = explode(",", $c["clasificaciones"]);
                $iCla = 0;
                $tx = array();
                if (trim($c["clasificaciones"]) != '') {
                    if (!empty($arrx)) {
                        foreach ($arrx as $x) {
                            $x = trim($x);
                            if (!isset($tx[$x])) {
                                $iCla++;
                                $retorno["exp1510"][$i]["clasif"][$iCla] = trim($x);
                                $tx[$x] = $x;
                            }
                        }
                    }
                }
                asort($retorno["exp1510"][$i]["clasif"]);
                unset($tx);
                $retorno["exp1510"][$i]["soportedeclaracion"] = '';
                $retorno["exp1510"][$i]["soportecontrato"] = '';
                $retorno["exp1510"][$i]["generardeclaracion"] = '';
                $retorno["exp1510"][$i]["revisado"] = '';
            }
            // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_proponentes_experiencia');
        }

        // ***************************************************************************** //
        // ARMA ARREGLO DE DATOS EN FIRME
        // ***************************************************************************** //
        if (isset($retorno["proponente"])) {
            $retorno["enfirme"]["proponente"] = $retorno["proponente"];
        }
        if (isset($retorno["matricula"])) {
            $retorno["enfirme"]["matricula"] = $retorno["matricula"];
        }
        if (isset($retorno["fecmatricula"])) {
            $retorno["enfirme"]["fecmatricula"] = $retorno["fecmatricula"];
        }
        if (isset($retorno["ctrestmatricula"])) {
            $retorno["enfirme"]["ctrestmatricula"] = $retorno["ctrestmatricula"];
        }
        if (isset($retorno["nombre"])) {
            $retorno["enfirme"]["nombre"] = $retorno["nombre"];
        }
        if (isset($retorno["ape1"])) {
            $retorno["enfirme"]["ape1"] = $retorno["ape1"];
        }
        if (isset($retorno["ape2"])) {
            $retorno["enfirme"]["ape2"] = $retorno["ape2"];
        }
        if (isset($retorno["nom1"])) {
            $retorno["enfirme"]["nom1"] = $retorno["nom1"];
        }
        if (isset($retorno["nom2"])) {
            $retorno["enfirme"]["nom2"] = $retorno["nom2"];
        }
        if (isset($retorno["sigla"])) {
            $retorno["enfirme"]["sigla"] = $retorno["sigla"];
        }
        if (isset($retorno["idtipoidentificacion"])) {
            $retorno["enfirme"]["idtipoidentificacion"] = $retorno["idtipoidentificacion"];
        }
        if (isset($retorno["categoria"])) {
            $retorno["enfirme"]["categoria"] = $retorno["categoria"];
        }
        if (isset($retorno["identificacion"])) {
            $retorno["enfirme"]["identificacion"] = $retorno["identificacion"];
        }
        if (isset($retorno["idpaisidentificacion"])) {
            $retorno["enfirme"]["idpaisidentificacion"] = $retorno["idpaisidentificacion"];
        }
        if (isset($retorno["nit"])) {
            $retorno["enfirme"]["nit"] = $retorno["nit"];
        }
        if (isset($retorno["nacionalidad"])) {
            $retorno["enfirme"]["nacionalidad"] = $retorno["nacionalidad"];
        }
        if (isset($retorno["organizacion"])) {
            $retorno["enfirme"]["organizacion"] = $retorno["organizacion"];
        }
        if (isset($retorno["tamanoempresa"])) {
            $retorno["enfirme"]["tamanoempresa"] = $retorno["tamanoempresa"];
        }
        if (isset($retorno["emprendedor28"])) {
            $retorno["enfirme"]["emprendedor28"] = $retorno["emprendedor28"];
        }
        if (isset($retorno["pemprendedor28"])) {
            $retorno["enfirme"]["pemprendedor28"] = $retorno["pemprendedor28"];
        }
        if (isset($retorno["vigcontrol"])) {
            $retorno["enfirme"]["vigcontrol"] = $retorno["vigcontrol"];
        }

        if (isset($retorno["idestadoproponente"])) {
            $retorno["enfirme"]["idestadoproponente"] = $retorno["idestadoproponente"];
        }
        if (isset($retorno["fechaultimainscripcion"])) {
            $retorno["enfirme"]["fechaultimainscripcion"] = $retorno["fechaultimainscripcion"];
        }
        if (isset($retorno["fechaultimarenovacion"])) {
            $retorno["enfirme"]["fechaultimarenovacion"] = $retorno["fechaultimarenovacion"];
        }
        if (isset($retorno["fechaultimaactualizacion"])) {
            $retorno["enfirme"]["fechaultimaactualizacion"] = $retorno["fechaultimaactualizacion"];
        }
        if (isset($retorno["fechacancelacion"])) {
            $retorno["enfirme"]["fechacancelacion"] = $retorno["fechacancelacion"];
        }

        // Datos de cambio de domicilio
        if (isset($retorno["propcamaraorigen"])) {
            $retorno["enfirme"]["propcamaraorigen"] = $retorno["propcamaraorigen"];
        }
        if (isset($retorno["cambidom_idmunicipioorigen"])) {
            $retorno["enfirme"]["cambidom_idmunicipioorigen"] = $retorno["cambidom_idmunicipioorigen"];
        }
        if (isset($retorno["cambidom_idmunicipiodestino"])) {
            $retorno["enfirme"]["cambidom_idmunicipiodestino"] = $retorno["cambidom_idmunicipiodestino"];
        }
        if (isset($retorno["cambidom_fechaultimainscripcion"])) {
            $retorno["enfirme"]["cambidom_fechaultimainscripcion"] = $retorno["cambidom_fechaultimainscripcion"];
        }
        if (isset($retorno["cambidom_fechaultimarenovacion"])) {
            $retorno["enfirme"]["cambidom_fechaultimarenovacion"] = $retorno["cambidom_fechaultimarenovacion"];
        }

        if (isset($retorno["dircom"])) {
            $retorno["enfirme"]["dircom"] = $retorno["dircom"];
        }
        if (isset($retorno["muncom"])) {
            $retorno["enfirme"]["muncom"] = $retorno["muncom"];
        }
        if (isset($retorno["telcom1"])) {
            $retorno["enfirme"]["telcom1"] = $retorno["telcom1"];
        }
        if (isset($retorno["telcom2"])) {
            $retorno["enfirme"]["telcom2"] = $retorno["telcom2"];
        }
        if (isset($retorno["faxcom"])) {
            $retorno["enfirme"]["faxcom"] = $retorno["faxcom"];
        }
        if (isset($retorno["celcom"])) {
            $retorno["enfirme"]["celcom"] = $retorno["celcom"];
        }
        if (isset($retorno["emailcom"])) {
            $retorno["enfirme"]["emailcom"] = $retorno["emailcom"];
        }
        if (isset($retorno["enviarint"])) {
            $retorno["enfirme"]["enviarint"] = $retorno["enviarint"];
        }

        // informacion de ubicacion de notificacion
        if (isset($retorno["dirnot"])) {
            $retorno["enfirme"]["dirnot"] = $retorno["dirnot"];
        }
        if (isset($retorno["munnot"])) {
            $retorno["enfirme"]["munnot"] = $retorno["munnot"];
        }
        if (isset($retorno["telnot"])) {
            $retorno["enfirme"]["telnot"] = $retorno["telnot"];
        }
        if (isset($retorno["telnot2"])) {
            $retorno["enfirme"]["telnot2"] = $retorno["telnot2"];
        }
        if (isset($retorno["faxnot"])) {
            $retorno["enfirme"]["faxnot"] = $retorno["faxnot"];
        }
        if (isset($retorno["celnot"])) {
            $retorno["enfirme"]["celnot"] = $retorno["celnot"];
        }
        if (isset($retorno["emailnot"])) {
            $retorno["enfirme"]["emailnot"] = $retorno["emailnot"];
        }
        if (isset($retorno["enviarnot"])) {
            $retorno["enfirme"]["enviarnot"] = $retorno["enviarnot"];
        }

        //
        $retorno["enfirme"]["inffin1510_fechacorte"] = $retorno["inffin1510_fechacorte"];
        $retorno["enfirme"]["inffin1510_actcte"] = $retorno["inffin1510_actcte"];
        $retorno["enfirme"]["inffin1510_actnocte"] = $retorno["inffin1510_actnocte"];
        $retorno["enfirme"]["inffin1510_fijnet"] = 0;
        $retorno["enfirme"]["inffin1510_actotr"] = 0;
        $retorno["enfirme"]["inffin1510_actval"] = 0;
        $retorno["enfirme"]["inffin1510_acttot"] = $retorno["inffin1510_acttot"];
        $retorno["enfirme"]["inffin1510_pascte"] = $retorno["inffin1510_pascte"];
        $retorno["enfirme"]["inffin1510_paslar"] = $retorno["inffin1510_paslar"];
        $retorno["enfirme"]["inffin1510_pastot"] = $retorno["inffin1510_pastot"];
        $retorno["enfirme"]["inffin1510_patnet"] = $retorno["inffin1510_patnet"];
        $retorno["enfirme"]["inffin1510_paspat"] = $retorno["inffin1510_paspat"];
        $retorno["enfirme"]["inffin1510_balsoc"] = $retorno["inffin1510_balsoc"];
        $retorno["enfirme"]["inffin1510_ingope"] = $retorno["inffin1510_ingope"];
        $retorno["enfirme"]["inffin1510_ingnoope"] = $retorno["inffin1510_ingnoope"];
        $retorno["enfirme"]["inffin1510_gasope"] = $retorno["inffin1510_gasope"];
        $retorno["enfirme"]["inffin1510_gasnoope"] = $retorno["inffin1510_gasnoope"];
        $retorno["enfirme"]["inffin1510_cosven"] = $retorno["inffin1510_cosven"];
        $retorno["enfirme"]["inffin1510_utinet"] = $retorno["inffin1510_utinet"];
        $retorno["enfirme"]["inffin1510_utiope"] = $retorno["inffin1510_utiope"];
        $retorno["enfirme"]["inffin1510_gasint"] = $retorno["inffin1510_gasint"];
        $retorno["enfirme"]["inffin1510_gasimp"] = $retorno["inffin1510_gasimp"];
        $retorno["enfirme"]["inffin1510_indliq"] = $retorno["inffin1510_indliq"];
        $retorno["enfirme"]["inffin1510_nivend"] = $retorno["inffin1510_nivend"];
        $retorno["enfirme"]["inffin1510_razcob"] = $retorno["inffin1510_razcob"];
        $retorno["enfirme"]["inffin1510_renpat"] = $retorno["inffin1510_renpat"];
        $retorno["enfirme"]["inffin1510_renact"] = $retorno["inffin1510_renact"];
        $retorno["enfirme"]["inffin1510_gruponiif"] = $retorno["inffin1510_gruponiif"];

        $iVig = 0;
        foreach ($retorno["financierahistorica"] as $fin1) {
            $iVig++;
            if (date("Y") == '2021') {
                if ($fin1["firmeza"] == 'S') {
                    if ($fin1["fechacorte"] >= '20200101') {
                        $retorno["enfirme"]["inffin1510_fechacorte"] = $fin1["fechacorte"];
                        $retorno["enfirme"]["inffin1510_actcte"] = trim($fin1["actcte"]);
                        $retorno["enfirme"]["inffin1510_actnocte"] = trim($fin1["actnocte"]);
                        $retorno["enfirme"]["inffin1510_fijnet"] = 0;
                        $retorno["enfirme"]["inffin1510_actotr"] = 0;
                        $retorno["enfirme"]["inffin1510_actval"] = 0;
                        $retorno["enfirme"]["inffin1510_acttot"] = trim($fin1["acttot"]);
                        $retorno["enfirme"]["inffin1510_pascte"] = trim($fin1["pascte"]);
                        $retorno["enfirme"]["inffin1510_paslar"] = trim($fin1["paslar"]);
                        $retorno["enfirme"]["inffin1510_pastot"] = trim($fin1["pastot"]);
                        $retorno["enfirme"]["inffin1510_patnet"] = trim($fin1["patnet"]);
                        $retorno["enfirme"]["inffin1510_paspat"] = trim($fin1["paspat"]);
                        $retorno["enfirme"]["inffin1510_balsoc"] = trim($fin1["balsoc"]);
                        $retorno["enfirme"]["inffin1510_ingope"] = trim($fin1["ingope"]);
                        $retorno["enfirme"]["inffin1510_ingnoope"] = trim($fin1["ingnoope"]);
                        $retorno["enfirme"]["inffin1510_gasope"] = trim($fin1["gasope"]);
                        $retorno["enfirme"]["inffin1510_gasnoope"] = trim($fin1["gasnoope"]);
                        $retorno["enfirme"]["inffin1510_cosven"] = trim($fin1["cosven"]);
                        $retorno["enfirme"]["inffin1510_utinet"] = trim($fin1["utinet"]);
                        $retorno["enfirme"]["inffin1510_utiope"] = trim($fin1["utiope"]);
                        $retorno["enfirme"]["inffin1510_gasint"] = trim($fin1["gasint"]);
                        $retorno["enfirme"]["inffin1510_gasimp"] = trim($fin1["gasimp"]);
                        $retorno["enfirme"]["inffin1510_indliq"] = trim($fin1["indliq"]);
                        $retorno["enfirme"]["inffin1510_nivend"] = trim($fin1["nivend"]);
                        $retorno["enfirme"]["inffin1510_razcob"] = trim($fin1["razcob"]);
                        $retorno["enfirme"]["inffin1510_renpat"] = trim($fin1["renpat"]);
                        $retorno["enfirme"]["inffin1510_renact"] = trim($fin1["renact"]);
                        $retorno["enfirme"]["inffin1510_gruponiif"] = trim($fin1["gruponiif"]);
                    }
                    if ($fin1["fechacorte"] >= '20190101' && $fin1["fechacorte"] <= '20191231') {
                        $retorno["enfirme"]["inffin399a_fechacorte"] = $fin1["fechacorte"];
                        $retorno["enfirme"]["inffin399a_pregrabado"] = 'si';
                        $retorno["enfirme"]["inffin399a_actcte"] = trim($fin1["actcte"]);
                        $retorno["enfirme"]["inffin399a_actnocte"] = trim($fin1["actnocte"]);
                        $retorno["enfirme"]["inffin399a_acttot"] = trim($fin1["acttot"]);
                        $retorno["enfirme"]["inffin399a_pascte"] = trim($fin1["pascte"]);
                        $retorno["enfirme"]["inffin399a_paslar"] = trim($fin1["paslar"]);
                        $retorno["enfirme"]["inffin399a_pastot"] = trim($fin1["pastot"]);
                        $retorno["enfirme"]["inffin399a_patnet"] = trim($fin1["patnet"]);
                        $retorno["enfirme"]["inffin399a_paspat"] = trim($fin1["paspat"]);
                        $retorno["enfirme"]["inffin399a_balsoc"] = trim($fin1["balsoc"]);
                        $retorno["enfirme"]["inffin399a_ingope"] = trim($fin1["ingope"]);
                        $retorno["enfirme"]["inffin399a_ingnoope"] = trim($fin1["ingnoope"]);
                        $retorno["enfirme"]["inffin399a_gasope"] = trim($fin1["gasope"]);
                        $retorno["enfirme"]["inffin399a_gasnoope"] = trim($fin1["gasnoope"]);
                        $retorno["enfirme"]["inffin399a_cosven"] = trim($fin1["cosven"]);
                        $retorno["enfirme"]["inffin399a_utinet"] = trim($fin1["utinet"]);
                        $retorno["enfirme"]["inffin399a_utiope"] = trim($fin1["utiope"]);
                        $retorno["enfirme"]["inffin399a_gasint"] = trim($fin1["gasint"]);
                        $retorno["enfirme"]["inffin399a_gasimp"] = trim($fin1["gasimp"]);
                        $retorno["enfirme"]["inffin399a_indliq"] = trim($fin1["indliq"]);
                        $retorno["enfirme"]["inffin399a_nivend"] = trim($fin1["nivend"]);
                        $retorno["enfirme"]["inffin399a_razcob"] = trim($fin1["razcob"]);
                        $retorno["enfirme"]["inffin399a_renpat"] = trim($fin1["renpat"]);
                        $retorno["enfirme"]["inffin399a_renact"] = trim($fin1["renact"]);
                        $retorno["enfirme"]["inffin399a_gruponiif"] = trim($fin1["gruponiif"]);
                        $retorno["enfirme"]["inffin399a_gruponiif"] = trim($fin1["gruponiif"]);
                    }
                    if ($fin1["fechacorte"] >= '20180101' && $fin1["fechacorte"] <= '20181231') {
                        $retorno["enfirme"]["inffin399b_fechacorte"] = $fin1["fechacorte"];
                        $retorno["enfirme"]["inffin399b_pregrabado"] = 'si';
                        $retorno["enfirme"]["inffin399b_actcte"] = trim($fin1["actcte"]);
                        $retorno["enfirme"]["inffin399b_actnocte"] = trim($fin1["actnocte"]);
                        $retorno["enfirme"]["inffin399b_acttot"] = trim($fin1["acttot"]);
                        $retorno["enfirme"]["inffin399b_pascte"] = trim($fin1["pascte"]);
                        $retorno["enfirme"]["inffin399b_paslar"] = trim($fin1["paslar"]);
                        $retorno["enfirme"]["inffin399b_pastot"] = trim($fin1["pastot"]);
                        $retorno["enfirme"]["inffin399b_patnet"] = trim($fin1["patnet"]);
                        $retorno["enfirme"]["inffin399b_paspat"] = trim($fin1["paspat"]);
                        $retorno["enfirme"]["inffin399b_balsoc"] = trim($fin1["balsoc"]);
                        $retorno["enfirme"]["inffin399b_ingope"] = trim($fin1["ingope"]);
                        $retorno["enfirme"]["inffin399b_ingnoope"] = trim($fin1["ingnoope"]);
                        $retorno["enfirme"]["inffin399b_gasope"] = trim($fin1["gasope"]);
                        $retorno["enfirme"]["inffin399b_gasnoope"] = trim($fin1["gasnoope"]);
                        $retorno["enfirme"]["inffin399b_cosven"] = trim($fin1["cosven"]);
                        $retorno["enfirme"]["inffin399b_utinet"] = trim($fin1["utinet"]);
                        $retorno["enfirme"]["inffin399b_utiope"] = trim($fin1["utiope"]);
                        $retorno["enfirme"]["inffin399b_gasint"] = trim($fin1["gasint"]);
                        $retorno["enfirme"]["inffin399b_gasimp"] = trim($fin1["gasimp"]);
                        $retorno["enfirme"]["inffin399b_indliq"] = trim($fin1["indliq"]);
                        $retorno["enfirme"]["inffin399b_nivend"] = trim($fin1["nivend"]);
                        $retorno["enfirme"]["inffin399b_razcob"] = trim($fin1["razcob"]);
                        $retorno["enfirme"]["inffin399b_renpat"] = trim($fin1["renpat"]);
                        $retorno["enfirme"]["inffin399b_renact"] = trim($fin1["renact"]);
                        $retorno["enfirme"]["inffin399b_gruponiif"] = trim($fin1["gruponiif"]);
                    }
                }
            }

            if (date("Y") == '2022') {
                if ($fin1["firmeza"] == 'S') {
                    if ($fin1["fechacorte"] >= '20210101') {
                        $retorno["enfirme"]["inffin1510_fechacorte"] = $fin1["fechacorte"];
                        $retorno["enfirme"]["inffin1510_actcte"] = trim($fin1["actcte"]);
                        $retorno["enfirme"]["inffin1510_actnocte"] = trim($fin1["actnocte"]);
                        $retorno["enfirme"]["inffin1510_fijnet"] = 0;
                        $retorno["enfirme"]["inffin1510_actotr"] = 0;
                        $retorno["enfirme"]["inffin1510_actval"] = 0;
                        $retorno["enfirme"]["inffin1510_acttot"] = trim($fin1["acttot"]);
                        $retorno["enfirme"]["inffin1510_pascte"] = trim($fin1["pascte"]);
                        $retorno["enfirme"]["inffin1510_paslar"] = trim($fin1["paslar"]);
                        $retorno["enfirme"]["inffin1510_pastot"] = trim($fin1["pastot"]);
                        $retorno["enfirme"]["inffin1510_patnet"] = trim($fin1["patnet"]);
                        $retorno["enfirme"]["inffin1510_paspat"] = trim($fin1["paspat"]);
                        $retorno["enfirme"]["inffin1510_balsoc"] = trim($fin1["balsoc"]);
                        $retorno["enfirme"]["inffin1510_ingope"] = trim($fin1["ingope"]);
                        $retorno["enfirme"]["inffin1510_ingnoope"] = trim($fin1["ingnoope"]);
                        $retorno["enfirme"]["inffin1510_gasope"] = trim($fin1["gasope"]);
                        $retorno["enfirme"]["inffin1510_gasnoope"] = trim($fin1["gasnoope"]);
                        $retorno["enfirme"]["inffin1510_cosven"] = trim($fin1["cosven"]);
                        $retorno["enfirme"]["inffin1510_utinet"] = trim($fin1["utinet"]);
                        $retorno["enfirme"]["inffin1510_utiope"] = trim($fin1["utiope"]);
                        $retorno["enfirme"]["inffin1510_gasint"] = trim($fin1["gasint"]);
                        $retorno["enfirme"]["inffin1510_gasimp"] = trim($fin1["gasimp"]);
                        $retorno["enfirme"]["inffin1510_indliq"] = trim($fin1["indliq"]);
                        $retorno["enfirme"]["inffin1510_nivend"] = trim($fin1["nivend"]);
                        $retorno["enfirme"]["inffin1510_razcob"] = trim($fin1["razcob"]);
                        $retorno["enfirme"]["inffin1510_renpat"] = trim($fin1["renpat"]);
                        $retorno["enfirme"]["inffin1510_renact"] = trim($fin1["renact"]);
                        $retorno["enfirme"]["inffin1510_gruponiif"] = trim($fin1["gruponiif"]);
                    }
                    if ($fin1["fechacorte"] >= '20200101' && $fin1["fechacorte"] <= '20201231') {
                        $retorno["enfirme"]["inffin399a_fechacorte"] = $fin1["fechacorte"];
                        $retorno["enfirme"]["inffin399a_pregrabado"] = 'si';
                        $retorno["enfirme"]["inffin399a_actcte"] = trim($fin1["actcte"]);
                        $retorno["enfirme"]["inffin399a_actnocte"] = trim($fin1["actnocte"]);
                        $retorno["enfirme"]["inffin399a_acttot"] = trim($fin1["acttot"]);
                        $retorno["enfirme"]["inffin399a_pascte"] = trim($fin1["pascte"]);
                        $retorno["enfirme"]["inffin399a_paslar"] = trim($fin1["paslar"]);
                        $retorno["enfirme"]["inffin399a_pastot"] = trim($fin1["pastot"]);
                        $retorno["enfirme"]["inffin399a_patnet"] = trim($fin1["patnet"]);
                        $retorno["enfirme"]["inffin399a_paspat"] = trim($fin1["paspat"]);
                        $retorno["enfirme"]["inffin399a_balsoc"] = trim($fin1["balsoc"]);
                        $retorno["enfirme"]["inffin399a_ingope"] = trim($fin1["ingope"]);
                        $retorno["enfirme"]["inffin399a_ingnoope"] = trim($fin1["ingnoope"]);
                        $retorno["enfirme"]["inffin399a_gasope"] = trim($fin1["gasope"]);
                        $retorno["enfirme"]["inffin399a_gasnoope"] = trim($fin1["gasnoope"]);
                        $retorno["enfirme"]["inffin399a_cosven"] = trim($fin1["cosven"]);
                        $retorno["enfirme"]["inffin399a_utinet"] = trim($fin1["utinet"]);
                        $retorno["enfirme"]["inffin399a_utiope"] = trim($fin1["utiope"]);
                        $retorno["enfirme"]["inffin399a_gasint"] = trim($fin1["gasint"]);
                        $retorno["enfirme"]["inffin399a_gasimp"] = trim($fin1["gasimp"]);
                        $retorno["enfirme"]["inffin399a_indliq"] = trim($fin1["indliq"]);
                        $retorno["enfirme"]["inffin399a_nivend"] = trim($fin1["nivend"]);
                        $retorno["enfirme"]["inffin399a_razcob"] = trim($fin1["razcob"]);
                        $retorno["enfirme"]["inffin399a_renpat"] = trim($fin1["renpat"]);
                        $retorno["enfirme"]["inffin399a_renact"] = trim($fin1["renact"]);
                        $retorno["enfirme"]["inffin399a_gruponiif"] = trim($fin1["gruponiif"]);
                    }
                    if ($fin1["fechacorte"] >= '20190101' && $fin1["fechacorte"] <= '20191231') {
                        $retorno["enfirme"]["inffin399b_fechacorte"] = $fin1["fechacorte"];
                        $retorno["enfirme"]["inffin399b_pregrabado"] = 'si';
                        $retorno["enfirme"]["inffin399b_actcte"] = trim($fin1["actcte"]);
                        $retorno["enfirme"]["inffin399b_actnocte"] = trim($fin1["actnocte"]);
                        $retorno["enfirme"]["inffin399b_acttot"] = trim($fin1["acttot"]);
                        $retorno["enfirme"]["inffin399b_pascte"] = trim($fin1["pascte"]);
                        $retorno["enfirme"]["inffin399b_paslar"] = trim($fin1["paslar"]);
                        $retorno["enfirme"]["inffin399b_pastot"] = trim($fin1["pastot"]);
                        $retorno["enfirme"]["inffin399b_patnet"] = trim($fin1["patnet"]);
                        $retorno["enfirme"]["inffin399b_paspat"] = trim($fin1["paspat"]);
                        $retorno["enfirme"]["inffin399b_balsoc"] = trim($fin1["balsoc"]);
                        $retorno["enfirme"]["inffin399b_ingope"] = trim($fin1["ingope"]);
                        $retorno["enfirme"]["inffin399b_ingnoope"] = trim($fin1["ingnoope"]);
                        $retorno["enfirme"]["inffin399b_gasope"] = trim($fin1["gasope"]);
                        $retorno["enfirme"]["inffin399b_gasnoope"] = trim($fin1["gasnoope"]);
                        $retorno["enfirme"]["inffin399b_cosven"] = trim($fin1["cosven"]);
                        $retorno["enfirme"]["inffin399b_utinet"] = trim($fin1["utinet"]);
                        $retorno["enfirme"]["inffin399b_utiope"] = trim($fin1["utiope"]);
                        $retorno["enfirme"]["inffin399b_gasint"] = trim($fin1["gasint"]);
                        $retorno["enfirme"]["inffin399b_gasimp"] = trim($fin1["gasimp"]);
                        $retorno["enfirme"]["inffin399b_indliq"] = trim($fin1["indliq"]);
                        $retorno["enfirme"]["inffin399b_nivend"] = trim($fin1["nivend"]);
                        $retorno["enfirme"]["inffin399b_razcob"] = trim($fin1["razcob"]);
                        $retorno["enfirme"]["inffin399b_renpat"] = trim($fin1["renpat"]);
                        $retorno["enfirme"]["inffin399b_renact"] = trim($fin1["renact"]);
                        $retorno["enfirme"]["inffin399b_gruponiif"] = trim($fin1["gruponiif"]);
                    }
                }
            }
        }

//
        if (isset($retorno["representanteslegales"])) {
            $retorno["enfirme"]["representanteslegales"] = $retorno["representanteslegales"];
        }
        if (isset($retorno["sitcontrol"])) {
            $retorno["enfirme"]["sitcontrol"] = $retorno["sitcontrol"];
        }
        if (isset($retorno["clasi1510"])) {
            $retorno["enfirme"]["clasi1510"] = $retorno["clasi1510"];
        }
        if (isset($retorno["exp1510"])) {
            $retorno["enfirme"]["exp1510"] = $retorno["exp1510"];
        }

        // 2017-09-20: JINT: Para no invocar dos veces a retornarExpedienteMercantil
        if (isset($retorno["idtipodocperjur"])) {
            $retorno["enfirme"]["idtipodocperjur"] = $retorno["idtipodocperjur"];
        }
        if (isset($retorno["numdocperjur"])) {
            $retorno["enfirme"]["numdocperjur"] = $retorno["numdocperjur"];
        }
        if (isset($retorno["fecdocperjur"])) {
            $retorno["enfirme"]["fecdocperjur"] = $retorno["fecdocperjur"];
        }
        if (isset($retorno["origendocperjur"])) {
            $retorno["enfirme"]["origendocperjur"] = $retorno["origendocperjur"];
        }
        if (isset($retorno["fechaconstitucion"])) {
            $retorno["enfirme"]["fechaconstitucion"] = $retorno["fechaconstitucion"];
        }
        if (isset($retorno["fechavencimiento"])) {
            $retorno["enfirme"]["fechavencimiento"] = $retorno["fechavencimiento"];
        }
        if (isset($retorno["facultades"])) {
            $retorno["enfirme"]["facultades"] = $retorno["facultades"];
        }
        if (isset($retorno["crt0041"])) {
            $retorno["enfirme"]["crt0041"] = '';
        }
        if (isset($retorno["crt1121"])) {
            $retorno["enfirme"]["crt1121"] = '';
        }
        if (isset($retorno["crt0041"])) {
            $retorno["enfirme"]["crt0041"] = $retorno["crt0041"];
        }
        if (isset($retorno["crt1121"])) {
            $retorno["enfirme"]["crt1121"] = $retorno["crt1121"];
        }


        // En caso que la última inscripción no esté en firme
        if ((!isset($arrTemMer) || $arrTemMer === false) && $estadoultimainscripcion != '04') {
            unset($retorno["enfirme"]["tamanoempresa"]);
            unset($retorno["enfirme"]["idtipodocperjur"]);
            unset($retorno["enfirme"]["numdocperjur"]);
            unset($retorno["enfirme"]["fecdocperjur"]);
            unset($retorno["enfirme"]["origendocperjur"]);
            unset($retorno["enfirme"]["fechaconstitucion"]);
            unset($retorno["enfirme"]["fechavencimiento"]);
            unset($retorno["enfirme"]["facultades"]);
            unset($retorno["enfirme"]["representanteslegales"]);
        }
        if ($estadoultimainscripcion != '' && $estadoultimainscripcion != '04') {
            unset($retorno["enfirme"]["sitcontrol"]);
            unset($retorno["enfirme"]["exp1510"]);
            unset($retorno["enfirme"]["inffin1510_fechacorte"]);
            unset($retorno["enfirme"]["inffin1510_actcte"]);
            unset($retorno["enfirme"]["inffin1510_actnocte"]);
            unset($retorno["enfirme"]["inffin1510_fijnet"]);
            unset($retorno["enfirme"]["inffin1510_actotr"]);
            unset($retorno["enfirme"]["inffin1510_actval"]);
            unset($retorno["enfirme"]["inffin1510_acttot"]);
            unset($retorno["enfirme"]["inffin1510_pascte"]);
            unset($retorno["enfirme"]["inffin1510_paslar"]);
            unset($retorno["enfirme"]["inffin1510_pastot"]);
            unset($retorno["enfirme"]["inffin1510_patnet"]);
            unset($retorno["enfirme"]["inffin1510_paspat"]);
            unset($retorno["enfirme"]["inffin1510_balsoc"]);
            unset($retorno["enfirme"]["inffin1510_ingope"]);
            unset($retorno["enfirme"]["inffin1510_ingnoope"]);
            unset($retorno["enfirme"]["inffin1510_gasope"]);
            unset($retorno["enfirme"]["inffin1510_gasnoope"]);
            unset($retorno["enfirme"]["inffin1510_cosven"]);
            unset($retorno["enfirme"]["inffin1510_utinet"]);
            unset($retorno["enfirme"]["inffin1510_utiope"]);
            unset($retorno["enfirme"]["inffin1510_gasint"]);
            unset($retorno["enfirme"]["inffin1510_gasimp"]);
            unset($retorno["enfirme"]["inffin1510_indliq"]);
            unset($retorno["enfirme"]["inffin1510_nivend"]);
            unset($retorno["enfirme"]["inffin1510_razcob"]);
            unset($retorno["enfirme"]["inffin1510_renpat"]);
            unset($retorno["enfirme"]["inffin1510_renact"]);
            unset($retorno["enfirme"]["inffin1510_gruponiif"]);
        }

        // ************************************************************************* //
        // Si el estado del ultimo acto es diferente a en firme (04)
        // Busca las inscripciones que no estan en firme
        // Si el expediente se recupera para una actualización especial solamente
        // recupera la información que está en firme
        // ************************************************************************* //
        if ($tipotramite != 'actualizacionespecial' && $arrTem) {
            if ($estadoultimoacto != '04') {
                $inscnofirme = 0;
                // Busca inscripciones que no estén en firme 
                // Para actualizar la información del proponente
                // Solo tiene en cuenta:
                // - Datos de constitución - si no es comerciante
                // - Facultades - si no es comerciante
                // - Representantes Legales - Si no es comerciante
                // - Financiera
                // - Experiencia - Solo los contratos que hubieren cambiado
                // - Situaciones de control
                $arrTemInsc = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "proponente='" . $arrTem["proponente"] . "' and fecharegistro >= '20160101'", "fecharegistro,horaregistro");
                if ($arrTemInsc && !empty($arrTemInsc)) {
                    foreach ($arrTemInsc as $insc) {
                        if (trim($insc["acto"]) == '01' && ($insc["estado"] == '00' || $insc["estado"] == '01')) {
                            $inscnofirme++;
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["libro"] = $insc["libro"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["registro"] = $insc["registro"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fecharegistro"] = $insc["fecharegistro"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fecpublicacionrue"] = $insc["fecpublicacionrue"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["acto"] = $insc["acto"];
                            $arrXml = \funcionesGenerales::desserializarExpedienteProponente($mysqli, $insc["xml"]);
                            if ($arrTemMer === false) {
                                $retorno["idtipodocperjur"] = $arrXml["idtipodocperjur"];
                                $retorno["numdocperjur"] = $arrXml["numdocperjur"];
                                $retorno["fecdocperjur"] = $arrXml["fecdocperjur"];
                                $retorno["origendocperjur"] = $arrXml["origendocperjur"];
                                $retorno["fechaconstitucion"] = $arrXml["fechaconstitucion"];
                                $retorno["fechavencimiento"] = $arrXml["fechavencimiento"];
                                $retorno["facultades"] = $arrXml["facultades"];
                                $retorno["representanteslegales"] = $arrXml["representanteslegales"];

                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["idtipodocperjur"] = $arrXml["idtipodocperjur"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["numdocperjur"] = $arrXml["numdocperjur"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fecdocperjur"] = $arrXml["fecdocperjur"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["origendocperjur"] = $arrXml["origendocperjur"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fechaconstitucion"] = $arrXml["fechaconstitucion"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fechavencimiento"] = $arrXml["fechavencimiento"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["facultades"] = $arrXml["facultades"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["representanteslegales"] = $arrXml["representanteslegales"];
                            }

                            $retorno["tamanoempresa"] = $arrXml["tamanoempresa"];
                            $retorno["sitcontrol"] = $arrXml["sitcontrol"];
                            $retorno["exp1510"] = $arrXml["exp1510"];

                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["tamanoempresa"] = $arrXml["tamanoempresa"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["sitcontrol"] = $arrXml["sitcontrol"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"] = $arrXml["exp1510"];

                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_fechacorte"] = $arrXml["inffin1510_fechacorte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_actcte"] = $arrXml["inffin1510_actcte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_actnocte"] = $arrXml["inffin1510_actnocte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_fijnet"] = $arrXml["inffin1510_fijnet"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_actotr"] = $arrXml["inffin1510_actotr"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_actval"] = $arrXml["inffin1510_actval"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_acttot"] = $arrXml["inffin1510_acttot"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_pascte"] = $arrXml["inffin1510_pascte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_paslar"] = $arrXml["inffin1510_paslar"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_pastot"] = $arrXml["inffin1510_pastot"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_patnet"] = $arrXml["inffin1510_patnet"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_paspat"] = $arrXml["inffin1510_paspat"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_balsoc"] = $arrXml["inffin1510_balsoc"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_ingope"] = $arrXml["inffin1510_ingope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_ingnoope"] = $arrXml["inffin1510_ingnoope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gasope"] = $arrXml["inffin1510_gasope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gasnoope"] = $arrXml["inffin1510_gasnoope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_cosven"] = $arrXml["inffin1510_cosven"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_utinet"] = $arrXml["inffin1510_utinet"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_utiope"] = $arrXml["inffin1510_utiope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gasint"] = $arrXml["inffin1510_gasint"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gasimp"] = $arrXml["inffin1510_gasimp"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_indliq"] = $arrXml["inffin1510_indliq"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_nivend"] = $arrXml["inffin1510_nivend"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_razcob"] = $arrXml["inffin1510_razcob"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_renpat"] = $arrXml["inffin1510_renpat"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_renact"] = $arrXml["inffin1510_renact"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gruponiif"] = $arrXml["inffin1510_gruponiif"];

                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_fechacorte"] = $arrXml["inffin399a_fechacorte"];
                            if (isset($arrXml["inffin399a_pregrabado"])) {
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_pregrabado"] = $arrXml["inffin399a_pregrabado"];
                            } else {
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_pregrabado"] = '';
                            }
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_actcte"] = $arrXml["inffin399a_actcte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_actnocte"] = $arrXml["inffin399a_actnocte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_acttot"] = $arrXml["inffin399a_acttot"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_pascte"] = $arrXml["inffin399a_pascte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_paslar"] = $arrXml["inffin399a_paslar"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_pastot"] = $arrXml["inffin399a_pastot"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_patnet"] = $arrXml["inffin399a_patnet"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_paspat"] = $arrXml["inffin399a_paspat"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_balsoc"] = $arrXml["inffin399a_balsoc"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_ingope"] = $arrXml["inffin399a_ingope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_ingnoope"] = $arrXml["inffin399a_ingnoope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gasope"] = $arrXml["inffin399a_gasope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gasnoope"] = $arrXml["inffin399a_gasnoope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_cosven"] = $arrXml["inffin399a_cosven"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_utinet"] = $arrXml["inffin399a_utinet"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_utiope"] = $arrXml["inffin399a_utiope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gasint"] = $arrXml["inffin399a_gasint"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gasimp"] = $arrXml["inffin399a_gasimp"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_indliq"] = $arrXml["inffin399a_indliq"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_nivend"] = $arrXml["inffin399a_nivend"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_razcob"] = $arrXml["inffin399a_razcob"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_renpat"] = $arrXml["inffin399a_renpat"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_renact"] = $arrXml["inffin399a_renact"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gruponiif"] = $arrXml["inffin399a_gruponiif"];

                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_fechacorte"] = $arrXml["inffin399b_fechacorte"];
                            if (isset($arrXml["inffin399b_pregrabado"])) {
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_pregrabado"] = $arrXml["inffin399b_pregrabado"];
                            } else {
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_pregrabado"] = '';
                            }
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_actcte"] = $arrXml["inffin399a_actcte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_actnocte"] = $arrXml["inffin399a_actnocte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_acttot"] = $arrXml["inffin399a_acttot"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_pascte"] = $arrXml["inffin399a_pascte"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_paslar"] = $arrXml["inffin399a_paslar"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_pastot"] = $arrXml["inffin399a_pastot"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_patnet"] = $arrXml["inffin399a_patnet"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_paspat"] = $arrXml["inffin399a_paspat"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_balsoc"] = $arrXml["inffin399a_balsoc"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_ingope"] = $arrXml["inffin399a_ingope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_ingnoope"] = $arrXml["inffin399a_ingnoope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gasope"] = $arrXml["inffin399a_gasope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gasnoope"] = $arrXml["inffin399a_gasnoope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_cosven"] = $arrXml["inffin399a_cosven"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_utinet"] = $arrXml["inffin399a_utinet"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_utiope"] = $arrXml["inffin399a_utiope"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gasint"] = $arrXml["inffin399a_gasint"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gasimp"] = $arrXml["inffin399a_gasimp"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_indliq"] = $arrXml["inffin399a_indliq"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_nivend"] = $arrXml["inffin399a_nivend"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_razcob"] = $arrXml["inffin399a_razcob"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_renpat"] = $arrXml["inffin399a_renpat"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_renact"] = $arrXml["inffin399a_renact"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gruponiif"] = $arrXml["inffin399b_gruponiif"];

                            $iExp = 0;
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"] = array();
                            foreach ($arrXml["exp1510"] as $key => $rep) {
                                $expe1 = array();
                                $expe1["secuencia"] = $rep["secuencia"];
                                $expe1["clavecontrato"] = '';
                                $expe1["celebradopor"] = $rep["celebradopor"];
                                $expe1["nombrecontratista"] = $rep["nombrecontratista"];
                                $expe1["nombrecontratante"] = $rep["nombrecontratante"];
                                $expe1["fecejecucion"] = $rep["fecejecucion"];
                                $expe1["valor"] = $rep["valor"];
                                $expe1["porcentaje"] = $rep["porcentaje"];
                                $expe1["clasif"] = array();

                                if (trim($rep["clasificaciones"]) != '') {
                                    $arrx = explode(",", $rep["clasificaciones"]);
                                    $iCla = 0;
                                    $tx = array();
                                    if (!empty($arrx)) {
                                        foreach ($arrx as $x) {
                                            $x = trim($x);
                                            if (!isset($tx[$x])) {
                                                $iCla++;
                                                $expe1["clasif"][$iCla] = trim($x);
                                                $tx[$x] = $x;
                                            }
                                        }
                                    }
                                } else {
                                    if (isset($rep["clasif"]) && !empty($rep["clasif"])) {
                                        $expe1["clasif"] = $rep["clasif"];
                                    }
                                }


                                $iExp++;
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp] = $expe1;
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["soportedeclaracion"] = '';
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["soportecontrato"] = '';
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["generardeclaracion"] = '';
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["revisado"] = '';

                                $retorno["exp1510"][$iExp] = $expe1;
                                $retorno["exp1510"][$iExp]["soportedeclaracion"] = '';
                                $retorno["exp1510"][$iExp]["soportecontrato"] = '';
                                $retorno["exp1510"][$iExp]["generardeclaracion"] = '';
                                $retorno["exp1510"][$iExp]["revisado"] = '';
                                $retorno["exp1510"][$iExp]["modificado"] = 'S';
                            }
                        }

                        if ((trim($insc["acto"]) == '02' || trim($insc["acto"]) == '03' || trim($insc["acto"]) == '36') && ($insc["estado"] == '00' || $insc["estado"] == '01')) {
                            $arrXml["gruposmodificados"] = array();
                            $inscnofirme++;
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'no';
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["libro"] = $insc["libro"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["registro"] = $insc["registro"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fecharegistro"] = $insc["fecharegistro"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fecpublicacionrue"] = $insc["fecpublicacionrue"];
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["acto"] = $insc["acto"];
                            $arrXml = \funcionesGenerales::desserializarExpedienteProponente($mysqli, $insc["xml"]);
                            if ($arrTemMer === false) {

                                if (isset($arrXml["gruposmodificados"]["perjur"]) ||
                                        $retorno["idtipodocperjur"] != $arrXml["idtipodocperjur"] ||
                                        $retorno["numdocperjur"] != $arrXml["numdocperjur"] ||
                                        $retorno["fecdocperjur"] != $arrXml["fecdocperjur"] ||
                                        $retorno["origendocperjur"] != $arrXml["origendocperjur"] ||
                                        $retorno["fechaconstitucion"] != $arrXml["fechaconstitucion"] ||
                                        $retorno["fechavencimiento"] != $arrXml["fechavencimiento"]) {
                                    $arrXml["gruposmodificados"]["perjur"] = "S";
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                    $retorno["idtipodocperjur"] = $arrXml["idtipodocperjur"];
                                    $retorno["numdocperjur"] = $arrXml["numdocperjur"];
                                    $retorno["fecdocperjur"] = $arrXml["fecdocperjur"];
                                    $retorno["origendocperjur"] = $arrXml["origendocperjur"];
                                    $retorno["fechaconstitucion"] = $arrXml["fechaconstitucion"];
                                    $retorno["fechavencimiento"] = $arrXml["fechavencimiento"];

                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["idtipodocperjur"] = $arrXml["idtipodocperjur"];
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["numdocperjur"] = $arrXml["numdocperjur"];
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fecdocperjur"] = $arrXml["fecdocperjur"];
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["origendocperjur"] = $arrXml["origendocperjur"];
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fechaconstitucion"] = $arrXml["fechaconstitucion"];
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["fechavencimiento"] = $arrXml["fechavencimiento"];
                                }


                                if (isset($arrXml["gruposmodificados"]["facultades"]) || $retorno["facultades"] != $arrXml["facultades"]) {
                                    $arrXml["gruposmodificados"]["facultades"] = "S";
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                    $retorno["facultades"] = $arrXml["facultades"];
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["facultades"] = $arrXml["facultades"];
                                }


                                if (isset($arrXml["gruposmodificados"]["repleg"]) || $retorno["representanteslegales"] != $arrXml["representanteslegales"]) {
                                    $arrXml["gruposmodificados"]["repleg"] = "S";
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                    $retorno["representanteslegales"] = $arrXml["representanteslegales"];
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["representanteslegales"] = $arrXml["representanteslegales"];
                                }
                            }


                            if (isset($arrXml["gruposmodificados"]["datosbasicos"]) || $retorno["tamanoempresa"] != $arrXml["tamanoempresa"]) {
                                $arrXml["gruposmodificados"]["datosbasicos"] = "S";
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                $retorno["tamanoempresa"] = $arrXml["tamanoempresa"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["tamanoempresa"] = $arrXml["tamanoempresa"];
                            }


                            if (isset($arrXml["gruposmodificados"]["sitcontrol"]) || $retorno["sitcontrol"] != $arrXml["sitcontrol"]) {
                                $arrXml["gruposmodificados"]["sitcontrol"] = "S";
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                $retorno["sitcontrol"] = $arrXml["sitcontrol"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["sitcontrol"] = $arrXml["sitcontrol"];
                            }


                            if (isset($arrXml["gruposmodificados"]["inffin1510"]) || $insc["acto"] == '02') {
                                $arrXml["gruposmodificados"]["inffin1510"] = "S";
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_fechacorte"] = $arrXml["inffin1510_fechacorte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_actcte"] = $arrXml["inffin1510_actcte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_actnocte"] = $arrXml["inffin1510_actnocte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_fijnet"] = $arrXml["inffin1510_fijnet"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_actotr"] = $arrXml["inffin1510_actotr"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_actval"] = $arrXml["inffin1510_actval"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_acttot"] = $arrXml["inffin1510_acttot"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_pascte"] = $arrXml["inffin1510_pascte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_paslar"] = $arrXml["inffin1510_paslar"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_pastot"] = $arrXml["inffin1510_pastot"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_patnet"] = $arrXml["inffin1510_patnet"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_paspat"] = $arrXml["inffin1510_paspat"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_balsoc"] = $arrXml["inffin1510_balsoc"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_ingope"] = $arrXml["inffin1510_ingope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_ingnoope"] = $arrXml["inffin1510_ingnoope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gasope"] = $arrXml["inffin1510_gasope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gasnoope"] = $arrXml["inffin1510_gasnoope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_cosven"] = $arrXml["inffin1510_cosven"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_utinet"] = $arrXml["inffin1510_utinet"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_utiope"] = $arrXml["inffin1510_utiope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gasint"] = $arrXml["inffin1510_gasint"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gasimp"] = $arrXml["inffin1510_gasimp"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_indliq"] = $arrXml["inffin1510_indliq"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_nivend"] = $arrXml["inffin1510_nivend"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_razcob"] = $arrXml["inffin1510_razcob"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_renpat"] = $arrXml["inffin1510_renpat"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_renact"] = $arrXml["inffin1510_renact"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin1510_gruponiif"] = $arrXml["inffin1510_gruponiif"];
                            }

                            if (isset($arrXml["gruposmodificados"]["inffin399a"]) || $insc["acto"] == '02' || $insc["acto"] == '36') {
                                $arrXml["gruposmodificados"]["inffin399a"] = "S";
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_fechacorte"] = $arrXml["inffin399a_fechacorte"];
                                if (isset($arrXml["inffin399a_pregrabado"])) {
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_pregrabado"] = $arrXml["inffin399a_pregrabado"];
                                } else {
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_pregrabado"] = '';
                                }
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_actcte"] = $arrXml["inffin399a_actcte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_actnocte"] = $arrXml["inffin399a_actnocte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_acttot"] = $arrXml["inffin399a_acttot"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_pascte"] = $arrXml["inffin399a_pascte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_paslar"] = $arrXml["inffin399a_paslar"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_pastot"] = $arrXml["inffin399a_pastot"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_patnet"] = $arrXml["inffin399a_patnet"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_paspat"] = $arrXml["inffin399a_paspat"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_balsoc"] = $arrXml["inffin399a_balsoc"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_ingope"] = $arrXml["inffin399a_ingope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_ingnoope"] = $arrXml["inffin399a_ingnoope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gasope"] = $arrXml["inffin399a_gasope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gasnoope"] = $arrXml["inffin399a_gasnoope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_cosven"] = $arrXml["inffin399a_cosven"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_utinet"] = $arrXml["inffin399a_utinet"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_utiope"] = $arrXml["inffin399a_utiope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gasint"] = $arrXml["inffin399a_gasint"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gasimp"] = $arrXml["inffin399a_gasimp"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_indliq"] = $arrXml["inffin399a_indliq"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_nivend"] = $arrXml["inffin399a_nivend"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_razcob"] = $arrXml["inffin399a_razcob"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_renpat"] = $arrXml["inffin399a_renpat"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_renact"] = $arrXml["inffin399a_renact"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399a_gruponiif"] = $arrXml["inffin399a_gruponiif"];
                            }

                            if (isset($arrXml["gruposmodificados"]["inffin399b"]) || $insc["acto"] == '02' || $insc["acto"] == '36') {
                                $arrXml["gruposmodificados"]["inffin399b"] = "S";
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_fechacorte"] = $arrXml["inffin399b_fechacorte"];
                                if (isset($arrXml["inffin399b_pregrabado"])) {
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_pregrabado"] = $arrXml["inffin399b_pregrabado"];
                                } else {
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_pregrabado"] = '';
                                }
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_actcte"] = $arrXml["inffin399b_actcte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_actnocte"] = $arrXml["inffin399b_actnocte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_acttot"] = $arrXml["inffin399b_acttot"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_pascte"] = $arrXml["inffin399b_pascte"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_paslar"] = $arrXml["inffin399b_paslar"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_pastot"] = $arrXml["inffin399b_pastot"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_patnet"] = $arrXml["inffin399b_patnet"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_paspat"] = $arrXml["inffin399b_paspat"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_balsoc"] = $arrXml["inffin399b_balsoc"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_ingope"] = $arrXml["inffin399b_ingope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_ingnoope"] = $arrXml["inffin399b_ingnoope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gasope"] = $arrXml["inffin399b_gasope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gasnoope"] = $arrXml["inffin399b_gasnoope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_cosven"] = $arrXml["inffin399b_cosven"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_utinet"] = $arrXml["inffin399b_utinet"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_utiope"] = $arrXml["inffin399b_utiope"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gasint"] = $arrXml["inffin399b_gasint"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gasimp"] = $arrXml["inffin399b_gasimp"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_indliq"] = $arrXml["inffin399b_indliq"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_nivend"] = $arrXml["inffin399b_nivend"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_razcob"] = $arrXml["inffin399b_razcob"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_renpat"] = $arrXml["inffin399b_renpat"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_renact"] = $arrXml["inffin399b_renact"];
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["inffin399b_gruponiif"] = $arrXml["inffin399b_gruponiif"];
                            }

                            $indTotal = 0;
                            foreach ($retorno["exp1510"] as $ind => $repf) {
                                $indTotal = $ind;
                            }

                            $iExp = 0;
                            $mod = '';
                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"] = array();
                            foreach ($arrXml["exp1510"] as $key => $rep) {

                                $expe1 = array();
                                $expe1["secuencia"] = $rep["secuencia"];
                                $expe1["clavecontrato"] = '';
                                $expe1["celebradopor"] = $rep["celebradopor"];
                                $expe1["nombrecontratista"] = $rep["nombrecontratista"];
                                $expe1["nombrecontratante"] = $rep["nombrecontratante"];
                                $expe1["fecejecucion"] = $rep["fecejecucion"];
                                $expe1["valor"] = $rep["valor"];
                                $expe1["porcentaje"] = $rep["porcentaje"];
                                $expe1["clasif"] = $rep["clasif"];

//
                                $encontro = '';
                                foreach ($retorno["exp1510"] as $ind => $repf) {
                                    if ($repf["secuencia"] == $rep["secuencia"]) {
                                        $encontro = 'si';

                                        if ($repf["celebradopor"] != $expe1["celebradopor"] ||
                                                $repf["nombrecontratista"] != $expe1["nombrecontratista"] ||
                                                $repf["nombrecontratante"] != $expe1["nombrecontratante"] ||
                                                $repf["fecejecucion"] != $expe1["fecejecucion"] ||
                                                $repf["valor"] != $expe1["valor"] ||
                                                $repf["porcentaje"] != $expe1["porcentaje"] ||
                                                $repf["clasif"] != $expe1["clasif"]) {
                                            $mod = 'si';
                                            $iExp++;
                                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp] = $expe1;
                                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["soportedeclaracion"] = '';
                                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["soportecontrato"] = '';
                                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["generardeclaracion"] = '';
                                            $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["revisado"] = '';

                                            $retorno["exp1510"][$ind] = $expe1;
                                            $retorno["exp1510"][$ind]["soportedeclaracion"] = '';
                                            $retorno["exp1510"][$ind]["soportecontrato"] = '';
                                            $retorno["exp1510"][$ind]["generardeclaracion"] = '';
                                            $retorno["exp1510"][$ind]["revisado"] = '';
                                            $retorno["exp1510"][$ind]["modificado"] = 'S';
                                        } else {
                                            $retorno["exp1510"][$ind]["soportedeclaracion"] = '';
                                            $retorno["exp1510"][$ind]["soportecontrato"] = '';
                                            $retorno["exp1510"][$ind]["generardeclaracion"] = '';
                                            $retorno["exp1510"][$ind]["revisado"] = '';
                                            $retorno["exp1510"][$ind]["modificado"] = 'N';
                                        }
                                    }
                                }
                                if ($encontro == '') {
                                    $mod = 'si';
                                    $indTotal++;
                                    $retorno["exp1510"][$indTotal] = $expe1;
                                    $retorno["exp1510"][$indTotal]["soportedeclaracion"] = '';
                                    $retorno["exp1510"][$indTotal]["soportecontrato"] = '';
                                    $retorno["exp1510"][$indTotal]["generardeclaracion"] = '';
                                    $retorno["exp1510"][$indTotal]["revisado"] = '';
                                    $retorno["exp1510"][$indTotal]["modificado"] = 'S';

                                    $iExp++;
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp] = $expe1;
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["soportedeclaracion"] = '';
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["soportecontrato"] = '';
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["generardeclaracion"] = '';
                                    $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["exp1510"][$iExp]["revisado"] = '';
                                }
                            }

                            if ($mod == 'si') {
                                $retorno["nofirme" . sprintf("%02s", $inscnofirme)]["datosmodificados"] = 'si';
                                $arrXml["gruposmodificados"]["exp1510"] = 'S';
                            } else {
                                $arrXml["gruposmodificados"]["exp1510"] = 'N';
                            }
                            foreach ($retorno["exp1510"] as $ind => $repf) {
                                if (!isset($repf["modificado"])) {
                                    $arrXml["gruposmodificados"]["exp1510-" . $repf["secuencia"]] = 'E';
                                    unset($retorno["exp1510"]["ind"]);
                                } else {
                                    if ($repf["modificado"] == 'S') {
                                        $arrXml["gruposmodificados"]["exp1510-" . $repf["secuencia"]] = 'S';
                                    } else {
                                        $arrXml["gruposmodificados"]["exp1510-" . $repf["secuencia"]] = 'N';
                                    }
                                }
                            }
                        }
                    }
                }
                // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_inscripciones_proponentes');
            }
        }

        // ***************************************************************************** //
        // ARMA CONTRATOS MULTAS Y SANCIONES
        // ***************************************************************************** //
        // Busca contratos
        if ($retornarRee == 'si') {
            $i = 0;
            if ($arrTem) {
                $arrTemCer = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_contratos', "proponente='" . $arrTem["proponente"] . "'", "secuencia");
                foreach ($arrTemCer as $c) {
                    if ($c["estadocont"] != '9') {
                        $i++;
                        $retorno["contratos"][$i]["codcamara"] = $c["codcamara"];
                        $retorno["contratos"][$i]["numradic"] = $c["numradic"];
                        $retorno["contratos"][$i]["fecradic"] = $c["fecradic"];
                        $retorno["contratos"][$i]["horradic"] = $c["horradic"];
                        $retorno["contratos"][$i]["fecreporte"] = $c["fecreporte"];
                        $retorno["contratos"][$i]["nitentidad"] = $c["nitentidad"];
                        $retorno["contratos"][$i]["nombreentidad"] = \funcionesGenerales::restaurarEspeciales($c["nombreentidad"]);
                        $retorno["contratos"][$i]["idmunientidad"] = $c["idmunientidad"];
                        $retorno["contratos"][$i]["divarea"] = \funcionesGenerales::restaurarEspeciales($c["divarea"]);
                        $retorno["contratos"][$i]["idefun"] = $c["idefun"];
                        $retorno["contratos"][$i]["nomfun"] = \funcionesGenerales::restaurarEspeciales($c["nomfun"]);
                        $retorno["contratos"][$i]["carfun"] = \funcionesGenerales::restaurarEspeciales($c["carfun"]);
                        $retorno["contratos"][$i]["numcontrato"] = $c["numcontrato"];
                        $retorno["contratos"][$i]["numcontratosecop"] = $c["numcontratosecop"];
                        $retorno["contratos"][$i]["fechaadj"] = $c["fechaadj"];
                        $retorno["contratos"][$i]["fechaper"] = $c["fechaper"];
                        $retorno["contratos"][$i]["fechaini"] = $c["fechaini"];
                        $retorno["contratos"][$i]["fechater"] = $c["fechater"];
                        $retorno["contratos"][$i]["fechaeje"] = $c["fechaeje"];
                        $retorno["contratos"][$i]["fechaliq"] = $c["fechaliq"];
                        $retorno["contratos"][$i]["codigoact"] = $c["codigoact"];
                        $retorno["contratos"][$i]["ciiu1"] = $c["ciiu1"];
                        $retorno["contratos"][$i]["ciiu2"] = $c["ciiu2"];
                        $retorno["contratos"][$i]["ciiu3"] = $c["ciiu3"];
                        $retorno["contratos"][$i]["ciiu4"] = $c["ciiu4"];
                        $retorno["contratos"][$i]["tipocont"] = $c["tipocont"];
                        $retorno["contratos"][$i]["valorcont"] = $c["valorcont"];
                        $retorno["contratos"][$i]["valorcontpag"] = $c["valorcontpag"];
                        $retorno["contratos"][$i]["indcump"] = $c["indcump"];
                        $retorno["contratos"][$i]["estadocont"] = $c["estadocont"];
                        $retorno["contratos"][$i]["contratorelacionadoconstruccion"] = $c["contratorelacionadoconstruccion"];
                        $retorno["contratos"][$i]["motivoter"] = \funcionesGenerales::restaurarEspeciales($c["motivoter"]);
                        $retorno["contratos"][$i]["fechaterant"] = $c["fechaterant"];
                        $retorno["contratos"][$i]["motivoces"] = \funcionesGenerales::restaurarEspeciales($c["motivoces"]);
                        $retorno["contratos"][$i]["fechaces"] = $c["fechaces"];
                        $retorno["contratos"][$i]["observaciones"] = \funcionesGenerales::restaurarEspeciales($c["observaciones"]);
                        $retorno["contratos"][$i]["numlibro"] = $c["numlibro"];
                        $retorno["contratos"][$i]["numreglib"] = $c["numreglib"];
                        $retorno["contratos"][$i]["fecreglib"] = $c["fecreglib"];
                        $retorno["contratos"][$i]["clasificaciones"] = array();
                        $retorno["contratos"][$i]["unspsc"] = array();
                        if (trim($c["clasificaciones"]) != '') {
                            $iCla = 0;
                            $arrx = explode(",", $c["clasificaciones"]);
                            foreach ($arrx as $x) {
                                $iCla++;
                                $retorno["contratos"][$i]["clasificaciones"][$iCla] = $x;
                            }
                        }
                        if (trim($c["unspsc"]) != '') {
                            $iCla = 0;
                            $arrx = explode(",", $c["unspsc"]);
                            foreach ($arrx as $x) {
                                $iCla++;
                                $retorno["contratos"][$i]["unspsc"][$iCla] = $x;
                            }
                        }
                        $retorno["contratos"][$i]["objeto"] = \funcionesGenerales::restaurarEspeciales($c["objeto"]);
                        $retorno["contratos"][$i]["codigocamaraorigen"] = $c["codigocamaraorigen"];
                        $retorno["contratos"][$i]["numregistrocamaraorigen"] = $c["numregistrocamaraorigen"];
                        $retorno["contratos"][$i]["fecregistrocamaraorigen"] = $c["fecregistrocamaraorigen"];
                        $retorno["contratos"][$i]["indicadorenvio"] = '0';
                    }
                }
                // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_proponentes_contratos');
                // Busca multas
                $i = 0;
                $arrTemCer = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_multas', "proponente='" . $arrTem["proponente"] . "'", "secuencia");
                foreach ($arrTemCer as $c) {
                    $i++;
                    $retorno["multas"][$i]["codcamara"] = $c["codcamara"];
                    $retorno["multas"][$i]["numradic"] = $c["numradic"];
                    $retorno["multas"][$i]["fecradic"] = $c["fecradic"];
                    $retorno["multas"][$i]["horradic"] = $c["horradic"];
                    $retorno["multas"][$i]["fecreporte"] = $c["fecreporte"];
                    $retorno["multas"][$i]["nitentidad"] = $c["nitentidad"];
                    $retorno["multas"][$i]["nombreentidad"] = \funcionesGenerales::restaurarEspeciales($c["nombreentidad"]);
                    $retorno["multas"][$i]["idmunientidad"] = $c["idmunientidad"];
                    $retorno["multas"][$i]["divarea"] = \funcionesGenerales::restaurarEspeciales($c["divarea"]);
                    $retorno["multas"][$i]["idefun"] = $c["idefun"];
                    $retorno["multas"][$i]["nomfun"] = \funcionesGenerales::restaurarEspeciales($c["nomfun"]);
                    $retorno["multas"][$i]["carfun"] = \funcionesGenerales::restaurarEspeciales($c["carfun"]);
                    $retorno["multas"][$i]["numcontrato"] = $c["numcontrato"];
                    $retorno["multas"][$i]["numcontratosecop"] = $c["numcontratosecop"];
                    $retorno["multas"][$i]["numacto"] = $c["numacto"];
                    $retorno["multas"][$i]["fechaacto"] = $c["fechaacto"];
                    $retorno["multas"][$i]["fechaeje"] = $c["fechaeje"];
                    $retorno["multas"][$i]["valormult"] = $c["valormult"];
                    $retorno["multas"][$i]["valormultpag"] = $c["valormultpag"];
                    $retorno["multas"][$i]["numsus"] = $c["numsus"];
                    $retorno["multas"][$i]["fechasus"] = $c["fechasus"];
                    $retorno["multas"][$i]["numconf"] = $c["numconf"];
                    $retorno["multas"][$i]["fechaconf"] = $c["fechaconf"];
                    $retorno["multas"][$i]["estadomult"] = $c["estadomult"];
                    $retorno["multas"][$i]["numrev"] = $c["numrev"];
                    $retorno["multas"][$i]["fechanumrev"] = $c["fechanumrev"];
                    $retorno["multas"][$i]["numlibro"] = $c["numlibro"];
                    $retorno["multas"][$i]["numreglib"] = $c["numreglib"];
                    $retorno["multas"][$i]["fecreglib"] = $c["fecreglib"];
                    $retorno["multas"][$i]["codigocamaraorigen"] = $c["codigocamaraorigen"];
                    $retorno["multas"][$i]["numregistrocamaraorigen"] = $c["numregistrocamaraorigen"];
                    $retorno["multas"][$i]["fecregistrocamaraorigen"] = $c["fecregistrocamaraorigen"];
                    $retorno["multas"][$i]["indicadorenvio"] = '0';
                    $retorno["multas"][$i]["observaciones"] = '';
                }
                // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_proponentes_multas');
                // Busca sanciones
                $i = 0;
                $arrTemCer = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_sanciones', "proponente='" . $arrTem["proponente"] . "'", "secuencia");
                foreach ($arrTemCer as $c) {
                    $i++;
                    $retorno["sanciones"][$i]["codcamara"] = $c["codcamara"];
                    $retorno["sanciones"][$i]["numradic"] = $c["numradic"];
                    $retorno["sanciones"][$i]["fecradic"] = $c["fecradic"];
                    $retorno["sanciones"][$i]["horradic"] = $c["horradic"];
                    $retorno["sanciones"][$i]["fecreporte"] = $c["fecreporte"];
                    $retorno["sanciones"][$i]["nitentidad"] = $c["nitentidad"];
                    $retorno["sanciones"][$i]["nombreentidad"] = \funcionesGenerales::restaurarEspeciales($c["nombreentidad"]);
                    $retorno["sanciones"][$i]["idmunientidad"] = $c["idmunientidad"];
                    $retorno["sanciones"][$i]["divarea"] = \funcionesGenerales::restaurarEspeciales($c["divarea"]);
                    $retorno["sanciones"][$i]["idefun"] = $c["idefun"];
                    $retorno["sanciones"][$i]["nomfun"] = \funcionesGenerales::restaurarEspeciales($c["nomfun"]);
                    $retorno["sanciones"][$i]["carfun"] = \funcionesGenerales::restaurarEspeciales($c["carfun"]);
                    $retorno["sanciones"][$i]["numcontrato"] = $c["numcontrato"];
                    $retorno["sanciones"][$i]["numcontratosecop"] = $c["numcontratosecop"];
                    $retorno["sanciones"][$i]["numacto"] = $c["numacto"];
                    $retorno["sanciones"][$i]["fechaacto"] = $c["fechaacto"];
                    $retorno["sanciones"][$i]["fechaeje"] = $c["fechaeje"];
                    $retorno["sanciones"][$i]["numsus"] = $c["numsus"];
                    $retorno["sanciones"][$i]["fechasus"] = $c["fechasus"];
                    $retorno["sanciones"][$i]["numconf"] = $c["numconf"];
                    $retorno["sanciones"][$i]["fechaconf"] = $c["fechaconf"];
                    $retorno["sanciones"][$i]["condinc"] = $c["condinc"];
                    $retorno["sanciones"][$i]["contratorelacionadoconstruccion"] = $c["contratorelacionadoconstruccion"];
                    $retorno["sanciones"][$i]["incumplimientoviviendainteressocial"] = $c["incumplimientoviviendainteressocial"];
                    $retorno["sanciones"][$i]["cumsanc"] = $c["cumsanc"];
                    $retorno["sanciones"][$i]["estadosanc"] = $c["estadosanc"];
                    $retorno["sanciones"][$i]["numrev"] = $c["numrev"];
                    $retorno["sanciones"][$i]["fechanumrev"] = $c["fechanumrev"];
                    $retorno["sanciones"][$i]["descripcion"] = \funcionesGenerales::restaurarEspeciales($c["descripcion"]);
                    $retorno["sanciones"][$i]["fundamento"] = \funcionesGenerales::restaurarEspeciales($c["fundamento"]);
                    $retorno["sanciones"][$i]["vigencia"] = $c["vigencia"];
                    $retorno["sanciones"][$i]["numlibro"] = $c["numlibro"];
                    $retorno["sanciones"][$i]["numreglib"] = $c["numreglib"];
                    $retorno["sanciones"][$i]["fecreglib"] = $c["fecreglib"];
                    $retorno["sanciones"][$i]["codigocamaraorigen"] = $c["codigocamaraorigen"];
                    $retorno["sanciones"][$i]["numregistrocamaraorigen"] = $c["numregistrocamaraorigen"];
                    $retorno["sanciones"][$i]["fecregistrocamaraorigen"] = $c["fecregistrocamaraorigen"];
                    $retorno["sanciones"][$i]["indicadorenvio"] = '0';
                    $retorno["sanciones"][$i]["observaciones"] = '';
                }
                // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_proponentes_sanciones');
                // Busca sanciones disciplinarias
                $i = 0;
                $arrTemCer = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_sandis', "proponente='" . $arrTem["proponente"] . "'", "secuencia");
                foreach ($arrTemCer as $c) {
                    $i++;
                    $retorno["sandis"][$i]["codcamara"] = $c["codcamara"];
                    $retorno["sandis"][$i]["numradic"] = $c["numradic"];
                    $retorno["sandis"][$i]["fecradic"] = $c["fecradic"];
                    $retorno["sandis"][$i]["horradic"] = $c["horradic"];
                    $retorno["sandis"][$i]["fecreporte"] = $c["fecreporte"];
                    $retorno["sandis"][$i]["nitentidad"] = $c["nitentidad"];
                    $retorno["sandis"][$i]["nombreentidad"] = \funcionesGenerales::restaurarEspeciales($c["nombreentidad"]);
                    $retorno["sandis"][$i]["idmunientidad"] = $c["idmunientidad"];
                    $retorno["sandis"][$i]["divarea"] = \funcionesGenerales::restaurarEspeciales($c["divarea"]);
                    $retorno["sandis"][$i]["idefun"] = $c["idefun"];
                    $retorno["sandis"][$i]["nomfun"] = \funcionesGenerales::restaurarEspeciales($c["nomfun"]);
                    $retorno["sandis"][$i]["carfun"] = \funcionesGenerales::restaurarEspeciales($c["carfun"]);
                    $retorno["sandis"][$i]["numcontratosecop"] = $c["numcontratosecop"];
                    $retorno["sandis"][$i]["numacto"] = $c["numacto"];
                    $retorno["sandis"][$i]["fechaacto"] = $c["fechaacto"];
                    $retorno["sandis"][$i]["fechaeje"] = $c["fechaeje"];
                    $retorno["sandis"][$i]["numsus"] = $c["numsus"];
                    $retorno["sandis"][$i]["fechasus"] = $c["fechasus"];
                    $retorno["sandis"][$i]["numconf"] = $c["numconf"];
                    $retorno["sandis"][$i]["fechaconf"] = $c["fechaconf"];
                    $retorno["sandis"][$i]["condinc"] = $c["condinc"];
                    $retorno["sandis"][$i]["cumsanc"] = $c["cumsanc"];
                    $retorno["sandis"][$i]["estadosanc"] = $c["estadosanc"];
                    $retorno["sandis"][$i]["numrev"] = $c["numrev"];
                    $retorno["sandis"][$i]["fechanumrev"] = $c["fechanumrev"];
                    $retorno["sandis"][$i]["descripcion"] = \funcionesGenerales::restaurarEspeciales($c["descripcion"]);
                    $retorno["sandis"][$i]["fundamento"] = \funcionesGenerales::restaurarEspeciales($c["fundamento"]);
                    $retorno["sandis"][$i]["vigencia"] = $c["vigencia"];
                    $retorno["sandis"][$i]["numlibro"] = $c["numlibro"];
                    $retorno["sandis"][$i]["numreglib"] = $c["numreglib"];
                    $retorno["sandis"][$i]["fecreglib"] = $c["fecreglib"];
                    $retorno["sandis"][$i]["codigocamaraorigen"] = $c["codigocamaraorigen"];
                    $retorno["sandis"][$i]["numregistrocamaraorigen"] = $c["numregistrocamaraorigen"];
                    $retorno["sandis"][$i]["fecregistrocamaraorigen"] = $c["fecregistrocamaraorigen"];
                    $retorno["sandis"][$i]["indicadorenvio"] = '0';
                    $retorno["sandis"][$i]["observaciones"] = '';
                }
                // \logApi::general2($nameLog, $prop, 'Leyo  mreg_est_proponentes_sandis');
            }
        }

        if (!isset($retorno["nit"])) {
            $retorno["nit"] = '';
        }
        if (!isset($retorno["proponente"])) {
            $retorno["proponente"] = '';
        }
        if (!isset($retorno["nombre"])) {
            $retorno["nombre"] = '';
        }

        // Certificas que se han grabado en el SII
        $retorno["crtsii"] = array();
        if (ltrim((string) $retorno["proponente"], "0") != '') {
            $arrTemC = retornarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', "registro='REGPRO' and expediente='" . ltrim($retorno["proponente"], "0") . "'", "idcertifica,id");
            if ($arrTemC && !empty($arrTemC)) {
                foreach ($arrTemC as $tx) {
                    if (trim($tx["contenido"]) != '') {
                        if (!isset($retorno["crtsii"][$tx["idcertifica"]])) {
                            $retorno["crtsii"][$tx["idcertifica"]] = '';
                        }
                        $txt1 = \funcionesGenerales::reemplazarAcutes(\funcionesGenerales::restaurarEspeciales(trim(str_replace("&rdquo;", '"', $tx["contenido"]))));
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        $retorno["crtsii"][$tx["idcertifica"]] .= $txt1;
                    }
                }
            }
            unset($arrTemC);
            // \logApi::general2($nameLog, $prop, 'Leyo  mreg_certificas_sii');
        }

        // Busca inhabilidad por incumplimiento reiterado
        // arreglo
        // $retorno["inhabilidad"]["inhabilidad"] = 'no/si';
        // $retorno["inhabilidad"]["ano"] = 'Año para el cual se detrermina la inhabilidad';
        // $retorno["inhabilidad"]["multas"] = array de multas
        // $retorno["inhabilidad"]["sanciones"] = array de sanciones
        // $retorno["inhabilidad"]["sinindicador"] = array de sanciones sin indicador
        // $retorno["inhabilidad"]["texto"] = array - texto en líneas de 65 para sirep
        // $retorno["inhabilidad"]["textosii"] = array - [0][1][2][3][4]

        if ($retornarInhabilidad != 'no') {
            if (ltrim((string) $retorno["proponente"], "0") != '') {
                $retorno["inhabilidad"] = \funcionesRues::validarInhabilidad($retorno["nit"], $retorno["proponente"], $retorno["nombre"], "CALCULO EN LINEA DE LA INHABILIDAD");
                // \logApi::general2($nameLog, $prop, 'Ejecuto validarInhabilidad');
            }
        }

        $retorno["imagenes"] = array();
        $imgs = array();
        if (ltrim((string) $retorno["proponente"], "0") != '') {

            // \logApi::general2($nameLog, $retorno["matricula"], 'Entró buscar imagenes: ' . date("His"));
            $imgs = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "proponente='" . ltrim($retorno["proponente"], "0") . "'", "idanexo");

            // \logApi::general2($nameLog, $retorno["matricula"], 'Salió de buscar imagenes: ' . date("His"));
            if ($imgs) {
                if (!empty($imgs)) {
                    $i = 0;
                    foreach ($imgs as $img) {
                        if ($img["tipoanexo"] != '509' && $img["eliminado"] != 'SI') {
                            $obs = '';
                            $fechamostrar = '';
                            $estadocb = '';
                            $libro = '';
                            $acto = '';
                            $registro = '';
                            $fecharad = '';
                            $fechareg = '';

                            //
                            if ($img["idtipodoc"] != '') {
                                $btd = retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "idtipodoc='" . $img["idtipodoc"] . "'");
                                if ($btd && !empty($btd)) {
                                    $obs .= '<strong>Tipo documental: </strong>' . $img["idtipodoc"] . ' - ' . $btd["nombre"] . '<br>';
                                }
                                if ($img["numdoc"] != '') {
                                    $obs .= '<strong>Número del documento: </strong>' . $img["numdoc"] . '<br>';
                                }
                                if ($img["fechadoc"] != '') {
                                    $obs .= '<strong>Fecha del documento: </strong>' . $img["fechadoc"] . '<br>';
                                }
                                
                            }
                            if ($img["observaciones"] != '') {
                                $obs .= '<strong>Observaciones: </strong>' . $img["observaciones"];
                                if ($img["identificador"] != '') {
                                    $obs .= '<strong>, Identificador: </strong>' . $img["identificador"];
                                }
                                $obs .= '<br>';
                            }

                            if ($img["idradicacion"] != '') {
                                $cb = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $img["idradicacion"] . "'");
                                $cbl = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_libros', "codigobarras='" . $img["idradicacion"] . "'", "id");
                                if ($cb && !empty($cb)) {
                                    $obs .= '<strong>Fecha radicación: </strong>' . $cb["fecharadicacion"] . '<br>';
                                    $fecharad = $cb["fecharadicacion"];
                                    $fechamostrar = $cb["fecharadicacion"];
                                }
                                if ($cbl && !empty($cbl)) {
                                    foreach ($cbl as $cbls) {
                                        $libro = 'RP01';
                                        $registro = $cbls["registro"];
                                        $estadocb = 'S';
                                    }
                                }
                            } else {
                                if ($img["registro"] != '') {
                                    $libro = 'RP01';
                                    $registro = $img["registro"];
                                    $estadocb = 'S';
                                }
                            }
                            if ($libro != '' && $registro != '') {
                                $rlib = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "libro='" . $libro . "' and registro='" . $registro . "'", "libro,registro,fecharegistro,acto,texto");
                                if ($rlib && !empty($rlib)) {
                                    $fechareg = $rlib["fecharegistro"];
                                    $fechamostrar = $cb["fecharadicacion"];
                                    $acto = $rlib["acto"];
                                    $obs .= '<strong>Inscripción No. </strong>' . $rlib["libro"] . '-' . $rlib["registro"] . '<br>';
                                    $obs .= '<strong>Fecha registro: </strong>' . $rlib["fecharegistro"] . '<br>';
                                    $obs .= '<strong>Noticia : </strong>' . $rlib["texto"];
                                }
                            }

                            if ($incluir == 'todos' ||
                                    ($incluir == 'inscritos' && $registro != '')) {
                                $i++;
                                $retorno["imagenes"][$i] = $img;
                                $retorno["imagenes"][$i]["bandeja"] = '6.-REGPRO';
                                $retorno["imagenes"][$i]["obs"] = $obs;
                                $retorno["imagenes"][$i]["fmostrar"] = $fechamostrar;
                                $retorno["imagenes"][$i]["estadocb"] = $estadocb;
                            }
                        }
                    }
                    $retorno["imagenes"] = \funcionesGenerales::ordenarMatriz($retorno["imagenes"], "fmostrar", true);
                }
            }
        }

        return $retorno;
    }

}

?>
