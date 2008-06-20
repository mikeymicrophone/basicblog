class Posts < Application
  
  def index
    @posts = Post.all
    render
  end

  def show
    if params[:permalink]
      @post = Post.first(:title.like => "%#{params[:title]}%",
        :created_at.gt => DateTime.civil(params[:year].to_i, params[:month].to_i, params[:day].to_i - 1),
        :created_at.lt => DateTime.civil(params[:year].to_i, params[:month].to_i, params[:day].to_i + 1))
    else
      @post = Post.get params[:id]
    end
    @comment = Comment.new
    render
  end
  
  def month_of
    @posts = Post.in_month params[:month].to_i
    render :index
  end

  def new
    @post = Post.new params[:post]
    @message = "this post is incomplete and NOT SUITABLE FOR THIS BLOOOOOOGGGGGGGGG." if params[:invalid]
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
      redirect url(:action => :new, :invalid => true, :post => params[:post])
    end
  end

  def update
    render
  end

  def destroy
    render
  end
  
end
