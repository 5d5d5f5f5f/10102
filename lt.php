<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>吹水室</title>
<style>
body { font-family: Arial, sans-serif; background:#f0f2f5; margin:0; padding:0; }
.container { max-width:800px; margin:0 auto; padding:10px; }
h2 { text-align:center; }
#chatbox { width:100%; height:400px; border:1px solid #ccc; overflow-y: scroll; background:#fff; padding:10px; border-radius:8px; box-sizing:border-box; }
#form { display:flex; flex-wrap:wrap; margin-top:10px; }
#form input[type="text"], #form input[type="password"] { flex:1; padding:8px; margin:5px 5px 5px 0; border-radius:4px; border:1px solid #ccc; box-sizing:border-box; }
#form button { padding:8px 15px; border-radius:4px; border:none; background:#0b93f6; color:#fff; cursor:pointer; margin:5px 0; }
#controls { text-align:right; margin:10px 0; }
.pagination { text-align:center; margin:10px 0; }
.pagination button { padding:5px 10px; margin:0 3px; border-radius:4px; border:1px solid #ccc; background:#fff; cursor:pointer; }
.pagination button.active { background:#0b93f6; color:#fff; border-color:#0b93f6; }
.chat-message { margin-bottom:10px; }
.chat-message .nickname { font-weight:bold; margin-bottom:2px; }
.chat-message .message { display:inline-block; padding:6px 10px; border-radius:12px; max-width:70%; background:#e1f5fe; word-wrap:break-word; }
#newMsgTip { position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%); background: #ff9800; color:#fff; padding:10px 20px; border-radius:20px; cursor:pointer; z-index:9999; }
</style>
</head>
<body>

<div class="container">
<h2>🔊吹水室</h2>
<h5>刷新查看新消息</h5>
<div id="chatbox"></div>
<div class="pagination" id="pagination"></div>

<div id="controls">
    <input type="password" id="clearPass" placeholder="输入密码清空聊天">
    <button onclick="clearChat()">清空聊天记录</button>
</div>

<form id="form" onsubmit="sendMessage(); return false;">
    <input type="text" id="nickname" placeholder="昵称" required>
    <input type="text" id="message" placeholder="输入消息..." required>
    <button type="submit">发送</button>
</form>
</div>

<script>
let currentPage = 1;
let autoScroll = true;
let lastMessageCount = 0;
let myNickname = '';

// 判断是否在底部
function isAtBottom() {
    let box = document.getElementById('chatbox');
    return box.scrollTop + box.clientHeight >= box.scrollHeight - 10;
}

// 显示新消息提示
function showNewMsgTip() {
    if (document.getElementById("newMsgTip")) return;
    let tip = document.createElement("div");
    tip.id = "newMsgTip";
    tip.innerText = "🔔 有新消息，点击查看 ↓";
    tip.onclick = () => {
        autoScroll = true;
        loadMessages(currentPage);
        tip.remove();
    };
    document.body.appendChild(tip);
}

// 加载聊天记录
function loadMessages(page = 1){
    fetch('fs.php?page=' + page)
    .then(r => r.json())
    .then(data => {
        let chatbox = document.getElementById('chatbox');
        let wasBottom = isAtBottom();
        chatbox.innerHTML = data.messages.join('');
        currentPage = data.currentPage;

        // 新消息提示
        if (data.messages.length !== lastMessageCount && !wasBottom) showNewMsgTip();
        lastMessageCount = data.messages.length;

        // 如果在底部，则自动滚动
        if (wasBottom || autoScroll) {
            chatbox.scrollTop = chatbox.scrollHeight;
            let tip = document.getElementById("newMsgTip");
            if (tip) tip.remove();
        }

        // 分页按钮
        let pagination = document.getElementById('pagination');
        pagination.innerHTML = '';
        for(let i = 1; i <= data.totalPages; i++){
            let btn = document.createElement('button');
            btn.textContent = i;
            if(i===currentPage) btn.classList.add('active');
            btn.onclick = ()=>loadMessages(i);
            pagination.appendChild(btn);
        }
    });
}

// 发送消息
function sendMessage(){
    if(!myNickname){
        myNickname = document.getElementById('nickname').value.trim();
        if(!myNickname){ alert('请输入昵称'); return; }
    }
    let msg = document.getElementById('message').value.trim();
    if(!msg) return;

    fetch('fs.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'nickname='+encodeURIComponent(myNickname)+'&message='+encodeURIComponent(msg)
    }).then(r=>r.text()).then(res=>{
        if(res==='ok'){
            document.getElementById('message').value='';
            autoScroll = true;
            loadMessages(currentPage);
        }
    });
}

// 清空聊天记录
function clearChat(){
    let password = document.getElementById('clearPass').value.trim();
    if(!password){ alert('请输入密码'); return; }

    fetch('fs.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'clear=1&password='+encodeURIComponent(password)
    }).then(r=>r.text()).then(msg=>{
        alert(msg);
        loadMessages(1);
    });
}

// 滚动监听
document.getElementById('chatbox').addEventListener('scroll', function(){
    autoScroll = isAtBottom();
});

// 自动刷新最新消息
setInterval(()=>loadMessages(currentPage),1500);

// 初始化加载
loadMessages();
</script>

</body>
</html>
