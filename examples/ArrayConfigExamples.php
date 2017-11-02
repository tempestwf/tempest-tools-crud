<?php

// Configurations:
// This is returned in the getTTConfig method of a repository.
$repoConfig = [
    '<string>'=>[ // Context keys may be layered as deep as you like. They are part of the contextual config nature of Scribe. See documentation for more details.
        'extends'=>['<array|null>'], // Optional. An array of string paths to extend from else where in the array. The values from array at the paths specified will be used as defaults for for the config.
        'read'=>[ // Optional.
            'query'=>[ // Optional.
                'select'=>[ // Optional. A list of arbitrary key names with select strings to use in the query. Tested in: testBasicRead
                    '<string>'=>'<string|null>' //Tested in: testBasicRead
                ],
                'from'=>[ // Optional. if not supplied it will be auto generated. A list of arbitrary key names and array keys and values to make a from part of the query. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<string>'=>[ // Optional. Can be null to disable the block. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'className'=>'<string>', // Class name of the Entity associated with the base table from the query. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', // The alias to use in the from. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string|null>', // Optional. indexBy functionality of Doctrine. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'append'=>'<boolean|null>' // Defaults to false. Whether or not to ad as an additional from, when you want more than 1 from in the query.  // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'where'=>[  // Optional. A list of arbitrary key names and strings, or arrays used to make where clauses. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ // Can be null to disable the block. Tested in: testGeneralQueryBuilding
                        'type'=>'<"and" | "or" | null>', // If null then it's neither a 'and' or 'or' where clause, other wise it can be set to 'and' or 'or' Tested in: testGeneralQueryBuilding
                        'value'=>'<string|array>' // Either a string used in the where clause or an array.  If an array of: ['expr'=>'<xpr name>', 'arguments'=>['<arguments, could be another xpr array>']] is used, then all parts will be parsed by the array helper, and corresponding xpr methods will be called with the specified arguments. This is true for all parts of the query //Tested in: testGeneralQueryBuilding
                    ]
                ],
                'having'=>[ // Optional. Works the same as the where section but applied to a having clause. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ // Optional. Can be null to disable the block. Tested in: testGeneralQueryBuilding
                        'type'=>'<"and" | "or" | null>', //Tested in: testGeneralQueryBuilding
                        'value'=>'<string|array>' // Can be an array expression like with the where. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'leftJoin'=>[ // Optional. A list of arbitrary key names and arrays with information about joins for the query. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<string>'=>[ // Optional. Can be null to disable the block. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'join'=>'<string>', // A join part of the query, such as <table alias>.<relationship being joined to>. When using a queryType of sql use: <from alias>.<name of table to join too>. IE: t.Albums //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', // The alias the table will be joined as. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'conditionType'=>'<"ON" | "WITH" | null>', // A condition type for the join such as: Expr\Join::WITH. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'condition'=>'<string | null>', // A condition to join on such as x = x. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string | null>', // Optional. Doctrine indexBy functionality. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'innerJoin'=>[ // Optional. Works the same as the leftJoin block but goes into innerJoin. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<string>'=>[ // Optional. Can be null to disable the block. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'join'=>'<string>', // A join part of the query, such as <table alias>.<relationship being joined to>. When using a queryType of sql use: <from alias>.<name of table to join too>. IE: t.Albums //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', // The alias the table will be joined as. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'conditionType'=>'<"ON" | "WITH" | null>', // A condition type for the join such as: Expr\Join::WITH. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'condition'=>'<string | null>', // A condition to join on such as x = x. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string | null>', // Optional. Doctrine indexBy functionality. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'orderBy'=>[ // Optional. A list of arbitrary key names and arrays with information for building order bys. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ // Optional. Can be null to disable the block. Tested in: testGeneralQueryBuilding
                        'sort'=>'<string|null>', // The field to be sorted by. Tested in: testGeneralQueryBuilding
                        'order'=>'<"ASC"|"DESC"|null>' // The direction to order by. ASC or DESC. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'groupBy'=>[ // Optional. A list of arbitrary key names and a string to used to group by. Tested in: testGeneralQueryBuilding
                    '<string>'=>'<string|null>' // The field to group by. Tested in: testGeneralQueryBuilding
                ],
            ],
            'settings'=>[ // Optional. Settings related to the query
                'queryType'=>'<"dql" | "sql" | null>', // Defaults to dql, if sql is used then Doctrine DBAL query is used instead of an ORM query. Design your syntax accordingly. sql tested in testSqlQueryFunctionality
                'cache'=>[ // Optional. Settings related to cache. Tested in: testGeneralQueryBuilding
                    'queryCacheProfile'=>'<\Doctrine\DBAL\Cache\QueryCacheProfile|null>',// Optional. See Doctrine docs for more information. Used only by SQL queries.  Tested in testSqlQueryFunctionality
                    'useQueryCache'=>'<boolean|null>', // Defaults to true. Whether or not use query cache with the query. Can't be properly determined by a test case
                    'useResultCache'=>'<boolean|null>', // Defaults to false. Whether or not to use result cache with the query. Can't be properly determined by a test case
                    'timeToLive'=>'<int|null>', // Optional. The time to live of the cache. Tested in: testGeneralQueryBuilding
                    'cacheId'=>'<string|null>', // Optional. A cache id to use for the result cache. Tested in: testGeneralQueryBuilding
                    'tagSet'=>[ // Optional. Can be null to disable the block. Future release will include this functionality. This will allow result cache to be tagged with provided tags.
                        '<string>'=>[
                            'disjunction'=>'<boolean|null>',
                            'templates'=>[
                                '<string>'
                            ]
                        ]

                    ]
                ],
                'placeholders'=>[ // Optional. A list of arbitrary keys and place holders to inject into the query. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ // Optional. Can be null to disable the block. Tested in: testGeneralQueryBuilding
                        'value'=>'<mixed>', //The value of the placeholder. Tested in: testGeneralQueryBuilding
                        'type'=>'<PDO::PARAM_* | \Doctrine\DBAL\Types\Type::* constant | null>' // Optional. The type of the value. This is optional and while Doctrine supports it it doesn't seem necessary or valuable. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'fetchJoin'=>'<boolean|null>', // Defaults to true. Whether or not when paginating this query requires a fetch join // Tested in: testGeneralDataRetrieval
            ],
            'permissions'=>[ // Optional. Permissions information related to the query.
                'settings'=>[ // Optional. Can be null to disable the block.
                    'closure'=>'<closure|null>', // Optional. A closure to test if the current query can be allowed to proceed. Tested in testMutateAndClosure
                    'mutate'=>'<closure|null>', // Optional. A closure to mutate information about the current query before running it. Tested in testMutateAndClosure
                ],
                'allowed'=>'<boolean|null>', // Default to true. Whether or not queries are allowed. Tested in testReadPermissions
                'maxLimit'=>'<int|null>', // Defaults to 100. The maximum number of rows that can be returned at once. Tested in testGeneralDataRetrieval
                'fixedLimit'=>'<int|null>', // Defaults to false. This can be used to force all returns to have the same number of rows. This is useful when it comes to caching to make sure the same types of pagination are requested every time.  Tested in testFixedLimit
                'where'=>[ // Optional. Can be null to disable the block. Permissions related to the where part of the query passed from the front end. Tested in testReadPermissions
                    'permissive'=>'<boolean|null>', // Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions
                    'fields'=>[ // Optional. Can be null to disable the block. Permissions related to individual fields. Tested in testReadPermissions
                        '<string>'=>[ // Optional. Can be null to disable the block. The field name the permissions are related to. Tested in testReadPermissions
                            'permissive'=>'<boolean|null>', //Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner.. Tested in testReadPermissions
                            'settings'=>[ // Optional.
                                'closure'=>'<closure|null>', // Optional. A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // Optional. A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure
                            ],
                            'operators'=>[ // Optional. Can be null to disable the block. A list of operators that are allowed or disallowed to be used in a filter from the front end.  Tested in testReadPermissions
                                '<string>'=>'<boolean|null>' // Defaults to permissive setting. An operator name and whether or not it's allowed. Tested in testReadPermissions
                            ]
                        ]
                    ]
                ],
                'having'=>[ // Optional. Can be null to disable the block. Works the same as where permissions
                    'permissive'=>'<boolean|null>', // Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions
                    'fields'=>[ // Optional. Can be null to disable the block. Permissions related to individual fields. Tested in testReadPermissions
                        '<string>'=>[ // Optional. Can be null to disable the block. The field name the permissions are related to. Tested in testReadPermissions
                            'permissive'=>'<boolean|null>', //Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner.. Tested in testReadPermissions
                            'settings'=>[ // Optional.
                                'closure'=>'<closure|null>', // Optional. A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // Optional. A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure
                            ],
                            'operators'=>[ // Optional. Can be null to disable the block. A list of operators that are allowed or disallowed to be used in a filter from the front end.  Tested in testReadPermissions
                                '<string>'=>'<boolean|null>' // Defaults to permissive setting. An operator name and whether or not it's allowed. Tested in testReadPermissions
                            ]
                        ]
                    ]
                ],
                'orderBy'=>[ // Optional. Can be null to disable the block. Permissions for order by requests from front end.
                    'permissive'=>'<boolean|null>', // Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'fields'=>[ // Optional. Can be null to disable the block.
                        '<string>'=>[ // Optional. Can be null to disable the block. The field name the permissions relate to. Tested in testReadPermissions2 and testReadPermissions3
                            'permissive'=>'<boolean|null>', // Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[ // Optional.
                                'closure'=>'<closure|null>', // Optional. A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // Optional. A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ],
                            'directions'=>[ // Optional. A list of directions that are allowed or not allowed. Tested in testReadPermissions2 and testReadPermissions3
                                '<"ASC" | "DESC">'=>'<boolean|null>' // Defaults to permissive setting. Whether or not the direction is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            ]
                        ]
                    ]
                ],
                'groupBy'=>[ // Optional. Can be null to disable the block. Permissions for group by requests from front end. Tested in testReadPermissions2 and testReadPermissions3
                    'permissive'=>'<boolean|null>', // Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'fields'=>[ // Optional. Can be null to disable the block. Tested in testReadPermissions2 and testReadPermissions3
                        '<string>'=>[ // Optional. Can be null to disable the block. The field name the permissions relate to. Tested in testReadPermissions2 and testReadPermissions3
                            'allowed'=>'<boolean|null>', // Defaults to permissive setting. Whether or group by for this field is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[ // Optional.
                                'closure'=>'<closure|null>', // Optional. A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // Optional. A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ]
                        ]
                    ]
                ],
                'placeholders'=>[ // Optional. Can be null to disable the block. Permissions for placeholders requests from front end.
                    'permissive'=>'<boolean|null>', // Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'placeholderNames'=>[ // Optional. Tested in testReadPermissions2 and testReadPermissions3
                        '<string>'=>[ // Optional. Can be null to disable the block. The name of the placeholder passed from the front end that permission relates too. Tested in testReadPermissions2 and testReadPermissions3
                            'allowed'=>'<boolean|null>', // Defaults to permissive setting. Whether or not use of this placeholder by the front end is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[ // Optional.
                                'closure'=>'<closure|null>', // Optional. A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure|null>', // Optional. A closure to mutate information related to the placeholder before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ]
                        ]
                    ]
                ],
            ]
        ],
        'create'=>[ // Optional. Can be null to disable the block.
            'prePopulateEntities'=>'<boolean|null>' // Defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ],
        'update'=>[ // Optional. Can be null to disable the block.
            'prePopulateEntities'=>'<boolean|null>' // Defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ],
        'delete'=>[ // Optional. Can be null to disable the block.
            'prePopulateEntities'=>'<boolean|null>' // Defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ]
    ]
];

// Backend options are passed to a repository when create, read, update, delete methods are called.
// Note: all options override query level options with the same names
$backendOptionsForRepo = [
    'options'=>[
        'paginate'=>'<boolean|null>', // Defaults to true. Whether or not paginate the results of a query. Tested in: testGeneralDataRetrieval
        'fetchJoin'=>'<boolean|null>', // Defaults to true. Whether or not to use a fetch join on a paginated query result. Tested in: testGeneralDataRetrieval
        'hydrate'=>'<boolean|null>', // Defaults to true. Whether or not to hydrate the results of a query. If false then the query object and info about the query is returned instead by the repo. Tested in: testGeneralDataRetrieval
        'hydrationType'=>'<Doctrine\ORM\Query::* constant|null>', // Defaults to: Query::HYDRATE_ARRAY. The hydration type for result sets. Tested in: testGeneralDataRetrieval
        '<string>'=>[  // Optional. Can be null to disable the block. Keys of placeholders to inject into queries. Tested in: testGeneralDataRetrieval
            'value'=>'<mixed>', // The value of the placeholder
            'type'=>'<PDO::PARAM_* | \Doctrine\DBAL\Types\Type::* constant | null>' // Optional. type of the placeholders.
        ],
        'queryCacheProfile'=>'<\Doctrine\DBAL\Cache\QueryCacheProfile|null>', // Optional. See Doctrine docs for more details.  Used only by SQL queries. Use a QueryCacheProfile object
        'queryCacheDrive'=>'<\Doctrine\Common\Cache\Cache|null>', // Optional. Query cache driver, see Doctrine docs for more details. Tested in: testGeneralQueryBuilding
        'resultCacheDrive'=>'<\Doctrine\Common\Cache\Cache|null>', // Optional. Result cache driver, see Doctrine docs for more details. Tested in: testGeneralQueryBuilding
        'allowCache'=>'<boolean|null>', // Optional. Whether or not to allow cache. Tested in: testGeneralQueryBuilding
        'cacheId' => '<string|null>', // Optional. An optional cache id to use for the result cache. Tested in: testGeneralQueryBuilding
        'useQueryCache' => '<boolean|null>', // Defaults to true. Whether ot not to use query cache. Tested in: testGeneralQueryBuilding
        'useResultCache' => '<boolean|null>', // Defaults to false. Whether or not to use result cache. Tested in: testGeneralQueryBuilding
        'timeToLive' => '<int|null>',// Optional. Time to live of the cache. Tested in: testGeneralQueryBuilding
        'tagSet' => '<array|null>', // Optional. In a future version you can pass in cache tags to assign to the result cache. Not yet implemented
        'transaction'=>'<boolean|null>', // Defaults to true. Whether ot not to process the database interactions as part of a transaction that will roll back on failure. Tested in testMultiAddAndChain
        'entitiesShareConfigs'=>'<boolean|null>', // Defaults to true. If turned on like entities will share configs, mildly speeding up execution times. Tested in testMultiAddAndChain
        'flush' => '<boolean|null>', // Defaults to true. Whether or not to flush to the db at the end of call to Create, Read, Update, or Delete. Tested in testMultiAddAndChain
        'batchMax' => '<int|null>', // Optional. The maximum number of arrays that can be passed to a Create, Update or Delete method. Used for stopping requests that are to large. Tested in testMaxBatch
        'queryMaxParams' => '<int|null>', // Optional. The maximum number of query params that can be passed to a Read method. Used for stopping requests that are to large. Tested in testGeneralDataRetrieval
        'maxLimit' => '<int|null>', // Defaults to 100. The maximum number of rows that can be returned at a time. Tested in testGeneralDataRetrieval
        'prePopulateEntities'=>'<boolean|null>', // Defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        'clearPrePopulatedEntitiesOnFlush'=>'<boolean|null>', // Defaults to true. whether or not when a flush occurred the pre populated entities should be cleared. Tested in testPrePopulate
        'fixedLimit'=>'<boolean|null>'  // Defaults to false. This can be used to force all returns to have the same number of rows. This is useful when it comes to caching to make sure the same types pagination are requested every time. Tested in testFixedLimit
    ]
];

// This is returned in the getTTConfig method of a entity.
$entityConfig = [
    '<string>'=>[ // Context keys may be layered as deep as you like. They are part of the contextual config nature of Scribe. See documentation for more details.
        '<"create" | "update" | "delete">'=>[ // Optional. Can be null to disable the block. Configs work the same for create, update and delete
            'extends'=>['<array|null>'], // Optional. An array of string paths to extend from else where in the array. The values from array at the paths specified will be used as defaults for for the config.
            'allowed'=>'<boolean|null>', // Defaults to true. Whether ot not this type of operation is permitted. Tested in: testAllowedWorks
            'permissive'=>'<boolean|null>', // Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner.Tested in: testPermissiveWorks1
            'settings'=>[ // Optional. Can be null to disable the block. Settings related to the type of operation.
                'setTo'=>'<array|null>', // Optional. Array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on pre-persist. Tested In: testTopLevelSetToAndMutate
                'enforce'=>'<array|null>', // Optional. Array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on pre-persist. Tested in: testEnforceTopLevelWorks
                'closure'=>'<closure|null>', // Optional. A closure to test if the operation should go ahead. Tested in: testTopLevelClosure
                'mutate'=>'<closure|null>', // Optional. A closure to mutate the entity on pre-persist. Tested In: testTopLevelSetToAndMutate
                'validate'=>[ // Optional. Can be null to disable the block. Validation settings passed to a validator used on the class. Tested in: testValidatorWorks
                    'fields'=>[ // Optional. Can be null to disable the block.
                        '<string>'
                    ], // if not set the keys from rules will be used instead. By default this works like the fields passed to a Laravel validator.
                    'rules'=>[
                        '<string>'=>'<string|null>' // Validator rules. By default these are rules that are used by Laravel validator. The keys are the field names the rules apply to.
                    ], // Tested in: testValidatorWorks
                    'messages'=>[ // Optional. Can be null to disable the block.
                        '<string>'=>'<string|null>'
                    ], // Custom validator messages to return on failure. By default works with Laravel validator functionality.
                    'customAttributes'=>[ // Optional. Can be null to disable the block.
                        '<string>'=>'<string|null>'
                    ], // Custom attributes to pass to the validator. By default works with Laravel validator functionality.
                ],
            ],
            'toArray'=>[ // Optional. Can be null to disable the block. Settings that dictate how an entity is converted to array.
                '<string>'=>[ // Can be null to disable the block. Putting in an empty array will just assume get type. Key names that will be returned in the resulting array. If type is get this key must match a field/association name on the entity.
                    'type'=>'<"get" | "literal" | null>',// Defaults to "get". if "get" then the key is a property but we get it by calling get<Property name>. If 'literal' then a value key must be included, the value key may be a closure that returns a value or a static value.  Tested in: testToArrayBasicFunctionality
                    'value'=>'<mixed|null>', // Optional. The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                    'format'=>'<string|null>', // Optional. Format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                    'allowLazyLoad'=>'<boolean|null>', // Defaults to false. If true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
                ]
            ],
            'fields'=>[ // Optional. Can be null to disable the block.
                '<string>'=>[ // Optional. Can be null to disable the block. Field name that the permissions here relate to.
                    'permissive'=>'<boolean|null>', // Defaults to true. Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in: testPermissiveWorks1 / testPermissiveWorks2
                    'settings'=>[ // Optional. Can be null to disable the block.
                        'setTo'=>'<mixed|null>', // Optional. A value to set the field to. Tested in: testFastMode2AndLowLevelSetTo
                        'enforce'=>'<mixed|null>', // Optional. If on a field then we test to make sure the field value being set matches. If on an association then this value should be an array that compares the values to the properties being set on the association. Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                        'closure'=>'<closure|null>', // Optional. A closure that tests if the operation should be able to continue. Tested in testLowLevelClosure
                        'mutate'=>'<closure|null>', // Optional. A closure that mutates the values of the field before being committed to the DB. Tested in testLowLevelMutate
                    ],
                    'assign'=>[ // Optional. Types of assignment operations that are allowed or disallowed.
                        'set'=>'<boolean|null>', // Defaults to permissive setting. set<fieldname> method allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'add'=>'<boolean|null>', // Defaults to permissive setting. add<fieldname> method allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'remove'=>'<boolean|null>', // Defaults to permissive setting. remove<fieldname> method allowed or not. Tested in: testChainRemove
                        'setSingle'=>'<boolean|null>', // Defaults to permissive setting. set<fieldname> method allowed or not. Works like set but changes pluralized field names to singular.  Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'addSingle'=>'<boolean|null>', // Defaults to permissive setting. add<fieldname> method allowed or not. Works like add but changes pluralized field names to singular. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'removeSingle'=>'<boolean|null>', // Defaults to permissive setting. remove<fieldname> method allowed or not. Works like remove but changes pluralized field names to singular. Tested in: testChainRemove
                        'null'=>'<boolean|null>', // Defaults to permissive setting. null assign types tell the system not to assign the entity that is manipulated. This is useful when an entity is already associated with another entity, but you want to interact with that entity any way with out calling add or remove methods on the parent entity. Tested in: testNullAssignType. Whether or not having no assign type is allowed.
                        'setNull'=>'<boolean|null>' // Defaults to permissive setting. This assign type sets a field to null.
                    ],
                    'chain'=>[ // Optional. Types of chaining that are allowed or not
                        'create'=>'<boolean|null>', // Defaults to permissive setting. Create type chaining allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'update'=>'<boolean|null>', // Defaults to permissive setting. Update type chaining allowed or not. Tested in: testUpdateWithChainAndEvents
                        'delete'=>'<boolean|null>', // Defaults to permissive setting. Delete type chaining allowed or not. Tested in: testMultiDeleteAndEvents
                        'read'=>'<boolean|null>' // Defaults to permissive setting. Read type chaining allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    ]
                ]
            ],
            'options'=>[ // Optional. Can be null to disable the block.
                '<string>'=>'<mixed|null>', // Reserved for custom use cases
            ]
        ],
        'read'=>[
            'toArray'=>[ // Optional. Can be null to disable the block. Settings that dictate how an entity is converted to array.
                '<string>'=>[ // Can be null to disable the block. Putting in an empty array will just assume get type. Key names that will be returned in the resulting array. If type is get this key must match a field/association name on the entity.
                    'type'=>'<"get" | "literal" | null>',// Defaults to "get". if "get" then the key is a property but we get it by calling get<Property name>. If 'literal' then a value key must be included, the value key may be a closure that returns a value or a static value.  Tested in: testToArrayBasicFunctionality
                    'value'=>'<mixed|null>', // Optional. The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                    'format'=>'<string|null>', // Optional. Format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                    'allowLazyLoad'=>'<boolean|null>', // Defaults to false. If true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
                ]
            ],
            'options'=>[ // Can be null to disable the block.
                '<string>'=>'<mixed|null>', // Reserved for custom use cases
            ]
        ]
    ]
];


$controllerConfig = [
    '<string>'=>[ // Context keys may be layered as deep as you like. They are part of the contextual config nature of Scribe. See documentation for more details.
        '<"GET" | "POST" | "PUT" | "DELETE" | null>'=> [ // GET, POST, PUT, DELETE. Corresponds to the request method used
            'extends'=>['<array|null>'], // Optional. An array of string paths to extend from else where in the array. The values from array at the paths specified will be used as defaults for for the config.
            'transaction'=>'<boolean|null>', // Defaults to false. Whether or not an additional transactions should be started at the controller level. This is useful if you mean to call one more than 1 repo in the controller using events.
            'overrides'=>['<array|null>'], // Optional. Overrides passed to the repo that will override the default options set on the repo.
            'transformerSettings'=>[ // Optional. Can be null to disable the block. Settings to be passed to the transformer, generally toArray settings. Tested in testToArrayArrayStorage and as part of all transformation tests
                'defaultMode'=>'<"create" | "read" | "update" | "delete" | null>', // Defaults to 'read'. This is the mode that will be initiated on the entity if no mode is currently active on the entity being transformed
                'defaultArrayHelper'=>'<\TempestTools\Common\Contracts\ArrayHelperContract | null>',// Optional.  If no array helper is set for the entity already this one will be used.
                'defaultPath'=>['<array|null>'],// Optional.  A contextual config path. If no path is set for the entity already this will be used
                'defaultFallBack'=>['array|null>'], // Optional.  A contextual config path. If no fall back is set for the entity already this will be used
                'force'=>'<boolean|null>', // Defaults to false. If true then the entity will be forced to use the path, mode, fall back and array helper that you are setting as defaults, regardless of if the entity has there own already.
                'store'=>'<boolean|null>', //Defaults to true. Whether or not the toArray result should be stored to be used again if toArray is called again.
                'recompute'=>'<boolean|null>', // Defaults to false. Whether or not to recompute toArray, even if one was previously stored.
                'useStored'=>'<boolean|null>', // Defaults to true. Whether or not to use a previously stored toArray result if one is found. If false then it will return a freshly generated result.
                'frontEndOptions'=>['<array|null>'], // Optional.  Options passed from the front end with the request, you may set these on the controller as defaults if you like but they will be overridden by data passed from the front end.
            ],
            'resourceIdConversion'=>[ // Optional. This lets you set up resource ids passed in the url that are automatically converted to placeholders in the filter from the front end. This is useful for filtering a query semi automatically based on the resource ids in the url path. Tested in testAlbumController
                '<string>'=>'<string|null>'// Placeholder as it appear in the url string.  If null then the placeholder will not be generated, other wise it will be converted to a placeholder with the name of value
            ]
        ]
    ]
];

// GET requests from the frontend:
// The following is a guide to filters you can pass from the frontend to filter the query that is run by Scribe.
// From the frontend, pass one of the following get params to let Scribe know where to look for your query:
// queryLocation = <body | singleParam | params> -- if 'body' they the query was passed as a json in the body of the request, if 'singleParam' it was passed in another get param called 'query' as a json encoded string, if 'params' the query was passed as param syntax listed further down. By default 'params' is used, because it's the most standards complaint.
// Tested in: testGeneralQueryBuildingWithGetParams

// This would be passed to a get request to an end point as json encoded string if either: <url>?queryLocation=body or <url>?queryLocation=singleParam&query=<json>
$frontEndQueryToGet = [
    'query'=>[
        'where'=>[ // Optional block. In this block filters can be set that will be applied to the where clause of the query. Tested in: testGeneralQueryBuilding
            [
                'field'=>'<string>', //Field name with the alias of the table the field is on, such as t.id. Tested in: testGeneralQueryBuilding
                'type'=>'<"and"|"or">', // Is it a an 'and' or an 'or' criteria. Tested in: testGeneralQueryBuilding
                'operator'=>'<string>', // An expression name that will be used to generate the operator. If operator is 'andX' or 'orX' then 'conditions' value with a nested list of conditions is used instead of the 'arguments' value. By default the following operators are allowed: 'andX', 'orX', 'eq', 'neq', 'lt', 'lte', 'gt', 'gte', 'in', 'notIn', 'isNull', 'isNotNull', 'like', 'notLike', 'between'. Tested in: testGeneralQueryBuilding
                'arguments'=>['<mixed>'],  // Arguments to pass to the expression. If operator is 'andX' or 'orX' this is omitted. Conditions are used in instead. Generally just 1 argument is needed, for between operators use 2 arguments, and for in and notIn operators use a nested array of values -- 'arguments'=>[['<string>', '<string>']]. //Tested in: testGeneralQueryBuilding
                'conditions'=>['<array>'] // Contains an array of additional criteria. This nested array has the exact same structure as the block above it in this example. If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting. //Tested in: testGeneralQueryBuilding
            ]
        ],
        'having'=>[ // Optional block. Works the same as the where block but applied to criteria to the having clause of the query. Tested in: testGeneralQueryBuilding
            [
                'field'=>'<string>', //Field name with the alias of the table the field is on, such as t.id. Tested in: testGeneralQueryBuilding
                'type'=>'<"and"|"or">', // Is it a an 'and' or an 'or' criteria. Tested in: testGeneralQueryBuilding
                'operator'=>'<string>', // An expression name that will be used to generate the operator. If operator is 'andX' or 'orX' then 'conditions' value with a nested list of conditions is used instead of the 'arguments' value. By default the following operators are allowed: 'andX', 'orX', 'eq', 'neq', 'lt', 'lte', 'gt', 'gte', 'in', 'notIn', 'isNull', 'isNotNull', 'like', 'notLike', 'between'. Tested in: testGeneralQueryBuilding
                'arguments'=>['<mixed>'],  // Arguments to pass to the expression. If operator is 'andX' or 'orX' this is omitted. Conditions are used in instead. Generally just 1 argument is needed, for between operators use 2 arguments, and for in and notIn operators use a nested array of values -- 'arguments'=>[['<string>', '<string>']]. //Tested in: testGeneralQueryBuilding
                'conditions'=>['<array>'] // Contains an array of additional criteria. This nested array has the exact same structure as the block above it in this example. If operator is not 'andX' or 'orX' this is omitted. This allows condition nesting. //Tested in: testGeneralQueryBuilding
            ]
        ],
        'orderBy'=>[ // Optional block. This block will add criteria to the order by clause of the query. Tested in: testGeneralQueryBuilding
            '<string>'=>'<"ASC" | "DESC">' // The key is the field name (including the table alias -- such as t.id) and the value is the direction of the order by. Tested in: testGeneralQueryBuilding
        ],
        'groupBy'=>[ // Optional block. Tested in: testGeneralQueryBuilding
            '<string>' // Each value is the a field name including the table alias -- such as t.id) to group by. Should only be used in queries that inherently have an aggregate in the select. Tested in: testGeneralQueryBuilding
        ],
        'placeholders'=>[ // Optional block. This blocks lets the frontend pass in values for placeholders which the back end developer has added to the base query. Tested in: testGeneralQueryBuilding
            '<string>'=>[ // The key is a placeholder name that is waiting to be used in the query. Tested in: testGeneralQueryBuilding
                'value'=>'<mixed>', //Value to put in the placeholder. Tested in: testGeneralQueryBuilding
                'type'=>'<PDO::PARAM_* | \Doctrine\DBAL\Types\Type::* constant | null>' // Optional type of the placeholders. Tested in: testGeneralQueryBuilding
            ]
        ],
    ],
    'options'=>[ // Optional block. This block lets you pass in options related to the query. You may add your own keys and values for your own implementations as well. These options will be passed to the repository actions that are called and may be referenced by your custom event listeners and closures.
        'returnCount'=>'<boolean|null>', // Defaults to true. Whether or not the count should be returned from and index action. Tested in: testGeneralDataRetrieval
        'limit'=>'<int|null>', // Defaults to 100. The limit to apply to the query (max number of rows that will be returned).  Tested in: testGeneralQueryBuilding
        'offset'=>'<int|null>', // Default to 0. The offset for the query. If omitted index actions will return data starting at the first available row. Tested in: testGeneralQueryBuilding
        'useGetParams'=>'<boolean|null>',// Defaults to true. Whether or not to expect the params to be in discrete get params format as illustrated below.  You would not pass this option directly from the frontend -- it will instead be detected from if you called the url like so: <url>?queryLocation=params, or if you omitted the queryLocation all together.  Tested in testGeneralQueryBuildingWithGetParams
        'resourceIds'=>[ // Optional block.
            '<string>'=>'<string>' // The key is the name of the resource id (as it would appear in the construction of the route in Laravel such as: users/{user}). The value is the placeholder name the key will be converted to. Placeholders created in this way will be added to the query as parameters for placeholders which the back end developer has put in the query to receive them.
        ] // This is automatically populated or appended to by the controller based on parameters passed through the url. The ability for the frontend to specify resourceIds is included for use with custom logic.
    ]
];

// Example json:

/*{
	"query": {
		"where": [{
			"field": "t.name",
			"type": "and",
			"operator": "eq",
			"arguments": ["BEETHOVEN"]
		}],
		"having": [{
			"field": "t.name",
			"type": "and",
			"operator": "eq",
			"arguments": ["BEETHOVEN"]
		}],
		"orderBy": {
			"t.name": "ASC",
			"t.id": "DESC"
		},
		"groupBy": [
			"t.name",
			"t.id"
		],
		"placeholders": {
			"test": {
				"value": 42,
			}
		}
	},
	"options": {
		"returnCount": true,
		"limit": 1,
		"offset": 1
	}
}*/

// A frontend option of useGetParams (which triggers processing of the query as get params), is also accepted by the code, but it would not be passed in the format above, instead it triggers either automatically or if the url was called with: <url>?queryLocation=params.
// The following details how to pass your filter and options from the frontend as discrete params instead of as a json encoded string.


// Get param query syntax:
// Where or having: <and|or>_<where|having>_<string>_<string>_<?int>=<mixed>
// The first string is the operator name', and the second string is the 'field name'.
// If the where operator is 'in' or 'between' then use an array for the addition arguments: IE:
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

// Non get requests:

// All non get requests will include params (described below) and may include an options block. The structure will look like this.
$exampleFrontEndNonGetRequest = [
    'params'=>['<array>'], // See below for examples for different types of param sets that can be passed
    'options'=>[ // Optional. This block relates to the options for the request.
        'simplifiedParams'=>'<boolean|null>', // Defaults to false. May also be set as a different default on the controller. This value is whether or not to process the params as standard verbose params or simplified params. Both param examples are below.
        'testMode'=>'<boolean|null>', // Defaults to false. If set to true then data will be rolled back instead of committed. This lets you write test cases that use the api but not store anything to the db.
        'toArray'=>[ // Optional. This blocks relates to the data that should be returned -- specifying how the entities will be transformed to an array.
            'completeness'=>'<"full" | "limited" | "minimal" | "none" | null>', // Defaults to 'full'. if 'full' then all data will be shown so long as it wouldn't trigger an infinite loop (relations between entities are omitted as soon as they loop back on them selves), if 'limited' then all data will be shown but relations leading to already processed entities will not be shown, if 'minimal' the same entity will never be shown twice in the return and an empty array will be in it's place, if 'none' nothing is returned. Tested in: testToArrayBasicFunctionality
            'maxDepth'=>'<int|null>', // Optional. How deep should the to array go. You can use this option to prevent unnecessary levels of depth in your return. Tested in: testToArrayBasicFunctionality
            'excludeKeys'=> ['<string>'], // Optional. Each string is the name of a field or association that should be returned in the resulting array from the back end. Use this to prevent certain keys from being converted to array to trim the return. Tested in: testToArrayBasicFunctionality
            'allowOnlyRequestedParams'=>'<boolean|null>',// Defaults to true. If true only the params that you requested to be changed on the effected entities will be shown in the return. This filters out any fields or associations that you did not request to be changed directly with this request. Tested in: testToArrayBasicFunctionality
            'forceIncludeKeys'=>['<string>'] // Defaults to: ['id']. These are keys to include in the result even if you didn't request to change them. Tested in: testToArrayBasicFunctionality
        ]
    ]
];


// Verbose param syntax:
// Verbose types of params execute more quickly but are less pretty to look at and write.
// Note: Create requests are structured a little differently than update, or delete requests.
// Note: When a association is set to null then an assignType of setNull is used internally which calls the set method with a null value
$singleParamForField = [ // You may have as many field names and values as you like in your request, one is shown in the example for illustration. Tested in CudTest.php
    '<string>'=>'<mixed>', // If a field name is specified as a key then the value will be the value that the field will be set to. Tested in CudTest.php
];

$singleParamForAssociation = [ //You may have as many field names and values as you like in your request, one is shown in the example for illustration. Tested in CudTest.php
    '<string>'=>[ // The key is the association name. A null can be put here instead to null the field, or a an id can be put here to automatically read and assign an entity with that id to the association. Tested in CudTest.php
        '<"create" | "read" | "update" | "delete">'=>[ // Optional This key is the chainType. chainType can be: create, update, delete, read. This triggers another entity to be created or retrieved and manipulated to be added to this association. Tested in CudTest.php
            '<string|int|null>'=>[ // If you are chaining with a create chain type, omit the key. For any other chain type the key references the id of the entity to be retrieved from the database and manipulated.
                '<string>'=>'<mixed>', // Optional. This is a reference to another field or association. Tested in CudTest.php
                'assignType'=>'<"set"|"add"|"remove"|"setSingle"|"addSingle"|"removeSingle"|"null"|"setNull"|null>' // Defaults to 'set'. This is the assignment type that the entity will be assigned to the association using. It corresponds to the method on the entity that will be called (IE: set<string> where string is the field name to set, such as setName). If null set will be used. Any time single is at the end of the assign type, then we strip the 's' off the end of the association name before calling the method. For instance if you have an association of users, but you have a method of addUser you need use an assignType of addSingle. // Tested in CudTest.php
            ]
        ]
    ]
];

// Note: To request a batch of creates be created at once, pass an array of create parameters instead of just params for a single entity.
$createBatchParams = [ // Tested in CudTest.php
    [
        '<string>'=>'<mixed>',
    ]
];

$nonCreateBatchParams = [ // Tested in CudTest.php
    '<string|int|null>'=>[ // If you are chaining with a create chain type, omit the key. For any other chain type the key references the id of the entity to be retrieved from the database and manipulated.
        '<string>'=>'<mixed>',
    ]
];

// Note: All following examples use the following Entities:
//  Album
//      with a ManyToOne association to Artist
//      with a ManyToMany association to User
//  Artist
//      with a oneToMany association to Album
//  User
//      with a ManyToMany association to Album


// Example: Create an album in a batch request using POST, chain a new artist on to it, and assign an existing user to the album:
/*{
    "params": [{
        "name": "BEETHOVEN: THE COMPLETE PIANO SONATAS",
		"releaseDate": "2017-10-31 01:43:01",
		"artist": {
            "create": [{
                "name": "BEETHOVEN",
				"assignType": "set"
			}]
		},
		"users": {
            "read": {
                "1": {
                    "assignType": "addSingle"
				}
			}
		}
	}]
}*/

// Example: update artist with an id of 1, then update an album with an id of 1 to have a new name as well, using a PUT request.

/*{
    "params": {
        "1": {
            "name": "The artist formerly known as BEETHOVEN",
			"albums": {
                "update": {
                    "1": {
                        "name": "Kick Ass Piano Solos!"
					}
				}
			}
		}
	}
}*/

// Example: Remove an album from an artist as part of an update request

/*
 {
	"params": {
		"1": {
			"albums": {
				"update": {
					"1": {
						"assignType": "removeSingle"
					}
				}
			}
		}
	}
}
 */

// Example: With a DELETE request remove a artist and an album related to it
/*
 {
	"params": {
		"1": {
			"albums": {
				"delete": {
					"1": {
						"assignType": "removeSingle"
					}
				}
			}
		}
	}
}
 */

// Simplified Param Syntax for Create Update and Delete. To activate pass a frontend option of 'simplifiedParams'=>true

$creates = [ // Note lack of id triggers create
    [
        '<string>'=>'<mixed>', // Field name and value to set it to too. Can also be a association with an array of data relating to the association.
    ]
];

$updates = [ // Including an id triggers an update when there is another field referenced
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>'<mixed>', // Field name and value to set it to too. Can also be a association with an array of data relating to the association.
    ]
];

$deletes = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
    ]
];


