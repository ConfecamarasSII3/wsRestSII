<?php

/**
 * Arma el pdf con las imágenes fotogrfáficas de quien radica en caja
 * @param type $detalle
 * @param type $evidencias
 * @param type $rutaSalida
 * @return type
 */
function armarPdfImagenesApi635($dbx, $detalle, $evidencias, $rutaSalida) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

    if (!class_exists('PDFEvidencias')) {

        class PDFEvidencias extends TCPDF {

            public $nrocontrolsipref = '';
            public $page_counter = true;
            public $razonsocial = '';
            public $logo = '';

            public function Make($num) {
                $this->page_counter = $num;
            }

            public function Header() {

                $this->Rect(10, 9, 195, 250);
                $this->SetMargins(10, 40, 10);
                $this->Image($this->logo, 15, 12, 20, 20);
                $this->SetFont('helvetica', 'B', 11);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 18);
                $this->Cell(200, 4, \funcionesGenerales::utf8_decode($this->razonsocial), 0, 0, 'C');
                $this->SetTextColor(139, 0, 0);
                $this->SetXY(12, 22);
                $this->Cell(50);
                $this->Cell(100, 4, 'NÚMERO DE CONTROL SIPREF : ' . $this->nrocontrolsipref, 0, 0, 'C');
            }

            public function Footer() {
                
            }

        }

    }

    /*
     * CREACION DE ARCHIVO PDF
     */
    $estampaInicio = date("H:i:s");

    $pdf = new PDFEvidencias(PDF_PAGE_ORIENTATION, PDF_UNIT, "LETTER", true, 'UTF-8', false, true);

    $pdf->nrocontrolsipref = $detalle["nrocontrolsipref"];
    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
        $pdf->logo = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg';
    }
    $pdf->razonsocial = RAZONSOCIAL_RESUMIDA;

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integración de Información');
    $pdf->SetTitle('Evidencias SIPREF - SII');
    $pdf->SetSubject('SII');
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, 28);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Array language
    $l['a_meta_charset'] = 'UTF-8';
    $l['a_meta_dir'] = 'ltr';
    $l['a_meta_language'] = 'en';
    $l['w_page'] = 'page';

    $pdf->setLanguageArray($l);
    $pdf->setFontSubsetting(false);

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);


    $txt = '';
    if (trim($detalle["tipotramite"]) != '') {
        $txt .= '<strong>Tipo trámite : </strong>' . $detalle["tipotramite"] . '<br>';
    }
    if (trim($detalle["idusuario"]) != '') {
        $txt .= '<strong>Usuario : </strong>' . $detalle["idusuario"] . '<br>';
    }
    if (trim($detalle["fechahora"]) != '') {
        $txt .= '<strong>Fecha y Hora : </strong>' . $detalle["fechahora"] . '<br>';
    }
    if (trim($detalle["nombreradicador"]) != '') {
        $txt .= '<strong>Nombre : </strong>' . $detalle["nombreradicador"] . '<br>';
    }
    if (trim($detalle["tipoideradicador"]) != '') {
        $txt .= '<strong>Tipo de dentificación : </strong>' . $detalle["tipoideradicador"] . '<br>';
    }
    if (trim($detalle["ideradicador"]) != '') {
        $txt .= '<strong>Identificación : </strong>' . $detalle["ideradicador"] . '<br>';
    }
    if (trim($detalle["fechaexpradicador"]) != '') {
        $txt .= '<strong>Fecha expedición : </strong>' . $detalle["fechaexpradicador"] . '<br>';
    }
    if (trim($detalle["recibo"]) != '') {
        $txt .= '<strong>Recibo : </strong>' . $detalle["recibo"] . '<br>';
    }
    if (trim($detalle["codigobarras"]) != '') {
        $txt .= '<strong>Código Barras : </strong>' . $detalle["codigobarras"] . '<br>';
    }

    $pdf->writeHTML($txt, true, false, true, false, 'C');

    foreach ($evidencias as $l) {
        if (substr($l, 0, 4) == 'http') {
            $pdf->writeHTML('<img height="180" src="' . $l . '" alt="" />', true, false, true, false, 'C');
        } else {
            if (file_exists($l)) {
                $pdf->writeHTML('<img height="180" src="' . $l . '" alt="" />', true, false, true, false, 'C');
            } else {
                $pdf->writeHTML('&nbsp;', true, false, true, false, 'C');
            }
        }
    }
    // $pdf->Make(false);
    $pdf->Output($rutaSalida, "F");
    unset($pdf);
    return $rutaSalida;
}

