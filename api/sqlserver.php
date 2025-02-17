<?php

// *************************************************************************************
// INTERFAZ BASICA DE SQL SERVER
// *************************************************************************************
//
function conexionSqlServer($xambiente = 'P') {

    if ($xambiente == 'P') {
        $serverName = RUES_P_SERVER;
        $connectionInfo = array("Database" => RUES_P_DATABASE, "UID" => RUES_P_USER, "PWD" => RUES_P_PASSWORD);
    }
    if ($xambiente == 'D') {
        $serverName = RUES_D_SERVER;
        $connectionInfo = array("Database" => RUES_D_DATABASE, "UID" => RUES_D_USER, "PWD" => RUES_D_PASSWORD);
    }
    if ($xambiente == 'Q') {
        $serverName = RUES_Q_SERVER;
        $connectionInfo = array("Database" => RUES_Q_DATABASE, "UID" => RUES_Q_USER, "PWD" => RUES_Q_PASSWORD);
    }

    //
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if (!$conn) {
        $_SESSION["generales"]["txtemergente"] = "Error en la conexion a la base de datos";
        return false;
    }
    return $conn;
}

// ************************************************************************************************* //
// Contar registros Sii 
// ************************************************************************************************* //
function contarRegistrosSqlServer($conn, $tabla, $condicion) {
    try {
        $result = sqlsrv_query($conn, "select count(*) as contador from " . $tabla . " where " . $condicion);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = sqlsrv_errors();
            return false;
        }
        while ($row = sqlsrv_fetch_object($result)) {
            return $row["contador"];
        }
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Excepcion';
        return false;
    }
}

function ejecutarQuerySqlServer($conn, $query) {
    try {
        $result = sqlsrv_query($conn, $query);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = sqlsrv_errors();
            return false;
        }
        $rows = array();
        while ($row = sqlsrv_fetch_object($result)) {
            $rows[] = $row;
        }
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Excepcion';
        return false;
    }
    return $rows;
}

function retornarRegistrosSqlServer($conn, $tabla, $condicion, $orden = '', $campos = '*') {

    $uncampo = 'no';
    if ($campos != '*') {
        if (strpos($campos, ',') === false) {
            $uncampo = 'si';
        }
    }

    $ordenx = '';
    if ($orden != '') {
        $ordenx = " order by " . $orden;
    }

    if ($campos == '*') {
        $query = "SELECT * from " . $tabla . " where " . $condicion . $ordenx;
    } else {
        $query = "SELECT " . $campos . " from " . $tabla . " where " . $condicion . $ordenx;
    }
    try {
        $result = sqlsrv_query($conn, $query);
        if ($result === false) {
            $txterrores = print_r(sqlsrv_errors(), true);
            $_SESSION["generales"]["mensajeerror"] = $txterrores;
            return false;
        }
        $rows = array();
        while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
        sqlsrv_free_stmt($result);  
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Excepcion';
        return false;
    }
    return $rows;
}

function retornarRegistroSqlServer($conn, $tabla, $condicion, $campos = '*', $tip = 'P') {

    $uncampo = 'no';
    if ($campos != '*') {
        if (strpos($campos, ',') === false) {
            $uncampo = 'si';
        }
    }

    $ordenx = '';
    if ($orden != '') {
        $ordenx = " order by " . $orden;
    }

    if ($campos == '*') {
        $query = "SELECT * from " . $tabla . " where " . $condicion;
    } else {
        $query = "SELECT " . $campos . " from " . $tabla . " where " . $condicion;
    }
    try {
        $result = sqlsrv_query($conn, $query);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = sqlsrv_errors();
            return false;
        }
        $rows = array();
        $icont = 0;
        while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
            $icont++;
            if ($tip == 'P' && $icont == 1) {
                $rows = $row;
            } else {
                if ($tip != 'P') {
                    $rows = $row;
                }
            }
        }
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Excepcion';
        return false;
    }
    return $rows;
}

function retornarEstablecimientosSqlServer($conn = null, $tipo = 'A', $nit = null, $dv = null, $cc = null) {

    if ($tipo == 'A') {
        $sql = "EXEC p_conConsultaEstablecimientosIdentificacion @NIT = ?, @DV = ?, @CLASEIDENTIFICA = ?, @CODIGO_CAMARA = ?";
    } else {
        $sql = "EXEC p_conConsultaEstablecimientosIdentificacionTodos @NIT = ?, @DV = ?, @CLASEIDENTIFICA = ?, @CODIGO_CAMARA = ?";
    }

    //
    $procedure_params = array(
        trim($nit),
        null,
        null,
        null
    );

    //
    $stmt = sqlsrv_prepare($conn, $sql, $procedure_params);
    if ($stmt === false) {
        $_SESSION["generales"]["mensajeerror"] = 'Error en preparacion de query de procedimiento almacenado';
        return false;
    }

    if (sqlsrv_execute($stmt) === false) {
        $_SESSION["generales"]["mensajeerror"] = 'Error en ejecución de procedimiento almacenado';
        return false;
    }

    //
    $rows = array();
    while ($row = sqlsrv_fetch_array($stmt)) {
        if (!isset($row["ano_renovado_anterior2"])) {
            $row["ano_renovado_anterior2"] = '';
            $row["ano_renovado_anterior3"] = '';
            $row["fecha_renovacion_anterior2"] = '';
            $row["fecha_renovacion_anterior3"] = '';
        }
        $rows[] = $row;
    }
    return $rows;
}

?>