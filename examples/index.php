<?php

require_once "Dahius/VirtualPos/Loader.php";

$path = realpath(dirname(__FILE__));
$vpos = new Dahius_VirtualPos("$path/etc/vpos/config.yml");
$adapter = $vpos->factory("bonus");

$request = new Dahius_VirtualPos_Request();

$request->setType("3dpay");
$request->setCardHolder("Hasan Ozgan");
$request->setCardNumber("5623-23XX-XXXX-0987");
$request->setCVC("456");
$request->setExpireDate(12, 2009);
$request->setAmount(999.33);
$request->setInstallment(2);
$request->setOrderId("ffasd98f7asd9f");


$response = $adapter->authenticate($request);
var_dump($response, $adapter);
die();
$adapter->provision($request);
$adapter->sale($request);
$adapter->reversal($request);
$adapter->disposal($request);
$adapter->refusal($request);
//$adapter->get_point($request);
 

