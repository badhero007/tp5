<?php
// 应用公共文件

/**
 * 生成指定范围内指定总和的随机数
 * @param $n 生成个数
 * @param int $max 总和
 * @param int $min 最小值
 * @return array
 */
function distribute($n ,$max = 10,$min = 10){
    $array = $zero = $normal = [];
    for($i=1;$i<=$n;$i++){
        $array[] = mt_rand($min,$max);
    }
    $k = $max / array_sum($array);  //求出放大系数k
    foreach($array as $key => $val){
        $value = floor($val * $k); //直接保留整数，以保证下一步的和肯定<max
        if($value<1){
            $zero[] = $value;
        }else{
            $normal[] = $value;
        }
    }
    $sum = array_sum($normal);
    $diff = $max - $sum; //这个值肯定<max
    if(!empty($zero)){ //如果有为0的值
        $count = count($zero);
        foreach($zero as $z){
            $normal[] = $diff / $count;
        }
    }else{ //随机分配给一个人
        $key = array_rand($normal);
        $normal[$key] = $normal[$key]+$diff;
    }
    unset($array,$zero,$sum,$diff);
    arsort($normal);
    return $normal;
}

function average($sum,$num){
    $arr = [];
    for ($i = 0;$i < $num; $i++) {
        $arr[] = floor($sum/$num);
    }
    return $arr;
}

/**
 * 创建目录
 * @param string $path 路径
 * @param int $mode 目录权限
 * @param bool $recursive
 * @return bool
 * @throws \Exception
 * @author zhangxiaodong1@daojia.com.cn
 */
function createDirectory($path, $mode = 0775, $recursive = true)
{
    if (is_dir($path)) {
        return true;
    }
    $parentDir = dirname($path);
    if ($recursive && !is_dir($parentDir)) {
        createDirectory($parentDir, $mode, true);
    }
    try {
        $result = mkdir($path, $mode);
        chmod($path, $mode);
    } catch (\Exception $e) {
        throw new \Exception("Failed to create directory '$path': " . $e->getMessage(), $e->getCode(), $e);
    }

    return $result;
}

/**
 * 生成文件
 * @param $file
 * @param int $mode
 * @author zhangxiaodong1@daojia.com.cn
 */
function touchFile($file,$mode = 0775)
{
    if (!file_exists($file)) { //文件不存在 则创建文件 并开放权限
        @touch($file);
        @chmod($file, $mode);
    }
}

/**
 * 下载文件
 * @param $fileName
 * @param bool $delDesFile
 * @param bool $isExit
 * @throws Exception
 */
function download( $fileName, $delDesFile = false, $isExit = true ) {
    if ( file_exists( $fileName ) ) {
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment;filename = ' . basename( $fileName ) );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate, post-check = 0, pre-check = 0' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $fileName ) );
        ob_clean();
        flush();
        readfile( $fileName );
        if ( $delDesFile ) {
            unlink( $fileName );
        }
        if ( $isExit ) {
            exit;
        }
    } else {
        echo 'file is not exist';
    }
}

/**
 * 导出excel表格
 * @param $data
 * @param string $file_dir 文件路径
 * @param string $file_name 文件名称
 * @param bool $download 是否下载
 * @param bool $style 是否添加样式
 * @param bool $runpath 是否存储于runtime路径
 * @throws Exception
 * @throws PHPExcel_Exception
 * @throws PHPExcel_Reader_Exception
 */
function exportExcel($data,$file_dir = '',$file_name = '',$download = true,$style = true,$runpath = true){
    vendor('phpoffice.phpexcel.Classes.PHPExcel');
    $objectPHPExcel = new \PHPExcel();
    //设定缓存模式为经gzip压缩后存入cache
    $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
    $cacheSettings = array();
    \PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);

    $objectPHPExcel->setActiveSheetIndex(0);
    $chars = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O', 'P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','Al','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];

    //表格头的输出
    foreach($data['map'] as $key=>$val) {
        var_dump($chars[$key].'1');
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue($chars[$key].'1',$val['key']);
    }

    foreach ( $data['rows'] as $k => $val ) {
        if(!$val) continue;
        $n = $k;
        //明细的输出
        foreach ($data['map'] as $kk => $vv) {
            if(!isset($val[$vv['value']])) continue;
            $objectPHPExcel->getActiveSheet()->getStyle($chars[$kk] . ($n + 2))->getFont()->setSize(10);
            if($style){
                $objectPHPExcel->getActiveSheet()->getStyle($chars[$kk] . ($n + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            }
            if (!is_numeric($val[$vv['value']])) {
                $objectPHPExcel->getActiveSheet()->setCellValue($chars[$kk] . ($n + 2),handleString($val[$vv['value']]));
            } else {
                $objectPHPExcel->getActiveSheet()->setCellValue($chars[$kk] . ($n + 2),$val[$vv['value']]);
            }
        }
    }

    $objectPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
    $objectPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(false);
    $fileName=$file_name ? $file_name : $data['title'].date('Ymdhis');
    $dir = $file_dir ? $file_dir : 'Files/excel/';

    if($runpath){
        if(!is_dir(RUNTIME_PATH.$dir)) {
            createDirectory(RUNTIME_PATH.$dir, 0777);
        }

        $path = RUNTIME_PATH.$dir.$fileName.".xls";
    } else {
        if(!is_dir($dir)){
            createDirectory($dir);
        }
        $path = $dir.$fileName.".xls";
    }

    $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
    $objWriter->save($path);
    if(!$download){
        return;
    }
    download( $path, true );
}

/**
 * 过滤字符串去除emoji表情等...
 * @param $string
 * @return string
 * @author zhangxiaodong1@daojia.com.cn
 */
function handleString($string){
    $charArr = preg_split('/(?<!^)(?!$)/u',$string);
    $final = [];
    foreach($charArr as $char) {
        //if($char != ' ' && preg_match('/[0-9 a-z A-Z \x{4e00}-\x{9fa5}]/u',$char)) {
        //暂时允许带空格
        if($char == '—' || $char == '-' || preg_match('/[0-9 a-z A-Z \x{4e00}-\x{9fa5}]/u',$char)) {
            $final[] = $char;
        }
    }
    return implode('',$final);
}

//获取唯一序列号
function generateNum() {
    //strtoupper转换成全大写的
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $uuid = substr($charid, 0, 8).substr($charid, 8, 4).substr($charid,12, 4).substr($charid,16, 4).substr($charid,20,12);
    return $uuid;
}

function getDays($date1,$date2){
    $startdate=strtotime($date1);

    $enddate=strtotime($date2);

    $days=round(($enddate-$startdate)/3600/24) ;
    return $days;
}