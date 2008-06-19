steps_for(:comment_on_post) do
  @title = 'qwantz'
  @body = 'a website for our time'
  @date = DateTime.civil(2008, 6, 19)
  @email = 'joseph@bigbigbank.com'
  @name = 'Big Joe'
  @comment_body = 'T-Rex is so motivational!!'
  @comment_body_textile = 'T-Rex is *so* motivational!!'
  
    Given("a post") do
      Post.create :title => @title, :body => @body, :created_at => @date
    end
    When("I follow the permalink") do
      @controller = get('/posts/2008/6/19/qwantz')
    end
    Then("I should see the post's page") do
      @controller.body.should contain('qwantz')
    end
    
    When("I click the comment link") do
      clicks_on 'comment'
    end
    Then("I should see a comment form") do
      @controller.body.should have_tag(:form, :class => 'comment')
      # will this work, since the form will be inserted asynchronously?
    end
    
    Given("some comments on the post") do
      Comment.create(:email => @email, :name => @name, :body => @comment_body)
      Comment.all.should_not be([])
    end
    When("I view the index page") do
      @controller = get('/posts/')
    end
    Then("I should see the number of comments on the post") do
      @comment_count = Post.first(:conditions => {:title => @title}).comments.length
      @controller.body.should have_tag(:div, :class => 'comment_count').with_content(@comment_count)      
    end
    
    Then("I should see the datestamp of the most recent comment") do
      @recent_comment = Post.first(:conditions => {:title => @title}).comments.sort { |c| c.created_at }.last
      @datestamp = @recent_comment.created_at.strftime("%m/%d")
      @controller.body.should contain(@datestamp)
    end
    
    # When("I click the comment button") do
    #   clicks_button "comment"
    # end
    Then("I should be taken to the post's page") do
      @controller.body.should contain(@title) #may not be specific enough
    end
    Then("I should see a comment form") do
      @controller.body.should have_tag(:form, :class => 'comment')
    end
    Then("the focus of the cursor should be in the comment form") do
      #don't know how to spec this yet
    end
    Given("I am viewing the post's page") do
      @controller = get('/posts/1')
    end
    When("I submit the comment form") do
      fills_in 'email', :with => @email
      fills_in 'name', :with => @name
      fills_in 'comment_body', :with => @comment_body
      clicks_button 'comment'
    end
    Then("the comment should appear asynchronously") do
      #not sure how to spec this yet - attempt below
      @controller.body.should contain @comment_body
    end
    Then("the comment field should be reset") do
      @controller.body.should have_tag(:textarea, :id => 'comment_body').with_value('') #don't think this with_value method exists
    end
    
    When("I submit a comment with an email but no name or message") do
      fills_in 'email', :with => @email
      clicks_button 'comment'
    end
    Then("the comment should be saved") do
      @comment.should be_valid
    end
    
    When("I submit a comment with an email and a name and a message") do
      fills_in 'email', :with => @email
      fills_in 'name', :with => @name
      fills_in 'comment_body', :with => @comment_body
      clicks_button 'comment'
    end
    
    When("I submit a comment with no email") do
      fills_in 'name', :with => @name
      fills_in 'comment_body', :with => @comment_body
      clicks_button 'comment'
    end
    Then("the comment should not be saved") do
      @comment.should_not be_valid
    end
    
    When("I submit a comment with textile enabled") do
      fills_in 'email', :with => @email
      fills_in 'name', :with => @name
      fills_in 'comment_body', :with => @comment_body_textile
      checks_box 'textile'
      clicks_button 'comment'
    end
    Then("the comment should be displayed with textile markup") do
      @controller.body.should contain('<b>so</b>')
    end
    
    When("I submit a comment with textile disabled") do
      fills_in 'email', :with => @email
      fills_in 'name', :with => @name
      fills_in 'comment_body', :with => @comment_body_textile
      clicks_button 'comment'
    end
    Then("the comment should be displayed without textile markup") do
      @controller.body.should contain('*so*')
    end
    
    When("I view the post's page") do
      @controller = get('/posts/1')
    end
    Then("I should see the number of comments it has") do
      @comment_count = Post.first(:conditions => {:title => @title}).comments.length
      @controller.body.should have_tag(:div, :class => 'comment_count').with_content(@comment_count)
    end
    
    Then("the most recent comment should be first") do
      @recent_comment = Post.first(:conditions => {:title => @title}).comments.sort { |c| c.created_at }.last
      @controller.body.should have_tag(:div, :id => 'comments').with_tag(:div, :class => 'comment').with_content(@recent_comment.body)
    end
end