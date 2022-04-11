<?php
namespace Ritc\Library\Exceptions;

use Psr\SimpleCache\CacheException as PCE;
use Ritc\Library\Abstracts\CustomExceptionAbstract;
use Ritc\Library\Helper\ExceptionHelper;

class CacheException extends CustomExceptionAbstract implements PCE
{
    public function getCodeNumber(string $value = ''): int
    {
        return ExceptionHelper::getCodeNumberCache($value);
    }

}