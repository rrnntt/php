<?php
    if (! defined('SIMPLE_TEST')) {
        define('SIMPLE_TEST', '../simpletest/');
    }
    require_once(SIMPLE_TEST . 'unit_tester.php');
    require_once(SIMPLE_TEST . 'reporter.php');
    require_once('ProblemFactory.php');

    class TestProblemFactory extends UnitTestCase {
		function __construct() {
			parent::__construct();
		}
		function testTextProblem()
		{
			$content = '<problem><text>Problem question.</text><answer>The answer.</answer></problem>';
			$p = ProblemFactory::create('text',$content);
			$txt=$p->getText();
			echo "text=$txt\nans=$p->answer\n";
			$this->assertEqual( $p->getText(), 'Problem question.' );
			$this->assertEqual( $p->answer, 'The answer.' );
		}
    }
	
    $test = new TestProblemFactory();
    $test->run(new TextReporter());
?>