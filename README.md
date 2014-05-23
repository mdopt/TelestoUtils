###General-purpose library for php.


The following examples use shorter array syntax from php 5.4+ for brevity, but the library works also for older versions (5.3.2+ to be precise).

####Strings:

```php
use Telesto\Utils\StringUtil;
```


``StringUtil::explode`` (standard explode with escaping delimiter feature)

```php

StringUtil::explode('.', 'first\.dont_explode_this.second', null, ['escapeChar'=> '\\']); 
// ['first.dont_explode_this', 'second']
```

``StringUtil::implode`` (standard implode with escaping delimiter feature)

```php
StringUtil::implode('.', ['first.escape_this_dot', 'second'], ['escapeChar'=> '\\']); 
// 'first\.escape_this_dot.second'
```

``StringUtil::strposAll`` (works like strpos but returns all positions)

```php 
StringUtil::strposAll(' @ @@ @', '@'); 
// [1, 3, 4, 6]
```

``StringUtil::substrConsecutiveCounts``

```php
StringUtil::substrConsecutiveCounts(' @ @@@ @@', '@'); 
// [1, 3, 2]
```

``StringUtil::substrMaxConsecutiveCount``

```php
StringUtil::substrMaxConsecutiveCount(' @ @@@ @@', '@'); 
// 3
```


####Arrays:

```php
use Telesto\Utils\ArrayUtil;
```

Set of array functions that also work for objects implementing ArrayAccess interface.


``ArrayUtil::getKeys`` (works like array_keys, but also supports iterators)

```php
ArrayUtil::getKeys(new \ArrayObject(['x' => 10, 'y' => 20])); 
// ['x', 'y']
```

``ArrayUtil::getElementByKeyPath``

```php
ArrayUtil::getElementByKeyPath(
    [
        'points'    => [
            [
                'x' => 10,
                'y' => 20
            ],
            [
                'x' => 100,
                'y' => 200
            ]
        ]
    ],
    'points.1.y'
);
// 200
```

``ArrayUtil::hasElementAtKeyPath``

```php
ArrayUtil::hasElementAtKeyPath(
    [
        'point' => [
            'x' => 10,
            'y' => 20
        ]
    ],
    'point.x'
);
// true

ArrayUtil::hasElementAtKeyPath(
    [
        'point' => [
            'x' => 10,
            'y' => 20
        ]
    ],
    'point.z'
);
// false
```

``ArrayUtil::setElementByKeyPath``

```php
$array = [
    'point' => [
        'x' => 10,
        'y' => 20
    ]
];

ArrayUtil::setElementByKeyPath($array, 'point.z', 30);

/* $array is now [
    'point' => [
        'x' => 10,
        'y' => 20,
        'z' => 30
    ]
]
*/
```

``ArrayUtil::unsetElementByKeyPath``

```php
$array = [
    'point' => [
        'x' => 10,
        'y' => 20
    ]
];

ArrayUtil::unsetElementByKeyPath($array, 'point.y');

/* $array is now [
    'point' => [
        'x' => 10
    ]
]
*/
```

####Array Operations

``Telesto\Utils\Arrays`` component provides interface for generic operations on arrays and objects implementing ArrayObject interface(some operations have additional requirements).

There are 2 types of operations:
- transformations (they produce output given the input)
- overwrites (they are given 2 arguments and use the first to overwrite the second)

Every transformation can be done by creating a new array and overwriting it(and that's the way it's implemented).

Every transformer is an instance of ``Telesto\Utils\Arrays\Transformation\Transformer`` interface.
Every overwriter is an instance of ``Telesto\Utils\Arrays\Overwriting\Overwriter`` interface.

You can create these instances by yourself. The following example shows how to create and use transformer to perform 'copy by key path map' operation.

```php
use Telesto\Utils\Arrays\Overwriting\Copy\KeyPathMap\BasicOverwriter as KeyPathMapOverwriter;
use Telesto\Utils\Arrays\Transformation\CreateAndOverwriteTransformer;

$transformer = new CreateAndOverwriteTransformer(
    new KeyPathMapOverwriter([
        'points.0.x'    => 'p.x',
        'points.0.y'    => 'p.y'
    ])
);

$output = $transformer->transform([
    'points'    => [
        [
            'x' => 10,
            'y' => 20
        ]
    ]
]);

/*
$output is [
    'p' => [
        'x' => 10,
        'y' => 20
    ]
]
*/
```

But this is requires a lot of boilerplate code. Here's a shorter version that uses OperationFacade:

```php
use Telesto\Utils\Arrays\OperationFacade;

$transformer = OperationFacade::createTransformer('copy.byKeyPathMap', [
    'points.0.x'    => 'p.x',
    'points.0.y'    => 'p.y'
]);

$output = $transformer->transform([
    'points'    => [
        [
            'x' => 10,
            'y' => 20
        ]
    ]
]);
```

or, if you only need to perform one operation and never use the transformer again:

```php
use Telesto\Utils\Arrays\OperationFacade;

$output = OperationFacade::transform(
    [
        'points'    => [
            [
                'x' => 10,
                'y' => 20
            ]
        ]
    ],
    'copy.byKeyPathMap',
    [
        'points.0.x'    => 'p.x',
        'points.0.y'    => 'p.y'
    ]
);
```


License: MIT
