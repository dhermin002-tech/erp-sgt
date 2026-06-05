<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirige_vers_login_pour_invite(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
