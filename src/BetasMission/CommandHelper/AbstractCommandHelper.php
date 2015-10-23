<?php

namespace BetasMission\CommandHelper;

use BetasMission\Helper\Logger;

/**
 * Class AbstractCommandHelper
 */
class AbstractCommandHelper
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * AbstractCommandHelper constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
}
