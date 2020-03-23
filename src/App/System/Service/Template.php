<?php

namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Exception\ServiceException;

class Template extends \Be\System\Service
{


    /**
     * 更新模板
     *
     * @param string $template 模析名
     * @param string $theme 主题名
     * @throws \Exception
     */
    public function update($template, $theme)
    {
        $themeProperty = Be::getProperty('Theme.' . $theme);

        $fileTheme = Be::getRuntime()->getRootPath() . $themeProperty->path . '/' . $theme . '.php';
        if (!file_exists($fileTheme)) {
            throw new ServiceException('主题 ' . $theme . ' 不存在！');
        }

        $parts = explode('.', $template);
        $type = array_shift($parts);
        $name = array_shift($parts);

        $property = Be::getProperty($type . '.' . $name);
        $fileTemplate = Be::getRuntime()->getRootPath() . $property->path . '/Template/' . implode('/', $parts) . '.php';

        if (!file_exists($fileTemplate)) {
            throw new ServiceException('模板 ' . $template . ' 不存在！');
        }

        $path = Be::getRuntime()->getCachePath() . '/System/Template/' . $theme . '/' . $type . '/' . $name . '/' . implode('/', $parts) . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $contentTheme = file_get_contents($fileTheme);
        $contentTemplate = file_get_contents($fileTemplate);

        $extends = '\\Be\\System\\Template';
        if (preg_match('/<!--\s*{\s*extends\s*:\s*(.*?)\s*}\s*-->/s', $contentTemplate, $matches)) {
            $extends = trim($matches[1]);
            $this->update($extends, $theme);
            $contentTemplate = preg_replace($matches[0], '', $contentTemplate);
        }

        if (preg_match('/<!--\s*{\s*include\s*:\s*(.*?)\s*}\s*-->/s', $contentTemplate, $matches)) {
            $i = 0;
            foreach ($matches[1] as $m) {
                $includes = explode('.', $m);
                if (count($includes) > 2) {
                    $tmpType = array_shift($includes);
                    $tmpName = array_shift($includes);

                    $tmpProperty = Be::getProperty($tmpType . '.' . $tmpName);
                    $fileInclude = Be::getRuntime()->getRootPath() . $property->path . '/Template/' . implode('/', $includes) . '.php';
                    if (!file_exists($fileInclude)) {
                        throw new ServiceException('模板中包含的文件 ' . $m . ' 不存在！');
                    }

                    $contentInclude = file_get_contents($fileInclude);
                    $contentTemplate = preg_replace($matches[0][$i], $contentInclude, $contentTemplate);
                }
                $i++;
            }
        }

        $codePre = '';
        $codeUse = '';
        $codeHtml = '';
        $pattern = '/<!--\s*{\s*html\s*}\s*-->(.*?)<!--\s*{\s*\/html\s*}\s*-->/s';
        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 html
            $codeHtml = trim($matches[1]);

            if (preg_match_all('/use\s+(.+);/', $contentTemplate, $matches)) {
                foreach ($matches[1] as $m) {
                    $codeUse .= 'use ' . $m . ';' . "\n";
                }
            }

            $pattern = '/<\?php(.*?)\?>\s*<!--\s*{html}\s*-->/s';
            if (preg_match($pattern, $contentTemplate, $matches)) {
                $codePre = trim($matches[1]);
                $codePre = preg_replace('/use\s+(.+);/', '', $codePre);
                $codePre = preg_replace('/\s+$/m', '', $codePre);
            }

        } else {

            if (preg_match($pattern, $contentTheme, $matches)) {
                $codeHtml = trim($matches[1]);

                $pattern = '/<!\s*--{\s*head\s*}\s*-->(.*?)<!--\s*{\s*\/head\s*}\s*-->/s';
                if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 head
                    $codeHead = $matches[1];
                    $codeHtml = preg_replace($pattern, $codeHead, $codeHtml);
                }

                $pattern = '/<!--\s*{\s*body\s*}\s*-->(.*?)<!--\s*{\/body}\s*-->/s';
                if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 body
                    $codeBody = $matches[1];
                    $codeHtml = preg_replace($pattern, $codeBody, $codeHtml);
                } else {

                    $pattern = '/<!--\s*{\s*north\s*}\s*-->(.*?)<!--\s*{\s*\/north\s*}\s*-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeNorth = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeNorth, $codeHtml);
                    }

