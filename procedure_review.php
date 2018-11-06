<html>
<head>
存储过程处理子页面，接收输入存储过程，并解析存储过程
</head>


<p>上传存储过程文件:</p>
<form action="./php_scripts/analysis_procedure.php" method="post" enctype="multipart/form-data">
<tr>
<td>选择你的数据库：</td>
<td><select name="dbname">
<?php
	require './php_scripts/conn.php';
	$result = mysqli_query($conn,"SELECT dbname FROM dbinfo");
	while($row = mysqli_fetch_array($result)){
		//echo "<option value='".$row[0]."'>".$row[0]."</option>"."</br>";
		echo "<option value=\"".$row[0]."\">".$row[0]."</option>"."<br>";
	}
?>
</select><td>
</tr>
<label for="file">请选择存储过程文件:</label>
<input type="file" name="file" id="file"><br>
<input type="submit" name="submit" value="开始分析">
</form>

</html>
