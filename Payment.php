<h1>TEST</h1>

<?php
////////////////////// global strings ///////////////////////
$url = 'https://apitest.cybersource.com/pts/v2/payments';
$host_name="apitest.cybersource.com";
$SECRET_KEY = "pz49QA6lRc01UU8DKTouMc5AI8gbKiKvoFmbfWj1Sl8=";
$API_KEY_ID = "88e67a79-e01d-4238-a37c-b8d675296394";
$payment_path ="/pts/v2/payments";
////////////////////////////////////////////////////////////
////////////////////// FUNCTIONS //////////////////////////
//////////////////////////////////////////////////////////
function getEncodedSignature($hostName,$digestEncoded,$SS_KEY,$path)
{
    $signatureString =  "host: ".$hostName."\n(request-target): post ".$path."\n"."digest: SHA-256=".$digestEncoded."\n"."v-c-merchant-id: starlock01";
    $signatureByteString = utf8_encode($signatureString);
    $decodeKey = base64_decode($SS_KEY);
    $signature = base64_encode(hash_hmac("sha256", $signatureByteString,
    $decodeKey, true));
    return $signature;
}
function getEncodedDigest($json_array)
{
    $utf8EncodedString = utf8_encode($json_array); // bytes
    $digestEncode = hash("sha256", $utf8EncodedString, true);  // hash
    $DigestEncoded= base64_encode($digestEncode); // encode in base64
    return $DigestEncoded;
}
function sendDataToUrl($hostName,$destinyURL,$rest_body,$API_KEY,$SS_KEY,$path)
{
    //// header data
    $payload = json_encode($rest_body); // encode string into a json format
    $headerdata = array(
    'Content-Type: application/json',
    'v-c-date: '.DateTimeInterface::RFC1123,
    'digest: SHA-256='.getEncodedDigest($payload),
    'Host: apitest.cybersource.com',
    'signature: keyid="'.$API_KEY.'", algorithm="HmacSHA256", headers="host (request-target) digest v-c-merchant-id", signature="'.getEncodedSignature($hostName,getEncodedDigest($payload),$SS_KEY,$path).'"',
    'v-c-merchant-id: starlock01');

    $ch=curl_init($destinyURL); // set url
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // set post request
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // send the json format into the rest body
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headerdata); // send the header data
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec($ch); // excute the POST and obtain the result for the request (HEADER+BODY)
    //-
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($result, 0, $header_size);
    $body = substr($result, $header_size);
    $headers = explode("\n", $header);
    //-
    curl_close($ch); // closes the curl
    return $body; // return only the body request
}
//////////////////////// REQUESTS BODIES //////////////////////
$test_payment = array(
    "clientReferenceInformation" => array("code"=>"TC50171_3"),
    "paymentInformation" =>array(
            "card"=>array(
                "number"=>"4111111111111111",
                "expirationMonth"=>"12",
                "expirationYear"=>"2031")),
    "orderInformation"=>array(
        "amountDetails"=>array(
            "totalAmount"=>
            "102.21",
            "currency"=>"USD"),
        "billTo" => array (
            "firstName" => "John",
            "lastName" => "Doe",
            "address1" => "1 Market St",
            "locality" =>  "san francisco",
            "administrativeArea" =>  "CA",
            "postalCode" => "94105",
            "country" => "US",
            "email" => "test@cybs.com",
            "phoneNumber"=>"4158880000"))
);
$RESPONSE = sendDataToUrl($host_name,$url,$test_payment, $API_KEY_ID,$SECRET_KEY,$payment_path); // obtain the object
$json_data =json_decode($RESPONSE); // decode into json
//echo $json_data->paymentAccountInformation->card->type; // print the value
//echo $json_data->status;
if($json_data->status==='AUTHORIZED')
{
    echo "Su pago ha sido autorizado";
}
else if($json_data->status==='DECLINED')
{
    echo "Su pago ha sido declinado";
}
?>