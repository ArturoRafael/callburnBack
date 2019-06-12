<?php namespace App;

class Helper{

	/**
	 * Create a new instance of HelperService
	 *
	 * @return Void
	 */
	public function __construct(){}

	public static function stripHtmlTags($str){
	    $str = preg_replace('/(<|>)\1{2}/is', '', $str);
	    $str = preg_replace(
	        array(// Remove invisible content
	            '@<head[^>]*?>.*?</head>@siu',
	            '@<style[^>]*?>.*?</style>@siu',
	            '@<script[^>]*?.*?</script>@siu',
	            '@<noscript[^>]*?.*?</noscript>@siu',
	            ),
	        "", //replace above with nothing
	        $str );
	    //$str = replaceWhitespace($str);
	    $str = strip_tags($str);
	    return $str;
	}

	public static function request( $url, $request=Array(), $content_type="application/x-www-form-urlencoded"  ){
	    $fields = '';
	    if( count($request) > 0 ){
	        foreach($request as $key => $value) { 
	           $fields .= $key . '=' . urlencode($value) . '&'; 
	        }
	        $fields = substr($fields, 0, -1);
	    }
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: '.$content_type));

	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $response = curl_exec($ch);
	    curl_close($ch);

	    return $response;
	}

	public static function _get_file_ext($filename) {
	    $filename = str_replace('\\', '\/', $filename);
	    $filename = explode('/', $filename);
	    $result = array_reverse($filename);
	    if (count($result) > 0) {
	        $result = explode('.', $result[0]);
	        $result = array_reverse($result);
	        return $result[0];
	    }
	    else
	        return '';
	}

	public static function _extractFileExtension( $filename ){
	    $tmp=explode(".",$filename);
	    return $tmp[count($tmp)-1];
	}

	public static function _stripFileExtension( $filename ){
	    $tmp=explode(".",$filename);
	    $extension = $tmp[count($tmp)-1];
	    return str_replace( '.'.$extension, '', $filename );
	}

	public static function _extractFileName( $filename ){
	    $tempFile=explode("\\",$filename);
	    if(count($tempFile)==1){
	        $tempFile=explode("/",$filename);
	    }
	    $tempFile = $tempFile[count($tempFile)-1];
	    return $tempFile;
	}


}