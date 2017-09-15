<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/15/2017
 * Time: 3:51 PM
 */

namespace TempestTools\Crud\Contracts\Orm\Transformers;

use Doctrine\Common\Collections\Collection;
use TempestTools\Crud\Contracts\Orm\EntityContract;

interface SimpleTransformerContract
{
    /**
     * @param EntityContract $entity
     */
    public function convert(EntityContract $entity);

    /**
     * @param $entity
     * @return mixed
     */
    public function item($entity);

    /**
     * @param EntityContract $entity
     * @return bool
     */
    public function verifyItem(EntityContract $entity): bool;

    /**
     * @param Collection $collection
     * @return array
     */
    public function collection(Collection $collection): array;

    /**
     * @param array $array
     * @return array
     * @internal param Collection $collection
     */
    public function array(array $array): array;

    /**
     * @param $subject
     * @return mixed
     */
    public function transform($subject);
}