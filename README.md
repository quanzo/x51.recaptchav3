x51.recaptchav3 - модуль для битрикс - поддержка Google Recaptcha v.3
=====================================================================

 

Модуль не заменяет стандартную капчу.

 

Установка
---------

Скопировать в папку `/bitrix/modules/x51.recaptchav3/`

 

Настройки модуля
----------------

-   Открытый и секретный ключи для recaptcha

-   Оценка пользователя, при которой он считается приемлемым

-   Запускать модуль только для определенных URL

-   Исключить определенные группы пользователей. Для них оценка всегда 1.0

-   Не запускать reСaptcha, если оценка пользователя уже определена

-   Запрет на добавление пользователя с низкой оценкой

-   Запрет на регистрацию пользователя с низкой оценкой

-   Запрет почтовых событий для пользователя с низкой оценкой

 

Как работает
------------

-   К странице сайта подключается recaptcha обычным способом

-   Запрашивается токен пользователя

-   Отправляется запрос на сервер, к модулю. Затем модуль, по токену, получает
    оценку пользователя  от recaptcha.

-   Возвращает 1 - если пользователь, и 0 - если бот. Для оценки используется
    настройка модуля.

 

Как использовать
----------------

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$validUser = true;

if (\Bitrix\Main\Loader::includeModule('x51.recaptchav3')) {
    $validUser = \x51\bitrix\module\recaptchav3\Main::isValidUser() ? true : false;
}

if (($request->isPost() && $validUser) || !$request->isPost()) {

//здесь компонент отправки формы

} else {
    echo 'Отправка сообщения запрещена!
Обнаружены признаки спама!';
}
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 

Возможно определить js функции.

**recaptchaSuccess(data)** - будет вызвана при удачной передаче токена на сайт
через ajax запрос. **data** будет содержать 1 или 0

**recaptchaError(request, status)** - будет вызвана при неудачной передаче
токена на сайт через ajax запрос

 

 

 
