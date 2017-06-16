<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/15/2017
 * Time: 5:59 PM
 */

namespace TempestTools\Crud\Laravel;

use TempestTools\Common\Laravel\Validation\ValidationFactoryHelper;
use TempestTools\Common\Utility\ValidationFactoryTrait;
use \Illuminate\Contracts\Validation\Factory;

class EntityAbstract extends \TempestTools\Crud\Doctrine\EntityAbstract
{
    use ValidationFactoryTrait;

    public function __construct()
    {
        $this->setValidationFactoryHelper(new ValidationFactoryHelper());
        parent::__construct();
    }

    /**
     * Needs extending in a child class to get a validation factory to use
     *
     * @throws \RuntimeException
     */
    public function getValidationFactory() : Factory {

        /** @noinspection NullPointerExceptionInspection */
        return $this->getValidationFactoryHelper()->getValidationFactory();
    }

}