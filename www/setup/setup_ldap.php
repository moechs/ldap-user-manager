<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include_once "web_functions.inc.php";
include_once "ldap_functions.inc.php";
include_once "module_functions.inc.php";

validate_setup_cookie();
set_page_access("setup");

render_header("$ORGANISATION_NAME 初始化用户管理系统");

$ldap_connection = open_ldap_connection();

$no_errors = TRUE;
$show_create_admin_button = FALSE;

# 执行初始化流程
if (isset($_POST['fix_problems'])) {
?>
<script>
    $(document).ready(function(){
     $('[data-toggle="popover"]').popover(); 
    });
</script>
<div class='container'>

 <div class="panel panel-default">
  <div class="panel-heading">正在更新 LDAP...</div>
   <div class="panel-body">
    <ul class="list-group">

<?php

 if (isset($_POST['setup_group_ou'])) {
  $ou_add = @ ldap_add($ldap_connection, $LDAP['group_dn'], array( 'objectClass' => 'organizationalUnit', 'ou' => $LDAP['group_ou'] ));
  if ($ou_add == TRUE) {
   print "$li_good 已创建组织单元 <strong>{$LDAP['group_dn']}</strong></li>\n";
  } else {
   $error = ldap_error($ldap_connection);
   print "$li_fail 无法创建 {$LDAP['group_dn']}: <pre>$error</pre></li>\n";
   $no_errors = FALSE;
  }
 }

 if (isset($_POST['setup_user_ou'])) {
  $ou_add = @ ldap_add($ldap_connection, $LDAP['user_dn'], array( 'objectClass' => 'organizationalUnit', 'ou' => $LDAP['user_ou'] ));
  if ($ou_add == TRUE) {
   print "$li_good 已创建组织单元 <strong>{$LDAP['user_dn']}</strong></li>\n";
  } else {
   $error = ldap_error($ldap_connection);
   print "$li_fail 无法创建 {$LDAP['user_dn']}: <pre>$error</pre></li>\n";
   $no_errors = FALSE;
  }
 }

 if (isset($_POST['setup_last_gid'])) {

  $highest_gid = ldap_get_highest_id($ldap_connection,'gid');
  $description = "用于记录创建 Posix 群组时使用的最后一个 GID，避免重复使用已删除群组的 GID。";

  $add_lastgid_r = array( 'objectClass' => array('device','top'),
                          'serialnumber' => $highest_gid,
                          'description' => $description );

  $gid_add = @ ldap_add($ldap_connection, "cn=lastGID,{$LDAP['base_dn']}", $add_lastgid_r);

  if ($gid_add == TRUE) {
   print "$li_good 已创建记录：<strong>cn=lastGID,{$LDAP['base_dn']}</strong></li>\n";
  } else {
   $error = ldap_error($ldap_connection);
   print "$li_fail 无法创建 cn=lastGID,{$LDAP['base_dn']}: <pre>$error</pre></li>\n";
   $no_errors = FALSE;
  }
 }

 if (isset($_POST['setup_last_uid'])) {

  $highest_uid = ldap_get_highest_id($ldap_connection,'uid');
  $description = "用于记录创建 Posix 账户时使用的最后一个 UID，避免重复使用已删除账户的 UID。";

  $add_lastuid_r = array( 'objectClass' => array('device','top'),
                          'serialnumber' => $highest_uid,
                          'description' => $description );

  $uid_add = @ ldap_add($ldap_connection, "cn=lastUID,{$LDAP['base_dn']}", $add_lastuid_r);

  if ($uid_add == TRUE) {
   print "$li_good 已创建记录：<strong>cn=lastUID,{$LDAP['base_dn']}</strong></li>\n";
  } else {
   $error = ldap_error($ldap_connection);
   print "$li_fail 无法创建 cn=lastUID,{$LDAP['base_dn']}: <pre>$error</pre></li>\n";
   $no_errors = FALSE;
  }
 }

 if (isset($_POST['setup_default_group'])) {

  $group_add = ldap_new_group($ldap_connection,$DEFAULT_USER_GROUP);
  
  if ($group_add == TRUE) {
   print "$li_good 已创建默认群组：<strong>$DEFAULT_USER_GROUP</strong></li>\n";
  } else {
   $error = ldap_error($ldap_connection);
   print "$li_fail 无法创建默认群组：<pre>$error</pre></li>\n";
   $no_errors = FALSE;
  }
 }

 if (isset($_POST['setup_admins_group'])) {

  $group_add = ldap_new_group($ldap_connection,$LDAP['admins_group']);
  
  if ($group_add == TRUE) {
   print "$li_good 已创建 LDAP 管理员群组：<strong>{$LDAP['admins_group']}</strong></li>\n";
  } else {
   $error = ldap_error($ldap_connection);
   print "$li_fail 无法创建 LDAP 管理员群组：<pre>$error</pre></li>\n";
   $no_errors = FALSE;
  }
 }

 $admins = ldap_get_group_members($ldap_connection,$LDAP['admins_group']);

 if (count($admins) < 1) {

  ?>
  <div class="form-group">
  <form action="<?php print "{$SERVER_PATH}account_manager/new_user.php"; ?>" method="post">
  <input type="hidden" name="setup_admin_account">
  <?php
  print "$li_fail LDAP 管理员群组为空。";
  print "<a href='#' data-toggle='popover' title='LDAP 管理员账户' data-content='";
  print "只有该群组（{$LDAP['admins_group']}）的成员才能访问账户管理功能，请添加成员。";
  print "'>这是什么？</a>";
  print "<label class='pull-right'><input type='checkbox' name='setup_admin_account' class='pull-right' checked>创建新账号并添加到管理员群组？&nbsp;</label>";
  print "</li>\n";
  $show_create_admin_button = TRUE;
 } else {
  print "$li_good LDAP 管理员群组 (<strong>{$LDAP['admins_group']}</strong>) 已有成员。</li>";
 }

?>
  </ul>
 </div>
</div>
<?php

##############

 if ($no_errors == TRUE) {
  if ($show_create_admin_button == FALSE) {
 ?>
 </form>
 <div class='well'>
  <form action="<?php print $THIS_MODULE_PATH; ?>">
   <input type='submit' class="btn btn-success center-block" value='完成' class='center-block'>
  </form>
 </div>
 <?php
  } else {
  ?>
    <div class='well'>
    <input type='submit' class="btn btn-warning center-block" value='创建新账号 >' class='center-block'>
   </form>
  </div>
  <?php 
  }
 } else {
 ?>
 </form>
 <div class='well'>
  <form action="<?php print $THIS_MODULE_PATH; ?>/run_checks.php">
   <input type='submit' class="btn btn-danger center-block" value='< 重新运行安装' class='center-block'>
  </form>
 </div>
<?php

 }

}

render_footer();

?>