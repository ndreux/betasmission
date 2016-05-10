<?php

namespace BetasMissionBundle\CommandHelper;
use Symfony\Bridge\Monolog\Logger;

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
