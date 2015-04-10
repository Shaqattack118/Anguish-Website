<?php
class StringBuilder {
	
	private $string;
	
	public function __construct($string = "") {
		$this->string = $string;
	}
	
	public function append($data) {
		$this->string .= $data;
	}
	
	public function getString() {
		return $this->string;
	}
	
	public function displayString() {
		echo $this->string;
	}
	
	public function removeAll($needle) {
		str_replace($needle, "", $this->string);
	}
	
	public function replaceAll($replaceMe, $with) {
		str_replace($replaceMe, $with, $this->string);
	}
	public static function removeAllIn($needle, $string) {
		str_replace($needle, "", $string);
		return $string;
	}
	
	public static function replaceAllIn($replaceMe, $with, $string) {
		str_replace($replaceMe, $with, $string);
		return $string;
	}
	
}
?>