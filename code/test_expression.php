<?php
/* require('expression.php');

try
{
	echo latex('1+a/b'),"\n\n";
	echo latex('\5'),"\n\n";
	echo latex('\(a+b)'),"\n\n";
	echo latex('(a+'),"\n\n";
}
catch(Exception $ex)
{
	echo "Exception:\n";
	echo $ex->getMessage(),"\n\n";
	echo "In ".$ex->getFile()."  at line ".$ex->getLine()."\n\n";
}
 */
?>
<?php
    if (! defined('SIMPLE_TEST')) {
        define('SIMPLE_TEST', '../simpletest/');
    }
    require_once(SIMPLE_TEST . 'unit_tester.php');
    require_once(SIMPLE_TEST . 'reporter.php');
    require_once('../code/expression.php');

    class TestExpression extends UnitTestCase {
		function __construct() {
			parent::__construct();
		}
        function testStuff() {
            //@unlink('../temp/test.log');
			$this->assertTrue(true);
        }
		
		function testBrr()
		{
			$str = 'a+(b+(c+1))';
			$brr = new Brr();
			$substituted = $brr->sub($str);
			$this->assertEqual($substituted,'a+([0]b+([1]c+1)[1])[0]');
			$substituted = str_replace('a','A',$substituted);
			$substituted = str_replace('b','B',$substituted);
			$substituted = str_replace('c','C',$substituted);
			$substituted = $brr->subback($substituted);
			$this->assertEqual($substituted,'A+(B+(C+1))');
		}
		function testSimpleVar()
		{
			$e = new Expression();
			$e->parse('x');
			$this->assertFalse($e->is_operator());
			$this->assertFalse($e->is_function());
			$this->assertFalse($e->is_number());
			$this->assertEqual($e->fun,'x');
		}
		function testSimplePlus()
		{
			$e = new Expression();
			$e->parse('x + y');
			$this->assertTrue($e->is_operator());
			$this->assertTrue($e->is_function());
			$this->assertFalse($e->is_number());
			$this->assertEqual($e->fun,'+');
			$this->assertEqual(sizeof($e->terms),2);
			$this->assertEqual($e->terms[0]->fun,'x');
			$this->assertEqual($e->terms[1]->fun,'y');
			$this->assertEqual($e->terms[0]->op,'');
			$this->assertEqual($e->terms[1]->op,'+');
		}
		function testSimplePlus1()
		{
			$e = new Expression('x + y');
			$this->assertTrue($e->is_operator());
			$this->assertTrue($e->is_function());
			$this->assertFalse($e->is_number());
			$this->assertEqual($e->fun,'+');
			$this->assertEqual(sizeof($e->terms),2);
			$this->assertEqual($e->terms[0]->fun,'x');
			$this->assertEqual($e->terms[1]->fun,'y');
			$this->assertEqual($e->terms[0]->op,'');
			$this->assertEqual($e->terms[1]->op,'+');
		}
		function testGetVariables()
		{
			$e = new Expression('2*x + y*sin(4*x)');
			$v = $e->getVariables();
			$this->assertEqual(sizeof($v),2);
			$this->assertEqual($v[0], 'x');
			$this->assertEqual($v[1], 'y');
		}
		
		function test_php_expr()
		{
			$e = new Expression('x + y');
			$this->assertEqual( $e->php_expr(), '($x+$y)' );
			$this->assertEqual( $e->eval_double( array( 'x' => 1, 'y' => 3 ) ), 4 );
		}
		
		function test_unary_minus()
		{
			$e = new Expression('-x');
			$this->assertEqual($e->fun,'+');
			$this->assertEqual(sizeof($e->terms),1);
			$this->assertEqual($e->terms[0]->fun,'x');
			$this->assertEqual($e->terms[0]->op,'-');

			$e = new Expression('-x+1 = y');
			$this->assertEqual($e->fun,'=');
			$this->assertEqual(sizeof($e->terms),2);
			$this->assertEqual($e->terms[0]->fun,'+');
			$this->assertEqual($e->terms[1]->fun,'y');

			$e = new Expression('x+1 >=-y');
			$this->assertEqual($e->fun,'=');
			$this->assertEqual(sizeof($e->terms),2);
			$this->assertEqual($e->terms[0]->fun,'+');
			$this->assertEqual($e->terms[1]->fun,'+');
		}
	}

	$test = new TestExpression();
	$test->run(new TextReporter());
?>