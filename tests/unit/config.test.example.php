<?php
ini_set("memory_limit","12M");

require_once 'MDB2.php';
define('DB_HOST', 'localhost');
define('DB_PASS', '');
define('DB_USER', 'root');
define('DB_NAME', 'pear');
define('DB_DSN', 'mysql://root:@localhost/pear');
define('PATH_ROOT', dirname(__FILE__) . '/../../');
define('PATH_INCLUDE_CONFIG', PATH_ROOT . 'Intraface/config/');
define('XMLRPC_PATH', PATH_ROOT . 'intraface.dk/xmlrpc/');
define('PATH_INCLUDE_MODULE', PATH_ROOT . 'Intraface/modules/');
define('PATH_INCLUDE_SHARED', PATH_ROOT . 'Intraface/shared/');
define('CONNECTION_INTERNET', 'ONLINE');
define('PATH_UPLOAD', PATH_ROOT . 'upload/');
define('PATH_UPLOAD_TEMPORARY', 'tempdir/');
define('FILE_VIEWER', '');
define('PATH_WWW', '');
define('IMAGE_LIBRARY', 'GD');
define('XMLRPC_SERVER_URL', 'http://intraface.dk/xmlrpc/');

// @todo what is the next thing used for
define('TEST_PATH_TEMP', '/var/lib/www/intraface_test/tmp/');

set_include_path(PATH_ROOT. PATH_SEPARATOR . get_include_path());

$db = MDB2::singleton(DB_DSN);
$db->setOption('debug', 0);
$db->setOption('portability', MDB2_PORTABILITY_NONE);

if ($db->getOption('debug')) {
    $db->setOption('log_line_break', "\n\n\n\n\t");

    require_once 'MDB2/Debug/ExplainQueries.php';

    $my_debug_handler = new MDB2_Debug_ExplainQueries($db);
    $db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));

    register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
    register_shutdown_function(array($my_debug_handler, 'dumpInfo'));
}