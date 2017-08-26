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
 * 	@success: {"result": "0", "message": "注册用户成功。"}
 * 	@failure: {"result": "1", "message": "服务器发生错误。"}
 */

include("config.php");

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];

if($username && $password && $email)
{
	$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
	if(!$conn)
	{
		echo '{"result": "1", "message": "服务器发生错误！"}';
		return;
	}

	mysqli_set_charset($conn,"utf8");

	$sql = "SELECT * FROM zhang_hao WHERE zhang_hao = '" . $username. "'";
	$result = $conn->query($sql);
	if($result->num_rows){
		echo '{"result": "1", "message": "用户名已存在！"}';
		return;
	}
	$result->free;

	$sql = "INSERT INTO zhang_hao SET zhang_hao='" . $username. "', mi_ma='" . $password . "', you_xiang ='" . $email . "'";
	$result = $conn->query($sql);
  	if($conn->affected_rows != 1){
		echo '{"result": "1", "message": "注册失败！"}';
		return;
	}

	$result->free;
	$conn->close;
	echo '{"result": "0", "message": "注册用户成功。"}';
}
else
{
	echo '{"result": "1", "message": "用户名、密码及邮箱不能为空！"}';
	return;
}

?>
