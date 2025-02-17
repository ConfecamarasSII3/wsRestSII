<?php

/**
 * Clase para permite imprimir los campos en sus posiciones respectivas del formulario Rues y sus anexos
 *
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2016/12/05
 * facu
 */
require_once '../components/fpdf186/fpdf.php';

class formularioRues2016 extends FPDF {

    // Declaraci&oacute;n como variables privadas de las rutas de los formularios creados en formato png
    private $imagen1 = '../formatos/2016/fm_rues2016_1.png';
    private $imagen2 = '../formatos/2016/fm_rues2016_2.png';
    private $imagen3 = '../formatos/2016/fm_rues2016_3.png';
    private $imagen4 = '../formatos/2016/fm_rues2016_4.png';
    private $imagen5 = '../formatos/2016/fm_rues2016_5.png';
    private $imagen6 = '../formatos/2016/fm_rues2016_6.png';
    private $imagen7 = '../formatos/2016/fm_rues2016_7.png';
    private $imagen8 = '../formatos/2016/fm_rues2016_8.png';
    private $imagen9 = '../formatos/2016/fm_rues2016_9.png';
    private $imagen10 = '../formatos/2016/fm_rues2016_10.png';
    private $logo = "";
    private $empresa = "";
    private $pagina = "";
    private $indice = "";
    private $numeroRecuperacion = "";
    private $numeroLiquidacion = "";
    private $fechaImpresion = "";

    function Footer() {
        $this->SetY(- 20);
        $this->SetFont('Courier', 'B', 8);

        if ($this->PageNo() >= 1) {
            if ($this->getNumeroLiquidacion() != '') {
                $this->Cell(0, 10, "Nro. Liq. " . $this->getNumeroLiquidacion(), 0, 0, 'R');
            }
            $this->SetY(- 17);
            //$this->Cell ( 0, 10, "Fecha: " . date ( 'Y/m/d H:i:s' ), 0, 0, 'R' );
            //if ($this->getFechaImpresion() != '') {
            $this->Cell(0, 10, "Fecha: " . $this->getFechaImpresion(), 0, 0, 'R');
            //}
        }
    }

