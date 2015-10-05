<?php

namespace tbn\ApiGeneratorBundle\Tests\Services;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Symfony\Component\HttpFoundation\Request;
use tbn\ApiGeneratorBundle\Services\ApiService;

/**
 *
 * @author Thomas BEAUJEAN
 *
 */
class ApiServiceTest extends \tbn\ApiGeneratorBundle\Tests\PHPUnitKernelAware
{
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

        $objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/../Generator/reference_1.yml', $em);
    }


    /**
     *
     */
    public function testCreateTcReference()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');
        $entityAlias = 'tcreference';

        $request = new Request();
        $request->request->replace([['id' => '3', 'name' => 'create']]);
        $entities = $apiService->handleAction($request, $entityAlias);

        $this->assertNotNull($entities);
        $this->assertNotEmpty($entities);
        $this->assertEquals(count($entities), 1);
        $entity = $entities[0];
        $this->assertEquals($entity->getId(), '3');
        $this->assertEquals($entity->getName(), 'create');
    }

    /**
     *
     */
    public function testCreateSeveralTcReference()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');
        $entityAlias = 'tcreference';

        $request = new Request();
        $request->request->replace([['id' => '3', 'name' => 'create3'], ['id' => '4', 'name' => 'create4']]);
        $entities = $apiService->handleAction($request, $entityAlias);

        $this->assertNotNull($entities);
        $this->assertNotEmpty($entities);

        $this->assertEquals(count($entities), 2);

        $entity = $entities[0];
        $this->assertEquals($entity->getId(), '3');
        $this->assertEquals($entity->getName(), 'create3');
        $entity = $entities[1];
        $this->assertEquals($entity->getId(), '4');
        $this->assertEquals($entity->getName(), 'create4');
    }

    /**
     *
     */
    public function testUpdateTcReference()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');
        $entityAlias = 'tcreference';

        $request = new Request();
        $request->request->set('data', ['id' => '1', 'name' => 'updated']);
        $entities = $apiService->handleAction($request, $entityAlias);

        $this->assertNotNull($entities);
        $this->assertNotEmpty($entities);
        $entity = $entities[0];
        $this->assertEquals($entity->getId(), '1');
        $this->assertEquals($entity->getName(), 'updated');
    }

    /**
     *
     */
    public function testNoData()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');
        $entityAlias = 'tcreference';


        $request = new Request();
        $exceptionRaised = false;

        try {
            $apiService->handleAction($request, $entityAlias);
        } catch (\Exception $ex) {
            $exceptionRaised = true;
        }
        $this->assertTrue($exceptionRaised, 'An exception should be raised when no data is provided');
    }
}
