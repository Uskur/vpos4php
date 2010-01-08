<?php

require_once("Dahius/VirtualPos.php");

$vpos = new Dahius_VirtualPos("/etc/vpos/config.yml");

$request = new Dahius_VirtualPos_Request("garanti", "3dpay");

$request->setCardHolder("Hasan Ozgan");
$request->setCardNumber("5623-23XX-XXXX-0987");
$request->setCVC("456");
$request->setExpire(12, 2009);
$request->setAmount(999.33);
$request->setInstallment(2);
$request->setOrderId("ffasd98f7asd9f");

$vpos->provision($request);
$vpos->sale($request);
$vpos->reversal($request);
$vpos->disposal($request);
$vpos->refusal($request);
$vpos->get_point($request);
