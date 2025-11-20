<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>群主后台</title>
<style>
body{font-family:Arial,sans-serif;background:#f0f2f5;margin:0;padding:0;}
.container{max-width:600px;margin:20px auto;padding:10px;}
input,button{padding:8px;margin:5px; border-radius:4px;}
button{background:#0b93f6;color:#fff;border:none;cursor:pointer;}
</style>
</head>
<body>
<div class="container">
<h2>群主后台</h2>
<form id="form" onsubmit="sendMessage(); return false;">
    <input type="password" id="password" placeholder="输入密码" required>
    <input type="text" id="message" placeholder="群主消息..." required>
    <button type="submit">发送</button>
</form>
</div>

<script>
function sendMessage(){
    let pass = document.getElementById('password').value.trim();
    let msg  = document.getElementById('message').value.trim();
    if(!pass||!msg) return;

    fetch('admin_send.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'password='+encodeURIComponent(pass)+'&message='+encodeURIComponent(msg)
    }).then(r=>r.text()).then(res=>{
        if(res==='ok'){
            document.getElementById('message').value='';
            alert('发送成功');
        }else{
            alert(res);
        }
    });
}
</script>
</body>
</html>
