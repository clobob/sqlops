<?php
  $remote_host="172.18.62.143";	
  $remote_user="root";   
  $remote_password="1233211234567";   
  $connection = ssh2_connect($remote_host,22);  
  $script='/usr/bin/sqladvisor -h '.$ip.' -u '.$user.' -p '.$pwd.' -P '.$port.' -d '.$db.' -q "'.$multi_sql[$x].'"'.' -v 1'; 
  ssh2_auth_password($connection,$remote_user,$remote_password);
  $stream = ssh2_exec($connection,$script);
  $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
  stream_set_blocking($errorStream, true);
  $message=stream_get_contents($errorStream);
  $message=str_replace(array("\r\n","\r","\n"),"<br/>",$message);
?>
