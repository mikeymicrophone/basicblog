steps_for(:browse) do
  
  Given("that the user is at a page they are not interested in") do
    @controller = get('/topic/2')
  end
  When("the user is thinking about whether to leave the site") do
    # just for documentation
  end
  Then("the user should see other items that are of interest") do
    @controller.body.should have_tag(:a, :class => 'topic')
  end
  Then("they should lead elsewhere within cw")
  Then("they should include a mini profile")
  Then("they should include geographically proximate topics")
  Then("they should include recommended topics")
  Then("they should include correlated topics")
end