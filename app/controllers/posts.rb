class Posts < Application
  
  def index
    @posts = Post.all
    render
  end

  def show
    @post = Post.get params[:id]
    render
  end
  
  def month_of
    @posts = Post.in_month params[:month]
    render :index
  end

  def new
    @post = Post.new
    render
  end

  def edit
    @post = Post.get params[:id]
    render
  end

  def delete
    render
  end

  def create
    @post = Post.create :body => params[:body], :title => params[:title], :author_id => 1
    render :show
  end

  def update
    render
  end

  def destroy
    render
  end
  
end
