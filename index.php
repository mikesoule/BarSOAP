<?php
// include the Bar class
require_once dirname(__FILE__) . '/Bar.php';

// initialize SOAP server with WSDL
$server = new SoapServer(dirname(__FILE__) . '/bar.wsdl');

// set handler object
$server->setObject(new Bar());

// handle and respond
$server->handle();