                    $pattern = '/<!--\s*{\s*middle\s*}\s*-->(.*?)<!--\s*{\s*\/middle\s*}\s*-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeMiddle = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeMiddle, $codeHtml);
                    } else {
                        $pattern = '/<!--\s*{\s*west\s*}\s*-->(.*?)<!--\s*{\s*\/west\s*}\s*-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 west
                            $codeWest = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeWest, $codeHtml);
                        }

                        $pattern = '/<!--\s*{\s*center\s*}\s*-->(.*?)<!--\s*{\s*\/center\s*}\s*-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 center
                            $codeCenter = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeCenter, $codeHtml);
                        }

                        $pattern = '/<!--\s*{\s*east\s*}\s*-->(.*?)<!--\s*{\s*\/east\s*}\s*-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 east
                            $codeEast = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeEast, $codeHtml);
                        }
                    }

                    $pattern = '/<!--\s*{\s*south\s*}\s*-->(.*?)<!--\s*{\s*\/south\s*}\s*-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeSouth = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeSouth, $codeHtml);
                    }
                }
            }

            $pattern = '/use\s+(.+);/';
            $uses = null;
            if (preg_match_all($pattern, $contentTheme, $matches)) {
                $uses = $matches[1];
                foreach ($matches[1] as $m) {
                    $codeUse .= 'use ' . $m . ';' . "\n";
                }
            }

            if (preg_match_all($pattern, $contentTemplate, $matches)) {
                foreach ($matches[1] as $m) {
                    if ($uses !== null && !in_array($m, $uses)) {
                        $codeUse .= 'use ' . $m . ';' . "\n";
                    }
                }
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--\s*{\s*html\s*}\s*-->/s';
            if (preg_match($pattern, $contentTheme, $matches)) {
                $codePreTheme = trim($matches[1]);
                $codePreTheme = preg_replace('/use\s+(.+);/', '', $codePreTheme);
                $codePreTheme = preg_replace('/\s+$/m', '', $codePreTheme);
                $codePre = $codePreTheme . "\n";
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--\s*{\s*(?:html|head|body|north|middle|west|center|east|south)\s*}\s*-->/s';
            if (preg_match($pattern, $contentTemplate, $matches)) {
                $codePreTemplate = trim($matches[1]);
                $codePreTemplate = preg_replace('/use\s+(.+);/', '', $codePreTemplate);
                $codePreTemplate = preg_replace('/\s+$/m', '', $codePreTemplate);

                $codePre .= $codePreTemplate . "\n";
            }
        }

        $codeVars = '';

        if (isset($themeProperty->colors) && is_array($themeProperty->colors)) {
            $codeVars .= '  public $colors = [\'' . implode('\',\'', $themeProperty->colors) . '\'];' . "\n";
        }

        $className = array_pop($parts);

        $codePhp = '<?php' . "\n";
        $codePhp .= 'namespace Be\\Cache\\System\\Template\\' . $theme . '\\' . $type . '\\' . $name . '\\' . implode('\\', $parts) . ';' . "\n";
        $codePhp .= "\n";
        $codePhp .= $codeUse;
        $codePhp .= "\n";
        $codePhp .= 'class ' . $className . ' extends ' . $extends . "\n";
        $codePhp .= '{' . "\n";
        $codePhp .= $codeVars;
        $codePhp .= "\n";
        $codePhp .= '  public function display()' . "\n";
        $codePhp .= '  {' . "\n";
        $codePhp .= $codePre;
        $codePhp .= '    ?>' . "\n";
        $codePhp .= $codeHtml . "\n";
        $codePhp .= '    <?php' . "\n";
        $codePhp .= '  }' . "\n";
        $codePhp .= '}' . "\n";
        $codePhp .= "\n";

        file_put_contents($path, $codePhp, LOCK_EX);
        chmod($path, 0755);
    }

}
