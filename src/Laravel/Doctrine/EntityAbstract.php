<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/15/2017
 * Time: 5:59 PM
 */

namespace TempestTools\Scribe\Laravel\Doctrine;

use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Laravel\Validation\ValidationFactoryHelper;
use TempestTools\Common\Utility\ValidationFactoryTrait;
use \Illuminate\Contracts\Validation\Factory;
use TempestTools\Scribe\Doctrine\EntityAbstract as EntityAbstractBase;
use TempestTools\Scribe\Exceptions\Orm\EntityException;
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

    }

    /**
       * Initialization the entity with helpers and and config context.
       * @param string $mode
       * @param ArrayHelperContract|null $arrayHelper
       * @param array|null $path
       * @param array|null $fallBack
       * @param bool $force
       * @throws \RuntimeException
       */
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = false):void
    {
        $this->setValidationFactoryHelper(new ValidationFactoryHelper());

        $force = $this->coreInit($arrayHelper, $path, $fallBack, $force, $mode);
        $this->entityArrayHelperInit($force, $mode);
        $this->eventManagerInit($force);
        $this->setLastMode($mode);
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
     * @throws \TempestTools\Scribe\Exceptions\Orm\EntityException
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