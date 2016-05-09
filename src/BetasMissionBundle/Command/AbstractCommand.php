<?php

namespace BetasMissionBundle\Command;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Class AbstractCommand.
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    const CONTEXT      = null;
    const ROOT_CONTEXT = 'betasmission';

    /**
     * @var Logger
     */
    protected $logger;

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        
        $lockHandler = new LockHandler(self::ROOT_CONTEXT.self::CONTEXT);
        if (!$lockHandler->lock()) {
            $this->logger->info('Script locked');
        }
    }
}
