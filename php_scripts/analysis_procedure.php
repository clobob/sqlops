<?php
require "upload_file.php";
require "procedure.php";
/***
*解析存储过程文件脚本
*
*
**/

# 上传文件
$fileoperator = new fileOperator();

$uploaded = $fileoperator->uploadProcedureFile("file");
if($uploaded){
	echo "<br> Mysql Procedure is uploaded. <br>";
}

# 解析文件内容
$allProceduresObj = new mysqlProcedures($fileoperator->fileName);

#echo $allProceduresObj->mysqlprocedures."<br>";
$allProceduresArray = $allProceduresObj->obtainProcedureContent();

$total = count($allProceduresArray);
if($total>0){
	echo "<br> procedures all loaded.\t Total is ".$total."<br>";

}

$procedureArray = array();
foreach ($allProceduresArray as $procedure) {
    array_push($procedureArray,new mysqlProcedure($procedure));
}

/**
$item = 1;
foreach ($procedureArray as $procedure){
echo "<br> The ".$item." procedure is : <br>";
$sqls = $procedure->obtainSqls($procedure->procedureCode[2]);
foreach ($sqls as $sql){

echo $sql.";<br>";
}
$item++;
}
**/
# 分析SQL
require 'sqlRulesClass.php';
$sqlRulesObj = new sqlRulesClass();

$item = 1;
foreach ($procedureArray as $procedure){
	echo "<br> The ".$item." procedure is : ".$procedure->procedureCode[1]." <br>";
	$sqls = $procedure->obtainSqls($procedure->procedureCode[2]);
	foreach ($sqls as $sql){
		echo "开始扫描SQL语句：<br>";
		//select 语句的情况
		if(stripos ($sql,'select')===0){
			$sqlRulesObj->selectRules($sql);
		}else{
			echo "<br>其他SQL语句未实行扫描或不是SQL语句<br>";
		}
	}
	$item++;
}





# 删除文件


$deleted = $fileoperator->deleteFile();

if($deleted){
echo "<br> 文件已移除<br>";
}
















?>
