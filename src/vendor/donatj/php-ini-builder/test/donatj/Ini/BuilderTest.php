<?php

namespace donatj\Ini\Test;

use donatj\Ini\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @expectedException \donatj\Ini\ExceededMaxDepthException
	 */
	public function testMaxDepthException() {
		$data    = array( 'x' => array( 'y' => array( 'z' => array( 'a' => 1 ) ) ) );
		$builder = new Builder();
		$builder->generate($data);
	}

	public function testEnableBoolDetection() {
		$builder = new Builder();
		$this->assertStringEndsWith("true", $builder->generate(array( 'x' => 1 )));

		$builder->enableBoolDetection(false);
		$this->assertStringEndsWith("1", $builder->generate(array( 'x' => 1 )));
	}

	public function testEnableNumericDetection() {
		// Integer
		$builder = new Builder();
		$this->assertStringEndsWith("7", $builder->generate(array( 'x' => 7 )));

		$builder->enableNumericDetection(false);
		$this->assertStringEndsWith("'7'", $builder->generate(array( 'x' => 7 )));

		// Float
		$builder->enableNumericDetection(true);
		$this->assertStringEndsWith("3.14159265", $builder->generate(array( 'x' => 3.14159265 )));

		$builder->enableNumericDetection(false);
		$this->assertStringEndsWith("'3.14159265'", $builder->generate(array( 'x' => 3.14159265 )));
	}

	public function testNumericIndex() {

		$data    = array( 'x' => array( 'y' => array( 'a' => 'test', '2', '3', '4', 6 => '4', '7', 5 => 'bbq', 'bbq' => 'soda' ) ) );
		$builder = new Builder();

		$this->assertSame($data, parse_ini_string($builder->generate($data), true));
	}

	public function testLateRootValues() {
		$builder = new Builder();
		$data    = array(
			'x'    => array(
				'y' => 'testValue'
			),
			'late' => 'value',
		);

		$this->assertTrue($this->arrays_are_similar(parse_ini_string($builder->generate($data), true), $data), 'Assert Late Root Keys Will be Processed');
	}

	public function testSkipNullValues() {
		$builder = new Builder();
		$builder->enableSkipNullValues(true);

		$data = array(
			'x'     => array(
				'z' => null,
			),
			'y'     => array( 1, 2, null, 3 ),
			'other' => null,
		);

		//demands empty x,skip index 2, no other
		$this->assertEquals(trim('[x]

[y]
0 = true
1 = 2
3 = 3'), trim($builder->generate($data)));

	}

	private function arrays_are_similar( $aSide, $bSide ) {

		$keys = array_unique(array_merge(
			array_keys($aSide),
			array_keys($bSide)
		));

		foreach( $keys as $key ) {
			if( !array_key_exists($key, $aSide) || !array_key_exists($key, $bSide) ) {
				return false;
			}

			$aSideValue = $aSide[$key];
			$bSideValue = $bSide[$key];

			if( is_array($aSideValue) && is_array($bSideValue) ) {
				if( !$this->arrays_are_similar($aSideValue, $bSideValue) ) {
					return false;
				}
			} elseif( !is_array($aSideValue) && !is_array($bSideValue) ) {
				if( $aSideValue !== $bSideValue ) {
					return false;
				}
			} else {
				return false;
			}
		}

		return true;
	}

}
 