# TestInfo Test

The test of TestInfo must validate the following :

## constructor
 * The method name MUST be stored into 'methodName' property
 * The annotations MUST be stored into a MapCollection as annotation name for key and annotation values into a ValueCollection as value.
 * Theannotations  MapCollection MUST be stored into the 'annotations' property.
 * IF no annotations are given, the 'annotations' property MUST be an empty MapCollection.
 * The 'dependence' property MUST contain an empty ValueCollection
 * The 'dependOf' property MUST contain an empty ValueCollection
 * The 'testStatus' MUST be relative to 'IN_QUEUE' by default

## getName
 * The 'getName' method MUST return the value stored in 'methodName' property

## getAnnotation
 * The 'getAnnotation' method MUST return a ValueCollection.
 * The 'getAnnotation' method SHOULD return the annotation ValueCollection corresponding to the given annotation name if it stored.
 * The 'getAnnotation' method SHOULD return an empty ValueCollection if the given annotation name is not stored.

## addDependency
 * The 'addDependency' method MUST add the given TestInfo instance to the 'dependence' property ValueCollection.
 * The 'addDependency' method MUST not return any value

## getDependency
 * The 'getDependency' method MUST return the stored 'dependence' ValueCollection.

## addDepend
 * The 'addDepend' method MUST add the given TestInfo instance to the 'dependOf' property ValueCollection.
 * The 'addDepend' method MUST not return any value

## getDepend
 * The 'getDepend' method MUST return the stored 'dependOf' ValueCollection.

## setStatus
 * The 'setStatus' method MUST store the given value into the 'testStatus' property.
 * The 'setStatus' method MUST not return any value

## getStatus
 * The 'getStatus' method MUST return the value stored into 'testStatus' property.
