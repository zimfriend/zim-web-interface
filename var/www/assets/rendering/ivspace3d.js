var IV={
INV_MTLS:2,
INV_VERSION:4
};


function space3d(view,gl){
	this.cfgTextures=true;
	this.gl=gl;
	this.window=view;
	this.root=null;
	this.view=null;
	this.materials=[];
	this.projectionTM = mat4.create();
	this.modelviewTM = mat4.create();
	this.cfgDbl=true;
	this.m_cfgKeepMeshData=3;// & 1 - faces, & 2 - vertices
	this.cfgDefMtl=null;
	this.textures=[];
	this.lights=0;
	this.activeShader=null;
	this.pre=[];
	this.post=[];
	this.clrSelection=[1,0,0];
	this.rmode=0;
	this.meshesInQueue=0;
	// each item f - faces, e - edge, n - normals, mtl - custom material
	this.rmodes=[//render modes
	{"f":true,"n":true,"e":false,"mtl":null},//solid mode
	{"f":false,"n":false,"e":true,"mtl":null},//wireframe
	];
}

function CreateRequest(f,p){
	if(f==undefined)return null;
	var r = new XMLHttpRequest();
	if(p)
		r.open("GET", p+f);
	else
		r.open("GET", f);
	return r;
}

space3d.prototype.getWindow = function(){return this.window;}


space3d.prototype.onMeshLoaded=function(m)
{
	this.meshesInQueue--;
	if(!this.meshesInQueue)
	{
	var w=this.window;
	if(w && w.onMeshesReady)
		w.onMeshesReady(w,this);
	}
	this.invalidate();
};

// update shader inputs
space3d.prototype.updateShadeArgs = function(a)
{
   var gl=this.gl,i;
   var p=this.activeShader;
   var ca = (p)?p.attrs.length:0,na=a?a.attrs.length:0;//current attributes, new attributes
   
	if (na > ca) //enable the missing attributes
	{
		for (i = ca; i < na; i++)gl.enableVertexAttribArray(i);
	}
	else if (na < ca) //disable the extra attributes
	{
		for (i = na; i < ca; i++)gl.disableVertexAttribArray(i);
	}   

	ca=p?p.textures.length:0;
	for (i = 0; i < ca; i++)
    {
		gl.activeTexture(gl.TEXTURE0+i);
		var txt=p.textures[i];
		var type=txt.txt.ivtype;
		gl.bindTexture(type===undefined?gl.TEXTURE_2D:type, null);
	}
}

space3d.prototype.activateShader = function(m,s,tm,flags)
{
	if(s!=this.activeShader)
		this.updateShadeArgs(s);
	if(s)s.activate(this,m,tm,flags,s==this.activeShader);
	else this.gl.useProgram(null);
	this.activeShader=s;
}

space3d.prototype.activateMaterial = function(m,tm,flags)
{
	var s=m?m.getShader(flags):0;
	if(s && !s.bValid)
	{
		if(this.activeShader)this.activateShader(null,null);// disable material
		s.update(m);
	}
	this.activateShader(m,s,tm,flags);
	return s;
}


function bk3d(space,txt)
{
	var gl=space.gl;
	this.uv=new Float32Array([0,0,1,0,0,1,0,1,1,0,1,1]);
	this.uvBuffer=ivBufferF(gl,this.uv,2);
	this.vertexBuffer=ivBufferF(gl,new Float32Array([-1.0,-1.0, 0.0,1.0, -1.0, 0.0,-1.0,1.0, 0.0,-1.0,1.0, 0.0,1.0,-1.0, 0.0,1.0,1.0,0.0]),3);
	var mtl=new material3d(space);
	var c=mtl.newChannel("emissive");
	mtl.newTexture(c,txt);
	c.wrapS=gl.CLAMP_TO_EDGE;
	c.wrapT=gl.CLAMP_TO_EDGE;
	this.mtl=mtl;
	this.texture=c.texture;
}

