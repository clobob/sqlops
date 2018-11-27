<?php
#Author: Dou Liyou (douliyou@live.cn)
#Date: 2018/10/29
#Version: 0.1
#License: Apache Licence
#CopyRight: 2018~2028

#mysql procedure 对象定义
#判断一个存储过程的基本结构是否符合 isProcedure() 返回布尔型
#构造函数 __construct($procedureString) 传入存储过程字符串，初始化完成存储过程解析到一个数组中 
#获取sql语句函数 obtainSqls() 
#
class mysqlProcedure {
	var $mysqlKeyWords=array("SELECT","INSERT","UPDATE","CREATE","ALTER","DELETE");
	//构造函数,初始化存储过程对象
	function __construct($procedure='') {
		$this->procedure = trim($procedure);
		$this->procedureCode = array();
		$this->parseProcedure();
	}
	
	
	#存储过程匹配模式 ... CREATE PROCEDURE ...[BEGIN]... END ...
	var $patternProcedure = "/[(.)(\r\n)]*CREATE(.+)PROCEDURE.+(\r\n)*(BEGIN)?.+(\r\n)*(END)?.+(\r\n)*/";
	
	#判断一个存储过程的基本结构是否符合,返回 布尔型
	function isProcedure() {
		$matched = preg_match_all($this->patternProcedure,strtoupper($this->procedure));
		if ((false!=$matched)and($matched>=1)){
			return true;
		}
#		echo 'debug info:: excute this line >>> no matched';
		return false;
	}
	#成员变量用于保存 存储过程解析后的数据片段
	var $procedureCode;
	#将存储过程解析出来
	function parseProcedure() {
		if ($this->isProcedure()){
			$createprocedure = $this->obtainKeyWords("/CREATE(.+)PROCEDURE/",strtoupper($this->procedure));
			#procedureCode[0] 保存 create procedure 之前的内容
			$this->procedureCode[0]=substr($this->procedure,0,stripos($this->procedure,$createprocedure));
			$temp = substr($this->procedure,
			(stripos($this->procedure,$createprocedure)+strlen($createprocedure)),strlen($this->procedure));
			#procedureCode[1] 存储过程名字信息及参数			
			$this->procedureCode[1]=$this->obtainKeyWords("/([^\(^\)]+)\([^\(^\)]*\)/",$temp);
			$temp = substr($temp,strlen($this->procedureCode[1]),(strlen($temp)-1));
			
			#procedureCode[2] 保存存储过程代码
			if(preg_match_all("/BEGIN/",$temp)) {
				#将包含BEGIN ... END 的代码抽取出来
				$temp = $this->obtainInnerValue("/.*BEGIN([^~]*)END.*/",$temp);
				$this->procedureCode[2] = $temp;
			}else {
				$this->procedureCode[2] = $temp;
			}		
		}
	}
	
	#提取关键字方法 例如: create [中间可以有 其他定义, 多个空格或制表符的情况] procedure
	function obtainKeyWords($patternKey,$sourceStr) {
		if (preg_match_all($patternKey,$sourceStr)) {
			preg_match_all($patternKey,$sourceStr,$matched,PREG_PATTERN_ORDER);
#			var_dump($matched[1][0]);
			return $matched[0][0];
		}
		#源字符串中没有查找的内容返回null
		return null;
	}
	#按指定的正则式提取截取范围内的数据 字符串 ，没有匹配到返回 null
	function obtainInnerValue($patternKey,$sourceStr) {
		if (preg_match_all($patternKey,$sourceStr)) {
			preg_match_all($patternKey,$sourceStr,$matched,PREG_PATTERN_ORDER);
#			var_dump($matched[1][0]);
			return $matched[1][0];
		}
		#源字符串中没有查找的内容返回null
		return null;
	}
	
