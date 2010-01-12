<?php

    session_start();

    require_once "Dahius/VirtualPos/Loader.php";
    $path = realpath(dirname(__FILE__));

    $vpos = new Dahius_VirtualPos("$path/etc/vpos/config.yml");

    $request = new Dahius_VirtualPos_Request();
    $request->isThreeDSecure = true;
    $request->cardHolder = "Steve Jobs";
    $request->cardNumber = "4253-6789-2345-9876";
    $request->cvc = 454;
    $request->expireMonth = 1;
    $request->expireYear = 2011;
    $request->amount = 10.67;
    $request->currency = "TRL"; // TRL, USD, EUR
    $request->installment = 5;
    $request->orderId = md5(uniqid(rand(), true)); // Your order id

    var_dump($request->binNumber,       // 425367
             $request->secureNumber,    // 4253-68**-****-9876
             $request->cardType);       // visa

    $adapter = $vpos->factory("bonus");

    $response = $adapter->provision($request);
    if (!$response->succeed) {
        throw new Exception($response->message);
    }

    if ($request->isThreeDSecure) {
        $_SESSION["__VirtualPOS__"] = serialize($request);
        die($response->message);
    }
    else {
        var_dump($response);
        echo "SUCCESS";
    }

    /**
     *  Adapter Features
     *  ---------------------------------- 
        $adapter->provision($request);
        $adapter->sale($request);
        $adapter->reversal($request);
        $adapter->disposal($request);
        $adapter->refusal($request);
    */
     

