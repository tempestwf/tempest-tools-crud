<?php
$readInfo = [
    'read'=>[
        'select'=>[
            '<keyName>'=>'<string>'
        ],
        'where'=>[
            '<keyName>'=>[
                'type'=>'<null, and, or>',
                'value'=>'<string>' // If an array of: ['xpr'=>'<xpr name>', 'arguments'=>['<arguments, could be another xpr array>']] is used, then all parts will be parsed by the array helper, and corresponding xpr methods will be called with the specified arguments. This is true for all parts of the query
            ]
        ],
        'having'=>[
            '<keyName>'=>[
                'type'=>'<null, and, or>',
                'value'=>'<string>'
            ]
        ],
        'orderBy'=>[
            '<keyName>'=>[
                'sort'=>'<sort string>',
                'order'=>'sort order'
            ]
        ],
        'groupBy'=>[
            '<keyName>'=>'<string>'
        ],
        'leftJoin'=>[
            '<keyName>'=>[
                'join'=>'<join string>',
                'alias'=>'<join alias>',
                'conditionType'=>'<condition type>',
                'condition'=>'<condition>',
                'indexBy'=>'<index by>',
            ]
        ],
        'innerJoin'=>[
            '<keyName>'=>[
                'join'=>'<join string>',
                'alias'=>'<join alias>',
                'conditionType'=>'<condition type>',
                'condition'=>'<condition>',
                'indexBy'=>'<index by>',
            ]
        ],
        'cache'=>[
            'useQueryCache'=>'<true or false>',
            'useResultCache'=>'<true or false>',
            'timeToLive'=>'<time to live>',
            'cacheId'=>'<template for cache id, optional>',
            'tagSet'=>[ // Future release
                '<tag set name>'=>[
                    'disjunction'=>'<true or false>',
                    'templates'=>[
                        '<templates used to make tags>'
                    ]
                ]

            ]
        ],
        'placeholders'=>[
            '<placeholder name>'=>[
                'value'=>'<value>',
                'type'=>'<param type can be null>'
            ]
        ],
        'fetchJoin'=>'<true or false>' // whether or not when paginating this query requires a fetch join
    ],
    'permissions'=>[
        'maxLimit'=>'<max limit>',
        'where'=>[
            'permissive'=>'<true or false>',
            'fields'=>[
                '<field name>'=>[
                    'permissive'=>'<true or false>',
                    'closure'=>'<closure to test param, return false from closure to cancel execution>',
                    'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                    'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
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
                    'closure'=>'<closure to test param, return false from closure to cancel execution>',
                    'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                    'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
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
                    'closure'=>'<closure to test param, return false from closure to cancel execution>',
                    'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                    'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
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
                    'closure'=>'<closure to test param, return false from closure to cancel execution>',
                    'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                    'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
                ]
            ]
        ],
        'placeholders'=>[
            'permissive'=>'<true or false>',
            'placeholderNames'=>[
                '<field name>'=>[
                    'allowed'=>'<true or false>',
                    'closure'=>'<closure to test param, return false from closure to cancel execution>',
                    'mutate'=>'<closure to modify paramaters passed from front end before applying them>',
                    'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>',
                ]
            ]
        ],
    ]
];
$frontEndQuery = [
    'query'=>[
        'where'=>[
            [
                'field'=>'<fieldName>',
                'type'=>'<null, and, or>',
                'operator'=>'<operator name>', // make sure here that only the safe ones are even used. If operator is 'andX' or 'orX' then conditions with a nested list of conditions is used instead
                'arguments'=>['<arguments that get passed to that query builder operator>'],  // If operator is 'andX' or 'orX' this is omitted. Conditions appears in instead.
                'conditions'=>['<array of just like any other filter>'] // If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting.
            ]
        ],
        'having'=>[
            [
                'field'=>'<fieldName>',
                'type'=>'<null, and, or>',
                'operator'=>'<operator name>', // make sure here that only the safe ones are even used. If operator is 'andX' or 'orX' then conditions with a nested list of conditions is used instead
                'arguments'=>['<arguments that get passed to that query builder operator>'],  // If operator is 'andX' or 'orX' this is omitted. Conditions appears in instead.
                'conditions'=>['<array of just like any other filter>'] // If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting.
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
            '<placeholder name>'=>[
                'value'=>'<value>',
                'type'=>'<param type can be null>'
            ]
        ],
    ],
    'options'=>[
        'returnCount'=>'<true or false>',
        'limit'=>'<limit>',
        'offset'=>'<offset>',
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

$backendOptions = [
    'options'=>[
        'paginate'=>'<true or false>',
        'hydrate'=>'<if false qb or paginator is returned>',
        'hydrationType'=>'doctrine hydration type',
        '<placeholder name>'=>[
            'value'=>'<value>',
            'type'=>'<param type can be null>'
        ],
        'queryCacheDrive'=>'<driver for query cache>',
        'resultCacheDrive'=>'<driver for query cache>',
        'allowQueryCache'=>'<whether or not to allow the query cache, true or false>',
        'transaction'=>'<true or false to wrap everythign in a transations>',
        'entitiesShareConfigs'=>'<if true then to optimize the process configs during batches the same config is used for each entity processed, to save reprocessing time>',
        'flush' => '<whether or not to automatically flush>',
        'batchMax' => '<the max we can do in one batch>',
        'queryMaxParams' => '<the max number of query params that can be passed in to a read request.>'
    ]
];

$entityInfo = [
    'create'=>[
        'allowed'=>'<true or false>', //Tested in: testAllowedWorks
        'permissive'=>'<true or false>',//Tested in: testPermissiveWorks1
        'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>', //Tested in: testFastMode1
        'validate'=>[ // Tested in: testValidatorWorks
            'fields'=>['<array of fields to validate>'], // if not set the keys from rules will be used instead
            'rules'=>['<rules>'], // Tested in: testValidatorWorks
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested In: testTopLevelSetToAndMutate
        'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested in: testEnforceTopLevelWorks
        'closure'=>'<validation closure>', // Tested in: testTopLevelClosure
        'mutate'=>'<mutate closure>', // Tested In: testTopLevelSetToAndMutate
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>', //Tested in: testPermissiveWorks1 / testPermissiveWorks2
                'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>', //Tested in: testFastMode2AndLowLevelSetTo
                'setTo'=>'<a value to set it to>', //Tested in: testFastMode2AndLowLevelSetTo
                'enforce'=>'<error if not this value, this can be array if used on a relation>', // Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                'closure'=>'<validation closure>', // Tested in testLowLevelClosure
                'mutate'=>'<mutate closure>', // Tested in testLowLevelMutate
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
        'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>', //Tested in: testFastMode1
        'validate'=>[ // Tested in: testValidatorWorks
            'fields'=>['<array of fields to validate>'], // if not set the keys from rules will be used instead
            'rules'=>['<rules>'], // Tested in: testValidatorWorks
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested In: testTopLevelSetToAndMutate
        'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested in: testEnforceTopLevelWorks
        'closure'=>'<validation closure>', // Tested in: testTopLevelClosure
        'mutate'=>'<mutate closure>', // Tested In: testTopLevelSetToAndMutate
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>', //Tested in: testPermissiveWorks1 / testPermissiveWorks2
                'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>', //Tested in: testFastMode2AndLowLevelSetTo
                'setTo'=>'<a value to set it to>', //Tested in: testFastMode2AndLowLevelSetTo
                'enforce'=>'<error if not this value, this can be array if used on a relation>', // Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                'closure'=>'<validation closure>', // Tested in testLowLevelClosure
                'mutate'=>'<mutate closure>', // Tested in testLowLevelMutate
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
        'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>', //Tested in: testFastMode1
        'validate'=>[ // Tested in: testValidatorWorks
            'fields'=>['<array of fields to validate>'], // if not set the keys from rules will be used instead
            'rules'=>['<rules>'], // Tested in: testValidatorWorks
            'messages'=>['<messages>'],
            'customAttributes'=>['<customAttributes>'],
        ],
        'setTo'=>'<array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested In: testTopLevelSetToAndMutate
        'enforce'=>'<array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist>', // Tested in: testEnforceTopLevelWorks
        'closure'=>'<validation closure>', // Tested in: testTopLevelClosure
        'mutate'=>'<mutate closure>', // Tested In: testTopLevelSetToAndMutate
        'fields'=>[
            '<field name>'=>[
                'permissive'=>'<true or false>', //Tested in: testPermissiveWorks1 / testPermissiveWorks2
                'fastMode'=>'<whether or not to bypass all the inline checks and changes to run more quickly>', //Tested in: testFastMode2AndLowLevelSetTo
                'setTo'=>'<a value to set it to>', //Tested in: testFastMode2AndLowLevelSetTo
                'enforce'=>'<error if not this value, this can be array if used on a relation>', // Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                'closure'=>'<validation closure>', // Tested in testLowLevelClosure
                'mutate'=>'<mutate closure>', // Tested in testLowLevelMutate
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