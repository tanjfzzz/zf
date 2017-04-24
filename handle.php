<?php

include ('simple_html_dom.php');
session_start();
error_reporting(0);
header("Content-type: text/html; charset=gbk"); //视学校而定，我们学校是gbk编码，php也采用gbk编码方式
function login_post($url, $cookie, $post) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不自动输出数据，要echo才行
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //重要，抓取跳转后数据
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_REFERER, 'http://202.116.160.170/default2.aspx'); //重要，ASP的302跳转需要referer，可以在Request Headers找到
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); //post提交数据
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
function getName() {
    $_SESSION['xh'] = $_POST['xh'];
    $xh = $_POST['xh'];
    $pw = $_POST['pw'];
    $code = $_POST['code'];
    $cookie = dirname(__FILE__) . '/cookie/' . $_SESSION['id'] . '.txt';
    $url = "http://202.116.160.170/default2.aspx"; //教务处地址
    $con1 = login_post($url, $cookie, '');
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
    $con2 = login_post($url, $cookie, http_build_query($post)); //将数组连接成字符串
    preg_match_all('/<span id="xhxm">([^<>]+)/', $con2, $xm); //正则出的数据存到$xm数组中
    $name = substr($xm[1][0], 0, -4); //字符串截取，获得学生姓名
    return $name;
}
function getDaySchedule($day = '') {
    $stuName = getName();
    $cookie = dirname(__FILE__) . '/cookie/' . $_SESSION['id'] . '.txt';
    $url = "http://202.116.160.170/xskbcx.aspx?xh=" . $_SESSION['xh'] . "&xm=" . $stuName . "&gnmkdm=N121603";
    $con = login_post($url, $cookie, '');
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
    // return $data4;
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
    // return $data6;
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
    foreach ($data8 as $k11 => $v11) {
        foreach ($v11 as $k12 => $v12) {
            $count2 = count($v12);
            //元素数量为2
            if ($count2 == 2) {
                $content = $v12[0];
                $classTime = cut('第', '节', $v12[1]);
                $classWeek = cut('第', '周', cut('{', '}', $v12[1]));
                $classWeekType = cut('|', '}', $v12[1]);
                //先解析上课时间
                $classTimeArray = HandleClassTime($classTime);
                $classWeekArray = HandleClassWeek($classWeek);
                $start_week = $classWeekArray['start_week'];
                $end_week = $classWeekArray['end_week'];
                //不分单双周的课程
                if (strlen($classWeekType) == 0) {
                    for ($i = $start_week; $i <= $end_week; $i++) {
                        switch ($day) {
                            case '星期一':
                            case '星期二':
                            case '星期三':
                            case '星期四':
                            case '星期五':
                            case '星期六':
                            case '星期日':
                        }
                }
            } else {
            }
        }
        //元素数量为4
        else {
        }
    }
}
}
function HandleClassTime($classTime) {
    if (!empty($classTime)) {
        switch ($classTime) {
                //单节
                
            case '1':
                $start = "8:00:00";
                $end = "8:45:00";
                break;

            case '2':
                $start = "8:50:00";
                $end = "9:35:00";
                break;

            case '3':
                $start = "10:05:00";
                $end = "10:50:00";
                break;

            case '4':
                $start = "10:55:00";
                $end = "11:40:00";
                break;

            case '5':
                $start = "12:30:00";
                $end = "13:15:00";
                break;

            case '6':
                $start = "13:20:00";
                $end = "14:05:00";
                break;

            case '7':
                $start = "14:30:00";
                $end = "15:15:00";
                break;

            case '8':
                $start = "15:20:00";
                $end = "16:05:00";
                break;

            case '9':
                $start = "16:35:00";
                $end = "17:20:00";
                break;

            case '10':
                $start = "17:25:00";
                $end = "18:10:00";
                break;

            case '11':
                $start = "19:30:00";
                $end = "20:15:00";
                break;

            case '12':
                $start = "20:20:00";
                $end = "21:05:00";
                break;

            case '13':
                $start = "21:10:00";
                $end = "21:55:00";
                break;
                //双节
                
            case '1,2':
                $start = "8:00:00";
                $end = "9:35:00";
                break;

            case '3,4':
                $start = "10:05:00";
                $end = "11:40:00";
                break;

            case '5,6':
                $start = "12:30:00";
                $end = "14:05:00";
                break;

            case '7,8':
                $start = "14:30:00";
                $end = "16:05:00";
                break;

            case '9,10':
                $start = "16:35:00";
                $end = "18:10:00";
                break;

            case '11,12':
                $start = "19:30:00";
                $end = "21:05:00";
                break;
                //三节
                
            case '11,12,13':
                $start = "19:30:00";
                $end = "21:55:00";
                break;
        }
        $now_time = date("Y-m-d");
        $start_time = strtotime($now_time . " " . $start);
        $end_time = strtotime($now_time . " " . $end);
        $length = $end_time - $start_time;
        $timeArray = array(
            'start_time' => $start_time,
            'end_time' => $end_time,
            'length' => $length,
        );
        return $timeArray;
    }
}
function HandleClassWeek($classWeek) {
    if (!empty($classWeek)) {
        $where = strpos($classWeek, '-');
        $str_length = strlen($classWeek);
        $start_week = substr($classWeek, 0, $where);
        $end_week = substr($classWeek, $where + 1, $str_length - 1);
        $weekArray = array(
            'start_week' => intval($start_week) ,
            'end_week' => intval($end_week) ,
        );
        return $weekArray;
    }
}
function cut($begin, $end, $str) {
    $b = mb_strpos($str, $begin) + mb_strlen($begin);
    $e = mb_strpos($str, $end) - $b;
    return mb_substr($str, $b, $e);
}
function getSchedule() {
    $Monday = getDaySchedule('星期一');
    $Tuesday = getDaySchedule('星期二');
    $Wednesday = getDaySchedule('星期三');
    $Thursday = getDaySchedule('星期四');
    $Friday = getDaySchedule('星期五');
    $Saturday = getDaySchedule('星期六');
    $Sunday = getDaySchedule('星期日');
    $timetable = array_merge_recursive($Monday, $Tuesday, $Wednesday, $Thursday, $Friday, $Saturday, $Sunday);
    return $timetable;
}
if (empty($_POST['xh']) || empty($_POST['pw']) || empty($_POST['code'])) {
    echo "<script> alert('请填写完整');parent.location.href='login.php'; </script>";
} else {
    $r = getSchedule();
    print_r($r);
}

