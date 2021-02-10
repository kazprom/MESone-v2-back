<?php

$string = file_get_contents(base_path('/composer.json'));
$json = json_decode($string, true);

return [

    'version' => $json['version'] ?? '2.0.0',

];
