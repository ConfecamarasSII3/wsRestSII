<?php

function generarCertificadosPdfProponentes($mysqli, $rup, $tipogasto, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ini_set('memory_limit', '6144M');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

    $nameLog = 'generarCertificadosPdfProponentes_' . date("Ymd");

    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }

    //
    if (!class_exists('PDFCerPro')) {

        class PDFCerPro extends TCPDF {

            public $tamanoLetra = 8;
            public $tipogasto = '';
            public $tituloTipo = '';
            public $razonsocial = '';
            public $operacion = '';
            public $recibo = '';
            public $aleatorio = '';
            public $estadodatos = '';
            public $tituloPathAbsoluto = '';
            public $page_counter = true;
            public $fechaSolicitud = '';
            public $HoraSolicitud = '';
            public $certificadoConsultaRues = 'no';
            public $tituloCamara = '';
            public $tituloNombreCamara = '';
            public $pagina = 0;

            public function Make($num) {
                $this->page_counter = $num;
            }

            /* Funcion para rotar un texto */

            public function Rotate($angle, $x = -1, $y = -1) {
                if ($x == - 1)
                    $x = $this->x;
                if ($y == - 1)
                    $y = $this->y;
                if ($this->angle != 0)
                    $this->_out('Q');
                $this->angle = $angle;

                if ($angle != 0) {
                    $angle *= M_PI / 180;
                    $c = cos($angle);
                    $s = sin($angle);
                    $cx = $x * $this->k;
                    $cy = ($this->h - $y) * $this->k;
                    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, - $s, $c, $cx, $cy, - $cx, - $cy));
                }
            }

            /* Funcion que imprime texto rotado */

            public function RotatedText($x, $y, $txt, $angle = 0) {
                $this->Rotate($angle, $x, $y);
                $this->Text($x, $y, $txt);
                $this->Rotate(0);
            }

            public function Header() {
                $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 35, PDF_MARGIN_RIGHT);
                // $this->SetMargins(10, 60, 7);
                // $this->Rect(10, 9, 195, 260);
                if (file_exists($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg')) {
                    $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                }
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 10);

                //
                $this->SetFontSize(8);
                $this->SetTextColor(0, 0, 0);
                $this->writeHTML('<strong>' . str_replace("CAMARA", "CÁMARA", $this->tituloNombreCamara) . '</strong>', true, false, true, false, 'C');
                $this->Ln();

                $txt = 'CERTIFICADO DE INSCRIPCIÓN Y CLASIFICACIÓN EN EL REGISTRO DE PROPONENTES';
                $this->writeHTML('<strong>' . $txt . '</strong>', true, false, true, false, 'C');
                $this->Ln();

                $this->writeHTML('<strong>Fecha expedición: </strong>' . date("d/m/Y") . ' - ' . date("H:i:s") . '</strong>', true, false, true, false, 'C');
                $txt = '';
                if ($this->recibo !== '') {
                    $txt .= 'Recibo No. ' . $this->recibo;
                }
                if (doubleval($this->valor) !== 0) {
                    if ($txt != '') {
                        $txt .= ', ';
                    }
                    $txt .= 'Valor ' . $this->valor;
                }
                if ($txt != '') {
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                $this->Ln();

                //
                // if ($this->tituloTipo != 'Consulta' && $this->tituloTipo != 'Api' && $this->tituloTipo != 'Revision') {
                if ($this->tituloTipo != 'Consulta' && $this->tituloTipo != 'Revision' && $this->tituloTipo != 'Api-Consulta') {
                    if ($this->aleatorio != '') {
                        $this->writeHTML('<strong>CÓDIGO DE VERIFICACIÓN ' . $this->aleatorio . '</strong>', true, false, true, false, 'C');
                        $this->Ln();

                        $txt = 'Verifique el contenido y confiabilidad de este certificado, ingresando a https://sii.confecamaras.co/vista/plantilla/cv.php?empresa=' . CODIGO_EMPRESA . ' y digite el respectivo código, ';
                        $txt .= 'para que visualice la imagen generada al momento de su expedición. La verificación se puede realizar de manera ilimitada, ';
                        $txt .= 'durante 60 días calendario contados a partir de la fecha de su expedición.';

                        $this->writeHTML($txt, true, false, true, false, 'C');
                        $this->Ln();
                    }
                } else {
                    $txt = 'Este es un ejemplo de certificación que se expide solo para consulta, no tiene validez jurídica';
                    $this->SetFontSize(14);
                    $this->writeHTML($txt, true, false, true, false, 'C');
                    $this->SetFontSize(8);
                    $this->writeHTML('-----------------------------------------------------------------------------------------------------------', true, false, true, false, 'C');
                    // $this->Ln();
                }

                //
                $this->Ln();

                //
                if ($this->certificadoConsultaRues == 'si') {
                    $this->SetTextColor(202, 202, 202);
                    $this->SetFontSize(20);
                    $this->RotatedText(30, 200, 'El presente documento cumple lo dispuesto en el artículo 15 del', 45);
                    $this->SetTextColor(0, 0, 0);
                    $this->SetTextColor(202, 202, 202);
                    $this->SetFontSize(20);
                    $this->RotatedText(30, 220, 'Decreto Ley 019/12. Para uso exclusivo de las entidades del Estado', 45);
                    $this->SetTextColor(0, 0, 0);
                }
            }

            public function Header1() {
                $this->SetMargins(10, 40, 7);
                $this->Rect(10, 9, 195, 260);
                if (file_exists($this->tituloPathAbsoluto . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
                    $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 15, 12, 20, 20);
                }
                // $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 15, 12, 20, 20);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 10);

                //
                $this->SetFontSize(8);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . RAZONSOCIAL . '</strong>', true, false, true, false, 'C');
                $this->writeHTML('<strong>' . $this->razonsocial . '</strong>', true, false, true, false, 'C');
                $this->SetTextColor(0, 0, 0);
                //
                $txt = '<strong>Fecha expedición: </strong>' . date("Y/m/d") . ' - ' . date("H:i:s");
                if ($this->recibo != '') {
                    $txt .= ' **** <strong>Recibo No. </strong>' . $this->recibo;
                }
                if ($this->operacion != '') {
                    $txt .= ' **** <strong>Num. Operación. </strong>' . $this->operacion;
                }
                $this->SetFontSize(7);
                $this->writeHTML($txt, true, false, true, false, 'C');

                //
                if (trim($this->estadodatos) != '') {
                    if ($this->estadodatos == '3') {
                        $txt = '!!! El expediente no se encuentra revisado !!!';
                        $this->SeatFontSize(8);
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        if ($this->estadodatos != '6') {
                            $txt = '!!! El expediente tiene trámites pendientes de registro o digitación !!!';
                            $this->SetFontSize(8);
                            $this->writeHTML($txt, true, false, true, false, 'C');
                        }
                    }
                }

                $this->Ln();
                $this->Ln();

                $this->SetFontSize(8);
                switch ($this->tipogasto) {
                    case "Consulta" :
                        $txt = '*** CERTIFICADO DE SOLO CONSULTA SIN VALIDEZ JURIDICA ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                    case "Api" :
                        $txt = '*** EXPEDIDO A TRAVES DEL SISTEMA VIRTUAL S.I.I. ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                    case "Notarias" :
                        $txt = '*** EXPEPIDO A SOLICITUD DE CLIENTES NOTARIALES ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                    case "GasAdm" :
                        $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII) ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                    case "GasAfi" :
                        $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A AFILIADOS ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                    case "GasOfi" :
                        $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A ENTIDAD OFICIAL ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                    case "Normal" :
                        $txt = '*** EXPEDIDO A TRAVES DEL SISTEMA VIRTUAL S.I.I. ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                    case "Dispensador" :
                        $txt = '*** EXPEDIDO A TRAVES DEL DISPENSADOR DE CERTIFICADOS DE LA CAMARA DE COMERCIO ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                    default :
                        $txt = '*** EXPEDIDO A TRAVES DEL SISTEMA VIRTUAL S.I.I. ***';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                        break;
                }
                $this->Ln();
                $y = $this->GetY();
                $this->Line(17, $y, 190, $y);

                if ($this->certificadoConsultaRues == 'si') {
                    $this->SetTextColor(202, 202, 202);
                    $this->SetFontSize(20);
                    $this->RotatedText(30, 200, 'El presente documento cumple lo dispuesto en el artículo 15 del', 45);
                    $this->SetTextColor(0, 0, 0);
                    $this->SetTextColor(202, 202, 202);
                    $this->SetFontSize(20);
                    $this->RotatedText(30, 220, 'Decreto Ley 019/12. Para uso exclusivo de las entidades del Estado', 45);
                    $this->SetTextColor(0, 0, 0);
                }
            }

            public function Footer() {
                $this->SetFontSize(10);
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    /*
     * CREACION DE ARCHIVO PDF
     */
    $estampaInicio = date("H:i:s");

    $pdf = new PDFCerPro(PDF_PAGE_ORIENTATION, PDF_UNIT, "LETTER", true, 'UTF-8', false, true);

    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }

    $pdf->tipogasto = $tipogasto;
    $pdf->tituloTipo = $tipogasto;
    $pdf->razonsocial = $rup["nombre"];
    $pdf->operacion = $operacion;
    $pdf->recibo = $recibo;
    $pdf->aleatorio = $aleatorio;
    $pdf->valor = $valorCertificado;
    $pdf->fechaSolicitud = date("Y/m/d");
    $pdf->HoraSolicitud = date("H:i:s");
    $pdf->certificadoConsultaRues = $certificadoConsultaRues;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = CODIGO_EMPRESA;
    $pdf->tituloNombreCamara = RAZONSOCIAL;

    $tipoFirma = 'FIRMA_SECRETARIO';
    if (!defined('CERTIFICADOS_FIRMA_DIGITAL')) {
        define('CERTIFICADOS_FIRMA_DIGITAL', 'FIRMA_SECRETARIO');
    }
    if (CERTIFICADOS_FIRMA_DIGITAL != '') {
        $tipoFirma = CERTIFICADOS_FIRMA_DIGITAL;
    }


    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integración de Información');
    $pdf->SetTitle('Certificados de Registro de Proponentes SII');
    $pdf->SetSubject('SII');
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, 28);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $pdf->setLanguageArray($l);
    $pdf->setFontSubsetting(false);

    /*
     * INICIO DEL CONTENIDO DEL CERTIFICADO
     */

    $pdf->AddPage();

    // *************************************************************************** //
    // Título del tipo de certificado
    // *************************************************************************** //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $pdf->writeHTML('<strong>CERTIFICADO DE INSCRIPCIÓN Y CLASIFICACIÓN EN EL REGISTRO DE PROPONENTES</strong>', true, false, true, false, 'C');
    $pdf->Ln();

    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $pdf->SetTextColor(0, 0, 0);
    construyeSeccionInformacion($pdf, $rup["codigosbarras"], $rup["reeentramite"], $mysqli);
    \logApi::general2($nameLog, '', 'construyeSeccionInformacion');

    // 2017-10-26: JINT
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $pdf->SetTextColor(0, 0, 0);
    construyeSeccionRecursosReposicion($pdf, $rup, $mysqli);
    \logApi::general2($nameLog, '', 'construyeSeccionRecursosReposicion');

    $txt = '<strong>************   LA SIGUIENTE INFORMACIÓN SE ENCUENTRA EN FIRME   ************</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
    armarBloqueCertificado($pdf, $rup, 'enfirme', $mysqli);
    \logApi::general2($nameLog, '', 'armarBloqueCertificado en firme');

    //
    if (!defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO')) {
        define('ACTIVADO_DECRETO_399_RUP_CERTIFICADO', '');
    }

    $inscripcionesNofirmes = 'no';
    if (isset($rup["nofirme01"]) && $rup["nofirme01"]["datosmodificados"] == 'si') {
        $mos = 'si';
        if ($rup["nofirme01"]["acto"] == '36' && ACTIVADO_DECRETO_399_RUP_CERTIFICADO != 'S') {
            $mos = 'no';
        }
        if ($mos == 'si') {
            $txt = '<strong>*** LA SIGUIENTE INFORMACIÓN SE ENCUENTRA EN PROCESO DE ADQUIRIR FIRMEZA ***</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            armarBloqueCertificado($pdf, $rup, 'nofirme01', $mysqli);
            \logApi::general2($nameLog, '', 'armarBloqueCertificado en nofirme01');
            $inscripcionesNofirmes = 'si';
        }
    }
    if (isset($rup["nofirme02"]) && $rup["nofirme02"]["datosmodificados"] == 'si') {
        $mos = 'si';
        if ($rup["nofirme02"]["acto"] == '36' && ACTIVADO_DECRETO_399_RUP_CERTIFICADO != 'S') {
            $mos = 'no';
        }
        if ($mos == 'si') {
            $txt = '<strong>*** LA SIGUIENTE INFORMACIÓN SE ENCUENTRA EN PROCESO DE ADQUIRIR FIRMEZA ***</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            armarBloqueCertificado($pdf, $rup, 'nofirme02', $mysqli);
            \logApi::general2($nameLog, '', 'armarBloqueCertificado en nofirme02');
            $inscripcionesNofirmes = 'si';
        }
    }
    if (isset($rup["nofirme03"]) && $rup["nofirme03"]["datosmodificados"] == 'si') {
        $mos = 'si';
        if ($rup["nofirme03"]["acto"] == '36' && ACTIVADO_DECRETO_399_RUP_CERTIFICADO != 'S') {
            $mos = 'no';
        }
        if ($mos == 'si') {
            $txt = '<strong>*** LA SIGUIENTE INFORMACIÓN SE ENCUENTRA EN PROCESO DE ADQUIRIR FIRMEZA ***</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            armarBloqueCertificado($pdf, $rup, 'nofirme03', $mysqli);
            \logApi::general2($nameLog, '', 'armarBloqueCertificado en nofirme03');
            $inscripcionesNofirmes = 'si';
        }
    }
    if (isset($rup["nofirme04"]) && $rup["nofirme04"]["datosmodificados"] == 'si') {
        $mos = 'si';
        if ($rup["nofirme04"]["acto"] == '36' && ACTIVADO_DECRETO_399_RUP_CERTIFICADO != 'S') {
            $mos = 'no';
        }
        if ($mos == 'si') {
            $txt = '<strong>*** LA SIGUIENTE INFORMACIÓN SE ENCUENTRA EN PROCESO DE ADQUIRIR FIRMEZA ***</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            armarBloqueCertificado($pdf, $rup, 'nofirme04', $mysqli);
            \logApi::general2($nameLog, '', 'armarBloqueCertificado en nofirme04');
            $inscripcionesNofirmes = 'si';
        }
    }

    if ($inscripcionesNofirmes == 'si') {
        construyeTextoFirmeza($pdf, $mysqli);
        \logApi::general2($nameLog, '', 'construyeTextoFirmeza');
    }


    /*
     * CERTIFICA RECURSOS REPOSICION 2018-07-09 WSI
     */
    if (isset($rup["textorecursosreposicion"])) {
        if (trim($rup["textorecursosreposicion"]) != '') {
            //
            $pdf->writeHTML('<strong>CERTIFICA:<br>RECURSOS DE REPOSICIÓN</strong><br>', true, false, true, false, 'C');
            $pdf->writeHTML($rup["textorecursosreposicion"] . '<br>', true, false, true, false, 'J');
            //
        }
    }

    /*
     * HISTORIA DE INSCRIPCIONES 2017-07-05 WSI
     */
    if (isset($rup["inscripciones"])) {
        if (count($rup["inscripciones"]) > 0) {
            construyeSeccionHistoriaInscripciones($pdf, $rup, $mysqli);
            \logApi::general2($nameLog, '', 'construyeSeccionHistoriaInscripciones');
        }
    }

    /*
     * CONTRATOS
     */
    $incluirtextoree = 'no';
    if (isset($rup["contratos"])) {
        if (count($rup["contratos"]) > 0) {
            construyeSeccionContratos($pdf, $rup, $mysqli);
            \logApi::general2($nameLog, '', 'construyeSeccionContratos');
            $incluirtextoree = 'si';
        }
    }

    /*
     * MULTAS
     */

    if (isset($rup["multas"])) {
        if (count($rup["multas"]) > 0) {
            $mostrarMultas = 0;
            foreach ($rup["multas"] as $m) {
                if (\funcionesGenerales::diferenciaEntreFechaBase30(date("Ymd"), $m["fecreglib"]) < 366) {
                    $mostrarMultas++;
                }
            }
            if ($mostrarMultas > 0) {
                $resx = construyeSeccionMultas($pdf, $rup, $mysqli);
                \logApi::general2($nameLog, '', 'construyeSeccionMultas');
                $incluirtextoree = 'si';
            }
        }
    }

    /*
     * SANCIONES
     */
    if (isset($rup["sanciones"])) {
        if (count($rup["sanciones"]) > 0) {
            $mostrarSanciones = 0;
            foreach ($rup["sanciones"] as $s) {
                if ($s["estadosanc"] != '4') {
                    if (ltrim(trim($s["vigencia"]), "0") != '') {
                        if ($s["vigencia"] > date("Ymd")) {
                            $mostrarSanciones++;
                        }
                    } else {
                        if (\funcionesGenerales::diferenciaEntreFechaBase30(date("Ymd"), $s["fecreglib"]) < 1825) {
                            $mostrarSanciones++;
                        }
                    }
                }
            }
            if ($mostrarSanciones > 0) {
                construyeSeccionSanciones($pdf, $rup, $mysqli);
                \logApi::general2($nameLog, '', 'construyeSeccionSanciones');
                $incluirtextoree = 'si';
            }
        }
    }

    /*
     * SANCIONES DISCIPLINARIAS
     */
    if (isset($rup["sandis"])) {
        if (count($rup["sandis"]) > 0) {
            construyeSeccionSancionesDisciplinarias($pdf, $rup, $mysqli);
            \logApi::general2($nameLog, '', 'construyeSeccionSancionesDisciplinarias');
            $incluirtextoree = 'si';
        }
    }

    if ($incluirtextoree == 'si') {
        construyeTextoRee($pdf, $mysqli);
        \logApi::general2($nameLog, '', 'construyeTextoRee');
    }

    /*
     * INHABILIDADES
     */
    if (isset($rup["inhabilidad"]["inhabilidad"]) && $rup["inhabilidad"]["inhabilidad"] == 'si') {
        construyeSeccionInhabilidad($pdf, $rup["inhabilidad"], $mysqli);
        \logApi::general2($nameLog, '', 'construyeSeccionInhabilidad');
        if (trim($rup["matricula"]) != '') {
            construyeTextoVerificacionDocumental($pdf, $mysqli);
            \logApi::general2($nameLog, '', 'construyeTextoVerificacionDocumental');
        }
    }


    //
    construyeTextoFirmezaFinal($pdf, $mysqli);
    \logApi::general2($nameLog, '', 'construyeTextoFirmezaFinal');

    if ($certificadoConsultaRues != 'si') {

        /*
         * TEXTOS COMPLEMENTARIOS DEL CERTIFICADO (VERIFICACIÓN)  
         */
        $pdf->Ln();
        if ($aleatorio == '') {
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(139, 0, 0);
            construyeTextoConsulta($pdf, $mysqli);
            \logApi::general2($nameLog, '', 'construyeTextoConsulta');
        } else {
            construyeTextoValorCertificado($pdf, $tipogasto, $valorCertificado);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(139, 0, 0);
            construyeTextoFirma($pdf, $tipoFirma);
            \logApi::general2($nameLog, '', 'construyeTextoValorCertificado');

            construyeTextoFirmaQueEs($pdf);
            \logApi::general2($nameLog, '', 'construyeTextoFirmaQueEs');

            construyeTextoFirmaImpresion($pdf, $aleatorio);
            \logApi::general2($nameLog, '', 'construyeTextoFirmaImpresion');

            construyeTextoFirmaVerificacion($pdf);
            \logApi::general2($nameLog, '', 'construyeTextoFirmaVerificacion');

            if ($pdf->tituloTipo != 'Consulta' && $pdf->tituloTipo != 'Revision' && $pdf->tituloTipo != 'Api-Consulta') {
                construyeTextoFirmaMecanica($pdf);
                \logApi::general2($nameLog, '', 'construyeTextoFirmaMecanica');
            }
        }
    }

    // *************************************************************************** //
    // FINAL DEL CERTIFICADO
    // *************************************************************************** //    

    $y = $pdf->GetY() + 20;
    $pdf->SetY($y);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Line(17, $y, 190, $y);
    $pdf->writeHTML('<strong>*** FINAL DEL CERTIFICADO ***</strong>', true, false, true, false, 'C');

    /*
     * CONSTRUYE SALIDA PDF
     */
    $estampaFin = date("H:i:s");
    $segundos = (strtotime($estampaFin) - strtotime($estampaInicio));

    if ($aleatorio == '') {
        $det = 'Consulta';
    } else {
        $det = $aleatorio;
    }

    \logApi::general2($nameLog, $rup["proponente"], 'Tipo gasto : ' . $pdf->tipogasto);
    \logApi::general2($nameLog, $rup["proponente"], $segundos . ' Seg.');

    //
    if ($pdf->tipogasto == 'Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        // ob_end_clean();
        return $name1;
    }

    if ($pdf->tipogasto == 'Api-Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        // ob_end_clean();
        $pdf->Output($name1, "F");
        return $name2;
    }

    if ($pdf->tipogasto == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        // ob_end_clean();
        return $name1;
    }

    //
    $anox = date("Y");
    $mesx = sprintf("%02s", date("m"));
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados')) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados', 0777);
        \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados');
    }
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox, 0777);
        \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox);
    }
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx, 0777);
        \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx);
    }
    $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    // ob_end_clean();
    $pdf->Output($name1, "F");
    \logApi::general2($nameLog, $rup["proponente"], 'Genero el certificado sin firma al repositorio : ' . $name1);

    /*
     * WSIERRA 2017/09/14 - Incluir firma digital en certificados diferentes de consulta RUES
     */
    if ($certificadoConsultaRues != 'si') {
        $msg = 'Recibo : ' . $recibo . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . '(' . $escajero . ') (' . $esbanco . ') | Proponente : ' . $rup["proponente"] . ' | Tipo : CerPro | Hora : ' . date("His");
        $nameLog1 = 'controlFirmaCertificados_' . date("Ymd");
        if (defined('ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS')) {
            if ((ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $escajero != 'SI') ||
                    ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI-TODOS' || (ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $firmar == 'si')
            ) {
                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/PDFA.class.php');
                $ins = new PDFA();
                $ins->generarPDFAfirmado($aleatorio, $name1, 'no');
                $msg .= '| Firmado digitalmente (' . date("His") . ')';
                \logApi::general2($nameLog1, '', $msg);
            } else {
                $msg .= '| Sin Firma digital';
                \logApi::general2($nameLog1, '', $msg);
            }
        } else {
            $msg .= '| Sin Firma digital';
            \logApi::general2($nameLog1, '', $msg);
        }
    } else {
        \logApi::general2($nameLog, $rup["proponente"], 'Por ser un certificado RUES, no lo firma');
    }

    // ob_end_clean();
    \logApi::general2($nameLog, $rup["proponente"], 'Retorno el certificado : ' . $name2);
    return $name2;
}

