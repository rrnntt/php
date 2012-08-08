<?php

$istop = 0;

$precedence = array(
 ','=>1,

 '='=>2,
 '>'=>2,
 '<'=>2,
 '!='=>2,
 '>='=>2,
 '<='=>2,

 '+'=>3,
 '-'=>3,

 '*'=>4,
 '/'=>4,

 '^'=>5,

 '@'=>6
);

$greek = array(
'pi' => '\pi',
'Pi' => '\Pi',
'alf' => '\alpha',
'bet' => '\beta',
'gam' => '\gamma',
'dlt' => '\delta',
'Dlt' => '\Delta',
'sig' => '\sigma',
'xi' => '\xi',
'zet' => '\zeta',
'eps' => '\epsilon',
'kap' => '\kappa',
'om' => '\omega',
'Ohm' => '\Omega',
'ee' => 'e'
);

$op_name = array(1=>',',2=>'=',3=>'+',4=>'*',5=>'^',6=>'@');

//$brackets_re_template = '|(\w*\(\[%d\].+?\)\[%d\])|';
$brackets_re_template = '|(\(\[%d\].+?\)\[%d\])|';
$brackets_re = array();


// Replace matching brackets with tags '([n]' and ')[n]' where n is an int number
class Brr
{
    function Brr()
    {
      $this->i = 0;
      $this->string = '';
      //self.brak = re.compile(r'([\(\)])'); # match ( or )
    }
    
    function repl($mo)
    {
        if ($mo[0] == '(')
        {
           $i = $this->i;
           $this->i += 1;
           return '(['.$i.']'; # replace '(' with '[<n>' 
        }
        else
        {
           $this->i -= 1;
           return ')['.$this->i.']'; # replace ')' with ']<n>'
        }
    }

    // replace brackets with the numbered tags
    function sub($expr)
    {
      $this->i = 0;
      $this->string = $expr;
      return preg_replace_callback('|([\(\)])|','Brr::repl',$this->string);
    }

    // reaplce the tags back to normal brackets
    function subback($str)
    {    
      return preg_replace('|([()])\[\d+\]|','$1',$str);
    }

}

/** An arithmetic expression
 */
class Expression
{

	public $op = '';
	public $fun = '';
	public $terms = array();

	private $brackets = array();
	private $ibra = 0;
	private $brackets_re = array();
	
	//function Expression()
	function __construct($str = '')
	{
		if ( strlen($str) > 0 )
		{
			$this->parse( $str );
		}
	}
// ---------------------------------------------------------------------------------
//			Public methods
// ---------------------------------------------------------------------------------

	function is_operator()
	{
		global $precedence;
		return array_key_exists($this->fun,$precedence);
	}

	function is_function()
	{
		return sizeof($this->terms) > 0;
	}

	function is_number()
	{
		return sizeof($this->terms) == 0 && preg_match(':[\d\.]+:',$this->fun);
	}

	/**
	 * Parse a string into an expression
	 */
	function parse($str)
	{
		// replace backslashes with sqrt, eg \2 -> sqrt(2)
		$ss = preg_replace(':\\\\([\w\.]+):','sqrt($1)',$str);
		// this replaces \(stuff) with sqrt(stuff), eg \(a^2+b^2) -> sqrt(a^2+b^2)
		$ss = preg_replace(':\\\\\((.+)\):','sqrt($1)',$ss);
		// equations/inequalities containing unary '-' are parsed wrongly
		// fix it by putting both part in brackets
		$ss = preg_replace(':(.+?)(<=|>=|<|>|=)(.+):','($1)$2($3)',$ss);
		//echo $ss.'<br>';
		$brr = new Brr();
		$c = $brr->sub($ss);
		$this->parse1($c,0);
		array_splice( $this->brackets, 0 );
		array_splice( $this->brackets_re, 0 );
	}
	
	/**
	 * Return a list of all variables in the expression
	 */
	function getVariables()
	{
		$vars = array();
		$this->collectVariables( $vars );
		return $vars;
	}

