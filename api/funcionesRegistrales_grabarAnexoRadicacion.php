<?php

class funcionesRegistrales_grabarAnexoRadicacion {

    public static function grabarAnexoRadicacion($dbx = null, $idradicacion = 0, $numerorecibo = '', $numerooperacion = '', $identificacion = '', $nombre = '', $acreedor = '', $nombreacreedor = '', $matricula = '', $proponente = '', $idtipodoc = '', $numdoc = '', $fechadoc = '', $idorigendoc = '', $txtorigendoc = '', $idclasificacion = '', $numcontrato = '', $idfuente = '', $version = 1, $path = '', $estado = '', $fechaescaneo = '', $idusuarioescaneo = '', $idcajaarchivo = '', $idlibroarchivo = '', $observaciones = '', $libro = '', $registro = '', $dupli = '', $bandeja = '', $soporterecibo = '', $identificador = '', $tipoanexo = '', $procesoespecial = '', $nir = '', $nuc = '', $datareferencia = '') {
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
            'dupli',
            'identificacion',
            'nombre',
            'acreedor',
            'nombreacreedor',
            'matricula',
            'proponente',
            'numerounico',
            'numerointerno',
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
            "'" . $dupli . "'",
            "'" . $identificacion . "'",
            "'" . addslashes(strtoupper($nombre)) . "'",
            "'" . $acreedor . "'",
            "'" . addslashes(strtoupper($nombreacreedor)) . "'",
            "'" . $matricula . "'",
            "'" . $proponente . "'",
            "'" . $nuc . "'",
            "'" . $nir . "'",
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
        insertarRegistrosMysqliApi($dbx, 'mreg_radicacionesanexos', $arrCampos, $arrValores, 'si');
        $idanexo = $_SESSION["generales"]["lastId"];

        //
        borrarRegistrosMysqliApi($dbx, 'mreg_radicacionanexos_campos', "idanexo=" . $idanexo . " and campo='datareferencia'");
        if ($datareferencia != '') {
            $arrCampos = array(
                'idanexo',
                'campo',
                'contenido'
            );
            $arrValores = array(
                $idanexo,
                "'datareferencia'",
                "'" . addslashes($datareferencia) . "'"
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_radicacionanexos_campos', $arrCampos, $arrValores);
        }
        return $idanexo;
    }

}

?>
