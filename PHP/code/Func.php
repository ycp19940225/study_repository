<?php

namespace app\tools;


/**
 * 通用函數
 * Class Func
 * @package library\tools
 */
class Func
{
    /**
     * 创建图片缩略图
     * @param $file要缩略的图片
     * @param $dw 画布的宽
     * @param $dh 画布的高
     * @param $path 保存路径
     * @return string
     */
    public static function thumb($file, $dw, $dh, $path)
    {//这四个参数分别是1、要缩略的图片，2、画布的宽（也就是你要缩略的宽）3、画布的高（也就是你要缩略的高），4、保存路径）
        //获取用户名图
        $data = new self();
        $srcImg = $data->getImg($file);//调用下面那个函数，实现根据图片类型来创建不同的图片画布
        //获取原图的宽高
        $infoSrc = getimagesize($file);//这个getimagesize()是php里面的系统函数用来获取图片的具体信息的
        $sw = $infoSrc[0];//获取要缩略图片的宽
        $sh = $infoSrc[1];//。。获取要缩略的图片的高
        //创建缩略图画布
        $destImg = imagecreatetruecolor($dw, $dh);
        //为缩略图填充背景色
        $bg = imagecolorallocate($destImg, 255, 255, 255);
        imagefill($destImg, 0, 0, $bg);
        //计算例缩放的尺寸
        if ($dh / $dw > $sh / $sw) {
            $fw = $dw;
            $fh = $sh / $sw * $fw;
        } else {
            $fh = $dh;
            $fw = $fh * $sw / $sh;
        }
        //居中放置
        $dx = ($dw - $fw) / 2;
        $dy = ($dh - $fh) / 2;
        //创建缩略图
        imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $fw, $fh, $sw, $sh);
        $baseName = 'thumb_' . basename($file);//给缩略的图片命名，basename()是系统内置函数用来获取后缀名的
        $savePath = $path . '/' . $baseName;//设置缩略图片保存路径
        if (!file_exists($path)) {
            if (!mkdir($path, 0777, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        imagejpeg($destImg, $savePath); //把缩略图存放到上一步设置的保存路径里
        return $savePath;
    }

    /**
     * //这是以一个动态创建图片画布的函数（根据具体的图片类型创相应类型的画布）
     * @param $file
     * @return resource
     */
    public function getImg($file)
    {
        $info = getimagesize($file);
        $fn = $info['mime'];//获得图片类型；
        switch ($fn) {
            case 'image/jpeg'://如果类型是imag/jpeg就创建jpeg类型的画布
                $img = imagecreatefromjpeg($file);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($file);//如果类型是gif就创建gif类型的画布
                break;
            case 'image/png':
                $img = imagecreatefrompng($file);//如果类型是png就创建png类型的画布
                break;
        }
        return $img;//返回画布类型
    }

    /**
     * 创建唯一ID
     * @param string $str
     * @return string
     */
    public static function createUUid($str = '')
    {
        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_id_main = date('YmdHis') . rand(10000000, 99999999);
        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i, 1));
        }
        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        return $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
    }


    /**
     * GCJ-02(高德)和BD-09(百度)之间转换
     * @param string $from 原始格式 gcj-02|bd-09
     * @param string $to 目标格式 gcj-02|bd-09
     * @param array $point 原始坐标值
     * @return array  转换后的坐标值
     * @throws \Exception
     */

    public static function coordinateTranslate($from, $to, array $point)
    {
        $pi = pi() * 3000.0 / 180.0;
        $from = strtoupper($from);
        $to = strtoupper($to);
        list($x, $y) = $point;

        if ($from === 'GCJ-02' && $to === 'BD-09') {
            $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $pi);
            $theta = atan2($y, $x) + 0.000003 * cos($x * $pi);
            return [$z * cos($theta) + 0.0065, $z * sin($theta) + 0.006];
        } else if ($from === 'BD-09' && $to === 'GCJ-02') {
            $x = $x - 0.0065;
            $y = $y - 0.006;
            $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $pi);
            $theta = atan2($y, $x) - 0.000003 * cos($x * $pi);
            return [$z * cos($theta), $z * sin($theta)];
        }
        throw new \Exception("unsupport $from to $to translate");
    }


    //本月第一天：参数：$day格式为yyyy-mm-dd
    public static function monFirstDay($day, $needEnd = false)
    {
        $startTime = date('Y-m-01', strtotime($day));
        $endTime = date('Y-m-d', strtotime("$startTime +1 month -1 day"));
        if ($needEnd) {
            return [$startTime, $endTime];
        }
        return $startTime;
    }

    //季度第一天：参数：$day格式为yyyy-mm-dd
    public static function quarterFirstDay($day, $needEnd = false)
    {
        $season = ceil((date('n', (strtotime($day))) / 3));  //当月是第几季度
        $startTime = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
        $endTime = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')));
        if ($needEnd) {
            return [$startTime, $endTime];
        }
        return $startTime;
    }

    /**
     * 上个月第一天：参数：$day格式为yyyy-mm-dd
     * @param $day
     * @param int $month 上几个月 比如上上个月 就是2
     * @return array|false|string
     */
    public static function lastMonFirstDay($day, $month = 1)
    {
        $time = strtotime($day);
        $startTime = date('Y-m-01 00:00:00', strtotime("-$month month", $time));
        $endTime = date('Y-m-t 23:59:59', strtotime("-$month month", $time));
        return [$startTime, $endTime];
    }

    /**
     * 上个季度第一天：参数：$day格式为yyyy-mm-dd
     * @param $day
     * @param int $Quarter $month 上几个季度 比如上上个季度 就是2
     * @return array|false|string
     */
    public static function lastQuarterFirstDay($day, $Quarter = 1)
    {
        $season = ceil((date('n', (strtotime($day))) / 3)) - $Quarter;  //当月是第几季度再减一
        $startTime = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
        $endTime = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')));
        return [$startTime, $endTime];
    }

    /**
     * 获取一年开头的日期
     * @param $time
     * @return array
     */
    public static function getYearDate($time = '')
    {
        $year = date('Y', time());
        if (empty($time)) {
            $start = $year . '-01-01';
            $end = $year . '-12-31';
        } else {
            $start = $year . '-01-01';
            $end = date('Y-m-d', strtotime("$time+1 day"));
        }
        return [$start, $end];
    }

    //保留两位小数
    public static function format_number($data, $num = 2)
    {
        return number_format($data, $num, '.', '');
    }

    /**
     * 二维数组添加字段
     * @param array $array 要添加的二维数组
     * @param $fields
     * @param $value
     * @return array
     */
    public static function array2AddFields(array $array, $fields, $value)
    {
        foreach ($array as $key => $val) {
            $array[$key][$fields] = $value;
        }
        return $array;
    }

    /**
     * 文件转base64输出
     * @param String $file 文件路径
     * @return String base64 string
     */
    public static function fileToBase64($file, $code = false)
    {
        $base64_file = '';
        $file = $base64_file . $file;
        if (file_exists($file)) {
            $mime_type = mime_content_type($file);
            $base64_data = base64_encode(file_get_contents($file));
            if ($code) {
                $base64_data = 'data:' . $mime_type . ';base64,' . $base64_data;
            }
        }
        return $base64_data;
    }


    /**
     * 判断是否是手机浏览器
     * @return false|int
     */
    public static function is_mobile()
    {

        // returns true if one of the specified mobile browsers is detected
        // 如果监测到是指定的浏览器之一则返回true

        $regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";

        $regex_match .= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";

        $regex_match .= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";

        $regex_match .= "symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";

        $regex_match .= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";

        $regex_match .= ")/i";

        // preg_match()方法功能为匹配字符，既第二个参数所含字符是否包含第一个参数所含字符，包含则返回1既true
        return preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']));
    }

    //对path进行判断，如果是本地文件就二进制读取并base64编码，如果是url,则返回
    public static function img_base64($path)
    {
        $img_data = "";
        if (substr($path, 0, strlen("http")) === "http") {
            $img_data = $path;
        } else {
            if ($fp = fopen($path, "rb", 0)) {
                $binary = fread($fp, filesize($path)); // 文件读取
                fclose($fp);
                $img_data = base64_encode($binary); // 转码
            } else {
                printf("%s 图片不存在", $img_path);
            }
        }
        return $img_data;
    }

    /**
     * 数字转中文货币大写
     *
     * · 个，十，百，千，万，十万，百万，千万，亿，十亿，百亿，千亿，万亿，十万亿，
     *   百万亿，千万亿，兆；此函数亿乘以亿为兆
     *
     * · 以「十」开头，如十五，十万，十亿等。两位数以上，在数字中部出现，则用「一十几」，
     *   如一百一十，一千零一十，一万零一十等
     *
     * · 「二」和「两」的问题。两亿，两万，两千，两百，都可以，但是20只能是二十，
     *   200用二百也更好。22,2222,2222是「二十二亿两千二百二十二万两千二百二十二」
     *
     * · 关于「零」和「〇」的问题，数字中一律用「零」，只有页码、年代等编号中数的空位
     *   才能用「〇」。数位中间无论多少个0，都读成一个「零」。2014是「两千零一十四」，
     *   20014是「二十万零一十四」，201400是「二十万零一千四百」
     *
     * 参考：https://jingyan.baidu.com/article/636f38bb3cfc88d6b946104b.html
     *
     * 人民币写法参考：[正确填写票据和结算凭证的基本规定](http://bbs.chinaacc.com/forum-2-35/topic-1181907.html)
     *
     * @param minx $number
     * @param boolean $isRmb
     * @return string
     */
    public static function number2chinese($number, $isRmb = false)
    {
        // 判断正确数字
        if (!preg_match('/^-?\d+(\.\d+)?$/', $number)) {
            throw new Exception('number2chinese() wrong number', 1);
        }
        list($integer, $decimal) = explode('.', $number . '.0');

        // 检测是否为负数
        $symbol = '';
        if (substr($integer, 0, 1) == '-') {
            $symbol = '负';
            $integer = substr($integer, 1);
        }
        if (preg_match('/^-?\d+$/', $number)) {
            $decimal = null;
        }
        $integer = ltrim($integer, '0');

        // 准备参数
        $numArr = ['', '一', '二', '三', '四', '五', '六', '七', '八', '九', '.' => '点'];
        $descArr = ['', '十', '百', '千', '万', '十', '百', '千', '亿', '十', '百', '千', '万亿', '十', '百', '千', '兆', '十', '百', '千'];
        if ($isRmb) {
            $number = substr(sprintf("%.5f", $number), 0, -1);
            $numArr = ['', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖', '.' => '点'];
            $descArr = ['', '拾', '佰', '仟', '万', '拾', '佰', '仟', '亿', '拾', '佰', '仟', '万亿', '拾', '佰', '仟', '兆', '拾', '佰', '仟'];
            $rmbDescArr = ['角', '分', '厘', '毫'];
        }

        // 整数部分拼接
        $integerRes = '';
        $count = strlen($integer);
        if ($count > max(array_keys($descArr))) {
            throw new Exception('number2chinese() number too large.', 1);
        } else if ($count == 0) {
            $integerRes = '零';
        } else {
            for ($i = 0; $i < $count; $i++) {
                $n = $integer[$i];      // 位上的数
                $j = $count - $i - 1;   // 单位数组 $descArr 的第几位
                // 零零的读法
                $isLing = $i > 1                    // 去除首位
                    && $n !== '0'                   // 本位数字不是零
                    && $integer[$i - 1] === '0';    // 上一位是零
                $cnZero = $isLing ? '零' : '';
                $cnNum = $numArr[$n];
                // 单位读法
                $isEmptyDanwei = ($n == '0' && $j % 4 != 0)     // 是零且一断位上
                    || substr($integer, $i - 3, 4) === '0000';  // 四个连续0
                $descMark = isset($cnDesc) ? $cnDesc : '';
                $cnDesc = $isEmptyDanwei ? '' : $descArr[$j];
                // 第一位是一十
                if ($i == 0 && $cnNum == '一' && $cnDesc == '十') $cnNum = '';
                // 二两的读法
                $isChangeEr = $n > 1 && $cnNum == '二'       // 去除首位
                    && !in_array($cnDesc, ['', '十', '百'])  // 不读两\两十\两百
                    && $descMark !== '十';                   // 不读十两
                if ($isChangeEr) $cnNum = '两';
                $integerRes .= $cnZero . $cnNum . $cnDesc;
            }
        }

        // 小数部分拼接
        $decimalRes = '';
        $count = strlen($decimal);
        if ($decimal === null) {
            $decimalRes = $isRmb ? '整' : '';
        } else if ($decimal === '0') {
            $decimalRes = $isRmb ? '' : '零';
        } else if ($count > max(array_keys($descArr))) {
            throw new Exception('number2chinese() number too large.', 1);
        } else {
            for ($i = 0; $i < $count; $i++) {
                if ($isRmb && $i > count($rmbDescArr) - 1) break;
                $n = $decimal[$i];
                if (!$isRmb) {
                    $cnZero = $n === '0' ? '零' : '';
                    $cnNum = $numArr[$n];
                    $cnDesc = '';
                    $decimalRes .= $cnZero . $cnNum . $cnDesc;
                } else {
                    // 零零的读法
                    $isLing = $i > 0                        // 去除首位
                        && $n !== '0'                       // 本位数字不是零
                        && $decimal[$i - 1] === '0';        // 上一位是零
                    $cnZero = $isLing ? '零' : '';
                    $cnNum = $numArr[$n];
                    $cnDesc = $cnNum ? $rmbDescArr[$i] : '';
                    $decimalRes .= $cnZero . $cnNum . $cnDesc;
                }
            }
        }
        // 拼接结果
        $res = $symbol . (
            $isRmb
                ? $integerRes . ($decimalRes === '' ? '元整' : "元$decimalRes")
                : $integerRes . ($decimalRes === '' ? '' : "点$decimalRes")
            );
        return $res;
    }
}