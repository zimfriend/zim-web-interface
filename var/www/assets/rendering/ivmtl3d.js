// material 3d interface

/* material flags
 1 - n
 2 - uv
 4 - colors
 8 - tm
 16 - bump
 256 - selected
*/

function channel3d(){
	this.mode=0;
	this.color=null;
	this.texture=null;
}

function mtlvar3d(id)
{
	this.id=id;
	this.slot=null;
}

function shader3d(f)
{
	this.flags=f;
	this.bValid=false;
	this.attrs=[];// list of attrs to program
	this.vars=[];// list of attrs to program
	this.textures=[];
	this.program=null;
	this.vShader=null;
	this.fShader=null;
	this.loadedtextures=0;
	this.numLights=0;
};

function material3d(space){
this.space=space;
this.gl=space.gl;
this.type="standard";
this.shaders=[];
// channels: diffuse, specular, emissive, reflection, bump, opacity
this.phong=18;
}

material3d.prototype.invalidate = function()
{
	var s=this.shader;
if(s)
{
for(var i=0;i<s.length;i++)s[i].detach(this.mtl);
this.shader=[];
}

}

material3d.prototype.reset = function()
{
	if(this.diffuse)delete this.diffuse;
	if(this.specular)delete this.specular;
	if(this.emissive)delete this.emissive;
	if(this.reflection)delete this.reflection;
	if(this.bump)delete this.bump;
	if(this.opacity)delete this.opacity;
	this.invalidate();
}

material3d.prototype.isChannel  = function(c)
{
	if( (c===undefined)||(c===null))return false;
	if(c.length===0)return false;
	for(var i=0;i<c.length;i++)
	{
		var item=c[i];
		if(item.texture!=null || item.color!=null || item.amount!=null)return true;
	}
	return false;
}

material3d.prototype.newChannel = function(type,ch)
{
	if(!ch)ch=new channel3d();
	if(!(type in this))this[type]=[];
	this[type].push(ch);
	this.bValid=false;
	return ch;
}

material3d.prototype.getChannel = function(type)//returns first channel
{
	if(!(type in this))return null;
	var items=this[type];
	return items[0];
}


material3d.prototype.newTexture = function(c,name,type)
{
	var gl=this.gl;
	if(type===undefined)type=gl.TEXTURE_2D;
	c.texture=this.space.getTexture(name,type);
	if(type==gl.TEXTURE_CUBE_MAP)
	{
		c.wrapS=gl.CLAMP_TO_EDGE;
		c.wrapT=gl.CLAMP_TO_EDGE;
	}else
	{
		c.wrapS=gl.REPEAT;
		c.wrapT=gl.REPEAT;
	}
}

function cnvTtxMatrix(a)
{
	var tm=mat3.create();
	var index=0;
	for(var i=0;i<3;i++)
	{
		for(var j=0;j<2;j++){tm[i*3+j]= a[index];index++;}
	}
	tm[2]=0;tm[5]=0;tm[8]=1;
	return tm;
}

material3d.prototype.loadChannel = function(d,name)
{
	var v=d[name];
	var type=typeof v;
	if(type==="object")
	{
		var c=this.newChannel(name);
		if((v instanceof Array) && v.length==3)c.color=v;
		else
		{
			if(v.color!==undefined)c.color=v.color;
			if(v.amount!==undefined)c.amount=v.amount;
			if(v.texture!==undefined)
			{
			var type=undefined;
			if(("type" in v) && v.type=="cube")type=this.gl.TEXTURE_CUBE_MAP;
			this.newTexture(c,v.texture,type);
			if(v.tm!==undefined)
				c.tm=cnvTtxMatrix(v.tm);
			if(v.cmp)c.cmp=v.cmp;
			}
		}
	}
};


material3d.prototype.load = function(d)
{
	for(var v in d)
	{switch(v)
	{
	case "diffuse":
	case "specular":
	case "emissive":
	case "reflection":
	case "opacity":
	case "bump":this.loadChannel(d,v);break;
	case "name":this.name=d[v];break;
	case "phong":this.phong=d[v];break;
	}}
	return true;
}

