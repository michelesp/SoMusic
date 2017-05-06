<?php

class SOMUSIC_CLASS_Lzw {
	private $dictionary;
	private $invDictionary;
	
	public function __construct() {
		$this->dictionary = array();
		$this->dictionary[0] = "";
		$this->invDictionary = array();
		$this->invDictionary[""] = 0;
	}
	
	public function compress($string){
		$data = "";
		$start = 0;
		$couples = array();
		$nTokens = 0;
		$str = "";
		$lastIndex = 0;
		$token = $this->nextToken($string, $start);
		$start += strlen($token);
		while(!is_bool($token)) {
			$nTokens++;
			$str.=$token;
			if(!isset($this->invDictionary[$str])) {
				$index = count($this->dictionary);
				$this->dictionary[$index] = $str;
				$this->invDictionary[$str] = $index;
				array_push($couples, array($lastIndex, $token));
				$p = pack("SZ*", $lastIndex, $token);
				$data.=$p;
				$str = "";
				$lastIndex = 0;
			}
			else $lastIndex = $this->invDictionary[$str];
			$token = $this->nextToken($string, $start);
			$start += strlen($token);
		}
		if(strlen($str)>0) {
			array_push($couples, array($lastIndex, null));
			$p = pack("SZ*", $lastIndex, "");
			$data.=$p;
		}
		return $data;
	}
	
	public function decode($data) {
		$dictionary = array();
		$dictionary[0] = "";
		$output = "";
		$lenData = strlen($data);
		$i = 0;
		while($i<$lenData) {
			$index = unpack("S", substr($data, $i, 2))[1];
			$i += 2;
			$str = "";
			$ch = unpack("C", $data[$i])[1];
			$i++;
			while($ch!=0) {
				$str.=chr($ch);
				$ch = unpack("C", $data[$i])[1];
				$i++;
			}
			$str1 = $dictionary[$index].$str;
			$output .= $str1;
			$dictionary[count($dictionary)] = $str1;
		}
		return $output;
	}

	private function nextToken($string, $start) {
		$len = strlen($string);
		if($len<=$start)
			return false;
		$token = $string[$start];
		for($i=$start+1; $i<$len && is_numeric($string[$i]); $i++) {
			$token.=$string[$i];
		}
		return $token;
	}


}

?>