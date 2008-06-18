class Post
  include DataMapper::Resource
  include DataMapper::Timestamp
  include DataMapper::Validate
  
  property :title,      String#, :nullable => false
  property :body,       Text#, :nullable => false
  property :id,         Integer, :serial => true
  property :author_id,  Integer
  property :created_at,  DateTime
  
    validates_present :title
    validates_present :body

  def self.in_month(month)
    case month
    when 1
      all(:created_at.gt => DateTime.civil(2007, 12, 31), :created_at.lt => DateTime.civil(2008, 2))
    when 2..11
      all(:created_at.gt => DateTime.civil(2008, month - 1, previous_month_end[month]), :created_at.lt => DateTime.civil(2008, month, previous_month_end[month + 1]))
    when 12
      all(:created_at.gt => DateTime.civil(2008, 11, 30), :created_at.lt => DateTime.civil(2009, 1))
    else
      []
    end
  end

  def self.previous_month_end
    {2 => 31, 3 => 28, 4 => 31, 5 => 30, 6 => 31, 7 => 30, 8 => 31, 9 => 31, 10 => 30, 11 => 31, 12 => 30}
  end
end