<?php

Prado::using('Application.code.ProblemFactory');

class ProblemRenderer extends TRepeaterItemRenderer
{
	public function getText()
	{
		$p = ProblemFactory::create($this->Data->problem_type,$this->Data->content);
		return $p->getText();
	}
}

?>
