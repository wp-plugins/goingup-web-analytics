<?php
/*
Plugin Name: GoingUp! Web Analytics
Plugin URI: http://www.goingup.com
Description: GoingUp! Web Analytics is an advanced website traffic and visitor analytics application which offers comprehensive visitor activity as well as search engine optimization and site ranking. Free to use, start GoingUp! today!
Author: GoingUP!
Version: 1.0.4

Requires WordPress 2.1 or later. Not for use with WPMU.
 */

function gustats_set(){
	global $plugin_page;
	$api_key = get_option('gstats_api_key');
	$ws_key = get_option('gstats_ws_key');
	?>
	<div class="wrap">
	<?php
	if(!empty($_POST[api_key]) AND !empty($_POST[ws_key])){
		$fcont = join ('', file ("http://goingup.com/xml/serialized.php?api=$_POST[api_key]&ws=$_POST[ws_key]"));
		$web_details = unserialize($fcont);
		if(isset($web_details[0]['total']) or $web_details[0]['total'] <> ""){
			update_option('gstats_api_key', $_POST[api_key]);
			update_option('gstats_ws_key', $_POST[ws_key]);
			$api_key = $_POST[api_key];
			$ws_key = $_POST[ws_key];
			echo "<center><span style='color:blue;'><strong>" . __("API key and Site Id are saved!") . "</strong></span><br/></center>";
		}else{
			echo "<center><span style='color:red;'><strong>" . __("Wrong API key or Site Id, please check it!") . "</strong></span><br/></center>";
		}
		}?>
		<h2><?php _e('GoingUP! Web Analytics'); ?></h2>
		<div class="narrow">
			<form action="options-general.php?page=<?php echo $plugin_page; ?>" method="post">
				<p><?php _e('Not GoingUp? <a href="http://www.goingup.com/signup.html" target="_blank"> Click here</a> to create a free account'); ?></p>
				<label for="api_key"><?php _e('API Key:'); ?> <input style='width:265px' type="text" name="api_key" id="api_key" value="<?php echo $api_key; ?>" /></label>
				<label for="ws_key"><?php _e('Site ID:'); ?> <input style='width:45px' type="text" name="ws_key" id="ws_key" value="<?php echo $ws_key; ?>" /></label>
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
}

function gustat_header(){
	$workdir = get_option('siteurl')."/".PLUGINDIR."/gustats/";
	$api_key = get_option('gstats_api_key');
	$ws_key = get_option('gstats_ws_key');
	?>
	<script language="JavaScript" type="text/javascript"><!-- // -->
	var popupTimer = null;
	var rowPopupContents = new Object();
	var xmlHttp = null;
	var apiKey = '<? echo $api_key;?>';
	var ws = '<? echo $ws_key;?>';
	
	function findPos(obj) {
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			curleft = obj.offsetLeft
			curtop = obj.offsetTop
			while (obj = obj.offsetParent) {
				curleft += obj.offsetLeft
				curtop += obj.offsetTop
			}
		}
		return [curleft,curtop];
	}

	// Get a XMLHttpObject for different browsers
	function GetXmlHttpObject(){
		try{
			// Firefox, Opera 8.0+, Safari
			xmlHttp = new XMLHttpRequest();
		}catch (e){
			// Internet Explorer
			try{
				xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
			}catch (e){
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}
		return xmlHttp;
	}

	function showHint(apiKey, ws){
		xmlHttp = GetXmlHttpObject();

		if (xmlHttp == null){
			return;
		}
		
		var url = "<?=$workdir?>Take.php";
		url = url+"?apiKey="+apiKey+"&ws="+ws;

		xmlHttp.onreadystatechange = stateChanged;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}
	// take state
	function stateChanged(){ 
		if (xmlHttp.readyState == 4){
			var popup_content = document.getElementById('row_popup_content');
			popup_content.innerHTML = xmlHttp.responseText;
		}
	}

	function showPopupTimer(){
		clearTimeout(popupTimer);
		popupTimer = setTimeout('showRowPopup()',300);
	}

	function showRowPopup(){
		var popupob = document.getElementById('row_popup');
		var gupob = document.getElementById('row_gu');
		var popup_content = document.getElementById('row_popup_content');
		var gu_pos = findPos(gupob);

		var loading_content = '<div stlye="width:100%; height:100%; margin:30px; vertical-align:middle; text-align:center"><br><span style="color:#111111; font-size:10px">Retrieving Data. Please Wait.</span></div>';
		popupob.style.left = gu_pos[0] + 250 + "px";
		popupob.style.top = gu_pos[1] - 250 + "px";
		
		if (popupob.style.display!='block'){
			popupob.style.visibility = 'visible';
			popupob.style.display = 'block';
			popup_content.innerHTML = loading_content;
		}
		fillRowPopup();
	}

	function fillRowPopup(){
		showHint(apiKey,ws);
	}
	
	function hideRowPopup(){
		clearTimeout(popupTimer);
		var popupob = document.getElementById('row_popup');
		popupob.style.visibility = 'hidden';
		popupob.style.display = 'none';
	}

	</script>
<?
}

function gustat_footer(){
	$workdir = get_option('siteurl')."/".PLUGINDIR."/gustats/";
	?>
	<div id="row_gu">
		<a href="http://www.goingup.com/" onmouseover="showPopupTimer();" onmouseout="hideRowPopup();"><img id='gu' src="<?=$workdir?>badge.gif" width="120" height="30" border="0"></a>
	</div>
<div id="row_popup" style="background:no-repeat; position:absolute; width:290px; height:229px; display:none; visibility:hidden; padding:4px 20px 20px 20px;">
	<div style="color:#FFFFFF; font-weight:bold; margin:4px;">GoingUp! Site Health Trend</div>
	<div id=row_popup_content></div>
	<script language="javascript">
		var arVersion = navigator.appVersion.split("MSIE");
		var version = parseFloat(arVersion[1]);
		
		if ((version >= 5.5) && (document.body.filters)) {
			document.getElementById('row_popup').style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=$workdir?>bg.png', sizingMethod='scale');";
		}else{
			document.getElementById('row_popup').style.backgroundImage = "url(\'<?=$workdir?>bg.png\');";
		}
	</script>

</div>

<?
}
add_action('wp_head', 'gustat_header');
add_action('admin_menu', 'gustats_admin_menu');
add_action('wp_footer', 'gustat_footer');
