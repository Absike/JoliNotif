<?php

/**
 * This file is part of the JoliNotif project.
 *
 * (c) Loïck Piera <pyrech@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoliNotif\tests;

use JoliNotif\Driver\Driver;
use JoliNotif\Notifier;
use JoliNotif\NotifierFactory;
use JoliNotif\tests\fixtures\ConfigurableDriver;
use JoliNotif\Util\OsHelper;

class NotifierFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param Notifier $notifier
     *
     * @return Driver[]
     */
    private function getNotifierDrivers(Notifier $notifier)
    {
        $reflection = new \ReflectionClass(get_class($notifier));
        $property = $reflection->getProperty('drivers');
        $property->setAccessible(true);

        return $property->getValue($notifier);
    }

    private function assertDriversClasses($expectedDriversClass, $drivers)
    {
        $expectedCount = count($expectedDriversClass);
        $this->assertSame($expectedCount, count($drivers));

        for ($i=0; $i<$expectedCount; $i++) {
            $this->assertInstanceOf($expectedDriversClass[$i], $drivers[$i]);
        }
    }

    public function testCreateUseDefaultDrivers()
    {
        $notifier = NotifierFactory::create();

        $this->assertInstanceOf('JoliNotif\\Notifier', $notifier);

        $drivers = $this->getNotifierDrivers($notifier);

        if (OsHelper::isUnix()) {
            $expectedDriversClass = [
                'JoliNotif\\Driver\\GrowlNotifyDriver',
                'JoliNotif\\Driver\\TerminalNotifierDriver',
                'JoliNotif\\Driver\\AppleScriptDriver',
                'JoliNotif\\Driver\\NotifySendDriver',
            ];
        } else {
            $expectedDriversClass = [
                'JoliNotif\\Driver\\ToasterDriver',
                'JoliNotif\\Driver\\NotifuDriver',
            ];
        }

        $this->assertDriversClasses($expectedDriversClass, $drivers);
    }

    public function testCreateUseGivenDrivers()
    {
        $notifier = NotifierFactory::create([
            new ConfigurableDriver(true),
            new ConfigurableDriver(true),
        ]);

        $this->assertInstanceOf('JoliNotif\\Notifier', $notifier);

        $drivers = $this->getNotifierDrivers($notifier);

        $expectedDriversClass = [
            'JoliNotif\\tests\\fixtures\\ConfigurableDriver',
            'JoliNotif\\tests\\fixtures\\ConfigurableDriver',
        ];

        $this->assertDriversClasses($expectedDriversClass, $drivers);
    }
}
