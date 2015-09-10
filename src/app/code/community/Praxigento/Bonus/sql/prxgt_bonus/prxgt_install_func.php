<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
if(!function_exists('prxgt_install_recreate_column')) {
    /**
     * Backup data for existing column, re-create column and move data back. Removes 'columnOld' in case of new name
     * for the column was applied.
     *
     * @param Varien_Db_Adapter_Interface $conn
     * @param                             $table
     * @param                             $column
     * @param                             $columnDef
     * @param null                        $columnOld old name for the column
     */
    function prxgt_install_recreate_column(Varien_Db_Adapter_Pdo_Mysql $conn, $table, $column, $columnDef, $columnOld = null) {
        $columnTmp = $column . '_tmp';
        $fetched = $conn->fetchAll("SELECT * FROM $table LIMIT 1");

        // analyze old named column data
        $oldColumnExists = (!is_null($columnOld) && is_array($fetched) && isset($fetched[0]) && array_key_exists($columnOld, $fetched[0]));
        // analyze current column data
        $columnExists = (is_array($fetched) && isset($fetched[0]) && array_key_exists($column, $fetched[0]));
        // create backup column and backup data
        if($columnExists || $oldColumnExists) {
            $conn->addColumn($table, $columnTmp, $columnDef);
            if($oldColumnExists) {
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
        if($columnExists || $oldColumnExists) {
            // restore existed data
            $conn->query("UPDATE  $table SET $column = $columnTmp");
            $conn->dropColumn($table, $columnTmp);
        }
        // drop old column (for case of empty table)
        if(!is_null($columnOld) && ($oldColumnExists) && ($columnOld != $column)) {
            $conn->dropColumn($table, $columnOld);
        }
    }
}

if(!function_exists('prxgt_install_create_index')) {

    /**
     * Create simple index.
     *
     * @param $conn  Varien_Db_Adapter_Interface
     * @param $table string Table name.
     * @param $fields array Fields names to include in index.
     */
    function prxgt_install_create_index(Varien_Db_Adapter_Interface $conn, $table, $fields) {
        $ndxName = $conn->getIndexName($table, $fields, Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);
        $conn->addIndex($table, $ndxName, $fields, Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);
    }
}

if(!function_exists('prxgt_install_create_index_unique')) {

    /**
     * Create unique index.
     *
     * @param $conn  Varien_Db_Adapter_Interface
     * @param $table string Table name.
     * @param $fields array Fields names to include in index.
     */
    function prxgt_install_create_index_unique(Varien_Db_Adapter_Interface $conn, $table, $fields) {
        $ndxName = $conn->getIndexName($table, $fields, Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);
        $conn->addIndex($table, $ndxName, $fields, Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);
    }
}

if(!function_exists('prxgt_install_create_foreign_key')) {

    /**
     * Create foreign key.
     *
     * @param Varien_Db_Adapter_Interface $conn
     * @param                             $priTableName
     * @param                             $priColumnName
     * @param                             $refTableName
     * @param                             $refColumnName
     * @param string                      $onDelete
     * @param string                      $onUpdate
     */
    function prxgt_install_create_foreign_key(
        Varien_Db_Adapter_Interface $conn,
        $priTableName,
        $priColumnName,
        $refTableName,
        $refColumnName,
        $onDelete = Varien_Db_Adapter_Interface::FK_ACTION_RESTRICT,
        $onUpdate = Varien_Db_Adapter_Interface::FK_ACTION_RESTRICT
    ) {
        $fkName = $conn->getForeignKeyName($priTableName, $priColumnName, $refTableName, $refColumnName);
        $conn->addForeignKey($fkName, $priTableName, $priColumnName, $refTableName, $refColumnName, $onDelete, $onUpdate);
    }
}