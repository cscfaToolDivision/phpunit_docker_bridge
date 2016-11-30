# TestThread Test

The test of TestThread must validate the following :

## constructor
 * The class name MUST be stored into the 'testClass' property
 * The 'tests' property MUST be initialized as an empty MapCollection

## addMethod
 * The 'addMethod' method MUST register a new TestInfo into the 'tests' MapCollection, referenced by test name
 * The created TestInfo MUST contain the given method name as name.
 * The created TestInfo SHOULD contain the given annotations as annotations.
 * The 'addMethod' method MUST return void.

## resolveDependence
 * The 'resolveDependence' method MUST inject the dependent test into their parents, and their parents into their dependences for each stored tests. 
 * The 'resolveDependence' method MUST return void.

## getTest
 * The 'getTest' method MUST return the requested TestInfo according with the test name if it's stored.
 * The 'getTest' method MUST return NULL if the requested test is not stored.