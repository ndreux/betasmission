<?php

namespace BetasMission\CommandHelper;

/**
 * Class MoveCommandHelper
 */
class MoveCommandHelper extends AbstractCommandHelper
{

    /**
     * @param string $episode
     * @param string $destinationPath
     *
     * @return bool
     */
    public function moveShow($from, $destinationPath)
    {
        if (is_file($from)) {
            $this->logger->log('Moving '.$from.' to '.$destinationPath.'/'.$episode);
            if (copy($from, $destinationPath.'/'.$episode)) {
                $this->logger->log('Remove : '.$from);
                unlink($from);
            }
        } else {
            $this->recurseCopy($from, $destinationPath.'/'.$episode);
            $this->recurseRmdir($from);
        }

        return true;
    }

    /**
     * @param string $showLabel
     *
     * @return string
     */
    public function computeDestinationPath($showLabel)
    {
        if (!is_dir($this->destination.'/'.$showLabel)) {
            mkdir($this->destination.'/'.$showLabel, 0777, true);
        }

        return $this->destination.'/'.$showLabel;
    }

    /**
     * @param string $src
     * @param string $dst
     *
     * @return bool
     */
    private function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurseCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    $this->logger->log('Copy : '.$src.'/'.$file.' to '.$dst.'/'.$file);
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @param string $src
     *
     * @return bool
     */
    protected function recurseRmdir($src)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurseRmdir($src.'/'.$file);
                } else {
                    $this->logger->log('Remove : '.$src.'/'.$file);
                    unlink($src.'/'.$file);
                }
            }
        }
        $this->logger->log('Remove : '.$src);
        rmdir($src);
        closedir($dir);

        return true;
    }
}
