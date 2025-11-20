<?php
date_default_timezone_set("Asia/Shanghai");
$chatFile = __DIR__.'/xx.json';
$maxMessages = 100;
$adminPassword = '123';

$password = $_POST['password'] ?? '';
$message  = strip_tags($_POST['message'] ?? '');

if($password !== $adminPassword){
    echo "密码错误"; exit;
}

if(!$message){ echo "消息为空"; exit; }

$nickname = "群主";
$color = '#ff0000';
$time = date("Y-m-d H:i:s");

$messages = file_exists($chatFile) ? json_decode(file_get_contents($chatFile),true) : [];
$id = count($messages) ? $messages[count($messages)-1]['id']+1 : 1;

$msgObj = [
    'id'=>$id,
    'nickname'=>$nickname,
    'message'=>$message,
    'html'=> "<div class='chat-message'><div class='nickname' style='color:$color'>[$time] $nickname:</div><div class='message'>$message</div></div>"
];

$messages[] = $msgObj;
if(count($messages)>$maxMessages) $messages = array_slice($messages,-$maxMessages);
file_put_contents($chatFile,json_encode($messages,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));

echo "ok";
