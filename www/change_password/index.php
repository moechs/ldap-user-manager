<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include_once "web_functions.inc.php";
include_once "ldap_functions.inc.php";

set_page_access("user");

if (isset($_POST['change_password'])) {

 if (!$_POST['password']) { $not_strong_enough = 1; }
 if ((!is_numeric($_POST['pass_score']) or $_POST['pass_score'] < 3) and $ACCEPT_WEAK_PASSWORDS != TRUE) { $not_strong_enough = 1; }
 if (preg_match("/\"|'/",$_POST['password'])) { $invalid_chars = 1; }
 if ($_POST['password'] != $_POST['password_match']) { $mismatched = 1; }

 if (!isset($mismatched) and !isset($not_strong_enough) and !isset($invalid_chars) ) {

  $ldap_connection = open_ldap_connection();
  ldap_change_password($ldap_connection,$USER_ID,$_POST['password']) or die("change_ldap_password() failed.");

  render_header("$ORGANISATION_NAME 用户管理系统 - 更改密码");
  ?>
  <div class="container">
    <div class="col-sm-6 col-sm-offset-3">
      <div class="panel panel-success">
        <div class="panel-heading">成功</div>
        <div class="panel-body">
          您的密码已成功更新。
        </div>
      </div>
    </div>
  </div>
  <?php
  render_footer();
  exit(0);
 }

}

render_header("更改您的 $ORGANISATION_NAME 密码");

if (isset($not_strong_enough)) {  ?>
<div class="alert alert-warning">
 <p class="text-center">密码强度不够。</p>
</div>
<?php }

if (isset($invalid_chars)) {  ?>
<div class="alert alert-warning">
 <p class="text-center">密码包含无效字符。</p>
</div>
<?php }

if (isset($mismatched)) {  ?>
<div class="alert alert-warning">
 <p class="text-center">两次输入的密码不匹配。</p>
</div>
<?php }

?>

<script src="<?php print $SERVER_PATH; ?>js/zxcvbn.min.js"></script>
<script type="text/javascript" src="<?php print $SERVER_PATH; ?>js/zxcvbn-bootstrap-strength-meter.js"></script>
<script type="text/javascript">$(document).ready(function(){	$("#StrengthProgressBar").zxcvbnProgressBar({ passwordInput: "#password" });});</script>

<div class="container">
 <div class="col-sm-6 col-sm-offset-3">

  <div class="panel panel-default">
   <div class="panel-heading text-center">更改您的密码</div>

   <ul class="list-group">
    <li class="list-group-item">
      <p>使用此表单更改您的 <?php print $ORGANISATION_NAME; ?> 密码。</p>  
      <p>当您开始输入新密码时，底部的仪表将显示其安全强度。</p>  
      <p>请在<b>确认</b>字段中再次输入您的密码。</p>  
      <p>如果密码不匹配，两个字段都会显示红色边框。</p>  
     </li>
   </ul>

   <div class="panel-body text-center">
   
    <form class="form-horizontal" action='' method='post'>

     <input type='hidden' id="change_password" name="change_password">
     <input type='hidden' id="pass_score" value="0" name="pass_score">
     
     <div class="form-group" id="password_div">
      <label for="password" class="col-sm-4 control-label">密码</label>
      <div class="col-sm-6">
       <input type="password" class="form-control" id="password" name="password">
      </div>
     </div>

     <script>
      function check_passwords_match() {

        if (document.getElementById('password').value != document.getElementById('confirm').value ) {
            document.getElementById('password_div').classList.add("has-error");
            document.getElementById('confirm_div').classList.add("has-error");
        }
        else {
         document.getElementById('password_div').classList.remove("has-error");
         document.getElementById('confirm_div').classList.remove("has-error");
        }
       }
     </script>

     <div class="form-group" id="confirm_div">
      <label for="password" class="col-sm-4 control-label">确认密码</label>
      <div class="col-sm-6">
       <input type="password" class="form-control" id="confirm" name="password_match" onkeyup="check_passwords_match()">
      </div>
     </div>

     <div class="form-group">
       <button type="submit" class="btn btn-default">更改密码</button>
     </div>
     
    </form>

    <div class="progress">
     <div id="StrengthProgressBar" class="progress progress-bar"></div>
    </div>

   </div>
  </div>

 </div>
</div>
<?php

render_footer();

?>