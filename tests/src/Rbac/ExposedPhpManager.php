<?php
namespace leapsunit\src\rbac;

use Leaps\Rbac\PhpManager;

/**
 * Exposes protected properties and methods to inspect from outside
 */
class ExposedPhpManager extends PhpManager
{
    /**
     * @var \Leaps\Rbac\Item[]
     */
    public $items = []; // itemName => item
    /**
     * @var array
     */
    public $children = []; // itemName, childName => child
    /**
     * @var \Leaps\Rbac\Assignment[]
     */
    public $assignments = []; // userId, itemName => assignment
    /**
     * @var \Leaps\Rbac\Rule[]
     */
    public $rules = []; // ruleName => rule

    public function load()
    {
        parent::load();
    }

    public function save()
    {
        parent::save();
    }
}