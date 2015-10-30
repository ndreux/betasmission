<?php

namespace BetasMission\CommandHelper;

use BetasMission\Business\FileManagementBusiness;
use BetasMission\Helper\Logger;

/**
 * Class RemoveCommandHelper
 */
class RemoveCommandHelper
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var FileManagementBusiness
     */
    private $fileManagementBusiness;

    /**
     * RemoveCommandHelper constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger                 = $logger;
        $this->fileManagementBusiness = new FileManagementBusiness($this->logger);

    }

    /**
     * @param string $toBeRemoved
     */
    public function remove($toBeRemoved)
    {
        $this->fileManagementBusiness->remove($toBeRemoved);
    }
}
