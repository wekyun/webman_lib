<?php

namespace Wekyun\WebmanLib;
class Tool
{

    /** 字符串到数组
     * @autho hugang
     * @param string $val
     * @param string $punctuate
     * @return 字符串到数组
     * */
    public function explode(string $val, string $punctuate = ',')
    {
        return $val ? explode($punctuate, $val) : [];
    }

    /** 数组到字符串
     * @autho hugang
     * @param array $val
     * @param string $punctuate
     * @return 数组到字符串
     * */
    public function implode(array $val, string $punctuate = ',')
    {
        return $val ? implode($punctuate, $val) : '';
    }

    /**
     * 判断当前服务器系统
     * @return string
     */
    public static function getOS()
    {
        if (PATH_SEPARATOR == ':') {
            return 'Linux';
        }
        return 'Windows';
    }

    /**
     * 当前微妙数
     * @return number
     */
    public static function microtime_float()
    {
        list ($usec, $sec) = explode(" ", microtime());
        return (( float )$usec + ( float )$sec);
    }

    /**
     * @param $url string 接口地址
     * @param $http_method 请求方式
     * @param $data 请求数据
     * @param $header 请求头(一维非关联数组)
     * @param $cookie 请cookie
     * @return 发送https的post请求
     */
    public function http_curl(string $url, $http_method = 'GET', $data = '', $header = array(), $cookie = '')
    {
        $headers = array(
            'Accept: application/json',
        );
        $headers = array_merge($headers, $header);
        if ($cookie) {
            $headers[] = "Cookie: $cookie";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        if ($http_method == 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $res = curl_exec($ch);
        //返回结果
        if ($res) {
            curl_close($ch);
            return $res;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return $error;
        }
    }


    /**
     * 计算两个经纬点之间的距离
     * @param $lng1 经度1
     * @param $lat1 纬度1
     * @param $lng2 经度2
     * @param $lat2 纬度2
     * @param int $unit m，km
     * @param int $decimal 位数
     * @return float
     */
    public function getDistance($lng1, $lat1, $lng2, $lat2, $unit = 2, $decimal = 2): float
    {

        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = 3.1415926535898;

        $radLat1 = $lat1 * $PI / 180.0;
        $radLat2 = $lat2 * $PI / 180.0;

        $radLng1 = $lng1 * $PI / 180.0;
        $radLng2 = $lng2 * $PI / 180.0;

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $distance = $distance * $EARTH_RADIUS * 1000;

        if ($unit === 2) {
            $distance /= 1000;
        }

        return round($distance, $decimal);
    }

    // 比较数组的值，如果不相同则保留，以第一个数组为准
    public function array_def_value($arr1, $arr2)
    {
        foreach ($arr1 as $k => $v) {
            if (isset($arr2[$k]) && $arr2[$k] == $v) unset($arr1[$k]);
        }
        return $arr1;
    }


    /*
	$data = array();
	$data[] = array('volume' => 67, 'edition' => 2);
	$data[] = array('volume' => 86, 'edition' => 1);
	$data[] = array('volume' => 85, 'edition' => 6);
	$data[] = array('volume' => 98, 'edition' => 2);
	$data[] = array('volume' => 86, 'edition' => 6);
	$data[] = array('volume' => 67, 'edition' => 7);
	arrlist_multisort($data, 'edition', TRUE);
*/
// 对多维数组排序
    public function arrlist_multisort($arrlist, $col, $asc = TRUE)
    {
        $colarr = array();
        foreach ($arrlist as $k => $arr) {
            $colarr[$k] = $arr[$col];
        }
        $asc = $asc ? SORT_ASC : SORT_DESC;
        array_multisort($colarr, $asc, $arrlist);
        return $arrlist;
    }

// 对数组进行查找，排序，筛选，支持多种条件排序
    public function arrlist_cond_orderby($arrlist, $cond = array(), $orderby = array(), $page = 1, $pagesize = 20)
    {
        $resultarr = array();
        if (empty($arrlist)) return $arrlist;

        // 根据条件，筛选结果
        if ($cond) {
            foreach ($arrlist as $key => $val) {
                $ok = TRUE;
                foreach ($cond as $k => $v) {
                    if (!isset($val[$k])) {
                        $ok = FALSE;
                        break;
                    }
                    if (!is_array($v)) {
                        if ($val[$k] != $v) {
                            $ok = FALSE;
                            break;
                        }
                    } else {
                        foreach ($v as $k3 => $v3) {
                            if (
                                ($k3 == '>' && $val[$k] <= $v3) ||
                                ($k3 == '<' && $val[$k] >= $v3) ||
                                ($k3 == '>=' && $val[$k] < $v3) ||
                                ($k3 == '<=' && $val[$k] > $v3) ||
                                ($k3 == '==' && $val[$k] != $v3) ||
                                ($k3 == 'LIKE' && stripos($val[$k], $v3) === FALSE)
                            ) {
                                $ok = FALSE;
                                break 2;
                            }
                        }
                    }
                }
                if ($ok) $resultarr[$key] = $val;
            }
        } else {
            $resultarr = $arrlist;
        }

        if ($orderby) {

            // php 7.2 deprecated each()
            //list($k, $v) = each($orderby);

            $k = key($orderby);
            $v = current($orderby);

            $resultarr = arrlist_multisort($resultarr, $k, $v == 1);
        }

        $start = ($page - 1) * $pagesize;

        $resultarr = array_assoc_slice($resultarr, $start, $pagesize);
        return $resultarr;
    }


// 从一个二维数组中取出一个 key=>value 格式的一维数组
    public function arrlist_key_values($arrlist, $key, $value = NULL, $pre = '')
    {
        $return = array();
        if ($key) {
            foreach ((array)$arrlist as $k => $arr) {
                $return[$pre . $arr[$key]] = $value ? $arr[$value] : $k;
            }
        } else {
            foreach ((array)$arrlist as $arr) {
                $return[] = $arr[$value];
            }
        }
        return $return;
    }

    // 从一个二维数组中取出一个 values() 格式的一维数组，某一列key
    public function arrlist_values($arrlist, $key)
    {
        if (!$arrlist) return array();
        $return = array();
        foreach ($arrlist as &$arr) {
            $return[] = $arr[$key];
        }
        return $return;
    }


// 从一个二维数组中对某一列求最大值
    public function arrlist_max($arrlist, $key)
    {
        if (!$arrlist) return 0;
        $first = array_pop($arrlist);
        $max = $first[$key];
        foreach ($arrlist as &$arr) {
            if ($arr[$key] > $max) {
                $max = $arr[$key];
            }
        }
        return $max;
    }

// 从一个二维数组中对某一列求最小值
    public function arrlist_min($arrlist, $key)
    {
        if (!$arrlist) return 0;
        $first = array_pop($arrlist);
        $min = $first[$key];
        foreach ($arrlist as &$arr) {
            if ($min > $arr[$key]) {
                $min = $arr[$key];
            }
        }
        return $min;
    }
}
