<?php

namespace BetasMission\CommandHelper;

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
     * MoveCommandHelper constructor.
     *
     * @param string $from
     * @param string $destination
     * @param string $defaultDestination
     */
    public function __construct(Logger $logger, $from, $destination, $defaultDestination)
    {
        parent::__construct($logger);

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

        $this->copy($from, $destinationPath.'/'.$episode);
        $this->remove($from);

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

    /**
     * @param string $src
     * @param string $dst
     */
    private function copy($src, $dst)
    {
        if (is_file($src)) {
            copy($src, $dst);
        } else {
            $dir = opendir($src);
            mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->copy($src.'/'.$file, $dst.'/'.$file);
                    } else {
                        $this->logger->log('Copy : '.$src.'/'.$file.' to '.$dst.'/'.$file);
                        copy($src.'/'.$file, $dst.'/'.$file);
                    }
                }
            }
            closedir($dir);
        }
    }

    /**
     * @param string $src
     */
    protected function remove($src)
    {
        if (is_file($src)) {
            unlink($src);
        } else {
            $dir = opendir($src);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->remove($src.'/'.$file);
                    } else {
                        $this->logger->log('Remove : '.$src.'/'.$file);
                        unlink($src.'/'.$file);
                    }
                }
            }
            $this->logger->log('Remove : '.$src);
            rmdir($src);
            closedir($dir);
        }
    }
}
