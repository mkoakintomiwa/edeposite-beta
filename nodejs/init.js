const fs = require("fs");
const path = require("path");
const fx = require("./functions");
const { info_prompt } = require("./stdout");
const { randomBytes } = require("crypto");

const config_path = path.join(__dirname,"..",".hftp","config.json"); 

if (!fs.existsSync(config_path)) fs.writeFileSync(config_path,"{}");
var config = JSON.parse(fs.readFileSync(config_path).toString());

const subject = "hftp";

(async _=>{
    await info_prompt("Domain name: ",subject,config.domain_name||"https://google.com").then(val=>{
        config.domain_name = val;
    });
    rewrite_config();


    await info_prompt("rel_dirname: ",subject,config.rel_dirname||"").then(val=>{
        config.rel_dirname = val;
    });
    rewrite_config();


    await info_prompt("handshake_auth_key: ",subject,config.handshake_auth_key||fx.hash(randomBytes(16))).then(val=>{
        config.handshake_auth_key = val;
    });
    rewrite_config();


    config.ftp = config.ftp || {};
    var ftp = config.ftp;

    await info_prompt("ftp.host",subject,ftp.host||"50.116.98.84").then(p=>{
        ftp.host = p;
    });


    await info_prompt("ftp.username",subject,ftp.user||"musthy").then(p=>{
        ftp.user = p;
    });


    await info_prompt("ftp.password",subject,ftp.password||"hamdan").then(p=>{
        ftp.password = p;
    });


    await info_prompt("ftp.port",subject,ftp.port||"21").then(p=>{
        ftp.port = p;
    });

    config.ftp = ftp;

    rewrite_config();


    
    config.settings = config.settings || {};
    var settings = config.settings;


    await info_prompt("settings.db_user",subject,settings.db_user||"root").then(p=>{
        settings.db_user = p;
    });


    await info_prompt("settings.db_password",subject,settings.db_password||"").then(p=>{
        settings.db_password = p;
    });


    await info_prompt("settings.db_name",subject,settings.db_name||"main").then(p=>{
        settings.db_name = p;
    });


    await info_prompt("settings.rel_dirname",subject,settings.rel_dirname||config.rel_dirname).then(p=>{
        settings.rel_dirname = p;
    });

    await info_prompt("settings.site_port",subject,settings.site_port||config.site_port).then(p=>{
        settings.site_port = p;
    });

    settings.host = ftp.host;


    config.settings = settings;

    rewrite_config();

    fs.writeFileSync(path.join(".hftp/settings.json"),JSON.stringify(config.settings,null,4));
    
    var remote_assets_dir = `/public_html${config.rel_dirname}/assets`;
    var remote_hftp_dir = `/public_html${config.rel_dirname}/.hftp`;

    console.log("");
    await fx.ftp_mkdir(remote_assets_dir);
    await fx.ftp_mkdir(remote_hftp_dir);

    await fx.upload_files([
        {
            "local":path.join(".hftp","settings.json"),
            "remote":"/public_html/settings.json"
        },
        {
            "local":path.join(".hftp","config.json"),
            "remote":remote_hftp_dir.concat("/config.json")
        },
        {
            "local":path.join(".hftp","settings.php"),
            "remote":"/public_html/settings.php"
        },
        {
            "local":path.join("assets","handshake.php"),
            "remote":remote_assets_dir.concat("/handshake.php")
        },
        {
            "local":path.join("assets","handshake_functions.php"),
            "remote":remote_assets_dir.concat("/handshake_functions.php")
        },
        {
            "local":path.join("assets","db.php"),
            "remote":remote_assets_dir.concat("/db.php")
        },
        {
            "local":path.join("assets","universal.php"),
            "remote":remote_assets_dir.concat("/universal.php")
        },
        {
            "local":path.join("assets","twig.php"),
            "remote":remote_assets_dir.concat("/twig.php")
        }
    ]);

    
})();



function rewrite_config(){
    fs.writeFileSync(config_path,JSON.stringify(config,null,4));     
}