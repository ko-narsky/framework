<?php
/**
 * @var $debug
 * @var $debugTag
 * @var $e
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style><?php include "css/error.css" ?></style>
</head>
<body>
<div class="error">
    <div class="error__main-message">
        <p>Запрос не может быть обработан</p>
        <p>Произошла внутренняя ошибка сервера</p>
    </div>
    <div class="error__additional-info">
        <p>Обратитесь к администратору системы</p>
        <p>support@efko.ru</p>
        <p>В запросе укажите идентификатор сеанса</p>
        <p>Идентификатор сеанса: <?= $this->debugTag ?></p>
    </div>
</div>

<?php if($this->debug) :?>
    <h2>Трейс вызова</h2>
    <div class="error">
        <pre>
            <?= $e ?>
        </pre>
    </div>
<?php endif;?>
</body>
</html>