<?php
/*
Plugin Name:Dashboard Editor
Plugin URI: http://anthologyoi.com/plugins/
Description: Allows you to customise the dashboard.
Author: Aaron Harun
Version: 0.2
Author URI: http://anthologyoi.com/
*/
$dashboard = get_option("dashboard");

if($_GET['page'] == 'dashboard.php'){

	add_action('admin_head', 'dashboard_head');

	function dashboard_head(){
		echo '
		<style type="text/css">
			textarea{
				width:100%;
			}
		</style>
		';
	}
}

if($dashboard['sidebar']){
	add_action('init', 'dashboard_sidebar');
	
	function dashboard_sidebar(){
		global $dashboard;
		if(function_exists('register_sidebar')){
			register_sidebar('name=admin');
$dashboard['admin_sidebars'] = (int)$dashboard['admin_sidebars'];
			if($dashboard['admin_sidebars'] > 0){
				for($i=1; $i <= $dashboard['admin_sidebars']; $i++){
				register_sidebar("name=admin$i");
				}
			}
			
		}
	}
}

if(strpos($_SERVER['SCRIPT_NAME'],'/index.php') == true && $_SERVER['QUERY_STRING'] == ''  && $dashboard){	

	add_action('admin_head', 'dashboard_start');	
	
	function dasboard_wipe($buffer){
	global $dashboard;
	

	if($dashboard['devnews'] == 1 || $dashboard['complete_wipe'] == 1){
		$buffer = str_replace("var update2 = new Ajax.Updater( 'devnews', 'index-extra.php?jax=devnews' );",'',$buffer);
	}else{
		$after .= '<div id="devnews"></div>';
	}
	if($dashboard['planetnews'] == 1 || $dashboard['complete_wipe'] == 1){
		$buffer = str_replace("var update3 = new Ajax.Updater( 'planetnews', 'index-extra.php?jax=planetnews'	);",'',$buffer);
	}else{
		$after .='<div id="planetnews"></div>';
	}
	
	if($dashboard['incoming'] == 1 || $dashboard['complete_wipe'] == 1){
		$buffer = str_replace("var update1 = new Ajax.Updater( 'incominglinks', 'index-extra.php?jax=incominglinks' );",'',$buffer);
	}
	if($dashboard['started'] == 1 || $dashboard['complete_wipe'] == 1){
		$buffer = preg_replace('/\<\/div\>\s*\<p\>.*?\<\/p\>[\S\s]*?\<\/ul\>/','</div>',$buffer);
	}
	if($dashboard['complete_wipe'] == 1){
		$parts = preg_split('/\<div class=\"wrap\"\>[\S\s]*\<div id=\"footer\"\>/',$buffer);
		$parts[0] .= '<div class="wrap">';
		$parts[1] .= '</div>';
	}else{
		$parts = preg_split('/\<div id="devnews">[\S\s]*\<div id=\"footer\"\>/',$buffer);
		
	}
		if(count($parts) > 0){
			echo $parts[0];
			eval ('?>'.stripslashes($dashboard['dashboard_code'])) ;


			echo $after;
			echo '<div style="clear: both">&nbsp;<br clear="all" /></div></div><div id="footer">';
			echo $parts[1];
		}else{
			echo $buffer;
		}
	}
	


	function dashboard_start($buffer){
		ob_start();
		add_action('admin_footer', 'dashboard_end');
		
	}
	
	function dashboard_end($buffer){
		$buffer = ob_get_contents();
		ob_end_clean();
		dasboard_wipe($buffer);
	}
}

add_action('admin_menu', 'dashboard_menu');

function dashboard_menu() {
 // Add a submenu to the Dashboard:
    add_submenu_page('index.php', 'Dashboard Managment', 'Dashboard Managment', 8, __file__, 'dashboard_manage');

}

