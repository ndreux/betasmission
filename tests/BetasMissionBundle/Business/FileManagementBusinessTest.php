<?php

namespace BetasMissionBundle\Tests\Business;

use BetasMissionBundle\Business\FileManagementBusiness;
use PHPUnit_Framework_TestCase;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class FileManagementBusinessTest
 */
class FileManagementBusinessTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @return void
     */
    public function setUp()
    {
        
        $this->logger = new Logger('');
        mkdir('/tmp/betasmission', 0777, true);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        rmdir('/tmp/betasmission');
    }

    /**
     * @return void
     */
    public function testCopyFile()
    {
        $business = new FileManagementBusiness($this->logger);

        touch('/tmp/betasmission/toto.mp4');

        $this->assertFileNotExists('/tmp/toto.mp4');
        $this->assertFileExists('/tmp/betasmission/toto.mp4');

        $business->copy('/tmp/betasmission/toto.mp4', '/tmp/toto.mp4');

        $this->assertFileExists('/tmp/betasmission/toto.mp4');
        $this->assertFileExists('/tmp/toto.mp4');

        unlink('/tmp/betasmission/toto.mp4');
        unlink('/tmp/toto.mp4');
    }

    /**
     * @return void
     */
    public function testCopyDir()
    {
        $business = new FileManagementBusiness($this->logger);

        mkdir('/tmp/betasmission/test');
        touch('/tmp/betasmission/test/toto.mp4');

        $this->assertFileNotExists('/tmp/toto.mp4');
        $this->assertFileExists('/tmp/betasmission/test/toto.mp4');

        $business->copy('/tmp/betasmission/test', '/tmp/test');

        $this->assertFileExists('/tmp/betasmission/test/toto.mp4');
        $this->assertFileExists('/tmp/test/toto.mp4');

        unlink('/tmp/betasmission/test/toto.mp4');
        rmdir('/tmp/betasmission/test/');
        unlink('/tmp/test/toto.mp4');
        rmdir('/tmp/test');
    }

    /**
     * @return void
     */
    public function testCopyDirRecursive()
    {
        $business = new FileManagementBusiness($this->logger);

        mkdir('/tmp/betasmission/test');
        mkdir('/tmp/betasmission/test/test2');
        touch('/tmp/betasmission/test/test2/toto.mp4');

        $this->assertFileNotExists('/tmp/toto.mp4');
        $this->assertFileExists('/tmp/betasmission/test/test2/toto.mp4');

        $business->copy('/tmp/betasmission/test', '/tmp/test');

        $this->assertFileExists('/tmp/betasmission/test/test2/toto.mp4');
        $this->assertFileExists('/tmp/test/test2/toto.mp4');

        unlink('/tmp/betasmission/test/test2/toto.mp4');
        rmdir('/tmp/betasmission/test/test2');
        rmdir('/tmp/betasmission/test/');
        unlink('/tmp/test/test2/toto.mp4');
        rmdir('/tmp/test/test2');
        rmdir('/tmp/test');
    }

    /**
     * @return void
     */
    public function testRemove()
    {
        $business = new FileManagementBusiness($this->logger);
        mkdir('/tmp/betasmission/test');
        touch('/tmp/betasmission/test/titi.mp4');
        mkdir('/tmp/betasmission/test/test2');
        touch('/tmp/betasmission/test/test2/toto.mp4');

        $this->assertFileExists('/tmp/betasmission/test/test2/toto.mp4');
        $this->assertFileExists('/tmp/betasmission/test/titi.mp4');

        $business->remove('/tmp/betasmission/test');

        $this->assertFileNotExists('/tmp/betasmission/test/test2/toto.mp4');
        $this->assertFileNotExists('/tmp/betasmission/test/test2');
        $this->assertFileNotExists('/tmp/betasmission/test/titi.mp4');
        $this->assertFileNotExists('/tmp/betasmission/test');
    }
}