// Chaining Examples:
// Note: These are all chains from inside a update action

$singleAssociationCreate = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note the lack of the id triggers the create. This automatically triggers an assignType='set' (used with *ToOne type relationships). If you included an array of arrays it would trigger a assignType=addSingle (used with *ToMany relationships)
            '<string>'=>'<mixed>', // Field name and value to set it to too. Can also be a association with an array of data relating to the association.
        ],
    ]
];

$singleAssociationUpdate = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note the id and the field means it's an update
            'id'=>'<string|int>', // The id of the entity being accessed.
            '<string>'=>'<mixed>', // Field name and value to set it to too. Can also be a association with an array of data relating to the association.
        ],
    ]
];

$singleAssociationRead = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note  just using the id triggers a read and then assigns the entity to the association
            'id'=>'<string|int>', // The id of the entity being accessed.
        ],
    ]
];

$singleAssociationDelete = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note that specifying chainType=delete triggers a delete
            'id'=>'<string|int>', // The id of the entity being accessed.
            'chainType'=>'delete' // This instructs Scribe to delete the associated entity.
        ],
    ]
];

// When you are chaining to a *ToMany relationship you use an array of arrays to trigger a addSingle assign type.
$multipleAssociationCreate = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note the lack of the id triggers the create. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
            [
                '<string>'=>'<mixed>', // Field name and value to set it to too. Can also be a association with an array of data relating to the association.
            ],
        ]
    ]
];