function armarBloqueCertificado($pdf, $rup, $grupo, $mysqli = null) {

    /*
     * VERIFICA PUBLICACIÓN EN RUES EN SECCIONES CON NO FIRMEZA
     */

    if (($grupo == 'nofirme01')) {
        if ($rup[$grupo]["fecpublicacionrue"] == '') {
            $rup[$grupo]["fecpublicacionrue"] = 'no';
        }
        construyeSeccionInscripcionNofirme($pdf, $rup, $grupo, $mysqli);
    }
    if (($grupo == 'nofirme02')) {
        if ($rup[$grupo]["fecpublicacionrue"] == '') {
            $rup[$grupo]["fecpublicacionrue"] = 'no';
        }
        construyeSeccionInscripcionNofirme($pdf, $rup, $grupo, $mysqli);
    }
    if (($grupo == 'nofirme03')) {
        if ($rup[$grupo]["fecpublicacionrue"] == '') {
            $rup[$grupo]["fecpublicacionrue"] = 'no';
        }
        construyeSeccionInscripcionNofirme($pdf, $rup, $grupo, $mysqli);
    }
    if (($grupo == 'nofirme04')) {
        if ($rup[$grupo]["fecpublicacionrue"] == '') {
            $rup[$grupo]["fecpublicacionrue"] = 'no';
        }
        construyeSeccionInscripcionNofirme($pdf, $rup, $grupo, $mysqli);
    }

    /*
     * IDENTIFICACIÓN
     */
    if ($grupo == 'enfirme') {
        construyeSeccionIdentificacionSociedadExtranjera($pdf, $rup, $grupo, $mysqli);
        construyeSeccionIdentificacion($pdf, $rup, $grupo, $mysqli);
    }

    /*
     * TAMAÑO DE EMPRESA
     */
    if ($grupo != 'enfirme' && $grupo != '') {
        if (isset($rup[$grupo]["tamanoempresa"])) {
            if (trim($rup[$grupo]["tamanoempresa"]) != '') {
                construyeSeccionTamEmpresa($pdf, $rup, $grupo, $mysqli);
            }
        }
    }

    /*
     * PERSONA JURIDICA
     */
    if (($rup["organizacion"] != '01') && ($rup["organizacion"] != '13')) {
        if (
                (isset($rup[$grupo]["idtipodocperjur"]) && trim($rup[$grupo]["idtipodocperjur"]) != '') ||
                (isset($rup[$grupo]["numdocperjur"]) && trim($rup[$grupo]["numdocperjur"]) != '') ||
                (isset($rup[$grupo]["fecdocperjur"]) && trim($rup[$grupo]["fecdocperjur"]) != '') ||
                (isset($rup[$grupo]["origendocperjur"]) && trim($rup[$grupo]["origendocperjur"]) != '') ||
                (isset($rup[$grupo]["fechaconstitucion"]) && trim($rup[$grupo]["fechaconstitucion"]) != '') ||
                (isset($rup[$grupo]["fechavencimiento"]) && trim($rup[$grupo]["fechavencimiento"]) != '')
        ) {
            construyeSeccionInscripcionRenovacion($pdf, $rup, $grupo, $mysqli);
        }
    }

    /*
     * REPRESENTACIÓN LEGAL
     */
    if ($rup["organizacion"] != '01' && $rup["organizacion"] != '13') {
        if ($rup["organizacion"] != '08') {
            construyeSeccionConstitucionRepLegal($pdf, $rup, $grupo, $mysqli);
        } else {
            if (isset($rup["crtsii"]["9601"]) && trim($rup["crtsii"]["9601"]) != '') {
                construyeSeccionConstitucionRepLegalSociedadExtranjera($pdf, $rup, $grupo, $mysqli);
            } else {
                construyeSeccionConstitucionRepLegal($pdf, $rup, $grupo, $mysqli);
            }
        }
    }

    /*
     * FACULTADES
     */
    if ($rup["organizacion"] != '01' && $rup["organizacion"] != '13') {
        construyeSeccionFacultades($pdf, $rup, $grupo, $mysqli);
    }

    /*
     * DOMICILIO
     */
    if ($grupo == 'enfirme') {
        construyeSeccionDomicilio($pdf, $rup, $grupo, $mysqli);
    }

    /*
     * CAMBIO DE DOMICILIO
     */
    if ($grupo == 'enfirme') {
        if (trim($rup[$grupo]["cambidom_idmunicipioorigen"]) != '') {
            construyeSeccionCambioDomicilio($pdf, $rup, $mysqli);
        }
    }


    /*
     * SITUACIONES DE CONTROL
     */
    if ($rup["organizacion"] != '01' && $rup["organizacion"] != '13') {
        construyeSeccionSituacionesControl($pdf, $rup, $grupo, $mysqli);
    }

    /*
     * CLASIFICACIONES
     */
    /*
      if ($grupo == 'enfirme') {
      if (isset($rup[$grupo]["clasi1510"])) {
      if (count($rup[$grupo]["clasi1510"]) > 0) {
      construyeSeccionClasificaciones1510($pdf, $rup, $grupo);
      }
      }
      }
     */
    if ($grupo == 'enfirme') {
        if (isset($rup["clasi1510"])) {
            if (count($rup["clasi1510"]) > 0) {
                construyeSeccionClasificaciones1510($pdf, $rup, $grupo, $mysqli);
            }
        }
    }

    /*
     * INFORMACIÓN FINANCIERA
     */
    construyeSeccionInformacionFinanciera($pdf, $rup, $grupo, $mysqli);
    /*
      if (isset($rup[$grupo]["inffin1510_fechacorte"])) {
      if (trim($rup[$grupo]["inffin1510_fechacorte"]) != '') {
      construyeSeccionInformacionFinanciera($pdf, $rup, $grupo, $mysqli);
      }
      }
     */

    /*
     * EXPERIENCIA
     */
    if (isset($rup[$grupo]["exp1510"])) {
        if (count($rup[$grupo]["exp1510"]) > 0) {
            construyeSeccionExperiencia($pdf, $rup, $grupo, $mysqli);
        }
    }

    unset($pdf);
    unset($rup);
}

function construyeSeccionInformacion($pdf, $cantidadCodigoBarras, $reeentramite, $mysqli = null) {

    $txt = '<strong>INFORMA :</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();

    if (intval($cantidadCodigoBarras) > 0) {
        $txt = 'A LA FECHA DE EXPEDICIÓN DE ESTE CERTIFICADO EXISTE UNA ';
        $txt .= 'SOLICITUD DE (RENOVACION O ACTUALIZACIÓN) LA CUAL SE ENCUENTRA, ';
        $txt .= 'EN TRÁMITE LO QUE EVENTUALMENTE PUEDE AFECTAR EL CONTENIDO DE LA ';
        $txt .= 'INFORMACIÓN QUE CONSTA EN ESTA CERTIFICACIÓN.<br>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }

    if ($reeentramite == 'si') {
        $txt = 'A LA FECHA DE EXPEDICIÓN DE ESTE CERTIFICADO EXISTE UNA ';
        $txt .= 'ACTUALIZACIÓN EN CURSO POR REPORTE DE INFORMACIÓN DE ENTIDAD.<BR>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }

    $txt = 'LA ' . RAZONSOCIAL . ' ,CON FUNDAMENTO EN ';
    $txt .= 'LO DISPUESTO EN EL ARTICULO 6.1 DE LA LEY 1150 DE 2007, ';
    $txt .= 'REGLAMENTADA POR EL DECRETO 1510 DE 2013, INCORPORADO EN EL ';
    $txt .= 'DECRETO 1082 DE 2015, CON BASE EN LA INFORMACION SUMINISTRADA POR ';
    $txt .= 'EL INSCRITO Y POR LAS ENTIDADES ESTATALES.<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function construyeSeccionRecursosReposicion($pdf, $rup, $mysqli = null) {

    $tienerecursos = 'no';
    foreach ($rup["lcodigosbarras"] as $lcb) {
        if ($lcb["ttra"] == '27') {
            $tienerecursos = 'si';
        }
    }
    if ($tienerecursos == 'si') {
        $txt = '<strong>RECURSOS DE REPOSICIÓN EN TRÁMITE :</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($rup["lcodigosbarras"] as $lcb) {
            if ($lcb["ttra"] == '27') {
                $temLiq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numeroradicacion='" . $lcb["cbar"] . "'");
                $temCampos = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $temLiq["idliquidacion"], "campo");
                $arrCmp = array();
                foreach ($temCampos as $c) {
                    $arrCmp[$c["campo"]] = $c["contenido"];
                }
                unset($temCampos);
                $txt = 'LA CAMARA DE COMERCIO INFORMA QUE EL ' . \funcionesGenerales::mostrarFecha($temLiq["fecharecibo"]) . ' SE RADICO ';
                $txt .= 'UN RECURSO DE REPOSICIÓN QUE AFECTA EL EXPEDIENTE. LA INFORMACION DEL ';
                $txt .= 'RECURSO RADICADO ES LA SIGUIENTE :<br><br>';
                $txt .= '<strong>NÚMERO DEL RECURSO: </strong>' . $temLiq["numdoc"] . '<br>';
                $txt .= '<strong>FECHA DEL RECURSO: </strong>' . $temLiq["fechadoc"] . '<br>';
                $txt .= '<strong>ORIGEN: </strong>' . $temLiq["origendoc"] . '<br>';
                $txt .= '<strong>MUNICIPIO: </strong>' . retornarNombreMunicipioMysqliApi($mysqli, $temLiq["mundoc"]) . '<br>';
                $txt .= '<strong>DESCRIPCION: </strong>' . $arrCmp["descripcionrr"] . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                unset($arrCmp);
                unset($temLiq);
            }
        }
    }
}

function construyeSeccionIdentificacionSociedadExtranjera($pdf, $rup, $grupo, $mysqli = null) {
    if (isset($rup["crtsii"]['9600']) && trim($rup["crtsii"]['9600']) != '') {
        $txt = '<strong>CERTIFICA:<br>INFORMACIÓN DE LA SOCIEDAD EXTRANJERA</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        construyeCertificaTexto($pdf,$rup,'9600');
    }
}

function construyeSeccionIdentificacion($pdf, $rup, $grupo, $mysqli = null) {
    $txt = '<strong>CERTIFICA:<br>IDENTIFICACIÓN</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');

    if ($rup["organizacion"] != '08') {
        $txt = '<strong>NOMBRE:</strong>' . $rup[$grupo]["nombre"] . '<br>';
    } else {
        $txt = '<strong>NOMBRE DE LA SUCURSAL EN COLOMBIA:</strong>' . $rup[$grupo]["nombre"] . '<br>';
    } 
    if (trim($rup[$grupo]["sigla"]) != '') {
        $txt .= '<strong>SIGLA:</strong>' . $rup[$grupo]["sigla"] . '<br>';
    }

    if (ltrim($rup[$grupo]["nit"], "0") != '') {
        $sp = \funcionesGenerales::separarDv($rup[$grupo]["nit"]);
        if ($rup["organizacion"] != '08') {
            $txt .= '<strong>NIT:</strong>' . $sp["identificacion"] . '-' . $sp["dv"] . '<br>';
        } else {
            $txt .= '<strong>NIT DE LA SUCURSAL EN COLOMBIA:</strong>' . $sp["identificacion"] . '-' . $sp["dv"] . '<br>';
        }
    }

    if ($rup["organizacion"] == '01') {
        $txt .= '<strong>C.C.:</strong>' . $rup[$grupo]["identificacion"] . '<br>';
    }

    if (trim($rup[$grupo]["nacionalidad"]) != '') {
        if ($rup["organizacion"] != '08') {
            $txt .= '<strong>NACIONALIDAD:</strong>' . $rup[$grupo]["nacionalidad"] . '<br>';
        }
    }
    if (trim($rup[$grupo]["matricula"]) != '' && substr($rup[$grupo]["matricula"], 0, 1) != 'S') {
        if ($rup["organizacion"] != '08') {
            $txt .= '<strong>MATRICULA MERCANTIL:</strong>' . $rup[$grupo]["matricula"] . '<br>';
        } else {
            $txt .= '<strong>MATRICULA MERCANTIL DE LA SUCURSAL EN COLOMBIA:</strong>' . $rup[$grupo]["matricula"] . '<br>';
        }
    }
    if (isset($rup[$grupo]["fecmatricula"]) != '' && $rup[$grupo]["fecmatricula"] != '' && trim($rup[$grupo]["matricula"]) != '' && substr($rup[$grupo]["matricula"], 0, 1) != 'S') {
        if ($rup["organizacion"] != '08') {
            $txt .= '<strong>FECHA DE MATRÍCULA EN EL REGISTRO MERCANTIL:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fecmatricula"]) . '<br>';
        } else {
            $txt .= '<strong>FECHA DE MATRÍCULA DE LA SUCURSAL EN COLOMBIA EN EL REGISTRO MERCANTIL:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fecmatricula"]) . '<br>';
        }
    }
    if (trim($rup[$grupo]["matricula"]) != '' && substr($rup[$grupo]["matricula"], 0, 1) == 'S') {
        $txt .= '<strong>INSCRIPCION REGISTRO DE ENTIDADES SIN ANIMO DE LUCRO:</strong>' . $rup[$grupo]["matricula"] . '<br>';
    }
    if (isset($rup[$grupo]["fecmatricula"]) != '' && $rup[$grupo]["fecmatricula"] != '' && trim($rup[$grupo]["matricula"]) != '' && substr($rup[$grupo]["matricula"], 0, 1) == 'S') {
        $txt .= '<strong>FECHA DE INSCRIPCIÓN AL REGISTRO DE ENTIDADES SIN ANIMO DE LUCRO:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fecmatricula"]) . '<br>';
    }

    if (trim($rup[$grupo]["proponente"]) != '') {
        $txt .= '<strong>NÚMERO DEL PROPONENTE:</strong>' . $rup[$grupo]["proponente"] . '<br>';
    }
    if (trim($rup[$grupo]["fechaultimainscripcion"]) != '') {
        $txt .= '<strong>FECHA DE LA ÚLTIMA INSCRIPCIÓN EN EL REGISTRO DE PROPONENTES:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechaultimainscripcion"]) . '<br>';
    }
    if ($rup[$grupo]["fechaultimarenovacion"] != '') {
        if (trim($rup[$grupo]["fechaultimainscripcion"]) < trim($rup[$grupo]["fechaultimarenovacion"])) {
            $txt .= '<strong>FECHA DE LA ÚLTIMA RENOVACIÓN EN EL REGISTRO DE PROPONENTES:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechaultimarenovacion"]) . '<br>';
        }
    } else {
        if ($rup[$grupo]["cambidom_fechaultimarenovacion"] != '') {
            // if (trim($rup[$grupo]["fechaultimainscripcion"]) < trim($rup[$grupo]["cambidom_fechaultimarenovacion"])) {
                $txt .= '<strong>FECHA DE LA ÚLTIMA RENOVACIÓN EN EL REGISTRO DE PROPONENTES:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["cambidom_fechaultimarenovacion"]) . '<br>';            
            // }
        }
    }

    if (trim($rup[$grupo]["organizacion"]) != '') {
        $txt .= '<strong>ORGANIZACIÓN:</strong>' . strtoupper(retornarNombreTablaBasicaMysqliApi($mysqli, 'bas_organizacionjuridica', $rup[$grupo]["organizacion"])) . '<br>';
    }
    if (trim($rup[$grupo]["tamanoempresa"]) != '') {
        $txt .= '<strong>TAMAÑO DE EMPRESA:</strong>' . mb_strtoupper(retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_seltamano', $rup[$grupo]["tamanoempresa"]), 'utf-8') . '<br>';
    }

    $pdf->writeHTML($txt, true, false, true, false, 'L');

    // unset($pdf);
    // unset($rup);
}

