<?php
define('IN_ECS', true);
header("content-Type: text/html; charset=Utf-8");
//require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/Classes/PHPExcel.php');
var_dump(strtotime(20151007),strtotime(20151101),strtotime(20151201),strtotime(20160101),strtotime(20160201),strtotime(20160301),strtotime(20160401));exit;

set_time_limit(0);
ini_set('memory_limit', '1024M');

$savePath = dirname(__FILE__) . '/upfile/';
$res = read($savePath . 'test.xls');

$mysql_server_name = "rdsuje2enuje2en.mysql.rds.aliyuncs.com:3306"; //数据库服务器名称
$mysql_username    = "ecshop"; // 连接数据库用户名
$mysql_password    = "ecshop123"; // 连接数据库密码
// 连接到数据库
$conn              = mysql_connect($mysql_server_name, $mysql_username, $mysql_password);
if (!$conn) {
    die('Could not connect: ' . mysql_error());
}
//mysql_select_db("wanjike", $conn);
mysql_select_db("ecshop", $conn);
mysql_query("SET NAMES 'UTF8'");
$suc = 0;
$fal = 0;
foreach($res as $v){
    $market_price = number_format($v[1], 0, '.', '');
    $shop_price = number_format($v[2], 0, '.', '');
    $r = mysql_query("update ecs_goods set market_price=$market_price,shop_price=$shop_price,min_price=$shop_price WHERE kjt_goods_id='".$v[0]."'");
    if($r){$suc++;}
    else{$fal++;}
}
echo "suc:".$suc."fal:".$fal;
exit;
$a = fopen(dirname(__FILE__)  .'/log/'. "log.log","a+");
fwrite($a,"success!");
fclose($a);
//$mysql_server_name="localhost"; //数据库服务器名称
//$mysql_username="root"; // 连接数据库用户名
//$mysql_password=""; // 连接数据库密码
$mysql_server_name = "rdsuje2enuje2en.mysql.rds.aliyuncs.com:3306"; //数据库服务器名称
$mysql_username    = "ecshop"; // 连接数据库用户名
$mysql_password    = "ecshop123"; // 连接数据库密码
// 连接到数据库
$conn              = mysql_connect($mysql_server_name, $mysql_username, $mysql_password);
if (!$conn) {
    die('Could not connect: ' . mysql_error());
}
//mysql_select_db("wanjike", $conn);
mysql_select_db("ecshop", $conn);
mysql_query("SET NAMES 'UTF8'");

$works = mysql_query("SELECT * FROM ecs_test_workers_info WHERE status=0 ORDER BY work_id ASC");

