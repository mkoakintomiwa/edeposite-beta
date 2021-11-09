var fs = require("fs");
try{
    var portal_properties_dir = exports.portal_properties_dir = process.env.portal+"/portals/demo/specs/assets/portal-properties";
    var portal_properties = exports.portal_properties = require(portal_properties_dir+"/index");
    var log_dir = exports.log_dir = `${process.env.portal}/nodejs/logs`
    var log_file = exports.log_file = `${log_dir}/shell_execute`;
    var shell_log_file = exports.shell_log_file = process.env.portal+"/logs/shell_execute";
    var echo_log_file = exports.echo_log_file = process.env.portal+"/logs/echo";
    var servers_properties_dir = exports.servers_properties_dir = "C:/Users/MIS/top-secret/servers-properties";
}catch(e){}