pushd ~/packer.perl
perl jsPacker.pl -e62 -i /usr/local/apache2/htdocs/plugins/debug/jquery.debug.js -o /tmp/jqd
echo -n "//">/tmp/jqd.js
grep 'version' /usr/local/apache2/htdocs/plugins/debug/jquery.debug.js >> /tmp/jqd.js
cat /tmp/jqd >> /tmp/jqd.js
cp /tmp/jqd.js /usr/local/apache2/htdocs/plugins/debug/jquery.debug-pack.js
ls -la /usr/local/apache2/htdocs/plugins/debug/
popd
