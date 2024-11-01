<?php

/**
 * The yocaptcha server URL's
 */
define("YOCAPTCHA_API_SERVER", "http://api.yocaptcha.com");
define("YOCAPTCHA_API_SECURE_SERVER", "https://api.yocaptcha.com");
define("YOCAPTCHA_VERIFY_SERVER", "api.yocaptcha.com");

/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _yocaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}



/**
 * Submits an HTTP POST to a yocaptcha server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function _yocaptcha_http_post($host, $path, $data, $port = 80) {

        $req = _yocaptcha_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: yocaptcha/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}



/**
 * Gets the challenge HTML (javascript and non-javascript version).
 * This is called from the browser, and the resulting yocaptcha HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for yocaptcha
 * @param string $error The error given by yocaptcha (optional, default is null)
 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

 * @return string - The HTML to be embedded in the user's form.
 */
function yocaptcha_get_html ($pubkey, $error = null, $use_ssl = false)
{
	if ($pubkey == null || $pubkey == '') {
		die ("To use yocaptcha you must get an API key from <a href='http://login.yocaptcha.com'>http://login.yocaptcha.com</a>");
	}
	
	if ($use_ssl) {
                $server = YOCAPTCHA_API_SECURE_SERVER;
        } else {
                $server = YOCAPTCHA_API_SERVER;
        }

        $errorpart = "";
        if ($error) {
           $errorpart = "&amp;error=" . $error;
        }
        return '<script type="text/javascript" src="'. $server . '/get.php?k=' . $pubkey . $errorpart . '"></script>

	<noscript>
  		<iframe src="'. $server . '/get.php?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
  		<textarea name="yocaptcha_challenge_field" rows="3" cols="40"></textarea>
  		<input type="hidden" name="yocaptcha_response_field" value="manual_challenge"/>
	</noscript>';
}




/**
 * A YoCaptchaResponse is returned from yocaptcha_check_answer()
 */
class YoCaptchaResponse {
        var $is_valid;
        var $error;
}


/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return YoCaptchaResponse
  */
function yocaptcha_check_answer ($pubkey, $privkey, $remoteip, $challenge, $response, $extra_params = array())
{

	if ($privkey == null || $privkey == '') {
		die ("To use yocaptcha you must get an API key from <a href='http://login.yocaptcha.com'>http://login.yocaptcha.com</a>");
	}

	if ($remoteip == null || $remoteip == '') {
		die ("For security reasons, you must pass the remote ip to yocaptcha");
	}

	
	
        //discard spam submissions
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
                $yocaptcha_response = new YoCaptchaResponse();
                $yocaptcha_response->is_valid = false;
                $yocaptcha_response->error = 'incorrect-captcha';
                return $yocaptcha_response;
        }

        $response = _yocaptcha_http_post (YOCAPTCHA_VERIFY_SERVER, "/verify.php",
                                          array ('k' => $pubkey,
                                                 'private_key' => $privkey,
                                                 'remoteip' => $remoteip,
                                                 'session' => $challenge,
                                                 'answer' => $response
                                                 ) + $extra_params
                                          );


$response = $response[1];

        $answers = explode ('\n', $response);
        $yocaptcha_response = new YoCaptchaResponse();

        if (trim ($answers[0]) == 'passed') {
                $yocaptcha_response->is_valid = true;
        }
        else {
                $yocaptcha_response->is_valid = false;
                $yocaptcha_response->error = $answers[1];
        }
        return $yocaptcha_response;

}

/**
 * gets a URL where the user can sign up for yocaptcha. If your application
 * has a configuration page where you enter a key, you should provide a link
 * using this function.
 * @param string $domain The domain where the page is hosted
 * @param string $appname The name of your application
 */
function yocaptcha_get_signup_url ($domain = null, $appname = null) {
	return "http://login.yocaptcha.com/" .  _yocaptcha_qsencode (array ('domains' => $domain, 'app' => $appname));
}

function _yocaptcha_aes_pad($val) {
	$block_size = 16;
	$numpad = $block_size - (strlen ($val) % $block_size);
	return str_pad($val, strlen ($val) + $numpad, chr($numpad));
}



?>
