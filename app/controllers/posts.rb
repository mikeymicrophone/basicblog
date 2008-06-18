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
    @post = Post.new params[:post]
    if params[:invalid]
      @message = "this post is incomplete and NOT SUITABLE FOR THIS BLOOOOOOGGGGGGGGG."
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
      redirect url(:action => :new, :invalid => true)
    end
  end

  def update
    render
  end

  def destroy
    render
  end
  
end
