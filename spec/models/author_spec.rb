require File.join( File.dirname(__FILE__), "..", "spec_helper" )

describe Author do
  
  before do
    @author = Author.create :name => 'Jim'
  end

  it "should have a name" do
    @author.name.should_not be_nil
  end
  
  it "should have an id" do
    @author.id.should_not be_nil
  end

end