<?php

return [
    'env'     => $_ENV['APP_ENV']  ?? 'production',
    'url'     => $_ENV['APP_URL']  ?? 'http://localhost',
    'session' => $_ENV['SESSION_NAME'] ?? 'jaanahai_session',
];
