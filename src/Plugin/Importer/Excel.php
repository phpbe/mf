<?php

namespace Be\Plugin\Importer;


use Be\System\Exception\RuntimeException;

class Excel extends Driver
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
        $excelReader = new \PHPExcel_Reader_Excel2007();
        if (!$excelReader->canRead($file)) {
            $excelReader = new \PHPExcel_Reader_Excel5();
            if (!$excelReader->canRead($file)) {
                $excelReader = new \PHPExcel_Reader_CSV();
                $excelReader->setInputEncoding('GBK');
                if (!$excelReader->canRead($file)) {
                    throw new RuntimeException('上传的文件不是有效的Excel文件！');
                }
            }
        }

        $excel = $excelReader->load($file);
        $worksheet = $excel->getSheet(0);
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

        if ($highestRow < 2) {
            throw new RuntimeException('您上传的文件中无数据！');
        }

        $headerColMap = [];
        for ($col = 0; $col <= $highestColumnIndex; $col++) {
            $header = (string)$worksheet->getCellByColumnAndRow($col, 1)->getValue();
            $header = trim($header);
            $headerColMap[$header] = $col;
        }

        $noHeader = null;
        $i = 0;
        foreach ($this->config['field']['items'] as $field) {
            $i++;

            if ($noHeader === null) {
                $noHeader = isset($field['label']) ? false : true;
            }

            if ($noHeader) {
                if (!isset($field['index'])) {
                    throw new RuntimeException('第 ' . $i . ' 项字段配置错误，未设置列编号！');
                }

                if ($field['index'] > $highestColumnIndex) {
                    throw new RuntimeException('您上传的Excel中缺少 #' . $field['index'] . ' 列！');
                }

            } else {
                if (!isset($field['label'])) {
                    throw new RuntimeException('第 ' . $i . ' 项字段配置错误，未设置列可表头！');
                }

                if (!isset($headerColMap[$field['label']])) {
                    throw new RuntimeException('您上传的文件中缺少 ' . $field['label'] . ' 列！');
                }
            }
        }

        $errors = [];
        for ($row = $noHeader ? 1 : 2; $row <= $highestRow; $row++) {

            try {

                $values = [];

                foreach ($this->config['field']['items'] as $field) {
                    $val = '';
                    if (isset($field['label'])) {
                        $val = (string)$worksheet->getCellByColumnAndRow($headerColMap[$field['label']], $row)->getValue();
                    } elseif (isset($field['index'])) {
                        $val = (string)$worksheet->getCellByColumnAndRow($field['index'], $row)->getValue();
                    }

                    switch ($field['type']) {
                        case 'date':
                            if (is_numeric($val)) {
                                $val = gmdate('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($val));
                            } else {
                                $val = str_replace('年', '-', $val);
                                $val = str_replace('月', '-', $val);
                                $val = str_replace('日', '', $val);
                                $val = date('Y-m-d', strtotime($val));
                            }
                            break;
                        case 'datetime':
                            if (is_numeric($val)) {
                                $val = gmdate('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($val));
                            } else {
                                $val = str_replace('年', '-', $val);
                                $val = str_replace('月', '-', $val);
                                $val = str_replace('日', '', $val);
                                $val = date('Y-m-d H:i:s', strtotime($val));
                            }
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

                    $values[$field['name']] = $val;
                }

                foreach ($this->config['field']['items'] as $field) {
                    if (isset($field['check']) && is_callable($field['check'])) {
                        $fn = $field['check'];
                        $fn($values);
                    }
                }

                yield $values;

            } catch (RuntimeException $e) {
                $errors[] = '第' . $row . '行：' . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            throw new RuntimeException('有' . count($errors) . '条数据有问题：' . "\n" . implode("\n", $errors));
        }

    }
}