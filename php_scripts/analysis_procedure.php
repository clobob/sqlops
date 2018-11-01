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

echo "<br> the first procedure is : <br>";
$sqls = $procedureArray[0]->obtainSqls($procedureArray[0]->procedureCode[2]);
foreach ($sqls as $sql){

echo $sql.";<br>";
}


# 分析SQL





# 删除文件



















?>
