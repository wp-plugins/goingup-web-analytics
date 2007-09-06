<?php
if(isset($_GET[apiKey]) and isset($_GET[ws])){
$fcont = join ('', file ("http://goingup.com/xml/serialized.php?api=$_GET[apiKey]&ws=$_GET[ws]")) or die("<table width=300>
	<tr>
		<td colspan=2>&nbsp;</td>
	</tr>
	<tr>
		<td align=center colspan=2><span style='font-size:10px;'>You're not GoingUp! Please configure the SMF plugin for GoingUp! now.</span></td>
	</tr>
	</table>");
$web_details = unserialize($fcont);
if(isset($web_details[0]['total']) or $web_details[0]['total'] <> ""){
?>
<script>
	function fiximage(img){
		var imgName = img.src.toUpperCase()
		if (imgName.substring(imgName.length-3, imgName.length) == "PNG"){
			var imgID = (img.id) ? "id='" + img.id + "' " : ""
			var imgClass = (img.className) ? "class='" + img.className + "' " : ""
			var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
			var imgStyle = "display:inline-block;" + img.style.cssText 
			if (img.align == "left") imgStyle = "float:left;" + imgStyle
			if (img.align == "right") imgStyle = "float:right;" + imgStyle
			if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
			var strNewHTML = "<span " + imgID + imgClass + imgTitle	+ " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";" + "filterrogidXImageTransform.Microsoft.AlphaImageLoader" + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>" 
			img.outerHTML = strNewHTML
			i = i-1
		}
	}

	fiximage(document.getElementById("img_trend"));
</script>
	<table width=200>
	<tr>
		<td align=left colspan=2><span style="color:#000000; font-size:20px;">&nbsp;Visitors</span></td>
	</tr>
	<tr>
		<td align=left width=70><img id="img_trend" name="img_trend" src="<?=$web_details[0]['trend_image']?>"></td>
		<td align=left>
			<table>
			<tr>
				<td align=left>
					<span style="color:#59AB0B; font-size:20px;"><?=$web_details[0]['month']?></span><br>
					<span style="color:#59AB0B;font-weight:bold; font-size:16px;"><?=$web_details[0]['trend_percent']?></span>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan=2 align=left>
			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="290" height="120" id="" >
			<param name="movie" value="http://www.goingup.com/FusionCharts/Charts/MSLine.swf" />
			<param name="FlashVars" value="&dataURL=http%3A//www.goingup.com/xml/charts/gu_chart.php%3Fws%3D<?=$_GET[ws]?>%26search_type%3Ddefined%26show_type%3Ddaily%26show_options%255B0%255D%3Dpages%26show_options%255B1%255D%3Dvisitors%26show_options%255B2%255D%3Dreturning_visitors%26api%3D<?=$_GET[apiKey]?>">
			<param name="quality" value="high" />
			<embed src="http://www.goingup.com/FusionCharts/Charts/MSLine.swf" flashVars="&dataURL=http%3A//www.goingup.com/xml/charts/gu_chart.php%3Fws%3D<?=$_GET[ws]?>%26search_type%3Ddefined%26show_type%3Ddaily%26show_options%255B0%255D%3Dpages%26show_options%255B1%255D%3Dvisitors%26show_options%255B2%255D%3Dreturning_visitors%26api%3D<?=$_GET[apiKey]?>"  quality ="high" width="290" height="120" name="" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
			</object>
		</td>
	</tr>
	</table>
<?php }else{?>
	<table width=300>
	<tr>
		<td colspan=2>&nbsp;</td>
	</tr>
	<tr>
		<td align=center colspan=2><span style='font-size:10px;'>You're not GoingUp! Please configure the SMF plugin for GoingUp! now.</span></td>
	</tr>
	</table>
<?php }
}?>
