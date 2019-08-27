<?php
namespace x51\bitrix\module\recaptchav3;

use \Bitrix\Main\Application;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

class Main
{
    public static $lastResponse = null;

    public static function handlerOnProlog()
    {
        $notLoadIfStatusDetect = Option::get('x51.recaptchav3', 'not_load_if_status_detect', 'N');
		$result = new \Bitrix\Main\Entity\EventResult(\Bitrix\Main\EventResult::SUCCESS);
        $request = Application::getInstance()->getContext()->getRequest();
        if ($request->isAdminSection()) {
            return $result;
        }

        $notLoadIfStatusDetect = Option::get('x51.recaptchav3', 'not_load_if_status_detect', 'N');
        
        if ($notLoadIfStatusDetect == 'N' || !isset($_SESSION['RECAPTCHA_V3_USER_VALID'])) {
            $public_key = Option::get('x51.recaptchav3', 'public_key', '');
            $secret_key = Option::get('x51.recaptchav3', 'secret_key', '');
            if ($secret_key && $public_key) {
				$passed = true;
				
                /*$only_url = Option::get('x51.recaptchav3', 'only_url', '');
                if (!empty($only_url)) {
                    $currPage = $request->getRequestedPage();
                    $arValidUrls = explode("\n", strtr($only_url, "\r", ''));
                    $passedUrl = false;
                    foreach ($arValidUrls as $pattern) {
                        if (fnmatch($pattern, $currPage)) {
                            $passedUrl = true;
                            break;
                        }
                    }
                    $passed = $passedUrl;
                }*/
				
				if (defined('DISABLE_RECAPTCHA')) {
					$passed = false;
				}
				
				if ($passed) {
					$passed = static::checkStartInPath();
				}
                
                if ($passed) { // user group check
                    // если группа пользователя в списке исключения - капчу не загружаем
                    $passed = !static::allowedUserGroup();
                }

                if ($passed) {
                    $assetPage = Asset::getInstance();
                    $assetPage->addString('<script type="text/javascript">RECAPTCHA_PUBLIC_KEY = "' . $public_key . '";</script>');
                    $assetPage->addJs('https://www.google.com/recaptcha/api.js?render=' . $public_key);
                    $assetPage->addJs('/bitrix/modules/x51.recaptchav3/assets/js/script.js');
                }
            }
        }
        return $result;
        //$errorMessage = Option::get('x51.orderauthuser', 'error_message', 'Err!');
        //return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError($errorMessage, 'code'), 'sale');
    } // end

    public static function handlerOnBeforeUserAdd(&$arParams)
    {
        $banAdd = Option::get('x51.recaptchav3', 'ban_add', 'N');
        if ($banAdd == 'Y' && !static::isValidUser()) {
            global $APPLICATION;
            $APPLICATION->throwException(Loc::getMessage("X51.RC3.BAN_ADD"));
			static::log('Ban user add');
            return false;
        }
        return true;
    } // end handlerOnBeforeUserAdd

    public static function handlerOnBeforeUserRegister(&$arParams)
    {
        $banRegister = Option::get('x51.recaptchav3', 'ban_reg', 'N');
        if ($banRegister == 'Y' && !static::isValidUser()) {
            global $APPLICATION;
            $APPLICATION->throwException(Loc::getMessage("X51.RC3.BAN_REG"));
			static::log('Ban user register');
            return false;
        }
        return true;
    } // end handlerOnBeforeUserAdd

    public function handlerOnBeforeEventSend(&$arFields, $arTemplate, $context)
    {
        if (static::banEventType($arTemplate['EVENT_NAME'])) {
            $arFields = []; // это запрещает отправку
			static::log('Ban event send: '.$arTemplate['EVENT_NAME']);
        }
    } // handlerOnBeforeEventSend

    // 11.0.16
    public function handlerOnBeforeEventAdd($event, $lid, $arFields, $message_id, $files, $languageId)
    {
        if (static::banEventType($event)) {
			static::log('Ban event add: '.$event);
            return false;
        }
        return true;
    } // handlerOnBeforeEventAdd

    public static function verifyToken($token, $action = 'user_check')
    {
        $secret_key = Option::get('x51.recaptchav3', 'secret_key', '');
        if ($secret_key) {
            $recaptcha = new \ReCaptcha\ReCaptcha($secret_key);
            $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
                ->setExpectedAction($action)
            /*->setScoreThreshold(0.5)*/;
            $response = $recaptcha->verify($token, $_SERVER['REMOTE_ADDR']);
            static::$lastResponse = $response;
            if ($response->isSuccess()) {
                return $response->toArray();
            }
        }
        return false;
    } // end verifyToken

