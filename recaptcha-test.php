<?
use Bitrix\Main\Loader;
use \x51\bitrix\module\recaptchav3\Main;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("�������� �����������");
?>
<h1>Recaptcha</h1>
<?php
	if (Loader::includeModule('x51.recaptchav3')) {
		if (Main::isValidUser()) {
			echo '<p>���������� ������������';
		}
		echo '<p>������: '.Main::score();	
	} else {
		echo '<p>������ x51.recaptchav3 �� ��������';
	}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>