<?php

namespace FK;

/**
 * ===================================================================
 * License: see lib/info/LICENSE.md
 * Terms: https://kantaro-cgi.com/terms/
 * Url: https://kantaro-cgi.com/program/formkit/
 * Distributor: kazaoki lab.
 * ===================================================================
 */

require_once dirname(__FILE__).'/../lib/core.php';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
	csrf_check();
	validation(true);
	submit();
	csrf_clear();
	header('location: '.(@$Config['finish_url'] ? $Config['finish_url'] : $_SERVER['REQUEST_URI']), true, 303);
	exit;
}
echo_mode('view');
