

function MCPItem(h,v,i)
{
	this.n=null;
	this.l=null;
	this.r=null;
	this.h=h;
	this.v=v.slice();
	this.i=i;
};

function FMCP()
{
	this.root=null;
	this.items=[];
	this.htable=[1,87,98,3,101,4];
};


FMCP.prototype.calcHache=function(d,pos)
{
	var h=0;
	for(k=0;k<6;k++)
		h+=d.getUint16(pos+k*2)*this.htable[k];
	return h;
}

FMCP.prototype.add=function(h,v)
{
	var pi=this.root;
	var _pi=null;
	var f=0;
	while(pi)
	{
		var _h=pi.h;
		_pi=pi;
		if(_h<h)
		{
			f=1;
			pi= pi.l;
		}
		else
		if(_h>h)
		{
			f=2;
			pi= pi.r;
		}
		else
		{
			while(pi)
			{	
				if((pi.v[0]==v[0])&&(pi.v[1]==v[1])&&(pi.v[2]==v[2]))
				{
					return pi.i;
				}
				_pi=pi;
				f=3;
				pi= pi.n;
			}
			break;
		}
	}
	var i=new MCPItem(h,v,this.items.length);
	if(_pi)
	{
		switch(f)
		{
			case 1:_pi.l=i;break;
			case 2:_pi.r=i;break;
			case 3:_pi.n=i;break;
		}
	}else this.root=i;
	this.items.push(i);
	return i.i;
}


function filterSTL(d,wnd)
{
	this.data=d;
	this.size=d.byteLength;
	this.pos=0;// used by text version mostly
	this.wnd=wnd;
	this.key=[];
}

filterSTL.prototype.isBinary=function()
{
	var d=this.data;
	var size=d.getUint32(80,true);
	//
	if((size*(12*4+2) +84)==this.size)return true;
	for(var i=0;i<80;i++)
	{
		var b=d.getUint8(i);
		if(b==0x53 || b==0x73)//S
		{
			b=d.getUint8(i+1);
			if(b==0x4f || b==0x6f)//o
			{
				b=d.getUint8(i+2);
				if(b==0x4c || b==0x6c)//l
				{
				b=d.getUint8(i+3);
				if(b==0x49 || b==0x69)//i
				{
				b=d.getUint8(i+4);
				if(b==0x44 || b==0x64)//i
					return false
				}
				}
			}
		}
	}
	return true;
}

filterSTL.prototype.makeMesh=function(mesh)
{
	var scene=this.wnd.space;
	var node=this.wnd.amfObject;
	var mtl=this.wnd.fileMaterial1;
	
	var normals=generateNormals(mesh.vertices,mesh.triangles);
	var n=node.newNode();
	if((normals.length/3)<65535)
	{
		var m=new mesh3d(scene.gl);
		makeMesh(m,mesh.vertices,mesh.triangles,normals);
		n.setObject(m);
		n.material=mtl;
	}else
	{
		makeMeshSubD(scene.gl,n,mtl,mesh.vertices,mesh.triangles,normals);
	}
	n.enableTM();
}

filterSTL.prototype.importBinary=function()
{
	var d=this.data;
	var pos=0;
	var i,j,k;
	
	while((pos+80)<d.byteLength)
	{
		pos+=80;
		var size=d.getUint32(pos,true);
		pos+=4;
		var mesh={};
		mesh.triangles=[];
		var v=[3];
		var cmp=new FMCP();
		for(i=0;i<size;i++)
		{
			var t={v:[0,0,0]};
			mesh.triangles.push(t);
			pos+=12;
			
			
			for(j=0;j<3;j++)
			{
			var h=cmp.calcHache(d,pos);

			//DIY change STL unit to mm
			// v[0]=d.getFloat32(pos,true);pos+=4;
			// v[1]=d.getFloat32(pos,true);pos+=4;
			// v[2]=d.getFloat32(pos,true);pos+=4;
			v[0] = d.getFloat32(pos, true) / 10; pos += 4;
			v[1] = d.getFloat32(pos, true) / 10; pos += 4;
			v[2] = d.getFloat32(pos, true) / 10; pos += 4;
			//DIY end - PNI
			t.v[j]=cmp.add(h,v);
			}
			pos+=2;
		}
		this.finishMesh(mesh,cmp);
	}
};

filterSTL.prototype.finishMesh=function(mesh,cmp)
{
		mesh.vertices=[];
		for(i=0;i<cmp.items.length;i++)
		{
			var v={v:cmp.items[i].v};
			mesh.vertices.push(v);
		}
		this.makeMesh(mesh);
}

filterSTL.prototype.skipLine=function()
{
	while(this.pos<this.size)// skip spaces
	{
		var c=this.data.getUint8(this.pos);
		if(c==13 || c==10)return true;else this.pos++;
	}
	return false;
}

filterSTL.prototype.getWord=function()
{
	if(this.pos>=this.size)return false;
	var d=this.data;
	while(this.pos<this.size)// skip spaces
	{
		var c=d.getUint8(this.pos);
		if(c==32 || c==13 || c==10 ||c==9)this.pos++;else break;
	}
	var j=0;
	while(this.pos<this.size)// skip spaces
	{
		var c=d.getUint8(this.pos);this.pos++;
		if(c==32 || c==13 || c==10 || c==9)break;else {
			this.key[j]=c;
			j++;
		}
	}
	this.keyLen=j;
	return true;
}


