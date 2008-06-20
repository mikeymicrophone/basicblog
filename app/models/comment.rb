class Comment
  include DataMapper::Resource
  
  belongs_to :post

  property :id, Integer, :serial => true
  property :post_id, Integer
  property :email, String
  property :name, String
  property :body, Text
  property :textile, Boolean
  property :created_at, DateTime
  
  validates_present :email
end
