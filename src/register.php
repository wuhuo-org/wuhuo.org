<?php

/*
 * function: 注册用户。
 * argument:
 * 	@username
 * 		用户名
 * 	@password
 * 		密码 
 * 	@email
 * 		邮箱
 * return value:
 * 	format: {"result": "成功标识，0为成功；1为失败", "message": "描述信息。"}
 * 	@success: {"result": "0", "message": "注册用户成功！"}
 * 	@failure: {"result": "1", "message": "服务器发生错误。"}
 */

include("config.php");

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$response = "";

if(!($username && $password && $email))
{
	$response = '{"result": "1", "message": "用户名、密码及邮箱不能为空！"}';
	goto L_return;
}

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if(!$conn)
{
	$response = '{"result": "1", "message": "服务器发生错误！"}';
	goto L_return;
}

mysqli_set_charset($conn,"utf8");
	
$sql = "SELECT * FROM zhang_hao WHERE zhang_hao = '" . $username. "'";
$result = $conn->query($sql);
if($result->num_rows != 0)
{
	$response = '{"result": "1", "message": "用户名已存在！"}';
	goto L_close_db;
}
$result->free;

$sql = "INSERT INTO zhang_hao SET zhang_hao='" . $username. "', mi_ma='" . $password . "', you_xiang ='" . $email . "'";
$result = $conn->query($sql);
if($conn->affected_rows != 1)
{
	$response = '{"result": "1", "message": "注册失败！"}';
	goto L_close_db;
}
$response = '{"result": "0", "message": "注册用户成功！"}';

L_close_db:
$result->free;
$conn->close;
goto L_return;

L_return:
echo $response;

?>
