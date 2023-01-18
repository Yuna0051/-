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
validation(@$Config['before_validates']);
csrf_generate();
echo_mode('input');
