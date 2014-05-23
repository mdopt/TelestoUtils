General-purpose library for php.


The following examples use shorter array syntax from php 5.4+ for brevity, but the library works also for older versions (5.3.2+ to be precise).

Strings:

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


Arrays:

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




License: MIT
