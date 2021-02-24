<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;

class DomainController extends Controller
{
    /**
     * Проверка доступности домена
     *
     * @param $_
     * @param array $args
     * @return bool
     */
    public function ping($_, array $args): bool
    {
        $protocol = $args['ad_protocol'] ?? 0;
        $server_uri = boolval($protocol) ? 'ldaps://' : 'ldap://';
        $server_uri .= $args['ad_server'];
        $port = $args['ad_server_port'] ?? 389;
        $server_uri .= ":$port";
        $ldap_connection = ldap_connect($server_uri);
        if (!$ldap_connection) return false;
        if (!@ldap_bind($ldap_connection)) return false;
        ldap_unbind($ldap_connection);
        return true;
    }
}
