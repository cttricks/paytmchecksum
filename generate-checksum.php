<?php
$CALLBACK_URL = "https://pguat.paytm.com/paytmchecksum/paytmCallback.jsp";
$MERCHANT_KEY = "MERCHANT_KEY";

$paramList = array();
$paramList["MID"] = "MERCHANT _ID";
$paramList["INDUSTRY_TYPE_ID"] = "Retail";
$paramList["CHANNEL_ID"] = "WEB";
$paramList["WEBSITE"] = "WEBSTAGING";

$paramList["ORDER_ID"] = "ORDER".time();
$paramList["CUST_ID"] = "CUST".time();
$paramList["CALLBACK_URL"] = $CALLBACK_URL;

function getChecksumFromArray($arrayList, $key, $sort=1) {
	if ($sort != 0) {
		ksort($arrayList);
	}
	
	$str = getArray2Str($arrayList); 
	$salt = generateSalt_e(4);
	$finalString = $str . "|" . $salt;
	$hash = hash("sha256", $finalString);
	$hashString = $hash . $salt;
	$checksum = encrypt_e($hashString, $key);
	return $checksum;
}

function getArray2Str($arrayList) {
	$findme   = 'REFUND';
	$findmepipe = '|';
	$paramStr = "";
	$flag = 1;	
	foreach ($arrayList as $key => $value) {
		$pos = strpos($value, $findme);
		$pospipe = strpos($value, $findmepipe);
		if ($pos !== false || $pospipe !== false) 
		{
			continue;
		}
		
		if ($flag) {
			$paramStr .= checkString_e($value);
			$flag = 0;
		} else {
			$paramStr .= "|" . checkString_e($value);
		}
	}
	return $paramStr;
}

function generateSalt_e($length) {
	$random = "";
	srand((double) microtime() * 1000000);

	$data = "AbcDE123IJKLMN67QRSTUVWXYZ";
	$data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
	$data .= "0FGH45OP89";

	for ($i = 0; $i < $length; $i++) {
		$random .= substr($data, (rand() % (strlen($data))), 1);
	}

	return $random;
}

function encrypt_e($input, $ky) {
	$key   = html_entity_decode($ky);
	$iv = "@@@@&&&&####$$$$";
	$data = openssl_encrypt ( $input , "AES-128-CBC" , $key, 0, $iv );
	return $data;
}

function checkString_e($value) {
	if ($value == 'null')
		$value = '';
	return $value;
}

$response = array("status"=>401,"msg"=>"method not allowed");
if(isset($_GET['amount']) && ctype_digit($_GET['amount'])){
	$paramList["TXN_AMOUNT"] = $_GET['amount'];
    $checkSum = getChecksumFromArray($paramList, $MERCHANT_KEY);
	$response = array(
		"status" => 200,
	    "mid" => $paramList["MID"],
	    "oid" => $paramList["ORDER_ID"],
	    "cid" => $paramList["CUST_ID"],
		"amount" => $paramList["TXN_AMOUNT"],
	    "checksum" => $checkSum
	);
}else{
	$response['msg'] = "invalid amount";
}

echo json_encode($response);
?>
