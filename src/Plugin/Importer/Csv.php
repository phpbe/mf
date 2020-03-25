<?php

namespace Be\Plugin\Importer;


use Be\System\Exception\RuntimeException;

class Csv extends Driver
{

    /**
     * 处理导入文件
     *
     * @param string $file 文件路径
     * @return \Generator
     * @throws RuntimeException
     */
    public function process($file)
    {

        $noHeader = null;
        $i = 0;
        foreach ($this->config['field']['items'] as $field) {
            $i++;

            if ($noHeader === null) {
                $noHeader = isset($field['label']) ? false : true;
            }

            if ($noHeader) {
                if (!isset($field['index'])) {
                    throw new RuntimeException('第 ' . $i . ' 项字段配置错误：未设置列编号！');
                }

            } else {
                if (!isset($field['label'])) {
                    throw new RuntimeException('第 ' . $i . ' 项字段配置错误：未设置列可表头！');
                }
            }
        }


        $delimiter = ','; // 设置字段分界符（只允许一个字符）
        $enclosure = '"'; // 设置字段环绕符（只允许一个字符）
        $escape = '\\'; // 设置转义字符（只允许一个字符），默认是一个反斜杠

        if (isset($this->config['delimiter'])) {
            $delimiter = $this->config['delimiter'];
        }

        if (isset($this->config['enclosure'])) {
            $enclosure = $this->config['enclosure'];
        }

        if (isset($this->config['escape'])) {
            $escape = $this->config['escape'];
        }

        $encoding = 'GBK';
        if (isset($this->config['encoding'])) {
            $encoding = strtoupper($this->config['encoding']);
        }

        if ($encoding == 'DETECT') {
            $contents = file_get_contents($file);
            $encoding = mb_detect_encoding($contents , array('GBK','GB2312','BIG5', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1'));
        }

        $f = fopen($file, 'r');

        // 表头元素数量
        $headerCount = null;

        // 表头 - 列索引 映射
        $headerColMap = null;

        // 有表头
        if (!$noHeader) {

            $headers = fgetcsv($f, 0, $delimiter, $enclosure, $escape);

            $headerCount = count($headers);
            if ($headerCount == 0) {
                throw new RuntimeException('您上传的文件中无数据！');
            }

            $headerColMap = [];
            $col = 0;
            foreach ($headers as &$header) {

                if ($encoding != 'UTF-8') {
                    $header = iconv($encoding, 'UTF-8//IGNORE', $header);
                }

                $header = str_replace(["\r", "\n", "\t"], '', $header);
                $header = trim($header);

                $headerColMap[$header] = $col;
                $col++;
            }
        }

        // 校验表头
        $i = 0;
        foreach ($this->config['field']['items'] as $field) {
            $i++;
            if ($noHeader) {
                if ($field['index'] > $headerCount) {
                    throw new RuntimeException('您上传的中缺少 #' . $field['index'] . ' 列！');
                }
            } else {
                if (!isset($headerColMap[$field['label']])) {
                    throw new RuntimeException('您上传的文件中缺少 ' . $field['label'] . ' 列！');
                }
            }
        }

        $errors = [];

        $row = $noHeader ? 1 : 2;
        while (!feof($f)) {
            try {

                $values = fgetcsv($f, 0, $delimiter, $enclosure, $escape);

                if (!is_array($values)) {
                    //$errors[] = '第 ' . $row . ' 行数据格式异常！';
                    continue;
                }

                if (!$noHeader) {
                    if (count($values) != $headerCount) {
                        //$errors[] = '第 ' . $row . ' 行数据格式异常！';
                        continue;
                    }
                }

                foreach ($values as &$v) {
                    if ($encoding != 'UTF-8') {
                        $v = iconv($encoding, 'UTF-8//IGNORE', $v);
                    }

                    $v = trim($v);
                    $v = trim($v, "'");
                }

                $formattedValues = [];
                foreach ($this->config['field']['items'] as $field) {
                    $val = '';

                    if (isset($field['label'])) {
                        $val = $values[$headerColMap[$field['label']]];
                    } elseif (isset($field['index'])) {
                        $val = $values[$field['index']];
                    }

                    switch ($field['type']) {
                        case 'date':
                            $val = str_replace('年', '-', $val);
                            $val = str_replace('月', '-', $val);
                            $val = str_replace('日', '', $val);
                            $val = date('Y-m-d', strtotime($val));
                            break;
                        case 'datetime':
                            $val = str_replace('年', '-', $val);
                            $val = str_replace('月', '-', $val);
                            $val = str_replace('日', '', $val);
                            $val = date('Y-m-d H:i:s', strtotime($val));
                            break;
                        case 'number':
                            $val = str_replace(',', '', $val);
                            break;
                    }

                    if ($field['required']) {
                        if (!$val) {
                            if (isset($field['label'])) {
                                throw new RuntimeException('列 ' . $field['label'] . ' 不能为空！');
                            } elseif (isset($field['index'])) {
                                throw new RuntimeException('列 #' . $field['index'] . ' 不能为空！');
                            }
                        }
                    }

                    $formattedValues[$field['name']] = $val;
                }

                foreach ($this->config['field']['items'] as $field) {
                    if (isset($field['check']) && is_callable($field['check'])) {
                        $fn = $field['check'];
                        $fn($formattedValues);
                    }
                }

                yield $formattedValues;

            } catch (RuntimeException $e) {
                $errors[] = '第' . $row . '行：' . $e->getMessage();
            }

            $row++;
        }
        fclose($f);

        if (count($errors) > 0) {
            throw new RuntimeException('有' . count($errors) . '条数据有问题！');
        }

    }

}