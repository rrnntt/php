<div class="post-box">
<h3>
<com:THyperLink Text="<%# $this->Data->problem_id %>"
    NavigateUrl="<%# $this->Service->constructUrl('posts.ListPost') %>" />
</h3>
 
<p>
<com:TLiteral Text="<%# $this->getText() %>" />
</p>

<div>
<com:THyperLink Text="Edit" SkinID="MainMenu"
    NavigateUrl="<%= $this->Service->constructUrl('problems.EditProblem',array('id'=>$this->Data->problem_id)) %>"
    Visible="<%= $this->User->IsAdmin %>" />
</div>
	
</div>
