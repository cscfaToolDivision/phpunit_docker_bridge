<?php
/**
 * This file is part of the PHPUnit Docker Bridge project.
 *
 * As each files provides by the CSCFA, this file is licensed
 * under the MIT license.
 *
 * PHP version 5.6
 *
 * @category TestCase
 * @package  PHPUnitDockerBridge
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */

namespace CSDT\PHPUnit\DockerBridgeBundle\TestCase;

use CSDT\PHPUnit\DockerBridgeBundle\TestThread\TestThread;
use CSDT\DockerUtilBundle\Container\Container;
use CSDT\PHPUnit\DockerBridgeBundle\TestThread\TestInfo;
use CSDT\CollectionsBundle\Collections\MapCollection;

/**
 * Test case trait
 *
 * This class is used to provide abstraction logic to the
 * container building tests
 *
 * @category TestCase
 * @package  PHPUnitDockerBridge
 * @author   matthieu vallance <matthieu.vallance@cscfa.fr>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     http://cscfa.fr
 */
trait TestCaseTrait
{

    /**
     * Test threads
     *
     * The current testThread by class
     *
     * @var MapCollection <TestThread>
     */
    private static $testThreads = null;

    /**
     * Container
     *
     * The current container by class
     *
     * @var MapCollection <Container>
     */
    private static $container = null;

    /**
     * BuildError
     *
     * The current class building error
     *
     * @var \Exception
     */
    private $buildError = null;

    /**
     * Last test
     *
     * The last runned test
     *
     * @var string
     */
    private static $lastTest = null;

    /**
     * Build container
     *
     * Here you can build a container to be used by
     * the tests
     *
     * @return Container
     */
    abstract protected function buildContainer();

    /**
     * Remove container
     *
     * Here you can define how to stop and remove
     * your containers
     *
     * @param Container $container The builded container
     *
     * @return void
     */
    abstract protected function removeContainer(Container $container);

    /**
     * Construct
     *
     * Constructs a test case with the given name.
     *
     * @param string $name     The test case name
     * @param array  $data     The test template data
     * @param string $dataName The data name
     *
     * @return void
     */
    public function __construct($name = null, array $data = array(), $dataName = "")
    {
        parent::__construct($name, $data, $dataName);

        if (self::$testThreads === null) {
            self::$testThreads = new MapCollection();
        }
        if (self::$container === null) {
            self::$container = new MapCollection();
        }

        if (!self::$testThreads->has(get_class($this))) {
            $this->parseTests();
        }
    }

    /**
     * Get test prefix
     *
     * Return the current test prefix, as 'test' by default
     *
     * @return string
     */
    protected function getTestPrefix()
    {
        return "test";
    }

    /**
     * This method is used to start building the container.
     *
     * {@inheritDoc}
     *
     * @see PHPUnit_Framework_TestCase::assertPreConditions()
     */
    protected function setUp()
    {
        parent::setUp();

        $testThread = $this->getCurrentTestThread();
        $test = $testThread->getTest($this->getName(false));

        $test->setStatus(TestInfo::IN_PROGRESS);

        if (!$test->getAnnotation("NoContainer")->isEmpty()) {
            return;
        }

        $isDataRecursion = (
            !$test->getAnnotation("dataProvider")->isEmpty() &&
            self::$lastTest == $test->getName()
        );

        try {
            if ($test->getDependence()->isEmpty() && !$isDataRecursion) {
                if (self::$container->has(get_class($this))) {
                    $this->removeContainer(self::$container->get(get_class($this)));
                    self::$container->remove(get_class($this));
                }
                self::$container->set(get_class($this), $this->buildContainer());
            } elseif (!self::$container->has(get_class($this))) {
                self::$container->set(get_class($this), $this->buildContainer());
            }
        } catch (\Exception $exception) {
            $this->buildError = $exception;
            throw $exception;
        }

        self::$lastTest = $test->getName();
    }

