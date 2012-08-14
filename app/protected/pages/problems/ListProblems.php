<?php

class ListProblems extends TPage
{
    /**
     * Initializes the repeater.
     * This method is invoked by the framework when initializing the page
     * @param mixed event parameter
     */
    public function onInit($param)
    {
        parent::onInit($param);
        if(!$this->IsPostBack)  // if the page is requested the first time
        {
		    $chapterID=(int)$this->Request['chapter'];
            // Construts a query criteria
			$criteria=new TActiveRecordCriteria;
			$criteria->Condition = 'chapter_id = :chap';
		    $criteria->Parameters[':chap']=$chapterID;
            // get the total number of posts available
            $arr = ProblemRecord::finder()->count($criteria);
            $this->Repeater->VirtualItemCount=$arr;
            // populates post data into the repeater
            $this->populateData();
        }
    }

    /**
     * Event handler to the OnPageIndexChanged event of the pager.
     * This method is invoked when the user clicks on a page button
     * and thus changes the page of posts to display.
     */
    public function pageChanged($sender,$param)
    {
        // change the current page index to the new one
        $this->Repeater->CurrentPageIndex=$param->NewPageIndex;
        // re-populate data into the repeater
        $this->populateData();
    }
 
    /**
     * Determines which page of posts to be displayed and
     * populates the repeater with the fetched data.
     */
    protected function populateData()
    {
        $offset=$this->Repeater->CurrentPageIndex*$this->Repeater->PageSize;
        $limit=$this->Repeater->PageSize;
        if($offset+$limit > $this->Repeater->VirtualItemCount)
            $limit=$this->Repeater->VirtualItemCount-$offset;
        $this->Repeater->DataSource=$this->getProblems($offset,$limit);
        $this->Repeater->dataBind();
    }
 
    /**
     * Fetches problems from database with offset and limit.
     */
    protected function getProblems($offset, $limit)
    {
	    $chapterID=(int)$this->Request['chapter'];
        // Construct a query criteria
		$criteria=new TActiveRecordCriteria;
		$criteria->Condition = 'chapter_id = :chap';
		$criteria->Parameters[':chap']=$chapterID;
        $criteria->OrdersBy['problem_id']='asc';
        $criteria->Limit=$limit;
        $criteria->Offset=$offset;
        // query for the posts with the above criteria and with author information
        return ProblemRecord::finder()->findAll($criteria);
    }
}

?>
