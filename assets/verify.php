<?php
use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \x51\bitrix\module\recaptchav3\Main;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
include_once (__DIR__.'/../classes/recaptcha/src/autoload.php');

/*
RECAPTCHA_V3_USER_VALID
RECAPTCHA_V3_USER_DATA
 */

if (Loader::includeModule('x51.recaptchav3')) {
    $request = Application::getInstance()->getContext()->getRequest();

    if ($request->isPost()) {
        $token = $request->getPost('token');
        if ($token) {
            if (!isset($_SESSION['RECAPTCHA_V3_USER_VALID'])) {
                $resVerify = Main::verifyToken($token);
                if (is_array($resVerify)) { // токен опознан
                    unset($_SESSION['RECAPTCHA_V3_ERRORS']);
                    $_SESSION['RECAPTCHA_V3_USER_VALID'] = Main::verifyScore($resVerify);
                    $_SESSION['RECAPTCHA_V3_USER_DATA'] = $resVerify;
					Main::log('Recaptcha score = '.strval($resVerify['score']));
                } else {
                    $response = Main::$lastResponse;
					$arErrors = $response->getErrorCodes();
                    $_SESSION['RECAPTCHA_V3_ERRORS'] = $arErrors;
					$strErrors = '';
					foreach ($arErrors as $errCode) {
						if ($strErrors) {
							$strErrors .= ', ';
						}
						$strErrors .= strval($errCode);
					}
					Main::log('Recaptcha errors '.$strErrors);
                }
            }
        }
    }
}

if (isset($_SESSION['RECAPTCHA_V3_USER_VALID'])) {
    echo $_SESSION['RECAPTCHA_V3_USER_VALID'] ? '1' : 0;
}
