<?php

//获取开发选择的数据库的配置信息，表结构为dbinfo.sql
#$conn=mysqli_connect(HOST,USERNAME,PASSWD,DBNAME,PORT);
include('conn.php');
$result = mysqli_query($conn,"SELECT ip,dbname,user,pwd,port FROM dbinfo where dbname='".$dbname ."'");
while($row = mysqli_fetch_array($result))
{
  $ip=$row[0];
  $db=$row[1];
  $user=$row[2];
  $pwd=$row[3];
  $port=$row[4];
}
mysqli_close($conn);

?>