/**
 * Arma el pdf de la notificacion vía email y la inserta en
 * mreg_radicacionesanexos
 * 
 * @param type $tnot    : tipo de notificacion
 *                          - 01.- radicacion
 *                          - 02.- Devolucion
 *                          - 03.- Inscripcion
 *                          - 04.- Desistimiento
 *                          - 05.- Reingreso
 *                          - 06.- Archivo desistimiento
 *                          - 07.- Reactivación de expedientes
 * @param type $rad     : Codigo de barras
 * @param type $dev     : Numero de devolucion
 * @param type $ope     : Numero de operacion
 * @param type $rec     : Numero del recibo
 * @param type $lib     : Libro
 * @param type $reg     : Registro
 * @param type $dup     : dupli
 * @param type $idc     : Clase de identificacion
 * @param type $ide     : Identificacion
 * @param type $mat     : Matricula
 * @param type $pro     : Proponente
 * @param type $nom     : Nombre
 * @param type $ema     : Email
 * @param type $fpro    : fecha de programacion
 * @param type $hpro    : hora de programacion
 * @param type $fnot    : fecha de notificacion
 * @param type $hnot    : Hora de notificacion
 * @param type $est     : Estado
 *                          - 1.- Pendiente de envio
 *                          - 2.- Enviado con error
 *                          - 3.- Envio satisfactorio
 * @param type $obs     : Observaciones 
 * @param type $bandeja : Bandeja
 * @param type $procesoespecial     : Proceso especial 
 */
