<?php

declare(strict_types=1);

namespace Auth0\WordPress\Filters;

final class Authentication extends Base
{
    protected array $registry = [
        // https://developer.wordpress.org/reference/hooks/query_vars/
        // 'query_vars' => [
        //     'method' => 'onQueryVars',
        //     'arguments' => 1
        // ],
    ];

    // public function onQueryVars($vars): array {
    //     var_dump("onQueryVars() HIT", $vars);
    //     exit;

    //     $vars[] = 'state';
    //     $vars[] = 'code';
    //     $vars[] = 'error';
    //     return $vars;
    // }
}