$multipleAssociationUpdate = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note the id and the field means it's an update. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
            [
                'id'=>'<string|int>', // The id of the entity being accessed.
                '<string>'=>'<mixed>', // Field name and value to set it to too. Can also be a association with an array of data relating to the association.
            ],
        ]
    ]
];

$singleAssociationRead = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note the just using the id triggers a read and then assigns the entity to the association. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
            [
                'id'=>'<string|int>',
            ],
        ]
    ]
];

$singleAssociationReadSimplierStill = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>'<string|int>' // With the key as the association name and the value as an id it will automatically read the associated entity. This is simplified version of the above example
    ]
];

$singleAssociationDelete = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note that specifying chain type delete triggers a delete. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
            [
                'id'=>'<string|int>', // The id of the entity being accessed.
                'chainType'=>'delete' // This instructs Scribe to delete the associated entity.
            ],
        ]
    ]
];

// Any update, read or delete chain can cause the entity referenced to be removed instead of added to the association by using 'assignType'=>'removeSingle'. You can also use 'remove' if you have a plural in the name of your method on the entity -- IE: removeUser vs removeUsers
$singleAssociationRead = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[  //Association name. Note the just using the id triggers a read and then assigns the entity to the association. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
            [
                'id'=>'<string|int>', // The id of the entity being accessed.
                'assignType'=>'removeSingle' // This triggers script to remove the associated entity form it's parent.
            ],
        ]
    ]
];

