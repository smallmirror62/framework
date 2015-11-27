<?php

namespace leapsunit\data\Ar;

use Leaps\Db\ActiveQuery;

/**
 * CustomerQuery
 */
class CustomerQuery extends ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');

        return $this;
    }
}