filterSTL.prototype.initTokens=function()
{
	this.tokens=[];
	var s=["vertex","facet","normal","outer","loop","endloop","endfacet","endsolid","solid"];
	for(var i=0;i<s.length;i++)
	{
		var t={};
		this.tokens.push(t);
		t.key=s[i];
		t.l=t.key.length;
		t.c=[];
		for(var j=0;j<t.l;j++)
		{
			t.c.push(t.key.charCodeAt(j)|0x20);
		}
	}
};

filterSTL.prototype.getToken=function()
{
	var l=this.keyLen;
	var _i=this.tokens;
	var d=this.data;
	var key=this.key;
	for(var i=0;i<_i.length;i++)
	{
		var t=_i[i];
		if(t.l==l)
		{
			var ok=true;
			for(var j=0;j<l;j++)
			{
				if((key[j]|0x20)!=t.c[j]){ok=false;break;}
			}
			if(ok)return t;
		}
	}
	return null;
}


filterSTL.prototype.getHache=function(cmp,v)
{
	this.hBuffer.setFloat32(0,v[0],true);
	this.hBuffer.setFloat32(4,v[1],true);
	this.hBuffer.setFloat32(8,v[2],true);
	return cmp.calcHache(this.hBuffer,0);
}

filterSTL.prototype.readV=function(v)
{
	var a=this.key;
	for(var i=0;i<3;i++)
	{
		if(!this.getWord())return false;
		// trying to parse float with manual code - avoiding converting to string
		// should be two times faster
		var n=0;
		var j=0;
		var sign=1.0;
		if(a[0]==0x2d){sign=-1;j=1;}
		var l=this.keyLen;
		var mode=0;
		while(j<l)
		{
			var c=a[j];j++;
			if(c>=0x30 && c<=0xc39){
				if(mode)
				{n+= (c-0x30)/(mode);mode*=10;}
				else
					n=n*10+ (c-0x30);
			}else
			if(c==0x2e)
				mode=10;
			else {mode=-1;break;}
		}
		//DIY change STL unit to mm
		if(mode<0)
		{
		// fallback to non supported form
		var str=String.fromCharCode.apply(null, a);
		// v[i]=parseFloat(str);
		v[i] = parseFloat(str) / 10;
		// }else v[i]=n*sign;
		}
		else {
			v[i] = n * sign / 10;
		}
		//DIY end - PNI
	}
	return true;
}

filterSTL.prototype.importASCII_solid=function()
{
	var mesh={};
	mesh.triangles=[];
	var v=[3];
	var cmp=new FMCP();
	
	while(this.getWord())
	{
		var t=this.getToken();
		if(!t)break;
		if(t.key=='endsolid')break;
		if(t.key=='facet')
		{
			var iv=0;
			var tri={v:[0,0,0]};
			mesh.triangles.push(tri);

			while(this.getWord())
			{
			t=this.getToken();
			if(!t)break;
			if(t.key=='normal')
			{
				//if(!this.readV(v))break;
				for(var j=0;j<3;j++){
					if(!this.getWord())break;
				}
			}else
			if(t.key=='vertex')
			{
				if(!this.readV(v))break;
				var h=this.getHache(cmp,v);
				tri.v[iv]=cmp.add(h,v);iv++;
			}else
			if(t.key=='outer')continue;// do nothing
			if(t.key=='loop')continue;// do nothing
			if(t.key=='endloop')continue;// do nothing
			if(t.key=='endfacet')break;
			}
		}
	}
	this.finishMesh(mesh,cmp);
	return true;
}

filterSTL.prototype.importASCII=function()
{
	if(!this.tokens)this.initTokens();
	if(!this.hBuffer)
	{
		var a=new ArrayBuffer(12);
		this.hBuffer=new DataView(a);
	}
	while(this.getWord())
	{
		var t=this.getToken();
		if(!t)break;
		if(t.key=='solid')
		{
			if(!this.skipLine())break;
			if(!this.importASCII_solid())break;
		}
	}
}


function STLParse(wnd,buffer)
{
	//console.profile("STL Parse");
	var root=wnd.space.root.newNode();
	wnd.amfObject=root;
	var data= new DataView(buffer);
	var flt= new filterSTL(data,wnd);
	if(flt.isBinary())
		flt.importBinary();
	else flt.importASCII();
	postCreateObject(wnd);
	//console.profileEnd();
}

/* DIY comment original STL loading function - PNI
function zpLoadSTL(view,file)
{
	var request = CreateRequest(file,null);
	request.timeout=30000;//30sec
	request.ivwnd=view;
	request.onreadystatechange = function () {
		if (this.readyState == 4 && this.status==200) {
			var arrayBuffer = this.response;
			STLParse(this.ivwnd,this.response);
		}
	}
	request.responseType = "arraybuffer";//binary
	request.send();
}
 */
