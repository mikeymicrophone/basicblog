require File.join(File.dirname(__FILE__), "../..", 'spec_helper.rb')

describe "posts/index" do 
 
  before( :each ) do 
    @controller = Posts.new(fake_request) 
    @controller.instance_variable_set(:@posts, [Post.new(:body => "magic", :title => "wand")])  
    @body = @controller.render(:index) 
  end 
 
  it "should have a div with id posts and one with class post" do 
    @body.should have_tag(:div, :id => :posts).with_tag(:div, :class => :post) 
  end
  
end