<?php

//Prado::using('Application.Expr');
require(__DIR__.'/../code/Expression.php');

class Home extends TPage
{
	public $test = '0';
    public function onInit($param)
    {
        parent::onInit($param);
		// redirects the browser to some other page
		$url=$this->Service->constructUrl('problems.ListProblems',array('chapter'=>2));
		$this->Response->redirect($url);
		//$e = new Expression('sin(x)+1/y');
		//$t = '$'.$e->latex().'$';
		//$this->test=$t;
    }
}
?>
