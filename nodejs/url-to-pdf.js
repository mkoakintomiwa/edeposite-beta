const fx = require("./functions");
const puppeteer = require('puppeteer');
const argv = require("yargs").argv;
const fs = require("fs");
const path = require("path");

(async () => {
    const browser = await puppeteer.launch({ args: ['--no-sandbox']});
    const page = await browser.newPage();

    const pdf_url = argv._[0];


    function random_characters(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
    }



    function random_digits(length) {
    var result           = '';
    var characters       = '0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
    }



    function unique_from_fs(directory_path, length, context){
        let  content;
        while(true){
            if (context==="digits"){
                content = random_digits(length)
            }else{
                content = random_characters(length);
            }

            
            if (!fs.existsSync(path.join(directory_path,content))){
                break;
            }
        }
        return content;
        
    }


    function unique_digits_from_fs(directory_path, length){
        return unique_from_fs(directory_path, length, "digits");
    }



    function unique_characters_from_fs(directory_path, length){
    return  unique_from_fs(directory_path, length, "characters");
    }

    const html = '/var/www/html'
    const nodejs = `${html}/nodejs`
    var portal_url = `http://34.70.42.98`



    //console.log(url);
    var file_id = unique_characters_from_fs(path.join(html,"tmp"),7);
    var file_name = file_id.concat(".pdf");
    var file_path = path.join(html,"tmp",file_name);
    var file_url = `${portal_url}/tmp/${file_name}`;


    await page.goto(pdf_url);
    await page.pdf({
        path:file_path,
        margin:{
            left:'20px',
            right:'20px'
        },
        printBackground: true,
        scale:0.9
    });

    await browser.close();
    
    console.log({
        url:file_url
    });

})();

