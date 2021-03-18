<?php

return [
    'login' => env('INSTALLER_LOGIN', 'root'),
    'password' => env('INSTALLER_PASSWORD', 'root'),
    'jenkins' => [
        'isAuth' => env('JENKINS_IS_AUTH', false),
        'url' => env('JENKINS_URL', ''),
        'user'=> env('JENKINS_USER', ''),
        'token' => env('JENKINS_TOKEN', ''),
    ],
];
