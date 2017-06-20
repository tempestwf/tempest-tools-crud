<?php

namespace TempestTools\Crud\Contracts;

use TempestTools\Common\Contracts\ArrayHelper;

interface EntityArrayHelper extends ArrayHelper
{

    /**
     * @param string $fieldName
     * @param string $keyName
     * @return mixed
     */
    public function getConfigForField(string $fieldName, string $keyName);

    /**
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     */
    public function canChain(string $associationName, string $chainType, bool $nosey = true): bool;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $associationName
     * @param string $assignType
     * @param array|null $fieldSettings
     * @param bool $nosey
     * @return bool
     */
    public function canAssign(string $associationName, string $assignType, array $fieldSettings = null, bool $nosey = true): bool;

    /**
     * @param bool $nosey
     * @return bool
     */
    public function allowed($nosey = true): bool;

    /**
     * @param string $fieldName
     * @return bool
     */
    public function checkFastMode(string $fieldName): bool;

    /**
     * @param string $fieldName
     * @param $params
     * @return array
     * @throws \RuntimeException
     */
    public function getFieldSettings(string $fieldName, array $params = []): array;

    /**
     * @param array $params
     * @return mixed
     * @throws \RuntimeException
     */
    public function processSetField(array $params);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $nosey
     * @throws \RuntimeException
     */
    public function enforceRelation(string $fieldName, array $values, array $params = [], array $fieldSettings=NULL, bool $nosey = true);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $associationName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @throws \RuntimeException
     * @return array
     */
    public function setToOnAssociation(string $associationName, array $values, array $params = [], array $fieldSettings=NULL):array;

    /**
     * @param array $params
     * @throws \RuntimeException
     * @return array
     */
    public function processAssociationParams(array $params):array;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     * @throws \RuntimeException
     */
    public function mutateOnField(string $fieldName, $params, $value, array $fieldSettings = null);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     * @throws \RuntimeException
     */
    public function setToOnField(string $fieldName, $params, $value, array $fieldSettings = null);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function closureOnField(string $fieldName, array $params, array $fieldSettings = null, bool $noisy = true): bool;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function enforceField(string $fieldName, $value, array $params, array $fieldSettings = null, bool $noisy = true): bool;
}