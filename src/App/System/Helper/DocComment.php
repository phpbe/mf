<?php
namespace Be\App\System\Helper;


class DocComment
{

    /**
     * 解析文档注释
     *
     * @param string $docComment 文档注释
     * @return array
     */
    public static function parse($docComment)
    {
        $result = [];
        if (preg_match('#^/\*\*(.*)\*/#s', $docComment, $comment) === false) return [];
        $comment = trim($comment[1]);

        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false) return [];
        $lines = $lines[1];

        $description = [];
        foreach ($lines as $line) {

            $line = trim($line);

            if ($line) {
                // 该行注释由 @ 开头
                if (strpos($line, '@') === 0) {
                    if (strpos($line, ' ') > 0) {
                        $param = substr($line, 1, strpos($line, ' ') - 1);
                        $value = substr($line, strlen($param) + 2);
                    } else {
                        $param = substr($line, 1);
                        $value = '';
                    }

                    if ($param == 'param' || $param == 'return') {
                        $pos = strpos($value, ' ');
                        $type = substr($value, 0, $pos);
                        $value = '(' . $type . ')' . substr($value, $pos + 1);
                    } elseif ($param == 'class') {
                        $r = preg_split("[|]", $value);
                        if (is_array($r)) {
                            $param = $r[0];
                            parse_str($r[1], $value);
                            foreach ($value as $key => $val) {
                                $val = explode(',', $val);
                                if (count($val) > 1)
                                    $value[$key] = $val;
                            }
                        } else {
                            $param = 'Unknown';
                        }
                    }

                    if (empty ($result[$param])) {
                        $result[$param] = $value;
                    } else if ($param == 'param') {
                        $arr = array(
                            $result[$param],
                            $value
                        );
                        $result[$param] = $arr;
                    } else {
                        $result[$param] = $value + $result[$param];
                    }

                    if (!isset($result['summary']) && count($description) > 0) {
                        $result['summary'] = implode(PHP_EOL, $description);
                        $description = [];
                    }
                } else {
                    $description[] = $line;
                }
            } else {
                if (!isset($result['summary']) && count($description) > 0) {
                    $result['summary'] = implode(PHP_EOL, $description);
                    $description = [];
                }
            }
        }

        if (count($description) > 0) {
            $description = implode(' ', $description);
            $result['description'] = $description;
        }

        return $result;
    }


}

