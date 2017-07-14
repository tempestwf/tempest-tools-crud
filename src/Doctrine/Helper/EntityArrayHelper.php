<?php
namespace TempestTools\Crud\Doctrine\Helper;


use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Contracts\EntityArrayHelper as EntityArrayHelperContract;

class EntityArrayHelper extends ArrayHelper implements EntityArrayHelperContract{
    use TTConfigTrait, ErrorConstantsTrait, ArrayHelperTrait;

    const ERRORS = [
        'chainTypeNotAllow'=>[
            'message'=>'Error: Requested chain type not permitted. chainType = %s, relationName = %s.',
        ],
        'assignTypeNotAllow'=>[
            'message'=>'Error: Requested assign type not permitted. assignType = %s, fieldName = %s.',
        ],
        'actionNotAllow'=>[
            'message'=>'Error: the requested action is not allowed on this entity for this request.'
        ],
        'enforcementFails' => [
            'message' => 'Error: A field is not set to it\'s enforced value. fieldName = %s.',
        ],
        'closureFails' => [
            'message' => 'Error: A validation closure did not pass. fieldName = %s.',
        ],
    ];

    /**
     * @param string $fieldName
     * @param string $keyName
     * @return mixed
     * @throws \RuntimeException
     */
    public function getConfigForField(string $fieldName, string $keyName)
    {
        return $this->parseArrayPath(['fields', $fieldName, $keyName]);
    }

