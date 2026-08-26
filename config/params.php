<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    $params = array_merge(
        require __DIR__ . '/params.php',
        file_exists(__DIR__ . '/params-local.php') ? require __DIR__ . '/params-local.php' : []
    )
];
