<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

if (!defined('BX_ROOT') || !$GLOBALS['USER']->IsAdmin()) {
	return;
}

class x51_recaptchav3 extends \CModule {
    public $MODULE_ID = 'x51.recaptchav3';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION='Поддержка recaptcha v3';
	public $MODULE_CSS;
	public $MODULE_GROUP_RIGHTS = "Y";

	public function __construct() {
		$arVer=include __DIR__.'/../version.php';
		$this->MODULE_VERSION=$arVer['version'];
		$this->MODULE_VERSION_DATE=$arVer['date'];

		$this->MODULE_NAME=Loc::getMessage('X51.RC3.MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('X51.RC3.DESCRIPTION');
		
		//die('construct');
	}

	// INSTALL
	public function InstallDB() {
		RegisterModule($this->MODULE_ID);
		COption::SetOptionString("x51.recaptchav3", "public_key", '');
		COption::SetOptionString("x51.recaptchav3", "secret_key", '');
		COption::SetOptionString("x51.recaptchav3", "only_url", '');
		COption::SetOptionString("x51.recaptchav3", "exclude_user_group", '');
		COption::SetOptionString("x51.recaptchav3", "not_load_if_status_detect", 'N');
		COption::SetOptionString("x51.recaptchav3", "ban_add", 'N');
		COption::SetOptionString("x51.recaptchav3", "ban_reg", 'N');
		return true;
	}

	public function InstallFiles() {
		$res=true;
		return $res;
	}

	public function InstallPublic() {
		return true;
	}

	public function InstallEvents() {
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('main', 'OnProlog', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnProlog');
		$eventManager->registerEventHandler('main', 'OnBeforeUserAdd', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnBeforeUserAdd');
		$eventManager->registerEventHandler('main', 'OnBeforeUserRegister', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnBeforeUserRegister');
		$eventManager->registerEventHandler('main', 'OnBeforeEventSend', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnBeforeEventSend');
		$eventManager->registerEventHandler('main', 'OnBeforeEventAdd', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnBeforeEventAdd');
		return true;
	}
	
	public function DoInstall() {
		//die('DoInstall');
		global $APPLICATION, $step;
		$keyGoodFiles = $this->InstallFiles();
		$keyGoodDB = $this->InstallDB();
		$keyGoodEvents = $this->InstallEvents();
		$keyGoodPublic = $this->InstallPublic();
		$APPLICATION->IncludeAdminFile('Установка', __DIR__."/install.php");
    }

	// UNINSTALL
	public function UnInstallDB($arParams = Array()) {
		COption::RemoveOption("x51.recaptchav3");
		UnRegisterModule($this->MODULE_ID);
		return true;
	}

	public function UnInstallFiles() {
		$res=true;
		return $res;
	}

	public function UnInstallPublic() {
		return true;
	}

	public function UnInstallEvents() {
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('main', 'OnProlog', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnProlog');
		$eventManager->unRegisterEventHandler('main', 'OnBeforeUserAdd', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnBeforeUserAdd');
		$eventManager->unRegisterEventHandler('main', 'OnBeforeUserRegister', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnBeforeUserRegister');
		$eventManager->unRegisterEventHandler('main', 'OnBeforeEventSend', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnBeforeEventSend');
		$eventManager->unRegisterEventHandler('main', 'OnBeforeEventAdd', $this->MODULE_ID, '\x51\bitrix\module\recaptchav3\Main', 'handlerOnBeforeEventAdd');
		return true;
	}

	public function DoUninstall() {
		global $APPLICATION, $step;
		$keyGoodFiles = $this->UnInstallFiles();
		$keyGoodDB = $this->UnInstallDB();
		$keyGoodEvents = $this->UnInstallEvents();
		$keyGoodPublic = $this->UnInstallPublic();
		$APPLICATION->IncludeAdminFile('Удаление модуля', __DIR__."/uninstall.php");
    }
}
