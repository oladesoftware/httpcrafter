<?php

namespace Oladesoftware\Httpcrafter\Middleware;

interface Authorization
{
    /**
     * Check if the user has access to the requested resource.
     *
     * @return bool True if authorized, otherwise false.
     */
    public function authorize(): bool;
}