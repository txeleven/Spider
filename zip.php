<?php

require_once  './upgrade.php';
use UP\UpGrade;
//解压文件
if ($_GET['z'] == 'un') {
    $zip = UpGrade::$zip;
    $s=$zip->name('./'.$_GET['zip'])->dec('./');
    var_dump($s);
}
//压缩 根目录文件
if ($_GET['z'] == 'set') {
    $list = UpGrade::dirloop('./');
    if (count($list) > 0) {
        $size = 0;
        $zip = UpGrade::$zip;
        $zipName = 'code';
        $i = 0;
        $fileList = [];
     //   echo '<pre>';
     //   var_dump(count($list));
        foreach ($list as $v) {
            $s=filesize($v);
            $size += ($s<5000?5000:$s);
            /*
            if($i==4){
                echo '<pre>';echo PHP_EOL.'FILE----'.PHP_EOL;
                echo $v.'--'.filesize($v).'---'.$size;

                echo PHP_EOL.'----'.PHP_EOL;
            }
            */
            if (ceil(($size) / 1000 / 10) / 100 >= 5) {
                $s = $zip->name('./zip/' . $zipName . '_' . $i . '.zip')->nar($fileList);
           //     var_dump(count($fileList));
                /*
                echo '<pre>';echo PHP_EOL.'----'.$i.PHP_EOL;
                var_dump($size);
                var_dump($s);
                */
                $size = 0;
                $i++;
            //    echo '<pre>';print_r($fileList);
                $fileList = [];
            }
            $fileList[str_ireplace(['../','./'], '', $v)] = $v;
        }
        $s = $zip->name('./zip/' . $zipName . '_' . $i . '.zip')->nar($fileList);
    }

}
