<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include "web_functions.inc.php";
include "ldap_functions.inc.php";

if (isset($_GET["unauthorised"])) { $display_unauth = TRUE; }
if (isset($_GET["session_timeout"])) { $display_logged_out = TRUE; }
if (isset($_GET["redirect_to"])) { $redirect_to = $_GET["redirect_to"]; }

if (isset($_GET['logged_out'])) {
?>
<div class="alert alert-warning">
<p class="text-center">You've been automatically logged out because you've been inactive for over
<?php print $SESSION_TIMEOUT; ?> minutes. Click on the 'Log in' link to get back into the system.</p>
</div>
<?php
}


if (isset($_POST["user_id"]) and isset($_POST["password"])) {

 $ldap_connection = open_ldap_connection();
 $account_id = ldap_auth_username($ldap_connection,$_POST["user_id"],$_POST["password"]);
 $is_admin = ldap_is_group_member($ldap_connection,$LDAP['admins_group'],$account_id);

 ldap_close($ldap_connection);

 if ($account_id != FALSE) {

  set_passkey_cookie($account_id,$is_admin);
  if (isset($_POST["redirect_to"])) {
   header("Location: //{$_SERVER['HTTP_HOST']}" . base64_decode($_POST['redirect_to']) . "\n\n");
  }
  else {
   if ($IS_ADMIN) { $default_module = "account_manager"; } else { $default_module = "change_password"; }
   header("Location: //{$_SERVER['HTTP_HOST']}{$SERVER_PATH}$default_module?logged_in\n\n");
  }
 }
 else {
  header("Location: //{$_SERVER['HTTP_HOST']}{$THIS_MODULE_PATH}/index.php?invalid\n\n");
 }

}
else {

 render_header("$ORGANISATION_NAME 用户管理系统 - 登录");

 ?>
<div class="container">
 <div class="col-sm-8 col-sm-offset-2">

  <div class="panel panel-default">
   <div class="panel-heading text-center">登录</div>
   <div class="panel-body text-center">

   <?php if (isset($display_unauth)) { ?>
   <div class="alert alert-warning">
    请先登录！
   </div>
   <?php } ?>

   <?php if (isset($display_logged_out)) { ?>
   <div class="alert alert-warning">
    由于会话已过期，您已退出。请重新登录！
   </div>
   <?php } ?>

   <?php if (isset($_GET["invalid"])) { ?>
   <div class="alert alert-warning">
    无效的用户名或密码！
   </div>
   <?php } ?>

   <form class="form-horizontal" action='' method='post'>
    <?php if (isset($redirect_to) and ($redirect_to != "")) { ?><input type="hidden" name="redirect_to" value="<?php print htmlspecialchars($redirect_to); ?>"><?php } ?>

    <div class="form-group">
     <label for="username" class="col-sm-4 control-label"><?php print $SITE_LOGIN_FIELD_LABEL; ?></label>
     <div class="col-sm-6">
      <input type="text" class="form-control" id="user_id" name="user_id">
     </div>
    </div>

    <div class="form-group">
     <label for="password" class="col-sm-4 control-label">密码</label>
     <div class="col-sm-6">
      <input type="password" class="form-control" id="confirm" name="password">
     </div>
    </div>

    <div class="form-group">
     <button type="submit" class="btn btn-default">登录</button>
    </div>

   </form>
  </div>
 </div>
</div>
<?php
}
render_footer();
?>
