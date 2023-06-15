<?php

namespace Wekyun\WebmanLib;

use think\exception\ValidateException;
use Webman\Config;

/**
 * @title TP验证类
 */
class Check
{
    private static $config = [];
    private static $class = [];//容器对象池

    //预定义验证场景类
    private static $mapping = [];

    //判断是否有值
    private static function check_isset_value($param, $check_key)
    {
        if (!isset($param[$check_key])) {
            return false;
        }
        if (is_string($param[$check_key]) && strlen($param[$check_key]) > 0) {
            return true;
        }

        if (is_array($param[$check_key]) && count($param[$check_key]) > 0) {
            return true;
        }

        if (!$param[$check_key]) {
            if ($param[$check_key] === 0 || $param[$check_key] === '0') {
                return true;
            }
            return false;
        }
        return true;
    }

    /** 调用指定验证场景类的场景
     * @autho hugang
     * @param string $name 场景类名
     * @param string $check_name 场景名
     * @return 调用指定验证场景类的场景
     * */
    public static function checkAll($name = null, $param = null)
    {
        return self::checkBase($name, $param);
    }

    /** 调用指定验证场景类的场景
     * @autho hugang
     * @param string $name 场景类名
     * @param string $check_name 场景名
     * @return 调用指定验证场景类的场景
     * */
    public static function checkGet($name = null, $param = null)
    {
        return self::checkBase($name, $param);
    }

    /** 调用指定验证场景类的场景
     * @autho hugang
     * @param string $name 场景类名
     * @param string $check_name 场景名
     * @return 调用指定验证场景类的场景
     * */
    public static function checkPost($name = null, $param = null)
    {
        return self::checkBase($name, $param);
    }

