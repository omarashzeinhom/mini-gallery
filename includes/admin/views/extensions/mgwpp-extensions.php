<?php
if (!defined('ABS_PATH')) {
    exit;
}

define('WEBSERVICE', 'https://api.remush.it/ws.php?img=');



class MGWPP_Extensions
{



    public function __construct() {}



    public function mgwpp_turn_external_extensions_(){

    }


    // Request Smush Method
    public function mgwpp_smush_optimize_all_images()
    {
        /**
         * @var string $mgwpp_smush_service = image links from wp directory 
         * @var string mgwpp_smush_context = creating context var 
         * @var json $mgwpp_smush_o json decoded string request
         * 
         * https://resmush.it/api
         */

        $mgwpp_smush_service = ''; // image should be added here any image basically uploaded to the mini gallery plugin 


        // Creating A Stream Context to Include Custom Headers
        $mgwpp_smush_context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: MyCustoUserAgent/1.0\r\n" . "Referer: https://example.com\r\n "
            ]
        ]);

        // Fetching Data With the Custom Headers
        $mgwpp_o = json_decode(file_get_contents(WEBSERVICE . $mgwpp_smush_service, false, $mgwpp_smush_context));

        if (isset($mgwpp_o->error)) {
            die('Error:' . $mgwpp_o->error);
        }
        echo $mgwpp_o->dest;
    }

    // Send Files to Smush API 

    public function mgwpp_send_smush_optimize_all_images(){
        $mgwpp_file = '';
        $mgwpp_mime = mime_content_type($mgwpp_file);
        $mgwpp_info = pathinfo($mgwpp_file);
        $mgwpp_name = $mgwpp_info['basename'];
        $mgwpp_output = new CURLFile($mgwpp_file, $mgwpp_mime, $mgwpp_name);
    
        $mgwpp_smush_data= array(
            "files" => $mgwpp_output,
        );

        $mgwpp_ch = curl_init();

        curl_setopt($mgwpp_ch, CURLOPT_URL, 'https://api.resmush.it/?qlty=80');
        curl_setopt($mgwpp_ch, CURLOPT_URL, 1);
        curl_setopt($mgwpp_ch, CURLOPT_URL, 1);
        curl_setopt($mgwpp_ch, CURLOPT_URL, 5);
        curl_setopt($mgwpp_ch, CURLOPT_URL, $mgwpp_smush_data);
        curl_setopt($mgwpp_ch, CURLOPT_URL, "MyCustomUserAgent/1.0");
        curl_setopt($mgwpp_ch, CURLOPT_URL, 'https://example.com');

        $mgwpp_result = curl_exec($mgwpp_ch); 

        if(curl_errno($mgwpp_ch)) {
        $mgwpp_result = curl_error($mgwpp_ch);
        };

        curl_close($mgwpp_ch);

        var_dump($mgwpp_result);
    
    }






}
