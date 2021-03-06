<?php

namespace Concerto\PanelBundle\Tests\Controller\FunctionalTests;

use Concerto\PanelBundle\Entity\AEntity;
use Concerto\PanelBundle\Entity\Test;

class TestNodeControllerTest extends AFunctionalTest {

    private static $repository;
    private static $portRepository;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$repository = static::$entityManager->getRepository("ConcertoPanelBundle:TestNode");
        self::$portRepository = static::$entityManager->getRepository("ConcertoPanelBundle:TestNodePort");
    }

    protected function setUp() {
        parent::setUp();

        $client = self::createLoggedClient();
        $client->request("POST", "/admin/Test/-1/save", array(
            "name" => "testFlow",
            "description" => "description",
            "code" => "print('start')",
            "visibility" => Test::VISIBILITY_FEATURED,
            "type" => Test::TYPE_FLOW,
            "accessibility" => AEntity::ACCESS_PUBLIC
        ));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(0, $content["result"]);
        $this->assertEquals(1, $content["object_id"]);

        $client->request("POST", "/admin/Test/-1/save", array(
            "name" => "test_s1",
            "description" => "description",
            "code" => "print('start')",
            "visibility" => Test::VISIBILITY_FEATURED,
            "type" => Test::TYPE_CODE,
            "accessibility" => AEntity::ACCESS_PUBLIC
        ));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(0, $content["result"]);
        $this->assertEquals(2, $content["object_id"]);

        $client->request("POST", "/admin/TestVariable/-1/save", array(
            "name" => "response",
            "test" => 2,
            "type" => 1,
            "passableThroughUrl" => '0'
        ));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(0, $content["result"]);
        $this->assertEquals(3, $content["object_id"]);

        $client->request("POST", "/admin/TestVariable/-1/save", array(
            "name" => "params",
            "test" => 2,
            "type" => 0,
            "passableThroughUrl" => '0'
        ));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(0, $content["result"]);
        $this->assertEquals(4, $content["object_id"]);

        $client->request("POST", "/admin/TestNode/-1/save", array(
            "flowTest" => 1,
            "sourceTest" => 2,
            "type" => 0,
            "posX" => 0,
            "posY" => 0
        ));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(0, $content["result"]);
        $this->assertEquals(3, $content["object_id"]);
    }

    public function testCollectionAction() {
        $client = self::createLoggedClient();

        $client->request('POST', '/admin/TestNode/collection');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $expected = array(
            array(
                "class_name" => "TestNode",
                "id" => 1,
                "type" => 1,
                "posX" => 15000,
                "posY" => 15000,
                "flowTest" => 1,
                "sourceTest" => 1,
                "sourceTestName" => "testFlow",
                "sourceTestDescription" => "description",
                "sourceTestObject" => json_decode($client->getResponse()->getContent(), true)[0]["sourceTestObject"],
                "ports" => array(
                    array(
                        "class_name" => "TestNodePort",
                        "id" => 1,
                        "node" => 1,
                        "string" => "1",
                        "defaultValue" => "1",
                        "variable" => 1,
                        "value" => '0',
                        "variableObject" => array(
                            "class_name" => "TestVariable",
                            "id" => 1,
                            "name" => "out",
                            "type" => 2,
                            "description" => "",
                            "passableThroughUrl" => "0",
                            "value" => '0',
                            "test" => 1,
                            "parentVariable" => null
                        )
                    )
                )
            ),
            array(
                "class_name" => "TestNode",
                "id" => 2,
                "type" => 2,
                "posX" => 15500,
                "posY" => 15100,
                "flowTest" => 1,
                "sourceTest" => 1,
                "sourceTestName" => "testFlow",
                "sourceTestDescription" => "description",
                "sourceTestObject" => json_decode($client->getResponse()->getContent(), true)[1]["sourceTestObject"],
                "ports" => array()
            ),
            array(
                "class_name" => "TestNode",
                "id" => 3,
                "type" => 0,
                "posX" => 0,
                "posY" => 0,
                "flowTest" => 1,
                "sourceTest" => 2,
                "sourceTestName" => "test_s1",
                "sourceTestDescription" => "description",
                "sourceTestObject" => json_decode($client->getResponse()->getContent(), true)[2]["sourceTestObject"],
                "ports" => array(
                    array(
                        "class_name" => "TestNodePort",
                        "id" => 2,
                        "node" => 3,
                        "string" => "1",
                        "defaultValue" => "1",
                        "variable" => 4,
                        "value" => null,
                        "variableObject" => array(
                            "class_name" => "TestVariable",
                            "id" => 4,
                            "name" => "params",
                            "type" => 0,
                            "description" => "",
                            "passableThroughUrl" => "0",
                            "value" => null,
                            "test" => 2,
                            "parentVariable" => null
                        )
                    ),
                    array(
                        "class_name" => "TestNodePort",
                        "id" => 3,
                        "node" => 3,
                        "string" => "1",
                        "defaultValue" => "1",
                        "variable" => 3,
                        "value" => null,
                        "variableObject" => array(
                            "class_name" => "TestVariable",
                            "id" => 3,
                            "name" => "response",
                            "type" => 1,
                            "description" => "",
                            "passableThroughUrl" => "0",
                            "value" => null,
                            "test" => 2,
                            "parentVariable" => null
                        )
                    ),
                    array(
                        "class_name" => "TestNodePort",
                        "id" => 4,
                        "node" => 3,
                        "string" => "1",
                        "defaultValue" => "1",
                        "variable" => 2,
                        "value" => "0",
                        "variableObject" => array(
                            "class_name" => "TestVariable",
                            "id" => 2,
                            "name" => "out",
                            "type" => 2,
                            "description" => "",
                            "passableThroughUrl" => "0",
                            "value" => '0',
                            "test" => 2,
                            "parentVariable" => null
                        )
                    )
                )
            )
        );
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    public function testCollectionByFlowTestAction() {
        $client = self::createLoggedClient();

        $client->request('POST', '/admin/TestNode/flow/1/collection');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $expected = array(
            array(
                "class_name" => "TestNode",
                "id" => 1,
                "type" => 1,
                "posX" => 15000,
                "posY" => 15000,
                "flowTest" => 1,
                "sourceTest" => 1,
                "sourceTestName" => "testFlow",
                "sourceTestDescription" => "description",
                "sourceTestObject" => json_decode($client->getResponse()->getContent(), true)[0]["sourceTestObject"],
                "ports" => array(
                    array(
                        "class_name" => "TestNodePort",
                        "id" => 1,
                        "node" => 1,
                        "string" => "1",
                        "defaultValue" => "1",
                        "variable" => 1,
                        "value" => '0',
                        "variableObject" => array(
                            "class_name" => "TestVariable",
                            "id" => 1,
                            "name" => "out",
                            "type" => 2,
                            "description" => "",
                            "passableThroughUrl" => "0",
                            "value" => '0',
                            "test" => 1,
                            "parentVariable" => null
                        )
                    )
                )
            ),
            array(
                "class_name" => "TestNode",
                "id" => 2,
                "type" => 2,
                "posX" => 15500,
                "posY" => 15100,
                "flowTest" => 1,
                "sourceTest" => 1,
                "sourceTestName" => "testFlow",
                "sourceTestDescription" => "description",
                "sourceTestObject" => json_decode($client->getResponse()->getContent(), true)[1]["sourceTestObject"],
                "ports" => array()
            ),
            array(
                "class_name" => "TestNode",
                "id" => 3,
                "type" => 0,
                "posX" => 0,
                "posY" => 0,
                "flowTest" => 1,
                "sourceTest" => 2,
                "sourceTestName" => "test_s1",
                "sourceTestDescription" => "description",
                "sourceTestObject" => json_decode($client->getResponse()->getContent(), true)[2]["sourceTestObject"],
                "ports" => array(
                    array(
                        "class_name" => "TestNodePort",
                        "id" => 2,
                        "node" => 3,
                        "string" => "1",
                        "defaultValue" => "1",
                        "variable" => 4,
                        "value" => null,
                        "variableObject" => array(
                            "class_name" => "TestVariable",
                            "id" => 4,
                            "name" => "params",
                            "type" => 0,
                            "description" => "",
                            "passableThroughUrl" => "0",
                            "value" => null,
                            "test" => 2,
                            "parentVariable" => null
                        )
                    ),
                    array(
                        "class_name" => "TestNodePort",
                        "id" => 3,
                        "node" => 3,
                        "string" => "1",
                        "defaultValue" => "1",
                        "variable" => 3,
                        "value" => null,
                        "variableObject" => array(
                            "class_name" => "TestVariable",
                            "id" => 3,
                            "name" => "response",
                            "type" => 1,
                            "description" => "",
                            "passableThroughUrl" => "0",
                            "value" => null,
                            "test" => 2,
                            "parentVariable" => null
                        )
                    ),
                    array(
                        "class_name" => "TestNodePort",
                        "id" => 4,
                        "node" => 3,
                        "string" => "1",
                        "defaultValue" => "1",
                        "variable" => 2,
                        "value" => "0",
                        "variableObject" => array(
                            "class_name" => "TestVariable",
                            "id" => 2,
                            "name" => "out",
                            "type" => 2,
                            "description" => "",
                            "passableThroughUrl" => "0",
                            "value" => '0',
                            "test" => 2,
                            "parentVariable" => null
                        )
                    )
                )
            )
        );
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));

        $client->request('POST', '/admin/TestNode/flow/2/collection');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $expected = array();
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    public function testDeleteAction() {
        $client = self::createLoggedClient();

        $client->request("POST", "/admin/TestNode/1/delete");
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertTrue($client->getResponse()->headers->contains("Content-Type", 'application/json'));
        $this->assertEquals(array("result" => 0, "object_ids" => 1), json_decode($client->getResponse()->getContent(), true));
        $this->assertCount(2, self::$repository->findAll());
        $this->assertCount(3, self::$portRepository->findAll());
    }

    public function testSaveActionNew() {
        $client = self::createLoggedClient();

        $client->request("POST", "/admin/TestNode/-1/save", array(
            "flowTest" => 1,
            "sourceTest" => 2,
            "type" => 0,
            "posX" => 100,
            "posY" => 100
        ));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertTrue($client->getResponse()->headers->contains("Content-Type", 'application/json'));

        $expected = array(
            "class_name" => "TestNode",
            "id" => 4,
            "type" => 0,
            "posX" => 100,
            "posY" => 100,
            "flowTest" => 1,
            "sourceTest" => 2,
            "sourceTestName" => "test_s1",
            "sourceTestDescription" => "description",
            "sourceTestObject" => json_decode($client->getResponse()->getContent(), true)["object"]["sourceTestObject"],
            "ports" => array(
                array(
                    "class_name" => "TestNodePort",
                    "id" => 5,
                    "node" => 4,
                    "string" => "1",
                    "defaultValue" => "1",
                    "variable" => 4,
                    "value" => null,
                    "variableObject" => array(
                        "class_name" => "TestVariable",
                        "id" => 4,
                        "name" => "params",
                        "type" => 0,
                        "description" => "",
                        "passableThroughUrl" => "0",
                        "value" => null,
                        "test" => 2,
                        "parentVariable" => null
                    )
                ),
                array(
                    "class_name" => "TestNodePort",
                    "id" => 6,
                    "node" => 4,
                    "string" => "1",
                    "defaultValue" => "1",
                    "variable" => 3,
                    "value" => null,
                    "variableObject" => array(
                        "class_name" => "TestVariable",
                        "id" => 3,
                        "name" => "response",
                        "type" => 1,
                        "description" => "",
                        "passableThroughUrl" => "0",
                        "value" => null,
                        "test" => 2,
                        "parentVariable" => null
                    )
                ),
                array(
                    "class_name" => "TestNodePort",
                    "id" => 7,
                    "node" => 4,
                    "string" => "1",
                    "defaultValue" => "1",
                    "variable" => 2,
                    "value" => "0",
                    "variableObject" => array(
                        "class_name" => "TestVariable",
                        "id" => 2,
                        "name" => "out",
                        "type" => 2,
                        "description" => "",
                        "passableThroughUrl" => "0",
                        "value" => '0',
                        "test" => 2,
                        "parentVariable" => null
                    )
                )
            )
        );

        $this->assertEquals(array(
            "result" => 0,
            "errors" => array(),
            "object_id" => 4,
            "object" => $expected
                ), json_decode($client->getResponse()->getContent(), true));
        $this->assertCount(4, self::$repository->findAll());
    }

    public function testSaveActionEdit() {
        $client = self::createLoggedClient();

        $client->request("POST", "/admin/TestNode/3/save", array(
            "flowTest" => 1,
            "sourceTest" => 2,
            "type" => 0,
            "posX" => 200,
            "posY" => 200
        ));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertTrue($client->getResponse()->headers->contains("Content-Type", 'application/json'));

        $expected = array(
            "class_name" => "TestNode",
            "id" => 3,
            "type" => 0,
            "posX" => 200,
            "posY" => 200,
            "flowTest" => 1,
            "sourceTest" => 2,
            "sourceTestName" => "test_s1",
            "sourceTestDescription" => "description",
            "sourceTestObject" => json_decode($client->getResponse()->getContent(), true)["object"]["sourceTestObject"],
            "ports" => array(
                array(
                    "class_name" => "TestNodePort",
                    "id" => 2,
                    "node" => 3,
                    "string" => "1",
                    "defaultValue" => "1",
                    "variable" => 4,
                    "value" => null,
                    "variableObject" => array(
                        "class_name" => "TestVariable",
                        "id" => 4,
                        "name" => "params",
                        "type" => 0,
                        "description" => "",
                        "passableThroughUrl" => "0",
                        "value" => null,
                        "test" => 2,
                        "parentVariable" => null
                    )
                ),
                array(
                    "class_name" => "TestNodePort",
                    "id" => 3,
                    "node" => 3,
                    "string" => "1",
                    "defaultValue" => "1",
                    "variable" => 3,
                    "value" => null,
                    "variableObject" => array(
                        "class_name" => "TestVariable",
                        "id" => 3,
                        "name" => "response",
                        "type" => 1,
                        "description" => "",
                        "passableThroughUrl" => "0",
                        "value" => null,
                        "test" => 2,
                        "parentVariable" => null
                    )
                ),
                array(
                    "class_name" => "TestNodePort",
                    "id" => 4,
                    "node" => 3,
                    "string" => "1",
                    "defaultValue" => "1",
                    "variable" => 2,
                    "value" => "0",
                    "variableObject" => array(
                        "class_name" => "TestVariable",
                        "id" => 2,
                        "name" => "out",
                        "type" => 2,
                        "description" => "",
                        "passableThroughUrl" => "0",
                        "value" => '0',
                        "test" => 2,
                        "parentVariable" => null
                    )
                )
            )
        );

        $this->assertEquals(array(
            "result" => 0,
            "errors" => array(),
            "object_id" => 3,
            "object" => $expected
                ), json_decode($client->getResponse()->getContent(), true));
        $this->assertCount(3, self::$repository->findAll());
    }

}
