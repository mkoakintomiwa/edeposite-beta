const fs = require("fs");
const fx = require("./functions");
const path = require("path");
const argv = require("yargs").argv;
const chalk = require("chalk");


const context = argv._[0];
const first_intent = argv._[1];
const second_intent = argv._[2];
const document_root = fx.document_root("hftp.cmd");


var file_rel_dir = second_intent;
var source_path = path.join(document_root,'source/');
var file_dir = path.normalize(source_path+file_rel_dir);


switch(context){
    case "page":
        
        switch(first_intent){
        
            case "add":

                var file_formats = ['php','html','scss','ts'];

                for (let file_format of file_formats){
                    var file_location = path.normalize(`${file_dir}/${path.basename(file_dir)}.${file_format}`,'');
                    if (!fs.existsSync(file_dir)) fs.mkdirSync(file_dir,{recursive:true});
                    var pre_content = '';
                    if (file_format==="scss"){
                        pre_content = '@use "assets/scss/styles";\n@use "assets/scss/variables" as *;'    
                    }else if (file_format==="php"){
                        pre_content = fs.readFileSync(path.normalize(document_root+'/.hftp/page-template.php').toString());
                    }else if (file_format==="ts"){
                        pre_content = `import {log} from "util"`
                    }
                    fs.writeFileSync(file_location,pre_content);
                }

                //fs.writeFileSync(path.join(file_dir,"index.d.ts"),"");

                console.log(chalk.green(`${file_rel_dir} successfully added.`));
            break;
            

            case "remove":
                fx.rmdir(file_dir).then(_=>{
                    console.log(chalk.green(`${file_rel_dir} successfully removed.`));
                });
        }
}