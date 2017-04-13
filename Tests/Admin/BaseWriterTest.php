<?php
/*
 * This file is part of the Blast Project package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Blast\CoreBundle\Test\Admin\Writer;

use Blast\CoreBundle\Admin\BaseWriter;

class BaseWriterTest extends \PHPUnit_Framework_TestCase
{

    protected $filename;

    public function setUp()
    {
        parent::setUp();
        $this->filename = 'foobar.txt';
        if (is_file($this->filename)) {
            unlink($this->filename);
        }
    }

    public function tearDown()
    {
        if (is_file($this->filename)) {
            unlink($this->filename);
        }
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The file foobar.txt already exist
     */
    public function testConfigure()
    {
        touch($this->filename);
        $stub = $this->getMockForAbstractClass(BaseWriter::class);
        $stub->configure($this->filename);
    }
}
