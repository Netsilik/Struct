<?php
require(__DIR__.'/../src/struct.php');

use Netsilik\Lib\Struct;

class Simple extends Struct
{
	
	protected $_settableProperties = array('a', 'c', 'f');
	
	private $a;
	
	protected $b;
	
	protected $c;
	
	public $d;
	
	protected $e;
	
	protected $f;
	
	public function setE($value)
	{
		$this->e = 'customPublic';
	}
	
	protected function setF($value)
	{
		$this->f = 'customProtected';
	}
}

class Annotated extends Struct
{
	
	protected $_settableProperties = array('a', 'b', 'c', 'f', 'g', 'h', 'j', 'k', 'l', 'm');
	
	/**
	 * @var string $a
	 */
	protected $a;
	
	/**
	 * @var string $wrong
	 */
	protected $b;
	
	
	/**
	 * @var string NO VARIABLE NAMED
	 */
	protected $c, $d;
	
	/**
	 * @var string $e
	 * NO INFO ON $f
	 */
	protected $e, $f;
	
	/**
	 * @var string No variable name specified
	 */
	protected $g;
	
	/**
	 * @var integer $h
	 * @var string $i
	 */
	protected $h, $i;
	
	/**
	 * @var DateTime $j
	 */
	protected $j;
	
	/**
	 * @var string|array $k
	 */
	protected $k;
	
	/**
	 * @var DateTime|null $l
	 */
	protected $l;
	
	/**
	 * @var DateTime $m
	 */
	protected $m = null;
}