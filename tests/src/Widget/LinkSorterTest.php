<?php

namespace leapsunit\src\Widget;

use Leaps;
use Leaps\Data\ActiveDataProvider;
use Leaps\Db\Connection;
use Leaps\Db\Query;
use Leaps\Widget\Breadcrumbs;
use Leaps\Widget\LinkSorter;
use Leaps\Widget\ListView;
use leapsunit\data\Ar\ActiveRecord;
use leapsunit\data\Ar\Order;
use leapsunit\src\Db\DatabaseTestCase;

/**
 * @group widgets
 */
class LinkSorterTest extends DatabaseTestCase
{
    protected $driverName = 'sqlite';

    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
        $this->mockWebApplication();
        $this->breadcrumbs = new Breadcrumbs();
    }

    public function testLabelsSimple()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find(),
            'models' => [new Order()],
            'totalCount' => 1,
            'sort' => [
                'route' => 'site/index',
            ],
        ]);

        ob_start();
        echo ListView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{sorter}",
        ]);
        $actualHtml = ob_get_clean();

        $this->assertTrue(strpos($actualHtml, '<a href="/index.php?r=site%2Findex&amp;sort=customer_id" data-sort="customer_id">Customer</a>') !== false);
        $this->assertTrue(strpos($actualHtml, '<a href="/index.php?r=site%2Findex&amp;sort=total" data-sort="total">Invoice Total</a>') !== false);
    }

    public function testLabelsExplicit()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find(),
            'models' => [new Order()],
            'totalCount' => 1,
            'sort' => [
                'attributes' => ['total'],
                'route' => 'site/index',
            ],
        ]);

        ob_start();
        echo ListView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{sorter}",
        ]);
        $actualHtml = ob_get_clean();

        $this->assertFalse(strpos($actualHtml, '<a href="/index.php?r=site%2Findex&amp;sort=customer_id" data-sort="customer_id">Customer</a>') !== false);
        $this->assertTrue(strpos($actualHtml, '<a href="/index.php?r=site%2Findex&amp;sort=total" data-sort="total">Invoice Total</a>') !== false);
    }

}
