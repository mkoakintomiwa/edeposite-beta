const variables = require("./variables");
const fs = require("fs");
const path = require("path");
const {spawn} = require("child_process");
const { exitProcess } = require("yargs");


class log{

	static write(content){
		fs.writeFileSync(variables.log_file,content);
	}

	static clear(){
		fs.writeFileSync(variables.log_file,'');
	}

	static content(){
		return fs.readFileSync(variables.log_file,"utf-8");
	}
}

exports.log = log;

var portal_properties_dir = exports.portal_properties_dir = function(portal_id){
	return path.normalize(`${variables.portal_properties_dir}/${portal_id}`)
}

var portal_properties = exports.portal_properties = function(portal_id){
    return variables.portal_properties.portalProperties(portal_id);
}

var remote_dir = exports.remote_dir = function(portal_id){
	return `/home/${portal_properties(portal_id).ssh.username}`;
}

var remote_public_html = exports.remote_public_html = function(portal_id){
	return `${remote_dir(portal_id)}/public_html`;
}


var remote_portal_dir = exports.remote_portal_dir = function(portal_id){
	return `${remote_public_html(portal_id)}${portal_properties(portal_id).rel_dirname}`;
}


var time = exports.time = function(){
    return Date.now();
}

var subdirectories = exports.subdirectories = function(directory){
	var _subdirectories = [];
	fs.readdirSync(directory).forEach(filename=>{
		if (fs.lstatSync(path.normalize(directory+'/'+filename)).isDirectory()) _subdirectories.push(filename);
	});
	return _subdirectories;
}


var files_in_directory = exports.files_in_directory = function(directory){
	var _files_in_directory = [];
	fs.readdirSync(directory).forEach(file=>{
		if (fs.lstatSync(path.join(directory,file)).isFile()) _files_in_directory.push(path.basename(file));
	});
	return _files_in_directory;
}



var copyFiles = exports.copyFiles = function(source,destination,excluded=[]){
	if (!fs.existsSync(destination)) fs.mkdirSync(destination,{recursive:true});
	const ncp = require("ncp").ncp;
	return new Promise (resolve=>{
		ncp.limit = 16;

		ncpOptions = {
			filter : function(file){
				filesBool = [];
				excluded.forEach((k)=>{
				filesBool.push(file.toString().indexOf(k)===-1);
				});
				return !filesBool.includes(false);
			}
		}
		
		ncp(source, destination, ncpOptions, function (err) {
			if (err) {
				resolve(err);
			}else{
				resolve(true);
			}
		});
	});
}



var emptyDir = exports.emptyDir = function(dir){
	const fsExtra = require("fs-extra");
	return new Promise(resolve=>{
		fsExtra.emptyDir(dir,_=>{
			resolve();
		})
	})
}


var rmdir = exports.rmdir = function(dir){
	const fsExtra = require("fs-extra");
	return new Promise(resolve=>{
		fsExtra.remove(dir,_=>{
			resolve();
		});
	});
}

/**
 * @param {String[]} dirs
 * @param {String} cwd
 */
var rmdirs = exports.rmdirs = async function(dirs,cwd=null){
	 
	for (let dir of dirs){
		var _dir;
		if (cwd){
			_dir = path.join(cwd,dir);
		}else{
			_dir = dir;
		}
		await rmdir(_dir);
	}
}



/**
 * @param {String[]} file_content_array
 * @param {String} cwd
 */
var writeFiles = exports.writeFiles = function(file_content_array,cwd=null,callback=null){
	return new Promise(async resolve=>{
		for (let file_content of file_content_array){
			const file = file_content[0];
			const _path = cwd?path.join(cwd,file):file;
			const content = file_content[1];
			await new Promise(resolve=>{
				fs.writeFile(_path,content,_=>{
					if(callback) callback(_path);
					resolve();
				});
			});
		}
		resolve();
	});
}



var setDefault = exports.setDefault = function(parameter,value){
	return typeof parameter != 'undefined' ? parameter : value;
}

var setDefaults = exports.setDefaults = function(defaults,options){
	for (let property in defaults){
        let value = defaults[property];
		if (typeof options[property] === 'undefined') options[property] = value; 
	};
    return options;
}

