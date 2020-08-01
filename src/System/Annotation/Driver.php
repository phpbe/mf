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
                    if (substr($v, 7) == 'return ') {
                        if (substr($v,-1) != ';') {
                            $v .= ';';
                        }

                        try {
                            $this->$k = eval($v);
                        } catch (\Throwable $e) {

                        }
                    } else {
                        $this->$k = $v;
                    }
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