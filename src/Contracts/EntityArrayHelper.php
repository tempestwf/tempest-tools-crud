<?php

namespace TempestTools\Crud\Contracts;

use TempestTools\Common\Contracts\ArrayHelper;

interface EntityArrayHelper extends ArrayHelper
{

    public function getConfigForField(string $fieldName, string $keyName);

    public function canChain(string $associationName, string $chainType, bool $nosey = true): bool;

    public function canAssign(string $associationName, string $assignType, bool $nosey = true): bool;

    public function allowed($nosey = true): bool;

    public function checkFastMode(string $fieldName): bool;
}