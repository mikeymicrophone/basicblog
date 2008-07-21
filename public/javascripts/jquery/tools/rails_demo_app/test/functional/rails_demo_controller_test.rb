require File.dirname(__FILE__) + '/../test_helper'
require 'rails_demo_controller'

# Re-raise errors caught by the controller.
class RailsDemoController; def rescue_action(e) raise e end; end

class RailsDemoControllerTest < Test::Unit::TestCase
  def setup
    @controller = RailsDemoController.new
    @request    = ActionController::TestRequest.new
    @response   = ActionController::TestResponse.new
  end

  # Replace this with your real tests.
  def test_truth
    assert true
  end
end
