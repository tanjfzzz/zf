<?php

session_start();
$id=session_id();
$_SESSION['id']=$id;

function getCode($codeUrl){
        $cookie = dirname(__FILE__) . '/cookie/'.$_SESSION['id'].'.txt'; //cookie路径，必须手动建立cookie目录
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $codeUrl);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);  //保存cookie
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $img = curl_exec($curl);  //执行curl
        curl_close($curl);
        $fp = fopen("verifyCode.jpg","w");  //文件名
        fwrite($fp,$img);  //写入文件 
        fclose($fp);
}

$codeUrl="http://202.116.160.170/CheckCode.aspx";
getCode($codeUrl);

?>

<html>
<head>
    <title>正方233333</title>
</head>
<body>

<div>
    <form action="handle.php" method="post">
        <a href="http://202.116.160.170/default2.aspx">202.116.160.170</a>
        <label>用户名：</label>
        <input name="xh" type="text"></input>
        <label>密码：</label>
        <input name="pw" type="password"></input>
        <label>验证码：</label>
        <input name="code" type="text"></input>
        <img src="verifyCode.jpg">
        <input type="submit" name="button" value="登陆"></input>
        <input type="reset" name="button" value="重置"></input>
    </form>
</div>

</body>
</html>

