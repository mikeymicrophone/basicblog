module JQueryOnRails

  def self.available_js
    Hash[*(Dir["#{ RAILS_ROOT }/public/javascripts/jquery_modules/*.js"] - JQUERY_LIBRARY).map {|x| 
      [x.split("/")[-1], File.open(x).read =~ /\$\(\"[^\"]*\"\)/ ? true : false] }.flatten].select{|x,y| y}.map{|x,y| x}
  end

  def self.selectors_per_js
    available_js.map {|x| 
      [x, File.read("#{ RAILS_ROOT }/public/javascripts/jquery_modules/#{ x }").scan(/\$\(\"([^\"]*)\"\)/).to_a.flatten] }.inject({}) {|s,x| 
        s.merge(x[0] => x[1]) }
  end
  
  module Routes
  
    def jquery
      @set.add_route("/j_query/app_jquery", :controller => "j_query", :action => "app_jquery")
    end
  
  end

end

ActionController::Routing::RouteSet::Mapper.send :include, JQueryOnRails::Routes 