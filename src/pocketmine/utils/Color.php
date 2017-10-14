<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);


namespace pocketmine\utils;


class Color{

	/** @var int */
	protected $a, $r, $g, $b;

	/** @var \SplFixedArray */
	public static $dyeColors = null;

	const COLOR_DYE_BLACK = 0;//dye colors
	const COLOR_DYE_RED = 1;
	const COLOR_DYE_GREEN = 2;
	const COLOR_DYE_BROWN = 3;
	const COLOR_DYE_BLUE = 4;
	const COLOR_DYE_PURPLE = 5;
	const COLOR_DYE_CYAN = 6;
	const COLOR_DYE_LIGHT_GRAY = 7;
	const COLOR_DYE_GRAY = 8;
	const COLOR_DYE_PINK = 9;
	const COLOR_DYE_LIME = 10;
	const COLOR_DYE_YELLOW = 11;
	const COLOR_DYE_LIGHT_BLUE = 12;
	const COLOR_DYE_MAGENTA = 13;
	const COLOR_DYE_ORANGE = 14;
	const COLOR_DYE_WHITE = 15;

	public function __construct(int $r, int $g, int $b, int $a = 0xff){
		$this->r = $r & 0xff;
		$this->g = $g & 0xff;
		$this->b = $b & 0xff;
		$this->a = $a & 0xff;
	}

	public static function init(){
		if(self::$dyeColors === null){
			self::$dyeColors = new \SplFixedArray(16);
			self::$dyeColors[self::COLOR_DYE_BLACK] = Color::getRGB(30, 27, 27);
			self::$dyeColors[self::COLOR_DYE_RED] = Color::getRGB(179, 49, 44);
			self::$dyeColors[self::COLOR_DYE_GREEN] = Color::getRGB(61, 81, 26);
			self::$dyeColors[self::COLOR_DYE_BROWN] = Color::getRGB(81, 48, 26);
			self::$dyeColors[self::COLOR_DYE_BLUE] = Color::getRGB(37, 49, 146);
			self::$dyeColors[self::COLOR_DYE_PURPLE] = Color::getRGB(123, 47, 190);
			self::$dyeColors[self::COLOR_DYE_CYAN] = Color::getRGB(40, 118, 151);
			self::$dyeColors[self::COLOR_DYE_LIGHT_GRAY] = Color::getRGB(153, 153, 153);
			self::$dyeColors[self::COLOR_DYE_GRAY] = Color::getRGB(67, 67, 67);
			self::$dyeColors[self::COLOR_DYE_PINK] = Color::getRGB(216, 129, 152);
			self::$dyeColors[self::COLOR_DYE_LIME] = Color::getRGB(65, 205, 52);
			self::$dyeColors[self::COLOR_DYE_YELLOW] = Color::getRGB(222, 207, 42);
			self::$dyeColors[self::COLOR_DYE_LIGHT_BLUE] = Color::getRGB(102, 137, 211);
			self::$dyeColors[self::COLOR_DYE_MAGENTA] = Color::getRGB(195, 84, 205);
			self::$dyeColors[self::COLOR_DYE_ORANGE] = Color::getRGB(235, 136, 68);
			self::$dyeColors[self::COLOR_DYE_WHITE] = Color::getRGB(240, 240, 240);
		}
	}

	public static function getRGB($r, $g, $b){
		return new Color((int) $r, (int) $g, (int) $b);
	}

	/**
	 * Returns the alpha (transparency) value of this colour.
	 * @return int
	 */
	public function getA() : int{
		return $this->a;
	}

	/**
	 * Sets the alpha (opacity) value of this colour, lower = more transparent
	 * @param int $a
	 */
	public function setA(int $a){
		$this->a = $a & 0xff;
	}

	/**
	 * Retuns the red value of this colour.
	 * @return int
	 */
	public function getR() : int{
		return $this->r;
	}

	/**
	 * Sets the red value of this colour.
	 * @param int $r
	 */
	public function setR(int $r){
		$this->r = $r & 0xff;
	}

	/**
	 * Returns the green value of this colour.
	 * @return int
	 */
	public function getG() : int{
		return $this->g;
	}