	#提取存储过程中SQL语句并保存到数组当中, 不包含SQL语句返回null
	# 入参 是字符串
	# 返回值 数组 或 null
	function obtainSqls($Sqls) {
		$realSqls = null;
		foreach ($this->mysqlKeyWords as $Key) {
			$matched = preg_match_all("/".$Key."/",strtoupper($Sqls));
			if(!($matched===false) and ($matched > 0)){
				#给定的字符串中包含sql关键字则继续进行处理
				$realSqls = array();
				break;
			}
		}
		if(null === $realSqls){
			return null;
		}else{
#			echo "start process sql codes";
			if(!(stripos($Sqls,";")===false)){
				$realSqls = preg_split("/;/",$Sqls);
			}else{
				$realSqls[0]=$Sqls;
			}
#			$realSqls = array_filter($realSqls);
			#不能执行,callback函数调用问题
			array_walk($realSqls,array($this,"my_trim"));
		}
		$onlySqls = array();
		foreach ($realSqls as $sql){
		#	echo $sql;
			foreach ($this->mysqlKeyWords as $sqlKey){
#echo $sqlKey;
				if(preg_match_all("/".$sqlKey."/",strtoupper($sql))){array_push($onlySqls,$sql);continue;}
			}
		}
		#return array_filter($realSqls);
		return $onlySqls;
	}
	#自定义方法 去除字符串两端空格
	function my_trim(&$item) {
		$item = trim($item);
	}
}
### mysqlprocedures类 加载存储过程文件转换为单个存储过程，保存到数组中
#   主要方法:
# obtainProcedureContent() 返回数组 >>> 可能为空数组
class mysqlProcedures {
	var $mysqlProcedureArray;
	var $mysqlprocedures;
	#构造方法可传入存储过程内容或文件名
	function __construct($mysqlprocedures) {
		if(is_file($mysqlprocedures)){
			$this->mysqlprocedures=file_get_contents($mysqlprocedures);
		}else{
			$this->mysqlprocedures = trim($mysqlprocedures);
		}
		$this->mysqlProcedureArray = array();
	}
	
	#从存储过程文件中获取到单一的存储过程内容保存到数组中, 返回存储过程数组 ,没有匹配返回空数组
	function obtainProcedureContent() {
		$pattern = "/DELIMITER ;;\r\n([^~]*?);;\r\nDELIMITER ;/";
		if (preg_match_all($pattern,$this->mysqlprocedures)){
			preg_match_all($pattern,$this->mysqlprocedures,$matched,PREG_PATTERN_ORDER);
			$this->mysqlProcedureArray = $matched[1];
		}
		return $this->mysqlProcedureArray;
	}
}

## dataUtil 类 用于去除文件内容中的注释
#  注释符合包含：
#  '-- '
#   '/*...*/'
class dataUtil {
	function commentFilter($file_name){
		$findCloseFlag = false;
		#$filter = array();
		$filtered = '';
		if(file_exists($file_name)){
			#echo "file exists\r\n";
			$file_handle = fopen($file_name,"r");
			while (!feof($file_handle)){
				$line = trim(fgets($file_handle));
				if($line == '--'){
					continue;
				}
				#-- 注释符处理
				$findMinus = strpos($line,'-- ');
				if(!($findMinus===false)){
					#注释符在行首的情况,整行跳过
					if($findMinus == 0){
						continue;
					}else{
						$line = substr($line,0,($findMinus - 1));
					}
				}
				#/*...*/注释符处理
				$findStartStar = strpos($line,'/*');
				$findEndStar = strpos($line,'*/');
				if(!($findStartStar===false)){
					if($findStartStar == 0){
						#单行注释的处理
						if($findEndStar>=2){
							$line = substr($line,$findEndStar+2);
						}else{
							#多行注释的处理,设定标志位,跳过本行
							$findCloseFlag = true;
							continue;
						}
					}
				}
				#多行注释处理,查找注释结尾的处理
				if($findCloseFlag){
					if($findEndStar === false){
						#没找到结尾符,整行跳过
						continue;
					}else{
						$findCloseFlag = false; # 多行注释结束,关闭标志位
						if(strlen(trim($line)) == ($findEndStar+2)){
							continue;
						}else{
							$line = substr($line,$findEndStar+2);
						}
					}
				}
				if(trim($line)==''){
					continue;
				}
				#$filter[]=$line;
				$filtered = $filtered.$line.'\r\n';
			}
			fclose($file_handle);	
		}else{
			#echo "no file found\r\n";
				#$filter[]='Nothing read, input is not a file';
		}
		#var_dump($filter);	
		#var_dump($filtered);
		return $filtered;
	}
		
}