	/**
	 * Recursively collects all variables in the expression into an array
	 * @param &$vars :: Reference to an array collecting the variable names
	 */
	private function collectVariables( &$vars )
	{
		if ( $this->is_function() )
		{
			foreach($this->terms as $e)
			{
				$e->collectVariables( $vars );
			}
		}
		else if ( !$this->is_number() and !in_array( $this->fun, $vars ) )
		{
			array_push( $vars, $this->fun );
		}
	}

	/**
	 * Return latex code for this expression.
	 */
	function latex()
	{
		if ($this->fun == '*' && sizeof($this->terms) == 2 && $this->terms[1]->op == '/')
		{
			return '\frac{'.$this->terms[0]->latex().'}{'.$this->terms[1]->latex().'}';
		}
		global $precedence;
		$out = '';
		$n = sizeof($this->terms); // number of terms
		$im_operator = $this->is_operator() && $this->fun != '^';
		$im_times = $this->fun == '*';
		$p = $precedence[$this->fun];
		// loop over the terms
		for($i=0;$i<$n;++$i)
		{
			// make latex for the term
			$t = $this->terms[$i];
			$tout = $t->latex();
			if ($im_operator && $t->is_operator() && $p > $precedence[$t->fun])
			{
				$tout = '('.$tout.')';
			}
			$op = $t->op;
			// treat special cases of operator: replace symbols with latex commands
			if ($im_times && $op == '*')
			{
				if ($t->is_number() && $this->terms[$i-1]->is_number())
				{
					$op = '\times ';
				}
				else
				{
					$op = ' ';
				}
			}
			if ($op == '>=')
			{
				$op = '\\ge';
			}
			if ($op == '<=')
			{
				$op = '\\le';
			}
			// add term's code to the output
			$out .= $op.'{'.$tout.'}';
		}
		if ($n > 0)
		{// I am a named function such as sin(...)
			if (!array_key_exists($this->fun,$precedence))
			{
				if ($n > 1 || sizeof($this->terms[0]->terms) > 0)
				{
					$out = '('.$out.')';
				}
				$out = '\\'.$this->fun.'{'.$out.'}';
			}
		}
		else
		{// I am a simple variable
			global $greek;
			if (strlen($this->fun) > 1 && array_key_exists($this->fun,$greek))
			{
				$out = $greek[$this->fun].' ';
			}
			else
			{
				$out = $this->fun;
			}
			//if (strlen($this->op) > 0)
			//{
			//	$out = '{'.$out.'}';
			//}
		}
		//$out = preg_replace(':(\d+)\*{(\d+)}:','${1}\times ${2}',$out);
		//$out = preg_replace(':\*:',' ',$out);
		return $out;
	}

	function log($br = '<br>')
	{
	  echo $this->op.$this->fun;
	  $n = sizeof($this->terms);
	  if ($n > 0) echo '[';
	  for($i=0;$i<$n;++$i)
	  {
		$this->terms[$i]->log(' ');
	  }
	  if ($n > 0) echo ']';
	  echo $br;
	}

	/**
	 * Returns the expression as a php expression, variable names prefixed with $
	 */
	function php_expr()
	{
		if (sizeof($this->terms) == 0)
		{// a variable or number
			if (preg_match(':^\d.*:',$this->fun))
			{
				return $this->fun;
			}
			else
			{
				return '$'.$this->fun;
			}
		}
		global $precedence;
		$res = '';
		if ($this->fun == '^')
		{
			if (sizeof($this->terms) != 2)
			{
				throw new Exception('Operator ^ cannot be chained, use brackets');
			}
			$res = 'pow';
		}
		else if (!array_key_exists($this->fun,$precedence))
		{
			$res .= $this->fun;
		}
		$res .= '(';
		foreach($this->terms as $e)
		{
			$op = $e->op == '^' ? ',' : $e->op;
			if ( $op == '=' ) $op = '=='; // throw instead?
			$res .= $op.$e->php_expr();
		}
		$res .= ')';
		return $res;
	}

	/**
	 * Evaluate the expression and return the result as a double
	 * @param $vars :: variable value dictionary, eg: array('a'=>1,'b'=>2)
	 */
	function eval_double($vars)
	{
		$pi = M_PI;
		$ee = M_E;
		reset($vars);
		while (list($key, $val) = each($vars)) {
			eval("\$$key = $val;");
		}
		return eval('return '.$this->php_expr().';');
	}

