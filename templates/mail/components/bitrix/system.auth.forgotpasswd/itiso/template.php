<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?><?

ShowMessage($arParams["~AUTH_RESULT"]);

?>
<section class="loginpage">
    <div class="loginpage__block">
        <p class="loginpage__logo">
            Цифровой ямщик
        </p>
        <form class="loginpage__form" name="bform" method="post" target="_top" action="<?= $arResult["AUTH_URL"] ?>">
            <?
            if ($arResult["BACKURL"] <> '') {
                ?>
                <input type="hidden" name="backurl" value="<?= $arResult["BACKURL"] ?>"/>
                <?
            }
            ?>
            <input type="hidden" name="AUTH_FORM" value="Y">
            <input type="hidden" name="TYPE" value="SEND_PWD">
            <p class="loginpage__form-title">Введите логин или email</p>
            <input class="loginpage__form-field"
                   type="text" name="USER_LOGIN"
                   value="<?= $arResult["USER_LOGIN"] ?>"
                   placeholder="<?= GetMessage("sys_forgot_pass_login1") ?>"
            />
            <input type="hidden" name="USER_EMAIL"/>
            <p class="loginpage__form-txt"><? echo GetMessage("sys_forgot_pass_note_email") ?></p>
            <? if ($arResult["USE_CAPTCHA"]): ?>
                <div>
                    <div>
                        <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
                        <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>" width="180"
                             height="40" alt="CAPTCHA"/>
                    </div>
                    <div><? echo GetMessage("system_auth_captcha") ?></div>
                    <div><input class="loginpage__form-field" type="text" name="captcha_word" maxlength="50" value=""/>
                    </div>
                </div>
            <? endif ?>
            <p class="loginpage__form-new"><a
                        href="/auth/"><?= GetMessage("AUTH_AUTH") ?></a></p>
            <div class="loginpage__form-submitWrapper">
                <input class="loginpage__form-submit" type="submit" name="send_account_info"
                       value="<?= GetMessage("AUTH_SEND") ?>"/>
            </div>
        </form>


    </div>
</section>
<script type="text/javascript">
    document.bform.onsubmit = function () {
        document.bform.USER_EMAIL.value = document.bform.USER_LOGIN.value;
    };
    document.bform.USER_LOGIN.focus();
</script>
