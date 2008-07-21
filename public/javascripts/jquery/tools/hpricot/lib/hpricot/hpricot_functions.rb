module Hpricot
  class Elements
  
    def attr key, value = nil
      if value
        each do |el|
          el.raw_attributes[key.to_s] = value.to_s
        end
        return self      
      end    
      if key.is_a?(Hash)
        key.each { |k,v| self.attr(k,v) }
        return self
      else
        return self[0].attributes[key.to_s]
      end
    end
  
    def add_class class_name
      each do |el|
        next unless el.raw_attributes
        classes = el.raw_attributes["class"].to_s.split(" ")
        el.raw_attributes["class"] = classes.push(class_name).uniq.join(" ")
      end
      self
    end
  
  end
  
  class Doc
    
    def [] selector
      self/selector
    end
    
  end
  
end