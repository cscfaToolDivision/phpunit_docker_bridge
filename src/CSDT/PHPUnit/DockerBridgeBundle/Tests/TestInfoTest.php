<?php

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
 * @author Matthieu Vallance <matthieu.vallance@cscfa.fr>
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
            "method2"
        );
        
        $annotations = array(
            "depends" => array("test1", "test2"),
            "noContainer" => array(true),
            "dataProvider" => array("valueProvider"),
            null
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
     * @return void
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
        } else if ($annotation instanceof MapCollection) {
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
                'The annotations MUST be stored into a MapCollection as '.
                'annotation name for key'
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
                'The annotations MUST be stored into a MapCollection'.
                ' as annotation name for key and annotation values '.
                'as value, into a ValueCollection'
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
            'IF no annotations are given, the \'annotations\' property MUST '.
            'be an empty MapCollection'
        );
    }
}
