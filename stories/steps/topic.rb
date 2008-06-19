steps_for(:topic) do
  Given("a user named $name")
  Given("$name is logged in")
  Given("$name is viewing a topic page")
  When("$name clicks an edit link")
  Then("he should be presented with a form for editing the topic")
  
  Given("$name is not logged in")
  Then("he should be presented with a form for logging in and editing the topic")
  
  Given("no user named $name")
  Then("he should be presented with a form for signing up and editing the topic")
  
  When("$name clicks a comment link")
  Then("she should be presented with a form for her comment")

  Then("he should be presented with a form for his comment and login credentials")
  
  Then("he should be presented with a form for his comment and signup information")
  Then("he should be directed to the topic registration flow")
  
end