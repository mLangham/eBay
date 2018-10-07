<?php

    require $_SERVER['DOCUMENT_ROOT']."/API/eBay/SLeBayAPI.class.php";
    $eBayAPI = new SLeBayAPI();

	// make call, the SL eBay API call will return a PHP formatted array
	$eBayTime = $eBayAPI -> GeteBayOfficialTime();

	// echo the data back
	echo $eBayTime['Timestamp'];

?>