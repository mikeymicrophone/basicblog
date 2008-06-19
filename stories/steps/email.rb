steps_for(:email) do
  
  Given("a user named Mississippi") do
    @mississippi = User.create :name => 'Mississippi'
  end
  Given("a user named Bobcatt") do
    @bobcatt = User.create :name => 'Bobcatt'
  end
  Given("a group called Freewheelers") do
    @freewheelers = Group.create :name => 'Freewheelers'
  end
  Given("Mississippi is a member of Freewheelers") do
    @mississippi.joins_group @freewheelers
  end
  Given("Bobcatt is a member of Freewheelers") do
    @bobcatt.joins_group @freewheelers
  end
  When("Bobcatt asks a question of the Freewheelers group") do
    @bobcatt.asks @freewheelers, 'where is the library at?'
  end
  Then("Mississippi should receive an email") do
    @library_location = (Email.get(:sender_id => @bobcatt.id, :group => @freewheelers.id)).should_not be_nil
  end
  Then("the email should contain Bobcatt's question") do
    @library_location.body.should contain("library")
  end
end