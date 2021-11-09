const chokidar = require('chokidar');
const fx = require("./functions");
const {spawn} = require("child_process");
const px = require("path");

chokidar.watch('.',{
    ignored:/^.git|rust.*target|rust.*Cargo.lock|^\.hftp|^bin|^node_modules|hftp\.cmd|package.json|package-lock.json|^.vscode/,
}).on('change', async (path,event) => {
    
    var is_source_file = false
    if (path.match(/^source/)!=null){
        path = px.dirname(path.replace(/^source\\/,"")).concat(".php");
        is_source_file = true;
    }
    await trace_save(path,is_source_file);
});


async function trace_save(path,is_source_file){
    return new Promise(resolve=>{
        var data = {
            "filename":path,
            "is_source_file":is_source_file?"true":"false"
        }
    
        var base64_arg = fx.base64_encode(JSON.stringify(data))
        let proc = spawn("bin/trace-save",[base64_arg]);

        proc.stdout.on("data",data=>{
            process.stdout.write(data.toString());
        })

        proc.stderr.on("data",data=>{
            process.stdout.write(data.toString());
        })

        proc.stdout.on("close",()=>{
            resolve();
        })
    });
}
