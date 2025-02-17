<?php

class funcionesRegistrales_actualizarMregEstInscritos {

    /**
     * 
     * @param type $mysqli
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @param type $altoimpacto
     * @param type $crear
     * @param type $arregloServiciosMatricula
     * @param type $arregloServiciosRenovacion
     * @param type $arregloServicosAfiliacion
     * @return bool
     */
    public static function actualizarMregEstInscritos($mysqli = null, $data = array(), $codbarras = '', $tt = '', $rec = '', $altoimpacto = 'no', $crear = 'si', $arregloServiciosMatricula = array(), $arregloServiciosRenovacion = array(), $arregloServicosAfiliacion = array()) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        //
        $nameLog = 'actualizarMregEstInscritos_' . date("Ymd");

        // Valida que el expediente tenga diferencias
        if (ltrim($data["matricula"], "0") == '' && ltrim($data["proponente"], "0") == '') {
            return true;
        }

        //
        $cerrarmysql = 'no';
        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarmysql = 'si';
        }

        // JINT: 20240316 - Asegurarese que se almacena la fechxa de renovación correcta.
        if ($data["matricula"] != '') {
            $histo = encontrarHistoricoPagosMysqliApi($mysqli, $data["matricula"], $arregloServiciosRenovacion, $arregloServicosAfiliacion, $arregloServiciosMatricula);
            if ($histo["fecultren"] != '') {
                $data["fecharenovacion"] = $histo["fecultren"];
                $data["ultanoren"] = $histo["ultanoren"];
            }
        }

        //
        if (!isset($data["nombreanterior"])) {
            $data["nombreanterior"] = '';
        }

        //
        // if (!isset($data["nombrebase64"]) || $data["nombrebase64"] == '') {
        $data["nombrebase64"] = base64_encode($data["nombre"]);
        // }
        // if (!isset($data["siglabase64"])  || $data["siglabase64"] == '') {
        $data["siglabase64"] = base64_encode($data["sigla"]);
        // }        
        if (!isset($data["latitud"])) {
            $data["latitud"] = '';
        }
        if (!isset($data["longitud"])) {
            $data["longitud"] = '';
        }
        if (!isset($data["econmixta"])) {
            $data["econmixta"] = '';
        }
        if (!isset($data["desactiv"])) {
            $data["desactiv"] = '';
        }
        if (!isset($data["telcor2"])) {
            $data["telcor2"] = '';
        }
        if (!isset($data["cap_apotra"])) {
            $data["cap_apotra"] = 0;
        }
        if (!isset($data["apolab"])) {
            $data["apolab"] = 0;
        }
        if (!isset($data["apolabadi"])) {
            $data["apolabadi"] = 0;
        }
        if (!isset($data["apodin"])) {
            $data["apodin"] = 0;
        }
        if (!isset($data["apotot"])) {
            $data["apotot"] = 0;
        }
        if (!isset($data["apotra"])) {
            $data["apotra"] = 0;
        }
        if (!isset($data["apoact"])) {
            $data["apoact"] = 0;
        }
        if (!isset($data["patnet"])) {
            $data["patnet"] = 0;
        }

        if (!isset($data["cap_porcnalpub"]) || ltrim($data["cap_porcnalpub"], "0") == '') {
            $data["cap_porcnalpub"] = 0;
        }
        if (!isset($data["cap_porcnalpri"]) || ltrim($data["cap_porcnalpri"], "0") == '') {
            $data["cap_porcnalpri"] = 0;
        }
        if (!isset($data["cap_porcextpub"]) || ltrim($data["cap_porcextpub"], "0") == '') {
            $data["cap_porcextpub"] = 0;
        }
        if (!isset($data["cap_porcextpri"]) || ltrim($data["cap_porcextpri"], "0") == '') {
            $data["cap_porcextpri"] = 0;
        }

        if (!isset($data["codigopostalcom"])) {
            $data["codigopostalcom"] = '';
        }
        if (!isset($data["codigopostalnot"])) {
            $data["codigopostalnot"] = '';
        }
        if (!isset($data["fechainiact1"])) {
            $data["fechainiact1"] = '';
        }
        if (!isset($data["fechainiact2"])) {
            $data["fechainiact2"] = '';
        }
        if (!isset($data["codaduaneros"])) {
            $data["codaduaneros"] = '';
        }
        if (!isset($data["gruponiif"])) {
            $data["gruponiif"] = '';
        }
        if (!isset($data["niifconciliacion"])) {
            $data["niifconciliacion"] = '';
        }
        if (!isset($data["ctrderpub"])) {
            $data["ctrderpub"] = '';
        }
        if (!isset($data["ctrcodcoop"])) {
            $data["ctrcodcoop"] = '';
        }
        if (!isset($data["ctrcodotras"])) {
            $data["ctrcodotras"] = '';
        }
        if (!isset($data["cumplerequisitos1780"])) {
            $data["cumplerequisitos1780"] = '';
        }
        if (!isset($data["renunciabeneficios1780"])) {
            $data["renunciabeneficios1780"] = '';
        }
        if (!isset($data["cumplerequisitos1780primren"])) {
            $data["cumplerequisitos1780primren"] = '';
        }

        if (!isset($data["vigcontrol"])) {
            $data["vigcontrol"] = '';
        }
        if (!isset($data["idorigenperj"])) {
            $data["idorigenperj"] = '';
        }
        if (!isset($data["origendocconst"])) {
            $data["origendocconst"] = '';
        }
        if (!isset($data["numperj"])) {
            $data["numperj"] = '';
        }
        if (!isset($data["fecperj"])) {
            $data["fecperj"] = '';
        }
        if (!isset($data["vigifecini"])) {
            $data["vigifecini"] = '';
        }
        if (!isset($data["vigifecfin"])) {
            $data["vigifecfin"] = '';
        }

        if (!isset($data["nrocontrolreactivacion"])) {
            $data["nrocontrolreactivacion"] = '';
        }
        if (!isset($data["fechanacimiento"])) {
            $data["fechanacimiento"] = '';
        }
        if (!isset($data["ideext"])) {
            $data["ideext"] = '';
        }
        if (!isset($data["anodatoscap"])) {
            $data["anodatoscap"] = '';
        }
        if (!isset($data["fechadatoscap"])) {
            $data["fechadatoscap"] = '';
        }
        if (!isset($data["cpafili"])) {
            $data["cpafili"] = '';
        }
        if (!isset($data["cpsaldo"])) {
            $data["cpsaldo"] = 0;
        }
        if (!isset($data["cpurenafi"])) {
            $data["cpurenafi"] = '';
        }
        if (!isset($data["feciniact1"])) {
            $data["feciniact1"] = '';
        }
        if (!isset($data["feciniact2"])) {
            $data["feciniact2"] = '';
        }
        if (!isset($data["estadocapturado"])) {
            $data["estadocapturado"] = '';
        }
        if (!isset($data["estadocapturadootros"])) {
            $data["estadocapturadootros"] = '';
        }
        if (!isset($data["cantest"])) {
            $data["cantest"] = 0;
        }
        if (!isset($data["certificardesde"])) {
            $data["certificardesde"] = '';
        }
        if (!isset($data["ctrbic"])) {
            $data["ctrbic"] = '';
        }

        if (!isset($data["sexo"])) {
            $data["sexo"] = '';
        }
        if (!isset($data["emprendimientosocial"])) {
            $data["emprendimientosocial"] = '';
        }
        if (!isset($data["empsoccategorias"])) {
            $data["empsoccategorias"] = '';
        }
        if (!isset($data["empsoccategorias_otros"])) {
            $data["empsoccategorias_otros"] = '';
        }
        if (!isset($data["empsocbeneficiarios"])) {
            $data["empsocbeneficiarios"] = '';
        }
        if (!isset($data["empsocbeneficiarios_otros"])) {
            $data["empsocbeneficiarios_otros"] = '';
        }
        if (!isset($data["cantidadmujeres"])) {
            $data["cantidadmujeres"] = 0;
        }
        if (!isset($data["cantidadmujerescargosdirectivos"])) {
            $data["cantidadmujerescargosdirectivos"] = 0;
        }
        if (!isset($data["cantidadcargosdirectivos"])) {
            $data["cantidadcargosdirectivos"] = 0;
        }

        if (!isset($data["participacionmujeres"])) {
            $data["participacionmujeres"] = 0;
        }
        if (!isset($data["ciiutamanoempresarial"])) {
            $data["ciiutamanoempresarial"] = '';
        }
        if (!isset($data["ingresostamanoempresarial"])) {
            $data["ingresostamanoempresarial"] = 0;
        }
        if (!isset($data["anodatostamanoempresarial"])) {
            $data["anodatostamanoempresarial"] = '';
        }
        if (!isset($data["fechadatostamanoempresarial"])) {
            $data["fechadatostamanoempresarial"] = '';
        }
        if (!isset($data["obligadorenovar"])) {
            $data["obligadorenovar"] = '';
        }
        if (!isset($data["vigilanciasuperfinanciera"])) {
            $data["vigilanciasuperfinanciera"] = '';
        }
        if (!isset($data["pendiente_ajuste_nuevo_formato"])) {
            $data["pendiente_ajuste_nuevo_formato"] = '';
        }
        if (!isset($data["fecha_pendiente_ajuste_nuevo_formato"])) {
            $data["fecha_pendiente_ajuste_nuevo_formato"] = '';
        }

        // 2023-02-27 - JINT - Todas las matrículas cuya fecha de matrícula sea posterior al
        // 20220101 quedqarán automáticamente revisadas.
        if ($data["fechamatricula"] > '20220100') {
            if ($data["pendiente_ajuste_nuevo_formato"] != 'R') {
                $data["pendiente_ajuste_nuevo_formato"] = 'R';
                $data["fecha_pendiente_ajuste_nuevo_formato"] = date("Ymd");
            }
        }

        //
        if (substr($data["matricula"], 0, 1) == 'S') {
            if ($data["estadomatricula"] == 'MA') {
                $data["estadomatricula"] = 'IA';
            }
            if ($data["estadomatricula"] == 'MC') {
                $data["estadomatricula"] = 'IC';
            }
            if ($data["estadomatricula"] == 'MI') {
                $data["estadomatricula"] = 'II';
            }
        }

        if (!isset($data["fechavencimiento"])) {
            $data["fechavencimiento"] = '';
        }

        if (!isset($data["fechavencimiento1"])) {
            $data["fechavencimiento1"] = '';
        }

        if (!isset($data["fechavencimiento2"])) {
            $data["fechavencimiento2"] = '';
        }

        if (!isset($data["fechavencimiento3"])) {
            $data["fechavencimiento3"] = '';
        }

        if (!isset($data["fechavencimiento4"])) {
            $data["fechavencimiento4"] = '';
        }

        if (!isset($data["fechavencimiento5"])) {
            $data["fechavencimiento5"] = '';
        }

        if (!isset($data["motivodesafiliacion"])) {
            $data["motivodesafiliacion"] = '';
        }

        if (!isset($data["txtmotivodesafiliacion"])) {
            $data["txtmotivodesafiliacion"] = '';
        }

        if (!isset($data["codrespotri"])) {
            $data["codrespotri"] = array();
        }

        if (!isset($data["etnia"])) {
            $data["etnia"] = '';
        }

        if (!isset($data["participacionetnia"]) || $data["participacionetnia"] == 0) {
            $data["participacionetnia"] = '';
        }

        if (trim($data["nombre"]) == '') {
            if ($data["organizacion"] == '01') {
                $data["nombre"] = trim($data["ape1"]);
                if (trim($data["ape2"]) != '') {
                    $data["nombre"] .= ' ' . trim($data["ape2"]);
                }
                if (trim($data["nom1"]) != '') {
                    $data["nombre"] .= ' ' . trim($data["nom1"]);
                }
                if (trim($data["nom2"]) != '') {
                    $data["nombre"] .= ' ' . trim($data["nom2"]);
                }
            }
        }

        if (!isset($data["extinciondominio"])) {
            $data["extinciondominio"] = '';
        }

        if (!isset($data["extinciondominiofechainicio"])) {
            $data["extinciondominiofechainicio"] = '';
        }

        if (!isset($data["extinciondominiofechafinal"])) {
            $data["extinciondominiofechafinal"] = '';
        }

        if (!isset($data["ctrfechadepuracion1727"])) {
            $data["ctrfechadepuracion1727"] = '';
        }
        if (!isset($data["propietarioacrear"])) {
            $data["propietarioacrear"] = array();
        }


        // **********************************************************************************
        // 2021-05-31: JINT: para prevenior error en Montería que aparecen los emails con
        // un . antes del arroba
        // Se previene que venga así al momento de actualizar.
        // **********************************************************************************
        $data["emailcom"] = str_replace(".@", "@", $data["emailcom"]);
        $data["emailcom2"] = str_replace(".@", "@", $data["emailcom2"]);
        $data["emailcom3"] = str_replace(".@", "@", $data["emailcom3"]);
        $data["emailnot"] = str_replace(".@", "@", $data["emailnot"]);

        //
        $grabarpor = '';
        $dataori = array();
        if ($data["matricula"] != '') {
            $dataori = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
            $grabarpor = 'matricula';
        } else {
            if ($data["proponente"] != '') {
                $dataori = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . ltrim($data["proponente"], "0") . "'");
                $grabarpor = 'proponente';
            }
        }


        // Control de libros de comercio
        $ctrlibroscomercio = 'N';
        if (!empty($data["libroscomercio"])) {
            if (count($data["libroscomercio"]) > 0) {
                $ctrlibroscomercio = 'S';
            }
        }

        //  Control de embargo
        $ctrembargo = 'N';
        if (!empty($data["ctrembargos"])) {
            foreach ($data["ctrembargos"] as $embx) {
                if ($embx["acto"] == '0900' || $embx["acto"] == '0910' || $embx["acto"] == '1000') {
                    if ($embx["estado"] == '1') {
                        $ctrembargo = 'S';
                    }
                }
            }
        }

        //  Control de embargos en tramite
        $ctrembargotramite = 'N';
        $ctrrecursotramite = 'N';
        if (!empty($data["lcodigosbarras"])) {
            foreach ($data["lcodigosbarras"] as $cbax) {
                if ($cbax["ttra"] == '07') {
                    $ctrembargotramite = 'S';
                }
                if ($cbax["ttra"] == '19' || $cbax["ttra"] == '54') {
                    $ctrrecursotramite = 'S';
                }
            }
        }


        // Localiza primer representante legal
        $idereplegal = '';
        $nomreplegal = '';
        if (!empty($data["replegal"])) {
            foreach ($data["replegal"] as $vinx) {
                if ($idereplegal == '') {
                    if (
                            $vinx["vinculoreplegal"] == '2170' ||
                            $vinx["vinculoreplegal"] == '4170'
                    ) {
                        $idereplegal = $vinx["identificacionreplegal"];
                        $nomreplegal = $vinx["nombrereplegal"];
                    }
                }
            }
        }

        // Localiza documento de constitucion
        $numdocconst = '';
        $fecdocconst = '';
        $oridocconst = $data["origendocconst"];
        if (trim($oridocconst) == '') {
            if (!empty($data["inscripciones"])) {
                foreach ($data["inscripciones"] as $insx) {
                    if ($insx["acto"] == '0040') {
                        $numdocconst = $insx["ndoc"];
                        $fecdocconst = $insx["fdoc"];
                        $oridocconst = $insx["txoridoc"];
                    }
                }
            }
        }



        $nomsegcon = '';
        $carsegcon = '';
        if (isset($data["nombresegundocontacto"])) {
            $nomsegcon = $data["nombresegundocontacto"];
        }
        if (isset($data["cargosegundocontacto"])) {
            $carsegcon = $data["cargosegundocontacto"];
        }

        $econmixta = '';
        if (isset($data["econmixta"])) {
            $econmixta = $data["econmixta"];
        }

        $capesadl = 0;
        if (isset($data["capesadl"])) {
            $capesadl = $data["capesadl"];
        }

        $aplicarcambionombre = '';
        if (trim((string) $data["nuevonombre"]) != '') {
            if (trim($data["nuevonombre"]) != $dataori["razonsocial"]) {
                \logApi::general2($nameLog, $data["matricula"], 'Implica cambio nombre de ' . $dataori["razonsocial"] . ' por : ' . $data["nuevonombre"]);
                $data["nombreanterior"] = $dataori["razonsocial"];
                $data["nombre"] = $data["nuevonombre"];
                $data["nombrebase64"] = base64_encode($data["nuevonombre"]);
                if ($tt == 'mutacionregmer' || $tt == 'mutacionesadl') {
                    $aplicarcambionombre = 'si';
                }
            }
        }

        // 2018-04-11 : JINT: Controles que se incluyen para prevenir que al actualizar el expediente
        // se elimine información vital
        if ($data["matricula"] != '') {
            if ($data["afiliado"] == '') {
                if (isset($dataori["ctrafiliacion"]) && $dataori["ctrafiliacion"] != '') {
                    $data["afiliado"] = $dataori["ctrafiliacion"];
                }
            }
            if ($data["dircom"] == '') {
                if (isset($dataori["dircom"]) && $dataori["dircom"] != '') {
                    $data["dircom"] = $dataori["dircom"];
                }
            }
            if ($data["muncom"] == '') {
                if (isset($dataori["muncom"]) && $dataori["muncom"] != '') {
                    $data["muncom"] = $dataori["muncom"];
                }
            }
        }

        // 2019-06-21: JINT: Incluye actualización del campo pendiente_ajuste_nuevo_formato
        if ($data["matricula"] != '') {
            if ($data["pendiente_ajuste_nuevo_formato"] == '') {
                if (isset($dataori["pendiente_ajuste_nuevo_formato"]) && $dataori["pendiente_ajuste_nuevo_formato"] != '') {
                    $data["pendiente_ajuste_nuevo_formato"] = $dataori["pendiente_ajuste_nuevo_formato"];
                }
            }
            if ($data["fecha_pendiente_ajuste_nuevo_formato"] == '') {
                if (isset($dataori["fecha_pendiente_ajuste_nuevo_formato"]) && $dataori["fecha_pendiente_ajuste_nuevo_formato"] != '') {
                    $data["fecha_pendiente_ajuste_nuevo_formato"] = $dataori["fecha_pendiente_ajuste_nuevo_formato"];
                }
            }
        }

        // ************************************************************************************************************ //
        // 2020-03-23: JINT
        // Si el parámetro alto impacto está en si, mueve a los campos data los contenidos originales,
        // De esta forma se actualiza el formulario menos los campos indicados
        // - nombre o razón social
        // - ciiu1, ciiu2, ciiu3, cii4, descripción de la actividad económica
        // - direccion, municipio
        // ************************************************************************************************************ //
        if ($altoimpacto == 'si') {
            $data["nombre"] = $dataori["razonsocial"];
            $data["ciius"][1] = $dataori["ciiu1"];
            $data["ciius"][2] = $dataori["ciiu2"];
            $data["ciius"][3] = $dataori["ciiu3"];
            $data["ciius"][4] = $dataori["ciiu4"];
            $data["desactiv"] = $dataori["actividad"];
            $data["dircom"] = $dataori["dircom"];
            $data["muncom"] = $dataori["muncom"];
        }

        if (substr($data["matricula"], 0, 1) == 'S') {
            if (trim($data["ctresaivc"]) == '') {
                if (trim($data["vigcontrol"]) != '' && is_numeric($data["vigcontrol"]) && ltrim($data["vigcontrol"], "0") != '') {
                    $data["ctresaivc"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='43' and idcodigo = '" . $data["vigcontrol"] . "'", "descripcion");
                }
                if (trim($data["vigcontrol"]) != '' && !is_numeric($data["vigcontrol"])) {
                    $data["ctresaivc"] = $data["vigcontrol"];
                }
            } else {
                $data["vigcontrol"] = $data["ctresaivc"];
            }
        }

        $arrCampos = array(
            'matricula',
            'proponente',
            'organizacion',
            'categoria',
            'naturaleza',
            'nrocontrolreactivacion',
            'razonsocial',
            'sigla',
            'complementorazonsocial',
            'nombrecomercial',
            'lggr',
            'nombre1',
            'nombre2',
            'apellido1',
            'apellido2',
            'idclase',
            'numid',
            'sexo',
            'numidextenso',
            'idmunidoc',
            'fechanacimiento',
            'fecexpdoc',
            'paisexpdoc',
            'nacionalidad',
            'idetripaiori', // Nuevo
            'paiori', // nuevo
            'idetriextep', // Nuevo
            'prerut',
            'admondian',
            'nit',
            'estadonit',
            'fecmatricula',
            'fecrenovacion',
            'ultanoren',
            'obligadorenovar',
            'feccancelacion',
            'motivocancelacion', // Nuevo
            'fecconstitucion',
            'fecdisolucion',
            'fecliquidacion',
            'estadotipoliquidacion', // Nuevo
            'fecvigencia',
            'tamanoempresa',
            'emprendedor28',
            'pemprendedor28',
            'empresafamiliar', // Nuevo
            'procesosinnovacion', // Nuevo
            'tipogruemp', // Nuevo
            'nombregruemp', // Nuevo
            'vigilanciasuperfinanciera', // 2020 10 05
            'vigcontrol', // Entidad que ejerce la vigilancia y control
            'vigifecini',
            'vigifecfin',
            'numperj',
            'fecperj',
            'otorgaperj',
            'numdocconst',
            'fecdocconst',
            'origendocconst',
            'fecmatant',
            'fecrenant',
            'camant',
            'munant',
            'ultanorenant',
            'matant',
            'benart7ant',
            'benley1780ant',
            'dircom',
            'dircom_tipovia',
            'dircom_numvia',
            'dircom_apevia',
            'dircom_orivia',
            'dircom_numcruce',
            'dircom_apecruce',
            'dircom_oricruce',
            'dircom_numplaca',
            'dircom_complemento',
            'codigopostalcom',
            'codigozonacom', // Nuevo
            'barriocom',
            'muncom',
            'paicom', // Nuevo
            'telcom1',
            'telcom2',
            'telcom3',
            'emailcom',
            'emailcom2',
            'emailcom3',
            'urlcom',
            'aacom',
            'faxcom',
            'numpredial',
            'latitud',
            'longitud',
            'dirnot',
            'dirnot_tipovia',
            'dirnot_numvia',
            'dirnot_apevia',
            'dirnot_orivia',
            'dirnot_numcruce',
            'dirnot_apecruce',
            'dirnot_oricruce',
            'dirnot_numplaca',
            'dirnot_complemento',
            'codigopostalnot',
            'codigozonanot', // Nuevo
            'barrionot',
            'munnot',
            'painot', // Nuevo
            'telnot',
            'telnot2',
            'telnot3',
            'emailnot',
            'urlnot',
            'aanot',
            'faxnot',
            'tiposedeadm',
            'dircor',
            'muncor',
            'telcor',
            'telcor2',
            'ciiu1',
            'ciiu2',
            'ciiu3',
            'ciiu4',
            'ciiu5',
            'versionciiu',
            'feciniact1', // Nuevo
            'feciniact2', // Nuevo
            'ingesperados', // Ingresos esperados
            'codaduaneros', // Nuevo
            'actividad',
            'fecaflia',
            'fecactaaflia',
            'numactaaflia',
            'fecactacanaflia',
            'numactacanaflia',
            'anorenaflia',
            'fecrenaflia',
            'valpagaflia',
            'saldoaflia',
            'telaflia',
            'diraflia',
            'munaflia',
            'profaflia',
            'contaflia',
            'dircontaflia',
            'muncontaflia',
            'fecexafiliacion',
            'motivodesafiliacion',
            'txtmotivodesafiliacion',
            'ctrexpedienteinactivo',
            'ctrestmatricula',
            'ctrestdatos',
            'ctrcertificardesde',
            // 'ctrestproponente', // 2019-04-25: JINT: No permitir el cambio de este campo al actualizar trámites de registro mercantil
            'ctrtipopropiedad',
            'ctrlibroscomercio',
            'ctrafiliacion',
            'ctrvigilancia',
            'ctrestadoactiva',
            'ctrestadopreoperativa',
            'ctrconcordato',
            'ctrestadointervenida',
            'ctrestadodisuelta',
            'ctrestadoenreestructuracion',
            'ctrestadocapturado', //Nuevo
            'ctrestadocapturadootros', // Nuevo
            'ctrembargo',
            'ctrembargostramite',
            'ctrrecursostramite',
            'ctrimpexp',
            'ctrtipolocal',
            'ctrfun',
            'ctrubi',
            'ctrclasegenesadl',
            'ctrclaseespeesadl',
            'ctrclaseeconsoli',
            'ctrderpub',
            'ctrcodcoop',
            'ctrcodotras',
            'ctreconmixta',
            'ctresacntasociados', // Nuevo
            'ctresacntmujeres', // Nuevo
            'ctresacnthombres', // Nuevo
            'ctresapertgremio', // Nuevo
            'ctresagremio', // Nuevo
            'ctresaacredita', // Nuevo
            'ctresaivc', // Nuevo
            'ctresainfoivc', // Nuevo
            'ctresaautregistro', // Nuevo
            'ctresaentautoriza', // Nuevo
            'ctresacodnat', // Nuevo
            'ctresadiscap', // Nuevo
            'ctresaetnia', // Nuevo
            'ctresacualetnia', // Nuevo
            'ctresadespvictreins', // Nuevo
            'ctresacualdespvictreins', // Nuevo
            'ctresaindgest', // Nuevo
            'ctresalgbti', // Nuevo
            'ctrbenart4',
            'ctrbenart7',
            'ctrbenart50',
            'ctrbenley1780',
            'cumplerequisitos1780',
            'renunciabeneficios1780',
            'cumplerequisitos1780primren',
            'ctrcance1429',
            'ctrdepuracion1727',
            'ctrfechadepuracion1727',
            'ctrben658',
            'ctrnotemail',
            'ctrnotsms',
            'ctrivcarea', // Double
            'ctrivcver',
            'ctrivccretip',
            'ctrivcali',
            'ctrivcqui',
            'ctrivcriesgo',
            'ctrivcenvio',
            'ctrivcsuelos',
            'ctrbic',
            'cantest',
            'idereplegal',
            'nomreplegal',
            'gruponiif', // Nuevo
            'niifconciliacion', // Nuevo
            'aportantesegsocial', // Nuevo
            'tipoaportantesegsocial', // Nuevo
            'anodatos',
            'fecdatos',
            'personal',
            'personaltemp',
            'actcte',
            'actnocte',
            'actfij',
            'fijnet',
            'actval',
            'actotr',
            'acttot',
            'actsinaju',
            'invent',
            'pascte',
            'paslar',
            'pastot',
            'pattot',
            'paspat',
            'balsoc',
            'ingope',
            'ingnoope',
            'gasope',
            'gasnoope',
            'gtoven',
            'gtoadm',
            'utiope',
            'utinet',
            'cosven',
            'depamo',
            'gasint',
            'gasimp',
            'actvin',
            'patrimonio',
            'anodatoscap',
            'fecdatoscap',
            'capaut',
            'capsus',
            'cappag',
            'capsoc',
            'apolab',
            'apolabadi',
            'apodin',
            'apotra',
            'apoact',
            'apotot',
            'capesadl',
            'pornalpri',
            'pornalpub',
            'pornaltot',
            'porextpri',
            'porextpub',
            'porexttot',
            'cantidadmujeres',
            'participacionmujeres',
            'cantidadmujerescargosdirectivos',
            'nomsegcon',
            'carsegcon',
            'cntest01',
            'cntest02',
            'cntest03',
            'cntest04',
            'cntest05',
            'cntest06',
            'cntest07',
            'cntest08',
            'cntest09',
            'cntest10',
            'cntest11',
            'refcrenom1',
            'refcreofi1',
            'refcretel1', // Nuevo
            'refcrenom2',
            'refcreofi2',
            'refcretel2', // Nuevo
            'refcomnom1',
            'refcomdir1',
            'refcomtel1',
            'refcomnom2',
            'refcomdir2',
            'refcomtel2',
            'cpcodcam',
            'cpnummat',
            'cprazsoc',
            'cpnumnit',
            'cpdircom',
            'cpdirnot',
            'cpnumtel',
            'cpnumfax',
            'cpcodmun',
            'cpmunnot',
            'cpafili',
            'cpsaldo', // Double
            'cpurenafi',
            'pendiente_ajuste_nuevo_formato',
            'fecha_pendiente_ajuste_nuevo_formato',
            'fecactualizacion',
            'compite360',
            'rues',
            'ivc'
        );

        $ctrdepuracion1727 = isset($data["ctrdepuracion1727"]) ? trim($data["ctrdepuracion1727"]) : '';
        $ctrben1780 = isset($data["ctrben658"]) ? trim($data["ctrben658"]) : '';

        // 249
        $arrValores = array(
            "'" . ltrim((string) $data["matricula"], "0") . "'",
            "'" . ltrim((string) $data["proponente"], "0") . "'",
            "'" . $data["organizacion"] . "'",
            "'" . $data["categoria"] . "'",
            "'" . $data["naturaleza"] . "'",
            "'" . $data["nrocontrolreactivacion"] . "'",
            "'" . addslashes(trim((string) $data["nombre"])) . "'",
            "'" . addslashes(trim((string) $data["sigla"])) . "'",
            "'" . addslashes(trim((string) $data["complementorazonsocial"])) . "'",
            "''", // Nombre comercial
            "'" . addslashes((string) trim($data["lggr"])) . "'",
            "'" . addslashes((string) trim($data["nom1"])) . "'",
            "'" . addslashes((string) trim($data["nom2"])) . "'",
            "'" . addslashes((string) trim($data["ape1"])) . "'",
            "'" . addslashes((string) trim($data["ape2"])) . "'",
            "'" . trim((string) $data["tipoidentificacion"]) . "'",
            "'" . trim((string) $data["identificacion"]) . "'",
            "'" . trim((string) $data["sexo"]) . "'",
            "'" . trim((string) $data["ideext"]) . "'",
            "'" . trim((string) $data["idmunidoc"]) . "'",
            "'" . trim((string) $data["fechanacimiento"]) . "'",
            "'" . trim((string) $data["fecexpdoc"]) . "'",
            "'" . trim((string) $data["paisexpdoc"]) . "'",
            "'" . trim((string) $data["nacionalidad"]) . "'",
            "'" . trim((string) $data["idetripaiori"]) . "'",
            "'" . trim((string) $data["paiori"]) . "'",
            "'" . trim((string) $data["idetriextep"]) . "'",
            "'" . trim((string) $data["prerut"]) . "'",
            "'" . trim((string) $data["admondian"]) . "'",
            "'" . trim((string) $data["nit"]) . "'",
            "'" . trim((string) $data["estadonit"]) . "'",
            "'" . trim((string) $data["fechamatricula"]) . "'",
            "'" . trim((string) $data["fecharenovacion"]) . "'",
            "'" . trim((string) $data["ultanoren"]) . "'",
            "'" . trim((string) $data["obligadorenovar"]) . "'",
            "'" . trim((string) $data["fechacancelacion"]) . "'",
            "'" . trim((string) $data["motivocancelacion"]) . "'",
            "'" . trim((string) $data["fechaconstitucion"]) . "'",
            "'" . trim((string) $data["fechadisolucion"]) . "'",
            "'" . trim((string) $data["fechaliquidacion"]) . "'",
            "'" . trim((string) $data["estadotipoliquidacion"]) . "'",
            "'" . trim((string) $data["fechavencimiento"]) . "'",
            "'" . trim((string) $data["tamanoempresa"]) . "'",
            "'" . trim((string) $data["emprendedor28"]) . "'",
            doubleval($data["pemprendedor28"]),
            "'" . trim((string) $data["empresafamiliar"]) . "'",
            "'" . trim((string) $data["procesosinnovacion"]) . "'",
            "'" . trim((string) $data["tipogruemp"]) . "'",
            "'" . addslashes(trim((string) $data["nombregruemp"])) . "'",
            "'" . trim((string) $data["vigilanciasuperfinanciera"]) . "'",
            "'" . addslashes(trim((string) $data["vigcontrol"])) . "'",
            "'" . trim((string) $data["vigifecini"]) . "'",
            "'" . trim((string) $data["vigifecfin"]) . "'",
            "'" . trim((string) $data["numperj"]) . "'",
            "'" . trim((string) $data["fecperj"]) . "'",
            "'" . trim((string) $data["idorigenperj"]) . "'",
            "'" . trim((string) $numdocconst) . "'",
            "'" . trim((string) $fecdocconst) . "'",
            "'" . trim((string) $oridocconst) . "'",
            "'" . trim((string) $data["fecmatant"]) . "'",
            "'" . trim((string) $data["fecrenant"]) . "'",
            "'" . trim((string) $data["camant"]) . "'",
            "'" . trim((string) $data["munant"]) . "'",
            "'" . trim((string) $data["ultanorenant"]) . "'",
            "'" . trim((string) $data["matant"]) . "'",
            "'" . trim((string) $data["benart7ant"]) . "'",
            "'" . trim((string) $data["benley1780ant"]) . "'",
            "'" . addslashes(trim((string) $data["dircom"])) . "'",
            "'" . trim((string) $data["dircom_tipovia"]) . "'",
            "'" . trim((string) $data["dircom_numvia"]) . "'",
            "'" . trim((string) $data["dircom_apevia"]) . "'",
            "'" . trim((string) $data["dircom_orivia"]) . "'",
            "'" . trim((string) $data["dircom_numcruce"]) . "'",
            "'" . trim((string) $data["dircom_apecruce"]) . "'",
            "'" . trim((string) $data["dircom_oricruce"]) . "'",
            "'" . trim((string) $data["dircom_numplaca"]) . "'",
            "'" . trim((string) $data["dircom_complemento"]) . "'",
            "'" . trim((string) $data["codigopostalcom"]) . "'",
            "'" . trim((string) $data["codigozonacom"]) . "'",
            "'" . trim((string) $data["barriocom"]) . "'",
            "'" . trim((string) $data["muncom"]) . "'",
            "'" . trim((string) $data["paicom"]) . "'",
            "'" . trim((string) $data["telcom1"]) . "'",
            "'" . trim((string) $data["telcom2"]) . "'",
            "'" . trim((string) $data["celcom"]) . "'",
            "'" . addslashes(trim((string) $data["emailcom"])) . "'",
            "'" . addslashes(trim((string) $data["emailcom2"])) . "'",
            "'" . addslashes(trim((string) $data["emailcom3"])) . "'",
            "'" . addslashes(trim((string) $data["urlcom"])) . "'",
            "'" . (trim((string) $data["aacom"])) . "'",
            "'" . (trim((string) $data["faxcom"])) . "'",
            "'" . trim((string) $data["numpredial"]) . "'",
            "'" . trim((string) $data["latitud"]) . "'",
            "'" . trim((string) $data["longitud"]) . "'",
            "'" . addslashes(trim((string) $data["dirnot"])) . "'",
            "'" . trim((string) $data["dirnot_tipovia"]) . "'",
            "'" . trim((string) $data["dirnot_numvia"]) . "'",
            "'" . trim((string) $data["dirnot_apevia"]) . "'",
            "'" . trim((string) $data["dirnot_orivia"]) . "'",
            "'" . trim((string) $data["dirnot_numcruce"]) . "'",
            "'" . trim((string) $data["dirnot_apecruce"]) . "'",
            "'" . trim((string) $data["dirnot_oricruce"]) . "'",
            "'" . trim((string) $data["dirnot_numplaca"]) . "'",
            "'" . trim((string) $data["dirnot_complemento"]) . "'",
            "'" . trim((string) $data["codigopostalnot"]) . "'",
            "'" . trim((string) $data["codigozonanot"]) . "'",
            "'" . trim((string) $data["barrionot"]) . "'",
            "'" . trim((string) $data["munnot"]) . "'",
            "'" . trim((string) $data["painot"]) . "'",
            "'" . trim((string) $data["telnot"]) . "'",
            "'" . trim((string) $data["telnot2"]) . "'",
            "'" . trim((string) $data["celnot"]) . "'",
            "'" . addslashes(trim((string) $data["emailnot"])) . "'",
            "'" . addslashes(trim((string) $data["urlnot"])) . "'",
            "'" . (trim((string) $data["aanot"])) . "'",
            "'" . (trim((string) $data["faxnot"])) . "'",
            "'" . (trim((string) $data["tiposedeadm"])) . "'",
            "'" . trim((string) $data["dircor"]) . "'",
            "'" . trim((string) $data["muncor"]) . "'",
            "'" . trim((string) $data["telcor"]) . "'",
            "'" . trim((string) $data["telcor2"]) . "'",
            "'" . trim((string) $data["ciius"][1]) . "'",
            "'" . trim((string) $data["ciius"][2]) . "'",
            "'" . trim((string) $data["ciius"][3]) . "'",
            "'" . trim((string) $data["ciius"][4]) . "'",
            "'" . trim((string) $data["ciius"][5]) . "'",
            "'" . trim((string) $data["versionciiu"]) . "'",
            "'" . trim((string) $data["feciniact1"]) . "'",
            "'" . trim((string) $data["feciniact2"]) . "'",
            doubleval($data["ingesperados"]),
            "'" . trim((string) $data["codaduaneros"]) . "'",
            "'" . addslashes(substr(trim((string) $data["desactiv"]), 0, 1000)) . "'",
            "'" . trim((string) $data["fechaafiliacion"]) . "'",
            "'" . trim((string) $data["fecactaaflia"]) . "'",
            "'" . trim((string) $data["numactaaflia"]) . "'",
            "'" . trim((string) $data["fecactaafliacan"]) . "'",
            "'" . trim((string) $data["numactaafliacan"]) . "'",
            "'" . trim((string) $data["ultanorenafi"]) . "'",
            "'" . trim((string) $data["fechaultpagoafi"]) . "'",
            doubleval($data["valorultpagoafi"]),
            doubleval($data["saldoafiliado"]),
            "'" . trim((string) $data["telaflia"]) . "'",
            "'" . trim((string) $data["diraflia"]) . "'",
            "'" . trim((string) $data["munaflia"]) . "'",
            "'" . trim((string) $data["profaflia"]) . "'",
            "'" . trim((string) $data["contaflia"]) . "'",
            "'" . trim((string) $data["dircontaflia"]) . "'",
            "'" . trim((string) $data["muncontaflia"]) . "'",
            "'" . trim((string) $data["fecexafiliacion"]) . "'",
            "'" . trim((string) $data["motivodesafiliacion"]) . "'",
            "'" . addslashes(trim((string) $data["txtmotivodesafiliacion"])) . "'",
            "''", // ctrexpedienteinactivo
            "'" . trim((string) $data["estadomatricula"]) . "'",
            "'" . trim((string) $data["estadodatosmatricula"]) . "'",
            "'" . trim((string) $data["certificardesde"]) . "'",
            // "'" . trim($data["estadoproponente"]) . "'", // 2019-04-25: JINT
            "'" . trim((string) $data["tipopropiedad"]) . "'",
            "'" . trim((string) $ctrlibroscomercio) . "'",
            "'" . trim((string) $data["afiliado"]) . "'",
            "''", // ctrvigilancia
            "''", // ctrestadoactiva
            "''", // ctrestadopreoperativa
            "''", // ctrestadoconcordato
            "''", // ctrestadointervenida
            "''", // ctrestadodisuelta
            "''", // ctrestadoenreestructuracion
            "'" . trim((string) $data["estadocapturado"]) . "'",
            "'" . addslashes(trim((string) $data["estadocapturadootros"])) . "'",
            "'" . trim((string) $ctrembargo) . "'",
            "'" . trim((string) $ctrembargotramite) . "'",
            "'" . trim((string) $ctrrecursotramite) . "'",
            "'" . trim((string) $data["impexp"]) . "'",
            "'" . trim((string) $data["tipolocal"]) . "'",
            "'" . trim((string) $data["ctrfun"]) . "'",
            "'" . trim((string) $data["ctrubi"]) . "'",
            "'" . trim((string) $data["clasegenesadl"]) . "'",
            "'" . trim((string) $data["claseespesadl"]) . "'",
            "'" . trim((string) $data["claseeconsoli"]) . "'",
            "'" . trim((string) $data["ctrderpub"]) . "'",
            "'" . trim((string) $data["ctrcodcoop"]) . "'",
            "'" . trim((string) $data["ctrcodotras"]) . "'",
            "'" . trim((string) $econmixta) . "'",
            intval($data["ctresacntasociados"]),
            intval($data["ctresacntmujeres"]),
            intval($data["ctresacnthombres"]),
            "'" . trim((string) $data["ctresapertgremio"]) . "'",
            "'" . addslashes($data["ctresagremio"]) . "'",
            "'" . addslashes($data["ctresaacredita"]) . "'",
            "'" . trim((string) $data["ctresaivc"]) . "'",
            "'" . trim((string) $data["ctresainfoivc"]) . "'",
            "'" . trim((string) $data["ctresaautregistro"]) . "'",
            "'" . addslashes($data["ctresaentautoriza"]) . "'",
            "'" . trim((string) $data["ctresacodnat"]) . "'",
            "'" . trim((string) $data["ctresadiscap"]) . "'",
            "'" . trim((string) $data["ctresaetnia"]) . "'",
            "'" . addslashes($data["ctresacualetnia"]) . "'",
            "'" . trim((string) $data["ctresadespvictreins"]) . "'",
            "'" . addslashes($data["ctresacualdespvictreins"]) . "'",
            "'" . trim((string) $data["ctresaindgest"]) . "'",
            "'" . trim((string) $data["ctresalgbti"]) . "'",
            "'" . trim((string) $data["art4"]) . "'",
            "'" . trim((string) $data["art7"]) . "'",
            "'" . trim((string) $data["art50"]) . "'",
            "'" . trim((string) $data["benley1780"]) . "'",
            "'" . trim((string) $data["cumplerequisitos1780"]) . "'",
            "'" . trim((string) $data["renunciabeneficios1780"]) . "'",
            "'" . trim((string) $data["cumplerequisitos1780primren"]) . "'",
            "'" . trim((string) $data["ctrcancelacion1429"]) . "'",
            "'" . $ctrdepuracion1727 . "'",
            "'" . trim((string) $data["ctrfechadepuracion1727"]) . "'",
            "'" . $ctrben1780 . "'",
            "'" . substr(trim((string) $data["ctrmen"]), 0, 1) . "'",
            "'" . substr(trim((string) $data["ctrmennot"]), 0, 1) . "'",
            doubleval($data["ivcarea"]),
            "'" . trim((string) $data["ivcver"]) . "'",
            "'" . trim((string) $data["ivccretip"]) . "'",
            "'" . trim((string) $data["ivcali"]) . "'",
            "'" . trim((string) $data["ivcqui"]) . "'",
            "'" . trim((string) $data["ivcriesgo"]) . "'",
            "'" . trim((string) $data["ivcenvio"]) . "'",
            "'" . trim((string) $data["ivcsuelos"]) . "'",
            "'" . trim((string) $data["ctrbic"]) . "'",
            intval($data["cantest"]),
            "'" . trim((string) $idereplegal) . "'",
            "'" . addslashes($nomreplegal) . "'",
            "'" . trim((string) $data["gruponiif"]) . "'",
            "'" . trim((string) $data["niifconciliacion"]) . "'",
            "'" . trim((string) $data["aportantesegsocial"]) . "'",
            "'" . trim((string) $data["tipoaportantesegsocial"]) . "'",
            "'" . trim((string) $data["anodatos"]) . "'",
            "'" . trim((string) $data["fechadatos"]) . "'",
            intval($data["personal"]),
            doubleval($data["personaltemp"]),
            doubleval($data["actcte"]),
            doubleval($data["actnocte"]),
            doubleval($data["actfij"]),
            doubleval($data["fijnet"]),
            doubleval($data["actval"]),
            doubleval($data["actotr"]),
            doubleval($data["acttot"]),
            doubleval($data["actsinaju"]),
            doubleval($data["invent"]),
            doubleval($data["pascte"]),
            doubleval($data["paslar"]),
            doubleval($data["pastot"]),
            doubleval($data["pattot"]),
            doubleval($data["paspat"]),
            doubleval($data["balsoc"]),
            doubleval($data["ingope"]),
            doubleval($data["ingnoope"]),
            doubleval($data["gtoven"]), //
            doubleval($data["gtoadm"]), //
            doubleval($data["gtoven"]),
            doubleval($data["gtoadm"]),
            doubleval($data["utiope"]),
            doubleval($data["utinet"]),
            doubleval($data["cosven"]), //
            doubleval($data["depamo"]),
            doubleval($data["gasint"]),
            doubleval($data["gasimp"]),
            doubleval($data["actvin"]),
            doubleval($data["patrimonio"]),
            "'" . trim((string) $data["anodatoscap"]) . "'",
            "'" . trim((string) $data["fechadatoscap"]) . "'",
            doubleval($data["capaut"]),
            doubleval($data["capsus"]),
            doubleval($data["cappag"]),
            doubleval($data["capsoc"]),
            doubleval($data["apolab"]),
            doubleval($data["apolabadi"]),
            doubleval($data["apodin"]),
            doubleval($data["apotra"]),
            doubleval($data["apoact"]),
            doubleval($data["apotot"]),
            doubleval($capesadl),
            doubleval($data["cap_porcnalpri"]),
            doubleval($data["cap_porcnalpub"]),
            doubleval($data["cap_porcnaltot"]),
            doubleval($data["cap_porcextpri"]),
            doubleval($data["cap_porcextpub"]),
            doubleval($data["cap_porcexttot"]),
            intval($data["cantidadmujeres"]),
            doubleval($data["participacionmujeres"]),
            intval($data["cantidadmujerescargosdirectivos"]),
            "'" . trim((string) $nomsegcon) . "'",
            "'" . trim((string) $carsegcon) . "'",
            intval($data["cntestab01"]),
            intval($data["cntestab02"]),
            intval($data["cntestab03"]),
            intval($data["cntestab04"]),
            intval($data["cntestab05"]),
            intval($data["cntestab06"]),
            intval($data["cntestab07"]),
            intval($data["cntestab08"]),
            intval($data["cntestab09"]),
            intval($data["cntestab10"]),
            intval($data["cntestab11"]),
            "'" . trim((string) $data["refcrenom1"]) . "'",
            "'" . trim((string) $data["refcreofi1"]) . "'",
            "'" . trim((string) $data["refcretel1"]) . "'",
            "'" . trim((string) $data["refcrenom2"]) . "'",
            "'" . trim((string) $data["refcreofi2"]) . "'",
            "'" . trim((string) $data["refcretel2"]) . "'",
            "'" . trim((string) $data["refcomnom1"]) . "'",
            "'" . trim((string) $data["refcomdir1"]) . "'",
            "'" . trim((string) $data["refcomtel1"]) . "'",
            "'" . trim((string) $data["refcomnom2"]) . "'",
            "'" . trim((string) $data["refcomdir2"]) . "'",
            "'" . trim((string) $data["refcomtel2"]) . "'",
            "'" . trim((string) $data["cpcodcam"]) . "'",
            "'" . trim((string) $data["cpnummat"]) . "'",
            "'" . trim((string) $data["cprazsoc"]) . "'",
            "'" . trim((string) $data["cpnumnit"]) . "'",
            "'" . trim((string) $data["cpdircom"]) . "'",
            "'" . trim((string) $data["cpdirnot"]) . "'",
            "'" . trim((string) $data["cpnumtel"]) . "'",
            "'" . trim((string) $data["cpnumfax"]) . "'",
            "'" . trim((string) $data["cpcodmun"]) . "'",
            "'" . trim((string) $data["cpmunnot"]) . "'",
            "'" . trim((string) $data["cpafili"]) . "'",
            doubleval($data["cpsaldo"]),
            "'" . trim((string) $data["cpurenafi"]) . "'",
            "'" . trim((string) $data["pendiente_ajuste_nuevo_formato"]) . "'",
            "'" . trim((string) $data["fecha_pendiente_ajuste_nuevo_formato"]) . "'",
            "'" . date("Ymd") . "'",
            "'NO'", // Compite360
            "'NO'", // Rues
            "'NO'" // ivc
        );

        // Graba el registro en mreg_est_inscritos
        $idgrabar = 0;

        //
        if ($data["matricula"] != '') {
            $idreg = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
            if ($idreg && !empty($idreg)) {
                $idgrabar = $idreg["id"];
            }
        } else {
            if ($data["proponente"] != '') {
                $idreg = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . ltrim($data["proponente"], "0") . "'");
                if ($idreg && !empty($idreg)) {
                    $idgrabar = $idreg["id"];
                }
            }
        }

        if ($idgrabar == 0) {
            if ($crear == 'si') {
                unset($_SESSION["expedienteactual"]);
                $control = 'insertar';
                $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error creando matrícula en mreg_est_inscritos : ' . $_SESSION["generales"]["mensajeerror"]);
                    if ($cerrarmysql == 'si') {
                        $mysqli->close();
                    }
                    return false;
                }
            } else {
                return false;
            }
        }
        if ($idgrabar != 0) {
            unset($_SESSION["expedienteactual"]);
            $control = 'regrabar';
            $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "id=" . $idgrabar);
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_inscritos: ' . $_SESSION["generales"]["mensajeerror"]);
                if ($cerrarmysql == 'si') {
                    $mysqli->close();
                }
                return false;
            }
        }

        // ***************************************************************************** //
        // fechas de vencimiento
        // ***************************************************************************** //
        borrarRegistrosMysqliApi($mysqli, 'mreg_est_vigencias', "matricula='" . ltrim($data["matricula"], "0") . "'");
        for ($ivs = 1; $ivs <= 5; $ivs++) {
            if (isset($data["fechavencimiento" . $ivs])) {
                if (trim($data["fechavencimiento" . $ivs]) != '') {
                    $arrCmp1 = array(
                        'matricula',
                        'fecha'
                    );
                    $arrVal1 = array(
                        "'" . $data["matricula"] . "'",
                        "'" . $data["fechavencimiento" . $ivs] . "'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_est_vigencias', $arrCmp1, $arrVal1);
                }
            }
        }

        //    
        $datafin = array();
        if ($data["matricula"] != '') {
            $datafin = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
        } else {
            if ($data["proponente"] != '') {
                $datafin = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . ltrim($data["proponente"], "0") . "'");
            }
        }

        // Actualiza mreg_tamano-empresarial
        if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && $data["categoria"] == '1')) {
            $condicion = "matricula='" . $data["matricula"] . "' and anodatos='" . $data["anodatostamanoempresarial"] . "' and fechadatos='" . $data["fechadatostamanoempresarial"] . "'";
            $arrCampos = array(
                'matricula',
                'anodatos',
                'fechadatos',
                'ciiu',
                'ingresos'
            );
            $arrValores = array(
                "'" . $data["matricula"] . "'",
                "'" . $data["anodatostamanoempresarial"] . "'",
                "'" . $data["fechadatostamanoempresarial"] . "'",
                "'" . $data["ciiutamanoempresarial"] . "'",
                doubleval($data["ingresostamanoempresarial"]),
            );
            if (contarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', $condicion) == 0) {
                insertarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', $arrCampos, $arrValores);
            } else {
                regrabarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', $arrCampos, $arrValores, $condicion);
            }

            // Recalcula el tamaño empresarial
            $te = \funcionesGenerales::calcularTamanoEmpresarial($mysqli, $data["matricula"]);
            if ($te && $te["codigo"] != '') {
                \funcionesRegistrales::actualizarMregEstInscritosCampoCampos($mysqli, $data["matricula"], 'tamano_empresarial', $te["codigo"], $codbarras, $tt, $rec);
            }
            // }
        }

        // Actualiza códigos CAE
        if (ltrim($data["matricula"], "0") != '') {
            $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_anexoscae', "1=1", "codigocae");
            if ($arrY && !empty($arrY)) {
                $iValores = 0;
                $arrValores = array();
                $arrCampos = array(
                    'tabla',
                    'registro',
                    'campo',
                    'contenido'
                );

                foreach ($arrY as $y) {
                    borrarRegistrosMysqliApi($mysqli, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim($data["matricula"], "0") . "' and campo='" . $y["codigocae"] . "'");
                    if (isset($data["codigoscae"][$y["codigocae"]]) && trim($data["codigoscae"][$y["codigocae"]]) != '') {
                        $iValores++;
                        $arrValores[$iValores] = array(
                            "'200'",
                            "'" . ltrim($data["matricula"], "0") . "'",
                            "'" . $y["codigocae"] . "'",
                            "'" . trim($data["codigoscae"][$y["codigocae"]]) . "'"
                        );
                    }
                }
                if ($iValores > 0) {
                    insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_est_campostablas', $arrCampos, $arrValores);
                }
            }
        }

        //
        if ($data["matricula"] != '') {
            $arrCampos = array(
                'matricula',
                'campo',
                'fecha',
                'hora',
                'codigobarras',
                'datoanterior',
                'datonuevo',
                'usuario',
                'ip',
                'tipotramite',
                'recibo'
            );
            $arrValores = array();
            $iCambios = 0;
            $fecxact = date("Ymd");
            $horxact = date("His");
            foreach ($datafin as $key => $valor) {
                if (
                        $key != 'id' &&
                        !is_numeric($key) &&
                        $key != 'matricula' &&
                        $key != 'fecactualizacion' &&
                        $key != 'horactualizacion' &&
                        $key != 'fecsincronizacion' &&
                        $key != 'horsincronizacion' &&
                        $key != 'compite360' &&
                        $key != 'rues' &&
                        $key != 'ivc'
                ) {

                    if (!isset($dataori[$key])) {
                        $dataori[$key] = '';
                    }
                    if ($dataori[$key] == null) {
                        $dataori[$key] = '';
                    }
                    if ($dataori[$key] == '.00' || $dataori[$key] == '0.00') {
                        $dataori[$key] = '0';
                    }
                    if ($datafin[$key] == '.00' || $datafin[$key] == '0.00') {
                        $datafin[$key] = '0';
                    }

                    if (ltrim(trim((string) $dataori[$key]), "0") != ltrim(trim((string) $datafin[$key]), "0")) {
                        $iCambios++;
                        $arrValores[$iCambios] = array(
                            "'" . ltrim($data["matricula"], "0") . "'",
                            "'" . $key . "'",
                            "'" . $fecxact . "'",
                            "'" . $horxact . "'",
                            "'" . $codbarras . "'",
                            "'" . addslashes(ltrim(trim((string) $dataori[$key]), "0")) . "'",
                            "'" . addslashes(ltrim(trim((string) $datafin[$key]), "0")) . "'",
                            "'" . $_SESSION["generales"]["codigousuario"] . "'",
                            "'" . \funcionesGenerales::localizarIP() . "'",
                            "'" . $tt . "-actualizarMregEstInscritos" . "'",
                            "'" . $rec . "'"
                        );
                    }
                }
            }

            //
            if ($iCambios > 0) {
                $res = insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_campos_historicos_' . substr($fecxact, 0, 4), $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2('actualizarMregCamposHistoricos_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error grabando matrícula en mreg_campos_historicos_AAAA: ' . $_SESSION["generales"]["mensajeerror"]);
                    if ($cerrarmysql == 'si') {
                        $mysqli->close();
                    }
                    return false;
                }
            }
        }

        // Revisa información financiera
        if ($data["matricula"] != '' && isset($data["f"])) {
            foreach ($data["f"] as $fin) {
                $arrFin = retornarRegistroMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $fin["anodatos"] . "' and fechadatos='" . $fin["fechadatos"] . "'");
                if ($arrFin === false || empty($arrFin)) {
                    $accion = 'insertar';
                    if (!isset($fin["patrimonio"])) {
                        $fin["patrimonio"] = 0;
                    }
                } else {
                    $accion = 'no';
                    if (!isset($fin["patrimonio"])) {
                        $fin["patrimonio"] = 0;
                    }
                    if (!isset($fin["patnet"])) {
                        $fin["patnet"] = $fin["pattot"];
                    }

                    if (
                            $fin["personal"] != $arrFin["personal"] ||
                            $fin["patrimonio"] != $arrFin["patrimonio"] ||
                            $fin["personaltemp"] != $arrFin["pcttemp"] ||
                            $fin["actvin"] != $arrFin["actvin"] ||
                            $fin["actcte"] != $arrFin["actcte"] ||
                            $fin["actnocte"] != $arrFin["actnocte"] ||
                            $fin["actfij"] != $arrFin["actfij"] ||
                            $fin["fijnet"] != $arrFin["fijnet"] ||
                            $fin["actval"] != $arrFin["actval"] ||
                            $fin["actotr"] != $arrFin["actotr"] ||
                            $fin["acttot"] != $arrFin["acttot"] ||
                            $fin["actsinaju"] != $arrFin["actsinaju"] ||
                            $fin["invent"] != $arrFin["invent"] ||
                            $fin["pascte"] != $arrFin["pascte"] ||
                            $fin["paslar"] != $arrFin["paslar"] ||
                            $fin["pastot"] != $arrFin["pastot"] ||
                            $fin["patnet"] != $arrFin["patnet"] ||
                            $fin["paspat"] != $arrFin["paspat"] ||
                            $fin["balsoc"] != $arrFin["balsoc"] ||
                            $fin["ingope"] != $arrFin["ingope"] ||
                            $fin["ingnoope"] != $arrFin["ingnoope"] ||
                            $fin["gtoven"] != $arrFin["gasope"] ||
                            $fin["gtoadm"] != $arrFin["gasadm"] ||
                            $fin["gtoadm"] != $arrFin["gasnoope"] ||
                            $fin["cosven"] != $arrFin["cosven"] ||
                            $fin["gtoven"] != $arrFin["gtoven"] ||
                            $fin["gasint"] != $arrFin["gasint"] ||
                            $fin["gasimp"] != $arrFin["gasimp"] ||
                            $fin["utiope"] != $arrFin["utiope"] ||
                            $fin["utinet"] != $arrFin["utinet"]
                    ) {
                        $accion = 'regrabar';
                    }
                }

                if ($accion == 'insertar' || $accion == 'regrabar') {
                    $arrCampos = array(
                        'matricula',
                        'anodatos',
                        'fechadatos',
                        'personal',
                        'pcttemp',
                        'patrimonio',
                        'actvin',
                        'actcte',
                        'actnocte',
                        'actfij',
                        'fijnet',
                        'actval',
                        'actotr',
                        'acttot',
                        'actsinaju',
                        'invent',
                        'pascte',
                        'paslar',
                        'pastot',
                        'patnet',
                        'paspat',
                        'balsoc',
                        'ingope',
                        'ingnoope',
                        'gasope',
                        'gasadm',
                        'gasnoope',
                        'cosven',
                        'gtoven',
                        'gasint',
                        'gasimp',
                        'utiope',
                        'utinet',
                        'depamo',
                        'fecsincronizacion',
                        'horsincronizacion',
                        'compite360'
                    );
                    $arrValores = array(
                        "'" . ltrim($data["matricula"], "0") . "'",
                        "'" . $fin["anodatos"] . "'",
                        "'" . $fin["fechadatos"] . "'",
                        intval($fin["personal"]),
                        doubleval($fin["personaltemp"]),
                        doubleval($fin["patrimonio"]),
                        doubleval($fin["actvin"]),
                        doubleval($fin["actcte"]),
                        doubleval($fin["actnocte"]),
                        doubleval($fin["actfij"]),
                        doubleval($fin["fijnet"]),
                        doubleval($fin["actval"]),
                        doubleval($fin["actotr"]),
                        doubleval($fin["acttot"]),
                        doubleval($fin["actsinaju"]),
                        doubleval($fin["invent"]),
                        doubleval($fin["pascte"]),
                        doubleval($fin["paslar"]),
                        doubleval($fin["pastot"]),
                        doubleval($fin["pattot"]),
                        doubleval($fin["paspat"]),
                        doubleval($fin["balsoc"]),
                        doubleval($fin["ingope"]),
                        doubleval($fin["ingnoope"]),
                        doubleval($fin["gtoven"]),
                        doubleval($fin["gtoadm"]),
                        doubleval($fin["gtoadm"]), //
                        doubleval($fin["cosven"]), //
                        doubleval($fin["gtoven"]), //
                        doubleval($fin["gasint"]), //
                        doubleval($fin["gasimp"]), //
                        doubleval($fin["utiope"]),
                        doubleval($fin["utinet"]),
                        0, // Depamo
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'NO'"
                    );
                    if ($accion == 'insertar') {
                        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', $arrCampos, $arrValores);
                        if ($res === false) {
                            \logApi::general2('actualizarMregEstFinanciera_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error insertando matrícula en mreg_est_financiera: ' . $_SESSION["generales"]["mensajeerror"]);
                            if ($cerrarmysql == 'si') {
                                $mysqli->close();
                            }
                            return false;
                        }
                    }
                    if ($accion == 'regrabar') {
                        $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', $arrCampos, $arrValores, "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $fin["anodatos"] . "' and fechadatos='" . $fin["fechadatos"] . "'");
                        if ($res === false) {
                            \logApi::general2('actualizarMregEstFinanciera_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_financiera: ' . $_SESSION["generales"]["mensajeerror"]);
                            if ($cerrarmysql == 'si') {
                                $mysqli->close();
                            }
                            return false;
                        }
                    }

                    //
                    $arrCampos = array(
                        'matricula',
                        'campo',
                        'fecha',
                        'hora',
                        'codigobarras',
                        'datoanterior',
                        'datonuevo',
                        'usuario',
                        'ip',
                        'tipotramite',
                        'recibo'
                    );
                    $arrValores = array();
                    $iCambios = 0;
                    $fecxact = date("Ymd");
                    $horxact = date("His");
                    $arrFinFin = retornarRegistroMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $fin["anodatos"] . "' and fechadatos='" . $fin["fechadatos"] . "'");
                    foreach ($arrFinFin as $key => $valor) {
                        if (
                                $key != 'id' &&
                                !is_numeric($key) &&
                                $key != 'matricula' &&
                                $key != 'anodatos' &&
                                $key != 'fechadatos' &&
                                $key != 'fecsincronizacion' &&
                                $key != 'horsincronizacion' &&
                                $key != 'compite360'
                        ) {
                            if (!isset($arrFin[$key])) {
                                $arrFin[$key] = '';
                            }
                            if ($arrFin[$key] == null) {
                                $arrFin[$key] = '';
                            }
                            if (ltrim(trim((string) $arrFinFin[$key]), "0") != ltrim(trim((string) $arrFin[$key]), "0")) {
                                $iCambios++;
                                $arrValores[$iCambios] = array(
                                    "'" . ltrim($data["matricula"], "0") . "'",
                                    "'" . $key . '-' . $data["anodatos"] . '-' . $data["fechadatos"] . "'",
                                    "'" . $fecxact . "'",
                                    "'" . $horxact . "'",
                                    "'" . $codbarras . "'",
                                    "'" . addslashes(ltrim((string) trim($arrFin[$key]), "0")) . "'",
                                    "'" . addslashes(ltrim((string) trim($arrFinFin[$key]), "0")) . "'",
                                    "'" . $_SESSION["generales"]["codigousuario"] . "'",
                                    "'" . \funcionesGenerales::localizarIP() . "'",
                                    "'" . $tt . "'",
                                    "'" . $rec . "'"
                                );
                            }
                        }
                    }
                    if ($iCambios > 0) {
                        $res = insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_campos_historicos_' . substr($fecxact, 0, 4), $arrCampos, $arrValores);
                        if ($res === false) {
                            \logApi::general2('actualizarMregCamposHistoricos_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error grabando matrícula en mreg_campos_historicos: ' . $_SESSION["generales"]["mensajeerror"]);
                            if ($cerrarmysql == 'si') {
                                $mysqli->close();
                            }
                            return false;
                        }
                    }
                }
            }
        }

        //
        if ($data["matricula"] != '') {
            if ($tt == 'digitacionmatriculados' || $tt == 'actualizacionmatriculados') {
                if ($data["anodatos"] != '' && $data["fechadatos"] != '') {
                    $condicion = "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'";
                    $arrFin = retornarRegistroMysqliApi($mysqli, 'mreg_est_financiera', $condicion);
                    $arrCampos = array(
                        'matricula',
                        'anodatos',
                        'fechadatos',
                        'personal',
                        'pcttemp',
                        'patrimonio',
                        'actvin',
                        'actcte',
                        'actnocte',
                        'actfij',
                        'fijnet',
                        'actval',
                        'actotr',
                        'acttot',
                        'actsinaju',
                        'invent',
                        'pascte',
                        'paslar',
                        'pastot',
                        'patnet',
                        'paspat',
                        'balsoc',
                        'ingope',
                        'ingnoope',
                        'gasope',
                        'gasadm',
                        'gasnoope',
                        'cosven',
                        'gtoven',
                        'gasint',
                        'gasimp',
                        'utiope',
                        'utinet',
                        'depamo',
                        'fecsincronizacion',
                        'horsincronizacion',
                        'compite360'
                    );
                    $arrValores = array(
                        "'" . ltrim($data["matricula"], "0") . "'",
                        "'" . $data["anodatos"] . "'",
                        "'" . $data["fechadatos"] . "'",
                        intval($data["personal"]),
                        doubleval($data["personaltemp"]),
                        doubleval($data["patrimonio"]),
                        doubleval($data["actvin"]),
                        doubleval($data["actcte"]),
                        doubleval($data["actnocte"]),
                        doubleval($data["actfij"]),
                        doubleval($data["fijnet"]),
                        doubleval($data["actval"]),
                        doubleval($data["actotr"]),
                        doubleval($data["acttot"]),
                        doubleval($data["actsinaju"]),
                        doubleval($data["invent"]),
                        doubleval($data["pascte"]),
                        doubleval($data["paslar"]),
                        doubleval($data["pastot"]),
                        doubleval($data["pattot"]),
                        doubleval($data["paspat"]),
                        doubleval($data["balsoc"]),
                        doubleval($data["ingope"]),
                        doubleval($data["ingnoope"]),
                        doubleval($data["gtoven"]),
                        doubleval($data["gtoadm"]),
                        doubleval($data["gtoadm"]), //
                        doubleval($data["cosven"]), //
                        doubleval($data["gtoven"]), //
                        doubleval($data["gasint"]), //
                        doubleval($data["gasimp"]), //
                        doubleval($data["utiope"]),
                        doubleval($data["utinet"]),
                        0, // Depamo
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'NO'"
                    );
                    if ($arrFin && !empty($arrFin)) {
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', $arrCampos, $arrValores, $condicion);
                    } else {
                        insertarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', $arrCampos, $arrValores);
                    }
                }
            }
        }

        // 2017-08-17 : JINT: Actualiza la información  de patrimonios de esadl 
        if (!isset($data["anodatospatrimonio"])) {
            $data["anodatospatrimonio"] = '';
        }
        if (!isset($data["fechadatospatrimonio"])) {
            $data["fechadatospatrimonio"] = '';
        }
        if (!isset($data["patrimonioesadl"])) {
            $data["patrimonioesadl"] = 0;
        }

        //
        if ($data["matricula"] != '') {
            if ($data["anodatospatrimonio"] != '' && $data["fechadatospatrimonio"] != '' && doubleval($data["patrimonioesadl"]) != 0) {
                $condicion = "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatospatrimonio"] . "' and fechadatos='" . $data["fechadatospatrimonio"] . "'";
                $arrPat = retornarRegistroMysqliApi($mysqli, 'mreg_est_patrimonios', $condicion);
                $arrCampos = array(
                    'matricula',
                    'anodatos',
                    'fechadatos',
                    'patrimonio'
                );
                $arrValores = array(
                    "'" . $data["matricula"] . "'",
                    "'" . $data["anodatospatrimonio"] . "'",
                    "'" . $data["fechadatospatrimonio"] . "'",
                    doubleval($data["patrimonioesadl"])
                );
                if ($arrPat && !empty($arrPat)) {
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_est_patrimonios', $arrCampos, $arrValores, $condicion);
                } else {
                    insertarRegistrosMysqliApi($mysqli, 'mreg_est_patrimonios', $arrCampos, $arrValores);
                }
            }
        }

        // ********************************************************************************************* //
        // 2021-06-01: JINT: Campos adicionales del expdiente
        // ********************************************************************************************* //
        if ($data["matricula"] != '') {
            $txs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . $data["matricula"] . "'", "campo");
            $camposadi = array();
            foreach ($txs as $tx1) {
                $camposadi[$tx1["campo"]] = $tx1["contenido"];
            }
            borrarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . $data["matricula"] . "'");

            //
            if (!isset($data["nombrebase64"])) {
                $data["nombrebase64"] = '';
            }
            if (trim($data["nombrebase64"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'nombrebase64'", "'" . addslashes($data["nombrebase64"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["nombrebase64"])) {
                $camposadi["nombrebase64"] = '';
            }
            if ($camposadi["nombrebase64"] != $data["nombrebase64"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "nombrebase64", $camposadi["nombrebase64"], $data["nombrebase64"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            //
            if (!isset($data["siglabase64"])) {
                $data["siglabase64"] = '';
            }
            if (trim($data["siglabase64"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'siglabase64'", "'" . addslashes($data["siglabase64"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["siglabase64"])) {
                $camposadi["siglabase64"] = '';
            }
            if ($camposadi["siglabase64"] != $data["siglabase64"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "siglabase64", $camposadi["siglabase64"], $data["siglabase64"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            //
            if (!isset($data["domicilio_ong"])) {
                $data["domicilio_ong"] = '';
            }
            if (trim($data["domicilio_ong"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'domicilio_ong'", "'" . addslashes($data["domicilio_ong"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["domicilio_ong"])) {
                $camposadi["domicilio_ong"] = '';
            }
            if ($camposadi["domicilio_ong"] != $data["domicilio_ong"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "domicilio_ong", $camposadi["domicilio_ong"], $data["domicilio_ong"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            //
            if (!isset($data["tituloorganodirectivo"])) {
                $data["tituloorganodirectivo"] = '';
            }
            if (trim($data["tituloorganodirectivo"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'tituloorganodirectivo'", "'" . addslashes($data["tituloorganodirectivo"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["tituloorganodirectivo"])) {
                $camposadi["tituloorganodirectivo"] = '';
            }
            if ($camposadi["tituloorganodirectivo"] != $data["tituloorganodirectivo"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "tituloorganodirectivo", $camposadi["tituloorganodirectivo"], $data["tituloorganodirectivo"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            //
            if (!isset($data["siglaenconstitucion"])) {
                $data["siglaenconstitucion"] = '';
            }
            if (trim($data["siglaenconstitucion"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'siglaenconstitucion'", "'" . addslashes($data["siglaenconstitucion"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["siglaenconstitucion"])) {
                $camposadi["siglaenconstitucion"] = '';
            }
            if ($camposadi["siglaenconstitucion"] != $data["siglaenconstitucion"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "siglaenconstitucion", $camposadi["siglaenconstitucion"], $data["siglaenconstitucion"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // etnia
            if (!isset($data["etnia"])) {
                $data["etnia"] = '';
            }
            if (trim($data["etnia"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'etnia'", "'" . $data["etnia"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["etnia"])) {
                $camposadi["etnia"] = '';
            }
            if ($camposadi["etnia"] != $data["etnia"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "emprendimientosocial", $camposadi["emprendimientosocial"], $data["emprendimientosocial"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // emprendimiento social
            if (!isset($data["emprendimientosocial"])) {
                $data["emprendimientosocial"] = '';
            }
            if (trim($data["emprendimientosocial"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'emprendimientosocial'", "'" . $data["emprendimientosocial"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["emprendimientosocial"])) {
                $camposadi["emprendimientosocial"] = '';
            }
            if ($camposadi["emprendimientosocial"] != $data["emprendimientosocial"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "emprendimientosocial", $camposadi["emprendimientosocial"], $data["emprendimientosocial"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // emprendimiento social - categorias
            if (!isset($data["empsoccategorias"])) {
                $data["empsoccategorias"] = '';
            }
            if (trim((string) $data["empsoccategorias"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'empsoccategorias'", "'" . addslashes($data["empsoccategorias"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["empsoccategorias"])) {
                $camposadi["empsoccategorias"] = '';
            }
            if ($camposadi["empsoccategorias"] != $data["empsoccategorias"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "empsoccategorias", $camposadi["empsoccategorias"], $data["empsoccategorias"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            //
            if (!isset($data["empsoccategorias_otros"])) {
                $data["empsoccategorias_otros"] = '';
            }
            if (trim((string) $data["empsoccategorias_otros"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'empsoccategorias_otros'", "'" . addslashes($data["empsoccategorias_otros"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["empsoccategorias_otros"])) {
                $camposadi["empsoccategorias_otros"] = '';
            }
            if ($camposadi["empsoccategorias_otros"] != $data["empsoccategorias_otros"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "empsoccategorias_otros", $camposadi["empsoccategorias_otros"], $data["empsoccategorias_otros"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // emprendimiento social -  beneficiarios
            if (!isset($data["empsocbeneficiarios"])) {
                $data["empsocbeneficiarios"] = '';
            }
            if (trim((string) $data["empsocbeneficiarios"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'empsocbeneficiarios'", "'" . addslashes($data["empsocbeneficiarios"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["empsocbeneficiarios"])) {
                $camposadi["empsocbeneficiarios"] = '';
            }
            if ($camposadi["empsocbeneficiarios"] != $data["empsocbeneficiarios"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "empsocbeneficiarios", $camposadi["empsocbeneficiarios"], $data["empsocbeneficiarios"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            //
            if (!isset($data["empsocbeneficiarios_otros"])) {
                $data["empsocbeneficiarios_otros"] = '';
            }
            if (trim((string) $data["empsocbeneficiarios_otros"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'empsocbeneficiarios_otros'", "'" . addslashes($data["empsocbeneficiarios_otros"]) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["empsocbeneficiarios_otros"])) {
                $camposadi["empsocbeneficiarios_otros"] = '';
            }
            if ($camposadi["empsocbeneficiarios_otros"] != $data["empsocbeneficiarios_otros"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "empsocbeneficiarios_otros", $camposadi["empsocbeneficiarios_otros"], $data["empsocbeneficiarios_otros"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // participacionetnia
            if (!isset($data["participacionetnia"])) {
                $data["participacionetnia"] = '';
            }
            if (trim($data["participacionetnia"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'participacionetnia'", "'" . $data["participacionetnia"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["participacionetnia"])) {
                $camposadi["participacionetnia"] = '';
            }
            if ($camposadi["participacionetnia"] != $data["participacionetnia"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "participacionetnia", $camposadi["participacionetnia"], $data["participacionetnia"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // condicion especial ley 2219
            if (!isset($data["condiespe2219"])) {
                $data["condiespe2219"] = '';
            }
            if (trim($data["condiespe2219"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'condiespe2219'", "'" . $data["condiespe2219"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["condiespe2219"])) {
                $camposadi["condiespe2219"] = '';
            }
            if ($camposadi["condiespe2219"] != $data["condiespe2219"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "condiespe2219", $camposadi["condiespe2219"], $data["condiespe2219"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // Descripcion motivo cancelacion
            if (!isset($data["descripcionmotivocancelacion"])) {
                $data["descripcionmotivocancelacion"] = '';
            }
            if (trim($data["descripcionmotivocancelacion"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'descripcionmotivocancelacion'", "'" . $data["descripcionmotivocancelacion"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["descripcionmotivocancelacion"])) {
                $camposadi["descripcionmotivocancelacion"] = '';
            }
            if ($camposadi["descripcionmotivocancelacion"] != $data["descripcionmotivocancelacion"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "descripcionmotivocancelacion", $camposadi["descripcionmotivocancelacion"], $data["descripcionmotivocancelacion"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // codigos de responsabilidad tributaria
            $trespotri = '';
            if (!empty($data["codrespotri"])) {
                foreach ($data["codrespotri"] as $codres) {
                    if (trim($codres) != '') {
                        if (trim($trespotri) != '') {
                            $trespotri .= ',';
                        }
                        $trespotri .= sprintf("%02s", $codres);
                    }
                }
            }
            if (trim($trespotri) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'codrespotri'", "'" . addslashes($trespotri) . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["codrespotri"])) {
                $camposadi["codrespotri"] = '';
            }
            if ($camposadi["codrespotri"] != $trespotri) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "codrespotri", $camposadi["codrespotri"], $trespotri, $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // Extincion de dominio
            if (!isset($data["extinciondominio"])) {
                $data["extinciondominio"] = '';
            }
            if (trim($data["extinciondominio"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'extinciondominio'", "'" . $data["extinciondominio"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["extinciondominio"])) {
                $camposadi["extinciondominio"] = '';
            }
            if ($camposadi["extinciondominio"] != $data["extinciondominio"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "extinciondominio", $camposadi["extinciondominio"], $data["extinciondominio"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // Extincion de dominio fecha de inicio
            if (!isset($data["extinciondominiofechainicio"])) {
                $data["extinciondominiofechainicio"] = '';
            }
            if (trim($data["extinciondominiofechainicio"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'extinciondominiofechainicio'", "'" . $data["extinciondominiofechainicio"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["extinciondominiofechainicio"])) {
                $camposadi["extinciondominiofechainicio"] = '';
            }
            if ($camposadi["extinciondominiofechainicio"] != $data["extinciondominiofechainicio"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "extinciondominiofechainicio", $camposadi["extinciondominiofechainicio"], $data["extinciondominiofechainicio"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // Extincion de dominio fecha final
            if (!isset($data["extinciondominiofechafinal"])) {
                $data["extinciondominiofechafinal"] = '';
            }
            if (trim($data["extinciondominiofechafinal"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'extinciondominiofechafinal'", "'" . $data["extinciondominiofechafinal"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["extinciondominiofechafinal"])) {
                $camposadi["extinciondominiofechafinal"] = '';
            }
            if ($camposadi["extinciondominiofechafinal"] != $data["extinciondominiofechafinal"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "extinciondominiofechafinal", $camposadi["extinciondominiofechafinal"], $data["extinciondominiofechafinal"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }

            // Control de acceso al expediente para usuarios públicos
            if (!isset($data["ctrcontrolaccesopublico"])) {
                $data["ctrcontrolaccesopublico"] = '';
            }
            if (trim($data["ctrcontrolaccesopublico"]) != '') {
                $arrCampos1 = array('matricula', 'campo', 'contenido');
                $arrValores1 = array("'" . $data["matricula"] . "'", "'ctrcontrolaccesopublico'", "'" . $data["ctrcontrolaccesopublico"] . "'");
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', $arrCampos1, $arrValores1);
            }
            if (!isset($camposadi["ctrcontrolaccesopublico"])) {
                $camposadi["ctrcontrolaccesopublico"] = '';
            }
            if ($camposadi["ctrcontrolaccesopublico"] != $data["ctrcontrolaccesopublico"]) {
                insertarCamposHistoricosMysqliApi($mysqli, $_SESSION["generales"]["codigousuario"], $data["matricula"], "ctrcontrolaccesopublico", $camposadi["ctrcontrolaccesopublico"], $data["ctrcontrolaccesopublico"], $tt, $rec, $codbarras, \funcionesGenerales::localizarIP());
            }
        }

        if (!empty($data["propietarioacrear"])) {
            $arrCampos = array(
                'matricula',
                'codigocamara',
                'matriculapropietario',
                'tipopropiedad',
                'tipoidentificacion',
                'identificacion',
                'nit',
                'razonsocial',
                'apellido1',
                'apellido2',
                'nombre1',
                'nombre2',
                'dircom',
                'muncom',
                'telcom1',
                'telcom2',
                'telcom3',
                'emailcom',
                'dirnot',
                'munnot',
                'telnot1',
                'telnot2',
                'telnot3',
                'emailnot',
                'tipoidentificacionreplegal',
                'identificacionreplegal',
                'estado'
            );
            $arrValores = array(
                "'" . $data["matricula"] . "'",
                "'" . $data["propietarioacrear"]["codigocamara"] . "'",
                "'" . $data["propietarioacrear"]["matriculapropietario"] . "'",
                "'" . $data["propietarioacrear"]["tipopropiedad"] . "'",
                "'" . $data["propietarioacrear"]["tipoidentificacion"] . "'",
                "'" . $data["propietarioacrear"]["identificacion"] . "'",
                "'" . $data["propietarioacrear"]["nit"] . "'",
                "'" . addslashes($data["propietarioacrear"]["razonsocial"]) . "'",
                "'" . addslashes($data["propietarioacrear"]["apellido1"]) . "'",
                "'" . addslashes($data["propietarioacrear"]["apellido2"]) . "'",
                "'" . addslashes($data["propietarioacrear"]["nombre1"]) . "'",
                "'" . addslashes($data["propietarioacrear"]["nombre2"]) . "'",
                "'" . addslashes($data["propietarioacrear"]["dircom"]) . "'",
                "'" . $data["propietarioacrear"]["muncom"] . "'",
                "'" . $data["propietarioacrear"]["telcom1"] . "'",
                "'" . $data["propietarioacrear"]["telcom2"] . "'",
                "'" . $data["propietarioacrear"]["telcom3"] . "'",
                "'" . $data["propietarioacrear"]["emailcom"] . "'",
                "'" . addslashes($data["propietarioacrear"]["dirnot"]) . "'",
                "'" . $data["propietarioacrear"]["munnot"] . "'",
                "'" . $data["propietarioacrear"]["telnot1"] . "'",
                "'" . $data["propietarioacrear"]["telnot2"] . "'",
                "'" . $data["propietarioacrear"]["telnot3"] . "'",
                "'" . addslashes($data["propietarioacrear"]["emailnot"]) . "'",
                "''",
                "''",
                "'V'"
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', $arrCampos, $arrValores);
        }

        // ********************************************************************************************* //
        // 2020-06-03: JINT: Calcula hash
        // 2023-04-27: JINT: Si la razón social es diferente a la razón social recuperada por aquello
        // de la aplicación de complementos, la regraba
        // ********************************************************************************************* //
        $hashcontrolnuevo = '';
        if ($data["matricula"] != '') {
            $datax = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim($data["matricula"], "0"));
            if ($datax["nombre"] != $data["nombre"]) {
                if ($data["organizacion"] != '01' && $data["organizacion"] != '02') {
                    $arrCampos = array(
                        'razonsocial'
                    );
                    $arrValores = array(
                        "'" . $datax["nombre"] . "'"
                    );
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $data["matricula"] . "'");
                }
            }
            $hashcontrolnuevo = $datax["hashcontrolnuevo"];
        }

        // ********************************************************************************************* //
        // 2018-08-31: JINT: Por seguridad actualiza la fecha de mreg_est_inscritos
        // ********************************************************************************************* 
        if ($data["matricula"] != '') {
            $arrCampos = array(
                'fecactualizacion',
                'compite360',
                'rues',
                'ivc',
                'hashcontrol'
            );
            $arrValores = array(
                "'" . date("Ymd") . "'",
                "'NO'",
                "'NO'",
                "'NO'",
                "'" . $hashcontrolnuevo . "'"
            );
            regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $data["matricula"] . "'");
        }

        // ********************************************************************************************* //
        // 2020 04 21 - JINT - Si es matriculado y solo tiene actividades no comerciales
        // Y no se ha notificado previamente
        // Envía alerta de persona natural solo con actividades no comerciales
        // Y si la matrícula esta activa
        // ********************************************************************************************* 
        \funcionesRegistrales::alertarNoComerciales($mysqli, $data);

        if ($cerrarmysql == 'si') {
            $mysqli->close();
        }

        //
        return true;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $mat
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @return bool
     */
    public static function actualizarMregEstTextos($mysqli = null, $mat = '', $data = array(), $codbarras = '', $tt = '', $rec = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        //
        $nameLog = 'actualizarMregEstInscritosTextos_' . date("Ymd");

        // Valida que el expediente tenga diferencias

        $cerrarmysql = 'no';
        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarmysql = 'si';
        }

        foreach ($data as $codcer => $texto) {
            \logApi::general2($nameLog,$mat,'Encontro : ' . $codcer);
            $arrCamposH = array(
                'matricula',
                'campo',
                'fecha',
                'hora',
                'codigobarras',
                'datoanterior',
                'datonuevo',
                'usuario',
                'ip',
                'tipotramite',
                'recibo'
            );

            $certant = retornarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', "registro='REGMER' and expediente='" . $mat . "' and idcertifica='" . $codcer . "'");
            if ($certant && !empty($certant)) {
                $textoant = $certant["contenido"];
            }
            $arrValoresH = array(
                "'" . ltrim($data["matricula"], "0") . "'",
                "'" . $codcer . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $codbarras . "'",
                "'" . addslashes(trim((string) $textoant)) . "'",
                "'" . addslashes(trim((string) $texto)) . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . \funcionesGenerales::localizarIP() . "'",
                "'" . $tt . "'",
                "'" . $rec . "'"
            );
            $res1 = insertarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . date("Y"), $arrCamposH, $arrValoresH);

            //
            $arrCampos = array(
                'registro',
                'expediente',
                'idcertifica',
                'contenido',
                'fechaultimamodificacion',
                'horaultimamodificacion',
                'idusuario'
            );
            $arrValores = array(
                "'REGMER'",
                "'" . $mat . "'",
                "'" . $codcer . "'",
                "'" . addslashes($texto) . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'"
            );
            if ($certant === false || empty($certant)) {
                insertarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', $arrCampos, $arrValores);
            } else {
                regrabarRegistrosMysqliApi($mysqli, 'mreg_certificas_sii', $arrCampos, $arrValores, "registro='REGMER' and expediente='" . $mat . "' and idcertifica='" . $codcer . "'");
            }
        }

        //
        return true;
    }

}
