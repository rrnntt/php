<?php
	require('Expression.php');

//==================================================================================
/**
 * A base class for a math problem
 */
class Problem
{
// ---------------------------------------------------------------------------------
	/// Text of the problem
	private $text = '';

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
		//$str = preg_replace_callback(':\$\$(.+?)\$\$:','Problem::repl_latex2',$this->text);
		//$str = preg_replace_callback(':\$(.+?)\$:','Problem::repl_latex1',$str);
		return $this->text;
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

?>