material3d.prototype.getShader = function(flags)
{
	flags&=~256;// remove selection
	if(!this.space.cfgTextures)flags&=~2;
	// we may need to remove bits from flags
	for(var i=0;i<this.shaders.length;i++)
	{
		var s=this.shaders[i];
		if(s.flags==flags)
		{
			if((s.loadedtextures!=s.textures.length) && s.bValid)
			{
				var c=s.readyTextures(false);
				if(c!=s.loadedtextures)s.bValid=false;
			}
			if(s.numLights!=this.space.lights.length)s.bValid=false;
			return s;
		}
	}
	var s=new shader3d(flags);
	this.shaders.push(s);
	return s;
}


shader3d.prototype.addVar = function(id)
{
	var v=new mtlvar3d(id);
	this.vars.push(v);
	return v;
}

shader3d.prototype.addAttr = function(id,shName,gl)
{
	var attr={};
	attr.id=id;
	attr.slot= gl.getAttribLocation(this.program, shName);
	gl.enableVertexAttribArray(attr.slot);// do we need this	
	this.attrs.push(attr);
}

shader3d.prototype.addLightVar = function(id,name,light)
{
	var v=this.addVar(id);
	v.name=name;
	v.light=light;
	return v;
}

shader3d.prototype.addChVar = function(id,name,ch)
{
	var v=this.addVar(id);
	v.name=name;
	v.channel=ch;
	return v;
}

function compareTM3(a,b)
{
	if(a===undefined && b===undefined)return true;
	if(a===undefined || b===undefined)return false;
	for(var i=0;i<9;i++)
		if(Math.abs(a[i]-b[i])>1e-4)return false;
	return true;
}

shader3d.prototype.getTexture=function(c)
{
	var items=this.textures;
	for(var j=0;j<items.length;j++)
	{
		var t=items[j];
		if(t.txt===c.texture && (t.wrapS==c.wrapS) && (t.wrapT==c.wrapT) && compareTM3(t.tm,c.tm))
			return t;
	}
	return null;
}

shader3d.prototype.preChannel=function(ch)
{
	var text="";
	for(var i=0;i<ch.length;i++)
	{
		var c=ch[i];
		c._id=this.channelId;this.channelId++;
		if(c.color!=null)
		{
			var name="ch"+c._id+"clr";
			this.addChVar("color",name,c);
			text+="uniform vec3 "+name+";\r\n";
		}
		if("amount" in c)
		{
			var name="ch"+c._id+"amount";
			this.addChVar("amount",name,c);
			text+="uniform float "+name+";\r\n";
		}
	}
	return text;
};

shader3d.prototype.handleChannel=function(gl,ch,cmp)
{
	var text="";
	for(var i=0;i<ch.length;i++)
	{
		var c=ch[i];
		var cname=null;
		var tname=null;
		var aname=null;
		if(c.color!=null){cname="ch"+c._id+"clr";}
		if(c.texture!=null && c.texture.ivready)
		{
			var t=this.getTexture(c);
			if(t){
				if(c.texture.ivtype==gl.TEXTURE_CUBE_MAP)
				{
				text+="vec3 lup = reflect(eyeDirection,normal);lup.y*=-1.0;vec4 refColor="+"textureCube(txtUnit"+t.slot+",lup);";//sorry - only one reflection texture, remove lup.y-1 from here
				tname="refColor";
				}else{
				tname="txtColor"+t.slot;
				}
			}
		}
		if(tname && c.amount!=null){aname="ch"+c._id+"amount";}
		if(cname || tname)
		{
		if(cmp)text+=cmp+"*=";
		else text+="color+=";
		if(aname && tname)text+="vec3("+aname+")*vec3("+tname +")";// color and amount
		else
		if(cname && tname)text+=cname+"*vec3("+tname +")";// color by texture
		else
		if(cname)text+=cname;
		else text+="vec3("+tname+")";
		text+=";\r\n";
		}
	}
	if(cmp)text+="color+="+cmp+";\r\n"
	return text;
};

