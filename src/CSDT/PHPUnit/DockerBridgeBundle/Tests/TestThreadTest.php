<?php
/**
 * This file is part of the PHPUnit Docker Bridge project.
 *
 * As each files provides by the CSCFA, this file is licensed
 * under the MIT license.
 *
 * PHP version 5.6
 *
 * @category Test
 * @package  PHPUnitDockerBridge
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */

namespace CSDT\PHPUnit\DockerBridgeBundle\Tests;

use PHPUnit\Framework\TestCase;
use CSDT\PHPUnit\DockerBridgeBundle\TestThread\TestThread;
use CSDT\CollectionsBundle\Collections\MapCollection;
use CSDT\PHPUnit\DockerBridgeBundle\TestThread\TestInfo;

/**
 * Test thread test
 *
 * This class is used to validate the TestThread logic
 *
 * @category Test
 * @package  PHPUnitDockerBridge
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */
class TestThreadTest extends TestCase
{
    /**
     * Test constructor
     *
     * This method validate the TestThread constructor method
     *
     * @return void
     */
    public function testConstructor()
    {
        $className = 'ClassName';
        $instance = new TestThread($className);

        $this->assertClassName(
            $instance,
            $className,
            'The class name MUST be stored into the \'testClass\' property'
        );
        $this->assertTestsConstructor($instance);
    }

    /**
     * Method provider
     *
     * This method return a set of method to add into a TestThread
     *
     * @return array
     */
    public function methodProvider()
    {
        return array(
            array(
                'methodName',
                array('annotation' => array('annotationValue')),
            ),
            array(
                'methodName',
                null,
            ),
        );
    }

    /**
     * Test add method
     *
     * This method validate the TestThread add method logic
     *
     * @param string $methodName  The test method name
     * @param array  $annotations The test annotations
     *
     * @dataProvider methodProvider
     * @return void
     */
    public function testAddMethod($methodName, array $annotations = null)
    {
        $className = 'ClassName';
        $instance = new TestThread($className);

        if (!is_null($annotations)) {
            $result = $instance->addMethod($methodName, $annotations);
        } else {
            $result = $instance->addMethod($methodName);
        }

        $this->assertNull(
            $result,
            'The \'addMethod\' method MUST return void'
        );

        $tests = $this->getTestsProperty($instance);

        $this->assertMethodAdded($tests, $methodName, $annotations);
    }

    /**
     * Test getTest
     *
     * This method validate the TestThread getTest method
     *
     * @depends testAddMethod
     * @return void
     */
    public function testGetTest()
    {
        $testName = 'test';
        $className = 'ClassName';
        $instance = new TestThread($className);
        $instance->addMethod($testName);

        $test = $instance->getTest($testName);

        $this->assertTestInfo(
            $test,
            'The \'getTest\' method MUST return the requested TestInfo '.'according with the test name if it\'s stored'
        );

        $this->assertTestInfoContent(
            $test,
            $testName
        );

        $this->assertNull(
            $instance->getTest('fakeTest'),
            'The \'getTest\' method MUST return NULL if the requested test '.'is not stored'
        );
    }

    /**
     * Test resolve dependence
     *
     * This method validate the TestThread resolveDependence method
     *
     * @depends testGetTest
     * @return void
     */
    public function testResolveDependence()
    {
        $dependenceTest = array('dependence', null);
        $childTest = array('child', array('depends' => array('dependence')));

        $className = 'ClassName';
        $instance = new TestThread($className);

        $instance->addMethod($dependenceTest[0]);
        $instance->addMethod($childTest[0], $childTest[1]);

        $result = $instance->resolveDependence();

        $this->assertNull(
            $result,
            'The \'resolveDependence\' method MUST return void'
        );

        $dependence = $instance->getTest($dependenceTest[0]);
        $child = $instance->getTest($childTest[0]);

        $message = 'The \'resolveDependence\' method MUST inject the '.'dependent test into their parents, and their parents into '.'their dependences for each stored tests';

        $this->assertEquals(
            1,
            $dependence->getDependency()->count(),
            $message
        );
        $expectedChild = $dependence->getDependency()->toArray()[0];

        $this->assertEquals(
            1,
            $child->getDependence()->count(),
            $message
        );
        $expectedParent = $child->getDependence()->toArray()[0];

        $this->assertTestInfoContent(
            $expectedChild,
            $childTest[0],
            $childTest[1]
        );

        $this->assertTestInfoContent(
            $expectedParent,
            $dependenceTest[0],
            $dependenceTest[1]
        );
    }

