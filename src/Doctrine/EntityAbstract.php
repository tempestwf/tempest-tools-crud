<?php
namespace TempestTools\Crud\Doctrine;

use App\Entities\Entity;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use TempestTools\Common\Contracts\ArrayHelpable;
use TempestTools\Common\Contracts\ArrayHelper;
use TempestTools\Common\Contracts\Evm;
use TempestTools\Common\Contracts\TTConfig;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;


abstract class EntityAbstract extends Entity implements EventSubscriber, TTConfig, Evm, ArrayHelpable {

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait;

    const ERRORS = [

    ];

    /**
     * Makes sure the entity is ready to go
     *
     * @param ArrayHelper $arrayHelper
     * @throws \RuntimeException
     */
    public function __construct(ArrayHelper $arrayHelper) {
        $this->setArrayHelper($arrayHelper);
        $this->parseTTConfig();
        $this->setEvm(new EventManager());
    }
}
?>