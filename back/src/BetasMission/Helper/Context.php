<?php

namespace BetasMission\Helper;

/**
 * Class Context
 */
class Context
{
    const CONTEXT_REMOVE            = '-remove';
    const CONTEXT_MOVE              = '-move';
    const CONTEXT_DOWNLOAD_SUBTITLE = '-subtitle';

    /**
     * @return string
     */
    public static function getAvailableContexts()
    {
        return [self::CONTEXT_DOWNLOAD_SUBTITLE, self::CONTEXT_REMOVE, self::CONTEXT_MOVE];
    }
}
