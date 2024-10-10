<?php
/**
 * @var $statusCode
 * @var $message
 * @var $debugTag
 * @var $debug
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
    <style><?php include "css/error.css" ?></style>
    <title>Error</title>
</head>
<body>
<div class="error">
    <div class="error__main-message">
        <p>Запрос не может быть обработан</p>
        <p>Ошибка: <?= $statusCode ?></p>
        <p><?= $message ?></p>
    </div>
    <div class="error__additional-info">
        <p>Идентификатор сеанса: <?= $this->debugTag ?></p>
    </div>
</div>

<?php if ($this->debug) : ?>
    <h2>Трейс вызова</h2>
    <div class="error">
            <pre>
                <?= $e ?>
            </pre>
    </div>
<?php endif; ?>
</body>
</html>