    /* Función que realiza rotación del texto */

    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == - 1)
            $x = $this->x;
        if ($y == - 1)
            $y = $this->y;
        //$this->angle = $angle;
        //     if (!isset($this->angle))
        //         $this->angle = $angle;
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

    /* Funci&oacute;n que imprime texto rotado */

    function RotatedText($x, $y, $txt, $angle = 0) {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    /* Funci&oacute;n cambia el tamaño de la fuente */

    private function tamFuente($_val, $fuente = 'Courier', $negrita = 'b') {
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
        $nombreCampo = isset($arr [0]) ? $arr [0] : '';
        $contenidoRecibido = isset($arr [1]) ? $arr [1] : '';
        $posX = isset($arr [2]) ? $arr [2] : '';
        $posY = isset($arr [3]) ? $arr [3] : '';
        $sepCaracteres = isset($arr [4]) ? $arr [4] : '1';
        $tamLetra = isset($arr [5]) ? $arr [5] : '9';
        $longContenido = isset($arr [6]) ? $arr [6] : '1';
        if (($posX != 0) and ( $posY != 0)) {
            switch ($nombreCampo) {

                //WSI 2016-12-05 - VARIABLES INCLUIDAS PARA FORMULARIO CON NIFF
                case 'p1.actcte':
                case 'p1.actnocte':
                case 'p1.acttot':
                case 'p1.pascte':
                case 'p1.paslar':
                case 'p1.pastot':
                case 'p1.pattot':
                case 'p1.paspat':
                case 'p1.balsoc':
                case 'p1.ingope':
                case 'p1.ingnoope':
                case 'p1.cosven':
                case 'p1.gasope':
                case 'p1.gasnoope':
                case 'p1.gasimp':
                case 'p1.utiope':
                case 'p1.utinet':
                    $this->tamFuente($tamLetra, 'Arial');
                    break;
                default:
                    $this->tamFuente($tamLetra);
                    break;
            }


            if ($nombreCampo == 'p9.facul_detalle') {

                $contenidoRetornado = substr($contenidoRecibido, 0, 7200);
                $this->SetXY($posX, $posY);
                $this->SetX($posX);
                $this->tamFuente($tamLetra, 'Courier', '');
                $this->MultiCell(173, 1.6, $contenidoRetornado, 0, 'J', 0, 1);
            } else {

                switch ($nombreCampo) {
                    case 'p2.firma_elec':
                    case 'p3.firma_elec':
                    case 'p4.firma_elec':
                    case 'p5.firma_elec':
                        $contenidoRetornado = substr($contenidoRecibido, 0, 400);
                        $this->SetXY($posX, $posY);
                        $this->SetX($posX);
                        $this->MultiCell(50, 1.5, $contenidoRetornado, 0, 'C', 0, 1);
                        break;
                    case 'p6.firma_elec':
                    case 'p7.firma_elec':
                    case 'p8.firma_elec':
                    case 'p9.firma_elec':
                    case 'p10.firma_elec':
                        $contenidoRetornado = substr($contenidoRecibido, 0, 400);
                        $this->SetXY($posX, $posY);
                        $this->SetX($posX);
                        $this->MultiCell(48, 1.5, $contenidoRetornado, 0, 'C', 0, 1);
                        break;

                    default:

                        /*
                          switch ($nombreCampo) {
                          case 'p7.num_hoja':
                          case 'p8.num_hoja':
                          case 'p9.num_hoja':
                          case 'p10.num_hoja':
                          $contenidoRetornado = str_pad($contenidoRetornado, 3, "0", STR_PAD_LEFT);
                          break;
                          default:
                          break;
                          }
                         */
                        $contenidoRetornado = substr($contenidoRecibido, 0, $longContenido);
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
    }

    /* Función imprime guias de página */

    private function lineas() {
        for ($x = 1; $x < 277; $x ++) {
            $x ++;
            $this->tamFuente(6);

            if ($x > 10) {
                $this->Line(7, $x, 200, $x);
            }
            $this->SetXY(3, $x);
            $this->Cell(0, 0, $x);
        }

        for ($x = 1; $x < 205; $x ++) {
            $x ++;
            $this->tamFuente(4);

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
            $this->Image($this->$nomVariablePNG, 10, 10, 190, 269);
            $this->empresa = $_SESSION ['generales'] ['codigoempresa'];

            $this->logo = "../../images/logocamara" . $_SESSION ['generales'] ['codigoempresa'] . ".jpg";

            if (file_exists($this->logo)) {
                switch ($this->pagina) {
                    case 1 :
                        $this->Image($this->logo, 22, 10, 15, 15);
                        break;
                    case 2 :
                        $this->Image($this->logo, 22, 10, 15, 15);
                        break;
                    case 3 :
                        $this->Image($this->logo, 24, 10, 15, 15);
                        break;
                    case 4 :
                    case 5 :
                        $this->Image($this->logo, 22, 12, 15, 15);
                        break;
                    case 6 :
                    case 7 :
                    case 8 :
                    case 9 :
                    case 10 :
                        $this->Image($this->logo, 15, 18, 15, 15);
                        break;
                }
            }

            // $this->SetDrawColor(280);
            //$this->lineas ();

            $this->SetFont('Courier', 'B', 14);
            $this->SetXY(167, 0);
            $this->Cell(0, 65, "" . $this->getNumeroRecuperacion());
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
                /*  Formulario Rues - Personas Hoja 1  */

                $p = array (
                    'p1.cod_camara' => array (68, 40, 2, 9, 2),
                    'p1.dia' => array (128, 40, 2, 9, 2),
                    'p1.mes' => array (147.5, 40, 2, 9, 2),
                    'p1.anio' => array (166, 40, 1, 9, 4),
                    'p1.rm_mat' => array (72, 56),
                    'p1.rm_ren' => array (72, 60),
                    'p1.rm_tras' => array (72, 64),
                    'p1.rm_num_mat' => array (48, 75.5, - 2, 9, 12),
                    'p1.rm_anio_ren' => array (49, 81, 1, 9, 4),
                    'p1.esal_ins' => array (127, 56),
                    'p1.esal_ren' => array (127, 60),
                    'p1.esal_tras' => array (127, 64),
                    'p1.esal_num_ins' => array (103, 75.5, - 2, 9, 12),
                    'p1.esal_anio_ren' => array (104, 81, 1, 9, 4),
                    'p1.rup_ins' => array (181, 56),
                    'p1.rup_act' => array (181, 60),
                    'p1.rup_ren' => array (181, 64),
                    'p1.rup_act_tras' => array (181, 69),
                    'p1.rup_cam_ant' => array (135, 73.5, - 2, 9, 20),
                    'p1.rup_can' => array (181, 77.5),
                    'p1.rup_num_ins' => array (158, 82, - 2, 9, 12),
                    'p1.raz_soc' => array (25, 91, - 2, 9, 80),
                    'p1.sig' => array (25, 96, - 2, 9, 80),
                    'p1.ape1' => array (25, 101, - 2, 9, 26),
                    'p1.ape2' => array (79, 101, - 2, 9, 23),
                    'p1.nom' => array (128, 101, - 2, 9, 26),
                    'p1.ide' => array (46, 106, 1, 9, 11),
                    'p1.cc' => array (113, 106.5),
                    'p1.ce' => array (123, 106.5),
                    'p1.ti' => array (132, 106.5),
                    'p1.pas' => array (149, 106.5),
                    'p1.pais' => array (159, 106.5, - 2, 9, 12),
                    'p1.nit' => array (38.5, 111.5, 1, 9, 10),
                    'p1.dv' => array (95.5, 111.5),
                    'p1.dom_dir' => array (25, 121, - 2, 9, 80),
                    'p1.dom_mun' => array (25, 126, - 2, 9, 20),
                    'p1.dom_cod_mun' => array (69, 125.5, 1, 9, 3),
                    'p1.dom_dep' => array (83, 126, - 2, 9, 19),
                    'p1.dom_cod_dep' => array (124, 125.5, 1, 9, 2),
                    'p1.dom_pais' => array (133, 126, - 2, 9, 10),
                    'p1.dom_bar' => array (156, 126, - 2, 9, 14),
                    'p1.dom_tel1' => array (40, 132, - 0.2, 7, 10),
                    'p1.dom_tel2' => array (89.5, 132, - 0.2, 7, 10),
                    'p1.dom_tel3' => array (139, 132, - 0.2, 7, 10),
                    'p1.dom_email' => array (25, 137, - 2, 9, 53),
                    'p1.dom_fax' => array (133, 137.5, - 2, 9, 25),
                    'p1.not_dir' => array (25, 143, - 2, 9, 80),
                    'p1.not_mun' => array (25, 148, - 2, 9, 20),
                    'p1.not_cod_mun' => array (69, 147.5, 1, 9, 3),
                    'p1.not_dep' => array (83, 148, - 2, 9, 19),
                    'p1.not_cod_dep' => array (124, 147.5, 1, 9, 2),
                    'p1.not_pais' => array (133, 148, - 2, 9, 10),
                    'p1.not_bar' => array (156, 148, - 2, 9, 14),
                    'p1.not_tel1' => array (40, 154, - 0.2, 7, 10),
                    'p1.not_tel2' => array (89.5, 154, - 0.2, 7, 10),
                    'p1.not_tel3' => array (139, 154, - 0.2, 7, 10),
                    'p1.not_email' => array (25, 159, - 2, 9, 53),
                    'p1.not_fax' => array (133, 159, - 2, 9, 20),
                    'p1.not_mail_si' => array (100, 166),
                    'p1.not_mail_no' => array (110, 166),
                    'p1.not_cel_si' => array (170, 166),
                    'p1.not_cel_no' => array (180, 166),
                    'p1.loc' => array (65, 171),
                    'p1.ofi' => array (82, 171),
                    'p1.loc_ofi' => array (105, 171),
                    'p1.fab' => array (122, 171),
                    'p1.viv' => array (139.5, 171),
                    'p1.fin' => array (155.5, 171),
                    'p1.ciiu1' => array (35, 185, 0, 9, 4),
                    'p1.ciiu2' => array (75.5, 185, 0, 9, 4),
                    'p1.ciiu3' => array (115, 185, 0, 9, 4),
                    'p1.ciiu4' => array (154, 185, 0, 9, 4),
                    //WSI 2016-12-05 - POSICIONAMIENTO EN FORMULARIO VARIABLES NIFF
                    'p1.actcte' => array (49.5, 203, -2.9, 6, 30),
                    'p1.actnocte' => array (49.5, 208.5, -2.9, 6, 30),
                    'p1.acttot' => array (49.5, 213.5, -2.9, 6, 30),
                    'p1.pascte' => array (99.5, 202.5, -2.9, 6, 30),
                    'p1.paslar' => array (99.5, 206.5, -2.9, 6, 30),
                    'p1.pastot' => array (99.5, 211, -2.9, 6, 30),
                    'p1.pattot' => array (99.5, 215, -2.9, 6, 30),
                    'p1.paspat' => array (99.5, 219, -2.9, 6, 30),
                    'p1.balsoc' => array (99.5, 224.5, -2.9, 6, 30),
                    'p1.ingope' => array (155.5, 202, -2.9, 6, 30),
                    'p1.ingnoope' => array (155.5, 205, -2.9, 6, 30),
                    'p1.cosven' => array (155.5, 208, -2.9, 6, 30),
                    'p1.gasope' => array (155.5, 211, -2.9, 6, 30),
                    'p1.gasnoope' => array (155.5, 214.5, -2.9, 6, 30),
                    'p1.gasimp' => array (155.5, 218, -2.9, 6, 30),
                    'p1.utiope' => array (155.5, 221.5, -2.9, 6, 30),
                    'p1.utinet' => array (155.5, 225.5, -2.9, 6, 30),
                    //
                    'p1.impo' => array (57, 231),
                    'p1.expo' => array (73, 231),
                    'p1.num_trab' => array (95, 231.5, - 2, 8, 5),
                    'p1.por_trab' => array (170, 231.5, - 2, 8, 5),
                    'p1.apor_lab1' => array (29, 239, - 3, 5, 20),
                    'p1.apor_lab2' => array (29, 242, - 3, 5, 20),
                    'p1.apor_act1' => array (59, 239, - 3, 5, 20),
                    'p1.apor_act2' => array (59, 242, - 3, 5, 20),
                    'p1.apor_adic1' => array (92, 239, - 3, 5, 20),
                    'p1.apor_adic2' => array (92, 242, - 3, 5, 20),
                    'p1.apor_din1' => array (128, 239, - 3, 5, 20),
                    'p1.apor_din2' => array (128, 242, - 3, 5, 20),
                    'p1.apor_tot' => array (157.5, 242, - 3, 5, 20),
                    'p1.anio_cons' => array (28.5, 249.5, 0, 9, 4),
                    'p1.mes_cons' => array (44.5, 249.5, 0, 9, 2),
                    'p1.dia_cons' => array (53, 249.5, 0, 9, 2),
                    'p1.anio_fin' => array (62.5, 249.5, 0, 9, 4),
                    'p1.mes_fin' => array (78, 249.5, 0, 9, 2),
                    'p1.dia_fin' => array (87, 249.5, 0, 9, 2),
                    'p1.cap_nal' => array (109, 251, - 2, 8, 4),
                    'p1.cap_nal_pub' => array (135, 249, - 2, 8, 4),
                    'p1.cap_nal_pri' => array (135, 252, - 2, 8, 4),
                    'p1.cap_ext' => array (149, 251, - 2, 8, 4),
                    'p1.cap_ext_pub' => array (173, 249, - 2, 8, 4),
                    'p1.cap_ext_pri' => array (173, 252, - 2, 8, 4),
                    'p1.est_01' => array (46, 260),
                    'p1.est_02' => array (89, 260),
                    'p1.est_03' => array (119, 260),
                    'p1.est_04' => array (146, 260),
                    'p1.est_05' => array (46, 264),
                    'p1.est_06' => array (89, 264),
                    'p1.est_07' => array (119, 264),
                    'p1.est_cual' => array (137, 264, - 2, 8, 20),
                    'p1.jov_si' => array (110, 268.5),
                    'p1.jov_no' => array (125, 268.5),
                    'p1.jov_par' => array (125, 272, - 2, 8, 4));
                break;

            case '2':
                /*  Formulario Rues _personas hoja 2 */
                $p = array (
                    'p2.org_01' => array (49.5, 43.5),
                    'p2.org_02' => array (98.5, 43.5),
                    'p2.org_03' => array (140.5, 43.5),
                    'p2.org_04' => array (179, 43.5),
                    'p2.org_05' => array (49.5, 48.5),
                    'p2.org_06' => array (98.5, 48.5),
                    'p2.org_07' => array (140.5, 48.5),
                    'p2.org_08' => array (179, 48.5),
                    'p2.org_09' => array (49.5, 54.5),
                    'p2.org_10' => array (98.5, 54.5),
                    'p2.org_11' => array (140.5, 54.5),
                    'p2.org_12' => array (49.5, 68),
                    'p2.org_12.1' => array (98.5, 61),
                    'p2.org_12.2' => array (140.5, 61),
                    'p2.org_12.3' => array (179, 61),
                    'p2.org_12.4' => array (98.5, 66),
                    'p2.org_12.5' => array (140.5, 66),
                    'p2.org_12.6' => array (179, 66),
                    'p2.org_12.7' => array (98.5, 75),
                    'p2.org_12.8' => array (140.5, 75),
                    'p2.org_12.9' => array (179, 75),
                    'p2.org_12.10' => array (98.5, 80),
                    'p2.org_veed' => array (140.5, 80),
                    'p2.org_ent_ext' => array (179, 80),
                    'p2.org_13' => array (49.5, 86.5),
                    'p2.org_14' => array (98.5, 86),
                    'p2.org_99' => array (140.5, 86),
                    'p2.org_cual' => array (158, 86, - 2, 8, 12),
                    'p2.num_agro' => array (67, 103.5, - 2, 8, 4),
                    'p2.num_mine' => array (127, 103.5, - 2, 8, 4),
                    'p2.num_manu' => array (173, 103.5, - 2, 8, 4),
                    'p2.num_serv' => array (67, 108, - 2, 8, 4),
                    'p2.num_cons' => array (127, 108, - 2, 8, 4),
                    'p2.num_come' => array (173, 108, - 2, 8, 4),
                    'p2.num_rest' => array (67, 112, - 2, 8, 4),
                    'p2.num_tras' => array (127, 112, - 2, 8, 4),
                    'p2.num_comu' => array (173, 112, - 2, 8, 4),
                    'p2.num_fina' => array (67, 117, - 2, 8, 4),
                    'p2.num_serv_com' => array (127, 117, - 2, 8, 4),
                    'p2.cred_nom1' => array (25, 129, - 2, 9, 40),
                    'p2.cred_ofi1' => array (115, 129, - 2, 9, 30),
                    'p2.cred_nom2' => array (25, 136, - 2, 9, 40),
                    'p2.cred_ofi2' => array (115, 136, - 2, 9, 30),
                    'p2.ref_nom1' => array (25, 146, - 2, 9, 25),
                    'p2.ref_dir1' => array (84, 146, - 2, 9, 34),
                    'p2.ref_tel1' => array (160, 146, - 2, 9, 12),
                    'p2.ref_nom2' => array (25, 153, - 2, 9, 25),
                    'p2.ref_dir2' => array (84, 153, - 2, 9, 34),
                    'p2.ref_tel2' => array (160, 153, - 2, 9, 12),
                    'p2.bien1_mat' => array (50, 168, - 2, 9, 25),
                    'p2.bien1_dir' => array (40, 173, - 2, 9, 30),
                    'p2.bien1_bar' => array (40, 178, - 2, 9, 30),
                    'p2.bien1_mun' => array (40, 183, - 2, 9, 30),
                    'p2.bien1_dep' => array (43.5, 188, - 2, 9, 28),
                    'p2.bien1_pais' => array (40, 192.5, - 2, 9, 30),
                    'p2.bien2_mat' => array (130, 168, - 2, 9, 25),
                    'p2.bien2_dir' => array (120, 173, - 2, 9, 30),
                    'p2.bien2_bar' => array (120, 178, - 2, 9, 30),
                    'p2.bien2_mun' => array (120, 183, - 2, 9, 30),
                    'p2.bien2_dep' => array (123.5, 188, - 2, 9, 28),
                    'p2.bien2_pais' => array (120, 192.5, - 2, 9, 30),
                    'p2.bien3_mat' => array (50, 200, - 2, 9, 25),
                    'p2.bien3_dir' => array (40, 204, - 2, 9, 30),
                    'p2.bien3_bar' => array (40, 209, - 2, 9, 30),
                    'p2.bien3_mun' => array (40, 214, - 2, 9, 30),
                    'p2.bien3_dep' => array (43.5, 219, - 2, 9, 28),
                    'p2.bien3_pais' => array (40, 224, - 2, 9, 30),
                    'p2.bien4_mat' => array (130, 200, - 2, 9, 25),
                    'p2.bien4_dir' => array (120, 204, - 2, 9, 30),
                    'p2.bien4_bar' => array (120, 209, - 2, 9, 30),
                    'p2.bien4_mun' => array (120, 214, - 2, 9, 30),
                    'p2.bien4_dep' => array (123.5, 219, - 2, 9, 28),
                    'p2.bien4_pais' => array (120, 224, - 2, 9, 30),
                    'p2.ent_vig' => array (25, 243.5, - 2, 9, 60),
                    'p2.firm_nom' => array (25, 256, - 2, 9, 53),
                    'p2.firm_ide' => array (52, 260, - 2, 9, 10),
                    'p2.firm_cc' => array (77, 260.5),
                    'p2.firm_ce' => array (84, 260.5),
                    'p2.firm_ti' => array (91, 260.5),
                    'p2.firm_pas' => array (105.5, 260.5),
                    'p2.firma_elec' => array (135, 255, - 3, 5, 110));
                break;
            case '3':
                /* Anexo 1 Establecimientos, sucursales o agencias  */
                $p = array (
                    'p3.cod_camara' => array (64, 42, 2, 9, 2),
                    'p3.dia' => array (129, 42, 2, 9, 2),
                    'p3.mes' => array (146, 42, 2, 9, 2),
                    'p3.anio' => array (165, 42, 1, 9, 4),
                    'p3.est' => array (53, 47),
                    'p3.suc' => array (53, 51),
                    'p3.age' => array (66, 51),
                    'p3.mat' => array (106, 47),
                    'p3.ren' => array (106, 51),
                    'p3.num_mat' => array (143, 47, - 2, 9, 15),
                    'p3.anio_ren' => array (143, 51, 1, 9, 4),
                    'p3.nom' => array (30, 59.5, - 2, 9, 70),
                    'p3.dir' => array (30, 64, - 2, 9, 50),
                    'p3.pos' => array (138, 64, - 2, 9, 5),
                    'p3.bar' => array (151, 64, - 2, 9, 15),
                    'p3.mun' => array (30, 69, - 2, 9, 30),
                    'p3.dep' => array (96, 69, - 2, 9, 30),
                    'p3.dane' => array (164, 69, - 2, 9, 10),
                    'p3.tel1' => array (40.5, 72.5, - 0.5, 7, 10),
                    'p3.tel2' => array (95.5, 72.5, - 0.5, 7, 10),
                    'p3.tel3' => array (150.5, 72.5, - 0.5, 7, 10),
                    'p3.email' => array (30, 78, - 2, 9, 52),
                    'p3.fax' => array (151, 78, - 2, 9, 15),
                    'p3.not_dir' => array (30, 83, - 2, 9, 50),
                    'p3.not_pos' => array (138, 83, - 2, 9, 5),
                    'p3.not_bar' => array (151, 83, - 2, 9, 15),
                    'p3.not_mun' => array (30, 87.5, - 2, 9, 25),
                    'p3.not_dep' => array (85, 87.5, - 2, 9, 35),
                    'p3.not_dane' => array (164, 87.5, - 2, 9, 10),
                    'p3.not_email' => array (30, 92, - 2, 9, 52),
                    'p3.not_fax' => array (151, 92, - 2, 9, 15),
                    'p3.activos' => array (86, 97, - 2, 9, 20),
                    'p3.ciiu1' => array (32, 113.5, 4, 9, 4),
                    'p3.ciiu2' => array (71, 113.5, 4, 9, 4),
                    'p3.ciiu3' => array (110, 113.5, 4, 9, 4),
                    'p3.ciiu4' => array (150, 113.5, 4, 9, 4),
                    'p3.num_trab' => array (115, 121, - 2, 9, 4),
                    'p3.prop' => array (48, 130),
                    'p3.soci' => array (73, 130),
                    'p3.coop' => array (94, 130),
                    'p3.loc_prop' => array (159, 130),
                    'p3.loc_ajen' => array (174, 130),
                    'p3.prop1_nom' => array (30, 140.5, - 2, 9, 75),
                    'p3.prop1_ide' => array (32.5, 147, - 0.5, 7, 11),
                    'p3.prop1_cc' => array (75, 147),
                    'p3.prop1_ce' => array (83, 147),
                    'p3.prop1_nit' => array (91, 147),
                    'p3.prop1_pas' => array (105.5, 147),
                    'p3.prop1_pais' => array (114, 147, - 2, 9, 6),
                    'p3.prop1_num_mat' => array (129, 147.5, - 2, 8, 14),
                    'p3.prop1_camara' => array (160, 147.5, - 2, 8, 12),
                    'p3.prop1_dom_dir' => array (30, 154, - 2, 9, 75),
                    'p3.prop1_dom_mun' => array (30, 159.5, - 2, 9, 35),
                    'p3.prop1_dom_dep' => array (110, 159.5, - 2, 9, 35),
                    'p3.prop1_dom_tel1' => array (43, 163.5, - 0.5, 7, 10),
                    'p3.prop1_dom_tel2' => array (94.5, 163.5, - 0.5, 7, 10),
                    'p3.prop1_dom_tel3' => array (147, 163.5, - 0.5, 7, 10),
                    'p3.prop1_not_dir' => array (30, 171, - 2, 9, 40),
                    'p3.prop1_not_mun' => array (115, 171, - 2, 9, 15),
                    'p3.prop1_not_dep' => array (148, 171, - 2, 9, 18),
                    'p3.rep1_nom' => array (30, 177, - 2, 9, 75),
                    'p3.rep1_cc' => array (56.5, 181),
                    'p3.rep1_ce' => array (65, 181),
                    'p3.rep1_ti' => array (72.5, 181),
                    'p3.rep1_pas' => array (87.5, 181),
                    'p3.rep1_ide' => array (97.5, 181, - 0.5, 7, 10),
                    'p3.rep1_pais' => array (145, 181.5, - 2, 8, 18),
                    'p3.prop2_nom' => array (30, 197.5, - 2, 9, 75),
                    'p3.prop2_ide' => array (32.5, 203.5, - 0.5, 7, 11),
                    'p3.prop2_cc' => array (75, 203.5),
                    'p3.prop2_ce' => array (83, 203.5),
                    'p3.prop2_nit' => array (91, 203.5),
                    'p3.prop2_pas' => array (105.5, 203.5),
                    'p3.prop2_pais' => array (114, 203.5, - 2, 9, 6),
                    'p3.prop2_num_mat' => array (129, 204, - 2, 8, 14),
                    'p3.prop2_camara' => array (160, 204, - 2, 8, 12),
                    'p3.prop2_dom_dir' => array (30, 210, - 2, 9, 75),
                    'p3.prop2_dom_mun' => array (30, 215, - 2, 9, 35),
                    'p3.prop2_dom_dep' => array (110, 215, - 2, 9, 35),
                    'p3.prop2_dom_tel1' => array (43, 219, - 0.5, 7, 10),
                    'p3.prop2_dom_tel2' => array (94.5, 219, - 0.5, 7, 10),
                    'p3.prop2_dom_tel3' => array (147, 219, - 0.5, 7, 10),
                    'p3.prop2_not_dir' => array (30, 226, - 2, 9, 40),
                    'p3.prop2_not_mun' => array (115, 226, - 2, 9, 15),
                    'p3.prop2_not_dep' => array (148, 226, - 2, 9, 18),
                    'p3.rep2_nom' => array (30, 232, - 2, 9, 75),
                    'p3.rep2_cc' => array (56.5, 236.5),
                    'p3.rep2_ce' => array (65, 236.5),
                    'p3.rep2_ti' => array (72.5, 236.5),
                    'p3.rep2_pas' => array (87.5, 236.5),
                    'p3.rep2_ide' => array (97.5, 236.5, - 0.5, 7, 10),
                    'p3.rep2_pais' => array (145, 236.5, - 2, 8, 18),
                    'p3.firma_elec' => array (125, 265, - 3, 5, 110));
                break;
            case '4':
                /*  Anexo 3 Personas - Años Pendientes */
                $p = array (
                    'p4.cod_camara' => array (45, 45, 2, 9, 2),
                    'p4.dia' => array (144, 45, 2, 9, 2),
                    'p4.mes' => array (127, 45, 2, 9, 2),
                    'p4.anio' => array (98, 45, 2, 9, 4),
                    'p4.nit' => array (26, 53),
                    'p4.num_nit' => array (40.5, 53, 1, 9, 10),
                    'p4.dv' => array (91, 53),
                    'p4.num_mat' => array (127, 53, - 2, 9, 25),
                    'p4.raz_soc' => array (20, 62, - 2, 9, 80),
                    'p4.ape1' => array (20, 69, - 2, 9, 25),
                    'p4.ape2' => array (80, 69, - 2, 9, 25),
                    'p4.nom' => array (135, 69, - 2, 9, 25),
                    //WSI 2016-12-05 - POSICIONAMIENTO EN FORMULARIO VARIABLES NIFF
                    'p4.f1_anio_ren' => array (48, 85.5, -0.8, 9, 4),
                    'p4.f1_actcte' => array (49.5, 90.5, -2.9, 6, 30),
                    'p4.f1_actnocte' => array (49.5, 96.5, -2.9, 6, 30),
                    'p4.f1_acttot' => array (49.5, 102.5, -2.9, 6, 30),
                    'p4.f1_pascte' => array (99.5, 85, -2.9, 6, 30),
                    'p4.f1_paslar' => array (99.5, 90, -2.9, 6, 30),
                    'p4.f1_pastot' => array (99.5, 94.5, -2.9, 6, 30),
                    'p4.f1_pattot' => array (99.5, 99, -2.9, 6, 30),
                    'p4.f1_paspat' => array (99.5, 104, -2.9, 6, 30),
                    'p4.f1_balsoc' => array (99.5, 109, -2.9, 6, 30),
                    'p4.f1_ingope' => array (155.5, 85, -2.9, 6, 30),
                    'p4.f1_ingnoope' => array (155.5, 88, -2.9, 6, 30),
                    'p4.f1_cosven' => array (155.5, 91.5, -2.9, 6, 30),
                    'p4.f1_gasope' => array (155.5, 95, -2.9, 6, 30),
                    'p4.f1_gasnoope' => array (155.5, 98.5, -2.9, 6, 30),
                    'p4.f1_gasimp' => array (155.5, 102, -2.9, 6, 30),
                    'p4.f1_utiope' => array (155.5, 105.5, -2.9, 6, 30),
                    'p4.f1_utinet' => array (155.5, 109, -2.9, 6, 30),
                    //
                    'p4.f2_anio_ren' => array (48, 126, -0.8, 9, 4),
                    'p4.f2_actcte' => array (49.5, 131, -2.9, 6, 30),
                    'p4.f2_actnocte' => array (49.5, 137, -2.9, 6, 30),
                    'p4.f2_acttot' => array (49.5, 142.5, -2.9, 6, 30),
                    'p4.f2_pascte' => array (99.5, 125, -2.9, 6, 30),
                    'p4.f2_paslar' => array (99.5, 130, -2.9, 6, 30),
                    'p4.f2_pastot' => array (99.5, 134.5, -2.9, 6, 30),
                    'p4.f2_pattot' => array (99.5, 139, -2.9, 6, 30),
                    'p4.f2_paspat' => array (99.5, 144, -2.9, 6, 30),
                    'p4.f2_balsoc' => array (99.5, 149, -2.9, 6, 30),
                    'p4.f2_ingope' => array (155.5, 125, -2.9, 6, 30),
                    'p4.f2_ingnoope' => array (155.5, 128, -2.9, 6, 30),
                    'p4.f2_cosven' => array (155.5, 131.5, -2.9, 6, 30),
                    'p4.f2_gasope' => array (155.5, 135, -2.9, 6, 30),
                    'p4.f2_gasnoope' => array (155.5, 138.5, -2.9, 6, 30),
                    'p4.f2_gasimp' => array (155.5, 142, -2.9, 6, 30),
                    'p4.f2_utiope' => array (155.5, 145.5, -2.9, 6, 30),
                    'p4.f2_utinet' => array (155.5, 149, -2.9, 6, 30),
                    //
                    'p4.f3_anio_ren' => array (48, 166.5, -0.8, 9, 4),
                    'p4.f3_actcte' => array (49.5, 171, -2.9, 6, 30),
                    'p4.f3_actnocte' => array (49.5, 177, -2.9, 6, 30),
                    'p4.f3_acttot' => array (49.5, 182.5, -2.9, 6, 30),
                    'p4.f3_pascte' => array (99.5, 165.5, -2.9, 6, 30),
                    'p4.f3_paslar' => array (99.5, 170.5, -2.9, 6, 30),
                    'p4.f3_pastot' => array (99.5, 175, -2.9, 6, 30),
                    'p4.f3_pattot' => array (99.5, 179.5, -2.9, 6, 30),
                    'p4.f3_paspat' => array (99.5, 184.5, -2.9, 6, 30),
                    'p4.f3_balsoc' => array (99.5, 189, -2.9, 6, 30),
                    'p4.f3_ingope' => array (155.5, 165.5, -2.9, 6, 30),
                    'p4.f3_ingnoope' => array (155.5, 168.5, -2.9, 6, 30),
                    'p4.f3_cosven' => array (155.5, 172, -2.9, 6, 30),
                    'p4.f3_gasope' => array (155.5, 175.5, -2.9, 6, 30),
                    'p4.f3_gasnoope' => array (155.5, 179, -2.9, 6, 30),
                    'p4.f3_gasimp' => array (155.5, 183, -2.9, 6, 30),
                    'p4.f3_utiope' => array (155.5, 186, -2.9, 6, 30),
                    'p4.f3_utinet' => array (155.5, 190, -2.9, 6, 30),
                    //
                    'p4.f4_anio_ren' => array (48, 207.5, -0.8, 9, 4),
                    'p4.f4_actcte' => array (49.5, 212, -2.9, 6, 30),
                    'p4.f4_actnocte' => array (49.5, 218, -2.9, 6, 30),
                    'p4.f4_acttot' => array (49.5, 224, -2.9, 6, 30),
                    'p4.f4_pascte' => array (99.5, 206, -2.9, 6, 30),
                    'p4.f4_paslar' => array (99.5, 211, -2.9, 6, 30),
                    'p4.f4_pastot' => array (99.5, 216.5, -2.9, 6, 30),
                    'p4.f4_pattot' => array (99.5, 221, -2.9, 6, 30),
                    'p4.f4_paspat' => array (99.5, 225.5, -2.9, 6, 30),
                    'p4.f4_balsoc' => array (99.5, 230, -2.9, 6, 30),
                    'p4.f4_ingope' => array (155.5, 205.5, -2.9, 6, 30),
                    'p4.f4_ingnoope' => array (155.5, 208.5, -2.9, 6, 30),
                    'p4.f4_cosven' => array (155.5, 212, -2.9, 6, 30),
                    'p4.f4_gasope' => array (155.5, 215.5, -2.9, 6, 30),
                    'p4.f4_gasnoope' => array (155.5, 219, -2.9, 6, 30),
                    'p4.f4_gasimp' => array (155.5, 222.5, -2.9, 6, 30),
                    'p4.f4_utiope' => array (155.5, 226.5, -2.9, 6, 30),
                    'p4.f4_utinet' => array (155.5, 230, -2.9, 6, 30),
                    //
                    'p4.firm_nom' => array (20, 251.5, - 2, 9, 54),
                    'p4.firm_ide' => array (48, 256, - 2, 9, 10),
                    'p4.firm_cc' => array (72.5, 256),
                    'p4.firm_ce' => array (79.5, 256),
                    'p4.firm_ti' => array (87, 256),
                    'p4.firm_pas' => array (101, 256),
                    'p4.firm_pais' => array (110, 256, - 2, 7, 10),
                    'p4.firma_elec' => array (135, 250, - 3, 5, 110));
                break;
            case '5':
                /* Anexo 4 Establecimientos - Años pendientes  */
                $p = array (
                    'p5.cod_camara' => array (46, 45, 2, 9, 2),
                    'p5.dia' => array (144, 45, 2, 9, 2),
                    'p5.mes' => array (127, 45, 2, 9, 2),
                    'p5.anio' => array (98, 45, 2, 9, 4),
                    'p5.est' => array (75, 53),
                    'p5.suc' => array (97, 53),
                    'p5.agen' => array (116, 53),
                    'p5.est_nom' => array (20, 61, - 2, 9, 43),
                    'p5.est_num_mat' => array (127, 60, - 2, 9, 25),
                    'p5.prop_nom' => array (20, 69, - 2, 9, 43),
                    'p5.prop_num_mat' => array (127, 68, - 2, 9, 25),
                    'p5.nit' => array (30, 76, 1, 9, 10),
                    'p5.dv' => array (80, 76, 1, 9, 1),
                    'p5.f1_anio_ren' => array (48, 90, 1, 9, 4),
                    'p5.f1_activo' => array (75, 95.5, - 2, 9, 25),
                    'p5.f2_anio_ren' => array (48, 115, 1, 9, 4),
                    'p5.f2_activo' => array (75, 121, - 2, 9, 25),
                    'p5.f3_anio_ren' => array (48, 140, 1, 9, 4),
                    'p5.f3_activo' => array (75, 147, - 2, 9, 25),
                    'p5.f4_anio_ren' => array (48, 166, 1, 9, 4),
                    'p5.f4_activo' => array (75, 172, - 2, 9, 25),
                    'p5.f5_anio_ren' => array (48, 192, 1, 9, 4),
                    'p5.f5_activo' => array (75, 198, - 2, 9, 25),
                    'p5.f6_anio_ren' => array (48, 218, 1, 9, 4),
                    'p5.f6_activo' => array (75, 223, - 2, 9, 25),
                    'p5.firm_nom' => array (22, 252, - 2, 9, 53),
                    'p5.firm_ide' => array (48, 256, - 2, 9, 10),
                    'p5.firm_cc' => array (72.5, 256.5),
                    'p5.firm_ce' => array (80, 256.5),
                    'p5.firm_ti' => array (86.5, 256.5),
                    'p5.firm_pas' => array (101, 256.5),
                    'p5.firm_pais' => array (109, 256, - 2, 9, 10),
                    'p5.firma_elec' => array (135, 250, - 3, 5, 110));
                break;
            case '6' :
                /* Anexo 2 Hoja 1 - Registro Proponentes */
                $p = array (
                    'p6.cod_camara' => array (59, 53, 2, 9, 2),
                    'p6.dia' => array (135, 53, 2, 9, 2),
                    'p6.mes' => array (154, 53, 2, 9, 2),
                    'p6.anio' => array (173, 53, 1, 9, 4),
                    'p6.ins' => array (36.5, 62),
                    'p6.ren' => array (79.5, 62),
                    'p6.act' => array (126.5, 62),
                    'p6.tras' => array (189.5, 62),
                    'p6.nit' => array (39.5, 69, 1, 9, 10),
                    'p6.dv' => array (95, 69),
                    'p6.gran_emp' => array (47, 86),
                    'p6.med_emp' => array (91, 86),
                    'p6.peq_emp' => array (140, 86),
                    'p6.micro_emp' => array (182, 86),
                    'p6.cap_anio' => array (86, 114.5, 1, 10, 4),
                    'p6.cap_mes' => array (116, 114.5, 2, 10, 2),
                    'p6.cap_dia' => array (133, 114.5, 2, 10, 2),
                    'p6.ind_liqui' => array (110, 133, - 2, 10, 11),
                    'p6.ind_endeu' => array (110, 150.5, - 2, 10, 11),
                    'p6.upo' => array (124, 168, - 2.5, 7, 20),
                    'p6.gas_int' => array (124, 172, - 2.5, 7, 20),
                    'p6.razon_cober' => array (160, 168, - 2, 7, 20),
                    'p6.rent_pat' => array (135, 195, - 2, 8, 20),
                    'p6.rent_act' => array (135, 213, - 2, 8, 20),
                    'p6.firm_nom' => array (15, 256, - 2, 9, 35),
                    'p6.firm_ide' => array (42, 266, - 2, 9, 10),
                    'p6.firm_cc' => array (67.5, 266.5, 2, 9, 1),
                    'p6.firm_ce' => array (77.5, 266.5, 2, 9, 1),
                    'p6.firm_pas' => array (93.5, 266.5, 2, 9, 1),
                    'p6.firma_elec' => array (145, 240, - 3, 5, 110));
                break;
            case '7' :
                /* Anexo 2 - Registro Proponentes - Situaciones de Control */
                $y = $this->tablaSitControl($item);
                $p = array (
                    'p7.num_hoja' => array (100, 32.5, - 2, 11, 4),
                    'p7.ins' => array (36, 50),
                    'p7.ren' => array (80, 50),
                    'p7.act' => array (127, 50),
                    'p7.tras' => array (189, 50),
                    'p7.nit' => array (39.5, 57, 1, 9, 9),
                    'p7.dv' => array (95, 57),
                    'p7.nom' => array (21, $y, - 2, 9, 24),
                    'p7.ide' => array (71, $y, - 2, 9, 14),
                    'p7.dom' => array (101, $y, - 2, 9, 23),
                    'p7.sit_1' => array (153, $y, - 2, 9),
                    'p7.sit_2' => array (165, $y, - 2, 9),
                    'p7.sit_3' => array (175, $y, - 2, 9),
                    'p7.sit_4' => array (186, $y, - 2, 9),
                    'p7.firm_nom' => array (15, 254, - 2, 9, 35),
                    'p7.firm_ide' => array (42, 266, - 2, 9, 10),
                    'p7.firm_cc' => array (67.5, 266.5),
                    'p7.firm_ce' => array (77.5, 266.5),
                    'p7.firm_pas' => array (93.5, 266.5),
                    'p7.firma_elec' => array (145, 240, - 3, 5, 110));
                break;
            case '8' :
                /*  Anexo 2 - Registro Proponentes - Clasificación */
                switch ($this->indice) {
                    case 'p8.cod_seg' :
                    case 'p8.cod_fam' :
                    case 'p8.cod_cla' :
                    case 'p8.cod_pro' :
                        $x = $this->tabla_Clasif51_X($item);
                        $y = $this->tabla_Clasif51_Y($item);
                        break;
                    case 'p8.cod_seg_elim' :
                    case 'p8.cod_fam_elim' :
                    case 'p8.cod_cla_elim' :
                    case 'p8.cod_pro_elim' : $x = $this->tabla_Clasif52_X($item);
                        $y = $this->tabla_Clasif52_Y($item);
                        break;
                }
                $p = array (
                    'p8.num_hoja' => array (94, 32.5, - 2, 11, 4),
                    'p8.ins' => array (36, 40),
                    'p8.ren' => array (80, 40),
                    'p8.act' => array (127, 40),
                    'p8.tras' => array (189, 40),
                    'p8.cod_seg' => array ($x, $y, 2, 8, 2),
                    'p8.cod_fam' => array ($x + 13, $y, 2, 8, 2),
                    'p8.cod_cla' => array ($x + 27, $y, 2, 8, 2),
                    'p8.cod_pro' => array ($x + 40, $y, 2, 8, 2),
                    'p8.cod_seg_elim' => array ($x, $y, 2, 8, 2),
                    'p8.cod_fam_elim' => array ($x + 13, $y, 2, 8, 2),
                    'p8.cod_cla_elim' => array ($x + 27, $y, 2, 8, 2),
                    'p8.cod_pro_elim' => array ($x + 40, $y, 2, 8, 2),
                    'p8.folios' => array (167, 222, 1, 9, 3),
                    'p8.firm_nom' => array (15, 254, - 2, 9, 35),
                    'p8.firm_ide' => array (42, 266, - 2, 9, 10),
                    'p8.firm_cc' => array (67.5, 266.5),
                    'p8.firm_ce' => array (77.5, 266.5),
                    'p8.firm_pas' => array (93.5, 266.5),
                    'p8.firma_elec' => array (145, 240, - 3, 5, 110));
                break;
            case '9' :
                /* Anexo 2 - Registro Proponentes - Facultades */
                $p = array (
                    'p9.num_hoja' => array (96, 37, - 2, 11, 4),
                    'p9.ins' => array (36, 44.5),
                    'p9.ren' => array (80, 44.5),
                    'p9.act' => array (127, 44.5),
                    'p9.tras' => array (189, 44.5),
                    'p9.nit' => array (46.5, 54, 1, 9, 9),
                    'p9.dv' => array (97, 54),
                    'p9.raz_soc' => array (25, 63.5, - 2, 9, 80),
                    'p9.dur_anio' => array (47, 69, 0, 9, 4),
                    'p9.dur_mes' => array (72, 69, 0, 9, 2),
                    'p9.dur_dia' => array (87, 69, 0, 9, 2),
                    'p9.dur_ind' => array (154, 69),
                    'p9.pj_anio' => array (104, 81, 0, 9, 4),
                    'p9.pj_mes' => array (129, 81, 0, 9, 2),
                    'p9.pj_dia' => array (144, 81, 0, 9, 2),
                    'p9.pj_cla_doc' => array (46, 87, - 2, 7, 25),
                    'p9.pj_num_doc' => array (138, 87, - 2, 7, 25),
                    'p9.pj_doc_anio' => array (54.5, 94, 0, 9, 4),
                    'p9.pj_doc_mes' => array (79.5, 94, 0, 9, 2),
                    'p9.pj_doc_dia' => array (95.5, 94, 0, 9, 2),
                    'p9.pj_doc_exp' => array (124, 94, - 2, 7, 33),
                    'p9.rep_incl' => array (71, 105),
                    'p9.rep_elim' => array (99, 105),
                    'p9.rep_nom' => array (59, 111, - 2, 7, 65),
                    'p9.rep_cc' => array (71, 116.5),
                    'p9.rep_ce' => array (84, 116.5),
                    'p9.rep_nit' => array (94, 116.5),
                    'p9.rep_pas' => array (115, 116.5),
                    'p9.rep_num_ide' => array (34, 122, - 2, 7, 24),
                    'p9.facul_incl' => array (71, 134.5),
                    'p9.facul_modif' => array (102, 134.5),
                    'p9.facul_elim' => array (129, 134.5),
                    'p9.facul_detalle' => array (21, 139.5, - 2, 5.8),
                    'p9.firm_nom' => array (15, 254, - 2, 9, 35),
                    'p9.firm_ide' => array (42, 266, - 2, 9, 10),
                    'p9.firm_cc' => array (67.5, 266.5),
                    'p9.firm_ce' => array (77.5, 266.5),
                    'p9.firm_pas' => array (93.5, 266.5),
                    'p9.firma_elec' => array (145, 240, - 3, 5, 110));
                break;
            case '10' :
                /* Anexo 2 - Registro Proponentes - Experiencia */
                switch ($this->indice) {
                    case 'p10.cod_seg' :
                    case 'p10.cod_fam' :
                    case 'p10.cod_cla' :
                    case 'p10.cod_pro' :
                        $x = $this->tabla_Exp88_X($item);
                        $y = $this->tabla_Exp88_Y($item);
                        break;
                    case 'p10.cod_contr_elim' :
                        $x = $this->tabla_Exp9_Y($item);
                        break;
                }
                $p = array (
                    'p10.num_hoja' => array (94, 32, - 2, 11, 4),
                    'p10.ins' => array (36, 40),
                    'p10.ren' => array (80, 40),
                    'p10.act' => array (127, 40),
                    'p10.tras' => array (189, 40),
                    'p10.nit' => array (46.5, 58.5, 1, 9, 10),
                    'p10.dv' => array (103.5, 58.5),
                    'p10.num_cons_rep' => array (116.5, 68.5, 1, 8, 3),
                    'p10.exp_prop' => array (42, 82.5),
                    'p10.exp_acc' => array (141, 91),
                    'p10.exp_cons' => array (141, 100),
                    'p10.nom_contratista' => array (51, 115, - 2, 9, 70),
                    'p10.nom_contratante' => array (51, 126, - 2, 9, 70),
                    'p10.val_cont' => array (81, 137, - 2, 9, 35),
                    'p10.porc_part' => array (137, 148, - 2, 9, 7),
                    'p10.cod_seg' => array ($x, $y, 2, 8, 2),
                    'p10.cod_fam' => array ($x + 13, $y, 2, 8, 2),
                    'p10.cod_cla' => array ($x + 27, $y, 2, 8, 2),
                    'p10.cod_pro' => array ($x + 41, $y, 2, 8, 2),
                    'p10.continua' => array (169, 198, - 2, 8, 12),
                    'p10.cod_contr_elim' => array ($x, 211, 2, 8, 3),
                    'p10.firm_nom' => array (15, 254, - 2, 9, 35),
                    'p10.firm_ide' => array (42, 266, - 2, 9, 10),
                    'p10.firm_cc' => array (67.5, 266.5),
                    'p10.firm_ce' => array (77.5, 266.5),
                    'p10.firm_pas' => array (93.5, 266.5),
                    'p10.firma_elec' => array (145, 240, - 3, 5, 110));
                break;
        }

        return $p;
    }

    /* Me$pruebatodo p$pruebablico que construye arreglo del campo a imprimir en el formulario */

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
        return $this->numeroRecuperacion;
    }

    public function setNumeroLiquidacion($liquidacion) {
        $this->numeroLiquidacion = $liquidacion;
    }

    public function getNumeroLiquidacion() {
        return $this->numeroLiquidacion;
    }

    public function setFechaImpresion($fec) {
        $this->fechaImpresion = $fec;
    }

    public function getFechaImpresion() {
        return $this->fechaImpresion;
    }

    /* Funci$prueban que retorna la posici$prueban en Y (entre 1 y 21) para la tabla de situaciones de control */

    function tablaSitControl($item) {
        $y = 0;
        switch ($item) {
            case '1' :
                $y = 96;
                break;
            case '2' :
                $y = 103;
                break;
            case '3' :
                $y = 109;
                break;
            case '4' :
                $y = 115;
                break;
            case '5' :
                $y = 121;
                break;
            case '6' :
                $y = 128;
                break;
            case '7' :
                $y = 134;
                break;
            case '8' :
                $y = 140;
                break;
            case '9' :
                $y = 146;
                break;
            case '10' :
                $y = 153;
                break;
            case '11' :
                $y = 159;
                break;
            case '12' :
                $y = 165;
                break;
            case '13' :
                $y = 171;
                break;
            case '14' :
                $y = 177;
                break;
            case '15' :
                $y = 184;
                break;
            case '16' :
                $y = 190;
                break;
            case '17' :
                $y = 196;
                break;
            case '18' :
                $y = 202;
                break;
            case '19' :
                $y = 209;
                break;
            case '20' :
                $y = 215;
                break;
            case '21' :
                $y = 221;
                break;
        }
        return $y;
    }

    /* Funci&oacute;n que retorna la posici&oacute;n en X (entre 1 y 3) en la tabla de clasificaciones */

    function tabla_Clasif51_X($item) {
        $x = 0;
        $residuo = ($item % 3);
        switch ($residuo) {
            case 1 :
                $x = 24;
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
                $y = 61;
                break;
            case ($item < 7) :
                $y = 67;
                break;
            case ($item < 10) :
                $y = 72;
                break;
            case ($item < 13) :
                $y = 78;
                break;
            case ($item < 16) :
                $y = 84;
                break;
            case ($item < 19) :
                $y = 90;
                break;
            case ($item < 22) :
                $y = 96;
                break;
            case ($item < 25) :
                $y = 101;
                break;
            case ($item < 28) :
                $y = 107;
                break;
            case ($item < 31) :
                $y = 112;
                break;
            case ($item < 34) :
                $y = 118;
                break;
            case ($item < 37) :
                $y = 124;
                break;
            case ($item < 40) :
                $y = 130;
                break;
            case ($item < 43) :
                $y = 136;
                break;
            case ($item < 46) :
                $y = 141;
                break;
            case ($item < 49) :
                $y = 147;
                break;
            case ($item < 52) :
                $y = 152;
                break;
            case ($item < 55) :
                $y = 158;
                break;
            case ($item < 58) :
                $y = 164;
                break;
            case ($item < 61) :
                $y = 170;
                break;
            case ($item < 64) :
                $y = 175;
                break;
        }
        return $y;
    }

    /* Funci&oacute;n que retorna la posici&oacute;n en X (entre 1 y 3) en la tabla de clasificaciones a eliminar */

    function tabla_Clasif52_X($item) {
        $x = 0;
        $residuo = ($item % 3);
        switch ($residuo) {
            case 1 :
                $x = 24;
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
                $y = 196.5;
                break;
            case ($item < 7) :
                $y = 202.5;
                break;
            case ($item < 10) :
                $y = 208;
                break;
        }
        return $y;
    }

    /* Funci&oacute;n que retorna la posici&oacute;n en X (entre 1 y 3) en la tabla de experiencia */

    function tabla_Exp88_X($item) {
        $x = 0;
        $residuo = ($item % 3);
        switch ($residuo) {
            case 1 :
                $x = 24;
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
                $y = 171;
                break;
            case ($item < 7) :
                $y = 177;
                break;
            case ($item < 10) :
                $y = 182.5;
                break;
            case ($item < 13) :
                $y = 188;
                break;
            case ($item < 16) :
                $y = 193;
                break;
        }
        return $y;
    }

    /* Funci&oacute;n que retorna la posici&oacute;n en X (entre 1 y 9) en la tabla de experiencia a eliminar */

    function tabla_Exp9_Y($item) {
        $x = 0;
        switch ($item) {
            case 1 :
                $x = 27;
                break;
            case 2 :
                $x = 45;
                break;
            case 3 :
                $x = 63;
                break;
            case 4 :
                $x = 80;
                break;
            case 5 :
                $x = 98;
                break;
            case 6 :
                $x = 115;
                break;
            case 7 :
                $x = 133;
                break;
            case 8 :
                $x = 151;
                break;
            case 9 :
                $x = 168;
                break;
        }
        return $x;
    }

}

?>