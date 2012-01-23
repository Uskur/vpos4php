<pre><?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
    session_start();
	set_include_path("../library/");
    require_once "../library/Dahius/VirtualPos/Loader.php";
    $path = realpath(dirname(__FILE__));

    $vpos = new Dahius_VirtualPos("$path/etc/vpos/config.yml");

    $request = unserialize($_SESSION["__VirtualPOS__"]);
    $request->threeDResponse = $_REQUEST;

    $adapter =& $vpos->factory($request->adapter);
    $response = $adapter->complete($request);

    if ($response->succeed) {
        var_dump($response);
        echo "SUCCESS";
    }
    else {
        throw new Exception($response->message);
    }
?></pre>