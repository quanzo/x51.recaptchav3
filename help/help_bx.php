<?php
use \Bitrix\Main\Config\Option;

$enable_log = Option::get('x51.recaptchav3', 'enable_log', 'N');
if ($enable_log == 'Y') {
	if (defined('LOG_DIR')) {
		echo '<p>Log dir = <b>'.LOG_DIR.'</b>';
	} elseif (defined('LOG_FILENAME')) {
		echo '<p>Log file = <b>'.LOG_FILENAME.'</b>';
	} else {
		echo '<p>For logging, set a constant <b>LOG_FILENAME</b>.';
	}
}
?>

<h2>� ������</h2>
<p>��������� recaptcha v3
<h2>Js �������</h2>
<p>�������� ���������� js �������.
<p><strong>recaptchaSuccess(data)</strong> - ����� ������� ��� ������� �������� ������ �� ���� ����� ajax ������
<p><strong>recaptchaError(request, status)</strong> - ����� ������� ��� ��������� �������� ������ �� ���� ����� ajax ������
<h2>��� ��� �������� �������� �����</h2>
<pre>
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$validUser = true;

if (\Bitrix\Main\Loader::includeModule('x51.recaptchav3')) {
	$validUser = \x51\bitrix\module\recaptchav3\Main::isValidUser() ? true : false;
}

if (($request->isPost() && $validUser) || !$request->isPost()) {

����� ��������� �������� �����

} else {
	echo '�������� ��������� ���������!<br>���������� �������� �����!';
}
</pre>