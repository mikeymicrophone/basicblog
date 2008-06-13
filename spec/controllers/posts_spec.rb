require File.join(File.dirname(__FILE__), "..", 'spec_helper.rb')

describe Posts, "index action" do
  before(:each) do
    dispatch_to(Posts, :index)
    @author = Author.create :name => 'Jim'
    Post.create :title => 'bicycle', :body => 'trek', :author_id => @author.id
    Post.create :title => 'motorcycle', :body => 'honda', :author_id => @author.id 
    @posts = Post.all
  end
  
  it "should have posts" do
    @posts.should_not be_empty
  end
end

describe Posts, "new action" do
  before(:each) do
    dispatch_to(Posts, :new)
    @author = Author.create :name => 'Veronica'
    @post = Post.first || Post.create(:title => 'bikini', :body => 'for ladies', :author_id => @author_id)
  end
  
  it "should load the new post page" do
    @post.should_not be_nil
  end
end

describe Posts, "create action" do
  before do
    dispatch_to(Posts, :create, :body => 'deer tick', :title => 'fantasy', :author_id => 1)
  end
  
  it "should save the data" do
    Post.first(:body.eql => 'deer tick').should_not be_nil
  end
end

describe Posts, "show action" do
  before do
    @author = Author.create :name => 'Veronica'
    @post = Post.create(:title => 'bikini', :body => 'for ladies', :author_id => @author_id)
    dispatch_to(Posts, :show, :id => 1)
  end
  
  it "should load the post" do
    @post.should_not be_nil
  end
end

describe Posts, "month_of action" do
  before do
    @post_june = Post.create(:body => 'a', :title => 'b', :author_id => 1, :created_at => DateTime.civil(2008, 6, 5))
    @post_january = Post.create(:body => 'c', :title => 'd', :author_id => 1, :created_at => DateTime.civil(2008, 1, 5))
  end
  
  it "should only pick january posts when january is the selected month" do
    # this one might not actually be testing any code that I'm using - it doesn't test the controller or the model, just the general methodology
    @jan_posts = Post.all(:created_at.gt => DateTime.civil(2007, 12), :created_at.lt => DateTime.civil(2008, 2))
    @jan_posts.each { |p| p.created_at.month.should be 1 }
  end
  
  it "should populate the @posts variable" do
    dispatch_to(Posts, :month_of, :month => 1)
    @posts.should_not be_nil
  end
end