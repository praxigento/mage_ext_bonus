<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
/**
 * User: Vladimir Golobokov <vladimirg@inbox.lv>
 */

namespace Praxigento\Test\Module;

use Composer\Script\Event;
use PDO;

class Installer {

    public static function postInstall(Event $event) {
        $composer = $event->getComposer();

        $dbh = new PDO('mysql:host=localhost;dbname=mage_module_test', 'mage_module_test', 'mage_module_test_pass');

        $dbh->exec("DROP PROCEDURE IF EXISTS `drop_all_tables`;");

        $dbh->exec("CREATE PROCEDURE `drop_all_tables`()\n" .
                   "BEGIN\n" .
                   "DECLARE _done INT DEFAULT FALSE;\n" .
                   "DECLARE _tableName VARCHAR(255);\n" .
                   "DECLARE _cursor CURSOR FOR\n" .
                   "SELECT table_name \n" .
                   "FROM information_schema.TABLES \n" .
                   "WHERE table_schema = SCHEMA();\n" .
                   "DECLARE CONTINUE HANDLER FOR NOT FOUND SET _done = TRUE;\n" .

                   "SET FOREIGN_KEY_CHECKS = 0;\n" .

                   "OPEN _cursor;\n" .

                   "REPEAT FETCH _cursor INTO _tableName;\n" .

                   "IF NOT _done THEN\n" .
                   "SET @stmt_sql = CONCAT('DROP TABLE ', _tableName);\n" .
                   "PREPARE stmt1 FROM @stmt_sql;\n" .
                   "EXECUTE stmt1;\n" .
                   "DEALLOCATE PREPARE stmt1;\n" .
                   "END IF;\n" .

                   "UNTIL _done END REPEAT;\n" .

                   "CLOSE _cursor;\n" .
                   "SET FOREIGN_KEY_CHECKS = 1;\n" .
                   "END\n");

        $dbh->exec("call drop_all_tables();");

        $_SERVER['argv'] = array(
            'Installer.php',
            "--license_agreement_accepted",
            "yes",
            "--locale",
            "en_US",
            "--timezone",
            "America/Los_Angeles",
            "--default_currency",
            "USD",
            "--db_host",
            "localhost",
            "--db_name",
            "mage_module_test",
            "--db_user",
            "mage_module_test",
            "--db_pass",
            "mage_module_test_pass",
            "--db_prefix",
            "magento_",
            "--url",
            "http://mage_module_test.prxgt.com",
            "--use_rewrites",
            "yes",
            "--use_secure",
            "no",
            "--secure_base_url",
            "",
            "--use_secure_admin",
            "no",
            "--admin_lastname",
            "Owner",
            "--admin_firstname",
            "Store",
            "--admin_email",
            "test@prxgt.com",
            "--admin_username",
            "admin",
            "--admin_password",
            "qwerty1234",
            "--skip_url_validation",
            "yes"
        );
        require 'mage/install.php';
    }

}
