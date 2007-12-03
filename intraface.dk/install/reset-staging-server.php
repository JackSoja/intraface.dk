<?php
require_once '../common.php';

$install_class = PATH_ROOT.'install/Install.php';
if(!file_exists($install_class)) {
    trigger_error('The install class is not present. Probably because you should not run it now!', E_USER_ERROR);
    exit;
}
require $install_class;

$install = new Intraface_Install;

if ($install->resetServer()) {
    
    if(!empty($_GET['modules'])) {
        $install->grantModuleAccess($_GET['modules']);
    }
    
    if(!empty($_GET['helper_function'])) {
        $install->runHelperFunction($_GET['helper_function']);
    }
    
    if(!empty($_GET['login'])) {
        if($install->loginUser()) {
            header('location: '.PATH_WWW.'/main/index.php');
            exit;
        }
        else {
            echo 'Error in login';
        }
    }
    echo 'staging server reset. Go to <a href="../main/login.php">login</a>.';
}
else {
    echo 'error';
}


?>