<?php
// This is returned in the getTTConfig method of a repository.
$readInfo = [
    '<context key>'=>[ // Context keys may be layered as deep as you like. They are part of the contextual config nature of Scribe. See documentation for more details.
        'extends'=>['<an array of string paths to extend from else where in the array.>'],
        'read'=>[
            'query'=>[
                'select'=>[ //A list of arbitrary key names select strings to use in the query. with Tested in: testBasicRead
                    '<keyName>'=>'<string>' //Tested in: testBasicRead
                ],
                'from'=>[ // if not supplied it will be auto generated. A list of arbitrary key names and array keys and values to make a from part of the query. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<keyName>'=>[ // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'className'=>'<string>', // Class name of the Entity associated with the base table from the query. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', // The alias to use in the from. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string|null>', // indexBy functionality of Doctrine. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'append'=>'<boolean|null>' // Whether or not to ad as an additional from, when you want more than 1 from in the query. Defaults to false // Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'where'=>[  // A list of arbitrary key names and strings, or arrays used to make where clauses. Tested in: testGeneralQueryBuilding
                    '<keyName>'=>[ //Tested in: testGeneralQueryBuilding
                        'type'=>'<null | and | or>', //If null then it's neither a 'and' or 'or' where clause, or wise it can be set to 'and' or 'or' Tested in: testGeneralQueryBuilding
                        'value'=>'<string|array>' // Either a string used in the where clause or an array.  If an array of: ['expr'=>'<xpr name>', 'arguments'=>['<arguments, could be another xpr array>']] is used, then all parts will be parsed by the array helper, and corresponding xpr methods will be called with the specified arguments. This is true for all parts of the query //Tested in: testGeneralQueryBuilding
                    ]
                ],
                'having'=>[ //Works the same as the where section but applied to a having clause. Tested in: testGeneralQueryBuilding
                    '<keyName>'=>[ //Tested in: testGeneralQueryBuilding
                        'type'=>'<null | and | or>', //Tested in: testGeneralQueryBuilding
                        'value'=>'<string|array>' // can be an array expression like with the where. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'leftJoin'=>[ // A list of arbitrary key names arrays with information about joins for the query to make. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<keyName>'=>[ //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'join'=>'<string>', // A join part of the query, such as <table alias>.<relationship being joined to>. When using a queryType of sql use: <from alias>.<name of table to join too>. IE: t.Albums //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', // The alias the table will be joined as. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'conditionType'=>'<condition type |null>', // A condition type for the join such as: Expr\Join::WITH. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'condition'=>'<string>', //A condition to join on such as x =  x. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string | null>', //Doctrine indexBy functionality. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'innerJoin'=>[ // Works the same as the leftJoin block but goes into innerJoin now. Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    '<keyName>'=>[ //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'join'=>'<string>', // When using a queryType of sql use: <from alias>.<name of table to join too>. IE: t.Albums //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'alias'=>'<string>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'conditionType'=>'<condition type | null>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'condition'=>'<string>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                        'indexBy'=>'<string|null>', //Tested in: testGeneralQueryBuilding. Sql tested in testSqlQueryFunctionality
                    ]
                ],
                'orderBy'=>[ // A list of arbitrary key names and array with information for building order bys. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ //Tested in: testGeneralQueryBuilding
                        'sort'=>'<string>', // The fields to be sorted by. Tested in: testGeneralQueryBuilding
                        'order'=>'<ASC|DESC>' // The direction to order by. ASC or DESC. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'groupBy'=>[ // A list of arbitrary key names and a string to use to group by. Tested in: testGeneralQueryBuilding
                    '<string>'=>'<string>' //The field to group by. Tested in: testGeneralQueryBuilding
                ],
            ],
            'settings'=>[ // Settings related to the query
                'queryType'=>'<dql | sql>', // Defaults to DQL, if SQL is used then Doctrine DBAL query is used instead of an ORM query. Design your syntax accordingly. sql tested in testSqlQueryFunctionality
                'cache'=>[ //Settings related to cache. Tested in: testGeneralQueryBuilding
                    'queryCacheProfile'=>'<a doctrine query cache profile>',// See Doctrine docs for more information. Used only by SQL queries. Use a QueryCacheProfile object. Tested in testSqlQueryFunctionality
                    'useQueryCache'=>'<boolean|null>', // Whether or not use query cache with the query. Can't be properly determined by a test case
                    'useResultCache'=>'<boolean|null>', // Whether or not to use result cache with the query. Can't be properly determined by a test case
                    'timeToLive'=>'<int|null>', // The time to live of the cache. Tested in: testGeneralQueryBuilding
                    'cacheId'=>'<string|null>', // A cache id to use for the result cache. Tested in: testGeneralQueryBuilding
                    'tagSet'=>[ // Future release will include this functionality. This will allow result cache to be tagged with provided tags.
                        '<string>'=>[
                            'disjunction'=>'<boolean|null>',
                            'templates'=>[
                                '<string>'
                            ]
                        ]

                    ]
                ],
                'placeholders'=>[ // A list of arbitrary keys and place holders to inject into the query. Tested in: testGeneralQueryBuilding
                    '<string>'=>[ //Tested in: testGeneralQueryBuilding
                        'value'=>'<mixed>', //The value of the placeholder. Tested in: testGeneralQueryBuilding
                        'type'=>'<param type | null>' //The type of the value. This is optional and while Doctrine supports it it doesn't seem necessaryy or valuable. Tested in: testGeneralQueryBuilding
                    ]
                ],
                'fetchJoin'=>'<boolean|null>', // whether or not when paginating this query requires a fetch join // Tested in: testGeneralDataRetrieval
            ],
            'permissions'=>[ // Permissions information related to the query.
                'settings'=>[
                    'closure'=>'<closure>', // A closure to test if the current query can be allowed to proceed. Tested in testMutateAndClosure
                    'mutate'=>'<closure>', // A closure to mutate information about the current query before running it. Tested in testMutateAndClosure
                ],
                'allowed'=>'<boolean|null>', // Whether or not queries are allowed. Tested in testReadPermissions
                'maxLimit'=>'<int|null>', // The maximum number of rows that can be returned at once. Tested in testGeneralDataRetrieval
                'fixedLimit'=>'<int|null>', // This can be used to force all returns to have the same number of rows. This is useful when it comes to caching to make sure the same types pagination are requested every time.  Tested in testFixedLimit
                'where'=>[ // Permissions related to the where part of the query passed from the front end. Tested in testReadPermissions
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions
                    'fields'=>[ // Permissive related to individual fields. Tested in testReadPermissions
                        '<string>'=>[ // The field name the permissions are related to. Tested in testReadPermissions
                            'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner.. Tested in testReadPermissions
                            'settings'=>[
                                'closure'=>'<closure>', // A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure>', // A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure
                            ],
                            'operators'=>[ //A list of operators that are allowed or disallowed to be used in a query from the front end.  Tested in testReadPermissions
                                '<string>'=>'<boolean|null>' // An operator name and whether or not it's allowed. Tested in testReadPermissions
                            ]
                        ]
                    ]
                ],
                'having'=>[ // Works the same as where permissions
                    'permissive'=>'<boolean|null>', // Tested in testReadPermissions2 and testReadPermissions3
                    'fields'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                        '<field name>'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                            'permissive'=>'<boolean|null>', // Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[
                                'closure'=>'<closure>', // Tested in testMutateAndClosure
                                'mutate'=>'<closure>', // Tested in testMutateAndClosure and testMutateUsed
                            ],
                            'operators'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                                '<string>'=>'<boolean|null>' // Tested in testReadPermissions2 and testReadPermissions3
                            ]
                        ]
                    ]
                ],
                'orderBy'=>[ // Permissions for order by requests from front end.
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'fields'=>[
                        '<string>'=>[ // The field name the permissions relate to. Tested in testReadPermissions2 and testReadPermissions3
                            'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[
                                'closure'=>'<closure>', // A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure>', // A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ],
                            'directions'=>[ // A list of directions that are allowed or not allowed. Tested in testReadPermissions2 and testReadPermissions3
                                '<ASC | DESC>'=>'<boolean|null>' // Whether or not the direction is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            ]
                        ]
                    ]
                ],
                'groupBy'=>[ // Permissions for group by requests from front end. Tested in testReadPermissions2 and testReadPermissions3
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'fields'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                        '<string>'=>[ // The field name the permissions relate to. Tested in testReadPermissions2 and testReadPermissions3
                            'allowed'=>'<boolean|null>', // Whether or group by for this field is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[
                                'closure'=>'<closure>', // A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure>', // A closure to mutate information related to the field before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ]
                        ]
                    ]
                ],
                'placeholders'=>[ // Permissions for placeholders requests from front end.
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in testReadPermissions2 and testReadPermissions3
                    'placeholderNames'=>[ // Tested in testReadPermissions2 and testReadPermissions3
                        '<string>'=>[ // The name of the placeholder passed from the front end that permission relates too. Tested in testReadPermissions2 and testReadPermissions3
                            'allowed'=>'<boolean|null>', // Whether or not use of this placeholder by the front end is allowed. Tested in testReadPermissions2 and testReadPermissions3
                            'settings'=>[
                                'closure'=>'<closure>', // A closure that tests whether or not the query should be allowed to go forward. Tested in testMutateAndClosure
                                'mutate'=>'<closure>', // A closure to mutate information related to the placeholder before it is used in the query. Tested in testMutateAndClosure and testMutateUsed
                            ]
                        ]
                    ]
                ],
            ]
        ],
        'create'=>[
            'prePopulateEntities'=>'<boolean|null>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ],
        'update'=>[
            'prePopulateEntities'=>'<boolean|null>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ],
        'delete'=>[
            'prePopulateEntities'=>'<boolean|null>' // defaults to true, if true entities referenced in the params passed to CUD methods will be pre fetched using the minimum number of queries. // Tested in testPrePopulate
        ]
    ]
];