$val1 = 0;
$val2 = 0;
$val3 = 0;
$val4 = 0;
while ($work = mysql_fetch_array($works)) {
    $t1          = microtime(true);
    $work_id     = $work['work_id'];
    $file_name   = $work['filename'];
    $objPHPExcel = new PHPExcel();
    $res = read($savePath . $file_name . '.xls');
    $result = array_splice($res, 1);
    $start = date('Y-m-',strtotime($work['t2']));
    $end=date('d',strtotime($work['t2']));
    $daynum=randomDivInt($end,count($result));
    foreach($daynum as $k => $v)
    {
        $nums=1;
        $day=sprintf("%02d",$k+1);
        $regtime=$start.$day;
        $region  = mysql_query("SELECT * FROM ecs_region ");
        $regions = array();
        while ($row = mysql_fetch_array($region)) {
            if ($row['region_type'] == 2) {
                break;
            }
            //得到城市ID数组
            $regions[$row['region_name']] = $row['region_id'];
        }
        $regions = array_splice($regions, 1);
        foreach ($result as $key => $value) {
            if($nums>$v){
                break;
            }
            $values=$value;
            $val3++;
            $nums++;
            $result = array_splice($result, 1);
            $password   = md5($values[1]);
            $birthday   = date('Y-m-d', rand_time('1950-01-01', '1995-01-01'));
            $reg_time   = rand_time($regtime . ' 06:00:00', $regtime . ' 22:00:00');
            $reg_time   = get_addtime(date('Y-m-d', $reg_time));
            $last_login = rand_time(date('Y-m-d H:i:s', $reg_time), date('Y-m-d H:i:s', time()));
            $time       = time(0);
            mysql_query("UPDATE ecs_test_workers_info SET total=total+1 WHERE work_id=$work_id ");
            //第一步：添加主表ecs_users
            $bl     = mysql_query("INSERT INTO ecs_users (email,user_name,password,sex,birthday,address_id,reg_time,last_login,last_time,last_ip,office_phone,home_phone,mobile_phone,mediaID) VALUES ('$value[2]','$value[0]','$password',0,'$birthday',0,$reg_time,$last_login,0,'0','','','$value[1]',0)");
            //添加主表后获取ID
            $userid = mysql_insert_id();
            if ($bl) {
                $val1++;
                mysql_query("UPDATE ecs_test_workers_info SET success=success+1 WHERE work_id=$work_id ");
                //第二步：添加分表ecs_user_address
                //主表添加成功之后并且ID不为0继续添加分表
                if ($values[6] == '') {
                    continue;
                }

                if (array_key_exists($values[5], $regions) && $regions[$values[5]]) {
                    $province = $regions[$values[5]];
                } else {
                    $province = 0;
                }
                $a1        = mysql_query("INSERT INTO ecs_user_address (address_name,user_id,consignee,email,country,province,city,district,address,tel,mobile) VALUES ('$value[4]',$userid,'$value[4]','$value[2]',1,$province,0,0,'$value[6]',$value[0],$value[1])");
                $addressid = mysql_insert_id();
                if ($a1) {
                    $val2++;
                    mysql_query("UPDATE ecs_test_workers_info SET zsuccess=zsuccess+1 WHERE work_id=$work_id ");
                    $a2 = mysql_query("UPDATE ecs_users SET address_id=$addressid WHERE user_id=$userid");
                    if ($a2) {
                        $val4++;
                        mysql_query("UPDATE ecs_test_workers_info SET updateid=updateid+1 WHERE work_id=$work_id ");
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            } else {
                //添加失败则继续下一条记录的插入
                continue;
            }
            $t2       = microtime(true);
            $worktime = round($t2 - $t1, 3);
            mysql_query("UPDATE ecs_test_workers_info SET status=1,worktime=$worktime WHERE work_id=$work_id ");
        }
    }
}
//echo "成功加入了" . $val1 . "条主表记录";
//echo "</br>";
//echo "成功加入了" . $val2 . "条副表记录";
//echo "</br>";
//echo "总共执行了" . $val3 . "条记录";
//echo "</br>";
//echo "总共" . $val4 . "条修改address_id成功";
//echo "</br>";
mysql_close($conn);

//记录日志
//$a = fopen(dirname(__FILE__) . '/log/' . "import_user.log", "a+");
//fwrite($a, "成功导入了".$val1.",成功加入了".$val2.",总共执行了".$val3."，总共修改了".$val4  . "\n");
//fclose($a);
function randomDivInt($num,$total){
    $min=1;//每天最少一条
    $a=array();
    for ($i=1;$i<$num;$i++)
    {
        $safe_total=($total-($num-$i)*$min)/($num-$i);//随机安全上限
        $n=mt_rand($min,$safe_total);
        $total=$total-$n;
        $a[]=$n;
    }
    $a[]=$total;
    return $a;
}
function rand_time($start_time, $end_time)
{
    $start_time = strtotime($start_time);
    $end_time   = strtotime($end_time);
    //return date('Y-m-d H:i:s', mt_rand($start_time,$end_time));
    return mt_rand($start_time, $end_time);
}
function get_addtime($date)
{
    $start_time = $date . ' 06:00:00'; //他们定下的下单时间规则
    $end_time   = $date . ' 21:59:59'; //他们定下的下单时间规则
    $start_time = strtotime($start_time);
    $end_time   = strtotime($end_time);
    return mt_rand($start_time, $end_time);
}
function read($filename, $encode = 'utf-8')
{
    $objReader = PHPExcel_IOFactory::createReader('Excel5');
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($filename);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $highestRow         = $objWorksheet->getHighestRow();
    $highestColumn      = $objWorksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    //$excelData = array();
    for ($row = 1; $row <= $highestRow; $row++) {
        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $excelData[$row][] = (string) $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
    }
    return $excelData;
}
?>