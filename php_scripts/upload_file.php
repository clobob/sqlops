 <?php
class fileOperator{
var $fileName;
var $fileSize;


function __construct(){
$this->fileName = "";
$this->fileSize = 0;

}
# 处理上传文件操作
# 参数： filename  指的是表单传来的表单名
# 返回 布尔型 上传成功或失败
# 保存 文件路和大小到成员变量
# 确保 操作路径有读写权限
function uploadProcedureFile($filename){

 if ($_FILES[$filename]["error"] > 0)
 {
 echo "Error: " . $_FILES[$filename]["error"] . "<br>";
 return false;
 }
 else
 {
 #echo "Upload: " . $_FILES[$filename]["name"] . "<br>";
 #echo "Type: " . $_FILES[$filename]["type"] . "<br>";
 #echo "Size: " . ($_FILES[$filename]["size"] / 1024) . " kB<br>";
 #echo "Stored in: " . $_FILES[$filename]["tmp_name"]."<br>";

$uploads_dir = getcwd()."/../tmp/upload";
$tmp_name = $_FILES[$filename]["tmp_name"];
$name = $_FILES[$filename]["name"];
#$contents = file_get_contents($tmp_name);
#echo $contents;

if (move_uploaded_file($tmp_name, "$uploads_dir/$name")){
    $this->fileName = "$uploads_dir/$name";
    $this->fileSize = $_FILES[$filename]["size"] / 1024;
    return true;
}else{
    return false;
}
}
}

# 使用过后将文件删除掉
function deleteFile(){
    if(file_exists($this->fileName)){
	echo $this->fileName;
	if(unlink($this->fileName)){
	    return true;
	}
    }
    return false;
}

}

/**
$fileoperator = new fileOperator();

$uploaded = $fileoperator->uploadProcedureFile("file");
if($uploaded){
echo "<br> Mysql Procedure is uploaded. <br>";

echo "<br> Start analysis the procedure.<br>";

echo "<br> file name : ".$fileoperator->fileName."<br>";

echo "<br> file size : ".$fileoperator->fileSize."<br>";

if($fileoperator->deleteFile()){
echo "<br> file is removed.";
}

}else{
echo "<br> upload failure. <br>";
}

echo "<form action=\"../procedure_review.html\" method=\"get\"> <input type='submit' name='submit' value='返回'></form>";
*/
?>
