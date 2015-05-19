<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
if (!function_exists('prxgt_install_recreate_column')) {
    /**
     * Backup data for existing column, re-create column and move data back. Removes 'columnOld' in case of new name
     * for the column was applied.
     *
     * @param Varien_Db_Adapter_Interface $conn
     * @param                             $table
     * @param                             $column
     * @param                             $columnDef
     * @param null $columnOld old name for the column
     */
    function prxgt_install_recreate_column(Varien_Db_Adapter_Pdo_Mysql $conn, $table, $column, $columnDef, $columnOld = null)
    {
        $columnTmp = $column . '_tmp';
        $fetched = $conn->fetchAll("SELECT * FROM $table LIMIT 1");

        // analyze old named column data
        $oldColumnExists = (!is_null($columnOld) && is_array($fetched) && isset($fetched[0]) && array_key_exists($columnOld, $fetched[0]));
        // analyze current column data
        $columnExists = (is_array($fetched) && isset($fetched[0]) && array_key_exists($column, $fetched[0]));
        // create backup column and backup data
        if ($columnExists || $oldColumnExists) {
            $conn->addColumn($table, $columnTmp, $columnDef);
            if ($oldColumnExists) {
                // backup old column data
                $conn->query("UPDATE  $table SET  $columnTmp = $columnOld");
            } else {
                // backup current column data
                $conn->query("UPDATE  $table SET  $columnTmp = $column");
            }
        }
        // re-create current column
        $conn->dropColumn($table, $column);
        $conn->addColumn($table, $column, $columnDef);
        // restore column data from backup
        if ($columnExists || $oldColumnExists) {
            // restore existed data
            $conn->query("UPDATE  $table SET $column = $columnTmp");
            $conn->dropColumn($table, $columnTmp);
        }
        // drop old column (for case of empty table)
        if (!is_null($columnOld) && ($oldColumnExists) && ($columnOld != $column)) {
            $conn->dropColumn($table, $columnOld);
        }
    }
}