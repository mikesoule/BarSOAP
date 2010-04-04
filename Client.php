<?php
$soapClient = new SoapClient('http://barsoap.dev/bar.wsdl.xml');

$menu = $soapClient->GetMenu();
foreach ($menu->Drinks as $drink) {
    echo "<p>$drink</p>";
}

echo "<h3>" . $soapClient->GetDrink(31) . "</h3>";