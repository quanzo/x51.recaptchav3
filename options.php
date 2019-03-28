<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

if (!defined('BX_ROOT') || !$USER->IsAdmin()) {
	return;
}

Loc::loadMessages(__FILE__);
Loader::includeModule('x51.recaptchav3');

// получим права доступа текущего пользователя на модуль
$RIGHTS = $APPLICATION->GetGroupRight('x51.recaptchav3');
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($RIGHTS == "D") {
	$APPLICATION->AuthForm(Loc::getMessage('X51.RC3.ACCESS_DENIED'));
}

$request = Application::getInstance()->getContext()->getRequest();
$uriString = $request->getRequestUri();
$uri = new Uri($uriString);
$redirect = $uri->getUri();

// --- Группы пользователей
	$arUGroupsEx = Array();
	$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
	while($arUGroups = $dbUGroups -> Fetch())
	{
		$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
	}
// --- почтовые события
	$arEventTypes = [];
	$dbET = CEventType::GetList([], ['SORT'=>'ASC', 'EVENT_NAME'=>'ASC']);
	while($arET = $dbET -> Fetch()) {
		$arEventTypes[$arET['EVENT_NAME']] = '['.$arET['EVENT_NAME'].'] '.$arET['NAME'];
	}



$aTabs = array(
	array(
		"DIV" => "x51_tab_options_1",
		"TAB" => Loc::getMessage('X51.RC3.OPTIONS'),
		"ICON" => "help",
		"TITLE" => Loc::getMessage('X51.RC3.OPTIONS.TITLE'),
	),
	array(
		"DIV" => "x51_tab_help_1",
		"TAB" => Loc::getMessage('X51.RC3.INFO'),
		"ICON" => "help",
		"TITLE" => Loc::getMessage('X51.RC3.INFO.TITLE'),
	),
);
$arAllOptions = array(
	'main' => array(
		Loc::getMessage("X51.RC3.RECAPTCHA_CFG_TITLE"),
		array("public_key", Loc::getMessage("X51.RC3.PUBLIC_KEY"), '', array('textarea', 5, 50)),
		array("secret_key", Loc::getMessage("X51.RC3.SECRET_KEY"), '', array('textarea', 5, 50)),
		Loc::getMessage("X51.RC3.RESTRICT_SCORE_TITLE"),
		array("score", Loc::getMessage("X51.RC3.SCORE"), '0.6', array('text', 3)),
		array(
			'note' => Loc::getMessage("X51.RC3.SCORE_NOTE")
		),
		Loc::getMessage("X51.RC3.RESTRICT_TITLE"),
		array("only_url", Loc::getMessage("X51.RC3.ONLY_URL"), '', array('textarea', 5, 50)),
		array(
			'note' => Loc::getMessage("X51.RC3.URL_NOTE")
		),
		array("exclude_user_group", Loc::getMessage('X51.RC3.EXCLUDE_USER_GROUP'), '', array('multiselectbox', $arUGroupsEx)),
		array("not_load_if_status_detect", Loc::getMessage('X51.RC3.NOT_LOAD_IF_DETECT'), 'N', array('checkbox', '', '')),
		Loc::getMessage("X51.RC3.BAN_USER_TITLE"),
		array("ban_add", Loc::getMessage('X51.RC3.BAN_ADD'), 'N', array('checkbox', '', '')),
		array("ban_reg", Loc::getMessage('X51.RC3.BAN_REG'), 'N', array('checkbox', '', '')),
		Loc::getMessage("X51.RC3.BAN_EVENTS_TITLE"),
		array("ban_event_types", Loc::getMessage('X51.RC3.BAN_EVENT_TYPES'), '', array('multiselectbox', $arEventTypes)),
		Loc::getMessage("X51.RC3.LOG_TITLE"),
		array("enable_log", Loc::getMessage('X51.RC3.ENABLE_LOG'), 'N', array('checkbox', '', '')),
		
	),
);

// сохранение парамтеров
if ($REQUEST_METHOD == 'POST' && (isset($_REQUEST['save']) || isset($_REQUEST['apply'])) && $RIGHTS == 'W' && check_bitrix_sessid()) {
	__AdmSettingsSaveOptions('x51.recaptchav3', $arAllOptions['main']);
}
?><form method="post" action="<?=$redirect?>" name="x51_oau"><?
	echo bitrix_sessid_post();
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
	$tabControl->BeginNextTab();
		__AdmSettingsDrawList('x51.recaptchav3', $arAllOptions["main"]);
	$tabControl->BeginNextTab();
		include __DIR__.'/help/help_bx.php';
	$tabControl->Buttons(array());
$tabControl->End();
?></form>