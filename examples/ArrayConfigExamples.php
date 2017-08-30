<?php
$readInfo = [
    'read'=>[
        'query'=>[
            'select'=>[ //Tested in: testBasicRead
                '<keyName>'=>'<string>'
            ],
            'from'=>[ // if not supplied it will be auto generated. Tested in: testGeneralQueryBuilding
                '<keyName>'=>[
                    'className'=>'<string>',
                    'alias'=>'<string>',
                    'indexBy'=>'<string>',
                    'append'=>'<true or false>' // whether or not to ad an an addition from. Defaults to false
                ]
            ],
            'where'=>[  //Tested in: testGeneralQueryBuilding
                '<keyName>'=>[
                    'type'=>'<null, and, or>',
                    'value'=>'<string>' // If an array of: ['expr'=>'<xpr name>', 'arguments'=>['<arguments, could be another xpr array>']] is used, then all parts will be parsed by the array helper, and corresponding xpr methods will be called with the specified arguments. This is true for all parts of the query
                ]
            ],
            'having'=>[ //Tested in: testGeneralQueryBuilding
                '<keyName>'=>[
                    'type'=>'<null, and, or>',
                    'value'=>'<string>'
                ]
            ],
            'leftJoin'=>[ //Tested in: testGeneralQueryBuilding
                '<keyName>'=>[
                    'join'=>'<join string>',
                    'alias'=>'<join alias>',
                    'conditionType'=>'<condition type>',
                    'condition'=>'<condition>',
                    'indexBy'=>'<index by>',
                ]
            ],
            'innerJoin'=>[ //Tested in: testGeneralQueryBuilding
                '<keyName>'=>[
                    'join'=>'<join string>',
                    'alias'=>'<join alias>',
                    'conditionType'=>'<condition type>',
                    'condition'=>'<condition>',
                    'indexBy'=>'<index by>',
                ]
            ],
            'orderBy'=>[ //Tested in: testGeneralQueryBuilding
                '<keyName>'=>[
                    'sort'=>'<sort string>',
                    'order'=>'sort order'
                ]
            ],
            'groupBy'=>[ //Tested in: testGeneralQueryBuilding
                '<keyName>'=>'<string>'
            ],
        ],
        'settings'=>[
            'queryType'=>'<dql or sql>', // Defaults to DQL, if SQL is used then Doctrine DBAL query is used instead of an ORM query. Design your syntax accordingly.
            'cache'=>[ //Tested in: testGeneralQueryBuilding
                'useQueryCache'=>'<true or false>', // Can't be properly determined by a test case
                'useResultCache'=>'<true or false>', // Can't be properly determined by a test case
                'timeToLive'=>'<time to live>', //Tested in: testGeneralQueryBuilding
                'cacheId'=>'<template for cache id, optional>', //Tested in: testGeneralQueryBuilding
                'tagSet'=>[ // Future release
                    '<tag set name>'=>[
                        'disjunction'=>'<true or false>',
                        'templates'=>[
                            '<templates used to make tags>'
                        ]
                    ]

                ]
            ],
            'placeholders'=>[ //Tested in: testGeneralQueryBuilding
                '<placeholder name>'=>[
                    'value'=>'<value>',
                    'type'=>'<param type can be null>'
                ]
            ],
            'fetchJoin'=>'<true or false>', // whether or not when paginating this query requires a fetch join
        ],
        'permissions'=>[
            'allowed'=>'<true or false>',
            'maxLimit'=>'<max limit>',
            'where'=>[
                'permissive'=>'<true or false>',
                'fields'=>[
                    '<field name>'=>[
                        'permissive'=>'<true or false>',
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>',
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                        ],
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
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>',
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                        ],
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
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>',
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                        ],
                        'directions'=>[
                            '<operator name>'=>'<allowed true or false>'
                        ]
                    ]
                ]
            ],
            'groupBy'=>[
                'permissive'=>'<true or false>',
                'fields'=>[
                    '<field name>'=>[
                        'allowed'=>'<true or false>',
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>',
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                        ]
                    ]
                ]
            ],
            'placeholders'=>[
                'permissive'=>'<true or false>',
                'placeholderNames'=>[
                    '<field name>'=>[
                        'allowed'=>'<true or false>',
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>',
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                        ]
                    ]
                ]
            ],
        ]
    ],


];
$frontEndQuery = [
    'query'=>[
        'where'=>[ //Tested in: testGeneralQueryBuilding
            [
                'field'=>'<fieldName>',
                'type'=>'<and, or>',
                'operator'=>'<operator name>', // make sure here that only the safe ones are even used. If operator is 'andX' or 'orX' then conditions with a nested list of conditions is used instead
                'arguments'=>['<arguments that get passed to that query builder operator>'],  // If operator is 'andX' or 'orX' this is omitted. Conditions appears in instead.
                'conditions'=>['<array of just like any other filter>'] // If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting.
            ]
        ],
        'having'=>[ //Tested in: testGeneralQueryBuilding
            [
                'field'=>'<fieldName>',
                'type'=>'<and, or>',
                'operator'=>'<operator name>', // make sure here that only the safe ones are even used. If operator is 'andX' or 'orX' then conditions with a nested list of conditions is used instead
                'arguments'=>['<arguments that get passed to that query builder operator>'],  // If operator is 'andX' or 'orX' this is omitted. Conditions appears in instead.
                'conditions'=>['<array of just like any other filter>'] // If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting.
            ]
        ],
        'orderBy'=>[ //Tested in: testGeneralQueryBuilding
            '<field name>'=>'<ASC or DESC>'
        ],
        'groupBy'=>[ //Tested in: testGeneralQueryBuilding
            '<field name>'
        ],
        'placeholders'=>[ //Tested in: testGeneralQueryBuilding
            '<placeholder name>'=>[
                'value'=>'<value>',
                'type'=>'<param type can be null>'
            ]
        ],
    ],
    'options'=>[
        'returnCount'=>'<true or false>',
        'limit'=>'<limit>', //Tested in: testGeneralQueryBuilding
        'offset'=>'<offset>', //Tested in: testGeneralQueryBuilding
    ]
];