function generarPdfNotificacionEmailApi635($dbx, $tnot = '', $rad = '', $dev = '', $ope = '', $rec = '', $lib = '', $reg = '', $dup = '', $idc = '', $ide = '', $mat = '', $pro = '', $nom = '', $ema = '', $det = '', $fpro = '', $hpro = '', $fnot = '', $hnot = '', $est = '', $obs = '', $bandeja = '', $procesoespecial = '') {
    ini_set('memory_limit', '1024M');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    $resError = set_error_handler('myErrorHandler');

    $_SESSION["generales"]["archivosalida"] = '';

    if (!class_exists('PDFNotificaciones')) {

        class PDFNotificaciones extends TCPDF {

            public $page_counter = true;
            public $logo = '';
            public $razonsocial = '';

            public function Make($num) {
                $this->page_counter = $num;
            }

            public function Header() {
                $this->Rect(10, 9, 195, 250);
                $this->SetMargins(10, 40, 10);
                $this->Image($this->logo, 15, 12, 20, 20);
                $this->SetFont('helvetica', 'B', 11);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 18);
                $this->Cell(200, 4, \funcionesGenerales::utf8_decode($this->razonsocial), 0, 0, 'C');
                $this->SetTextColor(139, 0, 0);
                $this->SetXY(12, 22);
                $this->Cell(50);
            }

            public function Footer() {
                
            }

        }

    }

    /*
     * CREACION DE ARCHIVO PDF
     */
    $pdf = new PDFNotificaciones(PDF_PAGE_ORIENTATION, PDF_UNIT, "LETTER", true, 'UTF-8', false, true);

    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
        $pdf->logo = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg';
    }
    $pdf->razonsocial = RAZONSOCIAL_RESUMIDA;

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integración de Información');
    $pdf->SetTitle('Notificaciones SIPREF - SII');
    $pdf->SetSubject('SII');
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, 28);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Array language
    $l['a_meta_charset'] = 'UTF-8';
    $l['a_meta_dir'] = 'ltr';
    $l['a_meta_language'] = 'en';
    $l['w_page'] = 'page';

    $pdf->setLanguageArray($l);
    $pdf->setFontSubsetting(false);

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);

    $txtnot = '';
    switch ($tnot) {
        case "01" : $txtnot = 'Radicación de trámite';
            break;
        case "02" : $txtnot = 'Devolución';
            break;
        case "03" : $txtnot = 'Inscripción';
            break;
        case "04" : $txtnot = 'Desistimiento tácito';
            break;
        case "05" : $txtnot = 'Reingreso';
            break;
        case "06" : $txtnot = 'Archivo de desistimiento';
            break;
        case "07" : $txtnot = 'Apertura de libros';
            break;
        case "08" : $txtnot = 'Anotaciones en libros';
            break;
        case "09" : $txtnot = 'Reactivación del expediente';
            break;
        case "10" : $txtnot = 'Asentar renovaciones';
            break;
        case "20" : $txtnot = 'Beneficios matrícula';
            break;
        case "21" : $txtnot = 'Beneficios renovación';
            break;
        case "30" : $txtnot = 'Soportes de pago';
            break;
        case "40" : $txtnot = 'Aviso o alerta';
            break;
        case "41" : $txtnot = 'Informa sobre archivo del trámite';
            break;
        
    }

    //
    $txt = '';
    $txt .= '<strong>' . RAZONSOCIAL . '</strong><br>';
    $txt .= '<strong>SOPORTE DE NOTIFICACIÓN A EMAIL</strong><br>';
    $txt .= '<strong>Fecha y hora de programación de la notificación : </strong>' . \funcionesGenerales::mostrarFecha($fpro) . ' - ' . \funcionesGenerales::mostrarHora($hpro) . '<br>';
    $txt .= '<br>';


    $txt .= '<strong>Tipo de notificacion : </strong>' . $txtnot . '<br>';
    if (trim($rad) != '') {
        $txt .= '<strong>Código de barras / radicado : </strong>' . $rad . '<br>';
    }
    if (trim($dev) != '') {
        $txt .= '<strong>Devolución : </strong>' . $dev . '<br>';
    }
    /*
      if (trim($ope) != '') {
      $txt .= '<strong>Operación : </strong>' . $ope . '<br>';
      }
     */
    if (trim($rec) != '') {
        $txt .= '<strong>Recibo : </strong>' . $rec . '<br>';
    }
    if (trim($lib) != '') {
        $txt .= '<strong>Registro en libros : </strong>' . $lib . '-' . $reg . '-' . $dup . '<br>';
    }
    $txt .= '<strong>Afectado : </strong>' . $ide . ' - ' . $nom . '<br>';
    if (trim($mat) != '') {
        $txt .= '<strong>Matrícula/Inscripción : </strong>' . $mat . '<br>';
    }
    if (trim($pro) != '') {
        $txt .= '<strong>Proponente : </strong>' . $pro . '<br>';
    }
    $txt .= '<strong>Email/Correo electrónico : </strong>' . $ema . '<br>';
    $txt .= '<br>';
    $txt .= '<strong>Detalle de la notificación : </strong><br>';
    $txt .= $det . '<br>';
    $txt .= '<br>';
    if (trim($obs) != '') {
        $txt .= '<strong>Observaciones : </strong>' . $obs . '<br>';
    }

    //
    $pdf->writeHTML($txt, true, false, true, false, 'C');

    //
    // $pdf->Make(false);    
    $rutaSalida = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-' . date("Ymd") . date("His") . rand(1000, 10000) . '.pdf';
    $pdf->Output($rutaSalida, "F");
    unset($pdf);

    $tdoc = TIPO_DOC_SIPREF_EMAIL_MERCANTIL;
    if ($bandeja == '5.-ESADL' || $bandeja == '5.-REGESADL') {
        $tdoc = TIPO_DOC_SIPREF_EMAIL_ESADL;
    }
    if ($bandeja == '6.-REGPRO') {
        $tdoc = TIPO_DOC_SIPREF_EMAIL_PROPONENTES;
    }

    //
    $id = \funcionesRegistrales::grabarAnexoRadicacion(
                    $dbx, // Conexion BD
                    $rad, // Numero del radicado
                    $rec, // recibo
                    $ope, // Numero de operacion
                    $ide, // identificacion
                    $nom, // Nombre 
                    '', // Acreedor
                    '', // Nombre acreedor
                    $mat, // Matricula
                    $pro, // Proponente
                    $tdoc, // Tipo de documento
                    '', // Numero del documento
                    $fpro, // Fecha del soporte
                    '', // Codigo de origen
                    'AREA DE REGISTROS PUBLICOS', // Txtorigen
                    '', // Clasificaci&oacute;n
                    '', // Numero del contrato
                    '', // Idfuente
                    1, // version
                    '', // Path
                    '1', // Estado
                    date("Ymd"), // fecha de escaneo o generaciOn
                    $_SESSION["generales"]["codigousuario"], // usuario
                    '', // Caja de archivo
                    '', // Libro de archivo
                    'SOPORTE NOTIFICACIÓN SIPREF ENVIO A EMAIL DE ' . mb_strtoupper($txtnot, 'utf-8') . ' - ' . $ema, // Observaciones
                    $lib, // Libro de registro
                    $reg, // Numero de registro
                    $dup, // Dupli
                    $bandeja, // Bandeja
                    'N', // Soporte recibo
                    '', // identificador
                    '518', // Tipo de anexo
                    $procesoespecial // Proceso especial
    );

    // Traslada la imagen pdf del sello al repositorio    

    $dirx = date("Ymd");
    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
    if (!is_dir($path) || !is_readable($path)) {
        mkdir($path, 0777);
        \funcionesGenerales::crearIndex($path);
    }

    $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
    copy($rutaSalida, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
    unlink($rutaSalida);
    \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
    $_SESSION["generales"]["archivosalida"] = $pathsalida;
    //
    return $id;
}

