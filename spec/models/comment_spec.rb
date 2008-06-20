require File.join( File.dirname(__FILE__), "..", "spec_helper" )

describe Comment do

  it "should not be valid without an email" do
    Comment.new(:body => 'sin').should_not be_valid
  end
  
  it "should be valid with only an email" do
    Comment.new(:email => 'b').should be_valid
  end

end