var document_root = exports.document_root = function(root_file="settings.json"){
    var dirname = __dirname;
    while(true){
        if (fs.existsSync(dirname+'/'+root_file)){
            return dirname;
        }else{
            dirname=path.dirname(dirname);
        }
    }
    
}


var settings = exports.settings = function(){
	return JSON.parse(fs.readFileSync(document_root()+'/settings.json','utf8'));
}



var portal_dir = exports.portal_dir = function(){
	return document_root("portal_root.json");
}




var log_file = exports.log_file = function(){
	var _portal_dir = portal_dir();
	return `${_portal_dir}/nodejs/logs/shell_execute`;
}


var shell_log_file = exports.shell_log_file = function(){
	var _portal_dir = portal_dir();
	return `${_portal_dir}/logs/shell_execute`;
}


var echo_log_file = exports.echo_log_file = function(){
	var _portal_dir = portal_dir();
	return `${_portal_dir}/logs/echo`;
}



var shell_exec = exports.shell_exec = function(command,_options={}){
	
	var options = setDefaults({
		stdout :data=>{
			console.log(data);
		},
		complete:code=>{
			
		},
		hide_output:false
	},_options);
	
	var logFile = log_file();
	var command_append;

	if (options.hide_output){
		command_append = `> ${logFile}`;
	}else{
		command_append = `| tee -a ${logFile}`;
	}
	
	command = `${command} 2>&1 ${command_append}`;
	
	
	return new Promise(resolve=>{

		var spawnOptions = {
			stdio:'inherit',
			shell:true
		}

		if (typeof options['cwd'] != "undefined"){
			spawnOptions['cwd'] = options['cwd'];
		}

		const ls = spawn(command,spawnOptions);

		// ls.stdout.on("data",data=>{
		// 	options.stdout(data);
		// });

		ls.on("exit",code=>{
			var r = fs.readFileSync(logFile,"utf-8").trim();
			fs.writeFileSync(logFile,"")
			resolve(r);
		});
	});
}



var fileDialog = exports.fileDialog = function(default_path=null,title=null){

	var i = '';
	if (default_path) i+= ` --default-path="${default_path}" `;
	if (title) i+= ` --title="${title}" ` 


	return new Promise(resolve=>{
		shell_exec(`electron %portal%/nodejs/dialog ${i}`,{
			hide_output:true
		}).then(response=>{
			resolve(JSON.parse(response)[0]);
		});;
	})
}



var filesDialog = exports.filesDialog = function(default_path=null,title=null){
	var i = ` --multiple-selections="true" `;
	if (default_path) i+= ` --default-path="${default_path}" `;
	if (title) i+= ` --title="${title}" `; 


	return new Promise(resolve=>{
		shell_exec(`electron %portal%/nodejs/dialog ${i}`,{
			hide_output:true
		}).then(response=>{
			resolve(JSON.parse(response));
		});;
	});
}




var folderDialog = exports.folderDialog = function(default_path=null,title=null){
	const glob = require("glob");
	var i = ` --dialog-type="directory" `;
	if (default_path) i+= ` --default-path="${default_path}" `;
	if (title) i+= ` --title="${title}" `; 


	return new Promise(resolve=>{
		shell_exec(`electron %portal%/nodejs/dialog ${i}`,{
			hide_output:true
		}).then(response=>{
			var _path = JSON.parse(response)[0];
			console.log(_path);
			glob(`**`, {
                absolute:true,
                cwd:path.normalize(_path)
            }, function (er, dir_stat) {
                var files = dir_stat.filter(x=>fs.lstatSync(x).isFile());
                resolve(files);
            });
		});;
	});
}



var file_copy_contents = exports.file_copy_contents = function(file_path){
	return shell_exec(`type "${path.normalize(file_path)}" | clip`);
}


var encoded_url = exports.encoded_url = function(main_link,queryStringObject={}){
	var url = main_link;
	if (Object.keys(queryStringObject).length>0) url+="?";

	for (key in queryStringObject){
		var value = queryStringObject[key];
		url+=`${encodeURIComponent(key)}=${encodeURIComponent(value)}&`;
	}

	if (Object.keys(queryStringObject).length>0) url = url.replace(/&$/,'');

	return url;
}



