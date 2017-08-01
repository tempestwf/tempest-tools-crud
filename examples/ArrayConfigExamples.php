<?php
$readInfo = [
    'read'=>[
        'select'=>[
            '<keyName>'=>'<string>'
        ],
        'where'=>[
            '<keyName>'=>[
                'type'=>'<null, and, or>',
                'value'=>'<string>' // If an array of: ['xpr'=>'<xpr name>', 'arguments'=>['<arguments, could be another xpr array>']] is used, then all parts will be parsed by the array helper, and corresponding xpr methods will be called with the specified arguments.
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
        'leftJoin'=>[
            '<keyName>'=>'<string>'
        ],
        'innerJoin'=>[
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
        'placeholders'=>[
            '<placeholder name>'=>'<value>'
        ],
        'queryCache'=>'<driver for query cache>',
        'allowQueryCache'=>'<whether or not to allow the query cache, true or false>',
        'transaction'=>'<true or false to wrap everythign in a transations>',
        'entitiesShareConfigs'=>'<if true then to optimize the process configs during batches the same config is used for each entity processed, to save reprocessing time>',
        'flush' => '<whether or not to automatically flush>',
        'batchMax' => '<the max we can do in one batch>',
        'queryMaxParams' => 'the max number of query params that can be passed in to a read request'
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