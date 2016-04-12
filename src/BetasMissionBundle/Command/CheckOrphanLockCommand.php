<?php

namespace BetasMissionBundle\Command;

use BetasMissionBundle\CommandHelper\CheckOrphanLockCommandHelper;
use BetasMissionBundle\Helper\Locker;
use DateTime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckOrphanLockCommand
 */
class CheckOrphanLockCommand extends AbstractCommand
{

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('betasmission:check-orphan-lock')
            ->setDescription('Check if scripts are not locked');
    }

    /**
     * @return bool|int
     */
    public function execute(InputInterface $input, OutputInterface $outputInterface)
    {
        $this->preExecute();

        $commandHelper = new CheckOrphanLockCommandHelper($this->logger, $this->getContainer()->get('betasmission.mailer'));
        $tempFiles     = scandir(Locker::LOCK_PATH);

        foreach ($tempFiles as $tempFile) {
            if (!$commandHelper->isBetasmissionLockFile($tempFile)) {
                continue;
            }

            $lockFileTimeStamp = $commandHelper->getTimeStampLock(Locker::LOCK_PATH.$tempFile);

            if ($commandHelper->isLockFileOutOfDate($lockFileTimeStamp)) {
                $commandHelper->sendAlert($tempFile, (new DateTime('now'))->setTimestamp($lockFileTimeStamp));
            }
        }

        $this->postExecute();
    }
}
