require File.join(File.dirname(__FILE__), "..", 'spec_helper.rb')

describe Posts, "index action" do
  before(:each) do
    @posts = [mock(:post), mock(:post)]
  end
  
  it "should have posts" do
    Post.should_receive(:all).and_return(@posts)
    dispatch_to(Posts, :index) do |controller|
      controller.stub!(:render)
    end
  end
end

describe Posts, "new action" do
  before(:each) do
    @post = mock(:post)
  end
  
  it "should load the new post page" do
    Post.should_receive(:new).and_return(@post)
    dispatch_to(Posts, :new) do |controller|
      controller.stub! :render
    end
  end
end

describe Posts, "create action with valid params" do
  before do
    @post = mock(:post, :body => 'deer tick', :title => 'fantasy')
  end
  
  it "should save the data if both fields are present" do
    Post.should_receive(:create).and_return(@post)
    @post.should_receive(:valid?).and_return(true)
    dispatch_to(Posts, :create, :post => {:body => 'deer tick', :title => 'fantasy'}) do |controller|
      controller.stub! :redirect
      controller.stub! :render
      # can we set an expectation that render will be called with :show, since we have already stubbed it?
    end
  end
  
end

describe Posts, "create action with no title" do
  it "should redirect to new if a title is absent" do
    Posts.should_receive(:new)
    
    dispatch_to(Posts, :create, :post => {:body => 'handsome'}) do |controller|
      controller.stub! :redirect
      controller.stub! :render
    end
  end  
end

describe Posts, "create action with no body" do
  it "should redirect to new if a body is absent" do
    Posts.should_receive(:new)
    
    dispatch_to(Posts, :create, :post => {:title => 'answazi'}) do |controller|
      controller.stub! :render
    end
  end
  
end

describe Posts, "show action" do
  before do
    @post = mock(:post, :title => 'bikini', :body => 'for ladies')
  end
  
  it "should load the post" do
    Post.should_receive(:get).with('1').and_return(@post)
    
    dispatch_to(Posts, :show, :id => 1) do |controller|
      controller.stub! :render
    end
  end
end

describe Posts, "month_of action" do
  before do
    @june = DateTime.civil(2008, 6, 5)
    @january = DateTime.civil(2008, 1, 5)
    @posts = [mock(:post, :created_at => @june), mock(:post, :created_at => @january)]
  end
  
  it "should only pick january posts when january is the selected month" do
    Post.should_receive(:in_month).with(1).and_return([@posts[1]])
    dispatch_to(Posts, :month_of, :month => 1) do |controller|
      controller.stub! :render
    end
  end
end