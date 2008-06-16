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
    @posts = Post.in_month params[:month].to_i
    render :index
  end

  def new
    if params[:post]
      @message = "this post is incomplete and NOT SUITABLE FOR THIS BLOOOOOOGGGGGGGGG."
    else
      @post = Post.new
    end
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
    @post = Post.create :body => params[:post][:body], :title => params[:post][:title], :author_id => 1
    if @post.valid?
      render :show
    else
      redirect :action => :new
    end
  end

  def update
    render
  end

  def destroy
    render
  end
  
end