    /**
     * This method is used to stop the container.
     *
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::assertPostConditions()
     */
    protected function assertPostConditions()
    {
        parent::assertPostConditions();

        $testThread = $this->getCurrentTestThread();
        $test = $testThread->getTest($this->getName(false));

        $test->setStatus(TestInfo::PASSED | TestInfo::SUCCESS);

        if (!$test->getAnnotation("NoContainer")->isEmpty()) {
            return;
        }

        try {
            if ($test->getDependency()->isEmpty()) {
                if (self::$container->has(get_class($this))) {
                    $this->removeContainer(self::$container->get(get_class($this)));
                    self::$container->remove(get_class($this));
                }
            }
        } catch (\Exception $exception) {
            $this->onNotSuccessfulTest($exception);
        }
    }

    /**
     * This method is used to stop the container.
     *
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::onNotSuccessfulTest()
     */
    protected function onNotSuccessfulTest(\Exception $exception)
    {
        $testThread = $this->getCurrentTestThread();
        $test = $testThread->getTest($this->getName(false));

        $test->setStatus(TestInfo::PASSED | TestInfo::FAILED);

        $this->setDependencySkipped($test);

        if (!$test->getAnnotation("NoContainer")->isEmpty()) {
            parent::onNotSuccessfulTest($exception);
        }

        try {
            if (self::$container->has(get_class($this))) {
                $this->removeContainer(self::$container->get(get_class($this)));
            }
            self::$container->remove(get_class($this));
        } catch (\Exception $e) {
            parent::onNotSuccessfulTest($exception);
        }

        if (!is_null($this->buildError)) {
            $exception = $this->buildError;
        }
        parent::onNotSuccessfulTest($exception);
    }

    /**
     * Set dependency skipped
     *
     * Set the current test dependence to skipped state
     *
     * @param TestInfo $test The current test
     *
     * @return void
     */
    private function setDependencySkipped(TestInfo $test)
    {
        foreach ($test->getDependency() as $child) {
            $this->setSkipped($child);
        }
    }

    /**
     * Set skipped
     *
     * Set a test to skipped
     *
     * @param TestInfo $test The test to update
     *
     * @return void
     */
    private function setSkipped(TestInfo $test)
    {
        $test->setStatus(TestInfo::SKIPPED);
    }

    /**
     * Get method name
     *
     * Return a method name
     *
     * @param \ReflectionMethod $method The reflection method instance
     *
     * @return string
     */
    private function getMethodName(\ReflectionMethod $method)
    {
        return $method->getName();
    }

    /**
     * Is test
     *
     * Test whether a method name is a test
     *
     * @param string $methodName The method name to test
     *
     * @return boolean
     */
    private function isTest($methodName)
    {
        return boolval(preg_match('/^'.$this->getTestPrefix().'/', $methodName));
    }

    /**
     * Parse test
     *
     * Parse the current class to build the test thread
     *
     * @return void
     */
    private function parseTests()
    {
        $currentClass = get_class($this);
        $classReflex = new \ReflectionClass($currentClass);

        $testThread = new TestThread(get_class($this));

        $methods = $classReflex->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $this->getMethodName($method);
            if (!$this->isTest($methodName)) {
                continue;
            }

            $annotation = \PHPUnit_Util_Test::parseTestMethodAnnotations($currentClass, $methodName);

            if (isset($annotation["method"])) {
                $testThread->addMethod($methodName, $annotation["method"]);
            } else {
                $testThread->addMethod($methodName);
            }
        }

        $testThread->resolveDependence();

        self::$testThreads->set(get_class($this), $testThread);
    }

    /**
     * Get current test
     *
     * Return the current test
     *
     * @return TestInfo|NULL
     */
    public function getCurrentTest()
    {
        $currentThread = $this->getCurrentTestThread();

        return $currentThread->getTest($this->getName(false));
    }

    /**
     * Get current test thread
     *
     * This class return the current test thread
     *
     * @return TestThread|null
     */
    private function getCurrentTestThread()
    {
        if (self::$testThreads->has(get_class($this))) {
            return self::$testThreads->get(get_class($this));
        }

        return null;
    }

    /**
     * Destruct
     *
     * The default class destructor
     *
     * @return void
     */
    public function __destruct()
    {
        if (self::$container->has(get_class($this))) {
            $this->removeContainer(self::$container->get(get_class($this)));
        }
    }
}