	/**
	 * Sets the green value of this colour.
	 * @param int $g
	 */
	public function setG(int $g){
		$this->g = $g & 0xff;
	}

	/**
	 * Returns the blue value of this colour.
	 * @return int
	 */
	public function getB() : int{
		return $this->b;
	}

	/**
	 * Sets the blue value of this colour.
	 * @param int $b
	 */
	public function setB(int $b){
		$this->b = $b & 0xff;
	}

	/**
	 * Returns a Color from the supplied RGB colour code (24-bit)
	 * @param int $code
	 *
	 * @return Color
	 */
	public static function fromRGB(int $code){
		return new Color(($code >> 16) & 0xff, ($code >> 8) & 0xff, $code & 0xff);
	}

	/**
	 * Returns a Color from the supplied ARGB colour code (32-bit)
	 *
	 * @param int $code
	 *
	 * @return Color
	 */
	public static function fromARGB(int $code){
		return new Color(($code >> 16) & 0xff, ($code >> 8) & 0xff, $code & 0xff, ($code >> 24) & 0xff);
	}

	/**
	 * Returns an ARGB 32-bit colour value.
	 * @return int
	 */
	public function toARGB() : int{
		return ($this->a << 24) | ($this->r << 16) | ($this->g << 8) | $this->b;
	}

	/**
	 * Returns a little-endian ARGB 32-bit colour value.
	 * @return int
	 */
	public function toBGRA() : int{
		return ($this->b << 24) | ($this->g << 16) | ($this->r << 8) | $this->a;
	}

	/**
	 * Returns an RGBA 32-bit colour value.
	 * @return int
	 */
	public function toRGBA() : int{
		return ($this->r << 24) | ($this->g << 16) | ($this->b << 8) | $this->a;
	}

	/**
	 * Returns a little-endian RGBA colour value.
	 * @return int
	 */
	public function toABGR() : int{
		return ($this->a << 24) | ($this->b << 16) | ($this->g << 8) | $this->r;
	}

	/**
	 * Returns a HSV color array
	 * @return array['h','s','v']
	 */
	public function toHSV() {
		$r = $this->getR();
		$g = $this->getG();
		$b = $this->getB();
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$hsv = array('v' => $max / 2.55, 's' => (!$max) ? 0 : (1 - ($min / $max)) * 100, 'h' => 0);
		$dmax = $max - $min;
		if (!$dmax) return $hsv;
		if ($max == $r) {
			if ($g < $b) {
				$hsv['h'] = ($g - $b) * 60;
			} elseif ($g == $b) {
				$hsv['h'] = 360;
			} else {
				$hsv['h'] = ((($g - $b) / $dmax) * 60) + 360;
			}
		} elseif ($max == $g) {
			$hsv['h'] = ((($b - $r) / $dmax) * 60) + 120;
		} else {
			$hsv['h'] = ((($r - $g) / $dmax) * 60) + 240;
		}
		return $hsv;
	}

	public static function fromABGR(int $code){
		return new Color($code & 0xff, ($code >> 8) & 0xff, ($code >> 16) & 0xff, ($code >> 24) & 0xff);
	}

	public static function getDyeColor($id){
		if(isset(self::$dyeColors[$id])){
			return clone self::$dyeColors[$id];
		}
		return Color::getRGB(0, 0, 0);
	}

	public function getColorCode(){
		return ($this->r << 16 | $this->g << 8 | $this->b) & 0xffffff;
	}

	/**
	 * Returns the logical distance between 2 colors, respecting weight
	 * @param Color $c1
	 * @param Color $c2
	 * @return int
	 */
	public static function getDistance(Color $c1, Color $c2) {
		$rmean = ($c1->getR() + $c2->getR()) / 2.0;
		$r = $c1->getR() - $c2->getR();
		$g = $c1->getG() - $c2->getG();
		$b = $c1->getB() - $c2->getB();
		$weightR = 2 + $rmean / 256;
		$weightG = 4;
		$weightB = 2 + (255 - $rmean) / 256;
		return $weightR * $r * $r + $weightG * $g * $g + $weightB * $b * $b;
	}

	public function __toString() {
		return "Color(r:" . $this->r . ", g:" . $this->g . ", b:" . $this->b . ", a:" . $this->a . ")";
	}
}