<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use TempestTools\AclMiddleware\Contracts\HasIdContract;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Doctrine\Utility\CreateEventManagerWrapperTrait;
use TempestTools\Crud\Orm\EntityCoreTrait;


/** @noinspection PhpSuperClassIncompatibleWithInterfaceInspection
 * Note: PHP Storm is mistaken, this does match with the contract
 */
abstract class EntityAbstract implements EventSubscriber, HasIdContract, EntityContract
{
    use EntityCoreTrait, CreateEventManagerWrapperTrait;


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

    /**
     * @param $propertyValue
     * @param bool $force
     * @return mixed
     * @throws \RuntimeException
     */
    protected function parseToArrayPropertyValue($propertyValue, bool $force = false) {
        $arrayHelper = $this->getArrayHelper();
        $path = $this->getTTPath();
        $fallBack = $this->getTTFallBack();
        if ($propertyValue instanceof EntityContract) {
            return $propertyValue->toArray('read', $arrayHelper, $path, $fallBack, $force);
        }

        if ($propertyValue instanceof Collection) {
            $return = [];
            foreach ($propertyValue as $entity) {
                $return[] = $entity->toArray('read', $arrayHelper, $path, $fallBack, $force);
            }
            return $return;
        }
        return $propertyValue;

    }

}
?>