<?php
namespace TempestTools\Crud\Doctrine;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\PersistentCollection;
use TempestTools\AclMiddleware\Contracts\HasIdContract;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Doctrine\Utility\CreateEventManagerWrapperTrait;
use TempestTools\Crud\Orm\EntityCoreTrait;


/**
 * An abstract class for that Doctrine entities must extend to use the functionality of this package.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
abstract class EntityAbstract implements EventSubscriber, HasIdContract, EntityContract
{
    use EntityCoreTrait, CreateEventManagerWrapperTrait;

    /**
     * The default format for date times
     */
    const DEFAULT_DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    /**
     * Makes event args to use
     *
     * @param array $params
     * @return GenericEventArgsContract
     */
    public function makeEventArgs(array $params): GenericEventArgsContract
    {
        return new GenericEventArgs(new \ArrayObject(['params' => $params, 'configArrayHelper' => $this->getConfigArrayHelper(), 'arrayHelper' => $this->getArrayHelper(), 'self' => $this]));
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Utilized by toArray functionality to convert a property of the entity to an array representation.
     * @param $propertyValue
     * @param array $settings
     * @param array $requestedSettings
     * @param mixed $slatedToTransform
     * @return mixed
     */
    public function parseToArrayPropertyValue($propertyValue, array $settings = [], array $requestedSettings, $slatedToTransform = null) {
        $requestedSettings['defaultArrayHelper'] = $this->getArrayHelper();
        $requestedSettings['defaultPath'] = $this->getTTPath();
        $requestedSettings['defaultFallBack'] = $this->getTTFallBack();
        if (is_object ($propertyValue) === true) {
            if ($propertyValue instanceof EntityContract) {
                return $propertyValue->toArray($requestedSettings, $slatedToTransform);
            }

            if ($propertyValue instanceof DateTimeInterface) {
                $format = $settings['format'] ?? static::DEFAULT_DATE_TIME_FORMAT;
                return [
                    'timezoneName'=>$propertyValue->getTimezone()->getName(),
                    'timestamp'=>$propertyValue->getTimestamp(),
                    'offset'=>$propertyValue->getOffset(),
                    'formatted'=>$propertyValue->format($format),
                ];
            }

            if ($propertyValue instanceof Collection) {
                $allowLazyLoad = $settings['allowLazyLoad'] ?? false;
                $return = [];
                /** @var PersistentCollection $propertyValue */
                if (
                    $allowLazyLoad === true
                    ||
                    $propertyValue instanceof PersistentCollection === false
                    ||
                    $propertyValue->isInitialized()
                ) {
                    foreach ($propertyValue as $entity) {
                        if ($entity instanceof EntityContract) {
                            $return[] = $entity->toArray($requestedSettings, $slatedToTransform);
                        }
                    }
                }

                return $return;
            }

            if (method_exists($propertyValue, 'toString')) {
                return $propertyValue->toString();
            }

            if (method_exists($propertyValue, 'toArray')) {
                return $propertyValue->toArray();
            }
        }

        return $propertyValue;

    }

}
?>