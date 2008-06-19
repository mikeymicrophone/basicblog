require File.join(File.dirname(__FILE__), "..", 'spec_helper.rb')

describe Merb::PostsHelper do

  before do
    @post = Post.create :title => 'text with formatting', :body => "h1. stasis"
  end

  it "should be able to textilize content" do
    textilized(@post).should have_tag(:h1)
  end
  

end