<?php 

//echo '<pre>';print_r($_GET);print_r($_SERVER);exit;
if(!@$_GET['url']){
die('error');	
}
header('Content-type: image/jpeg');
function getImg($url = "") {
 $hander = curl_init();
 curl_setopt($hander,CURLOPT_URL,$url);
 #curl_setopt($hander,CURLOPT_FILE,$fp);
 curl_setopt($hander,CURLOPT_HEADER,0);
 curl_setopt($hander,CURLOPT_FOLLOWLOCATION,1);
 curl_setopt($hander,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,当为false是直接显示出来
 curl_setopt($hander,CURLOPT_TIMEOUT,60);
 /*$options = array(
  CURLOPT_URL=> '/thum-f3ccdd27d2000e3f9255a7e3e2c4880020110622095243.jpg',
  CURLOPT_FILE => $fp,
  CURLOPT_HEADER => 0,
  CURLOPT_FOLLOWLOCATION => 1,
  CURLOPT_TIMEOUT => 60
 );
 curl_setopt_array($hander, $options);
 */
 curl_exec($hander);
 curl_close($hander);

 return true;
}


getImg($_GET['url']); 