space3d.prototype.drawBk=function()
{

	if(this.bk && this.bk.texture.ivready)
	{
		var gl=this.gl;
		if(gl.viewportHeight && gl.viewportWidth)
		{
		gl.clear(gl.DEPTH_BUFFER_BIT);
		
			var bk=this.bk;
			var s=this.activateMaterial(bk.mtl,null,2);
			
		for(var i=0;i<s.attrs.length;i++)
		{
		 	var v=s.attrs[i];
		 	switch(v.id)
		 	{
		 	  case "v":{
					gl.bindBuffer(gl.ARRAY_BUFFER, bk.vertexBuffer);
					gl.vertexAttribPointer(v.slot, bk.vertexBuffer.itemSize, gl.FLOAT, false, 0, 0);
					   }break;
		 	  case "uv":{
		 	  		gl.bindBuffer(gl.ARRAY_BUFFER, bk.uvBuffer);
			var img=bk.texture.image;
			var kx=gl.viewportWidth/img.naturalWidth, ky=gl.viewportHeight/img.naturalHeight;
			var x=0,y=0;
			if(kx>ky)
				y=(1.0-ky/kx)/2;
			else
			if(kx<ky)
				x=(1.0-kx/ky)/2;
			var uv=bk.uv;
			if(Math.abs(uv[0]-x)>1e-5 || Math.abs(uv[1]-y)>1e-5)
			{
				// 0,0, 1,0, 0,1,
				uv[0]=x;uv[1]=y;  uv[2]=1.0-x;uv[3]=y;  uv[4]=x;uv[5]=1.0-y;
				//0,1, 1,0, 1,1]);
				uv[6]=x;uv[7]=1.0-y;  uv[8]=1.0-x;uv[9]=y;  uv[10]=1.0-x;uv[11]=1.0-y;
				gl.bufferData(gl.ARRAY_BUFFER, uv, gl.STATIC_DRAW);
			}
					gl.vertexAttribPointer(v.slot, bk.uvBuffer.itemSize, gl.FLOAT, false, 0, 0);
		 	  			}break;
		 	}		
		}
			gl.disable(gl.DEPTH_TEST);
			gl.depthMask(false);
			gl.drawArrays(gl.TRIANGLES, 0, 6);
			gl.enable(gl.DEPTH_TEST);
			gl.depthMask(true);
			return true;
		}}
	return false;
}

space3d.prototype.invalidate = function(flags)
{
	if(flags!==undefined)
	{
		if(flags&IV.INV_MTLS && this.materials)
		{
			for(var i=0;i<this.materials;i++)this.materials[i].invalidate();
		}
	}else flags=0;
	this.window.invalidate(flags);
}


