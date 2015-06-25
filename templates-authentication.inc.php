<?php

/* This file is referred to in the code as '.ignore.[some name]-authentication.php'
   to avoid committing it to GitHub. Feel free to either tweak the
   require_once() at the start of a script or rename this file to
   match the require_once() call! */

// (nominally) read-only api process
define('CANVAS_API_TOKEN', 'VALID_CANVAS_API_TOKEN_GOES_HERE');
define('CANVAS_API_URL', 'https://canvas.instructure.com/api/v1');

define('MYSQL_HOST', 'localhost');
define('MYSQL_DATABASE', 'cachedb');
define('MYSQL_USER', 'templates');
define('MYSQL_PASSWORD', 's00pers3kr3t');

?>