shader3d.prototype.handleAlphaChannel=function(gl,ch)
{
	if(ch && ch.length)
	{
		if(ch.length==1)
		{
			var c=ch[0];
			var tname=null;
			var aname=null;

		if(c.texture!=null && c.texture.ivready)
		{
			var t=this.getTexture(c);
			if(t)
				tname="txtColor"+t.slot;
		}
		if(c.amount!=null){aname="ch"+c._id+"amount";}

		if(tname)
		{
			var t;
			if(aname)t=aname+"*";else t="";
			if(c.cmp && c.cmp=='a')
				t+=tname+".a";
			else
				t+="("+tname+".x+"+tname+".y+"+tname+".z)/3.0";
			return t;
		}else
		if(aname)return aname;
		}
	}
	return 1.0;
}

shader3d.prototype.handleBumpChannel=function(gl,ch)
{
	if(ch && ch.length)
	{
		var c=ch[0];
		if(c.texture!=null && c.texture.ivready)
		{
			var t=this.getTexture(c);
			if(t){
				if(c.texture)//
				{
					var tname="txtColor"+t.slot;
					var text="\r\nvec3 _n=vec3("+tname+");";
					text+="_n-=vec3(0.5,0.5,0);_n*=vec3(2.0,2.0,1.0);";
					if(c.amount!=null){var aname="ch"+c._id+"amount";text+="_n*=vec3("+aname+","+aname+",1.0);";}
					text+="_n=normalize(_n);";
					return text;
				}

			}
		}
	}
	return null;	
}

shader3d.prototype.collectTextures=function(ch)
{
	var rez=false;
	if(ch!=undefined)
	{
		for(var i=0;i<ch.length;i++)
		{
			var c=ch[i];
			if(c.texture){
				if(!this.getTexture(c))
				{
					var t={"txt":c.texture,"slot":0,"wrapS":c.wrapS,"wrapT":c.wrapT};
					if(c.tm){t.tm=c.tm;
					t.ch=c;
					}
					this.textures.push(t);
				}
				if(c.texture.ivready)
					rez=true;
			}
		}
	}
	return rez;
}

shader3d.prototype.readyTextures = function(bSet)
{
	var c=0;
	for(i=0;i<this.textures.length;i++)
	{
		var t=this.textures[i];
		if(t.txt.ivready)
		{
			if(bSet)t.slot=c;
			c++;
		}
	}
	return c;
}

