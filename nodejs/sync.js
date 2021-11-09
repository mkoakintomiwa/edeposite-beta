const fx = require("./functions");
const path = require("path");
const unirest = require("unirest");
const fs = require("fs");
const { transpile_typescript,transpile_sass } = require("./transpilers");
const chalk  = require("chalk");

const argv = require("yargs").argv;
var config = fx.config();

const document_root = fx.document_root("hftp.cmd");

(async _=>{
    var rows;
    await fx.spawn_process("bin/unsynced").then(output=>{
        rows = JSON.parse(JSON.parse(output));
    })

    for (let row of rows){
        if (fs.existsSync(path.normalize(row.filename)) || row.is_source_file==="true"){
            var response;
            
            fc = row.filename.replace(/^\\/,'').replace('\\','/');

            if (row.is_source_file==="true"){
                var source_dir = document_root + '/source'
                var source_file_name = path.basename(row.filename).replace('.php','');
                var source_rel_dir = path.dirname(row.filename);
                var file_ordinance = path.normalize(`${source_dir}/${source_rel_dir}/${source_file_name}/${source_file_name}`);
                var source_content = fs.readFileSync(`${file_ordinance}.php`).toString();

                var transpiled_typescript;
                await transpile_typescript(`${file_ordinance}.ts`,null,file_ordinance.indexOf("head_script")===-1).then(_transpiled=>{
                    transpiled_typescript = _transpiled;
                });
                
                if(transpiled_typescript){
                    source_content = source_content.replace("<script></script>",`<script>\n\t${transpiled_typescript}\n</script>`);
                }

                var transpiled_sass;
                
                await transpile_sass(`${file_ordinance}.scss`).then(_transpiled=>{
                    transpiled_sass = _transpiled;
                });

                if (transpiled_sass){
                    source_content = source_content.replace("<style></style>",`<style>\n\t${transpiled_sass}\n</style>`);
                }
                
                source_content = source_content.replace('<!--HTML-->',fs.readFileSync(`${file_ordinance}.html`).toString());

                var output_file_path = path.normalize(`${document_root}/${row.filename}`);
                var output_file_dir = path.dirname(output_file_path);
                if (!fs.existsSync(output_file_dir)) fs.mkdirSync(output_file_dir,{recursive:true})
                fs.writeFileSync(output_file_path,source_content);
            

                if(transpiled_typescript!=null && transpiled_sass!=null){
                    console.log(chalk.green(`${path.normalize(fc)} successfully transpiled`));
                }
            }
            
            await fx.hftp_request({
                fields:{
                    "filename":"/".concat(row.filename)
                },
                attachments:{
                    "row":path.normalize(row.filename)
                }
            }).then(r=>{
                response = r;
            });


            if (response.is_successful){
                await fx.spawn_process("bin/update-sent",[fx.base64_encode(JSON.stringify({
                    "filename":row.filename
                }))])
            }
        }
    }

})();

