<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include_once "web_functions.inc.php";
include_once "ldap_functions.inc.php";
include_once "module_functions.inc.php";
set_page_access("admin");

render_header("$ORGANISATION_NAME 用户管理系统");
render_submenu();

$ldap_connection = open_ldap_connection();

if (isset($_POST['delete_group'])) {

 $this_group = $_POST['delete_group'];
 $this_group = urldecode($this_group);

 $del_group = ldap_delete_group($ldap_connection,$this_group);

 if ($del_group) {
   render_alert_banner("用户组 <strong>$this_group</strong> 已删除！");
 }
 else {
   render_alert_banner("用户组 <strong>$this_group</strong> 删除失败，请检查日志以查看更多信息！","danger",15000);
 }

}

$groups = ldap_get_group_list($ldap_connection);

ldap_close($ldap_connection);

render_js_username_check();

?>
<script type="text/javascript">

 function show_new_group_form() {

  group_form = document.getElementById('group_name');
  group_submit = document.getElementById('add_group');
  group_form.classList.replace('invisible','visible');
  group_submit.classList.replace('invisible','visible');

 }

</script>
<div class="container">

 <div class="form-inline" id="new_group_div">
  <form action="<?php print "{$THIS_MODULE_PATH}"; ?>/show_group.php" method="post">
   <input type="hidden" name="new_group">
   <button type="button" class="btn btn-light">共 <?php print count($groups);?> 个用户组</button>  &nbsp;  <button id="show_new_group" class="form-control btn btn-default" type="button" onclick="show_new_group_form();">创建用户组</button>
   <input type="text" class="form-control invisible" name="group_name" id="group_name" placeholder="用户组名称" onkeyup="check_entity_name_validity(document.getElementById('group_name').value,'new_group_div');"><button id="add_group" class="form-control btn btn-primary btn-sm invisible" type="submit">添加</button>
  </form>
 </div>
 <input class="form-control" id="search_input" type="text" placeholder="搜索..">
 <table class="table table-striped">
  <thead>
   <tr>
     <th>用户组名称</th>
   </tr>
  </thead>
 <tbody id="grouplist">
   <script>
    $(document).ready(function(){
      $("#search_input").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#grouplist tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });
    });
  </script>
<?php
foreach ($groups as $group){
 print " <tr>\n   <td><a href='{$THIS_MODULE_PATH}/show_group.php?group_name=" . urlencode($group) . "'>$group</a></td>\n </tr>\n";
}
?>
  </tbody>
 </table>
</div>
<?php

render_footer();
?>
