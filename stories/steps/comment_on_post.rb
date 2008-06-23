steps_for(:comment_on_post) do  
    Given("a post") do
      Post.create :title => 'qwantz', :body => 'a website for our time', :created_at => DateTime.civil(2008, 6, 19)
    end
    
    When("I follow the permalink") do
      @permalink = Post.first(:title => 'qwantz').permalink
      @controller = get(@permalink)
    end
    
    Then("I should see the post's page") do
      @controller.body.should contain('qwantz')
    end
    
    Given("I am at the post's page") do
      @controller = get("/posts/#{Post.first(:title => 'qwantz').id}")
    end

    When("I click the comment link from the post's page") do
      # run javascript function show_comment_form()
    end
    
    When("I click the comment link from the index") do
      @controller = get(url(:post, Post.first(:title => 'qwantz')) + '?comment=true')
    end
    
    Then("I should see a comment form") do
      @controller.body.should have_tag(:form, :class => 'comment')
      # will this work, since the form will be made visible asynchronously?
    end
    
    Given("some comments on the post") do
      Comment.create(:email => 'joseph@bigbigbank.com', :name => 'Big Joe', :body => 'T-Rex is so motivational!!', :post_id => Post.first(:title => 'qwantz').id)
      Comment.all.should_not be([])
    end
    
    When("I view the index page") do
      @controller = get('/posts/')
    end
    
    Then("I should see the number of comments on the post") do
      @comment_count = Post.first(:title => 'qwantz').comments.length
      @controller.body.should have_tag(:div, :class => 'comment_count') do
        contain(@comment_count)
      end
    end
    
    Then("I should see the datestamp of the most recent comment") do
      @recent_comment = Post.first(:title => 'qwantz').comments.select { |c| c.created_at }.sort_by { |c| c.created_at }.last
      @datestamp = @recent_comment.created_at.strftime("%m/%e")
      @controller.body.should contain(@datestamp)
    end

    Then("I should be taken to the post's page") do
      @controller.body.should contain('qwantz') #may not be specific enough
    end
    
    Then("I should see a comment form") do
      @controller.body.should have_tag(:form, :class => 'comment')
    end
    
    Then("the focus of the cursor should be in the comment form") do
      #don't know how to spec this yet
    end
    
    Given("I am viewing the post's page") do
      @post = Post.first(:title => 'qwantz')
      @controller = get("/posts/#{@post.id}")
    end
    
    Given("I have filled out the comment form") do
      # not yet implemented
    end
    
    When("I submit the comment form") do
      @post = Post.first(:title => 'qwantz')
      @controller = post('/comments/create', {:comment => {:email => 'joseph@bigbigbank.com', :name => 'Big Joe', :body => 'T-Rex is so motivational!!', :post_id => @post.id}})
    end
    
    Then("the comment should appear asynchronously") do
      #not sure how to spec this yet - attempt below
      @controller.body.should have_selector('T-Rex is so motivational!!')
    end
    
    Then("the comment field should be reset") do
      # can this be tested without javascript?
      @controller.body.should have_tag(:textarea, :id => 'comment_body')#.with_value('') (with_value doesn't exist)
    end
    
    When("I submit a comment with an email but no name or message") do
      @post = Post.first(:title => 'qwantz')
      @controller = post('/comments/create', {:comment => {:email => 'joseph@bigbigbank.com', :post_id => @post.id}})
    end
    
    Then("the comment should be saved") do
      # the action loads a div with the text of the comment
      # for a comment with no text, it won't have any identifiable tags?
      @controller.body.should have_tag(:div, :class => :comment)
    end
    
    When("I submit a comment with an email and a name and a message") do
      @post = Post.first(:title => 'qwantz')
      @controller = post('/comments/create', {:comment => {:email => 'joseph@bigbigbank.com', :name => 'Big Joe', :body => 'T-Rex is so motivational!!', :post_id => @post.id}})
    end
    
    When("I submit a comment with no email") do
      @post = Post.first(:title => 'qwantz')
      @controller = post('/comments/create', {:comment => {:name => 'Big Joe', :body => 'T-Rex is so motivational!!', :post => @post.id}})
    end
    
    Then("the comment should not be saved") do
      @controller.body.should have_tag(:div, :class => :error) do
        with_tag('<li>Email must not be blank </li>')
      end
    end
    
    When("I submit a comment with textile enabled") do
      @post = Post.first(:title => 'qwantz')
      @controller = post('/comments/create', {:comment => {:email => 'joseph@bigbigbank.com', :name => 'Big Joe', :body => 'T-Rex is *so* motivational!!', :textile => 1, :post_id => @post.id}})
    end
    
    Then("the comment should be displayed with textile markup") do
      @controller.body.should contain('<b>so</b>')
    end
    
    When("I submit a comment with textile disabled") do
      @post = Post.first(:title => 'qwantz')
      @controller = post('/comments/create', {:comment => {:email => 'joseph@bigbigbank.com', :name => 'Big Joe', :body => 'T-Rex is *so* motivational!!', :textile => '0', :post_id => @post.id}})
    end
    
    Then("the comment should be displayed without textile markup") do
      @controller.body.should contain('*so*')
    end
    
    When("I view the post's page") do
      @post = Post.first(:title => 'qwantz')
      @controller = get("/posts/#{@post.id}")
    end
    
    Then("I should see the number of comments it has") do
      @comment_count = Post.first(:title => 'qwantz').comments.length
      @controller.body.should have_tag(:div, :class => 'comment_count').with_content(@comment_count)
    end
    
    Then("the most recent comment should be first") do
      @recent_comment = Post.first(:title => 'qwantz').comments.sort_by { |c| c.created_at }.last
      @controller.body.should have_tag(:div, :id => 'comments') do
        #with_tag('<div class="comment">?</div>', @recent_comment.body)
      end
    end
end