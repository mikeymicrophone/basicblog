#!/opt/local/bin/ruby
require 'webrick'
include WEBrick

s = HTTPServer.new(
  :Port            => ARGV[0] || 2000,
  :DocumentRoot    => Dir::pwd
)

## mount subdirectories
s.mount("",
        HTTPServlet::FileHandler, ".",
        true)  #<= allow to show directory index.

trap("INT"){ s.shutdown }
s.start