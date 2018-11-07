<?php
require "upload_file.php";
require "procedure.php";
/***
*解析存储过程文件脚本
*
*
**/
$dbname=$_POST['dbname'];
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
$sqlRulesObj = new sqlRulesClass($dbname);

$item = 1;
foreach ($procedureArray as $procedure){
	echo "<br> The ".$item." procedure is : ".$procedure->procedureCode[1]." <br>";
	$sqls = $procedure->obtainSqls($procedure->procedureCode[2]);
	echo "开始扫描SQL语句：<br>";
	$sql_number = 1;
	foreach ($sqls as $sql){
		echo "$sql_number 条SQL语句： <br>";
		//select 语句的情况
		if(stripos ($sql,'select')===0){
			$sqlRulesObj->selectRules($sql);
			$sql_number++;
			continue;
		}
		if(stripos ($sql,'insert')===0){
			$sqlRulesObj->insertRules($sql);
			$sql_number++;
			continue;
		}
		if((stripos ($sql,'update')===0)||(stripos($sql,'replace')===0)){
			$sqlRulesObj->updateRules($sql);
			$sql_number++;
			continue;
		}
		if(stripos ($sql,'alter')===0){
			$sqlRulesObj->alterRules($sql);
			$sql_number++;
			continue;
		}
		if(stripos ($sql,'delete')===0){
			$sqlRulesObj->deleteRules($sql);
			$sql_number++;
			continue;
		}
		if(stripos ($sql,'create')===0){
			$sqlRulesObj->createRules($sql);
			$sql_number++;
			continue;
		}
		echo "<br>可能不是SQL语句,人工查看一下^^^^^ $sql $$$$<br>";
		
	}
	$item++;
}





# 删除文件


$deleted = $fileoperator->deleteFile();

if($deleted){
echo "<br> 文件已移除<br>";
}
















?>
