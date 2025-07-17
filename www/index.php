<?php

set_include_path( __DIR__ . "/includes/");
include_once "web_functions.inc.php";

render_header();

 if (isset($_GET['logged_in'])) {
 ?>
 <div class="alert alert-success">
 <p class="text-center">您已登录，请点击导航菜单继续！</p>
 </div>
 <?php
 }

render_footer();
?>
