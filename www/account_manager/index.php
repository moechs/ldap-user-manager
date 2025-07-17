<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include_once "web_functions.inc.php";
include_once "ldap_functions.inc.php";
include_once "module_functions.inc.php";
set_page_access("admin");

render_header("$ORGANISATION_NAME 用户管理系统");
render_submenu();

$ldap_connection = open_ldap_connection();

if (isset($_POST['delete_user'])) {

  $this_user = $_POST['delete_user'];
  $this_user = urldecode($this_user);

  $del_user = ldap_delete_account($ldap_connection,$this_user);

  if ($del_user) {
    render_alert_banner("用户 <strong>$this_user</strong> 已删除！");
  }
  else {
    render_alert_banner("用户 <strong>$this_user</strong> 删除失败，请检查日志以查看更多信息！.","danger",15000);
  }


}

$people = ldap_get_user_list($ldap_connection);

?>
<div class="container">
 <form action="<?php print $THIS_MODULE_PATH; ?>/new_user.php" method="post">
  <button type="button" class="btn btn-light">共 <?php print count($people);?> 个用户</button>  &nbsp; <button id="add_group" class="btn btn-default" type="submit">创建新用户</button>
 </form> 
 <input class="form-control" id="search_input" type="text" placeholder="搜索..">
 <table class="table table-striped">
  <thead>
   <tr>
     <th>账号</th>
     <th>中文名</th>
     <th>姓氏</th>
     <th>名字</th>
     <th>邮箱</th>
     <th>所属用户组</th>
   </tr>
  </thead>
 <tbody id="userlist">
   <script>
    $(document).ready(function(){
      $("#search_input").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#userlist tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });
    });
  </script>
<?php
foreach ($people as $account_identifier => $attribs){

  $group_membership = ldap_user_group_membership($ldap_connection,$account_identifier);
  if (isset($people[$account_identifier]['mail'])) { $this_mail = $people[$account_identifier]['mail']; } else { $this_mail = ""; }
  print " <tr>\n   <td><a href='{$THIS_MODULE_PATH}/show_user.php?account_identifier=" . urlencode($account_identifier) . "'>$account_identifier</a></td>\n";
  print "   <td>" . $people[$account_identifier]['cn'] . "</td>\n";
  print "   <td>" . $people[$account_identifier]['sn'] . "</td>\n";
  print "   <td>" . $people[$account_identifier]['givenname'] . "</td>\n";
  print "   <td>$this_mail</td>\n"; 
  print "   <td>" . implode(", ", $group_membership) . "</td>\n";
  print " </tr>\n";

}
?>
  </tbody>
 </table>
</div>
<?php

ldap_close($ldap_connection);
render_footer();
?>
