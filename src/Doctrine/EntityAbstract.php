<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventSubscriber;
use TempestTools\AclMiddleware\Contracts\HasIdContract;
use TempestTools\Crud\Contracts\EntityContract;
use TempestTools\Crud\Contracts\GenericEventArgsContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Orm\EntityTrait;


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

    /**
     * Makes event args to use
     *
     * @param array $params
     * @return GenericEventArgsContract
     */
    public function makeEventArgs(array $params): GenericEventArgsContract
    {
        return new GenericEventArgs(new \ArrayObject(['params' => $params, 'configArrayHelper' => $this->getConfigArrayHelper(), 'arrayHelper' => $this->getArrayHelper(), 'self' => $this]));
    }
}
?>