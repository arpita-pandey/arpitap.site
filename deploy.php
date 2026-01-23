<?php
// Basic deployment script
$output = shell_exec('cd /var/www/arpitap.site/public_html && git pull 2>&1');
echo "<pre>$output</pre>";
?>
