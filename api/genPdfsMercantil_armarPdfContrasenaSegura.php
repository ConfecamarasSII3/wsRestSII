<?php
 
function armarPdfContrasenaSegura($dbx,$clave, $ide, $nom, $ideemp, $nomemp, $email, $cel, $nommun, $feclet, $ref) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/PDFA.class.php');

    //
    if (!class_exists('PDFMSG')) {

        class PDFMSG extends TCPDF {
            
        }

    }

    //
    $pdf = new PDFMSG(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integración de Información');
    $pdf->SetTitle('Generador de contrasenas');
    $pdf->SetSubject('Contrasenas');
    // $pdf->SetKeywords();
    //
    $titulo = RAZONSOCIAL;
    $subtitulo = "GESTION DE CONTRASENAS\n";

    // set default header data
    $pdf->SetHeaderData('', 0, $titulo, $subtitulo);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    require_once($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    $pdf->setLanguageArray($l);

    // ---------------------------------------------------------
    // set font
    $pdf->SetFont('dejavusans', '', 10);

    //
    $pdf->AddPage();

    $pdf->writeHTML($nommun . ", " . $feclet, true, false, true, false, '');
    $pdf->Ln();
    $pdf->writeHTML('Señor(a)', true, false, true, false, '');
    $pdf->writeHTML($nom, true, false, true, false, '');
    $pdf->writeHTML('Identificación: ' . $ide, true, false, true, false, '');
    $pdf->writeHTML('Email: ' . $email, true, false, true, false, '');
    $pdf->writeHTML('Número celular: ' . $cel, true, false, true, false, '');
    $pdf->Ln();
    $pdf->writeHTML('Apreciado/a señor/a', true, false, true, false, '');
    $pdf->Ln();


    $txt = 'La ' . RAZONSOCIAL . ' ha dispuesto para usted la opci&oacute;n de registrarse como Usuario Verificado. ';
    $txt .= 'Al registrarse como Usuario Verificado, usted podr&aacute; realizar tr&aacute;mites en forma 100% virtual, sin necesidad ';
    $txt .= 'de desplazarse a nuestras oficinas ni hacer llegar ning&uacute;n documento en forma f&iacute;sica.<br><br>';
    $txt .= 'Para el efecto le hemos generado la siguiente contrase&ntilde;a, solo conocida por usted que le permitir&aacute; realizar ';
    $txt .= 'los procesos virtuales con total seguridad.<br><br>';
    $txt .= 'La contrase&ntilde;a es : <br><br><strong>' . $clave . '</strong><br><br>';
    $txt .= 'La contrase&ntilde;a enviada es personal e intransferible y su uso es plena responsabilidad de ' . $nom . '.';
    $txt .= 'Con esta contrase&ntilde;a segura podr&aacute; realizar cualquier tipo de tr&aacute;mite ante los registros p&uacute;blicos que ';
    $txt .= 'administra nuestra organización.<br><br>';
    $txt .= 'Para activarse como usuario verificado por favor siga el enlace que se muestra a continuación.<br>';
    $pdf->writeHTML($txt, true, false, true, false, '');


    //
    $link = TIPO_HTTP . str_replace(array(":443", ":80"), "", HTTP_HOST) . '/auv.php?_referencia=' . base64_encode($_SESSION["generales"]["codigoempresa"] . $ref);
    // $link = TIPO_HTTP . str_replace(array(":443", ":80"), "", HTTP_HOST) . "/auv.php";
    // $pdf->Link(80,150,90,90,$link);    
    $txt = '<a href="' . $link . '">' . $link . '</a>';
    $pdf->writeHTML($txt);

    $txt = '<br><br>El sistema le solicitará confirmar su identificación, correo electrónico, su número celular y la contraseña asignada. Si estos datos concuerdan con los que están reportados al ';
    $txt .= 'Registro Mercantil, entonces se le solicitará digitar el PIN de activación que le debe llegar por SMS al celular.<br><br>';
    $txt .= 'Si usted no desea activarse como Usuario Verificado, o no es el destinatario de este mensaje, por favor destruya el ';
    $txt .= 'correo electrónico que le llegó y este pdf.<br><br>';
    $txt .= 'Cordialmente<br><br>';
    $txt .= 'Dirección de registros públicos';

    //$txt = ($txt);

    $pdf->writeHTML($txt, true, false, true, false, '');
    $pdf->Ln();

    $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $ide . '-ContraSeguraX.pdf';
    $name1 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $ide . '-ContraSeguraX.pdf';

    $pdf->Output($name, 'F');
    unset($pdf);

    // Firmado digital
    $ins = new PDFA();
    $ins->generarPDFAfirmado($_SESSION["generales"]["codigoempresa"] . '-' . $ide . '-temporal', $name);
    unset($ins);
    return $name1;
}

function armarPdfContrasenaSeguraSoloClave($clave, $ide, $nom, $ideemp, $nomemp, $email, $cel, $nommun, $feclet, $ref) {

    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/PDFA.class.php');


    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/font/');
    }

    //
    $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $ide . '-ContraSegura.pdf';
    $name1 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $ide . '-ContraSegura.pdf';

    //
    $pdf = new FPDF("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    // Imprime encabezados   
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el cliente 
    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    }
    // $pdf->Image('../../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);

    $pdf->SetXY(11, 30);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Write(4, $nommun . ", " . $feclet);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Write(4, \funcionesGenerales::utf8_decode('Señor(a)'));
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Write(4, \funcionesGenerales::utf8_decode($nom));
    $pdf->Ln();
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Write(4, \funcionesGenerales::utf8_decode('Identificación: ') . $ide);
    $pdf->Ln();
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Write(4, \funcionesGenerales::utf8_decode('Email: ') . $email);
    $pdf->Ln();
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Write(4, \funcionesGenerales::utf8_decode('Número celular: ') . $cel);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Write(4, \funcionesGenerales::utf8_decode('Apreciado/a señor/a'));
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    $txt = 'La ' . RAZONSOCIAL . ' ha dispuesto para usted la opción de registrarse como Usuario Verificado. ';
    $txt .= 'Al registrarse como Usuario Verificado, usted podrá realizar trámites en forma 100% virtual, sin necesidad ';
    $txt .= 'de desplazarse a nuestras oficinas ni hacer llegar ningún documento en forma física.' . chr(13) . CHR(10) . chr(13) . chr(10);
    $txt .= 'Para el efecto le hemos generado la siguiente contraseña, solo conocida por usted que le permitirá realizar ';
    $txt .= 'los procesos virtuales con total seguridad.' . chr(13) . CHR(10) . chr(13) . chr(10);
    $txt .= 'La contraseña es : ';
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(180, 4, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetX(11);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->MultiCell(180, 4, $clave, 0, 'C', 0);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    $txt = 'La contraseña enviada es personal e intransferible y su uso es plena responsabilidad de ' . $nom . '.' . chr(13) . CHR(10) . chr(13) . chr(10);
    $txt .= 'Con esta contraseña segura podrá realizar cualquier tipo de trámite ante los registros públicos que ';
    $txt .= 'administra nuestra organización.' . chr(13) . CHR(10) . chr(13) . chr(10);
    $txt .= 'Para activarse como usuario verificado por favor diríjase a ';
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(180, 4, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
    $pdf->Ln();

    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(180, 10, TIPO_HTTP . str_replace(array(":443", ":80"), "", HTTP_HOST) . '/auv2.php?_referencia=' . base64_encode($_SESSION["generales"]["codigoempresa"] . $ref), 0, 1, "C");
    $pdf->Ln();

    $txt = 'El sistema le solicitará confirmar su identificación, correo electrónico, número celular y la contraseña asignada. Si estos datos concuerdan con los que están reportados al ';
    $txt .= 'Registro Mercantil, su activación quedará inmediatamente autorizada.' . chr(13) . CHR(10) . chr(13) . chr(10);
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(180, 4, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
    $pdf->Ln();

    $txt = 'Si usted no desea activarse como Usuario Verificado, o no es el destinatario de este mensaje, por favor destruya el correo electrónico que le llegó y este pdf. ' . chr(13) . CHR(10) . chr(13) . chr(10);
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(180, 4, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    //
    $txt = 'Cordialmente' . chr(13) . CHR(10) . chr(13) . chr(10);
    $txt .= \funcionesGenerales::utf8_decode('Dirección de registros públicos') . chr(13) . chr(10);
    $txt .= RAZONSOCIAL;
    $pdf->SetX(11);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(180, 4, $txt, 0, 'J', 0);
    $pdf->Ln();

    //
    // $pdf->_endpage();
    $pdf->Output($name, "F");
    unset($pdf);

    // Firmado digital
    $ins = new PDFA();
    $ins->generarPDFAfirmado($_SESSION["generales"]["codigoempresa"] . '-' . $ide . '-temporal', $name);
    unset($ins);
    return $name1;
}

?>