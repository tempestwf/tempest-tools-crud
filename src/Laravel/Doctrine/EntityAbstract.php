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
/**
 * An abstract class which Doctrine entities must extend to use the functionality of this package
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
abstract class EntityAbstract extends EntityAbstractBase
{
    use ValidationFactoryTrait;

    /**
     * EntityAbstract constructor.
     */
    public function __construct()
    {
        $this->setValidationFactoryHelper(new ValidationFactoryHelper());
    }

    /**
     * Gets the validator factory to be used in validation
     *
     * @throws \RuntimeException
     */
    public function getValidationFactory() : Factory {

        /** @noinspection NullPointerExceptionInspection */
        return $this->getValidationFactoryHelper()->getValidationFactory();
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Uses the validation factory to validate the data passed to the method.
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