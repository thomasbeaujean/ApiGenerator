<?php

namespace tbn\ApiGeneratorBundle\Tests\Services;

use tbn\ApiGeneratorBundle\Services\AuthorizationService;

/**
 *
 * @author Thomas BEAUJEAN
 *
 */
class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function __construct()
    {
        $this->rights = array(
            'create',
            'update',
            'delete',
            'get_one',
            'get_one_deep',
            'get_all',
            'get_all_deep',
        );
    }

    /**
     *
     */
    public function testForbiddenRights()
    {
        $rights = $this->rights;

        $entityRights = array(
            'Item' => array(
                'create' => false,
                'update' => false,
                'delete' => false,
                'get_one' => false,
                'get_one_deep' => false,
                'get_all' => false,
                'get_all_deep' => false,
            ),
        );

        $authorizationService = new AuthorizationService($entityRights);

        foreach ($rights as $right) {
            $allowed = $authorizationService->isEntityAliasAllowedForRequest('Item', $right);

            $this->assertFalse($allowed, 'Should not be allowed for Item and action: '.$right);
        }
    }

    /**
     *
     */
    public function testAllowedRights()
    {
        $rights = $this->rights;

        $entityRights = array(
            'Item' => array(
                'create' => true,
                'update' => true,
                'delete' => true,
                'get_one' => true,
                'get_one_deep' => true,
                'get_all' => true,
                'get_all_deep' => true,
            ),
        );

        $authorizationService = new AuthorizationService($entityRights);

        foreach ($rights as $right) {
            $allowed = $authorizationService->isEntityAliasAllowedForRequest('Item', $right);

            $this->assertTrue($allowed, 'Should be allowed for Item and action: '.$right);
        }
    }
}
