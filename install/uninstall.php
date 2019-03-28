<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(!check_bitrix_sessid()) return;

echo CAdminMessage::ShowNote(Loc::getMessage('X51.MOD_UNINST_OK'));

?><form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=Loc::getMessage('X51.MOD_BACK')?>">
<form>