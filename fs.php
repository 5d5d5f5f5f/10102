<?php
date_default_timezone_set("Asia/Shanghai");

$chatFile = __DIR__.'/xx.json';
$maxMessages = 100;
$adminPassword = '123';
$groupOwner = '群主'; // 群主固定昵称

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// 清空聊天记录
if(isset($_POST['clear']) && $_POST['clear']==1){
    if(($_POST['password'] ?? '') !== $adminPassword){
        echo "密码错误"; exit;
    }
    file_put_contents($chatFile,json_encode([]));
    exit("已清空");
}

$nickname = $_POST['nickname'] ?? '';
$message  = $_POST['message'] ?? '';

if($message){
    $messages = file_exists($chatFile) ? json_decode(file_get_contents($chatFile),true) : [];
    $time = date("Y-m-d H:i:s");
    $id = count($messages) ? $messages[count($messages)-1]['id']+1 : 1;

    $color = '#'.substr(md5($nickname),0,6);
    $extraClass = '';

    // 群主消息
    if($nickname === $groupOwner){
        $color = '#ff0000';
        $extraClass = ' group-owner';
    }

    // 公告消息
    $isAnnouncement = false;
    if(strpos($message, '公告:') === 0){
        $isAnnouncement = true;
        $message = trim(substr($message,3));
        $extraClass .= ' announcement';
    }

    // 私聊消息
    $isPrivate = false;
    $privateTarget = '';
    if(preg_match('/^@(\S+)\s+(.*)$/u', $message, $matches)){
        $isPrivate = true;
        $privateTarget = $matches[1];
        $message = $matches[2];
    }

    $html = "<div class='chat-message$extraClass' data-nickname='".htmlspecialchars($nickname,ENT_QUOTES)."' data-private='$privateTarget'>
                <div class='nickname' style='color:$color'>[$time] $nickname:".($isPrivate?" [私聊]":"")."</div>
                <div class='message'>$message</div>
            </div>";

    $msgObj = [
        'id'=>$id,
        'nickname'=>$nickname,
        'message'=>$message,
        'private'=>$privateTarget,
        'announcement'=>$isAnnouncement,
        'html'=>$html
    ];

    $messages[] = $msgObj;
    if(count($messages) > $maxMessages) $messages = array_slice($messages, -$maxMessages);

    file_put_contents($chatFile,json_encode($messages,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    exit('ok');
}

// 返回分页消息
$messages = file_exists($chatFile) ? json_decode(file_get_contents($chatFile),true) : [];
$totalPages = ceil(count($messages)/$maxMessages);
$totalPages = $totalPages?:1;
$page = max(1,min($page,$totalPages));

$start = ($page-1)*$maxMessages;
$messagesSlice = array_slice($messages,$start,$maxMessages);

$msgHtml = [];
foreach($messagesSlice as $m){
    $msgHtml[] = $m['html'];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'messages'=>$msgHtml,
    'currentPage'=>$page,
    'totalPages'=>$totalPages
],JSON_UNESCAPED_UNICODE);