function construyeSeccionDomicilio($pdf, $rup, $grupo, $mysqli = null) {
    $txt = '<strong>CERTIFICA:<br>DOMICILIO</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');

    $txt = '<strong>DIRECCIÓN DEL DOMICILIO PRINCIPAL:</strong>' . $rup[$grupo]["dircom"] . '<br>';
    if (trim($rup[$grupo]["muncom"]) != '') {
        $txt .= '<strong>MUNICIPIO:</strong>' . $rup[$grupo]["muncom"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $rup[$grupo]["muncom"]) . '<br>';
        $txt .= '<strong>DEPARTAMENTO:</strong>' . retornarNombreDptoMysqliApi($mysqli, $rup[$grupo]["muncom"]) . '<br>';
    }
    if (isset($rup[$grupo]["paicom"]) && trim($rup[$grupo]["paicom"]) != '') {
        $txt .= '<strong>PAIS:</strong>' . \funcionesGenerales::retornarNombrePaisAbreviado($mysqli, $rup[$grupo]["paicom"]) . '<br>';
    }

    if (isset($rup[$grupo]["barriocom"])) {
        if (trim($rup[$grupo]["barriocom"]) != '') {
            $txt .= '<strong>BARRIO:</strong>' . retornarNombreBarrioMysqliApi($mysqli, $rup[$grupo]["muncom"], $rup[$grupo]["barriocom"]) . '<br>';
        }
    }

    if (trim($rup[$grupo]["telcom1"]) != '') {
        $txt .= '<strong>TELEFONO 1:</strong>' . $rup[$grupo]["telcom1"] . '<br>';
    }
    if (trim($rup[$grupo]["telcom2"]) != '') {
        $txt .= '<strong>TELEFONO 2:</strong>' . $rup[$grupo]["telcom2"] . '<br>';
    }
    if (trim($rup[$grupo]["celcom"]) != '') {
        $txt .= '<strong>TELEFONO 3:</strong>' . $rup[$grupo]["celcom"] . '<br>';
    }
    if (trim($rup[$grupo]["emailcom"]) != '') {
        $txt .= '<strong>CORREO ELECTRÓNICO:</strong>' . $rup[$grupo]["emailcom"] . '<br>';
    }
    $pdf->writeHTML($txt, true, false, true, false, 'L');

    $txt = '<strong>DIRECCIÓN PARA NOTIFICACIONES:</strong>' . $rup[$grupo]["dirnot"] . '<br>';
    if (trim($rup[$grupo]["munnot"]) != '') {
        $txt .= '<strong>MUNICIPIO:</strong>' . $rup[$grupo]["munnot"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $rup[$grupo]["munnot"]) . '<br>';
        $txt .= '<strong>DEPARTAMENTO:</strong>' . retornarNombreDptoMysqliApi($mysqli, $rup[$grupo]["munnot"]) . '<br>';
    }
    if (isset($rup[$grupo]["painot"]) && trim($rup[$grupo]["painot"]) != '') {
        $txt .= '<strong>PAIS:</strong>' . \funcionesGenerales::retornarNombrePaisAbreviado($mysqli, $rup[$grupo]["painot"]) . '<br>';
    }
    if (isset($rup[$grupo]["barrionot"])) {
        if (trim($rup[$grupo]["barrionot"]) != '') {
            $txt .= '<strong>BARRIO:</strong>' . retornarNombreBarrioMysqliApi($mysqli, $rup[$grupo]["munnot"], $rup[$grupo]["barrionot"]) . '<br>';
        }
    }

    if (trim($rup[$grupo]["telnot"]) != '') {
        $txt .= '<strong>TELEFONO 1  :</strong>' . $rup[$grupo]["telnot"] . '<br>';
    }
    if (trim($rup[$grupo]["telnot2"]) != '') {
        $txt .= '<strong>TELEFONO 2:</strong>' . $rup[$grupo]["telnot2"] . '<br>';
    }
    if (trim($rup[$grupo]["celnot"]) != '') {
        $txt .= '<strong>TELEFONO 3:</strong>' . $rup[$grupo]["celnot"] . '<br>';
    }
    if (trim($rup[$grupo]["emailnot"]) != '') {
        $txt .= '<strong>CORREO ELECTRÓNICO:</strong>' . $rup[$grupo]["emailnot"] . '<br>';
    }
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    if (trim($rup["matricula"]) != '') {
        construyeTextoNoSujetoVerificacionDocumental($pdf, 'construyeSeccionDomicilio', $mysqli);
    }
}

function construyeSeccionCambioDomicilio($pdf, $rup, $mysqli = null) {

    $numRegistro = '';
    $fecRegistro = '';
    foreach ($rup["inscripciones"] as $reg) {
        if ($reg["acto"] == '16') {
            $numRegistro = $reg["registro"];
            $fecRegistro = $reg["fecharegistro"];
        }
    }

    if ($numRegistro != '') {
        $txt = '<strong>CERTIFICA:<br>CAMBIO DE DOMICILIO</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $txt = 'QUE ' . $rup["nombre"] . ' TRASLADO SU DOMICILIO DE ' . retornarNombreMunicipioMysqliApi($mysqli, $rup["cambidom_idmunicipioorigen"]) . ' ';
        $txt .= 'A LA CIUDAD DE ' . retornarNombreMunicipioMysqliApi($mysqli, $rup["cambidom_idmunicipiodestino"]) . ', ';
        $txt .= 'QUE DICHO TRASLADO SE INSCRIBIÓ EN EL LIBRO PRIMERO DE LOS PROPONENTES BAJO EL NÚMERO ' . $numRegistro . ' ';
        $txt .= 'DEL DÍA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecRegistro)) . '.' . chr(10) . chr(13);
        $pdf->MultiCell(180, 4, $txt, 0, 'J', 0);
        $pdf->Ln();
    }
}

function construyeSeccionInscripcionRenovacion($pdf, $rup, $grupo, $mysqli = null) {
    if ($grupo == 'ENFIRME') {
        $txt = '<strong>CERTIFICA:<br>INSCRIPCIÓN Y RENOVACIÓN</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        if (trim($rup[$grupo]["fechaultimainscripcion"]) != '') {
            $txt .= '<strong>FECHA ÚLTIMA INSCRIPCIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechaultimainscripcion"]) . '<br>';
        }
        if (trim($rup[$grupo]["fechaultimarenovacion"]) != '') {
            $txt .= '<strong>FECHA ÚLTIMA RENOVACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechaultimarenovacion"]) . '<br>';
        }
        if (trim($rup[$grupo]["fechaultimaactualizacion"]) != '') {
            $txt .= '<strong>FECHA ÚLTIMA ACTUALIZACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechaultimaactualizacion"]) . '<br>';
        }
        if (trim($rup[$grupo]["fechacancelacion"]) != '') {
            $txt .= '<strong>FECHA ÚLTIMA CANCELACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechacancelacion"]) . '<br>';
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    } else {
        if (trim($rup["matricula"]) != '') {
            $txt = '<strong>CERTIFICA:<br>INSCRIPCIÓN Y RENOVACIÓN</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInscripcionRenovacion', $mysqli);
        }
    }
}

function construyeSeccionInscripcionNofirme($pdf, $rup, $grupo, $mysqli = null) {

    $accionVerbo = '__________________';
    $accionTexto = '__________________';
    switch ($rup[$grupo]["acto"]) {
        case '01':
            $accionVerbo = 'INSCRIBIÓ';
            $accionTexto = 'INSCRIPCIÓN';
            break;
        case '02':
            $accionVerbo = 'RENOVÓ';
            $accionTexto = 'RENOVACIÓN';
            break;
        case '03':
            $accionVerbo = 'ACTUALIZÓ';
            $accionTexto = 'ACTUALIZACIÓN';
            break;
        case '36':
            $accionVerbo = 'ACTUALIZÓ';
            $accionTexto = 'ACTUALIZACIÓN AL DECRETO 399';
            break;
    }

    $txt = '<strong>CERTIFICA:</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    if ($rup[$grupo]["fecpublicacionrue"] != 'no') {
        $txt = 'QUE EL DÍA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($rup[$grupo]["fecharegistro"])) . ' EL PROPONENTE SE ' . $accionVerbo . ' ';
        $txt .= 'EN EL REGISTRO ÚNICO DE PROPONENTES BAJO EL NÚMERO ' . $rup[$grupo]["registro"] . ' DEL LIBRO PRIMERO ';
        $txt .= 'DE LOS PROPONENTES, QUE ESTA INSCRIPCIÓN SE PUBLICÓ EN EL REGISTRO ÚNICO EMPRESARIAL Y SOCIAL EL DÍA ';
        $txt .= strtoupper(\funcionesGenerales::mostrarFechaLetras1($rup[$grupo]["fecpublicacionrue"])) . '. QUE LA SIGUIENTE INFORMACIÓN, ';
        $txt .= 'REPORTADA EN LA ' . $accionTexto . ' SE ENCUENTRA EN PROCESO DE ADQUIRIR FIRMEZA.' . chr(10) . chr(13) . chr(10) . chr(13);
    } else {
        $txt = 'QUE EL DÍA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($rup[$grupo]["fecharegistro"])) . ' EL PROPONENTE SE ' . $accionVerbo . ' ';
        $txt .= 'EN EL REGISTRO ÚNICO DE PROPONENTES BAJO EL NÚMERO ' . $rup[$grupo]["registro"] . ' DEL LIBRO PRIMERO ';
        $txt .= 'DE LOS PROPONENTES, QUE ESTA INSCRIPCIÓN ESTA PENDIENTE DE SER PUBLICADA EN EL REGISTRO ÚNICO EMPRESARIAL Y SOCIAL. ';
        $txt .= 'QUE LA SIGUIENTE INFORMACIÓN, REPORTADA EN LA ' . $accionTexto . ' SE ENCUENTRA EN PROCESO DE ADQUIRIR FIRMEZA.' . chr(10) . chr(13) . chr(10) . chr(13);
    }

    $pdf->MultiCell(180, 4, $txt, 0, 'J', 0);
    // $pdf->Ln();
}

function construyeSeccionSituacionesControl($pdf, $rup, $grupo, $mysqli = null) {
    if (isset($rup[$grupo]["sitcontrol"]) && !empty($rup[$grupo]["sitcontrol"])) {

        $txt = '<strong>SITUACIONES DE CONTROL</strong><br>';
        $i = 0;
        foreach ($rup[$grupo]["sitcontrol"] as $sc) {
            $txtDom = retornarNombreMunicipioMysqliApi($mysqli, $sc["domicilio"]);
            $i++;
            $txt = '<strong>SITUACIÓN DE CONTROL No.' . $i . ' :</strong><br>';
            $txt .= '<strong>NOMBRE________:</strong>' . $sc["nombre"] . '<br>';
            $txt .= '<strong>IDENTIFICACIÓN:</strong>' . $sc["identificacion"] . '<br>';
            $txt .= '<strong>DOMICILIO_____:</strong>' . $sc["domicilio"] . ' - ' . $txtDom . '<br>';
            $tctipo = '';
            switch ($sc["tipo"]) {
                case "0": $tctipo = 'MATRIZ';
                    break;
                case "1": $tctipo = 'SUBORDINADA';
                    break;
                case "2": $tctipo = 'CONTROLANTE';
                    break;
                case "3": $tctipo = 'CONTROLADA';
                    break;
            }
            $txt .= '<strong>TIPO__________:</strong>' . $tctipo . '<br>';
            $txtF = str_replace("_", "&nbsp;", $txt);
            $pdf->writeHTML($txtF, true, false, true, false, 'L');
            $pdf->Ln();
        }
        construyeTextoVerificacionDocumental($pdf, 'construyeSeccionSituacionesControl', $mysqli);
    }
}

function construyeSeccionConstitucionRepLegal($pdf, $rup, $grupo, $mysqli = null) {

    if (isset($rup[$grupo]["fechaconstitucion"])) {

        if ((trim($rup[$grupo]["fechaconstitucion"]) != '') || isset($rup[$grupo]["representanteslegales"])) {
            $txt = '<strong>CERTIFICA:<br>CONSTITUCIÓN Y REPRESENTACIÓN LEGAL</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
        }

        if (trim($rup[$grupo]["organizacion"]) != '01') {
            // if (trim($rup[$grupo]["fechaconstitucion"]) != '') {

            $txt = '<strong>INFORMACIÓN DE CONSTITUCIÓN</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');

            if ($rup[$grupo]["fechaconstitucion"] != '') {
                $txt = '';
                $txt .= '<strong>FECHA DE CONSTITUCIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechaconstitucion"]) . '<br>';

                if (trim($rup[$grupo]["idtipodocperjur"]) != '') {
                    $txt .= '<strong>CLASE DE DOCUMENTO:</strong>' . retornarRegistroMysqliApi($mysqli, "mreg_tipos_documentales_registro", "id='" . $rup[$grupo]["idtipodocperjur"] . "'", "descripcion") . '<br>';
                }
                if (trim($rup[$grupo]["numdocperjur"]) != '') {
                    $txt .= '<strong>NÚMERO DE DOCUMENTO:</strong>' . $rup[$grupo]["numdocperjur"] . '<br>';
                }
                if (trim($rup[$grupo]["fecdocperjur"]) != '') {
                    $txt .= '<strong>FECHA DEL DOCUMENTO:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fecdocperjur"]) . '<br>';
                }
                if (trim($rup[$grupo]["origendocperjur"]) != '') {
                    $txt .= '<strong>ENTIDAD QUE EXPIDE EL DOCUMENTO:</strong>' . $rup[$grupo]["origendocperjur"] . '<br>';
                }

                if (trim($rup[$grupo]["fechavencimiento"]) != '') {
                    $txt .= '<strong>FECHA DE VENCIMIENTO:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechavencimiento"]) . '<br>';
                } else {
                    $txt .= '<strong>FECHA DE VENCIMIENTO:</strong>INDEFINIDA<br>';
                }
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            }


            if ($rup[$grupo]["fechaconstitucion"] == '') {
                if (isset($rup[$grupo]["crt0041"]) && trim($rup[$grupo]["crt0041"]) != '') {
                    $txt1 = $rup[$grupo]["crt0041"];
                    $txt1 = str_replace("||", "|", $txt1);
                    $txt1 = str_replace("|", " ", $txt1);
                    $txt = $txt1 . '<br>';
                }

                if (trim($rup[$grupo]["fechavencimiento"]) != '') {
                    $txt .= '<br><strong>FECHA DE VENCIMIENTO:</strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["fechavencimiento"]) . '<br>';
                }
                $pdf->writeHTML($txt, true, false, true, false, 'J');
            }
        }
    }



    if (isset($rup[$grupo]["representanteslegales"])) {
        $txt = '<strong>REPRESENTACIÓN LEGAL</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        if (count($rup[$grupo]["representanteslegales"]) > 0) {
            $i = 0;
            foreach ($rup[$grupo]["representanteslegales"] as $rep) {
                $i++;
                if ($rep["idtipoidentificacionrepleg"] != '7') {
                    $txt = '<strong>REPRESENTANTE No.' . $i . ' :</strong><br>';
                    $txt .= '<strong>NOMBRE:</strong>' . $rep["nombrerepleg"] . '<br>';
                    $txt .= '<strong>IDENTIFICACIÓN:</strong>' . retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipoidentificacion', $rep["idtipoidentificacionrepleg"]) . ' - ' . $rep["identificacionrepleg"] . '<br>';
                    $txt .= '<strong>CARGO:</strong>' . $rep["cargorepleg"] . '<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'L');
                } else {
                    $txt = '<strong>REPRESENTANTE No.' . $i . ' :</strong><br>';
                    $txt .= '<strong>NOMBRE:</strong>' . $rep["nombrerepleg"] . '<br>';
                    // $txt .= '<strong>IDENTIFICACIÓN:</strong>' . $rep["identificacionrepleg"] . '<br>';
                    $txt .= '<strong>CARGO:</strong>' . $rep["cargorepleg"] . '<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'L');
                }
            }
        }
    }

    if (isset($rup[$grupo]["crt1121"]) && trim($rup[$grupo]["crt1121"]) != '') {
        $txt1 = $rup[$grupo]["crt1121"];
        $txt1 = str_replace("||", "|", $txt1);
        $txt1 = str_replace("|", " ", $txt1);
        $txt = $txt1 . '<br>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }
}

function construyeSeccionConstitucionRepLegalSociedadExtranjera($pdf, $rup, $grupo, $mysqli = null) {

    if (isset($rup["crtsii"]["9601"]) && trim($rup["crtsii"]["9601"]) != '') {
        $txt = '<strong>CERTIFICA:<br>CONSTITUCIÓN Y REPRESENTACIÓN LEGAL</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $txt = '<strong>INFORMACIÓN DE CONSTITUCIÓN</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        construyeCertificaTexto($pdf, $rup, '9601');
    } else {
        $txt = '<strong>CERTIFICA:<br>CONSTITUCIÓN Y REPRESENTACIÓN LEGAL</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
    }

    if (isset($rup[$grupo]["representanteslegales"])) {
        $txt = '<strong>REPRESENTACIÓN LEGAL EN COLOMBIA</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        if (count($rup[$grupo]["representanteslegales"]) > 0) {
            $i = 0;
            foreach ($rup[$grupo]["representanteslegales"] as $rep) {
                $i++;
                if ($rep["idtipoidentificacionrepleg"] != '7') {
                    $txt = '<strong>REPRESENTANTE No.' . $i . ' :</strong><br>';
                    $txt .= '<strong>NOMBRE:</strong>' . $rep["nombrerepleg"] . '<br>';
                    $txt .= '<strong>IDENTIFICACIÓN:</strong>' . retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipoidentificacion', $rep["idtipoidentificacionrepleg"]) . ' - ' . $rep["identificacionrepleg"] . '<br>';
                    $txt .= '<strong>CARGO:</strong>' . $rep["cargorepleg"] . '<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'L');
                } else {
                    $txt = '<strong>REPRESENTANTE No.' . $i . ' :</strong><br>';
                    $txt .= '<strong>NOMBRE:</strong>' . $rep["nombrerepleg"] . '<br>';
                    // $txt .= '<strong>IDENTIFICACIÓN:</strong>' . $rep["identificacionrepleg"] . '<br>';
                    $txt .= '<strong>CARGO:</strong>' . $rep["cargorepleg"] . '<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'L');
                }
            }
        }
    }



    if (isset($rup[$grupo]["crt1121"]) && trim($rup[$grupo]["crt1121"]) != '') {
        $txt1 = $rup[$grupo]["crt1121"];
        $txt1 = str_replace("||", "|", $txt1);
        $txt1 = str_replace("|", " ", $txt1);
        $txt = $txt1 . '<br>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }
}

function construyeSeccionFacultades($pdf, $rup, $grupo, $mysqli = null) {
    if (isset($rup[$grupo]["facultades"])) {
        $pdf->writeHTML('<strong>FACULTADES:</strong><br>', true, false, true, false, 'C');
        //$rup[$grupo]["facultades"] = str_replace("&nbsp;", "", $rup[$grupo]["facultades"]);
        //$rup[$grupo]["facultades"] = strip_tags($rup[$grupo]["facultades"]);
        $rup[$grupo]["facultades"] = str_replace("<p>&nbsp;</p>", "", $rup[$grupo]["facultades"]);

        $pdf->writeHTML(str_replace("|", " ", $rup[$grupo]["facultades"]) . '<br>', true, false, true, false, 'J');
        if (trim($rup["matricula"]) != '') {
            construyeTextoVerificacionDocumental($pdf, 'construyeSeccionFacultades', $mysqli);
        }
    }
}

