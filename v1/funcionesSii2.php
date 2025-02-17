<?php

class funcionesSii2 {

    public static function actualizarMregEstInscritos($dbx, $data, $codbarras = '', $tt = '', $rec = '') {
        require_once ('LogSii2.class.php');
        $nameLog = 'actualizarMregEstInscritos_' . date("Ymd");

        // Valida que el expediente tenga diferencias
        if (ltrim($data["matricula"], "0") == '' && ltrim($data["proponente"], "0") == '') {
            return true;
        }

        //
        $eOri = \funcionesSii2::retornarExpedienteMercantilSii($dbx, $data["matricula"], '', '', '', 'si', 'N');

        //
        if (!isset($data["fecexafiliacion"])) {
            $data["fecexafiliacion"] = $eOri["fecexafiliacion"];
        }
        if (!isset($data["latitud"])) {
            $data["latitud"] = $eOri["latitud"];
        }
        if (!isset($data["longitud"])) {
            $data["longitud"] = $eOri["longitud"];
        }
        if (!isset($data["econmixta"])) {
            $data["econmixta"] = $eOri["econmixta"];
        }
        if (!isset($data["desactiv"])) {
            $data["desactiv"] = $eOri["desactiv"];
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
        if (!isset($data["ctrdepuracion1727"])) {
            $data["ctrdepuracion1727"] = $eOri["ctrdepuracion1727"];
        }
        if (!isset($data["ctrfechadepuracion1727"])) {
            $data["ctrfechadepuracion1727"] = $eOri["ctrfechadepuracion1727"];
        }
        if (!isset($data["ctrben658"])) {
            $data["ctrben658"] = $eOri["ctrben658"];
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
        if (!isset($data["cantidadmujeres"])) {
            $data["cantidadmujeres"] = 0;
        }
        if (!isset($data["cantidadmujerescargosdirectivos"])) {
            $data["cantidadmujerescargosdirectivos"] = 0;
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

        //
        $grabarpor = '';
        $dataori = array();
        if ($data["matricula"] != '') {
            $dataori = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
            $grabarpor = 'matricula';
        } else {
            if ($data["proponente"] != '') {
                $dataori = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "proponente='" . ltrim($data["proponente"], "0") . "'");
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
                    if ($vinx["vinculoreplegal"] == '2170' ||
                            $vinx["vinculoreplegal"] == '4170') {
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

        //
        if (trim($data["nuevonombre"]) != '') {
            if (trim($data["nuevonombre"]) != $data["nombre"]) {
                $data["nombreanterior"] = $data["nombre"];
                $data["nombre"] = $data["nuevonombre"];
            }
        }

        // 2018-04-11 : JINT: Controles que se incluyen para pevenir que al actualizar el expediente
        // se elimine información vital
        if ($data["matricula"] != '') {
            if ($data["afiliado"] == '') {
                if (!isset($dataori["ctrafiliacion"])) {
                    $data["afiliado"] = $dataori["ctrafiliacion"];
                }
            }
            if ($data["dircom"] == '') {
                if (!isset($dataori["dircom"])) {
                    $data["dircom"] = $dataori["dircom"];
                }
            }
            if ($data["muncom"] == '') {
                if (!isset($dataori["muncom"])) {
                    $data["muncom"] = $dataori["muncom"];
                }
            }
            if ($data["proponente"] == '') {
                if (!isset($dataori["proponente"])) {
                    $data["proponente"] = $dataori["proponente"];
                }
            }
        }

        // 249
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
            'ctrexpedienteinactivo',
            'ctrestmatricula',
            'ctrestdatos',
            'ctrcertificardesde',
            'ctrestproponente',
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
            'fecactualizacion',
            'compite360',
            'rues',
            'ivc'
        );

        // 249
        $arrValores = array(
            "'" . ltrim($data["matricula"], "0") . "'",
            "'" . ltrim($data["proponente"], "0") . "'",
            "'" . $data["organizacion"] . "'",
            "'" . $data["categoria"] . "'",
            "'" . $data["naturaleza"] . "'",
            "'" . $data["nrocontrolreactivacion"] . "'",
            "'" . addslashes(trim($data["nombre"])) . "'",
            "'" . addslashes(trim($data["sigla"])) . "'",
            "'" . addslashes(trim($data["complementorazonsocial"])) . "'",
            "''", // Nombre comercial
            "'" . addslashes(trim($data["lggr"])) . "'",
            "'" . addslashes(trim($data["nom1"])) . "'",
            "'" . addslashes(trim($data["nom2"])) . "'",
            "'" . addslashes(trim($data["ape1"])) . "'",
            "'" . addslashes(trim($data["ape2"])) . "'",
            "'" . trim($data["tipoidentificacion"]) . "'",
            "'" . trim($data["identificacion"]) . "'",
            "'" . trim($data["sexo"]) . "'",
            "'" . trim($data["ideext"]) . "'",
            "'" . trim($data["idmunidoc"]) . "'",
            "'" . trim($data["fechanacimiento"]) . "'",
            "'" . trim($data["fecexpdoc"]) . "'",
            "'" . trim($data["paisexpdoc"]) . "'",
            "'" . trim($data["nacionalidad"]) . "'",
            "'" . trim($data["idetripaiori"]) . "'",
            "'" . trim($data["paiori"]) . "'",
            "'" . trim($data["idetriextep"]) . "'",
            "'" . trim($data["prerut"]) . "'",
            "'" . trim($data["admondian"]) . "'",
            "'" . trim($data["nit"]) . "'",
            "'" . trim($data["estadonit"]) . "'",
            "'" . trim($data["fechamatricula"]) . "'",
            "'" . trim($data["fecharenovacion"]) . "'",
            "'" . trim($data["ultanoren"]) . "'",
            "'" . trim($data["fechacancelacion"]) . "'",
            "'" . trim($data["motivocancelacion"]) . "'",
            "'" . trim($data["fechaconstitucion"]) . "'",
            "'" . trim($data["fechadisolucion"]) . "'",
            "'" . trim($data["fechaliquidacion"]) . "'",
            "'" . trim($data["estadotipoliquidacion"]) . "'",
            "'" . trim($data["fechavencimiento"]) . "'",
            "'" . trim($data["tamanoempresa"]) . "'",
            "'" . trim($data["emprendedor28"]) . "'",
            doubleval($data["pemprendedor28"]),
            "'" . trim($data["empresafamiliar"]) . "'",
            "'" . trim($data["procesosinnovacion"]) . "'",
            "'" . trim($data["tipogruemp"]) . "'",
            "'" . addslashes(trim($data["nombregruemp"])) . "'",
            "'" . addslashes(trim($data["vigcontrol"])) . "'",
            "'" . trim($data["vigifecini"]) . "'",
            "'" . trim($data["vigifecfin"]) . "'",
            "'" . trim($data["numperj"]) . "'",
            "'" . trim($data["fecperj"]) . "'",
            "'" . trim($data["idorigenperj"]) . "'",
            "'" . trim($numdocconst) . "'",
            "'" . trim($fecdocconst) . "'",
            "'" . trim($oridocconst) . "'",
            "'" . trim($data["fecmatant"]) . "'",
            "'" . trim($data["fecrenant"]) . "'",
            "'" . trim($data["camant"]) . "'",
            "'" . trim($data["munant"]) . "'",
            "'" . trim($data["ultanorenant"]) . "'",
            "'" . trim($data["matant"]) . "'",
            "'" . trim($data["benart7ant"]) . "'",
            "'" . trim($data["benley1780ant"]) . "'",
            "'" . addslashes(trim($data["dircom"])) . "'",
            "'" . trim($data["dircom_tipovia"]) . "'",
            "'" . trim($data["dircom_numvia"]) . "'",
            "'" . trim($data["dircom_apevia"]) . "'",
            "'" . trim($data["dircom_orivia"]) . "'",
            "'" . trim($data["dircom_numcruce"]) . "'",
            "'" . trim($data["dircom_apecruce"]) . "'",
            "'" . trim($data["dircom_oricruce"]) . "'",
            "'" . trim($data["dircom_numplaca"]) . "'",
            "'" . trim($data["dircom_complemento"]) . "'",
            "'" . trim($data["codigopostalcom"]) . "'",
            "'" . trim($data["codigozonacom"]) . "'",
            "'" . trim($data["barriocom"]) . "'",
            "'" . trim($data["muncom"]) . "'",
            "'" . trim($data["paicom"]) . "'",
            "'" . trim($data["telcom1"]) . "'",
            "'" . trim($data["telcom2"]) . "'",
            "'" . trim($data["celcom"]) . "'",
            "'" . addslashes(trim($data["emailcom"])) . "'",
            "'" . addslashes(trim($data["emailcom2"])) . "'",
            "'" . addslashes(trim($data["emailcom3"])) . "'",
            "'" . addslashes(trim($data["urlcom"])) . "'",
            "'" . (trim($data["aacom"])) . "'",
            "'" . (trim($data["faxcom"])) . "'",
            "'" . trim($data["numpredial"]) . "'",
            "'" . trim($data["latitud"]) . "'",
            "'" . trim($data["longitud"]) . "'",
            "'" . addslashes(trim($data["dirnot"])) . "'",
            "'" . trim($data["dirnot_tipovia"]) . "'",
            "'" . trim($data["dirnot_numvia"]) . "'",
            "'" . trim($data["dirnot_apevia"]) . "'",
            "'" . trim($data["dirnot_orivia"]) . "'",
            "'" . trim($data["dirnot_numcruce"]) . "'",
            "'" . trim($data["dirnot_apecruce"]) . "'",
            "'" . trim($data["dirnot_oricruce"]) . "'",
            "'" . trim($data["dirnot_numplaca"]) . "'",
            "'" . trim($data["dirnot_complemento"]) . "'",
            "'" . trim($data["codigopostalnot"]) . "'",
            "'" . trim($data["codigozonanot"]) . "'",
            "'" . trim($data["barrionot"]) . "'",
            "'" . trim($data["munnot"]) . "'",
            "'" . trim($data["painot"]) . "'",
            "'" . trim($data["telnot"]) . "'",
            "'" . trim($data["telnot2"]) . "'",
            "'" . trim($data["celnot"]) . "'",
            "'" . addslashes(trim($data["emailnot"])) . "'",
            "'" . addslashes(trim($data["urlnot"])) . "'",
            "'" . (trim($data["aanot"])) . "'",
            "'" . (trim($data["faxnot"])) . "'",
            "'" . (trim($data["tiposedeadm"])) . "'",
            "'" . trim($data["dircor"]) . "'",
            "'" . trim($data["muncor"]) . "'",
            "'" . trim($data["telcor"]) . "'",
            "'" . trim($data["telcor2"]) . "'",
            "'" . trim($data["ciius"][1]) . "'",
            "'" . trim($data["ciius"][2]) . "'",
            "'" . trim($data["ciius"][3]) . "'",
            "'" . trim($data["ciius"][4]) . "'",
            "'" . trim($data["ciius"][5]) . "'",
            "'" . trim($data["versionciiu"]) . "'",
            "'" . trim($data["feciniact1"]) . "'",
            "'" . trim($data["feciniact2"]) . "'",
            "'" . trim($data["codaduaneros"]) . "'",
            "'" . addslashes(substr(trim($data["desactiv"]), 0, 1000)) . "'",
            "'" . trim($data["fechaafiliacion"]) . "'",
            "'" . trim($data["fecactaaflia"]) . "'",
            "'" . trim($data["numactaaflia"]) . "'",
            "'" . trim($data["fecactaafliacan"]) . "'",
            "'" . trim($data["numactaafliacan"]) . "'",
            "'" . trim($data["ultanorenafi"]) . "'",
            "'" . trim($data["fechaultpagoafi"]) . "'",
            doubleval($data["valorultpagoafi"]),
            doubleval($data["saldoafiliado"]),
            "'" . trim($data["telaflia"]) . "'",
            "'" . trim($data["diraflia"]) . "'",
            "'" . trim($data["munaflia"]) . "'",
            "'" . trim($data["profaflia"]) . "'",
            "'" . trim($data["contaflia"]) . "'",
            "'" . trim($data["dircontaflia"]) . "'",
            "'" . trim($data["muncontaflia"]) . "'",
            "'" . trim($data["fecexafiliacion"]) . "'",
            "''", // ctrexpedienteinactivo
            "'" . trim($data["estadomatricula"]) . "'",
            "'" . trim($data["estadodatosmatricula"]) . "'",
            "'" . trim($data["certificardesde"]) . "'",
            "'" . trim($data["estadoproponente"]) . "'",
            "'" . trim($data["tipopropiedad"]) . "'",
            "'" . trim($ctrlibroscomercio) . "'",
            "'" . trim($data["afiliado"]) . "'",
            "''", // ctrvigilancia
            "''", // ctrestadoactiva
            "''", // ctrestadopreoperativa
            "''", // ctrestadoconcordato
            "''", // ctrestadointervenida
            "''", // ctrestadodisuelta
            "''", // ctrestadoenreestructuracion
            "'" . trim($data["estadocapturado"]) . "'",
            "'" . addslashes(trim($data["estadocapturadootros"])) . "'",
            "'" . trim($ctrembargo) . "'",
            "'" . trim($ctrembargotramite) . "'",
            "'" . trim($ctrrecursotramite) . "'",
            "'" . trim($data["impexp"]) . "'",
            "'" . trim($data["tipolocal"]) . "'",
            "'" . trim($data["ctrfun"]) . "'",
            "'" . trim($data["ctrubi"]) . "'",
            "'" . trim($data["clasegenesadl"]) . "'",
            "'" . trim($data["claseespesadl"]) . "'",
            "'" . trim($data["claseeconsoli"]) . "'",
            "'" . trim($data["ctrderpub"]) . "'",
            "'" . trim($data["ctrcodcoop"]) . "'",
            "'" . trim($data["ctrcodotras"]) . "'",
            "'" . trim($econmixta) . "'",
            intval($data["ctresacntasociados"]),
            intval($data["ctresacntmujeres"]),
            intval($data["ctresacnthombres"]),
            "'" . trim($data["ctresapertgremio"]) . "'",
            "'" . addslashes($data["ctresagremio"]) . "'",
            "'" . addslashes($data["ctresaacredita"]) . "'",
            "'" . trim($data["ctresaivc"]) . "'",
            "'" . trim($data["ctresainfoivc"]) . "'",
            "'" . trim($data["ctresaautregistro"]) . "'",
            "'" . addslashes($data["ctresaentautoriza"]) . "'",
            "'" . trim($data["ctresacodnat"]) . "'",
            "'" . trim($data["ctresadiscap"]) . "'",
            "'" . trim($data["ctresaetnia"]) . "'",
            "'" . addslashes($data["ctresacualetnia"]) . "'",
            "'" . trim($data["ctresadespvictreins"]) . "'",
            "'" . addslashes($data["ctresacualdespvictreins"]) . "'",
            "'" . trim($data["ctresaindgest"]) . "'",
            "'" . trim($data["ctresalgbti"]) . "'",
            "'" . trim($data["art4"]) . "'",
            "'" . trim($data["art7"]) . "'",
            "'" . trim($data["art50"]) . "'",
            "'" . trim($data["benley1780"]) . "'",
            "'" . trim($data["cumplerequisitos1780"]) . "'",
            "'" . trim($data["renunciabeneficios1780"]) . "'",
            "'" . trim($data["cumplerequisitos1780primren"]) . "'",
            "'" . trim($data["ctrcancelacion1429"]) . "'",
            "'" . trim($data["ctrdepuracion1727"]) . "'",
            "'" . trim($data["ctrfechadepuracion1727"]) . "'",
            "'" . trim($data["ctrben658"]) . "'",
            "'" . substr(trim($data["ctrmen"]), 0, 1) . "'",
            "'" . substr(trim($data["ctrmennot"]), 0, 1) . "'",
            doubleval($data["ivcarea"]),
            "'" . trim($data["ivcver"]) . "'",
            "'" . trim($data["ivccretip"]) . "'",
            "'" . trim($data["ivcali"]) . "'",
            "'" . trim($data["ivcqui"]) . "'",
            "'" . trim($data["ivcriesgo"]) . "'",
            "'" . trim($data["ivcenvio"]) . "'",
            "'" . trim($data["ivcsuelos"]) . "'",
            "'" . trim($data["ctrbic"]) . "'",
            intval($data["cantest"]),
            "'" . trim($idereplegal) . "'",
            "'" . addslashes($nomreplegal) . "'",
            "'" . trim($data["gruponiif"]) . "'",
            "'" . trim($data["niifconciliacion"]) . "'",
            "'" . trim($data["aportantesegsocial"]) . "'",
            "'" . trim($data["tipoaportantesegsocial"]) . "'",
            "'" . trim($data["anodatos"]) . "'",
            "'" . trim($data["fechadatos"]) . "'",
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
            "'" . trim($data["anodatoscap"]) . "'",
            "'" . trim($data["fechadatoscap"]) . "'",
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
            "'" . trim($nomsegcon) . "'",
            "'" . trim($carsegcon) . "'",
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
            "'" . trim($data["refcrenom1"]) . "'",
            "'" . trim($data["refcreofi1"]) . "'",
            "'" . trim($data["refcretel1"]) . "'",
            "'" . trim($data["refcrenom2"]) . "'",
            "'" . trim($data["refcreofi2"]) . "'",
            "'" . trim($data["refcretel2"]) . "'",
            "'" . trim($data["refcomnom1"]) . "'",
            "'" . trim($data["refcomdir1"]) . "'",
            "'" . trim($data["refcomtel1"]) . "'",
            "'" . trim($data["refcomnom2"]) . "'",
            "'" . trim($data["refcomdir2"]) . "'",
            "'" . trim($data["refcomtel2"]) . "'",
            "'" . trim($data["cpcodcam"]) . "'",
            "'" . trim($data["cpnummat"]) . "'",
            "'" . trim($data["cprazsoc"]) . "'",
            "'" . trim($data["cpnumnit"]) . "'",
            "'" . trim($data["cpdircom"]) . "'",
            "'" . trim($data["cpdirnot"]) . "'",
            "'" . trim($data["cpnumtel"]) . "'",
            "'" . trim($data["cpnumfax"]) . "'",
            "'" . trim($data["cpcodmun"]) . "'",
            "'" . trim($data["cpmunnot"]) . "'",
            "'" . trim($data["cpafili"]) . "'",
            doubleval($data["cpsaldo"]),
            "'" . trim($data["cpurenafi"]) . "'",
            "'" . date("Ymd") . "'",
            "'NO'", // Compite360
            "'NO'", // Rues
            "'NO'" // ivc
        );

        // Graba el registro en mreg_est_inscritos
        $idgrabar = 0;

        //
        if ($data["matricula"] != '') {
            $idreg = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
            if ($idreg && !empty($idreg)) {
                $idgrabar = $idreg["id"];
            }
        } else {
            if ($data["proponente"] != '') {
                $idreg = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "proponente='" . ltrim($data["proponente"], "0") . "'");
                if ($idreg && !empty($idreg)) {
                    $idgrabar = $idreg["id"];
                }
            }
        }

        if ($idgrabar == 0) {
            unset($_SESSION["expedienteactual"]);
            $control = 'insertar';
            $res = insertarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores);
            if ($res === false) {
                \logSii2::general2($nameLog, ltrim($data["matricula"], "0"), 'Error creando matrícula en mreg_est_inscritos : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }
        if ($idgrabar != 0) {
            unset($_SESSION["expedienteactual"]);
            $control = 'regrabar';
            $res = regrabarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "id=" . $idgrabar);
            if ($res === false) {
                \logSii2::general2($nameLog, ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_inscritos: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        //    
        $datafin = array();
        if ($data["matricula"] != '') {
            $datafin = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
        } else {
            if ($data["proponente"] != '') {
                $datafin = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "proponente='" . ltrim($data["proponente"], "0") . "'");
            }
        }

        // Actualiza códigos CAE
        if (ltrim($data["matricula"], "0") != '') {
            $arrY = retornarRegistrosMysqli2($dbx, 'mreg_anexoscae', "1=1", "codigocae");
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
                    borrarRegistrosMysqli2($dbx, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim($data["matricula"], "0") . "' and campo='" . $y["codigocae"] . "'");
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
                    insertarRegistrosBloqueMysqli2($dbx, 'mreg_est_campostablas', $arrCampos, $arrValores);
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
                if ($key != 'id' &&
                        !is_numeric($key) &&
                        $key != 'matricula' &&
                        $key != 'fecactualizacion' &&
                        $key != 'horactualizacion' &&
                        $key != 'fecsincronizacion' &&
                        $key != 'horsincronizacion' &&
                        $key != 'compite360' &&
                        $key != 'rues' &&
                        $key != 'ivc') {

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

                    if (ltrim(trim($dataori[$key]), "0") != ltrim(trim($datafin[$key]), "0")) {
                        $iCambios++;
                        $arrValores [$iCambios] = array(
                            "'" . ltrim($data["matricula"], "0") . "'",
                            "'" . $key . "'",
                            "'" . $fecxact . "'",
                            "'" . $horxact . "'",
                            "'" . $codbarras . "'",
                            "'" . addslashes(ltrim(trim($dataori[$key]), "0")) . "'",
                            "'" . addslashes(ltrim(trim($datafin[$key]), "0")) . "'",
                            "'" . $_SESSION["generales"]["codigousuario"] . "'",
                            "'" . \funcionesSii2::localizarIP() . "'",
                            "'" . $tt . "'",
                            "'" . $rec . "'"
                        );
                    }
                }
            }

            //
            if ($iCambios > 0) {
                $res = insertarRegistrosBloqueMysqli2($dbx, 'mreg_campos_historicos_' . substr($fecxact, 0, 4), $arrCampos, $arrValores);
                if ($res === false) {
                    \logSii2::general2('actualizarMregCamposHistoricos_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error grabando matrícula en mreg_campos_historicos_AAAA: ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
                }
            }
        }

        // Revisa información financiera
        if ($data["matricula"] != '') {
            if (isset($data["f"]) && !empty($data["f"])) {
                foreach ($data["f"] as $fin) {
                    $arrFin = retornarRegistroMysqli2($dbx, 'mreg_est_financiera', "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $fin["anodatos"] . "' and fechadatos='" . $fin["fechadatos"] . "'");
                    if ($arrFin === false || empty($arrFin)) {
                        $accion = 'insertar';
                    } else {
                        $accion = 'no';
                        if (!isset($fin["patrimonio"])) {
                            $fin["patrimonio"] = 0;
                        }
                        if ($fin["personal"] != $arrFin["personal"] ||
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
                            $res = insertarRegistrosMysqli2($dbx, 'mreg_est_financiera', $arrCampos, $arrValores);
                            if ($res === false) {
                                \logSii2::general2('actualizarMregEstFinanciera_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error insertando matrícula en mreg_est_financiera: ' . $_SESSION["generales"]["mensajeerror"]);
                                return false;
                            }
                        }
                        if ($accion == 'regrabar') {
                            $res = regrabarRegistrosMysqli2($dbx, 'mreg_est_financiera', $arrCampos, $arrValores, "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $fin["anodatos"] . "' and fechadatos='" . $fin["fechadatos"] . "'");
                            if ($res === false) {
                                \logSii2::general2('actualizarMregEstFinanciera_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_financiera: ' . $_SESSION["generales"]["mensajeerror"]);
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
                        $arrFinFin = retornarRegistroMysqli2($dbx, 'mreg_est_financiera', "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $fin["anodatos"] . "' and fechadatos='" . $fin["fechadatos"] . "'");
                        foreach ($arrFinFin as $key => $valor) {
                            if ($key != 'id' &&
                                    !is_numeric($key) &&
                                    $key != 'matricula' &&
                                    $key != 'anodatos' &&
                                    $key != 'fechadatos' &&
                                    $key != 'fecsincronizacion' &&
                                    $key != 'horsincronizacion' &&
                                    $key != 'compite360') {
                                if (!isset($arrFin[$key])) {
                                    $arrFin[$key] = '';
                                }
                                if ($arrFin[$key] == null) {
                                    $arrFin[$key] = '';
                                }
                                if (ltrim(trim($arrFinFin[$key]), "0") != ltrim(trim($arrFin[$key]), "0")) {
                                    $iCambios++;
                                    $arrValores [$iCambios] = array(
                                        "'" . ltrim($data["matricula"], "0") . "'",
                                        "'" . $key . '-' . $data["anodatos"] . '-' . $data["fechadatos"] . "'",
                                        "'" . $fecxact . "'",
                                        "'" . $horxact . "'",
                                        "'" . $codbarras . "'",
                                        "'" . addslashes(ltrim(trim($arrFin[$key]), "0")) . "'",
                                        "'" . addslashes(ltrim(trim($arrFinFin[$key]), "0")) . "'",
                                        "'" . $_SESSION["generales"]["codigousuario"] . "'",
                                        "'" . \funcionesSii2::localizarIP() . "'",
                                        "'" . $tt . "'",
                                        "'" . $rec . "'"
                                    );
                                }
                            }
                        }
                        if ($iCambios > 0) {
                            $res = insertarRegistrosBloqueMysqli2($dbx, 'mreg_campos_historicos_' . substr($fecxact, 0, 4), $arrCampos, $arrValores);
                            if ($res === false) {
                                \logSii2::general2('actualizarMregCamposHistoricos_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error grabando matrícula en mreg_campos_historicos: ' . $_SESSION["generales"]["mensajeerror"]);
                                return false;
                            }
                        }
                    }
                }
            }
        }

        //
        if ($data["matricula"] != '') {
            if ($tt == 'digitacionformulario' || $tt == 'actualizacionmatriculado') {
                if ($data["anodatos"] != '' && $data["fechadatos"] != '') {
                    $condicion = "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'";
                    $arrFin = retornarRegistroMysqli2($dbx, 'mreg_est_financiera', $condicion);
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
                        regrabarRegistrosMysqli2($dbx, 'mreg_est_financiera', $arrCampos, $arrValores, $condicion);
                    } else {
                        insertarRegistrosMysqli2($dbx, 'mreg_est_financiera', $arrCampos, $arrValores);
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
                $arrPat = retornarRegistroMysqli2($dbx, 'mreg_est_patrimonios', $condicion);
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
                    regrabarRegistrosMysqli2($dbx, 'mreg_est_patrimonios', $arrCampos, $arrValores, $condicion);
                } else {
                    insertarRegistrosMysqli2($dbx, 'mreg_est_patrimonios', $arrCampos, $arrValores);
                }
            }
        }

        // ********************************************************************************************* //
        // 2018-08-31: JINT: Por seguridad actualiza la fecha de mreg_est_inscritos
        // ********************************************************************************************* 
        if ($data["matricula"] != '') {
            $arrCampos = array(
                'fecactualizacion',
                'compite360',
                'rues',
                'ivc'
            );
            $arrValores = array(
                "'" . date("Ymd") . "'",
                "'NO'",
                "'NO'",
                "'NO'"
            );
            regrabarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $data["matricula"] . "'");
        }

        //
        return true;
    }

    public static function actualizarMregEstInformacionFinanciera($dbx = null, $data = array(), $codbarras = '', $tt = '', $rec = '') {

        $dataori = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
        if ($dataori === false || empty($dataori)) {
            $accion = 'insertar';
        } else {
            $accion = 'regrabar';
        }

        //
        $arrCampos = array(
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
            'fecactualizacion',
            'compite360',
            'rues',
            'ivc'
        );

        $arrValores = array(
            "'" . trim($data["anodatos"]) . "'",
            "'" . trim($data["fechadatos"]) . "'",
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
            doubleval($data["gasope"]), //
            doubleval($data["gasnoope"]), //
            doubleval($data["gtoven"]),
            doubleval($data["gtoadm"]),
            doubleval($data["utiope"]),
            doubleval($data["utinet"]),
            doubleval($data["cosven"]), //
            doubleval($data["depamo"]),
            doubleval($data["gasint"]),
            doubleval($data["gasimp"]),
            doubleval($data["actvin"]),
            "'" . date("Ymd") . "'", // fecha de actualizacion
            "'NO'", // Compite360
            "'NO'", // Rues
            "'NO'" // Ivc
        );

        // Graba el registro en mreg_est_inscritos
        if ($accion == 'insertar') {
            unset($_SESSION["expedienteactual"]);
            $res = insertarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores);
            if ($res === false) {
                \logSii2::general2('actualizarMregEstInformacionFinanciera_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error creando matrícula en mreg_est_inscritos : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        } else {
            unset($_SESSION["expedienteactual"]);
            $res = regrabarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . ltrim($data["matricula"], "0") . "'");
            if ($res === false) {
                \logSii2::general2('actualizarMregEstInformacionFinanciera_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_inscritos: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }


        // Revisa información financiera
        $arrFin = retornarRegistroMysqli2($dbx, 'mreg_est_financiera', "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'");
        if ($arrFin === false || empty($arrFin)) {
            $accion = 'insertar';
        } else {
            $accion = 'no';
            if ($data["personal"] != $arrFin["personal"] ||
                    $data["personaltemp"] != $arrFin["pcttemp"] ||
                    $data["actvin"] != $arrFin["actvin"] ||
                    $data["actcte"] != $arrFin["actcte"] ||
                    $data["actnocte"] != $arrFin["actnocte"] ||
                    $data["actfij"] != $arrFin["actfij"] ||
                    $data["fijnet"] != $arrFin["fijnet"] ||
                    $data["actval"] != $arrFin["actval"] ||
                    $data["actotr"] != $arrFin["actotr"] ||
                    $data["acttot"] != $arrFin["acttot"] ||
                    $data["actsinaju"] != $arrFin["actsinaju"] ||
                    $data["invent"] != $arrFin["invent"] ||
                    $data["pascte"] != $arrFin["pascte"] ||
                    $data["paslar"] != $arrFin["paslar"] ||
                    $data["pastot"] != $arrFin["pastot"] ||
                    $data["pattot"] != $arrFin["patnet"] ||
                    $data["paspat"] != $arrFin["paspat"] ||
                    $data["balsoc"] != $arrFin["balsoc"] ||
                    $data["ingope"] != $arrFin["ingope"] ||
                    $data["ingnoope"] != $arrFin["ingnoope"] ||
                    $data["gasope"] != $arrFin["gasope"] ||
                    $data["gtoadm"] != $arrFin["gasadm"] ||
                    $data["gasnoope"] != $arrFin["gasnoope"] ||
                    $data["cosven"] != $arrFin["cosven"] ||
                    $data["gtoven"] != $arrFin["gtoven"] ||
                    $data["gasint"] != $arrFin["gasint"] ||
                    $data["gasimp"] != $arrFin["gasimp"] ||
                    $data["utiope"] != $arrFin["utiope"] ||
                    $data["utinet"] != $arrFin["utinet"]
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
                doubleval($data["gasope"]),
                doubleval($data["gtoadm"]),
                doubleval($data["gasnoope"]), //
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
            if ($accion == 'insertar') {
                $res = insertarRegistrosMysqli2($dbx, 'mreg_est_financiera', $arrCampos, $arrValores);
                if ($res === false) {
                    \logSii2::general2('actualizarMregEstInformacionFinanciera_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error insertando matrícula en mreg_est_financiera: ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
                }
            }
            if ($accion == 'regrabar') {
                $res = regrabarRegistrosMysqli2($dbx, 'mreg_est_financiera', $arrCampos, $arrValores, "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'");
                if ($res === false) {
                    \logSii2::general2('actualizarMregEstInformacionFinanciera_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_financiera: ' . $_SESSION["generales"]["mensajeerror"]);
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
            $arrFinFin = retornarRegistroMysqli2($dbx, 'mreg_est_financiera', "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'");
            foreach ($arrFinFin as $key => $valor) {
                if ($key != 'id' &&
                        !is_numeric($key) &&
                        $key != 'matricula' &&
                        $key != 'anodatos' &&
                        $key != 'fechadatos' &&
                        $key != 'fecsincronizacion' &&
                        $key != 'horsincronizacion' &&
                        $key != 'compite360') {
                    if (!isset($arrFin[$key])) {
                        $arrFin[$key] = '';
                    }
                    if ($arrFin[$key] == null) {
                        $arrFin[$key] = '';
                    }
                    if (ltrim(trim($arrFinFin[$key]), "0") != ltrim(trim($arrFin[$key]), "0")) {
                        $iCambios++;
                        $arrValores [$iCambios] = array(
                            "'" . ltrim($data["matricula"], "0") . "'",
                            "'" . $key . '-' . $data["anodatos"] . '-' . $data["fechadatos"] . "'",
                            "'" . $fecxact . "'",
                            "'" . $horxact . "'",
                            "'" . $codbarras . "'",
                            "'" . addslashes(ltrim(trim($arrFin[$key]), "0")) . "'",
                            "'" . addslashes(ltrim(trim($arrFinFin[$key]), "0")) . "'",
                            "'" . $_SESSION["generales"]["codigousuario"] . "'",
                            "'" . \funcionesSii2::localizarIP() . "'",
                            "'" . $tt . "'",
                            "'" . $rec . "'"
                        );
                    }
                }
            }
            if ($iCambios > 0) {
                $res = insertarRegistrosBloqueMysqli2($dbx, 'mreg_campos_historicos_' . substr($fecxact, 0, 4), $arrCampos, $arrValores);
                if ($res === false) {
                    \logSii2::general2('actualizarMregCamposHistoricos_' . date("Ymd"), ltrim($data["matricula"], "0"), 'Error grabando matrícula en mreg_campos_historicos: ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
                }
            }
        }

        return true;
    }

    public static function actualizarMregEstInscritosCampo($dbx, $matricula, $campo, $contenido = '', $tipocampo = 'varchar', $codbarras = '', $tipotramite = '', $recibo = '') {


        // Valida que el expedienmte tenga diferencias
        if (ltrim($matricula, "0") == '') {
            return true;
        }

        //
        $datoori = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . $matricula . "'", $campo);
        if (ltrim(trim($datoori), "0") != ltrim(trim($contenido), "0")) {
            $arrCampos = array(
                $campo,
                'fecactualizacion',
                'compite360',
                'rues',
                'ivc'
            );
            if ($tipocampo == 'varchar') {
                $arrValores = array(
                    "'" . trim($contenido) . "'",
                    "'" . date("Ymd") . "'",
                    "'NO'",
                    "'NO'",
                    "'NO'"
                );
            }
            if ($tipocampo == 'int') {
                $arrValores = array(
                    intval($contenido),
                    "'" . date("Ymd") . "'",
                    "'NO'",
                    "'NO'",
                    "'NO'"
                );
            }
            if ($tipocampo == 'double') {
                $arrValores = array(
                    doubleval($contenido),
                    "'" . date("Ymd") . "'",
                    "'NO'",
                    "'NO'",
                    "'NO'"
                );
            }
            unset($_SESSION["expedienteactual"]);
            $res = regrabarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $matricula . "'");
            if ($res === false) {
                \logSii2::general2('actualizarMregEstInscritos_' . date("Ymd"), ltrim($matricula, "0"), 'Error regrabando en mreg_est_inscritos: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }

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
            $arrValores = array(
                "'" . ltrim($matricula, "0") . "'",
                "'" . $campo . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $codbarras . "'",
                "'" . addslashes($datoori) . "'",
                "'" . addslashes($contenido) . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . \funcionesSii2::localizarIP() . "'",
                "'" . $tipotramite . "'",
                "'" . $recibo . "'"
            );
            $res = insertarRegistrosMysqli2($dbx, 'mreg_campos_historicos_' . date("Y"), $arrCampos, $arrValores);
            if ($res === false) {
                logSii2::general2('actualizarMregCamposHistoricos_' . date("Ymd"), ltrim($matricula, "0"), 'Error grabando en mreg_campos_historicos: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        //
        return true;
    }

    public static function actualizarMregInscritosPendienteNuevoCertificado($dbx = null, $libro = null, $acto = null, $matricula = null) {
        $acto = retornarRegistroMysqli2($dbx, 'mreg_actos', "idlibro='" . $libro . "' and idacto='" . $acto . "'");
        if ($acto["idgrupoacto"] != '002' && // Cancelaciones
                $acto["idgrupoacto"] != '003' && // Mutaciones
                $acto["idgrupoacto"] != '004' && // Libros
                $acto["idgrupoacto"] != '046' && // Sitios web
                $acto["idgrupoacto"] != '047' && // habilitación del servicio de transporte
                $acto["idgrupoacto"] != '052' && // Proponente
                $acto["idgrupoacto"] != '999' // otros
        ) {
            if (ltrim($matricula, "0") != '') {
                $exptx = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . ltrim($matricula, "0") . "'", "matricula,organizacion,categoria,ctrestmatricula,pendiente_ajuste_nuevo_formato,fecha_pendiente_ajuste_nuevo_formato");
                if ($exptx &&
                        ($exptx["ctrestmatricula"] == 'MA' ||
                        $exptx["ctrestmatricula"] == 'IA' ||
                        $exptx["ctrestmatricula"] == 'MI' ||
                        $exptx["ctrestmatricula"] == 'II' ||
                        $exptx["ctrestmatricula"] == 'MR' ||
                        $exptx["ctrestmatricula"] == 'IR')
                ) {
                    if ($exptx["organizacion"] > '02' && ($exptx["categoria"] == '1' || $exptx["categoria"] == '2')) {
                        $marcar = 'no';
                        if ($exptx["organizacion"] == '12' || $exptx["organizacion"] == '14') {
                            if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL') && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL != '' && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL <= date("Ymd")) {
                                $marcar = 'si';
                            } else {
                                if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI != '' && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI <= date("Ymd")) {
                                    $marcar = 'si';
                                }
                            }
                        }
                        if ($marcar == 'si') {
                            if ($exptx["pendiente_ajuste_nuevo_formato"] == '') {
                                $arrCampos = array(
                                    'pendiente_ajuste_nuevo_formato',
                                    'fecha_pendiente_ajuste_nuevo_formato'
                                );
                                $arrValores = array(
                                    "'P'",
                                    "'" . date("Ymd") . ' ' . date("His") . "'"
                                );
                                regrabarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . ltrim($matricula, "0") . "'");
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public static function actualizarMregLiquidacionFlujo($dbx, $tt, $numliq, $idsol, $est, $numrec, $numope, $numrad, $fecrec, $horrec) {
        $arrCampos = array(
            'tipotramite',
            'idliquidacion',
            'idsolicitudpago',
            'idestadoflujo',
            'numerorecibo',
            'numerooperacion',
            'numeroradicacion',
            'fecharecibo',
            'horarecibo'
        );
        $arrValues = array(
            "'" . $tt . "'",
            $numliq,
            "'" . $idsol . "'",
            "'" . $est . "'",
            "'" . $numrec . "'",
            "'" . $numope . "'",
            "'" . $numrad . "'",
            "'" . $fecrec . "'",
            "'" . $horrec . "'"
        );

        insertarRegistrosMysqli2($dbx, 'mreg_liquidacionflujo', $arrCampos, $arrValues);
    }

    public static function actualizarMregLiquidacionPagoElectronico($dbx, $liqui, $est, $nomcli, $idtipide, $dir, $tel, $mun, $email, $ide, $pagefe, $pagche, $pagvis, $pagach, $pagmas, $pagame, $pagcre, $pagdin, $pagtdeb, $codban, $numche, $numaut, $caj, $numope, $numrec, $fecrec, $horrec, $codbar, $xfra, $xnfra, $formapago = '05') {

        $arrCampos = array(
            'idestado',
            'idtipoidentificacioncliente',
            'identificacioncliente',
            'nombrecliente',
            'email',
            'direccion',
            'idmunicipio',
            'telefono',
            'pagoefectivo',
            'pagocheque',
            'pagovisa',
            'pagomastercard',
            'pagoamerican',
            'pagocredencial',
            'pagodiners',
            'pagotdebito',
            'idformapago',
            'numerorecibo',
            'numerooperacion',
            'fecharecibo',
            'horarecibo',
            'idfranquicia',
            'nombrefranquicia',
            'numeroautorizacion',
            'idcodban',
            'nombrebanco',
            'numerocheque',
            'numeroradicacion'
        );
        $arrValues = array(
            "'" . $est . "'",
            "'" . $idtipide . "'",
            "'" . $ide . "'",
            "'" . $nomcli . "'",
            "'" . $email . "'",
            "'" . addslashes(utf8_encode($dir)) . "'",
            "'" . $mun . "'",
            "'" . $tel . "'",
            $pagefe,
            $pagche,
            $pagvis,
            $pagmas,
            $pagame,
            $pagcre,
            $pagdin,
            $pagtdeb,
            "'" . $formapago . "'", // idformapago (pago electr&oacute;nico)
            "'" . $numrec . "'",
            "'" . $numope . "'",
            "'" . $fecrec . "'",
            "'" . $horrec . "'",
            "'" . $xfra . "'", // idfranquicia
            "'" . $xnfra . "'", // nombre franquicia
            "'" . $numaut . "'",
            "'" . $codban . "'",
            "''", // nombre banco
            "'" . $numche . "'",
            "'" . $codbar . "'"
        );
        regrabarRegistrosMysqli2($dbx, 'mreg_liquidacion', $arrCampos, $arrValues, "idliquidacion=" . $liqui);
        return true;
    }

    public static function actualizarMregNotificacionesParaEnviarEmailSii(
            $mysqli, $tnot, $rad, $dev, $ope, $rec, $lib, $reg, $dup, $idc, $ide, $mat, $pro, $nom, $ema, $det, $fpro, $hpro, $fnot, $hnot, $est, $obs = '', $bandeja = ''
    ) {

        ini_set('memory_limit', '1024M');
        require_once ('genPdfImagenes.php');

        //
        $arrCampos = array(
            'tiponotificacion',
            'radicacion',
            'devolucion',
            'operacion',
            'recibo',
            'libro',
            'registro',
            'dupli',
            'idclase',
            'numid',
            'matricula',
            'proponente',
            'nombre',
            'email',
            'detallenotificacion',
            'fechaprogramacion',
            'horaprogramacion',
            'fechanotificacion',
            'horanotificacion',
            'idestadonotificacion',
            'observaciones'
        );
        $arrValores = array(
            "'" . $tnot . "'",
            "'" . $rad . "'",
            "'" . $dev . "'",
            "'" . $ope . "'",
            "'" . $rec . "'",
            "'" . $lib . "'",
            "'" . $reg . "'",
            "'" . $dup . "'",
            "'" . $idc . "'",
            "'" . $ide . "'",
            "'" . $mat . "'",
            "'" . $pro . "'",
            "'" . addslashes($nom) . "'",
            "'" . addslashes($ema) . "'",
            "'" . addslashes($det) . "'",
            "'" . $fpro . "'",
            "'" . $hpro . "'",
            "'" . $fnot . "'",
            "'" . $hnot . "'",
            "'" . $est . "'",
            "'" . addslashes($obs) . "'"
        );
        insertarRegistrosMysqli2($mysqli, 'mreg_notificaciones_para_enviar_email', $arrCampos, $arrValores);

        // Crea el pdf de la notifcicación
        $_SESSION["generales"]["idanexogenerado"] = generarPdfNotificacionEmailSii($mysqli, $tnot, $rad, $dev, $ope, $rec, $lib, $reg, $dup, $idc, $ide, $mat, $pro, $nom, $ema, $det, $fpro, $hpro, $fnot, $hnot, $est, $obs, $bandeja);

        // Valida que el pdf haya quedado bien generado
        // Busca en el texto del pdf la secuencia "Fecha y hora de programaci"
        // si la encuentra deuelve true de lo controario false.
        $retornar = true;
        $anx = retornarRegistroMysqli2($mysqli, 'mreg_radicacionesanexos', "idanexo=" . $_SESSION["generales"]["idanexogenerado"]);
        if (file_exists('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $anx["path"])) {
            $resv = validarPdfSii2('../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $anx["path"], 'Fecha y hora de programaci');
            if (!$resv) {
                $retornar = false;
            }
        }

        //
        return $retornar;
    }

    public static function actualizarMregRadicacionesDatos($dbx, $numrad, $sec, $tipotra, $exp, $est, $xml) {
        $arrCampos = array(
            'idradicacion',
            'secuencia',
            'tipotramite',
            'expediente',
            'idestado',
            'xml'
        );
        $arrValues = array(
            "'" . ltrim($numrad, "0") . "'",
            "'" . $sec . "'",
            "'" . $tipotra . "'",
            "'" . $exp . "'",
            "'" . $est . "'",
            "'" . addslashes($xml) . "'"
        );
        $query = "idradicacion='" . ltrim($numrad, "0") . "' and secuencia='" . $sec . "'";
        $result = borrarRegistrosMysqli2($dbx, 'mreg_radicacionesdatos', $query);
        if ($result === false) {
            return false;
        } else {
            $result = insertarRegistrosMysqli2($dbx, 'mreg_radicacionesdatos', $arrCampos, $arrValues);
            if ($result === false) {
                return false;
            } else {
                return true;
            }
        }
    }

    public static function actualizarPilaSmsSii($mysqli, $cel, $tip, $rec, $cba, $ins, $dev, $exp, $mat, $pro, $ide, $nom, $txt, $obs = '', $bandeja = '') {

        //
        require_once ('genPdfImagenes.php');

        //
        $arrCampos = array(
            'celular',
            'texto',
            'recibo',
            'codigobarras',
            'inscripcion',
            'devolucion',
            'expediente',
            'matricula',
            'proponente',
            'identificacion',
            'nombre',
            'fechaprogramacion',
            'horaprogramacion',
            'tipo',
            'estado',
            'fechaenvio',
            'horaenvio'
        );

        //
        $arrValores = array(
            "'" . $cel . "'",
            "'" . addslashes($txt) . "'",
            "'" . $rec . "'",
            "'" . $cba . "'",
            "'" . $ins . "'",
            "'" . $dev . "'",
            "'" . ltrim($exp, "0") . "'",
            "'" . ltrim($mat, "0") . "'",
            "'" . ltrim($pro, "0") . "'",
            "'" . ltrim($ide, "0") . "'",
            "'" . addslashes($nom) . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $tip . "'",
            "'1'",
            "''",
            "''"
        );

        insertarRegistrosMysqli2($mysqli, 'pila_sms', $arrCampos, $arrValores);

        // Crea el pdf de la notificación sipref 
        if ($tip != '6' && $tip != '7' && $tip != '90') {
            $id = generarPdfNotificacionSmsSii($mysqli, $cel, $tip, $rec, $cba, $ins, $dev, $exp, $mat, $pro, $ide, $nom, $txt, $obs, $bandeja);
        }
        return true;
    }

    /**
     * 
     * @param type $dbx
     * @param type $clave
     * @param type $contenido
     */
    public static function actualizarMregSecuenciasSii($dbx, $clave, $contenido) {

        $arrTem = retornarRegistroMysqli2($dbx, 'mreg_secuencias', "id='" . $clave . "'");

        //
        if ($arrTem === false || empty($arrTem)) {

            $arrCampos = array(
                'id',
                'secuencia'
            );
            $arrValores = array(
                "'" . $clave . "'",
                $contenido
            );
            insertarRegistrosMysqli2($dbx, 'mreg_secuencias', $arrCampos, $arrValores);
        } else {

            $arrCampos = array(
                'id',
                'secuencia'
            );
            $arrValores = array(
                "'" . $clave . "'",
                $contenido
            );
            regrabarRegistrosMysqli2($dbx, 'mreg_secuencias', $arrCampos, $arrValores, "id='" . $clave . "'");
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $clave
     * @param type $contenido
     */
    public static function actualizarMregSecuenciasAsentarReciboSii($mysqli, $clave, $contenido) {

        $arrTem = retornarRegistroMysqli2($mysqli, 'mreg_secuencias', "id='" . $clave . "'");

        //
        if ($arrTem === false || empty($arrTem)) {

            $arrCampos = array(
                'id',
                'secuencia'
            );
            $arrValores = array(
                "'" . $clave . "'",
                $contenido
            );
            insertarRegistrosMysqli2($mysqli, 'mreg_secuencias', $arrCampos, $arrValores);
        } else {

            $arrCampos = array(
                'id',
                'secuencia'
            );
            $arrValores = array(
                "'" . $clave . "'",
                $contenido
            );
            regrabarRegistrosMysqli2($mysqli, 'mreg_secuencias', $arrCampos, $arrValores, "id='" . $clave . "'");
        }
    }

    /**
     * 
     * @param type $dbx
     * @param type $cri
     * @param type $ide
     * @param type $clave
     * @param type $valor
     * @param type $recibo
     * @param type $numoperacion
     * @param type $servicio
     * @param type $cantidad
     * @param type $detalle
     * @param type $ip
     * @param type $usuario
     * @param type $expediente
     * @param type $email
     * @param type $nombre
     * @param type $celular
     * @param type $direccion
     * @param type $municipio
     * @param type $tipousuario
     * @param type $telefono
     * @param type $nom1
     * @param type $nom2
     * @param type $ape1
     * @param type $ape2
     * @return string
     */
    public static function actualizarPrepago($dbx, $cri, $ide, $clave = '', $valor = '', $recibo = '', $numoperacion = '', $servicio = '', $cantidad = '', $detalle = '', $ip = '', $usuario = '', $expediente = '', $email = '', $nombre = '', $celular = '', $direccion = '', $municipio = '', $tipousuario = '', $telefono = '', $nom1 = '', $nom2 = '', $ape1 = '', $ape2 = '') {

        $resultado = array();
        $resultado["codigoError"] = '0000';
        $resultado["msgError"] = '';
        $resultado["identificacion"] = $ide;
        $resultado["nombre"] = '';
        $resultado["nombre1"] = '';
        $resultado["nombre2"] = '';
        $resultado["apellido1"] = '';
        $resultado["apellido2"] = '';
        $resultado["email"] = '';
        $resultado["telefono"] = '';
        $resultado["celular"] = '';
        $resultado["direccion"] = '';
        $resultado["municipio"] = '';
        $resultado["clave"] = '';
        $resultado["saldoprepago"] = '0';
        $resultado["saldoafiliado"] = '0';
        $resultado["kardex"] = array();
        $resultado["saldos"] = array();

        if ($cri == 'S' || $cri == 'R') { // Retorna los datos y el saldo de un prepago
            $prep = retornarRegistroMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if ($prep && !empty($prep)) {
                $resultado["nombre"] = $prep["nombre"];
                $resultado["nombre1"] = $prep["nombre1"];
                $resultado["nombre2"] = $prep["nombre2"];
                $resultado["apellido1"] = $prep["apellido1"];
                $resultado["apellido2"] = $prep["apellido2"];
                $resultado["email"] = $prep["email"];
                $resultado["telefono"] = $prep["telefono"];
                $resultado["celular"] = $prep["celular"];
                $resultado["direccion"] = $prep["direccion"];
                $resultado["municipio"] = $prep["municipio"];
                $resultado["clave"] = $prep["clave"];
                $resultado["saldoprepago"] = '0';
                $resultado["saldoafiliado"] = '0';
                $preps = retornarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
                if ($preps && !empty($preps)) {
                    foreach ($preps as $p) {
                        if ($p["tipomov"] == 'C') {
                            $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                        } else {
                            $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                        }
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'K') { // retorna el kardex de un prepago
            $prep = retornarRegistroMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if ($prep && !empty($prep)) {
                $resultado["nombre"] = $prep["nombre"];
                $resultado["nombre1"] = $prep["nombre1"];
                $resultado["nombre2"] = $prep["nombre2"];
                $resultado["apellido1"] = $prep["apellido1"];
                $resultado["apellido2"] = $prep["apellido2"];
                $resultado["email"] = $prep["email"];
                $resultado["telefono"] = $prep["telefono"];
                $resultado["celular"] = $prep["celular"];
                $resultado["direccion"] = $prep["direccion"];
                $resultado["municipio"] = $prep["municipio"];
                $resultado["clave"] = $prep["clave"];
                $resultado["saldoprepago"] = '0';
                $resultado["saldoafiliado"] = '0';
                $preps = retornarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
                if ($preps && !empty($preps)) {
                    $ik = 0;
                    foreach ($preps as $p) {
                        if ($p["tipomov"] == 'C') {
                            $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                        } else {
                            $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                        }
                        $ik++;
                        $resultado["kardex"][$i]["fecha"] = $p["fecha"];
                        $resultado["kardex"][$i]["hora"] = $p["hora"];
                        $resultado["kardex"][$i]["usuario"] = $p["usuario"];
                        $resultado["kardex"][$i]["operador"] = $p["operador"];
                        $resultado["kardex"][$i]["tipomov"] = $p["tipomov"];
                        $resultado["kardex"][$i]["servicio"] = $p["servicio"];
                        $resultado["kardex"][$i]["expediente"] = $p["expediente"];
                        $resultado["kardex"][$i]["cantidad"] = $p["cantidad"];
                        $resultado["kardex"][$i]["valor"] = $p["valor"];
                        $resultado["kardex"][$i]["recibo"] = $p["recibo"];
                        $resultado["kardex"][$i]["operacion"] = '';
                        $resultado["kardex"][$i]["concepto"] = $p["concepto"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'L') { // retorna la lista de prepagos y su saldo
            $i = 0;
            $prep = retornarRegistrosMysqli2($dbx, 'mreg_prepagos', "1=1", "identificacion");
            if ($prep && !empty($prep)) {
                foreach ($prep as $p1) {
                    $i++;
                    $resultado["saldos"][$i]["identificacion"] = $p1["identificacion"];
                    $resultado["saldos"][$i]["nombre"] = $p1["nombre"];
                    $resultado["saldos"][$i]["email"] = $p1["email"];
                    $resultado["saldos"][$i]["telefono"] = $p1["telefono"];
                    $resultado["saldos"][$i]["saldo"] = 0;
                    $preps = retornarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', "identificacion='" . $p1["identificacion"] . "'", "fecha,hora");
                    if ($preps && !empty($preps)) {
                        $ik = 0;
                        foreach ($preps as $p) {
                            if ($p["tipomov"] == 'C') {
                                $resultado["saldos"][$i]["saldo"] = $resultado["saldos"][$i]["saldo"] + $p["valor"];
                            } else {
                                $resultado["saldos"][$i]["saldo"] = $resultado["saldos"][$i]["saldo"] - $p["valor"];
                            }
                        }
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'A') { // Verifica la clave de un prepago
            $prep = retornarRegistroMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if (!$prep || empty($prep)) {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE PREPAGO PARA LA IDENTIFICACION';
                return $resultado;
            }
            if ($tipousuario != '3') {
                if (md5($clave) != $prep["clave"]) {
                    $resultado["codigoError"] = '0020';
                    $resultado["msgError"] = 'CLAVE NO CORRESPONDE';
                    return $resultado;
                }
            }
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["celular"] = $prep["celular"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $prep["clave"];
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'P') { // Cambio de clave
            $prep = retornarRegistroMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if (!$prep || empty($prep)) {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE PREPAGO PARA LA IDENTIFICACION';
                return $resultado;
            }
            $arrCampos = array('clave');
            $arrValores = array("'" . md5($clave) . "'");
            regrabarRegistrosMysqli2($dbx, 'mreg_prepagos', $arrCampos, $arrValores, "identificacion='" . $ide . "'");
            $prep = retornarRegistroMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["celular"] = $prep["celular"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $clave;
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'C') { //Carga al prepgo
            $arrCampos = array(
                'identificacion',
                'nombre',
                'nombre1',
                'nombre2',
                'apellido1',
                'apellido2',
                'direccion',
                'telefono',
                'celular',
                'email',
                'municipio',
                'clave'
            );
            $arrValores = array(
                "'" . $ide . "'",
                "'" . addslashes($nombre) . "'",
                "'" . addslashes($nom1) . "'",
                "'" . addslashes($nom2) . "'",
                "'" . addslashes($ape1) . "'",
                "'" . addslashes($ape2) . "'",
                "'" . addslashes($direccion) . "'",
                "'" . $telefono . "'",
                "'" . $celular . "'",
                "'" . addslashes($email) . "'",
                "'" . $municipio . "'",
                "'" . md5($clave) . "'"
            );
            if (contarRegistrosMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'") == 0) {
                insertarRegistrosMysqli2($dbx, 'mreg_prepagos', $arrCampos, $arrValores);
            } else {
                regrabarRegistrosMysqli2($dbx, 'mreg_prepagos', $arrCampos, $arrValores, "identificacion='" . $ide . "'");
            }
            $arrCampos = array(
                'identificacion',
                'fecha',
                'hora',
                'ip',
                'operador',
                'usuario',
                'tipomov',
                'servicio',
                'concepto',
                'expediente',
                'recibo',
                'cantidad',
                'valor'
            );
            $arrValores = array(
                "'" . $ide . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $ip . "'",
                "''", // Operador
                "'" . $usuario . "'",
                "'C'",
                "'" . $servicio . "'",
                "'" . $detalle . "'",
                "''",
                "'" . $recibo . "'",
                0,
                $valor
            );
            insertarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', $arrCampos, $arrValores);
            $prep = retornarRegistroMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["celular"] = $prep["celular"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $prep["clave"];
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'D') { //Descuenta del prepgo
            $prep = retornarRegistroMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if (!$prep || empty($prep)) {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE PREPAGO PARA LA IDENTIFICACION';
                return $resultado;
            }
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["celular"] = $prep["celular"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $prep["clave"];
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            if ($resultado["saldoprepago"] < $valor) {
                $resultado["codigoError"] = '0021';
                $resultado["msgError"] = 'NO EXISTE SALDO SUFICIENTE';
                return $resultado;
            }

            $arrCampos = array(
                'identificacion',
                'fecha',
                'hora',
                'ip',
                'operador',
                'usuario',
                'tipomov',
                'servicio',
                'concepto',
                'expediente',
                'recibo',
                'cantidad',
                'valor'
            );
            $arrValores = array(
                "'" . $ide . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $ip . "'",
                "''", // Operador
                "'" . $usuario . "'",
                "'D",
                "'" . $servicio . "'",
                "'" . $detalle . "'",
                "'" . $expediente . "'",
                "'" . $recibo . "'",
                $cantidad,
                $valor
            );
            insertarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', $arrCampos, $arrValores);
            $prep = retornarRegistroMysqli2($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["celular"] = $prep["celular"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $prep["clave"];
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqli2($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'X') { // Resta del cupo de afiliado
            $expediente = ltrim($expediente, "0");

            $afil = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . $expediente . "'");
            if ($afil === false || empty($afil)) {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE AFILIADO PARA DESCONTAR';
                return $resultado;
            }
            if ($afil["ctrafiliacion"] != '1') {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE AFILIADO PARA DESCONTAR';
                return $resultado;
            }
            if ($afil["saldoaflia"] < $valor) {
                $resultado["codigoError"] = '0021';
                $resultado["msgError"] = 'NO EXISTE SALDO SUFICIENTE COMO AFILIADO';
                return $resultado;
            }
            $afil["saldoaflia"] = $afil["saldoaflia"] - $valor;
            $arrCampos = array(
                'saldoaflia'
            );
            $arrValores = array(
                $afil["saldoaflia"]
            );
            unset($_SESSION["expedienteactual"]);
            regrabarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $expediente . "'");

            $resultado["nombre"] = $afil["razonsocial"];
            $resultado["nombre1"] = $afil["nombre1"];
            $resultado["nombre2"] = $afil["nombre2"];
            $resultado["apellido1"] = $afil["apellido1"];
            $resultado["apellido2"] = $afil["apellido2"];
            $resultado["email"] = $afil["emailcom"];
            $resultado["telefono"] = $afil["telcom1"];
            $resultado["celular"] = '';
            $resultado["direccion"] = $prep["dircom"];
            $resultado["municipio"] = $prep["muncom"];
            $resultado["clave"] = '';
            $resultado["saldoprepago"] = '0';
            $resultado["saldoafiliado"] = $afil["saldoaflia"];
            return $resultado;
        }
    }

    /**
     * 
     * @param type $dbx
     * @param type $ind
     * @param type $tra
     * @param type $trans
     */
    public static function adicionarActoEstudioAsentarReciboSii($dbx, $ind, $tra, $trans) {
        $arrCampos = array(
            'idradicacion',
            'idliquidacion',
            'idsecuenciatransaccion',
            'idtransaccion',
            'orden',
            'matricula',
            'proponente',
            'idclase',
            'numid',
            'nombre',
            'idclaseacreedor',
            'numidacreedor',
            'nombreacreedor',
            'tipodoc',
            'numdoc',
            'fechadoc',
            'fechavenci',
            'mundoc',
            'txtorigendoc',
            'dirubi',
            'munubi',
            'valor',
            'descripcion',
            'libro',
            'acto',
            'noticia',
            'librocruce',
            'inscripcioncruce',
            'nuevaorganizacion',
            'nuevacategoria',
            'nuevonombre',
            'nuevafechaduracion',
            'camaraanterior',
            'matriculaanterior',
            'fechamatriculaanterior',
            'fecharenovacionanterior',
            'ultimoanorenovadoanterior',
            'municipioanterior',
            'benart7anterior',
            'personal',
            'acttot',
            'pastot',
            'pattot',
            'actvin',
            'clase_libro',
            'tipo_libro',
            'codigo_libro',
            'nombre_libro',
            'email_libro',
            'emailconfirmacion_libro',
            'paginainicial_libro',
            'paginafinal_libro',
            'libeleanot_libro',
            'libeleanot_registro',
            'libeleanot_dupli',
            'libeleanot_nroacta',
            'libeleanot_fechaacta',
            'libeleanot_nropaginas',
            'libeleanot_fechainiinscripciones',
            'libeleanot_fechafininscripciones',
            'libeleanot_nroregistros',
            'actanro_libro',
            'fechaacta_libro',
            'numlibro',
            'numreg',
            'dupli',
            'fechareg',
            'horareg',
            'usuario'
        );

        //
        switch ($ind) {
            case 1 : $lib = substr($trans["idacto1"], 0, 4);
                $act = $trans["idacto1"];
                break;
            case 2 : $lib = substr($trans["idacto2"], 0, 4);
                $act = $trans["idacto2"];
                break;
            case 3 : $lib = substr($trans["idacto3"], 0, 4);
                $act = $trans["idacto3"];
                break;
            case 4 : $lib = substr($trans["idacto4"], 0, 4);
                $act = $trans["idacto4"];
                break;
            case 5 : $lib = substr($trans["idacto5"], 0, 4);
                $act = $trans["idacto5"];
                break;
        }


        //
        if (ltrim($tra["cantidad"], "0") == '') {
            $tra["cantidad"] = 1;
        }

        //
        for ($i = 1; $i <= $tra["cantidad"]; $i++) {

            if (ltrim($tra["paginainicial_libro"], "0") == '') {
                $tra["paginainicial_libro"] = 0;
            }
            if (ltrim($tra["paginafinal_libro"], "0") == '') {
                $tra["paginafinal_libro"] = 0;
            }

            // En caso que sea un trámite de libros de comercio
            if ($tra["clase_libro"] == 'F') {
                $tra["noticia"] = "REGISTRO DEL LIBRO " . $tra["nombre_libro"] .
                        " DE LA PAGINA NO. " . $tra["paginainicial_libro"] .
                        " A LA " . $tra["paginafinal_libro"] . " (TOTAL " .
                        intval($tra["paginafinal_libro"] - $tra["paginainicial_libro"] + 1) . " FOLIOS)";
            }

            // En caso que la noticia mercantil esté vacía
            // Embargos
            // Desembargos        
            if (trim($tra["noticia"]) == '') {
                if (trim($tra["embargo"]) != '') {
                    $tra["noticia"] .= trim($tra["embargo"]);
                }
                if (trim($tra["desembargo"]) != '') {
                    if (trim($tra["noticia"]) != '') {
                        $tra["noticia"] .= chr(13) . chr(10);
                    }
                    $tra["noticia"] .= trim($tra["desembargo"]);
                }
                if (trim($tra["texto"]) != '') {
                    if (trim($tra["noticia"]) != '') {
                        $tra["noticia"] .= chr(13) . chr(10);
                    }
                    $tra["noticia"] .= trim($tra["texto"]);
                }
            }

            //
            if (trim($tra["noticia"]) == '') {
                $tra["noticia"] = retornarRegistroMysqli2($dbx, 'mreg_actos', "idlibro='" . $lib . "' and idacto='" . substr($act, 5, 4) . "'", "nombre");
            }

            // *************************************************************** //
            // Asigna ls variables del expediente al acto a registrar
            // Por defecto asume los asociados con la matrícula afectada
            // *************************************************************** //
            $matx = $tra["matriculaafectada"];
            $tipoidex = $tra["tipoidentificacion"];
            $idex = $tra["identificacion"];
            $nomx = $tra["razonsocial"];
            $norgx = $tra["organizacion"];
            $ncatx = $tra["categoria"];
            $nnomx = $tra["razonsocial"];
            $perx = $tra["personal"];
            $actix = $tra["activos"];

            // En caso de contratos de compraventa
            if ($trans["idtipotransaccion"] == '022') {
                $actx = retornarRegistroMysqli2($dbx, 'mreg_actos', "idlibro='" . $lib . "' and idacto='" . substr($act, 5, 4) . "'");
                if ($actx["idgrupoacto"] == '002') { // Si se trata de una cancelacion mueve el vendedor
                    $matx = $tra["matriculavendedor"];
                    $tipoidex = $tra["tipoidentificacionvendedor"];
                    $idex = $tra["identificacionvendedor"];
                    $nomx = $tra["nombrevendedor"];
                    $norgx = '';
                    $ncatx = '';
                    $nnomx = '';
                    $perx = 0;
                    $actix = 0;
                    if ($nomx == '') {
                        $nomx = trim($tra["apellido1vendedor"] . ' ' . $tra["apellido2vendedor"] . ' ' . $tra["nombre1vendedor"] . ' ' . $tra["nombre2vendedor"]);
                    }
                }
                if ($actx["idgrupoacto"] == '001' || $actx["idgrupoacto"] == '024') { // Si se trata de la matrícula mueve el comprador
                    $matx = $tra["matriculacomprador"];
                    $tipoidex = $tra["tipoidentificacioncomprador"];
                    $idex = $tra["identificacioncomprador"];
                    $nomx = $tra["nombrecomprador"];
                    $norgx = $tra["organizacioncomprador"];
                    $ncatx = $tra["categoriacomprador"];
                    $perx = $tra["personalcomprador"];
                    $actix = $tra["activoscomprador"];
                    if ($nomx == '') {
                        $nomx = trim($tra["apellido1comprador"] . ' ' . $tra["apellido2comprador"] . ' ' . $tra["nombre1comprador"] . ' ' . $tra["nombre2comprador"]);
                    }
                    $nnomx = $nomx;
                }
            }

            //
            $_SESSION["generales"]["icontrol"]++;
            $arrValores = array(
                "'" . ltrim($_SESSION["tramite"]["numeroradicacion"], "0") . "'",
                $tra["idliquidacion"],
                $tra["idsecuencia"],
                "'" . $tra["idtransaccion"] . "'",
                "'" . sprintf("%02s", $_SESSION["generales"]["icontrol"]) . "'",
                "'" . $matx . "'",
                "''", // proponente
                "'" . $tipoidex . "'", // Id clase
                "'" . $idex . "'", // Num id
                "'" . addslashes($nomx) . "'", // Nombre
                "''", // Tipo Id acreedor
                "''", // Num Id clase acreedor
                "''", // Nombre acreedor
                "'" . $tra["tipodoc"] . "'",
                "'" . $tra["numdoc"] . "'",
                "'" . $tra["fechadoc"] . "'",
                "''", // Fecha vencimiento
                "'" . $tra["mundoc"] . "'",
                "'" . $tra["origendoc"] . "'",
                "''", // Direccion ubicacion
                "''", // Municipio ubicacion
                0, // Valor
                "''", // Descripcion
                "'" . $lib . "'",
                "'" . $act . "'",
                "'" . addslashes($tra["noticia"]) . "'",
                "''", // Libro cruce
                "''", //Inscripcion cruce
                "'" . $norgx . "'", // Nueva organizacion
                "'" . $ncatx . "'", // Nueva categoria
                "'" . $nnomx . "'", // Nombre
                "'" . $tra["fechaduracion"] . "'", // Nueva fecha de duracion
                "'" . $tra["camaraanterior"] . "'",
                "'" . $tra["matriculaanterior"] . "'",
                "'" . $tra["fechamatriculaanterior"] . "'",
                "'" . $tra["fecharenovacionanterior"] . "'",
                "'" . $tra["ultimoanorevadoanterior"] . "'",
                "'" . $tra["municipioanterior"] . "'",
                "'" . $tra["benart7anterior"] . "'",
                doubleval($perx), // personal
                doubleval($actix), // acttot
                0, // pasttot
                doubleval($tra["patrimonio"]), // pattot
                doubleval($tra["activos"]), // actvin
                "'" . $tra["clase_libro"] . "'", // Clase de libro (F, E, A)
                "'" . $tra["tipo_libro"] . "'", // tipo de libro
                "'" . $tra["codigo_libro"] . "'", // Código del libro
                "'" . $tra["nombre_libro"] . "'", // Nombre del libro
                "'" . $tra["email_libro"] . "'", // Email del libro
                "'" . $tra["emailconfirmacion_libro"] . "'", // Emailconfirmacion del libro
                "'" . $tra["paginainicial_libro"] . "'", // Pagina inicial
                "'" . $tra["paginafinal_libro"] . "'", // Pagina final
                "'" . $tra["libeleanot_libro"] . "'", // Pagina final
                "'" . $tra["libeleanot_registro"] . "'", // Pagina final
                "'" . $tra["libeleanot_dupli"] . "'", // Pagina final
                "'" . $tra["libeleanot_nroacta"] . "'", // Pagina final
                "'" . $tra["libeleanot_fechaacta"] . "'", // Pagina final
                intval($tra["libeleanot_nropaginas"]), // Pagina final
                "'" . $tra["libeleanot_fechainiinscripciones"] . "'", // Pagina final
                "'" . $tra["libeleanot_fechafininscripciones"] . "'", // Pagina final
                intval($tra["libeleanot_nroregistros"]), // Pagina final
                "'" . $tra["actanro_libro"] . "'", // Acta Nro Libro
                "'" . $tra["fechaacta_libro"] . "'", // Fecha del Acta del Libro
                "''", // Libro
                "''", // Registro
                "''", // Dupli
                "''", // Fecha registro
                "''", // Hora registro
                "''" // Usuario
            );
            $res = insertarRegistrosMysqli2($dbx, 'mreg_estudio_actos_registro', $arrCampos, $arrValores);
            if ($res === false) {
                $nameLog = 'adicionarActoEstudioAsentarReciboSii_' . date("Ymd");
                \logSii2::general2($nameLog, '', utf8_encode('Error creando mreg_estudio_actos_registro : ' . $_SESSION["generales"]["mensajeerror"]));
            }
        }
    }

    /**
     * 
     * @param type $dbx
     * @param type $tramae
     * @param type $tradat
     * @param type $tiptra
     * @param type $numacto
     * @return type
     */
    public static function adicionarInscripcionCancelacion($dbx, $tramae, $tradat, $tiptra, $numacto) {

        $xActo = '';
        switch ($numacto) {
            case 1 : $xActo = $tramae["idacto1"];
                break;
            case 2 : $xActo = $tramae["idacto2"];
                break;
            case 3 : $xActo = $tramae["idacto3"];
                break;
            case 4 : $xActo = $tramae["idacto4"];
                break;
            case 5 : $xActo = $tramae["idacto5"];
                break;
        }

        $res = array();
        $res ["tiporegistro"] = $tiptra["tiporegistro"];
        $res ["tiposello"] = '90.20.31';
        $res ["libro"] = substr($xActo, 0, 4);
        $res ["numreg"] = '';
        $res ["organizacion"] = $tradat["organizacion"];
        $res ["filesello"] = '';
        $res ["noticia"] = 'CANCELACION MATRICULA MERCANTIL';
        if ($tradat["organizacion"] == '02') {
            $res ["noticia"] = 'CANCELACION MATRICULA MERCANTIL DE ESTABLECIMIENTO DE COMERCIO';
        }
        if (substr($xActo, 5, 4) == '0532') {
            $res ["noticia"] = 'CANCELACION MATRICULA MERCANTIL POR CAMBIO DE DOMICILIO';
            if ($tradat["municipiodestino"] != '') {
                $res["noticia"] .= ' A LA CIUDAD DE ' . retornarRegistroMysqli2($dbx, 'bas_municipios', "codigomunicipio='" . $tradat["municipiodestino"] . "'", "ciudad");
            }
        }
        $res ["matricula"] = $tradat["matriculaafectada"];
        $res ["proponente"] = '';
        $res ["tipodoc"] = $tradat["tipodoc"];
        $res ["numdoc"] = $tradat["numdoc"];
        $res ["origendoc"] = $tradat["origendoc"];
        $res ["fechadoc"] = $tradat["fechadoc"];
        $res ["municipio"] = $tradat["mundoc"];
        $res ["acto"] = substr($xActo, 5, 4);
        $res ["fecha"] = date("Ymd");
        $res ["hora"] = date("His");
        $res ["tipoidentificacion"] = $tradat["tipoidentificacion"];
        $res ["identificacion"] = $tradat["identificacion"];
        $res ["nombre"] = $tradat["razonsocial"];
        $res ["ope"] = $_SESSION["generales"]["idcodigosirepcaja"]; // 12016-01-12 : JINT : Asigna el registro añ cajero que hce el trámite
        $res ["bandeja"] = $tiptra["bandeja"];

        return $res;
    }

    /**
     * 
     * @param type $dbx
     * @param type $liq
     * @param type $dat
     * @param type $mom
     * @return boolean
     */
    public static function almacenarDatosImportantesRenovacion($dbx, $liq, $dat, $mom) {

        //
        if ($mom == 'F') {
            borrarRegistrosMysqli2($dbx, 'mreg_renovacion_datos_control', "idliquidacion=" . $liq . " and matricula='" . ltrim($dat["matricula"], "0") . "' and momento='F'");
        }

        //
        $arrCampos = array(
            'idliquidacion',
            'matricula',
            'dato',
            'contenido',
            'momento'
        );

        //
        $arrValores = array();

        //
        foreach ($dat as $key => $valor) {
            if (!is_array($valor)) {
                if (ltrim(trim($valor), "0") != '') {
                    $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'" . $key . "'", "'" . $valor . "'", "'" . $mom . "'");
                }
            } else {
                foreach ($valor as $k1 => $v1) {
                    if (!is_array($v1)) {
                        if (ltrim(trim($v1), "0") != '') {
                            $dx = $key . '|' . $k1;
                            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'" . $dx . "'", "'" . $v1 . "'", "'" . $mom . "'");
                        }
                    } else {
                        foreach ($v1 as $k2 => $v2) {
                            if (!is_array($v2)) {
                                if (ltrim(trim($v2), "0") != '') {
                                    $dx = $key . '|' . $k1 . '|' . $k2;
                                    $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'" . $dx . "'", "'" . $v2 . "'", "'" . $mom . "'");
                                }
                            }
                        }
                    }
                }
            }
        }

        //
        if (trim($dat["ciius"][1]) != '') {
            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu1'", "'" . $dat["ciius"][1] . "'", "'" . $mom . "'"); // Ciiu1
        }
        if (trim($dat["ciius"][2]) != '') {
            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu2'", "'" . $dat["ciius"][2] . "'", "'" . $mom . "'"); // Ciiu2
        }
        if (trim($dat["ciius"][3]) != '') {
            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu3'", "'" . $dat["ciius"][3] . "'", "'" . $mom . "'"); // Ciiu3
        }
        if (trim($dat["ciius"][4]) != '') {
            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu4'", "'" . $dat["ciius"][4] . "'", "'" . $mom . "'"); // Ciiu4
        }

        //
        insertarRegistrosBloqueMysqli2($dbx, 'mreg_renovacion_datos_control', $arrCampos, $arrValores);
        return true;
    }

    /**
     * Asigna las variables de sesion necesarias para ejecutar los procesos internos
     * 
     * @param type $dbx
     * @param type $entrada
     * @return boolean
     */
    public static function asignarVariablesSessionSii($dbx, $entrada) {

        $_SESSION["generales"]["zonahoraria"] = "America/Bogota";
        $_SESSION["generales"]["idioma"] = "es";
        $_SESSION["generales"]["navegador"] = obtenerNavegador(getenv("HTTP_USER_AGENT"));
        $_SESSION["generales"]["codigousuario"] = 'USUPUBXX';
        $_SESSION["generales"]["tipousuario"] = '00';
        $_SESSION["generales"]["emailusuariocontrol"] = $entrada["emailcontrol"];
        $_SESSION["generales"]["identificacionusuariocontrol"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["celularusuariocontrol"] = $entrada["celularcontrol"];
        $_SESSION["generales"]["validado"] = 'NO';
        $_SESSION["generales"]["escajero"] = 'NO';
        $_SESSION["generales"]["tipousuariocontrol"] = 'usuarioanonimo';
        $_SESSION["generales"]["sedeusuario"] = '99';
        $_SESSION["generales"]["gastoadministrativo"] = 'NO';
        $_SESSION["generales"]["esdispensador"] = 'NO';
        $_SESSION["generales"]["escensador"] = 'NO';
        $_SESSION["generales"]["esbrigadista"] = 'NO';
        $_SESSION["generales"]["puedecerrarcaja"] = 'NO';
        $_SESSION["generales"]["visualizatotales"] = 'NO';
        $_SESSION["generales"]["esrue"] = 'NO';
        $_SESSION["generales"]["eswww"] = 'SI';
        $_SESSION["generales"]["esreversion"] = 'NO';
        $_SESSION["generales"]["essa"] = 'NO';
        $_SESSION["generales"]["esbanco"] = 'NO';
        $_SESSION["generales"]["abogadocoordinador"] = 'NO';
        $_SESSION["generales"]["loginemailusuario"] = $entrada["emailcontrol"];
        $_SESSION["generales"]["perfildocumentacion"] = '';
        $_SESSION["generales"]["controlapresupuesto"] = '';
        $_SESSION["generales"]["idtipoidentificacionusuario"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["identificacionusuario"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["nitempresausuario"] = '';
        $_SESSION["generales"]["nombreempresausuario"] = '';
        $_SESSION["generales"]["direccionusuario"] = '';
        $_SESSION["generales"]["idmuniciopiousuario"] = '';
        $_SESSION["generales"]["telefonousuario"] = '';
        $_SESSION["generales"]["movilusuario"] = $entrada["celularcontrol"];
        $_SESSION["generales"]["operadorsirepusuario"] = '';
        $_SESSION["generales"]["ccosusuario"] = '';
        $_SESSION["generales"]["cargousuario"] = '';
        $_SESSION["generales"]["nombreempresa"] = '';
        $_SESSION["generales"]["idcodigosirepcaja"] = '';
        $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
        $_SESSION["generales"]["idcodigosirepregistro"] = '';
        $_SESSION["generales"]["habilitadobiometria"] = 'NO';
        $_SESSION["generales"]["administradorbiometria"] = 'NO';
        $_SESSION["generales"]["controlverificacion"] = '';
        $_SESSION["generales"]["fechaactivacion"] = '';
        $_SESSION["generales"]["fechainactivacion"] = '';
        $_SESSION["generales"]["fechacambioclave"] = '';

        if ($entrada["idusuario"] == 'USUPUBXX') {
            $query = "email='" . $entrada["emailcontrol"] . "' and identificacion='" . $entrada["identificacioncontrol"] . "' and celular='" . $entrada["celularcontrol"] . "'";
            $temx = retornarRegistroMysqli2($dbx, 'usuarios_verificados', $query);
            if ($temx === false || empty($temx)) {
                $temx = retornarRegistroMysqli2($dbx, 'usuarios_registrados', $query);
                if ($temx === false || empty($temx)) {
                    return false;
                } else {
                    $tusu = 'usuarioregistrado';
                }
            } else {
                $tusu = 'usuarioverificado';
            }
            $_SESSION["generales"]["validado"] = 'SI';
            $_SESSION["generales"]["escajero"] = 'NO';
            $_SESSION["generales"]["tipousuariocontrol"] = $tusu;
            $_SESSION["generales"]["sedeusuario"] = '99';
            $_SESSION["generales"]["gastoadministrativo"] = 'NO';
            $_SESSION["generales"]["esdispensador"] = 'NO';
            $_SESSION["generales"]["escensador"] = 'NO';
            $_SESSION["generales"]["esbrigadista"] = 'NO';
            $_SESSION["generales"]["puedecerrarcaja"] = 'NO';
            $_SESSION["generales"]["visualizatotales"] = 'NO';
            $_SESSION["generales"]["esrue"] = 'NO';
            $_SESSION["generales"]["eswww"] = 'SI';
            $_SESSION["generales"]["esreversion"] = 'NO';
            $_SESSION["generales"]["essa"] = 'NO';
            $_SESSION["generales"]["esbanco"] = 'NO';
            $_SESSION["generales"]["abogadocoordinador"] = 'NO';
            $_SESSION["generales"]["loginemailusuario"] = $entrada["emailcontrol"];
            $_SESSION["generales"]["perfildocumentacion"] = '';
            $_SESSION["generales"]["controlapresupuesto"] = '';
            $_SESSION["generales"]["idtipoidentificacionusuario"] = $entrada["identificacioncontrol"];
            $_SESSION["generales"]["identificacionusuario"] = $entrada["identificacioncontrol"];
            $_SESSION["generales"]["nitempresausuario"] = '';
            $_SESSION["generales"]["nombreempresausuario"] = '';
            $_SESSION["generales"]["direccionusuario"] = '';
            $_SESSION["generales"]["idmuniciopiousuario"] = '';
            $_SESSION["generales"]["telefonousuario"] = '';
            $_SESSION["generales"]["movilusuario"] = $entrada["celularcontrol"];
            $_SESSION["generales"]["operadorsirepusuario"] = '';
            $_SESSION["generales"]["ccosusuario"] = '';
            $_SESSION["generales"]["cargousuario"] = '';
            $_SESSION["generales"]["nombreempresa"] = '';
            $_SESSION["generales"]["idcodigosirepcaja"] = '';
            $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
            $_SESSION["generales"]["idcodigosirepregistro"] = '';
            $_SESSION["generales"]["controlverificacion"] = '';
            $_SESSION["generales"]["fechaactivacion"] = '';
            $_SESSION["generales"]["fechainactivacion"] = '';
            $_SESSION["generales"]["fechacambioclave"] = '';
        } else {
            $temx = retornarRegistroMysqli2($dbx, "usuarios", "idusuario='" . $entrada["idusuario"] . "'");
            if ($temx === false || empty($temx)) {
                return false;
            }
            $_SESSION["generales"]["codigousuario"] = $entrada["idusuario"];
            $_SESSION["generales"]["tipousuario"] = $temx["idtipousuario"];
            $_SESSION["generales"]["emailusuariocontrol"] = $temx["email"];
            $_SESSION["generales"]["identificacionusuariocontrol"] = $temx["identificacion"];
            $_SESSION["generales"]["celularusuariocontrol"] = $temx["celular"];
            $_SESSION["generales"]["validado"] = 'SI';
            $_SESSION["generales"]["escajero"] = $temx["escajero"];
            $_SESSION["generales"]["tipousuariocontrol"] = 'usuariointerno';
            $_SESSION["generales"]["sedeusuario"] = $temx["idsede"];
            if ($entrada["idusuario"] == 'RUE') {
                $_SESSION["generales"]["sedeusuario"] = '90';
            }
            if ($entrada["idusuario"] == 'RUE') {
                $_SESSION["generales"]["sedeusuario"] = $temx["idsede"];
            }
            if ($temx["idtipousuario"] == '06') {
                $_SESSION["generales"]["sedeusuario"] = '98';
            }

            if ($temx["esbanco"] == 'SI') {
                $_SESSION["generales"]["sedeusuario"] = '97';
            }
            $_SESSION["generales"]["gastoadministrativo"] = $temx["gastoadministrativo"];
            $_SESSION["generales"]["esdispensador"] = $temx["esdispensador"];
            $_SESSION["generales"]["escensador"] = $temx["escensador"];
            $_SESSION["generales"]["esbrigadista"] = $temx["esbrigadista"];
            $_SESSION["generales"]["puedecerrarcaja"] = $temx["puedecerrarcaja"];
            $_SESSION["generales"]["visualizatotales"] = $temx["visualizatotales"];
            $_SESSION["generales"]["esrue"] = $temx["esrue"];
            $_SESSION["generales"]["eswww"] = $temx["eswww"];
            $_SESSION["generales"]["esreversion"] = $temx["esreversion"];
            $_SESSION["generales"]["essa"] = $temx["essa"];
            $_SESSION["generales"]["esbanco"] = $temx["esbanco"];
            $_SESSION["generales"]["abogadocoordinador"] = $temx["abogadocoordinador"];
            $_SESSION["generales"]["loginemailusuario"] = $temx["email"];
            $_SESSION["generales"]["perfildocumentacion"] = $temx["idperfildocumentacion"];
            $_SESSION["generales"]["controlapresupuesto"] = $temx["controlapresupuesto"];
            $_SESSION["generales"]["idtipoidentificacionusuario"] = $temx["idtipoidentificacion"];
            $_SESSION["generales"]["identificacionusuario"] = $temx["identificacion"];
            $_SESSION["generales"]["nitempresausuario"] = $temx["nitempresa"];
            $_SESSION["generales"]["nombreempresausuario"] = $temx["nombreempresa"];
            $_SESSION["generales"]["direccionusuario"] = $temx["direccion"];
            $_SESSION["generales"]["idmuniciopiousuario"] = $temx["idmunicipio"];
            $_SESSION["generales"]["telefonousuario"] = $temx["telefonos"];
            $_SESSION["generales"]["movilusuario"] = $temx["celular"];
            $_SESSION["generales"]["operadorsirepusuario"] = '';
            $_SESSION["generales"]["ccosusuario"] = '';
            $_SESSION["generales"]["cargousuario"] = '';
            $_SESSION["generales"]["nombreempresa"] = '';
            $_SESSION["generales"]["idcodigosirepcaja"] = $temx["idcodigosirepcaja"];
            $_SESSION["generales"]["idcodigosirepdigitacion"] = $temx["idcodigosirepdigitacion"];
            $_SESSION["generales"]["idcodigosirepregistro"] = $temx["idcodigosirepregistro"];
            $_SESSION["generales"]["habilitadobiometria"] = $temx["habilitadobiometria"];
            $_SESSION["generales"]["administradorbiometria"] = $temx["administradorbiometria"];
            $_SESSION["generales"]["controlverificacion"] = '';
            $_SESSION["generales"]["fechaactivacion"] = $temx["fechaactivacion"];
            $_SESSION["generales"]["fechainactivacion"] = $temx["fechainactivacion"];
            $_SESSION["generales"]["fechacambioclave"] = '';
            $_SESSION["generales"]["validado"] = 'NO';
            $_SESSION["generales"]["mensajeerror"] = '';
            $_SESSION["generales"]["pagina"] = '';
            $_SESSION["generales"]["disco"] = '001';
            $_SESSION["generales"]["tipodoc"] = 'mreg';
            $_SESSION["generales"]["sega"] = 'PPAL';
        }

        return true;
    }

    /**
     * 
     * @param type $dbx
     * @param type $tipo
     * @return type
     */
    public static function asignarNumeroRecuperacionSii($dbx, $tipo) {
        require_once('generales.php');

        $OK = 'NO';
        while ($OK == 'NO') {
            if ($tipo == 'mreg') {


                $num = strtoupper(trim(\funcionesSii2::generarAleatorioAlfanumerico('')));
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . trim($num) . "'") == 0) {
                    $OK = "SI";
                } else {
                    $num = strtoupper(trim(\funcionesSii2::generarAleatorioAlfanumerico('')));
                }
            } else {
                if ($tipo == 'news') {
                    $num = strtoupper(trim(\funcionesSii2::generarAleatorioAlfanumerico10('')));
                    if (contarRegistrosMysqli2($dbx, 'news', "numerorecuperacion='" . trim($num) . "'") == 0) {
                        $OK = "SI";
                    } else {
                        $num = strtoupper(trim(\funcionesSii2::generarAleatorioAlfanumerico10('')));
                    }
                } else {
                    $num = strtoupper(trim(\funcionesSii2::generarAleatorioAlfanumerico('')));
                }
            }
        }
        return $num;
    }

    /**
     * 
     * @param type $action
     * @param type $secret_key
     * @param type $secret_iv
     * @param type $string
     * @return type
     */
    public static function encrypt_decrypt($action, $secret_key = 'c0nf3c4m4r4s', $secret_iv = 'c0nf3c4m4r4s', $string = '') {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    public static function encontrarTamanoEmpresa($mysqli, $fecha, $valor, $personal) {
        $smmlv = \funcionesSii2::localizarSmmlv($fecha, $mysqli);
        if (doubleval($smmlv) == 0) {
            return "";
        }

        //
        $salida = '';
        $valor1 = $valor / $smmlv;
        if ($valor1 <= 500 && $personal <= 10) {
            $salida = 'micro';
        } else {
            if ($valor1 <= 5000 && $personal <= 50) {
                $salida = 'pequena';
            } else {
                if ($valor1 <= 30000 && $personal <= 200) {
                    $salida = 'mediana';
                } else {
                    $salida = 'gran';
                }
            }
        }
        return $salida;
    }

    /**
     * 
     * @param type $servmail
     * @param type $portsmtp
     * @param type $requiautenticacion
     * @param type $tipoencripcion
     * @param type $ctaremi
     * @param type $passremi
     * @param type $remiemail
     * @param type $nombreremi
     * @param type $destino
     * @param type $asunto
     * @param type $mensaje
     * @param type $attach
     * @param type $ctrmasivo
     * @return boolean
     */
    public static function enviarEmailSii2($servmail, $portsmtp, $requiautenticacion, $tipoencripcion, $ctaremi, $passremi, $remiemail, $nombreremi, $destino, $asunto, $mensaje, $attach = array(), $ctrmasivo = 'no') {
        require_once ('phpmailer/class.phpmailer.php');
        // require_once ('myErrorHandler.php');
        require_once ('LogSii2.class.php');
        // set_error_handler('myErrorHandler');
        // Envía correo a través de servicios SMTP
        $mail = new PHPMailer();
        $mail->PluginDir = 'phpmailer/';
        $mail->SetLanguage("es", 'phpmailer/language/');

        //
        $mail->IsSMTP();

        //
        try {
            $mail->Host = $servmail;
            $mail->Port = $portsmtp;
            // $mail->SMTPAuth = $requiautenticacion;
            $mail->SMTPAuth = true;
            if ($tipoencripcion != '') {
                $mail->SMTPSecure = $tipoencripcion;
            }
            $mail->Username = $ctaremi;
            $mail->Password = $passremi;
            $mail->From = $remiemail;
            $mail->FromName = $nombreremi;

            //
            $cantidadRemitentes = 0;
            $cantidadRemitentesAlterno = 0;

            //
            $txt = '';
            if (is_array($destino)) {
                foreach ($destino as $dest) {
                    $encuentraDominio = strpos($dest, 'mail.confecamaras.co');
                    if ($encuentraDominio === false) {
                        $txt .= $dest . ' - ';
                        $mail->AddAddress($dest);
                        $cantidadRemitentes++;
                    }
                }

                $cantidadRemitentes = $cantidadRemitentes + $cantidadRemitentesAlterno;
                if ($cantidadRemitentes <= 0) {
                    return false;
                }
            } else {

                $encuentraDominio = strpos($destino, 'mail.confecamaras.co');
                if ($encuentraDominio === false) {
                    $txt .= $destino . ' - ';
                    $mail->AddAddress($destino);
                    $cantidadRemitentes++;
                }
            }

            if ($cantidadRemitentes <= 0) {
                return false;
            }

            //
            $mail->WordWrap = 50;
            if (!empty($attach)) {
                foreach ($attach as $at) {
                    $mail->AddAttachment($at);
                }
            }
            $mail->IsHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $asunto;
            $posamazon = strpos($servmail, 'amazon');
            if ($posamazon !== false) {
                $mail->addCustomHeader('X-SES-CONFIGURATION-SET:Config-set-kinesis');
            }
            $mail->Body = $mensaje;

            //
            if (!$mail->Send()) {
                $_SESSION["generales"]["mensajeerror"] = $mail->ErrorInfo;
                \logSii2::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            } else {
                $_SESSION["generales"]["mensajeerror"] = '';
                \logSii2::general2('email', __FUNCTION__, $txt . ' = ok');
                return true;
            }
        } catch (phpmailerException $e) {
            $_SESSION["generales"]["mensajeerror"] = $e->errorMessage(); //Pretty error messages from PHPMailer;
            \logSii2::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = $e->getMessage(); //Boring error messages from anything else!
            \logSii2::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }
    }

    public static function evaluarArchivadorAsentarReciboSii($idserv) {
        $archivador = '01';
        if (substr($idserv, 0, 6) == '010101') {
            $archivador = '01';
        }
        if (substr($idserv, 0, 6) == '010102') {
            $archivador = '02';
        }
        if (substr($idserv, 0, 6) == '010103') {
            $archivador = '03';
        }
        if (substr($idserv, 0, 6) == '010201') {
            $archivador = '01';
        }
        if (substr($idserv, 0, 6) == '010202') {
            $archivador = '01';
        }
        if (substr($idserv, 0, 6) == '010203' ||
                substr($idserv, 0, 6) == '010204' ||
                substr($idserv, 0, 6) == '010205' ||
                substr($idserv, 0, 6) == '010206') {
            $archivador = '02';
        }
        if (substr($idserv, 0, 6) >= '010301' &&
                substr($idserv, 0, 6) <= '010322') {
            $archivador = '01';
        }
        if (substr($idserv, 0, 6) >= '010351' &&
                substr($idserv, 0, 6) <= '010355') {
            $archivador = '03';
        }
        if (substr($idserv, 0, 8) == '01020208') {
            $archivador = '03';
        }
        if (substr($idserv, 0, 6) == '010501') {
            $archivador = '01';
        }
        if (substr($idserv, 0, 6) == '010502') {
            $archivador = '02';
        }
        if (substr($idserv, 0, 6) == '010503') {
            $archivador = '03';
        }
        return $archivador;
    }

    public static function generarAleatorioNumerico($dbx, $validar = '') {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = '0123456789';
                $num = '';
                for ($i = 1; $i <= 6; $i++) {
                    $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                }
                if (strlen($num) == 6) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }
            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico($dbx, $validar = '') {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $num = '';
                for ($i = 1; $i <= 6; $i++) {
                    $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                }
                if (strlen($num) == 6) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }
            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_baloto') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_exito') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_reactivaciones_propias') {
                if (contarRegistrosMysqli2($dbx, 'mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_certificados_virtuales') {
                if (contarRegistrosMysqli2($dbx, 'mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico8($dbx, $validar = '') {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $num = '';
                for ($i = 1; $i <= 8; $i++) {
                    $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                }
                if (strlen($num) == 8) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }
            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_baloto') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_exito') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_reactivaciones_propias') {
                if (contarRegistrosMysqli2($dbx, 'mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_certificados_virtuales') {
                if (contarRegistrosMysqli2($dbx, 'mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico9() {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $num = '';
            for ($i = 1; $i <= 9; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (strlen($num) == 9) {
                $ok = 'SI';
            }
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico10($dbx, $validar = '') {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                // $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $alfanumerico = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ123456789';
                $num = '';
                for ($i = 1; $i <= 10; $i++) {
                    $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                }
                if (strlen($num) == 10) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }

            if ($validar == 'desarrollo_actividades') {
                if (contarRegistrosMysqli2($dbx, 'desarrollo_actividades', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'desarrollo_actualizaciones') {
                if (contarRegistrosMysqli2($dbx, 'desarrollo_actualizaciones', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }


            if ($validar == 'desarrollo_control_cambios') {
                if (contarRegistrosMysqli2($dbx, 'desarrollo_control_cambios', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'desarrollo_control_cambios_casosuso') {
                if (contarRegistrosMysqli2($dbx, 'desarrollo_control_cambios_casosuso', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'infraestructura_contratos') {
                if (contarRegistrosMysqli2($dbx, 'infraestructura_contratos', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'infraestructura_proveedores') {
                if (contarRegistrosMysqli2($dbx, 'infraestructura_proveedores', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'infraestructura_clientes') {
                if (contarRegistrosMysqli2($dbx, 'infraestructura_clientes', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'infraestructura_comentarios') {
                if (contarRegistrosMysqli2($dbx, 'infraestructura_comentarios', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'mreg_envio_matriculas_api') {
                if (contarRegistrosMysqli2($dbx, 'mreg_envio_matriculas_api', "idenvio='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'mreg_liquidacion_sobre') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_sobre', "idsobre='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_baloto') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_exito') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_reactivaciones_propias') {
                if (contarRegistrosMysqli2($dbx, 'mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_certificados_virtuales') {
                if (contarRegistrosMysqli2($dbx, 'mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_rues_bloque') {
                if (contarRegistrosMysqli2($dbx, 'mreg_rues_bloque', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_est_inscritos_fotos') {
                if (contarRegistrosMysqli2($dbx, 'mreg_est_inscritos_fotos', "identificadorimagen='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioNumerico10($dbx, $validar = '') {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = '0123456789';
                $num = '';
                for ($i = 1; $i <= 10; $i++) {
                    $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                }
                if (strlen($num) == 10) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }
            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_baloto') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_exito') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_reactivaciones_propias') {
                if (contarRegistrosMysqli2($dbx, 'mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_certificados_virtuales') {
                if (contarRegistrosMysqli2($dbx, 'mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico20($dbx, $validar = '') {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $num = '';
                for ($i = 1; $i <= 20; $i++) {
                    $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                }
                if (strlen($num) == 20) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }

            if ($validar == 'mreg_documentos_firmados') {
                if (contarRegistrosMysqli2($dbx, 'mreg_documentos_firmados', "idfirmado='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'mreg_envio_matriculas_api') {
                if (contarRegistrosMysqli2($dbx, 'mreg_envio_matriculas_api', "idenvio='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function buscarRangoTarifaSii($dbx, $idservicio = '', $ano = 0, $base = 0, $tipotarifa = 'tarifa') {
        $retornar = 0;
        if ($ano < 1993) {
            $ano = 1993;
        }
        $result = retornarRegistrosMysqli2($dbx, 'mreg_tarifas', "idservicio='" . $idservicio . "' and ano='" . $ano . "'", "idrango");
        if ($result === false || empty($result)) {
            $retornar = false;
        } else {
            $retornar = 0;
            foreach ($result as $res) {
                if (doubleval($res["topeminimo"]) <= doubleval($base) && doubleval($res["topemaximo"]) >= doubleval($base)) {
                    if ($tipotarifa == 'tarifa') {
                        $retornar = doubleval($res["tarifa"]);
                    }
                    if ($tipotarifa == 'tarifapnat') {
                        $retornar = doubleval($res["tarifapnat"]);
                    }
                    if ($tipotarifa == 'tarifapjur') {
                        $retornar = doubleval($res["tarifapjur"]);
                    }
                }
            }
        }
        return $retornar;
    }

    public static function borrarPalabrasAutomaticas($txt, $comple = '') {
        $salida = $txt;
        if ($comple != '') {
            $pos = strpos($salida, $comple);
            if ($pos) {
                $pos = $pos - 1;
                $salida = substr($salida, 0, $pos);
            }
        }
        $pos = strpos($salida, '- EN LIQUIDACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }

        $pos = strpos($salida, '- EN LIQUIDACION JUDICIAL');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }

        $pos = strpos($salida, '- EN LIQUIDACION FORZOSA');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }

        $pos = strpos($salida, '- EN ACUERDO DE REESTRUCTURACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }
        $pos = strpos($salida, '- EN REESTRUCTURACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }
        $pos = strpos($salida, '- EN REORGANIZACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }

        $pos = strpos($salida, 'EN LIQUIDACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }

        $pos = strpos($salida, 'EN LIQUIDACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }

        $pos = strpos($salida, 'EN LIQUIDACION JUDICIAL');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }

        $pos = strpos($salida, 'EN LIQUIDACION FORZOSA');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }

        $pos = strpos($salida, 'EN ACUERDO DE REESTRUCTURACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }
        $pos = strpos($salida, 'EN REESTRUCTURACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }
        $pos = strpos($salida, 'EN REORGANIZACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }
        return $salida;
    }

    public static function buscaTarifaSii($dbx, $idservicio = '', $ano = 0, $cantidad = 0, $base = 0, $tipotarifa = 'tarifa') {
        $tarifa = 0;
        $arrServicio = retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $idservicio . "'");
        if (!$arrServicio) {
            return false;
        }
        if ($arrServicio == 0) {
            return false;
        }
        // Liquida por valor &uacute;nico
        if ($arrServicio["idclasevalor"] == '1') {
            $tarifa = $arrServicio["valorservicio"];
        }
        // Liquida por rango de tarifas
        if ($arrServicio["idclasevalor"] == '2') {
            $tarifa = \funcionesSii2::buscarRangoTarifaSii($dbx, $idservicio, $ano, $base, $tipotarifa);
        }
        // Liquida por porcentaje
        if ($arrServicio["idclasevalor"] == '4') {
            $tarifa = intval($arrServicio["valorservicio"] * $base / 100);
        }
        // Liquida por c&aacute;lculo
        if ($arrServicio["idclasevalor"] == '5') {
            $tarifa = intval($arrServicio["valorservicio"] * $base / 100);
        }
        $tarifa = $tarifa * $cantidad;

        // 2015-12-29: JINT : Redondeos
        if (!isset($arrServicio["redondeo"])) {
            return $tarifa;
        }

        if (trim($arrServicio["redondeo"]) == '') {
            return $tarifa;
        }

        switch ($arrServicio["redondeo"]) {

            case "50":
                $ent = intval($tarifa / 50) * 50;
                $res = $tarifa - $ent;
                if ($res <= 25) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 50;
                }
                break;

            case "100":
                $ent = intval($tarifa / 100) * 100;
                $res = $tarifa - $ent;
                if ($res <= 50) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 100;
                }
                break;

            case "500":
                $ent = intval($tarifa / 500) * 500;
                $res = $tarifa - $ent;
                if ($res <= 250) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 500;
                }
                break;

            case "1000":
                $ent = intval($tarifa / 1000) * 1000;
                $res = $tarifa - $ent;
                if ($res <= 500) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 1000;
                }
                break;

            case "50+":
                if (intval($tarifa) < 50) {
                    $tarifa = 50;
                }
                break;
            case "100+":
                if (intval($tarifa) < 100) {
                    $tarifa = 100;
                }
                break;
            case "500+":
                if (intval($tarifa) < 500) {
                    $tarifa = 500;
                }
                break;
            case "500+":
                if (intval($tarifa) < 1000) {
                    $tarifa = 1000;
                }
                break;
        }
        return $tarifa;
    }

    public static function consultarMultasPoliciaSii($dbx, $tid, $id, $idliq = 0) {

        $nameLog = 'validacionMultasPonal_API_' . date("Ymd");

        //
        $mysqli = conexionMysqli2();

        //
        $reintentar = 3;
        $multadovencido = 'ER';
        $textoerror = '';
        while ($reintentar > 0) {
            $buscartoken = true;
            $buscarmulta = true;
            $name = PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt';
            if (file_exists($name)) {
                $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt');
                list ($token, $expira) = explode("|", $x);
                $act = date("Y-m-d H:i:s");
                if ($act <= $expira) {
                    $buscartoken = false;
                }
            }

            if ($buscartoken) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://catalogoservicioweb.policia.gov.co/sw/token');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "username=fvera@confecamaras.org.co&password=fveraPolicia2017*2018&grant_type=password");
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $result = curl_exec($ch);
                curl_close($ch);
                if ((is_string($result) &&
                        (is_object(json_decode($result)) ||
                        is_array(json_decode($result))))) {
                    $resultado = json_decode($result, true);
                    $access_token = $resultado['access_token'];
                    $fecha = date('Y-m-d H:i:s', (strtotime("+20 Hours")));
                    $f = fopen($name, "w");
                    fwrite($f, $access_token . '|' . $fecha);
                    fclose($f);
                } else {
                    $textoerror = 'NO FUE POSIBLE SOLICITAR EL TOKEN (1), LA RESPUESTA DEL SERVICIUO WEB DE TOKEN ES INCORRECTA';
                    \logSii2::general2($nameLog, $tid . '-' . $id, $textoerror . ' : ' . $result);
                    $buscarmulta = false;
                    $reintentar--;
                }
            }

            //
            if ($buscarmulta) {
                if (file_exists($name)) {
                    $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt');
                    list ($access_token, $expira) = explode("|", $x);
                } else {
                    $textoerror = 'NO FUE POSIBLE SOLICITAR EL TOKEN (2), NO EXISTE ARCHIVO CON EL TOKEN ALMACENADO';
                    $buscarmulta = false;
                    $reintentar--;
                }
            }

            //
            if ($buscarmulta) {
                $data = array(
                    'codigoCamara' => CODIGO_EMPRESA,
                    'tipoConsulta' => 'CC',
                    'numeroIdentificacion' => $id
                );

                //            
                $fields = json_encode($data);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://catalogoservicioweb.policia.gov.co/sw/api/Multa/ConsultaMultaVencidaSeisMeses');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $result = curl_exec($ch);
                curl_close($ch);

                //
                \logSii2::general2($nameLog, $tid . '-' . $id, $result);

                //
                if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"])) {
                    mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"], 0777);
                    crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"]);
                }
                if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/")) {
                    mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/", 0777);
                    crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/");
                }
                if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/")) {
                    mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/", 0777);
                    crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/");
                }

                //
                if (trim($result) == '') {
                    $result = "El servicio web PONAL-MULTAS no retorno respuesta";
                }
                $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/ponal/validaciones-' . date("Ym") . '.log';
                $f1 = fopen($name1, "a");
                fwrite($f1, date("Y-m-d") . '|' . date("His") . '|' . $tid . '|' . $id . '|' . $result . chr(13) . chr(10));
                fclose($f1);

                //
                if ((is_string($result) &&
                        (is_object(json_decode($result)) ||
                        is_array(json_decode($result))))) {
                    $resultado = json_decode($result, true);
                    if (isset($resultado["Message"])) {
                        if ($resultado["Message"] == 'Authorization has been denied for this request.') {
                            unlink($name);
                            $reintentar--;
                        } else {
                            $reintentar = 0;
                        }
                    } else {
                        $multadovencido = 'NO';
                        $fecx = date("Ymd");
                        $horx = date("His");
                        foreach ($resultado as $multa) {
                            $arrCampos = array(
                                'fecha',
                                'hora',
                                'tipoidentificacion',
                                'identificacion',
                                'nombres',
                                'apellidos',
                                'nit',
                                'razonsocial',
                                'estado',
                                'fechaimposicion',
                                'multavencida',
                                'direccionhechos',
                                'codigomunicipio',
                                'nombremunicipio',
                                'codigodpto',
                                'nombredpto',
                                'codigobarrio',
                                'nombrebarrio',
                                'numeralinfringido',
                                'articuloinfringido',
                                'idliquidacion'
                            );
                            $arrValores = array(
                                "'" . $fecx . "'",
                                "'" . $horx . "'",
                                "'" . $tid . "'",
                                "'" . $id . "'",
                                "'" . addslashes($multa["NOMBRES"]) . "'",
                                "'" . addslashes($multa["APELLIDOS"]) . "'",
                                "'" . $multa["NIT"] . "'",
                                "'" . addslashes($multa["RAZON_SOCIAL"]) . "'",
                                "'" . $multa["ESTADO"] . "'",
                                "'" . $multa["FECHA_IMPOSICION"] . "'",
                                "'" . $multa["MULTA_VENCIDA"] . "'",
                                "'" . addslashes($multa["DIRECCION_HECHOS"]) . "'",
                                "'" . $multa["COD_MUNICIPIO"] . "'",
                                "'" . addslashes($multa["MUNICIPIO"]) . "'",
                                "'" . $multa["COD_DEPARTAMENTO"] . "'",
                                "'" . addslashes($multa["DEPARTAMENTO"]) . "'",
                                "'" . $multa["COD_BARRIO"] . "'",
                                "'" . addslashes($multa["BARRIO"]) . "'",
                                "'" . addslashes($multa["ARTICULO_INFRINGIDO"]) . "'",
                                "'" . addslashes($multa["NUMERAL_INFRINGIDO"]) . "'",
                                $idliq
                            );
                            insertarRegistrosMysqli($mysqli, 'mreg_multas_ponal', $arrCampos, $arrValores);
                            //
                            if ($multa["MULTA_VENCIDA"] == 'SI') {
                                $multadovencido = 'SI';
                            }
                        }
                        $arrCampos = array(
                            'sincronizomultasponal',
                            'fechasincronizomultasponal',
                            'resultadosincronizomultasponal'
                        );
                        $arrValores = array(
                            "'SI'",
                            "'" . date("Ymd") . "'",
                            "'" . $multadovencido . "'"
                        );
                        regrabarRegistrosMysqli($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "idclase='" . $tid . "' and numid='" . $id . "'");
                        $reintentar = 0;
                    }
                } else {
                    $result = str_replace('"', '', $result);
                    if (substr($result, 0, 16) == 'Para la consulta') {
                        $multadovencido = 'NO';
                        $arrCampos = array(
                            'sincronizomultasponal',
                            'fechasincronizomultasponal',
                            'resultadosincronizomultasponal'
                        );
                        $arrValores = array(
                            "'SI'",
                            "'" . date("Ymd") . "'",
                            "'" . $multadovencido . "'"
                        );
                    } else {
                        $multadovencido = 'ER';
                        $arrCampos = array(
                            'sincronizomultasponal',
                            'fechasincronizomultasponal',
                            'resultadosincronizomultasponal'
                        );
                        $arrValores = array(
                            "'SI'",
                            "'" . date("Ymd") . "'",
                            "'" . $multadovencido . "'"
                        );
                    }
                    regrabarRegistrosMysqli($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "idclase='" . $tid . "' and numid='" . $id . "'");
                    $reintentar = 0;
                }
            }
        }

        //
        $mysqli->close();

        return $multadovencido;
    }

    public static function cambiarSustitutoHtml($txt) {
        $txt = str_replace("[0]", " ", $txt);
        $txt = str_replace("[1]", "<", $txt);
        $txt = str_replace("[2]", ">", $txt);
        $txt = str_replace("[3]", "/", $txt);
        $txt = str_replace("[4]", " ", $txt);
        $txt = str_replace("[5]", "\"", $txt);
        $txt = str_replace("[6]", "'", $txt);
        $txt = str_replace("[7]", "&", $txt);
        $txt = str_replace("[8]", "?", $txt);
        $txt = str_replace("[9]", "&aacute;", $txt);
        $txt = str_replace("[10]", "&eacute;", $txt);
        $txt = str_replace("[11]", "&iacute;", $txt);
        $txt = str_replace("[12]", "&oacute;", $txt);
        $txt = str_replace("[13]", "&uacute;", $txt);
        $txt = str_replace("[14]", "&ntilde;", $txt);
        $txt = str_replace("[15]", "&ntilde;", $txt);
        $txt = str_replace("[16]", "+", $txt);
        $txt = str_replace("[17]", "#", $txt);
        $txt = str_replace("[18]", "&aacute;", $txt);
        $txt = str_replace("[19]", "&eacute;", $txt);
        $txt = str_replace("[20]", "&iacute;", $txt);
        $txt = str_replace("[21]", "&oacute;", $txt);
        $txt = str_replace("[22]", "&uacute;", $txt);
        $txt = str_replace("[menorque]", "<", $txt);
        $txt = str_replace("[mayorque]", ">", $txt);
        $txt = str_replace("[slash]", "/", $txt);
        $txt = str_replace("[caracterblanco]", "&nbsp;", $txt);
        $txt = str_replace("[comilladoble]", "\"", $txt);
        $txt = str_replace("[comillasimple]", "'", $txt);
        $txt = str_replace("[ampersand]", "&", $txt);
        $txt = str_replace("[interrogacion]", "?", $txt);
        $txt = str_replace("[atilde]", "&aacute;", $txt);
        $txt = str_replace("[etilde]", "&eacute;", $txt);
        $txt = str_replace("[itilde]", "&iacute;", $txt);
        $txt = str_replace("[otilde]", "&oacute;", $txt);
        $txt = str_replace("[utilde]", "&uacute;", $txt);
        $txt = str_replace("[ene]", "&ntilde;", $txt);
        $txt = str_replace("[ENE]", "&ntilde;", $txt);
        $txt = str_replace("[mas]", "+", $txt);
        return $txt;
    }

    public static function consultarEstablecimientosNacionales($dbx, $tide, $ide) {
        $buscartoken = true;
        $name = PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenrues.txt';
        if (file_exists($name)) {
            $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenrues.txt');
            list ($token, $expira) = explode("|", $x);
            $act = date("Y-m-d H:i:s");
            if ($act <= $expira) {
                $buscartoken = false;
            }
        }

        $buscartoken = true;
        if ($buscartoken) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://ruesapi.rues.org.co/Token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "username=SIIUser&password=Webapi2017*&grant_type=password");
            $result = curl_exec($ch);
            curl_close($ch);
            $resultado = json_decode($result, true);
            $access_token = $resultado['access_token'];
            $fecha = date('Y-m-d H:i:s', (strtotime("+20 Hours")));
            $f = fopen($name, "w");
            fwrite($f, $access_token . '|' . $fecha);
            fclose($f);
        }

        //
        if (file_exists($name)) {
            $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenrues.txt');
            list ($access_token, $expira) = explode("|", $x);
        }

        //
        $nameLog = 'api_consultarEstablecimientosNacionales_' . date("Ymd");
        if ($tide != '2') {
            $ide1 = $ide;
            $ide2 = '';
        } else {
            $sep = \funcionesSii2::separarDv($ide);
            $ide1 = $sep["identificacion"];
            $ide2 = $sep["dv"];
        }
        $url = 'https://ruesapi.rues.org.co/api/establecimientos?usuario=' . CODIGO_EMPRESA . '-API' . '&nit=' . $ide1 . '&dv=' . $ide2;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);

        \logSii2::general2($nameLog, $tide . '-' . $ide, '****** PETICION : ' . $url . ' *** RESPUESTA : ' . $result);
        \logSii2::general2($nameLog, $tide . '-' . $ide, '');
        //
        if (!\funcionesSii2::isJson($result)) {
            return array();
        }

        //
        $resultado = json_decode($result, true);
        if (isset($resultado["error"]) && $resultado["error"] != null) {
            return array();
        }

        //
        if ($resultado["establecimientos"] == null) {
            return array();
        }

        //
        $xcon = array();
        $salida = array();
        $ix = 0;
        foreach ($resultado["establecimientos"] as $est) {

            $ind = $est["codigo_camara"] . '-' . $est["matricula"];
            if (!isset($xcon[$ind])) {
                $xcon[$ind] = 1;

                $ix++;
                $salida[$ix] = $est;
                $salida[$ix]["ind"] = $ind;
                $salida[$ix]["nombre_municipio_comercial"] = retornarRegistroMysqli2($dbx, "bas_municipios", "codigomunicipio='" . $est["municipio_comercial"] . "'", "ciudad");

                // Homologa organizacion juridica
                $xorg = '';
                switch ($est["codigo_organizacion_juridica"]) {
                    case "01" : $xorg = '01';
                        break;
                    case "02" : $xorg = '02';
                        break;
                    case "03" : $xorg = '03';
                        break;
                    case "04" : $xorg = '04';
                        break;
                    case "05" : $xorg = '05';
                        break;
                    case "06" : $xorg = '06';
                        break;
                    case "07" : $xorg = '07';
                        break;
                    case "08" : $xorg = '08';
                        break;
                    case "09" : $xorg = '09';
                        break;

                    case "10" : $xorg = '11';
                        break;
                    case "11" : $xorg = '17';
                        break;
                    case "12" : $xorg = '99';
                        break;
                    case "13" : $xorg = '15';
                        break;
                    default: $xorg = '12';
                        break;
                }
                $salida[$ix]["codigo_organizacion_juridica"] = $xorg;

                // Homologo categoria
                $xcat = '';
                switch ($est["codigo_categoria_matricula"]) {
                    case "00" : $xcat = '';
                        break;
                    case "01" : $xcat = '1';
                        break;
                    case "02" : $xcat = '2';
                        break;
                    case "03" : $xcat = '3';
                        break;
                    case "04" : $xcat = '';
                        break;
                }
                $salida[$ix]["codigo_categoria_matricula"] = $xcat;

                // Ajusta fecha de renovacion
                if ($salida[$ix]["fecha_renovacion"] == '') {
                    if (isset($salida[$ix]["fecha_matricula"]) && $salida[$ix]["fecha_matricula"] != '') {
                        $salida[$ix]["fecha_renovacion"] = $salida[$ix]["fecha_matricula"];
                    }
                }
            }
        }
        $salida1 = \funcionesSii2::ordenarMatriz($salida, "ind");

        //
        unset($resultado);

        //
        return $salida1;
    }

    public static function consultarSaldoAfiliado($dbx, $matricula) {

        $fcorte = retornarRegistroMysqli2($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");

        //
        $formaCalculoAfiliacion = retornarClaveValorSii2($dbx, '90.01.60');

        $salida = array(
            'valorultpagoafi' => 0,
            'fechaultpagoafi' => '',
            'pago' => 0,
            'cupo' => 0
        );

        //
        $exp = retornarRegistroMysqli2($dbx, "mreg_est_inscritos", "matricula='" . $matricula . "'");

        //
        $arrSerAfil = retornarRegistrosMysqli2($dbx, "mreg_servicios", "grupoventas='02'", "idservicio");
        $Servicios = "";
        $ServiciosAfiliacion = array();
        foreach ($arrSerAfil as $ServAfil) {
            if ($Servicios != '') {
                $Servicios .= ",";
            }
            $Servicios .= "'" . $ServAfil["idservicio"] . "'";
            $ServiciosAfiliacion[] = $ServAfil["idservicio"];
        }

        //
        $arrFecValAfi = retornarRegistroMysqli2($dbx, 'mreg_est_recibos', "matricula='" . $matricula . "' and servicio in (" . $Servicios . ") and ctranulacion = '0' and (substring(numerorecibo,1,1)='R' or substring(numerorecibo,1,1)='S') order by fecoperacion desc limit 1");
        $salida["valorultpagoafi"] = $arrFecValAfi["valor"];
        $salida["fechaultpagoafi"] = $arrFecValAfi["fecoperacion"];
        unset($arrFecValAfi);

        //
        $detalle = array();
        $iDetalle = 0;
        if ($exp["ultanoren"] == date("Y")) {
            $anox = date("Y");
            $feciniafi = $exp["fecrenovacion"];
        } else {
            if (date("Ymd") <= $fcorte) {
                $anox = date("Y") - 1;
                $feciniafi = (date("Y") - 1) . '0101';
            } else {
                $anox = date("Y");
                $feciniafi = date("Y") . '0101';
            }
        }
        $inix = retornarRegistroMysqli2($dbx, 'mreg_saldos_afiliados_sirp', "ano='" . $anox . "' and matricula='" . $matricula . "'");
        if ($inix && !empty($inix)) {
            $iDetalle++;
            $detalle[$iDetalle] = array(
                'tipo' => 'SaldoInicial-SIRP',
                'fecha' => $anox,
                'recibo' => '',
                'valor' => $inix["cupocargado"],
                'cupo' => $inix["cupocargado"] - $inix["cupoconsumido"]
            );
            $salida["cupo"] = $inix["cupocargado"] - $inix["cupoconsumido"];
        }

        //
        $arrRecs = retornarRegistrosMysqli2($dbx, 'mreg_est_recibos', "(matricula='" . $matricula . "') and ctranulacion = '0' and left(numerorecibo,1) IN ('H','G','R','S') and fecoperacion >= '" . $feciniafi . "'", "fecoperacion");
        if ($arrRecs && !empty($arrRecs)) {
            foreach ($arrRecs as $rx) {
                if (in_array($rx["servicio"], $ServiciosAfiliacion)) {
                    // Si se cambia de año en el pago, se reinicia el histórico e pagos
                    if (substr($rx["fecoperacion"], 0, 4) != substr($feciniafi, 0, 4)) {
                        $iDetalle = 0;
                        $detalle = array();
                        $salida["cupo"] = 0;
                        $salida["pago"] = 0;
                    }

                    //
                    $iDetalle++;
                    $detalle[$iDetalle] = array(
                        'tipo' => 'PagoAfiliación',
                        'fecha' => $rx["fecoperacion"],
                        'recibo' => $rx["numerorecibo"],
                        'valor' => $rx["valor"],
                        'cupo' => 0
                    );
                    $salida["pago"] = $salida["pago"] + $rx["valor"];
                    if ($formaCalculoAfiliacion != '') {
                        if ($formaCalculoAfiliacion == 'RANGO_VAL_AFI') {
                            $arrRan = retornarRegistrosMysqli2($dbx, 'mreg_rangos_cupo_afiliacion', "ano='" . date("Y") . "'", "orden");
                            foreach ($arrRan as $rx1) {
                                if ($rx1["minimo"] <= $salida["pago"] && $rx1["maximo"] >= $salida["pago"]) {
                                    $salida["cupo"] = $rx1["cupo"];
                                }
                            }
                            unset($arrRan);
                            unset($rx1);
                        } else {
                            $salida["cupo"] = round(doubleval($formaCalculoAfiliacion) * $salida["pago"], 0);
                        }
                    }
                    $detalle[$iDetalle]["cupo"] = $salida["cupo"];
                }
                if ($salida["cupo"] > 0) {
                    if ($rx["tipogasto"] == '1') {
                        if ($salida["cupo"] - $rx["valor"] >= 0) {
                            $salida["cupo"] = $salida["cupo"] - $rx["valor"];
                        } else {
                            $salida["cupo"] = 0;
                        }
                        $iDetalle++;
                        $detalle[$iDetalle] = array(
                            'tipo' => 'Consumo',
                            'fecha' => $rx["fecoperacion"],
                            'recibo' => $rx["numerorecibo"],
                            'valor' => $rx["valor"],
                            'cupo' => $salida["cupo"]
                        );
                    }
                    // }
                }
            }
        }

        //
        return $salida;
    }

    public static function consultarSaldoAfiliadoCantidad($mysqli = null, $matricula) {

        $cerrarmysql = 'no';
        if ($mysqli == null) {
            $cerrarmysql = 'si';
            $fuente = '';
            include ($_SESSION ["generales"] ["pathabsoluto"] . '/librerias/funciones/asignaBD.php');
            $mysqli = new mysqli($dbhost, $dbusuario, $dbpassword, $dbname);
        }

        $fcorte = retornarRegistroMysqli2($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");

        //
        $cupo = 0;

        //
        $exp = retornarRegistroMysqli($mysqli, "mreg_est_inscritos", "matricula='" . $matricula . "'");

        //
        $arrSerAfil = retornarRegistrosMysqli2($mysqli, "mreg_servicios", "grupoventas='02'", "idservicio");
        $Servicios = "";
        $ServiciosAfiliacion = array();
        foreach ($arrSerAfil as $ServAfil) {
            if ($Servicios != '') {
                $Servicios .= ",";
            }
            $Servicios .= "'" . $ServAfil["idservicio"] . "'";
            $ServiciosAfiliacion[] = $ServAfil["idservicio"];
        }

        //
        $arrFecValAfi = retornarRegistroMysqli2($mysqli, 'mreg_est_recibos', "(matricula='" . $matricula . "' or expedienteafectado='" . $matricula . "') and servicio in (" . $Servicios . ") and ctranulacion = '0' and (substring(numerorecibo,1,1)='R' or substring(numerorecibo,1,1)='S') order by fecoperacion desc limit 1");
        $salida["valorultpagoafi"] = $arrFecValAfi["valor"];
        $salida["fechaultpagoafi"] = $arrFecValAfi["fecoperacion"];
        unset($arrFecValAfi);

        //
        $fecpagoafiliacion = '';
        $detalle = array();
        $iDetalle = 0;
        if ($exp["ultanoren"] == date("Y")) {
            $anox = date("Y");
            $feciniafi = $exp["fecrenovacion"];
        } else {
            if (date("Ymd") <= $fcorte) {
                $anox = date("Y") - 1;
                $feciniafi = (date("Y") - 1) . '0101';
            } else {
                $anox = date("Y");
                $feciniafi = date("Y") . '0101';
            }
        }

        //
        $arrRecs = retornarRegistrosMysqli2($mysqli, 'mreg_est_recibos', "(matricula='" . $matricula . "' or expedienteafectado='" . $matricula . "') and ctranulacion = '0' and left(numerorecibo,1) IN ('H','G','R','S') and fecoperacion >= '" . $feciniafi . "'", "fecoperacion");
        if ($arrRecs && !empty($arrRecs)) {
            foreach ($arrRecs as $rx) {
                if (in_array($rx["servicio"], $ServiciosAfiliacion)) {
                    // Si se cambia de año en el pago, se reinicia el histórico e pagos
                    if (substr($rx["fecoperacion"], 0, 4) != substr($feciniafi, 0, 4)) {
                        $iDetalle = 0;
                        $detalle = array();
                        $fecpagoafiliacion = '';
                    } else {
                        if ($fecpagoafiliacion == '') {
                            $fecpagoafiliacion = $rx["fecoperacion"];
                        }
                    }

                    //
                    $iDetalle++;
                    $detalle[$iDetalle] = array(
                        'tipo' => 'PagoAfiliación',
                        'fecha' => $rx["fecoperacion"],
                        'recibo' => $rx["numerorecibo"],
                        'valor' => $rx["valor"],
                        'cantidad' => CANTIDAD_CERTIFICADOS_CUPO_AFILIADO,
                        'cupo' => CANTIDAD_CERTIFICADOS_CUPO_AFILIADO
                    );
                    $cupo = CANTIDAD_CERTIFICADOS_CUPO_AFILIADO;
                }
            }
            foreach ($arrRecs as $rx) {
                if (!in_array($rx["servicio"], $ServiciosAfiliacion)) {
                    if ($cupo > 0) {
                        if ($rx["tipogasto"] == '1') {
                            if ($rx["fecoperacion"] >= $fecpagoafiliacion) {
                                if ($cupo - $rx["cantidad"] >= 0) {
                                    $cupo = $cupo - $rx["cantidad"];
                                } else {
                                    $cupo = 0;
                                }
                                $iDetalle++;
                                $detalle[$iDetalle] = array(
                                    'tipo' => 'Consumo',
                                    'fecha' => $rx["fecoperacion"],
                                    'recibo' => $rx["numerorecibo"],
                                    'valor' => $rx["valor"],
                                    'cantidad' => $rx["cantidad"],
                                    'cupo' => $cupo
                                );
                            }
                        }
                    }
                }
            }
        }

        //
        if ($cerrarmysql == 'si') {
            $mysqli->close();
        }

        //
        return $detalle;
    }

    public static function crearIndex($dir) {

        if (!file_exists($dir . '/index.html')) {
            $f = fopen($dir . '/index.html', "w");
            $txt = '	
		<!DOCTYPE HTML>
		<html>
		<head>
		<title>Directorio protegido</title>
		<meta name="keywords" content="" />
		<meta name="description" content="" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="language" content="es" />
		<meta http-equiv="cache-control" content="no-cache">
		</head>
		<body>
			<center>
				<h1>Este directorio no puede ser consultado en forma directa, se encuentra protegido</h1>
			</center>
		</body>
	</html>';
            fwrite($f, $txt);
            fclose($f);
        }
        return true;
    }

    public static function desserializarExpedienteMatricula($dbx, $xml) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $retorno = \funcionesGenerales::desserializarExpedienteMatricula($dbx, $xml);
        return $retorno;
    }

    function desserializarExpedienteProponente($dbx, $xml, $codigoempresa = '', $controlprimeravez = 'no', $proceso = 'llamado directo a desserializarExpedienteProponente', $tipotramite = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $retorno = \funcionesGenerales::desserializarExpedienteProponente($dbx, $xml, $controlprimeravez, $proceso, $tipotramite);
        return $retorno;
    }

    public static function diferenciaEntreFechasCalendario($fecha_principal, $fecha_secundaria, $obtener = 'DIAS', $redondear = true) {
        date_default_timezone_set($_SESSION["generales"]["zonahoraria"]);
        $f0 = strtotime($fecha_principal);
        $f1 = strtotime($fecha_secundaria);
        if ($f0 < $f1) {
            $tmp = $f1;
            $f1 = $f0;
            $f0 = $tmp;
        }
        $resultado = ($f0 - $f1);
        switch ($obtener) {
            default: break;
            case "MINUTOS" : $resultado = $resultado / 60;
                break;
            case "HORAS" : $resultado = $resultado / 60 / 60;
                break;
            case "DIAS" :
                $resultado = $resultado / 60 / 60 / 24;
                // $resultado = $resultado+1;   
                break;
            case "SEMANAS" : $resultado = $resultado / 60 / 60 / 24 / 7;
                break;
            case "MESES" : $resultado = $resultado / 60 / 60 / 24 / 30;
                break;
            case "ANOS" : $resultado = $resultado / 60 / 60 / 24 / 30 / 12;
                break;
        }
        if ($redondear) {
            $resultado = round($resultado);
        }
        return $resultado;
    }

    public static function dumpArregloSii($arreglo) {
        $txt = '';
        foreach ($arreglo as $key => $valor) {
            if (!is_array($valor)) {
                if ($valor != '') {
                    $txt .= $key . ' => ' . $valor . chr(13) . chr(10);
                }
            } else {
                $txt .= $key . '(arreglo)' . chr(13) . chr(10);
                foreach ($valor as $key1 => $valor1) {
                    if (!is_array($valor1)) {
                        if ($valor1 != '') {
                            $txt .= '..... ' . $key1 . ' => ' . $valor1 . chr(13) . chr(10);
                        }
                    } else {
                        $txt .= '..... ' . $key1 . '(arreglo)' . chr(13) . chr(10);
                        foreach ($valor1 as $key2 => $valor2) {
                            if (!is_array($valor2)) {
                                if ($valor2 != '') {
                                    $txt .= '.... ..... ' . $key2 . ' => ' . $valor2 . chr(13) . chr(10);
                                }
                            } else {
                                $txt .= '.... .... ' . $key2 . '(arreglo)' . chr(13) . chr(10);
                                foreach ($valor2 as $key3 => $valor3) {
                                    if (!is_array($valor3)) {
                                        if ($valor3 != '') {
                                            $txt .= '.... ..... .... ' . $key3 . ' => ' . $valor3 . chr(13) . chr(10);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $txt;
    }

    public static function encontrarPathImagen($dbx, $tam = 0, $directorio = 'mreg', $tipoanexo = '000') {
        $disco = intval(\funcionesSii2::localizarDiscoActual($dbx, $tipoanexo));
        if ($disco === false) {
            return false;
        }

        //
        if (ltrim($disco, "0") == '') {
            $disco = 1;
        }

        if (!defined('TIPO_REPOSITORIO_NUEVAS_IMAGENES')) {
            define('TIPO_REPOSITORIO_NUEVAS_IMAGENES', 'LOCAL');
        }

        $peso = 0;
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionessii2::crearIndex($path);
            }

            // ubica el disco
            $ok = 'no';
            $limite = 0;
            while ($ok != 'si') {
                $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco);
                if (!is_dir($path) || !is_readable($path)) {
                    mkdir($path, 0777);
                    \funcionessii2::crearIndex($path);
                }
                $salida = array();
                exec('du --block-size=1000 ' . $path, $salida);
                $list = explode("/", $salida[0]);
                $peso = intval(trim($list[0]));
                if ($peso < (TAMANO_DISCO_KB)) {
                    $ok = 'si';
                } else {
                    $disco++;
                    $limite++;
                    if ($limite == 100) {
                        $ok = 'si';
                    }
                }
            }
        }

        // SI el repositorio es Remoto
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'WS') {
            $path = $_SESSION["generales"]["codigoempresa"] . '/' . $directorio;
            crearDirectorioWsRemoto($path);

            // ubica el disco
            $ok = 'no';
            $limite = 0;
            while ($ok != 'si') {
                $path = $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco);
                crearDirectorioWsRemoto($path);
                $peso = tamanoDirectorioWsRemoto($path);
                if ($peso === false) {
                    $ok = 'si';
                } else {
                    if ($peso < (TAMANO_DISCO_KB)) {
                        $ok = 'si';
                    } else {
                        $disco++;
                        $limite++;
                        if ($limite == 100) {
                            $ok = 'si';
                        }
                    }
                }
            }
        }

        // SI el repositorio es Aws S3
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4') {
            $path = $_SESSION["generales"]["codigoempresa"] . '/' . $directorio;

            // ubica el disco
            $ok = 'no';
            $limite = 0;
            while ($ok != 'si') {
                $path = $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco);
                $peso = intval(tamanoS3Version4($path));
                if ($peso === false) {
                    $ok = 'si';
                } else {
                    if ($peso < (TAMANO_DISCO_KB)) {
                        $ok = 'si';
                    } else {
                        $disco++;
                        $limite++;
                        if ($limite == 100) {
                            $ok = 'si';
                        }
                    }
                }
            }
        }


        if ($peso === false) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar el directorio a utilizar';
            return false;
        }

        //
        // Actualización del campo "disco" para el registro mercantil en claves-valor
        $arrTem = retornarRegistroMysqli2($dbx, 'bas_tipoanexodocumentos', "id='" . $tipoanexo . "'");
        if ($arrTem === false || empty($arrTem)) {
            $_SESSION["generales"]["mensajeerror"] = 'Imposible localizar tipo de anexo en tabla bas_tipoanexodocumentos';
            return false;
        }

        $arrTem1 = retornarRegistroMysqli2($dbx, 'bas_claves_valor', "idorden='" . $arrTem["clavevalor"] . "'");
        if ($arrTem1 === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Imposible localizar tipo de anexo en tabla bas_claves_valor';
            return false;
        }

        //
        $arrCampos = array(
            'id',
            'valor'
        );
        $arrValores = array(
            $arrTem1["id"],
            "'" . sprintf("%03s", $disco) . "'"
        );

        //
        if (contarRegistrosMysqli2($dbx, 'claves_valor', "id=" . $arrTem1["id"]) == 0) {
            insertarRegistrosMysqli2($dbx, 'claves_valor', $arrCampos, $arrValores);
        } else {
            regrabarRegistrosMysqli2($dbx, 'claves_valor', $arrCampos, $arrValores, "id=" . $arrTem1["id"]);
        }

        //
        // Si el repositorio es Local
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            // Crea el directorio base, si no existe
            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio)) {
                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio, 0777);
                \funcionesSii2::crearIndex(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio);
            }

            // Crea el disco, si no existe
            if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco))) {
                mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco), 0777);
                \funcionesSii2::crearIndex(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco));
            }
        }

        //
        return $disco;
    }

    public static function diferenciaEntreFechaBase30($fechafinal, $fechainicial) {
        $fechafinal = str_replace(array("-", "/"), "", $fechafinal);
        $fechainicial = str_replace(array("-", "/"), "", $fechainicial);
        $iDias = 0;
        $iFecha = $fechainicial;
        while ($iFecha <= $fechafinal) {
            $ano = intval(substr($iFecha, 0, 4));
            $mes = intval(substr($iFecha, 4, 2));
            $dia = intval(substr($iFecha, 6, 2));

            if ($dia < 31) {
                $iDias++;
            }

            if ($dia == 31) {
                $dia = 1;
                $mes++;
                if ($mes == 13) {
                    $ano++;
                    $mes = 1;
                }
            } else {
                if ($dia == 30) {
                    if (($mes == 4) || ($mes == 6) || ($mes == 9) || ($mes == 11)) {
                        $dia = 1;
                        $mes++;
                    } else {
                        $dia++;
                    }
                } else {
                    if ($dia == 29) {
                        if (($mes == 2)) {
                            $dia = 1;
                            $mes++;
                            $iDias++;
                        } else {
                            $dia++;
                        }
                    } else {
                        if ($dia == 28) {
                            if (($mes == 2)) {
                                if (($ano != 2000) && ($ano != 2004) && ($ano != 2008) && ($ano != 2012) && ($ano != 2014) &&
                                        ($ano != 2018) && ($ano != 2022) && ($ano != 2026) && ($ano != 2030) && ($ano != 2034)) {
                                    $dia = 1;
                                    $mes++;
                                    $iDias++;
                                    $iDias++;
                                } else {
                                    $dia++;
                                }
                            } else {
                                $dia++;
                            }
                        } else {
                            $dia++;
                        }
                    }
                }
            }
            $iFecha = sprintf("%04s", $ano) . sprintf("%02s", $mes) . sprintf("%02s", $dia);
        }
        return $iDias;
    }

    public static function grabarAnexoRadicacion(
            $dbx, $idradicacion = 0, $numerorecibo = '', $numerooperacion = '', $identificacion = '', $nombre = '', $acreedor = '', $nombreacreedor = '', $matricula = '', $proponente = '', $idtipodoc = '', $numdoc = '', $fechadoc = '', $idorigendoc = '', $txtorigendoc = '', $idclasificacion = '', $numcontrato = '', $idfuente = '', $version = 1, $path = '', $estado = '', $fechaescaneo = '', $idusuarioescaneo = '', $idcajaarchivo = '', $idlibroarchivo = '', $observaciones = '', $libro = '', $registro = '', $bandeja = '', $soporterecibo = '', $identificador = '', $tipoanexo = '', $procesoespecial = ''
    ) {
        if (ltrim($idradicacion, "0") == '') {
            $idradicacion = '';
        }

        if (trim($libro) != '') {
            if (strlen($libro) == 2) {
                if ($bandeja == '6.-REGPRO') {
                    $libro = 'RP' . sprintf("%02s", $libro);
                } else {
                    if ($libro >= '01' && $libro <= '49') {
                        $libro = 'RM' . sprintf("%02s", $libro);
                    } else {
                        $libro = 'RE' . sprintf("%02s", $libro);
                    }
                }
            }
        }

        $arrCampos = array(
            'idradicacion',
            'numerorecibo',
            'numerooperacion',
            'libro',
            'registro',
            'identificacion',
            'nombre',
            'acreedor',
            'nombreacreedor',
            'matricula',
            'proponente',
            'idtipodoc',
            'numdoc',
            'fechadoc',
            'idorigendoc',
            'txtorigendoc',
            'idclasificacion',
            'numcontrato',
            'idfuente',
            'version',
            'path',
            'estado',
            'fechaescaneo',
            'idusuarioescaneo',
            'idcajaarchivo',
            'idlibroarchivo',
            'observaciones',
            'bandeja',
            'soporterecibo',
            'identificador',
            'tipoanexo',
            'procesosespeciales',
            'eliminado'
        );

        //
        $arrValores = array(
            "'" . ltrim($idradicacion, "0") . "'",
            "'" . $numerorecibo . "'",
            "'" . $numerooperacion . "'",
            "'" . $libro . "'",
            "'" . $registro . "'",
            "'" . $identificacion . "'",
            "'" . addslashes(strtoupper($nombre)) . "'",
            "'" . $acreedor . "'",
            "'" . addslashes(strtoupper($nombreacreedor)) . "'",
            "'" . $matricula . "'",
            "'" . $proponente . "'",
            "'" . $idtipodoc . "'",
            "'" . $numdoc . "'",
            "'" . $fechadoc . "'",
            "'" . $idorigendoc . "'",
            "'" . addslashes(strtoupper($txtorigendoc)) . "'",
            "'" . $idclasificacion . "'",
            "'" . $numcontrato . "'",
            "'" . $idfuente . "'",
            $version,
            "'" . $path . "'",
            "'" . $estado . "'",
            "'" . $fechaescaneo . "'",
            "'" . $idusuarioescaneo . "'",
            "'" . $idcajaarchivo . "'",
            "'" . $idlibroarchivo . "'",
            "'" . addslashes(strtoupper($observaciones)) . "'",
            "'" . $bandeja . "'",
            "'" . $soporterecibo . "'",
            "'" . $identificador . "'",
            "'" . $tipoanexo . "'",
            "'" . $procesoespecial . "'",
            "'NO'"
        );
        insertarRegistrosMysqli2($dbx, 'mreg_radicacionesanexos', $arrCampos, $arrValores, 'si');
        return $_SESSION["generales"]["lastId"];
    }

    public static function grabarHistoricos($dbx, $fecha = '', $hora = '', $mat = '', $pro = '', $tipoid = '', $numid = '', $nom = '', $tt = '', $reg = '', $lib = '', $numreg = '', $rec = '', $ope = '', $codbar = '', $xmlo = '', $xmlf = '', $usu = '', $ip = '') {
        $arrCampos = array(
            'fecha',
            'hora',
            'matricula',
            'proponente',
            'tipoidentificacion',
            'identificacion',
            'nombre',
            'tipotramite',
            'registro',
            'libro',
            'inscripcion',
            'recibo',
            'operacion',
            'codigobarras',
            'xmloriginal',
            'xmlfinal',
            'usuario',
            'ip'
        );
        $arrValores = array(
            "'" . $fecha . "'",
            "'" . $hora . "'",
            "'" . $mat . "'",
            "'" . $pro . "'",
            "'" . $tipoid . "'",
            "'" . $numid . "'",
            "'" . addslashes($nom) . "'",
            "'" . $tt . "'",
            "'" . $reg . "'",
            "'" . $lib . "'",
            "'" . $numreg . "'",
            "'" . $rec . "'",
            "'" . $ope . "'",
            "'" . $codbar . "'",
            "'" . addslashes($xmlo) . "'",
            "'" . addslashes($xmlf) . "'",
            "'" . $usu . "'",
            "'" . $ip . "'"
        );

        insertarRegistrosMysqli2($dbx, 'mreg_historicos_' . date("Y"), $arrCampos, $arrValores);
    }

    public static function grabarLiquidacionMregSii($dbx) {
        require_once ('sanarEntradas.class.php');

        //
        $conteo = contarRegistrosMysqli2($dbx, 'mreg_liquidacion', 'idliquidacion=' . $_SESSION["tramite"]["numeroliquidacion"]);
        if ($conteo === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error al ubicar la liquidación en mreg_liquidacion';
            return false;
        }

        //
        if (!isset($_SESSION["tramite"]["tipomatricula"])) {
            $_SESSION["tramite"]["tipomatricula"] = '';
        }

        if (!isset($_SESSION["tramite"]["subtipotramite"])) {
            $_SESSION["tramite"]["subtipotramite"] = '';
        }

        if (!isset($_SESSION["tramite"]["origen"])) {
            $_SESSION["tramite"]["origen"] = '';
        }

        if (!isset($_SESSION["tramite"]["sede"])) {
            $_SESSION["tramite"]["sede"] = '';
        }
        if ($_SESSION["tramite"]["sede"] == '') {
            if ($_SESSION["tramite"]["idusuario"] == 'USUPUBXX') {
                $_SESSION["tramite"]["sede"] = '99';
            } else {
                if ($_SESSION["tramite"]["idusuario"] == 'RUE') {
                    $_SESSION["tramite"]["sede"] = '90';
                } else {
                    if (!isset($_SESSION["generales"]["sedeusuario"])) {
                        $_SESSION["generales"]["sedeusuario"] = '01';
                    }
                    $_SESSION["tramite"]["sede"] = $_SESSION["generales"]["sedeusuario"];
                }
            }
        }
        if ($_SESSION["tramite"]["sede"] == '') {
            $_SESSION["tramite"]["sede"] = '01';
        }

        // Datos base
        if (!isset($_SESSION["tramite"]["matriculabase"])) {
            $_SESSION["tramite"]["matriculabase"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrebase"])) {
            $_SESSION["tramite"]["nombrebase"] = '';
        }
        if (!isset($_SESSION["tramite"]["nom1base"])) {
            $_SESSION["tramite"]["nom1base"] = '';
        }
        if (!isset($_SESSION["tramite"]["nom2base"])) {
            $_SESSION["tramite"]["nom2base"] = '';
        }
        if (!isset($_SESSION["tramite"]["ape1base"])) {
            $_SESSION["tramite"]["ape1base"] = '';
        }
        if (!isset($_SESSION["tramite"]["ape2base"])) {
            $_SESSION["tramite"]["ape2base"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidentificacionbase"])) {
            $_SESSION["tramite"]["tipoidentificacionbase"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionbase"])) {
            $_SESSION["tramite"]["identificacionbase"] = '';
        }
        if (!isset($_SESSION["tramite"]["organizacionbase"])) {
            $_SESSION["tramite"]["organizacionbase"] = '';
        }
        if (!isset($_SESSION["tramite"]["categoriabase"])) {
            $_SESSION["tramite"]["categoriabase"] = '';
        }
        if (!isset($_SESSION["tramite"]["afiliadobase"])) {
            $_SESSION["tramite"]["afiliadobase"] = '';
        }

        if (!isset($_SESSION["tramite"]["idexpedientebase"])) {
            $_SESSION["tramite"]["idexpedientebase"] = '';
        }
        if (!isset($_SESSION["tramite"]["idmatriculabase"])) {
            $_SESSION["tramite"]["idmatriculabase"] = '';
        }
        if (!isset($_SESSION["tramite"]["idproponentebase"])) {
            $_SESSION["tramite"]["idproponentebase"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoproponente"])) {
            $_SESSION["tramite"]["tipoproponente"] = '';
        }

        // Datos del cliente
        if (!isset($_SESSION["tramite"]["tipocliente"])) {
            $_SESSION["tramite"]["tipocliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["razonsocialcliente"])) {
            $_SESSION["tramite"]["razonsocialcliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellidocliente"])) {
            $_SESSION["tramite"]["apellidocliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1cliente"])) {
            $_SESSION["tramite"]["apellido1cliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2cliente"])) {
            $_SESSION["tramite"]["apellido2cliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1cliente"])) {
            $_SESSION["tramite"]["nombre1cliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2cliente"])) {
            $_SESSION["tramite"]["nombre2cliente"] = '';
        }

        // Datos del pagador
        if (!isset($_SESSION["tramite"]["tipopagador"])) {
            $_SESSION["tramite"]["tipopagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["razonsocialpagador"])) {
            $_SESSION["tramite"]["razonsocialpagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1pagador"])) {
            $_SESSION["tramite"]["nombre1pagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2pagador"])) {
            $_SESSION["tramite"]["nombre2pagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1pagador"])) {
            $_SESSION["tramite"]["apellido1pagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2pagador"])) {
            $_SESSION["tramite"]["apellido2pagador"] = '';
        }

        if (!isset($_SESSION["tramite"]["movilpagador"])) {
            $_SESSION["tramite"]["movilpagador"] = '';
        }


        if (!isset($_SESSION["tramite"]["idsolicitudpago"])) {
            $_SESSION["tramite"]["idsolicitudpago"] = '';
        }
        if ($_SESSION["tramite"]["idsolicitudpago"] == '') {
            $_SESSION["tramite"]["idsolicitudpago"] = 0;
        }

        //
        if (!isset($_SESSION["tramite"]["alertaid"])) {
            $_SESSION["tramite"]["alertaid"] = 0;
        }
        if (!isset($_SESSION["tramite"]["alertaservicio"])) {
            $_SESSION["tramite"]["alertaservicio"] = '';
        }
        if (!isset($_SESSION["tramite"]["alertavalor"])) {
            $_SESSION["tramite"]["alertavalor"] = 0;
        }
        if (!isset($_SESSION["tramite"]["ctrcancelacion"])) {
            $_SESSION["tramite"]["ctrcancelacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["idasesor"])) {
            $_SESSION["tramite"]["idasesor"] = '';
        }
        if (!isset($_SESSION["tramite"]["numeroempleados"])) {
            $_SESSION["tramite"]["numeroempleados"] = 0;
        }
        if (!isset($_SESSION["tramite"]["nombrepagador"])) {
            $_SESSION["tramite"]["nombrepagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellidopagador"])) {
            $_SESSION["tramite"]["apellidopagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidentificacionpagador"])) {
            $_SESSION["tramite"]["tipoidentificacionpagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionpagador"])) {
            $_SESSION["tramite"]["identificacionpagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionpagador"])) {
            $_SESSION["tramite"]["direccionpagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonopagador"])) {
            $_SESSION["tramite"]["telefonopagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["municipiopagador"])) {
            $_SESSION["tramite"]["municipiopagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailpagador"])) {
            $_SESSION["tramite"]["emailpagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["pagoprepago"])) {
            $_SESSION["tramite"]["pagoprepago"] = 0;
        }
        if (!isset($_SESSION["tramite"]["pagoafiliado"])) {
            $_SESSION["tramite"]["pagoafiliado"] = 0;
        }
        if (!isset($_SESSION["tramite"]["pagoacredito"])) {
            $_SESSION["tramite"]["pagoacredito"] = 0;
        }
        if (!isset($_SESSION["tramite"]["pagoconsignacion"])) {
            $_SESSION["tramite"]["pagoconsignacion"] = 0;
        }
        if (!isset($_SESSION["tramite"]["proyectocaja"])) {
            $_SESSION["tramite"]["proyectocaja"] = '001';
        }
        if (!isset($_SESSION["tramite"]["cargoafiliacion"])) {
            $_SESSION["tramite"]["cargoafiliacion"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["cargogastoadministrativo"])) {
            $_SESSION["tramite"]["cargogastoadministrativo"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["cargoentidadoficial"])) {
            $_SESSION["tramite"]["cargoentidadoficial"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["cargoconsulta"])) {
            $_SESSION["tramite"]["cargoconsulta"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["actualizacionciiuversion4"])) {
            $_SESSION["tramite"]["actualizacionciiuversion4"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["domicilioorigen"])) {
            $_SESSION["tramite"]["domicilioorigen"] = '';
        }
        if (!isset($_SESSION["tramite"]["domiciliodestino"])) {
            $_SESSION["tramite"]["domiciliodestino"] = '';
        }
        if (!isset($_SESSION["tramite"]["reliquidacion"])) {
            $_SESSION["tramite"]["reliquidacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["pagoafiliacion"])) {
            $_SESSION["tramite"]["pagoafiliacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerofactura"])) {
            $_SESSION["tramite"]["numerofactura"] = '';
        }
        if (!isset($_SESSION["tramite"]["nrocontrolsipref"])) {
            $_SESSION["tramite"]["nrocontrolsipref"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoideradicador"])) {
            $_SESSION["tramite"]["tipoideradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["ideradicador"])) {
            $_SESSION["tramite"]["ideradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["fechaexpradicador"])) {
            $_SESSION["tramite"]["fechaexpradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreradicador"])) {
            $_SESSION["tramite"]["nombreradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrebanco"])) {
            $_SESSION["tramite"]["nombrebanco"] = '';
        }

        if (!isset($_SESSION["tramite"]["orgpnat"])) {
            $_SESSION["tramite"]["orgpnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidepnat"])) {
            $_SESSION["tramite"]["tipoidepnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["idepnat"])) {
            $_SESSION["tramite"]["idepnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrepnat"])) {
            $_SESSION["tramite"]["nombrepnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["actpnat"])) {
            $_SESSION["tramite"]["actpnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["perpnat"])) {
            $_SESSION["tramite"]["perpnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["munpnat"])) {
            $_SESSION["tramite"]["munpnat"] = '';
        }

        if (!isset($_SESSION["tramite"]["numeromatriculapnat"])) {
            $_SESSION["tramite"]["numeromatriculapnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["camarapnat"])) {
            $_SESSION["tramite"]["camarapnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreest"])) {
            $_SESSION["tramite"]["nombreest"] = '';
        }
        if (!isset($_SESSION["tramite"]["actest"])) {
            $_SESSION["tramite"]["actest"] = '';
        }
        if (!isset($_SESSION["tramite"]["munest"])) {
            $_SESSION["tramite"]["munest"] = '';
        }
        if (!isset($_SESSION["tramite"]["ultanoren"])) {
            $_SESSION["tramite"]["ultanoren"] = '';
        }
        if (!isset($_SESSION["tramite"]["incluirformularios"])) {
            $_SESSION["tramite"]["incluirformularios"] = '';
        }
        if (!isset($_SESSION["tramite"]["incluircertificados"])) {
            $_SESSION["tramite"]["incluircertificados"] = '';
        }
        if (!isset($_SESSION["tramite"]["incluirdiploma"])) {
            $_SESSION["tramite"]["incluirdiploma"] = '';
        }
        if (!isset($_SESSION["tramite"]["incluircartulina"])) {
            $_SESSION["tramite"]["incluircartulina"] = '';
        }
        if (!isset($_SESSION["tramite"]["matricularpnat"])) {
            $_SESSION["tramite"]["matricularpnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["matricularest"])) {
            $_SESSION["tramite"]["matricularest"] = '';
        }
        if (!isset($_SESSION["tramite"]["regimentributario"])) {
            $_SESSION["tramite"]["regimentributario"] = '';
        }
        if (!isset($_SESSION["tramite"]["benart7"])) {
            $_SESSION["tramite"]["benart7"] = 'N';
        }
        if (!isset($_SESSION["tramite"]["benley1780"])) {
            $_SESSION["tramite"]["benley1780"] = 'N';
        }
        if (!isset($_SESSION["tramite"]["fechanacimientopnat"])) {
            $_SESSION["tramite"]["fechanacimientopnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["controlfirma"])) {
            $_SESSION["tramite"]["controlfirma"] = 'N';
        }
        if (!isset($_SESSION["tramite"]["modcom"])) {
            $_SESSION["tramite"]["modcom"] = 'N';
        }
        if (!isset($_SESSION["tramite"]["modnot"])) {
            $_SESSION["tramite"]["modnot"] = 'N';
        }
        if (!isset($_SESSION["tramite"]["modciiu"])) {
            $_SESSION["tramite"]["modciiu"] = 'N';
        }
        if (!isset($_SESSION["tramite"]["modnombre"])) {
            $_SESSION["tramite"]["modnombre"] = 'N';
        }

        if (!isset($_SESSION["tramite"]["nombrepjur"])) {
            $_SESSION["tramite"]["nombrepjur"] = '';
        }
        if (!isset($_SESSION["tramite"]["perpjur"])) {
            $_SESSION["tramite"]["perpjur"] = '';
        }
        if (!isset($_SESSION["tramite"]["actpjur"])) {
            $_SESSION["tramite"]["actpjur"] = '';
        }
        if (!isset($_SESSION["tramite"]["munpjur"])) {
            $_SESSION["tramite"]["munpjur"] = '';
        }
        if (!isset($_SESSION["tramite"]["orgpjur"])) {
            $_SESSION["tramite"]["orgpjur"] = '';
        }

        if (!isset($_SESSION["tramite"]["nombresuc"])) {
            $_SESSION["tramite"]["nombresuc"] = '';
        }
        if (!isset($_SESSION["tramite"]["actsuc"])) {
            $_SESSION["tramite"]["actsuc"] = '';
        }
        if (!isset($_SESSION["tramite"]["munsuc"])) {
            $_SESSION["tramite"]["munsuc"] = '';
        }
        if (!isset($_SESSION["tramite"]["orgsuc"])) {
            $_SESSION["tramite"]["orgsuc"] = '';
        }

        if (!isset($_SESSION["tramite"]["nombreage"])) {
            $_SESSION["tramite"]["nombreage"] = '';
        }
        if (!isset($_SESSION["tramite"]["actage"])) {
            $_SESSION["tramite"]["actage"] = '';
        }
        if (!isset($_SESSION["tramite"]["munage"])) {
            $_SESSION["tramite"]["munage"] = '';
        }
        if (!isset($_SESSION["tramite"]["orgage"])) {
            $_SESSION["tramite"]["orgage"] = '';
        }

        if (!isset($_SESSION["tramite"]["matriculacambidom"])) {
            $_SESSION["tramite"]["matriculacambidom"] = '';
        }
        if (!isset($_SESSION["tramite"]["camaracambidom"])) {
            $_SESSION["tramite"]["camaracambidom"] = '';
        }
        if (!isset($_SESSION["tramite"]["municipiocambidom"])) {
            $_SESSION["tramite"]["municipiocambidom"] = '';
        }
        if (!isset($_SESSION["tramite"]["fecmatcambidom"])) {
            $_SESSION["tramite"]["fecmatcambidom"] = '';
        }
        if (!isset($_SESSION["tramite"]["fecrencambidom"])) {
            $_SESSION["tramite"]["fecrencambidom"] = '';
        }

        if (ltrim($_SESSION["tramite"]["actpnat"], "0") == '') {
            $_SESSION["tramite"]["actpnat"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["perpnat"], "0") == '') {
            $_SESSION["tramite"]["perpnat"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["perpjur"], "0") == '') {
            $_SESSION["tramite"]["perpjur"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["actest"], "0") == '') {
            $_SESSION["tramite"]["actest"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["actpjur"], "0") == '') {
            $_SESSION["tramite"]["actpjur"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["actsuc"], "0") == '') {
            $_SESSION["tramite"]["actsuc"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["actage"], "0") == '') {
            $_SESSION["tramite"]["actage"] = 0;
        }

        if (!isset($_SESSION["tramite"]["capital"])) {
            $_SESSION["tramite"]["capital"] = 0;
        }
        if (!isset($_SESSION["tramite"]["tipodoc"])) {
            $_SESSION["tramite"]["tipodoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["numdoc"])) {
            $_SESSION["tramite"]["numdoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["fechadoc"])) {
            $_SESSION["tramite"]["fechadoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["origendoc"])) {
            $_SESSION["tramite"]["origendoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["mundoc"])) {
            $_SESSION["tramite"]["mundoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["organizacion"])) {
            $_SESSION["tramite"]["organizacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["categoria"])) {
            $_SESSION["tramite"]["categoria"] = '';
        }

        if (!isset($_SESSION["tramite"]["tipoiderepleg"])) {
            $_SESSION["tramite"]["tipoiderepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["iderepleg"])) {
            $_SESSION["tramite"]["iderepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrerepleg"])) {
            $_SESSION["tramite"]["nombrerepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1repleg"])) {
            $_SESSION["tramite"]["nombre1repleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2repleg"])) {
            $_SESSION["tramite"]["nombre2repleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1repleg"])) {
            $_SESSION["tramite"]["apellido1repleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2repleg"])) {
            $_SESSION["tramite"]["apellido2repleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["cargorepleg"])) {
            $_SESSION["tramite"]["cargorepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailrepleg"])) {
            $_SESSION["tramite"]["emailrepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["firmorepleg"])) {
            $_SESSION["tramite"]["firmorepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularrepleg"])) {
            $_SESSION["tramite"]["celularrepleg"] = '';
        }

        if (!isset($_SESSION["tramite"]["tipoideradicador"])) {
            $_SESSION["tramite"]["tipoideradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["ideradicador"])) {
            $_SESSION["tramite"]["ideradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreradicador"])) {
            $_SESSION["tramite"]["nombreradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailradicador"])) {
            $_SESSION["tramite"]["emailradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonoradicador"])) {
            $_SESSION["tramite"]["telefonoradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularradicador"])) {
            $_SESSION["tramite"]["celularradicador"] = '';
        }

        if (!isset($_SESSION["tramite"]["tipolibro"])) {
            $_SESSION["tramite"]["tipolibro"] = '';
        }
        if (!isset($_SESSION["tramite"]["codigolibro"])) {
            $_SESSION["tramite"]["codigolibro"] = '';
        }
        if (!isset($_SESSION["tramite"]["primeravez"])) {
            $_SESSION["tramite"]["primeravez"] = '';
        }
        if (!isset($_SESSION["tramite"]["confirmadigital"])) {
            $_SESSION["tramite"]["confirmadigital"] = '';
        }

        if (!isset($_SESSION["tramite"]["iderevfis"])) {
            $_SESSION["tramite"]["iderevfis"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1revfis"])) {
            $_SESSION["tramite"]["nombre1revfis"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2revfis"])) {
            $_SESSION["tramite"]["nombre2revfis"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1revfis"])) {
            $_SESSION["tramite"]["apellido1revfis"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2revfis"])) {
            $_SESSION["tramite"]["apellido2revfis"] = '';
        }
        if (!isset($_SESSION["tramite"]["cargorevfis"])) {
            $_SESSION["tramite"]["cargorevfis"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailrevfis"])) {
            $_SESSION["tramite"]["emailrevfis"] = '';
        }
        if (!isset($_SESSION["tramite"]["firmorevfis"])) {
            $_SESSION["tramite"]["firmorevfis"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularrevfis"])) {
            $_SESSION["tramite"]["celularrevfis"] = '';
        }

        if (!isset($_SESSION["tramite"]["idepreasa"])) {
            $_SESSION["tramite"]["idepreasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1preasa"])) {
            $_SESSION["tramite"]["nombre1preasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2preasa"])) {
            $_SESSION["tramite"]["nombre2preasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1preasa"])) {
            $_SESSION["tramite"]["apellido1preasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2preasa"])) {
            $_SESSION["tramite"]["apellido2preasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["cargopreasa"])) {
            $_SESSION["tramite"]["cargopreasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailpreasa"])) {
            $_SESSION["tramite"]["emailpreasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["firmopreasa"])) {
            $_SESSION["tramite"]["firmopreasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularpreasa"])) {
            $_SESSION["tramite"]["celularpreasa"] = '';
        }

        if (!isset($_SESSION["tramite"]["idesecasa"])) {
            $_SESSION["tramite"]["idesecasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1secasa"])) {
            $_SESSION["tramite"]["nombre1secasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2secasa"])) {
            $_SESSION["tramite"]["nombre2secasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1secasa"])) {
            $_SESSION["tramite"]["apellido1secasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2secasa"])) {
            $_SESSION["tramite"]["apellido2secasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["cargosecasa"])) {
            $_SESSION["tramite"]["cargosecasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailsecasa"])) {
            $_SESSION["tramite"]["emailsecasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["firmosecasa"])) {
            $_SESSION["tramite"]["firmosecasa"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularsecasa"])) {
            $_SESSION["tramite"]["celularsecasa"] = '';
        }

        if (ltrim($_SESSION["tramite"]["actpnat"], "0") == '') {
            $_SESSION["tramite"]["actpnat"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["perpnat"], "0") == '') {
            $_SESSION["tramite"]["perpnat"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["actest"], "0") == '') {
            $_SESSION["tramite"]["actest"] = 0;
        }
        if (ltrim($_SESSION["tramite"]["capital"], "0") == '') {
            $_SESSION["tramite"]["capital"] = 0;
        }

        if (!isset($_SESSION["tramite"]["tipoidentificacionaceptante"])) {
            $_SESSION["tramite"]["tipoidentificacionaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionaceptante"])) {
            $_SESSION["tramite"]["identificacionaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1aceptante"])) {
            $_SESSION["tramite"]["nombre1aceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2aceptante"])) {
            $_SESSION["tramite"]["nombre2aceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1aceptante"])) {
            $_SESSION["tramite"]["apellido1aceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2aceptante"])) {
            $_SESSION["tramite"]["apellido2aceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionaceptante"])) {
            $_SESSION["tramite"]["direccionaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["municipioaceptante"])) {
            $_SESSION["tramite"]["municipioaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailaceptante"])) {
            $_SESSION["tramite"]["emailaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonoaceptante"])) {
            $_SESSION["tramite"]["telefonoaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularaceptante"])) {
            $_SESSION["tramite"]["celularaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["cargoaceptante"])) {
            $_SESSION["tramite"]["cargoaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["fechadocideaceptante"])) {
            $_SESSION["tramite"]["fechadocideaceptante"] = '';
        }

        if (!isset($_SESSION["tramite"]["motivocorreccion"])) {
            $_SESSION["tramite"]["motivocorreccion"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoerror1"])) {
            $_SESSION["tramite"]["tipoerror1"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoerror2"])) {
            $_SESSION["tramite"]["tipoerror2"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoerror3"])) {
            $_SESSION["tramite"]["tipoerror3"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidentificacioncor"])) {
            $_SESSION["tramite"]["tipoidentificacioncor"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacioncor"])) {
            $_SESSION["tramite"]["identificacioncor"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1cor"])) {
            $_SESSION["tramite"]["nombre1cor"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2cor"])) {
            $_SESSION["tramite"]["nombre2cor"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1cor"])) {
            $_SESSION["tramite"]["apellido1cor"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2cor"])) {
            $_SESSION["tramite"]["apellido2cor"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccioncor"])) {
            $_SESSION["tramite"]["direccioncor"] = '';
        }
        if (!isset($_SESSION["tramite"]["municipiocor"])) {
            $_SESSION["tramite"]["municipiocor"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailcor"])) {
            $_SESSION["tramite"]["emailcor"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonocor"])) {
            $_SESSION["tramite"]["telefonocor"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularcor"])) {
            $_SESSION["tramite"]["celularcor"] = '';
        }


        if (!isset($_SESSION["tramite"]["descripcionembargo"])) {
            $_SESSION["tramite"]["descripcionembargo"] = '';
        }
        if (!isset($_SESSION["tramite"]["descripciondesembargo"])) {
            $_SESSION["tramite"]["descripciondesembargo"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidentificaciondemandante"])) {
            $_SESSION["tramite"]["tipoidentificaciondemandante"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificaciondemandante"])) {
            $_SESSION["tramite"]["identificaciondemandante"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombredemandante"])) {
            $_SESSION["tramite"]["nombredemandante"] = '';
        }
        if (!isset($_SESSION["tramite"]["libro"])) {
            $_SESSION["tramite"]["libro"] = '';
        }
        if (!isset($_SESSION["tramite"]["numreg"])) {
            $_SESSION["tramite"]["numreg"] = '';
        }

        if (!isset($_SESSION["tramite"]["descripcionpqr"])) {
            $_SESSION["tramite"]["descripcionpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidentificacionpqr"])) {
            $_SESSION["tramite"]["tipoidentificacionpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionpqr"])) {
            $_SESSION["tramite"]["identificacionpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1pqr"])) {
            $_SESSION["tramite"]["nombre1pqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2pqr"])) {
            $_SESSION["tramite"]["nombre2pqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1pqr"])) {
            $_SESSION["tramite"]["apellido1pqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2pqr"])) {
            $_SESSION["tramite"]["apellido2pqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionpqr"])) {
            $_SESSION["tramite"]["direccionpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["municipiopqr"])) {
            $_SESSION["tramite"]["municipiopqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailpqr"])) {
            $_SESSION["tramite"]["emailpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonopqr"])) {
            $_SESSION["tramite"]["telefonopqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularpqr"])) {
            $_SESSION["tramite"]["celularpqr"] = '';
        }

        if (!isset($_SESSION["tramite"]["descripcionrr"])) {
            $_SESSION["tramite"]["descripcionrr"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidentificacionrr"])) {
            $_SESSION["tramite"]["tipoidentificacionrr"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionrr"])) {
            $_SESSION["tramite"]["identificacionrr"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1rr"])) {
            $_SESSION["tramite"]["nombre1rr"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2rr"])) {
            $_SESSION["tramite"]["nombre2rr"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1rr"])) {
            $_SESSION["tramite"]["apellido1rr"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2rr"])) {
            $_SESSION["tramite"]["apellido2rr"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionrr"])) {
            $_SESSION["tramite"]["direccionrr"] = '';
        }
        if (!isset($_SESSION["tramite"]["municipiorr"])) {
            $_SESSION["tramite"]["municipiorr"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailrr"])) {
            $_SESSION["tramite"]["emailrr"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonorr"])) {
            $_SESSION["tramite"]["telefonorr"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularrr"])) {
            $_SESSION["tramite"]["celularrr"] = '';
        }

        if (!isset($_SESSION["tramite"]["tipocertificado"])) {
            $_SESSION["tramite"]["tipocertificado"] = '';
        }
        if (!isset($_SESSION["tramite"]["explicacion"])) {
            $_SESSION["tramite"]["explicacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["textolibre"])) {
            $_SESSION["tramite"]["textolibre"] = '';
        }

        // Datos de mutación de direccion - comercial
        if (!isset($_SESSION["tramite"]["ant_dircom"])) {
            $_SESSION["tramite"]["ant_dircom"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_telcom1"])) {
            $_SESSION["tramite"]["ant_telcom1"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_telcom2"])) {
            $_SESSION["tramite"]["ant_telcom2"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_faxcom"])) {
            $_SESSION["tramite"]["ant_faxcom"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_celcom"])) {
            $_SESSION["tramite"]["ant_celcom"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_barriocom"])) {
            $_SESSION["tramite"]["ant_barriocom"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_muncom"])) {
            $_SESSION["tramite"]["ant_muncom"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_emailcom"])) {
            $_SESSION["tramite"]["ant_emailcom"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_emailcom2"])) {
            $_SESSION["tramite"]["ant_emailcom2"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_emailcom3"])) {
            $_SESSION["tramite"]["ant_emailcom3"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_numpredial"])) {
            $_SESSION["tramite"]["ant_numpredial"] = '';
        }

        if (!isset($_SESSION["tramite"]["dircom"])) {
            $_SESSION["tramite"]["dircom"] = '';
        }
        if (!isset($_SESSION["tramite"]["telcom1"])) {
            $_SESSION["tramite"]["telcom1"] = '';
        }
        if (!isset($_SESSION["tramite"]["telcom2"])) {
            $_SESSION["tramite"]["telcom2"] = '';
        }
        if (!isset($_SESSION["tramite"]["faxcom"])) {
            $_SESSION["tramite"]["faxcom"] = '';
        }
        if (!isset($_SESSION["tramite"]["celcom"])) {
            $_SESSION["tramite"]["celcom"] = '';
        }
        if (!isset($_SESSION["tramite"]["barriocom"])) {
            $_SESSION["tramite"]["barriocom"] = '';
        }
        if (!isset($_SESSION["tramite"]["muncom"])) {
            $_SESSION["tramite"]["muncom"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailcom"])) {
            $_SESSION["tramite"]["emailcom"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailcom2"])) {
            $_SESSION["tramite"]["emailcom2"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailcom3"])) {
            $_SESSION["tramite"]["emailcom3"] = '';
        }
        if (!isset($_SESSION["tramite"]["numpredial"])) {
            $_SESSION["tramite"]["numpredial"] = '';
        }

        // Datos de mutación de direccion - notificación
        if (!isset($_SESSION["tramite"]["ant_dirnot"])) {
            $_SESSION["tramite"]["ant_dirnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_telnot1"])) {
            $_SESSION["tramite"]["ant_telnot1"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_telnot2"])) {
            $_SESSION["tramite"]["ant_telnot2"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_faxnot"])) {
            $_SESSION["tramite"]["ant_faxnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_celnot"])) {
            $_SESSION["tramite"]["ant_celnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_barrionot"])) {
            $_SESSION["tramite"]["ant_barrionot"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_munnot"])) {
            $_SESSION["tramite"]["ant_munnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_emailnot"])) {
            $_SESSION["tramite"]["ant_emailnot"] = '';
        }

        if (!isset($_SESSION["tramite"]["dirnot"])) {
            $_SESSION["tramite"]["dirnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["telnot1"])) {
            $_SESSION["tramite"]["telnot1"] = '';
        }
        if (!isset($_SESSION["tramite"]["telnot2"])) {
            $_SESSION["tramite"]["telnot2"] = '';
        }
        if (!isset($_SESSION["tramite"]["faxnot"])) {
            $_SESSION["tramite"]["faxnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["celnot"])) {
            $_SESSION["tramite"]["celnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["barrionot"])) {
            $_SESSION["tramite"]["barrionot"] = '';
        }
        if (!isset($_SESSION["tramite"]["munnot"])) {
            $_SESSION["tramite"]["munnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailnot"])) {
            $_SESSION["tramite"]["emailnot"] = '';
        }

        // Datos de mutación de actividad
        if (!isset($_SESSION["tramite"]["ant_versionciiu"])) {
            $_SESSION["tramite"]["ant_versionciiu"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_ciiu11"])) {
            $_SESSION["tramite"]["ant_ciiu11"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_ciiu12"])) {
            $_SESSION["tramite"]["ant_ciiu12"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_ciiu13"])) {
            $_SESSION["tramite"]["ant_ciiu13"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_ciiu14"])) {
            $_SESSION["tramite"]["ant_ciiu14"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_ciiu21"])) {
            $_SESSION["tramite"]["ant_ciiu21"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_ciiu22"])) {
            $_SESSION["tramite"]["ant_ciiu22"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_ciiu23"])) {
            $_SESSION["tramite"]["ant_ciiu23"] = '';
        }
        if (!isset($_SESSION["tramite"]["ant_ciiu24"])) {
            $_SESSION["tramite"]["ant_ciiu24"] = '';
        }

        if (!isset($_SESSION["tramite"]["versionciiu"])) {
            $_SESSION["tramite"]["versionciiu"] = '';
        }
        if (!isset($_SESSION["tramite"]["ciiu11"])) {
            $_SESSION["tramite"]["ciiu11"] = '';
        }
        if (!isset($_SESSION["tramite"]["ciiu12"])) {
            $_SESSION["tramite"]["ciiu12"] = '';
        }
        if (!isset($_SESSION["tramite"]["ciiu13"])) {
            $_SESSION["tramite"]["ciiu13"] = '';
        }
        if (!isset($_SESSION["tramite"]["ciiu14"])) {
            $_SESSION["tramite"]["ciiu14"] = '';
        }
        if (!isset($_SESSION["tramite"]["ciiu21"])) {
            $_SESSION["tramite"]["ciiu21"] = '';
        }
        if (!isset($_SESSION["tramite"]["ciiu22"])) {
            $_SESSION["tramite"]["ciiu22"] = '';
        }
        if (!isset($_SESSION["tramite"]["ciiu23"])) {
            $_SESSION["tramite"]["ciiu23"] = '';
        }
        if (!isset($_SESSION["tramite"]["ciiu24"])) {
            $_SESSION["tramite"]["ciiu24"] = '';
        }

        // Datos mutación de nombre
        if (!isset($_SESSION["tramite"]["nombreanterior"])) {
            $_SESSION["tramite"]["nombreanterior"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrenuevo"])) {
            $_SESSION["tramite"]["nombrenuevo"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrenuevo"])) {
            $_SESSION["tramite"]["nombrenuevo"] = '';
        }
        if (!isset($_SESSION["tramite"]["nuevonombre"])) {
            $_SESSION["tramite"]["nuevonombre"] = '';
        }

        // Datos del afiliado
        if (!isset($_SESSION["tramite"]["matriculaafiliado"])) {
            $_SESSION["tramite"]["matriculaafiliado"] = '';
        }
        if (!isset($_SESSION["tramite"]["opcionafiliado"])) {
            $_SESSION["tramite"]["opcionafiliado"] = '';
        }
        if (!isset($_SESSION["tramite"]["saldoafiliado"])) {
            $_SESSION["tramite"]["saldoafiliado"] = 0;
        }
        if (!isset($_SESSION["tramite"]["ultanorenafi"])) {
            $_SESSION["tramite"]["ultanorenafi"] = '';
        }

        // Variables de trámites rues
        if (!isset($_SESSION["tramite"]["rues_empleados"])) {
            $_SESSION["tramite"]["rues_empleados"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_numerointerno"])) {
            $_SESSION["tramite"]["rues_numerointerno"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_numerounico"])) {
            $_SESSION["tramite"]["rues_numerounico"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_camarareceptora"])) {
            $_SESSION["tramite"]["rues_camarareceptora"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_camararesponsable"])) {
            $_SESSION["tramite"]["rues_camararesponsable"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_matricula"])) {
            $_SESSION["tramite"]["rues_matricula"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_proponente"])) {
            $_SESSION["tramite"]["rues_proponente"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_nombreregistrado"])) {
            $_SESSION["tramite"]["rues_nombreregistrado"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_claseidentificacion"])) {
            $_SESSION["tramite"]["rues_claseidentificacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_numeroidentificacion"])) {
            $_SESSION["tramite"]["rues_numeroidentificacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_dv"])) {
            $_SESSION["tramite"]["rues_dv"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_estado_liquidacion"])) {
            $_SESSION["tramite"]["rues_estado_liquidacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_estado_transaccion"])) {
            $_SESSION["tramite"]["rues_estado_transaccion"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_nombrepagador"])) {
            $_SESSION["tramite"]["rues_nombrepagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_origendocumento"])) {
            $_SESSION["tramite"]["rues_origendocumento"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_fechadocumento"])) {
            $_SESSION["tramite"]["rues_fechadocumento"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_fechapago"])) {
            $_SESSION["tramite"]["rues_fechapago"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_numerofactura"])) {
            $_SESSION["tramite"]["rues_numerofactura"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_referenciaoperacion"])) {
            $_SESSION["tramite"]["rues_referenciaoperacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_totalpagado"])) {
            $_SESSION["tramite"]["rues_totalpagado"] = 0;
        }
        if (!isset($_SESSION["tramite"]["rues_formapago"])) {
            $_SESSION["tramite"]["rues_formapago"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_indicadororigen"])) {
            $_SESSION["tramite"]["rues_indicadororigen"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_indicadorbeneficio"])) {
            $_SESSION["tramite"]["rues_indicadorbeneficio"] = 0;
        }
        if (!isset($_SESSION["tramite"]["rues_fecharespuesta"])) {
            $_SESSION["tramite"]["rues_fecharespuesta"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_codigoservicioradicar"])) {
            $_SESSION["tramite"]["rues_codigoservicioradicar"] = '';
        }

        if (!isset($_SESSION["tramite"]["rues_horarespuesta"])) {
            $_SESSION["tramite"]["rues_horarespuesta"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_codigoerror"])) {
            $_SESSION["tramite"]["rues_codigoerror"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_mensajeerror"])) {
            $_SESSION["tramite"]["rues_mensajeerror"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_firmadigital"])) {
            $_SESSION["tramite"]["rues_firmadigital"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_caracteres_por_linea"])) {
            $_SESSION["tramite"]["rues_caracteres_por_linea"] = '';
        }
        if (!isset($_SESSION["tramite"]["rues_texto"])) {
            $_SESSION["tramite"]["rues_texto"] = array();
        }

        // Variables del firmante
        if (!isset($_SESSION["tramite"]["tipoidefirmante"])) {
            $_SESSION["tramite"]["tipoidefirmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionfirmante"])) {
            $_SESSION["tramite"]["identificacionfirmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["fechaexpfirmante"])) {
            $_SESSION["tramite"]["fechaexpfirmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido1firmante"])) {
            $_SESSION["tramite"]["apellido1firmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellido2firmante"])) {
            $_SESSION["tramite"]["apellido2firmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre1firmante"])) {
            $_SESSION["tramite"]["nombre1firmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombre2firmante"])) {
            $_SESSION["tramite"]["nombre2firmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailfirmante"])) {
            $_SESSION["tramite"]["emailfirmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailfirmanteseguimiento"])) {
            $_SESSION["tramite"]["emailfirmanteseguimiento"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularfirmante"])) {
            $_SESSION["tramite"]["celularfirmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionfirmante"])) {
            $_SESSION["tramite"]["direccionfirmante"] = '';
        }
        if (!isset($_SESSION["tramite"]["municipiofirmante"])) {
            $_SESSION["tramite"]["municipiofirmante"] = '';
        }

        if (!isset($_SESSION["tramite"]["firmadoelectronicamente"])) {
            $_SESSION["tramite"]["firmadoelectronicamente"] = '';
        }

        // Variables para el proceso de asesoria
        if (!isset($_SESSION["tramite"]["emailcontactoasesoria"])) {
            $_SESSION["tramite"]["emailcontactoasesoria"] = '';
        }
        if (!isset($_SESSION["tramite"]["comentariosasesoria"])) {
            $_SESSION["tramite"]["comentariosasesoria"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["pedirbalance"])) {
            $_SESSION["tramite"]["pedirbalance"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["quienasesora"])) {
            $_SESSION["tramite"]["quienasesora"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["incrementocupocertificados"])) {
            $_SESSION["tramite"]["incrementocupocertificados"] = 0;
        }

        //
        if (!isset($_SESSION["tramite"]["aceptadoprepago"])) {
            $_SESSION["tramite"]["aceptadoprepago"] = 'NO';
        }

        //
        if (!isset($_SESSION["tramite"]["propcamaraorigen"])) {
            $_SESSION["tramite"]["propcamaraorigen"] = '';
        }
        if (!isset($_SESSION["tramite"]["propidmunicipioorigen"])) {
            $_SESSION["tramite"]["propidmunicipioorigen"] = '';
        }
        if (!isset($_SESSION["tramite"]["propidmunicipiodestino"])) {
            $_SESSION["tramite"]["propidmunicipiodestino"] = '';
        }
        if (!isset($_SESSION["tramite"]["propproponenteorigen"])) {
            $_SESSION["tramite"]["propproponenteorigen"] = '';
        }
        if (!isset($_SESSION["tramite"]["propfechaultimainscripcion"])) {
            $_SESSION["tramite"]["propfechaultimainscripcion"] = '';
        }
        if (!isset($_SESSION["tramite"]["propfechaultimarenovacion"])) {
            $_SESSION["tramite"]["propfechaultimarenovacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["propdircom"])) {
            $_SESSION["tramite"]["propdircom"] = '';
        }
        if (!isset($_SESSION["tramite"]["propmuncom"])) {
            $_SESSION["tramite"]["propmuncom"] = '';
        }
        if (!isset($_SESSION["tramite"]["proptelcom1"])) {
            $_SESSION["tramite"]["proptelcom1"] = '';
        }
        if (!isset($_SESSION["tramite"]["proptelcom2"])) {
            $_SESSION["tramite"]["proptelcom2"] = '';
        }
        if (!isset($_SESSION["tramite"]["proptelcom3"])) {
            $_SESSION["tramite"]["proptelcom3"] = '';
        }
        if (!isset($_SESSION["tramite"]["propemailcom"])) {
            $_SESSION["tramite"]["propemailcom"] = '';
        }
        if (!isset($_SESSION["tramite"]["propdirnot"])) {
            $_SESSION["tramite"]["propdirnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["propmunnot"])) {
            $_SESSION["tramite"]["propmunnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["proptelnot1"])) {
            $_SESSION["tramite"]["proptelnot1"] = '';
        }
        if (!isset($_SESSION["tramite"]["proptelnot2"])) {
            $_SESSION["tramite"]["proptelnot2"] = '';
        }
        if (!isset($_SESSION["tramite"]["proptelnot3"])) {
            $_SESSION["tramite"]["proptelnot3"] = '';
        }
        if (!isset($_SESSION["tramite"]["propemailnot"])) {
            $_SESSION["tramite"]["propemailnot"] = '';
        }

        // 2017-11-26:JINT: Para indicar el email del usuario que está logueado
        if (!isset($_SESSION["tramite"]["emailcontrol"])) {
            if (isset($_SESSION["generales"]["emailusuariocontrol"])) {
                $_SESSION["tramite"]["emailcontrol"] = $_SESSION["generales"]["emailusuariocontrol"];
            } else {
                $_SESSION["tramite"]["emailcontrol"] = '';
            }
        }

        // 2017-11-26:JINT: control para el cumplimiento de los requisitos de la ley 1780
        if (!isset($_SESSION["tramite"]["cumplorequisitosbenley1780"])) {
            $_SESSION["tramite"]["cumplorequisitosbenley1780"] = '';
        }

        // 2017-11-26:JINT: control para el mantenimiento de los requisitos de la ley 1780
        if (!isset($_SESSION["tramite"]["mantengorequisitosbenley1780"])) {
            $_SESSION["tramite"]["mantengorequisitosbenley1780"] = '';
        }

        // 2017-12-23:JINT: Control de renuncia al beneficio ley 1780
        if (!isset($_SESSION["tramite"]["renunciobeneficiosley1780"])) {
            $_SESSION["tramite"]["renunciobeneficiosley1780"] = '';
        }

        // 2017-11-26:JINT: control de actividades de alto impacto
        if (!isset($_SESSION["tramite"]["controlactividadaltoimpacto"])) {
            $_SESSION["tramite"]["controlactividadaltoimpacto"] = '';
        }

        // 2017-12-16:JINT: control de multas
        if (!isset($_SESSION["tramite"]["multadoponal"])) {
            $_SESSION["tramite"]["multadoponal"] = '';
        }

        // 2016-04-08: JINT
        if (!isset($_SESSION["tramite"]["tramitepresencial"]) || $_SESSION["tramite"]["tramitepresencial"] == '') {
            if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
                $_SESSION["tramite"]["tramitepresencial"] = '1'; // Trámite virtual
            } else {
                $_SESSION["tramite"]["tramitepresencial"] = '4'; // Trámite presencial
            }
        }

        // 2016-08-23: JINT
        if (!isset($_SESSION["tramite"]["cobrarmutacion"])) {
            $_SESSION["tramite"]["cobrarmutacion"] = '';
        }

        // 2018-11-07:JINT: Prediligenciados
        if (!isset($_SESSION["tramite"]["tiporenovacion"])) {
            $_SESSION["tramite"]["tiporenovacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["activosbase"])) {
            $_SESSION["tramite"]["activosbase"] = '';
        }
        if (!isset($_SESSION["tramite"]["personalbase"])) {
            $_SESSION["tramite"]["personalbase"] = '';
        }

        if (!isset($_SESSION["tramite"]["enviara"])) {
            $_SESSION["tramite"]["enviara"] = '';
        }

        //    
        $arrCampos = array(
            'idliquidacion',
            'fecha',
            'hora',
            'fechaultimamodificacion',
            'idusuario',
            'sede',
            'tipotramite',
            'iptramite',
            'idestado',
            'idexpedientebase',
            'idmatriculabase',
            'idproponentebase',
            'tipoidentificacionbase',
            'identificacionbase',
            'nombrebase',
            'organizacionbase',
            'categoriabase',
            'tipoidepnat',
            'idepnat',
            'nombrepnat',
            'actpnat',
            'perpnat',
            'numeromatriculapnat',
            'camarapnat',
            'nombreest',
            'actest',
            'ultanoren',
            'domicilioorigen',
            'domiciliodestino',
            'idtipoidentificacioncliente',
            'identificacioncliente',
            'nombrecliente',
            'apellidocliente',
            'email',
            'direccion',
            'idmunicipio',
            'telefono',
            'movil',
            'nombrepagador',
            'apellidopagador',
            'tipoidentificacionpagador',
            'identificacionpagador',
            'direccionpagador',
            'telefonopagador',
            'movilpagador',
            'municipiopagador',
            'emailpagador',
            'valorbruto',
            'valorbaseiva',
            'valoriva',
            'valortotal',
            'idsolicitudpago',
            'pagoefectivo',
            'pagocheque',
            'pagoconsignacion',
            'pagovisa',
            'pagoach',
            'pagomastercard',
            'pagoamerican',
            'pagocredencial',
            'pagodiners',
            'pagotdebito',
            'pagoprepago',
            'pagoafiliado',
            'pagoacredito',
            'idformapago',
            'numerorecibo',
            'numerooperacion',
            'fecharecibo',
            'horarecibo',
            'idfranquicia',
            'nombrefranquicia',
            'numeroautorizacion',
            'idcodban',
            'nombrebanco',
            'numerocheque',
            'numerorecuperacion',
            'numeroradicacion',
            'alertaid',
            'alertaservicio',
            'alertavalor',
            'ctrcancelacion',
            'idasesor',
            'numeroempleados',
            'pagoafiliacion',
            'numerofactura',
            'incluirformularios',
            'incluircertificados',
            'incluirdiploma',
            'incluircartulina',
            'matricularpnat',
            'matricularest',
            'regimentributario',
            'tipomatricula',
            'camaracambidom',
            'matriculacambidom',
            'municipiocambidom',
            'fecmatcambidom',
            'benart7',
            'controlfirma',
            'proyectocaja',
            'cargoafiliacion',
            'cargogastoadministrativo',
            'cargoentidadoficial',
            'cargoconsulta',
            'actualizacionciiuversion4',
            'reliquidacion',
            'capital',
            'tipodoc',
            'numdoc',
            'fechadoc',
            'origendoc',
            'mundoc',
            'organizacion',
            'categoria',
            'tipoiderepleg',
            'iderepleg',
            'nombrerepleg',
            'tipoideradicador',
            'ideradicador',
            'fechaexpradicador',
            'nombreradicador',
            'emailradicador',
            'telefonoradicador',
            'celularradicador',
            'nrocontrolsipref',
            'tramitepresencial',
            'firmadoelectronicamente',
            'emailcontrol',
            'cumplorequisitosbenley1780',
            'mantengorequisitosbenley1780',
            'renunciobeneficiosley1780',
            'controlactividadaltoimpacto',
            'multadoponal'
        );

        $arrValues = array(
            $_SESSION["tramite"]["numeroliquidacion"],
            "'" . $_SESSION["tramite"]["fecha"] . "'",
            "'" . $_SESSION["tramite"]["hora"] . "'",
            "'" . date("Ymd") . "'",
            "'" . $_SESSION["tramite"]["idusuario"] . "'",
            "'" . $_SESSION["tramite"]["sede"] . "'",
            "'" . $_SESSION["tramite"]["tipotramite"] . "'",
            "'" . str_replace(",", "", $_SESSION["tramite"]["iptramite"]) . "'",
            "'" . $_SESSION["tramite"]["idestado"] . "'",
            "'" . ltrim($_SESSION["tramite"]["idexpedientebase"], '0') . "'",
            "'" . ltrim($_SESSION["tramite"]["idmatriculabase"], '0') . "'",
            "'" . ltrim($_SESSION["tramite"]["idproponentebase"], '0') . "'",
            "'" . ltrim($_SESSION["tramite"]["tipoidentificacionbase"], '0') . "'",
            "'" . ltrim($_SESSION["tramite"]["identificacionbase"], '0') . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["nombrebase"], 0, 250)) . "'",
            "'" . $_SESSION["tramite"]["organizacionbase"] . "'",
            "'" . $_SESSION["tramite"]["categoriabase"] . "'",
            "'" . $_SESSION["tramite"]["tipoidepnat"] . "'",
            "'" . $_SESSION["tramite"]["idepnat"] . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["nombrepnat"], 0, 128)) . "'",
            doubleval($_SESSION["tramite"]["actpnat"]),
            doubleval($_SESSION["tramite"]["perpnat"]),
            "'" . $_SESSION["tramite"]["numeromatriculapnat"] . "'",
            "'" . $_SESSION["tramite"]["camarapnat"] . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["nombreest"], 0, 128)) . "'",
            doubleval($_SESSION["tramite"]["actest"]),
            "'" . $_SESSION["tramite"]["ultanoren"] . "'",
            "'" . $_SESSION["tramite"]["domicilioorigen"] . "'",
            "'" . $_SESSION["tramite"]["domiciliodestino"] . "'",
            "'" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'",
            "'" . $_SESSION["tramite"]["identificacioncliente"] . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["nombrecliente"], 0, 60)) . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["apellidocliente"], 0, 128)) . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["email"], 0, 60)) . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["direccion"], 0, 128)) . "'",
            "'" . $_SESSION["tramite"]["idmunicipio"] . "'",
            "'" . $_SESSION["tramite"]["telefono"] . "'",
            "'" . $_SESSION["tramite"]["movil"] . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["nombrepagador"], 0, 50)) . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["apellidopagador"], 0, 50)) . "'",
            "'" . $_SESSION["tramite"]["tipoidentificacionpagador"] . "'",
            "'" . $_SESSION["tramite"]["identificacionpagador"] . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["direccionpagador"], 0, 128)) . "'",
            "'" . $_SESSION["tramite"]["telefonopagador"] . "'",
            "'" . $_SESSION["tramite"]["movilpagador"] . "'",
            "'" . $_SESSION["tramite"]["municipiopagador"] . "'",
            "'" . addslashes(substr($_SESSION["tramite"]["emailpagador"], 0, 70)) . "'",
            doubleval($_SESSION["tramite"]["valorbruto"]),
            doubleval($_SESSION["tramite"]["valorbaseiva"]),
            doubleval($_SESSION["tramite"]["valoriva"]),
            doubleval($_SESSION["tramite"]["valortotal"]),
            doubleval($_SESSION["tramite"]["idsolicitudpago"]),
            doubleval($_SESSION["tramite"]["pagoefectivo"]),
            doubleval($_SESSION["tramite"]["pagocheque"]),
            doubleval($_SESSION["tramite"]["pagoconsignacion"]),
            doubleval($_SESSION["tramite"]["pagovisa"]),
            doubleval($_SESSION["tramite"]["pagoach"]),
            doubleval($_SESSION["tramite"]["pagomastercard"]),
            doubleval($_SESSION["tramite"]["pagoamerican"]),
            doubleval($_SESSION["tramite"]["pagocredencial"]),
            doubleval($_SESSION["tramite"]["pagodiners"]),
            doubleval($_SESSION["tramite"]["pagotdebito"]),
            doubleval($_SESSION["tramite"]["pagoprepago"]),
            doubleval($_SESSION["tramite"]["pagoafiliado"]),
            doubleval($_SESSION["tramite"]["pagoacredito"]),
            "'" . $_SESSION["tramite"]["idformapago"] . "'",
            "'" . $_SESSION["tramite"]["numerorecibo"] . "'",
            "'" . $_SESSION["tramite"]["numerooperacion"] . "'",
            "'" . $_SESSION["tramite"]["fecharecibo"] . "'",
            "'" . $_SESSION["tramite"]["horarecibo"] . "'",
            "'" . $_SESSION["tramite"]["idfranquicia"] . "'",
            "'" . $_SESSION["tramite"]["nombrefranquicia"] . "'",
            "'" . $_SESSION["tramite"]["numeroautorizacion"] . "'",
            "'" . $_SESSION["tramite"]["idcodban"] . "'",
            "'" . $_SESSION["tramite"]["nombrebanco"] . "'",
            "'" . $_SESSION["tramite"]["numerocheque"] . "'",
            "'" . $_SESSION["tramite"]["numerorecuperacion"] . "'",
            "'" . ltrim($_SESSION["tramite"]["numeroradicacion"], '0') . "'",
            intval($_SESSION["tramite"]["alertaid"]),
            "'" . $_SESSION["tramite"]["alertaservicio"] . "'",
            doubleval($_SESSION["tramite"]["alertavalor"]),
            "'" . $_SESSION["tramite"]["ctrcancelacion"] . "'",
            "'" . $_SESSION["tramite"]["idasesor"] . "'",
            intval($_SESSION["tramite"]["numeroempleados"]),
            "'" . $_SESSION["tramite"]["pagoafiliacion"] . "'",
            "'" . $_SESSION["tramite"]["numerofactura"] . "'",
            "'" . $_SESSION["tramite"]["incluirformularios"] . "'",
            "'" . $_SESSION["tramite"]["incluircertificados"] . "'",
            "'" . $_SESSION["tramite"]["incluirdiploma"] . "'",
            "'" . $_SESSION["tramite"]["incluircartulina"] . "'",
            "'" . $_SESSION["tramite"]["matricularpnat"] . "'",
            "'" . $_SESSION["tramite"]["matricularest"] . "'",
            "'" . $_SESSION["tramite"]["regimentributario"] . "'",
            "'" . $_SESSION["tramite"]["tipomatricula"] . "'",
            "'" . $_SESSION["tramite"]["camaracambidom"] . "'",
            "'" . $_SESSION["tramite"]["matriculacambidom"] . "'",
            "'" . $_SESSION["tramite"]["municipiocambidom"] . "'",
            "'" . $_SESSION["tramite"]["fecmatcambidom"] . "'",
            "'" . $_SESSION["tramite"]["benart7"] . "'",
            "'" . $_SESSION["tramite"]["controlfirma"] . "'",
            "'" . $_SESSION["tramite"]["proyectocaja"] . "'",
            "'" . $_SESSION["tramite"]["cargoafiliacion"] . "'",
            "'" . $_SESSION["tramite"]["cargogastoadministrativo"] . "'",
            "'" . $_SESSION["tramite"]["cargoentidadoficial"] . "'",
            "'" . $_SESSION["tramite"]["cargoconsulta"] . "'",
            "'" . $_SESSION["tramite"]["actualizacionciiuversion4"] . "'",
            "'" . $_SESSION["tramite"]["reliquidacion"] . "'",
            doubleval($_SESSION["tramite"]["capital"]),
            // "'" . substr(trim($_SESSION["tramite"]["tipodoc"]),0,2) . "'",
            "''", // Tipo de documento
            "'" . $_SESSION["tramite"]["numdoc"] . "'",
            "'" . $_SESSION["tramite"]["fechadoc"] . "'",
            "'" . addslashes($_SESSION["tramite"]["origendoc"]) . "'",
            "'" . $_SESSION["tramite"]["mundoc"] . "'",
            "'" . $_SESSION["tramite"]["organizacion"] . "'",
            "'" . $_SESSION["tramite"]["categoria"] . "'",
            "'" . $_SESSION["tramite"]["tipoiderepleg"] . "'",
            "'" . $_SESSION["tramite"]["iderepleg"] . "'",
            "'" . addslashes($_SESSION["tramite"]["nombrerepleg"]) . "'",
            "'" . $_SESSION["tramite"]["tipoideradicador"] . "'",
            "'" . $_SESSION["tramite"]["ideradicador"] . "'",
            "'" . $_SESSION["tramite"]["fechaexpradicador"] . "'",
            "'" . addslashes($_SESSION["tramite"]["nombreradicador"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["emailradicador"]) . "'",
            "'" . $_SESSION["tramite"]["telefonoradicador"] . "'",
            "'" . $_SESSION["tramite"]["celularradicador"] . "'",
            "'" . addslashes($_SESSION["tramite"]["nrocontrolsipref"]) . "'",
            "'" . $_SESSION["tramite"]["tramitepresencial"] . "'",
            "'" . $_SESSION["tramite"]["firmadoelectronicamente"] . "'",
            "'" . addslashes($_SESSION["tramite"]["emailcontrol"]) . "'",
            "'" . $_SESSION["tramite"]["cumplorequisitosbenley1780"] . "'",
            "'" . $_SESSION["tramite"]["mantengorequisitosbenley1780"] . "'",
            "'" . $_SESSION["tramite"]["renunciobeneficiosley1780"] . "'",
            "'" . $_SESSION["tramite"]["controlactividadaltoimpacto"] . "'",
            "'" . $_SESSION["tramite"]["multadoponal"] . "'"
        );

        //
        if ($conteo == 0) {
            $result = insertarRegistrosmyqli2($dbx, 'mreg_liquidacion', $arrCampos, $arrValues);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error insertando liquidacion en mreg_liquidacion (' . $_SESSION["generales"]["mensajeerror"] . ')';
                return false;
            }
        }

        //
        if ($conteo > 0) {
            $condicion = 'idliquidacion=' . $_SESSION["tramite"]["numeroliquidacion"];
            $result = regrabarRegistrosMysqli2($dbx, 'mreg_liquidacion', $arrCampos, $arrValues, $condicion);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error actualizando liquidacion en mreg_liquidacion (' . $_SESSION["generales"]["mensajeerror"] . ')';
                return false;
            }
        }


        // Campos adicionales de la liquidación
        borrarRegistrosMysqli2($dbx, 'mreg_liquidacion_campos', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"]);

        //

        $res = array();
        $arrCampos = array(
            'idliquidacion',
            'campo',
            'contenido'
        );
        $ix = 0;
        $arrValores = array();

        // WSIERRA 2018-03-05 grabación del campo de control de matriculas a renovar   
        if (isset($_SESSION["tramite"]["procesartodas"]) && trim($_SESSION["tramite"]["procesartodas"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'procesartodas'", "'" . $_SESSION["tramite"]["procesartodas"] . "'");
        }

        if (trim($_SESSION["tramite"]["origen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'origen'", "'" . $_SESSION["tramite"]["origen"] . "'");
        }

        if (trim($_SESSION["tramite"]["subtipotramite"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'subtipotramite'", "'" . $_SESSION["tramite"]["subtipotramite"] . "'");
        }

        if (trim($_SESSION["tramite"]["tipoproponente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoproponente'", "'" . $_SESSION["tramite"]["tipoproponente"] . "'");
        }

        if (trim($_SESSION["tramite"]["matriculabase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'matriculabase'", "'" . $_SESSION["tramite"]["matriculabase"] . "'");
        }

        if (trim($_SESSION["tramite"]["nom1base"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nom1base'", "'" . $_SESSION["tramite"]["nom1base"] . "'");
        }

        if (trim($_SESSION["tramite"]["nom2base"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nom2base'", "'" . $_SESSION["tramite"]["nom2base"] . "'");
        }

        if (trim($_SESSION["tramite"]["ape1base"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ape1base'", "'" . $_SESSION["tramite"]["ape1base"] . "'");
        }

        if (trim($_SESSION["tramite"]["ape2base"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ape1base'", "'" . $_SESSION["tramite"]["ape2base"] . "'");
        }

        if (trim($_SESSION["tramite"]["tipoidentificacionbase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoidentificacionbase'", "'" . $_SESSION["tramite"]["tipoidentificacionbase"] . "'");
        }

        if (trim($_SESSION["tramite"]["identificacionbase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'identificacionbase'", "'" . $_SESSION["tramite"]["identificacionbase"] . "'");
        }

        if (trim($_SESSION["tramite"]["organizacionbase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'organizacionbase'", "'" . $_SESSION["tramite"]["organizacionbase"] . "'");
        }

        if (trim($_SESSION["tramite"]["categoriabase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'categoriabase'", "'" . $_SESSION["tramite"]["categoriabase"] . "'");
        }

        if (trim($_SESSION["tramite"]["afiliadobase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'afiliadobase'", "'" . $_SESSION["tramite"]["afiliadobase"] . "'");
        }


        if (trim($_SESSION["tramite"]["tipocliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipocliente'", "'" . $_SESSION["tramite"]["tipocliente"] . "'");
        }

        if (trim($_SESSION["tramite"]["razonsocialcliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'razonsocialcliente'", "'" . $_SESSION["tramite"]["razonsocialcliente"] . "'");
        }

        if (trim($_SESSION["tramite"]["apellido1cliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1cliente'", "'" . $_SESSION["tramite"]["apellido1cliente"] . "'");
        }

        if (trim($_SESSION["tramite"]["apellido2cliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2cliente'", "'" . $_SESSION["tramite"]["apellido2cliente"] . "'");
        }

        if (trim($_SESSION["tramite"]["nombre1cliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1cliente'", "'" . $_SESSION["tramite"]["nombre1cliente"] . "'");
        }

        if (trim($_SESSION["tramite"]["nombre2cliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2cliente'", "'" . $_SESSION["tramite"]["nombre2cliente"] . "'");
        }



        //
        if (trim($_SESSION["tramite"]["tipopagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipopagador'", "'" . $_SESSION["tramite"]["tipopagador"] . "'");
        }
        if (trim($_SESSION["tramite"]["razonsocialpagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'razonsocialpagador'", "'" . $_SESSION["tramite"]["razonsocialpagador"] . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1pagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1pagador'", "'" . $_SESSION["tramite"]["apellido1pagador"] . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2pagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2pagador'", "'" . $_SESSION["tramite"]["apellido2pagador"] . "'");
        }
        if (trim($_SESSION["tramite"]["nombre1pagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1pagador'", "'" . $_SESSION["tramite"]["nombre1pagador"] . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2pagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2pagador'", "'" . $_SESSION["tramite"]["nombre2pagador"] . "'");
        }

        //
        if (trim($_SESSION["tramite"]["modcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'modcom'", "'" . $_SESSION["tramite"]["modcom"] . "'");
        }
        if (trim($_SESSION["tramite"]["modnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'modnot'", "'" . $_SESSION["tramite"]["modnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["modciiu"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'modciiu'", "'" . $_SESSION["tramite"]["modciiu"] . "'");
        }
        if (trim($_SESSION["tramite"]["modnombre"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'modnombre'", "'" . $_SESSION["tramite"]["modnombre"] . "'");
        }
        if (trim($_SESSION["tramite"]["fecrencambidom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'fecrencambidom'", "'" . $_SESSION["tramite"]["fecrencambidom"] . "'");
        }

        if (trim($_SESSION["tramite"]["nombre1repleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1repleg'", "'" . addslashes($_SESSION["tramite"]["nombre1repleg"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2repleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2repleg'", "'" . addslashes($_SESSION["tramite"]["nombre2repleg"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1repleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1repleg'", "'" . addslashes($_SESSION["tramite"]["apellido1repleg"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2repleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2repleg'", "'" . addslashes($_SESSION["tramite"]["apellido2repleg"]) . "'");
        }
        if (trim($_SESSION["tramite"]["cargorepleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'cargorepleg'", "'" . addslashes($_SESSION["tramite"]["cargorepleg"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailrepleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailrepleg'", "'" . addslashes($_SESSION["tramite"]["emailrepleg"]) . "'");
        }
        if (trim($_SESSION["tramite"]["firmorepleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'firmorepleg'", "'" . $_SESSION["tramite"]["firmorepleg"] . "'");
        }
        if (trim($_SESSION["tramite"]["celularrepleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularrepleg'", "'" . $_SESSION["tramite"]["celularrepleg"] . "'");
        }

        if (trim($_SESSION["tramite"]["tipolibro"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipolibro'", "'" . $_SESSION["tramite"]["tipolibro"] . "'");
        }
        if (trim($_SESSION["tramite"]["codigolibro"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'codigolibro'", "'" . $_SESSION["tramite"]["codigolibro"] . "'");
        }
        if (trim($_SESSION["tramite"]["primeravez"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'primeravez'", "'" . $_SESSION["tramite"]["primeravez"] . "'");
        }
        if (trim($_SESSION["tramite"]["confirmadigital"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'confirmadigital'", "'" . $_SESSION["tramite"]["confirmadigital"] . "'");
        }

        if (trim($_SESSION["tramite"]["iderevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'iderevfis'", "'" . $_SESSION["tramite"]["iderevfis"] . "'");
        }
        if (trim($_SESSION["tramite"]["nombre1revfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1revfis'", "'" . addslashes($_SESSION["tramite"]["nombre1revfis"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2revfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2revfis'", "'" . addslashes($_SESSION["tramite"]["nombre2revfis"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1revfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1revfis'", "'" . addslashes($_SESSION["tramite"]["apellido1revfis"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2revfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2revfis'", "'" . addslashes($_SESSION["tramite"]["apellido2revfis"]) . "'");
        }
        if (trim($_SESSION["tramite"]["cargorevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'cargorevfis'", "'" . addslashes($_SESSION["tramite"]["cargorevfis"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailrevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailrevfis'", "'" . addslashes($_SESSION["tramite"]["emailrevfis"]) . "'");
        }
        if (trim($_SESSION["tramite"]["firmorevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'firmorevfis'", "'" . $_SESSION["tramite"]["firmorevfis"] . "'");
        }
        if (trim($_SESSION["tramite"]["celularrevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularrevfis'", "'" . $_SESSION["tramite"]["celularrevfis"] . "'");
        }

        if (trim($_SESSION["tramite"]["idepreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'idepreasa'", "'" . $_SESSION["tramite"]["idepreasa"] . "'");
        }
        if (trim($_SESSION["tramite"]["nombre1preasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1preasa'", "'" . addslashes($_SESSION["tramite"]["nombre1preasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2preasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2preasa'", "'" . addslashes($_SESSION["tramite"]["nombre2preasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1preasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1preasa'", "'" . addslashes($_SESSION["tramite"]["apellido1preasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2preasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2preasa'", "'" . addslashes($_SESSION["tramite"]["apellido2preasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["cargopreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'cargopreasa'", "'" . addslashes($_SESSION["tramite"]["cargopreasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailpreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailpreasa'", "'" . addslashes($_SESSION["tramite"]["emailpreasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["firmopreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'firmopreasa'", "'" . addslashes($_SESSION["tramite"]["firmopreasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["celularpreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularpreasa'", "'" . $_SESSION["tramite"]["celularpreasa"] . "'");
        }

        if (trim($_SESSION["tramite"]["idesecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'idesecasa'", "'" . addslashes($_SESSION["tramite"]["idesecasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre1secasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1secasa'", "'" . addslashes($_SESSION["tramite"]["nombre1secasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2secasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2secasa'", "'" . addslashes($_SESSION["tramite"]["nombre2secasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1secasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1secasa'", "'" . addslashes($_SESSION["tramite"]["apellido1secasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2secasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2secasa'", "'" . addslashes($_SESSION["tramite"]["apellido2secasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["cargosecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'cargosecasa'", "'" . addslashes($_SESSION["tramite"]["cargosecasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailsecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailsecasa'", "'" . addslashes($_SESSION["tramite"]["emailsecasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["firmosecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'firmosecasa'", "'" . addslashes($_SESSION["tramite"]["firmosecasa"]) . "'");
        }
        if (trim($_SESSION["tramite"]["celularsecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularsecasa'", "'" . $_SESSION["tramite"]["celularsecasa"] . "'");
        }

        if (trim($_SESSION["tramite"]["tipoidentificacionaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoidentificacionaceptante'", "'" . addslashes($_SESSION["tramite"]["tipoidentificacionaceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["identificacionaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'identificacionaceptante'", "'" . addslashes($_SESSION["tramite"]["identificacionaceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre1aceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1aceptante'", "'" . addslashes($_SESSION["tramite"]["nombre1aceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2aceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2aceptante'", "'" . addslashes($_SESSION["tramite"]["nombre2aceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1aceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1aceptante'", "'" . addslashes($_SESSION["tramite"]["apellido1aceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2aceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2aceptante'", "'" . addslashes($_SESSION["tramite"]["apellido2aceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["direccionaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'direccionaceptante'", "'" . addslashes($_SESSION["tramite"]["direccionaceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailaceptante'", "'" . addslashes($_SESSION["tramite"]["emailaceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["municipioaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'municipioaceptante'", "'" . addslashes($_SESSION["tramite"]["municipioaceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["telefonoaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'telefonoaceptante'", "'" . addslashes($_SESSION["tramite"]["telefonoaceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["celularaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularaceptante'", "'" . addslashes($_SESSION["tramite"]["celularaceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["cargoaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'cargoaceptante'", "'" . addslashes($_SESSION["tramite"]["cargoaceptante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["fechadocideaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'fechadocideaceptante'", "'" . addslashes($_SESSION["tramite"]["fechadocideaceptante"]) . "'");
        }

        if (trim($_SESSION["tramite"]["motivocorreccion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'motivocorreccion'", "'" . addslashes($_SESSION["tramite"]["motivocorreccion"]) . "'");
        }
        if (trim($_SESSION["tramite"]["tipoerror1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoerror1'", "'" . addslashes($_SESSION["tramite"]["tipoerror1"]) . "'");
        }
        if (trim($_SESSION["tramite"]["tipoerror2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoerror2'", "'" . addslashes($_SESSION["tramite"]["tipoerror2"]) . "'");
        }
        if (trim($_SESSION["tramite"]["tipoerror3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoerror3'", "'" . addslashes($_SESSION["tramite"]["tipoerror3"]) . "'");
        }
        if (trim($_SESSION["tramite"]["tipoidentificacioncor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoidentificacioncor'", "'" . addslashes($_SESSION["tramite"]["tipoidentificacioncor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["identificacioncor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'identificacioncor'", "'" . addslashes($_SESSION["tramite"]["identificacioncor"]) . "'");
        }

        if (trim($_SESSION["tramite"]["nombre1cor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1cor'", "'" . addslashes($_SESSION["tramite"]["nombre1cor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2cor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2cor'", "'" . addslashes($_SESSION["tramite"]["nombre2cor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1cor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1cor'", "'" . addslashes($_SESSION["tramite"]["apellido1cor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2cor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2cor'", "'" . addslashes($_SESSION["tramite"]["apellido2cor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["direccioncor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'direccioncor'", "'" . addslashes($_SESSION["tramite"]["direccioncor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["municipiocor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'municipiocor'", "'" . addslashes($_SESSION["tramite"]["municipiocor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailcor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailcor'", "'" . addslashes($_SESSION["tramite"]["emailcor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["telefonocor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'telefonocor'", "'" . addslashes($_SESSION["tramite"]["telefonocor"]) . "'");
        }
        if (trim($_SESSION["tramite"]["celularcor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularcor'", "'" . addslashes($_SESSION["tramite"]["celularcor"]) . "'");
        }


        if (trim($_SESSION["tramite"]["descripcionembargo"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'descripcionembargo'", "'" . addslashes($_SESSION["tramite"]["descripcionembargo"]) . "'");
        }
        if (trim($_SESSION["tramite"]["descripciondesembargo"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'descripciondesembargo'", "'" . addslashes($_SESSION["tramite"]["descripciondesembargo"]) . "'");
        }
        if (trim($_SESSION["tramite"]["tipoidentificaciondemandante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoidentificaciondemandante'", "'" . addslashes($_SESSION["tramite"]["tipoidentificaciondemandante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["identificaciondemandante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'identificaciondemandante'", "'" . addslashes($_SESSION["tramite"]["identificaciondemandante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombredemandante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombredemandante'", "'" . addslashes($_SESSION["tramite"]["nombredemandante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["libro"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'libro'", "'" . addslashes($_SESSION["tramite"]["libro"]) . "'");
        }
        if (trim($_SESSION["tramite"]["numreg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'numreg'", "'" . addslashes($_SESSION["tramite"]["numreg"]) . "'");
        }

        if (trim($_SESSION["tramite"]["descripcionpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'descripcionpqr'", "'" . addslashes($_SESSION["tramite"]["descripcionpqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["tipoidentificacionpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoidentificacionpqr'", "'" . addslashes($_SESSION["tramite"]["tipoidentificacionpqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["identificacionpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'identificacionpqr'", "'" . addslashes($_SESSION["tramite"]["identificacionpqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre1pqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1pqr'", "'" . addslashes($_SESSION["tramite"]["nombre1pqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2pqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2pqr'", "'" . addslashes($_SESSION["tramite"]["nombre2pqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1pqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1pqr'", "'" . addslashes($_SESSION["tramite"]["apellido1pqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2pqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2pqr'", "'" . addslashes($_SESSION["tramite"]["apellido2pqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["direccionpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'direccionpqr'", "'" . addslashes($_SESSION["tramite"]["direccionpqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["municipiopqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'municipiopqr'", "'" . addslashes($_SESSION["tramite"]["municipiopqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailpqr'", "'" . addslashes($_SESSION["tramite"]["emailpqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["telefonopqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'telefonopqr'", "'" . addslashes($_SESSION["tramite"]["telefonopqr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["celularpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularpqr'", "'" . addslashes($_SESSION["tramite"]["celularpqr"]) . "'");
        }

        if (trim($_SESSION["tramite"]["descripcionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'descripcionrr'", "'" . addslashes($_SESSION["tramite"]["descripcionrr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["tipoidentificacionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoidentificacionrr'", "'" . addslashes($_SESSION["tramite"]["tipoidentificacionrr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["identificacionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'identificacionrr'", "'" . addslashes($_SESSION["tramite"]["identificacionrr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre1rr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1rr'", "'" . addslashes($_SESSION["tramite"]["nombre1rr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2rr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2rr'", "'" . addslashes($_SESSION["tramite"]["nombre2rr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1rr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1rr'", "'" . addslashes($_SESSION["tramite"]["apellido1rr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2rr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2rr'", "'" . addslashes($_SESSION["tramite"]["apellido2rr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["direccionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'direccionrr'", "'" . addslashes($_SESSION["tramite"]["direccionrr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["municipiorr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'municipiorr'", "'" . addslashes($_SESSION["tramite"]["municipiorr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailrr'", "'" . addslashes($_SESSION["tramite"]["emailrr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["telefonorr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'telefonorr'", "'" . addslashes($_SESSION["tramite"]["telefonorr"]) . "'");
        }
        if (trim($_SESSION["tramite"]["celularrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularrr'", "'" . addslashes($_SESSION["tramite"]["celularrr"]) . "'");
        }


        if (trim($_SESSION["tramite"]["tipocertificado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipocertificado'", "'" . addslashes($_SESSION["tramite"]["tipocertificado"]) . "'");
        }
        if (trim($_SESSION["tramite"]["explicacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'explicacion'", "'" . addslashes($_SESSION["tramite"]["explicacion"]) . "'");
        }

        if (trim($_SESSION["tramite"]["textolibre"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'textolibre'", "'" . addslashes($_SESSION["tramite"]["textolibre"]) . "'");
        }

        if (trim($_SESSION["tramite"]["matriculaafiliado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'matriculaafiliado'", "'" . addslashes($_SESSION["tramite"]["matriculaafiliado"]) . "'");
        }
        if (trim($_SESSION["tramite"]["opcionafiliado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'opcionafiliado'", "'" . addslashes($_SESSION["tramite"]["opcionafiliado"]) . "'");
        }
        if (trim($_SESSION["tramite"]["saldoafiliado"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'saldoafiliado'", "'" . addslashes($_SESSION["tramite"]["saldoafiliado"]) . "'");
        }
        if (trim($_SESSION["tramite"]["ultanorenafi"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ultanorenafi'", "'" . addslashes($_SESSION["tramite"]["ultanorenafi"]) . "'");
        }

        //
        if (trim($_SESSION["tramite"]["nombrepjur"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombrepjur'", "'" . addslashes($_SESSION["tramite"]["nombrepjur"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombresuc"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombresuc'", "'" . addslashes($_SESSION["tramite"]["nombresuc"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombreage"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombreage'", "'" . addslashes($_SESSION["tramite"]["nombreage"]) . "'");
        }

        if (trim($_SESSION["tramite"]["orgpnat"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'orgpnat'", "'" . addslashes($_SESSION["tramite"]["orgpnat"]) . "'");
        }
        if (trim($_SESSION["tramite"]["orgpjur"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'orgpjur'", "'" . addslashes($_SESSION["tramite"]["orgpjur"]) . "'");
        }
        if (trim($_SESSION["tramite"]["orgsuc"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'orgsuc'", "'" . addslashes($_SESSION["tramite"]["orgsuc"]) . "'");
        }
        if (trim($_SESSION["tramite"]["orgage"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'orgage'", "'" . addslashes($_SESSION["tramite"]["orgage"]) . "'");
        }

        if (trim($_SESSION["tramite"]["munpnat"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'munpnat'", "'" . addslashes($_SESSION["tramite"]["munpnat"]) . "'");
        }
        if (trim($_SESSION["tramite"]["munest"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'munest'", "'" . addslashes($_SESSION["tramite"]["munest"]) . "'");
        }
        if (trim($_SESSION["tramite"]["munpjur"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'munpjur'", "'" . addslashes($_SESSION["tramite"]["munpjur"]) . "'");
        }
        if (trim($_SESSION["tramite"]["munsuc"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'munsuc'", "'" . addslashes($_SESSION["tramite"]["munsuc"]) . "'");
        }
        if (trim($_SESSION["tramite"]["munage"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'munage'", "'" . addslashes($_SESSION["tramite"]["munage"]) . "'");
        }

        if (trim($_SESSION["tramite"]["perpjur"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'perpjur'", "'" . addslashes($_SESSION["tramite"]["perpjur"]) . "'");
        }

        if (trim($_SESSION["tramite"]["actpjur"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'actpjur'", "'" . addslashes($_SESSION["tramite"]["actpjur"]) . "'");
        }
        if (trim($_SESSION["tramite"]["actsuc"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'actsuc", "'" . addslashes($_SESSION["tramite"]["actsuc"]) . "'");
        }
        if (trim($_SESSION["tramite"]["actage"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'actage'", "'" . addslashes($_SESSION["tramite"]["actage"]) . "'");
        }

        // En caso de mutaciones - direccion comercial
        if (trim($_SESSION["tramite"]["ant_dircom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_dircom'", "'" . $_SESSION["tramite"]["ant_dircom"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_telcom1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_telcom1'", "'" . $_SESSION["tramite"]["ant_telcom1"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_telcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_telcom2'", "'" . $_SESSION["tramite"]["ant_telcom2"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_faxcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_faxcom'", "'" . $_SESSION["tramite"]["ant_faxcom"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_celcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_celcom'", "'" . $_SESSION["tramite"]["ant_celcom"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_numpredial"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_numpredial'", "'" . $_SESSION["tramite"]["ant_numpredial"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_barriocom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_barriocom'", "'" . $_SESSION["tramite"]["ant_barriocom"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_muncom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_muncom'", "'" . $_SESSION["tramite"]["ant_muncom"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_emailcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_emailcom'", "'" . $_SESSION["tramite"]["ant_emailcom"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_emailcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_emailcom2'", "'" . $_SESSION["tramite"]["ant_emailcom2"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_emailcom3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_emailcom3'", "'" . $_SESSION["tramite"]["ant_emailcom3"] . "'");
        }

        if (trim($_SESSION["tramite"]["dircom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'dircom'", "'" . $_SESSION["tramite"]["dircom"] . "'");
        }
        if (trim($_SESSION["tramite"]["telcom1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'telcom1'", "'" . $_SESSION["tramite"]["telcom1"] . "'");
        }
        if (trim($_SESSION["tramite"]["telcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'telcom2'", "'" . $_SESSION["tramite"]["telcom2"] . "'");
        }
        if (trim($_SESSION["tramite"]["faxcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'faxcom'", "'" . $_SESSION["tramite"]["faxcom"] . "'");
        }
        if (trim($_SESSION["tramite"]["celcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celcom'", "'" . $_SESSION["tramite"]["celcom"] . "'");
        }
        if (trim($_SESSION["tramite"]["barriocom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'barriocom'", "'" . $_SESSION["tramite"]["barriocom"] . "'");
        }
        if (trim($_SESSION["tramite"]["muncom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'muncom'", "'" . $_SESSION["tramite"]["muncom"] . "'");
        }
        if (trim($_SESSION["tramite"]["numpredial"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'numpredial'", "'" . $_SESSION["tramite"]["numpredial"] . "'");
        }
        if (trim($_SESSION["tramite"]["emailcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailcom'", "'" . $_SESSION["tramite"]["emailcom"] . "'");
        }
        if (trim($_SESSION["tramite"]["emailcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailcom2'", "'" . $_SESSION["tramite"]["emailcom2"] . "'");
        }
        if (trim($_SESSION["tramite"]["emailcom3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailcom3'", "'" . $_SESSION["tramite"]["emailcom3"] . "'");
        }

        // En caso de mutaciones - direccion notificacion
        if (trim($_SESSION["tramite"]["ant_dirnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_dirnot'", "'" . $_SESSION["tramite"]["ant_dirnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_telnot1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_telnot1'", "'" . $_SESSION["tramite"]["ant_telnot1"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_telnot2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_telnot2'", "'" . $_SESSION["tramite"]["ant_telnot2"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_faxnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_faxnot'", "'" . $_SESSION["tramite"]["ant_faxnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_celnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_celnot'", "'" . $_SESSION["tramite"]["ant_celnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_barrionot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_barrionot'", "'" . $_SESSION["tramite"]["ant_barrionot"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_munnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_munnot'", "'" . $_SESSION["tramite"]["ant_munnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_emailnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_emailnot'", "'" . $_SESSION["tramite"]["ant_emailnot"] . "'");
        }

        if (trim($_SESSION["tramite"]["dirnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'dirnot'", "'" . $_SESSION["tramite"]["dirnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["telnot1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'telnot1'", "'" . $_SESSION["tramite"]["telnot1"] . "'");
        }
        if (trim($_SESSION["tramite"]["telnot2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'telnot2'", "'" . $_SESSION["tramite"]["telnot2"] . "'");
        }
        if (trim($_SESSION["tramite"]["faxnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'faxnot'", "'" . $_SESSION["tramite"]["faxnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["celnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celnot'", "'" . $_SESSION["tramite"]["celnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["barrionot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'barrionot'", "'" . $_SESSION["tramite"]["barrionot"] . "'");
        }
        if (trim($_SESSION["tramite"]["munnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'munnot'", "'" . $_SESSION["tramite"]["munnot"] . "'");
        }
        if (trim($_SESSION["tramite"]["emailnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailnot'", "'" . $_SESSION["tramite"]["emailnot"] . "'");
        }

        // En caso de mutaciones - actividad
        if (trim($_SESSION["tramite"]["ant_versionciiu"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_versionciiu'", "'" . $_SESSION["tramite"]["ant_versionciiu"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_ciiu11"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_ciiu11'", "'" . $_SESSION["tramite"]["ant_ciiu11"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_ciiu12"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_ciiu12'", "'" . $_SESSION["tramite"]["ant_ciiu12"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_ciiu13"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_ciiu13'", "'" . $_SESSION["tramite"]["ant_ciiu13"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_ciiu14"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_ciiu14'", "'" . $_SESSION["tramite"]["ant_ciiu14"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_ciiu21"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_ciiu21'", "'" . $_SESSION["tramite"]["ant_ciiu21"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_ciiu22"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_ciiu22'", "'" . $_SESSION["tramite"]["ant_ciiu22"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_ciiu23"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_ciiu23'", "'" . $_SESSION["tramite"]["ant_ciiu23"] . "'");
        }
        if (trim($_SESSION["tramite"]["ant_ciiu24"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ant_ciiu24'", "'" . $_SESSION["tramite"]["ant_ciiu24"] . "'");
        }

        if (trim($_SESSION["tramite"]["nombreanterior"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombreanterior'", "'" . $_SESSION["tramite"]["nombreanterior"] . "'");
        }
        if (trim($_SESSION["tramite"]["nombrenuevo"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombrenuevo'", "'" . $_SESSION["tramite"]["nombrenuevo"] . "'");
        }
        if (trim($_SESSION["tramite"]["nuevonombre"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nuevonombre'", "'" . $_SESSION["tramite"]["nuevonombre"] . "'");
        }

        if (trim($_SESSION["tramite"]["versionciiu"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'versionciiu'", "'" . $_SESSION["tramite"]["versionciiu"] . "'");
        }
        if (trim($_SESSION["tramite"]["ciiu11"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ciiu11'", "'" . $_SESSION["tramite"]["ciiu11"] . "'");
        }
        if (trim($_SESSION["tramite"]["ciiu12"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ciiu12'", "'" . $_SESSION["tramite"]["ciiu12"] . "'");
        }
        if (trim($_SESSION["tramite"]["ciiu13"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ciiu13'", "'" . $_SESSION["tramite"]["ciiu13"] . "'");
        }
        if (trim($_SESSION["tramite"]["ciiu14"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ciiu14'", "'" . $_SESSION["tramite"]["ciiu14"] . "'");
        }
        if (trim($_SESSION["tramite"]["ciiu21"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ciiu21'", "'" . $_SESSION["tramite"]["ciiu21"] . "'");
        }
        if (trim($_SESSION["tramite"]["ciiu22"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ciiu22'", "'" . $_SESSION["tramite"]["ciiu22"] . "'");
        }
        if (trim($_SESSION["tramite"]["ciiu23"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ciiu23'", "'" . $_SESSION["tramite"]["ciiu23"] . "'");
        }
        if (trim($_SESSION["tramite"]["ciiu24"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'ciiu24'", "'" . $_SESSION["tramite"]["ciiu24"] . "'");
        }

        if (trim($_SESSION["tramite"]["nombreanterior"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombreanterior'", "'" . $_SESSION["tramite"]["nombreanterior"] . "'");
        }
        if (trim($_SESSION["tramite"]["nombrenuevo"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombrenuevo'", "'" . $_SESSION["tramite"]["nombrenuevo"] . "'");
        }

        // En caso de tr&aacute;mites rues
        if (trim($_SESSION["tramite"]["rues_numerointerno"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_numerointerno'", "'" . addslashes($_SESSION["tramite"]["rues_numerointerno"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_numerounico"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_numerounico'", "'" . addslashes($_SESSION["tramite"]["rues_numerounico"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_camarareceptora"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_camarareceptora'", "'" . addslashes($_SESSION["tramite"]["rues_camarareceptora"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_camararesponsable"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_camararesponsable'", "'" . addslashes($_SESSION["tramite"]["rues_camararesponsable"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_codigoservicioradicar"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_codigoservicioradicar'", "'" . addslashes($_SESSION["tramite"]["rues_codigoservicioradicar"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_matricula"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_matricula'", "'" . addslashes($_SESSION["tramite"]["rues_matricula"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_proponente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_proponente'", "'" . addslashes($_SESSION["tramite"]["rues_proponente"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_nombreregistrado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_nombreregistrado'", "'" . addslashes($_SESSION["tramite"]["rues_nombreregistrado"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_claseidentificacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_claseidentificacion'", "'" . $_SESSION["tramite"]["rues_claseidentificacion"] . "'");
        }
        if (trim($_SESSION["tramite"]["rues_numeroidentificacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_numeroidentificacion'", "'" . $_SESSION["tramite"]["rues_numeroidentificacion"] . "'");
        }
        if (trim($_SESSION["tramite"]["rues_dv"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_dv'", "'" . $_SESSION["tramite"]["rues_dv"] . "'");
        }
        if (trim($_SESSION["tramite"]["rues_estado_liquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_estado_liquidacion'", "'" . $_SESSION["tramite"]["rues_estado_liquidacion"] . "'");
        }
        if (trim($_SESSION["tramite"]["rues_estado_transaccion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_estado_transaccion'", "'" . $_SESSION["tramite"]["rues_estado_transaccion"] . "'");
        }
        if (trim($_SESSION["tramite"]["rues_nombrepagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_nombrepagador'", "'" . addslashes($_SESSION["tramite"]["rues_nombrepagador"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_origendocumento"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_origendocumento'", "'" . addslashes($_SESSION["tramite"]["rues_origendocumento"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_fechadocumento"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_fechadocumento'", "'" . addslashes($_SESSION["tramite"]["rues_fechadocumento"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_fechapago"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_fechapago'", "'" . addslashes($_SESSION["tramite"]["rues_fechapago"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_numerofactura"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_numerofactura'", "'" . addslashes($_SESSION["tramite"]["rues_numerofactura"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_referenciaoperacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_referenciaoperacion'", "'" . addslashes($_SESSION["tramite"]["rues_referenciaoperacion"]) . "'");
        }
        if (doubleval($_SESSION["tramite"]["rues_totalpagado"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_totalpagado'", "'" . addslashes($_SESSION["tramite"]["rues_totalpagado"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_formapago"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_formapago'", "'" . addslashes($_SESSION["tramite"]["rues_formapago"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_indicadororigen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_indicadororigen'", "'" . addslashes($_SESSION["tramite"]["rues_indicadororigen"]) . "'");
        }
        if (doubleval($_SESSION["tramite"]["rues_empleados"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_empleados'", "'" . addslashes($_SESSION["tramite"]["rues_empleados"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_indicadorbeneficio"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_indicadorbeneficio'", "'" . addslashes($_SESSION["tramite"]["rues_indicadorbeneficio"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_fecharespuesta"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_fecharespuesta'", "'" . addslashes($_SESSION["tramite"]["rues_fecharespuesta"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_horarespuesta"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_horarespuesta'", "'" . addslashes($_SESSION["tramite"]["rues_horarespuesta"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_codigoerror"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_codigoerror'", "'" . addslashes($_SESSION["tramite"]["rues_codigoerror"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_mensajeerror"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_mensajeerror'", "'" . addslashes($_SESSION["tramite"]["rues_mensajeerror"]) . "'");
        }
        if (trim($_SESSION["tramite"]["rues_firmadigital"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_firmadigital'", "'" . addslashes($_SESSION["tramite"]["rues_firmadigital"]) . "'");
        }
        if (doubleval($_SESSION["tramite"]["rues_caracteres_por_linea"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'rues_caracteres_por_linea'", "'" . addslashes($_SESSION["tramite"]["rues_caracteres_por_linea"]) . "'");
        }
        if (trim($_SESSION["tramite"]["tipoidefirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tipoidefirmante'", "'" . addslashes($_SESSION["tramite"]["tipoidefirmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["identificacionfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'identificacionfirmante'", "'" . addslashes($_SESSION["tramite"]["identificacionfirmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["fechaexpfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'fechaexpfirmante'", "'" . addslashes($_SESSION["tramite"]["fechaexpfirmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido1firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido1firmante'", "'" . addslashes($_SESSION["tramite"]["apellido1firmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["apellido2firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'apellido2firmante'", "'" . addslashes($_SESSION["tramite"]["apellido2firmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre1firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre1firmante'", "'" . addslashes($_SESSION["tramite"]["nombre1firmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["nombre2firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'nombre2firmante'", "'" . addslashes($_SESSION["tramite"]["nombre2firmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailfirmante'", "'" . addslashes($_SESSION["tramite"]["emailfirmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["emailfirmanteseguimiento"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailfirmanteseguimiento'", "'" . addslashes($_SESSION["tramite"]["emailfirmanteseguimiento"]) . "'");
        }
        if (trim($_SESSION["tramite"]["celularfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'celularfirmante'", "'" . addslashes($_SESSION["tramite"]["celularfirmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["direccionfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'direccionfirmante'", "'" . addslashes($_SESSION["tramite"]["direccionfirmante"]) . "'");
        }
        if (trim($_SESSION["tramite"]["municipiofirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'municipiofirmante'", "'" . addslashes($_SESSION["tramite"]["municipiofirmante"]) . "'");
        }

        //
        if (trim($_SESSION["tramite"]["emailcontactoasesoria"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'emailcontactoasesoria'", "'" . addslashes($_SESSION["tramite"]["emailcontactoasesoria"]) . "'");
        }
        if (trim($_SESSION["tramite"]["comentariosasesoria"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'comentariosasesoria'", "'" . addslashes($_SESSION["tramite"]["comentariosasesoria"]) . "'");
        }

        //
        if (trim($_SESSION["tramite"]["pedirbalance"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'pedirbalance'", "'" . addslashes($_SESSION["tramite"]["pedirbalance"]) . "'");
        }

        //
        if (trim($_SESSION["tramite"]["quienasesora"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'quienasesora'", "'" . addslashes($_SESSION["tramite"]["quienasesora"]) . "'");
        }

        //
        if (trim($_SESSION["tramite"]["benley1780"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'benley1780'", "'" . addslashes($_SESSION["tramite"]["benley1780"]) . "'");
        }

        //
        if (trim($_SESSION["tramite"]["fechanacimientopnat"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'fechanacimientopnat'", "'" . addslashes($_SESSION["tramite"]["fechanacimientopnat"]) . "'");
        }

        //
        if (intval(trim($_SESSION["tramite"]["incrementocupocertificados"])) != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'incrementocupocertificados'", "'" . ($_SESSION["tramite"]["incrementocupocertificados"]) . "'");
        }

        // 2016-08-23 : JINT
        if (intval(trim($_SESSION["tramite"]["cobrarmutacion"])) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'cobrarmutacion'", "'" . ($_SESSION["tramite"]["cobrarmutacion"]) . "'");
        }

        // 2016-10-22 : JINT
        $ix++;
        $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'aceptadoprepago'", "'" . ($_SESSION["tramite"]["aceptadoprepago"]) . "'");

        // 2016-10-22 : JINT
        if (trim($_SESSION["tramite"]["propcamaraorigen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propcamaraorigen'", "'" . ($_SESSION["tramite"]["propcamaraorigen"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propidmunicipioorigen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propidmunicipioorigen'", "'" . ($_SESSION["tramite"]["propidmunicipioorigen"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propidmunicipiodestino"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propidmunicipiodestino'", "'" . ($_SESSION["tramite"]["propidmunicipiodestino"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propproponenteorigen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propproponenteorigen'", "'" . ($_SESSION["tramite"]["propproponenteorigen"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propfechaultimainscripcion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propfechaultimainscripcion'", "'" . ($_SESSION["tramite"]["propfechaultimainscripcion"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propfechaultimarenovacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propfechaultimarenovacion'", "'" . ($_SESSION["tramite"]["propfechaultimarenovacion"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propdircom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propdircom'", "'" . ($_SESSION["tramite"]["propdircom"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propmuncom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propmuncom'", "'" . ($_SESSION["tramite"]["propmuncom"]) . "'");
        }
        if (trim($_SESSION["tramite"]["proptelcom1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'proptelcom1'", "'" . ($_SESSION["tramite"]["proptelcom1"]) . "'");
        }
        if (trim($_SESSION["tramite"]["proptelcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'proptelcom2'", "'" . ($_SESSION["tramite"]["proptelcom2"]) . "'");
        }
        if (trim($_SESSION["tramite"]["proptelcom3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'proptelcom3'", "'" . ($_SESSION["tramite"]["proptelcom3"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propemailcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propemailcom'", "'" . ($_SESSION["tramite"]["propemailcom"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propdirnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propdirnot'", "'" . ($_SESSION["tramite"]["propdirnot"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propmunnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propmunnot'", "'" . ($_SESSION["tramite"]["propmunnot"]) . "'");
        }
        if (trim($_SESSION["tramite"]["proptelnot1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'proptelnot1'", "'" . ($_SESSION["tramite"]["proptelnot1"]) . "'");
        }
        if (trim($_SESSION["tramite"]["proptelnot2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'proptelnot2'", "'" . ($_SESSION["tramite"]["proptelnot2"]) . "'");
        }
        if (trim($_SESSION["tramite"]["proptelnot3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'proptelnot3'", "'" . ($_SESSION["tramite"]["proptelnot3"]) . "'");
        }
        if (trim($_SESSION["tramite"]["propemailnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'propemailnot'", "'" . ($_SESSION["tramite"]["propemailnot"]) . "'");
        }

        if (trim($_SESSION["tramite"]["tiporenovacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'tiporenovacion'", "'" . ($_SESSION["tramite"]["tiporenovacion"]) . "'");
        }
        if (trim($_SESSION["tramite"]["activosbase"]) != '' && $_SESSION["tramite"]["activosbase"] != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'activosbase'", "'" . ($_SESSION["tramite"]["activosbase"]) . "'");
        }
        if (trim($_SESSION["tramite"]["personalbase"]) != '' && $_SESSION["tramite"]["personalbase"] != 0) {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'personalbase'", "'" . ($_SESSION["tramite"]["personalbase"]) . "'");
        }
        if (trim($_SESSION["tramite"]["enviara"]) != '') {
            $ix++;
            $arrValores[$ix] = array($_SESSION["tramite"]["numeroliquidacion"], "'enviara'", "'" . ($_SESSION["tramite"]["enviara"]) . "'");
        }

        //
        $res = insertarRegistrosBloqueMysqli2($dbx, 'mreg_liquidacion_campos', $arrCampos, $arrValores);
        if ($res === false) {
            return false;
        }

        $result = borrarRegistrosMysqli2($dbx, 'mreg_liquidaciondetalle', 'idliquidacion=' . $_SESSION["tramite"]["numeroliquidacion"]);
        $exp1 = '';
        $nom1 = '';
        $i = 0;
        foreach ($_SESSION["tramite"]["liquidacion"] as $liq) {
            if (!isset($liq["idsec"])) {
                $liq["idsec"] = '';
            }
            if (!isset($liq["idservicio"])) {
                $liq["idservicio"] = '';
            }
            if (!isset($liq["expediente"])) {
                $liq["expediente"] = '';
            }
            if (!isset($liq["nombre"])) {
                $liq["nombre"] = '';
            }
            if (!isset($liq["ano"])) {
                $liq["ano"] = '';
            }
            if (!isset($liq["cantidad"])) {
                $liq["cantidad"] = 0;
            }
            if (!isset($liq["valorbase"])) {
                $liq["valorbase"] = 0;
            }
            if (!isset($liq["porcentaje"])) {
                $liq["porcentaje"] = 0;
            }
            if (!isset($liq["valorservicio"])) {
                $liq["valorservicio"] = 0;
            }
            if (!isset($liq["benart7"])) {
                $liq["benart7"] = '';
            }
            if (!isset($liq["benley1780"])) {
                $liq["benley1780"] = '';
            }

            if (!isset($liq["reliquidacion"])) {
                $liq["reliquidacion"] = '';
            }
            if (!isset($liq["serviciobase"])) {
                $liq["serviciobase"] = '';
            }
            if (!isset($liq["pagoafiliacion"])) {
                $liq["pagoafiliacion"] = '';
            }
            if (!isset($liq["ir"])) {
                $liq["ir"] = '';
            }
            if (!isset($liq["iva"])) {
                $liq["iva"] = '';
            }
            if (!isset($liq["idalerta"])) {
                $liq["idalerta"] = '';
            }

            if (trim($liq["idservicio"]) != '') {
                $i++;
                if ($i == 1) {
                    $exp1 = $liq["expediente"];
                    $nom1 = $liq["nombre"];
                }

                //
                $arrCampos = array(
                    'idliquidacion',
                    'secuencia',
                    'idsec',
                    'idservicio',
                    'cc',
                    'expediente',
                    'nombre',
                    'ano',
                    'cantidad',
                    'valorbase',
                    'porcentaje',
                    'valorservicio',
                    'benart7',
                    'benley1780',
                    'reliquidacion',
                    'serviciobase',
                    'pagoafiliacion',
                    'ir',
                    'iva',
                    'idalerta'
                );

                //
                if (!isset($liq["cc"])) {
                    $liq["cc"] = '';
                }
                //
                $arrValues = array(
                    $_SESSION["tramite"]["numeroliquidacion"],
                    $i,
                    "'" . sprintf("%03s", $liq["idsec"]) . "'",
                    "'" . $liq["idservicio"] . "'",
                    "'" . $liq["cc"] . "'",
                    "'" . $liq["expediente"] . "'",
                    "'" . addslashes($liq["nombre"]) . "'",
                    "'" . $liq["ano"] . "'",
                    intval($liq["cantidad"]),
                    doubleval($liq["valorbase"]),
                    doubleval($liq["porcentaje"]),
                    doubleval($liq["valorservicio"]),
                    "'" . $liq["benart7"] . "'",
                    "'" . $liq["benley1780"] . "'",
                    "'" . $liq["reliquidacion"] . "'",
                    "'" . $liq["serviciobase"] . "'",
                    "'" . $liq["pagoafiliacion"] . "'",
                    "'" . $liq["ir"] . "'",
                    "'" . $liq["iva"] . "'",
                    intval($liq["idalerta"])
                );

                //
                $result = insertarRegistrosMysqli2($dbx, 'mreg_liquidaciondetalle', $arrCampos, $arrValues);
                if ($result === false) {
                    $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_liquidaciondetalle (' . $_SESSION ["generales"] ["mensajeerror"] . ')';
                    return false;
                }
            }
        }

        // Graba liquidacion RUES
        $result = borrarRegistrosMysqli2($dbx, 'mreg_liquidaciondetalle_rues', 'idliquidacion=' . $_SESSION["tramite"]["numeroliquidacion"]);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros de mreg_liquidaciondetalle_rues';
            return false;
        }
        $exp1 = '';
        $nom1 = '';
        $i = 0;

        //
        if (!isset($_SESSION["tramite"]["rues_servicios"])) {
            $_SESSION["tramite"]["rues_servicios"] = array();
        }

        //
        foreach ($_SESSION["tramite"]["rues_servicios"] as $liq) {
            if (!isset($liq["codigo_servicio"])) {
                $liq["codigo_servicio"] = '';
            }
            if (!isset($liq["descripcion_servicio"])) {
                $liq["descripcion_servicio"] = '';
            }
            if (!isset($liq["orden_servicio"])) {
                $liq["orden_servicio"] = 0;
            }
            if (!isset($liq["orden_servicio_asociado"])) {
                $liq["orden_servicio_asociado"] = 0;
            }
            if (!isset($liq["nombre_base"])) {
                $liq["nombre_base"] = '';
            }
            if (!isset($liq["valor_base"])) {
                $liq["valor_base"] = 0;
            }
            if (!isset($liq["valor_liquidacion"])) {
                $liq["valor_liquidacion"] = 0;
            }
            if (!isset($liq["cantidad_servicio"])) {
                $liq["cantidad_servicio"] = 0;
            }
            if (!isset($liq["indicador_base"])) {
                $liq["indicador_base"] = '';
            }
            if (!isset($liq["indicador_renovacion"])) {
                $liq["indicador_renovacion"] = '';
            }
            if (!isset($liq["matricula_servicio"])) {
                $liq["matricula_servicio"] = '';
            }
            if (!isset($liq["nombre_matriculado"])) {
                $liq["nombre_matriculado"] = '';
            }
            if (!isset($liq["ano_renovacion"])) {
                $liq["ano_renovacion"] = '';
            }
            if (!isset($liq["valor_activos_sin_ajustes"])) {
                $liq["valor_activos_sin_ajustes"] = 0;
            }

            $i++;

            $arrCampos = array(
                'idliquidacion',
                'secuencia',
                'codigo_servicio',
                'descripcion_servicio',
                'orden_servicio',
                'orden_servicio_asociado',
                'nombre_base',
                'valor_base',
                'valor_liquidacion',
                'cantidad_servicio',
                'indicador_base',
                'indicador_renovacion',
                'matricula_servicio',
                'nombre_matriculado',
                'ano_renovacion',
                'valor_activos_sin_ajustes'
            );

            $arrValues = array(
                $_SESSION["tramite"]["numeroliquidacion"],
                $i,
                "'" . $liq["codigo_servicio"] . "'",
                "'" . $liq["descripcion_servicio"] . "'",
                "'" . $liq["orden_servicio"] . "'",
                "'" . $liq["orden_servicio_asociado"] . "'",
                "'" . addslashes($liq["nombre_base"]) . "'",
                "'" . $liq["valor_base"] . "'",
                "'" . $liq["valor_liquidacion"] . "'",
                "'" . $liq["cantidad_servicio"] . "'",
                "'" . $liq["indicador_base"] . "'",
                "'" . $liq["indicador_renovacion"] . "'",
                "'" . $liq["matricula_servicio"] . "'",
                "'" . addslashes($liq["nombre_matriculado"]) . "'",
                "'" . $liq["ano_renovacion"] . "'",
                "'" . $liq["valor_activos_sin_ajustes"] . "'"
            );

            $result = insertarRegistrosMysqli2($dbx, 'mreg_liquidaciondetalle_rues', $arrCampos, $arrValues);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_liquidaciondetalle_rues (' . $_SESSION ["generales"] ["mensajeerror"] . ')';
                return false;
            }
        }

        //
        if (!isset($_SESSION["tramite"]["rues_textos"])) {
            $_SESSION["tramite"]["rues_textos"] = array();
        }

        //
        borrarRegistrosMysqli2($dbx, 'mreg_liquidacion_textos_rues', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"]);

        //
        $ix = 0;
        foreach ($_SESSION["tramite"]["rues_textos"] as $tx) {
            $ix++;

            //
            $arrCampos = array(
                'idliquidacion',
                'secuencia',
                'texto',
            );

            //
            $arrValues = array(
                $_SESSION["tramite"]["numeroliquidacion"],
                $ix,
                "'" . addslashes($tx) . "'"
            );

            //
            $result = insertarRegistrosMysqli2($dbx, 'mreg_liquidacion_textos_rues', $arrCampos, $arrValues);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_liquidaciontextos_rues (' . $_SESSION ["generales"] ["mensajeerror"] . ')';
                return false;
            }
        }


        // Graba expedientes
        $result = borrarRegistrosMysqli2($dbx, 'mreg_liquidacionexpedientes', 'idliquidacion=' . $_SESSION["tramite"]["numeroliquidacion"]);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros de mreg_liquidacionexpedientes';
            return false;
        }

        if (!empty($_SESSION["tramite"]["expedientes"])) {
            $i = 0;
            foreach ($_SESSION["tramite"]["expedientes"] as $mat) {
                if (!isset($mat["cc"])) {
                    $mat["cc"] = '';
                }
                if (!isset($mat["matricula"])) {
                    $mat["matricula"] = '';
                }
                if (!isset($mat["proponente"])) {
                    $mat["proponente"] = '';
                }
                if (!isset($mat["numrue"])) {
                    $mat["numrue"] = '';
                }
                if (!isset($mat["idtipoidentificacion"])) {
                    $mat["idtipoidentificacion"] = '';
                }
                if (!isset($mat["identificacion"])) {
                    $mat["identificacion"] = '';
                }
                if (!isset($mat["razonsocial"])) {
                    $mat["razonsocial"] = '';
                }
                if (!isset($mat["ape1"])) {
                    $mat["ape1"] = '';
                }
                if (!isset($mat["ape2"])) {
                    $mat["ape2"] = '';
                }
                if (!isset($mat["nom1"])) {
                    $mat["nom1"] = '';
                }
                if (!isset($mat["nom2"])) {
                    $mat["nom2"] = '';
                }
                if (!isset($mat["organizacion"])) {
                    $mat["organizacion"] = '';
                }
                if (!isset($mat["categoria"])) {
                    $mat["caetgoria"] = '';
                }
                if (!isset($mat["afiliado"])) {
                    $mat["afiliado"] = '';
                }
                if (!isset($mat["propietariojurisdiccion"])) {
                    $mat["propietariojurisdiccion"] = '';
                }
                if (!isset($mat["primeranorenovado"])) {
                    $mat["primeranorenovado"] = '';
                }
                if (!isset($mat["ultimoanoafiliado"])) {
                    $mat["ultimoanoafiliado"] = '';
                }
                if (!isset($mat["ultimoanorenovado"])) {
                    $mat["ultimoanorenovado"] = '';
                }
                if (!isset($mat["ultimosactivos"])) {
                    $mat["ultimosactivos"] = 0;
                }
                if (!isset($mat["nuevosactivos"])) {
                    $mat["nuevosactivos"] = 0;
                }
                if (!isset($mat["actividad"])) {
                    $mat["actividad"] = '';
                }
                if (!isset($mat["registrobase"])) {
                    $mat["registrobase"] = '';
                }
                if (!isset($mat["benart7"])) {
                    $mat["benart7"] = '';
                }
                if (!isset($mat["benley1780"])) {
                    $mat["benley1780"] = '';
                }

                if (!isset($mat["renovaresteano"])) {
                    $mat["renovaresteano"] = '';
                }
                if (!isset($mat["fechanacimiento"])) {
                    $mat["fechanacimiento"] = '';
                }
                if (!isset($mat["fechamatricula"])) {
                    $mat["fechamatricula"] = '';
                }
                if (!isset($mat["fecmatant"])) {
                    $mat["fecmatant"] = '';
                }
                if (!isset($mat["reliquidacion"])) {
                    $mat["reliquidacion"] = '';
                }
                if ($mat["ultimosactivos"] == '') {
                    $mat["ultimosactivos"] = 0;
                }
                if ($mat["nuevosactivos"] == '') {
                    $mat["nuevosactivos"] = 0;
                }
                if (!isset($mat["controlpot"])) {
                    $mat["controlpot"] = '';
                }
                if (!isset($mat["dircom"])) {
                    $mat["dircom"] = '';
                }
                if (!isset($mat["muncom"])) {
                    $mat["muncom"] = '';
                }

                //
                $i++;
                $arrCampos = array(
                    'idliquidacion',
                    'secuencia',
                    'cc',
                    'matricula',
                    'proponente',
                    'numrue',
                    'idtipoidentificacion',
                    'identificacion',
                    'razonsocial',
                    'ape1',
                    'ape2',
                    'nom1',
                    'nom2',
                    'organizacion',
                    'categoria',
                    'afiliado',
                    'propietariojurisdiccion',
                    'ultimoanoafiliado',
                    'ultimoanorenovado',
                    'primeranorenovado',
                    'ultimosactivos',
                    'nuevosactivos',
                    'actividad',
                    'registrobase',
                    'benart7',
                    'benley1780',
                    'renovaresteano',
                    'fechanacimiento',
                    'fechamatricula',
                    'fecmatant',
                    'reliquidacion',
                    'controlpot',
                    'dircom',
                    'muncom'
                );
                if (!isset($mat["primeranorenovado"])) {
                    $mat["primeranorenovado"] = '';
                }
                if (!isset($mat["controlpot"])) {
                    $mat["controlpot"] = '';
                }
                $arrValues = array(
                    $_SESSION["tramite"]["numeroliquidacion"],
                    sprintf("%03s", $i),
                    "'" . $mat["cc"] . "'",
                    "'" . $mat["matricula"] . "'",
                    "'" . $mat["proponente"] . "'",
                    "'" . $mat["numrue"] . "'",
                    "'" . $mat["idtipoidentificacion"] . "'",
                    "'" . $mat["identificacion"] . "'",
                    "'" . addslashes(\sanarEntradas::sanarString($mat["razonsocial"])) . "'",
                    "'" . addslashes(\sanarEntradas::sanarString($mat["ape1"])) . "'",
                    "'" . addslashes(\sanarEntradas::sanarString($mat["ape2"])) . "'",
                    "'" . addslashes(\sanarEntradas::sanarString($mat["nom1"])) . "'",
                    "'" . addslashes(\sanarEntradas::sanarString($mat["nom2"])) . "'",
                    "'" . $mat["organizacion"] . "'",
                    "'" . $mat["categoria"] . "'",
                    "'" . $mat["afiliado"] . "'",
                    "'" . $mat["propietariojurisdiccion"] . "'",
                    "'" . $mat["ultimoanoafiliado"] . "'",
                    "'" . $mat["ultimoanorenovado"] . "'",
                    "'" . $mat["primeranorenovado"] . "'",
                    $mat["ultimosactivos"],
                    $mat["nuevosactivos"],
                    "'" . $mat["actividad"] . "'",
                    "'" . $mat["registrobase"] . "'",
                    "'" . $mat["benart7"] . "'",
                    "'" . $mat["benley1780"] . "'",
                    "'" . $mat["renovaresteano"] . "'",
                    "'" . $mat["fechanacimiento"] . "'",
                    "'" . $mat["fechamatricula"] . "'",
                    "'" . $mat["fecmatant"] . "'",
                    "'" . $mat["reliquidacion"] . "'",
                    "'" . $mat["controlpot"] . "'",
                    "'" . addslashes($mat["dircom"]) . "'",
                    "'" . $mat["muncom"] . "'"
                );
                $result = insertarRegistrosMysqli2($dbx, 'mreg_liquidacionexpedientes', $arrCampos, $arrValues);
                if ($result === false) {
                    $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros de mreg_liquidacionexpedientes';
                    return false;
                }
            }
        }

        // Graba transacciones
        $result = borrarRegistrosMysqli2($dbx, 'mreg_liquidacion_transacciones', 'idliquidacion=' . $_SESSION["tramite"]["numeroliquidacion"]);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros de mreg_liquidaciondetalle';
            return false;
        }
        $exp1 = '';
        $nom1 = '';
        $i = 0;

        //
        $retornar = true;
        $mensajeRetornar = '';

        if (!empty($_SESSION["tramite"]["transacciones"])) {
            foreach ($_SESSION["tramite"]["transacciones"] as $tra) {
                if (!isset($tra["ultimoanorenovadoanterior"])) {
                    $tra["ultimoanorenovadoanterior"] = '';
                }

                //
                if (!isset($tra["benart7"])) {
                    $tra["benart7"] = '';
                }
                if (!isset($tra["benley1780"])) {
                    $tra["benley1780"] = '';
                }
                if (!isset($tra["fechanacimientopnat"])) {
                    $tra["fechanacimientopnat"] = '';
                }

                //
                if (!isset($tra["patrimonio"])) {
                    $tra["patrimonio"] = 0;
                }
                if (!isset($tra["capitalsocial"])) {
                    $tra["capitalsocial"] = 0;
                }
                if (!isset($tra["capitalautorizado"])) {
                    $tra["capitalautorizado"] = 0;
                }
                if (!isset($tra["capitalsuscrito"])) {
                    $tra["capitalsuscrito"] = 0;
                }
                if (!isset($tra["capitalpagado"])) {
                    $tra["capitalpagado"] = 0;
                }
                if (!isset($tra["aportedinero"])) {
                    $tra["aportedinero"] = 0;
                }
                if (!isset($tra["aporteactivos"])) {
                    $tra["aporteactivos"] = 0;
                }
                if (!isset($tra["aportelaboral"])) {
                    $tra["aportelaboral"] = 0;
                }
                if (!isset($tra["aportelaboraladicional"])) {
                    $tra["aportelaboraladicional"] = 0;
                }
                if (!isset($tra["capitalasignado"])) {
                    $tra["capitalasignado"] = 0;
                }
                if (!isset($tra["pornaltot"])) {
                    $tra["pornaltot"] = 0;
                }
                if (!isset($tra["pornalpub"])) {
                    $tra["pornalpub"] = 0;
                }
                if (!isset($tra["pornalpri"])) {
                    $tra["pornalpri"] = 0;
                }
                if (!isset($tra["porexttot"])) {
                    $tra["porexttot"] = 0;
                }
                if (!isset($tra["porextpub"])) {
                    $tra["porextpub"] = 0;
                }
                if (!isset($tra["porextpri"])) {
                    $tra["porextpri"] = 0;
                }

                if (!isset($tra["tipoidentificacionvendedor"])) {
                    $tra["tipoidentificacionvendedor"] = '';
                }
                if (!isset($tra["identificacionvendedor"])) {
                    $tra["identificacionvendedor"] = '';
                }
                if (!isset($tra["nombrevendedor"])) {
                    $tra["nombrevendedor"] = '';
                }
                if (!isset($tra["camaravendedor"])) {
                    $tra["camaravendedor"] = '';
                }
                if (!isset($tra["matriculavendedor"])) {
                    $tra["matriculavendedor"] = '';
                }
                if (!isset($tra["nombre1vendedor"])) {
                    $tra["nombre1vendedor"] = '';
                }
                if (!isset($tra["nombre2vendedor"])) {
                    $tra["nombre2vendedor"] = '';
                }
                if (!isset($tra["apellido1vendedor"])) {
                    $tra["apellido1vendedor"] = '';
                }
                if (!isset($tra["apellido2vendedor"])) {
                    $tra["apellido2vendedor"] = '';
                }
                if (!isset($tra["emailvendedor"])) {
                    $tra["emailvendedor"] = '';
                }
                if (!isset($tra["celularvendedor"])) {
                    $tra["celularvendedor"] = '';
                }
                if (!isset($tra["cancelarvendedor"])) {
                    $tra["cancelarvendedor"] = '';
                }

                if (!isset($tra["tipoidentificacioncomprador"])) {
                    $tra["tipoidentificacioncomprador"] = '';
                }
                if (!isset($tra["identificacioncomprador"])) {
                    $tra["identificacioncomprador"] = '';
                }
                if (!isset($tra["nombrecomprador"])) {
                    $tra["nombrecomprador"] = '';
                }
                if (!isset($tra["camaracomprador"])) {
                    $tra["camaracomprador"] = '';
                }
                if (!isset($tra["matriculacomprador"])) {
                    $tra["matriculacomprador"] = '';
                }
                if (!isset($tra["organizacioncomprador"])) {
                    $tra["organizacioncomprador"] = '';
                }
                if (!isset($tra["municipiocomprador"])) {
                    $tra["municipiocomprador"] = '';
                }
                if (!isset($tra["nombre1comprador"])) {
                    $tra["nombre1comprador"] = '';
                }
                if (!isset($tra["nombre2comprador"])) {
                    $tra["nombre2comprador"] = '';
                }
                if (!isset($tra["apellido1comprador"])) {
                    $tra["apellido1comprador"] = '';
                }
                if (!isset($tra["apellido2comprador"])) {
                    $tra["apellido2comprador"] = '';
                }
                if (!isset($tra["emailcomprador"])) {
                    $tra["emailcomprador"] = '';
                }
                if (!isset($tra["celularcomprador"])) {
                    $tra["celularcomprador"] = '';
                }
                if (!isset($tra["activoscomprador"])) {
                    $tra["activoscomprador"] = 0;
                }
                if (!isset($tra["personalcomprador"])) {
                    $tra["personalcomprador"] = 0;
                }

                if (!isset($tra["municipioanterior"])) {
                    $tra["municipioanterior"] = '';
                }
                if (!isset($tra["matriculaanterior"])) {
                    $tra["matriculaanterior"] = '';
                }
                if (!isset($tra["camaraanterior"])) {
                    $tra["camaraanterior"] = '';
                }
                if (!isset($tra["fecharenovacionanterior"])) {
                    $tra["fecharenovacionanterior"] = '';
                }
                if (!isset($tra["benart7anterior"])) {
                    $tra["benart7anterior"] = '';
                }
                if (!isset($tra["municipiodestino"])) {
                    $tra["municipiodestino"] = '';
                }
                if (!isset($tra["camaradestino"])) {
                    $tra["camaradestino"] = '';
                }
                if (!isset($tra["prerut"])) {
                    $tra["prerut"] = '';
                }
                if (!isset($tra["acreditapagoir"])) {
                    $tra["acreditapagoir"] = '';
                }
                if (!isset($tra["nroreciboacreditapagoir"])) {
                    $tra["nroreciboacreditapagoir"] = '';
                }
                if (!isset($tra["fechareciboacreditapagoir"])) {
                    $tra["fechareciboacreditapagoir"] = '';
                }
                if (!isset($tra["gobernacionreciboacreditapagoir"])) {
                    $tra["gobernacionreciboacreditapagoir"] = '';
                }
                if (!isset($tra["tipodisolucion"])) {
                    $tra["tipodisolucion"] = '';
                }
                if (!isset($tra["tipoliquidacion"])) {
                    $tra["tipoliquidacion"] = '';
                }
                if (!isset($tra["motivoliquidacion"])) {
                    $tra["motivoliquidacion"] = '';
                }
                if (!isset($tra["ciiu1"])) {
                    $tra["ciiu1"] = '';
                }
                if (!isset($tra["ciiu2"])) {
                    $tra["ciiu2"] = '';
                }
                if (!isset($tra["ciiu3"])) {
                    $tra["ciiu3"] = '';
                }
                if (!isset($tra["ciiu4"])) {
                    $tra["ciiu4"] = '';
                }
                if (!isset($tra["entidadvigilancia"])) {
                    $tra["entidadvigilancia"] = '';
                }
                if (!isset($tra["objetosocial"])) {
                    $tra["objetosocial"] = '';
                }
                if (!isset($tra["facultades"])) {
                    $tra["facultades"] = '';
                }
                if (!isset($tra["limitaciones"])) {
                    $tra["limitaciones"] = '';
                }
                if (!isset($tra["entidadvigilancia"])) {
                    $tra["entidadvigilancia"] = '';
                }
                if (!isset($tra["poderespecial"])) {
                    $tra["poderespecial"] = '';
                }

                if (!isset($tra["clase_libro"])) {
                    $tra["clase_libro"] = '';
                }
                if (!isset($tra["tipo_libro"])) {
                    $tra["tipo_libro"] = '';
                }
                if (!isset($tra["codigo_libro"])) {
                    $tra["codigo_libro"] = '';
                }
                if (!isset($tra["nombre_libro"])) {
                    $tra["nombre_libro"] = '';
                }
                if (!isset($tra["email_libro"])) {
                    $tra["email_libro"] = '';
                }
                if (!isset($tra["emailconfirmacion_libro"])) {
                    $tra["emailconfirmacion_libro"] = '';
                }
                if (!isset($tra["paginainicial_libro"]) || ltrim($tra["paginainicial_libro"], "0") == '') {
                    $tra["paginainicial_libro"] = 0;
                }
                if (!isset($tra["paginafinal_libro"]) || ltrim($tra["paginafinal_libro"], "0") == '') {
                    $tra["paginafinal_libro"] = 0;
                }
                if (!isset($tra["incluirrotulado_libro"])) {
                    $tra["incluirrotulado_libro"] = '';
                }

                if (!isset($tra["actanro_libro"])) {
                    $tra["actanro_libro"] = '';
                }
                if (!isset($tra["fechaacta_libro"])) {
                    $tra["fechaacta_libro"] = '';
                }
                if (!isset($tra["horaacta_libro"])) {
                    $tra["horaacta_libro"] = '';
                }
                if (!isset($tra["fechaini_libroelectronico"])) {
                    $tra["fechaini_libroelectronico"] = '';
                }
                if (!isset($tra["fechafin_libroelectronico"])) {
                    $tra["fechafin_libroelectronico"] = '';
                }
                if (!isset($tra["fechainiinscripciones_libroelectronico"])) {
                    $tra["fechainiinscripciones_libroelectronico"] = '';
                }
                if (!isset($tra["fechafininscripciones_libroelectronico"])) {
                    $tra["fechafininscripciones_libroelectronico"] = '';
                }

                //
                if (!isset($tra["libeleanot_libro"])) {
                    $tra["libeleanot_libro"] = '';
                }
                if (!isset($tra["libeleanot_registro"])) {
                    $tra["libeleanot_registro"] = '';
                }
                if (!isset($tra["libeleanot_dupli"])) {
                    $tra["libeleanot_dupli"] = '';
                }

                if (!isset($tra["libeleanot_nroacta"])) {
                    $tra["libeleanot_nroacta"] = '';
                }
                if (!isset($tra["libeleanot_fechaacta"])) {
                    $tra["libeleanot_fechaacta"] = '';
                }
                if (!isset($tra["libeleanot_nropaginas"])) {
                    $tra["libeleanot_nropaginas"] = 0;
                }
                if (!isset($tra["libeleanot_fechainiinscripciones"])) {
                    $tra["libeleanot_fechainiinscripciones"] = '';
                }
                if (!isset($tra["libeleanot_fechafininscripciones"])) {
                    $tra["libeleanot_fechafininscripciones"] = '';
                }
                if (!isset($tra["libeleanot_nroregistros"])) {
                    $tra["libeleanot_nroregistros"] = 0;
                }


                //
                if (!isset($tra["embargo"])) {
                    $tra["embargo"] = '';
                }
                if (!isset($tra["tipoideembargante"])) {
                    $tra["tipoideembargante"] = '';
                }
                if (!isset($tra["ideembargante"])) {
                    $tra["ideembargante"] = '';
                }
                if (!isset($tra["nom1embargante"])) {
                    $tra["nom1embargante"] = '';
                }
                if (!isset($tra["nom2embargante"])) {
                    $tra["nom2embargante"] = '';
                }
                if (!isset($tra["ape1embargante"])) {
                    $tra["ape1embargante"] = '';
                }
                if (!isset($tra["ape2embargante"])) {
                    $tra["ape2embargante"] = '';
                }

                //
                if (!isset($tra["desembargo"])) {
                    $tra["desembargo"] = '';
                }
                if (!isset($tra["librocruce"])) {
                    $tra["librocruce"] = '';
                }
                if (!isset($tra["inscripcioncruce"])) {
                    $tra["inscripcioncruce"] = '';
                }

                if (!isset($tra["cantidad"])) {
                    $tra["cantidad"] = 1;
                }

                if (!isset($tra["cantidadadicional"])) {
                    $tra["cantidadadicional"] = 0;
                }

                $i++;
                $arrCampos = array(
                    'idliquidacion',
                    'idsecuencia',
                    'idtransaccion',
                    'matriculaafectada',
                    'tipodoc',
                    'numdoc',
                    'fechadoc',
                    'origendoc',
                    'mundoc',
                    'camaravendedor',
                    'matriculavendedor',
                    'tipoidentificacionvendedor',
                    'identificacionvendedor',
                    'nombrevendedor',
                    'nombre1vendedor',
                    'nombre2vendedor',
                    'apellido1vendedor',
                    'apellido2vendedor',
                    'emailvendedor',
                    'celularvendedor',
                    'cancelarvendedor',
                    'camaracomprador',
                    'matriculacomprador',
                    'organizacioncomprador',
                    'municipiocomprador',
                    'tipoidentificacioncomprador',
                    'identificacioncomprador',
                    'nombrecomprador',
                    'nombre1comprador',
                    'nombre2comprador',
                    'apellido1comprador',
                    'apellido2comprador',
                    'emailcomprador',
                    'celularcomprador',
                    'activoscomprador',
                    'personalcomprador',
                    'municipioanterior',
                    'matriculaanterior',
                    'camaraanterior',
                    'fechamatriculaanterior',
                    'fecharenovacionanterior',
                    'ultimoanorenovadoanterior',
                    'benart7anterior',
                    'municipiodestino',
                    'camaradestino',
                    'organizacion',
                    'categoria',
                    'razonsocial',
                    'sigla',
                    'tipoidentificacion',
                    'identificacion',
                    'nit',
                    'prerut',
                    'ape1',
                    'ape2',
                    'nom1',
                    'nom2',
                    'cargo',
                    'idvinculo',
                    'idrenglon',
                    'aceptacion',
                    'identificacionrepresentada',
                    'razonsocialrepresentada',
                    'pornaltot',
                    'pornalpub',
                    'pornalpri',
                    'porexttot',
                    'porextpub',
                    'porextpri',
                    'personal',
                    'activos',
                    'costotransaccion',
                    'patrimonio',
                    'benart7',
                    'benley1780',
                    'fechanacimientopnat',
                    'capitalsocial',
                    'capitalautorizado',
                    'capitalsuscrito',
                    'capitalpagado',
                    'aporteactivos',
                    'aportedinero',
                    'aportelaboral',
                    'aportelaboraladicional',
                    'capitalasignado',
                    'acreditapagoir',
                    'nroreciboacreditapagoir',
                    'fechareciboacreditapagoir',
                    'gobernacionacreditapagoir',
                    'dircom',
                    'municipio',
                    'fechaduracion',
                    'tipodisolucion',
                    'motivodisolucion',
                    'tipoliquidacion',
                    'motivoliquidacion',
                    'ciiu1',
                    'ciiu2',
                    'ciiu3',
                    'ciiu4',
                    'clase_libro',
                    'tipo_libro',
                    'codigo_libro',
                    'nombre_libro',
                    'email_libro',
                    'emailconfirmacion_libro',
                    'paginainicial_libro',
                    'paginafinal_libro',
                    'incluirrotulado_libro',
                    'libeleanot_libro',
                    'libeleanot_registro',
                    'libeleanot_dupli',
                    'libeleanot_nroacta',
                    'libeleanot_fechaacta',
                    'libeleanot_nropaginas',
                    'libeleanot_fechainiinscripciones',
                    'libeleanot_fechafininscripciones',
                    'libeleanot_nroregistros',
                    'actanro_libro',
                    'fechaacta_libro',
                    'horaacta_libro',
                    'fechaini_libroelectronico',
                    'fechafin_libroelectronico',
                    'fechainiinscripciones_libroelectronico',
                    'fechafininscripciones_libroelectronico',
                    'embargo',
                    'tipoideembargante',
                    'ideembargante',
                    'nom1embargante',
                    'nom2embargante',
                    'ape1embargante',
                    'ape2embargante',
                    'desembargo',
                    'librocruce',
                    'inscripcioncruce',
                    'entidadvigilancia',
                    'objetosocial',
                    'facultades',
                    'limitaciones',
                    'poderespecial',
                    'texto',
                    'cantidad'
                );

                if (doubleval($tra["activos"]) == 0) {
                    $tra["activos"] = 0;
                }
                if (doubleval($tra["personal"]) == 0) {
                    $tra["personal"] = 0;
                }
                if (doubleval($tra["costotransaccion"]) == 0) {
                    $tra["costotransaccion"] = 0;
                }
                if (doubleval($tra["patrimonio"]) == 0) {
                    $tra["patrimonio"] = 0;
                }
                if (doubleval($tra["capitalsocial"]) == 0) {
                    $tra["capitalsocial"] = 0;
                }
                if (doubleval($tra["capitalautorizado"]) == 0) {
                    $tra["capitalautorizado"] = 0;
                }
                if (doubleval($tra["capitalsuscrito"]) == 0) {
                    $tra["capitalsuscrito"] = 0;
                }
                if (doubleval($tra["capitalpagado"]) == 0) {
                    $tra["capitalpagado"] = 0;
                }
                if (doubleval($tra["aportedinero"]) == 0) {
                    $tra["aportedinero"] = 0;
                }
                if (doubleval($tra["aporteactivos"]) == 0) {
                    $tra["aporteactivos"] = 0;
                }
                if (doubleval($tra["aportelaboral"]) == 0) {
                    $tra["aportelaboral"] = 0;
                }
                if (doubleval($tra["aportelaboraladicional"]) == 0) {
                    $tra["aportelaboraladicional"] = 0;
                }
                if (doubleval($tra["capitalasignado"]) == 0) {
                    $tra["capitalasignado"] = 0;
                }
                if (doubleval($tra["pornaltot"]) == 0) {
                    $tra["pornaltot"] = 0;
                }
                if (doubleval($tra["pornalpub"]) == 0) {
                    $tra["pornalpub"] = 0;
                }
                if (doubleval($tra["pornalpri"]) == 0) {
                    $tra["pornalpri"] = 0;
                }
                if (doubleval($tra["porexttot"]) == 0) {
                    $tra["porexttot"] = 0;
                }
                if (doubleval($tra["porextpub"]) == 0) {
                    $tra["porextpub"] = 0;
                }
                if (doubleval($tra["porextpri"]) == 0) {
                    $tra["porextpri"] = 0;
                }
                if (!isset($tra["nit"])) {
                    $tra["nit"] = '';
                }

                $arrValores = array(
                    $_SESSION["tramite"]["numeroliquidacion"],
                    "'" . sprintf("%03s", $i) . "'",
                    "'" . $tra['idtransaccion'] . "'",
                    "'" . $tra['matriculaafectada'] . "'",
                    "'" . $tra['tipodoc'] . "'",
                    "'" . $tra['numdoc'] . "'",
                    "'" . $tra['fechadoc'] . "'",
                    "'" . $tra['origendoc'] . "'",
                    "'" . $tra['mundoc'] . "'",
                    "'" . $tra['camaravendedor'] . "'",
                    "'" . $tra['matriculavendedor'] . "'",
                    "'" . $tra['tipoidentificacionvendedor'] . "'",
                    "'" . $tra['identificacionvendedor'] . "'",
                    "'" . addslashes(($tra['nombrevendedor'])) . "'",
                    "'" . addslashes(($tra['nombre1vendedor'])) . "'",
                    "'" . addslashes(($tra['nombre2vendedor'])) . "'",
                    "'" . addslashes(($tra['apellido1vendedor'])) . "'",
                    "'" . addslashes(($tra['apellido2vendedor'])) . "'",
                    "'" . addslashes($tra['emailvendedor']) . "'",
                    "'" . addslashes($tra['celularvendedor']) . "'",
                    "'" . addslashes($tra['cancelarvendedor']) . "'",
                    "'" . $tra['camaracomprador'] . "'",
                    "'" . $tra['matriculacomprador'] . "'",
                    "'" . $tra['organizacioncomprador'] . "'",
                    "'" . $tra['municipiocomprador'] . "'",
                    "'" . $tra['tipoidentificacioncomprador'] . "'",
                    "'" . $tra['identificacioncomprador'] . "'",
                    "'" . addslashes(($tra['nombrecomprador'])) . "'",
                    "'" . addslashes(($tra['nombre1comprador'])) . "'",
                    "'" . addslashes(($tra['nombre2comprador'])) . "'",
                    "'" . addslashes(($tra['apellido1comprador'])) . "'",
                    "'" . addslashes(($tra['apellido2comprador'])) . "'",
                    "'" . addslashes($tra['emailcomprador']) . "'",
                    "'" . addslashes($tra['celularcomprador']) . "'",
                    $tra['activoscomprador'],
                    $tra['personalcomprador'],
                    "'" . $tra['municipioanterior'] . "'",
                    "'" . $tra['matriculaanterior'] . "'",
                    "'" . $tra['camaraanterior'] . "'",
                    "'" . $tra['fechamatriculaanterior'] . "'",
                    "'" . $tra['fecharenovacionanterior'] . "'",
                    "'" . $tra['ultimoanorenovadoanterior'] . "'",
                    "'" . $tra['benart7anterior'] . "'",
                    "'" . $tra['municipiodestino'] . "'",
                    "'" . $tra['camaradestino'] . "'",
                    "'" . $tra['organizacion'] . "'",
                    "'" . $tra['categoria'] . "'",
                    "'" . addslashes(($tra['razonsocial'])) . "'",
                    "'" . addslashes(($tra['sigla'])) . "'",
                    "'" . $tra['tipoidentificacion'] . "'",
                    "'" . $tra['identificacion'] . "'",
                    "'" . $tra['nit'] . "'",
                    "'" . $tra['prerut'] . "'",
                    "'" . addslashes(($tra['ape1'])) . "'",
                    "'" . addslashes(($tra['ape2'])) . "'",
                    "'" . addslashes(($tra['nom1'])) . "'",
                    "'" . addslashes(($tra['nom2'])) . "'",
                    "'" . addslashes(($tra['cargo'])) . "'",
                    "'" . $tra['idvinculo'] . "'",
                    "'" . $tra['idrenglon'] . "'",
                    "'" . $tra['aceptacion'] . "'",
                    "'" . $tra['identificacionrepresentada'] . "'",
                    "'" . addslashes(($tra['razonsocialrepresentada'])) . "'",
                    $tra['pornaltot'],
                    $tra['pornalpub'],
                    $tra['pornalpri'],
                    $tra['porexttot'],
                    $tra['porextpub'],
                    $tra['porextpri'],
                    $tra['personal'],
                    $tra['activos'],
                    $tra['costotransaccion'],
                    $tra['patrimonio'],
                    "'" . addslashes($tra['benart7']) . "'",
                    "'" . addslashes($tra['benley1780']) . "'",
                    "'" . addslashes($tra['fechanacimientopnat']) . "'",
                    $tra['capitalsocial'],
                    $tra['capitalautorizado'],
                    $tra['capitalsuscrito'],
                    $tra['capitalpagado'],
                    $tra['aporteactivos'],
                    $tra['aportedinero'],
                    $tra['aportelaboral'],
                    $tra['aportelaboraladicional'],
                    $tra['capitalasignado'],
                    "'" . $tra['acreditapagoir'] . "'",
                    "'" . $tra['nroreciboacreditapagoir'] . "'",
                    "'" . $tra['fechareciboacreditapagoir'] . "'",
                    "'" . $tra['gobernacionacreditapagoir'] . "'",
                    "'" . addslashes($tra['dircom']) . "'",
                    "'" . $tra['municipio'] . "'",
                    "'" . $tra['fechaduracion'] . "'",
                    "'" . $tra['tipodisolucion'] . "'",
                    "'" . addslashes($tra['motivodisolucion']) . "'",
                    "'" . $tra['tipoliquidacion'] . "'",
                    "'" . addslashes($tra['motivoliquidacion']) . "'",
                    "'" . $tra['ciiu1'] . "'",
                    "'" . $tra['ciiu2'] . "'",
                    "'" . $tra['ciiu3'] . "'",
                    "'" . $tra['ciiu4'] . "'",
                    "'" . $tra['clase_libro'] . "'",
                    "'" . $tra['tipo_libro'] . "'",
                    "'" . $tra['codigo_libro'] . "'",
                    "'" . addslashes($tra['nombre_libro']) . "'",
                    "'" . addslashes($tra['email_libro']) . "'",
                    "'" . addslashes($tra['emailconfirmacion_libro']) . "'",
                    $tra['paginainicial_libro'],
                    $tra['paginafinal_libro'],
                    "'" . $tra['incluirrotulado_libro'] . "'",
                    "'" . $tra['libeleanot_libro'] . "'",
                    "'" . $tra['libeleanot_registro'] . "'",
                    "'" . $tra['libeleanot_dupli'] . "'",
                    "'" . $tra['libeleanot_nroacta'] . "'",
                    "'" . $tra['libeleanot_fechaacta'] . "'",
                    intval($tra['libeleanot_nropaginas']),
                    "'" . $tra['libeleanot_fechainiinscripciones'] . "'",
                    "'" . $tra['libeleanot_fechafininscripciones'] . "'",
                    intval($tra['libeleanot_nroregistros']),
                    "'" . $tra['actanro_libro'] . "'",
                    "'" . $tra['fechaacta_libro'] . "'",
                    "'" . $tra['horaacta_libro'] . "'",
                    "'" . $tra['fechaini_libroelectronico'] . "'",
                    "'" . $tra['fechafin_libroelectronico'] . "'",
                    "'" . $tra['fechainiinscripciones_libroelectronico'] . "'",
                    "'" . $tra['fechafininscripciones_libroelectronico'] . "'",
                    "'" . addslashes($tra['embargo']) . "'",
                    "'" . $tra['tipoideembargante'] . "'",
                    "'" . $tra['ideembargante'] . "'",
                    "'" . addslashes($tra['nom1embargante']) . "'",
                    "'" . addslashes($tra['nom2embargante']) . "'",
                    "'" . addslashes($tra['ape1embargante']) . "'",
                    "'" . addslashes($tra['ape2embargante']) . "'",
                    "'" . addslashes($tra['desembargo']) . "'",
                    "'" . $tra['librocruce'] . "'",
                    "'" . $tra['inscripcioncruce'] . "'",
                    "'" . addslashes($tra['entidadvigilancia']) . "'",
                    "'" . addslashes($tra['objetosocial']) . "'",
                    "'" . addslashes($tra['facultades']) . "'",
                    "'" . addslashes($tra['limitaciones']) . "'",
                    "'" . addslashes($tra['poderespecial']) . "'",
                    "'" . addslashes($tra['texto']) . "'",
                    intval($tra["cantidad"])
                );

                $res = insertarRegistrosMysqli2($dbx, 'mreg_liquidacion_transacciones', $arrCampos, $arrValores);
                if ($res === false) {
                    $retornar = false;
                    $mensajeRetornar = $_SESSION["generales"]["mensajeerror"];
                }
            }
        }

        $_SESSION["generales"]["mensajeerror"] = $mensajeRetornar;
        return $retornar;
    }

    public static function grabarPathAnexoRadicacion($dbx, $idanexo = '', $path = '') {
        $arrCampos = array(
            'path'
        );
        $arrValores = array(
            "'" . $path . "'"
        );
        $condicion = "idanexo = " . $idanexo;
        regrabarRegistrosMysqli2($dbx, 'mreg_radicacionesanexos', $arrCampos, $arrValores, $condicion);
    }

    public static function generarEmailNotificacionInscripcionSiprefSii($mysqli, $t) {
        $tiporeg = '';
        switch (substr($t["libro"], 0, 2)) {
            case "RM" :
            case "ME" :
                $tiporeg = 'REGISTRO PUBLICO MERCANTIL';
                if (substr($t["libro"], 2, 2) == '22') {
                    $tiporeg = 'REGISTRO DE COMERCIANTES QUE EJERCEN LA ACTIVIDAD DE APUESTAS Y JUEGOS DE AZAR';
                }
                break;
            case "RE" :
            case "ES" :
                $tiporeg = 'REGISTRO DE ENTIDADES SIN ANIMO DE LUCRO';
                if (substr($t["libro"], 2, 2) == '53') {
                    $tiporeg = 'REGISTRO DE LA ECONOMIA SOLIDARIA';
                }
                if (substr($t["libro"], 2, 2) == '54') {
                    $tiporeg = 'REGISTRO DE VEEDURIAS';
                }
                if (substr($t["libro"], 2, 2) == '55') {
                    $tiporeg = 'REGISTRO DE ENTIDADES SIN ANIMO DE LUCRO DE CAPITAL PRIVADO EXTRANJERO';
                }
                break;
        }

        //
        $msg = '';
        $msg .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . mostrarFechaSii2($t["fecharegistro"]) . ' a las ' . mostrarHoraSii2($t["horaregistro"]) . ' ';
        $msg .= 'fue inscrito en el ' . $tiporeg . ', en el libro ' . substr($t["libro"], 2, 2) . ' bajo el numero ' . $t["registro"] . ' la siguiente actuacion: <br><br>';

        if (trim($t["recibo"]) != '') {
            $msg .= 'Recibo de Caja No. ' . $t["recibo"] . '<br>';
        }
        if (trim($t["numerooperacion"]) != '') {
            $msg .= 'Numero Operacion: ' . $t["numerooperacion"] . '<br>';
        }
        if (ltrim($t["matricula"], "0") != '') {
            $msg .= 'Matricula: ' . $t["matricula"] . '<br>';
        }
        if (ltrim($t["identificacion"], "0") != '') {
            $msg .= 'Identificacion: ' . $t["identificacion"] . '<br>';
        }
        $msg .= 'Nombre: ' . utf8_decode($t["nombre"]) . '<br>';

        //
        $txtx = '';
        if (substr($t["libro"], 0, 2) == 'RM' || substr($t["libro"], 0, 2) == 'ME') {
            $txtx = 'Acto: ' . retornarRegistroMysqli2($mysqli, 'mreg_actos', "idlibro='" . $t["libro"] . "' and idacto='" . $t["acto"] . "'", "nombre");
        }
        if (substr($t["libro"], 0, 2) == 'RE' || substr($t["libro"], 0, 2) == 'ES') {
            $txtx = 'Acto: ' . retornarRegistroMysqli2($mysqli, 'mreg_actos', "idlibro='" . $t["libro"] . "' and idacto='" . $t["acto"] . "'", "nombre");
        }

        //
        $msg .= $txtx . '<br>';

        //
        $msg .= 'En el siguiente enlace podra encontrar el detalle de la inscripcion realizada: ';
        $msg .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregConsultaNoticiaMercantil.php?accion=vernoticia&_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&libro=' . $t["libro"] . '&inscripcion=' . $t["registro"] . '&dupli=' . $t["dupli"] . '">Ver inscripcion</a>';
        $msg .= '<br><br>';
        $msg .= 'Antes de proceder con la solicitud del certificado en la C&aacute;mara de Comercio le recomendamos validar en el siguiente enlace que ';
        $msg .= 'el radicado haya terminado su proceso de digitaci&oacute;n y control de calidad. ';
        $msg .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregConsultaRutaDocumentos.php?accion=traerruta&_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&_recibo=' . $t["recibo"] . '">Verificar</a>';
        $msg .= '<br><br>';
        //
        if (!defined('NOTIFICAR_TELEFONO')) {
            define('NOTIFICAR_TELEFONO', 'NO');
        }
        if (NOTIFICAR_TELEFONO == 'SI') {
            $msg .= 'Para mayores informes por favor comunicarse al numero ';
            $msg .= TELEFONO_ATENCION_USUARIOS . ' en la ciudad de ' . retornarRegistroMysqli2($mysqli, "bas_municipios", "codigomunicipio='" . MUNICIPIO . "'", "ciudad") . '.<br><br>';
        }
        $msg .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' y tiene por objeto informar, ';
        $msg .= 'en cumplimiento a lo contemplado en el Codigo de Procedimiento Administrativo y de lo Contencioso Administrativo.';
        $msg .= '<br><br>';
        $msg .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
        $msg .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
        $msg .= '<br><br>';
        $msg .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';
        return $msg;
    }

    public static function generarFormularioRenovacionRepositorio($dbx, $mat = '') {
        //
        $iForm = 0;
        $iEstab = 0;
        $iPpal = 0;

        //
        $_SESSION["formulario"]["datos"] = array();
        $_SESSION["formulario"]["datoshijos"] = array();
        $_SESSION["formulario"]["matriculashijos"] = array();
        $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];

        //
        $datPrincipal = array();
        $textoFirmadoDatPrincipal = '';
        $tramasha1DatPrincipal = '';

        $arrForms = retornarRegistrosMysqli2($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"], "id", "xml");
        // $arrForms = retornarLiquidacionDatosExpedienteArregloXml($_SESSION["tramite"]["numeroliquidacion"]);
        if (!empty($arrForms)) {
            foreach ($arrForms as $form) {

                $x = \funcionesSii2::desserializarExpedienteMatricula($dbx, $form);

                // 2018-10-17: JINT: Para generarf formato de renovación de afiliacion
                if (defined('TIPO_DOC_FOR_AFILIACION') && TIPO_DOC_FOR_AFILIACION != '') {
                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                        if ($x["organizacion"] == '01' || $x["organizacion"] > '02') {
                            if ($x["afiliado"] == '1') {
                                $datPrincipal = $x;
                            }
                        }
                    }
                }

                if (
                        $x["organizacion"] == '01' ||
                        ($x["organizacion"] > '02' && $x["categoria"] == '1')
                ) {
                    $_SESSION["formulario"]["datos"] = $x;
                    $_SESSION["formulario"]["datos"]["fechaultimamodificacion"] = $_SESSION["tramite"]["fechaultimamodificacion"];
                    $iPpal++;
                } else {
                    $iEstab++;
                    $_SESSION["formulario"]["datoshijos"][$iEstab] = $x;
                    $_SESSION["formulario"]["datoshijos"][$iEstab]["fechaultimamodificacion"] = $_SESSION["tramite"]["fechaultimamodificacion"];
                }
                $iForm++;
            }


            if ($iForm > 0) {

                if ($iPpal > 0) {

                    $det = '';

                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                            $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl'
                    ) {
                        $det = 'FORMULARIO DE RENOVACION - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                    }

                    if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculapjur'
                    ) {
                        $det = 'FORMULARIO DE MATRICULA - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                    }

                    if ($_SESSION["tramite"]["tipotramite"] == 'matriculaesadl'
                    ) {
                        $det = 'FORMULARIO DE INSCRIPCION AL REG-ESADL - NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                    }
                    $name = armarPdfPrincipalNuevo1082Sii($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                    if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                        if (ACTIVAR_CIRCULAR_002_2016 == 'SI1') {
                            $name1 = armarPdfPrincipalNuevoAnosAnteriores1510Sii($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                        } else {
                            $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Sii($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                        }
                        unirPdfs(array('../../../tmp/' . $name, '../../../tmp/' . $name1), '../../tmp/' . $name);
                    }

                    //
                    $bandeja = '';
                    if (substr(ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), 0, 1) == 'S') {
                        $bandeja = '5.-REGESADL';
                    } else {
                        $bandeja = '4.-REGMER';
                    }

                    //
                    $id = \funcionesSii2::grabarAnexoRadicacion(
                                    $dbx, ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), // Codigo barras
                                    trim($_SESSION["tramite"]["numerorecibo"]), // Recibo
                                    trim($_SESSION["tramite"]["numerooperacion"]), // Operacion
                                    ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), // Identificacion
                                    trim($_SESSION["tramite"]["nombrecliente"]), // Nombre
                                    '', // Acreedor
                                    '', // Nombre acreedor
                                    ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                                    '', // N&uacute;mero de proponente
                                    TIPO_DOC_FOR_MERCANTIL, // Tipo de documento
                                    '', // N&uacute;mero del documento
                                    $_SESSION["tramite"]["fecharecibo"], '', // C&oacute;digo de origen
                                    'EL COMERCIANTE', '', // Clasificaci&oacute;n
                                    '', // N&uacute;mero del contrato
                                    '', // Idfuente
                                    1, // versi&oacute;n
                                    '', // Path
                                    '1', // Estado
                                    date("Ymd"), // fecha de escaneo o generaci&oacute;n
                                    $_SESSION["generales"]["codigousuario"], '', // Caja
                                    '', // Libro
                                    $det, '', // Libro del registro
                                    '', // N&uacute;mero del registro
                                    $bandeja, // Bandeja de registro
                                    'S', // Soporte del recibo
                                    '', // identificador
                                    '503' // Tipo anexo
                    );

                    $dirx = date("Ymd");
                    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesSii2::crearIndex($path);
                    }

                    $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                    copy('../../../tmp/' . $name, '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                    unlink('../../tmp/' . $name);
                    \funcionesSii2::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
                }

                if ($iEstab > 0) {

                    foreach ($_SESSION["formulario"]["datoshijos"] as $hijo) {

                        $_SESSION["formulario"]["datos"] = $hijo;
                        $det = '';

                        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                                $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl'
                        ) {
                            $det = 'FORMULARIO DE RENOVACION - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                        }

                        if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                                $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                                $_SESSION["tramite"]["tipotramite"] == 'matriculapjur' ||
                                $_SESSION["tramite"]["tipotramite"] == 'matriculaesadl'
                        ) {
                            $det = 'FORMULARIO DE MATRICULA - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                        }

                        /*
                          if (FECHA_INICIAL_DECRETO1510 <= date("Ymd")) {
                          $name = armarPdfEstablecimientoNuevo1510($_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                          } else {
                          $name = armarPdfEstablecimientoNuevo($_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                          }
                         */
                        if (ACTIVAR_CIRCULAR_002_2016 == 'SI1') {
                            $name = armarPdfEstablecimientoNuevo1510Sii($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                        } else {
                            $name = armarPdfEstablecimientoNuevo1082Sii($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                        }

                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {

                            /*
                              if (FECHA_INICIAL_DECRETO1510 <= date("Ymd")) {
                              $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1510($_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                              } else {
                              $name1 = armarPdfEstablecimientoNuevoAnosAnteriores($_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                              }
                             */
                            if (ACTIVAR_CIRCULAR_002_2016 == 'SI1') {
                                $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1510Sii($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                            } else {
                                $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1082Sii($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                            }



                            unirPdfs(array('../../../tmp/' . $name, '../../../tmp/' . $name1), '../../tmp/' . $name);
                        }


                        //
                        $bandeja = '';
                        if (substr(ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), 0, 1) == 'S') {
                            $bandeja = '5.-REGESADL';
                        } else {
                            $bandeja = '4.-REGMER';
                        }

                        $id = \funcionesSii2::grabarAnexoRadicacion(
                                        $dbx, ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), trim($_SESSION["tramite"]["numerorecibo"]), trim($_SESSION["tramite"]["numerooperacion"]), ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), trim($_SESSION["tramite"]["nombrecliente"]), '', // Acreedor
                                        '', // Nombre acreedor
                                        ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                                        '', // N&uacute;mero de proponente
                                        TIPO_DOC_FOR_MERCANTIL, // Tipo de documento
                                        '', // N&uacute;mero del documento
                                        $_SESSION["tramite"]["fecharecibo"], '', // C&oacute;digo de origen
                                        'EL COMERCIANTE', '', // Clasificaci&oacute;n
                                        '', // N&uacute;mero del contrato
                                        '', // Idfuente
                                        1, // versi&oacute;n
                                        '', // Path
                                        '1', // Estado
                                        date("Ymd"), // fecha de escaneo o generaci&oacute;n
                                        $_SESSION["generales"]["codigousuario"], '', // Caja
                                        '', // Libro
                                        $det, '', // Libro del registro
                                        '', // N&uacute;mero del registro
                                        $bandeja, // Bandeja de registro
                                        'S', // Soporte del recibo
                                        '', // Identificador
                                        '503' // Tipo de anexo
                        );

                        $dirx = date("Ymd");
                        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                        if (!is_dir($path) || !is_readable($path)) {
                            mkdir($path, 0777);
                            \funcionesSii2::crearIndex($path);
                        }

                        $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                        copy('../../../tmp/' . $name, '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                        unlink('../../../tmp/' . $name);
                        \funcionesSii2::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
                    }
                }
            }
            unset($arrForms);
            unset($form);

            //
            if (!empty($datPrincipal)) {
                $bandeja = '4.-REGMER';
                $_SESSION["formulario"]["datos"] = $datPrincipal;
                $_SESSION["formulario"]["numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
                $name = armarPdfFormatoAfiliacionSii($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '', '');
                $det = 'Formato de renovación de afiliación matrícula No. ' . $datPrincipal["matricula"];
                $id = \funcionesSii2::grabarAnexoRadicacion(
                                $dbx, ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), // COdigo barras
                                trim($_SESSION["tramite"]["numerorecibo"]), // Recibo
                                trim($_SESSION["tramite"]["numerooperacion"]), // Operacion
                                ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), // identificacion
                                trim($_SESSION["tramite"]["nombrecliente"]), // Nombre
                                '', // Acreedor
                                '', // Nombre acreedor
                                ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // Numero de matricula
                                '', // N&uacute;mero de proponente
                                TIPO_DOC_FOR_AFILIACION, // Tipo de documento
                                '', // Numero del documento
                                $_SESSION["tramite"]["fecharecibo"], // fecha del recibo
                                '', // Codigo de origen
                                'EL COMERCIANTE', '', // Clasificacion
                                '', // Numero del contrato
                                '', // Idfuente
                                1, // version
                                '', // Path
                                '1', // Estado
                                date("Ymd"), // fecha de escaneo o generacion
                                $_SESSION["generales"]["codigousuario"], // Usuario que genera
                                '', // Caja
                                '', // Libro
                                $det, // Detalle
                                '', // Libro del registro
                                '', // Numero del registro
                                $bandeja, // Bandeja de registro
                                'S', // Soporte del recibo
                                '', // Identificador
                                '503' // Tipo de anexo
                );

                $dirx = date("Ymd");
                $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                if (!is_dir($path) || !is_readable($path)) {
                    mkdir($path, 0777);
                    \funcionesSii2::crearIndex($path);
                }

                $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                copy('../../../tmp/' . $name, '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                unlink('../../../tmp/' . $name);
                \funcionesSii2::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
            }
        }
    }

    public static function generarReciboCajaRepositorio($dbx, $mat, $pro, $arreglo = array(), $tiporegistro = 'RegMer', $genrec = '') {
        require_once ('genPdfsGenerales.php');
        if (!isset($_SESSION["tramite"]["claveprepago"])) {
            $_SESSION["tramite"]["claveprepago"] = '';
        }
        if (!isset($_SESSION["tramite"]["saldoprepago"])) {
            $_SESSION["tramite"]["saldoprepago"] = 0;
        }

        // echo "entra a armar pdf del recibo<br>";
        $name = armarPdfReciboSii($dbx, $_SESSION["tramite"]["idliquidacion"], 'T', 'SI', $arreglo, $_SESSION["tramite"]["claveprepago"], $_SESSION["tramite"]["saldoprepago"], $genrec);
        // echo "armo pdf del recibo<br>";

        $tipodoc = '';
        $bandeja = '';
        switch ($tiporegistro) {
            case "RegMer": {
                    $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                    $bandeja = '4.-REGMER';
                    break;
                }
            case "LibCom":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "CerEle":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "CerVirt":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "InscDoc":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "InscDocRegMer":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "InscDocEsadl":
                $tipodoc = TIPO_DOC_REC_CAJA_ESADL;
                $bandeja = '5.-REGESADL';
                break;
            case "crm":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "RegEsadl":
                $tipodoc = TIPO_DOC_REC_CAJA_ESADL;
                $bandeja = '5.-REGESADL';
                break;
            case "RegPro":
                $tipodoc = TIPO_DOC_REC_CAJA_PROPONENTES;
                $bandeja = '6.-REGPRO';
                break;
            case "PrePag":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            default :
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
        }

        $id = \funcionesSii2::grabarAnexoRadicacion(
                        $dbx, // COnexion
                        ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), // NumRad
                        trim($_SESSION["tramite"]["numerorecibo"]), // NumRecibo
                        trim($_SESSION["tramite"]["numerooperacion"]), // NumOpe
                        ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), // IdeCliente
                        trim($_SESSION["tramite"]["nombrecliente"]), // NomCliente
                        '', // Acreedor
                        '', // Nombre acreedor
                        ltrim($mat, "0"), // mat
                        ltrim($pro, "0"), // Prop
                        $tipodoc, // Tipo de documento
                        '', // N&uacute;mero del documento
                        $_SESSION["tramite"]["fecharecibo"], // fechaRecibo
                        '', // C&oacute;digo de origen
                        'CAJA DE LA CAMARA DE COMERCIO', // Txtorigen
                        '', // Clasificaci&oacute;n
                        '', // N&uacute;mero del contrato
                        '', // Idfuente
                        1, // versi&oacute;n
                        '', // Path
                        '1', // Estado
                        date("Ymd"), // fecha de escaneo o generaci&oacute;n
                        $_SESSION["generales"]["codigousuario"], // Usuario
                        '', // Caja
                        '', // Libro
                        'RECIBO DE CAJA No. ' . $_SESSION["tramite"]["numerorecibo"], // Detalle
                        '', // Libro de comercio
                        '', // N&uacute;mero de registros en libro
                        $bandeja, // Bandeja de registro
                        'S', // Soporte del recibo de caja
                        '', // Identificador
                        '509' // Tipo anexo	
        );

        // Traslada la imagen tif del sello al repositorio
        // $tam = filesize($name);

        $dirx = date("Ymd");
        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            \funcionesSii2::crearIndex($path);
        }

        $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
        copy($name, '../../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
        unlink($name);
        \funcionesSii2::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
    }

    public static function generarSecuenciaCodigoBarras($dbx) {
        $nameLog = 'generarSecuenciaCodigoBarras_' . date("Ymd");
        //
        $cb = 0;
        $cb = \funcionesSii2::retornarMregSecuencia($dbx, 'CODIGOS-BARRAS');
        if ($cb == 0) {
            log::general2($nameLog, '', 'Error recuperando secuencia codigo de barras : ' . $_SESSION["generales"]["mensajeerror"]);
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar el ultimo codigo de barras asignado';
            return false;
        }

        // ************************************************************************************************ //
        // Revisa que el codigo de barras no este previamente creado
        // ************************************************************************************************ //
        $seguir = "si";
        while ($seguir == 'si') {
            $cb++;
            if (contarRegistrosMysqli2($dbx, 'mreg_est_codigosbarras', "codigobarras='" . $cb . "'") == 0) {
                $seguir = 'no';
            }
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        \funcionesSii2::actualizarMregSecuenciasSii($dbx, 'CODIGOS-BARRAS', $cb);

        // ************************************************************************************************ //
        // Arma los arreglos para grabar el recibo
        // ************************************************************************************************ //

        $arrCampos = array(
            'codigobarras',
            'operacion',
            'recibo',
            'fecharadicacion',
            'matricula',
            'proponente',
            'idclase',
            'numid',
            'numdocextenso',
            'nombre',
            'estadofinal',
            'operadorfinal',
            'fechaestadofinal',
            'horaestadofinal',
            'sucursalfinal',
            'activos',
            'liquidacion',
            'reliquidacion',
            'actoreparto',
            'tipdoc',
            'numdoc',
            'oridoc',
            'mundoc',
            'fecdoc',
            'detalle',
            'canins',
            'candoc',
            'canfor',
            'cananx1',
            'cananx2',
            'cananx3',
            'cananx4',
            'cananx5',
            'sucursalradicacion',
            'tiprut',
            'numcaja',
            'escaneocompleto',
            'clavefirmado'
        );

        //
        $arrValores = array(
            "'" . $cb . "'",
            "''", // operacion
            "''", // recibo
            "'" . date("Ymd") . "'",
            "''", // matricula
            "''", // proponente
            "''", // idclase
            "''", // numid
            "''", // numdocextenso
            "''", // nombre
            "'" . '01' . "'",
            "''", // operadorfinal
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "''", // sucursalfinal
            0, // activos
            0, // liquidacion
            "''", // reliquidacion
            "''", // actoreparto
            "''", // tipdoc
            "''", // numdoc
            "''", // oridoc
            "''", // mundoc
            "''", // fecdoc
            "''", // detalle
            0, // canins
            0, // candoc
            0, // canfor
            0, // cananx1
            0, // cananx2
            0, // cananx3
            0, // cananx4
            0, // cananx5
            "''", // sucursal radicacion
            "''", // tiprut
            "''", // numcaja
            "''", // escaneo completo
            "''" // clave firmado
        );

        //
        $res = insertarRegistrosMysqli2($dbx, 'mreg_est_codigosbarras', $arrCampos, $arrValores);
        if ($res === false) {
            log::general2($nameLog, '', 'Error creando codigo de barras : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }
        $detalle = 'Creo codigo de barras No. ' . $cb . ', estado final: 01';
        actualizarLogMysqli2($dbx, '069', $_SESSION["generales"]["codigousuario"], 'funcionesSii2.php', '', '', '', $detalle, '', '');

        return $cb;
    }

    public static function generarSecuenciaCodigoBarrasPowerFile($dbx) {
        $cb = false;
        $headers = array(
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        );
        $data = array('pmhost' => 'http://bpm.cccucuta.org.co',
            'workspace' => 'cccucuta',
            'clientId' => 'NZPXVTXFOLFZTOGQVGGYUDXGCGLLRFQV',
            'clientSecret' => '13653564459a7194df19bb2021547438',
            'username' => 'confecamara',
            'password' => '123456789'
        );

        $fields = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://bpm.cccucuta.org.co/syscccucuta/en/neoclassic/NewUser/services/loginUserService.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        $resultado = json_decode($result, true);
        $access_token = $resultado['access_token'];
        $expires_in = $resultado['expires_in'];
        $token_type = $resultado['token_type'];
        $scope = $resultado['scope'];
        $refresh_token = $resultado['refresh_token'];

        //echo $access_token;
        //Validacion de Autenticacion
        if ($access_token != '') {
            $header = array(
                'function: newCaseTrigger',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'taskId: 130462646598ddd080ea780009585776',
                'userId: 85714721659a72800144f89026507541',
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://bpm.cccucuta.org.co/syscccucuta/en/neoclassic/NewUser/services/powerfileService.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $results = curl_exec($ch);
            curl_close($ch);
            $resultado = json_decode($results, true);
            if (isset($resultado["txt_NumRad"])) {
                $cb = ltrim(trim($resultado["txt_NumRad"]), "0");
            }
        }

        // ************************************************************************************************ //
        // Arma los arreglos para grabar el recibo
        // ************************************************************************************************ //

        if ($cb && $cb != '') {
            $arrCampos = array(
                'codigobarras',
                'operacion',
                'recibo',
                'fecharadicacion',
                'matricula',
                'proponente',
                'idclase',
                'numid',
                'numdocextenso',
                'nombre',
                'estadofinal',
                'operadorfinal',
                'fechaestadofinal',
                'horaestadofinal',
                'sucursalfinal',
                'activos',
                'liquidacion',
                'reliquidacion',
                'actoreparto',
                'tipdoc',
                'numdoc',
                'oridoc',
                'mundoc',
                'fecdoc',
                'detalle',
                'canins',
                'candoc',
                'canfor',
                'cananx1',
                'cananx2',
                'cananx3',
                'cananx4',
                'cananx5',
                'sucursalradicacion',
                'tiprut',
                'numcaja',
                'escaneocompleto',
                'clavefirmado'
            );

            //
            $arrValores = array(
                "'" . $cb . "'",
                "''", // operacion
                "''", // recibo
                "'" . date("Ymd") . "'",
                "''", // matricula
                "''", // proponente
                "''", // idclase
                "''", // numid
                "''", // numdocextenso
                "''", // nombre
                "'" . '01' . "'",
                "''", // operadorfinal
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "''", // sucursalfinal
                0, // activos
                0, // liquidacion
                "''", // reliquidacion
                "''", // actoreparto
                "''", // tipdoc
                "''", // numdoc
                "''", // oridoc
                "''", // mundoc
                "''", // fecdoc
                "''", // detalle
                0, // canins
                0, // candoc
                0, // canfor
                0, // cananx1
                0, // cananx2
                0, // cananx3
                0, // cananx4
                0, // cananx5
                "''", // sucursal radicacion
                "''", // tiprut
                "''", // numcaja
                "''", // escaneo completo
                "''" // clave firmado
            );

            //
            $res = insertarRegistrosMysqli2($dbx, 'mreg_est_codigosbarras', $arrCampos, $arrValores);
            if ($res === false) {
                return false;
            }
            $detalle = 'Creo codigo de barras No. ' . $cb . ', estado final: 01';
            actualizarLogMysqli2($dbx, '069', $_SESSION["generales"]["codigousuario"], 'funcionesSii2.php', '', '', '', $detalle, '', '');
        }

        return $cb;
    }

    public static function generarSecuenciaLibros($dbx, $libro = '') {
        if (strlen($libro) == 2) {
            if ($libro < '50') {
                $libro = 'RM' . $libro;
            } else {
                $libro = 'RE' . $libro;
            }
        }

        //
        $ins = 0;
        $ins = retornarMregSecuencia('LIBRO-' . $libro);
        if ($ins == 0) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar la ultima inscripcion para el libro' . $libro;
            return false;
        }

        // ************************************************************************************************ //
        // Revisa que la inscripción no haya sido previamente asignada
        // ************************************************************************************************ //
        $seguir = "si";
        while ($seguir == 'si') {
            $ins++;
            if (contarRegistros('mreg_est_inscripciones', "libro='" . $libro . "' and registro='" . $ins . "'") == 0) {
                $seguir = 'no';
            }
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        actualizarMregSecuencias('LIBRO-' . $libro, $ins);

        // ************************************************************************************************ //
        // Arma los arreglos para grabar el recibo
        // ************************************************************************************************ //

        $arrCampos = array(
            'libro',
            'registro',
            'dupli'
        );

        //
        $arrValores = array(
            "'" . $libro . "'",
            "'" . $ins . "'",
            "'1'"
        );

        //
        $res = insertarRegistros('mreg_est_inscripciones', $arrCampos, $arrValores);
        if ($res === false) {
            return false;
        }

        return $ins;
    }

    public static function generarSecuenciaMatriculaSii($dbx, $tipomat = '') {
        $nameLog = 'generarSecuenciaMatriculaSii_' . date("Ymd");

        //
        $mat = 0;
        $mat = \funcionesSii2::retornarMregSecuenciaSii($dbx, $tipomat);

        if ($mat == 0) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar la ultima matricula asignada';
            return false;
        }

        // ************************************************************************************************ //
        // Revisa que la inscripción no haya sido previamente asignada
        // ************************************************************************************************ //
        $seguir = "si";
        while ($seguir == 'si') {
            $mat++;
            if ($tipomat == 'MATREGMER') {
                $xMat = ltrim($mat, "0");
            }
            if ($tipomat == 'MATESADL') {
                $xMat = "S" . sprintf("%07s", $mat);
            }
            if ($tipomat == 'MATCIVIL') {
                $xMat = "N" . sprintf("%07s", $mat);
            }

            // $tx = retornarRegistro ('mreg_est_inscritos',"matricula='" . $xMat . "'");
            if (contarRegistrosMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . $xMat . "'") == 0) {
                $seguir = 'no';
            }
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        \funcionesSii2::actualizarMregSecuenciasSii($dbx, $tipomat, $mat);

        //
        $arrCampos = array(
            'matricula',
            'ctrestmatricula',
            'ctrestdatos',
            'origendocconst',
            'fecactualizacion',
            'fecsincronizacion',
            'horsincronizacion'
        );
        $arrValores = array(
            "'" . $xMat . "'",
            "'AS'",
            "'3'",
            "''",
            "''",
            "''",
            "''"
        );
        unset($_SESSION["expedienteactual"]);
        $res = insertarRegistrosMysqli2($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores);
        if ($res === false) {
            \logSii2::general2($nameLog, '', 'Error asignando numero de matricula : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        return $xMat;
    }

    public static function generarSecuenciaOperacion($dbx, $usuario, $fecha, $cajero = '', $sedex = '') {
        $sec = 0;
        $sec1 = 0;
        $sede = $sedex;

        // ************************************************************************************************ //
        // Si el usuario es INTERNET o USUPUBXX
        // ************************************************************************************************ //
        if ($cajero == 'INTERNET' || $cajero == 'USUPUBXX') {
            $sede = '99';
        }

        // ************************************************************************************************ //
        // Si el usuario es INTERNET o USUPUBXX
        // ************************************************************************************************ //    
        if ($cajero == 'RUE') {
            $sede = '90';
        }

        // ************************************************************************************************ //
        // Si el usuario es diferente de INTERNET, USUPUBXX, RUE
        // ************************************************************************************************ //    
        if ($cajero != 'INTERNET' && $cajero != 'USUPUBXX' && $cajero != 'RUE') {

            // ************************************************************************************************ //
            // Localiza el usuario en la tabla de usuarios
            // ************************************************************************************************ //
            $arrTem = retornarRegistroMysqli2($dbx, "usuarios", "idusuario='" . $cajero . "'");

            // ************************************************************************************************ //
            // Si el usuario no existe
            // ************************************************************************************************ //
            if ($arrTem === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Usuario-cajero no localizado en la tabla de usuarios';
                return false;
            }

            // ************************************************************************************************ //
            // Si el usuario esta inactivo
            // ************************************************************************************************ //
            if ($arrTem["fechainactivacion"] != '' && $arrTem["fechainactivacion"] != '00000000') {
                $_SESSION["generales"]["mensajeerror"] = 'Usuario-cajero esta inactivo';
                return false;
            }

            // ************************************************************************************************ //
            // Si el usuario no ha sido activado
            // ************************************************************************************************ //
            if ($arrTem["fechaactivacion"] == '' || $arrTem["fechaactivacion"] == '00000000') {
                $_SESSION["generales"]["mensajeerror"] = 'Usuario-cajero no ha sido activado';
                return false;
            }

            // ************************************************************************************************ //
            // Si el usuario no ha sido activado
            // ************************************************************************************************ //
            if ($arrTem["escajero"] != 'SI') {
                $_SESSION["generales"]["mensajeerror"] = 'Usuario-cajero no es tipo cajero';
                return false;
            }

            // ************************************************************************************************ //
            // Asigna la sede al número de operación.
            // Solo si esta no fue pasada como parámetro desde la liquidación.
            // ************************************************************************************************ //    
            if ($sede == '') {
                $sede = $arrTem["idsede"];
                if (trim($sede) == '') {
                    $sede = '01';
                }
            }
        }

        // ************************************************************************************************ //
        // Calcula la secuencia de la operacion
        // ************************************************************************************************ //
        $contar = contarRegistrosMysqli2($dbx, 'mreg_controlusuarios', "usuario like '" . $cajero . "' and fecha like '" . $fecha . "'");
        if ($contar === false) {
            return false;
        }

        if ($contar == 0) {
            $arrCampos = array(
                'usuario',
                'fecha',
                'secuencia'
            );
            $arrValores = array(
                "'" . $usuario . "'",
                "'" . $fecha . "'",
                1
            );
            insertarRegistrosMysqli2($dbx, 'mreg_controlusuarios', $arrCampos, $arrValores);
            $sec1 = 1;
        } else {
            $arrTem = retornarRegistroMysqli2($dbx, 'mreg_controlusuarios', "usuario like '" . $cajero . "' and fecha like '" . $fecha . "'");
            $sec = $arrTem["secuencia"] + 1;
            $sec1 = $sec;
            $arrCampos = array(
                'secuencia'
            );
            $arrValores = array(
                $sec
            );
            regrabarRegistrosMysqli2($dbx, 'mreg_controlusuarios', $arrCampos, $arrValores, "usuario like '" . $cajero . "' and fecha like '" . $fecha . "'");
        }

        // ************************************************************************************************ //
        // Retorna el numero de operacion
        // 2016-07-30 : JINT
        // Si hay integración con SIREP, retornar el número de operación a 12 dígitos
        // Si no hay integración con SIREP retorna el número de operación a 25 digitos
        // ************************************************************************************************ //
        return $sede . '-' . trim($cajero) . '-' . $fecha . '-' . sprintf("%04s", $sec1);
    }

    public static function generarSecuenciaReciboNuevaSii($mysqli, $tipo = 'S', $fps = array(), $operacion = '', $fecha = '', $hora = '', $codbarras = '', $tiporegistro = '', $identificacion = '', $nombre = '', $organizacion = '', $categoria = '', $idtipodoc = '', $numdoc = '', $origendoc = '', $fechadoc = '') {

        // ******************************************************************************* //
        // validas que todos los datos requeridos se pasen
        // ******************************************************************************* //
        $errores = array();
        if (!empty($fps)) {
            $jx = 0;
            foreach ($fps as $d) {
                $jx++;
                if (!isset($d["tipo"])) {
                    $errores[] = 'FPago ' . $jx . ' . Tipo no reportado';
                }
                if (!isset($d["valor"])) {
                    $errores[] = 'FPago ' . $jx . ' . Valor no reportado';
                }
                if (!isset($d["banco"])) {
                    $errores[] = 'FPago ' . $jx . ' . Banco no reportado';
                }
                if (!isset($d["cheque"])) {
                    $errores[] = 'FPago ' . $jx . ' . Cheque no reportado';
                }
            }
        }

        if (!empty($errores)) {
            $_SESSION["generales"]["mensajeerror"] = '';
            foreach ($errores as $e) {
                $_SESSION["generales"]["mensajeerror"] .= $e . " ** ";
            }
            return false;
        }

        //
        $rec = 0;
        $recx = '';

        // ************************************************************************************************ //
        // Localiza el numero del recibo a generar dependiendo del tipo de documento
        // ************************************************************************************************ //
        if ($tipo == 'S') { // Si son recibos normales
            $tclave = 'RECIBOS-NORMALES';
            $rec = \funcionesSii2::retornarMregSecuenciaMaxAsentarReciboSii($mysqli, $tclave);
        }
        if ($tipo == 'M') { // Si son notas de reversion
            $tclave = 'RECIBOS-NOTAS';
            $rec = \funcionesSii2::retornarMregSecuenciaMaxAsentarReciboSii($mysqli, $tclave);
        }
        if ($tipo == 'H') { // Si son gastos administrativos
            $tclave = 'RECIBOS-GA';
            $rec = \funcionesSii2::retornarMregSecuenciaMaxAsentarReciboSii($mysqli, $tclave);
        }
        if ($tipo == 'D') { // Si son consultas
            $tclave = 'RECIBOS-CO';
            $rec = \funcionesSii2::retornarMregSecuenciaMaxAsentarReciboSii($mysqli, $tclave);
        }
        if ($rec === false) {
            \logSii2::general2('reportarPago_' . date("Ymd"), '', 'Error recuperando la secuencia del recibo de caja : ' . $_SESSION["generales"]["mensajeerror"]);
            $_SESSION["generales"]["mensajeerror"] = 'Error recuperando la secuencia del recibo de caja';
            return false;
        } else {
            \logSii2::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Secuencia generada : ' . $rec);
        }

        // ************************************************************************************************ //
        if ($rec == '') {
            $rec = 0;
        } else {
            $rec = intval($rec);
        }

        // ************************************************************************************************ //
        // Revisa que el recibo no esta creado previamente, de ser asi, genera un nuevo numero
        // ************************************************************************************************ //
        $seguir = "si";
        while ($seguir == 'si') {
            $rec++;
            $recx = $tipo . sprintf("%09s", $rec);
            if (contarRegistrosMysqli2($mysqli, 'mreg_recibosgenerados', "recibo='" . $recx . "'") == 0) {
                $seguir = 'no';
            }
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        \funcionesSii2::actualizarMregSecuenciasAsentarReciboSii($mysqli, $tclave, $rec);

        // ************************************************************************************************ //
        // Arma los arreglos para grabar el recibo
        // ************************************************************************************************ //

        $arrCampos = array(
            'recibo',
            'operacion',
            'factura',
            'codigobarras',
            'fecha',
            'hora',
            'usuario',
            'tipogasto',
            'tipoidentificacion',
            'identificacion',
            'razonsocial',
            'nombre1',
            'nombre2',
            'apellido1',
            'apellido2',
            'direccion',
            'municipio',
            'telefono1',
            'telefono2',
            'email',
            'idliquidacion',
            'tipotramite',
            'valorneto',
            'pagoprepago',
            'pagoafiliado',
            'pagoefectivo',
            'pagocheque',
            'pagoconsignacion',
            'pagopseach',
            'pagovisa',
            'pagomastercard',
            'pagocredencial',
            'pagoamerican',
            'pagodiners',
            'pagotdebito',
            'numeroautorizacion',
            'cheque',
            'franquicia',
            'nombrefranquicia',
            'codbanco',
            'nombrebanco',
            'alertaid',
            'alertaservicio',
            'alertavalor',
            'proyectocaja',
            'numerounicorue',
            'numerointernorue',
            'tipotramiterue',
            'idformapago',
            'estado',
            'estadoemail',
            'estadosms'
        );

        $_SESSION["tramite"]["tipotramiterue"] = '';
        if (substr($_SESSION["tramite"]["tipotramite"], 0, 4) == 'rues') {
            $_SESSION["tramite"]["tipotramiterue"] = substr($_SESSION["tramite"]["tipotramite"], 6);
        }

        //
        $arrValores = array(
            "'" . $recx . "'",
            "'" . $operacion . "'",
            "'" . $_SESSION["tramite"]["numerofactura"] . "'",
            "'" . $codbarras . "'",
            "'" . $fecha . "'",
            "'" . $hora . "'",
            "'" . $_SESSION["generales"]["cajero"] . "'",
            "'" . $_SESSION["tramite"]["tipogasto"] . "'",
            "'" . $_SESSION["tramite"]["tipoidentificacionpagador"] . "'",
            "'" . ltrim($_SESSION["tramite"]["identificacionpagador"], "0") . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombrepagador"] . ' ' . $_SESSION["tramite"]["apellidopagador"])) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombre1pagador"])) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["nombre2pagador"])) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["apellido1pagador"])) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["apellido2pagador"])) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["direccionpagador"])) . "'",
            "'" . trim($_SESSION["tramite"]["municipiopagador"]) . "'",
            "'" . trim($_SESSION["tramite"]["telefonopagador"]) . "'",
            "'" . trim($_SESSION["tramite"]["movilpagador"]) . "'",
            "'" . addslashes(trim($_SESSION["tramite"]["emailpagador"])) . "'",
            $_SESSION["tramite"]["numeroliquidacion"],
            "'" . trim($_SESSION["tramite"]["tipotramite"]) . "'",
            $_SESSION["tramite"]["valortotal"],
            $_SESSION["tramite"]["pagoprepago"],
            $_SESSION["tramite"]["pagoafiliado"],
            $_SESSION["tramite"]["pagoefectivo"],
            $_SESSION["tramite"]["pagocheque"],
            $_SESSION["tramite"]["pagoconsignacion"],
            $_SESSION["tramite"]["pagoach"],
            $_SESSION["tramite"]["pagovisa"],
            $_SESSION["tramite"]["pagomastercard"],
            $_SESSION["tramite"]["pagocredencial"],
            $_SESSION["tramite"]["pagoamerican"],
            $_SESSION["tramite"]["pagodiners"],
            $_SESSION["tramite"]["pagotdebito"],
            "'" . $_SESSION["tramite"]["numeroautorizacion"] . "'",
            "'" . $_SESSION["tramite"]["numerocheque"] . "'",
            "'" . $_SESSION["tramite"]["idfranquicia"] . "'",
            "'" . $_SESSION["tramite"]["nombrefranquicia"] . "'",
            "'" . $_SESSION["tramite"]["idcodban"] . "'",
            "''", // Nombre banco
            $_SESSION["tramite"]["alertaid"],
            "'" . $_SESSION["tramite"]["alertaservicio"] . "'",
            $_SESSION["tramite"]["alertavalor"],
            "'" . $_SESSION["tramite"]["proyectocaja"] . "'",
            "'" . $_SESSION["tramite"]["rues_numerounico"] . "'",
            "'" . $_SESSION["tramite"]["rues_numerointerno"] . "'",
            "'" . $_SESSION["tramite"]["tipotramiterue"] . "'",
            "'" . $_SESSION["tramite"]["idformapago"] . "'",
            "'01'",
            "'0'",
            "'0'"
        );

        //
        $res = insertarRegistrosMysqli2($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores);
        if ($res === false) {
            \logSii2::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        // 2020-01-09: JINT
        $sitcredito = 'no';
        if ($_SESSION["tramite"]["pagovisa"] != 0 ||
                $_SESSION["tramite"]["pagomastercard"] != 0 ||
                $_SESSION["tramite"]["pagocredencial"] != 0 ||
                $_SESSION["tramite"]["pagoamerican"] != 0 ||
                $_SESSION["tramite"]["pagodiners"] != 0) {
            $sitcredito = 'si';
        }

        // ************************************************************************************************ //
        // Arma el detalle del recibo y lo graba
        // ************************************************************************************************ //
        $arrCampos = array(
            'recibo',
            'secuencia',
            'fecha',
            'idservicio',
            // 'cc',
            'matricula',
            'proponente',
            // 'tipogasto',
            'ano',
            'cantidad',
            'valorbase',
            'porcentaje',
            'valorservicio',
            'identificacion',
            'razonsocial',
            'organizacion',
            'categoria',
            'idtipodoc',
            'numdoc',
            'origendoc',
            'fechadoc',
            'expedienteafectado',
            'fecharenovacionaplicable'
        );
        $sec = 0;
        foreach ($_SESSION["tramite"]["liquidacion"] as $d) {
            $matx = '';
            $prox = '';
            if (!isset($d["expediente"])) {
                $d["expediente"] = '';
            }
            if (!isset($d["idservicio"])) {
                $d["idservicio"] = '';
            }
            if (!isset($d["ano"])) {
                $d["ano"] = '';
            }
            if (!isset($d["cantidad"])) {
                $d["cantidad"] = 0;
            }
            if (!isset($d["valorbase"])) {
                $d["valorbase"] = 0;
            }
            if (!isset($d["porcentaje"])) {
                $d["porcentaje"] = 0;
            }
            if (!isset($d["valorservicio"])) {
                $d["valorservicio"] = 0;
            }
            if (!isset($d["expedienteafiliado"])) {
                $d["expedienteafiliado"] = '';
            }
            if (!isset($d["ccos"])) {
                $d["ccos"] = '';
            }

            //
            if ($tiporegistro == 'RegPro') {
                $prox = $d["expediente"];
            } else {
                $matx = $d["expediente"];
            }

            $sec++;
            $arrValores = array(
                "'" . $recx . "'",
                $sec,
                "'" . $fecha . "'",
                "'" . $d["idservicio"] . "'",
                // "''",
                "'" . ltrim($matx, "0") . "'",
                "'" . ltrim($prox, "0") . "'",
                // "'" . $tots["tipogasto"] . "'",
                "'" . ltrim($d["ano"], "0") . "'",
                intval($d["cantidad"]),
                doubleval($d["valorbase"]),
                doubleval($d["porcentaje"]),
                doubleval($d["valorservicio"]),
                "'" . $identificacion . "'",
                "'" . addslashes($nombre) . "'",
                "'" . $organizacion . "'",
                "'" . $categoria . "'",
                "'" . $idtipodoc . "'",
                "'" . $numdoc . "'",
                "'" . addslashes($origendoc) . "'",
                "'" . $fechadoc . "'",
                "'" . $d["expedienteafiliado"] . "'",
                "'" . $fecha . "'"
            );
            $res = insertarRegistrosMysqli2($mysqli, 'mreg_recibosgenerados_detalle', $arrCampos, $arrValores);
            if ($res === false) {
                \logSii2::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_detalle : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        // ************************************************************************************************ //
        // Arma formas de pago del recibo y las graba
        // ************************************************************************************************ //
        $arrCampos = array(
            'recibo',
            'tipo',
            'valor',
            'banco',
            'cheque',
        );
        $sec = 0;
        foreach ($fps as $fp) {
            $arrValores = array(
                "'" . $recx . "'",
                "'" . $fp["tipo"] . "'",
                $fp["valor"],
                "'" . $fp["banco"] . "'",
                "'" . $fp["cheque"] . "'"
            );
            $res = insertarRegistrosMysqli2($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logSii2::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        // ************************************************************************************************ //
        // 2016-07-31 : JINT
        // crea el recibo automáticamente en mreg_est_recibos
        // ************************************************************************************************ //
        $arrCampos = array(
            'numerorecibo',
            'ctranulacion',
            'numfactura',
            'fecoperacion',
            'horaoperacion',
            'idclase',
            'identificacion',
            'nombre',
            'operador',
            'sucursal',
            'ccos',
            'unidad',
            'producto',
            'servicio',
            'serviciodescuento',
            'cantidad',
            'valor',
            'tipogasto',
            'base',
            'moneda',
            'tasa',
            'codigocontable',
            'matricula',
            'activos',
            'anorenovacion',
            'formapago',
            'apellido1',
            'apellido2',
            'nombre1',
            'nombre2',
            'numinterno',
            'numunico',
            'numerooperacion',
            'direccion',
            'municipio',
            'telefono',
            'email',
            'compite360',
            'proyecto',
            'expedienteafectado',
            'fecharenovacionaplicable'
        );
        $arrValores = array();
        $sec = 0;
        if (!isset($_SESSION["tramite"]["camaradestino"])) {
            $_SESSION["tramite"]["camaradestino"] = '';
        }
        if (!isset($_SESSION["tramite"]["camaraorigen"])) {
            $_SESSION["tramite"]["camaraorigen"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerointernorue"])) {
            $_SESSION["tramite"]["numerointernorue"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerounicorue"])) {
            $_SESSION["tramite"]["numerounicorue"] = '';
        }
        if (!isset($_SESSION["tramite"]["proyecto"])) {
            $_SESSION["tramite"]["proyecto"] = '001';
        }

        foreach ($_SESSION["tramite"]["liquidacion"] as $d) {
            if (!isset($d["expediente"])) {
                $d["expediente"] = '';
            }
            if (!isset($d["idservicio"])) {
                $d["idservicio"] = '';
            }
            if (!isset($d["ano"])) {
                $d["ano"] = '';
            }
            if (!isset($d["cantidad"])) {
                $d["cantidad"] = 0;
            }
            if (!isset($d["valorbase"])) {
                $d["valorbase"] = 0;
            }
            if (!isset($d["porcentaje"])) {
                $d["porcentaje"] = 0;
            }
            if (!isset($d["valorservicio"])) {
                $d["valorservicio"] = 0;
            }
            if (!isset($d["expedienteafiliado"])) {
                $d["expedienteafiliado"] = '';
            }
            if (!isset($d["ccos"])) {
                $d["ccos"] = '';
            }

            //            
            $cc = '';
            if ($_SESSION["tramite"]["tipogasto"] == '7') {
                $cc = $_SESSION["tramite"]["camaradestino"];
            }
            if ($_SESSION["tramite"]["tipogasto"] == '8') {
                $cc = $_SESSION["tramite"]["camaraorigen"];
            }

            $fp = '1';
            switch ($_SESSION["tramite"]["idformapago"]) {
                case "02" : $fp = '2';
                    break;
                case "03" : $fp = '7';
                    break;
                case "04" : $fp = '3';
                    break;
                case "05" :
                    if ($sitcredito == 'si') {
                        $fp = '3';
                    } else {
                        $fp = '7';
                    }
                    break;
                case "06" : $fp = '5';
                    break;
                case "09" : $fp = '7';
                    break;
                case "90" : $fp = '4';
                    break;
            }

            $sec++;

            $arrValores = array(
                "'" . $recx . "'",
                "'0'",
                "'" . $_SESSION["tramite"]["numerofactura"] . "'",
                "'" . $fecha . "'",
                "'" . $hora . "'",
                "'" . $_SESSION["tramite"]["tipoidentificacionpagador"] . "'",
                "'" . ltrim($_SESSION["tramite"]["identificacionpagador"], "0") . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["nombrepagador"])) . "'",
                "'" . $_SESSION["generales"]["cajero"] . "'",
                "'" . $_SESSION["tramite"]["sede"] . "'",
                "'" . $d["ccos"] . "'", // Ccos
                "''", // Unidad
                "''", // Servicio
                "'" . $d["idservicio"] . "'",
                "''", // Servicio descuento
                intval($d["cantidad"]),
                doubleval($d["valorservicio"]),
                "'" . $_SESSION["tramite"]["tipogasto"] . "'",
                doubleval($d["valorbase"]),
                "'001'",
                0,
                "'" . $cc . "'",
                "'" . ltrim($d["expediente"], "0") . "'",
                doubleval($d["valorbase"]),
                "'" . $d["ano"] . "'",
                "'" . $fp . "'",
                "'" . addslashes(substr(trim($_SESSION["tramite"]["apellido1pagador"]), 0, 50)) . "'",
                "'" . addslashes(substr(trim($_SESSION["tramite"]["apellido2pagador"]), 0, 50)) . "'",
                "'" . addslashes(substr(trim($_SESSION["tramite"]["nombre1pagador"]), 0, 50)) . "'",
                "'" . addslashes(substr(trim($_SESSION["tramite"]["nombre2pagador"]), 0, 50)) . "'",
                "'" . $_SESSION["tramite"]["numerointernorue"] . "'",
                "'" . $_SESSION["tramite"]["numerounicorue"] . "'",
                "'" . $operacion . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["direccionpagador"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["municipiopagador"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["telefonopagador"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["emailpagador"])) . "'",
                "'NO'",
                "'" . sprintf("%03s", $_SESSION["tramite"]["proyectocaja"]) . "'",
                "'" . $d["expedienteafiliado"] . "'",
                "'" . $fecha . "'"
            );
            $res = insertarRegistrosMysqli2($mysqli, 'mreg_est_recibos', $arrCampos, $arrValores);
            if ($res === false) {
                \logSii2::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_est_recibos : ' . $_SESSION["generales"]["mensajeerror"]);
            }
        }

        //
        return $recx;
    }

    public static function retornarSalarioMinimoActualSii($dbx = null, $ano = '') {
        $smlvs = retornarRegistrosMysqli2($dbx, 'bas_smlv', "1=1", "fecha asc");
        $minimo = 0;
        foreach ($smlvs as $sm) {
            if ($ano != '') {
                if (substr($sm["fecha"], 0, 4) == $ano) {
                    $minimo = $sm["salario"];
                }
            } else {
                if (($sm["fecha"] <= date("Ymd"))) {
                    $minimo = $sm["salario"];
                }
            }
        }
        unset($smlvs);
        unset($sm);
        return $minimo;
    }

    public static function isJson($string) {
        return ((is_string($string) &&
                (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }

    public static function localizarDiscoActual($dbx, $tipo = '000') {
        $arrTem = retornarRegistroMysqli2($dbx, 'bas_tipoanexodocumentos', "id='" . $tipo . "'");
        if ($arrTem === false || (empty($arrTem))) {
            $_SESSION["generales"]["mensajeerror"] = 'Imposible localizar el tipo de anexo en la tabla bas_tipoanexodocumentos';
            return false;
        }
        if (trim($arrTem["clavevalor"]) == '') {
            $_SESSION["generales"]["mensajeerror"] = 'Tipo anexo de documentos ' . $tipo . ' no tiene asignada una clave_valor para localización del disco actual';
            return false;
        }
        $disco = retornarClaveValorSii2($dbx, $arrTem["clavevalor"]); // Disco de la carpeta correspondiente
        if ($disco === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Imposible localizar la entrada ' . $arrTem["clavevalor"] . ' en claves_valor';
            return false;
        }
        if (ltrim(trim($disco), "0") == '') {
            $disco = '001';
        }
        return $disco;
    }

    public static function retornarPantallaPredisenada($mysqli, $pantalla = '') {
        $pant = retornarRegistroMysqli2($mysqli, 'pantallas_propias', "idpantalla='" . $pantalla . "'");
        if ($pant === false || empty($pant)) {
            $pant = retornarRegistroMysqli2($mysqli, 'bas_pantallas', "idpantalla='" . $pantalla . "'");
            if ($pant === false || empty($pant)) {
                return "";
            } else {
                return $pant["txtasociado"];
            }
        } else {
            return $pant["txtasociado"];
        }
    }

    public static function localizarIP() {
        $ip = '';

        //
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        //
        if ($ip == '') {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = '127.0.0.1';
                }
            }
        }
        return $ip;
    }

    public static function localizarLetraCiiu($dbx, $ciiu) {
        $query = "select * from bas_ciius where idciiunum='" . $ciiu . "'";
        $res = retornarRegistroMysqli2($dbx, "bas_ciius", "idciiunum='" . $ciiu . "'");
        if ($res === false || empty($res)) {
            return '';
        }
        $resultado = $res["idciiu"];
        return substr($resultado, 0, 1);
    }

    public static function localizarSmmlv($fecha, $dbx = null) {
        require_once ('mysqli.php');
        $resultado = '';
        $cerrar = 'no';
        if ($dbx === null) {
            $dbx = conexionMysqli2();
            $cerrar = 'si';
        }

        $temx = retornarRegistrosMysqli2($dbx, 'bas_smlv', "fecha");
        foreach ($temx as $res) {
            if ($res["fecha"] < $fecha) {
                $resultado = $res["salario"];
            }
        }

        if ($cerrar == 'si') {
            $dbx->close();
        }
        return $resultado;
    }

    public static function mostrarFecha($fec) {
        if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
            return '';
        }
        if (strlen($fec) == 10) {
            $fec = str_replace("/", "-", $fec);
            return $fec;
        } else {
            return substr($fec, 0, 4) . '-' . substr($fec, 4, 2) . '-' . substr($fec, 6, 2);
        }
    }

    public static function mostrarFecha2($fec) {
        if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
            return '';
        }
        if (strlen($fec) == 10) {
            $fec = str_replace(array("/", "-"), "", $fec);
        }
        return substr($fec, 6, 2) . '/' . substr($fec, 4, 2) . '/' . substr($fec, 0, 4);
    }

    public static function mostrarFechaLetras($fec) {
        if (trim($fec) == '') {
            return '';
        }
        $fec = str_replace(array('-', '/'), "", $fec);
        if (trim($fec) == '') {
            return '';
        }
        $txt = '';
        $mes = substr($fec, 4, 2);
        switch ($mes) {
            case "01": $txt = 'enero';
                break;
            case "02": $txt = 'febrero';
                break;
            case "03": $txt = 'marzo';
                break;
            case "04": $txt = 'abril';
                break;
            case "05": $txt = 'mayo';
                break;
            case "06": $txt = 'junio';
                break;
            case "07": $txt = 'julio';
                break;
            case "08": $txt = 'agosto';
                break;
            case "09": $txt = 'septiembre';
                break;
            case "10": $txt = 'octubre';
                break;
            case "11": $txt = 'noviembre';
                break;
            case "12": $txt = 'diciembre';
                break;
        }
        if (strlen($fec) == 6) {
            return $txt . ' ' . ' de ' . substr($fec, 0, 4);
        } else {
            return $txt . ' ' . substr($fec, 6, 2) . ' de ' . substr($fec, 0, 4);
        }
    }

    public static function mostrarFechaLetras1($fec) {
        if (trim($fec) == '') {
            return '';
        }
        $fec = str_replace(array('-', '/'), "", $fec);
        if (trim($fec) == '') {
            return '';
        }
        $txt = '';
        $mes = substr($fec, 4, 2);
        switch ($mes) {
            case "01": $txt = 'enero';
                break;
            case "02": $txt = 'febrero';
                break;
            case "03": $txt = 'marzo';
                break;
            case "04": $txt = 'abril';
                break;
            case "05": $txt = 'mayo';
                break;
            case "06": $txt = 'junio';
                break;
            case "07": $txt = 'julio';
                break;
            case "08": $txt = 'agosto';
                break;
            case "09": $txt = 'septiembre';
                break;
            case "10": $txt = 'octubre';
                break;
            case "11": $txt = 'noviembre';
                break;
            case "12": $txt = 'diciembre';
                break;
        }
        if (strlen($fec) == 6) {
            return $txt . ' ' . ' de ' . substr($fec, 0, 4);
        } else {
            return substr($fec, 6, 2) . ' de ' . $txt . ' de ' . substr($fec, 0, 4);
        }
    }

    public static function mostrarHora($dat) {
        $dat = str_replace(":", "", $dat);
        if ((trim($dat) == '') || (ltrim($dat, "0") == '')) {
            return '';
        }
        if (strlen($dat) == 6) {
            return substr($dat, 0, 2) . ':' . substr($dat, 2, 2) . ':' . substr($dat, 4, 2);
        } else {
            if (strlen($dat) == 4) {
                return substr($dat, 0, 2) . ':' . substr($dat, 2, 2) . ':00';
            } else {
                return $dat;
            }
        }
    }

    public static function mostrarPesos2($var) {
        if (trim($var) == '') {
            return "-o-";
        }
        if (!is_numeric($var)) {
            return "-o-";
        }
        return "$" . number_format($var, 2, ",", ".");
    }

    public static function ordenarMatriz($arreglo, $campo, $inverse = false) {
        $position = array();
        $newRow = array();
        foreach ($arreglo as $key => $row) {
            $position[$key] = $row[$campo];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    public static function programarAlertaTempranaSii($dbx, $tiporegistro, $liquidacion, $matricula, $proponente, $tipotramite) {

        //
        if ($_SESSION["generales"]["tipousuario"] == '00' || $_SESSION["generales"]["tipousuario"] == '06') {
            if (trim($_SESSION["generales"]["emailusuariocontrol"]) != '') {
                $resEmail = retornarRegistroMysqli2($dbx, 'mreg_email_excluidos_alertas_tempranas', "email='" . $_SESSION["generales"]["emailusuariocontrol"] . "'");
                $res = retornarRegistroMysqli2($dbx, 'mreg_alertas_tempranas', "idliquidacion=" . $liquidacion);
                if ($res === false || empty($res)) {
                    if ($tiporegistro == 'RegPro') {
                        $arrExp = retornarRegistroMysqli2($dbx, 'mreg_est_proponentes', "proponente='" . $proponente . "'");
                        if ($arrExp && !empty($arrExp)) {
                            $arrExp["razonsocial"] = $arrExp["nombre"];
                        }
                    } else {
                        $arrExp = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . $matricula . "'");
                    }
                    if ($arrExp && !empty($arrExp)) {
                        $email = '';
                        $email = $arrExp["emailnot"];
                        if ($email == '') {
                            $email = $arrExp["emailcom"];
                        }
                        if (trim($email) != '') {
                            $asunto = 'Alerta temprana por acceso al expediente No. ' . trim($matricula . $proponente) . ' en la ' . RAZONSOCIAL;
                            $detalle = 'Señor(es)<br>';
                            $detalle .= $arrExp["razonsocial"] . '<br><br>';
                            $detalle .= 'Nos permitimos informarle que el día ' . date("Y-m-d") . ' a las ' . date("H:i:s") . ' ';
                            $detalle .= 'se solicitó en los sistemas de registro que administra la ' . RAZONSOCIAL . ' el siguiente trámite:<br><br>';
                            $detalle .= '- Expediente : ' . $matricula . $proponente . '<br>';
                            $detalle .= '- Trámite solicitado : ' . $tipotramite . '<br>';
                            $detalle .= '- Email del usuario que solicita el trámite : ' . $_SESSION["generales"]["emailusuariocontrol"] . '<br>';
                            $detalle .= '- Ip del usuario : ' . \funcionesSii2::localizarIP() . '<br><br>';
                            $detalle .= 'Esta alerta se genera en cumplimiento de lo establecido en la Circular 002 de 2016 expedida por la ';
                            $detalle .= 'Superintendencia de Industria y Comercio, numeral 1.14.<br><br>';
                            $detalle .= 'Cordialmente<br><br>';
                            $detalle .= 'Area de Registros Públicos<br>';
                            $detalle .= RAZONSOCIAL;

                            $arrCampos = array(
                                'idliquidacion',
                                'matricula',
                                'proponente',
                                'fecha',
                                'hora',
                                'email',
                                'celular',
                                'usuario',
                                'tipotramite',
                                'ip',
                                'textoalerta',
                                'estado'
                            );
                            $arrValores = array(
                                $liquidacion,
                                "'" . $matricula . "'",
                                "'" . $proponente . "'",
                                "'" . date("Ymd") . "'",
                                "'" . date("His") . "'",
                                "'" . addslashes($email) . "'",
                                "''", // celular
                                "'" . addslashes($_SESSION["generales"]["emailusuariocontrol"]) . "'",
                                "'" . $tipotramite . "'",
                                "'" . \funcionesSii2::localizarIP() . "'",
                                "'" . addslashes($detalle) . "'",
                                "'1'" // programada
                            );
                            insertarRegistrosMysqli2($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores);

                            if (!defined('TIPO_AMBIENTE')) {
                                define('TIPO_AMBIENTE', 'PRUEBAS');
                            }
                            if (TIPO_AMBIENTE == 'PRODUCCION') {
                                if (!$resEmail || empty($resEmail)) {
                                    if ($_SESSION["generales"]["emailusuariocontrol"] != 'prueba@prueba.prueba') {
                                        $res = \funcionesSii2::enviarEmailSii2(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $email, $asunto, $detalle);
                                    }
                                }
                            } else {
                                if ($_SESSION["generales"]["emailusuariocontrol"] != 'prueba@prueba.prueba') {
                                    if (isset($resEmail) && !empty($resEmail)) {
                                        $res = \funcionesSii2::enviarEmailSii2(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $resEmail["email"], $asunto, $detalle);
                                    }
                                }
                            }
                            if ($res) {
                                $arrCampos = array(
                                    'estado'
                                );
                                $arrValores = array(
                                    "'3'" // Enviado con éxito
                                );
                                regrabarRegistrosMysqli2($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores, "idliquidacion=" . $liquidacion);
                            } else {
                                $arrCampos = array(
                                    'estado'
                                );
                                $arrValores = array(
                                    "'4'" // Envio con error
                                );
                                regrabarRegistrosMysqli2($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores, "idliquidacion=" . $liquidacion);
                            }
                        }
                    }
                }
            }
        }
    }

    public static function retornarSecuenciaSii($dbx, $sec) {
        $res = retornarRegistroMysqli2($dbx, 'secuencias', "tipo='" . $sec . "'");
        if ($res === false) {
            return false;
        }
        if (empty($res)) {
            $retornar = 0;
        } else {
            $retornar = $res["consecutivo"];
        }

        //
        $retornar++;

        //
        if ($sec == 'LIQUIDACION-REGISTROS') {
            $ok = 'no';
            while ($ok == 'no') {
                if (contarRegistrosMysqli2($dbx, 'mreg_liquidacion', "idliquidacion=" . $retornar) > 0) {
                    $retornar++;
                } else {
                    $arrCampos = array(
                        'idliquidacion',
                        'fecha',
                        'hora',
                        'idestado'
                    );
                    $arrValores = array(
                        $retornar,
                        "'" . date("Ymd") . "'",
                        "'" . date("H:i:s") . "'",
                        "'01'"
                    );
                    insertarRegistrosMysqli2($dbx, 'mreg_liquidacion', $arrCampos, $arrValores);
                    $ok = 'si';
                }
            }
        }

        if ($sec == 'DEVOLUCION-REGISTROS') {
            $ok = 'no';
            while ($ok == 'no') {
                if (contarRegistrosMysqli2($dbx, 'mreg_devoluciones_nueva', "iddevolucion=" . $retornar) > 0) {
                    $retornar++;
                } else {
                    $ok = 'si';
                }
            }
        }

        if ($sec == 'RADICACION-REPORTES-EE') {
            $ok = 'no';
            while ($ok == 'no') {
                if (contarRegistrosMyqli2($dbx, 'mreg_reportesradicados', "idradicacion='" . ltrim($retornar, "0") . "'") > 0) {
                    $retornar++;
                } else {
                    $ok = 'si';
                }
            }
        }

        if (empty($res)) {
            $arrCampos = array(
                'tipo',
                'consecutivo'
            );
            $arrValores = array(
                "'" . $sec . "'",
                $retornar
            );
            insertarRegistrosMyqli2($dbx, 'secuencias', $arrCampos, $arrValores);
        } else {
            $arrCampos = array(
                'consecutivo'
            );
            $arrValores = array(
                $retornar
            );
            regrabarRegistrosMysqli2($dbx, 'secuencias', $arrCampos, $arrValores, "tipo='" . $sec . "'");
        }

        //
        return $retornar;
    }

    public static function rutinaNotificarRadicacionSii($dbx, $recibo = '', $codbarras = '', $emailsentrada = array(), $celularesentrada = array(), $nameLog = '') {
        require_once ('LogSii2.class.php');
        ini_set('memory_limit', '1024M');

        if ($nameLog == '') {
            $nameLog = 'rutinaNotificarRadicacionSii_' . date("Ymd");
        }
        \logSii2::general2($nameLog, '', 'Ingreso a notificar radicacion  : ' . $recibo . ' /' . $codbarras);

        //
        $reg = false;
        $notificar = 'si';

        //
        if ($recibo == '' && $codbarras != '') {
            $temx = retornarRegistroMysqli2($dbx, 'mreg_est_codigosbarras', "codigobarras='" . $codbarras . "'");
            if ($temx && !empty($temx)) {
                if ($temx["recibo"] != '') {
                    $query = "recibo='" . $temx["recibo"] . "' and tipogasto IN ('0','4','6','8')";
                    $reg = retornarRegistroMysqli2($dbx, 'mreg_recibosgenerados', $query);
                }
            }
            \logSii2::general2($nameLog, '', 'Localizo recibo a traves del codigo de barras');
        }

        if ($recibo != '') {
            $query = "recibo='" . $recibo . "' and tipogasto IN ('0','4','6','8')";
            $reg = retornarRegistroMysqli2($dbx, 'mreg_recibosgenerados', $query);
            \logSii2::general2($nameLog, '', 'Localizo recibo a traves del nro de recibo');
        }

        //
        $resultadoRecibo = array();
        if ($reg && !empty($reg)) {

            $liq = retornarRegistroMysqli2($dbx, 'mreg_liquidacion', "numerorecibo='" . $reg["recibo"] . "'");

            $arrTemCB = retornarRegistroMysqli2($dbx, 'mreg_est_codigosbarras', "codigobarras='" . $reg["codigobarras"] . "'");
            if ($arrTemCB && !empty($arrTemCB)) {
                if ($arrTemCB["actoreparto"] == '07' || $arrTemCB["actoreparto"] == '29') {
                    $notificar = 'no';
                }
            } else {
                $notificar = 'no';
            }
            if ($notificar == 'no') {
                \logSii2::general2($nameLog, $liq["idliquidacion"], 'Código de barras no notificable  : ' . $reg["recibo"] . ' /' . $reg["codigobarras"]);
            }
            if ($notificar == 'si') {

                $bandejaDigitalizacion = '';
                $tt = retornarRegistroMysqli2($dbx, 'mreg_codrutas', "id='" . $arrTemCB["actoreparto"] . "'");
                if ($tt && !empty($tt)) {
                    $bandejaDigitalizacion = $tt["bandeja"];
                }

                //
                \logSii2::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Localizo recibo : ' . $reg["recibo"]);
                $resultadoRecibo["nrec"] = $reg["recibo"];
                $resultadoRecibo["cba"] = $reg["codigobarras"];
                $resultadoRecibo["ope"] = $reg["operacion"];
                $resultadoRecibo["fec"] = $reg["fecha"];
                $resultadoRecibo["hor"] = $reg["hora"];
                $resultadoRecibo["ide"] = $reg["identificacion"];
                $resultadoRecibo["nom"] = $reg["razonsocial"];
                $resultadoRecibo["mat"] = array();
                $resultadoRecibo["pro"] = '';
                $resultadoRecibo["ser"] = '';
                $resultadoRecibo["valor"] = $reg["valorneto"];
                $resultadoRecibo["tt"] = $reg["tipotramite"];
                $resultadoRecibo["emails"] = array();
                $resultadoRecibo["telefonos"] = array();

                //
                $resultadoRecibo["emails"][$reg["email"]] = $reg["email"];
                if ($reg["telefono1"] != '' && strlen($reg["telefono1"]) == 10 && substr($reg["telefono1"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$reg["telefono1"]] = $reg["telefono1"];
                }
                if ($reg["telefono2"] != '' && strlen($reg["telefono2"]) == 10 && substr($reg["telefono2"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$reg["telefono2"]] = $reg["telefono2"];
                }

                //
                if (empty($emailsentrada)) {
                    foreach ($emailsentrada as $e) {
                        if (!isset($resultadoRecibo["emails"][$e])) {
                            $resultadoRecibo["emails"][$e] = $e;
                        }
                    }
                }

                //
                if (empty($celularesentrada)) {
                    foreach ($celularesentrada as $e) {
                        if (!isset($resultadoRecibo["telefonos"][$e])) {
                            $resultadoRecibo["telefonos"][$e] = $e;
                        }
                    }
                }

                //
                $arrTem = retornarRegistrosMysqli2($dbx, 'mreg_recibosgenerados_detalle', "recibo='" . $reg["recibo"] . "'", "secuencia");
                $j = 0;
                if ($arrTem && !empty($arrTem)) {
                    foreach ($arrTem as $tx) {
                        $j++;
                        if ($j == 1) {
                            if ($tx["matricula"] != '' && substr($tx["matricula"], 0, 5) != 'NUEVA') {
                                $resultadoRecibo["mat"][$tx["matricula"]] = $tx["matricula"];
                            }
                            if ($tx["proponente"] != '') {
                                $resultadoRecibo["pro"] = $tx["proponente"];
                            }
                            $resultadoRecibo["ser"] = $tx["idservicio"];
                            \logSii2::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Localizo matricula : ' . $tx["matricula"]);
                            \logSii2::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Localizo servicio : ' . $tx["idservicio"]);
                        }
                    }
                }

                // ************************************************************************************************** //
                // 2017-12-15: JINT: Se notifica sin importar que sea a los emails del recibo y del código de barras
                // ************************************************************************************************** //
                // $arrTemCB = retornarRegistroMysqli($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $reg["codigobarras"] . "'");
                if ($arrTemCB["emailnot1"] != '') {
                    $resultadoRecibo["emails"][$arrTemCB["emailnot1"]] = $arrTemCB["emailnot1"];
                }
                if ($arrTemCB["emailnot2"] != '') {
                    $resultadoRecibo["emails"][$arrTemCB["emailnot2"]] = $arrTemCB["emailnot2"];
                }
                if ($arrTemCB["emailnot3"] != '') {
                    $resultadoRecibo["emails"][$arrTemCB["emailnot3"]] = $arrTemCB["emailnot3"];
                }
                if ($arrTemCB["celnot1"] != '' && strlen($arrTemCB["celnot1"]) == 10 && substr($arrTemCB["celnot1"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$arrTemCB["celnot1"]] = $arrTemCB["celnot1"];
                }
                if ($arrTemCB["celnot2"] != '' && strlen($arrTemCB["celnot2"]) == 10 && substr($arrTemCB["celnot2"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$arrTemCB["celnot2"]] = $arrTemCB["celnot2"];
                }
                if ($arrTemCB["celnot3"] != '' && strlen($arrTemCB["celnot3"]) == 10 && substr($arrTemCB["celnot3"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$arrTemCB["celnot3"]] = $arrTemCB["celnot3"];
                }

                // *********************************************************************************** //
                // Siempre y cuando no sea un embargo
                // *********************************************************************************** //            
                if (($arrTemCB["actoreparto"] != '07') && ($arrTemCB["actoreparto"] != '29')) {

                    // *********************************************************************************** //
                    // Busca cada expediente
                    // *********************************************************************************** //            
                    if (!empty($resultadoRecibo["mat"])) {
                        foreach ($resultadoRecibo["mat"] as $m) {
                            $exp = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . $m . "'");

                            // *********************************************************************************** //
                            // Localiza emails y celulares actuales
                            // *********************************************************************************** //                                    
                            if ($exp && !empty($exp)) {
                                if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                                }
                                if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                                }
                                if (trim($exp["telcom3"]) != '' && strlen($exp["telcom3"]) == 10 && substr($exp["telcom3"], 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][$exp["telcom3"]] = $exp["telcom3"];
                                }
                                if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][$exp["telnot"]] = $exp["telnot"];
                                }
                                if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                                }
                                if (trim($exp["telnot3"]) != '' && strlen($exp["telnot3"]) == 10 && substr($exp["telnot3"], 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][$exp["telnot3"]] = $exp["telnot3"];
                                }
                                if (trim($exp["emailcom"]) != '') {
                                    $resultadoRecibo["emails"][$exp["emailcom"]] = $exp["emailcom"];
                                }
                                if (trim($exp["emailcom2"]) != '') {
                                    $resultadoRecibo["emails"][$exp["emailcom2"]] = $exp["emailcom2"];
                                }
                                if (trim($exp["emailcom3"]) != '') {
                                    $resultadoRecibo["emails"][$exp["emailcom3"]] = $exp["emailcom3"];
                                }
                                if (trim($exp["emailnot"]) != '') {
                                    $resultadoRecibo["emails"][$exp["emailnot"]] = $exp["emailnot"];
                                }
                            }

                            // *********************************************************************************** //
                            // Localiza emails y celulares anteriores migrados del SIREP
                            // *********************************************************************************** //                                                            
                            $exps = retornarRegistrosMysqli2($dbx, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim($m, "0") . "'", "id");
                            if ($exps && !empty($exps)) {
                                foreach ($exps as $exps1) {
                                    if (trim($exps1["campo"]) == "EMAILCOM-ANTERIOR") {
                                        $resultadoRecibo["emails"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                    }
                                    if (trim($exps1["campo"]) == "EMAILNOT-ANTERIOR") {
                                        $resultadoRecibo["emails"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                    }
                                    if (trim($exps1["campo="]) == "CELCOM-ANTERIOR") {
                                        if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                            $resultadoRecibo["telefonos"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                        }
                                    }
                                    if (trim($exps1["campo"]) == "CELNOT-ANTERIOR") {
                                        if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                            $resultadoRecibo["telefonos"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                        }
                                    }
                                }
                            }

                            // *********************************************************************************** //
                            // Localiza emails y celulares modificados en mreg_campos_historicos_AAAA
                            // *********************************************************************************** //                                                            
                            $d = localizarCampoAnteriorTodosSii2($dbx, $m, 'telcom1');
                            foreach ($d as $d1) {
                                if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosSii2($dbx, $m, 'telcom2');
                            foreach ($d as $d1) {
                                if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosSii2($dbx, $m, 'telcom3');
                            foreach ($d as $d1) {
                                if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosSii2($dbx, $m, 'telnot');
                            foreach ($d as $d1) {
                                if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][trim($d1)] = trim($d1);
                                }
                            }
                            $d = localizarCampoAnteriorTodosSii2($dbx, $m, 'emailcom');
                            foreach ($d as $d1) {
                                $resultadoRecibo["emails"][trim($d1)] = trim($d1);
                            }
                            $d = localizarCampoAnteriorTodosSii2($dbx, $m, 'emailnot');
                            foreach ($d as $d1) {
                                $resultadoRecibo["emails"][trim($d1)] = trim($d1);
                            }
                        }
                    }
                }

                // Recupera números telefonicos y emails actuales - proponentes
                if ($resultadoRecibo["pro"] != '') {
                    $exp = retornarRegistroMysqli2($dbx, 'mreg_est_proponentes', "proponente='" . $resultadoRecibo["pro"] . "'");
                    if ($exp && !empty($exp)) {
                        if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                            $resultadoRecibo["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                        }
                        if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                            $resultadoRecibo["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                        }
                        if (trim($exp["celcom"]) != '' && strlen($exp["celcom"]) == 10 && substr($exp["celcom"], 0, 1) == '3') {
                            $resultadoRecibo["telefonos"][$exp["celcom"]] = $exp["celcom"];
                        }
                        if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                            $resultadoRecibo["telefonos"][$exp["telnot"]] = $exp["telnot"];
                        }
                        if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                            $resultadoRecibo["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                        }
                        if (trim($exp["celnot"]) != '' && strlen($exp["celnot"]) == 10 && substr($exp["celnot"], 0, 1) == '3') {
                            $resultadoRecibo["telefonos"][$exp["celnot"]] = $exp["celnot"];
                        }
                        if (trim($exp["emailcom"]) != '') {
                            $resultadoRecibo["emails"][$exp["emailcom"]] = $exp["emailcom"];
                        }
                        if (trim($exp["emailnot"]) != '') {
                            $resultadoRecibo["emails"][$exp["emailnot"]] = $exp["emailnot"];
                        }
                    }
                }

                // recupera números y emails anteriores (proponentes)
                if ($resultadoRecibo["pro"] != '') {
                    $exps = retornarRegistrosMysqli2($dbx, 'mreg_est_campostablas', "tabla='032' and registro='" . ltrim($resultadoRecibo["pro"], "0") . "'", "id");
                    if ($exps && !empty($exps)) {
                        foreach ($exps as $exps1) {
                            if (trim($exps1["campo"]) == "EMAILCOM-ANTERIOR") {
                                $resultadoRecibo["emails"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                            }
                            if (trim($exps1["campo"]) == "EMAILNOT-ANTERIOR") {
                                $resultadoRecibo["emails"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                            }
                            if (trim($exps1["campo"]) == "CELCOM-ANTERIOR") {
                                if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                }
                            }
                            if (trim($exps1["campo"]) == "CELNOT-ANTERIOR") {
                                if (strlen(trim($exps1["contenido"])) == 10 && substr(trim($exps1["contenido"]), 0, 1) == '3') {
                                    $resultadoRecibo["telefonos"][trim($exps1["contenido"])] = trim($exps1["contenido"]);
                                }
                            }
                        }
                    }
                }

                unset($reg);
                unset($reg1);

                //
                //
                \logSii2::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total emails : ' . count($resultadoRecibo["emails"]));
                \logSii2::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total celulares : ' . count($resultadoRecibo["telefonos"]));

                //
                \logSii2::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Servicio base ' . $resultadoRecibo["ser"]);
                $sinemails = 0;

                if (count($resultadoRecibo["emails"]) == 0) {
                    $arrCampos = array('estadoemail');
                    $arrValores = array("'2'");
                    regrabarRegistrosMysqli2($dbx, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                    $sinemails++;
                } else {
                    $msg = '';
                    $msg .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . mostrarFecha2Sii2($resultadoRecibo["fec"]) . ' a las ' . mostrarHoraSii2($resultadoRecibo["hor"]) . ' ';
                    $msg .= 'fue radicada en nuestras oficinas una transaccion sujeta a registro en los registros publicos que ';
                    $msg .= 'administra y maneja nuestra entidad. Los datos del tramite radicado son los siguientes:<br><br>';
                    $msg .= 'Recibo de Caja No. ' . $resultadoRecibo["nrec"] . '<br>';
                    $msg .= 'Numero operacion: ' . $resultadoRecibo["ope"] . '<br>';
                    if (ltrim($resultadoRecibo["cba"], "0") != '') {
                        $msg .= 'Codigo de barras: ' . $resultadoRecibo["cba"] . '<br>';
                    }
                    // $msg .= 'Expediente: ' . $resultadoRecibo["mat"] . '/' . $resultadoRecibo["pro"] . '<br>';
                    $mats = '';
                    if (!empty($resultadoRecibo["mat"])) {
                        foreach ($resultadoRecibo["mat"] as $mt1) {
                            if ($mats != '') {
                                $mats .= ', ';
                            }
                            $mats .= $mt1;
                        }
                    }
                    if ($mats != '') {
                        $msg .= 'Matriculas/Inscripciones: ' . $mats . '<br>';
                    }
                    if (ltrim($resultadoRecibo["pro"], "0") != '') {
                        $msg .= 'Proponente: ' . $resultadoRecibo["pro"] . '<br>';
                    }

                    $msg .= 'Identificacion: ' . $resultadoRecibo["ide"] . '<br>';
                    $msg .= 'Nombre: ' . utf8_decode($resultadoRecibo["nom"]) . '<br>';
                    $msg .= 'Tramite: ' . retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $resultadoRecibo["ser"] . "'", "nombre") . '<br>';
                    foreach ($resultadoRecibo["emails"] as $emx) {
                        $msg .= 'Email : ' . $emx . '<br>';
                    }
                    $msg .= '<br>';
                    $msg .= 'Valor de la transaccion: ' . $resultadoRecibo["valor"] . '<br><br>';

                    if (!defined('NOTIFICAR_TELEFONO')) {
                        define('NOTIFICAR_TELEFONO', 'NO');
                    }
                    if (NOTIFICAR_TELEFONO == 'SI') {
                        $msg .= 'Si tiene alguna duda o inquietud con el contenido de esta notificacion, puede comunicarse al ';
                        $msg .= 'numero ' . TELEFONO_ATENCION_USUARIOS . ' en la ciudad de ' . retornarRegistroMysqli2($dbx, 'bas_municipios', "codigomunicipio='" . MUNICIPIO . "'", "ciudad") . ' ';
                        $msg .= 'citando el tramite (recibo de caja) No. ' . $resultadoRecibo["nrec"] . '<br><br>';
                    }

                    $msg .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' ';
                    $msg .= 'en cumplimiento a lo contemplado en el Codigo de Procedimiento Administrativo y de lo Contencioso Administrativo.';
                    $msg .= '<br><br>';
                    $msg .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
                    $msg .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
                    $msg .= '<br><br>';
                    $msg .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';

                    // ***************************************************************************************** //
                    // En caso de renovación que se asienta automáticamente
                    // ***************************************************************************************** //
                    $msg1 = '';
                    $msg1 .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . mostrarFechaSii2($resultadoRecibo["fec"]) . ' a las ' . mostrarHoraSii2($resultadoRecibo["hor"]) . ' ';
                    $msg1 .= 'fue asentada en los registros publicos que ';
                    $msg1 .= 'administra y maneja nuestra organizacion una renovacion con la siguiente informacion: <br><br>';
                    $msg1 .= 'Recibo de Caja No. ' . $resultadoRecibo["nrec"] . '<br>';
                    $msg1 .= 'Numero operacion: ' . $resultadoRecibo["ope"] . '<br>';
                    if (ltrim($resultadoRecibo["cba"], "0") != '') {
                        $msg1 .= 'Codigo de barras: ' . $resultadoRecibo["cba"] . '<br>';
                    }
                    // $msg1 .= 'Expediente: ' . $resultadoRecibo["mat"] . '/' . $resultadoRecibo["pro"] . '<br>';
                    $mats = '';
                    if (!empty($resultadoRecibo["mat"])) {
                        foreach ($resultadoRecibo["mat"] as $mt1) {
                            if ($mats != '') {
                                $mats .= ', ';
                            }
                            $mats .= $mt1;
                        }
                    }
                    if ($mats != '') {
                        $msg1 .= 'Matriculas/Inscripciones: ' . $mats . '<br>';
                    }
                    if (ltrim($resultadoRecibo["pro"], "0") != '') {
                        $msg1 .= 'Proponente: ' . $resultadoRecibo["pro"] . '<br>';
                    }

                    $msg1 .= 'Identificacion: ' . $resultadoRecibo["ide"] . '<br>';
                    $msg1 .= 'Nombre: ' . utf8_decode($resultadoRecibo["nom"]) . '<br>';
                    $msg1 .= 'Tramite: ' . retornarRegistroMysqli2($dbx, 'mreg_servicios', "idservicio='" . $resultadoRecibo["ser"] . "'", "nombre") . '<br>';
                    foreach ($resultadoRecibo["emails"] as $emx) {
                        $msg1 .= 'Email : ' . $emx . '<br>';
                    }
                    $msg1 .= '<br>';
                    $msg1 .= 'Valor de la transaccion: ' . $resultadoRecibo["valor"] . '<br><br>';

                    if (!defined('NOTIFICAR_TELEFONO')) {
                        define('NOTIFICAR_TELEFONO', 'NO');
                    }
                    if (NOTIFICAR_TELEFONO == 'SI') {
                        $msg1 .= 'Si tiene alguna duda o inquietud con el contenido de esta notificacion, puede comunicarse al ';
                        $msg1 .= 'numero ' . TELEFONO_ATENCION_USUARIOS . ' en la ciudad de ' . retornarRegistroMysqli2($dbx, 'bas_municipios', "codigomunicipio='" . MUNICIPIO . "'", "ciudad") . ' ';
                        $msg1 .= 'citando el tramite (recibo de caja) No. ' . $resultadoRecibo["nrec"] . '<br><br>';
                    }

                    $msg1 .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' ';
                    $msg1 .= 'en cumplimiento a lo contemplado en el Codigo de Procedimiento Administrativo y de lo Contencioso Administrativo.';
                    $msg1 .= '<br><br>';
                    $msg1 .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
                    $msg1 .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
                    $msg1 .= '<br><br>';
                    $msg1 .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';

                    //
                    $emailtotales = 0;
                    $emailvalidos = 0;
                    foreach ($resultadoRecibo["emails"] as $emx) {
                        if (trim($emx) != '') {
                            $emx1 = $emx;
                            if (TIPO_AMBIENTE == 'PRUEBAS') {
                                $emx1 = 'jint@confecamaras.org.co';
                            }
                            $emailtotales++;
                            if (validarEmailSii2($emx) === true) {

                                $rEmail = \funcionesSii2::enviarEmailSii2(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Notificacion de radicacion No. ' . $resultadoRecibo["nrec"] . ' en  LA ' . RAZONSOCIAL, $msg);
                                if ($resultadoRecibo["tt"] == 'renovacionmatricula' || $resultadoRecibo["tt"] == 'renovacionesadl') {
                                    if ($_SESSION["tramite"]["multadoponal"] != 'S' &&
                                            $_SESSION["tramite"]["multadoponal"] != 'L' &&
                                            $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' &&
                                            ($_SESSION ["tramite"] ["cumplorequisitosbenley1780"] != 'S' ||
                                            $_SESSION ["tramite"] ["mantengorequisitosbenley1780"] != 'S' ||
                                            $_SESSION ["tramite"] ["renunciobeneficiosley1780"] != 'N')) {
                                        $rEmail1 = \funcionesSii2::enviarEmailSii2(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Notificacion de asentamiento No. ' . $resultadoRecibo["nrec"] . ' en  LA ' . RAZONSOCIAL, $msg1);
                                    } else {
                                        unset($rEmail1);
                                    }
                                } else {
                                    unset($rEmail1);
                                }
                                foreach ($resultadoRecibo["mat"] as $mt1) {
                                    if ($rEmail === false) {
                                        \funcionesSii2::actualizarMregNotificacionesParaEnviarEmailSii($dbx, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                    } else {
                                        \funcionesSii2::actualizarMregNotificacionesParaEnviarEmailSii($dbx, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                    }
                                    if (isset($rEmail1)) {
                                        if ($rEmail1 === false) {
                                            \funcionesSii2::actualizarMregNotificacionesParaEnviarEmailSii($dbx, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                        } else {
                                            \funcionesSii2::actualizarMregNotificacionesParaEnviarEmailSii($dbx, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                        }
                                    }
                                }
                                if ($rEmail) {
                                    $emailvalidos++;
                                }
                            }
                        }
                    }

                    // Actualiza el estado de la notificacion en SIREP
                    if ($emailvalidos == 0) {
                        if (NOTIFICAR_RADICACION == 'SI') {
                            $arrCampos = array('estadoemail');
                            $arrValores = array("'2'");
                            regrabarRegistrosMysqli2($dbx, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                        }
                    }

                    if ($emailvalidos > 0) {
                        if (NOTIFICAR_RADICACION == 'SI') {
                            $arrCampos = array('estadoemail');
                            $arrValores = array("'1'");
                            regrabarRegistrosMysqli2($dbx, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                        }
                    }
                }


                if (count($resultadoRecibo["telefonos"]) == 0) {
                    if (NOTIFICAR_RADICACION == 'SI') {
                        $arrCampos = array('estadosms');
                        $arrValores = array("'2'");
                        regrabarRegistrosMysqli2($dbx, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                    }
                } else {
                    $mt1 = '';
                    foreach ($resultadoRecibo["mat"] as $mx) {
                        if ($mt1 == '') {
                            $mt1 = $mx;
                        }
                    }

                    if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                        $txtSms = 'La ' . RAZONSOCIALSMS . ' le informa que el ' . mostrarFechaSii2($resultadoRecibo["fec"]) . ' a las ' . mostrarHoraSii2($resultadoRecibo["hor"]) . ' se radico una transaccion para el expediente ' . ltrim($mt1, "0") . ltrim($resultadoRecibo["pro"], "0");
                    } else {
                        $txtSms = 'La ' . RAZONSOCIAL . ' le informa que el ' . mostrarFechaSii2($resultadoRecibo["fec"]) . ' a las ' . mostrarHoraSii2($resultadoRecibo["hor"]) . ' se radico una transaccion para el expediente ' . ltrim($mt1, "0") . ltrim($resultadoRecibo["pro"], "0");
                    }

                    if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                        $txtSms1 = 'La ' . RAZONSOCIALSMS . ' le informa que el ' . mostrarFechaSii2($resultadoRecibo["fec"]) . ' a las ' . mostrarHoraSii2($resultadoRecibo["hor"]) . ' se asento la renovacion del expediente ' . ltrim($mt1, "0") . ltrim($resultadoRecibo["pro"], "0");
                    } else {
                        $txtSms1 = 'La ' . RAZONSOCIAL . ' le informa que el ' . mostrarFechaSii2($resultadoRecibo["fec"]) . ' a las ' . mostrarHoraSii2($resultadoRecibo["hor"]) . ' se asento la renovacion del expediente ' . ltrim($mt1, "0") . ltrim($resultadoRecibo["pro"], "0");
                    }

                    foreach ($resultadoRecibo["telefonos"] as $t) {
                        $exp1 = '';
                        if (ltrim($mt1, "0") != '') {
                            $exp1 = $mt1;
                        }
                        if (ltrim($resultadoRecibo["pro"], "0") != '') {
                            $exp1 = $resultadoRecibo["pro"];
                        }
                        \funcionesSii2::actualizarPilaSmsSii($dbx, $t, '1', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', $exp1, $mt1, $resultadoRecibo["pro"], $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms, '', $bandejaDigitalizacion);
                        if ($resultadoRecibo["tt"] == 'renovacionmatricula' || $resultadoRecibo["tt"] == 'renovacionesadl') {
                            if ($_SESSION["tramite"]["multadoponal"] != 'S' &&
                                    $_SESSION["tramite"]["multadoponal"] != 'L' &&
                                    $_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S' &&
                                    ($_SESSION ["tramite"] ["cumplorequisitosbenley1780"] != 'S' ||
                                    $_SESSION ["tramite"] ["mantengorequisitosbenley1780"] != 'S' ||
                                    $_SESSION ["tramite"] ["renunciobeneficiosley1780"] != 'N')) {
                                \funcionesSii2::actualizarPilaSmsSii($dbx, $t, '10', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', $exp1, $mt1, $resultadoRecibo["pro"], $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms1, '', $bandejaDigitalizacion);
                            }
                        }
                    }

                    $arrCampos = array('estadosms');
                    $arrValores = array("'1'");
                    regrabarRegistrosMysqli2($dbx, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                }
            }
        }
    }

    public static function quitarCaracteresDireccion($txt) {

        $patronesLetraNum[0] = '[^a-zA-Z0-9 ]';
        $reemplazos1[0] = '';
        $txt_tmp = trim(preg_replace($patronesLetraNum, $reemplazos1, $txt));

        $patronesSimbolos[0] = '[^.-#ºª?°]';
        $reemplazos2[0] = '';
        return trim(preg_replace($patronesSimbolos, $reemplazos2, $txt_tmp));
    }

    public static function reemplazarInvertidos($txt) {

        $txt = str_replace("À", "Á", $txt);
        $txt = str_replace("È", "É", $txt);
        $txt = str_replace("Ì", "Í", $txt);
        $txt = str_replace("Ò", "Ó", $txt);
        $txt = str_replace("Ù", "Ú", $txt);

        $txt = str_replace("à", "á", $txt);
        $txt = str_replace("è", "é", $txt);
        $txt = str_replace("ì", "í", $txt);
        $txt = str_replace("ò", "ó", $txt);
        $txt = str_replace("ù", "ú", $txt);

        $txt = str_replace("©", "@", $txt);

        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y reemplaza caracteres especiales por tags
     * Utilizado para enviar la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt	Texto a convertir
     * @return 	string				Texto convertido
     */
    public static function reemplazarEspeciales($txt) {

        //
        $txt = str_replace("\"", "[0]", $txt);
        $txt = str_replace("'", "[1]", $txt);
        $txt = str_replace("&", "[2]", $txt);
        // $txt = str_replace("?", "[3]", $txt);
        $txt = str_replace("á", "[4]", $txt);
        $txt = str_replace("é", "[5]", $txt);
        $txt = str_replace("í", "[6]", $txt);
        $txt = str_replace("ó", "[7]", $txt);
        $txt = str_replace("ú", "[8]", $txt);
        $txt = str_replace("ñ", "[9]", $txt);
        $txt = str_replace("Ñ", "[10]", $txt);
        // $txt = str_replace("+", "[11]", $txt);
        // $txt = str_replace("#", "[12]", $txt);
        $txt = str_replace("Á", "[13]", $txt);
        $txt = str_replace("É", "[14]", $txt);
        $txt = str_replace("Í", "[15]", $txt);
        $txt = str_replace("Ó", "[16]", $txt);
        $txt = str_replace("Ú", "[17]", $txt);
        $txt = str_replace("Ü", "[18]", $txt);
        $txt = str_replace("º", "[19]", $txt);
        $txt = str_replace("°", "[20]", $txt);
        //
        $txt = str_replace("ª", "[21]", $txt);
        $txt = str_replace("!", "[22]", $txt);
        $txt = str_replace("¡", "[23]", $txt);
        $txt = str_replace("'", "[24]", $txt);
        $txt = str_replace("´", "[25]", $txt);
        $txt = str_replace("`", "[26]", $txt);
        //
        $txt = str_replace("À", "[28]", $txt);
        $txt = str_replace("È", "[29]", $txt);
        $txt = str_replace("Ì", "[30]", $txt);
        $txt = str_replace("Ò", "[31]", $txt);
        $txt = str_replace("Ù", "[32]", $txt);
        //
        $txt = str_replace("à", "[33]", $txt);
        $txt = str_replace("è", "[34]", $txt);
        $txt = str_replace("ì", "[35]", $txt);
        $txt = str_replace("ò", "[36]", $txt);
        $txt = str_replace("ù", "[37]", $txt);

        //
        $txt = str_replace("©", "@", $txt);

        //             
        $txt = str_replace("[SALTOPARRAFO]", "", $txt);

        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y reemplaza caracteres especiales por tags
     * Utilizado para enviar la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt	Texto a convertir
     * @return 	string				Texto convertido
     */
    public static function reemplazarEspecialesDom($txt) {
        $txt = str_replace("&", "[2]", $txt);
        $txt = str_replace("á", "[4]", $txt);
        $txt = str_replace("é", "[5]", $txt);
        $txt = str_replace("í", "[6]", $txt);
        $txt = str_replace("ó", "[7]", $txt);
        $txt = str_replace("ú", "[8]", $txt);
        $txt = str_replace("ñ", "[9]", $txt);
        $txt = str_replace("Ñ", "[10]", $txt);
        $txt = str_replace("Á", "[13]", $txt);
        $txt = str_replace("É", "[14]", $txt);
        $txt = str_replace("Í", "[15]", $txt);
        $txt = str_replace("Ó", "[16]", $txt);
        $txt = str_replace("Ú", "[17]", $txt);
        $txt = str_replace("Ü", "[18]", $txt);
        $txt = str_replace("º", "[19]", $txt);
        $txt = str_replace("°", "[20]", $txt);
        //
        $txt = str_replace("ª", "[21]", $txt);
        // $txt = str_replace("!", "[22]", $txt);
        $txt = str_replace("¡", "[23]", $txt);
        $txt = str_replace("'", "[24]", $txt);
        $txt = str_replace("´", "[25]", $txt);
        $txt = str_replace("`", "[26]", $txt);
        //
        $txt = str_replace("À", "[28]", $txt);
        $txt = str_replace("È", "[29]", $txt);
        $txt = str_replace("Ì", "[30]", $txt);
        $txt = str_replace("Ò", "[31]", $txt);
        $txt = str_replace("Ù", "[32]", $txt);
        //
        $txt = str_replace("à", "[33]", $txt);
        $txt = str_replace("è", "[34]", $txt);
        $txt = str_replace("ì", "[35]", $txt);
        $txt = str_replace("ò", "[36]", $txt);
        $txt = str_replace("ù", "[37]", $txt);
        //
        return $txt;
    }

    public static function reemplazarEspecialesDomRee($txt) {
        $txt = str_replace("&", "[2]", $txt);
        $txt = str_replace("á", "[4]", $txt);
        $txt = str_replace("é", "[5]", $txt);
        $txt = str_replace("í", "[6]", $txt);
        $txt = str_replace("ó", "[7]", $txt);
        $txt = str_replace("ú", "[8]", $txt);
        $txt = str_replace("ñ", "[9]", $txt);
        $txt = str_replace("Ñ", "[10]", $txt);
        $txt = str_replace("Á", "[13]", $txt);
        $txt = str_replace("É", "[14]", $txt);
        $txt = str_replace("Í", "[15]", $txt);
        $txt = str_replace("Ó", "[16]", $txt);
        $txt = str_replace("Ú", "[17]", $txt);
        $txt = str_replace("Ü", "[18]", $txt);
        $txt = str_replace("º", "[19]", $txt);
        $txt = str_replace("°", "[20]", $txt);
        //
        $txt = str_replace("ª", "[21]", $txt);
        // $txt = str_replace("!", "[22]", $txt);
        $txt = str_replace("¡", "[23]", $txt);
        // $txt = str_replace("'", "[24]", $txt);
        $txt = str_replace("´", "[25]", $txt);
        $txt = str_replace("`", "[26]", $txt);
        //
        $txt = str_replace("À", "[28]", $txt);
        $txt = str_replace("È", "[29]", $txt);
        $txt = str_replace("Ì", "[30]", $txt);
        $txt = str_replace("Ò", "[31]", $txt);
        $txt = str_replace("Ù", "[32]", $txt);
        //
        $txt = str_replace("à", "[33]", $txt);
        $txt = str_replace("è", "[34]", $txt);
        $txt = str_replace("ì", "[35]", $txt);
        $txt = str_replace("ò", "[36]", $txt);
        $txt = str_replace("ù", "[37]", $txt);
        //
        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y sustituye los tags por caracteres
     * Utilizado para recibir la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt		Texto a convertir
     * @return 	string					RTexto convertido
     */
    public static function restaurarEspeciales($txt) {
        $txt = str_replace("[0]", "\"", $txt);
        $txt = str_replace("[1]", "'", $txt);
        $txt = str_replace("[2]", "&", $txt);
        $txt = str_replace("[3]", "?", $txt);
        $txt = str_replace("[4]", "á", $txt);
        $txt = str_replace("[5]", "é", $txt);
        $txt = str_replace("[6]", "í", $txt);
        $txt = str_replace("[7]", "ó", $txt);
        $txt = str_replace("[8]", "ú", $txt);
        $txt = str_replace("[9]", "ñ", $txt);
        $txt = str_replace("[10]", "Ñ", $txt);
        $txt = str_replace("[11]", "+", $txt);
        $txt = str_replace("[12]", "#", $txt);
        $txt = str_replace("[13]", "Á", $txt);
        $txt = str_replace("[14]", "É", $txt);
        $txt = str_replace("[15]", "Í", $txt);
        $txt = str_replace("[16]", "Ó", $txt);
        $txt = str_replace("[17]", "Ú", $txt);
        $txt = str_replace("[18]", "Ü", $txt);
        $txt = str_replace("[19]", "º", $txt);
        $txt = str_replace("[20]", "°", $txt);
        $txt = str_replace("[21]", "ª", $txt);
        //
        $txt = str_replace("[22]", "!", $txt);
        $txt = str_replace("[23]", "¡", $txt);
        $txt = str_replace("[24]", "'", $txt);
        $txt = str_replace("[25]", "´", $txt);
        $txt = str_replace("[26]", "`", $txt);
        //
        $txt = str_replace("[28]", "À", $txt);
        $txt = str_replace("[29]", "È", $txt);
        $txt = str_replace("[30]", "Ì", $txt);
        $txt = str_replace("[31]", "Ò", $txt);
        $txt = str_replace("[32]", "Ù", $txt);
        //
        $txt = str_replace("[33]", "à", $txt);
        $txt = str_replace("[34]", "è", $txt);
        $txt = str_replace("[35]", "ì", $txt);
        $txt = str_replace("[36]", "ò", $txt);
        $txt = str_replace("[37]", "ù", $txt);

        $txt = str_replace("[39]", "Ñ", $txt);
        //
        return $txt;
    }

    function restaurarEspecialesMayusculas($txt) {
        $txt = str_replace("[0]", "\"", $txt);
        $txt = str_replace("[1]", "'", $txt);
        $txt = str_replace("[2]", "&", $txt);
        $txt = str_replace("[3]", "?", $txt);
        $txt = str_replace("[4]", "Á", $txt);
        $txt = str_replace("[5]", "É", $txt);
        $txt = str_replace("[6]", "Í", $txt);
        $txt = str_replace("[7]", "Ó", $txt);
        $txt = str_replace("[8]", "Ú", $txt);
        $txt = str_replace("[9]", "Ñ", $txt);
        $txt = str_replace("[10]", "Ñ", $txt);
        $txt = str_replace("[11]", "+", $txt);
        $txt = str_replace("[12]", "#", $txt);
        $txt = str_replace("[13]", "Á", $txt);
        $txt = str_replace("[14]", "É", $txt);
        $txt = str_replace("[15]", "Í", $txt);
        $txt = str_replace("[16]", "Ó", $txt);
        $txt = str_replace("[17]", "Ú", $txt);
        $txt = str_replace("[18]", "Ü", $txt);
        $txt = str_replace("[19]", "º", $txt);
        $txt = str_replace("[20]", "°", $txt);
        $txt = str_replace("[21]", "ª", $txt);
        //
        $txt = str_replace("[22]", "!", $txt);
        $txt = str_replace("[23]", "¡", $txt);
        $txt = str_replace("[24]", "'", $txt);
        $txt = str_replace("[25]", "´", $txt);
        $txt = str_replace("[26]", "`", $txt);
        //
        $txt = str_replace("[28]", "À", $txt);
        $txt = str_replace("[29]", "È", $txt);
        $txt = str_replace("[30]", "Ì", $txt);
        $txt = str_replace("[31]", "Ò", $txt);
        $txt = str_replace("[32]", "Ù", $txt);
        //
        $txt = str_replace("[33]", "à", $txt);
        $txt = str_replace("[34]", "è", $txt);
        $txt = str_replace("[35]", "ì", $txt);
        $txt = str_replace("[36]", "ò", $txt);
        $txt = str_replace("[37]", "ù", $txt);

        $txt = str_replace("[39]", "Ñ", $txt);
        //
        return $txt;
    }

    public static function restaurarEspecialesSinTildes($txt) {
        $txt = str_replace("[0]", "\"", $txt);
        $txt = str_replace("[1]", "'", $txt);
        $txt = str_replace("[2]", "&", $txt);
        $txt = str_replace("[3]", "?", $txt);
        $txt = str_replace("[4]", "a", $txt);
        $txt = str_replace("[5]", "e", $txt);
        $txt = str_replace("[6]", "i", $txt);
        $txt = str_replace("[7]", "o", $txt);
        $txt = str_replace("[8]", "u", $txt);
        $txt = str_replace("[9]", "&ntilde;", $txt);
        $txt = str_replace("[10]", "&Ntilde;", $txt);
        $txt = str_replace("[11]", "+", $txt);
        $txt = str_replace("[12]", "#", $txt);
        $txt = str_replace("[13]", "A", $txt);
        $txt = str_replace("[14]", "E", $txt);
        $txt = str_replace("[15]", "I", $txt);
        $txt = str_replace("[16]", "O", $txt);
        $txt = str_replace("[17]", "U", $txt);
        $txt = str_replace("[18]", "Ü", $txt);
        $txt = str_replace("[19]", "º", $txt);
        $txt = str_replace("[20]", "°", $txt);
        //
        $txt = str_replace("[21]", "ª", $txt);
        $txt = str_replace("[22]", "!", $txt);
        $txt = str_replace("[23]", "¡", $txt);
        $txt = str_replace("[24]", "'", $txt);
        $txt = str_replace("[25]", "´", $txt);
        $txt = str_replace("[26]", "`", $txt);
        //
        $txt = str_replace("[28]", "A", $txt);
        $txt = str_replace("[29]", "E", $txt);
        $txt = str_replace("[30]", "I", $txt);
        $txt = str_replace("[31]", "O", $txt);
        $txt = str_replace("[32]", "U", $txt);
        //
        $txt = str_replace("[33]", "a", $txt);
        $txt = str_replace("[34]", "e", $txt);
        $txt = str_replace("[35]", "i", $txt);
        $txt = str_replace("[36]", "o", $txt);
        $txt = str_replace("[37]", "u", $txt);
        //
        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y sustituye los tags por caracteres
     * Utilizado para recibir la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt		Texto a convertir
     * @return 	string					RTexto convertido
     */
    public static function restaurarEspecialesRazonSocial($txt) {
        $txt = str_replace("[0]", "", $txt);
        $txt = str_replace("[1]", "", $txt);
        $txt = str_replace("[2]", "&", $txt);
        $txt = str_replace("[3]", "?", $txt);
        $txt = str_replace("[4]", "á", $txt);
        $txt = str_replace("[5]", "é", $txt);
        $txt = str_replace("[6]", "í", $txt);
        $txt = str_replace("[7]", "ó", $txt);
        $txt = str_replace("[8]", "ú", $txt);
        $txt = str_replace("[9]", "ñ", $txt);
        $txt = str_replace("[10]", "Ñ", $txt);
        $txt = str_replace("[11]", "+", $txt);
        $txt = str_replace("[12]", "#", $txt);
        $txt = str_replace("[13]", "Á", $txt);
        $txt = str_replace("[14]", "É", $txt);
        $txt = str_replace("[15]", "Í", $txt);
        $txt = str_replace("[16]", "Ó", $txt);
        $txt = str_replace("[17]", "Ú", $txt);
        $txt = str_replace("[18]", "Ü", $txt);
        $txt = str_replace("[19]", "º", $txt);
        $txt = str_replace("[20]", "°", $txt);
        //
        $txt = str_replace("[21]", "ª", $txt);
        $txt = str_replace("[22]", "!", $txt);
        $txt = str_replace("[23]", "¡", $txt);
        $txt = str_replace("[24]", "'", $txt);
        $txt = str_replace("[25]", "´", $txt);
        $txt = str_replace("[26]", "`", $txt);
        //
        $txt = str_replace("[28]", "À", $txt);
        $txt = str_replace("[29]", "È", $txt);
        $txt = str_replace("[30]", "Ì", $txt);
        $txt = str_replace("[31]", "Ò", $txt);
        $txt = str_replace("[32]", "Ù", $txt);
        //
        $txt = str_replace("[33]", "à", $txt);
        $txt = str_replace("[34]", "è", $txt);
        $txt = str_replace("[35]", "ì", $txt);
        $txt = str_replace("[36]", "ò", $txt);
        $txt = str_replace("[37]", "ù", $txt);
        //
        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y reemplaza caracteres especiales por tags
     * Utilizado para enviar la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt	Texto a convertir
     * @return 	string				Texto convertido
     */
    public static function reemplazarHtmlPdf($txt) {
        $txt = strip_tags($txt, "<p><ul><il>");
        $txt = str_replace("&nbsp;", " ", $txt);
        $txt = str_replace("&aacute;", "á", $txt);
        $txt = str_replace("&eacute;", "é", $txt);
        $txt = str_replace("&iacute;", "í", $txt);
        $txt = str_replace("&oacute;", "ó", $txt);
        $txt = str_replace("&uacute;", "ú", $txt);
        $txt = str_replace("&ntilde;", "ñ", $txt);
        $txt = str_replace("&Aacute;", "Á", $txt);
        $txt = str_replace("&Eacute;", "É", $txt);
        $txt = str_replace("&Iacute;", "Í", $txt);
        $txt = str_replace("&Oacute;", "Ó", $txt);
        $txt = str_replace("&Uacute;", "Ú", $txt);
        $txt = str_replace("&Ntilde;", "Ñ", $txt);
        $txt = str_replace("<p>", "", $txt);
        $txt = str_replace("</p>", chr(13) . chr(10), $txt);
        $txt = str_replace("<ul>", "*", $txt);
        $txt = str_replace("<il>", "*", $txt);
        return $txt;
    }

    public static function reemplazarHtml($txt) {
        $txt = str_replace("á", "&aacute;", $txt);
        $txt = str_replace("é", "&eacute;", $txt);
        $txt = str_replace("í", "&iacute;", $txt);
        $txt = str_replace("ó", "&oacute;", $txt);
        $txt = str_replace("ú", "&uacute;", $txt);
        $txt = str_replace("ñ", "&ntilde;", $txt);
        $txt = str_replace("Á", "&Aacute;", $txt);
        $txt = str_replace("É", "&Eacute;", $txt);
        $txt = str_replace("Í", "&Iacute;", $txt);
        $txt = str_replace("Ó", "&Oacute;", $txt);
        $txt = str_replace("Ú", "&Uacute;", $txt);
        $txt = str_replace("Ñ", "&Ntilde;", $txt);
        return $txt;
    }

    public static function quitarTildes($txt) {
        $txt = str_replace("á", "a", $txt);
        $txt = str_replace("é", "e", $txt);
        $txt = str_replace("í", "i", $txt);
        $txt = str_replace("ó", "o", $txt);
        $txt = str_replace("ú", "u", $txt);
        $txt = str_replace("ñ", "n", $txt);
        $txt = str_replace("Á", "A", $txt);
        $txt = str_replace("É", "E", $txt);
        $txt = str_replace("Í", "I", $txt);
        $txt = str_replace("Ó", "O", $txt);
        $txt = str_replace("Ú", "U", $txt);
        $txt = str_replace("Ñ", "N", $txt);
        return $txt;
    }

    public static function serializacionLineal($arr) {
        $sal = '';
        foreach ($arr as $key => $datos) {
            if (!is_array($datos)) {
                $sal .= '<' . $key . '><![CDATA[' . $datos . ']]></' . $key . '>';
            } else {
                $sal .= '<' . $key . '>';
                foreach ($datos as $key1 => $datos1) {
                    if (!is_array($datos1)) {
                        $sal .= '<' . $key1 . '><![CDATA[' . $datos1 . ']]></' . $key1 . '>';
                    } else {
                        $sal .= '<' . $key1 . '>';
                        foreach ($datos1 as $key2 => $datos2) {
                            if (!is_array($datos2)) {
                                $sal .= '<' . $key2 . '><![CDATA[' . $datos2 . ']]></' . $key2 . '>';
                            } else {
                                $sal .= '<' . $key2 . '>';
                                foreach ($datos2 as $key3 => $datos3) {
                                    $sal .= '<' . $key3 . '><![CDATA[' . $datos3 . ']]></' . $key3 . '>';
                                }
                                $sal .= '</' . $key2 . '>';
                            }
                        }
                        $sal .= '</' . $key1 . '>';
                    }
                }
                $sal .= '</' . $key . '>';
            }
        }
        return $sal;
    }

    public static function serializarExpedienteMatricula($dbx, $numrec = '', $datos = array(), $reemplazar = 'si', $extendido = 'si') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $xml = \funcionesRegistrales::serializarExpedienteMatricula($dbx, $numrec, $datos, $reemplazar, $extendido);
        return $xml;
    }

    public static function serializarExpedienteProponente($dbx, $data = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $xml = \funcionesRegistrales::serializarExpedienteProponente($dbx, $data);
        return $xml;
    }

    public static function tamano80SentarPagoSii($txt) {
        $salida = '';
        if (strlen($txt) > 78) {
            $salida = substr($txt, 0, 78);
        } else {
            $salida = sprintf("%-78s", $txt);
        }
        return $salida;
    }

    //
    public static function tamanoArchivo($file) {
        if (!file_exists($file)) {
            return 0;
        } else {
            return (filesize($file));
        }
    }

    public static function truncateFloat($number, $digitos, $pd = '.', $pm = ',') {
        $raiz = 10;
        $multiplicador = pow($raiz, $digitos);
        $resultado = ((int) ($number * $multiplicador)) / $multiplicador;
        $x = number_format($resultado, $digitos, $pd, $pm);
        $x = str_replace(",", "", $x);
        return $x;
    }

    public static function truncateFloatForm($number, $digitos, $pd = '.', $pm = ',') {
        $raiz = 10;
        $multiplicador = pow($raiz, $digitos);
        $resultado = ((int) ($number * $multiplicador)) / $multiplicador;
        $x = number_format($resultado, $digitos, $pd, $pm);
        return $x;
    }

    public static function truncateFinancialIndexes($number) {
        $sep = explode(",", $number);
        if (isset($sep[1])) {
            if (strlen($sep[1]) == 1) {
                $number = $sep[0] . ',' . $sep[1] . '0';
            }
            if (strlen($sep[1]) > 2) {
                $number = $sep[0] . ',' . substr($sep[1], 0, 2);
            }
        }
        return $number;
    }

    public static function retornarExpedienteProponenteSii($dbx = null, $prop, $mat = '', $tipotramite = '', $proceso = '', $origen = '', $retornarInhabilidad = 'si', $retornarRee = 'si') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $retorno = \funcionesRegistrales::retornarExpedienteProponente($dbx, $prop, $mat, $tipotramite, $proceso, $origen, $retornarInhabilidad, $retornarRee);
        return $retorno;
    }

    //

    /**
     * 
     * @param type $dbx
     * @param type $mat
     * @param type $idclase
     * @param type $numid
     * @param type $namex
     * @param type $tipodata
     * @param type $tipoconsulta
     * @return boolean|int
     */
    public static function retornarExpedienteMercantilSii($dbx = null, $mat = '', $idclase = '', $numid = '', $namex = '', $tipodata = 'E', $tipoconsulta = 'T', $establecimientosnacionales = 'N') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $retorno = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $mat, $idclase, $numid, $namex, $tipodata, $tipoconsulta, $establecimientosnacionales);
        return $retorno;
    }

    public static function retornarDescripcionCiiu($dbx, $ciiu = '', $version = '*') {
        if ($version == '*' || $version == '4') {
            $result = retornarRegistroMysqli2($dbx, 'bas_ciius', "idciiu='" . trim($ciiu) . "'");
        }
        if ($version == '3.1') {
            $result = retornarRegistroMysqli2($dbx, 'bas_ciius_3_1', "idciiu='" . trim($ciiu) . "'");
        }

        $retornar = '';
        if ($result && !empty($result)) {
            $retornar = $result["descripcion"];
        }
        unset($result);
        return $retornar;
    }

    public static function retornarGrupoNiifFormulario($dbx, $id) {
        $res = retornarRegistroMysqli2($dbx, 'bas_gruponiif', "id='" . $id . "'");
        return $res["idformulario"];
    }

    public static function retornarGrupoNiifFormularioDescripcion($dbx, $id) {
        $res = retornarRegistroMysqli2($dbx, 'bas_gruponiif', "id='" . $id . "'");
        return str_replace(array("1.- ", "2.- ", "3.- ", "4.- ", "5.- ", "6.- ", "7.- "), "", $res["descripcion"]);
    }

    public static function imprimeArray($arreglo = array(), $finaliza = true) {
        echo "<pre>";
        print_r($arreglo);
        echo "</pre>";

        if ($finaliza) {
            die();
        }
    }

    public static function retornarMregLiquidacionSii($dbx, $numliq, $tipo = 'L') {

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
        $respuesta["tiporenovacion"] = '';
        $respuesta["origen"] = '';
        $respuesta["iptramite"] = '';
        $respuesta["idestado"] = '';
        $respuesta["txtestado"] = '';
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

        // En caso de trámites rues
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
        $respuesta["firmadomanuscrita"] = '';
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

        //
        $respuesta["activosbase"] = '';
        $respuesta["personalbase"] = '';

        // 2016-04-08: JINT:
        // Asigna el tipo de trámite inicial
        // 
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
                $_SESSION["entrada"]["idliquidacion"] = $arrLiq["idliquidacion"];
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
        $respuesta["idliquidacion"] = $arrLiq["idliquidacion"];
        $respuesta["numeroliquidacion"] = $arrLiq["idliquidacion"];
        $respuesta["fecha"] = $arrLiq["fecha"];
        $respuesta["hora"] = $arrLiq["hora"];
        $respuesta["fechaultimamodificacion"] = $arrLiq["fechaultimamodificacion"];
        $respuesta["idusuario"] = $arrLiq["idusuario"];
        $respuesta["tipotramite"] = $arrLiq["tipotramite"];
        $respuesta["iptramite"] = $arrLiq["iptramite"];
        $respuesta["idestado"] = $arrLiq["idestado"];
        $respuesta["txtestado"] = retornarRegistroMysqli2($dbx, "mreg_liquidacionestados", "id='" . $arrLiq["idestado"] . "'", "descripcion");

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
        $respuesta["firmadomanuscrita"] = $arrLiq["firmadomanuscrita"];

        $respuesta["cumplorequisitosbenley1780"] = $arrLiq["cumplorequisitosbenley1780"];
        $respuesta["mantengorequisitosbenley1780"] = $arrLiq["mantengorequisitosbenley1780"];
        $respuesta["renunciobeneficiosley1780"] = $arrLiq["renunciobeneficiosley1780"];
        $respuesta["controlactividadaltoimpacto"] = $arrLiq["controlactividadaltoimpacto"];
        $respuesta["multadoponal"] = $arrLiq["multadoponal"];

        //
        $respuesta["rues_claseidentificacion "] = '';

        /*
         * LLena los siguientes campos a partir de mreg_liquidacion_campos
         * 
         * - subtipotramite
         * - origen
         * - tipoproponente
         * - benley1780
         * 
         * - tipocliente
         * - razonsocialcliente
         * - nombre1cliente
         * - nombre2cliente
         * - apellido1cliente
         * - apellido2cliente
         * 
         * - tipopagador
         * - razonsocialpagador
         * - nombre1pagador
         * - nombre2pagador
         * - apellido1pagador
         * - apellido2pagador
         * 
         * - nombrepjur
         * - nombresuc
         * - nombreage
         * 
         * - actpjur
         * - actsuc
         * - actage
         * 
         * - perpejur
         * 
         * - munpnat
         * - munest
         * - munpjur
         * - munsuc
         * - munage
         * 
         * - orgpnat
         * - orgpjur
         * - orgsuc
         * - orgage
         * 
         * - fecrencambidom
         * 
         * - opcionafiliado
         * - saldoafiliado
         * - matriculaafiliado
         * - ultanorenafi
         * 
         * - nombre1repleg
         * - nombre2repleg
         * - apellido1repleg
         * - apellido2repleg
         * - cargorepleg
         * - emailrepleg
         * - firmarepleg
         * - celularrepleg
         * 
         * - tipolibro
         * - codigolibro
         * - primeravez
         * - confirmadigital
         * 
         * - iderevfis
         * - nombre1revfis
         * - nombre2revfis
         * - apellido1revfis
         * - apellido2revfis
         * - cargorevfis
         * - emailrevfis
         * - firmarevfis
         * - celularrevfis
         *
         * - idepreasa
         * - nombre1preasa
         * - nombre2preasa
         * - apellido1preasa
         * - apellido2preasa
         * - cargopreasa
         * - emailpreasa
         * - firmopreasa
         * - celularpreasa
         * 
         * - idesecasa
         * - nombre1secasa
         * - nombre2secasa
         * - apellido1secasa
         * - apellido2secasa
         * - cargosecasa
         * - emailsecasa
         * - firmosecasa
         * - celularsecasa
         * 
         * - tipoidentificacionaceptante
         * - identificacionaceptante
         * - nombreaceptante
         * - cargoaceptante
         * - fechadocideaceptante
         * 
         * - motivocorreccion
         * - tipoerror1
         * - tipoerror2
         * - tipoerror3
         * 
         * - descripcionembargo
         * - descripciondesembargo
         * - tipoidentificaciondemandante
         * - identificaciondemandante
         * - nombredemandante
         * - libro
         * - numreg
         * 
         * - descripcionpqr
         * - tipoidentificacionpqr
         * - nombrepqr
         * - emailpqr
         * - telefonopqr
         * - celularpqr
         * 
         * - descripcionrr
         * - tipoidentificacionrr
         * - nombrerr
         * - emailrr
         * - telefonorr
         * - celularrr    
         * 
         * - tipocertificado
         * - explicacion
         * - textolibre
         * 
         * - modcom
         * - modnot
         * - modciiu
         * - modnombre
         * - matriculabase
         * - nom1base
         * - nom2base
         * - ape1base
         * - ape2base
         * - matriculabase
         * 
         * - ant_dircom
         * - ant_telcom1
         * - ant_telcom2
         * - ant_faxcom
         * - ant_celcom
         * - ant_muncom
         * - ant_barriocom
         * - ant_numpredialcom
         * - ant_emailcom
         * - ant_emailcom2
         * - ant_emailcom3
         * - ant_dirnot
         * - ant_telnot1
         * - ant_telnot2
         * - ant_faxnot
         * - ant_celnot
         * - ant_munnot
         * - ant_barrionot
         * - ant_emailnot
         * - ant_vesionciiu
         * - ant_ciiu11
         * - ant_ciiu12
         * - ant_ciiu13
         * - ant_ciiu14
         * - ant_ciiu21
         * - ant_ciiu22
         * - ant_ciiu23
         * - ant_ciiu24
         * 
         * - dircom
         * - telcom1
         * - telcom2
         * - faxcom
         * - celcom
         * - muncom
         * - barriocom
         * - numpredialcom
         * - emailcom
         * - emailcom2
         * - emailcom3
         * - dirnot
         * - telnot1
         * - telnot2
         * - faxnot
         * - celnot
         * - munnot
         * - barrionot
         * - emailnot
         * - nombreanterior
         * - nombrenuevo
         * - vesionciiu
         * - ciiu11
         * - ciiu12
         * - ciiu13
         * - ciiu14
         * - ciiu21
         * - ciiu22
         * - ciiu23
         * - ciiu24
         * 
         * - rues_numerointerno
         * - rues_numerounico
         * - rues_camarareceptora
         * - rues_camararesponsable
         * - rues_matricula
         * - rues_proponente
         * - rues_nombreregistrado
         * - rues_claseidentificacion
         * - rues_numeroidentificacion
         * - rues_dv
         * - rues_estado_liquidacion
         * - rues_estado_transaccion
         * - rues_nombrepagador
         * - rues_origendocumento
         * - rues_fechadocumento
         * - rues_fechapago
         * - rues_numerofactura
         * - rues_referenciaoperacion
         * - rues_totalpagado
         * - rues_formapago
         * - rues_indicadororigen
         * - rues_empleados
         * - rues_indicadorbeneficio
         * - rues_fecharespuesta
         * - rues_horarespuesta
         * - rues_codigoerror
         * - rues_mensajeerror
         * - rues_firmadigital
         * - rues_caracteres_por_linea
         * 
         * - firmadoelectronicamente (Se excluye de esta lista)
         * - tipoidefirmante
         * - identificacionfirmante
         * - apellido1firmante
         * - apellido2firmante
         * - nombre1firmante
         * - nombre2firmante
         * - emailfirmante
         * - emailfirmanteseguimiento
         * - celularfirmante
         * - direccionfirmante
         * - municipiofirmante
         * 
         * - emailcontactoasesoria
         * - comentariosasesoria
         * 
         * - incrementocupocertificados
         * - cobrarmutacion
         */
        $temCampos = retornarRegistrosMysqli2($dbx, 'mreg_liquidacion_campos', "idliquidacion=" . $numliq);

        if (!empty($temCampos)) {
            foreach ($temCampos as $c) {
                if ($c["campo"] == 'incrementocupocertificados') {
                    $respuesta[$c["campo"]] = intval(trim($c["contenido"]));
                } else {
                    if ($c["campo"] != 'firmadoelectronicamente') {
                        $respuesta[$c["campo"]] = trim($c["contenido"]);
                    }
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

        // Arma arreglo de la liquidación
        $arrDet = retornarRegistrosMysqli2($dbx, "mreg_liquidaciondetalle", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"], "idsec");
        foreach ($arrDet as $lin) {
            $renglon = array();
            $renglon["idsec"] = sprintf("%03s", $lin["idsec"]);
            $renglon["idservicio"] = $lin["idservicio"];
            $renglon["txtservicio"] = retornarRegistroMysqli2($dbx, "mreg_servicios", "idservicio='" . $lin["idservicio"] . "'", "nombre");
            if (!isset($lin["cc"])) {
                $lin["cc"] = '';
            }
            $renglon["cc"] = $lin["cc"];
            //$renglon["expediente"] = $lin["cc"].' - '.$lin["expediente"];
            $renglon["expediente"] = $lin["expediente"];
            $renglon["nombre"] = $lin["nombre"];
            $renglon["ano"] = $lin["ano"];
            $renglon["cantidad"] = $lin["cantidad"];
            $renglon["valorbase"] = $lin["valorbase"];
            $renglon["porcentaje"] = $lin["porcentaje"];
            $renglon["valorservicio"] = $lin["valorservicio"];
            $renglon["benart7"] = $lin["benart7"];
            $renglon["benley1780"] = $lin["benley1780"];
            $renglon["reliquidacion"] = $lin["reliquidacion"];
            $renglon["serviciobase"] = $lin["serviciobase"];
            $renglon["pagoafiliacion"] = $lin["pagoafiliacion"];
            $renglon["ir"] = $lin["ir"];
            $renglon["iva"] = $lin["iva"];
            $renglon["idalerta"] = $lin["idalerta"];
            $respuesta["liquidacion"][] = $renglon;
        }
        unset($arrDet);

        // Arma arreglo de la liquidación RUES
        $arrDet = retornarRegistrosMysqli2($dbx, "mreg_liquidaciondetalle_rues", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"], "secuencia");
        foreach ($arrDet as $lin) {
            $renglon = array();
            $renglon["codigo_servicio"] = $lin["codigo_servicio"];
            $renglon["descripcion_servicio"] = $lin["descripcion_servicio"];
            $renglon["orden_servicio"] = $lin["orden_servicio"];
            $renglon["orden_servicio_asociado"] = $lin["orden_servicio_asociado"];
            $renglon["nombre_base"] = $lin["nombre_base"];
            $renglon["valor_base"] = $lin["valor_base"];
            $renglon["valor_liquidacion"] = $lin["valor_liquidacion"];
            $renglon["cantidad_servicio"] = $lin["cantidad_servicio"];
            $renglon["indicador_base"] = $lin["indicador_base"];
            $renglon["indicador_renovacion"] = $lin["indicador_renovacion"];
            $renglon["matricula_servicio"] = $lin["matricula_servicio"];
            $renglon["nombre_matriculado"] = $lin["nombre_matriculado"];
            $renglon["ano_renovacion"] = $lin["ano_renovacion"];
            $renglon["valor_activos_sin_ajustes"] = $lin["valor_activos_sin_ajustes"];
            $respuesta["rues_servicios"][] = $renglon;
        }
        unset($arrDet);

        // Arma arreglo de expedientes
        $temx = retornarRegistrosMysqli2($dbx, "mreg_liquidacionexpedientes", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
        if ($temx === false) {
            $arrExp = false;
        } else {
            $arrExp = array();
            $i = -1;
            foreach ($temx as $res) {
                $i++;
                $arrExp[$i] = $res;
                if ($arrExp[$i]["registrobase"] == 'S') {
                    if (trim($arrExp[$i]["primeranorenovado"]) == '') {
                        $arrExp[$i]["primeranorenovado"] = $arrExp[$i]["ultimoanorenovado"];
                    }
                }
            }
        }
        unset($temx);

        if ($arrExp) {
            $i = 0;
            foreach ($arrExp as $lin) {
                $renglon = array();
                $i++;
                if (!isset($lin["cc"])) {
                    $lin["cc"] = '';
                }
                $renglon["cc"] = $lin["cc"];
                $renglon["matricula"] = $lin["matricula"];
                $renglon["proponente"] = $lin["proponente"];
                $renglon["numrue"] = $lin["numrue"];
                $renglon["idtipoidentificacion"] = $lin["idtipoidentificacion"];
                $renglon["identificacion"] = $lin["identificacion"];
                $renglon["razonsocial"] = $lin["razonsocial"];
                $renglon["ape1"] = $lin["ape1"];
                $renglon["ape2"] = $lin["ape2"];
                $renglon["nom1"] = $lin["nom1"];
                $renglon["nom2"] = $lin["nom2"];
                $renglon["organizacion"] = $lin["organizacion"];
                $renglon["txtorganizacion"] = retornarRegistroMysqli2($dbx, "bas_organizacionjuridica", "id='" . $lin["organizacion"] . "'", "descripcion");
                $renglon["categoria"] = $lin["categoria"];
                $renglon["txtcategoria"] = '';
                if ($lin ["organizacion"] != '01' && $lin ["organizacion"] != '02') {
                    $renglon["txtcategoria"] = retornarRegistroMysqli2($dbx, "bas_categorias", "id='" . $lin["categoria"] . "'", "descripcion");
                }
                $renglon["afiliado"] = $lin["afiliado"];
                $renglon["propietariojurisdiccion"] = $lin["propietariojurisdiccion"];
                $renglon["ultimoanoafiliado"] = $lin["ultimoanoafiliado"];
                $renglon["primeranorenovado"] = $lin["primeranorenovado"];
                $renglon["ultimoanorenovado"] = $lin["ultimoanorenovado"];
                $renglon["ultimosactivos"] = $lin["ultimosactivos"];
                $renglon["nuevosactivos"] = $lin["nuevosactivos"];
                $renglon["actividad"] = $lin["actividad"];
                $renglon["registrobase"] = $lin["registrobase"];
                $renglon["benart7"] = $lin["benart7"];
                $renglon["benley1780"] = $lin["benley1780"];
                $renglon["fechanacimiento"] = $lin["fechanacimiento"];
                $renglon["renovaresteano"] = $lin["renovaresteano"];
                $renglon["fechamatricula"] = $lin["fechamatricula"];
                $renglon["fecmatant"] = $lin["fecmatant"];
                $renglon["reliquidacion"] = $lin["reliquidacion"];
                $renglon["controlpot"] = $lin["controlpot"];
                $respuesta["expedientes"][] = $renglon;
            }
        }
        unset($arrExp);

        // Arma arreglo de transacciones
        $arrTra = retornarRegistrosMysqli2($dbx, 'mreg_liquidacion_transacciones', "idliquidacion=" . $respuesta["numeroliquidacion"], "idsecuencia");
        foreach ($arrTra as $tra) {
            $respuesta["transacciones"][] = $tra;
        }
        unset($arrTra);

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


        return $respuesta;
    }

    public static function retornarMregSecuencia($dbx, $clave) {
        $arrTem = retornarRegistroMysqli2($dbx, 'mreg_secuencias', "id='" . $clave . "'");
        if ($arrTem === false || empty($arrTem)) {
            return 0;
        } else {
            return $arrTem["secuencia"];
        }
    }

    public static function retornarMregSecuenciaMaxAsentarReciboSii($dbx, $clave) {

        //
        if ($clave == 'RECIBOS-NORMALES') {
            $query = "select max(recibo) as a from mreg_recibosgenerados where substring(recibo,1,1) = 'S'";
        }
        if ($clave == 'RECIBOS-NOTAS') {
            $query = "select max(recibo) as a from mreg_recibosgenerados where substring(recibo,1,1) = 'M'";
        }
        if ($clave == 'RECIBOS-GA') {
            $query = "select max(recibo) as a from mreg_recibosgenerados where substring(recibo,1,1) = 'H'";
        }
        if ($clave == 'RECIBOS-CO') {
            $query = "select max(recibo) as a from mreg_recibosgenerados where substring(recibo,1,1) = 'D'";
        }

        //
        $temx = ejecutarQueryMysqli2($dbx, $query);
        $siguiente = ltrim(substr($temx[1]["a"], 1), "0");

        //
        return $siguiente;
    }

    public static function retornarNombrePais($dbx, $id) {

        if (is_numeric($id)) {
            $reg = retornarRegistroMysqli2($dbx, 'bas_paises', "codnumpais='" . $id . "'");
            if ($reg && !empty($reg)) {
                return $reg["nombrepais"];
            } else {
                return "";
            }
        } else {
            $reg = retornarRegistroMysqli2($dbx, 'bas_paises', "idpais='" . $id . "'");
            if ($reg && !empty($reg)) {
                return $reg["nombrepais"];
            } else {
                return "";
            }
        }
    }

    public static function retornarNombrePaisAbreviado($dbx, $id) {

        if (is_numeric($id)) {
            $reg = retornarRegistroMysqli2($dbx, 'bas_paises', "codnumpais='" . $id . "'");
            if ($reg && !empty($reg)) {
                return $reg["idpais"];
            } else {
                return "";
            }
        } else {
            return $id;
        }
    }

    public static function validarFecha($dsfecha) {

        if (strlen($dsfecha) < 8) {
            return false;
        } else {
            if (substr($dsfecha, 0, 4) < "1800") {
                return false;
            } else {
                $ano = substr($dsfecha, 0, 4);
                if ((substr($dsfecha, 4, 2) < "01") || (substr($dsfecha, 4, 2) > "12")) {
                    return false;
                } else {
                    $mal = "0";
                    if ((substr($dsfecha, 4, 2) == "01") ||
                            (substr($dsfecha, 4, 2) == "03") ||
                            (substr($dsfecha, 4, 2) == "05") ||
                            (substr($dsfecha, 4, 2) == "07") ||
                            (substr($dsfecha, 4, 2) == "08") ||
                            (substr($dsfecha, 4, 2) == "10") ||
                            (substr($dsfecha, 4, 2) == "12")) {
                        if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "31")) {
                            $mal = "1";
                        }
                    }
                    if ((substr($dsfecha, 4, 2) == "04") ||
                            (substr($dsfecha, 4, 2) == "06") ||
                            (substr($dsfecha, 4, 2) == "09") ||
                            (substr($dsfecha, 4, 2) == "11")) {
                        if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "30")) {
                            $mal = "1";
                        }
                    }
                    if (substr($dsfecha, 4, 2) == "02") {
                        if (($ano % 4 == 0) && (($ano % 100 != 0) || ($ano % 400 == 0))) {
                            if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "29")) {
                                $mal = "1";
                            }
                        } else {
                            if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "28")) {
                                $mal = "1";
                            }
                        }
                    }
                    if ($mal == "1") {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }
    }

    public static function encomillar($cadenaBusq = '') {
        if (trim($cadenaBusq) != '') {
            $arrBusqueda = explode(",", $cadenaBusq);

            $arrSal = array();
            foreach ($arrBusqueda as $reg) {
                $arrSal[] = "'" . $reg . "'";
            }
            return implode(",", $arrSal);
        } else {
            return '';
        }
    }

    public static function validacionFormularioGrabado($dbx, $mat) {
        $temValid = retornarRegistroMysqli2($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION ["tramite"] ["numeroliquidacion"] . " and expediente='" . $mat . "'");
        if ($temValid === false || empty($temValid)) {
            return false;
        } else {
            $datos = \funcionesSii2_desserializaciones::desserializarExpedienteMatricula($dbx, $temValid["xml"]);
            if ($datos["organizacion"] == '02' || $datos["categoria"] == '2' || $datos["categoria"] == '3') {
                return true;
            } else {
                if (floatval($datos["acttot"]) != floatval($datos["paspat"])) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    public static function consumirWsConsultarRutaSii2($dbx, $codbarras) {

        if (trim($codbarras) == '') {
            return false;
        }

        //
        $arrTem = retornarRegistroMysqli2($dbx, 'mreg_est_codigosbarras', "codigobarras='" . $codbarras . "'");
        $arrTemRec = retornarRegistroMysqli2($dbx, 'mreg_recibosgenerados', "codigobarras='" . $codbarras . "'");
        $arrTemRad = retornarRegistroMysqli2($dbx, 'mreg_rue_radicacion', "codigobarras='" . $codbarras . "'");
        if (ltrim(trim($codbarras), "0") != '') {
            $arrTemliq = retornarRegistroMysqli2($dbx, 'mreg_liquidacion', "numeroradicacion='" . $codbarras . "'");
        } else {
            $arrTemliq = false;
        }
        //
        $emails = array();
        $tels = array();

        $emailRadicado = '';
        $celularRadicado = '';
        if (($arrTem["actoreparto"] == '07') || ($arrTem["actoreparto"] == '29')) {
            $emailRadicado = $arrTemRec["email"];
            $celularRadicado = $arrTemRec["telefono2"];
        }

        //
        if ($arrTemRec["email"] != '') {
            $emails[$arrTemRec["email"]] = $arrTemRec["email"];
        }
        if ($arrTemRec["telefono1"] != '' && strlen($arrTemRec["telefono1"]) == 10 && substr($arrTemRec["telefono1"], 0, 1) == '3') {
            $tels[$arrTemRec["telefono1"]] = $arrTemRec["telefono1"];
        }
        if ($arrTemRec["telefono2"] != '' && strlen($arrTemRec["telefono2"]) == 10 && substr($arrTemRec["telefono2"], 0, 1) == '3') {
            if (!in_array($arrTemRec["telefono2"], $tels)) {
                $tels[$arrTemRec["telefono2"]] = $arrTemRec["telefono2"];
            }
        }

        if (ltrim(trim($arrTem["matricula"]), "0") != '') {
            $arrTemMat = retornarRegistroMysqli2($dbx, 'mreg_est_inscritos', "matricula='" . ltrim(trim($arrTem["matricula"]), "0") . "'");
            if ($arrTemMat && !empty($arrTemMat)) {
                if (trim($arrTemMat["emailcom"]) != '') {
                    $emails[$arrTemMat["emailcom"]] = $arrTemMat["emailcom"];
                }
                if (trim($arrTemMat["emailcom2"]) != '') {
                    $emails[$arrTemMat["emailcom2"]] = $arrTemMat["emailcom2"];
                }
                if (trim($arrTemMat["emailcom3"]) != '') {
                    $emails[$arrTemMat["emailcom3"]] = $arrTemMat["emailcom3"];
                }
                if (trim($arrTemMat["emailnot"]) != '') {
                    $emails[$arrTemMat["emailnot"]] = $arrTemMat["emailnot"];
                }
                if (trim($arrTemMat["telcom1"]) != '' && strlen($arrTemMat["telcom1"]) == 10 && substr($arrTemMat["telcom1"], 0, 1) == '3') {
                    $tels[$arrTemMat["telcom1"]] = $arrTemMat["telcom1"];
                }
                if (trim($arrTemMat["telcom2"]) != '' && strlen($arrTemMat["telcom2"]) == 10 && substr($arrTemMat["telcom2"], 0, 1) == '3') {
                    $tels[$arrTemMat["telcom2"]] = $arrTemMat["telcom2"];
                }
                if (trim($arrTemMat["telcom3"]) != '' && strlen($arrTemMat["telcom3"]) == 10 && substr($arrTemMat["telcom3"], 0, 1) == '3') {
                    $tels[$arrTemMat["telcom3"]] = $arrTemMat["telcom3"];
                }
                if (trim($arrTemMat["telnot"]) != '' && strlen($arrTemMat["telnot"]) == 10 && substr($arrTemMat["telnot"], 0, 1) == '3') {
                    $tels[$arrTemMat["telnot"]] = $arrTemMat["telnot"];
                }
                if (trim($arrTemMat["telnot2"]) != '' && strlen($arrTemMat["telnot2"]) == 10 && substr($arrTemMat["telnot2"], 0, 1) == '3') {
                    $tels[$arrTemMat["telnot2"]] = $arrTemMat["telnot2"];
                }
                if (trim($arrTemMat["telnot3"]) != '' && strlen($arrTemMat["telnot3"]) == 10 && substr($arrTemMat["telnot3"], 0, 1) == '3') {
                    $tels[$arrTemMat["telnot3"]] = $arrTemMat["telnot3"];
                }
            }

            $arrTemCC = retornarRegistrosMysqli2($dbx, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim(trim($arrTem["matricula"]), "0") . "'", "id");
            foreach ($arrTemCC as $cc) {
                if ($cc["campo"] == "EMAILNOT-ANTERIOR" && trim($cc["contenido"]) != '') {
                    $emails[$cc["contenido"]] = $cc["contenido"];
                    $emailnotant = trim($cc["contenido"]);
                }
                if ($cc["campo"] == "EMAILCOM-ANTERIOR" && trim($cc["contenido"]) != '') {
                    $emails[$cc["contenido"]] = $cc["contenido"];
                    $emailcomant = trim($cc["contenido"]);
                }
            }

            // Incluir la búsqueda sobre mreg_campos_historicos_AAAA
        }

        //
        $retorno = array();
        $retorno["codbarras"] = $codbarras;
        $retorno["operacion"] = $arrTem["operacion"];
        $retorno["matricula"] = $arrTem["matricula"];
        $retorno["proponente"] = $arrTem["proponente"];
        $retorno["idclase"] = $arrTem["idclase"];
        $retorno["numid"] = $arrTem["numid"];
        $retorno["nombre"] = $arrTem["nombre"];
        $retorno["organizacion"] = '';
        $retorno["tipotramite"] = $arrTemliq["tipotramite"];
        $retorno["idliquidacion"] = $arrTemliq["idliquidacion"];
        $retorno["fecha"] = $arrTem["fecharadicacion"];
        $retorno["tipodoc"] = $arrTem["tipdoc"];
        $retorno["numdoc"] = $arrTem["numdoc"];
        $retorno["fechadoc"] = $arrTem["fecdoc"];
        $retorno["mundoc"] = $arrTem["mundoc"];
        $retorno["txtorigendoc"] = $arrTem["oridoc"];
        $retorno["tramite"] = $arrTem["actoreparto"];
        $retorno["estado"] = $arrTem["estadofinal"];
        $retorno["festado"] = $arrTem["fechaestadofinal"];
        $retorno["usuario"] = $arrTem["operadorfinal"];
        $retorno["recibo"] = $arrTem["recibo"];
        $retorno["erecibo"] = '0'; // Normal
        if ($arrTemRec["estado"] == '03') {
            $retorno["erecibo"] = '2'; // Reversado
        }
        if ($arrTemRec["estado"] == '99') {
            $retorno["erecibo"] = '1'; // Anulado
        }
        $retorno["fecopera"] = '';
        $retorno["horpago"] = '';
        if ($arrTemRec && !empty($arrTemRec)) {
            $retorno["fecopera"] = $arrTemRec["fecha"];
            $retorno["horpago"] = $arrTemRec["hora"];
        }
        $retorno["escaneocompleto"] = $arrTem["escaneocompleto"];
        $retorno["nin"] = '';
        $retorno["nuc"] = '';
        if ($arrTemRad && !empty($arrTemRad)) {
            $retorno["nin"] = $arrTemRad["numerointernorue"];
            $retorno["nuc"] = $arrTemRad["numerounicoconsulta"];
        }
        $retorno["emails"] = $emails;
        if ($emailRadicado != '') {
            $retorno["emailradicado"] = $emailRadicado;
            $retorno["celradicado"] = $celularRadicado;
        }

        $retorno["telefonos"] = $tels;
        $retorno["detalle"] = $arrTem["detalle"];
        $retorno["emailnot1"] = $arrTem["emailnot1"];
        $retorno["emailnot2"] = $arrTem["emailnot2"];
        $retorno["emailnot3"] = $arrTem["emailnot3"];
        $retorno["celnot1"] = $arrTem["celnot1"];
        $retorno["celnot2"] = $arrTem["celnot2"];
        $retorno["celnot3"] = $arrTem["celnot3"];

        //
        $i = 0;
        $retorno["pasosruta"] = array();
        $arrTemEst = retornarRegistrosMysqli2($dbx, 'mreg_est_codigosbarras_documentos', "codigobarras='" . $codbarras . "'", "fecha,hora");
        foreach ($arrTemEst as $reg) {
            $i++;
            $retorno["pasosruta"][$i]["fecha"] = $reg["fecha"];
            $retorno["pasosruta"][$i]["hora"] = $reg["hora"];
            $retorno["pasosruta"][$i]["codigoruta"] = $reg["estado"];
            if ($arrTem["actoreparto"] == '09' || $arrTem["actoreparto"] == '53') {
                $retorno["pasosruta"][$i]["estado"] = retornarNombreTablaBasicaMysqli2($dbx, 'mreg_codestados_rutaproponentes', $reg["estado"]);
            } else {
                $retorno["pasosruta"][$i]["estado"] = retornarNombreTablaBasicaMysqli2($dbx, 'mreg_codestados_rutamercantil', $reg["estado"]);
            }
            if (trim($reg["sucursal"]) == '') {
                $retorno["pasosruta"][$i]["idusuario"] = $reg["operador"];
            } else {
                $retorno["pasosruta"][$i]["idusuario"] = $reg["sucursal"] . '-' . $reg["operador"];
            }

            $retorno["pasosruta"][$i]["usuario"] = utf8_decode(retornarNombreUsuarioMysqli2($dbx, $reg["operador"]));
            if (trim($retorno["pasosruta"][$i]["usuario"]) == '') {
                $retorno["pasosruta"][$i]["usuario"] = utf8_decode(retornarNombreUsuarioSirepMysqli2($dbx, $reg["operador"]));
            }

            if ($retorno["pasosruta"][$i]["idusuario"] == 'BAT') {
                $retorno["pasosruta"][$i]["usuario"] = 'PROCESOS AUTOMATICOS';
            }
        }

        //
        $i = 0;
        $retorno["codbarrasacto"] = array();
        $arrTemEst = retornarRegistrosMysqli2($dbx, 'mreg_est_codigosbarras_libros', "codigobarras='" . $codbarras . "'", "id");
        foreach ($arrTemEst as $reg) {
            $i++;
            if ($retorno["tramite"] != '09' && $retorno["tramite"] != '33' && $retorno["tramite"] != '53') {
                $temx = retornarRegistroMysqli2($dbx, 'mreg_est_inscripciones', "libro='" . $reg["libro"] . "' and registro='" . ltrim(trim($reg["registro"]), "0") . "'");
                $retorno["codbarrasacto"][$i]["libro"] = $reg["libro"];
                $retorno["codbarrasacto"][$i]["registro"] = ltrim(trim($reg["registro"]), "0");
                $retorno["codbarrasacto"][$i]["certif"] = retornarNombreActosRegistroMysqli2($dbx, $reg["libro"], $reg["acto"]);
                $retorno["codbarrasacto"][$i]["fechareg"] = $temx["fecharegistro"];
                $retorno["codbarrasacto"][$i]["horareg"] = $temx["horaregistro"];
                $retorno["codbarrasacto"][$i]["acto"] = $reg["acto"];
                $retorno["codbarrasacto"][$i]["noticia"] = $temx["noticia"];
                if ($reg["libro"] == 'RM07' || $reg["libro"] == 'RM22' || $reg["libro"] == 'RE52') {
                    if ($reg["acto"] == '' || $reg["acto"] == '0003') {
                        if ($temx["descripcionlibro"] != '') {
                            $retorno["codbarrasacto"][$i]["noticia"] = $temx["descripcionlibro"] . ' - ' . $temx["numeropaginas"];
                        } else {
                            $retorno["codbarrasacto"][$i]["noticia"] = $temx["codigolibro"] . ' - ' . $temx["numeropaginas"];
                        }
                    }
                }
                $retorno["codbarrasacto"][$i]["expediente"] = $temx["matricula"];
                $retorno["codbarrasacto"][$i]["nombre"] = $temx["nombre"];
                $retorno["codbarrasacto"][$i]["email"] = ''; // Completar
                $retorno["codbarrasacto"][$i]["firma"] = $temx["firma"];
            } else {
                $temx = retornarRegistroMysqli2($dbx, 'mreg_est_inscripciones_proponentes', "libro='" . $reg["libro"] . "' and registro='" . $reg["registro"] . "'");
                $retorno["codbarrasacto"][$i]["libro"] = $reg["libro"];
                $retorno["codbarrasacto"][$i]["registro"] = $reg["registro"];
                $retorno["codbarrasacto"][$i]["certif"] = retornarNombreActosProponentesMysqli2($dbx, $reg["acto"]);
                $retorno["codbarrasacto"][$i]["fechareg"] = $temx["fecharegistro"];
                $retorno["codbarrasacto"][$i]["horareg"] = $temx["horaregistro"];
                $retorno["codbarrasacto"][$i]["acto"] = $reg["acto"];
                $retorno["codbarrasacto"][$i]["noticia"] = $temx["texto"];
                $retorno["codbarrasacto"][$i]["expediente"] = $temx["proponente"];
                $retorno["codbarrasacto"][$i]["nombre"] = $temx["nombre"];
                $retorno["codbarrasacto"][$i]["email"] = ''; // Completar
                $retorno["codbarrasacto"][$i]["firma"] = $temx["firma"];
            }
        }

        //        
        $retorno["servicios"] = array();
        if (trim($arrTem["recibo"]) != '') {
            $arrTemSer = retornarRegistrosMysqli2($dbx, 'mreg_recibosgenerados_detalle', "recibo='" . $arrTem["recibo"] . "'", "id");
            $i = 0;
            foreach ($arrTemSer as $reg) {
                $i++;
                $retorno["servicios"][$i]["ser"] = $reg["idservicio"];
                $retorno["servicios"][$i]["can"] = $reg["cantidad"];
                $retorno["servicios"][$i]["val"] = $reg["valorservicio"];
                $retorno["servicios"][$i]["mat"] = $reg["matricula"];
                $retorno["servicios"][$i]["pro"] = $reg["proponente"];
                $retorno["servicios"][$i]["ano"] = $reg["ano"];
                $retorno["servicios"][$i]["act"] = $reg["valorbase"];
            }
        }

        return $retorno;
    }

    public static function consumirANI2($dbx, $tide, $ide) {

        $nameLog = 'validacionANI2_' . date("Ymd");

        $buscartoken = true;
        $name = '../../../tmp/' . CODIGO_EMPRESA . '-tokenrues.txt';
        if (file_exists($name)) {
            $x = file_get_contents('../../../tmp/' . CODIGO_EMPRESA . '-tokenrues.txt');
            list($access_token, $expira) = explode("|", $x);
            $act = date("Y-m-d H:i:s");
            if ($access_token != '') {
                if ($act <= $expira) {
                    $buscartoken = false;
                }
            }
        }

        //
        $urlapirues = '';
        $userapirues = '';
        $passwordapirues = '';
        if (!defined('RUES_API_URL') || RUES_API_URL == '') {
            $urlapirues = 'https://ruesapi.rues.org.co';
            if (TIPO_AMBIENTE == 'PRUEBAS') {
                $urlapirues = 'http://pruebasruesapi.rues.org.co';
            }
        }
        if (!defined('RUES_API_USER') || RUES_API_USER == '') {
            $userapirues = 'SIIUser';
            if (TIPO_AMBIENTE == 'PRUEBAS') {
                $userapirues = 'pruebassii';
            }
        }
        if (!defined('RUES_API_PASSWORD') || RUES_API_PASSWORD == '') {
            $passwordapirues = 'Webapi2017*';
            if (TIPO_AMBIENTE == 'PRUEBAS') {
                $passwordapirues = 'Webapi2018*';
            }
        }

        if ($buscartoken) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlapirues . '/Token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "username=" . $userapirues . "&password=" . $passwordapirues . "&grant_type=password");
            $result = curl_exec($ch);
            curl_close($ch);
            $resultado = json_decode($result, true);
            $access_token = $resultado['access_token'];

            //
            $txt = '';
            if (!is_array($resultado)) {
                $txt = $resultado;
            } else {
                foreach ($resultado as $key => $valor) {
                    $txt .= $key . ' => ' . $valor . "\r\n";
                }
            }
            \logSii2::general2($nameLog, $tide . '-' . $ide, $txt);

            //
            $fecha = date('Y-m-d H:i:s', (strtotime("+20 Hours")));
            $f = fopen($name, "w");
            fwrite($f, $access_token . '|' . $fecha);
            fclose($f);
        }

        //
        if ($access_token == '') {
            $_SESSION["generales"]["mensajeerror"] = 'No encontro token para continuar';
            return false;
        }

        //
        if (file_exists($name)) {
            $x = file_get_contents('../../../tmp/' . CODIGO_EMPRESA . '-tokenrues.txt');
            list($access_token, $expira) = explode("|", $x);
        }

        //    
        if ($tide != '2') {
            $ide1 = $ide;
            $ide2 = '';
        } else {
            $sep = separarDv($ide);
            $ide1 = $sep["identificacion"];
            $ide2 = $sep["dv"];
        }

        $json = '{
            "codigoCamara":"' . CODIGO_EMPRESA . '",
            "usuarioCamara":"' . CODIGO_EMPRESA . '-' . $_SESSION["generales"]["codigousuario"] . '",
            "cedulas":["' . $ide1 . '"]        
        }';

        $url = $urlapirues . '/api/ConsultaANI/ConsultarCedula';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $result = curl_exec($ch);
        curl_close($ch);

        //
        \logSii2::general2($nameLog, $tide . '-' . $ide, '****** PETICION : ' . $url . ' *** RESPUESTA : ' . $result);
        \logSii2::general2($nameLog, '', '');

        //
        $arrCampos = array(
            'fecha',
            'hora',
            'identificacion',
            'estadoconsulta',
            'xml'
        );

        //
        $estadoConsulta = '0';
        $error = 'no';
        $xml = '';

        //
        if (!funcionesSii2::isJson($result)) {
            \logSii2::general2($nameLog, $tide . '-' . $ide, 'La respuesta del servicio web no es un Json (1) - ' . $result);
            $estadoConsulta = '1';
            $xml = 'No se obtuvo respuesta del servicio web de ANI2 (1)';
            $_SESSION["generales"]["mensajeerror"] = 'No se obtuvo respuesta del servicio web de ANI2 (1)';
            $error = 'si';
        }

        if ($error == 'no') {
            $resultado = json_decode($result, true);
        }

        if ($error == 'no') {
            if (!isset($resultado["return"])) {
                \logSii2::general2($nameLog, $tide . '-' . $ide, 'Respuesta erronea de ANI2 (2) - ' . $result);
                $estadoConsulta = '1';
                $xml = 'Respuesta erronea de ANI2 (2) - ' . $result;
                $_SESSION["generales"]["mensajeerror"] = 'Respuesta erronea de ANI2 (2) - ' . $result;
                $error = 'si';
            }
        }

        //
        if ($error == 'no') {
            if (!isset($resultado["return"]["estadoConsulta"])) {
                \logSii2::general2($nameLog, $tide . '-' . $ide, 'Respuesta erronea de ANI2 (3) - ' . $result);
                $estadoConsulta = '1';
                $xml = 'Respuesta erronea de ANI2 (3) - ' . $result;
                $_SESSION["generales"]["mensajeerror"] = 'Respuesta erronea de ANI2 (3) - ' . $result;
                $error = 'si';
            }
        }

        //
        if ($error == 'no') {
            if (!isset($resultado["return"]["estadoConsulta"]["codError"])) {
                \logSii2::general2($nameLog, $tide . '-' . $ide, 'Respuesta erronea de ANI2 (4) - ' . $result);
                $estadoConsulta = '1';
                $xml = 'Respuesta erronea de ANI2 (4) - ' . $result;
                $_SESSION["generales"]["mensajeerror"] = 'Respuesta erronea de ANI2 (4) - ' . $result;
                $error = 'si';
            }
        }

        //
        if ($error == 'no') {
            if ($resultado["return"]["estadoConsulta"]["codError"] != '0') {
                \logSii2::general2($nameLog, $tide . '-' . $ide, 'Error en la consulta ANI (5) - ' . $resultado["return"]["estadoConsulta"]["descripcionError"] . ' - ' . $result);
                $estadoConsulta = '1';
                if ($resultado["return"]["estadoConsulta"]["codError"] == '1') {
                    $estadoConsulta = '2';
                }
                $xml = 'Error en la repsuesta del servicio ANI2 (5) - ' . $resultado["return"]["estadoConsulta"]["descripcionError"] . ' - ' . $result;
                $_SESSION["generales"]["mensajeerror"] = 'Error en la repsuesta del servicio ANI2 (5) - ' . $resultado["return"]["estadoConsulta"]["descripcionError"] . ' - ' . $result;
                $error = 'si';
            }
        }

        //
        if ($error == 'no') {
            $xml = $result;
            $salida1 = array();
            $salida1["nuip"] = '';
            $salida1["codError"] = '';
            $salida1["primerApellido"] = '';
            $salida1["particula"] = '';
            $salida1["segundoApellido"] = '';
            $salida1["primerNombre"] = '';
            $salida1["segundoNombre"] = '';
            $salida1["municipioExpedicion"] = '';
            $salida1["departamentoExpedicion"] = '';
            $salida1["fechaExpedicion"] = '';
            $salida1["estadoCedula"] = '';
            $salida1["numResolucion"] = '';
            $salida1["anoResolucion"] = '';
            $salida1["genero"] = '';
            $salida1["fechaNacimiento"] = '';
            $salida1["lugarNacimiento"] = '';
            $salida1["informante"] = '';
            $salida1["serial"] = '';
            $salida1["fechaDefuncion"] = '';
            $salida1["lugarNovedad"] = '';
            $salida1["lugarPreparacion"] = '';
            $salida1["grupoSanguineo"] = '';
            $salida1["estatura"] = '';
        }

        //
        if ($error == 'no') {
            foreach ($resultado["return"]["datosCedulas"] as $ced) {
                $salida1["codError"] = $ced["codError"];
                $salida1["nuip"] = $ced["nuip"];
                $salida1["primerApellido"] = $ced["primerApellido"];
                $salida1["particula"] = $ced["particula"];
                $salida1["segundoApellido"] = $ced["segundoApellido"];
                $salida1["primerNombre"] = $ced["primerNombre"];
                $salida1["segundoNombre"] = $ced["segundoNombre"];
                $salida1["municipioExpedicion"] = $ced["municipioExpedicion"];
                $salida1["departamentoExpedicion"] = $ced["departamentoExpedicion"];
                if (trim($ced["fechaExpedicion"]) != '' && $ced["fechaExpedicion"] != 'null') {
                    $salida1["fechaExpedicion"] = substr($ced["fechaExpedicion"], 6, 4) . substr($ced["fechaExpedicion"], 3, 2) . substr($ced["fechaExpedicion"], 0, 2);
                }
                $salida1["estadoCedula"] = $ced["estadoCedula"];
                $salida1["numResolucion"] = $ced["numResolucion"];
                $salida1["anoResolucion"] = $ced["anoResolucion"];

                if (trim($ced["genero"]) != '' && $ced["genero"] != 'null') {
                    $salida1["genero"] = $ced["genero"];
                }
                if (trim($ced["fechaNacimiento"]) != '' && $ced["fechaNacimiento"] != 'null') {
                    $salida1["fechaNacimiento"] = substr($ced["fechaNacimiento"], 6, 4) . substr($ced["fechaNacimiento"], 3, 2) . substr($ced["fechaNacimiento"], 0, 2);
                }
                if (trim($ced["lugarNacimiento"]) != '' && $ced["lugarNacimiento"] != 'null') {
                    $salida1["lugarNacimiento"] = $ced["lugarNacimiento"];
                }
                if (trim($ced["informante"]) != '' && $ced["informante"] != 'null') {
                    $salida1["informante"] = $ced["informante"];
                }
                if (trim($ced["serial"]) != '' && $ced["serial"] != 'null') {
                    $salida1["serial"] = $ced["serial"];
                }
                if (trim($ced["fechaDefuncion"]) != '' && $ced["fechaDefuncion"] != 'null') {
                    $salida1["fechaDefuncion"] = substr($ced["fechaDefuncion"], 6, 4) . substr($ced["fechaDefuncion"], 3, 2) . substr($ced["fechaDefuncion"], 0, 2);
                }
                if (trim($ced["lugarNovedad"]) != '' && $ced["lugarNovedad"] != 'null') {
                    $salida1["lugarNovedad"] = $ced["lugarNovedad"];
                }
                if (trim($ced["lugarPreparacion"]) != '' && $ced["lugarPreparacion"] != 'null') {
                    $salida1["lugarPreparacion"] = $ced["lugarPreparacion"];
                }
                if (trim($ced["grupoSanguineo"]) != '' && $ced["grupoSanguineo"] != 'null') {
                    $salida1["grupoSanguineo"] = $ced["grupoSanguineo"];
                }
                if (trim($ced["estatura"]) != '' && $ced["estatura"] != 'null') {
                    $salida1["estatura"] = $ced["estatura"];
                }
            }
        }

        //
        $arrValores = array(
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $tide . '-' . $ide . "'",
            "'" . $estadoConsulta . "'",
            "'" . addslashes($xml) . "'",
        );

        //
        insertarRegistrosMysqli2($dbx, 'mreg_ani_log', $arrCampos, $arrValores);

        //
        if ($error == 'no') {
            return $salida1;
        } else {
            return false;
        }
    }

    public static function convertirStringNumero($valx) {
        $valx = trim($valx);
        $val = 0;
        $signo = '+';
        $valx = str_replace(",", "", $valx);
        $valx = ltrim(trim($valx), "0");
        if ($valx == '' || $valx == '0.00' || $valx == '.00') {
            $val = 0;
        } else {
            if (substr($valx, 0, 1) == '-') {
                $signo = '-';
                $valx = str_replace("-", "", $valx);
            }
            $a = explode(".", $valx);
            $val = doubleval($a[0]);
            if (isset($a[1])) {
                $len = strlen($a[1]);
                switch ($len) {
                    case 1: $val = $val + intval($a[1]) / 10;
                        break;
                    case 2: $val = $val + intval($a[1]) / 100;
                        break;
                    case 3: $val = $val + intval($a[1]) / 1000;
                        break;
                    case 4: $val = $val + intval($a[1]) / 10000;
                        break;
                    case 5: $val = $val + intval($a[1]) / 100000;
                        break;
                }
            }
        }
        if ($signo == '-') {
            $val = $val * -1;
        }
        return $val;
    }

    public static function validarInhabilidad($mysqli, $nit, $proponente = '', $nombre = '', $observaciones = '') {

        require_once ('../../../configuracion/common.php');

        $fechabase = '20110712'; // Fecha de la entrada en vigencia de la Ley 1474
        $sepIde = \funcionesSii2::separarDv($nit);
        $identificacion = $sepIde["identificacion"];
        $dv = $sepIde["dv"];

        //
        $numinterno = date("Ymd") . date("His") . '001' . CODIGO_EMPRESA . '00' . '00000000';
        $usuario = $_SESSION["generales"]["codigousuario"];
        $res = \funcionesSii2::consultarReportes($mysqli, $numinterno, $usuario, $identificacion, $dv);

        // En caso de error 
        if ($res["codigoError"] != '0000') {
            $_SESSION["generales"]["mensajeerror"] = 'No es posible determinar la inhabilidad - ' . $res["msgError"];
            \funcionesSii2::validarInhabilidadGrabarLog($mysqli, $nit, $proponente, $nombre, '', '', $observaciones, $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        $arrAnos = array();

        // 2016-04-06: JINT: Verifica que cada multa sea contada solo una vez
        // Toma el último registro reportado por el RUES para cada caso.
        if (!empty($res["multas"])) {
            $multas = array();
            foreach ($res["multas"] as $m) {
                $ind = ltrim($m["nit_proponente"], "0") . '-' . ltrim($m["nit_entidad"], "0") . '-' . $m["municipio_entidad"] . '-' . ltrim($m["numero_contrato"] . '-' . $m["numero_acto_administrativo"], "0");
                $multas[$ind] = $m;
            }
            $res["multas"] = $multas;
        }

        // 2016-04-06: JINT: Verifica que cada sanción sea contada solo una vez
        // Toma el último registro reportado por el RUES para cada caso.
        if (!empty($res["sanciones"])) {
            $sanciones = array();
            foreach ($res["sanciones"] as $m) {
                $ind = ltrim($m["nit_proponente"], "0") . '-' . ltrim($m["nit_entidad"], "0") . '-' . $m["municipio_entidad"] . '-' . ltrim($m["numero_contrato"] . '-' . $m["numero_acto_administrativo"], "0");
                $sanciones[$ind] = $m;
            }
            $res["sanciones"] = $sanciones;
        }

        //
        if (!empty($res["multas"])) {
            foreach ($res["multas"] as $mul) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($mul["fecha_ejecutoria"], "0") != '') {
                    $fechacontrol = $mul["fecha_ejecutoria"];
                    $anocontrol = substr($mul["fecha_ejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $mul["fecha_acto_administrativo"];
                    $anocontrol = substr($mul["fecha_acto_administrativo"], 0, 4);
                }
                if (($mul["cod_estado"] == '0') || ($mul["cod_estado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (!isset($arrAnos[$anocontrol])) {
                            $arrAnos[$anocontrol]["anocontrol"] = $anocontrol;
                            $arrAnos[$anocontrol]["multas"] = 0;
                            $arrAnos[$anocontrol]["sanciones"] = 0;
                            $arrAnos[$anocontrol]["sinindicador"] = 0;
                            $arrAnos[$anocontrol]["fechaultimainscripcion"] = '';
                        }
                        $arrAnos[$anocontrol]["multas"] = $arrAnos[$anocontrol]["multas"] + 1;
                        if ($mul["fecha_inscripcion_camara"] > $arrAnos[$anocontrol]["fechaultimainscripcion"]) {
                            $arrAnos[$anocontrol]["fechaultimainscripcion"] = $mul["fecha_inscripcion_camara"];
                        }
                    }
                }
            }
        }

        //
        if (!empty($res["sanciones"])) {
            foreach ($res["sanciones"] as $san) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($san["fecha_ejecutoria"], "0") != '') {
                    $fechacontrol = $san["fecha_ejecutoria"];
                    $anocontrol = substr($san["fecha_ejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $san["fecha_acto_administrativo"];
                    $anocontrol = substr($san["fecha_acto_administrativo"], 0, 4);
                }
                if (($san["cod_estado"] == '0') || ($san["cod_estado"] == '2')) {
                    if (!isset($arrAnos[$anocontrol])) {
                        $arrAnos[$anocontrol]["anocontrol"] = $anocontrol;
                        $arrAnos[$anocontrol]["multas"] = 0;
                        $arrAnos[$anocontrol]["sanciones"] = 0;
                        $arrAnos[$anocontrol]["sinindicador"] = 0;
                        $arrAnos[$anocontrol]["fechaultimainscripcion"] = '';
                    }
                    if ($fechacontrol >= $fechabase) {
                        if (($san["condicion_incumplimiento"] == 'S') || ($san["condicion_incumplimiento"] == 's')) {
                            $arrAnos[$anocontrol]["sanciones"] = $arrAnos[$anocontrol]["sanciones"] + 1;
                            if ($san["fecha_inscripcion_camara"] > $arrAnos[$anocontrol]["fechaultimainscripcion"]) {
                                $arrAnos[$anocontrol]["fechaultimainscripcion"] = $san["fecha_inscripcion_camara"];
                            }
                        }
                        if ((trim($san["condicion_incumplimiento"]) == '') || ($san["condicion_incumplimiento"] == 'I') || ($san["condicion_incumplimiento"] == 'i')) {
                            $arrAnos[$anocontrol]["sinindicador"] = $arrAnos[$anocontrol]["sinindicador"] + 1;
                            if ($san["fecha_inscripcion_camara"] > $arrAnos[$anocontrol]["fechaultimainscripcion"]) {
                                $arrAnos[$anocontrol]["fechaultimainscripcion"] = $san["fecha_inscripcion_camara"];
                            }
                        }
                    }
                }
            }
        }

        //
        $resultado = array();
        $resultado["inhabilidad"] = 'no';
        $resultado["tipoinhabilidad"] = '';
        $resultado["ano"] = '';
        $resultado["multas"] = array();
        $resultado["sanciones"] = array();
        $resultado["sinindicador"] = array();
        $resultado["texto"] = array();
        $resultado["textosii"] = array();

        if (empty($arrAnos)) {
            return $resultado;
        }

        //
        $inhabilidad = 'no';
        $anoinhabilidad = '';
        foreach ($arrAnos as $an) {
            $hoy = date("Ymd");
            $anoini = intval(substr($hoy, 0, 4)) - 3;
            $fecini = sprintf("%04s", $anoini) . substr($hoy, 4, 4);
            if ($an["fechaultimainscripcion"] >= $fecini) {
                if ($an["multas"] >= 5) {
                    $inhabilidad = 'si1';
                    $anoinhabilidad = $an["anocontrol"];
                } else {
                    if (($an["multas"] >= 0) && ($an["sanciones"] >= 2)) {
                        $inhabilidad = 'si2';
                        $anoinhabilidad = $an["anocontrol"];
                    } else {
                        if (($an["multas"] >= 2) && ($an["sanciones"] >= 1)) {
                            $inhabilidad = 'si3';
                            $anoinhabilidad = $an["anocontrol"];
                        }
                    }
                }
                if ($inhabilidad == 'no') {
                    if (($an["multas"] >= 0) && (($an["sanciones"] + $an["sinindicador"]) >= 2)) {
                        $inhabilidad = 'duda2';
                        $anoinhabilidad = $an["anocontrol"];
                    }
                    if (($an["multas"] >= 2) && (($an["sanciones"] + $an["sinindicador"]) >= 1)) {
                        $inhabilidad = 'duda3';
                        $anoinhabilidad = $an["anocontrol"];
                    }
                }
            }
        }

        if (($inhabilidad == 'si1') || ($inhabilidad == 'si2') || ($inhabilidad == 'si3')) {
            $resultado["inhabilidad"] = 'si';
            $resultado["ano"] = $anoinhabilidad;
            foreach ($res["multas"] as $mul) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($mul["fecha_ejecutoria"], "0") != '') {
                    $fechacontrol = $mul["fecha_ejecutoria"];
                    $anocontrol = substr($mul["fecha_ejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $mul["fecha_acto_administrativo"];
                    $anocontrol = substr($mul["fecha_acto_administrativo"], 0, 4);
                }
                if (($mul["cod_estado"] == '0') || ($mul["cod_estado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (substr($fechacontrol, 0, 4) == $anoinhabilidad) {
                            $resultado["multas"][] = $mul;
                        }
                    }
                }
            }
            foreach ($res["sanciones"] as $san) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($san["fecha_ejecutoria"], "0") != '') {
                    $fechacontrol = $san["fecha_ejecutoria"];
                    $anocontrol = substr($san["fecha_ejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $san["fecha_acto_administrativo"];
                    $anocontrol = substr($san["fecha_acto_administrativo"], 0, 4);
                }
                if (($san["cod_estado"] == '0') || ($san["cod_estado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (substr($fechacontrol, 0, 4) == $anoinhabilidad) {
                            if (($san["condicion_incumplimiento"] == 'S') || ($san["condicion_incumplimiento"] == 's')) {
                                $resultado["sanciones"][] = $san;
                            }
                        }
                        if ((trim($san["condicion_incumplimiento"]) == '') || ($san["condicion_incumplimiento"] == 'I') || ($san["condicion_incumplimiento"] == 'i')) {
                            $resultado["sinindicador"][] = $san;
                        }
                    }
                }
            }
        }

        if (($inhabilidad == 'duda2') || ($inhabilidad == 'duda3')) {
            $resultado["inhabilidad"] = 'duda';
            $resultado["ano"] = $anoinhabilidad;
            foreach ($res["multas"] as $mul) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($mul["fechaejecutoria"], "0") != '') {
                    $fechacontrol = $mul["fechaejecutoria"];
                    $anocontrol = substr($mul["fechaejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $mul["fecha_acto_administrativo"];
                    $anocontrol = substr($mul["fecha_acto_administrativo"], 0, 4);
                }
                if (($mul["codestado"] == '0') || ($mul["codestado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (substr($fechacontrol, 0, 4) == $anoinhabilidad) {
                            $resultado["multas"][] = $mul;
                        }
                    }
                }
            }
            foreach ($res["sanciones"] as $san) {
                $fechacontrol = '';
                $anocontrol = '';
                if (ltrim($san["fechaejecutoria"], "0") != '') {
                    $fechacontrol = $san["fechaejecutoria"];
                    $anocontrol = substr($san["fechaejecutoria"], 0, 4);
                } else {
                    $fechacontrol = $san["fecha_acto_administrativo"];
                    $anocontrol = substr($san["fecha_acto_administrativo"], 0, 4);
                }
                if (($san["codestado"] == '0') || ($san["codestado"] == '2')) {
                    if ($fechacontrol >= $fechabase) {
                        if (substr($fechacontrol, 0, 4) == $anoinhabilidad) {
                            if (($san["condicion_incumplimiento"] == 'S') || ($san["condicion_incumplimiento"] == 's')) {
                                $resultado["sanciones"][] = $san;
                            }
                        }
                        if ((trim($san["condicion_incumplimiento"]) == '') || ($san["condicion_incumplimiento"] == 'I') || ($san["condicion_incumplimiento"] == 'i')) {
                            $resultado["sinindicador"][] = $san;
                        }
                    }
                }
            }
        }

        if ($resultado["inhabilidad"] == 'si') {
            $resultado["tipoinhabilidad"] = $inhabilidad;
            // 			 12345678901234567890123456789012345678901234567890123456789012345
            $resultado["texto"][] = '        CERTIFICACION DE INCUMPLIMIENTO REITERADO';
            $resultado["texto"][] = '';
            $resultado["texto"][] = 'QUE DE ACUERDO CON LO DISPUESTO EN EL ARTICULO 90 DE LA  LEY 1474';
            $resultado["texto"][] = 'DE JULIO 12 DE 2011 Y CON LO DISPUESTO EN LOS PARAGRAFOS 2-4  DEL';
            $resultado["texto"][] = 'ARTICULO 6.1.3.5 DEL DECRETO 734 DE 2012:';
            $resultado["texto"][] = '';
            $resultado["texto"][] = 'EL INSCRITO REGISTRA MULTAS E INCUMPLIMIENTOS, REPORTADOS POR LAS';
            $resultado["texto"][] = 'ENTIDADES ESTATALES, DURANTE LA VIGENCIA FISCAL DEL A&ntilde;O ' . $resultado["ano"] . ' Y EN';
            $resultado["texto"][] = 'CONSECUENCIA SE ENCUENTRA INCURSO EN INHABILIDAD POR INCUMPLIMIEN';
            $resultado["texto"][] = 'TO REITERADO, POR EL TERMINO DE TRES (3) A&ntilde;OS CONTADOS  A  PARTIR';
            $resultado["texto"][] = 'DE LA INSCRIPCION DEL ULTIMO ACTO QUE IMPONE LA MULTA  O  DECLARA';
            $resultado["texto"][] = 'TORIA DE INCUMPLIMIENTO.';
            $resultado["texto"][] = '';
            $resultado["texto"][] = 'LA   INHABILIDAD   POR  INCUMPLIMIENTO  REITERADO  SE  CONSTITUYO';
            $resultado["texto"][] = 'TENIENDO EN CUENTA LAS SIGUIENTES MULTAS E INCUMPLIMIENTOS:';
            $resultado["texto"][] = '';

            // 			 12345678901234567890123456789012345678901234567890123456789012345
            $resultado["textosii"][0] = 'CERTIFICACION DE INCUMPLIMIENTO REITERADO';

            //
            $resultado["textosii"][1] = 'QUE DE ACUERDO CON LO DISPUESTO EN EL ARTICULO 90 DE LA  LEY 1474 ';
            $resultado["textosii"][1] .= 'DE JULIO 12 DE 2011 Y CON LO DISPUESTO EN LOS PARAGRAFOS 2-4 DEL ';
            $resultado["textosii"][1] .= 'ARTICULO 6.1.3.5 DEL DECRETO 734 DE 2012:<br><br>';
            $resultado["textosii"][1] .= 'EL INSCRITO REGISTRA MULTAS E INCUMPLIMIENTOS, REPORTADOS POR LAS ';
            $resultado["textosii"][1] .= 'ENTIDADES ESTATALES, DURANTE LA VIGENCIA FISCAL DEL A&ntilde;O ' . $resultado["ano"] . ' Y EN ';
            $resultado["textosii"][1] .= 'CONSECUENCIA SE ENCUENTRA INCURSO EN INHABILIDAD POR INCUMPLIMIENTO ';
            $resultado["textosii"][1] .= 'REITERADO, POR EL TERMINO DE TRES (3) A&ntilde;OS CONTADOS  A  PARTIR ';
            $resultado["textosii"][1] .= 'DE LA INSCRIPCION DEL ULTIMO ACTO QUE IMPONE LA MULTA  O  DECLARATORIA ';
            $resultado["textosii"][1] .= 'DE INCUMPLIMIENTO.<br><br>';
            $resultado["textosii"][1] .= 'LA INHABILIDAD POR INCUMPLIMIENTO REITERADO SE CONSTITUYO ';
            $resultado["textosii"][1] .= 'TENIENDO EN CUENTA LAS SIGUIENTES MULTAS E INCUMPLIMIENTOS:<br><br>';
        }

        //if (($resultado["inhabilidad"]=='si')  || ($resultado["inhabilidad"]=='duda')) {
        if ($resultado["inhabilidad"] == 'si') {
            if (!empty($resultado["multas"])) {
                $resultado["textosii"][2] = '';
            }
            foreach ($resultado["multas"] as $mul) {
                $resultado["texto"][] = '*** MULTA:';
                $resultado["texto"][] = 'NIT ENTIDAD : ' . $mul["nit_entidad"] . '-' . $mul["dv_entidad"];
                $resultado["texto"][] = 'NOMBRE : ' . substr($mul["nombre_entidad"], 0, 57);
                $resultado["texto"][] = 'MUNICIPIO : ' . retornarNombreMunicipioMysqli2($mysqli, $mul["municipio_entidad"]);
                $resultado["texto"][] = 'SECCIONAL : ' . $mul["seccional_entidad"];
                $resultado["texto"][] = 'NUMERO CONTRATO : ' . $mul["numero_contrato"];
                $resultado["texto"][] = 'ACTO ADMINISTRATIVO : ' . $mul["numero_acto_administrativo"];
                $resultado["texto"][] = 'FECHA ACTO ADMINISTRATIVO : ' . $mul["fecha_acto_administrativo"];
                $resultado["texto"][] = 'FECHA EJECUTORIA : ' . $mul["fecha_ejecutoria"];
                $resultado["texto"][] = 'VALOR MULTA : ' . $mul["valor_multa"];
                $resultado["texto"][] = 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $mul["codigo_camara"];
                $resultado["texto"][] = 'EN FECHA : ' . $mul["fecha_inscripcion_camara"];
                $resultado["texto"][] = 'NUMERO DE INSCRIPCION : ' . $mul["numero_inscripcion_libro"];
                $resultado["texto"][] = '';

                //
                $resultado["textosii"][2] .= '<strong>*** MULTA:</strong><br>';
                $resultado["textosii"][2] .= 'NIT ENTIDAD : ' . $mul["nit_entidad"] . '-' . $mul["dv_entidad"] . '<br>';
                $resultado["textosii"][2] .= 'NOMBRE : ' . substr($mul["nombre_entidad"], 0, 57) . '<br>';
                $resultado["textosii"][2] .= 'MUNICIPIO : ' . retornarNombreMunicipioMysqli2($mysqli, $mul["municipio_entidad"]) . '<br>';
                $resultado["textosii"][2] .= 'SECCIONAL : ' . $mul["seccional_entidad"] . '<br>';
                $resultado["textosii"][2] .= 'NUMERO CONTRATO : ' . $mul["numero_contrato"] . '<br>';
                $resultado["textosii"][2] .= 'ACTO ADMINISTRATIVO : ' . $mul["numero_acto_administrativo"] . '<br>';
                $resultado["textosii"][2] .= 'FECHA ACTO ADMINISTRATIVO : ' . $mul["fecha_acto_administrativo"] . '<br>';
                $resultado["textosii"][2] .= 'FECHA EJECUTORIA : ' . $mul["fecha_ejecutoria"] . '<br>';
                $resultado["textosii"][2] .= 'VALOR MULTA : ' . $mul["valor_multa"] . '<br>';
                $resultado["textosii"][2] .= 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $mul["codigo_camara"] . '<br>';
                $resultado["textosii"][2] .= 'EN FECHA : ' . $mul["fecha_inscripcion_camara"] . '<br>';
                $resultado["textosii"][2] .= 'NUMERO DE INSCRIPCION : ' . $mul["numero_inscripcion_libro"] . '<br><br>';
            }

            if (!empty($resultado["sanciones"])) {
                $resultado["textosii"][3] = '';
            }
            foreach ($resultado["sanciones"] as $san) {

                //
                $san["descripcion"] = sprintf("%-600s", $san["descripcion"]);
                $resultado["texto"][] = '*** SANCION :';
                $resultado["texto"][] = 'NIT ENTIDAD : ' . $san["nit_entidad"] . '-' . $san["dv_entidad"];
                $resultado["texto"][] = 'NOMBRE : ' . substr($san["nombre_entidad"], 0, 57);
                $resultado["texto"][] = 'MUNICIPIO : ' . retornarNombreMunicipioMysqli2($mysqli, $san["municipio_entidad"]);
                $resultado["texto"][] = 'SECCIONAL : ' . $san["seccional_entidad"];
                $resultado["texto"][] = 'NUMERO CONTRATO : ' . $san["numero_contrato"];
                $resultado["texto"][] = 'ACTO ADMINISTRATIVO : ' . $san["numero_acto_administrativo"];
                $resultado["texto"][] = 'FECHA ACTO ADMINISTRATIVO : ' . $san["fecha_acto_administrativo"];
                $resultado["texto"][] = 'FECHA EJECUTORIA : ' . $san["fecha_ejecutoria"];
                $resultado["texto"][] = 'DESCRIPCION SANCION : ' . substr($san["descripcion_sancion"], 0, 44);
                if (trim(substr($san["descripcion"], 44, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 44, 65);
                }
                if (trim(substr($san["descripcion"], 109, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 109, 65);
                }
                if (trim(substr($san["descripcion"], 174, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 174, 65);
                }
                if (trim(substr($san["descripcion"], 239, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 239, 65);
                }
                if (trim(substr($san["descripcion"], 304, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 304, 65);
                }
                if (trim(substr($san["descripcion"], 369, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 369, 65);
                }
                $resultado["texto"][] = 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $san["codigo_camara"];
                $resultado["texto"][] = 'EN FECHA : ' . $san["fecha_inscripcion_camara"];
                $resultado["texto"][] = 'NUMERO DE INSCRIPCION : ' . $san["numero_inscripcion_libro"];
                $resultado["texto"][] = '';

                //
                $resultado["textosii"][3] .= '<strong>*** SANCION :</strong><br>';
                $resultado["textosii"][3] .= 'NIT ENTIDAD : ' . $san["nit_entidad"] . '-' . $san["dv_entidad"] . '<br>';
                $resultado["textosii"][3] .= 'NOMBRE : ' . substr($san["nombre_entidad"], 0, 57) . '<br>';
                $resultado["textosii"][3] .= 'MUNICIPIO : ' . retornarNombreMunicipioMysqli2($mysqli, $san["municipio_entidad"]) . '<br>';
                $resultado["textosii"][3] .= 'SECCIONAL : ' . $san["seccional_entidad"] . '<br>';
                $resultado["textosii"][3] .= 'NUMERO CONTRATO : ' . $san["numero_contrato"] . '<br>';
                $resultado["textosii"][3] .= 'ACTO ADMINISTRATIVO : ' . $san["numero_acto_administrativo"] . '<br>';
                $resultado["textosii"][3] .= 'FECHA ACTO ADMINISTRATIVO : ' . $san["fecha_acto_administrativo"] . '<br>';
                $resultado["textosii"][3] .= 'FECHA EJECUTORIA : ' . $san["fecha_ejecutoria"] . '<br>';
                $resultado["textosii"][3] .= 'DESCRIPCION SANCION : ' . trim($san["descripcion_sancion"]) . '<br>';
                $resultado["textosii"][3] .= 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $san["codigo_camara"] . '<br>';
                $resultado["textosii"][3] .= 'EN FECHA : ' . $san["fecha_inscripcion_camara"] . '<br>';
                $resultado["textosii"][3] .= 'NUMERO DE INSCRIPCION : ' . $san["numero_inscripcion_libro"] . '<br><br>';
            }

            if (!empty($resultado["sinindicador"])) {
                $resultado["textosii"][4] = '';
            }
            foreach ($resultado["sinindicador"] as $san) {
                $san["descripcion"] = sprintf("%-600s", $san["descripcion"]);
                $resultado["texto"][] = '*** SANCION SIN INDICADOR DE INCUMPLIMIENTO :';
                $resultado["texto"][] = 'NIT ENTIDAD : ' . $san["nit_entidad"] . '-' . $san["dv_entidad"];
                $resultado["texto"][] = 'NOMBRE : ' . substr($san["nombre_entidad"], 0, 57);
                $resultado["texto"][] = 'MUNICIPIO : ' . retornarNombreMunicipioMysqli2($mysqli, $san["municipio_entidad"]);
                $resultado["texto"][] = 'NUMERO CONTRATO : ' . $san["numero_contrato"];
                $resultado["texto"][] = 'ACTO ADMINISTRATIVO : ' . $san["numero_acto_administrativo"];
                $resultado["texto"][] = 'SECCIONAL : ' . $san["seccional_entidad"];
                $resultado["texto"][] = 'FECHA ACTO ADMINISTRATIVO : ' . $san["fecha_acto_administrativo"];
                $resultado["texto"][] = 'FECHA EJECUTORIA : ' . $san["fecha_ejecutoria"];
                $resultado["texto"][] = 'DESCRIPCION SANCION: ' . substr($san["descripcion"], 0, 44);
                if (trim(substr($san["descripcion"], 44, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 44, 65);
                }
                if (trim(substr($san["descripcion"], 109, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 109, 65);
                }
                if (trim(substr($san["descripcion"], 174, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 174, 65);
                }
                if (trim(substr($san["descripcion"], 239, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 239, 65);
                }
                if (trim(substr($san["descripcion"], 304, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 304, 65);
                }
                if (trim(substr($san["descripcion"], 369, 65)) != '') {
                    $resultado["texto"][] = substr($san["descripcion"], 369, 65);
                }
                $resultado["texto"][] = 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $san["codigo_camara"];
                $resultado["texto"][] = 'EN FECHA : ' . $san["fecha_inscripcion_camara"];
                $resultado["texto"][] = 'NUMERO DE INSCRIPCION : ' . $san["numero_inscripcion_libro"];
                $resultado["texto"][] = '';

                //
                $resultado["textosii"][4] .= '<strong>*** SANCION SIN INDICADOR DE INCUMPLIMIENTO :</strong><br>';
                $resultado["textosii"][4] .= 'NIT ENTIDAD : ' . $san["nit_entidad"] . '-' . $san["dv_entidad"] . '<br>';
                $resultado["textosii"][4] .= 'NOMBRE : ' . substr($san["nombre_entidad"], 0, 57) . '<br>';
                $resultado["textosii"][4] .= 'MUNICIPIO : ' . retornarNombreMunicipioMysqli2($mysqli, $san["municipio_entidad"]) . '<br>';
                $resultado["textosii"][4] .= 'ACTO ADMINISTRATIVO : ' . $san["numero_acto_administrativo"] . '<br>';
                $resultado["textosii"][4] .= 'SECCIONAL : ' . $san["seccional_entidad"] . '<br>';
                $resultado["textosii"][4] .= 'NUMERO CONTRATO : ' . $san["numero_contrato"] . '<br>';
                $resultado["textosii"][4] .= 'FECHA ACTO ADMINISTRATIVO : ' . $san["fecha_acto_administrativo"] . '<br>';
                $resultado["textosii"][4] .= 'FECHA EJECUTORIA : ' . $san["fecha_ejecutoria"] . '<br>';
                $resultado["textosii"][4] .= 'DESCRIPCION SANCION: ' . trim($san["descripcion"]) . '<br>';
                $resultado["textosii"][4] .= 'INSCRITA EN LA CAMARA DE COMERCIO NO. : ' . $san["codigo_camara"] . '<br>';
                $resultado["textosii"][4] .= 'EN FECHA : ' . $san["fecha_inscripcion_camara"] . '<br>';
                $resultado["textosii"][4] .= 'NUMERO DE INSCRIPCION : ' . $san["numero_inscripcion_libro"] . '<br><br>';
            }
        }

        // 2018-02-05: JINT: Almacena el log del cálculo de la inhabilidad del proponente
        $txt = '';
        foreach ($resultado["textosii"] as $tx) {
            $txt .= $tx . '<br><br>';
        }
        \funcionesSii2::validarInhabilidadGrabarLog($mysqli, $nit, $proponente, $nombre, $resultado["inhabilidad"], $txt, $observaciones, 'OK');

        //
        return $resultado;
    }

    // 2018-02-05: JINT: Graba log con el resultado del cálculo de la inhabilidad
    public static function validarInhabilidadGrabarLog($mysqli, $nit, $proponente, $nombre, $inh, $txt, $observaciones, $complemento) {
        $arrCampos = array(
            'fecha',
            'hora',
            'nit',
            'proponente',
            'nombre',
            'inhabilidad',
            'texto',
            'estadosirep'
        );
        $arrValores = array(
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $nit . "'",
            "'" . $proponente . "'",
            "'" . addslashes($nombre) . "'",
            "'" . $inh . "'",
            "'" . addslashes($txt) . "'",
            "'" . addslashes($observaciones . ' : ' . $complemento) . "'"
        );
        insertarRegistrosMysqli2($mysqli, 'mreg_log_control_inhabilidades_proponentes', $arrCampos, $arrValores);
    }

    public static function separarDv($id) {
        $id = str_replace(",", "", ltrim(trim($id), "0"));
        $id = str_replace(".", "", $id);
        $id = str_replace("-", "", $id);
        $entrada = sprintf("%016s", $id);
        $dv = substr($entrada, 15, 1);
        return array(
            'identificacion' => ltrim(substr($entrada, 0, 15), "0"),
            'dv' => $dv);
    }

    public static function consultarReportes($numinterno, $usuario, $numid, $dv) {


        require_once ('../../../configuracion/common.php');

        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';

        $RUE_ConsultaHistoriaProponente_BC = array(
            "numero_interno" => $numinterno,
            'usuario' => $usuario,
            'nit_proponente' => $numid,
            'dv_proponente' => $dv
        );

        $wsdl = URL_RUE_WS . "_DL/RR17N.asmx?WSDL";

        // Instancia el servicio web
        try {
            $client = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true, 'encoding' => 'ISO-8859-1'));
            if (is_soap_fault($client)) {
                $respuesta["codigoError"] = '0001';
                $respuesta["msgError"] = 'No fue posible crear la conexi&oacute;n con el servicio web  de consulta historia de proponentes ';
                \logSii2::general2('consultarReportesRue', 'Error Soap ', 'Error consultando el proponente ' . $numid . ' - ' . $respuesta["msgError"]);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            $respuesta["msgError"] = 'Error de excepci&oacute;n instanciando el servico web :  ' . $e->getMessage();
            \logSii2::general2('consultarReportesRue', 'Error de excepci&oacute;n instanciando el servico web ', 'Error consultando el proponente ' . $numid . ' - ' . $e->getMessage());
            return $respuesta;
        } catch (SoapFault $fault) {
            $respuesta["codigoError"] = '0003';
            \logSii2::general2('consultarReportesRue', 'Error Soap Fault ', 'Error consultando el proponente ' . $numid . ' - ' . $e->getMessage());
            return $respuesta;
        }

        try {
            $result = $client->consultaHistoriaProponente(array("consultaHistorialProponente" => $RUE_ConsultaHistoriaProponente_BC));
            if ($result === false) {
                $respuesta["codigoError"] = '0004';
                $respuesta["msgError"] = 'Error en la respuesta del servico web ' . $result;
                \logSii2::general2('consultarReportesRue', 'Client Error ', 'Error consultando el proponente ' . $numid . ' - ' . $result);
                return $respuesta;
            }
        } catch (Exception $e) {
            $respuesta["codigoError"] = '0002';
            \logSii2::general2('consultarReportesRue', 'Client Exception Error', 'Error consultando el proponente ' . $numid . ' - ' . $e->getMessage());
            return $respuesta;
        }

        $t = (array) $result;
        \logSii2::general2('consultarReportesRue', 'Respuesta correcta', 'Proponente ' . $numid . ' - ' . print_r($t, true));
        if (ltrim($t["RUE_ConsultaHistoriaProponente_BC"]->codigo_error, "0") != '') {
            $respuesta["codigoError"] = $t["RUE_ConsultaHistoriaProponente_BC"]->codigo_error;
            $respuesta["msgError"] = $t["RUE_ConsultaHistoriaProponente_BC"]->mensaje_error;
            \logSii2::general2('consultarReportesRue', 'Error en Respuesta', 'Error consultando el proponente ' . $numid . ' - ' . $respuesta["codigoError"] . ' - ' . $respuesta["msgError"]);
        } else {
            $iCnt = 0;
            $respuesta["contratos"] = array();
            $respuesta["multas"] = array();
            $respuesta["sanciones"] = array();

            if (isset($t["RUE_ConsultaHistoriaProponente_BC"]->contratos)) {
                if (is_array($t["RUE_ConsultaHistoriaProponente_BC"]->contratos)) {
                    foreach ($t["RUE_ConsultaHistoriaProponente_BC"]->contratos as $cnt) {
                        $iCnt++;
                        $respuesta["contratos"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_indicador_envio);
                        $respuesta["contratos"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_proponente);
                        $respuesta["contratos"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_proponente);
                        $respuesta["contratos"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_entidad);
                        $respuesta["contratos"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_entidad);
                        $respuesta["contratos"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->municipio_entidad);
                        $respuesta["contratos"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_contrato);
                        $respuesta["contratos"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_entidad);
                        $respuesta["contratos"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_proponente);
                        $respuesta["contratos"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->seccional_entidad);
                        $respuesta["contratos"][$iCnt]["fecha_adjudicacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_adjudicacion);
                        $respuesta["contratos"][$iCnt]["fecha_perfeccionamiento"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_perfeccionamiento);
                        $respuesta["contratos"][$iCnt]["fecha_inicio"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_inicio);
                        $respuesta["contratos"][$iCnt]["fecha_terminacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_terminacion);
                        $respuesta["contratos"][$iCnt]["fecha_liquidacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_liquidacion);
                        $respuesta["contratos"][$iCnt]["valor_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->valor_contrato);
                        $respuesta["contratos"][$iCnt]["valor_pagado"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->valor_pagado);
                        $respuesta["contratos"][$iCnt]["cod_estado_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_estado_contrato);
                        $respuesta["contratos"][$iCnt]["cod_tipo_contratista"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_tipo_contratista);
                        $respuesta["contratos"][$iCnt]["motivo_terminacion_anticipada"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->motivo_terminacion_anticipada);
                        $respuesta["contratos"][$iCnt]["fecha_terminacion_anticipada"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_terminacion_anticipada);
                        $respuesta["contratos"][$iCnt]["cod_actividad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_actividad);
                        $respuesta["contratos"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->observaciones);
                        $respuesta["contratos"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_camara);
                        $respuesta["contratos"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_libro_registro);
                        $respuesta["contratos"][$iCnt]["numero_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_inscripcion_camara);
                        $respuesta["contratos"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_inscripcion_camara);
                        $respuesta["contratos"][$iCnt]["clasificasiones1464"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->clasificasiones1464);
                        $respuesta["contratos"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_radicacion_rue);
                        $respuesta["contratos"][$iCnt]["nueva_fechareporte"] = '';
                        $respuesta["contratos"][$iCnt]["nueva_libro"] = '';
                        $respuesta["contratos"][$iCnt]["nueva_inscripcion"] = '';
                        $respuesta["contratos"][$iCnt]["nueva_fechainscripcion"] = '';
                    }
                } else {
                    $iCnt++;
                    $respuesta["contratos"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->cod_indicador_envio);
                    $respuesta["contratos"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->nit_proponente);
                    $respuesta["contratos"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->dv_proponente);
                    $respuesta["contratos"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->nit_entidad);
                    $respuesta["contratos"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->dv_entidad);
                    $respuesta["contratos"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->municipio_entidad);
                    $respuesta["contratos"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->numero_contrato);
                    $respuesta["contratos"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->nombre_entidad);
                    $respuesta["contratos"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->nombre_proponente);
                    $respuesta["contratos"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->seccional_entidad);
                    $respuesta["contratos"][$iCnt]["fecha_adjudicacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_adjudicacion);
                    $respuesta["contratos"][$iCnt]["fecha_perfeccionamiento"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_perfeccionamiento);
                    $respuesta["contratos"][$iCnt]["fecha_inicio"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_inicio);
                    $respuesta["contratos"][$iCnt]["fecha_terminacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_terminacion);
                    $respuesta["contratos"][$iCnt]["fecha_liquidacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_liquidacion);
                    $respuesta["contratos"][$iCnt]["valor_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->valor_contrato);
                    $respuesta["contratos"][$iCnt]["valor_pagado"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->valor_pagado);
                    $respuesta["contratos"][$iCnt]["cod_estado_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->cod_estado_contrato);
                    $respuesta["contratos"][$iCnt]["cod_tipo_contratista"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->cod_tipo_contratista);
                    $respuesta["contratos"][$iCnt]["motivo_terminacion_anticipada"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->motivo_terminacion_anticipada);
                    $respuesta["contratos"][$iCnt]["fecha_terminacion_anticipada"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_terminacion_anticipada);
                    $respuesta["contratos"][$iCnt]["cod_actividad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->cod_actividad);
                    $respuesta["contratos"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->observaciones);
                    $respuesta["contratos"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->codigo_camara);
                    $respuesta["contratos"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->codigo_libro_registro);
                    $respuesta["contratos"][$iCnt]["numero_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->numero_inscripcion_camara);
                    $respuesta["contratos"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->fecha_inscripcion_camara);
                    $respuesta["contratos"][$iCnt]["clasificasiones1464"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->clasificasiones1464);
                    $respuesta["contratos"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->contratos->numero_radicacion_rue);
                    $respuesta["contratos"][$iCnt]["nueva_fechareporte"] = '';
                    $respuesta["contratos"][$iCnt]["nueva_libro"] = '';
                    $respuesta["contratos"][$iCnt]["nueva_inscripcion"] = '';
                    $respuesta["contratos"][$iCnt]["nueva_fechainscripcion"] = '';
                }
            }
            $iCnt = 0;
            if (isset($t["RUE_ConsultaHistoriaProponente_BC"]->multas)) {
                if (is_array($t["RUE_ConsultaHistoriaProponente_BC"]->multas)) {
                    foreach ($t["RUE_ConsultaHistoriaProponente_BC"]->multas as $cnt) {
                        $iCnt++;
                        $respuesta["multas"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_indicador_envio);
                        $respuesta["multas"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_proponente);
                        $respuesta["multas"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_proponente);
                        $respuesta["multas"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_entidad);
                        $respuesta["multas"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_entidad);
                        $respuesta["multas"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->municipio_entidad);
                        $respuesta["multas"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_contrato);
                        $respuesta["multas"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_entidad);
                        $respuesta["multas"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_proponente);
                        $respuesta["multas"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->seccional_entidad);
                        $respuesta["multas"][$iCnt]["numero_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_administrativo);
                        $respuesta["multas"][$iCnt]["fecha_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_administrativo);
                        $respuesta["multas"][$iCnt]["fecha_ejecutoria"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_ejecutoria);
                        $respuesta["multas"][$iCnt]["valor_multa"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->valor_multa);
                        $respuesta["multas"][$iCnt]["valor_pagado_multa"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->valor_pagado_multa);
                        $respuesta["multas"][$iCnt]["cod_estado"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_estado);
                        $respuesta["multas"][$iCnt]["numero_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_suspension);
                        $respuesta["multas"][$iCnt]["fecha_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_suspension);
                        $respuesta["multas"][$iCnt]["numero_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_confirmacion);
                        $respuesta["multas"][$iCnt]["fecha_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_confirmacion);
                        $respuesta["multas"][$iCnt]["numero_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_revocacion);
                        $respuesta["multas"][$iCnt]["fecha_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_revocacion);
                        $respuesta["multas"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->observaciones);
                        $respuesta["multas"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_camara);
                        $respuesta["multas"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_libro_registro);
                        $respuesta["multas"][$iCnt]["numero_inscripcion_libro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_inscripcion_libro);
                        $respuesta["multas"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_inscripcion_camara);
                        $respuesta["multas"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_radicacion_rue);
                        $respuesta["multas"][$iCnt]["nueva_fechareporte"] = '';
                        $respuesta["multas"][$iCnt]["nueva_libro"] = '';
                        $respuesta["multas"][$iCnt]["nueva_inscripcion"] = '';
                        $respuesta["multas"][$iCnt]["nueva_fechainscripcion"] = '';
                    }
                } else {
                    $respuesta["multas"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->cod_indicador_envio);
                    $respuesta["multas"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->nit_proponente);
                    $respuesta["multas"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->dv_proponente);
                    $respuesta["multas"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->nit_entidad);
                    $respuesta["multas"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->dv_entidad);
                    $respuesta["multas"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->municipio_entidad);
                    $respuesta["multas"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_contrato);
                    $respuesta["multas"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->nombre_entidad);
                    $respuesta["multas"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->nombre_proponente);
                    $respuesta["multas"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->seccional_entidad);
                    $respuesta["multas"][$iCnt]["numero_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_acto_administrativo);
                    $respuesta["multas"][$iCnt]["fecha_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_acto_administrativo);
                    $respuesta["multas"][$iCnt]["fecha_ejecutoria"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_ejecutoria);
                    $respuesta["multas"][$iCnt]["valor_multa"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->valor_multa);
                    $respuesta["multas"][$iCnt]["valor_pagado_multa"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->valor_pagado_multa);
                    $respuesta["multas"][$iCnt]["cod_estado"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->cod_estado);
                    $respuesta["multas"][$iCnt]["numero_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_acto_suspension);
                    $respuesta["multas"][$iCnt]["fecha_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_acto_suspension);
                    $respuesta["multas"][$iCnt]["numero_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_acto_confirmacion);
                    $respuesta["multas"][$iCnt]["fecha_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_acto_confirmacion);
                    $respuesta["multas"][$iCnt]["numero_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_acto_revocacion);
                    $respuesta["multas"][$iCnt]["fecha_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_acto_revocacion);
                    $respuesta["multas"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->observaciones);
                    $respuesta["multas"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->codigo_camara);
                    $respuesta["multas"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->codigo_libro_registro);
                    $respuesta["multas"][$iCnt]["numero_inscripcion_libro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_inscripcion_libro);
                    $respuesta["multas"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->fecha_inscripcion_camara);
                    $respuesta["multas"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->multas->numero_radicacion_rue);
                    $respuesta["multas"][$iCnt]["nueva_fechareporte"] = '';
                    $respuesta["multas"][$iCnt]["nueva_libro"] = '';
                    $respuesta["multas"][$iCnt]["nueva_inscripcion"] = '';
                    $respuesta["multas"][$iCnt]["nueva_fechainscripcion"] = '';
                }
            }

            $iCnt = 0;
            if (isset($t["RUE_ConsultaHistoriaProponente_BC"]->sanciones)) {
                if (is_array($t["RUE_ConsultaHistoriaProponente_BC"]->sanciones)) {
                    foreach ($t["RUE_ConsultaHistoriaProponente_BC"]->sanciones as $cnt) {
                        $iCnt++;
                        $respuesta["sanciones"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_indicador_envio);
                        $respuesta["sanciones"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_proponente);
                        $respuesta["sanciones"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_proponente);
                        $respuesta["sanciones"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nit_entidad);
                        $respuesta["sanciones"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->dv_entidad);
                        $respuesta["sanciones"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->municipio_entidad);
                        $respuesta["sanciones"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_contrato);
                        $respuesta["sanciones"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_entidad);
                        $respuesta["sanciones"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->nombre_proponente);
                        $respuesta["sanciones"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->seccional_entidad);
                        $respuesta["sanciones"][$iCnt]["numero_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_administrativo);
                        $respuesta["sanciones"][$iCnt]["fecha_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_administrativo);
                        $respuesta["sanciones"][$iCnt]["fecha_ejecutoria"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_ejecutoria);
                        $respuesta["sanciones"][$iCnt]["descripcion_sancion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->descripcion_sancion);
                        $respuesta["sanciones"][$iCnt]["condicion_incumplimiento"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->condicion_incumplimiento);
                        $respuesta["sanciones"][$iCnt]["cod_estado"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->cod_estado);
                        $respuesta["sanciones"][$iCnt]["numero_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_suspension);
                        $respuesta["sanciones"][$iCnt]["fecha_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_suspension);
                        $respuesta["sanciones"][$iCnt]["numero_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_confirmacion);
                        $respuesta["sanciones"][$iCnt]["fecha_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_confirmacion);
                        $respuesta["sanciones"][$iCnt]["numero_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_acto_revocacion);
                        $respuesta["sanciones"][$iCnt]["fecha_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_acto_revocacion);
                        $respuesta["sanciones"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->observaciones);
                        $respuesta["sanciones"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_camara);
                        $respuesta["sanciones"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->codigo_libro_registro);
                        $respuesta["sanciones"][$iCnt]["numero_inscripcion_libro"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_inscripcion_libro);
                        $respuesta["sanciones"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->fecha_inscripcion_camara);
                        $respuesta["sanciones"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $cnt->numero_radicacion_rue);
                        $respuesta["sanciones"][$iCnt]["nueva_fechareporte"] = '';
                        $respuesta["sanciones"][$iCnt]["nueva_libro"] = '';
                        $respuesta["sanciones"][$iCnt]["nueva_inscripcion"] = '';
                        $respuesta["sanciones"][$iCnt]["nueva_fechainscripcion"] = '';
                    }
                } else {
                    $respuesta["sanciones"][$iCnt]["cod_indicador_envio"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->cod_indicador_envio);
                    $respuesta["sanciones"][$iCnt]["nit_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->nit_proponente);
                    $respuesta["sanciones"][$iCnt]["dv_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->dv_proponente);
                    $respuesta["sanciones"][$iCnt]["nit_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->nit_entidad);
                    $respuesta["sanciones"][$iCnt]["dv_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->dv_entidad);
                    $respuesta["sanciones"][$iCnt]["municipio_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->municipio_entidad);
                    $respuesta["sanciones"][$iCnt]["numero_contrato"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_contrato);
                    $respuesta["sanciones"][$iCnt]["nombre_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->nombre_entidad);
                    $respuesta["sanciones"][$iCnt]["nombre_proponente"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->nombre_proponente);
                    $respuesta["sanciones"][$iCnt]["seccional_entidad"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->seccional_entidad);
                    $respuesta["sanciones"][$iCnt]["numero_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_acto_administrativo);
                    $respuesta["sanciones"][$iCnt]["fecha_acto_administrativo"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_acto_administrativo);
                    $respuesta["sanciones"][$iCnt]["fecha_ejecutoria"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_ejecutoria);
                    $respuesta["sanciones"][$iCnt]["descripcion_sancion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->descripcion_sancion);
                    $respuesta["sanciones"][$iCnt]["condicion_incumplimiento"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->condicion_incumplimiento);
                    $respuesta["sanciones"][$iCnt]["cod_estado"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->cod_estado);
                    $respuesta["sanciones"][$iCnt]["numero_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_acto_suspension);
                    $respuesta["sanciones"][$iCnt]["fecha_acto_suspension"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_acto_suspension);
                    $respuesta["sanciones"][$iCnt]["numero_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_acto_confirmacion);
                    $respuesta["sanciones"][$iCnt]["fecha_acto_confirmacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_acto_confirmacion);
                    $respuesta["sanciones"][$iCnt]["numero_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_acto_revocacion);
                    $respuesta["sanciones"][$iCnt]["fecha_acto_revocacion"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_acto_revocacion);
                    $respuesta["sanciones"][$iCnt]["observaciones"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->observaciones);
                    $respuesta["sanciones"][$iCnt]["codigo_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->codigo_camara);
                    $respuesta["sanciones"][$iCnt]["codigo_libro_registro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->codigo_libro_registro);
                    $respuesta["sanciones"][$iCnt]["numero_inscripcion_libro"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_inscripcion_libro);
                    $respuesta["sanciones"][$iCnt]["fecha_inscripcion_camara"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->fecha_inscripcion_camara);
                    $respuesta["sanciones"][$iCnt]["numero_radicacion_rue"] = str_replace(array("<![CDATA[", "]]>"), "", $t["RUE_ConsultaHistoriaProponente_BC"]->sanciones->numero_radicacion_rue);
                    $respuesta["sanciones"][$iCnt]["nueva_fechareporte"] = '';
                    $respuesta["sanciones"][$iCnt]["nueva_libro"] = '';
                    $respuesta["sanciones"][$iCnt]["nueva_inscripcion"] = '';
                    $respuesta["sanciones"][$iCnt]["nueva_fechainscripcion"] = '';
                }
            }
        }
        return $respuesta;
    }

    public static function reemplazarAcutesSii2($txt) {
        $txt = str_replace("&amp;", "&", $txt);
        $txt = str_replace("&AMP;", "&", $txt);
        $txt = str_replace("&aacute;", "á", $txt);
        $txt = str_replace("&eacute;", "é", $txt);
        $txt = str_replace("&iacute;", "í", $txt);
        $txt = str_replace("&oacute;", "ó", $txt);
        $txt = str_replace("&uacute;", "ú", $txt);
        $txt = str_replace("&Aacute;", "Á", $txt);
        $txt = str_replace("&Eacute;", "É", $txt);
        $txt = str_replace("&Iacute;", "Í", $txt);
        $txt = str_replace("&Oacute;", "Ó", $txt);
        $txt = str_replace("&Uacute;", "Ú", $txt);
        $txt = str_replace("&AACUTE;", "Á", $txt);
        $txt = str_replace("&EACUTE;", "É", $txt);
        $txt = str_replace("&IACUTE;", "Í", $txt);
        $txt = str_replace("&OACUTE;", "Ó", $txt);
        $txt = str_replace("&UACUTE;", "Ú", $txt);
        $txt = str_replace("&ntilde;", "ñ", $txt);
        $txt = str_replace("&Ntilde;", "Ñ", $txt);
        $txt = str_replace("&NTILDE;", "Ñ", $txt);
        $txt = str_replace("&NBSP;", " ", $txt);
        $txt = str_replace("&nbsp;", " ", $txt);
        return $txt;
    }

    public static function construirJson($mysqli, $exp, $tiporeporte, $sistemadestino, $tipoenvio = '1') {

        $arrJson = array();
        $arrJson["idenvio"] = '';
        $arrJson["tiporeporte"] = $tiporeporte;
        $arrJson["matricula"] = $exp["matricula"];
        $arrJson["idclase"] = $exp["tipoidentificacion"];
        $arrJson["numid"] = $exp["identificacion"];
        $arrJson["nit"] = $exp["nit"];
        $arrJson["razonsocial"] = $exp["nombre"];
        $arrJson["sigla"] = $exp["sigla"];

        $arrJson["apellido1"] = $exp["ape1"];
        $arrJson["apellido2"] = $exp["ape2"];
        $arrJson["nombre1"] = $exp["nom1"];
        $arrJson["nombre2"] = $exp["nom2"];
        $arrJson["organizacion"] = $exp["organizacion"];
        $arrJson["categoria"] = $exp["categoria"];

        $arrJson["estado"] = 'MA';
        switch ($exp["estadomatricula"]) {
            case "MC" :
            case "IC" :
            case "MF" :
            case "MG" :
            case "IF" :
            case "IG" :
                $arrJson["estado"] = 'MC';
                break;
        }

        $arrJson["fecmatricula"] = $exp["fechamatricula"];
        $arrJson["fecrenovacion"] = $exp["fecharenovacion"];
        $arrJson["ultanoren"] = $exp["ultanoren"];
        $arrJson["feccancelacion"] = $exp["fechacancelacion"];

        $arrJson["dircom"] = $exp["dircom"];
        $arrJson["telcom1"] = $exp["telcom1"];
        $arrJson["telcom2"] = $exp["telcom2"];
        $arrJson["telcom3"] = $exp["celcom"];
        $arrJson["emailcom"] = $exp["emailcom"];
        $arrJson["muncom"] = $exp["muncom"];

        $arrJson["dirnot"] = '';
        $arrJson["telnot1"] = '';
        $arrJson["telnot2"] = '';
        $arrJson["telnot3"] = '';
        $arrJson["emailnot"] = '';
        $arrJson["munnot"] = '';

        //
        if ($exp["organizacion"] != '02' && $exp["categoria"] != '3') {
            $arrJson["dirnot"] = $exp["dirnot"];
            $arrJson["telnot1"] = $exp["telnot"];
            $arrJson["telnot2"] = $exp["telnot2"];
            $arrJson["telnot3"] = $exp["celnot"];
            $arrJson["emailnot"] = $exp["emailnot"];
            $arrJson["munnot"] = $exp["munnot"];
        }

        //
        $arrJson["ingresosesperados"] = $exp["ingesperados"];
        $arrJson["activosvinculados"] = $exp["actvin"];
        if ($exp["organizacion"] != '02' && $exp["categoria"] != '2' && $exp["categoria"] != '3') {
            $arrJson["activosvinculados"] = $exp["acttot"];
        }
        $arrJson["personalvinculado"] = $exp["personal"];

        $arrJson["ciiu1"] = '';
        $arrJson["ciiu2"] = '';
        $arrJson["ciiu3"] = '';
        $arrJson["ciiu4"] = '';

        //
        $arrJson["ciiu1"] = substr($exp["ciius"][1], 1);
        if ($exp["ciius"][2] != '') {
            $arrJson["ciiu2"] = substr($exp["ciius"][2], 1);
        }
        if ($exp["ciius"][3] != '') {
            $arrJson["ciiu3"] = substr($exp["ciius"][3], 1);
        }
        if ($exp["ciius"][4] != '') {
            $arrJson["ciiu4"] = substr($exp["ciius"][4], 1);
        }

        //
        $arrJson["ciiu1consector"] = $exp["ciius"][1];
        $arrJson["ciiu2consector"] = $exp["ciius"][2];
        $arrJson["ciiu3consector"] = $exp["ciius"][3];
        $arrJson["ciiu4consector"] = $exp["ciius"][4];

        $arrJson["feciniact1"] = $exp["feciniact1"];
        $arrJson["feciniact2"] = $exp["feciniact2"];
        $arrJson["actividad"] = $exp["desactiv"];
        if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
            if (!isset($exp["crtsii"]["0740"])) {
                $exp["crtsii"]["0740"] = '';
            }
            $arrJson["actividad"] = strip_tags($exp["crtsii"]["0740"]);
            if (trim($arrJson["actividad"]) == '') {
                $arrJson["actividad"] = strip_tags($exp["crt"]["0740"]);
            }
        }

        // propietarios
        $arrJson["propietario"] = array();

        // Si es establecimiento
        if ($exp["organizacion"] == '02') {
            foreach ($exp["propietarios"] as $p) {
                $prop = array();
                $encontroprop = 'no';
                if ($p["matriculapropietario"] != '' && ($p["camarapropietario"] == '' || $p["camarapropietario"] == CODIGO_EMPRESA)) {
                    $p1 = \funcionesSii2::retornarExpedienteMercantilSii($mysqli, $p["matriculapropietario"], '', '', '', '');
                    if ($p1 && !empty($p1)) {
                        $encontroprop = 'si';
                        $prop["camara"] = CODIGO_EMPRESA;
                        $prop["matricula"] = $p1["matricula"];
                        $prop["idclase"] = $p1["tipoidentificacion"];
                        $prop["numid"] = $p1["identificacion"];
                        $prop["nit"] = $p1["nit"];
                        $prop["razonsocial"] = $p1["nombre"];
                        $prop["dircom"] = $p1["dircom"];
                        $prop["telcom1"] = $p1["telcom1"];
                        $prop["telcom2"] = $p1["telcom2"];
                        $prop["telcom3"] = $p1["celcom"];
                        $prop["emailcom"] = $p1["emailcom"];
                        $prop["muncom"] = $p1["muncom"];
                        $prop["dirnot"] = $p1["dirnot"];
                        $prop["telnot1"] = $p1["telnot"];
                        $prop["telnot2"] = $p1["telnot2"];
                        $prop["telnot3"] = $p1["celnot"];
                        $prop["emailnot"] = $p1["emailnot"];
                        $prop["munnot"] = $p1["munnot"];
                        $prop["idclasereplegal"] = '';
                        $prop["numidreplegal"] = '';
                        $prop["nombrereplegal"] = '';
                        if ($p1["organizacion"] > '02' && $p1["categoria"] == '1') {
                            foreach ($p1["vinculos"] as $v) {
                                if ($v["tipovinculo"] == 'RLP') {
                                    if ($prop["idclasereplegal"] == '') {
                                        $prop["idclasereplegal"] = $v["idtipoidentificacionotros"];
                                        $prop["numidreplegal"] = $v["identificacionotros"];
                                        $prop["nombrereplegal"] = $v["nombreotros"];
                                    }
                                }
                            }
                        }
                    }
                }
                if ($encontroprop == 'no') {
                    $prop["camara"] = $p["camarapropietario"];
                    $prop["matricula"] = $p["matriculapropietario"];
                    $prop["idclase"] = $p["idtipoidentificacionpropietario"];
                    $prop["numid"] = $p["identificacionpropietario"];
                    $prop["nit"] = $p["nitpropietario"];
                    $prop["razonsocial"] = $p["nombrepropietario"];
                    $prop["dircom"] = $p["direccionpropietario"];
                    $prop["telcom1"] = $p["telefonopropietario"];
                    $prop["telcom2"] = $p["telefono2propietario"];
                    $prop["telcom3"] = $p["celularpropietario"];
                    $prop["emailcom"] = '';
                    $prop["muncom"] = $p["municipiopropietario"];
                    $prop["dirnot"] = $p["direccionnotpropietario"];
                    $prop["telnot1"] = '';
                    $prop["telnot2"] = '';
                    $prop["telnot3"] = '';
                    $prop["emailnot"] = '';
                    $prop["munnot"] = $p["municipionotpropietario"];
                    $prop["idclasereplegal"] = $p["tipoidreplegpropietario"];
                    $prop["numidreplegal"] = $p["numidreplegpropietario"];
                    $prop["nombrereplegal"] = $p["nomreplegpropietario"];
                }
                $arrJson["propietario"][] = $prop;
            }
        }

        // Falta propietarios sucursales y agencias
        // Representantes legales
        $arrJson["representantelegal"] = array();

        // Si se trata de personas jurídicas principales
        if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
            foreach ($exp["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'RLP') {
                    $rl = array();
                    $rl["idclasereplegal"] = $v["idtipoidentificacionotros"];
                    $rl["numidreplegal"] = $v["identificacionotros"];
                    $rl["nombrereplegal"] = $v["nombreotros"];
                    $arrJson["representantelegal"][] = $rl;
                }
            }
        }

        // Información financiera
        $arrJson["informacionfinanciera"] = array();
        $arrJson["informacionfinanciera"]["acttot"] = 0;
        $arrJson["informacionfinanciera"]["actcte"] = 0;
        $arrJson["informacionfinanciera"]["actnocte"] = 0;
        $arrJson["informacionfinanciera"]["pascte"] = 0;
        $arrJson["informacionfinanciera"]["paslar"] = 0;
        $arrJson["informacionfinanciera"]["pattot"] = 0;
        $arrJson["informacionfinanciera"]["ingope"] = 0;
        $arrJson["informacionfinanciera"]["ingnoope"] = 0;
        $arrJson["informacionfinanciera"]["utiope"] = 0;
        $arrJson["informacionfinanciera"]["utinet"] = 0;
        $arrJson["informacionfinanciera"]["gruponiif"] = '';
        if ($exp["organizacion"] != '02' && $exp["categoria"] != '2' && $exp["categoria"] != '3') {
            $arrJson["informacionfinanciera"] = array();
            $arrJson["informacionfinanciera"]["acttot"] = $exp["acttot"];
            $arrJson["informacionfinanciera"]["actcte"] = $exp["actcte"];
            $arrJson["informacionfinanciera"]["actnocte"] = $exp["actnocte"];
            $arrJson["informacionfinanciera"]["pascte"] = $exp["pascte"];
            $arrJson["informacionfinanciera"]["paslar"] = $exp["paslar"];
            $arrJson["informacionfinanciera"]["pattot"] = $exp["pattot"];
            $arrJson["informacionfinanciera"]["ingope"] = $exp["ingope"];
            $arrJson["informacionfinanciera"]["ingnoope"] = $exp["ingnoope"];
            $arrJson["informacionfinanciera"]["utiope"] = $exp["utiope"];
            $arrJson["informacionfinanciera"]["utinet"] = $exp["utinet"];
            $arrJson["informacionfinanciera"]["gruponiif"] = $exp["gruponiif"];
        }

        //
        $arrJson["idenvio"] = \funcionesSii2::generarAleatorioAlfanumerico20($mysqli, $validar = 'mreg_envio_matriculas_api');

        //
        if ($tipoenvio == '1') {
            $txtJson = json_encode($arrJson);
            $hash = hash('sha256', $txtJson);
            $arrCampos = array(
                'idenvio',
                'sistemadestino',
                'tiporeporte',
                'matricula',
                'fechahoraultimoenvio',
                'json',
                'hashcontrol',
                'estadoenvio',
                'fechahorarespuesta',
                'codigoasignadorespuesta',
                'observaciones'
            );
            $arrValores = array(
                "'" . $arrJson["idenvio"] . "'",
                "'" . $sistemadestino . "'",
                "'" . $tiporeporte . "'",
                "'" . $arrJson["matricula"] . "'",
                "'" . date("Ymd") . ' ' . date("His") . "'",
                "'" . addslashes($txtJson) . "'",
                "'" . $hash . "'",
                "''",
                "''",
                "''",
                "''"
            );
            insertarRegistrosMysqli2($mysqli, 'mreg_envio_matriculas_api', $arrCampos, $arrValores);
        }

        //
        return $arrJson;
    }

    public static function encontrarNuevas($mysqli, $control, $tipoenvio = '1') {
        $matriculas = array();
        $mats = retornarRegistrosMysqli2($mysqli, 'mreg_est_inscritos', "fecmatricula>='" . $_SESSION["entrada"]["fechainicial"] . "' and fecmatricula <= '" . $_SESSION["entrada"]["fechafinal"] . "' and muncom='" . $_SESSION["entrada"]["municipio"] . "'", "matricula", "matricula,ctrestmatricula");
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if ($m["ctrestmatricula"] != 'NA' && $m["ctrestmatricula"] != 'NM') {
                    if ($tipoenvio == '1') {
                        $env = retornarRegistroMysqli2($mysqli, 'mreg_envio_matriculas_api', "sistemadestino='" . $control["sistemadestino"] . "' and tiporeporte='2' and matricula='" . $m["matricula"] . "'");
                        if ($env === false || empty($env) || $env["estadoenvio"] == '' || $env["estadoenvio"] == 'ER') {
                            $matriculas[] = $m["matricula"];
                        }
                    } else {
                        $matriculas[] = $m["matricula"];
                    }
                }
            }
        }
        unset($mats);
        return $matriculas;
    }

    public static function encontrarModificaciones($mysqli, $control, $tipoenvio = '1') {
        $modificaciones = array();
        $condicion = "(fecha >= '" . $_SESSION["entrada"]["fechainicial"] . "') and fecha <= '" . $_SESSION["entrada"]["fechafinal"] . ")";
        $mats = retornarRegistrosMysqli2($mysqli, 'mreg_campos_historicos_' . substr($_SESSION["entrada"]["fechainicial"], 0, 4), $condicion, "matricula");
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if (($m["fecha"] == $_SESSION["entrada"]["fechainicial"] && $m["hora"] >= $_SESSION["entrada"]["horainicial"]) ||
                        $m["fecha"] > $_SESSION["entrada"]["fechainicial"]) {
                    if (($m["fecha"] == $_SESSION["entrada"]["fechafinal"] && $m["hora"] <= $_SESSION["entrada"]["horafinal"]) ||
                            $m["fecha"] < $_SESSION["entrada"]["fechafinal"]) {
                        $modificaciones[$m["matricula"]] = $m["matricula"];
                    }
                }
            }
        }
        return $modificaciones;
    }

    public static function encontrarCancelaciones($mysqli, $control, $tipoenvio = '1') {
        $cancelaciones = array();
        $mats = retornarRegistrosMysqli2($mysqli, 'mreg_est_inscritos', "feccancelacion>='" . $_SESSION["entrada"]["fechainicial"] . "' and muncom='" . $_SESSION["entrada"]["municipio"] . "'", "matricula");
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if ($m["ctrestmatricula"] != 'NA' && $m["ctrestmatricula"] != 'NM') {
                    if ($tipoenvio == '1') {
                        $env = retornarRegistroMysqli2($mysqli, 'mreg_envio_matriculas_api', "sistemadestino='" . $control["sistemadestino"] . "' and tiporeporte='4' and matricula='" . $m["matricula"] . "'", '*', 'U');
                        if ($env === false || empty($env) || $env["estadoenvio"] == '' || $env["estadoenvio"] == 'PE' || $env["estadoenvio"] == 'ER') {
                            $cancelaciones[] = $m["matricula"];
                        }
                    } else {
                        $cancelaciones[] = $m["matricula"];
                    }
                }
            }
        }
        return $cancelaciones;
    }

}
