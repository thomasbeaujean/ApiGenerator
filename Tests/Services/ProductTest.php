<?php

namespace tbn\ApiGeneratorBundle\Tests\Services;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @author Thomas BEAUJEAN
 *
 */
class ProductTest extends \tbn\ApiGeneratorBundle\Tests\PHPUnitKernelAware
{
    protected $entityAlias = 'product';

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
        $this->assertEquals(count($entities), 4, 'The retrieve all should give 4 entries');
    }

    /**
     *
     */
    public function testCreateOne()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');

        $data = [['name' => 'New car']];

        $request = new Request();
        $request->request->replace($data);
        $entities = $apiService->handleAction($request, $this->entityAlias);

        $this->assertNotNull($entities);
        $this->assertEquals(count($entities), 1, 'The create entity should return only 1 entry');

        $entity = $entities[0];
        $this->assertNotNull($entity->getId());
        $this->assertEquals($entity->getName(), 'New car');
    }

    /**
     *
     */
    public function testCreateandUpdate()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');

        $data = [['id' => 1, 'name' => 'Updated car'], ['name' => 'New car']];

        $request = new Request();
        $request->request->replace($data);
        $entities = $apiService->handleAction($request, $this->entityAlias);

        $this->assertNotNull($entities);
        $this->assertEquals(count($entities), 2, 'The create and udpate entity should return only 2 entries');
    }

    /**
     *
     */
    public function testUpdateOne()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');

        $data = [['id' => 1, 'name' => 'Updated car', 'createdAt' => '2015-12-25 12:53:00', 'reference' => null]];

        $request = new Request();
        $request->request->replace($data);
        $udpatedEntities = $apiService->handleAction($request, $this->entityAlias);

        $this->assertNotNull($udpatedEntities);
        $this->assertEquals(count($udpatedEntities), 1, 'The updated entity should return only 1 entry');
    }

    /**
     *
     */
    public function testCreateOneWithCategory()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');

        $data = [['name' => 'New car', 'category' => ['id' => 1]]];

        $request = new Request();
        $request->request->replace($data);
        $entities = $apiService->handleAction($request, $this->entityAlias);

        $this->assertNotNull($entities);
        $this->assertEquals(count($entities), 1, 'The create entity should return only 1 entry');

        $entity = $entities[0];
        $this->assertNotNull($entity->getId());
        $this->assertEquals($entity->getName(), 'New car');
        $this->assertNotNull($entity->getCategory());
        $this->assertEquals($entity->getCategory()->getId(), 1);
    }

    /**
     *
     */
    public function testCreateOneWithTags()
    {
        $apiService = $this->getService('tbn.api_generator.service.api_service');

        $tagsData = [
            [
                'name' => 'First Tag',
            ],
            [
                'name' => 'Second Tag',
            ],
        ];

        $data = [['name' => 'New car', 'tags' => $tagsData]];

        unset($tagsData);

        $request = new Request();
        $request->request->replace($data);
        $entities = $apiService->handleAction($request, $this->entityAlias);

        $this->assertNotNull($entities);
        $this->assertEquals(count($entities), 1, 'The create entity should return only 1 entry');

        $entity = $entities[0];
        $this->assertNotNull($entity->getId());

        $tags = $entity->getTags();

        $this->assertNotNull($tags);
        $this->assertEquals(count($tags), 2, 'The tags were not created');
    }
}
