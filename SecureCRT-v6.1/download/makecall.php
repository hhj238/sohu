<?php

function getfilelist($conf) {
        $larray = array();
        if(!is_readable($conf)) {
                return $larray;
        }
        $file = fopen($conf , "r");
        while(!feof($file)) {
                $line = trim(fgets($file));
                if(!ereg("#",$line) && $line){
                         $larray[] = $line;
                }
        }
        fclose($file);
        return $larray;
}

//要导入的服务器内网IP列表，一行一个，应尽量确保一次导入的机器都是一种角色
$listfile = "/root/ssd.txt";
$lines = getfilelist($listfile);

$i = 0;

//一次导入的IP数量
$j = 10000;

foreach($lines as $l) {
	$postdata = array();
	$postdata[act] = 'add';
	$postdata[ip_in] = $l;
	$e = explode('.',$l);
	$postdata[ip_ex] = $e[0].'.'.($e[1]-1).'.'.$e[2].'.'.$e[3];
	$postdata[env] = 'online';

//根据B段IP数字判断所属IDC编号的，这个idcid编号应根据你们数据库里idc表里的id来决定。如果你们要导入的内网IP无法在B段上区分，这里要自行改下实现方式。
	switch($e[1]) {
	case 75:{
		if($e[2] == 48) $idcid = 15;
		else $idcid = 9;
		break;}
	case 73:{$idcid = 6;break;}
	case 55:{$idcid = 1;break;}
	case 69:{$idcid = 2;break;}
	case 81:{$idcid = 14;break;}
	case 67:{$idcid = 4;break;}
	default:{;;}
	}
	$postdata[idc_id] = $idcid;
	$postdata[desc] = "";

//要导入的服务器的角色编号，对应于module表里的id号，必须确认并改下。比如Squid模块对应的module表id是64，这里就写64。
	$postdata[mrole_id] = 69;
	$poststring=json_encode($postdata);
	$u = curl_init("http://dpadmint.grid.sina.com.cn/api/syn_node_from_oadmin.php");
	curl_setopt($u, CURLOPT_RETURNTRANSFER , TRUE);
	curl_setopt($u, CURLOPT_TIMEOUT, 2);
	curl_setopt ($u, CURLOPT_POST, 1);
	curl_setopt ($u, CURLOPT_POSTFIELDS, array('sendata' => $poststring));
	curl_exec($u);
	echo $l." imported.";
	echo "\n";
	$i += 1;
	if($i >= $j) break;
}

?>
