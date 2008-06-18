require File.join(File.dirname(__FILE__), "../..", 'spec_helper.rb')

describe "posts/new" do
  before do
    @controller = Posts.new(fake_request)
    @controller.instance_variable_set(:@post, Post.new)
    @body = @controller.render(:new)
  end
  
  it "should render a form for a post" do
    @body.should have_tag(:form).with_tag(:textarea).with_tag(:input)
  end
end