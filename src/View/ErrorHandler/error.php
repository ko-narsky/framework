<?php
/**
 * @var int $statusCode
 * @var string $message
 * @var string $debugTag
 * @var bool $debug
 * @var string $trace
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Error</title>
</head>
<body>
<div style="background-color: #fcbeca; padding: 20px;">
    <div style="font-size: 16px;">
        <p style="font-weight: bold">Ошибка <?= $statusCode ?></p>
        <p><?= $message ?></p>
        <p>x-debug-tag: <?= $debugTag ?></p>
    </div>
</div>

<?php
if ($debug === true) : ?>
    <h2>Трейс вызова</h2>
    <div style="background-color: #fcbeca; padding: 20px;">
            <pre>
                <?= $trace ?>
            </pre>
    </div>
<?php
endif; ?>
</body>
</html>
