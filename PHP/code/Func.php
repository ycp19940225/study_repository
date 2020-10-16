<?php

namespace app\tools;


/**
 * 通用函數
 * Class Func
 * @package library\tools
 */
class Func
{
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

    public function getImg($file)
    {//这是以一个动态创建图片画布的函数（根据具体的图片类型创相应类型的画布）
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
}