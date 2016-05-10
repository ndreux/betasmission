<?php

namespace BetasMissionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Class AbstractCommand.
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    const CONTEXT      = null;
    const ROOT_CONTEXT = 'betasmission';

    /**
     * @return bool
     */
    protected function isLocked()
    {
        $lockHandler = new LockHandler(static::ROOT_CONTEXT.static::CONTEXT);
        return (!$lockHandler->lock());
    }
}
