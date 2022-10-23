<?php
/**
 * @author    林跃
 * @principal 冯家浩
 * @date      2021/12/22
 * @time      16:56
 */

namespace app\module\purchase\dto\logic\purchaseOrder;

class LogisticsChannelListItem extends \app\framework\base\dto\AbstractDto
{
    /** @var int 物流商ID */
    public $logistics_provider_id;

    /** @var string 物流商名称 */
    public $logistics_provider_name;

    /** @var \app\module\purchase\dto\logic\purchaseOrder\LogisticsChannelListChannelItem[] 成功的数据列表 */
    public $channels = [];
}