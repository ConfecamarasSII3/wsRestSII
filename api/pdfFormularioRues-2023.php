<?php

/**
 * Clase para permite imprimir los campos en sus posiciones respectivas del formulario Rues y sus anexos
 *
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2017/05/01
 *
 */
// require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf_alpha.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');

// class formularioRues2020Api extends PDF_ImageAlpha {
class formularioRues2023Api extends FPDF {

    // Declaración como variables privadas de las rutas de los formularios creados en formato png
    private $imagen1 = '/images/sii/formatos/2020/hoja_1.png';
    private $imagen2 = '/images/sii/formatos/2023/hoja_2.png';
    private $imagen3 = '/images/sii/formatos/2020/hoja_3.png';
    private $imagen4 = '/images/sii/formatos/2020/hoja_4.png';
    private $imagen5 = '/images/sii/formatos/2020/hoja_5.png';
    private $imagen6 = '/images/sii/formatos/2020/hoja_6.png';
    private $imagen7 = '/images/sii/formatos/2020/hoja_7.png';
    private $imagen8 = '/images/sii/formatos/2020/hoja_8.png';
    private $imagen9 = '/images/sii/formatos/2020/hoja_9.png';
    private $imagen10 = '/images/sii/formatos/2020/hoja_10.png';
    private $imagen11 = '/images/sii/formatos/2020/hoja_11.png';
    private $imagen12 = '/images/sii/formatos/2020/hoja_12.png';
    private $logo = "";
    private $empresa = "";
    private $pagina = "";
    private $indice = "";
    private $numeroRecuperacion = "";
    private $numeroLiquidacion = "";
    private $fechaImpresion = "";

    function Footer() {
        $this->SetY(- 25);
        $this->SetFont('Courier', 'B', 8);

        if ($this->PageNo() >= 1) {
            if ($this->getNumeroLiquidacion() != '') {
                $this->Cell(180, 10, "Nro. Liq. " . $this->getNumeroLiquidacion(), 0, 0, 'R');
            }
            $this->SetY(- 22);
            if ($this->getFechaImpresion() != '') {
                $this->Cell(180, 10, "Fecha: " . $this->getFechaImpresion(), 0, 0, 'R');
            }
        }
    }

