<?php
namespace CSDT\PHPUnit\DockerBridgeBundle\TestThread;

use CSDT\CollectionsBundle\Collections\MapCollection;

/**
 * Test thread
 *
 * This class is used to store a test case thread.
 *
 * @author Matthieu Vallance <matthieu.vallance@cscfa.fr>
 */
class TestThread
{
    /**
     * Test class
     *
     * The tested class name
     *
     * @var string
     */
    private $testClass;

    /**
     * Tests
     *
     * The class tests
     *
     * @var MapCollection
     */
    private $tests;

    /**
     * Construct
     *
     * The default class constructor
     *
     * @param string $class The test case class
     *
     * @return void
     */
    public function __construct($class)
    {
        $this->testClass = $class;
        $this->tests = new MapCollection();
    }

    /**
     * Add method
     *
     * Inject a test method into the current test thread
     *
     * @param string $methodName  The test method name
     * @param array  $annotations The test method annotations
     *
     * @return void
     */
    public function addMethod($methodName, array $annotations = array())
    {
        $testInfo = new TestInfo($methodName, $annotations);

        $this->tests->set($methodName, $testInfo);
    }

    /**
     * Resolve dependence
     *
     * This method resolve the dependence of the current thread
     *
     * @return void
     */
    public function resolveDependence()
    {

        foreach ($this->tests as $test) {
            $dependency = $this->getDependency($test);
            $this->addTestDependency($test, $dependency);
        }
    }

    /**
     * Add test dependency
     *
     * This method inject the dependency informations
     * into the dependents tests
     *
     * @param TestInfo $test    The dependent test
     * @param array    $depends The dependency of the test
     *
     * @return void
     */
    private function addTestDependency(TestInfo $test, array $depends)
    {
        foreach ($depends as $depend) {
            $test->addDepend($depend);

            if ($depend instanceof TestInfo) {
                $depend->addDependency($test);
            }
        }
    }

    /**
     * Get dependency
     *
     * Return the array dependency of the given test
     *
     * @param TestInfo $test The test whence return the dependency
     *
     * @return array
     */
    private function getDependency(TestInfo $test)
    {
        $dependency = $test->getAnnotation('depends');

        if ($dependency->isEmpty()) {
            return array();
        }

        $dependOf = array();
        foreach ($dependency as $depend) {
            if ($this->tests->has($depend)) {
                array_push($dependOf, $this->tests->get($depend));
            }
        }

        return $dependOf;
    }

    /**
     * Get test
     *
     * Return the thread tests if exists, or null
     *
     * @return TestInfo|NULL
     */
    public function getTest($name)
    {
        return $this->tests->get($name);
    }
}
