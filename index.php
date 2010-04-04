<?php
// include the Bar class
require_once 'Bar.php';

// ensure that the request is populated regardless of execution environment
if (empty($request)) {
    $request = empty($HTTP_RAW_POST_DATA) ? file_get_contents('php://input') : $HTTP_RAW_POST_DATA;
}

// initialize SOAP server with WSDL
$server = new SoapServer('bar.wsdl.xml');

// set handler object
$server->setObject(new Bar());

// handle and respond
$server->handle($request);
