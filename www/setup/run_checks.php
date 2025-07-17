<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include_once "web_functions.inc.php";
include_once "ldap_functions.inc.php";
include_once "module_functions.inc.php";

validate_setup_cookie();
set_page_access("setup");

render_header("$ORGANISATION_NAME 初始化用户管理系统");

$show_finish_button = TRUE;

$ldap_connection = open_ldap_connection();

?>
<script>
    $(document).ready(function(){
     $('[data-toggle="popover"]').popover();
    });
</script>
<div class="form-group">
  <form action="<?php print $THIS_MODULE_PATH; ?>/setup_ldap.php" method="post">
  <input type="hidden" name="fix_problems">


    <div class='container'>

     <div class="panel panel-default">
      <div class="panel-heading">LDAP连接测试</div>
      <div class="panel-body">
       <ul class="list-group">
<?php

#Can we connect?  The open_ldap_connection() function will call die() if we can't.
print "$li_good 已连接到 {$LDAP['uri']}</li>\n";

#TLS?
if ($LDAP['connection_type'] != "plain") {
 print "$li_good 通过 {$LDAP['connection_type']} 加密连接到 {$LDAP['uri']}</li>\n";
}
else {
 print "$li_warn 无法通过 StartTLS 连接到 {$LDAP['uri']}。 ";
 print "<a href='#' data-toggle='popover' title='StartTLS' data-content='";
 print "与LDAP服务器的连接正常，但无法启用加密通信。";
 print "'>这是什么？</a></li>\n";
}


?>
       </ul>
      </div>
     </div>

     <div class="panel panel-default">
      <div class="panel-heading">LDAP RFC2307BIS架构检查</div>
      <div class="panel-body">
       <ul class="list-group">
<?php

$bis_detected = ldap_detect_rfc2307bis($ldap_connection);

if ($bis_detected == TRUE) {

 if ($LDAP['forced_rfc2307bis'] == TRUE) {
  print "$li_warn FORCE_RFC2307BIS 设置为 TRUE，这意味着用户管理器跳过了RFC2307BIS架构的自动检测。如果您的LDAP服务器实际上没有RFC2307BIS架构可用，这可能会在创建组时导致错误。 ";
 }
 else {
  print "$li_good RFC2307BIS架构似乎可用。 ";
 }
 print "<a href='#' data-toggle='popover' title='RFC2307BIS架构' data-content='";
 print "RFC2307BIS架构增强了posixGroups，允许您在LDAP搜索中使用\"memberOf\"。";
 print "'>这是什么？</a>";
 print "</li>\n";

}
else {

 print "$li_warn RFC2307BIS架构似乎不可用。<br>\n如果这是不正确的，请将FORCE_RFC2307BIS设置为TRUE，重启用户管理器并再次运行设置。 ";
 print "<a href='#' data-toggle='popover' title='RFC2307BIS' data-content='";
 print "RFC2307BIS架构增强了posixGroups，允许进行memberOf LDAP搜索。";
 print "'>这是什么？</a>";
 print "</li>\n";

}


?>
       </ul>
      </div>
     </div>

     <div class="panel panel-default">
      <div class="panel-heading">LDAP 组织单元检查</div>
      <div class="panel-body">
       <ul class="list-group">
<?php

$group_filter = "(&(objectclass=organizationalUnit)(ou={$LDAP['group_ou']}))";
$ldap_group_search = ldap_search($ldap_connection, "{$LDAP['base_dn']}", $group_filter);
$group_result = ldap_get_entries($ldap_connection, $ldap_group_search);

if ($group_result['count'] != 1) {

 print "$li_fail 组织单元 (<strong>{$LDAP['group_dn']}</strong>) 不存在。 ";
 print "<a href='#' data-toggle='popover' title='{$LDAP['group_dn']}' data-content='";
 print "这是存储组的组织单元(OU)。";
 print "'>这是什么？</a>";
 print "<label class='pull-right'><input type='checkbox' name='setup_group_ou' class='pull-right' checked>创建？&nbsp;</label>";
 print "</li>\n";
 $show_finish_button = FALSE;

}
else {
 print "$li_good 组织单元 (<strong>{$LDAP['group_dn']}</strong>) 存在。</li>";
}

$user_filter  = "(&(objectclass=organizationalUnit)(ou={$LDAP['user_ou']}))";
$ldap_user_search = ldap_search($ldap_connection, "{$LDAP['base_dn']}", $user_filter);
$user_result = ldap_get_entries($ldap_connection, $ldap_user_search);

if ($user_result['count'] != 1) {

 print "$li_fail 用户组织单元 (<strong>{$LDAP['user_dn']}</strong>) 不存在。 ";
 print "<a href='#' data-toggle='popover' title='{$LDAP['user_dn']}' data-content='";
 print "这是存储用户账户的组织单元(OU)。";
 print "'>这是什么？</a>";
 print "<label class='pull-right'><input type='checkbox' name='setup_user_ou' class='pull-right' checked>创建？&nbsp;</label>";
 print "</li>\n";
 $show_finish_button = FALSE;

}
else {
 print "$li_good 用户组织单元 (<strong>{$LDAP['user_dn']}</strong>) 存在。</li>";
}

?>
       </ul>
      </div>
     </div>

     <div class="panel panel-default">
      <div class="panel-heading">LDAP 组和设置</div>
      <div class="panel-body">
       <ul class="list-group">
