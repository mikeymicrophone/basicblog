require 'digest/md5'

module JQueryHelper

  def include_jquery
    javascript_include_tag(*(JQueryOnRails::JQUERY_LIBRARY + JQueryOnRails::JQUERY_LIBRARY_EXTRAS)) +
      "<script src='/j_query/app_jquery?#{ Digest::MD5::hexdigest(@content_for_layout + controller.class.default_layout) }'></script>"
  end
  
  def sortable_table options = {}, &block
    html_options = options.delete(:html) || {}
    html = content_tag("table", capture(&block), 
      {:class => "sortable_table " + options.to_camelized_json}.merge(html_options))
    concat(html, block.binding)
  end  
    
  def tab_container id, fragments, options = {}, &block
    html_options = options.delete(:html) || {}
    html = Builder::XmlMarkup.new
    html.div ({:id => id, :class => ("tab_container " + options.to_camelized_json)}.merge(html_options)) do
      html.ul do
        fragments.each do |k,v|
          html.li do
            html.a :href => "##{k}" do
              html.span v
            end
          end
        end
      end
      html << capture(&block)
    end
    concat(html.target!, block.binding)
  end
  
  def tab_contents id, &block
    div = content_tag("div", capture(&block), :id => id)
    concat(div, block.binding)
  end
  
end

class Hash
  
  def to_camelized_json
    "{" + map {|k,v| "#{k.to_s.camelize(:lower)}:#{v.respond_to?(:to_camelized_json) ? v.to_camelized_json : v.to_json}" }.join(",") + "}"
  end
  
end