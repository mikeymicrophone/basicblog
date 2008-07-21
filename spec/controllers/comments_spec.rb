require File.join(File.dirname(__FILE__), "..", 'spec_helper.rb')

describe Comments, "index action" do
  before(:each) do
    dispatch_to(Comments, :index)
  end
end

describe Comments, "create action with valid params" do
  before do
    @comment = mock(:comment, :email => 'j')
    @full_comment = mock(:comment, :email => 'j', :name => 'k', :post_id => 1, :body => 'love')
  end
  
  it "should save a comment with just email" do
    Comment.should_receive(:create).and_return(@comment)
    @comment.should_receive(:valid?).and_return(true)
    dispatch_to(Comments, :create, :comment => {:email => 'j'}) do |controller|
      controller.stub! :render
    end
  end
  
  it "should save a comment that is fully filled out" do
    Comment.should_receive(:create).and_return(@full_comment)
    @full_comment.should_receive(:valid?).and_return(true)
    dispatch_to(Comments, :create, :comment => {:email => 'j', :name => 'k', :post_id => 1, :body => 'love'}) do |controller|
      controller.stub! :partial
    end
  end
end