function construyeSeccionInformacionFinanciera($pdf, $rup, $grupo, $mysqli = null) {

    // INFORMACION FINANCIERA EN FIRME
    if ($grupo == 'enfirme') {
        $finfirme = 'no';
        $iVig = 0;
        $acto = '';
        $mostrofirmeza = 'no';

        if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
            $finfirme = 'si';
        }


        if ($finfirme == 'si') {
            $txt = '<strong>CERTIFICA:<br>INFORMACION FINANCIERA</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $txt = 'QUE EN RELACIÓN A SU INFORMACIÓN FINANCIERA EL PROPONENTE REPORTÓ:<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');

            $txt = '<strong>CERTIFICA:<br>INFORMACION FINANCIERA</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $txt = 'QUE EN RELACIÓN A SU INFORMACIÓN FINANCIERA EL PROPONENTE REPORTÓ:<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $txt = '<strong>FECHA CORTE DE LA INFORMACIÓN FINANCIERA : </strong>' . \funcionesGenerales::mostrarFecha2($rup["enfirme"]["inffin1510_fechacorte"]) . '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $txt = '';
            $txt .= 'ACTIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_actcte"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'ACTIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_acttot"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'PASIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_pascte"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'PASIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_pastot"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'PATRIMONIO_______________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_patnet"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'UTILIDAD/PERDIDA OPERACIONAL_____: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_utiope"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'GASTOS DE INTERESES______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_gasint"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txtF = str_replace("_", "&nbsp;", $txt);
            $pdf->writeHTML($txtF, true, false, true, false, 'L');
        }
    }


    // CAPACIDAD FINANCIERA EN FIRME
    if ($grupo == 'enfirme') {
        $finfirme = 'no';
        $iVig = 0;
        $acto = '';
        $mostrofirmeza = 'no';
        if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
            $finfirme = 'si';
        }

        if ($finfirme == 'si') {
            $txt = '<strong>CERTIFICA:<br>CAPACIDAD FINANCIERA</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $decimalesVisibles = 2;

            //
            if (doubleval($rup["enfirme"]["inffin1510_indliq"]) != 998 && doubleval($rup["enfirme"]["inffin1510_indliq"]) != 999 && doubleval($rup["enfirme"]["inffin1510_indliq"]) != 0) {
                $indiceLiquidez1 = $rup["enfirme"]["inffin1510_actcte"] / $rup["enfirme"]["inffin1510_pascte"];
                if (abs($indiceLiquidez1) < 0.01) {
                    $indiceLiquidez = '0,00';
                } else {
                    $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_indliq"]));
                }
            } else {
                $indiceLiquidez = 'INDETERMINADO';
                if ($rup["enfirme"]["inffin1510_pascte"] != 0 && $rup["enfirme"]["inffin1510_actcte"] != 0) {
                    $indiceLiquidez1 = $rup["enfirme"]["inffin1510_actcte"] / $rup["enfirme"]["inffin1510_pascte"];
                    if (abs($indiceLiquidez1) < 0.01) {
                        $indiceLiquidez = '0,00';
                    } else {
                        $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_actcte"] / $rup["enfirme"]["inffin1510_pascte"]));
                    }
                }
                if ($rup["enfirme"]["inffin1510_pascte"] != 0 && $rup["enfirme"]["inffin1510_actcte"] == 0) {
                    $indiceLiquidez = '0,00';
                }
            }

            //
            $indiceEndeudamiento = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_nivend"]));

            //
            if (doubleval($rup["enfirme"]["inffin1510_razcob"]) != 998 && doubleval($rup["enfirme"]["inffin1510_razcob"]) != 999 && doubleval($rup["enfirme"]["inffin1510_razcob"]) != 0) {
                $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_razcob"]));
            } else {
                $razonCobertura = 'INDETERMINADO';
                if ($rup["enfirme"]["inffin1510_gasint"] != 0 && $rup["enfirme"]["inffin1510_utiope"] != 0) {
                    $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_utiope"] / $rup["enfirme"]["inffin1510_gasint"]));
                }
                if ($rup["enfirme"]["inffin1510_gasint"] != 0 && $rup["enfirme"]["inffin1510_utiope"] == 0) {
                    $razonCobertura = '0,00';
                }
            }

            $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD FINANCIERA EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($rup["enfirme"]["inffin1510_fechacorte"]) . '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');

            $txt = '';
            $txt .= 'INDICE DE LIQUIDEZ_______________: ' . str_pad($indiceLiquidez, 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'INDICE DE ENDEUDAMIENTO__________: ' . str_pad($indiceEndeudamiento, 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'RAZÓN DE COBERTURA DE INTERESES__: ' . str_pad($razonCobertura, 25, '_', STR_PAD_LEFT) . '<br>';
            $txtF = str_replace("_", "&nbsp;", $txt);
            $pdf->writeHTML($txtF, true, false, true, false, 'L');
            construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
        }
    }

    // CAPACIDAD ORGANIZACIONAL EN FIRME
    if ($grupo == 'enfirme') {
        $finfirme = 'no';
        $iVig = 0;
        $acto = '';
        $mostrofirmeza = 'no';
        if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
            $finfirme = 'si';
        }

        if ($finfirme == 'si') {
            $txt = '<strong>CERTIFICA:<br>CAPACIDAD ORGANIZACIONAL</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $decimalesVisibles = 2;

            //
            if ($rup["enfirme"]["inffin1510_renpat"] != 998 && $rup["enfirme"]["inffin1510_renpat"] != 999) {
                $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_renpat"]));
            } else {
                $rentabilidadPatrimonio = 'INDETERMINADO';
                if ($rup["enfirme"]["inffin1510_patnet"] != 0 && $rup["enfirme"]["inffin1510_utiope"] != 0) {
                    $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_utiope"] / $rup["enfirme"]["inffin1510_patnet"]));
                }
                if ($rup["enfirme"]["inffin1510_patnet"] != 0 && $rup["enfirme"]["inffin1510_utiope"] == 0) {
                    $rentabilidadPatrimonio = "0,00";
                }
            }

            //
            if ($rup["enfirme"]["inffin1510_renact"] != 998 && $rup["enfirme"]["inffin1510_renact"] != 999) {
                $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_renact"]));
            } else {
                $rentabilidadActivo = 'INDETERMINADO';
                if ($rup["enfirme"]["inffin1510_acttot"] != 0 && $rup["enfirme"]["inffin1510_utiope"] != 0) {
                    $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_utiope"] / $rup["enfirme"]["inffin1510_acttot"]));
                }
                if ($rup["enfirme"]["inffin1510_acttot"] != 0 && $rup["enfirme"]["inffin1510_utiope"] == 0) {
                    $rentabilidadActivo = "0,00";
                }
            }

            //
            $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD ORGANIZACIONAL EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($rup["enfirme"]["inffin1510_fechacorte"]) . '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $txt = '';
            $txt .= 'RENTABILIDAD DEL PATRIMONIO______: ' . str_pad($rentabilidadPatrimonio, 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'RENTABILIDAD DEL ACTIVO__________: ' . str_pad($rentabilidadActivo, 25, '_', STR_PAD_LEFT) . '<br>';
            $txtF = str_replace("_", "&nbsp;", $txt);
            $pdf->writeHTML($txtF, true, false, true, false, 'L');
            construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
        }
    }

    // INFORMACION FINANCIERA SIN FIRMEZA    
    $notadecreto399 = 'no';
    $pubruesdecreto399 = '';
    $fecinsdecreto399 = '';
    $numregdecreto399 = '';

    //
    if (substr($grupo, 0, 7) == 'nofirme') {
        if ($rup[$grupo]["inffin1510_fechacorte"] != '') {
            $txt = '<strong>CERTIFICA:<br>INFORMACION FINANCIERA</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $txt = 'QUE EN RELACIÓN A SU INFORMACIÓN FINANCIERA EL PROPONENTE REPORTÓ:<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');

            $txt = '<strong>FECHA CORTE DE LA INFORMACIÓN FINANCIERA : </strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["inffin1510_fechacorte"]) . '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $txt = '';
            $txt .= 'ACTIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_actcte"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'ACTIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_acttot"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'PASIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_pascte"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'PASIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_pastot"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'PATRIMONIO_______________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_patnet"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'UTILIDAD/PERDIDA OPERACIONAL_____: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_utiope"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'GASTOS DE INTERESES______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_gasint"]), 25, '_', STR_PAD_LEFT) . '<br>';
            $txtF = str_replace("_", "&nbsp;", $txt);
            $pdf->writeHTML($txtF, true, false, true, false, 'L');
        }
    }

    // CAPACIDAD FINANCIERA SIN FIRMEZA
    if (substr($grupo, 0, 7) == 'nofirme') {
        if ($rup[$grupo]["inffin1510_fechacorte"] != '') {
            $decimalesVisibles = 2;

            //
            if (doubleval($rup[$grupo]["inffin1510_indliq"]) != 998 && doubleval($rup[$grupo]["inffin1510_indliq"]) != 999 && doubleval($rup[$grupo]["inffin1510_indliq"]) != 0) {
                $indiceLiquidez1 = $rup[$grupo]["inffin1510_actcte"] / $rup[$grupo]["inffin1510_pascte"];
                if (abs($indiceLiquidez1) < 0.01) {
                    $indiceLiquidez = '0,00';
                } else {
                    $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_indliq"]));
                }
            } else {
                $indiceLiquidez = 'INDETERMINADO';
                if ($rup[$grupo]["inffin1510_pascte"] != 0 && $rup[$grupo]["inffin1510_actcte"] != 0) {
                    $indiceLiquidez1 = $rup[$grupo]["inffin1510_actcte"] / $rup[$grupo]["inffin1510_pascte"];
                    if (abs($indiceLiquidez1) < 0.01) {
                        $indiceLiquidez = '0,00';
                    } else {
                        $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_actcte"] / $rup[$grupo]["inffin1510_pascte"]));
                    }
                }
                if ($rup[$grupo]["inffin1510_pascte"] != 0 && $rup[$grupo]["inffin1510_actcte"] == 0) {
                    $indiceLiquidez = '0,00';
                }
            }

            //
            $indiceEndeudamiento = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_nivend"]));

            //
            if (doubleval($rup[$grupo]["inffin1510_razcob"]) != 998 && doubleval($rup[$grupo]["inffin1510_razcob"]) != 999 && doubleval($rup[$grupo]["inffin1510_razcob"]) != 0) {
                $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_razcob"]));
            } else {
                $razonCobertura = 'INDETERMINADO';
                if ($rup[$grupo]["inffin1510_gasint"] != 0 && $rup[$grupo]["inffin1510_utiope"] != 0) {
                    $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_utiope"] / $rup[$grupo]["inffin1510_gasint"]));
                }
                if ($rup[$grupo]["inffin1510_gasint"] != 0 && $rup[$grupo]["inffin1510_utiope"] == 0) {
                    $razonCobertura = '0,00';
                }
            }

            //
            $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD FINANCIERA EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["inffin1510_fechacorte"]) . '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $txt = '';
            $txt .= 'INDICE DE LIQUIDEZ_______________: ' . str_pad($indiceLiquidez, 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'INDICE DE ENDEUDAMIENTO__________: ' . str_pad($indiceEndeudamiento, 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'RAZÓN DE COBERTURA DE INTERESES__: ' . str_pad($razonCobertura, 25, '_', STR_PAD_LEFT) . '<br>';
            $txtF = str_replace("_", "&nbsp;", $txt);
            $pdf->writeHTML($txtF, true, false, true, false, 'L');
            construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
        }
    }

    // CAPACIDAD ORGANIZACIONAL SIN FIRMEZA
    if (substr($grupo, 0, 7) == 'nofirme') {
        if ($rup[$grupo]["inffin1510_fechacorte"] != '') {
            $decimalesVisibles = 2;

            //
            if ($rup[$grupo]["inffin1510_renpat"] != 998 && $rup[$grupo]["inffin1510_renpat"] != 999) {
                $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_renpat"]));
            } else {
                $rentabilidadPatrimonio = 'INDETERMINADO';
                if ($rup[$grupo]["inffin1510_patnet"] != 0 && $rup[$grupo]["inffin1510_utiope"] != 0) {
                    $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_utiope"] / $rup[$grupo]["inffin1510_patnet"]));
                }
                if ($rup[$grupo]["inffin1510_patnet"] != 0 && $rup[$grupo]["inffin1510_utiope"] == 0) {
                    $rentabilidadPatrimonio = "0,00";
                }
            }

            //
            if ($rup[$grupo]["inffin1510_renact"] != 998 && $rup[$grupo]["inffin1510_renact"] != 999) {
                $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_renact"]));
            } else {
                $rentabilidadActivo = 'INDETERMINADO';
                if ($rup[$grupo]["inffin1510_acttot"] != 0 && $rup[$grupo]["inffin1510_utiope"] != 0) {
                    $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_utiope"] / $rup[$grupo]["inffin1510_acttot"]));
                }
                if ($rup[$grupo]["inffin1510_acttot"] != 0 && $rup[$grupo]["inffin1510_utiope"] == 0) {
                    $rentabilidadActivo = "0,00";
                }
            }

            $txt = '<strong>CERTIFICA:<br>CAPACIDAD ORGANIZACIONAL</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            
            $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD ORGANIZACIONAL EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["inffin1510_fechacorte"]) . '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $txt = '';
            $txt .= 'RENTABILIDAD DEL PATRIMONIO______: ' . str_pad($rentabilidadPatrimonio, 25, '_', STR_PAD_LEFT) . '<br>';
            $txt .= 'RENTABILIDAD DEL ACTIVO__________: ' . str_pad($rentabilidadActivo, 25, '_', STR_PAD_LEFT) . '<br>';
            $txtF = str_replace("_", "&nbsp;", $txt);
            $pdf->writeHTML($txtF, true, false, true, false, 'L');
            construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
        }
    }
}

