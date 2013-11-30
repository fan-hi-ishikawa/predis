<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Configuration;

use stdClass;
use PredisTestCase;

/**
 * @todo We should test the inner work performed by this class
 *       using mock objects, but it is quite hard to to that.
 */
class OptionsTest extends PredisTestCase
{
    /**
     * @group disconnected
     */
    public function testConstructorWithoutArguments()
    {
        $options = new Options();

        $this->assertInstanceOf('Predis\Connection\ConnectionFactoryInterface', $options->connections);
        $this->assertInstanceOf('Predis\Profile\ProfileInterface', $options->profile);
        $this->assertInstanceOf('Predis\Connection\ClusterConnectionInterface', $options->cluster);
        $this->assertInstanceOf('Predis\Connection\ReplicationConnectionInterface', $options->replication);
        $this->assertTrue($options->exceptions);
        $this->assertNull($options->prefix);
    }

    /**
     * @group disconnected
     */
    public function testConstructorWithArrayArgument()
    {
        $options = new Options(array(
            'exceptions'  => false,
            'profile'     => '2.0',
            'prefix'      => 'prefix:',
            'connections' => $this->getMock('Predis\Connection\ConnectionFactoryInterface'),
            'cluster'     => $this->getMock('Predis\Connection\ClusterConnectionInterface'),
            'replication' => $this->getMock('Predis\Connection\ReplicationConnectionInterface'),
        ));

        $this->assertInternalType('bool', $options->exceptions);
        $this->assertInstanceOf('Predis\Profile\ProfileInterface', $options->profile);
        $this->assertInstanceOf('Predis\Command\Processor\CommandProcessorInterface', $options->prefix);
        $this->assertInstanceOf('Predis\Connection\ConnectionFactoryInterface', $options->connections);
        $this->assertInstanceOf('Predis\Connection\ClusterConnectionInterface', $options->cluster);
        $this->assertInstanceOf('Predis\Connection\ReplicationConnectionInterface', $options->replication);
    }

    /**
     * @group disconnected
     */
    public function testSupportsCustomOptions()
    {
        $options = new Options(array(
            'custom' => 'foobar',
        ));

        $this->assertSame('foobar', $options->custom);
    }

    /**
     * @group disconnected
     */
    public function testUndefinedOptionsReturnNull()
    {
        $options = new Options();

        $this->assertFalse($options->defined('unknown'));
        $this->assertFalse(isset($options->unknown));
        $this->assertNull($options->unknown);
    }

    /**
     * @group disconnected
     */
    public function testCanCheckOptionsIfDefinedByUser()
    {
        $options = new Options(array(
            'prefix' => 'prefix:',
            'custom' => 'foobar',
            'void'   => null,
        ));

        $this->assertTrue($options->defined('prefix'));
        $this->assertTrue($options->defined('custom'));
        $this->assertTrue($options->defined('void'));
        $this->assertFalse($options->defined('profile'));
    }

    /**
     * @group disconnected
     */
    public function testIsSetReplicatesPHPBehavior()
    {
        $options = new Options(array(
            'prefix' => 'prefix:',
            'custom' => 'foobar',
            'void'   => null,
        ));

        $this->assertTrue(isset($options->prefix));
        $this->assertTrue(isset($options->custom));
        $this->assertFalse(isset($options->void));
        $this->assertFalse(isset($options->profile));
    }

    /**
     * @group disconnected
     */
    public function testReturnsDefaultValueOfSpecifiedOption()
    {
        $options = new Options();

        $this->assertInstanceOf('Predis\Profile\ProfileInterface', $options->getDefault('profile'));
    }

    /**
     * @group disconnected
     */
    public function testReturnsNullAsDefaultValueForUndefinedOption()
    {
        $options = new Options();

        $this->assertNull($options->getDefault('unknown'));
    }

    /**
     * @group disconnected
     */
    public function testLazilyInitializesOptionValueUsingObjectWithInvokeMagicMethod()
    {
        $profile = $this->getMock('Predis\Profile\ProfileInterface');

        // NOTE: closure values are covered by this test since they define __invoke().
        $callable = $this->getMock('stdClass', array('__invoke'));
        $callable->expects($this->once())
                 ->method('__invoke')
                 ->with($this->isInstanceOf('Predis\Configuration\OptionsInterface'), 'profile')
                 ->will($this->returnValue($profile));

        $options = new Options(array(
            'profile' => $callable,
        ));

        $this->assertSame($profile, $options->profile);
        $this->assertSame($profile, $options->profile);
    }

    /**
     * @group disconnected
     */
    public function testLazilyInitializesCustomOptionValueUsingObjectWithInvokeMagicMethod()
    {
        $custom = new stdClass;

        // NOTE: closure values are covered by this test since they define __invoke().
        $callable = $this->getMock('stdClass', array('__invoke'));
        $callable->expects($this->once())
                 ->method('__invoke')
                 ->with($this->isInstanceOf('Predis\Configuration\OptionsInterface'), 'custom')
                 ->will($this->returnValue($custom));

        $options = new Options(array(
            'custom' => $callable,
        ));

        $this->assertSame($custom, $options->custom);
        $this->assertSame($custom, $options->custom);
    }
}
