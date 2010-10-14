<?php

if (!function_exists('gustats_getcontent')){
	function gustats_getcontent($url,$user_agent=''){
		$url_parsed = parse_url($url);
	    if ( empty($url_parsed['scheme']) ) {
	        $url_parsed = parse_url('http://'.$url);
	    }
	    $rtn['url'] = $url_parsed;
	
	    $port = $url_parsed["port"];
	    if ( !$port ) {
	        $port = 80;
	    }
	    $rtn['url']['port'] = $port;
	    
	    $path = $url_parsed["path"];
	    if ( empty($path) ) {
	            $path="/";
	    }
	    if ( !empty($url_parsed["query"]) ) {
	        $path .= "?".$url_parsed["query"];
	    }
	    $rtn['url']['path'] = $path;
	
	    $host = $url_parsed["host"];
	    $foundBody = false;
	
	   
	    
	    $out = "GET $path HTTP/1.0\r\n";
	    $out .= "Host: $host\r\n";
	    if ($user_agent){
	    	$out .= "user-agent: ".$user_agent;
	    }
	    $out .= "Connection: Close\r\n\r\n";
	
	    if ( !$fp = @fsockopen($host, $port, $errno, $errstr, 30) ) {
	        $rtn['errornumber'] = $errno;
	        $rtn['errorstring'] = $errstr;
	        return $rtn;
	    }
	    fwrite($fp, $out);
	    while (!feof($fp)) {
	        $s = fgets($fp, 128);
	        if ( $s == "\r\n" ) {
	            $foundBody = true;
	            continue;
	        }
	        if ( $foundBody ) {
	            $body .= $s;
	        } else {
	            if ( ($followRedirects) && (stristr($s, "location:") != false) ) {
	                $redirect = preg_replace("/location:/i", "", $s);
	                return HttpGet( trim($redirect) );
	            }
	            $header .= $s;
	        }
	    }
	    fclose($fp);
	
	    $__ = explode("\n",$header);
	    $___ = explode(" ",$__[0]);
	    
	    $rtn['header'] = ($header);
	    $rtn['http_code'] = trim($___[1]);
	    $rtn['http_header'] = trim($__[0]);
	    $rtn['body'] = trim($body);
	    return $rtn;
	}
}

if(isset($_GET[apiKey]) and isset($_GET[ws])){
	
$gu_track_host = urlencode("http://".$_SERVER['HTTP_HOST']);
$gu_track_referrer = urlencode($_SERVER['HTTP_REFERER']);
$gu_track_ipadress = urlencode($_SERVER['REMOTE_ADDR']);
$gu_track_agent = urlencode($_SERVER['HTTP_USER_AGENT']);
$gu_track_uri = urlencode($_SERVER['REQUEST_URI']);
$gu_track_websiteid = $_GET['ws'];
$gu_track_urlparams = "st=".$gu_track_websiteid."&vip=".$gu_track_ipadress."&cur=".$gu_track_host.$gu_track_uri."&ref=".$gu_track_referrer."&ua=".$gu_track_agent."&b=5";

$res = gustats_getcontent("http://counter.goingup.com/phptrack.php?".$gu_track_urlparams);
	echo $res['body']; 
}else{
	echo "<table width=300><tr><td colspan=2>&nbsp;</td></tr><tr><td align=center colspan=2>
			<span style='font-size:10px;'>You're not GoingUp! Please configure 
			the SMF plugin for GoingUp! now.</span></td></tr></table>";
}

?>