function construyeSeccionInformacionFinancieraCon399($pdf, $rup, $grupo, $mysqli = null) {

    // INFORMACION FINANCIERA EN FIRME
    if ($grupo == 'enfirme') {
        $finfirme = 'no';
        $iVig = 0;
        $acto = '';
        $mostrofirmeza = 'no';
        if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
            foreach ($rup["financierahistorica"] as $f) {
                if (date("Y") == '2024' && date("Ymd") <= '20240405') {
                    if ($f["fechacorte"] > '20200100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2024') {
                    if ($f["fechacorte"] > '20210100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }

                if (date("Y") == '2023' && date("Ymd") <= '20230411') {
                    if ($f["fechacorte"] > '20190100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2023') {
                    if ($f["fechacorte"] > '20200100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }

                if (date("Y") == '2022' && date("Ymd") <= '20220407') {
                    if ($f["fechacorte"] > '20180100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2022') {
                    if ($f["fechacorte"] > '20190100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
            }
        } else {
            if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
                $finfirme = 'si';
            }
        }

        if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
            $finfirme = 'si';
        }


        if ($finfirme == 'si') {
            $txt = '<strong>CERTIFICA:<br>INFORMACION FINANCIERA</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $txt = 'QUE EN RELACIÓN A SU INFORMACIÓN FINANCIERA EL PROPONENTE REPORTÓ:<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');

            if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
                $iVig = 0;
                $acto = '';
                $mostrarprimero = 'no';
                foreach ($rup["financierahistorica"] as $f) {
                    $mostrar = 'no';
                    if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
                        if (date("Y") == '2024' && date("Ymd") <= '20240405') {
                            if ($f["fechacorte"] > '20200100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2024') {
                            if ($f["fechacorte"] > '20210100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2023' && date("Ymd") <= '20230411') {
                            if ($f["fechacorte"] > '20190100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2023') {
                            if ($f["fechacorte"] > '20200100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }

                        if (date("Y") == '2022' && date("Ymd") <= '20220407') {
                            if ($f["fechacorte"] > '20180100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2022') {
                            if ($f["fechacorte"] > '20190100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                    } else {
                        $iVig++;
                        if ($iVig == 1) {
                            $acto = $f["acto"];
                            if ($f["firmeza"] == 'S') {
                                $mostrar = 'si';
                                $mostrarprimero = 'si';
                            }
                        } else {
                            if ($iVig == 2 && $mostrarprimero == 'no' && $f["firmeza"] == 'S' && $acto != '01') {
                                $mostrar = 'si';
                            }
                        }
                    }

                    if ($mostrar == 'si') {
                        $txt = '<strong>FECHA CORTE DE LA INFORMACIÓN FINANCIERA : </strong>' . \funcionesGenerales::mostrarFecha2($f["fechacorte"]) . '<br>';
                        $pdf->writeHTML($txt, true, false, true, false, 'L');
                        $txt = '';
                        $txt .= 'ACTIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["actcte"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'ACTIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["acttot"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'PASIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["pascte"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'PASIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["pastot"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'PATRIMONIO_______________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["patnet"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'UTILIDAD/PERDIDA OPERACIONAL_____: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["utiope"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'GASTOS DE INTERESES______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["gasint"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txtF = str_replace("_", "&nbsp;", $txt);
                        $pdf->writeHTML($txtF, true, false, true, false, 'L');
                    }
                }
            } else {
                $txt = '<strong>CERTIFICA:<br>INFORMACION FINANCIERA</strong><br>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $txt = 'QUE EN RELACIÓN A SU INFORMACIÓN FINANCIERA EL PROPONENTE REPORTÓ:<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $txt = '<strong>FECHA CORTE DE LA INFORMACIÓN FINANCIERA : </strong>' . \funcionesGenerales::mostrarFecha2($rup["enfirme"]["inffin1510_fechacorte"]) . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'L');
                $txt = '';
                $txt .= 'ACTIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_actcte"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'ACTIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_acttot"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'PASIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_pascte"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'PASIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_pastot"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'PATRIMONIO_______________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_patnet"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'UTILIDAD/PERDIDA OPERACIONAL_____: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_utiope"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'GASTOS DE INTERESES______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup["enfirme"]["inffin1510_gasint"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txtF = str_replace("_", "&nbsp;", $txt);
                $pdf->writeHTML($txtF, true, false, true, false, 'L');
            }
        }
    }


    // CAPACIDAD FINANCIERA EN FIRME
    if ($grupo == 'enfirme') {
        $finfirme = 'no';
        $iVig = 0;
        $acto = '';
        $mostrofirmeza = 'no';

        if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
            foreach ($rup["financierahistorica"] as $f) {
                if (date("Y") == '2024' && date("Ymd") <= '20240405') {
                    if ($f["fechacorte"] > '20200100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2024') {
                    if ($f["fechacorte"] > '20210100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }

                if (date("Y") == '2023' && date("Ymd") <= '20230411') {
                    if ($f["fechacorte"] > '20190100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2023') {
                    if ($f["fechacorte"] > '20200100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }

                if (date("Y") == '2022' && date("Ymd") <= '20220407') {
                    if ($f["fechacorte"] > '20180100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2022') {
                    if ($f["fechacorte"] > '20190100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
            }
        } else {
            if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
                $finfirme = 'si';
            }
        }

        if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
            $finfirme = 'si';
        }

        if ($finfirme == 'si') {
            $txt = '<strong>CERTIFICA:<br>CAPACIDAD FINANCIERA</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');

            if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
                $iVig = 0;
                $acto = '';
                $mostrarprimero = 'no';
                foreach ($rup["financierahistorica"] as $f) {
                    $mostrar = 'no';
                    if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
                        if (date("Y") == '2024' && date("Ymd") <= '20240405') {
                            if ($f["fechacorte"] > '20200100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2024') {
                            if ($f["fechacorte"] > '20210100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2023' && date("Ymd") <= '20230411') {
                            if ($f["fechacorte"] > '20190100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2023') {
                            if ($f["fechacorte"] > '20200100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }

                        if (date("Y") == '2022' && date("Ymd") <= '20220407') {
                            if ($f["fechacorte"] > '20180100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2022') {
                            if ($f["fechacorte"] > '20190100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                    } else {
                        $iVig++;
                        if ($iVig == 1) {
                            $acto = $f["acto"];
                            if ($f["firmeza"] == 'S') {
                                $mostrar = 'si';
                                $mostrarprimero = 'si';
                            }
                        } else {
                            if ($iVig == 2 && $mostrarprimero == 'no' && $f["firmeza"] == 'S' && $acto != '01') {
                                $mostrar = 'si';
                            }
                        }
                    }

                    if ($mostrar == 'si') {
                        $decimalesVisibles = 2;

                        //
                        if (doubleval($f["indliq"]) != 998 && doubleval($f["indliq"]) != 999 && doubleval($f["indliq"]) != 0) {
                            $indiceLiquidez1 = $f["actcte"] / $f["pascte"];
                            if (abs($indiceLiquidez1) < 0.01) {
                                $indiceLiquidez = '0,00';
                            } else {
                                $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["indliq"]));
                            }
                        } else {
                            $indiceLiquidez = 'INDETERMINADO';
                            if ($f["pascte"] != 0 && $f["actcte"] != 0) {
                                $indiceLiquidez1 = $f["actcte"] / $f["pascte"];
                                if (abs($indiceLiquidez1) < 0.01) {
                                    $indiceLiquidez = '0,00';
                                } else {
                                    $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["actcte"] / $f["pascte"]));
                                }
                            }
                            if ($f["pascte"] != 0 && $f["actcte"] == 0) {
                                $indiceLiquidez = "0,00";
                            }
                        }

                        //
                        $indiceEndeudamiento = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["nivend"]));

                        //
                        if (doubleval($f["razcob"]) != 998 && doubleval($f["razcob"]) != 999 && doubleval($f["razcob"]) != 0) {
                            $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["razcob"]));
                        } else {
                            $razonCobertura = 'INDETERMINADO';
                            if ($f["gasint"] != 0 && $f["utiope"] != 0) {
                                $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["utiope"] / $f["gasint"]));
                            }
                            if ($f["gasint"] != 0 && $f["utiope"] == 0) {
                                $razonCobertura = "0,00";
                            }
                        }

                        $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD FINANCIERA EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($f["fechacorte"]) . '<br>';
                        $pdf->writeHTML($txt, true, false, true, false, 'J');

                        $txt = '';
                        $txt .= 'INDICE DE LIQUIDEZ_______________: ' . str_pad($indiceLiquidez, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'INDICE DE ENDEUDAMIENTO__________: ' . str_pad($indiceEndeudamiento, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'RAZÓN DE COBERTURA DE INTERESES__: ' . str_pad($razonCobertura, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txtF = str_replace("_", "&nbsp;", $txt);
                        $pdf->writeHTML($txtF, true, false, true, false, 'L');
                        construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
                    }
                }
            } else {
                $decimalesVisibles = 2;

                //
                if (doubleval($rup["enfirme"]["inffin1510_indliq"]) != 998 && doubleval($rup["enfirme"]["inffin1510_indliq"]) != 999 && doubleval($rup["enfirme"]["inffin1510_indliq"]) != 0) {
                    $indiceLiquidez1 = $rup["enfirme"]["inffin1510_actcte"] / $rup["enfirme"]["inffin1510_pascte"];
                    if (abs($indiceLiquidez1) < 0.01) {
                        $indiceLiquidez = '0,00';
                    } else {
                        $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_indliq"]));
                    }
                } else {
                    $indiceLiquidez = 'INDETERMINADO';
                    if ($rup["enfirme"]["inffin1510_pascte"] != 0 && $rup["enfirme"]["inffin1510_actcte"] != 0) {
                        $indiceLiquidez1 = $rup["enfirme"]["inffin1510_actcte"] / $rup["enfirme"]["inffin1510_pascte"];
                        if (abs($indiceLiquidez1) < 0.01) {
                            $indiceLiquidez = '0,00';
                        } else {
                            $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_actcte"] / $rup["enfirme"]["inffin1510_pascte"]));
                        }
                    }
                    if ($rup["enfirme"]["inffin1510_pascte"] != 0 && $rup["enfirme"]["inffin1510_actcte"] == 0) {
                        $indiceLiquidez = '0,00';
                    }
                }

                //
                $indiceEndeudamiento = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_nivend"]));

                //
                if (doubleval($rup["enfirme"]["inffin1510_razcob"]) != 998 && doubleval($rup["enfirme"]["inffin1510_razcob"]) != 999 && doubleval($rup["enfirme"]["inffin1510_razcob"]) != 0) {
                    $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_razcob"]));
                } else {
                    $razonCobertura = 'INDETERMINADO';
                    if ($rup["enfirme"]["inffin1510_gasint"] != 0 && $rup["enfirme"]["inffin1510_utiope"] != 0) {
                        $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_utiope"] / $rup["enfirme"]["inffin1510_gasint"]));
                    }
                    if ($rup["enfirme"]["inffin1510_gasint"] != 0 && $rup["enfirme"]["inffin1510_utiope"] == 0) {
                        $razonCobertura = '0,00';
                    }
                }

                $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD FINANCIERA EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($rup["enfirme"]["inffin1510_fechacorte"]) . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');

                $txt = '';
                $txt .= 'INDICE DE LIQUIDEZ_______________: ' . str_pad($indiceLiquidez, 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'INDICE DE ENDEUDAMIENTO__________: ' . str_pad($indiceEndeudamiento, 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'RAZÓN DE COBERTURA DE INTERESES__: ' . str_pad($razonCobertura, 25, '_', STR_PAD_LEFT) . '<br>';
                $txtF = str_replace("_", "&nbsp;", $txt);
                $pdf->writeHTML($txtF, true, false, true, false, 'L');
                construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
            }
        }
    }

    // CAPACIDAD ORGANIZACIONAL EN FIRME
    if ($grupo == 'enfirme') {
        $finfirme = 'no';
        $iVig = 0;
        $acto = '';
        $mostrofirmeza = 'no';

        if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
            foreach ($rup["financierahistorica"] as $f) {
                if (date("Y") == '2024' && date("Ymd") <= '20240405') {
                    if ($f["fechacorte"] > '20200100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2024') {
                    if ($f["fechacorte"] > '20240100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2023' && date("Ymd") <= '20230411') {
                    if ($f["fechacorte"] > '20190100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2023') {
                    if ($f["fechacorte"] > '20200100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }

                if (date("Y") == '2022' && date("Ymd") <= '20220407') {
                    if ($f["fechacorte"] > '20180100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
                if (date("Y") == '2022') {
                    if ($f["fechacorte"] > '20190100') {
                        if ($f["firmeza"] == 'S') {
                            $finfirme = 'si';
                        }
                    }
                }
            }
        } else {
            if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
                $finfirme = 'si';
            }
        }

        if ($rup["enfirme"]["inffin1510_fechacorte"] != '') {
            $finfirme = 'si';
        }

        if ($finfirme == 'si') {
            $txt = '<strong>CERTIFICA:<br>CAPACIDAD ORGANIZACIONAL</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');

            if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
                $iVig = 0;
                $acto = '';
                $mostrarprimero = 'no';
                foreach ($rup["financierahistorica"] as $f) {
                    $mostrar = 'no';
                    if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
                        if (date("Y") == '2024' && date("Ymd") <= '20240405') {
                            if ($f["fechacorte"] > '20200100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2024') {
                            if ($f["fechacorte"] > '20210100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2023' && date("Ymd") <= '20230411') {
                            if ($f["fechacorte"] > '20190100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2023') {
                            if ($f["fechacorte"] > '20200100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }

                        if (date("Y") == '2022' && date("Ymd") <= '20220407') {
                            if ($f["fechacorte"] > '20180100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (date("Y") == '2022') {
                            if ($f["fechacorte"] > '20190100') {
                                if ($f["firmeza"] == 'S') {
                                    $mostrar = 'si';
                                }
                            }
                        }
                    } else {
                        $iVig++;
                        if ($iVig == 1) {
                            $acto = $f["acto"];
                            if ($f["firmeza"] == 'S') {
                                $mostrar = 'si';
                                $mostrarprimero = 'si';
                            }
                        } else {
                            if ($iVig == 2 && $mostrarprimero == 'no' && $f["firmeza"] == 'S' && $acto != '01') {
                                $mostrar = 'si';
                            }
                        }
                    }

                    if ($mostrar == 'si') {
                        $decimalesVisibles = 2;

                        //
                        if ($f["renpat"] != 998 && $f["renpat"] != 999 && $f["renpat"] != 0) {
                            $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["renpat"]));
                        } else {
                            $rentabilidadPatrimonio = 'INDEFINIDO';
                            if ($f["patnet"] != 0) {
                                $rentabilidadPatrimonio = $f["utiope"] / $f["patnet"];
                                if (abs($rentabilidadPatrimonio) < 0.01) {
                                    $rentabilidadPatrimonio = '0,00';
                                } else {
                                    $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rentabilidadPatrimonio));
                                }
                                if ($f["patnet"] != 0 && $f["utiope"] == 0) {
                                    $rentabilidadPatrimonio = '0,00';
                                }
                            }
                        }

                        //
                        if ($f["renact"] != 998 && $f["renact"] != 999 && $f["renact"] != 0) {
                            $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["renact"]));
                        } else {
                            $rentabilidadActivo = 'INDEFINIDO';
                            if ($f["acttot"] != 0) {
                                $rentabilidadActivo = $f["utiope"] / $f["acttot"];
                                if (abs($rentabilidadActivo) < 0.01) {
                                    $rentabilidadActivo = '0,00';
                                } else {
                                    $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rentabilidadActivo));
                                }
                            }
                            if ($f["acttot"] != 0 && $f["utiope"] == 0) {
                                $rentabilidadActivo = '0,00';
                            }
                        }


                        //
                        $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD ORGANIZACIONAL EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($f["fechacorte"]) . '<br>';
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $txt = '';
                        $txt .= 'RENTABILIDAD DEL PATRIMONIO______: ' . str_pad($rentabilidadPatrimonio, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'RENTABILIDAD DEL ACTIVO__________: ' . str_pad($rentabilidadActivo, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txtF = str_replace("_", "&nbsp;", $txt);
                        $pdf->writeHTML($txtF, true, false, true, false, 'L');
                        construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
                    }
                }
            } else {
                $decimalesVisibles = 2;

                //
                if ($rup["enfirme"]["inffin1510_renpat"] != 998 && $rup["enfirme"]["inffin1510_renpat"] != 999) {
                    $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_renpat"]));
                } else {
                    $rentabilidadPatrimonio = 'INDETERMINADO';
                    if ($rup["enfirme"]["inffin1510_patnet"] != 0 && $rup["enfirme"]["inffin1510_utiope"] != 0) {
                        $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_utiope"] / $rup["enfirme"]["inffin1510_patnet"]));
                    }
                    if ($rup["enfirme"]["inffin1510_patnet"] != 0 && $rup["enfirme"]["inffin1510_utiope"] == 0) {
                        $rentabilidadPatrimonio = "0,00";
                    }
                }

                //
                if ($rup["enfirme"]["inffin1510_renact"] != 998 && $rup["enfirme"]["inffin1510_renact"] != 999) {
                    $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_renact"]));
                } else {
                    $rentabilidadActivo = 'INDETERMINADO';
                    if ($rup["enfirme"]["inffin1510_acttot"] != 0 && $rup["enfirme"]["inffin1510_utiope"] != 0) {
                        $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup["enfirme"]["inffin1510_utiope"] / $rup["enfirme"]["inffin1510_acttot"]));
                    }
                    if ($rup["enfirme"]["inffin1510_acttot"] != 0 && $rup["enfirme"]["inffin1510_utiope"] == 0) {
                        $rentabilidadActivo = "0,00";
                    }
                }

                //
                $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD ORGANIZACIONAL EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($rup["enfirme"]["inffin1510_fechacorte"]) . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $txt = '';
                $txt .= 'RENTABILIDAD DEL PATRIMONIO______: ' . str_pad($rentabilidadPatrimonio, 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'RENTABILIDAD DEL ACTIVO__________: ' . str_pad($rentabilidadActivo, 25, '_', STR_PAD_LEFT) . '<br>';
                $txtF = str_replace("_", "&nbsp;", $txt);
                $pdf->writeHTML($txtF, true, false, true, false, 'L');
                construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
            }
        }
    }

    // INFORMACION FINANCIERA SIN FIRMEZA    
    $notadecreto399 = 'no';
    $pubruesdecreto399 = '';
    $fecinsdecreto399 = '';
    $numregdecreto399 = '';

    //
    if (substr($grupo, 0, 7) == 'nofirme') {
        if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
            $finnofirme = 'no';
            $iVig = 0;
            $acto = '';
            $mostrofirmeza = 'no';
            foreach ($rup["financierahistorica"] as $f) {
                if ($f["libro"] == $rup[$grupo]["libro"] && $f["registro"] == $rup[$grupo]["registro"]) {
                    $finnofirme = 'si';
                }
            }
            if ($finnofirme == 'si') {
                $txt = '<strong>CERTIFICA:<br>INFORMACION FINANCIERA</strong><br><br>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $txt = 'QUE EN RELACIÓN A SU INFORMACIÓN FINANCIERA EL PROPONENTE REPORTÓ:<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');

                foreach ($rup["financierahistorica"] as $f) {
                    $mostrar = 'no';
                    if ($f["libro"] == $rup[$grupo]["libro"] && $f["registro"] == $rup[$grupo]["registro"]) {
                        $mostrar = 'si';
                    }

                    if ($mostrar == 'si') {
                        if ($rup[$grupo]["acto"] == '36') {
                            $notadecreto399 = 'si';
                            $pubruesdecreto399 = $rup[$grupo]["fecpublicacionrue"];
                            $fecinsdecreto399 = $rup[$grupo]["fecharegistro"];
                            $numregdecreto399 = $rup[$grupo]["registro"];
                        }
                        $txt = '<strong>FECHA CORTE DE LA INFORMACIÓN FINANCIERA : </strong>' . \funcionesGenerales::mostrarFecha2($f["fechacorte"]) . '<br>';
                        $pdf->writeHTML($txt, true, false, true, false, 'L');
                        $txt = '';
                        $txt .= 'ACTIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["actcte"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'ACTIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["acttot"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'PASIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["pascte"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'PASIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["pastot"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'PATRIMONIO_______________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["patnet"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'UTILIDAD/PERDIDA OPERACIONAL_____: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["utiope"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'GASTOS DE INTERESES______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($f["gasint"]), 25, '_', STR_PAD_LEFT) . '<br>';
                        $txtF = str_replace("_", "&nbsp;", $txt);
                        $pdf->writeHTML($txtF, true, false, true, false, 'L');
                    }
                }
            }
        } else {
            if ($rup[$grupo]["inffin1510_fechacorte"] != '') {
                $txt = '<strong>CERTIFICA:<br>INFORMACION FINANCIERA</strong><br>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $txt = 'QUE EN RELACIÓN A SU INFORMACIÓN FINANCIERA EL PROPONENTE REPORTÓ:<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');

                $txt = '<strong>FECHA CORTE DE LA INFORMACIÓN FINANCIERA : </strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["inffin1510_fechacorte"]) . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'L');
                $txt = '';
                $txt .= 'ACTIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_actcte"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'ACTIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_acttot"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'PASIVO CORRIENTE_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_pascte"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'PASIVO TOTAL_____________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_pastot"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'PATRIMONIO_______________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_patnet"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'UTILIDAD/PERDIDA OPERACIONAL_____: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_utiope"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'GASTOS DE INTERESES______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_gasint"]), 25, '_', STR_PAD_LEFT) . '<br>';
                $txtF = str_replace("_", "&nbsp;", $txt);
                $pdf->writeHTML($txtF, true, false, true, false, 'L');
            }
        }
    }

    // CAPACIDAD FINANCIERA SIN FIRMEZA
    if (substr($grupo, 0, 7) == 'nofirme') {
        if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
            $finnofirme = 'no';
            $iVig = 0;
            $acto = '';
            $mostrofirmeza = 'no';
            foreach ($rup["financierahistorica"] as $f) {
                if ($f["libro"] == $rup[$grupo]["libro"] && $f["registro"] == $rup[$grupo]["registro"]) {
                    $finnofirme = 'si';
                }
            }
            if ($finnofirme == 'si') {
                $txt = '<strong>CERTIFICA:<br>CAPACIDAD FINANCIERA</strong><br>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                foreach ($rup["financierahistorica"] as $f) {
                    $mostrar = 'no';
                    if ($f["libro"] == $rup[$grupo]["libro"] && $f["registro"] == $rup[$grupo]["registro"]) {
                        $mostrar = 'si';
                    }
                    if ($mostrar == 'si') {
                        $decimalesVisibles = 2;
                        if (doubleval($f["indliq"]) != 998 && doubleval($f["indliq"]) != 999 && doubleval($f["indliq"]) != 0) {
                            $indiceLiquidez1 = $f["actcte"] / $f["pascte"];
                            if (abs($indiceLiquidez1) < 0.01) {
                                $indiceLiquidez = '0,00';
                            } else {
                                $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["indliq"]));
                            }
                        } else {
                            $indiceLiquidez = 'INDETERMINADO';
                            if ($f["pascte"] != 0 && $f["actcte"] != 0) {
                                $indiceLiquidez1 = $f["actcte"] / $f["pascte"];
                                if (abs($indiceLiquidez1) < 0.01) {
                                    $indiceLiquidez = '0,00';
                                } else {
                                    $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["actcte"] / $f["pascte"]));
                                }
                            }
                            if ($f["pascte"] != 0 && $f["actcte"] == 0) {
                                $indiceLiquidez = '0,00';
                            }
                        }

                        //
                        $indiceEndeudamiento = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["nivend"]));

                        //
                        if (doubleval($f["razcob"]) != 998 && doubleval($f["razcob"]) != 999 && doubleval($f["razcob"]) != 0) {
                            $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["razcob"]));
                        } else {
                            $razonCobertura = 'INDETERMINADO';
                            if ($f["gasint"] != 0 && $f["utiope"] != 0) {
                                $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["utiope"] / $f["gasint"]));
                            }
                            if ($f["gasint"] != 0 && $f["utiope"] == 0) {
                                $razonCobertura = '0,00';
                            }
                        }

                        $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD FINANCIERA EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($f["fechacorte"]) . '<br>';
                        $pdf->writeHTML($txt, true, false, true, false, 'J');

                        $txt = '';
                        $txt .= 'INDICE DE LIQUIDEZ_______________: ' . str_pad($indiceLiquidez, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'INDICE DE ENDEUDAMIENTO__________: ' . str_pad($indiceEndeudamiento, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'RAZÓN DE COBERTURA DE INTERESES__: ' . str_pad($razonCobertura, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txtF = str_replace("_", "&nbsp;", $txt);
                        $pdf->writeHTML($txtF, true, false, true, false, 'L');
                        construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
                    }
                }
            }
        } else {
            if ($rup[$grupo]["inffin1510_fechacorte"] != '') {
                $decimalesVisibles = 2;

                //
                if (doubleval($rup[$grupo]["inffin1510_indliq"]) != 998 && doubleval($rup[$grupo]["inffin1510_indliq"]) != 999 && doubleval($rup[$grupo]["inffin1510_indliq"]) != 0) {
                    $indiceLiquidez1 = $rup[$grupo]["inffin1510_actcte"] / $rup[$grupo]["inffin1510_pascte"];
                    if (abs($indiceLiquidez1) < 0.01) {
                        $indiceLiquidez = '0,00';
                    } else {
                        $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_indliq"]));
                    }
                } else {
                    $indiceLiquidez = 'INDETERMINADO';
                    if ($rup[$grupo]["inffin1510_pascte"] != 0 && $rup[$grupo]["inffin1510_actcte"] != 0) {
                        $indiceLiquidez1 = $rup[$grupo]["inffin1510_actcte"] / $rup[$grupo]["inffin1510_pascte"];
                        if (abs($indiceLiquidez1) < 0.01) {
                            $indiceLiquidez = '0,00';
                        } else {
                            $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_actcte"] / $rup[$grupo]["inffin1510_pascte"]));
                        }
                    }
                    if ($rup[$grupo]["inffin1510_pascte"] != 0 && $rup[$grupo]["inffin1510_actcte"] == 0) {
                        $indiceLiquidez = '0,00';
                    }
                }

                //
                $indiceEndeudamiento = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_nivend"]));

                //
                if (doubleval($rup[$grupo]["inffin1510_razcob"]) != 998 && doubleval($rup[$grupo]["inffin1510_razcob"]) != 999 && doubleval($rup[$grupo]["inffin1510_razcob"]) != 0) {
                    $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_razcob"]));
                } else {
                    $razonCobertura = 'INDETERMINADO';
                    if ($rup[$grupo]["inffin1510_gasint"] != 0 && $rup[$grupo]["inffin1510_utiope"] != 0) {
                        $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_utiope"] / $rup[$grupo]["inffin1510_gasint"]));
                    }
                    if ($rup[$grupo]["inffin1510_gasint"] != 0 && $rup[$grupo]["inffin1510_utiope"] == 0) {
                        $razonCobertura = '0,00';
                    }
                }

                //
                $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD FINANCIERA EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["inffin1510_fechacorte"]) . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $txt = '';
                $txt .= 'INDICE DE LIQUIDEZ_______________: ' . str_pad($indiceLiquidez, 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'INDICE DE ENDEUDAMIENTO__________: ' . str_pad($indiceEndeudamiento, 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'RAZÓN DE COBERTURA DE INTERESES__: ' . str_pad($razonCobertura, 25, '_', STR_PAD_LEFT) . '<br>';
                $txtF = str_replace("_", "&nbsp;", $txt);
                $pdf->writeHTML($txtF, true, false, true, false, 'L');
                construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
            }
        }
    }

    // CAPACIDAD ORGANIZACIONAL SIN FIRMEZA
    if (substr($grupo, 0, 7) == 'nofirme') {
        if (defined('ACTIVADO_DECRETO_399_RUP_CERTIFICADO') && ACTIVADO_DECRETO_399_RUP_CERTIFICADO == 'S') {
            $finnofirme = 'no';
            $iVig = 0;
            $acto = '';
            $mostrofirmeza = 'no';
            foreach ($rup["financierahistorica"] as $f) {
                if ($f["libro"] == $rup[$grupo]["libro"] && $f["registro"] == $rup[$grupo]["registro"]) {
                    $finnofirme = 'si';
                }
            }
            if ($finnofirme == 'si') {
                $txt = '<strong>CERTIFICA:<br>CAPACIDAD ORGANIZACIONAL</strong><br>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                foreach ($rup["financierahistorica"] as $f) {
                    $mostrar = 'no';
                    if ($f["libro"] == $rup[$grupo]["libro"] && $f["registro"] == $rup[$grupo]["registro"]) {
                        $mostrar = 'si';
                    }
                    if ($mostrar == 'si') {
                        $decimalesVisibles = 2;

                        //
                        if ($f["renpat"] != 998 && $f["renpat"] != 999 && $f["renpat"] != 0) {
                            $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["renpat"]));
                        } else {
                            $rentabilidadPatrimonio = 'INDETERMINADO';
                            if ($f["patnet"] != 0) {
                                $rentabilidadPatrimonio = $f["utiope"] / $f["patnet"];
                                if (abs($rentabilidadPatrimonio) < 0.01) {
                                    $rentabilidadPatrimonio = '0,00';
                                } else {
                                    $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rentabilidadPatrimonio));
                                }
                            }
                            if ($f["patnet"] != 0 && $f["utiope"] == 0) {
                                $rentabilidadPatrimonio = '0,00';
                            }
                        }

                        //
                        if ($f["renact"] != 998 && $f["renact"] != 999 && $f["renact"] != 0) {
                            $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $f["renact"]));
                        } else {
                            $rentabilidadActivo = 'INDETERMINADO';
                            if ($f["acttot"] != 0) {
                                $rentabilidadActivo = $f["utiope"] / $f["acttot"];
                                if (abs($rentabilidadActivo) < 0.01) {
                                    $rentabilidadActivo = '0,00';
                                } else {
                                    $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rentabilidadActivo));
                                }
                            }
                            if ($f["acttot"] != 0 && $f["utiope"] == 0) {
                                $rentabilidadActivo = '0,00';
                            }
                        }

                        //
                        $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD ORGANIZACIONAL EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($f["fechacorte"]) . '<br>';
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $txt = '';
                        $txt .= 'RENTABILIDAD DEL PATRIMONIO______: ' . str_pad($rentabilidadPatrimonio, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txt .= 'RENTABILIDAD DEL ACTIVO__________: ' . str_pad($rentabilidadActivo, 25, '_', STR_PAD_LEFT) . '<br>';
                        $txtF = str_replace("_", "&nbsp;", $txt);
                        $pdf->writeHTML($txtF, true, false, true, false, 'L');
                        construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
                    }
                }
            }
        } else {
            if ($rup[$grupo]["inffin1510_fechacorte"] != '') {
                $decimalesVisibles = 2;

                //
                if ($rup[$grupo]["inffin1510_renpat"] != 998 && $rup[$grupo]["inffin1510_renpat"] != 999) {
                    $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_renpat"]));
                } else {
                    $rentabilidadPatrimonio = 'INDETERMINADO';
                    if ($rup[$grupo]["inffin1510_patnet"] != 0 && $rup[$grupo]["inffin1510_utiope"] != 0) {
                        $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_utiope"] / $rup[$grupo]["inffin1510_patnet"]));
                    }
                    if ($rup[$grupo]["inffin1510_patnet"] != 0 && $rup[$grupo]["inffin1510_utiope"] == 0) {
                        $rentabilidadPatrimonio = "0,00";
                    }
                }

                //
                if ($rup[$grupo]["inffin1510_renact"] != 998 && $rup[$grupo]["inffin1510_renact"] != 999) {
                    $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_renact"]));
                } else {
                    $rentabilidadActivo = 'INDETERMINADO';
                    if ($rup[$grupo]["inffin1510_acttot"] != 0 && $rup[$grupo]["inffin1510_utiope"] != 0) {
                        $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_utiope"] / $rup[$grupo]["inffin1510_acttot"]));
                    }
                    if ($rup[$grupo]["inffin1510_acttot"] != 0 && $rup[$grupo]["inffin1510_utiope"] == 0) {
                        $rentabilidadActivo = "0,00";
                    }
                }

                $txt = 'QUE EN RELACIÓN A LOS INDICADORES DE LA CAPACIDAD ORGANIZACIONAL EL PROPONENTE REPORTÓ CON CORTE A ' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["inffin1510_fechacorte"]) . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $txt = '';
                $txt .= 'RENTABILIDAD DEL PATRIMONIO______: ' . str_pad($rentabilidadPatrimonio, 25, '_', STR_PAD_LEFT) . '<br>';
                $txt .= 'RENTABILIDAD DEL ACTIVO__________: ' . str_pad($rentabilidadActivo, 25, '_', STR_PAD_LEFT) . '<br>';
                $txtF = str_replace("_", "&nbsp;", $txt);
                $pdf->writeHTML($txtF, true, false, true, false, 'L');
                construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
            }
        }
    }

    //
    if ($notadecreto399 == 'si') {
        $txt = '<strong>ACTUALIZACIÓN ESPECIAL DECRETO 399 DE 2021</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $txt = 'QUE EL DÍA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecinsdecreto399)) . ' EL PROPONENTE ACTUALIZÓ EL REGISTRO ÚNICO DE PROPONENTES DE CONFORMIDAD CON EL DECRETO 399 DE 2021, BAJO EL NÚMERO ' . $numregdecreto399 . ' DEL LIBRO PRIMERO DE LOS PROPONENTES, ';
        $txt .= 'QUE ESTA INSCRIPCIÓN SE PUBLICÓ EN EL REGISTRO ÚNICO EMPRESARIAL Y SOCIAL (RUES) EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras($pubruesdecreto399)) . '<br>.';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }
}

function construyeSeccionInformacionFinancieraAnterior($pdf, $rup, $grupo, $mysqli = null) {

    $txt = '<strong>CERTIFICA:<br>CAPACIDAD FINANCIERA Y DE ORGANIZACIÓN</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $txt = '<strong>FECHA CORTE DE LA INFORMACIÓN FINANCIERA : </strong>' . \funcionesGenerales::mostrarFecha2($rup[$grupo]["inffin1510_fechacorte"]) . '<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    //
    $txt = '<strong>INFORMACIÓN DEL ACTIVO</strong><br>';
    // if (doubleval($rup[$grupo]["inffin1510_actcte"]) != 0) {
    $txt .= 'ACTIVO CORRIENTE_______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_actcte"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    if (doubleval($rup[$grupo]["inffin1510_actnocte"]) != 0) {
        $actNoCorriente = $rup[$grupo]["inffin1510_actnocte"];
    } else {
        $actNoCorriente = $rup[$grupo]["inffin1510_acttot"] - $rup[$grupo]["inffin1510_actcte"];
    }
    $txt .= 'ACTIVO NO CORRIENTE____________: ' . str_pad(\funcionesGenerales::mostrarPesos2($actNoCorriente), 25, '_', STR_PAD_LEFT) . '<br>';

    if (doubleval($rup[$grupo]["inffin1510_fijnet"]) != 0) {
        $txt .= 'ACTIVO FIJO NETO_______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_fijnet"]), 25, '_', STR_PAD_LEFT) . '<br>';
    }
    if (doubleval($rup[$grupo]["inffin1510_actotr"]) != 0) {
        $txt .= 'ACTIVO OTROS___________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_actotr"]), 25, '_', STR_PAD_LEFT) . '<br>';
    }
    if (doubleval($rup[$grupo]["inffin1510_actval"]) != 0) {
        $txt .= 'ACTIVO VALORIZACIONES__________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_actval"]), 25, '_', STR_PAD_LEFT) . '<br>';
    }
    $txt .= 'ACTIVO TOTAL___________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_acttot"]), 25, '_', STR_PAD_LEFT) . '<br>';

    $txt .= '<strong>INFORMACIÓN DEL PASIVO Y PATRIMONIO</strong><br>';
    // if (doubleval($rup[$grupo]["inffin1510_pascte"]) != 0) {
    $txt .= 'PASIVO CORRIENTE_______________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_pascte"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    if (doubleval($rup[$grupo]["inffin1510_paslar"]) != 0) {
        $pasNoCorriente = $rup[$grupo]["inffin1510_paslar"];
    } else {
        $pasNoCorriente = $rup[$grupo]["inffin1510_pastot"] - $rup[$grupo]["inffin1510_pascte"];
    }
    $txt .= 'PASIVO NO CORRIENTE____________: ' . str_pad(\funcionesGenerales::mostrarPesos2($pasNoCorriente), 25, '_', STR_PAD_LEFT) . '<br>';

    // if (doubleval($rup[$grupo]["inffin1510_pastot"]) != 0) {
    $txt .= 'PASIVO TOTAL___________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_pastot"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    if (doubleval($rup[$grupo]["inffin1510_patnet"]) != 0) {
        $txt .= 'PATRIMONIO NETO________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_patnet"]), 25, '_', STR_PAD_LEFT) . '<br>';
    }
    if (doubleval($rup[$grupo]["inffin1510_paspat"]) != 0) {
        $txt .= 'PASIVO + PATRIMONIO____________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_paspat"]), 25, '_', STR_PAD_LEFT) . '<br>';
    }
    if (doubleval($rup[$grupo]["inffin1510_balsoc"]) != 0) {
        $txt .= 'BALANCE SOCIAL_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_balsoc"]), 25, '_', STR_PAD_LEFT) . '<br>';
    }

    $txt .= '<strong>UTILIDADES Y RESERVAS AL CORTE</strong><br>';
    // if (doubleval($rup[$grupo]["inffin1510_utiope"]) = 0) {
    $txt .= 'UTILIDAD PERDIDA OPERATIVA_____: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_utiope"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    // if (doubleval($rup[$grupo]["inffin1510_utinet"]) != 0) {
    $txt .= 'RESULTADOS DEL PERIODO_________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_utinet"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    $txt .= '<strong>INFORMACIÓN DEL P Y G AL CORTE</strong><br>';
    // if (doubleval($rup[$grupo]["inffin1510_ingope"]) != 0) {
    $txt .= 'INGRESOS ACTIVIDAD ORDINARIA___: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_ingope"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    // if (doubleval($rup[$grupo]["inffin1510_ingnoope"]) != 0) {
    $txt .= 'OTROS INGRESOS_________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_ingnoope"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    // if (doubleval($rup[$grupo]["inffin1510_cosven"]) != 0) {
    $txt .= 'COSTO DE VENTAS________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_cosven"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    // if (doubleval($rup[$grupo]["inffin1510_gasope"]) != 0) {
    $txt .= 'GASTOS OPERACIONALES___________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_gasope"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    // if (doubleval($rup[$grupo]["inffin1510_gasnoope"]) != 0) {
    $txt .= 'OTROS GASTOS___________________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_gasnoope"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    // if (doubleval($rup[$grupo]["inffin1510_gasimp"]) != 0) {
    $txt .= 'GASTOS POR IMPUESTOS___________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_gasimp"]), 25, '_', STR_PAD_LEFT) . '<br>';
    // }
    $txt .= 'GASTOS DE INTERESES____________: ' . str_pad(\funcionesGenerales::mostrarPesos2($rup[$grupo]["inffin1510_gasint"]), 25, '_', STR_PAD_LEFT) . '<br>';

    $decimalesVisibles = 2;

    //
    if (doubleval($rup[$grupo]["inffin1510_indliq"]) != 998 &&
            doubleval($rup[$grupo]["inffin1510_indliq"]) != 999 &&
            doubleval($rup[$grupo]["inffin1510_pascte"]) != 0) {
        $indiceLiquidez1 = $rup[$grupo]["inffin1510_actcte"] / $rup[$grupo]["inffin1510_pascte"];
        if (abs($indiceLiquidez1) < 0.01) {
            $indiceLiquidez = '0,00';
        } else {
            $indiceLiquidez = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_indliq"]));
        }
    } else {
        $indiceLiquidez = 'INDETERMINADO';
    }

    //
    // $indiceEndeudamiento = number_format(truncateFloat($rup[$grupo]["inffin1510_nivend"], $decimalesVisibles), $decimalesVisibles, ',', '.');
    $indiceEndeudamiento = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_nivend"]));

    //
    if (doubleval($rup[$grupo]["inffin1510_razcob"]) != 998 &&
            doubleval($rup[$grupo]["inffin1510_razcob"]) != 999 &&
            doubleval($rup[$grupo]["inffin1510_gasint"]) != 0) {
        // $razonCobertura = number_format(truncateFloat($rup[$grupo]["inffin1510_razcob"], $decimalesVisibles), $decimalesVisibles, ',', '.');
        $razonCobertura = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_razcob"]));
    } else {
        $razonCobertura = 'INDETERMINADO';
    }

    //
    // $rentabilidadPatrimonio = number_format(truncateFloat($rup[$grupo]["inffin1510_renpat"], $decimalesVisibles), $decimalesVisibles, ',', '.');
    $rentabilidadPatrimonio = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_renpat"]));

    //
    // $rentabilidadActivo = number_format(truncateFloat($rup[$grupo]["inffin1510_renact"], $decimalesVisibles), $decimalesVisibles, ',', '.');
    $rentabilidadActivo = \funcionesGenerales::truncateFinancialIndexes(str_replace(".", ",", $rup[$grupo]["inffin1510_renact"]));

    $txt .= '<strong>INDICES E INDICADORES</strong><br>';
    $txt .= 'INDICE DE LIQUIDEZ_____________: ' . str_pad($indiceLiquidez, 25, '_', STR_PAD_LEFT) . '<br>';
    $txt .= 'INDICE DE ENDEUDAMIENTO________: ' . str_pad($indiceEndeudamiento, 25, '_', STR_PAD_LEFT) . '<br>';
    $txt .= 'RAZÓN DE COBERTURA DE INTERESES: ' . str_pad($razonCobertura, 25, '_', STR_PAD_LEFT) . '<br>';
    $txt .= 'RENTABILIDAD DEL PATRIMONIO____: ' . str_pad($rentabilidadPatrimonio, 25, '_', STR_PAD_LEFT) . '<br>';
    $txt .= 'RENTABILIDAD DEL ACTIVO________: ' . str_pad($rentabilidadActivo, 25, '_', STR_PAD_LEFT) . '<br>';

    $txtF = str_replace("_", "&nbsp;", $txt);
    $pdf->writeHTML($txtF, true, false, true, false, 'L');
    construyeTextoVerificacionDocumental($pdf, 'construyeSeccionInformacionFinanciera', $mysqli);
}

function construyeSeccionClasificaciones1510($pdf, $rup, $grupo, $mysqli = null) {

    $txt = '<strong>CERTIFICA:<br>CLASIFICACIONES DE BIENES, OBRAS Y SERVICIOS</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');

    $txt = 'QUE EN RELACION A LOS BIENES, OBRAS Y SERVICIOS QUE OFRECERA A ';
    $txt .= 'LAS ENTIDADES ESTATALES, IDENTIFICADOS CON EL CLASIFICADOR DE ';
    $txt .= 'BIENES, OBRAS Y SERVICIOS EN EL TERCER NIVEL(CLASE), EL ';
    $txt .= 'PROPONENTE REPORTÓ:' . chr(10) . chr(13);
    $pdf->MultiCell(180, 4, $txt, 0, 'J', 0);
    $pdf->Ln();
    // construyeDetalleUnspsc($pdf, $rup[$grupo]["clasi1510"]);
    construyeDetalleUnspsc($pdf, $rup["clasi1510"], $mysqli);
    construyeTextoVerificacionDocumental($pdf, 'construyeSeccionClasificaciones1510', $mysqli);
}

function construyeSeccionExperiencia($pdf, $rup, $grupo, $mysqli = null) {
    $txt = '<strong>CERTIFICA:<br>EXPERIENCIA</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $txt = 'QUE EN RELACIÓN A LOS CONTRATOS EJECUTADOS EL PROPONENTE REPORTÓ:' . chr(10) . chr(13);
    $pdf->MultiCell(180, 4, $txt, 0, 'L', 0);
    $pdf->Ln();

    $i = 0;

    $arrCnts = array();
    foreach ($rup[$grupo]["exp1510"] as $cnt) {
        $sec = sprintf("%04s", $cnt["secuencia"]);
        $arrCnt[$sec] = $cnt;
    }
    asort($arrCnt);
    // foreach ($rup[$grupo]["exp1510"] as $cnt) {
    foreach ($arrCnt as $cnt) {
        $quienCelebra = '';
        if ($cnt["celebradopor"] == '1') {
            $quienCelebra = ' - EL PROPONENTE';
        }
        if ($cnt["celebradopor"] == '2') {
            $quienCelebra = ' - SOCIO / ASOCIADO';
        }
        if ($cnt["celebradopor"] == '3') {
            $quienCelebra = ' - CONSORCIO O UNIÓN TEMPORAL';
        }

        $i++;
        $txt = '<strong>*** EXPERIENCIA No.' . $i . ' :</strong><br>';
        $txt .= '<strong>NÚMERO CONSECUTIVO DEL CONTRATO:</strong>' . $cnt["secuencia"] . '<br>';
        $txt .= '<strong>CONTRATO CELEBRADO POR_________:</strong>' . $cnt["celebradopor"] . $quienCelebra . '<br>';
        $txt .= '<strong>NOMBRE DEL CONTRATISTA_________:</strong>' . $cnt["nombrecontratista"] . '<br>';
        $txt .= '<strong>NOMBRE DEL CONTRATANTE_________:</strong>' . $cnt["nombrecontratante"] . '<br>';
        $vx = str_replace(",", "", $cnt["valor"]);
        $vx = str_replace(".", ",", $vx);
        $txt .= '<strong>VALOR CONTRATADO EN SMMLV______:</strong>' . \funcionesGenerales::truncateFinancialIndexes($vx) . '<br>';
        if (ltrim($cnt["porcentaje"], "0") != '') {
            $txt .= '<strong>PORCENTAJE DE PARTICIPACIÓN EN EL VALOR EJECUTADO EN CASO DE CONSORCIOS Y UNIONES TEMPORALES: </strong>' . $cnt["porcentaje"] . '%<br>';
        }
        $txtF = str_replace("_", "&nbsp;", $txt);
        $pdf->writeHTML($txtF, true, false, true, false, 'L');
        construyeDetalleUnspsc($pdf, $cnt["clasif"], $mysqli);
    }
    construyeTextoVerificacionDocumental($pdf, 'construyeSeccionExperiencia', $mysqli);
}

function construyeSeccionTamEmpresa($pdf, $rup, $grupo, $mysqli = null) {
    $txt = '<strong>CERTIFICA:<br>CLASIFICACIÓN POR TAMAÑO DE LA EMPRESA</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $txt = '<strong>QUE EL INSCRITO SE CLASIFICÓ COMO : </strong>' . mb_strtoupper(retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_seltamano', $rup[$grupo]["tamanoempresa"]), 'utf-8') . '<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    if (trim($rup["matricula"]) != '') {
        construyeTextoVerificacionDocumental($pdf, 'construyeSeccionTamEmpresa', $mysqli);
    }
}

