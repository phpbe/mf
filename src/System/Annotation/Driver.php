<?php

namespace Be\System\Annotation;

/**
 * 驱动
 */
class Driver
{
    protected $value;

    /**
     * Driver constructor.
     * @param string | array $value
     */
    public function __construct($value = '')
    {
        if ($value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->$k = $v;
                }
            } else {
                $this->value = $value;
            }
        }
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }
}