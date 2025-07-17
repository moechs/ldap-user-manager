<?php

set_include_path(".:" . __DIR__ . "/../includes/");
session_start();

include_once "web_functions.inc.php";

render_header("$ORGANISATION_NAME - 申请账户");

if ($ACCOUNT_REQUESTS_ENABLED == FALSE) {

?><div class='alert alert-warning'><p class='text-center'>账户申请功能已禁用。</p></div><?php

render_footer();
exit(0);

}

if($_POST) {

  $error_messages = array();

  if(! isset($_POST['validate']) or strcasecmp($_POST['validate'], $_SESSION['proof_of_humanity']) != 0) {
    array_push($error_messages, "验证码与图片不匹配。");
  }

  if (! isset($_POST['firstname']) or $_POST['firstname'] == "") {
    array_push($error_messages, "您没有输入名字。");
  }
  else {
    $firstname=filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  }

  if (! isset($_POST['lastname']) or $_POST['lastname'] == "") {
    array_push($error_messages, "您没有输入姓氏。");
  }
  else {
    $lastname=filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  }

  if (isset($_POST['email']) and $_POST['email'] != "") {
    $email=filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  }

  if (isset($_POST['notes']) and $_POST['notes'] != "") {
    $notes=filter_var($_POST['notes'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  }


  if (count($error_messages) > 0) { ?>
    <div class="alert alert-danger" role="alert">
    请求无法发送，原因如下：
    <p>
    <ul>
      <?php
       foreach($error_messages as $message) {
         print "<li>$message</li>\n";
       }
      ?>
    </ul>
    </div>
  <?php
  }
  else {

    $mail_subject = "$firstname $lastname 申请了 $ORGANISATION_NAME 的账户。";

$link_url="{$SITE_PROTOCOL}{$SERVER_HOSTNAME}{$SERVER_PATH}account_manager/new_user.php?account_request&first_name=$firstname&last_name=$lastname&email=$email";

if (!isset($email)) { $email = "n/a"; }
if (!isset($notes)) { $notes = "n/a"; }

    $mail_body = <<<EoT
有人提交了 $ORGANISATION_NAME 账户申请：
<p>
名字: <b>$firstname</b><br>
姓氏: <b>$lastname</b><br>
邮箱: <b>$email</b><br>
备注: <pre>$notes</pre><br>
<p>
<a href="$link_url">点击这里创建该账户。</a>
EoT;

     include_once "mail_functions.inc.php";
     $sent_email = send_email($ACCOUNT_REQUESTS_EMAIL,"$ORGANISATION_NAME 账户申请",$mail_subject,$mail_body);
     if ($sent_email) { ?>
       <div class="container">
         <div class="col-sm-6 col-sm-offset-3">
           <div class="panel panel-success">
             <div class="panel-heading">谢谢</div>
             <div class="panel-body">
               请求已发送，管理员会尽快处理。
             </div>
           </div>
         </div>
       </div>
     <?php }
     else { ?>
       <div class="container">
         <div class="col-sm-6 col-sm-offset-3">
           <div class="panel panel-danger">
             <div class="panel-heading">错误</div>
             <div class="panel-body">
               很遗憾，由于技术原因，账户申请未能成功发送。
             </div>
           </div>
         </div>
       </div>
    <?php
    }
   render_footer();
   exit(0);

  }
}
?>
<div class="container">
 <div class="col-sm-8 col-sm-offset-2">

  <div class="panel panel-default">
    <div class="panel-body">
    使用此表单向 <?php print $ORGANISATION_NAME; ?> 管理员发送账户申请。
    如果管理员批准，TA 会与您联系并提供新账户信息。
    </div>
  </div>

  <div class="panel panel-default"> 
   <div class="panel-heading text-center">申请 <?php print $ORGANISATION_NAME; ?> 账户</div>
   <div class="panel-body text-center">

   <form class="form-horizontal" action='' method='post'>

    <div class="form-group">
     <label for="firstname" class="col-sm-4 control-label">名字</label>
     <div class="col-sm-6">
      <input type="text" class="form-control" id="firstname" name="firstname" placeholder="必填" <?php if (isset($firstname)) { print "value='$firstname'"; } ?>>
     </div>
    </div>

    <div class="form-group">
     <label for="lastname" class="col-sm-4 control-label">姓氏</label>
     <div class="col-sm-6">
      <input type="text" class="form-control" id="lastname" name="lastname" placeholder="必填" <?php if (isset($lastname)) { print "value='$lastname'"; } ?>>
     </div>
    </div>

    <div class="form-group">
     <label for="email" class="col-sm-4 control-label">邮箱</label>
     <div class="col-sm-6">
      <input type="text" class="form-control" id="email" name="email" <?php if (isset($email)) { print "value='$email'"; } ?>>
     </div>
    </div>

    <div class="form-group">
     <label for="Notes" class="col-sm-4 control-label">备注</label>
     <div class="col-sm-6">
      <textarea class="form-control" id="notes" name="notes" placeholder="可输入您想让管理员了解的其他信息"><?php if (isset($notes)) { print $notes; } ?></textarea>
     </div>
    </div>

    <div class="form-group">
     <label for="validate" class="col-sm-4 control-label">验证码</label>
     <div class="col-sm-6">
      <span class="center-block">
        <img src="human.php" class="human-check" alt="人机验证">
        <button type="button" class="btn btn-default btn-sm" onclick="document.querySelector('.human-check').src = 'human.php?' + Date.now()">
         <span class="glyphicon glyphicon-refresh"></span> 刷新
        </button>
      </span>
      <input type="text" class="form-control center-block" id="validate" name="validate" placeholder="请输入图片中的字符">
     </div>
    </div>

    <div class="form-group">
     <button type="submit" class="btn btn-default">提交申请</button>
    </div>
   
   </form>
  </div>
 </div>
</div>
<?php
render_footer();
?>