function construyeSeccionContratos($pdf, $rup, $mysqli = null) {
    $pdf->writeHTML('<strong>CERTIFICA:<br>CONTRATOS</strong><br>', true, false, true, false, 'C');
    $txt = 'QUE LA INFORMACIÓN QUE HAN REPORTADO LAS ENTIDADES ESTATALES EN ';
    $txt .= 'RELACIÓN CON CONTRATOS ADJUDICADOS, EN EJECUCIÓN, Y EJECUTADOS ';
    $txt .= 'ES LA SIGUIENTE:<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
    construyeDetalleContratos($pdf, $rup["contratos"], $mysqli);
}

function construyeDetalleContratos($pdf, $arrContratos, $mysqli = null) {

    $arrContratosOrd = ordenarArrayPorCampo($arrContratos, 'estadocont', false);

    $cantAdjudicados = $cantPerfeccionados = $cantEjecucion = $cantEjecutados = $cantLiquidados = $cantTerminados = $cantSesion = 0;

    foreach ($arrContratosOrd as $cont) {
        if (($cont["estadocont"] == '0') && ($cantAdjudicados == 0)) {
            $txt = '<strong>CONTRATOS ADJUDICADOS:</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $cantAdjudicados++;
        }
        if (($cont["estadocont"] == '1') && ($cantPerfeccionados == 0)) {
            $txt = '<strong>CONTRATOS PERFECCCIONADOS:</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $cantPerfeccionados++;
        }
        if (($cont["estadocont"] == '2') && ($cantEjecucion == 0)) {
            $txt = '<strong>CONTRATOS EN EJECUCIÓN:</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $cantEjecucion++;
        }
        if (($cont["estadocont"] == '3') && ($cantEjecutados == 0)) {
            $txt = '<strong>CONTRATOS TERMINADOS O EJECUTADOS:</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $cantEjecutados++;
        }
        if (($cont["estadocont"] == '4') && ($cantLiquidados == 0)) {
            $txt = '<strong>CONTRATOS LIQUIDADOS:</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $cantLiquidados++;
        }
        if (($cont["estadocont"] == '5') && ($cantTerminados == 0)) {
            $txt = '<strong>CONTRATOS TERMINADOS ANTICIPADAMENTE:</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $cantTerminados++;
        }
        if (($cont["estadocont"] == '6') && ($cantSesion == 0)) {
            $txt = '<strong>CONTRATOS EN SESIÓN DE CONTRATO:</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $cantSesion++;
        }
        $txt = '<strong>ENTIDAD CONTRATANTE:</strong>' . $cont["nombreentidad"] . '<br>';
        if (trim($cont["nitentidad"]) != '') {
            $txt .= '<strong>NIT:</strong>' . $cont["nitentidad"] . '<br>';
        }
        if (trim($cont["idmunientidad"]) != '') {
            $txt .= '<strong>MUNICIPIO:</strong>' . $cont["idmunientidad"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $cont["idmunientidad"]) . '<br>';
        }
        if (trim($cont["divarea"]) != '') {
            $txt .= '<strong>AREA O SECCIONAL:</strong>' . $cont["divarea"] . '<br>';
        }
        if (trim($cont["idefun"]) != '') {
            $txt .= '<strong>IDENTIFICACIÓN FUNCIONARIO:</strong>' . $cont["idefun"] . '<br>';
        }
        if (trim($cont["nomfun"]) != '') {
            $txt .= '<strong>NOMBRE FUNCIONARIO:</strong>' . $cont["nomfun"] . '<br>';
        }
        if (trim($cont["numcontrato"]) != '') {
            $txt .= '<strong>NÚMERO DEL CONTRATO:</strong>' . $cont["numcontrato"] . '<br>';
        }
        if (trim($cont["numcontratosecop"]) != '') {
            $txt .= '<strong>NÚMERO DEL CONTRATO SECOP:</strong>' . $cont["numcontratosecop"] . '<br>';
        }
        if (trim($cont["fechaadj"]) != '') {
            $txt .= '<strong>FECHA DE ADJUDICACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fechaadj"]) . '<br>';
        }
        if (trim($cont["fechaper"]) != '') {
            $txt .= '<strong>FECHA DE PERFECCIONAMIENTO:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fechaper"]) . '<br>';
        }
        if (trim($cont["fechaini"]) != '') {
            $txt .= '<strong>FECHA DE INICIO:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fechaini"]) . '<br>';
        }
        if (trim($cont["fechaeje"]) != '') {
            $txt .= '<strong>FECHA DE EJECUTORÍA:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fechaeje"]) . '<br>';
        }
        if (trim($cont["fechater"]) != '') {
            $txt .= '<strong>FECHA DE TERMINADO:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fechater"]) . '<br>';
        }
        if (trim($cont["fechaliq"]) != '') {
            $txt .= '<strong>FECHA DE LIQUIDACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fechaliq"]) . '<br>';
        }
        if (trim($cont["tipocont"]) != '') {
            $txt .= '<strong>TIPO DEL CONTRATO:</strong>' . $cont["tipocont"] . '<br>';
        }
        if (trim($cont["valorcont"]) != '') {
            $txt .= '<strong>VALOR DEL CONTRATO:</strong>' . \funcionesGenerales::mostrarPesos2($cont["valorcont"]) . '<br>';
        }
        if (trim($cont["valorcontpag"]) != '') {
            $txt .= '<strong>VALOR DEL CONTRATO PAGADO:</strong>' . \funcionesGenerales::mostrarPesos2($cont["valorcontpag"]) . '<br>';
        }
        if (trim($cont["indcump"]) != '') {
            $txt .= '<strong>INDICADOR DE CUMPLIMIENTO:</strong>' . $cont["indcump"] . '<br>';
        }
        if (trim($cont["motivoter"]) != '') {
            $txt .= '<strong>MOTIVO DE TERMINACIÓN:</strong>' . $cont["motivoter"] . '<br>';
        }
        if (trim($cont["fechaterant"]) != '') {
            $txt .= '<strong>FECHA DE TERMINACIÓN ANTICIPADA:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fechaterant"]) . '<br>';
        }
        if (trim($cont["motivoces"]) != '') {
            $txt .= '<strong>MOTIVO DE CESIÓN:</strong>' . $cont["motivoces"] . '<br>';
        }
        if (trim($cont["fechaces"]) != '') {
            $txt .= '<strong>FECHA DE CESIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fechaces"]) . '<br>';
        }
        if (trim($cont["fecreglib"]) != '') {
            $txt .= '<strong>FECHA DE INSCRIPCIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fecreglib"]) . '<br>';
        }
        if (trim($cont["numreglib"]) != '') {
            $txt .= '<strong>NÚMERO REGISTRO LIBRO I DE PROPOPONENTES:</strong>' . $cont["numreglib"] . '<br>';
        }

        if (trim($cont["codigocamaraorigen"]) != '') {
            $txt .= '<strong>CÓDIGO CÁMARA ORIGEN:</strong>' . $cont["codigocamaraorigen"] . '<br>';
        }

        if (trim($cont["numregistrocamaraorigen"]) != '') {
            $txt .= '<strong>REGISTRO CÁMARA ORIGEN:</strong>' . $cont["numregistrocamaraorigen"] . '<br>';
        }

        if (trim($cont["fecregistrocamaraorigen"]) != '') {
            $txt .= '<strong>FECHA DE REGISTRO CÁMARA ORIGEN:</strong>' . \funcionesGenerales::mostrarFecha2($cont["fecregistrocamaraorigen"]) . '<br>';
        }
        if (trim($cont["objeto"]) != '') {
            $txt .= '<strong>OBJETO:</strong>' . $cont["objeto"] . '<br>';
            // $pdf->writeHTML($txt, true, false, true, false, 'J');
        }

        $pdf->writeHTML($txt, true, false, true, false, 'L');
        if (!empty($cont["clasificaciones"])) {
            construyeDetalleClasificaciones($pdf, $cont["clasificaciones"], $mysqli);
        } else {
            if (!empty($cont["unspsc"])) {
                construyeDetalleUnspsc($pdf, $cont["unspsc"], $mysqli);
            }
        }
    }
    unset($arrContratos);
}