$createSingleParams = [
    '<fieldName>'=>'<fieldValue>',
    '<associationName>'=>[ // A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association.
        '<chainType>'=>[ // chainType can be: create, update, delete, read
            '<fieldName>'=>'<fieldValue>',
            'assignType'=>'<set, add, or remove, or setSingle, addSingle, removeSingle>' // any time single is at the end of the assign type, then we strip the s off the end of the assignation name before calling the method. For instance if you have a relation of users, but you have a method of addUser you need use an assignType of addSingle.
        ]
    ]
];

$createBatchParams = [
    [
        '<fieldName>'=>'<fieldValue>',
        '<associationName>'=>[ // A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association.
            '<chainType>'=>[ // chainType can be: create, update, delete, read
                '<fieldName>'=>'<fieldValue>',
                'assignType'=>'<set, add, or remove, or setSingle, addSingle, removeSingle>'
            ]
        ]
    ]
];

$singleParams = [ // id will be passed as a separate argument
    '<fieldName>'=>'<fieldValue>',
    '<associationName>'=>[ // A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association.
        '<chainType>'=>[ // chainType can be: create, update, delete, read
            '<fieldName>'=>'<fieldValue>',
            'assignType'=>'<set, add, or remove, or setSingle, addSingle, removeSingle>'
        ]
    ]
];

$batchParams = [
    [
        [
            '<id of entity>' => [
                '<fieldName>'=>'<fieldValue>',
                '<associationName>'=>[ // A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association.
                    '<chainType>'=>[ // chainType can be: create, update, delete, read
                        '<fieldName>'=>'<fieldValue>',
                        'assignType'=>'<set, add, or remove, or setSingle, addSingle, removeSingle>'
                    ]
                ]
            ]
        ]
    ]
];

