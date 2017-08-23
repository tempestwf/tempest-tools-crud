<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/15/2017
 * Time: 5:59 PM
 */

namespace TempestTools\Crud\Laravel\Doctrine;

use TempestTools\Common\Laravel\Validation\ValidationFactoryHelper;
use TempestTools\Common\Utility\ValidationFactoryTrait;
use \Illuminate\Contracts\Validation\Factory;
use TempestTools\Crud\Doctrine\EntityAbstract as EntityAbstractBase;
use TempestTools\Crud\Exceptions\Orm\EntityException;

abstract class EntityAbstract extends EntityAbstractBase
{
    use ValidationFactoryTrait;

    public function __construct()
    {
        $this->setValidationFactoryHelper(new ValidationFactoryHelper());
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

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     *
     * @param array $values
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @throws \TempestTools\Crud\Exceptions\Orm\EntityException
     * @throws \RuntimeException
     */
    public function validate(array $values, array $rules, array $messages = [], array $customAttributes = []):void
    {
        /** @var Factory $factory */
        $factory = $this->getValidationFactory();
        $validator = $factory->make($values, $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag()->all();
            $errorMessage = implode(' \n', $messages);
            $errorMessage = $errorMessage === ''?EntityException::prePersistValidatorFails():$errorMessage;
            throw new EntityException($errorMessage);
        }
    }

}