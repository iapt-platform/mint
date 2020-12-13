<?php
require_once "../public/_pdo.php";
require_once "../path.php";


	$db_file = _FILE_DB_PAGE_INDEX_;
	PDO_Connect("sqlite:$db_file");

	// 打开文件并读取数据
	$irow=0;
	if(($fp=fopen("./cs6_para.csv", "r"))!==FALSE){
		// 开始一个事务，关闭自动提交
		$PDO->beginTransaction();
		$query="INSERT INTO cs6_para ('book','para','book_name','cspara') VALUES (  ? , ? , ? , ? )";
		$stmt = $PDO->prepare($query);

		// 提交更改 
		try{
			while(($data=fgetcsv($fp,0,','))!==FALSE){
				$irow++;
				if($irow>1){
					if($data[3]==$data[4]){
						$stmt->execute(array($data[0],$data[1],$data[2],$data[3]));
					}
					else{
						$begin = (int)$data[3];
						$end = (int)$data[4];
						$arr1=array();
						for($i=$begin; $i<=$end; $i++){
							$arr1[] = $i;
						}
						foreach ($arr1 as $key => $value) {
							$stmt->execute(array($data[0],$data[1],$data[2],$value));
						}						
					}

				}
			}
			$PDO->commit();
		}catch (Exception $e){
			var_dump($e);
			$PDO->rollback();
		}

		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			$error = PDO_ErrorInfo();
			echo "error - $error[2] \n";
		}
		else{
			echo "updata  recorders.\n";
		}			

		fclose($fp);
	}
	else{
		echo "can not open csv file. cs6_para.csv";
	}
	


	echo "齐活！功德无量！all done!";	
?>
