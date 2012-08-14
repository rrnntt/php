<div class="post-box">
<h3>
<com:THyperLink Text="<%# $this->Data->problem_id %>"
    NavigateUrl="<%# $this->Service->constructUrl('posts.ListPost') %>" />
</h3>
 
<p>
<com:TLiteral Text="<%# $this->Data->content %>" />
</p>
</div>
