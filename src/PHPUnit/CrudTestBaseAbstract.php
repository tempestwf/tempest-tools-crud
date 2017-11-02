<?php

namespace TempestTools\Crud\PHPUnit;

use App\API\V1\Entities\User;
use TempestTools\Common\Doctrine\Utility\MakeEmTrait;
use TempestTools\Common\Helper\ArrayHelper;


/** @noinspection LongInheritanceChainInspection */
/**
 * A base class with data on it to make building tests for the package easier
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
abstract class CrudTestBaseAbstract extends \TestCase
{
    use MakeEmTrait;

    public function createRobAndBobData():array
    {
        return [
            [
                'name'=>'bob',
                'email'=>'bob@bob.com',
                'password'=>'bobsyouruncle'
            ],
            [
                'name'=>'rob',
                'email'=>'rob@rob.com',
                'password'=>'norobsyouruncle'
            ],
        ];
    }


    /**
     * @return array
     */
    public function createData (): array
    {
        return [
            [
                'name'=>'BEETHOVEN: THE COMPLETE PIANO SONATAS',
                'releaseDate'=>new \DateTime('now'),
                'artist'=>[
                    'create'=>[
                        [
                            'name'=>'BEETHOVEN',
                            'assignType'=>'set',
                        ],
                    ],
                ],
                'users'=>[
                    'read'=>[
                        '1'=>[
                            'assignType'=>'addSingle',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $userIds
     * @return array
     */
    public function createArtistChainData (array $userIds):array {
        return [
            [
                'name'=>'BEETHOVEN',
                'albums'=>[
                    'create'=> [
                        [
                            'name'=> 'BEETHOVEN: THE COMPLETE PIANO SONATAS',
                            'assignType'=>'addSingle',
                            'releaseDate'=>new \DateTime('now'),
                            'users'=>[
                                'read'=> [
                                    $userIds[0]=>[
                                        'assignType'=>'addSingle',
                                    ],
                                    $userIds[1]=>[
                                        'assignType'=>'addSingle',
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name'=> 'BEETHOVEN: THE COMPLETE STRING QUARTETS',
                            'assignType'=>'addSingle',
                            'releaseDate'=>new \DateTime('now'),
                            'users'=>[
                                'read'=> [
                                    $userIds[0]=>[
                                        'assignType'=>'addSingle',
                                    ],
                                    $userIds[1]=>[
                                        'assignType'=>'addSingle',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name'=>'BACH',
                'albums'=>[
                    'create'=> [
                        [
                            'name'=> 'Amsterdam Baroque Orchestra',
                            'assignType'=>'addSingle',
                            'releaseDate'=>new \DateTime('now'),
                            'users'=>[
                                'read'=> [
                                    $userIds[0]=>[
                                        'assignType'=>'addSingle',
                                    ],
                                    $userIds[1]=>[
                                        'assignType'=>'addSingle',
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name'=> 'The English Suites',
                            'assignType'=>'addSingle',
                            'releaseDate'=>new \DateTime('now'),
                            'users'=>[
                                'read'=> [
                                    $userIds[0]=>[
                                        'assignType'=>'addSingle',
                                    ],
                                    $userIds[1]=>[
                                        'assignType'=>'addSingle',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return ArrayHelper
     * @throws \TempestTools\Common\Exceptions\Helper\ArrayHelperException
     */
    public function makeArrayHelper ():ArrayHelper {
        /** @var User $repo */
        $userRepo = $this->em->getRepository(User::class);
        $user = $userRepo->findOneBy(['id'=>1]);
        $arrayHelper = new ArrayHelper();
        $arrayHelper->extract([$user]);
        return $arrayHelper;
    }

    /**
     * @return array
     */
    protected function makeFrontEndQueryOptions():array
    {
        return [
            'returnCount'=>true,
            'limit'=>1,
            'offset'=>1,
        ];
    }

    /**
     * @return array
     */
    protected function makeTestFrontEndQueryArtist(): array
    {
        return [
            'query'=>[
                'where'=>[
                    [
                        'operator'=>'andX',
                        'conditions'=>[
                            [
                                'field'=>'t.name',
                                'operator'=>'eq',
                                'arguments'=>['BEETHOVEN1']
                            ],
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['Bob Marley']
                            ]
                        ]
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'eq',
                        'arguments'=>['BEETHOVEN3']
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'eq',
                        'arguments'=>['BEETHOVEN4']
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'neq',
                        'arguments'=>['Blink 182']
                    ],
                    [
                        'field'=>'t.id',
                        'type'=>'and',
                        'operator'=>'lt',
                        'arguments'=>[99999991]
                    ],
                    [
                        'field'=>'t.id',
                        'type'=>'and',
                        'operator'=>'lte',
                        'arguments'=>[99999992]
                    ],
                    [
                        'field'=>'t.id',
                        'type'=>'and',
                        'operator'=>'gt',
                        'arguments'=>[-1]
                    ],
                    [
                        'field'=>'t.id',
                        'type'=>'and',
                        'operator'=>'gte',
                        'arguments'=>[-2]
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'in',
                        'arguments'=>[['BEETHOVEN5']]
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'notIn',
                        'arguments'=>[['Vanilla Ice']]
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'isNull',
                        'arguments'=>[]
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'isNotNull',
                        'arguments'=>[]
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'like',
                        'arguments'=>['%BEETHOV%']
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'notLike',
                        'arguments'=>['%The Ruttles%']
                    ],
                    [
                        'field'=>'t.id',
                        'type'=>'and',
                        'operator'=>'between',
                        'arguments'=>[0,99999993]
                    ],
                    [
                        'type'=>'or',
                        'operator'=>'orX',
                        'conditions'=>[
                            [
                                'field'=>'t.name',
                                'operator'=>'eq',
                                'arguments'=>['BEETHOVEN2']
                            ],
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['Urethra Franklin']
                            ]
                        ]
                    ],
                ],
                'having'=>[
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'eq',
                        'arguments'=>['BEETHOVEN7']
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'or',
                        'operator'=>'eq',
                        'arguments'=>['BEETHOVEN6']
                    ],
                ],
                'orderBy'=>[
                    't.name'=>'ASC',
                    't.id'=>'DESC'
                ],
                'groupBy'=>[
                    't.name',
                    't.id'
                ],
                'placeholders'=>[
                    'frontEndTestPlaceholder'=>[
                        'value'=>777,
                        'type'=>'integer'
                    ],
                    'frontEndTestPlaceholder2'=>[
                        'value'=>'stuff2',
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    protected function makeTestFrontEndQueryArtistGetParams(): array
    {
        return [
            'and_where_andX'=>json_encode([
                [
                    'field'=>'t.name',
                    'operator'=>'eq',
                    'arguments'=>['BEETHOVEN1']
                ],
                [
                    'field'=>'t.name',
                    'operator'=>'neq',
                    'arguments'=>['Bob Marley']
                ]
            ],true),
            'and_where_eq_t-name'=>'BEETHOVEN3',
            'and_where_eq_t-name_2'=>'BEETHOVEN4',
            'and_where_neq_t-name'=>'Blink 182',
            'and_where_lt_t-id'=>99999991,
            'and_where_lte_t-id'=>99999992,
            'and_where_gt_t-id'=>-1,
            'and_where_gte_t-id'=>-2,
            'and_where_in_t-name'=>['BEETHOVEN5'],
            'and_where_notIn_t-name'=>['Vanilla Ice'],
            'and_where_isNull_t-name'=>'',
            'and_where_isNotNull_t-name'=>'',
            'and_where_like_t-name'=>'%BEETHOV%',
            'and_where_notLike_t-name'=>'%The Ruttles%',
            'and_where_between_t-id'=>[0,99999993],
            'or_where_orX'=>json_encode([
                [
                    'field'=>'t.name',
                    'operator'=>'eq',
                    'arguments'=>['BEETHOVEN2']
                ],
                [
                    'field'=>'t.name',
                    'operator'=>'neq',
                    'arguments'=>['Urethra Franklin']
                ]
            ]),
            'and_having_eq_t-name'=>'BEETHOVEN7',
            'or_having_eq_t-name_2'=>'BEETHOVEN6',
            'orderBy_t-name'=>'ASC',
            'orderBy_t-id'=>'DESC',
            'groupBy'=>[
                't-name',
                't-id'
            ],
            'placeholder_frontEndTestPlaceholder_integer'=>777,
            'placeholder_frontEndTestPlaceholder2'=>'stuff2',
            'option_returnCount'=>1,
            'option_limit'=>1,
            'option_offset'=>1,
        ];
    }




    /**
     * @return array
     */
    protected function makeTestFrontEndQueryArtist2(): array
    {
        return [
            'query'=>[
                'where'=>[
                    [
                        'type'=>'and',
                        'operator'=>'andX',
                        'conditions'=>[
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['BEEFOVEN']
                            ],
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['Bob Marley']
                            ],
                            [
                                'field'=>'t.name',
                                'operator'=>'in',
                                'arguments'=>[['Brahms', 'BEETHOVEN', 'BACH']]
                            ]
                        ]
                    ],
                    [
                        'type'=>'and',
                        'operator'=>'andX',
                        'conditions'=>[
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['Urethra Franklin']
                            ],
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['Khaaan!!']
                            ]
                        ]
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'neq',
                        'arguments'=>['Gossepi the squid']
                    ],
                ],
                'having'=>[
                    [
                        'field'=>'t.id',
                        'type'=>'or',
                        'operator'=>'neq',
                        'arguments'=>[0]
                    ],
                    [
                        'field'=>'t.id',
                        'type'=>'or',
                        'operator'=>'neq',
                        'arguments'=>[0]
                    ],
                ],
                'placeholders'=>[
                    'frontEndTestPlaceholder'=>[
                        'value'=>777,
                        'type'=>'integer'
                    ],
                    'frontEndTestPlaceholder2'=>[
                        'value'=>'stuff2',
                        'type'=>'string'
                    ]
                ]
            ]
        ];
    }



    /**
     * @return array
     */
    protected function makeTestFrontEndQueryArtist3(): array
    {
        return [
            'query'=>[
                'where'=>[
                    [
                        'type'=>'and',
                        'operator'=>'andX',
                        'conditions'=>[
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['BEEFOVEN']
                            ],
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['Bob Marley']
                            ],
                            [
                                'field'=>'t.name',
                                'operator'=>'eq',
                                'arguments'=>['Brahms']
                            ]
                        ]
                    ],
                    [
                        'type'=>'and',
                        'operator'=>'andX',
                        'conditions'=>[
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['Urethra Franklin']
                            ],
                            [
                                'field'=>'t.name',
                                'operator'=>'neq',
                                'arguments'=>['Khaaan!!']
                            ]
                        ]
                    ],
                    [
                        'field'=>'t.name',
                        'type'=>'and',
                        'operator'=>'neq',
                        'arguments'=>['Gossepi the squid']
                    ],
                ],
                'having'=>[
                    [
                        'field'=>'t.id',
                        'type'=>'or',
                        'operator'=>'neq',
                        'arguments'=>[0]
                    ],
                    [
                        'field'=>'t.id',
                        'type'=>'or',
                        'operator'=>'neq',
                        'arguments'=>[0]
                    ],
                ],
                'placeholders'=>[
                    'frontEndTestPlaceholder'=>[
                        'value'=>777,
                        'type'=>'integer'
                    ],
                    'frontEndTestPlaceholder2'=>[
                        'value'=>'stuff2',
                        'type'=>'string'
                    ]
                ]
            ]
        ];
    }


    /**
     * @return string
     */
    protected function getToken ():string {
        $userRepo = $this->em->getRepository(User::class);

        $testUser = $userRepo->findOneBy(['id'=>1]);

        $response = $this->json('POST', '/auth/authenticate', ['email' => $testUser->getEmail(), 'password' => $testUser->getPassword()]);
        $result = $response->decodeResponseJson();

        /** @var string $token */
        $token = $result['token'];
        $this->refreshApplication();
        return $token;
    }
}
