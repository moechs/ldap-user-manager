<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include_once "web_functions.inc.php";
include_once "ldap_functions.inc.php";

if (isset($_POST["admin_password"])) {

 $ldap_connection = open_ldap_connection();
 $user_auth = ldap_setup_auth($ldap_connection,$_POST["admin_password"]);
 ldap_close($ldap_connection);

 if ($user_auth != FALSE) {
  set_setup_cookie($user_auth);
  header("Location: //{$_SERVER["HTTP_HOST"]}{$THIS_MODULE_PATH}/run_checks.php\n\n");
 }
 else {
  header("Location: //{$_SERVER["HTTP_HOST"]}{$THIS_MODULE_PATH}/index.php?invalid\n\n");
 }

}
else {

 render_header("$ORGANISATION_NAME 初始化用户管理系统");

 if (isset($_GET["invalid"])) {
 ?>
 <div class="alert alert-warning">
  <p class="text-center">无效的密码！</p>
 </div>
 <?php
 }
 ?>
 <div class="container">
  <div class="panel panel-default">
   <div class="panel-heading text-center">请输入账号 <?php print $LDAP['admin_bind_dn']; ?> 的密码</div>
   <div class="panel-body text-center">
    <form class="form-inline" action='' method='post'>
     <div class="form-group">
      <input type='password' class="form-control" name='admin_password'>
     </div>
     <div class="form-group">
      <input type='submit' class="btn btn-default" value='登录'>
     </div>
    </form>
   </div>
  </div>
 </div>
<?php
}
render_footer();
?>
