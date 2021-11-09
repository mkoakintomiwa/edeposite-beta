const fs = require("fs");
const path = require("path");
const babel = require("@babel/core");
const sass = require('sass');
const fx = require("./functions.js");
const { chalk } = require("./stdout");

var transpile_typescript = exports.transpile_typescript = function(file_path,output_path=null,minify=true){
    return new Promise(async resolve=>{
        var _return = null;
        try{
            var transpiled = babel.transformFileSync(file_path,{
                presets: ["@babel/preset-typescript","@babel/preset-env","minify"],
                plugins: ["transform-class-properties"],
                comments:false
            }).code;


            if (output_path){
                var output_dirname = path.dirname(output_path);
                if (!fs.existsSync(output_dirname)) fs.mkdirSync(output_dirname,{recursive:true});
                fs.writeFileSync(output_path,transpiled);
            }

            if (minify){
               
            }

            
            _return = transpiled;
            
        }catch(e){
            console.log(chalk.redBright(e))
        }

        resolve(_return);
    });
}


var transpile_sass = exports.transpile_sass = function(file_path,output_path=null){
    return new Promise(resolve=>{
        var _return = null;

        try{
            var css = sass.renderSync({
                file: file_path,
                outputStyle:'compressed'
            }).css.toString();

            if (output_path){
                var output_dirname = path.dirname(output_path);
                if (!fs.existsSync(output_dirname)) fs.mkdirSync(output_dirname,{recursive:true});
                fs.writeFileSync(output_path,transpiled);
            }

            _return = css;
        
        }catch(e){
            console.log(chalk.redBright(e))
        }
        resolve(_return);
    });
}