    /* Función que realiza rotación del texto */

    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == - 1) {
            $x = $this->x;
        }
        if ($y == - 1) {
            $y = $this->y;
        }
        if ($this->angle != 0) {
            $this->_out('Q');
        }
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

    /* Función que imprime texto rotado */

    function RotatedText($x, $y, $txt, $angle = 0) {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    /* Función cambia el tamaño de la fuente */

    private function tamFuente($_val, $fuente = 'Courier', $negrita = '') {
        if ($fuente == 'Arial') {
            $this->SetFont($fuente, '', $_val);
        } else {
            $this->SetFont($fuente, $negrita, $_val);
        }
    }

    /*
     * Método privado realiza la impresión de texto a partir de un arreglo con el nombre del campo,
     *  el contenido, la ubicación, separación, tamaño y longitud respectiva
     */

    private function imprimirTexto($arr) {


        // 'p1.paslar' => array (posX, posxY,SepCaracteres, tamLetra, longContenido),

        $nombreCampo = isset($arr [0]) ? $arr [0] : '';
        $contenidoRecibido = isset($arr [1]) ? $arr [1] : '';
        $posX = isset($arr [2]) ? $arr [2] : '';
        $posY = isset($arr [3]) ? $arr [3] : '';
        $sepCaracteres = isset($arr [4]) ? $arr [4] : '1';
        $tamLetra = isset($arr [5]) ? $arr [5] : '9';
        $longContenido = isset($arr [6]) ? $arr [6] : '1';
        if (($posX != 0) and ( $posY != 0)) {

            $this->tamFuente($tamLetra);
            $this->SetXY($posX, $posY);
            if (strlen($contenidoRecibido) > $longContenido) {
                $contenidoRetornado = substr($contenidoRecibido, 0, $longContenido);
            } else {
                $contenidoRetornado = $contenidoRecibido;
            }

            switch ($nombreCampo) {
                case 'p9.facul_detalle':
                    $this->tamFuente($tamLetra, 'Courier', '');
                    $this->MultiCell(167, 1.6, $contenidoRetornado, 0, 'J', 0, 1);
                    break;

                case 'p1.sec4_desc_obj_soc':
                    $this->SetX($posX);
                    $this->MultiCell(166, 2.2, $contenidoRetornado, 0, 'J', 0, 1);
                    break;
                case 'p3.sec2_desc_act_eco':
                    $this->SetX($posX);
                    $this->MultiCell(178, 2.2, $contenidoRetornado, 0, 'J', 0, 1);
                    break;
                case 'p2.firma_elec':
                    $this->SetX($posX);
                    $this->MultiCell(41, 2, $contenidoRetornado, 0, 'C', 0, 1);
                    break;
                case 'p3.firma_elec':
                    $this->SetX($posX);
                    $this->MultiCell(95, 2, $contenidoRetornado, 0, 'C', 0, 1);
                    break;
                case 'p4.firma_elec':
                    $this->SetX($posX);
                    $this->MultiCell(47, 2, $contenidoRetornado, 0, 'C', 0, 1);
                    break;
                case 'p5.firma_elec' :
                case 'p6.firma_elec' :
                case 'p7.firma_elec' :
                case 'p8.firma_elec' :
                    $this->SetX($posX);
                    $this->MultiCell(48, 2, $contenidoRetornado, 0, 'C', 0, 1);
                    break;
                case 'p9.firma_elec' :
                case 'p10.firma_elec' :
                case 'p11.firma_elec' :
                case 'p12.firma_elec' :
                    $this->SetX($posX);
                    $this->MultiCell(48, 2, $contenidoRetornado, 0, 'C', 0, 1);
                    break;                
                case 'p7.sec7_6_text':
                    //3007
                    $this->SetX($posX);
                    $this->MultiCell(167, 2.4, $contenidoRetornado, 0, 'C', 0, 1);
                    break;
                case 'p2.firma_manuscrita' :
                case 'p4.firma_manuscrita' :
                case 'p5.firma_manuscrita' :
                case 'p11.firma_manuscrita' :
                case 'p12.firma_manuscrita' :
                    $tmpfile = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . rand(1000000, 9999999) . '-' . date("Ymd") . '-' . date("His") . '.png';
                    $f = fopen($tmpfile, "wb");
                    fwrite($f, base64_decode($contenidoRecibido));
                    fclose($f);
                    $this->Image($tmpfile, $posX, $posY, 30, 20);
                    break;
                case 'p3.firma_manuscrita' :
                    $tmpfile = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . rand(1000000, 9999999) . '-' . date("Ymd") . '-' . date("His") . '.png';
                    $f = fopen($tmpfile, "wb");
                    fwrite($f, base64_decode($contenidoRecibido));
                    fclose($f);
                    $this->Image($tmpfile, $posX, $posY, 20, 15);
                    break;

                default:
                    $arrContenido = str_split($contenidoRetornado);
                    foreach ($arrContenido as $valor) {
                        $this->SetXY($posX, $posY);
                        $this->Cell(0, 0, $valor, 0, 0);
                        $posX = $posX + 4 + $sepCaracteres;
                    }

                    break;
            }
        }
    }

    /* Función imprime guias de página */

    private function lineas() {

        for ($x = 1; $x < 277; $x += 5) {
            $x ++;
            $this->tamFuente(6);

            if ($x > 10) {
                $this->Line(7, $x, 200, $x);
            }
            $this->SetXY(3, $x);
            $this->Cell(0, 0, $x);
        }

        for ($x = 1; $x < 205; $x += 5) {
            $x ++;
            $this->tamFuente(6);

            if ($x > 10) {
                $this->Line($x, 4, $x, 275);
            }
            $this->SetXY($x, 2);
            if ($x < 100) {
                $this->Cell(0, 0, $x);
            } else {
                $this->Cell(0, 0, $x - 100);
            }
        }
    }

    /* Método público que agrega página al pdf final con parametros el número 
     * de la página y estado (0=Borrador 1=Normal) 
     */

    public function agregarPagina($numPagina, $estado) {
        $this->pagina = $numPagina;
        $nomVariablePNG = 'imagen' . $numPagina;

        if (isset($this->pagina)) {

            $this->AddPage('Portrait', 'A4');
            $this->SetCreator('SII - CONFECAMARAS');

            $this->Image($_SESSION["generales"]["pathabsoluto"] . $this->$nomVariablePNG, 10, 10, 190, 269);
            //$this->ImagePngWithAlpha($this->$nomVariablePNG, 10, 10, 190, 269);

            $this->empresa = $_SESSION ['generales'] ['codigoempresa'];

            //
            if (file_exists($_SESSION["generales"]["pathabsoluto"] . "/images/logocamara" . $_SESSION ['generales'] ['codigoempresa'] . ".jpg")) {
                $this->logo = $_SESSION["generales"]["pathabsoluto"] . "/images/logocamara" . $_SESSION ['generales'] ['codigoempresa'] . ".jpg";
                $type = 'JPG';
            }

            //
            if (file_exists($this->logo)) {
                switch ($this->pagina) {
                    case 1 :
                        $this->Image($this->logo, 22, 10, 25, 25, $type);
                        break;
                    case 2 :
                        $this->Image($this->logo, 22, 8, 25, 25, $type);
                        break;
                    case 3 :
                        $this->Image($this->logo, 22, 10, 25, 25, $type);
                        break;
                    case 4 :
                        $this->Image($this->logo, 22, 20, 15, 15, $type);
                        break;
                    case 5 :
                        $this->Image($this->logo, 20, 26, 15, 15, $type);
                        break;
                    case 6 :
                        $this->Image($this->logo, 20, 24, 15, 15, $type);
                        break;
                    case 7 :
                        $this->Image($this->logo, 22, 24, 15, 15, $type);
                        break;
                    case 8 :
                        $this->Image($this->logo, 22, 28, 15, 15, $type);
                        break;
                    case 9 :
                        $this->Image($this->logo, 22, 34, 15, 15, $type);
                        break;
                    case 10 :
                        $this->Image($this->logo, 22, 26, 15, 15, $type);
                        break;
                    case 11 :
                        $this->Image($this->logo, 22, 24, 25, 25, $type);
                        break;
                    case 12 :
                        $this->Image($this->logo, 22, 10, 25, 25, $type);
                        break;
                    
                }
            }

            //$this->SetDrawColor(18, 242, 26);
            //$this->lineas();

            $this->SetFont('Courier', 'B', 12);

            switch ($this->pagina) {
                case 1 :
                    $this->SetXY(175, 16);
                    break;
                case 2 :
                    $this->SetXY(175, 15);
                    break;
                case 3 :
                    $this->SetXY(179, 21);
                    break;
                case 4 :
                    $this->SetXY(175, 21);
                    break;
                case 5 :
                    $this->SetXY(175, 27);
                    break;
                case 6 :
                    $this->SetXY(175, 26);
                    break;
                case 7 :
                    $this->SetXY(175, 27);
                    break;
                case 8 :
                    $this->SetXY(175, 28);
                    break;
                case 9 :
                    $this->SetXY(175, 36);
                    break;
                case 10 :
                    $this->SetXY(175, 28);
                    break;
                case 11 :
                    $this->SetXY(175, 31);
                    break;
                case 12 :
                    $this->SetXY(175, 10);
                    break;
                
            }
            $this->Cell(0, 35, "" . $this->getNumeroRecuperacion());

            if ($estado === 0) {
                $this->SetTextColor(202, 202, 202);
                $this->SetFont('Arial', '', 56);
                $this->RotatedText(20, 240, 'FORMULARIO BORRADOR', 45);
                $this->SetTextColor(0, 0, 0);
            }
        }
    }

    /* Método público que construye campo en el formulario */

    public function obtenerArregloCampos($numPagina, $item) {
        $x = $y = 0;

        switch ($numPagina) {
            case '1':
                /*  PERSONAS NATURALES ANEXO - HOJA 1  */

                //revisado archivo : hoja_1.png

                $p = array(
                    'p1.cod_camara' => array(150, 45, -2, 9, 18),
                    'p1.sec1_col1_mat_ins' => array(69, 67, 9, 9, 1),
                    'p1.sec1_col1_ren' => array(69, 71, 9, 9, 1), // 66, 72
                    'p1.sec1_col1_tra_dom' => array(69, 75, 9, 9, 1),
                    'p1.sec1_col1_aju_inf' => array(69, 79, 9, 9, 1),
                    'p1.sec1_col1_num_mat' => array(28.5, 84, 0.28, 9, 10),
                    'p1.sec1_col1_ano_ren' => array(53.5, 88, 0.45, 9, 4),
                    'p1.sec1_col1_cod_org_gen' => array(70, 92, 0.45, 9, 2),
                    'p1.sec1_col1_cod_org_esp' => array(70, 98, 0.45, 9, 2),
                    'p1.sec1_col1_bic' => array(70, 104, 0.45, 9, 2), //                    
                    'p1.sec1_col2_ins' => array(121.5, 69, 9, 9, 1),
                    'p1.sec1_col2_ren' => array(121.5, 74, 9, 9, 1),
                    'p1.sec1_col2_tra_dom' => array(121.5, 79, 9, 9, 1),
                    'p1.sec1_col2_aju_inf' => array(121.5, 84, 9, 9, 1),
                    'p1.sec1_col2_num_ins' => array(83.5, 92, 0.28, 9, 10),
                    'p1.sec1_col2_ano_ren' => array(108.5, 97, 0.45, 9, 4),
                    'p1.sec1_col3_ins' => array(174.5, 68.5, 9, 9, 1),
                    'p1.sec1_col3_act' => array(174.5, 73.5, 9, 9, 1),
                    'p1.sec1_col3_ren' => array(174.5, 79, 9, 9, 1),
                    'p1.sec1_col3_can' => array(174.4, 85, 0.45, 9, 1),
                    'p1.sec1_col3_act_tra' => array(174.5, 91.5, 9, 9, 1),
                    'p1.sec1_col3_cam_ant' => array(138.5, 97.5, -2, 9, 19),
                    'p1.sec1_col3_num_ins' => array(139, 105, 0, 9, 10),
                    'p1.sec2_raz_soc' => array(43.5, 117, -2.5, 8.5, 60),
                    'p1.sec2_sig' => array(143, 117, -2.5, 8.5, 30),
                    'p1.sec2_nit' => array(36, 126, 0.2, 9, 11),
                    'p1.sec2_dv' => array(102, 126, 0, 9, 1),
                    'p1.sec2_ape1' => array(46, 122, -2.5, 9, 20),
                    'p1.sec2_ape2' => array(87, 122, -2.5, 9, 20),
                    'p1.sec2_nom1' => array(124, 122, -2.5, 9, 12),
                    'p1.sec2_nom2' => array(154, 122, -2.5, 9, 12),
                    'p1.sec2_genm' => array(186, 122, -2.5, 9, 12), //
                    'p1.sec2_genf' => array(178, 122, -2.5, 9, 12), //
                    'p1.sec2_ide' => array(29, 134, 0.1, 9, 11),
                    'p1.sec2_fec_exp' => array(77, 134, -2, 8, 11),
                    'p1.sec2_lug_exp' => array(102, 134, -2, 8, 11),
                    'p1.sec2_cc' => array(131.5, 134, -1.8, 9, 1),
                    'p1.sec2_ce' => array(139, 134, -1.8, 9, 1),
                    'p1.sec2_ti' => array(146, 134, -1.8, 9, 1),
                    'p1.sec2_pas' => array(164, 134, -1.8, 9, 1),
                    'p1.sec2_pep' => array(134, 130, -1.8, 9, 3),
                    'p1.sec2_ppt' => array(134, 130, -1.8, 9, 3),
                    'p1.sec2_pais' => array(173, 134, -2, 8, 7),
                    'p1.sec2_ide_trib_pais_ori' => array(29, 144, -1.8, 9, 22),
                    'p1.sec2_pais_ori' => array(82, 144, -1.8, 9, 22),
                    'p1.sec2_ide_tri_soc' => array(136, 144, -1.8, 9, 23),
                    // 'p1.sec3_dom_dir' => array (63, 159, -1.9, 9, 30),
                    'p1.sec3_dom_dir' => array(63, 159, -2.5, 7, 40),
                    'p1.sec3_dom_urb' => array(143.5, 159, 0, 9, 1),
                    'p1.sec3_dom_rur' => array(156.5, 159, 0, 9, 1),
                    'p1.sec3_dom_post' => array(177, 159, -2.5, 8.5, 6),
                    'p1.sec3_dom_loc' => array(69, 164, 0, 9, 1),
                    'p1.sec3_dom_ofi' => array(90, 164, 0, 9, 1),
                    'p1.sec3_dom_loc_ofi' => array(119, 164, 0, 9, 1),
                    'p1.sec3_dom_fab' => array(141, 164, 0, 9, 1),
                    'p1.sec3_dom_viv' => array(162, 164, 0, 9, 1),
                    'p1.sec3_dom_fin' => array(180, 164, 0, 9, 1),
                    'p1.sec3_dom_muni' => array(39, 169.5, -2, 9, 9),
                    'p1.sec3_dom_muni_num' => array(60, 169.5, 0, 9, 3),
                    'p1.sec3_dom_dep' => array(89.5, 169.5, -2, 9, 10),
                    'p1.sec3_dom_dep_num' => array(112, 169.5, 0, 9, 2),
                    'p1.sec3_dom_lbvc' => array(145.5, 169.5, -2, 9, 10),
                    'p1.sec3_dom_pais' => array(171.3, 169.5, -2, 9, 8),
                    'p1.sec3_dom_tel1' => array(34, 179.8, 0.2, 9, 10),
                    'p1.sec3_dom_tel2' => array(88, 179.8, 0.27, 9, 10),
                    'p1.sec3_dom_tel3' => array(142, 179.8, 0.27, 9, 10),
                    'p1.sec3_dom_email' => array(62, 184.5, -2, 9, 63),
                    // 'p1.sec3_not_dir' => array(67, 195, -1.9, 9, 28),
                    'p1.sec3_not_dir' => array(67, 194.5, -2.5, 7, 39),
                    'p1.sec3_not_urb' => array(143.5, 195, 0, 9, 1),
                    'p1.sec3_not_rur' => array(156.5, 195, 0, 9, 1),
                    'p1.sec3_not_post' => array(177, 195, -2.5, 8.5, 6),
                    'p1.sec3_not_muni' => array(39, 200, -2, 9, 9),
                    'p1.sec3_not_muni_num' => array(60, 200, 0, 9, 3),
                    'p1.sec3_not_dep' => array(89.5, 200, -2, 9, 10),
                    'p1.sec3_not_dep_num' => array(112, 200, 0, 9, 2),
                    'p1.sec3_not_lbvc' => array(145.5, 200, -2, 9, 10),
                    'p1.sec3_not_pais' => array(171.3, 200, -2, 9, 8),
                    'p1.sec3_not_tel1' => array(33, 207, 0.2, 9, 10),
                    'p1.sec3_not_tel2' => array(84, 207, 0.27, 9, 10),
                    'p1.sec3_not_tel3' => array(140, 207, 0.2, 9, 10),
                    'p1.sec3_not_email' => array(62, 211, -2, 9, 63),
                    'p1.sec3_sede1' => array(36, 219, -2, 9, 1),
                    'p1.sec3_sede2' => array(54, 219, -2, 9, 1),
                    'p1.sec3_sede3' => array(72, 219, -2, 9, 1),
                    'p1.sec3_sede4' => array(90, 219, -2, 9, 1),
                    'p1.sec3_not_si' => array(172.5, 217.5, -2, 9, 1),
                    'p1.sec3_not_no' => array(184, 217.5, -2, 9, 1),
                    'p1.sec4_ciiu1' => array(36, 239, 0, 9, 4),
                    'p1.sec4_ciiu2' => array(75, 239, 0, 9, 4),
                    'p1.sec4_ciiu3' => array(116, 239, 0.2, 9, 4),
                    'p1.sec4_ciiu4' => array(158, 239, 0.2, 9, 4),
                    'p1.sec4_fec_ini_act1' => array(29.5, 247, 0.2, 9, 8),
                    'p1.sec4_fec_ini_act2' => array(71, 247, 0.2, 9, 8),
                    'p1.sec4_imp' => array(122, 246, 0.2, 9, 1),
                    'p1.sec4_exp' => array(149.5, 246, 0.2, 9, 1),
                    'p1.sec4_usu_adu' => array(182.5, 246, 0.2, 9, 1),
                    'p1.sec4_desc_obj_soc' => array(26, 252.7, -2.5, 6, 700),
                    'p1.sec4_ciiu_may_ing' => array(151, 267, 0.6, 9, 4) //
                    );

                break;

            case '2':
                /*  PERSONAS NATURALES - ANEXO - HOJA 2 */

                //revisado archivo : hoja_2.png

                $p = array(
                    'p2.cod_camara' => array(150, 45, -2, 9, 18),
                    'p2.sec5_act_cor' => array(49.5, 65, -2.7, 7, 22),
                    'p2.sec5_act_no_cor' => array(49.5, 69.5, -2.7, 7, 22),
                    'p2.sec5_act_tot' => array(49.5, 74, -2.7, 7, 22),
                    'p2.sec5_pas_cor' => array(100.5, 64.9, -2.7, 7, 22),
                    'p2.sec5_pas_no_cor' => array(100.5, 69.4, -2.7, 7, 22),
                    'p2.sec5_pas_tot' => array(100.5, 73.9, -2.7, 7, 22),
                    'p2.sec5_pas_net' => array(100.5, 79.2, -2.7, 7, 22),
                    'p2.sec5_pas_pat' => array(100.5, 83.3, -2.7, 7, 22),
                    'p2.sec5_bal_soc' => array(100.5, 88, -2.7, 7, 22),
                    'p2.sec5_ing_act_ord' => array(162, 64.9, -2.7, 7, 22),
                    'p2.sec5_otr_ing' => array(162, 69.4, -2.7, 7, 22),
                    'p2.sec5_cos_ven' => array(162, 73.9, -2.7, 7, 22),
                    'p2.sec5_gas_ope' => array(162, 78.5, -2.7, 7, 22),
                    'p2.sec5_otr_gas' => array(162, 83.3, -2.7, 7, 22),
                    'p2.sec5_gas_imp' => array(162, 88, -2.7, 7, 22),
                    'p2.sec5_uti_ope' => array(162, 92.5, -2.7, 7, 22),
                    'p2.sec5_res_per' => array(162, 97, -2.7, 7, 22),
                    'p2.sec5_grupo_niif' => array(146, 104, 0, 9, 1),
                    'p2.sec5_grupo_niif_des' => array(150, 104, -2.7, 7, 25),
                    'p2.sec5_cap_nac_pub' => array(139.5, 108.5, -1.9, 9, 3),
                    'p2.sec5_cap_nac_pri' => array(172.5, 108.5, -1.9, 9, 3),
                    'p2.sec5_cap_ext_pub' => array(139.5, 111.5, -1.9, 9, 3),
                    'p2.sec5_cap_ext_pri' => array(172.5, 111.5, -1.9, 9, 3),
                    'p2.sec5_cap_par_muj' => array(172.5, 114.5, -1.9, 9, 3), //
                    'p2.sec6_apt_lab_val' => array(29.3, 125.5, -2.5, 7, 10),
                    'p2.sec6_apt_lab_por' => array(48.5, 125.5, -1.9, 7, 3),
                    'p2.sec6_apt_act_val' => array(60.5, 125.5, -2.5, 7, 10),
                    'p2.sec6_apt_act_por' => array(79.5, 125.5, -1.9, 7, 3),
                    'p2.sec6_apt_lab_adi_val' => array(94.3, 125.5, -2.5, 7, 10),
                    'p2.sec6_apt_lab_adi_por' => array(113.5, 125.5, -1.9, 7, 3),
                    'p2.sec6_apt_din_val' => array(131.8, 125.5, -2.5, 7, 10),
                    'p2.sec6_apt_din_por' => array(151, 125.5, -1.9, 7, 3),
                    'p2.sec6_tot_apt_val' => array(163.3, 125.5, -2.5, 7, 10),
                    'p2.sec6_tot_apt_por' => array(182.5, 125.5, -1.9, 7, 3),
                    'p2.sec7_ref_ent_nom1' => array(40, 136, -2.5, 8, 20),
                    'p2.sec7_ref_ent_nom2' => array(40, 140.5, -2.5, 8, 20),
                    'p2.sec7_ref_ent_tel1' => array(85, 136, -2.5, 8, 10),
                    'p2.sec7_ref_ent_tel2' => array(85, 140.5, -2.5, 8, 10),
                    'p2.sec7_ref_com_nom1' => array(121, 136, -2.5, 8, 20),
                    'p2.sec7_ref_com_nom2' => array(121, 140.5, -2.5, 8, 20),
                    'p2.sec7_ref_com_tel1' => array(166, 136, -2.5, 8, 10),
                    'p2.sec7_ref_com_tel2' => array(166, 140.5, -2.5, 8, 10),
                    'p2.sec8_cod_est_per_jud' => array(83, 148, 0, 9, 2),
                    'p2.sec8_otro_est' => array(98, 148, -2.5, 9, 20),
                    'p2.sec8_num_emp' => array(158, 148, 0.2, 9, 5),
                    'p2.sec8_eas_si' => array(87, 157.5, -1.9, 9, 1),
                    'p2.sec8_eas_no' => array(98, 157.5, -1.9, 9, 1),
                    'p2.sec8_eas_num' => array(115, 160, -1.9, 9, 6),
                    'p2.sec8_innov_si' => array(175.8, 157.5, -1.9, 9, 1),
                    'p2.sec8_innov_no' => array(184.5, 157.5, -1.9, 9, 1),
                    'p2.sec8_emp_fam_si' => array(87, 162.5, 0, 9, 1),
                    'p2.sec8_emp_fam_no' => array(98, 162.5, 0, 9, 1),
                    'p2.sec8_por_emp_temp' => array(157.5, 162.5, -2, 9, 6),
                    'p2.sec8_can_muj_dir' => array(91.3, 153, 0.5, 9, 5), //
                    'p2.sec8_can_muj_tot' => array(166, 153, 0.5, 9, 5), //
                    
                    'p2.sec8_emp_soc_si' => array(128.2, 166.5, -1.9, 9, 1),
                    'p2.sec8_emp_soc_no' => array(139, 166.5, -1.9, 9, 1),
                    
                    'p2.sec9_mat1' => array(53, 177, -2.5, 7, 35),
                    'p2.sec9_dir1' => array(53, 181, -2.5, 7, 35),
                    'p2.sec9_bar1' => array(53, 185, -2.5, 7, 35),
                    'p2.sec9_mun1' => array(53, 189, -2.5, 7, 35),
                    'p2.sec9_dep1' => array(53, 193, -2.5, 7, 35),
                    'p2.sec9_pas1' => array(53, 197, -2.5, 7, 35),
                    'p2.sec9_mat2' => array(135, 177, -2.5, 7, 35),
                    'p2.sec9_dir2' => array(135, 181, -2.5, 7, 35),
                    'p2.sec9_bar2' => array(135, 185, -2.5, 7, 35),
                    'p2.sec9_mun2' => array(135, 189, -2.5, 7, 35),
                    'p2.sec9_dep2' => array(135, 193, -2.5, 7, 35),
                    'p2.sec9_pas2' => array(135, 197, -2.5, 7, 35),
                    'p2.sec10_1780_dec_si' => array(55, 218, -2, 9, 1),
                    'p2.sec10_1780_dec_no' => array(72, 218, -2, 9, 1),
                    // 'p2.sec10_1780_renun' => array (119, 219.5, -2, 9, 1),
                    'p2.sec10_1780_cumple_si' => array(132, 218, -2, 9, 1),
                    'p2.sec10_1780_cumple_no' => array(160, 218, -2, 9, 1),
                    'p2.sec11_apor_si' => array(111.5, 229, -2, 9, 1),
                    'p2.sec11_apor_no' => array(128, 229, -2, 9, 1),
                    'p2.sec11_tipo_apt1' => array(90, 235, -2, 9, 1),
                    'p2.sec11_tipo_apt2' => array(120.5, 235, -2, 9, 1),
                    'p2.sec11_tipo_apt3' => array(162.3, 235, -2, 9, 1),
                    'p2.sec11_tipo_apt4' => array(184.6, 235, -2, 9, 1),
                    'p2.firma_nom' => array(21, 256.5, -2, 9, 33),
                    'p2.firma_ide' => array(46.5, 263.5, -2, 9, 15),
                    'p2.firma_cc' => array(84, 264, -2, 9, 1),
                    'p2.firma_ce' => array(91.5, 264, -2, 9, 1),
                    'p2.firma_ti' => array(98.7, 264, -2, 9, 1),
                    'p2.firma_pas' => array(117.2, 264, -2, 9, 1),
                    'p2.firma_pais' => array(127.5, 264, -2, 9, 9),
                    'p2.firma_elec' => array(150, 249, -2, 6, 300),
                    'p2.firma_manuscrita' => array(110, 243, -2, 6, 300)
                );

                break;
            case '3':
                /* ESTABLECIMIENTOS, SUCURSALES Y AGENCIAS - ANEXO 1  */

                //revisado archivo : hoja_3.png

                $p = array(
                    'p3.cod_camara' => array(148, 50, -2, 9, 22),
                    'p3.est' => array(48.3, 55.7, 2, 9, 1),
                    'p3.suc' => array(66, 55.7, 2, 9, 1),
                    'p3.age' => array(82.5, 55.7, 2, 9, 1),
                    'p3.mat' => array(99.8, 55.7, 2, 9, 1),
                    'p3.ren' => array(121, 55.7, 2, 9, 1),
                    'p3.num_mat' => array(152, 55.7, 0.2, 9, 10),
                    'p3.ano_ren' => array(177.2, 59.5, 0.2, 9, 4),
                    'p3.sec1_nom_est' => array(18, 72.2, -2, 9, 87),
                    'p3.sec1_dom_dir' => array(18, 79.2, -2, 9, 50),
                    'p3.sec1_dom_pos' => array(121.5, 78.5, 0.2, 9, 6),
                    'p3.sec1_dom_blvc' => array(147.5, 79.2, -2, 9, 23),
                    'p3.sec1_dom_tel1' => array(19.2, 86, 0.19, 9, 10),
                    'p3.sec1_dom_tel2' => array(77.8, 86, 0.2, 9, 10),
                    'p3.sec1_dom_tel3' => array(138.5, 86, 0.2, 9, 10),
                    'p3.sec1_dom_muni' => array(18, 92.5, -2, 9, 17),
                    'p3.sec1_dom_muni_num' => array(54, 91.5, 0.2, 9, 3),
                    'p3.sec1_dom_dep' => array(66, 92.5, -2, 9, 23),
                    'p3.sec1_dom_dep_num' => array(113.5, 91.5, 0.2, 9, 2),
                    'p3.sec1_dom_email' => array(18, 98, -2, 9, 51),
                    'p3.sec1_dom_loc' => array(144, 92.5, -2, 9, 1),
                    'p3.sec1_dom_loc_ofi' => array(170, 92.5, -2, 9, 1),
                    'p3.sec1_dom_ofi' => array(144, 97, -2, 9, 1),
                    'p3.sec1_dom_fab' => array(170, 97, -2, 9, 1),
                    'p3.sec1_dom_viv' => array(186, 92.5, -2, 9, 1),
                    'p3.sec1_dom_fin' => array(18, 97, -2, 9, 1),
                    'p3.sec1_not_dir' => array(18, 105, -2, 9, 54),
                    'p3.sec1_not_pos' => array(128, 104.5, 0.2, 9, 6),
                    'p3.sec1_not_blvc' => array(153, 105, -2, 9, 20),
                    'p3.sec1_not_muni' => array(18, 110.5, -2, 9, 20),
                    'p3.sec1_not_muni_num' => array(92, 109.8, 0.2, 9, 3),
                    'p3.sec1_not_dep' => array(108, 110.5, -2, 9, 35),
                    'p3.sec1_not_dep_num' => array(185, 109.8, 0.2, 9, 2),
                    'p3.sec1_not_email' => array(18, 117, -2, 9, 88),
                    'p3.sec1_act_vin' => array(97, 121.7, -2, 9, 20),
                    'p3.sec1_num_trab' => array(176.5, 122.5, -2, 9, 7),
                    'p3.sec2_ciiu1' => array(26, 137.5, 0, 9, 4),
                    'p3.sec2_ciiu2' => array(68, 137.5, 0, 9, 4),
                    'p3.sec2_ciiu3' => array(114.7, 137.5, 0.2, 9, 4),
                    'p3.sec2_ciiu4' => array(161.5, 137.5, 0.2, 9, 4),
                    'p3.sec2_desc_act_eco' => array(18.5, 146, -2, 6, 550),
                    'p3.sec3_prop_uni' => array(39.5, 158, -2, 9, 1),
                    'p3.sec3_soc_hec' => array(68, 158, -2, 9, 1),
                    'p3.sec3_copro' => array(90.5, 158, -2, 9, 1),
                    'p3.sec3_loc_pro' => array(168.5, 158, -2, 9, 1),
                    'p3.sec3_loc_aje' => array(182.5, 158, -2, 9, 1),
                    'p3.sec4_prop1_nom' => array(84, 168.2, -2, 9, 55),
                    'p3.sec4_prop1_ide' => array(19, 174.5, 0.2, 9, 11),
                    'p3.sec4_prop1_dv' => array(69, 174.5, 0.2, 9, 1),
                    'p3.sec4_prop1_cc' => array(78.5, 174.5, 0.2, 9, 1),
                    'p3.sec4_prop1_ce' => array(86, 174.5, 0.2, 9, 1),
                    'p3.sec4_prop1_nit' => array(93, 174.5, 0.2, 9, 1),
                    'p3.sec4_prop1_ti' => array(99, 174.5, 0.2, 9, 1),
                    'p3.sec4_prop1_pas' => array(116, 174.5, 0.2, 9, 1),
                    'p3.sec4_prop1_mat' => array(137, 173.5, -2, 8, 10),
                    'p3.sec4_prop1_cam' => array(174.5, 173.5, -2, 9, 9),
                    'p3.sec4_prop1_dir' => array(50, 179, -2, 9, 72),
                    'p3.sec4_prop1_muni' => array(29, 183.5, -2, 9, 25),
                    'p3.sec4_prop1_muni_num' => array(84, 183.5, 0.2, 9, 3),
                    'p3.sec4_prop1_dep' => array(114.6, 183.5, -2, 9, 25),
                    'p3.sec4_prop1_dep_num' => array(184, 183.5, 0.2, 9, 2),
                    'p3.sec4_prop1_tel1' => array(19.2, 190.5, 0.19, 9, 10),
                    'p3.sec4_prop1_tel2' => array(77.8, 190.5, 0.2, 9, 10),
                    'p3.sec4_prop1_tel3' => array(138.5, 190.5, 0.2, 9, 10),
                    'p3.sec4_prop1_not_dir' => array(57, 195.5, -2.5, 9, 30),
                    'p3.sec4_prop1_not_mun' => array(120, 195.5, -2.5, 8, 10),
                    'p3.sec4_prop1_not_mun_num' => array(138, 195.5, 0.2, 9, 3),
                    'p3.sec4_prop1_not_dep' => array(168, 195.5, -2.5, 8, 10),
                    'p3.sec4_prop1_not_dep_num' => array(186, 195.5, 0.2, 9, 2),
                    'p3.sec4_prop1_repleg_nom' => array(72.5, 199.5, -2, 9, 61),
                    'p3.sec4_prop1_repleg_cc' => array(52.3, 204, -2, 9, 1),
                    'p3.sec4_prop1_repleg_ce' => array(63.3, 204, -2, 9, 1),
                    'p3.sec4_prop1_repleg_ti' => array(73.5, 204, -2, 9, 1),
                    'p3.sec4_prop1_repleg_pas' => array(93, 204, -2, 9, 1),
                    'p3.sec4_prop1_repleg_ide' => array(103, 204, 0.2, 9, 11),
                    'p3.sec4_prop1_repleg_pais' => array(161.7, 204, -2, 9, 15),
                    'p3.sec4_prop2_nom' => array(84.2, 218, -2, 9, 55),
                    'p3.sec4_prop2_ide' => array(19, 225, 0.2, 9, 11),
                    'p3.sec4_prop2_dv' => array(69, 225, 0.2, 9, 1),
                    'p3.sec4_prop2_cc' => array(78.5, 225, 0.2, 9, 1),
                    'p3.sec4_prop2_ce' => array(86, 225, 0.2, 9, 1),
                    'p3.sec4_prop2_nit' => array(93, 225, 0.2, 9, 1),
                    'p3.sec4_prop2_ti' => array(99, 225, 0.2, 9, 1),
                    'p3.sec4_prop2_pas' => array(116, 225, 0.2, 9, 1),
                    'p3.sec4_prop2_mat' => array(137, 224, -2, 8, 10),
                    'p3.sec4_prop2_cam' => array(174.5, 224, -2, 9, 9),
                    'p3.sec4_prop2_dir' => array(50, 229.5, -2, 9, 72),
                    'p3.sec4_prop2_muni' => array(29, 234, -2, 9, 25),
                    'p3.sec4_prop2_muni_num' => array(84, 234, 0.2, 9, 3),
                    'p3.sec4_prop2_dep' => array(114.6, 234, -2, 9, 25),
                    'p3.sec4_prop2_dep_num' => array(184, 234, 0.2, 9, 2),
                    'p3.sec4_prop2_tel1' => array(19.2, 240.5, 0.19, 9, 10),
                    'p3.sec4_prop2_tel2' => array(77.8, 240.5, 0.2, 9, 10),
                    'p3.sec4_prop2_tel3' => array(138.8, 240.5, 0.2, 9, 10),
                    'p3.sec4_prop2_not_dir' => array(57, 245.5, -2.5, 9, 30),
                    'p3.sec4_prop2_not_mun' => array(120, 245.5, -2, 9, 7),
                    'p3.sec4_prop2_not_mun_num' => array(138, 245.5, 0.2, 9, 3),
                    'p3.sec4_prop2_not_dep' => array(168, 245.5, -2, 9, 5),
                    'p3.sec4_prop2_not_dep_num' => array(186, 245.5, 0.2, 9, 2),
                    'p3.sec4_prop2_repleg_nom' => array(73, 250, -2, 9, 60),
                    'p3.sec4_prop2_repleg_cc' => array(52.3, 254.5, -2, 9, 1),
                    'p3.sec4_prop2_repleg_ce' => array(63.3, 254.5, -2, 9, 1),
                    'p3.sec4_prop2_repleg_ti' => array(73.5, 254.5, -2, 9, 1),
                    'p3.sec4_prop2_repleg_pas' => array(93, 254.5, -2, 9, 1),
                    'p3.sec4_prop2_repleg_ide' => array(103, 254.5, 0.2, 9, 11),
                    'p3.sec4_prop2_repleg_pais' => array(161.5, 254.5, -2, 9, 15),
                    'p3.firma_elec' => array(101, 268, -2, 6, 219),
                    // 'p3.firma_manuscrita' => array(30, 257, -2, 6, 219),
                    'p3.firma_manuscrita' => array(160, 255, -2, 6, 219),
                    'p3.firma_manuscrita_des1' => array(55, 261, -2.5, 6, 150),
                    'p3.firma_manuscrita_des2' => array(55, 263, -2.5, 6, 150)
                );

                break;

            case '4' :
                /* AÑOS ANTERIORES PERSONAS NATURALES Y JURIDICAS - ANEXO 3 */

                //revisado archivo : hoja_4.png

                $p = array(
                    'p4.cod_camara' => array(150, 48, -2, 9, 18),
                    'p4.nit' => array(26, 57.5, - 2, 9, 1),
                    'p4.nit_num' => array(38.5, 57.5, -0.4, 9, 11),
                    'p4.nit_dv' => array(88.5, 57.5, -2, 9, 1),
                    'p4.mat' => array(120, 57.5, -2, 9, 34),
                    'p4.raz_soc' => array(19, 66.5, -2, 9, 85),
                    'p4.ape1' => array(42, 74, -2, 9, 23),
                    'p4.ape2' => array(90, 74, -2, 9, 23),
                    'p4.nom' => array(140, 74, -2, 9, 23),
                    'p4.f1_ano' => array(46, 91.3, -0.25, 9, 4),
                    'p4.f1_act_cor' => array(47.2, 95.3, -2.5, 7, 17),
                    'p4.f1_act_no_cor' => array(47.2, 100.3, -2.5, 7, 17),
                    'p4.f1_act_tot' => array(47.2, 105.3, -2.5, 7, 17),
                    'p4.f1_pas_cor' => array(103, 90, -2.5, 7, 17),
                    'p4.f1_pas_no_cor' => array(103, 94.3, -2.5, 7, 17),
                    'p4.f1_pas_tot' => array(103, 98.6, -2.5, 7, 17),
                    'p4.f1_pas_net' => array(103, 103.3, -2.5, 7, 17),
                    'p4.f1_pas_pat' => array(103, 107.5, -2.5, 7, 17),
                    'p4.f1_bal_soc' => array(103, 112, -2.5, 7, 17),
                    //
                    'p4.f1_ing_act_ord' => array(163, 89.5, -2.5, 7, 17),
                    'p4.f1_otr_ing' => array(163, 93, -2.5, 7, 17),
                    'p4.f1_cos_ven' => array(163, 97, -2.5, 7, 17),
                    'p4.f1_gas_ope' => array(163, 100.5, -2.5, 7, 17),
                    'p4.f1_otr_gas' => array(163, 104, -2.5, 7, 17),
                    'p4.f1_gas_imp' => array(163, 107.5, -2.5, 7, 17),
                    'p4.f1_uti_ope' => array(163, 111, -2.5, 7, 17),
                    'p4.f1_res_per' => array(163, 114.6, -2.5, 7, 17),
                    //
                    'p4.f2_ano' => array(46, 132.3, -0.25, 9, 4),
                    'p4.f2_act_cor' => array(47.2, 136.3, -2.5, 7, 17),
                    'p4.f2_act_no_cor' => array(47.2, 141.3, -2.5, 7, 17),
                    'p4.f2_act_tot' => array(47.2, 146.3, -2.5, 7, 17),
                    'p4.f2_pas_cor' => array(103, 130.6, -2.5, 7, 17),
                    'p4.f2_pas_no_cor' => array(103, 135, -2.5, 7, 17),
                    'p4.f2_pas_tot' => array(103, 139, -2.5, 7, 17),
                    'p4.f2_pas_net' => array(103, 144, -2.5, 7, 17),
                    'p4.f2_pas_pat' => array(103, 148.2, -2.5, 7, 17),
                    'p4.f2_bal_soc' => array(103, 153, -2.5, 7, 17),
                    //
                    'p4.f2_ing_act_ord' => array(163, 130.2, -2.5, 7, 17),
                    'p4.f2_otr_ing' => array(163, 133.5, -2.5, 7, 17),
                    'p4.f2_cos_ven' => array(163, 137.5, -2.5, 7, 17),
                    'p4.f2_gas_ope' => array(163, 141, -2.5, 7, 17),
                    'p4.f2_otr_gas' => array(163, 145, -2.5, 7, 17),
                    'p4.f2_gas_imp' => array(163, 148.5, -2.5, 7, 17),
                    'p4.f2_uti_ope' => array(163, 152, -2.5, 7, 17),
                    'p4.f2_res_per' => array(163, 155.5, -2.5, 7, 17),
                    //
                    'p4.f3_ano' => array(46, 173.3, -0.25, 9, 4),
                    'p4.f3_act_cor' => array(47.2, 177, -2.5, 7, 17),
                    'p4.f3_act_no_cor' => array(47.2, 182, -2.5, 7, 17),
                    'p4.f3_act_tot' => array(47.2, 187, -2.5, 7, 17),
                    'p4.f3_pas_cor' => array(103, 171.6, -2.5, 7, 17),
                    'p4.f3_pas_no_cor' => array(103, 176, -2.5, 7, 17),
                    'p4.f3_pas_tot' => array(103, 180, -2.5, 7, 17),
                    'p4.f3_pas_net' => array(103, 184.6, -2.5, 7, 17),
                    'p4.f3_pas_pat' => array(103, 189, -2.5, 7, 17),
                    'p4.f3_bal_soc' => array(103, 193.6, -2.5, 7, 17),
                    //
                    'p4.f3_ing_act_ord' => array(163, 171, -2.5, 7, 17),
                    'p4.f3_otr_ing' => array(163, 174.5, -2.5, 7, 17),
                    'p4.f3_cos_ven' => array(163, 178, -2.5, 7, 17),
                    'p4.f3_gas_ope' => array(163, 182, -2.5, 7, 17),
                    'p4.f3_otr_gas' => array(163, 185.5, -2.5, 7, 17),
                    'p4.f3_gas_imp' => array(163, 189, -2.5, 7, 17),
                    'p4.f3_uti_ope' => array(163, 192, -2.5, 7, 17),
                    'p4.f3_res_per' => array(163, 196, -2.5, 7, 17),
                    //
                    'p4.f4_ano' => array(46, 214, -0.25, 9, 4),
                    'p4.f4_act_cor' => array(47.2, 217.7, -2.5, 7, 17),
                    'p4.f4_act_no_cor' => array(47.2, 222.6, -2.5, 7, 17),
                    'p4.f4_act_tot' => array(47.2, 227.6, -2.5, 7, 17),
                    'p4.f4_pas_cor' => array(103, 212.2, -2.5, 7, 17),
                    'p4.f4_pas_no_cor' => array(103, 216.6, -2.5, 7, 17),
                    'p4.f4_pas_tot' => array(103, 220.6, -2.5, 7, 17),
                    'p4.f4_pas_net' => array(103, 225.2, -2.5, 7, 17),
                    'p4.f4_pas_pat' => array(103, 229.7, -2.5, 7, 17),
                    'p4.f4_bal_soc' => array(103, 234.2, -2.5, 7, 17),
                    //
                    'p4.f4_ing_act_ord' => array(163, 211.6, -2.5, 7, 17),
                    'p4.f4_otr_ing' => array(163, 215, -2.5, 7, 17),
                    'p4.f4_cos_ven' => array(163, 219, -2.5, 7, 17),
                    'p4.f4_gas_ope' => array(163, 222.5, -2.5, 7, 17),
                    'p4.f4_otr_gas' => array(163, 226, -2.5, 7, 17),
                    'p4.f4_gas_imp' => array(163, 229.5, -2.5, 7, 17),
                    'p4.f4_uti_ope' => array(163, 233.5, -2.5, 7, 17),
                    'p4.f4_res_per' => array(163, 237, -2.5, 7, 17),
                    //
                    'p4.firma_nom' => array(18.5, 257, -2, 9, 32),
                    'p4.firma_ide' => array(41.5, 263.5, -2, 9, 15),
                    'p4.firma_cc' => array(76.6, 264.3, -2, 9, 1),
                    'p4.firma_ce' => array(84.5, 264.3, -2, 9, 1),
                    'p4.firma_ti' => array(91, 264.3, -2, 9, 1),
                    'p4.firma_pas' => array(106.1, 264.3, -2, 9, 1),
                    'p4.firma_pais' => array(115.3, 264.3, -2, 9, 11),
                    'p4.firma_elec' => array(145, 250, -2, 6, 250),
                    'p4.firma_manuscrita' => array(110, 245, -2, 6, 300)
                );
                break;


            case '5' :
                /* AÑOS ANTERIORES - ESTABLECIMIENTOS, SUCURSALES Y AGENCIAS - ANEXO 4 */

                //revisado archivo : hoja_5.png

                $p = array(
                    'p5.cod_camara' => array(150, 57, -2, 9, 18),
                    'p5.est' => array(81.2, 65.5, - 2, 9, 1),
                    'p5.suc' => array(110.2, 65.5, - 2, 9, 1),
                    'p5.age' => array(141.2, 65.5, - 2, 9, 1),
                    'p5.nom_est' => array(18.5, 75.8, - 2, 9, 57),
                    'p5.mat_est' => array(147, 74, - 2, 9, 20),
                    'p5.nom_pro_est' => array(18.8, 84.5, - 2, 9, 57),
                    'p5.nom_pro_est_mat' => array(147, 82.5, - 2, 9, 20),
                    'p5.nit' => array(26, 90.5, 0, 9, 1),
                    'p5.nit_num' => array(38.3, 90.5, -0.36, 9, 11),
                    'p5.nit_dv' => array(89, 90.5, 0, 9, 1),
                    'p5.f1_ano' => array(26.5, 107.3, -0.36, 9, 4),
                    'p5.f1_act' => array(70, 107.3, -2.5, 7, 57),
                    'p5.f2_ano' => array(26.5, 126.3, -0.36, 9, 4),
                    'p5.f2_act' => array(70, 126.6, -2.5, 7, 57),
                    'p5.f3_ano' => array(26.5, 145.7, -0.36, 9, 4),
                    'p5.f3_act' => array(70, 145.7, -2.5, 7, 57),
                    'p5.f4_ano' => array(26.5, 165.2, -0.36, 9, 4),
                    'p5.f4_act' => array(70, 165.2, -2.5, 7, 57),
                    'p5.f5_ano' => array(26.5, 184.7, -0.36, 9, 4),
                    'p5.f5_act' => array(70, 184.7, -2.5, 7, 57),
                    'p5.firma_nom' => array(18.5, 250, -2, 9, 32),
                    'p5.firma_ide' => array(41.5, 256.8, -2, 9, 15),
                    'p5.firma_cc' => array(76.6, 257.7, -2, 9, 1),
                    'p5.firma_ce' => array(84.5, 257.7, -2, 9, 1),
                    'p5.firma_ti' => array(91, 257.7, -2, 9, 1),
                    'p5.firma_pas' => array(106.1, 257.7, -2, 9, 1),
                    'p5.firma_pais' => array(115.3, 257.7, -2, 9, 11),
                    'p5.firma_elec' => array(144, 240, -2, 6, 300),
                    'p5.firma_manuscrita' => array(110, 232, -2, 6, 300)
                );
                break;

            case '6':
                /*  PROPONENTES - ANEXO 2 HOJA 1 */

                //revisado archivo : hoja_6.png

                $p = array(
                    'p6.cod_camara' => array(140, 60, -2, 9, 22),
                    'p6.ins' => array(34.5, 69, 2, 9, 1),
                    'p6.ren' => array(72.8, 69, 2, 9, 1),
                    'p6.act' => array(112.2, 69, 2, 9, 1),
                    'p6.act_tra' => array(179.5, 69, 2, 9, 1),
                    'p6.nit' => array(46.5, 76, 0.2, 9, 11),
                    'p6.dv' => array(101.2, 76, 2, 9, 1),
                    'p6.sec1_tam1' => array(47, 94.5, 2, 9, 1),
                    'p6.sec1_tam2' => array(91.8, 94.5, 2, 9, 1),
                    'p6.sec1_tam3' => array(129, 94.5, 2, 9, 1),
                    'p6.sec1_tam4' => array(170.5, 94.5, 2, 9, 1),
                    //'p6.sec2_inf1' => array (80, 101, -2.5, 7, 70),
                    //'p6.sec2_inf2' => array (90, 108, -2.5, 7, 65),
                    'p6.sec2_ano' => array(70.4, 122, 0.2, 9, 4),
                    'p6.sec2_mes' => array(102, 122, 0.2, 9, 2),
                    'p6.sec2_dia' => array(120, 122, 0.2, 9, 2),
                    'p6.sec2_ind_liq' => array(104.5, 132, -2.5, 7, 27),
                    'p6.sec2_ind_end' => array(104.5, 145, -2.5, 7, 27),
                    'p6.sec2_raz_cob_upo' => array(114, 159, -2.5, 7, 16),
                    'p6.sec2_raz_cob_gas' => array(114, 163, -2.5, 7, 16),
                    'p6.sec2_raz_cob_tot' => array(147, 159, -2.5, 7, 24),
                    'p6.sec3_ren_pat' => array(119.5, 192, -2.5, 7, 26),
                    'p6.sec3_ren_act' => array(119.5, 205, -2.5, 7, 26),
                    'p6.firma_nom' => array(18.5, 244, -2, 9, 38),
                    'p6.firma_ide' => array(46, 252, -2, 9, 26),
                    'p6.firma_cc' => array(103.5, 253, -2, 9, 1),
                    'p6.firma_ce' => array(114.5, 253, -2, 9, 1),
                    'p6.firma_pas' => array(135, 253, -2, 9, 1),
                    'p6.firma_elec' => array(144, 222, -2, 6, 760));
                break;
            case '7':
                /* PROPONENTES - SITUACIONES DE CONTROL - ANEXO 2 HOJA XXX  */

                //revisado archivo : hoja_7.png

                $y = $this->tablaSitControl($item);
                $p = array(
                    'p7.num_hoja' => array(118, 43, -2, 9, 4),
                    'p7.ins' => array(34.5, 62.5, 2, 9, 1),
                    'p7.ren' => array(72.8, 62.5, 2, 9, 1),
                    'p7.act' => array(112.2, 62.5, 2, 9, 1),
                    'p7.act_tra' => array(179.5, 62.5, 2, 9, 1),
                    'p7.nit' => array(46.5, 69.5, 0.2, 9, 11),
                    'p7.dv' => array(101.2, 69.5, 2, 9, 1),
                    'p7.nom' => array(23, $y, -2, 9, 23),
                    'p7.ide' => array(71.2, $y, -2, 9, 17),
                    'p7.dom' => array(106.5, $y, -2, 9, 18),
                    'p7.sit_1' => array(144.5, $y, -2, 9, 5),
                    'p7.sit_2' => array(156, $y, -2, 9, 5),
                    'p7.sit_3' => array(167.8, $y, -2, 9, 5),
                    'p7.sit_4' => array(179.2, $y, -2, 9, 5),
                    'p7.firma_nom' => array(19, 250.8, -2, 9, 35),
                    'p7.firma_ide' => array(46, 260.5, -2, 9, 26),
                    'p7.firma_cc' => array(103.5, 261, -2, 9, 1),
                    'p7.firma_ce' => array(114.5, 261, -2, 9, 1),
                    'p7.firma_pas' => array(135, 261, -2, 9, 1),
                    'p7.firma_elec' => array(143, 240, - 3, 5, 400));
                break;
            case '8' :
                /* PROPONENTES - CLASIFICACION  - ANEXO 2 HOJA XXX */

                //revisado archivo : hoja_8.png

                switch ($this->indice) {
                    case 'p8.cod_seg' :
                    case 'p8.cod_fam' :
                    case 'p8.cod_cla' :
                        //case 'p8.cod_pro' :
                        $x = $this->tabla_Clasif51_X($item);
                        $y = $this->tabla_Clasif51_Y($item);
                        break;
                    case 'p8.cod_seg_elim' :
                    case 'p8.cod_fam_elim' :
                    case 'p8.cod_cla_elim' :
                        //case 'p8.cod_pro_elim' : 
                        $x = $this->tabla_Clasif52_X($item);
                        $y = $this->tabla_Clasif52_Y($item);
                        break;
                }
                $p = array(
                    'p8.num_hoja' => array(118, 43, - 2, 10, 4),
                    'p8.ins' => array(34.5, 51, 2, 9, 1),
                    'p8.ren' => array(72.8, 51, 2, 9, 1),
                    'p8.act' => array(112.2, 51, 2, 9, 1),
                    'p8.tras' => array(180, 51, 2, 9, 1),
                    'p8.cod_seg' => array($x, $y, 2, 8, 2),
                    'p8.cod_fam' => array($x + 13, $y, 2, 8, 2),
                    'p8.cod_cla' => array($x + 25, $y, 2, 8, 2),
                    'p8.cod_seg_elim' => array($x, $y, 2, 8, 2),
                    'p8.cod_fam_elim' => array($x + 13, $y, 2, 8, 2),
                    'p8.cod_cla_elim' => array($x + 25, $y, 2, 8, 2),
                    'p8.folios' => array(146, 220, 0, 9, 3),
                    'p8.firma_nom' => array(20, 251, - 2, 9, 35),
                    'p8.firma_ide' => array(46, 260.5, -2, 9, 26),
                    'p8.firma_cc' => array(103.5, 261, -2, 9, 1),
                    'p8.firma_ce' => array(114.5, 261, -2, 9, 1),
                    'p8.firma_pas' => array(135, 261, -2, 9, 1),
                    'p8.firma_elec' => array(143, 240, - 3, 5, 300));
                break;
            case '9' :
                /* PROPONENTES - SOLO PARA SOCIEDADES EXTRANJERAS - ANEXO 2 HOJA XXX */

                //revisado archivo : hoja_9.png

                $p = array(
                    'p9.num_hoja' => array(118.5, 53, - 2, 10, 4),
                    'p9.ins' => array(34.5, 59.5, 2, 9, 1),
                    'p9.ren' => array(72.8, 59.5, 2, 9, 1),
                    'p9.act' => array(112.2, 59.5, 2, 9, 1),
                    'p9.tras' => array(180, 59.5, 2, 9, 1),
                    'p9.nit' => array(53, 69.5, 0.2, 9, 11),
                    'p9.dv' => array(108, 69.5, 2, 9, 1),
                    'p9.raz_soc' => array(25, 80, - 2, 9, 80),
                    'p9.dur_anio' => array(63, 86, 0, 9, 4),
                    'p9.dur_mes' => array(95, 86, 0, 9, 2),
                    'p9.dur_dia' => array(119, 86, 0, 9, 2),
                    'p9.dur_ind' => array(180, 86, 0, 9, 1),
                    'p9.pj_anio' => array(108, 98, 0, 9, 4),
                    'p9.pj_mes' => array(136, 98, 0, 9, 2),
                    'p9.pj_dia' => array(157, 98, 0, 9, 2),
                    'p9.pj_cla_doc' => array(50, 104, - 2.5, 7, 24),
                    'p9.pj_num_doc' => array(139, 104, - 2.5, 7, 24),
                    'p9.pj_doc_anio' => array(60, 110, 0, 9, 4),
                    'p9.pj_doc_mes' => array(83, 110, 0, 9, 2),
                    'p9.pj_doc_dia' => array(102, 110, 0, 9, 2),
                    'p9.pj_doc_exp' => array(130, 110, - 2.5, 7, 38),
                    'p9.rep_incl' => array(85, 123),
                    'p9.rep_elim' => array(112, 123),
                    'p9.rep_nom' => array(65, 128, - 2.5, 7, 60),
                    'p9.rep_cc' => array(67, 134),
                    'p9.rep_ce' => array(79, 134),
                    'p9.rep_nit' => array(91, 134),
                    'p9.rep_pas' => array(112, 134),
                    'p9.rep_num_ide' => array(132, 134, - 2.5, 7, 24),
                    'p9.facul_incl' => array(85, 146),
                    'p9.facul_modif' => array(114, 146),
                    'p9.facul_elim' => array(147, 146),
                    'p9.facul_detalle' => array(25, 150, - 2, 5.8, 6164),
                    'p9.firma_nom' => array(20, 251, - 2, 9, 35),
                    'p9.firma_ide' => array(46, 260.5, -2, 9, 26),
                    'p9.firma_cc' => array(103.5, 261, -2, 9, 1),
                    'p9.firma_ce' => array(114.5, 261, -2, 9, 1),
                    'p9.firma_pas' => array(135, 261, -2, 9, 1),
                    'p9.firma_elec' => array(143, 240, - 3, 5, 350));


                break;
            case '10' :
                /*  PROPONENTES  - EXPERIENCIA - ANEXO 2 HOJA XXX */

                //revisado archivo : hoja_10.png

                switch ($this->indice) {
                    case 'p10.cod_seg' :
                    case 'p10.cod_fam' :
                    case 'p10.cod_cla' :
                    case 'p10.cod_pro' :
                        $x = $this->tabla_Exp88_X($item);
                        $y = $this->tabla_Exp88_Y($item);
                        break;
                    case 'p10.cod_contr_elim' :
                        $x = $this->tabla_Exp9_X($item);
                        break;
                }
                $p = array(
                    'p10.num_hoja' => array(118, 43, - 2, 10, 4),
                    'p10.ins' => array(34.5, 51, 2, 9, 1),
                    'p10.ren' => array(72.8, 51, 2, 9, 1),
                    'p10.act' => array(112.2, 51, 2, 9, 1),
                    'p10.tras' => array(180, 51, 2, 9, 1),
                    'p10.nit' => array(50, 71, 0.2, 9, 11),
                    'p10.dv' => array(106, 71, 2, 9, 1),
                    'p10.num_cons_rep' => array(115, 79, 1, 8, 4),
                    'p10.exp_prop' => array(45, 92),
                    'p10.exp_acc' => array(137, 98),
                    'p10.exp_cons' => array(139, 105),
                    'p10.nom_contratista' => array(53, 119, - 2, 9, 65),
                    'p10.nom_contratante' => array(54, 126, - 2, 9, 65),
                    'p10.val_cont' => array(83, 133, - 2.5, 8, 40),
                    'p10.porc_part' => array(132, 140, - 2, 9, 5),
                    'p10.cod_seg' => array($x, $y, 2, 8, 2),
                    'p10.cod_fam' => array($x + 13, $y, 2, 8, 2),
                    'p10.cod_cla' => array($x + 25, $y, 2, 8, 2),
                    'p10.cod_contr_elim' => array($x, 207, 1, 8, 3),
                    'p10.continua' => array(165, 198, - 2, 8, 12),
                    'p10.firma_nom' => array(20, 251, - 2, 9, 35),
                    'p10.firma_ide' => array(46, 260.5, -2, 9, 26),
                    'p10.firma_cc' => array(103.5, 261, -2, 9, 1),
                    'p10.firma_ce' => array(114.5, 261, -2, 9, 1),
                    'p10.firma_pas' => array(135, 261, -2, 9, 1),
                    'p10.firma_elec' => array(143, 240, - 3, 5, 350));
                break;

            case '11' :
                /* ENTIDADES DE ECONOMIA SOLIDARIA Y ESADL - ANEXO 5 */

                //revisado archivo : hoja_11.png

                $p = array(
                    'p11.cod_camara' => array(148, 60, -2, 8, 20),
                    'p11.sec1_num_aso' => array(65, 83, 0.2, 9, 5),
                    'p11.sec1_num_muj' => array(116, 83, 0.2, 9, 5),
                    'p11.sec1_num_hom' => array(168, 83, 0.2, 9, 5),
                    'p11.sec1_grem_si' => array(56, 93, -2, 9, 1),
                    'p11.sec1_grem_no' => array(69, 93, -2, 9, 1),
                    'p11.sec1_grem_cual' => array(83, 93, -2.5, 8, 11),
                    'p11.sec1_ent_cur_eco' => array(110, 93, -2.5, 8, 50),
                    'p11.sec1_ent_eje_ivc' => array(26, 103, -2.5, 8, 50),
                    'p11.sec1_aut_reg_si' => array(162, 102, -2.5, 8, 1),
                    'p11.sec1_aut_reg_no' => array(176, 102, -2.5, 8, 1),
                    'p11.sec1_doc_ivc_si' => array(83, 112, -2.5, 8, 1),
                    'p11.sec1_doc_ivc_no' => array(95, 112, -2.5, 8, 1),
                    'p11.sec1_ent_aut' => array(110, 114, -2.5, 8, 50),
                    'p11.sec2_esadl_aso' => array(72, 127, -2.5, 8, 1),
                    'p11.sec2_esadl_cor' => array(72, 133, -2.5, 8, 1),
                    'p11.sec2_esadl_fun' => array(72, 138, -2.5, 8, 1),
                    'p11.sec2_esadl_econ_sol' => array(72, 144, -2.5, 8, 1),
                    'p11.sec2_esadl_otro' => array(36, 149, -2.5, 8, 1),
                    'p11.sec2_esadl_otro_cual' => array(50, 148, -2.7, 6, 20),
                    'p11.sec2_esadl_cod' => array(132.5, 143, -1, 8, 2),
                    'p11.sec3_disc_si' => array(75, 165, -2.5, 8, 1),
                    'p11.sec3_disc_no' => array(85, 165, -2.5, 8, 1),
                    'p11.sec3_lgtbi_si' => array(75, 175, -2.5, 8, 1),
                    'p11.sec3_lgtbi_no' => array(85, 175, -2.5, 8, 1),
                    'p11.sec3_etnia_si' => array(146, 165, -2.5, 8, 1),
                    'p11.sec3_etnia_no' => array(156, 165, -2.5, 8, 1),
                    'p11.sec3_etnia_cual' => array(167, 165.5, -2.5, 8, 14),
                    'p11.sec3_ind_gest_si' => array(151, 175, -2.5, 8, 1),
                    'p11.sec3_ind_gest_no' => array(162, 175, -2.5, 8, 1),
                    'p11.sec3_vict_si' => array(146, 182, -2.5, 8, 1),
                    'p11.sec3_vict_no' => array(156, 182, -2.5, 8, 1),
                    'p11.sec3_vict_cual' => array(167, 182.5, -2.5, 8, 14),
                    'p11.firma_nom' => array(25, 222, -2, 8, 35),
                    'p11.firma_ide' => array(52, 229, -2, 9, 26),
                    'p11.firma_cc' => array(53, 236, -2, 9, 1),
                    'p11.firma_ce' => array(64, 236, -2, 9, 1),
                    'p11.firma_ti' => array(73.5, 236, -2, 9, 1),
                    'p11.firma_pas' => array(93, 236, -2, 9, 1),
                    'p11.firma_pais' => array(105, 236, -2, 9, 11),
                    'p11.firma_elec' => array(143, 200, -2, 6, 760),
                    'p11.firma_manuscrita' => array(110, 200, -2, 6, 300)
                );
                break;
            
            case '12' :
                /* ACTUALIZACION DECRETO 399 DE 2021 */

                //revisado archivo : hoja_12.png

                $p = array(
                    'p12.cod_camara' => array(159, 40, -2, 8, 20),
                    'p12.ins' => array(44, 46.8, 2, 9, 1),
                    'p12.ren' => array(72.8, 46.8, 2, 9, 1),
                    'p12.act' => array(161.2, 46.8, 2, 9, 1),
                    'p12.pro' => array(42, 50.7, 0.6, 9, 11),
                    'p12.nit' => array(30, 65, 0.6, 9, 11),
                    'p12.dv' => array(85, 65, 2, 9, 1),
                    'p12.raz' => array(53, 55, - 2, 9, 47),
                    'p12.sig' => array(155, 55, - 2, 9, 20),
                    'p12.ape1' => array(55, 61, - 2, 9, 20),
                    'p12.ape2' => array(95, 61, - 2, 9, 20),
                    'p12.nom1' => array(134, 61, - 2, 9, 20),
                    'p12.nom2' => array(173, 61, - 2, 9, 20),
                    
                    'p12.1.anocorte' => array(87, 78.3, - 2, 9, 80),
                    'p12.1.mescorte' => array(108, 78.3, - 2, 9, 80),
                    'p12.1.diacorte' => array(122, 78.3, - 2, 9, 80),
                    'p12.1.gruponiif' => array(150, 78.3, - 2, 9, 1),
                    'p12.1.actcte' => array(88, 86, - 2, 8, 80),
                    'p12.1.pascte' => array(88, 91, - 2, 8, 80),
                    'p12.1.indliq' => array(132, 87, - 2, 8, 80),
                    
                    'p12.1.pastot' => array(88, 96, - 2, 8, 80),
                    'p12.1.acttot' => array(88, 101, - 2, 8, 80),
                    'p12.1.nivend' => array(132, 97, - 2, 8, 80),
                    
                    'p12.1.utiope' => array(97, 108, - 2, 8, 80),
                    'p12.1.gasint' => array(97, 113, - 2, 8, 80),
                    'p12.1.razcob' => array(142, 109, - 2, 8, 80),
                    
                    'p12.1.utiope1' => array(98, 132.5, - 2, 8, 80),
                    'p12.1.patnet' => array(98, 137, - 2, 8, 80),
                    'p12.1.renpat' => array(142, 133, - 2, 8, 80),
                    
                    'p12.1.utiope2' => array(98, 142, - 2, 8, 80),
                    'p12.1.acttot1' => array(98, 147, - 2, 8, 80),
                    'p12.1.renact' => array(142, 143, - 2, 8, 80),
                    
                    'p12.2.anocorte' => array(92, 159.5, - 2, 8, 80),
                    'p12.2.mescorte' => array(111, 159.5, - 2, 8, 80),
                    'p12.2.diacorte' => array(123, 159.5, - 2, 8, 80),
                    'p12.2.gruponiif' => array(150, 159.5, - 2, 8, 1),
                    
                    'p12.2.actcte' => array(88, 171, - 2, 8, 80),
                    'p12.2.pascte' => array(88, 175, - 2, 8, 80),
                    'p12.2.indliq' => array(132, 171, - 2, 8, 80),
                    
                    'p12.2.pastot' => array(88, 181, - 2, 8, 80),
                    'p12.2.acttot' => array(88, 185, - 2, 8, 80),
                    'p12.2.nivend' => array(132, 181, - 2, 8, 80),
                    
                    'p12.2.utiope' => array(96, 192, - 2, 8, 80),
                    'p12.2.gasint' => array(96, 197, - 2, 8, 80),
                    'p12.2.razcob' => array(142, 193, - 2, 8, 80),
                    
                    'p12.2.utiope1' => array(96, 220, - 2, 8, 80),
                    'p12.2.patnet' => array(96, 225, - 2, 8, 80),
                    'p12.2.renpat' => array(144, 220, - 2, 8, 80),
                    
                    'p12.2.utiope2' => array(96, 230, - 2, 8, 80),
                    'p12.2.acttot1' => array(96, 235, - 2, 8, 80),
                    'p12.2.renact' => array(144, 230, - 2, 8, 80),
                    
                    'p12.firma_nom' => array(17, 259, -2, 8, 35),
                    'p12.firma_ide' => array(42, 268, -2, 9, 26),
                    'p12.firma_cc' => array(84, 269.5, -2, 9, 1),
                    'p12.firma_ce' => array(101, 269.5, -2, 9, 1),
                    'p12.firma_pas' => array(121, 269.5, -2, 9, 1),
                    'p12.firma_elec' => array(142, 250, -2, 6, 760),
                    'p12.firma_manuscrita' => array(142, 250, -2, 6, 300)
                );
                break;            
        }

        return $p;
    }

    /* Método público que construye arreglo del campo a imprimir en el formulario */

    public function armarCampo($nombreCampo, $contenidoCampo = "", $item = 1) {
        $this->indice = $nombreCampo;

        if ($this->pagina) {

            // OBTENGO EL ARREGLO DEL CAMPO SOLICITADO CON LAS RESPECTIVAS COORDENADAS
            $p = $this->obtenerArregloCampos($this->pagina, $item);

            $f [] = $nombreCampo;
            $f [] = $contenidoCampo;

            foreach ($p [$nombreCampo] as $param) {
                $f [] .= $param;
            }

            /*
              $nombreCampo = isset($arr [0]) ? $arr [0] : '';
              $contenidoRecibido = isset($arr [1]) ? $arr [1] : '';
              $posX = isset($arr [2]) ? $arr [2] : '';
              $posY = isset($arr [3]) ? $arr [3] : '';
              $sepCaracteres = isset($arr [4]) ? $arr [4] : '1';
              $tamLetra = isset($arr [5]) ? $arr [5] : '9';
              $longContenido = isset($arr [6]) ? $arr [6] : '1';
             */
            $this->imprimirTexto($f);

            unset($p);
            unset($f);
            unset($item);
        }
    }

    public function armarCampoImagen($nombreCampo, $contenidoCampo = "", $item = 1) {
        $this->indice = $nombreCampo;

        if ($this->pagina) {

            // OBTENGO EL ARREGLO DEL CAMPO SOLICITADO CON LAS RESPECTIVAS COORDENADAS
            $p = $this->obtenerArregloCampos($this->pagina, $item);

            $f [] = $nombreCampo;
            $f [] = $contenidoCampo;

            foreach ($p [$nombreCampo] as $param) {
                $f [] .= $param;
            }

            /*
              $nombreCampo = isset($arr [0]) ? $arr [0] : '';
              $contenidoRecibido = isset($arr [1]) ? $arr [1] : '';
              $posX = isset($arr [2]) ? $arr [2] : '';
              $posY = isset($arr [3]) ? $arr [3] : '';
              $sepCaracteres = isset($arr [4]) ? $arr [4] : '1';
              $tamLetra = isset($arr [5]) ? $arr [5] : '9';
              $longContenido = isset($arr [6]) ? $arr [6] : '1';
             */
            $this->imprimirTexto($f);

            unset($p);
            unset($f);
            unset($item);
        }
    }

    public function setNumeroRecuperacion($numeroRecuperacion) {
        $this->numeroRecuperacion = $numeroRecuperacion;
    }

    public function getNumeroRecuperacion() {
        if (isset($this->numeroRecuperacion)) {
            return $this->numeroRecuperacion;
        } else {
            return "";
        }
    }

    public function setNumeroLiquidacion($liquidacion) {
        $this->numeroLiquidacion = $liquidacion;
    }

    public function getNumeroLiquidacion() {
        if (isset($this->numeroLiquidacion)) {
            return $this->numeroLiquidacion;
        } else {
            return 0;
        }
    }

    public function setFechaImpresion($fec) {
        $this->fechaImpresion = $fec;
    }

    public function getFechaImpresion() {
        if (isset($this->fechaImpresion)) {
            return $this->fechaImpresion;
        } else {
            return "";
        }
    }

    /* Función que retorna la posición en Y (entre 1 y 21) para la tabla de situaciones de control */

    function tablaSitControl($item) {
        $y = 0;
        switch ($item) {
            case '1' :
                $y = 104;
                break;
            case '2' :
                $y = 110;
                break;
            case '3' :
                $y = 116;
                break;
            case '4' :
                $y = 122;
                break;
            case '5' :
                $y = 128;
                break;
            case '6' :
                $y = 134;
                break;
            case '7' :
                $y = 140;
                break;
            case '8' :
                $y = 145;
                break;
            case '9' :
                $y = 151;
                break;
            case '10' :
                $y = 157;
                break;
            case '11' :
                $y = 163;
                break;
            case '12' :
                $y = 169;
                break;
            case '13' :
                $y = 174;
                break;
            case '14' :
                $y = 180;
                break;
            case '15' :
                $y = 186;
                break;
            case '16' :
                $y = 192;
                break;
            case '17' :
                $y = 198;
                break;
            case '18' :
                $y = 203;
                break;
            case '19' :
                $y = 209;
                break;
            case '20' :
                $y = 215;
                break;
            case '21' :
                $y = 220;
                break;
        }
        return $y;
    }

    /* Función que retorna la posición en X (entre 1 y 3) en la tabla de clasificaciones */

    function tabla_Clasif51_X($item) {
        $x = 0;
        $residuo = ($item % 3);
        switch ($residuo) {
            case 1 :
                $x = 28;
                break;
            case 2 :
                $x = 83;
                break;
            case 0 :
            case 3 :
                $x = 140;
                break;
        }
        return $x;
    }

    function tabla_Clasif51_Y($item) {
        $y = 0;
        switch (TRUE) {
            case ($item < 4) :
                $y = 72;
                break;
            case ($item < 7) :
                $y = 78;
                break;
            case ($item < 10) :
                $y = 84;
                break;
            case ($item < 13) :
                $y = 89;
                break;
            case ($item < 16) :
                $y = 94;
                break;
            case ($item < 19) :
                $y = 100;
                break;
            case ($item < 22) :
                $y = 105;
                break;
            case ($item < 25) :
                $y = 110;
                break;
            case ($item < 28) :
                $y = 116;
                break;
            case ($item < 31) :
                $y = 122;
                break;
            case ($item < 34) :
                $y = 127;
                break;
            case ($item < 37) :
                $y = 133;
                break;
            case ($item < 40) :
                $y = 138;
                break;
            case ($item < 43) :
                $y = 144;
                break;
            case ($item < 46) :
                $y = 149;
                break;
            case ($item < 49) :
                $y = 154;
                break;
            case ($item < 52) :
                $y = 160;
                break;
            case ($item < 55) :
                $y = 165;
                break;
            case ($item < 58) :
                $y = 170;
                break;
            case ($item < 61) :
                $y = 176;
                break;
        }
        return $y;
    }

    /* Función que retorna la posici&oacute;n en X (entre 1 y 3) en la tabla de clasificaciones a eliminar */

    function tabla_Clasif52_X($item) {
        $x = 0;
        $residuo = ($item % 3);
        switch ($residuo) {
            case 1 :
                $x = 28;
                break;
            case 2 :
                $x = 83;
                break;
            case 0 :
            case 3 :
                $x = 140;
                break;
        }
        return $x;
    }

    function tabla_Clasif52_Y($item) {
        $y = 0;
        switch (TRUE) {
            case ($item < 4) :
                $y = 193;
                break;
            case ($item < 7) :
                $y = 199;
                break;
            case ($item < 10) :
                $y = 204;
                break;
        }
        return $y;
    }

    /* Función que retorna la posición en X (entre 1 y 3) en la tabla de experiencia */

    function tabla_Exp88_X($item) {
        $x = 0;
        $residuo = ($item % 3);
        switch ($residuo) {
            case 1 :
                $x = 27;
                break;
            case 2 :
                $x = 83;
                break;
            case 0 :
            case 3 :
                $x = 140;
                break;
        }
        return $x;
    }

    function tabla_Exp88_Y($item) {
        $y = 0;
        switch (TRUE) {
            case ($item < 4) :
                $y = 161;
                break;
            case ($item < 7) :
                $y = 166;
                break;
            case ($item < 10) :
                $y = 172;
                break;
            case ($item < 13) :
                $y = 177;
                break;
            case ($item < 16) :
                $y = 182;
                break;
            case ($item < 19) :
                $y = 188;
                break;
        }
        return $y;
    }

    /* Función que retorna la posición en X (entre 1 y 9) en la tabla de experiencia a eliminar */

    function tabla_Exp9_X($item) {
        $x = 0;
        switch ($item) {
            case 1 :
                $x = 36;
                break;
            case 2 :
                $x = 52;
                break;
            case 3 :
                $x = 68;
                break;
            case 4 :
                $x = 84.5;
                break;
            case 5 :
                $x = 100.5;
                break;
            case 6 :
                $x = 117;
                break;
            case 7 :
                $x = 133;
                break;
            case 8 :
                $x = 149.5;
                break;
            case 9 :
                $x = 165.5;
                break;
        }
        return $x;
    }

}

?>