// Some times you want to update an entity through an association, but you don't want to trigger any assignType (because if you trigger an assignType you will be adding the same entity to an association that already exists).
$multipleAssociationUpdateWithOutAddingIt = [
    [
        'id'=>'<string|int>', // The id of the entity being accessed.
        '<string>'=>[ //Association name. Note the id and the field means it's an update. These will automatically be added to the association with the same assignment behaviour as assignType=>addSingle
            [
                'id'=>'<string|int>', // The id of the entity being accessed.
                '<string>'=>'<mixed>', // Field name and value to set it to too. Can also be a association with an array of data relating to the association.
                'assignType'=>'null' // This causes the entity to be updated, but not assigned to the parent entity through the association.
            ],
        ]
    ]
];

// Examples:

// Example with a POST request create an artist, and album via chaining. Notice that whether or not you are doing a batch request is automatically detected when you use a POST. This example shows the end point will see this is not a batch request (since it only has 1 artist being created).
// The url might look like: /artists
/*
{
	"params": {
		"name": "Test Artist",
		"albums": [{
			"name": "Test Album",
			"releaseDate": "2017-10-31 01:43:01"
		}]
	},
	"options": {
		"simplifiedParams": true
	}
}
 */

// Example with PUT request. Update a album to have a new name, and then remove a user from it via chaining. This would be done via a batch request.
// The url might look like: /albums/batch
/*
{
	"params": [{
		"id": 1,
		"name": "Name Redacted",
		"users": [{
			"id": 1,
			"assignType": "removeSingle"
		}]
	}],
	"options": {
		"simplifiedParams": true
	}
}
 */

// Example with PUT request. Update an album, and update it's user, with out triggering an assignType=addSingle that would try to add the user back onto the album a second time.
// The url might look like: /albums/batch
/*
{
	"params": [{
		"id": 1,
		"name": "Name Redacted",
		"users": [{
			"id": 1,
            "name":"Your name is Dave now",
			"assignType": "null"
		}]
	}],
	"options": {
		"simplifiedParams": true
	}
}

// Example with PUT request. You can access a single resource by including it's id in the url and providing params for just 1 resource and not putting the id in the params. Here is another way to trigger the same behaviour demonstrated above.
// The url might look like: /albums/1
// Note that in the same manner you can batch PUT requests, or have them access a single resource you can do the same with DELETE requests.

/*
{
	"params": {
		"name": "Name Redacted",
		"users": [{
			"id": 1,
			"assignType": "removeSingle"
		}]
	},
	"options": {
		"simplifiedParams": true
	}
}
 */

// Example with a DELETE request. Delete a album and the artist it is associate with
// The url might look like: /albums/batch

/*
{
	"params": [{
		"id": 1,
		"artist": {
			"id": 1,
			"chainType": "delete"
		}
	}],
	"options": {
		"simplifiedParams": true
	}
}
 */