function isPOW2(v){return (v&(v-1))==0;}
function handleLoadedTexture(texture) {
	if(texture.image.naturalWidth>0 && texture.image.naturalHeight>0)
	{
		var type=texture.ivtype;
		var gl=texture.ivspace.gl;
		gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
		gl.bindTexture(type, texture);
		gl.texImage2D(type, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, texture.image);
		var pot=isPOW2(texture.image.naturalWidth)&&isPOW2(texture.image.naturalHeight);
		gl.texParameteri(type, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		gl.texParameteri(type, gl.TEXTURE_MIN_FILTER, pot?gl.LINEAR_MIPMAP_NEAREST:gl.LINEAR);
		if(pot)gl.generateMipmap(type);
		gl.bindTexture(type, null);
		texture.ivready=true;
		texture.ivpot=pot;
		texture.ivspace.invalidate();
	}
	delete texture.image.ivtexture;
	delete texture.ivspace;
}

function handleLoadedCubeTexture(image)
{
	var texture=image.ivtexture;
	var gl=texture.ivspace.gl;
	gl.bindTexture(gl.TEXTURE_CUBE_MAP, texture);
	gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false);
	gl.texImage2D(image.ivface, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);
	gl.texParameteri(gl.TEXTURE_CUBE_MAP, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
	gl.texParameteri(gl.TEXTURE_CUBE_MAP, gl.TEXTURE_MAG_FILTER, gl.LINEAR);

	gl.bindTexture(gl.TEXTURE_CUBE_MAP, null);
	texture.ivnumfaces++;
	if(texture.ivnumfaces==6)
	{
		texture.ivready=true;
		texture.ivspace.invalidate();
		delete texture.ivspace;
	}
	delete image.ivtexture;
};

// no insertion into list

space3d.prototype.getTexture = function(str,type) {
	
	var t;
	for(var i=0;i<this.textures.length;i++)
	{
		var t=this.textures[i];
		if((t.ivfile==str) && (t.ivtype==type))
		{
			t.ivrefcount++;
			return t;
		}
	}
	var gl=this.gl;
	if(this.e_ans===undefined){
		this.e_ans = (gl.getExtension('EXT_texture_filter_anisotropic') ||gl.getExtension('MOZ_EXT_texture_filter_anisotropic') ||gl.getExtension('WEBKIT_EXT_texture_filter_anisotropic'));

	if(this.e_ans)	
	   this.e_ansMax = gl.getParameter(this.e_ans.MAX_TEXTURE_MAX_ANISOTROPY_EXT);
	}

	t = this.gl.createTexture();
	t.ivspace=this;
	t.ivready=false;
	t.ivfile=str;
	t.ivtype=type;
	t.ivrefcount=1;
	if(type==gl.TEXTURE_CUBE_MAP)
	{
	var faces = [["posx", gl.TEXTURE_CUBE_MAP_POSITIVE_X],
	["negx", gl.TEXTURE_CUBE_MAP_NEGATIVE_X],
	["posy", gl.TEXTURE_CUBE_MAP_POSITIVE_Y],
	["negy", gl.TEXTURE_CUBE_MAP_NEGATIVE_Y],
	["posz", gl.TEXTURE_CUBE_MAP_POSITIVE_Z],
	["negz", gl.TEXTURE_CUBE_MAP_NEGATIVE_Z]];

	t.ivnumfaces=0;
	var _str=str.split(".");
	if(this.path)_str[0]=this.path+_str[0];
		for(var i=0;i<6;i++)
		{
			var filename=_str[0]+faces[i][0]+"."+_str[1];
			var image = new Image();
			image.ivtexture=t;
			image.ivface=faces[i][1];
			image.onload = function () {handleLoadedCubeTexture(this)};
			image.src =filename;
		}
	}else{
	t.image = new Image();
	t.image.ivtexture=t;
	t.image.onload = function () {handleLoadedTexture(this.ivtexture)};
	t.image.src =this.path?this.path+str:str;
	}
	this.textures.push(t);
	return t;
}

space3d.prototype.load = function(data) {
	if(data)
	{
		if(data.space)
		{
			var s=data.space,m=s.meshes,i;
			var d={objects:[],materials:[],space:this};
			if(s.materials)
			for(i=0;i<s.materials.length;i++)
			{
			  var mtl=new material3d(this);
			  mtl.load(s.materials[i]);
			  this.materials.push(mtl);
			  d.materials.push(mtl);
			}
			if(m)
			for(i=0;i<m.length;i++)
			{
				var obj=new mesh3d(this.gl);
				if(this.path)
					obj.url=this.path+m[i].ref;
				else obj.url=m[i].ref;
				d.objects.push(obj);
			}
			if(s.root)
			{
				if(!this.root)this.root=new node3d();
				this.root.load(s.root,d);
			}
			if(s.view)
				this.view=s.view;
		
			this.lights=s.lights;
			for(i=0;i<this.lights.length;i++)
			{
			var l=this.lights[i];
			if(l.dir)vec3.normalize(l.dir);
			}
			if(s.views)this.views=s.views;
				
				if(data.space.bk!=undefined)
					this.bk=new bk3d(this,data.space.bk);
			var w=this.window;
			if(w && w.onDataReady)
				w.onDataReady(this);
		}
	}
};

space3d.prototype.renderQueue = function(items)// t transparency
{
	var c=items.length;
	var a;
	var gl=this.gl;
	for(var i=0;i<c;i++)
	{
		var item=items[i];
		var d=this.cfgDbl||((item.state&32)!=0);
		if(d!=a)
		{
			if(d)gl.disable(gl.CULL_FACE);else gl.enable(gl.CULL_FACE);
			a=d;
		}
		item.object.render(item.tm,this,item.mtl,item.state);
	};
}


space3d.prototype.updatePrjTM = function(tm)
{
	var w=this.window;
	var gl=this.gl;
	var v=[0,0,0];
	var bOk=false;
	var far=0,near=0,z;
	var tm=mat4.create();
	for(var iPass=0;iPass<2;iPass++)
	{
		var items=iPass?this.post:this.pre;if(!items)continue;
		var c=items.length;
		for(var iO=0;iO<c;iO++)
		{
			var item=items[iO];
			tm= mat4.m(item.tm,this.modelviewTM,tm);
			var _min=item.object.boxMin;
			var _max=item.object.boxMax;
			
			for(var i=0;i<8;i++)
			{
				v[0]=(i&1) ? _max[0] : _min[0];
				v[1]=(i&2) ? _max[1] : _min[1];
				v[2]=(i&4) ? _max[2] : _min[2];
				mat4.mulPoint(tm,v);
				z=-v[2];
				if(bOk)
				{
				if(z<near)near=z;else if(z>far)far=z;
				}else {far=near=z;bOk=true};
			}
		}
	}
	if(bOk)
	{
		var d=far-near;
		d/=100;far+=d;near-=d;// some guard distance
		d=far/1000;
		if(near<d)near=d;// avoid Z buffer corruption
	}else
	{
		near=w.viewNear,far=w.viewFar;
	}
	mat4.perspective(w.fov, gl.viewportWidth / gl.viewportHeight, near, far, this.projectionTM);
};

space3d.prototype.render = function(tm) {
	if(this.root){
		var gl=this.gl;
		gl.cullFace(gl.BACK);
		var tmWorld = mat4.create();
		mat4.identity(tmWorld);
		this.root.traverse(tmWorld,nodeRender,this,this.rmode<<8);
		
		mat4.copy(tm,this.modelviewTM);
		this.updatePrjTM(tm);
		this.renderQueue(this.pre);
		this.pre=[];
		
		if(this.post.length)
		{	
			gl.enable(gl.BLEND);
			gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
			this.renderQueue(this.post);
			gl.disable(gl.BLEND);
			this.post=[];
		}
		this.activateMaterial(null);//reset state
	}
};

space3d.prototype.toRenderQueue = function(atm,node,state)
{
	var mtl=this.cfgDefMtl?this.cfgDefMtl:node.material;
	var rmode=this.rmodes[(state&0xff00)>>8];
	if(rmode.mtl)mtl=rmode.mtl;
	var item={"tm":atm,"object":node.object,"mtl":mtl,"state":(state|(node.state&(16|32)))};
	var l=(mtl.opacity!=undefined)?this.post:this.pre;
	l.push(item);
};

space3d.prototype.getMaterial = function(name)
{
var it=this.materials;
	for(var i=0;i<it.length;i++)
	{
	var m=it[i];
	if((m.name!==undefined) && m.name==name)return m;
	}
return null;	
}
