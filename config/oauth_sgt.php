<?php

return [
    'issuer' => env('SGT_OAUTH_ISSUER', 'https://sgt.kaytechnologie.online'),
    'resource' => env('SGT_OAUTH_RESOURCE', 'https://mcp.kaytechnologie.online/mcp'),
    'redirect_uris' => [
        'https://claude.ai/api/mcp/auth_callback',
        'https://claude.com/api/mcp/auth_callback',
    ],
];
