<?php

namespace Be\System\App\SearchItem;

use Be\System\App\DataItem\DataItemText;


/**
 * 搜索项 整型
 */
class SearchItemText extends DataItemText
{
    use Driver;


    /**
     * 提交处理
     *
     * @param array $data
     * @return string
     */
    public function buildWhere($data)
    {
        $where = '';
        if ($this->newValue) {

            if (isset($this->option['table'])) {
                $where = '`' . $this->option['table'] . '`.';
            }

            $field = isset($this->option['field']) ? $this->option['field'] : $this->name;

            $where .=  '`' . $field . '`=\'' . $this->newValue . '\'';
        }

        return $where;
    }
}
