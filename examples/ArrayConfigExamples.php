<?php

// Configurations:
// This is returned in the getTTConfig method of a repository.
$repoConfig = [
    '<string>'=>[ // Context keys may be layered as deep as you like. They are part of the contextual config nature of Scribe. See documentation for more details.
        'extends'=>['<array|null>'], // An array of string paths to extend from else where in the array. The values from array at the paths specified will be used as defaults for for the config.
        'read'=>[
            'query'=>[
                'select'=>[ //A list of arbitrary key names select strings to use in the query. with Tested in: testBasicRead
                    '<string>'=>'<string|null>' //Tested in: testBasicRead
                ],
                'from'=>[ // if not supplied it will be auto generated. A list of arbitrary key names and array keys and values to make a from part of the query. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<string>'=>[ // Can be null to disable the block. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'className'=>'<string>', // Class name of the Entity associated with the base table from the query. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', // The alias to use in the from. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string|null>', // indexBy functionality of Doctrine. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'append'=>'<boolean|null>' // Whether or not to ad as an additional from, when you want more than 1 from in the query. Defaults to false // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'where'=>[  // A list of arbitrary key names and strings, or arrays used to make where clauses. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ // Can be null to disable the block.Tested in: testGeneralQueryBuilding
                        'type'=>'<null | and | or>', //If null then it's neither a 'and' or 'or' where clause, or wise it can be set to 'and' or 'or' Tested in: testGeneralQueryBuilding
                        'value'=>'<string|array>' // Either a string used in the where clause or an array.  If an array of: ['expr'=>'<xpr name>', 'arguments'=>['<arguments, could be another xpr array>']] is used, then all parts will be parsed by the array helper, and corresponding xpr methods will be called with the specified arguments. This is true for all parts of the query //Tested in: testGeneralQueryBuilding
                    ]
                ],
                'having'=>[ //Works the same as the where section but applied to a having clause. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ // Can be null to disable the block. Tested in: testGeneralQueryBuilding
                        'type'=>'<null | and | or>', //Tested in: testGeneralQueryBuilding
                        'value'=>'<string|array>' // can be an array expression like with the where. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'leftJoin'=>[ // A list of arbitrary key names arrays with information about joins for the query to make. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<string>'=>[ // Can be null to disable the block. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'join'=>'<string>', // A join part of the query, such as <table alias>.<relationship being joined to>. When using a queryType of sql use: <from alias>.<name of table to join too>. IE: t.Albums //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', // The alias the table will be joined as. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'conditionType'=>'<ON | WITH | null>', // A condition type for the join such as: Expr\Join::WITH. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'condition'=>'<string | null>', //A condition to join on such as x =  x. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string | null>', //Doctrine indexBy functionality. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'innerJoin'=>[ // Works the same as the leftJoin block but goes into innerJoin now. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<string>'=>[ // Can be null to disable the block. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'join'=>'<string>', // When using a queryType of sql use: <from alias>.<name of table to join too>. IE: t.Albums //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'conditionType'=>'<ON | WITH | null>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'condition'=>'<string|null>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string|null>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'orderBy'=>[ // A list of arbitrary key names and array with information for building order bys. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ // Can be null to disable the block. Tested in: testGeneralQueryBuilding
                        'sort'=>'<string|null>', // The fields to be sorted by. Tested in: testGeneralQueryBuilding
                        'order'=>'<ASC|DESC|null>' // The direction to order by. ASC or DESC. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'groupBy'=>[ // A list of arbitrary key names and a string to use to group by. Tested in: testGeneralQueryBuilding
                    '<string>'=>'<string|null>' //The field to group by. Tested in: testGeneralQueryBuilding
                ],
            ],
            'settings'=>[ // Settings related to the query
                'queryType'=>'<dql | sql | null>', // Defaults to DQL, if SQL is used then Doctrine DBAL query is used instead of an ORM query. Design your syntax accordingly. sql tested in testSqlQueryFunctionality
                'cache'=>[ //Settings related to cache. Tested in: testGeneralQueryBuilding
                    'queryCacheProfile'=>'<\Doctrine\DBAL\Cache\QueryCacheProfile|null>',// See Doctrine docs for more information. Used only by SQL queries. Use a QueryCacheProfile object. Tested in testSqlQueryFunctionality
                    'useQueryCache'=>'<boolean|null>', // Whether or not use query cache with the query. Can't be properly determined by a test case
                    'useResultCache'=>'<boolean|null>', // Whether or not to use result cache with the query. Can't be properly determined by a test case
                    'timeToLive'=>'<int|null>', // The time to live of the cache. Tested in: testGeneralQueryBuilding
                    'cacheId'=>'<string|null>', // A cache id to use for the result cache. Tested in: testGeneralQueryBuilding
                    'tagSet'=>[ // Can be null to disable the block. Future release will include this functionality. This will allow result cache to be tagged with provided tags.
                        '<string>'=>[
                            'disjunction'=>'<boolean|null>',
                            'templates'=>[
                                '<string>'
                            ]
                        ]

                    ]
                ],
                'placeholders'=>[ // A list of arbitrary keys and place holders to inject into the query. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ // Can be null to disable the block. Tested in: testGeneralQueryBuilding
                        'value'=>'<mixed>', //The value of the placeholder. Tested in: testGeneralQueryBuilding
                        'type'=>'<PDO::PARAM_* | \Doctrine\DBAL\Types\Type::* constant | null>' // The type of the value. This is optional and while Doctrine supports it it doesn't seem necessaryy or valuable. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'fetchJoin'=>'<boolean|null>', // whether or not when paginating this query requires a fetch join // Tested in: testGeneralDataRetrieval
            ],
            'permissions'=>[ // Permissions information related to the query.
                'settings'=>[ // Can be null to disable the block.
                    'closure'=>'<closure|null>', // A closure to test if the current query can be allowed to proceed. Tested in testMutateAndClosure
                    'mutate'=>'<closure|null>', // A closure to mutate information about the current query before running it. Tested in testMutateAndClosure
                ],
                'allowed'=>'<boolean|null>', // Whether or not queries are allowed. Tested in testReadPermissions
                'maxLimit'=>'<int|null>', // The maximum number of rows that can be returned at once. Tested in testGeneralDataRetrieval
                'fixedLimit'=>'<int|null>', // This can be used to force all returns to have the same number of rows. This is useful when it comes to caching to make sure the same types pagination are requested every time.  Tested in testFixedLimit
                'where'=>[ // Can be null to disable the block. Permissions related to the where part of the query passed from the front end. Tested in testReadPermissions
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions
                    'fields'=>[ // Can be null to disable the block. Permissive related to individual fields. Tested in testReadPermissions
                        '<string>'=>[ // The field name the permissions are related to. Tested in testReadPermissions
                            'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner.. Tested in testReadPermissions
                            'settings'=>[
                                'closure'=>'<closure|null>', // A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure
                            ],
                            'operators'=>[ // Can be null to disable the block. A list of operators that are allowed or disallowed to be used in a query from the front end.  Tested in testReadPermissions
                                '<string>'=>'<boolean|null>' // An operator name and whether or not it's allowed. Tested in testReadPermissions
                            ]
                        ]
                    ]
                ],
                'having'=>[ // Can be null to disable the block. Works the same as where permissions
                    'permissive'=>'<boolean|null>', // Tested in testReadPermissions2 and testReadPermissions3
                    'fields'=>[ // Can be null to disable the block. Tested in testReadPermissions2 and testReadPermissions3
                        '<string>'=>[ // Can be null to disable the block. Tested in testReadPermissions2 and testReadPermissions3
                            'permissive'=>'<boolean|null>', // Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[
                                'closure'=>'<closure|null>', // Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // Tested in testMutateAndClosure and testMutateUsed
                            ],
                            'operators'=>[ // Can be null to disable the block. Tested in testReadPermissions2 and testReadPermissions3
                                '<string>'=>'<boolean|null>' // Tested in testReadPermissions2 and testReadPermissions3
                            ]
                        ]
                    ]
                ],
                'orderBy'=>[ // Can be null to disable the block. Permissions for order by requests from front end.
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'fields'=>[ // Can be null to disable the block.
                        '<string>'=>[ // Can be null to disable the block. The field name the permissions relate to. Tested in testReadPermissions2 and testReadPermissions3
                            'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[
                                'closure'=>'<closure|null>', // A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ],
                            'directions'=>[ // A list of directions that are allowed or not allowed. Tested in testReadPermissions2 and testReadPermissions3
                                '<ASC | DESC>'=>'<boolean|null>' // Whether or not the direction is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            ]
                        ]
                    ]
                ],
                'groupBy'=>[ // Can be null to disable the block. Permissions for group by requests from front end. Tested in testReadPermissions2 and testReadPermissions3
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'fields'=>[ // Can be null to disable the block. Tested in testReadPermissions2 and testReadPermissions3
                        '<string>'=>[ // Can be null to disable the block. The field name the permissions relate to. Tested in testReadPermissions2 and testReadPermissions3
                            'allowed'=>'<boolean|null>', // Whether or group by for this field is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[
                                'closure'=>'<closure|null>', // A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ]
                        ]
                    ]
                ],
                'placeholders'=>[ // Can be null to disable the block. Permissions for placeholders requests from front end.
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'placeholderNames'=>[ // Can be null to disable the block. Tested in testReadPermissions2 and testReadPermissions3
                        '<string>'=>[ // The name of the placeholder passed from the front end that permission relates too. Tested in testReadPermissions2 and testReadPermissions3
                            'allowed'=>'<boolean|null>', // Whether or not use of this placeholder by the front end is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[
                                'closure'=>'<closure|null>', // A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // A closure to mutate information related to the placeholder before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ]
                        ]
                    ]
                ],
            ]
        ],
        'create'=>[ // Can be null to disable the block.
            'prePopulateEntities'=>'<boolean|null>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ],
        'update'=>[ // Can be null to disable the block.
            'prePopulateEntities'=>'<boolean|null>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ],
        'delete'=>[ // Can be null to disable the block.
            'prePopulateEntities'=>'<boolean|null>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ]
    ]
];

// Back end options are passed to a repository when create, read, update, delete methods.
$backendOptionsForRepo = [ // note all options override query level options
    'options'=>[
        'paginate'=>'<boolean|null>', // Defaults to true. Whether or not paginate the results of a query. Tested in: testGeneralDataRetrieval
        'fetchJoin'=>'<boolean|null>', // Whether or not to use a fetch join on a paginated query result. Optional // Tested in: testGeneralDataRetrieval
        'hydrate'=>'<boolean|null>', // Defaults to true. Whether or not to hydrate the results of a query. If false then the query object is returned instead. Tested in: testGeneralDataRetrieval
        'hydrationType'=>'<Doctrine\ORM\Query::* constant|null>', // Defaults to: Query::HYDRATE_ARRAY. The hydration type for result sets. Tested in: testGeneralDataRetrieval
        '<string>'=>[  // Can be null to disable the block. Keys of placeholders to inject into queries. Optional // Tested in: testGeneralDataRetrieval
            'value'=>'<mixed>', // The value of the placeholder
            'type'=>'<PDO::PARAM_* | \Doctrine\DBAL\Types\Type::* constant | null>' // Optional type of the placeholders.
        ],
        'queryCacheProfile'=>'<\Doctrine\DBAL\Cache\QueryCacheProfile|null>', // See Doctrine docs for more details. Optional // Used only by SQL queries. Use a QueryCacheProfile object
        'queryCacheDrive'=>'<\Doctrine\Common\Cache\Cache|null>', // Query cache driver, see Doctrine docs for more details. Optional //Tested in: testGeneralQueryBuilding
        'resultCacheDrive'=>'<\Doctrine\Common\Cache\Cache|null>', // Result cache driver, see Doctrine docs for more details. Optional //Tested in: testGeneralQueryBuilding
        'allowCache'=>'<boolean|null>', // Whether or not to allow cache. Optional //Tested in: testGeneralQueryBuilding
        'cacheId' => '<string|null>', // An optional cache id to use for the result cache. Optional //Tested in: testGeneralQueryBuilding
        'useQueryCache' => '<boolean|null>', // Whether ot not to use query cache. Optional //Tested in: testGeneralQueryBuilding
        'useResultCache' => '<boolean|null>', // Whether or not to use result cache. Optional //Tested in: testGeneralQueryBuilding
        'timeToLive' => '<int|null>',// Optional  // Time to live of the cache. Tested in: testGeneralQueryBuilding
        'tagSet' => '<array|null>', // In a future version you can pass in cache tags to assign to the result cache. Not yet implemented
        'transaction'=>'<boolean|null>', // Defaults to true. Whether ot not to process the database interactions as part of a transaction that will roll back on failure. Tested in testMultiAddAndChain
        'entitiesShareConfigs'=>'<boolean|null>', // If turned on like entities will share configs, mildly speeding up execution times. Tested in testMultiAddAndChain
        'flush' => '<boolean|null>', // Whether or not to flush to the db at the end of call to Create, Read, Update, or Delete. Tested in testMultiAddAndChain
        'batchMax' => '<int|null>', // The maximum number of arrays that can be passed to a Create, Update or Delete method. Used for stopping requests that are to large. Optional // Tested in testMaxBatch
        'queryMaxParams' => '<int|null>', // The maximum number of query params that can be passed to a Read method. Used for stopping requests that are to large. Optional // Tested in testGeneralDataRetrieval
        'maxLimit' => '<int|null>', // The maximum number of rows that can be returned at a time. Optional // Tested in testGeneralDataRetrieval
        'prePopulateEntities'=>'<boolean|null>', // Optional  // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        'clearPrePopulatedEntitiesOnFlush'=>'<boolean|null>', // whether or not when a flush occurred the pre populated entities should be cleared // Tested in testPrePopulate
        'fixedLimit'=>'<boolean|null>'  // This can be used to force all returns to have the same number of rows. This is useful when it comes to caching to make sure the same types pagination are requested every time. Tested in testFixedLimit
    ]
];

// This is returned in the getTTConfig method of a entity.
$entityConfig = [
    '<string>'=>[ // Context keys may be layered as deep as you like. They are part of the contextual config nature of Scribe. See documentation for more details.
        '<create | update | delete>'=>[ // Can be null to disable the block. Configs work the same for create, update and delete
            'extends'=>['<array|null>'], // An array of string paths to extend from else where in the array. The values from array at the paths specified will be used as defaults for for the config.
            'allowed'=>'<boolean|null>', // Whether ot not this type of operation is permitted. Tested in: testAllowedWorks
            'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner.Tested in: testPermissiveWorks1
            'settings'=>[ // Can be null to disable the block. Settings related to the type of operation.
                'setTo'=>'<array|null>', // array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist. Tested In: testTopLevelSetToAndMutate
                'enforce'=>'<array|null>', // array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist. Tested in: testEnforceTopLevelWorks
                'closure'=>'<closure|null>', // A closure to test if the operation should go ahead. Tested in: testTopLevelClosure
                'mutate'=>'<closure|null>', // A closure to mutate the entity of prepersist. Tested In: testTopLevelSetToAndMutate
                'validate'=>[ // Can be null to disable the block. Validation settings passed a validator used on the class. Tested in: testValidatorWorks
                    'fields'=>[ // Can be null to disable the block.
                        '<string>'
                    ], // if not set the keys from rules will be used instead. By default this works like the fields passed to a Laravel validator.
                    'rules'=>[
                        '<string>'=>'<string|null>' // Validator rules. By default these are rules that are used by laravel validator. The keys are the field names the rules apply to.
                    ], // Tested in: testValidatorWorks
                    'messages'=>[ // Can be null to disable the block.
                        '<string>'=>'<string|null>'
                    ], // Custom validator messages to return on value. By default works with Laravel validator functionality.
                    'customAttributes'=>[ // Can be null to disable the block.
                        '<string>'=>'<string|null>'
                    ], // Custom attributes to pass to the validator. By default works with Laravel validator functionality.
                ],
            ],
            'toArray'=>[ // Can be null to disable the block. Settings that dictate how an entity is converted to array.
                '<string>'=>[ // Can be null to disable the block. Putting in an empty array will just assume get type. Key names that will be returned in the resulting array. If type is get this key equates to a field name on the entity.
                    'type'=>'<get | literal | null>',// Defaults to get. if 'get' then the key is a property but we get it by calling get<Property name>. If 'literal' then a value property must be included, the value property may be a closure that returns a value.  Tested in: testToArrayBasicFunctionality
                    'value'=>'<mixed|null>', // The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                    'format'=>'<string|null>', // format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                    'allowLazyLoad'=>'<boolean|null>', // Defaults to false, if true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
                ]
            ],
            'fields'=>[ // Can be null to disable the block.
                '<string>'=>[ // Can be null to disable the block. Field name that the permissions here relate to.
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in: testPermissiveWorks1 / testPermissiveWorks2
                    'settings'=>[ // Can be null to disable the block.
                        'setTo'=>'<mixed|null>', // A value to set the field to. Tested in: testFastMode2AndLowLevelSetTo
                        'enforce'=>'<mixed|null>', // On a field then we test to make sure the field value matches. On an association then this value should be an array that compares the values to the properties being set on the association. Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                        'closure'=>'<closure|null>', // A closure that tests if the operation should be able to continue. Tested in testLowLevelClosure
                        'mutate'=>'<closure|null>', // A closure that mutates the values of the field before being committed to the DB. Tested in testLowLevelMutate
                    ],
                    'assign'=>[ // Types of assignment operations that allowed or disallowed. Note: all combinations of assign type as not tested, but there component parts are tested and shown to work.
                        'set'=>'<boolean|null>', // set<fieldname> method allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'add'=>'<boolean|null>', // add<fieldname> method allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'remove'=>'<boolean|null>', // remove<fieldname> method allowed or not. Tested in: testChainRemove
                        'setSingle'=>'<boolean|null>', // set<fieldname>. Works like set but changes pluralized field names to singular. method allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'addSingle'=>'<boolean|null>', // add<fieldname>. Works like add but changes pluralized field names to singular.Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'removeSingle'=>'<boolean|null>', // remove<fieldname>. Works like remove but changes pluralized field names to singular.Tested in: testChainRemove
                        'null'=>'<boolean|null>', // null assign types tell the system not to assign the entity that is manipulated. This is useful when an entity is already associated with another entity, but you want to interact with that entity any way. Tested in: testNullAssignType. Whether or not having no assign type is allowed.
                        'setNull'=>'<boolean|null>' // This assign type sets a field to null.
                    ],
                    'chain'=>[ // Types of chaining that are allow or not
                        'create'=>'<boolean|null>', // Create type chaining allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'update'=>'<boolean|null>', // Update type chaining allowed or not. Tested in: testUpdateWithChainAndEvents
                        'delete'=>'<boolean|null>', // Delete type chaining allowed or not. Tested in: testMultiDeleteAndEvents
                        'read'=>'<boolean|null>' // Read type chaining allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    ]
                ]
            ],
            'options'=>[ // Can be null to disable the block.
                '<string>'=>'<mixed|null>', // reserved for custom use cases
            ]
        ],
        'read'=>[
            'toArray'=>[ // Can be null to disable the block. Settings that dictate how an entity is converted to array.
                '<string>'=>[ // Can be null to disable the block. Key names that will be returned in the resulting array. If type is get this key equates to a field name on the entity.
                    'type'=>'<get | literal | null>',// Defaults to get. if 'get' then the key is a property but we get it by calling get<Property name>. If 'literal' then a value property must be included, the value property may be a closure that returns a value.  Tested in: testToArrayBasicFunctionality
                    'value'=>'<mixed|null>', // The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                    'format'=>'<string|null>', // format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                    'allowLazyLoad'=>'<boolean|null>', // Defaults to false, if true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
                ]
            ],
            'options'=>[ // Can be null to disable the block.
                '<string>'=>'<mixed|null>', // reserved for custom use cases
            ]
        ]
    ]
];


$controllerConfig = [
    '<string>'=>[ // Context keys may be layered as deep as you like. They are part of the contextual config nature of Scribe. See documentation for more details.
        '<GET | POST | PUT | DELETE | null>'=> [ // GET, POST, PUT, DELETE. Corresponds to the method that was requested
            'extends'=>['<array|null>'], // An array of string paths to extend from else where in the array. The values from array at the paths specified will be used as defaults for for the config.
            'transaction'=>'<boolean|null>', // Whether or not an additional transactions should be started at the controller level. This is useful if you mean to call one more than 1 repo in the controller using events.
            'overrides'=>['<array|null>'], // Overrides passed to the repo that will override the default options set on the repo.
            'transformerSettings'=>[ // Can be null to disable the block. Settings to be passed to the transformer, generally toArray settings. Tested in testToArrayArrayStorage and as part of all transformation tests
                'defaultMode'=>'<create | read | update | delete | null>', // Defaults to 'read'. This is the mode that will be initiated on the entity if no mode is currently active on the entity being transformed
                'defaultArrayHelper'=>'<\TempestTools\Common\Contracts\ArrayHelperContract | null>',// If no array helper is set for the entity already this one will be used.
                'defaultPath'=>['<array|null>'],// A contextual config path. If no path is set for the entity already this will be used
                'defaultFallBack'=>['array|null>'], // A contextual config path. If no fall back is set for the entity already this will be used
                'force'=>'<boolean|null>', // Defaults to false. If true then the entity will be forced to use the path, mode, fall back and array helper that you are setting as defaults, regardless of if the entity has there own already.
                'store'=>'<boolean|null>', //Defaults to true. Whether or not the toArray result should be stored to be used again if toArray is called again.
                'recompute'=>'<boolean|null>', // Defaults to false. Whether or not to recompute toArray, even if one was previously stored.
                'useStored'=>'<boolean|null>', // Defaults to true. Whether or not to use a previously stored toArray result if one is found. If false then it will return a freshly generated result.
                'frontEndOptions'=>['<array|null>'], // Options passed from the front end with the request, you may set these on the controller as defaults if you like but they will be overridden by data passed from the front end.
            ],
            'resourceIdConversion'=>[ // This lets you set up resource ids passed in the url that are automatically converted to placeholders in the filter from the front end. This is useful for filtering a query semi automatically based on the resource ids in the url path.
                '<string>'=>'<string|null>'// Placeholder as it appear in the url string.  If null then the placeholder will not be generated, other wise it will be converted to a placeholder with the name of value
            ]
        ]
    ]
];

// GET requests from the front end:
// The following is a guide to filters you can pass from the front end to filter the query that is run by scribe.
// From the front end, pass one of the following get params to let Scribe know where to look for your query:
// queryLocation = '<body | singleParam | params>' -- if 'body' they the query was passed as a json in the body of the request, if 'singleParam' it was passed in another get param called 'query' as a json encoded string, if 'params' the query was passed as param syntax listed further down. By default 'params' is used, because it's the most standards complaint.
// Tested in: testGeneralQueryBuildingWithGetParams

// This would be passed to a get request to an end point as json encoded string if either: <url>?queryLocation=body or <url>?queryLocation=singleParam&query=<json>
$frontEndQueryToGet = [
    'query'=>[
        'where'=>[ // Optional block. In this block filters can be set that will be applied to the where clause of the query. Tested in: testGeneralQueryBuilding
            [
                'field'=>'<string>', //Field name with the alias of the table the field is on, such as t.id. Tested in: testGeneralQueryBuilding
                'type'=>'<and|or>', // Is it a an 'and' or an 'or' criteria. Tested in: testGeneralQueryBuilding
                'operator'=>'<string>', // An expression name that will be used to generate the operator in the make sure here that only the safe ones are even used. If operator is 'andX' or 'orX' then conditions with a nested list of conditions is used instead. By default the following operators are allowed: 'andX', 'orX', 'eq', 'neq', 'lt', 'lte', 'gt', 'gte', 'in', 'notIn', 'isNull', 'isNotNull', 'like', 'notLike', 'between'. Tested in: testGeneralQueryBuilding
                'arguments'=>['<mixed>'],  // Arguments to pass to the expression. If operator is 'andX' or 'orX' this is omitted. Conditions are used in instead. Generally just 1 argument is needed, for between operators use 2 arguments, and for in and notIn operators use a nested array of values -- 'arguments'=>[['<string>', '<string>']]. //Tested in: testGeneralQueryBuilding
                'conditions'=>['<array>'] // Contains an array of additional criteria. This nested array has the exact same structure as the block above it in this example. If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting. //Tested in: testGeneralQueryBuilding
            ]
        ],
        'having'=>[ // Optional block. Works the same as the where block but applied to criteria to the having clause of the query. Tested in: testGeneralQueryBuilding
            [
                'field'=>'<string>', //Field name with the alias of the table the field is on, such as t.id. Tested in: testGeneralQueryBuilding
                'type'=>'<and|or>', // Is it a an 'and' or an 'or' criteria. Tested in: testGeneralQueryBuilding
                'operator'=>'<string>', // An expression name that will be used to generate the operator in the make sure here that only the safe ones are even used. If operator is 'andX' or 'orX' then conditions with a nested list of conditions is used instead. By default the following operators are allowed: 'andX', 'orX', 'eq', 'neq', 'lt', 'lte', 'gt', 'gte', 'in', 'notIn', 'isNull', 'isNotNull', 'like', 'notLike', 'between'. Tested in: testGeneralQueryBuilding
                'arguments'=>['<mixed>'],  // Arguments to pass to the expression. If operator is 'andX' or 'orX' this is omitted. Conditions are used in instead. Generally just 1 argument is needed, for between operators use 2 arguments, and for in and notIn operators use a nested array of values -- 'arguments'=>[['<string>', '<string>']]. //Tested in: testGeneralQueryBuilding
                'conditions'=>['<array>'] // Contains an array of additional criteria. This nested array has the exact same structure as the block above it in this example. If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting. //Tested in: testGeneralQueryBuilding
            ]
        ],
        'orderBy'=>[ // Optional block. This block will add criteria to the order by clause of the query. Tested in: testGeneralQueryBuilding
            '<string>'=>'<ASC | DESC>' // The key is the field name (including the table alias -- such as t.id) and the value is the direction of the order by. Tested in: testGeneralQueryBuilding
        ],
        'groupBy'=>[ // Optional block. Tested in: testGeneralQueryBuilding
            '<string>' // Each value is the a field name including the table alias -- such as t.id) to group by. Should only be used in queries that inherently have an aggregate in the select. Tested in: testGeneralQueryBuilding
        ],
        'placeholders'=>[ // Optional block. This blocks lets the front end pass in values for placeholders which the back end developer has added to the base query. Tested in: testGeneralQueryBuilding
            '<string>'=>[ //The key is a placeholder name that is waiting to be used in the query. Tested in: testGeneralQueryBuilding
                'value'=>'<mixed>', //Value to put in the placeholder. Tested in: testGeneralQueryBuilding
                'type'=>'<PDO::PARAM_* | \Doctrine\DBAL\Types\Type::* constant | null>' // Optional type of the placeholders. Tested in: testGeneralQueryBuilding
            ]
        ],
    ],
    'options'=>[ // Optional block. This block lets you pass in options related to the query. You may add your own keys and values for your own implementations as well. These options will be passed to the repository actions that are called and may be referenced by your custom event listeners and closures.
        'returnCount'=>'<boolean|null>', // Whether or not the count should be returned from and index action. Defaults to true. Tested in: testGeneralDataRetrieval
        'limit'=>'<int|null>', // The limit to apply to the query (max number of rows that will be returned). Defaults to 100. Tested in: testGeneralQueryBuilding
        'offset'=>'<int|null>', // The offset for the query. If omitted index actions will return data starting at the first available row. Tested in: testGeneralQueryBuilding
        'useGetParams'=>'<boolean|null>',// Whether or not to expect the params to be in discrete get params format as illustrated below. Defaults to true. You would not pass this option directly from the front end -- it will instead be detected from if you called the url like so: <url>?queryLocation=params, or if you omitted the queryLocation all together.  Tested in testGeneralQueryBuildingWithGetParams
        'resourceIds'=>[ // Optional block.
            '<string>'=>'<string>' // The key is the name of the resource id (as it would appear in the construction of the route in laravel such as: users/{user}. The value is the placeholder name will be converted to. Placeholders created in this way will be added to the query as parameters for placeholders which the back end developer has put in the query to receive them.
        ] // This is automatically populated or appended to by the controller based on parameters passed through the url. The ability for the front end to specify resourceIds is included for use with custom logic.
    ]
];

// Example json:

/*{
    "query": {
        "where": [
            {
                "field": "t.name",
                "type": "and",
                "operator": "eq",
                "arguments": ["BEETHOVEN7"]
            }
        ]
	},
	"options": {
        "returnCount": true,
		"limit": 1,
		"offset": 1
	}
}*/

// A front end option of useGetParams (which triggers processing of the query as get params), is also accepted by the ORM code, but it would not be passed in the format above, instead it triggers either automatically or if the url was called as: <url>?queryLocation=params.
// The following details how to pass your filter and options from the front end as discrete params instead of as a json encoded string.


// Get param query syntax:
// Where or having: <and|or>_<where|having>_<string>_<string>_<?int>=<mixed>
// The first string is the operator name', and the second string is the 'field name'.
// Where operator is 'in' or 'between' then use an array for the addition arguments: IE:
// <and|or>_<where|having>_<string>_<string>_<?int>[]=value1
// <and|or>_<where|having>_<string>_<string>_<?int>[]=value2
// Example: and_where_eq_t-name=bob
// Example: and_where_eq_t-name_2=rob

// Example: and_where_in_t-name[]=bob
// Example: and_where_in_t-name[]=rob

// Example: and_where_between_t-id[]=1
// Example: and_where_between_t-id[]=2

// The optional number at the end is so you can have conditions that are are identical in key name, but have different values
// In field names always replace the dot (such as t.name) with a dash.

// When using an andX or orX operator. Json encode the value using standard syntax described above

// Example: and_where_andX=[[{"field":"t.name","operator":"eq","arguments":["BEETHOVEN"]},{"field":"t.name","operator":"neq","arguments":["BACH"]}]]


// orderBy: orderBy_<string>=<ASC|DESC>. The first string is the field name with the table alias (such as t-id), the value is the direction to order by.

// Example: orderBy_t-name=ASC,

// groupBy: groupBy[]=<string>. The strings are the field names with the table alias (such as t-id).

// Example: groupBy[]=t-name

// placeholder: placeholder_<string>_<?string>=<mixed>. The first string is the placeholder name. The second string is an optional placeholder type.

// Example: placeholder_myPlaceholder1=1
// Example: placeholder_myPlaceholder2_integer=2

// option: option_<string>=<mixed>. The string is an option name, the value is what ever you would like to set that option too.

// Example: option_returnCount=1
// Example: option_limit=1
// Example: option_offset=1

// None get requests:

// All none get requests will include params (described below) and may include an options block. The structure will look like this.
$exampleFrontEndNonGetRequest = [
    'params'=>['<array>'], // See below for examples for different types of param sets that can be passed
    'options'=>[ // Optional block. This block relates to the options for the request.
        'simplifiedParams'=>'<boolean|null>', //Defaults to false, may also be set as a different default on the controller. This value is whether or not to process the params as standard params or simplified params. Both param examples are below.
        'testMode'=>'<boolean|null>', // Defaults to false, if set to true then data will be rolled back instead of committed. This lets you write test cases that use the api but not store anything to the db
        'toArray'=>[ // Optional block. This blocks relates to the data that should be returned -- specifying how the entities will be transformed to an array.
            'completeness'=>'<full | limited | minimal | none | null>', // Defaults to full, if 'full' then all data will be shown so long as it wouldn't trigger an infinite loop (relations between entities are omitted as soon as they loop back on them selves), if 'limited' then all data will be shown but relations leading to already processed entities will not be shown, if 'minimal' the same entity will never be shown twice in the return and an empty array will be in it's place, if 'none' nothing is returned. Tested in: testToArrayBasicFunctionality
            'maxDepth'=>'<int|null>', // How deep should the to array go. You can use this option to prevent unnecessary levels of depth in your return. Tested in: testToArrayBasicFunctionality
            'excludeKeys'=> ['<string>'], // Each string is the name of a field or association that should be returned in the resulting array from the back end. Use this to prevent certain keys from being converted to array to trim the return. Tested in: testToArrayBasicFunctionality
            'allowOnlyRequestedParams'=>'<boolean|null>',// Defaults to true, if true only the params that you requested to be changed on the effect entities will be shown in the return. This filters out any fields or associations that you did not request to be changed directly with this request. Tested in: testToArrayBasicFunctionality
            'forceIncludeKeys'=>['<string>'] // Defaults to: ['id'], these are keys to include in the result even if you didn't request to change them. Tested in: testToArrayBasicFunctionality
        ]
    ]
];


// Complex param syntax:
// Complex types of params execute more quickly but are less pretty to look at and write.
// Note: Create requests are structures a little differently than update, or delete requests.
// Note: When a relation is set to null then an assignType of setNull is used internally which calls the set method with a null value
$singleParamForField = [ // You may have as many field names and values as you like in your request, one is shown in the example for illustration. Tested in CudTest.php
    '<string>'=>'<mixed>', // If a field name is specified as a key then the value will be the value that the field will be set to. Tested in CudTest.php
];

$singleParamForAssociation = [ //You may have as many field names and values as you like in your request, one is shown in the example for illustration. Tested in CudTest.php
    '<string>'=>[ // The key is the association name. A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association. // Tested in CudTest.php
        '<create | read | update | delete>'=>[ // This key is the chainType. chainType can be: create, update, delete, read. This triggers another entity to be created, retrieved and manipulated to be added to this association. Tested in CudTest.php
            '<string|int|null>'=>[ // If you are chaining with a create chain type, omit the key. For another other chain type the key references the id of the entity to be retrieved from the database and manipulated.
                '<string>'=>'<mixed>', // This is a reference to another field or association. Tested in CudTest.php
                'assignType'=>'<set|add|remove|setSingle|addSingle|removeSingle|null(in quotes)|setNull|null>' // This is the assignment type that the entity will assigned to the association using. It corresponds to the method on the entity that will be called (IE: set<string> where string is the field name to set, such as setName). If null set will be used. Any time single is at the end of the assign type, then we strip the s off the end of the association name before calling the method. For instance if you have an association of users, but you have a method of addUser you need use an assignType of addSingle. // Tested in CudTest.php
            ]
        ]
    ]
];

// Note: to request a batch of creates be created at once, pass an array of create parameters instead of just params for a single entity.
$createBatchParams = [ // Tested in CudTest.php
    [
        '<string>'=>'<mixed>',
    ]
];

//TODO: Put in examples

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