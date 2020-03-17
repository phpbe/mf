<?php

namespace Be\System\App\SearchItem;

/**
 * 驱动
 */
trait Driver
{

    /**
     * 提交处理
     *
     * @param $condition
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