shader3d.prototype.update = function(mtl)
{
	if(this.program)this.detach(mtl.gl);// may be just update?
	this.numLights=mtl.space.lights.length;
	this.channelId=0;
	var gl=mtl.gl,i;
	var _lights=null;
	var vText=(this.flags&8)?"uniform mat4 tmWorld;uniform mat4 tmModelView; uniform mat4 tmPrj;":"";
	var bNormals=(this.flags&1)!=0,bSpecular=false,bDiffuse=false,bLights=false,bReflection=false,bBump=false;
	var bEmissive=mtl.isChannel(mtl.emissive);
	var bOpacity=mtl.isChannel(mtl.opacity);
	var lights=mtl.space.lights;
	vText+="attribute vec3 inV;"
	vText+="varying vec4 wPosition;"
	if(this.flags&4)
	  vText+="varying vec3 vC;attribute vec3 inC;"
	
	var bUV=false;
	
	if(bNormals){
		vText+="varying vec3 wNormal;attribute vec3 inN;"
		if(lights.length)
		{
			if(mtl.isChannel(mtl.diffuse))bDiffuse=true;
			if(mtl.isChannel(mtl.specular))bSpecular=true;
			bLights=bDiffuse||bSpecular;
		}
	if(mtl.space.cfgTextures)
		bReflection=this.collectTextures(mtl.reflection);
	}
	if(this.flags&2){
		if(bDiffuse)bUV|=this.collectTextures(mtl.diffuse);
		if(bSpecular)bUV|=this.collectTextures(mtl.specular);
		if(bEmissive)bUV|=this.collectTextures(mtl.emissive);
		if(bOpacity)bUV|=this.collectTextures(mtl.opacity);
		if(bNormals && bLights)bUV|=(bBump=this.collectTextures(mtl.bump));
	}
	if(bUV)
		vText+="varying vec2 vUV;attribute vec2 inUV;";
	if(bBump)
	{vText+="varying vec3 vBN,vBT;attribute vec3 inBN,inBT;";}
	this.loadedtextures=this.readyTextures(true);
	
	vText+="\r\nvoid main(void){\r\n";
	if(this.flags&8){
		vText+="wPosition= tmWorld*vec4(inV,1.0);vec4 vPosition = tmModelView* wPosition; gl_Position = tmPrj* vPosition; ";
		this.addVar("tmWorld");this.addVar("tmModelView");this.addVar("tmPrj");
	}
	else vText+="gl_Position = vec4(inV,1.0);"
	if(bNormals)
		vText+="\r\n wNormal = normalize(vec3(tmWorld* vec4(inN,0.0)));"
	if(bBump){vText+="\r\n vBN = normalize(vec3(tmWorld* vec4(inBN,0.0)));vBT = normalize(vec3(tmWorld* vec4(inBT,0.0)));"}
	if(bUV)vText+="vUV = inUV;"
	if(this.flags&4)vText+="vC = inC;"

	vText+="}";

	var fText= "precision mediump float;"	
	if(bNormals)fText+="varying vec4 wPosition;";
	if(this.flags&4)fText+="varying vec3 vC;";
	if(bUV)fText+="varying vec2 vUV;";
	if(bBump)
		fText+="varying vec3 vBN,vBT;";
	if(bDiffuse)fText+=this.preChannel(mtl.diffuse);
	if(bSpecular)fText+=this.preChannel(mtl.specular);
	if(bEmissive)fText+=this.preChannel(mtl.emissive);
	if(bReflection)fText+=this.preChannel(mtl.reflection);
	if(bOpacity)fText+=this.preChannel(mtl.opacity);
	if(bBump)fText+=this.preChannel(mtl.bump);

	for(i=0;i<this.textures.length;i++)
	{
		var t=this.textures[i];
		if(t.txt.ivready){
			fText+="uniform ";
			if(t.txt.ivtype==gl.TEXTURE_CUBE_MAP)
				fText+="samplerCube";
			else
				fText+="sampler2D";
			fText+=" txtUnit"+ t.slot+";";
			if(t.tm){
				var v=this.addVar("tm");
				v.channel=t.ch;
				fText+="uniform mat3 ch"+t.ch._id+"tm;";
			}
		}
	}
	if(bNormals){
		fText+="uniform vec3 eye;";
		this.addVar("eye");
		if(bSpecular)
		{
			fText+="uniform float mtlPhong;";
			this.addVar("mtlPhong");
		}
		fText+="varying vec3 wNormal;"
		fText+="float k;";
		if(bLights)
		{
		fText+="vec3 diffuse,specular,lightDir;";//lightDir - for point lights only
		_lights=[];
		for(i=0;i<lights.length;i++)
		{
			var ls=lights[i];
			var l={};
			l.light=ls;
			var colorname="light"+i+"Clr";
			fText+="\r\n uniform vec3 "+colorname+";";
			l.colorname=colorname;
			this.addLightVar ("lightColor",colorname,ls);

			if(ls.dir)
			{
				var dirname="light"+i+"Dir";
				l.dirname=dirname;
				fText+="uniform vec3 "+dirname+";";
				this.addLightVar ("lightDir",dirname,ls);
			}
			if(ls.org)
			{
				var orgname="light"+i+"Org";
				l.orgname=orgname;
				fText+="uniform vec3 "+orgname+";";
				this.addLightVar ("lightOrg",orgname,ls);
			}
			_lights.push(l);
		}}
	}

	fText+="\nvoid main(void) {\r\n";
	for(i=0;i<this.textures.length;i++)
	{
		var t=this.textures[i];
		if(t.txt.ivready && t.txt.ivtype==gl.TEXTURE_2D){

			if(t.tm)
				fText+="vec2 _uv=vec2(ch"+t.ch._id+"tm*vec3(vUV,1.0));\r\n";
			fText+="vec4 txtColor"+t.slot+"= texture2D(txtUnit"+t.slot+","+ ((t.tm)?"_uv":"vUV")+");";
		}
	}

	if(bNormals)
	{
		fText+="vec3 normal = normalize(wNormal);"
		
		if(bBump)
		{
			var txt=this.handleBumpChannel(gl,mtl.bump);
			if(txt){
				fText+=txt;
				fText+="mat3 tsM = mat3(normalize(vBN), normalize(vBT), normal);";//tangent space matrix
				fText+="normal =  normalize(tsM*_n);";
			}
		}
		if(mtl.space.cfgDbl)fText+="if(!gl_FrontFacing)normal=-normal;"// revert tangent?
		
		fText+="vec3 eyeDirection = normalize(wPosition.xyz-eye);vec3 reflDir;";
		if(_lights)
		for(i=0;i<_lights.length;i++)
		{
			var l=_lights[i];
			var dirName;
			if(l.orgname){
				fText+="lightDir = normalize( wPosition.xyz-"+l.orgname+");";
				dirName="lightDir";
			}else dirName=l.dirname;

					
			if(bSpecular)
			{
			fText+="\nreflDir = reflect(-"+dirName+", normal);";
			fText+="k= pow(max(dot(reflDir, eyeDirection), 0.0), mtlPhong);";
			if(i)   fText+="specular+=";else fText+="specular=";
			fText+="k*"+l.colorname+";";
			}
			if(bDiffuse)
			{
			fText+="k = max(dot(normal, -"+dirName+"), 0.0);";
			if(i)   fText+="diffuse+=";else fText+="diffuse=";
			fText+="k*"+l.colorname+";";
			}
		}
	}
	fText+="vec3 color= vec3(0.0,0.0,0.0);\r\n"
	if(bReflection)
		fText+=this.handleChannel(gl,mtl.reflection,null);

	if(this.flags&4)
	{
		fText+= bDiffuse?"diffuse=diffuse*vC;":"color+=vC;"
	}
	if(bDiffuse)
		fText+=this.handleChannel(gl,mtl.diffuse,"diffuse");
	
	if(bSpecular)
		fText+=this.handleChannel(gl,mtl.specular,"specular");
	
	if(bEmissive)fText+=this.handleChannel(gl,mtl.emissive,null);
	if(bOpacity)
	{
		var n=this.handleAlphaChannel(gl,mtl.opacity);
		if (bReflection)
		{
		fText+="float alpha = "+n+";";
		//fText+="alpha +=(refColor.x+refColor.y+refColor.z)/3.0;";
		fText+="gl_FragColor = vec4(color,alpha);"
		}else{
			fText+="gl_FragColor = vec4(color,"+n+");"
		}
	}else 
		fText+="gl_FragColor = vec4(color,1);"
	fText+="}";

	this.vShader = ivCompileShader(gl, vText,gl.VERTEX_SHADER);
	this.fShader = ivCompileShader(gl, fText,gl.FRAGMENT_SHADER);

	var shPrg = gl.createProgram();
	this.program=shPrg;

	gl.attachShader(shPrg, this.vShader);
	gl.attachShader(shPrg, this.fShader);
	gl.linkProgram(shPrg);

	if (!gl.getProgramParameter(shPrg, gl.LINK_STATUS)) {
		alert("Could not initialise shaders");
	}

	gl.useProgram(shPrg);

	// addAttr after useProgram
	this.addAttr("v","inV",gl);
	if(bNormals)this.addAttr("n","inN",gl);
	if(bUV)this.addAttr("uv","inUV",gl);
	if(bBump){this.addAttr("bn","inBN",gl);this.addAttr("bt","inBT",gl);}
	

	if(this.flags&4)this.addAttr("clr","inC",gl);
	for(i=0;i<this.textures.length;i++)
	{
		var t=this.textures[i];
		if(t.txt.ivready)
		{
			t.uniform=gl.getUniformLocation(shPrg, "txtUnit"+t.slot);
		}
	}
	for(i=0;i<this.vars.length;i++)
	{
		var v=this.vars[i];
		switch(v.id)
		{
		case "tm":v.slot=gl.getUniformLocation(shPrg, "ch"+v.channel._id+"tm");break;
		case "color":v.slot=gl.getUniformLocation(shPrg, "ch"+v.channel._id+"clr");break;
		case "amount":v.slot=gl.getUniformLocation(shPrg, "ch"+v.channel._id+"amount");break;
		case "lightColor":
		case "lightDir":
		case "lightOrg":v.slot=gl.getUniformLocation(shPrg, v.name);break;
		default:v.slot=gl.getUniformLocation(shPrg, v.id);
		}		
	}
	this.bValid=true;
	return true;
}

