<?php
namespace TempestTools\Crud\Doctrine\Helper;


use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;

class QueryHelper extends ArrayHelper implements \TempestTools\Crud\Contracts\QueryHelper {
    use TTConfigTrait, ErrorConstantsTrait, ArrayHelperTrait;
}
?>