<?php
namespace hcgrzh\captcha;
/*
 * 验证码
 * 允许大小设定
 * session 名称 verification
 */
class Captcha{
	//app或web
	static private $isapp=false;
	//宽 默认100
	static private $width=100;
	//高 默认30
	static private $height=30;
	//验证码长度
	static private $strlength=5;
	//背景设置
	static private $bgcolor=false;
	//像素点颜色
	static private $pixelcolor=false;
	//字体颜色
	static private $textcolor=false;
	//线条颜色
	static private $linecolor=false;
	//像素点开启
	static private $pixelstatus=false;
	//默认保存 验证码 session 名称
	static private $captchaname='hcgrzh_captcha_name';

	//设置尺寸入口
	public static function setconfig($config=array()){
		if(isset($config['isapp'])){
			self::$isapp=$config['isapp'];
		}
		if(isset($config['width'])){
			self::$width=$config['width'];
		}
		if(isset($config['height'])){
			self::$height=$config['height'];
		}
		if(isset($config['strlength'])){
			self::$strlength=$config['strlength'];
		}
		if(isset($config['bgcolor'])){
			self::$bgcolor=$config['bgcolor'];
		}
		if(isset($config['pixelcolor'])){
			self::$pixelcolor=$config['pixelcolor'];
		}
		if(isset($config['pixelstatus'])){
			self::$pixelstatus=$config['pixelstatus'];
		}
		if(isset($config['textcolor'])){
			self::$textcolor=$config['textcolor'];
		}
		if(isset($config['linecolor'])){
			self::$linecolor=$config['linecolor'];
		}
		if(isset($config['captchaname'])){
			self::$captchaname=$config['captchaname'];
		}
	}
	/**
	* 产生随机字符串
	* 数字及字母
	* @param    int        $length  输出长度
	* @param    string     $chars   可选的 ，默认为 0123456789
	* @return   string     字符串
	*/
	public static function strlength($length){
		$hash = '';
		$hash_empty='';
		//第一位不能为0；
		$charsone="123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
		$maxone = strlen($charsone) - 1;
		$hash.= $charsone[mt_rand(0, $maxone)];
		//其他位
		$chars = '0123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length-1; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
		for ($j=0; $j<strlen($hash); $j++){
			$hash_empty.=$hash[$j]." ";
  		}
  		$hash_empty=trim($hash_empty);
  		$res=array();
  		$res['hash']=$hash;
  		$res['hash_empty']=$hash_empty;
  		return $res;
	}
	 /**
     * 十六进制 转 RGB
     */
	public static function hex2rgb($hexColor) {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        return $rgb;
    }
	//生成验证码
	public static function run(){
		if(self::$isapp===false){
			//生成图片功能声明
			header("cache-control:no-cache,must-revalidate");
			//图片格式声明
			header("Content-Type:image/png");
		}
		$im=imagecreate(self::$width,self::$height) or W_ERROR('图片功能障碍!');
		//背景颜色
		if(self::$bgcolor===false){
			$background_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		}else{
			$rgb_bgcolor=self::hex2rgb(self::$bgcolor);
			$background_color=imagecolorallocate($im,$rgb_bgcolor['r'],$rgb_bgcolor['g'],$rgb_bgcolor['b']);
		}
		//字体颜色
		if(self::$textcolor===false){
			$text_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		}else{
			$rgb_text=self::hex2rgb(self::$textcolor);
			$text_color=imagecolorallocate($im,$rgb_text['r'],$rgb_text['g'],$rgb_text['b']);
		}

		//线条颜色
		if(self::$linecolor===false){
			$line_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		}else{
			$rgb_line=self::hex2rgb(self::$linecolor);
			$line_color=imagecolorallocate($im,$rgb_line['r'],$rgb_line['g'],$rgb_line['b']);
		}
		//绘制矩形
		imagefilledrectangle($im,0,0,self::$width,self::$height,$line_color);


		//像素点颜色
		if(self::$pixelcolor===false){
			$pixel_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		}else{
			$rgb_pixel=self::hex2rgb(self::$pixelcolor);
			$pixel_color=imagecolorallocate($im,$rgb_pixel['r'],$rgb_pixel['g'],$rgb_pixel['b']);
		}
		//绘制像素点
		if(self::$pixelstatus===true){
			for($i=0;$i<200;$i++){
				ImageSetPixel($im, rand(0,self::$width),rand(0,self::$height),$pixel_color);
			}
		}
		$codearr=self::strlength(self::$strlength);
		$codeempty=$codearr['hash_empty'];
		$code=$codearr['hash'];
		$_SESSION[self::$captchaname] =md5(strtolower($code));
		//验证码居中位置设置
		$start_x=ceil(self::$width/8);
		$start_y=ceil(self::$height/4);
		imagestring($im,5,$start_x,$start_y,$codeempty,$text_color);
		//显示图片
		imagepng($im);
		//摧毁图片
		imagedestroy($im);
	}
	public static function returnCode($code){
		$curcode=md5(strtolower($code));
		if($curcode==$_SESSION[self::$captchaname]){
			return true;
		}else{
			return false;
		}
	}
	//base64
	public static function baserun(){
		$im=imagecreate(self::$width,self::$height) or W_ERROR('图片功能障碍!');
		//背景颜色
		if(self::$bgcolor===false){
			$background_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		}else{
			$rgb_bgcolor=self::hex2rgb(self::$bgcolor);
			$background_color=imagecolorallocate($im,$rgb_bgcolor['r'],$rgb_bgcolor['g'],$rgb_bgcolor['b']);
		}
		//字体颜色
		if(self::$textcolor===false){
			$text_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		}else{
			$rgb_text=self::hex2rgb(self::$textcolor);
			$text_color=imagecolorallocate($im,$rgb_text['r'],$rgb_text['g'],$rgb_text['b']);
		}

		//线条颜色
		if(self::$linecolor===false){
			$line_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		}else{
			$rgb_line=self::hex2rgb(self::$linecolor);
			$line_color=imagecolorallocate($im,$rgb_line['r'],$rgb_line['g'],$rgb_line['b']);
		}
		//绘制矩形
		imagefilledrectangle($im,0,0,self::$width,self::$height,$line_color);


		//像素点颜色
		if(self::$pixelcolor===false){
			$pixel_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		}else{
			$rgb_pixel=self::hex2rgb(self::$pixelcolor);
			$pixel_color=imagecolorallocate($im,$rgb_pixel['r'],$rgb_pixel['g'],$rgb_pixel['b']);
		}
		//绘制像素点
		if(self::$pixelstatus===true){
			for($i=0;$i<200;$i++){
				ImageSetPixel($im, rand(0,self::$width),rand(0,self::$height),$pixel_color);
			}
		}
		$codearr=self::strlength(self::$strlength);
		$codeempty=$codearr['hash_empty'];
		$code=$codearr['hash'];
		//验证码居中位置设置
		$start_x=ceil(self::$width/8);
		$start_y=ceil(self::$height/4);
		imagestring($im,5,$start_x,$start_y,$codeempty,$text_color);
		ob_start();
		//显示图片
		imagepng($im);
		$contents=ob_get_contents();
		ob_end_clean();
		//摧毁图片
		imagedestroy($im);
		$return['code']=$code;
		$return['basecode']="data:image/png;base64,".base64_encode($contents);
		return $return;
	}
}
?>
