<?php

$local = is_file(__DIR__ . '/params-local.php') ? require __DIR__ . '/params-local.php' : [];

return array_merge([
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
], $local);
