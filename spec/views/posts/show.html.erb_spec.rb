require File.join(File.dirname(__FILE__), "../..", 'spec_helper.rb')

describe "posts/show" do
  before do
    @controller = Posts.new(fake_request)
    @controller.instance_variable_set(:@post, Post.create(:body => 'my body', :title => 'fantastic'))
    @controller.instance_variable_set(:@comment, Comment.new)
    @body = @controller.render(:show)
  end
  
  it "should show the data from the post" do
    @body.should have_tag(:div, :class => 'post') do 
      with_tag(:div, :class => 'title')
      with_tag(:div, :class => 'content')
    end
  end
  
  
end