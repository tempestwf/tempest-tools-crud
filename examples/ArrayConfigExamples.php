<?php
$readInfo = [
    'read'=>[
        'query'=>[
            'select'=>[ //Tested in: testBasicRead
                '<keyName>'=>'<string>' //Tested in: testBasicRead
            ],
            'from'=>[ // if not supplied it will be auto generated. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                '<keyName>'=>[ // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'className'=>'<string>', // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'alias'=>'<string>', // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'indexBy'=>'<string>', // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'append'=>'<true or false>' // whether or not to ad an an addition from. Defaults to false // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                ]
            ],
            'where'=>[  //Tested in: testGeneralQueryBuilding
                '<keyName>'=>[ //Tested in: testGeneralQueryBuilding
                    'type'=>'<null, and, or>', //Tested in: testGeneralQueryBuilding
                    'value'=>'<string>' // If an array of: ['expr'=>'<xpr name>', 'arguments'=>['<arguments, could be another xpr array>']] is used, then all parts will be parsed by the array helper, and corresponding xpr methods will be called with the specified arguments. This is true for all parts of the query //Tested in: testGeneralQueryBuilding
                ]
            ],
            'having'=>[ //Tested in: testGeneralQueryBuilding
                '<keyName>'=>[ //Tested in: testGeneralQueryBuilding
                    'type'=>'<null, and, or>', //Tested in: testGeneralQueryBuilding
                    'value'=>'<string>' //Tested in: testGeneralQueryBuilding
                ]
            ],
            'leftJoin'=>[ //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                '<keyName>'=>[ //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'join'=>'<join string>', // When using a queryType of sql use: <from alias>.<name of table to join too>. IE: t.Albums //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'alias'=>'<join alias>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'conditionType'=>'<condition type>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'condition'=>'<condition>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'indexBy'=>'<index by>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                ]
            ],
            'innerJoin'=>[ //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                '<keyName>'=>[ //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'join'=>'<join string>', // When using a queryType of sql use: <from alias>.<name of table to join too>. IE: t.Albums //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'alias'=>'<join alias>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'conditionType'=>'<condition type>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'condition'=>'<condition>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    'indexBy'=>'<index by>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                ]
            ],
            'orderBy'=>[ //Tested in: testGeneralQueryBuilding
                '<keyName>'=>[ //Tested in: testGeneralQueryBuilding
                    'sort'=>'<sort string>', //Tested in: testGeneralQueryBuilding
                    'order'=>'sort order' //Tested in: testGeneralQueryBuilding
                ]
            ],
            'groupBy'=>[ //Tested in: testGeneralQueryBuilding
                '<keyName>'=>'<string>' //Tested in: testGeneralQueryBuilding
            ],
        ],
        'settings'=>[
            'queryType'=>'<dql or sql>', // Defaults to DQL, if SQL is used then Doctrine DBAL query is used instead of an ORM query. Design your syntax accordingly. sql tested in testSqlQueryFunctionality
            'cache'=>[ //Tested in: testGeneralQueryBuilding
                'queryCacheProfile'=>'<a doctrine query cache profile>',// Used only by SQL queries. Use a QueryCacheProfile object. Tested in testSqlQueryFunctionality
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
                '<placeholder name>'=>[ //Tested in: testGeneralQueryBuilding
                    'value'=>'<value>', //Tested in: testGeneralQueryBuilding
                    'type'=>'<param type can be null>' //Tested in: testGeneralQueryBuilding
                ]
            ],
            'fetchJoin'=>'<true or false>', // whether or not when paginating this query requires a fetch join // Tested in: testGeneralDataRetrieval
        ],
        'permissions'=>[
            'settings'=>[
                'closure'=>'<closure to test param, return false from closure to cancel execution>', // Tested in testMutateAndClosure
                'mutate'=>'<closure to modify paramaters passed from front end before applying them>', // Tested in testMutateAndClosure
            ],
            'allowed'=>'<true or false>', // Tested in testReadPermissions
            'maxLimit'=>'<max limit>', // Tested in testGeneralDataRetrieval
            'fixedLimit'=>'<a number that represents that all limits and offsets from the must be divisable by this number>', // Tested in testFixedLimit
            'where'=>[ // Tested in testReadPermissions
                'permissive'=>'<true or false>', // Tested in testReadPermissions
                'fields'=>[ // Tested in testReadPermissions
                    '<field name>'=>[ // Tested in testReadPermissions
                        'permissive'=>'<true or false>', // Tested in testReadPermissions
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>', // Tested in testMutateAndClosure
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>', // Tested in testMutateAndClosure
                        ],
                        'operators'=>[ // Tested in testReadPermissions
                            '<operator name>'=>'<allowed>' // Tested in testReadPermissions
                        ]
                    ]
                ]
            ],
            'having'=>[
                'permissive'=>'<true or false>', // Tested in testReadPermissions2 and testReadPermissions3
                'fields'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                    '<field name>'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                        'permissive'=>'<true or false>', // Tested in testReadPermissions2 and testReadPermissions3
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>', // Tested in testMutateAndClosure
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>', // Tested in testMutateAndClosure and testMutateUsed
                        ],
                        'operators'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                            '<operator name>'=>'<allowed>' // Tested in testReadPermissions2 and testReadPermissions3
                        ]
                    ]
                ]
            ],
            'orderBy'=>[
                'permissive'=>'<true or false>', // Tested in testReadPermissions2 and testReadPermissions3
                'fields'=>[
                    '<field name>'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                        'permissive'=>'<true or false>', // Tested in testReadPermissions2 and testReadPermissions3
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>', // Tested in testMutateAndClosure
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>', // Tested in testMutateAndClosure and testMutateUsed
                        ],
                        'directions'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                            '<ASC or DESC>'=>'<allowed true or false>' // Tested in testReadPermissions2 and testReadPermissions3
                        ]
                    ]
                ]
            ],
            'groupBy'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                'permissive'=>'<true or false>', // Tested in testReadPermissions2 and testReadPermissions3
                'fields'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                    '<field name>'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                        'allowed'=>'<true or false>', // Tested in testReadPermissions2 and testReadPermissions3
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>', // Tested in testMutateAndClosure
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>', // Tested in testMutateAndClosure and testMutateUsed
                        ]
                    ]
                ]
            ],
            'placeholders'=>[
                'permissive'=>'<true or false>', // Tested in testReadPermissions2 and testReadPermissions3
                'placeholderNames'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                    '<name>'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                        'allowed'=>'<true or false>', // Tested in testReadPermissions2 and testReadPermissions3
                        'settings'=>[
                            'closure'=>'<closure to test param, return false from closure to cancel execution>', // Tested in testMutateAndClosure
                            'mutate'=>'<closure to modify paramaters passed from front end before applying them>', // Tested in testMutateAndClosure and testMutateUsed
                        ]
                    ]
                ]
            ],
        ]
    ],
    'create'=>[
        'prePopulateEntities'=>'<true or false>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
    ],
    'update'=>[
        'prePopulateEntities'=>'<true or false>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
    ],
    'delete'=>[
        'prePopulateEntities'=>'<true or false>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
    ]
];

