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
    public function __construct()
    {
        $this->rights = array(
            'create',
            'update',
            'delete',
            'get_one',
            'get_one_deep',
            'get_all',
            'get_all_deep');
    }

    /*
     *
     */
    public function testGeneralRights()
    {
        $rights = $this->rights;

        $allRights = array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'get_one' => true,
            'get_one_deep' => true,
            'get_all' => true,
            'get_all_deep' => true
        );

        $specifiedEntities = array();
        $entityRights = array();

        foreach ($rights as $right) {
            $allRights[$right] = true;

            $authorizationService = new AuthorizationService($allRights, $entityRights, $specifiedEntities);

            $exceptionRaised = false;

            try {
                $authorizationService->checkItemNamespaceAction('ANY', $right);
            } catch (\Exception $ex) {
                $exceptionRaised = true;
            }

            $this->assertFalse($exceptionRaised, 'Exception raised for ANY and action: '.$right);
        }

        foreach ($rights as $right) {
            $allRights[$right] = false;

            $authorizationService = new AuthorizationService($allRights, $entityRights, $specifiedEntities);

            $exceptionRaised = false;

            try {
                $authorizationService->checkItemNamespaceAction('ANY', $right);
            } catch (\Exception $ex) {
                $exceptionRaised = true;
            }

            $this->assertTrue($exceptionRaised, 'Exception not raised for ANY and action: '.$right);
        }
    }


    /*
     *
     */
    public function testSpecificRights()
    {
        $rights = $this->rights;

        $allRights = array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'get_one' => false,
            'get_one_deep' => false,
            'get_all' => false,
            'get_all_deep' => false
        );

        $specifiedEntities = array('Item');
        $entityRights = array('Item' => array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'get_one' => false,
            'get_one_deep' => false,
            'get_all' => false,
            'get_all_deep' => false
        ));

        foreach ($rights as $right) {
            $entityRights['Item'][$right] = true;

            $authorizationService = new AuthorizationService($allRights, $entityRights, $specifiedEntities);

            $exceptionRaised = false;

            try {
                $authorizationService->checkItemNamespaceAction('Item', $right);
            } catch (\Exception $ex) {
                $exceptionRaised = true;
            }

            $this->assertFalse($exceptionRaised, 'Exception raised for Item and action: '.$right);
        }

        foreach ($rights as $right) {
            $entityRights['Item'][$right] = false;

            $authorizationService = new AuthorizationService($allRights, $entityRights, $specifiedEntities);

            $exceptionRaised = false;

            try {
                $authorizationService->checkItemNamespaceAction('Item', $right);
            } catch (\Exception $ex) {
                $exceptionRaised = true;
            }

            $this->assertTrue($exceptionRaised, 'Exception not raised for Item and action: '.$right);
        }
    }
}