    /**
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canChain (string $associationName, string $chainType, bool $nosey = true):bool {
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->parseArrayPath(['fields', $associationName]);

        $allowed = $this->permissivePermissionCheck($actionSettings, $fieldSettings, 'chain', $chainType);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, ['associationName'=>$associationName, 'chainType'=> $chainType, 'self'=>$this]);
        if ($nosey === true && $allowed === false) {
            throw new \RuntimeException(sprintf($this->getErrorFromConstant('chainTypeNotAllow')['message'], $chainType, $associationName));
        }

        return $allowed;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $associationName
     * @param string $assignType
     * @param array $fieldSettings
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canAssign (string $associationName, string $assignType, array $fieldSettings = NULL, bool $nosey = true):bool {
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $fieldSettings ?? $this->getFieldSettings($associationName);

        $allowed = $this->permissivePermissionCheck($actionSettings, $fieldSettings, 'assign', $assignType);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, ['associationName'=>$associationName, 'assignType'=> $assignType, 'self'=>$this]);
        if ($nosey === true && $allowed === false) {
            throw new \RuntimeException(sprintf($this->getErrorFromConstant('assignTypeNotAllow')['message'], $assignType, $associationName));
        }
        return $allowed;
    }

    /**
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function allowed ($nosey = true):bool {

        /** @noinspection NullPointerExceptionInspection */
        $array = $this->getArray();
        $allowed = $array['allowed'] ?? true;

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, ['self'=>$this]);
        if ($nosey === true && $allowed === false) {
            throw new \RuntimeException($this->getErrorFromConstant('actionNotAllow')['message']);
        }
        return $allowed;
    }

    /**
     * @param string $fieldName
     * @return bool
     * @throws \RuntimeException
     */
    public function checkFastMode(string $fieldName):bool {

        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->parseArrayPath(['fields', $fieldName]);
        $fastMode = $this->highLowSettingCheck($actionSettings, $fieldSettings, 'fastMode');
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->getArrayHelper()->parse($fastMode, ['fieldName'=>$fieldName, 'self'=>$this]);
        return $fastMode;
    }

    /**
     * @param string $fieldName
     * @param $params
     * @return array
     * @throws \RuntimeException
     */
    public function getFieldSettings (string $fieldName, array $params = []):array {
        $fieldSettings = $this->parseArrayPath(['fields', $fieldName], $params);
        $fieldSettings = $fieldSettings??[];
        return $fieldSettings;
    }

    /**
     * @param array $params
     * @return mixed
     * @throws \RuntimeException
     */
    public function processSetField (array $params) {
        $fieldName = $params['fieldName'];
        $value = $params['value'];

        $fieldSettings = $this->getFieldSettings($fieldName, $params);

        /** @noinspection NullPointerExceptionInspection */
        $this->canAssign($fieldName, 'set', $fieldSettings);
        $this->enforceField($fieldName, $value, $params, $fieldSettings);
        $this->closureOnField($fieldName, $params, $fieldSettings);
        $value = $this->setToOnField($fieldName, $params, $value, $fieldSettings);
        $value = $this->mutateOnField($fieldName, $params, $value, $fieldSettings);
        return $value;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $nosey
     * @throws \RuntimeException
     */
    public function enforceRelation(string $fieldName, array $values, array $params = [], array $fieldSettings=NULL, bool $nosey = true) {
        // Get the settings for the field so we can do quick comparisons
        $fieldSettings = $fieldSettings ?? $this->getFieldSettings($fieldName);

        // Check if fields that are needed to be enforced as enforced
        /** @noinspection NullPointerExceptionInspection */
        $enforce = isset($fieldSettings['enforce']) ? $this->getArrayHelper()->parse($fieldSettings['enforce'], $params) : [];

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->testEnforceValues($values, $enforce, $params);

        if ($allowed === false && $nosey === true) {
            throw new \RuntimeException(sprintf($this->getErrorFromConstant('enforcementFails')['message'], $fieldName));
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $associationName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @throws \RuntimeException
     * @return array
     */
    public function setToOnAssociation(string $associationName, array $values, array $params = [], array $fieldSettings=NULL):array {
        $fieldSettings = $fieldSettings ?? $this->getFieldSettings($associationName);
        // Figure out if there are values that need to be set to, and set it to those values if any found
        /** @noinspection NullPointerExceptionInspection */
        $setTo = isset($fieldSettings['setTo']) ? $this->getArrayHelper()->parse($fieldSettings['setTo'], $params) : [];

        if ($setTo !== null) {
            $values = array_replace_recursive($values, $setTo);
        }
        return $values;
    }

    /**
     * @param array $params
     * @throws \RuntimeException
     * @return array
     */
    public function processAssociationParams(array $params):array {
        $associationName = $params['associationName'];
        $values = $params['values'];

        $fieldSettings = $this->getFieldSettings($associationName, $params);

        // Check if assignment and chaining settings are allowed
        $assignType = $values['assignType'] ?? 'set';
        $chainType = $values['chainType'] ?? null;
        if ($chainType !== null) {
            /** @noinspection NullPointerExceptionInspection */
            $this->canChain($associationName, $chainType);
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->canAssign($associationName, $assignType);
        $this->enforceRelation($associationName, $values, $params, $fieldSettings);
        $this->closureOnField($associationName, $params, $fieldSettings);
        $values = $this->setToOnAssociation($associationName, $values, $params, $fieldSettings);
        $values = $this->mutateOnField($associationName, $params, $values, $fieldSettings);
        return $values;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     * @throws \RuntimeException
     */
    public function mutateOnField (string $fieldName, $params, $value, array $fieldSettings = NULL) {
        // Get the settings for the field so we can do quick comparisons
        $fieldSettings = $fieldSettings ?? $this->getFieldSettings($fieldName);
        /** @noinspection NullPointerExceptionInspection */
        $value = isset($fieldSettings['mutate']) ? $this->getArrayHelper()->parse($fieldSettings['mutate'], $params) : $value;
        return $value;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     * @throws \RuntimeException
     */
    public function setToOnField (string $fieldName, $params, $value, array $fieldSettings = NULL) {
        // Get the settings for the field so we can do quick comparisons
        $fieldSettings = $fieldSettings ?? $this->getFieldSettings($fieldName);
        /** @noinspection NullPointerExceptionInspection */
        $value = isset($fieldSettings['setTo']) ? $this->getArrayHelper()->parse($fieldSettings['setTo'], $params) : $value;
        return $value;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function closureOnField (string $fieldName, array $params, array $fieldSettings = NULL, bool $noisy = true):bool {
        // Get the settings for the field so we can do quick comparisons
        $fieldSettings = $fieldSettings ?? $this->getFieldSettings($fieldName);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($fieldSettings['closure']) && $this->getArrayHelper()->parse($fieldSettings['closure'], $params) === false);

        if ($allowed === false && $noisy === true) {
            throw new \RuntimeException(sprintf($this->getErrorFromConstant('closureFails')['message']), $fieldName);
        }
        return $allowed;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function enforceField (string $fieldName, $value, array $params, array $fieldSettings = NULL, bool $noisy = true):bool {
        // Get the settings for the field so we can do quick comparisons
        $fieldSettings = $fieldSettings ?? $this->getFieldSettings($fieldName);
        // Additional validation
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($fieldSettings['enforce']) && $this->getArrayHelper()->parse($value, $params) !== $this->getArrayHelper()->parse($fieldSettings['enforce'], $params));

        // Any validation failure error out
        if ($allowed === false && $noisy === true) {
            throw new \RuntimeException(sprintf($this->getErrorFromConstant('enforcementFails')['message']), $fieldName);
        }

        return $allowed;
    }
}
?>