$backendOptions = [ // not all cache options override query level cache options
    'options'=>[
        'paginate'=>'<true or false>', // Tested in: testGeneralDataRetrieval
        'hydrate'=>'<if false qb or paginator is returned>', // Tested in: testGeneralDataRetrieval
        'hydrationType'=>'doctrine hydration type', // Tested in: testGeneralDataRetrieval
        '<placeholder name>'=>[ // Tested in: testGeneralDataRetrieval
            'value'=>'<value>',
            'type'=>'<param type can be null>'
        ],
        'queryCacheDrive'=>'<driver for query cache>', //Tested in: testGeneralQueryBuilding
        'resultCacheDrive'=>'<driver for query cache>', //Tested in: testGeneralQueryBuilding
        'allowQueryCache'=>'<whether or not to allow the query cache, true or false>', //Tested in: testGeneralQueryBuilding
        'cacheId' => '<result cache id>', //Tested in: testGeneralQueryBuilding
        'useQueryCache' => '<whether or not to use query cache>', //Tested in: testGeneralQueryBuilding
        'useResultCache' => '<whether or not to use result cache>', //Tested in: testGeneralQueryBuilding
        'timeToLive' => '<result cache time to live>', //Tested in: testGeneralQueryBuilding
        'tagSet' => '<future feature for tags sets in cache>',
        'transaction'=>'<true or false to wrap everythign in a transations>', // Tested in testMultiAddAndChain
        'entitiesShareConfigs'=>'<if true then to optimize the process configs during batches the same config is used for each entity processed, to save reprocessing time>', // Tested in testMultiAddAndChain
        'flush' => '<whether or not to automatically flush>', // Tested in testMultiAddAndChain
        'batchMax' => '<the max we can do in one batch>', // Tested in testMaxBatch
        'queryMaxParams' => '<the max number of query params that can be passed in to a read request.>'
    ]
];

