require File.join( File.dirname(__FILE__), "..", "spec_helper" )

describe Post do
  
  before do
    @author = Author.create :name => 'Jim'
    @post = Post.create :body => 'riveted?', :title => 'just wait', :author_id => @author.id
  end

  it "should have a body" do
    @post.body.should_not be_nil
  end
  
  it "should have a title" do
    @post.title.should_not be_nil
  end
  
  it "should have a timestamp" do
    @post.created_at.should_not be_nil
  end

end

def post_in_month(mon)
  Post.create :body => 'wonderful news', :title => 'catchy tagline', :author_id => 1, :created_at => DateTime.civil(2008, mon, 15)
end

describe "Post month sorting" do
  before do # this will put 1 post in January, 2 in February, etc
    [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].each do |amount|
      amount.times { post_in_month(amount) }
    end
  end
  
  it "should be able to scope the posts to those from a single month" do
    (1..12).each do |month|
      Post.in_month(month).map { |post| post.created_at.month }.uniq.length.should be 1
    end
  end
end