    /** 调用指定验证场景类的场景
     * @autho hugang
     * @param string $name 场景类名
     * @param string $check_name 场景名
     * @return 调用指定验证场景类的场景
     * */
    protected static function checkBase($name = null, $param = null, $param_type = 'all')
    {
        $new_data = [];//最终接收的参数
        //参数为空，接收所有参数
        $input = self::get_all_data($param_type);
        if (!$name && !$param) return $input;
        var_dump($input);

        $check = [];
        if (self::$config == []) {
            //加载
            $check = Config::get('check');
            if (!$check) return self::err_json('check验证配置为空');
            if (!isset($check['mapping'])) return self::err_json('验证配置:mapping 未定义');
            if (count($check['mapping']) == 0) return self::err_json('验证配置:mapping 值为空');
            self::$err_code = isset($check['err_code']) ? $check['err_code'] : 203;
            self::$err_func = isset($check['err_func']) ? $check['err_func'] : false;
            self::$config = $check;
        }

        $obj = null;
        if (isset(self::$class[$name])) {
            //加载过直接使用
            $obj = self::$class[$name];
        } else {
            $mapping = $check['mapping'];//保存自定义验证规则文件
            $class = $mapping[$name];
            try {
                $obj = new $class();
                self::$class[$name] = $obj;
            } catch (\Exception $e) {
                return self::err_json('验证规则配置文件错误，请检查验证配置文件mapping设置的验证器路径是否正确；' . $e->getMessage());
            }
        }

        if (is_string($param)) {
            $param = explode(',', $param);
        }
        if (!is_array($param)) {
            return self::err_json('接收参数请传递数组或者字符串');
        }
        //处理：是不是必传，按照什么验证
        $check_param = [];//指定要接收的参数
        foreach ($param as $value) {
            if (strpos($value, '>.') !== false) {
                return self::err_json('验证规则书写错误了 . 必须在 > 前面');
            }
            $value_more = explode('.', $value);//0是key部分未解析的，1是默认值和提示部分的
            $check_key = null;
//            dd($value_more);
            $vali_data = [
                'key' => '',
                'def_val' => '',
                'tips_name' => '',
                'tips_msg' => '',
                'is_check_rule' => true,//是否验证规则
                'is_mast' => false,
            ];
            $is_mast = false;
            if (count($value_more) == 1) {
                //不是必传
                $value_more1_rule_val = explode(':', $value_more[0]);
                if (count($value_more1_rule_val) == 2) {
                    $value_more[0] = $value_more1_rule_val[0];
                    $value_more[1] = $value_more1_rule_val[1];
                    $vali_data['def_val'] = $value_more1_rule_val[1];

                    if (strpos($value_more[1], '>') !== false) {
                        $value_more1_def_val = explode('>', $value_more[1]);
                        $vali_data['def_val'] = $value_more1_def_val[0];
                        $vali_data['tips_msg'] = $value_more1_def_val[1];
                    }
                } else {
                    //只有自定义提示
                    if (strpos($value_more1_rule_val[0], '>') !== false) {
                        $value_more1_tip_val = explode('>', $value_more1_rule_val[0]);
                        $value_more[0] = $value_more1_tip_val[0];
                        $vali_data['tips_msg'] = $value_more1_tip_val[1];
                    }
                }
            } else {
                //必传
                $is_mast = true;
                if (strpos($value_more[1], ':') !== false) {
                    return self::err_json('必填项只能设置错误提示，不能设置默认值');
                }
                if (strpos($value_more[1], '>') !== false) {
                    $value_more1_def_val = explode('>', $value_more[1]);
                    $vali_data['tips_msg'] = $value_more1_def_val[1];
                    $vali_data['def_val'] = '';
                }
            }

            $vali_data['key'] = $value_more[0];

            if (strpos($vali_data['key'], '|') !== false) {
                $value_more0_val = explode('|', $vali_data['key']);
                if (!self::is_mast($value_more0_val[1])) {
                    return self::err_json('使用|设置的提示名称,不能为空。|后面应该定义提示的名称');
                }
                $vali_data['key'] = $value_more0_val[0];
                $vali_data['tips_name'] = $value_more0_val[1];
            }
//            dd($vali_data);

            $data_log_name_more = explode('!', $vali_data['key']);
            $is_check_rule = false;
            if (count($data_log_name_more) == 2) {
                //指定不验证这个参数的格式
                $vali_data['key'] = $data_log_name_more[1];
            } else {
                $is_check_rule = true;
                $vali_data['key'] = $data_log_name_more[0];
            }

            $check_key = $vali_data['key'];
            if (!self::check_isset_value($input, $check_key)) {
                if ($is_mast) {//必传
                    if ($vali_data['tips_msg'] != '') {
                        return self::err_json($vali_data['tips_msg']);
                    } else {
                        $tps = $vali_data['tips_name'] ? $vali_data['tips_name'] : $check_key;
                        return self::err_json($tps . '不可为空');
                    }
                } else {
                    //默认参数和空,不验证参数因为不是传递过来的参数,这个是后端自己定义的,说明是可以用的,比如手机不传就为0,就是可以的,传就必须是格式正确的
                    $is_check_rule = false;
                    if ($vali_data['def_val']) {
                        $new_data[$check_key] = $vali_data['def_val'];
                    } else {
                        $new_data[$check_key] = '';
                    }
                }
            } else {
                $new_data[$check_key] = $input[$check_key];
            }

            if ($is_check_rule) {
                if (!$obj->check($new_data)) {
                    $err_msg = $obj->getError();
                    if ($vali_data['tips_name']) {
                        $err_msg = str_replace($check_key, $vali_data['tips_name'], $err_msg);
                    }
                    return self::err_json($err_msg);
                }
            }
        }
        return array_merge($input, $new_data);
    }

    private static function get_param_data($check_name, $input)
    {
//        $data = [];
//        foreach ($check_name as $key => $value) {
//        }
    }

    //获取所有参数：Post和Get的集合
    private static function get_all_data($type)
    {
        $r = \request();
        switch ($type) {
            case 'all':
                return \request()->all();
            case 'get':
                return \request()->get();
            case 'post':
                return \request()->post();
        }

    }

    public static $err_code;
    public static $msg = false;
    private static $err_func;

    //错误提示
    private static function err_json($msg)
    {
        self::$msg = $msg;
        if (self::$err_func instanceof \Closure) {
            return self::$err_func($msg, self::$err_code);
        }
        throw new ValidateException($msg, self::$err_code);
    }

    /** 验证变量是否存在有值
     * @autho hugang
     * @param string $val 场景类名
     * @return 验证变量是否存在有值
     * */
    private static function is_mast($val)
    {
        if (strlen($val) > 0) {
            return true;
        }
        return false;
    }

}