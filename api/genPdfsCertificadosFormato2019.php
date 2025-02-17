<?php

/*
 * Certifica de matrícula mercantil
 */

/**
 * @param type $valorCe
 *
 * @param type $data
 * @param type $tiportificado
 * @param type $operacion
 * @param type $recibo
 * @param type $aleatorio
 * @param type $certificadoConsultaRues
 * @param type $escajeroin
 * @param type $esbanco
 * @param type $firmar
 * @return string
 */
function generarCertificadosPdfMatriculaFormato2019($mysqli, $data, $tipo, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ob_clean();
    ob_start();
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    $nameLog = 'generarCertificadosPdfMatriculaFormato2019_' . date("Ymd");

    //
    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }

    //
    $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["fcortemesdia"] = substr($_SESSION["generales"]["fcorte"], 4, 4);
    $_SESSION["generales"]["ultanoren"] = $data["ultanoren"];

    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'Inicia armado del certificado');

    $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';

    // Arma lista de certificas y su clasificacion
    $_SESSION["generales"]["clasecerti"] = array();
    $temcerts = retornarRegistrosMysqliApi($mysqli, 'mreg_codigos_certificas', "1=1", "id");
    foreach ($temcerts as $c) {
        $_SESSION["generales"]["clasecerti"][$c["id"]] = $c;
    }
    unset($temcerts);

    if (!class_exists('PDFRequeMat')) {

        class PDFRequeMat extends TCPDF {

            public $angle = 0;
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
            public $tituloRecibo = '';
            public $tituloOperacion = '';
            public $tituloEstadoDatos = '';
            public $norenovado = '';
            public $certificadoConsultaRues = 'no';
            public $codcertificaanterior = '';
            public $codigoverificacion = '';
            public $pagina = 0;
            public $matricula = '';
            public $tituloRazonSocial = '';
            public $organizacion = '';
            public $categoria = '';
            public $valor = 0;
            public $tipohttp = '';
            public $httphost = '';
            public $tituloNombramientos = '';
            public $tipocertificado = '';
            public $disuelta = '';
            public $vigilanciasuperfinanciera = '';
            public $imprimiotituloprocesosespeciales = '';

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

                $txt = 'CERTIFICADO DE MATRÍCULA MERCANTIL';
                if ($this->organizacion == '01') {
                    $txt .= ' DE PERSONA NATURAL';
                }
                if ($this->organizacion == '02') {
                    $txt .= ' DE ESTABLECIMIENTO DE COMERCIO';
                }
                if ($this->categoria == '2') {
                    $txt .= ' DE SUCURSAL NACIONAL';
                }
                if ($this->categoria == '3') {
                    $txt .= ' DE AGENCIA';
                }
                if ($this->organizacion == '08') {
                    $txt .= ' DE SUCURSAL DE SOCIEDAD EXTRANJERA';
                }

                $this->writeHTML('<strong>' . $txt . '</strong>', true, false, true, false, 'C');
                $this->Ln();

                $this->writeHTML('<strong>Fecha expedición: </strong>' . date("d/m/Y") . ' - ' . date("H:i:s") . '</strong>', true, false, true, false, 'C');
                $txt = '';
                if ($this->tituloRecibo != '') {
                    $txt .= 'Recibo No. ' . $this->tituloRecibo;
                }
                if ($this->tituloTipo == 'GasAdm' || $this->tituloTipo == 'GasAfi' || $this->tituloTipo == 'GasOfi' || $this->tituloTipo == 'Consulta') {
                    if ($txt != '') {
                        $txt .= ', ';
                    }
                    $txt .= 'Valor 0';
                } else {
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
                    if ($this->codigoverificacion != '') {
                        $this->writeHTML('<strong>CÓDIGO DE VERIFICACIÓN ' . $this->codigoverificacion . '</strong>', true, false, true, false, 'C');
                        $this->Ln();

                        $txt = 'Verifique el contenido y confiabilidad de este certificado, ingresando a https://sii.confecamaras.co/vista/plantilla/cv.php?empresa=' . CODIGO_EMPRESA . ' y digite el respectivo código, ';
                        $txt .= 'para que visualice la imagen generada al momento de su expedición. La verificación se puede realizar de manera ilimitada, ';
                        $txt .= 'durante 60 días calendario contados a partir de la fecha de su expedición.';

                        $this->writeHTML($txt, true, false, true, false, 'C');
                        $this->Ln();
                    }

                    if (
                            $this->tituloEstadoMatricula != 'MF' &&
                            $this->tituloEstadoMatricula != 'MC' &&
                            $this->tituloEstadoMatricula != 'IC' &&
                            $this->tituloEstadoMatricula != 'IF'
                    ) {
                        if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                            if ($_SESSION["generales"]["ultanoren"] < date("Y")) {
                                $txt = 'La matrícula mercantil proporciona seguridad y confianza en los negocios,';
                                $txt .= 'renueve su matrícula a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . '.';
                                $this->SetFontSize(8);
                                $this->writeHTML($txt, true, false, true, false, 'C');
                                $this->writeHTML('-----------------------------------------------------------------------------------------------------------', true, false, true, false, 'C');
                                $this->Ln();
                            }
                        }
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
                        $this->RotatedText(50, 180, 'CON LA OBLIGACIÓN LEGAL DE', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        if (substr($this->matricula, 0, 1) != 'N' && substr($this->matricula, 0, 1) != 'S') {
                            if ($this->organizacion == '10' && $this->categoria == '1') {
                                $this->RotatedText(70, 180, 'RENOVAR SU INSCRIPCIÓN', 45);
                            } else {
                                $this->RotatedText(50, 200, 'RENOVAR SU MATRÍCULA MERCANTIL', 45);
                            }
                        } else {
                            $this->RotatedText(70, 180, 'RENOVAR SU INSCRIPCIÓN', 45);
                        }
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
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    // ****************************************************************************** //
    // Instanciamiento
    // ****************************************************************************** //
    $pdf = new PDFRequeMat(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }

    $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = 'SI';
    if (defined('TITULOS_EN_CERTIFICADOS_SII') && TITULOS_EN_CERTIFICADOS_SII != '') {
        $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = TITULOS_EN_CERTIFICADOS_SII;
    }

    if (isset($data["vigilanciasuperfinanciera"])) {
        $data["vigilanciasuperfinanciera"] = '';
    }
    $pdf->tipocertificado = 'CerMat';
    $pdf->matricula = $data["matricula"];
    $pdf->organizacion = $data["organizacion"];
    $pdf->categoria = $data["categoria"];
    if ($data["nombrebase64decodificado"] != '') {
        $pdf->tituloRazonSocial = $data["nombrebase64decodificado"];
    } else {
        $pdf->tituloRazonSocial = $data["nombre"];
    }
    $pdf->tituloEstadoMatricula = $data["estadomatricula"];
    $pdf->vigilanciasuperfinanciera = $data["vigilanciasuperfinanciera"];
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    $pdf->tituloRecibo = $recibo;
    $pdf->tituloOperacion = $operacion;
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;
    $pdf->norenovado = 'no';
    if ($tipo == 'Revision') {
        $aleatorio = rand(1000000, 10000000);
    }
    $pdf->codigoverificacion = $aleatorio;
    $pdf->pagina = 1;
    $pdf->valor = $valorCertificado;
    $pdf->tipohttp = TIPO_HTTP;
    $pdf->httphost = HTTP_HOST;

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
                        if (substr($data["fechavencimiento"], 4, 4) <= $_SESSION["generales"]["fcortemesdia"]) {
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
                if ($data["organizacion"] == '01') {
                    if ($data["perdidacalidadcomerciante"] == 'si') {
                        $ano1 = $data["ultanoren"] + 1;
                        $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $ano1 . "'", "corte");
                        if ($data["fechaperdidacalidadcomerciante"] <= $fcorte1) {
                            $pdf->norenovado = 'no';
                        }
                    } else {
                        if ($data["fechareactivacioncalidadcomerciante"] >= date("Y") . '0101') {
                            $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
                            if ($data["fechareactivacioncalidadcomerciante"] <= $fcorte1) {
                                $pdf->norenovado = 'no';
                            }
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
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->SetFont('courier', '', 8);

    //
    $pdf->AddPage();

    //
    armarTextoEleccionesFormato2019($mysqli, $pdf);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoEleccionesFormato2019');

    //
    armarTextoCodigosBarrasFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoCodigosBarrasFormato2019');

    //
    armarCertificaEnDepuracion1727Formato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEnDepuracion1727Formato2019');

    //
    armarTextoFundamentoFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFundamentoFormato2019');

    if ($data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        // Nombre, Datos generales y matrícula
        armarCertificaNombreDatosGeneralesMatriculaFormato2019($mysqli, $pdf, $data, $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaNombreDatosGeneralesMatricula');
        armarCertificaCesacionActividadFormato2019($mysqli, $pdf, $data);
        armarCertificaReactivacionActividadFormato2019($mysqli, $pdf, $data);
    } else {
        // Nombre, identificación y domicilio
        armarCertificaNombreIdentificacionDomicilioFormato2019($mysqli, $pdf, $data, $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaNombreIdentificacionDomicilioFormato2019');

        // Datos de matrícula
        armarCertificaMatriculaFormato2019($mysqli, $pdf, $data, $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaMatriculaFormato2019');

        // armarCertificaCesacionActividadFormato2019($mysqli, $pdf, $data);
        // armarCertificaReactivacionActividadFormato2019($mysqli, $pdf, $data);
    }

    // Certifica renovación
    armarCertificaRenovacionFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRenovacionFormato2019');

    // Pequeña empresa joven
    if ($data["organizacion"] == '01') {
        armarCertificaPequenaEmpresaJovenFormato2019($mysqli, $pdf, $data, $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaPequenaEmpresaJovenFormato2019');
    }

    // Ubicación
    armarCertificaUbicacionFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaUbicacionFormato2019');

    // Reseña a casa principal
    if ($data["organizacion"] == '08' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        armarCertificaCasaPrincipalFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCasaPrincipalFormato2019');
    }

    // Constitución y aperturas   
    armarCertificaConstitucionFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaConstitucionFormato2019');

    // Reformas especiales
    armarCertificaReformasEspecialesFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaReformasEspecialesFormato2019');

    // Ordenes de autoridad competente (Embargos y medidas cautelares)
    armarCertificaEmbargosFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEmbargosFormato2019');

    armarCertificaResolucionesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaResolucionesFormato2019');

    // Certificas especiales
    // Reorganización empresarial
    armarCertificasLibroRm03Rm18Rm19Formato2019($mysqli, $pdf, $data);
    // armarCertificasLibroXVIIIFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificasLibroXVIIIFormato2019');

    // Acuerdos de reestructuración y liquidación judicial
    // armarCertificaReestructuracionFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaReestructuracionFormato2019');

    // Vigilancia y seguridad
    // armarCertificasSeguridadVigilancia2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-OBJSOC');
    // Objeto social
    // armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-OBJSOC', 'OBJETO SOCIAL', 'si', $nameLog);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-OBJSOC');
    // Prohibiciones
    armarCertificaProhibicionesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaProhibicionesFormato2019');

    armarCertificaInhabilidadesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaInhabilidadesFormato2019');

    // Autorizaciones
    // armarCertificaAutorizacionesFormato2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaAutorizacionesFormato2019');
    // Providencias
    // armarCertificaProvidenciasFormato2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaProvidenciasFormato2019');
    // Termino de duración
    armarCertificaTerminoDuracionFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTerminoDuracionFormato2019');

    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-DISOLUCION', 'ACLARACIÓN A LA DISOLUCIÓN', 'si', $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-DISOLUCION');

    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-LIQUIDACION', 'ACLARACIÓN A LA LIQUIDACIÓN', 'si', $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-LIQUIDACION');

    // Objeto social
    if ($data["organizacion"] == '08') {
        armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-OBJSOC', 'OBJETO SOCIAL', 'si', $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-OBJSOC');
    }

    // Capital
    if ($data["organizacion"] == '08') {
        armarCertificaCapitalFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCapitalFormato2019');
    }

    // **********************************************************************************
    if ($data["organizacion"] == '08' || $data["categoria"] == '2') {
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-REPLEG', 'REPRESENTACIÓN LEGAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRepresentacionLegalFormato2019 CRT-REPLEG');
    }

    // Facultades
    if ($data["organizacion"] == '08' || $data["categoria"] == '2') {
        // armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-FACULTADES', 'FACULTADES Y LIMITACIONES DEL REPRESENTANTE LEGAL');
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-FACULTADES', 'FACULTADES Y LIMITACIONES');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-FACULTADES');
    }

    // limitación facultades
    if ($data["organizacion"] == '08' || $data["categoria"] == '2') {
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-LIM-REP-LEG', 'LIMITACIONES A LA REPRESENTACIÓN LEGAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-FACULTADES');
    }

    // Aclaraciones a la representación legal
    if ($data["organizacion"] == '08' || $data["categoria"] == '2') {
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-REPLEG', 'ACLARACION REPRESENTACION LEGAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRepresentacionLegalFormato2019 AC-REPLEG');
    }

    $pdf->tituloNombramientos = 'NO';

    // Representantes legales principales
    // if ($data["organizacion"] == '08' || $data["categoria"] == '2' || $data["categoria"] == '3') {
    armarVinculosRepresentantesLegalesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosClase RL');
    // }
    // Aclaraciones a los representantes legales
    // if ($data["organizacion"] == '08' || $data["categoria"] == '2') {
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-RL', 'ACLARACIONES A LA REPRESENTACION LEGAL');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-RL');
    // }
    // facultades y limitaciones del administrador
    // solo para agencias
    if ($data["categoria"] == '3') {
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-FUN-ADM', 'FACULTADES Y LIMITACIONES DEL ADMINISTRADOR');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-FUN-ADM');
    }

    //  Junta directiva 
    if ($data["organizacion"] == '08') {
        armarVinculosJuntaDirectivaFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosJuntaDirectivaFormato2019');
    }

    // Aclaraciones a la junta directiva
    if ($data["organizacion"] == '08') {
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-JD', 'ACLARACIONES A LA JUNTA DIRECTIVA');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-JD');
    }

    //  Revisores fiscales 
    if ($data["organizacion"] == '08') {
        armarVinculosRevisoresFiscalesFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosClase RF');
    }

    // Aclaraciones a la revisoría fiscal
    if ($data["organizacion"] == '08') {
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-RF', 'ACLARACIONES A LA REVISORIA FISCAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-RF');
    }

    //  Depositarios
    armarVinculosDepositariosFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosClase DPP');

    // *********************************************************************************
    //  Poderes - apoderados
    if ($data["organizacion"] == '01' || $data["organizacion"] == '02' || $data["organizacion"] == '08' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        armarVinculosApoderadosFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosApoderadosFormato2019');
    }

    //  Poderes
    if ($data["organizacion"] == '01' || $data["organizacion"] == '02' || $data["organizacion"] == '08' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        // armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
        // armarCertificaTextoLibrePoderesFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
        armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseMultiCellFormato2019 CRT-PODER');
    }

    // Situaciones de control
    if ($data["organizacion"] == '01' || $data["organizacion"] == '08' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        armarCertificaSitControlFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaSitControlFormato2019');
    }

    // Habilitaciones especiales
    // armarCertificaTransporteFormato2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTransporteFormato2019');

    armarCertificaSuperVigilanciaFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaSuperVigilanciaFormato2019');

    // Recursos de reposición
    if ($data["categoria"] == '3') {
        armarCertificaRecursosReposicionFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRecursosReposicionFormato2019');
    }

    // Actividad económica
    armarCertificaActividadFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaActividadFormato2019');

    // Constitución y reformas de la casa principal
    if ($data["organizacion"] == '08' || $data["categoria"] == '2') {
        armarCertificaConstitucionReformasCasaPrincipalFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaConstitucionReformasCasaPrincipalFormato2019');
    }

    // Reseña a casa principal
    if ($data["organizacion"] == '08' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        armarCertificaListaReformasCasaPrincipalFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaListaReformasCasaPrincipalFormato2019');
    }

    // Información financiera
    if ($data["organizacion"] == '01') {
        armarCertificaInformacionFinancieraFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaInformacionFinancieraFormato2019');
    }

    // Propietarios
    if ($data["organizacion"] == '02') {
        armarCertificaPropietariosFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEstablecimientosFormato2019');
    }

    // Arrendatarios
    if ($data["organizacion"] == '02') {
        armarCertificaArrendatarioFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaArrendatarioFormato2019');
    }


    // Recursos de reposición
    if ($data["categoria"] != '3') {
        armarCertificaRecursosReposicionFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRecursosReposicionFormato2019');
    }


    // Establecimientos, sucursales y agencias
    if ($data["organizacion"] == '01' || $data["categoria"] == '1') {
        armarCertificaEstablecimientosFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEstablecimientosFormato2019');
    }

    // Contratos
    armarCertificaContratosFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaContratosFormato2019');

    // Prendas en texto
    armarCertificaPrendasFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaPrendasFormato2019');

    // Habilitaciones especiales
    armarCertificaTransporteFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTransporteFormato2019');

    // **************************************************************************************** //
    // Información complementaria
    // **************************************************************************************** //
    // Tamaño empresarial
    if ($data["organizacion"] == '01' || $data["categoria"] == '1') {
        armarCertificaTamanoEmpresarial957Formato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTamanoEmpresarial957Formato2019');
    }

    //  Textos varios
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-VARIOS', 'CERTIFICAS ESPECIALES');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-VARIOS');

    // Información VUE / CAE / reporte a Alcaldías
    armarCertificaCaeFomato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCaeFomato2019');

    // Información complementaria
    armarCertificaInformacionComplementariaFomato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaInformacionComplementariaFomato2019');

    // Reflejo situación legal
    armarTextoReflejoSituacionLegalFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoReflejoSituacionLegalFormato2019');

    // **************************************************************************************** //
    // Final del certificado
    // **************************************************************************************** // 

    armarTextoFirmaFormato2019($pdf, $aleatorio, CERTIFICADOS_FIRMA_DIGITAL);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaFormato2019');

    if ($pdf->tituloTipo != 'Consulta' && $pdf->tituloTipo != 'Revision' && $pdf->tituloTipo != 'Api-Consulta') {
        armarTextoFirmaQueEsFormato2019($pdf, $aleatorio);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaQueEsFormato2019');

        armarTextoFirmaMecanicaFormato2019($pdf);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaMecanicaFormato2019');

        armarImagenFirmaFormato2019($pdf, $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarImagenFirmaFormato2019');

        armarTextoFirmaVerificacionFormato2019($pdf, $aleatorio);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaVerificacionFormato2019');
    }

    armarFinCertificadoFormato2019($pdf);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarFinCertificadoFormato2019');

    //
    if ($pdf->tituloTipo == 'Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name1;
    }

    //
    if ($pdf->tituloTipo == 'ConsultaD') {
        $namex = $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $namex;
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $namex;
    }

    //
    if ($pdf->tituloTipo == 'Api-Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name2;
    }

    //
    if ($pdf->tituloTipo == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name1;
    }

    //
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
    $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    ob_end_clean();
    $pdf->Output($name1, "F");

    /*
     * WSIERRA 2017/09/14 - Incluir firma digital en certificados diferentes de consulta RUES
     */
    // if ($certificadoConsultaRues != 'si') {
    $msg = 'Recibo : ' . $recibo . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . '(' . $escajero . ') (' . $esbanco . ') | Matricula : ' . $data["matricula"] . ' | Tipo : CerExi | Hora : ' . date("His");
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
    // }
    //
    // ob_end_clean();
    return $name2;
}

/*
 * Certifica de existencia
 *
 */

/**
 *
 * @param string $data
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
function generarCertificadosPdfExistenciaFormato2019($mysqli, $data, $tipo, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ob_clean();
    ob_start();
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');

    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    $nameLog = 'generarCertificadosPdfExistenciaFormato2019_' . date("Ymd");

    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }

    //
    $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["fcortemesdia"] = substr($_SESSION["generales"]["fcorte"], 4, 4);
    $_SESSION["generales"]["ultanoren"] = $data["ultanoren"];

    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'Inicia armado del certificado');
    $txt = json_encode($data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], $txt);

    $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';

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

    if (!class_exists('PDFRequeExi')) {

        class PDFRequeExi extends TCPDF {

            public $angle = 0;
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
            public $tituloRecibo = '';
            public $tituloOperacion = '';
            public $tituloEstadoDatos = '';
            public $norenovado = '';
            public $certificadoConsultaRues = 'no';
            public $codcertificaanterior = '';
            public $codigoverificacion = '';
            public $pagina = 0;
            public $matricula = '';
            public $tituloRazonSocial = '';
            public $organizacion = '';
            public $categoria = '';
            public $valor = 0;
            public $tipohttp = '';
            public $httphost = '';
            public $tituloNombramientos = '';
            public $tipocertificado = '';
            public $disuelta = '';
            public $imprimiotituloprocesosespeciales = '';
            public $vigilanciasuperfinanciera = '';

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
                $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 40, PDF_MARGIN_RIGHT);
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

                if ($this->vigilanciasuperfinanciera == 'S') {
                    $txt = 'CERTIFICADO DE INSCRIPCIÓN DE DOCUMENTOS';
                } else {
                    $txt = 'CERTIFICADO DE EXISTENCIA Y REPRESENTACIÓN LEGAL';
                }
                $this->writeHTML('<strong>' . $txt . '</strong>', true, false, true, false, 'C');
                $this->Ln();

                $this->writeHTML('<strong>Fecha expedición: </strong>' . date("d/m/Y") . ' - ' . date("H:i:s") . '</strong>', true, false, true, false, 'C');
                $txt = '';
                if ($this->tituloRecibo != '') {
                    $txt .= 'Recibo No. ' . $this->tituloRecibo;
                }
                if ($this->tituloTipo == 'GasAdm' || $this->tituloTipo == 'GasAfi' || $this->tituloTipo == 'GasOfi' || $this->tituloTipo == 'Consulta') {
                    if ($txt != '') {
                        $txt .= ', ';
                    }
                    $txt .= 'Valor 0';
                } else {
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
                    if ($this->codigoverificacion != '') {
                        $this->writeHTML('<strong>CÓDIGO DE VERIFICACIÓN ' . $this->codigoverificacion . '</strong>', true, false, true, false, 'C');
                        $this->Ln();

                        $txt = 'Verifique el contenido y confiabilidad de este certificado, ingresando a https://sii.confecamaras.co/vista/plantilla/cv.php?empresa=' . CODIGO_EMPRESA . ' y digite el respectivo código, ';
                        $txt .= 'para que visualice la imagen generada al momento de su expedición. La verificación se puede realizar de manera ilimitada, ';
                        $txt .= 'durante 60 días calendario contados a partir de la fecha de su expedición.';

                        $this->writeHTML($txt, true, false, true, false, 'C');
                        $this->Ln();
                    }
                    if (
                            $this->tituloEstadoMatricula != 'MF' &&
                            $this->tituloEstadoMatricula != 'MC' &&
                            $this->tituloEstadoMatricula != 'IC' &&
                            $this->tituloEstadoMatricula != 'IF'
                    ) {
                        if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                            if ($_SESSION["generales"]["ultanoren"] < date("Y")) {
                                if (($this->organizacion == '12' || $this->organizacion == '14') && $this->categoria == '1') {
                                    $txt = 'La inscripción al Registro de las Entidades sin Ánimo de Lucro o al Registro de la Economía Solidaria proporciona seguridad y confianza en los negocios,';
                                    // $txt .= 'renueve su inscripción a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . ' y evite sanciones de hasta  17 S.M.L.M.V';
                                    $txt .= 'renueve su inscripción a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . '.';
                                } else {
                                    $txt = 'La matrícula mercantil proporciona seguridad y confianza en los negocios,';
                                    // $txt .= 'renueve su matrícula a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . ' y evite sanciones de hasta  17 S.M.L.M.V';
                                    $txt .= 'renueve su matrícula a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . '.';
                                }
                                $this->SetFontSize(8);
                                $this->writeHTML($txt, true, false, true, false, 'C');
                                $this->writeHTML('-----------------------------------------------------------------------------------------------------------', true, false, true, false, 'C');
                                $this->Ln();
                            }
                        }
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
                        $this->RotatedText(50, 180, 'CON LA OBLIGACIÓN LEGAL DE', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        if (substr($this->matricula, 0, 1) != 'N' && substr($this->matricula, 0, 1) != 'S') {
                            if ($this->organizacion == '10' && $this->categoria == '1') {
                                $this->RotatedText(70, 180, 'RENOVAR SU INSCRIPCIÓN', 45);
                            } else {
                                $this->RotatedText(50, 200, 'RENOVAR SU MATRÍCULA MERCANTIL', 45);
                            }
                        } else {
                            $this->RotatedText(70, 180, 'RENOVAR SU INSCRIPCIÓN', 45);
                        }
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
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    // ****************************************************************************** //
    // Instanciamiento
    // ****************************************************************************** //
    $pdf = new PDFRequeExi(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }

    $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = 'SI';
    if (defined('TITULOS_EN_CERTIFICADOS_SII') && TITULOS_EN_CERTIFICADOS_SII != '') {
        $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = TITULOS_EN_CERTIFICADOS_SII;
    }

    $pdf->tipocertificado = 'CerExi';
    $pdf->matricula = $data["matricula"];
    $pdf->organizacion = $data["organizacion"];
    $pdf->categoria = $data["categoria"];
    if ($data["nombrebase64decodificado"] != '') {
        $pdf->tituloRazonSocial = $data["nombrebase64decodificado"];
    } else {
        $pdf->tituloRazonSocial = $data["nombre"];
    }
    $pdf->tituloEstadoMatricula = $data["estadomatricula"];
    $pdf->vigilanciasuperfinanciera = $data["vigilanciasuperfinanciera"];
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    $pdf->tituloRecibo = $recibo;
    $pdf->tituloOperacion = $operacion;
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;
    $pdf->norenovado = 'no';
    if ($tipo == 'Revision') {
        $aleatorio = rand(1000000, 10000000);
    }
    $pdf->codigoverificacion = $aleatorio;
    $pdf->pagina = 1;
    $pdf->valor = $valorCertificado;
    $pdf->tipohttp = TIPO_HTTP;
    $pdf->httphost = HTTP_HOST;
    $pdf->disuelta = 'no';

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
                        $pdf->disuelta = 'si';
                    } else {
                        if ($data["disueltaporacto510"] == 'si') {
                            if ($data["fechaacto510"] <= $_SESSION["generales"]["fcorte"]) {
                                $pdf->norenovado = 'no';
                                $pdf->disuelta = 'si';
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
                    $pdf->disuelta = 'si';
                    if (($data["fechaacto510"] != '') && $data["fechaacto510"] < $data["fechavencimiento"]) {
                        $data["disueltaporvencimiento"] = 'no';
                    } else {
                        if (substr($data["fechavencimiento"], 4, 4) <= $_SESSION["generales"]["fcortemesdia"]) {
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
                    $pdf->disuelta = 'si';
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
                } else {
                    if ($data["reactivadaacto511"] == 'si') {
                        if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                            if ($data["fechaacto511"] >= (date("Y") - 1) . '0101' && $data["fechaacto511"] <= $_SESSION["generales"]["fcorte"]) {
                                $pdf->norenovado = 'no';
                            }
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
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->SetFont('courier', '', 8);

    //
    $pdf->AddPage();

    //
    armarTextoEleccionesFormato2019($mysqli, $pdf);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoEleccionesFormato2019');

    //
    armarTextoCodigosBarrasFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoCodigosBarrasFormato2019');

    //
    armarCertificaEnDepuracion1727Formato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEnDepuracion1727Formato2019');

    //
    armarTextoFundamentoFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFundamentoFormato2019');

    // Nombre, identificación y domicilio
    armarCertificaNombreIdentificacionDomicilioFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaNombreIdentificacionDomicilioFormato2019');

    // Datos de matrícula
    armarCertificaMatriculaFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaMatriculaFormato2019');

    // Certifica renovación
    armarCertificaRenovacionFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRenovacionFormato2019');

    // Pequeña empresa joven
    armarCertificaPequenaEmpresaJovenFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaPequenaEmpresaJovenFormato2019');

    // Ubicación
    armarCertificaUbicacionFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaUbicacionFormato2019');

    // Constitución
    armarCertificaConstitucionFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaConstitucionFormato2019');

    // Aclaratoria a la constitución
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-CONSTI', 'ACLARATORIA A LA CONSTITUCIÓN', 'si', $nameLog); // Aclaratoria constitución
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019');

    // Entidad de vigilancia
    armarCertificaEntidadVigilanciaFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEntidadVigilanciaFormato2019');

    // Reformas especiales
    armarCertificaReformasEspecialesFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaReformasEspecialesFormato2019');

    // Certifica de cancelacion
    armarCertificaCancelacionFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCancelacionFormato2019');

    // Ordenes de autoridad competente (Embargos y medidas cautelares)
    armarCertificaEmbargosFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEmbargosFormato2019');

    armarCertificaResolucionesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaResolucionesFormato2019');

    // Reorganización empresarial
    // Certificas especiales
    armarCertificasLibroRm03Rm18Rm19Formato2019($mysqli, $pdf, $data);
    // armarCertificasLibroXVIIIFormato2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificasLibroXVIIIFormato2019');
    // Acuerdos de reestructuración y liquidación judicial
    // armarCertificaReestructuracionFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaReestructuracionFormato2019');

    // Termino de duración
    armarCertificaTerminoDuracionFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTerminoDuracionFormato2019');

    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-DISOLUCION', 'ACLARACIÓN A LA DISOLUCIÓN', 'si', $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-DISOLUCION');

    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-LIQUIDACION', 'ACLARACIÓN A LA LIQUIDACIÓN', 'si', $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-LIQUIDACION');

    // Disolución / reactivación
    // armarCertificaReactivacionFormato2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaReactivacionFormato2019');
    // Vigilancia y seguridad
    armarCertificasSeguridadVigilancia2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-OBJSOC');

    armarCertificaSuperVigilanciaFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaSuperVigilanciaFormato2019');

    // Habilitaciones especiales
    armarCertificaTransporteFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTransporteFormato2019');

    // Objeto social
    // armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-OBJSOC', 'OBJETO SOCIAL', 'si', $nameLog);
    armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-OBJSOC', 'OBJETO SOCIAL', 'si', $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-OBJSOC');

    // Limitaciones, prohibiciones y autorizaciones en texto
    armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-LIM-PRO-AUT', 'LIMITACIONES, PROHIBICIONES Y AUTORIZACIONES DE LA CAPACIDAD DE LA PERSONA JURÍDICA', 'si', $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-LIM-PRO-AUT');

    // Limitaciones
    armarCertificaLimitacionesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaLimitacionesFormato2019');

    // Prohibiciones
    armarCertificaProhibicionesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaProhibicionesFormato2019');

    // Autorizaciones
    armarCertificaAutorizacionesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaAutorizacionesFormato2019');

    // Providencias
    // armarCertificaProvidenciasFormato2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaProvidenciasFormato2019');
    // Capital
    armarCertificaCapitalFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCapitalFormato2019');

    // Representación legal y sus aclaratorias
    if (CODIGO_EMPRESA == '20') {
        if (isset($data["crtsii"]["1120"]) && trim($data["crtsii"]["1120"]) != '') {
            $data["crtsii"]["1120"] = '';
        }
        if (isset($data["crt"]["1120"]) && trim($data["crt"]["1120"]) != '') {
            $data["crt"]["1120"] = '';
        }
    }
    armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-REPLEG', 'REPRESENTACIÓN LEGAL');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRepresentacionLegalFormato2019 CRT-REPLEG');

    // Facultades
    armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-FACULTADES', 'FACULTADES Y LIMITACIONES DEL REPRESENTANTE LEGAL');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-FACULTADES');

    // limitación facultades
    armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-LIM-REP-LEG', 'LIMITACIONES A LA REPRESENTACIÓN LEGAL');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-LIM-REP-LEG');

    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-REPLEG', 'ACLARACION REPRESENTACION LEGAL');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRepresentacionLegalFormato2019 AC-REPLEG');

    $pdf->tituloNombramientos = 'NO';

    // Representantes legales principales
    armarVinculosRepresentantesLegalesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosClase RL');

    //
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-RL', 'ACLARACIONES A LA REPRESENTACION LEGAL');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-RL');

    //  Junta directiva 
    armarVinculosJuntaDirectivaFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosJuntaDirectivaFormato2019');

    //
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-JD', 'ACLARACIONES A LA JUNTA DIRECTIVA');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-JD');

    //  Revisores fiscales 
    armarVinculosRevisoresFiscalesFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosClase RF');

    //
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-RF', 'ACLARACIONES A LA REVISORIA FISCAL');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-RF');

    //  Depositarios
    armarVinculosDepositariosFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosClase DPP');

    //  Poderes
    armarVinculosApoderadosFormato2019($mysqli, $pdf, $data);
    // armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
    // armarCertificaTextoLibrePoderesFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
    armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseMultiCellFormato2019 CRT-PODER');

    //  Profesionales del derecho
    armarVinculosProfesionalesDelDerechoFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosProfesionalesDelDerechoFormato2019');

    // Lista de reformas estatutarias
    armarCertificaListaReformasFormato019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarListaReformasEstaturaiasFormato2019');

    // Recursos de reposición
    armarCertificaRecursosReposicionFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRecursosReposicionFormato2019');

    // Situaciones de control
    armarCertificaSitControlFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaSitControlFormato2019');

    // Actividad económica
    armarCertificaActividadFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaActividadFormato2019');

    // Establecimientos, sucursales y agncias
    armarCertificaEstablecimientosFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEstablecimientosFormato2019');

    // Reseña a casa principal
    armarCertificaCasaPrincipalFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCasaPrincipalFormato2019');

    // Habilitaciones especiales
    // armarCertificaTransporteFormato2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTransporteFormato2019');
    // **************************************************************************************** //
    // Información complementaria
    // **************************************************************************************** //
    // Tamaño empresarial
    armarCertificaTamanoEmpresarial957Formato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTamanoEmpresarial957Formato2019');

    // Contratos
    armarCertificaContratosFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaContratosFormato2019');

    // Prendas en texto
    armarCertificaPrendasFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaPrendasFormato2019');

    //  Textos varios
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-VARIOS', 'CERTIFICAS ESPECIALES');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-VARIOS');

    // Información VUE / CAE / reporte a Alcaldías
    armarCertificaCaeFomato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCaeFomato2019');

    // Información complementaria
    armarCertificaInformacionComplementariaFomato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaInformacionComplementariaFomato2019');

    // Reflejo situación legal
    armarTextoReflejoSituacionLegalFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoReflejoSituacionLegalFormato2019');

    // **************************************************************************************** //
    // Final del certificado
    // **************************************************************************************** // 

    armarTextoFirmaFormato2019($pdf, $aleatorio, CERTIFICADOS_FIRMA_DIGITAL);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaFormato2019');

    if ($pdf->tituloTipo != 'Consulta' && $pdf->tituloTipo != 'Revision' && $pdf->tituloTipo != 'Api-Consulta') {
        armarTextoFirmaQueEsFormato2019($pdf, $aleatorio);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaQueEsFormato2019');

        armarTextoFirmaMecanicaFormato2019($pdf);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaMecanicaFormato2019');

        armarImagenFirmaFormato2019($pdf, $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarImagenFirmaFormato2019');

        armarTextoFirmaVerificacionFormato2019($pdf, $aleatorio);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaVerificacionFormato2019');
    }

    armarFinCertificadoFormato2019($pdf);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarFinCertificadoFormato2019');

    //
    if ($pdf->tituloTipo == 'Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name1;
    }
    if ($pdf->tituloTipo == 'ConsultaD') {
        $namex = $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $namex;
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $namex;
    }

    if ($pdf->tituloTipo == 'Api-Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name2;
    }

    if ($pdf->tituloTipo == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name1;
    }

    //
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
    $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    ob_end_clean();
    $pdf->Output($name1, "F");

    /*
     * WSIERRA 2017/09/14 - Incluir firma digital en certificados diferentes de consulta RUES
     */
    // if ($certificadoConsultaRues != 'si') {
    $msg = 'Recibo : ' . $recibo . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . '(' . $escajero . ') (' . $esbanco . ') | Matricula : ' . $data["matricula"] . ' | Tipo : CerExi | Hora : ' . date("His");
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
    // }
    //
    unset($pdf);
    // ob_end_clean();
    return $name2;
}

/*
 * Certifica de existencia de ESADL, en el siguiente orden:
 *
 */

/**
 *
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
function generarCertificadosPdfEsadlFormato2019($mysqli, $data, $tipo, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ob_clean();
    ob_start();
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');

    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    $nameLog = 'generarCertificadosPdfEsadlFormato2019_' . date("Ymd");

    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }

    //
    $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["fcortemesdia"] = substr($_SESSION["generales"]["fcorte"], 4, 4);
    $_SESSION["generales"]["ultanoren"] = $data["ultanoren"];

    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'Inicia armado del certificado');

    $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] = '20170101';

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

    if (!class_exists('PDFRequeEsadl')) {

        class PDFRequeEsadl extends TCPDF {

            public $angle = 0;
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
            public $tituloRecibo = '';
            public $tituloOperacion = '';
            public $tituloEstadoDatos = '';
            public $norenovado = '';
            public $certificadoConsultaRues = 'no';
            public $codcertificaanterior = '';
            public $codigoverificacion = '';
            public $pagina = 0;
            public $matricula = '';
            public $tituloRazonSocial = '';
            public $organizacion = '';
            public $categoria = '';
            public $valor = 0;
            public $tipohttp = '';
            public $httphost = '';
            public $tituloNombramientos = '';
            public $tipocertificado = '';
            public $claseespesadl = '';
            public $disuelta = '';
            public $imprimiotituloprocesosespeciales = '';

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
                $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 40, PDF_MARGIN_RIGHT);
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

                $txt = 'CERTIFICADO DE EXISTENCIA Y REPRESENTACIÓN LEGAL';
                if ($this->claseespesadl == '61') {
                    $txt = 'CERTIFICADO DE INSCRIPCIÓN DE APODERADO JUDICIAL DE ENTIDADES EXTRANJERAS DE<br>DERECHO PRIVADO SIN ÁNIMO DE LUCRO';
                }
                if ($this->claseespesadl == '62') {
                    $txt = 'CERTIFICADO DE INSCRIPCIÓN DE VEEDURÍAS CIUDADANAS';
                }
                $this->writeHTML('<strong>' . $txt . '</strong>', true, false, true, false, 'C');
                $this->Ln();

                $this->writeHTML('<strong>Fecha expedición: </strong>' . date("d/m/Y") . ' - ' . date("H:i:s") . '</strong>', true, false, true, false, 'C');
                $txt = '';
                if ($this->tituloRecibo != '') {
                    $txt .= 'Recibo No. ' . $this->tituloRecibo;
                }
                if ($this->tituloTipo == 'GasAdm' || $this->tituloTipo == 'GasAfi' || $this->tituloTipo == 'GasOfi' || $this->tituloTipo == 'Consulta') {
                    if ($txt != '') {
                        $txt .= ', ';
                    }
                    $txt .= 'Valor 0';
                } else {
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
                    if ($this->codigoverificacion != '') {
                        $this->writeHTML('<strong>CÓDIGO DE VERIFICACIÓN ' . $this->codigoverificacion . '</strong>', true, false, true, false, 'C');
                        $this->Ln();

                        $txt = 'Verifique el contenido y confiabilidad de este certificado, ingresando a https://sii.confecamaras.co/vista/plantilla/cv.php?empresa=' . CODIGO_EMPRESA . ' y digite el respectivo código, ';
                        $txt .= 'para que visualice la imagen generada al momento de su expedición. La verificación se puede realizar de manera ilimitada, ';
                        $txt .= 'durante 60 días calendario contados a partir de la fecha de su expedición.';

                        $this->writeHTML($txt, true, false, true, false, 'C');
                        $this->Ln();
                    }

                    //
                    // $this->writeHTML('--------------------------------------------------------------------------------------------------------------------------------------------------------------', true, false, true, false, 'C');
                    // $this->Ln();
                    //
                    if (
                            $this->tituloEstadoMatricula != 'MF' &&
                            $this->tituloEstadoMatricula != 'MC' &&
                            $this->tituloEstadoMatricula != 'IC' &&
                            $this->tituloEstadoMatricula != 'IF'
                    ) {
                        if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                            if ($_SESSION["generales"]["ultanoren"] < date("Y")) {
                                $txt = 'La inscripción al Registro de las Entidades sin Ánimo de Lucro o al Registro de la Economía Solidaria proporciona seguridad y confianza en los negocios,';
                                $txt .= 'renueve su inscripción a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . '.';
                                $this->SetFontSize(8);
                                $this->writeHTML($txt, true, false, true, false, 'C');
                                $this->writeHTML('-----------------------------------------------------------------------------------------------------------', true, false, true, false, 'C');
                                $this->Ln();
                            }
                        }
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
                        $this->RotatedText(50, 180, 'CON LA OBLIGACIÓN LEGAL DE', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        $this->RotatedText(70, 180, 'RENOVAR SU INSCRIPCIÓN', 45);
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
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    // ****************************************************************************** //
    // Instanciamiento
    // ****************************************************************************** //
    $pdf = new PDFRequeEsadl(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }

    $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = 'SI';
    if (defined('TITULOS_EN_CERTIFICADOS_SII') && TITULOS_EN_CERTIFICADOS_SII != '') {
        $_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] = TITULOS_EN_CERTIFICADOS_SII;
    }

    $pdf->tipocertificado = "CerEsadl";
    $pdf->matricula = $data["matricula"];
    $pdf->organizacion = $data["organizacion"];
    $pdf->categoria = $data["categoria"];
    if ($data["nombrebase64decodificado"] != '') {
        $pdf->tituloRazonSocial = $data["nombrebase64decodificado"];
    } else {
        $pdf->tituloRazonSocial = $data["nombre"];
    }
    $pdf->tituloEstadoMatricula = $data["estadomatricula"];
    $pdf->vigilanciasuperfinanciera = $data["vigilanciasuperfinanciera"];
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    $pdf->tituloRecibo = $recibo;
    $pdf->tituloOperacion = $operacion;
    $pdf->claseespesadl = $data["claseespesadl"];
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;
    $pdf->norenovado = 'no';
    if ($tipo == 'Revision') {
        $aleatorio = rand(1000000, 10000000);
    }
    $pdf->codigoverificacion = $aleatorio;
    $pdf->pagina = 1;
    $pdf->valor = $valorCertificado;
    $pdf->tipohttp = TIPO_HTTP;
    $pdf->httphost = HTTP_HOST;
    $pdf->disuelta = 'no';

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
                        $pdf->disuelta = 'si';
                    } else {
                        if ($data["disueltaporacto510"] == 'si') {
                            if ($data["fechaacto510"] <= $_SESSION["generales"]["fcorte"]) {
                                $pdf->norenovado = 'no';
                                $pdf->disuelta = 'si';
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
                    $pdf->disuelta = 'si';
                    if (($data["fechaacto510"] != '') && $data["fechaacto510"] < $data["fechavencimiento"]) {
                        $data["disueltaporvencimiento"] = 'no';
                    } else {
                        if (substr($data["fechavencimiento"], 4, 4) <= $_SESSION["generales"]["fcortemesdia"]) {
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
                    $pdf->disuelta = 'si';
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
                } else {
                    if ($data["reactivadaacto511"] == 'si') {
                        if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                            if ($data["fechaacto511"] >= (date("Y") - 1) . '0101' && $data["fechaacto511"] <= $_SESSION["generales"]["fcorte"]) {
                                $pdf->norenovado = 'no';
                            }
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
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->SetFont('courier', '', 8);

    //
    $pdf->AddPage();

    //
    // armarTextoEleccionesFormato2019($mysqli, $pdf);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoEleccionesFormato2019');
    //
    armarTextoCodigosBarrasFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoCodigosBarrasFormato2019');

    //
    armarCertificaEnDepuracion1727Formato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEnDepuracion1727Formato2019');

    //
    armarTextoFundamentoFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFundamentoFormato2019');

    // Nombre, identificación y domicilio
    armarCertificaNombreIdentificacionDomicilioFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaNombreIdentificacionDomicilioFormato2019');

    // Datos de matrícula
    armarCertificaMatriculaFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaMatriculaFormato2019');

    // Certifica renovación
    armarCertificaRenovacionFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRenovacionFormato2019');

    // Ubicación
    armarCertificaUbicacionFormato2019($mysqli, $pdf, $data, $nameLog);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaUbicacionFormato2019');

    // En caso de ONGs extranjeras
    if ($data["claseespesadl"] == '61') {

        // Representantes legales
        armarVinculosRepresentantesLegalesFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosRepresentantesLegalesFormato2019');

        // Facultades
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-FACULTADES', 'FACULTADES Y LIMITACIONES DEL APODERADO JUDICIAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-FACULTADES');

        // limitación facultades
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-LIM-REP-LEG', 'LIMITACIONES DEL APODERADO JUDICIAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-FACULTADES');

        // Aclaracion a la representación legal
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'AC-REPLEG', 'ACLARACION REPRESENTACION LEGAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRepresentacionLegalFormato2019  AC-REPLEG');

        // Lista de reformas estatutarias
        armarCertificaListaReformasFormato019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarListaReformasEstaturaiasFormato2019');
    }

    // Si es diferente a ONGs extranjeras
    if ($data["claseespesadl"] != '61') {

        // Constitución
        armarCertificaConstitucionFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaConstitucionFormato2019');

        // Aclaratoria a la constitución
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-CONSTI', 'ACLARATORIA A LA CONSTITUCIÓN', 'si', $nameLog); // Aclaratoria constitución
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019');

        // Personería jurídica
        armarCertificaPersoneriaJuridicaFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaPersoneriaJuridicaFormato2019');

        // Entidad de vigilancia
        armarCertificaEntidadVigilanciaFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEntidadVigilanciaFormato2019');

        // Reformas especiales
        armarCertificaReformasEspecialesFormato2019($mysqli, $pdf, $data, $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], ' ');

        // Ordenes de autoridad competente (Embargos y medidas cautelares)
        armarCertificaEmbargosFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEmbargosFormato2019');

        armarCertificaResolucionesFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaResolucionesFormato2019');

        // Reorganización empresarial
        // Certificas especiales
        armarCertificasLibroRm03Rm18Rm19Formato2019($mysqli, $pdf, $data);
        // armarCertificasLibroXVIIIFormato2019($mysqli, $pdf, $data);
        // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificasLibroXVIIIFormato2019');
        // Acuerdos de reestructuración y liquidación judicial
        // armarCertificaReestructuracionFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaReestructuracionFormato2019');

        // Nivel territorial
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-NIV-TER', 'NIVEL TERRITORIAL', 'si', $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-NIV-TER');

        // Termino de duración
        armarCertificaTerminoDuracionFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTerminoDuracionFormato2019');

        // Disolución / reactivación
        // armarCertificaReactivacionFormato2019($mysqli, $pdf, $data);
        // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaReactivacionFormato2019');
        // Vigilancia y seguridad
        armarCertificasSeguridadVigilancia2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificasSeguridadVigilancia2019');

        // Habilitaciones especiales
        // armarCertificaTransporteFormato2019($mysqli, $pdf, $data);
        // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTransporteFormato2019');
        armarCertificaSuperVigilanciaFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaSuperVigilanciaFormato2019');

        // Habilitaciones especiales
        armarCertificaTransporteFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTransporteFormato2019');

        // Objeto social
        if ($data["claseespesadl"] == '62') {
            // armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-OBJSOC', 'OBJETO DE LA VIGILANCIA', 'si', $nameLog);
            armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-OBJSOC', 'OBJETO DE LA VIGILANCIA', 'si', $nameLog);

            \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-OBJSOC');
        } else {
            // armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-OBJSOC', 'OBJETO SOCIAL', 'si', $nameLog);
            armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-OBJSOC', 'OBJETO SOCIAL', 'si', $nameLog);
            \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-OBJSOC');
        }

        // Limitaciones, prohibiciones y autorizaciones en texto
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-LIM-PRO-AUT', 'LIMITACIONES, PROHIBICIONES Y AUTORIZACIONES DE LA CAPACIDAD DE LA PERSONERÍA JURÍDICA', 'si', $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-LIM-PRO-AUT');

        // Limitaciones
        armarCertificaLimitacionesFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaLimitacionesFormato2019');

        // Prohibiciones
        armarCertificaProhibicionesFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaProhibicionesFormato2019');

        // Autorizaciones
        armarCertificaAutorizacionesFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaAutorizacionesFormato2019');

        // Providencias
        // armarCertificaProvidenciasFormato2019($mysqli, $pdf, $data);
        // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaProvidenciasFormato2019');
        // Patrimonio en texto
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-PATRIMONIO', 'PATRIMONIO', 'si');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 - patrimonio en texto');

        // Capital
        armarCertificaCapitalFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCapitalFormato2019');

        // Representación legal y sus aclaratorias
        if (CODIGO_EMPRESA == '20') {
            if (isset($data["crtsii"]["1120"]) && trim($data["crtsii"]["1120"]) != '') {
                $data["crtsii"]["1120"] = '';
            }
            if (isset($data["crt"]["1120"]) && trim($data["crt"]["1120"]) != '') {
                $data["crt"]["1120"] = '';
            }
        }

        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-ORGADM', 'REPRESENTACION LEGAL Y ORGANO DE ADMINISTRACIÓN');
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-REPLEG', 'REPRESENTACION LEGAL');

        // Facultades
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-FACULTADES', 'FACULTADES Y LIMITACIONES DEL REPRESENTANTE LEGAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-FACULTADES');

        // limitación facultades
        armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, 'CRT-LIM-REP-LEG', 'LIMITACIONES A LA REPRESENTACIÓN LEGAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-FACULTADES');

        // Aclaracion a la representación legal
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-REPLEG', 'ACLARACION REPRESENTACION LEGAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRepresentacionLegalFormato2019  AC-REPLEG');

        $pdf->tituloNombramientos = 'NO';

        // Representantes legales
        armarVinculosRepresentantesLegalesFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosRepresentantesLegalesFormato2019');

        // INTEGRANTES VEEDURÍA
        if ($data["claseespesadl"] == '62') {
            armarVinculosIntegrantesFormato2019($mysqli, $pdf, $data);
            \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosIntegrantesFormato2019');
        }

        //
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-RL', 'ACLARACIONES A LA REPRESENTACION LEGAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-RL');

        //  Organo de administracion - solo esadl
        armarVinculosOrganoAdministracionFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosOrganoAdministracionFormato2019');

        //
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-JD', 'ACLARACIONES A LA JUNTA DIRECTIVA');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-JD');

        //  Revisores fiscales 
        armarVinculosRevisoresFiscalesFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosClase RF');

        //
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-VIN-RF', 'ACLARACIONES A LA REVISORIA FISCAL');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 AC-VIN-RF');

        //
        armarVinculosApoderadosFormato2019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarVinculosApoderadosFormato2019');

        //  Poderes
        // armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
        // armarCertificaTextoLibrePoderesFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
        armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'PODERES');
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseMultiCellFormato2019 CRT-PODER');

        // Lista de reformas estatutarias
        armarCertificaListaReformasFormato019($mysqli, $pdf, $data);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarListaReformasEstaturaiasFormato2019');
    }


    // Recursos de reposición
    armarCertificaRecursosReposicionFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaRecursosReposicionFormato2019');

    // Situaciones de control
    armarCertificaSitControlFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaSitControlFormato2019');

    // Actividad económica
    armarCertificaActividadFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaActividadFormato2019');

    // Establecimientos, sucursales y agncias
    armarCertificaEstablecimientosFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaEstablecimientosFormato2019');

    // Habilitaciones especiales
    // armarCertificaTransporteFormato2019($mysqli, $pdf, $data);
    // \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTransporteFormato2019');
    // **************************************************************************************** //
    // Información complementaria
    // **************************************************************************************** //
    // Tamaño empresarial
    armarCertificaTamanoEmpresarial957Formato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTamanoEmpresarial957Formato2019');

    //  Textos varios
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-VARIOS', 'CERTIFICAS ESPECIALES');
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaTextoLibreClaseFormato2019 CRT-VARIOS');

    // Información VUE / CAE / reporte a Alcaldías
    armarCertificaCaeFomato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaCaeFomato2019');

    // Información complementaria
    armarCertificaInformacionComplementariaFomato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarCertificaInformacionComplementariaFomato2019');

    // Reflejo situación legal
    armarTextoReflejoSituacionLegalFormato2019($mysqli, $pdf, $data);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoReflejoSituacionLegalFormato2019');

    // **************************************************************************************** //
    // Final del certificado
    // **************************************************************************************** // 
    armarTextoFirmaFormato2019($pdf, $aleatorio, CERTIFICADOS_FIRMA_DIGITAL);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaFormato2019');

    if ($pdf->tituloTipo != 'Consulta' && $pdf->tituloTipo != 'Revision' && $pdf->tituloTipo != 'Api-Consulta') {
        armarTextoFirmaQueEsFormato2019($pdf, $aleatorio);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaQueEsFormato2019');

        armarTextoFirmaMecanicaFormato2019($pdf);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaMecanicaFormato2019');

        armarImagenFirmaFormato2019($pdf, $nameLog);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarImagenFirmaFormato2019');

        armarTextoFirmaVerificacionFormato2019($pdf, $aleatorio);
        \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarTextoFirmaVerificacionFormato2019');
    }

    armarFinCertificadoFormato2019($pdf);
    \logApi::general2($nameLog, $operacion . '-' . $data["matricula"], 'armarFinCertificadoFormato2019');

    //
    if ($pdf->tituloTipo == 'Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name1;
    }
    if ($pdf->tituloTipo == 'ConsultaD') {
        $namex = $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $namex;
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $namex;
    }

    if ($pdf->tituloTipo == 'Api-Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name2;
    }

    if ($pdf->tituloTipo == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name1;
    }
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
    $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    $name2 = 'mreg/certificados/' . $anox . '/' . $mesx . '/' . $aleatorio . '.pdf';
    ob_end_clean();
    $pdf->Output($name1, "F");

    /*
     * WSIERRA 2017/09/14 - Incluir firma digital en certificados diferentes de consulta RUES
     */
    // if ($certificadoConsultaRues != 'si') {
    $msg = 'Recibo : ' . $recibo . ' | Usuario : ' . $_SESSION["generales"]["codigousuario"] . '(' . $escajero . ') (' . $esbanco . ') | Matricula : ' . $data["matricula"] . ' | Tipo : CerExi | Hora : ' . date("His");
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
    // }
    // ob_end_clean();
    unset($pdf);
    return $name2;
}

/*
 * Certifica de libros, en el siguiente orden:
 *
 * - Texto del tipo de certificado
 * - Relación de libros
 */

/**
 *
 * @param type $data
 * @param type $tipo
 * @param type $valorCertificado
 * @param type $operacion
 * @param type $recibo
 * @param type $aleatorio
 * @param type $tipoCertificado
 * @param type $certificadoConsultaRues
 * @param type $escajero
 * @param type $esbanco
 * @param type $firmar
 * @return string
 */
function generarCertificadosPdfLibrosFormato2019($mysqli, $data, $tipo, $valorCertificado = 0, $operacion = '', $recibo = '', $aleatorio = '', $tipoCertificado = '', $certificadoConsultaRues = 'no', $escajero = 'SI', $esbanco = 'NO', $firmar = '') {

    ob_clean();
    ob_start();
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler1.php');
    set_error_handler('myErrorHandler1');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

    if ($aleatorio == '') {
        $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
    }

    //
    $_SESSION["generales"]["fcorte"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["ultanoren"] = $data["ultanoren"];

    //
    $nameLog = 'generarCertificadosPdfLibrosSii_' . date("Ymd");

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
    set_error_handler('myErrorHandler');
    ob_clean();

    if (!class_exists('PDFRequeLib')) {

        class PDFRequeLib extends TCPDF {

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
            public $valor = 0;
            public $tipocertificado = '';
            public $codigoverificacion = '';
            public $organizacion = '';
            public $imprimiotituloprocesosespeciales = '';
            public $norenovado = '';
            public $certificadoConsultaRues = '';

            public function Header() {
                $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 40, PDF_MARGIN_RIGHT);
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

                $txt = 'CERTIFICADO DE LIBROS DE COMERCIO';
                if ($this->organizacion == '12') {
                    $txt = 'CERTIFICADO DE LIBROS DE COMERCIO DE ENTIDADES SIN ÁNIMO DE LUCRO';
                }
                if ($this->organizacion == '14') {
                    $txt = 'CERTIFICADO DE LIBROS DE COMERCIO DE ENTIDADES DE LA ECONOMÍA SOLIDARIA';
                }
                $this->writeHTML('<strong>' . $txt . '</strong>', true, false, true, false, 'C');
                $this->Ln();

                $this->writeHTML('<strong>Fecha expedición: </strong>' . date("d/m/Y") . ' - ' . date("H:i:s") . '</strong>', true, false, true, false, 'C');
                $txt = '';
                if ($this->tituloRecibo != '') {
                    $txt .= 'Recibo No. ' . $this->tituloRecibo;
                }
                if ($this->tituloTipo == 'GasAdm' || $this->tituloTipo == 'GasAfi' || $this->tituloTipo == 'GasOfi' || $this->tituloTipo == 'Consulta') {
                    if ($txt != '') {
                        $txt .= ', ';
                    }
                    $txt .= 'Valor 0';
                } else {
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
                    if ($this->codigoverificacion != '') {
                        $this->writeHTML('<strong>CÓDIGO DE VERIFICACIÓN ' . $this->codigoverificacion . '</strong>', true, false, true, false, 'C');
                        $this->Ln();

                        $txt = 'Verifique el contenido y confiabilidad de este certificado, ingresando a https://sii.confecamaras.co/vista/plantilla/cv.php?empresa=' . CODIGO_EMPRESA . ' y digite el respectivo código, ';
                        $txt .= 'para que visualice la imagen generada al momento de su expedición. La verificación se puede realizar de manera ilimitada, ';
                        $txt .= 'durante 60 días calendario contados a partir de la fecha de su expedición.';

                        $this->writeHTML($txt, true, false, true, false, 'C');
                        $this->Ln();
                    }
                    if (
                            $this->tituloEstadoMatricula != 'MF' &&
                            $this->tituloEstadoMatricula != 'MC' &&
                            $this->tituloEstadoMatricula != 'IC' &&
                            $this->tituloEstadoMatricula != 'IF'
                    ) {
                        if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                            if ($_SESSION["generales"]["ultanoren"] < date("Y")) {
                                if (($this->organizacion == '12' || $this->organizacion == '14') && $this->categoria == '1') {
                                    $txt = 'La inscripción al Registro de las Entidades sin Ánimo de Lucro o al Registro de la Economía Solidaria proporciona seguridad y confianza en los negocios,';
                                    // $txt .= 'renueve su inscripción a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . ' y evite sanciones de hasta  17 S.M.L.M.V';
                                    $txt .= 'renueve su inscripción a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . '.';
                                } else {
                                    $txt = 'La matrícula mercantil proporciona seguridad y confianza en los negocios,';
                                    // $txt .= 'renueve su matrícula a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . ' y evite sanciones de hasta  17 S.M.L.M.V';
                                    $txt .= 'renueve su matrícula a más tardar el ' . \funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"]) . '.';
                                }
                                $this->SetFontSize(8);
                                $this->writeHTML($txt, true, false, true, false, 'C');
                                $this->writeHTML('-----------------------------------------------------------------------------------------------------------', true, false, true, false, 'C');
                                $this->Ln();
                            }
                        }
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
                        $this->RotatedText(50, 180, 'CON LA OBLIGACIÓN LEGAL DE', 45);
                        $this->SetTextColor(0, 0, 0);

                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(26);
                        if (substr($this->matricula, 0, 1) != 'N' && substr($this->matricula, 0, 1) != 'S') {
                            if ($this->organizacion == '10' && $this->categoria == '1') {
                                $this->RotatedText(70, 180, 'RENOVAR SU INSCRIPCIÓN', 45);
                            } else {
                                $this->RotatedText(50, 200, 'RENOVAR SU MATRÍCULA MERCANTIL', 45);
                            }
                        } else {
                            $this->RotatedText(70, 180, 'RENOVAR SU INSCRIPCIÓN', 45);
                        }
                        $this->SetTextColor(0, 0, 0);
                        $this->SetTextColor(202, 202, 202);
                        $this->SetFontSize(30);
                        $this->RotatedText(53, 207, '--------------------------------------------------', 45);
                        $this->SetTextColor(0, 0, 0);
                    }
                }
            }

            public function Header1() {
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
                    if (date("Ymd") <= $_SESSION["generales"]["fcorte"]) {
                        $this->SetFontSize(7);
                        $txt = 'LA MATRÍCULA MERCANTIL PROPORCIONA SEGURIDAD Y CONFIANZA EN LOS NEGOCIOS<br>';
                        // $txt .= 'RENUEVE SU MATRÍCULA A MÁS TARDAR EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"])) . ' Y EVITE SANCIONES DE HASTA 17 S.M.L.M.V';
                        $txt .= 'RENUEVE SU MATRÍCULA A MÁS TARDAR EL ' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($_SESSION["generales"]["fcorte"])) . '.';
                        $this->writeHTML($txt, true, false, true, false, 'C');
                    } else {
                        $this->Ln();
                        $this->Ln();
                    }
                } else {
                    $this->Ln();
                    $this->Ln();
                }
                // if ($this->tituloTipo == 'Consulta' || $this->tituloTipo == 'Api') {
                if ($this->tituloTipo == 'Consulta') {
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
    $pdf = new PDFRequeLib(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    if (defined('TAMANO_LETRA_CERTIFICADOS_SII') && trim(TAMANO_LETRA_CERTIFICADOS_SII) != '') {
        $pdf->tamanoLetra = TAMANO_LETRA_CERTIFICADOS_SII;
    }

    //
    $pdf->tipocertificado = 'CerLib';
    $pdf->tituloEstadoMatricula = $data["estadomatricula"];
    $pdf->tituloTipo = $tipo;
    $pdf->tituloPathAbsoluto = PATH_ABSOLUTO_SITIO;
    $pdf->tituloCamara = $_SESSION["generales"]["codigoempresa"];
    $pdf->tituloNombreCamara = RAZONSOCIAL;
    if ($data["nombrebase64decodificado"] != '') {
        $pdf->tituloRazonSocial = $data["nombrebase64decodificado"];
    } else {
        $pdf->tituloRazonSocial = $data["nombre"];
    }
    $pdf->tituloRecibo = $recibo;
    $pdf->tituloOperacion = $operacion;
    $pdf->tituloTipoHttp = TIPO_HTTP;
    $pdf->tituloHttpHost = HTTP_HOST;
    $pdf->codigoverificacion = $aleatorio;
    $pdf->valor = $valorCertificado;
    $pdf->organizacion = $data["organizacion"];

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

    // *************************************************************************** //
    // Mensaje elecciones de juntag directiva
    // *************************************************************************** //
    if (substr($data["matricula"], 0, 1) != 'S') {
        armarTextoEleccionesFormato2019($mysqli, $pdf);
    }

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
    if ($data["estadomatricula"] == 'MI' || $data["estadomatricula"] == 'II') {
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
    armarDatosBasicosLibrosFormato2019($mysqli, $pdf, $data);
    armarLibrosFormato2019($mysqli, $pdf, $data);
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'RR-LIBROS', 'RECURSOS DE REPOSICIÓN A INSCRIPCIONES EN LIBROS', '');
    armarCertificaFirmezaFormato2019($pdf);
    armarTextoFirmaFormato2019($pdf, $aleatorio, CERTIFICADOS_FIRMA_DIGITAL);
    if ($pdf->tituloTipo != 'Consulta' && $pdf->tituloTipo != 'Revision' && $pdf->tituloTipo != 'Api-Consulta') {
        armarTextoFirmaQueEsFormato2019($pdf, $aleatorio);
        armarTextoFirmaVerificacionFormato2019($pdf, $aleatorio);
        armarTextoFirmaMecanicaFormato2019($pdf);

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
    // }
    //
    if ($pdf->tituloTipo == 'Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        unset($pdf);
        ob_end_clean();
        return $name1;
    }
    if ($pdf->tituloTipo == 'ConsultaD') {
        $namex = $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $namex;
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $namex;
    }

    if ($pdf->tituloTipo == 'Api-Consulta') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        $name2 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificadoApi-' . $aleatorio . '.pdf';
        ob_end_clean();
        $pdf->Output($name1, "F");
        unset($pdf);
        return $name2;
    }

    if ($pdf->tituloTipo == 'Revision') {
        $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-ConsultaCertificado-' . $aleatorio . '.pdf';
        $pdf->Output($name1, "F");
        unset($pdf);
        ob_end_clean();
        return $name1;
    }

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
    }

    // ob_end_clean();
    unset($pdf);
    return $name2;
}

// Fundamento legal
function armarTextoFundamentoFormato2019($mysqli, $pdf, $data, $nameLog) {
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
        if ($data["claseespesadl"] == '61') {
            $pdf->writeHTML('<strong>CON FUNDAMENTO EN LAS INSCRIPCIONES EFECTUADAS EN EL REGISTRO DE APODERADOS JUDICIALES DE ENTIDADES EXTRANJERAS DE DERECHO PRIVADO SIN ÁNIMO DE LUCRO, LA CÁMARA DE COMERCIO CERTIFICA:</strong>', true, false, true, false, 'C');
        } else {
            if ($data["claseespesadl"] == '62') {
                $pdf->writeHTML('<strong>CON FUNDAMENTO EN LAS INSCRIPCIONES EFECTUADAS EN EL REGISTRO DE VEEDURÍAS CIUDADANAS, LA CÁMARA DE COMERCIO CERTIFICA:</strong>', true, false, true, false, 'C');
            } else {
                $pdf->writeHTML('<strong>CON FUNDAMENTO EN LAS INSCRIPCIONES DEL REGISTRO DE ENTIDADES SIN ÁNIMO DE LUCRO Y DE LA ECONOMIA SOLIDARIA, LA CÁMARA DE COMERCIO CERTIFICA:</strong>', true, false, true, false, 'C');
            }
        }
    } else {
        if ($data["organizacion"] == '10' && $data["categoria"] == '1') {
            $pdf->writeHTML('<strong>CON FUNDAMENTO EN LAS INSCRIPCIONES EFECTUADAS EN EL REGISTRO MERCANTIL, LA CÁMARA DE COMERCIO CERTIFICA:</strong>', true, false, true, false, 'C');
        } else {
            $pdf->writeHTML('<strong>CON FUNDAMENTO EN LA MATRÍCULA E INSCRIPCIONES EFECTUADAS EN EL REGISTRO MERCANTIL, LA CÁMARA DE COMERCIO CERTIFICA:</strong>', true, false, true, false, 'C');
        }
    }
    $pdf->Ln();
}

// Reflejo situación legal
function armarTextoReflejoSituacionLegalFormato2019($mysqli, $pdf, $data) {
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $imprimio = 'no';
    if ($data["organizacion"] == '01') {
        $txt = 'Este certificado refleja la situación jurídica registral de la persona natural, a la fecha y hora de su expedición.';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $imprimio = 'si';
    }
    if ($data["organizacion"] == '02') {
        $txt = 'Este certificado refleja la situación jurídica registral del establecimiento, a la fecha y hora de su expedición.';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $imprimio = 'si';
    }
    if ($data["categoria"] == '2') {
        $txt = 'Este certificado refleja la situación jurídica registral de la sucursal, a la fecha y hora de su expedición.';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $imprimio = 'si';
    }
    if ($data["categoria"] == '3') {
        $txt = 'Este certificado refleja la situación jurídica registral de la agencia, a la fecha y hora de su expedición.';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $imprimio = 'si';
    }
    if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
        $txt = 'Este certificado refleja la situación jurídica registral de la entidad, a la fecha y hora de su expedición.';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $imprimio = 'si';
    }
    if ($data["organizacion"] == '10' && $data["categoria"] == '1') {
        $txt = 'Este certificado refleja la situación jurídica registral de la sociedad civil, a la fecha y hora de su expedición.';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $imprimio = 'si';
    }
    if ($imprimio == 'no') {
        $txt = 'Este certificado refleja la situación jurídica registral de la sociedad, a la fecha y hora de su expedición.';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }
    $pdf->Ln();
}

// Nombres e identificación
function armarCertificaNombreIdentificacionDomicilioFormato2019($mysqli, $pdf, $data, $nameLog) {
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $pdf->writeHTML('<strong>NOMBRE, IDENTIFICACIÓN Y DOMICILIO</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    if ($data["organizacion"] == '01') {
        if ($data["nombrebase64decodificado"] != '') {
            $txtnombre = $data["nombrebase64decodificado"];
        } else {
            $txtnombre = $data["nombre"];
        }
        /*
          $txtnombre = $data["nom1"];
          if ($data["nom2"] != '') {
          $txtnombre .= ' ' . $data["nom2"];
          }
          if ($data["ape1"] != '') {
          $txtnombre .= ' ' . $data["ape1"];
          }
          if ($data["ape2"] != '') {
          $txtnombre .= ' ' . $data["ape2"];
          }
         */
        $pdf->writeHTML('Nombres y apellidos : ' . $txtnombre, true, false, true, false, 'L');
    }

    //
    if ($data["organizacion"] == '02') {
        $pdf->writeHTML('Nombre del establecimiento de comercio : ' . $data["nombre"], true, false, true, false, 'L');
    }

    //
    if ($data["categoria"] == '2') {
        $pdf->writeHTML('Nombre de la sucursal : ' . $data["nombre"], true, false, true, false, 'L');
    }

    //
    if ($data["categoria"] == '3') {
        $pdf->writeHTML('Nombre de la agencia : ' . $data["nombre"], true, false, true, false, 'L');
    }

    //
    if ($data["organizacion"] > '02' && $data["categoria"] == '1') {
        $txtnombre = $data["nombre"];
        $txtnombre = str_replace('Ẽ', 'Ê', (string) $txtnombre);
        $txtnombre = str_replace('ĩ', 'Î', (string) $txtnombre);
        $txtnombre = str_replace('Ĩ', 'Î', (string) $txtnombre);
        $pdf->writeHTML('Razón Social : ' . $txtnombre, true, false, true, false, 'L');
    }

    //
    if (trim($data["sigla"]) != '') {
        $txtsigla = $data["sigla"];
        $txtsigla = str_replace('Ẽ', 'Ê', (string) $txtsigla);
        $txtsigla = str_replace('ĩ', 'Î', (string) $txtsigla);
        $txtsigla = str_replace('Ĩ', 'Î', (string) $txtsigla);
        $pdf->writeHTML('Sigla : ' . $txtsigla, true, false, true, false, 'L');
    }

    //
    if ($data["organizacion"] > '02' && $data["categoria"] == '1') {
        if (ltrim($data["nit"], "0") != '') {
            $sp = \funcionesGenerales::separarDv($data["nit"]);
            $txt = 'Nit : ' . $sp["identificacion"] . '-' . $sp["dv"];
            $pdf->writeHTML($txt, true, false, true, false, 'L');
        } else {
            if ($data["estadonit"] == '4' || $data["tipoidentificacion"] == '2') {
                $txt = '<strong>Nit : </strong>' . 'Deberá ser tramitado directamente ante la DIAN.';
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            } else {
                if (diferenciaEntreFechaBase30Formato2019(date("Ymd"), $data["fechamatricula"]) > 2) {
                    $txt = 'Nit : ' . 'No reportó.';
                } else {
                    $txt = 'Nit : ' . 'En trámite.';
                }
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            }
        }
    }

    //
    if ($data["organizacion"] == '01') {
        $txt = 'Identificación : ' . retornarTxtTipoIdeFormato2019($data["tipoidentificacion"]) . ' - ' . $data["identificacion"];
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        if (ltrim($data["nit"], "0") != '') {
            $sp = \funcionesGenerales::separarDv($data["nit"]);
            $txt = 'Nit : ' . $sp["identificacion"] . '-' . $sp["dv"];
            $pdf->writeHTML($txt, true, false, true, false, 'L');
        } else {
            if ($data["estadonit"] == '4' || $data["tipoidentificacion"] == '2') {
                $txt = '<strong>Nit : </strong>' . 'Deberá ser tramitado directamente ante la DIAN.';
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            } else {
                if (diferenciaEntreFechaBase30Formato2019(date("Ymd"), $data["fechamatricula"]) > 2) {
                    $txt = 'Nit : ' . 'No reportó.';
                } else {
                    $txt = 'Nit : ' . 'En trámite.';
                }
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            }
        }
    }

    //
    if ($data["claseespesadl"] == '61' && $data["domicilio_ong"] != '') {
        $pdf->writeHTML('Domicilio principal: ' . $data["domicilio_ong"], true, false, true, false, 'L');
    } else {
        $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["muncom"] . "'");
        $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($data["muncom"], 0, 2) . "'");
        if ($tdep === false || empty($tdep)) {
            $tdep = array('nombre' => '');
        }
        if ($tmun && !empty($tmun)) {
            $pdf->writeHTML('Domicilio: ' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"], true, false, true, false, 'L');
        }
    }
    $pdf->Ln();
}

function armarCertificaNombreDatosGeneralesMatriculaFormato2019($mysqli, $pdf, $data, $nameLog) {
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $pdf->writeHTML('<strong>NOMBRE, DATOS GENERALES Y MATRÍCULA</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'IC' || $data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'IF') {
        $imprimio = 'no';
        if ($data["organizacion"] == '10' && $data["categoria"] == '1') {
            $pdf->SetFont('courier', '', 14);
            $pdf->writeHTML('<strong>*** ESTA INSCRIPCIÓN SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            $imprimio = 'si';
        }
        if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
            $pdf->SetFont('courier', '', 14);
            $pdf->writeHTML('<strong>*** ESTA INSCRIPCIÓN SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            $imprimio = 'si';
        }
        if ($imprimio == 'no') {
            $pdf->SetFont('courier', '', 14);
            $pdf->writeHTML('<strong>*** ESTA MATRICULA SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        }
    }


    if ($data["nombrebase64decodificado"] != '') {
        $txtnombre = $data["nombrebase64decodificado"];
    } else {
        $txtnombre = $data["nombre"];
    }
    $pdf->writeHTML('Nombre : ' . htmlentities($txtnombre), true, false, true, false, 'L');
    $pdf->writeHTML('Matrícula No: ' . $data["matricula"], true, false, true, false, 'L');
    $pdf->writeHTML('Fecha de matrícula: ' . \funcionesGenerales::mostrarFechaLetras1($data["fechamatricula"]), true, false, true, false, 'L');
    if ($data["fechamatricula"] != $data["fecharenovacion"]) {
        $pdf->writeHTML('Ultimo año renovado: ' . $data["ultanoren"], true, false, true, false, 'L');
        $pdf->writeHTML('Fecha de renovación: ' . \funcionesGenerales::mostrarFechaLetras1($data["fecharenovacion"]), true, false, true, false, 'L');
    }
    if ($data["fechacancelacion"] != '') {
        $pdf->writeHTML('Fecha de cancelación: ' . \funcionesGenerales::mostrarFechaLetras1($data["fechacancelacion"]), true, false, true, false, 'L');
    }
    // $pdf->writeHTML('Activos vinculados : ' . \funcionesGenerales::mostrarPesos2($data["actvin"]), true, false, true, false, 'L');
    $pdf->Ln();

    // ******************************************************************************** //
    // Cancelaciones
    // ******************************************************************************** //
    if ($data["organizacion"] == '01' || $data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        foreach ($data["inscripciones"] as $insc) {
            if ($insc["grupoacto"] == '002') {
                if ($insc["tdoc"] == '38') {
                    $txt = 'De acuerdo con lo establecido en el artículo 31 de la Ley 1727 del 11 de julio de 2014, el ';
                    $txt .= \funcionesGenerales::mostrarFechaLetras1($insc["fdoc"]) . ' se inscribe, bajo el no. ' . $insc["nreg"] . ' ';
                    $txt .= 'del Libro ' . retornarLibroFormato2019($insc["lib"]) . ' la cancelación de la matrícula ';
                    $txt .= 'mercantil por concepto de depuración del Registro Unico Empresarial y Social (RUES).';
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                } else {
                    $txt = retornarTipoDocFormato2019($mysqli, $insc["tdoc"], $insc["ndoc"]) . ' del ' . \funcionesGenerales::mostrarFechaLetras1($insc["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
                    $txt .= \funcionesGenerales::mostrarFechaLetras1($insc["freg"]) . ' con el No. ' . $insc["nreg"] . ' del Libro ' . retornarLibroFormato2019($insc["lib"]) . ', ';
                    $txt .= 'se informa: ' . pasar_a_oracion($insc["not"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    }

    // ******************************************************************************** //
    // Cierre de establecimientos
    // ******************************************************************************** //
    if ($data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        foreach ($data["inscripciones"] as $insc) {

            if ($insc["grupoacto"] == '026') {
                $txtcierre = '';
                if ($data["organizacion"] == '02') {
                    $txtcierre = 'del establecimiento de comercio';
                }
                if ($data["categoria"] == '2') {
                    $txtcierre = 'de la sucursal';
                }
                if ($data["categoria"] == '3') {
                    $txtcierre = 'de la agencia';
                }
                $txt = retornarTipoDocFormato2019($mysqli, $insc["tdoc"], $insc["ndoc"]) . ' del ' . \funcionesGenerales::mostrarFechaLetras1($insc["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
                $txt .= \funcionesGenerales::mostrarFechaLetras1($insc["freg"]) . ' con el No. ' . $insc["nreg"] . ' del Libro ' . retornarLibroFormato2019($insc["lib"]) . ', ';
                $txt .= 'el propietario informó sobre el cierre ' . $txtcierre . '.<br><br>';
                $txt .= 'En cumplimiento de lo dispuesto en el numeral 1.3.5.8 de la Circular Única de la Superintendencia de Sociedades, a partir de la inscripción del cierre ' . $txtcierre . ' ';
                $txt .= 'no se causarán derechos de renovación de la matrícula mercantil.';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }
}

// Matrícula
function armarCertificaMatriculaFormato2019($mysqli, $pdf, $data, $nameLog) {
    $tienecambidom = 'no';
    foreach ($data["inscripciones"] as $incs) {
        if ($incs["grupoacto"] == '024') {
            $tienecambidom = 'si';
        }
    }
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if (($data["organizacion"] == '10' || $data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
        $pdf->writeHTML('<strong>INSCRIPCIÓN</strong>', true, false, true, false, 'C');
        $pdf->Ln();

        //
        if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'IC' || $data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'IF') {
            $imprimio = 'no';
            if ($data["organizacion"] == '10' && $data["categoria"] == '1') {
                $pdf->SetFont('courier', '', 14);
                $pdf->writeHTML('<strong>*** ESTA INSCRIPCIÓN SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                $imprimio = 'si';
            }
            if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
                $pdf->SetFont('courier', '', 14);
                $pdf->writeHTML('<strong>*** ESTA INSCRIPCIÓN SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                $imprimio = 'si';
            }

            if ($imprimio == 'no') {
                $pdf->SetFont('courier', '', 14);
                $pdf->writeHTML('<strong>*** ESTA MATRICULA SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            }
        }


        $pdf->writeHTML('Inscripción No: ' . $data["matricula"], true, false, true, false, 'L');
        if ($tienecambidom == 'si') {
            $pdf->writeHTML('Fecha de inscripción en esta Cámara de Comercio: ' . \funcionesGenerales::mostrarFechaLetras1($data["fechamatricula"]), true, false, true, false, 'L');
        } else {
            $pdf->writeHTML('Fecha de inscripción: ' . \funcionesGenerales::mostrarFechaLetras1($data["fechamatricula"]), true, false, true, false, 'L');
        }
    } else {
        $pdf->writeHTML('<strong>MATRÍCULA</strong>', true, false, true, false, 'C');
        $pdf->Ln();

        //
        if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'IC' || $data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'IF') {
            $imprimio = 'no';
            if ($data["organizacion"] == '10' && $data["categoria"] == '1') {
                $pdf->SetFont('courier', '', 14);
                $pdf->writeHTML('<strong>*** ESTA INSCRIPCIÓN SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                $imprimio = 'si';
            }
            if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
                $pdf->SetFont('courier', '', 14);
                $pdf->writeHTML('<strong>*** ESTA INSCRIPCIÓN SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                $imprimio = 'si';
            }

            if ($imprimio == 'no') {
                $pdf->SetFont('courier', '', 14);
                $pdf->writeHTML('<strong>*** ESTA MATRICULA SE ENCUENTRA CANCELADA ***</strong>', true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            }
        }

        $pdf->writeHTML('Matrícula No: ' . $data["matricula"], true, false, true, false, 'L');
        if ($tienecambidom == 'si') {
            $pdf->writeHTML('Fecha de matrícula en esta Cámara de Comercio: ' . \funcionesGenerales::mostrarFechaLetras1($data["fechamatricula"]), true, false, true, false, 'L');
        } else {
            $pdf->writeHTML('Fecha de matrícula: ' . \funcionesGenerales::mostrarFechaLetras1($data["fechamatricula"]), true, false, true, false, 'L');
        }
    }

    if ($data["fechamatricula"] != $data["fecharenovacion"]) {
        $pdf->writeHTML('Ultimo año renovado: ' . $data["ultanoren"], true, false, true, false, 'L');
        $pdf->writeHTML('Fecha de renovación: ' . \funcionesGenerales::mostrarFechaLetras1($data["fecharenovacion"]), true, false, true, false, 'L');
    } else {
        if ($data["fecmatant"] != '') {
            $pdf->writeHTML('Ultimo año renovado: ' . $data["ultanoren"], true, false, true, false, 'L');
            $pdf->writeHTML('Fecha de renovación: ' . \funcionesGenerales::mostrarFechaLetras1($data["fecharenovacion"]), true, false, true, false, 'L');            
        }
    }

    if ($data["fechacancelacion"] != '') {
        $pdf->writeHTML('Fecha de cancelación: ' . \funcionesGenerales::mostrarFechaLetras1($data["fechacancelacion"]), true, false, true, false, 'L');
    }

    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && $data["categoria"] == '1')) {
        if (trim($data["gruponiif"]) != '0' && $data["gruponiif"] != '') {
            $pdf->writeHTML('Grupo NIIF : ' . retornarRegistroMysqliApi($mysqli, 'bas_gruponiif', "id='" . $data["gruponiif"] . "'", "descripcion"), true, false, true, false, 'L');
        } else {
            $pdf->writeHTML('Grupo NIIF : No reportó.', true, false, true, false, 'L');
        }
    }

    if ($data["organizacion"] == '02' || ($data["categoria"] == '2' || $data["categoria"] == '3')) {
        $pdf->writeHTML('Activos vinculados : ' . \funcionesGenerales::mostrarPesos2($data["actvin"]), true, false, true, false, 'L');
    }
    $pdf->Ln();

    // ******************************************************************************** //
    // Autorizaciones a menores de edad
    // ******************************************************************************** //
    if ($data["organizacion"] == '01') {
        $regl4 = false;
        $lib4 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='RM04' and identificacion='" . $data["identificacion"] . "'", "fecharegistro asc");
        if ($lib4 && !empty($lib4)) {
            foreach ($lib4 as $l4) {
                if ($l4["acto"] == '3040') {
                    $regl4 = $l4;
                }
                if ($l4["acto"] == '3041') {
                    $regl4 = false;
                }
            }
        }
        if ($regl4) {
            $txtnombre = $data["nom1"];
            if ($data["nom2"] != '') {
                $txtnombre .= ' ' . $data["nom2"];
            }
            if ($data["ape1"] != '') {
                $txtnombre .= ' ' . $data["ape1"];
            }
            if ($data["ape2"] != '') {
                $txtnombre .= ' ' . $data["ape2"];
            }
            if ($txtnombre == '') {
                $txtnombre = $regl4["nombre"];
            }
            $txt = 'Por documento privado del ' . \funcionesGenerales::mostrarFechaLetras1($regl4["fechadocumento"]) . ' inscrito en esta Cámara de Comecio el ';
            $txt .= \funcionesGenerales::mostrarFechaLetras1($regl4["fecharegistro"]) . ', con el No. ' . $regl4["registro"] . ' del Libro IV, ';
            $txt .= $regl4["txtapoderados"] . ' autorizaron al menor de edad ' . $txtnombre . ' para ejercer el comercio.';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $pdf->Ln();
        }
    }

    // ******************************************************************************** //
    // Pérdida de calidad de comerciante y su rectivación
    // Ojo aquí es posible que toque cambiar algo
    // ******************************************************************************** //
    if ($data["organizacion"] == '01') {
        $perdida = false;
        $reactivacion = false;
        foreach ($data["inscripciones"] as $insc) {
            if ($insc["grupoacto"] == '071') {
                $perdida = $insc;
                $reactivacion = false;
            }
            if ($insc["grupoacto"] == '073') {
                $reactivacion = $insc;
            }
        }

        if ($perdida && $reactivacion == false) {
            $txt = 'Por documento privado del ' . \funcionesGenerales::mostrarFechaLetras1($perdida["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
            $txt .= \funcionesGenerales::mostrarFechaLetras1($perdida["freg"]) . ' con el No. ' . $perdida["nreg"] . ' del Libro ' . retornarLibroFormato2019($perdida["lib"]) . ', ';
            $txt .= 'la  persona natural informó sobre la pérdida de su calidad de comerciante.<br><br>';
            $txt .= 'En cumplimiento de lo dispuesto en el numeral 1.3.5.8, de la Circular 100-000002 de la Superintendencia de Sociedades, ';
            $txt .= 'a partir de la inscripción de la pérdida de  la  calidad  de  comerciante  no  se  causarán  derechos  de renovación de la matrícula mercantil.';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $pdf->Ln();
        }
        if ($perdida && $reactivacion) {
            $txt = 'Por documento privado del ' . \funcionesGenerales::mostrarFechaLetras1($perdida["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
            $txt .= \funcionesGenerales::mostrarFechaLetras1($perdida["freg"]) . ' con el No. ' . $perdida["nreg"] . ' del Libro ' . retornarLibroFormato2019($perdida["lib"]) . ', ';
            $txt .= 'la  persona natural informó sobre la pérdida de su calidad de comerciante y por documento privado del ' . \funcionesGenerales::mostrarFechaLetras1($reactivacion["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
            $txt .= \funcionesGenerales::mostrarFechaLetras1($reactivacion["freg"]) . ' con el No. ' . $reactivacion["nreg"] . ' del Libro ' . retornarLibroFormato2019($reactivacion["lib"]) . ', ';
            $txt .= 'la  persona natural reactivó su calidad de comerciante.';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $pdf->Ln();
        }
    }

    // ******************************************************************************** //
    // Cambios de domicilio
    // ******************************************************************************** //
    if ($data["organizacion"] == '01') {
        foreach ($data["inscripciones"] as $insc) {
            if ($insc["grupoacto"] == '024') {
                $txt = 'Por documento privado del ' . \funcionesGenerales::mostrarFechaLetras1($insc["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
                $txt .= \funcionesGenerales::mostrarFechaLetras1($insc["freg"]) . ' con el No. ' . $insc["nreg"] . ' del Libro ' . retornarLibroFormato2019($insc["lib"]) . ', ';
                $txt .= 'se informa: ' . pasar_a_oracion($insc["not"]);
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }

    // ******************************************************************************** //
    // Cancelaciones
    // ******************************************************************************** //
    if ($data["organizacion"] == '01' || $data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        foreach ($data["inscripciones"] as $insc) {
            if ($insc["grupoacto"] == '002') {
                if ($insc["tdoc"] !== "38") {
                    $ndoc = $insc["ndoc"];
                    if ($insc["ndocext"] != '') {
                        $ndoc = $insc["ndocext"];
                    }
                    $txt = retornarTipoDocFormato2019($mysqli, $insc["tdoc"], $ndoc) . ' del ' . \funcionesGenerales::mostrarFechaLetras1($insc["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
                    $txt .= \funcionesGenerales::mostrarFechaLetras1($insc["freg"]) . ' con el No. ' . $insc["nreg"] . ' del Libro ' . retornarLibroFormato2019($insc["lib"]) . ', ';
                    $txt .= 'se informa: ' . pasar_a_oracion($insc["not"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                } else {
                    $txt = 'De acuerdo con lo establecido en el artículo 31 de la Ley 1727 del 11 de julio de 2014, el ';
                    $txt .= \funcionesGenerales::mostrarFechaLetras1($insc["fdoc"]) . ' se inscribe, bajo el no. ' . $insc["nreg"] . ' ';
                    $txt .= 'del Libro ' . retornarLibroFormato2019($insc["lib"]) . ' la cancelación de la matrícula ';
                    $txt .= 'mercantil por concepto de depuración del Registro Unico Empresarial y Social (RUES).';
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    }

    // ******************************************************************************** //
    // Cierre de establecimientos
    // ******************************************************************************** //
    if ($data["organizacion"] == '02') {
        foreach ($data["inscripciones"] as $insc) {
            if ($insc["grupoacto"] == '026') {
                $txt = retornarTipoDocFormato2019($mysqli, $insc["tdoc"], $insc["ndoc"]) . ' del ' . \funcionesGenerales::mostrarFechaLetras1($insc["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
                // $txt = 'Por documento privado del ' . \funcionesGenerales::mostrarFechaLetras1($insc["fdoc"]) . ', inscrito en esta Cámara de Comercio el ';
                $txt .= \funcionesGenerales::mostrarFechaLetras1($insc["freg"]) . ' con el No. ' . $insc["nreg"] . ' del Libro ' . retornarLibroFormato2019($insc["lib"]) . ', ';
                $txt .= 'el propietario informó sobre el cierre del establecimiento de comercio.<br><br>';
                $txt .= 'En cumplimiento de lo dispuesto en el numeral 1.3.5.8 de la Circular 100-000002 de la Superintendencia de Sociedades, a partir de la inscripción del cierre del ';
                $txt .= 'establecimiento de comercio no se causarán derechos de renovación de la matrícula mercantil.';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }
}

// Ubicación
function armarCertificaUbicacionFormato2019($mysqli, $pdf, $data, $nameLog) {
    $tienesitioweb = '';
    foreach ($data["inscripciones"] as $incs) {
        if ($incs["acto"] == '4000') {
            if (strtolower(substr($incs["not"], 0, 3)) == 'www') {
                $tienesitioweb = $incs["not"];
            } else {
                $tienesitioweb = $data["urlcom"];
            }
        }
        if ($incs["acto"] == '4001') {
            $tienesitioweb = '';
        }
    }
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $pdf->writeHTML('<strong>UBICACIÓN</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    // $txtDireccion = pasar_a_oracion($data["dircom"]);
    $txtDireccion = $data["dircom"];
    if ($data["barriocom"] != '') {
        $txtDireccion .= ' - ' . \funcionesGenerales::limpiarTextosRedundantes(pasar_a_oracion(retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $data["muncom"] . "' and idbarrio='" . $data["barriocom"] . "'", "nombre")));
    }
    if ($data["organizacion"] == '02') {
        $pdf->writeHTML('Dirección Comercial : ' . $txtDireccion, true, false, true, false, 'L');
    } else {
        $pdf->writeHTML('Dirección del domicilio principal : ' . $txtDireccion, true, false, true, false, 'L');
    }
    $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["muncom"] . "'");
    $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($data["muncom"], 0, 2) . "'");
    if ($tdep === false || empty($tdep)) {
        $tdep = array('nombre' => '');
    }
    $pdf->writeHTML('Municipio : ' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"], true, false, true, false, 'L');
    if (trim($data["emailcom"]) != '') {
        $pdf->writeHTML('Correo electrónico : ' . $data["emailcom"], true, false, true, false, 'L');
    } else {
        $pdf->writeHTML('Correo electrónico : No reportó.', true, false, true, false, 'L');
    }
    if (trim($data["telcom1"]) != '') {
        $pdf->writeHTML('Teléfono comercial 1 : ' . $data["telcom1"], true, false, true, false, 'L');
    } else {
        $pdf->writeHTML('Teléfono comercial 1 : No reportó.', true, false, true, false, 'L');
    }
    if (trim($data["telcom2"]) != '') {
        $pdf->writeHTML('Teléfono comercial 2 : ' . $data["telcom2"], true, false, true, false, 'L');
    } else {
        $pdf->writeHTML('Teléfono comercial 2 : No reportó.', true, false, true, false, 'L');
    }
    if (trim($data["celcom"]) != '') {
        $pdf->writeHTML('Teléfono comercial 3 : ' . $data["celcom"], true, false, true, false, 'L');
    } else {
        $pdf->writeHTML('Teléfono comercial 3 : No reportó.', true, false, true, false, 'L');
    }
    if ($tienesitioweb != '') {
        $pdf->writeHTML('Página web : ' . strtolower($tienesitioweb), true, false, true, false, 'L');
        /*
          if (subtr(strtolower($tienesitioweb), 0, 3) == 'www') {
          $pdf->writeHTML('Página web : ' . strtolower($tienesitioweb), true, false, true, false, 'L');
          } else {
          if (trim($data["urlcom"]) != '') {
          $pdf->writeHTML('Página web : ' . $data["urlcom"], true, false, true, false, 'L');
          }
          }
         */
    }
    $pdf->Ln();

    // if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && $data["categoria"] == '1') || $data["categoria"] == '2' || $data["categoria"] == '3') {
    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && $data["categoria"] == '1') || $data["categoria"] == '2') {
        // $txtDireccion = pasar_a_oracion($data["dirnot"]);
        $txtDireccion = $data["dirnot"];
        if ($data["barrionot"] != '') {
            $txtDireccion .= ' - ' . \funcionesGenerales::limpiarTextosRedundantes(pasar_a_oracion(retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $data["munnot"] . "' and idbarrio='" . $data["barrionot"] . "'", "nombre")));
        }
        $pdf->writeHTML('Dirección para notificación judicial : ' . $txtDireccion, true, false, true, false, 'L');

        $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["munnot"] . "'");
        $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($data["munnot"], 0, 2) . "'");
        if ($tdep === false || empty($tdep)) {
            $tdep = array('nombre' => '');
        }
        if ($tmun && !empty($tmun)) {
            $pdf->writeHTML('Municipio : ' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"], true, false, true, false, 'L');
        }
        if (trim($data["emailnot"]) != '') {
            $pdf->writeHTML('Correo electrónico de notificación : ' . $data["emailnot"], true, false, true, false, 'L');
        } else {
            $pdf->writeHTML('Correo electrónico de notificación : No reportó.', true, false, true, false, 'L');
        }

        if (trim($data["telnot"]) != '') {
            $pdf->writeHTML('Teléfono para notificación 1 : ' . $data["telnot"], true, false, true, false, 'L');
            // } else {
            //    $pdf->writeHTML('Teléfono para notificación 1 : No reportó.', true, false, true, false, 'L');
        }
        if (trim($data["telnot2"]) != '') {
            $pdf->writeHTML('Teléfono para notificación 2 : ' . $data["telnot2"], true, false, true, false, 'L');
            // } else {
            //    $pdf->writeHTML('Teléfono notificación 2 : No reportó.', true, false, true, false, 'L');
        }
        if (trim($data["celnot"]) != '') {
            $pdf->writeHTML('Teléfono para notificación 3 : ' . $data["celnot"], true, false, true, false, 'L');
            // } else {
            //    $pdf->writeHTML('Teléfono notificación 3 : No reportó.', true, false, true, false, 'L');
        }
        $pdf->Ln();
    }

    // *************************************************************************** //
    // Certifica de envío de mensajes al email 
    // *************************************************************************** //
    // if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && ($data["categoria"] == '1' || $data["categoria"] == '2'))) {
    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && ($data["categoria"] == '1' || $data["categoria"] == '2'))) {
        $aut = 'NO';
        if (substr($data["ctrmennot"], 0, 1) == 'S' || substr($data["ctrmennot"], 0, 1) == 's') {
            $aut = 'SI';
        }
        if ($data["claseespesadl"] == '61') {
            $txt = 'El apoderado ';
        } else {
            if ($data["organizacion"] == '01') {
                $txt = 'La persona natural ';
            } else {
                if ($data["categoria"] == '1') {
                    $txt = 'La persona jurídica ';
                } else {
                    $txt = 'La persona jurídica ';
                }
            }
        }
        $txt .= '<strong>' . $aut . '</strong> autorizó para recibir notificaciones personales a través del correo ';
        $txt .= 'electrónico, de conformidad con lo establecido en los artículos 291 del Código General del Proceso y del 67 del Código ';
        $txt .= 'de Procedimiento Administrativo y de lo Contencioso Administrativo';
        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }
}

// *************************************************************************** //
// Certifica datos de la ultima renovación
// *************************************************************************** //
function armarCertificaRenovacionFormato2019($mysqli, $pdf, $data) {
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
                $imprimiotexto = 'no';
                if ($data["organizacion"] == '01') {
                    $txt = '<strong>EL COMERCIANTE NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL. POR TAL RAZÓN, LOS DATOS ';
                    $txt .= 'CORRESPONDEN A LA ÚLTIMA INFORMACIÓN SUMINISTRADA POR EL COMERCIANTE EN EL FORMULARIO DE MATRÍCULA Y/O RENOVACIÓN ';
                    $txt .= 'DEL AÑO: ' . $data["ultanoren"] . '</strong>';
                    $imprimiotexto = 'si';
                }
                if ($data["organizacion"] == '02') {
                    $txt = '<strong>EL PROPIETARIO DEL ESTABLECIMIENTO DE COMERCIO NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL. POR TAL RAZÓN, LOS DATOS ';
                    $txt .= 'CORRESPONDEN A LA ÚLTIMA INFORMACIÓN SUMINISTRADA EN EL FORMULARIO DE MATRÍCULA Y/O RENOVACIÓN ';
                    $txt .= 'DEL AÑO: ' . $data["ultanoren"] . '</strong>';
                    $imprimiotexto = 'si';
                }
                if ($data["categoria"] == '1' && ($data["organizacion"] == '12' || $data["organizacion"] == '14')) {
                    $txt = '<strong>LA ENTIDAD SIN ÁNIMO DE LUCRO NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU INSCRIPCIÓN. POR TAL RAZÓN, LOS DATOS ';
                    $txt .= 'CORRESPONDEN A LA ÚLTIMA INFORMACIÓN SUMINISTRADA POR EL INSCRITO EN EL FORMULARIO DE INSCRIPCIÓN Y/O RENOVACIÓN ';
                    $txt .= 'DEL AÑO: ' . $data["ultanoren"] . '</strong>';
                    $imprimiotexto = 'si';
                }
                if ($data["categoria"] == '1' && substr($data["matricula"], 0, 1) == 'N') {
                    $txt = '<strong>LA PERSONA JURÍDICA NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU INSCRIPCIÓN. POR TAL RAZÓN, LOS DATOS ';
                    $txt .= 'CORRESPONDEN A LA ÚLTIMA INFORMACIÓN SUMINISTRADA POR EL INSCRITO EN EL FORMULARIO DE INSCRIPCIÓN Y/O RENOVACIÓN ';
                    $txt .= 'DEL AÑO: ' . $data["ultanoren"] . '</strong>';
                    $imprimiotexto = 'si';
                }
                if ($data["categoria"] == '1' && $data["organizacion"] == '10' && $data["categoria"] == '1') {
                    $txt = '<strong>LA PERSONA JURÍDICA NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU INSCRIPCIÓN. POR TAL RAZÓN, LOS DATOS ';
                    $txt .= 'CORRESPONDEN A LA ÚLTIMA INFORMACIÓN SUMINISTRADA POR EL INSCRITO EN EL FORMULARIO DE INSCRIPCIÓN Y/O RENOVACIÓN ';
                    $txt .= 'DEL AÑO: ' . $data["ultanoren"] . '</strong>';
                    $imprimiotexto = 'si';
                }
                if ($data["categoria"] == '1' && $imprimiotexto == 'no') {
                    $txt = '<strong>LA PERSONA JURÍDICA NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL. POR TAL RAZÓN, LOS DATOS ';
                    $txt .= 'CORRESPONDEN A LA ÚLTIMA INFORMACIÓN SUMINISTRADA POR EL COMERCIANTE EN EL FORMULARIO DE MATRÍCULA Y/O RENOVACIÓN ';
                    $txt .= 'DEL AÑO: ' . $data["ultanoren"] . '</strong>';
                    $imprimiotexto = 'si';
                }
                if ($data["categoria"] == '2') {
                    $txt = '<strong>LA SUCURSAL NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL. POR TAL RAZÓN, LOS DATOS ';
                    $txt .= 'CORRESPONDEN A LA ÚLTIMA INFORMACIÓN SUMINISTRADA POR EL COMERCIANTE EN EL FORMULARIO DE MATRÍCULA Y/O RENOVACIÓN ';
                    $txt .= 'DEL AÑO: ' . $data["ultanoren"] . '</strong>';
                    $imprimiotexto = 'si';
                }
                if ($data["categoria"] == '3') {
                    $txt = '<strong>LA AGENCIA NO HA CUMPLIDO CON LA OBLIGACIÓN LEGAL DE RENOVAR SU MATRÍCULA MERCANTIL. POR TAL RAZÓN, LOS DATOS ';
                    $txt .= 'CORRESPONDEN A LA ÚLTIMA INFORMACIÓN SUMINISTRADA POR EL COMERCIANTE EN EL FORMULARIO DE MATRÍCULA Y/O RENOVACIÓN ';
                    $txt .= 'DEL AÑO: ' . $data["ultanoren"] . '</strong>';
                    $imprimiotexto = 'si';
                }
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
                $pdf->SetTextColor(0, 0, 0);
            }
        }

        //
        if ($data["organizacion"] > '02' && $data["categoria"] == '1') {
            $imprimiodisolucion = 'no';
            if ($data["disueltaporacto510"] == 'si') {
                $imprimiodisolucion = 'si';
                $pdf->SetFont('courier', 'B', 8);
                $pdf->SetTextColor(139, 0, 0);
                if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                    $txt = '<strong>LAS PERSONAS JURÍDICAS EN ESTADO DE LIQUIDACIÓN NO TIENEN QUE RENOVAR LA INSCRIPCION DESDE LA FECHA EN QUE ';
                    $txt .= 'SE INSCRIBIO EL DOCUMENTO QUE DA INCIO AL PROCESO DE LIQUIDACIÓN. (ARTÍCULO 31 LEY 1429 DE 2010, NUMERAL 1.3.5.10 ';
                    $txt .= 'DE LA CIRCULAR DE LA SUPERINTENDENCIA DE SOCIEDADES).</strong>';
                } else {
                    $txt = '<strong>LAS PERSONAS JURÍDICAS EN ESTADO DE LIQUIDACIÓN NO TIENEN QUE RENOVAR LA MATRÍCULA MERCANTIL DESDE LA FECHA EN QUE ';
                    $txt .= 'SE INSCRIBIO EL DOCUMENTO QUE DA INCIO AL PROCESO DE LIQUIDACIÓN. (ARTÍCULO 31 LEY 1429 DE 2010, NUMERAL 1.3.5.10 ';
                    $txt .= 'DE LA CIRCULAR DE LA SUPERINTENDENCIA DE SOCIEDADES).</strong>';
                }
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->SetFont('courier', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Ln();
            }
            if ($data["disueltaporvencimiento"] == 'si' && $imprimiodisolucion == 'no') {
                $imprimiodisolucion = 'si';
                $pdf->SetFont('courier', 'B', 8);
                $pdf->SetTextColor(139, 0, 0);
                if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                    $txt = '<strong>LAS PERSONAS JURÍDICAS EN ESTADO DE LIQUIDACIÓN NO TIENEN QUE RENOVAR LA INSCRIPCIÓN DESDE LA FECHA EN QUE ';
                    $txt .= 'SE INSCRIBIO EL DOCUMENTO QUE DA INCIO AL PROCESO DE LIQUIDACIÓN. (ARTÍCULO 31 LEY 1429 DE 2010, NUMERAL 1.3.5.10 ';
                    $txt .= 'DE LA CIRCULAR DE LA SUPERINTENDENCIA DE SOCIEDADES).</strong>';
                } else {
                    $txt = '<strong>LAS PERSONAS JURÍDICAS EN ESTADO DE LIQUIDACIÓN NO TIENEN QUE RENOVAR LA MATRÍCULA MERCANTIL DESDE LA FECHA EN QUE ';
                    $txt .= 'SE INSCRIBIO EL DOCUMENTO QUE DA INCIO AL PROCESO DE LIQUIDACIÓN. (ARTÍCULO 31 LEY 1429 DE 2010, NUMERAL 1.3.5.10 ';
                    $txt .= 'DE LA CIRCULAR DE LA SUPERINTENDENCIA DE SOCIEDADES).</strong>';
                }
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->SetFont('courier', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Ln();
            }

            if ($data["perdidacalidadcomerciante"] == 'si' && $imprimiodisolucion == 'no') {
                $imprimiodisolucion = 'si';
                $pdf->SetFont('courier', 'B', 8);
                $pdf->SetTextColor(139, 0, 0);
                $txt = '<strong>LAS PERSONAS NATURALES QUE INSCRIBEN ACTO DE CESACIÓN DE ACTIVIDAD COMERCIAL, NO TIENEN OBLIGACIÓN DE RENOVAR SU MATRÍCULA MERCANTIL ';
                $txt .= 'A PARTIR DEL MOMENTO EN QUE SE REALIZA LA INSCRIPCIÓN EN CUESTIÓN.';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->SetFont('courier', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Ln();
            }
        }
    }
}

function armarCertificaPersoneriaJuridicaFormato2019($mysqli, $pdf, $data) {
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
        $txt = '<strong>PERSONERÍA JURÍDICA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        if ($data["organizacion"] == '12') {
            $txt = 'Que la Entidad Sin Ánimo de Lucro obtuvo su personería jurídica el ';
        }
        if ($data["organizacion"] == '14') {
            $txt = 'Que la entidad Entidad de Economía Solidaria obtuvo su personería jurídica el ';
        }
        $txt .= \funcionesGenerales::mostrarFechaLetras1($data["fecperj"]) . ' bajo el número ' . $data["numperj"] . ' ';
        if (ltrim(trim($data["idorigenperj"]), "0") != '') {
            if (strlen(trim($data["idorigenperj"])) == 5) {
                $txt .= 'otorgada por ' . retornarRegistroMysqliApi($mysqli, "mreg_tablassirep", "idtabla='43' and idcodigo='" . $data["idorigenperj"] . "'", "descripcion");
            } else {
                if (trim($data["idorigenperj"]) != '') {
                    $txt .= 'otorgada por ' . $data["idorigenperj"];
                } else {
                    if (trim($data["origendocconst"]) != '') {
                        $txt .= 'otorgada por ' . $data["origendocconst"];
                    }
                }
            }
        } else {
            if (trim($data["origendocconst"]) != '') {
                $txt .= 'otorgada por ' . $data["origendocconst"];
            }
        }
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }
}

// Es pequeña empresa en los términos de la Ley 1429 de 2010
function armarCertificaPequenaEmpresaFormato2019($mysqli, $pdf, $data, $nameLog) {

    //
    return false;

    //
    if (
            $data["organizacion"] === '02' ||
            $data["organizacion"] === '12' ||
            $data["organizacion"] === '14' ||
            $data["categoria"] === '2' || $data["organizacion"] === '3'
    ) {
        return true;
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
        $mipyme = verificarTamanoMipymeFormato2019($mysqli, $data);
    }

    // Valida renovación
    if ($mipyme == 'si') {
        $mipyme = verificarRenovacionMipymeFormato2019($mysqli, $data);
    }

    if ($mipyme == 'si') {
        $txt = '<strong>CONDICIÓN PEQUEÑA EMPRESA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $txt = 'Que el matriculado tiene la condición de pequeña empresa de acuerdo con lo establecido ';
        $txt .= 'en el numeral 1 del artículo 2 de la ley 1429 de 2010.';
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    } else {
        $mipyme = 'si';
        $mipyme = verificarTamanoMipymeFormato2019($mysqli, $data);
        if ($mipyme == 'si') {
            $mipyme = verificarRenovacionMipymeFormato2019($mysqli, $data);
        }
        if ($mipyme == 'si') {
            $txt = '<strong>CONDICIÓN DE PEQUEÑA EMPRESA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $txt = 'Que el matriculado tiene la condición de pequeña empresa.';
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
        }
    }
}

// Es pequeña empresa JOVEN en los términos de la Ley 1780 DE 2016
function armarCertificaPequenaEmpresaJovenFormato2019($mysqli, $pdf, $data, $nameLog) {
    if (
            $data["organizacion"] == '02' ||
            $data["organizacion"] == '12' ||
            $data["organizacion"] == '14' ||
            $data["categoria"] == '2' || $data["categoria"] == '3'
    ) {
        return false;
    }

    //
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
        $mipyme = verificarTamanoMipymeFormato2019($mysqli, $data);
    }

    // Valida renovación
    if ($mipyme == 'si') {
        $mipyme = verificarRenovacionMipymeFormato2019($mysqli, $data);
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
        $txt = '<strong>PEQUEÑA EMPRESA JOVEN</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        if ($data["organizacion"] == '10' && $data["categoria"] == '1') {
            $txt = 'QUE EL INSCRITO TIENE LA CONDICIÓN DE PEQUEÑA EMPRESA JOVEN DE ACUERDO CON LO ESTABLECIDO EN EL ARTÍCULO 2 DE LA LEY 1780 DE 2016.';
        } else {
            $txt = 'QUE EL MATRICULADO TIENE LA CONDICIÓN DE PEQUEÑA EMPRESA JOVEN DE ACUERDO CON LO ESTABLECIDO EN EL ARTÍCULO 2 DE LA LEY 1780 DE 2016.';
        }
        $pdf->writeHTML('<strong>' . $txt . '</strong>', true, false, true, false, 'J');
        $pdf->Ln();
    }
}

function verificarTamanoMipymeFormato2019($mysqli, $data) {
    if ($data["fechamatricula"] == $data["fecharenovacion"]) {
        $fec = $data["fechamatricula"];
    } else {
        $fec = $data["fecharenovacion"];
    }
    $salmin = retornarSalarioMinimoActualMysqliApi($mysqli, substr($fec, 0, 4));
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

function verificarRenovacionMipymeFormato2019($mysqli, $data) {

    // Si se matriculo el mismo año del certificado
    if ($data["fechamatricula"] < '20160502') {
        return 'no';
    }

    /*
      if ($data["fechamatricula"] > '20191229') {
      return 'no';
      }
     */

    //
    $mipyme = 'no';

    // Si se matriculo el mismo año del certificado
    if (substr($data["fechamatricula"], 0, 4) == date("Y")) {
        return 'si';
    } else {
        if (date("Ymd") > $_SESSION["generales"]["fcorte"]) {
            if ($data["ultanoren"] == date("Y")) {
                if ($data["fecharenovacion"] <= $_SESSION["generales"]["fcorte"]) {
                    $mipyme = 'si';
                }
            }
        } else {
            $mipyme = 'si';
        }
        return $mipyme;
    }
}

function armarCertificaConstitucionFormato2019($mysqli, $pdf, $data) {

    //
    if ($data["organizacion"] == '01') {
        return true;
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $tieneapertura = 'no';
    if ($data["organizacion"] == '02' || $data["organizacion"] == '08' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        foreach ($data["inscripciones"] as $i) {

            // Grupo de actos de constitución
            if ($i["grupoacto"] == '005' || $i["grupoacto"] == '025') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $tieneapertura = 'si';
                }
            }
        }
    }
    if ($tieneapertura == 'si') {
        $txt = '<strong>APERTURA</strong>';
        if ($data["organizacion"] == '08' && $data["categoria"] == '1') {
            $txt = '<strong>APERTURA SUCURSAL DE SOCIEDAD EXTRANJERA</strong>';
        } else {
            if ($data["categoria"] == '2') {
                $txt = '<strong>APERTURA DE SUCURSAL </strong>';
            }
            if ($data["categoria"] == '3') {
                $txt = '<strong>APERTURA DE AGENCIA</strong>';
            }
        }

        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '005' || $i["grupoacto"] == '025') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $nat = '';
                    if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                        if ($data["categoria"] == '1') {
                            $nat = retornarRegistroMysqliApi($mysqli, 'mreg_clase_esadl_gen', "id='" . $data["clasegenesadl"] . "'", "descripcioncert");
                        }
                    }
                    if (isset($data["siglaenconstitucion"]) && $data["siglaenconstitucion"] == 'N') {
                        $sig = '';
                    } else {
                        $sig = $data["sigla"];
                    }
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"], $sig, $nat);

                    //
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }

    //
    $tienetransformacion = 'no';
    if ($data["organizacion"] == '02' || $data["organizacion"] == '08' || $data["categoria"] == '2' || $data["categoria"] == '3') {
        foreach ($data["inscripciones"] as $i) {

            // Grupo de actos de transformacion
            if ($i["grupoacto"] == '035') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $tienetransformacion = 'si';
                }
            }
        }
    }
    if ($tienetransformacion == 'si') {
        if ($tieneapertura == 'no') {
            $txt = '<strong>CONVERSION</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '035') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);

                    //
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }


    if ($data["organizacion"] != '02' && $data["organizacion"] != '08' && $data["categoria"] != '2' && $data["categoria"] != '3') {
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
                    if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
                    if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
        if ($tieneconstitucion == 'no') {
            armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-CONSTI-ANT', 'CONSTITUCIÓN', 'si');
        }

        //
        if ($tieneconstitucion == 'si') {
            $txt = '<strong>CONSTITUCIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '005') {
                    if ($i["lib"] != 'RM15') {
                        if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                            $i["crev"] = '0';
                        }
                        if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                            $nat = '';
                            if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                                if ($data["categoria"] == '1') {
                                    $nat = retornarRegistroMysqliApi($mysqli, 'mreg_clase_esadl_gen', "id='" . $data["clasegenesadl"] . "'", "descripcioncert");
                                }
                            }
                            if (isset($data["siglaenconstitucion"]) && $data["siglaenconstitucion"] == 'N') {
                                $sig = '';
                            } else {
                                $sig = $data["sigla"];
                            }
                            $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"], $sig, $nat);

                            //
                            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
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
            $txt = '<strong>ACLARATORIAS A LA CONSTITUCIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '060') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::limpiarTextosRedundantes($txt);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }

        // *************************************************************************** //
        // Certifica re-Constitución - Reconstitucion
        // *************************************************************************** //
        if ($tienereconstitucion == 'si') {
            $txt = '<strong>RECONSTITUCION</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '061') {
                    if ($i["lib"] != 'RM15') {
                        if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                            $i["crev"] = '0';
                        }
                        if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                            $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                            $txt = \funcionesGenerales::limpiarTextosRedundantes($txt);
                            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '.</span>', true, false, true, false);
                            $pdf->Ln();
                        }
                    }
                }
            }
        }
    }
}

function armarCertificaConstitucionReformasCasaPrincipalFormato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $tieneconstitucion = 'no';
    $tienereformas = 'no';
    foreach ($data["inscripciones"] as $i) {

        // Grupo de actos de constitución
        if ($i["grupoacto"] == '062') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                $tieneconstitucion = 'si';
            }
        }

        // Grupo de reformas
        if ($i["grupoacto"] == '063') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                $tienereformas = 'si';
            }
        }
    }

    //
    if ($tieneconstitucion == 'si' || $tienereformas == 'si') {
        $txt = '<strong>CONSTITUCIÓN Y REFORMAS CASA PRINCIPAL</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
    }

    //
    if ($tieneconstitucion == 'si') {
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '062') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $data["nombre"], $data["complementorazonsocial"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);

                    //
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }

    if ($tienereformas == 'si') {
        $txt = 'Los estatutos de la casa principal han sido reformados así:';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        $numeroreformas = 0;
        $txt = '';
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '063') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '8' && $ins["crev"] != '9') {
                    $numeroreformas++;
                    if ($numeroreformas == 1) {
                        $txt = '<table>';
                        $txt .= '<tr>';
                        $txt .= '<td width="60%"><strong>DOCUMENTO</strong></td>';
                        $txt .= '<td width="2%">&nbsp;</td>';
                        $txt .= '<td width="38%"><strong>INSCRIPCIÓN</strong></td>';
                        $txt .= '</tr>';
                    }
                    $txt .= '<tr>';

                    //
                    if (trim($ins["ndoc"]) == '' || trim($ins["ndoc"]) == '0' || strtoupper(trim($ins["ndoc"])) == 'NA' || strtoupper(trim($ins["ndoc"])) == 'N/A' || strtoupper(trim($ins["ndoc"])) == 'SN' || strtoupper(trim($ins["ndoc"])) == 'S/N') {
                        $ins["ndoc"] = '';
                    }

                    //
                    $ntd = '';
                    switch ($ins["tdoc"]) {
                        case "01":
                            $ntd = 'Acta';
                            break;
                        case "02":
                            $ntd = 'E.P.';
                            break;
                        case "03":
                            $ntd = 'Res.';
                            break;
                        case "04":
                            $ntd = 'Oficio';
                            break;
                        case "05":
                            $ntd = 'P.J.';
                            break;
                        case "06":
                            $ntd = 'D.P.';
                            break;
                        case "07":
                            $ntd = 'DM-';
                            break;
                        case "08":
                            $ntd = 'FO-';
                            break;
                        case "09":
                            $ntd = 'Dec.';
                            break;
                        case "10":
                            $ntd = 'Cert.';
                            break;
                        case "11":
                            $ntd = 'Auto';
                            break;
                        case "12":
                            $ntd = 'P.A.';
                            break;
                        case "13":
                            $ntd = 'C.C.';
                            break;
                        case "15":
                            $ntd = 'Ley';
                            break;
                        case "26":
                            $ntd = 'Acta Ac.';
                            break;
                        default:
                            $ntd = $ins["tdoc"];
                    }

                    $txt .= '<td width="55%">';
                    $txt .= '*) ' . $ntd;
                    if ($ins["ndoc"] != '') {
                        $txt .= ' No. ' . $ins["ndoc"];
                    }
                    $txt .= ' del ' . \funcionesGenerales::mostrarFechaLetras1($ins["fdoc"]);
                    $txto = '';
                    if ($ins["idoridoc"] != '' && $ins["idoridoc"] != '000000' && $ins["idoridoc"] != '999999') {
                        $txto = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='51' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion");
                        if ($txto == '') {
                            if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                                $ins["txoridoc"] = 'Órganos de administración';
                            }
                            $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper($ins["txoridoc"]))));
                        } else {
                            $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper(retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='51' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion")))));
                        }
                    } else {
                        if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                            $ins["txoridoc"] = 'Órganos de administración';
                        }
                        $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper($ins["txoridoc"]))));
                    }
                    if ($txto != '') {
                        $txt .= ' de la ' . $txto;
                    }
                    if ($ins["idmunidoc"] != '') {
                        if ($ins["tdoc"] == '02') {
                            $txt .= ' ' . retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $ins["idmunidoc"] . "'", "ciudadminusculas");
                        }
                    }
                    $txt .= '</td>';
                    $txt .= '<td width="2%">';
                    $txt .= '&nbsp;';
                    $txt .= '</td>';
                    $txt .= '<td width="43%">';
                    $txt .= $ins["nreg"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($ins["freg"]) . ' del libro ' . retornarLibroFormato2019($ins["lib"]);
                    $txt .= '</td>';
                    $txt .= '</tr>';
                }
            }
        }
        if ($numeroreformas != 0) {
            $txt .= '</table>';
        }
        $pdf->SetFont('courier', '', 8);
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    }
}

function armarCertificaEntidadVigilanciaFormato2019($mysqli, $pdf, $data) {
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $txtvig = '';
    if ($data["vigilanciasuperfinanciera"] == 'S') {
        $txtvig = 'SUPERINTENDENCIA FINANCIERA';
    } else {
        if ($data["ctresaivc"] != '' && !is_numeric($data["ctresaivc"])) {
            $txtvig = $data["ctresaivc"];
        } else {
            if (ltrim(trim($data["vigcontrol"]), "0") != '') {
                $txtvig = retornarNombreTablasSirepMysqliApi($mysqli, '43', $data["vigcontrol"]);
                if ($txtvig == '') {
                    $txtvig = trim($data["vigcontrol"]);
                }
            }
        }
    }
    if ($txtvig != '') {
        $txt = '<strong>ENTIDAD QUE EJERCE INSPECCIÓN, VIGILANCIA Y CONTROL</strong>';
        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->writeHTML($txtvig, true, false, true, false, 'l');
        $pdf->Ln();
    }
}

// Certifica situacion de control
function armarCertificaSitControlFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienesitcontrol = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '022' || $i["grupoacto"] == '023') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
        $txt = '<strong>SITUACIONES DE CONTROL Y GRUPOS EMPRESARIALES</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '022' || $i["grupoacto"] == '023') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesSitControlFormato2019($mysqli, $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $data["organizacion"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["tomo72"], $i["folio72"], $i["registro72"]);

                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
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
                                $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["muncom"] . "'");
                                $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($data["muncom"], 0, 2) . "'");
                                if ($tdep === false || empty($tdep)) {
                                    $tdep = array('nombre' => '');
                                }
                                if ($tmun && !empty($tmun)) {
                                    $txt .= 'Domicilio: ' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"] . '<br>';
                                }
                            }
                            if (trim($data["paicom"]) != '') {
                                $txt .= 'País: ' . retornarNombrePaisMysqliApi($mysqli, $data["paicom"]) . '<br>';
                            }
                            if (trim((string) $data["nacionalidad"]) != '') {
                                $txt .= 'Nacionalidad: ' . $data["nacionalidad"] . '<br>';
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
                                    // $tx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $v["identificacionotros"] . "' and (ctrestmatricula='MA' or ctrestmatricula='MI' or ctrestmatricula='II' or ctrestmatricula='IA')");
                                    $name = '';
                                    if (trim($v["nombre1otros"]) != '') {
                                        $name = trim($v["nombre1otros"]);
                                    }
                                    if (trim($v["nombre2otros"]) != '') {
                                        $name .= ' ' . trim($v["nombre2otros"]);
                                    }
                                    if (trim($v["apellido1otros"]) != '') {
                                        $name .= ' ' . trim($v["apellido1otros"]);
                                    }
                                    if (trim($v["apellido2otros"]) != '') {
                                        $name .= ' ' . trim($v["apellido2otros"]);
                                    }
                                    if ($name == '') {
                                        $name = trim($v["nombreotros"]);
                                    }
                                    $txt .= '<br><strong>** ' . $tipoSit . ' : </strong>' . $name . '<br>';
                                    $txt .= '<strong>' . $v["cargootros"] . '</strong><br>';
                                    if (trim($v["identificacionotros"]) != '' && $v["idtipoidentificacionotros"] != '' && $v["idtipoidentificacionotros"] != '0' && $v["idtipoidentificacionotros"] != '7') {
                                        $txt .= 'Identificación: ' . $v["identificacionotros"] . '<br>';
                                    } else {
                                        $txt .= 'Identificación: NO REPORTADA<br>';
                                    }
                                    if (trim((string) $v["nacionalidad"]) != '') {
                                        $txt .= 'Nacionalidad: ' . $v["nacionalidad"] . '<br>';
                                    } else {
                                        if ($v["idtipoidentificacionotros"] == '1' || $v["idtipoidentificacionotros"] == '4' || $v["idtipoidentificacionotros"] == 'R') {
                                            $txt .= 'Nacionalidad: Colombiano/a<br>';
                                        }
                                        if ($v["idtipoidentificacionotros"] == 'V' || $v["idtipoidentificacionotros"] == 'P') {
                                            $txt .= 'Nacionalidad: Venezolano/a<br>';
                                        }
                                    }
                                    if (trim($v["municipiootros"]) != '') {
                                        if ($v["municipiootros"] == '00000' || $v["municipiootros"] == '99999') {
                                            $txt .= 'Domicilio: ' . 'Fuera del país' . '<br>';
                                        } else {
                                            $txt .= 'Domicilio: ' . $v["municipiootros"] . ' - ' . ucwords(mb_strtolower(retornarNombreMunicipioMysqliApi($mysqli, $v["municipiootros"]))) . '<br>';
                                        }
                                    }
                                    if (trim($v["direccionotros"]) != '') {
                                        $txt .= 'Dirección : ' . \funcionesGenerales::parsearOracion($v["direccionotros"]) . '<br>';
                                    }
                                    if (trim($v["paisotros"]) != '') {
                                        $tpai = retornarRegistroMysqliApi($mysqli, 'bas_paises', "idpais='" . $v["paisotros"] . "'", "nombrepais");
                                        if ($tpai == '') {
                                            $tpai = retornarRegistroMysqliApi($mysqli, 'bas_paises', "codnumpais='" . $v["paisotros"] . "'", "nombrepais");
                                        }
                                        if ($tpai == '') {
                                            $tpai = 'Colombia';
                                        }
                                        $txt .= 'País: ' . $tpai . '<br>';
                                    }
                                    if (trim($v["ciiu1"]) != '') {
                                        $txt .= 'CIIU : ' . $v["ciiu1"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $v["ciiu1"]) . '<br>';
                                    }
                                    if ($v["ciiu2"] != '') {
                                        $txt .= 'CIIU : ' . $v["ciiu2"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $v["ciiu2"]) . '<br>';
                                    }
                                    if ($v["ciiu3"] != '') {
                                        $txt .= 'CIIU : ' . $v["ciiu3"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $v["ciiu3"]) . '<br>';
                                    }
                                    if ($v["ciiu4"] != '') {
                                        $txt .= 'CIIU : ' . $v["ciiu4"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $v["ciiu4"]) . '<br>';
                                    }
                                    if ($v["desactiv"] != '') {
                                        $txt .= 'Descripción de la actividad: ' . $v["desactiv"] . '<br>';
                                    }
                                    // }
                                    if ($v["fechaconfiguracion"] != '') {
                                        $txt .= 'Fecha de configuración de la situación: ' . \funcionesGenerales::mostrarFecha2($v["fechaconfiguracion"]) . '<br>';
                                    }
                                    if ($v["fechavencimiento"] != '') {
                                        $txt .= 'Fecha de vencimiento de la situación: ' . \funcionesGenerales::mostrarFecha2($v["fechavencimiento"]) . '<br>';
                                    }
                                    if ($v["tipositcontrol"] != '') {
                                        switch ($v["tipositcontrol"]) {
                                            case "1" :
                                                $txt .= 'Presupuesto que da lugar a la situación de control y/o al grupo empresarial: "Numeral 1 Artículo 261 Código de Comercio".<br>';
                                                break;
                                            case "2" :
                                                $txt .= 'Presupuesto que da lugar a la situación de control y/o al grupo empresarial: "Numeral 2 Artículo 261 Código de Comercio"<br>';
                                                break;
                                            case "3" :
                                                $txt .= 'Presupuesto que da lugar a la situación de control y/o al grupo empresarial: "Numeral 3 Artículo 261 Código de Comercio"<br>';
                                                break;
                                            case "4" :
                                                $txt .= 'Presupuesto que da lugar a la situación de control y/o al grupo empresarial: "Artículo 2.2.2.41.6.1 Decreto 1074 de 2015 adicionado por el artículo 1 Decreto 667 de 2018"<br>';
                                                break;
                                        }
                                    }

                                    if (isset($v["tipositcontroltexto"]) && $v["tipositcontroltexto"] != '') {
                                        $txt .= $v["tipositcontroltexto"] . '<br>';
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
                            $name = '';
                            if (trim($data["nom1"]) != '') {
                                $name = trim($data["nom1"]);
                            }
                            if (trim($data["nom2"]) != '') {
                                $name .= ' ' . trim($data["nom1"]);
                            }
                            if (trim($data["ape1"]) != '') {
                                $name .= ' ' . trim($data["ape1"]);
                            }
                            if (trim($data["ape2"]) != '') {
                                $name .= ' ' . trim($data["ape2"]);
                            }
                            if ($name == '') {
                                $name = trim($data["nombre"]);
                            }
                            $txt .= '<br><strong>** ' . $tipoSit . ' : </strong>' . $name . '<br>';

                            if (trim($data["muncom"]) != '') {
                                $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["muncom"] . "'");
                                $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($data["muncom"], 0, 2) . "'");
                                if ($tdep === false || empty($tdep)) {
                                    $tdep = array('nombre' => '');
                                }
                                if ($tmun && !empty($tmun)) {
                                    $txt .= 'Domicilio: ' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"] . '<br>';
                                }
                            }
                            if (trim($data["paicom"]) != '') {
                                $txt .= 'País: ' . retornarNombrePaisMysqliApi($mysqli, $data["paicom"]) . '<br>';
                            }
                            if (trim($data["nacionalidad"]) != '') {
                                $txt .= 'Nacionalidad: ' . $data["nacionalidad"] . '<br>';
                            }
                        }
                    }

                    // Imprime la inscripcion
                    // $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();

                    // Imprime aclaratoria si la tiene.
                    if (trim((string) $i["aclaratoria"]) != '') {
                        $x = str_replace(chr(13) . chr(10), '<br>', $i["aclaratoria"]);
                        $pdf->writeHTML($x, true, false, true, false, 'J');
                        $pdf->Ln();
                    }
                }
            }
        }
    }
    if ($tienesitcontrol == 'no') {
        $resx = armarCertificaTextoLibreClaseWriteHtmlFormato2019($mysqli, $pdf, $data, 'CRT-SITCONTROL', 'SITUACIONES DE CONTROL Y GRUPOS EMPRESARIALES');
        $resx = armarCertificaTextoLibreClaseWriteHtmlFormato2019($mysqli, $pdf, $data, 'AC-SITCONTROL', 'ACLARACION A SITUACIONES DE CONTROL Y GRUPOS EMPRESARIALES');
        return $resx;
    } else {
        $resx = armarCertificaTextoLibreClaseWriteHtmlFormato2019($mysqli, $pdf, $data, 'AC-SITCONTROL', 'ACLARACION A SITUACIONES DE CONTROL Y GRUPOS EMPRESARIALES');
        return true;
    }
}

// Certifica Constitución - Acto 0042
function armarCertificaConstitucionCambioDomicilioFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneconstitucion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '024') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    } else {
        // 2017.11.19: JINT: Grupo textual  constitución por cambio de domicilio CRT-CAMDOM
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-CAMDOM', 'CERTIFICA - CAMBIOS DE DOMICILIO');
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
        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
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
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
                $tiene0710 = 'si';
            }
        }
    }
}

// Certifica Cambios de domicilio
function armarCertificaCambiosDomicilioFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienecb = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '059') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica Cambios de domicilio - Certificacion
function armarCertificaCambiosDomicilioCertificacionesFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tienecb = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '074') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $data["nomant"], '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica capitales SAT - en texto certifica 9001
function armarCertificaCapitalSatFormato2019($mysqli, $pdf, $data) {
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-CAPSAT', 'CERTIFICA - CAPITAL SOCIEDADES AGRARIAS DE TRANSOFRMACIÓN');
}

// Lista de reformas
function armarCertificaListaReformasFormato019($mysqli, $pdf, $data) {

    if ($pdf->tipocertificado != 'CerExi' && $pdf->tipocertificado != 'CerEsadl') {
        return false;
    }

    if ($data["categoria"] != '1') {
        return false;
    }

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
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REFORMAS-HTML', 'REFORMAS A LOS ESTATUTOS (LISTA) ', $mysqli);

    // Certifca de reformas textual
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REFORMAS', 'REFORMAS A LOS ESTATUTOS (TEXTO)', $mysqli);
    $tieneactosreforma = 'no';
    $numeroreformas = 0;

    // Lista de reformas en actos
    $txt = '';
    foreach ($data["inscripciones"] as $ins) {
        if ($ins["esreforma"] == 'S') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1' && $ins["crev"] != '8' && $ins["crev"] != '9') {
                $tieneactosreforma = 'si';
                $numeroreformas++;
                if ($numeroreformas == 1) {
                    if ($data["claseespesadl"] == '61') {
                        $pdf->writeHTML('<strong>REFORMAS FACULTADES DEL APODERADO</strong>', true, false, true, false, 'C');
                        $pdf->Ln();
                        $pdf->writeHTML('Las facultades del apoderado han sido reformadas así:', true, false, true, false, 'L');
                        $pdf->Ln();
                    } else {
                        $pdf->writeHTML('<strong>REFORMAS DE ESTATUTOS</strong>', true, false, true, false, 'C');
                        $pdf->Ln();
                        $pdf->writeHTML('Los estatutos de la sociedad han sido reformados así:', true, false, true, false, 'L');
                        $pdf->Ln();
                    }

                    $txt = '<table>';
                    $txt .= '<tr>';
                    $txt .= '<td width="60%"><strong>DOCUMENTO</strong></td>';
                    $txt .= '<td width="2%">&nbsp;</td>';
                    $txt .= '<td width="38%"><strong>INSCRIPCIÓN</strong></td>';
                    $txt .= '</tr>';
                }
                $txt .= '<tr>';

                if (trim($ins["ndoc"]) == '' || strtoupper(trim($ins["ndoc"])) == 'NA' || strtoupper(trim($ins["ndoc"])) == 'N/A' || strtoupper(trim($ins["ndoc"])) == 'SN' || strtoupper(trim($ins["ndoc"])) == 'S/N') {
                    $ins["ndoc"] = '';
                }

                //
                $ntd = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $ins["tdoc"] . "'", "corto");
                if ($ntd == '') {
                    switch ($ins["tdoc"]) {
                        case "01":
                            $ntd = 'Acta';
                            break;
                        case "02":
                            $ntd = 'E.P.';
                            break;
                        case "03":
                            $ntd = 'Res.';
                            break;
                        case "04":
                            $ntd = 'Oficio';
                            break;
                        case "05":
                            $ntd = 'P.J.';
                            break;
                        case "06":
                            $ntd = 'D.P.';
                            break;
                        case "07":
                            $ntd = 'DM-';
                            break;
                        case "08":
                            $ntd = 'FO-';
                            break;
                        case "09":
                            $ntd = 'Dec.';
                            break;
                        case "10":
                            $ntd = 'Cert.';
                            break;
                        case "11":
                            $ntd = 'Auto';
                            break;
                        case "12":
                            $ntd = 'P.A.';
                            break;
                        case "13":
                            $ntd = 'C.C.';
                            break;
                        case "15":
                            $ntd = 'B.G.';
                            break;
                        case "25":
                            $ntd = 'Aviso';
                            break;
                        case "26":
                            $ntd = 'Acta Ac.';
                            break;
                        case "38":
                            $ntd = 'Ley 1727';
                            break;

                        default:
                            $ntd = $ins["tdoc"];
                    }
                }
                $txt .= '<td width="55%">';
                $txt .= '*) ' . $ntd;
                if ($ins["ndoc"] != '') {
                    $txt .= ' No. ' . $ins["ndoc"];
                }
                $txt .= ' del ' . \funcionesGenerales::mostrarFechaLetras1($ins["fdoc"]);
                $txto = '';
                if ($ins["idoridoc"] != '' && $ins["idoridoc"] != '000000' && $ins["idoridoc"] != '999999') {
                    $txto = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='51' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion");
                    if ($txto == '') {
                        if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                            $ins["txoridoc"] = 'Órganos de administración';
                        }
                        $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper($ins["txoridoc"]))));
                    } else {
                        $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper(retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='51' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion")))));
                    }
                } else {
                    if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                        $ins["txoridoc"] = 'Órganos de administración';
                    }
                    $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper($ins["txoridoc"]))));
                }
                if ($txto != '') {
                    $txt .= ' de la ' . $txto;
                }
                if ($ins["idmunidoc"] != '') {
                    if ($ins["tdoc"] == '02') {
                        $txt .= ' ' . retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $ins["idmunidoc"] . "'", "ciudadminusculas");
                    }
                }
                $txt .= '</td>';
                $txt .= '<td width="2%">';
                $txt .= '&nbsp;';
                $txt .= '</td>';
                $txt .= '<td width="43%">';
                if ($ins["tomo72"] != '') {
                    $txt .= $ins["registro72"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($ins["freg"]) . ' del tomo ' . $ins["tomo72"] . ', folio ' . $ins["folio72"];
                } else {
                    $txt .= $ins["nreg"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($ins["freg"]) . ' del libro ' . retornarLibroFormato2019($ins["lib"]);
                }
                $txt .= '</td>';
                $txt .= '</tr>';
            }
        }
    }
    if ($numeroreformas != 0) {
        $txt .= '</table>';
    }
    $pdf->SetFont('courier', '', 8);
    $pdf->writeHTML($txt, true, false, true, false, 'J');
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($numeroreformas == 0) {
        // Certifca de reformas textual (lista)
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REFORMAS-LISTA', 'CERTIFICA - REFORMAS', $mysqli);
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
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-REFORMAS', 'CERTIFICA', $mysqli);
}

//
function armarCertificaListaReformasCasaPrincipalFormato2019($mysqli, $pdf, $data) {

    if ($pdf->tipocertificado != 'CerMat') {
        return false;
    }

    //
    if ($data["categoria"] != '1') {
        return false;
    }

    // Lista de reformas en actos
    $numeroreformas = 0;
    $txt = '';
    foreach ($data["inscripciones"] as $ins) {
        if ($ins["esreforma"] == 'S') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1' && $ins["crev"] != '8' && $ins["crev"] != '9') {
                $tieneactosreforma = 'si';
                $numeroreformas++;
                if ($numeroreformas == 1) {
                    $pdf->writeHTML('<strong>REFORMAS DE LA SUCURSAL</strong>', true, false, true, false, 'C');
                    $pdf->Ln();

                    $pdf->writeHTML('El acto de apertura de la sucursal ha sido reformado así: ', true, false, true, false, 'L');
                    $pdf->Ln();

                    $txt = '<table>';
                    $txt .= '<tr>';
                    $txt .= '<td width="60%"><strong>DOCUMENTO</strong></td>';
                    $txt .= '<td width="2%">&nbsp;</td>';
                    $txt .= '<td width="38%"><strong>INSCRIPCIÓN</strong></td>';
                    $txt .= '</tr>';
                }
                $txt .= '<tr>';

                //
                if (trim($ins["ndoc"]) == '' || strtoupper(trim($ins["ndoc"])) == 'NA' || strtoupper(trim($ins["ndoc"])) == 'N/A' || strtoupper(trim($ins["ndoc"])) == 'SN' || strtoupper(trim($ins["ndoc"])) == 'S/N') {
                    $ins["ndoc"] = '';
                }

                //
                $ntd = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $ins["tdoc"] . "'", "corto");
                if ($ntd == '') {
                    switch ($ins["tdoc"]) {
                        case "01":
                            $ntd = 'Acta';
                            break;
                        case "02":
                            $ntd = 'E.P.';
                            break;
                        case "03":
                            $ntd = 'Res.';
                            break;
                        case "04":
                            $ntd = 'Oficio';
                            break;
                        case "05":
                            $ntd = 'P.J.';
                            break;
                        case "06":
                            $ntd = 'D.P.';
                            break;
                        case "07":
                            $ntd = 'DM-';
                            break;
                        case "08":
                            $ntd = 'FO-';
                            break;
                        case "09":
                            $ntd = 'Dec.';
                            break;
                        case "10":
                            $ntd = 'Cert.';
                            break;
                        case "11":
                            $ntd = 'Auto';
                            break;
                        case "12":
                            $ntd = 'P.A.';
                            break;
                        case "13":
                            $ntd = 'C.C.';
                            break;
                        case "15":
                            $ntd = 'B.G.';
                            break;
                        case "25":
                            $ntd = 'Aviso';
                            break;
                        case "26":
                            $ntd = 'Acta Ac.';
                            break;
                        case "38":
                            $ntd = 'Ley 1727';
                            break;

                        default:
                            $ntd = $ins["tdoc"];
                    }
                }
                $txt .= '<td width="55%">';
                $txt .= '*) ' . $ntd;
                if ($ins["ndoc"] != '') {
                    $txt .= ' No. ' . $ins["ndoc"];
                }
                $txt .= ' del ' . \funcionesGenerales::mostrarFechaLetras1($ins["fdoc"]);
                $txto = '';
                if ($ins["idoridoc"] != '' && $ins["idoridoc"] != '000000' && $ins["idoridoc"] != '999999') {
                    $txto = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='51' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion");
                    if ($txto == '') {
                        if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                            $ins["txoridoc"] = 'Órganos de administración';
                        }
                        $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper($ins["txoridoc"]))));
                    } else {
                        $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper(retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='51' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion")))));
                    }
                } else {
                    if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                        $ins["txoridoc"] = 'Órganos de administración';
                    }
                    $txto = ucwords(strtolower(str_replace("NOTARIAS NOTARIA", "NOTARÍA", strtoupper($ins["txoridoc"]))));
                }
                if ($txto != '') {
                    $txt .= ' de la ' . $txto;
                }
                if ($ins["idmunidoc"] != '') {
                    if ($ins["tdoc"] == '02') {
                        $txt .= ' ' . retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $ins["idmunidoc"] . "'", "ciudadminusculas");
                    }
                }
                $txt .= '</td>';
                $txt .= '<td width="2%">';
                $txt .= '&nbsp;';
                $txt .= '</td>';
                $txt .= '<td width="43%">';
                if ($ins["tomo72"] != '') {
                    $txt .= $ins["registro72"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($ins["freg"]) . ' del tomo ' . $ins["tomo72"] . ', folio ' . $ins["folio72"];
                } else {
                    $txt .= $ins["nreg"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($ins["freg"]) . ' del libro ' . retornarLibroFormato2019($ins["lib"]);
                }
                $txt .= '</td>';
                $txt .= '</tr>';
            }
        }
    }
    if ($numeroreformas != 0) {
        $txt .= '</table>';
    }
    $pdf->SetFont('courier', '', 8);
    $pdf->writeHTML($txt, true, false, true, false, 'J');
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
}

// Certifica Reformas - Lista
// Textual: Grupo CRT-REFORMAS
// Actos: Que el campo esreforma = 'si'
// Textual: AC-REFORMAS
function armarCertificaReformasFormato2019($mysqli, $pdf, $data) {

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

    // Certifca de reformas textual
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REFORMAS', 'CERTIFICA - REFORMAS');
    $tieneactosreforma = 'no';
    $numeroreformas = 0;

    // Lista de reformas en actos
    $txt = '';
    foreach ($data["inscripciones"] as $ins) {
        if ($ins["esreforma"] == 'S') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1' && $ins["crev"] != '8' && $ins["crev"] != '9') {
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
                if (trim($ins["ndoc"]) == '' || strtoupper(trim($ins["ndoc"])) == 'NA' || strtoupper(trim($ins["ndoc"])) == 'N/A' || strtoupper(trim($ins["ndoc"])) == 'SN' || strtoupper(trim($ins["ndoc"])) == 'S/N') {
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
                    $txto = retornarNombreTablasSirepMysqliApi($mysqli, '51', $ins["idoridoc"]);
                    if ($txto == '') {
                        if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                            $ins["txoridoc"] = 'ORGANOS DE ADMINISTRACION';
                        }
                        $txt .= '<td width="23%">' . str_replace("NOTARIAS NOTARIA", "NOTARIA", strtoupper($ins["txoridoc"])) . '</td>';
                    } else {
                        $txt .= '<td width="23%">' . retornarNombreTablasSirepMysqliApi($mysqli, '51', $ins["idoridoc"]) . '</td>';
                    }
                } else {
                    if (strtoupper(trim($ins["txoridoc"])) == 'NO TIENE NO TIENE') {
                        $ins["txoridoc"] = 'ORGANOS DE ADMINISTRACION';
                    }
                    $txt .= '<td width="23%">' . str_replace("NOTARIAS NOTARIA", "NOTARIA", strtoupper($ins["txoridoc"])) . '</td>';
                }
                $txt .= '<td width="2%">&nbsp;</td>';
                $txt .= '<td width="10%">' . substr(retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"]), 0, 15) . '</td>';
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
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REFORMAS-LISTA', 'CERTIFICA - REFORMAS');
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
    armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-REFORMAS', 'CERTIFICA');
}

// Certifica de reformas especiales
function armarCertificaReformasEspecialesFormato2019($mysqli, $pdf, $data, $nameLog = '') {
    if ($data["organizacion"] == '01' ||
            $data["organizacion"] == '02' ||
            ($data["categoria"] == '2' && $data["organizacion"] != '08') ||
            ($data["categoria"] == '3' && $data["organizacion"] != '08')) {
        return false;
    }

    $cantref = 0;
    foreach ($data["inscripciones"] as $dtx) {
        if (trim((string) $dtx["esreformaespecial"]) == '') {
            $dtx["esreformaespecial"] = 'N';
        }
        \logApi::general2($nameLog, $data["matricula"], 'Reformas especiales : ' . $dtx["lib"] . '-' . $dtx["nreg"] . '-' . $dtx["acto"] . '-' . $dtx["esreformaespecial"]);
        if (trim($dtx["esreformaespecial"]) == 'S') {
            if ($dtx["crev"] != '8' && $dtx["crev"] != '9') {
                $cantref++;
                if ($cantref == 1) {
                    $txt = '<strong>REFORMAS ESPECIALES</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                $txt1 = descripcionesFormato2019(
                        $mysqli,
                        $data["organizacion"],
                        $dtx["acto"],
                        $dtx["tdoc"],
                        $dtx["ndoc"],
                        $dtx["ndocext"],
                        $dtx["fdoc"],
                        $dtx["idoridoc"],
                        $dtx["txoridoc"],
                        $dtx["idmunidoc"],
                        $dtx["lib"],
                        $dtx["nreg"],
                        $dtx["freg"],
                        $dtx["not"],
                        $data["nomant"],
                        $data["nombre"],
                        $data["complementorazonsocial"],
                        $dtx["camant"],
                        $dtx["libant"],
                        $dtx["regant"],
                        $dtx["fecant"],
                        $dtx["camant2"],
                        $dtx["libant2"],
                        $dtx["regant2"],
                        $dtx["fecant2"],
                        $dtx["camant3"],
                        $dtx["libant3"],
                        $dtx["regant3"],
                        $dtx["fecant3"],
                        $dtx["camant4"],
                        $dtx["libant4"],
                        $dtx["regant4"],
                        $dtx["fecant4"],
                        $dtx["camant5"],
                        $dtx["libant5"],
                        $dtx["regant5"],
                        $dtx["fecant5"],
                        $dtx["aclaratoria"],
                        $dtx["tomo72"],
                        $dtx["folio72"],
                        $dtx["registro72"]
                );

                //
                $txt = \funcionesGenerales::limpiarTextosRedundantes(\funcionesGenerales::parsearOracion($txt1));
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
    }
}

// Certifica sitios web
function armarCertificaSitiosWebFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $siurl = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '046') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica de recursos de reposición
function armarCertificaRecursosReposicionFormato2019($mysqli, $pdf, $data) {

    if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'MG' || $data["estadomatricula"] == 'IC' || $data["estadomatricula"] == 'IF' || $data["estadomatricula"] == 'IG') {
        return false;
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    // if (isset($data["rr"]) && !empty($data["rr"])) {
    if (!defined('HABIL_SABADO') || trim(HABIL_SABADO) == '') {
        $habilsabado = 'NO';
    } else {
        $habilsabado = HABIL_SABADO;
    }

    //
    $txt = '<strong>RECURSOS CONTRA LOS ACTOS DE INSCRIPCIÓN</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
    $txt = 'De conformidad con lo establecido en el Código de Procedimiento Administrativo y de lo Contencioso Administrativo ';
    $txt .= 'y la Ley 962 de 2005, los Actos Administrativos de registro quedan en firme, dentro de los diez (10) días hábiles ';
    $txt .= 'siguientes a la fecha de inscripción, siempre que no sean objeto de recursos. Para estos efectos, se informa que ';
    $txt .= 'para la ' . str_replace("CAMARA", "CÁMARA", RAZONSOCIAL) . ', los sábados <strong>' . $habilsabado . '</strong> son días hábiles.<br><br>';
    $txt .= 'Una vez interpuestos los recursos, los Actos Administrativos recurridos quedan en efecto suspensivo, hasta tanto ';
    $txt .= 'los mismos sean resueltos, conforme lo prevé el artículo 79 del Código de Procedimiento Administrativo y de lo ';
    $txt .= 'Contencioso Administrativo.<br><br>';
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $txt = '';
    if (isset($data["rr"]) && !empty($data["rr"])) {
        $txt .= 'A la fecha, se encuentran en curso los siguientes recursos contra actos de inscripción: <br>';
        foreach ($data["rr"] as $r) {
            $insrr1 = false;
            $insrr2 = false;
            $insrr3 = false;
            $insrr4 = false;
            foreach ($data["inscripciones"] as $ins1) {
                if ($ins1["lib"] == $r["libroafectado"] && $ins1["nreg"] == $r["registroafectado"] && $ins1["dupli"] == $r["dupliafectado"]) {
                    $insrr1 = $ins1;
                }
                if ($r["libroafectado2"] != '') {
                    if ($ins1["lib"] == $r["libroafectado2"] && $ins1["nreg"] == $r["registroafectado2"] && $ins1["dupli"] == $r["dupliafectado2"]) {
                        $insrr2 = $ins1;
                    }
                }
                if ($r["libroafectado3"] != '') {
                    if ($ins1["lib"] == $r["libroafectado3"] && $ins1["nreg"] == $r["registroafectado3"] && $ins1["dupli"] == $r["dupliafectado3"]) {
                        $insrr3 = $ins1;
                    }
                }
                if ($r["libroafectado4"] != '') {
                    if ($ins1["lib"] == $r["libroafectado4"] && $ins1["nreg"] == $r["registroafectado4"] && $ins1["dupli"] == $r["dupliafectado4"]) {
                        $insrr4 = $ins1;
                    }
                }
            }


            $subsidioapelacion = '';
            if (isset($r["subsidioapelacion"]) && $r["subsidioapelacion"] == 'S') {
                $subsidioapelacion = ' y en subsidio de apelación';
            }

            $numrecurrentes = 1;
            $recurrentes = $r["nombrerecurrente"];
            if (trim($r["nombrerecurrente2"]) != '') {
                $recurrentes .= ', ' . $r["nombrerecurrente2"];
                $numrecurrentes++;
            }
            if (trim($r["nombrerecurrente3"]) != '') {
                $recurrentes .= ', ' . $r["nombrerecurrente3"];
                $numrecurrentes++;
            }


            $txt .= '<br>El ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharadicacion"]) . ', ' . $recurrentes;
            if ($numrecurrentes === 1) {
                $txt .= ' interpuso ';
            } else {
                $txt .= ' interpusieron ';
            }

            if (isset($r["soloapelacion"]) && $r["soloapelacion"] == 'S') {
                $txt .= 'recurso de apelación contra el Acto Administrativo No. ' . $r["registroafectado"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado"]);
            } else {
                $txt .= 'recurso de reposición' . $subsidioapelacion . ' contra el Acto Administrativo No. ' . $r["registroafectado"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado"]);
            }
            if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
                $txt .= ' del libro ' . retornarLibroFormato2019($r["libroafectado"]) . ', ';
            } else {
                $txt .= ' del libro ' . retornarLibroFormato2019($r["libroafectado"]) . ' del Registro Mercantil, ';
            }

            if ($insrr1) {
                $txt .= 'correspondiente a la inscripción de ';
                $txt .= descripcionesDocumentoFormato2019($mysqli, $data["organizacion"], $insrr1["acto"], $insrr1["tdoc"], $insrr1["ndoc"], $insrr1["ndocext"], $insrr1["fdoc"], $insrr1["idoridoc"], $insrr1["txoridoc"], $insrr1["idmunidoc"]) . ', ';
            }
            $txt .= 'la cual se refiere a ' . $r["noticiarecurrida"];

            if ($insrr2 !== false) {
                if (isset($r["soloapelacion"]) && $r["soloapelacion"] == 'S') {
                    $txt .= ', y recurso de apelación contra el Acto Administrativo No. ' . $r["registroafectado2"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado2"]);
                } else {
                    $txt .= ', y recurso de reposición' . $subsidioapelacion . ' contra el Acto Administrativo No. ' . $r["registroafectado2"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado2"]);
                }
                if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
                    $txt .= ' del libro ' . retornarLibroFormato2019($r["libroafectado2"]) . ', ';
                } else {
                    $txt .= ' del libro ' . retornarLibroFormato2019($r["libroafectado2"]) . ' del Registro Mercantil, ';
                }
                $txt .= 'correspondiente a la inscripción de ';
                $txt .= descripcionesDocumentoFormato2019($mysqli, $data["organizacion"], $insrr2["acto"], $insrr2["tdoc"], $insrr2["ndoc"], $insrr2["ndocext"], $insrr2["fdoc"], $insrr2["idoridoc"], $insrr2["txoridoc"], $insrr2["idmunidoc"]) . ', ';
                $txt .= 'la cual se refiere a ' . $r["noticiarecurrida2"];
            }

            if ($insrr3 !== false) {
                if (isset($r["soloapelacion"]) && $r["soloapelacion"] == 'S') {
                    $txt .= ', y recurso de apelación contra el Acto Administrativo No. ' . $r["registroafectado3"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado3"]);
                } else {
                    $txt .= ', y recurso de reposición' . $subsidioapelacion . ' contra el Acto Administrativo No. ' . $r["registroafectado3"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado3"]);
                }
                if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
                    $txt .= ' del libro ' . retornarLibroFormato2019($r["libroafectado3"]) . ', ';
                } else {
                    $txt .= ' del libro ' . retornarLibroFormato2019($r["libroafectado3"]) . ' del Registro Mercantil, ';
                }
                $txt .= 'correspondiente a la inscripción de ';
                $txt .= descripcionesDocumentoFormato2019($mysqli, $data["organizacion"], $insrr3["acto"], $insrr3["tdoc"], $insrr3["ndoc"], $insrr3["ndocext"], $insrr3["fdoc"], $insrr3["idoridoc"], $insrr3["txoridoc"], $insrr3["idmunidoc"]) . ', ';
                $txt .= 'la cual se refiere a ' . $r["noticiarecurrida3"];
            }

            if ($insrr4 !== false) {
                if (isset($r["soloapelacion"]) && $r["soloapelacion"] == 'S') {
                    $txt .= ', y recurso de apelación contra el Acto Administrativo No. ' . $r["registroafectado4"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado4"]);
                } else {
                    $txt .= ', y recurso de reposición' . $subsidioapelacion . ' contra el Acto Administrativo No. ' . $r["registroafectado4"] . ' del ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharegistroafectado4"]);
                }
                if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
                    $txt .= ' del libro ' . retornarLibroFormato2019($r["libroafectado4"]) . ', ';
                } else {
                    $txt .= ' del libro ' . retornarLibroFormato2019($r["libroafectado4"]) . ' del Registro Mercantil, ';
                }
                $txt .= 'correspondiente a la inscripción de ';
                $txt .= descripcionesDocumentoFormato2019($mysqli, $data["organizacion"], $insrr4["acto"], $insrr4["tdoc"], $insrr4["ndoc"], $insrr4["ndocext"], $insrr4["fdoc"], $insrr4["idoridoc"], $insrr4["txoridoc"], $insrr4["idmunidoc"]) . ', ';
                $txt .= 'la cual se refiere a ' . $r["noticiarecurrida4"];
            }
            $txt .= '. ';
            $txt .= 'Por lo anterior, la(s) inscripción(es) recurrida(s) se encuentra(n) bajo el efecto suspensivo previsto en el artículo 79 ';
            $txt .= 'del Código de Procedimiento Administrativo y de lo Contencioso Administrativo.<br>';

            if ($r["confirmainscripcion"] == 'C' && $r["subsidioapelacion"] == 'S') {
                $txt .= '<br>';
                $txt .= 'Mediante resolución No. ' . $r["numeroresolucion"] . ' de ' . \funcionesGenerales::mostrarFechaLetras1($r["fecharesolucion"]) . ' ';
                $txt .= 'esta Cámara de Comercio resolvió el anterior recurso, ';
                $txt .= 'confirmó la(s) inscripción(es) y concedió ante la Superintendencia de Sociedades el recurso de apelación interpuesto. ';
                $txt .= 'Por lo anterior, la(s) inscripción(es) recurrida(s) continúa(n) bajo el efecto ';
                $txt .= 'suspensivo previsto en el artículo 79 del Código de Procedimiento Administrativo y de lo Contencioso Administrativo.<br>';
            }
        }
        if ($txt != '') {
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        }
        $existe = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REQ-REPOSICION', '', 'si');
        $existe = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REQ-APELACION', 'RECURSO DE APELACION', 'si');
        $existe = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REQ-QUEJA', 'RECURSO DE QUEJA', 'si');
    } else {
        if (existeCertifica($data, 'CRT-REQ-QUEJA') || existeCertifica($data, 'CRT-REQ-APELACION') || existeCertifica($data, 'CRT-REQ-REPOSICION')) {
            $txt = 'A la fecha, se encuentran en curso los siguientes recursos contra actos de inscripción: <br>';
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $existe = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REQ-REPOSICION', 'RECURSO DE REPOSICIÓN', 'si');
            $existe = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REQ-APELACION', 'RECURSO DE APELACION', 'si');
            $existe = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-REQ-QUEJA', 'RECURSO DE QUEJA', 'si');
        } else {
            $txt = 'A la fecha y hora de expedición de este certificado, NO se encuentra en curso ningún recurso.<br>';
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        }
    }
    $pdf->Ln();
}

function existeCertifica($data, $clase) {
    $existe = false;
    foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
        if ($dtax["clase"] == $clase) {
            if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                $existe = true;
            } else {
                if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                    $existe = true;
                }
            }
        }
    }
    return $existe;
}

// Certifica prohibiciones
function armarCertificaProhibicionesFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $siurl = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '051') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
        $txt = '<strong>PROHIBICIONES DE ENAJENACION</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '051') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                $incact = 'si';
                if ($i["crev"] == '1' || $i["crev"] == '8' || $i["crev"] == '9') {
                    $incact = 'no';
                } else {
                    if ($i["fechalimite"] != '') {
                        if ($i["fechalimite"] < date("Ymd")) {
                            $incact = 'no';
                        }
                    }
                }
                if ($incact == 'si') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica prohibiciones
function armarCertificaInhabilidadesFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $siurl = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '020') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
        $txt = '<strong>INHABILIDADES</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '020') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                $incact = 'si';
                if ($i["crev"] == '1' || $i["crev"] == '8' || $i["crev"] == '9') {
                    $incact = 'no';
                } else {
                    if ($i["fechalimite"] != '') {
                        if ($i["fechalimite"] < date("Ymd")) {
                            $incact = 'no';
                        }
                    }
                }
                if ($incact == 'si') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica autorizaciones
function armarCertificaAutorizacionesFormato2019($mysqli, $pdf, $data) {
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($data["organizacion"] == '01') {
        $regl4 = false;
        $lib4 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='RM04' and identificacion='" . $data["identificacion"] . "'", "fecharegistro asc");
        if ($lib4 && !empty($lib4)) {
            foreach ($lib4 as $l4) {
                if ($l4["ctrrevoca"] != '1' && $l4["ctrrevoca"] != '8' && $l4["ctrrevoca"] != '9') {
                    if ($l4["acto"] == '3040') {
                        $regl4 = $l4;
                    }
                    if ($l4["acto"] == '3041') {
                        $regl4 = false;
                    }
                }
            }
        }
        if ($regl4) {

            $txt = '<strong>AUTORIZACIONES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();

            $txtnombre = $data["nom1"];
            if ($data["nom2"] != '') {
                $txtnombre .= ' ' . $data["nom2"];
            }
            if ($data["ape1"] != '') {
                $txtnombre .= ' ' . $data["ape1"];
            }
            if ($data["ape2"] != '') {
                $txtnombre .= ' ' . $data["ape2"];
            }
            if ($txtnombre == '') {
                $txtnombre = $regl4["nombre"];
            }
            $txt = 'Por documento privado del ' . \funcionesGenerales::mostrarFechaLetras1($regl4["fechadocumento"]) . ' inscrito en esta Cámara de Comecio el ';
            $txt .= \funcionesGenerales::mostrarFechaLetras1($regl4["fecharegistro"]) . ', con el No. ' . $regl4["registro"] . ' del Libro IV, ';
            $txt .= $regl4["txtapoderados"] . ' autorizaron al menor de edad ' . $txtnombre . ' para ejercer el comercio.';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $pdf->Ln();
        }
    }
}

// Certifica limitaciones
function armarCertificaLimitacionesFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $siurl = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == 'XXX') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
        $txt = '<strong>LIMITACIONES</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == 'XXX') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Reseña a casa principal
function armarCertificaResenaCasaPrincipalFormato2019($mysqli, $pdf, $data) {
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
            $sp = separarDv($data["cpnumnit"]);
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

// Certifica Oposición a la enajenación
function armarCertificaOposicionEnajenacionFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneoposicion = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '036') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica Renuncias y Retiros 0732, 1121, 1731
function armarCertificaRenunciasRetirosFormato2019($mysqli, $pdf, $data) {
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
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica Renuncias y Retiros 0731
function armarCertificaAccionSocialResponsabilidadFormato2019($mysqli, $pdf, $data) {
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica Resoluciones
function armarCertificaResolucionesFormato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneresoluciones = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '044') {
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                $tieneresoluciones = 'si';
            }
        }
    }
    if ($tieneresoluciones == 'si') {
        $txt = '<strong>ORDENES DE AUTORIDAD COMPETENTE - RESOLUCIONES</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '044') {
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica Providencias
function armarCertificaProvidenciasFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneprovidencia = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["grupoacto"] == '039') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '9') {
                $tieneprovidencia = 'si';
            }
        }
    }
    if ($tieneprovidencia == 'si') {
        if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
            $txt = '<strong> PROVIDENCIAS</strong>';
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Arrendatario
// *************************************************************************** //
function armarCertificaArrendatarioFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>ARRENDATARIO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $txt = 'Que el bien se encuentra dado en calidad de arrendamiento a : ';
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $pdf->Ln();
            foreach ($data["vinculos"] as $v) {
                if ($v["tipovinculo"] == 'ARR') {
                    $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                    if ($xnom == '') {
                        $xnom = $v["nombreotros"];
                    }
                    $txt = '<strong>*** Nombre o razón social : </strong>' . $xnom . '<br>';
                    $txt .= '<strong>Identificación : </strong>' . retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $v["idtipoidentificacionotros"] . "'", "descripcion") . ' - ' . $v["identificacionotros"] . '<br>';
                    $txt .= '<strong>Fecha de registro del contrato : </strong>' . \funcionesGenerales::mostrarFechaLetras1($v["fechaotros"]) . '<br>';
                    $txt .= '<strong>Libro y número de inscripción : </strong>' . $v["librootros"] . ' - ' . $v["inscripcionotros"] . '<br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'L');
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica actividad
function armarCertificaActividadFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if (
            $data["ciius"][1] != '' ||
            $data["ciius"][2] != '' ||
            $data["ciius"][3] != '' ||
            $data["ciius"][4] != ''
    ) {
        $txt = '<strong>CLASIFICACIÓN DE ACTIVIDADES ECONÓMICAS - CIIU</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        $txt = '<strong>Actividad principal  Código CIIU: </strong>' . $data["ciius"][1] . '<br>';
        if (trim($data["ciius"][2]) != '') {
            $txt .= '<strong>Actividad secundaria  Código CIIU: </strong>' . $data["ciius"][2] . '<br>';
        } else {
            $txt .= '<strong>Actividad secundaria  Código CIIU: </strong>No reportó<br>';
        }
        if (trim($data["ciius"][3]) != '' || trim($data["ciius"][4]) != '') {
            $txt .= '<strong>Otras actividades Código CIIU: </strong>';
            if (trim($data["ciius"][3]) != '') {
                $txt .= $data["ciius"][3] . ' ';
            }
            if (trim($data["ciius"][4]) != '') {
                $txt .= $data["ciius"][4];
            }
            $txt .= '<br>';
        } else {
            $txt .= '<strong>Otras actividades Código CIIU: </strong>No reportó<br>';
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        // $pdf->Ln();

        if ($data["organizacion"] == '01' || $data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3') {
            $txt = '<strong>Descripción de la actividad económica reportada en el Formulario del Registro Único Empresarial y Social -RUES- : </strong>' . \funcionesGenerales::limpiarTextosRedundantes(\funcionesGenerales::parsearOracion($data["desactiv"]));
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }

        $pdf->Ln();
    }
}

// Certifica transporte
function armarCertificaTransporteFormato2019Anterior($mysqli, $pdf, $data, $nameLog = '') {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $titulohabilitacionesespeciales = 'si';
    $estransportecarga = 'no';
    $estransporteespecial = 'no';
    $estransportemixto = 'no';

    $certificartransportecarga = 'no';
    $certificartransporteespecial = 'no';
    $certificartransportemixto = 'no';
    $certificartransportepasajeros = 'no';

    $certificargrupo093 = 'no';

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
    if (
            $data["ciius"][1] == 'H4922' ||
            $data["ciius"][2] == 'H4922' ||
            $data["ciius"][3] == 'H4922' ||
            $data["ciius"][4] == 'H4922'
    ) {
        $estransportemixto = 'si';
    }

    foreach ($data["inscripciones"] as $ins) {
        if ($ins["grupoacto"] == '066') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                $certificartransportecarga = 'encontro';
            }
        }
    }

    foreach ($data["inscripciones"] as $ins) {
        if ($ins["grupoacto"] == '067') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                $certificartransporteespecial = 'encontro';
            }
        }
    }

    foreach ($data["inscripciones"] as $ins) {
        if ($ins["grupoacto"] == '091') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                $certificartransportemixto = 'encontro';
            }
        }
    }

    foreach ($data["inscripciones"] as $ins) {
        if ($ins["grupoacto"] == '092') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                $certificartransportepasajeros = 'encontro';
            }
        }
    }

    foreach ($data["inscripciones"] as $ins) {
        if ($ins["grupoacto"] == '093') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            $imp = 'no';
            if (trim((string) $ins["flim"])) {
                $imp = 'si';
            } else {
                if (trim((string) $ins["flim"]) != '' && $ins["flim"] < date("Ymd")) {
                    $imp = 'si';
                }
            }
            if ($imp == 'si') {
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $certificargrupo093 = 'no';
                }
            }
        }
    }


    if ($estransportecarga == 'si') {
        $estransportecarga = 'falta';
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '066') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $estransportecarga = 'encontro';
                }
            }
        }
    }

    if ($estransporteespecial == 'si') {
        $estransporteespecial = 'falta';
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '067') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $estransporteespecial = 'encontro';
                }
            }
        }
    }

    if ($estransportemixto == 'si') {
        $estransportemixto = 'falta';
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '091') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    $estransportemixto = 'encontro';
                }
            }
        }
    }

    //
    if ($certificartransportemixto == 'encontro') {
        $titulohabilitacionesespeciales = 'no';
        $txt = '<strong>HABILITACIÓN(ES) ESPECIAL(ES)</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '091') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    if (trim($ins["ndocext"]) != '') {
                        $ndocx = $ins["ndocext"];
                    } else {
                        $ndocx = $ins["ndoc"];
                    }
                    $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                    $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                    $txt .= 'El Ministerio de Transporte que lo habilita para prestar el servicio público de transporte terrestre automotor mixto.';
                }
            }
        }
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }

    //
    if ($certificartransportepasajeros == 'encontro') {
        $titulohabilitacionesespeciales = 'no';
        $txt = '<strong>HABILITACIÓN(ES) ESPECIAL(ES)</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '092') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    if (trim($ins["ndocext"]) != '') {
                        $ndocx = $ins["ndocext"];
                    } else {
                        $ndocx = $ins["ndoc"];
                    }
                    $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                    $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                    $txt .= 'El Ministerio de Transporte que lo habilita para prestar el servicio público de transporte terrestre de pasajeros.';
                }
            }
        }
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }

    //
    if ($estransportecarga == 'encontro' || $certificartransportecarga == 'encontro') {
        if ($titulohabilitacionesespeciales == 'si') {
            $txt = '<strong>HABILITACIÓN(ES) ESPECIAL(ES)</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $titulohabilitacionesespeciales = 'no';
        }
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '066') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    if (trim($ins["ndocext"]) != '') {
                        $ndocx = $ins["ndocext"];
                    } else {
                        $ndocx = $ins["ndoc"];
                    }
                    if ($ins["idmunidoc"] != '') {
                        if (trim($ins["txoridoc"]) != '') {
                            $txtmunicipio = 'en ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"])));
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                            $txt .= ucwords(strtolower($ins["txoridoc"])) . ' ' . $txtmunicipio . ', que lo habilita para prestar el servicio ';
                            $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                        } else {
                            $txtmunicipio = 'expedido en ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"])));
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', ' . $txtmunicipio;
                            $txt .= 'que lo habilita para prestar el servicio ';
                            $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                        }
                    } else {
                        if (trim($ins["txoridoc"]) != '') {
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                            $txt .= ucwords(strtolower($ins["txoridoc"])) . ', que lo habilita para prestar el servicio ';
                            $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                        } else {
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', ';
                            $txt .= 'que lo habilita para prestar el servicio ';
                            $txt .= 'público de transporte terrestre automotor en la modalidad de carga.';
                        }
                    }
                }
            }
        }
        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }

    //
    if ($estransporteespecial == 'encontro' || $certificartransporteespecial == 'encontro') {
        if ($titulohabilitacionesespeciales == 'si') {
            $txt = '<strong>HABILITACIÓN(ES) ESPECIAL(ES)</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $titulohabilitacionesespeciales = 'no';
        }

        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '067') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    if (trim($ins["ndocext"]) != '') {
                        $ndocx = $ins["ndocext"];
                    } else {
                        $ndocx = $ins["ndoc"];
                    }
                    if (trim($ins["txoridoc"]) != '') {
                        $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                        $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                        $txt .= ucwords(strtolower($ins["txoridoc"])) . ', que lo habilita para prestar el servicio ';
                        $txt .= 'público de transporte terrestre automotor especial.';
                    } else {
                        $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' DE FECHA ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                        $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ' ';
                        $txt .= 'que lo habilita para prestar el servicio ';
                        $txt .= 'público de transporte terrestre automotor especial.';
                    }
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    }

    //
    if ($estransportecarga == 'falta') {
        $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-TRACAR', 'CERTIFICA - SERVICIO PUBLICO DE TRANSPORTE DE CARGA');
        if ($resx === false) {
            if ($titulohabilitacionesespeciales == 'si') {
                $txt = '<strong>HABILITACIÓN(ES) ESPECIAL(ES)</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
                $titulohabilitacionesespeciales = 'no';
            }
            if ($data["organizacion"] == '01') {
                $txt = 'La persona natural ';
            } else {
                $txt = 'La persona jurídica ';
            }
            $txt .= 'no ha inscrito el acto administrativo que lo habilita para prestar el servicio público de transporte automotor en la modalidad de carga.';
            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
    }

    //
    if ($certificargrupo093 == 'encontro') {
        if ($titulohabilitacionesespeciales == 'si') {
            $txt = '<strong>HABILITACIÓN(ES) ESPECIAL(ES)</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $titulohabilitacionesespeciales = 'no';
        }

        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '093') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }

                //
                $imp = 'no';
                if (trim((string) $ins["flim"])) {
                    $imp = 'si';
                } else {
                    if (trim((string) $ins["flim"]) != '' && $ins["flim"] < date("Ymd")) {
                        $imp = 'si';
                    }
                }

                //
                if ($imp == 'si') {
                    if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                        if (trim($ins["ndocext"]) != '') {
                            $ndocx = $ins["ndocext"];
                        } else {
                            $ndocx = $ins["ndoc"];
                        }
                        if (trim($ins["txoridoc"]) != '') {
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                            $txt .= ucwords(strtolower($ins["txoridoc"])) . ',  a través del cual se ' . $ins["not"];
                        } else {
                            $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' DE FECHA ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                            $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ' ';
                            $txt .= 'a través del cual se ' . $ins["not"];
                        }
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

function armarCertificaTransporteFormato2019($mysqli, $pdf, $data, $nameLog = '') {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if (!empty($data["habilitacionesespeciales"])) {
        $txt = '<strong>HABILITACIÓN(ES) ESPECIAL(ES)</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["habilitacionesespeciales"] as $he) {
            $pdf->writeHTML($he, true, false, true, false, 'J');
            $pdf->Ln();
        }
    }
}

// Certifica transporte
function armarCertificaSuperVigilanciaFormato2019($mysqli, $pdf, $data, $nameLog = '') {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $certificarsupervigilancia = 'no';
    foreach ($data["inscripciones"] as $ins) {
        if ($ins["grupoacto"] == '090') {
            if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                $ins["crev"] = '0';
            }
            if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                $certificarsupervigilancia = 'si';
            }
        }
    }

    if ($certificarsupervigilancia == 'si') {
        $txt = '<strong>HABILITACIÓN(ES) ESPECIAL(ES) - SUPERINTENDENCIA DE VIGILANCIA Y SEGURIDAD PRIVADA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '090') {
                if ($ins["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $ins["crev"] == '1') {
                    $ins["crev"] = '0';
                }
                if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                    if (trim($ins["ndocext"]) != '') {
                        $ndocx = $ins["ndocext"];
                    } else {
                        $ndocx = $ins["ndoc"];
                    }
                    $textoacto = '';
                    switch ($ins["acto"]) {
                        case "0860" : $textoacto = 'OTORGA';
                            BREAK;
                        case "0862" : $textoacto = 'NIEGA';
                            BREAK;
                        case "0861" : $textoacto = 'RENUEVA';
                            BREAK;
                        case "0863" : $textoacto = 'SUSPENDE';
                            BREAK;
                        case "0864" : $textoacto = 'CANCELA';
                            BREAK;
                    }
                    $txt = 'Mediante inscripción No. ' . $ins["nreg"] . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["freg"])) . ' ';
                    $txt .= 'se registró el acto administrativo No. ' . $ndocx . ' de ' . strtolower(\funcionesGenerales::mostrarFechaLetras1($ins["fdoc"])) . ', expedido por ';
                    $txt .= 'La Superintendencia de Vigilancia y Seguridad Privada que le ' . $textoacto . ' la licencia para prestar el servicio de vigilancia y seguridad privada.';
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica información financiera
function armarCertificaInformacionFinancieraFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $financiera = 'no';
    if (
            $data["actvin"] == 0 &&
            $data["actcte"] == 0 &&
            $data["actnocte"] == 0 &&
            $data["actval"] == 0 &&
            $data["actotr"] == 0 &&
            $data["actfij"] == 0 &&
            $data["fijnet"] == 0 &&
            $data["acttot"] == 0 &&
            $data["pascte"] == 0 &&
            $data["paslar"] == 0 &&
            $data["pastot"] == 0 &&
            $data["paspat"] == 0 &&
            $data["balsoc"] == 0 &&
            $data["ingope"] == 0 &&
            $data["ingnoope"] == 0 &&
            $data["cosven"] == 0 &&
            $data["gtoven"] == 0 &&
            $data["gtoadm"] == 0 &&
            $data["gasimp"] == 0 &&
            $data["utiope"] == 0 &&
            $data["utinet"] == 0
    ) {
        if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && $data["categoria"] == '1')) {
            $financiera = 'si';
        } else {
            return false;
        }
    }

    //
    $txt = '<strong>INFORMACION FINANCIERA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();

    if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
        $txt = 'La entidad reportó la siguiente información financiera:';
    } else {
        $txt = 'El comerciante matriculado reportó la siguiente información financiera, la cual corresponde a la última información reportada en la matrícula mercantil, así:';
    }
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
    $txt = '<strong>Estado de la situación financiera:</strong><br>';
    $txt .= 'Activo corriente: ' . \funcionesGenerales::mostrarPesos2($data["actcte"]) . '<br>';
    $txt .= 'Activo no corriente: ' . \funcionesGenerales::mostrarPesos2($data["actnocte"]) . '<br>';
    $txt .= 'Activo total: ' . \funcionesGenerales::mostrarPesos2($data["acttot"]) . '<br>';
    $txt .= 'Pasivo corriente: ' . \funcionesGenerales::mostrarPesos2($data["pascte"]) . '<br>';
    $txt .= 'Pasivo no corriente: ' . \funcionesGenerales::mostrarPesos2($data["paslar"]) . '<br>';
    $txt .= 'Pasivo total: ' . \funcionesGenerales::mostrarPesos2($data["pastot"]) . '<br>';
    $txt .= 'Patrimonio neto: ' . \funcionesGenerales::mostrarPesos2($data["pattot"]) . '<br>';
    $txt .= 'Pasivo más patrimonio: ' . \funcionesGenerales::mostrarPesos2($data["paspat"]) . '<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    $pdf->Ln();

    $txt = '<strong>Estado de resultados:</strong><br>';
    $txt .= 'Ingresos actividad ordinaria: ' . \funcionesGenerales::mostrarPesos2($data["ingope"]) . '<br>';
    $txt .= 'Otros ingresos: ' . \funcionesGenerales::mostrarPesos2($data["ingnoope"]) . '<br>';
    $txt .= 'Costo de ventas: ' . \funcionesGenerales::mostrarPesos2($data["cosven"]) . '<br>';
    $txt .= 'Gastos operacionales: ' . \funcionesGenerales::mostrarPesos2($data["gtoven"]) . '<br>';
    $txt .= 'Otros gastos: ' . \funcionesGenerales::mostrarPesos2($data["gtoadm"]) . '<br>';
    $txt .= 'Gastos por impuestos: ' . \funcionesGenerales::mostrarPesos2($data["gasimp"]) . '<br>';
    $txt .= 'Utilidad operacional: ' . \funcionesGenerales::mostrarPesos2($data["utiope"]) . '<br>';
    $txt .= 'Resultado del periodo: ' . \funcionesGenerales::mostrarPesos2($data["utinet"]) . '<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    $pdf->Ln();
}

// Certifica libro XVIII
// armarCertificasLibroRm03Rm18Rm19Formato2019
function armarCertificasLibroRm03Rm18Rm19Formato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $actosLibro03 = 'no';
    $actosLibro18 = 'no';
    $actosLibro19reorganizacion = 'no';
    $actosLibro19liqjudicial = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["lib"] == 'RM03') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                $actosLibro03 = 'si';
            }
        }
        if ($i["lib"] == 'RM18') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                $actosLibro18 = 'si';
            }
        }
        if ($i["lib"] == 'RM19') {
            if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                $i["crev"] = '0';
            }
            if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                if ($i["grupoacto"] == '029') {
                    $actosLibro19reorganizacion = 'si';
                }
                if ($i["grupoacto"] == '078') {
                    $actosLibro19liqjudicial = 'si';
                }
            }
        }
    }

    if ($actosLibro03 == 'si') {
        if ($pdf->imprimiotituloprocesosespeciales != 'si') {
            $txt = '<strong>PROCESOS ESPECIALES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->imprimiotituloprocesosespeciales = 'si';
        }
        $txt = '<strong>CONCORDATO O LIQUIDACION OBLIGATORIA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        foreach ($data["inscripciones"] as $i) {
            if ($i["lib"] == 'RM03') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }

    if ($actosLibro18 == 'si') {
        if ($pdf->imprimiotituloprocesosespeciales != 'si') {
            $txt = '<strong>PROCESOS ESPECIALES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->imprimiotituloprocesosespeciales = 'si';
        }
        $txt = '<strong>ACUERDO DE REESTRUCTURACIÓN</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        foreach ($data["inscripciones"] as $i) {
            if ($i["lib"] == 'RM18') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }

    if ($actosLibro19reorganizacion == 'si') {
        if ($pdf->imprimiotituloprocesosespeciales != 'si') {
            $txt = '<strong>PROCESOS ESPECIALES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->imprimiotituloprocesosespeciales = 'si';
        }
        $txt = '<strong>PROCESO DE REORGANIZACIÓN EMPRESARIAL</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        foreach ($data["inscripciones"] as $i) {
            if ($i["lib"] == 'RM19') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    if ($i["grupoacto"] == '029') {
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }

    if ($actosLibro19liqjudicial == 'si') {
        if ($pdf->imprimiotituloprocesosespeciales != 'si') {
            $txt = '<strong>PROCESOS ESPECIALES</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->imprimiotituloprocesosespeciales = 'si';
        }
        $txt = '<strong>PROCESO DE LIQUIDACIÓN JUDICIAL</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        foreach ($data["inscripciones"] as $i) {
            if ($i["lib"] == 'RM19') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '8' && $i["crev"] != '9') {
                    if ($i["grupoacto"] == '078') {
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// Certifica sEGURIDAD Y vIGILANCIA
function armarCertificasSeguridadVigilancia2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $actosSegVig = 'no';
    foreach ($data["inscripciones"] as $i) {
        if ($i["lib"] == 'RM09') {
            if ($i["grupoacto"] == '084') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $actosSegVig = 'si';
                }
            }
        }
    }
    if ($actosSegVig == 'si') {
        $txt = '<strong>SERVICIO DE SEGURIDAD Y VIGILANCIA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $i) {
            if ($i["lib"] == 'RM09') {
                if ($i["grupoacto"] == '084') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// *************************************************************************** //
// Arma certifica de datos básicos
// *************************************************************************** //
function armarDatosBasicosLibrosFormato2019($mysqli, $pdf, $data) {
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

// Textos libres
function armarCertificaTextoLibreFormato2019($mysqli, $pdf, $data, $certif = '', $titulo = '', $titulos = 'si') {
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
            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
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
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                $pdf->MultiCell(185, 4, $txt . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
            }
        }
    }
}

// Textos Libres por Clase
function armarCertificaTextoLibreObjetoSocialFormato2019($mysqli, $pdf, $data, $clase = '', $titulo = '', $titulos = 'si', $namelog = '') {

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

    // wsierra: 2019-05-10 : Se excluye CRT-VARIOS
    if ($clase != 'AC-SOCIOS' && $clase != 'CRT-REFORMAS' && $clase != 'CRT-VARIOS') {

        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim((string) $data["crtsii"][$certif]);
                    // $txt1 = str_replace('<p>&nbsp;</p>', chr(13) . chr(10), $txt1);
                    // $txt1 = str_replace('<p style="text-align: justify;">&nbsp;</p>', chr(13) . chr(10), $txt1);
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", " "), $txt1);
                    // $txt1 = strip_tags((string) $txt1);
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
        // $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if (trim($txtx) != '') {
            if ($titulos == 'si') {
                $txt = '<strong>' . $titulo . '</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txtlower = \funcionesGenerales::parsearOracion($txtx);
            // $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
            // $txtlower = \funcionesGenerales::agregarPuntoFinal($txtlower);
            // $pdf->MultiCell(185, 4, $txtlower, 0, 'J', 0);
            $pdf->writeHTML($txtlower . '<br>', true, false, true, false, 'J');
            // $pdf->Ln();
            $retornar = true;
        }
    }

    // wsierra: 2019-05-10 : Se incluye CRT-VARIOS
    if ($clase == 'AC-SOCIOS' || $clase == 'CRT-REFORMAS' || $clase == 'CRT-VARIOS') {
        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim($data["crtsii"][$certif]);
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", ""), $txt1);
                    // $txt1 = strip_tags($txt1);
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", " "), $txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = $data["crt"][$certif];
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
        if (trim($txtx) != '') {
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
            // $pdf->writeHTML($txtx, true, false, true, false, 'J');
            $txtlower = \funcionesGenerales::parsearOracion($txtx);
            // $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
            // $txtlower = \funcionesGenerales::agregarPuntoFinal($txtlower);
            $pdf->MultiCell(185, 4, $txtlower . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
            // $pdf->Ln();
            $retornar = true;
        }
    }


    return $retornar;
}

function armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, $clase = '', $titulo = '', $titulos = 'si', $namelog = '') {

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

    if ($clase != 'AC-SOCIOS' && $clase != 'CRT-REFORMAS' && $clase != 'CRT-VARIOS') {

        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim((string) $data["crtsii"][$certif]);
                    // $txt1 = str_replace(array('<p>&nbsp;</p>', '<p></p>'), chr(13) . chr(10), $txt1);
                    // $txt1 = str_replace('<p style="text-align: justify;">&nbsp;</p>', chr(13) . chr(10), $txt1);
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", " "), $txt1);
                    // $txt1 = strip_tags((string) $txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txtx = '';
                        $txt1 = $data["crt"][$certif];
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        $txt1 = strip_tags($txt1);
                        $txtx .= $txt1;
                    }
                }
            }
        }

        //
        // $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if (trim($txtx) != '') {
            if ($titulos == 'si') {
                $txt = '<strong>' . $titulo . '</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txtlower = $txtx;
            $txtlower = \funcionesGenerales::parsearOracion($txtx);
            // $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
            // $txtlower = \funcionesGenerales::agregarPuntoFinal($txtlower);
            // $pdf->MultiCell(185, 4, $txtlower, 0, 'J', 0);
            $pdf->writeHTML($txtlower . '<br>', true, false, true, false, 'J');
            // $pdf->Ln();
            $retornar = true;
        }
    }

    if ($clase == 'AC-SOCIOS' || $clase == 'CRT-REFORMAS' || $clase == 'CRT-VARIOS') {
        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim($data["crtsii"][$certif]);
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", ""), $txt1);
                    $txt1 = strip_tags($txt1);
                    $txt1 = str_replace(array("?"), array(" "), $txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = $data["crt"][$certif];
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
        if (trim($txtx) != '') {
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
            // $pdf->writeHTML($txtx, true, false, true, false, 'J');
            // $txtlower = $txtx;
            $txtlower = strip_tags($txtx);
            $txtlower = \funcionesGenerales::parsearOracion($txtlower);
            $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
            $txtlower = \funcionesGenerales::agregarPuntoFinal($txtlower);
            $pdf->MultiCell(185, 4, $txtlower . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
            // $pdf->Ln();
            $retornar = true;
        }
    }


    return $retornar;
}

function armarCertificaTextoLibrePoderesFormato2019($mysqli, $pdf, $data, $clase = '', $titulo = '', $titulos = 'si', $namelog = '') {

    if ($titulos == '') {
        $titulos = 'si';
    }
    $retornar = false;
    $txtx = '';

    foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
        if ($dtax["clase"] == $clase) {
            if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                $txtx = '';
                $txt1 = trim((string) $data["crtsii"][$certif]);
                /*
                  $txt1 = str_replace(array('<p>&nbsp;</p>', '<p></p>'), chr(13) . chr(10), $txt1);
                  $txt1 = str_replace('<p style="text-align: justify;">&nbsp;</p>', chr(13) . chr(10), $txt1);
                  $txt1 = str_replace(array("?", "&nbsp;"), array(" ", " "), $txt1);
                  $txt1 = strip_tags((string) $txt1);
                 */
                $txtx .= $txt1;
                if ($titulos == 'si') {
                    $txt = '<strong>' . $titulo . '</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                $txtlower = \funcionesGenerales::parsearOracion($txtx);
                $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
                $pdf->writeHTML($txtlower . '<br>', true, false, true, false, 'J');
                $retornar = true;
            } else {
                if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                    $txtx = '';
                    $txt1 = $data["crt"][$certif];
                    $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                    $txt1 = str_replace("|", " ", $txt1);
                    $txt1 = strip_tags($txt1);
                    $txtx .= $txt1;
                    if ($titulos == 'si') {
                        $txt = '<strong>' . $titulo . '</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    }
                    $txtlower = \funcionesGenerales::parsearOracion($txtx);
                    $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
                    $pdf->writeHTML($txtlower . '<br>', true, false, true, false, 'J');
                    $retornar = true;
                }
            }
        }
    }


    return $retornar;
}

function armarCertificaTextoLibreClaseWriteHtmlFormato2019($mysqli, $pdf, $data, $clase = '', $titulo = '', $titulos = 'si', $namelog = '') {

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

    // wsierra: 2019-05-10 : Se excluye CRT-VARIOS
    if ($clase != 'AC-SOCIOS' && $clase != 'CRT-REFORMAS' && $clase != 'CRT-VARIOS') {

        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim((string) $data["crtsii"][$certif]);
                    // $txt1 = str_replace('<p>&nbsp;</p>', chr(13) . chr(10), $txt1);
                    // $txt1 = str_replace('<p style="text-align: justify;">&nbsp;</p>', chr(13) . chr(10), $txt1);
                    $txt1 = str_replace(array("?", "&nbsp;"), array(" ", " "), $txt1);
                    // $txt1 = strip_tags((string) $txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = $data["crt"][$certif];
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        $txt1 = strip_tags($txt1);
                        $txtx .= $txt1;
                    }
                }
            }
        }

        //
        // $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if (trim($txtx) != '') {
            if ($titulos == 'si') {
                $txt = '<strong>' . $titulo . '</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txtlower = \funcionesGenerales::parsearOracion($txtx);
            $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
            // $txtlower = \funcionesGenerales::agregarPuntoFinal($txtlower);
            // $pdf->MultiCell(185, 4, $txtlower, 0, 'J', 0);
            $pdf->writeHTML($txtlower . '<br>', true, false, true, false, 'J');
            // $pdf->Ln();
            $retornar = true;
        }
    }

    // wsierra: 2019-05-10 : Se incluye CRT-VARIOS
    if ($clase == 'AC-SOCIOS' || $clase == 'CRT-REFORMAS' || $clase == 'CRT-VARIOS') {
        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim($data["crtsii"][$certif]);
                    $txt1 = str_replace(array("?"), array(" "), $txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = $data["crt"][$certif];
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
        if (trim($txtx) != '') {
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
            // $pdf->writeHTML($txtx, true, false, true, false, 'J');
            $txtlower = \funcionesGenerales::parsearOracion($txtx);
            $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
            // $txtlower = \funcionesGenerales::agregarPuntoFinal($txtlower);
            // $pdf->MultiCell(185, 4, $txtlower . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
            $pdf->writeHTML($txtlower . '<br>', true, false, true, false, 'J');
            // $pdf->Ln();
            $retornar = true;
        }
    }


    return $retornar;
}

function armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, $clase = '', $titulo = '', $titulos = 'si', $namelog = '') {

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

    // wsierra: 2019-05-10 : Se excluye CRT-VARIOS
    if ($clase != 'AC-SOCIOS' && $clase != 'CRT-REFORMAS' && $clase != 'CRT-VARIOS') {

        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim((string) $data["crtsii"][$certif]);
                    $txt1 = str_replace('<p style="text-align: justify;">', chr(13) . chr(10), $txt1);
                    $txt1 = str_replace('<p style="text-align: Justify;">', chr(13) . chr(10), $txt1);
                    $txt1 = str_replace('<p style="text-align: justify;">&nbsp;</p>', chr(13) . chr(10), $txt1);
                    $txt1 = str_replace('<p style="text-align: Justify;">&nbsp;</p>', chr(13) . chr(10), $txt1);
                    $txt1 = str_replace('<p>&nbsp;</p>', chr(13) . chr(10), $txt1);
                    $txt1 = str_replace('&nbsp;', " ", $txt1);
                    $txt1 = str_replace('<p>', chr(13) . chr(10), $txt1);
                    $txt1 = str_replace('</p>', chr(13) . chr(10), $txt1);
                    $txt1 = str_replace(array("?", "&nbsp;"), array(" ", " "), $txt1);
                    $txt1 = strip_tags((string) $txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = $data["crt"][$certif];
                        $txt1 = str_replace("||", CHR(13) . CHR(10) . CHR(13) . CHR(10), $txt1);
                        $txt1 = str_replace("|", " ", $txt1);
                        $txt1 = strip_tags($txt1);
                        $txtx .= $txt1;
                    }
                }
            }
        }

        //        
        $lineas = explode(chr(13) . chr(10), $txtx);

        //
        // $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if (trim($txtx) != '') {
            if ($titulos == 'si') {
                $txt = '<strong>' . $titulo . '</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }

            if (strlen($txtx) > 500) {
                // $txtlower = \funcionesGenerales::parsearOracion($txtx);
                // $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
                // $txtlower = \funcionesGenerales::agregarPuntoFinal($txtlower);
                // $pdf->MultiCell(185, 4, $txtx . "\r\n\r\n", 0, 'J', 0);
                foreach ($lineas as $l) {
                    if (trim((string) $l) != '') {
                        $l = \funcionesGenerales::parsearOracion($l);
                        $pdf->MultiCell(185, 4, $l . "\r\n\r\n", 0, 'J', 0);
                    }
                }
            } else {
                $txtx = \funcionesGenerales::parsearOracion($txtx);
                $pdf->writeHTML($txtx . '<br>', true, false, true, false, 'J');
            }
            $retornar = true;
        }
    }

    //
    // wsierra: 2019-05-10 : Se incluye CRT-VARIOS
    if ($clase == 'AC-SOCIOS' || $clase == 'CRT-REFORMAS' || $clase == 'CRT-VARIOS') {
        foreach ($_SESSION["generales"]["clasecerti"] as $certif => $dtax) {
            if ($dtax["clase"] == $clase) {
                if (isset($data["crtsii"][$certif]) && trim($data["crtsii"][$certif]) != '') {
                    $txt1 = trim($data["crtsii"][$certif]);
                    // $txt1 = str_replace(array("?", "&nbsp;"), array(" ", ""), $txt1);
                    $txt1 = strip_tags($txt1);
                    $txt1 = str_replace(array("?"), array(" "), $txt1);
                    $txtx .= $txt1;
                } else {
                    if (isset($data["crt"][$certif]) && trim($data["crt"][$certif]) != '') {
                        $txt1 = $data["crt"][$certif];
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
        if (trim($txtx) != '') {
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
            // $pdf->writeHTML($txtx, true, false, true, false, 'J');
            $txtlower = \funcionesGenerales::parsearOracion($txtx);
            $txtlower = \funcionesGenerales::limpiarTextosRedundantes($txtlower);
            $txtlower = \funcionesGenerales::agregarPuntoFinal($txtlower);
            $pdf->MultiCell(185, 4, $txtlower . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
            // $pdf->Ln();
            $retornar = true;
        }
    }


    return $retornar;
}

// Textos prooios sacados de pantallas_propias
function armarCertificaTextoPropioFormato2019($mysqli, $pdf, $data, $pantalla = '', $titulo = '', $titulos = 'si') {
    if ($titulos == '') {
        $titulos = 'si';
    }

    //
    $retornar = true;
    if ($pantalla != '') {
        $texto = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, $pantalla));
        if ($texto != '') {
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            if ($titulos == 'si') {
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>' . $titulo . '</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
            }
            $texto = \funcionesGenerales::agregarPuntoFinal($texto);
            $pdf->writeHTML($texto, true, false, true, false, 'J');
        }
    }

    //
    return $retornar;
}

// Certifica cambio de domicilio de la matrícula en caso que hubiere
// estado matriculado con anterioridad en otra cámara de comercio
function armarCertificaCambioDomicilioFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if (ltrim($data["camant"], "0") != '') {
        $txt = '<strong>CAMBIOS DE DOMICILIO</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $txt = 'Que el comerciante cambió su domicilio desde ' . retornarNombreMunicipioMysqliApi($mysqli, $data["munant"], 'm') . ', ';
        $txt .= 'donde estuvo matriculado bajo el número ' . $data["matant"] . " del " . \funcionesGenerales::mostrarFechaLetras1($data["fecmatant"]);
        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();
    }
}

// Certifica Cambios de Jurisdicción, Acto 9997
function armarCertificaCambioJurisdiccionFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>CAMBIOS DE JURISDICCIÓN</strong>';
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica Cambios de domicilio sale, Acto 0497
function armarCertificaCambioDomicilioSaleFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>CAMBIOS DE DOMICILIO</strong>';
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica Cambios de nombre
function armarCertificaCambiosNombreFormato2019($mysqli, $pdf, $data) {
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
            $nomx = borrarPalabrasAutomaticas($n["nom"]);
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
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-CAMNOM', 'CERTIFICA - CAMBIOS DE NOMBRE O RAZON SOCIAL');
    }
}

// Certifica Cambios de nombre - inscripciones
function armarCertificaCambiosNombreConInscripcionFormato2019($mysqli, $pdf, $data) {
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
                $txt = descripcionesCambioNombreFormato2019($mysqli, $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $data["organizacion"], $data["categoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                if (isset($data["nomant"][$num1])) {
                    $nomx = \funcionesGenerales::borrarPalabrasAutomaticas($n["nom"]);
                    $nomantx = \funcionesGenerales::borrarPalabrasAutomaticas($data["nomant"][$num1]["nom"]);
                    $txt .= $nomx . ' por ' . $nomantx;
                } else {
                    $nomx = \funcionesGenerales::borrarPalabrasAutomaticas($n["nom"]);
                    $nomantx = \funcionesGenerales::borrarPalabrasAutomaticas($data["nombre"], $data["complementorazonsocial"]);
                    $txt .= $nomx . ' por ' . $nomantx;
                }
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
    }
}

// Certifica disolucion - Acto 0510
function armarCertificaDisolucionFormato2019($mysqli, $pdf, $data) {
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
                $txt = '<strong>DISOLUCIÓN</strong>';
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
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// Certifica reactivaciones - Acto 0511
function armarCertificaReactivacionFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>REACTIVACIÓN</strong>';
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
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// Certifica liquidacion - Acto 0520
function armarCertificaLiquidacionFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>LIQUIDACIÓN</strong>';
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
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
            $txt = '<strong>LIQUIDACIÓN ADICIONAL</strong>';
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica depuracion ley 1429
function armarCertificaDepuracion1429Formato2019($mysqli, $pdf, $data) {
    if (!isset($data["ctrcancelacion1429"])) {
        $data["ctrcancelacion1429"] = '';
    }
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if (ltrim(trim($data["ctrcancelacion1429"]), "0") == '3') {
        $txt = '<strong>CERTIFICA - DEPURACION LEY 1429</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        $txt = 'Que en cumplimiento de lo establecido en el artículo 50 de la Ley 1429 de 2010, ';
        if (
                $data["organizacion"] == '01' ||
                $data["organizacion"] == '02' ||
                $data["categoria"] == '2' ||
                $data["categoria"] == '3'
        ) {
            $txt .= 'se decretó la cancelación de la matrícula mercantil.';
        } else {
            $txt .= 'se decretó la disolución de la persoina jurídica.';
        }
        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    }
}

// Certifica depuracion ley 1727
function armarCertificaDepuracion1727Formato2019($mysqli, $pdf, $data) {
    if (!isset($data["ctrdepuracion1727"])) {
        $data["ctrdepuracion1727"] = '';
    }
    if (!isset($data["ctrfechadepuracion1727"])) {
        $data["ctrfechadepuracion1727"] = '';
    }
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if ($data["ctrdepuracion1727"] == 'S') {
        $txt = '<strong>CERTIFICA - DEPURACION LEY 1727 DE 2014</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();

        if ($data["ctrfechadepuracion1727"] == '') {
            $txt = 'Que en cumplimiento de lo establecido en el artículo 31 de la Ley 1727 de 2014, ';
        } else {
            $txt = 'Que en cumplimiento de lo establecido en el artículo 31 de la Ley 1727 de 2014, el ';
            $txt .= \funcionesGenerales::mostrarFechaLetras($data["ctrfechadepuracion1727"]) . ' ';
        }
        if (
                $data["organizacion"] == '01' ||
                $data["organizacion"] == '02' ||
                $data["categoria"] == '2' ||
                $data["categoria"] == '3'
        ) {
            $txt .= 'se decretó la cancelación de la matrícula mercantil.';
        } else {
            $txt .= 'se decretó la disolución de la persona jurídica.';
        }
        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    }
}

// Certifica EN depuracion ley 1727
function armarCertificaEnDepuracion1727Formato2019($mysqli, $pdf, $data) {
    //
    if ($data["obligadorenovar"] == 'N') {
        return true;
    }

    if (isset($pdf->disuelta) && $pdf->disuelta == 'si') {
        return true;
    }

    $mes = intval(substr($_SESSION["generales"]["fcorte"], 4, 2)) + 1;
    $mes = sprintf("%02s", $mes);
    if (date("Ymd") > $_SESSION["generales"]["fcorte"] && date("md") <= $mes . '31') {
        if (
                $data["estadomatricula"] != 'IC' &&
                $data["estadomatricula"] != 'MC' &&
                $data["estadomatricula"] != 'MF' &&
                $data["estadomatricula"] != 'MG' &&
                $data["estadomatricula"] != 'NA'
        ) {
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            if ($data["ultanoren"] < (date("Y") - 4)) {
                $inc = 'si';
                if ($data["fechaacto511"] != '') {
                    if ($data["fechaacto511"] >= date("Y") . '0101') {
                        $inc = 'no';
                    }
                }
                if ($inc == 'si') {
                    if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
                        $txt = '<strong>DEPURACIÓN LEY 1727 DE 2014</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                        $txt = 'A la fecha de expedición de este certificado, este registro se encuentra en proceso de depuración ';
                        $txt .= 'en cumplimiento de lo establecido en el artículo 31 de la Ley 1727 de 2014, ';
                        $txt .= 'lo que eventualmente puede afectar el contenido de la información que consta en el mismo.';
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    } else {
                        $txt = '<strong>DEPURACIÓN LEY 1727 DE 2014</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                        $txt = 'A la fecha de expedición de este certificado, esta matrícula/registro se encuentra en proceso de depuración ';
                        $txt .= 'en cumplimiento de lo establecido en el artículo 31 de la Ley 1727 de 2014, ';
                        $txt .= 'lo que eventualmente puede afectar el contenido de la información que consta en el mismo.';
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

// Liquidación obligatoria, actos 0650 al 0690
// Grupoacto liquidacion obligatoria
function armarCertificaLiquidacionObligatoriaFormato2019($mysqli, $pdf, $data) {
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
        $txt = '<strong>LIQUIDACIÓN OBLIGATORIA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '032') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }
}

// Certifica de cancelacion
function armarCertificaCancelacionCambioDomicilioFormato2019($mysqli, $pdf, $data) {
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
                $txt = '<strong>CAMBIO DE DOMICILIO</strong>';
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
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
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
function armarCertificaCierreFormato2019($mysqli, $pdf, $data) {
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
                    $txt = '<strong>CIERRE</strong>';
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
                            $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                            $pdf->writeHTML($txt, true, false, true, false, 'J');
                            $pdf->Ln();
                        }
                    }
                }
            }
            if ($cancelacion == 'no') {
                if ($data["fechacancelacion"] != '') {
                    if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                        $txt = '<strong>ESTADO DE LA MATRICULA MERCANTIL</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    } else {
                        $txt = '<strong>CERTIFICA</strong>';
                        $pdf->writeHTML($txt, true, false, true, false, 'C');
                        $pdf->Ln();
                    }
                    $txt = 'La matrícula se encuentra cancelada en el Registro Público Mercantil a partir del ' . \funcionesGenerales::mostrarFechaLetras1($data["fechacancelacion"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
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
function armarCertificaCancelacionFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>CERTIFICA - CANCELACION</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '002') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                    }
                }
            }
        }
        if ($cancelacion == 'no') {
            if ($data["fechacancelacion"] != '') {
                $txt = '<strong>CERTIFICA - CANCELACIÓN</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
                $txt = 'La matrícula se encuentra cancelada en el Registro Público Mercantil a partir del ' . \funcionesGenerales::mostrarFechaLetras1($data["fechacancelacion"]);
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Certifica Cesacion Actividad
// *************************************************************************** //
function armarCertificaCesacionActividadFormato2019($mysqli, $pdf, $data) {
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
            if ($data["organizacion"] == '01') {
                $txt = '<strong>CESACIÓN DE ACTIVIDAD COMERCIAL</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            } else {
                if ($data["organizacion"] == '02' || $data["categoria"] == '2' || $data["categoria"] == '3') {
                    $txt = '<strong>CIERRE DE ESTABLECIMIENTO DE COMERCIO</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CESACIÓN DE ACTIVIDAD COMERCIAL</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
            }
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '071') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"] . '|' . $data["categoria"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', $i["nombre"], '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

function armarCertificaDisolucionReactivacionFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["estadomatricula"] != 'MF' && $data["estadomatricula"] != 'MC') {
        $disreact = 'no';
        foreach ($data["inscripciones"] as $i) {
            if ($i["grupoacto"] == '009' || $i["grupoacto"] == '011') {
                if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                    $i["crev"] = '0';
                }
                if ($i["crev"] != '1' && $i["crev"] != '9') {
                    $disreact = 'si';
                }
            }
        }
        if ($disreact == 'si') {
            $fecdisx = '';
            $fecvenx = 0;
            foreach ($data["inscripciones"] as $i) {
                if ($i["grupoacto"] == '009') {
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["fecant3"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                        $fecdisx = $i["freg"];
                    }
                }
                if ($i["grupoacto"] == '011') {
                    if ($data["fechavencimiento1"] != '' && $data["fechavencimiento1"] <= $i["freg"] && $fecvenx == 0) {
                        $txt = descripcionesDisolucionFechaVencimientoFormato2019($mysqli, $pdf, $data, $data["fechavencimiento1"]);
                        $fecvenx++;
                        $fecdisx = $data["fechavencimiento1"];
                    }
                    if ($data["fechavencimiento2"] != '' && $data["fechavencimiento2"] <= $i["freg"] && $fecvenx == 1) {
                        $txt = descripcionesDisolucionFechaVencimientoFormato2019($mysqli, $pdf, $data, $data["fechavencimiento2"]);
                        $fecvenx++;
                        $fecdisx = $data["fechavencimiento21"];
                    }
                    if ($data["fechavencimiento3"] != '' && $data["fechavencimiento3"] <= $i["freg"] && $fecvenx == 2) {
                        $txt = descripcionesDisolucionFechaVencimientoFormato2019($mysqli, $pdf, $data, $data["fechavencimiento3"]);
                        $fecvenx++;
                        $fecdisx = $data["fechavencimiento3"];
                    }
                    if ($data["fechavencimiento4"] != '' && $data["fechavencimiento4"] <= $i["freg"] && $fecvenx == 3) {
                        $txt = descripcionesDisolucionFechaVencimientoFormato2019($mysqli, $pdf, $data, $data["fechavencimiento4"]);
                        $fecvenx++;
                        $fecdisx = $data["fechavencimiento4"];
                    }
                    if ($data["fechavencimiento5"] != '' && $data["fechavencimiento5"] <= $i["freg"] && $fecvenx == 4) {
                        $txt = descripcionesDisolucionFechaVencimientoFormato2019($mysqli, $pdf, $data, $data["fechavencimiento5"]);
                        $fecvenx++;
                        $fecdisx = $data["fechavencimiento5"];
                    }

                    //
                    if ($i["freg"] < $_SESSION["generales"]["FECHA_CORTE_REVOCACION"] && $i["crev"] == '1') {
                        $i["crev"] = '0';
                    }
                    if ($i["crev"] != '1' && $i["crev"] != '9') {
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["fecant3"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                        $fecdisx = $i["freg"];
                    }
                }
            }
        }
    }
}

// *************************************************************************** //
// Certifica Fusiones
// *************************************************************************** //
function armarCertificaFusionesFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>FUSIONES</strong>';
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    } else {
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-FUSION', 'CERTIFICA - FUSIONES');
    }
}

// *************************************************************************** //
// Certifica Escisiones
// *************************************************************************** //
function armarCertificaEscisionesFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>ESCISIONES</strong>';
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
                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"], $i["camant2"], $i["libant2"], $i["regant2"], $i["fecant2"], $i["camant3"], $i["libant3"], $i["regant3"], $i["fecant3"], $i["camant4"], $i["libant4"], $i["regant4"], $i["fecant4"], $i["camant5"], $i["libant5"], $i["regant5"], $i["fecant5"], $i["aclaratoria"], $i["tomo72"], $i["folio72"], $i["registro72"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }
        }
    } else {
        armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-ESCISION', 'CERTIFICA - ESCISIONES');
    }
}

// *************************************************************************** //
// Certifica de tamaño empresarial decreto 957
// *************************************************************************** //
function armarCertificaTamanoEmpresarial957Formato2019($mysqli, $pdf, $data) {

    // 
    if ($data["tamanoempresarial957"] != '') {
        if ($data["tamanoempresarialformacalculo"] == 'uvt' || $data["tamanoempresarialformacalculo"] == 'uvb') {
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            $txtCuerpo = 'De conformidad con lo previsto en el artículo 2.2.1.13.2.1 del Decreto 1074 de 2015 y ';
            $txtCuerpo .= 'la Resolución 2225 de 2019 del DANE el tamaño de la empresa es ' . $data["tamanoempresarial957"] . ".\r\n" . "\r\n";
            $txtCuerpo .= 'Lo anterior de acuerdo a la información  reportada por el matriculado o inscrito en el formulario RUES:' . "\r\n" . "\r\n";
            $txtCuerpo .= 'Ingresos por actividad ordinaria : ' . \funcionesGenerales::mostrarPesos2($data["tamanoempresarialingresos"]) . "\r\n";
            $txtCuerpo .= 'Actividad económica por la que percibió mayores ingresos en el periodo - CIIU : ' . $data["tamanoempresarialciiu"] . "\r\n";
            $txt = '<strong>INFORMA - TAMAÑO DE EMPRESA</strong>';
            $txtCuerpo = \funcionesGenerales::agregarPuntoFinal($txtCuerpo);
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->MultiCell(185, 4, $txtCuerpo . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
            return true;
        }
    }
}

// *************************************************************************** //
// Certifica de Vigencia
// *************************************************************************** //
function armarCertificaTerminoDuracionFormato2019($mysqli, $pdf, $data, $nameLog = '') {
    //

    if ($data["claseespesadl"] == '61') {
        return true;
    }

    if ($data["organizacion"] == '01' ||
            $data["organizacion"] == '02' ||
            ($data["categoria"] == '2' && $data["organizacion"] != '08') ||
            ($data["categoria"] == '3' && $data["organizacion"] != '08')) {
        return true;
    }

    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    // Si la matrícula no esta activa, no muestra vigencia
    if (
            $data["estadomatricula"] != 'MA' && $data["estadomatricula"] != 'MI' &&
            $data["estadomatricula"] != 'MR' && $data["estadomatricula"] != 'IA' &&
            $data["estadomatricula"] != 'II' && $data["estadomatricula"] != 'IR'
    ) {
        return true;
    }

    //
    $estextual = 'no';

    // ***************************************************************************** //
    // Si la matrícula no se encuentra disuelta
    // ***************************************************************************** //
    if ($data["disueltaporvencimiento"] != 'si' && $data["disueltaporacto510"] != 'si') {
        if (ltrim(trim($data["fechavencimiento"]), "0") == '' || ltrim(trim($data["fechavencimiento"]), "0") == '9999997') {
            $estextual = 'si';
        } else {
            if (ltrim(trim($data["fechavencimiento"]), "0") != '' && ltrim(trim($data["fechavencimiento"]), "0") != '99999997' && ltrim(trim($data["fechavencimiento"]), "0") != '9999998' && ltrim(trim($data["fechavencimiento"]), "0") != '99999999') {
                $txt = '<strong>TÉRMINO DE DURACIÓN</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
                $txt = 'La persona jurídica no se encuentra disuelta y su duración es hasta el ' . \funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"]));
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                $pdf->writeHTML($txt, true, false, true, false, 'J');
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
                    $txt = '<strong>TÉRMINO DE DURACIÓN</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                    $txt = 'La persona jurídica no se encuentra disuelta y su duración es indefinida';
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $pdf->Ln();
                }
            }

            if (isset($data["reactivadaacto511"]) && $data["reactivadaacto511"] == 'si') {
                armarCertificaDisolucionReactivacionFormato2019($mysqli, $pdf, $data);
            }
            return true;
        }
    }


    // ***************************************************************************** //
    // Si se encuentra disuelta por vencimiento de términos
    // ***************************************************************************** //
    if ($data["disueltaporvencimiento"] == 'si') {
        $mostrar = 'si';
        if ($data["disueltaporacto510"] == 'si') {
            if ($data["fechaacto510"] < $data["fechavencimiento"]) {
                $mostrar = 'no';
            }
        }
        if ($mostrar == 'si') {
            $txt = '<strong>DISOLUCIÓN</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            if (ltrim(trim($data["fechavencimiento"]), "0") != '') {
                if (ltrim(trim($data["fechavencimiento"]), "0") != '99999997') {
                    if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                        $txt = 'La Entidad sin Ánimo de Lucro se encuentra disuelta desde el ' . \funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"]));
                    } else {
                        $txt = 'La persona jurídica se encuentra disuelta desde el ' . \funcionesGenerales::mostrarFechaLetras1(trim($data["fechavencimiento"]));
                    }
                    $txt .= ', por vencimiento del término de duración.';
                } else {
                    $estextual = 'si';
                }
            } else {
                if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                    $txt = 'La Entidad sin Ánimo de Lucro se encuentra disuelta por vencimiento del término de duración.';
                } else {
                    $txt = 'La persona jurídica se encuentra disuelta por vencimiento del término de duración.';
                }
            }
            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $pdf->Ln();
        }
    }

    // ***************************************************************************** //
    // Si se encuentra disuelta por acto
    // ***************************************************************************** //
    if (($data["disueltaporvencimiento"] == 'si' && $data["disueltaporacto510"] == 'si' && $mostrar == 'no') ||
            ($data["disueltaporvencimiento"] != 'si' && $data["disueltaporacto510"] == 'si')) {
        $tipdocdis = '';
        $numdocdis = '';
        $fecdocdis = '';
        $oridocdis = '';
        $fecregdis = '';
        $numregdis = '';
        $libregdis = '';
        $disdep = '';
        foreach ($data["inscripciones"] as $ins) {
            if ($ins["grupoacto"] == '009' || $ins["controldisolucion"] == 'S' || $ins["textoenliquidacion"] == 'S' || $ins["textoenliquidacion"] == 'L') {
                $tipdocdis = $ins["tdoc"];
                $numdocdis = $ins["ndoc"];
                if (trim($ins["ndocext"]) != '') {
                    $numdocdis = $ins["ndocext"];
                }
                $fecdocdis = $ins["fdoc"];
                if (trim($ins["txoridoc"]) != '') {
                    $oridocdis = ucwords(strtolower($ins["txoridoc"]));
                } else {
                    $oridocdis = ucwords(strtolower(retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='12' and idcodigo='" . $ins["idoridoc"] . "'", "descripcion")));
                }
                $fecregdis = $ins["freg"];
                $numregdis = $ins["nreg"];
                $libregdis = $ins["lib"];
                $normadep = '';
                $pos = strpos($ins["not"], 'DEPURACI');
                if ($pos !== false) {
                    $pos1 = strpos($ins["not"], '1727');
                    if ($pos1 !== false) {
                        $normadep = 'el artículo 31 de la ley 1727 de 2014';
                        if ($libregdis == 'RM91' || $libregdis == 'RE91') {
                            $disdep = 'si-1727-91';
                        } else {
                            $disdep = 'si-1727';
                        }
                    } else {
                        $normadep = 'el artículo 50 de la Ley 1429 de 2010';
                        $disdep = 'si-1429';
                    }
                }
            }
            if ($ins["grupoacto"] == '011') {
                $tipdocdis = '';
                $numdocdis = '';
                $fecdocdis = '';
                $oridocdis = '';
                $fecregdis = '';
                $numregdis = '';
                $libregdis = '';
                $disdep = '';
                $normadep = '';
            }
        }

        $txt = '<strong>DISOLUCIÓN</strong>';
        $pdf->writeHTML(\funcionesGenerales::limpiarTextosRedundantes($txt), true, false, true, false, 'C');
        $pdf->Ln();

        if ($disdep != '') {
            if ($disdep == 'si-1429') {
                if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                    $txt = 'La Entidad sin Ánimo de Lucro se disolvió y entró en estado de liquidación en virtud de lo establecido en el artículo 50 de la ley 1429 de 2010, mediante inscripción No. ' . $numregdis . ' del ' . \funcionesGenerales::mostrarFechaLetras1($fecregdis);
                } else {
                    $txt = 'La persona jurídica se disolvió y entró en estado de liquidación en virtud de lo establecido en el artículo 50 de la ley 1429 de 2010, mediante inscripción No. ' . $numregdis . ' del ' . \funcionesGenerales::mostrarFechaLetras1($fecregdis);
                }
            }
            if ($disdep == 'si-1727' || $disdep == 'si-1727-91') {
                if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                    if ($disdep == 'si-1727-91') {
                        $txt = 'La Entidad sin Ánimo de Lucro se disolvió y entró en estado de liquidación en virtud de lo establecido en el artículo 31 de la ley 1727 de 2014, mediante inscripción del ' . \funcionesGenerales::mostrarFechaLetras1($fecregdis);
                    } else {
                        $txt = 'La Entidad sin Ánimo de Lucro se disolvió y entró en estado de liquidación en virtud de lo establecido en el artículo 31 de la ley 1727 de 2014, mediante inscripción No. ' . $numregdis . ' del ' . \funcionesGenerales::mostrarFechaLetras1($fecregdis);
                    }
                } else {
                    if ($disdep == 'si-1727-91') {
                        $txt = 'La persona jurídica se disolvió y entró en estado de liquidación en virtud de lo establecido en el artículo 31 de la ley 1727 de 2014, mediante inscripción del ' . \funcionesGenerales::mostrarFechaLetras1($fecregdis);
                    } else {
                        $txt = 'La persona jurídica se disolvió y entró en estado de liquidación en virtud de lo establecido en el artículo 31 de la ley 1727 de 2014, mediante inscripción No. ' . $numregdis . ' del ' . \funcionesGenerales::mostrarFechaLetras1($fecregdis);
                    }
                }
            }
            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
            $pdf->writeHTML(\funcionesGenerales::limpiarTextosRedundantes($txt), true, false, true, false, 'J');
            $pdf->Ln();
        } else {
            if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
                $txt = 'La Entidad sin Ánimo de Lucro quedó disuelta y entró en estado de liquidación por ' . retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdocdis . "'", "descripcionlower");
                if ($numdocdis != '' && strtoupper($normadep) != 'NA' && strtoupper($normadep) != 'N/A' && strtoupper($normadep) != 'SN' && strtoupper($normadep) != 'S/N') {
                    $txt .= ' No. ' . $numdocdis . ' del ' . \funcionesGenerales::mostrarFechaLetras1($fecdocdis) . ' del ' . $oridocdis . ', ';
                } else {
                    $txt .= ' del ' . \funcionesGenerales::mostrarFechaLetras1($fecdocdis) . ' del ' . $oridocdis . ', ';
                }
            } else {
                $txt = 'La persona jurídica quedó disuelta y entró en estado de liquidación por ' . retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdocdis . "'", "descripcionlower");
                if ($numdocdis != '' && strtoupper($normadep) != 'NA' && strtoupper($normadep) != 'N/A' && strtoupper($normadep) != 'SN' && strtoupper($normadep) != 'S/N') {
                    $txt .= ' No. ' . $numdocdis . ' del ' . \funcionesGenerales::mostrarFechaLetras1($fecdocdis) . ' de ' . $oridocdis . ', ';
                } else {
                    $txt .= ' del ' . \funcionesGenerales::mostrarFechaLetras1($fecdocdis) . ' de ' . $oridocdis . ', ';
                }
            }
            $txt .= 'inscrita en esta Cámara de Comercio el ' . \funcionesGenerales::mostrarFechaLetras1($fecregdis) . ' con el No. ' . $numregdis . ' del libro ' . retornarLibroFormato2019($libregdis) . '.';
            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
            $pdf->writeHTML(\funcionesGenerales::limpiarTextosRedundantes($txt), true, false, true, false, 'J');
            $pdf->Ln();
        }
    }

    //
    if ($estextual == 'si') {
        return false;
    }
    return true;
}

// *************************************************************************** //
// Certifica de Vigilancia y control
// *************************************************************************** //
function armarCertificaVigilanciaControlFormato2019($mysqli, $pdf, $data) {
    if ($data["categoria"] == '1') {
        if (ltrim(trim($data["vigcontrol"]), "0") != '') {
            $nom = retornarNombreTablasSirepMysqliApi($mysqli, '43', $data["vigcontrol"]);
            if ($nom == '') {
                $nom = trim($data["vigcontrol"]);
            }
            if ($nom != '') {
                $nom = pasar_a_oracion($nom);
                if ($_SESSION["generales"]["TITULOS_EN_CERTIFICADOS_SII"] != 'NO') {
                    $txt = '<strong>CERTIFICA - ENTIDAD DE VIGILANCIA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                } else {
                    $txt = '<strong>CERTIFICA</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                $txt = 'Que la entidad que ejerce la función de inspección, vigilancia y control es ' . $nom;
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
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
function armarCertificaEmbargosFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $titulo = 'no';
    $certifica900 = 'no';

    //
    foreach ($data["inscripciones"] as $ins) {
        $inc = 'no';
        if ($ins["grupoacto"] == '018' || $ins["grupoacto"] == '039' || $ins["grupoacto"] == '077' || $ins["grupoacto"] == '088') {
            if ($ins["crev"] != '1' && $ins["crev"] != '9') {
                if ($ins["grupoacto"] == '018') {
                    foreach ($data["ctrembargos"] as $e) {
                        if ($e["libro"] == $ins["lib"] &&
                                $e["numreg"] == $ins["nreg"] &&
                                sprintf("%03s", $e["dupli"]) == sprintf("%03s", $ins["dupli"]) &&
                                $e["acto"] == $ins["acto"]) {
                            if ($e["estado"] == '1') {
                                if ($e["esembargo"] == 'S') {
                                    $inc = 'si';
                                }
                            }
                        }
                    }
                    if ($inc == 'no') {
                        foreach ($data["ctrembargos"] as $e) {
                            if ($e["libro"] == $ins["lib"] &&
                                    $e["numreg"] == $ins["nreg"] &&
                                    $e["acto"] == $ins["acto"]) {
                                if ($e["estado"] == '1') {
                                    if ($e["esembargo"] == 'S') {
                                        $inc = 'si';
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $inc = 'si';
                }
            }
        }
        if ($inc == 'si') {
            if ($titulo == 'no') {
                $titulo = 'si';
                $txt = '<strong>ORDENES DE AUTORIDAD COMPETENTE</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
            }
            $txMun = retornarNombreMunicipioMysqliApi($mysqli, $ins["idmunidoc"]);
            $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $ins["acto"], $ins["tdoc"], $ins["ndoc"], $ins["ndocext"], $ins["fdoc"], $ins["idoridoc"], $ins["txoridoc"], $ins["idmunidoc"], $ins["lib"], $ins["nreg"], $ins["freg"], $ins["not"], array(), '', '', $ins["camant"], $ins["libant"], $ins["regant"], $ins["fecant"], $ins["camant2"], $ins["libant2"], $ins["regant2"], $ins["fecant2"], $ins1["camant3"], $ins["libant3"], $ins["regant3"], $ins["fecant3"], $ins["camant4"], $ins["libant4"], $ins["regant4"], $ins["fecant4"], $ins["camant5"], $ins["libant5"], $ins["regant5"], $ins["fecant5"], $ins["acalaratoria"], $ins["tomo72"], $ins["folio72"], $ins["registro72"]);
            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
            if ($ins["grupoacto"] == '018') {
                $certifica900 = 'si';
            }
            $txt = '';
        }
    }
    if ($certifica900 == 'no') {
        $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CTR-EMBARGOS', 'EMBARGOS');
        if ($resx) {
            $certifica900 = 'si';
        }
    }
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-EMBARGOS', 'ACLARACION DE EMBARGOS');
    if ($certifica900 == 'no') {
        $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CTR-DEMANDAS');
    }
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-DEMANDAS', 'ACLARACION DE DEMANDAS');
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CTR-MEDCAU', 'MEDIDAS CAUTELARES');
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-MEDCAU', 'MEDIDAS CAUTELARES');
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-MEDCAU', 'MEDIDAS CAUTELARES');

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
}

function armarCertificaEmbargosFormato2019Anterior($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $titulo = 'no';
    $certifica900 = 'no';
    if (!empty($data["ctrembargos"])) {
        foreach ($data["ctrembargos"] as $e) {
            if ($e["estado"] == '1') {
                if ($e["esembargo"] == 'S') {
                    if (trim($e["numreg"]) != '') {
                        if ($titulo == 'no') {
                            $titulo = 'si';
                            $txt = '<strong>ORDENES DE AUTORIDAD COMPETENTE</strong>';
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
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $ins1["acto"], $ins1["tdoc"], $ins1["ndoc"], $ins1["ndocext"], $ins1["fdoc"], $ins1["idoridoc"], $ins1["txoridoc"], $ins1["idmunidoc"], $ins1["lib"], $ins1["nreg"], $ins1["freg"], $ins1["not"], array(), '', '', $ins1["camant"], $ins1["libant"], $ins1["regant"], $ins1["fecant"], $ins1["camant2"], $ins1["libant2"], $ins1["regant2"], $ins1["fecant2"], $ins1["camant3"], $ins1["libant3"], $ins1["regant3"], $ins1["fecant3"], $ins1["camant4"], $ins1["libant4"], $ins1["regant4"], $ins1["fecant4"], $ins1["camant5"], $ins1["libant5"], $ins1["regant5"], $ins1["fecant5"], $ins1["aclaratoria"], $ins1["tomo72"], $ins1["folio72"], $ins1["registro72"]);
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                        $certifica900 = 'si';
                        $txt = '';
                    }
                }
            }
        }
    }

    if ($certifica900 == 'no') {
        $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CTR-EMBARGOS', 'EMBARGOS');
        if ($resx) {
            $certifica900 = 'si';
        }
    }
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-EMBARGOS', 'ACLARACION DE EMBARGOS');
    if ($certifica900 == 'no') {
        $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CTR-DEMANDAS');
    }
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-DEMANDAS', 'ACLARACION DE DEMANDAS');
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CTR-MEDCAU', 'MEDIDAS CAUTELARES');
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-MEDCAU', 'MEDIDAS CAUTELARES');
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-MEDCAU', 'MEDIDAS CAUTELARES');

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
}

// *************************************************************************** //
// Revisa si los establecimientos históricos y cancelados tienen embargos
// *************************************************************************** //
function armarCertificaEmbargosEstablecimientosCanceladosFomato2019($mysqli, $pdf, $data) {
    //
    $sitiene = 'no';

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    if (isset($data["establecimientosc"]) && $data["establecimientosc"] && !empty($data["establecimientosc"])) {
        foreach ($data["establecimientosc"] as $h) {
            $embs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . $h["matriculaestablecimiento"] . "'", "fecinscripcion");
            if ($embs && !empty($embs)) {
                foreach ($embs as $emb) {
                    if ($emb["acto"] == '0900' || $emb["acto"] == '1000') {
                        if ($emb["ctrestadoembargo"] == '1') {
                            if ($sitiene == 'no') {
                                $txt = '<strong>EMBARGOS, DEMANDAS Y MEDIDAS CAUTELARES - SOBRE ESTABLECIMIENTOS CANCELADOS</strong>';
                                $pdf->writeHTML($txt, true, false, true, false, 'C');
                                $pdf->Ln();
                                $sitiene = 'si';
                            }
                            if ($emb["dupli"] == '') {
                                $insx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $emb["libro"] . "' and registro='" . $emb["numreg"] . "'");
                            } else {
                                $insx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $emb["libro"] . "' and registro='" . $emb["numreg"] . "' and dupli='" . $emb["dupli"] . "'");
                            }
                            if ($insx && !empty($insx)) {
                                $datae = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $h["matriculaestablecimiento"] . "'");
                                $txMun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $insx["municipiodocumento"] . "'", "ciudadminusculas");
                                $txt = '<strong>** MATRICULA ESTABLECIMIENTO : </strong>' . $h["matriculaestablecimiento"] . '<br>';
                                $txt .= '<strong>Nombre : </strong>' . htmlentities($datae["razonsocial"]) . '<br>';
                                $txt .= '<strong>Descripción de la medida cautelar : </strong>' . descripcionesEmbargosFormato2019($mysqli, $insx["acto"], $insx["tipodocumento"], $insx["numerodocumento"], $insx["numdocextenso"], $insx["fechadocumento"], $insx["idorigendoc"], $insx["origendocumento"], $txMun, $insx["libro"], $insx["registro"], $insx["fecharegistro"], $insx["noticia"], '', '', '', '', '', '');
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
// *************************************************************************** //
function armarCertificaEstablecimientosFormato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $tieneestablecimientos = 'no';

    //
    if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && ($data["categoria"] == '' || $data["categoria"] == '0' || $data["categoria"] == '1'))) { {
            if (!empty($data["establecimientos"]) || !empty($data["sucursalesagencias"])) {
                foreach ($data["establecimientos"] as $e) {
                    if ($e["estadodatosestablecimiento"] == 'MA' || $e["estadodatosestablecimiento"] == 'MI') {
                        $tieneestablecimientos = 'si';
                    }
                }
                if ($tieneestablecimientos != 'si') {
                    foreach ($data["sucursalesagencias"] as $e) {
                        if ($e["estado"] == 'MA' || $e["estado"] == 'MI') {
                            $tieneestablecimientos = 'si';
                        }
                    }
                }
            }
        }
    }

    //
    if ($tieneestablecimientos == 'si') {
        $txt = '<strong>ESTABLECIMIENTOS DE COMERCIO, SUCURSALES Y AGENCIAS</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        if ($data["organizacion"] == '01') {
            $txt = 'A nombre de la persona natural, ';
        } else {
            $txt = 'A nombre de la persona jurídica, ';
        }
        $txt .= 'figura(n) matriculado(s) en la ' . str_replace("CAMARA", "CÁMARA", RAZONSOCIAL) . ' el(los) siguiente(s) ';
        $txt .= 'establecimiento(s) de comercio:<br>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();

        //
        if (!empty($data["establecimientos"])) {
            $txt = '<strong>ESTABLECIMIENTOS DE COMERCIO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            foreach ($data["establecimientos"] as $e) {
                if ($e["estadodatosestablecimiento"] == 'MA' || $e["estadodatosestablecimiento"] == 'MI') {
                    $txt = '';
                    $txt .= 'Nombre: ' . htmlentities($e["nombreestablecimiento"]) . '<br>';
                    $txt .= 'Matrícula  No.: ' . $e["matriculaestablecimiento"] . '<br>';
                    $txt .= 'Fecha de Matrícula: ' . \funcionesGenerales::mostrarFechaLetras1($e["fechamatricula"]) . '<br>';
                    $txt .= 'Último año renovado: ' . $e["ultanoren"] . '<br>';
                    $txt .= 'Categoría: Establecimiento de Comercio<br>';
                    // $txtDireccion = pasar_a_oracion($e["dircom"]);
                    $txtDireccion = $e["dircom"];
                    if ($e["barriocom"] != '') {
                        $txtDireccion .= ' - ' . ucwords(strtolower(retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $e["muncom"] . "' and idbarrio='" . $e["barriocom"] . "'", "nombre")));
                    }
                    $txt .= 'Dirección  : ' . $txtDireccion . '<br>';
                    $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $e["muncom"] . "'");
                    $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($e["muncom"], 0, 2) . "'");
                    if ($tdep === false || empty($tdep)) {
                        $tdep = array('nombre' => '');
                    }
                    if ($tmun && !empty($tmun)) {
                        $txt .= 'Municipio: ' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"];
                    }
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    incluirEmbargos($mysqli, $pdf, $e["matriculaestablecimiento"]);
                    $pdf->Ln();
                }
            }
        }

        //
        if (!empty($data["sucursalesagencias"])) {
            $txt = '<strong>SUCURSALES Y AGENCIAS</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            foreach ($data["sucursalesagencias"] as $e) {
                if ($e["estado"] == 'MA' || $e["estado"] == 'MI') {
                    $catx = '';
                    if ($e["categoria"] == '2') {
                        $catx = 'Sucursal';
                    }
                    if ($e["categoria"] == '3') {
                        $catx = 'Agencia';
                    }
                    $txt = '';
                    $txt .= 'Nombre: ' . htmlentities($e["nombresucage"]) . '<br>';
                    $txt .= 'Matrícula  No.: ' . $e["matriculasucage"] . '<br>';
                    $txt .= 'Fecha de Matrícula: ' . \funcionesGenerales::mostrarFechaLetras1($e["fechamatricula"]) . '<br>';
                    $txt .= 'Último año renovado: ' . $e["ultanoren"] . '<br>';
                    $txt .= 'Categoría: ' . $catx . '<br>';
                    // $txtDireccion = pasar_a_oracion($e["dircom"]);
                    $txtDireccion = $e["dircom"];
                    if ($e["barriocom"] != '') {
                        $txtDireccion .= ' - ' . ucwords(strtolower(retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $e["muncom"] . "' and idbarrio='" . $e["barriocom"] . "'", "nombre")));
                    }
                    $txt .= 'Dirección  : ' . $txtDireccion . '<br>';
                    $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $e["muncom"] . "'");
                    $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($e["muncom"], 0, 2) . "'");
                    if ($tdep === false || empty($tdep)) {
                        $tdep = array('nombre' => '');
                    }
                    if ($tmun && !empty($tmun)) {
                        $txt .= 'Municipio: ' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"];
                    }
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    incluirEmbargos($mysqli, $pdf, $e["matriculasucage"]);
                    $pdf->Ln();
                }
            }
        }

        // $pdf->Ln();
        $txt = '<br>SI DESEA OBTENER INFORMACIÓN DETALLADA DE LOS ANTERIORES ESTABLECIMIENTOS DE COMERCIO O DE AQUELLOS MATRICULADOS ';
        $txt .= 'EN UNA JURISDICCIÓN DIFERENTE A LA DEL PROPIETARIO, DEBERÁ SOLICITAR EL CERTIFICADO DE MATRÍCULA MERCANTIL DEL ';
        $txt .= 'RESPECTIVO ESTABLECIMIENTO DE COMERCIO';
        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
    }


    if ($data["claseespesadl"] != '61') {
        if ($data["organizacion"] == '01' || ($data["organizacion"] > '02' && ($data["categoria"] == '' || $data["categoria"] == '0' || $data["categoria"] == '1'))) {
            $txt = '<br>LA INFORMACIÓN CORRESPONDIENTE A LOS ESTABLECIMIENTOS DE COMERCIO, AGENCIAS Y SUCURSALES, QUE LA ';
            if ($data["organizacion"] == '01') {
                $txt .= 'PERSONA NATURAL ';
            } else {
                $txt .= 'PERSONA JURÍDICA ';
            }
            $txt .= 'TIENE MATRICULADOS EN OTRAS CÁMARAS DE COMERCIO DEL PAÍS, PODRÁ CONSULTARLA EN WWW.RUES.ORG.CO';
            $txt = \funcionesGenerales::agregarPuntoFinal($txt);
            $pdf->writeHTML($txt, true, false, true, false, 'J');
            $pdf->Ln();
        }
    }
}

function incluirEmbargos($mysqli, $pdf, $mat) {
    $cant = 0;
    $embs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . $mat . "'", "fecinscripcion");
    if ($embs && !empty($embs)) {
        foreach ($embs as $emb) {
            if ($emb["ctrestadoembargo"] == '1') {
                $act = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $emb["libro"] . "' and idacto='" . $emb["acto"] . "'");
                if ($act && !empty($act) && ($act["idgrupoacto"] == '018' || $act["idgrupoacto"] == '051')) {

                    $est = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat . "'", "matricula,organizacion,categoria");
                    if (trim($emb["dupli"]) == '') {
                        $insc = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $emb["libro"] . "' and registro='" . $emb["numreg"] . "' and matricula='" . $mat . "'");
                    } else {
                        $insc = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $emb["libro"] . "' and registro='" . $emb["numreg"] . "' and dupli='" . $emb["dupli"] . "'  and matricula='" . $mat . "'");
                    }
                    if ($insc && !empty($insc)) {
                        if ($insc["fechalimite"] == '' || ($insc["fechalimite"] != '' && date("Ymd") < $insc["fechalimite"])) {
                            $txt = '** Embargo, medida cautelar o prohibición: ' . descripcionesFormato2019($mysqli, $est["organizacion"], $emb["acto"], $insc["tipodocumento"], $insc["numerodocumento"], '', $insc["fechadocumento"], $insc["idorigendoc"], $insc["origendocumento"], $insc["municipiodocumento"], $insc["libro"], $insc["registro"], $insc["fecharegistro"], $insc["noticia"], array(), '', '', $insc["camaraanterior"], $insc["libroanterior"], $insc["registroanterior"], $insc["fecharegistroanterior"], $insc["camaraanterior2"], $insc["libroanterior2"], $insc["registroanterior2"], $insc["fecharegistroanterior2"], $insc["camaraanterior3"], $insc["libroanterior3"], $insc["registroanterior3"], $insc["fecharegistroanterior3"], $insc["camaraanterior4"], $insc["libroanterior4"], $insc["registroanterior4"], $insc["fecharegistroanterior4"], $insc["camaraanterior5"], $insc["libroanterior5"], $insc["registroanterior5"], $insc["fecharegistroanterior5"], $insc["aclaratoria"], $insc["tomo72"], $insc["folio72"], $insc["registro72"]);
                            $txt = \funcionesGenerales::agregarPuntoFinal(\funcionesGenerales::limpiarTextosRedundantes($txt));
                            $pdf->writeHTML($txt, true, false, true, false, 'J');
                            $cant++;
                        }
                    }
                }
            }
        }
    }
    if ($cant == 0) {
        return false;
    } else {
        return true;
    }
}

// *************************************************************************** //
// Revisa establecimientos de comercio
// *************************************************************************** //
function armarCertificaEstablecimientosArrendadosFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>ESTABLECIMIENTOS QUE TIENE EN ARRIENDO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'Que el comerciante tiene en arriendo los siguientes establecimientos de comercio :';
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
        foreach ($data["establecimientosarrendados"] as $e) {
            $txt = '<br><strong>*** Nombre del Establecimiento : </strong>' . htmlentities($e["nombreestablecimiento"]) . '<br>';
            $txt .= '<strong>Matrícula  : </strong>' . $e["matriculaestablecimiento"] . '<br>';
            $txt .= '<strong>Fecha de Matrícula : </strong>' . $e["fechamatricula"] . '<br>';
            $txt .= '<strong>Fecha d renovación  : </strong>' . $e["fecharenovacion"] . '<br>';
            $txt .= '<strong>Último año renovado  : </strong>' . $e["ultanoren"] . '<br>';
            // $txt .= '<strong>Dirección  : </strong>' . pasar_a_oracion($e["dircom"]) . '<br>';
            $txt .= '<strong>Dirección  : </strong>' . $e["dircom"] . '<br>';
            if (trim($e["barriocom"]) != '') {
                $txt .= '<strong>Barrio  : </strong>' . retornarNombreBarrioMysqliApi($mysqli, $e["muncom"], $e["barriocom"]) . '<br>';
            }
            $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $e["muncom"] . "'");
            $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($e["muncom"], 0, 2) . "'");
            if ($tdep === false || empty($tdep)) {
                $tdep = array('nombre' => '');
            }
            if ($tmun && !empty($tmun)) {
                $txt .= '<strong>Municipio  : </strong>' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"] . '<br>';
            }
            if ($e["telcom1"] != '') {
                $txt .= '<strong>Teléfono 1  : </strong>' . $e["telcom1"] . '<br>';
            }
            if ($e["telcom2"] != '') {
                $txt .= '<strong>Teléfono 2  : </strong>' . $e["telcom2"] . '<br>';
            }
            if ($e["telcom3"] != '') {
                $txt .= '<strong>Teléfono 3  : </strong>' . $e["telcom3"] . '<br>';
            }
            if ($e["emailcom"] != '') {
                $txt .= '<strong>Correo electrónmico  : </strong>' . $e["emailcom"] . '<br>';
            }
            $txt .= '<strong>Actividad principal : </strong>' . $e["ciiu1"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $e["ciiu1"]);
            if ($e["ciiu2"] != '') {
                $txt .= '<br><strong>Actividad secundaria : </strong>' . $e["ciiu2"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $e["ciiu2"]);
            }
            if ($e["ciiu3"] != '') {
                $txt .= '<br><strong>Otras actividades : </strong>' . $e["ciiu3"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $e["ciiu3"]);
            }
            if ($e["ciiu4"] != '') {
                $txt .= '<br><strong>Otras actividades : </strong>' . $e["ciiu4"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $e["ciiu4"]);
            }
            $txt .= '<br><strong>Valor del establecimiento : </strong>' . number_format($e["valest"]);
            if ($e["embargado"] == 'SI') {
                $txt .= '<br><strong>EMBARGOS, DEMANDAS Y MEDIDAS CAUTELARES</strong>';
                foreach ($e["embargos"] as $em) {
                    $txt .= '<br><strong>** Libro : </strong>' . $em["libroembargo"] .
                            ', <strong>Inscripción: </strong>' . $em["registroembargo"] .
                            ', <strong>Fecha: </strong>' . $em["fechaembargo"] .
                            ', <strong>Origen: </strong>' . $em["txtorigenembargo"] .
                            ', <strong>Noticia: </strong>' . pasar_a_oracion($em["noticiaembargo"]);
                }
            }
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }
    }
}

// *************************************************************************** //
// Revisa sucursales y agencias
// *************************************************************************** //
function armarCertificaSucursalesAgenciasFormato2019($mysqli, $pdf, $data) {
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
            $txt = '<strong>SUCURSALES Y AGENCIAS</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        } else {
            $txt = '<strong>CERTIFICA</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
        }
        $txt = 'Que es propietario de las siguientes sucursales y agencias en la jurisdicción de esta Cámara de Comercio';
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
                $txt = '<br><strong>*** Nombre : </strong>' . $e["nombresucage"] . '<br>';
                $txt .= '<strong>Categoria : </strong>' . $xcat . '<br>';
                $txt .= '<strong>Matrícula  : </strong>' . $e["matriculasucage"] . '<br>';
                $txt .= '<strong>Fecha de matrícula  : </strong>' . $e["fechamatricula"] . '<br>';
                $txt .= '<strong>Fecha de renovación  : </strong>' . $e["fecharenovacion"] . '<br>';
                $txt .= '<strong>Último año renovado  : </strong>' . $e["ultanoren"] . '<br>';
                // $txt .= '<strong>Dirección  : </strong>' . pasar_a_oracion($e["dircom"]) . '<br>';
                $txt .= '<strong>Dirección  : </strong>' . $e["dircom"] . '<br>';
                $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $e["muncom"] . "'");
                $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($e["muncom"], 0, 2) . "'");
                if ($tdep === false || empty($tdep)) {
                    $tdep = array('nombre' => '');
                }
                if ($tmun && !empty($tmun)) {
                    $txt .= '<strong>Municipio  : </strong>' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"] . '<br>';
                }
                if ($e["telcom1"] != '') {
                    $txt .= '<strong>Teléfono 1  : </strong>' . $e["telcom1"] . '<br>';
                }
                if ($e["telcom2"] != '') {
                    $txt .= '<strong>Teléfono 2  : </strong>' . $e["telcom2"] . '<br>';
                }
                if ($e["telcom3"] != '') {
                    $txt .= '<strong>Teléfono 3  : </strong>' . $e["telcom3"] . '<br>';
                }
                if ($e["emailcom"] != '') {
                    $txt .= '<strong>Correo electrónico  : </strong>' . $e["emailcom"] . '<br>';
                }
                $txt .= '<strong>Actividad principal : </strong>' . $e["ciiu1"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $e["ciiu1"]);
                if ($e["ciiu2"] != '') {
                    $txt .= '<br><strong>Actividad secundaria : </strong>' . $e["ciiu2"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $e["ciiu2"]);
                }
                if ($e["ciiu3"] != '') {
                    $txt .= '<br><strong>Otras actividades : </strong>' . $e["ciiu3"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $e["ciiu3"]);
                }
                if ($e["ciiu4"] != '') {
                    $txt .= '<br><strong>Otras actividades : </strong>' . $e["ciiu4"] . ' - ' . retornarDescripcionCiiuMysqliApi($mysqli, $e["ciiu4"]);
                }
                $txt .= '<br><strong>Activos vinculados : </strong>' . number_format($e["actvin"]);
                if ($e["embargado"] == 'SI') {
                    $txt .= '<br><strong>EMBARGOS, DEMANDAS Y MEDIDAS CAUTELARES</strong>';
                    foreach ($e["embargos"] as $em) {
                        $txt .= '<br><strong>** Libro : </strong>' . $em["libroembargo"] .
                                ', <strong>Inscripción: </strong>' . $em["registroembargo"] .
                                ', <strong>Fecha: </strong>' . $em["fechaembargo"] .
                                ', <strong>Origen: </strong>' . $em["txtorigenembargo"] .
                                ', <strong>Noticia: </strong>' . pasar_a_oracion($em["noticiaembargo"]);
                    }
                }
                $pdf->writeHTML($txt, true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Poderes (certifica 1410 y 1500)
// *************************************************************************** //
function armarCertificaCambiosNombreEstablecimientosFormato2019($mysqli, $pdf, $data) {
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
                            $txt = '<strong>ACTUALIZACIÓN DE DATOS</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        } else {
                            $txt = '<strong>CERTIFICA</strong>';
                            $pdf->writeHTML($txt, true, false, true, false, 'C');
                            $pdf->Ln();
                        }
                    }
                    $txt = 'Que el' . strtoupper(\funcionesGenerales::mostrarFechaLetras1($insc["freg"])) . ' se registró para el establecimiento ';
                    $txt .= 'la siguiente actualización : ' . pasar_a_oracion($insc["not"]);
                    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
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
function armarCertificaPoderesFormato2019($mysqli, $pdf, $data) {
    //  $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'CERTIFICA - PODERES');
    // $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-PODER', 'CERTIFICA - ACLARACION PODERES');
    // $resx = armarCertificaTextoLibrePoderesFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'CERTIFICA - PODERES');
    // $resx = armarCertificaTextoLibrePoderesFormato2019($mysqli, $pdf, $data, 'AC-PODER', 'CERTIFICA - ACLARACION PODERES');

    $resx = armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'CRT-PODER', 'CERTIFICA - PODERES');
    $resx = armarCertificaTextoLibreClaseMultiCellFormato2019($mysqli, $pdf, $data, 'AC-PODER', 'CERTIFICA - ACLARACION PODERES');
}

// *************************************************************************** //
// Revisa propietarios
// *************************************************************************** //
function armarCertificaPropietariosFormato2019($mysqli, $pdf, $data) {
    if ($data["organizacion"] != '02') {
        return false;
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
        $txt = '<strong>PROPIETARIOS</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        if ($data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'MC') {
            $txt = 'Que el(los) propietario(s) del establecimiento de comercio fue(ron): ';
        } else {
            $txt = 'Que la propiedad sobre el establecimiento de comercio la tiene(n) la(s) siguiente(s) persona(s) natural(es) o jurídica(s) ';
            if ($sociedadhecho == 'si') {
                $txt .= '(en sociedad de hecho) ';
            }
            $txt .= ': ';
        }
        $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
        $nomx = '';
        foreach ($data["propietarios"] as $e) {

            $nomx = $e["nombrepropietario"];
            $nom1 = $e["nombrepropietario"];
            /*
              if ($e["organizacionpropietario"] == '01') {
              $nom1 = $e["nom1propietario"];
              if (trim($e["nom2propietario"]) != '') {
              $nom1 .= ' ' . trim($e["nom2propietario"]);
              }
              if (trim($e["ape1propietario"]) != '') {
              $nom1 .= ' ' . trim($e["ape1propietario"]);
              }
              if (trim($e["ape2propietario"]) != '') {
              $nom1 .= ' ' . trim($e["ape2propietario"]);
              }
              }
             */
            if (trim((string) $nomx) != '' && trim((string) $nom1) != '') {
                $txt = '<strong>*** Nombre : </strong>' . $nom1 . '<br>';
            }
            if (trim((string) $nomx) == '' && trim((string) $nom1) != '') {
                $txt = '<strong>*** Nombre : </strong>' . $nom1 . '<br>';
            }
            if (trim((string) $nomx) != '' && trim((string) $nom1) == '') {
                $txt = '<strong>*** Nombre : </strong>' . $nomx . '<br>';
            }
            if ($e["idtipoidentificacionpropietario"] == '2') {
                $sp = \funcionesGenerales::separarDv($e["identificacionpropietario"]);
                $txt .= '<strong>Nit  : </strong>' . $sp["identificacion"] . '-' . $sp["dv"] . '<br>';
            } else {
                $txt .= '<strong>Identificación  : </strong>' . retornarTxtTipoIdeFormato2019($e["idtipoidentificacionpropietario"]) . ' - ' . $e["identificacionpropietario"] . '<br>';
                if (ltrim($e["nitpropietario"], "0") != '') {
                    $sp = \funcionesGenerales::separarDv($e["nitpropietario"]);
                    $txt .= '<strong>Nit  : </strong>' . $sp["identificacion"] . '-' . $sp["dv"] . '<br>';
                }
            }
            $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $e["municipiopropietario"] . "'");
            $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($e["municipiopropietario"], 0, 2) . "'");
            if ($tmun && !empty($tmun)) {
                if ($e["municipiopropietario"] != '99999') {
                    $txt .= '<strong>Domicilio  : </strong>' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"] . '<br>';
                } else {
                    $txt .= '<strong>Domicilio  : </strong>Fuera del País<br>';
                }
            }
            if ($e["matriculapropietario"] != '') {
                $txt .= '<strong>Matrícula/inscripción No. : </strong>' . $e["camarapropietario"] . '-' . $e["matriculapropietario"] . '<br>';
            }
            if ($e["matriculapropietario"] != '' && $e["camarapropietario"] == CODIGO_EMPRESA) {
                $txt .= '<strong>Fecha de matrícula/inscripción  : </strong>' . \funcionesGenerales::mostrarFechaLetras1($e["fecmatripropietario"]) . '<br>';
                $txt .= '<strong>Último año renovado : </strong>' . $e["ultanorenpropietario"] . '<br>';
                $txt .= '<strong>Fecha de renovación  : </strong>' . \funcionesGenerales::mostrarFechaLetras1($e["fecrenovpropietario"]) . '<br>';
            }
            if ($e["estadomatriculapropietario"] == 'MC' || $e["estadomatriculapropietario"] == 'IC') {
                $txt .= '<strong>Estado  : </strong>Cancelado<br>';
            }
            if (count($data["propietarios"]) > 1) {
                if ($e["participacionpropietario"] != 0 && $e["participacionpropietario"] != 100) {
                    $txt .= '<strong>Participación : </strong>' . number_format($e["participacionpropietario"], 2) . '%<br>';
                }
            }
            $pdf->writeHTML($txt, true, false, true, false, 'L');
            $pdf->Ln();
        }
    }
}

// *************************************************************************** //
// Casa principal
// *************************************************************************** //
function armarCertificaCasaPrincipalFormato2019($mysqli, $pdf, $data) {
    if ($data["organizacion"] != '08') {
        if ($data["categoria"] != '2' && $data["categoria"] != '3') {
            return false;
        }
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $txt = '<strong>PROPIETARIO - CASA PRINCIPAL</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
    $txt = '<strong>Nombre de la persona jurídica propietaria (Casa Principal): </strong>' . $data["cprazsoc"] . '<br>';
    if ($data["cpnummat"] != '') {
        $txt .= '<strong>Matrícula/inscripción  : </strong>' . $data["cpcodcam"] . '-' . $data["cpnummat"] . '<br>';
    }
    if ($data["cpnumnit"] != '') {
        $sp = \funcionesGenerales::separarDv($data["cpnumnit"]);
        $txt .= '<strong>Nit/Identificación  : </strong>' . $sp["identificacion"] . '-' . $sp["dv"] . '<br>';
    }
    // $txt .= '<strong>Dirección  : </strong>' . pasar_a_oracion($data["cpdircom"]) . '<br>';
    $txt .= '<strong>Dirección  : </strong>' . $data["cpdircom"] . '<br>';
    if ($data["cpnumtel"] != '') {
        $txt .= '<strong>Teléfono  : </strong>' . pasar_a_oracion($data["cpnumtel"]) . '<br>';
    }
    if ($data["cpcodmun"] != '' && $data["cpcodmun"] != '99999' && $data["cpcodmun"] != '00001') {
        if (is_numeric($data["cpcodmun"])) {
            $tmun = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $data["cpcodmun"] . "'");
            $tdep = retornarRegistroMysqliApi($mysqli, 'bas_departamentos', "id='" . substr($data["cpcodmun"], 0, 2) . "'");
            if ($tmun && !empty($tmun)) {
                $txt .= '<strong>Domicilio Casa Principal  : </strong>' . $tmun["ciudadminusculas"] . ', ' . $tdep["nombre"] . '<br>';
            }
        } else {
            $txt .= '<strong>Domicilio Casa Principal  : </strong>' . $data["cpcodmun"] . '<br>';
        }
    }
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    $pdf->Ln();
}

// Contratos
function armarCertificaContratosFormato2019($mysqli, $pdf, $data, $nameLog = '') {
    // if ($data["organizacion"] != '02' && $data["categoria"] != '3') {
    //    return false;
    // }
    // ******************************************************************************** //
    // Certifica contratos
    // ******************************************************************************** //
    $titulo = 'no';
    foreach ($data["inscripciones"] as $insc) {
        if ($insc["grupoacto"] == '012' || $insc["grupoacto"] == '021' || $insc["grupoacto"] == '036' || $insc["grupoacto"] == '037' || $insc["grupoacto"] == '038' || $insc["grupoacto"] == '048' || $insc["grupoacto"] == '076') {
            if ($insc["crev"] != '1' && $insc["crev"] != '9') {
                if ($titulo == 'no') {
                    $titulo = 'si';
                    $txt = '<strong>CONTRATOS</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }
                $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $insc["acto"], $insc["tdoc"], $insc["ndoc"], $insc["ndocext"], $insc["fdoc"], $insc["idoridoc"], $insc["txoridoc"], $insc["idmunidoc"], $insc["lib"], $insc["nreg"], $insc["freg"], $insc["not"], '', '', '', '', '', '', '', '', '', '', '', '', '', '', $insc["aclaratoria"], $insc["tomo72"], $insc["folio72"], $insc["registro72"]);
                $txt = \funcionesGenerales::limpiarTextosRedundantes($txt);
                $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                $pdf->writeHTML(str_replace(array(chr(13) . chr(10), chr(13), chr(10)), "<br>", $txt), true, false, true, false, 'J');
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Certifica de prendas - en texto certifica 1010
// *************************************************************************** //
function armarCertificaPrendasFormato2019($mysqli, $pdf, $data) {
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'CRT-PRENDAS', 'CERTIFICA - PRENDAS');
}

// *************************************************************************** //
// Certifica Aclaratoria del patrimonio
// En caso de manizales, si existe certifica 8001 (Capital SAT)
// quita el 0761
// *************************************************************************** //
function armarCertificaAclaratoriaCapitalPatrimonioFormato2019($mysqli, $pdf, $data) {
    if (isset($data["crtsii"]["8001"]) && trim($data["crtsii"]["8001"]) != '') {
        unset($data["crtsii"]["0761"]);
        unset($data["crt"]["0761"]);
    }
    $resx = armarCertificaTextoLibreClaseFormato2019($mysqli, $pdf, $data, 'AC-CAPSOC', 'CERTIFICA - ACLARATORIA CAPITAL Y PATRIMONIOS');
}

// *************************************************************************** //
// Certifica de objeto social - Inscripciones antes del 72
// *************************************************************************** //
function armarCertificaCaeFomato2019($mysqli, $pdf, $data) {

    //
    if ($data["claseespesadl"] == '61') {
        return true;
    }

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
        $txtCuerpo = str_replace("[FECHAENVIO]", \funcionesGenerales::mostrarFechaLetras1($data["placaalcaldiafecha"]), $txtCuerpo);
        $txtCuerpo = str_replace("[PLACA]", $data["placaalcaldia"], $txtCuerpo);
        $txtCuerpo = \funcionesGenerales::limpiarTextosRedundantes(\funcionesGenerales::parsearOracion($txtCuerpo));
        $txt = '<strong>INFORMACIÓN COMPLEMENTARIA - REPORTE A ENTIDADES</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->writeHTML($txtCuerpo, true, false, true, false, 'J');
        $pdf->Ln();
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
    $txtCuerpo = \funcionesGenerales::limpiarTextosRedundantes(\funcionesGenerales::parsearOracion($txtCuerpo));

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
            $txtCuerpo .= \funcionesGenerales::mostrarFechaLetras1($data["placaalcaldiafecha"]) . ", PARA IDENTIFICAR ESTE NÚMERO DE MATRÍCULA MERCANTIL.";
            $txtCuerpo = \funcionesGenerales::limpiarTextosRedundantes(\funcionesGenerales::parsearOracion($txtCuerpo));
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
        $txtCuerpo = \funcionesGenerales::limpiarTextosRedundantes(\funcionesGenerales::parsearOracion($txtCuerpo));
    }


    //
    if ($txtCuerpo != '') {
        $txt = '<strong>INFORMACIÓN COMPLEMENTARIA - REPORTE A ENTIDADES</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $txtCuerpo = \funcionesGenerales::agregarPuntoFinal($txtCuerpo);
        $pdf->writeHTML($txtCuerpo, true, false, true, false, 'J');
        $pdf->Ln();
    }
}

// *************************************************************************** //
// Certifica de información complementaria
// *************************************************************************** //
function armarCertificaInformacionComplementariaFomato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $txtCuerpo = '';
    if ($data["claseespesadl"] != '61') {
        $txtCuerpo = \funcionesGenerales::cambiarSustitutoHtml(retornarPantallaPredisenadaMysqliApi($mysqli, 'informacion.complementaria'));
    }

    //                                                       
    if (trim($txtCuerpo) != '') {
        $txt = '<strong>INFORMACIÓN COMPLEMENTARIA</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $txtCuerpo = \funcionesGenerales::agregarPuntoFinal($txtCuerpo);
        $pdf->writeHTML($txtCuerpo, true, false, true, false, 'J');
        $pdf->Ln();
        return true;
    }
}

// *************************************************************************** //
// Certifica de objeto social - Inscripciones antes del 72
// *************************************************************************** //
function armarCertificaInspeccionEsadlFomato2019($mysqli, $pdf, $data) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($data["categoria"] == '1') {
        $txtCuerpo = "La persona jurídica de que trata est certificado se encuentra sujeta a la inspección, vigilancia y ";
        $txtCuerpo .= "control de las autoridades que ejercen esta función, por lo tanto ";
        $txtCuerpo .= "deberá presentar ante la autoridad correspondiente, el certificado ";
        $txtCuerpo .= "de registro respectivo, expedido por la Cámara de Comercio, dentro de los 10 días ";
        $txtCuerpo .= "hábiles siguientes a la fecha de inscripción, m+as el término de la distancia, ";
        $txtCuerpo .= "cuando el domicilio de la persona jurídica sin ánimo de lucro que se registra es ";
        $txtCuerpo .= "diferente al de la Cámara de Comercio que le corresponde. En el caso de reformas ";
        $txtCuerpo .= "estatutarias además se allegará copia de los estatutos. Toda autorización, permiso, ";
        $txtCuerpo .= "licencia o reconocimiento de carácter oficial, se tramitará con posterioridad a la ";
        $txtCuerpo .= "inscripción de las personas jurídicas sin ánimo de lucro en la respectiva Cámara de Comercio.";
        if ($txtCuerpo != "") {
            $txt = '<strong>IMPORTANTE</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $txtCuerpo = \funcionesGenerales::agregarPuntoFinal($txtCuerpo);
            $pdf->MultiCell(185, 4, $txtCuerpo . chr(10) . chr(13) . chr(10) . chr(13), 0, 'J', 0);
        }
    }
}

// *************************************************************************** //
// Certifica de capital
// *************************************************************************** //
function armarCertificaCapitalFormato2019($mysqli, $pdf, $data) {

    if ($data["estadomatricula"] == 'MC' || $data["estadomatricula"] == 'MF' || $data["estadomatricula"] == 'IC' || $data["estadomatricula"] == 'IF') {
        return true;
    }

    if ($data["organizacion"] == '01' || $data["organizacion"] == '02') {
        return true;
    }

    if ($data["categoria"] != '1') {
        return true;
    }

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $titpat = 'no';

    //
    if ($data["organizacion"] == '12' || $data["organizacion"] == '14') {
        if ($data["patrimonioesadl"] != 0) {
            $txt = '<strong>PATRIMONIO</strong>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->writeHTML('$ ' . truncarDecimalesFormato2019($data["patrimonioesadl"]), true, false, true, false, 'L');
            $pdf->Ln();
            $titpat = 'si';
        }
    } else {

        //
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
                $txt = '<strong>CAPITAL</strong>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
                $pdf->Ln();
                $titpat = 'si';

                if ($data["monedacap"] != '' && $data["monedacap"] != '001') {
                    $txt = '<strong>EL CAPITAL ESTÁ EXPRESADO EN ' . retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='10' and idcodigo='" . $data["monedacap"] . "'", "descripcion") . '</strong>';
                    $pdf->writeHTML($txt, true, false, true, false, 'C');
                    $pdf->Ln();
                }

                $simbolo = '$';
                if ($data["monedacap"] == '002') {
                    $simbolo = 'US$';
                }

                //
                $otroscapitales = 'si';

                // Si son anonimas o civiles anónimas
                if ($data["organizacion"] == '04' ||
                        $data["organizacion"] == '07' ||
                        $data["organizacion"] == '16' ||
                        ($data["organizacion"] == '10' && $data["capaut"] != 0)) {

                    if ($data["cuoaut"] != 0) {
                        if ($data["nomaut"] != 0) {
                            $valnom = $data["nomaut"];
                        } else {
                            $valnom = $data["capaut"] / $data["cuoaut"];
                        }
                    } else {
                        $valnom = 0;
                    }

                    $pdf->writeHTML('* CAPITAL AUTORIZADO *', true, false, true, false, 'C');
                    $txt = '<table>';
                    $txt .= '<tr>';
                    $txt .= '<td width="30%" align="left">Valor</td>';
                    $txt .= '<td width="70%" align="left">' . $simbolo . ' ' . truncarDecimalesFormato2019($data["capaut"]) . '</td>';
                    $txt .= '</tr>';
                    $txt .= '<tr>';
                    $txt .= '<td width="30%" align="left">No. Acciones</td>';
                    $txt .= '<td width="70%" align="left">' . truncarDecimalesFormato2019($data["cuoaut"], 'T') . '</td>';
                    $txt .= '</tr>';
                    $txt .= '<tr>';
                    $txt .= '<td width="30%" align="left">Valor Nominal Acciones</td>';
                    $txt .= '<td width="70%" align="left">' . $simbolo . ' ' . truncarDecimalesFormato2019($valnom, 'T') . '</td>';
                    $txt .= '</tr>';
                    $txt .= '</table>';
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();

                    if ($data["cuosus"] != 0) {
                        if ($data["nomsus"] != 0) {
                            $valnom = $data["nomsus"];
                        } else {
                            $valnom = $data["capsus"] / $data["cuosus"];
                        }
                    } else {
                        $valnom = 0;
                    }
                    $pdf->writeHTML('* CAPITAL SUSCRITO *', true, false, true, false, 'C');
                    $txt = '<table>';
                    $txt .= '<tr>';
                    $txt .= '<td width="30%" align="left">Valor</td>';
                    $txt .= '<td width="70%" align="left">' . $simbolo . ' ' . truncarDecimalesFormato2019($data["capsus"]) . '</td>';
                    $txt .= '</tr>';
                    $txt .= '<tr>';
                    $txt .= '<td width="30%" align="left">No. Acciones</td>';
                    $txt .= '<td width="70%" align="left">' . truncarDecimalesFormato2019($data["cuosus"], 'T') . '</td>';
                    $txt .= '</tr>';
                    $txt .= '<tr>';
                    $txt .= '<td width="30%" align="left">Valor Nominal Acciones</td>';
                    $txt .= '<td width="70%" align="left">' . $simbolo . ' ' . truncarDecimalesFormato2019($valnom, 'T') . '</td>';
                    $txt .= '</tr>';
                    $txt .= '</table>';
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();

                    if ($data["cuopag"] != 0) {
                        if ($data["nompag"] != 0) {
                            $valnom = $data["nompag"];
                        } else {
                            $valnom = $data["cappag"] / $data["cuopag"];
                        }
                        $pdf->writeHTML('* CAPITAL PAGADO *', true, false, true, false, 'C');
                        $txt = '<table>';
                        $txt .= '<tr>';
                        $txt .= '<td width="30%" align="left">Valor</td>';
                        $txt .= '<td width="70%" align="left">' . $simbolo . ' ' . truncarDecimalesFormato2019($data["cappag"]) . '</td>';
                        $txt .= '</tr>';
                        $txt .= '<tr>';
                        $txt .= '<td width="30%" align="left">No. Acciones</td>';
                        $txt .= '<td width="70%" align="left">' . truncarDecimalesFormato2019($data["cuopag"], 'T') . '</td>';
                        $txt .= '</tr>';
                        $txt .= '<tr>';
                        $txt .= '<td width="30%" align="left">Valor Nominal Acciones</td>';
                        $txt .= '<td width="70%" align="left">' . $simbolo . ' ' . truncarDecimalesFormato2019($valnom, 'T') . '</td>';
                        $txt .= '</tr>';
                        $txt .= '</table>';
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    } else {
                        $pdf->writeHTML('* CAPITAL PAGADO *', true, false, true, false, 'C');
                        $txt = '<table>';
                        $txt .= '<tr>';
                        $txt .= '<td width="30%" align="left">Valor</td>';
                        $txt .= '<td width="70%" align="left">' . $simbolo . ' ' . truncarDecimalesFormato2019($data["cappag"]) . '</td>';
                        $txt .= '</tr>';
                        $txt .= '</table>';
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }

                    //    
                    $otroscapitales = 'no';
                }

                // Si se tatra de sucursales de sociedades extranjeras
                if ($data["organizacion"] == '08') {
                    if ($data["monedacap"] == '001') {
                        $txt = 'Capital asignado a la sucursal : ' . $simbolo . ' ' . number_format($data["capsuc"], 2, ',', '.');
                    }
                    if ($data["monedacap"] == '002') {
                        $txt = 'Capital asignado a la sucursal en dólares de los Estados Unidos : ' . number_format($data["capsuc"], 2, ',', '.') . '<br><br>';
                        $txt .= 'Que el capital asignado para el funcionamiento de la sucursal en Colombia es la cantidad equivalente en pesos ';
                        $txt .= 'Colombianos de US$ ' . number_format($data["capsuc"], 2, ',', '.') . ' convertidos a tasa representativa del mercado en la ';
                        $txt .= 'fecha que la cantidad de dólares es negociada con cualquier otro banco local.';
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
                    $txt .= '<td width="20%">' . truncarDecimalesFormato2019($data["cap_apolab"]) . '</td>';
                    $txt .= '<td width="20%">' . truncarDecimalesFormato2019($data["cap_apolabadi"]) . '</td>';
                    $txt .= '<td width="20%">' . truncarDecimalesFormato2019($data["cap_apoact"]) . '</td>';
                    $txt .= '<td width="20%">' . truncarDecimalesFormato2019($data["cap_apodin"]) . '</td>';
                    $txt .= '<td width="20%">' . truncarDecimalesFormato2019($totapo) . '</td>';
                    $txt .= '</tr>';
                    $txt .= '</table>';
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();

                    $txt = 'Distribuido así:';
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();

                    // $txt = '';
                    foreach ($data["vinculos"] as $v) {
                        if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1110' || $v["vinculootros"] == '1170') {
                            $txt = '';
                            $vlab = $v["va1"];
                            $vlabadi = $v["va2"];
                            $vdin = $v["va3"];
                            $vact = $v["va4"];
                            if ($v["va5"] != 0 ||
                                    $v["va6"] != 0 ||
                                    $v["va7"] != 0 ||
                                    $v["va8"] != 0) {
                                $vlab = $v["va5"];
                                $vlabadi = $v["va6"];
                                $vdin = $v["va7"];
                                $vact = $v["va8"];
                            }
                            $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                            $txt3 = '';
                            $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                            if ($xnom != '') {
                                $txt3 = $xnom;
                            } else {
                                $txt3 = $v["nombreotros"];
                            }
                            $txt .= '<table>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="60%">' . $txt3 . '</td>';
                            $txt .= '<td width="40%">' . $idex . '</td>';
                            $txt .= '</tr>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="10%">&nbsp;</td>';
                            $txt .= '<td width="50%">Aporte en activos</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($vact, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                            $txt .= '<tr>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="10%">&nbsp;</td>';
                            $txt .= '<td width="50%">Aporte en dinero</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($vdin, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="10%">&nbsp;</td>';
                            $txt .= '<td width="50%">Aporte laboral</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($vlab, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="10%">&nbsp;</td>';
                            $txt .= '<td width="50%">Aporte laboral adicional</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($vlabadi, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                            $txt .= '</table>';
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            $pdf->Ln();
                            if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                $pdf->writeHTML('<span style="text-align:justify;">' . $v["textoembargo"] . '</span>', true, false, true, false);
                            }
                            $pdf->Ln();
                        }
                    }
                }

                // Comandita simple
                if ($data["organizacion"] == '06' && $data["capsoc"] != 0) {

                    $tienegestores = 'no';
                    $tienecomanditarios = 'no';
                    $tieneproindiviso1 = 'no';
                    $tieneproindiviso2 = 'no';
                    $tieneproindiviso3 = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if ($v["vinculootros"] == '1120' || $v["vinculootros"] == '1121' || $v["vinculootros"] == '1122' || $v["vinculootros"] == '1126') {
                            $tienegestores = 'si';
                        }
                        if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1130') {
                            $tienecomanditarios = 'si';
                            if ($v["proindiviso"] == 'S1') {
                                $tieneproindiviso1 = 'si';
                            }
                            if ($v["proindiviso"] == 'S2') {
                                $tieneproindiviso2 = 'si';
                            }
                            if ($v["proindiviso"] == 'S3') {
                                $tieneproindiviso3 = 'si';
                            }
                        }
                    }

                    $tcuotas = 0;
                    $tvalor = 0;

                    if ($data["nomsoc"] != 0) {
                        $valnom = $data["nomsoc"];
                    } else {
                        $valnom = $data["capsoc"] / $data["cuosoc"];
                    }
                    $txt = 'El capital social corresponde a la suma de ' . $simbolo . ' ' . number_format($data["capsoc"], 2, ',', '.') . ' dividido en ';
                    $txt .= number_format($data["cuosoc"], 2, ',', '.') . ' cuotas con valor nominal de ' . $simbolo . ' ' . number_format($valnom, 2, ',', '.') . ' cada una';
                    if ($tienegestores == 'si' || $tienecomanditarios == 'si') {
                        $txt .= ', distribuido así: <br><br>';
                    } else {
                        $txt .= '.<br>';
                    }
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $txt = '';

                    //
                    if ($tienegestores == 'si') {
                        $txt .= '<strong>- Socio(s) gestor(es)</strong><br><br>';
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $txt = '';
                        foreach ($data["vinculos"] as $v) {
                            if ($v["vinculootros"] == '1120' || $v["vinculootros"] == '1121' || $v["vinculootros"] == '1122' || $v["vinculootros"] == '1126') {
                                $txt = '';
                                $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                $txt3 = '';
                                $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                if ($xnom != '') {
                                    $txt3 = $xnom;
                                } else {
                                    $txt3 = armarNombreInvertidoFormato2019($v["nombreotros"]);
                                }
                                $txt .= '<table>';
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="60%">' . $txt3 . '</td>';
                                $txt .= '<td width="40%">' . $idex . '</td>';
                                $txt .= '</tr>';
                                if ($v["valorconst"] != 0 || $v["valorref"] != 0) {
                                    $cuotas = $v["cuotasconst"];
                                    $valor = $v["valorconst"];
                                    if ($v["cuotasref"] != 0) {
                                        $cuotas = $v["cuotasref"];
                                        $valor = $v["valorref"];
                                    }
                                    $tcuotas = $tcuotas + $cuotas;
                                    $tvalor = $tvalor + $valor;
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                                    $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                                    $txt .= '</tr>';
                                }
                                $txt .= '</table>';
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                $pdf->Ln();
                                if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $v["textoembargo"] . '</span>', true, false, true, false);
                                }
                                $pdf->Ln();
                            }
                        }
                    }

                    //
                    if ($tienecomanditarios == 'si') {
                        $txt = '<strong>- Socio(s) comanditario(s)</strong><br><br>';
                        $pdf->writeHTML($txt, true, false, true, false, 'J');

                        foreach ($data["vinculos"] as $v) {
                            if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1130') {
                                if ($v["proindiviso"] != 'S1' && $v["proindiviso"] != 'S2' && $v["proindiviso"] != 'S3') {
                                    if ($v["valorconst"] != 0 || $v["valorref"] != 0) {
                                        $txt = '';
                                        $cuotas = $v["cuotasconst"];
                                        $valor = $v["valorconst"];
                                        if ($v["cuotasref"] != 0) {
                                            $cuotas = $v["cuotasref"];
                                            $valor = $v["valorref"];
                                        }
                                        $tcuotas = $tcuotas + $cuotas;
                                        $tvalor = $tvalor + $valor;
                                        $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                        $txt3 = '';
                                        $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                        if ($xnom != '') {
                                            $txt3 = $xnom;
                                        } else {
                                            $txt3 = armarNombreInvertidoFormato2019($v["nombreotros"]);
                                        }
                                        $txt .= '<table>';
                                        $txt .= '<tr align="left">';
                                        $txt .= '<td width="60%">' . $txt3 . '</td>';
                                        $txt .= '<td width="40%">' . $idex . '</td>';
                                        $txt .= '</tr>';
                                        $txt .= '<tr align="left">';
                                        $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                                        $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                                        $txt .= '</tr>';
                                        $txt .= '</table>';
                                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                        $pdf->Ln();
                                        if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                            $pdf->writeHTML('<span style="text-align:justify;">' . $v["textoembargo"] . '</span>', true, false, true, false);
                                        }
                                        $pdf->Ln();
                                    }
                                }
                            }
                        }

                        if ($tieneproindiviso1 == 'si') {
                            $txt = '<table>';
                            $cuotas = 0;
                            $valor = 0;
                            foreach ($data["vinculos"] as $v) {
                                if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1130') {
                                    if ($v["proindiviso"] == 'S1') {
                                        if ($v["cuotasref"] != 0) {
                                            $cuotas += $v["cuotasref"];
                                            $valor += $v["valorref"];
                                        } else {
                                            $cuotas += $v["cuotasconst"];
                                            $valor += $v["valorconst"];
                                        }
                                        $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                        $txt3 = '';
                                        $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                        if ($xnom != '') {
                                            $txt3 = $xnom;
                                        } else {
                                            $txt3 = armarNombreInvertidoFormato2019($v["nombreotros"]);
                                        }
                                        $txt .= '<tr align="left">';
                                        $txt .= '<td width="60%">' . $txt3 . '</td>';
                                        $txt .= '<td width="40%">' . $idex . '</td>';
                                        $txt .= '</tr>';

                                        if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                            $txt .= '<tr align="left" colspan="2">';
                                            $txt .= '<td width="60%">' . $v["textoembargo"] . '</td>';
                                            $txt .= '<td width="40%">&nbsp;</td>';
                                            $txt .= '</tr>';
                                            $txt .= '<tr align="left" colspan="2">';
                                            $txt .= '<td width="60%">&nbsp</td>';
                                            $txt .= '<td width="40%">&nbsp;</td>';
                                            $txt .= '</tr>';
                                        }
                                    }
                                }
                            }

                            if ($cuotas != 0 || $valor != 0) {
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                                $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                                $txt .= '</tr>';
                            }
                            $txt .= '</table>';
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);

                            $tcuotas = $tcuotas + $cuotas;
                            $tvalor = $tvalor + $valor;
                        }

                        //
                        if ($tieneproindiviso2 == 'si') {
                            $txt = '<table>';
                            $cuotas = 0;
                            $valor = 0;
                            foreach ($data["vinculos"] as $v) {
                                if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1130') {
                                    if ($v["proindiviso"] == 'S2') {
                                        if ($v["cuotasref"] != 0) {
                                            $cuotas += $v["cuotasref"];
                                            $valor += $v["valorref"];
                                        } else {
                                            $cuotas += $v["cuotasconst"];
                                            $valor += $v["valorconst"];
                                        }
                                        $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                        $txt3 = '';
                                        $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                        if ($xnom != '') {
                                            $txt3 = $xnom;
                                        } else {
                                            $txt3 = armarNombreInvertidoFormato2019($v["nombreotros"]);
                                        }
                                        $txt .= '<tr align="left">';
                                        $txt .= '<td width="60%">' . $txt3 . '</td>';
                                        $txt .= '<td width="40%">' . $idex . '</td>';
                                        $txt .= '</tr>';
                                        if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                            $txt .= '<tr align="left" colspan="2">';
                                            $txt .= '<td width="60%">' . $v["textoembargo"] . '</td>';
                                            $txt .= '<td width="40%">&nbsp;</td>';
                                            $txt .= '</tr>';
                                            $txt .= '<tr align="left" colspan="2">';
                                            $txt .= '<td width="60%">&nbsp</td>';
                                            $txt .= '<td width="40%">&nbsp;</td>';
                                            $txt .= '</tr>';
                                        }
                                    }
                                }
                            }
                            if ($cuotas != 0 || $valor != 0) {
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                                $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                                $txt .= '</tr>';
                            }
                            $txt .= '</table>';
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);

                            $tcuotas = $tcuotas + $cuotas;
                            $tvalor = $tvalor + $valor;
                        }

                        //
                        if ($tieneproindiviso3 == 'si') {
                            $txt = '<table>';
                            $cuotas = 0;
                            $valor = 0;
                            foreach ($data["vinculos"] as $v) {
                                if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1130') {
                                    if ($v["proindiviso"] == 'S3') {
                                        if ($v["cuotasref"] != 0) {
                                            $cuotas += $v["cuotasref"];
                                            $valor += $v["valorref"];
                                        } else {
                                            $cuotas += $v["cuotasconst"];
                                            $valor += $v["valorconst"];
                                        }
                                        $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                        $txt3 = '';
                                        $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                        if ($xnom != '') {
                                            $txt3 = $xnom;
                                        } else {
                                            $txt3 = armarNombreInvertidoFormato2019($v["nombreotros"]);
                                        }
                                        $txt .= '<tr align="left">';
                                        $txt .= '<td width="60%">' . $txt3 . '</td>';
                                        $txt .= '<td width="40%">' . $idex . '</td>';
                                        $txt .= '</tr>';
                                        if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                            $txt .= '<tr align="left" colspan="2">';
                                            $txt .= '<td width="60%">' . $v["textoembargo"] . '</td>';
                                            $txt .= '<td width="40%">&nbsp;</td>';
                                            $txt .= '</tr>';
                                            $txt .= '<tr align="left" colspan="2">';
                                            $txt .= '<td width="60%">&nbsp</td>';
                                            $txt .= '<td width="40%">&nbsp;</td>';
                                            $txt .= '</tr>';
                                        }
                                    }
                                }
                            }
                            if ($cuotas != 0 || $valor != 0) {
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                                $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                                $txt .= '</tr>';
                            }
                            $txt .= '</table>';
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            $tcuotas = $tcuotas + $cuotas;
                            $tvalor = $tvalor + $valor;
                        }
                    }

                    if ($tcuotas != 0 || $tvalor != 0) {
                        $txt = '<table>';
                        $txt .= '<tr align="left">';
                        $txt .= '<td width="60%">Totales</td>';
                        $txt .= '<td width="40%"></td>';
                        $txt .= '</tr>';
                        $txt .= '<tr align="left">';
                        $txt .= '<td width="60%">Nro. Cuotas ' . $tcuotas . '</td>';
                        $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($tvalor, 2, ',', '.') . '</td>';
                        $txt .= '</tr>';
                        $txt .= '</table>';
                        $txt .= '<br><br>';
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        $pdf->Ln();
                    }
                }

                // Limitadas o civiles limitadas
                if ($data["organizacion"] == '03' || ($data["organizacion"] == '10' && $data["capsoc"] != 0) || ($data["organizacion"] == '17' && $data["capsoc"] != 0)) {
                    $tieneproindiviso1 = 'no';
                    $tieneproindiviso2 = 'no';
                    $tieneproindiviso3 = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1110' || $v["vinculootros"] == '1120' || $v["vinculootros"] == '1130' || $v["vinculootros"] == '1140' || $v["vinculootros"] == '3110') {
                            if ($v["proindiviso"] == 'S1') {
                                $tieneproindiviso1 = 'si';
                            }
                            if ($v["proindiviso"] == 'S2') {
                                $tieneproindiviso2 = 'si';
                            }
                            if ($v["proindiviso"] == 'S3') {
                                $tieneproindiviso3 = 'si';
                            }
                        }
                    }

                    if ($data["nomsoc"] != 0) {
                        $valnom = $data["nomsoc"];
                    } else {
                        if ($data["cuosoc"] != 0) {
                            $valnom = $data["capsoc"] / $data["cuosoc"];
                        } else {
                            $valnom = $data["capsoc"];
                        }
                    }

                    //
                    $txt = 'El capital social corresponde a la suma de ' . $simbolo . ' ' . number_format($data["capsoc"], 2, ',', '.') . ' dividido en ';
                    $txt .= number_format($data["cuosoc"], 2, ',', '.') . ' cuotas con valor nominal de ' . $simbolo . ' ' . number_format($valnom, 2, ',', '.') . ' cada una, ';
                    $txt .= 'distribuido así: <br><br>';
                    $txt .= '<strong>- Socios capitalistas</strong><br><br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'J');
                    $txt = '';
                    $tcuotas = 0;
                    $tvalor = 0;
                    foreach ($data["vinculos"] as $v) {
                        if ($v["proindiviso"] != 'S1' && $v["proindiviso"] != 'S2' && $v["proindiviso"] != 'S3') {
                            if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1110' || $v["vinculootros"] == '1120' || $v["vinculootros"] == '1130' || $v["vinculootros"] == '1140' || $v["vinculootros"] == '3110') {
                                $txt = '';
                                $cuotas = $v["cuotasconst"];
                                $valor = $v["valorconst"];
                                if ($v["cuotasref"] != 0) {
                                    $cuotas = $v["cuotasref"];
                                    $valor = $v["valorref"];
                                }
                                $tcuotas = $tcuotas + $cuotas;
                                $tvalor = $tvalor + $valor;
                                $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                $txt3 = '';
                                $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                if ($xnom != '') {
                                    $txt3 = $xnom;
                                } else {
                                    $txt3 = $v["nombreotros"];
                                }
                                $txt .= '<table>';
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="60%">' . $txt3 . '</td>';
                                $txt .= '<td width="40%">' . $idex . '</td>';
                                $txt .= '</tr>';
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                                $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                $pdf->Ln();
                                if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $v["textoembargo"] . '</span>', true, false, true, false);
                                }
                                $pdf->Ln();
                            }
                        }
                    }

                    if ($tieneproindiviso1 == 'si') {
                        $txt = '<table>';
                        $cuotas = 0;
                        $valor = 0;
                        foreach ($data["vinculos"] as $v) {
                            if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1110' || $v["vinculootros"] == '1120' || $v["vinculootros"] == '1130' || $v["vinculootros"] == '1140' || $v["vinculootros"] == '3110') {
                                if ($v["proindiviso"] == 'S1') {
                                    if ($v["cuotasref"] != 0) {
                                        $cuotas += $v["cuotasref"];
                                        $valor += $v["valorref"];
                                    } else {
                                        $cuotas += $v["cuotasconst"];
                                        $valor += $v["valorconst"];
                                    }
                                    $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                    $txt3 = '';
                                    $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                    if ($xnom != '') {
                                        $txt3 = $xnom;
                                    } else {
                                        $txt3 = armarNombreInvertidoFormato2019($v["nombreotros"]);
                                    }
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="60%">' . $txt3 . '</td>';
                                    $txt .= '<td width="40%">' . $idex . '</td>';
                                    $txt .= '</tr>';

                                    if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                        $txt .= '<tr align="left" colspan="2">';
                                        $txt .= '<td width="60%">' . $v["textoembargo"] . '</td>';
                                        $txt .= '<td width="40%">&nbsp;</td>';
                                        $txt .= '</tr>';
                                        $txt .= '<tr align="left" colspan="2">';
                                        $txt .= '<td width="60%">&nbsp</td>';
                                        $txt .= '<td width="40%">&nbsp;</td>';
                                        $txt .= '</tr>';
                                    }
                                }
                            }
                        }

                        if ($cuotas != 0 || $valor != 0) {
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                        }
                        $txt .= '</table>';
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);

                        $tcuotas = $tcuotas + $cuotas;
                        $tvalor = $tvalor + $valor;
                    }

                    if ($tieneproindiviso2 == 'si') {
                        $txt = '<table>';
                        $cuotas = 0;
                        $valor = 0;
                        foreach ($data["vinculos"] as $v) {
                            if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1110' || $v["vinculootros"] == '1120' || $v["vinculootros"] == '1130' || $v["vinculootros"] == '1140' || $v["vinculootros"] == '3110') {
                                if ($v["proindiviso"] == 'S2') {
                                    if ($v["cuotasref"] != 0) {
                                        $cuotas += $v["cuotasref"];
                                        $valor += $v["valorref"];
                                    } else {
                                        $cuotas += $v["cuotasconst"];
                                        $valor += $v["valorconst"];
                                    }
                                    $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                    $txt3 = '';
                                    $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                    if ($xnom != '') {
                                        $txt3 = $xnom;
                                    } else {
                                        $txt3 = armarNombreInvertidoFormato2019($v["nombreotros"]);
                                    }
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="60%">' . $txt3 . '</td>';
                                    $txt .= '<td width="40%">' . $idex . '</td>';
                                    $txt .= '</tr>';

                                    if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                        $txt .= '<tr align="left" colspan="2">';
                                        $txt .= '<td width="60%">' . $v["textoembargo"] . '</td>';
                                        $txt .= '<td width="40%">&nbsp;</td>';
                                        $txt .= '</tr>';
                                        $txt .= '<tr align="left" colspan="2">';
                                        $txt .= '<td width="60%">&nbsp</td>';
                                        $txt .= '<td width="40%">&nbsp;</td>';
                                        $txt .= '</tr>';
                                    }
                                }
                            }
                        }

                        if ($cuotas != 0 || $valor != 0) {
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                        }
                        $txt .= '</table>';
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);

                        $tcuotas = $tcuotas + $cuotas;
                        $tvalor = $tvalor + $valor;
                    }

                    if ($tieneproindiviso3 == 'si') {
                        $txt = '<table>';
                        $cuotas = 0;
                        $valor = 0;
                        foreach ($data["vinculos"] as $v) {
                            if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1110' || $v["vinculootros"] == '1120' || $v["vinculootros"] == '1130' || $v["vinculootros"] == '1140' || $v["vinculootros"] == '3110') {
                                if ($v["proindiviso"] == 'S3') {
                                    if ($v["cuotasref"] != 0) {
                                        $cuotas += $v["cuotasref"];
                                        $valor += $v["valorref"];
                                    } else {
                                        $cuotas += $v["cuotasconst"];
                                        $valor += $v["valorconst"];
                                    }
                                    $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                                    $txt3 = '';
                                    $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                                    if ($xnom != '') {
                                        $txt3 = $xnom;
                                    } else {
                                        $txt3 = armarNombreInvertidoFormato2019($v["nombreotros"]);
                                    }
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="60%">' . $txt3 . '</td>';
                                    $txt .= '<td width="40%">' . $idex . '</td>';
                                    $txt .= '</tr>';

                                    if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                        $txt .= '<tr align="left" colspan="2">';
                                        $txt .= '<td width="60%">' . $v["textoembargo"] . '</td>';
                                        $txt .= '<td width="40%">&nbsp;</td>';
                                        $txt .= '</tr>';
                                        $txt .= '<tr align="left" colspan="2">';
                                        $txt .= '<td width="60%">&nbsp</td>';
                                        $txt .= '<td width="40%">&nbsp;</td>';
                                        $txt .= '</tr>';
                                    }
                                }
                            }
                        }

                        if ($cuotas != 0 || $valor != 0) {
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                        }
                        $txt .= '</table>';
                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);

                        $tcuotas = $tcuotas + $cuotas;
                        $tvalor = $tvalor + $valor;
                    }

                    $txt = '';
                    $txt .= '<table>';
                    $txt .= '<tr align="left">';
                    $txt .= '<td width="60%"><strong>Totales</strong></td>';
                    $txt .= '<td width="40%">&nbsp;</td>';
                    $txt .= '</tr>';
                    $txt .= '<tr align="left">';
                    $txt .= '<td width="60%">Nro. Cuotas: ' . $tcuotas . '</td>';
                    $txt .= '<td width="40%">Valor: ' . $simbolo . ' ' . number_format($tvalor, 2, ',', '.') . '</td>';
                    $txt .= '</tr>';
                    $txt .= '</table>';
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }

                // Limitadas o civiles limitadas
                if ($data["organizacion"] == '11' && $data["capsoc"] != 0) {
                    if ($data["nomsoc"] != 0) {
                        $valnom = $data["nomsoc"];
                    } else {
                        $valnom = $data["capsoc"] / $data["cuosoc"];
                    }
                    $txt = 'El capital social corresponde a la suma de ' . $simbolo . ' ' . number_format($data["capsoc"], 2, ',', '.') . ' dividido en ';
                    $txt .= number_format($data["cuosoc"], 2, ',', '.') . ' cuotas con valor nominal de ' . $simbolo . ' ' . number_format($valnom, 2, ',', '.') . ' cada una, ';
                    $txt .= 'distribuido así: <br><br>';
                    $pdf->writeHTML($txt, true, false, true, false, 'J');

                    $tcuotas = 0;
                    $tvalor = 0;
                    $socemp = 0;
                    $soccap = 0;
                    foreach ($data["vinculos"] as $v) {
                        if ($v["vinculootros"] == '3110') {
                            $txt = '';
                            $socemp++;
                            if ($socemp == 1) {
                                $txt = '<strong>- Socios empresarios</strong><br>';
                                $pdf->writeHTML($txt, true, false, true, false, 'J');
                                $txt = '';
                            }
                            $cuotas = $v["cuotasconst"];
                            $valor = $v["valorconst"];
                            if ($v["cuotasref"] != 0) {
                                $cuotas = $v["cuotasref"];
                                $valor = $v["valorref"];
                            }
                            $tcuotas = $tcuotas + $cuotas;
                            $tvalor = $tvalor + $valor;
                            $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                            $txt3 = '';
                            $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                            if ($xnom != '') {
                                $txt3 = $xnom;
                            } else {
                                $txt3 = $v["nombreotros"];
                            }
                            $txt = '<table>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="60%">' . $txt3 . '</td>';
                            $txt .= '<td width="40%">' . $idex . '</td>';
                            $txt .= '</tr>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                            $txt .= '</table>';
                            $txt .= '<br><br>';
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            $pdf->Ln();
                            if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                $pdf->writeHTML('<span style="text-align:justify;">' . $v["textoembargo"] . '</span>', true, false, true, false);
                            }
                            $pdf->Ln();
                        }
                    }

                    foreach ($data["vinculos"] as $v) {
                        if ($v["vinculootros"] == '1100' || $v["vinculootros"] == '1110' || $v["vinculootros"] == '1120' || $v["vinculootros"] == '1130' || $v["vinculootros"] == '1140') {
                            $soccap++;
                            if ($soccap == 1) {
                                $txt = '<strong>- Socios capitalistas</strong><br>';
                                $pdf->writeHTML($txt, true, false, true, false, 'J');
                                $txt = '';
                            }
                            $cuotas = $v["cuotasconst"];
                            $valor = $v["valorconst"];
                            if ($v["cuotasref"] != 0) {
                                $cuotas = $v["cuotasref"];
                                $valor = $v["valorref"];
                            }
                            $tcuotas = $tcuotas + $cuotas;
                            $tvalor = $tvalor + $valor;
                            $idex = retornarTxtTipoIdeFormato2019($v["idtipoidentificacionotros"]) . ' ' . $v["identificacionotros"];
                            $txt3 = '';
                            $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                            if ($xnom != '') {
                                $txt3 = $xnom;
                            } else {
                                $txt3 = $v["nombreotros"];
                            }
                            $txt = '<table>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="60%">' . $txt3 . '</td>';
                            $txt .= '<td width="40%">' . $idex . '</td>';
                            $txt .= '</tr>';
                            $txt .= '<tr align="left">';
                            $txt .= '<td width="60%">Nro. Cuotas ' . $cuotas . '</td>';
                            $txt .= '<td width="40%">Valor ' . $simbolo . ' ' . number_format($valor, 2, ',', '.') . '</td>';
                            $txt .= '</tr>';
                            $txt .= '</table>';
                            $txt .= '<br><br>';
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            $pdf->Ln();
                            if (isset($v["textoembargo"]) && trim($v["textoembargo"]) != '') {
                                $pdf->writeHTML('<span style="text-align:justify;">' . $v["textoembargo"] . '</span>', true, false, true, false);
                            }
                            $pdf->Ln();
                        }
                    }

                    //
                    $txt = '<table>';
                    $txt .= '<tr align="left">';
                    $txt .= '<td width="60%"><strong>Totales</strong></td>';
                    $txt .= '<td width="40%">&nbsp;</td>';
                    $txt .= '</tr>';
                    $txt .= '<tr align="left">';
                    $txt .= '<td width="60%">Nro. Cuotas: ' . $tcuotas . '</td>';
                    $txt .= '<td width="40%">Valor: ' . $simbolo . ' ' . number_format($tvalor, 2, ',', '.') . '</td>';
                    $txt .= '</tr>';
                    $txt .= '</table>';
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                }
            }
        }
    }

    // Aclaratoria al capital
    if (isset($data["crtsii"]["8001"]) && trim($data["crtsii"]["8001"]) != '') {
        unset($data["crtsii"]["0761"]);
        unset($data["crt"]["0761"]);
    }

    armarCertificaTextoLibreClaseWriteHtmlFormato2019($mysqli, $pdf, $data, 'AC-CAPSOC', 'CERTIFICA - ACLARATORIA CAPITAL Y PATRIMONIOS', 'no', '');
    armarCertificaTextoLibreClaseWriteHtmlFormato2019($mysqli, $pdf, $data, 'AC-SOCIOS', 'ACLARATORIA AL CAPITAL Y PATRIMONIO', 'no', '');

    // Inscripciones relacionadas con el capital
    foreach ($data["inscripciones"] as $dtx) {
        if ($dtx["anotacionalcapital"] == 'S') {
            if ($dtx["crev"] != '9') {
                $txt1 = descripcionesFormato2019(
                        $mysqli,
                        $data["organizacion"],
                        $dtx["acto"],
                        $dtx["tdoc"],
                        $dtx["ndoc"],
                        $dtx["ndocext"],
                        $dtx["fdoc"],
                        $dtx["idoridoc"],
                        $dtx["txoridoc"],
                        $dtx["idmunidoc"],
                        $dtx["lib"],
                        $dtx["nreg"],
                        $dtx["freg"],
                        $dtx["not"],
                        $data["nomant"],
                        $data["nombre"],
                        $data["complementorazonsocial"],
                        $dtx["camant"],
                        $dtx["libant"],
                        $dtx["regant"],
                        $dtx["fecant"],
                        $dtx["camant2"],
                        $dtx["libant2"],
                        $dtx["regant2"],
                        $dtx["fecant2"],
                        $dtx["camant3"],
                        $dtx["libant3"],
                        $dtx["regant3"],
                        $dtx["fecant3"],
                        $dtx["camant4"],
                        $dtx["libant4"],
                        $dtx["regant4"],
                        $dtx["fecant4"],
                        $dtx["camant5"],
                        $dtx["libant5"],
                        $dtx["regant5"],
                        $dtx["fecant5"],
                        $dtx["aclaratoria"],
                        $dtx["tomo72"],
                        $dtx["folio72"],
                        $dtx["registro72"]
                );

                //
                $txt = \funcionesGenerales::limpiarTextosRedundantes(\funcionesGenerales::parsearOracion($txt1));
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
            }
        }
    }
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosFomato2019($mysqli, $pdf, $data, $tipovinculo) {
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
        armarVinculosTipoSiiFormato2019($mysqli, $pdf, $data, $tipovinculo);
    }
    if ($vinculos == 'si' && $vinculoscompletos == 'no') {
        armarVinculosTipoSirepFormato2019($mysqli, $pdf, $data, $tipovinculo);
    }
}

function armarVinculosTipoSiiFormato2019($mysqli, $pdf, $data, $tipovinculo) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    armarVinculoTitulo($pdf, $data, $tipovinculo);

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
                        $camant2 = $ins["camant2"];
                        $libant2 = $ins["libant2"];
                        $regant2 = $ins["regant2"];
                        $fecant2 = $ins["fecant2"];
                        $camant3 = $ins["camant3"];
                        $libant3 = $ins["libant3"];
                        $regant3 = $ins["regant3"];
                        $fecant3 = $ins["fecant3"];
                        $camant4 = $ins["camant4"];
                        $libant4 = $ins["libant4"];
                        $regant4 = $ins["regant4"];
                        $fecant4 = $ins["fecant4"];
                        $camant5 = $ins["camant5"];
                        $libant5 = $ins["libant5"];
                        $regant5 = $ins["regant5"];
                        $fecant5 = $ins["fecant5"];
                        $tomo72 = $ins["tomo72"];
                        $folio72 = $ins["folio72"];
                        $registro72 = $ins["registro72"];
                    }
                }
            }

            $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, '', '', $tomo72, $folio72, $registro72);
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
                $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                if (isset($arrCargo) && !empty($arrCargo)) {
                    $descCargo = $arrCargo["descripcion"];
                } else {
                    $descCargo = '';
                }
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
                $sp = separarDv($v["identificacionotros"]);
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
                        $txt = \funcionesGenerales::agregarPuntoFinal($txt);
                        $pdf->MultiCell(185, 4, $txt, 0, 'J', false, 1, '', '', true, 0, true);
                    }
                }
            }
        }
    }
}

function armarVinculosTipoSirepFormato2019($mysqli, $pdf, $data, $tipovinculo) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    armarVinculoTitulo($pdf, $data, $tipovinculo);

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
                $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                if (isset($arrCargo) && !empty($arrCargo)) {
                    $descCargo = $arrCargo["descripcion"];
                } else {
                    $descCargo = '';
                }
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

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosJuntaDirectivaFormato2019($mysqli, $pdf, $data) {

    $retornar = false;

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $tipojuridica = 'mtil';
    if (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $data["categoria"] == '1') {
        return true;
    }

    //
    $arrVinculosCertificados = array();
    $arrInscripciones = array();
    $vinculos = 'no';
    $libro = '';
    $registro = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["dupliotros"] == '') {
            $v["dupliotros"] = '1';
        }
        if ($v["tipovinculo"] == 'JDP' || $v["tipovinculo"] == 'JDS') {
            $vinculos = 'si';
            $ind = $v["fechaotros"] . '-' . $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . sprintf("%02s", $v["dupliotros"]);
            if ($v["dupliotros"] == '') {
                $v["dupliotros"] = '1';
            }

            // $ind = $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
            $arrInscripciones[$ind] = array();
            $arrInscripciones[$ind]["libro"] = $v["librootros"];
            $arrInscripciones[$ind]["registro"] = $v["inscripcionotros"];
            $arrInscripciones[$ind]["dupli"] = sprintf("%02s", $v["dupliotros"]);
            $arrInscripciones[$ind]["fecha"] = $v["fechaotros"];
        }
    }

    // ***************************************************************************************** //
    // En caso quje la junta directiva se hubiere nombrado con diferentes inscripciones
    // Se genera primero un resumen de la Junta Directiva
    // ***************************************************************************************** //
    if (count($arrInscripciones) > 1 && $vinculos == 'si') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($pdf->tituloNombramientos == 'NO') {
            $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->tituloNombramientos = 'SI';
        }
        if (!isset($data["tituloorganodirectivo"]) || trim((string) $data["tituloorganodirectivo"]) == '') {
            $pdf->writeHTML('<strong>JUNTA DIRECTIVA</strong>', true, false, true, false, 'C');
        } else {
            $pdf->writeHTML('<strong>' . $data["tituloorganodirectivo"] . '</strong>', true, false, true, false, 'C');
        }
        $pdf->Ln();

        $txt = '<table>';
        $txt .= '<tr align="left">';
        $txt .= '<td width="30%"><strong>CARGO</strong></td>';
        $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
        $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
        $txt .= '</tr>';
        $txt .= '</table>';
        $pdf->SetFont('courier', '', 9);
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $pdf->Ln();

        // Principales JD
        $pdf->writeHTML('<strong>PRINCIPALES</strong>', true, false, true, false, 'L');
        $pdf->SetFont('courier', '', 8.5);
        $txt = '';
        foreach ($data["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'JDP') {
                $txt = '<table>';
                $txt .= '<tr align="left">';
                if (trim($v["cargootros"]) == '') {
                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                    ) {
                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                    } else {
                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                        if (isset($arrCargo) && !empty($arrCargo)) {
                            $descCargo = $arrCargo["descripcion"];
                        } else {
                            $descCargo = '';
                        }
                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                    }
                } else {
                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                }

                $txt .= '<td width="40%">';
                $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                if ($xnom != '') {
                    $txt .= $xnom;
                } else {
                    $txt .= $v["nombreotros"];
                }
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                if ($v["idtipoidentificacionotros"] == '2') {
                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                    $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                } else {
                    if ($v["idtipoidentificacionotros"] == '7') {
                        $txt .= '<td width="30%">**********</td>';
                    } else {
                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                            $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                        } else {
                            $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                        }
                    }
                }
                $txt .= '</tr>';
                $txt .= '</table>';
                if ($v["renrem"]) {
                    $txt .= '<table>';
                    $txt .= '<tr align="left">';
                    $txt .= '<td width="100%">';
                    $txt .= '<span style="text-align:justify;">' . $v["renrem"] . '</span>';
                    $txt .= '</td>';
                    $txt .= '</tr>';
                    $txt .= '</table>';
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            }
        }
        $pdf->Ln();
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);

        // Suplentes JD
        $isup = 0;
        $txt = '';
        foreach ($data["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'JDS') {
                $isup++;
                if ($isup == 1) {
                    $pdf->writeHTML('<strong>SUPLENTES</strong>', true, false, true, false, 'L');
                    $pdf->SetFont('courier', '', 8.5);
                }
                $txt = '<table>';
                $txt .= '<tr align="left">';
                if (trim($v["cargootros"]) == '') {
                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                    ) {
                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                    } else {
                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                        if (isset($arrCargo) && !empty($arrCargo)) {
                            $descCargo = $arrCargo["descripcion"];
                        } else {
                            $descCargo = '';
                        }
                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                    }
                } else {
                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                }

                $txt .= '<td width="40%">';
                $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                if ($xnom != '') {
                    $txt .= $xnom;
                } else {
                    $txt .= $v["nombreotros"];
                }
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                if ($v["idtipoidentificacionotros"] == '2') {
                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                    $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                } else {
                    if ($v["idtipoidentificacionotros"] == '7') {
                        $txt .= '<td width="30%">**********</td>';
                    } else {
                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                            $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                        } else {
                            $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                        }
                    }
                }
                $txt .= '</tr>';
                $txt .= '</table>';
                if ($v["renrem"]) {
                    $txt .= '<table>';
                    $txt .= '<tr align="left">';
                    $txt .= '<td width="100%">';
                    $txt .= '<span style="text-align:justify;">' . $v["renrem"] . '</span>';
                    $txt .= '</td>';
                    $txt .= '</tr>';
                    $txt .= '</table>';
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            }
        }
        $pdf->Ln();
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    } else {
        if ($vinculos == 'si') {
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            if ($pdf->tituloNombramientos == 'NO') {
                $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->tituloNombramientos = 'SI';
            }
            if (!isset($data["tituloorganodirectivo"]) || trim((string) $data["tituloorganodirectivo"]) == '') {
                $pdf->writeHTML('<strong>JUNTA DIRECTIVA</strong>', true, false, true, false, 'C');
            } else {
                $pdf->writeHTML('<strong>' . $data["tituloorganodirectivo"] . '</strong>', true, false, true, false, 'C');
            }
            $pdf->Ln();
        }
    }

    // **************************************************************************** //
    // Se muestra la junta directiva nombrada con cada documento
    // **************************************************************************** //
    if ($vinculos == 'si') {
        foreach ($arrInscripciones as $insx) {
            if ($insx["dupli"] == '') {
                $insx["dupli"] = '1';
            }
            foreach ($data["inscripciones"] as $ins) {
                if ($ins["dupli"] == '') {
                    $ins["dupli"] = '1';
                }
                if ($ins["lib"] == $insx["libro"] &&
                        $ins["nreg"] == $insx["registro"] &&
                        sprintf("%02s", $ins["dupli"]) == sprintf("%02s", $insx["dupli"]) &&
                        $ins["freg"] == $insx["fecha"]) {
                    $tipdoc = $ins["tdoc"];
                    $numdoc = $ins["ndoc"];
                    $ndocext = $ins["ndocext"];
                    $fecdoc = $ins["fdoc"];
                    $idorigen = $ins["idoridoc"];
                    $txtorigen = $ins["txoridoc"];
                    $libro = $ins["lib"];
                    $registro = $ins["nreg"];
                    $dupli = sprintf("%02s", $ins["dupli"]);
                    $fecins = $ins["freg"];
                    $idmunidoc = $ins["idmunidoc"];
                    $camant = $ins["camant"];
                    $libant = $ins["libant"];
                    $regant = $ins["regant"];
                    $fecant = $ins["fecant"];
                    $camant2 = $ins["camant2"];
                    $libant2 = $ins["libant2"];
                    $regant2 = $ins["regant2"];
                    $fecant2 = $ins["fecant2"];
                    $camant3 = $ins["camant3"];
                    $libant3 = $ins["libant3"];
                    $regant3 = $ins["regant3"];
                    $fecant3 = $ins["fecant3"];
                    $camant4 = $ins["camant4"];
                    $libant4 = $ins["libant4"];
                    $regant4 = $ins["regant4"];
                    $fecant4 = $ins["fecant4"];
                    $camant5 = $ins["camant5"];
                    $libant5 = $ins["libant5"];
                    $regant5 = $ins["regant5"];
                    $fecant5 = $ins["fecant5"];
                    $tomo72 = $ins["tomo72"];
                    $folio72 = $ins["folio72"];
                    $registro72 = $ins["registro72"];

                    $aclaratoria = $ins["aclaratoria"];
                    if (!isset($ins["renrem"])) {
                        $ins["renrem"] = '';
                    }
                    $renunciasremocion = $ins["renrem"];
                    $txt = '';

                    //
                    $ien = 'no';
                    $ien1 = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if (!isset($arrVinculosCertificados[$v["id"]])) {
                            if ($v["dupliotros"] == '') {
                                $v["dupliotros"] = '1';
                            }
                            if ($v["librootros"] == $insx["libro"] &&
                                    $v["inscripcionotros"] == $insx["registro"] &&
                                    sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                    $v["fechaotros"] == $insx["fecha"] &&
                                    $v["tipovinculo"] == 'JDP') {
                                if ($ien == 'no') {
                                    $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                    $pdf->writeHTML('<strong>PRINCIPALES</strong>', true, false, true, false, 'L');
                                    $pdf->SetFont('courier', '', 9);
                                    $txt = '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                                    $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
                                    $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
                                    $txt .= '</tr>';
                                    $txt .= '</table>';
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $ien = 'si';
                                    $ien1 = 'si';
                                }
                                $arrVinculosCertificados[$v["id"]] = $v["id"];
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                if (trim($v["cargootros"]) == '') {
                                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                                    ) {
                                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                                    } else {
                                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                                        if (isset($arrCargo) && !empty($arrCargo)) {
                                            $descCargo = $arrCargo["descripcion"];
                                        } else {
                                            $descCargo = '';
                                        }
                                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                                    }
                                } else {
                                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                                }

                                $txt .= '<td width="40%">';
                                $xnom = '';
                                if (trim($v["nombre1otros"]) != '') {
                                    $xnom .= trim($v["nombre1otros"]);
                                }
                                if (trim($v["nombre2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["nombre2otros"]);
                                }
                                if (trim($v["apellido1otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido1otros"]);
                                }
                                if (trim($v["apellido2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido2otros"]);
                                }
                                if ($xnom != '') {
                                    $txt .= $xnom;
                                } else {
                                    $txt .= $v["nombreotros"];
                                }
                                if (trim($v["numidemp"]) != '') {
                                    $txt .= '<br>';
                                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                                }
                                $txt .= '</td>';
                                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                if ($v["idtipoidentificacionotros"] == '2') {
                                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                    $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                                } else {
                                    if ($v["idtipoidentificacionotros"] == '7') {
                                        $txt .= '<td width="30%">**********</td>';
                                    } else {
                                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                            $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                        } else {
                                            $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                                        }
                                    }
                                }
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                if ($v["renrem"]) {
                                    $txt .= '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="100%">';
                                    $txt .= '<span style="text-align:justify;">' . $v["renrem"] . '</span>';
                                    $txt .= '</td>';
                                    $txt .= '</tr>';
                                    $txt .= '</table>';
                                }
                                $pdf->SetFont('courier', '', 8.5);
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            }
                        }
                    }
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                    $txt = '';

                    //
                    $ien = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if (!isset($arrVinculosCertificados[$v["id"]])) {
                            if ($v["dupliotros"] == '') {
                                $v["dupliotros"] = '1';
                            }
                            if ($v["librootros"] == $insx["libro"] &&
                                    $v["inscripcionotros"] == $insx["registro"] &&
                                    sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                    $v["fechaotros"] == $insx["fecha"] &&
                                    $v["tipovinculo"] == 'JDS') {
                                if ($ien == 'no') {
                                    if ($ien1 == 'no') {
                                        $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                        $pdf->Ln();
                                    }
                                    $pdf->writeHTML('<strong>SUPLENTES</strong>', true, false, true, false, 'L');
                                    $pdf->SetFont('courier', '', 9);
                                    $txt = '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                                    $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
                                    $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
                                    $txt .= '</tr>';
                                    $txt .= '<table>';
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $ien = 'si';
                                }
                                $arrVinculosCertificados[$v["id"]] = $v["id"];
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                if (trim($v["cargootros"]) == '') {
                                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                                    ) {
                                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                                    } else {
                                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                                        if (isset($arrCargo) && !empty($arrCargo)) {
                                            $descCargo = $arrCargo["descripcion"];
                                        } else {
                                            $descCargo = '';
                                        }
                                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                                    }
                                } else {
                                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                                }

                                $txt .= '<td width="40%">';
                                $xnom = '';
                                if (trim($v["nombre1otros"]) != '') {
                                    $xnom .= trim($v["nombre1otros"]);
                                }
                                if (trim($v["nombre2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["nombre2otros"]);
                                }
                                if (trim($v["apellido1otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido1otros"]);
                                }
                                if (trim($v["apellido2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido2otros"]);
                                }
                                if ($xnom != '') {
                                    $txt .= $xnom;
                                } else {
                                    $txt .= $v["nombreotros"];
                                }
                                if (trim($v["numidemp"]) != '') {
                                    $txt .= '<br>';
                                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                                }
                                $txt .= '</td>';
                                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                if ($v["idtipoidentificacionotros"] == '2') {
                                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                    $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                                } else {
                                    if ($v["idtipoidentificacionotros"] == '7') {
                                        $txt .= '<td width="30%">**********</td>';
                                    } else {
                                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                            $txt .= '<td width="30%">' . $ti . ' ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                        } else {
                                            $txt .= '<td width="30%">' . $ti . ' ' . $v["identificacionotros"] . '</td>';
                                        }
                                    }
                                }
                                $txt .= '</tr>';
                                $txt .= '<table>';
                                if ($v["renrem"]) {
                                    $txt .= '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="100%">';
                                    $txt .= '<span style="text-align:justify;">' . $v["renrem"] . '</span>';
                                    $txt .= '</td>';
                                    $txt .= '</tr>';
                                    $txt .= '<table>';
                                }
                                $pdf->SetFont('courier', '', 8.5);
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            }
                        }
                    }
                    $txt = '';
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                }
            }
        }
    }

    //
    if ($vinculos == 'si') {
        $retornar = true;
    }

    //
    return $retornar;
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosRepresentantesLegalesFormato2019($mysqli, $pdf, $data) {

    $retornar = false;

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $arrVinculosCertificados = array();
    $arrInscripciones = array();
    $vinculos = 'no';
    $libro = '';
    $registro = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == 'ADMP' ||
                $v["tipovinculo"] == 'ADMS1' ||
                $v["tipovinculo"] == 'ADMS2' ||
                $v["tipovinculo"] == 'RLP' ||
                $v["tipovinculo"] == 'RLS' ||
                $v["tipovinculo"] == 'RLS1' ||
                $v["tipovinculo"] == 'RLS2' ||
                $v["tipovinculo"] == 'RLS3' ||
                $v["tipovinculo"] == 'RLS4' ||
                $v["tipovinculoesadl"] == 'RLP' ||
                $v["tipovinculoesadl"] == 'RLS') {
            $vinculos = 'si';
            if ($v["dupliotros"] == '') {
                $v["dupliotros"] = '1';
            }
            // $ind = $v["fechaotros"] . '-' . $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
            $ind = $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
            $arrInscripciones[$ind] = array();
            $arrInscripciones[$ind]["libro"] = $v["librootros"];
            $arrInscripciones[$ind]["registro"] = $v["inscripcionotros"];
            $arrInscripciones[$ind]["dupli"] = $v["dupliotros"];
            $arrInscripciones[$ind]["fecha"] = $v["fechaotros"];
        }
    }


    if ($vinculos == 'si') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($pdf->tituloNombramientos != 'SI') {
            if ($data["categoria"] == '1') {
                $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
                $pdf->Ln();
            }
            $pdf->tituloNombramientos = 'SI';
        }
        if ($data["claseespesadl"] == '61') {
            $pdf->writeHTML('<strong>DESIGNACIÓN APODERADO JUDICIAL</strong>', true, false, true, false, 'C');
            $pdf->Ln();
        } else {

            if ($data["categoria"] == '1') {
                $pdf->writeHTML('<strong>REPRESENTANTES LEGALES</strong>', true, false, true, false, 'C');
                $pdf->Ln();
            }
            if ($data["categoria"] == '2') {
                $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
                $pdf->Ln();
            }
            if ($data["categoria"] == '3' || $data["organizacion"] == '02') {
                $pdf->writeHTML('<strong>NOMBRAMIENTO DE ADMINISTRADOR</strong>', true, false, true, false, 'C');
                $pdf->Ln();
            }
        }
    }

    // **************************************************************************** //
    // Se muestra lo9s representantes legales con cada documento
    // **************************************************************************** //
    if ($vinculos == 'si') {
        foreach ($arrInscripciones as $insx) {
            if ($insx["dupli"] == '') {
                $insx["dupli"] = '1';
            }
            foreach ($data["inscripciones"] as $ins) {
                if ($ins["dupli"] == '') {
                    $ins["dupli"] = '1';
                }
                if ($ins["lib"] == $insx["libro"] &&
                        $ins["nreg"] == $insx["registro"] &&
                        sprintf("%02s", $ins["dupli"]) == sprintf("%02s", $insx["dupli"])) {
                    // && $ins["freg"] == $insx["fecha"]) {
                    $tipdoc = $ins["tdoc"];
                    $numdoc = $ins["ndoc"];
                    $ndocext = $ins["ndocext"];
                    $fecdoc = $ins["fdoc"];
                    $idorigen = $ins["idoridoc"];
                    $txtorigen = $ins["txoridoc"];
                    $libro = $ins["lib"];
                    $registro = $ins["nreg"];
                    $dupli = sprintf("%02s", $ins["dupli"]);
                    $fecins = $ins["freg"];
                    $idmunidoc = $ins["idmunidoc"];
                    $camant = $ins["camant"];
                    $libant = $ins["libant"];
                    $regant = $ins["regant"];
                    $fecant = $ins["fecant"];
                    $camant2 = $ins["camant2"];
                    $libant2 = $ins["libant2"];
                    $regant2 = $ins["regant2"];
                    $fecant2 = $ins["fecant2"];
                    $camant3 = $ins["camant3"];
                    $libant3 = $ins["libant3"];
                    $regant3 = $ins["regant3"];
                    $fecant3 = $ins["fecant3"];
                    $camant4 = $ins["camant4"];
                    $libant4 = $ins["libant4"];
                    $regant4 = $ins["regant4"];
                    $fecant4 = $ins["fecant4"];
                    $camant5 = $ins["camant5"];
                    $libant5 = $ins["libant5"];
                    $regant5 = $ins["regant5"];
                    $fecant5 = $ins["fecant5"];
                    $tomo72 = $ins["tomo72"];
                    $folio72 = $ins["folio72"];
                    $registro72 = $ins["registro72"];

                    $aclaratoria = $ins["aclaratoria"];
                    if (!isset($ins["renrem"])) {
                        $ins["renrem"] = '';
                    }
                    $renunciasremocion = $ins["renrem"];

                    //
                    $ien = 'no';
                    foreach ($data["vinculos"] as $key => $v) {
                        if (!isset($arrVinculosCertificados[$v["id"]])) {
                            if ($v["dupliotros"] == '' || $v["dupliotros"] == '0') {
                                $v["dupliotros"] = '1';
                            }
                            if ($v["librootros"] == $insx["libro"] &&
                                    $v["inscripcionotros"] == $insx["registro"] &&
                                    sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                    // $v["fechaotros"] == $insx["fecha"] &&
                                    ($v["tipovinculo"] == 'ADMP' ||
                                    $v["tipovinculo"] == 'ADMS1' ||
                                    $v["tipovinculo"] == 'ADMS2' ||
                                    $v["tipovinculo"] == 'RLP' ||
                                    $v["tipovinculo"] == 'RLS' ||
                                    $v["tipovinculo"] == 'RLS1' ||
                                    $v["tipovinculo"] == 'RLS2' ||
                                    $v["tipovinculo"] == 'RLS3' ||
                                    $v["tipovinculo"] == 'RLS4' ||
                                    $v["tipovinculoesadl"] == 'RLP' ||
                                    $v["tipovinculoesadl"] == 'RLS')
                            ) {
                                $arrVinculosCertificados[$v["id"]] = $v["id"];
                                if ($ien == 'no') {
                                    $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                    $pdf->SetFont('courier', '', 9);
                                    $txt = '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                                    $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
                                    $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
                                    $txt .= '</tr>';
                                    $txt .= '</table>';
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $ien = 'si';
                                }
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                if (trim($v["cargootros"]) == '') {
                                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                                    ) {
                                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                                    } else {
                                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                                        if (isset($arrCargo) && !empty($arrCargo)) {
                                            $descCargo = $arrCargo["descripcion"];
                                        } else {
                                            $descCargo = '';
                                        }
                                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                                    }
                                } else {
                                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                                }

                                $txt .= '<td width="40%">';

                                /*
                                  if (trim($v["numidemp"]) != '') {
                                  $txt .= '<br>';
                                  $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                                  }
                                 */
                                $xnom = '';
                                if (trim($v["nombre1otros"]) != '') {
                                    $xnom .= trim($v["nombre1otros"]);
                                }
                                if (trim($v["nombre2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["nombre2otros"]);
                                }
                                if (trim($v["apellido1otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido1otros"]);
                                }
                                if (trim($v["apellido2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido2otros"]);
                                }
                                if ($xnom == '') {
                                    $xnom = $v["nombreotros"];
                                }

                                if (trim((string) $v["numidemp"]) != '') {
                                    $txt .= $v["nombreemp"] . '<br>';
                                    $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                    $txt .= 'REPRESENTADA POR: ' . $ti . ' ' . $v["identificacionotros"] . ' - ' . $xnom;
                                } else {
                                    if ($xnom != '') {
                                        $txt .= $xnom;
                                    } else {
                                        $txt .= $v["nombreotros"];
                                    }
                                }
                                $txt .= '</td>';
                                if (trim((string) $v["numidemp"]) != '') {
                                    $txt .= '<td width="30%">';
                                    $sp = \funcionesGenerales::separarDv($v["numidemp"]);
                                    $txt .= 'No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"];
                                    /*
                                      $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                      if ($v["idtipoidentificacionotros"] == '2') {
                                      $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                      $txt .= $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"];
                                      } else {
                                      if ($v["idtipoidentificacionotros"] == '7') {
                                      $txt .= '&nbsp;';
                                      } else {
                                      if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                      $txt .= $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.');
                                      } else {
                                      $txt .= $ti . ' No. ' . $v["identificacionotros"];
                                      }
                                      }
                                      }
                                     */
                                    $txt .= '</td>';
                                } else {
                                    $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                    if ($v["idtipoidentificacionotros"] == '2') {
                                        $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                        $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                                    } else {
                                        if ($v["idtipoidentificacionotros"] == '7') {
                                            $txt .= '<td width="30%">**********</td>';
                                        } else {
                                            if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                                $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                            } else {
                                                $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                                            }
                                        }
                                    }
                                }
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                if ($v["renrem"]) {
                                    $txt .= '<br><br>' . $v["renrem"] . '<br><br>';
                                }
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            }
                        }
                    }
                    // $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                }
            }
        }
    }

    //
    if ($vinculos == 'si') {
        $retornar = true;
    }

    //
    return $retornar;
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosIntegrantesFormato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    $arrVinculosCertificados = array();
    $arrInscripciones = array();
    $vinculos = 'no';
    $libro = '';
    $registro = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == 'SOC') {
            $vinculos = 'si';
            $ind = $v["fechaotros"] . '-' . $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
            $arrInscripciones[$ind] = array();
            $arrInscripciones[$ind]["libro"] = $v["librootros"];
            $arrInscripciones[$ind]["registro"] = $v["inscripcionotros"];
            $arrInscripciones[$ind]["dupli"] = $v["dupliotros"];
            $arrInscripciones[$ind]["fecha"] = $v["fechaotros"];
        }
    }


    if ($vinculos == 'si') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $pdf->writeHTML('<strong>INTEGRANTES</strong>', true, false, true, false, 'C');
        $pdf->Ln();
    }

    // **************************************************************************** //
    // Se muestra la junta directiva nombrada con cada documento
    // **************************************************************************** //
    if ($vinculos == 'si') {
        foreach ($arrInscripciones as $insx) {
            if ($insx["dupli"] == '') {
                $insx["dupli"] = '1';
            }
            foreach ($data["inscripciones"] as $ins) {
                if ($ins["dupli"] == '') {
                    $ins["dupli"] = '1';
                }
                if ($ins["lib"] == $insx["libro"] &&
                        $ins["nreg"] == $insx["registro"] &&
                        sprintf("%02s", $ins["dupli"]) == sprintf("%02s", $insx["dupli"]) &&
                        $ins["freg"] == $insx["fecha"]) {
                    $tipdoc = $ins["tdoc"];
                    $numdoc = $ins["ndoc"];
                    $ndocext = $ins["ndocext"];
                    $fecdoc = $ins["fdoc"];
                    $idorigen = $ins["idoridoc"];
                    $txtorigen = $ins["txoridoc"];
                    $libro = $ins["lib"];
                    $registro = $ins["nreg"];
                    $dupli = sprintf("%02s", $ins["dupli"]);
                    $fecins = $ins["freg"];
                    $idmunidoc = $ins["idmunidoc"];
                    $camant = $ins["camant"];
                    $libant = $ins["libant"];
                    $regant = $ins["regant"];
                    $fecant = $ins["fecant"];
                    $camant2 = $ins["camant2"];
                    $libant2 = $ins["libant2"];
                    $regant2 = $ins["regant2"];
                    $fecant2 = $ins["fecant2"];
                    $camant3 = $ins["camant3"];
                    $libant3 = $ins["libant3"];
                    $regant3 = $ins["regant3"];
                    $fecant3 = $ins["fecant3"];
                    $camant4 = $ins["camant4"];
                    $libant4 = $ins["libant4"];
                    $regant4 = $ins["regant4"];
                    $fecant4 = $ins["fecant4"];
                    $camant5 = $ins["camant5"];
                    $libant5 = $ins["libant5"];
                    $regant5 = $ins["regant5"];
                    $fecant5 = $ins["fecant5"];
                    $tomo72 = $ins["tomo72"];
                    $folio72 = $ins["folio72"];
                    $registro72 = $ins["registro72"];

                    $aclaratoria = $ins["aclaratoria"];
                    if (!isset($ins["renrem"])) {
                        $ins["renrem"] = '';
                    }
                    $renunciasremocion = $ins["renrem"];

                    //
                    $ien = 'no';
                    foreach ($data["vinculos"] as $key => $v) {
                        if (!isset($arrVinculosCertificados[$v["id"]])) {
                            if ($v["dupliotros"] == '' || $v["dupliotros"] == '0') {
                                $v["dupliotros"] = '1';
                            }
                            if ($v["librootros"] == $insx["libro"] &&
                                    $v["inscripcionotros"] == $insx["registro"] &&
                                    sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                    $v["fechaotros"] == $insx["fecha"] &&
                                    ($v["tipovinculo"] == 'SOC')
                            ) {
                                $arrVinculosCertificados[$v["id"]] = $v["id"];
                                if ($ien == 'no') {
                                    $txt = descripcionesVinculosIntegrantesFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                    $pdf->SetFont('courier', '', 9);
                                    $txt = '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="70%"><strong>NOMBRE</strong></td>';
                                    $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
                                    $txt .= '</tr>';
                                    $txt .= '</table>';
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $ien = 'si';
                                }
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="70%">';
                                $xnom = '';
                                if (trim($v["nombre1otros"]) != '') {
                                    $xnom .= trim($v["nombre1otros"]);
                                }
                                if (trim($v["nombre2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["nombre2otros"]);
                                }
                                if (trim($v["apellido1otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido1otros"]);
                                }
                                if (trim($v["apellido2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido2otros"]);
                                }
                                if ($xnom != '') {
                                    $txt .= $xnom;
                                } else {
                                    $txt .= $v["nombreotros"];
                                }
                                if (trim($v["numidemp"]) != '') {
                                    $txt .= '<br>';
                                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                                }
                                $txt .= '</td>';
                                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                if ($v["idtipoidentificacionotros"] == '2') {
                                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                    $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                                } else {
                                    if ($v["idtipoidentificacionotros"] == '7') {
                                        $txt .= '<td width="30%">**********</td>';
                                    } else {
                                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                            $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                        } else {
                                            $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                                        }
                                    }
                                }
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                if ($v["renrem"]) {
                                    $txt .= '<br><br>' . $v["renrem"] . '<br><br>';
                                }
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            }
                        }
                    }
                    // $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                }
            }
        }
    }

    //
    if ($vinculos == 'si') {
        $retornar = true;
    }

    //
    return $retornar;
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosRevisoresFiscalesFormato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $retornar = false;
    //
    $arrVinculosCertificados = array();
    $arrInscripciones = array();
    $vinculos = 'no';
    $libro = '';
    $registro = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == 'RFP' ||
                $v["tipovinculo"] == 'RFS' ||
                $v["tipovinculo"] == 'RFS1' ||
                $v["tipovinculo"] == 'RFS2' ||
                $v["tipovinculo"] == 'RFDP' ||
                $v["tipovinculo"] == 'RFDS' ||
                $v["tipovinculoesadl"] == 'RFP' ||
                $v["tipovinculoesadl"] == 'RFS'
        ) {
            $vinculos = 'si';
            $ind = $v["fechaotros"] . '-' . $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
            $arrInscripciones[$ind] = array();
            $arrInscripciones[$ind]["libro"] = $v["librootros"];
            $arrInscripciones[$ind]["registro"] = $v["inscripcionotros"];
            $arrInscripciones[$ind]["dupli"] = $v["dupliotros"];
            $arrInscripciones[$ind]["fecha"] = $v["fechaotros"];
        }
    }


    if ($vinculos == 'si') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($pdf->tituloNombramientos == 'NO') {
            $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->tituloNombramientos = 'SI';
        }
        $pdf->writeHTML('<strong>REVISORES FISCALES</strong>', true, false, true, false, 'C');
        $pdf->Ln();
    }

    // **************************************************************************** //
    // Se muestra la junta directiva nombrada con cada documento
    // **************************************************************************** //
    if ($vinculos == 'si') {
        foreach ($arrInscripciones as $insx) {
            if ($insx["dupli"] == '') {
                $insx["dupli"] = '1';
            }
            foreach ($data["inscripciones"] as $ins) {
                if ($ins["dupli"] == '') {
                    $ins["dupli"] = '1';
                }
                if ($ins["lib"] == $insx["libro"] &&
                        $ins["nreg"] == $insx["registro"] &&
                        sprintf("%02s", $ins["dupli"]) == sprintf("%02s", $insx["dupli"]) &&
                        $ins["freg"] == $insx["fecha"]) {
                    $tipdoc = $ins["tdoc"];
                    $numdoc = $ins["ndoc"];
                    $ndocext = $ins["ndocext"];
                    $fecdoc = $ins["fdoc"];
                    $idorigen = $ins["idoridoc"];
                    $txtorigen = $ins["txoridoc"];
                    $libro = $ins["lib"];
                    $registro = $ins["nreg"];
                    $dupli = sprintf("%02s", $ins["dupli"]);
                    $fecins = $ins["freg"];
                    $idmunidoc = $ins["idmunidoc"];
                    $camant = $ins["camant"];
                    $libant = $ins["libant"];
                    $regant = $ins["regant"];
                    $fecant = $ins["fecant"];
                    $camant2 = $ins["camant2"];
                    $libant2 = $ins["libant2"];
                    $regant2 = $ins["regant2"];
                    $fecant2 = $ins["fecant2"];
                    $camant3 = $ins["camant3"];
                    $libant3 = $ins["libant3"];
                    $regant3 = $ins["regant3"];
                    $fecant3 = $ins["fecant3"];
                    $camant4 = $ins["camant4"];
                    $libant4 = $ins["libant4"];
                    $regant4 = $ins["regant4"];
                    $fecant4 = $ins["fecant4"];
                    $camant5 = $ins["camant5"];
                    $libant5 = $ins["libant5"];
                    $regant5 = $ins["regant5"];
                    $fecant5 = $ins["fecant5"];
                    $tomo72 = $ins["tomo72"];
                    $folio72 = $ins["folio72"];
                    $registro72 = $ins["registro72"];

                    $aclaratoria = $ins["aclaratoria"];
                    if (!isset($ins["renrem"])) {
                        $ins["renrem"] = '';
                    }
                    $renunciasremocion = $ins["renrem"];

                    //
                    $ien = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if (!isset($arrVinculosCertificados[$v["id"]])) {
                            if ($v["dupliotros"] == '') {
                                $v["dupliotros"] = '1';
                            }
                            if ($v["librootros"] == $insx["libro"] &&
                                    $v["inscripcionotros"] == $insx["registro"] &&
                                    sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                    $v["fechaotros"] == $insx["fecha"] &&
                                    ($v["tipovinculo"] == 'RFP' ||
                                    $v["tipovinculo"] == 'RFS' ||
                                    $v["tipovinculo"] == 'RFS1' ||
                                    $v["tipovinculo"] == 'RFS2' ||
                                    $v["tipovinculo"] == 'RFDP' ||
                                    $v["tipovinculo"] == 'RFDS' ||
                                    $v["tipovinculoesadl"] == 'RFP' ||
                                    $v["tipovinculoesadl"] == 'RFS'
                                    )
                            ) {
                                if ($ien == 'no') {
                                    $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                    $pdf->SetFont('courier', '', 9);
                                    $txt = '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                                    $txt .= '<td width="35%"><strong>NOMBRE</strong></td>';
                                    $txt .= '<td width="25%"><strong>IDENTIFICACION</strong></td>';
                                    $txt .= '<td width="10%"><strong>T. PROF</strong></td>';
                                    $txt .= '</tr>';
                                    $txt .= '</table>';
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $ien = 'si';
                                }
                                $arrVinculosCertificados[$v["id"]] = $v["id"];
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                if (trim($v["cargootros"]) == '') {
                                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                                    ) {
                                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                                    } else {
                                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                                        if (isset($arrCargo) && !empty($arrCargo)) {
                                            $descCargo = $arrCargo["descripcion"];
                                        } else {
                                            $descCargo = '';
                                        }
                                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                                    }
                                } else {
                                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                                }

                                $txt .= '<td width="35%">';
                                $xnom = '';
                                if (trim($v["nombre1otros"]) != '') {
                                    $xnom .= trim($v["nombre1otros"]);
                                }
                                if (trim($v["nombre2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["nombre2otros"]);
                                }
                                if (trim($v["apellido1otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido1otros"]);
                                }
                                if (trim($v["apellido2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido2otros"]);
                                }
                                if ($xnom != '') {
                                    $txt .= $xnom;
                                } else {
                                    $txt .= $v["nombreotros"];
                                }
                                if (trim($v["numidemp"]) != '') {
                                    $txt .= '<br>';
                                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                                }
                                $txt .= '</td>';
                                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                if ($v["idtipoidentificacionotros"] == '2') {
                                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                    $txt .= '<td width="25%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                                } else {
                                    if ($v["idtipoidentificacionotros"] == '7') {
                                        $txt .= '<td width="25%">**********</td>';
                                    } else {
                                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                            $txt .= '<td width="25%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                        } else {
                                            $txt .= '<td width="25%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                                        }
                                    }
                                }
                                $txt .= '<td width="10%">' . $v["numtarprofotros"] . '</td>';
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                if ($v["renrem"]) {
                                    $txt .= '<br><br>' . $v["renrem"] . '<br><br>';
                                }
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            }
                        }
                    }
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                }
            }
        }
    }

    //
    if ($vinculos == 'si') {
        $retornar = true;
    }

    //
    return $retornar;
}

// *************************************************************************** //
// Arma certifica de vínculos - depositarios
// *************************************************************************** //
function armarVinculosDepositariosFormato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $retornar = false;
    //
    $arrVinculosCertificados = array();
    $arrInscripciones = array();
    $vinculos = 'no';
    $libro = '';
    $registro = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == 'DPP') {
            $vinculos = 'si';
            $ind = $v["fechaotros"] . '-' . $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
            $arrInscripciones[$ind] = array();
            $arrInscripciones[$ind]["libro"] = $v["librootros"];
            $arrInscripciones[$ind]["registro"] = $v["inscripcionotros"];
            $arrInscripciones[$ind]["dupli"] = $v["dupliotros"];
            $arrInscripciones[$ind]["fecha"] = $v["fechaotros"];
        }
    }


    if ($vinculos == 'si') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($pdf->tituloNombramientos == 'NO') {
            $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->tituloNombramientos = 'SI';
        }
        $pdf->writeHTML('<strong>DEPOSITARIOS</strong>', true, false, true, false, 'C');
        $pdf->Ln();
    }

    // **************************************************************************** //
    // Se muestra la junta directiva nombrada con cada documento
    // **************************************************************************** //
    if ($vinculos == 'si') {
        foreach ($arrInscripciones as $insx) {
            if ($insx["dupli"] == '') {
                $insx["dupli"] = '1';
            }
            foreach ($data["inscripciones"] as $ins) {
                if ($ins["dupli"] == '') {
                    $ins["dupli"] = '1';
                }
                if ($ins["lib"] == $insx["libro"] &&
                        $ins["nreg"] == $insx["registro"] &&
                        sprintf("%02s", $ins["dupli"]) == sprintf("%02s", $insx["dupli"]) &&
                        $ins["freg"] == $insx["fecha"]) {
                    $tipdoc = $ins["tdoc"];
                    $numdoc = $ins["ndoc"];
                    $ndocext = $ins["ndocext"];
                    $fecdoc = $ins["fdoc"];
                    $idorigen = $ins["idoridoc"];
                    $txtorigen = $ins["txoridoc"];
                    $libro = $ins["lib"];
                    $registro = $ins["nreg"];
                    $dupli = sprintf("%02s", $ins["dupli"]);
                    $fecins = $ins["freg"];
                    $idmunidoc = $ins["idmunidoc"];
                    $camant = $ins["camant"];
                    $libant = $ins["libant"];
                    $regant = $ins["regant"];
                    $fecant = $ins["fecant"];
                    $camant2 = $ins["camant2"];
                    $libant2 = $ins["libant2"];
                    $regant2 = $ins["regant2"];
                    $fecant2 = $ins["fecant2"];
                    $camant3 = $ins["camant3"];
                    $libant3 = $ins["libant3"];
                    $regant3 = $ins["regant3"];
                    $fecant3 = $ins["fecant3"];
                    $camant4 = $ins["camant4"];
                    $libant4 = $ins["libant4"];
                    $regant4 = $ins["regant4"];
                    $fecant4 = $ins["fecant4"];
                    $camant5 = $ins["camant5"];
                    $libant5 = $ins["libant5"];
                    $regant5 = $ins["regant5"];
                    $fecant5 = $ins["fecant5"];
                    $tomo72 = $ins["tomo72"];
                    $folio72 = $ins["folio72"];
                    $registro72 = $ins["registro72"];

                    $aclaratoria = $ins["aclaratoria"];
                    if (!isset($ins["renrem"])) {
                        $ins["renrem"] = '';
                    }
                    $renunciasremocion = $ins["renrem"];

                    //
                    $ien = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if (!isset($arrVinculosCertificados[$v["id"]])) {
                            if ($v["dupliotros"] == '') {
                                $v["dupliotros"] = '1';
                            }
                            if ($v["librootros"] == $insx["libro"] &&
                                    $v["inscripcionotros"] == $insx["registro"] &&
                                    sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                    $v["fechaotros"] == $insx["fecha"] &&
                                    $v["tipovinculo"] == 'DPP'
                            ) {
                                if ($ien == 'no') {
                                    $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                    $pdf->SetFont('courier', '', 9);
                                    $txt = '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                                    $txt .= '<td width="35%"><strong>NOMBRE</strong></td>';
                                    $txt .= '<td width="35%"><strong>IDENTIFICACION</strong></td>';
                                    $txt .= '</tr>';
                                    $txt .= '</table>';
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $ien = 'si';
                                }
                                $arrVinculosCertificados[$v["id"]] = $v["id"];
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                if (trim($v["cargootros"]) == '') {
                                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                                    ) {
                                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                                    } else {
                                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                                        if (isset($arrCargo) && !empty($arrCargo)) {
                                            $descCargo = $arrCargo["descripcion"];
                                        } else {
                                            $descCargo = '';
                                        }
                                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                                    }
                                } else {
                                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                                }

                                $txt .= '<td width="35%">';
                                $xnom = '';
                                if (trim($v["nombre1otros"]) != '') {
                                    $xnom .= trim($v["nombre1otros"]);
                                }
                                if (trim($v["nombre2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["nombre2otros"]);
                                }
                                if (trim($v["apellido1otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido1otros"]);
                                }
                                if (trim($v["apellido2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido2otros"]);
                                }
                                if ($xnom != '') {
                                    $txt .= $xnom;
                                } else {
                                    $txt .= $v["nombreotros"];
                                }
                                if (trim($v["numidemp"]) != '') {
                                    $txt .= '<br>';
                                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                                }
                                $txt .= '</td>';
                                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                if ($v["idtipoidentificacionotros"] == '2') {
                                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                    $txt .= '<td width="35%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                                } else {
                                    if ($v["idtipoidentificacionotros"] == '7') {
                                        $txt .= '<td width="35%">**********</td>';
                                    } else {
                                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                            $txt .= '<td width="35%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                        } else {
                                            $txt .= '<td width="35%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                                        }
                                    }
                                }
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                if ($v["renrem"]) {
                                    $txt .= '<br><br>' . $v["renrem"] . '<br><br>';
                                }
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            }
                        }
                    }
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                }
            }
        }
    }

    //
    if ($vinculos == 'si') {
        $retornar = true;
    }

    //
    return $retornar;
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosApoderadosFormato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $retornar = false;

    //
    $arrInscripciones = array();
    $arrInscripciones1 = array();
    $vinculos = 'no';
    $libro = '';
    $registro = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == 'APOD' || $v["tipovinculoesadl"] == 'APOD') {
            if (trim($v["codcertifica"]) == '') {
                $vinculos = 'si';
                $ind = $v["fechaotros"] . '-' . $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
                $arrInscripciones[$ind] = array();
                $arrInscripciones[$ind]["libro"] = $v["librootros"];
                $arrInscripciones[$ind]["registro"] = $v["inscripcionotros"];
                if ($v["dupliotros"] == '') {
                    $v["dupliotros"] = '1';
                }
                $arrInscripciones[$ind]["dupli"] = $v["dupliotros"];
                $arrInscripciones[$ind]["fecha"] = $v["fechaotros"];
            } else {
                $vinculos = 'si';
                if ($v["dupliotros"] == '') {
                    $v["dupliotros"] = '1';
                }
                $ind = $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . sprintf("%03s", $v["dupliotros"]);
                if (!isset($arrInscripciones1[$v["codcertifica"]])) {
                    $arrInscripciones1[$v["codcertifica"]] = array();
                }
                $arrInscripciones1[$v["codcertifica"]][$ind] = $ind;
            }
        }
    }


    if ($vinculos == 'si') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($data["claseespesadl"] == '61') {
            $pdf->writeHTML('<strong>APODERADOS</strong>', true, false, true, false, 'C');
        } else {
            $pdf->writeHTML('<strong>PODERES</strong>', true, false, true, false, 'C');
        }
        $pdf->Ln();
    }

    // **************************************************************************** //
    // Se muestra la junta directiva nombrada con cada documento
    // **************************************************************************** //
    if ($vinculos == 'si') {
        // Certificar agrupados por inscripción
        foreach ($arrInscripciones as $insx) {
            if ($insx["dupli"] == '') {
                $insx["dupli"] = '1';
            }
            foreach ($data["inscripciones"] as $ins) {
                if ($ins["lib"] == $insx["libro"] &&
                        $ins["nreg"] == $insx["registro"] &&
                        $ins["dupli"] == $insx["dupli"] &&
                        $ins["freg"] == $insx["fecha"]) {
                    $tipdoc = $ins["tdoc"];
                    $numdoc = $ins["ndoc"];
                    $ndocext = $ins["ndocext"];
                    $fecdoc = $ins["fdoc"];
                    $idorigen = $ins["idoridoc"];
                    $txtorigen = $ins["txoridoc"];
                    $libro = $ins["lib"];
                    $registro = $ins["nreg"];
                    $dupli = $ins["dupli"];
                    $fecins = $ins["freg"];
                    $idmunidoc = $ins["idmunidoc"];
                    $camant = $ins["camant"];
                    $libant = $ins["libant"];
                    $regant = $ins["regant"];
                    $fecant = $ins["fecant"];
                    $camant2 = $ins["camant2"];
                    $libant2 = $ins["libant2"];
                    $regant2 = $ins["regant2"];
                    $fecant2 = $ins["fecant2"];
                    $camant3 = $ins["camant3"];
                    $libant3 = $ins["libant3"];
                    $regant3 = $ins["regant3"];
                    $fecant3 = $ins["fecant3"];
                    $camant4 = $ins["camant4"];
                    $libant4 = $ins["libant4"];
                    $regant4 = $ins["regant4"];
                    $fecant4 = $ins["fecant4"];
                    $camant5 = $ins["camant5"];
                    $libant5 = $ins["libant5"];
                    $regant5 = $ins["regant5"];
                    $fecant5 = $ins["fecant5"];
                    $tomo72 = $ins["tomo72"];
                    $folio72 = $ins["folio72"];
                    $registro72 = $ins["registro72"];
                    $acto = $ins["acto"];
                    $aclaratoria = $ins["aclaratoria"];
                    if (!isset($ins["renrem"])) {
                        $ins["renrem"] = '';
                    }
                    $renunciasremocion = $ins["renrem"];
                    $arrApodIdClase = array();
                    $arrApodNumId = array();
                    $arrApodNombre = array();
                    $arrApodApe1 = array();
                    $arrApodApe2 = array();
                    $arrApodNom1 = array();
                    $arrApodNom2 = array();
                    $iApod = 0;
                    //
                    foreach ($data["vinculos"] as $v) {
                        if ($v["dupliotros"] == '') {
                            $v["dupliotros"] = '1';
                        }
                        if ($v["librootros"] == $insx["libro"] &&
                                $v["inscripcionotros"] == $insx["registro"] &&
                                $v["dupliotros"] == $insx["dupli"] &&
                                $v["fechaotros"] == $insx["fecha"] &&
                                ($v["tipovinculo"] == 'APOD' || $v["tipovinculoesadl"] == 'APOD')) {
                            $iApod++;
                            $arrApodIdClase[$iApod] = $v["idtipoidentificacionotros"];
                            $arrApodNumId[$iApod] = $v["identificacionotros"];
                            $arrApodNombre[$iApod] = $v["nombreotros"];
                            $arrApodApe1[$iApod] = $v["apellido1otros"];
                            $arrApodApe2[$iApod] = $v["apellido2otros"];
                            $arrApodNom1[$iApod] = $v["nombre1otros"];
                            $arrApodNom2[$iApod] = $v["nombre2otros"];
                        }
                    }
                    $txt = descripcionesApoderadosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $arrApodIdClase, $arrApodNumId, $arrApodNombre, $arrApodApe1, $arrApodApe2, $arrApodNom1, $arrApodNom2, $ins["txtpoder"], $tomo72, $folio72, $registro72, $acto);
                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                }
            }
        }

        // Certificar agrupados por codcertifica
        if (!empty($arrInscripciones1)) {
            $codcertifica = '';
            foreach ($arrInscripciones1 as $codcert => $dat) {
                $txtcerti = '';
                foreach ($dat as $dt) {
                    list ($xlibro, $xinscri, $xdupli) = explode("-", $dt);
                    foreach ($data["inscripciones"] as $ins) {
                        if ($ins["lib"] == $xlibro &&
                                $ins["nreg"] == $xinscri &&
                                sprintf("%03s", $ins["dupli"]) == sprintf("%03s", $xdupli)) {
                            $arrApodIdClase = array();
                            $arrApodNumId = array();
                            $arrApodNombre = array();
                            $arrApodApe1 = array();
                            $arrApodApe2 = array();
                            $arrApodNom1 = array();
                            $arrApodNom2 = array();
                            $iApod = 0;
                            foreach ($data["vinculos"] as $v) {
                                if ($v["dupliotros"] == '') {
                                    $v["dupliotros"] = '1';
                                }
                                if ($v["codcertifica"] == $codcert &&
                                        $v["librootros"] == $ins["lib"] &&
                                        $v["inscripcionotros"] == $ins["nreg"] &&
                                        sprintf("%03s", $v["dupliotros"]) == sprintf("%03s", $ins["dupli"]) &&
                                        ($v["tipovinculo"] == 'APOD' || $v["tipovinculoesadl"] == 'APOD')) {
                                    $iApod++;
                                    $arrApodIdClase[$iApod] = $v["idtipoidentificacionotros"];
                                    $arrApodNumId[$iApod] = $v["identificacionotros"];
                                    $arrApodNombre[$iApod] = $v["nombreotros"];
                                    $arrApodApe1[$iApod] = $v["apellido1otros"];
                                    $arrApodApe2[$iApod] = $v["apellido2otros"];
                                    $arrApodNom1[$iApod] = $v["nombre1otros"];
                                    $arrApodNom2[$iApod] = $v["nombre2otros"];
                                }
                            }
                            if ($iApod > 0) {
                                if ($txtcerti == '') {
                                    $txtcerti .= 'Por ';
                                } else {
                                    $txtcerti .= ', y por ';
                                }
                                $txtcerti .= descripcionesApoderadosCodCertificaFormato2019(
                                        $mysqli,
                                        $data["organizacion"],
                                        $ins["tdoc"],
                                        $ins["ndoc"],
                                        $ins["ndocext"],
                                        $ins["fdoc"],
                                        $ins["idoridoc"],
                                        $ins["txoridoc"],
                                        $ins["idmunidoc"],
                                        $ins["lib"],
                                        $ins["nreg"],
                                        $ins["freg"],
                                        $ins["camant"],
                                        $ins["libant"],
                                        $ins["regant"],
                                        $ins["fecant"],
                                        $ins["camant2"],
                                        $ins["libant2"],
                                        $ins["regant2"],
                                        $ins["fecant2"],
                                        $ins["camant3"],
                                        $ins["libant3"],
                                        $ins["regant3"],
                                        $ins["fecant3"],
                                        $ins["camant4"],
                                        $ins["libant4"],
                                        $ins["regant4"],
                                        $ins["fecant4"],
                                        $ins["camant5"],
                                        $ins["libant5"],
                                        $ins["regant5"],
                                        $ins["fecant5"],
                                        $arrApodIdClase,
                                        $arrApodNumId,
                                        $arrApodNombre,
                                        $arrApodApe1,
                                        $arrApodApe2,
                                        $arrApodNom1,
                                        $arrApodNom2,
                                        $tomo72,
                                        $folio72,
                                        $registro72,
                                        $ins["acto"]);
                            }
                        }
                    }
                }
                $txtcerti .= ' ' . \funcionesGenerales::parsearOracion($data["crtsii"][$codcert]);
                $pdf->writeHTML('<span style="text-align:justify;">' . $txtcerti . '</span>', true, false, true, false);
                $pdf->Ln();
                $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            }
        }
    }

    //
    if ($vinculos == 'si') {
        $retornar = true;
    }

    //
    return $retornar;
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosOrganoAdministracionFormato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    if ($data["organizacion"] != '12' && $data["organizacion"] != '14') {
        return true;
    }

    //
    $arrInscripciones = array();
    $vinculos = 'no';
    $libro = '';
    $registro = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculoceresadl"] == 'OAP' || $v["tipovinculoceresadl"] == 'OAS') {
            $vinculos = 'si';
            if ($v["dupliotros"] == '') {
                $v["dupliotros"] = '1';
            }
            $ind = $v["fechaotros"] . '-' . $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . sprintf("%02s", $v["dupliotros"]);
            $arrInscripciones[$ind] = array();
            $arrInscripciones[$ind]["libro"] = $v["librootros"];
            $arrInscripciones[$ind]["registro"] = $v["inscripcionotros"];
            $arrInscripciones[$ind]["dupli"] = sprintf("%02s", $v["dupliotros"]);
            $arrInscripciones[$ind]["fecha"] = $v["fechaotros"];
        }
    }

    // ***************************************************************************************** //
    // En caso quje la junta directiva se hubiere nombrado con diferentes inscripciones
    // Se genera primero un resumen de la Junta Directiva
    // ***************************************************************************************** //
    if (count($arrInscripciones) > 1 && $vinculos == 'si') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($pdf->tituloNombramientos == 'NO') {
            $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->tituloNombramientos = 'SI';
        }
        $pdf->writeHTML('<strong>ÓRGANO DE ADMINISTRACIÓN</strong>', true, false, true, false, 'C');
        $pdf->Ln();

        $txt = '<table>';
        $txt .= '<tr align="left">';
        $txt .= '<td width="30%"><strong>CARGO</strong></td>';
        $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
        $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
        $txt .= '</tr>';
        $txt .= '</table>';
        $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
        $pdf->Ln();

        // Principales JD
        $iprin = 0;
        $pdf->SetFont('courier', '', 9);
        foreach ($data["vinculos"] as $v) {
            if ($v["tipovinculoceresadl"] == 'OAP') {
                $iprin++;
                if ($iprin == 1) {
                    $pdf->writeHTML('<strong>PRINCIPALES</strong>', true, false, true, false, 'L');
                }
                $txt = '<table>';
                $txt .= '<tr align="left">';
                if (trim($v["cargootros"]) == '') {
                    $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                    if (isset($arrCargo) && !empty($arrCargo)) {
                        $descCargo = $arrCargo["descripcion"];
                    } else {
                        $descCargo = '';
                    }
                    $txt .= '<td width="30%">' . $descCargo . '</td>';
                } else {
                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                }

                $txt .= '<td width="40%">';
                $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                if ($xnom != '') {
                    $txt .= $xnom;
                } else {
                    $txt .= $v["nombreotros"];
                }
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'EN REPRESENTACIÓN DE: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                if ($v["idtipoidentificacionotros"] == '2') {
                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                    $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                } else {
                    if ($v["idtipoidentificacionotros"] == '7') {
                        $txt .= '<td width="30%">**********</td>';
                    } else {
                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                            $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                        } else {
                            $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                        }
                    }
                }
                $txt .= '</tr>';
                $txt .= '</table>';
                if ($v["renrem"]) {
                    $txt .= '<span style="text-align:justify;">' . $v["renrem"] . "<br><br>" . '</span>';
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            }
        }
        $pdf->Ln();
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);

        // Suplentes JD
        $isup = 0;
        $pdf->SetFont('courier', '', 9);
        foreach ($data["vinculos"] as $v) {
            if ($v["tipovinculoceresadl"] == 'OAS') {
                $isup++;
                if ($isup == 1) {
                    $pdf->writeHTML('<strong>SUPLENTES</strong>', true, false, true, false, 'L');
                }
                $txt = '<table>';
                $txt .= '<tr align="left">';
                if (trim($v["cargootros"]) == '') {
                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                    ) {
                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                    } else {
                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                        if (isset($arrCargo) && !empty($arrCargo)) {
                            $descCargo = $arrCargo["descripcion"];
                        } else {
                            $descCargo = '';
                        }
                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                    }
                } else {
                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                }

                $txt .= '<td width="40%">';
                $xnom = armarNombreFormato2019($v["nombre1otros"], $v["nombre2otros"], $v["apellido1otros"], $v["apellido2otros"]);
                if ($xnom != '') {
                    $txt .= $xnom;
                } else {
                    $txt .= $v["nombreotros"];
                }
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'EN REPRESENTACIÓN DE: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                if ($v["idtipoidentificacionotros"] == '2') {
                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                    $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                } else {
                    if ($v["idtipoidentificacionotros"] == '7') {
                        $txt .= '<td width="30%">**********</td>';
                    } else {
                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                            $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                        } else {
                            $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                        }
                    }
                }
                $txt .= '</tr>';
                $txt .= '</table>';
                if ($v["renrem"]) {
                    $txt .= '<span style="text-align:justify;">' . $v["renrem"] . '<br><br></span>';
                }
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            }
        }
        $pdf->Ln();
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    } else {
        if ($vinculos == 'si') {
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            if ($pdf->tituloNombramientos == 'NO') {
                $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
                $pdf->Ln();
                $pdf->tituloNombramientos = 'SI';
            }
            $pdf->writeHTML('<strong>ÓRGANO DE ADMINISTRACIÓN</strong>', true, false, true, false, 'C');
            $pdf->Ln();
        }
    }

    // **************************************************************************** //
    // Se muestra la junta directiva nombrada con cada documento
    // **************************************************************************** //
    if ($vinculos == 'si') {
        foreach ($arrInscripciones as $insx) {
            foreach ($data["inscripciones"] as $ins) {
                if ($ins["dupli"] == '') {
                    $ins["dupli"] = '01';
                }
                if ($ins["lib"] == $insx["libro"] &&
                        $ins["nreg"] == $insx["registro"] &&
                        sprintf("%02s", $ins["dupli"]) == sprintf("%02s", $insx["dupli"]) &&
                        $ins["freg"] == $insx["fecha"]) {
                    $tipdoc = $ins["tdoc"];
                    $numdoc = $ins["ndoc"];
                    $ndocext = $ins["ndocext"];
                    $fecdoc = $ins["fdoc"];
                    $idorigen = $ins["idoridoc"];
                    $txtorigen = $ins["txoridoc"];
                    $libro = $ins["lib"];
                    $registro = $ins["nreg"];
                    $dupli = sprintf("%02s", $ins["dupli"]);
                    $fecins = $ins["freg"];
                    $idmunidoc = $ins["idmunidoc"];
                    $camant = $ins["camant"];
                    $libant = $ins["libant"];
                    $regant = $ins["regant"];
                    $fecant = $ins["fecant"];
                    $camant2 = $ins["camant2"];
                    $libant2 = $ins["libant2"];
                    $regant2 = $ins["regant2"];
                    $fecant2 = $ins["fecant2"];
                    $camant3 = $ins["camant3"];
                    $libant3 = $ins["libant3"];
                    $regant3 = $ins["regant3"];
                    $fecant3 = $ins["fecant3"];
                    $camant4 = $ins["camant4"];
                    $libant4 = $ins["libant4"];
                    $regant4 = $ins["regant4"];
                    $fecant4 = $ins["fecant4"];
                    $camant5 = $ins["camant5"];
                    $libant5 = $ins["libant5"];
                    $regant5 = $ins["regant5"];
                    $fecant5 = $ins["fecant5"];
                    $tomo72 = $ins["tomo72"];
                    $folio72 = $ins["folio72"];
                    $registro72 = $ins["registro72"];
                    $aclaratoria = $ins["aclaratoria"];
                    if (!isset($ins["renrem"])) {
                        $ins["renrem"] = '';
                    }
                    $renunciasremocion = $ins["renrem"];

                    //
                    $ien = 'no';
                    $ienp = 'no';

                    //
                    foreach ($data["vinculos"] as $v) {
                        if ($v["dupliotros"] == '') {
                            $v["dupliotros"] = '1';
                        }
                        if ($v["librootros"] == $insx["libro"] &&
                                $v["inscripcionotros"] == $insx["registro"] &&
                                sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                $v["fechaotros"] == $insx["fecha"] &&
                                $v["tipovinculoceresadl"] == 'OAP') {
                            if ($ien == 'no') {
                                $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                $pdf->Ln();
                                $pdf->writeHTML('<strong>PRINCIPALES</strong>', true, false, true, false, 'L');
                                $pdf->SetFont('courier', '', 9);
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                                $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
                                $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                $ien = 'si';
                                $ienp = 'si';
                            }
                            $txt = '<table>';
                            $txt .= '<tr align="left">';
                            if (trim($v["cargootros"]) == '') {
                                if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                                ) {
                                    $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                                } else {
                                    $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                                    if (isset($arrCargo) && !empty($arrCargo)) {
                                        $descCargo = $arrCargo["descripcion"];
                                    } else {
                                        $descCargo = '';
                                    }
                                    $txt .= '<td width="30%">' . $descCargo . '</td>';
                                }
                            } else {
                                $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                            }

                            $txt .= '<td width="40%">';
                            $xnom = '';
                            if (trim($v["nombre1otros"]) != '') {
                                $xnom .= trim($v["nombre1otros"]);
                            }
                            if (trim($v["nombre2otros"]) != '') {
                                $xnom .= ' ' . trim($v["nombre2otros"]);
                            }
                            if (trim($v["apellido1otros"]) != '') {
                                $xnom .= ' ' . trim($v["apellido1otros"]);
                            }
                            if (trim($v["apellido2otros"]) != '') {
                                $xnom .= ' ' . trim($v["apellido2otros"]);
                            }
                            if ($xnom != '') {
                                $txt .= $xnom;
                            } else {
                                $txt .= $v["nombreotros"];
                            }
                            if (trim($v["numidemp"]) != '') {
                                $txt .= '<br>';
                                $sep = \funcionesGenerales::separarDv($v["numidemp"]);
                                $txt .= 'EN REPRESENTACIÓN DE: ' . $sep["identificacion"] . '-' . $sep["dv"] . ' - ' . $v["nombreemp"];
                            }
                            $txt .= '</td>';
                            $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                            if ($v["idtipoidentificacionotros"] == '2') {
                                $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                            } else {
                                if ($v["idtipoidentificacionotros"] == '7') {
                                    $txt .= '<td width="30%">**********</td>';
                                } else {
                                    if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                        $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                    } else {
                                        $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                                    }
                                }
                            }
                            $txt .= '</tr>';
                            $txt .= '</table>';
                            if ($v["renrem"]) {
                                $txt .= '<span style="text-align:justify;">' . $v["renrem"] . '<br><br></span>';
                            }
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span><br>', true, false, true, false);
                        }
                    }
                    $txt .= '</table>';
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

                    //
                    $txt = '';
                    $ien = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if ($v["dupliotros"] == '') {
                            $v["dupliotros"] = '1';
                        }
                        if ($v["librootros"] == $insx["libro"] &&
                                $v["inscripcionotros"] == $insx["registro"] &&
                                sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                $v["fechaotros"] == $insx["fecha"] &&
                                $v["tipovinculoceresadl"] == 'OAS') {
                            if ($ien == 'no') {
                                if ($ienp == 'no') {
                                    $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                }
                                $pdf->writeHTML('<strong>SUPLENTES</strong>', true, false, true, false, 'L');
                                $pdf->SetFont('courier', '', 9);
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                                $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
                                $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                $ien = 'si';
                                $ienp = 'si';
                            }
                            $txt = '<table>';
                            $txt .= '<tr align="left">';
                            if (trim($v["cargootros"]) == '') {
                                if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                                ) {
                                    $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                                } else {
                                    $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                                    if (isset($arrCargo) && !empty($arrCargo)) {
                                        $descCargo = $arrCargo["descripcion"];
                                    } else {
                                        $descCargo = '';
                                    }
                                    $txt .= '<td width="30%">' . $descCargo . '</td>';
                                }
                            } else {
                                $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                            }

                            $txt .= '<td width="40%">';
                            $xnom = '';
                            if (trim($v["nombre1otros"]) != '') {
                                $xnom .= trim($v["nombre1otros"]);
                            }
                            if (trim($v["nombre2otros"]) != '') {
                                $xnom .= ' ' . trim($v["nombre2otros"]);
                            }
                            if (trim($v["apellido1otros"]) != '') {
                                $xnom .= ' ' . trim($v["apellido1otros"]);
                            }
                            if (trim($v["apellido2otros"]) != '') {
                                $xnom .= ' ' . trim($v["apellido2otros"]);
                            }
                            if ($xnom != '') {
                                $txt .= $xnom;
                            } else {
                                $txt .= $v["nombreotros"];
                            }
                            if (trim($v["numidemp"]) != '') {
                                $txt .= '<br>';
                                $sep = \funcionesGenerales::separarDv($v["numidemp"]);
                                $txt .= 'EN REPRESENTACIÓN DE: ' . $sep["identificacion"] . '-' . $sep["dv"] . ' - ' . $v["nombreemp"];
                            }
                            $txt .= '</td>';
                            $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                            if ($v["idtipoidentificacionotros"] == '2') {
                                $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                            } else {
                                if ($v["idtipoidentificacionotros"] == '7') {
                                    $txt .= '<td width="30%">**********</td>';
                                } else {
                                    if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                        $txt .= '<td width="30%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                    } else {
                                        $txt .= '<td width="30%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                                    }
                                }
                            }
                            $txt .= '</tr>';
                            $txt .= '</table>';
                            if ($v["renrem"]) {
                                $txt .= '<span style="text-align:justify;">' . $v["renrem"] . '<br><br></span>';
                            }
                            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                        }
                    }
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                }
            }
        }
    }

    //
    if ($vinculos == 'si') {
        $retornar = true;
    }

    //
    return $retornar;
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosProfesionalesDelDerechoFormato2019($mysqli, $pdf, $data) {

    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
    $retornar = false;
    //
    $arrVinculosCertificados = array();
    $arrInscripciones = array();
    $vinculos = 'no';
    $libro = '';
    $registro = '';
    foreach ($data["vinculos"] as $v) {
        if ($v["tipovinculo"] == 'PDDP') {
            $vinculos = 'si';
            $ind = $v["fechaotros"] . '-' . $v["librootros"] . '-' . $v["inscripcionotros"] . '-' . $v["dupliotros"];
            $arrInscripciones[$ind] = array();
            $arrInscripciones[$ind]["libro"] = $v["librootros"];
            $arrInscripciones[$ind]["registro"] = $v["inscripcionotros"];
            $arrInscripciones[$ind]["dupli"] = $v["dupliotros"];
            $arrInscripciones[$ind]["fecha"] = $v["fechaotros"];
        }
    }


    if ($vinculos == 'si') {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        if ($pdf->tituloNombramientos == 'NO') {
            $pdf->writeHTML('<strong>NOMBRAMIENTOS</strong>', true, false, true, false, 'C');
            $pdf->Ln();
            $pdf->tituloNombramientos = 'SI';
        }
        $pdf->writeHTML('<strong>PROFESIONALES DEL DERECHO</strong>', true, false, true, false, 'C');
        $pdf->Ln();
    }

    // **************************************************************************** //
    // Se muestran los profesionales del derecho nombrados
    // **************************************************************************** //
    if ($vinculos == 'si') {
        foreach ($arrInscripciones as $insx) {
            if ($insx["dupli"] == '') {
                $insx["dupli"] = '1';
            }
            foreach ($data["inscripciones"] as $ins) {
                if ($ins["dupli"] == '') {
                    $ins["dupli"] = '1';
                }
                if ($ins["lib"] == $insx["libro"] &&
                        $ins["nreg"] == $insx["registro"] &&
                        sprintf("%02s", $ins["dupli"]) == sprintf("%02s", $insx["dupli"]) &&
                        $ins["freg"] == $insx["fecha"]) {
                    $tipdoc = $ins["tdoc"];
                    $numdoc = $ins["ndoc"];
                    $ndocext = $ins["ndocext"];
                    $fecdoc = $ins["fdoc"];
                    $idorigen = $ins["idoridoc"];
                    $txtorigen = $ins["txoridoc"];
                    $libro = $ins["lib"];
                    $registro = $ins["nreg"];
                    $dupli = sprintf("%02s", $ins["dupli"]);
                    $fecins = $ins["freg"];
                    $idmunidoc = $ins["idmunidoc"];
                    $camant = $ins["camant"];
                    $libant = $ins["libant"];
                    $regant = $ins["regant"];
                    $fecant = $ins["fecant"];
                    $camant2 = $ins["camant2"];
                    $libant2 = $ins["libant2"];
                    $regant2 = $ins["regant2"];
                    $fecant2 = $ins["fecant2"];
                    $camant3 = $ins["camant3"];
                    $libant3 = $ins["libant3"];
                    $regant3 = $ins["regant3"];
                    $fecant3 = $ins["fecant3"];
                    $camant4 = $ins["camant4"];
                    $libant4 = $ins["libant4"];
                    $regant4 = $ins["regant4"];
                    $fecant4 = $ins["fecant4"];
                    $camant5 = $ins["camant5"];
                    $libant5 = $ins["libant5"];
                    $regant5 = $ins["regant5"];
                    $fecant5 = $ins["fecant5"];
                    $tomo72 = $ins["tomo72"];
                    $folio72 = $ins["folio72"];
                    $registro72 = $ins["registro72"];
                    $aclaratoria = $ins["aclaratoria"];
                    if (!isset($ins["renrem"])) {
                        $ins["renrem"] = '';
                    }
                    $renunciasremocion = $ins["renrem"];

                    //
                    $ien = 'no';
                    foreach ($data["vinculos"] as $v) {
                        if (!isset($arrVinculosCertificados[$v["id"]])) {
                            if ($v["dupliotros"] == '') {
                                $v["dupliotros"] = '1';
                            }
                            if ($v["librootros"] == $insx["libro"] &&
                                    $v["inscripcionotros"] == $insx["registro"] &&
                                    sprintf("%02s", $v["dupliotros"]) == sprintf("%02s", $insx["dupli"]) &&
                                    $v["fechaotros"] == $insx["fecha"] &&
                                    ($v["tipovinculo"] == 'PDDP')
                            ) {
                                if ($ien == 'no') {
                                    $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                    $pdf->SetFont('courier', '', 9);
                                    $txt = '<table>';
                                    $txt .= '<tr align="left">';
                                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                                    $txt .= '<td width="35%"><strong>NOMBRE</strong></td>';
                                    $txt .= '<td width="25%"><strong>IDENTIFICACION</strong></td>';
                                    $txt .= '<td width="10%"><strong>T. PROF</strong></td>';
                                    $txt .= '</tr>';
                                    $txt .= '</table>';
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $ien = 'si';
                                }
                                $arrVinculosCertificados[$v["id"]] = $v["id"];
                                $txt = '<table>';
                                $txt .= '<tr align="left">';
                                if (trim($v["cargootros"]) == '') {
                                    if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                                    ) {
                                        $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                                    } else {
                                        $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                                        if (isset($arrCargo) && !empty($arrCargo)) {
                                            $descCargo = $arrCargo["descripcion"];
                                        } else {
                                            $descCargo = '';
                                        }
                                        $txt .= '<td width="30%">' . $descCargo . '</td>';
                                    }
                                } else {
                                    $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
                                }

                                $txt .= '<td width="35%">';
                                $xnom = '';
                                if (trim($v["nombre1otros"]) != '') {
                                    $xnom .= trim($v["nombre1otros"]);
                                }
                                if (trim($v["nombre2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["nombre2otros"]);
                                }
                                if (trim($v["apellido1otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido1otros"]);
                                }
                                if (trim($v["apellido2otros"]) != '') {
                                    $xnom .= ' ' . trim($v["apellido2otros"]);
                                }
                                if ($xnom != '') {
                                    $txt .= $xnom;
                                } else {
                                    $txt .= $v["nombreotros"];
                                }
                                if (trim($v["numidemp"]) != '') {
                                    $txt .= '<br>';
                                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                                }
                                $txt .= '</td>';
                                $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
                                if ($v["idtipoidentificacionotros"] == '2') {
                                    $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                                    $txt .= '<td width="25%">' . $ti . ' No. ' . number_format($sp["identificacion"], 0, ',', '.') . '-' . $sp["dv"] . '</td>';
                                } else {
                                    if ($v["idtipoidentificacionotros"] == '7') {
                                        $txt .= '<td width="25%">**********</td>';
                                    } else {
                                        if (is_numeric($v["identificacionotros"]) && $v["idtipoidentificacionotros"] != '5') {
                                            $txt .= '<td width="25%">' . $ti . ' No. ' . number_format($v["identificacionotros"], 0, ',', '.') . '</td>';
                                        } else {
                                            $txt .= '<td width="25%">' . $ti . ' No. ' . $v["identificacionotros"] . '</td>';
                                        }
                                    }
                                }
                                $txt .= '<td width="10%">' . $v["numtarprofotros"] . '</td>';
                                $txt .= '</tr>';
                                $txt .= '</table>';
                                if ($v["renrem"]) {
                                    $txt .= '<br><br>' . $v["renrem"] . '<br><br>';
                                }
                                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                            }
                        }
                    }
                    $pdf->Ln();
                    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
                }
            }
        }
    }

    //
    if ($vinculos == 'si') {
        $retornar = true;
    }

    //
    return $retornar;
}

// *************************************************************************** //
// Arma certifica de vínculos
// *************************************************************************** //
function armarVinculosClaseFormato2019($mysqli, $pdf, $data, $tipovinculo, $titulo = 'si') {
    //
    $retornar = false;
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $vinculos = 'no';
    $vinculoscompletos = 'si';
    foreach ($data["vinculos"] as $v) {
        if (($data["organizacion"] != '12' && $data["organizacion"] != '14' && $v["tipovinculo"] == $tipovinculo) ||
                (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $v["tipovinculoceresadl"] == $tipovinculo)) {
            $vinculos = 'si';
            if (ltrim(trim($v["librootros"]), "0") == '' || ltrim(trim($v["inscripcionotros"]), "0") == '') {
                $vinculoscompletos = 'no';
            }
        }
    }

    if ($vinculos == 'si' && $vinculoscompletos == 'si') {
        armarVinculosClaseSiiFormato2019($mysqli, $pdf, $data, $tipovinculo, $titulo);
    }
    if ($vinculos == 'si' && $vinculoscompletos == 'no') {
        armarVinculosClaseSirepFormato2019($mysqli, $pdf, $data, $tipovinculo, $titulo);
    }
    if ($vinculos == 'si') {
        $retornar = true;
    }
    return $retornar;
}

function armarVinculoTituloClaseFormato2019($mysqli, $pdf, $data, $tipovinculo) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($pdf->tituloNombramientos == 'NO') {
        $txt = '<strong>NOMBRAMIENTOS</strong>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $pdf->Ln();
        $pdf->tituloNombramientos = 'SI';
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

function armarVinculosClaseSiiFormato2019($mysqli, $pdf, $data, $tipovinculo, $titulo = 'si') {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    if ($titulo == 'si') {
        armarVinculoTituloClaseFormato2019($mysqli, $pdf, $data, $tipovinculo);
    }

    //
    $libsel = '';
    $inscsel = '';
    $duplisel = '';
    $canvins = 0;
    foreach ($data["vinculos"] as $v) {
        if (($data["organizacion"] != '12' && $data["organizacion"] != '14' && $v["tipovinculo"] == $tipovinculo) ||
                (($data["organizacion"] == '12' || $data["organizacion"] == '14') && $v["tipovinculoceresadl"] == $tipovinculo)) {
            $canvins++;
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
                            $dupli = $ins["dupli"];
                            $fecins = $ins["freg"];
                            $idmunidoc = $ins["idmunidoc"];
                            $camant = $ins["camant"];
                            $libant = $ins["libant"];
                            $regant = $ins["regant"];
                            $fecant = $ins["fecant"];
                            $camant2 = $ins["camant2"];
                            $libant2 = $ins["libant2"];
                            $regant2 = $ins["regant2"];
                            $fecant2 = $ins["fecant2"];
                            $camant3 = $ins["camant3"];
                            $libant3 = $ins["libant3"];
                            $regant3 = $ins["regant3"];
                            $fecant3 = $ins["fecant3"];
                            $camant4 = $ins["camant4"];
                            $libant4 = $ins["libant4"];
                            $regant4 = $ins["regant4"];
                            $fecant4 = $ins["fecant4"];
                            $camant5 = $ins["camant5"];
                            $libant5 = $ins["libant5"];
                            $regant5 = $ins["regant5"];
                            $fecant5 = $ins["fecant5"];
                            $aclaratoria = $ins["aclaratoria"];
                            if (!isset($ins["renrem"])) {
                                $ins["renrem"] = '';
                            }
                            $renunciasremocion = $ins["renrem"];
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
                            $dupli = $ins["dupli"];
                            $fecins = $ins["freg"];
                            $idmunidoc = $ins["idmunidoc"];
                            $camant = $ins["camant"];
                            $libant = $ins["libant"];
                            $regant = $ins["regant"];
                            $fecant = $ins["fecant"];
                            $camant2 = $ins["camant2"];
                            $libant2 = $ins["libant2"];
                            $regant2 = $ins["regant2"];
                            $fecant2 = $ins["fecant2"];
                            $camant3 = $ins["camant3"];
                            $libant3 = $ins["libant3"];
                            $regant3 = $ins["regant3"];
                            $fecant3 = $ins["fecant3"];
                            $camant4 = $ins["camant4"];
                            $libant4 = $ins["libant4"];
                            $regant4 = $ins["regant4"];
                            $fecant4 = $ins["fecant4"];
                            $camant5 = $ins["camant5"];
                            $libant5 = $ins["libant5"];
                            $regant5 = $ins["regant5"];
                            $fecant5 = $ins["fecant5"];
                            $tomo72 = $ins["tomo72"];
                            $folio72 = $ins["folio72"];
                            $registro72 = $ins["registro72"];
                            $aclaratoria = $ins["aclaratoria"];
                            if (!isset($ins["renrem"])) {
                                $ins["renrem"] = '';
                            }
                            $renunciasremocion = $ins["renrem"];
                        }
                    }
                }
            }

            //
            if ($libsel != $ins["lib"] ||
                    $inscsel != $ins["nreg"] ||
                    $duplisel != $ins["dupli"]) {
                $txt = descripcionesVinculosFormato2019($mysqli, $data["organizacion"], $tipdoc, $numdoc, $ndocext, $fecdoc, $idorigen, $txtorigen, $idmunidoc, $libro, $registro, $fecins, '', $camant, $libant, $regant, $fecant, $camant2, $libant2, $regant2, $fecant2, $camant3, $libant3, $regant3, $fecant3, $camant4, $libant4, $regant4, $fecant4, $camant5, $libant5, $regant5, $fecant5, $aclaratoria, $renunciasremocion, $tomo72, $folio72, $registro72);
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $pdf->Ln();
                $pdf->SetFont('courier', '', 9);
                $txt = '<table>';
                $txt .= '<tr align="left">';
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
                    $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
                    $txt .= '<td width="10%"><strong>T. PROF</strong></td>';
                } else {
                    $txt .= '<td width="30%"><strong>CARGO</strong></td>';
                    $txt .= '<td width="40%"><strong>NOMBRE</strong></td>';
                    $txt .= '<td width="30%"><strong>IDENTIFICACION</strong></td>';
                }
                $txt .= '</tr>';
                $txt .= '</table>';
                $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                $libsel = $ins["lib"];
                $inscsel = $ins["nreg"];
                $duplisel = $ins["dupli"];
            } else {
                $pdf->SetFont('courier', '', 9);
            }

            //
            $txt = '<table>';
            $txt .= '<tr align="left">';
            if (trim($v["cargootros"]) == '') {
                if (($v["idcargootros"] >= '0001' && $v["idcargootros"] <= '0099') || ($v["idcargootros"] == '0000' || $v["idcargootros"] == '')
                ) {
                    $txt .= '<td width="30%">' . $v["descripcionvinculotros"] . '</td>';
                } else {
                    $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                    if (isset($arrCargo) && !empty($arrCargo)) {
                        $descCargo = $arrCargo["descripcion"];
                    } else {
                        $descCargo = '';
                    }
                    $txt .= '<td width="30%">' . $descCargo . '</td>';
                }
            } else {
                $txt .= '<td width="30%">' . $v["cargootros"] . '</td>';
            }

            //
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
                $xnom = '';
                if (trim($v["nombre1otros"]) != '') {
                    $xnom .= trim($v["nombre1otros"]);
                }
                if (trim($v["nombre2otros"]) != '') {
                    $xnom .= ' ' . trim($v["nombre2otros"]);
                }
                if (trim($v["apellido1otros"]) != '') {
                    $xnom .= ' ' . trim($v["apellido1otros"]);
                }
                if (trim($v["apellido2otros"]) != '') {
                    $xnom .= ' ' . trim($v["apellido2otros"]);
                }
                if ($xnom != '') {
                    $txt .= $xnom;
                } else {
                    $txt .= $v["nombreotros"];
                }
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
            } else {
                $txt .= '<td width="40%">';
                $xnom = '';
                if (trim($v["nombre1otros"]) != '') {
                    $xnom .= trim($v["nombre1otros"]);
                }
                if (trim($v["nombre2otros"]) != '') {
                    $xnom .= ' ' . trim($v["nombre2otros"]);
                }
                if (trim($v["apellido1otros"]) != '') {
                    $xnom .= ' ' . trim($v["apellido1otros"]);
                }
                if (trim($v["apellido2otros"]) != '') {
                    $xnom .= ' ' . trim($v["apellido2otros"]);
                }
                if ($xnom != '') {
                    $txt .= $xnom;
                } else {
                    $txt .= $v["nombreotros"];
                }
                if (trim($v["numidemp"]) != '') {
                    $txt .= '<br>';
                    $txt .= 'ENTIDAD: ' . $v["numidemp"] . ' - ' . $v["nombreemp"];
                }
                $txt .= '</td>';
            }

            $ti = encontrarTipoIdentificacionFormato2019($v["idtipoidentificacionotros"]);
            if ($v["idtipoidentificacionotros"] == '2') {
                $sp = \funcionesGenerales::separarDv($v["identificacionotros"]);
                $txt .= '<td width="30%">' . $ti . ' ' . $sp["identificacion"] . '-' . $sp["dv"] . '</td>';
            } else {
                if ($v["idtipoidentificacionotros"] == '7') {
                    $txt .= '<td width="30%">**********</td>';
                } else {
                    if ($v["idtipoidentificacionotros"] == '5' || $v["idtipoidentificacionotros"] == 'E') {
                        $txt .= '<td width="30%">' . $ti . ' ' . $v["identificacionotros"] . '</td>';
                    } else {
                        if (is_numeric($v["identificacionotros"])) {
                            $txt .= '<td width="30%">' . $ti . ' ' . number_format($v["identificacionotros"], 0) . '</td>';
                        } else {
                            $txt .= '<td width="30%">' . $ti . ' ' . $v["identificacionotros"] . '</td>';
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
                $txt .= '<td width="10%">' . $v["numtarprofotros"] . '</td>';
            }
            $txt .= '</tr>';
            $txt .= '</table>';
            if ($v["renrem"] != '') {
                $txt .= '<span style="text-align:justify;">' . $v["renrem"] . '<br><br></span>';
            }
            $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);

            //
            $pdf->SetFont('courier', '', $pdf->tamanoLetra);
            foreach ($data["inscripciones"] as $ins) {
                if (isset($ins["vinculoafectado"])) {
                    if ($ins["vinculoafectado"] == $v["vinculootros"] && $ins["identificacionafectada"] == $v["identificacionotros"]) {
                        if ($ins["crev"] == '0') {
                            if ($ins["fechalimite"] > date("Ymd")) {
                                if ($ins["grupoacto"] == '080') { // Si impone sanciones, se excluye si las levanta
                                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $ins["acto"], $ins["tdoc"], $ins["ndoc"], $ins["ndocext"], $ins["fdoc"], $ins["idoridoc"], $ins["origendocumento"], $ins["idmunidoc"], $ins["lib"], $ins["nreg"], $ins["freg"], $ins["not"], $ins["camant"], $ins["libant"], $ins["regant"], $ins["fecant"], $ins["camant2"], $ins["libant2"], $ins["regant2"], $ins["fecant2"], $ins["camant3"], $ins["libant3"], $ins["regant3"], $i["fecant3"], $ins["camant4"], $ins["libant4"], $ins["regant4"], $i["fecant4"], $ins["camant5"], $ins["libant5"], $ins["regant5"], $i["fecant5"], $ins["aclaratoria"], $ins["tomo72"], $ins["folio72"], $ins["registro72"]);
                                    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
                                    $pdf->Ln();
                                }
                                if ($ins["grupoacto"] == '082') { // Suspensión temporal de sanciones a nombramientos
                                    $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $ins["acto"], $ins["tdoc"], $ins["ndoc"], $ins["ndocext"], $ins["fdoc"], $ins["idoridoc"], $ins["origendocumento"], $ins["idmunidoc"], $ins["lib"], $ins["nreg"], $ins["freg"], $ins["not"], $ins["camant"], $ins["libant"], $ins["regant"], $ins["fecant"], $ins["camant2"], $ins["libant2"], $ins["regant2"], $ins["fecant2"], $ins["camant3"], $ins["libant3"], $ins["regant3"], $i["fecant3"], $ins["camant4"], $ins["libant4"], $ins["regant4"], $i["fecant4"], $ins["camant5"], $ins["libant5"], $ins["regant5"], $i["fecant5"], $ins["aclaratoria"], $ins["tomo72"], $ins["folio72"], $ins["registro72"]);
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
    if ($canvins != 0) {
        $pdf->Ln();
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

    $pdf->SetFont('courier', '', $pdf->tamanoLetra);
}

function armarVinculosClaseSirepFormato2019($mysqli, $pdf, $data, $tipovinculo) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    //
    armarVinculoTituloClaseFormato2019($mysqli, $pdf, $data, $tipovinculo);

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
                $arrCargo = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14' and idcodigo='" . $v["idcargootros"] . "'");
                if (isset($arrCargo) && !empty($arrCargo)) {
                    $descCargo = $arrCargo["descripcion"];
                } else {
                    $descCargo = '';
                }
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

function descripcionesDisolucionFechaVencimientoFormato2019($mysqli, $pdf, $data, $fecha) {
    $txt = 'La persona jurídica se disolvió y entró en estado de liquidación por vencimiento del término de duración el ' . \funcionesGenerales::mostrarFechaLetras1($fecha);
    $txt = \funcionesGenerales::agregarPuntoFinal($txt);
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->Ln();
}

/**
 * 
 * @param type $mysqli
 * @param type $organizacion
 * @param type $acto
 * @param string $tipdoc
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
 * @param type $nan
 * @param type $nombre
 * @param type $comple
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @param type $camant2
 * @param type $libant2
 * @param type $regant2
 * @param type $fecant2
 * @param type $camant3
 * @param type $libant3
 * @param type $regant3
 * @param type $fecant3
 * @param type $camant4
 * @param type $libant4
 * @param type $regant4
 * @param type $fecant4
 * @param type $camant5
 * @param type $libant5
 * @param type $regant5
 * @param type $fecant5
 * @param type $aclaratoria
 * @param type $tomo72
 * @param type $folio72
 * @param type $registro72
 * @param type $sigla
 * @param type $nat
 * @return string
 */
function descripcionesFormato2019($mysqli, $organizacion1, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro, $registro, $fecins, $noticia, $nan = array(), $nombre = '', $comple = '', $camant = '', $libant = '', $regant = '', $fecant = '', $camant2 = '', $libant2 = '', $regant2 = '', $fecant2 = '', $camant3 = '', $libant3 = '', $regant3 = '', $fecant3 = '', $camant4 = '', $libant4 = '', $regant4 = '', $fecant4 = '', $camant5 = '', $libant5 = '', $regant5 = '', $fecant5 = '', $aclaratoria = '', $tomo72 = '', $folio72 = '', $registro72 = '', $sigla = '', $nat = '') {

    $noticia = str_replace('Ẽ', 'Ê', (string) $noticia);
    $noticia = str_replace('ĩ', 'Î', (string) $noticia);
    $noticia = str_replace('Ĩ', 'Î', (string) $noticia);

    if (strpos($organizacion1, '|') === false) {
        $categoria = '';
        $organizacion = $organizacion1;
    } else {
        list ($organizacion, $categoria) = explode("|", $organizacion1);
    }

    //
    if ($numdocext != '') {
        $numdoc = $numdocext;
    }

    //
    $txt = 'Por ';

    //
    if ($tipdoc == '15' && $numdoc == '1727') {
        $tipdoc = '38';
    }

    //
    if ($tipdoc == '38' && $numdoc == '1727') {
        $txt = 'De acuerdo a lo establecido en la ';
        $numdoc = '';
    }

    $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
    if ($txtDoc == '') {
        $txtDoc = 'documento';
    }

    //
    $txt .= $txtDoc . ' ';

    //
    if (trim($numdocext) != '' && trim($numdocext) != '0' && strtoupper(trim($numdocext)) != 'NA' && strtoupper(trim($numdocext)) != 'N/A' && strtoupper(trim($numdocext)) != 'SN' && strtoupper(trim($numdocext)) != 'S/N') {
        $txt .= 'No. ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
            $txt .= 'No. ' . trim($numdoc) . ' ';
        }
    }

    //
    if ($fecdoc != '' && $tipdoc != '38') {
        $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
    }

    //

    $txtSuscribe = '';

    //
    if ($tipdoc != '38') {
        if ($txtorigen != '') {
            if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
                $txtorigen = '';
            }
            $txtorigen = str_replace("NOTARIAS NOTARIA", "Notaría", $txtorigen);
            $txtorigen = str_replace("ACTAS ", "", $txtorigen);
            $txtorigen = str_replace("JUZGADOS CIVILES DEL CIRCUITO ", "", $txtorigen);
            $txtSuscribe = ucwords(strtolower($txtorigen));
        } else {
            if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
                $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
            }
        }

        //
        $txtParticula = 'de la';
        $noparticula = '';
        if (strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTANTE LEGAL'
        ) {
            $txtParticula = 'de';
            $txtSuscribe = 'el Representante Legal';
            $noparticula = 'si';
        }

        //
        if (substr(strtoupper($txtSuscribe), 0, 19) == 'REPRESENTANTE LEGAL') {
            $txtParticula = 'de';
            $txtSuscribe = 'el ' . $txtSuscribe;
            $noparticula = 'si';
        }

        if (strtoupper($txtSuscribe) == 'REVISOR FISCAL') {
            $txtParticula = 'de';
            $txtSuscribe = 'el Revisor Fiscal';
            $noparticula = 'si';
        }

        if (strtoupper($txtSuscribe) == 'COMERCIANTE') {
            $txtParticula = 'de';
            $txtSuscribe = 'el Comerciante';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
            $txtParticula = 'de';
            $txtSuscribe = 'la Junta de Socios';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
            $txtParticula = 'de';
            $txtSuscribe = 'la Junta Directiva';
            $noparticula = 'si';
        }
        if (substr(strtoupper($txtSuscribe), 0, 7) == 'JUZGADO') {
            $txtParticula = 'del';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
            $txtParticula = 'de';
            $txtSuscribe = 'el Propietario';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
            $txtParticula = 'de';
            $txtSuscribe = 'La Administración de Impuestos Nacionales';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
            $txtParticula = 'de';
            $txtSuscribe = 'Accionistas';
            $noparticula = 'si';
        }

        // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
        if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
            $txtParticula = 'de';
            $txtSuscribe = 'Único Accionista';
            $noparticula = 'si';
        }

        // 2018-06-18: JINT
        if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
            $txtParticula = 'del';
            $txtSuscribe = 'Accionista Único';
            $noparticula = 'si';
        }

        // 2018-06-18: JINT
        if (strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN') {
            $txtParticula = 'del';
            $txtSuscribe = 'Comité de Administración';
            $noparticula = 'si';
        }

        // 2018-06-18: JINT
        if (strtoupper($txtSuscribe) == 'CONTADOR PÚBLICO' ||
                strtoupper($txtSuscribe) == 'CONTADOR PúBLICO' ||
                $txtSuscribe == 'Contador Público') {
            $txtParticula = 'del';
            $txtSuscribe = 'Contador Público';
            $noparticula = 'si';
        }


        // 2018-06-26: JINT
        if (strtoupper($txtSuscribe) == 'EL COMERCIANTE') {
            $txtParticula = '';
            $txtSuscribe = '';
            $noparticula = 'si';
        }


        if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
            if ($organizacion == '11') {
                if ($acto == '0040') {
                    $txtParticula = '';
                    $txtSuscribe = 'del Empresario Constituyente';
                    $noparticula = 'si';
                }
            }
        }

        if (strtoupper($txtSuscribe) == 'EL SUSCRITO') {
            $txtParticula = '';
            $txtSuscribe = 'de el Suscrito';
            $noparticula = 'si';
        }

        if (strtoupper($txtSuscribe) == 'MUNICIPIO') {
            $txtParticula = '';
            $txtSuscribe = 'del municipio';
            $noparticula = 'si';
        }

        if ($noparticula != 'si') {
            $origenes = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='origenes' and idcodigo='" . strtoupper($txtSuscribe) . "'");
            if ($origenes && !empty($origenes)) {
                $txtParticula = $origenes["campo1"];
                $txtSuscribe = $origenes["campo2"];
                $noparticula = 'si';
            }
        }


        if ($txtSuscribe != '') {
            $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
        }

        //
        if (strtoupper($txtSuscribe) != 'DE EL SUSCRITO') {
            if (strtoupper($txtSuscribe) == 'DEL MUNICIPIO') {
                $txt .= ' de ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $munori)));
            } else {
                // if ($tipdoc == '02' || $tipdoc == '04') {
                if ($munori != '' && $munori != '00000' && $munori != '99999') {
                    $txt .= ' de ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $munori)));
                }
            }
        }

        //
        $txt = str_replace(" , ", ", ", $txt);
        $txt = str_replace(array("DE LA LA", "DE LA EL"), array("DE LA", "DE EL"), $txt);
        $txt = str_replace(array("de la la", "de la el"), array("de la", "de el"), $txt);
        $txt = str_replace(array("de la los", "de la Los"), array("de los", "de Los"), $txt);
    }

    if (trim((string) $tomo72) != '') {
        $txt .= ', inscrito bajo el número  ' . $registro72 . ', del tomo ' . $tomo72 . ', folio ' . $folio72 . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ' ';
    } else {
        if ($camant != '') {
            $txt .= ', inscrita inicialmente en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant);
            if ($regant != '') {
                $txt .= ' bajo el No. ' . $regant;
            }
            if ($libant != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant);
            }
        }

        if ($camant2 != '') {
            $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant2))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant2);
            if ($regant2 != '') {
                $txt .= ' bajo el No. ' . $regant2;
            }
            if ($libant2 != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant2);
            }
        }

        if ($camant3 != '') {
            $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant3))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant3);
            if ($regant3 != '') {
                $txt .= ' bajo el No. ' . $regant3;
            }
            if ($libant3 != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant3);
            }
        }

        if ($camant4 != '') {
            $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant4))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant4);
            if ($regant4 != '') {
                $txt .= ' bajo el No. ' . $regant4;
            }
            if ($libant4 != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant4);
            }
        }

        if ($camant5 != '') {
            $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant5))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant5);
            if ($regant5 != '') {
                $txt .= ' bajo el No. ' . $regant5;
            }
            if ($libant5 != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant5);
            }
        }


        //
        if ($camant != '' || $camant2 != '' || $camant3 != '' || $camant4 != '' || $camant5 != '') {
            $txt .= ' y posteriormente inscrita ';
        } else {
            $txt .= ', inscrito ';
        }
        $txt .= 'en esta Cámara de Comercio el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', con el No. ' . $registro . ' ';
        $txt .= 'del Libro ' . retornarLibroFormato2019($libro) . ', ';
    }

    //
    $si = 'no';
    if ($acto == '0030') { // Constitución
        $txt .= 'se inscribió ';
        $si = 'si';
    }
    if ($acto == '0040') { // Constitución
        $txt .= 'se constituyó ';
        $si = 'si';
    }

    if ($acto == '0042') { // Constitución por cambio de domicilio
        // $txt .= 'se inscribe el cambio de domicilio de ';
        $si = 'si';
    }

    if ($acto == '0050') { // Constitución
        $txt .= 'se constituyó ';
        $si = 'si';
    }
    if ($acto == '0080') { // Constitución
        $txt .= ' ';
        $si = 'si';
    }

    if ($acto == '0192') { // Oposiciones
        $txt .= 'se inscribió ';
        $si = 'si';
    }
    if ($acto == '0400') { // Transformaciones
        $txt .= 'se inscribió ';
        $si = 'si';
    }
    if ($acto >= '0530' && $acto <= '0540') { // Cancelaciones
        $txt .= 'se inscribio ';
        $si = 'si';
    }
    if ($acto >= '0650' && $acto <= '0690') { // Liquidación obligatoria
        $txt .= 'se insribió ';
        $si = 'si';
    }
    if ($libro == 'RM19') { // Reestructuracion
        $txt .= 'se inscribió ';
        $si = 'si';
    }
    if ($libro == 'RM08') { // Embargos
        $txt .= ' se decretó ';
        $si = 'si';
    }
    if ($libro == 'RM11') { // Reestructuracion
        $txt .= 'se inscribió ';
        $si = 'si';
    }
    if ($acto == '1921') { // Resoluciones
        $txt .= 'se resolvió ';
        $si = 'si';
    }

    if ($acto == '4000') { // SITIOS WEB
        $txt .= 'se registró ';
        $si = 'si';
    }
    if ($libro == '9997') { // Cambios de jurisdicción
        $txt .= 'se decretó ';
        $si = 'si';
    }
    if ($acto == '8999') { // Cambios de jurisdicción
        $si = 'si';
    }

    if ($si == 'no') {
        $txt .= 'se decretó ';
    }

    //
    $pegarNoticia = 'si';
    if ($acto == '0040') { // Constitución
        if (!empty($nan)) {
            if ($libro == 'RM13') {
                $txt .= 'la persona jurídica de naturaleza civil denominada ' . $nan[1]["nom"];
                $pegarNoticia = 'no';
            } else {
                if ($libro == 'RE51' || $libro == 'RE52' || $libro == 'RE53' || $libro == 'RE54' || $libro == 'RE55') {
                    if (trim((string) $nat) == '') {
                        if ($organizacion == '14') {
                            $txt .= ' la persona jurídica del sector solidario denominada ' . $nan[1]["nom"];
                        } else {
                            $txt .= ' la entidad sin ánimo de lucro denominada ' . $nan[1]["nom"];
                        }
                    } else {
                        if ($organizacion == '14') {
                            $txt .= ' la persona jurídica del sector solidario de naturaleza ' . $nat . ' denominada ' . $nan[1]["nom"];
                        } else {
                            $txt .= ' la entidad sin ánimo de lucro de naturaleza ' . $nat . ' denominada ' . $nan[1]["nom"];
                        }
                    }
                    $pegarNoticia = 'no';
                } else {
                    if ($organizacion == '08') {
                        $txt .= 'la sucursal de sociedad extranjera de naturaleza comercial denominada ' . $nan[1]["nom"];
                    } else {
                        if ($organizacion == '11') {
                            $txt .= 'la empresa unipersonal de naturaleza comercial denominada ' . $nan[1]["nom"];
                        } else {
                            if ($organizacion == '10') {
                                $txt .= 'la persona jurídica de naturaleza civil denominada ' . $nan[1]["nom"];
                            } else {
                                if ($organizacion == '12' || $organizacion == '14') {
                                    $txt .= 'la entidad sin ánimo de lucro denominada ' . $nan[1]["nom"];
                                } else {
                                    $txt .= 'la persona jurídica de naturaleza comercial denominada ' . $nan[1]["nom"];
                                }
                            }
                        }
                    }
                    $pegarNoticia = 'no';
                }
                /*
                  if ($sigla != '') {
                  $txt .= ', Sigla ' . $sigla;
                  }
                 */
            }
        } else {
            if ($nombre != '') {
                $nom1 = \funcionesGenerales::borrarPalabrasAutomaticas($nombre, $comple);
                if ($libro == 'RM13') {
                    $txt .= 'la persona jurídica de naturaleza civil denominada ' . $nom1;
                    $pegarNoticia = 'no';
                } else {
                    if ($libro == 'RE51' || $libro == 'RE52' || $libro == 'RE53' || $libro == 'RE54' || $libro == 'RE55') {
                        if (trim((string) $nat) == '') {
                            if ($organizacion == '14') {
                                $txt .= ' la persona jurídica del sector solidario denominada ' . $nom1;
                            } else {
                                $txt .= ' la entidad sin ánimo de lucro denominada ' . $nom1;
                            }
                        } else {
                            if ($organizacion == '14') {
                                $txt .= ' la persona jurídica del sector solidario de naturaleza ' . $nat . ' denominada ' . $nom1;
                            } else {
                                $txt .= ' la entidad sin ánimo de lucro de naturaleza ' . $nat . ' denominada ' . $nom1;
                            }
                        }
                        $pegarNoticia = 'no';
                    } else {
                        if ($organizacion == '08') {
                            $txt .= 'la sucursal de sociedad extranjera de naturaleza comercial denominada ' . $nom1;
                        } else {
                            if ($organizacion == '11') {
                                $txt .= 'la empresa unipersonal de naturaleza comercial denominada ' . $nom1;
                            } else {
                                if ($organizacion == '10') {
                                    $txt .= 'la persona jurídica de naturaleza civil denominada ' . $nom1;
                                } else {
                                    if ($organizacion == '12' || $organizacion == '14') {
                                        $txt .= 'la entidad sin ánimo de lucro denominada ' . $nom1;
                                    } else {
                                        $txt .= 'la persona jurídica de naturaleza comercial denominada ' . $nom1;
                                    }
                                }
                            }
                        }
                        $pegarNoticia = 'no';
                    }
                    if ($sigla != '') {
                        $txt .= ', Sigla ' . $sigla;
                    }
                }
            }
        }
    }

    //
    if ($acto == '0197' && $libro == 'RM15') { // Cesacion de actividad
        if ($organizacion == '01') {
            $txt .= 'la cesación de la actidad comercial de la persona natural de nominada ' . $nombre;
        } else {
            if ($organizacion == '02') {
                $txt .= 'el cierre del establecimiento de comercio denominado ' . $nombre;
            } else {
                if ($categoria == '2') {
                    $txt .= 'el cierre de la sucursal denominada ' . $nombre;
                } else {
                    if ($categoria == '3') {
                        $txt .= 'el cierre de la agencia denominada ' . $nombre;
                    } else {
                        $txt .= 'la cesación de la actidad comercial de la persona jurídica denominada ' . $nombre;
                    }
                }
            }
        }
    }

    //
    if ($acto == '0197' && $libro == 'RM06') { // cierre del establecimiento de comercio
        $txt .= 'el cierre del establecimiento de comercio denominado ' . $nombre;
    }

    // En caso de depuración.
    if ($acto == '0510' && $tipdoc == '38') {
        $txt .= 'la disolución por depuración de acuerdo con lo indicado en la Ley 1727 de 2014.';
        $pegarNoticia = 'no';
    }

    if (($acto == '0530' || $acto == '0540') && $tipdoc == '38') {
        $txt .= 'la cancelación por depuración de acuerdo con lo indicado en la ley 1727 de 2014.';
        $pegarNoticia = 'no';
    }

    //
    if ($acto == '2000' || $acto == '2010') { //
        $txt .= 'la comunicación que se ha configurado una situación de control : ';
    }
    if ($acto == '2020' || $acto == '2030') { //
        $txt .= 'la comunicación que se ha configurado un grupo empresarial : ';
    }

    //
    if ($pegarNoticia == 'si') {
        // $txt .= \funcionesGenerales::parsearOracionNoticia($noticia);
        $txt .= $noticia;
    }

    if (trim((string) $aclaratoria) != '') {
        // $txt .= '<br><br>' . \funcionesGenerales::parsearOracionNoticia($aclaratoria);
        $txt .= '<br><br>' . $aclaratoria;
    }

    return $txt;
}

/**
 * 
 * @param type $mysqli
 * @param type $acto
 * @param string $tipdoc
 * @param type $numdoc
 * @param type $numdocext
 * @param type $fecdoc
 * @param type $idorigen
 * @param type $txtorigen
 * @param type $munori
 * @param type $organizacion
 * @param type $libro
 * @param type $registro
 * @param type $fecins
 * @param type $noticia
 * @param type $nan
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @param type $camant2
 * @param type $libant2
 * @param type $regant2
 * @param type $fecant2
 * @param type $camant3
 * @param type $libant3
 * @param type $regant3
 * @param type $fecant3
 * @param type $camant4
 * @param type $libant4
 * @param type $regant4
 * @param type $fecant4
 * @param type $camant5
 * @param type $libant5
 * @param type $regant5
 * @param type $fecant5
 * @param type $tomo72
 * @param type $folio72
 * @param type $registro72
 * @return type
 */
function descripcionesSitControlFormato2019($mysqli, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $organizacion, $libro, $registro = '', $fecins = '', $noticia = '', $nan = array(), $camant = '', $libant = '', $regant = '', $fecant = '', $camant2 = '', $libant2 = '', $regant2 = '', $fecant2 = '', $camant3 = '', $libant3 = '', $regant3 = '', $fecant3 = '', $camant4 = '', $libant4 = '', $regant4 = '', $fecant4 = '', $camant5 = '', $libant5 = '', $regant5 = '', $fecant5 = '', $tomo72 = '', $folio72 = '', $registro72 = '') {

    //
    if ($numdocext != '') {
        $numdoc = $numdocext;
    }

    //
    $txt = 'Por ';

    //
    if ($tipdoc == '15' && $numdoc == '1727') {
        $tipdoc = '38';
    }

    //
    if ($tipdoc == '38' && $numdoc == '1727') {
        $txt = 'De acuerdo a lo establecido en la ';
        $numdoc = '';
    }

    $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
    if ($txtDoc == '') {
        $txtDoc = 'documento';
    }

    //
    $txt .= $txtDoc . ' ';

    //
    if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
        $txt .= 'No. ' . trim($numdoc) . ' ';
    }

    //
    if ($fecdoc != '' && $tipdoc != '38') {
        $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
    }

    //
    $txtSuscribe = '';

    //
    if ($tipdoc != '38') {
        if ($txtorigen != '') {
            if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
                $txtorigen = '';
            }
            $txtorigen = str_replace("NOTARIAS NOTARIA", "Notaría", $txtorigen);
            $txtorigen = str_replace("ACTAS ", "", $txtorigen);
            $txtorigen = str_replace("JUZGADOS CIVILES DEL CIRCUITO ", "", $txtorigen);
            $txtSuscribe = ucwords(strtolower($txtorigen));
        } else {
            if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
                $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
            }
        }

        //
        $txtParticula = 'de la';
        $noparticula = '';

        if (strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTANTE LEGAL'
        ) {
            $txtParticula = 'de';
            $txtSuscribe = 'el Representante Legal';
            $noparticula = 'si';
        }

        //
        if (substr(strtoupper($txtSuscribe), 0, 19) == 'REPRESENTANTE LEGAL') {
            $txtParticula = 'de';
            $txtSuscribe = 'el ' . $txtSuscribe;
            $noparticula = 'si';
        }

        if (strtoupper($txtSuscribe) == 'REVISOR FISCAL') {
            $txtParticula = 'de';
            $txtSuscribe = 'el Revisor Fiscal';
            $noparticula = 'si';
        }

        if (strtoupper($txtSuscribe) == 'COMERCIANTE') {
            $txtParticula = 'del';
            $txtSuscribe = 'Comerciante';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
            $txtParticula = 'de';
            $txtSuscribe = 'la Junta de Socios';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
            $txtParticula = 'de';
            $txtSuscribe = 'la Junta Directiva';
            $noparticula = 'si';
        }
        if (substr(strtoupper($txtSuscribe), 0, 7) == 'JUZGADO') {
            $txtParticula = 'del';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
            $txtParticula = 'de';
            $txtSuscribe = 'el Propietario';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
            $txtParticula = 'de';
            $txtSuscribe = 'La Administración de Impuestos Nacionales';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
            $txtParticula = 'de';
            $txtSuscribe = 'Accionistas';
            $noparticula = 'si';
        }

        // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
        if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
            $txtParticula = 'de';
            $txtSuscribe = 'Único Accionista';
            $noparticula = 'si';
        }

        // 2018-06-18: JINT
        if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
            $txtParticula = 'del';
            $txtSuscribe = 'Accionista Único';
            $noparticula = 'si';
        }

        // 2018-06-18: JINT
        if (strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN') {
            $txtParticula = 'del';
            $txtSuscribe = 'Comité de Administración';
            $noparticula = 'si';
        }

        // 2018-06-26: JINT
        if (strtoupper($txtSuscribe) == 'EL COMERCIANTE') {
            $txtParticula = '';
            $txtSuscribe = '';
            $noparticula = 'si';
        }


        if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
            if ($organizacion == '11') {
                if ($acto == '0040') {
                    $txtParticula = '';
                    $txtSuscribe = 'del Empresario Constituyente';
                    $noparticula = 'si';
                }
            }
        }

        if ($noparticula != 'si') {
            $origenes = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='origenes' and idcodigo='" . strtoupper($txtSuscribe) . "'");
            if ($origenes && !empty($origenes)) {
                $txtParticula = $origenes["campo1"];
                $txtSuscribe = $origenes["campo2"];
                $noparticula = 'si';
            }
        }

        if ($txtSuscribe != '') {
            $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
        }

        //
        if ($tipdoc == '02' || $tipdoc == '04') {
            if ($munori != '' && $munori != '00000' && $munori != '99999') {
                $txt .= ' de ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $munori)));
            }
        }

        //
        $txt = str_replace(" , ", ", ", $txt);
        $txt = str_replace(array("DE LA LA", "DE LA EL"), array("DE LA", "DE EL"), $txt);
        $txt = str_replace(array("de la la", "de la el"), array("de la", "de el"), $txt);
        $txt = str_replace(array("de la los", "de la Los"), array("de los", "de Los"), $txt);
    }

    if (trim($tomo72) != '') {
        $txt .= ', inscrito bajo el número  ' . $registro72 . ', del tomo ' . $tomo72 . ', folio ' . $folio72 . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ' ';
    } else {
        if ($camant != '') {
            $txt .= ', inscrita inicialmente en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant);
            if ($regant != '') {
                $txt .= ' bajo el No. ' . $regant;
            }
            if ($libant != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant);
            }
        }

        if ($camant2 != '') {
            $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant2))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant2);
            if ($regant2 != '') {
                $txt .= ' bajo el No. ' . $regant2;
            }
            if ($libant2 != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant2);
            }
        }

        if ($camant3 != '') {
            $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant3))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant3);
            if ($regant3 != '') {
                $txt .= ' bajo el No. ' . $regant3;
            }
            if ($libant3 != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant3);
            }
        }

        if ($camant4 != '') {
            $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant4))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant4);
            if ($regant4 != '') {
                $txt .= ' bajo el No. ' . $regant4;
            }
            if ($libant4 != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant4);
            }
        }

        if ($camant5 != '') {
            $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant5))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant5);
            if ($regant5 != '') {
                $txt .= ' bajo el No. ' . $regant5;
            }
            if ($libant5 != '') {
                $txt .= ' del Libro ' . retornarLibroFormato2019($libant5);
            }
        }


        //
        if ($camant != '' || $camant2 != '' || $camant3 != '' || $camant4 != '' || $camant5 != '') {
            $txt .= ' y posteriormente inscrita ';
        } else {
            $txt .= ', inscrito ';
        }
        $txt .= 'en esta Cámara de Comercio el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', con el No. ' . $registro . ' ';
        $txt .= 'del Libro ' . retornarLibroFormato2019($libro) . ', ';
    }


    //
    if ($acto == '2000' || $acto == '2010') { //
        $txt .= 'se inscribió la comunicación que se ha configurado una situación de control : ';
    }
    if ($acto == '2020' || $acto == '2030') { //
        $txt .= 'se inscribió la comunicación que se ha configurado un grupo empresarial : ';
    }

    //
    $txt .= \funcionesGenerales::parsearOracionNoticia($noticia);

    // $txt .= '<br><br>' . \funcionesGenerales::limpiarTextosRedundantes(\funcionesGenerales::parsearOracion($noticia)) . '<br>';
    return $txt;
}

/**
 * 
 * @param type $mysqli
 * @param type $organizacion
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
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @param type $camant2
 * @param type $libant2
 * @param type $regant2
 * @param type $fecant2
 * @param type $camant3
 * @param type $libant3
 * @param type $regant3
 * @param type $fecant3
 * @param type $camant4
 * @param type $libant4
 * @param type $regant4
 * @param type $fecant4
 * @param type $camant5
 * @param type $libant5
 * @param type $regant5
 * @param type $fecant5
 * @param type $aclaratoria
 * @param type $renunciaremocion
 * @param type $tomo72
 * @param type $folio72
 * @param type $registro72
 * @return string
 */
function descripcionesVinculosFormato2019($mysqli, $organizacion, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro = '', $registro = '', $fecins = '', $noticia = '', $camant = '', $libant = '', $regant = '', $fecant = '', $camant2 = '', $libant2 = '', $regant2 = '', $fecant2 = '', $camant3 = '', $libant3 = '', $regant3 = '', $fecant3 = '', $camant4 = '', $libant4 = '', $regant4 = '', $fecant4 = '', $camant5 = '', $libant5 = '', $regant5 = '', $fecant5 = '', $aclaratoria = '', $renunciaremocion = '', $tomo72 = '', $folio72 = '', $registro72 = '') {

    //
    if ($numdocext != '') {
        $numdoc = $numdocext;
    }

    //
    $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
    if ($txtDoc == '') {
        $txtDoc = 'documento';
    }
    $txt = 'Por ';
    $txt .= $txtDoc . ' ';
    if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
        $txt .= 'No. ' . trim($numdoc) . ' ';
    }

    //
    if ($fecdoc != '') {
        $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
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
        $txtSuscribe = ucwords(strtolower($txtorigen));
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
        }
    }

    //
    $txtParticula = 'de la';
    $noparticula = '';

    if (strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTANTE LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTANTE  LEGAL'
    ) {
        $txtParticula = 'de';
        $txtSuscribe = 'el Representante Legal';
        $noparticula = 'si';
    }
//
    if (substr(strtoupper($txtSuscribe), 0, 19) == 'REPRESENTANTE LEGAL') {
        $txtParticula = 'de';
        $txtSuscribe = 'el ' . $txtSuscribe;
        $noparticula = 'si';
    }


    //
    if (strtoupper($txtSuscribe) == 'REVISOR FISCAL') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Revisor Fiscal';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'EL EMPRESARIO' ||
            strtoupper($txtSuscribe) == 'EMPRESARIO'
    ) {
        $txtParticula = 'del';
        $txtSuscribe = 'Empresario';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'COMERCIANTE' ||
            strtoupper($txtSuscribe) == 'EL COMERCIANTE') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Comerciante';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
        $txtParticula = 'de';
        $txtSuscribe = 'la Junta de Socios';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
        $txtParticula = 'de';
        $txtSuscribe = 'la Junta Directiva';
        $noparticula = 'si';
    }
    if (substr($txtSuscribe, 0, 7) == 'JUZGADO') {
        $txtParticula = 'del';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Propietario';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtParticula = 'de';
        $txtSuscribe = 'La Administración de Impuestos Nacionales';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
        $txtParticula = 'de';
        $txtSuscribe = 'Accionistas';
        $noparticula = 'si';
    }

    // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
    if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
        $txtParticula = 'de';
        $txtSuscribe = 'Único Accionista';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
        $txtParticula = 'del';
        $txtSuscribe = 'Accionista Único';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN') {
        $txtParticula = 'del';
        $txtSuscribe = 'Comité de Administración';
        $noparticula = 'si';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtParticula = '';
        $txtSuscribe = '';
        $noparticula = 'si';
    }


    if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
        if ($organizacion == '11') {
            if ($acto == '0040') {
                $txtParticula = '';
                $txtSuscribe = 'del Empresario Constituyente';
                $noparticula = 'si';
            }
        }
    }

    if ($noparticula != 'si') {
        $origenes = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='origenes' and idcodigo='" . strtoupper($txtSuscribe) . "'");
        if ($origenes && !empty($origenes)) {
            $txtParticula = $origenes["campo1"];
            $txtSuscribe = $origenes["campo2"];
            $noparticula = 'si';
        }
    }

    if ($txtSuscribe != '') {
        $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
    }

    //
    if ($tipdoc == '02' || $tipdoc == '04') {
        if ($munori != '' && $munori != '00000' && $munori != '99999') {
            $txt .= ' de ' . retornarNombreMunicipioMysqliApi($mysqli, $munori) . ', ';
        } else {
            $txt .= ', ';
        }
    } else {
        $txt .= ', ';
    }

    //
    $txt = str_replace(" , ", ", ", $txt);

    //
    if (trim($tomo72) != '') {
        $txt .= ', inscrito bajo el número  ' . $registro72 . ', del tomo ' . $tomo72 . ', folio ' . $folio72 . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', ';
    } else {
        $txtLibro = retornarLibroFormato2019($libro);
        $txt .= 'inscrita/o en esta Cámara de Comercio el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ' con el No. ' . $registro . ' ';
        $txt .= 'del libro ' . $txtLibro . ', ';

        //
        if ($camant != '') {
            $txt .= 'inscrita/o originalmente el ' . \funcionesGenerales::mostrarFechaLetras1($fecant) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
            if ($regant != '') {
                $txt .= ' con el No. ' . $regant;
            }
            if ($libant != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant2 != '') {
            $txt .= 'inscrito/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant2) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant2);
            if ($regant2 != '') {
                $txt .= ' con el No. ' . $regant2;
            }
            if ($libant2 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant2);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant3 != '') {
            $txt .= 'inscrita/o el ' . \funcionesGenerales::mostrarFechaLetras1($fecant3) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant3);
            if ($regant3 != '') {
                $txt .= ' con el No. ' . $regant3;
            }
            if ($libant3 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant3);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant4 != '') {
            $txt .= 'inscrita/o el ' . \funcionesGenerales::mostrarFechaLetras1($fecant4) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant4);
            if ($regant4 != '') {
                $txt .= ' con el No. ' . $regant4;
            }
            if ($libant4 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant4);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant5 != '') {
            $txt .= 'inscrita/o el ' . \funcionesGenerales::mostrarFechaLetras1($fecant5) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant5);
            if ($regant5 != '') {
                $txt .= ' con el No. ' . $regant5;
            }
            if ($libant5 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant5);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }
    }

    $txt .= 'se designó a: ';
    return $txt;
}

/**
 * 
 * @param type $mysqli
 * @param type $organizacion
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
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @param type $camant2
 * @param type $libant2
 * @param type $regant2
 * @param type $fecant2
 * @param type $camant3
 * @param type $libant3
 * @param type $regant3
 * @param type $fecant3
 * @param type $camant4
 * @param type $libant4
 * @param type $regant4
 * @param type $fecant4
 * @param type $camant5
 * @param type $libant5
 * @param type $regant5
 * @param type $fecant5
 * @param type $aclaratoria
 * @param type $renunciaremocion
 * @param type $tomo72
 * @param type $folio72
 * @param type $registro72
 * @return string
 */
function descripcionesVinculosIntegrantesFormato2019($mysqli, $organizacion, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro = '', $registro = '', $fecins = '', $noticia = '', $camant = '', $libant = '', $regant = '', $fecant = '', $camant2 = '', $libant2 = '', $regant2 = '', $fecant2 = '', $camant3 = '', $libant3 = '', $regant3 = '', $fecant3 = '', $camant4 = '', $libant4 = '', $regant4 = '', $fecant4 = '', $camant5 = '', $libant5 = '', $regant5 = '', $fecant5 = '', $aclaratoria = '', $renunciaremocion = '', $tomo72 = '', $folio72 = '', $registro72 = '') {

    //
    if ($numdocext != '') {
        $numdoc = $numdocext;
    }

    //
    $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
    if ($txtDoc == '') {
        $txtDoc = 'documento';
    }
    $txt = 'Por ';
    $txt .= $txtDoc . ' ';
    if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
        $txt .= 'No. ' . trim($numdoc) . ' ';
    }

    //
    if ($fecdoc != '') {
        $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
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
        $txtSuscribe = ucwords(strtolower($txtorigen));
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
        }
    }

    //
    $txtParticula = 'de la';
    $noparticula = '';
    if (strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTANTE LEGAL'
    ) {
        $txtParticula = 'de';
        $txtSuscribe = 'el Representante Legal';
        $noparticula = 'si';
    }

    //
    if (substr(strtoupper($txtSuscribe), 0, 19) == 'REPRESENTANTE LEGAL') {
        $txtParticula = 'de';
        $txtSuscribe = 'el ' . $txtSuscribe;
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'REVISOR FISCAL') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Revisor Fiscal';
        $noparticula = 'si';
    }


    if (strtoupper($txtSuscribe) == 'COMERCIANTE') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Comerciante';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
        $txtParticula = 'de';
        $txtSuscribe = 'la Junta de Socios';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
        $txtParticula = 'de';
        $txtSuscribe = 'la Junta Directiva';
        $noparticula = 'si';
    }
    if (substr($txtSuscribe, 0, 7) == 'JUZGADO') {
        $txtParticula = 'del';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Propietario';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtParticula = 'de';
        $txtSuscribe = 'La Administración de Impuestos Nacionales';
        $noparticula = 'si';
    }
    if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
        $txtParticula = 'de';
        $txtSuscribe = 'Accionistas';
        $noparticula = 'si';
    }

    // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
    if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
        $txtParticula = 'de';
        $txtSuscribe = 'Único Accionista';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
        $txtParticula = 'del';
        $txtSuscribe = 'Accionista Único';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN') {
        $txtParticula = 'del';
        $txtSuscribe = 'Comité de Administración';
        $noparticula = 'si';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtParticula = '';
        $txtSuscribe = '';
        $noparticula = 'si';
    }


    if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
        if ($organizacion == '11') {
            if ($acto == '0040') {
                $txtParticula = '';
                $txtSuscribe = 'del Empresario Constituyente';
                $noparticula = 'si';
            }
        }
    }

    if ($noparticula != 'si') {
        $origenes = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='origenes' and idcodigo='" . strtoupper($txtSuscribe) . "'");
        if ($origenes && !empty($origenes)) {
            $txtParticula = $origenes["campo1"];
            $txtSuscribe = $origenes["campo2"];
            $noparticula = 'si';
        }
    }

    if ($txtSuscribe != '') {
        $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
    }

    //
    if ($tipdoc == '02' || $tipdoc == '04') {
        if ($munori != '' && $munori != '00000' && $munori != '99999') {
            $txt .= ' de ' . retornarNombreMunicipioMysqliApi($mysqli, $munori) . ', ';
        } else {
            $txt .= ', ';
        }
    } else {
        $txt .= ', ';
    }

    //
    $txt = str_replace(" , ", ", ", $txt);

    //
    if (trim($tomo72) != '') {
        $txt .= ', inscrito bajo el número  ' . $registro72 . ', del tomo ' . $tomo72 . ', folio ' . $folio72 . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', ';
    } else {
        $txtLibro = retornarLibroFormato2019($libro);
        $txt .= 'inscrita/o en esta Cámara de Comercio el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ' con el No. ' . $registro . ' ';
        $txt .= 'del libro ' . $txtLibro . ', ';

        //
        if ($camant != '') {
            $txt .= 'inscrita/o originalmente el ' . \funcionesGenerales::mostrarFechaLetras1($fecant) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
            if ($regant != '') {
                $txt .= ' con el No. ' . $regant;
            }
            if ($libant != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant2 != '') {
            $txt .= 'inscrito/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant2) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant2);
            if ($regant2 != '') {
                $txt .= ' con el No. ' . $regant2;
            }
            if ($libant2 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant2);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant3 != '') {
            $txt .= 'inscrita/o el ' . \funcionesGenerales::mostrarFechaLetras1($fecant3) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant3);
            if ($regant3 != '') {
                $txt .= ' con el No. ' . $regant3;
            }
            if ($libant3 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant3);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant4 != '') {
            $txt .= 'inscrita/o el ' . \funcionesGenerales::mostrarFechaLetras1($fecant4) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant4);
            if ($regant4 != '') {
                $txt .= ' con el No. ' . $regant4;
            }
            if ($libant4 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant4);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant5 != '') {
            $txt .= 'inscrita/o el ' . \funcionesGenerales::mostrarFechaLetras1($fecant5) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant5);
            if ($regant5 != '') {
                $txt .= ' con el No. ' . $regant5;
            }
            if ($libant5 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant5);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }
    }

    $txt .= 'se reportaron los siguientes integrantes:';
    return $txt;
}

/**
 * 
 * @param type $mysqli
 * @param type $organizacion
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
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @param type $camant2
 * @param type $libant2
 * @param type $regant2
 * @param type $fecant2
 * @param type $camant3
 * @param type $libant3
 * @param type $regant3
 * @param type $fecant3
 * @param type $camant4
 * @param type $libant4
 * @param type $regant4
 * @param type $fecant4
 * @param type $camant5
 * @param type $libant5
 * @param type $regant5
 * @param type $fecant5
 * @param type $acla
 * @param type $renrem
 * @param type $tivin
 * @param type $ivin
 * @param type $nvin
 * @param type $a1vin
 * @param type $a2vin
 * @param type $n1vin
 * @param type $n2vin
 * @param type $not
 * @param type $tomo72
 * @param type $folio72
 * @param type $registro72
 * @return string
 */
function descripcionesApoderadosFormato2019($mysqli, $organizacion, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro = '', $registro = '', $fecins = '', $noticia = '', $camant = '', $libant = '', $regant = '', $fecant = '', $camant2 = '', $libant2 = '', $regant2 = '', $fecant2 = '', $camant3 = '', $libant3 = '', $regant3 = '', $fecant3 = '', $camant4 = '', $libant4 = '', $regant4 = '', $fecant4 = '', $camant5 = '', $libant5 = '', $regant5 = '', $fecant5 = '', $acla = '', $renrem = '', $tivin = array(), $ivin = array(), $nvin = array(), $a1vin = array(), $a2vin = array(), $n1vin = array(), $n2vin = array(), $not = '', $tomo72 = '', $folio72 = '', $registro72 = '', $acto = '') {
    $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
    if ($txtDoc == '') {
        $txtDoc = 'documento';
    }
    $txt = 'Por ';
    $txt .= $txtDoc . ' ';
    if (ltrim(trim((string) $numdocext), "0") != '') {
        $txt .= 'No. ' . trim((string) $numdocext) . ' ';
    } else {
        if (trim((string) $numdoc) != '' && trim((string) $numdoc) != '0' && strtoupper(trim((string) $numdoc)) != 'NA' && strtoupper(trim((string) $numdoc)) != 'N/A' && strtoupper(trim((string) $numdoc)) != 'SN' && strtoupper(trim((string) $numdoc)) != 'S/N') {
            $txt .= 'No. ' . trim((string) $numdoc) . ' ';
        }
    }
    if ($fecdoc != '') {
        $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
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
        $txtSuscribe = ucwords(strtolower($txtorigen));
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
        }
    }

    //
    $txtParticula = 'de la';
    $noparticula = '';

    if (strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL'
    ) {
        $txtParticula = 'de';
        $txtSuscribe = 'el Representante Legal';
        $noparticula = 'si';
    }

    //
    if (substr(strtoupper($txtSuscribe), 0, 19) == 'REPRESENTANTE LEGAL') {
        $txtParticula = 'de';
        $txtSuscribe = 'el ' . $txtSuscribe;
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'COMERCIANTE') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Comerciante';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
        $txtParticula = 'de';
        $txtSuscribe = 'la Junta de Socios';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
        $txtParticula = 'de';
        $txtSuscribe = 'la Junta Directiva';
        $noparticula = 'si';
    }

    if (substr($txtSuscribe, 0, 7) == 'JUZGADO') {
        $txtParticula = 'del';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Propietario';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtParticula = 'de';
        $txtSuscribe = 'La Administración de Impuestos Nacionales';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
        $txtParticula = 'de';
        $txtSuscribe = 'Accionistas';
        $noparticula = 'si';
    }

    // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
    if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
        $txtParticula = 'de';
        $txtSuscribe = 'Único Accionista';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN') {
        $txtParticula = 'del';
        $txtSuscribe = 'Comité de Administración';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
        $txtParticula = 'del';
        $txtSuscribe = 'Accionista Único';
        $noparticula = 'si';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtParticula = '';
        $txtSuscribe = '';
        $noparticula = 'si';
    }


    if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
        if ($organizacion == '11') {
            if ($acto == '0040') {
                $txtParticula = '';
                $txtSuscribe = 'del Empresario Constituyente';
                $noparticula = 'si';
            }
        }
    }

    if ($noparticula != 'si') {
        $origenes = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='origenes' and idcodigo='" . strtoupper($txtSuscribe) . "'");
        if ($origenes && !empty($origenes)) {
            $txtParticula = $origenes["campo1"];
            $txtSuscribe = $origenes["campo2"];
            $noparticula = 'si';
        }
    }

    if ($txtSuscribe != '') {
        $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
    }

    //
    if ($tipdoc == '02' || $tipdoc == '04') {
        if ($munori != '' && $munori != '00000' && $munori != '99999') {
            $txt .= ' de ' . retornarNombreMunicipioMysqliApi($mysqli, $munori) . ', ';
        } else {
            $txt .= ', ';
        }
    } else {
        $txt .= ', ';
    }

    //
    $txt = str_replace(" , ", ", ", $txt);

    //
    if (trim($tomo72) != '') {
        $txt .= ', inscrito bajo el número  ' . $registro72 . ', del tomo ' . $tomo72 . ', folio ' . $folio72 . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', ';
    } else {
        $txtLibro = retornarLibroFormato2019($libro);
        $txt .= 'registrado/a en esta Cámara de Comercio el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ' con el No. ' . $registro . ' ';
        $txt .= 'del libro ' . $txtLibro . ', ';

        //
        if ($camant != '') {
            $txt .= 'registrado/a originalmente el ' . \funcionesGenerales::mostrarFechaLetras1($fecant) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
            if ($regant != '') {
                $txt .= ' con el No. ' . $regant;
            }
            if ($libant != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant2 != '') {
            $txt .= 'registrado/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant2) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant2);
            if ($regant2 != '') {
                $txt .= ' con el No. ' . $regant2;
            }
            if ($libant2 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant2);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant3 != '') {
            $txt .= 'registrado/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant3) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant3);
            if ($regant3 != '') {
                $txt .= ' con el No. ' . $regant3;
            }
            if ($libant3 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant3);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant4 != '') {
            $txt .= 'registrado/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant4) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant4);
            if ($regant4 != '') {
                $txt .= ' con el No. ' . $regant4;
            }
            if ($libant4 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant4);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant5 != '') {
            $txt .= 'registrado/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant5) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant5);
            if ($regant5 != '') {
                $txt .= ' con el No. ' . $regant5;
            }
            if ($libant5 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant5);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }
    }

    $txtapo = '';
    foreach ($tivin as $key => $valor) {
        $nom = trim($n1vin[$key]);
        if (trim($n2vin[$key]) != '') {
            $nom .= ' ' . trim($n2vin[$key]);
        }
        if (trim($a1vin[$key]) != '') {
            $nom .= ' ' . trim($a1vin[$key]);
        }
        if (trim($a2vin[$key]) != '') {
            $nom .= ' ' . trim($a2vin[$key]);
        }
        if (trim($nom) == '') {
            $nom = $nvin[$key];
        }
        $txtti = retornarTxtTipoIdeFormato2019($tivin[$key]);
        if ($txtapo != '') {
            $txtapo .= ' y ';
        }
        // $txtapo .= '<strong>' . $nom . '</strong> identificado con ' . $txtti . '  No.  ' . '<strong>' . trim($ivin[$key]) . '</strong>';
        $txtapo .= '<strong>' . $nom . ' identificado con ' . $txtti . '  No. ' . trim($ivin[$key]) . '</strong>';
    }

    //
    // $txt .= ' la persona jurídica confirió poder general, amplio y suficiente a <strong>' . $nom . '</strong> identificado/a con ' . $txtti . ' No. ' . $ivin . ', para que ' . str_replace(chr(13) . chr(10), "<br>", $not);
    if ($acto == '1410') {
        $txt .= ' la persona jurídica confirió poder general, amplio y suficiente a ' . $txtapo . ', para que ' . str_replace(chr(13) . chr(10), "<br>", $not);
    } else {
        if ($acto == '1411') {
            $txt .= ' la persona jurídica confirió poder especial, amplio y suficiente a ' . $txtapo . ', para que ' . str_replace(chr(13) . chr(10), "<br>", $not);
        } else {
            $txt .= ' la persona jurídica confirió poder general, amplio y suficiente a ' . $txtapo . ', para que ' . str_replace(chr(13) . chr(10), "<br>", $not);
        }
    }
    return $txt;
}

/**
 * 
 * @param type $mysqli
 * @param type $organizacion
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
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @param type $camant2
 * @param type $libant2
 * @param type $regant2
 * @param type $fecant2
 * @param type $camant3
 * @param type $libant3
 * @param type $regant3
 * @param type $fecant3
 * @param type $camant4
 * @param type $libant4
 * @param type $regant4
 * @param type $fecant4
 * @param type $camant5
 * @param type $libant5
 * @param type $regant5
 * @param type $fecant5
 * @param type $tivin
 * @param type $ivin
 * @param type $nvin
 * @param type $a1vin
 * @param type $a2vin
 * @param type $n1vin
 * @param type $n2vin
 * @param type $tomo72
 * @param type $folio72
 * @param type $registro72
 * @return string
 */
function descripcionesApoderadosCodCertificaFormato2019($mysqli, $organizacion, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro = '', $registro = '', $fecins = '', $camant = '', $libant = '', $regant = '', $fecant = '', $camant2 = '', $libant2 = '', $regant2 = '', $fecant2 = '', $camant3 = '', $libant3 = '', $regant3 = '', $fecant3 = '', $camant4 = '', $libant4 = '', $regant4 = '', $fecant4 = '', $camant5 = '', $libant5 = '', $regant5 = '', $fecant5 = '', $tivin = array(), $ivin = array(), $nvin = array(), $a1vin = array(), $a2vin = array(), $n1vin = array(), $n2vin = array(), $tomo72 = '', $folio72 = '', $registro72 = '', $acto = '') {
    $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
    if ($txtDoc == '') {
        $txtDoc = 'documento';
    }
    $txt = '';
    $txt .= $txtDoc . ' ';
    if (ltrim(trim($numdocext), "0") != '') {
        $txt .= 'No. ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
            $txt .= 'No. ' . trim($numdoc) . ' ';
        }
    }
    if ($fecdoc != '') {
        $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
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
        $txtSuscribe = ucwords(strtolower($txtorigen));
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
        }
    }

    //
    $txtParticula = 'de la';
    $noparticula = '';
    if (strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
            strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL'
    ) {
        $txtParticula = 'de';
        $txtSuscribe = 'el Representante Legal';
        $noparticula = 'si';
    }

    //
    if (substr(strtoupper($txtSuscribe), 0, 19) == 'REPRESENTANTE LEGAL') {
        $txtParticula = 'de';
        $txtSuscribe = 'el ' . $txtSuscribe;
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'COMERCIANTE') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Comerciante';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
        $txtParticula = 'de';
        $txtSuscribe = 'la Junta de Socios';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
        $txtParticula = 'de';
        $txtSuscribe = 'la Junta Directiva';
        $noparticula = 'si';
    }

    if (substr($txtSuscribe, 0, 7) == 'JUZGADO') {
        $txtParticula = 'del';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
        $txtParticula = 'de';
        $txtSuscribe = 'el Propietario';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtParticula = 'de';
        $txtSuscribe = 'La Administración de Impuestos Nacionales';
        $noparticula = 'si';
    }

    if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
        $txtParticula = 'de';
        $txtSuscribe = 'Accionistas';
        $noparticula = 'si';
    }

    // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
    if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
        $txtParticula = 'de';
        $txtSuscribe = 'Único Accionista';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN') {
        $txtParticula = 'del';
        $txtSuscribe = 'Comité de Administración';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
        $txtParticula = 'del';
        $txtSuscribe = 'Accionista Único';
        $noparticula = 'si';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'EL COMERCIANTE') {
        $txtParticula = '';
        $txtSuscribe = '';
        $noparticula = 'si';
    }


    if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
        if ($organizacion == '11') {
            if ($acto == '0040') {
                $txtParticula = '';
                $txtSuscribe = 'del Empresario Constituyente';
                $noparticula = 'si';
            }
        }
    }

    if ($noparticula != 'si') {
        $origenes = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='origenes' and idcodigo='" . strtoupper($txtSuscribe) . "'");
        if ($origenes && !empty($origenes)) {
            $txtParticula = $origenes["campo1"];
            $txtSuscribe = $origenes["campo2"];
            $noparticula = 'si';
        }
    }

    if ($txtSuscribe != '') {
        $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
    }

    //
    if ($tipdoc == '02' || $tipdoc == '04') {
        if ($munori != '' && $munori != '00000' && $munori != '99999') {
            $txt .= ' de ' . retornarNombreMunicipioMysqliApi($mysqli, $munori) . ', ';
        } else {
            $txt .= ', ';
        }
    } else {
        $txt .= ', ';
    }

    //
    $txt = str_replace(" , ", ", ", $txt);

    //
    if (trim($tomo72) != '') {
        $txt .= ', inscrito bajo el número  ' . $registro72 . ', del tomo ' . $tomo72 . ', folio ' . $folio72 . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', ';
    } else {
        $txtLibro = retornarLibroFormato2019($libro);
        $txt .= 'registrado/a en esta Cámara de Comercio el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ' con el No. ' . $registro . ' ';
        $txt .= 'del libro ' . $txtLibro . ', ';

        //
        if ($camant != '') {
            $txt .= 'registrado/a originalmente el ' . \funcionesGenerales::mostrarFechaLetras1($fecant) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant);
            if ($regant != '') {
                $txt .= ' con el No. ' . $regant;
            }
            if ($libant != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant2 != '') {
            $txt .= 'registrado/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant2) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant2);
            if ($regant2 != '') {
                $txt .= ' con el No. ' . $regant2;
            }
            if ($libant2 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant2);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant3 != '') {
            $txt .= 'registrado/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant3) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant3);
            if ($regant3 != '') {
                $txt .= ' con el No. ' . $regant3;
            }
            if ($libant3 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant3);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant4 != '') {
            $txt .= 'registrado/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant4) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant4);
            if ($regant4 != '') {
                $txt .= ' con el No. ' . $regant4;
            }
            if ($libant4 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant4);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }

        //
        if ($camant5 != '') {
            $txt .= 'registrado/a el ' . \funcionesGenerales::mostrarFechaLetras1($fecant5) . ' en la ' . retornarNombreCamaraMysqliApi($mysqli, $camant5);
            if ($regant5 != '') {
                $txt .= ' con el No. ' . $regant5;
            }
            if ($libant5 != '') {
                $txtLibroAnt = retornarLibroFormato2019($libant5);
                $txt .= ' del libro ' . $txtLibroAnt;
            }
            $txt .= ', ';
        }
    }

    $txtapo = '';
    foreach ($tivin as $key => $valor) {
        $nom = trim($n1vin[$key]);
        if (trim($n2vin[$key]) != '') {
            $nom .= ' ' . trim($n2vin[$key]);
        }
        if (trim($a1vin[$key]) != '') {
            $nom .= ' ' . trim($a1vin[$key]);
        }
        if (trim($a2vin[$key]) != '') {
            $nom .= ' ' . trim($a2vin[$key]);
        }
        if (trim($nom) == '') {
            $nom = $nvin[$key];
        }
        $txtti = retornarTxtTipoIdeFormato2019($tivin[$key]);
        if ($txtapo != '') {
            $txtapo .= ' y ';
        }
        // $txtapo .= '<strong>' . $nom . '</strong> identificado con ' . $txtti . ' No. ' . '<strong>' . $ivin[$key] . '</strong>';
        $txtapo .= '<strong>' . $nom . ' identificado con ' . $txtti . ' No. ' . $ivin[$key] . '</strong>';
    }

    //
    $txt .= ' la persona jurídica confirió a ' . $txtapo;
    return $txt;
}

/**
 * 
 * @param type $mysqli
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
 * @param type $camant
 * @param type $libant
 * @param type $regant
 * @param type $fecant
 * @param type $camant2
 * @param type $libant2
 * @param type $regant2
 * @param type $fecant2
 * @param type $camant3
 * @param type $libant3
 * @param type $regant3
 * @param type $fecant3
 * @param type $camant4
 * @param type $libant4
 * @param type $regant4
 * @param type $fecant4
 * @param type $camant5
 * @param type $libant5
 * @param type $regant5
 * @param type $fecant5
 * @param type $organizacion
 * @param type $categoria
 * @param type $tomo72
 * @param type $folio72
 * @param type $registro72
 * @return string
 */
function descripcionesCambioNombreFomato2019($mysqli, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro = '', $registro = '', $fecins = '', $noticia = '', $camant = '', $libant = '', $regant = '', $fecant = '', $camant2 = '', $libant2 = '', $regant2 = '', $fecant2 = '', $camant3 = '', $libant3 = '', $regant3 = '', $fecant3 = '', $camant4 = '', $libant4 = '', $regant4 = '', $fecant4 = '', $camant5 = '', $libant5 = '', $regant5 = '', $fecant5 = '', $organizacion = '', $categoria = '', $tomo72 = '', $folio72 = '', $registro72 = '') {
    $txtDoc = retornarNombreTablaBasicaMysqliApi($mysqli, 'mreg_tipos_documentales_registro', $tipdoc);
    if ($txtDoc == '') {
        $txtDoc = 'Documento';
    }
    $txt = 'Por ';
    $txt .= $txtDoc . ' ';
    if (ltrim(trim($numdocext), "0") != '') {
        $txt .= 'número ' . trim($numdocext) . ' ';
    } else {
        if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
            $txt .= 'número ' . trim($numdoc) . ' ';
        }
    }
    if ($fecdoc != '') {
        $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
    }

    //
    $txtSuscribe = '';
    $noparticula = '';

    //
    if ($txtorigen != '') {
        if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
            $txtorigen = '';
        }
        $txtorigen = str_replace("NOTARIAS NOTARIA", "NOTARIA", $txtorigen);
        $txtSuscribe = $txtorigen;
        $noparticula = 'si';
    } else {
        if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
            $txtSuscribe = retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen);
            $noparticula = 'si';
        }
    }

    //
    if ($txtSuscribe == 'REPRESENTACION LEGAL') {
        $txtSuscribe = 'El Representante Legal';
        $noparticula = 'si';
    }
    if ($txtSuscribe == 'COMERCIANTE') {
        $txtSuscribe = 'El Comerciante';
        $noparticula = 'si';
    }
    if ($txtSuscribe == 'JUNTA DE SOCIOS') {
        $txtSuscribe = 'La Junta de Socios';
        $noparticula = 'si';
    }
    if ($txtSuscribe == 'JUNTA DIRECTIVA') {
        $txtSuscribe = 'La Junta Directiva';
        $noparticula = 'si';
    }
    if ($txtSuscribe == 'PROPIETARIO') {
        $txtSuscribe = 'El Propietario';
        $noparticula = 'si';
    }
    if ($txtSuscribe == 'ADMON. DE IMPUESTOS NACIONALES') {
        $txtSuscribe = 'La Administración de Impuestos Nacionales';
        $noparticula = 'si';
    }

    // 2018-06-18: JINT
    if (strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
            strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN') {
        $txtSuscribe = 'El Comité de Administración';
        $noparticula = 'si';
    }

    // 2018-06-26: JINT
    if ($txtSuscribe == 'El Comerciante') {
        $txtSuscribe = '';
        $noparticula = 'si';
    }

    if ($noparticula != 'si') {
        $origenes = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='origenes' and idcodigo='" . strtoupper($txtSuscribe) . "'");
        if ($origenes && !empty($origenes)) {
            $txtSuscribe = $origenes["campo1"] . ' ' . $origenes["campo2"];
            $noparticula = 'si';
        }
    }

    //
    if ($txtSuscribe != '') {
        if ($tipdoc == '02') {
            $txt .= 'otorgada por ' . $txtSuscribe . ' ';
        } else {
            $txt .= 'suscrito por ' . $txtSuscribe . ' ';
        }
    }

    //
    if ($tipdoc == '02') {
        if ($munori != '' && $munori != '00000' && $munori != '99999') {
            $txt .= ' de ' . retornarNombreMunicipioMysqliApi($mysqli, $munori) . ', ';
        }
    }

    //
    $txt = str_replace(" , ", ", ", $txt);

    //
    if (trim($tomo72) != '') {
        $txt .= ', inscrito bajo el número  ' . $registro72 . ', del tomo ' . $tomo72 . ', folio ' . $folio72 . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', ';
    } else {
        $txtLibro = retornarLibroFormato2019($libro);
        $txt .= 'registrado en esta Cámara de Comercio bajo el número ' . $registro . ' ';
        $txt .= 'del libro ' . $txtLibro . ' el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', ';
    }
    if ($categoria == '2') {
        $txt .= 'la sucursal cambió su nombre de ';
    } else {
        if ($categoria == '3') {
            $txt .= 'la agencia ambió su nombre de  ';
        } else {
            $txt .= 'la persona jurídica cambió su nombre de ';
        }
    }

    return $txt;
}

function descripcionesDocumentoFormato2019($mysqli, $organizacion, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori) {

    //
    if ($numdocext != '') {
        $numdoc = $numdocext;
    }

    //
    $txt = '';

    //
    if ($tipdoc == '15' && $numdoc == '1727') {
        $tipdoc = '38';
    }

    //
    if ($tipdoc == '38' && $numdoc == '1727') {
        $numdoc = '';
    }

    $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
    if ($txtDoc == '') {
        $txtDoc = 'documento';
    }

    //
    $txt .= $txtDoc . ' ';

    //
    if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
        $txt .= 'No. ' . trim($numdoc) . ' ';
    }

    //
    if ($fecdoc != '' && $tipdoc != '38') {
        $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
    }

    // 
    $txtSuscribe = '';

    //
    if ($tipdoc != '38') {
        if ($txtorigen != '') {
            if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
                $txtorigen = '';
            }
            $txtorigen = str_replace("NOTARIAS NOTARIA", "Notaría", $txtorigen);
            $txtorigen = str_replace("ACTAS ", "", $txtorigen);
            $txtorigen = str_replace("JUZGADOS CIVILES DEL CIRCUITO ", "", $txtorigen);
            $txtSuscribe = ucwords(strtolower($txtorigen));
        } else {
            if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
                $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
            }
        }

        //
        $txtParticula = 'de la';
        $noparticula = '';

        //
        if (strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL'
        ) {
            $txtParticula = 'de';
            $txtSuscribe = 'el Representante Legal';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'COMERCIANTE') {
            $txtParticula = 'de';
            $txtSuscribe = 'el Comerciante';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
            $txtParticula = 'de';
            $txtSuscribe = 'la Junta de Socios';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
            $txtParticula = 'de';
            $txtSuscribe = 'la Junta Directiva';
            $noparticula = 'si';
        }
        if (substr($txtSuscribe, 0, 7) == 'JUZGADO') {
            $txtParticula = 'del';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
            $txtParticula = 'de';
            $txtSuscribe = 'el Propietario';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
            $txtParticula = 'de';
            $txtSuscribe = 'La Administración de Impuestos Nacionales';
            $noparticula = 'si';
        }
        if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
            $txtParticula = 'de';
            $txtSuscribe = 'Accionistas';
            $noparticula = 'si';
        }

        // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
        if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
            $txtParticula = 'de';
            $txtSuscribe = 'Único Accionista';
            $noparticula = 'si';
        }

        // 2018-06-18: JINT
        if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
            $txtParticula = 'del';
            $txtSuscribe = 'Accionista Único';
            $noparticula = 'si';
        }

        // 2018-06-18: JINT
        if (strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN') {
            $txtParticula = 'del';
            $txtSuscribe = 'Comité de Administración';
            $noparticula = 'si';
        }

        // 2018-06-18: JINT
        if (strtoupper($txtSuscribe) == 'REVISOR FISCAL') {
            $txtParticula = 'del';
            $txtSuscribe = 'Revisor Fiscal';
            $noparticula = 'si';
        }

        // 2018-06-26: JINT
        if (strtoupper($txtSuscribe) == 'EL COMERCIANTE') {
            $txtParticula = '';
            $txtSuscribe = '';
            $noparticula = 'si';
        }


        if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
            if ($organizacion == '11') {
                if ($acto == '0040') {
                    $txtParticula = '';
                    $txtSuscribe = 'del Empresario Constituyente';
                    $noparticula = 'si';
                }
            }
        }

        if ($noparticula != 'si') {
            $origenes = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='origenes' and idcodigo='" . strtoupper($txtSuscribe) . "'");
            if ($origenes && !empty($origenes)) {
                $txtParticula = $origenes["campo1"];
                $txtSuscribe = $origenes["campo2"];
                $noparticula = 'si';
            }
        }

        if ($txtSuscribe != '') {
            $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
        }

        //
        if ($tipdoc == '02' || $tipdoc == '04') {
            if ($munori != '' && $munori != '00000' && $munori != '99999') {
                $txt .= ' de ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $munori)));
            }
        }

        //
        $txt = str_replace(" , ", ", ", $txt);
        $txt = str_replace(array("DE LA LA", "DE LA EL"), array("DE LA", "DE EL"), $txt);
        $txt = str_replace(array("de la la", "de la el"), array("de la", "de el"), $txt);
        $txt = str_replace(array("de la los", "de la Los"), array("de los", "de Los"), $txt);
    }

    //
    return $txt;
}

// *************************************************************************** //
// Información sacada del formulario
// *************************************************************************** //
function armarCertificaInformacionFormulariosFormato2019($pdf) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = '<strong>CERTIFICA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
    $txt = 'La información anterior ha sido tomada directamente del formulario de matrícula, inscripción y renovación diligenciado por el comerciante.';
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->Ln();
}

//
// *************************************************************************** //
// Firmeza de inscripciones
// *************************************************************************** //
function armarCertificaFirmezaFormato2019($pdf) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = '<strong>CERTIFICA</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $pdf->Ln();
    $txt = 'De conformidad con lo establecido en el Código de Procedimiento Administrativo y de lo Contencioso Administrativo y de la ';
    $txt .= 'Ley 962 de 2005, los actos administrativos de registro aquí certificados quedan en firme diez (10) días ';
    $txt .= 'hábiles despues de la fecha de inscripción, siempre que no sean objeto de recursos. El día sábado no ';
    $txt .= 'se debe contar como día hábil.';
    $pdf->SetFont('courier', '', 9);
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->Ln();
}

function armarTextoEleccionesFormato2019($dbx, $pdf) {

    //
    /*
      if (
      date("Y") != '2022' &&
      date("Y") != '2026' &&
      date("Y") != '2030' &&
      date("Y") != '2034'
      ) {
      return true;
      }
     */

    //
    $textoElecciones = 'no';
    if (defined('MOSTRAR_TEXTO_ELECCIONES') && MOSTRAR_TEXTO_ELECCIONES == 'SI') {
        $texto = \funcionesGenerales::cambiarSustitutoHtml(retornarPantallaPredisenadaMysqliApi($dbx, 'texto_elecciones'));
        if (trim($texto) !== '') {
            $textoElecciones = 'si';
        }
    }

    //
    if ($textoElecciones == 'no') {
        return true;
    }

    //
    $pdf->SetFont('courier', '', 9);

    //
    $telx = TELEFONO_AFILIADOS;
    if (TELEFONO_AFILIADOS == '') {
        $telx = TELEFONO_ATENCION_USUARIOS;
    }
    $wwwx = WWW_ENTIDAD;

    //
    $texto = str_replace("[RAZONSOCIAL]", RAZONSOCIAL, $texto);
    $texto = str_replace("[TELEFONO_AFILIADOS]", $telx, $texto);
    $texto = str_replace("[WWW_ENTIDAD]", $wwwx, $texto);

    // $pdf->SetFont('helvetica', '', 7);
    // $pdf->writeHTML('<span style="text-align:justify;">' . $texto . '</span>', true, false, true, false);
    $pdf->writeHTML($texto, true, false, true, false);
    $pdf->writeHTML('<span style="text-align:center;">*************************************************************************************</span>', true, false, true, false);
    $pdf->Ln();
}

function armarTextoCodigosBarrasFormato2019($mysqli, $pdf, $data, $nameLog) {
    if (!empty($data["lcodigosbarras"])) {
        $pdf->SetFont('courier', '', $pdf->tamanoLetra);
        $txt = '<strong>A LA FECHA DE EXPEDICIÓN DE ESTE CERTIFICADO, EXISTEN PETICIONES EN TRÁMITE. LAS CUALES PUEDEN AFECTAR EL CONTENIDO DE LA INFORMACIÓN ';
        $txt .= 'QUE CONSTA EN EL MISMO.</strong>';
        $pdf->SetTextColor(139, 0, 0);
        $pdf->writeHTML($txt, true, false, true, false, 'J');
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
        \logApi::general2($nameLog, '' . '-' . $data["matricula"], 'Imprimio que tiene codigos de barras pendientes');
    }
}

function armarTextoFirmaFormato2019($pdf, $aleatorio, $tipoFirma) {
    //
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    if ($aleatorio != '') {
        if ($tipoFirma == 'FIRMA_SECRETARIO' || $tipoFirma == 'CERTITOKEN') {
            $txt = 'IMPORTANTE: La firma digital del secretario de la ' . str_replace("CAMARA", "CÁMARA", RAZONSOCIAL) . ' contenida en este certificado electrónico '
                    . 'se encuentra emitida por una entidad de certificación acreditada por el Organismo Nacional de Acreditación de Colombia (ONAC), '
                    . 'de conformidad con las exigencias establecidas en la Ley 527 de 1999 para validez jurídica y probatoria '
                    . 'de los documentos electrónicos.' . "\n";
            $pdf->SetFont('helvetica', '', 7);
            $pdf->writeHTML('&nbsp;<br><span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
        }

        if ($tipoFirma == 'FIRMA_PERJUR') {
            $txt = 'IMPORTANTE: La firma digital de la ' . str_replace("CAMARA", "CÁMARA", RAZONSOCIAL) . ' contenida en este certificado electrónico '
                    . 'se encuentra emitida por una entidad de certificación acreditada por el Organismo Nacional de Acreditación de Colombia (ONAC), '
                    . 'de conformidad con las exigencias establecidas en la Ley 527 de 1999 para validez jurídica y probatoria '
                    . 'de los documentos electrónicos.' . "\n";
            $pdf->SetFont('helvetica', '', 7);
            $pdf->writeHTML('&nbsp;<br><span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
            $pdf->Ln();
        }
    }
}

function armarTextoFirmaQueEsFormato2019($pdf, $aleatorio = '') {
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

function armarTextoFirmaVerificacionFormato2019($pdf, $aleatorio) {
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

function armarTextoFirmaMecanicaFormato2019($pdf) {
    $pdf->SetFont('courier', '', $pdf->tamanoLetra);

    $txt = 'La firma mecánica que se muestra a continuación es la representación gráfica de la firma del secretario jurídico (o de quien haga sus veces) '
            . 'de la Cámara de Comercio quien avala este certificado. La firma mecánica no reemplaza la firma digital en los documentos electrónicos.' . "\n\n";
    $pdf->SetFont('helvetica', '', 7);
    $pdf->writeHTML('<span style="text-align:justify;">' . $txt . '</span>', true, false, true, false);
    $pdf->Ln();
}

function armarImagenFirmaFormato2019($pdf, $nameLog) {
    // if ($pdf->tituloTipo != 'Consulta' && $pdf->tituloTipo != 'Api' && $pdf->tituloTipo != 'Revision') {
    if ($pdf->tituloTipo != 'Consulta' && $pdf->tituloTipo != 'Revision') {
        // if ($pdf->tituloTipo != 'Consulta' && $pdf->tituloTipo != 'Revision') {
        $rutaFirmaMecanica = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/formatos/firmacertificados.png';
        if (file_exists($rutaFirmaMecanica)) {
            $x = $pdf->GetX() + 70;
            $y = $pdf->GetY();
            $pdf->SetY($y);
            $pdf->Image($rutaFirmaMecanica, $x, $y, 50, 30, 'png', '', '', true);
        } else {
            \logApi::general2($nameLog, '', 'Firma de certificados no localizada : ' . $rutaFirmaMecanica);
        }
    }
}

function armarFinCertificadoFormato2019($pdf) {
    $y = $pdf->GetY() + 30;
    $pdf->SetY($y);
    $pdf->Line(17, $y, 190, $y);
    $txt = '<strong>*** FINAL DEL CERTIFICADO ***</strong>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $y = $y + 4;
    $pdf->Line(17, $y, 190, $y);
}

function retornarTxtTipoIdeFormato2019($tipoIde) {
    switch ($tipoIde) {
        case "1":
            $txtTipoIde = 'CC.';
            break;
        case "2":
            $txtTipoIde = 'NIT.';
            break;
        case "3":
            $txtTipoIde = 'CE.';
            break;
        case "4":
            $txtTipoIde = 'TI.';
            break;
        case "5":
            $txtTipoIde = 'PA.';
            break;
        case "6":
            $txtTipoIde = 'PJ.';
            break;
        case "E":
            $txtTipoIde = 'DE.';
            break;
        case "N":
            $txtTipoIde = 'NUIP.';
            break;
        case "R":
            $txtTipoIde = 'RC.';
            break;
        case "V":
            $txtTipoIde = 'PEP.';
            break;
        case "P":
            $txtTipoIde = 'PPT.';
            break;

        default:
            $txtTipoIde = '';
            break;
    }
    return $txtTipoIde;
}

function retornarNombreLibroFormato2019($libro) {
    $txtLibro = '';
    switch ($libro) {

        case "RM01":
            $txtLibro = 'I del Registro Mercantil';
            break;
        case "RM02":
            $txtLibro = 'II del Registro Mercantil';
            break;
        case "RM03":
            $txtLibro = 'III del Registro Mercantil';
            break;
        case "RM04":
            $txtLibro = 'IV del Registro Mercantil';
            break;
        case "RM05":
            $txtLibro = 'V del Registro Mercantil';
            break;
        case "RM06":
            $txtLibro = 'VI del Registro Mercantil';
            break;
        case "RM07":
            $txtLibro = 'VII del Registro Mercantil';
            break;
        case "RM08":
            $txtLibro = 'VIII del Registro Mercantil';
            break;
        case "RM09":
            $txtLibro = 'IX del Registro Mercantil';
            break;
        case "RM10":
            $txtLibro = 'X del Registro Mercantil';
            break;
        case "RM11":
            $txtLibro = 'XI del Registro Mercantil';
            break;
        case "RM12":
            $txtLibro = 'XII del Registro Mercantil';
            break;
        case "RM13":
            $txtLibro = 'XIII del Registro Mercantil';
            break;
        case "RM14":
            $txtLibro = 'XIV del Registro Mercantil';
            break;
        case "RM15":
            $txtLibro = 'XV del Registro Mercantil';
            break;
        case "RM16":
            $txtLibro = 'XVI del Registro Mercantil';
            break;
        case "RM17":
            $txtLibro = 'XVII del Registro Mercantil';
            break;
        case "RM18":
            $txtLibro = 'XVIII del Registro Mercantil';
            break;
        case "RM19":
            $txtLibro = 'XIX del Registro Mercantil';
            break;
        case "RM20":
            $txtLibro = 'XX del Registro Mercantil';
            break;
        case "RM21":
            $txtLibro = 'XXI del Registro Mercantil';
            break;
        case "RM22":
            $txtLibro = 'XXII del Registro Mercantil';
            break;
        case "RE51":
            $txtLibro = 'I del Registro de Entidades Sin Ánimo de Lucro';
            break;
        case "RE52":
            $txtLibro = 'II del Registro de Entidades Sin Ánimo de Lucro';
            break;
        case "RE53":
            $txtLibro = 'III del Registro de Entidades Sin Ánimo de Lucro';
            break;
        case "RE54":
            $txtLibro = 'IV del Registro de Entidades Sin Ánimo de Lucro';
            break;
        case "RE55":
            $txtLibro = 'V del Registro de Entidades Sin Ánimo de Lucro';
            break;
    }
    return $txtLibro;
}

function retornarTipoDocFormato2019($mysqli, $tdoc, $numdoc) {
    $txtDoc = '';
    if ($tdoc == '15' && $numdoc == '1727') {
        $tdoc = '38';
    }
    if ($tdoc == '38' && $numdoc == '1727') {
        $txtDoc = 'De acuerdo a lo establecido en la ';
        $numdoc = '';
    }

    $txtDoc1 = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tdoc . "'", "descripcionlower");
    if ($txtDoc1 == '') {
        $txtDoc = 'Por documento';
    } else {
        $txtDoc = 'Por ' . $txtDoc1;
    }

    if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
        $txtDoc .= ' No. ' . $numdoc;
    }
    return $txtDoc;
}

function truncarDecimalesFormato2019($valor, $decimalesVisibles = '2') {
    // return number_format(\funcionesGenerales::truncateFloat($valor, $decimalesVisibles), $decimalesVisibles, ',', '.');
    $ent = explode(".", (string) $valor);
    if (trim((string) $ent[0]) == '') {
        $valsal = 0;
    } else {
        $valsal = number_format($ent[0], 0, ',', '.');
    }
    if (isset($ent[1])) {
        if (strlen((string) $ent[1]) == 1) {
            $valsal .= ',' . $ent[1] . '0';
        } else {
            if (strlen((string) $ent[1]) == 2) {
                $valsal .= ',' . substr((string) $ent[1], 0, 2);
            } else {
                if ($decimalesVisibles == 'T') {
                    $valsal .= ',' . $ent[1];
                } else {
                    $valsal .= ',' . substr((string) $ent[1], 0, $decimalesVisibles);
                }
            }
        }
    } else {
        $valsal .= ',00';
    }
    return $valsal;
}

function diferenciaEntreFechaBase30Formato2019($fechafinal, $fechainicial) {
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

function retornarLibroFormato2019($libro) {
    $txtLibro = '';
    switch ($libro) {
        case "RM01":
            $txtLibro = 'I';
            break;
        case "RM02":
            $txtLibro = 'II';
            break;
        case "RM03":
            $txtLibro = 'III';
            break;
        case "RM04":
            $txtLibro = 'IV';
            break;
        case "RM05":
            $txtLibro = 'V';
            break;
        case "RM06":
            $txtLibro = 'VI';
            break;
        case "RM07":
            $txtLibro = 'VII';
            break;
        case "RM08":
            $txtLibro = 'VIII';
            break;
        case "RM09":
            $txtLibro = 'IX';
            break;
        case "RM10":
            $txtLibro = 'X';
            break;
        case "RM11":
            $txtLibro = 'XI';
            break;
        case "RM12":
            $txtLibro = 'XII';
            break;
        case "RM13":
            $txtLibro = 'XIII';
            break;
        case "RM14":
            $txtLibro = 'XIV';
            break;
        case "RM15":
            $txtLibro = 'XV';
            break;
        case "RM16":
            $txtLibro = 'XVI';
            break;
        case "RM17":
            $txtLibro = 'XVII';
            break;
        case "RM18":
            $txtLibro = 'XVIII';
            break;
        case "RM19":
            $txtLibro = 'XIX';
            break;
        case "RM20":
            $txtLibro = 'XX';
            break;
        case "RM21":
            $txtLibro = 'XXI';
            break;
        case "RM22":
            $txtLibro = 'XXII';
            break;
        case "RE51":
            $txtLibro = 'I del Registro de Entidades sin Ánimo de Lucro';
            break;
        case "RE52":
            $txtLibro = 'II del Registro de Entidades sin Ánimo de Lucro';
            break;
        case "RE53":
            $txtLibro = 'III del Registro de Entidades de la Economía Solidaria';
            break;
        case "RE54":
            $txtLibro = 'IV del Registro de Entidades de Veeduría Ciudadana';
            break;
        case "RE55":
            $txtLibro = 'V del Registro de las Entidades Extranjeras de Derecho Privado sin Ánimo de Lucro';
            break;
    }
    return $txtLibro;
}

function pasar_a_oracion($entrada) {
    $salida = \funcionesGenerales::parsearOracion($entrada);
    $salida = str_replace(" De ", " de ", $salida);
    return $salida;
}

function encontrarTipoIdentificacionFormato2019($tie) {
    $ti = '';
    switch ($tie) {
        case "1":
            $ti = 'C.C.';
            break;
        case "2":
            $ti = 'NIT';
            break;
        case "3":
            $ti = 'C.E.';
            break;
        case "4":
            $ti = 'T.I.';
            break;
        case "5":
            $ti = 'PAS.';
            break;
        case "6":
            $ti = 'P.J.';
            break;
        case "E":
            $ti = 'D.E.';
            break;
        case "R":
            $ti = 'R.C.';
            break;
        case "V":
            $ti = 'PEP.';
            break;
        case "P":
            $ti = 'PPT.';
            break;
    }
    return $ti;
}

// *************************************************************************** //
// Arma libros de comercio
// *************************************************************************** //
function armarLibrosFormato2019($mysqli, $pdf, $data) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

    $nameLog = 'armarLibrosFormato2019_' . date("Ymd");

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
                $ix["grupoacto"] == '004' || $ix["grupoacto"] == '085' || (($ix["lib"] == 'RM07' || $ix["lib"] == 'RE52') && (ltrim(trim($ix["acto"]), "0") == ''))
        ) {
            if ($ix["acto"] != '0005') {
                if ($ix["crev"] != '1' && $ix["crev"] != '9') {

                    $txt2 = 'NO DEFINIDO';

                    if (trim((string) $ix["deslib"]) != '') {
                        $txt2 = trim($ix["deslib"]);
                    }

                    if ($txt2 == 'NO DEFINIDO') {
                        if (ltrim(trim((string) $ix["idlibvii"]), "0")) {
                            $txt2 = retornarRegistroMysqliApi($mysqli, "mreg_tablassirep", "idtabla='09' and idcodigo='" . $ix["idlibvii"] . "'", "descripcion");
                        }
                    }

                    if ($txt2 == 'NO DEFINIDO' || trim((string) $txt2) == '') {
                        if (substr((string) $ix["lib"], 0, 2) == 'RE') {
                            if (substr((string) $ix["not"], 0, 8) == 'PAGINAS:') {
                                $ix["numhojas"] = ltrim(trim(substr((string) $ix["not"], 8, 4)));
                                $txt2 = rtrim(trim(substr((string) $ix["not"], 12)), "-");
                            } else {
                                $txt2 = rtrim(trim((string) $ix["not"]), "-");
                            }
                        } else {
                            $txt2 = trim($ix["not"]);
                            // $txt2 = substr((string) $ix["not"], 42);
                            // if ($txt2 == '') {
                            //    $txt2 = trim($ix["not"]);
                            // }
                        }
                    }

                    if ($txt2 == 'NO DEFINIDO' || trim($txt2) == '') {
                        if (defined('CAMARA_SUR_OCCIDENTE') && CAMARA_SUR_OCCIDENTE == 'S') {
                            if ($ix["asa"] != '') {
                                $txt2 = retornarRegistroMysqliApi($mysqli, 'rp_datos_actos', "num_inscripcion='" . $ix["nreg"] . "' and cod_tipo_dato='72'");
                            }
                        }
                    }

                    if ($ix["tipolibro"] == 'E') {
                        $txt2 .= ' (ELECTRONICO)';
                    }

                    if ($ix["camant"] != '') {
                        if ($ix["lib"] == 'RM91') {
                            $txt2 .= '. EL CUAL FUE PREVIAMENTE INSCRITO EN LA ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ix["camant"] . "'", "nombre");
                        } else {
                            $txt2 .= '. EL CUAL FUE PREVIAMENTE INSCRITO EN LA ' . retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $ix["camant"] . "'", "nombre");
                            if (trim((string) $ix["fecant"]) != '') {
                                $txt2 .= ' EL ' . \funcionesGenerales::mostrarFechaLetras1($ix["fecant"]) . ' BAJO EL NÚMERO ' . $ix["regant"];
                            }
                        }
                    }

                    $txt2 .= '<br>';

                    //
                    $tieneLibros = 'SI';
                    $txt1 .= '<tr>';
                    $txt1 .= '<td width="45%">' . $txt2 . '</td>';
                    $txt1 .= '<td width="3%">&nbsp;</td>';
                    // $txt1 .= '<td width="48%">Prueba x</td>';
                    if (trim((string) $ix["lib"]) == 'RE51') {
                        $ix["lib"] = 'RE01';
                    }
                    if (trim((string) $ix["lib"]) == 'RE52') {
                        $ix["lib"] = 'RE02';
                    }
                    if (trim((string) $ix["lib"]) == 'RE53') {
                        $ix["lib"] = 'RE03';
                    }
                    if (trim((string) $ix["lib"]) == 'RE54') {
                        $ix["lib"] = 'RE04';
                    }
                    if (trim((string) $ix["lib"]) == 'RE55') {
                        $ix["lib"] = 'RE05';
                    }
                    if ($ix["lib"] == 'RM91') {
                        $txt1 .= '<td width="16%">' . $ix["libant"] . '-' . $ix["regant"] . '</td>';
                        $txt1 .= '<td width="13%">' . \funcionesGenerales::mostrarFecha($ix["fecant"]) . '</td>';
                    } else {
                        $txt1 .= '<td width="16%">' . $ix["lib"] . '-' . $ix["nreg"] . '</td>';
                        $txt1 .= '<td width="13%">' . \funcionesGenerales::mostrarFecha($ix["freg"]) . '</td>';
                    }
                    if ($ix["tipolibro"] == 'E') {
                        if ($ix["fecant"] != '') {
                            $nano = substr((string) $ix["fecant"], 0, 4);
                            $nano = (int) $nano + 1;
                            $nfec = (string) $nano . substr((string) $ix["freg"], 4);
                        } else {
                            $nano = substr((string) $ix["freg"], 0, 4);
                            $nano = (int) $nano + 1;
                            $nfec = (string) $nano . substr((string) $ix["freg"], 4);
                        }
                        $txt1 .= '<td width="23%">VIGENTE HASTA: ' . \funcionesGenerales::mostrarFecha($nfec) . '</td>';
                        $txt1 .= '</tr>';
                    } else {
                        $txt1 .= '<td width="23%"># HOJAS: ' . number_format((int) $ix["numhojas"], 0) . '</td>';
                        $txt1 .= '</tr>';
                    }
                }
            }
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
        $txt .= '<td width="45%">DESCRIPCION<br>--------------------------------------</td>';
        $txt .= '<td width="3%">&nbsp;</td>';
        $txt .= '<td width="16%">REGISTRO<br>------------</td>';
        $txt .= '<td width="13%">FECHA<br>----------</td>';
        $txt .= '<td width="23%">&nbsp;<br>--------------------<br></td>';
        $txt .= '</tr>';
        $txt .= $txt1;
        $txt .= '</table>';
        $pdf->writeHTML($txt, true, false, true, false, '');
        // $pdf->writeHTML($txt, true, false, true, false, 'L');
        $pdf->Ln();
    }
}

// *************************************************************************** //
// Certifica Reactivacion Actividad
// *************************************************************************** //
function armarCertificaReactivacionActividadFormato2019($mysqli, $pdf, $data) {
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
                        $txt = descripcionesFormato2019($mysqli, $data["organizacion"], $i["acto"], $i["tdoc"], $i["ndoc"], $i["ndocext"], $i["fdoc"], $i["idoridoc"], $i["txoridoc"], $i["idmunidoc"], $i["lib"], $i["nreg"], $i["freg"], $i["not"], '', '', '', $i["camant"], $i["libant"], $i["regant"], $i["fecant"]);
                        $pdf->writeHTML($txt, true, false, true, false, 'J');
                        $pdf->Ln();
                    }
                }
            }
        }
    }
}

function armarNombreFormato2019($nom1, $nom2, $ape1, $ape2) {
    $xnom = '';
    if (trim($nom1) != '') {
        $xnom .= trim($nom1);
    }
    if (trim($nom2) != '') {
        $xnom .= ' ' . trim($nom2);
    }
    if (trim($ape1) != '') {
        $xnom .= ' ' . trim($ape1);
    }
    if (trim($ape2) != '') {
        $xnom .= ' ' . trim($ape2);
    }
    return $xnom;
}

function armarNombreInvertidoFormato2019($nom) {
    $tnom = explode(" ", $nom);
    if (count($tnom) == 4) {
        $xnom = trim($tnom[2]) . ' ' . trim($tnom[3]) . ' ' . trim($tnom[0]) . ' ' . trim($tnom[1]);
    } else {
        $xnom = $nom;
    }
    return $xnom;
}

?>
