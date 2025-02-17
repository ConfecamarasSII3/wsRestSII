<?php

class funcionesRegistrales_adicionarActoEstudioAsentarRecibo {

    public static function adicionarActoEstudioAsentarRecibo($dbx, $ind, $tra, $trans) {
        $existeED = false;
        $result = ejecutarQueryMysqliApi($dbx, "SHOW COLUMNS FROM mreg_estudio_actos_registro WHERE Field = 'extinciondominio'");
        if ($result && !empty($result)) {
            $existeED = true;
        }


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
            'motivocancelacion',
            'numlibro',
            'numreg',
            'dupli',
            'fechareg',
            'horareg',
            'usuario'
        );
        if ($existeED) {
            $arrCampos[] = 'extinciondominio';
        }

        //
        $tra["noticia"] = '';

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

        if (ltrim($tra["cantidad"], "0") == '') {
            $tra["cantidad"] = 1;
        }


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

            if (trim($tra["noticia"]) == '') {
                $tra["noticia"] = retornarRegistroMysqliApi($dbx, 'mreg_actos', "idlibro='" . $lib . "' and idacto='" . substr($act, 5, 4) . "'", "nombre");
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
            // $nnomx = $tra["razonsocial"];
            $nnomx = '';
            $perx = $tra["personal"];
            $actix = $tra["activos"];
            $actx = retornarRegistroMysqliApi($dbx, 'mreg_actos', "idlibro='" . $lib . "' and idacto='" . substr($act, 5, 4) . "'");

            // En caso de contratos de compraventa            
            if ($trans["idtipotransaccion"] == '022') {
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

            if (isset($_SESSION["generales"]["codigobarras1"]) && $_SESSION["generales"]["codigobarras1"] != '') {
                $cb = $_SESSION["generales"]["codigobarras1"];
            } else {
                $cb = $_SESSION["tramite"]["numeroradicacion"];
            }
            $_SESSION["generales"]["icontrol"]++;
            $arrValores = array(
                "'" . ltrim($cb, "0") . "'",
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
                "'" . addslashes($nnomx) . "'", // Nombre
                "'" . $tra["fechaduracion"] . "'", // Nueva fecha de duracion
                "'" . $tra["camaraanterior"] . "'",
                "'" . $tra["matriculaanterior"] . "'",
                "'" . $tra["fechamatriculaanterior"] . "'",
                "'" . $tra["fecharenovacionanterior"] . "'",
                "'" . $tra["ultimoanorenovadoanterior"] . "'",
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
                "'" . $tra["motivocancelacion"] . "'", // Motivo de la cancelación
                "''", // Libro
                "''", // Registro
                "''", // Dupli
                "''", // Fecha registro
                "''", // Hora registro
                "''" // Usuario
            );

            if ($existeED) {
                $arrValores[] = "'" . $actx["controlextinciondominio"] . "'";
            }


            $res = insertarRegistrosMysqliApi($dbx, 'mreg_estudio_actos_registro', $arrCampos, $arrValores);
            if ($res === false) {
                $nameLog = 'adicionarActoEstudioAsentarReciboSii_' . date("Ymd");
                \logApi::general2($nameLog, '', utf8_encode('Error creando mreg_estudio_actos_registro : ' . $_SESSION["generales"]["mensajeerror"]));
                return false;
            }

            //
            $arrCampos = array(
                'idradicacion',
                'orden',
                'campo',
                'contenido'
            );

            //
            $arrValores = array(
                "'" . $cb . "'",
                "'" . sprintf("%02s", $_SESSION["generales"]["icontrol"]) . "'",
                "'nombrebase64'",
                "'" . base64_encode($nomx) . "'"
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_estudio_actos_registro_campos', $arrCampos, $arrValores);

            //
            $arrValores = array(
                "'" . $cb . "'",
                "'" . sprintf("%02s", $_SESSION["generales"]["icontrol"]) . "'",
                "'noticiabase64'",
                "'" . base64_encode($tra["noticia"]) . "'"
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_estudio_actos_registro_campos', $arrCampos, $arrValores);

            //
            if ($nnomx != '') {
                //
                $arrValores = array(
                    "'" . $cb . "'",
                    "'" . sprintf("%02s", $_SESSION["generales"]["icontrol"]) . "'",
                    "'nuevonombrebase64'",
                    "'" . base64_encode($nnomx) . "'"
                );
                insertarRegistrosMysqliApi($dbx, 'mreg_estudio_actos_registro_campos', $arrCampos, $arrValores);
            }
        }
        return true;
    }

}

?>