    /**
     * Проверяет числовую оценку на соответствие заданному критерию
     * Возвращает true если проверка пройдена.
     *
     * @param float|array $score
     * @return boolean
     */
    public static function verifyScore($score)
    {
        if (defined('DISABLE_RECAPTCHA')) {
			return true;
		}
		$scoreBadUser = floatval(Option::get('x51.recaptchav3', 'score', 0.6));
        if (is_array($score) && isset($score['score'])) {
            return floatval($score['score']) >= $scoreBadUser;
        } else {
            return floatval($score) >= $scoreBadUser;
        }
    } // end verifyToken

    public static function score()
    {
        if (isset($_SESSION['RECAPTCHA_V3_USER_DATA'])) {
            return floatval($_SESSION['RECAPTCHA_V3_USER_DATA']['score']);
        }
		if (defined('DISABLE_RECAPTCHA')) {
			return 1;
		}
		if (static::allowedUserGroup()) {
            return 1;
        }		
        return 0;
    }
	
	/**
	* Записывает сообщение в лог
	*/
	public function log($message) {
		$enable_log = Option::get('x51.recaptchav3', 'enable_log', 'N');
		if ($enable_log == 'Y') {
			if (class_exists('\x51\classes\Base')) {
				$env = \x51\classes\Base::getEnvironment();
				$env['LOGGER']->log('x51.recaptchav3', $message);
			} else {
				AddMessage2Log($message, 'x51.recaptchav3');
			}
		}
	}

    /**
     * Сбрасывает состояние. Как будто бы проверок не было.
     *
     * @return void
     */
    public static function reset()
    {
        unset(
            $_SESSION['RECAPTCHA_V3_USER_DATA'],
            $_SESSION['RECAPTCHA_V3_USER_VALID'],
            $_SESSION['RECAPTCHA_V3_ERRORS']
        );
        static::$lastResponse = null;
    }

    /**
     * Проверяет пользователя
     * true - если пользователь не робот
     * false - если пользователь подозрителен
     * null - проверка не проводилась или была не удачной
     *
     * @return boolean
     */
    public static function isValidUser()
    {
        if (defined('DISABLE_RECAPTCHA')) {
			return true;
		}
		if (isset($_SESSION['RECAPTCHA_V3_USER_VALID'])) {
            return $_SESSION['RECAPTCHA_V3_USER_VALID'];
        }
		if (static::allowedUserGroup()) {
            return true;
        }
		
        return null;
    }

    protected function allowedUserGroup()
    {
        $exclude_user_group = Option::get('x51.recaptchav3', 'exclude_user_group', '');
        $allowed = false;
        if ($exclude_user_group) {
            $arExcludeUserGroup = explode(',', $exclude_user_group);
            array_walk($arExcludeUserGroup, function (&$val) {
                $val = intval($val);
            });
            global $USER;
            if ($USER) {
                $arCurrUserGroups = $USER->GetUserGroupArray();
                if (array_intersect($arExcludeUserGroup, $arCurrUserGroups)) { // в группах пользователя есть те, которые в списках исключения
                    $allowed = true;
                }
            }
        }/* else {
            $allowed = true;
        }*/
        return $allowed;
    }
	
	protected function checkStartInPath()
    {
        $checked = false;
        $only_url = Option::get('x51.recaptchav3', 'only_url', '');
        if (!empty($only_url)) {
            $request = Application::getInstance()->getContext()->getRequest();
			$currPage = $request->getRequestedPage();
            $arValidUrls = explode("\n", strtr($only_url, "\r", ''));			
            foreach ($arValidUrls as $pattern) {                
				if (fnmatch(str_replace(array("\r", "\n", "\t"), '', $pattern), $currPage)) {
                    $checked = true;
                    break;
                }
            }
        } else {
			$checked = true;
		}
		//file_put_contents(__DIR__.'/4.txt', print_r($currPage, true).print_r($arValidUrls, true).print_r($checked, true));
        return $checked;
    }

    protected function banEventType($eventType) {
        $ban_event_types = Option::get('x51.recaptchav3', 'ban_event_types', '');
        if ($ban_event_types) {
            $arBanEventTypes = explode(',', $ban_event_types);
            return in_array($eventType, $arBanEventTypes);
        }
        return false;
    } // end checkEventType

} // end class