function construyeDetalleUnspsc($pdf, $arrUnspsc, $mysqli = null) {

    if (!isset($_SESSION["generales"]["unspsc"])) {

        $rest = retornarRegistrosMysqliApi($mysqli, 'mreg_unspsc', "1=1", "idcodigo");
        foreach ($rest as $rx) {
            $arrCodigosUnspsc[$rx["idcodigo"]] = $rx['descripcion'];
        }
        unset($rest);
        $_SESSION["generales"]["unspsc"] = $arrCodigosUnspsc;
    } else {
        $arrCodigosUnspsc = $_SESSION["generales"]["unspsc"];
    }

    $pdf->SetFont('courier', 'B', $pdf->tamanoLetra);
    $pdf->Cell(200, 4, 'SG FM CL PR - DESCRIPCIÓN', 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    foreach ($arrUnspsc as $unspsc) {
        if (!empty($unspsc)) {
            if (strlen(trim($arrCodigosUnspsc[$unspsc . '00'])) > 74) {
                $txcla = substr($arrCodigosUnspsc[$unspsc . '00'], 0, 71) . '...';
            } else {
                $txcla = $arrCodigosUnspsc[$unspsc . '00'];
            }
            $linea = substr($unspsc, 0, 2) . ' ' .
                    substr($unspsc, 2, 2) . ' ' .
                    substr($unspsc, 4, 2) . ' ' .
                    '00 : ' . mb_strtoupper($txcla, 'utf-8') . '';

            $pdf->Cell(200, 4, $linea, 0, 0, 'L');
            $pdf->Ln();
        }
    }
    $pdf->Ln();

    unset($arrCodigosUnspsc);
    unset($arrUnspsc);
}

function construyeDetalleClasificaciones($pdf, $arrClasificaciones, $mysqli = null) {
    $pdf->SetFont('courier', 'B', $pdf->tamanoLetra);
    $pdf->Cell(200, 4, 'CÓDIGO - DESCRIPCIÓN', 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    foreach ($arrClasificaciones as $cla) {
        if (!empty($cla)) {
            $linea = $cla . ' : ' . mb_strtoupper(substr(retornarRegistroMysqliApi($mysqli, "bas_clasificaciones", "idclasificacion='" . $cla . "'", "nombre"), 0, 75), 'utf-8') . '';
            $pdf->Cell(200, 4, $linea, 0, 0, 'L');
            $pdf->Ln();
        }
    }
    $pdf->Ln();
    unset($arrClasificaciones);
}

function construyeSeccionHistoriaInscripciones($pdf, $rup, $mysqli = null) {

    //OBTENER LA INFORMACIÓN HISTÓRICA A PARTIR DE LA ÚLTIMA INSCRIPCIÓN REALIZADA - 2017-07-14 - WSI
    $inscripcionesCertificadas = array();
    $i = 0;
    $insertarInscripcion = 'si';
    foreach ($rup["inscripciones"] as $reg) {
        switch ($reg["acto"]) {
            case '01':
            case '16':
                $actoInicial = 'si';
                $insertarInscripcion = 'si';
                break;
            case '02':
            case '03':
            case '06':
            case '36':
                $mos = 'si';
                if ($reg["acto"] == '36' && ACTIVADO_DECRETO_399_RUP_CERTIFICADO != 'S') {
                    $mos = 'no';
                }
                if ($mos == 'si') {
                    $actoInicial = 'no';
                    $insertarInscripcion = 'si';
                }
                break;
            default:
                $insertarInscripcion = 'no';
                break;
        }

        if ($insertarInscripcion == 'si') {
            if ($actoInicial == 'si') {
                unset($inscripcionesCertificadas);
                $i = 0;
                $inscripcionesCertificadas[$i] = $reg;
                $i++;
            } else {
                $inscripcionesCertificadas[$i] = $reg;
                $i++;
            }
        }
    }


    if (!empty($inscripcionesCertificadas)) {
        $pdf->writeHTML('<strong>CERTIFICA:<br>HISTORIA DE INSCRIPCIONES</strong><br>', true, false, true, false, 'C');

        construyeCertificaTexto($pdf, $rup, '9500');

        foreach ($inscripcionesCertificadas as $reg) {

            $mostrarHistoria = 'si';
            switch ($reg["acto"]) {
                case '01':
                    $accionVerbo = 'INSCRIBIÓ';
                    break;
                case '16':
                    $accionVerbo = 'ACTUALIZÓ POR CAMBIO DE DOMICILIO';
                    break;
                case '02':
                    $accionVerbo = 'RENOVÓ';
                    break;
                case '03':
                    $accionVerbo = 'ACTUALIZÓ';
                    break;
                case '06':
                    $accionVerbo = '';
                    break;
                case '36':
                    $accionVerbo = 'ACTUALIZÓ AL DECRETO 399';
                    break;
                default :
                    $mostrarHistoria = 'no';
                    break;
            }

            if ($mostrarHistoria == 'si') {
                if ($accionVerbo != '') {
                    if (trim($reg["fecpublicacionrue"]) != '') {
                        $txt = 'QUE EL DÍA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($reg["fecharegistro"])) . ' EL PROPONENTE SE ' . $accionVerbo . ' ';
                        $txt .= 'EN EL REGISTRO ÚNICO DE PROPONENTES BAJO EL NÚMERO ' . $reg["registro"] . ' DEL LIBRO PRIMERO ';
                        $txt .= 'DE LOS PROPONENTES, QUE ESTA INSCRIPCIÓN SE PUBLICÓ EN EL REGISTRO ÚNICO EMPRESARIAL Y SOCIAL EL DÍA ';
                        $txt .= strtoupper(\funcionesGenerales::mostrarFechaLetras1($reg["fecpublicacionrue"])) . '.' . chr(10) . chr(13) . chr(10) . chr(13);
                    } else {
                        $txt = 'QUE EL DÍA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($reg["fecharegistro"])) . ' EL PROPONENTE SE ' . $accionVerbo . ' ';
                        $txt .= 'EN EL REGISTRO ÚNICO DE PROPONENTES BAJO EL NÚMERO ' . $reg["registro"] . ' DEL LIBRO PRIMERO ';
                        $txt .= 'DE LOS PROPONENTES, QUE ESTA INSCRIPCIÓN ESTA PENDIENTE DE SER PUBLICADA EN EL REGISTRO ÚNICO EMPRESARIAL Y SOCIAL.' . chr(10) . chr(13) . chr(10) . chr(13);
                    }
                } else {
                    $txt = mb_strtoupper($reg["texto"], 'utf-8');
                }

                $pdf->MultiCell(180, 4, $txt, 0, 'J', 0);
            }
        }
    }
}

function construyeSeccionMultas($pdf, $rup, $mysqli = null) {

    $i = 0;
    foreach ($rup["multas"] as $multa) {
        if ($multa["estadomult"] != '9') {
            // 2020 01 23 - JINT - Cambio de la rutina que valida las fechas para la multa
            // if (diferenciaEntreFechaBase30Sii2(date("Ymd"), $multa["fecreglib"]) < 366) {
            if (\funcionesGenerales::diferenciaEntreFechaBase30(date("Ymd"), $multa["fecreglib"]) < 366) {
                $i++;
                if ($i == 1) {
                    $pdf->writeHTML('<strong>CERTIFICA:<br>MULTAS</strong><br>', true, false, true, false, 'C');
                    $txt = 'QUE LA INFORMACIÓN QUE HAN REPORTADO LAS ENTIDADES ESTATALES CON ';
                    $txt .= 'RELACION CON LAS MULTAS EN FIRME ES LA SIGUIENTE:<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                }
                $txt = '<strong>MULTA No.' . $i . ' </strong><br>';
                $txt .= '<strong>ENTIDAD QUE REPORTÓ LA MULTA:</strong>' . $multa["nombreentidad"] . '<br>';
                if (trim($multa["nitentidad"]) != '') {
                    $txt .= '<strong>NIT:</strong>' . $multa["nitentidad"] . '<br>';
                }
                if (trim($multa["idmunientidad"]) != '') {
                    $txt .= '<strong>MUNICIPIO:</strong>' . $multa["idmunientidad"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $multa["idmunientidad"]) . '<br>';
                }
                if (trim($multa["divarea"]) != '') {
                    $txt .= '<strong>AREA O SECCIONAL:</strong>' . $multa["divarea"] . '<br>';
                }
                if (trim($multa["idefun"]) != '') {
                    $txt .= '<strong>IDENTIFICACIÓN FUNCIONARIO:</strong>' . $multa["idefun"] . '<br>';
                }
                if (trim($multa["nomfun"]) != '') {
                    $txt .= '<strong>NOMBRE FUNCIONARIO:</strong>' . $multa["nomfun"] . '<br>';
                }
                if (trim($multa["numcontrato"]) != '') {
                    $txt .= '<strong>NÚMERO DEL CONTRATO:</strong>' . $multa["numcontrato"] . '<br>';
                }
                if (trim($multa["numcontratosecop"]) != '') {
                    $txt .= '<strong>NÚMERO DEL CONTRATO SECOP:</strong>' . $multa["numcontratosecop"] . '<br>';
                }
                if (trim($multa["fechaacto"]) != '') {
                    $txt .= '<strong>FECHA DE ACTO:</strong>' . \funcionesGenerales::mostrarFecha2($multa["fechaacto"]) . '<br>';
                }
                if (trim($multa["fechaeje"]) != '') {
                    $txt .= '<strong>FECHA DE EJECUTORÍA:</strong>' . \funcionesGenerales::mostrarFecha2($multa["fechaeje"]) . '<br>';
                }
                if (trim($multa["valormult"]) != '') {
                    $txt .= '<strong>VALOR DE LA MULTA:</strong>' . \funcionesGenerales::mostrarPesos2($multa["valormult"]) . '<br>';
                }
                if (trim($multa["valormultpag"]) != '') {
                    $txt .= '<strong>VALOR PAGADO DE LA MULTA:</strong>' . \funcionesGenerales::mostrarPesos2($multa["valormultpag"]) . '<br>';
                }
                if (trim($multa["numsus"]) != '') {
                    $txt .= '<strong>NÚMERO DE ACTO DE SUSPENSIÓN:</strong>' . $multa["numsus"] . '<br>';
                }
                if (trim($multa["fechasus"]) != '') {
                    $txt .= '<strong>FECHA DE ACTO DE SUSPENSIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($multa["fechasus"]) . '<br>';
                }
                if (trim($multa["numconf"]) != '') {
                    $txt .= '<strong>NÚMERO DE ACTO DE CONFIRMACIÓN:</strong>' . $multa["numconf"] . '<br>';
                }
                if (trim($multa["fechaconf"]) != '') {
                    $txt .= '<strong>FECHA DE ACTO DE CONFIRMACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($multa["fechaconf"]) . '<br>';
                }
                if (trim($multa["numrev"]) != '') {
                    $txt .= '<strong>NÚMERO DE ACTO DE REVOCACIÓN:</strong>' . $multa["numrev"] . '<br>';
                }
                if (trim($multa["fechanumrev"]) != '') {
                    $txt .= '<strong>FECHA DE ACTO DE REVOCACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($multa["fechanumrev"]) . '<br>';
                }
                if (trim($multa["fecreglib"]) != '') {
                    $txt .= '<strong>FECHA DE INSCRIPCIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($multa["fecreglib"]) . '<br>';
                }
                if (trim($multa["numreglib"]) != '') {
                    $txt .= '<strong>NÚMERO REGISTRO LIBRO I DE PROPOPONENTES:</strong>' . $multa["numreglib"] . '<br>';
                }
                if (trim($multa["codigocamaraorigen"]) != '') {
                    $txt .= '<strong>CÓDIGO CÁMARA ORIGEN:</strong>' . $multa["codigocamaraorigen"] . '<br>';
                }
                if (trim($multa["numregistrocamaraorigen"]) != '') {
                    $txt .= '<strong>REGISTRO CÁMARA ORIGEN:</strong>' . $multa["numregistrocamaraorigen"] . '<br>';
                }
                if (trim($multa["fecregistrocamaraorigen"]) != '') {
                    $txt .= '<strong>FECHA DE REGISTRO CÁMARA ORIGEN:</strong>' . \funcionesGenerales::mostrarFecha2($multa["fecregistrocamaraorigen"]) . '<br>';
                }

                $pdf->writeHTML($txt, true, false, true, false, 'L');
                $pdf->Ln();
            }
        }
    }
    if ($i == 0) {
        return false;
    } else {
        return true;
    }
}

function construyeSeccionSanciones($pdf, $rup, $mysqli = null) {

    $pdf->writeHTML('<strong>CERTIFICA:<br>SANCIONES</strong><br>', true, false, true, false, 'C');
    $txt = 'QUE LA INFORMACIÓN QUE HAN REPORTADO LAS ENTIDADES ESTATALES CON ';
    $txt .= 'RELACION CON LAS SANCIONES EN FIRME ES LA SIGUIENTE:<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');

    $i = 0;
    foreach ($rup["sanciones"] as $sanc) {
        $mos = 'no';
        if ($sanc["estadosanc"] != '4') {
            if (ltrim(trim($sanc["vigencia"]), "0") != '') {
                if ($sanc["vigencia"] > date("Ymd")) {
                    $mos = 'si';
                }
            } else {
                if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $sanc["fecreglib"]) < 1825) {
                    $mos = 'si';
                }
            }
        }
        if ($mos == 'si') {
            $i++;
            $txt = '<strong>SANCIÓN No.' . $i . ' :</strong><br>';
            $txt .= '<strong>ENTIDAD QUE REPORTÓ LA SANCIÓN:</strong>' . $sanc["nombreentidad"] . '<br>';

            if (trim($sanc["nitentidad"]) != '') {
                $txt .= '<strong>NIT:</strong>' . $sanc["nitentidad"] . '<br>';
            }
            if (trim($sanc["idmunientidad"]) != '') {
                $txt .= '<strong>MUNICIPIO:</strong>' . $sanc["idmunientidad"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $sanc["idmunientidad"]) . '<br>';
            }
            if (trim($sanc["divarea"]) != '') {
                $txt .= '<strong>AREA O SECCIONAL:</strong>' . $sanc["divarea"] . '<br>';
            }
            if (trim($sanc["idefun"]) != '') {
                $txt .= '<strong>IDENTIFICACIÓN FUNCIONARIO:</strong>' . $sanc["idefun"] . '<br>';
            }
            if (trim($sanc["nomfun"]) != '') {
                $txt .= '<strong>NOMBRE FUNCIONARIO:</strong>' . $sanc["nomfun"] . '<br>';
            }
            if (trim($sanc["numcontrato"]) != '') {
                $txt .= '<strong>NÚMERO DEL CONTRATO:</strong>' . $sanc["numcontrato"] . '<br>';
            }
            if (trim($sanc["numcontratosecop"]) != '') {
                $txt .= '<strong>NÚMERO DEL CONTRATO SECOP:</strong>' . $sanc["numcontratosecop"] . '<br>';
            }
            if (trim($sanc["fechaacto"]) != '') {
                $txt .= '<strong>FECHA DE ACTO:</strong>' . \funcionesGenerales::mostrarFecha2($sanc["fechaacto"]) . '<br>';
            }
            if (trim($sanc["fechaeje"]) != '') {
                $txt .= '<strong>FECHA DE EJECUTORÍA:</strong>' . \funcionesGenerales::mostrarFecha2($sanc["fechaeje"]) . '<br>';
            }
            if (trim($sanc["vigencia"]) != '') {
                $txt .= '<strong>FECHA DE VIGENCIA:</strong>' . \funcionesGenerales::mostrarFecha2($sanc["vigencia"]) . '<br>';
            }
            if (trim($sanc["numsus"]) != '') {
                $txt .= '<strong>NÚMERO DE ACTO DE SUSPENSIÓN:</strong>' . $sanc["numsus"] . '<br>';
            }
            if (trim($sanc["fechasus"]) != '') {
                $txt .= '<strong>FECHA DE ACTO DE SUSPENSIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($sanc["fechasus"]) . '<br>';
            }
            if (trim($sanc["numconf"]) != '') {
                $txt .= '<strong>NÚMERO DE ACTO DE CONFIRMACIÓN:</strong>' . $sanc["numconf"] . '<br>';
            }
            if (trim($sanc["fechaconf"]) != '') {
                $txt .= '<strong>FECHA DE ACTO DE CONFIRMACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($sanc["fechaconf"]) . '<br>';
            }
            if (trim($sanc["numrev"]) != '') {
                $txt .= '<strong>NÚMERO DE ACTO DE REVOCACIÓN:</strong>' . $sanc["numrev"] . '<br>';
            }
            if (trim($sanc["fechanumrev"]) != '') {
                $txt .= '<strong>FECHA DE ACTO DE REVOCACIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($sanc["fechanumrev"]) . '<br>';
            }
            if (trim($sanc["fecreglib"]) != '') {
                $txt .= '<strong>FECHA DE INSCRIPCIÓN:</strong>' . \funcionesGenerales::mostrarFecha2($sanc["fecreglib"]) . '<br>';
            }
            if (trim($sanc["numreglib"]) != '') {
                $txt .= '<strong>NÚMERO REGISTRO LIBRO I DE PROPOPONENTES:</strong>' . $sanc["numreglib"] . '<br>';
            }
            if (trim($sanc["codigocamaraorigen"]) != '') {
                $txt .= '<strong>CÓDIGO CÁMARA ORIGEN:</strong>' . $sanc["codigocamaraorigen"] . '<br>';
            }
            if (trim($sanc["numregistrocamaraorigen"]) != '') {
                $txt .= '<strong>REGISTRO CÁMARA ORIGEN:</strong>' . $sanc["numregistrocamaraorigen"] . '<br>';
            }
            if (trim($sanc["fecregistrocamaraorigen"]) != '') {
                $txt .= '<strong>FECHA DE REGISTRO CÁMARA ORIGEN:</strong>' . \funcionesGenerales::mostrarFecha2($sanc["fecregistrocamaraorigen"]) . '<br>';
            }
            $pdf->writeHTML($txt, true, false, true, false, 'L');

            if (trim($sanc["descripcion"]) != '') {
                $txt = '<strong>DESCRIPCIÓN:</strong>' . $sanc["descripcion"];
                $pdf->writeHTML($txt, true, false, true, false, 'J');
            }
            if (trim($sanc["fundamento"]) != '') {
                $txt = '<strong>FUNDAMENTO LEGAL:</strong>' . $sanc["fundamento"];
                $pdf->writeHTML($txt, true, false, true, false, 'J');
            }

            $pdf->Ln();
        }
    }
}