shader3d.prototype.detach = function(gl)
{
	if(this.program!==null)
	{
		gl.detachShader(this.program,this.vShader);
		gl.detachShader(this.program,this.fShader);
		gl.deleteProgram(this.program);
		gl.deleteShader(this.vShader);
		gl.deleteShader(this.fShader);
		this.program=null;
		this.fShader=null;
		this.vShader=null;
	}
	this.attrs=[];
	this.vars=[]; 
	this.textures=[];
	this.loadedtextures=0;
}

shader3d.prototype.activate = function(space,mtl,tm,flags,newObj)
{
	var gl=mtl.gl,i;
	if(!newObj){
	gl.useProgram(this.program);
	for(i=0;i<this.textures.length;i++)
		{
			var t=this.textures[i];
			if(t.txt.ivready)
			{
				gl.activeTexture(gl.TEXTURE0+t.slot);
				var type=t.txt.ivtype;
				gl.bindTexture(type, t.txt);
				if(type==gl.TEXTURE_2D && space.e_ans)
				gl.texParameterf(type, space.e_ans.TEXTURE_MAX_ANISOTROPY_EXT, space.e_ansMax);
				gl.texParameteri(type, gl.TEXTURE_WRAP_S, t.wrapS);//gl.REPEAT
				gl.texParameteri(type, gl.TEXTURE_WRAP_T, t.wrapT);
				gl.uniform1i(t.uniform,t.slot);
			}
		}
	}
	if(flags&256)//selection
	{
		var sel=space.clrSelection;
	}

	for(i=0;i<this.vars.length;i++)
	{
		var a=this.vars[i],s=a.slot;
		switch(a.id)
		{
			case "tmWorld":gl.uniformMatrix4fv(s, false, tm);break;
			case "tmModelView":gl.uniformMatrix4fv(s,false, space.modelviewTM);break;
			case "tmPrj":gl.uniformMatrix4fv(s,false,space.projectionTM);break;
			case "mtlPhong":gl.uniform1f(s,mtl.phong);break;
			case "color":{
var clr=sel?sel:a.channel.color;
//$TODO$ remove work with string from production code
var t=typeof clr;
if( t=== 'string')
{
	clr=parseInt(clr.substr(1,6),16);
	if(!sel)a.channel.color=clr;
	t= 'number';
}
if( t=== 'number')gl.uniform3f(s,((clr>>16)&0xff)/255,((clr>>8)&0xff)/255,(clr&0xff)/255);
else
gl.uniform3fv(s,clr);

}break;
			case "tm":gl.uniformMatrix3fv(s, false, a.channel.tm);break;
			case "amount":gl.uniform1f(s,a.channel.amount);break;
			case "lightColor":gl.uniform3fv(s,a.light.color);break;
			case "lightOrg":gl.uniform3fv(s,a.light.org);break;
			case "lightDir":gl.uniform3fv(s,a.light.dir);break;
			case "eye":gl.uniform3fv(s, space.window.viewFrom);break;
			default:{
			
			}
		}
	}
}

function ivCompileShader(gl,str,type)
{
	var shader= gl.createShader(type);
	gl.shaderSource(shader, str);
	gl.compileShader(shader);
	if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
		alert(str+"\r\n"+gl.getShaderInfoLog(shader));
		return null;
	}
	return shader;
}
