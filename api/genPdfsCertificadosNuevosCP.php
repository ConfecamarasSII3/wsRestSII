<?php

function generarCertificadosPdfEspeciales($mysqli, $data, $i, $cuerpo, $tipo = 'Consulta', $firmar = 'si') {
    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/tcpdf.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/examples/lang/eng.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

    $nameLog = 'generarCertificadosPdfEspeciales_' . date("Ymd");

    //
    if (!defined('TITULOS_EN_CERTIFICADOS_SII')) {
        define('TITULOS_EN_CERTIFICADOS_SII', 'SI');
    }
    if (!defined('MAYUSCULAS_SOSTENIDAS')) {
        define('MAYUSCULAS_SOSTENIDAS', 'SI');
    }
    $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = TITULOS_EN_CERTIFICADOS_SII;
    $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';
    $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = true;
    if (MAYUSCULAS_SOSTENIDAS == 'NO') {
        $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = false;
    }


    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
    set_error_handler('myErrorHandler');
    ob_clean();

    if (!class_exists('PDFReque')) {

        class PDFReque extends TCPDF {

            public $tamanoLetra = 8;
            public $tituloTipoFirma = '';
            public $tituloTipoHttp = '';
            public $tituloHttpHost = '';
            public $tituloAleatorio = '';
            public $tituloTipo = '';
            public $tituloPathAbsoluto = '';
            public $tituloNombreCamara = '';
            public $tituloCamara = '';
            public $tituloRecibo = '';
            public $tituloOperacion = '';
            public $pagina = 0;

            /* Funcion para rotar un txto */

            public function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
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
                    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
                }
            }

            /* Funcion que imprime texto rotado */

            public function RotatedText($x, $y, $txt, $angle = 0) {
                $this->Rotate($angle, $x, $y);
                $this->Text($x, $y, $txt);
                $this->Rotate(0);
            }

            public function Header() {
                $this->SetMargins(10, 40, 7);
                $this->Rect(10, 9, 195, 260);
                if (file_exists($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg')) {
                    $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                }
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 10);

                //
                $this->SetFontSize(8);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tituloNombreCamara . '</strong>', true, false, true, false, 'C');
                $this->SetTextColor(0, 0, 0);

                //
                $txt = '<strong>Fecha expedición: </strong>' . date("Y/m/d") . ' - ' . date("H:i:s");
                if ($this->tituloRecibo != '') {
                    $txt .= ' **** <strong>Recibo No. </strong>' . $this->tituloRecibo;
                }
                if ($this->tituloOperacion != '') {
                    $txt .= ' **** <strong>Num. Operación. </strong>' . $this->tituloOperacion;
                }
                $this->SetFontSize(7);
                $this->writeHTML($txt, true, false, true, false, 'C');

                //
                if ($this->tituloTipo == 'Consulta' || $this->tituloTipo == 'Api') {
                    $this->SetFontSize(8);
                    $txt = '*** SOLO CONSULTA SIN VALIDEZ JURÍDICA ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'Notarias') {
                    $this->SetFontSize(8);
                    $txt = '*** EXPEDIDO A SOLICITUD DE CLIENTES NOTARIALES ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }

                if ($this->tituloTipo == 'GasAfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A AFILIADOS ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAdm') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII) ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasOfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A ENTIDAD OFICIAL ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'Normal' || $this->tituloTipo == '') {
                    $this->SetFontSize(8);
                    $txt = '*** EXPEDIDO A TRAVÉS DEL SISTEMA VIRTUAL S.I.I. ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->codigoverificacion != '') {
                    $txt = '<strong>CODIGO DE VERIFICACIÓN ' . $this->codigoverificacion . '</strong>';
                    $this->SetFontSize(8);
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }

                //
                $this->Ln();
                $this->Ln();
                $y = $this->GetY();
                $this->Line(17, $y, 190, $y);
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    // ****************************************************************************** //
    // Instanciamiento
    // ****************************************************************************** //
    $pdf = new PDFReque(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    $pdf->tituloRecibo = $data["recibo"];
    $pdf->tituloOperacion = $data["operacion"];
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;
    $pdf->pagina = 0;
    $pdf->codigoverificacion = $data["codigoverificacion"] . '-' . $i;

    //
    $tipoFirma = 'FIRMA_SECRETARIO';
    if (!defined('CERTIFICADOS_FIRMA_DIGITAL')) {
        define('CERTIFICADOS_FIRMA_DIGITAL', 'FIRMA_SECRETARIO');
    }
    if (CERTIFICADOS_FIRMA_DIGITAL != '') {
        $tipoFirma = CERTIFICADOS_FIRMA_DIGITAL;
    }

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integrado de Información SII');
    $pdf->SetTitle('Certificados');
    $pdf->SetSubject('Certificados');

    // set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    // require_once('../../includes/tcpdf_6.2.13/examples/lang/eng.php');
    $pdf->setLanguageArray($l);

    // ---------------------------------------------------------
    // set font
    $pdf->SetFont('helvetica', '', TAMANO_LETRA_CERTIFICADOS_SII);

    //
    $pdf->AddPage();


    // *************************************************************************** //
    // Mensaje elecciones de juntag directiva
    // *************************************************************************** //    
    armarTextoElecciones($pdf);


    // *************************************************************************** //
    // 2. Título del tipo de certificado
    // *************************************************************************** //
    $txt1 = '';
    $pdf->SetFont('helvetica', '', $pdf->tamanoLetra);
    $txt1 = 'CERTIFICADO ESPECIAL';
    $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');

    // *************************************************************************** //
    // Cuerpo del certificado
    // *************************************************************************** //    
    $pdf->SetFont('helvetica', '', $pdf->tamanoLetra);
    $pdf->writeHTML($cuerpo, true, false, true, false, 'J');
    $pdf->Ln();

    //
    armarImagenFirma($pdf);

    // 
    if ($data["codigoverificacion"] == '') {
        if ($pdf->tituloTipo == 'Consulta') {
            $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
            $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoEspecial-' . $aleatorio . '.pdf';
            $pdf->Output($name1, "D");
            return $name1;
        } else {
            $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
            if (isset($_SESSION["generales"]["pathabsoluto"])) {
                $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoEspecial-' . $aleatorio . '.pdf';
            } else {
                $name1 = '../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoEspecial-' . $aleatorio . '.pdf';
            }
            $pdf->Output($name1, "F");

            return $name1;
        }
    } else {
        $anox = date("Y");
        $mesx = sprintf("%02s", date("m"));
        if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox)) {
            mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox, 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox);
        }
        if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx)) {
            mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx, 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx);
        }
        $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $data["codigoverificacion"] . '-' . $i . '.pdf';
        $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $data["codigoverificacion"] . '-' . $i . '.pdf';
        $pdf->Output($name1, "F");

        //
        $msg = 'Recibo : ' . $data["recibo"] . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . ' | Tipo : CerEsp | Hora : ' . date("His");
        $nameLog1 = 'controlFirmaCertificados_' . date("Ymd");
        if (defined('ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS')) {
            if ((ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' || ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI-TODOS') && $firmar == 'si') {
                require_once("PDFA.class.php");
                $ins = new PDFA();
                $ins->generarPDFAfirmado($data["codigoverificacion"], $name1, 'no');
                $msg .= '| Firmado digitalmente (' . date("His") . ')';
                log::general2($nameLog1, '', $msg);
            } else {
                $msg .= '| Sin Firma digital';
                log::general2($nameLog1, '', $msg);
            }
        } else {
            $msg .= '| Sin Firma digital';
            log::general2($nameLog1, '', $msg);
        }
        echo $name2;
        return $name2;
    }
}

/**
 * 
 * @param type $mysqli
 * @param type $data
 * @param type $tipo
 * @param type $valorCertificado
 * @param type $operacion
 * @param type $recibo
 * @param type $aleatorio
 * @param type $certificadoConsultaRues
 * @param type $escajero
 * @param type $esbanco
 * @param type $firmar
 * @return string
 */
function generarCertificadosPdfMatricula($mysqli, $data, $tipo, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ob_clean();
    ob_start();
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');

    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/tcpdf.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/examples/lang/eng.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');

    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }

    $nameLog = 'generarCertificadosPdfMatricula_' . date("Ymd");

    //
    $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");

    //
    if (!defined('TITULOS_EN_CERTIFICADOS_SII')) {
        define('TITULOS_EN_CERTIFICADOS_SII', 'SI');
    }
    if (!defined('MAYUSCULAS_SOSTENIDAS')) {
        define('MAYUSCULAS_SOSTENIDAS', 'SI');
    }
    $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = TITULOS_EN_CERTIFICADOS_SII;
    $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';
    $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = true;
    if (MAYUSCULAS_SOSTENIDAS == 'NO') {
        $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = false;
    }

    // Arma lista de certificas y su clasificacion
    $_SESSION["generales"]["clasecerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_codigos_certificas', "1=1", "id");
    foreach ($temcerts as $c) {
        $_SESSION["generales"]["clasecerti"][$c["id"]] = $c;
    }
    unset($temcerts);

    // Arma orden del certificado
    $_SESSION["generales"]["ordencerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_orden_certificas', "tipocertificado='CerMat'", "orden");
    $i = 0;
    foreach ($temcerts as $c) {
        $i++;
        if (strtoupper($c["estado"]) == 'A') {
            $_SESSION["generales"]["ordencerti"][$i] = $c;
        }
    }
    unset($temcerts);

    //
    // set_error_handler('myErrorHandler');
    ob_clean();

    if (!class_exists('PDFReque')) {

        class PDFReque extends TCPDF {

            public $tamanoLetra = 8;
            public $tituloTipoFirma = '';
            public $tituloTipoHttp = '';
            public $tituloHttpHost = '';
            public $tituloAleatorio = '';
            public $tituloEstadoMatricula = '';
            public $tituloTipo = '';
            public $tituloPathAbsoluto = '';
            public $tituloNombreCamara = '';
            public $tituloCamara = '';
            public $tituloRazonSocial = '';
            public $tituloRecibo = '';
            public $tituloOperacion = '';
            public $tituloEstadoDatos = '';
            public $norenovado = '';
            public $certificadoConsultaRues = 'no';
            public $codcertificaanterior = '';
            public $pagina = 0;

            /* Funcion para rotar un txto */

            public function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
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
                    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
                }
            }

            /* Funcion que imprime texto rotado */

            public function RotatedText($x, $y, $txt, $angle = 0) {
                $this->Rotate($angle, $x, $y);
                $this->Text($x, $y, $txt);
                $this->Rotate(0);
            }

            public function Header() {
                $this->SetMargins(10, 40, 7);
                $this->Rect(10, 9, 195, 260);
                if (file_exists($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg')) {
                    $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                }
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 10);

                //
                $this->SetFontSize(8);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tituloNombreCamara . '</strong>', true, false, true, false, 'C');
                $this->writeHTML('<strong>' . $this->tituloRazonSocial . '</strong>', true, false, true, false, 'C');
                $this->SetTextColor(0, 0, 0);
                //
                $txt = '<strong>Fecha expedición: </strong>' . date("Y/m/d") . ' - ' . date("H:i:s");
                if ($this->tituloRecibo != '') {
                    $txt .= ' **** <strong>Recibo No. </strong>' . $this->tituloRecibo;
                }
                if ($this->tituloOperacion != '') {
                    $txt .= ' **** <strong>Num. Operación. </strong>' . $this->tituloOperacion;
                }
                $this->SetFontSize(7);
                $this->writeHTML($txt, true, false, true, false, 'C');

                //
                if ($this->tituloEstadoDatos != '') {
                    if ($this->tituloEstadoDatos == '3') {
                        $txt = '!!! El expediente no se encuentra revisado !!!';
                        $this->SetFontSize(8);
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        if ($this->tituloEstadoDatos != '6') {
                            $txt = '!!! El expediente tiene trámites pendientes de registro o digitación !!!';
                            $this->SetFontSize(8);
                            $this->writeHTML($txt, true, false, true, false, 'C');
                        }
                    }
                }

                if ($this->tituloEstadoMatricula != 'MF' && $this->tituloEstadoMatricula != 'MC') {
                    if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                        $this->SetFontSize(7);
                        $txt = 'LA MATRÍCULA MERCANTIL PROPORCIONA SEGURIDAD Y CONFIANZA EN LOS NEGOCIOS<br>';
                        $txt .= 'RENUEVE SU MATRÍCULA A MÁS TARDAR EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"])) . ' Y EVITE SANCIONES DE HASTA 17 S.M.L.M.V';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        $this->Ln();
                        $this->Ln();
                    }
                } else {
                    $this->Ln();
                    $this->Ln();
                }
                if ($this->tituloTipo == 'Consulta' || $this->tituloTipo == 'Api') {
                    $this->SetFontSize(8);
                    $txt = '*** SOLO CONSULTA SIN VALIDEZ JURÍDICA ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'Notarias') {
                    $this->SetFontSize(8);
                    $txt = '*** EXPEPIDO A SOLICITUD DE CLIENTES NOTARIALES ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'Notarias') {
                    $this->SetFontSize(8);
                    $txt = '*** EXPEPIDO A SOLICITUD DE CLIENTES NOTARIALES ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A AFILIADOS ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAdm') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII) ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasOfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A ENTIDAD OFICIAL ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'Normal' || $this->tituloTipo == '') {
                    $this->SetFontSize(8);
                    $txt = '*** EXPEDIDO A TRAVÉS DEL SISTEMA VIRTUAL S.I.I. ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->codigoverificacion != '') {
                    $txt = '<strong>CODIGO DE VERIFICACIÓN ' . $this->codigoverificacion . '</strong>';
                    $this->SetFontSize(8);
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }

                //
                $this->Ln();
                $y = $this->GetY();
                $this->Line(17, $y, 190, $y);

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
                } else {
                    if ($this->norenovado == 'si') {
                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(30, 170, '-----------------------------------------------', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(60, 150, 'NO HA CUMPLIDO', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        $this->RotatedText(50, 180, 'CON LA OBLIGACION LEGAL DE', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        $this->RotatedText(50, 200, 'RENOVAR SU MATRICULA MERCANTIL', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(53, 207, '--------------------------------------------------', 45);
                        $this->SetTextColor(0, 0, 0);
                    }
                }
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    // ****************************************************************************** //
    // Convierte textos a mayúsculas
    // ****************************************************************************** //    
    if (convertirMayusculas($data["organizacion"], $data["categoria"])) {
        $data["crt1"] = array();
        foreach ($data["crt"] as $id => $text) {
            $data["crt1"][$id] = mb_strtoupper($text, 'UTF-8');
        }
        $data["crt"] = $data["crt1"];
        unset($data["crt1"]);
        $data["crtsii1"] = array();
        foreach ($data["crtsii"] as $id => $text) {
            $data["crtsii1"][$id] = mb_strtoupper($text, 'UTF-8');
        }
        $data["crtsii"] = $data["crtsii1"];
        unset($data["crtsii1"]);
    }

    // ****************************************************************************** //
    // Instanciamiento
    // ****************************************************************************** //
    $pdf = new PDFReque(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }
    $pdf->tituloEstadoMatricula = $data["estadomatricula"];
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    $pdf->tituloRazonSocial = $data["nombre"];
    $pdf->tituloRecibo = $recibo;
    $pdf->tituloOperacion = $operacion;
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;
    $pdf->norenovado = 'no';
    $pdf->pagina = 0;
    $pdf->codigoverificacion = $aleatorio;

    if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {

        if ($data["ultanoren"] == date("Y") - 1) {
            if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                $pdf->norenovado = 'si';
                if ($data["disueltaporvencimiento"] == 'si') {
                    $pdf->norenovado = 'no';
                } else {
                    if ($data["disueltaporacto510"] == 'si') {
                        if ($data["fechaacto510"] <= $_SESSION["generales"]["fcorte"]) {
                            $pdf->norenovado = 'no';
                        }
                    } else {
                        if ($data["perdidacalidadcomerciante"] == 'si') {
                            $ano1 = $data["ultanoren"] + 1;
                            $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                            if ($data["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                $pdf->norenovado = 'no';
                            }
                        }
                    }
                }
            }
        }

        //
        if ($data["ultanoren"] < date("Y") - 1) {
            $pdf->norenovado = 'si';
            if ($data["disueltaporvencimiento"] == 'si') {
                $pdf->norenovado = 'no';
            } else {
                if ($data["disueltaporacto510"] == 'si') {
                    $ano1 = $data["ultanoren"] + 1;
                    $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                    if ($data["fechaacto510"] <= $fcorte1) {
                        $pdf->norenovado = 'no';
                    }
                } else {
                    if ($data["perdidacalidadcomerciante"] == 'si') {
                        $ano1 = $data["ultanoren"] + 1;
                        $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                        if ($data["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                            $pdf->norenovado = 'no';
                        }
                    }
                }
            }
        }
    }

    //
    $pdf->certificadoConsultaRues = $certificadoConsultaRues;

    //
    $tipoFirma = 'FIRMA_SECRETARIO';
    if (!defined('CERTIFICADOS_FIRMA_DIGITAL')) {
        define('CERTIFICADOS_FIRMA_DIGITAL', 'FIRMA_SECRETARIO');
    }
    if (CERTIFICADOS_FIRMA_DIGITAL != '') {
        $tipoFirma = CERTIFICADOS_FIRMA_DIGITAL;
    }

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integrado de Información SII');
    $pdf->SetTitle('Certificados');
    $pdf->SetSubject('Certificados');

    // set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    // require_once('../../includes/tcpdf_6.2.13/examples/lang/eng.php');
    $pdf->setLanguageArray($l);

    // ---------------------------------------------------------
    // set font
    $pdf->SetFont('courier', '', 8);

    //
    $pdf->AddPage();


    // *************************************************************************** //
    // Mensaje elecciones de juntag directiva
    // *************************************************************************** //    
    armarTextoElecciones($pdf);
    \logApi::general2($nameLog, '', 'armarTextoElecciones');

    // *************************************************************************** //
    // 1. Mensaje si tiene códigos de barras pendientes
    // *************************************************************************** //
    if (!empty($data["lcodigosbarras"])) {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $txt = '<strong>NOS PERMITIMOS INFORMARLE QUE AL MOMENTO DE LA EXPEDICIÓN DE ESTE CERTIFICADO, ';
        $txt .= 'EXISTEN PETICIONES EN TRÁMITE, LO QUE PUEDE AFECTAR EL CONTENIDO ';
        $txt .= 'DE LA INFORMACIÓN QUE CONSTA EN EL MISMO</strong>';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
        \logApi::general2($nameLog, '', 'lcodigosbarras');
    }

    // *************************************************************************** //
    // 2. Título del tipo de certificado
    // *************************************************************************** //
    $txt1 = '';
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $txt1 = 'CERTIFICADO DE MATRÍCULA MERCANTIL.';
    if ($data["organizacion"] == '01') {
        $txt1 = 'CERTIFICADO DE MATRÍCULA MERCANTIL DE PERSONA NATURAL.';
    }
    if ($data["organizacion"] == '02') {
        $txt1 = 'CERTIFICADO DE MATRÍCULA MERCANTIL DE ESTABLECIMIENTO DE COMERCIO.';
    }
    if ($data["categoria"] == '3') {
        $txt1 = 'CERTIFICADO DE MATRÍCULA MERCANTIL DE AGENCIA.';
    }

    $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    if (substr($data["matricula"], 0, 1) != 'S') {
        $pdf->writeHTML('Con fundamento en las matrículas e inscripciones del Registro Mercantil,', true, false, true, false, 'C');
    } else {
        $pdf->writeHTML('Con fundamento en las inscripciones del Registro de Entidades sin Ánimo de Lucro y de la Economía Solidaria,', true, false, true, false, 'C');
    }
    $pdf->Ln();
    // *************************************************************************** //
    // Título de certifica general
    // *************************************************************************** //    
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $txt = '<strong>CERTIFICA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();

    // *************************************************************************** //
    // Si la matrícula se encuentra cancelada
    // *************************************************************************** //
    if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'MF') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $txt1 = '**** LA MATRÍCULA MERCANTIL SE ENCUENTRA CANCELADA ****';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
    }


    // *************************************************************************** //
    foreach ($_SESSION["generales"]["ordencerti"] as $c) {
        //
        $incluir = true;
        if (isset($c["endisueltas"])) {
            if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'IC') {
                if ($c["encanceladas"] != 'S') {
                    $incluir = false;
                }
            } else {
                if ($data["disueltaporvencimiento"] == 'si' || $data["disueltaporacto510"] == 'si') {
                    if ($c["endisueltas"] != 'S') {
                        $incluir = false;
                    }
                } else {
                    if (isset($data["liquidada"]) && $data["liquidada"] == 'si') {
                        if ($c["enliquidadas"] != 'S') {
                            $incluir = false;
                        }
                    }
                }
            }
        }

        //
        if ($incluir) {
            $con = str_replace("&amp;&amp;", "&&", $c["condicion"]);
            $con = str_replace("&gt;", ">", $con);
            if (trim($con) == '') {
                $incluir = true;
            } else {
                eval("$con");
            }
            if ($incluir) {
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                $rut = $c["rutina"];
                eval("$rut");
                \logApi::general2($nameLog, '', $rut);
            }
        }
    }

    //
    if ($pdf->tituloTipo == 'Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        ob_end_clean();
        return $name1;
    }
    if ($pdf->tituloTipo == 'Api') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        ob_end_clean();
        return $name2;
    }
    if ($pdf->tituloTipo == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        ob_end_clean();
        return $name1;
    }

    //
    $anox = date("Y");
    $mesx = sprintf("%02s", date("m"));
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox, 0777);
        crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox);
    }
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx, 0777);
        crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx);
    }
    $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $pdf->Output($name1, "F");

    /*
     * WSIERRA 2017/09/14 - Incluir firma digital en certificados diferentes de consulta RUES
     */
    if ($certificadoConsultaRues != 'si') {
        $msg = 'Recibo : ' . $recibo . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . '(' . $escajero . ') (' . $esbanco . ') | Matricula : ' . $data["matricula"] . ' | Tipo : CerExi | Hora : ' . date("His");
        $nameLog1 = 'controlFirmaCertificados_' . date("Ymd");
        if (defined('ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS')) {
            if ((ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $escajero != 'SI') ||
                    ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI-TODOS' || (ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $firmar == 'si')
            ) {
                require_once('PDFA.class.php');
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
    }

    ob_end_clean();
    return $name2;
}

/*
 * Certifica de existencia, en el siguiente orden:
 * 
 */

function generarCertificadosPdfExistencia($mysqli, $data, $tipo, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ob_clean();
    ob_start();
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');

    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/tcpdf.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/examples/lang/eng.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');

    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }

    $nameLog = 'generarCertificadosPdfExistenciaSii_' . date("Ymd");

    //
    $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");

    //
    if (!defined('TITULOS_EN_CERTIFICADOS_SII')) {
        define('TITULOS_EN_CERTIFICADOS_SII', 'SI');
    }
    if (!defined('MAYUSCULAS_SOSTENIDAS')) {
        define('MAYUSCULAS_SOSTENIDAS', 'SI');
    }
    $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = TITULOS_EN_CERTIFICADOS_SII;
    $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';
    $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = true;
    if (MAYUSCULAS_SOSTENIDAS == 'NO') {
        $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = false;
    }

    // Arma lista de certificas y su clasificacion
    $_SESSION["generales"]["clasecerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_codigos_certificas', "1=1", "id");
    foreach ($temcerts as $c) {
        $_SESSION["generales"]["clasecerti"][$c["id"]] = $c;
    }
    unset($temcerts);

    // Arma orden del certificado
    $_SESSION["generales"]["ordencerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_orden_certificas', "tipocertificado='CerExi'", "orden");
    $i = 0;
    foreach ($temcerts as $c) {
        $i++;
        if (strtoupper($c["estado"]) == 'A') {
            $_SESSION["generales"]["ordencerti"][$i] = $c;
        }
    }
    unset($temcerts);


    if (!class_exists('PDFReque')) {

        class PDFReque extends TCPDF {

            public $tamanoLetra = 8;
            public $tituloTipoFirma = '';
            public $tituloTipoHttp = '';
            public $tituloHttpHost = '';
            public $tituloAleatorio = '';
            public $tituloEstadoMatricula = '';
            public $tituloTipo = '';
            public $tituloPathAbsoluto = '';
            public $tituloNombreCamara = '';
            public $tituloCamara = '';
            public $tituloRazonSocial = '';
            public $tituloRecibo = '';
            public $tituloOperacion = '';
            public $tituloEstadoDatos = '';
            public $norenovado = '';
            public $certificadoConsultaRues = 'no';
            public $codcertificaanterior = '';
            public $codigoverificacion = '';
            public $pagina = 0;

            /* Funcion para rotar un txto */

            public function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
                    $y = $this->y;
                if (isset($this->angle) && $this->angle != 0)
                    $this->_out('Q');
                $this->angle = $angle;

                if ($angle != 0) {
                    $angle *= M_PI / 180;
                    $c = cos($angle);
                    $s = sin($angle);
                    $cx = $x * $this->k;
                    $cy = ($this->h - $y) * $this->k;
                    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
                }
            }

            /* Funcion que imprime texto rotado */

            public function RotatedText($x, $y, $txt, $angle = 0) {
                $this->Rotate($angle, $x, $y);
                $this->Text($x, $y, $txt);
                $this->Rotate(0);
            }

            public function Header() {
                $this->SetMargins(10, 40, 7);
                $this->Rect(10, 9, 195, 260);
                if (file_exists($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg')) {
                    $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                }
                // $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 10);

                //
                $this->SetFontSize(8);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tituloNombreCamara . '</strong>', true, false, true, false, 'C');
                $this->writeHTML('<strong>' . $this->tituloRazonSocial . '</strong>', true, false, true, false, 'C');
                $this->SetTextColor(0, 0, 0);
                //
                $txt = '<strong>Fecha expedición: </strong>' . date("Y/m/d") . ' - ' . date("H:i:s");
                if ($this->tituloRecibo != '') {
                    $txt .= ' **** <strong>Recibo No. </strong>' . $this->tituloRecibo;
                }
                if ($this->tituloOperacion != '') {
                    $txt .= ' **** <strong>Num. Operación. </strong>' . $this->tituloOperacion;
                }
                $this->SetFontSize(7);
                $this->writeHTML($txt, true, false, true, false, 'C');

                //
                if ($this->tituloEstadoDatos != '') {
                    if ($this->tituloEstadoDatos == '3') {
                        $txt = '!!! El expediente no se encuentra revisado !!!';
                        $this->SeatFontSize(8);
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        if ($this->tituloEstadoDatos != '6') {
                            $txt = '!!! El expediente tiene trámites pendientes de registro o digitación !!!';
                            $this->SetFontSize(8);
                            $this->writeHTML($txt, true, false, true, false, 'C');
                        }
                    }
                }

                if (
                        $this->tituloEstadoMatricula != 'MF' &&
                        $this->tituloEstadoMatricula != 'MC' &&
                        $this->tituloEstadoMatricula != 'IC' &&
                        $this->tituloEstadoMatricula != 'IF'
                ) {
                    if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                        $this->SetFontSize(7);
                        $txt = 'LA MATRÍCULA MERCANTIL PROPORCIONA SEGURIDAD Y CONFIANZA EN LOS NEGOCIOS<br>';
                        $txt .= 'RENUEVE SU MATRÍCULA A MÁS TARDAR EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"])) . ' Y EVITE SANCIONES DE HASTA 17 S.M.L.M.V';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        $this->Ln();
                        $this->Ln();
                    }
                } else {
                    $this->Ln();
                    $this->Ln();
                }
                if ($this->tituloTipo == 'Consulta' || $this->tituloTipo == 'Api') {
                    $this->SetFontSize(8);
                    $txt = '*** SOLO CONSULTA SIN VALIDEZ JURÍDICA ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'Notarias') {
                    $this->SetFontSize(8);
                    $txt = '*** EXPEDIDO A SOLICITUD DE CLIENTES NOTARIALES ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A AFILIADOS ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAdm') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII) ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasOfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A ENTIDAD OFICIAL ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->codigoverificacion != '') {
                    $txt = '<strong>CODIGO DE VERIFICACIÓN ' . $this->codigoverificacion . '</strong>';
                    $this->SetFontSize(8);
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }

                //
                $this->Ln();
                $y = $this->GetY();
                $this->Line(17, $y, 190, $y);

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
                } else {
                    if ($this->norenovado == 'si') {
                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(30, 170, '-----------------------------------------------', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(60, 150, 'NO HA CUMPLIDO', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        $this->RotatedText(50, 180, 'CON LA OBLIGACION LEGAL DE', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        $this->RotatedText(50, 200, 'RENOVAR SU MATRICULA MERCANTIL', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(53, 207, '--------------------------------------------------', 45);
                        $this->SetTextColor(0, 0, 0);
                    }
                }
            }

            //
            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    // ****************************************************************************** //
    // Convierte textos a mayúsculas
    // ****************************************************************************** //    
    if (convertirMayusculas($data["organizacion"], $data["categoria"])) {
        $data["crt1"] = array();
        foreach ($data["crt"] as $id => $text) {
            $data["crt1"][$id] = mb_strtoupper($text, 'UTF-8');
        }
        $data["crt"] = $data["crt1"];
        unset($data["crt1"]);
        $data["crtsii1"] = array();
        foreach ($data["crtsii"] as $id => $text) {
            $data["crtsii1"][$id] = mb_strtoupper($text, 'UTF-8');
        }
        $data["crtsii"] = $data["crtsii1"];
        unset($data["crtsii1"]);
    }

    // ****************************************************************************** //
    // Instanciamiento
    // ****************************************************************************** //
    $pdf = new PDFReque(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }
    $pdf->tituloEstadoMatricula = $data["estadomatricula"];
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    $pdf->tituloRazonSocial = $data["nombre"];
    $pdf->tituloRecibo = $recibo;
    $pdf->tituloOperacion = $operacion;
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;
    $pdf->norenovado = 'no';
    $pdf->codigoverificacion = $aleatorio;
    $pdf->pagina = 1;

    //
    if (
            $data["estadomatricula"] != 'MF' &&
            $data["estadomatricula"] != 'MC' &&
            $data["estadomatricula"] != 'IC' &&
            $data["estadomatricula"] != 'IF'
    ) {
        if ($data["ctrcancelacion1429"] != '3') {
            if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                if ($data["ultanoren"] == date("Y") - 1) {
                    $pdf->norenovado = 'si';
                    if ($data["disueltaporvencimiento"] == 'si') {
                        $pdf->norenovado = 'no';
                    } else {
                        if ($data["disueltaporacto510"] == 'si') {
                            if ($data["fechaacto510"] <= $_SESSION["generales"]["fcorte"]) {
                                $pdf->norenovado = 'no';
                            }
                        } else {
                            if ($data["perdidacalidadcomerciante"] == 'si') {
                                $ano1 = $data["ultanoren"] + 1;
                                $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                                if ($data["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                    $pdf->norenovado = 'no';
                                }
                            }
                        }
                    }
                }
            }
            //

            if ($data["ultanoren"] < date("Y") - 1) {
                $pdf->norenovado = 'si';
                if ($data["disueltaporvencimiento"] == 'si') {
                    if (($data["fechaacto510"] != '') && $data["fechaacto510"] < $data["fechavencimiento"]) {
                        $data["disueltaporvencimiento"] = 'no';
                    } else {
                        if (substr($data["fechavencimiento"], 4, 4) <= '0331') {
                            $ultanorendis = substr($data["fechavencimiento"], 0, 4) - 1;
                        } else {
                            $ultanorendis = substr($data["fechavencimiento"], 0, 4);
                        }
                        if ($data["ultanoren"] >= $ultanorendis) {
                            $pdf->norenovado = 'no';
                        }
                    }
                }
                if ($data["disueltaporacto510"] == 'si') {
                    if (substr($data["fechaacto510"], 0, 4) == $data["ultanoren"]) {
                        $pdf->norenovado = 'no';
                    } else {
                        $ano1 = $data["ultanoren"] + 1;
                        $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                        if ($data["fechaacto510"] <= $fcorte1) {
                            $pdf->norenovado = 'no';
                        } else {
                            if ($data["perdidacalidadcomerciante"] == 'si') {
                                if ($data["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                    $pdf->norenovado = 'no';
                                }
                            } else {
                                if ($data["reactivadaacto511"] == 'si') {
                                    if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                                        if ($data["fechaacto511"] >= (date("Y") - 1) . '0101' && $data["fechaacto511"] <= $_SESSION["generales"]["fcorte"]) {
                                            $pdf->norenovado = 'no';
                                        }
                                    } else {
                                        if ($data["fechaacto511"] >= date("Y") . '0101') {
                                            $pdf->norenovado = 'no';
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

    /*
      if ($data["estadomatricula"] != 'MF' &&
      $data["estadomatricula"] != 'MC' &&
      $data["estadomatricula"] != 'IC' &&
      $data["estadomatricula"] != 'IF') {
      if ($data["ctrcancelacion1429"] != '3') {
      if (date("md") > '0331') {
      if ($data["ultanoren"] == date("Y") - 1) {
      $pdf->norenovado = 'si';
      if ($data["disueltaporvencimiento"] == 'si') {
      $pdf->norenovado = 'no';
      } else {
      if ($data["disueltaporacto510"] == 'si') {
      if ($data["fechaacto510"] <= date("Y") . '0331') {
      $pdf->norenovado = 'no';
      }
      } else {
      if ($data["perdidacalidadcomerciante"] == 'si') {
      if ($data["fechaperdidacalidadcomerciante"] <= ($data["ultanoren"]) + 1 . '0331') {
      $pdf->norenovado = 'no';
      }
      }
      }
      }
      }
      }
      //

      if ($data["ultanoren"] < date("Y") - 1) {
      $pdf->norenovado = 'si';
      if ($data["disueltaporvencimiento"] == 'si') {
      if (($data["fechaacto510"] != '') && $data["fechaacto510"] < $data["fechavencimiento"]) {
      $data["disueltaporvencimiento"] = 'no';
      } else {
      if (substr($data["fechavencimiento"], 4, 4) <= '0331') {
      $ultanorendis = substr($data["fechavencimiento"], 0, 4) - 1;
      } else {
      $ultanorendis = substr($data["fechavencimiento"], 0, 4);
      }
      if ($data["ultanoren"] >= $ultanorendis) {
      $pdf->norenovado = 'no';
      }
      }
      }
      if ($data["disueltaporacto510"] == 'si') {
      if ($data["disueltaporacto510"] == 'si') {
      if (substr($data["fechaacto510"], 0, 4) == $data["ultanoren"]) {
      $pdf->norenovado = 'no';
      } else {
      if ($data["fechaacto510"] <= ($data["ultanoren"] + 1) . '0331') {
      $pdf->norenovado = 'no';
      } else {
      if ($data["perdidacalidadcomerciante"] == 'si') {
      if ($data["fechaperdidacalidadcomerciante"] <= ($data["ultanoren"]) + 1 . '0331') {
      $pdf->norenovado = 'no';
      }
      } else {
      if ($data["reactivadaacto511"] == 'si') {
      if (date("md") <= '0331') {
      if ($data["fechaacto511"] >= (date("Y") - 1) . '0101' && $data["fechaacto511"] <= date("Y") . '0331') {
      $pdf->norenovado = 'no';
      }
      } else {
      if ($data["fechaacto511"] >= date("Y") . '0101') {
      $pdf->norenovado = 'no';
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
     */

    //
    $pdf->certificadoConsultaRues = $certificadoConsultaRues;

    //
    $tipoFirma = 'FIRMA_SECRETARIO';
    if (!defined('CERTIFICADOS_FIRMA_DIGITAL')) {
        define('CERTIFICADOS_FIRMA_DIGITAL', 'FIRMA_SECRETARIO');
    }
    if (CERTIFICADOS_FIRMA_DIGITAL != '') {
        $tipoFirma = CERTIFICADOS_FIRMA_DIGITAL;
    }

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integrado de Información SII');
    $pdf->SetTitle('Certificados');
    $pdf->SetSubject('Certificados');

    // set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    // require_once('../../includes/tcpdf_6.2.13/examples/lang/eng.php');
    $pdf->setLanguageArray($l);

    // ---------------------------------------------------------
    // set font
    $pdf->SetFont('courier', '', 8);

    //
    $pdf->AddPage();

    // *************************************************************************** //
    // Mensaje elecciones de juntag directiva
    // *************************************************************************** //    
    armarTextoElecciones($pdf);

    // *************************************************************************** //
    // Mensaje si tiene códigos de barras pendientes
    // *************************************************************************** //
    if (!empty($data["lcodigosbarras"])) {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $txt = '<strong>NOS PERMITIMOS INFORMARLE QUE AL MOMENTO DE LA EXPEDICIÓN DE ESTE CERTIFICADO, ';
        $txt .= 'EXISTEN PETICIONES EN TRÁMITE, LO QUE PUEDE AFECTAR EL CONTENIDO ';
        $txt .= 'DE LA INFORMACIÓN QUE CONSTA EN EL MISMO</strong>';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
    }

    // *************************************************************************** //
    // 2. Título del tipo de certificado
    // *************************************************************************** //
    $txt1 = '';
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($data["categoria"] == '1') {
        $txt1 = 'CERTIFICADO DE EXISTENCIA Y REPRESENTACIÓN LEGAL O DE INSCRIPCIÓN DE DOCUMENTOS.';
    }
    if ($data["categoria"] == '2') {
        $txt1 = 'CERTIFICADO DE EXISTENCIA Y REPRESENTACIÓN LEGAL O DE INSCRIPCIÓN DE DOCUMENTOS DE SUCURSAL.';
    }
    $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    if (substr($data["matricula"], 0, 1) != 'S') {
        $pdf->writeHTML('Con fundamento en las matrículas e inscripciones del Registro Mercantil,', true, false, true, false, 'C');
    } else {
        $pdf->writeHTML('Con fundamento en las inscripciones del Registro de Entidades sin Ánimo de Lucro y de la Economía Solidaria,', true, false, true, false, 'C');
    }
    $pdf->Ln();

    // *************************************************************************** //
    // Título de certifica general
    // *************************************************************************** //    
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $txt = '<strong>CERTIFICA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();

    // *************************************************************************** //
    // Si la matrícula se encuentra cancelada
    // *************************************************************************** //
    if ($data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'MC') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $txt1 = '**** LA MATRÍCULA MERCANTIL SE ENCUENTRA CANCELADA ****';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
    }
    if ($data["estadomatricula"] == 'IC' || $data["estadomatricula"] == 'IF') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $txt1 = '**** LA INSCRIPCIÓN EN EL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO SE ENCUENTRA CANCELADA ****';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
    }

    // *************************************************************************** //
    foreach ($_SESSION["generales"]["ordencerti"] as $c) {
        //
        $incluir = true;
        if (isset($c["endisueltas"])) {
            if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'IC') {
                if ($c["encanceladas"] != 'S') {
                    $incluir = false;
                }
            } else {
                if ($data["disueltaporvencimiento"] == 'si' || $data["disueltaporacto510"] == 'si') {
                    if ($c["endisueltas"] != 'S') {
                        $incluir = false;
                    }
                } else {
                    if (isset($data["liquidada"]) && $data["liquidada"] == 'si') {
                        if ($c["enliquidadas"] != 'S') {
                            $incluir = false;
                        }
                    }
                }
            }
        }

        //
        if ($incluir) {
            $con = str_replace("&amp;&amp;", "&&", $c["condicion"]);
            $con = str_replace("&gt;", ">", $con);
            if (trim($con) == '') {
                $incluir = true;
            } else {                
                eval("$con");
            }
            if ($incluir) {
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                $rut = $c["rutina"];
                // \logApi::general2($nameLog, $data["matricula"], $rut);
                eval("$rut");
            }
        }
    }


    //
    if ($pdf->tituloTipo == 'Consulta') {
        $name1 = str_replace("//", "/", $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoConsulta-' . $aleatorio . '.pdf');
        $pdf->Output($name1, "F");
        ob_end_clean();
        unset ($pdf);
        return $name1;
    }
    if ($pdf->tituloTipo == 'Api') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset ($pdf);
        return $name2;
    }
    if ($pdf->tituloTipo == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoRevision-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        ob_end_clean();
        unset ($pdf);
        return $name1;
    }
    $anox = date("Y");
    $mesx = sprintf("%02s", date("m"));
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox, 0777);
        crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox);
    }
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx, 0777);
        crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx);
    }
    $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    ob_end_clean();
    $pdf->Output($name1, "F");
    unset ($pdf);

    /*
     * WSIERRA 2017/09/14 - Incluir firma digital en certificados diferentes de consulta RUES
     */
    if ($certificadoConsultaRues != 'si') {
        $msg = 'Recibo : ' . $recibo . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . '(' . $escajero . ') (' . $esbanco . ') | Matricula : ' . $data["matricula"] . ' | Tipo : CerExi | Hora : ' . date("His");
        $nameLog1 = 'controlFirmaCertificados_' . date("Ymd");
        if (defined('ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS')) {
            if ((ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $escajero != 'SI') ||
                    ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI-TODOS' || (ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $firmar == 'si')
            ) {
                require_once('PDFA.class.php');
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
    }

    ob_end_clean();
    return $name2;
}

/*
 * Certifica de existencia de ESADL, en el siguiente orden:
 * 
 */

function generarCertificadosPdfEsadl($mysqli, $data, $tipo, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ob_clean();
    ob_start();
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');

    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/tcpdf.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/examples/lang/eng.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');

    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }


    $nameLog = 'generarCertificadosPdfEsadl_' . date("Ymd");

    //
    $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli,'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");

    //
    if (!defined('TITULOS_EN_CERTIFICADOS_SII')) {
        define('TITULOS_EN_CERTIFICADOS_SII', 'SI');
    }
    if (!defined('MAYUSCULAS_SOSTENIDAS')) {
        define('MAYUSCULAS_SOSTENIDAS', 'SI');
    }
    $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = TITULOS_EN_CERTIFICADOS_SII;
    $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';
    $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = true;
    if (MAYUSCULAS_SOSTENIDAS == 'NO') {
        $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = false;
    }

    // Arma lista de certificas y su clasificacion
    $_SESSION["generales"]["clasecerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_codigos_certificas', "1=1", "id");
    foreach ($temcerts as $c) {
        $_SESSION["generales"]["clasecerti"][$c["id"]] = $c;
    }
    unset($temcerts);

    // Arma orden del certificado
    $_SESSION["generales"]["ordencerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_orden_certificas', "tipocertificado='CerEsadl'", "orden");
    $i = 0;
    foreach ($temcerts as $c) {
        $i++;
        if (strtoupper($c["estado"]) == 'A') {
            $_SESSION["generales"]["ordencerti"][$i] = $c;
        }
    }
    unset($temcerts);

    //
    // set_error_handler('myErrorHandler');
    ob_clean();

    if (!class_exists('PDFReque')) {

        class PDFReque extends TCPDF {

            public $tamanoLetra = 8;
            public $tituloTipoFirma = '';
            public $tituloTipoHttp = '';
            public $tituloHttpHost = '';
            public $tituloAleatorio = '';
            public $tituloEstadoMatricula = '';
            public $tituloTipo = '';
            public $tituloPathAbsoluto = '';
            public $tituloNombreCamara = '';
            public $tituloCamara = '';
            public $tituloRazonSocial = '';
            public $tituloRecibo = '';
            public $tituloOperacion = '';
            public $tituloEstadoDatos = '';
            public $norenovado = '';
            public $certificadoConsultaRues = 'no';
            public $codcertificaanterior = '';
            public $codigoverificacion = '';
            public $pagina = 0;

            /* Funcion para rotar un txto */

            public function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
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
                    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
                }
            }

            /* Funcion que imprime texto rotado */

            public function RotatedText($x, $y, $txt, $angle = 0) {
                $this->Rotate($angle, $x, $y);
                $this->Text($x, $y, $txt);
                $this->Rotate(0);
            }

            public function Header() {
                $this->SetMargins(10, 40, 7);
                $this->Rect(10, 9, 195, 260);
                if (file_exists($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg')) {
                    $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                }
                // $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 10);

                //
                $this->SetFontSize(8);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tituloNombreCamara . '</strong>', true, false, true, false, 'C');
                // $this->writeHTML('<strong>' . $this->tituloRazonSocial . '</strong>', true, false, true, false, 'C');
                $this->SetXY(35, 15);
                $this->MultiCell(165, 4, $this->tituloRazonSocial . chr(13) . chr(10) . chr(13) . chr(10), 0, 'C', 0);
                $this->SetTextColor(0, 0, 0);
                //
                $txt = '<strong>Fecha expedición: </strong>' . date("Y/m/d") . ' - ' . date("H:i:s");
                if ($this->tituloRecibo != '') {
                    $txt .= ' **** <strong>Recibo No. </strong>' . $this->tituloRecibo;
                }
                if ($this->tituloOperacion != '') {
                    $txt .= ' **** <strong>Num. Operación. </strong>' . $this->tituloOperacion;
                }
                $this->SetFontSize(7);
                $this->writeHTML($txt, true, false, true, false, 'C');

                //
                if ($this->tituloEstadoDatos != '') {
                    if ($this->tituloEstadoDatos == '3') {
                        $txt = '!!! El expediente no se encuentra revisado !!!';
                        $this->SetFontSize(8);
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        if ($this->tituloEstadoDatos != '6') {
                            $txt = '!!! El expediente tiene trámites pendientes de registro o digitación !!!';
                            $this->SetFontSize(8);
                            $this->writeHTML($txt, true, false, true, false, 'C');
                        }
                    }
                }

                if ($this->tituloEstadoMatricula != 'IC') {
                    if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                        $this->SetFontSize(7);
                        $txt = 'LA INSCRIPCIÓN PROPORCIONA SEGURIDAD Y CONFIANZA EN LOS NEGOCIOS<br>';
                        $txt .= 'RENUEVE SU INSCRIPCIÓN A MÁS TARDAR EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"])) . ' Y EVITE SANCIONES DE HASTA 17 S.M.L.M.V';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    }
                } else {
                    $this->Ln();
                    $this->Ln();
                }

                $this->Ln();
                // $this->Ln();
                if ($this->tituloTipo == 'Consulta' || $this->tituloTipo == 'Api') {
                    $this->SetFontSize(8);
                    $txt = '*** SOLO CONSULTA SIN VALIDEZ JURÍDICA ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'Notarias') {
                    $this->SetFontSize(8);
                    $txt = '*** EXPEPIDO A SOLICITUD DE CLIENTES NOTARIALES ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A AFILIADOS ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAdm') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII) ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasOfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A ENTIDAD OFICIAL ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->codigoverificacion != '') {
                    $txt = '<strong>CODIGO DE VERIFICACIÓN ' . $this->codigoverificacion . '</strong>';
                    $this->SetFontSize(8);
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }


                //
                // $this->Ln();
                $y = $this->GetY();
                $this->Line(17, $y, 190, $y);
                // $this->Ln();
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
                } else {
                    if ($this->norenovado == 'si') {
                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(30, 170, '-----------------------------------------------', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(60, 150, 'NO HA CUMPLIDO', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        $this->RotatedText(50, 180, 'CON LA OBLIGACION LEGAL DE', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        $this->RotatedText(70, 180, 'RENOVAR SU INSCRIPCION', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(53, 207, '--------------------------------------------------', 45);
                        $this->SetTextColor(0, 0, 0);
                    }
                }
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    $nameLog = 'certificadosEsadl_' . date("Ymd");

    // ****************************************************************************** //
    // Convierte textos a mayúsculas
    // ****************************************************************************** //    
    if (convertirMayusculas($data["organizacion"], $data["categoria"])) {
        $data["crt1"] = array();
        foreach ($data["crt"] as $id => $text) {
            $data["crt1"][$id] = mb_strtoupper($text, 'UTF-8');
        }
        $data["crt"] = $data["crt1"];
        unset($data["crt1"]);
        $data["crtsii1"] = array();
        foreach ($data["crtsii"] as $id => $text) {
            $data["crtsii1"][$id] = mb_strtoupper($text, 'UTF-8');
        }
        $data["crtsii"] = $data["crtsii1"];
        unset($data["crtsii1"]);
    }

    // ****************************************************************************** //
    // Instanciamiento
    // ****************************************************************************** //
    $pdf = new PDFReque(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }
    $pdf->tituloEstadoMatricula = $data["estadomatricula"];
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    $pdf->tituloRazonSocial = $data["nombre"];
    $pdf->tituloRecibo = $recibo;
    $pdf->tituloOperacion = $operacion;
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;
    $pdf->norenovado = 'no';
    $pdf->codigoverificacion = $aleatorio;
    $pdf->pagina = 0;

    //
    if (
            $data["estadomatricula"] != 'MF' &&
            $data["estadomatricula"] != 'MC' &&
            $data["estadomatricula"] != 'IC' &&
            $data["estadomatricula"] != 'IF'
    ) {
        if ($data["ctrcancelacion1429"] != '3') {

            if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
                if ($data["ultanoren"] == (date("Y") - 1)) {
                    $pdf->norenovado = 'si';
                    if ($data["disueltaporvencimiento"] == 'si') {
                        $pdf->norenovado = 'no';
                    } else {
                        if ($data["disueltaporacto510"] == 'si') {
                            if ($data["fechaacto510"] <= $_SESSION["generales"]["fcorte"]) {
                                $pdf->norenovado = 'no';
                            }
                        } else {
                            if ($data["perdidacalidadcomerciante"] == 'si') {
                                $ano1 = $data["ultanoren"] + 1;
                                $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                                if ($data["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                    $pdf->norenovado = 'no';
                                }
                            }
                        }
                    }
                }
            }
            //

            if ($data["ultanoren"] < (date("Y") - 1)) {

                $pdf->norenovado = 'si';
                
                if ($data["disueltaporvencimiento"] == 'si') {
                    if (($data["fechaacto510"] != '') && $data["fechaacto510"] < $data["fechavencimiento"]) {
                        $data["disueltaporvencimiento"] = 'no';
                    } else {
                        if (substr($data["fechavencimiento"], 4, 4) <= '0331') {
                            $ultanorendis = substr($data["fechavencimiento"], 0, 4) - 1;
                        } else {
                            $ultanorendis = substr($data["fechavencimiento"], 0, 4);
                        }
                        if ($data["ultanoren"] >= $ultanorendis) {
                            $pdf->norenovado = 'no';
                        }
                    }
                }
                if ($data["disueltaporacto510"] == 'si') {
                    // log::general2($nameLogAuditoria, $data["matricula"], __LINE__.' - '.$pdf->norenovado);        
                    if (substr($data["fechaacto510"], 0, 4) == $data["ultanoren"]) {
                        $pdf->norenovado = 'no';
                    } else {
                        //                        
                        $ano1 = $data["ultanoren"] + 1;
                        //$fcorte1 = retornarRegistro('mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                        $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                        $anocorte1 = substr($fcorte1, 0, 4);
                        if ($data["fechaacto510"] <= $fcorte1) {
                            $pdf->norenovado = 'no';
                        } else {
                            //Weymer : 20190515
                            //  log::general2($nameLogAuditoria, $data["matricula"], __LINE__.' - '.$pdf->norenovado);
                            if ((substr($data["fechaacto510"], 0, 4) < '2013') && ($data["ultanoren"] < '2013')) {
                                $pdf->norenovado = 'no';
                                //log::general2($nameLogAuditoria, $data["matricula"], __LINE__.' - '.$pdf->norenovado);
                            }

                            if ($data["perdidacalidadcomerciante"] == 'si') {
                                if ($data["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                                    $pdf->norenovado = 'no';
                                }
                            } else {
                                if ($data["reactivadaacto511"] == 'si') {
                                    if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                                        if ($data["fechaacto511"] >= (date("Y") - 1) . '0101' && $data["fechaacto511"] <= $_SESSION["generales"]["fcorte"]) {
                                            $pdf->norenovado = 'no';
                                        }
                                    } else {
                                        if ($data["fechaacto511"] >= date("Y") . '0101') {
                                            $pdf->norenovado = 'no';
                                        }
                                    }
                                }
                            }
                        }
                        //
                    }
                }
            }
        }
    }


    $pdf->certificadoConsultaRues = $certificadoConsultaRues;

    //
    $tipoFirma = 'FIRMA_SECRETARIO';
    if (!defined('CERTIFICADOS_FIRMA_DIGITAL')) {
        define('CERTIFICADOS_FIRMA_DIGITAL', 'FIRMA_SECRETARIO');
    }
    if (CERTIFICADOS_FIRMA_DIGITAL != '') {
        $tipoFirma = CERTIFICADOS_FIRMA_DIGITAL;
    }

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integrado de Información SII');
    $pdf->SetTitle('Certificados');
    $pdf->SetSubject('Certificados');

    // set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    // require_once('../../includes/tcpdf_6.2.13/examples/lang/eng.php');
    $pdf->setLanguageArray($l);

    // ---------------------------------------------------------
    // set font
    $pdf->SetFont('courier', '', 8);

    //
    $pdf->AddPage();

    // *************************************************************************** //
    // 1. Mensaje si tiene códigos de barras pendientes
    // *************************************************************************** //
    if (!empty($data["lcodigosbarras"])) {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $txt = '<strong>NOS PERMITIMOS INFORMARLE QUE AL MOMENTO DE LA EXPEDICIÓN DE ESTE CERTIFICADO, ';
        $txt .= 'EXISTEN PETICIONES EN TRÁMITE, LO QUE PUEDE AFECTAR EL CONTENIDO ';
        $txt .= 'DE LA INFORMACIÓN QUE CONSTA EN EL MISMO</strong>';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'Imprimio que tiene codigos de barras pendientes');
    }

    // *************************************************************************** //
    // 2. Título del tipo de certificado
    // *************************************************************************** //
    $txt1 = '';
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($data["categoria"] == '1') {
        // $txt1 = 'CERTIFICADO DE EXISTENCIA Y REPRESENTACIÓN LEGAL DE ' . strtoupper(retornarNombreTablaBasica('bas_organizacionjuridica', $data["organizacion"]));
        $txt1 = 'CERTIFICADO DE EXISTENCIA Y REPRESENTACIÓN LEGAL O DE INSCRIPCIÓN DE DOCUMENTOS.';
    }
    if ($data["categoria"] == '2') {
        // $txt1 = 'CERTIFICADO DE EXISTENCIA Y REPRESENTACIÓN LEGAL DE SUCURSAL DE ' . strtoupper(retornarNombreTablaBasica('bas_organizacionjuridica', $data["organizacion"]));
        $txt1 = 'CERTIFICADO DE EXISTENCIA Y REPRESENTACIÓN LEGAL O DE INSCRIPCIÓN DE DOCUMENTOS DE SUCURSAL.';
    }
    $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    if (substr($data["matricula"], 0, 1) != 'S') {
        $pdf->writeHTML('Con fundamento en las matrículas e inscripciones del Registro Mercantil,', true, false, true, false, 'C');
    } else {
        $pdf->writeHTML('Con fundamento en las inscripciones del Registro de Entidades sin Ánimo de Lucro y de la Economía Solidaria,', true, false, true, false, 'C');
    }
    $pdf->Ln();
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'Imprimio titulo certificado');

    // *************************************************************************** //
    // Título de certifica general
    // *************************************************************************** //    
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $txt = '<strong>CERTIFICA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();

    // *************************************************************************** //
    // Si la matrícula se encuentra cancelada
    // *************************************************************************** //
    if (
            $data["estadomatricula"] == 'IC' ||
            $data["estadomatricula"] == 'IF'
    ) {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $txt1 = '**** LA INSCRIPCIÓN EN EL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO SE ENCUENTRA CANCELADA ****';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'Imprimio texto matricula cancelada');
    }



    // *************************************************************************** //
    foreach ($_SESSION["generales"]["ordencerti"] as $c) {
        //
        $incluir = true;
        if (isset($c["endisueltas"])) {
            if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'IC') {
                if ($c["encanceladas"] != 'S') {
                    $incluir = false;
                }
            } else {
                if ($data["disueltaporvencimiento"] == 'si' || $data["disueltaporacto510"] == 'si') {
                    if ($c["endisueltas"] != 'S') {
                        $incluir = false;
                    }
                } else {
                    if ($data["liquidada"] == 'si') {
                        if ($c["enliquidadas"] != 'S') {
                            $incluir = false;
                        }
                    }
                }
            }
        }

        //
        if ($incluir) {
            $con = str_replace("&amp;&amp;", "&&", $c["condicion"]);
            $con = str_replace("&gt;", ">", $con);
            if (trim($con) == '') {
                $incluir = true;
            } else {
                eval("$con");
            }
            if ($incluir) {
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                $rut = $c["rutina"];
                eval("$rut");
            }
        }
    }

    //
    if ($pdf->tituloTipo == 'Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        ob_end_clean();
        return $name1;
    }
    if ($pdf->tituloTipo == 'Api') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        return $name2;
    }
    if ($pdf->tituloTipo == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        ob_end_clean();
        return $name1;
    }
    $anox = date("Y");
    $mesx = sprintf("%02s", date("m"));
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox, 0777);
        crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox);
    }
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx, 0777);
        crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx);
    }
    $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    ob_end_clean();
    $pdf->Output($name1, "F");

    /*
     * WSIERRA 2017/09/14 - Incluir firma digital en certificados diferentes de consulta RUES
     */
    if ($certificadoConsultaRues != 'si') {
        $msg = 'Recibo : ' . $recibo . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . '(' . $escajero . ') (' . $esbanco . ') | Matricula : ' . $data["matricula"] . ' | Tipo : CerExi | Hora : ' . date("His");
        $nameLog1 = 'controlFirmaCertificados_' . date("Ymd");
        if (defined('ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS')) {
            if ((ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $escajero != 'SI') ||
                    ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI-TODOS' || (ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $firmar == 'si')
            ) {
                require_once('PDFA.class.php');
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
    }

    ob_end_clean();
    return $name2;
}

/*
 * Certifica de libros, en el siguiente orden:
 * 
 * - Texto del tipo de certificado
 * - Relación de libros
 */

function generarCertificadosPdfLibros($mysqli, $data, $tipo, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $tipoCertificado = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ob_clean();
    ob_start();
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');

    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/tcpdf.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.2.13/examples/lang/eng.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');

    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }

    $nameLog = 'generarCertificadosPdfLibros_' . date("Ymd");

    //
    if (!defined('TITULOS_EN_CERTIFICADOS_SII')) {
        define('TITULOS_EN_CERTIFICADOS_SII', 'SI');
    }
    if (!defined('MAYUSCULAS_SOSTENIDAS')) {
        define('MAYUSCULAS_SOSTENIDAS', 'SI');
    }
    $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = TITULOS_EN_CERTIFICADOS_SII;
    $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';
    $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = true;
    if (MAYUSCULAS_SOSTENIDAS == 'NO') {
        $_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"] = false;
    }

    // Arma lista de certificas y su clasificacion
    $_SESSION["generales"]["clasecerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_codigos_certificas', "1=1", "id");
    foreach ($temcerts as $c) {
        $_SESSION["generales"]["clasecerti"][$c["id"]] = $c;
    }
    unset($temcerts);

    // Arma orden del certificado
    $_SESSION["generales"]["ordencerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_orden_certificas', "tipocertificado='CerExi'", "orden");
    $i = 0;
    foreach ($temcerts as $c) {
        $i++;
        if (strtoupper($c["estado"]) == 'A') {
            $_SESSION["generales"]["ordencerti"][$i] = $c;
        }
    }
    unset($temcerts);


    //
    // set_error_handler('myErrorHandler');
    ob_clean();

    if (!class_exists('PDFReque')) {

        class PDFReque extends TCPDF {

            public $tamanoLetra = 8;
            public $tituloTipoFirma = '';
            public $tituloTipoHttp = '';
            public $tituloHttpHost = '';
            public $tituloAleatorio = '';
            public $tituloEstadoMatricula = '';
            public $tituloTipo = '';
            public $tituloPathAbsoluto = '';
            public $tituloNombreCamara = '';
            public $tituloCamara = '';
            public $tituloRazonSocial = '';
            public $tituloRecibo = '';
            public $tituloOperacion = '';
            public $tituloEstadoDatos = '';
            public $codcertificaanterior = '';
            public $pagina = 0;

            public function Header() {
                $this->SetMargins(10, 40, 7);
                $this->Rect(10, 9, 195, 260);
                if (file_exists($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg')) {
                    $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                }
                // $this->Image($this->tituloPathAbsoluto . '/images/logocamara' . $this->tituloCamara . '.jpg', 15, 12, 20, 20);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 10);

                //
                $this->SetFontSize(8);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tituloNombreCamara . '</strong>', true, false, true, false, 'C');
                $this->writeHTML('<strong>' . $this->tituloRazonSocial . '</strong>', true, false, true, false, 'C');
                $this->SetTextColor(0, 0, 0);
                //

                $txt = '<strong>Fecha expedición: </strong>' . date("Y/m/d") . ' - ' . date("H:i:s");
                if ($this->tituloRecibo != '') {
                    $txt .= ' **** <strong>Recibo No. </strong>' . $this->tituloRecibo;
                }
                if ($this->tituloOperacion != '') {
                    $txt .= ' **** <strong>Num. Operación. </strong>' . $this->tituloOperacion;
                }
                $this->SetFontSize(7);
                $this->writeHTML($txt, true, false, true, false, 'C');

                //
                if ($this->tituloEstadoDatos != '') {
                    if ($this->tituloEstadoDatos == '3') {
                        $txt = '!!! El expediente no se encuentra revisado !!!';
                        $this->SetFontSize(8);
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        if ($this->tituloEstadoDatos != '6') {
                            $txt = '!!! El expediente tiene trámites pendientes de registro o digitación !!!';
                            $this->SetFontSize(8);
                            $this->writeHTML($txt, true, false, true, false, 'C');
                        }
                    }
                }

                if ($this->tituloEstadoMatricula != 'MF' && $this->tituloEstadoMatricula != 'MC' && $this->tituloEstadoMatricula != 'IC' && $this->tituloEstadoMatricula != 'IA') {
                    if (date("md") <= '0331') {
                        $this->SetFontSize(7);
                        $txt = 'LA MATRÍCULA MERCANTIL PROPORCIONA SEGURIDAD Y CONFIANZA EN LOS NEGOCIOS<br>';
                        $txt .= 'RENUEVE SU MATRÍCULA A MÁS TARDAR EL 31 DE MARZO Y EVITE SANCIONES DE HASTA 17 S.M.L.M.V';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        $this->Ln();
                        $this->Ln();
                    }
                } else {
                    $this->Ln();
                    $this->Ln();
                }
                if ($this->tituloTipo == 'Consulta' || $this->tituloTipo == 'Api') {
                    $this->SetFontSize(8);
                    $txt = '*** SOLO CONSULTA SIN VALIDEZ JURÍDICA ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'Notarias') {
                    $this->SetFontSize(8);
                    $txt = '*** EXPEPIDO A SOLICITUD DE CLIENTES NOTARIALES ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVÉS DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A AFILIADOS ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasAdm') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII) ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }
                if ($this->tituloTipo == 'GasOfi') {
                    $this->SetFontSize(8);
                    $txt = '*** CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A ENTIDAD OFICIAL ***';
                    $this->writeHTML($txt, true, false, true, false, 'C');
                }

                //
                $this->Ln();
                $y = $this->GetY();
                $this->Line(17, $y, 190, $y);
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    // ****************************************************************************** //
    // Instanciamiento
    // ****************************************************************************** //
    $pdf = new PDFReque(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }
    $pdf->tituloEstadoMatricula = $data["estadomatricula"];
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    $pdf->tituloRazonSocial = $data["nombre"];
    $pdf->tituloRecibo = $recibo;
    $pdf->tituloOperacion = $operacion;
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;

    //
    $tipoFirma = 'FIRMA_SECRETARIO';
    if (!defined('CERTIFICADOS_FIRMA_DIGITAL')) {
        define('CERTIFICADOS_FIRMA_DIGITAL', 'FIRMA_SECRETARIO');
    }
    if (CERTIFICADOS_FIRMA_DIGITAL != '') {
        $tipoFirma = CERTIFICADOS_FIRMA_DIGITAL;
    }

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integrado de Información SII');
    $pdf->SetTitle('Certificados');
    $pdf->SetSubject('Certificados');

    // set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    //require_once('../../includes/tcpdf_6.2.13/examples/lang/eng.php');
    $pdf->setLanguageArray($l);

    // ---------------------------------------------------------
    // set font
    $pdf->SetFont('courier', '', 8);

    //
    $pdf->AddPage();

    if ($aleatorio != '') {
        $txt = '<strong>CÓDIGO DE VERIFICACIÓN ' . $aleatorio . '</strong>';
        $pdf->SetFontSize(12);
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetFontSize(8);
    }

    // *************************************************************************** //
    // Mensaje elecciones de juntag directiva
    // *************************************************************************** //    
    armarTextoElecciones($pdf);
    \logApi::general2($nameLog, '', 'armarTextoElecciones');

    // *************************************************************************** //
    // 2017-08-22: JINT: Muestra informa de migración de sistema al SII
    // *************************************************************************** //    
    armarInformaRevisionCertificados($pdf, $data);
    \logApi::general2($nameLog, '', 'armarInformaRevisionCertificados');

    // *************************************************************************** //
    // Título del tipo de certificado
    // *************************************************************************** //
    $txt1 = '';
    $txt1 = 'CERTIFICADO DE LIBROS DE COMERCIO';
    if ($data["organizacion"] == '12') {
        $txt1 = 'CERTIFICADO DE LIBROS DE COMERCIO DE ENTIDADES SIN ÁNIMO DE LUCRO';
    }
    if ($data["organizacion"] == '14') {
        $txt1 = 'CERTIFICADO DE LIBROS DE COMERCIO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
    }
    $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();

    // *************************************************************************** //
    // Si la matrícula se encuentra cancelada
    // *************************************************************************** //
    if ($data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'MC') {
        $txt1 = '**** LA MATRÍCULA MERCANTIL SE ENCUENTRA CANCELADA ****';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
    }
    if ($data["estadomatricula"] == 'IC') {
        $txt1 = '**** LA INSCRIPCIÓN SE ENCUENTRA CANCELADA ****';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
    }

    // *************************************************************************** //
    // Si la matrícula se encuentra inactiva
    // *************************************************************************** //
    if ($data["estadomatricula"] == 'MI') {
        if ($data["organizacion"] != '12' && $data["organizacion"] != '14') {
            $pdf->SetTextColor(139, 0, 0);
            $txt1 = '**** LA MATRÍCULA MERCANTIL SE ENCUENTRA INACTIVA EN LOS TÉRMINOS DEL ARTÍCULO 31 DE LA LEY 1727 DE 2014 ****';
            $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->SetTextColor(0, 0, 0);
        }
        if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
            $pdf->SetTextColor(139, 0, 0);
            $txt1 = '**** LA INSCRIPCIÓN SE ENCUENTRA INACTIVA EN LOS TÉRMINOS DEL ARTÍCULO 31 DE LA LEY 1727 DE 2014 ****';
            $pdf->writeHTML('<strong>' . $txt1 . '</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->SetTextColor(0, 0, 0);
        }
    }

    // *************************************************************************** //
    // Mensaje si tiene códigos de barras pendientes
    // *************************************************************************** //
    if (!empty($data["lcodigosbarras"])) {
        $txt = '<strong>NOS PERMITIMOS INFORMARLE QUE AL MOMENTO DE LA EXPEDICIÓN DE ESTE CERTIFICADO, ';
        $txt .= 'SE ENCUENTRAN EN TRÁMITE SOLICITUDES DE INSCRIPCIÓN QUE PUEDEN AFECTAR EL CONTENIDO ';
        $txt .= 'DEL MISMO</strong>';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
    }

    $txt = 'LA ' . RAZONSOCIAL . ' CON FUNDAMENTO EN LAS MATRÍCULAS E INSCRIPCIONES DEL REGISTRO MERCANTIL,';
    if ($data["organizacion"] == '12') {
        $txt = 'LA ' . RAZONSOCIAL . ' CON FUNDAMENTO EN LAS INSCRIPCIONES DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO,';
    }
    if ($data["organizacion"] == '14') {
        $txt = 'LA ' . RAZONSOCIAL . ' CON FUNDAMENTO EN LAS INSCRIPCIONES DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA,';
    }
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    $pdf->Ln();

    // *************************************************************************** //
    // Certificas
    // *************************************************************************** //
    armarDatosBasicosLibros($pdf, $data);
    \logApi::general2($nameLog, '', 'armarDatosBasicosLibros');

    armarLibros($pdf, $data);
    \logApi::general2($nameLog, '', 'armarLibros');

    // armarCertificaInformacionFormularios($pdf);
    // Recuro de reposición libros
    armarCertificaTextoLibreClase($pdf, $data, 'RR-LIBROS', 'RECURSOS DE REPOSICIÓN A INSCRIPCIONES EN LIBROS', '');
    \logApi::general2($nameLog, '', 'armarCertificaTextoLibreClase');


    if ($certificadoConsultaRues != 'si') {
        armarCertificaFirmeza($pdf);
        \logApi::general2($nameLog, '', 'armarCertificaFirmeza');

        armarTextoValorCertificado($pdf, $tipo, $valorCertificado);
        \logApi::general2($nameLog, '', 'armarTextoValorCertificado');

        armarTextoTipoGasto($pdf, $tipo);
        \logApi::general2($nameLog, '', 'armarTextoTipoGasto');

        armarTextoFirma($pdf, $aleatorio, $tipoFirma);
        \logApi::general2($nameLog, '', 'armarTextoFirma');

        armarTextoFirmaQueEs($pdf, $aleatorio);
        \logApi::general2($nameLog, '', 'armarTextoFirmaQueEs');

        armarTextoFirmaImpresion($pdf, $aleatorio);
        \logApi::general2($nameLog, '', 'armarTextoFirmaImpresion');

        armarTextoFirmaVerificacion($pdf, $aleatorio);
        \logApi::general2($nameLog, '', 'armarTextoFirmaVerificacion');

        armarTextoFirmaMecanica($pdf);
        \logApi::general2($nameLog, '', 'armarTextoFirmaMecanica');

        // *************************************************************************** //
        // Imprime firma mecánica
        // *************************************************************************** //          
        $rutaFirmaMecanica = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/formatos/firmacertificados.png';
        if (file_exists($rutaFirmaMecanica)) {
            $x = $pdf->GetX() + 70;
            $y = $pdf->GetY();
            $pdf->SetY($y);
            $pdf->Image($rutaFirmaMecanica, $x, $y, 50, 30, 'png', '', '', true);
        }

        // *************************************************************************** //
        // FINAL DEL CERTIFICADO
        // *************************************************************************** //              
        $y = $pdf->GetY() + 30;
        $pdf->SetY($y);
        $pdf->Line(17, $y, 190, $y);
        $txt = '<strong>*** FINAL DEL CERTIFICADO ***</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $y = $y + 4;
        $pdf->Line(17, $y, 190, $y);
    }

    //
    if ($pdf->tituloTipo == 'Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        ob_end_clean();
        return $name1;
    }
    if ($pdf->tituloTipo == 'Api') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        return $name2;
    }
    if ($pdf->tituloTipo == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        ob_end_clean();
        return $name1;
    }
    $anox = date("Y");
    $mesx = sprintf("%02s", date("m"));
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox, 0777);
        crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox);
    }
    if (!is_dir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx)) {
        mkdir(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx, 0777);
        crearIndex(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx);
    }
    $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    ob_end_clean();
    $pdf->Output($name1, "F");

    /*
     * WSIERRA 2017/09/14 - Incluir firma digital en certificados diferentes de consulta RUES
     */
    if ($certificadoConsultaRues != 'si') {
        $msg = 'Recibo : ' . $recibo . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . '(' . $escajero . ') (' . $esbanco . ') | Matricula : ' . $data["matricula"] . ' | Tipo : CerLib | Hora : ' . date("His");
        $nameLog1 = 'controlFirmaCertificados_' . date("Ymd");
        if (defined('ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS')) {
            if ((ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $escajero != 'SI') ||
                    ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI-TODOS' || (ACTIVAR_FIRMA_DIGITAL_CERTIFICADOS == 'SI' && $firmar == 'si')
            ) {
                require_once('PDFA.class.php');
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
    }

    ob_end_clean();
    return $name2;
}

// *************************************************************************** //
// Arma certifica de datos básicos
// *************************************************************************** //
function armarInformaRevisionCertificados($pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = '<strong>!!! IMPORTANTE !!!</strong>';

    $txt = 'LA CAMARA DE COMERCIO SE PERMITE INFORMAR QUE POR MOTIVOS DE CAMBIO EN EL SISTEMA ';
    $txt .= 'DE INFORMACION CON EL QUE SE GESTIONAN LOS REGISTROS PUBLICOS QUE ADMINISTRA, LOS EXPEDIENTES SE ENCUENTRAN ';
    $txt .= 'EN PROCESO DE REVISION LEGAL Y PUEDEN PRESENTARSE INCONSISTENCIAS EN EL CONTENIDO DEL CERTIFICADO. ';
    $txt .= 'LE SOLICITAMOS POR FAVOR REVISAR EL CERTIFICADO EXPEDIDO Y EN CASO DE ENCONTRAR ';
    $txt .= 'INFORMACION NO CONGRUENTE REPORTARLO AL CORREO ELECTRONICO ' . EMAIL_ATENCION_USUARIOS . ', INDICANDO ';
    $txt .= 'EL INCONVENIENTE DETECTADO. REVISADA LA INCIDENCIA QUE SE REPORTE, LA CAMARA DE COMERCIO ';
    $txt .= 'LE EXPEDIRÁ UN NUEVO CERTIFICADO QUE LE SERÁ ENVIADO AL CORREO ELECTRONICO DESDE EL CUAL ';
    $txt .= 'SE ENVIA LA SOLICITUD DE REVISION.';
    $txt = '';
    // $pdf->writeHTML($txt, true, false, true, false, 'J');
    // $pdf->Ln();
}

// *************************************************************************** //
// Arma certifica de datos básicos
// *************************************************************************** //
function armarDatosBasicos($pdf, $data, $titulo = '', $imprimirtitulo = 'si', $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($titulo == '') {
        $txt = '<strong>NOMBRE, IDENTIFICACIÓN Y DOMICILIO</strong>';
    } else {
        $txt = '<strong>' . $titulo . '</strong>';
    }
    if ($imprimirtitulo == 'si') {
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    }

    // Razon social o nombre
    $txt = '<strong>';
    if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
        $txt .= 'NOMBRE o RAZÓN SOCIAL: ';
    } else {
        $txt .= 'Nombre o razón social: ';
    }
    $txt .= '</strong>' . $data["nombre"];
    $pdf->writeHTML($txt, true, false, true, false, 'L');

    // Sigla
    if (trim($data["sigla"]) != '') {
        $txt = '<strong>';
        if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
            $txt .= 'SIGLA: ';
        } else {
            $txt .= 'Sigla: ';
        }
        $txt .= '</strong>' . $data["sigla"];
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    }

    // Organización jurídica
    $txt = '<strong>';
    if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
        $txt .= 'ORGANIZACIÓN JURÍDICA: ';
    } else {
        $txt .= 'Organización jurídica: ';
    }
    if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
        $txt .= '</strong>' . strtoupper(retornarRegistroMysqliApi($mysqli, 'bas_organizacionjuridica', "id='" . $data["organizacion"] . "'", "descripcion"));
    } else {
        $txt .= '</strong>' . retornarRegistroMysqliApi($mysqli, 'bas_organizacionjuridica', "id='" . $data["organizacion"] . "'", "descripcion");
    }
    $pdf->writeHTML($txt, true, false, true, false, 'L');

    // Categoría
    if ($data["organizacion"] > '02' && $data["organizacion"] != '08') {
        if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
            switch ($data["categoria"]) {
                case "1":
                    $txt = '<strong>CATEGORÍA : </strong>PERSONA JURÍDICA PRINCIPAL';
                    break;
                case "2":
                    $txt = '<strong>CATEGORÍA : </strong>SUCURSAL';
                    break;
                case "3":
                    $txt = '<strong>CATEGORÍA : </strong>AGENCIA';
                    break;
            }
        } else {
            switch ($data["categoria"]) {
                case "1":
                    $txt = '<strong>Categoría : </strong>Persona jurídica principal';
                    break;
                case "2":
                    $txt = '<strong>Categoría : </strong>Sucursal';
                    break;
                case "3":
                    $txt = '<strong>Categoría : </strong>Agencia';
                    break;
            }
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    }

    // Identificación
    if ($data["organizacion"] == '01') {
        if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
            $txt = '<strong>IDENTIFICACIÓN : </strong>' . strtoupper(retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $data["tipoidentificacion"] . "'", "descripcion") . ' - ' . $data["identificacion"]);
        } else {
            $txt = '<strong>Identificación : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $data["tipoidentificacion"] . "'", "descripcion") . ' - ' . $data["identificacion"];
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    }

    // Certifica el NIT
    if (
            $data["organizacion"] == '01' || ($data["organizacion"] > '02' && $data["categoria"] == '1')
    ) {
        if (ltrim($data["nit"], "0") != '') {
            $sp = \funcionesGenerales::separarDv($data["nit"]);
            if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
                $txt = '<strong>NIT : </strong>' . $sp["identificacion"] . '-' . $sp["dv"];
            } else {
                $txt = '<strong>Nit : </strong>' . $sp["identificacion"] . '-' . $sp["dv"];
            }
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            if (ltrim(trim($data["admondian"]), "0") != '') {
                if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
                    $txt = '<strong>ADMINISTRACIÓN DIAN : </strong>' . strtoupper(retornarRegistroMysqliApi($mysqli, 'bas_admindian', "id='" . $data["admondian"] . "'", "descripcion"));
                } else {
                    $txt = '<strong>Administración DIAN : </strong>' . retornarRegistroMysqliApi($mysqli, 'bas_admindian', "id='" . $data["admondian"] . "'", "descripcion");
                }
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            }
        } else {
            if ($data["estadonit"] == '4' || $data["tipoidentificacion"] == 'V') {
                if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
                    $txt = '<strong>NIT : </strong>' . 'DEBERÁ SER TRAMITADO DIRECTAMENTE ANTE LA DIAN.';
                } else {
                    $txt = '<strong>Nit : </strong>' . 'Deberá ser tramitado directamente ante la DIAN.';
                }
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            } else {
                if (diferenciaEntreFechaBase30Certificados(date("Ymd"), $data["fechamatricula"]) > 2) {
                    if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
                        $txt = '<strong>NIT : </strong>' . 'NO REPORTADO.';
                    } else {
                        $txt = '<strong>Nit : </strong>' . 'No reportado.';
                    }
                } else {
                    if (defined('MAYUSCULAS_SOSTENIDAS') && MAYUSCULAS_SOSTENIDAS == 'SI') {
                        $txt = '<strong>NIT : </strong>' . 'EN TRÁMITE.';
                    } else {
                        $txt = '<strong>Nit : </strong>' . 'En trámite.';
                    }
                }
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            }
        }
    }

    // Domicilio
    if ($_SESSION["generales"]["MAYUSCULAS_SOSTENIDAS"]) {
        $txt = '<strong>DOMICILIO : </strong>' . strtoupper(retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["muncom"] . "'", "ciudad"));
    } else {
        $txt = '<strong>Domicilio : </strong>' . strtolower(retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["muncom"] . "'", "ciudad"));
    }
    $pdf->writeHTML($txt, true, false, true, false, 'L');


    $pdf->Ln();
}

// *************************************************************************** //
// Arma certifica de matricula/inscripcion
// *************************************************************************** //
function armarCertificaMatriculaInscripcion($pdf, $data, $titulo = '', $imprimirtitulo = 'si', $mysqli = null) {
    // Titulo de matrícula e inscripción
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($titulo == '') {
        $txt = '<strong>MATRÍCULA/INSCRIPCIÓN</strong>';
    } else {
        $txt = '<strong>' . $titulo . '</strong>';
    }
    if ($imprimirtitulo == 'si') {
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    }

    //
    $titm = 'MATRÍCULA';
    $titf = 'FECHA DE MATRÍCULA';
    $titr = 'FECHA DE RENOVACION DE LA MATRÍCULA';
    if (substr($data["matricula"], 0, 1) == 'S') {
        $titm = 'INSCRIPCIÓN';
        $titf = 'FECHA DE INSCRIPCIÓN';
        $titr = 'FECHA DE RENOVACION DE LA INSCRIPCIÓN';
    }
    if ($data["organizacion"] == '10' && $data["categoria"] == '1') {
        $titm = 'EXPEDIENTE';
        $titf = 'FECHA DE INSCRIPCION';
        $titr = 'FECHA DE RENOVACION DE LA INSCRIPCIÓN';
    }
    $txt = '<strong>' . $titm . ' NO : </strong>' . $data["matricula"] . '<br>';
    $txt .= '<strong>' . $titf . ' : </strong>' . strtoupper(\funcionesGenerales::mostrarFechaLetras($data["fechamatricula"])) . '<br>';
    $txt .= '<strong>ULTIMO AÑO RENOVADO  : </strong>' . $data["ultanoren"] . '<br>';
    $txt .= '<strong>' . $titr . ' : </strong>' . strtoupper(\funcionesGenerales::mostrarFechaLetras($data["fecharenovacion"])) . '<br>';
    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && $data["categoria"] == '1')) {
        $txt .= '<strong>ACTIVO TOTAL  : </strong>' . number_format($data["acttot"], 2) . '<br>';
        if (trim($data["gruponiif"]) != '') {
            if (trim($data["gruponiif"]) != '0') {
                $txt .= '<strong>GRUPO NIIF  : </strong>' . substr(retornarRegistroMysqliApi($mysqli, 'bas_gruponiif', "id='" . $data["gruponiif"] . "'", "descripcion"), 4) . '<br>';
            } else {
                $txt .= '<strong>GRUPO NIIF  : </strong>' . retornarRegistroMysqliApi($mysqli, 'bas_gruponiif', "id='" . $data["gruponiif"] . "'", "descripcion") . '<br>';
            }
        }
    } else {
        $txt .= '<strong>ACTIVO VINCULADO  : </strong>' . number_format($data["actvin"], 2) . '<br>';
    }

    $pdf->writeHTML($txt, true, false, true, false, 'L');
    $pdf->Ln();
}

function armarUbicacionDatosGenerales($pdf, $data, $titulo = '', $imprimirtitulo = 'si', $mysqli = null) {

    // Titulo de matrícula e inscripción
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($titulo == '') {
        $txt = '<strong>UBICACION Y DATOS GENERALES</strong>';
    } else {
        $txt = '<strong>' . $titulo . '</strong>';
    }
    if ($imprimirtitulo == 'si') {
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    }

    $txt = '<strong>DIRECCIÓN DEL DOMICILIO PRINCIPAL  : </strong>' . $data["dircom"] . '<br>';
    if (trim($data["barriocom"]) != '') {
        $txt .= '<strong>BARRIO  : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $data["muncom"] . "' and idbarrio='" . $data["barriocom"] . "'", "nombre") . '<br>';
    }
    $txt .= '<strong>MUNICIPIO  / DOMICILIO: </strong>' . $data["muncom"] . ' - ' . retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["muncom"] . "'", "ciudad") . '<br>';
    if (trim($data["telcom1"]) != '') {
        $txt .= '<strong>TELÉFONO COMERCIAL 1  : </strong>' . $data["telcom1"] . '<br>';
    } else {
        $txt .= '<strong>TELÉFONO COMERCIAL 1  : </strong>NO REPORTÓ<br>';
    }
    if (trim($data["telcom2"]) != '') {
        $txt .= '<strong>TELÉFONO COMERCIAL 2  : </strong>' . $data["telcom2"] . '<br>';
    } else {
        $txt .= '<strong>TELÉFONO COMERCIAL 2  : </strong>NO REPORTÓ<br>';
    }
    if (trim($data["celcom"]) != '') {
        $txt .= '<strong>TELÉFONO COMERCIAL 3  : </strong>' . $data["celcom"] . '<br>';
    } else {
        $txt .= '<strong>TELÉFONO COMERCIAL 3  : </strong>NO REPORTÓ<br>';
    }
    if (trim($data["emailcom"]) != '') {
        $txt .= '<strong>CORREO ELECTRÓNICO No. 1 : </strong>' . $data["emailcom"] . '<br>';
    }
    if (trim($data["emailcom2"]) != '') {
        $txt .= '<strong>CORREO ELECTRÓNICO No. 2 : </strong>' . $data["emailcom2"] . '<br>';
    }
    if (trim($data["emailcom3"]) != '') {
        $txt .= '<strong>CORREO ELECTRÓNICO No. 3 : </strong>' . $data["emailcom3"] . '<br>';
    }

    if (trim($data["urlcom"]) != '') {
        $txt .= '<strong>SITIO WEB  : </strong>' . $data["urlcom"];
    }

    $pdf->writeHTML($txt, true, false, true, false, 'L');
    $pdf->Ln();

    if ($data["organizacion"] != '02' && $data["categoria"] != '3') {
        if (
                trim($data["dirnot"]) != '' ||
                trim($data["munnot"]) != '' ||
                trim($data["telnot"]) != '' ||
                trim($data["telnot2"]) != '' ||
                trim($data["celnot"]) != '' ||
                trim($data["emailnot"]) != ''
        ) {

            $txt = '<strong>DIRECCIÓN PARA NOTIFICACIÓN JUDICIAL : </strong>' . $data["dirnot"] . '<br>';
            $txt .= '<strong>MUNICIPIO  : </strong>' . $data["munnot"] . ' - ' . retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["munnot"] . "'", "ciudad") . '<br>';
            if (trim($data["barrionot"]) != '') {
                $txt .= '<strong>BARRIO  : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $data["munnot"] . "' and idbarrio='" . $data["barrionot"] . "'", "nombre") . '<br>';
            }

            if (trim($data["telnot"]) != '') {
                $txt .= '<strong>TELÉFONO 1  : </strong>' . $data["telnot"] . '<br>';
            }
            if (trim($data["telnot2"]) != '') {
                if ($data["telnot2"] != $data["telnot"] && $data["telnot2"] != $data["celnot"]) {
                    $txt .= '<strong>TELÉFONO 2  : </strong>' . $data["telnot2"] . '<br>';
                }
            }
            if (trim($data["celnot"]) != '') {
                if ($data["celnot"] != $data["telnot"] && $data["celnot"] != $data["telnot2"]) {
                    $txt .= '<strong>TELÉFONO 3  : </strong>' . $data["celnot"] . '<br>';
                }
            }
            if (trim($data["emailnot"]) != '') {
                $txt .= '<strong>CORREO ELECTRÓNICO  : </strong>' . $data["emailnot"];
            }
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $pdf->Ln();
        }
    }

    // 2018-03-27 : JINT: Se incluye para pruebas por solicitud del área jurídica de confecámaras
    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && $data["categoria"] == '1')) {
        // if ($data["fechamatricula"] > '20170831') {
        if ($data["ctrmennot"] != '') {
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            $txt = '<strong>NOTIFICACIONES A TRAVÉS DE CORREO ELECTRÓNICO</strong>';
            if ($imprimirtitulo == 'si') {
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }

            $txt = 'De acuerdo con lo establecido en el artículo 67 del Código de Procedimiento Administrativo y de lo Contencioso Administrativo, ';
            if (substr($data["ctrmennot"], 0, 1) == 'S' || substr($data["ctrmennot"], 0, 1) == 's') {
                $txt .= '<strong>SI AUTORIZO</strong> para que me notifiquen personalmente a través del correo electrónico de notificación : ';
                $txt .= $data["emailnot"];
            } else {
                $txt .= '<strong>NO AUTORIZO</strong> para que me notifiquen personalmente a través del correo electrónico de notificación.';
            }
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $pdf->Ln();
        }
        // }
    }
}

// *************************************************************************** //
// Arma certifica de datos básicos
// *************************************************************************** //
function armarDatosBasicosLibros($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
        $txt = '<strong>CERTIFICA - DATOS BÁSICOS</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    } else {
        $txt = '<strong>CERTIFICA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    }

    $txt = '<strong>NOMBRE o RAZÓN SOCIAL: </strong>' . $data["nombre"];
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    if (trim($data["sigla"]) != '') {
        $txt = '<strong>SIGLA : </strong>' . $data["sigla"];
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    }
    $txt = '<strong>ORGANIZACION JURIDICA: </strong>' . strtoupper(retornarRegistroMysqliApi($mysqli, 'bas_organizacionjuridica', "id='" . $data["organizacion"] . "'", "descripcion"));
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    if ($data["organizacion"] > '02') {
        switch ($data["categoria"]) {
            case "1":
                $txt = '<strong>CATEGORIA : </strong>PERSONA JURIDICA PRINCIPAL';
                break;
            case "2":
                $txt = '<strong>CATEGORIA : </strong>SUCURSAL';
                break;
            case "3":
                $txt = '<strong>CATEGORIA : </strong>AGENCIA';
                break;
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    }
    if ($data["organizacion"] == '01') {
        $txt = '<strong>IDENTIFICACIÓN : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $data["tipoidentificacion"] . "'", "descripcion") . ' - ' . $data["identificacion"];
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    }
    if (ltrim($data["nit"], "0") != '') {
        $sp = \funcionesGenerales::separarDv($data["nit"]);
        $txt = '<strong>NIT : </strong>' . $sp["identificacion"] . '-' . $sp["dv"];
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    }
    $txt = '<strong>MATRÍCULA NO : </strong>' . $data["matricula"] . '<br>';
    if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
        $txt = '<strong>INSCRIPCION NO : </strong>' . $data["matricula"] . '<br>';
    }
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    $pdf->Ln();
}

// *************************************************************************** //
// Arma libros de comercio
// *************************************************************************** //
function armarLibros($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = '<strong>CERTIFICA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();

    $txt1 = '';
    $tieneLibros = 'NO';
    $data["inscripciones"] = \funcionesGenerales::ordenarMatriz($data["inscripciones"], "freg");
    foreach ($data["inscripciones"] as $ix) {
        if (
                $ix["grupoacto"] == '004' || $ix["grupoacto"] == '059' || ($ix["lib"] == 'RM07' || $ix["lib"] == 'RE52') && (ltrim(trim($ix["acto"]), "0") == '')
        ) {
            $txt2 = 'NO DEFINIDO';
            if (trim($ix["deslib"]) != '') {
                $txt2 = trim($ix["deslib"]);
            } else {
                if (ltrim(trim($ix["idlibvii"]), "0")) {
                    $txt2 = retornarRegistroMysqli2($mysqli, "mreg_tablassirep", "idtabla='09' and idcodigo='" . $ix["idlibvii"] . "'", "descripcion");
                }
                if (trim($txt2) == '') {
                    if (substr($ix["lib"], 0, 2) == 'RE') {
                        if (substr($ix["not"], 0, 8) == 'PAGINAS:') {
                            $ix["numhojas"] = ltrim(trim(substr($ix["not"], 8, 4)));
                            $txt2 = rtrim(trim(substr($ix["not"], 12)), "-");
                        } else {
                            $txt2 = rtrim(trim($ix["not"]), "-");
                        }
                    } else {
                        $txt2 = substr($ix["not"], 42);
                        if ($txt2 == '') {
                            $txt2 = trim($ix["not"]);
                        }
                    }
                }
            }
            $tieneLibros = 'SI';
            $txt1 .= '<tr>';
            $txt1 .= '<td width="60%">' . $txt2 . '</td>';
            if (trim($ix["lib"]) == 'RE51') {
                $ix["lib"] = 'RE01';
            }
            if (trim($ix["lib"]) == 'RE52') {
                $ix["lib"] = 'RE02';
            }
            if (trim($ix["lib"]) == 'RE53') {
                $ix["lib"] = 'RE03';
            }
            if (trim($ix["lib"]) == 'RE54') {
                $ix["lib"] = 'RE04';
            }
            if (trim($ix["lib"]) == 'RE55') {
                $ix["lib"] = 'RE05';
            }
            $txt1 .= '<td width="14%">' . $ix["lib"] . '-' . $ix["nreg"] . '</td>';
            $txt1 .= '<td width="13%">' . \funcionesGenerales::mostrarFecha($ix["freg"]) . '</td>';
            $txt1 .= '<td width="13%">' . number_format($ix["numhojas"], 0) . '</td>';
            $txt1 .= '</tr>';
        }
    }
    if ($tieneLibros == 'NO') {
        $txt = 'QUE A LA FECHA DE EXPEDICION DE ESTE CERTIFICADO NO SE ENCUENTRA ';
        $txt .= 'INSCRITO EN LOS REGISTROS PUBLICOS QUE ADMINISTRA LA CAMARA DE COMERCIO ';
        $txt .= 'NINGUN LIBRO DE COMERCIO A NOMBRE DEL ';
        if ($data["organizacion"] != '12' && $data["organizacion"] != '14') {
            $txt .= 'MATRICULADO.';
        } else {
            $txt .= 'INSCRITO.';
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
    } else {
        $txt = 'QUE A SU NOMBRE FIGURAN INSCRITOS LOS SIGUIENTES LIBROS DE COMERCIO: ';
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
        $txt = '<table>';
        $txt .= '<tr>';
        $txt .= '<td width="60%">DESCRIPCION</td>';
        $txt .= '<td width="14%">REGISTRO</td>';
        $txt .= '<td width="13%">FECHA</td>';
        $txt .= '<td width="13%"># HOJAS</td>';
        $txt .= '</tr>';
        $txt .= $txt1;
        $txt .= '</table>';
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
    }
}

// *************************************************************************** //
// Certifica datos de notificacion judicial
// *************************************************************************** //
function armarDireccionNotificacion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["organizacion"] != '02') {
        if (
                trim($data["dirnot"]) != '' ||
                trim($data["munnot"]) != '' ||
                trim($data["telnot"]) != '' ||
                trim($data["telnot2"]) != '' ||
                trim($data["celnot"]) != '' ||
                trim($data["emailnot"]) != ''
        ) {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - DATOS DE NOTIFICACIÓN JUDICIAL</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }

            $txt = '<strong>DIRECCIÓN DE NOTIFICACIÓN JUDICIAL : </strong>' . $data["dirnot"] . '<br>';
            $txt .= '<strong>MUNICIPIO  : </strong>' . $data["munnot"] . ' - ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $data["munnot"] . "'", "ciudad") . '<br>';
            if (trim($data["barrionot"]) != '') {
                $txt .= '<strong>BARRIO  : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $data["munnot"] . "' and idbarrio='" . $data["barrionot"] . "'", "nombre") . '<br>';
            }

            if (trim($data["telnot"]) != '') {
                $txt .= '<strong>TELÉFONO 1  : </strong>' . $data["telnot"] . '<br>';
            }
            if (trim($data["telnot2"]) != '') {
                if ($data["telnot2"] != $data["telnot"] && $data["telnot2"] != $data["celnot"]) {
                    $txt .= '<strong>TELÉFONO 2  : </strong>' . $data["telnot2"] . '<br>';
                }
            }
            if (trim($data["celnot"]) != '') {
                if ($data["celnot"] != $data["telnot"] && $data["celnot"] != $data["telnot2"]) {
                    $txt .= '<strong>TELÉFONO 3  : </strong>' . $data["celnot"] . '<br>';
                }
            }
            if (trim($data["emailnot"]) != '') {
                $txt .= '<strong>CORREO ELECTRÓNICO  : </strong>' . $data["emailnot"];
            }
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $pdf->Ln();
        }
    }
}

// *************************************************************************** //
// Arma certifica de afiliación
// *************************************************************************** //
function armarCertificaAfiliacion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["afiliado"] == '1') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - AFILIACIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'EL COMERCIANTE ES UN AFILIADO DE ACUERDO CON LOS TÉRMINOS ESTABLECIDOS EN EL ARTÍCULO 12 DE LA LEY 1727 DE 2014.';
        $pdf->writeHTML('<strong>' . $txt . '</strong>', true, false, true, false, 'C');
        $pdf->Ln();
    }
}

// *************************************************************************** //
// Certifica Administrador
// *************************************************************************** //
function armarCertificaAdministrador($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {
        $tieneadministrador = 0;
        foreach ($data["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'ADMP' || $v["tipovinculo"] == 'ADMS1' || $v["tipovinculo"] == 'ADMS2') {
                $tieneadministrador++;
            }
        }
        if ($tieneadministrador > 0) {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - ADMINISTRACIÓN</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txt = 'QUE EL BIEN SE ENCUENTRA ADMINISTRADO POR LA(S) SIGUIENTE(S) PERSONA(S) : ';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $pdf->Ln();
            foreach ($data["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'ADMP' || $v["tipovinculo"] == 'ADMS1' || $v["tipovinculo"] == 'ADMS2') {
                    // if ($v["vinculootros"] == '2600' || $v["vinculootros"] == '2601' || $v["vinculootros"] == '2602') {
                    $txt = '<strong>*** NOMBRE : </strong>' . $v["nombreotros"] . '<br>';
                    $txt .= '<strong>IDENTIFICACION : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion") . ' - ' . $v["identificacionotros"] . '<br>';
                    $txt .= '<strong>VINCULACION : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $v["vinculootros"] . "'", "descripcion") . '<br>';
                    $txt .= '<strong>FECHA DE REGISTRO DE LA VINCULACION : </strong>' . strtoupper(\funcionesGenerales::mostrarFechaLetras($v["fechaotros"])) . '<br>';
                    $txt .= '<strong>LIBRO Y NÚMERO DE INSCRIPCIÓN : </strong>' . $v["librootros"] . ' - ' . $v["inscripcionotros"] . '<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'L');
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Arrendatario
// *************************************************************************** //
function armarCertificaArrendatario($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {
        $tienearrendatario = 0;
        foreach ($data["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'ARR') {
                $tienearrendatario++;
            }
        }
        if ($tienearrendatario > 0) {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - ARRENDATARIO</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txt = 'QUE EL BIEN SE ENCUENTRA DADO EN CALIDAD DE ARRENDAMIENTO A : ';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $pdf->Ln();
            foreach ($data["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'ARR') {
                    // if ($v["vinculootros"] == '2690') {
                    $txt = '<strong>*** NOMBRE : </strong>' . $v["nombreotros"] . '<br>';
                    $txt .= '<strong>IDENTIFICACION : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion") . ' - ' . $v["identificacionotros"] . '<br>';
                    $txt .= '<strong>VINCULACION : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $v["vinculootros"] . "'", "descripcion") . '<br>';
                    $txt .= '<strong>FECHA DE REGISTRO DEL CONTRATO : </strong>' . strtoupper(\funcionesGenerales::mostrarFechaLetras($v["fechaotros"])) . '<br>';
                    $txt .= '<strong>LIBRO Y NÚMERO DE INSCRIPCIÓN : </strong>' . $v["librootros"] . ' - ' . $v["inscripcionotros"] . '<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'L');
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica datos de la ultima renovación
// *************************************************************************** //
function armarCertificaRenovacion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if (
            $data["estadomatricula"] != 'MF' &&
            $data["estadomatricula"] != 'MC' &&
            $data["estadomatricula"] != 'IC'
    ) {

        if ($pdf->norenovado == 'si') {
            if ($data["disueltaporvencimiento"] != 'si' && $data["disueltaporacto510"] != 'si') {
                $pdf->SetTextColor(139, 0, 0);
                if ($data["organizacion"] == '01') {
                    $txt = '<strong>EL COMERCIANTE NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL</strong>';
                } else {
                    if ($data["organizacion"] == '02') {
                        $txt = '<strong>EL ESTABLECIMIENTO DE COMERCIO NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL</strong>';
                    } else {
                        $txt = '<strong>EL COMERCIANTE NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL</strong>';
                        if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                            if ($data["categoria"] == '1') {
                                $txt = '<strong>LA ENTIDAD NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU INSCRIPCION</strong>';
                            }
                        }
                        if ($data["categoria"] == '2') {
                            $txt = '<strong>LA SUCURSAL NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL</strong>';
                        }
                        if ($data["categoria"] == '3') {
                            $txt = '<strong>LA AGENCIA NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL</strong>';
                        }
                    }
                }
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->SetTextColor(0, 0, 0);
            }
        }
        if ($data["disueltaporacto510"] == 'si') {
            if (trim($data["fechaacto510"]) != '') {
                if ($data["ultanoren"] < substr($data["fechaacto510"], 0, 4)) {
                    $pdf->SetFont('courier', 'B', 8);
                    $pdf->SetTextColor(139, 0, 0);
                    $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN. SIN EMBARGO DEBEN RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN HASTA EL AÑO EN QUE FUE DISUELTA.</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->SetFont('courier', '', 8);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->Ln();
                } else {
                    $pdf->SetFont('courier', 'B', 8);
                    $pdf->SetTextColor(139, 0, 0);
                    $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN.</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->SetFont('courier', '', 8);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->Ln();
                }
            } else {
                $pdf->SetFont('courier', 'B', 8);
                $pdf->SetTextColor(139, 0, 0);
                $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN.</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->SetFont('courier', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Ln();
            }
        } else {
            if ($data["disueltaporvencimiento"] == 'si') {
                if (trim($data["fechavencimiento"]) != '') {
                    if ($data["ultanoren"] < substr($data["fechavencimiento"], 0, 4)) {
                        $pdf->SetFont('courier', 'B', 8);
                        $pdf->SetTextColor(139, 0, 0);
                        $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN. SIN EMBARGO DEBEN RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN HASTA EL AÑO EN QUE FUE DISUELTA.</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->SetFont('courier', '', 8);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->Ln();
                    } else {
                        $pdf->SetFont('courier', 'B', 8);
                        $pdf->SetTextColor(139, 0, 0);
                        $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN.</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->SetFont('courier', '', 8);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->Ln();
                    }
                } else {
                    $pdf->SetFont('courier', 'B', 8);
                    $pdf->SetTextColor(139, 0, 0);
                    $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN.</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->SetFont('courier', '', 8);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->Ln();
                }
            } else {
                if ($data["perdidacalidadcomerciante"] == 'si') {
                    if (trim($data["fechaperdidacalidadcomerciante"]) != '') {
                        if ($data["ultanoren"] < substr($data["fechaperdidacalidadcomerciante"], 0, 4)) {
                            $pdf->SetFont('courier', 'B', 8);
                            $pdf->SetTextColor(139, 0, 0);
                            $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN. SIN EMBARGO DEBEN RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN HASTA EL AÑO EN QUE FUE DISUELTA.</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->SetFont('courier', '', 8);
                            $pdf->SetTextColor(0, 0, 0);
                            $pdf->Ln();
                        } else {
                            $pdf->SetFont('courier', 'B', 8);
                            $pdf->SetTextColor(139, 0, 0);
                            $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN.</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->SetFont('courier', '', 8);
                            $pdf->SetTextColor(0, 0, 0);
                            $pdf->Ln();
                        }
                    } else {
                        $pdf->SetFont('courier', 'B', 8);
                        $pdf->SetTextColor(139, 0, 0);
                        $txt = '<strong>EN CUMPLIMIENTO DE LO SEÑALADO EN EL INCISO SEGUNDO DEL ARTÍCULO 31 DE LA LEY 1429 DE 2010, LAS PERSONAS JURÍDICAS QUE SE ENCUENTREN DISUELTAS Y EN ESTADO DE LIQUIDACIÓN NO TIENEN LA OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL O INSCRIPCIÓN, DESDE LA FECHA EN QUE SE INICIO EL PROCESO DE LIQUIDACIÓN.</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->SetFont('courier', '', 8);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// *************************************************************************** //
// Es pequeña empresa en los términos de la Ley 1429 de 2010
// *************************************************************************** //
function armarCertificaPequenaEmpresa($pdf, $data, $mysqli = null) {

    //
    return false;

    //
    if (
            $data["organizacion"] === '02' ||
            $data["organizacion"] === '12' ||
            $data["organizacion"] === '14' ||
            $data["categoria"] === '2' || $data["organizacion"] === '3'
    ) {
        return false;
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["categoria"] == '2' || $data["categoria"] == '3') {
        return true;
    }
    if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
        return true;
    }

    $mipyme = 'no';
    if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {
        if (substr(strtoupper($data["art7"]), 0, 1) == 'S') {
            $mipyme = 'si';
        }
    }

    // Valida tamaño
    if ($mipyme == 'si') {
        $mipyme = verificarTamanoMipyme($mysqli, $data);
    }

    // Valida renovación
    if ($mipyme == 'si') {
        $mipyme = verificarRenovacionMipyme($mysqli, $data);
    }

    if ($mipyme == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CONDICIÓN PEQUEÑA EMPRESA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'QUE EL MATRICULADO TIENE LA CONDICIÓN DE PEQUEÑA EMPRESA DE ACUERDO CON LO ESTABLECIDO ';
        $txt .= 'EN EL NUMERAL 1 DEL ARTÍCULO 2 DE LA LEY 1429 DE 2010.';
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    } else {
        $mipyme = 'si';
        $mipyme = verificarTamanoMipyme($mysqli, $data);
        if ($mipyme == 'si') {
            $mipyme = verificarRenovacionMipyme($mysqli, $data);
        }
        if ($mipyme == 'si') {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - CONDICIÓN DE PEQUEÑA EMPRESA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txt = 'QUE EL MATRICULADO TIENE LA CONDICIÓN DE PEQUEÑA EMPRESA.';
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
        }
    }
}

// *************************************************************************** //
// Es pequeña empresa JOVEN en los términos de la Ley 1780 DE 2016
// *************************************************************************** //
function armarCertificaPequenaEmpresaJoven($pdf, $data, $mysqli = null) {

    if (
            $data["organizacion"] == '02' ||
            $data["organizacion"] == '12' ||
            $data["organizacion"] == '14' ||
            $data["categoria"] == '2' || $data["organizacion"] == '3'
    ) {
        return false;
    }

    // 2018-01-31: JINT : Se adiciona control para que no genere el mensaje de
    // pequeña empresa joven si han pasado más de dos años desde la matrícula
    $anox = substr($data["fechamatricula"], 0, 4);
    if ((date("Y") - $anox + 1) > 2) {
        return false;
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $mipyme = 'no';
    if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {
        if (substr(strtoupper($data["benley1780"]), 0, 1) == 'S') {
            $mipyme = 'si';
        }
    }

    // Valida tamaño
    if ($mipyme == 'si') {
        $mipyme = verificarTamanoMipyme($mysqli, $data);
    }

    // Valida renovación
    if ($mipyme == 'si') {
        $mipyme = verificarRenovacionMipyme($mysqli, $data);
    }

    if ($mipyme == 'si') {
        if (date("Y") > substr($data["fechamatricula"], 0, 4)) {
            if (
                    $data["cumplerequisitos1780"] == 'N' ||
                    $data["cumplerequisitos1780primren"] == 'N' ||
                    $data["renunciabeneficios1780"] == 'S'
            ) {
                $mipyme = 'no';
            }
        } else {
            if (
                    $data["cumplerequisitos1780"] == 'N' ||
                    $data["renunciabeneficios1780"] == 'S'
            ) {
                $mipyme = 'no';
            }
        }
    }

    if ($mipyme == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - PEQUEÑA EMPRESA JOVEN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'QUE EL MATRICULADO TIENE LA CONDICIÓN DE PEQUEÑA EMPRESA JOVEN DE ACUERDO CON LO ESTABLECIDO ';
        $txt .= 'EN EL ARTÍCULO 2 DE LA LEY 1780 DE 2016.';
        $pdf->writeHTML('<strong>' . $txt . '</strong>', true, false, true, false, 'J');
        $pdf->Ln();
    }
}

function verificarTamanoMipyme($mysqli = null, $data = array()) {
    if ($data["fechamatricula"] == $data["fecharenovacion"]) {
        $fec = $data["fechamatricula"];
    } else {
        $fec = $data["fecharenovacion"];
    }
    $salmin = \funcionesGenerales::localizarSmmlv($fec, $mysqli);
    if ($salmin == 0) {
        return 'no';
    } else {
        if ($data["acttot"] / $salmin <= 5000 && $data["personal"] <= 50) {
            return 'si';
        } else {
            return 'no';
        }
    }
}

function verificarRenovacionMipyme($mysqli = null, $data = array()) {

    // Si se matriculo el mismo año del certificado
    if ($data["fechamatricula"] < '20101229') {
        return 'no';
    }

    if ($data["fechamatricula"] > '20191229') {
        return 'no';
    }

    //
    $mipyme = 'no';

    // Si se matriculo el mismo año del certificado
    if (substr($data["fechamatricula"], 0, 4) == date("Y")) {
        return 'si';
    } else {
        if (date("md") > '0331') {
            if ($data["ultanoren"] == date("Y")) {
                if ($data["fecharenovacion"] <= date("Y") . '0331') {
                    $mipyme = 'si';
                }
            }
        } else {
            $mipyme = 'si';
        }
        return $mipyme;
    }
}

// *************************************************************************** //
// Certifica de Personerìa Jurídica
// *************************************************************************** //
function armarCertificaPersoneriaJuridica($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienepersoneria = 'no';
    if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
        if ($data["categoria"] == '1') {
            if (ltrim($data["numperj"], "0") != '') {
                $tienepersoneria = 'si';
            }
        }
    }
    if ($tienepersoneria == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - PERSONERÍA JURIDICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        if ($data["organizacion"] == '12') {
            $txt = 'QUE LA ENTIDAD SIN ÁNIMO DE LUCRO OBTUVO SU PERSONERÍA JURÍDICA EL ';
        }
        if ($data["organizacion"] == '14') {
            $txt = 'QUE LA ENTIDAD DE LA ECONOMÍA SOLIDARIA OBTUVO SU PERSONERÍA JURÍDICA EL ';
        }
        $txt .= strtoupper(\funcionesGenerales::mostrarFechaLetras1($data["fecperj"])) . ' BAJO EL NÚMERO ' . $data["numperj"] . ' ';
        if (ltrim(trim($data["idorigenperj"]), "0") != '') {
            if (strlen(trim($data["idorigenperj"])) == 5) {
                $txt .= 'OTORGADA POR ' . retornarRegistroMysqliApi($mysqli, "mreg_tablassirep", "idtabla='43' and idcodigo='" . $data["idorigenperj"] . "'", "descripcion");
            } else {
                if (trim($data["idorigenperj"]) != '') {
                    $txt .= 'OTORGADA POR ' . $data["idorigenperj"];
                } else {
                    if (trim($data["origendocconst"]) != '') {
                        $txt .= 'OTORGADA POR ' . $data["origendocconst"];
                    }
                }
            }
        } else {
            if (trim($data["origendocconst"]) != '') {
                $txt .= 'OTORGADA POR ' . $data["origendocconst"];
            }
        }
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }
}

function armarCertificaConstitucion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneconstitucion = 'no';
    $tienereconstitucion = 'no';
    $tieneAclaratoria = 'no';
    foreach ($data["inscripciones"] as $i) {

        // Grupo de actos de constitución
        if ($i["grupoacto"] == '005') {
            if ($i["lib"] != 'RM15') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $tieneconstitucion = 'si';
                }
            }
        }

        // Grupo de actos reconstitucion
        if ($i["grupoacto"] == '061') {
            if ($i["lib"] != 'RM15') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $tienereconstitucion = 'si';
                }
            }
        }

        // Grupo de actos aclaratoria a la constitución
        if ($i["grupoacto"] == '060') {
            $tieneAclaratoria = 'si';
        }
    }

    // *************************************************************************** //
    // Certifica Constitución
    // *************************************************************************** //
    if ($tieneconstitucion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CONSTITUCIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '005') {
                if ($i["lib"] != 'RM15') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '.</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }

    // *************************************************************************** //
    // Certifica Constitución - Aclaratoria a la constitución
    // *************************************************************************** //

    if ($tieneAclaratoria == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - ACLARATORIAS A LA CONSTITUCIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '060') {
                $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '.</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
    }

    // *************************************************************************** //
    // Certifica re-Constitución - Reconstitucion
    // *************************************************************************** //
    if ($tienereconstitucion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - RECONSTITUCION</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '061') {
                if ($i["lib"] != 'RM15') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '.</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

function armarCertificaGrupoActos($pdf, $data, $grupo, $titulo, $titulos = 'si', $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    if (is_array($grupo)) {
        $grupos = $grupo;
    } else {
        $grupos = array();
        $grupos[] = $grupo;
    }

    //
    $tienegrupo = 'no';

    //
    foreach ($data["inscripciones"] as $i) {
        if (in_array($i["grupoacto"], $grupos)) {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tienegrupo = 'si';
            }
        }
    }

    // *************************************************************************** //
    // Certifica los grupos seleccionados
    // *************************************************************************** //
    if ($tienegrupo == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>' . $titulo . '</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if (in_array($i["grupoacto"], $grupos)) {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '.</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
    if ($tienegrupo == 'si') {
        return true;
    } else {
        return false;
    }
}

// *************************************************************************** //
// Certifica Constitución - Certifica 0050
// *************************************************************************** //
function armarCertificaConstitucionCasaPrincipal($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $conscasaprincipal = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '062') {
            // if ($i["acto"] == '0030') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $conscasaprincipal = 'si';
            }
        }
    }

    //
    if ($conscasaprincipal == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CONSTITUCIÓN CASA PRINCIPAL</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '062') {
                // if ($i["acto"] == '0030') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }

    // 2017-11-19: JINT: Certifica casa principal en texto
    // Busca si la constitución de la casa principal está en texto
    if ($conscasaprincipal == 'no') {
        $resx = armarCertificaTextoLibreClase($pdf, $data, 'CRT-CONSTI-CASPAL', 'CERTIFICA -- CONSTITUCIÓN CASA PRINCIPAL', $mysqli = null);
        if ($resx === false) {
            return false;
        } else {
            return true;
        }
    } else {
        return true;
    }
}

// *************************************************************************** //
// Certifica Apertura Suc / Age
// *************************************************************************** //
function armarCertificaAperturaSucursalAgencia($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneconstitucion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '025') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneconstitucion = 'si';
            }
        }
    }
    if ($tieneconstitucion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - APERTURA DE SUCURSAL O AGENCIA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '025') {
                // if ($i["acto"] == '0080') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica situacion de control
// 2017-11-08: JINT; Nuevo esquema de certificación de situaciones de control
// *************************************************************************** //
function armarCertificaSitControl($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienesitcontrol = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '022') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                foreach ($data["vinculos"] as $v) {
                    if (
                            (trim($v["dupliotros"]) == '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"]) || (trim($v["dupliotros"]) != '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"] && $v["dupliotros"] == $i["dupli"])
                    ) {
                        if (
                                $v["tipovinculo"] == 'SCMAI' ||
                                $v["tipovinculo"] == 'SCCOI' ||
                                $v["tipovinculo"] == 'SISUI' ||
                                $v["tipovinculo"] == 'SICNI' ||
                                $v["tipovinculo"] == 'SITC1' ||
                                $v["tipovinculo"] == 'SITC2' ||
                                $v["tipovinculo"] == 'SITC3' ||
                                $v["tipovinculo"] == 'SITC4'
                        ) {
                            $tienesitcontrol = 'si';
                        }
                    }
                }
            }
        }
    }

    //
    if ($tienesitcontrol == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - SITUACIONES DE CONTROL Y GRUPOS EMPRESARIALES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '022') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripcionesSitControl($mysqli, $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');

                    $txt = '';
                    $vinculoindividualizado = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if (
                                (trim($v["dupliotros"]) == '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"]) || (trim($v["dupliotros"]) != '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"] && $v["dupliotros"] == $i["dupli"])
                        ) {
                            if (
                                    $v["tipovinculo"] == 'SCMAI' ||
                                    $v["tipovinculo"] == 'SCCOI' ||
                                    $v["tipovinculo"] == 'SISUI' ||
                                    $v["tipovinculo"] == 'SICNI'
                            ) {
                                $vinculoindividualizado = 'si';
                            }
                        }
                    }

                    // La empresa es controlante
                    // Sin vinculos individualizados                    
                    if ($vinculoindividualizado == 'no') {
                        $tipoSit = '';
                        if ($i["acto"] == '2000') {
                            $tipoSit = 'EMPRESA MATRIZ / CONTROLANTE';
                        }
                        if ($i["acto"] == '2020') {
                            $tipoSit = 'EMPRESA MATRIZ / CONTROLANTE';
                        }
                        if ($tipoSit != '') {
                            $txt .= '<br><strong>** ' . $tipoSit . ' : </strong>' . $data["nombre"] . '<br>';
                            if (trim($data["muncom"]) != '') {
                                $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["muncom"] . "'", "ciudad") . '<br>';
                            }
                            if (trim($data["paicom"]) != '') {
                                $txt .= 'PAIS : ' . \funcionesGenerales::retornarNombrePais($mysqli, $data["paicom"]) . '<br>';
                            }
                        }
                    }

                    // Certifica los vínculos especificos
                    foreach ($data["vinculos"] as $v) {
                        if (
                                (trim($v["dupliotros"]) == '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"]) || (trim($v["dupliotros"]) != '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"] && $v["dupliotros"] == $i["dupli"])
                        ) {
                            $tipoSit = '';
                            switch ($v["tipovinculo"]) {
                                case 'SCMAI':
                                    $tipoSit = 'EMPRESA MATRIZ / CONTROLANTE';
                                    break;
                                case 'SCCOI':
                                    $tipoSit = 'EMPRESA MATRIZ / CONTROLANTE';
                                    break;
                                case 'SISUI':
                                    $tipoSit = 'EMPRESA SUBORDINADA / CONTROLADA';
                                    break;
                                case 'SICNI':
                                    $tipoSit = 'EMPRESA SUBORDINADA / CONTROLADA';
                                    break;
                                case 'SITC1':
                                    $tipoSit = 'EMPRESA MATRIZ / CONTROLANTE';
                                    break;
                                case 'SITC2':
                                    $tipoSit = 'EMPRESA SUBORDINADA / CONTROLADA';
                                    break;
                                case 'SITC3':
                                    $tipoSit = 'EMPRESA MATRIZ / CONTROLANTE';
                                    break;
                                case 'SITC4':
                                    $tipoSit = 'EMPRESA SUBORDINADA / CONTROLADA';
                                    break;
                            }
                            if ($tipoSit != '') {
                                if (trim($v["identificacionotros"]) != '') {
                                    $tx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $v["identificacionotros"] . "' and (ctrestmatricula='MA' or ctrestmatricula='MI' or ctrestmatricula='II' or ctrestmatricula='IA')");
                                    if ($tx && !empty($tx)) {
                                        $txt .= '<br><strong>** ' . $tipoSit . ' : </strong>' . $tx["razonsocial"] . '<br>';
                                        $txt .= '<strong>' . $v["cargootros"] . '</strong><br>';
                                        $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                        if (trim($tx["muncom"]) != '') {
                                            if ($tx["muncom"] == '00000' || $tx["muncom"] == '99999') {
                                                $txt .= 'MUNICIPIO : ' . 'FUERA DEL PAIS' . '<br>';
                                            } else {
                                                $txt .= 'MUNICIPIO : ' . $tx["muncom"] . ' - ' . retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $tx["muncom"] . "'", "ciudad") . '<br>';
                                            }
                                        }
                                        if (trim($tx["dircom"]) != '') {
                                            $txt .= 'DIRECCIÓN : ' . $tx["dircom"] . '<br>';
                                        }
                                        if (trim($tx["paicom"]) != '') {
                                            $tpai = \funcionesGenerales::retornarNombrePais($mysqli, $tx["paicom"]);
                                            if ($tpai == '') {
                                                $tpai = 'Colombia';
                                            }
                                            $txt .= 'PAIS : ' . $tpai . '<br>';
                                        }

                                        if (trim($tx["ciiu1"]) != '') {
                                            $txt .= 'CIIU : ' . $tx["ciiu1"] . ' - ' . \funcionesgenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu1"]) . '<br>';
                                        }
                                        if ($tx["ciiu2"] != '') {
                                            $txt .= 'CIIU : ' . $tx["ciiu2"] . ' - ' . \funcionesgenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu2"]) . '<br>';
                                        }
                                        if ($tx["ciiu3"] != '') {
                                            $txt .= 'CIIU : ' . $tx["ciiu3"] . ' - ' . \funcionesgenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu3"]) . '<br>';
                                        }
                                        if ($tx["ciiu4"] != '') {
                                            $txt .= 'CIIU : ' . $tx["ciiu4"] . ' - ' . \funcionesgenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu4"]) . '<br>';
                                        }
                                        if ($tx["actividad"] != '') {
                                            $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $tx["actividad"] . '<br>';
                                        }
                                    } else {
                                        $txt .= '<br><strong>** ' . $tipoSit . ' : </strong>' . $v["nombreotros"] . '<br>';
                                        $txt .= '<strong>' . $v["cargootros"] . '</strong><br>';
                                        if (trim($v["identificacionotros"]) != '') {
                                            $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                        }
                                        if (trim($v["municipiootros"]) != '') {
                                            if ($v["municipiootros"] == '00000' || $v["municipiootros"] == '99999') {
                                                $txt .= 'MUNICIPIO : ' . 'FUERA DEL PAIS' . '<br>';
                                            } else {
                                                $txt .= 'MUNICIPIO : ' . $v["municipiootros"] . ' - ' . retornarRegistroMysqliApi($mysqli, "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                            }
                                        }
                                        if (trim($v["direccionotros"]) != '') {
                                            $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                        }
                                        if (trim($v["paisotros"]) != '') {
                                            $tpai = retornarRegistroMysqliApi($mysqli,'bas_paises', "idpais='" . $v["paisotros"] . "'", "nombrepais");
                                            if ($tpai == '') {
                                                $tpai = retornarRegistroMysqliApi($mysqli,'bas_paises', "codnumpais='" . $v["paisotros"] . "'", "nombrepais");
                                            }
                                            if ($tpai == '') {
                                                $tpai = 'Colombia';
                                            }
                                            $txt .= 'PAIS : ' . $tpai . '<br>';
                                        }
                                        if (trim($v["ciiu1"]) != '') {
                                            $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesgenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                        }
                                        if ($v["ciiu2"] != '') {
                                            $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesgenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                        }
                                        if ($v["ciiu3"] != '') {
                                            $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesgenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                        }
                                        if ($v["ciiu4"] != '') {
                                            $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesgenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                        }
                                        if ($v["desactiv"] != '') {
                                            $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                        }
                                    }
                                    if ($v["fechaconfiguracion"] != '') {
                                        $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                    }
                                }
                            }
                        }
                    }

                    // La empresa es subordinada
                    // Sin vinculos individualizados
                    if ($vinculoindividualizado == 'no') {
                        $tipoSit = '';
                        if ($i["acto"] == '2010') {
                            $tipoSit = 'EMPRESA SUBORDINADA / CONTROLADA';
                        }
                        if ($i["acto"] == '2030') {
                            $tipoSit = 'EMPRESA SUBORDINADA / CONTROLADA';
                        }
                        if ($tipoSit != '') {
                            $txt .= '<br><strong>** ' . $tipoSit . ' : </strong>' . $data["nombre"] . '<br>';

                            if (trim($data["muncom"]) != '') {
                                $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $data["muncom"] . "'", "ciudad") . '<br>';
                            }
                            if (trim($data["paicom"]) != '') {
                                $txt .= 'PAIS : ' . \funcionesGenerales::retornarNombrePais($mysqli, $data["paicom"]) . '<br>';
                            }
                        }
                    }

                    // Imprime la inscripcion
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    }
    if ($tienesitcontrol == 'no') {
        $resx = armarCertificaTextoLibreClase($pdf, $data, 'CRT-SITCONTROL', 'CERTIFICA - SITUACIONES DE CONTROL Y GRUPOS EMPRESARIALES', $mysqli);
        return $resx;
    } else {
        return true;
    }
}

// *************************************************************************** //
// Certifica situacion de control
// *************************************************************************** //
function armarCertificaSitControlOriginal($pdf, $data, $acto, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienesitcontrol = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["acto"] == $acto) {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tienesitcontrol = 'si';
            }
        }
    }
    if ($tienesitcontrol == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - SITUACIONES DE CONTROL Y GRUPOS EMPRESARIALES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["acto"] == $acto) {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripcionesSitControlOriginal($mysqli, $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                    if ($acto == '2000' || $acto == '2020') { // Si $data["nombre"] es matriz
                        $txt = '<strong>** MATRIZ O CONTROLANTE : </strong>' . $data["nombre"] . '<br>';
                        if (trim($data["muncom"]) != '') {
                            $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $data["muncom"] . "'", "ciudad") . '<br>';
                        }
                        if (trim($data["paicom"]) != '') {
                            $txt .= 'PAIS : ' . \funcionesGenerales::retornarNombrePais($mysqli, $data["paicom"]) . '<br>';
                        }

                        foreach ($data["vinculos"] as $v) {
                            if (
                                    (trim($v["dupliotros"]) == '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"]) || (trim($v["dupliotros"]) != '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"] && $v["dupliotros"] == $i["dupli"])
                            ) {
                                if ($v["identificacionotros"] != $data["identificacion"]) {
                                    if ($v["vinculootros"] == '6000' || $v["vinculootros"] == '6002') {
                                        if (trim($v["identificacionotros"]) != '') {
                                            $tx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $v["identificacionotros"] . "' and (ctrestmatricula='MA' or ctrestmatricula='MI' or ctrestmatricula='II' or ctrestmatricula='IA')");
                                            if ($tx && !empty($tx)) {
                                                $txt .= '<strong>** MATRIZ O CONTROLANTE : </strong> ' . $tx["razonsocial"] . '<br>';
                                                if (trim($v["identificacionotros"]) != '') {
                                                    $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                                }
                                                if (trim($tx["muncom"]) != '') {
                                                    $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $tx["muncom"] . "'", "ciudad") . '<br>';
                                                }
                                                if (trim($tx["dircom"]) != '') {
                                                    $txt .= 'DIRECCIÓN : ' . $tx["dircom"] . '<br>';
                                                }
                                                if (trim($tx["paicom"]) != '') {
                                                    $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $tx["paicom"])) . '<br>';
                                                }
                                                if (trim($tx["ciiu1"]) != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu1"]) . '<br>';
                                                }
                                                if ($tx["ciiu2"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu2"]) . '<br>';
                                                }
                                                if ($tx["ciiu3"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu3"]) . '<br>';
                                                }
                                                if ($tx["ciiu4"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu4"]) . '<br>';
                                                }
                                                if ($tx["actividad"] != '') {
                                                    $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $tx["actividad"] . '<br>';
                                                }
                                            } else {
                                                $txt .= '<strong>** MATRIZ O CONTROLANTE : </strong> ' . $v["nombreotros"] . '<br>';
                                                if (trim($v["identificacionotros"]) != '') {
                                                    $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                                }
                                                if (trim($v["municipiootros"]) != '') {
                                                    $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                                }
                                                if (trim($v["direccionotros"]) != '') {
                                                    $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                                }
                                                if (trim($v["paisotros"]) != '') {
                                                    $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $v["paisotros"])) . '<br>';
                                                }
                                                if (trim($v["ciiu1"]) != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                                }
                                                if ($v["ciiu2"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                                }
                                                if ($v["ciiu3"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                                }
                                                if ($v["ciiu4"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                                }
                                                if ($v["desactiv"] != '') {
                                                    $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                                }
                                            }
                                            if ($v["fechaconfiguracion"] != '') {
                                                $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                            }
                                        } else {
                                            $txt .= '<strong>** MATRIZ O CONTROLANTE : </strong> ' . $v["nombreotros"] . '<br>';
                                            if (trim($v["identificacionotros"]) != '') {
                                                $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                            }
                                            if (trim($v["municipiootros"]) != '') {
                                                $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                            }
                                            if (trim($v["direccionotros"]) != '') {
                                                $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                            }
                                            if (trim($v["paisotros"]) != '') {
                                                $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $v["paisotros"])) . '<br>';
                                            }
                                            if (trim($v["ciiu1"]) != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                            }
                                            if ($v["ciiu2"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                            }
                                            if ($v["ciiu3"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                            }
                                            if ($v["ciiu4"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                            }
                                            if ($v["desactiv"] != '') {
                                                $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                            }
                                            if ($v["fechaconfiguracion"] != '') {
                                                $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                            }
                                        }
                                    }
                                    if ($v["vinculootros"] == '6001' || $v["vinculootros"] == '6003') {
                                        if (trim($v["identificacionotros"]) != '') {
                                            $tx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $v["identificacionotros"] . "' and (ctrestmatricula='MA' or ctrestmatricula='MI' or ctrestmatricula='II' or ctrestmatricula='IA')");
                                            if ($tx && !empty($tx)) {
                                                $txt .= '<strong>** SUBORDINADA O CONTROLADA : </strong> ' . $tx["razonsocial"] . '<br>';
                                                if (trim($v["identificacionotros"]) != '') {
                                                    $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                                }
                                                if (trim($tx["muncom"]) != '') {
                                                    $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $tx["muncom"] . "'", "ciudad") . '<br>';
                                                }
                                                if (trim($tx["dircom"]) != '') {
                                                    $txt .= 'DIRECCIÓN : ' . $tx["dircom"] . '<br>';
                                                }
                                                if (trim($tx["paicom"]) != '') {
                                                    $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $tx["paicom"])) . '<br>';
                                                }
                                                if (trim($tx["ciiu1"]) != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu1"]) . '<br>';
                                                }
                                                if ($tx["ciiu2"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu2"]) . '<br>';
                                                }
                                                if ($tx["ciiu3"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu3"]) . '<br>';
                                                }
                                                if ($tx["ciiu4"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu4"]) . '<br>';
                                                }
                                                if ($tx["actividad"] != '') {
                                                    $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $tx["actividad"] . '<br>';
                                                }
                                            } else {
                                                $txt .= '<strong>** SUBORDINADA O CONTROLADA : </strong> ' . $v["nombreotros"] . '<br>';
                                                if (trim($v["identificacionotros"]) != '') {
                                                    $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                                }
                                                if (trim($v["municipiootros"]) != '') {
                                                    $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                                }
                                                if (trim($v["direccionotros"]) != '') {
                                                    $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                                }
                                                if (trim($v["paisotros"]) != '') {
                                                    $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $v["paisotros"])) . '<br>';
                                                }
                                                if (trim($v["ciiu1"]) != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                                }
                                                if ($v["ciiu2"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                                }
                                                if ($v["ciiu3"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                                }
                                                if ($v["ciiu4"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                                }
                                                if ($v["desactiv"] != '') {
                                                    $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                                }
                                            }
                                            if ($v["fechaconfiguracion"] != '') {
                                                $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                            }
                                        } else {
                                            $txt .= '<strong>** SUBORDINADA O CONTROLADA : </strong> ' . $v["nombreotros"] . '<br>';
                                            if (trim($v["identificacionotros"]) != '') {
                                                $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                            }
                                            if (trim($v["municipiootros"]) != '') {
                                                $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                            }
                                            if (trim($v["direccionotros"]) != '') {
                                                $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                            }
                                            if (trim($v["paisotros"]) != '') {
                                                $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $v["paisotros"])) . '<br>';
                                            }
                                            if (trim($v["ciiu1"]) != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                            }
                                            if ($v["ciiu2"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                            }
                                            if ($v["ciiu3"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                            }
                                            if ($v["ciiu4"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                            }
                                            if ($v["desactiv"] != '') {
                                                $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                            }
                                            if ($v["fechaconfiguracion"] != '') {
                                                $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($acto == '2010' || $acto == '2030') { // Si $data["nombre"] es subordinada
                        $txt = '';
                        foreach ($data["vinculos"] as $v) {
                            if (
                                    (trim($v["dupliotros"]) == '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"]) || (trim($v["dupliotros"]) != '' && $v["librootros"] == $i["lib"] && $v["inscripcionotros"] == $i["nreg"] && $v["dupliotros"] == $i["dupli"])
                            ) {
                                if ($v["identificacionotros"] != $data["identificacion"]) {
                                    if ($v["vinculootros"] == '6000' || $v["vinculootros"] == '6002') {
                                        if (trim($v["identificacionotros"]) != '') {
                                            $tx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $v["identificacionotros"] . "' and (ctrestmatricula='MA' or ctrestmatricula='MI' or ctrestmatricula='II' or ctrestmatricula='IA')");
                                            if ($tx && !empty($tx)) {
                                                $txt .= '<strong>** MATRIZ O CONTROLANTE : </strong> ' . $tx["razonsocial"] . '<br>';
                                                if (trim($v["identificacionotros"]) != '') {
                                                    $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                                }
                                                if (trim($tx["muncom"]) != '') {
                                                    $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $tx["muncom"] . "'", "ciudad") . '<br>';
                                                }
                                                if (trim($tx["paicom"]) != '') {
                                                    $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $tx["paicom"])) . '<br>';
                                                }
                                                if (trim($tx["dircom"]) != '') {
                                                    $txt .= 'DIRECCIÓN : ' . $tx["dircom"] . '<br>';
                                                }
                                                if (trim($tx["ciiu1"]) != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu1"]) . '<br>';
                                                }
                                                if ($tx["ciiu2"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu2"]) . '<br>';
                                                }
                                                if ($tx["ciiu3"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu3"]) . '<br>';
                                                }
                                                if ($tx["ciiu4"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu4"]) . '<br>';
                                                }
                                                if ($v["fechaconfiguracion"] != '') {
                                                    $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                                }
                                            } else {
                                                $txt .= '<strong>** MATRIZ O CONTROLANTE : </strong> ' . $v["nombreotros"] . '<br>';
                                                if (trim($v["identificacionotros"]) != '') {
                                                    $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                                }
                                                if (ltrim(trim($v["municipiootros"]), "0") != '') {
                                                    $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                                }
                                                if (ltrim(trim($v["paisotros"]), "0") != '') {
                                                    $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $v["paisotros"])) . '<br>';
                                                }
                                                if (trim($v["direccionotros"]) != '') {
                                                    $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                                }
                                                if (trim($v["ciiu1"]) != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                                }
                                                if ($v["ciiu2"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                                }
                                                if ($v["ciiu3"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                                }
                                                if ($v["ciiu4"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                                }
                                                if ($v["desactiv"] != '') {
                                                    $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                                }
                                                if ($v["fechaconfiguracion"] != '') {
                                                    $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                                }
                                            }
                                        } else {
                                            $txt .= '<strong>** MATRIZ O CONTROLANTE : </strong> ' . $v["nombreotros"] . '<br>';
                                            if (trim($v["identificacionotros"]) != '') {
                                                $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                            }
                                            if (ltrim(trim($v["municipiootros"]), "0") != '') {
                                                $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                            }
                                            if (ltrim(trim($v["paisotros"]), "0") != '') {
                                                $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $v["paisotros"])) . '<br>';
                                            }
                                            if (trim($v["direccionotros"]) != '') {
                                                $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                            }
                                            if (trim($v["ciiu1"]) != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                            }
                                            if ($v["ciiu2"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                            }
                                            if ($v["ciiu3"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                            }
                                            if ($v["ciiu4"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                            }
                                            if ($v["desactiv"] != '') {
                                                $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                            }
                                            if ($v["fechaconfiguracion"] != '') {
                                                $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                            }
                                        }
                                    }
                                    if ($v["vinculootros"] == '6001' || $v["vinculootros"] == '6003') {
                                        if (trim($v["identificacionotros"]) != '') {
                                            $tx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $v["identificacionotros"] . "' and (ctrestmatricula='MA' or ctrestmatricula='MI' or ctrestmatricula='II' or ctrestmatricula='IA')");
                                            if ($tx && !empty($tx)) {
                                                $txt .= '<strong>** SUBORDINADA O CONTROLADA : </strong> ' . $tx["razonsocial"] . '<br>';
                                                if (trim($v["identificacionotros"]) != '') {
                                                    $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                                }
                                                if (trim($tx["muncom"]) != '') {
                                                    $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $tx["muncom"] . "'", "ciudad") . '<br>';
                                                }
                                                if (trim($tx["paicom"]) != '') {
                                                    $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $tx["paicom"])) . '<br>';
                                                }
                                                if (trim($tx["dircom"]) != '') {
                                                    $txt .= 'DIRECCIÓN : ' . $tx["dircom"] . '<br>';
                                                }
                                                if (trim($tx["ciiu1"]) != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu1"]) . '<br>';
                                                }
                                                if ($tx["ciiu2"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu2"]) . '<br>';
                                                }
                                                if ($tx["ciiu3"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu3"]) . '<br>';
                                                }
                                                if ($tx["ciiu4"] != '') {
                                                    $txt .= 'CIIU : ' . $tx["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $tx["ciiu4"]) . '<br>';
                                                }
                                                if ($v["fechaconfiguracion"] != '') {
                                                    $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                                }
                                            } else {
                                                $txt .= '<strong>** SUBORDINADA O CONTROLADA : </strong> ' . $v["nombreotros"] . '<br>';
                                                if (trim($v["identificacionotros"]) != '') {
                                                    $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                                }
                                                if (ltrim(trim($v["municipiootros"]), "0") != '') {
                                                    $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                                }
                                                if (ltrim(trim($v["paisotros"]), "0") != '') {
                                                    $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $v["paisotros"])) . '<br>';
                                                }
                                                if (trim($v["direccionotros"]) != '') {
                                                    $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                                }
                                                if (trim($v["ciiu1"]) != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                                }
                                                if ($v["ciiu2"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                                }
                                                if ($v["ciiu3"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                                }
                                                if ($v["ciiu4"] != '') {
                                                    $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                                }
                                                if ($v["desactiv"] != '') {
                                                    $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                                }
                                                if ($v["fechaconfiguracion"] != '') {
                                                    $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                                }
                                            }
                                        } else {
                                            $txt .= '<strong>** SUBORDINADA O CONTROLADA : </strong> ' . $v["nombreotros"] . '<br>';
                                            if (trim($v["identificacionotros"]) != '') {
                                                $txt .= 'IDENTIFICACION : ' . $v["identificacionotros"] . '<br>';
                                            }
                                            if (ltrim(trim($v["municipiootros"]), "0") != '') {
                                                $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $v["municipiootros"] . "'", "ciudad") . '<br>';
                                            }
                                            if (ltrim(trim($v["paisotros"]), "0") != '') {
                                                $txt .= 'PAIS : ' . strtoupper(\funcionesGenerales::retornarNombrePais($mysqli, $v["paisotros"])) . '<br>';
                                            }
                                            if (trim($v["direccionotros"]) != '') {
                                                $txt .= 'DIRECCIÓN : ' . $v["direccionotros"] . '<br>';
                                            }
                                            if (trim($v["ciiu1"]) != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu1"]) . '<br>';
                                            }
                                            if ($v["ciiu2"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu2"]) . '<br>';
                                            }
                                            if ($v["ciiu3"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu3"]) . '<br>';
                                            }
                                            if ($v["ciiu4"] != '') {
                                                $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $v["ciiu4"]) . '<br>';
                                            }
                                            if ($v["desactiv"] != '') {
                                                $txt .= 'DESCRIPCIÓN DE LA ACTIVIDAD : ' . $v["desactiv"] . '<br>';
                                            }
                                            if ($v["fechaconfiguracion"] != '') {
                                                $txt .= 'FECHA DE CONFIGURACION DE LA SITUACIÓN : ' . \funcionesGenerales::mostrarFecha($v["fechaconfiguracion"]) . '<br>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $txt .= '<strong>** SUBORDINADA O CONTROLADA : </strong>' . $data["nombre"] . '<br>';
                        $txt .= 'MUNICIPIO : ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $data["muncom"] . "'", "ciudad") . '<br>';
                    }
                    $txt .= '<br>' . $i["not"] . '<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Constitución - Acto 0042
// *************************************************************************** //
function armarCertificaConstitucionCambioDomicilio($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneconstitucion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '024') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneconstitucion = 'si';
            }
        }
    }
    if ($tieneconstitucion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CAMBIOS DE DOMICILIO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '024') {
                // if ($i["acto"] == '0042') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    } else {
        // 2017.11.19: JINT: Grupo textual  constitución por cambio de domicilio CRT-CAMDOM
        armarCertificaTextoLibreClase($pdf, $data, 'CRT-CAMDOM', 'CERTIFICA - CAMBIOS DE DOMICILIO', $mysqli);
    }

    // 2017-09-18: JINT: SE ELIMINA LA CERTIFICACION DEL CODIGO 0043 PUES ESTE
    // NO EXISTE EN LOS CERTIFICAS SIREP / SII.
    if (isset($data["crtsii"]["0043X"])) {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - ACLARACION CAMBIOS DE DOMICILIO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = $data["crtsii"]["0043X"];
        // $pdf->SetFont('courier', '', 8);
        $pdf->writeHTML(str_replace("<p>&nbsp;</p>", "<br>", $txt), true, false, true, false, 'J');
        // $pdf->SetFont('courier', '', 8);
        $pdf->Ln();
        $tiene0710 = 'si';
    } else {
        if (isset($data["crt"]["0043X"])) {
            if (trim($data["crt"]["0043X"]) != '') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA -- ACLARACION CAMBIOS DE DOMICILIO</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                $txt = $data["crt"]["0043X"];
                $txt = str_replace("||", "<br>", $txt);
                $txt = str_replace("|", " ", $txt);
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
                $tiene0710 = 'si';
            }
        }
    }
}

// *************************************************************************** //
// Certifica Cambios de domicilio
// *************************************************************************** //
function armarCertificaCambiosDomicilio($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienecb = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '059') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tienecb = 'si';
            }
        }
    }
    if ($tienecb == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CAMBIOS DE DOMICILIO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '059') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Cambios de domicilio - Certificacion
// *************************************************************************** //
function armarCertificaCambiosDomicilioCertificaciones($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienecb = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '074') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tienecb = 'si';
            }
        }
    }
    if ($tienecb == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CAMBIOS DE DOMICILIO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '074') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica capitales SAT - en texto certifica 9001
// 2017.11.19: JINT: Grupo textual  patrimonio SATs CRT-CAPSAT
// *************************************************************************** //
function armarCertificaCapitalSat($pdf, $data, $mysqli = null) {
    armarCertificaTextoLibreClase($pdf, $data, 'CRT-CAPSAT', 'CERTIFICA - CAPITAL SOCIEDADES AGRARIAS DE TRANSOFRMACIÓN', $mysqli);
}

// *************************************************************************** //
// Certifica Reformas - 
// Textual: Grupo CRT-REFORMAS
// Actos: Que el campo esreforma = 'si'
// Textual: AC-REFORMAS
// *************************************************************************** //
function armarCertificaReformas($pdf, $data, $mysqli = null) {

    // 2017-11-28: JINT: Condición especial para manizales
    // Si existe certifica 8000 debe eliminar el certifica 0710
    // No incluye el certifica 0711
    if (CODIGO_EMPRESA == '20') {
        if (isset($data["crtsii"]["8000"]) && trim($data["crtsii"]["8000"]) != '') {
            unset($data["crtsii"]["0710"]);
            unset($data["crt"]["0710"]);
        }
        unset($data["crtsii"]["0711"]);
        unset($data["crt"]["0711"]);
    }

    // 2019-09-06: JINT: para imprimir textos con writehtml.
    armarCertificaTextoLibreClase($pdf, $data, 'CRT-REFORMAS-HTML', 'CERTIFICA - REFORMAS', $mysqli);

    // Certifca de reformas textual
    armarCertificaTextoLibreClase($pdf, $data, 'CRT-REFORMAS', 'CERTIFICA - REFORMAS', $mysqli);
    $tieneactosreforma = 'no';
    $numeroreformas = 0;

    // Lista de reformas en actos
    $txt = '';
    foreach ($data["inscripciones"] as $ins) {
        if ($ins["esreforma"] == 'S') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1') {
                $tieneactosreforma = 'si';
                $numeroreformas++;
                if ($numeroreformas == 1) {
                    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                        $txt = '<strong>CERTIFICA - REFORMAS</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    } else {
                        $txt = '<strong>CERTIFICA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    }
                    $txt = '<table>';
                    $txt .= '<tr>';
                    $txt .= '<td width="10%">DOCUMENTO</td>';
                    $txt .= '<td width="15%">FECHA</td>';
                    $txt .= '<td width="23%">PROCEDENCIA DOCUMENTO</td>';
                    $txt .= '<td width="2%"></td>';
                    $txt .= '<td width="10%"></td>';
                    $txt .= '<td witdh="30%">INSCRIPCION</td>';
                    $txt .= '<td width="12%">FECHA</td>';
                    $txt .= '</tr>';
                }
                $txt .= '<tr>';

                //
                if (
                        trim($ins["ndoc"]) == 'N/A' ||
                        trim($ins["ndoc"]) == 'NA' ||
                        trim($ins["ndoc"]) == 'n/a' ||
                        trim($ins["ndoc"]) == 'na'
                ) {
                    $ins["ndoc"] = '';
                }

                //
                $ntd = '';
                switch ($ins["tdoc"]) {
                    case "01":
                        $ntd = 'AC-';
                        break;
                    case "02":
                        $ntd = 'EP-';
                        break;
                    case "03":
                        $ntd = 'RS-';
                        break;
                    case "04":
                        $ntd = 'OF-';
                        break;
                    case "05":
                        $ntd = 'PJ-';
                        break;
                    case "06":
                        if (trim($ins["ndoc"]) != '') {
                            $ntd = 'DP-';
                        } else {
                            $ntd = 'DOC.PRIV.';
                        }
                        break;
                    case "07":
                        $ntd = 'DM-';
                        break;
                    case "08":
                        $ntd = 'FO-';
                        break;
                    case "09":
                        $ntd = 'DE-';
                        break;
                    case "10":
                        $ntd = 'CE-';
                        break;
                    case "11":
                        $ntd = 'AU-';
                        break;
                    case "12":
                        $ntd = 'PA-';
                        break;
                    case "13":
                        $ntd = 'CC-';
                        break;
                    case "15":
                        $ntd = 'LEY-';
                        break;
                }

                //
                $txt .= '<td width="10%">' . $ntd . trim($ins["ndoc"]) . '</td>';
                $txt .= '<td width="15%">' . $ins["fdoc"] . '</td>';
                if ($ins["idoridoc"] != '' && $ins["idoridoc"] != '000000' && $ins["idoridoc"] != '999999') {
                    $txto = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='51' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion");
                    if ($txto == '') {
                        if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                            $ins["txoridoc"] = 'ORGANOS DE ADMINISTRACION';
                        }
                        $txt .= '<td width="23%">' . str_replace("NOTARIAS NOTARIA", "NOTARIA", strtoupper($ins["txoridoc"])) . '</td>';
                    } else {
                        $txt .= '<td width="23%">' . retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='51' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion");
                    }
                } else {
                    if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                        $ins["txoridoc"] = 'ORGANOS DE ADMINISTRACION';
                    }
                    $txt .= '<td width="23%">' . str_replace("NOTARIAS NOTARIA", "NOTARIA", strtoupper($ins["txoridoc"])) . '</td>';
                }
                $txt .= '<td width="2%">&nbsp;</td>';
                $txt .= '<td width="10%">' . substr(retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $ins["idmunidoc"] . "'", "ciudad"), 0, 15) . '</td>';
                $txLib = '';
                switch ($ins["lib"]) {
                    case "RE51":
                        $txLib = 'RE01';
                        break;
                    case "RE52":
                        $txLib = 'RE02';
                        break;
                    case "RE53":
                        $txLib = 'RE03';
                        break;
                    case "RE54":
                        $txLib = 'RE04';
                        break;
                    case "RE54":
                        $txLib = 'RE05';
                        break;
                    default:
                        $txLib = $ins["lib"];
                        break;
                }
                $txt .= '<td witdh="30%">' . $txLib . '-' . $ins["nreg"] . '</td>';
                $txt .= '<td width="12%">' . $ins["freg"] . '</td>';
                $txt .= '</tr>';
            }
        }
    }
    if ($numeroreformas != 0) {
        $txt .= '</table>';
    }
    $pdf->SetFont('courier', '', 7.7);
    $pdf->writeHTML($txt, true, false, true, false, 'J');
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($numeroreformas == 0) {
        // Certifca de reformas textual (lista)
        armarCertificaTextoLibreClase($pdf, $data, 'CRT-REFORMAS-LISTA', 'CERTIFICA - REFORMAS', $mysqli);
    }

    // Texto aclaratoria de reformas
    foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
        if ($dtax["clase"] == 'AC-REFORMAS') {
            $incluircert = 'si';
            if ($data["organizacion"] == '16' && $data["categoria"] == '1') {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    if (strpos($data["crtsii"][$certif], 'SOCIEDAD COMERCIAL SIMPLIFICADA')) {
                        $incluircert = 'no';
                    }
                    if ($incluircert == 'si') {
                        if (strpos($data["crtsii"][$certif], 'DE LA ESPECIE DE LAS')) {
                            $incluircert = 'no';
                        }
                    }
                }
            }
            if ($incluircert == 'no') {
                unset($data["crtsii"][$certif]);
            }
            $incluircert = 'si';
            if ($data["organizacion"] == '16' && $data["categoria"] == '1') {
                if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                    $txt1 = $data["crt"][$certif];
                    $txt1 = str_replace("||", "|", $txt1);
                    $txt1 = str_replace("|", " ", $txt1);

                    if (strpos($txt1, 'SOCIEDAD COMERCIAL SIMPLIFICADA')) {
                        $incluircert = 'no';
                    }
                    if ($incluircert == 'si') {
                        if (strpos($txt1, 'DE LA ESPECIE DE LAS')) {
                            $incluircert = 'no';
                        }
                    }
                }
            }
            if ($incluircert == 'no') {
                unset($data["crt"][$certif]);
            }
        }
    }
    armarCertificaTextoLibreClase($pdf, $data, 'AC-REFORMAS', 'CERTIFICA', $mysqli);
}

// *************************************************************************** //
// Certifica sitios web
// *************************************************************************** //
function armarCertificaSitiosWeb($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $siurl = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '046') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $siurl = 'si';
            }
        }
    }
    if ($siurl == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - SITIOS WEB</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '046') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica prohibiciones
// *************************************************************************** //
function armarCertificaProhibiciones($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $siurl = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '051') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $siurl = 'si';
                if ($i["fechalimite"] != '') {
                    if ($i["fechalimite"] < date("Ymd")) {
                        $siurl = 'no';
                    }
                }
            }
        }
    }
    if ($siurl == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - PROHIBICIONES DE ENAJENACION</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '051') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Arma certifica de socios
// *************************************************************************** //
function armarCertificaSocios($pdf, $data, $tiposocios, $mysqli = null) {

    if (
            $data["organizacion"] == '03' ||
            $data["organizacion"] == '05' ||
            $data["organizacion"] == '06' ||
            $data["organizacion"] == '08' ||
            $data["organizacion"] == '09' ||
            $data["organizacion"] == '11' ||
            $data["organizacion"] == '15' ||
            $data["organizacion"] == '17' || ($data["organizacion"] == '10' && ($data["naturaleza"] == '' || $data["naturaleza"] == '0' || $data["naturaleza"] == '1' || $data["naturaleza"] == '2' || $data["naturaleza"] == '4'))
    ) {
        //
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);

        $socios = 'no';
        if ($data["categoria"] == '1') {
            $socios = 'si';
            if ($data["organizacion"] == '04' || $data["organizacion"] == '07' || $data["organizacion"] == '16') {
                $socios = 'no';
            }
        }
        if ($socios == 'si') {
            $socios = 'no';
            foreach ($data["vinculos"] as $v) {
                if ($v["vinculootros"] == $tiposocios) {
                    $socios = 'si';
                }
            }
        }
        if ($socios == 'si') {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - SOCIOS</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }

            if (defined('CAMARA_SUR_OCCIDENTE') && CAMARA_SUR_OCCIDENTE == 'S') {
                $txt = '<strong>SOCIOS / ASOCIADOS</strong>';
            } else {
                switch ($tiposocios) {
                    case "1100":
                        $txt = '<strong>SOCIOS CAPITALISTAS</strong>';
                        break;
                    case "1101":
                        $txt = '<strong>SOCIOS CAPITALISTAS - SUPLENTES</strong>';
                        break;
                    case "1110":
                        $txt = '<strong>SOCIOS INDUSTRIALES</strong>';
                        break;
                    case "1120":
                        $txt = '<strong>SOCIOS GESTORES</strong>';
                        break;
                    case "1121":
                        $txt = '<strong>SOCIOS GESTORES - PRIMEROS SUPLENTES</strong>';
                        break;
                    case "1122":
                        $txt = '<strong>SOCIOS GESTORES - SEGUNDOS SUPLENTES</strong>';
                        break;
                    case "1126":
                        $txt = '<strong>SOCIOS GESTORES - ADMINISTRADORES</strong>';
                        break;
                    case "1130":
                        $txt = '<strong>SOCIOS COMANDITARIOS</strong>';
                        break;
                    case "1140":
                        $txt = '<strong>SOCIOS ACCIONISTAS</strong>';
                        break;
                    case "1150":
                        $txt = '<strong>SOCIOS ADMINISTRADORES</strong>';
                        break;
                    case "1151":
                        $txt = '<strong>SOCIOS ADMINISTRADORES - PRIMEROS SUPLENTES</strong>';
                        break;
                    case "1152":
                        $txt = '<strong>SOCIOS ADMINISTRADORES - SEGUNDOS SUPLENTES</strong>';
                        break;
                    case "1160":
                        $txt = '<strong>SOCIOS COLECTIVOS</strong>';
                        break;
                    case "1170":
                        $txt = '<strong>ASOCIADOS</strong>';
                        break;
                    case "3110":
                        $txt = '<strong>SOCIOS EMPRESARIOS</strong>';
                        break;
                    case "3111":
                        $txt = '<strong>SOCIOS EMPRESARIOS - SUPLENTES</strong>';
                        break;
                }
            }
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();

            if ($data["organizacion"] == '09') {
                $txt = '<table>';

                $txt .= '<tr align="center">';
                $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
                $txt .= '<td width="20%"><strong>IDENTIFICACION</strong></td>';
                $txt .= '<td width="20%"><strong>APORTE</strong></td>';
                $txt .= '<td width="20%"><strong>VALOR</strong></td>';
                $txt .= '</tr>';

                foreach ($data["vinculos"] as $v) {
                    if ($v["vinculootros"] == $tiposocios) {
                        $apolab = doubleval($v["va1"]);
                        $apolabadi = doubleval($v["va2"]);
                        $apodin = doubleval($v["va3"]);
                        $apotra = doubleval($v["va4"]);
                        if (
                                doubleval($v["va5"]) != 0 ||
                                doubleval($v["va6"]) != 0 ||
                                doubleval($v["va7"]) != 0 ||
                                doubleval($v["va8"]) != 0
                        ) {
                            $apolab = doubleval($v["va5"]);
                            $apolabadi = doubleval($v["va6"]);
                            $apodin = doubleval($v["va7"]);
                            $apotra = doubleval($v["va8"]);
                        }
                        $apotot = $apolab + $apolabadi + $apotra + $apodin;

                        $txt .= '<tr align="center">';
                        $txt .= '<td width="40%">&nbsp;</td>';
                        $txt .= '<td width="20%">&nbsp;</td>';
                        $txt .= '<td width="20%">&nbsp;</td>';
                        $txt .= '<td width="20%">&nbsp;</td>';
                        $txt .= '</tr>';

                        $txt .= '<tr align="center">';
                        $txt .= '<td width="40%">' . $v["nombreotros"] . '</td>';
                        if ($v["idtipoidentificacionotros"] == '2') {
                            $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                            $txt .= '<td width="20%">' . $sp["identificacion"] . '-' . $sp["dv"] . '</td>';
                        } else {
                            $txt .= '<td width="20%">' . retornarTxtTipoIde($v["idtipoidentificacionotros"]) . '-' . number_format($v["identificacionotros"], 0) . '</td>';
                        }
                        $txt .= '<td width="20%">' . 'TOTAL' . '</td>';
                        $txt .= '<td width="20%">' . $apotot . '</td>';
                        $txt .= '</tr>';

                        $txt .= '<tr align="center">';
                        $txt .= '<td width="40%">&nbsp;</td>';
                        $txt .= '<td width="20%">&nbsp;</td>';
                        $txt .= '<td width="20%">' . 'LABORAL' . '</td>';
                        $txt .= '<td width="20%">' . $apolab . '</td>';
                        $txt .= '</tr>';

                        $txt .= '<tr align="center">';
                        $txt .= '<td width="40%">&nbsp;</td>';
                        $txt .= '<td width="20%">&nbsp;</td>';
                        $txt .= '<td width="20%">' . 'LABORAL ADICIONAL' . '</td>';
                        $txt .= '<td width="20%">' . $apolabadi . '</td>';
                        $txt .= '</tr>';

                        $txt .= '<tr align="center">';
                        $txt .= '<td width="40%">&nbsp;</td>';
                        $txt .= '<td width="20%">&nbsp;</td>';
                        $txt .= '<td width="20%">' . 'DINERO' . '</td>';
                        $txt .= '<td width="20%">' . $apodin . '</td>';
                        $txt .= '</tr>';

                        $txt .= '<tr align="center">';
                        $txt .= '<td width="40%">&nbsp;</td>';
                        $txt .= '<td width="20%">&nbsp;</td>';
                        $txt .= '<td width="20%">' . 'TRABAJO' . '</td>';
                        $txt .= '<td width="20%">' . $apotra . '</td>';
                        $txt .= '</tr>';
                    }
                }
                $txt .= '</table>';
                $pdf->writeHTML($txt, true, false, true, false);
                $pdf->Ln();
            } else {
                $txt = '<table>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="35%"><strong>NOMBRE</strong></td>';
                $txt .= '<td width="25%"><strong>IDENTIFICACION</strong></td>';
                $txt .= '<td width="20%"><strong>CUOTAS</strong></td>';
                $txt .= '<td width="20%"><strong>VALOR</strong></td>';
                $txt .= '</tr>';
                foreach ($data["vinculos"] as $v) {
                    if ($v["vinculootros"] == $tiposocios) {
                        $cuotas = $v["cuotasconst"];
                        $valor = $v["valorconst"];
                        if ($v["cuotasref"] != 0) {
                            $cuotas = $v["cuotasref"];
                            $valor = $v["valorref"];
                        }
                        $txt .= '<tr align="center">';
                        $txt .= '<td width="35%">' . $v["nombreotros"] . '</td>';
                        if ($v["idtipoidentificacionotros"] == '2') {
                            $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                            $txt .= '<td width="25%">' . retornarTxtTipoIde($v["idtipoidentificacionotros"]) . '-' . $sp["identificacion"] . '-' . $sp["dv"] . '</td>';
                        } else {
                            if ($v["idtipoidentificacionotros"] == '7') {
                                $txt .= '<td width="25%">********</td>';
                            } else {
                                if ($v["idtipoidentificacionotros"] == 'R') {
                                    $txt .= '<td width="25%">' . retornarTxtTipoIde($v["idtipoidentificacionotros"]) . '-' . $v["identificacionotros"] . '</td>';
                                } else {
                                    if ($v["idtipoidentificacionotros"] == '1') {
                                        $txt .= '<td width="25%">' . retornarTxtTipoIde($v["idtipoidentificacionotros"]) . '-' . number_format($v["identificacionotros"], 0) . '</td>';
                                    } else {
                                        $txt .= '<td width="25%">' . retornarTxtTipoIde($v["idtipoidentificacionotros"]) . '-' . ($v["identificacionotros"]) . '</td>';
                                    }
                                }
                            }
                        }
                        if ($cuotas != 0) {
                            if ($cuotas == intval($cuotas)) {
                                $txt .= '<td width="20%">' . $cuotas . '</td>';
                            } else {
                                $ac = explode(".", $cuotas);
                                if (!isset($ac[1])) {
                                    $txt .= '<td width="20%">' . $cuotas . '</td>';
                                } else {
                                    if (strlen($ac[1]) > 2) {
                                        $txt .= '<td width="20%">' . truncarDecimales($cuotas, 3) . '</td>';
                                    } else {
                                        $txt .= '<td width="20%">' . truncarDecimales($cuotas, 2) . '</td>';
                                    }
                                }
                            }
                        } else {
                            $txt .= '<td width="20%">&nbsp;</td>';
                        }
                        $txt .= '<td width="20%">$' . truncarDecimales($valor) . '</td>';
                        $txt .= '</tr>';
                    }
                }
                $txt .= '</table>';
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Reseña a casa principal
// *************************************************************************** //
function armarCertificaResenaCasaPrincipal($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["organizacion"] == '08' || ($data["categoria"] == '2' || $data["categoria"] == '3')) {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - RESEÑA A CASA PRINCIPAL</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'QUE LA INFORMACION REFERENTE A LA CASA PRINCIPAL ES LA SIGUIENTE:';
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
        $txt = '<strong>NOMBRE CASA PRINCIPAL : </strong>' . $data["cprazsoc"] . '<br>';
        if ($data["cpcodcam"] != '') {
            $sp = \funcionesGenerales::separarDv($data["cpnumnit"]);
            $txt .= '<strong>IDENTIFICACIÓN : </strong>' . $sp["identificacion"] . '-' . $sp["dv"] . '<br>';
        } else {
            $txt .= '<strong>IDENTIFICACIÓN : </strong>' . $data["cpnumnit"] . '<br>';
        }
        if ($data["cpdircom"] != '') {
            $txt .= '<strong>DIRECCIÓN : </strong>' . $data["cpdircom"] . '<br>';
        }
        if ($data["cpcodmun"] != '') {
            if ($data["cpcodmun"] != '99999') {
                $txt .= '<strong>DOMICILIO : </strong>' . retornarNombreMunicipioMysqliApi($mysqli, $data["cpcodmun"]) . '<br>';
            } else {
                $txt .= '<strong>DOMICILIO : </strong>FUERA DEL PAÍS<br>';
            }
        }
        if (ltrim($data["cpcodcam"], "0") != '') {
            $txt .= '<strong>CAMARA DE COMERCIO : </strong>' . retornarNombreCamaraMysqliApi($mysqli, $data["cpcodcam"]) . '<br>';
        }
        if (ltrim($data["cpnummat"], "0") != '') {
            $txt .= '<strong>MATRÍCULA NÚMERO : </strong>' . $data["cpnummat"] . '<br>';
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
    }
}

// *************************************************************************** //
// Certifica Oposición a la enajenación
// *************************************************************************** //
function armarCertificaOposicionEnajenacion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneoposicion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '036') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneoposicion = 'si';
            }
        }
    }
    if ($tieneoposicion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - OPOSICIONES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '036') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Renuncias y Retiros 0732, 1121, 1731
// *************************************************************************** //
function armarCertificaRenunciasRetiros($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    // 2017-09-18: JINT: Se inactiva por solicitud de la CC Manizales. Es muy 
    // complejo manejarlo como un acto de certificaciónm automático    
    return true;

    //
    $tienerenuncias = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '064') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tienerenuncias = 'si';
            }
        }
    }
    if ($tienerenuncias == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - RENUNCIAS Y RETIROS</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '064') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Renuncias y Retiros 0731
// *************************************************************************** //
function armarCertificaAccionSocialResponsabilidad($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneacr = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '065') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneacr = 'si';
            }
        }
    }
    if ($tieneacr == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - ACCIONES SOCIALES DE RESPONSABILIDAD</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '065') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Resoluciones
// *************************************************************************** //
function armarCertificaResoluciones($pdf, $data, $mysqli = null) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneoposicion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '044') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneoposicion = 'si';
            }
        }
    }
    if ($tieneoposicion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - RESOLUCIONES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '044') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Providencias
// *************************************************************************** //
function armarCertificaProvidencias($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneoposicion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '039') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneoposicion = 'si';
            }
        }
    }
    if ($tieneoposicion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - PROVIDENCIAS</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '039') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica actividad
// *************************************************************************** //
function armarCertificaActividad($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if (
            $data["ciius"][1] != '' ||
            $data["ciius"][2] != '' ||
            $data["ciius"][3] != '' ||
            $data["ciius"][4] != ''
    ) {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - ACTIVIDAD ECONÓMICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }

        if (
                $data["desactiv"] != '' && ($data["organizacion"] == '01' || $data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3')
        ) {
            $txt = '<strong>DESCRIPCIÓN DE LA ACIVIDAD ECONÓMICA : </strong>' . $data["desactiv"];
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $pdf->Ln();
        }

        $txt = '<strong>ACTIVIDAD PRINCIPAL : </strong>' . $data["ciius"][1] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $data["ciius"][1]));
        if ($data["ciius"][2] != '') {
            $txt .= '<br><strong>ACTIVIDAD SECUNDARIA : </strong>' . $data["ciius"][2] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $data["ciius"][2]));
        }
        if ($data["ciius"][3] != '') {
            $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $data["ciius"][3] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $data["ciius"][3]));
        }
        if ($data["ciius"][4] != '') {
            $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $data["ciius"][4] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $data["ciius"][4]));
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
    } else {
        if (isset($data["ciius3"])) {
            if (
                    $data["ciius3"][1] != '' ||
                    $data["ciius3"][2] != '' ||
                    $data["ciius3"][3] != '' ||
                    $data["ciius3"][4] != ''
            ) {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA - ACTIVIDAD ECONÓMICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }

                $txt = '<strong>ACTIVIDAD PRINCIPAL : </strong>' . $data["ciius3"][1] . ' ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $data["ciius3"][1], '3.1'));
                if ($data["ciius3"][2] != '') {
                    $txt .= '<br><strong>ACTIVIDAD SECUNDARIA : </strong>' . $data["ciius3"][2] . ' ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $data["ciius3"][2], '3.1'));
                }
                if ($data["ciius3"][3] != '') {
                    $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $data["ciius3"][3] . ' ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $data["ciius3"][3], '3.1'));
                }
                if ($data["ciius3"][4] != '') {
                    $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $data["ciius3"][4] . ' ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $data["ciius3"][4], '3.1'));
                }
                $pdf->writeHTML($txt, true, false, true, false, 'L');
                $pdf->Ln();
                $txt = 'EL COMERCIANTE NO HA ACTUALIZADO LOS CÓDIGOS DE ACTIVIDAD ECONÓMICA DE LA VERSION 3.1 A.C A LA VERSION 4 A.C COMO LO ESTABLECE LA RESOLUCION DIAN NRO. 0139 DEL 21 DE NOVIEMBRE DE 2012.';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Certifica actividad
// *************************************************************************** //
function armarCertificaTransporte($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $estransportecarga = 'no';
    $estransporteespecial = 'no';
    $certificartransportecarga = 'no';
    $certificartransporteespecial = 'no';

    //
    if (
            $data["ciius"][1] == 'H4923' ||
            $data["ciius"][2] == 'H4923' ||
            $data["ciius"][3] == 'H4923' ||
            $data["ciius"][4] == 'H4923'
    ) {
        $estransportecarga = 'si';
    }
    if (
            $data["ciius"][1] == 'H4921' ||
            $data["ciius"][2] == 'H4921' ||
            $data["ciius"][3] == 'H4921' ||
            $data["ciius"][4] == 'H4921'
    ) {
        $estransporteespecial = 'si';
    }

    foreach ($data["inscripciones"] as $ins) {
        if ($ins["grupoacto"] == '066') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $certificartransportecarga = 'encontro';
            }
        }
    }

    foreach ($data["inscripciones"] as $ins) {
        if ($ins["grupoacto"] == '067') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $certificartransporteespecial = 'encontro';
            }
        }
    }

    if ($estransportecarga == 'si') {
        $estransportecarga = 'falta';
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '066') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $estransportecarga = 'encontro';
                }
            }
        }
    }

    if ($estransporteespecial == 'si') {
        $estransporteespecial = 'falta';
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '067') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $estransporteespecial = 'encontro';
                }
            }
        }
    }

    if ($estransportecarga == 'encontro' || $certificartransportecarga == 'encontro') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - SERVICIO PUBLICO DE TRANSPORTE EN LA MODALIDAD DE CARGA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '066') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    if ($ins["idmunidoc"] != '') {
                        if (trim($ins["txoridoc"]) != '') {
                            $txtmunicipio = 'EN ' . retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"]);
                            $txt = 'MEDIANTE INSCRIPCION NO. ' . $ins["nreg"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'SE REGISTRO EL ACTO ADMINISTRATIVO NO. ' . $ins["ndoc"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', EXPEDIDO POR ';
                            $txt .= $ins["txoridoc"] . ' ' . $txtmunicipio . ', QUE LO HABILITA PARA PRESTAR EL SERVICIO ';
                            $txt .= 'PUBLICO DE TRANSPORTE TERRESTRE AUTOMOTOR EN LA MODALIDAD DE CARGA.';
                        } else {
                            $txtmunicipio = 'EXPEDIDO EN ' . retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"]);
                            $txt = 'MEDIANTE INSCRIPCION NO. ' . $ins["nreg"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'SE REGISTRO EL ACTO ADMINISTRATIVO NO. ' . $ins["ndoc"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', ' . $txtmuncipio;
                            $txt .= 'QUE LO HABILITA PARA PRESTAR EL SERVICIO ';
                            $txt .= 'PUBLICO DE TRANSPORTE TERRESTRE AUTOMOTOR EN LA MODALIDAD DE CARGA.';
                        }
                    } else {
                        if (trim($ins["txoridoc"]) != '') {
                            $txt = 'MEDIANTE INSCRIPCION NO. ' . $ins["nreg"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'SE REGISTRO EL ACTO ADMINISTRATIVO NO. ' . $ins["ndoc"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', EXPEDIDO POR ';
                            $txt .= $ins["txoridoc"] . ', QUE LO HABILITA PARA PRESTAR EL SERVICIO ';
                            $txt .= 'PUBLICO DE TRANSPORTE TERRESTRE AUTOMOTOR EN LA MODALIDAD DE CARGA.';
                        } else {
                            $txt = 'MEDIANTE INSCRIPCION NO. ' . $ins["nreg"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'SE REGISTRO EL ACTO ADMINISTRATIVO NO. ' . $ins["ndoc"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', ';
                            $txt .= 'QUE LO HABILITA PARA PRESTAR EL SERVICIO ';
                            $txt .= 'PUBLICO DE TRANSPORTE TERRESTRE AUTOMOTOR EN LA MODALIDAD DE CARGA.';
                        }
                    }
                }
            }
        }
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }

    if ($estransporteespecial == 'encontro' || $certificartransporteespecial == 'encontro') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - SERVICIO PUBLICO DE TRANSPORTE ESPECIAL</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '067') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    if (trim($ins["txoridoc"]) != '') {
                        $txt = 'MEDIANTE INSCRIPCION NO. ' . $ins["nreg"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                        $txt .= 'SE REGISTRO EL ACTO ADMINISTRATIVO NO. ' . $ins["ndoc"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', EXPEDIDO POR ';
                        $txt .= $ins["txoridoc"] . ', QUE LO HABILITA PARA PRESTAR EL SERVICIO ';
                        $txt .= 'PUBLICO DE TRANSPORTE TERRESTRE AUTOMOTOR ESPECIAL.';
                    } else {
                        $txt = 'MEDIANTE INSCRIPCION NO. ' . $ins["nreg"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                        $txt .= 'SE REGISTRO EL ACTO ADMINISTRATIVO NO. ' . $ins["ndoc"] . ' DE FECHA ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ' ';
                        $txt .= 'QUE LO HABILITA PARA PRESTAR EL SERVICIO ';
                        $txt .= 'PUBLICO DE TRANSPORTE TERRESTRE AUTOMOTOR ESPECIAL.';
                    }
                }
            }
        }
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }

    if ($estransportecarga == 'falta') {
        $resx = armarCertificaTextoLibreClase($pdf, $data, 'CRT-TRACAR', 'CERTIFICA - SERVICIO PUBLICO DE TRANSPORTE DE CARGA', $mysqli);
        if ($resx === false) {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - SERVICIO PUBLICO DE TRANSPORTE DE CARGA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txt = 'NO HA INSCRITO EL ACTO ADMINISTRATIVO QUE LO HABILITA PARA PRESTAR EL SERVICIO ';
            $txt .= 'PUBLICO DE TRANSPORTE TERRESTRE AUTOMOTOR EN LA MODALIDAD DE CARGA';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
    }
}

// *************************************************************************** //
// Certifica información financiera
// *************************************************************************** //
function armarCertificaInformacionFinanciera($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $financiera = 'no';
    if (
            $data["actvin"] != 0 ||
            $data["actcte"] != 0 ||
            $data["actnocte"] != 0 ||
            $data["actval"] != 0 ||
            $data["actotr"] != 0 ||
            $data["actfij"] != 0 ||
            $data["fijnet"] != 0 ||
            $data["acttot"] != 0 ||
            $data["pascte"] != 0 ||
            $data["paslar"] != 0 ||
            $data["pastot"] != 0 ||
            $data["pattot"] != 0 ||
            $data["paspat"] != 0 ||
            $data["balsoc"] != 0 ||
            $data["ingope"] != 0 ||
            $data["ingnoope"] != 0 ||
            $data["cosven"] != 0 ||
            $data["gtoven"] != 0 ||
            $data["gtoadm"] != 0 ||
            $data["gasimp"] != 0 ||
            $data["utiope"] != 0 ||
            $data["utinet"] != 0
    ) {
        $financiera = 'si';
    }
    if ($financiera == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - INFORMACION FINANCIERA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }

        if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
            $txt = 'QUE LA INFORMACION FINANCIERA REPORTADA AL MOMENTO DE LA INSCRIPCIÓN Y/O A LA FECHA DE LA ÚLTIMA RENOVACIÓN FUE:';
        } else {
            $txt = 'QUE LA INFORMACION FINANCIERA REPORTADA AL MOMENTO DE LA MATRÍCULA Y/O A LA FECHA DE LA ÚLTIMA RENOVACIÓN FUE:';
        }
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $sinfinanciera = 'si';
        $txt = '';
        if ($data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3') {
            $txt .= '<strong>ACTIVOS VINCULADOS : </strong>$' . number_format($data["actvin"], 0);
            $sinfinanciera = 'no';
        } else {
            if ($data["acttot"] != 0) {
                $txt .= '<strong>ACTIVOS TOTALES : </strong>$' . number_format($data["acttot"], 0) . '<br>';
                $sinfinanciera = 'no';
            }
            if ($sinfinanciera == 'si') {
                $txt .= '<strong>SIN INFORMACIÓN FINANCIERA EN LOS REGISTROS SISTEMATIZADOS QUE LLEVA LA CÁMARA DE COMERCIO</strong>';
            }
        }
        if ($sinfinanciera == 'si') {
            $pdf->writeHTML($txt, true, false, true, false, 'C');
        } else {
            $pdf->writeHTML($txt, true, false, true, false, 'L');
        }
        $pdf->Ln();
    }
}

// *************************************************************************** //
// Certifica Transformaciones
// *************************************************************************** //
function armarCertificaTransformacion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $transformaciones = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '035') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $transformaciones = 'si';
            }
        }
    }
    if ($transformaciones == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - TRANSFORMACIONES / CONVERSIONES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '035') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    } else {
        armarCertificaTextoLibreClase($pdf, $data, 'CRT-TRANSFORMACION', 'CERTIFICA - TRANSFORMACIONES / CONVERSIONES', $mysqli);
    }
}

// *************************************************************************** //
// Certifica libro XVIII
// *************************************************************************** //
function armarCertificasLibroXVIII($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $actosLibro18 = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["lib"] == 'RM18') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $actosLibro18 = 'si';
            }
        }
    }
    if ($actosLibro18 == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICAS - REESTRUCTURACIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["lib"] == 'RM18') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();

                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Textos varios (certifica 1940)
// *************************************************************************** //    
function armarCertificaTextosVarios($pdf, $data, $titulos = 'si', $mysqli = null) {
    armarCertificaTextoLibreClase($pdf, $data, 'CRT-VARIOS', 'CERTIFICA', $titulos, $mysqli);
}

// *************************************************************************** //
// Textos varios 
// *************************************************************************** //    
function armarCertificaTextoLibre($pdf, $data, $certif = '', $titulo = '', $titulos = 'si', $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if (isset($data["crtsii"][$certif])) {
        if (trim($data["crtsii"][$certif]) != '') {
            if ($titulos == 'si') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>' . $titulo . '</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
            }
            $txt = trim($data["crtsii"][$certif]);
            $txt = str_replace("&nbsp;", "", $txt);
            $txt = strip_tags($txt);
            $pdf->MultiCell(185, 4, $txt . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
        }
    } else {
        if (isset($data["crt"][$certif])) {
            if (trim($data["crt"][$certif]) != '') {
                if ($titulos == 'si') {
                    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                        $txt = '<strong>' . $titulo . '</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    } else {
                        $txt = '<strong>CERTIFICA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    }
                }
                /*
                 * 2017-08-30 WSI Se adopta impresión de parafo utilizando MultiCell, 
                 * lo anterior debido a que writeHTML para parrafos html extenso 
                 * tiene elevado consumo de memoria
                 */
                $txt = trim($data["crt"][$certif]);
                $txt = str_replace("||", "<br>", $txt);
                $txt = str_replace("|", " ", $txt);
                $txt = str_replace("&nbsp;", "", $txt);
                $txt = strip_tags($txt);
                $pdf->MultiCell(185, 4, $txt . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
            }
        }
    }
}

// *************************************************************************** //
// Textos Libres por Clase
// *************************************************************************** //    
function armarCertificaTextoLibreClase($pdf, $data, $clase = '', $titulo = '', $titulos = 'si', $mysqli = null) {

    if (CODIGO_EMPRESA == '20') {
        if (isset($data["crtsii"]["8001"]) && trim($data["crtsii"]["8001"]) != '') {
            unset($data["crtsii"]["0761"]);
            unset($data["crt"]["0761"]);
        }
    }

    if ($titulos == '') {
        $titulos = 'si';
    }
    $retornar = false;
    $txtx = '';

    //wsierra: 2019-05-10 : Se excluye CRT-VARIOS 
    if ($clase != 'AC-SOCIOS' && $clase != 'CRT-REFORMAS' && $clase != 'CRT-REFORMAS-HTML' && $clase != 'CRT-VARIOS') {
        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim($data["crtsii"][$certif]);
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", ""), $txt1);
                    $txt1 = strip_tags($txt1);
                    $txtx .= str_replace(array("&NBSP;","&nbsp;"),"",$txt1);
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = $data["crt"][$certif];
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        $txt1 = strip_tags($txt1);
                        $txtx .= str_replace(array("&NBSP;","&nbsp;"),"",$txt1);
                        
                    }
                }
            }
        }

       
        

        //
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($txtx != '') {
                if ($titulos == 'si') {
                    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {

                       
                        $txt = '<strong>' . $titulo . '</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                            
                    } else {
                        $txt = '<strong>CERTIFICA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    }
                }
            
                //wsierra: 2019-12-18 : Limitar tamaño de parrafo y fraccionarlo CRT-OBJSOC
                $txtrev='';
                if ($clase == 'CRT-OBJSOC') {
                    if(strlen($txtx)>=10000){
                        $txt = '<strong>' . $titulo . '</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();

                        //
                        for($i=0;$i<strlen($txtx);$i++){ 
                            if(($txtx{$i}=='.') && !is_numeric($txtx{$i-1})){
                                $txtrev.=$txtx{$i}.'||';    
                            }else{
                                $txtrev.=$txtx{$i};
                            }  
                        }

                        $parrafos = explode("||", $txtrev);
                        foreach ($parrafos as $prf){
                            $pdf->MultiCell(185, 4, $prf . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
                        }
                        $retornar = true;

                    }else{
                        $pdf->MultiCell(185, 4, $txtx . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
                        $retornar = true;
                    }
                }else{
                    $pdf->MultiCell(185, 4, $txtx . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
                    $retornar = true;
                }
                
                              
        }
    }

    //wsierra: 2019-05-10 : Se incluye CRT-VARIOS
    if ($clase == 'AC-SOCIOS' || $clase == 'CRT-REFORMAS' || $clase == 'CRT-VARIOS') {
        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = str_replace(array("&NBSP;","&nbsp;"),"",trim($data["crtsii"][$certif]));
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", ""), $txt1);
                    $txt1 = strip_tags($txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = str_replace(array("&NBSP;","&nbsp;"),"",$data["crt"][$certif]);
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        $txt1 = strip_tags($txt1);
                        $txtx .= $txt1;
                    }
                }
            }
        }

        //
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($txtx != '') {
            if ($titulos == 'si') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>' . $titulo . '</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
            }
            $pdf->MultiCell(185, 4, $txtx . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
            $retornar = true;
        }
    }

    //wsierra: 2019-05-10 : Se incluye CRT-VARIOS
    if ($clase == 'CRT-REFORMAS-HTML') {
        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim($data["crtsii"][$certif]);
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", ""), $txt1);
                    // $txt1 = strip_tags($txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = $data["crt"][$certif];
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        // $txt1 = strip_tags($txt1);
                        $txtx .= $txt1;
                    }
                }
            }
        }


        //
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($txtx != '') {
            if ($titulos == 'si') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>' . $titulo . '</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
            }
            $pdf->writeHTML($txtx, true, false, true, false, 'J');
            $retornar = true;
        }
    }

    return $retornar;
}

// *************************************************************************** //
// Certifica cambio de domicilio de la matrícula en caso que hubiere
// estado matriculado con anterioridad en otra cámara de comercio
// *************************************************************************** //
function armarCertificaCambioDomicilio($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if (ltrim($data["camant"], "0") != '') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - INFORMACION DEL DOMICILIO ANTERIOR DEL EXPEDIENTE</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'QUE EL COMERCIANTE CAMBIO SU DOMICILIO DESDE ' . retornarNombreMunicipioMysqliApi($mysqli, $data["munant"]) . ', ';
        $txt .= 'DONDE ESTUVO MATRICULADO BAJO EL NUMERO ' . $data["matant"] . " DEL " . strtoupper(\funcionesGenerales::mostrarFechaLetras1($data["fecmatant"]));
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    }
}

// *************************************************************************** //
// Certifica Cambios de Jurisdicción, Acto 9997
// *************************************************************************** //
function armarCertificaCambioJurisdiccion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $camjur9997 = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '068') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $camjur9997 = 'si';
            }
        }
    }
    if ($camjur9997 == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CAMBIOS DE JURISDICCIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '068') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Cambios de domicilio sale, Acto 0497
// *************************************************************************** //
function armarCertificaCambioDomicilioSale($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $camjur9997 = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '069') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $camjur9997 = 'si';
            }
        }
    }
    if ($camjur9997 == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CAMBIOS DE DOMICILIO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '069') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Cambios de nombre
// *************************************************************************** //
function armarCertificaCambiosNombre($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if (!empty($data["nomant"])) {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - RELACION DE NOMBRES QUE HA TENIDO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'QUE LA PERSONA JURÍDICA HA TENIDO LOS SIGUIENTES NOMBRES O RAZONES SOCIALES';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();

        $txt = '<table>';
        $in = 0;
        foreach ($data["nomant"] as $n) {
            $nomx = \funcionesGenerales::borrarPalabrasAutomaticas($n["nom"]);
            $in++;
            $txt .= '<tr align="justify">';
            $txt .= '<td width="10%">' . $in . ')</td>';
            $txt .= '<td width="90%">' . $nomx . '</td>';
            $txt .= '</tr>';
        }
        $nomx = \funcionesGenerales::borrarPalabrasAutomaticas($data["nombre"], $data["complementorazonsocial"]);
        $txt .= '<tr align="justify">';
        $txt .= '<td width="10%">Actual.)</td>';
        $txt .= '<td width="90%">' . $nomx . '</td>';
        $txt .= '</tr>';

        $txt .= '</table>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    } else {
        armarCertificaTextoLibreClase($pdf, $data, 'CRT-CAMNOM', 'CERTIFICA - CAMBIOS DE NOMBRE O RAZON SOCIAL', $mysqli);
    }
}

// *************************************************************************** //
// Certifica Cambios de nombre
// *************************************************************************** //
function armarCertificaCambiosNombreConInscripcion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if (!empty($data["nomant"])) {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - CAMBIOS DE NOMBRE O RAZON SOCIAL</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }

        $in = 0;
        $num1 = 1;
        foreach ($data["nomant"] as $n) {
            $in++;
            $num1++;
            $i = array();
            foreach ($data["inscripciones"] as $ix) {
                if ($n["dup"] == '') {
                    if (
                            $ix["lib"] == $n["lib"] &&
                            $ix["nreg"] == $n["nreg"]
                    ) {
                        $i = $ix;
                    }
                } else {
                    if (
                            $ix["lib"] == $n["lib"] &&
                            $ix["nreg"] == $n["nreg"] &&
                            $ix["dupli"] == $n["dup"]
                    ) {
                        $i = $ix;
                    }
                }
            }
            if (!empty($i)) {
                $txt = descripcionesCambioNombre($mysqli, $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $data["organizacion"], $data["categoria"]);
                if (isset($data["nomant"][$num1])) {
                    $nomx = \funcionesGenerales::borrarPalabrasAutomaticas($n["nom"]);
                    $nomantx = \funcionesGenerales::borrarPalabrasAutomaticas($data["nomant"][$num1]["nom"]);
                    $txt .= $nomx . ' POR ' . $nomantx;
                } else {
                    $nomx = \funcionesGenerales::borrarPalabrasAutomaticas($n["nom"]);
                    $nomantx = \funcionesGenerales::borrarPalabrasAutomaticas($data["nombre"], $data["complementorazonsocial"]);
                    $txt .= $nomx . ' POR ' . $nomantx;
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Certifica disolucion - Acto 0510
// *************************************************************************** //
function armarCertificaDisolucion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienedisolucion = 'no';
    $mostrardisolucion = 'no';
    $dlib = '';
    $dins = '';
    $ddup = '';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '009') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $mostrardisolucion = 'si';
            }
        }
        // Evalua si hay reactivacion
        if ($i["grupoacto"] == '011') {
            $mostrardisolucion = 'no';
        }
    }
    if ($mostrardisolucion == 'si') {
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '009') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $tienedisolucion = 'si';
                }
            }
        }
        if ($tienedisolucion == 'si') {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - DISOLUCIÓN</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '009') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica reactivaciones - Acto 0511
// *************************************************************************** //
function armarCertificaReactivacion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienereactivacion = 'no';
    $lr = '';
    $ir = '';
    $dr = '';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '011' || $i["grupoacto"] == '009') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                if ($i["grupoacto"] == '009') {
                    $lr = '';
                    $ir = '';
                    $dr = '';
                    $tienereactivacion = 'no';
                }
                if ($i["grupoacto"] == '011') {
                    $lr = $i["lib"];
                    $ir = $i["nreg"];
                    $dr = $i["dupli"];
                    $tienereactivacion = 'si';
                }
            }
        }
    }
    if ($tienereactivacion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - REACTIVACIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '011') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    if ($i["lib"] == $lr && $i["nreg"] == $ir && $i["dupli"] == $dr) {
                        $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica liquidacion - Acto 0520
// *************************************************************************** //
function armarCertificaLiquidacion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneliquidacion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '010') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneliquidacion = 'si';
            }
        }
    }
    if ($tieneliquidacion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - LIQUIDACIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '010') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }

    $tieneliquidacion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '070') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneliquidacion = 'si';
            }
        }
    }
    if ($tieneliquidacion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - LIQUIDACIÓN ADICIONAL</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '070') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica depuracion ley 1429
// *************************************************************************** //
function armarCertificaDepuracion1429($pdf, $data, $mysqli = null) {
    if (!isset($data["ctrcancelacion1429"])) {
        $data["ctrcancelacion1429"] = '';
    }
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if (ltrim(trim($data["ctrcancelacion1429"]), "0") == '3') {
        $txt = '<strong>CERTIFICA - DEPURACION LEY 1429</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        $txt = 'QUE EN CUMPLIMIENTO DE LO ESTABLECIDO EN EL ARTÍCULO 50 DE LA ';
        $txt .= 'LEY 1429 DE 2010, ';
        if (
                $data["organizacion"] == '01' ||
                $data["organizacion"] == '02' ||
                $data["categoria"] == '2' ||
                $data["categoria"] == '3'
        ) {
            $txt .= 'SE DECRETO LA CANCELACION DE LA MATRÍCULA MERCANTIL.';
        } else {
            $txt .= 'SE DECRETO DISOLUCIÓN DE LA PERSONA JURÍDICA.';
        }
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    }
}

// *************************************************************************** //
// Certifica depuracion ley 1727
// *************************************************************************** //
function armarCertificaDepuracion1727($pdf, $data, $mysqli = null) {
    if (!isset($data["ctrdepuracion1727"])) {
        $data["ctrdepuracion1727"] = '';
    }
    if (!isset($data["ctrfechadepuracion1727"])) {
        $data["ctrfechadepuracion1727"] = '';
    }
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($data["ctrdepuracion1727"] == 'S') {
        $txt = '<strong>CERTIFICA - DEPURACION LEY 1727</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        if ($data["ctrfechadepuracion1727"] == '') {
            $txt = 'QUE EN CUMPLIMIENTO DE LO ESTABLECIDO EN EL ARTÍCULO 31 DE LA ';
            $txt .= 'LEY 1727 DE 2014, ';
        } else {
            $txt = 'QUE EN CUMPLIMIENTO DE LO ESTABLECIDO EN EL ARTÍCULO 31 DE LA ';
            $txt .= 'LEY 1727 DE 2014, EL ' . \funcionesGenerales::mostrarFechaLetras($data["ctrfechadepuracion1727"]) . ' ';
        }
        if (
                $data["organizacion"] == '01' ||
                $data["organizacion"] == '02' ||
                $data["categoria"] == '2' ||
                $data["categoria"] == '3'
        ) {
            $txt .= 'SE DECRETO LA CANCELACION DE LA MATRÍCULA MERCANTIL.';
        } else {
            $txt .= 'SE DECRETO DISOLUCIÓN DE LA PERSONA JURÍDICA.';
        }
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    }
}

// *************************************************************************** //
// Certifica EN depuracion ley 1727
// *************************************************************************** //
function armarCertificaEnDepuracion1727($pdf, $data, $mysqli = null) {
    if (date("md") >= '0401' && date("md") <= '0430') {
        if (
                $data["estadomatricula"] != 'IC' &&
                $data["estadomatricula"] != 'MC' &&
                $data["estadomatricula"] != 'MF' &&
                $data["estadomatricula"] != 'MG' &&
                $data["estadomatricula"] != 'NA'
        ) {
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            if ($data["ultanoren"] < (date("Y") - 4)) {
                if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
                    $txt = '<strong>CERTIFICA - DEPURACION LEY 1727</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                    $txt = 'A LA FECHA DE EXPEDICIÓN DE ESTE CERTIFICADO, ESTE REGISTRO SE ENCUENTRA EN PROCESO ';
                    $txt .= 'DE DEPURACIÓN EN CUMPLIMIENTO DE LO ESTABLECIDO EN EL ARTÍCULO 31 DE LA LEY 1727 DE 2014, ';
                    $txt .= 'LO QUE EVENTUALMENTE PUEDE AFECTAR EL CONTENIDO DE LA INFORMACIÓN QUE CONSTA EN EL MISMO.';
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA - DEPURACION LEY 1727</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                    $txt = 'A LA FECHA DE EXPEDICIÓN DE ESTE CERTIFICADO, ESTA MATRÍCULA MERCANTIL SE ENCUENTRA EN PROCESO ';
                    $txt .= 'DE DEPURACIÓN EN CUMPLIMIENTO DE LO ESTABLECIDO EN EL ARTÍCULO 31 DE LA LEY 1727 DE 2014, ';
                    $txt .= 'LO QUE EVENTUALMENTE PUEDE AFECTAR EL CONTENIDO DE LA INFORMACIÓN QUE CONSTA EN EL MISMO.';
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Liquidación obligatoria, actos 0650 al 0690
// Grupoacto liquidacion obligatoria
// *************************************************************************** //
function armarCertificaLiquidacionObligatoria($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $liqobli = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '032') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $liqobli = 'si';
            }
        }
    }
    if ($liqobli == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - LIQUIDACIÓN OBLIGATORIA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '032') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Libro XIX
// *************************************************************************** //
function armarCertificaReestructuracion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $libro19 = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["lib"] == 'RM19') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $libro19 = 'si';
            }
        }
    }
    if ($libro19 == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - REORGANIZACION, ADJUDICIACION O LIQUIDACION JUDICIAL</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["lib"] == 'RM19') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica de cancelacion 
// *************************************************************************** //
function armarCertificaCancelacionCambioDomicilio($pdf, $data, $mysqli = null) {
    //
    if (
            $data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'MF' ||
            $data["estadomatricula"] == 'IC'
    ) {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);

        $acto0498 = 'no';
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '069') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $acto0498 = 'si';
                }
            }
        }
        if ($acto0498 == 'si') {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - CAMBIO DE DOMICILIO</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '069') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Cierre
// *************************************************************************** //
function armarCertificaCierre($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    if ($data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        if ($data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'MC') {
            $cancelacion = 'no';
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '026') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $cancelacion = 'si';
                    }
                }
            }
            if ($cancelacion == 'si') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA - CIERRE</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                foreach ($data["inscripciones"] as $i) {
                    if ($i["grupoacto"] == '026') {
                        if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                            $i["crev"] = '0';
                        }
                        if ($i["crev"] != '1' && $i["crev"] != '9') {
                            $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                            $pdf->writeHTML($txt, true, false, true, false, 'J');
                            $pdf->Ln();
                        }
                    }
                }
            }
            if ($cancelacion == 'no') {
                if ($data["fechacancelacion"] != '') {
                    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                        $txt = '<strong>CERTIFICA - ESTADO DE LA MATRICULA MERCANTIL</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    } else {
                        $txt = '<strong>CERTIFICA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    }
                    $txt = 'LA MATRICULA SE ENCUENTRA CANCELADA EN EL REGISTRO PÚBLICO MERCANTIL A PARTIR DEL ' . \funcionesGenerales::mostrarFechaLetras1($data["fechacancelacion"]);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Cancelaciones
// *************************************************************************** //
function armarCertificaCancelacion($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'MC') {
        $cancelacion = 'no';
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '002') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $cancelacion = 'si';
                }
            }
        }
        if ($cancelacion == 'si') {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - CANCELACIÓN</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '002') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                    }
                }
            }
        }
        if ($cancelacion == 'no') {
            if ($data["fechacancelacion"] != '') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA - CANCELACIÓN</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                $txt = 'LA MATRICULA SE ENCUENTRA CANCELADA EN EL REGISTRO PÚBLICO MERCANTIL A PARTIR DEL ' . \funcionesGenerales::mostrarFechaLetras1($data["fechacancelacion"]);
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Certifica Cesacion Actividad
// *************************************************************************** //
function armarCertificaCesacionActividad($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {
        $cesacion = 'no';
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '071') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $cesacion = 'si';
                }
            }
        }
        if ($cesacion == 'si') {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - CESACIÓN DE ACTIVIDAD COMERCIAL</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '071') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Reactivacion Actividad
// *************************************************************************** //
function armarCertificaReactivacionActividad($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {
        $cesacion = 'no';
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '073') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $cesacion = 'si';
                }
            }
        }
        if ($cesacion == 'si') {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - REACTIVACIÓN DE ACTIVIDAD COMERCIAL</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '073') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Fusiones
// *************************************************************************** //
function armarCertificaFusiones($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $fusion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '016') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $fusion = 'si';
            }
        }
    }
    if ($fusion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - FUSIONES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '016') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    } else {
        armarCertificaTextoLibreClase($pdf, $data, 'CRT-FUSION', 'CERTIFICA - FUSIONES', $mysqli);
    }
}

// *************************************************************************** //
// Certifica Escisiones
// *************************************************************************** //
function armarCertificaEscisiones($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $fusion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '015') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $fusion = 'si';
            }
        }
    }
    if ($fusion == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - ESCISIONES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '015') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripciones($ysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    } else {
        armarCertificaTextoLibreClase($pdf, $data, 'CRT-ESCISION', 'CERTIFICA - ESCISIONES', $mysqli);
    }
}

// *************************************************************************** //
// Certifica de Vigencia
// *************************************************************************** //
function armarCertificaVigencia($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    // En caso de personas naturales
    if ($data["organizacion"] == '01') {
        return true;
    }

    // En caso de sucursales de sociedad extranjera
    // if ($data["organizacion"] == '08' || $data["categoria"] != '1') {
    //     return true;
    // }
    // En caso de no ser principales
    if ($data["categoria"] != '1') {
        return true;
    }

    $estextual = 'no';

    // En caso de matrícula activa
    if (
            $data["estadomatricula"] == 'MA' || $data["estadomatricula"] == 'MI' ||
            $data["estadomatricula"] == 'MR' || $data["estadomatricula"] == 'IA' ||
            $data["estadomatricula"] == 'II' || $data["estadomatricula"] == 'IR'
    ) {
        if ($data["disueltaporvencimiento"] == 'si') {
            $mostrar = 'si';
            if ($data["disueltaporacto510"] == 'si') {
                if ($data["fechaacto510"] < $data["fechavencimiento"]) {
                    $mostrar = 'no';
                }
            }
            if ($mostrar == 'si') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA - VIGENCIA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                if (ltrim(trim($data["fechavencimiento"]), "0") != '') {
                    if (ltrim(trim($data["fechavencimiento"]), "0") != '99999997') {
                        $txt = 'QUE LA PERSONA JURÍDICA SE ENCUENTRA DISUELTA POR VENCIMIENTO DEL TÉRMINO DE ';
                        $txt .= 'DURACIÓN, QUE SU VIGENCIA FUE HASTA EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"])));
                    } else {
                        $estextual = 'si';
                    }
                } else {
                    $txt = 'QUE LA PERSONA JURÍDICA SE ENCUENTRA DISUELTA POR VENCIMIENTO DEL TÉRMINO DE ';
                    $txt .= 'DURACIÓN.';
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        } else {
            if ($data["disueltaporacto510"] == 'si') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA - VIGENCIA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                if (ltrim(trim($data["fechavencimiento"]), "0") != '') {
                    if ($data["fechavencimiento"] > date("Ymd")) {
                        $txt = 'QUE LA PERSONA JURÍDICA SE ENCUENTRA DISUELTA Y EN CAUSAL DE LIQUIDACIÓN. ';
                    } else {
                        $txt = 'QUE LA PERSONA JURÍDICA SE ENCUENTRA DISUELTA Y EN CAUSAL DE LIQUIDACIÓN. ';
                        $txt .= 'QUE SU VIGENCIA ERA HASTA EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"])));
                    }
                } else {
                    $txt = 'QUE LA PERSONA JURÍDICA SE ENCUENTRA DISUELTA Y EN CAUSAL DE LIQUIDACIÓN. ';
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
        if ($data["disueltaporvencimiento"] != 'si' && $data["disueltaporacto510"] != 'si') {
            if (ltrim(trim($data["fechavencimiento"]), "0") == '' || ltrim(trim($data["fechavencimiento"]), "0") == '9999997') {
                $estextual = 'si';
            } else {
                if (ltrim(trim($data["fechavencimiento"]), "0") != '' && ltrim(trim($data["fechavencimiento"]), "0") != '99999997' && ltrim(trim($data["fechavencimiento"]), "0") != '9999998' && ltrim(trim($data["fechavencimiento"]), "0") != '99999999') {
                    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                        $txt = '<strong>CERTIFICA - VIGENCIA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    } else {
                        $txt = '<strong>CERTIFICA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    }
                    $txt = 'QUE LA DURACIÓN DE LA PERSONA JURÍDICA (VIGENCIA) ES HASTA EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"])));
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                } else {
                    if (ltrim(trim($data["fechavencimiento"]), "0") != '99999999') {
                        $estextual = 'no';
                        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
                            if ($dtax["clase"] == 'CRT-VIGENCIA') {
                                if ((isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') ||
                                        isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != ''
                                ) {
                                    $estextual = 'si';
                                }
                            }
                        }
                    }
                    if ($estextual == 'no') {
                        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                            $txt = '<strong>CERTIFICA - VIGENCIA</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        } else {
                            $txt = '<strong>CERTIFICA</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        }
                        $txt = 'VIGENCIA: QUE EL TÉRMINO DE DURACIÓN DE LA PERSONA JURÍDICA ES INDEFINIDO.';
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }

    // En caso de matrícula cancelada
    if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'IC') {
        if ($data["disueltaporvencimiento"] == 'si') {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - VIGENCIA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            if (ltrim(trim($data["fechavencimiento"]), "0") != '') {
                if (ltrim(trim($data["fechavencimiento"]), "0") != '99999997') {
                    if ($data["fechavencimiento"] <= date("Ymd")) {
                        $txt = 'QUE LA VIGENCIA DE LA PERSONA JURIDICA FUE HASTA EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"])));
                    } else {
                        $txt = 'QUE LA VIGENCIA DE LA PERSONA JURIDICA ES HASTA EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"])));
                    }
                } else {
                    $estextual = 'si';
                }
            } else {
                $txt = 'QUE LA PERSONA JURÍDICA FUE DISUELTA POR VENCIMIENTO DEL TÉRMINO DE ';
                $txt .= 'DURACIÓN.';
            }
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
        } else {
            if ($data["disueltaporacto510"] == 'si') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA - VIGENCIA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                if (ltrim(trim($data["fechavencimiento"]), "0") != '' && $data["fechavencimiento"] != '99999997' && $data["fechavencimiento"] != '99999999') {
                    $txt = 'QUE SU VIGENCIA ERA HASTA EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"])));
                } else {
                    $txt = 'QUE LA DURACION DE LA PERSONA JURÍDICA ERA INDEFINIDA. ';
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }

        if ($data["disueltaporvencimiento"] != 'si' && $data["disueltaporacto510"] != 'si') {
            if (ltrim(trim($data["fechavencimiento"]), "0") == '99999997' || ltrim(trim($data["fechavencimiento"]), "0") == '9999997') {
                $estextual = 'si';
            } else {
                if (ltrim(trim($data["fechavencimiento"]), "0") != '' && ltrim(trim($data["fechavencimiento"]), "0") != '99999997' && ltrim(trim($data["fechavencimiento"]), "0") != '9999997' && ltrim(trim($data["fechavencimiento"]), "0") != '99999999') {
                    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                        $txt = '<strong>CERTIFICA - VIGENCIA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    } else {
                        $txt = '<strong>CERTIFICA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    }
                    if ($data["fechavencimiento"] <= date("Ymd")) {
                        $txt = 'QUE LA VIGENCIA DE LA PERSONA JURIDICA FUE HASTA EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"])));
                    } else {
                        $txt = 'QUE LA VIGENCIA DE LA PERSONA JURIDICA ES HASTA EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"])));
                    }
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                } else {
                    if (ltrim(trim($data["fechavencimiento"]), "0") == '' || ltrim(trim($data["fechavencimiento"]), "0") == '99999999') {
                        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                            $txt = '<strong>CERTIFICA - VIGENCIA</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        } else {
                            $txt = '<strong>CERTIFICA</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        }
                        $txt = 'QUE LA DURACIÓN DE LA PERSONA JURÍDICA ERA POR TÉRMINO INDEFINIDO.';
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }

    if ($estextual == 'si') {
        return false;
    }
    return true;
}

// *************************************************************************** //
// Certifica de Vigilancia y control
// *************************************************************************** //
function armarCertificaVigilanciaControl($pdf, $data, $mysqli = null) {
    if ($data["categoria"] == '1') {
        if (ltrim(trim($data["vigcontrol"]), "0") != '') {
            $nom = retornarNombreTablasSirepMysqliApi($mysqli, '43', $data["vigcontrol"]);
            if ($nom == '') {
                $nom = trim($data["vigcontrol"]);
            }
            if ($nom != '') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA - ENTIDAD DE VIGILANCIA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                $txt = 'QUE LA ENTIDAD QUE EJERCE LA FUNCIÓN DE INSPECCIÓN, VIGILANCIA Y CONTROL ES ' . $nom;
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

// *************************************************************************** //
// Revisa si la matricula se encuentra embargada
// *************************************************************************** //
function armarCertificaEmbargos($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $certifica900 = 'no';
    if (!empty($data["ctrembargos"])) {
        foreach ($data["ctrembargos"] as $e) {
            if ($e["estado"] == '1') {
                if ($e["esembargo"] == 'S') {
                    if (trim($e["numreg"]) != '') {
                        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                            $txt = '<strong>CERTIFICA - EMBARGOS, DEMANDAS Y MEDIDAS CAUTELARES</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        } else {
                            $txt = '<strong>CERTIFICA</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        }
                        $txMun = '';
                        $ndocext = '';
                        $ins = array();
                        foreach ($data["inscripciones"] as $ins) {
                            if (!isset($e["dupli"]) || $e["dupli"] == '') { // Si no está definido el dupli
                                if (
                                        $e["libro"] == $ins["lib"] &&
                                        $e["numreg"] == $ins["nreg"]
                                ) {
                                    $txMun = retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"]);
                                    $ndocext = $ins["ndocext"];
                                    $ins1 = $ins;
                                }
                            } else {
                                if (isset($e["dupli"]) && $e["dupli"] == '') { // SI el dupli esta definido pero es vacío
                                    if (
                                            $e["libro"] == $ins["lib"] &&
                                            $e["numreg"] == $ins["nreg"]
                                    ) {
                                        $txMun = retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"]);
                                        $ndocext = $ins["ndocext"];
                                        $ins1 = $ins;
                                    }
                                } else {
                                    if (isset($e["dupli"]) && $e["dupli"] != '') { // SI existe dupli y es diferente de vacío
                                        if (
                                                $e["libro"] == $ins["lib"] &&
                                                $e["numreg"] == $ins["nreg"] &&
                                                $e["dupli"] == $ins["dupli"]
                                        ) {
                                            $txMun = retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"]);
                                            $ndocext = $ins["ndocext"];
                                            $ins1 = $ins;
                                        }
                                    }
                                }
                            }
                        }
                        if (defined('CAMARA_SUR_OCCIDENTE') && CAMARA_SUR_OCCIDENTE == 'S') {
                            $txt = descripcionesEmbargos($mysqli, $ins1["acto"], $ins1["tdoc"], $ins1["ndoc"], $ins1["ndocext"], $ins1["fdoc"], $ins1["idoridoc"], $ins1["txoridoc"], $txMun, $ins1["lib"], $ins1["nreg"], $ins1["freg"], $ins1["not"], '', '', '', '', '', '');
                        } else {
                            // $txt = descripcionesEmbargos($e["acto"], $e["tipdoc"], $e["numdoc"], $ndocext, $e["fecdoc"], $e["idorigen"], $e["txtorigen"], $txMun, $e["libro"], $e["numreg"], $e["fecinscripcion"], $e["noticia"], '', '', '', '', '', '');
                            $txt = descripcionesEmbargos($mysqli, $ins1["acto"], $ins1["tdoc"], $ins1["ndoc"], $ins1["ndocext"], $ins1["fdoc"], $ins1["idoridoc"], $ins1["txoridoc"], $txMun, $ins1["lib"], $ins1["nreg"], $ins1["freg"], $ins1["not"], '', '', '', '', '', '');
                        }
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                        $certifica900 = 'si';
                    }
                }
            }
        }
    }

    if ($certifica900 == 'no') {
        $resx = armarCertificaTextoLibreClase($pdf, $data, 'CTR-EMBARGOS', 'CERTIFICA - EMBARGOS', $mysqli);
        if ($resx) {
            $certifica900 = 'si';
        }
    }
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'AC-EMBARGOS', 'CERTIFICA - ACLARACION DE EMBARGOS', $mysqli);
    if ($certifica900 == 'no') {
        // Certifica 1000
        $resx = armarCertificaTextoLibreClase($pdf, $data, 'CTR-DEMANDAS', 'CERTIFICA - DEMANDAS', $mysqli);
    }
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'AC-DEMANDAS', 'CERTIFICA - ACLARACION DE DEMANDAS', $mysqli);
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'CTR-MEDCAU', 'CERTIFICA - MEDIDAS CAUTELARES', $mysqli);
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'AC-MEDCAU', 'CERTIFICA - ACLARACION MEDIDAS CAUTELARES', $mysqli);
}

// *************************************************************************** //
// Revisa si los establecimientos históricos y cancelados tienen embargos
// *************************************************************************** //
function armarCertificaEmbargosEstablecimientosCancelados($pdf, $data, $mysqli = null) {
    //
    $sitiene = 'no';

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($data["establecimientosh"] && !empty($data["establecimientosh"])) {
        foreach ($data["establecimientosh"] as $h) {
            $embs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . $h["matriculaestablecimiento"] . "'", "fecinscripcion");
            if ($embs && !empty($embs)) {
                foreach ($embs as $emb) {
                    if ($emb["acto"] == '0900' || $emb["acto"] == '1000') {
                        if ($emb["ctrestadoembargo"] == '1') {
                            if ($sitiene == 'no') {
                                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                                    $txt = '<strong>CERTIFICA - EMBARGOS, DEMANDAS Y MEDIDAS CAUTELARES - SOBRE ESTABLECIMIENTOS CANCELADOS</strong>';
                                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                                    $pdf->Ln();
                                } else {
                                    $txt = '<strong>CERTIFICA</strong>';
                                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                                    $pdf->Ln();
                                }
                                $sitiene = 'si';
                            }
                            if ($emb["dupli"] == '') {
                                $insx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "libro='" . $emb["libro"] . "' and registro='" . $emb["numreg"] . "'");
                            } else {
                                $insx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "libro='" . $emb["libro"] . "' and registro='" . $emb["numreg"] . "' and dupli='" . $emb["dupli"] . "'");
                            }
                            if ($insx && !empty($insx)) {
                                $txMun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $insx["municipiodocumento"] . "'", "ciudad");
                                $txt = descripcionesEmbargos($insx["acto"], $insx["tipodocumento"], $insx["numerodocumento"], $insx["numdocextenso"], $insx["fechadocumento"], $insx["idorigendoc"], $insx["origendocumento"], $txMun, $insx["libro"], $insx["registro"], $insx["fecharegistro"], $insx["noticia"], '', '', '', '', '', '');
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                $pdf->Ln();
                            }
                        }
                    }
                }
            }
        }
    }
}

// *************************************************************************** //
// Revisa establecimientos de comercio
// AQUI VOY
// *************************************************************************** //
function armarCertificaEstablecimientos($pdf, $data, $mysqli = null) {

    // Conexion con la BD
    $cerrarMysql = 'no';
    if ($mysqli == null) {
        $mysqli = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        $cerrarMysql = 'si';
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneestablecimientos = 'no';
    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && ($data["categoria"] == '' || $data["categoria"] == '0' || $data["categoria"] == '1'))) { {
            if (!empty($data["establecimientos"])) {
                foreach ($data["establecimientos"] as $e) {
                    if ($e["estadodatosestablecimiento"] == 'MA' || $e["estadodatosestablecimiento"] == 'MI') {
                        $tieneestablecimientos = 'si';
                    }
                }
            }
        }
    }
    if ($tieneestablecimientos == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - ESTABLECIMIENTOS</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'QUE ES PROPIETARIO DE LOS SIGUIENTES ESTABLECIMIENTOS DE COMERCIO EN LA JURISDICCIÓN DE ESTA CÁMARA DE COMERCIO:';
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();

        //
        foreach ($data["establecimientos"] as $e) {
            if ($e["estadodatosestablecimiento"] == 'MA' || $e["estadodatosestablecimiento"] == 'MI') {
                $txt = '<br><strong>*** NOMBRE ESTABLECIMIENTO : </strong>' . $e["nombreestablecimiento"] . '<br>';
                $txt .= '<strong>MATRICULA  : </strong>' . $e["matriculaestablecimiento"] . '<br>';
                $txt .= '<strong>FECHA DE MATRICULA  : </strong>' . $e["fechamatricula"] . '<br>';
                $txt .= '<strong>FECHA DE RENOVACION  : </strong>' . $e["fecharenovacion"] . '<br>';
                $txt .= '<strong>ULTIMO AÑO RENOVADO  : </strong>' . $e["ultanoren"] . '<br>';
                $txt .= '<strong>DIRECCION  : </strong>' . $e["dircom"] . '<br>';
                if (trim($e["barriocom"]) != '') {
                    $txt .= '<strong>BARRIO  : </strong>' . retornarNombreBarrioMysqliApi($mysqli, $e["muncom"], $e["barriocom"]) . '<br>';
                }

                $txt .= '<strong>MUNICIPIO  : </strong>' . $e["muncom"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $e["muncom"]) . '<br>';
                if ($e["telcom1"] != '') {
                    $txt .= '<strong>TELEFONO 1  : </strong>' . $e["telcom1"] . '<br>';
                }
                if ($e["telcom2"] != '') {
                    $txt .= '<strong>TELEFONO 2  : </strong>' . $e["telcom2"] . '<br>';
                }
                if ($e["telcom3"] != '') {
                    $txt .= '<strong>TELEFONO 3  : </strong>' . $e["telcom3"] . '<br>';
                }
                if ($e["emailcom"] != '') {
                    $txt .= '<strong>CORREO ELECTRONICO  : </strong>' . $e["emailcom"] . '<br>';
                }
                $txt .= '<strong>ACTIVIDAD PRINCIPAL : </strong>' . $e["ciiu1"] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu1"]));
                if ($e["ciiu2"] != '') {
                    $txt .= '<br><strong>ACTIVIDAD SECUNDARIA : </strong>' . $e["ciiu2"] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu2"]));
                }
                if ($e["ciiu3"] != '') {
                    $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $e["ciiu3"] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu3"]));
                }
                if ($e["ciiu4"] != '') {
                    $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $e["ciiu4"] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu4"]));
                }
                $txt .= '<br><strong>VALOR DEL ESTABLECIMIENTO : </strong>' . number_format($e["valest"]);
                if ($e["embargado"] == 'SI') {
                    $txt .= '<br><strong>EMBARGOS, DEMANDAS Y MEDIDAS CAUTELARES</strong>';
                    foreach ($e["embargos"] as $em) {
                        $txt .= '<br><strong>** LIBRO : </strong>' . $em["libroembargo"] .
                                ', <strong>INSCRIPCION: </strong>' . $em["registroembargo"] .
                                ', <strong>FECHA: </strong>' . $em["fechaembargo"] .
                                ', <strong>ORIGEN: </strong>' . $em["txtorigenembargo"] .
                                ', <strong>NOTICIA: </strong>' . $em["noticiaembargo"];
                    }
                }
                if (trim($e["ideadministrador"]) != '') {
                    $txt .= '<br><strong>ADMINISTRADOR : </strong>' . $e["ideadministrador"] . ' - ' . $e["nombreadministrador"];
                }
                if (trim($e["idearrendatario"]) != '') {
                    $txt .= '<br><strong>ARRENDATARIO : </strong>' . $e["idearrendatario"] . ' - ' . $e["nombrearrendatario"];
                }
                $txt .= '<br>';

                // 2017-11-27
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                // $txt = str_replace("<br>", chr(13) . chr(10), $txt);
                // $txt = strip_tags($txt);
                // $pdf->MultiCell(185, 4, $txt . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
                $exptem = array();
                $exptem["crt"] = array();
                $exptem["crtsii"] = array();
                foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
                    if ($dtax["clase"] == 'CRT-VARIOS') {
                        $exptem["crt"][$certif] = retornarRegistroMysqliApi($mysqli, 'mreg_est_certificas', "matricula='" . $e["matriculaestablecimiento"] . "' and idcertifica='" . $certif . "'", "texto");
                        if ($exptem["crt"][$certif] == false || empty($exptem["crt"][$certif])) {
                            $exptem["crt"][$certif] = '';
                        }
                        $exptem["crtsii"][$certif] = retornarRegistroMysqliApi($mysqli, 'mreg_certificas_sii', "expediente='" . $e["matriculaestablecimiento"] . "' and idcertifica='" . $certif . "'", "contenido");
                        if ($exptem["crtsii"][$certif] == false || empty($exptem["crtsii"][$certif])) {
                            $exptem["crtsii"][$certif] = '';
                        }
                    }
                }
                armarCertificaTextosVarios($pdf, $exptem, 'no', $mysqli);
            }
        }
    }

    if ($cerrarMysql == 'si') {
        $mysqli->close();
    }
}

// *************************************************************************** //
// Revisa establecimientos de comercio
// *************************************************************************** //
function armarCertificaEstablecimientosArrendados($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneestablecimientos = 'no';
    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && ($data["categoria"] == '' || $data["categoria"] == '0' || $data["categoria"] == '1'))) {
        if (!empty($data["establecimientosarrendados"])) {
            $tieneestablecimientos = 'si';
        }
    }
    if ($tieneestablecimientos == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - ESTABLECIMIENTOS QUE TIENE EN ARRIENDO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'QUE ES COMERCIANTE TIENE EN ARRIENDO LOS SIGUIENTES ESTABLECIMIENTOS DE COMERCIO:';
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
        foreach ($data["establecimientosarrendados"] as $e) {
            $txt = '<br><strong>*** NOMBRE ESTABLECIMIENTO : </strong>' . $e["nombre"] . '<br>';
            $txt .= '<strong>MATRICULA  : </strong>' . $e["matricula"] . '<br>';
            $txt .= '<strong>FECHA DE MATRICULA  : </strong>' . $e["fechamatricula"] . '<br>';
            $txt .= '<strong>FECHA DE RENOVACION  : </strong>' . $e["fecharenovacion"] . '<br>';
            $txt .= '<strong>ULTIMO AÑO RENOVADO  : </strong>' . $e["ultanoren"] . '<br>';
            $txt .= '<strong>DIRECCION  : </strong>' . $e["dircom"] . '<br>';
            if (trim($e["barriocom"]) != '') {
                $txt .= '<strong>BARRIO  : </strong>' . retornarNombreBarrioMysqliApi($mysqli, $e["muncom"], $e["barriocom"]) . '<br>';
            }

            $txt .= '<strong>MUNICIPIO  : </strong>' . $e["muncom"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $e["muncom"]) . '<br>';
            if ($e["telcom1"] != '') {
                $txt .= '<strong>TELEFONO 1  : </strong>' . $e["telcom1"] . '<br>';
            }
            if ($e["telcom2"] != '') {
                $txt .= '<strong>TELEFONO 2  : </strong>' . $e["telcom2"] . '<br>';
            }
            if ($e["telcom3"] != '') {
                $txt .= '<strong>TELEFONO 3  : </strong>' . $e["telcom3"] . '<br>';
            }
            if ($e["emailcom"] != '') {
                $txt .= '<strong>CORREO ELECTRONICO  : </strong>' . $e["emailcom"] . '<br>';
            }
            $txt .= '<strong>ACTIVIDAD PRINCIPAL : </strong>' . $e["ciiu1"] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu1"]));
            if ($e["ciiu2"] != '') {
                $txt .= '<br><strong>ACTIVIDAD SECUNDARIA : </strong>' . $e["ciiu2"] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu2"]));
            }
            if ($e["ciiu3"] != '') {
                $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $e["ciiu3"] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu3"]));
            }
            if ($e["ciiu4"] != '') {
                $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $e["ciiu4"] . ' - ' . strtoupper(\funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu4"]));
            }
            $txt .= '<br><strong>VALOR DEL ESTABLECIMIENTO : </strong>' . number_format($e["valest"]);
            $txt .= '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }
    }
}

// *************************************************************************** //
// Revisa sucursales y agencias
// *************************************************************************** //
function armarCertificaSucursalesAgencias($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienesucage = 'no';
    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && ($data["categoria"] == '' || $data["categoria"] == '0' || $data["categoria"] == '1'))) { {
            if (!empty($data["sucursalesagencias"])) {
                foreach ($data["sucursalesagencias"] as $e) {
                    if ($e["estado"] == 'MA' || $e["estado"] == 'MI' || $e["estado"] == 'IA' || $e["estado"] == 'II') {
                        $tienesucage = 'si';
                    }
                }
            }
        }
    }
    if ($tienesucage == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - SUCURSALES Y AGENCIAS</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'QUE ES PROPIETARIO DE LAS SIGUIENTES SUCURSALES Y AGENCIAS EN LA JURISDICCIÓN DE ESTA CÁMARA DE COMERCIO:';
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
        foreach ($data["sucursalesagencias"] as $e) {
            if ($e["estado"] == 'MA' || $e["estado"] == 'MI' || $e["estado"] == 'IA' || $e["estado"] == 'II') {
                $xcat = '';
                if ($e["categoria"] == '2') {
                    $xcat = 'SUCURSAL';
                }
                if ($e["categoria"] == '3') {
                    $xcat = 'AGENCIA';
                }
                $txt = '<br><strong>*** NOMBRE : </strong>' . $e["nombresucage"] . '<br>';
                $txt .= '<strong>CATEGORÍA : </strong>' . $xcat . '<br>';
                $txt .= '<strong>MATRÍCULA  : </strong>' . $e["matriculasucage"] . '<br>';
                $txt .= '<strong>FECHA DE MATRÍCULA  : </strong>' . $e["fechamatricula"] . '<br>';
                $txt .= '<strong>FECHA DE RENOVACIÓN  : </strong>' . $e["fecharenovacion"] . '<br>';
                $txt .= '<strong>ÚLTIMO AÑO RENOVADO  : </strong>' . $e["ultanoren"] . '<br>';
                $txt .= '<strong>DIRECCION  : </strong>' . $e["dircom"] . '<br>';
                $txt .= '<strong>MUNICIPIO  : </strong>' . $e["muncom"] . ' - ' . retornarNombreMunicipioMysqliApi($mysqli, $e["muncom"]) . '<br>';
                if ($e["telcom1"] != '') {
                    $txt .= '<strong>TELÉFONO 1  : </strong>' . $e["telcom1"] . '<br>';
                }
                if ($e["telcom2"] != '') {
                    $txt .= '<strong>TELÉFONO 2  : </strong>' . $e["telcom2"] . '<br>';
                }
                if ($e["telcom3"] != '') {
                    $txt .= '<strong>TELÉFONO 3  : </strong>' . $e["telcom3"] . '<br>';
                }
                if ($e["emailcom"] != '') {
                    $txt .= '<strong>CORREO ELECTRÓNICO  : </strong>' . $e["emailcom"] . '<br>';
                }
                $txt .= '<strong>ACTIVIDAD PRINCIPAL : </strong>' . $e["ciiu1"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu1"]);
                if ($e["ciiu2"] != '') {
                    $txt .= '<br><strong>ACTIVIDAD SECUNDARIA : </strong>' . $e["ciiu2"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu2"]);
                }
                if ($e["ciiu3"] != '') {
                    $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $e["ciiu3"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu3"]);
                }
                if ($e["ciiu4"] != '') {
                    $txt .= '<br><strong>OTRAS ACTIVIDADES : </strong>' . $e["ciiu4"] . ' - ' . \funcionesGenerales::retornarDescripcionCiiu($mysqli, $e["ciiu4"]);
                }
                $txt .= '<br><strong>ACTIVOS VINCULADOS : </strong>' . number_format($e["actvin"]);
                if ($e["embargado"] == 'SI') {
                    $txt .= '<br><strong>EMBARGOS, DEMANDAS Y MEDIDAS CAUTELARES</strong>';
                    foreach ($e["embargos"] as $em) {
                        $txt .= '<br><strong>** LIBRO : </strong>' . $em["libroembargo"] .
                                ', <strong>INSCRIPCION: </strong>' . $em["registroembargo"] .
                                ', <strong>FECHA: </strong>' . $em["fechaembargo"] .
                                ', <strong>ORIGEN: </strong>' . $em["txtorigenembargo"] .
                                ', <strong>NOTICIA: </strong>' . $em["noticiaembargo"];
                    }
                }
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Representacion legal - en texto certifica 1110 y 1121
// Incluye 1120 - excluye la cc de manizales
// *************************************************************************** //
function armarCertificaRepresentacionLegal($pdf, $data, $mysqli = null) {
    // En caso de manizales se borra el certifica 1120
    if (CODIGO_EMPRESA == '20') {
        if (isset($data["crtsii"]["1120"]) && trim($data["crtsii"]["1120"]) != '') {
            $data["crtsii"]["1120"] = '';
        }
        if (isset($data["crt"]["1120"]) && trim($data["crt"]["1120"]) != '') {
            $data["crt"]["1120"] = '';
        }
    }
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'CRT-REPLEG', 'CERTIFICA - REPRESENTACION LEGAL', $mysqli);
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'AC-REPLEG', 'CERTIFICA - ACLARACION REPRESENTACION LEGAL', $mysqli);
}

// *************************************************************************** //
// Facultades del representante legal - en texto certifica 1300 
// *************************************************************************** //
function armarCertificaFacultadesEliminar($pdf, $data, $mysqli = null) {
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'CRT-FACULTADES', 'CERTIFICA - FACULTADES Y LIMITACIONES', $mysqli);
}

// *************************************************************************** //
// Poderes (certifica 1410 y 1500) 
// *************************************************************************** //  
function armarCertificaCambiosNombreEstablecimientos($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["organizacion"] != '02') {
        return true;
    }
    if (!isset($data["inscripciones"]) || empty($data["inscripciones"])) {
        return true;
    }

    $ican = 0;
    foreach ($data["inscripciones"] as $insc) {
        if ($insc["grupoacto"] == '003' || $insc["grupoacto"] == '072') {
            if ($insc["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $insc["crev"] == '1') {
                $insc["crev"] = '0';
            }
            if ($insc["crev"] != '1' && $insc["crev"] != '9') {
                $cert003 = 'no';
                if ($insc["grupoacto"] == '072') {
                    $cert003 = 'si';
                }
                if ($insc["grupoacto"] == '003') {
                    if ($data["nomant"] && !empty($data["nomant"])) {
                        foreach ($data["nomant"] as $ant) {
                            if ($ant["lib"] == $insc["lib"] && ltrim($ant["nreg"], "0") == ltrim($insc["nreg"], "0")) {
                                $cert003 = 'si';
                            }
                        }
                    }
                }
                if ($cert003 == 'si') {
                    $ican++;
                    if ($ican == 1) {
                        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                            $txt = '<strong>CERTIFICA - ACTUALIZACIÓN DE DATOS</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        } else {
                            $txt = '<strong>CERTIFICA</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        }
                    }
                    $txt = 'QUE EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($insc["freg"])) . ' SE REGISTRÓ PARA EL ESTABLECIMIENTO ';
                    $txt .= 'LA SIGUIENTE ACTUALIZACIÓN : ' . $insc["not"];
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Poderes (certifica 1320, 1410 y 1500)
// *************************************************************************** //  
function armarCertificaPoderes($pdf, $data, $mysqli = null) {
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'CRT-PODER', 'CERTIFICA - PODERES', $mysqli);
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'AC-PODER', 'CERTIFICA - ACLARACION PODERES', $mysqli);
}

// *************************************************************************** //
// Revisa propietarios
// *************************************************************************** //
function armarCertificaPropietarios($pdf, $data, $mysqli = null) {
    require_once (PATH_ABSOLUTO_SITIO . '/api/funcionesGenerales.php');
    require_once (PATH_ABSOLUTO_SITIO . '/api/mysqli.php');

    //
    $cerrarMysqli = 'no';
    if ($mysqli == null) {
        $mysqli = conexionMysqliApi();
        $cerrarMysqli = 'si';
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienepropietarios = 'no';
    $sociedadhecho = 'no';
    if ($data["organizacion"] == '02') {
        foreach ($data["propietarios"] as $e) {
            $tienepropietarios = 'si';
            if ($e["tipopropiedad"] == '1') {
                $sociedadhecho = 'si';
            }
        }
    }

    //
    if ($tienepropietarios == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong>CERTIFICA - PROPIETARIOS</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        if ($data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'MC') {
            $txt = 'QUE EL(LOS) PROPIETARIO(S) DEL ESTABLECIMIENTO DE COMERCIO FUE(RON) :';
        } else {
            $txt = 'QUE LA PROPIEDAD SOBRE EL ESTABLECIMIENTO LA TIENE(N) EL(LOS) SIGUIENTE(S) COMERCIANTES ';
            if ($sociedadhecho == 'si') {
                $txt .= '(EN SOCIEDAD DE HECHO) ';
            }
            $txt .= ': ';
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();

        //

        foreach ($data["propietarios"] as $e) {
            $txt = '<strong>*** NOMBRE DEL PROPIETARIO : </strong>' . $e["nombrepropietario"] . '<br>';
            if ($e["idtipoidentificacionpropietario"] == '2') {
                $sp = \funcionesGenerales::separarDv($e["identificacionpropietario"]);
                $txt .= '<strong>NIT  : </strong>' . $sp["identificacion"] . '-' . $sp["dv"] . '<br>';
            } else {
                $txt .= '<strong>IDENTIFICACIÓN  : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $e["idtipoidentificacionpropietario"] . "'", "descripcion") . ' - ' . $e["identificacionpropietario"] . '<br>';
                if (ltrim($e["nitpropietario"], "0") != '') {
                    $sp = \funcionesGenerales::separarDv($e["nitpropietario"]);
                    $txt .= '<strong>NIT  : </strong>' . $sp["identificacion"] . '-' . $sp["dv"] . '<br>';
                }
            }
            if ($e["camarapropietario"] == CODIGO_EMPRESA || $e["camarapropietario"] == '') {
                if (trim($e["matriculapropietario"]) != '') {
                    if ($e["estadomatriculapropietario"] == 'MA' || $e["estadomatriculapropietario"] == 'MI' || $e["estadomatriculapropietario"] == 'IA') {
                        $txt .= '<strong>MATRICULA  : </strong>' . $e["matriculapropietario"] . '<br>';
                        $txt .= '<strong>FECHA DE MATRICULA  : </strong>' . $e["fecmatripropietario"] . '<br>';
                        $txt .= '<strong>FECHA DE RENOVACION  : </strong>' . $e["fecrenovpropietario"] . '<br>';
                        $txt .= '<strong>ULTIMO AÑO RENOVADO  : </strong>' . $e["ultanorenpropietario"] . '<br>';
                    } else {
                        $txt .= 'ESTUVO INSCRITO/MATRICULADO EN LA CÁMARA DE COMERCIO BAJO EL NÚMERO ' . $e["matriculapropietario"] . '<br>';
                    }
                }
            } else {
                if (trim($e["direccionpropietario"]) != '') {
                    $txt .= '<strong>DIRECCIÓN  : </strong>' . $e["direccionpropietario"] . '<br>';
                }
                if (trim($e["municipiopropietario"]) != '') {
                    $txt .= '<strong>MUNICIPIO  : </strong>' . $e["municipiopropietario"] . ' - ' . retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $e["municipiopropietario"] . "'", "ciudad") . '<br>';
                }
            }
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $pdf->Ln();
        }
    }

    if ($cerrarMysqli == 'si') {
        $mysqli->close();
    }
}

// *************************************************************************** //
// Certifica de prendas - en texto certifica 1010
// *************************************************************************** //
function armarCertificaPrendas($pdf, $data, $mysqli = null) {
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'CRT-PRENDAS', 'CERTIFICA - PRENDAS', $mysqli);
}

// *************************************************************************** //
// Certifica Aclaratoria del patrimonio
// En caso de manizales, si existe certifica 8001 (Capital SAT)
// quita el 0761
// *************************************************************************** //
function armarCertificaAclaratoriaCapitalPatrimonio($pdf, $data, $mysqli = null) {
    if (isset($data["crtsii"]["8001"]) && trim($data["crtsii"]["8001"]) != '') {
        unset($data["crtsii"]["0761"]);
        unset($data["crt"]["0761"]);
    }
    $resx = armarCertificaTextoLibreClase($pdf, $data, 'AC-CAPSOC', 'CERTIFICA - ACLARATORIA CAPITAL Y PATRIMONIOS', $mysqli);
}

// *************************************************************************** //
// Certifica de objeto social - Inscripciones antes del 72
// *************************************************************************** //
function armarCertificaCae($pdf, $data, $mysqli = null) {

    // 
    if ($data["reportealcaldia"] != 'si') {
        return true;
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $txtCuerpo = retornarRegistroMysqliApi($mysqli, 'mreg_alcaldias', "idmunicipio='" . $data["muncom"] . "'", "informa");
    if (trim($txtCuerpo) != '') {
        $txtCuerpo = str_replace("[MUNICIPIO]", retornarNombreMunicipioMysqliApi($mysqli, $data["muncom"]), $txtCuerpo);
        $txtCuerpo = str_replace("[FECHAENVIO]", \funcionesGenerales::mostrarFecha($data["placaalcaldiafecha"]), $txtCuerpo);
        $txtCuerpo = str_replace("[PLACA]", $data["placaalcaldia"], $txtCuerpo);
        $txt = '<strong>INFORMA - REPORTE A ENTIDADES</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->MultiCell(185, 4, $txtCuerpo . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
        return true;
    }

    // 2017-09-28: JINT: Mensaje CAE por defecto
    $txtCuerpo = "QUE LA MATRÍCULA DEL COMERCIANTE Y/O ESTABLECIMIENTO DE COMERCIO ";
    $txtCuerpo .= "LOCALIZADO EN LA DIRECCIÓN QUE APARECE REPORTADA EN ESTE CERTIFICADO, ";
    $txtCuerpo .= "SE INFORMÓ A LAS SECRETARÍAS DE PLANEACIÓN, SALUD, GOBIERNO, ";
    $txtCuerpo .= "HACIENDA MUNICIPAL DE LA ALCALDIA DE " . retornarNombreMunicipioMysqliApi($mysqli, $data["muncom"]) . " Y BOMBEROS, ";
    $txtCuerpo .= "A EXCEPCIÓN DE AQUELLOS CASOS QUE NO APLIQUE. LOS DATOS CONTENIDOS ";
    $txtCuerpo .= "EN ESTA SECCIÓN DE INFORMACIÓN COMPLEMENTARIA, NO HACEN PARTE ";
    $txtCuerpo .= "DEL REGISTRO PÚBLICO MERCANTIL, NI SON CERTIFICADOS POR LA ";
    $txtCuerpo .= "CÁMARA DE COMERCIO EN EJERCICIO DE SUS FUNCIONES LEGALES.";


    // En caso de ser cúcuta
    if ($_SESSION["generales"]["codigoempresa"] == '11') {
        if ($data["placaalcaldia"] != '') {
            $txtCuerpo = "QUE LA MATRÍCULA DEL COMERCIANTE Y/O ESTABLECIMIENTO DE COMERCIO ";
            $txtCuerpo .= "LOCALIZADO EN LA DIRECCIÓN QUE APARECE REPORTADA EN ESTE CERTIFICADO, ";
            $txtCuerpo .= "SE INFORMÓ A LAS SECRETARÍAS DE PLANEACIÓN, SALUD, GOBIERNO, ";
            $txtCuerpo .= "HACIENDA MUNICIPAL DE LA ALCALDIA DE CÚCUTA, ";
            $txtCuerpo .= "A EXCEPCIÓN DE AQUELLOS CASOS QUE NO APLIQUE. LOS DATOS CONTENIDOS ";
            $txtCuerpo .= "EN ESTA SECCIÓN DE INFORMACIÓN COMPLEMENTARIA, NO HACEN PARTE ";
            $txtCuerpo .= "DEL REGISTRO PÚBLICO MERCANTIL, NI SON CERTIFICADOS POR LA ";
            $txtCuerpo .= "CÁMARA DE COMERCIO EN EJERCICIO DE SUS FUNCIONES LEGALES. ";
            $txtCuerpo .= "LA ENTIDAD SOLO HACE PÚBLICO EL CONOCIMIENTO QUE DE ELLOS ";
            $txtCuerpo .= "HA TENIDO. IGUALMENTE LA ENTIDAD A TRAVÉS DEL CENTRO DE ATENCIÓN EMPRESARIAL - CAE, ";
            $txtCuerpo .= "REALIZA LA VERIFICACIÓN DEL USO DE SUELO, A LOS NUEVOS ESTABLECIMIENTOS DE COMERCIO ";
            $txtCuerpo .= "MATRICULADOS POR EL COMERCIANTE. QUE COMO CONSECUENCIA DEL REPORTE REALIZADO POR LA ";
            $txtCuerpo .= "CÁMARA DE COMERCIO, LA ALCALDÍA ASIGNÓ EL NÚMERO " . $data["placaalcaldia"] . " EL ";
            $txtCuerpo .= \funcionesGenerales::mostrarFecha($data["placaalcaldiafecha"]) . ", PARA IDENTIFICAR ESTE NÚMERO DE MATRÍCULA MERCANTIL.";
        }
    }

    // En caso de ser pereira
    if ($_SESSION["generales"]["codigoempresa"] == '27') {
        $txtCuerpo = 'QUE A PARTIR DEL 25 DE SEPTIEMBRE DE 2007, EN EL CENTRO DE ATENCIÓN EMPRESARIAL CAE, A LAS MATRÍCULAS ';
        $txtCuerpo .= 'DE NUEVOS COMERCIANTES Y SUS ESTABLECIMIENTOS DE COMERCIO, CON DOMICILIO PRINCIPAL EN LA CIUDAD DE PEREIRA, ';
        $txtCuerpo .= 'SE LES HACE SIMULTÁNEAMENTE EL REGISTRO ANTE INDUSTRIA Y COMERCIO Y SE LES EFECTÚA LA ASIGNACIÓN DEL CÓDIGO ';
        $txtCuerpo .= 'TRIBUTARIO DE INDUSTRIA Y COMERCIO. ';
        $txtCuerpo .= 'EN EL CAE IGUALMENTE, SE REALIZA LA VERIFICACIÓN DEL USO DEL SUELO A LOS NUEVOS ESTABLECIMIENTOS ';
        $txtCuerpo .= 'DE COMERCIO MATRICULADOS POR EL COMERCIANTE. ';
        $txtCuerpo .= 'ADICIONALMENTE, LA CÁMARA DE COMERCIO DE PEREIRA, A TRAVÉS DE UN APLICATIVO VIRTUAL, NOTIFICA A LAS SECRETARIAS ';
        $txtCuerpo .= 'MUNICIPALES DE: HACIENDA, GOBIERNO, PLANEACIÓN Y SALUD, LA INFORMACIÓN REFERENTE A LOS COMERCIANTES Y ';
        $txtCuerpo .= 'ESTABLECIMIENTOS MATRICULADOS.';
        if ($data["placaalcaldia"] != '') {
            $txtCuerpo .= 'EN DESARROLLO DEL CONVENIO DE INTERCAMBIO DE INFORMACIÓN, LA ALCALDÍA DE PEREIRA AL RECIBIR EL ';
            $txtCuerpo .= 'COMERCIANTE ENVIADO, LE ASIGNÓ EL NÚMERO DE PLACA ' . $data["placaalcaldia"];
        }
    }

    //
    if ($txtCuerpo != '') {
        $txt = '<strong>INFORMA - REPORTE A ENTIDADES MUNICIPALES</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->MultiCell(185, 4, $txtCuerpo . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
    }
}

// *************************************************************************** //
// Informa migración
// *************************************************************************** //
function armarInformaMigracion($pdf) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $txtCuerpo = 'LA CÁMARA DE COMERCIO HA EFECTUADO MIGRACIÓN DE LA INFORMACIÓN DE LOS REGISTROS ';
    $txtCuerpo .= 'PÚBLICOS A UN NUEVO SISTEMA REGISTRAL, LO CUAL PUEDE OCASIONAR OMISIONES O ERRORES ';
    $txtCuerpo .= 'EN LA INFORMACIÓN CERTIFICADA, POR LO CUAL EN CASO DE ENCONTRAR ALGUNA OBSERVACIÓN ';
    $txtCuerpo .= 'EN EL CERTIFICADO, VERIFICAREMOS LA INFORMACIÓN Y PROCEDEREMOS A SU CORRECCION.';

    if ($txtCuerpo != '') {
        $txt = '<strong>INFORMA - MIGRACIÓN DE INFORMACIÓN</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->MultiCell(185, 4, $txtCuerpo . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
    }
}

// *************************************************************************** //
// Certifica de objeto social - Inscripciones antes del 72
// *************************************************************************** //
function armarCertificaInspeccionEsadl($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["categoria"] == '1') {
        $txtCuerpo = '';
        $txtCuerpo = "LA PERSONA JURIDICA DE QUE TRATA ESTE CERTIFICADO SE ENCUENTRA SUJETA A LA INSPECCION, ";
        $txtCuerpo .= "VIGILANCIA Y CONTROL DE LAS AUTORIDADES QUE EJERCEN ESTA FUNCION, POR LO TANTO DEBERA ";
        $txtCuerpo .= "PRESENTAR ANTE LA AUTORIDAD CORRESPONDIENTE, EL CERTIFICADO DE REGISTRO RESPECTIVO, ";
        $txtCuerpo .= "EXPEDIDO POR LA CAMARA DE COMERCIO, DENTRO DE LOS 10 DIAS HABILES SIGUIENTES A LA FECHA ";
        $txtCuerpo .= "DE INSCRIPCION, MAS EL TERMINO DE LA DISTANCIA CUANDO EL DOMICILIO DE LA PERSONA JURIDICA ";
        $txtCuerpo .= "SIN ANIMO DE LUCRO QUE SE REGISTRA ES DIFERENTE AL DE LA CAMARA DE COMERCIO QUE LE CORRESPONDE. ";
        $txtCuerpo .= "EN EL CASO DE REFORMAS ESTATUTARIAS ADEMAS SE ALLEGARA COPIA DE LOS ESTATUTOS.TODA ";
        $txtCuerpo .= "AUTORIZACION, PERMISO, LICENCIA O RECONOCIMIENTO DE CARACTER OFICIAL, SE TRAMITARA CON ";
        $txtCuerpo .= "POSTERIORIDAD A LA INSCRIPCION DE LAS PERSONAS JURIDICAS SIN ANIMO DE LUCRO EN LA ";
        $txtCuerpo .= "RESPECTIVA CAMARA DE COMERCIO";
        if ($txtCuerpo != '') {
            $txt = '<strong>IMPORTANTE</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->MultiCell(185, 4, $txtCuerpo . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
        }
    }
}

// *************************************************************************** //
// Certifica de capital 
// *************************************************************************** //
function armarCertificaCapital($pdf, $data, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = '<td width="20%">' . truncarDecimales($data["cap_apolab"]) . '</td>';
    $txt .= '<td width="20%">' . truncarDecimales($data["cap_apolabadi"]) . '</td>';
    $txt .= '<td width="20%">' . truncarDecimales($data["cap_apoact"]) . '</td>';
    $txt .= '<td width="20%">' . truncarDecimales($data["cap_apodin"]) . '</td>';

    // if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {
    if ($data["categoria"] == '1') {
        if (
                $data["capaut"] != 0 ||
                $data["capsus"] != 0 ||
                $data["cappag"] != 0 ||
                $data["capsoc"] != 0 ||
                $data["capsuc"] != 0 ||
                $data["cap_apolab"] != 0 ||
                $data["cap_apolabadi"] != 0 ||
                $data["cap_apoact"] != 0 ||
                $data["cap_apodin"] != 0
        ) {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - CAPITAL</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }

            //
            $otroscapitales = 'si';

            // Si son anonimas
            if ($data["organizacion"] == '04' || $data["organizacion"] == '07' || $data["organizacion"] == '16') {
                $txt = '<table>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>TIPO DE CAPITAL</strong></td>';
                $txt .= '<td width="25%">VALOR</td>';
                $txt .= '<td width="25%">ACCIONES</td>';
                $txt .= '<td width="25%">VALOR NOMINAL</td>';
                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>CAPITAL AUTORIZADO</strong></td>';
                $txt .= '<td width="25%">' . truncarDecimales($data["capaut"]) . '</td>';

                if ($data["cuoaut"] != 0) {
                    $txt .= '<td width="25%">' . truncarDecimales($data["cuoaut"]) . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                if ($data["cuoaut"] != 0) {
                    $valnom = $data["capaut"] / $data["cuoaut"];
                    /*
                      $sep = explode(".", $valnom);
                      if (isset($sep[1]) && strlen($sep[1]) > 2) {
                      $tvalnom = truncarDecimales($valnom, 3);
                      } else {
                      $tvalnom = truncarDecimales($valnom, 2);
                      }
                      $txt .= '<td width="25%">' . $tvalnom . '</td>';
                     */
                    //Weymer : 2019-06-12 : Se trunca a dos decimales (solicitud Incidencia Pereira) sujeto a próximo ajuste en nuevo certificado
                    $tvalnom = truncarDecimales($valnom, 2);
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>CAPITAL SUSCRITO</strong></td>';
                $txt .= '<td width="25%">' . truncarDecimales($data["capsus"]) . '</td>';

                if ($data["cuosus"] != 0) {
                    $txt .= '<td width="25%">' . truncarDecimales($data["cuosus"]) . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                if ($data["cuosus"] != 0) {
                    $valnom = $data["capsus"] / $data["cuosus"];
                    /*
                      $sep = explode(".", $valnom);
                      if (isset($sep[1]) && strlen($sep[1]) > 2) {
                      $tvalnom = truncarDecimales($valnom, 3);
                      } else {
                      $tvalnom = truncarDecimales($valnom, 2);
                      }
                      $txt .= '<td width="25%">' . $tvalnom . '</td>';
                     */
                    //Weymer : 2019-06-12 : Se trunca a dos decimales (solicitud Incidencia Pereira) sujeto a próximo ajuste en nuevo certificado
                    $tvalnom = truncarDecimales($valnom, 2);
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>CAPITAL PAGADO</strong></td>';
                $txt .= '<td width="25%">' . truncarDecimales($data["cappag"]) . '</td>';

                if ($data["cuopag"] != 0) {
                    $txt .= '<td width="25%">' . truncarDecimales($data["cuopag"]) . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                if ($data["cuopag"] != 0) {
                    $valnom = $data["cappag"] / $data["cuopag"];
                    /*
                      $sep = explode(".", $valnom);
                      if (isset($sep[1]) && strlen($sep[1]) > 2) {
                      $tvalnom = truncarDecimales($valnom, 3);
                      } else {
                      $tvalnom = truncarDecimales($valnom, 2);
                      }
                      $txt .= '<td width="25%">' . $tvalnom . '</td>';
                     */
                    //Weymer : 2019-06-12 : Se trunca a dos decimales (solicitud Incidencia Pereira) sujeto a próximo ajuste en nuevo certificado
                    $tvalnom = truncarDecimales($valnom, 2);
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                $txt .= '</tr>';
                $txt .= '</table>';
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
                $otroscapitales = 'no';
            }

            // Si se tatra de sucursales de sociedades extranjeras
            if ($data["organizacion"] == '08') {
                if ($data["monedacap"] == '001') {
                    $txt = 'CAPITAL ASIGNADO A LA SUCURSAL : ' . number_format($data["capsuc"], 2);
                }
                if ($data["monedacap"] == '002') {
                    $txt = 'CAPITAL ASIGNADO A LA SUCURSAL EN DÓLARES DE LOS ESTADOS UNIDOS : ' . number_format($data["capsuc"], 2) . '<br><br>';
                    $txt .= 'QUE EL CAPITAL ASIGNADO PARA EL FUNCIONAMIENTO DE LA SUCURSAL EN COLOMBIA ES LA CANTIDAD EQUIVALENTE EN PESOS ';
                    $txt .= 'COLOMBIANOS DE US$ ' . number_format($data["capsuc"], 2) . ' CONVERTIDOS A TASA REPRESENTATIVA DEL MERCADO EN LA ';
                    $txt .= 'FECHA QUE LA CANTIDAD DE DÓLARES ES NEGOCIADA CON CUALQUIER OTRO BANCO LOCAL.';
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
                $otroscapitales = 'no';
            }

            // Si son EATs
            if ($data["organizacion"] == '09') {
                $totapo = $data["cap_apolab"] + $data["cap_apolabadi"] + $data["cap_apoact"] + $data["cap_apodin"];
                $txt = '<table>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="20%"><strong>APORTE LABORAL</strong></td>';
                $txt .= '<td width="20%"><strong>APORTE LABORAL ADICIONAL</strong></td>';
                $txt .= '<td width="20%"><strong>APORTE ACTIVOS</strong></td>';
                $txt .= '<td width="20%"><strong>APORTE DINERO</strong></td>';
                $txt .= '<td width="20%"><strong>APORTE TOTAL</strong></td>';
                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="20%">' . truncarDecimales($data["cap_apolab"]) . '</td>';
                $txt .= '<td width="20%">' . truncarDecimales($data["cap_apolabadi"]) . '</td>';
                $txt .= '<td width="20%">' . truncarDecimales($data["cap_apoact"]) . '</td>';
                $txt .= '<td width="20%">' . truncarDecimales($data["cap_apodin"]) . '</td>';
                $txt .= '<td width="20%">' . truncarDecimales($totapo) . '</td>';
                $txt .= '</tr>';
                $txt .= '</table>';
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
                $otroscapitales = 'no';
            }

            // Civiles - anonimas
            if ($data["organizacion"] == '10' && $data["capaut"] != 0) {
                $txt = '<table>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>TIPO DE CAPITAL</strong></td>';
                $txt .= '<td width="25%">VALOR</td>';
                $txt .= '<td width="25%">ACCIONES</td>';
                $txt .= '<td width="25%">VALOR NOMINAL</td>';
                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>CAPITAL AUTORIZADO</strong></td>';
                $txt .= '<td width="25%">' . truncarDecimales($data["capaut"]) . '</td>';

                if ($data["cuoaut"] != 0) {
                    $txt .= '<td width="25%">' . truncarDecimales($data["cuoaut"]) . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                if ($data["cuoaut"] != 0) {
                    $valnom = $data["capaut"] / $data["cuoaut"];
                    /*
                      $sep = explode(".", $valnom);
                      if (isset($sep[1]) && strlen($sep[1]) > 2) {
                      $tvalnom = number_format($valnom, 3);
                      } else {
                      $tvalnom = number_format($valnom, 2);
                      }
                      $txt .= '<td width="25%">' . $tvalnom . '</td>';
                     */
                    //Weymer : 2019-06-12 : Se trunca a dos decimales (solicitud Incidencia Pereira) sujeto a próximo ajuste en nuevo certificado
                    // $tvalnom = truncarDecimales($valnom, 2);
                    //Weymer : 2019-106-04 : Basado el indente Monteria se omite el truncamiento por función excluyendo el uso de truncateFloat
                    $tvalnom = number_format($valnom, 2, ',', '.');
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>CAPITAL SUSCRITO</strong></td>';
                $txt .= '<td width="25%">' . truncarDecimales($data["capsus"]) . '</td>';

                if ($data["cuosus"] != 0) {
                    $txt .= '<td width="25%">' . truncarDecimales($data["cuosus"]) . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                if ($data["cuosus"] != 0) {
                    $valnom = $data["capsus"] / $data["cuosus"];
                    /*
                      $sep = explode(".", $valnom);
                      if (isset($sep[1]) && strlen($sep[1]) > 2) {
                      $tvalnom = number_format($valnom, 3);
                      } else {
                      $tvalnom = number_format($valnom, 2);
                      }
                      $txt .= '<td width="25%">' . $tvalnom . '</td>';
                     */
                    //Weymer : 2019-06-12 : Se trunca a dos decimales (solicitud Incidencia Pereira) sujeto a próximo ajuste en nuevo certificado
                    // $tvalnom = truncarDecimales($valnom, 2);
                    //Weymer : 2019-106-04 : Basado el indente Monteria se omite el truncamiento por función excluyendo el uso de truncateFloat
                    $tvalnom = number_format($valnom, 2, ',', '.');
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>CAPITAL PAGADO</strong></td>';
                $txt .= '<td width="25%">' . truncarDecimales($data["cappag"]) . '</td>';

                if ($data["cuopag"] != 0) {
                    $txt .= '<td width="25%">' . truncarDecimales($data["cuopag"]) . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                if ($data["cuopag"] != 0) {
                    $valnom = $data["cappag"] / $data["cuopag"];
                    /*
                      $sep = explode(".", $valnom);
                      if (isset($sep[1]) && strlen($sep[1]) > 2) {
                      $tvalnom = number_format($valnom, 3);
                      } else {
                      $tvalnom = number_format($valnom, 2);
                      }
                      $txt .= '<td width="25%">' . $tvalnom . '</td>';
                     */
                    //Weymer : 2019-06-12 : Se trunca a dos decimales (solicitud Incidencia Pereira) sujeto a próximo ajuste en nuevo certificado
                    // $tvalnom = truncarDecimales($valnom, 2);
                    //Weymer : 2019-106-04 : Basado el indente Monteria se omite el truncamiento por función excluyendo el uso de truncateFloat
                    $tvalnom = number_format($valnom, 2, ',', '.');
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                $txt .= '</tr>';
                $txt .= '</table>';
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
                $otroscapitales = 'no';
            }

            // Civiles - limitadas
            if ($data["organizacion"] == '10' && $data["capsoc"] != 0) {
                $txt = '<table>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>TIPO DE CAPITAL</strong></td>';
                $txt .= '<td width="25%">VALOR</td>';
                $txt .= '<td width="25%">CUOTAS</td>';
                $txt .= '<td width="25%">VALOR NOMINAL</td>';
                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>CAPITAL SOCIAL</strong></td>';
                $txt .= '<td width="25%">' . truncarDecimales($data["capsoc"]) . '</td>';
                if ($data["cuosoc"] != 0) {
                    $txt .= '<td width="25%">' . truncarDecimales($data["cuosoc"]) . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                if ($data["cuosoc"] != 0) {
                    $valnom = $data["capsoc"] / $data["cuosoc"];
                    /*
                      $sep = explode(".", $valnom);
                      if (isset($sep[1]) && strlen($sep[1]) > 2) {
                      $tvalnom = number_format($valnom, 3);
                      } else {
                      $tvalnom = number_format($valnom, 2);
                      }
                      $txt .= '<td width="25%">' . $tvalnom . '</td>';
                     */
                    //Weymer : 2019-06-12 : Se trunca a dos decimales (solicitud Incidencia Pereira) sujeto a próximo ajuste en nuevo certificado
                    $tvalnom = truncarDecimales($valnom, 2);
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }

                $txt .= '</tr>';
                $txt .= '</table>';
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
                $otroscapitales = 'no';
            }

            // Limitadas y sus asimiladas
            if ($otroscapitales == 'si') {
                $txt = '<table>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>TIPO DE CAPITAL</strong></td>';
                $txt .= '<td width="25%">VALOR</td>';
                $txt .= '<td width="25%">CUOTAS</td>';
                $txt .= '<td width="25%">VALOR NOMINAL</td>';
                $txt .= '</tr>';
                $txt .= '<tr align="center">';
                $txt .= '<td width="25%"><strong>CAPITAL SOCIAL</strong></td>';
                $txt .= '<td width="25%">' . truncarDecimales($data["capsoc"]) . '</td>';
                if ($data["cuosoc"] != 0) {
                    $txt .= '<td width="25%">' . truncarDecimales($data["cuosoc"]) . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }
                if ($data["cuosoc"] != 0) {
                    $valnom = $data["capsoc"] / $data["cuosoc"];
                    /*
                      $sep = explode(".", $valnom);
                      if (isset($sep[1]) && strlen($sep[1]) > 2) {
                      $tvalnom = number_format($valnom, 3);
                      } else {
                      $tvalnom = number_format($valnom, 2);
                      }
                      $txt .= '<td width="25%">' . $tvalnom . '</td>';
                     */
                    //Weymer : 2019-06-12 : Se trunca a dos decimales (solicitud Incidencia Pereira) sujeto a próximo ajuste en nuevo certificado
                    $tvalnom = truncarDecimales($valnom, 2);
                    $txt .= '<td width="25%">' . $tvalnom . '</td>';
                } else {
                    $txt .= '<td width="25%">&nbsp;</td>';
                }
                $txt .= '</tr>';
                $txt .= '</table>';
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
    }
    // }
}

// *************************************************************************** //
// Certifica de Patrimonio 
// *************************************************************************** //
function armarCertificaPatrimonio($pdf, $data, $mysqli = null) {
    //
    $return = false;
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["categoria"] == '1') {
        if ($data["patrimonioesadl"] != 0) {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA - PATRIMONIO</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txt = '<strong>PATRIMONIO : </strong>$' . number_format($data["patrimonioesadl"]);
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $pdf->Ln();
            $return = true;
        }
    }
    return $return;
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculos($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $vinculos = 'no';
    $vinculoscompletos = 'si';
    foreach ($data["vinculos"] as $v) {
        if ($v["vinculootros"] == $tipovinculo) {
            $vinculos = 'si';
            if (ltrim(trim($v["librootros"]), "0") == '' || ltrim(trim($v["inscripcionotros"]), "0") == '') {
                $vinculoscompletos = 'no';
            }
        }
    }

    //
    if ($vinculos == 'si' && $vinculoscompletos == 'si') {
        armarVinculosTipoSii($pdf, $data, $tipovinculo, $mysqli);
    }
    if ($vinculos == 'si' && $vinculoscompletos == 'no') {
        armarVinculosTipoSirep($pdf, $data, $tipovinculo, $mysqli);
    }
}

function armarVinculosTipoSii($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    armarVinculoTitulo($pdf, $data, $tipovinculo, $mysqli);

    //
    foreach ($data["vinculos"] as $v) {
        if ($v["vinculootros"] == $tipovinculo) {
            $tipdoc = '';
            $numdoc = '';
            $fecdoc = '';
            $idorigen = '';
            $txtorigen = '';
            $libro = '';
            $registro = '';
            $fecins = '';

            // busca inscripcion
            foreach ($data["inscripciones"] as $ins) {
                if ($tipdoc == '') {
                    $okins = 'no';
                    if ($v["dupliotros"] != '') {
                        if (
                                $ins["lib"] == $v["librootros"] &&
                                $ins["nreg"] == $v["inscripcionotros"] &&
                                $ins["dupli"] == $v["dupliotros"]
                        ) {
                            $okins = 'si';
                        }
                    } else {
                        if (
                                $ins["lib"] == $v["librootros"] &&
                                $ins["nreg"] == $v["inscripcionotros"]
                        ) {
                            $okins = 'si';
                        }
                    }
                    if ($okins == 'si') {
                        $tipdoc = $ins["tdoc"];
                        $numdoc = $ins["ndoc"];
                        $ndocext = $ins["ndocext"];
                        $fecdoc = $ins["fdoc"];
                        $idorigen = $ins["idoridoc"];
                        $txtorigen = $ins["txoridoc"];
                        $libro = $ins["lib"];
                        $registro = $ins["nreg"];
                        $fecins = $ins["freg"];
                        $idmunidoc = $ins["idmunidoc"];
                        $camant = $ins["camant"];
                        $libant = $ins["libant"];
                        $regant = $ins["regant"];
                        $fecant = $ins["fecant"];
                    }
                }
            }

            $txt = descripcionesVinculos($mysqli, $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant);
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
            $txt = '<table>';
            $txt .= '<tr align="center">';
            if (
                    $tipovinculo == '2160' ||
                    $tipovinculo == '2161' ||
                    $tipovinculo == '2162' ||
                    $tipovinculo == '2163' ||
                    $tipovinculo == '2164' ||
                    $tipovinculo == '5160' ||
                    $tipovinculo == '5260'
            ) {
                $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                $txt .= '<td width="30%"><strong>NOMBRE</strong></td>';
                $txt .= '<td width="20%"><strong>IDENTIFICACION</strong></td>';
                $txt .= '<td width="20%"><strong>T. PROF</strong></td>';
            } else {
                $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                $txt .= '<td width="50%"><strong>NOMBRE</strong></td>';
                $txt .= '<td width="20%"><strong>IDENTIFICACION</strong></td>';
            }
            $txt .= '</tr>';
            $txt .= '<tr align="center">';

            // 2017-09-11 WSI - Búsqueda de cargo si no esta definido en SII
            if (trim($v["cargootros"]) == '') {
                $descCargo = retornarNombreTablasSirepMysqliApi($mysqli, '14', $v["idcargootros"]);
                $txt .= '<td width="30%">' . $descCargo . '</td>';
            } else {
                $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
            }

            if (
                    $tipovinculo == '2160' ||
                    $tipovinculo == '2161' ||
                    $tipovinculo == '2162' ||
                    $tipovinculo == '2163' ||
                    $tipovinculo == '2164' ||
                    $tipovinculo == '5160' ||
                    $tipovinculo == '5260'
            ) {
                $txt .= '<td width="30%">';
                $txt .= $v["nombreotros"];
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
            } else {
                $txt .= '<td width="50%">';
                $txt .= $v["nombreotros"];
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
            }

            $ti = '';
            switch ($v["idtipoidentificacionotros"]) {
                case "1":
                    $ti = 'CC';
                    break;
                case "2":
                    $ti = 'NIT';
                    break;
                case "3":
                    $ti = 'CE';
                    break;
                case "4":
                    $ti = 'TI';
                    break;
                case "5":
                    $ti = 'PAS';
                    break;
                case "6":
                    $ti = 'PJ';
                    break;
                case "E":
                    $ti = 'DE';
                    break;
                case "R":
                    $ti = 'RC';
                    break;
            }
            if ($v["idtipoidentificacionotros"] == '2') {
                $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                $txt .= '<td width="20%">' . $ti . ' ' . $sp["identificacion"] . '-' . $sp["dv"] . '</td>';
            } else {
                if ($v["idtipoidentificacionotros"] == '7') {
                    $txt .= '<td width="20%">**********</td>';
                } else {
                    if (is_numeric($v["identificacionotros"])) {
                        $txt .= '<td width="20%">' . $ti . ' ' . number_format($v["identificacionotros"], 0) . '</td>';
                    } else {
                        $txt .= '<td width="20%">' . $ti . ' ' . $v["identificacionotros"] . '</td>';
                    }
                }
            }
            if (
                    $tipovinculo == '2160' ||
                    $tipovinculo == '2161' ||
                    $tipovinculo == '2162' ||
                    $tipovinculo == '2163' ||
                    $tipovinculo == '2164' ||
                    $tipovinculo == '5160' ||
                    $tipovinculo == '5260'
            ) {
                $txt .= '<td width="20%">' . $v["numtarprofotros"] . '</td>';
            }
            $txt .= '</tr>';
            $txt .= '</table>';
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();

            if ($tipovinculo == '2800') {
                if ($v["codcertifica"] != '') {
                    if (isset($data["crtsii"][$v["codcertifica"]]) && trim($data["crtsii"][$v["codcertifica"]]) != '') {
                        $txt = trim($data["crtsii"][$v["codcertifica"]]);
                        $txt = str_replace("&nbsp;", "", $txt) . '<br>';
                        $pdf->MultiCell(185, 4, $txt, 0, 'J', false, 1, '', '', true, 0, true);
                    }
                }
            }
        }
    }
}

function armarVinculosTipoSirep($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    armarVinculoTitulo($pdf, $data, $tipovinculo, $mysqli);

    //
    $txt = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["vinculootros"] == $tipovinculo) {
            $ti = '';
            switch ($v["idtipoidentificacionotros"]) {
                case "1":
                    $ti = 'CC';
                    break;
                case "2":
                    $ti = 'NIT';
                    break;
                case "3":
                    $ti = 'CE';
                    break;
                case "4":
                    $ti = 'TI';
                    break;
                case "5":
                    $ti = 'PAS';
                    break;
                case "6":
                    $ti = 'PJ';
                    break;
                case "E":
                    $ti = 'DE';
                    break;
                case "R":
                    $ti = 'RC';
                    break;
            }

            if (trim($v["cargootros"]) == '') {
                $descCargo = retornarNombreTablasSirepMysqliApi($mysqli, '14', $v["idcargootros"]);
                $txt .= '<strong>*** ' . $descCargo . '***</strong><br>';
            } else {
                $txt .= '<strong>*** ' . $v["cargootros"] . '***</strong><br>';
            }

            //
            $txt .= 'Nombre: ' . $v["nombreotros"] . '<br>';
            $txt .= 'Identificación: ' . $ti . ' ' . $v["identificacionotros"] . '<br>';
            if (ltrim(trim($v["librootros"]), "0") != '' || ltrim(trim($v["inscripcionotros"]), "0") != '') {
                $txt .= 'Inscripción : ';
            }
            if (ltrim(trim($v["librootros"]), "0") != '') {
                $txt .= $v["librootros"];
            }
            if (ltrim(trim($v["librootros"]), "0") == '' && trim(trim($v["inscripcionotros"]), "0") != '') {
                $txt .= $v["inscripcionotros"];
            }
            if (ltrim(trim($v["librootros"]), "0") != '' && trim(trim($v["inscripcionotros"]), "0") != '') {
                $txt .= ' - ' . $v["inscripcionotros"];
            }
            if (ltrim(trim($v["librootros"]), "0") != '' || ltrim(trim($v["inscripcionotros"]), "0") != '') {
                $txt .= '<br>';
            }
            if (ltrim(trim($v["fechaotros"]), "0") != '') {
                $txt .= 'Fecha inscripción : ' . $v["fechaotros"] . '<br>';
            }

            $txt .= '<br>';
        }
    }
    if ($txt != '') {
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    }
}

function armarVinculoTitulo($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
        $txt = '<strong>CERTIFICA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    } else {
        $txt = '<strong>CERTIFICA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    }
    switch ($tipovinculo) {
        case "2120":
            $txt = '<strong>GERENTES</strong>';
            break;

        case "2140":
            $txt = '<strong>JUNTA DIRECTIVA PRINCIPALES</strong>';
            break;

        case "2141":
            $txt = '<strong>JUNTA DIRECTIVA PRIMEROS SUPLENTES</strong>';
            break;

        case "2142":
            $txt = '<strong>JUNTA DIRECTIVA SEGUNDOS SUPLENTES</strong>';
            break;

        case "2143":
            $txt = '<strong>JUNTA DIRECTIVA TERCEROS SUPLENTES</strong>';
            break;

        case "2160":
            $txt = '<strong>REVISORES FISCALES - PRINCIPALES</strong>';
            break;

        case "2161":
            $txt = '<strong>REVISORES FISCALES - SUPLENTES</strong>';
            break;

        case "2162":
            $txt = '<strong>REVISORES FISCALES - SEGUNDOS SUPLENTES</strong>';
            break;

        case "2163":
            $txt = '<strong>REVISORES FISCALES - DELEGADO - PRINCIPALES</strong>';
            break;

        case "2164":
            $txt = '<strong>REVISORES FISCALES - DELEGADOS - SUPLENTES</strong>';
            break;

        case "2170":
            $txt = '<strong>REPRESENTACION LEGAL - PRINCIPALES</strong>';
            break;

        case "2171":
            $txt = '<strong>REPRESENTACION LEGAL - SUPLENTES</strong>';
            break;

        case "2172":
            $txt = '<strong>REPRESENTACION LEGAL - SEGUNDOS SUPLENTES</strong>';
            break;

        case "2173":
            $txt = '<strong>REPRESENTACION LEGAL - TERCEROS SUPLENTES </strong>';
            break;

        case "2176":
            $txt = '<strong>REPRESENTACION LEGAL - ESPECIALES</strong>';
            break;

        case "2555":
            $txt = '<strong>JUNTA DE DIRECTORES - PRINCIPALES</strong>';
            break;

        case "2556":
            $txt = '<strong>JUNTA DE DIRECTORES - SUPLENTES</strong>';
            break;

        case "2706":
            $txt = '<strong>PROMOTOR ACUERDO DE REESTRUCTURACION</strong>';
            break;

        case "2800":
            $txt = '<strong>APODERADOS</strong>';
            break;

        case "4100":
            $txt = '<strong>MIEMBROS COMITE EJECUTIVO</strong>';
            break;

        case "4120":
            $txt = '<strong>GERENTES</strong>';
            break;

        case "4136":
            $txt = '<strong>JUNTA DE VIGILANCIA - PRINCIPALES</strong>';
            break;

        case "4236":
            $txt = '<strong>JUNTA DE VIGILANCIA - SUPLENTES</strong>';
            break;

        case "4549":
            $txt = '<strong>JUNTA DE VIGILANCIA</strong>';
            break;

        case "4139":
            $txt = '<strong>JUNTA ADMINISTRADORA - PRINCIPALES</strong>';
            break;

        case "4239":
            $txt = '<strong>JUNTA ADMINISTRADORA - SUPLENTES</strong>';
            break;

        case "4140":
            $txt = '<strong>JUNTA DIRECTIVA - PRINCIPALES</strong>';
            break;

        case "4240":
            $txt = '<strong>JUNTA DIRECTIVA - SUPLENTES</strong>';
            break;

        case "4143":
            $txt = '<strong>CONSEJO DE ADMINISTRACIÓN - PRINCIPALES</strong>';
            break;

        case "4243":
            $txt = '<strong>CONSEJO DE ADMINISTRACIÓN - SUPLENTES</strong>';
            break;

        case "4153":
            $txt = '<strong>DIRECTOR</strong>';
            break;

        case "4155":
            $txt = '<strong>CONSEJO DE ADMINISTRACIÓN - PRINCIPALES</strong>';
            break;

        case "4255":
            $txt = '<strong>CONSEJO DE ADMINISTRACIÓN - SUPLENTES</strong>';
            break;

        case "4144":
            $txt = '<strong>CONSEJO DIRECTIVO - PRINCIPALES</strong>';
            break;

        case "4244":
            $txt = '<strong>CONSEJO DIRECTIVO - SUPLENTES</strong>';
            break;

        case "4148":
            $txt = '<strong>COMITE EJECUTIVO - PRINCIPALES</strong>';
            break;

        case "4248":
            $txt = '<strong>COMITE EJECUTIVO - SUPLENTES</strong>';
            break;

        case "4170":
            $txt = '<strong>REPRESENTACION LEGAL - PRINCIPALES</strong>';
            break;

        case "4270":
            $txt = '<strong>REPRESENTACION LEGAL - SUPLENTES</strong>';
            break;

        case "4565":
            $txt = '<strong>COMITE DE CONTROL</strong>';
            break;

        case "4639":
            $txt = '<strong>CONSEJO DIRECTIVO</strong>';
            break;

        case "4609":
            $txt = '<strong>CONSEJO DE ADMINISTRACION - PRINCIPALES</strong>';
            break;

        case "4619":
            $txt = '<strong>JUNTA DE DIRECTORES - PRINCIPALES</strong>';
            break;

        case "4610":
            $txt = '<strong>CONSEJO DE ADMINISTRACION - SUPLENTES</strong>';
            break;

        case "5160":
            $txt = '<strong>REVISORES FISCALES - PRINCIPALES</strong>';
            break;

        case "5260":
            $txt = '<strong>REVISORES FISCALES - SUPLENTES</strong>';
            break;

        case "5161":
            $txt = '<strong>FISCALES - PRINCIPALES</strong>';
            break;

        case "5261":
            $txt = '<strong>FISCALES - SUPLENTES</strong>';
            break;

        case "5162":
            $txt = '<strong>CONTRALORES - PRINCIPALES</strong>';
            break;

        case "5262":
            $txt = '<strong>CONTRALORES - SUPLENTES</strong>';
            break;
    }
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosClase($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $retornar = false;
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $vinculos = 'no';
    $vinculoscompletos = 'si';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == $tipovinculo) {
            $vinculos = 'si';
            if (ltrim(trim($v["librootros"]), "0") == '' || ltrim(trim($v["inscripcionotros"]), "0") == '') {
                $vinculoscompletos = 'no';
            }
        }
    }

    //
    // if ($_SESSION["generales"]["codigoempresa"] == '55' && substr($data["matricula"],0,1) == 'S') {
    //     $vinculoscompletos = 'no';
    // }

    if ($vinculos == 'si' && $vinculoscompletos == 'si') {
        armarVinculosClaseSii($pdf, $data, $tipovinculo, $mysqli);
    }
    if ($vinculos == 'si' && $vinculoscompletos == 'no') {
        armarVinculosClaseSirep($pdf, $data, $tipovinculo, $mysqli);
    }
    if ($vinculos == 'si') {
        $retornar = true;
    }
    return $retornar;
}

function armarVinculoTituloClase($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
        $txt = '<strong>CERTIFICA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    } else {
        $txt = '<strong>CERTIFICA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    }

    //
    if (!isset($data["clasevinculo"][$tipovinculo])) {
        $namev = '<strong>CLASE VINCULO PENDIENTE DE DEFINIR : ' . $tipovinculo . '</strong>';
    } else {
        $namev = '<strong>' . $data["clasevinculo"][$tipovinculo] . '</strong>';
    }

    $pdf->writeHTML($namev, true, false, true, false, 'C');
    $pdf->Ln();
}

function armarVinculosClaseSii($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    armarVinculoTituloClase($pdf, $data, $tipovinculo, $mysqli);

    //
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == $tipovinculo) {
            $tipdoc = '';
            $numdoc = '';
            $fecdoc = '';
            $idorigen = '';
            $txtorigen = '';
            $libro = '';
            $registro = '';
            $fecins = '';

            //
            if ($tipovinculo == 'APOD') {
                if ($pdf->codcertificaanterior == '') {
                    $pdf->codcertificaanterior = trim($v["codcertifica"]);
                }
                if ($v["codcertifica"] != $pdf->codcertificaanterior) {
                    if ($pdf->codcertificaanterior != '') {
                        if (isset($data["crtsii"][$pdf->codcertificaanterior]) && trim($data["crtsii"][$pdf->codcertificaanterior]) != '') {
                            $txt = trim($data["crtsii"][$pdf->codcertificaanterior]);
                            $txt = str_replace("&nbsp;", "", $txt) . '<br>';
                            $pdf->MultiCell(185, 4, $txt, 0, 'J', false, 1, '', '', true, 0, true);
                        }
                    }
                    $pdf->codcertificaanterior = trim($v["codcertifica"]);
                }
            }

            // busca inscripcion
            if (defined('CAMARA_SUR_OCCIDENTE') && CAMARA_SUR_OCCIDENTE == 'S' && $v["fechaotros"] != '') {
                foreach ($data["inscripciones"] as $ins) {
                    if ($tipdoc == '') {
                        $okins = 'no';
                        if ($v["dupliotros"] != '') {
                            if (
                                    $ins["lib"] == $v["librootros"] &&
                                    $ins["nreg"] == $v["inscripcionotros"] &&
                                    $ins["dupli"] == $v["dupliotros"] &&
                                    $ins["freg"] == $v["fechaotros"]
                            ) {
                                $okins = 'si';
                            }
                        } else {
                            if (
                                    $ins["lib"] == $v["librootros"] &&
                                    $ins["nreg"] == $v["inscripcionotros"]
                            ) {
                                $okins = 'si';
                            }
                        }
                        if ($okins == 'si') {
                            $tipdoc = $ins["tdoc"];
                            $numdoc = $ins["ndoc"];
                            $ndocext = $ins["ndocext"];
                            $fecdoc = $ins["fdoc"];
                            $idorigen = $ins["idoridoc"];
                            $txtorigen = $ins["txoridoc"];
                            $libro = $ins["lib"];
                            $registro = $ins["nreg"];
                            $fecins = $ins["freg"];
                            $idmunidoc = $ins["idmunidoc"];
                            $camant = $ins["camant"];
                            $libant = $ins["libant"];
                            $regant = $ins["regant"];
                            $fecant = $ins["fecant"];
                        }
                    }
                }
            } else {
                foreach ($data["inscripciones"] as $ins) {
                    if ($tipdoc == '') {
                        $okins = 'no';
                        if ($v["dupliotros"] != '') {
                            if (
                                    $ins["lib"] == $v["librootros"] &&
                                    $ins["nreg"] == $v["inscripcionotros"] &&
                                    $ins["dupli"] == $v["dupliotros"]
                            ) {
                                $okins = 'si';
                            }
                        } else {
                            if (
                                    $ins["lib"] == $v["librootros"] &&
                                    $ins["nreg"] == $v["inscripcionotros"]
                            ) {
                                $okins = 'si';
                            }
                        }
                        if ($okins == 'si') {
                            $tipdoc = $ins["tdoc"];
                            $numdoc = $ins["ndoc"];
                            $ndocext = $ins["ndocext"];
                            $fecdoc = $ins["fdoc"];
                            $idorigen = $ins["idoridoc"];
                            $txtorigen = $ins["txoridoc"];
                            $libro = $ins["lib"];
                            $registro = $ins["nreg"];
                            $fecins = $ins["freg"];
                            $idmunidoc = $ins["idmunidoc"];
                            $camant = $ins["camant"];
                            $libant = $ins["libant"];
                            $regant = $ins["regant"];
                            $fecant = $ins["fecant"];
                        }
                    }
                }
            }

            $txt = descripcionesVinculos($mysqli, $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant);
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
            $txt = '<table>';
            $txt .= '<tr align="center">';
            if (
                    $tipovinculo == 'APOD' ||
                    $tipovinculo == 'RFP' ||
                    $tipovinculo == 'RFS' ||
                    $tipovinculo == 'RFS1' ||
                    $tipovinculo == 'RFS2' ||
                    $tipovinculo == 'RFS3' ||
                    $tipovinculo == 'RFS4' ||
                    $tipovinculo == 'RFDP' ||
                    $tipovinculo == 'RFDS1' ||
                    $tipovinculo == 'PDDP'
            ) {
                $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                $txt .= '<td width="30%"><strong>NOMBRE</strong></td>';
                $txt .= '<td width="20%"><strong>IDENTIFICACION</strong></td>';
                $txt .= '<td width="20%"><strong>T. PROF</strong></td>';
            } else {
                $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                $txt .= '<td width="50%"><strong>NOMBRE</strong></td>';
                $txt .= '<td width="20%"><strong>IDENTIFICACION</strong></td>';
            }
            $txt .= '</tr>';
            $txt .= '<tr align="center">';

            // 2017-09-11 WSI - Búsqueda de cargo si no esta definido en SII
            if (trim($v["cargootros"]) == '') {
                if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                ) {
                    $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                } else {
                    $descCargo = retornarNombreTablasSirepMysqliApi($mysqli, '14', $v["idcargootros"]);
                    $txt .= '<td width="30%">' . $descCargo . '</td>';
                }
            } else {
                $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
            }

            if (
                    $tipovinculo == 'APOD' ||
                    $tipovinculo == 'RFP' ||
                    $tipovinculo == 'RFS' ||
                    $tipovinculo == 'RFS1' ||
                    $tipovinculo == 'RFS2' ||
                    $tipovinculo == 'RFS3' ||
                    $tipovinculo == 'RFS4' ||
                    $tipovinculo == 'RFDP' ||
                    $tipovinculo == 'RFDS1' ||
                    $tipovinculo == 'PDDP'
            ) {
                $txt .= '<td width="30%">';
                $txt .= $v["nombreotros"];
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
            } else {
                $txt .= '<td width="50%">';
                $txt .= $v["nombreotros"];
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
            }

            $ti = '';
            switch ($v["idtipoidentificacionotros"]) {
                case "1":
                    $ti = 'CC';
                    break;
                case "2":
                    $ti = 'NIT';
                    break;
                case "3":
                    $ti = 'CE';
                    break;
                case "4":
                    $ti = 'TI';
                    break;
                case "5":
                    $ti = 'PAS';
                    break;
                case "6":
                    $ti = 'PJ';
                    break;
                case "E":
                    $ti = 'DE';
                    break;
                case "R":
                    $ti = 'RC';
                    break;
            }
            if ($v["idtipoidentificacionotros"] == '2') {
                $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                $txt .= '<td width="20%">' . $ti . ' ' . $sp["identificacion"] . '-' . $sp["dv"] . '</td>';
            } else {
                if ($v["idtipoidentificacionotros"] == '7') {
                    $txt .= '<td width="20%">**********</td>';
                } else {
                    if ($v["idtipoidentificacionotros"] == '5' || $v["idtipoidentificacionotros"] == 'E') {
                        $txt .= '<td width="20%">' . $ti . ' ' . $v["identificacionotros"] . '</td>';
                    } else {
                        if (is_numeric($v["identificacionotros"])) {
                            $txt .= '<td width="20%">' . $ti . ' ' . number_format($v["identificacionotros"], 0) . '</td>';
                        } else {
                            $txt .= '<td width="20%">' . $ti . ' ' . $v["identificacionotros"] . '</td>';
                        }
                    }
                }
            }
            if (
                    $tipovinculo == 'APOD' ||
                    $tipovinculo == 'RFP' ||
                    $tipovinculo == 'RFS' ||
                    $tipovinculo == 'RFS1' ||
                    $tipovinculo == 'RFS2' ||
                    $tipovinculo == 'RFS3' ||
                    $tipovinculo == 'RFS4' ||
                    $tipovinculo == 'RFDP' ||
                    $tipovinculo == 'RFDS1' ||
                    $tipovinculo == 'PDDP'
            ) {
                $txt .= '<td width="20%">' . $v["numtarprofotros"] . '</td>';
            }
            $txt .= '</tr>';
            $txt .= '</table>';
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();

            // 2018-05-29
            // Verifica si el vínculo tiene inscripciones relacionadas con sanciones o levantamiento de sanciones
            foreach ($data["inscripciones"] as $ins) {
                if (isset($ins["vinculoafectado"])) {
                    if ($ins["vinculoafectado"] == $v["vinculootros"] && $ins["identificacionafectada"] == $v["identificacionotros"]) {
                        if ($ins["crev"] == '0') {
                            if ($ins["fechalimite"] > date("Ymd")) {
                                if ($ins["grupoacto"] == '080') { // Si impone sanciones, se excluye si las levanta
                                    $txt = descripciones($mysqli, $data["organizacion"], $ins["acto"], $ins["tdoc"], $ins["ndoc"], $ins["ndocext"], $ins["fdoc"], $ins["idoridoc"], $ins["origendocumento"], $ins["idmunidoc"], $ins["lib"], $ins["nreg"], $ins["freg"], $ins["not"], $ins["camant"], $ins["libant"], $ins["regant"], $ins["fecant"]);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                }
                                if ($ins["grupoacto"] == '082') { // Suspensión temporal de sanciones a nombramientos
                                    $txt = descripciones($mysqli, $data["organizacion"], $ins["acto"], $ins["tdoc"], $ins["ndoc"], $ins["ndocext"], $ins["fdoc"], $ins["idoridoc"], $ins["origendocumento"], $ins["idmunidoc"], $ins["lib"], $ins["nreg"], $ins["freg"], $ins["not"], $ins["camant"], $ins["libant"], $ins["regant"], $ins["fecant"]);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    //
    if ($tipovinculo == 'APOD') {
        if ($pdf->codcertificaanterior != '') {
            if (isset($data["crtsii"][$pdf->codcertificaanterior]) && trim($data["crtsii"][$pdf->codcertificaanterior]) != '') {
                $txt = trim($data["crtsii"][$pdf->codcertificaanterior]);
                $txt = str_replace("&nbsp;", "", $txt) . '<br>';
                $pdf->MultiCell(185, 4, $txt, 0, 'J', false, 1, '', '', true, 0, true);
            }
        }
    }
}

function armarVinculosClaseSirep($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    armarVinculoTituloClase($pdf, $data, $tipovinculo, $mysqli);

    //
    $txt = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == $tipovinculo) {
            $ti = '';
            switch ($v["idtipoidentificacionotros"]) {
                case "1":
                    $ti = 'CC';
                    break;
                case "2":
                    $ti = 'NIT';
                    break;
                case "3":
                    $ti = 'CE';
                    break;
                case "4":
                    $ti = 'TI';
                    break;
                case "5":
                    $ti = 'PAS';
                    break;
                case "6":
                    $ti = 'PJ';
                    break;
                case "E":
                    $ti = 'DE';
                    break;
                case "R":
                    $ti = 'RC';
                    break;
            }

            if (trim($v["cargootros"]) == '') {
                $descCargo = retornarNombreTablasSirepMysqliApi($mysqli, '14', $v["idcargootros"]);
                $txt .= '<strong>*** ' . $descCargo . '***</strong><br>';
            } else {
                $txt .= '<strong>*** ' . $v["cargootros"] . '***</strong><br>';
            }

            //
            $txt .= 'Nombre: ' . $v["nombreotros"] . '<br>';
            $txt .= 'Identificación: ' . $ti . ' ' . $v["identificacionotros"] . '<br>';
            if (ltrim(trim($v["librootros"]), "0") != '' || ltrim(trim($v["inscripcionotros"]), "0") != '') {
                $txt .= 'Inscripción : ';
            }
            if (ltrim(trim($v["librootros"]), "0") != '') {
                $txt .= $v["librootros"];
            }
            if (ltrim(trim($v["librootros"]), "0") == '' && trim(trim($v["inscripcionotros"]), "0") != '') {
                $txt .= $v["inscripcionotros"];
            }
            if (ltrim(trim($v["librootros"]), "0") != '' && trim(trim($v["inscripcionotros"]), "0") != '') {
                $txt .= ' - ' . $v["inscripcionotros"];
            }
            if (ltrim(trim($v["librootros"]), "0") != '' || ltrim(trim($v["inscripcionotros"]), "0") != '') {
                $txt .= '<br>';
            }
            if (ltrim(trim($v["fechaotros"]), "0") != '') {
                $txt .= 'Fecha inscripción : ' . $v["fechaotros"] . '<br>';
            }

            $txt .= '<br>';
        }
    }
    if ($txt != '') {
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    }
}

function armarVinculosOriginal($pdf, $data, $tipovinculo, $mysqli = null) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $vinculos = 'no';
    $noms = array();
    foreach ($data["vinculos"] as $v) {
        if ($v["vinculootros"] == $tipovinculo) {
            if (trim($v["dupliotros"]) != '') {
                $nom = $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
            } else {
                $nom = $v["librootros"] . '-' . $v["inscripcionotros"];
            }
            $noms[$nom] = $nom;
            $vinculos = 'si';
        }
    }
    if ($vinculos == 'si') {
        if (!empty($noms)) {
            if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                $txt = '<strong>CERTIFICA</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            switch ($tipovinculo) {
                case "2120":
                    $txt = '<strong>GERENTES</strong>';
                    break;

                case "2140":
                    $txt = '<strong>JUNTA DIRECTIVA PRINCIPALES</strong>';
                    break;
                case "2141":
                    $txt = '<strong>JUNTA DIRECTIVA PRIMEROS SUPLENTES</strong>';
                    break;
                case "2142":
                    $txt = '<strong>JUNTA DIRECTIVA SEGUNDOS SUPLENTES</strong>';
                    break;
                case "2143":
                    $txt = '<strong>JUNTA DIRECTIVA TERCEROS SUPLENTES</strong>';
                    break;

                case "2160":
                    $txt = '<strong>REVISORES FISCALES - PRINCIPALES</strong>';
                    break;
                case "2161":
                    $txt = '<strong>REVISORES FISCALES - SUPLENTES</strong>';
                    break;
                case "2162":
                    $txt = '<strong>REVISORES FISCALES - SEGUNDOS SUPLENTES</strong>';
                    break;
                case "2163":
                    $txt = '<strong>REVISORES FISCALES - PRIMER SUPLENTE DEL REVISOR FISCAL - DELEGADO</strong>';
                    break;
                case "2164":
                    $txt = '<strong>REVISORES FISCALES - SEGUNDO SUPLENTE DEL REVISOR FISCAL - DELEGADO</strong>';
                    break;

                case "2170":
                    $txt = '<strong>REPRESENTACION LEGAL - PRINCIPALES</strong>';
                    break;
                case "2171":
                    $txt = '<strong>REPRESENTACION LEGAL - SUPLENTES</strong>';
                    break;
                case "2172":
                    $txt = '<strong>REPRESENTACION LEGAL - SEGUNDOS SUPLENTES</strong>';
                    break;
                case "2173":
                    $txt = '<strong>REPRESENTACION LEGAL - TERCEROS SUPLENTES </strong>';
                    break;

                case "2176":
                    $txt = '<strong>REPRESENTACION LEGAL - ESPECIALES</strong>';
                    break;

                case "2706":
                    $txt = '<strong>PROMOTOR ACUERDO DE REESTRUCTURACION</strong>';
                    break;

                case "2800":
                    $txt = '<strong>APODERADOS</strong>';
                    break;


                case "4120":
                    $txt = '<strong>GERENTES</strong>';
                    break;

                case "4136":
                    $txt = '<strong>JUNTA DE VIGILANCIA - PRINCIPALES</strong>';
                    break;
                case "4236":
                    $txt = '<strong>JUNTA DE VIGILANCIA - SUPLENTES</strong>';
                    break;

                case "4549":
                    $txt = '<strong>JUNTA DE VIGILANCIA</strong>';
                    break;

                case "4139":
                    $txt = '<strong>JUNTA ADMINISTRADORA - PRINCIPALES</strong>';
                    break;
                case "4239":
                    $txt = '<strong>JUNTA ADMINISTRADORA - SUPLENTES</strong>';
                    break;

                case "4140":
                    $txt = '<strong>JUNTA DIRECTIVA - PRINCIPALES</strong>';
                    break;
                case "4240":
                    $txt = '<strong>JUNTA DIRECTIVA - SUPLENTES</strong>';
                    break;

                case "4143":
                    $txt = '<strong>CONSEJO DE ADMINISTRACIÓN - PRINCIPALES</strong>';
                    break;
                case "4243":
                    $txt = '<strong>CONSEJO DE ADMINISTRACIÓN - SUPLENTES</strong>';
                    break;

                case "4155":
                    $txt = '<strong>CONSEJO DE ADMINISTRACIÓN - PRINCIPALES</strong>';
                    break;
                case "4255":
                    $txt = '<strong>CONSEJO DE ADMINISTRACIÓN - SUPLENTES</strong>';
                    break;

                case "4144":
                    $txt = '<strong>CONSEJO DIRECTIVO - PRINCIPALES</strong>';
                    break;
                case "4244":
                    $txt = '<strong>CONSEJO DIRECTIVO - SUPLENTES</strong>';
                    break;

                case "4148":
                    $txt = '<strong>COMITE EJECUTIVO - PRINCIPALES</strong>';
                    break;
                case "4248":
                    $txt = '<strong>COMITE EJECUTIVO - SUPLENTES</strong>';
                    break;

                case "4170":
                    $txt = '<strong>REPRESENTACION LEGAL - PRINCIPALES</strong>';
                    break;
                case "4270":
                    $txt = '<strong>REPRESENTACION LEGAL - SUPLENTES</strong>';
                    break;

                case "4565":
                    $txt = '<strongCOMITE DE CONTROL</strong>';
                    break;

                case "5160":
                    $txt = '<strong>REVISORES FISCALES - PRINCIPALES</strong>';
                    break;
                case "5260":
                    $txt = '<strong>REVISORES FISCALES - SUPLENTES</strong>';
                    break;

                case "5161":
                    $txt = '<strong>FISCALES - PRINCIPALES</strong>';
                    break;
                case "5261":
                    $txt = '<strong>FISCALES - SUPLENTES</strong>';
                    break;

                case "5162":
                    $txt = '<strong>CONTRALORES - PRINCIPALES</strong>';
                    break;
                case "5262":
                    $txt = '<strong>CONTRALORES - SUPLENTES</strong>';
                    break;
            }
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            foreach ($noms as $x) {
                $tipdoc = '';
                $numdoc = '';
                $fecdoc = '';
                $idorigen = '';
                $txtorigen = '';
                $libro = '';
                $registro = '';
                $fecins = '';

                // busca primero con duplis
                foreach ($data["inscripciones"] as $ins) {
                    if ($tipdoc == '') {
                        $nom = $ins["lib"] . '-' . $ins["nreg"] . '-' . $ins["dupli"];
                        if ($nom == $x) {
                            $tipdoc = $ins["tdoc"];
                            $numdoc = $ins["ndoc"];
                            $fecdoc = $ins["fdoc"];
                            $idorigen = $ins["idoridoc"];
                            $txtorigen = $ins["txoridoc"];
                            $libro = $ins["lib"];
                            $registro = $ins["nreg"];
                            $fecins = $ins["freg"];
                        }
                    }
                }

                // Si no encontro con duplis, busca sin duplis
                if ($tipdoc == '') {
                    foreach ($data["inscripciones"] as $ins) {
                        if ($tipdoc == '') {
                            $nom = $ins["lib"] . '-' . $ins["nreg"];
                            if ($nom == $x) {
                                $tipdoc = $ins["tdoc"];
                                $numdoc = $ins["ndoc"];
                                $ndocext = $ins["ndocext"];
                                $fecdoc = $ins["fdoc"];
                                $idorigen = $ins["idoridoc"];
                                $txtorigen = $ins["txoridoc"];
                                $libro = $ins["lib"];
                                $registro = $ins["nreg"];
                                $fecins = $ins["freg"];
                                $idmunidoc = $ins["idmunidoc"];
                                $camant = $ins["camant"];
                                $libant = $ins["libant"];
                                $regant = $ins["regant"];
                                $fecant = $ins["fecant"];
                            }
                        }
                    }
                }

                //
                $txt = descripcionesVinculos($mysqli, $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camanr, $libant, $regant, $fecant);
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
                $txt = '<table>';
                $txt .= '<tr align="center">';
                if ($tipovinculo == '2160' || $tipovinculo == '2161' || $tipovinculo == '5160' || $tipovinculo == '5260') {
                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                    $txt .= '<td width="30%"><strong>NOMBRE</strong></td>';
                    $txt .= '<td width="20%"><strong>IDENTIFICACION</strong></td>';
                    $txt .= '<td width="20%"><strong>T. PROF</strong></td>';
                } else {
                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                    $txt .= '<td width="50%"><strong>NOMBRE</strong></td>';
                    $txt .= '<td width="20%"><strong>IDENTIFICACION</strong></td>';
                }
                $txt .= '</tr>';
                foreach ($data["vinculos"] as $v) {
                    if ($v["vinculootros"] == $tipovinculo) {
                        if ($v["dupliotros"] == '') {
                            $nom = $v["librootros"] . '-' . $v["inscripcionotros"];
                        } else {
                            $nom = $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
                        }
                        if ($nom == $x) {

                            $txt .= '<tr align="center">';

                            // 2017-09-11 WSI - Búsqueda de cargo si no esta definido en SII
                            if (trim($v["cargootros"]) == '') {

                                $arrCargo = retornarNombreTablasSirepMysqliApi($mysqli, '14', $v["idcargootros"]);

                                if (isset($arrCargo) && !empty($arrCargo)) {
                                    $descCargo = $arrCargo["descripcion"];
                                } else {
                                    $descCargo = '';
                                }
                                $txt .= '<td width="30%">' . $descCargo . '</td>';
                            } else {
                                $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                            }

                            if ($tipovinculo == '2160' || $tipovinculo == '2161' || $tipovinculo == '5160' || $tipovinculo == '5260') {
                                $txt .= '<td width="30%">' . $v["nombreotros"] . '</td>';
                            } else {
                                $txt .= '<td width="50%">' . $v["nombreotros"] . '</td>';
                            }
                            $ti = '';
                            switch ($v["idtipoidentificacionotros"]) {
                                case "1":
                                    $ti = 'CC';
                                    break;
                                case "2":
                                    $ti = 'NIT';
                                    break;
                                case "3":
                                    $ti = 'CE';
                                    break;
                                case "4":
                                    $ti = 'TI';
                                    break;
                                case "5":
                                    $ti = 'PAS';
                                    break;
                                case "6":
                                    $ti = 'PJ';
                                    break;
                                case "E":
                                    $ti = 'DE';
                                    break;
                                case "R":
                                    $ti = 'RC';
                                    break;
                            }
                            if ($v["idtipoidentificacionotros"] == '2') {
                                $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                $txt .= '<td width="20%">' . $ti . ' ' . $sp["identificacion"] . '-' . $sp["dv"] . '</td>';
                            } else {
                                if ($v["idtipoidentificacionotros"] == '7') {
                                    $txt .= '<td width="20%">**********</td>';
                                } else {
                                    if (is_numeric($v["identificacionotros"])) {
                                        $txt .= '<td width="20%">' . $ti . ' ' . number_format($v["identificacionotros"], 0) . '</td>';
                                    } else {
                                        $txt .= '<td width="20%">' . $ti . ' ' . $v["identificacionotros"] . '</td>';
                                    }
                                }
                            }
                            if ($tipovinculo == '2160' || $tipovinculo == '2161' || $tipovinculo == '5160' || $tipovinculo == '5260') {
                                $txt .= '<td width="20%">' . $v["numtarprofotros"] . '</td>';
                            }
                            $txt .= '</tr>';
                        }
                    }
                }
                $txt .= '</table>';
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
    }
}

/**
 * 
 * @param type $acto
 * @param type $tipdoc
 * @param type $numdoc
 * @param type $numdocext
 * @param type $fecdoc
 * @param type $idorigen
 * @param type $txtorigen
 * @param type $libro
 * @param type $registro
 * @param type $fecins
 * @param type $noticia
 * @param type $nan
 * @param type $nombre
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @return type
 */
function descripciones($mysqli, $organizacion, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro, $registro, $fecins, $noticia, $nan = array(), $nombre = '', $comple = '', $camant = '', $libant = '', $regant = '', $fecant = '') {

    $txt = 'POR ';

    //
    if ($tipdoc == '15' && $numdoc == '1727') {
        $tipdoc = '38';
    }

    //
    if ($tipdoc == '38' && $numdoc == '1727') {
        $txt = 'DE ACUERDO A LO ESTABLECIDO EN LA ';
        $numdoc = '';
    }

    //
    $txtDoc = retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipos_documentales_registro', $tipdoc);
    if ($txtDoc == '') {
        $txtDoc = 'DOCUMENTO';
    }

    //
    $txt .= $txtDoc . ' ';

    //
    if (ltrim(trim($numdocext), "0") != '') {
        $txt .= 'NÚMERO ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && $numdoc != 'NA' && $numdoc != 'N/A' && $numdoc != 'n/A' && $numdoc != '0') {
            $txt .= 'NÚMERO ' . trim($numdoc) . ' ';
        }
    }
    if ($fecdoc != '' && $tipdoc != '38') {
        $txt .= 'DEL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecdoc)) . ' ';
    }

    //
    $txtSuscribe = '';

    //
    if ($tipdoc != '38') {
        if ($txtorigen != '') {
            if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
                $txtorigen = '';
            }
            $txtorigen = str_replace("NOTARIAS NOTARIA", "NOTARIA", $txtorigen);
            $txtorigen = str_replace("ACTAS ", "", $txtorigen);
            $txtSuscribe = $txtorigen;
        } else {
            if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
                $txtSuscribe = retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen);
            }
        }

        //
        $txtParticula = 'DE LA';
        if ($tipdoc == '10') {
            $txtParticula = 'EXPEDIDA POR';
        }
        if ($tipdoc == '01') {
            $txtParticula = 'SUSCRITA POR';
        }
        if ($txtSuscribe == 'REPRESENTACION LEGAL') {
            $txtParticula = 'DE';
            $txtSuscribe = 'EL REPRESENTANTE LEGAL';
        }
        if ($txtSuscribe == 'COMERCIANTE') {
            $txtParticula = 'DE';
            $txtSuscribe = 'EL COMERCIANTE';
        }
        if ($txtSuscribe == 'JUNTA DE SOCIOS') {
            $txtParticula = 'DE';
            $txtSuscribe = 'LA JUNTA DE SOCIOS';
        }
        if ($txtSuscribe == 'JUNTA DIRECTIVA') {
            $txtParticula = 'DE';
            $txtSuscribe = 'LA JUNTA DIRECTIVA';
        }
        if ($txtSuscribe == 'PROPIETARIO') {
            $txtParticula = 'DE';
            $txtSuscribe = 'EL PROPIETARIO';
        }
        if ($txtSuscribe == 'ADMON. DE IMPUESTOS NACIONALES') {
            $txtParticula = 'DE';
            $txtSuscribe = 'LA ADMINISTRACIÓN DE IMPUESTOS NACIONALES';
        }
        if ($txtSuscribe == 'ACCIONISTAS') {
            $txtParticula = 'DE';
            $txtSuscribe = 'ACCIONISTAS';
        }

        // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
        if ($txtSuscribe == 'UNICO ACCIONISTA') {
            $txtParticula = 'DE';
            $txtSuscribe = 'UNICO ACCIONISTA';
        }

        // 2018-06-18: JINT
        if ($txtSuscribe == 'ACCIONISTA UNICO') {
            $txtParticula = 'DEL';
            $txtSuscribe = 'ACCIONISTA UNICO';
        }

        // 2018-06-26: JINT
        if ($txtSuscribe == 'EL COMERCIANTE') {
            $txtParticula = '';
            $txtSuscribe = '';
        }


        if ($txtSuscribe == 'LA JUNTA DE SOCIOS') {
            if ($organizacion = '11') {
                if ($acto == '0040') {
                    $txtParticula = '';
                    $txtSuscribe = 'DEL EMPRESARIO CONSTITUYENTE';
                }
            }
        }
        if ($txtSuscribe != '') {
            // 2017-08-22: JINT: Se cambia SUSCRITO POR por DE LA
            // Por sugerencia de la CC de Aburrá Sur
            // $txt .= 'SUSCRITO POR ' . $txtSuscribe . ', ';
            $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
        }


        //
        if ($tipdoc == '02' || $tipdoc == '04') {
            if ($munori != '' && $munori != '00000' && $munori != '99999') {
                $txt .= ' DE ' . retornarNombreMunicipioMysqliApi($mysqli, $munori) . ', ';
            } else {
                $txt .= ', ';
            }
        } else {
            $txt .= ', ';
        }


        //
        $txt = str_replace(" , ", ", ", $txt);

        //
        $txt = str_replace(array("DE LA LA", "DE LA EL"), array("DE LA", "DE EL"), $txt);
    }

    //
    $txtLibro = '';
    switch ($libro) {
        case "RM01":
            $txtLibro = 'I DEL REGISTRO MERCANTIL';
            break;
        case "RM02":
            $txtLibro = 'II DEL REGISTRO MERCANTIL';
            break;
        case "RM03":
            $txtLibro = 'III DEL REGISTRO MERCANTIL';
            break;
        case "RM04":
            $txtLibro = 'IV DEL REGISTRO MERCANTIL';
            break;
        case "RM05":
            $txtLibro = 'V DEL REGISTRO MERCANTIL';
            break;
        case "RM06":
            $txtLibro = 'VI DEL REGISTRO MERCANTIL';
            break;
        case "RM07":
            $txtLibro = 'VII DEL REGISTRO MERCANTIL';
            break;
        case "RM08":
            $txtLibro = 'VIII DEL REGISTRO MERCANTIL';
            break;
        case "RM09":
            $txtLibro = 'IX DEL REGISTRO MERCANTIL';
            break;
        case "RM10":
            $txtLibro = 'X DEL REGISTRO MERCANTIL';
            break;
        case "RM11":
            $txtLibro = 'XI DEL REGISTRO MERCANTIL';
            break;
        case "RM12":
            $txtLibro = 'XII DEL REGISTRO MERCANTIL';
            break;
        case "RM13":
            $txtLibro = 'XIII DEL REGISTRO MERCANTIL';
            break;
        case "RM14":
            $txtLibro = 'XIV DEL REGISTRO MERCANTIL';
            break;
        case "RM15":
            $txtLibro = 'XV DEL REGISTRO MERCANTIL';
            break;
        case "RM16":
            $txtLibro = 'XVI DEL REGISTRO MERCANTIL';
            break;
        case "RM17":
            $txtLibro = 'XVII DEL REGISTRO MERCANTIL';
            break;
        case "RM18":
            $txtLibro = 'XVIII DEL REGISTRO MERCANTIL';
            break;
        case "RM19":
            $txtLibro = 'XIX DEL REGISTRO MERCANTIL';
            break;
        case "RM20":
            $txtLibro = 'XX DEL REGISTRO MERCANTIL';
            break;
        case "RM21":
            $txtLibro = 'XXI DEL REGISTRO MERCANTIL';
            break;
        case "RM22":
            $txtLibro = 'XXII DEL REGISTRO MERCANTIL';
            break;
        case "RE51":
            $txtLibro = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE52":
            $txtLibro = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE53":
            $txtLibro = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
            break;
        case "RE54":
            $txtLibro = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
            break;
        case "RE55":
            $txtLibro = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
            break;
    }
    $txt .= 'REGISTRADO EN ESTA CÁMARA DE COMERCIO BAJO EL NÚMERO ' . $registro . ' ';
    $txt .= 'DEL LIBRO ' . $txtLibro . ' EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecins)) . ', ';

    if ($camant != '') {
        $txt .= 'INSCRITO ORIGINALMENTE EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecant)) . ' EN LA ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
        if ($regant != '') {
            $txt .= ' BAJO EL NUMERO ' . $regant;
        }
        if ($libant != '') {
            $txt .= ' DEL LIBRO ' . $libant;
        }
        $txt .= ', ';
    }

    //
    $si = 'no';
    if ($acto == '0030') { // Constitución
        $txt .= 'SE INSCRIBE : ';
        $si = 'si';
    }
    if ($acto == '0040') { // Constitución
        $txt .= 'SE INSCRIBE : ';
        $si = 'si';
    }
    if ($acto == '0042') { // Constitución por cambio de domicilio
        $txt .= 'SE INSCRIBE EL CAMBIO DE DOMICILIO DE : ';
        $si = 'si';
    }
    if ($acto == '0050') { // Constitución
        $txt .= 'SE INSCRIBE : ';
        $si = 'si';
    }
    if ($acto == '0080') { // Constitución
        $txt .= 'SE INSCRIBE : ';
        $si = 'si';
    }

    if ($acto == '0192') { // Oposiciones
        $txt .= 'SE INSCRIBE : ';
        $si = 'si';
    }
    if ($acto == '0400') { // Transformaciones
        $txt .= 'SE INSCRIBE LA TRANSFORMACION : ';
        $si = 'si';
    }
    if ($acto >= '0530' && $acto <= '0540') { // Cancelaciones
        $txt .= 'SE INSCRIBE : ';
        $si = 'si';
    }
    if ($acto >= '0650' && $acto <= '0690') { // Liquidación obligatoria
        $txt .= 'SE INSCRIBE : ';
        $si = 'si';
    }
    if ($libro == 'RM19') { // Reestructuracion
        $txt .= 'SE INSCRIBE : ';
        $si = 'si';
    }
    if ($acto == '0900' || $acto == '1000' || $acto == '1040') { // EMbargos
        $txt .= 'SE DECRETO : ';
        $si = 'si';
    }
    if ($acto == '4000') { // SITIOS WEB
        $txt .= 'SE REGISTRA : ';
        $si = 'si';
    }
    if ($libro == '9997') { // Cambios de jurisdicción
        $txt .= 'SE DECRETÓ : ';
        $si = 'si';
    }
    if ($si == 'no') {
        $txt .= 'SE DECRETÓ : ';
    }

    //
    $pegarNoticia = 'si';
    if ($acto == '0040') { // Constitución
        if (!empty($nan)) {
            if ($tipdoc == '10') {
                $txt .= 'LA ENTIDAD DENOMINADA ' . $nan[1]["nom"];
                $pegarNoticia = 'no';
            } else {
                if ($organizacion == '08') {
                    $txt .= 'LA CONSTITUCIÓN DE LA SUCURSAL DE SOCIEDAD EXTRANJERA DENOMINADA ' . $nan[1]["nom"];
                } else {
                    $txt .= 'LA CONSTITUCIÓN DE PERSONA JURIDICA DENOMINADA ' . $nan[1]["nom"];
                }
                $pegarNoticia = 'no';
            }
        } else {
            if ($nombre != '') {
                $nom1 = \funcionesGenerales::borrarPalabrasAutomaticas($nombre, $comple);
                if ($tipdoc == '10') {
                    $txt .= 'LA ENTIDAD DENOMINADA ' . $nom1;
                    $pegarNoticia = 'no';
                } else {
                    if ($organizacion == '08') {
                        $txt .= 'LA CONSTITUCIÓN DE LA SUCURSAL DE SOCIEDAD EXTRANJERA DENOMINADA ' . $nom1;
                    } else {
                        $txt .= 'LA CONSTITUCIÓN DE PERSONA JURIDICA DENOMINADA ' . $nom1;
                    }
                    $pegarNoticia = 'no';
                }
            }
        }
    }

    //
    if ($acto == '0197' && $libro == 'RM15') { // Cesacion de actividad
        $txt .= 'LA CESACIÓN DE LA ACTIVIDAD COMERCIAL.' . $nombre;
    }

    //
    if ($acto == '0197' && $libro == 'RM06') { // cierre del establecimiento de comercio
        $txt .= 'EL CIERRE DEL ESTABLECIMIENTO DE COMERCIO.' . $nombre;
    }

    // En caso de depuración.
    if ($acto == '0510' && $tipdoc == '38') {
        $txt .= 'LA DISOLUCIÓN POR DEPURACIÓN.';
        $pegarNoticia = 'no';
    }

    if (($acto == '0530' || $acto == '0540') && $tipdoc == '38') {
        $txt .= 'LA CANCELACION POR DEPURACIÓN.';
        $pegarNoticia = 'no';
    }


    //
    if ($pegarNoticia == 'si') {
        $txt .= parsear_a_mayusculas($noticia);
    }

    // Localiza si existe recurso de reposición contra la inscripción
    //    
    return $txt;
}

function descripcionesEmbargos($mysqli, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $txMun, $libro, $registro, $fecins, $noticia, $nan = array(), $nombre = '', $camant = '', $libant = '', $regant = '', $fecant = '') {
    $txtDoc = retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipos_documentales_registro', $tipdoc);
    if ($txtDoc == '') {
        $txtDoc = 'DOCUMENTO';
    }
    $txt = 'POR ';
    $txt .= $txtDoc . ' ';
    if (ltrim(trim($numdocext), "0") != '') {
        $txt .= 'NÚMERO ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && $numdoc != 'NA' && $numdoc != 'N/A' && $numdoc != 'n/A' && $numdoc != '0') {
            $txt .= 'NÚMERO ' . trim($numdoc) . ' ';
        }
    }
    if ($fecdoc != '') {
        $txt .= 'DEL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecdoc)) . ' ';
    }

    //
    $txtSuscribe = '';

    //
    if ($txtorigen != '') {
        if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
            $txtorigen = '';
        }
        $txtorigen = str_replace("NOTARIAS NOTARIA", "NOTARIA", $txtorigen);
        $txtSuscribe = $txtorigen;
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen);
        }
    }

    if ($txtSuscribe == 'REPRESENTACION LEGAL') {
        $txtSuscribe = 'EL REPRESENTANTE LEGAL';
    }
    if ($txtSuscribe == 'COMERCIANTE') {
        $txtSuscribe = 'EL COMERCIANTE';
    }
    if ($txtSuscribe == 'JUNTA DE SOCIOS') {
        $txtSuscribe = 'LA JUNTA DE SOCIOS';
    }
    if ($txtSuscribe == 'JUNTA DIRECTIVA') {
        $txtSuscribe = 'LA JUNTA DIRECTIVA';
    }
    if ($txtSuscribe == 'PROPIETARIO') {
        $txtSuscribe = 'EL PROPIETARIO';
    }
    if ($txtSuscribe == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtSuscribe = 'LA ADMINISTRACIÓN DE IMPUESTOS NACIONALES';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtSuscribe = '';
    }

    //
    if ($txtSuscribe != '') {
        $txt .= 'SUSCRITO POR EL(LA) ' . $txtSuscribe . ', ';
    }

    //
    if ($txMun != '') {
        $txt .= ' DE ' . $txMun . ', ';
    }

    //
    if ($tipdoc == '02') {
        if ($txMun != '') {
            $txt .= ' DE ' . $txMun . ', ';
        }
    }

    //
    $txt = str_replace(" , ", ", ", $txt);

    //
    $txtLibro = '';
    switch ($libro) {
        case "RM01":
            $txtLibro = 'I DEL REGISTRO MERCANTIL';
            break;
        case "RM02":
            $txtLibro = 'II DEL REGISTRO MERCANTIL';
            break;
        case "RM03":
            $txtLibro = 'III DEL REGISTRO MERCANTIL';
            break;
        case "RM04":
            $txtLibro = 'IV DEL REGISTRO MERCANTIL';
            break;
        case "RM05":
            $txtLibro = 'V DEL REGISTRO MERCANTIL';
            break;
        case "RM06":
            $txtLibro = 'VI DEL REGISTRO MERCANTIL';
            break;
        case "RM07":
            $txtLibro = 'VII DEL REGISTRO MERCANTIL';
            break;
        case "RM08":
            $txtLibro = 'VIII DEL REGISTRO MERCANTIL';
            break;
        case "RM09":
            $txtLibro = 'IX DEL REGISTRO MERCANTIL';
            break;
        case "RM10":
            $txtLibro = 'X DEL REGISTRO MERCANTIL';
            break;
        case "RM11":
            $txtLibro = 'XI DEL REGISTRO MERCANTIL';
            break;
        case "RM12":
            $txtLibro = 'XII DEL REGISTRO MERCANTIL';
            break;
        case "RM13":
            $txtLibro = 'XIII DEL REGISTRO MERCANTIL';
            break;
        case "RM14":
            $txtLibro = 'XIV DEL REGISTRO MERCANTIL';
            break;
        case "RM15":
            $txtLibro = 'XV DEL REGISTRO MERCANTIL';
            break;
        case "RM16":
            $txtLibro = 'XVI DEL REGISTRO MERCANTIL';
            break;
        case "RM17":
            $txtLibro = 'XVII DEL REGISTRO MERCANTIL';
            break;
        case "RM18":
            $txtLibro = 'XVIII DEL REGISTRO MERCANTIL';
            break;
        case "RM19":
            $txtLibro = 'XIX DEL REGISTRO MERCANTIL';
            break;
        case "RM20":
            $txtLibro = 'XX DEL REGISTRO MERCANTIL';
            break;
        case "RM21":
            $txtLibro = 'XXI DEL REGISTRO MERCANTIL';
            break;
        case "RM22":
            $txtLibro = 'XXII DEL REGISTRO MERCANTIL';
            break;
        case "RE51":
            $txtLibro = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE52":
            $txtLibro = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE53":
            $txtLibro = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
            break;
        case "RE54":
            $txtLibro = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
            break;
        case "RE55":
            $txtLibro = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
            break;
    }
    $txt .= 'REGISTRADO EN ESTA CÁMARA DE COMERCIO BAJO EL NÚMERO ' . $registro . ' ';
    $txt .= 'DEL LIBRO ' . $txtLibro . ' EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecins)) . ', ';

    if ($camant != '') {
        $txt .= 'INSCRITO ORIGINALMENTE EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecant)) . ' EN LA ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
        if ($regant != '') {
            $txt .= ' BAJO EL NUMERO ' . $regant;
        }
        if ($libant != '') {
            $txt .= ' DEL LIBRO ' . $libant;
        }
        $txt .= ', ';
    }

    //
    $pegarNoticia = 'si';
    if ($pegarNoticia == 'si') {
        $txt .= parsear_a_mayusculas($noticia);
        // $txt .= $noticia;
    }

    // Localiza si existe recurso de reposición contra la inscripción
    //    
    return $txt;
}

/**
 * 
 * @param type $acto
 * @param type $tipdoc
 * @param type $numdoc
 * @param type $fecdoc
 * @param type $idorigen
 * @param type $txtorigen
 * @param type $libro
 * @param type $registro
 * @param type $fecins
 * @param type $noticia
 * @param type $nan
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @return string
 */
function descripcionesSitControl($mysqli, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $libro, $registro = '', $fecins = '', $noticia = '', $nan = array(), $camant = '', $libant = '', $regant = '', $fecant = '') {
    $txtDoc = retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipos_documentales_registro', $tipdoc);
    if ($txtDoc == '') {
        $txtDoc = 'DOCUMENTO';
    }
    $txt = 'POR ';
    $txt .= $txtDoc . ' ';
    if (ltrim(trim($numdocext), "0") != '') {
        $txt .= 'NÚMERO ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && $numdoc != 'NA' && $numdoc != 'N/A' && $numdoc != 'n/A' && $numdoc != '0') {
            $txt .= 'NÚMERO ' . trim($numdoc) . ' ';
        }
    }
    if ($fecdoc != '') {
        $txt .= 'DEL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecdoc)) . ' ';
    }

    //
    $txtSuscribe = '';

    //
    if ($txtorigen != '') {
        if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
            $txtorigen = '';
        }
        $txtorigen = str_replace("NOTARIAS NOTARIA", "NOTARIA", $txtorigen);
        $txtSuscribe = $txtorigen;
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen);
        }
    }


    if ($txtSuscribe == 'REPRESENTACION LEGAL') {
        $txtSuscribe = 'EL REPRESENTANTE LEGAL';
    }
    if ($txtSuscribe == 'COMERCIANTE') {
        $txtSuscribe = 'EL COMERCIANTE';
    }
    if ($txtSuscribe == 'JUNTA DE SOCIOS') {
        $txtSuscribe = 'LA JUNTA DE SOCIOS';
    }
    if ($txtSuscribe == 'JUNTA DIRECTIVA') {
        $txtSuscribe = 'LA JUNTA DIRECTIVA';
    }
    if ($txtSuscribe == 'PROPIETARIO') {
        $txtSuscribe = 'EL PROPIETARIO';
    }
    if ($txtSuscribe == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtSuscribe = 'LA ADMINISTRACIÓN DE IMPUESTOS NACIONALES';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtSuscribe = '';
    }

    //
    if ($txtSuscribe != '') {
        $txt .= 'SUSCRITO POR ' . $txtSuscribe . ', ';
    }

    //
    $txtLibro = '';
    switch ($libro) {
        case "RM01":
            $txtLibro = 'I DEL REGISTRO MERCANTIL';
            break;
        case "RM02":
            $txtLibro = 'II DEL REGISTRO MERCANTIL';
            break;
        case "RM03":
            $txtLibro = 'III DEL REGISTRO MERCANTIL';
            break;
        case "RM04":
            $txtLibro = 'IV DEL REGISTRO MERCANTIL';
            break;
        case "RM05":
            $txtLibro = 'V DEL REGISTRO MERCANTIL';
            break;
        case "RM06":
            $txtLibro = 'VI DEL REGISTRO MERCANTIL';
            break;
        case "RM07":
            $txtLibro = 'VII DEL REGISTRO MERCANTIL';
            break;
        case "RM08":
            $txtLibro = 'VIII DEL REGISTRO MERCANTIL';
            break;
        case "RM09":
            $txtLibro = 'IX DEL REGISTRO MERCANTIL';
            break;
        case "RM10":
            $txtLibro = 'X DEL REGISTRO MERCANTIL';
            break;
        case "RM11":
            $txtLibro = 'XI DEL REGISTRO MERCANTIL';
            break;
        case "RM12":
            $txtLibro = 'XII DEL REGISTRO MERCANTIL';
            break;
        case "RM13":
            $txtLibro = 'XIII DEL REGISTRO MERCANTIL';
            break;
        case "RM14":
            $txtLibro = 'XIV DEL REGISTRO MERCANTIL';
            break;
        case "RM15":
            $txtLibro = 'XV DEL REGISTRO MERCANTIL';
            break;
        case "RM16":
            $txtLibro = 'XVI DEL REGISTRO MERCANTIL';
            break;
        case "RM17":
            $txtLibro = 'XVII DEL REGISTRO MERCANTIL';
            break;
        case "RM18":
            $txtLibro = 'XVIII DEL REGISTRO MERCANTIL';
            break;
        case "RM19":
            $txtLibro = 'XIX DEL REGISTRO MERCANTIL';
            break;
        case "RM20":
            $txtLibro = 'XX DEL REGISTRO MERCANTIL';
            break;
        case "RM21":
            $txtLibro = 'XXI DEL REGISTRO MERCANTIL';
            break;
        case "RM22":
            $txtLibro = 'XXII DEL REGISTRO MERCANTIL';
            break;
        case "RE51":
            $txtLibro = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE52":
            $txtLibro = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE53":
            $txtLibro = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
            break;
        case "RE54":
            $txtLibro = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
            break;
        case "RE55":
            $txtLibro = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
            break;
    }
    $txt .= 'REGISTRADO EN ESTA CÁMARA DE COMERCIO BAJO EL NÚMERO ' . $registro . ' ';
    $txt .= 'DEL LIBRO ' . $txtLibro . ' EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecins)) . ', ';

    if ($camant != '') {
        $txt .= 'INSCRITO ORIGINALMENTE EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecant)) . ' EN LA ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
        if ($regant != '') {
            $txt .= ' BAJO EL NUMERO ' . $regant;
        }
        if ($libant != '') {
            $txtLibroAnt = '';
            switch ($libant) {
                case "RM01":
                    $txtLibroAnt = 'I DEL REGISTRO MERCANTIL';
                    break;
                case "RM02":
                    $txtLibroAnt = 'II DEL REGISTRO MERCANTIL';
                    break;
                case "RM03":
                    $txtLibroAnt = 'III DEL REGISTRO MERCANTIL';
                    break;
                case "RM04":
                    $txtLibroAnt = 'IV DEL REGISTRO MERCANTIL';
                    break;
                case "RM05":
                    $txtLibroAnt = 'V DEL REGISTRO MERCANTIL';
                    break;
                case "RM06":
                    $txtLibroAnt = 'VI DEL REGISTRO MERCANTIL';
                    break;
                case "RM07":
                    $txtLibroAnt = 'VII DEL REGISTRO MERCANTIL';
                    break;
                case "RM08":
                    $txtLibroAnt = 'VIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM09":
                    $txtLibroAnt = 'IX DEL REGISTRO MERCANTIL';
                    break;
                case "RM10":
                    $txtLibroAnt = 'X DEL REGISTRO MERCANTIL';
                    break;
                case "RM11":
                    $txtLibroAnt = 'XI DEL REGISTRO MERCANTIL';
                    break;
                case "RM12":
                    $txtLibroAnt = 'XII DEL REGISTRO MERCANTIL';
                    break;
                case "RM13":
                    $txtLibroAnt = 'XIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM14":
                    $txtLibroAnt = 'XIV DEL REGISTRO MERCANTIL';
                    break;
                case "RM15":
                    $txtLibroAnt = 'XV DEL REGISTRO MERCANTIL';
                    break;
                case "RM16":
                    $txtLibroAnt = 'XVI DEL REGISTRO MERCANTIL';
                    break;
                case "RM17":
                    $txtLibroAnt = 'XVII DEL REGISTRO MERCANTIL';
                    break;
                case "RM18":
                    $txtLibroAnt = 'XVIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM19":
                    $txtLibroAnt = 'XIX DEL REGISTRO MERCANTIL';
                    break;
                case "RM20":
                    $txtLibroAnt = 'XX DEL REGISTRO MERCANTIL';
                    break;
                case "RM21":
                    $txtLibroAnt = 'XXI DEL REGISTRO MERCANTIL';
                    break;
                case "RM22":
                    $txtLibroAnt = 'XXII DEL REGISTRO MERCANTIL';
                    break;
                case "RE51":
                    $txtLibroAnt = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
                    break;
                case "RE52":
                    $txtLibroAnt = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
                    break;
                case "RE53":
                    $txtLibroAnt = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
                    break;
                case "RE54":
                    $txtLibroAnt = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
                    break;
                case "RE55":
                    $txtLibroAnt = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
                    break;
            }
            $txt .= ' DEL LIBRO ' . $txtLibroAnt;
        }
        $txt .= ', ';
    }


    //
    if ($acto == '2000' || $acto == '2010') { // 
        $txt .= 'SE COMUNICÓ QUE SE HA CONFIGURADO UNA SITUACION DE CONTROL: ';
    }
    if ($acto == '2020' || $acto == '2030') { // 
        $txt .= 'SE COMUNICÓ QUE SE HA CONFIGURADO UN GRUPO EMPRESARIAL : ';
    }
    $txt .= '<br><br>' . parsear_a_mayusculas($noticia) . '<br>';
    return $txt;
}

function descripcionesSitControlOriginal($mysqli, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $libro, $registro = '', $fecins = '', $noticia = '', $nan = array(), $camant = '', $libant = '', $regant = '', $fecant = '') {
    $txtDoc = retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipos_documentales_registro', $tipdoc);
    if ($txtDoc == '') {
        $txtDoc = 'DOCUMENTO';
    }
    $txt = 'POR ';
    $txt .= $txtDoc . ' ';
    if (ltrim(trim($numdocext), "0") != '') {
        $txt .= 'NÚMERO ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && $numdoc != 'NA' && $numdoc != 'N/A' && $numdoc != 'n/A' && $numdoc != '0') {
            $txt .= 'NÚMERO ' . trim($numdoc) . ' ';
        }
    }
    if ($fecdoc != '') {
        $txt .= 'DEL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecdoc)) . ' ';
    }

    //
    $txtSuscribe = '';
    if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
        $txtSuscribe = retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen);
    } else {
        if ($txtorigen != '') {
            $txtSuscribe = $txtorigen;
        }
    }
    if ($txtSuscribe == 'REPRESENTACION LEGAL') {
        $txtSuscribe = 'EL REPRESENTANTE LEGAL';
    }
    if ($txtSuscribe == 'COMERCIANTE') {
        $txtSuscribe = 'EL COMERCIANTE';
    }
    if ($txtSuscribe == 'JUNTA DE SOCIOS') {
        $txtSuscribe = 'LA JUNTA DE SOCIOS';
    }
    if ($txtSuscribe == 'JUNTA DIRECTIVA') {
        $txtSuscribe = 'LA JUNTA DIRECTIVA';
    }
    if ($txtSuscribe == 'PROPIETARIO') {
        $txtSuscribe = 'EL PROPIETARIO';
    }
    if ($txtSuscribe == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtSuscribe = 'LA ADMINISTRACIÓN DE IMPUESTOS NACIONALES';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtSuscribe = '';
    }

    //
    if ($txtSuscribe != '') {
        $txt .= 'SUSCRITO POR ' . $txtSuscribe . ', ';
    }

    //
    $txtLibro = '';
    switch ($libro) {
        case "RM01":
            $txtLibro = 'I DEL REGISTRO MERCANTIL';
            break;
        case "RM02":
            $txtLibro = 'II DEL REGISTRO MERCANTIL';
            break;
        case "RM03":
            $txtLibro = 'III DEL REGISTRO MERCANTIL';
            break;
        case "RM04":
            $txtLibro = 'IV DEL REGISTRO MERCANTIL';
            break;
        case "RM05":
            $txtLibro = 'V DEL REGISTRO MERCANTIL';
            break;
        case "RM06":
            $txtLibro = 'VI DEL REGISTRO MERCANTIL';
            break;
        case "RM07":
            $txtLibro = 'VII DEL REGISTRO MERCANTIL';
            break;
        case "RM08":
            $txtLibro = 'VIII DEL REGISTRO MERCANTIL';
            break;
        case "RM09":
            $txtLibro = 'IX DEL REGISTRO MERCANTIL';
            break;
        case "RM10":
            $txtLibro = 'X DEL REGISTRO MERCANTIL';
            break;
        case "RM11":
            $txtLibro = 'XI DEL REGISTRO MERCANTIL';
            break;
        case "RM12":
            $txtLibro = 'XII DEL REGISTRO MERCANTIL';
            break;
        case "RM13":
            $txtLibro = 'XIII DEL REGISTRO MERCANTIL';
            break;
        case "RM14":
            $txtLibro = 'XIV DEL REGISTRO MERCANTIL';
            break;
        case "RM15":
            $txtLibro = 'XV DEL REGISTRO MERCANTIL';
            break;
        case "RM16":
            $txtLibro = 'XVI DEL REGISTRO MERCANTIL';
            break;
        case "RM17":
            $txtLibro = 'XVII DEL REGISTRO MERCANTIL';
            break;
        case "RM18":
            $txtLibro = 'XVIII DEL REGISTRO MERCANTIL';
            break;
        case "RM19":
            $txtLibro = 'XIX DEL REGISTRO MERCANTIL';
            break;
        case "RM20":
            $txtLibro = 'XX DEL REGISTRO MERCANTIL';
            break;
        case "RM21":
            $txtLibro = 'XXI DEL REGISTRO MERCANTIL';
            break;
        case "RM22":
            $txtLibro = 'XXII DEL REGISTRO MERCANTIL';
            break;
        case "RE51":
            $txtLibro = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE52":
            $txtLibro = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE53":
            $txtLibro = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
            break;
        case "RE54":
            $txtLibro = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
            break;
        case "RE55":
            $txtLibro = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
            break;
    }
    $txt .= 'REGISTRADO EN ESTA CÁMARA DE COMERCIO BAJO EL NÚMERO ' . $registro . ' ';
    $txt .= 'DEL LIBRO ' . $txtLibro . ' EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecins)) . ', ';

    if ($camant != '') {
        $txt .= 'INSCRITO ORIGINALMENTE EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecant)) . ' EN LA ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
        if ($regant != '') {
            $txt .= ' BAJO EL NUMERO ' . $regant;
        }
        if ($libant != '') {
            $txtLibroAnt = '';
            switch ($libant) {
                case "RM01":
                    $txtLibroAnt = 'I DEL REGISTRO MERCANTIL';
                    break;
                case "RM02":
                    $txtLibroAnt = 'II DEL REGISTRO MERCANTIL';
                    break;
                case "RM03":
                    $txtLibroAnt = 'III DEL REGISTRO MERCANTIL';
                    break;
                case "RM04":
                    $txtLibroAnt = 'IV DEL REGISTRO MERCANTIL';
                    break;
                case "RM05":
                    $txtLibroAnt = 'V DEL REGISTRO MERCANTIL';
                    break;
                case "RM06":
                    $txtLibroAnt = 'VI DEL REGISTRO MERCANTIL';
                    break;
                case "RM07":
                    $txtLibroAnt = 'VII DEL REGISTRO MERCANTIL';
                    break;
                case "RM08":
                    $txtLibroAnt = 'VIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM09":
                    $txtLibroAnt = 'IX DEL REGISTRO MERCANTIL';
                    break;
                case "RM10":
                    $txtLibroAnt = 'X DEL REGISTRO MERCANTIL';
                    break;
                case "RM11":
                    $txtLibroAnt = 'XI DEL REGISTRO MERCANTIL';
                    break;
                case "RM12":
                    $txtLibroAnt = 'XII DEL REGISTRO MERCANTIL';
                    break;
                case "RM13":
                    $txtLibroAnt = 'XIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM14":
                    $txtLibroAnt = 'XIV DEL REGISTRO MERCANTIL';
                    break;
                case "RM15":
                    $txtLibroAnt = 'XV DEL REGISTRO MERCANTIL';
                    break;
                case "RM16":
                    $txtLibroAnt = 'XVI DEL REGISTRO MERCANTIL';
                    break;
                case "RM17":
                    $txtLibroAnt = 'XVII DEL REGISTRO MERCANTIL';
                    break;
                case "RM18":
                    $txtLibroAnt = 'XVIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM19":
                    $txtLibroAnt = 'XIX DEL REGISTRO MERCANTIL';
                    break;
                case "RM20":
                    $txtLibroAnt = 'XX DEL REGISTRO MERCANTIL';
                    break;
                case "RM21":
                    $txtLibroAnt = 'XXI DEL REGISTRO MERCANTIL';
                    break;
                case "RM22":
                    $txtLibroAnt = 'XXII DEL REGISTRO MERCANTIL';
                    break;
                case "RE51":
                    $txtLibroAnt = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
                    break;
                case "RE52":
                    $txtLibroAnt = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
                    break;
                case "RE53":
                    $txtLibroAnt = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
                    break;
                case "RE54":
                    $txtLibroAnt = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
                    break;
                case "RE55":
                    $txtLibroAnt = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
                    break;
            }
            $txt .= ' DEL LIBRO ' . $txtLibroAnt;
        }
        $txt .= ', ';
    }


    //
    if ($acto == '2000') { // 
        $txt .= 'SE COMUNICÓ QUE SE HA CONFIGURADO UNA SITUACION DE CONTROL POR PARTE DE LA ';
        $txt .= 'SOCIEDAD MATRIZ.';
    }
    if ($acto == '2020') { // 
        $txt .= 'SE COMUNICÓ QUE SE HA CONFIGURADO UN GRUPO EMPRESARIAL POR PARTE DE LA ';
        $txt .= 'SOCIEDAD MATRIZ.';
    }
    if ($acto == '2010') { // 
        $txt .= 'COMUNICÓ LA SOCIEDAD MATRIZ QUE SE HA CONFIGURADO UNA SITUACIÓN DE CONTROL ';
        $txt .= 'CON LA PERSONA JURÍDICA DE LA REFERENCIA.';
    }
    if ($acto == '2030') { // 
        $txt .= 'COMUNICÓ LA SOCIEDAD MATRIZ QUE SE HA CONFIGURADO UN GRUPO EMPRESARIAL ';
        $txt .= 'CON LA PERSONA JURÍDICA DE LA REFERENCIA.';
    }
    return $txt;
}

// *************************************************************************** //
// Descripcion de vínculos
// *************************************************************************** //
function descripcionesVinculos($mysqli, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro = '', $registro = '', $fecins = '', $noticia = '', $camant = '', $libant = '', $regant = '', $fecant = '') {
    $txtDoc = retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipos_documentales_registro', $tipdoc);
    if ($txtDoc == '') {
        $txtDoc = 'DOCUMENTO';
    }
    $txt = 'POR ';
    $txt .= $txtDoc . ' ';
    if (ltrim(trim($numdocext), "0") != '') {
        $txt .= 'NÚMERO ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && $numdoc != 'NA' && $numdoc != 'N/A' && $numdoc != 'n/A' && $numdoc != '0') {
            $txt .= 'NÚMERO ' . trim($numdoc) . ' ';
        }
    }
    if ($fecdoc != '') {
        $txt .= 'DEL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecdoc)) . ' ';
    }

    //
    $txtSuscribe = '';

    //
    if ($txtorigen != '') {
        if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
            $txtorigen = '';
        }
        $txtorigen = str_replace("NOTARIAS NOTARIA", "NOTARIA", $txtorigen);
        $txtorigen = str_replace("ACTAS ", "", $txtorigen);
        $txtSuscribe = $txtorigen;
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen);
        }
    }

    //
    if ($txtSuscribe == 'REPRESENTACION LEGAL') {
        $txtSuscribe = 'EL REPRESENTANTE LEGAL';
    }
    if ($txtSuscribe == 'COMERCIANTE') {
        $txtSuscribe = 'EL COMERCIANTE';
    }
    if ($txtSuscribe == 'JUNTA DE SOCIOS') {
        $txtSuscribe = 'LA JUNTA DE SOCIOS';
    }
    if ($txtSuscribe == 'JUNTA DIRECTIVA') {
        $txtSuscribe = 'LA JUNTA DIRECTIVA';
    }
    if ($txtSuscribe == 'PROPIETARIO') {
        $txtSuscribe = 'EL PROPIETARIO';
    }
    if ($txtSuscribe == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtSuscribe = 'LA ADMINISTRACIÓN DE IMPUESTOS NACIONALES';
    }


    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtSuscribe = '';
    }

    /*
      if ($txtSuscribe != '') {
      $txt .= 'SUSCRITO POR ' . $txtSuscribe . ' ';
      }
     */

    if ($txtSuscribe != '') {
        $txt .= ' DE ' . $txtSuscribe;
    }

    //
    if ($tipdoc == '02' || $tipdoc == '04') {
        if ($munori != '' && $munori != '00000' && $munori != '99999') {
            $txt .= ' DE ' . retornarNombreMunicipioMysqliApi($mysqli, $munori) . ', ';
        } else {
            $txt .= ', ';
        }
    } else {
        $txt .= ', ';
    }

    //
    $txt = str_replace(" , ", ", ", $txt);

    //
    $txtLibro = '';
    switch ($libro) {
        case "RM01":
            $txtLibro = 'I DEL REGISTRO MERCANTIL';
            break;
        case "RM02":
            $txtLibro = 'II DEL REGISTRO MERCANTIL';
            break;
        case "RM03":
            $txtLibro = 'III DEL REGISTRO MERCANTIL';
            break;
        case "RM04":
            $txtLibro = 'IV DEL REGISTRO MERCANTIL';
            break;
        case "RM05":
            $txtLibro = 'V DEL REGISTRO MERCANTIL';
            break;
        case "RM06":
            $txtLibro = 'VI DEL REGISTRO MERCANTIL';
            break;
        case "RM07":
            $txtLibro = 'VII DEL REGISTRO MERCANTIL';
            break;
        case "RM08":
            $txtLibro = 'VIII DEL REGISTRO MERCANTIL';
            break;
        case "RM09":
            $txtLibro = 'IX DEL REGISTRO MERCANTIL';
            break;
        case "RM10":
            $txtLibro = 'X DEL REGISTRO MERCANTIL';
            break;
        case "RM11":
            $txtLibro = 'XI DEL REGISTRO MERCANTIL';
            break;
        case "RM12":
            $txtLibro = 'XII DEL REGISTRO MERCANTIL';
            break;
        case "RM13":
            $txtLibro = 'XIII DEL REGISTRO MERCANTIL';
            break;
        case "RM14":
            $txtLibro = 'XIV DEL REGISTRO MERCANTIL';
            break;
        case "RM15":
            $txtLibro = 'XV DEL REGISTRO MERCANTIL';
            break;
        case "RM16":
            $txtLibro = 'XVI DEL REGISTRO MERCANTIL';
            break;
        case "RM17":
            $txtLibro = 'XVII DEL REGISTRO MERCANTIL';
            break;
        case "RM18":
            $txtLibro = 'XVIII DEL REGISTRO MERCANTIL';
            break;
        case "RM19":
            $txtLibro = 'XIX DEL REGISTRO MERCANTIL';
            break;
        case "RM20":
            $txtLibro = 'XX DEL REGISTRO MERCANTIL';
            break;
        case "RM21":
            $txtLibro = 'XXI DEL REGISTRO MERCANTIL';
            break;
        case "RM22":
            $txtLibro = 'XXII DEL REGISTRO MERCANTIL';
            break;
        case "RE51":
            $txtLibro = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE52":
            $txtLibro = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE53":
            $txtLibro = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
            break;
        case "RE54":
            $txtLibro = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
            break;
        case "RE55":
            $txtLibro = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
            break;
    }
    $txt .= 'REGISTRADO EN ESTA CÁMARA DE COMERCIO BAJO EL NÚMERO ' . $registro . ' ';
    $txt .= 'DEL LIBRO ' . $txtLibro . ' EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecins)) . ', ';

    if ($camant != '') {
        $txt .= 'INSCRITO ORIGINALMENTE EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecant)) . ' EN LA ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
        if ($regant != '') {
            $txt .= ' BAJO EL NUMERO ' . $regant;
        }
        if ($libant != '') {
            $txtLibroAnt = '';
            switch ($libant) {
                case "RM01":
                    $txtLibroAnt = 'I DEL REGISTRO MERCANTIL';
                    break;
                case "RM02":
                    $txtLibroAnt = 'II DEL REGISTRO MERCANTIL';
                    break;
                case "RM03":
                    $txtLibroAnt = 'III DEL REGISTRO MERCANTIL';
                    break;
                case "RM04":
                    $txtLibroAnt = 'IV DEL REGISTRO MERCANTIL';
                    break;
                case "RM05":
                    $txtLibroAnt = 'V DEL REGISTRO MERCANTIL';
                    break;
                case "RM06":
                    $txtLibroAnt = 'VI DEL REGISTRO MERCANTIL';
                    break;
                case "RM07":
                    $txtLibroAnt = 'VII DEL REGISTRO MERCANTIL';
                    break;
                case "RM08":
                    $txtLibroAnt = 'VIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM09":
                    $txtLibroAnt = 'IX DEL REGISTRO MERCANTIL';
                    break;
                case "RM10":
                    $txtLibroAnt = 'X DEL REGISTRO MERCANTIL';
                    break;
                case "RM11":
                    $txtLibroAnt = 'XI DEL REGISTRO MERCANTIL';
                    break;
                case "RM12":
                    $txtLibroAnt = 'XII DEL REGISTRO MERCANTIL';
                    break;
                case "RM13":
                    $txtLibroAnt = 'XIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM14":
                    $txtLibroAnt = 'XIV DEL REGISTRO MERCANTIL';
                    break;
                case "RM15":
                    $txtLibroAnt = 'XV DEL REGISTRO MERCANTIL';
                    break;
                case "RM16":
                    $txtLibroAnt = 'XVI DEL REGISTRO MERCANTIL';
                    break;
                case "RM17":
                    $txtLibroAnt = 'XVII DEL REGISTRO MERCANTIL';
                    break;
                case "RM18":
                    $txtLibroAnt = 'XVIII DEL REGISTRO MERCANTIL';
                    break;
                case "RM19":
                    $txtLibroAnt = 'XIX DEL REGISTRO MERCANTIL';
                    break;
                case "RM20":
                    $txtLibroAnt = 'XX DEL REGISTRO MERCANTIL';
                    break;
                case "RM21":
                    $txtLibroAnt = 'XXI DEL REGISTRO MERCANTIL';
                    break;
                case "RM22":
                    $txtLibroAnt = 'XXII DEL REGISTRO MERCANTIL';
                    break;
                case "RE51":
                    $txtLibroAnt = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
                    break;
                case "RE52":
                    $txtLibroAnt = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
                    break;
                case "RE53":
                    $txtLibroAnt = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
                    break;
                case "RE54":
                    $txtLibroAnt = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
                    break;
                case "RE55":
                    $txtLibroAnt = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
                    break;
            }
            $txt .= ' DEL LIBRO ' . $txtLibroAnt;
        }
        $txt .= ', ';
    }

    $txt .= 'FUERON NOMBRADOS : ';
    return $txt;
}

/**
 * 
 * @param type $tipdoc
 * @param type $numdoc
 * @param type $numdocext
 * @param type $fecdoc
 * @param type $idorigen
 * @param type $txtorigen
 * @param type $munori
 * @param type $libro
 * @param type $registro
 * @param type $fecins
 * @param type $noticia
 * @param type $organizacion
 * @param type $categoria
 * @return string
 */
function descripcionesCambioNombre($mysqli, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro = '', $registro = '', $fecins = '', $noticia = '', $camant = '', $libant = '', $regant = '', $fecant = '', $organizacion = '', $categoria = '') {
    $txtDoc = retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipos_documentales_registro', $tipdoc);
    if ($txtDoc == '') {
        $txtDoc = 'DOCUMENTO';
    }
    $txt = 'POR ';
    $txt .= $txtDoc . ' ';
    if (ltrim(trim($numdocext), "0") != '') {
        $txt .= 'NÚMERO ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && $numdoc != '0' && $numdoc != 'SN' && $numdoc != 'S/N' && $numdoc != 'NA' && $numdoc != 'N/A' && $numdoc != 'n/A' && $numdoc != '0') {
            $txt .= 'NÚMERO ' . trim($numdoc) . ' ';
        }
    }
    if ($fecdoc != '') {
        $txt .= 'DEL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecdoc)) . ' ';
    }

    //
    $txtSuscribe = '';

    //
    if ($txtorigen != '') {
        if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
            $txtorigen = '';
        }
        $txtorigen = str_replace("NOTARIAS NOTARIA", "NOTARIA", $txtorigen);
        $txtSuscribe = $txtorigen;
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen);
        }
    }

    //
    if ($txtSuscribe == 'REPRESENTACION LEGAL') {
        $txtSuscribe = 'EL REPRESENTANTE LEGAL';
    }
    if ($txtSuscribe == 'COMERCIANTE') {
        $txtSuscribe = 'EL COMERCIANTE';
    }
    if ($txtSuscribe == 'JUNTA DE SOCIOS') {
        $txtSuscribe = 'LA JUNTA DE SOCIOS';
    }
    if ($txtSuscribe == 'JUNTA DIRECTIVA') {
        $txtSuscribe = 'LA JUNTA DIRECTIVA';
    }
    if ($txtSuscribe == 'PROPIETARIO') {
        $txtSuscribe = 'EL PROPIETARIO';
    }
    if ($txtSuscribe == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtSuscribe = 'LA ADMINISTRACIÓN DE IMPUESTOS NACIONALES';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtSuscribe = '';
    }

    //
    if ($txtSuscribe != '') {
        $txt .= 'SUSCRITO POR ' . $txtSuscribe . ' ';
    }


    //
    if ($tipdoc == '02') {
        if ($munori != '' && $munori != '00000' && $munori != '99999') {
            $txt .= ' DE ' . retornarNombreMunicipioMysqliApi($mysqli, $munori) . ', ';
        }
    }

    //
    $txt = str_replace(" , ", ", ", $txt);

    //
    $txtLibro = '';
    switch ($libro) {
        case "RM01":
            $txtLibro = 'I DEL REGISTRO MERCANTIL';
            break;
        case "RM02":
            $txtLibro = 'II DEL REGISTRO MERCANTIL';
            break;
        case "RM03":
            $txtLibro = 'III DEL REGISTRO MERCANTIL';
            break;
        case "RM04":
            $txtLibro = 'IV DEL REGISTRO MERCANTIL';
            break;
        case "RM05":
            $txtLibro = 'V DEL REGISTRO MERCANTIL';
            break;
        case "RM06":
            $txtLibro = 'VI DEL REGISTRO MERCANTIL';
            break;
        case "RM07":
            $txtLibro = 'VII DEL REGISTRO MERCANTIL';
            break;
        case "RM08":
            $txtLibro = 'VIII DEL REGISTRO MERCANTIL';
            break;
        case "RM09":
            $txtLibro = 'IX DEL REGISTRO MERCANTIL';
            break;
        case "RM10":
            $txtLibro = 'X DEL REGISTRO MERCANTIL';
            break;
        case "RM11":
            $txtLibro = 'XI DEL REGISTRO MERCANTIL';
            break;
        case "RM12":
            $txtLibro = 'XII DEL REGISTRO MERCANTIL';
            break;
        case "RM13":
            $txtLibro = 'XIII DEL REGISTRO MERCANTIL';
            break;
        case "RM14":
            $txtLibro = 'XIV DEL REGISTRO MERCANTIL';
            break;
        case "RM15":
            $txtLibro = 'XV DEL REGISTRO MERCANTIL';
            break;
        case "RM16":
            $txtLibro = 'XVI DEL REGISTRO MERCANTIL';
            break;
        case "RM17":
            $txtLibro = 'XVII DEL REGISTRO MERCANTIL';
            break;
        case "RM18":
            $txtLibro = 'XVIII DEL REGISTRO MERCANTIL';
            break;
        case "RM19":
            $txtLibro = 'XIX DEL REGISTRO MERCANTIL';
            break;
        case "RM20":
            $txtLibro = 'XX DEL REGISTRO MERCANTIL';
            break;
        case "RM21":
            $txtLibro = 'XXI DEL REGISTRO MERCANTIL';
            break;
        case "RM22":
            $txtLibro = 'XXII DEL REGISTRO MERCANTIL';
            break;
        case "RE51":
            $txtLibro = 'I DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE52":
            $txtLibro = 'II DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO';
            break;
        case "RE53":
            $txtLibro = 'III DEL REGISTRO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
            break;
        case "RE54":
            $txtLibro = 'IV DEL REGISTRO DE ENTIDADES DE VEEDURÍA CIUDADANA';
            break;
        case "RE55":
            $txtLibro = 'V DEL REGISTRO DE LAS ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
            break;
    }
    $txt .= 'REGISTRADO EN ESTA CÁMARA DE COMERCIO BAJO EL NÚMERO ' . $registro . ' ';
    $txt .= 'DEL LIBRO ' . $txtLibro . ' EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($fecins)) . ', ';
    if ($categoria == '2') {
        $txt .= 'LA SUCURSAL CAMBIO SU NOMBRE DE ';
    } else {
        if ($categoria == '3') {
            $txt .= 'LA AGENCIA CAMBIO SU NOMBRE DE ';
        } else {
            $txt .= 'LA PERSONA JURIDICA CAMBIO SU NOMBRE DE ';
        }
    }

    return $txt;
}

// *************************************************************************** //
// Información sacada del formulario
// *************************************************************************** //    
function armarCertificaInformacionFormularios($pdf) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = '<strong>CERTIFICA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
    $txt = 'LA INFORMACIÓN ANTERIOR HA SIDO TOMADA DIRECTAMENTE DEL FORMULARIO DE MATRÍCULA Y RENOVACIÓN DILIGENCIADO POR EL COMERCIANTE';
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->Ln();
}

//
// *************************************************************************** //
// Firmeza de inscripciones
// *************************************************************************** //      
function armarCertificaFirmeza($pdf) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = '<strong>CERTIFICA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
    $txt = 'DE CONFORMIDAD CON LO ESTABLECIDO EN EL CÓDIGO DE PROCEDIMIENTO ADMINISTRATIVO Y DE LO CONTENCIOSO Y DE LA ';
    $txt .= 'LEY 962 DE 2005, LOS ACTOS ADMINISTRATIVOS DE REGISTRO AQUÍ CERTIFICADOS QUEDAN EN FIRME DIEZ (10) DÍAS ';
    $txt .= 'HÁBILES DESPUES DE LA FECHA DE INSCRIPCIÓN, SIEMPRE QUE NO SEAN OBJETO DE RECURSOS. EL DÍA SÁBADO NO ';
    $txt .= 'SE DEBE CONTAR COMO DÍA HÁBIL.';
    $pdf->SetFont('courier', '', 9);
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->Ln();
}

function armarTextoElecciones($pdf) {
    //
    $textoElecciones = 'no';
    if (
            date("Y") == '2018' ||
            date("Y") == '2022' ||
            date("Y") == '2026' ||
            date("Y") == '2030'
    ) {
        if (date("md") >= '0801') {

            // 2018-12-10: JINT: Se ajusta mensaje para la CC de Santa Marta
            // Aplazamiento de las elecciones poara el 20 de diciembre de 2018.
            if (CODIGO_EMPRESA == '06' || CODIGO_EMPRESA == '26' || CODIGO_EMPRESA == '32') {
                if (date("Y") == '2018' && date("md") <= '1220') {
                    $textoElecciones = 'si';
                }
                if (date("Y") == '2022' && date("md") <= '1201') {
                    $textoElecciones = 'si';
                }
                if (date("Y") == '2026' && date("md") <= '1203') {
                    $textoElecciones = 'si';
                }
                if (date("Y") == '2030' && date("md") <= '1205') {
                    $textoElecciones = 'si';
                }
            }

            if (CODIGO_EMPRESA != '06' && CODIGO_EMPRESA != '26' && CODIGO_EMPRESA != '32') {
                if (date("Y") == '2018' && date("md") <= '1206') {
                    $textoElecciones = 'si';
                }
                if (date("Y") == '2022' && date("md") <= '1201') {
                    $textoElecciones = 'si';
                }
                if (date("Y") == '2026' && date("md") <= '1203') {
                    $textoElecciones = 'si';
                }
                if (date("Y") == '2030' && date("md") <= '1205') {
                    $textoElecciones = 'si';
                }
            }
        }
    }
    if ($textoElecciones == 'no') {
        return true;
    }

    //
    $pdf->SetFont('courier', '', 9);

    $ya = 'no';

    //
    if (CODIGO_EMPRESA == '55') {
        $txt = '<strong>"EL JUEVES 6 DE DICIEMBRE DE 2018 SE ELEGIRÁ JUNTA DIRECTIVA EN LA CÁMARA DE COMERCIO ABURRA SUR. ';
        $txt .= 'LA INSCRIPCIÓN DE LISTAS DE CANDIDATOS DEBE HACERSE DURANTE LA SEGUNDA QUINCENA DEL MES DE OCTUBRE DE 2018.<br><br>';
        $txt .= 'PARA INFORMACIÓN DETALLADA PODRÁ COMUNICARSE AL TELÉFONO 444.23.44 EXTENSIONES 1100, 1101 Y 1500, O ';
        $txt .= 'DIRIGIRSE A LA SEDE PRINCIPAL UBICADA EN EL MUNICIPIO DE ITAGUI CALLE 48 NRO. 50-16, O A TRAVÉS DE LA PÁGINA ';
        $txt .= 'WEB  www.ccas.org.co"</strong>';
        $ya = 'si';
    }

    if (CODIGO_EMPRESA == '23X') {
        $txt = '<strong>"EL PRIMER JUEVES HÁBIL DE DICIEMBRE DE ESTE AÑO SE ELEGIRÁ JUNTA DIRECTIVA EN LA CÁMARA DE NEIVA POR AFILIADOS. ';
        $txt .= 'LA INSCRIPCIÓN DE LISTAS DE CANDIDATOS DEBE HACERSE DURANTE LA SEGUNDA QUINCENA DEL MES DE OCTUBRE.<br><br>';
        $txt .= 'PARA INFORMACIÓN DETALLADA PODRÁ COMUNICARSE AL TELÉFONO 8713666 EXTENSIONES 130 Y 127 O ';
        $txt .= 'DIRIGIRSE A LA SEDE PRINCIPAL, A LAS SEDES SECCIONALES DE PITALITO, OFICINA DE GARZÓN O LA PLATA, O A TRAVÉS DE LA PÁGINA ';
        $txt .= 'WEB www.ccneiva.org"</strong>';
        $ya = 'si';
    }

    // 2018-12-10: JINT: Se ajusta mensaje para la CC de Santa Marta
    // Aplazamiento de las elecciones poara el 20 de diciembre de 2018.
    if ((CODIGO_EMPRESA == '06' || CODIGO_EMPRESA == '26' || CODIGO_EMPRESA == '32') && date("Y") == '2018') {
        $telx = TELEFONO_AFILIADOS;
        if (TELEFONO_AFILIADOS == '') {
            $telx = TELEFONO_ATENCION_USUARIOS;
        }
        $txt = '<strong>"EL JUEVES 20 DE DICIEMBRE DE ESTE AÑO SE ELEGIRÁ JUNTA DIRECTIVA DE LA ' . RAZONSOCIAL . '.<br><br>';
        $txt .= 'LA INSCRIPCIÓN DE LISTAS DE CANDIDATOS DEBE HACERSE DURANTE LA SEGUNDA QUINCENA DEL MES DE OCTUBRE.<br><br>';
        $txt .= 'PARA INFORMACIÓN DETALLADA PODRÁ COMUNICARSE AL TELÉFONO ' . $telx . ' O DIRIGIRSE A LA SEDE ';
        $txt .= 'PRINCIPAL, A LAS SEDES AUTORIZADAS PARA ESTE EFECTO';
        if (trim(WWW_ENTIDAD) != '') {
            $txt .= ', O A TRAVÉS DE LA PÁGINA WEB ' . WWW_ENTIDAD;
        }
        if (trim(WWW_ENTIDAD) == '') {
            $txt .= '.';
        }
        $txt .= '"</strong>';
        $ya = 'si';
    }

    //
    if ($ya == 'no') {
        $telx = TELEFONO_AFILIADOS;
        if (TELEFONO_AFILIADOS == '') {
            $telx = TELEFONO_ATENCION_USUARIOS;
        }
        $txt = '<strong>"EL PRIMER JUEVES HÁBIL DE DICIEMBRE DE ESTE AÑO SE ELEGIRÁ JUNTA DIRECTIVA DE LA ' . RAZONSOCIAL . '.<br><br>';
        $txt .= 'LA INSCRIPCIÓN DE LISTAS DE CANDIDATOS DEBE HACERSE DURANTE LA SEGUNDA QUINCENA DEL MES DE OCTUBRE.<br><br>';
        $txt .= 'PARA INFORMACIÓN DETALLADA PODRÁ COMUNICARSE AL TELÉFONO ' . $telx . ' O DIRIGIRSE A LA SEDE ';
        $txt .= 'PRINCIPAL, A LAS SEDES AUTORIZADAS PARA ESTE EFECTO';
        if (trim(WWW_ENTIDAD) != '') {
            $txt .= ', O A TRAVÉS DE LA PÁGINA WEB ' . WWW_ENTIDAD;
        }
        if (trim(WWW_ENTIDAD) == '') {
            $txt .= '.';
        }
        $txt .= '"</strong>';
    }


    // $pdf->SetFont('helvetica', '', 7);
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->writeHTML('<span style="text-align:center;">*************************************************************************************</span>', true, false, true, false);
    $pdf->Ln();
}

// *************************************************************************** //
// Valor del certificado
// *************************************************************************** //  
function armarTextoValorCertificado($pdf, $tipo, $valor) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($tipo == 'Normal') {
        $txt = 'VALOR DEL CERTIFICADO : $' . number_format($valor, 0);
        $pdf->SetFont('courier', '', 7);
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
    }
}

// *************************************************************************** //
// texto del tipogsto del certificado
// *************************************************************************** //
function armarTextoTipoGasto($pdf, $tipo) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = '';
    switch ($tipo) {
        case "Consulta":
            $txt = 'CONSULTA SIN VALIDEZ JURIDICA';
            break;
        case "GasAdm":
            $txt = 'CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII)';
            break;
        case "GasAfi":
            $txt = 'CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A AFILIADOS';
            break;
        case "GasOfi":
            $txt = 'CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES CON DESTINO A ENTIDAD OFICIAL';
            break;
        case "Normal":
            $txt = 'CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII)';
            break;
        case "Dispensador":
            $txt = 'CERTIFICADO EXPEDIDO A TRAVES DEL DISPENSADOR DE CERTIFICADOS DE LA CAMARA DE COMERCIO';
            break;
        default:
            $txt = 'CERTIFICADO EXPEDIDO A TRAVES DEL PORTAL DE SERVICIOS VIRTUALES (SII)';
            break;
    }
    $pdf->SetFont('helvetica', '', 7);
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->Ln();
}

function armarTextoFirma($pdf, $aleatorio, $tipoFirma) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($aleatorio != '') {
        if ($tipoFirma == 'FIRMA_SECRETARIO' || $tipoFirma == 'CERTITOKEN') {
            $txt = 'IMPORTANTE: La firma digital del secretario de la ' . RAZONSOCIAL . ' contenida en este certificado electrónico '
                    . 'se encuentra emitida por una entidad de certificación abierta autorizada y vigilada por la Superintendencia de Industria '
                    . 'y Comercio, de conformidad con las exigencias establecidas en la Ley 527 de 1999 para validez jurídica y probatoria '
                    . 'de los documentos electrónicos.' . "\n";
            $pdf->SetFont('helvetica', '', 7);
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
        }

        if ($tipoFirma == 'FIRMA_PERJUR') {
            $txt = 'IMPORTANTE: La firma digital de la ' . RAZONSOCIAL . ' contenida en este certificado electrónico '
                    . 'se encuentra emitida por una entidad de certificación abierta autorizada y vigilada por la Superintendencia de Industria '
                    . 'y Comercio, de conformidad con las exigencias establecidas en la Ley 527 de 1999 para validez jurídica y probatoria '
                    . 'de los documentos electrónicos.' . "\n";
            $pdf->SetFont('helvetica', '', 7);
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
        }
    }
}

function armarTextoFirmaQueEs($pdf, $aleatorio) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($aleatorio != '') {
        $txt = 'La firma digital no es una firma digitalizada o escaneada, por lo tanto, la firma digital que acompaña este documento la podrá '
                . 'verificar a través de su aplicativo visor de documentos pdf.' . "\n";
        $pdf->SetFont('helvetica', '', 7);
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    }
}

function armarTextoFirmaImpresion($pdf, $aleatorio) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($aleatorio != '') {
        $txt = 'No obstante, si usted va a imprimir este certificado, lo puede hacer desde su computador, con la certeza de que el mismo fue '
                . 'expedido a través del canal virtual de la cámara de comercio y que la persona o entidad a la que usted le va a entregar el certificado '
                . 'impreso, puede verificar por una sola vez el contenido del mismo, ingresando al enlace ' . TIPO_HTTP . HTTP_HOST . '/cv.php '
                . 'seleccionando la cámara de comercio e indicando el código de verificación ' . $aleatorio . "\n";
        $pdf->SetFont('helvetica', '', 7);
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    }
}

function armarTextoFirmaVerificacion($pdf, $aleatorio) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($aleatorio != '') {
        $txt = 'Al realizar la verificación podrá visualizar (y descargar) una imagen exacta del certificado que fue entregado al usuario en el momento '
                . 'que se realizó la transacción.' . "\n";
        $pdf->SetFont('helvetica', '', 7);
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    }
}

function armarTextoFirmaMecanica($pdf) {
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = 'La firma mecánica que se muestra a continuación es la representación gráfica de la firma del secretario jurídico (o de quien haga sus veces) '
            . 'de la Cámara de Comercio quien avala este certificado. La firma mecánica no reemplaza la firma digital en los documentos electrónicos.' . "\n\n";
    $pdf->SetFont('helvetica', '', 7);
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->Ln();
}

function armarImagenFirma($pdf) {
    $rutaFirmaMecanica = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/formatos/firmacertificados.png';
    if (file_exists($rutaFirmaMecanica)) {
        $x = $pdf->GetX() + 70;
        $y = $pdf->GetY();
        $pdf->SetY($y);
        $pdf->Image($rutaFirmaMecanica, $x, $y, 50, 30, 'png', '', '', true);
    }
}

function armarFinCertificado($pdf) {
    $y = $pdf->GetY() + 30;
    $pdf->SetY($y);
    $pdf->Line(17, $y, 190, $y);
    $txt = '<strong>*** FINAL DEL CERTIFICADO ***</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $y = $y + 4;
    $pdf->Line(17, $y, 190, $y);
}

function retornarTxtTipoIde($tipoIde) {
    switch ($tipoIde) {
        case "1":
            $txtTipoIde = 'CC';
            break;
        case "2":
            $txtTipoIde = 'NIT';
            break;
        case "3":
            $txtTipoIde = 'CE';
            break;
        case "4":
            $txtTipoIde = 'TI';
            break;
        case "5":
            $txtTipoIde = 'PA';
            break;
        case "E":
            $txtTipoIde = 'DE';
            break;
        case "N":
            $txtTipoIde = 'NUIP';
            break;
        case "R":
            $txtTipoIde = 'RC';
            break;
        default:
            $txtTipoIde = '';
            break;
    }
    return $txtTipoIde;
}

function truncarDecimales($valor, $decimalesVisibles = '2') {
    return number_format(\funcionesGenerales::truncateFloat($valor, $decimalesVisibles), $decimalesVisibles, ',', '.');
}

function diferenciaEntreFechaBase30Certificados($fechafinal, $fechainicial) {
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
                            if (($ano != 2000) && ($ano != 2004) && ($ano != 2008) && ($ano != 2012) && ($ano != 2014) && ($ano != 2018) && ($ano != 2022) && ($ano != 2026) && ($ano != 2030) && ($ano != 2034)
                            ) {
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

function convertirMayusculas($org, $cat) {
    $retornar = false;

    // Certificado de matrícula
    if ($org == '01' || $org == '02' || ($org > '02' && $cat == '3')) {
        if (!defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT') || FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT == '' || FECHA_INICIO_NUEVO_CERTIFICADO_CERMAT > date("Ymd")) {
            $retornar = true;
        }
    }

    // Certificado de existencia
    if ($org > '02' && $org != '12' && $org != '14' && ($cat == '1' || $cat == '2')) {
        if (!defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') || FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI == '' || FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI > date("Ymd")) {
            $retornar = true;
        }
    }

    // Certificado de existencia
    if ($org != '12' && $org != '14' && ($cat == '2')) {
        if (!defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') || FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI == '' || FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI > date("Ymd")) {
            $retornar = true;
        }
    }

    // Certificado de ESADL
    if (($org == '12' || $org == '14') && $cat == '1') {
        if (!defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL') || FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL == '' || FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL > date("Ymd")) {
            $retornar = true;
        }
    }

    return $retornar;
}

function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

function parsear_a_mayusculas ($txt) {
    $mays = "ABCDEFGHIJKLMNOPQRSTUVWXYZ12345ÁÉÍÓÚÑ.,;- ";
    $tilds = 'áéíóúñ';
    $s = '';
    $t = str_split_unicode($txt);
    foreach ($t as $t1) {
        $post = strpos($mays, $t1);
        if ($post === false) {
            $post = strpos($tilds, $t1);
            if ($post === false) {
                $s .= strtoupper($t1);
            } else {
                switch ($t1) {
                    case "á" : $s .= 'Á'; break;
                    case "é" : $s .= 'É'; break;
                    case "í" : $s .= 'Í'; break;
                    case "ó" : $s .= 'Ó'; break;
                    case "ú" : $s .= 'Ú'; break;
                    case "ñ" : $s .= 'Ñ'; break;
                }
            }
        } else {
            $s .= $t1;
        }
    }
    return $s;
}

