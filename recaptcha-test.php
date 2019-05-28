<?
use Bitrix\Main\Loader;
use \x51\bitrix\module\recaptchav3\Main;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проверка авторизации");
?>
<h1>Recaptcha</h1>
<?php
	if (Loader::includeModule('x51.recaptchav3')) {
		if (Main::isValidUser()) {
			echo '<p>Доверенный пользователь';
		}
		echo '<p>Оценка: '.Main::score();	
	} else {
		echo '<p>Модуль x51.recaptchav3 не загружен';
	}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>