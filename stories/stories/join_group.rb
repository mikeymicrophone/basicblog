require File.join(File.dirname(__FILE__), "../helper")

with_steps_for :join_group do
  run File.expand_path(__FILE__).gsub(".rb",""), :type => Merb::Test::RspecStory
end