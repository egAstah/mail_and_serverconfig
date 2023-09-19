<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--    <script src="https://kit.fontawesome.com/b675a8d36a.js" crossorigin="anonymous"></script>-->
    <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/css/style.css">
    <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/css/all.css">
    <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/css/brands.css">
    <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/css/fontawesome.css">
    <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/css/regular.css">
    <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/css/solid.css">
    <?$APPLICATION->ShowHead();?>
    <title>Цифровой ямщик</title>
</head>
<?$APPLICATION->ShowPanel()?>
<body>
<?if($USER->IsAuthorized()):?>
    <header class="header">
        <div class="header__row">
            <p class="header__logo">
                <a href="/?page=inbox">Цифровой ямщик</a>
            </p>

            <a href="/?logout=yes&<?=bitrix_sessid_get()?>" class="header__logout">
                Выйти
            </a>

        </div>
    </header>
<?endif;?>