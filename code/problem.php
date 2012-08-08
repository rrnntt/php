<?php
	require_once('expression.php');

//==================================================================================
/**
 * A base class for a math problem
 */
class Problem
{
// ---------------------------------------------------------------------------------
	/// Text of the problem
	private $text = '';

	// default constructor
	function __construct() 
	{
	}
	
// ---------------------------------------------------------------------------------
//			Public methods
// ---------------------------------------------------------------------------------

	/**
	 * Set the text of the problem.
	 */
	function setText( $str )
	{
		$this->text = $str;
	}

	/**
	 * Return the text of the problem processed for publishing: formulas are replaced with
	 * their latex code, etc.
	 */
	function getText()
	{
		$str = preg_replace_callback(':\$\$(.+?)\$\$:','Problem::repl_latex2',$this->text);
		$str = preg_replace_callback(':\$(.+?)\$:','Problem::repl_latex1',$str);
		return $str;
	}
	
// ---------------------------------------------------------------------------------
//			Private methods
// ---------------------------------------------------------------------------------
	/**
	 * A replace callback function to replace $text$ with \(latex\)
	 */
	private function repl_latex1($m)
	{
		$n = sizeof($m);
		return '\\('.latex( $m[1] ).'\\)';
	}

	/**
	 * A replace callback function to replace $$text$$ with \[latex\]
	 */
	private function repl_latex2($m)
	{
		$n = sizeof($m);
		return '\\['.latex( $m[1] ).'\\]';
	}
	
}
//==================================================================================
/**
 * A base class for a math problem in which a set of equations must be solved.
 */
class EquationProblem extends Problem
{
// ---------------------------------------------------------------------------------
//			Member variables
// ---------------------------------------------------------------------------------
	/// Initial equations
	public $equations = array();
	/// Variables used in the equations
	public $vars = array();
	
// ---------------------------------------------------------------------------------
//			Public methods
// ---------------------------------------------------------------------------------
	/**
	 * Add an initial equation. If any of the variables in the equation is not
	 * in $this->vars yet it is added with value of 'unset'.
	 * @param $str :: An equation as a string
	 */
	function addEquation( $str )
	{
		$e = new Expression( $str );
		$vars = $e->getVariables();
		foreach($vars as $v)
		{
			$this->addVariable( $v, 'unset' );
		}
	}
	/**
	 * Add a variable. 
	 * @param $name :: A name of the variable
	 * @param $value :: The value of the variable
	 */
	function addVariable( $name, $value )
	{
		if ( !array_key_exists( $name, $this->vars ) )
		{
			$this->vars[$name] = $value;
		}
	}
	/**
	 * Return a list of all variable
	 */
	function getVariables()
	{
		return array_keys( $this->vars );
	}
	
	/**
	 * Get a value of a variable. If the variable doesn't exist
	 * return NULL
	 * @param $var :: A name of a variable.
	 */
	function getValue( $var )
	{
		if ( !array_key_exists( $var, $this->vars ) )
		{
			return NULL;
		}
		return $this->vars[$var];
	}
	
	/**
	 * Set a value of a variable. If the variable doesn't exist
	 * add it
	 * @param $var :: A name of a variable.
	 * @param $value :: The new value of the variable
	 */
	function setValue( $var, $value )
	{
		$this->vars[$var] = $value;
	}
	
	/**
	 * Test if a variable has a particular value.
	 * @param $var :: A name of a variable
	 * @param $value :: The value to test
	 * @return :: True if the value is correct or false otherwse.
	 */
	function checkValue( $var, $value )
	{
		$v = $this->getValue( $var );
		if ( $v == NULL )
		{
			throw new Exception("Variable $var isn't defined.");
		}
		if ( $v == 'unset' )
		{
			throw new Exception("Variable $var isn't set.");
		}
		return $v == $value;
	}
	
	/**
	 * Check that an equation is correct with the variable values in this problem.
	 * All variables in the equation must be defined in this problem.
	 * @param $str :: An equation as a string.
	 * @return :: A string 'true' if the equation is correct and error message otherwise.
	 */
	function checkEquation( $str )
	{
		$e = new Expression( $str );
		if ( $e->fun != '=' )
		{
			return 'not equation';
		}
		$vars = $e->getVariables();
		$undef = $this->findUndefinedVariables( $vars );
		if ( sizeof($undef) > 0 )
		{
			return 'undefined: '.implode(', ', $undef);
		}
		$lhs = $e->terms[0];
		$rhs = $e->terms[1];
		$fun = $rhs->op;
		$lv = $lhs->eval_double( $this->vars );
		$rv = $rhs->eval_double( $this->vars );
		$res = 'oops';
		if ( $fun == '=' )
		{
			$res = $lv == $rv ? 'true' : 'false';
		}
		else if ( $fun == '>' )
		{
			$res = $lv > $rv ? 'true' : 'false';
		}
		else if ( $fun == '<' )
		{
			$res = $lv < $rv ? 'true' : 'false';
		}
		else if ( $fun == '>=' )
		{
			$res = $lv >= $rv ? 'true' : 'false';
		}
		else if ( $fun == '<=' )
		{
			$res = $lv <= $rv ? 'true' : 'false';
		}
		return $res;
	}
// ---------------------------------------------------------------------------------
//			Private methods
// ---------------------------------------------------------------------------------
	/**
	 * Find all names in $vars that not defined as variables in theis problem.
	 * @param $vars :: An array of variable names.
	 */
	private function findUndefinedVariables( $vars )
	{
		$out = array();
		foreach($vars as $v)
		{
			if ( $this->getValue( $v ) == NULL )
			{
				array_push( $out, $v );
			}
		}
		return $out;
	}
}
// ---------------------------------------------------------------------------------

?>