var open_in_browser = exports.open_in_browser = function(url,browser='electron',_options={}){
	var options = setDefaults({
		nodeIntegration:false
	},_options);

	var command='';
	if (browser==="electron"){
		command = `electron %portal%/nodejs/browser "${url}"`;
		if (options.nodeIntegration) command+=` --node-integration="true" `
	}else{
		command = `start ${browser} "${url}" /new-window`;
	}
	return shell_exec(command);
}


var copy_to_clipboard = exports.copy_to_clipboard = function(content){
	log.write(content);
	return file_copy_contents(variables.log_file);
}


var unique_school_id = exports.unique_school_id = function(){
	return shell_exec(`php php/generate_school_id.php`,{
		hide_output:true
	});
}


var random_characters = exports.random_characters = function(length){
	return shell_exec(`php php/generate_random_characters.php --length=${length}`,{
		hide_output:true
	});
}


var school_portal_ids = exports.school_portal_ids = function(){
	var excluded = ['.git'];
	var portal_properties_dir = variables.portal_properties_dir;
	var portals_in_tree = subdirectories(portal_properties_dir).filter(x=>!excluded.includes(x));

	var r = [];

	for (let portal_id of portals_in_tree){
		if (fs.existsSync(`${variables.portal_properties_dir}/${portal_id}/portal-properties.json`)){
			var _school = school(portal_id);

			try{
				var compulsory_criteria = [
					_school.ssh,
					_school.settings,
					_school.htaccess,
					_school.htaccess.conditions
				];

				var compulsory_accumulated = [];
				for (let compulsory_criterion of compulsory_criteria){
					compulsory_accumulated.push(typeof compulsory_criterion != "undefined");
				}



				if (!compulsory_accumulated.includes(false)){
					var necessary_criteria = [
						_school.portal_id,
						_school.portal_url,
						_school.school_name,
						_school.handshake_auth_key,
						_school.integration_time,
						_school.ssh.host,
						_school.ssh.username,
						_school.ssh.password,
						_school.ssh.passphrase,
						_school.settings.db_name,
						_school.settings.db_user,
						_school.settings.db_password,
						_school.htaccess.conditions.https
					];

					var neccesary_accumulated = [];
					for (let neccesary_criterion of necessary_criteria){
						neccesary_accumulated.push(typeof neccesary_criterion != "undefined");
					}

					if (!neccesary_accumulated.includes(false)){
						r.push(portal_id);
					}
				}
			}catch(e){}
		}
	}
	return r;
}


var school = exports.school = function(school_portal_id){
	var _servers = servers();
	var servers_ids = Object.keys(_servers);
	if (servers_ids.includes(school_portal_id)){
		return _servers[school_portal_id];
	}else{
		return JSON.parse(fs.readFileSync(`${variables.portal_properties_dir}/${school_portal_id}/portal-properties.json`,"utf-8"));
	}
}


var schools = exports.schools = function(order_by="integration_time",sort="ASC"){
	var _school_portal_ids = school_portal_ids();
	
	var schools_data = [];

	for(let portal_id of _school_portal_ids){
		schools_data.push(school(portal_id));
	}

	if (sort==="ASC"){
		schools_data = schools_data.sort((a,b)=>{
			return a[order_by] - b[order_by]
		});
	}else if(sort==="DESC"){
		schools_data = schools_data.sort((a,b)=>{
			return b[order_by] - a[order_by]
		});
	}
	
	return schools_data;
}


var active_schools = exports.active_schools = function(){
	
	var _schools = schools();
	var _active_schools = [];
	for (let _school of _schools){
		if (_school.active){
			_active_schools.push(_school)
		}
	}
	
	return _active_schools;
}



var inactive_schools = exports.inactive_schools = function(){
	
	var _schools = schools();
	var _inactive_schools = [];
	for (let _school of _schools){
		if (!_school.active){
			_inactive_schools.push(_school)
		}
	}
	
	return _inactive_schools;
}



var portal_ids = exports.portal_ids = function(){
	var r = [];
	var _schools = schools();
	for(let _school of _schools){
		r.push(_school.portal_id);
	}
	return r;
}


var forward_slash = exports.forward_slash = function(string){
	return string.replace(/\\/g,"/");
}