#测试代码1
function testClass1(){
$testProcedure1 = "CREATE    		PROCEDURE delbyId\(IN id INT UNSIGNED\) 
BEGIN 
DELETE FROM t_name  
WHERE u_id \= id; 
END";
$testProcedure2 ="CREATE DEFINER=`etl_user`@`%` PROCEDURE `p_accs_PTY_AA_ORG`(in etl_date char, in etl_sequ int)
BEGIN
  
    declare _err,_step int default 0;
    declare _var varchar(20);
    declare continue handler for not found set _err=1;
    declare continue handler for sqlwarning set _err=2;
    declare continue handler for sqlexception set _err=3;
    
     update accs.PTY_AA_ORG a inner join ods.accs_PTY_AA_ORG b
             on a.aa_org_id = b.aa_org_id and b.data_del_flag = 1
	set
	 a.last_alter_psn_id = b.last_alter_psn_id
	,a.last_alter_psn_name = b.last_alter_psn_name
	,a.last_alter_psn_dept_id = b.last_alter_psn_dept_id
	,a.last_alter_psn_dept_name = b.last_alter_psn_dept_name
	,a.last_alter_time = b.last_alter_time
	,a.data_op_net_flg = b.data_op_net_flg
	,a.data_del_flag = b.data_del_flag;
     
     replace into accs.PTY_AA_ORG (
     aa_org_id  
     ,org_fname  
     ,aa_org_lvl_cde  
     ,supv_org_id  
     ,org_cde  
     ,set_up_date  
     ,reg_cny_cde  
     ,reg_captl_amt  
     ,reg_reg_org_name  
     ,biz_reg_reg_nbr  
     ,spvs_ppdm_cde  
     ,reg_addr_coun_cde  
     ,reg_addr_prvc_cde  
     ,reg_addr_city_cde  
     ,reg_addr_dtl_addr  
     ,oa_prvc_cde  
     ,oa_city_cde  
     ,oa_pstl_cde  
     ,oa_dtl_addr  
     ,cntct_tel_nbr  
     ,fax_nbr  
     ,email  
     ,url  
     ,manage_range  
     ,manage_place  
     ,org_stctr_describ  
     ,org_sts_cde  
     ,org_stctr_pic  
     ,org_intro  
     ,legal_rep_psn_cert_type_cde  
     ,legal_rep_psn_cert_nbr  
     ,legal_rep_psn_name  
     ,sprac_psn_name  
     ,sprac_psn_title_name  
     ,sprac_psn_cntct_tel_nbr  
     ,sprac_psn_fax_nbr  
     ,sprac_psn_mob_nbr  
     ,sprac_psn_email  
     ,drctr_cert_type_cde  
     ,drctr_cert_nbr  
     ,drctr_id  
     ,drctr_name  
     ,drctr_fixed_tel_nbr  
     ,drctr_email  
     ,drctr_fax_nbr  
     ,tot_nbr  
     ,set_up_mode_cde  
     ,set_up_mode_intro  
     ,f_reg_indc  
     ,f_reg_dtl_addr  
     ,get_sec_qlf_date  
     ,sec_lic_nbr  
     ,aa_nbr  
     ,used_name  
     ,entr_psn_id  
     ,entr_psn_name  
     ,entr_psn_dept_id  
     ,entr_psn_dept_name  
     ,entr_time  
     ,last_alter_psn_id  
     ,last_alter_psn_name  
     ,last_alter_psn_dept_id  
     ,last_alter_psn_dept_name  
     ,last_alter_time  
     ,data_op_net_flg  
     ,data_del_flag  

    ) 
    select 
     aa_org_id  
     ,org_fname  
     ,aa_org_lvl_cde  
     ,supv_org_id  
     ,org_cde  
     ,set_up_date  
     ,reg_cny_cde  
     ,reg_captl_amt  
     ,reg_reg_org_name  
     ,biz_reg_reg_nbr  
     ,spvs_ppdm_cde  
     ,reg_addr_coun_cde  
     ,reg_addr_prvc_cde  
     ,reg_addr_city_cde  
     ,reg_addr_dtl_addr  
     ,oa_prvc_cde  
     ,oa_city_cde  
     ,oa_pstl_cde  
     ,oa_dtl_addr  
     ,cntct_tel_nbr  
     ,fax_nbr  
     ,email  
     ,url  
     ,manage_range  
     ,manage_place  
     ,org_stctr_describ  
     ,org_sts_cde  
     ,org_stctr_pic  
     ,org_intro  
     ,legal_rep_psn_cert_type_cde  
     ,legal_rep_psn_cert_nbr  
     ,legal_rep_psn_name  
     ,sprac_psn_name  
     ,sprac_psn_title_name  
     ,sprac_psn_cntct_tel_nbr  
     ,sprac_psn_fax_nbr  
     ,sprac_psn_mob_nbr  
     ,sprac_psn_email  
     ,drctr_cert_type_cde  
     ,drctr_cert_nbr  
     ,drctr_id  
     ,drctr_name  
     ,drctr_fixed_tel_nbr  
     ,drctr_email  
     ,drctr_fax_nbr  
     ,tot_nbr  
     ,set_up_mode_cde  
     ,set_up_mode_intro  
     ,f_reg_indc  
     ,f_reg_dtl_addr  
     ,get_sec_qlf_date  
     ,sec_lic_nbr  
     ,aa_nbr  
     ,used_name  
     ,entr_psn_id  
     ,entr_psn_name  
     ,entr_psn_dept_id  
     ,entr_psn_dept_name  
     ,entr_time  
     ,last_alter_psn_id  
     ,last_alter_psn_name  
     ,last_alter_psn_dept_id  
     ,last_alter_psn_dept_name  
     ,last_alter_time  
     ,data_op_net_flg  
     ,data_del_flag  

    from ods.accs_PTY_AA_ORG
    where data_del_flag<>1;
    
    if _err=3 then  
        set _var = \"Error\";
        rollback;
    elseif _err = 2 then
        set _var = \"Warning\";
    elseif _err = 1 then
        set _var = \"Not Found\";
    end if;  
    commit;
      
    select  _var ;  
END";

echo "\n\r\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\r\n\n";
echo "\n".$testProcedure1."\r\n";
echo $testProcedure2."\r\n";
$testprocedure = new mysqlProcedure($testProcedure1);
$testprocedure2 = new mysqlProcedure($testProcedure2);
echo "\r\nmatched result: ".$testprocedure->isProcedure();
echo "\r\nmatched result2: ".$testprocedure2->isProcedure();
$testprocedure->parseProcedure();
echo "\n\r\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\r\n\n";
var_dump($testprocedure->procedureCode);

$testprocedure2->parseProcedure();
echo "\n\r\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\r\n\n";
var_dump($testprocedure2->procedureCode);

echo "\n\r\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\r\n\n";
echo "Key words: >>> ".$testprocedure->obtainKeyWords("/CREATE(.+)PROCEDURE/",$testProcedure1)."\n";
#echo "Key words: >>> ".$testprocedure->obtainKeyWords("/BEGIN([^>]*)END/",$testProcedure1)."\n";
#var_dump($testProcedure1);
echo "\nInner value: >>>".$testprocedure->obtainInnerValue("/BEGIN([^~]*)END/",$testProcedure1);
#测试sql语句抽取
echo "\n Not a sql: ".$testprocedure->obtainSqls("123456")." <<< check output as expected\r\n";
echo "\n standard sql: >>> check output as expected \r\n>>>";
print_r($testprocedure->obtainSqls("select * from table1; insert into table2 where id=\"2\";"));

print_r($testprocedure2->obtainSqls($testprocedure2->procedureCode[2]));
}

#测试代码2
function testClass2(){
		$allProcedures = "./accs_proc_all.sql";
		$sqls = new mysqlProcedures($allProcedures);
		$actual_output = $sqls->obtainProcedureContent();
		echo "Start testing ................................................................\r\n\r\n";
		echo ">>>> ".count($actual_output)." items in test data \r\n>>>> the first item is : \r\n";
		print_r($actual_output[0]);
		echo "End testing ................................................................\r\n\r\n";
}

function testClass3(){
	$utilObj = new dataUtil();
	$utilObj->commentFilter("./sqlops/sql/sql_db.sql");
}
#测试代码 ，产品中注释该行
#testClass1();
#testClass2();
#testClass3();
?>
