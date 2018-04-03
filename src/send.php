<?php
require __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;

//If this is a post request
if(!empty($_POST['foo']))
{

    echo "SLACK APP TOKEN: \n". $_ENV["SLACK_APP_TOKEN"];

    var_dump($_POST);

    function getUrl($url)
    {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch); 
        curl_close($ch);   
        return json_decode($result);    
    }

    function sendSMS($destinationNumber, $text)
    {
        $sid = $_ENV["TWILIO_ACCOUNT_ID"];
        $token = $_ENV["TWILIO_AUTH_TOKEN"];
        $client = new Client($sid, $token);

        $client->messages->create(
            $destinationNumber,
            array(
                'from' => $_ENV["TWILIO_PHONE_NUMBER"],
                'body' => $text
            )
        );
    }

    function normalizeNumber($originalPhone)
    {
        $onlyNumbers = preg_replace('[\D]', '', $originalPhone);
        return "+1".$onlyNumbers;
    }

    function getUserId($text)
    {
        $result = str_replace("@", "", str_replace(">", "", str_replace("<", "", $text)));

        if (strpos($result, "|") > 0)
        {   
            $result = substr_replace($result,"", strpos($result, "|") );
        }
        return $result;
    }

    $parts = explode(" ", $_POST["text"], 2);

    $userId  = getUserId($parts[0]);

    $profileUrl = "https://slack.com/api/users.profile.get?token=".$_ENV["SLACK_APP_TOKEN"]."&user=".$userId;

    $result = getUrl($profileUrl);

    echo "PROFILE REQUEST RESULT: \n";
    var_dump($result);

    $sanitizedPhone = normalizeNumber($result->profile->phone); 
    sendSMS($sanitizedPhone, $parts[1] );
}
else
{
    echo "Bad request";
}
?>