	/**
	 * Evaluate the expression multiple times and return the result as an array of doubles
	 * @param $vars :: variable value dictionary, eg: array('a'=>1,'b'=>2)
	 * @param $x :: name of the running variable
	 * @param $xvalues :: array of values for the running variabe (in $x)
	 * @return Array of doubles calculated for each value in $xvalues
	 */
	function eval_double_array($vars,$x,$xvalues)
	{
		$pi = M_PI;
		$ee = M_E;
		reset($vars);
		while (list($key, $val) = each($vars)) {
			eval("\$$key = $val;");
		}
		$res = array();
		$cmd = 'foreach($xvalues as $val){$'.$x.'=$val; array_push($res,'.$this->php_expr().');};';
		eval($cmd);
		return $res;
	}
	
// ---------------------------------------------------------------------------------
//			Private methods
// ---------------------------------------------------------------------------------
	private function get_brackets_re($n)
	{
		global $brackets_re_template;
		return sprintf($brackets_re_template,$n,$n);
		/*$l = sizeof($this->brackets_re);
		echo 'getting brackets '.$n.' '.$l.' <br>';
		if ($n < $l)
		{
			return $this->brackets_re[$n-1];
		}
		else if ($n == $l)
		{
			$r = sprintf($brackets_re_template,$n,$n);
			array_push($this->brackets_re,$r);
			return $r;
		}
		else
		{
			$this->get_brackets_re($n-1);
			return $this->get_brackets_re($n);
		}*/
	}
	  
  private function repl_brackets($m)
  {
	//echo 'repl_brakets '.$m[1].'<br>';
    $i = sizeof($this->brackets);
    if ($this->ibra > $i)
    {
      $i = $this->ibra;
    }
    $key = '{'.$i.'}';
    $this->brackets[$key] = $m[0];
    $this->ibra += 1;
    return $key;
  }
  
	/*
	* Add a name (?)
	* @param $name :: array of 2 elements (tuple) with first being op and second is fun
	*/
	private function add_name($name,$n)
	{
		/*echo 'add name:'.$name[1].'<br>';
		while (list($key, $val) = each($this->brackets)) {
			echo "$key => $val<br>";
		}//*/
		$e = new Expression();
		$e->op = $name[0];
		if (preg_match(':(\w*)({\d+}):',$name[1],$m))
		{
			$s = $this->brackets[$m[2]];
			//echo 'found:'.$s.'<br>';
			//if (preg_match('|(\w+)\s*\(\[\d+\](.+)\)|',$s,$m) > 0)
			if (strlen($m[1]) > 0)
			{
				$e->parse1($s,$n);
				if ($e->fun != ',')
				{
					$e1 = $e;
					$e = new Expression();
					$e->op = $e1->op;
					$e1->op = '';
					array_push($e->terms,$e1);
				}
				$e->fun = $m[1];
			}
			else
			{
				$e->parse1($s,$n);
			}
		}
		else
		{
			$e->fun = $name[1];
		}
		array_push($this->terms,$e);
	}

  private function set_name($s,$n)
  {
    //echo 'set name:'.$s.'<br>';
    if (preg_match(':(\w*)({\d+}):',$s,$m))
    {
      $s = $this->brackets[$m[2]];
      //if (preg_match(':(\w+)\s*\(\[\d+\](.+)\):',$s,$m))
      if (strlen($m[1]) > 0)
      {
	  //echo 'hi:'.$s.'<br>';
        $e = new Expression();
        $e->parse1($s,$n);
		//echo 'log:';$e->log();
        if ($e->fun == ',')
        {
          $this->terms = $e->terms;
        }
        else
        {
          array_push($this->terms,$e);
        }
        $this->fun = $m[1];
      }
      else
      {
        $this->parse1($s,$n);
      }
    }
    else
    {
      $this->fun = $s;
    }
  }