function dashboard_manage(){
	global $wpdb, $dashboard,$use_options,$wp_version;
  if ($_POST["action"] == "saveconfiguration") {
			dashboard_update_options($_REQUEST['dashboard']);
			update_option('dashboard',$dashboard);
  			$message .= 'dashboard options updated.<br/>';

		//if we don't the panel will show old value...which may scare people.
		//$dashboard_all doesn't need to be updated because it has the new values added to it immediately
		$use_options = get_option('dashboard_use_options');

    echo '<div class="updated"><p><strong>'.$message;
    echo '</strong></p></div>';
	}
	echo '<div class="wrap">';
	echo '<form method="post">';
	echo '<table width="100%">';


	if($dashboard['devnews'] == 1){ $dn = 'checked="checked"'; }
	if($dashboard['planetnews'] == 1){ $pn = 'checked="checked"'; }
	if($dashboard['complete_wipe'] == 1){ $cw = 'checked="checked"'; }
	if($dashboard['incoming'] == 1){ $in = 'checked="checked"'; }
	if($dashboard['started'] == 1){ $st = 'checked="checked"'; }
	if($dashboard['sidebar'] == 1){ $si = 'checked="checked"'; }
echo <<<block

	<tr>
		<td colspan="2"><strong>Dashboard Configuration</strong></td>
	</tr>

	<tr>
		<td colspan="2"><p><strong>New Dashboard Code</strong> Use valid HTML, XHTML or PHP.</p></td>
	</tr>
	<tr>
		<td colspan="2">
block;

the_editor(stripslashes($dashboard['dashboard_code']),'dashboard[dashboard_code]');

echo <<<block
</td>
	</tr>
	<tr>
		<td>Completely wipe dashboard? (will remove everything except the header and footer):</td>
		<td><input type="checkbox" value="1" $cw name="dashboard[complete_wipe]"></td>
	</tr>
	<tr>
		<td>Remove Developers news?:</td>
		<td><input type="checkbox" value="1" $dn name="dashboard[devnews]"></td>
	</tr>
	<tr>
		<td>Remove Planet News:</td>
		<td><input type="checkbox" value="1" $pn name="dashboard[planetnews]"></td>
	</tr>
	<tr>
		<td>Remove incoming links:</td>
		<td><input type="checkbox" value="1" $in name="dashboard[incoming]"></td>
	</tr>
	<tr>
		<td>Remove getting started section:</td>
		<td><input type="checkbox" value="1" $st name="dashboard[started]"></td>
	</tr>
	<tr>
		<td>Use Sidebar Widgets:</td>
		<td><input type="checkbox" value="1" $si name="dashboard[sidebar]"></td>
	</tr>
	<tr>
		<td>If you would like more than 1 sidebar how many extra would you like?: (name will be 'admin#')</td>
		<td><input type="text" value="$dashboard[admin_sidebars]" name="dashboard[admin_sidebars]"></td>
	</tr>
block;

	
	echo '	</table>
			<input type="hidden" name="action" value="saveconfiguration">
			<input type="submit" value="Save">
		</form>
</span>
	</fieldset>';
?>

<br/><br/>
Have you found this Plugin useful?<br/>
If this Plugin has helped you, isn't it worth a little bit of time or money? <strong>If you are feeling monetarily generous make a donation</strong>.<br/> <strong>How much is entirely up to you</strong>, but numbers with lots of 0's, a 1 on the left and a decimal point on the right are the best kind. =D
<span style="text-align:center;">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="ajd777@gmail.com">
<input type="hidden" name="item_name" value="Donation For Dashboard Editor">
<input type="hidden" name="no_shipping" value="2">
<input type="hidden" name="note" value="1">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="tax" value="0">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="bn" value="PP-DonationsBF">
<input type="image" src="http://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make a donation with PayPal - it's fast, free and secure!">
<img alt="" border="0" src="http://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form></span>
<br/>
Or if circumstances make a donation impossible, <em>links, refferals and comments are appreciated</em>.

<br/><br/>

<strong>Quick Doc</strong><br/>

<p>To add widget support add &lt;?php dynamic_sidebar('admin') ?&gt; to the Dashboard Editor Context box. You can then add widgets just as you would normally.</p>
 </div>

<?php

}

function dashboard_update_options($options){
global $dashboard;
if(!$options['devnews']){ $options['devnews'] = 0; }
if(!$options['complete_wipe']){ $options['complete_wipe'] = 0; }
if(!$options['planetnews']){ $options['planetnews'] = 0; }
if(!$options['incoming']){ $options['incoming'] = 0; }
if(!$options['started']){ $options['started'] = 0; }
if(!$options['sidebar']){ $options['sidebar'] = 0; }
	while (list($option, $value) = each($options)) {
			if( get_magic_quotes_gpc() ) {
			$value = stripslashes($value);
			}
		$dashboard[$option] =  $value;
	}
return $dashboard;
}
?>