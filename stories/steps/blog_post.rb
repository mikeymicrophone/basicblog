steps_for(:blog_post) do
  When("I create a blog post") do
    @controller = post('/posts/create', :post => {:body => 'insight', :title => 'hook'})
  end
  Then("the page should show the post's title") do
    @controller.body.should have_tag(:div, :class => 'title')
  end
  Then("the page should show the post's body") do
    @controller.body.should have_tag(:div, :class => 'content')
  end
  
  When("I create an untitled post") do
    @controller = post('/posts/create', :post => {:body => 'insect'})
  end
  
  Then("I should see the new post page") do
    @controller.body.should have_tag(:form)
  end
  
  Then("my post should not be saved") do
    Post.all.select { |p| p.body == 'insect' }.should == []
  end
  
  Then("my post should be in the body box") do
    @controller.body.should contain('insect')
  end
  
  Then("the helpful text should be displayed") do
    @controller.body.should contain("BLOOOOOOGGGGGGGGG")
  end
end