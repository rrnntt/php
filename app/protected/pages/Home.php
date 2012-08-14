<?php
class Home extends TPage
{
    public function onInit($param)
    {
        parent::onInit($param);
		// redirects the browser to some other page
		$url=$this->Service->constructUrl('problems.ListProblems',array('chapter'=>2));
		$this->Response->redirect($url);
    }
}
?>
