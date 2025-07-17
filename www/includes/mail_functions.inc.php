<?php

require_once "/opt/PHPMailer/src/PHPMailer.php";
require_once "/opt/PHPMailer/src/SMTP.php";
require_once "/opt/PHPMailer/src/Exception.php";

# 默认邮件内容

$new_account_mail_subject = (getenv('NEW_ACCOUNT_EMAIL_SUBJECT') ? getenv('NEW_ACCOUNT_EMAIL_SUBJECT') : "您的 {organisation} 账户已创建");
$new_account_mail_body = getenv('NEW_ACCOUNT_EMAIL_BODY') ?: <<<EoNA
您已成功在 {organisation} 创建账户。您的登录信息如下：
<p>
账号：{login}<br>
密码：{password}
<p>
请尽快登录 <a href="{change_password_url}">{change_password_url}</a> 并修改密码。
EoNA;

$reset_password_mail_subject = (getenv('RESET_PASSWORD_EMAIL_SUBJECT') ? getenv('RESET_PASSWORD_EMAIL_SUBJECT') : "您的 {organisation} 密码已重置");
$reset_password_mail_body = getenv('RESET_PASSWORD_EMAIL_BODY') ?: <<<EoRP
您在 {organisation} 的密码已被重置，新密码为：{password}
<p>
请尽快登录 <a href="{change_password_url}">{change_password_url}</a> 并修改该密码。
EoRP;


function parse_mail_text($template,$password,$login,$first_name,$last_name) {

  global $ORGANISATION_NAME, $SITE_PROTOCOL, $SERVER_HOSTNAME, $SERVER_PATH;

  $template = str_replace("{password}", $password, $template);
  $template = str_replace("{login}", $login, $template);
  $template = str_replace("{first_name}", $first_name, $template);
  $template = str_replace("{last_name}", $last_name, $template);

  $template = str_replace("{organisation}", $ORGANISATION_NAME, $template);
  $template = str_replace("{site_url}", "{$SITE_PROTOCOL}{$SERVER_HOSTNAME}{$SERVER_PATH}", $template);
  $template = str_replace("{change_password_url}", "{$SITE_PROTOCOL}{$SERVER_HOSTNAME}{$SERVER_PATH}change_password", $template);

  return $template;

}

function send_email($recipient_email,$recipient_name,$subject,$body) {

  global $EMAIL, $SMTP, $log_prefix;

  $mail = new PHPMailer\PHPMailer\PHPMailer();
  $mail->CharSet = 'UTF-8';
  $mail->isSMTP();

  $mail->SMTPDebug = $SMTP['debug_level'];
  $mail->Debugoutput = function($message, $level) { error_log("$log_prefix SMTP（级别 $level）：$message"); };

  $mail->Host = $SMTP['host'];
  $mail->Port = $SMTP['port'];

  if (isset($SMTP['user'])) {
    $mail->SMTPAuth = true;
    $mail->Username = $SMTP['user'];
    $mail->Password = $SMTP['pass'];
  }

  if ($SMTP['tls'] == TRUE) { $mail->SMTPSecure = 'tls'; }
  if ($SMTP['ssl'] == TRUE) { $mail->SMTPSecure = 'ssl'; }

  $mail->SMTPAutoTLS = false;
  $mail->setFrom($EMAIL['from_address'], $EMAIL['from_name']);
  $mail->addAddress($recipient_email, $recipient_name);
  $mail->Subject = $subject;
  $mail->Body = $body;
  $mail->IsHTML(true);

  if (!$mail->Send())  {
    error_log("$log_prefix SMTP：无法发送邮件：" . $mail->ErrorInfo);
    return FALSE;
  }
  else {
    error_log("$log_prefix SMTP：已向 $recipient_email ($recipient_name) 发送邮件");
    return TRUE;
  }

}

?>
