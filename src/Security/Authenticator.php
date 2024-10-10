<?php

namespace Oladesoftware\Httpcrafter\Security;

interface Authenticator
{
    /**
     * Authenticate based on the provided credentials.
     *
     * @param array $credentials The credentials needed for authentication.
     * @return bool True if authentication is successful, otherwise false.
     */
    public function authenticate(array $credentials): bool;
}