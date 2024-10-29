<?php
/*
  Plugin Name:Alert Before Your Post
  Plugin URI: http://graffitibooks.net
  Description: これは自分専用のプラグインテンプレートです。
  Version: v0.10
  Author: kokonohi
  Author URI: http://graffitibooks.net
*/

function plugin_activate() {
	plugin_setup_activate('alert_post_options');
}

function plugin_setup_activate($table_name) {
	global $wpdb;
	if($wpdb->get_var("show tables like'" . table_name_with_prefix($table_name) . "'") != table_name_with_prefix($table_name)) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql_create_func = "sql_create_$table_name";
		$sql = $sql_create_func();
		add_option("hoges_version", '1.0');
		dbDelta($sql);
	}
}

function table_name_with_prefix($table_name) {
	global $wpdb;
	return $wpdb->prefix . $table_name;
}

function sql_create_alert_post_options() {
	return "CREATE TABLE " . table_name_with_prefix('alert_post_options') . "(
		id int not null auto_increment,
		name varchar(255) not null,
		validity boolean default 0,
		primary key(id)
	);";
}

function plugin_menu() {
	add_options_page('My Plugin Options', 'Alert Before Post',8, __FILE__, 'my_plugin_options');
}

function my_plugin_options() {
	
global $wpdb;
global $current_user;
get_currentuserinfo();

if (isset($_POST['name'])){
	$wpdb->update(
		$wpdb->prefix.'alert_post_options',
		array( 'validity'=> $_POST['val']),
		array( 'name'=> $_POST['name'])
	);
}

if(count($wpdb->get_results("SELECT * FROM ".$wpdb->prefix."alert_post_options WHERE name =".$current_user->ID)) <= 0) {
	$wpdb->insert(
		$wpdb->prefix.'alert_post_options',
		array( 'name' => $current_user->ID,
		'validity' => 0)
	);
}

echo "<form id='frm' action=".str_replace('%7E', '~', $_SERVER['REQUEST_URI'])." method='post'>";

wp_nonce_field('update-options');

echo <<<EOF
	<div class="wrap">
		<h2>Alert Before Your Post の設定</h2>
	</div>
	<table class='form-table'>
		<tbody>
EOF;

$userSearchResults = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."alert_post_options");

foreach($userSearchResults as $userSearchResult) {
	$user_info = get_userdata($userSearchResult->name);
	echo "<tr valign='top'>";
	echo "<th scope='row'>";
	echo $user_info->user_login;
	echo "</th>";
	echo "<td>";
	if($userSearchResult->validity == 0) {
		echo "<input class='button' type='submit' name='btn' value='有効にする' onclick=("."alert_true(".$userSearchResult->name.")".") />";
	} else {
		echo "<input class='button' type='submit' name='btn' value='無効にする' onclick=("."alert_false(".$userSearchResult->name.")".") />";
	}
	echo "</td>";
	echo "</tr>";
}

echo <<<EOF
		</tbody>
	</table>
EOF;

echo <<<EOF
<script type="text/javascript">
	function alert_true(name) {
		$("input#name").val(name);
		$("input#val").val(1);
	}
	
	function alert_false(name) {
		$("input#name").val(name);
		$("input#val").val(0);
	}
</script>
EOF;

echo "<input id='name' type='hidden' value='' name='name' />";
echo "<input id='val' type='hidden' value='' name='val' />";
echo "</form>";
}


function mypluginAddHeaderFirst() {
global $wpdb;
global $current_user;
get_currentuserinfo();

echo "<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.js'></script>";

if(count($wpdb->get_results("SELECT * FROM ".$wpdb->prefix."alert_post_options WHERE name =".$current_user->ID)) <= 0) {
	return false;
}

$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."alert_post_options WHERE name =".$current_user->ID);

if($result->validity == 0) {
	return false;
}

echo <<<EOF
<script type='text/javascript'>
$(function() {
	$('input#publish').click(function() {
		if(window.confirm('公開してもよろしいでしょうか？')) {
			return true;
		} else {
			return false;
		}
	});
});
</script>
EOF;
}


register_activation_hook(__FILE__,'plugin_activate');
add_action('admin_menu', 'plugin_menu');
add_action('admin_head', 'mypluginAddHeaderFirst', 1);

?>