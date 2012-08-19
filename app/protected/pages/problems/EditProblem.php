<?php

Prado::using('Application.code.ProblemFactory');

class EditProblem extends TPage
{
    /**
     * Initializes the inputs with existing post data.
     * This method is invoked by the framework when the page is being initialized.
     * @param mixed event parameter
     */
    public function onInit($param)
    {
        parent::onInit($param);
        // Retrieves the existing user data. This is equivalent to:
        // $postRecord=$this->getPost();
        $problemRecord=$this->Problem;
        // Authorization check: only the author or the administrator can edit the post
        //if($postRecord->author_id!==$this->User->Name && !$this->User->IsAdmin)
        //    throw new THttpException(500,'You are not allowed to edit this post.');
 
        if(!$this->IsPostBack)  // if the page is initially requested
        {
            // Populates the input controls with the existing post data
			$p = ProblemFactory::create($problemRecord->problem_type,$problemRecord->content);
            $this->TextEdit->Text=$p->getText();
            $this->AnswerEdit->Text=$p->answer;
        }
    }
 
    /**
     * Saves the post if all inputs are valid.
     * This method responds to the OnClick event of the "Save" button.
     * @param mixed event sender
     * @param mixed event parameter
     */
    public function saveButtonClicked($sender,$param)
    {
        if($this->IsValid)  // when all validations succeed
        {
            // Retrieves the existing user data. This is equivalent to:
            $problemRecord=$this->Problem;
			
			$content = '<problem><text>'.$this->TextEdit->SafeText.'</text><answer>'.
			$this->AnswerEdit->SafeText.'</answer></problem>';
 
            // Fetches the input data
            $problemRecord->content=$content;
 
            // saves to the database via Active Record mechanism
            $problemRecord->save();
			//$this->TextEdit->Text = $content;
			//$this->AnswerEdit->Text = '';
 
            // redirects the browser to the ReadPost page
            $url=$this->Service->constructUrl('problems.ListProblems',array('chapter'=>$problemRecord->chapter_id));
            $this->Response->redirect($url);
        }
    }
 
    /**
     * Returns the post data to be editted.
     * @return PostRecord the post data to be editted.
     * @throws THttpException if the post data is not found.
     */
    protected function getProblem()
    {
        // the ID of the post to be editted is passed via GET parameter 'id'
        $problemID=(int)$this->Request['id'];
        // use Active Record to look for the specified post ID
        $problemRecord=ProblemRecord::finder()->findByPk($problemID);
        if($problemRecord===null)
            throw new THttpException(500,'Problem is not found.');
        return $problemRecord;
    }
}

?>
