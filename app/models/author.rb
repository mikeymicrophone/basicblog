class Author
  include ::DataMapper::Resource

    property :id, Integer, :serial => true
    property :name, String
    property :created_at, DateTime
  
end
