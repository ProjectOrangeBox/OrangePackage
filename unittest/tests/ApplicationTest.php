<?php

declare(strict_types=1);

use orange\framework\Application;

/**
 * @runTestsInSeparateProcesses
 */
final class ApplicationTest extends \UnitTestHelper
{
    public function testMakeReturnsSingletonInstance(): void
    {
        $app1 = Application::make();
        $app2 = Application::make();

        $this->assertSame($app1, $app2);
    }

    public function testLoadEnvironmentLoadsIniValues(): void
    {
        $envFile = WORKINGDIR . '/env/test_env.ini';
        file_put_contents($envFile, "FOO=bar\n");

        $app = Application::make([$envFile]);

        $this->assertEquals('bar', $app->env('FOO'));

        unlink($envFile);
    }

}
