<?php

namespace BetasMission\Command;

/**
 * Interface CommandInterface
 */
interface CommandInterface
{
    public function execute();
    public function preExecute();
    public function postExecute();
}