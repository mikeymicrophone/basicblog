steps_for(:join_group) do
  Given("a user named $name") do
    @member = User.create :name => name
  end
  Given("no user named $name") do
    User.first(:name => name).should be_nil
  end
  Given("a group called $group_name") do
    @group = Group.create :name => group_name
  end
  Given("$name is a member of $group_name") do
    Membership.create :user_id => User.first(:name => name).id, :group_id => Group.first(:name => group_name).id
  end
  Given("$name is a potential member of $group_name") do
    @nonmember = User.create :name => name
    @group.accepts_membership_credentials_of(@nonmember).should be_true
  end
  When("$sender_name sends $invitee_name an invite") do
    User.first(:name => sender_name).invites(User.create(:name => invitee_name))
  end
  When("$name receives an email") do
    @invitee = User.first :name => name
    Invite.first(:user_id => @invitee.id).send_invitation
  end
  When("$name follows the link in the email") do
    @invitee = User.first :name => name
    @invite = Invite.first :user_id => @invitee.id
    @controller = get("/invitation/#{@invite.code}")
  end
  Then("$name should be taken to a registration page") do
    @controller.body.should have_tag(:div, :class => 'registration_form')
  end
  Then("$name should be identified as a potential member of $group_name") do
    @potential_member = User.first :name => name
    Group.first(:name => group_name).accepts_membership_credentials_of(@potential_member).should be_true
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
    @question = User.first(:name => name).asks_question('why is the sky blue?')
  end
  When("selects $group_name to answer her question") do
    @question.assign_to_group(Group.first(:name => group_name))
  end
  Then("an email should be sent to $group_name") do
    @question.email_targets.should contain(Group.first(:name => group_name))
  end
  Then("the email should contain the question") do
    @question.body.should contain('sky')
  end
  
  Given("$name is not a member of $group_name") do
    Group.first(:name => group_name).members.should_not contain(User.first(:name => name))
  end
  Then("no email should be sent to $group_name") do
    @question.email_targets.should_not contain(Group.first(:name => group_name))
  end
  Then("$name should be directed to the group registration page for $group_name") do
    @controller.body.should have_tag(:div, :class => 'group registration', :id => group_name + '_registration')
  end
end