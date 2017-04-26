<?php

include ('simple_html_dom.php');
session_start();
error_reporting(E_ALL);
set_time_limit(0);// 设置超时时间为无限,防止超时
header("Content-type: text/html; charset=gbk"); //视学校而定，我们学校是gbk编码，php也采用gbk编码方式

class Schedule {

	const LOGIN_URL = "http://202.116.160.170/default2.aspx";
	const SCHEDULE_URL = "http://202.116.160.170/xskbcx.aspx";

    private $cookie;

	private $stuName; //学生姓名

	private $scheduleUrl; //课表url

	private $schedule; //课表

	public function __construct(){
		try{
            $this->cookie = dirname(__FILE__) . '/cookie/' . $_SESSION['id'] . '.txt'; //cookie路径
			$this->stuName = $this->getName(self::LOGIN_URL,$this->cookie);
			$this->scheduleUrl = self::SCHEDULE_URL."?xh=". $_SESSION['xh'] . "&xm=" . $this->stuName . "&gnmkdm=N121603";
			$this->schedule = $this->getSchedule($this->scheduleUrl,$this->cookie);
			print_r($this->schedule);
		}catch(\Exception $e){

		}
	}

	/**
	 * 发送登陆post请求
	 */
	public function loginPost($url, $cookie, $post) {
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不自动输出数据，要echo才行
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //重要，抓取跳转后数据
    	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    	curl_setopt($ch, CURLOPT_REFERER, $url); //重要，ASP的302跳转需要referer，可以在Request Headers找到
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $post); //post提交数据
    	$result = curl_exec($ch);
    	curl_close($ch);
    	return $result;
	}

	/**
	 *	获取学生姓名
	 */
	public function getName($loginUrl,$cookie) {
    	$_SESSION['xh'] = $_POST['xh'];
    	$xh = $_POST['xh'];
    	$pw = $_POST['pw'];
    	$code = $_POST['code'];
    	$con1 = $this->loginPost($loginUrl, $cookie, '');
    	preg_match_all('/<input type="hidden" name="__VIEWSTATE" value="([^<>]+)" \/>/', $con1, $vs); //获取__VIEWSTATE字段并存到$view数组中
    	$post = array(
        	'__VIEWSTATE' => $vs[1][0],
        	'txtUserName' => $xh,
        	'TextBox2' => $pw,
        	'txtSecretCode' => $code,
        	'shenfen' => '%D1%A7%C9%FA', //“学生”的gbk编码
        	'Button1' => '',
        	'lbLanguage' => '',
        	'hidPdrs' => '',
        	'hidsc' => ''
    	);
    	$con2 = $this->loginPost($loginUrl, $cookie, http_build_query($post)); //将数组连接成字符串
    	preg_match_all('/<span id="xhxm">([^<>]+)/', $con2, $xm); //正则出的数据存到$xm数组中
    	$name = substr($xm[1][0], 0, -4); //字符串截取，获得学生姓名
    	return $name;
	}

		/**
	 * 获取星期几的课表
	 */
	public function getDaySchedule($scheduleUrl,$cookie,$day = '') {
    	$con = $this->loginPost($scheduleUrl, $cookie, '');
    	$html = new simple_html_dom();
    	$html->load($con);
    	$table = $html->find('table.blacktab tbody tr td');
    	$sum = 0;
    	$classNameArr = array(
        	'第1节',
        	'第2节',
        	'第3节',
        	'第4节',
        	'第5节',
        	'第6节',
        	'第7节',
        	'第8节',
        	'第9节',
        	'第10节',
        	'第11节',
        	'第12节',
        	'第13节'
    	);
    foreach ($table as $key => $value) {
        $temp = preg_split("/\s+/i", $value->plaintext);
        if (count($temp) == 1) {
            $data[$sum] = $temp['0'];
        }
        if (in_array($temp['0'], $classNameArr)) {
            $pos[] = $sum;
        } else {
            $data[$sum] = $temp;
        }
        $sum++;
    }
    unset($table);
    $pos[] = count($data);
    $posCount = count($pos);
    $classNum = 1;
    for ($i = 0; $i < $posCount - 1; $i++) {
        $startPos = $pos[$i];
        $endPos = $pos[$i + 1];
        for ($j = $startPos; $j < $endPos; $j++) {
            if ($data[$j] == '&nbsp;') {
                $result[$classNum][] = NULL;
            } else {
                $result[$classNum][] = $data[$j];
            }
        }
        $classNum++;
    }
    $data = $result;
    foreach ($data as $k1 => $v1) {
        foreach ($v1 as $k2 => $v2) {
            if (is_array($v2)) {
                foreach ($v2 as $k3 => $v3) {
                    if ($v3 == '&nbsp;') {
                        $v2[$k3] = NULL;
                    }
                    if ($v3 == '下午' || $v3 == '晚上') {
                        unset($v2[$k3]);
                    }
                }
                $data1[] = $v2;
            }
        }
    }
    $data2 = array_values(array_filter($data1));
    foreach ($data2 as $k4 => $v4) {
        $data3[] = array_filter($v4);
    }
    foreach ($data3 as $k5 => $v5) {
        if (!empty($v5)) {
            $data4[] = $v5;
        }
    }

    $data5 = array();
    foreach ($data4 as $k6 => $v6) {
        foreach ($v6 as $k7 => $v8) {
            switch ($day) {
                case '星期一':
                    if (is_int(strpos($v8, '周一'))) {
                        $data5['星期一'][] = $v6;
                    }
                    break;

                case '星期二':
                    if (is_int(strpos($v8, '周二'))) {
                        $data5['星期二'][] = $v6;
                    }
                    break;

                case '星期三':
                    if (is_int(strpos($v8, '周三'))) {
                        $data5['星期三'][] = $v6;
                    }
                    break;

                case '星期四':
                    if (is_int(strpos($v8, '周四'))) {
                        $data5['星期四'][] = $v6;
                    }
                    break;

                case '星期五':
                    if (is_int(strpos($v8, '周五'))) {
                        $data5['星期五'][] = $v6;
                    }
                    break;

                case '星期六':
                    if (is_int(strpos($v8, '周六'))) {
                        $data5['星期六'][] = $v6;
                    }
                    break;

                case '星期日':
                    if (is_int(strpos($v8, '周日'))) {
                        $data5['星期日'][] = $v6;
                    }
                    break;
            }
        }
    }
    $data6 = array();
    foreach ($data5 as $k7 => $v7) {
        foreach ($v7 as $k8 => $v8) {
            if (array_key_exists($k8, $v7) && array_key_exists($k8 + 1, $v7)) {
                $result = array_diff_assoc($v7[$k8], $v7[$k8 + 1]);
                if (empty($result)) {
                    unset($v7[$k8]);
                }
            }
        }
        $data6[$day] = array_values(array_filter($v7));
    }
        
    $data7=array();
    foreach ($data6 as $k9 => $v9) {
        foreach ($v9 as $k10 => $v10) {
            $count1 = count($v10);
            //元素数量小于等于4的(3和4两种情况)
            if ($count1 <= 4) {
                if ($count1 == 3) {
                    unset($v10[2]);
                } else {
                    unset($v10[2]);
                    unset($v10[3]);
                }
            }
            //元素数量大于4，小于等于8
            if ($count1 > 4 && $count1 <= 8) {
                if ($count1 == 6) {
                    unset($v10[2]);
                    unset($v10[5]);
                } else {
                    unset($v10[2]);
                    unset($v10[3]);
                    unset($v10[6]);
                    unset($v10[7]);
                }
            }
            $data7[] = array_values(array_filter($v10));
        }
    }
    $data8[$day] = $data7;
    return $data8;
}

	/**
	 * 获取整个课表
	 */
	public function getSchedule($scheduleUrl,$cookie) {
    	$Monday = $this->getDaySchedule($scheduleUrl,$cookie,'星期一');
    	$Tuesday = $this->getDaySchedule($scheduleUrl,$cookie,'星期二');
    	$Wednesday = $this->getDaySchedule($scheduleUrl,$cookie,'星期三');
    	$Thursday = $this->getDaySchedule($scheduleUrl,$cookie,'星期四');
    	$Friday = $this->getDaySchedule($scheduleUrl,$cookie,'星期五');
    	$Saturday = $this->getDaySchedule($scheduleUrl,$cookie,'星期六');
    	$Sunday = $this->getDaySchedule($scheduleUrl,$cookie,'星期日');
    	$timetable = array_merge_recursive($Monday, $Tuesday, $Wednesday, $Thursday, $Friday, $Saturday, $Sunday);
    	return $timetable;
	}

}

$w = new Schedule();
	



