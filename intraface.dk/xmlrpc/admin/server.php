<?php
require_once '../../common.php';
XML_RPC2_Backend::setBackend('php');
$HTTP_RAW_POST_DATA = file_get_contents('php://input');

$options = array(
    'prefix' => 'intraface.', 
    'encoding' => 'utf-8');

$server = XML_RPC2_ServerFixedEncodingObject::create(new Intraface_XMLRPC_Admin_Server(), $options);
$server->handleCall();