<?php

$gid_filter  = "(&(objectclass=device)(cn=lastGID))";
$ldap_gid_search = ldap_search($ldap_connection, "{$LDAP['base_dn']}", $gid_filter);
$gid_result = ldap_get_entries($ldap_connection, $ldap_gid_search);

if ($gid_result['count'] != 1) {

 print "$li_warn <strong>lastGID</strong> 条目不存在。 ";
 print "<a href='#' data-toggle='popover' title='cn=lastGID,{$LDAP['base_dn']}' data-content='";
 print "这用于存储创建POSIX组时使用的最后一个组ID。没有这个，会找到当前最高的组ID并递增，但这可能会重用已删除组的GID。";
 print "'>这是什么？</a>";
 print "<label class='pull-right'><input type='checkbox' name='setup_last_gid' class='pull-right' checked>创建？&nbsp;</label>";
 print "</li>\n";
 $show_finish_button = FALSE;

}
else {
 print "$li_good <strong>lastGID</strong> 条目存在。</li>";
}


$uid_filter  = "(&(objectclass=device)(cn=lastUID))";
$ldap_uid_search = ldap_search($ldap_connection, "{$LDAP['base_dn']}", $uid_filter);
$uid_result = ldap_get_entries($ldap_connection, $ldap_uid_search);

if ($uid_result['count'] != 1) {

 print "$li_warn <strong>lastUID</strong> 条目不存在。 ";
 print "<a href='#' data-toggle='popover' title='cn=lastUID,{$LDAP['base_dn']}' data-content='";
 print "这用于存储创建POSIX账户时使用的最后一个用户ID。没有这个，会找到当前最高的用户ID并递增，但这可能会重用已删除账户的UID。";
 print "'>这是什么？</a>";
 print "<label class='pull-right'><input type='checkbox' name='setup_last_uid' class='pull-right' checked>创建？&nbsp;</label>";
 print "</li>\n";
 $show_finish_button = FALSE;

}
else {
 print "$li_good <strong>lastUID</strong> 条目存在。</li>";
}


$defgroup_filter  = "(&(objectclass=posixGroup)({$LDAP['group_attribute']}={$DEFAULT_USER_GROUP}))";
$ldap_defgroup_search = ldap_search($ldap_connection, "{$LDAP['base_dn']}", $defgroup_filter);
$defgroup_result = ldap_get_entries($ldap_connection, $ldap_defgroup_search);

if ($defgroup_result['count'] != 1) {

 print "$li_warn 默认组 (<strong>$DEFAULT_USER_GROUP</strong>) 不存在。 ";
 print "<a href='#' data-toggle='popover' title='默认用户组' data-content='";
 print "当我们添加用户时，需要为他们分配一个默认组 ($DEFAULT_USER_GROUP)。如果这个组不存在，那么会为每个用户账户创建一个新组，这可能不是理想的。";
 print "'>这是什么？</a>";
 print "<label class='pull-right'><input type='checkbox' name='setup_default_group' class='pull-right' checked>创建？&nbsp;</label>";
 print "</li>\n";
 $show_finish_button = FALSE;

}
else {
 print "$li_good 默认用户组 (<strong>$DEFAULT_USER_GROUP</strong>) 存在。</li>";
}


$adminsgroup_filter  = "(&(objectclass=posixGroup)({$LDAP['group_attribute']}={$LDAP['admins_group']}))";
$ldap_adminsgroup_search = ldap_search($ldap_connection, "{$LDAP['base_dn']}", $adminsgroup_filter);
$adminsgroup_result = ldap_get_entries($ldap_connection, $ldap_adminsgroup_search);

if ($adminsgroup_result['count'] != 1) {

 print "$li_fail 定义LDAP账户管理员的组 (<strong>{$LDAP['admins_group']}</strong>) 不存在。 ";
 print "<a href='#' data-toggle='popover' title='LDAP账户管理员组' data-content='";
 print "只有这个组的成员 ({$LDAP['admins_group']}) 才能访问账户管理部分，所以这绝对是您想要创建的。";
 print "'>这是什么？</a>";
 print "<label class='pull-right'><input type='checkbox' name='setup_admins_group' class='pull-right' checked>创建？&nbsp;</label>";
 print "</li>\n";
 $show_finish_button = FALSE;

}
else {
 print "$li_good LDAP账户管理员组 (<strong>{$LDAP['admins_group']}</strong>) 存在。</li>";

 $admins = ldap_get_group_members($ldap_connection,$LDAP['admins_group']);

 if (count($admins) < 1) {
  print "$li_fail LDAP管理员组是空的。您可以在下一节中添加管理员账户。</li>";
  $show_finish_button = FALSE;
 }
}





?>
       </ul>
      </div>
     </div>
<?php

##############

if ($show_finish_button == TRUE) {
?>
     </form>
     <div class='well'>
      <form action="<?php print "{$SERVER_PATH}log_in"; ?>">
       <input type='submit' class="btn btn-success center-block" value='完成'>
      </form>
     </div>
<?php
}
else {
?>
     <div class='well'>
      <input type='submit' class="btn btn-primary center-block" value='下一步 >'>
     </div>
     </form>
<?php
}


?>
 </div>
</div>
<?php

render_footer();

?>