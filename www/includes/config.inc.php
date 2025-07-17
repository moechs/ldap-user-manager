<?php

 $log_prefix="";

 # 用户账户默认值

 $DEFAULT_USER_GROUP = (getenv('DEFAULT_USER_GROUP') ? getenv('DEFAULT_USER_GROUP') : 'everybody');
 $DEFAULT_USER_SHELL = (getenv('DEFAULT_USER_SHELL') ? getenv('DEFAULT_USER_SHELL') : '/bin/bash');
 $ENFORCE_SAFE_SYSTEM_NAMES = ((strcasecmp(getenv('ENFORCE_SAFE_SYSTEM_NAMES'),'FALSE') == 0) ? FALSE : TRUE);
 $USERNAME_FORMAT = (getenv('USERNAME_FORMAT') ? getenv('USERNAME_FORMAT') : '{first_name}-{last_name}');
 $USERNAME_REGEX  = (getenv('USERNAME_REGEX')  ? getenv('USERNAME_REGEX') : '^[a-z][a-zA-Z0-9\._-]{2,31}$');   # 用户名和组名正则

 if (getenv('PASSWORD_HASH')) { $PASSWORD_HASH = strtoupper(getenv('PASSWORD_HASH')); }
 $ACCEPT_WEAK_PASSWORDS = ((strcasecmp(getenv('ACCEPT_WEAK_PASSWORDS'),'TRUE') == 0) ? TRUE : FALSE);

 $min_uid = 2000;
 $min_gid = 2000;

 # 默认属性和对象类

 $LDAP['account_attribute'] = (getenv('LDAP_ACCOUNT_ATTRIBUTE') ? getenv('LDAP_ACCOUNT_ATTRIBUTE') : 'uid');
 $LDAP['account_objectclasses'] = array( 'person', 'inetOrgPerson', 'posixAccount' );
 $LDAP['default_attribute_map'] = array(
    "sn" => array(
        "label" => "姓氏",
        "onkeyup" => "update_username(); update_email(); update_cn(); update_homedir(); check_email_validity(document.getElementById('mail').value);",
        "required" => TRUE,
    ),
    "givenname" => array(
        "label" => "名字",
        "onkeyup" => "update_username(); update_email(); update_cn(); update_homedir(); check_email_validity(document.getElementById('mail').value);",
        "required" => TRUE,
    ),
    "cn" => array(
        "label" => "中文名",
        "onkeyup" => "auto_cn_update = true;",
        "required" => TRUE,
    ),
    "uid" => array(
        "label" => "账号 (用户名)",
        "onkeyup" => "check_entity_name_validity(document.getElementById('uid').value,'uid_div'); update_email(); update_homedir(); check_email_validity(document.getElementById('mail').value);",
    ),
    "mail" => array(
        "label" => "邮箱",
        "onkeyup" => "auto_email_update = true; check_email_validity(document.getElementById('mail').value);",
        "required" => TRUE,
    )
 );

 $LDAP['group_attribute'] = (getenv('LDAP_GROUP_ATTRIBUTE') ? getenv('LDAP_GROUP_ATTRIBUTE') : 'cn');
 $LDAP['group_objectclasses'] = array( 'top', 'posixGroup' ); #groupOfUniqueNames 会自动添加（如果 rfc2307bis 可用）

 $LDAP['default_group_attribute_map'] = array( "description" => array("label" => "描述"));

 $SHOW_POSIX_ATTRIBUTES = ((strcasecmp(getenv('SHOW_POSIX_ATTRIBUTES'),'TRUE') == 0) ? TRUE : FALSE);

 if ($SHOW_POSIX_ATTRIBUTES != TRUE) {
   if ($LDAP['account_attribute'] == "uid") {
     //unset($LDAP['default_attribute_map']['cn']);
   }
   else {
     unset($LDAP['default_attribute_map']['uid']);
   }
 }
 else {
   $LDAP['default_attribute_map']["uidnumber"]  = array("label" => "UID");
   $LDAP['default_attribute_map']["gidnumber"]  = array("label" => "GID");
   $LDAP['default_attribute_map']["homedirectory"]  = array("label" => "主目录", "onkeyup" => "auto_homedir_update = false;");
   $LDAP['default_attribute_map']["loginshell"]  = array("label" => "Shell", "default" => $DEFAULT_USER_SHELL);
   $LDAP['default_group_attribute_map']["gidnumber"] = array("label" => "组 ID");
 }

 ## LDAP 服务器配置

 $LDAP['uri'] = getenv('LDAP_URI');
 $LDAP['base_dn'] = getenv('LDAP_BASE_DN');
 $LDAP['admin_bind_dn'] = getenv('LDAP_ADMIN_BIND_DN');
 $LDAP['admin_bind_pwd'] = getenv('LDAP_ADMIN_BIND_PWD');
 $LDAP['connection_type'] = "plain";
 $LDAP['require_starttls'] = ((strcasecmp(getenv('LDAP_REQUIRE_STARTTLS'),'TRUE') == 0) ? TRUE : FALSE);
 $LDAP['ignore_cert_errors'] = ((strcasecmp(getenv('LDAP_IGNORE_CERT_ERRORS'),'TRUE') == 0) ? TRUE : FALSE);
 $LDAP['rfc2307bis_check_run'] = FALSE;

 # 高级 LDAP 设置

 $LDAP['admins_group'] = getenv('LDAP_ADMINS_GROUP');
 $LDAP['group_ou'] = (getenv('LDAP_GROUP_OU') ? getenv('LDAP_GROUP_OU') : 'groups');
 $LDAP['user_ou'] = (getenv('LDAP_USER_OU') ? getenv('LDAP_USER_OU') : 'people');
 $LDAP['forced_rfc2307bis'] = ((strcasecmp(getenv('FORCE_RFC2307BIS'),'TRUE') == 0) ? TRUE : FALSE);

 if (getenv('LDAP_ACCOUNT_ADDITIONAL_OBJECTCLASSES')) { $account_additional_objectclasses = strtolower(getenv('LDAP_ACCOUNT_ADDITIONAL_OBJECTCLASSES')); }
 if (getenv('LDAP_ACCOUNT_ADDITIONAL_ATTRIBUTES')) { $LDAP['account_additional_attributes'] = getenv('LDAP_ACCOUNT_ADDITIONAL_ATTRIBUTES'); }

 if (getenv('LDAP_GROUP_ADDITIONAL_OBJECTCLASSES')) { $group_additional_objectclasses = getenv('LDAP_GROUP_ADDITIONAL_OBJECTCLASSES'); }
 if (getenv('LDAP_GROUP_ADDITIONAL_ATTRIBUTES')) { $LDAP['group_additional_attributes'] = getenv('LDAP_GROUP_ADDITIONAL_ATTRIBUTES'); }

 if (getenv('LDAP_GROUP_MEMBERSHIP_ATTRIBUTE')) { $LDAP['group_membership_attribute'] = getenv('LDAP_GROUP_MEMBERSHIP_ATTRIBUTE'); }
 if (getenv('LDAP_GROUP_MEMBERSHIP_USES_UID')) {
   if (strtoupper(getenv('LDAP_GROUP_MEMBERSHIP_USES_UID')) == 'TRUE' )   { $LDAP['group_membership_uses_uid']  = TRUE;  }
   if (strtoupper(getenv('LDAP_GROUP_MEMBERSHIP_USES_UID')) == 'FALSE' )  { $LDAP['group_membership_uses_uid']  = FALSE; }
 }

 $LDAP['group_dn'] = "ou={$LDAP['group_ou']},{$LDAP['base_dn']}";
 $LDAP['user_dn'] = "ou={$LDAP['user_ou']},{$LDAP['base_dn']}";

 if (isset($account_additional_objectclasses) and $account_additional_objectclasses != "") {
   $LDAP['account_objectclasses'] = array_merge($LDAP['account_objectclasses'], explode(",", $account_additional_objectclasses));
 }
 if (isset($group_additional_objectclasses) and $group_additional_objectclasses != "") {
   $LDAP['group_objectclasses'] = array_merge($LDAP['group_objectclasses'], explode(",", $group_additional_objectclasses));
 }

 # 界面定制

 $ORGANISATION_NAME = (getenv('ORGANISATION_NAME') ? getenv('ORGANISATION_NAME') : 'LDAP');
 $SITE_NAME = (getenv('SITE_NAME') ? getenv('SITE_NAME') : "$ORGANISATION_NAME 用户管理系统");

 $SITE_LOGIN_LDAP_ATTRIBUTE = (getenv('SITE_LOGIN_LDAP_ATTRIBUTE') ? getenv('SITE_LOGIN_LDAP_ATTRIBUTE') : $LDAP['account_attribute'] );
 $SITE_LOGIN_FIELD_LABEL = (getenv('SITE_LOGIN_FIELD_LABEL') ? getenv('SITE_LOGIN_FIELD_LABEL') : "账号" );

 $SERVER_HOSTNAME = (getenv('SERVER_HOSTNAME') ? getenv('SERVER_HOSTNAME') : "ldapusermanager.org");
 $SERVER_PATH = (getenv('SERVER_PATH') ? getenv('SERVER_PATH') : "/");

 $SESSION_TIMEOUT = (getenv('SESSION_TIMEOUT') ? getenv('SESSION_TIMEOUT') : 10);

 $NO_HTTPS = ((strcasecmp(getenv('NO_HTTPS'),'TRUE') == 0) ? TRUE : FALSE);

 $REMOTE_HTTP_HEADERS_LOGIN = ((strcasecmp(getenv('REMOTE_HTTP_HEADERS_LOGIN'),'TRUE') == 0) ? TRUE : FALSE);

 # 中文菜单名映射
 $MODULE_NAMES_CN = array(
   'users'           => '用户',
   'groups'          => '用户组',
   'log_in'          => '登录',
   'log_out'         => '退出',
   'change_password' => '修改密码',
   'account_manager' => '账户管理',
   'request_account' => '申请账户'
 );

 # 发送邮件

 $SMTP['host'] = getenv('SMTP_HOSTNAME');
 $SMTP['user'] = (getenv('SMTP_USERNAME') ? getenv('SMTP_USERNAME') : NULL);
 $SMTP['pass'] = (getenv('SMTP_PASSWORD') ? getenv('SMTP_PASSWORD') : NULL);
 $SMTP['port'] = (getenv('SMTP_HOST_PORT') ? getenv('SMTP_HOST_PORT') : 25);
 $SMTP['ssl']  = ((strcasecmp(getenv('SMTP_USE_SSL'),'TRUE') == 0) ? TRUE : FALSE);
 $SMTP['tls']  = ((strcasecmp(getenv('SMTP_USE_TLS'),'TRUE') == 0) ? TRUE : FALSE);
 if ($SMTP['tls'] == TRUE) { $SMTP['ssl'] = FALSE; }

 $EMAIL_DOMAIN = (getenv('EMAIL_DOMAIN') ? getenv('EMAIL_DOMAIN') : Null);

 $default_email_from_domain = ($EMAIL_DOMAIN ? $EMAIL_DOMAIN : 'ldapusermanger.org');

 $EMAIL['from_address'] = (getenv('EMAIL_FROM_ADDRESS') ? getenv('EMAIL_FROM_ADDRESS') : "admin@" . $default_email_from_domain );
 $EMAIL['from_name'] = (getenv('EMAIL_FROM_NAME') ? getenv('EMAIL_FROM_NAME') : $SITE_NAME );

 if ($SMTP['host'] != "") { $EMAIL_SENDING_ENABLED = TRUE; } else { $EMAIL_SENDING_ENABLED = FALSE; }

 # 账号申请

 $ACCOUNT_REQUESTS_ENABLED = ((strcasecmp(getenv('ACCOUNT_REQUESTS_ENABLED'),'TRUE') == 0) ? TRUE : FALSE);
 if (($EMAIL_SENDING_ENABLED == FALSE) && ($ACCOUNT_REQUESTS_ENABLED == TRUE)) {
   $ACCOUNT_REQUESTS_ENABLED = FALSE;
   error_log("$log_prefix 配置: ACCOUNT_REQUESTS_ENABLED 设置为 TRUE，但未设置 SMTP_HOSTNAME，无法发送申请邮件，因此已禁用账号申请功能",0);
 }

 $ACCOUNT_REQUESTS_EMAIL = (getenv('ACCOUNT_REQUESTS_EMAIL') ? getenv('ACCOUNT_REQUESTS_EMAIL') : $EMAIL['from_address']);

 # 调试

 $LDAP_DEBUG = ((strcasecmp(getenv('LDAP_DEBUG'),'TRUE') == 0) ? TRUE : FALSE);
 $LDAP_VERBOSE_CONNECTION_LOGS = ((strcasecmp(getenv('LDAP_VERBOSE_CONNECTION_LOGS'),'TRUE') == 0) ? TRUE : FALSE);
 $SESSION_DEBUG = ((strcasecmp(getenv('SESSION_DEBUG'),'TRUE') == 0) ? TRUE : FALSE);
 $SMTP['debug_level'] = getenv('SMTP_LOG_LEVEL');
 if (!is_numeric($SMTP['debug_level']) or $SMTP['debug_level'] >4 or $SMTP['debug_level'] <0) { $SMTP['debug_level'] = 0; }

 # 合理性检查

 $errors = "";

 if (empty($LDAP['uri'])) {
  $errors .= "<div class='alert alert-warning'><p class='text-center'>LDAP_URI 未设置</p></div>\n";
 }
 if (empty($LDAP['base_dn'])) {
  $errors .= "<div class='alert alert-warning'><p class='text-center'>LDAP_BASE_DN 未设置</p></div>\n";
 }
 if (empty($LDAP['admin_bind_dn'])) {
  $errors .= "<div class='alert alert-warning'><p class='text-center'>LDAP_ADMIN_BIND_DN 未设置</p></div>\n";
 }
 if (empty($LDAP['admin_bind_pwd'])) {
  $errors .= "<div class='alert alert-warning'><p class='text-center'>LDAP_ADMIN_BIND_PWD 未设置</p></div>\n";
 }
 if (empty($LDAP['admins_group'])) {
  $errors .= "<div class='alert alert-warning'><p class='text-center'>LDAP_ADMINS_GROUP 未设置</p></div>\n";
 }

 if ($errors != "") {
  render_header("严重错误",false);
  print $errors;
  render_footer();
  exit(1);
 }

?>
