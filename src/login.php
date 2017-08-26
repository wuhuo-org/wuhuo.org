<?php

/*
 * function: 用户登录。
 * argument:
 * 	@username
 * 		用户名
 * 	@password
 * 		密码 
 * return value:
 * 	format: {"result": "成功标识，0为成功；1为失败", "message": "描述信息。"}
 * 	@success: {"result": "0", "message": "登录成功！"}
 * 	@failure: {"result": "1", "message": "登录失败！"}
 */

$username = $_POST['username'];
$password = $_POST['password'];
$response = "";

//echo '{"result": "1", "message": "username = ' . $username . ', password = ' . $password . '"}';
//return;

if(!($username && $password))
{
	$response = '{"result": "1", "message": "用户名、密码不能为空！"}';
	goto L_return;
}

include("config.php");
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if(!$conn)
{
	$response = '{"result": "1", "message": "服务器发生错误！"}';
	goto L_return;
}

mysqli_set_charset($conn,"utf8");

$sql = "SELECT id, zhang_hao FROM zhang_hao WHERE zhang_hao='" . $username . "' AND mi_ma='" . $password . "'";
$result = $conn->query($sql);
if($result->num_rows == 0)
{
	$response = '{"result": "1", "message": "帐号或密码错误！"}';
	goto L_close_db;
}
else if($result->num_rows > 1)
{
	$response = '{"result": "1", "message": "服务器发生错误！"}';
	goto L_close_db;
}

session_start();
$row=$result->fetch_assoc();
$str = $row['zhang_hao'] . ":)" . $row['id'];
$_SESSION['linux011-user'] = $str;
$response = '{"result": "0", "message": "登录成功！"}';

L_close_db:
$result->free;
$conn->close;
goto L_return;

L_return:
echo $response;

?>