$entityInfo = [
    'create'=>[
        'allowed'=>'<true or false>', //Tested in: testAllowedWorks
        'permissive'=>'<true or false>',//Tested in: testPermissiveWorks1
        'settings'=>[
            'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested In: testTopLevelSetToAndMutate
            'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested in: testEnforceTopLevelWorks
            'closure'=>'<validation closure>', // Tested in: testTopLevelClosure
            'mutate'=>'<mutate closure>', // Tested In: testTopLevelSetToAndMutate
            'validate'=>[ // Tested in: testValidatorWorks
                'fields'=>['<array of fields to validate>'], // if not set the keys from rules will be used instead
                'rules'=>['<rules>'], // Tested in: testValidatorWorks
                'messages'=>['<messages>'],
                'customAttributes'=>['<customAttributes>'],
            ],
        ],
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>', //Tested in: testPermissiveWorks1 / testPermissiveWorks2
                'settings'=>[
                    'setTo'=>'<a value to set it to>', //Tested in: testFastMode2AndLowLevelSetTo
                    'enforce'=>'<error if not this value, this can be array if used on a relation>', // Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                    'closure'=>'<validation closure>', // Tested in testLowLevelClosure
                    'mutate'=>'<mutate closure>', // Tested in testLowLevelMutate
                ],
                'assign'=>[ // Note: all combinations of assign type as not tested, but there component parts are tested and shown to work.
                    'set'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'add'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'remove'=>'<true or false>',//Tested in: testChainRemove
                    'setSingle'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'addSingle'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'removeSingle'=>'<true or false>', //Tested in: testChainRemove
                    'null'=>'<true or false>' // Tested in: testNullAssignType. Whether or not having no assign type is allowed
                ],
                'chain'=>[
                    'create'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'update'=>'<true or false>', //Tested in: testUpdateWithChainAndEvents
                    'delete'=>'<true or false>', //Tested in: testMultiDeleteAndEvents
                    'read'=>'<true or false>' //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ]
    ],
    'update'=>[
        'allowed'=>'<true or false>', //Tested in: testAllowedWorks
        'permissive'=>'<true or false>',//Tested in: testPermissiveWorks1
        'validate'=>[ // Tested in: testValidatorWorks
            'fields'=>['<array of fields to validate>'], // if not set the keys from rules will be used instead
            'rules'=>['<rules>'], // Tested in: testValidatorWorks
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'settings'=>[
            'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested In: testTopLevelSetToAndMutate
            'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested in: testEnforceTopLevelWorks
            'closure'=>'<validation closure>', // Tested in: testTopLevelClosure
            'mutate'=>'<mutate closure>', // Tested In: testTopLevelSetToAndMutate
        ],
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>', //Tested in: testPermissiveWorks1 / testPermissiveWorks2
                'settings'=>[
                    'setTo'=>'<a value to set it to>', //Tested in: testFastMode2AndLowLevelSetTo
                    'enforce'=>'<error if not this value, this can be array if used on a relation>', // Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                    'closure'=>'<validation closure>', // Tested in testLowLevelClosure
                    'mutate'=>'<mutate closure>', // Tested in testLowLevelMutate
                ],
                'assign'=>[ // Note: all combinations of assign type as not tested, but there component parts are tested and shown to work.
                    'set'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'add'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'remove'=>'<true or false>',//Tested in: testChainRemove
                    'setSingle'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'addSingle'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'removeSingle'=>'<true or false>', //Tested in: testChainRemove
                    'null'=>'<true or false>' // Tested in: testNullAssignType. Whether or not having no assign type is allowed
                ],
                'chain'=>[
                    'create'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'update'=>'<true or false>', //Tested in: testUpdateWithChainAndEvents
                    'delete'=>'<true or false>', //Tested in: testMultiDeleteAndEvents
                    'read'=>'<true or false>' //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ]
    ],
    'delete'=>[
        'allowed'=>'<true or false>', //Tested in: testAllowedWorks
        'permissive'=>'<true or false>',//Tested in: testPermissiveWorks1
        'validate'=>[ // Tested in: testValidatorWorks
            'fields'=>['<array of fields to validate>'], // if not set the keys from rules will be used instead
            'rules'=>['<rules>'], // Tested in: testValidatorWorks
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'settings'=>[
            'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested In: testTopLevelSetToAndMutate
            'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested in: testEnforceTopLevelWorks
            'closure'=>'<validation closure>', // Tested in: testTopLevelClosure
            'mutate'=>'<mutate closure>', // Tested In: testTopLevelSetToAndMutate
        ],
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>', //Tested in: testPermissiveWorks1 / testPermissiveWorks2
                'settings'=>[
                    'setTo'=>'<a value to set it to>', //Tested in: testFastMode2AndLowLevelSetTo
                    'enforce'=>'<error if not this value, this can be array if used on a relation>', // Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                    'closure'=>'<validation closure>', // Tested in testLowLevelClosure
                    'mutate'=>'<mutate closure>', // Tested in testLowLevelMutate
                ],
                'assign'=>[ // Note: all combinations of assign type as not tested, but there component parts are tested and shown to work.
                    'set'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'add'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'remove'=>'<true or false>',//Tested in: testChainRemove
                    'setSingle'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'addSingle'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'removeSingle'=>'<true or false>', //Tested in: testChainRemove
                    'null'=>'<true or false>' // Tested in: testNullAssignType. Whether or not having no assign type is allowed
                ],
                'chain'=>[
                    'create'=>'<true or false>', //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    'update'=>'<true or false>', //Tested in: testUpdateWithChainAndEvents
                    'delete'=>'<true or false>', //Tested in: testMultiDeleteAndEvents
                    'read'=>'<true or false>' //Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                ]
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>',
        ]
    ]
];