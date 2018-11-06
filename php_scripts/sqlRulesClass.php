<?php
/**
*sqlRulesClass 定义了扫描sql语句的规则,安装语句类型进行区分
**/

class sqlRulesClass{

function selectRules($select_sql){
//	array_push($dml_parm,$parmArr[0]);
	$parmArr = preg_split("/[\s]+/",ltrim(str_replace("\r\n","  ",$select_sql)));
	if(in_array('*',$parmArr)){
		echo '提示：select * 是否有必要查询所有的字段？</br>';
		$s++;
	}
	if(!in_array('where',$parmArr)){
		echo '<big><font color="#FF0000">警告！没有where条件，注意where后面的字段要加上索引</font></big></br>';
		$s++;
	}
	if(!in_array('limit',$parmArr)){
		echo '提示：没有limit会查询更多的数据</br>';
		$s++;
	}
	if(in_array("exists",$parmArr)){
		echo '<big><font color="#FF0000">警告！子查询性能低下，请转为join表关联</font></big></br>';
		$s++;
	}
	if(in_array("in",$parmArr)){
		$countIn = array_count_values($parmArr);
		if(preg_match_all('/\(.*\)/',$parm,$out)){
			echo "提示：in里面的数值不要超过1000个</br>";
				$s++;
		}
		if(in_array("(select",$parmArr)){
			echo '<big><font color="#FF0000">警告！子查询性能低下，请转为join表关联</font></big></br>';
			$s++;
		}
		if($countIn['select']>1){
			echo '<big><font color="#FF0000">警告!！子查询性能低下，请转为join表关联</font></big></br>';
			$s++;
		}	
	}
	if(in_array("in(select",$parmArr)){
		echo '<big><font color="#FF0000">警告！子查询性能低下，请转为join表关联</font></big></br>';
		$s++;
	}
	if(in_array("join",$parmArr)){
		echo '提示：采用join关联，注意关联字段要都加上索引，如on a.id=b.id</br>';
		$countArr = array_count_values($parmArr);
		if($countArr['join']>1){
			echo '提示：MySQL对多表join关联性能低下，建议不要超过3个表以上的关联</br>';
			$s++;
		}
	}
	if(in_array("like",$parmArr)){
		if(preg_match_all("/'%(.)*%'/",$parm,$out)){
			 echo "<big><font color=\"#FF0000\">警告！like '%%'双百分号无法用到索引，like 'mysql%'这样是可以利用到索引的</font></big></br>";
			 $s++;
		}
	}
	if(in_array('group',$parmArr)){
		if(in_array('by',$parmArr)){
			 echo '提示：默认情况下，MySQL对所有GROUP BY col1，col2...的字段进行排序。如果查询包括GROUP BY，想要避免排序结果的消耗，则可以指定ORDER BY NULL禁止排序。</br>';
			 $s++;
			if(!in_array('having',$parmArr)){
				echo '提示：是否要加一个having过滤下？</br>';
				$s++;
			}
		}
	}
	if(in_array('order',$parmArr)){
		 if(preg_match_all("/by.*rand().*/",$parm,$out)){
			  echo '<big><font color="#FF0000">警告！MySQL里用到order by rand()在数据量比较多的时候是很慢的，因为会导致MySQL全表扫描，故也不会用到索引</font></big></br>';
			  $s++;
		 }
		 /*if(in_array('group',$parmArr)){
			  if(in_array('by',$parmArr)){
					echo '提示：是否要加一个having过滤下？</br>';
					$s++;
			  }
		 }*/
		 if(!in_array('group',$parmArr)){
			  if(preg_match_all("/count(.*)/",$parm,$out)){
					echo '<big><font color="#FF0000">警告！禁止不必要的order by排序,因为前面已经count统计了</font></big></br>';
					$s++;
			  }
		 }
	}
	if(in_array('where',$parmArr)){
		 if(preg_match_all("/\(.*\)\s{0,}(>|<|=)/",$parm,$out)){
			echo "<big><font color=\"#FF0000\">警告！MySQL里不支持函数索引，例DATE_FORMAT('create_time','%Y-%m-%d')='2016-01-01'是无法用到索引的，需要改写为create_time>='2016-01-01 00:00:00' and create_time<='2016-01-01 23:59:59'</font></big></br>";
			 $s++;
		 }
		 if(preg_match_all("/\(.*\)\s{0,}(>|<|=|between)/",$parm,$out)){
			 echo "<big><font color=\"#FF0000\">警告！MySQL里不支持函数索引，例DATE_FORMAT('create_time','%Y-%m-%d')='2016-01-01'是无法用到索引的，需要改写为create_time>='2016-01-01 00:00:00' and create_time<='2016-01-01 23:59:59'</font></big></br>";
			 $s++;
		 }
	}
	if($s==0){
		 echo 'SQL语句未发现问题</br>';
	}
	echo '</br>';
	//echo '<big><font color=\"#0000FF\">开始调用美团网SQLAdvisor进行第二次索引检查</font></big></br>';
/**	
	require 'sqladvisor_config.php';
	if ($message === ''){
			echo "查询字段索引已经创建了,无需创建.</br>";
	}else{	
		echo "查询的字段没有创建索引，建议添加如下索引：</br>";
		echo $message."</br>";
		echo "<big><font color=\"#FF0000\">大表创建索引风险很高，如果一定要创建，请联系DBA进行执行。</font></big></br>";
	}	
	echo "</br>";
	echo "如果你觉得审核意见比较满意，请心中默念666，并推广给其他小伙伴使用。</br>";
	fclose($stream);
	fclose($errorStream);
**/

}






}

?>