// Back end options are passed to a repository when create, read, update, delete methods.
$backendOptions = [ // note all options override query level options
    'options'=>[
        'paginate'=>'<boolean|null>', // Defaults to true. Whether or not paginate the results of a query. Tested in: testGeneralDataRetrieval
        'fetchJoin'=>'<boolean|null>', // Whether or not to use a fetch join on a paginated query result. Optional // Tested in: testGeneralDataRetrieval
        'hydrate'=>'<boolean|null>', // Defaults to true. Whether or not to hydrate the results of a query. If false then the query object is returned instead. Tested in: testGeneralDataRetrieval
        'hydrationType'=>'<doctrine hydration type>', // Defaults to: Query::HYDRATE_ARRAY. The hydration type for result sets. Tested in: testGeneralDataRetrieval
        '<string>'=>[  // Keys of placeholders to inject into queries. Optional // Tested in: testGeneralDataRetrieval
            'value'=>'<value>', // The value of the placeholder
            'type'=>'<param type | null>' // Optional type of the placeholders.
        ],
        'queryCacheProfile'=>'<a doctrine query cache profile>', // See Doctrine docs for more details. Optional // Used only by SQL queries. Use a QueryCacheProfile object
        'queryCacheDrive'=>'<driver for query cache>', // Query cache driver, see Doctrine docs for more details. Optional //Tested in: testGeneralQueryBuilding
        'resultCacheDrive'=>'<driver for query cache>', // Result cache driver, see Doctrine docs for more details. Optional //Tested in: testGeneralQueryBuilding
        'allowCache'=>'<boolean|null>', // Whether or not to allow cache. Optional //Tested in: testGeneralQueryBuilding
        'cacheId' => '<string>', // An optional cache id to use for the result cache. Optional //Tested in: testGeneralQueryBuilding
        'useQueryCache' => '<boolean|null>', // Whether ot not to use query cache. Optional //Tested in: testGeneralQueryBuilding
        'useResultCache' => '<boolean|null>', // Whether or not to use result cache. Optional //Tested in: testGeneralQueryBuilding
        'timeToLive' => '<int|null>',// Optional  // Time to live of the cache. Tested in: testGeneralQueryBuilding
        'tagSet' => '<future feature for tags sets in cache>', // In a future version you can pass in cache tags to assign to the result cache. Not yet implemented
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
$entityInfo = [
    '<string>'=>[ // Context keys may be layered as deep as you like. They are part of the contextual config nature of Scribe. See documentation for more details.
        'extends'=>['<an array of string paths to extend from else where in the array.>'],
        '<create | update | delete>'=>[ // Configs work the same for create, update and delete
            'allowed'=>'<boolean|null>', // Whether ot not this type of operation is permitted. Tested in: testAllowedWorks
            'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner.Tested in: testPermissiveWorks1
            'settings'=>[ // Settings related to the type of operation.
                'setTo'=>'<array|null>', // array of field names with values to set them to, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist. Tested In: testTopLevelSetToAndMutate
                'enforce'=>'<array|null>', // array of field names with values to make sure they match, if a field name is an association then an array should be given which will be run on the entity that is associated. Runs on prepersist. Tested in: testEnforceTopLevelWorks
                'closure'=>'<closure>', // A closure to test if the operation should go ahead. Tested in: testTopLevelClosure
                'mutate'=>'<closure>', // A closure to mutate the entity of prepersist. Tested In: testTopLevelSetToAndMutate
                'validate'=>[ // Validation settings passed a validator used on the class. Tested in: testValidatorWorks
                    'fields'=>['<array of strings>'], // if not set the keys from rules will be used instead. By default this works like the fields passed to a Laravel validator.
                    'rules'=>[
                        '<string>'=>'<string>' // Validator rules. By default these are rules that are used by laravel validator. The keys are the field names the rules apply to.
                    ], // Tested in: testValidatorWorks
                    'messages'=>['<string>'], // Custom validator messages to return on value. By default works with Laravel validator functionality.
                    'customAttributes'=>['<customAttributes>'], // Custom attributes to pass to the validator. By default works with Laravel validator functionality.
                ],
            ],
            'toArray'=>[ // Settings that dictate how an entity is converted to array.
                '<string>'=>[ // Key names that will be returned in the resulting array. If type is get this key equates to a field name on the entity.
                    'type'=>'<get | literal>',// Defaults to get. if 'get' then the key is a property but we get it by calling get<Property name>. If 'literal' then a value property must be included, the value property may be a closure that returns a value.  Tested in: testToArrayBasicFunctionality
                    'value'=>'<mixed|null>', // The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                    'format'=>'<string|null>', // format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                    'allowLazyLoad'=>'<boolean|null>', // Defaults to false, if true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
                ]
            ],
            'fields'=>[
                '<string>'=>[ // Field name that the permissions here relate to.
                    'permissive'=>'<boolean|null>', // Whether or not to use "permissive" permissions. Permissive true means that we assume that everything is allowed unless specified other wise, and false works in the opposite manner. Tested in: testPermissiveWorks1 / testPermissiveWorks2
                    'settings'=>[
                        'setTo'=>'<mixed|null>', // A value to set the field to. Tested in: testFastMode2AndLowLevelSetTo
                        'enforce'=>'<mixed|null>', // On a field then we test to make sure the field value matches. On an association then this value should be an array that compares the values to the properties being set on the association. Tested in: testLowLevelEnforce and testLowLevelEnforceOnRelation
                        'closure'=>'<closure>', // A closure that tests if the operation should be able to continue. Tested in testLowLevelClosure
                        'mutate'=>'<closure>', // A closure that mutates the values of the field before being committed to the DB. Tested in testLowLevelMutate
                    ],
                    'assign'=>[ // Types of assignment operations that allowed or disallowed. Note: all combinations of assign type as not tested, but there component parts are tested and shown to work.
                        'set'=>'<boolean|null>', // set<fieldname> method allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'add'=>'<boolean|null>', // add<fieldname> method allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'remove'=>'<boolean|null>', // remove<fieldname> method allowed or not. Tested in: testChainRemove
                        'setSingle'=>'<boolean|null>', // set<fieldname>. Works like set but changes pluralized field names to singular. method allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'addSingle'=>'<boolean|null>', // add<fieldname>. Works like add but changes pluralized field names to singular.Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'removeSingle'=>'<boolean|null>', // remove<fieldname>. Works like remove but changes pluralized field names to singular.Tested in: testChainRemove
                        'null'=>'<boolean|null>' // null assign types tell the system not to assign the entity that is manipulated. This is useful when an entity is already associated with another entity, but you want to interact with that entity any way. Tested in: testNullAssignType. Whether or not having no assign type is allowed
                    ],
                    'chain'=>[ // Types of chaining that are allow or not
                        'create'=>'<boolean|null>', // Create type chaining allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                        'update'=>'<boolean|null>', // Update type chaining allowed or not. Tested in: testUpdateWithChainAndEvents
                        'delete'=>'<boolean|null>', // Delete type chaining allowed or not. Tested in: testMultiDeleteAndEvents
                        'read'=>'<boolean|null>' // Read type chaining allowed or not. Tested in: testCreateAlbumAndArtistAndAddUserToAlbum
                    ]
                ]
            ],
            'options'=>[
                '<string>'=>'<mixed>', // reserved for custom use cases
            ]
        ],
        'read'=>[
            'toArray'=>[ // Settings that dictate how an entity is converted to array.
                '<string>'=>[ // Key names that will be returned in the resulting array. If type is get this key equates to a field name on the entity.
                    'type'=>'<get | literal>',// Defaults to get. if 'get' then the key is a property but we get it by calling get<Property name>. If 'literal' then a value property must be included, the value property may be a closure that returns a value.  Tested in: testToArrayBasicFunctionality
                    'value'=>'<mixed|null>', // The value to set the key to if it's a literal, a closure or array expression closure may be used.  Tested in: testToArrayBasicFunctionality
                    'format'=>'<string|null>', // format used if this is a date time field. By default sql date format is used.  Tested in: testToArrayBasicFunctionality
                    'allowLazyLoad'=>'<boolean|null>', // Defaults to false, if true then when a collection is encountered that isn't loaded, during the course of calling a get method, it will be lazy loaded from the db. Be careful because this can cause huge amounts of load if used with out caution.  Tested in: testToArrayBasicFunctionality
                ]
            ],
            'options'=>[
                '<custom option name>'=>'<custom option value>', // reserved for custom use cases
            ]
        ]
    ]
];























$controllerConfig = [
    '<http request mode>'=> [ // GET, POST, PUT, DELETE. Corresponds to the method that was requested
        'transaction'=>'<boolean|null>', // whether or not an additional transactions should be started at the controller level. This is useful if you mean to call one more than 1 repo in the controller
        'overrides'=>['<override key and value pairs>'], // overrides passed to the repo
        'transformerSettings'=>['<settings to be passed to the transformer, generally toArray settings>'],
        'resourceIdConversion'=>[ // This lets you set up resource ids passed in the url that are automatically converted to placeholders in the filter from the front end
            '<placeholder as it appearts in the url string>'=>'<query placeholders or null>'// If null then the placeholder will not be generated, other wise it will be converted to a placeholder with the name of value
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
        'returnCount'=>'<boolean|null>', // Tested in: testGeneralDataRetrieval
        'limit'=>'<limit>', //Tested in: testGeneralQueryBuilding
        'offset'=>'<offset>', //Tested in: testGeneralQueryBuilding
        'useGetParams'=>'<boolean|null>',// whether or not to expect the params to be in get format as illustrated below. Tested in testGeneralQueryBuildingWithGetParams
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
        'simplifiedParams'=>'<boolean|null>', //Defaults to false, may also be set as a different default on the controller // whether or not to process the params as standard version of simplified version. Both param examples are below.
        'testMode'=>'<boolean|null>', // Defaults to false, if set to true then data will be rolled back instead of committed. This lets you write test cases that use the api but not store anything to the db
        'toArray'=>[
            'completeness'=>'<full, limited, minimal, none>', // Defaults to full, if 'full' then all data will be shown so long as it wouldn't trigger an infinite loop, if 'limited' then all data will be shown but relations leading to already processed entities will not be shown, if 'minimal' the same entity will never be shown twice in the return and an empty array will be in it's place, if 'none' nothing is returned. Tested in: testToArrayBasicFunctionality
            'maxDepth'=>'<number or null>', // how deep should the to array go. Tested in: testToArrayBasicFunctionality
            'excludeKeys'=> ['<list of keys to exclude>'], // Use this to prevent certain keys from being converted to array to trim the return. Tested in: testToArrayBasicFunctionality
            'allowOnlyRequestedParams'=>'<boolean|null>',// Defaults to true, if true only the params that you requested to be changed on the entity will be shown in the return. This filters out any fields or associations that you did not request to be set directly with this request. Tested in: testToArrayBasicFunctionality
            'forceIncludeKeys'=>['<list of keys to include>'] // Defaults to: ['id'], these are keys to include in the result even if you didn't request to change them. Tested in: testToArrayBasicFunctionality
        ]
    ]
];

$toArrayTransformerSettings = [ // Tested in testToArrayArrayStorage and as part of all transformation tests
    'defaultMode'=>'<create, read, update, or delete>', // Defaults to 'read'. This is the mode that will be initiated on the entity if no mode is currently active on the entity
    'defaultArrayHelper'=>'<array helper>',// If no array helper is set for the entity already this one will be used
    'defaultPath'=>['<path>'],// If no path is set for the entity already this will be used
    'defaultFallBack'=>['<fall back>'], // If no fall back is set for the entity already this will be used
    'force'=>'<boolean|null>', // Defaults to false. If true then the entity will be force to use the path, mode, fall back and array helper that you are setting as defaults, regardless of if they have there own already.
    'store'=>'<boolean|null>', //Defaults to true. Whether or not the toArray result should be stored to be used again if toArray is called again.
    'recompute'=>'<boolean|null>', // Defaults to false. Whether or not to recompute toArray, even if one was previously stored
    'useStored'=>'<boolean|null>', // Defaults to true. Whether or not to use a previously stored toArray result if one is found. If false then it will return a freshly generated result.
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