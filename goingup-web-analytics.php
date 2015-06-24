<?php
/*
Plugin Name: GoingUp! Web Analytics
Plugin URI: http://www.goingup.com/
Description: GoingUp! Web Analytics is an advanced website traffic, SEO, and visitor analytics application which offers comprehensive visitor activity as well as search engine optimization and site ranking. Free to use, start GoingUp! today!.
Author: GoingUP!
Version: 4.1.1

*/

function gustats_getcontent($url,$user_agent=''){

	$tries = 0;
	$maxTries = 5;
	$repeat = true;

	$rtn = array(
		'http_code' => null,
		'http_hreader' => null,
		'body' => null,
		'error' => null,
	);

	do {
		$tries++;
		if ($tries >= $maxTries) {
			$repeat = false;
			break;
		}

		if (function_exists('curl_exec')){

			$chx = curl_init();
			curl_setopt($chx, CURLOPT_URL, $url);
			curl_setopt($chx, CURLOPT_HEADER, 0);
			curl_setopt($chx, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($chx, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($chx, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($chx, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($chx, CURLOPT_TIMEOUT, 60);
			curl_setopt($chx, CURLOPT_USERAGENT, ($user_agent ? $user_agent : "Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0"));
			curl_setopt($chx, CURLOPT_VERBOSE, 0);
			curl_setopt($chx, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($chx, CURLOPT_SSL_VERIFYPEER, FALSE);

			$result = @curl_exec($chx);
			$curl_info = curl_getinfo($chx);
			curl_close($chx);

			if ($curl_info && $curl_info['http_code'] == 200 && $result) {
				$repeat = false;
				$rtn['http_code'] = 200;
				$rtn['body'] = $result;
			} else {
				if ($curl_info) {
					$rtn['error'] = 'Response Code: '.$curl_info['http_code'].'<br/>Content: '.($result ? $result : 'empty');
				} else {
					$rnt['error'] = 'Timedout';
				}
			}

		} else {

			// Create a stream
			$opts = array(
				'http'=>array(
					'method'=>"GET",
					'header'=>"Cache-Control:no-cache,no-store\r\n".
							  "Connection:close\r\n"
				)
			);
			$context = stream_context_create($opts);
			// Open the file using the HTTP headers set above
			$file = file_get_contents($url, false, $context);
			$rtn['error'] = 'Content: '.$file;
			if($http_response_header) {
				foreach ($http_response_header as $header) {
					if ($header == "HTTP/1.1 200 OK") {
						$repeat = false;
						$rtn['http_code'] = 200;
						$rtn['body'] = $file;
					}
				}
			}
		}
	} while ($repeat);
    return $rtn;}


function gustats_set(){
	global $plugin_page;
	global $api_key;
	global $ws_key;
	global $widget_codes;
	?>
	<div class="wrap">
	<?php
	if(!empty($_POST[api_key]) AND !empty($_POST[ws_key])){
		$res = gustats_getcontent("http://www.goingup.com/xml/serialized.php?api=".$_POST['api_key']."&ws=".$_POST['ws_key']."&rand=".mt_rand());
		if ($res['http_code']!='200'){
			echo "<center><span style='color:red;'><strong>" . __("There is communication problem with www.goingup.com") . "</strong></span><br/></center><br/><br/>";
			if ($res['error']) {
				echo "Error Details<br/>".$res['error'];
			}
		}else{
			$fcont = $res['body'];
			$web_details = unserialize($fcont);

			if($web_details){
				update_option('gstats_api_key', $_POST[api_key]);
				update_option('gstats_ws_key', $_POST[ws_key]);
				update_option('gstats_widget_codes', $_POST[ws_widget_codes]);
				$api_key = $_POST[api_key];
				$ws_key = $_POST[ws_key];
				$widget_codes = stripslashes($_POST[ws_widget_codes]);
				echo "<center><span style='color:blue;'><strong>" . __("API Key, Site ID and Widget Codes are saved!") . "</strong></span><br/></center>";
			}else{
				echo "<center><span style='color:red;'><strong>" . __("Wrong API Key or Site ID, please check it!") . "</strong></span><br/></center>";
			}
		}
	} else {
    	$api_key = get_option('gstats_api_key');
    	$ws_key = get_option('gstats_ws_key');
    	$widget_codes = stripslashes(get_option('gstats_widget_codes'));
	}
	?>
		<h2><?php _e('GoingUP! Web Analytics'); ?></h2>
		<div class="narrow">
			<form action="options-general.php?page=<?php echo $plugin_page; ?>" method="post">
				<p><?php _e('Not GoingUp? <a href="http://www.goingup.com/signup.html" target="_blank"> Click here</a> to create a free account'); ?></p>
				<label for="api_key"><?php _e('API Key:'); ?> <input style='width:285px' type="text" name="api_key" id="api_key" value="<?php echo $api_key; ?>" /></label>
				<label for="ws_key"><?php _e('Site ID:'); ?> <input style='width:95px' type="text" name="ws_key" id="ws_key" value="<?php echo $ws_key; ?>" /></label></br>
				<label for="ws_widget_codes"><?php _e('Widget Codes:'); ?></br><textarea style='width:481px' rows='10' name="ws_widget_codes" id="ws_widget_codes"><?php echo $widget_codes; ?></textarea></label>
				<p class="submit"><input type="submit" value="<?php _e('Save &raquo;'); ?>" /></p>
			</form>
		</div>
	</div>
<?php
}

function gustats_admin_menu(){
	add_submenu_page('options-general.php', __('GoingUp! Web Analytics'), __('GoingUp! Web Analytics'), 'manage_options', 'gustats', 'gustats_set');
	add_option('gstats_api_key', '', 'GoingUp! stats api key');
	add_option('gstats_ws_key', '', 'GoingUp! stats ws key');
	add_option('gstats_widget_codes', '', 'GoingUp! widget codes');
}

function gustat_footer(){
	$workdir = get_option('siteurl')."/".PLUGINDIR."/goingup-web-analytics/";
	$api_key = get_option('gstats_api_key');
	$ws_key = get_option('gstats_ws_key');
	$widget_codes = stripslashes(get_option('gstats_widget_codes'));

	if (isset($ws_key) and isset($api_key))	{
		$gu_track_host = urlencode("http://".$_SERVER['HTTP_HOST']);
		$gu_track_referrer = urlencode($_SERVER['HTTP_REFERER']);
		$gu_track_ipadress = urlencode($_SERVER['REMOTE_ADDR']);
		$gu_track_agent = urlencode($_SERVER['HTTP_USER_AGENT']);
		$gu_track_uri = urlencode($_SERVER['REQUEST_URI']);
		$gu_track_websiteid = $ws_key;
		$gu_track_urlparams = "st=".$gu_track_websiteid."&vip=".$gu_track_ipadress."&cur=".$gu_track_host.$gu_track_uri."&ref=".$gu_track_referrer."&ua=".$gu_track_agent."&b=5&rand=".mt_rand();
		$res = gustats_getcontent("http://counter.goingup.com/visitor.php?".$gu_track_urlparams);
		echo "<div style=\"display:none\">".$res['body']."</div>";

	}
}

function gustat_head(){
    $api_key = get_option('gstats_api_key');
    $ws_key = get_option('gstats_ws_key');
    $widget_codes = stripslashes(get_option('gstats_widget_codes'));

    if (isset($ws_key) and isset($api_key))	{
        echo $widget_codes;

    }
}
/* settings link in plugin management screen */
function gustats_admin_menu_link($actions, $file) {	
	if(stripos($file, 'goingup-web-analytics') !== false) {
		$actions['settings'] = '<a href="options-general.php?page=gustats">Settings</a>';
	}
	return $actions;
}
add_filter('plugin_action_links', 'gustats_admin_menu_link', 2, 2);
add_action('admin_menu', 'gustats_admin_menu');
add_action('wp_footer', 'gustat_footer');
add_action('wp_head', 'gustat_head');
