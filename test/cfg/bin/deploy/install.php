<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Start DB re-creation manually.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
include_once('Installer.php');

Praxigento\Deploy\Installer::postInstall(null);