var back_slash = exports.back_slash = function(string){
	return string.replace(/\//g,"\\");
}

var escapeRegExp = exports.escapeRegExp = function(string) {
	return string.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&');
}

var escape_sed = exports.escape_sed = function(command){
	return escapeRegExp(command).replace(/\//g,"\\/").replace(/\\\\\\/g,'\\');
}

var brackets_replace = exports.brackets_replace = function(string,properties,brackets=2){
	try{
		for(let property in properties){
			var value = properties[property];
			var b = "{".repeat(brackets);
			var eb = "}".repeat(brackets);
			string = string.replace(new RegExp(escapeRegExp(`${b}${property}${eb}`),'g'),value);
		}
	}catch(e){}
    return string;
}



var dollar_replace = exports.dollar_replace = function(string,properties){
	try{
		for(let property in properties){
			var value = properties[property];
			string = string.replace(new RegExp(escapeRegExp(`$${property}`),'g'),value);
		}
	}catch(e){}
    return string;
}


var real_array = exports.real_array = function(array,trim=false){
	var output = [];
	array.forEach(element=>{
		if (element.trim().length>0){
			output.push(trim?element.trim():element);
		}
	});
	return output;
}


var taskkil = exports.taskkil = function(process_name){
	return shell_exec(`taskkil /IMF ${process_name}`)
}


var colorpicker = exports.colorpicker = function(output="last_color"){
	var last_color = "ERROR_NO_COLOR_SUPPLIED"
	var history = [];
	var _resolve;

	return new Promise(resolve=>{
		
		shell_exec(`"C:/Program Files/Colorpicker/Colorpicker.exe"`,{
			hide_output:true
		}).then(response=>{
			try{
				response.match(/{ lastColor: '(.*)' }/g).forEach(element=>{
					var color = element.match(/lastColor: '(.*)'/)[1];
					history.push(color);
				});
				last_color = history.slice(-1).pop()
			}catch(e){}
			if (output==="last_color"){
				copy_to_clipboard(last_color).then(_=>{
					resolve(last_color);
				})
			}
			 
			if (output==="history"){
				resolve(history);
			}
			
		});
	});
}



var sleep = exports.sleep = function(milliseconds){
	return new Promise(resolve=>{
		setTimeout(resolve,milliseconds);
    });
}




var modify_resource_icon = exports.modify_resource_icon = function(resource_name,resource_icon_path,_options){

	var options = setDefaults({
		cwd:__dirname
	},_options);

	return shell_exec(`ResourceHacker -open "${resource_name}" -save "${resource_name}" -action addoverwrite  -res "${resource_icon_path}" -mask ICONGROUP,1,1033`,{cwd:options.cwd});
}

/**
 * @param select Decide if file path is focused in directory. Default `true`
 */
var show_in_explorer = exports.show_in_explorer = (file_path,select=true)=>{
	file_path = path.normalize(file_path);
    return new Promise(resolve=>{
		var command;
		if (select){
			command = `explorer /select,"${file_path}"` 
		}else{
			command = `explorer "${file_path}"`
		}
		 
		shell_exec(command).then(_=>{
			resolve();
		})
    });
}


var dotted_parameter = exports.dotted_parameter = function(parameter,object){
    var _p = real_array(parameter.split("."));

    var _r = object;
    for (let k of _p){
        _r = _r[k]
    }
    return _r;
}


var match = exports.match = function(pattern,haystack){
	var regex = new RegExp(pattern,"g")
	var matches = [];
	
	var match_result = haystack.match(regex);
	
	for (let index in match_result){
		var item = match_result[index];
		matches[index] = item.match(new RegExp(pattern)); 
	}
	return matches;
}


var parse_htaccess = exports.parse_htaccess = function(portal_id){

	const portal = process.env.portal;
	const htaccess_template_path = path.normalize(`${portal}/templates/htacess`);
	const htaccess_template = fs.readFileSync(htaccess_template_path,"utf-8");
	var htaccess = htaccess_template;

	const _variables = match("{{(.*)}}",htaccess_template);

	var _school = school(portal_id);

    for (let value of _variables){
		var variable = value[1];
		var dotted_value = dotted_parameter(variable,_school);
        htaccess = htaccess.replace(`{{${variable}}}`,((variable==="rel_dirname" && dotted_value.length===0)?"/":"")+dotted_value);
    }
    
    
    var lines = htaccess.split('\n');
    var new_lines = [];
    for (let index in lines){
        var line = lines[index];
        var matches =  match("<<(.*)>>",line);
        if (matches.length>0){
            var condition = matches[0][1];
    
            if (_school.htaccess.conditions[condition]){
                new_lines.push(line.replace(`<<${condition}>>`,""));
            }
        }else{
            if (!(index===0 && line.trim().length===0)){
                new_lines.push(line);
            }
        }
    }
    if (new_lines[0].trim().length===0) new_lines = new_lines.slice(1,new_lines.length);
    htaccess = new_lines.join('\n');
    fs.writeFileSync(`${variables.portal_properties_dir}/${portal_id}/.htaccess`,htaccess);
}



var require_portal_id = exports.require_portal_id = function(supposed_portal_id){
	var stdout = require("./stdout");
	return new Promise(resolve=>{
		if (supposed_portal_id){
			resolve(supposed_portal_id);
		}else{
			stdout.info_prompt("portal_id","required","demo").then(p=>{
				resolve(p);
			});
		}
	});
}



var portal_api_request = exports.portal_api_request = function(portal_id,relative_server_script="assets/handshake.php",request_options={}){
	var unirest = require("unirest");
	var _school = school(portal_id);

	request_options = setDefaults({
		fields:{},
		attachments:{}
	},request_options);

	return new Promise(resolve=>{
		unirest.post(_school.portal_url+"/"+relative_server_script).headers({
			"Authorization": _school.handshake_auth_key
		}).field(Object.assign({
			"auth_key":_school.handshake_auth_key
		},request_options.fields)).attach(request_options.attachments).then(async response=>{
			response['is_successful'] = response.status===200 && response.body && response.body.trim().length>0 && response.body.indexOf('Error: ')===-1 && response.body.indexOf('Redirecting')===-1;             
			resolve(response);
		});
	});
}



var portal_http_upload = exports.portal_http_upload = function(portal_id,relative_local_path,relative_remote_path=null,relative_server_script="/assets/handshake.php"){
	const portal = process.env.portal;
	return portal_api_request(portal_id,relative_server_script,{
		fields:{
			"filename":"\\"+(relative_remote_path?relative_remote_path:relative_local_path)
		},
		attachments:{
			"file":path.normalize(portal+"\\"+relative_local_path)
		}
	});
}


var file_request_success_message = exports.file_request_success_message = function(school,response,row_cursor_position,rows){
	const { default:chalk } = require("chalk");
	console.log("");
	console.log(`${chalk.yellowBright(response.body)} ${chalk.whiteBright('*')} ${chalk.yellowBright(`${row_cursor_position}/${rows.length} files synced`)} ${chalk.whiteBright('*')} ${chalk.yellowBright(`${parseFloat(((row_cursor_position/rows.length)*100).toFixed(2))}%`)}`);
}



var file_request_error_message = exports.file_request_error_message = function(school,response){
	const { default:chalk } = require("chalk");
	console.log("");
	console.log(`${school.school_name} * ${school.portal_id} * ${chalk.redBright(response.raw_body)} * ${chalk.redBright(response.statusMessage)}`);
	console.log("");
}



var servers = exports.servers = function(){
	var properties_containers = subdirectories(variables.servers_properties_dir);
	
	var servers_information = {};
	for (let properties_container of properties_containers){
		servers_information[properties_container] = JSON.parse(fs.readFileSync(`${variables.servers_properties_dir}/${properties_container}/server-properties.json`,"utf-8"));
		servers_information[properties_container]["privateKey"] = path.normalize(`${variables.servers_properties_dir}/${properties_container}/id_rsa.ppk`) 
	}
	return servers_information;
}


var server = exports.server = function(server_id){
	return servers()[server_id];
}


var server_ids = exports.server_ids = function(){
	return subdirectories(variables.servers_properties_dir);
}

 
var write_portal_properties = exports.write_portal_properties = function(portal_id,properties){
	var _portal_properties_dir = portal_properties_dir(portal_id);
	fs.writeFileSync(path.normalize(`${_portal_properties_dir}/portal-properties.json`),JSON.stringify(properties,null,4));
}


class minify{
	
	static js(code){
		const { minify } = require("javascript-minifier");
		return new Promise(resolve=>{
			minify(code).then(minified_code=>{
				resolve(minified_code);
			}).catch(error=>{
				resolve(null);
			});
		})
	}
}

exports.minify = minify;


var git_full_address = exports.git_full_address = function(username,password){
	return `https://${username}:${encodeURIComponent(password)}@github.com/${username}/`;
}



var random_float = exports.random_float = function(min,max,precision=2){
    return (Math.random() * (max - min) + min).toFixed(precision);
}


var config = exports.config = function(){
    return JSON.parse(fs.readFileSync(path.join(".hftp","config.json")).toString())
}


var hash = exports.hash = function(string){
	var crypto = require('crypto');
	return crypto.createHash('sha256').update(string).digest('hex');
}



var upload_files = exports.upload_files = function(local_remote_array){

	return new Promise(resolve=>{
		var Client = require('ftp');
		var _config = config();

		var c = new Client();
		c.on('ready', async function() {

			for (let local_remote of local_remote_array){
				var local_path = local_remote["local"];
				var remote_path = local_remote["remote"];
				
				const chalk = require("chalk");
				
				console.log(`${chalk.magentaBright('put file:')} ${chalk.greenBright(forward_slash(local_path))} ${chalk.redBright(`->`)} ${chalk.cyanBright(forward_slash(remote_path))}`);

				await ftp_put(local_path,remote_path,c);
			}
			c.end();
			resolve();
		});

		c.connect(_config.ftp);
	});
}



var upload_file = exports.upload_file = function(local_path,remote_path){
	return upload_files([
		{
			local:local_path,
			remote:remote_path
		}
	]);
}


var upload_project_files = exports.upload_project_files = function(file_relative_paths){
	var local_remote_array = [];

	var _config = config();

	for (let rel_path of file_relative_paths){
		local_remote_array.push({
			local: path.join(_config.rel_dirname,rel_path),
			remote: `/public_html/${rel_path}`
		});
	}
	return upload_files(local_remote_array);
}



var upload_project_file = exports.upload_project_file = function(file_relative_path){
	return upload_file([file_relative_path]);
}


var ftp_put = exports.ftp_put = function(local_file_path, remote_file_path, ftp_client_connection){
	return new Promise(resolve=>{
		ftp_client_connection.put(local_file_path, remote_file_path, function(err) {
			resolve(err?err:true);
		});
	});
}




var ftp_mkdir = exports.ftp_mkdir = function(directory_relative_path){

	return new Promise(resolve=>{
		var Client = require('ftp');
		var _config = config();

		var chalk = require("chalk");
		console.log(chalk.magentaBright(`ftp > mkdir -> ${directory_relative_path}`));
		var c = new Client();
		c.on('ready', async function() {

			c.mkdir(directory_relative_path,true,(err)=>{
				c.end();
				resolve();
			});
		});

		c.connect(_config.ftp);
	});
}



var hftp_request = exports.hftp_request = function(request_options={}){
	var unirest = require("unirest");

	const _config = config();

	var request_options = setDefaults({
		fields:{},
		attachments:{}
	},request_options);

	return new Promise(resolve=>{
		unirest.post(_config.domain_name.concat("/assets/handshake.php")).headers({
			"Authorization": _config.handshake_auth_key
		}).field(Object.assign({
			"auth_key":_config.handshake_auth_key
		},request_options.fields)).attach(request_options.attachments).then(async response=>{
			response['is_successful'] = response.status===200 && response.body && response.body.trim().length>0 && response.body.indexOf('Error: ')===-1 && response.body.indexOf('Redirecting')===-1;
			console.log(response.raw_body)             
			resolve(response);
		});
	});
}


var base64_encode = exports.base64_encode = function(non_base64_string){
	return Buffer.from(non_base64_string).toString('base64');
}


var base64_decode = exports.base64_decode = function(base64_string){
	Buffer.from(base64_string, 'base64').toString('ascii')
}


var spawn_process = exports.spawn_process = function(command,options=null){
	return new Promise(resolve=>{
        const proc = spawn(command,options);
        var output;
        proc.stdout.on("data",data=>{
            output = data;
        });
        proc.on("close",_=>{
            resolve(output?output.toString():"null");
        });
    })
}