	private function parse1($s,$n,$names = array())
	{
		/*global $istop;
		$istop++;
		if ($istop > 10) return;
		echo 'parse '.$s.' '.$n.' '.sizeof($names).' '.$istop.'<br>';
		foreach($names as $name)
		{
			echo 'name '.$name[0].$name[1].'<br>';
		}//*/
		if ($s && sizeof($s) > 0)
		{
			$r = $this->get_brackets_re($n);
			$this->string = $s;
			//echo 're('.$n.')='.$r.'<br>';
			$s = preg_replace_callback($r,'Expression::repl_brackets',$s);
			//echo 'repl '.$s.'<br>';
			foreach($this->brackets as $key => $value)
			{
				//echo 'key '.$key.' value='.$value.'<br>';
				$value = preg_replace('|([\(\)])\['.$n.'\]|','${1}',$value);
				if ($value[0] == '(' && $value[strlen($value)-1] = ')')
				{
					$value = substr($value,1,strlen($value)-2);
				}
				//echo 'xvalue='.$value.'<br>';
				$this->brackets[$key] = $value;
			}
			// fill in an array of pairs (op,name) in $s
			$names = array();
			if (preg_match(':^\s*(\w*{\d+}|[\w.]+|{\d+}):',$s,$m))
			//if (preg_match(':^\s*([\w.]+|{\d+}):',$s,$m))
			{
				//echo 'push '.$m[1].'<br>';
				array_push($names,array('',$m[1]));
			}
			$ms = array();
			//echo 'parsing '.$s.'<br>';
			$res = preg_match_all(':([=<>+\-*/^,]+)\s*(\w*{\d+}|[\w.]+|{\d+}):',$s,$ms);
			for($i = 0; $i < $res; ++$i)
			{
				//echo 'term:'.$ms[1][$i].' '.$ms[2][$i].'<br/>';
				array_push($names,array($ms[1][$i],$ms[2][$i]));
			}
		}
		// find the smallest precedence
		$prec = 1000;
		global $precedence, $op_name;
		
		for($i = 1; $i < sizeof($names); ++$i)
		{
			$name = $names[$i];
			$op = $name[0];
			if (strlen($op) == 0) continue;
			if (array_key_exists($op,$precedence))
			{
				$p = $precedence[$op];
				if ($p < $prec)
				{
					$prec = $p;
				}
			}
			else
			{
				 throw new Exception('Operator ['.$op.'] is not defined');
			}
		}
		if ($prec > sizeof($op_name))
		{
			/*foreach($names as $name)
			{
				echo 'name '.$name[0].$name[1].'<br>';
			}//*/
			//$this->fun = 'function';
			if (sizeof($names) != 1)
			{
				throw new Exception('Found strange function '.$this->fun.' '.sizeof($names));
			}
			$op = $names[0][0];
			if ($op == '')
			{
				$this->set_name($names[0][1],$n+1);
			}
			else
			{
				$this->fun = $op_name[$precedence[$op]];
				$this->add_name($names[0],$n+1);
			}
			return;
		}
		else
		{
			$this->fun = $op_name[$prec];
		}
		$i = 0;
		$l = sizeof($names);
		for($k = 1;$k < sizeof($names); ++$k)
		{
			$name = $names[$k];
			$op = $name[0];
			$p = $precedence[$op];
			if ($p == $prec)
			{
				$j = $k;
				if ($j == $i + 1)
				{
					$this->add_name($names[$i],$n+1);
				}
				else
				{
					$e = new Expression();
					$e->brackets = $this->brackets;
					$e->op = $names[$i][0];
					$names[$i] = array('',$names[$i][1]);
					$e->parse1('',$n+1,array_slice($names,$i,$j-$i));
					array_push($this->terms,$e);
				}
				$i = $j;
			}
		}
		if ($i == $l - 1)
		{
			$this->add_name($names[$i],$n+1);
		}
		else
		{
			$e = new Expression();
			$e->brackets = $this->brackets;
			$e->op = $names[$i][0];
			$names[$i] = array('',$names[$i][1]);
			$e->parse1('',$n+1,array_slice($names,$i,$l-$i));
			array_push($this->terms,$e);
		}
	}
	

/**
 * Return latex code for the expression.
 */

}; // class Expression

function latex($str)
{
$e1 = new Expression();
$e1->parse($str);
return $e1->latex();
}

?>
