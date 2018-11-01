<?php $HOST = "172.18.60.124";
$PORT = "3306";
$USERNAME = "sql_db";
$DBNAME = "sql_db";
$PASSWD = "sql_db";
$conn = mysqli_connect ($HOST, $USERNAME, $PASSWD, $DBNAME, $PORT) or
die ("数据库链接错误".mysql_error ());
mysqli_query ($conn, "set names utf8");

?>