$backendOptions = [ // note all options override query level options
    'options'=>[
        'paginate'=>'<true or false>', // Tested in: testGeneralDataRetrieval
        'fetchJoin'=>'<true or false>', // Optional // Tested in: testGeneralDataRetrieval
        'hydrate'=>'<if false qb or paginator is returned>', // Tested in: testGeneralDataRetrieval
        'hydrationType'=>'doctrine hydration type', // Tested in: testGeneralDataRetrieval
        '<placeholder name>'=>[  // Optional // Tested in: testGeneralDataRetrieval
            'value'=>'<value>',
            'type'=>'<param type can be null>'
        ],
        'queryCacheProfile'=>'<a doctrine query cache profile>', // Optional // Used only by SQL queries. Use a QueryCacheProfile object
        'queryCacheDrive'=>'<driver for query cache>', // Optional //Tested in: testGeneralQueryBuilding
        'resultCacheDrive'=>'<driver for query cache>', // Optional //Tested in: testGeneralQueryBuilding
        'allowCache'=>'<whether or not to allow the query cache, true or false>', // Optional //Tested in: testGeneralQueryBuilding
        'cacheId' => '<result cache id>', // Optional //Tested in: testGeneralQueryBuilding
        'useQueryCache' => '<whether or not to use query cache>', // Optional //Tested in: testGeneralQueryBuilding
        'useResultCache' => '<whether or not to use result cache>', // Optional //Tested in: testGeneralQueryBuilding
        'timeToLive' => '<result cache time to live>',// Optional  //Tested in: testGeneralQueryBuilding
        'tagSet' => '<future feature for tags sets in cache>', // Not yet implemented
        'transaction'=>'<true or false to wrap everythign in a transations>', // Tested in testMultiAddAndChain
        'entitiesShareConfigs'=>'<if true then to optimize the process configs during batches the same config is used for each entity processed, to save reprocessing time>', // Tested in testMultiAddAndChain
        'flush' => '<whether or not to automatically flush>', // Tested in testMultiAddAndChain
        'batchMax' => '<the max we can do in one batch>', // Optional // Tested in testMaxBatch
        'queryMaxParams' => '<the max number of query params that can be passed in to a read request.>', // Optional // Tested in testGeneralDataRetrieval
        'maxLimit' => '<The maxium number of rows that can be returned by a read at once>', // Optional // Tested in testGeneralDataRetrieval
        'prePopulateEntities'=>'<true or false>', // Optional  // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        'clearPrePopulatedEntitiesOnFlush'=>'<true or false>', // whether or not when a flush occurred the pre populated entities should be cleared // Tested in testPrePopulate
        'fixedLimit'=>'<a number that represents that all limits and offsets from the must be divisable by this number>' // Tested in testFixedLimit
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
        'toArray'=>[ // Note when an entity or collection of entities is found
            '<key name>'=>[
                'type'=>'<get, literal>',// Defaults to get. if 'get' then the key is a property but we get it by calling get<Property name>. If 'literal' then a value property must be included, the value property may be a closure that returns a value.  Tested in: testToArrayBasicFunctionality
                'value'=>'<literal value>', // The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                'format'=>'<literal value>', // format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                'allowLazyLoad'=>'<true of false>', // Defaults to false, if true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
            ]
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
            '<custom option name>'=>'<custom option value>', // reserved for custom use cases
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
        'toArray'=>[ // Note when an entity or collection of entities is found
            '<key name>'=>[
                'type'=>'<get, literal>',// Defaults to get. if 'get' then the key is a property but we get it by calling get<Property name>. If 'literal' then a value property must be included, the value property may be a closure that returns a value.  Tested in: testToArrayBasicFunctionality
                'value'=>'<literal value>', // The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                'format'=>'<literal value>', // format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                'allowLazyLoad'=>'<true of false>', // Defaults to false, if true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
            ]
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
            '<custom option name>'=>'<custom option value>', // reserved for custom use cases
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
        'toArray'=>[ // Note when an entity or collection of entities is found
            '<key name>'=>[
                'type'=>'<get, literal>',// Defaults to get. if 'get' then the key is a property but we get it by calling get<Property name>. If 'literal' then a value property must be included, the value property may be a closure that returns a value.  Tested in: testToArrayBasicFunctionality
                'value'=>'<literal value>', // The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                'format'=>'<literal value>', // format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                'allowLazyLoad'=>'<true of false>', // Defaults to false, if true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
            ]
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
            '<custom option name>'=>'<custom option value>', // reserved for custom use cases
        ]
    ],
    'read'=>[
        'toArray'=>[ // Note when an entity or collection of entities is found
            '<key name>'=>[
                'type'=>'<get, literal>',// Defaults to get. if 'get' then the key is a property but we get it by calling get<Property name>. If 'literal' then a value property must be included, the value property may be a closure that returns a value.  Tested in: testToArrayBasicFunctionality
                'value'=>'<literal value>', // The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                'format'=>'<literal value>', // format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                'allowLazyLoad'=>'<true of false>', // Defaults to false, if true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
            ]
        ],
        'options'=>[
            '<custom option name>'=>'<custom option value>', // reserved for custom use cases
        ]
    ]
];


$controllerConfig = [
    '<http request mode>'=> [ // GET, POST, PUT, DELETE. Corresponds to the method that was requested
        'transaction'=>'<true or false>', // whether or not an additional transactions should be started at the controller level. This is useful if you mean to call one more than 1 repo in the controller
        'overrides'=>['<override key and value pairs>'], // overrides passed to the repo
        'transformerSettings'=>['<settings to be passed to the transformer, generally toArray settings>'],
        'resourceIdConversion'=>[ // This lets you set up resource ids passed in the url that are automatically converted to placeholders in the filter from the front end
            '<placeholder as it appearts in the url string>'=>'<query placeholders or null>'// If null then the placeholder will be automatically converted to: <name from url>ResourceId
        ]
    ]
];

$frontEndQuery = [
    'query'=>[
        'where'=>[ //Tested in: testGeneralQueryBuilding
            [
                'field'=>'<fieldName>', //Tested in: testGeneralQueryBuilding
                'type'=>'<and, or>', //Tested in: testGeneralQueryBuilding
                'operator'=>'<operator name>', // make sure here that only the safe ones are even used. If operator is 'andX' or 'orX' then conditions with a nested list of conditions is used instead //Tested in: testGeneralQueryBuilding
                'arguments'=>['<arguments that get passed to that query builder operator>'],  // If operator is 'andX' or 'orX' this is omitted. Conditions appears in instead. //Tested in: testGeneralQueryBuilding
                'conditions'=>['<array of just like any other filter>'] // If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting. //Tested in: testGeneralQueryBuilding
            ]
        ],
        'having'=>[ //Tested in: testGeneralQueryBuilding
            [
                'field'=>'<fieldName>', //Tested in: testGeneralQueryBuilding
                'type'=>'<and, or>', //Tested in: testGeneralQueryBuilding
                'operator'=>'<operator name>', // make sure here that only the safe ones are even used. If operator is 'andX' or 'orX' then conditions with a nested list of conditions is used instead //Tested in: testGeneralQueryBuilding
                'arguments'=>['<arguments that get passed to that query builder operator>'],  // If operator is 'andX' or 'orX' this is omitted. Conditions appears in instead. //Tested in: testGeneralQueryBuilding
                'conditions'=>['<array of just like any other filter>'] // If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting. //Tested in: testGeneralQueryBuilding
            ]
        ],
        'orderBy'=>[ //Tested in: testGeneralQueryBuilding
            '<field name>'=>'<ASC or DESC>' //Tested in: testGeneralQueryBuilding
        ],
        'groupBy'=>[ //Tested in: testGeneralQueryBuilding
            '<field name>' //Tested in: testGeneralQueryBuilding
        ],
        'placeholders'=>[ //Tested in: testGeneralQueryBuilding
            '<placeholder name>'=>[ //Tested in: testGeneralQueryBuilding
                'value'=>'<value>', //Tested in: testGeneralQueryBuilding
                'type'=>'<param type can be null>' //Tested in: testGeneralQueryBuilding
            ]
        ],
    ],
    'options'=>[
        'returnCount'=>'<true or false>', // Tested in: testGeneralDataRetrieval
        'limit'=>'<limit>', //Tested in: testGeneralQueryBuilding
        'offset'=>'<offset>', //Tested in: testGeneralQueryBuilding
        'useGetParams'=>'<true or false>',// whether or not to expect the params to be in get format as illustrated below. Tested in testGeneralQueryBuildingWithGetParams
        'resourceIds'=>['<an array of resource ids passed to the url>'] // This is automatically populated or appended to by the controller based on parameters passed through the url. This is included for custom logic so such as closures to enforce resource ids passed are appropriate for the given route
    ]
];

// From the front end, pass one of the following get param to the index to let it know where to look for your query:
// queryLocation = '<body, singleParam, params>' -- if body they the query was passed as a json in the body of the request, if singleParam it was passed in another get param called 'query' as a json encoded string, if params the query was passed as param syntax listed below
// Tested in: testGeneralQueryBuildingWithGetParams
// Note: A front end option of useGetParams (which triggers processing of the query as get params), is also accepted by the ORM code, but it would not be passed in the format above.
// Note the optional number at the end is so you can have conditions that are are identical in key name, but have different values
// Get param query syntax:
// Where or having: <and|or>_<where|having>_<operator>_<fieldName>_<optional number>=<value>
// Where operator is 'in' or 'between' then use an array for the addition arguments: IE:
// <and|or>_<where|having>_<operator>_<fieldName>_<optional number>[]=value1
// <and|or>_<where|having>_<operator>_<fieldName>_<optional number>[]=value2
// When using an andX or orX operator. Json encode the value using standard syntax described above

// Note in field names always replace the dot (such as t.name) with a dash.

// orderBy: orderBy_<field>=<direction>

// groupBy: groupBy[]=<field name to group by>

// placeholder: placeholder_<name>_<optional type>=<value>

// option: option_<option name>=<value>

// All requests from the front end should have a params and an options:

$exampleFrontEndRequest = [
    'params'=>['<see param examples below>'],
    'options'=>[
        'simplifiedParams'=>'<true or false>', //Defaults to false, may also be set as a different default on the controller // whether or not to process the params as standard version of simplified version. Both param examples are below.
        'testMode'=>'<true or false>', // Defaults to false, if set to true then data will be rolled back instead of committed. This lets you write test cases that use the api but not store anything to the db
        'toArray'=>[
            'completeness'=>'<full, limited, minimal, none>', // Defaults to full, if 'full' then all data will be shown so long as it wouldn't trigger an infinite loop, if 'limited' then all data will be shown but relations leading to already processed entities will not be shown, if 'minimal' the same entity will never be shown twice in the return and an empty array will be in it's place, if 'none' nothing is returned. Tested in: testToArrayBasicFunctionality
            'maxDepth'=>'<number or null>', // how deep should the to array go. Tested in: testToArrayBasicFunctionality
            'excludeKeys'=> ['<list of keys to exclude>'], // Use this to prevent certain keys from being converted to array to trim the return. Tested in: testToArrayBasicFunctionality
            'allowOnlyRequestedParams'=>'<true or false>',// Defaults to true, if true only the params that you requested to be changed on the entity will be shown in the return. This filters out any fields or associations that you did not request to be set directly with this request. Tested in: testToArrayBasicFunctionality
            'forceIncludeKeys'=>['<list of keys to include>'] // Defaults to: ['id'], these are keys to include in the result even if you didn't request to change them. Tested in: testToArrayBasicFunctionality
        ]
    ]
];

$toArrayTransformerSettings = [ // Tested in testToArrayArrayStorage and as part of all transformation tests
    'defaultMode'=>'<create, read, update, or delete>', // Defaults to 'read'. This is the mode that will be initiated on the entity if no mode is currently active on the entity
    'defaultArrayHelper'=>'<array helper>',// If no array helper is set for the entity already this one will be used
    'defaultPath'=>['<path>'],// If no path is set for the entity already this will be used
    'defaultFallBack'=>['<fall back>'], // If no fall back is set for the entity already this will be used
    'force'=>'<true or false>', // Defaults to false. If true then the entity will be force to use the path, mode, fall back and array helper that you are setting as defaults, regardless of if they have there own already.
    'store'=>'<true or false>', //Defaults to true. Whether or not the toArray result should be stored to be used again if toArray is called again.
    'recompute'=>'<true or false>', // Defaults to false. Whether or not to recompute toArray, even if one was previously stored
    'useStored'=>'<true or false>', // Defaults to true. Whether or not to use a previously stored toArray result if one is found. If false then it will return a freshly generated result.
    'frontEndOptions'=>['<options>'], // Options passed from the front end with the request
];
// Complex param versions, these execute more quickly but may be less like what you might expect them to look like.
// Note: When a relation is set to null then an assignType of setNull is used internally which calls the set method with a null value
$createSingleParams = [ // Tested in CudTest.php
    '<fieldName>'=>'<fieldValue>', // Tested in CudTest.php
    '<associationName>'=>[ // A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association. // Tested in CudTest.php
        '<chainType>'=>[ // chainType can be: create, update, delete, read // Tested in CudTest.php
            '<fieldName>'=>'<fieldValue>', // Tested in CudTest.php
            'assignType'=>'<set, add, or remove, or setSingle, addSingle, removeSingle, null (in quotes), setNull>' // any time single is at the end of the assign type, then we strip the s off the end of the assignation name before calling the method. For instance if you have a relation of users, but you have a method of addUser you need use an assignType of addSingle. // Tested in CudTest.php
        ]
    ]
];

$createBatchParams = [ // Tested in CudTest.php
    [
        '<fieldName>'=>'<fieldValue>', // Tested in CudTest.php
        '<associationName>'=>[ // A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association. // Tested in CudTest.php
            '<chainType>'=>[ // chainType can be: create, update, delete, read // Tested in CudTest.php
                '<fieldName>'=>'<fieldValue>', // Tested in CudTest.php
                'assignType'=>'<set, add, or remove, or setSingle, addSingle, removeSingle, null (in quotes), setNull>' // Tested in CudTest.php
            ]
        ]
    ]
];

$singleParams = [ // id will be passed as a separate argument // Tested in CudTest.php
    '<fieldName>'=>'<fieldValue>', // Tested in CudTest.php
    '<associationName>'=>[ // A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association. // Tested in CudTest.php
        '<chainType>'=>[ // chainType can be: create, update, delete, read // Tested in CudTest.php
            '<fieldName>'=>'<fieldValue>', // Tested in CudTest.php
            'assignType'=>'<set, add, or remove, or setSingle, addSingle, removeSingle, null (in quotes), setNull>' // Tested in CudTest.php
        ]
    ]
];

$batchParams = [ // Tested in CudTest.php
    [
        [
            '<id of entity>' => [ // Tested in CudTest.php
                '<fieldName>'=>'<fieldValue>', // Tested in CudTest.php
                '<associationName>'=>[ // A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association. // Tested in CudTest.php
                    '<chainType>'=>[ // chainType can be: create, update, delete, read // Tested in CudTest.php
                        '<fieldName>'=>'<fieldValue>', // Tested in CudTest.php
                        'assignType'=>'<set, add, or remove, or setSingle, addSingle, removeSingle, null (in quotes), setNull>' // Tested in CudTest.php
                    ]
                ]
            ]
        ]
    ]
];

// simplified Param Syntax for Create Update and Delete. To active pass a front end option of 'simplifiedParams'=>true
// Top level examples:
$creates = [ // Note lack of id triggers create
    [
        '<fieldName>'=>'<fieldValue>',
    ]
];

$updates = [ // Id triggers an update when there is another field referenced
    [
        'id'=>'<id of entiy>',
        '<fieldName>'=>'<fieldValue>',
    ]
];

$deletes = [
    [
        'id'=>'<id of entiy>',
    ]
];


// Chaining examples, not that these are all chains from inside a update action

$singleAssociationCreate = [
    [
        'id'=>'<id of entity>',
        '<associationName>'=>[ // Note the lack of the id triggers the create
            '<fieldName>'=>'<fieldValue>',
        ],
    ]
];

$singleAssociationUpdate = [
    [
        'id'=>'<id of entiy>',
        '<associationName>'=>[ // Note the id and the field means it's an update
            'id'=>'<id of entiy>',
            '<fieldName>'=>'<fieldValue>',
        ],
    ]
];

$singleAssociationRead = [
    [
        'id'=>'<id of entiy>',
        '<associationName>'=>[ // Note the just using the id triggers a read and then assigns the entity to the association
            'id'=>'<id of entiy>',
        ],
    ]
];

$singleAssociationDelete = [
    [
        'id'=>'<id of entiy>',
        '<associationName>'=>[ // Note that specifying chain type delete triggers a delete
            'id'=>'<id of entiy>',
            'chainType'=>'delete'
        ],
    ]
];




$multipleAssociationCreate = [
    [
        'id'=>'<id of entity>',
        '<associationName>'=>[
            [ // Note the lack of the id triggers the create. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
                '<fieldName>'=>'<fieldValue>',
            ],
        ]
    ]
];

$multipleAssociationUpdate = [
    [
        'id'=>'<id of entiy>',
        '<associationName>'=>[
            [ // Note the id and the field means it's an update. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
                'id'=>'<id of entiy>',
                '<fieldName>'=>'<fieldValue>',
            ],
        ]
    ]
];

$singleAssociationRead = [
    [
        'id'=>'<id of entiy>',
        '<associationName>'=>[ // Note, just putting an id here, instead of an array will trigger the same behaviour and is simplier still thank this example
            [ // Note the just using the id triggers a read and then assigns the entity to the association. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
                'id'=>'<id of entiy>',
            ],
        ]
    ]
];

$singleAssociationReadSimplierStill = [
    [
        'id'=>'<id of entiy>',
        '<associationName>'=>'<id of entity>' // simplified version of the above example
    ]
];

$singleAssociationDelete = [
    [
        'id'=>'<id of entiy>',
        '<associationName>'=>[
            [ // Note that specifying chain type delete triggers a delete. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
                'id'=>'<id of entiy>',
                'chainType'=>'delete'
            ],
        ]
    ]
];

// Any update, read or delete chain can cause the entity referenced to be removed instead of added to the association by using 'assignType'=>'removeSingle'. You can also use 'remove' if you have a plural in the name of your method on the entity -- IE: removeUser vs removeUsers
$singleAssociationRead = [
    [
        'id'=>'<id of entiy>',
        '<associationName>'=>[ // Note, just putting an id here, instead of an array will trigger the same behaviour and is simplier still thank this example
            [ // Note the just using the id triggers a read and then assigns the entity to the association. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
                'id'=>'<id of entiy>',
                'assignType'=>'removeSingle'
            ],
        ]
    ]
];