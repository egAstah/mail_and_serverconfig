<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\Bitrix\Main\Page\Asset::getInstance()->addCss(
    '/bitrix/css/main/system.auth/flat/style.css'
);

if ($arResult['AUTHORIZED']) {
    echo Loc::getMessage('MAIN_AUTH_CHD_SUCCESS');
    return;
}

$fields = $arResult['FIELDS'];
?>

<section class="loginpage">
    <div class="loginpage__block">
        <p class="loginpage__logo">
            Цифровой ямщик
        </p>
        <form class="loginpage__form" name="bform" method="post" target="_top" action="<?= POST_FORM_ACTION_URI; ?>">
            <? if ($arResult['ERRORS']): ?>
                <div class="alert alert-danger">
                    <? foreach ($arResult['ERRORS'] as $error) {
                        echo $error;
                    }
                    ?>
                </div>
            <? elseif ($arResult['SUCCESS']): ?>
                <div class="alert alert-success">
                    <?= $arResult['SUCCESS']; ?>
                </div>
            <? endif; ?>
            <p class="loginpage__form-title">
                <?= Loc::getMessage('MAIN_AUTH_CHD_HEADER'); ?>
            </p>
            <p class="loginpage__form-new">
                <?= $arResult['GROUP_POLICY']['PASSWORD_REQUIREMENTS']; ?>
            </p>
            <input class="loginpage__form-field" type="text" name="<?= $fields['login']; ?>" maxlength="255"
                   value="<?= \htmlspecialcharsbx($arResult['LAST_LOGIN']); ?>"/>
            <input class="loginpage__form-field" disabled type="hidden" name="<?= $fields['checkword']; ?>"
                   maxlength="255"
                   value="<?= \htmlspecialcharsbx($arResult[$fields['checkword']]); ?>"/>
            <? if ($arResult['SECURE_AUTH']): ?>
                <div class="bx-authform-psw-protected" id="bx_auth_secure" style="display:none">
                    <div class="bx-authform-psw-protected-desc"><span></span>
                        <?= Loc::getMessage('MAIN_AUTH_CHD_SECURE_NOTE'); ?>
                    </div>
                </div>
                <script type="text/javascript">
                    document.getElementById('bx_auth_secure').style.display = '';
                </script>
            <? endif; ?>
            <input class="loginpage__form-field" type="password" placeholder="Новый пароль"
                   name="<?= $fields['password']; ?>"
                   value="<?= \htmlspecialcharsbx($arResult[$fields['password']]); ?>" maxlength="255"
                   autocomplete="off"/>
            <? if ($arResult['SECURE_AUTH']): ?>
                <div class="bx-authform-psw-protected" id="bx_auth_secure2" style="display:none">
                    <div class="bx-authform-psw-protected-desc"><span></span>
                        <?= Loc::getMessage('MAIN_AUTH_CHD_SECURE_NOTE'); ?>
                    </div>
                </div>
                <script type="text/javascript">
                    document.getElementById('bx_auth_secure2').style.display = '';
                </script>
            <? endif; ?>
            <input class="loginpage__form-field" type="password" placeholder="Подтвердите пароль"
                   name="<?= $fields['confirm_password']; ?>"
                   value="<?= \htmlspecialcharsbx($arResult[$fields['confirm_password']]); ?>" maxlength="255"
                   autocomplete="off"/>
            <? if ($arResult['CAPTCHA_CODE']): ?>
                <input type="hidden" name="captcha_sid" value="<?= \htmlspecialcharsbx($arResult['CAPTCHA_CODE']); ?>"/>
                <div class="bx-authform-formgroup-container dbg_captha">
                    <div class="bx-authform-label-container">
                        <?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_CAPTCHA'); ?>
                    </div>
                    <div class="bx-captcha"><img
                                src="/bitrix/tools/captcha.php?captcha_sid=<?= \htmlspecialcharsbx($arResult['CAPTCHA_CODE']); ?>"
                                width="180" height="40" alt="CAPTCHA"/></div>
                    <input class="loginpage__form-field" type="text" name="captcha_word" maxlength="50" value=""
                           autocomplete="off"/>
                </div>
            <? endif; ?>
            <div class="loginpage__form-submitWrapper">
                <input class="loginpage__form-submit" type="submit" class="btn btn-primary"
                       name="<?= $fields['action']; ?>"
                       value="<?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_SUBMIT'); ?>"/>
            </div>
            <? if ($arResult['AUTH_AUTH_URL'] || $arResult['AUTH_REGISTER_URL']): ?>
                <hr class="bxe-light">
                <? if ($arResult['AUTH_AUTH_URL']): ?>
                    <p class="loginpage__form-new">
                        <a href="<?= $arResult['AUTH_AUTH_URL']; ?>" rel="nofollow">
                            <?= Loc::getMessage('MAIN_AUTH_CHD_URL_AUTH_URL'); ?>
                        </a>
                    </p>
                <? endif; ?>
                <? if ($arResult['AUTH_REGISTER_URL']): ?>
                    <p class="loginpage__form-new">
                        <a href="<?= $arResult['AUTH_REGISTER_URL']; ?>" rel="nofollow">
                            <?= Loc::getMessage('MAIN_AUTH_CHD_URL_REGISTER_URL'); ?>
                        </a>
                    </p>
                <? endif; ?>
            <? endif; ?>
        </form>
    </div>
</section>

<script type="text/javascript">
    document.bform.<?= $fields['login'];?>.focus();
</script>
