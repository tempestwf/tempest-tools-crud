<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventSubscriber;
use TempestTools\AclMiddleware\Contracts\HasIdContract;
use TempestTools\Crud\Contracts\EntityContract;


/** @noinspection PhpSuperClassIncompatibleWithInterfaceInspection
 * Note: PHP Storm is mistaken, this does match with the contract
 */
abstract class EntityAbstract implements EventSubscriber, HasIdContract, EntityContract
{
    use EntityTrait;
    const ERRORS = [
        'noArrayHelper' => [
            'message' => 'Error: No array helper on entity.',
        ],
        'enforcementFails' => [
            'message' => 'Error: A field is not set to it\'s enforced value. Value is %s, value should be %s',
        ],
        'closureFails' => [
            'message' => 'Error: A validation closure did not pass.',
        ],
        'prePersistValidatorFails' => [
            'message' => 'Error: Validation failed on pre-persist.',
        ],
    ];

}
?>