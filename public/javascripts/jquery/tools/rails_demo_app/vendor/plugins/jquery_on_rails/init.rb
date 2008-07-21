# Include hook code here
gem 'hpricot', '0.5.142'
require 'hpricot'
require 'jquery_on_rails'
require 'jquery_helpers'
require 'jqor_control'
JQueryOnRails::JQUERY_LIBRARY = ["jquery", "interface", "form", "dimensions", "metadata"]
JQueryOnRails::JQUERY_LIBRARY_EXTRAS = ["jquery.tablesorter.js", "jquery.tabs.js"]
ActionView::Base.send :include, JQueryHelper