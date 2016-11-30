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
use CSDT\PHPUnit\DockerBridgeBundle\TestThread\TestInfo;
use CSDT\CollectionsBundle\Collections\MapCollection;
use CSDT\CollectionsBundle\Collections\ValueCollection;

/**
 * Test info test
 *
 * This class is used to validate the TestInfo logic
 *
 * @category Test
 * @package  PHPUnitDockerBridge
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */
class TestInfoTest extends TestCase
{
    /**
     * Argument provider
     *
     * Return a set of arguments for TestInfo
     *
     * @return array
     */
    public function argumentsProvider()
    {
        $methodNames = array(
            "method1",
            "method2",
        );

        $annotations = array(
            "depends" => array("test1", "test2"),
            "noContainer" => array(true),
            "dataProvider" => array("valueProvider"),
            null,
        );

        $result = array();
        foreach ($methodNames as $method) {
            foreach ($annotations as $annotation => $value) {
                if (is_null($value)) {
                    array_push($result, array($method, null));
                } else {
                    array_push(
                        $result,
                        array($method, array($annotation => $value))
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Test constructor
     *
     * This method validate the TestInfo constructor
     *
     * @param string $method      The constructor method name
     * @param array  $annotations The constructor annotations
     *
     * @dataProvider argumentsProvider
     * @return       void
     */
    public function testConstructor($method, array $annotations = null)
    {
        if (is_null($annotations)) {
            $instance = new TestInfo($method);
        } else {
            $instance = new TestInfo($method, $annotations);
        }

        $this->assertDependOf($instance);

        $this->assertDependence($instance);

        $this->assertTestStatus($instance);

        $this->assertMethodName($instance, $method);

        $this->assertAnnotation($instance, $annotations);
    }

    /**
     * Test getName
     *
     * This method validate the TestInfo getName method
     *
     * @param string $method The constructor method name
     *
     * @dataProvider argumentsProvider
     * @return       void
     */
    public function testGetName($method)
    {
        $classReflection = new \ReflectionClass(TestInfo::class);
        $instance = $classReflection->newInstanceWithoutConstructor();

        $methodNameProperty = new \ReflectionProperty(
            TestInfo::class,
            "methodName"
        );
        $methodNameProperty->setAccessible(true);
        $methodNameProperty->setValue($instance, $method);

        $this->assertEquals(
            $method,
            $instance->getName(),
            'The \'getName\' method MUST return the value stored in '.'\'methodName\' property'
        );
    }

    /**
     * Test get annotation
     *
     * This method validate the TestInfo getAnnotation method
     *
     * @param string $method      The constructor method name
     * @param array  $annotations The constructor annotations
     *
     * @dataProvider argumentsProvider
     * @return       void
     */
    public function testGetAnnotation($method, array $annotations = null)
    {
        if (is_null($annotations)) {
            $instance = new TestInfo($method);
        } else {
            $instance = new TestInfo($method, $annotations);
        }

        $names = array();
        if (!is_null($annotations)) {
            foreach ($annotations as $name => $values) {
                array_push($names, $name);
                $this->assertGetAnnotationExist($instance, $name, $values);
            }
        }

        $this->assertGetFakeAnnotation($instance, $names);
    }

    /**
     * Test add dependency
     *
     * This method validate the TestInfo addDependency method
     *
     * @return array [TestInfo, ValueCollection]
     */
    public function testAddDependency()
    {
        $instance = new TestInfo("method");
        $dependency = new TestInfo("method");

        $returnValue = $instance->addDependency($dependency);

        $this->assertNull(
            $returnValue,
            'The \'addDependency\' method MUST not return any value'
        );

        $dependenceProperty = new \ReflectionProperty(
            TestInfo::class,
            "dependence"
        );
        $dependenceProperty->setAccessible(true);

        $dependence = $dependenceProperty->getValue($instance);
        $this->assertAddDepency(
            $dependence,
            $dependency
        );

        return array($instance, $dependence);
    }

    /**
     * Test get dependency
     *
     * This method validate the TestInfo getDependency method
     *
     * @param array $arguments The instance and dependence into array
     *
     * @depends testAddDependency
     * @return  void
     */
    public function testGetDependency(array $arguments)
    {
        list($instance, $dependence) = $arguments;

        $result = $instance->getDependency();

        $this->assertSame(
            $dependence,
            $result,
            'The \'getDependency\' method MUST return the stored '.'\'dependence\' ValueCollection'
        );
    }

    /**
     * Test add depend
     *
     * This method validate the TestInfo addDepend method
     *
     * @return array [TestInfo, ValueCollection]
     */
    public function testAddDepend()
    {
        $instance = new TestInfo("method");
        $depend = new TestInfo("method");

        $returnValue = $instance->addDepend($depend);

        $this->assertNull(
            $returnValue,
            'The \'addDepend\' method MUST not return any value'
        );

        $dependOfProperty = new \ReflectionProperty(
            TestInfo::class,
            "dependOf"
        );
        $dependOfProperty->setAccessible(true);

        $dependOf = $dependOfProperty->getValue($instance);
        $this->assertAddDepend(
            $dependOf,
            $depend
        );

        return array($instance, $dependOf);
    }

    /**
     * Test get dependence
     *
     * This method validate the TestInfo getDependence method
     *
     * @param array $arguments The instance and dependOf into array
     *
     * @depends testAddDepend
     * @return  void
     */
    public function testGetDependence(array $arguments)
    {
        list($instance, $dependOf) = $arguments;

        $result = $instance->getDependence();

        $this->assertSame(
            $dependOf,
            $result,
            'The \'getDepend\' method MUST return the stored \'dependOf\' '.'ValueCollection'
        );
    }

    /**
     * Status provider
     *
     * This method is used to provide the TestInfo status
     *
     * @return binary[]
     */
    public function statusProvider()
    {
        return array(
            array(TestInfo::FAILED),
            array(TestInfo::IN_PROGRESS),
            array(TestInfo::IN_QUEUE),
            array(TestInfo::PASSED),
            array(TestInfo::SKIPPED),
            array(TestInfo::SUCCESS),
        );
    }

    /**
     * Test set status
     *
     * This method validate the TestInfo setStatus method
     *
     * @param binary $status The status to assign
     *
     * @dataProvider statusProvider
     * @return       void
     */
    public function testSetStatus($status)
    {
        $instance = new TestInfo("method");

        $testStatusProperty = new \ReflectionProperty(
            TestInfo::class,
            "testStatus"
        );
        $testStatusProperty->setAccessible(true);

        $result = $instance->setStatus($status);

        $this->assertNull(
            $result,
            'The \'setStatus\' method MUST not return any value'
        );

        $testStatus = $testStatusProperty->getValue($instance);
        $this->assertEquals(
            $status,
            $testStatus,
            'The \'setStatus\' method MUST store the given value into the '.'\'testStatus\' property'
        );
    }

    /**
     * Test get status
     *
     * This method validate the TestInfo getStatus method
     *
     * @param array $status The expected status
     *
     * @dataProvider statusProvider
     * @return       void
     */
    public function testGetStatus($status)
    {
        $instance = new TestInfo("method");

        $testStatusProperty = new \ReflectionProperty(
            TestInfo::class,
            "testStatus"
        );
        $testStatusProperty->setAccessible(true);
        $testStatusProperty->setValue($instance, $status);

        $result = $instance->getStatus();

        $this->assertEquals(
            $status,
            $result,
            'The \'getStatus\' method MUST return the value stored into '.'\'testStatus\' property'
        );
    }

    /**
     * Assert add depend
     *
     * This method validate the dependOf content after
     * addDepend method calling
     *
     * @param ValueCollection $dependOf The dependOf content
     * @param TestInfo        $expected The expected content
     *
     * @return void
     */
    private function assertAddDepend(
        ValueCollection $dependOf,
        TestInfo $expected
    ) {

        $message = 'The \'addDepend\' method MUST add the given TestInfo'.' instance to the \'dependOf\' property ValueCollection';

        $this->assertDependencies($dependOf, $expected, $message);
    }

    /**
     * Assert add dependency
     *
     * This method validate the dependence content after
     * addDependency method calling
     *
     * @param ValueCollection $dependence The dependence content
     * @param TestInfo        $expected   The expected content
     *
     * @return void
     */
    private function assertAddDepency(
        ValueCollection $dependence,
        TestInfo $expected
    ) {

        $message = 'The \'addDependency\' method MUST add the given TestInfo'.' instance to the \'dependence\' property ValueCollection';

        $this->assertDependencies($dependence, $expected, $message);
    }

    /**
     * Assert dependencies
     *
     * This method validate both addDependency and addDepend methods
     *
     * @param ValueCollection $dependence The dependence content
     * @param TestInfo        $expected   The expected content
     * @param string          $message    The error message
     *
     * @return void
     */
    private function assertDependencies(
        ValueCollection $dependence,
        TestInfo $expected,
        $message
    ) {

        $dependencies = $dependence->toArray();

        $this->assertCount(
            1,
            $dependencies,
            $message
        );

        $this->assertSame(
            $expected,
            $dependencies[0],
            $message
        );
    }

    /**
     * Assert get fake annotation
     *
     * Validate the getAnnotation logic with an unexisting annotation
     *
     * @param TestInfo $instance The current tested instance
     * @param array    $names    The existing annotation names
     *
     * @return void
     */
    private function assertGetFakeAnnotation(TestInfo $instance, array $names)
    {
        $fakeName = "";
        do {
            $fakeName = uniqid();
        } while (array_search($fakeName, $names) !== false);

        $emptyAnnotation = $instance->getAnnotation($fakeName);


        $this->assertInstanceOf(
            ValueCollection::class,
            $emptyAnnotation,
            'The \'getAnnotation\' method MUST return a ValueCollection'
        );

        $this->assertTrue(
            $emptyAnnotation->isEmpty(),
            'The \'getAnnotation\' method SHOULD return an empty '.'ValueCollection if the given annotation name is not stored'
        );
    }

    /**
     * Assert get annotation exist
     *
     * Validate the getAnnotation logic with an existing annotation
     *
     * @param TestInfo $instance The current tested instance
     * @param string   $name     The annotation name
     * @param array    $values   The expected annotation values
     *
     * @return void
     */
    private function assertGetAnnotationExist(
        TestInfo $instance,
        $name,
        array $values
    ) {

        $annotation = $instance->getAnnotation($name);

        $this->assertInstanceOf(
            ValueCollection::class,
            $annotation,
            'The \'getAnnotation\' method MUST return a ValueCollection'
        );

        foreach ($values as $value) {
            $this->assertTrue(
                $annotation->contain($value),
                'The \'getAnnotation\' method SHOULD return the annotation '.'ValueCollection corresponding to the given annotation name '.'if it stored'
            );
        }
    }

    /**
     * Assert testStatus
     *
     * This method validate the TestInfo testStatus storage
     * logic from constructor
     *
     * @param TestInfo $instance The current tested instance
     *
     * @return void
     */
    private function assertTestStatus(TestInfo $instance)
    {
        $testStatusProperty = new \ReflectionProperty(
            TestInfo::class,
            "testStatus"
        );
        $testStatusProperty->setAccessible(true);

        $testStatus = $testStatusProperty->getValue($instance);
        $this->assertEquals(
            TestInfo::IN_QUEUE,
            $testStatus,
            'The \'testStatus\' MUST be relative to \'IN QUEUE\' by default'
        );
    }

    /**
     * Assert dependOf
     *
     * This method validate the TestInfo dependOf storage
     * logic from constructor
     *
     * @param TestInfo $instance The current tested instance
     *
     * @return void
     */
    private function assertDependOf(TestInfo $instance)
    {
        $dependOfProperty = new \ReflectionProperty(
            TestInfo::class,
            "dependOf"
        );
        $dependOfProperty->setAccessible(true);

        $dependOf = $dependOfProperty->getValue($instance);
        $this->assertInstanceOf(
            ValueCollection::class,
            $dependOf,
            'The \'dependOf\' property MUST contain a ValueCollection'
        );

        $this->assertTrue(
            $dependOf->isEmpty(),
            'The \'dependOf\' property MUST contain an empty ValueCollection'
        );
    }

    /**
     * Assert dependence
     *
     * This method validate the TestInfo dependence storage
     * logic from constructor
     *
     * @param TestInfo $instance The current tested instance
     *
     * @return void
     */
    private function assertDependence(TestInfo $instance)
    {
        $dependenceProperty = new \ReflectionProperty(
            TestInfo::class,
            "dependence"
        );
        $dependenceProperty->setAccessible(true);

        $dependence = $dependenceProperty->getValue($instance);
        $this->assertInstanceOf(
            ValueCollection::class,
            $dependence,
            'The \'dependence\' property MUST contain a ValueCollection'
        );

        $this->assertTrue(
            $dependence->isEmpty(),
            'The \'dependence\' property MUST contain an empty ValueCollection'
        );
    }

    /**
     * Assert method name
     *
     * This method validate the TestInfo method name storage
     * logic from constructor
     *
     * @param TestInfo $instance The current tested instance
     * @param string   $method   The method name
     *
     * @return void
     */
    private function assertMethodName(TestInfo $instance, $method)
    {
        $methodNameProperty = new \ReflectionProperty(
            TestInfo::class,
            "methodName"
        );
        $methodNameProperty->setAccessible(true);

        $this->assertEquals(
            $method,
            $methodNameProperty->getValue($instance),
            'The method name MUST be stored into \'methodName\' property'
        );
    }

    /**
     * Assert annotation
     *
     * This method validate the TestInfo annotation storage
     * logic from constructor
     *
     * @param TestInfo $instance    The current tested instance
     * @param array    $annotations The annotations
     *
     * @return void
     */
    private function assertAnnotation(TestInfo $instance, $annotations)
    {
        $annotationPropery = new \ReflectionProperty(
            TestInfo::class,
            "annotations"
        );
        $annotationPropery->setAccessible(true);

        $annotation = $annotationPropery->getValue($instance);
        $this->assertInstanceOf(
            MapCollection::class,
            $annotation,
            'The annotations MUST be stored into a MapCollection'
        );

        if (is_null($annotations)) {
            $this->assertNullAnnotation($annotation);
        } elseif ($annotation instanceof MapCollection) {
            $this->assertNotNullAnnotation($annotation, $annotations);
        }
    }

    /**
     * Assert not null annotation
     *
     * This method validate the TestInfo annotation storage
     * logic from constructor in case of not empty array
     * annotation given
     *
     * @param MapCollection $annotation  The stored annotations
     * @param array         $annotations The given annotations
     *
     * @return void
     */
    private function assertNotNullAnnotation(
        MapCollection $annotation,
        array $annotations
    ) {

        $this->assertFalse(
            $annotation->isEmpty(),
            'The annotations MUST be stored into a MapCollection'
        );

        foreach ($annotations as $key => $values) {
            $this->assertTrue(
                $annotation->has($key),
                'The annotations MUST be stored into a MapCollection as '.'annotation name for key'
            );

            $annotationValues = $annotation->get($key);
            $this->assertInstanceOf(
                ValueCollection::class,
                $annotationValues,
                'The annotations values MUST be stored as ValueCollection'
            );

            $this->assertAnnotationContent($annotationValues, $values);
        }
    }

    /**
     * Assert annotation content
     *
     * This method validate the TestInfo by annotation
     * name storage
     *
     * @param ValueCollection $collection The stored annotation
     * @param array           $values     The given values
     *
     * @return void
     */
    private function assertAnnotationContent(
        ValueCollection $collection,
        array $values
    ) {

        foreach ($values as $value) {
            $this->assertTrue(
                $collection->contain($value),
                'The annotations MUST be stored into a MapCollection'.' as annotation name for key and annotation values '.'as value, into a ValueCollection'
            );
        }
    }

    /**
     * Test null annotation
     *
     * This method validate the TestInfo annotation storage
     * logic from constructor in case of null annotation
     * given
     *
     * @param MapCollection $annotation The stored annotations
     *
     * @return void
     */
    private function assertNullAnnotation(MapCollection $annotation)
    {
        $this->assertTrue(
            $annotation->isEmpty(),
            'IF no annotations are given, the \'annotations\' property MUST '.'be an empty MapCollection'
        );
    }
}
