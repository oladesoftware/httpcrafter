<?php /** @noinspection PhpUnused */

/** @noinspection PhpConditionAlreadyCheckedInspection */

namespace Router;

use Oladesoftware\Httpcrafter\Router\Router;
use PHPUnit\Framework\TestCase;
use stdClass;

class RouterTest extends TestCase
{
    private Router $router;

    /*
     * @before
     * */
    protected function setUp(): void
    {
        parent::setUp();
        $this->router = Router::getInstance();
    }
}