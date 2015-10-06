<?php

namespace tbn\ApiGeneratorBundle\Tests\Services;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;

/**
 *
 * @author Thomas BEAUJEAN
 *
 */
class CategoryTest extends \tbn\ApiGeneratorBundle\Tests\PHPUnitKernelAware
{
    protected $entityAlias = 'category';

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $em = $this->getEm();

        $loader = new Loader();
        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());

        \Nelmio\Alice\Fixtures::load(__DIR__.'/../Generator/reference_1.yml', $em);
    }

    /**
     *
     */
    public function testGetAll()
    {
        $apiService = $this->getService('tbn.api_generator.service.retrieve_service');

        $entities = $apiService->retrieveAllEntities($this->entityAlias);

        $this->assertNotNull($entities);
        $this->assertEquals(count($entities), 3, 'The retrieve all should give 3 entries');
    }

    /**
     *
     */
    public function testGetOne()
    {
        $apiService = $this->getService('tbn.api_generator.service.retrieve_service');

        $data = ['id' => 1];
        $entity = $apiService->retrieveEntity($this->entityAlias, $data);

        $this->assertNotNull($entity);
    }

    /**
     *
     */
    public function testGetOneException()
    {
        $apiService = $this->getService('tbn.api_generator.service.retrieve_service');

        $data = ['id' => 999999];

        $exceptionRaised = false;

        try {
            $apiService->retrieveEntity($this->entityAlias, $data);
        } catch (\Exception $ex) {
            $exceptionRaised = true;
        }

        $this->assertTrue($exceptionRaised, 'An exception should be raised');
    }
}
