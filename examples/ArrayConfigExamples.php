<?php
$readInfo = [
    'batchMax' => '<the max we can do in one batch>',
    'batchMaxIncludesChains' => '<whether or not to include chains when caculating batch max>',
    'batchMaxIncludesAssigns' => '<whether or not to include assigns when caculating batch max>',
    'query'=>[
        'select'=>[
            '<keyName>'=>'<string>'
        ],
        'where'=>[
            '<keyName>'=>[
                'type'=>'<null, and, or>',
                'value'=>'<string>'
            ]
        ],
        'having'=>[
            '<keyName>'=>[
                'type'=>'<null, and, or>',
                'value'=>'<string>'
            ]
        ],
        'orderBy'=>[
            '<keyName>'=>'<string>'
        ],
        'groupBy'=>[
            '<keyName>'=>'<string>'
        ],
        'cache'=>[
            'useCache'=>'<true or false>',
            'timeToLive'=>'<time to live>',
            'cacheId'=>'<template for cache id, optional>',
            'tagSet'=>[
                '<tag set name>'=>[
                    'disjunction'=>'<true or false>',
                    'templates'=>[
                        '<templates used to make tags>'
                    ]
                ]

            ]
        ],
    ],
    'permissions'=>[
        'maxLimit'=>'<max limit>',
        'limitAllowed'=>'<use page instead if false>',
        'where'=>[
            'permissive'=>'<true or false>',
            'fields'=>[
                '<field name>'=>[
                    'permissive'=>'<true or false>',
                    'closure'=>'closure',
                    'operators'=>[
                        '<operator name>'=>'<allowed>'
                    ]
                ]
            ]
        ],
        'having'=>[
            'permissive'=>'<true or false>',
            'fields'=>[
                '<field name>'=>[
                    'permissive'=>'<true or false>',
                    'closure'=>'closure',
                    'operators'=>[
                        '<operator name>'=>'<allowed>'
                    ]
                ]
            ]
        ],
        'orderBy'=>[
            'permissive'=>'<true or false>',
            'fields'=>[
                '<field name>'=>[
                    'permissive'=>'<true or false>',
                    'closure'=>'closure',
                    'directions'=>[
                        '<operator name>'=>'<allowed>'
                    ]
                ]
            ]
        ],
        'groupBy'=>[
            'permissive'=>'<true or false>',
            'fields'=>[
                '<field name>'=>[
                    'permissive'=>'<true or false>',
                    'closure'=>'closure'
                ]
            ]
        ],
        'placeholders'=>[
            'permissive'=>'<true or false>',
            'placeholderNames'=>[
                '<field name>'=>[
                    'allowed'=>'<true or false>',
                    'closure'=>'closure'
                ]
            ]
        ],
    ]
];
$fromMasterAray = [
    'frontend'=>[
        'query'=>[
            'where'=>[
                '<field name>'=>[
                    'type'=>'<null, and, or>',
                    'operator'=>'<operator name>', // make sure here that only the safe ones are even used
                    'arguments'=>['<arguments that get passed to that query builder operator>']
                ]
            ],
            'having'=>[
                '<field name>'=>[
                    'type'=>'<null, and, or>',
                    'operator'=>'<operator name>', // make sure here that only the safe ones are even used
                    'arguments'=>['<arguments that get passed to that query builder operator>']
                ]
            ],
            'orderBy'=>[
                '<field name>'=>[
                    'direction'=>'<ASC or DESC>'
                ]
            ],
            'groupBy'=>[
                '<field name>'
            ],
            'placeholders'=>[
                '<placeholder name>'=>'<value>'
            ],
        ],
        'options'=>[
            'returnCount'=>'<true or false>',
            'resultsPerPage'=>'<resutls per page>',
            'limit'=>'<limit>',
            'offset'=>'<offset>',
            'page'=>'<used instead of limit and offset>'
        ]
    ],
    'backend'=>[
        'options'=>[
            'paginate'=>'<true or false>',
            'hydrate'=>'<if false qb or paginator is returned>',
            'hydrationType'=>'doctrine hydration type',
            'placeholders'=>[
                '<placeholder name>'=>'<value>'
            ],
            'queryCache'=>'<driver for query cache>',
            'allowQueryCache'=>'<whether or not to allow the query cache, true or false>',
            'transaction'=>'<true or false to wrap everythign in a transations>',
            'entitiesShareConfigs'=>'<if true then to optimize the process configs during batches the same config is used for each entity processed, to save reprocessing time>',
            'flush' => '<whether or not to automatically flush>'
        ]
    ]

];

$entityInfo = [
    'create'=>[
        'permissive'=>'<true or false>',
        'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
        'validator'=>[
            'fields'=>['<array of fields to validate>'],
            'rules'=>['<rules>'],
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>',
        'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>',
        'closure'=>'<validation closure>',
        'mutate'=>'<mutate closure>',
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>',
                'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
                'setTo'=>'<a value to set it to>',
                'enforce'=>'<error if not this value, this can be array if used on a relation>',
                'closure'=>'<validation closure>',
                'mutate'=>'<mutate closure>',
                'assign'=>[
                    'set'=>'<true or false>',
                    'add'=>'<true or false>',
                    'remove'=>'<true or false>',
                ],
                'chain'=>[
                    'create'=>'<true or false>',
                    'update'=>'<true or false>',
                    'delete'=>'<true or false>'
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ]
    ],
    'update'=>[
        'permissive'=>'<true or false>',
        'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
        'validator'=>[
            'fields'=>['<array of fields to validate>'],
            'rules'=>['<rules>'],
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>',
        'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>',
        'closure'=>'<validation closure>',
        'mutate'=>'<mutate closure>',
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>',
                'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
                'setTo'=>'<a value to set it to>',
                'enforce'=>'<error if not this value, this can be array if used on a relation>',
                'closure'=>'<validation closure>',
                'mutate'=>'<mutate closure>',
                'assign'=>[
                    'set'=>'<true or false>',
                    'add'=>'<true or false>',
                    'remove'=>'<true or false>',
                ],
                'chain'=>[
                    'create'=>'<true or false>',
                    'update'=>'<true or false>',
                    'delete'=>'<true or false>'
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ]
    ],
    'delete'=>[
        'permissive'=>'<true or false>',
        'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
        'validator'=>[
            'fields'=>['<array of fields to validate>'],
            'rules'=>['<rules>'],
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>',
        'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>',
        'closure'=>'<validation closure>',
        'mutate'=>'<mutate closure>',
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>',
                'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
                'setTo'=>'<a value to set it to>',
                'enforce'=>'<error if not this value, this can be array if used on a relation>',
                'closure'=>'<validation closure>',
                'mutate'=>'<mutate closure>',
                'assign'=>[
                    'set'=>'<true or false>',
                    'add'=>'<true or false>',
                    'remove'=>'<true or false>',
                ],
                'chain'=>[
                    'create'=>'<true or false>',
                    'update'=>'<true or false>',
                    'delete'=>'<true or false>'
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ]
    ]
];