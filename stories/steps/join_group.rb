steps_for(:join_group) do
  Given("a user named $name") do
    @member = User.create :name => name
  end
  Given("no user named $name") do
    (@nonmember = User.get(:name => name)).should be_nil
  end
  Given("a group called $group_name") do
    @group = Group.create :name => group_name
  end
  Given("$name is a member of $group_name") do
    Membership.create :user_id => User.get(:name => name).id, :group_id => Group.get(:name => group_name).id
  end
  Given("$name is a potential member of $group_name") do
    @group.accepts_membership_credentials_of(@nonmember).should be_true
  end
  When("$sender_name sends $invitee_name an invite") do
    User.get(:name => sender_name).invites(User.create(:name => invitee_name))
  end
  When("$name receives an email") do
    Invite.get(:user_id => User.get(:name => name)).send_invitation
  end
  When("$name follows the link in the email") do
    @controller = get("/invitation/#{Invite.get(:user_id => User.get(:name => name)).code}")
  end
  Then("$name should be taken to a registration page") do
    @controller.body.should have_tag(:div, :class => 'registration_form')
  end
  Then("$name should be identified as a potential member of $group_name") do
    Group.get(:name => group_name).accepts_membership_credentials_of(User.get(:name => name)).should be_true
  end

  When("$name loads the registration page") do
    @controller = get('/register/step2')
  end
  Then("he should be offered membership in groups") do
    @controller.body.should have_tag(:div, :class => 'groups available')
  end
  Then("he should be offered listserv options for the groups") do
    @controller.body.should have_tag(:checkbox, :class => 'group list')
  end

  When("$name asks a question") do
    @question = User.get(:name => name).asks_question('why is the sky blue?')
  end
  When("selects $group_name to answer her question") do
    @question.assign_to_group(Group.get(:name => group_name))
  end
  Then("an email should be sent to $group_name") do
    @question.email_targets.should contain(Group.get(:name => group_name))
  end
  Then("the email should contain the question") do
    @question.body.should contain('sky')
  end
  
  Given("$name is not a member of $group_name") do
    Group.get(:name => group_name).members.should_not contain(User.get(:name => name))
  end
  Then("no email should be sent to $group_name") do
    @question.email_targets.should_not contain(Group.get(:name => group_name))
  end
  Then("$name should be directed to the group registration page for $group_name") do
    @controller.body.should have_tag(:div, :class => 'group registration', :id => group_name + '_registration')
  end
end