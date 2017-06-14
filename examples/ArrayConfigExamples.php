<?php
$readInfo = [
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
    ],
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
            'transaction'=>'<true or false to wrap everythign in a transations>'
        ]
    ]

];

$entityInfo = [
    'create'=>[
        'permissive'=>'<true or false>',
        'validator'=>[
            'fields'=>['<array of fields to validate>'],
            'rules'=>['<rules>'],
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'closure'=>'<validation closure>',
        'mutate'=>'<mutate closure>',
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>',
                'setTo'=>'<a value to set it to>',
                'enforce'=>'<error if not this value>',
                'closure'=>'<validation closure>',
                'mutate'=>'<mutate closure>',
                'actions'=>[
                    'set'=>'<true or false>',
                    'add'=>'<true or false>',
                    'remove'=>'<true or false>',
                ],
                'chains'=>[
                    'create'=>'<true or false>',
                    'update'=>'<true or false>',
                    'delete'=>'<true or false>'
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ],
        'batchLimit'=>'<how many can be added at once>'
    ],
    'update'=>[
        'permissive'=>'<true or false>',
        'validator'=>[
            'fields'=>['<array of fields to validate>'],
            'rules'=>['<rules>'],
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'closure'=>'<validation closure>',
        'mutate'=>'<mutate closure>',
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>',
                'setTo'=>'<a value to set it to>',
                'enforce'=>'<error if not this value>',
                'closure'=>'<validation closure>',
                'mutate'=>'<mutate closure>',
                'actions'=>[
                    'set'=>'<true or false>',
                    'add'=>'<true or false>',
                    'remove'=>'<true or false>',
                ],
                'chains'=>[
                    'create'=>'<true or false>',
                    'update'=>'<true or false>',
                    'delete'=>'<true or false>'
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ],
        'batchLimit'=>'<how many can be added at once>'
    ],
    'delete'=>[
        'permissive'=>'<true or false>',
        'validator'=>[
            'fields'=>['<array of fields to validate>'],
            'rules'=>['<rules>'],
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'closure'=>'<validation closure>',
        'mutate'=>'<mutate closure>',
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>',
                'setTo'=>'<a value to set it to>',
                'enforce'=>'<error if not this value>',
                'closure'=>'<validation closure>',
                'mutate'=>'<mutate closure>',
                'actions'=>[
                    'set'=>'<true or false>',
                    'add'=>'<true or false>',
                    'remove'=>'<true or false>',
                ],
                'chains'=>[
                    'create'=>'<true or false>',
                    'update'=>'<true or false>',
                    'delete'=>'<true or false>'
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ],
        'batchLimit'=>'<how many can be added at once>'
    ]
];