<?php 

class Matrix
{
	function Matrix($nrows,$ncols)
	{
		$this->nrows = $nrows;
		$this->ncols = $ncols;
		$this->data = array_fill(0,$nrows * $ncols, 0.0);
	}
	
	function get($row,$col)
	{
		return $this->data[$row * $this->ncols + $col];
	}
	
	function set($row,$col,$value)
	{
		$this->data[$row * $this->ncols + $col] = $value;
	}
	
	function add($row,$col,$value)
	{
		$this->data[$row * $this->ncols + $col] += $value;
	}
	
	function mul($row,$col,$value)
	{
		$this->data[$row * $this->ncols + $col] *= $value;
	}
	
	function log()
	{
		$s = '<br>Matrix '.$this->nrows.' x '.$this->ncols.'<br>';
		for($row=0; $row<$this->nrows;++$row)
		{
			for($col=0; $col<$this->ncols;++$col)
			{
				$s = $s.$this->get($row,$col).' ';
			}
			$s = $s.'<br>';
		}
		$s = $s.'<br>';
		echo($s);
	}
	
	function copy()
	{
		$m = new Matrix($this->nrows,$this->ncols);
		$n = sizeof($this->data);
		for($i = 0; $i < $n; ++$i)
		{
			$m->data[$i] = $this->data[$i];
		}
		return $m;
	}
	
	/**
	 * Multiply this matrix by a vector. Return resulting vector
	 */
	function mulVect($x)
	{
		if ($this->ncols != sizeof($x))
		{
			throw new Exception("Matrix and vector sizes don't match");
		}
		$res = array();
		$nm = $this->ncols * $this->nrows;
		for($row = 0; $row < $nm; $row += $this->ncols)
		{
			$sum = 0.0;
			for($i = 0; $i < $this->ncols; ++$i)
			{
				$sum += $x[$i] * $this->data[$row + $i];
			}
			array_push($res,$sum);
		}
		return $res;
	}
	
	/**
	 * Solves a set of linear equations M.x=b, where M is this matrix.
	 * No singularity checks are done.
	 * @param $b :: The b vector.
	 * @return :: The solution x.
	 */
	function solve_bad($b)
	{
		if ($this->nrows != $this->ncols)
		{
			throw new Exception('Matrix must be square');
		}
		$n = $this->nrows;
		for($row = 0; $row < $n; ++$row)
		{
			//echo 'row'.$row.'<br>';
			for($col = $n-1; $col > $row; --$col)
			{
				$f = $this->get($row,$col);
				if ($f == 0) continue;
				$k = $row+1;
				while($this->get($k,$col) == 0)
				{
					++$k;
				}
				$f = - $this->get($k,$col) / $f;
				//echo 'f '.$row.','.$col.'  '.$f.'<br>';
				$b[$row] *= $f;
				$b[$row] += $b[$k];
				$this->set($row,$col,0.0);
				for($j = $row; $j < $col; ++$j)
				{
					$v = $this->get($row,$j);
					$this->set($row,$j,$v * $f + $this->get($k,$j));
				}
				//$this->log();
				//echo 'b='.json_encode($b).'<br>';
			}
			$b[$row] /= $this->get($row,$row); // this is x[row]
			$x = $b[$row];
			//echo 'x['.$row.']='.$x.'<br>';
			if ($row < $n - 1)
			{
				for($j = $row+1; $j < $n; ++$j)
				{
					$b[$j] -= $this->get($j,$row) * $x;
				}
			}
		}
		return $b;
	}
	
	/*
    Replaces the matrix by its LU decomposition of a rowwise
    permutation of itself. indx is an output vector that records
    the row permutation effected by the partial pivoting;
    d is output as +/-1 depending on whether the number of rows
    interchanges was even or odd respectively.
*/
function ludcmp(&$indx,&$d){
	if ($this->nrows != $this->ncols)
	{
		throw new Exception('Matrix must be square');
	}
	$n = $this->nrows;
	$indx = array_fill(0,$n,0);
	//size_t i,imax,j,k,n;
	//double big,dum,sum,temp;
	$tiny = 1e-20;
	$vv = array_fill(0,$n,0); // vv stores the implicit scaling of each row
	$d = 1.;

	// Loop over rows to get the implicit scaling information
	for($i = 0; $i < $n;++$i)
	{
		$big = 0.;
		for($j = 0; $j < $n; ++$j)
		{
			if (($temp = abs($this->get($i,$j))) > $big) $big = $temp;
		}
		if ($big == 0.)
		{// Singular matrix
			throw new Exception("Singular matrix");
		}
		$vv[$i] = 1./$big;
	}

	for($j = 0; $j < $n; ++$j)
	{
		for($i = 0; $i < $j; ++$i)
		{
			$sum = $this->get($i,$j);
			for($k = 0; $k < $i; ++$k) $sum -= $this->get($i,$k)*$this->get($k,$j);
			$this->set($i,$j,$sum);
		}
		$big = 0.;
		for($i = $j; $i < $n; ++$i)
		{
			$sum = $this->get($i,$j);
			for($k = 0; $k < $j; ++$k) $sum -= $this->get($i,$k)*$this->get($k,$j);
			$this->set($i,$j,$sum);
			if (($dum = $vv[$i]*abs($sum)) >= $big)
			{
				$big = $dum;
				$imax = $i;
			}
		}

		if ($j != $imax)
		{
			for($k = 0; $k < $n; ++$k)
			{
				$dum = $this->get($imax,$k);
				$this->set($imax,$k, $this->get($j,$k));
				$this->set($j,$k,$dum);
			}
			$d *= -1.;
			$dum = $vv[$imax];
			$vv[$imax] = $vv[$j]; //??????? interchange?
			$vv[$j] = $dum;
		}
		$indx[$j] = $imax;
		if ($this->get($j,$j) == 0) $this->set($j,$j,$tiny);
		if ($j < $n - 1)
		{
			$dum = 1./$this->get($j,$j);
			for($i = $j + 1; $i < $n; ++$i)
			{
				$this->mul($i,$j,$dum);
			}
		}
	}
}

/*
   Solves set of n linear equations A.x = b . This matrix must be
   LU decomposition of A after using method ludcmp(...). indx is
   input as the permutation vector returned by ludcmp. b is input as
   righ-hand side vector b, and returns with the solution vector x.
   Matrix and indx are not modified by this method. This routine takes
   into account the possibility that b will begin with many zero
   elements, so it is efficient for use in matrix inversion.
*/
function lubksb($indx,&$b){
	//size_t i0,i,ii,ip,j,n=b.size();
	$n = $this->nrows;
	//double sum;
	$ok = false;
	if (sizeof($indx) != $n)
	{
		throw new Exception("indx size mismatch in lubksb");
	}
	for($i = 0; $i < $n; ++$i)
	{
		$ip = $indx[$i];
		$sum = $b[$ip];
		$b[$ip] = $b[$i];

		if ($ok)
		{
			for($j = $ii; $j < $i; ++$j) $sum -= $this->get($i,$j)*$b[$j];
		}
		else if ($sum != 0.0)
		{
			$ii = $i;
			$ok = true;
		}
		$b[$i] = $sum;
	}

	for($i0 = $n; $i0 >= 1; --$i0)
	{
		$i = $i0 - 1;
		$sum = $b[$i];
		for($j = $i + 1; $j < $n; ++$j) $sum -= $this->get($i,$j)*$b[$j];
		$b[$i] = $sum/$this->get($i,$i);
	}
}


function solve($x){
	$indx = array();
	$this->ludcmp($indx,$d);
	$this->lubksb($indx,$x);
	return $x;
}

};

?>

