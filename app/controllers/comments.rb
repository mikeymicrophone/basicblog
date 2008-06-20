class Comments < Application
  # provides :xml, :yaml, :js

  def index
    @comments = Comment.all
    display @comments
  end

  def show
    @comment = Comment.get(params[:id])
    raise NotFound unless @comment
    display @comment
  end

  def new
    only_provides :html
    @comment = Comment.new
    render
  end

  def edit
    only_provides :html
    @comment = Comment.get(params[:id])
    raise NotFound unless @comment
    render
  end

  def create
    @comment = Comment.new(params[:comment])
    Merb.logger.info "creating comment from #{request.xhr? ? 'xhr' : 'post'}"
    if @comment.save
      partial :comment, :with => @comment#redirect url(:comment, @comment)
    else
      render :new
    end
  end

  def update
    @comment = Comment.get(params[:id])
    raise NotFound unless @comment
    if @comment.update_attributes(params[:comment]) || !@comment.dirty?
      redirect url(:comment, @comment)
    else
      raise BadRequest
    end
  end

  def destroy
    @comment = Comment.get(params[:id])
    raise NotFound unless @comment
    if @comment.destroy
      redirect url(:comment)
    else
      raise BadRequest
    end
  end

end
