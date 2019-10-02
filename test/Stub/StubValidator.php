<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 02/10/2019
 * Time: 09:12
 */

namespace Test\Stub;

use Repository\ValidatorInterface;

class StubValidator implements ValidatorInterface
{
    public function __invoke(array $data)
    {
    }
}