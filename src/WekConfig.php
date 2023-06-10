<?php

namespace Wekyun\WebmanTool;

class WekConfig
{

//    public static function cs()
//    {
//        echo time();
//    }

    /**
     * 配置方法setPluginConfigValue：基于xiuno 的文件配置修改成webman的插件配置修改
     * 如果需要改成自己的，可以继承wek类使用 file_replace_var 方法
     * */

    /**
     *
     * @return
     */
    public static function setPluginConfigValue(array $config_val, string $plugin_name, $file_name)
    {
        if (!defined('BASE_PATH')) return var_dump('请安装 webman 再使用');
        if (!is_array($config_val)) return var_dump('config data is must array');
        $plugin_config_path = BASE_PATH . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . $plugin_name . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $file_name . '.php';
        //验证修改存在的配置文件，防止改谁的都不知道
        if (!is_file($plugin_config_path)) {
            return var_dump('config file is not:' . $plugin_config_path);
        }
        static $obj;
        if (!$obj) {
            $obj = new wek();
        }
        return $obj->file_replace_var($plugin_config_path, $config_val);
    }

    /*==============================================文件================================================*/
    // 将变量写入到文件，根据后缀判断文件格式，先备份，再写入，写入失败，还原备份
    protected function file_replace_var($filepath, $replace = array(), $pretty = FALSE)
    {
        $ext = $this->file_ext($filepath);
        if ($ext == 'php') {
            $arr = array();
            $is_file = is_file($filepath);
            if ($is_file == false) {
                $file = fopen($filepath, "w");
                fclose($file);
            } else {
                $arr = include $filepath;
                if (!is_array($arr)) $arr = array();
            }
            $arr = array_merge($arr, $replace);
            $s = "<?php\r\nreturn " . var_export($arr, true) . ";\r\n?>";
            // 备份文件
            $this->file_backup($filepath);
            $r = $this->file_put_contents_try($filepath, $s);
            $r != strlen($s) ? $this->file_backup_restore($filepath) : $this->file_backup_unlink($filepath);
            return $r;
        } elseif ($ext == 'js' || $ext == 'json') {
            $s = $this->file_get_contents_try($filepath);
            $arr = $this->xn_json_decode($s);
            if (empty($arr)) return FALSE;
            $arr = array_merge($arr, $replace);
            $s = $this->xn_json_encode($arr, $pretty);
            $this->file_backup($filepath);
            $r = $this->file_put_contents_try($filepath, $s);
            $r != strlen($s) ? $this->file_backup_restore($filepath) : $this->file_backup_unlink($filepath);
            return $r;
        }
    }

    // 文件后缀名，不包含 .
    private function file_ext($filename, $max = 16)
    {
        $ext = strtolower(substr(strrchr($filename, '.'), 1));
        $ext = $this->xn_urlencode($ext);
        strlen($ext) > $max and $ext = substr($ext, 0, $max);
        if (!preg_match('#^\w+$#', $ext)) $ext = 'attach';
        return $ext;
    }

    // 备份文件
    private function file_backup($filepath)
    {
        $backfile = $this->file_backname($filepath);
        if (is_file($backfile)) return TRUE; // 备份已经存在
        $r = $this->xn_copy($filepath, $backfile);
        clearstatcache();
        return $r && filesize($backfile) == filesize($filepath);
    }

    private function file_put_contents_try($file, $s, $times = 3)
    {
        while ($times-- > 0) {
            $fp = fopen($file, 'wb');
            if ($fp and flock($fp, LOCK_EX)) {
                $n = fwrite($fp, $s);
                version_compare(PHP_VERSION, '5.3.2', '>=') and flock($fp, LOCK_UN);
                fclose($fp);
                clearstatcache();
                return $n;
            } else {
                sleep(1);
            }
        }
        return FALSE;
    }

    // 还原备份
    private function file_backup_restore($filepath)
    {
        $backfile = $this->file_backname($filepath);
        $r = $this->xn_copy($backfile, $filepath);
        clearstatcache();
        $r && filesize($backfile) == filesize($filepath) && $this->xn_unlink($backfile);
        return $r;
    }

    // 删除备份
    private function file_backup_unlink($filepath)
    {
        $backfile = $this->file_backname($filepath);
        $r = $this->xn_unlink($backfile);
        return $r;
    }

    private function file_get_contents_try($file, $times = 3)
    {
        while ($times-- > 0) {
            $fp = fopen($file, 'rb');
            if ($fp) {
                $size = filesize($file);
                if ($size == 0) return '';
                $s = fread($fp, $size);
                fclose($fp);
                return $s;
            } else {
                sleep(1);
            }
        }
        return FALSE;
    }

    private function xn_urlencode($s)
    {
        $s = urlencode($s);
        $s = str_replace('_', '_5f', $s);
        $s = str_replace('-', '_2d', $s);
        $s = str_replace('.', '_2e', $s);
        $s = str_replace('+', '_2b', $s);
        $s = str_replace('=', '_3d', $s);
        $s = str_replace('%', '_', $s);
        return $s;
    }

    private function xn_json_decode($json)
    {
        $json = trim($json, "\xEF\xBB\xBF");
        $json = trim($json, "\xFE\xFF");
        return json_decode($json, 1);
    }

    private function xn_json_encode($data, $pretty = FALSE, $level = 0)
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $tab = $pretty ? str_repeat("\t", $level) : '';
        $tab2 = $pretty ? str_repeat("\t", $level + 1) : '';
        $br = $pretty ? "\r\n" : '';
        switch ($type = gettype($data)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return ($data ? 'true' : 'false');
            case 'integer':
            case 'double':
            case 'float':
                return $data;
            case 'string':
                $data = '"' . str_replace(array('\\', '"'), array('\\\\', '\\"'), $data) . '"';
                $data = str_replace("\r", '\\r', $data);
                $data = str_replace("\n", '\\n', $data);
                $data = str_replace("\t", '\\t', $data);
                return $data;
            case 'object':
                $data = get_object_vars($data);
            case 'array':
                $output_index_count = 0;
                $output_indexed = array();
                $output_associative = array();
                foreach ($data as $key => $value) {
                    $output_indexed[] = $this->xn_json_encode($value, $pretty, $level + 1);
                    $output_associative[] = $tab2 . '"' . $key . '":' . $this->xn_json_encode($value, $pretty, $level + 1);
                    if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                        $output_index_count = NULL;
                    }
                }
                if ($output_index_count !== NULL) {
                    return '[' . implode(",$br", $output_indexed) . ']';
                } else {
                    return "{{$br}" . implode(",$br", $output_associative) . "{$br}{$tab}}";
                }
            default:
                return ''; // Not supported
        }
    }

    private function xn_copy($src, $dest)
    {
        $r = is_file($src) ? copy($src, $dest) : FALSE;
        return $r;
    }

    private function file_backname($filepath)
    {

        $dirname = dirname($filepath);
        //$filename = file_name($filepath);
        $filepre = $this->file_pre($filepath);
        $fileext = $this->file_ext($filepath);
        $s = "$filepre.backup.$fileext";
        return $s;
    }

    // 文件的前缀，不包含最后一个 .
    private function file_pre($filename, $max = 32)
    {
        return substr($filename, 0, strrpos($filename, '.'));
    }

    private function xn_unlink($file)
    {
        $r = is_file($file) ? unlink($file) : FALSE;
        return $r;
    }
    /*==============================================================文件end===============================================================*/

}