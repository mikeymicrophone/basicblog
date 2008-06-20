module Merb
  module GlobalHelpers
    # helpers defined here available to all views.
    def textilized(post)
      RedCloth.new(post.body).to_html unless post.body.nil?
    end  
  end
end
