<?php namespace MoesifLaravel42\MoesifLaravel\Tests;

use Orchestra\Testbench\TestCase;

/**
*
*/
abstract class MoesifLaravelTestCase extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->app['path.base'] = __DIR__ . '/../../../../src';

        $this->modifyConfiguration($this->app);
        $this->prepareRoutes();
    }

    /**
     * @return array
     */
    protected function getPackageProviders()
    {
        return ['MoesifLaravel42\MoesifLaravel\ServiceProvider'];
    }

    /**
     * @return array
     */
    protected function getPackageAliases()
    {
        return [];
    }

    /**
    * Define environment setup.
    *
    * @param  \Illuminate\Foundation\Application  $app
    * @return void
    */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
          'driver'   => 'sqlite',
          'database' => ':memory:',
          'prefix'   => '',
        ]);
    }

    /**
     * Perform user specific configuration.
     */
    protected function modifyConfiguration($app)
    {
    }

    /**
     * Prepare routes.
     */
    protected function prepareRoutes()
    {
        $this->app['router']->get('test', [function () {
            return json_encode(['message' => 'testing']);
        }]);
    }
}
