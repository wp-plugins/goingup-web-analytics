<?php
/*
Plugin Name: GoingUp! Web Analytics
Plugin URI: http://www.goingup.com/
Description: GoingUp! Web Analytics is an advanced website traffic and visitor analytics application which offers comprehensive visitor activity as well as search engine optimization and site ranking. Free to use, start GoingUp! today!.
Author: GoingUP!
Version: 3.5.1

Requires WordPress 3.5.1 or later. Not for use with WPMU.
*/

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


function gustats_set(){
	global $plugin_page;
	global $api_key;
	$api_key = get_option('gstats_api_key');
	global $ws_key;
	$ws_key = get_option('gstats_ws_key');
	?>
	<div class="wrap">
	<?php
	if(!empty($_POST[api_key]) AND !empty($_POST[ws_key])){
		$res = gustats_getcontent("http://www.goingup.com/xml/serialized.php?api=".$_POST['api_key']."&ws=".$_POST['ws_key']);
		if ($res['http_code']!='200'){
			echo "<center><span style='color:red;'><strong>" . __("There is communication problem with www.goingup.com") . "</strong></span><br/></center>";
		}else{			
			$fcont = $res['body'];
			$web_details = unserialize($fcont);
			
			if($web_details){
				update_option('gstats_api_key', $_POST[api_key]);
				update_option('gstats_ws_key', $_POST[ws_key]);
				$api_key = $_POST[api_key];
				$ws_key = $_POST[ws_key];
				echo "<center><span style='color:blue;'><strong>" . __("API key and Site Id are saved!") . "</strong></span><br/></center>";
			}else{
				echo "<center><span style='color:red;'><strong>" . __("Wrong API key or Site Id, please check it!") . "</strong></span><br/></center>";
			}
		}
	}
	?>
		<h2><?php _e('GoingUP! Web Analytics'); ?></h2>
		<div class="narrow">
			<form action="options-general.php?page=<?php echo $plugin_page; ?>" method="post">
				<p><?php _e('Not GoingUp? <a href="http://www.goingup.com/signup.html" target="_blank"> Click here</a> to create a free account'); ?></p>
				<label for="api_key"><?php _e('API Key:'); ?> <input style='width:285px' type="text" name="api_key" id="api_key" value="<?php echo $api_key; ?>" /></label>
				<label for="ws_key"><?php _e('Site ID:'); ?> <input style='width:95px' type="text" name="ws_key" id="ws_key" value="<?php echo $ws_key; ?>" /></label>
				<p class="submit"><input type="submit" value="<?php _e('Save &raquo;'); ?>" /></p>
			</form>
		</div>
	</div>
<?php
}

function gustats_admin_menu(){
	add_submenu_page('options-general.php', __('GoingUp! Web Analytics'), __('GoingUp! Web Analytics'), 'manage_options', 'gustats', 'gustats_set');
	add_option('gstats_api_key', '', 'GoingUp! API Key');
	add_option('gstats_ws_key', '', 'GoingUp! Site ID');
}

function gustat_footer(){
	$workdir = get_option('siteurl')."/".PLUGINDIR."/goingup-web-analytics/";
	$api_key = get_option('gstats_api_key');	
	$ws_key = get_option('gstats_ws_key');
	
	if (isset($ws_key) and isset($api_key))	{
		$gu_track_host = urlencode("http://".$_SERVER['HTTP_HOST']);
		$gu_track_referrer = urlencode($_SERVER['HTTP_REFERER']);
		$gu_track_ipadress = urlencode($_SERVER['REMOTE_ADDR']);
		$gu_track_agent = urlencode($_SERVER['HTTP_USER_AGENT']);
		$gu_track_uri = urlencode($_SERVER['REQUEST_URI']);
		$gu_track_websiteid = $ws_key;
		$gu_track_urlparams = "st=".$gu_track_websiteid."&vip=".$gu_track_ipadress."&cur=".$gu_track_host.$gu_track_uri."&ref=".$gu_track_referrer."&ua=".$gu_track_agent."&b=5";
		$res = gustats_getcontent("http://counter.goingup.com/phptrack.php?".$gu_track_urlparams);
		echo "<div style=\"display:none\">".$res['body']."</div>";
	
	}
}
add_action('admin_menu', 'gustats_admin_menu');
add_action('wp_footer', 'gustat_footer');
