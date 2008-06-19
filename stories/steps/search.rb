steps_for(:search) do
  
  Given("I search for a term found in a cw uri") do
    # just for documentation
  end
  When("the topic page loads") do
    @controller = get('/topics/2')
  end
  Then("I should find the information I wanted") do
    @controller.body.should have_tag(:div, :class => 'topic')
  end
  
  When("it doesn't have the information I wanted") do
    # just for documentation
  end
  Then("I should find other interesting information") do
    @controller.body.should have_tag(:div, :class => 'off_topic')
  end
  
  Given("I search for something") do
    # just for documentation
  end
  When("the unlogged-in homepage loads") do
    @controller = get('/')
  end
  Then("I should find what I wanted") do
    # just for documentation
  end
  Then("I should find ways to register") do
    @controller.body.should have_tag(:a, :id => 'register')
  end

  When("I don't find what I wanted") do
    # just for documentation
  end
  
  Given("I have a cookie from cw") do
    cookies.length.should_not be(0)
  end
  Given("the cookie is valid") do
    cookies.first.expires.should be_gt(Time.now) 
  end
  Given("the cookie claims that I am still logged in") do
    cookies.first.logged_in.should be_true
  end
  Then("it should include my personalized information") do
    @controller.body.should have_tag(:a, :class => 'profile_link')
  end
  Then("it should include the information I wanted") do
    # documentation only?
  end
  Then("I should find ways to navigate the rest of the site") do
    @controller.body.should have_tag(:a, :class => 'navigation')
  end
end