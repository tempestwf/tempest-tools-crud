<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/15/2017
 * Time: 3:15 PM
 */

namespace TempestTools\Crud\Doctrine\Transformers;


use Doctrine\Common\Collections\Collection;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use Doctrine\Common\Proxy\Proxy;
use TempestTools\Crud\Contracts\Orm\Transformers\SimpleTransformerContract;

abstract class SimpleTransformerAbstract implements SimpleTransformerContract
{
    /**
     * @param EntityContract $entity
     */
    abstract public function convert (EntityContract $entity);

    /**
     * @param $entity
     * @return mixed
     */
    public function item ($entity)
    {
        if ($this->verifyItem($entity) === true) {
            return $this->convert($entity);
        }
        return null;
    }

    /**
     * @param EntityContract $entity
     * @return bool
     */
    public function verifyItem(EntityContract $entity): bool
    {
        if($entity instanceof Proxy)
        {
            try
            {
                if($entity->__isInitialized() === FALSE)
                {
                    $entity->__load();
                }
            } catch(\Exception $ex)
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * @param Collection $collection
     * @return array
     */
    public function collection (Collection $collection):array
    {
        $return = [];
        foreach ($collection as $entity) {
            $return[] = $this->item($entity);
        }
        return $return;
    }

    /**
     * @param array $array
     * @return array
     * @internal param Collection $collection
     */
    public function array (array $array):array
    {
        $return = [];
        foreach ($array as $entity) {
            $return[] = $this->item($entity);
        }
        return $return;
    }

    /**
     * @param $subject
     * @return mixed
     */
    public function transform ($subject)
    {
        if (is_array($subject)) {
            return $this->array($subject);
        }

        if ($subject instanceof EntityContract) {
            return $this->item($subject);
        }

        if ($subject instanceof Collection) {
            return $this->collection($subject);
        }
        return null;
    }


}