/**
 *  Genera pdf cvon la notificación sipref a celular
 * @param type $cel : Celular
 * @param type $tip : Tipo de notificacion
 *                      - 1.- Radicacion
 *                      - 2.- Inscripcion
 *                      - 3.- Firmados
 *                      - 4.- Devoluciones
 *                      - 5.- Desistimientos
 *                      - 6.- Verificacion de identidad
 *                      - 7.- Firmado de trámites
 *                      - 8.- Reingreso de trámites
 *                      - 9.- Reactivacion de expedientes
 * @param type $rec : Recibo
 * @param type $cba : Codigo de barras
 * @param type $ins : Inscripcion
 * @param type $dev : Devolucion
 * @param type $exp : Expediente
 * @param type $mat : matricula
 * @param type $pro : Proponente
 * @param type $ide : Idntificacion
 * @param type $nom : Nombre
 * @param type $det : Texto
 * @param type $obs : Observaciones
 * @param type $bandeja : Bandeja
 * @param type $procesoespecial : Proceso especial
 * 
 * @return boolean 
 */
function generarPdfNotificacionSmsApi635($dbx, $cel = '', $tip = '', $rec = '', $cba = '', $ins = '', $dev = '', $exp = '', $mat = '', $pro = '', $ide = '', $nom = '', $det = '', $obs = '', $bandeja = '', $procesoespecial = '') {
    // Arma el pdf con el soporte de la notificación
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    $resError = set_error_handler('myErrorHandler');

    $_SESSION["generales"]["archivosalida"] = '';

    if (!class_exists('PDFNotificaciones')) {

        class PDFNotificaciones extends TCPDF {

            public $page_counter = true;
            public $logo = '';
            public $razonsocial = '';

            public function Make($num) {
                $this->page_counter = $num;
            }

            public function Header() {
                $this->Rect(10, 9, 195, 250);
                $this->SetMargins(10, 40, 10);
                $this->Image($this->logo, 15, 12, 20, 20);
                $this->SetFont('helvetica', 'B', 11);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(12, 18);
                $this->Cell(200, 4, \funcionesGenerales::utf8_decode($this->razonsocial), 0, 0, 'C');
                $this->SetTextColor(139, 0, 0);
                $this->SetXY(12, 22);
                $this->Cell(50);
                // $this->Cell(100, 4, 'NÚMERO DE CONTROL SIPREF : ' . $this->nrocontrolsipref, 0, 0, 'C');
            }

            public function Footer() {
                
            }

        }

    }

    /*
     * CREACION DE ARCHIVO PDF
     */
    $pdf = new PDFNotificaciones(PDF_PAGE_ORIENTATION, PDF_UNIT, "LETTER", true, 'UTF-8', false, true);

    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
        $pdf->logo = $_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg';
    }
    $pdf->razonsocial = RAZONSOCIAL_RESUMIDA;

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integración de Información');
    $pdf->SetTitle('Notificaciones SIPREF - SII');
    $pdf->SetSubject('SII');
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, 28);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Array language
    $l['a_meta_charset'] = 'UTF-8';
    $l['a_meta_dir'] = 'ltr';
    $l['a_meta_language'] = 'en';
    $l['w_page'] = 'page';

    $pdf->setLanguageArray($l);
    $pdf->setFontSubsetting(false);

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);

    $txtnot = '';
    switch ($tip) {
        case "1" : $txtnot = 'Radicación de trámite';
            break;
        case "2" : $txtnot = 'Inscripción';
            break;
        case "3" : $txtnot = 'Firmados';
            break;
        case "4" : $txtnot = 'Devolución';
            break;
        case "5" : $txtnot = 'Desistimiento';
            break;
        case "6" : $txtnot = 'Verificación de identidad';
            break;
        case "7" : $txtnot = 'Firmado de trámites';
            break;
        case "8" : $txtnot = 'Reingreso';
            break;
        case "9" : $txtnot = 'Reactivación de expedientes';
            break;
        case "10" : $txtnot = 'Asentamiento';
            break;
        case "11" : $txtnot = 'Alerta temprana';
            break;
        case "90" : $txtnot = 'Consulta';
            break;        
        case "91" : $txtnot = 'Información de archivo de trámite';
            break;
        
    }

    //
    $txt = '';
    $txt .= '<strong>' . RAZONSOCIAL . '</strong><br>';
    $txt .= '<strong>SOPORTE DE NOTIFICACIÓN A SMS</strong><br>';
    $txt .= '<strong>Fecha y hora de programación de la notificación : </strong>' . \funcionesGenerales::mostrarFecha(date("Ymd")) . ' - ' . \funcionesGenerales::mostrarHora(date("His")) . '<br>';
    $txt .= '<br>';


    $txt .= '<strong>Tipo de notificacion : </strong>' . $txtnot . '<br>';
    if (trim($cba) != '') {
        $txt .= '<strong>Código de barras / radicado : </strong>' . $cba . '<br>';
    }
    if (trim($dev) != '') {
        $txt .= '<strong>Devolución : </strong>' . $dev . '<br>';
    }
    if (trim($rec) != '') {
        $txt .= '<strong>Recibo : </strong>' . $rec . '<br>';
    }

    $lib = '';
    $reg = '';
    $dup = '';
    if (trim($ins) != '') {
        $txt .= '<strong>Registro en libros : </strong>' . $ins . '<br>';
        $ar = explode("-", $ins);
        $lib = $ar[0];
        $reg = $ar[1];
        if (isset($ar[2])) {
            $dup = $ar[2];
        }
    }
    $txt .= '<strong>Afectado : </strong>' . $ide . ' - ' . $nom . '<br>';
    if (trim($mat) != '') {
        $txt .= '<strong>Matrícula/Inscripción : </strong>' . $mat . '<br>';
    }
    if (trim($pro) != '') {
        $txt .= '<strong>Proponente : </strong>' . $pro . '<br>';
    }
    $txt .= '<strong>Celular : </strong>' . $cel . '<br>';
    $txt .= '<br>';
    $txt .= '<strong>Detalle de la notificación : </strong><br>';
    $txt .= $det . '<br>';
    $txt .= '<br>';
    if (trim($obs) != '') {
        $txt .= '<strong>Observaciones : </strong>' . $obs . '<br>';
    }

    //
    $pdf->writeHTML($txt, true, false, true, false, 'C');

    //
    // $pdf->Make(false);    
    $rutaSalida = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-' . date("Ymd") . date("His") . rand(1000, 10000) . '.pdf';
    $pdf->Output($rutaSalida, "F");
    unset($pdf);

    $tdoc = TIPO_DOC_SIPREF_SMS_MERCANTIL;
    if ($bandeja == '5.-ESADL' || $bandeja == '5.-REGESADL') {
        $tdoc = TIPO_DOC_SIPREF_SMS_ESADL;
    }
    if ($bandeja == '6.-REGPRO') {
        $tdoc = TIPO_DOC_SIPREF_SMS_PROPONENTES;
    }

    //
    $id = \funcionesRegistrales::grabarAnexoRadicacion(
                    $dbx, // Conexion
                    $cba, // Numero del radicado
                    $rec, // recibo
                    '', // Numero de operacion
                    $ide, // identificacion
                    $nom, // Nombvre 
                    '', // Acreedor
                    '', // Nombre acreedor
                    $mat, // Matricula
                    $pro, // Proponente
                    $tdoc, // Tipo de documento
                    '', // Numero del documento
                    date("Ymd"), // Fecha del soporte
                    '', // Codigo de origen
                    'AREA DE REGISTROS PUBLICOS', // Txtorigen
                    '', // Clasificaci&oacute;n
                    '', // Numero del contrato
                    '', // Idfuente
                    1, // version
                    '', // Path
                    '1', // Estado
                    date("Ymd"), // fecha de escaneo o generaciOn
                    $_SESSION["generales"]["codigousuario"], // usuario
                    '', // Caja de archivo
                    '', // Libro de archivo
                    'SOPORTE NOTIFICACIÓN SIPREF VIA SMS - ' . mb_strtoupper($txtnot, 'utf-8') . ' - ' . $cel, // Observaciones
                    $lib, // Libro de registro
                    $reg, // Numero de registro
                    $dup, // Dupli
                    $bandeja, // Bandeja
                    'N', // Soporte recibo
                    '', // identificador
                    '519', // Tipo de anexo
                    $procesoespecial // Proceso especial
    );

    // Traslada la imagen pdf del sello al repositorio    
    $dirx = date("Ymd");
    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
    if (!is_dir($path) || !is_readable($path)) {
        mkdir($path, 0777);
        \funcionesGenerales::crearIndex($path);
    }

    $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
    copy($rutaSalida, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
    unlink($rutaSalida);
    \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
    $_SESSION["generales"]["archivosalida"] = $pathsalida;

    //
    return $id;
}

?>