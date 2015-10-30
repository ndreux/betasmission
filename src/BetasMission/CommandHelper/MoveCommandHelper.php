<?php

namespace BetasMission\CommandHelper;

use BetasMission\Business\FileManagementBusiness;
use BetasMission\Helper\Logger;

/**
 * Class MoveCommandHelper
 */
class MoveCommandHelper extends AbstractCommandHelper
{

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $defaultDestination;

    /**
     * @var FileManagementBusiness
     */
    private $fileManagementBusiness;

    /**
     * MoveCommandHelper constructor.
     *
     * @param string $from
     * @param string $destination
     * @param string $defaultDestination
     */
    public function __construct(Logger $logger, $from, $destination, $defaultDestination)
    {
        parent::__construct($logger);
        $this->fileManagementBusiness = new FileManagementBusiness($logger);

        $this->from               = $from;
        $this->destination        = $destination;
        $this->defaultDestination = $defaultDestination;
    }

    /**
     * Move the given episode to its destination
     *
     * @param string $episode         Episode to move
     * @param string $destinationPath Destination path
     *
     * @return bool
     */
    public function moveShow($episode, $destinationPath)
    {
        $from = $this->from.'/'.$episode;

        $this->fileManagementBusiness->copy($from, $destinationPath.'/'.$episode);
        $this->fileManagementBusiness->remove($from);

        return true;
    }

    /**
     * Return the destination path of the given TV Show
     *
     * @param string $showLabel
     *
     * @return string
     */
    public function getTVShowDestinationPath($showLabel)
    {
        if (!is_dir($this->destination.'/'.$showLabel)) {
            mkdir($this->destination.'/'.$showLabel, 0777, true);
        }

        return $this->destination.'/'.$showLabel;
    }
}
