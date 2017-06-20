<?php
namespace TempestTools\Crud\Doctrine\Helper;


use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;

class EntityArrayHelper extends ArrayHelper {
    use TTConfigTrait, ErrorConstantsTrait;

    const ERRORS = [
        'chainTypeNotAllow'=>[
            'message'=>'Error: Requested chain type not permitted.',
        ],
        'assignTypeNotAllow'=>[
            'message'=>'Error: Requested assign type not permitted.',
        ],
        'actionNotAllow'=>[
            'message'=>'Error: the requested action is not allowed on this entity for this request.'
        ]
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

        if ($nosey === true && $allowed === false) {
            throw new \RuntimeException($this->getErrorFromConstant('chainTypeNotAllow')['message']);
        }

        return $allowed;
    }

    /**
     * @param string $associationName
     * @param string $assignType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canAssign (string $associationName, string $assignType, bool $nosey = true):bool {
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->parseArrayPath(['fields', $associationName]);

        $allowed = $this->permissivePermissionCheck($actionSettings, $fieldSettings, 'assign', $assignType);

        if ($nosey === true && $allowed === false) {
            throw new \RuntimeException($this->getErrorFromConstant('assignTypeNotAllow')['message']);
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
        return $this->highLowSettingCheck($actionSettings, $fieldSettings, 'fastMode');
    }
}
?>