    /**
     * Assert method added
     *
     * This method validate the TestThread addMethod logic
     *
     * @param MapCollection $collection  The current test collection
     * @param string        $methodName  The test method name
     * @param array         $annotations The test annotations
     *
     * @return void
     */
    private function assertMethodAdded(
        MapCollection $collection,
        $methodName,
        array $annotations = null
    ) {
        $methodArray = $collection->toArray();

        $message = 'The \'addMethod\' method MUST register a new TestInfo '.'into the \'tests\' MapCollection, referenced by test name';

        $this->assertEquals(1, count($methodArray), $message);

        $testMethod = $methodArray[array_keys($methodArray)[0]];
        $test = $this->assertTestInfo($testMethod, $message);
        $this->assertTestInfoContent(
            $this->assertTestInfo($test, $message),
            $methodName,
            $annotations
        );
    }

    /**
     * Assert TestInfo content
     *
     * This method validate the created TestInfo content
     *
     * @param TestInfo $test        The current TestInfo instance
     * @param string   $methodName  The test method name
     * @param array    $annotations The test annotations
     *
     * @return void
     */
    private function assertTestInfoContent(
        TestInfo $test,
        $methodName,
        array $annotations = null
    ) {
        $this->assertEquals(
            $methodName,
            $test->getName(),
            'The created TestInfo MUST contain the given method name as name'
        );

        if (!is_null($annotations)) {
            foreach ($annotations as $annotationName => $annotationContent) {
                $annotation = $test->getAnnotation($annotationName);

                $this->assertFalse(
                    $annotation->isEmpty(),
                    'The created TestInfo SHOULD contain the given '.'annotations as annotations'
                );

                foreach ($annotationContent as $content) {
                    $this->assertTrue(
                        $annotation->contain($content),
                        'The created TestInfo SHOULD contain the given '.'annotations as annotations'
                    );
                }
            }
        }
    }

    /**
     * Assert test info
     *
     * This method validate the given instance is a TestInfo instance
     *
     * @param mixed  $test    The expected TestInfo
     * @param string $message The error message
     *
     * @return TestInfo
     */
    private function assertTestInfo($test, $message)
    {
        $this->assertInstanceOf(
            TestInfo::class,
            $test,
            $message
        );

        return $test;
    }

    /**
     * Assert empty tests
     *
     * This method validate the tests are an empty MapCollection
     * after TestThread construction
     *
     * @param TestThread $instance The current tested instance
     *
     * @return void
     */
    private function assertTestsConstructor(TestThread $instance)
    {
        $tests = $this->getTestsProperty($instance);
        $this->assertMapCollection(
            $tests,
            'The \'tests\' property MUST be initialized as a MapCollection'
        );
        $this->assertMapCollectionEmpty(
            $tests,
            'The \'tests\' property MUST be initialized as an empty '.'MapCollection'
        );
    }

    /**
     * Assert MapCollection empty
     *
     * Validate the given MapCollection is empty
     *
     * @param MapCollection $collection The collection
     * @param string        $message    The error message
     *
     * @return void
     */
    private function assertMapCollectionEmpty(
        MapCollection $collection,
        $message
    ) {
        $this->assertTrue($collection->isEmpty(), $message);
    }

    /**
     * Assert MapCollection
     *
     * This method validate the given object is an instance of
     * MapCollection
     *
     * @param mixed  $object  The object to validate
     * @param string $message The error message
     *
     * @return void
     */
    private function assertMapCollection($object, $message)
    {
        $this->assertInstanceOf(MapCollection::class, $object, $message);
    }

    /**
     * Assert class name
     *
     * This method assert a given instance class name is
     * equal to the given expected class name
     *
     * @param TestThread $instance The current tested instance
     * @param string     $expected The expected class name
     * @param string     $message  The error message
     *
     * @return void
     */
    private function assertClassName(TestThread $instance, $expected, $message)
    {
        $this->assertEquals(
            $expected,
            $this->getClassNameProperty($instance),
            $message
        );
    }

    /**
     * Get tests property
     *
     * Return the tests property from a TestThread instance
     *
     * @param TestThread $instance The current tested instance
     *
     * @return MapCollection
     */
    private function getTestsProperty(TestThread $instance)
    {
        return $this->getPropertyValue($instance, 'tests');
    }

    /**
     * Get tests property
     *
     * Return the tests property from a TestThread instance
     *
     * @param TestThread $instance The current tested instance
     *
     * @return MapCollection
     */
    private function getClassNameProperty(TestThread $instance)
    {
        return $this->getPropertyValue($instance, 'testClass');
    }

    /**
     * Get property value
     *
     * Return the value stored into the given instance property
     *
     * @param TestThread $instance     The current tested instance
     * @param string     $propertyName The property name
     *
     * @return mixed
     */
    private function getPropertyValue(TestThread $instance, $propertyName)
    {
        $property = new \ReflectionProperty(TestThread::class, $propertyName);
        $property->setAccessible(true);

        return $property->getValue($instance);
    }
}
