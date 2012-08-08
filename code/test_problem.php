<?php
    if (! defined('SIMPLE_TEST')) {
        define('SIMPLE_TEST', '../simpletest/');
    }
    require_once(SIMPLE_TEST . 'unit_tester.php');
    require_once(SIMPLE_TEST . 'reporter.php');
    require_once('problem.php');

    class TestProblem extends UnitTestCase {
		function __construct() {
			parent::__construct();
		}
		function testGetText()
		{
			$p = new Problem();
			$p->setText( 'aaa $x+y$ bbbb $$1+a/b$$.' );
			$this->assertEqual( $p->getText(), 'aaa \\({x}+{y}\\) bbbb \\[{1}+{\frac{a}{b}}\\].' );
		}
		function testEquation_GetVariables()
		{
			$p = new EquationProblem();
			$p->addVariable( 'x', 11.2 );
			$p->addVariable( 'a', 2.5 );
			$v = $p->getVariables();
			$this->assertEqual( sizeof($v), 2 );
			$this->assertEqual( $v[0], 'x' );
			$this->assertEqual( $v[1], 'a' );
		}
		function testEquation_VariableVaues()
		{
			$p = new EquationProblem();
			$p->addVariable( 'a', 11.2 );
			$p->addVariable( 'b', 2.5 );
			$p->addEquation('a*x + b');
			$va = $p->getValue('a');
			$vb = $p->getValue('b');
			$vx = $p->getValue('x');
			$vy = $p->getValue('y');
			$this->assertEqual( $va, 11.2 );
			$this->assertEqual( $vb, 2.5 );
			$this->assertEqual( $vx, 'unset' );
			$this->assertEqual( $vy, NULL );
			
			$this->assertTrue( $p->checkValue('a', 11.2) );
			$this->assertFalse( $p->checkValue('b', 11.2) );
			
			$this->expectException();
			$p->checkValue('x', 11.2);
			$p->checkValue('D', 11.2);
		}
		function testEquation_CheckEquation()
		{
			$p = new EquationProblem();
			$p->addVariable( 'a', 3 );
			$p->addVariable( 'b', 4 );
			$this->assertEqual( $p->checkEquation('a+3'), 'not equation' );
			$this->assertEqual( $p->checkEquation('a-c*x=3'), 'undefined: c, x' );
			$this->assertEqual( $p->checkEquation('a=3'), 'true' );
			$this->assertEqual( $p->checkEquation('a^2 = 9'), 'true' );
			$this->assertEqual( $p->checkEquation('a^2 + 1 = 9'), 'false' );
			$this->assertEqual( $p->checkEquation('a^2 + b^2 = 25'), 'true' );
			$this->assertEqual( $p->checkEquation('\(a^2 + b^2) = 5'), 'true' );
			$this->assertEqual( $p->checkEquation('a>3'), 'false' );
			$this->assertEqual( $p->checkEquation('a >= 3'), 'true' );
			$this->assertEqual( $p->checkEquation('a> 1'), 'true' );
			$this->assertEqual( $p->checkEquation('a> -3'), 'true' );
		}
	}
    
    $test = new TestProblem();
    $test->run(new TextReporter());
?>