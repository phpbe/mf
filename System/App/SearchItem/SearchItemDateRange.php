<?php

namespace Be\System\App\SearchItem;

use Be\System\App\DataItem\DataItemDateRange;


/**
 * 搜索项 整型
 */
class SearchItemDateRange extends DataItemDateRange
{

    use Driver;

    public function buildWhere()
    {
        $where = '';
        if ($this->newValue) {

            if (isset($this->option['table'])) {
                $where = '`' . $this->option['table'] . '`.';
            }

            $field = isset($this->option['field']) ? $this->option['field'] : $this->name;

            $where .=  '`' . $field . '`>=\'' . $this->newValue[0] .' 00:00:00\'';
            $where .=  '`' . $field . '`<=\'' . $this->newValue[1] .' 23:59:59\'';
        }

        return $where;
    }


}
