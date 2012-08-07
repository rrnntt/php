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
	}
    
    $test = new TestProblem();
    $test->run(new TextReporter());
?>