function construyeSeccionSancionesDisciplinarias($pdf, $rup, $mysqli = null) {
    $txt = '<strong>CERTIFICA:<br>SANCIONES DISCIPLINARIAS</strong><br>';
    /*
      $pdf->writeHTML($txt, true, false, true, false, 'C');
      $txt .= var_export($rup["sandis"], true);
      $pdf->writeHTML($txt, true, false, true, false, 'C');
     */
}

function construyeSeccionInhabilidad($pdf, $regInab, $Mysqli = null) {
    $pdf->writeHTML('<strong>' . $regInab["textosii"][0] . '</strong><br>', true, false, true, false, 'C');
    $pdf->writeHTML($regInab["textosii"][1], true, false, true, false, 'J');
    $pdf->writeHTML($regInab["textosii"][2], true, false, true, false, 'J');
    $pdf->writeHTML($regInab["textosii"][3], true, false, true, false, 'J');
    $pdf->writeHTML($regInab["textosii"][4], true, false, true, false, 'J');
}

function construyeTextoValorCertificado($pdf, $tipo, $valor, $mysqli = null) {
    if ($tipo == 'Normal') {
        $txt = 'VALOR DEL CERTIFICADO : $' . number_format($valor, 0);
        $pdf->SetFont('courier', 'B', 9);
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
    }
}

function construyeTextoFirma($pdf, $tipoFirma, $mysqli = null) {
    if ($tipoFirma == 'FIRMA_SECRETARIO' || $tipoFirma == 'CERTITOKEN') {
        $txt = '<strong>IMPORTANTE:</strong> La firma digital del secretario de la <strong>' . RAZONSOCIAL . '</strong> contenida en este certificado electrónico '
                . 'se encuentra emitida por una entidad de certificación acreditada por el Organismo Nacional de Acreditación de Colombia (ONAC), '
                . 'de conformidad con las exigencias establecidas en la Ley 527 de 1999 para validez jurídica y probatoria '
                . 'de los documentos electrónicos.<br>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }

    if ($tipoFirma == 'FIRMA_PERJUR') {
        $txt = '<strong>IMPORTANTE:</strong> La firma digital de la <strong>' . RAZONSOCIAL . '</strong> contenida en este certificado electrónico '
                . 'se encuentra emitida por una entidad de certificación acreditada por el Organismo Nacional de Acreditación de Colombia (ONAC), '
                . 'de conformidad con las exigencias establecidas en la Ley 527 de 1999 para validez jurídica y probatoria '
                . 'de los documentos electrónicos.<br>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }
}

function construyeTextoFirmaQueEs($pdf, $mysqli = null) {
    $txt = 'La firma digital no es una firma digitalizada o escaneada, por lo tanto, la firma digital que acompaña este documento la podrá '
            . 'verificar a través de su aplicativo visor de documentos pdf.<br>';
    $pdf->SetFont('helvetica', '', 7);
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function construyeTextoFirmaImpresion($pdf, $aleatorio, $mysqli = null) {
    /*
      $txt = 'No obstante, si usted va a imprimir este certificado, lo puede hacer desde su computador, con la certeza de que el mismo fue '
      . 'expedido a través del canal virtual de la cámara de comercio y que la persona o entidad a la que usted le va a entregar el certificado '
      . 'impreso, puede verificarlos cuantas veces sea necesario durante un lapso de 2 meses, ingresando al enlace <strong>' . TIPO_HTTP . HTTP_HOST . '/cv.php</strong> '
      . 'seleccionando la cámara de comercio e indicando el código de verificación <strong>' . $aleatorio . "</strong><br>";
      $pdf->writeHTML($txt, true, false, true, false, 'J');
     */
}

function construyeTextoFirmaVerificacion($pdf, $mysqli = null) {
    $txt = 'Al realizar la verificación podrá visualizar (y descargar) una imagen exacta del certificado que fue entregado al usuario en el momento '
            . 'que se realizó la transacción.<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function construyeTextoRee($pdf, $mysqli = null) {
    $txt = 'LA INFORMACIÓN REMITIDA POR LAS ENTIDADES ESTATALES '
            . 'EN VIRTUD DEL ARTÍCULO 14 DEL DECRETO 1510 DE JULIO 17 DE 2013, '
            . 'INCORPORADO EN EL DECRETO 1082 DE 2015, NO SERÁ VERIFICADA POR LAS '
            . 'CÁMARAS DE COMERCIO. POR LO TANTO LAS CONTROVERSIAS RESPECTO '
            . 'DE LOS CONTRATOS, MULTAS Y SANCIONES REPORTADOS POR LAS ENTIDADES '
            . 'ESTATALES, DEBERÁN SURTIRSE ANTE LA ENTIDAD ESTATAL CORRESPONDIENTE '
            . 'Y NO PODRÁN DEBATIRSE ANTE LAS CÁMARAS DE COMERCIO.<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function construyeTextoFirmaMecanica($pdf, $mysqli = null) {
    $txt = 'La firma mecánica que se muestra a continuación es la representación gráfica de la firma del secretario jurídico (o de quien haga sus veces) '
            . 'de la Cámara de Comercio quien avala este certificado. La firma mecánica no reemplaza la firma digital en los documentos electrónicos.<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
    $rutaFirmaMecanica = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/formatos/firmacertificados.png';
    if (file_exists($rutaFirmaMecanica)) {
        $pdf->writeHTML('<img height="120" src="' . $rutaFirmaMecanica . '" alt="" />', true, false, true, false, 'C');
    } else {
        $pdf->writeHTML('&nbsp;', true, false, true, false, 'C');
    }
}

function construyeTextoConsulta($pdf, $mysqli = null) {

    $txt = '<strong>IMPORTANTE:</strong> La consulta de este certificado ha sido generada a través del Portal de Servicios '
            . 'Virtuales de la <strong>' . RAZONSOCIAL . '</strong>, se expide a solicitud del interesado unicamente '
            . 'como mecanismo para consultar los datos del expediente en cuestión, <strong>NO TIENE NINGUNA VALIDEZ '
            . 'JURIDICA</strong>. Para verificar la confiabilidad de información contenida en esta consulta, ingresar a '
            . '<strong>' . TIPO_HTTP . HTTP_HOST . '/cv.php</strong><br>';

    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function construyeTextoVerificacionDocumental($pdf, $bloqueorigen = '', $mysqli = null) {
    $txt = 'ESTA INFORMACION FUE OBJETO DE VERIFICACIÓN DOCUMENTAL POR PARTE DE LA ' . RAZONSOCIAL . '.<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function construyeTextoNoSujetoVerificacionDocumental($pdf, $bloqueorigen = '', $mysqli = null) {
    $txt = 'ESTA INFORMACION NO ESTA SUJETA A VERIFICACIÓN DOCUMENTAL, DE ACUERDO CON LO DISPUESTO EN EL DECRETO 1510 DE 2013, INCORPORADO EN EL DECRETO 1082 DE 2015.<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function construyeTextoNoVerificacionDocumental($pdf, $mysqli = null) {
    $txt = 'ESTA INFORMACION NO ESTA SUJETA A VERIFICACIÓN DOCUMENTAL, DE ';
    $txt .= 'ACUERDO CON LO DISPUESTO EN EL DECRETO 1510 DE 2013, INCORPORADO ';
    $txt .= 'EN EL DECRETO 1082 DE 2015.<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function construyeTextoFirmezaFinal($pdf, $mysqli = null) {
    if (!defined('HABIL_SABADO') || trim(HABIL_SABADO) == '') {
        $habilsabado = 'NO';
    } else {
        $habilsabado = HABIL_SABADO;
    }
    
    $pdf->writeHTML('<strong>CERTIFICA:</strong><br>', true, false, true, false, 'C');
    $txt = 'DE CONFORMIDAD CON LO ESTABLECIDO EN EL CÓDIGO DE PROCEDIMIENTO ADMINISTRATIVO Y DE LO CONTENCIOSO Y DE LA ';
    $txt .= 'LEY 962 DE 2005, LOS ACTOS ADMINISTRATIVOS DE REGISTRO AQUÍ CERTIFICADOS QUEDAN EN FIRME DIEZ (10) DÍAS ';
    $txt .= 'HÁBILES DESPUES DE LA FECHA DE INSCRIPCIÓN, SIEMPRE QUE NO SEAN OBJETO DE RECURSOS. EL DÍA SÁBADO ' . $habilsabado . ' ';
    $txt .= 'SE CUENTA COMO DÍA HÁBIL.';
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
}

function construyeTextoFirmeza($pdf, $mysqli = null) {
    $txt = 'LA INFORMACIÓN RELACIONADA CON LA INSCRIPCIÓN AQUI CERTIFICADA, QUEDARÁ ';
    $txt .= 'EN FIRME DIEZ (10) DÍAS HÁBILES DESPUÉS DE LA FECHA DE PUBLICACIÓN, ';
    $txt .= 'SIEMPRE QUE NO SEA OBJETO DE RECURSO (ARTÍCULO 6.3 DE LA LEY 1150 DE 2007).<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function ordenarArrayPorCampo($toOrderArray, $field, $inverse = false) {

    $position = array();
    $newRow = array();
    foreach ($toOrderArray as $key => $row) {
        $position[$key] = $row[$field];
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

function construyeCertificaTexto($pdf, $rup, $certif) {
    if (isset($rup["crtsii"][$certif]) && trim($rup["crtsii"][$certif]) != '') {
        $txt1 = trim($rup["crtsii"][$certif]);
        $txt1 = str_replace(array("?", "&nbsp;"), array(" ", ""), $txt1);
        $pdf->writeHTML($txt1 . '<br>', true, false, true, false, 'J');
    }
}
