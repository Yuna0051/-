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
post_only();
csrf_check();
validation(strlen(@$Config['before_validates']) ? $Config['before_validates'] : true);
echo_mode('view');
$Config['hiddens_tag_all'] = true;
