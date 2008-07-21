##
# Do some checks.
puts

$errors = 0

require 'fileutils'

puts "** Copying jQuery files over to public/javascripts"

js_source = File.join(File.dirname(__FILE__), 'js')
css_source = File.join(File.dirname(__FILE__), 'css')
js_dest = File.join(File.dirname(__FILE__), '..', '..', '..', 'public', 'javascripts')
css_dest = File.join(File.dirname(__FILE__), '..', '..', '..', 'public', 'stylesheets')

js_list = %w{ dimensions form interface jquery jquery.tablesorter jquery.tabs metadata }.map {|x| File.join(js_source, x + ".js") }
js_modules = %w{ sortable_tables tabs }.map {|x| File.join(js_source, 'jquery_modules', x + ".js") }
css_list = %w{ jquery.tabs }.map {|x| File.join(css_source, x + ".css") }

begin
  FileUtils.cp(js_list, js_dest)
  FileUtils.cp(css_list, css_dest)
  FileUtils.mkdir_p(File.join(js_dest, 'jquery_modules'))
  FileUtils.cp(js_modules, File.join(js_dest, 'jquery_modules'))
rescue Exception
  puts "** The copy failed. Please try by hand"
end

puts "** Now would be a good time to check out the README.  Enjoy your day."
puts
